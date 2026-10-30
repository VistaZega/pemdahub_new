<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PembdaCIRCLE — Wadah Kreasi, Nongki & Portofolio Siswa Pembda')</title>

    <!-- OpenGraph Social Card Tags (Optimal untuk Share WhatsApp & Instagram) -->
    @yield('og_meta')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Tailwind CSS (Vite & CDN Fallback) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Space Grotesk"', 'sans-serif'],
                    },
                    colors: {
                        circle: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 9999px;
        }
    </style>
    @yield('styles')
</head>
<body class="bg-gray-50 text-gray-900 font-sans min-h-screen pb-20 md:pb-10 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-200 transition-all">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <a href="{{ route('circle.feed') }}" class="flex items-center gap-2.5 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 flex items-center justify-center text-white shadow-md shadow-indigo-200 group-hover:scale-105 transition-transform">
                    <i class="fas fa-bullseye text-lg"></i>
                </div>
                <div>
                    <span class="font-display font-extrabold text-xl tracking-tight text-gray-900 flex items-center gap-1.5">
                        Pembda<span class="bg-gradient-to-r from-indigo-600 to-pink-600 bg-clip-text text-transparent">CIRCLE</span>
                    </span>
                    <span class="text-[10px] font-semibold text-gray-400 block -mt-1 tracking-wider uppercase">Social & Portfolio Hub</span>
                </div>
            </a>

            <!-- Quick Navigation Unit Filters -->
            <div class="hidden md:flex items-center gap-1.5 bg-gray-100/80 p-1 rounded-xl text-xs font-semibold">
                <a href="{{ route('circle.feed') }}" class="px-3 py-1.5 rounded-lg {{ !request('school') ? 'bg-white text-indigo-700 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">Semua Unit</a>
                <a href="{{ route('circle.feed', ['school' => 'SMK']) }}" class="px-3 py-1.5 rounded-lg {{ request('school') == 'SMK' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:text-orange-600' }}">
                    <i class="fas fa-wrench mr-1 text-[10px]"></i>SMK Pembda
                </a>
                <a href="{{ route('circle.feed', ['school' => 'SMA']) }}" class="px-3 py-1.5 rounded-lg {{ request('school') == 'SMA' ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:text-blue-600' }}">
                    <i class="fas fa-atom mr-1 text-[10px]"></i>SMA Pembda
                </a>
                <a href="{{ route('circle.feed', ['school' => 'SMP']) }}" class="px-3 py-1.5 rounded-lg {{ request('school') == 'SMP' ? 'bg-emerald-600 text-white shadow-xs' : 'text-gray-600 hover:text-emerald-600' }}">
                    <i class="fas fa-seedling mr-1 text-[10px]"></i>SMP Pembda
                </a>
            </div>

            <!-- User Auth Bar -->
            <div class="flex items-center gap-3">
                @auth
                    @php
                        $user = Auth::user();
                        $profile = $user->circleProfile ?? \App\Models\CircleProfile::getOrCreateForUser($user);
                    @endphp
                    <a href="{{ route('circle.profile', ['username' => $profile->username]) }}" class="flex items-center gap-2 pl-2 pr-3 py-1 rounded-full bg-gray-100 hover:bg-gray-200 transition">
                        <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" alt="{{ $user->name }}" class="w-7 h-7 rounded-full object-cover border border-white shadow-xs">
                        <span class="text-xs font-bold text-gray-800 max-w-[100px] truncate">{{ $profile->nickname ?? $user->name }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-sm transition">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk Akun</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation (Instagram Style) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-gray-200 px-6 py-2 flex items-center justify-around shadow-lg">
        <a href="{{ route('circle.feed') }}" class="flex flex-col items-center text-xs {{ request()->routeIs('circle.feed') && !request('tab') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
            <i class="fas fa-home text-lg"></i>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="{{ route('circle.feed', ['tab' => 'reels']) }}" class="flex flex-col items-center text-xs {{ request('tab') == 'reels' ? 'text-pink-600 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
            <i class="fas fa-play-circle text-lg"></i>
            <span class="text-[10px] mt-0.5">Reels</span>
        </a>
        <a href="{{ route('circle.feed', ['tab' => 'portfolio']) }}" class="flex flex-col items-center text-xs {{ request('tab') == 'portfolio' ? 'text-amber-600 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
            <i class="fas fa-trophy text-lg"></i>
            <span class="text-[10px] mt-0.5">Karya</span>
        </a>
        @auth
            <a href="{{ route('circle.profile', ['username' => Auth::user()->circleProfile->username ?? Auth::user()->username]) }}" class="flex flex-col items-center text-xs {{ request()->routeIs('circle.profile') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
                <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.svg') }}" class="w-5 h-5 rounded-full object-cover border border-gray-300">
                <span class="text-[10px] mt-0.5">Profil</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="flex flex-col items-center text-xs text-gray-500 hover:text-gray-900">
                <i class="fas fa-user text-lg"></i>
                <span class="text-[10px] mt-0.5">Masuk</span>
            </a>
        @endauth
    </nav>

    @yield('scripts')
</body>
</html>
