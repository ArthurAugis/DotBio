<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Analytic;
use App\Models\Link;
use App\Models\Profile;
use GeoIp2\Database\Reader;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
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

        if ($this->shouldCountView($profile)) {
            $profile->increment('views_count');
            $this->recordAnalytics($profile->id, 'views');
        }

        return view('profile', [
            'profile' => $profile,
            'initialDiscordStatus' => $profile->custom_discord_status ?? 'offline',
            'initialDiscordActivity' => $profile->custom_status_text ?? '',
        ]);
    }

    public function trackClick(Link $link): JsonResponse
    {
        if (auth()->check()) {
            return response()->json(['success' => true]);
        }

        $link->increment('clicks_count');
        $this->recordAnalytics($link->profile_id, 'clicks');

        return response()->json(['success' => true]);
    }

    private function shouldCountView(Profile $profile): bool
    {
        if (auth()->check()) {
            return false;
        }

        $visitor = sha1(implode('|', [
            request()->ip(),
            (string) request()->userAgent(),
        ]));

        return Cache::add(
            'profile-view:'.$profile->id.':'.$visitor,
            true,
            now()->endOfDay(),
        );
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
        try {
            $analytic = Analytic::firstOrCreate(
                ['profile_id' => $profileId, 'date' => now()->toDateString()],
                ['views' => 0, 'clicks' => 0],
            );
        } catch (UniqueConstraintViolationException) {
            $analytic = Analytic::where('profile_id', $profileId)
                ->whereDate('date', now()->toDateString())
                ->firstOrFail();
        }

        $analytic->increment($column, $amount);

        if ($column !== 'views') {
            return;
        }

        $countryCode = $this->resolveCountryCode();

        if ($countryCode) {
            $countries = $analytic->countries ?? [];
            $countries[$countryCode] = ($countries[$countryCode] ?? 0) + $amount;
            $analytic->update(['countries' => $countries]);
        }

        $referrerSource = $this->resolveReferrerSource();
        $referrers = $analytic->referrers ?? [];
        $referrers[$referrerSource] = ($referrers[$referrerSource] ?? 0) + $amount;

        $deviceType = $this->resolveDeviceType();
        $devices = $analytic->devices ?? [];
        $devices[$deviceType] = ($devices[$deviceType] ?? 0) + $amount;

        $analytic->update(['referrers' => $referrers, 'devices' => $devices]);
    }

    private function resolveDeviceType(): string
    {
        $userAgent = request()->userAgent();

        if (! $userAgent) {
            return 'desktop';
        }

        if (preg_match('/iPad|Tablet|PlayBook|Silk|(Android(?!.*Mobile))/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/Mobile|iPhone|iPod|Android|BlackBerry|Opera Mini|IEMobile/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function resolveReferrerSource(): string
    {
        $referer = request()->headers->get('referer');

        if (! $referer) {
            return 'Direct';
        }

        $host = parse_url($referer, PHP_URL_HOST);

        if (! $host || $host === request()->getHost()) {
            return 'Direct';
        }

        return preg_replace('/^www\./', '', strtolower($host));
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
