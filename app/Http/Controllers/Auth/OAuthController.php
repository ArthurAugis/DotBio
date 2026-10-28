<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class OAuthController extends Controller
{
    public const PROVIDERS = ['discord', 'google', 'github', 'twitch'];

    /**
     * @return list<string>
     */
    public static function configuredProviders(): array
    {
        return array_values(array_filter(
            self::PROVIDERS,
            fn (string $provider): bool => filled(config("services.{$provider}.client_secret")),
        ));
    }

    public function redirect(string $provider): RedirectResponse
    {
        return $this->driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        try {
            $account = $this->driver($provider)->user();
        } catch (Throwable $exception) {
            Log::error('OAuth authentication failed.', ['provider' => $provider, 'exception' => $exception]);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Authentication failed. Please try again.']);
        }

        $column = $provider.'_id';
        $accountId = (string) $account->getId();
        $page = Profile::with('user')->first();
        $owner = $page?->user;

        if ($owner?->loginMethod() !== null && $owner->{$column} !== $accountId) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'This account is not allowed to manage this DotBio.']);
        }

        $nickname = $account->getNickname() ?? $account->getName();
        $email = $account->getEmail() ?? $accountId.'@'.$provider.'.user';

        $user = User::where($column, $accountId)->first()
            ?? User::where('email', $email)->first()
            ?? new User;

        $usernameTaken = User::where('username', $nickname)->where('id', '!=', $user->id ?? 0)->exists();

        $user->fill([
            $column => $accountId,
            'name' => $account->getName() ?? $nickname ?? $email,
            'email' => $email,
            'avatar' => $account->getAvatar(),
            'username' => $usernameTaken ? $user->username : $nickname,
        ])->save();

        $profile = $page ?? Profile::create(['display_name' => $nickname ?? $user->name]);

        $profile->update(['user_id' => $user->id] + ($provider === 'discord'
            ? [
                'discord_id' => $accountId,
                'avatar_url' => $account->getAvatar() ?: $profile->avatar_url,
                'use_discord_avatar' => true,
            ]
            : [
                'avatar_url' => $profile->avatar_url ?: $account->getAvatar(),
                'use_discord_avatar' => false,
            ]));

        Auth::login($user, true);

        return redirect()->route('admin.dashboard');
    }

    private function driver(string $provider): Provider
    {
        $driver = Socialite::driver($provider);

        if (app()->isLocal()) {
            $driver->setHttpClient(new Client(['verify' => false]));
        }

        return $driver;
    }
}
