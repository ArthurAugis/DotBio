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

        $updates->writeStatus([
            'state' => UpdateService::STATE_RUNNING,
            'step' => 'Rolling back',
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'error' => null,
        ]);

        $artisan = base_path('artisan');

        $steps = [
            'Enabling maintenance mode' => fn () => $this->runProcess($updates, [PHP_BINARY, $artisan, 'down']),
            'Restoring code' => fn () => $this->runProcess($updates, ['git', 'reset', '--hard', $commit]),
            'Restoring the database' => fn () => $this->restoreDatabase($updates, (string) ($status['backup'] ?? '')),
            'Installing PHP dependencies' => fn () => $this->runProcess($updates, ['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress']),
            'Building assets' => fn () => $this->runProcess($updates, ['npm', 'run', 'build']),
            'Clearing caches' => fn () => $this->runProcess($updates, [PHP_BINARY, $artisan, 'optimize:clear']),
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
                'step' => 'Rollback complete',
                'finished_at' => now()->toIso8601String(),
                'to' => $commit,
            ]);

            $updates->appendLog('Rollback complete.');
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
            Process::path(base_path())->timeout(60)->run([PHP_BINARY, base_path('artisan'), 'up']);
        }

        return $updates->status()['state'] === UpdateService::STATE_DONE ? self::SUCCESS : self::FAILURE;
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
            $lines = array_values(array_filter(array_map('trim', explode("\n", $reason))));

            throw new RuntimeException(sprintf(
                '%s exited with code %d. %s',
                basename($command[0]),
                $result->exitCode(),
                $lines === [] ? 'No output was captured.' : (string) end($lines),
            ));
        }
    }

    private function restoreDatabase(UpdateService $updates, string $backup): void
    {
        if ($backup === '' || ! is_file($backup)) {
            $updates->appendLog('No database backup recorded, leaving the current database untouched.');

            return;
        }

        copy($backup, (string) config('database.connections.sqlite.database'));
        $updates->appendLog('Database restored from '.$backup);
    }
}
