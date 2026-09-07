<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UpdateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class UpdateController extends Controller
{
    public function index(UpdateService $updates): View
    {
        return view('admin.update', [
            'enabled' => $updates->isEnabled(),
            'isGitCheckout' => $updates->isGitCheckout(),
            'repository' => $updates->repository(),
            'branch' => $updates->branch(),
            'currentCommit' => $updates->currentCommit(),
            'latestCommit' => $updates->latestCommit(),
            'pendingCommits' => $updates->pendingCommits(),
            'updateAvailable' => $updates->updateAvailable(),
            'status' => $updates->status(),
        ]);
    }

    public function check(UpdateService $updates): RedirectResponse
    {
        Cache::forget('dotbio-latest-commit');

        $updates->latestCommit();

        return redirect()
            ->route('admin.update')
            ->with('status', $updates->updateAvailable() ? 'An update is available.' : 'DotBio is up to date.');
    }

    public function run(UpdateService $updates): RedirectResponse
    {
        if (! $updates->isEnabled()) {
            return redirect()->route('admin.update')->with('error', 'Self-update is disabled on this install.');
        }

        if ($updates->isRunning()) {
            return redirect()->route('admin.update')->with('error', 'An update is already running.');
        }

        $updates->markQueued('Starting the update');
        $updates->dispatchUpdate('dotbio:update');

        return redirect()->route('admin.update');
    }

    public function rollback(UpdateService $updates): RedirectResponse
    {
        if (! $updates->isEnabled()) {
            return redirect()->route('admin.update')->with('error', 'Self-update is disabled on this install.');
        }

        if ($updates->isRunning()) {
            return redirect()->route('admin.update')->with('error', 'An update is already running.');
        }

        $updates->markQueued('Starting the rollback');
        $updates->dispatchUpdate('dotbio:rollback');

        return redirect()->route('admin.update');
    }

    public function status(UpdateService $updates): JsonResponse
    {
        return response()->json([
            'status' => $updates->status(),
            'log' => $updates->log(),
        ]);
    }
}
