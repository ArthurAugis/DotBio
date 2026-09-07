<x-admin.layout title="DotBio - Visual Builder & Media Manager" x-data="visualProfileBuilder({{ json_encode($profile) }})" @contextmenu.prevent="openContextMenu($event, 'canvas')" @keydown.window="handleKeyDown($event)" @close-inspector.window="inspector.show = false">
    <x-slot name="head">
        <style>
            .draggable-item {
            position: relative;
            outline: 2px dashed transparent;
            transition: outline 0.15s ease;
            cursor: pointer;
        }
        .draggable-item:hover {
            outline: 2px dashed #a855f7;
            border-radius: 14px;
        }

        .element-badge-label {
            position: absolute;
            top: -12px;
            right: 10px;
            background: #8b5cf6;
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 6px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            z-index: 20;
        }
        .draggable-item:hover .element-badge-label {
            opacity: 1;
        }

        .drag-handle {
            position: absolute;
            left: -26px;
            top: 50%;
            transform: translateY(-50%);
            color: #a855f7;
            opacity: 0;
            transition: opacity 0.15s ease;
        }
        .draggable-item:hover .drag-handle {
            opacity: 1;
        }

        .inspector-drawer {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 380px;
            z-index: 100;
            background: #14121b;
            border-left: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @media (max-width: 767.98px) {
            .inspector-drawer {
                width: 100% !important;
                left: 0 !important;
                right: 0 !important;
                top: 0 !important;
                bottom: 0 !important;
                border-left: none !important;
                z-index: 100 !important;
            }
        }

        .custom-card-container {
            transition: all 0.3s ease;
        }

        .preview-social-item {
            position: relative;
            display: inline-flex;
        }
        .preview-social-tooltip {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            padding: 3px 8px;
            border-radius: 6px;
            border-width: 1px;
            border-style: solid;
            font-size: 11px;
            line-height: 1.4;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.18s ease;
            z-index: 60;
        }
        .preview-social-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: var(--preview-tooltip-arrow, #8b5cf6);
        }
        .preview-social-item:hover .preview-social-tooltip {
            opacity: 1;
        }

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

        [contenteditable="true"]:focus {
            outline: 2px solid #a855f7 !important;
            border-radius: 8px;
            background: rgba(168, 85, 247, 0.1);
            padding: 2px 6px;
        }

        @if($profile->custom_font_url)
            @font-face {
                font-family: 'UserCustomFont';
                src: url('{{ asset($profile->custom_font_url) }}');
            }
        @endif
        </style>
    </x-slot>

    <div class="space-y-6 relative transition-all duration-300" :class="inspector.show ? 'lg:pr-[400px]' : ''">

        <div x-cloak x-show="isSaving" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-[-20px]"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-[-20px]"
             class="fixed top-6 right-6 z-50 bg-[#12101b]/95 border border-purple-500/40 text-white px-6 py-4 rounded-2xl shadow-2xl backdrop-blur-xl w-80 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-spinner fa-spin text-purple-400"></i>
                    <span>Uploading & Saving...</span>
                </span>
                <span class="text-xs font-mono text-purple-300 font-bold" x-text="uploadProgress + '%'"></span>
            </div>
            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-purple-600 to-indigo-500 rounded-full transition-all duration-150" :style="{ width: uploadProgress + '%' }"></div>
            </div>
        </div>

        <div x-cloak x-show="saveToast" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-[-20px]"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-[-20px]"
             class="fixed top-6 right-6 z-50 bg-purple-900/90 border border-purple-500/50 text-white px-5 py-3 rounded-2xl shadow-2xl backdrop-blur-xl flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-purple-400 text-lg"></i>
            <div>
                <p class="text-xs font-bold">Profile Saved Successfully!</p>
                <p class="text-[10px] text-purple-300">Your changes have been saved without page reload.</p>
            </div>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="fixed top-6 right-6 z-50 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 px-5 py-3 rounded-2xl shadow-2xl backdrop-blur-xl flex items-center gap-3 transition">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span class="text-xs font-bold">{{ session('success') }}</span>
                <button @click="show = false" class="text-emerald-400/60 hover:text-emerald-300 ml-2 cursor-pointer"><i class="fa-solid fa-xmark text-xs"></i></button>
            </div>
        @endif
        <div class="min-h-[620px] rounded-3xl p-8 flex flex-col items-center justify-center relative overflow-hidden border border-white/5 cursor-pointer"
             :style="{ 
                 backgroundColor: profile.background_color || '#080808', 
                 fontFamily: profile.custom_font_url ? 'UserCustomFont, sans-serif' : (profile.font_family + ', sans-serif') 
             }">

            <div class="absolute inset-0 pointer-events-none z-0" x-show="profile.background_url">
                <template x-if="profile.background_url && (profile.background_type === 'video' || profile.background_url.includes('.mp4') || profile.background_url.includes('.webm'))">
                    <video autoplay loop muted playsinline :key="profile.background_url" :src="profile.background_url.startsWith('http') || profile.background_url.startsWith('/') ? profile.background_url : '/' + profile.background_url" class="w-full h-full object-cover"></video>
                </template>
                <template x-if="profile.background_url && !(profile.background_type === 'video' || profile.background_url.includes('.mp4') || profile.background_url.includes('.webm'))">
                    <div class="w-full h-full bg-cover bg-center" :style="{ backgroundImage: 'url(' + (profile.background_url.startsWith('http') || profile.background_url.startsWith('/') ? profile.background_url : '/' + profile.background_url) + ')' }"></div>
                </template>
                <div class="absolute inset-0 bg-black/60"></div>
            </div>

            <template x-if="profile.show_mute_icon">
                <div class="absolute top-6 left-6 z-40" x-data="{ hovered: false }">
                    <button type="button"
                            @click.stop.prevent="openInspectorAtMouse('mute_icon', $event)" 
                            @contextmenu.prevent.stop="openContextMenu($event, 'mute_icon')"
                            @mouseenter="hovered = true"
                            @mouseleave="hovered = false"
                            class="transition-all duration-200 text-xl p-2.5 cursor-pointer focus:outline-none rounded-full relative z-40" 
                            :style="{
                                color: hovered ? (profile.mute_icon_hover_color || profile.mute_icon_color || '#ffffff') : (profile.mute_icon_color || '#ffffff'),
                                backgroundColor: 'transparent'
                            }"
                            title="Top Left Mute Speaker Icon (Click to edit colors & states)">
                        <i class="fa-solid fa-volume-high"></i>
                    </button>
                </div>
            </template>

            <div class="w-full transition-all duration-200 text-center space-y-4 relative cursor-pointer z-10"
                 :class="profile.show_card_container ? 'custom-card-container p-6' : 'p-2'"
                 x-data="{ rx: 0, ry: 0 }"
                 @mousemove="if (profile.tilt_3d_enabled) {
                     let r = $el.getBoundingClientRect();
                     let cx = r.left + r.width / 2;
                     let cy = r.top + r.height / 2;
                     let dx = ($event.clientX - cx) / (r.width / 2);
                     let dy = ($event.clientY - cy) / (r.height / 2);
                     rx = -dy * 15;
                     ry = dx * 15;
                 }"
                 @mouseleave="rx = 0; ry = 0; if (hoveredTarget === 'card') hoveredTarget = '';"
                 :style="{ 
                     maxWidth: (profile.card_width || 420) + 'px',
                     backgroundColor: profile.show_card_container ? getCardBgRgba() : 'transparent !important',
                     backdropFilter: profile.show_card_container ? ('blur(' + (profile.background_blur || 16) + 'px)') : 'none !important',
                     border: profile.show_card_container ? (((profile.border_width !== undefined && profile.border_width !== null) ? profile.border_width : 1) + 'px solid ' + (profile.border_color || 'rgba(255, 255, 255, 0.08)')) : 'none !important',
                     boxShadow: profile.show_card_container ? '0 25px 50px -12px rgba(0, 0, 0, 0.5)' : 'none !important',
                     borderRadius: (profile.border_radius || 24) + 'px',
                     transform: profile.tilt_3d_enabled ? ('perspective(1000px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)') : 'none',
                     transition: (rx === 0 && ry === 0) ? 'transform 0.5s ease, background-color 0.2s ease' : 'background-color 0.2s ease'
                 }"

                 @mouseenter="hoveredTarget = 'card'">

                <template x-if="profile.show_card_container && (profile.discord_banner_url || profile.banner_url)">
                    <div class="w-full h-32 bg-cover bg-center relative -mt-6 -mx-6 rounded-t-3xl overflow-hidden" 
                         :style="{ backgroundImage: 'url(' + (profile.discord_banner_url || profile.banner_url) + ')' }">
                        <template x-if="profile.discord_profile_effect_url">
                            <img :src="profile.discord_profile_effect_url" class="absolute inset-0 w-full h-full object-cover pointer-events-none">
                        </template>
                    </div>
                </template>
                
                <span x-show="profile.show_card_container" class="element-badge-label">Card Container</span>

                <div class="space-y-4 flex flex-col items-center" :class="(profile.show_card_container && (profile.discord_banner_url || profile.banner_url)) ? '-mt-12' : ''">

                    
                    <template x-for="(el, index) in elementOrder" :key="el">
                        <div x-show="isElementActive(el)" 
                             class="draggable-item w-full flex flex-col items-center relative group cursor-pointer"
                             @mouseenter="hoveredTarget = el"
                             @mouseleave="if (hoveredTarget === el) hoveredTarget = ''"
                             @contextmenu.prevent.stop="openContextMenu($event, el)">
                            
                            <div class="drag-handle flex flex-col gap-1 text-[10px]">
                                <button @click.stop="moveElementUp(index)" type="button" class="w-5 h-5 bg-purple-600 text-white rounded flex items-center justify-center hover:bg-purple-500 cursor-pointer" title="Move Up">▲</button>
                                <button @click.stop="moveElementDown(index)" type="button" class="w-5 h-5 bg-purple-600 text-white rounded flex items-center justify-center hover:bg-purple-500 cursor-pointer" title="Move Down">▼</button>
                            </div>

                            <template x-if="el === 'avatar' && profile.show_avatar">
                                <div class="mx-auto relative" 
                                     :class="'hover-anim-' + (profile.avatar_hover_effect || 'none')"
                                     :style="{ width: (profile.avatar_size || 96) + 'px', height: (profile.avatar_size || 96) + 'px' }">
                                    <span class="element-badge-label">Avatar Image</span>
                                    <img :src="getAvatarSrc()" 
                                         class="w-full h-full object-cover shadow-lg"
                                         :class="profile.avatar_glow ? 'avatar-neon-ring' : ''"
                                         :style="{ borderRadius: (profile.avatar_radius || 9999) + 'px', border: '2px solid ' + (profile.avatar_border_color || 'rgba(255,255,255,0.1)') }">
                                </div>
                            </template>

                            <template x-if="el === 'display_name' && profile.show_display_name">
                                <div class="space-y-1">
                                    <span class="element-badge-label">Display Name</span>
                                    <h1 contenteditable="true"
                                        @blur="profile.display_name = $el.innerText"
                                        class="font-bold tracking-tight text-3xl focus:outline-none cursor-text" 
                                        :style="{ color: profile.text_color || '#ffffff' }" 
                                        x-text="profile.display_name || '{{ auth()->user()->name }}'"></h1>
                                </div>
                            </template>

                            <template x-if="el === 'location' && profile.show_location">
                                <div>
                                    <span class="element-badge-label">Location Tag</span>
                                    <p class="text-xs font-medium flex items-center justify-center gap-1" :style="{ color: profile.location_text_color || '#a1a1aa' }">
                                        <i class="fa-solid fa-location-dot text-[10px]" :style="{ color: profile.location_icon_color || '#c084fc' }"></i>
                                        <span contenteditable="true"
                                              @blur="profile.location = $el.innerText"
                                              class="focus:outline-none cursor-text"
                                              x-text="profile.location || 'Location'"></span>
                                    </p>
                                </div>
                            </template>

                            <template x-if="el === 'bio' && profile.show_bio">
                                <div class="w-full">
                                    <span class="element-badge-label">Bio</span>
                                    <p contenteditable="true"
                                       @blur="profile.bio = $el.innerText"
                                       class="font-normal leading-relaxed focus:outline-none cursor-text w-full" 
                                       :style="{ 
                                           color: profile.bio_text_color || '#d4d4d8',
                                           fontSize: (profile.bio_font_size || 14) + 'px',
                                           textAlign: profile.bio_align || 'center'
                                       }"
                                       x-text="profile.bio || 'Add your bio here...'"></p>
                                </div>
                            </template>

                            <template x-if="el === 'discord' && profile.show_lanyard_card">
                                <div class="w-full flex justify-center py-2"
                                     :style="{ 
                                         marginTop: (profile.discord_status_scale > 100 ? ((profile.discord_status_scale - 100) * 0.25) : 0) + 'px',
                                         marginBottom: (profile.discord_status_scale > 100 ? ((profile.discord_status_scale - 100) * 0.25) : 0) + 'px'
                                     }">
                                    <span class="element-badge-label">Discord Widget</span>
                                    <div class="discord-card flex items-center gap-3 px-4 py-2.5 rounded-2xl inline-flex transition-transform origin-center"
                                         :class="[profile.discord_glow ? 'avatar-neon-ring' : '', 'hover-anim-' + (profile.discord_hover_effect || 'none')]"
                                         :style="{ 
                                             transform: 'scale(' + ((profile.discord_status_scale || 100) / 100) + ')', 
                                             backgroundColor: (() => { let h=(profile.discord_card_bg_color||'#000000').replace('#',''); if(h.length===3)h=h[0]+h[0]+h[1]+h[1]+h[2]+h[2]; return 'rgba('+parseInt(h.substr(0,2),16)+','+parseInt(h.substr(2,2),16)+','+parseInt(h.substr(4,2),16)+','+(profile.discord_opacity??1)+')'; })(),
                                             border: profile.discord_border_color ? (((profile.discord_border_width !== undefined && profile.discord_border_width !== null) ? profile.discord_border_width : 1) + 'px solid ' + profile.discord_border_color) : '1px solid rgba(255, 255, 255, 0.05)'
                                         }">

                                         <div class="relative w-8 h-8 flex-shrink-0">
                                             <img :src="getAvatarSrc()" class="w-full h-full object-cover rounded-full">
                                             <template x-if="profile.show_discord_decoration && profile.discord_avatar_decoration_url">
                                                 <img :src="profile.discord_avatar_decoration_url" class="absolute -top-1 -left-1 w-10 h-10 pointer-events-none z-10 max-w-none">
                                             </template>
                                             <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border border-black z-20"
                                                   :style="{ backgroundColor: profile.discord_status_color || null }"
                                                   :class="!profile.discord_status_color ? {
                                                       'bg-emerald-500': discordStatus === 'online',
                                                       'bg-amber-500': discordStatus === 'idle',
                                                       'bg-rose-500': discordStatus === 'dnd',
                                                       'bg-zinc-500': discordStatus === 'offline'
                                                   } : ''"></span>
                                         </div>
                                         <div class="text-left">
                                             <div class="flex items-center gap-1.5">
                                                 <p class="text-xs font-bold leading-none" :style="{ color: profile.discord_name_color || '#ffffff' }" x-text="profile.display_name || '{{ auth()->user()->name }}'"></p>
                                                 <template x-if="profile.show_discord_tag && profile.discord_tag">
                                                     <span class="bg-black/60 border border-white/10 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full flex items-center gap-1 leading-none">
                                                         <template x-if="profile.discord_clan_badge_url">
                                                             <img :src="profile.discord_clan_badge_url" class="w-3 h-3 object-contain rounded-sm">
                                                         </template>
                                                         <span x-text="profile.discord_tag"></span>
                                                     </span>
                                                 </template>
                                             </div>
                                             <p x-show="discordStatus === 'offline' || discordActivityText"
                                                class="text-[10px] mt-1 font-mono" 
                                                :style="{ color: profile.discord_text_color || '#a1a1aa' }"
                                                x-text="discordStatus === 'offline' ? discordLastSeen : discordActivityText"></p>

                                         </div>

                                    </div>
                                </div>
                            </template>

                            <template x-if="el === 'socials' && profile.show_social_links">
                                <div class="w-full flex items-center justify-center gap-3 flex-wrap pt-1">
                                    <span class="element-badge-label">Social Links</span>
                                    @foreach($profile->links as $link)
                                        <div class="preview-social-item">
                                            <div class="icon-badge p-1 text-xl flex items-center justify-center transition cursor-pointer"
                                                 :class="'hover-anim-' + ('{{ $link->hover_effect ?? '' }}' ? '{{ $link->hover_effect }}' : (profile.socials_hover_effect || 'none'))">
                                                <i class="{{ $link->icon_class }}" style="color: {{ $link->color ?: ($profile->icon_color ?? '#ffffff') }};"></i>
                                            </div>
                                            <span class="preview-social-tooltip"
                                                  x-show="profile.show_social_tooltips"
                                                  :style="{
                                                      background: getTooltipBgRgba(),
                                                      color: profile.social_tooltip_text_color || '#fafafa',
                                                      borderColor: profile.social_tooltip_border_color || profile.accent_color || '#8b5cf6',
                                                      borderWidth: ((profile.social_tooltip_border_width ?? 1) + 'px'),
                                                      '--preview-tooltip-arrow': (Number(profile.social_tooltip_border_width ?? 1) > 0
                                                          ? (profile.social_tooltip_border_color || profile.accent_color || '#8b5cf6')
                                                          : getTooltipBgRgba())
                                                  }">{{ $link->title }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </template>

                            <template x-if="el === 'audio' && profile.show_audio_player">
                                <div class="player-card rounded-2xl p-3 w-full flex items-center justify-between gap-3 shadow-2xl mt-2" 
                                     :class="[profile.audio_glow ? 'avatar-neon-ring' : '', 'hover-anim-' + (profile.audio_hover_effect || 'none')]"
                                     :style="{ 
                                         backgroundColor: (() => { let h=(profile.audio_card_bg_color||'#000000').replace('#',''); if(h.length===3)h=h[0]+h[0]+h[1]+h[1]+h[2]+h[2]; return 'rgba('+parseInt(h.substr(0,2),16)+','+parseInt(h.substr(2,2),16)+','+parseInt(h.substr(4,2),16)+','+(profile.audio_opacity??1)+')'; })(),
                                         border: profile.audio_border_color ? (((profile.audio_border_width !== undefined && profile.audio_border_width !== null) ? profile.audio_border_width : 1) + 'px solid ' + profile.audio_border_color) : '1px solid rgba(255, 255, 255, 0.08)'
                                     }">

                                    <span class="element-badge-label">Audio Player</span>
                                    <div class="flex items-center gap-3 flex-1 overflow-hidden">
                                        <img :src="getAudioCoverSrc()" class="w-10 h-10 object-cover flex-shrink-0" style="border-radius: 12px;">
                                        <div class="text-left flex-1 min-w-0">
                                            <p class="text-xs font-bold truncate" :style="{ color: profile.audio_title_color || '#ffffff' }" x-text="profile.audio_title || '<3'"></p>
                                            <div class="flex items-center gap-2 text-[10px] text-zinc-400 font-mono w-full">
                                                <span>0:00</span>
                                                <div class="flex-1 h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full w-1/3" :style="{ backgroundColor: profile.audio_progress_color || '#ffffff' }"></div>
                                                </div>
                                                <span>1:05</span>
                                            </div>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-play text-xs pr-2 flex-shrink-0" :style="{ color: profile.audio_button_color || '#a1a1aa' }"></i>
                                </div>
                            </template>

                            <template x-if="el === 'views' && profile.show_views_count">
                                <div class="pt-1 flex items-center justify-center gap-1.5 text-xs font-mono" :style="{ color: profile.views_text_color || '#71717a' }">
                                    <span class="element-badge-label">Views Counter</span>
                                    <i class="fa-regular fa-eye text-[11px]" :style="{ color: profile.views_icon_color || '#71717a' }"></i>
                                    <span>0 views</span>
                                </div>
                            </template>

                        </div>
                    </template>

                </div>

            </div>

        </div>

        <div class="w-full bg-[#14121b] border border-white/10 rounded-2xl p-3 flex flex-wrap items-center justify-between gap-3 shadow-xl">
            <div class="flex items-center gap-2 overflow-x-auto text-xs font-semibold py-1">
                <span class="text-zinc-400 text-[11px] font-mono mr-2">QUICK EDIT:</span>

                <x-admin.builder.action x-show="profile.show_card_container" @click="openInspector('card')" icon="fa-regular fa-square text-purple-400" label="Card Container" />
                <x-admin.builder.action x-show="profile.show_avatar" @click="openInspector('avatar')" icon="fa-regular fa-user text-purple-400" label="Avatar" />
                <x-admin.builder.action x-show="profile.show_display_name" @click="openInspector('display_name')" icon="fa-solid fa-font text-sky-400" label="Display Name" />
                <x-admin.builder.action x-show="profile.show_location" @click="openInspector('location')" icon="fa-solid fa-location-dot text-emerald-400" label="Location" />
                <x-admin.builder.action x-show="profile.show_bio" @click="openInspector('bio')" icon="fa-solid fa-align-left text-amber-400" label="Bio" />
                <x-admin.builder.action x-show="profile.show_lanyard_card" @click="openInspector('discord')" icon="fa-brands fa-discord text-indigo-400" label="Discord" />
                <x-admin.builder.action x-show="profile.show_social_links" @click="openInspector('socials')" icon="fa-solid fa-link text-emerald-400" label="Socials" />
                <x-admin.builder.action x-show="profile.show_audio_player" @click="openInspector('audio')" icon="fa-solid fa-music text-rose-400" label="Audio" />
                <x-admin.builder.action x-show="profile.show_views_count" @click="openInspector('views')" icon="fa-regular fa-eye text-teal-400" label="Views" />
                <x-admin.builder.action @click="openInspector('enter_screen')" icon="fa-solid fa-arrow-right-to-bracket text-amber-400" label="Enter Screen" />
            </div>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" type="button" class="px-4 py-2 bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 text-xs font-bold rounded-xl border border-purple-500/30 flex items-center gap-2 cursor-pointer transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Hidden Element</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>

                <div x-show="open" x-cloak class="absolute right-0 bottom-full mb-2 w-52 bg-[#181522] border border-white/15 rounded-2xl p-2 shadow-2xl z-50 space-y-1">
                    <x-admin.builder.action x-show="!profile.show_card_container" @click="profile.show_card_container = true; open = false; openInspector('card');" mode="menu" icon="fa-regular fa-square text-purple-400" label="Card Container" />
                    <x-admin.builder.action x-show="!profile.show_avatar" @click="profile.show_avatar = true; open = false; openInspector('avatar');" mode="menu" icon="fa-regular fa-user text-purple-400" label="Avatar Image" />
                    <x-admin.builder.action x-show="!profile.show_display_name" @click="profile.show_display_name = true; open = false; openInspector('display_name');" mode="menu" icon="fa-solid fa-font text-sky-400" label="Display Name" />
                    <x-admin.builder.action x-show="!profile.show_location" @click="profile.show_location = true; open = false; openInspector('location');" mode="menu" icon="fa-solid fa-location-dot text-emerald-400" label="Location Tag" />
                    <x-admin.builder.action x-show="!profile.show_bio" @click="profile.show_bio = true; open = false; openInspector('bio');" mode="menu" icon="fa-solid fa-align-left text-amber-400" label="Bio Description" />
                    <x-admin.builder.action x-show="!profile.show_lanyard_card" @click="profile.show_lanyard_card = true; open = false; openInspector('discord');" mode="menu" icon="fa-brands fa-discord text-indigo-400" label="Discord Account" />
                    <x-admin.builder.action x-show="!profile.show_social_links" @click="profile.show_social_links = true; open = false; openInspector('socials');" mode="menu" icon="fa-solid fa-link text-emerald-400" label="Social Links" />
                    <x-admin.builder.action x-show="!profile.show_mute_icon" @click="profile.show_mute_icon = true; open = false; openInspector('mute_icon');" mode="menu" icon="fa-solid fa-volume-high text-pink-400" label="Top Mute Icon" />
                    <x-admin.builder.action x-show="!profile.show_audio_player" @click="profile.show_audio_player = true; open = false; openInspector('audio');" mode="menu" icon="fa-solid fa-music text-rose-400" label="Audio Player" />
                    <x-admin.builder.action x-show="!profile.show_views_count" @click="profile.show_views_count = true; open = false; openInspector('views');" mode="menu" icon="fa-regular fa-eye text-teal-400" label="Views Counter" />
                </div>
            </div>
        </div>

        <div class="w-full flex items-stretch justify-between gap-4 pt-2">
            <button @click="mediaModal = true" type="button" class="px-6 py-3.5 bg-white/5 hover:bg-white/10 text-white text-sm font-bold rounded-2xl transition border border-white/10 cursor-pointer flex items-center justify-center gap-2.5">
                <i class="fa-solid fa-cloud-arrow-up text-purple-400 text-base"></i>
                <span>Upload Media & Fonts</span>
            </button>

            <button @click="submitSaveForm()" type="button" class="flex-1 py-3.5 bg-purple-600 hover:bg-purple-500 text-white text-sm font-bold rounded-2xl transition shadow-xl shadow-purple-600/25 cursor-pointer flex items-center justify-center gap-2.5">
                <i class="fa-solid fa-floppy-disk text-base"></i>
                <span>Save Profile</span>
            </button>
        </div>

        <form action="{{ route('admin.customize.update') }}" method="POST" enctype="multipart/form-data" id="saveForm" class="hidden">
            @csrf
            <input type="hidden" name="display_name" :value="profile.display_name">
            <input type="hidden" name="bio" :value="profile.bio">
            <input type="hidden" name="location" :value="profile.location">
            <input type="hidden" name="card_opacity" :value="profile.card_opacity">
            <input type="hidden" name="background_blur" :value="profile.background_blur">
            <input type="hidden" name="card_width" :value="profile.card_width">
            <input type="hidden" name="avatar_size" :value="profile.avatar_size">
            <input type="hidden" name="border_radius" :value="profile.border_radius">
            <input type="hidden" name="avatar_radius" :value="profile.avatar_radius">
            <input type="hidden" name="accent_color" :value="profile.accent_color">
            <input type="hidden" name="text_color" :value="profile.text_color">
            <input type="hidden" name="background_color" :value="profile.background_color">
            <input type="hidden" name="border_color" :value="profile.border_color">
            <input type="hidden" name="border_width" :value="profile.border_width">
            <input type="hidden" name="icon_color" :value="profile.icon_color">
            <input type="hidden" name="font_family" :value="profile.font_family">
            <input type="hidden" name="hover_animation" :value="profile.hover_animation">
            <input type="hidden" name="hover_zoom_scale" :value="profile.hover_zoom_scale">
            <input type="hidden" name="hover_glow_color" :value="profile.hover_glow_color">
            <input type="hidden" name="avatar_glow_color" :value="profile.avatar_glow_color">
            <input type="hidden" name="discord_glow_color" :value="profile.discord_glow_color">
            <input type="hidden" name="audio_glow_color" :value="profile.audio_glow_color">
            <input type="hidden" name="username_glow_color" :value="profile.username_glow_color">
            <input type="hidden" name="socials_glow_color" :value="profile.socials_glow_color">
            <input type="hidden" name="hover_lift_amount" :value="profile.hover_lift_amount">
            <input type="hidden" name="hover_bounce_intensity" :value="profile.hover_bounce_intensity">
            <input type="hidden" name="discord_offline_text" :value="profile.discord_offline_text">
            <input type="hidden" name="discord_status_scale" :value="profile.discord_status_scale">
            <input type="hidden" name="audio_title" :value="profile.audio_title">
            <input type="hidden" name="element_order" :value="JSON.stringify(elementOrder)">
            <input type="hidden" name="background_type" :value="profile.background_type">
            
            <input type="hidden" name="bio_font_size" :value="profile.bio_font_size">
            <input type="hidden" name="bio_align" :value="profile.bio_align">
            <input type="hidden" name="typewriter_speed" :value="profile.typewriter_speed">
            <input type="hidden" name="hover_bg_color" :value="profile.hover_bg_color">
            <input type="hidden" name="selection_bg_color" :value="profile.selection_bg_color">
            <input type="hidden" name="selection_text_color" :value="profile.selection_text_color">
            <input type="hidden" name="audio_progress_color" :value="profile.audio_progress_color">
            <input type="hidden" name="audio_title_color" :value="profile.audio_title_color">
            <input type="hidden" name="audio_card_bg_color" :value="profile.audio_card_bg_color">
            <input type="hidden" name="audio_button_color" :value="profile.audio_button_color">
            <input type="hidden" name="bio_text_color" :value="profile.bio_text_color">
            <input type="hidden" name="location_text_color" :value="profile.location_text_color">
            <input type="hidden" name="location_icon_color" :value="profile.location_icon_color">
            <input type="hidden" name="discord_text_color" :value="profile.discord_text_color">
            <input type="hidden" name="discord_card_bg_color" :value="profile.discord_card_bg_color">
            <input type="hidden" name="discord_name_color" :value="profile.discord_name_color">
            <input type="hidden" name="discord_status_color" :value="profile.discord_status_color">
            <input type="hidden" name="avatar_border_color" :value="profile.avatar_border_color">
            <input type="hidden" name="views_text_color" :value="profile.views_text_color">
            <input type="hidden" name="views_icon_color" :value="profile.views_icon_color">
            <input type="hidden" name="mute_icon_color" :value="profile.mute_icon_color">
            <input type="hidden" name="mute_icon_bg_color" :value="profile.mute_icon_bg_color">
            <input type="hidden" name="mute_icon_unmute_color" :value="profile.mute_icon_unmute_color">
            <input type="hidden" name="mute_icon_hover_color" :value="profile.mute_icon_hover_color">
            <input type="hidden" name="mute_icon_hover_unmute_color" :value="profile.mute_icon_hover_unmute_color">
            <input type="hidden" name="mute_icon_hover_bg_color" :value="profile.mute_icon_hover_bg_color">
            <input type="hidden" name="enter_text_color" :value="profile.enter_text_color">
            <input type="hidden" name="enter_bg_color" :value="profile.enter_bg_color">
            <input type="hidden" name="enter_image_url" :value="profile.enter_image_url">
            <input type="hidden" name="audio_border_color" :value="profile.audio_border_color">
            <input type="hidden" name="audio_border_width" :value="profile.audio_border_width">
            <input type="hidden" name="audio_opacity" :value="profile.audio_opacity">
            <input type="hidden" name="audio_glow" :value="profile.audio_glow ? '1' : ''">
            <input type="hidden" name="discord_border_color" :value="profile.discord_border_color">
            <input type="hidden" name="discord_border_width" :value="profile.discord_border_width">
            <input type="hidden" name="discord_opacity" :value="profile.discord_opacity">
            <input type="hidden" name="discord_glow" :value="profile.discord_glow ? '1' : ''">
            <input type="hidden" name="entry_animation" :value="profile.entry_animation">
            <input type="hidden" name="typewriter_enabled" :value="profile.typewriter_enabled ? '1' : ''">
            <input type="hidden" name="display_name_typewriter_words" :value="JSON.stringify(profile.display_name_typewriter_words || [])">
            <input type="hidden" name="bio_typewriter_enabled" :value="profile.bio_typewriter_enabled ? '1' : ''">
            <input type="hidden" name="bio_typewriter_words" :value="JSON.stringify(profile.bio_typewriter_words || [])">
            <input type="hidden" name="tilt_3d_enabled" :value="profile.tilt_3d_enabled ? '1' : ''">
            <input type="hidden" name="avatar_hover_effect" :value="profile.avatar_hover_effect">
            <input type="hidden" name="discord_hover_effect" :value="profile.discord_hover_effect">
            <input type="hidden" name="audio_hover_effect" :value="profile.audio_hover_effect">
            <input type="hidden" name="socials_hover_effect" :value="profile.socials_hover_effect">
            <input type="hidden" name="discord_tag" :value="profile.discord_tag">

            
            <input type="hidden" name="show_card_container" :value="profile.show_card_container ? '1' : ''">
            <input type="hidden" name="show_avatar" :value="profile.show_avatar ? '1' : ''">
            <input type="hidden" name="show_display_name" :value="profile.show_display_name ? '1' : ''">
            <input type="hidden" name="show_location" :value="profile.show_location ? '1' : ''">
            <input type="hidden" name="show_bio" :value="profile.show_bio ? '1' : ''">
            <input type="hidden" name="show_lanyard_card" :value="profile.show_lanyard_card ? '1' : ''">
            <input type="hidden" name="show_social_links" :value="profile.show_social_links ? '1' : ''">
            <input type="hidden" name="show_audio_player" :value="profile.show_audio_player ? '1' : ''">
            <input type="hidden" name="show_mute_icon" :value="profile.show_mute_icon ? '1' : ''">
            <input type="hidden" name="show_discord_tag" :value="profile.show_discord_tag ? '1' : ''">
            <input type="hidden" name="show_views_count" :value="profile.show_views_count ? '1' : ''">
            <input type="hidden" name="show_social_tooltips" :value="profile.show_social_tooltips ? '1' : ''">
            <input type="hidden" name="social_tooltip_bg_color" :value="profile.social_tooltip_bg_color">
            <input type="hidden" name="social_tooltip_opacity" :value="profile.social_tooltip_opacity">
            <input type="hidden" name="social_tooltip_text_color" :value="profile.social_tooltip_text_color">
            <input type="hidden" name="social_tooltip_border_color" :value="profile.social_tooltip_border_color">
            <input type="hidden" name="social_tooltip_border_width" :value="profile.social_tooltip_border_width">

            <input type="file" id="avatarFileInput" name="avatar_file" @change="submitSaveForm()">
            <input type="file" id="secondaryAvatarFileInput" name="secondary_avatar_file" @change="submitSaveForm()">
            <input type="file" id="enterImageFileInput" name="enter_image" @change="submitSaveForm()">
            <input type="file" id="bgFileInput" name="background_file" @change="submitSaveForm()">

            <input type="file" id="bannerFileInput" name="banner_file" @change="submitSaveForm()">
            <input type="file" id="discordBannerFileInput" name="discord_banner_file" @change="submitSaveForm()">
            <input type="file" id="discordProfileEffectFileInput" name="discord_profile_effect_file" @change="submitSaveForm()">
            <input type="file" id="audioFileInput" name="audio_file" @change="submitSaveForm()">
            <input type="file" id="audioCoverFileInput" name="audio_cover_file" @change="submitSaveForm()">
            <input type="file" id="cursorFileInput" name="custom_cursor_file" @change="submitSaveForm()">
            <input type="file" id="fontFileInput" name="custom_font_file" @change="submitSaveForm()">
        </form>

        <div x-show="mediaModal" x-cloak @click.away="mediaModal = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
            <div class="bg-[#14121b] border border-white/10 rounded-3xl p-6 w-full max-w-2xl space-y-6 shadow-2xl overflow-y-auto max-h-[90vh]">
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <h2 class="text-base font-bold font-space text-white flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up text-purple-400"></i>
                        Media & Custom File Upload Center
                    </h2>
                    <button @click="mediaModal = false" type="button" class="text-zinc-500 hover:text-white cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <x-profile.upload-card
                        icon="fa-solid fa-font"
                        title="Custom Font (.TTF / .OTF)"
                        description="Upload your custom typography font file."
                        button-text="Choose Font File"
                        button-action="document.getElementById('fontFileInput').click()"
                        title-class="text-purple-400"
                        button-class="bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border-purple-500/30"
                    />

                    <x-profile.upload-card
                        icon="fa-solid fa-film"
                        title="Background Media (Video / Image)"
                        :description="'MP4, WEBM, PNG, JPG (Max ' . $maxUploadSizeMB . 'MB).'"
                        button-text="Choose Video / Image"
                        button-action="document.getElementById('bgFileInput').click()"
                        title-class="text-sky-400"
                        button-class="bg-sky-600/20 hover:bg-sky-600/30 text-sky-300 border-sky-500/30"
                    >
                        <template x-if="profile.background_url">
                            <p class="text-[10px] text-emerald-400 font-mono">✓ Active Background Uploaded</p>
                        </template>
                    </x-profile.upload-card>

                    <x-profile.upload-card
                        icon="fa-solid fa-music"
                        title="Audio File & Cover Art"
                        :description="'Upload audio track (Max ' . $maxUploadSizeMB . 'MB) & album cover image.'"
                        button-text="Audio File"
                        button-action="document.getElementById('audioFileInput').click()"
                        title-class="text-rose-400"
                        button-class="bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border-rose-500/30"
                    >
                        <div class="flex gap-2">
                            <button @click="document.getElementById('audioCoverFileInput').click()" type="button" class="w-full py-2 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 font-bold rounded-xl border border-rose-500/30 cursor-pointer text-center">
                                Cover Image
                            </button>
                        </div>
                        <template x-if="profile.audio_url">
                            <p class="text-[10px] text-emerald-400 font-mono">✓ Audio File Uploaded</p>
                        </template>
                    </x-profile.upload-card>

                    <x-profile.upload-card
                        icon="fa-solid fa-arrow-pointer"
                        title="Custom Cursor File"
                        description="Upload custom cursor pointer file."
                        button-text="Choose Cursor File"
                        button-action="document.getElementById('cursorFileInput').click()"
                        title-class="text-amber-500"
                        button-class="bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 border-amber-500/30"
                    />
                </div>

                <div class="flex justify-end border-t border-white/10 pt-4">
                    <button @click="mediaModal = false" type="button" class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-xl text-xs cursor-pointer">Done</button>
                </div>
            </div>
        </div>

        <div x-show="inspector.show" x-cloak
             class="inspector-drawer">

            <div class="p-4 border-b border-white/10 flex items-center justify-between bg-black/40">
                <h3 class="text-sm font-bold font-space text-white flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-purple-400"></i>
                    <span class="capitalize" x-text="'Edit ' + (inspector.target ? inspector.target.replace('_', ' ') : 'Element')"></span>
                </h3>
                <div class="flex items-center gap-2">
                    <button x-show="inspector.target && inspector.target !== 'enter_screen'" 
                            @click="toggleElementVisibility(inspector.target); inspector.show = false;" 
                            type="button" 
                            title="Remove / Hide element from card"
                            class="px-2.5 py-1 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 hover:text-rose-300 transition text-[11px] font-bold flex items-center gap-1.5 cursor-pointer border border-rose-500/20">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Remove</span>
                    </button>
                    <button @click="inspector.show = false" type="button" class="w-7 h-7 rounded-full bg-white/5 hover:bg-white/15 flex items-center justify-center text-zinc-400 hover:text-white transition cursor-pointer">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-4 text-xs custom-scrollbar">

                
                <template x-if="inspector.target === 'location'">
                    @include('admin.customize-panels.location')
                </template>

                <template x-if="inspector.target === 'display_name'">
                    @include('admin.customize-panels.display-name')
                </template>

                <template x-if="inspector.target === 'avatar'">
                    @include('admin.customize-panels.avatar')
                </template>

                <template x-if="inspector.target === 'bio'">
                    @include('admin.customize-panels.bio')
                </template>

                <template x-if="inspector.target === 'socials'">
                    @include('admin.customize-panels.socials')
                </template>

                <template x-if="inspector.target === 'discord'">
                    @include('admin.customize-panels.discord')
                </template>

                <template x-if="inspector.target === 'mute_icon'">
                    @include('admin.customize-panels.mute-icon')
                </template>

                <template x-if="inspector.target === 'enter_screen'">
                    @include('admin.customize-panels.enter-screen')
                </template>

                <template x-if="inspector.target === 'audio'">
                    @include('admin.customize-panels.audio')
                </template>

                <template x-if="inspector.target === 'views'">
                    @include('admin.customize-panels.views')
                </template>

                <template x-if="inspector.target === 'card'">
                    @include('admin.customize-panels.card')
                </template>

            </div>

        </div>

    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('visualProfileBuilder', (initialProfile) => ({
                profile: initialProfile,
                showSubmenu: false,
                mediaModal: false,
                elementOrder: initialProfile.element_order || ['avatar', 'display_name', 'location', 'bio', 'discord', 'socials', 'audio', 'views'],
                hoveredTarget: '',
                discordStatus: 'offline',
                discordActivityText: '',
                isSaving: false,
                uploadProgress: 0,
                saveToast: false,
                contextMenu: { show: false, x: 0, y: 0, target: '' },
                inspector: { show: false, x: 0, y: 0, target: '' },

                init() {
                    if (!this.profile.hover_zoom_scale) this.profile.hover_zoom_scale = 1.08;
                    if (!this.profile.hover_glow_color) this.profile.hover_glow_color = '#8b5cf6';
                    if (!this.profile.hover_lift_amount) this.profile.hover_lift_amount = 10;
                    if (!this.profile.hover_bounce_intensity) this.profile.hover_bounce_intensity = 8;
                    if (!this.profile.text_color) this.profile.text_color = '#ffffff';
                    if (!this.profile.location_text_color) this.profile.location_text_color = '#a1a1aa';
                    if (!this.profile.location_icon_color) this.profile.location_icon_color = '#c084fc';
                    if (!this.profile.bio_font_size) this.profile.bio_font_size = 14;
                    if (!this.profile.bio_align) this.profile.bio_align = 'center';
                    if (!this.profile.typewriter_speed) this.profile.typewriter_speed = 90;
                    if (!this.profile.bio_text_color) this.profile.bio_text_color = '#d4d4d8';
                    if (!this.profile.discord_name_color) this.profile.discord_name_color = '#ffffff';
                    if (!this.profile.discord_text_color) this.profile.discord_text_color = '#a1a1aa';
                    if (!this.profile.discord_card_bg_color) this.profile.discord_card_bg_color = '#000000';
                    if (!this.profile.audio_title_color) this.profile.audio_title_color = '#ffffff';
                    if (!this.profile.audio_progress_color) this.profile.audio_progress_color = '#ffffff';
                    if (!this.profile.audio_card_bg_color) this.profile.audio_card_bg_color = '#000000';
                    if (!this.profile.audio_button_color) this.profile.audio_button_color = '#a1a1aa';
                    if (!this.profile.views_text_color) this.profile.views_text_color = '#71717a';
                    if (!this.profile.views_icon_color) this.profile.views_icon_color = '#71717a';
                    if (!this.profile.mute_icon_color) this.profile.mute_icon_color = '#ffffff';
                    if (!this.profile.mute_icon_unmute_color) this.profile.mute_icon_unmute_color = '#ffffff';
                    if (!this.profile.mute_icon_hover_color) this.profile.mute_icon_hover_color = '#ffffff';
                    if (!this.profile.mute_icon_hover_unmute_color) this.profile.mute_icon_hover_unmute_color = '#ffffff';
                    if (!this.profile.enter_text_color) this.profile.enter_text_color = '#a1a1aa';
                    if (!this.profile.enter_bg_color) this.profile.enter_bg_color = '#000000';
                    if (!this.profile.audio_border_color) this.profile.audio_border_color = '#ffffff';
                    if (this.profile.audio_opacity === undefined || this.profile.audio_opacity === null) this.profile.audio_opacity = 1;
                    if (!this.profile.discord_border_color) this.profile.discord_border_color = '#ffffff';
                    if (this.profile.discord_opacity === undefined || this.profile.discord_opacity === null) this.profile.discord_opacity = 1;
                    if (!this.profile.avatar_border_color) this.profile.avatar_border_color = '#ffffff';

                    if (!this.profile.card_bg_color) this.profile.card_bg_color = '#121017';
                    if (!this.profile.border_color) this.profile.border_color = '#ffffff';
                    if (!this.profile.background_color) this.profile.background_color = '#09090b';

                    if (this.profile.discord_id) {
                        this.initLanyard(this.profile.discord_id);
                    }
                },

                getCardBgRgba() {
                    let hex = (this.profile.card_bg_color || '#121017').replace('#', '');
                    if (hex.length === 3) {
                        hex = hex[0]+hex[0] + hex[1]+hex[1] + hex[2]+hex[2];
                    }
                    let r = parseInt(hex.substring(0, 2), 16) || 18;
                    let g = parseInt(hex.substring(2, 4), 16) || 16;
                    let b = parseInt(hex.substring(4, 6), 16) || 23;
                    let a = this.profile.card_opacity !== undefined && this.profile.card_opacity !== null ? this.profile.card_opacity : 1;
                    return `rgba(${r}, ${g}, ${b}, ${a})`;
                },

                getTooltipBgRgba() {
                    let hex = (this.profile.social_tooltip_bg_color || '#0a0a0c').replace('#', '');
                    if (hex.length === 3) {
                        hex = hex[0]+hex[0] + hex[1]+hex[1] + hex[2]+hex[2];
                    }
                    let r = parseInt(hex.substring(0, 2), 16) || 0;
                    let g = parseInt(hex.substring(2, 4), 16) || 0;
                    let b = parseInt(hex.substring(4, 6), 16) || 0;
                    let a = this.profile.social_tooltip_opacity !== undefined && this.profile.social_tooltip_opacity !== null ? this.profile.social_tooltip_opacity : 1;
                    return `rgba(${r}, ${g}, ${b}, ${a})`;
                },

                getAvatarSrc() {

                    if (this.profile.avatar_url) return this.profile.avatar_url;
                    return '{{ auth()->user()->avatar ?? 'https://api.dicebear.com/7.x/bottts/svg?seed=dotbio' }}';
                },

                getAudioCoverSrc() {
                    if (this.profile.audio_cover_url) return this.profile.audio_cover_url;
                    return this.getAvatarSrc();
                },

                initLanyard(discordId) {
                    const fetchStatus = () => {
                        fetch(`/api/discord-status/${discordId}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.status) {
                                    this.discordStatus = data.status;
                                    if (data.activity) {
                                        this.discordActivityText = data.activity;
                                    }
                                }
                            }).catch(() => {});
                    };

                    fetchStatus();
                    setInterval(fetchStatus, 10000);
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

                isSaving: false,
                saveToast: false,

                submitSaveForm() {
                    const form = document.getElementById('saveForm');
                    if (!form) return;

                    this.isSaving = true;
                    this.uploadProgress = 10;

                    const formData = new FormData(form);
                    const targetAction = form.getAttribute('action') || '/admin/customize';

                    fetch(targetAction, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(async (response) => {
                        this.isSaving = false;
                        this.uploadProgress = 100;

                        let data = null;
                        try {
                            data = await response.json();
                        } catch (e) {}

                        if (response.ok && data && (data.success || data.profile)) {
                            this.saveToast = true;
                            setTimeout(() => { this.saveToast = false; }, 3000);
                            form.querySelectorAll('input[type="file"]').forEach(input => input.value = '');

                            if (data.profile) {
                                Object.assign(this.profile, data.profile);
                            }
                        } else {
                            let msg = (data && data.message) ? data.message : ('Server error (' + response.status + ')');
                            if (data && data.errors) {
                                msg += '\n' + Object.values(data.errors).flat().join('\n');
                            }
                            alert('Save Failed: ' + msg);
                        }
                    })
                    .catch((error) => {
                        this.isSaving = false;
                        alert('Error submitting form: ' + error.message);
                    });
                },

                openContextMenu(e, target) {
                    this.contextMenu.x = Math.min(e.clientX, window.innerWidth - 340);
                    this.contextMenu.y = Math.min(e.clientY, window.innerHeight - 320);
                    this.contextMenu.target = target;
                    this.contextMenu.show = true;
                    this.showSubmenu = false;
                    this.inspector.show = false;
                },

                openInspector(target) {
                    if (this.inspector.show && this.inspector.target === target) {
                        this.inspector.show = false;
                    } else {
                        this.inspector.target = target;
                        this.inspector.show = true;
                        window.dispatchEvent(new CustomEvent('close-mobile-navbar'));
                    }
                },
                openInspectorAtMouse(target, e) {
                    this.openInspector(target);
                },

                handleKeyDown(e) {
                    if (e.key === 'Delete' || e.key === 'Backspace' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
                        if (this.hoveredTarget && this.hoveredTarget !== 'canvas') {
                            this.toggleElementVisibility(this.hoveredTarget);
                        } else if (this.contextMenu.show && this.contextMenu.target && this.contextMenu.target !== 'canvas') {
                            this.toggleElementVisibility(this.contextMenu.target);
                            this.contextMenu.show = false;
                        }
                    }
                },

                isElementActive(target) {
                    if (target === 'avatar') return !!this.profile.show_avatar;
                    if (target === 'display_name') return !!this.profile.show_display_name;
                    if (target === 'location') return !!this.profile.show_location;
                    if (target === 'bio') return !!this.profile.show_bio;
                    if (target === 'discord') return !!this.profile.show_lanyard_card;
                    if (target === 'socials') return !!this.profile.show_social_links;
                    if (target === 'audio') return !!this.profile.show_audio_player;
                    if (target === 'views') return !!this.profile.show_views_count;
                    return true;
                },

                moveElementUp(index) {
                    if (index > 0) {
                        const temp = this.elementOrder[index];
                        this.elementOrder[index] = this.elementOrder[index - 1];
                        this.elementOrder[index - 1] = temp;
                        this.elementOrder = [...this.elementOrder];
                    }
                },

                moveElementDown(index) {
                    if (index < this.elementOrder.length - 1) {
                        const temp = this.elementOrder[index];
                        this.elementOrder[index] = this.elementOrder[index + 1];
                        this.elementOrder[index + 1] = temp;
                        this.elementOrder = [...this.elementOrder];
                    }
                },

                toggleElementVisibility(target) {
                    if (target === 'card') this.profile.show_card_container = false;
                    if (target === 'avatar') this.profile.show_avatar = false;
                    if (target === 'display_name') this.profile.show_display_name = false;
                    if (target === 'location') this.profile.show_location = false;
                    if (target === 'bio') this.profile.show_bio = false;
                    if (target === 'discord') this.profile.show_lanyard_card = false;
                    if (target === 'socials') this.profile.show_social_links = false;
                    if (target === 'audio') this.profile.show_audio_player = false;
                    if (target === 'mute_icon') this.profile.show_mute_icon = false;
                    if (target === 'views') this.profile.show_views_count = false;
                    if (this.inspector.target === target) this.inspector.show = false;
                }
            }));
        });
    </script>

    <x-slot name="scripts">
        <script>
        </script>
    </x-slot>
</x-admin.layout>
