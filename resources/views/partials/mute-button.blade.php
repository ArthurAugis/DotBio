<div x-cloak x-show="profile.show_audio_player" x-data="{ hovered: false }">
    <button type="button" @click="toggleMute()"
            @mouseenter="hovered = true"
            @mouseleave="hovered = false"
            class="transition-all duration-200 text-xl p-2.5 cursor-pointer focus:outline-none rounded-full"
            :style="{
                color: isMuted ? (hovered ? ('{{ $profile->mute_icon_hover_color }}' || '{{ $profile->mute_icon_color }}' || '#ffffff') : ('{{ $profile->mute_icon_color }}' || '#ffffff')) : (hovered ? ('{{ $profile->mute_icon_hover_unmute_color }}' || '{{ $profile->mute_icon_unmute_color }}' || '#ffffff') : ('{{ $profile->mute_icon_unmute_color }}' || '#ffffff')),
                backgroundColor: 'transparent'
            }"
            :title="isMuted ? 'Unmute Audio' : 'Mute Audio'">
        <i class="fa-solid" :class="isMuted ? 'fa-volume-xmark' : 'fa-volume-high'"></i>
    </button>
</div>
