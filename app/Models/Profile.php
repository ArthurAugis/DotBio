<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    use HasFactory;

    public const DEFAULT_ELEMENT_ORDER = [
        'avatar',
        'display_name',
        'location',
        'bio',
        'discord',
        'socials',
        'audio',
        'views',
    ];

    protected $fillable = [
        'user_id',
        'display_name',
        'bio',
        'location',

        'avatar_url',
        'secondary_avatar_url',
        'banner_url',
        'custom_cursor_url',
        'custom_font_url',

        'background_type',
        'background_url',
        'background_color',
        'background_effect',
        'background_blur',

        'card_bg_color',
        'card_opacity',
        'card_width',
        'show_card_container',

        'text_color',
        'icon_color',
        'accent_color',
        'border_color',
        'border_width',
        'border_radius',
        'font_family',
        'hover_bg_color',
        'selection_bg_color',
        'selection_text_color',

        'show_avatar',
        'show_display_name',
        'show_location',
        'show_bio',
        'show_lanyard_card',
        'show_social_links',
        'show_audio_player',
        'show_mute_icon',
        'show_views_count',

        'avatar_size',
        'avatar_radius',
        'avatar_border_color',
        'avatar_glow',
        'avatar_glow_color',

        'username_effect',
        'glow_username',
        'username_glow_color',
        'glow_socials',
        'socials_glow_color',

        'bio_text_color',
        'bio_font_size',
        'bio_align',
        'location_text_color',
        'location_icon_color',

        'typewriter_enabled',
        'typewriter_speed',
        'display_name_typewriter_words',
        'bio_typewriter_enabled',
        'bio_typewriter_words',

        'audio_enabled',
        'audio_url',
        'audio_title',
        'audio_cover_url',
        'audio_progress_color',
        'audio_title_color',
        'audio_card_bg_color',
        'audio_button_color',
        'audio_border_color',
        'audio_border_width',
        'audio_opacity',
        'audio_glow',
        'audio_glow_color',
        'audio_hover_effect',

        'mute_icon_color',
        'mute_icon_bg_color',
        'mute_icon_unmute_color',
        'mute_icon_hover_color',
        'mute_icon_hover_bg_color',
        'mute_icon_hover_unmute_color',

        'discord_id',
        'custom_discord_status',
        'custom_status_text',
        'discord_offline_text',
        'last_seen_at',
        'use_discord_avatar',
        'discord_banner_url',
        'discord_profile_effect_url',
        'discord_avatar_decoration_url',
        'show_discord_decoration',
        'show_discord_tag',
        'discord_tag',
        'discord_clan_badge_url',
        'discord_status_scale',
        'discord_text_color',
        'discord_card_bg_color',
        'discord_name_color',
        'discord_status_color',
        'discord_border_color',
        'discord_border_width',
        'discord_opacity',
        'discord_glow',
        'discord_glow_color',
        'discord_hover_effect',

        'enter_text',
        'enter_text_color',
        'enter_bg_color',
        'enter_image_url',
        'entry_animation',

        'hover_animation',
        'hover_zoom_scale',
        'hover_glow_color',
        'hover_lift_amount',
        'hover_bounce_intensity',
        'avatar_hover_effect',
        'socials_hover_effect',
        'tilt_3d_enabled',

        'element_order',

        'views_text_color',
        'views_icon_color',

        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_image',
        'meta_robots',
        'favicon_type',
        'favicon_url',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',

            'element_order' => 'array',
            'display_name_typewriter_words' => 'array',
            'bio_typewriter_words' => 'array',

            'show_card_container' => 'boolean',
            'show_avatar' => 'boolean',
            'show_display_name' => 'boolean',
            'show_location' => 'boolean',
            'show_bio' => 'boolean',
            'show_lanyard_card' => 'boolean',
            'show_social_links' => 'boolean',
            'show_audio_player' => 'boolean',
            'show_mute_icon' => 'boolean',
            'show_views_count' => 'boolean',
            'show_discord_decoration' => 'boolean',
            'show_discord_tag' => 'boolean',
            'use_discord_avatar' => 'boolean',
            'avatar_glow' => 'boolean',
            'glow_username' => 'boolean',
            'glow_socials' => 'boolean',
            'audio_enabled' => 'boolean',
            'audio_glow' => 'boolean',
            'discord_glow' => 'boolean',
            'typewriter_enabled' => 'boolean',
            'bio_typewriter_enabled' => 'boolean',
            'tilt_3d_enabled' => 'boolean',

            'views_count' => 'integer',
            'background_blur' => 'integer',
            'card_opacity' => 'float',
            'audio_opacity' => 'float',
            'discord_opacity' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class)->orderBy('sort_order');
    }

    protected function avatarSource(): Attribute
    {
        return Attribute::get(function (): string {
            $authenticatedAvatar = auth()->user()?->avatar;

            if ($this->use_discord_avatar && $authenticatedAvatar) {
                return $authenticatedAvatar;
            }

            return $this->avatar_url
                ?? $authenticatedAvatar
                ?? 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($this->display_name ?? 'dotbio');
        });
    }

    protected function audioCoverSource(): Attribute
    {
        return Attribute::get(fn (): string => $this->audio_cover_url ?: $this->avatar_source);
    }

    protected function elementOrderList(): Attribute
    {
        return Attribute::get(fn (): array => $this->element_order ?: self::DEFAULT_ELEMENT_ORDER);
    }

    protected function faviconSource(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->favicon_type === 'custom' && $this->favicon_url) {
                return asset($this->favicon_url);
            }

            return asset($this->avatar_source);
        });
    }

    protected function discordMargin(): Attribute
    {
        return Attribute::get(function (): int {
            $scale = $this->discord_status_scale ?: 100;

            return $scale > 100 ? (int) (($scale - 100) * 0.25) : 0;
        });
    }
}
