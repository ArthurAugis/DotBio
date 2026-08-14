@props([
    'icon' => '',
    'title' => '',
    'description' => '',
    'buttonText' => '',
    'buttonAction' => '',
    'titleClass' => 'text-purple-400',
    'buttonClass' => 'bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border-purple-500/30',
])

<div class="p-4 bg-black/40 border border-white/5 rounded-2xl space-y-3">
    <div class="flex items-center gap-2 font-bold {{ $titleClass }}">
        <i class="{{ $icon }}"></i> {{ $title }}
    </div>
    <p class="text-[11px] text-zinc-400">{{ $description }}</p>
    <button @click="{{ $buttonAction }}" type="button" class="w-full py-2 font-bold rounded-xl border cursor-pointer {{ $buttonClass }}">
        {{ $buttonText }}
    </button>

    {{ $slot }}
</div>