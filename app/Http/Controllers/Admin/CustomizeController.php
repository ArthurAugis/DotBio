<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomizationRequest;
use App\Models\Profile;
use App\Support\UploadLimit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class CustomizeController extends Controller
{
    /**
     * Request file field => [profile column, storage directory].
     */
    private const FILE_UPLOADS = [
        'background_file' => ['background_url', 'uploads/backgrounds'],
        'banner_file' => ['banner_url', 'uploads/banners'],
        'discord_banner_file' => ['discord_banner_url', 'uploads/discord_banners'],
        'discord_profile_effect_file' => ['discord_profile_effect_url', 'uploads/discord_effects'],
        'discord_avatar_decoration_file' => ['discord_avatar_decoration_url', 'uploads/discord_decorations'],
        'secondary_avatar_file' => ['secondary_avatar_url', 'uploads/secondary_avatars'],
        'enter_image' => ['enter_image_url', 'uploads/enter_images'],
        'audio_file' => ['audio_url', 'uploads/audio'],
        'audio_cover_file' => ['audio_cover_url', 'uploads/audio_covers'],
        'avatar_file' => ['avatar_url', 'uploads/avatars'],
        'custom_cursor_file' => ['custom_cursor_url', 'uploads/cursors'],
        'custom_font_file' => ['custom_font_url', 'uploads/fonts'],
    ];

    private const BOOLEAN_FIELDS = [
        'show_card_container',
        'show_avatar',
        'show_display_name',
        'show_location',
        'show_bio',
        'show_lanyard_card',
        'show_social_links',
        'show_audio_player',
        'show_mute_icon',
        'show_discord_tag',
        'show_discord_decoration',
        'show_views_count',
        'use_discord_avatar',
        'avatar_glow',
        'glow_username',
        'glow_socials',
        'show_social_tooltips',
        'typewriter_enabled',
        'bio_typewriter_enabled',
        'audio_glow',
        'discord_glow',
        'tilt_3d_enabled',
    ];

    public function edit(): View
    {
        return view('admin.customize', [
            'profile' => Profile::firstOrFail(),
            'maxUploadSizeMB' => UploadLimit::maxSizeInMegabytes(),
        ]);
    }

    public function update(UpdateCustomizationRequest $request): JsonResponse
    {
        $profile = Profile::first() ?? Profile::create(['user_id' => auth()->id()]);

        $validated = $request->validated();

        foreach (self::FILE_UPLOADS as $field => [$column, $directory]) {
            $newUrl = $this->storePublicFile($request, $field, $directory);

            if (! $newUrl) {
                continue;
            }

            $this->deleteOldPublicFile($profile->{$column});
            $validated[$column] = $newUrl;

            if ($field === 'audio_file') {
                $validated['audio_enabled'] = true;
            }

            if ($field === 'background_file') {
                $mimeType = (string) $request->file($field)?->getMimeType();
                $validated['background_type'] = str_starts_with($mimeType, 'video/') ? 'video' : 'image';
            }
        }

        if ($request->has('element_order')) {
            $validated['element_order'] = $this->jsonArrayValue($request->input('element_order'));
        }

        if ($request->has('display_name_typewriter_words')) {
            $validated['display_name_typewriter_words'] = $this->jsonArrayValue($request->input('display_name_typewriter_words'));
        }

        if ($request->has('bio_typewriter_words')) {
            $validated['bio_typewriter_words'] = $this->jsonArrayValue($request->input('bio_typewriter_words'));
        }

        $submittedBooleans = array_values(array_filter(
            self::BOOLEAN_FIELDS,
            fn (string $field): bool => $request->has($field),
        ));

        $profile->update(array_merge(
            $validated,
            $this->booleanFieldValues($request, $submittedBooleans),
        ));

        return response()->json([
            'success' => true,
            'message' => 'Profile customized successfully.',
            'profile' => $profile->fresh(),
        ]);
    }
}
