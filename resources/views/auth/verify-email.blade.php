<!DOCTYPE html>
<html lang="en" class="h-full bg-[#09090b] text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify email - DotBio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-[#09090b]">
    <div class="w-full max-w-sm bg-[#121215] border border-zinc-800/80 rounded-2xl p-7 text-center space-y-6">
        <div class="space-y-1.5">
            <h1 class="text-2xl font-bold tracking-tight text-white">DotBio</h1>
            <p class="text-xs text-zinc-400">Check your inbox to verify your email</p>
        </div>

        @if (session('status'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-3 rounded-xl text-xs text-left">
                {{ session('status') }}
            </div>
        @endif

        <p class="text-sm text-zinc-300">
            We sent a verification link to <span class="text-white font-medium">{{ $email }}</span>. Click it to access your admin panel.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full py-3 px-4 bg-white hover:bg-zinc-200 text-black font-medium rounded-xl text-sm transition">
                Resend the link
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs text-zinc-500 hover:text-zinc-300 transition">Log out</button>
        </form>
    </div>
</body>
</html>
