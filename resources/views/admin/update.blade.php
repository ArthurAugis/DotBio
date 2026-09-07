<x-admin.layout title="DotBio - Updates">
    <div class="space-y-8"
         x-data="updatePanel(@js($status), @js(route('admin.update.status')))"
         x-init="init()">

        <div class="space-y-1">
            <h1 class="text-2xl font-bold font-space flex items-center gap-3">
                <i class="fa-solid fa-cloud-arrow-down text-purple-400 text-xl"></i>
                Updates
            </h1>
            <p class="text-xs text-zinc-400">Keep this DotBio install in sync with {{ $repository }} ({{ $branch }}).</p>
        </div>

        @if(session('status'))
            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-xs text-red-300">
                {{ session('error') }}
            </div>
        @endif

        @unless($isGitCheckout)
            <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-300">
                This install is not a git checkout, so it cannot update itself. Update it manually, or reinstall with
                <span class="font-mono">git clone</span> to enable one-click updates.
            </div>
        @endunless

        <x-admin.card class="p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    @if($updateAvailable)
                        <p class="text-sm font-semibold text-purple-300 flex items-center gap-2">
                            <i class="fa-solid fa-circle-arrow-up"></i>
                            An update is available
                        </p>
                        <p class="text-xs text-zinc-400">
                            {{ count($pendingCommits) }} {{ count($pendingCommits) === 1 ? 'commit' : 'commits' }} behind {{ $branch }}.
                        </p>
                    @elseif($latestCommit === null)
                        <p class="text-sm font-semibold text-zinc-300">Could not reach GitHub</p>
                        <p class="text-xs text-zinc-400">The version check will retry on its own.</p>
                    @else
                        <p class="text-sm font-semibold text-emerald-400 flex items-center gap-2">
                            <i class="fa-solid fa-circle-check"></i>
                            DotBio is up to date
                        </p>
                        <p class="text-xs text-zinc-400">You are running the latest commit on {{ $branch }}.</p>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <form action="{{ route('admin.update.check') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-[#181621] hover:bg-[#201d2c] border border-white/10 rounded-xl text-xs font-semibold text-zinc-200 transition cursor-pointer flex items-center gap-2">
                            <i class="fa-solid fa-rotate text-[11px]"></i>
                            Check again
                        </button>
                    </form>

                    @if($enabled && $updateAvailable)
                        <form action="{{ route('admin.update.run') }}" method="POST"
                              @submit="running = true">
                            @csrf
                            <button type="submit"
                                    :disabled="running"
                                    class="px-4 py-2 bg-purple-600 hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl text-xs font-bold text-white transition cursor-pointer flex items-center gap-2 shadow-lg shadow-purple-600/20">
                                <i class="fa-solid fa-cloud-arrow-down text-[11px]"></i>
                                <span x-text="running ? 'Updating...' : 'Update now'"></span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <div class="p-3 bg-[#09080d] border border-white/5 rounded-xl space-y-1">
                    <p class="text-[11px] text-zinc-500">Installed</p>
                    <p class="text-xs font-mono text-zinc-200">{{ $currentCommit ? substr($currentCommit, 0, 12) : 'unknown' }}</p>
                </div>
                <div class="p-3 bg-[#09080d] border border-white/5 rounded-xl space-y-1">
                    <p class="text-[11px] text-zinc-500">Latest on {{ $branch }}</p>
                    <p class="text-xs font-mono text-zinc-200">{{ $latestCommit ? substr($latestCommit['sha'], 0, 12) : 'unknown' }}</p>
                </div>
            </div>
        </x-admin.card>

        @if($pendingCommits !== [])
            <x-admin.card class="p-6 space-y-4">
                <h2 class="text-sm font-semibold text-zinc-200">What you will get</h2>

                <div class="space-y-2 max-h-[320px] overflow-y-auto pr-1">
                    @foreach($pendingCommits as $commit)
                        <div class="p-3 bg-[#09080d] border border-white/5 rounded-xl space-y-1">
                            <p class="text-xs font-medium text-zinc-200">{{ $commit['message'] }}</p>
                            <p class="text-[11px] text-zinc-500 font-mono">
                                {{ substr($commit['sha'], 0, 7) }}
                                @if($commit['author'] !== '')
                                    &middot; {{ $commit['author'] }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        @endif

        <x-admin.card class="p-6 space-y-5" x-show="hasActivity" x-cloak>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-sm font-semibold flex items-center gap-2"
                    :class="{
                        'text-purple-300': running,
                        'text-emerald-400': status.state === 'done',
                        'text-red-300': status.state === 'failed'
                    }">
                    <span x-show="running" class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                    <i x-show="status.state === 'done'" class="fa-solid fa-circle-check"></i>
                    <i x-show="status.state === 'failed'" class="fa-solid fa-circle-exclamation"></i>
                    <span x-text="headline"></span>
                </h2>

                <template x-if="status.state === 'failed' && status.from">
                    <form action="{{ route('admin.update.rollback') }}" method="POST"
                          onsubmit="return confirm('Restore the code and database from before the update?')">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 rounded-lg text-[11px] font-semibold text-red-300 transition cursor-pointer shrink-0">
                            Roll back
                        </button>
                    </form>
                </template>
            </div>

            <template x-if="status.error">
                <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 space-y-1">
                    <p class="text-[11px] font-semibold text-red-300">Failed during: <span x-text="status.step"></span></p>
                    <p class="text-xs text-red-200 font-mono break-words" x-text="status.error"></p>
                </div>
            </template>

            <div class="space-y-1.5" x-show="status.steps.length > 0">
                <template x-for="step in status.steps" :key="step.label">
                    <div class="flex items-center gap-3 text-xs">
                        <span class="w-4 shrink-0 flex items-center justify-center">
                            <i x-show="step.state === 'pending'" class="fa-regular fa-circle text-zinc-600 text-[11px]"></i>
                            <i x-show="step.state === 'running'" class="fa-solid fa-spinner fa-spin text-purple-400 text-[11px]"></i>
                            <i x-show="step.state === 'done'" class="fa-solid fa-check text-emerald-400 text-[11px]"></i>
                            <i x-show="step.state === 'failed'" class="fa-solid fa-xmark text-red-400 text-[11px]"></i>
                        </span>
                        <span x-text="step.label"
                              :class="{
                                  'text-zinc-500': step.state === 'pending',
                                  'text-purple-300 font-semibold': step.state === 'running',
                                  'text-zinc-300': step.state === 'done',
                                  'text-red-300 font-semibold': step.state === 'failed'
                              }"></span>
                    </div>
                </template>
            </div>

            <div x-data="{ showLog: false }" class="space-y-2">
                <button @click="showLog = !showLog"
                        class="text-[11px] text-zinc-400 hover:text-zinc-200 transition cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-terminal text-[10px]"></i>
                    <span x-text="showLog ? 'Hide output' : 'Show output'"></span>
                </button>

                <pre x-show="showLog" x-cloak
                     class="p-4 bg-[#09080d] border border-white/5 rounded-xl text-[11px] font-mono text-zinc-400 overflow-x-auto max-h-[320px] overflow-y-auto whitespace-pre-wrap"
                     x-text="log || 'Waiting for output...'"></pre>
            </div>
        </x-admin.card>
    </div>

    <x-slot name="scripts">
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('updatePanel', (initialStatus, statusUrl) => ({
                status: initialStatus,
                log: '',
                running: ['queued', 'running'].includes(initialStatus.state),
                timer: null,

                get hasActivity() {
                    return this.status.state !== 'idle';
                },

                get headline() {
                    if (this.status.state === 'queued') return this.status.step || 'Starting...';
                    if (this.status.state === 'running') return this.status.step || 'Working...';
                    if (this.status.state === 'done') return this.status.step || 'Done';
                    if (this.status.state === 'failed') return 'Update failed';
                    return '';
                },

                init() {
                    if (this.hasActivity) {
                        this.poll();
                    }

                    if (this.running) {
                        this.timer = setInterval(() => this.poll(), 1500);
                    }
                },

                async poll() {
                    let payload;

                    try {
                        const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                        payload = await response.json();
                    } catch (error) {
                        // PHP restarts mid-update, so an occasional failed poll is expected.
                        return;
                    }

                    const wasRunning = this.running;

                    this.status = payload.status;
                    this.log = payload.log;
                    this.running = ['queued', 'running'].includes(payload.status.state);

                    if (!wasRunning || this.running) {
                        return;
                    }

                    clearInterval(this.timer);
                    this.timer = null;

                    if (payload.status.state === 'done') {
                        setTimeout(() => window.location.reload(), 1500);
                    }
                }
            }));
        });
        </script>
    </x-slot>
</x-admin.layout>
