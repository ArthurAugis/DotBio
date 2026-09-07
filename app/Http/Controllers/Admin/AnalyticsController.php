<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Analytic;
use App\Models\Profile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
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

    /**
     * @var array<string, array{span: int, step: int, label: string}>
     */
    private const RANGES = [
        '3days' => ['span' => 3, 'step' => 1, 'label' => 'Last 3 days'],
        '7days' => ['span' => 7, 'step' => 1, 'label' => 'Last 7 days'],
        '30days' => ['span' => 29, 'step' => 7, 'label' => 'Last 30 days'],
        '3months' => ['span' => 90, 'step' => 7, 'label' => 'Last 3 months'],
        '6months' => ['span' => 180, 'step' => 14, 'label' => 'Last 6 months'],
        '1year' => ['span' => 364, 'step' => 28, 'label' => 'Last year'],
        'alltime' => ['span' => 0, 'step' => 0, 'label' => 'All time'],
    ];

    public function index(Request $request): View
    {
        $profile = Profile::with('links')->firstOrFail();

        $range = array_key_exists((string) $request->query('range'), self::RANGES)
            ? (string) $request->query('range')
            : '3days';

        $isAllTime = $range === 'alltime';
        $offset = $isAllTime ? 0 : max(0, (int) $request->query('offset', 0));

        if ($isAllTime) {
            $firstDate = Analytic::where('profile_id', $profile->id)->min('date');
            $start = $firstDate ? Carbon::parse($firstDate)->startOfDay() : now()->startOfDay();
            $end = now()->startOfDay();
            $totalDays = max(1, $start->diffInDays($end) + 1);
            $step = $totalDays <= 30 ? 1 : ($totalDays <= 90 ? 7 : ($totalDays <= 365 ? 14 : 30));
            $previousStart = null;
            $previousEnd = null;
        } else {
            $span = self::RANGES[$range]['span'];
            $step = self::RANGES[$range]['step'];
            $end = now()->startOfDay()->subDays($offset * $span);
            $start = $end->copy()->subDays($span - 1);
            $previousEnd = $start->copy()->subDay();
            $previousStart = $previousEnd->copy()->subDays($span - 1);
        }

        $chartDates = $this->dateSequence($start, $end, $step);

        $viewsSeries = $this->sumForDates($profile, $chartDates, 'views');
        $viewsTotal = $this->rangeSum($profile, $start, $end, 'views');
        $clicksTotal = $this->rangeSum($profile, $start, $end, 'clicks');

        $previousViewsTotal = $previousStart ? $this->rangeSum($profile, $previousStart, $previousEnd, 'views') : null;
        $viewsDelta = $previousViewsTotal === null ? null : $viewsTotal - $previousViewsTotal;

        $countryViews = $this->countryViews($profile, $start, $end);
        $referrerViews = $this->referrerViews($profile, $start, $end);
        $deviceViews = $this->deviceViews($profile, $start, $end);

        return view('admin.analytics', [
            'profile' => $profile,
            'ranges' => self::RANGES,
            'range' => $range,
            'offset' => $offset,
            'isAllTime' => $isAllTime,
            'canGoToPreviousPeriod' => ! $isAllTime,
            'canGoToNextPeriod' => ! $isAllTime && $offset > 0,
            'periodLabel' => $start->isSameDay($end)
                ? $start->format('j M Y')
                : $start->format('j M Y').' - '.$end->format('j M Y'),
            'dates' => $this->formattedDates($chartDates),
            'views' => $viewsSeries,
            'viewsTotal' => $viewsTotal,
            'clicksTotal' => $clicksTotal,
            'viewsDelta' => $viewsDelta,
            'avgDailyViews' => round($viewsTotal / max(1, $start->diffInDays($end) + 1), 1),
            'countryViews' => $countryViews,
            'topCountries' => $this->topCountries($countryViews),
            'topReferrers' => $this->topReferrers($referrerViews),
            'deviceViews' => $deviceViews,
            'deviceTotal' => array_sum($deviceViews),
        ]);
    }

    /**
     * @return array<int, Carbon>
     */
    private function dateSequence(Carbon $start, Carbon $end, int $step): array
    {
        $dates = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dates[] = $cursor->copy();
            $cursor->addDays($step);
        }

        $last = end($dates);

        if ($last === false || ! $last->isSameDay($end)) {
            $dates[] = $end->copy();
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
    private function sumForDates(Profile $profile, array $dates, string $column): array
    {
        return array_map(
            fn (Carbon $date): int => (int) Analytic::where('profile_id', $profile->id)
                ->whereDate('date', $date->toDateString())
                ->value($column),
            $dates,
        );
    }

    private function rangeSum(Profile $profile, Carbon $start, Carbon $end, string $column): int
    {
        return (int) Analytic::where('profile_id', $profile->id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->sum($column);
    }

    /**
     * @return array<string, int>
     */
    private function countryViews(Profile $profile, Carbon $start, Carbon $end): array
    {
        return Analytic::where('profile_id', $profile->id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
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
     * @return array<string, int>
     */
    private function referrerViews(Profile $profile, Carbon $start, Carbon $end): array
    {
        return Analytic::where('profile_id', $profile->id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->pluck('referrers')
            ->filter()
            ->reduce(function (array $totals, array $referrers): array {
                foreach ($referrers as $source => $views) {
                    $totals[$source] = ($totals[$source] ?? 0) + (int) $views;
                }

                return $totals;
            }, []);
    }

    /**
     * @return array{desktop: int, mobile: int, tablet: int}
     */
    private function deviceViews(Profile $profile, Carbon $start, Carbon $end): array
    {
        return Analytic::where('profile_id', $profile->id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->pluck('devices')
            ->filter()
            ->reduce(function (array $totals, array $devices): array {
                foreach ($devices as $type => $views) {
                    if (array_key_exists($type, $totals)) {
                        $totals[$type] += (int) $views;
                    }
                }

                return $totals;
            }, ['desktop' => 0, 'mobile' => 0, 'tablet' => 0]);
    }

    /**
     * @param  array<string, int>  $referrerViews
     * @return array<int, array{source: string, views: int}>
     */
    private function topReferrers(array $referrerViews): array
    {
        arsort($referrerViews);

        return array_map(fn (string $source, int $views): array => [
            'source' => $source === 'Direct' ? 'DotBio Direct' : $source,
            'views' => $views,
        ], array_keys($referrerViews), array_values($referrerViews));
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
