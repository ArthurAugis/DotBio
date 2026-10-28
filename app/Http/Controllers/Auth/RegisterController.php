<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Profile::ownerLoginMethod() !== null) {
            return redirect()->route('login');
        }

        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('username'),
            'username' => $request->validated('username'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $profile = Profile::first() ?? new Profile;
        $profile->fill([
            'user_id' => $user->id,
            'display_name' => $user->username,
            'use_discord_avatar' => false,
        ]);
        $profile->avatar_url = $this->storePublicFile($request, 'avatar_file', 'uploads/avatars') ?? $profile->avatar_url;
        $profile->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}
