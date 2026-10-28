@foreach($profile->linkBlocks as $block)
    @php
        $blockTag = $block->url ? 'a' : 'div';
        $blockLink = $block->url
            ? 'href="'.e($block->url).'" target="_blank" rel="noopener noreferrer" @click="trackBlockClick('.$block->id.')"'
            : '';
    @endphp
    <div class="lp-item" style="--i: {{ min($loop->iteration, 12) }}">
        @if($block->type === 'heading')
            <p class="pt-4 pb-1 text-xs font-bold uppercase tracking-[0.2em] text-center opacity-70" style="color: {{ $profile->text_color ?? '#ffffff' }};">{{ $block->title }}</p>

        @elseif($block->type === 'card')
            <{{ $blockTag }} {!! $blockLink !!} class="lp-block lp-group-card lp-hover-{{ $linkStyles['card']['hover_effect'] }} block w-full text-left">
                @if($block->image_url)
                    <div class="aspect-video overflow-hidden">
                        <img src="{{ asset($block->image_url) }}" alt="" loading="lazy" class="lp-thumb w-full h-full object-cover">
                    </div>
                @endif
                <div class="px-5 py-4">
                    <p class="text-base font-bold leading-snug">{{ $block->title }}</p>
                    @if($block->subtitle)
                        <p class="lp-sub text-sm mt-0.5">{{ $block->subtitle }}</p>
                    @endif
                </div>
            </{{ $blockTag }}>

        @elseif($block->type === 'button')
            <{{ $blockTag }} {!! $blockLink !!} class="lp-block lp-group-button lp-hover-{{ $linkStyles['button']['hover_effect'] }} w-full flex items-center gap-4 px-4 py-3.5 min-h-16">
                @if($block->image_url)
                    <img src="{{ asset($block->image_url) }}" alt="" loading="lazy" class="w-11 h-11 rounded-xl object-cover flex-shrink-0">
                @else
                    <span class="w-11 flex-shrink-0"></span>
                @endif
                <span class="flex-1 min-w-0 text-center">
                    <span class="block text-sm font-bold truncate">{{ $block->title }}</span>
                    @if($block->subtitle)
                        <span class="lp-sub block text-xs truncate">{{ $block->subtitle }}</span>
                    @endif
                </span>
                <span class="w-11 flex-shrink-0 flex justify-end opacity-50">
                    @if($block->url)
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    @endif
                </span>
            </{{ $blockTag }}>

        @elseif($block->type === 'media' && $block->embed)
            <div class="lp-block lp-group-card w-full">
                @if($block->embed['kind'] === 'video')
                    <video src="{{ asset($block->embed['src']) }}" controls playsinline preload="metadata" class="w-full aspect-video bg-black"></video>
                @elseif($block->embed['height'])
                    <iframe src="{{ $block->embed['src'] }}" loading="lazy" class="w-full block" style="height: {{ $block->embed['height'] }}px;"
                            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" allowfullscreen></iframe>
                @else
                    <iframe src="{{ $block->embed['src'] }}" loading="lazy" class="w-full aspect-video block"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                @endif
                @if($block->title || $block->subtitle)
                    <div class="px-5 py-3.5 text-left">
                        @if($block->title)
                            <p class="text-sm font-bold">{{ $block->title }}</p>
                        @endif
                        @if($block->subtitle)
                            <p class="lp-sub text-xs mt-0.5">{{ $block->subtitle }}</p>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
@endforeach
