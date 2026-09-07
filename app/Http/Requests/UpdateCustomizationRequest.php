<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\UploadLimit;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomizationRequest extends FormRequest
{
    public function rules(): array
    {
        $maxKilobytes = UploadLimit::maxSizeInMegabytes() * 1024;

        return [
            'display_name' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],

            'card_width' => ['nullable', 'integer', 'min:200', 'max:1200'],
            'card_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'card_bg_color' => ['nullable', 'string', 'max:50'],
            'border_width' => ['nullable', 'integer', 'min:0', 'max:20'],
            'border_radius' => ['nullable', 'integer', 'min:0', 'max:50'],
            'border_color' => ['nullable', 'string', 'max:50'],

            'background_type' => ['nullable', 'string', 'in:color,image,video'],
            'background_color' => ['nullable', 'string', 'max:20'],
            'background_blur' => ['nullable', 'integer', 'min:0', 'max:50'],

            'font_family' => ['nullable', 'string', 'max:50'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'icon_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'hover_bg_color' => ['nullable', 'string', 'max:50'],
            'selection_bg_color' => ['nullable', 'string', 'max:50'],
            'selection_text_color' => ['nullable', 'string', 'max:50'],

            'avatar_size' => ['nullable', 'integer', 'min:40', 'max:200'],
            'avatar_radius' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'avatar_border_color' => ['nullable', 'string', 'max:20'],
            'avatar_glow_color' => ['nullable', 'string', 'max:50'],

            'username_effect' => ['nullable', 'string', 'max:50'],
            'username_glow_color' => ['nullable', 'string', 'max:50'],
            'socials_glow_color' => ['nullable', 'string', 'max:50'],

            'bio_text_color' => ['nullable', 'string', 'max:20'],
            'bio_font_size' => ['nullable', 'integer', 'min:10', 'max:32'],
            'bio_align' => ['nullable', 'string', 'in:left,center,right'],
            'location_text_color' => ['nullable', 'string', 'max:20'],
            'location_icon_color' => ['nullable', 'string', 'max:20'],

            'typewriter_speed' => ['nullable', 'integer', 'min:20', 'max:300'],
            'display_name_typewriter_words' => ['nullable', 'string'],
            'bio_typewriter_words' => ['nullable', 'string'],

            'audio_title' => ['nullable', 'string', 'max:255'],
            'audio_progress_color' => ['nullable', 'string', 'max:20'],
            'audio_title_color' => ['nullable', 'string', 'max:20'],
            'audio_card_bg_color' => ['nullable', 'string', 'max:50'],
            'audio_button_color' => ['nullable', 'string', 'max:20'],
            'audio_border_color' => ['nullable', 'string', 'max:50'],
            'audio_border_width' => ['nullable', 'integer', 'min:0', 'max:20'],
            'audio_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'audio_glow_color' => ['nullable', 'string', 'max:50'],
            'audio_hover_effect' => ['nullable', 'string', 'max:50'],

            'mute_icon_color' => ['nullable', 'string', 'max:20'],
            'mute_icon_bg_color' => ['nullable', 'string', 'max:20'],
            'mute_icon_unmute_color' => ['nullable', 'string', 'max:20'],
            'mute_icon_hover_color' => ['nullable', 'string', 'max:20'],
            'mute_icon_hover_unmute_color' => ['nullable', 'string', 'max:20'],
            'mute_icon_hover_bg_color' => ['nullable', 'string', 'max:20'],

            'discord_tag' => ['nullable', 'string', 'max:100'],
            'discord_offline_text' => ['nullable', 'string', 'max:255'],
            'discord_status_scale' => ['nullable', 'integer', 'min:50', 'max:200'],
            'discord_text_color' => ['nullable', 'string', 'max:20'],
            'discord_card_bg_color' => ['nullable', 'string', 'max:50'],
            'discord_name_color' => ['nullable', 'string', 'max:20'],
            'discord_status_color' => ['nullable', 'string', 'max:20'],
            'discord_border_color' => ['nullable', 'string', 'max:50'],
            'discord_border_width' => ['nullable', 'integer', 'min:0', 'max:20'],
            'discord_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'discord_glow_color' => ['nullable', 'string', 'max:50'],
            'discord_hover_effect' => ['nullable', 'string', 'max:50'],

            'enter_text' => ['nullable', 'string', 'max:255'],
            'enter_text_color' => ['nullable', 'string', 'max:20'],
            'enter_bg_color' => ['nullable', 'string', 'max:20'],
            'enter_image_url' => ['nullable', 'string', 'max:255'],
            'entry_animation' => ['nullable', 'string', 'in:fade,slide_up,zoom_in,bounce'],

            'hover_animation' => ['nullable', 'string', 'in:scale,glow,bounce,lift,none'],
            'hover_zoom_scale' => ['nullable', 'numeric', 'min:1', 'max:2'],
            'hover_glow_color' => ['nullable', 'string', 'max:50'],
            'hover_lift_amount' => ['nullable', 'integer', 'min:1', 'max:50'],
            'hover_bounce_intensity' => ['nullable', 'integer', 'min:1', 'max:30'],
            'avatar_hover_effect' => ['nullable', 'string', 'max:50'],
            'socials_hover_effect' => ['nullable', 'string', 'max:50'],

            'element_order' => ['nullable', 'string'],

            'views_text_color' => ['nullable', 'string', 'max:20'],
            'views_icon_color' => ['nullable', 'string', 'max:20'],

            'background_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,mp4,webm', 'max:'.$maxKilobytes],
            'banner_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif', 'max:'.$maxKilobytes],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,flac,aiff,aac', 'max:'.$maxKilobytes],
            'audio_cover_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:'.$maxKilobytes],
            'avatar_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'secondary_avatar_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'enter_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'custom_cursor_file' => ['nullable', 'file', 'mimes:png,gif,cur,ico,svg,jpeg,jpg,webp', 'max:10240'],
            'custom_font_file' => ['nullable', 'file', 'mimes:ttf,otf,woff,woff2', 'max:10240'],
            'discord_banner_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'discord_profile_effect_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,apng', 'max:10240'],
            'discord_avatar_decoration_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,apng', 'max:10240'],
        ];
    }
}
