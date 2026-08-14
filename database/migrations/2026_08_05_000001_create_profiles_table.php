<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('display_name')->nullable();
            $table->string('bio')->nullable();
            $table->string('location')->nullable();

            $table->string('avatar_url')->nullable();
            $table->string('secondary_avatar_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->string('custom_cursor_url')->nullable();
            $table->string('custom_font_url')->nullable();

            $table->string('background_type')->nullable();
            $table->string('background_url')->nullable();
            $table->string('background_color')->nullable();
            $table->string('background_effect')->nullable();
            $table->integer('background_blur')->nullable();

            $table->string('card_bg_color')->nullable();
            $table->integer('card_opacity')->nullable();
            $table->integer('card_width')->nullable();
            $table->boolean('show_card_container')->default(false);

            $table->string('text_color')->nullable();
            $table->string('icon_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('border_color')->nullable();
            $table->integer('border_width')->nullable();
            $table->integer('border_radius')->nullable();
            $table->string('font_family')->nullable();
            $table->string('hover_bg_color')->nullable();
            $table->string('selection_bg_color')->nullable();
            $table->string('selection_text_color')->nullable();

            $table->boolean('show_avatar')->default(false);
            $table->boolean('show_display_name')->default(false);
            $table->boolean('show_location')->default(false);
            $table->boolean('show_bio')->default(false);
            $table->boolean('show_lanyard_card')->default(false);
            $table->boolean('show_social_links')->default(false);
            $table->boolean('show_audio_player')->default(false);
            $table->boolean('show_mute_icon')->default(true);
            $table->boolean('show_views_count')->default(false);

            $table->integer('avatar_size')->nullable();
            $table->integer('avatar_radius')->nullable();
            $table->string('avatar_border_color')->nullable();
            $table->boolean('avatar_glow')->default(true);
            $table->string('avatar_glow_color')->nullable();

            $table->string('username_effect')->nullable();
            $table->boolean('glow_username')->default(false);
            $table->string('username_glow_color')->nullable();
            $table->boolean('glow_socials')->default(false);
            $table->string('socials_glow_color')->nullable();

            $table->string('bio_text_color')->nullable();
            $table->integer('bio_font_size')->nullable();
            $table->string('bio_align')->nullable();
            $table->string('location_text_color')->nullable();
            $table->string('location_icon_color')->nullable();

            $table->boolean('typewriter_enabled')->default(true);
            $table->integer('typewriter_speed')->nullable();
            $table->json('display_name_typewriter_words')->nullable();
            $table->boolean('bio_typewriter_enabled')->default(false);
            $table->json('bio_typewriter_words')->nullable();

            $table->boolean('audio_enabled')->default(false);
            $table->string('audio_url')->nullable();
            $table->string('audio_title')->nullable();
            $table->string('audio_cover_url')->nullable();
            $table->string('audio_progress_color')->nullable();
            $table->string('audio_title_color')->nullable();
            $table->string('audio_card_bg_color')->nullable();
            $table->string('audio_button_color')->nullable();
            $table->string('audio_border_color')->nullable();
            $table->integer('audio_border_width')->nullable();
            $table->float('audio_opacity')->default(1);
            $table->boolean('audio_glow')->default(false);
            $table->string('audio_glow_color')->nullable();
            $table->string('audio_hover_effect')->nullable();

            $table->string('mute_icon_color')->nullable();
            $table->string('mute_icon_bg_color')->nullable();
            $table->string('mute_icon_unmute_color')->nullable();
            $table->string('mute_icon_hover_color')->nullable();
            $table->string('mute_icon_hover_bg_color')->nullable();
            $table->string('mute_icon_hover_unmute_color')->nullable();

            $table->string('discord_id')->nullable()->index();
            $table->string('custom_discord_status')->nullable();
            $table->string('custom_status_text')->nullable();
            $table->string('discord_offline_text')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('use_discord_avatar')->default(false);
            $table->string('discord_banner_url')->nullable();
            $table->string('discord_profile_effect_url')->nullable();
            $table->string('discord_avatar_decoration_url')->nullable();
            $table->boolean('show_discord_decoration')->default(true);
            $table->boolean('show_discord_tag')->default(true);
            $table->string('discord_tag')->nullable();
            $table->string('discord_clan_badge_url')->nullable();
            $table->integer('discord_status_scale')->nullable();
            $table->string('discord_text_color')->nullable();
            $table->string('discord_card_bg_color')->nullable();
            $table->string('discord_name_color')->nullable();
            $table->string('discord_status_color')->nullable();
            $table->string('discord_border_color')->nullable();
            $table->integer('discord_border_width')->nullable();
            $table->float('discord_opacity')->default(1);
            $table->boolean('discord_glow')->default(false);
            $table->string('discord_glow_color')->nullable();
            $table->string('discord_hover_effect')->nullable();

            $table->string('enter_text')->nullable();
            $table->string('enter_text_color')->nullable();
            $table->string('enter_bg_color')->nullable();
            $table->string('enter_image_url')->nullable();
            $table->string('entry_animation')->nullable();

            $table->string('hover_animation')->nullable();
            $table->float('hover_zoom_scale')->nullable();
            $table->string('hover_glow_color')->nullable();
            $table->integer('hover_lift_amount')->nullable();
            $table->integer('hover_bounce_intensity')->nullable();
            $table->string('avatar_hover_effect')->nullable();
            $table->string('socials_hover_effect')->nullable();
            $table->boolean('tilt_3d_enabled')->default(false);

            $table->json('element_order')->nullable();

            $table->string('views_text_color')->nullable();
            $table->string('views_icon_color')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('meta_robots')->default('index, follow');
            $table->string('favicon_type')->default('avatar');
            $table->string('favicon_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
