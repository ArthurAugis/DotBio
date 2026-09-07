<div class="space-y-3">
    <div class="space-y-1">
        <div class="flex items-center justify-between">
            <label class="text-zinc-400 font-semibold text-xs">Global Social Icon Override</label>
            <button @click="profile.icon_color = ''" type="button" class="text-[10px] text-purple-400 hover:underline cursor-pointer">
                Reset to Original Colors
            </button>
        </div>
        <x-admin.color-picker model="profile.icon_color" label="" />
        <p class="text-[10px] text-zinc-500">Leave blank / reset to use each link's custom individual color from Manage Links.</p>
    </div>

    <x-admin.custom-select 
        model="profile.socials_hover_effect" 
        label="Social Links Hover Effect"
        :options="[
            ['value' => 'none', 'label' => 'None', 'icon' => 'fa-solid fa-ban'],
            ['value' => 'scale', 'label' => 'Subtle Zoom', 'icon' => 'fa-solid fa-magnifying-glass-plus'],
            ['value' => 'bounce', 'label' => 'Subtle Bounce', 'icon' => 'fa-solid fa-bolt'],
            ['value' => 'lift', 'label' => 'Vertical Lift', 'icon' => 'fa-solid fa-arrow-up-long'],
            ['value' => 'glow', 'label' => 'Neon Glow Hover', 'icon' => 'fa-solid fa-wand-magic-sparkles']
        ]" 
    />

    <div class="p-3 bg-[#09080d] rounded-2xl border border-purple-500/20 space-y-3" x-show="profile.socials_hover_effect && profile.socials_hover_effect !== 'none'">
        <p class="text-[11px] font-bold text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-sliders"></i>
            <span x-text="profile.socials_hover_effect + ' Settings'"></span>
        </p>

        <div x-show="profile.socials_hover_effect === 'scale'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Zoom Intensity Scale</label>
            <input type="range" min="1.01" max="1.35" step="0.01" x-model="profile.hover_zoom_scale" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_zoom_scale || 1.08) + 'x'"></span>
        </div>
        <div x-show="profile.socials_hover_effect === 'glow'">
            <x-admin.color-picker model="profile.hover_glow_color" label="Hover Glow Color" />
        </div>
        <div x-show="profile.socials_hover_effect === 'lift'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Lift Elevation (px)</label>
            <input type="range" min="2" max="30" step="1" x-model="profile.hover_lift_amount" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_lift_amount || 10) + 'px'"></span>
        </div>
        <div x-show="profile.socials_hover_effect === 'bounce'" class="space-y-1">
            <label class="text-zinc-400 font-semibold text-xs">Bounce Jump Height (px)</label>
            <input type="range" min="2" max="25" step="1" x-model="profile.hover_bounce_intensity" class="w-full accent-purple-500 cursor-pointer">
            <span class="text-purple-400 font-mono text-xs" x-text="(profile.hover_bounce_intensity || 8) + 'px'"></span>
        </div>
    </div>

    <div class="space-y-2">
        <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
            <span class="text-xs font-semibold text-zinc-300">Social Icons Glow</span>
            <input type="checkbox" x-model="profile.glow_socials" class="accent-purple-500 w-4 h-4 cursor-pointer">
        </div>

        <div x-show="profile.glow_socials" class="pl-2 border-l-2 border-purple-500/40">
            <x-admin.color-picker model="profile.socials_glow_color" label="Socials Glow Ring Color" />
        </div>
    </div>

    <div class="space-y-2">
        <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
            <span class="text-xs font-semibold text-zinc-300">Name Tooltip On Hover</span>
            <input type="checkbox" x-model="profile.show_social_tooltips" class="accent-purple-500 w-4 h-4 cursor-pointer">
        </div>

        <div x-show="profile.show_social_tooltips" class="p-3 bg-[#09080d] rounded-2xl border border-purple-500/20 space-y-3">
            <x-admin.color-picker model="profile.social_tooltip_bg_color" label="Tooltip Background" />

            <div class="space-y-1">
                <label class="text-zinc-400 font-semibold text-xs">Tooltip Background Opacity</label>
                <input type="range" min="0" max="1" step="0.01" x-model="profile.social_tooltip_opacity" class="w-full accent-purple-500 cursor-pointer">
                <span class="text-purple-400 font-mono text-xs" x-text="Math.round((profile.social_tooltip_opacity ?? 0.92) * 100) + '%'"></span>
            </div>

            <x-admin.color-picker model="profile.social_tooltip_text_color" label="Tooltip Text Color" />
            <x-admin.color-picker model="profile.social_tooltip_border_color" label="Tooltip Border Color" />
            <p class="text-[10px] text-zinc-500">Leave the border blank to follow the accent color.</p>
        </div>
    </div>

    <a href="{{ route('admin.links.index') }}" class="block py-2 bg-purple-600/20 hover:bg-purple-600/30 border border-purple-500/30 text-purple-300 rounded-xl text-center font-bold cursor-pointer">
        Manage Social Links (Snapchat, Youtube, Instagram...) →
    </a>
</div>