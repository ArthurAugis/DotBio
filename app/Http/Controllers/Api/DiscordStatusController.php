<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDiscordStatusRequest;
use App\Models\Profile;
use App\Services\DiscordApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DiscordStatusController extends Controller
{
    public function getStatus(string $discordId): JsonResponse
    {
        $profile = Profile::where('discord_id', $discordId)->first();

        if (! $profile) {
            return response()->json(['status' => 'offline', 'activity' => '']);
        }

        $missingAssets = ! $profile->discord_avatar_decoration_url || ! $profile->discord_clan_badge_url;

        if ($missingAssets && Cache::add('discord-assets-lookup:'.$discordId, true, now()->addHour())) {
            $assets = DiscordApi::make()->profileAssets($discordId);

            if ($assets !== []) {
                $profile->update($assets);
            }
        }

        return response()->json([
            'status' => $profile->custom_discord_status ?? 'offline',
            'activity' => $profile->custom_status_text ?? '',
            'last_seen' => $this->lastSeenText($profile),
            'decoration_url' => $profile->discord_avatar_decoration_url,
            'tag' => $profile->discord_tag,
            'clan_badge_url' => $profile->discord_clan_badge_url,
        ]);
    }

    public function updateStatus(UpdateDiscordStatusRequest $request): JsonResponse
    {
        $profile = Profile::where('discord_id', $request->validated('discord_id'))->first();

        if (! $profile) {
            return response()->json(['error' => 'Profile not found for the given discord_id.'], 404);
        }

        $status = $request->validated('status');

        $attributes = [
            'custom_discord_status' => $status,
            'custom_status_text' => $request->validated('activity') ?? '',
        ];

        if ($status === 'offline' && $profile->custom_discord_status !== 'offline') {
            $attributes['last_seen_at'] = now('UTC');
        }

        $profile->update($attributes);

        return response()->json([
            'success' => true,
            'status' => $profile->custom_discord_status,
            'activity' => $profile->custom_status_text,
        ]);
    }

    private function lastSeenText(Profile $profile): string
    {
        if ($profile->last_seen_at) {
            return 'last seen '.$profile->last_seen_at->diffForHumans();
        }

        return $profile->discord_offline_text ?? 'last seen recently';
    }
}
