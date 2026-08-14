@props([
    'icon' => null,
    'label' => '',
    'mode' => 'pill',
])

@php
    $baseClass = $mode === 'menu'
        ? 'w-full text-left p-2 hover:bg-purple-600/20 text-xs hover:text-purple-300 rounded-xl transition flex items-center gap-2 cursor-pointer'
        : 'px-3 py-1.5 bg-white/5 hover:bg-purple-600/20 hover:text-purple-300 text-zinc-300 rounded-xl transition border border-white/5 flex items-center gap-1.5 cursor-pointer';
@endphp

<button {{ $attributes->merge(['type' => 'button', 'class' => $baseClass]) }}>
    @if($icon)
        <i class="{{ $icon }}"></i>
    @endif

    <span>{{ $label }}</span>
</button>