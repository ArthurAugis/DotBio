<?php

declare(strict_types=1);

use App\Services\UpdateService;
use Illuminate\Support\Facades\Schedule;

Schedule::command('discord:bot')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::call(fn () => app(UpdateService::class)->latestCommit())
    ->hourly()
    ->name('dotbio-update-check')
    ->withoutOverlapping();
