<aside class="admin-desktop-sidebar w-64 bg-[#121017] border-r border-white/5 flex flex-col justify-between p-4 shrink-0 select-none">
    <div class="space-y-6">
        <div class="flex items-center gap-3 px-2">
            <div class="w-8 h-8 rounded-lg bg-purple-600/20 border border-purple-500/30 flex items-center justify-center text-purple-400 font-bold font-space">
                .B
            </div>
            <span class="font-bold text-xl font-space tracking-tight text-white">DotBio</span>
        </div>

        <nav class="space-y-1 text-sm font-medium">
            <div class="space-y-1" x-data="{ accountOpen: {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.analytics') ? 'true' : 'false' }} }">
                <button @click="accountOpen = !accountOpen" 
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition text-left cursor-pointer {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.analytics') ? 'bg-[#1e1b26] text-purple-300 font-semibold' : 'text-zinc-400 hover:text-zinc-200 hover:bg-white/5' }}">
                    <span class="flex items-center">
                        <i class="fa-solid fa-user text-base" style="margin-right: 12px; display: inline-block;"></i>
                        <span>Account</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="accountOpen ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="accountOpen" x-cloak class="space-y-1 text-xs text-zinc-400" style="padding-left: 44px;">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center py-1.5 transition cursor-pointer {{ request()->routeIs('admin.dashboard') ? 'text-purple-400 font-semibold' : 'hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie text-[11px]" style="margin-right: 10px; display: inline-block;"></i>
                        <span>Overview</span>
                    </a>
                    <a href="{{ route('admin.analytics') }}" class="flex items-center py-1.5 transition cursor-pointer {{ request()->routeIs('admin.analytics') ? 'text-purple-400 font-semibold' : 'hover:text-white' }}">
                        <i class="fa-solid fa-chart-line text-[11px]" style="margin-right: 10px; display: inline-block;"></i>
                        <span>Analytics</span>
                    </a>
                </div>
            </div>

            <a href="{{ route('admin.customize') }}" 
               class="w-full flex items-center px-3 py-2.5 rounded-xl transition text-left cursor-pointer {{ request()->routeIs('admin.customize') ? 'bg-[#1e1b26] text-purple-300 font-semibold' : 'text-zinc-400 hover:text-zinc-200 hover:bg-white/5' }}">
                <i class="fa-solid fa-pen-nib text-base" style="margin-right: 12px; display: inline-block;"></i>
                <span>Customize</span>
            </a>

            <a href="{{ route('admin.links.index') }}" 
               class="w-full flex items-center px-3 py-2.5 rounded-xl transition text-left cursor-pointer {{ request()->routeIs('admin.links.index') ? 'bg-[#1e1b26] text-purple-300 font-semibold' : 'text-zinc-400 hover:text-zinc-200 hover:bg-white/5' }}">
                <i class="fa-solid fa-link text-base" style="margin-right: 12px; display: inline-block;"></i>
                <span>Links</span>
            </a>

            <a href="{{ route('admin.seo') }}" 
               class="w-full flex items-center px-3 py-2.5 rounded-xl transition text-left cursor-pointer {{ request()->routeIs('admin.seo') ? 'bg-[#1e1b26] text-purple-300 font-semibold' : 'text-zinc-400 hover:text-zinc-200 hover:bg-white/5' }}">
                <i class="fa-solid fa-magnifying-glass text-base" style="margin-right: 12px; display: inline-block;"></i>
                <span>SEO & Favicon</span>
            </a>
        </nav>
    </div>

    <div class="space-y-3 pt-4 border-t border-white/5">
        <a href="/" target="_blank" class="w-full py-2.5 bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border border-purple-500/30 rounded-xl text-xs font-semibold transition flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 8px; display: inline-block;"></i>
            My page
        </a>

        <div class="flex items-center justify-between p-2 rounded-xl bg-white/5">
            <div class="flex items-center gap-2 overflow-hidden">
                <img src="{{ auth()->user()->avatar ?? 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode(auth()->user()->name) }}" class="w-8 h-8 rounded-full border border-white/10 shrink-0">
                <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="flex items-center">
                @csrf
                <button type="submit" class="text-zinc-400 hover:text-rose-400 p-1 flex items-center justify-center transition cursor-pointer leading-none" title="Log out">
                    <i class="fa-solid fa-power-off text-xs leading-none"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="admin-mobile-header" x-data="{ mobileOpen: false }" @close-mobile-navbar.window="mobileOpen = false">
    <header class="bg-[#121017] border-b border-white/5 px-4 py-3 flex items-center justify-between shrink-0 z-30 relative">
        <button @click="mobileOpen = !mobileOpen; if (mobileOpen) window.dispatchEvent(new CustomEvent('close-inspector'))" 
                type="button" 
                class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-300 hover:text-white transition cursor-pointer"
                title="Toggle Menu">
            <i class="fa-solid text-base" :class="mobileOpen ? 'fa-xmark text-purple-400' : 'fa-bars text-zinc-300'"></i>
        </button>

        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-purple-600/20 border border-purple-500/30 flex items-center justify-center text-purple-400 font-bold font-space text-xs">
                .B
            </div>
            <span class="font-bold text-sm font-space tracking-tight text-white">DotBio</span>
        </div>
    </header>

    <div x-show="mobileOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 w-full h-full bg-[#121017] flex flex-col justify-between p-6 overflow-y-auto select-none">
        
        <div class="space-y-8">
            <div class="flex items-center justify-between border-b border-white/5 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-600/20 border border-purple-500/30 flex items-center justify-center text-purple-400 font-bold font-space text-base">
                        .B
                    </div>
                    <span class="font-bold text-2xl font-space tracking-tight text-white">DotBio</span>
                </div>
                <button @click="mobileOpen = false" 
                        type="button" 
                        class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-400 hover:text-white transition cursor-pointer"
                        title="Close Menu">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <nav class="space-y-2 text-base font-medium">
                <div class="space-y-2" x-data="{ accountOpen: {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.analytics') ? 'true' : 'false' }} }">
                    <button @click="accountOpen = !accountOpen" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl transition text-left cursor-pointer {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.analytics') ? 'bg-[#1e1b26] text-purple-300 font-semibold' : 'text-zinc-300 hover:text-white hover:bg-white/5' }}">
                        <span class="flex items-center">
                            <i class="fa-solid fa-user text-lg text-purple-400" style="margin-right: 16px; display: inline-block;"></i>
                            <span>Account</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="accountOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="accountOpen" x-cloak class="space-y-2 text-sm text-zinc-400" style="padding-left: 54px;">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center py-2 transition cursor-pointer {{ request()->routeIs('admin.dashboard') ? 'text-purple-400 font-semibold' : 'hover:text-white' }}">
                            <i class="fa-solid fa-chart-pie text-xs" style="margin-right: 12px; display: inline-block;"></i>
                            <span>Overview</span>
                        </a>
                        <a href="{{ route('admin.analytics') }}" class="flex items-center py-2 transition cursor-pointer {{ request()->routeIs('admin.analytics') ? 'text-purple-400 font-semibold' : 'hover:text-white' }}">
                            <i class="fa-solid fa-chart-line text-xs" style="margin-right: 12px; display: inline-block;"></i>
                            <span>Analytics</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('admin.customize') }}" 
                   class="w-full flex items-center px-4 py-3 rounded-2xl transition text-left cursor-pointer {{ request()->routeIs('admin.customize') ? 'bg-[#1e1b26] text-purple-300 font-semibold' : 'text-zinc-300 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-pen-nib text-lg text-purple-400" style="margin-right: 16px; display: inline-block;"></i>
                    <span>Customize</span>
                </a>

                <a href="{{ route('admin.links.index') }}" 
                   class="w-full flex items-center px-4 py-3 rounded-2xl transition text-left cursor-pointer {{ request()->routeIs('admin.links.index') ? 'bg-[#1e1b26] text-purple-300 font-semibold' : 'text-zinc-300 hover:text-white hover:bg-white/5' }}">
                    <i class="fa-solid fa-link text-lg text-purple-400" style="margin-right: 16px; display: inline-block;"></i>
                    <span>Links</span>
                </a>

                <a href="{{ route('admin.seo') }}" 
                   class="w-full flex items-center px-4 py-3 rounded-2xl transition text-left cursor-pointer {{ request()->routeIs('admin.seo') ? 'bg-[#1e1b26] text-purple-300 font-semibold' : 'text-zinc-400 hover:text-zinc-200 hover:bg-white/5' }}">
                    <i class="fa-solid fa-magnifying-glass text-lg text-purple-400" style="margin-right: 16px; display: inline-block;"></i>
                    <span>SEO & Favicon</span>
                </a>
            </nav>
        </div>

        <div class="space-y-4 pt-6 border-t border-white/5 mt-auto">
            <a href="/" target="_blank" class="w-full py-3 bg-purple-600 hover:bg-purple-500 text-white rounded-2xl text-sm font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-purple-600/20">
                <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 8px; display: inline-block;"></i>
                My page
            </a>

            <div class="flex items-center justify-between p-3 rounded-2xl bg-white/5 border border-white/5">
                <div class="flex items-center gap-3 overflow-hidden">
                    <img src="{{ auth()->user()->avatar ?? 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode(auth()->user()->name) }}" class="w-10 h-10 rounded-full border border-white/10 shrink-0 object-cover">
                    <div class="overflow-hidden">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-zinc-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="flex items-center">
                    @csrf
                    <button type="submit" class="text-zinc-400 hover:text-rose-400 p-2 flex items-center justify-center transition cursor-pointer leading-none" title="Log out">
                        <i class="fa-solid fa-power-off text-base"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
