<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ProviderAvatars
{
    public const TOKEN_PROVIDERS = ['google', 'patreon'];

    public function fetch(User $user, string $provider): ?string
    {
        $id = (string) $user->{$provider.'_id'};

        return match ($provider) {
            'github' => $this->http()->get("https://api.github.com/user/{$id}")->throw()->json('avatar_url'),
            'steam' => $this->http()->get('https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/', [
                'key' => config('services.steam.client_secret'),
                'steamids' => $id,
            ])->throw()->json('response.players.0.avatarmedium'),
            'twitch' => $this->http()
                ->withToken($this->clientCredentialsToken('https://id.twitch.tv/oauth2/token', 'twitch'))
                ->withHeaders(['Client-Id' => config('services.twitch.client_id')])
                ->get('https://api.twitch.tv/helix/users', ['id' => $id])
                ->throw()->json('data.0.profile_image_url'),
            'spotify' => $this->http()
                ->withToken($this->clientCredentialsToken('https://accounts.spotify.com/api/token', 'spotify'))
                ->get("https://api.spotify.com/v1/users/{$id}")
                ->throw()->json('images.0.url'),
            'google' => $this->withUserToken($user, 'google', 'https://oauth2.googleapis.com/token')
                ?->get('https://www.googleapis.com/oauth2/v3/userinfo')
                ->throw()->json('picture'),
            'patreon' => $this->withUserToken($user, 'patreon', 'https://www.patreon.com/api/oauth2/token')
                ?->get('https://www.patreon.com/api/oauth2/v2/identity', ['fields[user]' => 'image_url'])
                ->throw()->json('data.attributes.image_url'),
            default => null,
        };
    }

    private function http(): PendingRequest
    {
        return Http::timeout(10)->acceptJson();
    }

    private function clientCredentialsToken(string $url, string $provider): string
    {
        return (string) $this->http()->asForm()->post($url, [
            'grant_type' => 'client_credentials',
            'client_id' => config("services.{$provider}.client_id"),
            'client_secret' => config("services.{$provider}.client_secret"),
        ])->throw()->json('access_token');
    }

    private function withUserToken(User $user, string $provider, string $tokenUrl): ?PendingRequest
    {
        $tokens = $user->oauth_tokens ?? [];

        if (empty($tokens[$provider])) {
            return null;
        }

        $response = $this->http()->asForm()->post($tokenUrl, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $tokens[$provider],
            'client_id' => config("services.{$provider}.client_id"),
            'client_secret' => config("services.{$provider}.client_secret"),
        ])->throw();

        if ($rotated = $response->json('refresh_token')) {
            $user->update(['oauth_tokens' => [$provider => $rotated] + $tokens]);
        }

        return $this->http()->withToken((string) $response->json('access_token'));
    }
}
