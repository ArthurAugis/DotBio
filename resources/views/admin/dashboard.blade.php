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
                'discord' => ['Discord', 'fa-brands fa-discord', '#5865F2'],
                'google' => ['Google', 'fa-brands fa-google', '#4285F4'],
                'github' => ['GitHub', 'fa-brands fa-github', '#E4E4E7'],
                'twitch' => ['Twitch', 'fa-brands fa-twitch', '#9146FF'],
                'steam' => ['Steam', 'fa-brands fa-steam', '#66C0F4'],
                'spotify' => ['Spotify', 'fa-brands fa-spotify', '#1DB954'],
                'patreon' => ['Patreon', 'fa-brands fa-patreon', '#FF424D'],
                'password' => ['Email', 'fa-solid fa-envelope', '#A1A1AA'],
            ];
            $user = auth()->user();
            $current = $user->loginMethod();
            $linked = [...$user->linkedProviders(), ...($user->password !== null ? ['password'] : [])];
            [$currentLabel, $currentIcon, $currentColor] = $methods[$current] ?? $methods['password'];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-admin.card class="p-5 space-y-2">
                <div class="flex items-center justify-between text-zinc-400 text-xs font-medium">
                    <span>{{ $currentLabel }} Account</span>
                    <i class="{{ $currentIcon }} text-sm" style="color: {{ $currentColor }};"></i>
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
                    <i class="fa-solid fa-link text-sm" style="color: {{ $currentColor }};"></i>
                </div>
                @foreach($linked as $method)
                    <div class="w-full py-2 px-3 border rounded-xl text-xs font-semibold text-zinc-100 flex items-center justify-between"
                         style="background-color: {{ $methods[$method][2] }}26; border-color: {{ $methods[$method][2] }}4D;">
                        <span class="flex items-center gap-2">
                            <i class="{{ $methods[$method][1] }} text-sm" style="color: {{ $methods[$method][2] }};"></i>
                            {{ $methods[$method][0] }}
                        </span>
                        <span class="text-[10px] font-mono {{ $method === $current ? 'text-emerald-400' : 'text-zinc-500' }}">{{ $method === $current ? 'Current' : 'Linked' }}</span>
                    </div>
                @endforeach
            </x-admin.card>
        </div>

</x-admin.layout>
