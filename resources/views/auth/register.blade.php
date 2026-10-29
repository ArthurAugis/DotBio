<!DOCTYPE html>
<html lang="en" class="h-full bg-[#09090b] text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DotBio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-[#09090b]">
    <div class="w-full max-w-sm bg-[#121215] border border-zinc-800/80 rounded-2xl p-7 text-center space-y-6">
        <div class="space-y-1.5">
            <h1 class="text-2xl font-bold tracking-tight text-white">DotBio</h1>
            <p class="text-xs text-zinc-400">{{ $invited ? 'Create your admin account with the email you were invited with' : 'Create your page with an email and password' }}</p>
        </div>

        @if ($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-xl text-xs space-y-1 text-left">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="space-y-3 text-left"
              x-data="{ preview: null }">
            @csrf
            @unless($invited)
            <label class="flex items-center gap-3 cursor-pointer">
                <span class="w-14 h-14 rounded-full bg-[#09090b] border border-zinc-800 overflow-hidden flex items-center justify-center text-[10px] text-zinc-500 shrink-0">
                    <img x-show="preview" :src="preview" alt="" class="w-full h-full object-cover">
                    <span x-show="! preview">Avatar</span>
                </span>
                <span class="text-xs text-zinc-400">Choose a profile picture (optional)</span>
                <input type="file" name="avatar_file" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden"
                       @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
            </label>
            @endunless
            <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" required autocomplete="username"
                   class="w-full px-4 py-3 bg-[#09090b] border border-zinc-800 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="email"
                   class="w-full px-4 py-3 bg-[#09090b] border border-zinc-800 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
            <input type="password" name="password" placeholder="Password" required autocomplete="new-password"
                   class="w-full px-4 py-3 bg-[#09090b] border border-zinc-800 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
            <input type="password" name="password_confirmation" placeholder="Confirm password" required autocomplete="new-password"
                   class="w-full px-4 py-3 bg-[#09090b] border border-zinc-800 rounded-xl text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-600">
            <button type="submit" class="w-full py-3 px-4 bg-white hover:bg-zinc-200 text-black font-medium rounded-xl text-sm transition">
                {{ $invited ? 'Create my account' : 'Create my DotBio' }}
            </button>
        </form>

        <p class="text-xs text-zinc-500">
            Already have an account? <a href="{{ route('login') }}" class="text-zinc-300 hover:text-white transition">Log in</a>
        </p>
    </div>
</body>
</html>
