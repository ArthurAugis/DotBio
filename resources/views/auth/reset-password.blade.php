<!DOCTYPE html>
<html lang="en" class="h-full bg-[#09090b] text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password - DotBio</title>
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
            <p class="text-xs text-zinc-400">Choose a new password</p>
        </div>

        @if ($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-xl text-xs space-y-1 text-left">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-3 text-left">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="Email" required autocomplete="email"
                   class="w-full px-4 py-3 bg-[#09090b] border border-zinc-800 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
            <input type="password" name="password" placeholder="New password" required autocomplete="new-password"
                   class="w-full px-4 py-3 bg-[#09090b] border border-zinc-800 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
            <input type="password" name="password_confirmation" placeholder="Confirm new password" required autocomplete="new-password"
                   class="w-full px-4 py-3 bg-[#09090b] border border-zinc-800 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
            <button type="submit" class="w-full py-3 px-4 bg-white hover:bg-zinc-200 text-black font-medium rounded-xl text-sm transition">
                Reset password
            </button>
        </form>
    </div>
</body>
</html>
