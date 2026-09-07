<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Analytic;
use App\Models\Profile;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $profile = Profile::with('links')->first() ?? Profile::create([
            'user_id' => auth()->id(),
            'display_name' => auth()->user()?->name ?? 'Admin',
        ]);

        $totalViews = (int) Analytic::where('profile_id', $profile->id)->sum('views');

        $currentWeekViews = (int) Analytic::where('profile_id', $profile->id)
            ->whereDate('date', '>=', now()->subDays(6)->toDateString())
            ->whereDate('date', '<=', now()->toDateString())
            ->sum('views');

        return view('admin.dashboard', compact('profile', 'totalViews', 'currentWeekViews'));
    }
}
