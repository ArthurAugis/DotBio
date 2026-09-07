<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $pageTitle = $profile->meta_title ?: ($profile->display_name ?? 'DotBio Profile');
        $pageDescription = $profile->meta_description ?: ($profile->bio ?? 'Welcome to my DotBio profile card.');
        $faviconUrl = $profile->favicon_source;
        $metaImageUrl = $profile->meta_image ? asset($profile->meta_image) : asset($profile->avatar_source);
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if($profile->meta_keywords)
        <meta name="keywords" content="{{ $profile->meta_keywords }}">
    @endif
    <meta name="robots" content="{{ $profile->meta_robots ?? 'index, follow' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $metaImageUrl }}">
    <meta property="og:url" content="{{ request()->url() }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $metaImageUrl }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@400;600;700&family=Space+Grotesk:wght@500;600;700&family=Press+Start+2P&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style id="dynamicStyles">
        [x-cloak] { display: none !important; }

        @if($profile->custom_font_url)
            @font-face {
                font-family: 'UserCustomFont';
                src: url('{{ asset($profile->custom_font_url) }}');
            }
        @endif

        @if($profile->selection_bg_color)
            ::selection {
                background-color: {{ $profile->selection_bg_color }} !important;
                color: {{ $profile->selection_text_color ?? '#ffffff' }} !important;
            }
        @endif

        body {
            @if($profile->custom_font_url)
                font-family: 'UserCustomFont', sans-serif !important;
            @else
                font-family: '{{ $profile->font_family ?? 'Outfit' }}', sans-serif;
            @endif
            color: {{ $profile->text_color ?? '#ffffff' }};
            background-color: {{ $profile->background_color ?? '#080808' }};
            @if($profile->custom_cursor_url)
                cursor: url('{{ asset($profile->custom_cursor_url) }}'), auto !important;
            @endif
        }

        .avatar-neon-ring {
            box-shadow: 0 0 20px {{ $profile->accent_color ?? '#8b5cf6' }}, 0 0 35px {{ $profile->accent_color ?? '#8b5cf6' }};
        }

        /* Global Entry Animation Keyframes */
        @keyframes profileFadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes profileSlideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes profileZoomIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes profileBounceIn { 0% { opacity: 0; transform: scale(0.3); } 50% { transform: scale(1.05); } 70% { transform: scale(0.9); } 100% { opacity: 1; transform: scale(1); } }

        .entry-anim-fade { animation: profileFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .entry-anim-slide_up { animation: profileSlideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .entry-anim-zoom_in { animation: profileZoomIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .entry-anim-bounce { animation: profileBounceIn 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        /* Sub-element hover animations with custom dynamic settings */
        @php
            $zoomScale = $profile->hover_zoom_scale ?? 1.08;
            $glowColor = $profile->hover_glow_color ?: ($profile->accent_color ?? '#8b5cf6');
            $liftAmount = $profile->hover_lift_amount ?? 10;
            $bounceHeight = $profile->hover_bounce_intensity ?? 8;
        @endphp

        @keyframes subtleBounceKeyframe {
            0% { transform: scale(1); }
            40% { transform: scale(1.08) translateY(-{{ $bounceHeight }}px); }
            70% { transform: scale(0.98) translateY(-2px); }
            100% { transform: scale(1.05) translateY(-{{ max(2, $bounceHeight - 3) }}px); }
        }

        .hover-anim-none { transition: none !important; }

        .hover-anim-scale { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important; }
        .hover-anim-scale:hover { transform: scale({{ $zoomScale }}) !important; }

        .hover-anim-bounce:hover { animation: subtleBounceKeyframe 0.5s ease forwards !important; }

        .hover-anim-lift { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease !important; }
        .hover-anim-lift:hover { transform: translateY(-{{ $liftAmount }}px) !important; }

        .hover-anim-glow { transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important; }
        .hover-anim-glow:hover { 
            box-shadow: 0 0 25px {{ $glowColor }}, inset 0 0 15px {{ $glowColor }} !important; 
            border-color: {{ $glowColor }} !important;
            transform: scale(1.03) !important; 
        }

        @if($profile->glow_username)
        .username-glow {
            text-shadow: 0 0 12px {{ $profile->accent_color ?? '#8b5cf6' }}, 0 0 24px {{ $profile->accent_color ?? '#8b5cf6' }};
        }
        @endif

        .icon-badge {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }
        .icon-badge:hover {
            @if($profile->glow_socials)
                filter: drop-shadow(0 0 8px {{ $profile->socials_glow_color ?: ($profile->accent_color ?? '#8b5cf6') }});
            @endif
        }

        @php
            $tooltipHex = ltrim($profile->social_tooltip_bg_color ?: '#0a0a0c', '#');
            if (strlen($tooltipHex) === 3) {
                $tooltipHex = $tooltipHex[0].$tooltipHex[0].$tooltipHex[1].$tooltipHex[1].$tooltipHex[2].$tooltipHex[2];
            }
            $tooltipR = hexdec(substr($tooltipHex, 0, 2));
            $tooltipG = hexdec(substr($tooltipHex, 2, 2));
            $tooltipB = hexdec(substr($tooltipHex, 4, 2));
            $tooltipA = $profile->social_tooltip_opacity ?? 0.92;
            $tooltipBg = "rgba({$tooltipR},{$tooltipG},{$tooltipB},{$tooltipA})";
            $tooltipBorder = $profile->social_tooltip_border_color ?: ($profile->accent_color ?? '#8b5cf6');
        @endphp

        .social-item {
            position: relative;
            display: inline-flex;
        }
        .social-tooltip {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%) translateY(4px);
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            line-height: 1.4;
            white-space: nowrap;
            background: {{ $tooltipBg }};
            color: {{ $profile->social_tooltip_text_color ?: '#fafafa' }};
            border: 1px solid {{ $tooltipBorder }};
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.45);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
            z-index: 60;
        }
        .social-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: {{ $tooltipBorder }};
        }
        .social-item:hover .social-tooltip,
        .social-item:focus-within .social-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        /* 1. Main Global Profile Card Container */
        .custom-card-container {
            @if($profile->show_card_container)
                @php
                    $hex = ltrim($profile->card_bg_color ?? '#121017', '#');
                    if (strlen($hex) == 3) {
                        $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
                        $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
                        $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
                    } else {
                        $r = hexdec(substr($hex, 0, 2));
                        $g = hexdec(substr($hex, 2, 2));
                        $b = hexdec(substr($hex, 4, 2));
                    }
                    $alpha = $profile->card_opacity ?? 1;
                @endphp
                background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, {{ $alpha }});
                backdrop-filter: blur({{ $profile->background_blur ?? 10 }}px);
                border: {{ is_numeric($profile->border_width) ? $profile->border_width : 1 }}px solid {{ $profile->border_color ?? 'rgba(255, 255, 255, 0.08)' }};
                border-radius: {{ $profile->border_radius ?? 24 }}px;
                transition: background-color 0.2s ease;
            @else

                background: transparent !important;
                background-color: transparent !important;
                border: none !important;
                outline: none !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                opacity: 1 !important;
            @endif
        }
        @if($profile->show_card_container && $profile->hover_bg_color)
        .custom-card-container:hover {
            background-color: {{ $profile->hover_bg_color }} !important;
        }
        @endif

        /* 2. Independent Sub-Cards (Audio Player & Discord Widget) */
        .player-card {
            background: rgba(18, 16, 23, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        }

        .discord-card {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="h-full min-h-screen relative flex flex-col items-center justify-center p-4 overflow-x-hidden select-none"
      x-data="dotBioProfile({{ json_encode($profile) }})">

    @if($profile->audio_enabled && $profile->audio_url && ($profile->show_mute_icon ?? true))
    <div x-cloak x-show="profile.show_audio_player" class="fixed top-6 left-6 z-40" x-data="{ hovered: false }">
        <button @click="toggleMute()" 
                @mouseenter="hovered = true"
                @mouseleave="hovered = false"
                class="transition-all duration-200 text-xl p-2.5 cursor-pointer focus:outline-none rounded-full" 
                :style="{
                    color: isMuted ? (hovered ? ('{{ $profile->mute_icon_hover_color }}' || '{{ $profile->mute_icon_color }}' || '#ffffff') : ('{{ $profile->mute_icon_color }}' || '#ffffff')) : (hovered ? ('{{ $profile->mute_icon_hover_unmute_color }}' || '{{ $profile->mute_icon_unmute_color }}' || '#ffffff') : ('{{ $profile->mute_icon_unmute_color }}' || '#ffffff')),
                    backgroundColor: 'transparent'
                }"
                :title="isMuted ? 'Unmute Audio' : 'Mute Audio'">
            <i class="fa-solid" :class="isMuted ? 'fa-volume-xmark' : 'fa-volume-high'"></i>
        </button>
    </div>
    @endif

    @if(($profile->audio_enabled && $profile->audio_url) || !empty($profile->background_url))
    <div x-show="!entered" 
         @click="enterSite()"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center cursor-pointer select-none gap-4 p-6 text-center"
         style="background-color: {{ $profile->enter_bg_color ?? '#000000' }};">
        @if($profile->enter_image_url)
            <img src="{{ asset($profile->enter_image_url) }}" class="max-w-[200px] max-h-[160px] object-contain drop-shadow-2xl animate-pulse">
        @endif
        <p class="text-xs font-semibold tracking-widest uppercase transition"
           style="color: {{ $profile->enter_text_color ?? '#a1a1aa' }};">
            {{ $profile->enter_text ?? 'Click anywhere to enter' }}
        </p>
    </div>
    @endif

    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        @if($profile->background_type === 'video' && $profile->background_url)
            <video id="bg-video" autoplay loop muted playsinline class="w-full h-full object-cover">
                <source src="{{ asset($profile->background_url) }}" type="video/mp4">
            </video>
        @elseif($profile->background_type === 'image' && $profile->background_url)
            <div class="w-full h-full bg-cover bg-center" style="background-image: url('{{ asset($profile->background_url) }}');"></div>
        @else
            <div class="w-full h-full" style="background: radial-gradient(circle at center, #1a1625 0%, #080808 100%);"></div>
        @endif

        <div class="absolute inset-0 bg-black/60"></div>
    </div>

    @php
        $avatarSrc = $profile->avatar_source;
        $audioCoverSrc = $profile->audio_cover_source;
        $order = $profile->element_order_list;
        $discordScale = $profile->discord_status_scale ?: 100;
        $discordMargin = $profile->discord_margin;
    @endphp

    <main class="w-full mx-auto z-10 my-8 flex flex-col items-center entry-anim-{{ $profile->entry_animation ?? 'fade' }}" 
          style="max-width: {{ $profile->card_width ?? 420 }}px;"
          @if($profile->tilt_3d_enabled ?? false)
              x-data="{ rx: 0, ry: 0 }"
              @mousemove="
                  let r = $el.getBoundingClientRect();
                  let cx = r.left + r.width / 2;
                  let cy = r.top + r.height / 2;
                  let dx = ($event.clientX - cx) / (r.width / 2);
                  let dy = ($event.clientY - cy) / (r.height / 2);
                  rx = -dy * 15;
                  ry = dx * 15;
              "
              @mouseleave="rx = 0; ry = 0;"
              :style="{
                  transform: 'perspective(1000px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)',
                  transition: rx === 0 && ry === 0 ? 'transform 0.5s ease' : 'none'
              }"
          @endif>
        @if($profile->show_card_container)
            <div class="custom-card-container w-full overflow-hidden text-center space-y-5 pb-6 hover-anim-{{ $profile->hover_animation ?? 'none' }}">
                @php
                    $activeBanner = $profile->discord_banner_url ?: $profile->banner_url;
                @endphp
                @if($activeBanner)
                    <div class="w-full h-32 bg-cover bg-center relative" style="background-image: url('{{ asset($activeBanner) }}');">
                        @if($profile->discord_profile_effect_url)
                            <img src="{{ asset($profile->discord_profile_effect_url) }}" class="absolute inset-0 w-full h-full object-cover pointer-events-none">
                        @endif
                    </div>
                @endif

                <div class="px-6 space-y-4 flex flex-col items-center {{ $activeBanner ? '-mt-12' : 'pt-6' }}">
        @else
            <div class="w-full space-y-4 flex flex-col items-center">
        @endif

                
                @foreach($order as $el)
                    @if($el === 'avatar' && $profile->show_avatar)
                        <div class="mx-auto relative hover-anim-{{ $profile->avatar_hover_effect ?? 'none' }}" style="width: {{ $profile->avatar_size ?? 96 }}px; height: {{ $profile->avatar_size ?? 96 }}px;">
                            <img src="{{ $avatarSrc }}" alt="{{ $profile->display_name }}" 
                                 class="w-full h-full object-cover shadow-lg {{ $profile->avatar_glow ? 'avatar-neon-ring' : '' }}"
                                 style="border-radius: {{ $profile->avatar_radius ?? 9999 }}px; border: 2px solid {{ $profile->avatar_border_color ?? 'rgba(255,255,255,0.1)' }};">

                            @if($profile->secondary_avatar_url)
                                <img src="{{ asset($profile->secondary_avatar_url) }}" 
                                     class="absolute -bottom-1 -right-1 w-1/3 h-1/3 object-cover rounded-full border border-black shadow-md">
                            @endif

                            @if($profile->discord_avatar_decoration_url)
                                <img src="{{ asset($profile->discord_avatar_decoration_url) }}" 
                                     class="absolute -top-3 -left-3 w-[calc(100%+24px)] h-[calc(100%+24px)] max-w-none pointer-events-none object-contain">
                            @endif
                        </div>
                    @elseif($el === 'display_name' && $profile->show_display_name)
                        <div class="space-y-1">
                            <h1 class="font-bold tracking-tight text-3xl {{ $profile->glow_username ? 'username-glow' : '' }} {{ $profile->username_effect === 'sparkle' ? 'sparkle-text' : '' }} {{ $profile->username_effect === 'rainbow' ? 'rainbow-text' : '' }}"
                                style="color: {{ $profile->text_color ?? '#ffffff' }};">
                                @if($profile->typewriter_enabled && !empty($profile->display_name_typewriter_words))
                                    <span x-text="nameTypewriterText"></span><span class="animate-pulse">|</span>
                                @else
                                    {{ $profile->display_name ?? (auth()->user()->name ?? 'User') }}
                                @endif
                            </h1>
                            @if($profile->show_discord_tag && ($profile->discord_tag || (auth()->check() && auth()->user()->name)))
                                <p class="text-xs text-indigo-300/80 font-mono font-medium flex items-center justify-center gap-1">
                                    <i class="fa-brands fa-discord text-[11px]"></i>
                                    <span>{{ $profile->discord_tag ?: ('@' . (auth()->user()->name ?? 'user')) }}</span>
                                </p>
                            @endif
                        </div>

                    @elseif($el === 'location' && $profile->show_location)
                        <div>
                            <p class="text-xs font-medium flex items-center justify-center gap-1" style="color: {{ $profile->location_text_color ?? '#a1a1aa' }};">
                                <i class="fa-solid fa-location-dot text-[10px]" style="color: {{ $profile->location_icon_color ?? '#c084fc' }};"></i>
                                <span>{{ $profile->location ?? 'Location' }}</span>
                            </p>
                        </div>
                    @elseif($el === 'bio' && $profile->show_bio && ($profile->bio || ($profile->bio_typewriter_enabled && !empty($profile->bio_typewriter_words))))
                        <p class="leading-relaxed font-normal w-full" 
                           style="color: {{ $profile->bio_text_color ?? '#d4d4d8' }}; font-size: {{ $profile->bio_font_size ?? 14 }}px; text-align: {{ $profile->bio_align ?? 'center' }};">
                            @if($profile->bio_typewriter_enabled && !empty($profile->bio_typewriter_words))
                                <span x-text="bioTypewriterText"></span><span class="animate-pulse">|</span>
                            @else
                                {{ $profile->bio }}
                            @endif
                        </p>

                    @elseif($el === 'discord' && $profile->show_lanyard_card)
                        <div class="w-full flex justify-center py-2"
                             style="margin-top: {{ $discordMargin }}px; margin-bottom: {{ $discordMargin }}px;">
                            @php
                                $discordBgHex = ltrim($profile->discord_card_bg_color ?? '#000000', '#');
                                if (strlen($discordBgHex) === 3) {
                                    $discordBgHex = $discordBgHex[0].$discordBgHex[0].$discordBgHex[1].$discordBgHex[1].$discordBgHex[2].$discordBgHex[2];
                                }
                                $discordBgR = hexdec(substr($discordBgHex, 0, 2));
                                $discordBgG = hexdec(substr($discordBgHex, 2, 2));
                                $discordBgB = hexdec(substr($discordBgHex, 4, 2));
                                $discordBgA = $profile->discord_opacity ?? 1;
                                $discordBgRgba = "rgba({$discordBgR},{$discordBgG},{$discordBgB},{$discordBgA})";
                            @endphp
                            <div class="discord-card flex items-center gap-3 px-4 py-2.5 rounded-2xl inline-flex origin-center {{ $profile->discord_glow ? 'avatar-neon-ring' : '' }} hover-anim-{{ $profile->discord_hover_effect ?? 'none' }}"
                                 style="transform: scale({{ $discordScale / 100 }}); background-color: {{ $discordBgRgba }}; border: {{ $profile->discord_border_color ? ((is_numeric($profile->discord_border_width) ? $profile->discord_border_width : 1) . 'px solid ' . $profile->discord_border_color) : 'none' }};">

                                <div class="relative w-8 h-8 flex-shrink-0">
                                    <img src="{{ $avatarSrc }}" alt="{{ $profile->display_name }}" class="w-full h-full object-cover rounded-full">
                                    @if($profile->show_discord_decoration)
                                        <template x-if="discordDecorationUrl">
                                            <img :src="discordDecorationUrl" class="absolute -top-1 -left-1 w-10 h-10 pointer-events-none z-10 max-w-none">
                                        </template>
                                    @endif
                                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border border-black z-20"
                                          @if(!empty($profile->discord_status_color))
                                              style="background-color: {{ $profile->discord_status_color }};"
                                          @else
                                              :class="{
                                                  'bg-emerald-500': discordStatus === 'online',
                                                  'bg-amber-500': discordStatus === 'idle',
                                                  'bg-rose-500': discordStatus === 'dnd',
                                                  'bg-zinc-500': discordStatus === 'offline'
                                              }"
                                          @endif
                                    ></span>
                                </div>
                                <div class="text-left">
                                    <div class="flex items-center gap-1.5">
                                        <p class="text-xs font-bold leading-none" style="color: {{ $profile->discord_name_color ?? '#ffffff' }};">{{ $profile->display_name ?? (auth()->user()->name ?? 'User') }}</p>
                                        @if($profile->show_discord_tag)
                                            <template x-if="discordTag || '{{ $profile->discord_tag }}'">
                                                <span class="bg-black/60 border border-white/10 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full flex items-center gap-1 leading-none">
                                                    <template x-if="discordClanBadgeUrl || '{{ $profile->discord_clan_badge_url }}'">
                                                        <img :src="discordClanBadgeUrl || '{{ $profile->discord_clan_badge_url }}'" class="w-3 h-3 object-contain rounded-sm">
                                                    </template>
                                                    <span x-text="discordTag || '{{ $profile->discord_tag }}'"></span>
                                                </span>
                                            </template>
                                        @endif

                                    </div>
                                    <p x-show="discordStatus === 'offline' || discordActivityText" class="text-[10px] mt-1 font-mono" style="color: {{ $profile->discord_text_color ?? '#a1a1aa' }};" x-text="discordStatus === 'offline' ? discordLastSeen : discordActivityText"></p>
                                </div>

                            </div>
                        </div>

                    @elseif($el === 'socials' && $profile->show_social_links)
                        <div class="w-full flex items-center justify-center gap-3 flex-wrap pt-1">
                            @foreach($profile->links as $link)
                                <span class="social-item">
                                    <a href="{{ $link->url }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       aria-label="{{ $link->title }}"
                                       @click="trackClick({{ $link->id }})"
                                       class="icon-badge p-1 text-xl flex items-center justify-center transition cursor-pointer hover-anim-{{ $link->hover_effect ? $link->hover_effect : ($profile->socials_hover_effect ?? 'none') }}">
                                        <i class="{{ $link->icon_class }}" style="color: {{ $link->color ?: ($profile->icon_color ?: '#ffffff') }};"></i>
                                    </a>
                                    @if($profile->show_social_tooltips ?? true)
                                        <span class="social-tooltip">{{ $link->title }}</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @elseif($el === 'views' && $profile->show_views_count)
                        <div class="pt-2 flex items-center justify-center gap-1.5 text-xs font-mono" style="color: {{ $profile->views_text_color ?? '#71717a' }};">
                            <i class="fa-regular fa-eye text-[11px]" style="color: {{ $profile->views_icon_color ?? '#71717a' }};"></i>
                            <span>{{ number_format($profile->views_count) }} views</span>
                        </div>
                    @elseif($el === 'audio' && $profile->audio_enabled && $profile->audio_url && $profile->show_audio_player)
                        @php
                            $audioBgHex = ltrim($profile->audio_card_bg_color ?? '#000000', '#');
                            if (strlen($audioBgHex) === 3) {
                                $audioBgHex = $audioBgHex[0].$audioBgHex[0].$audioBgHex[1].$audioBgHex[1].$audioBgHex[2].$audioBgHex[2];
                            }
                            $audioBgR = hexdec(substr($audioBgHex, 0, 2));
                            $audioBgG = hexdec(substr($audioBgHex, 2, 2));
                            $audioBgB = hexdec(substr($audioBgHex, 4, 2));
                            $audioBgA = $profile->audio_opacity ?? 1;
                            $audioBgRgba = "rgba({$audioBgR},{$audioBgG},{$audioBgB},{$audioBgA})";
                        @endphp
                        <div class="player-card rounded-2xl p-3 w-full flex items-center justify-between gap-3 shadow-2xl mt-2 {{ $profile->audio_glow ? 'avatar-neon-ring' : '' }} hover-anim-{{ $profile->audio_hover_effect ?? 'none' }}" style="background-color: {{ $audioBgRgba }}; border: {{ $profile->audio_border_color ? ((is_numeric($profile->audio_border_width) ? $profile->audio_border_width : 1) . 'px solid ' . $profile->audio_border_color) : 'none' }};">

                            <div class="flex items-center gap-3 overflow-hidden flex-1">
                                <img src="{{ asset($audioCoverSrc) }}" class="w-10 h-10 object-cover flex-shrink-0" style="border-radius: 12px;">
                                <div class="text-left overflow-hidden flex-1 min-w-0">
                                    <p class="text-xs font-bold truncate" style="color: {{ $profile->audio_title_color ?? '#ffffff' }};">{{ $profile->audio_title ?? '<3' }}</p>
                                    <div class="flex items-center gap-2 text-[10px] text-zinc-400 font-mono w-full">
                                        <span x-text="currentTimeFormatted">0:00</span>
                                        <div class="flex-1 h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all" style="background-color: {{ $profile->audio_progress_color ?? '#ffffff' }};" :style="{ width: progressPercent + '%' }"></div>
                                        </div>
                                        <span x-text="durationFormatted">1:05</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 text-xs flex-shrink-0" style="color: {{ $profile->audio_button_color ?? '#a1a1aa' }};">
                                <button @click="toggleAudio()" class="hover:opacity-80 transition cursor-pointer p-1">
                                    <i class="fa-solid" :class="isPlaying ? 'fa-pause' : 'fa-play'"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                @endforeach
            </div>
        @if($profile->show_card_container)
            </div>
        @endif

    </main>

    @if($profile->audio_enabled && $profile->audio_url)
        <audio id="bg-audio" loop src="{{ asset($profile->audio_url) }}"></audio>
    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dotBioProfile', (profileData) => ({
                profile: profileData,
                entered: {{ (($profile->audio_enabled && $profile->audio_url) || !empty($profile->background_url)) ? 'false' : 'true' }},
                isPlaying: false,
                isMuted: false,
                currentTimeFormatted: '0:00',
                durationFormatted: '0:00',
                progressPercent: 0,
                discordStatus: '{{ $initialDiscordStatus ?? "offline" }}',
                discordActivityText: '{{ addslashes($initialDiscordActivity ?? "") }}',
                discordLastSeen: '{{ $profile->last_seen_at ? ("last seen " . $profile->last_seen_at->diffForHumans()) : ($profile->discord_offline_text ?? "last seen recently") }}',
                discordDecorationUrl: '{{ $profile->discord_avatar_decoration_url ?? "" }}',
                discordTag: '{{ $profile->discord_tag ?? "" }}',
                discordClanBadgeUrl: '{{ $profile->discord_clan_badge_url ?? "" }}',

                nameTypewriterText: '',
                bioTypewriterText: '',

                init() {
                    const savedMute = localStorage.getItem('dotbio_audio_muted');
                    if (savedMute !== null) {
                        this.isMuted = savedMute === 'true';
                    }
                    this.setupAudioListeners();
                    if (this.profile.discord_id) {
                        this.initLanyard(this.profile.discord_id);
                    }
                    this.initTypewriters();
                },

                initTypewriters() {
                    const nameWords = @json($profile->display_name_typewriter_words ?: [$profile->display_name ?? 'User']);
                    if (this.profile.typewriter_enabled && nameWords.length > 0) {
                        this.runTypewriterLoop(nameWords, (txt) => this.nameTypewriterText = txt);
                    }

                    let bioWords = @json($profile->bio_typewriter_words ?: []);
                    const mainBio = {!! json_encode($profile->bio ?? '') !!};
                    if (mainBio && !bioWords.includes(mainBio)) {
                        bioWords = [mainBio, ...bioWords];
                    }
                    if (this.profile.bio_typewriter_enabled && bioWords.length > 0) {
                        this.runTypewriterLoop(bioWords, (txt) => this.bioTypewriterText = txt, this.profile.typewriter_speed || 90);
                    }
                },

                runTypewriterLoop(words, updateCallback, customSpeed = 90) {
                    let wordIndex = 0;
                    let charIndex = 0;
                    let isDeleting = false;

                    const type = () => {
                        const currentWord = words[wordIndex] || '';
                        if (isDeleting) {
                            charIndex--;
                        } else {
                            charIndex++;
                        }

                        updateCallback(currentWord.substring(0, charIndex));

                        let delay = isDeleting ? Math.max(15, Math.round(customSpeed * 0.45)) : customSpeed;

                        if (!isDeleting && charIndex === currentWord.length) {
                            delay = 2000;
                            isDeleting = true;
                        } else if (isDeleting && charIndex === 0) {
                            isDeleting = false;
                            wordIndex = (wordIndex + 1) % words.length;
                            delay = 400;
                        }

                        setTimeout(type, delay);
                    };

                    type();
                },

                enterSite() {
                    this.entered = true;

                    const video = document.getElementById('bg-video');
                    if (video) {
                        video.play().catch(() => {});
                    }

                    const audio = document.getElementById('bg-audio');
                    if (audio) {
                        audio.muted = this.isMuted;
                        audio.play().then(() => { this.isPlaying = true; }).catch(() => {});
                    }
                },

                initLanyard(discordId) {
                    const fetchStatus = () => {
                        fetch(`/api/discord-status/${discordId}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.status) {
                                    this.discordStatus = data.status;
                                    this.discordActivityText = data.activity || '';
                                    if (data.last_seen) {
                                        this.discordLastSeen = data.last_seen;
                                    }
                                    if (data.decoration_url) {
                                        this.discordDecorationUrl = data.decoration_url;
                                    }
                                    if (data.tag) {
                                        this.discordTag = data.tag;
                                    }
                                    if (data.clan_badge_url) {
                                        this.discordClanBadgeUrl = data.clan_badge_url;
                                    }
                                }
                            }).catch(() => {});
                    };

                    fetchStatus();
                    setInterval(fetchStatus, 5000);
                },

                updateLanyardData(d) {
                    this.discordStatus = d.discord_status || 'offline';
                    if (d.activities && d.activities.length > 0) {
                        const customAct = d.activities.find(a => a.type === 4 && a.state);
                        const gameAct = d.activities.find(a => a.type === 0);
                        const spotifyAct = d.spotify ? { state: `Listening to ${d.spotify.song}` } : null;
                        
                        const active = customAct || spotifyAct || gameAct || d.activities[0];
                        this.discordActivityText = active ? (active.state || active.details || active.name || '') : '';
                    } else {
                        this.discordActivityText = '';
                    }
                },

                setupAudioListeners() {
                    const audio = document.getElementById('bg-audio');
                    if (!audio) return;
                    audio.muted = this.isMuted;
                    audio.addEventListener('timeupdate', () => {
                        this.progressPercent = (audio.currentTime / audio.duration) * 100 || 0;
                        this.currentTimeFormatted = this.formatTime(audio.currentTime);
                        this.durationFormatted = this.formatTime(audio.duration || 0);
                    });
                },

                formatTime(seconds) {
                    const mins = Math.floor(seconds / 60);
                    const secs = Math.floor(seconds % 60);
                    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
                },

                enterSite() {
                    this.entered = true;
                    const audio = document.getElementById('bg-audio');
                    if (audio) {
                        audio.muted = this.isMuted;
                        audio.play().then(() => { this.isPlaying = true; }).catch(() => {});
                    }
                },

                toggleAudio() {
                    const audio = document.getElementById('bg-audio');
                    if (!audio) return;
                    if (this.isPlaying) {
                        audio.pause();
                        this.isPlaying = false;
                    } else {
                        audio.play();
                        this.isPlaying = true;
                    }
                },

                toggleMute() {
                    const audio = document.getElementById('bg-audio');
                    this.isMuted = !this.isMuted;
                    localStorage.setItem('dotbio_audio_muted', this.isMuted);
                    if (audio) {
                        audio.muted = this.isMuted;
                    }
                },

                trackClick(linkId) {
                    fetch(`/links/${linkId}/click`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                }
            }));
        });
    </script>
</body>
</html>
