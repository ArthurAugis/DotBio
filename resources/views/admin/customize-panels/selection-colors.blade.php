<div class="border-t border-white/10 pt-3 space-y-3">
    <div>
        <p class="text-xs font-bold text-purple-400">Text Selection Colors</p>
        <p class="text-[10px] text-zinc-500">Leave empty to use the card's selection colors.</p>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <x-admin.color-picker model="profile.selection_colors.{{ $target }}.bg" label="Selection Background" />
        <x-admin.color-picker model="profile.selection_colors.{{ $target }}.text" label="Selection Text" />
    </div>
</div>
