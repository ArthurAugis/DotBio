<x-admin.layout title="DotBio - Dashboard Overview">
    <x-slot name="head">
        <style>
            .dotbio-card {
                background: rgba(18, 16, 23, 0.7);
                backdrop-filter: blur(12px);
                border-radius: 1.25rem;
            }
        </style>
    </x-slot>

    <div class="space-y-8">
        <h1 class="text-2xl font-bold font-space">Account Overview</h1>

        @php
            $methods = [
                'discord' => ['Discord', 'fa-brands fa-discord'],
                'google' => ['Google', 'fa-brands fa-google'],
                'github' => ['GitHub', 'fa-brands fa-github'],
                'twitch' => ['Twitch', 'fa-brands fa-twitch'],
                'steam' => ['Steam', 'fa-brands fa-steam'],
                'spotify' => ['Spotify', 'fa-brands fa-spotify'],
                'patreon' => ['Patreon', 'fa-brands fa-patreon'],
                'password' => ['Email', 'fa-solid fa-envelope'],
            ];
            $user = auth()->user();
            $current = $user->loginMethod();
            $linked = [...$user->linkedProviders(), ...($user->password !== null ? ['password'] : [])];
            [$currentLabel, $currentIcon] = $methods[$current] ?? ['Email', 'fa-solid fa-envelope'];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-admin.card class="p-5 space-y-2">
                <div class="flex items-center justify-between text-zinc-400 text-xs font-medium">
                    <span>{{ $currentLabel }} Account</span>
                    <i class="{{ $currentIcon }} text-indigo-400 text-sm"></i>
                </div>
                <p class="text-xl font-bold text-white font-space truncate">{{ $user->name }}</p>
                <p class="text-[11px] text-zinc-500">{{ $current === 'password' ? 'Connected with email and password' : "Connected via {$currentLabel} OAuth" }}</p>
            </x-admin.card>

            <x-admin.card class="p-5 space-y-2">
                <div class="flex items-center justify-between text-zinc-400 text-xs font-medium">
                    <span>Profile Views</span>
                    <i class="fa-solid fa-eye text-zinc-500"></i>
                </div>
                <p class="text-xl font-bold text-white font-space">{{ number_format($totalViews) }}</p>
                <p class="text-[11px] text-emerald-400">+{{ number_format($currentWeekViews) }} in the last 7 days</p>
            </x-admin.card>

            <x-admin.card class="p-5 space-y-2 flex flex-col justify-between">
                <div class="flex items-center justify-between text-zinc-400 text-xs font-medium">
                    <span>Account Connection</span>
                    <i class="fa-solid fa-link text-indigo-400 text-sm"></i>
                </div>
                @foreach($linked as $method)
                    <div class="w-full py-2 px-3 bg-[#5865F2]/15 border border-[#5865F2]/30 rounded-xl text-xs font-semibold text-indigo-300 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="{{ $methods[$method][1] }} text-sm"></i>
                            {{ $methods[$method][0] }}
                        </span>
                        <span class="text-[10px] font-mono {{ $method === $current ? 'text-emerald-400' : 'text-zinc-500' }}">{{ $method === $current ? 'Current' : 'Linked' }}</span>
                    </div>
                @endforeach
            </x-admin.card>
        </div>

</x-admin.layout>
