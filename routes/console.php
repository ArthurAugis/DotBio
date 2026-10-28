<?php

declare(strict_types=1);

use App\Services\UpdateService;
use Illuminate\Support\Facades\Schedule;

if (config('dotbio.run_discord_bot_from_scheduler')) {
    Schedule::command('discord:bot')
        ->everyMinute()
        ->withoutOverlapping(60)
        ->runInBackground();
}

Schedule::command('geoip:update --if-missing')->hourly()->withoutOverlapping();
Schedule::command('geoip:update')->monthlyOn(3)->withoutOverlapping();

Schedule::call(fn () => app(UpdateService::class)->latestCommit())
    ->hourly()
    ->name('dotbio-update-check')
    ->withoutOverlapping();
