<!DOCTYPE html>
<html lang="en" class="h-full bg-[#09090b] text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DotBio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-[#09090b]">
    <div class="w-full max-w-sm bg-[#121215] border border-zinc-800/80 rounded-2xl p-7 text-center space-y-6">
        <div class="space-y-1.5">
            <h1 class="text-2xl font-bold tracking-tight text-white">DotBio</h1>
            <p class="text-xs text-zinc-400">Sign in to manage your page</p>
        </div>

        @if ($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-xl text-xs space-y-1 text-left">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(in_array($loginMethod, [null, 'password'], true))
        <form method="POST" action="{{ route('login.store') }}" class="space-y-3 text-left">
            @csrf
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="email"
                   class="w-full px-4 py-3 bg-[#09090b] border border-zinc-800 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password"
                   class="w-full px-4 py-3 bg-[#09090b] border border-zinc-800 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
            <label class="flex items-center gap-2 text-xs text-zinc-400">
                <input type="checkbox" name="remember" value="1" class="accent-zinc-400">
                Remember me
            </label>
            <button type="submit" class="w-full py-3 px-4 bg-white hover:bg-zinc-200 text-black font-medium rounded-xl text-sm transition">
                Log in
            </button>
        </form>
        @endif

        @if($loginMethod === null)
        <div class="flex items-center gap-3 text-xs text-zinc-500">
            <span class="flex-1 h-px bg-zinc-800"></span>
            or
            <span class="flex-1 h-px bg-zinc-800"></span>
        </div>
        @endif

        <div class="space-y-2.5">
            @foreach([
                'discord' => ['Discord', 'bg-[#5865F2] hover:bg-[#4752C4] text-white'],
                'google' => ['Google', 'bg-white hover:bg-zinc-200 text-black'],
                'github' => ['GitHub', 'bg-zinc-800 hover:bg-zinc-700 text-white'],
            ] as $provider => [$label, $classes])
                @if(in_array($loginMethod, [null, $provider], true))
                <a href="{{ route('auth.redirect', $provider) }}"
                   class="w-full py-3 px-4 {{ $classes }} font-medium rounded-xl text-sm transition flex items-center justify-center gap-2.5">
                    <i class="fa-brands fa-{{ $provider }} text-lg"></i>
                    <span>Log in with {{ $label }}</span>
                </a>
                @endif
            @endforeach
        </div>

        @if($loginMethod === null)
        <p class="text-xs text-zinc-500">
            No account yet? <a href="{{ route('register') }}" class="text-zinc-300 hover:text-white transition">Create your DotBio</a>
        </p>
        @endif

        @if($hasProfile)
        <div class="pt-2">
            <a href="/" class="text-xs text-zinc-500 hover:text-zinc-300 transition">
                ← Back to public profile
            </a>
        </div>
        @endif
    </div>
</body>
</html>
