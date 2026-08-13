<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PembdaHUB Mobile')</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('192x192.png') }}">

    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN + Alpine JS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                            950: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Smooth Scrolling & Mobile Touch Adjustments */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }
        /* Custom scrollbar for mobile */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 9999px;
        }
        /* Glassmorphism utilities */
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-nav {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col justify-between overflow-x-hidden">

    <!-- Top Mobile App Header -->
    <header class="sticky top-0 z-40 w-full glass-card border-b-0 px-4 py-3 flex items-center justify-between shadow-lg">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white font-black text-lg shadow-md shadow-indigo-500/20">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-white tracking-tight leading-none">PembdaHUB</h1>
                <span class="text-[10px] font-medium text-indigo-400">Mobile Edition</span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <!-- PWA Install Button (Alpine) -->
            <div x-data="{ deferredPrompt: null, canInstall: false }" 
                 x-init="window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; canInstall = true; });">
                <button x-show="canInstall" 
                        @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then(() => { canInstall = false; });"
                        class="px-2.5 py-1 text-xs font-semibold bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 rounded-lg hover:bg-indigo-600/50 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-download"></i> Install
                </button>
            </div>

            @auth
            <!-- Notification Bell -->
            <a href="{{ route('mobile.space.index') }}" class="relative w-9 h-9 rounded-xl bg-slate-800/80 border border-slate-700/50 flex items-center justify-center text-slate-300 hover:text-white transition">
                <i class="fa-regular fa-bell text-sm"></i>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
            </a>
            @endauth
        </div>
    </header>

    <!-- Alert Messages / Toast Notifications -->
    <div class="px-4 pt-2">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                 class="mb-3 p-3.5 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mb-3 p-3.5 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-300 text-xs font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-400 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-rose-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 pb-24 px-4 pt-2 overflow-y-auto">
        @yield('content')
    </main>

    <!-- Bottom Navigation Bar (Fixed for Mobile App feel) -->
    @auth
    <nav class="fixed bottom-0 left-0 right-0 z-50 glass-nav px-3 py-2 flex items-center justify-around">
        <!-- Tab 1: Dashboard -->
        <a href="{{ route('mobile.dashboard') }}" 
           class="flex flex-col items-center py-1 px-3 rounded-xl transition duration-200 {{ request()->routeIs('mobile.dashboard') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
            <i class="fa-solid fa-house text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-tight">Beranda</span>
        </a>

        <!-- Tab 2: Pembda Space -->
        <a href="{{ route('mobile.space.index') }}" 
           class="flex flex-col items-center py-1 px-3 rounded-xl transition duration-200 {{ request()->routeIs('mobile.space.*') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
            <i class="fa-solid fa-comments text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-tight">Space</span>
        </a>

        <!-- Tab 3: LMS -->
        <a href="{{ route('mobile.lms.index') }}" 
           class="flex flex-col items-center py-1 px-3 rounded-xl transition duration-200 {{ request()->routeIs('mobile.lms.*') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
            <i class="fa-solid fa-book-open text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-tight">LMS</span>
        </a>

        <!-- Tab 4: Absensi -->
        <a href="{{ route('mobile.absensi.index') }}" 
           class="flex flex-col items-center py-1 px-3 rounded-xl transition duration-200 {{ request()->routeIs('mobile.absensi.*') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
            <i class="fa-solid fa-fingerprint text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-tight">Absensi</span>
        </a>

        <!-- Tab 5: Profile -->
        <a href="{{ route('mobile.profile') }}" 
           class="flex flex-col items-center py-1 px-3 rounded-xl transition duration-200 {{ request()->routeIs('mobile.profile') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
            <i class="fa-solid fa-circle-user text-lg mb-0.5"></i>
            <span class="text-[10px] tracking-tight">Profil</span>
        </a>
    </nav>
    @endauth

    <!-- Service Worker Registration script for PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW registered!'))
                    .catch(err => console.log('SW registration failed: ', err));
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
