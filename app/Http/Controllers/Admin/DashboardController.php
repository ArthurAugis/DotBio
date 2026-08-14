<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        return view('admin.dashboard', compact('profile'));
    }
}
