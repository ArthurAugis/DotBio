@props([
    'model',
    'options' => [],
    'label' => null,
])

@php
    $optionsJson = json_encode($options);
@endphp

<div class="space-y-1.5" x-data="{
    open: false,
    options: {{ $optionsJson }},
    get selectedLabel() {
        const val = {{ $model }};
        const opt = this.options.find(o => o.value === val);
        return opt ? opt.label : (val || 'Select...');
    },
    get selectedIcon() {
        const val = {{ $model }};
        const opt = this.options.find(o => o.value === val);
        return opt ? opt.icon : null;
    },
    select(val) {
        {{ $model }} = val;
        this.open = false;
    }
}" @click.outside="open = false">
    @if($label)
        <label class="text-zinc-400 font-semibold text-xs block">{{ $label }}</label>
    @endif

    <div class="relative">
        <button type="button" 
                @click="open = !open" 
                class="w-full bg-[#09080d] hover:bg-[#121019] border border-white/10 hover:border-purple-500/40 rounded-xl px-3 py-2 text-xs text-white flex items-center justify-between transition cursor-pointer select-none">
            <div class="flex items-center gap-2.5 truncate">
                <template x-if="selectedIcon">
                    <i :class="selectedIcon" class="text-purple-400 text-xs w-4 text-center"></i>
                </template>
                <span x-text="selectedLabel" class="truncate font-medium text-zinc-200"></span>
            </div>
            <i class="fa-solid fa-chevron-down text-[10px] text-zinc-400 transition-transform duration-200 shrink-0 ml-2" :class="{ 'rotate-180': open }"></i>
        </button>

        <div x-show="open" 
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute left-0 right-0 mt-1.5 bg-[#14121b] border border-white/10 rounded-xl shadow-2xl py-1.5 z-50 overflow-hidden max-h-56 overflow-y-auto">
            <template x-for="opt in options" :key="opt.value">
                <button type="button" 
                        @click="select(opt.value)" 
                        class="w-full px-3 py-2 text-left text-xs hover:bg-purple-600/20 hover:text-purple-300 transition flex items-center justify-between cursor-pointer select-none" 
                        :class="{ 'text-purple-400 font-semibold bg-purple-600/10': {{ $model }} === opt.value, 'text-zinc-300': {{ $model }} !== opt.value }">
                    <div class="flex items-center gap-2.5 truncate">
                        <template x-if="opt.icon">
                            <i :class="opt.icon" class="text-purple-400 text-xs w-4 text-center"></i>
                        </template>
                        <span x-text="opt.label" class="truncate"></span>
                    </div>
                    <i x-show="{{ $model }} === opt.value" class="fa-solid fa-check text-purple-400 text-xs shrink-0 ml-2"></i>
                </button>
            </template>
        </div>
    </div>
</div>
