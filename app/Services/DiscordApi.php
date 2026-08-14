<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class DiscordApi
{
    private const BASE_URL = 'https://discord.com/api/v10';

    private const CDN_URL = 'https://cdn.discordapp.com';

    public function __construct(private readonly ?string $botToken = null) {}

    public static function make(): self
    {
        return new self(config('services.discord.bot_token'));
    }

    /**
     * Fetch the decoration, clan tag and clan badge of a Discord user.
     *
     * @return array{discord_avatar_decoration_url?: string, discord_tag?: string, discord_clan_badge_url?: string}
     */
    public function profileAssets(string $discordId): array
    {
        $user = $this->fetchUser($discordId);

        if ($user === null) {
            return [];
        }

        $assets = [];

        if ($asset = data_get($user, 'avatar_decoration_data.asset')) {
            $assets['discord_avatar_decoration_url'] = self::decorationUrl((string) $asset);
        }

        if ($tag = data_get($user, 'clan.tag')) {
            $assets['discord_tag'] = (string) $tag;
        }

        $guildId = data_get($user, 'clan.identity_guild_id');
        $badge = data_get($user, 'clan.badge');

        if ($guildId && $badge) {
            $assets['discord_clan_badge_url'] = self::clanBadgeUrl((string) $guildId, (string) $badge);
        }

        return $assets;
    }

    public static function decorationUrl(string $asset): string
    {
        return self::CDN_URL.'/avatar-decoration-presets/'.$asset.'.png';
    }

    public static function clanBadgeUrl(string $guildId, string $badgeHash): string
    {
        return self::CDN_URL.'/clan-badges/'.$guildId.'/'.$badgeHash.'.png';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchUser(string $discordId): ?array
    {
        if (! $this->botToken) {
            return null;
        }

        $client = Http::withToken($this->botToken, 'Bot')->timeout(5);

        if (app()->isLocal()) {
            $client = $client->withoutVerifying();
        }

        try {
            $response = $client->get(self::BASE_URL.'/users/'.$discordId);
        } catch (Throwable) {
            return null;
        }

        return $response->successful() ? $response->json() : null;
    }
}
