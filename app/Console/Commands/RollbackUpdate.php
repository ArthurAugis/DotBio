<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\UpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

class RollbackUpdate extends Command
{
    protected $signature = 'dotbio:rollback';

    protected $description = 'Restore the code and database captured before the last DotBio update';

    private const STEP_TIMEOUT = 900;

    public function handle(UpdateService $updates): int
    {
        $status = $updates->status();
        $commit = (string) ($status['from'] ?? '');

        if (! preg_match('/^[0-9a-f]{40}$/', $commit)) {
            $this->error('No recorded commit to roll back to.');

            return self::FAILURE;
        }

        $updates->resetLog();
        $updates->writeStatus([
            'state' => UpdateService::STATE_RUNNING,
            'step' => 'Rolling back',
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'error' => null,
        ]);

        try {
            $this->runStep($updates, 'Enabling maintenance mode', [PHP_BINARY, base_path('artisan'), 'down']);
            $this->runStep($updates, 'Restoring code', ['git', 'reset', '--hard', $commit]);
            $this->restoreDatabase($updates, (string) ($status['backup'] ?? ''));
            $this->runStep($updates, 'Installing PHP dependencies', ['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress']);
            $this->runStep($updates, 'Building assets', ['npm', 'run', 'build']);
            $this->runStep($updates, 'Clearing caches', [PHP_BINARY, base_path('artisan'), 'optimize:clear']);

            $updates->writeStatus([
                'state' => UpdateService::STATE_DONE,
                'step' => 'Rollback complete',
                'finished_at' => now()->toIso8601String(),
                'to' => $commit,
            ]);

            $updates->appendLog('Rollback complete.');
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

    private function restoreDatabase(UpdateService $updates, string $backup): void
    {
        $updates->writeStatus(['step' => 'Restoring the database']);

        if ($backup === '' || ! is_file($backup)) {
            $updates->appendLog('No database backup recorded, leaving the current database untouched.');

            return;
        }

        copy($backup, (string) config('database.connections.sqlite.database'));
        $updates->appendLog('Database restored from '.$backup);
    }
}
