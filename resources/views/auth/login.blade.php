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
            <p class="text-xs text-zinc-400">Sign in with your Discord account</p>
        </div>

        @if ($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-xl text-xs space-y-1 text-left">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div>
            <a href="{{ route('auth.discord') }}" 
               class="w-full py-3 px-4 bg-[#5865F2] hover:bg-[#4752C4] text-white font-medium rounded-xl text-sm transition flex items-center justify-center gap-2.5">
                <i class="fa-brands fa-discord text-lg"></i>
                <span>Log in with Discord</span>
            </a>
        </div>

        @if(\App\Models\Profile::exists())
        <div class="pt-2">
            <a href="/" class="text-xs text-zinc-500 hover:text-zinc-300 transition">
                ← Back to public profile
            </a>
        </div>
        @endif
    </div>
</body>
</html>
