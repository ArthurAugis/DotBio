<div class="space-y-4">
    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold">Display Name Text</label>
        <input type="text" x-model="profile.display_name" placeholder="Your Display Name" class="w-full bg-[#09080d] border border-white/10 rounded-xl p-2 text-xs text-white focus:outline-none">
    </div>

    <x-admin.color-picker model="profile.text_color" label="Username Text Color" />

    <div class="flex items-center justify-between p-2.5 bg-black/40 rounded-xl border border-white/5">
        <span class="text-xs font-semibold text-zinc-300">Neon Username Glow</span>
        <input type="checkbox" x-model="profile.glow_username" class="accent-purple-500 w-4 h-4 cursor-pointer">
    </div>

    <x-admin.custom-select 
        model="profile.username_effect" 
        label="Username Special Effect"
        :options="[
            ['value' => 'none', 'label' => 'None (Clean)', 'icon' => 'fa-solid fa-ban'],
            ['value' => 'sparkle', 'label' => 'Sparkle Trail', 'icon' => 'fa-solid fa-wand-magic-sparkles'],
            ['value' => 'rainbow', 'label' => 'Rainbow Animation', 'icon' => 'fa-solid fa-palette']
        ]" 
    />

    <div class="border-t border-white/10 pt-3 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-purple-400">Typewriter Text Loop</span>
            <input type="checkbox" x-model="profile.typewriter_enabled" class="accent-purple-500 w-4 h-4 cursor-pointer">
        </div>

        <div x-show="profile.typewriter_enabled" class="space-y-2">
            <p class="text-[11px] text-zinc-400">Add multiple phrases to loop automatically:</p>
            <template x-for="(w, idx) in (profile.display_name_typewriter_words || [])" :key="idx">
                <div class="flex items-center gap-2">
                    <input type="text" x-model="profile.display_name_typewriter_words[idx]" placeholder="Typewriter phrase..." class="flex-1 bg-[#09080d] border border-white/10 rounded-lg p-1.5 text-xs text-white focus:outline-none">
                    <button @click="profile.display_name_typewriter_words.splice(idx, 1)" type="button" class="text-rose-400 hover:text-rose-300 text-xs cursor-pointer p-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </template>
            <button @click="if(!profile.display_name_typewriter_words) profile.display_name_typewriter_words = []; profile.display_name_typewriter_words.push('')" type="button" class="w-full py-1.5 bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 text-[11px] font-bold rounded-lg border border-purple-500/30 cursor-pointer">
                + Add Typewriter Phrase
            </button>
        </div>
    </div>
</div>