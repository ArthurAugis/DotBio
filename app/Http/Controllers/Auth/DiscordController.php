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

class DiscordController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return $this->driver()->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $discordUser = $this->driver()->user();
        } catch (Throwable $exception) {
            Log::error('Discord OAuth failed.', ['exception' => $exception]);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Discord authentication failed. Please try again.']);
        }

        $user = User::updateOrCreate(
            ['discord_id' => $discordUser->getId()],
            [
                'name' => $discordUser->getName() ?? $discordUser->getNickname(),
                'email' => $discordUser->getEmail() ?? $discordUser->getId().'@discord.user',
                'avatar' => $discordUser->getAvatar(),
                'username' => $discordUser->getNickname(),
            ],
        );

        $profile = Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $discordUser->getNickname() ?? $discordUser->getName() ?? $user->name,
                'discord_id' => $discordUser->getId(),
            ],
        );

        $profile->update([
            'discord_id' => $discordUser->getId(),
            'avatar_url' => $discordUser->getAvatar() ?: $profile->avatar_url,
            'use_discord_avatar' => true,
        ]);

        Auth::login($user, true);

        return redirect()->route('admin.dashboard');
    }

    private function driver(): Provider
    {
        $driver = Socialite::driver('discord');

        if (app()->isLocal()) {
            $driver->setHttpClient(new Client(['verify' => false]));
        }

        return $driver;
    }
}
