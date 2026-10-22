<x-admin.layout title="DotBio - Link Page" lang="en" x-data="linkPageManager({{ json_encode($profile->linkBlocks) }})">
    <x-slot name="head">
        <style>
            @include('partials.link-block-styles')
        </style>
    </x-slot>

    <div class="max-w-6xl space-y-8">
        <h1 class="text-2xl font-bold font-space">Link Page</h1>

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-2xl text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-2xl text-xs font-semibold space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.link-page.settings') }}" method="POST" class="space-y-8"
              x-data="{ enabled: {{ $profile->link_page_enabled ? 'true' : 'false' }}, direction: '{{ $profile->link_page_direction ?? 'right' }}', buttonText: {{ json_encode($profile->link_page_button_text ?? '') }}, styles: {{ json_encode($profile->link_page_styles) }}, group: 'button' }">
        @csrf
        <x-admin.card class="p-6 space-y-5">
            <div>
                <h2 class="text-base font-bold font-space text-white flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-purple-400"></i>
                    A second page, one click away.
                </h2>
                <p class="text-xs text-zinc-400 mt-1">Adds a button to your profile that slides in a page of cards, buttons and videos. Also reachable directly with <span class="font-mono text-purple-300">{{ url('/') }}#links</span>.</p>
            </div>

            <div class="space-y-5">
                <input type="hidden" name="link_page_enabled" :value="enabled ? 1 : 0">
                <input type="hidden" name="link_page_direction" :value="direction">

                <label class="flex items-center justify-between gap-4 p-4 bg-[#09080d] border border-white/10 rounded-2xl cursor-pointer">
                    <span>
                        <span class="block text-sm font-bold text-white">Enable the link page</span>
                        <span class="block text-xs text-zinc-400">Shows the trigger button on your profile. Move it in Customize like any other element.</span>
                    </span>
                    <button type="button" @click="enabled = !enabled" class="relative w-11 h-6 rounded-full transition cursor-pointer flex-shrink-0" :class="enabled ? 'bg-purple-600' : 'bg-white/10'">
                        <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white transition-transform" :class="enabled ? 'translate-x-5' : ''"></span>
                    </button>
                </label>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-zinc-400">Button Text</label>
                        <input type="text" name="link_page_button_text" x-model="buttonText" placeholder="Links" maxlength="60"
                               class="w-full bg-[#09080d] border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-zinc-400">Page Title</label>
                        <input type="text" name="link_page_title" value="{{ $profile->link_page_title }}" placeholder="{{ $profile->display_name }}" maxlength="120"
                               class="w-full bg-[#09080d] border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-zinc-400">Page slides in from</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        @foreach(['right' => ['Right', 'fa-arrow-left'], 'left' => ['Left', 'fa-arrow-right'], 'top' => ['Top', 'fa-arrow-down'], 'bottom' => ['Bottom', 'fa-arrow-up']] as $value => [$label, $icon])
                            <button type="button" @click="direction = '{{ $value }}'"
                                    class="p-3 rounded-2xl border text-xs font-bold transition cursor-pointer flex items-center justify-center gap-2"
                                    :class="direction === '{{ $value }}' ? 'bg-purple-600/20 border-purple-500/50 text-purple-200' : 'bg-white/5 border-white/10 text-zinc-400 hover:text-white hover:bg-white/10'">
                                <i class="fa-solid {{ $icon }}"></i>
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

            </div>
        </x-admin.card>

        <x-admin.card class="p-6 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold font-space text-white flex items-center gap-2">
                        <i class="fa-solid fa-palette text-purple-400"></i>
                        Style
                    </h2>
                    <p class="text-xs text-zinc-400 mt-1">Buttons cover the profile trigger and button blocks. Containers cover cards and videos.</p>
                </div>
                <div class="flex bg-[#09080d] border border-white/10 rounded-xl p-1 text-xs font-bold">
                    <button type="button" @click="group = 'button'" class="px-3 py-1.5 rounded-lg transition cursor-pointer" :class="group === 'button' ? 'bg-purple-600 text-white' : 'text-zinc-400 hover:text-white'">Buttons</button>
                    <button type="button" @click="group = 'card'" class="px-3 py-1.5 rounded-lg transition cursor-pointer" :class="group === 'card' ? 'bg-purple-600 text-white' : 'text-zinc-400 hover:text-white'">Containers</button>
                </div>
            </div>

            <template x-for="(style, styleGroup) in styles" :key="styleGroup">
                <div class="hidden">
                    <template x-for="(value, field) in style" :key="field">
                        <input type="hidden" :name="'link_page_style[' + styleGroup + '][' + field + ']'" :value="value">
                    </template>
                </div>
            </template>

            <div class="grid lg:grid-cols-[1fr_minmax(0,22rem)] gap-6">
                <div class="space-y-5">
                    <div class="space-y-3">
                        <p class="text-[11px] font-bold text-purple-400 uppercase tracking-wider">Normal</p>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <x-admin.color-picker model="styles[group].bg_color" label="Background" />
                            <div class="space-y-1.5">
                                <label class="text-zinc-400 font-semibold text-xs block">Background Opacity <span class="text-purple-400 font-mono" x-text="styles[group].bg_opacity + '%'"></span></label>
                                <input type="range" min="0" max="100" x-model="styles[group].bg_opacity" class="w-full accent-purple-500 cursor-pointer">
                            </div>
                            <x-admin.color-picker model="styles[group].border_color" label="Border" />
                            <div class="space-y-1.5">
                                <label class="text-zinc-400 font-semibold text-xs block">Border Opacity <span class="text-purple-400 font-mono" x-text="styles[group].border_opacity + '%'"></span></label>
                                <input type="range" min="0" max="100" x-model="styles[group].border_opacity" class="w-full accent-purple-500 cursor-pointer">
                            </div>
                            <x-admin.color-picker model="styles[group].text_color" label="Title Text" />
                            <x-admin.color-picker model="styles[group].subtitle_color" label="Subtitle Text" />
                        </div>
                        <div class="grid sm:grid-cols-3 gap-3">
                            <div class="space-y-1.5">
                                <label class="text-zinc-400 font-semibold text-xs block">Border Radius <span class="text-purple-400 font-mono" x-text="styles[group].radius + 'px'"></span></label>
                                <input type="range" min="0" max="60" x-model="styles[group].radius" class="w-full accent-purple-500 cursor-pointer">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-zinc-400 font-semibold text-xs block">Border Width <span class="text-purple-400 font-mono" x-text="styles[group].border_width + 'px'"></span></label>
                                <input type="range" min="0" max="10" x-model="styles[group].border_width" class="w-full accent-purple-500 cursor-pointer">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-zinc-400 font-semibold text-xs block">Background Blur <span class="text-purple-400 font-mono" x-text="styles[group].blur + 'px'"></span></label>
                                <input type="range" min="0" max="40" x-model="styles[group].blur" class="w-full accent-purple-500 cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-[11px] font-bold text-purple-400 uppercase tracking-wider">Hover</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach(['none' => ['None', 'fa-ban'], 'lift' => ['Lift', 'fa-arrow-up-long'], 'scale' => ['Zoom', 'fa-magnifying-glass-plus'], 'glow' => ['Neon Glow', 'fa-wand-magic-sparkles']] as $value => [$label, $icon])
                                <button type="button" @click="styles[group].hover_effect = '{{ $value }}'"
                                        class="p-2.5 rounded-xl border text-xs font-bold transition cursor-pointer flex items-center justify-center gap-2"
                                        :class="styles[group].hover_effect === '{{ $value }}' ? 'bg-purple-600/20 border-purple-500/50 text-purple-200' : 'bg-white/5 border-white/10 text-zinc-400 hover:text-white hover:bg-white/10'">
                                    <i class="fa-solid {{ $icon }}"></i>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <x-admin.color-picker model="styles[group].hover_bg_color" label="Hover Background" />
                            <div class="space-y-1.5">
                                <label class="text-zinc-400 font-semibold text-xs block">Hover Background Opacity <span class="text-purple-400 font-mono" x-text="styles[group].hover_bg_opacity + '%'"></span></label>
                                <input type="range" min="0" max="100" x-model="styles[group].hover_bg_opacity" class="w-full accent-purple-500 cursor-pointer">
                            </div>
                            <x-admin.color-picker model="styles[group].hover_border_color" label="Hover Border" />
                            <x-admin.color-picker model="styles[group].hover_text_color" label="Hover Title Text" />
                            <x-admin.color-picker model="styles[group].glow_color" label="Shadow / Glow" />
                            <div class="space-y-1.5">
                                <label class="text-zinc-400 font-semibold text-xs block">Shadow / Glow Opacity <span class="text-purple-400 font-mono" x-text="styles[group].glow_opacity + '%'"></span></label>
                                <input type="range" min="0" max="100" x-model="styles[group].glow_opacity" class="w-full accent-purple-500 cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Live Preview <span class="normal-case font-normal tracking-normal text-zinc-500">(hover me)</span></p>
                    <div class="rounded-2xl p-5 space-y-4" style="background: radial-gradient(circle at 25% 15%, #5b21b6 0%, transparent 55%), radial-gradient(circle at 85% 90%, #9d174d 0%, #0d0c10 70%);">
                        <a href="#" @click.prevent :style="styleVars(styles.button)" :class="'lp-hover-' + styles.button.hover_effect"
                           class="lp-block w-full flex items-center justify-between gap-3 px-5 py-3.5 text-sm font-semibold">
                            <span class="w-4"></span>
                            <span x-text="buttonText || 'Links'"></span>
                            <i class="fa-solid fa-arrow-right text-xs opacity-70"></i>
                        </a>
                        <a href="#" @click.prevent :style="styleVars(styles.button)" :class="'lp-hover-' + styles.button.hover_effect"
                           class="lp-block w-full flex items-center gap-4 px-4 py-3.5 min-h-16">
                            <span class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex-shrink-0"></span>
                            <span class="flex-1 min-w-0 text-center">
                                <span class="block text-sm font-bold truncate">Button title</span>
                                <span class="lp-sub block text-xs truncate">Subtitle</span>
                            </span>
                            <span class="w-11 flex-shrink-0 flex justify-end opacity-50"><i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></span>
                        </a>
                        <a href="#" @click.prevent :style="styleVars(styles.card)" :class="'lp-hover-' + styles.card.hover_effect"
                           class="lp-block block w-full text-left">
                            <div class="aspect-video overflow-hidden">
                                <div class="lp-thumb w-full h-full bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500"></div>
                            </div>
                            <div class="px-5 py-4">
                                <p class="text-base font-bold leading-snug">Card title</p>
                                <p class="lp-sub text-sm mt-0.5">Card subtitle</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    Save Settings
                </button>
            </div>
        </x-admin.card>
        </form>

        <x-admin.card class="p-6 space-y-5">
            <h2 class="text-base font-bold font-space text-white flex items-center justify-between">
                <span>Blocks</span>
                <span class="text-xs text-zinc-400 font-normal">{{ $profile->linkBlocks->count() }} block(s)</span>
            </h2>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5">
                <template x-for="type in types" :key="type.id">
                    <button @click="openAddModal(type.id)" type="button"
                            class="p-3 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 flex items-center gap-3 transition-all duration-200 hover:scale-[1.02] cursor-pointer text-left">
                        <div class="w-9 h-9 rounded-xl bg-purple-500/15 text-purple-300 flex items-center justify-center flex-shrink-0">
                            <i :class="type.icon"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-white leading-tight" x-text="type.name"></p>
                            <p class="text-[10px] text-zinc-400 leading-tight" x-text="type.hint"></p>
                        </div>
                    </button>
                </template>
            </div>

            <div class="space-y-3">
                @forelse($profile->linkBlocks as $block)
                    <div class="p-4 bg-[#09080d] border border-white/10 rounded-2xl flex items-center justify-between gap-3 {{ $block->is_visible ? '' : 'opacity-50' }}">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="flex flex-col gap-1 text-[10px]">
                                <button @click="move({{ $loop->index }}, -1)" type="button" class="w-5 h-5 bg-white/5 hover:bg-purple-600 text-zinc-300 rounded flex items-center justify-center cursor-pointer disabled:opacity-30" @disabled($loop->first) title="Move Up"><i class="fa-solid fa-chevron-up"></i></button>
                                <button @click="move({{ $loop->index }}, 1)" type="button" class="w-5 h-5 bg-white/5 hover:bg-purple-600 text-zinc-300 rounded flex items-center justify-center cursor-pointer disabled:opacity-30" @disabled($loop->last) title="Move Down"><i class="fa-solid fa-chevron-down"></i></button>
                            </div>
                            @if($block->image_url)
                                <img src="{{ asset($block->image_url) }}" class="w-10 h-10 rounded-xl object-cover flex-shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-300 flex items-center justify-center flex-shrink-0">
                                    <i :class="typeIcon('{{ $block->type }}')"></i>
                                </div>
                            @endif
                            <div class="overflow-hidden">
                                <p class="text-sm font-bold text-white truncate">{{ $block->title ?: ucfirst($block->type) }}</p>
                                <p class="text-xs text-zinc-400 truncate">
                                    <span class="uppercase tracking-wider text-[10px] text-purple-400 font-bold">{{ $block->type }}</span>
                                    @if($block->url || $block->media_url)
                                        &bull; {{ $block->url ?: $block->media_url }}
                                    @endif
                                    @if($block->url)
                                        &bull; <span class="text-purple-400 font-mono">{{ number_format($block->clicks_count) }} click(s)</span>
                                    @endif
                                    @if($block->type === 'media' && ! $block->embed)
                                        &bull; <span class="text-rose-400">unsupported media URL</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <form action="{{ route('admin.link-page.toggle', $block) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs text-zinc-300 hover:text-white bg-white/5 hover:bg-white/10 w-9 h-9 rounded-xl border border-white/10 transition cursor-pointer" title="{{ $block->is_visible ? 'Hide' : 'Show' }}">
                                    <i class="fa-solid {{ $block->is_visible ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>
                            <button @click="openEditModal({{ json_encode($block) }})" type="button" class="text-xs text-purple-400 hover:text-purple-300 bg-purple-500/10 hover:bg-purple-500/20 px-3 py-2 rounded-xl border border-purple-500/20 transition cursor-pointer flex items-center gap-1.5 font-bold">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span class="hidden sm:inline">Edit</span>
                            </button>
                            <form action="{{ route('admin.link-page.destroy', $block) }}" method="POST" onsubmit="return confirm('Delete this block?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 px-3 py-2 rounded-xl border border-rose-500/20 transition cursor-pointer flex items-center gap-1.5 font-bold">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span class="hidden sm:inline">Delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center space-y-2">
                        <i class="fa-solid fa-layer-group text-zinc-600 text-3xl"></i>
                        <p class="text-xs text-zinc-400">No blocks yet.</p>
                        <p class="text-[11px] text-zinc-500">Pick a block type above to build your page.</p>
                    </div>
                @endforelse
            </div>
        </x-admin.card>
    </div>

    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="modalOpen = false" class="bg-[#121017] border border-white/10 rounded-3xl p-6 w-full max-w-md space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/15 text-purple-300 flex items-center justify-center text-lg">
                        <i :class="typeIcon(block.type)"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white font-space" x-text="(block.id ? 'Edit ' : 'Add ') + typeName(block.type)"></h3>
                        <p class="text-[11px] text-zinc-400" x-text="typeHint(block.type)"></p>
                    </div>
                </div>
                <button @click="modalOpen = false" type="button" class="text-zinc-400 hover:text-white text-sm cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="block.id ? '/admin/link-page/blocks/' + block.id : '{{ route('admin.link-page.store') }}'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <template x-if="block.id">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="type" :value="block.type">

                <div class="space-y-1">
                    <label class="text-xs font-bold text-zinc-400" x-text="block.type === 'media' ? 'Title (optional)' : 'Title'"></label>
                    <input type="text" name="title" x-model="block.title" :required="block.type !== 'media'" maxlength="255"
                           class="w-full bg-[#09080d] border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none">
                </div>

                <div class="space-y-1" x-show="block.type !== 'heading'">
                    <label class="text-xs font-bold text-zinc-400">Subtitle (optional)</label>
                    <input type="text" name="subtitle" x-model="block.subtitle" maxlength="255"
                           class="w-full bg-[#09080d] border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none">
                </div>

                <div class="space-y-1" x-show="block.type === 'card' || block.type === 'button'">
                    <label class="text-xs font-bold text-zinc-400">Destination URL</label>
                    <input type="text" name="url" x-model="block.url" placeholder="https://..." maxlength="500"
                           class="w-full bg-[#09080d] border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none">
                </div>

                <div class="space-y-2" x-show="block.type === 'card' || block.type === 'button'">
                    <label class="text-xs font-bold text-zinc-400" x-text="block.type === 'card' ? 'Thumbnail (16:9 recommended)' : 'Small Image (optional)'"></label>
                    <template x-if="block.image_url && !block.remove_image">
                        <div class="flex items-center gap-3">
                            <img :src="block.image_url" class="w-14 h-14 rounded-xl object-cover">
                            <button type="button" @click="block.remove_image = true" class="text-[11px] text-rose-400 hover:text-rose-300 cursor-pointer">Remove image</button>
                        </div>
                    </template>
                    <input type="hidden" name="remove_image" :value="block.remove_image ? 1 : 0">
                    <input type="file" name="image_file" accept="image/*"
                           class="w-full text-xs text-zinc-400 file:mr-3 file:px-3 file:py-2 file:rounded-xl file:border-0 file:bg-white/10 file:text-white file:text-xs file:font-bold file:cursor-pointer">
                </div>

                <div class="space-y-3" x-show="block.type === 'media'">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-zinc-400">Media URL</label>
                        <input type="text" name="media_url" x-model="block.media_url" placeholder="YouTube, Vimeo, Spotify, SoundCloud or .mp4 link" maxlength="500"
                               class="w-full bg-[#09080d] border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-zinc-400">Or upload a video (max {{ $maxUploadSizeMB }} MB)</label>
                        <input type="file" name="media_file" accept="video/mp4,video/webm,video/ogg,video/quicktime"
                               class="w-full text-xs text-zinc-400 file:mr-3 file:px-3 file:py-2 file:rounded-xl file:border-0 file:bg-white/10 file:text-white file:text-xs file:font-bold file:cursor-pointer">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="modalOpen = false" type="button" class="px-4 py-2.5 bg-white/5 hover:bg-white/10 text-white rounded-xl text-xs font-bold transition cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        <span x-text="block.id ? 'Update Block' : 'Save Block'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('linkPageManager', (blocks) => ({
                blocks,
                modalOpen: false,
                block: {},
                types: [
                    { id: 'card', name: 'Thumbnail Card', hint: 'Big image, title and subtitle', icon: 'fa-solid fa-image' },
                    { id: 'button', name: 'Button', hint: 'Large button with a small image', icon: 'fa-solid fa-hand-pointer' },
                    { id: 'media', name: 'Video / Embed', hint: 'YouTube, Spotify, local video...', icon: 'fa-solid fa-circle-play' },
                    { id: 'heading', name: 'Section Title', hint: 'Separate your blocks', icon: 'fa-solid fa-heading' },
                ],
                type(id) {
                    return this.types.find((type) => type.id === id) || this.types[0];
                },
                typeName(id) { return this.type(id).name; },
                typeHint(id) { return this.type(id).hint; },
                typeIcon(id) { return this.type(id).icon; },
                styleVars(style) {
                    return {
                        '--lp-bg': style.bg_color,
                        '--lp-bg-o': style.bg_opacity + '%',
                        '--lp-blur': style.blur + 'px',
                        '--lp-border': style.border_color,
                        '--lp-border-o': style.border_opacity + '%',
                        '--lp-bw': style.border_width + 'px',
                        '--lp-radius': style.radius + 'px',
                        '--lp-text': style.text_color,
                        '--lp-sub': style.subtitle_color,
                        '--lp-hover-bg': style.hover_bg_color,
                        '--lp-hover-bg-o': style.hover_bg_opacity + '%',
                        '--lp-hover-border': style.hover_border_color,
                        '--lp-hover-text': style.hover_text_color,
                        '--lp-glow': style.glow_color,
                        '--lp-glow-o': style.glow_opacity + '%',
                    };
                },
                openAddModal(type) {
                    this.block = { type, title: '', subtitle: '', url: '', media_url: '', image_url: null, remove_image: false };
                    this.modalOpen = true;
                },
                openEditModal(block) {
                    this.block = { ...block, remove_image: false };
                    this.modalOpen = true;
                },
                move(index, offset) {
                    const order = this.blocks.map((block) => block.id);
                    const target = index + offset;
                    if (target < 0 || target >= order.length) return;
                    [order[index], order[target]] = [order[target], order[index]];

                    fetch('{{ route('admin.link-page.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order })
                    }).then(() => window.location.reload());
                }
            }));
        });
        </script>
    </x-slot>
</x-admin.layout>
