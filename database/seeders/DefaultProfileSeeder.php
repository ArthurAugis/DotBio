<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultProfileSeeder extends Seeder
{
    private const LINKS = [
        ['title' => 'GitHub', 'url' => 'https://github.com', 'icon' => 'github', 'color' => '#ffffff'],
        ['title' => 'Discord', 'url' => 'https://discord.gg', 'icon' => 'discord', 'color' => '#5865f2'],
        ['title' => 'YouTube', 'url' => 'https://youtube.com', 'icon' => 'youtube', 'color' => '#ff0000'],
        ['title' => 'X', 'url' => 'https://x.com', 'icon' => 'x-twitter', 'color' => '#1da1f2'],
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@dotbio.local'],
            [
                'name' => 'DotBio Admin',
                'password' => Hash::make('password'),
            ],
        );

        $profile = Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => 'DotBio',
                'bio' => 'A self-hosted, open-source bio page.',
                'avatar_url' => 'https://api.dicebear.com/7.x/bottts/svg?seed=dotbio',
                'background_type' => 'color',
                'background_color' => '#09090b',
                'accent_color' => '#8b5cf6',
                'card_bg_color' => '#121017',
                'card_opacity' => 0.85,
                'show_card_container' => true,
                'show_avatar' => true,
                'show_display_name' => true,
                'show_bio' => true,
                'show_social_links' => true,
                'show_views_count' => true,
                'enter_text' => 'Click anywhere to enter',
                'meta_title' => 'DotBio',
                'meta_description' => 'My personal link page, powered by DotBio.',
            ],
        );

        if ($profile->links()->exists()) {
            return;
        }

        foreach (self::LINKS as $index => $link) {
            Link::create($link + [
                'profile_id' => $profile->id,
                'sort_order' => $index + 1,
                'is_visible' => true,
            ]);
        }
    }
}
