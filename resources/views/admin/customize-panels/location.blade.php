<div class="space-y-3">
    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Location Text</label>
        <input type="text" x-model="profile.location" placeholder="Paris, France" class="w-full bg-[#09080d] border border-white/10 rounded-xl p-2 text-xs text-white focus:outline-none">
    </div>

    <div class="grid grid-cols-2 gap-3 pt-1">
        <div class="space-y-1">
            <label class="text-zinc-400 font-semibold">Text Color</label>
            <input type="color" x-model="profile.location_text_color" class="w-full h-8 rounded-lg border-none bg-transparent cursor-pointer">
        </div>
        <div class="space-y-1">
            <label class="text-zinc-400 font-semibold">Icon Color</label>
            <input type="color" x-model="profile.location_icon_color" class="w-full h-8 rounded-lg border-none bg-transparent cursor-pointer">
        </div>
    </div>
</div>