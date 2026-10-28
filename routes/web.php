<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CustomizeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\LinkPageController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Api\DiscordStatusController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/links/{link}/click', [ProfileController::class, 'trackClick'])
    ->middleware('throttle:20,1')
    ->name('links.click');
Route::post('/blocks/{block}/click', [ProfileController::class, 'trackBlockClick'])
    ->middleware('throttle:20,1')
    ->name('blocks.click');

Route::get('/api/discord-status/{discordId}', [DiscordStatusController::class, 'getStatus'])
    ->middleware('throttle:60,1')
    ->name('api.discord.status');
Route::post('/api/discord/update-status', [DiscordStatusController::class, 'updateStatus'])
    ->middleware('throttle:60,1')
    ->name('api.discord.update_status');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1')->name('register.store');

Route::get('/auth/{provider}', [OAuthController::class, 'redirect'])
    ->whereIn('provider', OAuthController::PROVIDERS)
    ->name('auth.redirect');
Route::get('/auth/{provider}/callback', [OAuthController::class, 'callback'])
    ->whereIn('provider', OAuthController::PROVIDERS);

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

    Route::get('/link-page', [LinkPageController::class, 'index'])->name('link-page.index');
    Route::post('/link-page/settings', [LinkPageController::class, 'settings'])->name('link-page.settings');
    Route::post('/link-page/blocks', [LinkPageController::class, 'store'])->name('link-page.store');
    Route::post('/link-page/blocks/reorder', [LinkPageController::class, 'reorder'])->name('link-page.reorder');
    Route::put('/link-page/blocks/{block}', [LinkPageController::class, 'update'])->name('link-page.update');
    Route::patch('/link-page/blocks/{block}/toggle', [LinkPageController::class, 'toggle'])->name('link-page.toggle');
    Route::delete('/link-page/blocks/{block}', [LinkPageController::class, 'destroy'])->name('link-page.destroy');

    Route::get('/update', [UpdateController::class, 'index'])->name('update');
    Route::get('/update/status', [UpdateController::class, 'status'])->name('update.status');
    Route::post('/update/check', [UpdateController::class, 'check'])->name('update.check');
    Route::post('/update/run', [UpdateController::class, 'run'])->name('update.run');
    Route::post('/update/rollback', [UpdateController::class, 'rollback'])->name('update.rollback');
});
