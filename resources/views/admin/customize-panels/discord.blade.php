<div class="space-y-4">
    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Discord Widget Scale Size (%)</label>
        <input type="range" min="50" max="200" step="5" x-model="profile.discord_status_scale" class="w-full accent-purple-500 cursor-pointer">
        <span class="text-purple-400 font-mono font-bold" x-text="(profile.discord_status_scale || 100) + '%'"></span>
    </div>

    <x-admin.custom-select 
        model="profile.discord_hover_effect" 
        label="Discord Widget Hover Effect"
        :options="[
            ['value' => 'none', 'label' => 'None', 'icon' => 'fa-solid fa-ban'],
            ['value' => 'scale', 'label' => 'Subtle Zoom', 'icon' => 'fa-solid fa-magnifying-glass-plus'],
            ['value' => 'bounce', 'label' => 'Subtle Bounce', 'icon' => 'fa-solid fa-bolt'],
            ['value' => 'lift', 'label' => 'Vertical Lift', 'icon' => 'fa-solid fa-arrow-up-long'],
            ['value' => 'glow', 'label' => 'Neon Glow Hover', 'icon' => 'fa-solid fa-wand-magic-sparkles']
        ]" 
    />

    <div class="grid grid-cols-2 gap-3">
        <x-admin.color-picker model="profile.discord_name_color" label="Username Color" />
        <x-admin.color-picker model="profile.discord_text_color" label="Activity Text Color" />
    </div>

    <div class="grid grid-cols-2 gap-3">
        <x-admin.color-picker model="profile.discord_card_bg_color" label="Card Container BG" />
        <x-admin.color-picker model="profile.discord_border_color" label="Border Color" />
    </div>

    <div class="grid grid-cols-2 gap-3">
        <x-admin.color-picker model="profile.discord_status_color" label="Status Dot Color" />
        <div class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs block">Background Transparency</label>
            <input type="range" min="0" max="1" step="0.05" x-model="profile.discord_opacity" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-[10px]" x-text="Math.round((1 - (profile.discord_opacity ?? 1)) * 100) + '% transparent'"></span>
        </div>
    </div>

    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold text-xs block">Border Width</label>
        <input type="range" min="0" max="10" step="1" x-model="profile.discord_border_width" class="w-full accent-purple-500 cursor-pointer">
        <span class="text-purple-400 font-mono text-xs" x-text="(profile.discord_border_width !== undefined && profile.discord_border_width !== null ? profile.discord_border_width : 1) + 'px'"></span>
    </div>

    <div class="p-3 bg-[#09080d] rounded-2xl border border-purple-500/20 space-y-3" x-show="profile.discord_hover_effect && profile.discord_hover_effect !== 'none'">
        <p class="text-[11px] font-bold text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-sliders"></i>
            <span x-text="profile.discord_hover_effect + ' Settings'"></span>
        </p>

        <div x-show="profile.discord_hover_effect === 'scale'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Zoom Intensity Scale</label>
            <input type="range" min="1.01" max="1.25" step="0.01" x-model="profile.hover_zoom_scale" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_zoom_scale || 1.08) + 'x'"></span>
        </div>
        <div x-show="profile.discord_hover_effect === 'glow'">
            <x-admin.color-picker model="profile.hover_glow_color" label="Hover Glow Color" />
        </div>
        <div x-show="profile.discord_hover_effect === 'lift'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Lift Elevation (px)</label>
            <input type="range" min="2" max="30" step="1" x-model="profile.hover_lift_amount" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_lift_amount || 10) + 'px'"></span>
        </div>
        <div x-show="profile.discord_hover_effect === 'bounce'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Bounce Jump Height (px)</label>
            <input type="range" min="2" max="25" step="1" x-model="profile.hover_bounce_intensity" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_bounce_intensity || 8) + 'px'"></span>
        </div>
    </div>

    <div class="space-y-2">
        <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
            <span class="text-xs font-semibold text-zinc-300">Neon Glow Effect</span>
            <input type="checkbox" x-model="profile.discord_glow" class="accent-purple-500 w-4 h-4 cursor-pointer">
        </div>

        <div x-show="profile.discord_glow" class="pl-2 border-l-2 border-purple-500/40">
            <x-admin.color-picker model="profile.discord_glow_color" label="Neon Glow Ring Color" />
        </div>
    </div>

    <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
        <span class="text-xs font-semibold text-zinc-300">Show Discord Avatar Decoration</span>
        <input type="checkbox" x-model="profile.show_discord_decoration" class="accent-purple-500 w-4 h-4 cursor-pointer">
    </div>

    <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
        <span class="text-xs font-semibold text-zinc-300">Show Discord Clan Tag Badge</span>
        <input type="checkbox" x-model="profile.show_discord_tag" class="accent-purple-500 w-4 h-4 cursor-pointer">
    </div>
</div>