<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Throwable;

class UpdateService
{
    public const STATE_IDLE = 'idle';

    public const STATE_QUEUED = 'queued';

    public const STATE_RUNNING = 'running';

    public const STATE_DONE = 'done';

    public const STATE_FAILED = 'failed';

    private const STALE_AFTER_SECONDS = 1800;

    /**
     * A queued run that never reaches the command means the background process
     * could not start at all, which is worth surfacing quickly.
     */
    private const NEVER_STARTED_AFTER_SECONDS = 120;

    public function repository(): string
    {
        return (string) config('dotbio.repository');
    }

    public function branch(): string
    {
        return (string) config('dotbio.branch');
    }

    public function isEnabled(): bool
    {
        return (bool) config('dotbio.self_update_enabled') && $this->isGitCheckout();
    }

    public function isGitCheckout(): bool
    {
        return is_dir(base_path('.git'));
    }

    public function currentCommit(): ?string
    {
        $result = Process::path(base_path())->run(['git', 'rev-parse', 'HEAD']);

        return $result->successful() ? trim($result->output()) : null;
    }

    /**
     * Latest commit on the configured branch, or null when GitHub is unreachable.
     *
     * @return array{sha: string, message: string, author: string, date: string}|null
     */
    public function latestCommit(): ?array
    {
        return Cache::remember(
            'dotbio-latest-commit',
            (int) config('dotbio.update_check_ttl'),
            function (): ?array {
                try {
                    $response = Http::timeout(5)
                        ->withHeaders(['Accept' => 'application/vnd.github+json'])
                        ->get(sprintf('https://api.github.com/repos/%s/commits/%s', $this->repository(), $this->branch()));

                    if ($response->failed()) {
                        return null;
                    }

                    return [
                        'sha' => (string) $response->json('sha'),
                        'message' => strtok((string) $response->json('commit.message'), "\n") ?: '',
                        'author' => (string) $response->json('commit.author.name'),
                        'date' => (string) $response->json('commit.author.date'),
                    ];
                } catch (Throwable) {
                    return null;
                }
            },
        );
    }

    /**
     * Commits present on the remote branch but missing locally, newest first.
     *
     * @return array<int, array{sha: string, message: string, author: string, date: string}>
     */
    public function pendingCommits(): array
    {
        $current = $this->currentCommit();
        $latest = $this->latestCommit();

        if (! $current || ! $latest || $current === $latest['sha']) {
            return [];
        }

        return Cache::remember(
            'dotbio-pending-commits:'.$current.':'.$latest['sha'],
            (int) config('dotbio.update_check_ttl'),
            function () use ($current, $latest): array {
                try {
                    $response = Http::timeout(5)
                        ->withHeaders(['Accept' => 'application/vnd.github+json'])
                        ->get(sprintf(
                            'https://api.github.com/repos/%s/compare/%s...%s',
                            $this->repository(),
                            $current,
                            $latest['sha'],
                        ));

                    if ($response->failed()) {
                        return [];
                    }

                    $commits = array_map(fn (array $commit): array => [
                        'sha' => (string) $commit['sha'],
                        'message' => strtok((string) $commit['commit']['message'], "\n") ?: '',
                        'author' => (string) ($commit['commit']['author']['name'] ?? ''),
                        'date' => (string) ($commit['commit']['author']['date'] ?? ''),
                    ], $response->json('commits', []));

                    return array_reverse($commits);
                } catch (Throwable) {
                    return [];
                }
            },
        );
    }

    public function updateAvailable(): bool
    {
        $current = $this->currentCommit();
        $latest = $this->latestCommit();

        return $current !== null && $latest !== null && $current !== $latest['sha'];
    }

    /**
     * Cache-only variant for the admin chrome, so rendering a page never waits on GitHub.
     */
    public function updateAvailableFromCache(): bool
    {
        $latest = Cache::get('dotbio-latest-commit');

        if (! is_array($latest)) {
            return false;
        }

        return $this->currentCommit() === $latest['sha'] ? false : $this->isEnabled();
    }

    /**
     * @return array{state: string, step: string, started_at: ?string, finished_at: ?string, error: ?string, from: ?string, to: ?string, backup: ?string}
     */
    public function status(): array
    {
        $default = [
            'state' => self::STATE_IDLE,
            'step' => '',
            'steps' => [],
            'started_at' => null,
            'finished_at' => null,
            'error' => null,
            'from' => null,
            'to' => null,
            'backup' => null,
        ];

        if (! is_file($this->statusPath())) {
            return $default;
        }

        $decoded = json_decode((string) file_get_contents($this->statusPath()), true);

        if (! is_array($decoded)) {
            return $default;
        }

        $status = array_merge($default, $decoded);

        if ($status['state'] === self::STATE_QUEUED && $this->olderThan($status, self::NEVER_STARTED_AFTER_SECONDS)) {
            $status['state'] = self::STATE_FAILED;
            $status['error'] = 'The background process never started. Check that PHP can spawn processes and that the web user can run php, git, composer and npm.';
        }

        if ($status['state'] === self::STATE_RUNNING && $this->olderThan($status, self::STALE_AFTER_SECONDS)) {
            $status['state'] = self::STATE_FAILED;
            $status['error'] = 'The update process stopped responding.';
        }

        return $status;
    }

    public function isRunning(): bool
    {
        return in_array($this->status()['state'], [self::STATE_QUEUED, self::STATE_RUNNING], true);
    }

    public function isExecuting(): bool
    {
        return $this->status()['state'] === self::STATE_RUNNING;
    }

    public function log(int $lines = 200): string
    {
        if (! is_file($this->logPath())) {
            return '';
        }

        $content = (string) file_get_contents($this->logPath());

        return implode("\n", array_slice(explode("\n", $content), -$lines));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function writeStatus(array $attributes): void
    {
        $status = array_merge($this->status(), $attributes);

        file_put_contents($this->statusPath(), json_encode($status, JSON_PRETTY_PRINT), LOCK_EX);
    }

    /**
     * Mark the run as started from the web request itself, so the page that
     * follows the redirect already shows progress instead of racing the
     * background process for the first status write.
     */
    public function markQueued(string $step): void
    {
        $this->resetLog();
        $this->writeStatus([
            'state' => self::STATE_QUEUED,
            'step' => $step,
            'steps' => [],
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'error' => null,
        ]);
    }

    /**
     * @param  array<int, string>  $labels
     */
    public function initialiseSteps(array $labels): void
    {
        $this->writeStatus([
            'steps' => array_map(fn (string $label): array => [
                'label' => $label,
                'state' => 'pending',
            ], $labels),
        ]);
    }

    public function markStep(string $label, string $state): void
    {
        $steps = $this->status()['steps'];

        foreach ($steps as $index => $step) {
            if ($step['label'] === $label) {
                $steps[$index]['state'] = $state;
            }
        }

        $this->writeStatus(['steps' => $steps, 'step' => $label]);
    }

    public function appendLog(string $line): void
    {
        file_put_contents($this->logPath(), rtrim($line)."\n", FILE_APPEND | LOCK_EX);
    }

    public function resetLog(): void
    {
        file_put_contents($this->logPath(), '', LOCK_EX);
    }

    /**
     * Launch the update command detached so it survives the current request.
     */
    public function dispatchUpdate(string $command): void
    {
        $artisan = escapeshellarg(base_path('artisan'));
        $php = escapeshellarg(PHP_BINARY);

        if (PHP_OS_FAMILY === 'Windows') {
            Process::path(base_path())->run(sprintf('start /B %s %s %s', $php, $artisan, $command));

            return;
        }

        // nohup keeps the update alive once PHP-FPM tears down the request that started it.
        Process::path(base_path())->run(['sh', '-c', sprintf('nohup %s %s %s > /dev/null 2>&1 &', $php, $artisan, $command)]);
    }

    public function statusPath(): string
    {
        return storage_path('app/dotbio-update-status.json');
    }

    public function logPath(): string
    {
        return storage_path('logs/dotbio-update.log');
    }

    public function backupDirectory(): string
    {
        return storage_path('app/backups');
    }

    /**
     * @param  array{started_at: ?string}  $status
     */
    private function olderThan(array $status, int $seconds): bool
    {
        if (! $status['started_at']) {
            return true;
        }

        return strtotime($status['started_at']) < time() - $seconds;
    }
}
