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
            $this->error('Self-update is disabled or this install is not a git checkout.');

            return self::FAILURE;
        }

        if ($updates->isRunning() && ! $this->option('force')) {
            $this->error('An update is already running.');

            return self::FAILURE;
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

        try {
            $backup = $this->backupDatabase($updates);
            $updates->writeStatus(['backup' => $backup]);

            $this->runStep($updates, 'Enabling maintenance mode', [PHP_BINARY, base_path('artisan'), 'down']);
            $this->runStep($updates, 'Pulling latest code', ['git', 'pull', '--ff-only', (string) config('dotbio.remote'), (string) config('dotbio.branch')]);
            $this->runStep($updates, 'Installing PHP dependencies', ['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress']);
            $this->runStep($updates, 'Installing front-end dependencies', ['npm', 'ci', '--no-audit', '--no-fund']);
            $this->runStep($updates, 'Building assets', ['npm', 'run', 'build']);
            $this->runStep($updates, 'Running migrations', [PHP_BINARY, base_path('artisan'), 'migrate', '--force']);
            $this->runStep($updates, 'Clearing caches', [PHP_BINARY, base_path('artisan'), 'optimize:clear']);

            $updates->writeStatus([
                'state' => UpdateService::STATE_DONE,
                'step' => 'Update complete',
                'finished_at' => now()->toIso8601String(),
                'to' => $updates->currentCommit(),
            ]);

            $updates->appendLog('Update complete.');
        } catch (Throwable $exception) {
            $updates->writeStatus([
                'state' => UpdateService::STATE_FAILED,
                'finished_at' => now()->toIso8601String(),
                'error' => $exception->getMessage(),
            ]);

            $updates->appendLog('FAILED: '.$exception->getMessage());
        } finally {
            Process::path(base_path())->timeout(60)->run([PHP_BINARY, base_path('artisan'), 'up']);
        }

        return $updates->status()['state'] === UpdateService::STATE_DONE ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  array<int, string>  $command
     */
    private function runStep(UpdateService $updates, string $label, array $command): void
    {
        $updates->writeStatus(['step' => $label]);
        $updates->appendLog('');
        $updates->appendLog('$ '.implode(' ', $command));

        $result = Process::path(base_path())
            ->timeout(self::STEP_TIMEOUT)
            ->run($command, function (string $type, string $output) use ($updates): void {
                $updates->appendLog($output);
            });

        if ($result->failed()) {
            throw new RuntimeException($label.' failed (exit code '.$result->exitCode().').');
        }
    }

    private function backupDatabase(UpdateService $updates): ?string
    {
        $updates->writeStatus(['step' => 'Backing up the database']);

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
