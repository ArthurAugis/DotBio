<div x-data="{ hovered: false }"
     @mouseenter="hovered = true"
     @mouseleave="hovered = false"
     class="flex items-center gap-1 {{ ($reverse ?? false) ? 'flex-row-reverse' : '' }}">
    <button type="button" @click="toggleMute()"
            class="transition-all duration-200 text-xl p-2.5 cursor-pointer focus:outline-none rounded-full"
            :style="{
                color: isMuted ? (hovered ? ('{{ $profile->mute_icon_hover_color }}' || '{{ $profile->mute_icon_color }}' || '#ffffff') : ('{{ $profile->mute_icon_color }}' || '#ffffff')) : (hovered ? ('{{ $profile->mute_icon_hover_unmute_color }}' || '{{ $profile->mute_icon_unmute_color }}' || '#ffffff') : ('{{ $profile->mute_icon_unmute_color }}' || '#ffffff')),
                backgroundColor: 'transparent'
            }"
            :title="isMuted ? 'Unmute Audio' : 'Mute Audio'">
        <i class="fa-solid fa-fw" :class="isMuted || volume === 0 ? 'fa-volume-xmark' : (volume < 0.5 ? 'fa-volume-low' : 'fa-volume-high')"></i>
    </button>
    <input type="range" min="0" max="1" step="0.01" aria-label="Volume"
           x-show="hovered" x-transition.opacity
           x-model.number="volume" @input="setVolume()"
           class="w-20 cursor-pointer"
           style="accent-color: {{ $profile->mute_icon_unmute_color ?: '#ffffff' }};">
</div>
