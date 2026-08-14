<div class="space-y-3">
    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold text-xs">Bio Text</label>
        <textarea x-model="profile.bio" placeholder="Write your bio..." class="w-full bg-[#09080d] border border-white/10 rounded-xl p-2.5 text-xs text-white focus:outline-none" rows="3"></textarea>
    </div>

    <x-admin.color-picker model="profile.bio_text_color" label="Bio Description Text Color" />

    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold text-xs">Font Size (px)</label>
        <input type="range" min="10" max="32" step="1" x-model="profile.bio_font_size" class="w-full accent-purple-500 cursor-pointer">
        <span class="text-purple-400 font-mono text-xs" x-text="(profile.bio_font_size || 14) + 'px'"></span>
    </div>

    <div class="space-y-1">
        <label class="text-zinc-400 font-semibold text-xs">Text Alignment</label>
        <div class="grid grid-cols-3 gap-2">
            <button @click="profile.bio_align = 'left'" type="button" 
                    class="py-2 text-xs font-bold rounded-xl border flex items-center justify-center gap-1.5 cursor-pointer transition"
                    :class="(profile.bio_align === 'left') ? 'bg-purple-600 border-purple-500 text-white' : 'bg-white/5 border-white/10 text-zinc-400 hover:text-white'">
                <i class="fa-solid fa-align-left"></i> Left
            </button>
            <button @click="profile.bio_align = 'center'" type="button" 
                    class="py-2 text-xs font-bold rounded-xl border flex items-center justify-center gap-1.5 cursor-pointer transition"
                    :class="(!profile.bio_align || profile.bio_align === 'center') ? 'bg-purple-600 border-purple-500 text-white' : 'bg-white/5 border-white/10 text-zinc-400 hover:text-white'">
                <i class="fa-solid fa-align-center"></i> Center
            </button>
            <button @click="profile.bio_align = 'right'" type="button" 
                    class="py-2 text-xs font-bold rounded-xl border flex items-center justify-center gap-1.5 cursor-pointer transition"
                    :class="(profile.bio_align === 'right') ? 'bg-purple-600 border-purple-500 text-white' : 'bg-white/5 border-white/10 text-zinc-400 hover:text-white'">
                <i class="fa-solid fa-align-right"></i> Right
            </button>
        </div>
    </div>

    <div class="border-t border-white/10 pt-3 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-purple-400">⌨️ Typewriter Text Loop</span>
            <input type="checkbox" x-model="profile.bio_typewriter_enabled" class="accent-purple-500 w-4 h-4 cursor-pointer">
        </div>

        <div x-show="profile.bio_typewriter_enabled" class="space-y-3">
            <p class="text-[11px] text-zinc-400">Add multiple phrases to loop automatically in bio:</p>
            <template x-for="(w, idx) in (profile.bio_typewriter_words || [])" :key="idx">
                <div class="flex items-center gap-2">
                    <input type="text" x-model="profile.bio_typewriter_words[idx]" placeholder="Bio phrase..." class="flex-1 bg-[#09080d] border border-white/10 rounded-lg p-1.5 text-xs text-white focus:outline-none">
                    <button @click="profile.bio_typewriter_words.splice(idx, 1)" type="button" class="text-rose-400 hover:text-rose-300 text-xs cursor-pointer p-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </template>
            <button @click="if(!profile.bio_typewriter_words) profile.bio_typewriter_words = []; profile.bio_typewriter_words.push('')" type="button" class="w-full py-1.5 bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 text-[11px] font-bold rounded-lg border border-purple-500/30 cursor-pointer">
                + Add Bio Phrase
            </button>

            <div class="space-y-1 pt-1 border-t border-white/5">
                <label class="text-zinc-400 font-semibold text-xs">Typewriter Animation Speed (ms)</label>
                <input type="range" min="20" max="200" step="5" x-model="profile.typewriter_speed" class="w-full accent-purple-500 cursor-pointer">
                <div class="flex items-center justify-between text-xs text-purple-400 font-mono">
                    <span x-text="(profile.typewriter_speed || 90) + 'ms'"></span>
                    <span class="text-[10px] text-zinc-500" x-text="(profile.typewriter_speed <= 50 ? 'Fast' : (profile.typewriter_speed >= 120 ? 'Slow' : 'Normal'))"></span>
                </div>
            </div>
        </div>
    </div>
</div>