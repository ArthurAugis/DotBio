<x-admin.layout title="DotBio - Team" lang="en">
    <div class="max-w-4xl space-y-8">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold font-space flex items-center gap-3">
                <i class="fa-solid fa-user-group text-purple-400 text-xl"></i>
                Team
            </h1>
            <p class="text-xs text-zinc-400">Invite people to manage this DotBio and choose who owns it.</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-amber-500/10 border border-amber-500/30 text-amber-400 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-2xl text-xs font-semibold space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <x-admin.card class="p-4 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold font-space text-white flex items-center gap-2 border-b border-white/10 pb-3">
                <i class="fa-solid fa-envelope text-purple-400"></i>
                Invite an admin
            </h2>

            <form action="{{ route('admin.team.invite') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" placeholder="friend@example.com" required
                       class="flex-1 px-4 py-2.5 bg-black/30 border border-white/10 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-purple-500/50">
                <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-sm font-bold transition cursor-pointer">
                    Send invitation
                </button>
            </form>

            <p class="text-xs text-zinc-400">
                The invited person logs in with any enabled method (Discord, Google, GitHub, Twitch, Spotify, Patreon or email and password) whose account uses this exact email.
                Steam never shares an email, so a Steam login cannot accept an invitation.
            </p>

            <div class="space-y-2" x-data="{ copied: false }">
                <label class="text-xs font-bold text-zinc-300">Login link to share if the email does not arrive</label>
                <div class="flex gap-2">
                    <input type="text" readonly value="{{ route('login') }}" x-ref="link"
                           class="flex-1 px-4 py-2.5 bg-black/30 border border-white/10 rounded-xl text-xs text-zinc-300">
                    <button type="button" @click="navigator.clipboard.writeText($refs.link.value); copied = true; setTimeout(() => copied = false, 1500)"
                            class="px-4 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-bold text-zinc-200 transition cursor-pointer"
                            x-text="copied ? 'Copied' : 'Copy'"></button>
                </div>
            </div>
        </x-admin.card>

        @if($invitations->isNotEmpty())
            <x-admin.card class="p-4 sm:p-6 space-y-4">
                <h2 class="text-sm font-bold font-space text-white flex items-center gap-2 border-b border-white/10 pb-3">
                    <i class="fa-solid fa-hourglass-half text-purple-400"></i>
                    Pending invitations
                </h2>

                <ul class="divide-y divide-white/5">
                    @foreach($invitations as $invitation)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <span class="text-sm text-zinc-200 truncate">{{ $invitation->email }}</span>
                            <form action="{{ route('admin.team.revoke', $invitation) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-bold cursor-pointer">Revoke</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
        @endif

        <x-admin.card class="p-4 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold font-space text-white flex items-center gap-2 border-b border-white/10 pb-3">
                <i class="fa-solid fa-users text-purple-400"></i>
                Admins
            </h2>

            <ul class="divide-y divide-white/5">
                @foreach($admins as $admin)
                    <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $admin->avatar ?? 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($admin->name) }}" alt="" class="w-9 h-9 rounded-full border border-white/10 object-cover shrink-0">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-white truncate">
                                    {{ $admin->name }}
                                    @if($admin->id === $ownerId)
                                        <span class="ml-1 text-[10px] uppercase tracking-wide text-purple-300 bg-purple-500/10 border border-purple-500/30 px-1.5 py-0.5 rounded-md">Owner</span>
                                    @endif
                                </p>
                                <p class="text-xs text-zinc-400 truncate">{{ $admin->email }} · {{ ucfirst($admin->loginMethod()) }}</p>
                            </div>
                        </div>

                        @if($admin->id !== $ownerId)
                            <div class="flex items-center gap-3 shrink-0">
                                <form action="{{ route('admin.team.transfer', $admin) }}" method="POST"
                                      onsubmit="return confirm(@js('Make '.$admin->name.' the owner of this DotBio? Their account avatar and Discord will be used on the page.'))">
                                    @csrf
                                    <button type="submit" class="text-xs text-purple-400 hover:text-purple-300 font-bold cursor-pointer">Make owner</button>
                                </form>
                                <form action="{{ route('admin.team.remove', $admin) }}" method="POST"
                                      onsubmit="return confirm(@js('Remove '.$admin->name.'? Their account will be deleted.'))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-bold cursor-pointer">Remove</button>
                                </form>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    </div>
</x-admin.layout>
