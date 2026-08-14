@props([
    'model',
    'label' => null,
    'defaultColor' => '#ffffff',
])

<div class="space-y-1.5" x-data="{
    get color() {
        let c = {{ $model }};
        return c || '{{ $defaultColor }}';
    },
    set color(val) {
        {{ $model }} = val;
    }
}">
    @if($label)
        <label class="text-zinc-400 font-semibold text-xs block">{{ $label }}</label>
    @endif

    <div class="flex items-center gap-2 bg-[#09080d] border border-white/10 hover:border-purple-500/40 rounded-xl p-1.5 transition">
        <div class="relative w-8 h-8 rounded-lg overflow-hidden shrink-0 border border-white/15 shadow-md flex items-center justify-center" :style="{ backgroundColor: color }">
            <input type="color" 
                   :value="color.startsWith('#') ? color : '#ffffff'" 
                   @input="color = $event.target.value" 
                   class="absolute -top-4 -left-4 w-16 h-16 opacity-0 cursor-pointer">
        </div>
        <div class="flex-1 font-mono text-xs text-zinc-300 font-bold uppercase">
            <input type="text" 
                   :value="color" 
                   @input="color = $event.target.value" 
                   placeholder="#HEX"
                   class="w-full bg-transparent border-none text-xs font-mono uppercase text-white focus:outline-none tracking-wider">
        </div>
    </div>
</div>
