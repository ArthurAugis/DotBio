<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Profile;
use App\Models\User;
use App\Services\ProviderAvatars;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class OAuthController extends Controller
{
    public const PROVIDERS = ['discord', 'google', 'github', 'twitch', 'steam', 'spotify', 'patreon'];

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
        $driver = $this->driver($provider);

        if ($provider === 'google') {
            $driver->with(['access_type' => 'offline', 'prompt' => 'consent']);
        }

        return $driver->redirect();
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
        $claimed = $page?->user?->loginMethod() !== null;

        $nickname = $account->getNickname() ?? $account->getName();
        $email = $account->getEmail() ?? $accountId.'@'.$provider.'.user';

        $user = User::where($column, $accountId)->first()
            ?? User::where('email', $email)->first()
            ?? new User;
        $invitation = Invitation::pendingFor($email);

        if ($claimed && $user->loginMethod() === null && $invitation === null) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'This account is not allowed to manage this DotBio.']);
        }

        $usernameTaken = User::where('username', $nickname)->where('id', '!=', $user->id ?? 0)->exists();
        $previousAvatar = $user->avatar;

        $user->fill([
            $column => $accountId,
            'name' => $account->getName() ?? $nickname ?? $email,
            'email' => $email,
            'avatar' => $account->getAvatar(),
            'username' => $usernameTaken ? $user->username : $nickname,
            'last_login_provider' => $provider,
        ]);

        if (in_array($provider, ProviderAvatars::TOKEN_PROVIDERS, true) && $refreshToken = $account->refreshToken ?? null) {
            $user->oauth_tokens = [$provider => $refreshToken] + ($user->oauth_tokens ?? []);
        }

        $user->save();

        $invitation?->delete();

        if (! $claimed || $page->user_id === $user->id) {
            $profile = $page ?? Profile::create(['display_name' => $nickname ?? $user->name]);

            $profile->update(['user_id' => $user->id] + ($provider === 'discord'
                ? [
                    'discord_id' => $accountId,
                    'avatar_url' => $account->getAvatar() ?: $profile->avatar_url,
                    'use_discord_avatar' => true,
                ]
                : [
                    'avatar_url' => in_array($profile->avatar_url, [null, '', $previousAvatar], true)
                        ? ($account->getAvatar() ?: $profile->avatar_url)
                        : $profile->avatar_url,
                    'use_discord_avatar' => false,
                ]));
        }

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
