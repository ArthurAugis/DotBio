<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Profile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $loginMethod = Profile::ownerLoginMethod();

        return view('auth.login', [
            'loginMethod' => $loginMethod,
            'oauthProviders' => array_values(array_filter(
                OAuthController::configuredProviders(),
                fn (string $provider): bool => in_array($loginMethod, [null, $provider], true),
            )),
            'hasProfile' => Profile::exists(),
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (Profile::ownerLoginMethod() !== 'password'
            || ! Auth::attempt($request->validated(), $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
