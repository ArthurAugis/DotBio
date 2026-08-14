<div class="space-y-4">
    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Avatar Image Size (px)</label>
        <input type="range" min="40" max="160" step="5" x-model="profile.avatar_size" class="w-full accent-purple-500 cursor-pointer">
        <span class="text-purple-400 font-mono" x-text="profile.avatar_size + 'px'"></span>
    </div>

    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Avatar Border Radius / Shape</label>
        <input type="range" min="0" max="100" step="5" x-model="profile.avatar_radius" class="w-full accent-purple-500 cursor-pointer">
        <span class="text-purple-400 font-mono" x-text="profile.avatar_radius >= 100 ? 'Circle' : profile.avatar_radius + 'px'"></span>
    </div>

    <x-admin.color-picker model="profile.avatar_border_color" label="Avatar Border Ring Color" />

    <div class="space-y-2">
        <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
            <span class="text-xs font-semibold text-zinc-300">Neon Avatar Ring Glow</span>
            <input type="checkbox" x-model="profile.avatar_glow" class="accent-purple-500 w-4 h-4 cursor-pointer">
        </div>

        <div x-show="profile.avatar_glow" class="pl-2 border-l-2 border-purple-500/40">
            <x-admin.color-picker model="profile.avatar_glow_color" label="Neon Glow Ring Color" />
        </div>
    </div>

    <x-admin.custom-select 
        model="profile.avatar_hover_effect" 
        label="Avatar Hover Effect"
        :options="[
            ['value' => 'none', 'label' => 'None', 'icon' => 'fa-solid fa-ban'],
            ['value' => 'scale', 'label' => 'Subtle Zoom', 'icon' => 'fa-solid fa-magnifying-glass-plus'],
            ['value' => 'bounce', 'label' => 'Subtle Bounce', 'icon' => 'fa-solid fa-bolt'],
            ['value' => 'lift', 'label' => 'Vertical Lift', 'icon' => 'fa-solid fa-arrow-up-long'],
            ['value' => 'glow', 'label' => 'Neon Glow Hover', 'icon' => 'fa-solid fa-wand-magic-sparkles']
        ]" 
    />

    <div class="p-3 bg-[#09080d] rounded-2xl border border-purple-500/20 space-y-3" x-show="profile.avatar_hover_effect && profile.avatar_hover_effect !== 'none'">
        <p class="text-[11px] font-bold text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-sliders"></i>
            <span x-text="profile.avatar_hover_effect + ' Settings'"></span>
        </p>

        <div x-show="profile.avatar_hover_effect === 'scale'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Zoom Intensity Scale</label>
            <input type="range" min="1.01" max="1.25" step="0.01" x-model="profile.hover_zoom_scale" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_zoom_scale || 1.08) + 'x'"></span>
        </div>
        <div x-show="profile.avatar_hover_effect === 'glow'">
            <x-admin.color-picker model="profile.hover_glow_color" label="Hover Glow Color" />
        </div>
        <div x-show="profile.avatar_hover_effect === 'lift'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Lift Elevation (px)</label>
            <input type="range" min="2" max="30" step="1" x-model="profile.hover_lift_amount" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_lift_amount || 10) + 'px'"></span>
        </div>
        <div x-show="profile.avatar_hover_effect === 'bounce'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Bounce Jump Height (px)</label>
            <input type="range" min="2" max="25" step="1" x-model="profile.hover_bounce_intensity" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_bounce_intensity || 8) + 'px'"></span>
        </div>
    </div>

    <div class="pt-2 border-t border-white/10 space-y-2">
        <button @click="document.getElementById('avatarFileInput').click()" type="button" class="w-full py-2 bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 font-bold rounded-xl border border-purple-500/30 cursor-pointer text-center flex items-center justify-center gap-2">
            <i class="fa-solid fa-camera"></i> Upload Main Avatar Image
        </button>

        <button @click="document.getElementById('secondaryAvatarFileInput').click()" type="button" class="w-full py-2 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 font-bold rounded-xl border border-emerald-500/30 cursor-pointer text-center flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Upload Secondary Avatar Overlay
        </button>
    </div>
</div>