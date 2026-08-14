<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Analytic;
use App\Models\Link;
use App\Models\Profile;
use GeoIp2\Database\Reader;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ProfileController extends Controller
{
    private const GEOIP_CACHE_DAYS = 7;

    public function show(): View|RedirectResponse
    {
        $profile = Profile::with([
            'user',
            'links' => fn (HasMany $links) => $links->where('is_visible', true)->orderBy('sort_order'),
        ])->first();

        if (! $profile) {
            return redirect()->route('login');
        }

        $this->syncDiscordAvatar($profile);

        $profile->increment('views_count');
        $this->recordAnalytics($profile->id, 'views');

        return view('profile', [
            'profile' => $profile,
            'initialDiscordStatus' => $profile->custom_discord_status ?? 'offline',
            'initialDiscordActivity' => $profile->custom_status_text ?? '',
        ]);
    }

    public function trackClick(Link $link): JsonResponse
    {
        $link->increment('clicks_count');
        $this->recordAnalytics($link->profile_id, 'clicks');

        return response()->json(['success' => true]);
    }

    private function syncDiscordAvatar(Profile $profile): void
    {
        $discordAvatar = $profile->user?->avatar;

        if (! $discordAvatar) {
            return;
        }

        if ($profile->avatar_url && ! str_contains($profile->avatar_url, 'dicebear')) {
            return;
        }

        $profile->update(['avatar_url' => $discordAvatar]);
    }

    private function recordAnalytics(int $profileId, string $column, int $amount = 1): void
    {
        $analytic = Analytic::firstOrCreate(
            ['profile_id' => $profileId, 'date' => now()->toDateString()],
            ['views' => 0, 'clicks' => 0],
        );

        $analytic->increment($column, $amount);

        if ($column !== 'views') {
            return;
        }

        $countryCode = $this->resolveCountryCode();

        if (! $countryCode) {
            return;
        }

        $countries = $analytic->countries ?? [];
        $countries[$countryCode] = ($countries[$countryCode] ?? 0) + $amount;

        $analytic->update(['countries' => $countries]);
    }

    private function resolveCountryCode(): ?string
    {
        $ipAddress = request()->ip();

        if (! $ipAddress || in_array($ipAddress, ['127.0.0.1', '::1'], true)) {
            return null;
        }

        return Cache::remember(
            'geoip-country:'.$ipAddress,
            now()->addDays(self::GEOIP_CACHE_DAYS),
            fn (): ?string => $this->lookupCountryCode($ipAddress),
        );
    }

    private function lookupCountryCode(string $ipAddress): ?string
    {
        $databasePath = storage_path('app/geoip/GeoLite2-Country.mmdb');

        if (! file_exists($databasePath)) {
            return null;
        }

        try {
            return (new Reader($databasePath))->country($ipAddress)->country->isoCode;
        } catch (Throwable) {
            return null;
        }
    }
}
