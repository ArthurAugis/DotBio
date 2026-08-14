<x-admin.layout title="DotBio - Social Links" lang="en" x-data="socialLinksManager()">
    <x-slot name="head">
        <style>
            .dotbio-card {
                background-color: #121017;
                border-radius: 20px;
            }
        </style>
    </x-slot>

    <div class="max-w-6xl space-y-8">
        <h1 class="text-2xl font-bold font-space">Social Links</h1>

            @if(session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-2xl text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <x-admin.card class="p-6 space-y-5">
                <div>
                    <h2 class="text-base font-bold font-space text-white flex items-center gap-2">
                        <i class="fa-solid fa-link text-purple-400"></i>
                        Connect your social media profiles.
                    </h2>
                    <p class="text-xs text-zinc-400 mt-1">Select a social network to add to your profile.</p>
                </div>

                <div class="flex flex-wrap gap-2.5 items-center">
                    <template x-for="platform in availablePlatforms" :key="platform.id">
                        <button @click="openAddModal(platform)" 
                                type="button"
                                class="w-11 h-11 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 flex items-center justify-center text-lg transition-all duration-200 hover:scale-110 cursor-pointer relative group"
                                :title="platform.name">
                            <i :class="platform.iconClass" :style="{ color: platform.color }"></i>
                        </button>
                    </template>

                    <button @click="openAddModal(customPlatform)" 
                            type="button"
                            class="h-11 px-4 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 flex items-center gap-3 transition-all duration-200 hover:scale-105 cursor-pointer text-left">
                        <div class="w-7 h-7 rounded-xl bg-white/10 flex items-center justify-center text-zinc-300">
                            <i class="fa-solid fa-globe text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white leading-tight">Add Custom URL</p>
                            <p class="text-[10px] text-zinc-400 leading-tight">Use your custom link and choose a icon.</p>
                        </div>
                    </button>
                </div>
            </x-admin.card>

            <x-admin.card class="p-6 space-y-4">
                <h2 class="text-base font-bold font-space text-white flex items-center justify-between">
                    <span>Active Profile Links</span>
                    <span class="text-xs text-zinc-400 font-normal">{{ $profile->links->count() }} active links</span>
                </h2>

                <div class="space-y-3">
                    @forelse($profile->links as $link)
                        <div class="p-4 bg-[#09080d] border border-white/10 rounded-2xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg" style="color: {{ $link->color }}; background: {{ $link->color }}18; border: 1px solid {{ $link->color }}30;">
                                    <i class="{{ $link->icon_class }}"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-sm font-bold text-white truncate">{{ $link->title }}</p>
                                    <p class="text-xs text-zinc-400 truncate">{{ str_replace('mailto:', '', $link->url) }} • <span class="text-purple-400 font-mono">{{ number_format($link->clicks_count) }} click(s)</span></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button @click="openEditModal({{ json_encode($link) }})" type="button" class="text-xs text-purple-400 hover:text-purple-300 bg-purple-500/10 hover:bg-purple-500/20 px-3 py-2 rounded-xl border border-purple-500/20 transition cursor-pointer flex items-center gap-1.5 font-bold">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Edit
                                </button>

                                <form action="{{ route('admin.links.destroy', $link) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 px-3 py-2 rounded-xl border border-rose-500/20 transition cursor-pointer flex items-center gap-1.5 font-bold">
                                        <i class="fa-solid fa-trash-can"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center space-y-2">
                            <i class="fa-solid fa-link text-zinc-600 text-3xl"></i>
                            <p class="text-xs text-zinc-400">No social links added yet.</p>
                            <p class="text-[11px] text-zinc-500">Click on any platform above to add your first link!</p>
                        </div>
                    @endforelse
                </div>
            </x-admin.card>
        </div>

        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div @click.away="modalOpen = false" class="bg-[#121017] border border-white/10 rounded-3xl p-6 w-full max-w-md space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" :style="{ color: activePlatform.color, background: activePlatform.color + '20' }">
                            <i :class="getPlatformIconClass(activePlatform)"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white font-space" x-text="isEditMode ? 'Edit ' + activePlatform.name : 'Add ' + activePlatform.name"></h3>
                            <p class="text-[11px] text-zinc-400">Enter your link or username</p>
                        </div>
                    </div>
                    <button @click="modalOpen = false" type="button" class="text-zinc-400 hover:text-white text-sm cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="isEditMode ? '/admin/links/' + activePlatform.link_id : '{{ route('admin.links.store') }}'" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEditMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="icon" :value="activePlatform.icon">

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-zinc-400">Link Title</label>
                        <input type="text" name="title" x-model="activePlatform.name" required
                               class="w-full bg-[#09080d] border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-zinc-400" x-text="activePlatform.id === 'email' ? 'Email Address' : 'Destination URL / Link'"></label>
                        <input type="text" name="url" x-model="activePlatform.urlPlaceholder" required :placeholder="activePlatform.id === 'email' ? '' : 'https://...'"
                               class="w-full bg-[#09080d] border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-zinc-400">Custom Icon Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="activePlatform.color" class="w-9 h-9 rounded-xl bg-transparent border border-white/10 cursor-pointer p-0.5">
                            <input type="text" name="color" x-model="activePlatform.color" class="flex-1 bg-[#09080d] border border-white/10 rounded-xl px-3.5 py-2 text-xs text-white font-mono uppercase focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>

                    <x-admin.custom-select 
                        model="activePlatform.hover_effect" 
                        label="Individual Hover Effect"
                        :options="[
                            ['value' => 'none', 'label' => 'None', 'icon' => 'fa-solid fa-ban'],
                            ['value' => 'scale', 'label' => 'Subtle Zoom', 'icon' => 'fa-solid fa-magnifying-glass-plus'],
                            ['value' => 'bounce', 'label' => 'Subtle Bounce', 'icon' => 'fa-solid fa-bolt'],
                            ['value' => 'lift', 'label' => 'Vertical Lift', 'icon' => 'fa-solid fa-arrow-up-long'],
                            ['value' => 'glow', 'label' => 'Neon Glow Hover', 'icon' => 'fa-solid fa-wand-magic-sparkles']
                        ]" 
                    />
                    <input type="hidden" name="hover_effect" :value="activePlatform.hover_effect || 'none'">

                    <div class="p-3 bg-[#09080d] rounded-2xl border border-purple-500/20 space-y-3" x-show="activePlatform.hover_effect && activePlatform.hover_effect !== 'none'">
                        <p class="text-[11px] font-bold text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-sliders"></i>
                            <span x-text="activePlatform.hover_effect + ' Settings'"></span>
                        </p>

                        <div x-show="activePlatform.hover_effect === 'scale'" class="space-y-1">
                            <label class="text-zinc-400 font-semibold text-xs">Zoom Intensity Scale</label>
                            <input type="range" min="1.01" max="1.35" step="0.01" x-model="activePlatform.hover_zoom_scale" class="w-full accent-purple-500 cursor-pointer">
                            <span class="text-purple-400 font-mono text-xs" x-text="(activePlatform.hover_zoom_scale || 1.08) + 'x'"></span>
                        </div>
                        <div x-show="activePlatform.hover_effect === 'glow'" class="space-y-1">
                            <label class="text-zinc-400 font-semibold text-xs">Hover Glow Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" x-model="activePlatform.hover_glow_color" class="w-8 h-8 rounded-xl bg-transparent border border-white/10 cursor-pointer p-0.5">
                                <input type="text" x-model="activePlatform.hover_glow_color" placeholder="#8B5CF6" class="flex-1 bg-[#09080d] border border-white/10 rounded-xl px-3 py-1.5 text-xs text-white font-mono uppercase focus:outline-none">
                            </div>
                        </div>
                        <div x-show="activePlatform.hover_effect === 'lift'" class="space-y-1">
                            <label class="text-zinc-400 font-semibold text-xs">Lift Elevation (px)</label>
                            <input type="range" min="2" max="30" step="1" x-model="activePlatform.hover_lift_amount" class="w-full accent-purple-500 cursor-pointer">
                            <span class="text-purple-400 font-mono text-xs" x-text="(activePlatform.hover_lift_amount || 10) + 'px'"></span>
                        </div>
                        <div x-show="activePlatform.hover_effect === 'bounce'" class="space-y-1">
                            <label class="text-zinc-400 font-semibold text-xs">Bounce Jump Height (px)</label>
                            <input type="range" min="2" max="25" step="1" x-model="activePlatform.hover_bounce_intensity" class="w-full accent-purple-500 cursor-pointer">
                            <span class="text-purple-400 font-mono text-xs" x-text="(activePlatform.hover_bounce_intensity || 8) + 'px'"></span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button @click="modalOpen = false" type="button" class="px-4 py-2.5 bg-white/5 hover:bg-white/10 text-white rounded-xl text-xs font-bold transition cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            <span x-text="isEditMode ? 'Update Link' : 'Save Link'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('socialLinksManager', () => ({
                modalOpen: false,
                activePlatform: {},
                customPlatform: {
                    id: 'custom',
                    name: 'Custom URL',
                    icon: 'globe',
                    iconClass: 'fa-solid fa-globe',
                    color: '#a855f7',
                    urlPlaceholder: 'https://'
                },
                availablePlatforms: [
                    { id: 'snapchat', name: 'Snapchat', icon: 'snapchat', iconClass: 'fa-brands fa-snapchat', color: '#FFFC00', urlPlaceholder: 'https://snapchat.com/add/' },
                    { id: 'youtube', name: 'YouTube', icon: 'youtube', iconClass: 'fa-brands fa-youtube', color: '#FF0000', urlPlaceholder: 'https://youtube.com/@' },
                    { id: 'discord', name: 'Discord', icon: 'discord', iconClass: 'fa-brands fa-discord', color: '#5865F2', urlPlaceholder: 'https://discord.gg/' },
                    { id: 'spotify', name: 'Spotify', icon: 'spotify', iconClass: 'fa-brands fa-spotify', color: '#1DB954', urlPlaceholder: 'https://open.spotify.com/user/' },
                    { id: 'instagram', name: 'Instagram', icon: 'instagram', iconClass: 'fa-brands fa-instagram', color: '#E4405F', urlPlaceholder: 'https://instagram.com/' },
                    { id: 'x-twitter', name: 'X / Twitter', icon: 'x-twitter', iconClass: 'fa-brands fa-x-twitter', color: '#FFFFFF', urlPlaceholder: 'https://x.com/' },
                    { id: 'tiktok', name: 'TikTok', icon: 'tiktok', iconClass: 'fa-brands fa-tiktok', color: '#FFFFFF', urlPlaceholder: 'https://tiktok.com/@' },
                    { id: 'telegram', name: 'Telegram', icon: 'telegram', iconClass: 'fa-brands fa-telegram', color: '#26A5E4', urlPlaceholder: 'https://t.me/' },
                    { id: 'soundcloud', name: 'SoundCloud', icon: 'soundcloud', iconClass: 'fa-brands fa-soundcloud', color: '#FF5500', urlPlaceholder: 'https://soundcloud.com/' },
                    { id: 'paypal', name: 'PayPal', icon: 'paypal', iconClass: 'fa-brands fa-paypal', color: '#003087', urlPlaceholder: 'https://paypal.me/' },
                    { id: 'github', name: 'GitHub', icon: 'github', iconClass: 'fa-brands fa-github', color: '#FFFFFF', urlPlaceholder: 'https://github.com/' },
                    { id: 'venmo', name: 'Venmo', icon: 'v', iconClass: 'fa-brands fa-vimeo', color: '#008CFF', urlPlaceholder: 'https://venmo.com/' },
                    { id: 'playstation', name: 'PlayStation', icon: 'playstation', iconClass: 'fa-brands fa-playstation', color: '#003791', urlPlaceholder: 'https://my.playstation.com/' },
                    { id: 'xbox', name: 'Xbox', icon: 'xbox', iconClass: 'fa-brands fa-xbox', color: '#107C10', urlPlaceholder: 'https://account.xbox.com/' },
                    { id: 'applemusic', name: 'Apple Music', icon: 'apple', iconClass: 'fa-brands fa-apple', color: '#FA243C', urlPlaceholder: 'https://music.apple.com/' },
                    { id: 'gitlab', name: 'GitLab', icon: 'gitlab', iconClass: 'fa-brands fa-gitlab', color: '#FC6D26', urlPlaceholder: 'https://gitlab.com/' },
                    { id: 'twitch', name: 'Twitch', icon: 'twitch', iconClass: 'fa-brands fa-twitch', color: '#9146FF', urlPlaceholder: 'https://twitch.tv/' },
                    { id: 'reddit', name: 'Reddit', icon: 'reddit', iconClass: 'fa-brands fa-reddit', color: '#FF4500', urlPlaceholder: 'https://reddit.com/user/' },
                    { id: 'vk', name: 'VK', icon: 'vk', iconClass: 'fa-brands fa-vk', color: '#4C75A3', urlPlaceholder: 'https://vk.com/' },
                    { id: 'steam', name: 'Steam', icon: 'steam', iconClass: 'fa-brands fa-steam', color: '#FFFFFF', urlPlaceholder: 'https://steamcommunity.com/profiles/' },
                    { id: 'pinterest', name: 'Pinterest', icon: 'pinterest', iconClass: 'fa-brands fa-pinterest', color: '#BD081C', urlPlaceholder: 'https://pinterest.com/' },
                    { id: 'facebook', name: 'Facebook', icon: 'facebook', iconClass: 'fa-brands fa-facebook', color: '#1877F2', urlPlaceholder: 'https://facebook.com/' },
                    { id: 'threads', name: 'Threads', icon: 'threads', iconClass: 'fa-brands fa-threads', color: '#FFFFFF', urlPlaceholder: 'https://threads.net/@' },
                    { id: 'patreon', name: 'Patreon', icon: 'patreon', iconClass: 'fa-brands fa-patreon', color: '#FF424D', urlPlaceholder: 'https://patreon.com/' },
                    { id: 'bitcoin', name: 'Bitcoin', icon: 'bitcoin', iconClass: 'fa-brands fa-bitcoin', color: '#F7931A', urlPlaceholder: 'https://blockchain.com/' },
                    { id: 'ethereum', name: 'ethereum', icon: 'ethereum', iconClass: 'fa-brands fa-ethereum', color: '#627EEA', urlPlaceholder: 'https://etherscan.io/' },
                    { id: 'email', name: 'Email', icon: 'envelope', iconClass: 'fa-solid fa-envelope', color: '#FFFFFF', urlPlaceholder: '' }
                ],
                isEditMode: false,
                openAddModal(platform) {
                    this.isEditMode = false;
                    this.activePlatform = { ...platform, hover_effect: 'none' };
                    this.modalOpen = true;
                },
                openEditModal(link) {
                    this.isEditMode = true;
                    this.activePlatform = {
                        link_id: link.id,
                        id: link.icon,
                        name: link.title,
                        icon: link.icon,
                        color: link.color || '#5865F2',
                        hover_effect: link.hover_effect || 'none',
                        urlPlaceholder: link.url.replace('mailto:', '')
                    };
                    this.modalOpen = true;
                },
                getPlatformIconClass(platform) {
                    if (!platform || !platform.icon) return 'fa-solid fa-globe';
                    if (platform.icon === 'envelope') return 'fa-solid fa-envelope';
                    if (platform.iconClass) return platform.iconClass;
                    return 'fa-brands fa-' + platform.icon;
                }
            }));
        });
        </script>
    </x-slot>
</x-admin.layout>

