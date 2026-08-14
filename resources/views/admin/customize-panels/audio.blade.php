<div class="space-y-3">
    <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
        <span class="text-xs font-semibold text-zinc-300">Show Audio Player Widget</span>
        <input type="checkbox" x-model="profile.show_audio_player" class="accent-purple-500 w-4 h-4 cursor-pointer">
    </div>

    <x-admin.custom-select 
        model="profile.audio_hover_effect" 
        label="Audio Player Hover Effect"
        :options="[
            ['value' => 'none', 'label' => 'None', 'icon' => 'fa-solid fa-ban'],
            ['value' => 'scale', 'label' => 'Subtle Zoom', 'icon' => 'fa-solid fa-magnifying-glass-plus'],
            ['value' => 'bounce', 'label' => 'Subtle Bounce', 'icon' => 'fa-solid fa-bolt'],
            ['value' => 'lift', 'label' => 'Vertical Lift', 'icon' => 'fa-solid fa-arrow-up-long'],
            ['value' => 'glow', 'label' => 'Neon Glow Hover', 'icon' => 'fa-solid fa-wand-magic-sparkles']
        ]" 
    />

    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Song Title Name</label>
        <input type="text" x-model="profile.audio_title" placeholder="My Favorite Track" class="w-full bg-[#09080d] border border-white/10 rounded-xl p-2 text-xs text-white focus:outline-none">
    </div>

    <div class="grid grid-cols-2 gap-3 pt-1">
        <x-admin.color-picker model="profile.audio_title_color" label="Song Title Color" />
        <x-admin.color-picker model="profile.audio_progress_color" label="Progress Bar Color" />
    </div>

    <div class="grid grid-cols-2 gap-3">
        <x-admin.color-picker model="profile.audio_card_bg_color" label="Widget Card BG" />
        <x-admin.color-picker model="profile.audio_button_color" label="Play Button Color" />
    </div>

    <div class="grid grid-cols-2 gap-3">
        <x-admin.color-picker model="profile.audio_border_color" label="Border Color" />
        <div class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs block">Background Transparency</label>
            <input type="range" min="0" max="1" step="0.05" x-model="profile.audio_opacity" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-[10px]" x-text="Math.round((1 - (profile.audio_opacity ?? 1)) * 100) + '% transparent'"></span>
        </div>
    </div>

    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold text-xs block">Border Width</label>
        <input type="range" min="0" max="10" step="1" x-model="profile.audio_border_width" class="w-full accent-purple-500 cursor-pointer">
        <span class="text-purple-400 font-mono text-xs" x-text="(profile.audio_border_width !== undefined && profile.audio_border_width !== null ? profile.audio_border_width : 1) + 'px'"></span>
    </div>

    <div class="p-3 bg-[#09080d] rounded-2xl border border-purple-500/20 space-y-3" x-show="profile.audio_hover_effect && profile.audio_hover_effect !== 'none'">
        <p class="text-[11px] font-bold text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-sliders"></i>
            <span x-text="profile.audio_hover_effect + ' Settings'"></span>
        </p>

        <div x-show="profile.audio_hover_effect === 'scale'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Zoom Intensity Scale</label>
            <input type="range" min="1.01" max="1.25" step="0.01" x-model="profile.hover_zoom_scale" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_zoom_scale || 1.08) + 'x'"></span>
        </div>
        <div x-show="profile.audio_hover_effect === 'glow'">
            <x-admin.color-picker model="profile.hover_glow_color" label="Hover Glow Color" />
        </div>
        <div x-show="profile.audio_hover_effect === 'lift'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Lift Elevation (px)</label>
            <input type="range" min="2" max="30" step="1" x-model="profile.hover_lift_amount" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_lift_amount || 10) + 'px'"></span>
        </div>
        <div x-show="profile.audio_hover_effect === 'bounce'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Bounce Jump Height (px)</label>
            <input type="range" min="2" max="25" step="1" x-model="profile.hover_bounce_intensity" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_bounce_intensity || 8) + 'px'"></span>
        </div>
    </div>

    <div class="space-y-2">
        <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
            <span class="text-xs font-semibold text-zinc-300">Neon Glow Effect</span>
            <input type="checkbox" x-model="profile.audio_glow" class="accent-purple-500 w-4 h-4 cursor-pointer">
        </div>

        <div x-show="profile.audio_glow" class="pl-2 border-l-2 border-purple-500/40">
            <x-admin.color-picker model="profile.audio_glow_color" label="Neon Glow Ring Color" />
        </div>
    </div>

    <div class="flex gap-2 pt-2">
        <button @click="document.getElementById('audioFileInput').click()" type="button" class="w-1/2 py-2 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 font-bold rounded-xl border border-rose-500/30 cursor-pointer text-center">
            Upload Audio →
        </button>
        <button @click="document.getElementById('audioCoverFileInput').click()" type="button" class="w-1/2 py-2 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 font-bold rounded-xl border border-rose-500/30 cursor-pointer text-center">
            Cover Art →
        </button>
    </div>

    <div class="p-2.5 bg-black/40 rounded-xl border border-white/5 space-y-1">
        <p class="text-[10px] text-zinc-400">
            Audio File Status: <span class="font-bold font-mono" :class="profile.audio_url ? 'text-emerald-400' : 'text-zinc-500'" x-text="profile.audio_url ? '✔ Uploaded' : '❌ No File'"></span>
        </p>
        <p class="text-[10px] text-zinc-400">
            Cover Art: <span class="font-bold font-mono" :class="profile.audio_cover_url ? 'text-emerald-400' : 'text-zinc-500'" x-text="profile.audio_cover_url ? '✔ Uploaded' : 'Default Avatar'"></span>
        </p>
    </div>
</div>