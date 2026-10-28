<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Patreon\PatreonExtendSocialite;
use SocialiteProviders\Spotify\SpotifyExtendSocialite;
use SocialiteProviders\Steam\SteamExtendSocialite;
use SocialiteProviders\Twitch\TwitchExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(SocialiteWasCalled::class, DiscordExtendSocialite::class.'@handle');
        Event::listen(SocialiteWasCalled::class, TwitchExtendSocialite::class.'@handle');
        Event::listen(SocialiteWasCalled::class, SteamExtendSocialite::class.'@handle');
        Event::listen(SocialiteWasCalled::class, SpotifyExtendSocialite::class.'@handle');
        Event::listen(SocialiteWasCalled::class, PatreonExtendSocialite::class.'@handle');
    }
}
