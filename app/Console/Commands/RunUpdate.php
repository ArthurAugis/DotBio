<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\UpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

class RunUpdate extends Command
{
    protected $signature = 'dotbio:update {--force : Run even if another update is marked as running}';

    protected $description = 'Pull the latest DotBio release, install dependencies, build assets and migrate';

    private const STEP_TIMEOUT = 900;

    public function handle(UpdateService $updates): int
    {
        if (! $updates->isEnabled()) {
            return $this->abort($updates, 'Self-update is disabled or this install is not a git checkout.');
        }

        if ($updates->isExecuting() && ! $this->option('force')) {
            return $this->abort($updates, 'An update is already running.');
        }

        $fromCommit = $updates->currentCommit();

        $updates->resetLog();
        $updates->writeStatus([
            'state' => UpdateService::STATE_RUNNING,
            'step' => 'Starting',
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'error' => null,
            'from' => $fromCommit,
            'to' => null,
            'backup' => null,
        ]);

        $artisan = base_path('artisan');
        $php = $updates->phpBinary();

        $steps = [
            'Checking prerequisites' => fn () => $this->checkPrerequisites($updates),
            'Backing up the database' => function () use ($updates): void {
                $updates->writeStatus(['backup' => $this->backupDatabase($updates)]);
            },
            'Enabling maintenance mode' => fn () => $this->runProcess($updates, [$php, $artisan, 'down']),
            'Pulling latest code' => fn () => $this->runProcess($updates, ['git', 'pull', '--ff-only', (string) config('dotbio.remote'), (string) config('dotbio.branch')]),
            'Installing PHP dependencies' => fn () => $this->runProcess($updates, ['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress']),
            'Installing front-end dependencies' => fn () => $this->runProcess($updates, ['npm', 'ci', '--no-audit', '--no-fund']),
            'Building assets' => fn () => $this->runProcess($updates, ['npm', 'run', 'build']),
            'Running migrations' => fn () => $this->runProcess($updates, [$php, $artisan, 'migrate', '--force']),
            'Clearing caches' => fn () => $this->runProcess($updates, [$php, $artisan, 'optimize:clear']),
        ];

        $updates->initialiseSteps(array_keys($steps));
        $currentStep = '';

        try {
            foreach ($steps as $label => $action) {
                $currentStep = $label;
                $updates->markStep($label, 'running');
                $updates->appendLog('');
                $updates->appendLog('=== '.$label.' ===');

                $action();

                $updates->markStep($label, 'done');
            }

            $updates->writeStatus([
                'state' => UpdateService::STATE_DONE,
                'step' => 'Update complete',
                'finished_at' => now()->toIso8601String(),
                'to' => $updates->currentCommit(),
            ]);

            $updates->appendLog('Update complete.');
        } catch (Throwable $exception) {
            if ($currentStep !== '') {
                $updates->markStep($currentStep, 'failed');
            }

            $updates->writeStatus([
                'state' => UpdateService::STATE_FAILED,
                'finished_at' => now()->toIso8601String(),
                'error' => $exception->getMessage(),
            ]);

            $updates->appendLog('FAILED: '.$exception->getMessage());
        } finally {
            Process::path(base_path())->timeout(60)->run([$updates->phpBinary(), base_path('artisan'), 'up']);
        }

        return $updates->status()['state'] === UpdateService::STATE_DONE ? self::SUCCESS : self::FAILURE;
    }

    private function checkPrerequisites(UpdateService $updates): void
    {
        $problems = [];

        foreach (['git', 'composer', 'npm'] as $binary) {
            if (Process::path(base_path())->run(['sh', '-c', 'command -v '.$binary])->failed()) {
                $problems[] = sprintf('%s is not in the PATH of the %s user.', $binary, get_current_user());
            }
        }

        foreach (['.git', 'storage', 'bootstrap/cache'] as $path) {
            if (! is_writable(base_path($path))) {
                $problems[] = sprintf('%s is not writable by the %s user.', $path, get_current_user());
            }
        }

        if ($problems === []) {
            $updates->appendLog('All prerequisites met.');

            return;
        }

        foreach ($problems as $problem) {
            $updates->appendLog('- '.$problem);
        }

        throw new RuntimeException(implode(' ', $problems));
    }

    private function abort(UpdateService $updates, string $reason): int
    {
        $this->error($reason);

        $updates->writeStatus([
            'state' => UpdateService::STATE_FAILED,
            'finished_at' => now()->toIso8601String(),
            'error' => $reason,
        ]);

        return self::FAILURE;
    }

    /**
     * @param  array<int, string>  $command
     */
    private function runProcess(UpdateService $updates, array $command): void
    {
        $updates->appendLog('$ '.implode(' ', $command));

        $result = Process::path(base_path())
            ->timeout(self::STEP_TIMEOUT)
            ->run($command, function (string $type, string $output) use ($updates): void {
                $updates->appendLog($output);
            });

        if ($result->failed()) {
            $reason = trim($result->errorOutput()) ?: trim($result->output());

            throw new RuntimeException(sprintf(
                '%s exited with code %d. %s',
                basename($command[0]),
                $result->exitCode(),
                $this->lastMeaningfulLine($reason),
            ));
        }
    }

    private function lastMeaningfulLine(string $output): string
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $output))));

        return $lines === [] ? 'No output was captured.' : (string) end($lines);
    }

    private function backupDatabase(UpdateService $updates): ?string
    {
        if (config('database.default') !== 'sqlite') {
            $updates->appendLog('Database is not SQLite, skipping the automatic backup.');

            return null;
        }

        $source = (string) config('database.connections.sqlite.database');

        if (! is_file($source)) {
            $updates->appendLog('SQLite database not found, skipping the automatic backup.');

            return null;
        }

        if (! is_dir($updates->backupDirectory())) {
            mkdir($updates->backupDirectory(), 0755, true);
        }

        $destination = $updates->backupDirectory().'/database-'.now()->format('Y-m-d_His').'.sqlite';
        copy($source, $destination);

        $updates->appendLog('Database backed up to '.$destination);

        return $destination;
    }
}
