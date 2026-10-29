<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use App\Services\ProviderAvatars;
use Illuminate\Console\Command;
use Throwable;

class RefreshProviderAvatars extends Command
{
    protected $signature = 'avatars:refresh';

    protected $description = 'Refresh account avatars from their login provider (Discord is handled by the bot)';

    public function handle(ProviderAvatars $avatars): int
    {
        $page = Profile::first();

        foreach (User::all() as $user) {
            $provider = $user->loginMethod();

            if (in_array($provider, [null, 'discord', 'password'], true)) {
                continue;
            }

            try {
                $avatar = $avatars->fetch($user, $provider);
            } catch (Throwable $exception) {
                report($exception);

                continue;
            }

            if (! $avatar || $avatar === $user->avatar) {
                continue;
            }

            if ($page?->user_id === $user->id
                && (! $page->avatar_url || $page->avatar_url === $user->avatar || str_contains($page->avatar_url, 'dicebear'))) {
                $page->update(['avatar_url' => $avatar]);
            }

            $user->update(['avatar' => $avatar]);
        }

        return self::SUCCESS;
    }
}
