<div class="space-y-4">
    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Click to Enter Text</label>
        <input type="text" x-model="profile.enter_text" placeholder="Click anywhere to enter" class="w-full bg-[#09080d] border border-white/10 rounded-xl p-2 text-xs text-white focus:outline-none">
    </div>

    <div class="grid grid-cols-2 gap-3">
        <x-admin.color-picker model="profile.enter_text_color" label="Text Color" />
        <x-admin.color-picker model="profile.enter_bg_color" label="Screen BG Color" />
    </div>

    <div class="pt-2 border-t border-white/10 space-y-2">
        <label class="text-zinc-400 font-semibold block">Top Image Overlay (Optional)</label>
        <button @click="document.getElementById('enterImageFileInput').click()" type="button" class="w-full py-2 bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 font-bold rounded-xl border border-amber-500/30 cursor-pointer text-center flex items-center justify-center gap-2">
            <i class="fa-solid fa-image"></i> Upload Enter Screen Image
        </button>
        <template x-if="profile.enter_image_url">
            <div class="flex items-center justify-between p-2 bg-black/40 rounded-xl border border-white/5">
                <span class="text-[10px] text-emerald-400 font-mono font-bold">✔ Image Uploaded</span>
                <button @click="profile.enter_image_url = ''" type="button" class="text-rose-400 text-[10px] hover:underline cursor-pointer">Remove</button>
            </div>
        </template>
    </div>
</div>