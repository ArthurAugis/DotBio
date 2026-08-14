<div class="space-y-4">
    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Container Max Width (200px - 1200px)</label>
        <input type="range" min="200" max="1200" step="20" x-model="profile.card_width" class="w-full accent-purple-500 cursor-pointer">
        <span class="text-purple-400 font-mono font-bold" x-text="profile.card_width + 'px'"></span>
    </div>

    <x-admin.custom-select 
        model="profile.entry_animation" 
        label="Global Entry Transition Animation"
        :options="[
            ['value' => 'fade', 'label' => 'Smooth Fade In', 'icon' => 'fa-solid fa-eye'],
            ['value' => 'slide_up', 'label' => 'Slide Up Entrance', 'icon' => 'fa-solid fa-arrow-up-from-bracket'],
            ['value' => 'zoom_in', 'label' => 'Zoom In Scale', 'icon' => 'fa-solid fa-expand'],
            ['value' => 'bounce', 'label' => 'Energetic Bounce', 'icon' => 'fa-solid fa-bolt']
        ]" 
    />

    <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
        <span class="text-xs font-semibold text-zinc-300">3D Mouse Tilt / Parallax Effect</span>
        <input type="checkbox" x-model="profile.tilt_3d_enabled" class="accent-purple-500 w-4 h-4 cursor-pointer">
    </div>

    <x-admin.custom-select 
        model="profile.hover_animation" 
        label="Hover Animation Style"
        :options="[
            ['value' => 'none', 'label' => 'None', 'icon' => 'fa-solid fa-ban'],
            ['value' => 'scale', 'label' => 'Smooth Scale Zoom', 'icon' => 'fa-solid fa-magnifying-glass-plus'],
            ['value' => 'glow', 'label' => 'Neon Glow Border', 'icon' => 'fa-solid fa-wand-magic-sparkles'],
            ['value' => 'bounce', 'label' => 'Subtle Bounce', 'icon' => 'fa-solid fa-bolt'],
            ['value' => 'lift', 'label' => 'Vertical Lift', 'icon' => 'fa-solid fa-arrow-up-long']
        ]" 
    />

    <div class="p-3 bg-[#09080d] rounded-2xl border border-purple-500/20 space-y-3" x-show="profile.hover_animation && profile.hover_animation !== 'none'">
        <p class="text-[11px] font-bold text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-sliders"></i>
            <span x-text="profile.hover_animation + ' Settings'"></span>
        </p>

        <div x-show="profile.hover_animation === 'scale'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Zoom Intensity Scale</label>
            <input type="range" min="1.01" max="1.25" step="0.01" x-model="profile.hover_zoom_scale" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_zoom_scale || 1.08) + 'x'"></span>
        </div>

        <div x-show="profile.hover_animation === 'glow'">
            <x-admin.color-picker model="profile.hover_glow_color" label="Hover Glow Color" />
        </div>

        <div x-show="profile.hover_animation === 'lift'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Lift Elevation (px)</label>
            <input type="range" min="2" max="30" step="1" x-model="profile.hover_lift_amount" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_lift_amount || 10) + 'px'"></span>
        </div>

        <div x-show="profile.hover_animation === 'bounce'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Bounce Jump Height (px)</label>
            <input type="range" min="2" max="25" step="1" x-model="profile.hover_bounce_intensity" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_bounce_intensity || 8) + 'px'"></span>
        </div>
    </div>

    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Card Border Radius (px)</label>
        <input type="range" min="0" max="50" step="2" x-model="profile.border_radius" class="w-full accent-purple-500 cursor-pointer">
        <span class="text-purple-400 font-mono" x-text="profile.border_radius + 'px'"></span>
    </div>

    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Background Transparency</label>
        <input type="range" min="0" max="1" step="0.05" x-model="profile.card_opacity" class="w-full accent-purple-500 cursor-pointer">
        <span class="text-purple-400 font-mono" x-text="Math.round((1 - (profile.card_opacity ?? 1)) * 100) + '% transparent'"></span>
    </div>

    <div class="grid grid-cols-2 gap-3 pt-2">
        <x-admin.color-picker model="profile.card_bg_color" label="Container Color" />
        <x-admin.color-picker model="profile.hover_bg_color" label="Hover Color" />
        <x-admin.color-picker model="profile.border_color" label="Border Color" />
        <x-admin.color-picker model="profile.background_color" label="Background Color" />

        <div class="space-y-1 col-span-2">
            <label class="text-zinc-400 font-semibold text-xs">Border Width</label>
            <input type="range" min="0" max="10" step="1" x-model="profile.border_width" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.border_width !== undefined && profile.border_width !== null ? profile.border_width : 1) + 'px'"></span>
        </div>
    </div>

    <div class="border-t border-white/10 pt-3 space-y-3">
        <p class="text-xs font-bold text-purple-400">Text Selection Colors (Highlight)</p>
        <div class="grid grid-cols-2 gap-3">
            <x-admin.color-picker model="profile.selection_bg_color" label="Selection Background" />
            <x-admin.color-picker model="profile.selection_text_color" label="Selection Text" />
        </div>
    </div>
</div>