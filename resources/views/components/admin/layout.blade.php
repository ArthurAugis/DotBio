@props([
    'title',
    'lang' => 'en',
    'bodyClass' => 'h-full flex overflow-hidden select-none bg-[#0d0c10]',
    'pageClass' => 'flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 lg:space-y-8 bg-[#0d0c10]',
])

<!DOCTYPE html>
<html lang="{{ $lang }}" class="h-full bg-[#0a0a0c] text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @php
        $adminFavicon = \App\Models\Profile::first()?->favicon_source;
    @endphp
    @if($adminFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $adminFavicon }}">
        <link rel="shortcut icon" href="{{ $adminFavicon }}">
        <link rel="apple-touch-icon" href="{{ $adminFavicon }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; background-color: #0d0c10; color: #e4e4e7; }

        .font-space { font-family: 'Space Grotesk', sans-serif; }
        .dotbio-card {
            background-color: #121017;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
        }

        @media (max-width: 767.98px) {
            .admin-desktop-sidebar { display: none !important; }
            .admin-root-layout { flex-direction: column !important; }
        }
        @media (min-width: 768px) {
            .admin-mobile-header { display: none !important; }
            .admin-root-layout { flex-direction: row !important; }
        }
    </style>

    {{ $head ?? '' }}
</head>
<body {{ $attributes->merge(['class' => 'h-full flex admin-root-layout overflow-hidden select-none bg-[#0d0c10]']) }}>
    @include('admin.partials.sidebar')

    <main class="{{ $pageClass }}">
        {{ $slot }}
    </main>

    {{ $scripts ?? '' }}
</body>
</html>