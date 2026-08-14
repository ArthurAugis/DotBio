<x-admin.layout title="DotBio - SEO & Favicon" lang="en">
    @php
        $appUrl = rtrim(config('app.url') ?: request()->getSchemeAndHttpHost(), '/');
        $domain = parse_url($appUrl, PHP_URL_HOST) ?: request()->getHost();
    @endphp

    <div class="max-w-6xl space-y-8" x-data="seoManager({{ json_encode([
        'meta_title' => $profile->meta_title ?? '',
        'meta_description' => $profile->meta_description ?? '',
        'meta_keywords' => $profile->meta_keywords ?? '',
        'favicon_type' => $profile->favicon_type ?? 'avatar',
        'meta_robots' => $profile->meta_robots ?? 'index, follow',
        'default_title' => $profile->display_name ?? auth()->user()->name ?? 'DotBio User',
        'default_description' => $profile->bio ?? 'Welcome to my official DotBio profile card.',
        'avatar_url' => asset($profile->avatar_source),
        'favicon_url' => asset($profile->favicon_source),
        'meta_image_url' => $profile->meta_image ? asset($profile->meta_image) : asset($profile->avatar_source),
        'site_domain' => $domain,
        'site_url' => $appUrl,
    ]) }})">

        <div class="space-y-1">
            <h1 class="text-2xl font-bold font-space flex items-center gap-3">
                <i class="fa-solid fa-magnifying-glass text-purple-400 text-xl"></i>
                SEO & Favicon Settings
            </h1>
            <p class="text-xs text-zinc-400">Optimize how your DotBio profile appears on Google, Discord, Twitter, and browser tabs.</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            <div class="lg:col-span-7 space-y-6">
                <form action="{{ route('admin.seo.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <x-admin.card class="p-4 sm:p-6 space-y-5">
                        <h2 class="text-sm font-bold font-space text-white flex items-center gap-2 border-b border-white/10 pb-3">
                            <i class="fa-solid fa-heading text-purple-400"></i>
                            Page Title & Meta Description
                        </h2>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-zinc-300">Site / Page Title (Meta Title)</label>
                            <input type="text" 
                                   name="meta_title" 
                                   x-model="form.meta_title" 
                                   placeholder="Default: {{ $profile->display_name }}"
                                   class="w-full bg-[#09080d] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none transition">
                            <p class="text-[11px] text-zinc-500">The title displayed in search engine results and browser tabs. Defaults to your Display Name if blank.</p>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-zinc-300">Meta Description</label>
                                <span class="text-[10px] text-zinc-500 font-mono" x-text="(form.meta_description ? form.meta_description.length : 0) + ' / 160 chars'"></span>
                            </div>
                            <textarea name="meta_description" 
                                      x-model="form.meta_description" 
                                      rows="3"
                                      placeholder="Default: {{ $profile->bio ?: 'Welcome to my official DotBio profile card.' }}"
                                      class="w-full bg-[#09080d] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none transition resize-none"></textarea>
                            <p class="text-[11px] text-zinc-500">A brief snippet describing your page for search engines and social card previews. Defaults to your Bio if blank.</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-zinc-300">Meta Keywords</label>
                            <input type="text" 
                                   name="meta_keywords" 
                                   x-model="form.meta_keywords" 
                                   placeholder="dotbio, bio, profile, developer, creator"
                                   class="w-full bg-[#09080d] border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none transition">
                            <p class="text-[11px] text-zinc-500">Comma-separated keywords related to your profile.</p>
                        </div>
                    </x-admin.card>

                    <x-admin.card class="p-4 sm:p-6 space-y-5">
                        <h2 class="text-sm font-bold font-space text-white flex items-center gap-2 border-b border-white/10 pb-3">
                            <i class="fa-solid fa-icons text-purple-400"></i>
                            Favicon Settings (Browser Icon)
                        </h2>

                        <div class="space-y-4">
                            <label class="p-3.5 sm:p-4 bg-[#09080d] border rounded-2xl flex items-start gap-3 sm:gap-4 cursor-pointer transition"
                                   :class="form.favicon_type === 'avatar' ? 'border-purple-500/50 bg-purple-950/10' : 'border-white/5 hover:border-white/10'">
                                <input type="radio" name="favicon_type" value="avatar" x-model="form.favicon_type" class="accent-purple-500 mt-1 cursor-pointer">
                                <div class="space-y-1.5 flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                                        <span class="text-xs font-bold text-white">Use Profile Picture (PDP) as Favicon</span>
                                        <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] px-2 py-0.5 rounded-full font-semibold inline-flex items-center gap-1 self-start sm:self-auto">
                                            <i class="fa-solid fa-arrows-rotate text-[9px] animate-spin"></i>
                                            Auto-Sync Enabled
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-zinc-400 leading-relaxed">Your browser tab favicon will automatically sync and update whenever you change your avatar or update your Discord profile picture.</p>
                                    
                                    <div class="pt-2 flex items-center gap-3">
                                        <img :src="data.avatar_url" class="w-8 h-8 rounded-full border border-purple-500/40 object-cover shrink-0">
                                        <span class="text-[11px] font-mono text-purple-300 font-semibold truncate">Active Avatar Thumbnail</span>
                                    </div>
                                </div>
                            </label>

                            <label class="p-3.5 sm:p-4 bg-[#09080d] border rounded-2xl flex items-start gap-3 sm:gap-4 cursor-pointer transition"
                                   :class="form.favicon_type === 'custom' ? 'border-purple-500/50 bg-purple-950/10' : 'border-white/5 hover:border-white/10'">
                                <input type="radio" name="favicon_type" value="custom" x-model="form.favicon_type" class="accent-purple-500 mt-1 cursor-pointer">
                                <div class="space-y-2 flex-1 min-w-0">
                                    <span class="text-xs font-bold text-white block">Upload Custom Favicon Image</span>
                                    <p class="text-[11px] text-zinc-400 leading-relaxed">Upload a custom `.ico`, `.png`, or `.svg` image to use specifically as your tab favicon.</p>

                                    <div x-show="form.favicon_type === 'custom'" class="pt-2 space-y-3">
                                        <input type="file" name="favicon_file" accept=".ico,.png,.jpg,.jpeg,.svg,.webp"
                                               class="block w-full text-xs text-zinc-400 file:mr-3 sm:file:mr-4 file:py-2 file:px-3 sm:file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-600/20 file:text-purple-300 hover:file:bg-purple-600/30 file:cursor-pointer cursor-pointer">
                                        @if($profile->favicon_url)
                                            <div class="flex items-center gap-3 pt-1">
                                                <img src="{{ asset($profile->favicon_url) }}" class="w-8 h-8 rounded-lg border border-white/10 object-contain bg-black/40 shrink-0">
                                                <span class="text-[11px] font-mono text-zinc-400 truncate">Current custom favicon</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        </div>
                    </x-admin.card>

                    <x-admin.card class="p-4 sm:p-6 space-y-5">
                        <h2 class="text-sm font-bold font-space text-white flex items-center gap-2 border-b border-white/10 pb-3">
                            <i class="fa-solid fa-share-nodes text-purple-400"></i>
                            Social Media Preview Image & Robots
                        </h2>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-zinc-300">Social Share Image (OpenGraph Image)</label>
                            <input type="file" name="meta_image_file" accept=".png,.jpg,.jpeg,.webp"
                                   class="block w-full text-xs text-zinc-400 file:mr-3 sm:file:mr-4 file:py-2 file:px-3 sm:file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-600/20 file:text-purple-300 hover:file:bg-purple-600/30 file:cursor-pointer cursor-pointer">
                            <p class="text-[11px] text-zinc-500">Image displayed when sharing your link on Discord, Twitter, or iMessage. Defaults to your Avatar if not provided.</p>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-white/5">
                            <x-admin.custom-select 
                                model="form.meta_robots" 
                                label="Search Engine Indexing (Robots)"
                                :options="[
                                    ['value' => 'index, follow', 'label' => 'Allow Indexing (Recommended - Visible on Google)', 'icon' => 'fa-solid fa-eye'],
                                    ['value' => 'noindex, nofollow', 'label' => 'Block Indexing (Hide profile from search engines)', 'icon' => 'fa-solid fa-eye-slash']
                                ]" 
                            />
                            <input type="hidden" name="meta_robots" :value="form.meta_robots">
                        </div>
                    </x-admin.card>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold transition cursor-pointer flex items-center justify-center gap-2 shadow-lg shadow-purple-600/20">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save SEO Settings
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-8">
                <x-admin.card class="p-4 sm:p-6 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-purple-400 flex items-center gap-2">
                        <i class="fa-brands fa-google"></i>
                        Google Search Preview
                    </h3>

                    <div class="p-3.5 sm:p-4 bg-[#17151c] border border-white/5 rounded-2xl space-y-2.5 shadow-inner">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-[10px] overflow-hidden shrink-0 border border-white/10">
                                <img :src="form.favicon_type === 'avatar' ? data.avatar_url : (data.favicon_url || data.avatar_url)" class="w-full h-full object-cover">
                            </div>
                            <div class="overflow-hidden min-w-0">
                                <p class="text-[11px] font-semibold text-zinc-200 leading-none truncate" x-text="data.site_domain"></p>
                                <p class="text-[10px] text-zinc-400 font-mono leading-tight truncate" x-text="data.site_url"></p>
                            </div>
                        </div>

                        <h4 class="text-base font-semibold text-[#8ab4f8] hover:underline cursor-pointer truncate leading-snug" 
                            x-text="form.meta_title ? form.meta_title : data.default_title"></h4>

                        <p class="text-xs text-zinc-400 line-clamp-2 leading-relaxed" 
                           x-text="form.meta_description ? form.meta_description : data.default_description"></p>
                    </div>
                </x-admin.card>

                <x-admin.card class="p-4 sm:p-6 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-purple-400 flex items-center gap-2">
                        <i class="fa-brands fa-discord"></i>
                        Social Card Share Preview
                    </h3>

                    <div class="p-3.5 sm:p-4 bg-[#1e1f22] border border-white/5 rounded-2xl space-y-3 shadow-xl">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-purple-600/30 border border-purple-500/40 flex items-center justify-center text-purple-400 text-xs font-bold shrink-0">
                                .B
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-white">DotBio Bot</span>
                                <span class="bg-[#5865f2] text-white text-[9px] font-bold px-1.5 py-0.2 rounded font-mono uppercase tracking-wider">APP</span>
                            </div>
                        </div>

                        <div class="bg-[#2b2d31] border-l-4 border-[#5865f2] p-3 sm:p-4 rounded-r-xl space-y-2.5 max-w-md overflow-hidden">
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest font-mono truncate" x-text="data.site_domain"></p>
                            
                            <h4 class="text-sm font-bold text-white leading-snug truncate" x-text="form.meta_title ? form.meta_title : data.default_title"></h4>
                            
                            <p class="text-xs text-zinc-300 leading-relaxed line-clamp-2" x-text="form.meta_description ? form.meta_description : data.default_description"></p>

                            <div class="pt-1.5">
                                <div class="w-full aspect-[16/9] max-h-48 rounded-lg overflow-hidden border border-white/10 bg-black/40 flex items-center justify-center">
                                    <img :src="data.meta_image_url" class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>

    </div>

    <x-slot name="scripts">
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('seoManager', (initialData) => ({
                    data: initialData,
                    form: {
                        meta_title: initialData.meta_title,
                        meta_description: initialData.meta_description,
                        meta_keywords: initialData.meta_keywords,
                        favicon_type: initialData.favicon_type,
                        meta_robots: initialData.meta_robots
                    }
                }));
            });
        </script>
    </x-slot>
</x-admin.layout>
