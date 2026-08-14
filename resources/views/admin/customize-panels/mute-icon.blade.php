<div class="space-y-3">
    <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
        <span class="text-xs font-semibold text-zinc-300">Show Top-Left Mute Icon</span>
        <input type="checkbox" x-model="profile.show_mute_icon" class="accent-purple-500 w-4 h-4 cursor-pointer">
    </div>

    <div class="grid grid-cols-2 gap-3">
        <x-admin.color-picker model="profile.mute_icon_color" label="Muted Icon Color" />
        <x-admin.color-picker model="profile.mute_icon_unmute_color" label="Unmuted Icon Color" />
    </div>

    <div class="grid grid-cols-2 gap-3">
        <x-admin.color-picker model="profile.mute_icon_hover_color" label="Icon Color Hover (Muted)" />
        <x-admin.color-picker model="profile.mute_icon_hover_unmute_color" label="Icon Color Hover (Unmuted)" />
    </div>

    <button @click="profile.show_mute_icon = false; inspector.show = false;" type="button" class="w-full py-2 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 font-bold rounded-xl border border-rose-500/30 cursor-pointer text-center">
        Hide / Remove Mute Icon
    </button>
</div>