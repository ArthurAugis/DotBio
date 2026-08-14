<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CustomizeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Api\DiscordStatusController;
use App\Http\Controllers\Auth\DiscordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/links/{link}/click', [ProfileController::class, 'trackClick'])->name('links.click');

Route::get('/api/discord-status/{discordId}', [DiscordStatusController::class, 'getStatus'])->name('api.discord.status');
Route::post('/api/discord/update-status', [DiscordStatusController::class, 'updateStatus'])->name('api.discord.update_status');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/auth/discord', [DiscordController::class, 'redirect'])->name('auth.discord');
Route::get('/auth/discord/callback', [DiscordController::class, 'callback']);

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    Route::get('/customize', [CustomizeController::class, 'edit'])->name('customize');
    Route::post('/customize', [CustomizeController::class, 'update'])->name('customize.update');

    Route::get('/seo', [SeoController::class, 'edit'])->name('seo');
    Route::post('/seo', [SeoController::class, 'update'])->name('seo.update');

    Route::get('/links', [LinkController::class, 'index'])->name('links.index');
    Route::post('/links', [LinkController::class, 'store'])->name('links.store');
    Route::put('/links/{link}', [LinkController::class, 'update'])->name('links.update');
    Route::delete('/links/{link}', [LinkController::class, 'destroy'])->name('links.destroy');
    Route::post('/links/reorder', [LinkController::class, 'reorder'])->name('links.reorder');
});
