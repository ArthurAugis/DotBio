<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Analytic;
use App\Models\Profile;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Locale;

class AnalyticsController extends Controller
{
    /**
     * Country names used when the intl extension is unavailable.
     */
    private const COUNTRY_NAME_FALLBACK = [
        'BE' => 'Belgium',
        'CA' => 'Canada',
        'CH' => 'Switzerland',
        'DE' => 'Germany',
        'ES' => 'Spain',
        'FR' => 'France',
        'GB' => 'United Kingdom',
        'IT' => 'Italy',
        'US' => 'United States',
    ];

    public function index(): View
    {
        $profile = Profile::with('links')->firstOrFail();

        $threeDays = $this->dateRange(2);
        $sevenDays = $this->dateRange(6);
        $thirtyDays = $this->dateRange(28, 7);

        $countryViews = $this->countryViews($profile);

        return view('admin.analytics', [
            'profile' => $profile,
            'dates3Days' => $this->formattedDates($threeDays),
            'views3Days' => $this->viewsForDates($profile, $threeDays),
            'dates7Days' => $this->formattedDates($sevenDays),
            'views7Days' => $this->viewsForDates($profile, $sevenDays),
            'dates30Days' => $this->formattedDates($thirtyDays),
            'views30Days' => $this->viewsForDates($profile, $thirtyDays),
            'countryViews' => $countryViews,
            'topCountries' => $this->topCountries($countryViews),
        ]);
    }

    /**
     * @return array<int, Carbon>
     */
    private function dateRange(int $daysAgo, int $step = 1): array
    {
        $dates = [];

        for ($offset = $daysAgo; $offset >= 0; $offset -= $step) {
            $dates[] = now()->subDays($offset);
        }

        return $dates;
    }

    /**
     * @param  array<int, Carbon>  $dates
     * @return array<int, string>
     */
    private function formattedDates(array $dates): array
    {
        return array_map(fn (Carbon $date): string => $date->format('j M'), $dates);
    }

    /**
     * @param  array<int, Carbon>  $dates
     * @return array<int, int>
     */
    private function viewsForDates(Profile $profile, array $dates): array
    {
        return array_map(
            fn (Carbon $date): int => (int) Analytic::where('profile_id', $profile->id)
                ->whereDate('date', $date->toDateString())
                ->value('views'),
            $dates,
        );
    }

    /**
     * @return array<string, int>
     */
    private function countryViews(Profile $profile): array
    {
        return Analytic::where('profile_id', $profile->id)
            ->pluck('countries')
            ->filter()
            ->reduce(function (array $totals, array $countries): array {
                foreach ($countries as $code => $views) {
                    $code = strtoupper((string) $code);

                    if (preg_match('/^[A-Z]{2}$/', $code)) {
                        $totals[$code] = ($totals[$code] ?? 0) + (int) $views;
                    }
                }

                return $totals;
            }, []);
    }

    /**
     * @param  array<string, int>  $countryViews
     * @return array<int, array{code: string, name: string, views: int}>
     */
    private function topCountries(array $countryViews): array
    {
        arsort($countryViews);

        return array_map(fn (string $code, int $views): array => [
            'code' => $code,
            'name' => $this->countryName($code),
            'views' => $views,
        ], array_keys($countryViews), array_values($countryViews));
    }

    private function countryName(string $countryCode): string
    {
        if (class_exists(Locale::class)) {
            $name = Locale::getDisplayRegion('en_'.$countryCode, 'en');

            if ($name !== '') {
                return $name;
            }
        }

        return self::COUNTRY_NAME_FALLBACK[$countryCode] ?? $countryCode;
    }
}
