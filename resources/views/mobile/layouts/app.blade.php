<!DOCTYPE html>
<html lang="id" class="h-full bg-[#faf8f5] text-slate-800 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#f3efe8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PembdaHUB Mobile Serene')</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('192x192.png') }}">

    <!-- Google Fonts: Playfair Display (Serene Serif) & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN + Alpine JS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    },
                    colors: {
                        pastel: {
                            lavender: '#e5deff',
                            sage: '#d8e8d8',
                            peach: '#fde5d4',
                            rose: '#ffd6db',
                            cream: '#faf8f5',
                            terracotta: '#e8a58a',
                            mint: '#cbe6d5',
                            sand: '#f5edd6',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            background-color: #faf8f5;
            color: #2d3748;
        }
        .serene-card {
            background: #ffffff;
            border-radius: 1.75rem;
            border: 1px solid rgba(230, 224, 215, 0.7);
            box-shadow: 0 12px 32px -8px rgba(180, 170, 160, 0.12);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .serene-card:active {
            transform: scale(0.97);
        }
        .serene-nav {
            background: rgba(232, 228, 245, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 16px 40px -10px rgba(120, 110, 150, 0.2);
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-[#faf8f5] text-slate-800 flex flex-col justify-between overflow-x-hidden">

    <!-- Top Serene Header -->
    <header class="sticky top-0 z-40 w-full bg-[#faf8f5]/90 backdrop-blur-md border-b border-stone-200/50 px-5 py-3.5 flex items-center justify-between"
            x-data="{ showRoleModal: false }">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-300 via-indigo-200 to-rose-200 flex items-center justify-center text-purple-900 font-extrabold text-xl shadow-sm border border-white">
                <i class="fa-solid fa-spa"></i>
            </div>
            <div>
                <h1 class="text-lg font-serif font-bold text-slate-800 tracking-tight leading-none">PembdaHUB</h1>
                <span class="text-[10px] font-semibold text-purple-700 tracking-wide">Serene Mobile</span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @auth
            <!-- Role Switcher Button -->
            @php $currentRole = session('active_role', auth()->user()->role); @endphp
            @if(auth()->user()->isOwnerOrSuperAdmin() || auth()->user()->isGuru() || auth()->user()->isAdminSekolah() || auth()->user()->isKepalaSekolah())
                <button @click="showRoleModal = true" 
                        class="px-3 py-1.5 text-xs font-bold bg-purple-100/70 text-purple-900 border border-purple-200/80 rounded-2xl hover:bg-purple-200 flex items-center gap-1.5 transition shadow-sm">
                    <i class="fa-solid fa-repeat text-purple-700 text-xs"></i>
                    <span class="capitalize">{{ str_replace('_', ' ', $currentRole) }}</span>
                </button>
            @endif

            <!-- PWA Install Button -->
            <div x-data="{ deferredPrompt: null, canInstall: false }" 
                 x-init="window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; canInstall = true; });">
                <button x-show="canInstall" 
                        @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then(() => { canInstall = false; });"
                        class="w-10 h-10 rounded-2xl bg-white border border-stone-200/70 flex items-center justify-center text-slate-700 hover:text-purple-700 transition shadow-sm">
                    <i class="fa-solid fa-download text-sm"></i>
                </button>
            </div>
            @endauth
        </div>

        <!-- Role Switcher Modal -->
        @auth
        <div x-show="showRoleModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
             style="display: none;">
            <div class="bg-[#faf8f5] rounded-3xl p-6 w-full max-w-xs shadow-2xl border border-white space-y-4">
                <div class="flex items-center justify-between border-b border-stone-200/70 pb-3">
                    <h3 class="text-sm font-serif font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-repeat text-purple-600"></i> Beralih Peran (Switch Role)
                    </h3>
                    <button @click="showRoleModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <p class="text-xs text-slate-600 font-medium">Pilih tampilan role aktif untuk akun Anda:</p>

                <form action="{{ route('mobile.switch-role') }}" method="POST" class="space-y-2">
                    @csrf
                    @if(auth()->user()->isOwnerOrSuperAdmin())
                        <button type="submit" name="role" value="superadmin" class="w-full text-left p-3.5 rounded-2xl bg-white border border-stone-200 text-xs font-bold hover:bg-purple-50 hover:border-purple-300 transition flex items-center justify-between shadow-sm">
                            <span>👑 Super Admin / Yayasan</span>
                            @if($currentRole === 'superadmin')<i class="fa-solid fa-circle-check text-purple-600 text-sm"></i>@endif
                        </button>
                    @endif

                    @if(auth()->user()->isOwnerOrSuperAdmin() || auth()->user()->isGuru() || auth()->user()->isAdminSekolah())
                        <button type="submit" name="role" value="guru" class="w-full text-left p-3.5 rounded-2xl bg-white border border-stone-200 text-xs font-bold hover:bg-purple-50 hover:border-purple-300 transition flex items-center justify-between shadow-sm">
                            <span>👨‍🏫 Guru / Tenaga Pendidik</span>
                            @if($currentRole === 'guru')<i class="fa-solid fa-circle-check text-purple-600 text-sm"></i>@endif
                        </button>
                    @endif

                    @if(auth()->user()->isOwnerOrSuperAdmin() || auth()->user()->hasRole('siswa'))
                        <button type="submit" name="role" value="siswa" class="w-full text-left p-3.5 rounded-2xl bg-white border border-stone-200 text-xs font-bold hover:bg-purple-50 hover:border-purple-300 transition flex items-center justify-between shadow-sm">
                            <span>🎓 Siswa</span>
                            @if($currentRole === 'siswa')<i class="fa-solid fa-circle-check text-purple-600 text-sm"></i>@endif
                        </button>
                    @endif
                </form>
            </div>
        </div>
        @endauth
    </header>

    <!-- Toast Notifications -->
    <div class="px-5 pt-3">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                 class="mb-3 p-4 rounded-2xl bg-emerald-100/70 border border-emerald-200 text-emerald-900 text-xs font-bold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mb-3 p-4 rounded-2xl bg-rose-100/70 border border-rose-200 text-rose-900 text-xs font-bold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-rose-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 pb-32 px-5 pt-1 overflow-y-auto">
        @yield('content')
    </main>

    <!-- Floating Serene Bottom Navigation Bar -->
    @auth
    <div class="fixed bottom-4 left-5 right-5 z-50">
        <nav class="serene-nav rounded-3xl px-3 py-2.5 flex items-center justify-around">
            <!-- Tab 1: Dashboard -->
            <a href="{{ route('mobile.dashboard') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.dashboard') ? 'text-purple-900 font-extrabold bg-white/90 shadow-sm scale-105 border border-purple-200/50' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fa-solid fa-leaf text-base mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Beranda</span>
            </a>

            <!-- Tab 2: Pembda Space -->
            <a href="{{ route('mobile.space.index') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.space.*') ? 'text-purple-900 font-extrabold bg-white/90 shadow-sm scale-105 border border-purple-200/50' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fa-solid fa-comments text-base mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Space</span>
            </a>

            <!-- Tab 3: LMS -->
            <a href="{{ route('mobile.lms.index') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.lms.*') ? 'text-purple-900 font-extrabold bg-white/90 shadow-sm scale-105 border border-purple-200/50' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fa-solid fa-book-open text-base mb-0.5"></i>
                <span class="text-[10px] tracking-tight">LMS</span>
            </a>

            <!-- Tab 4: Absensi -->
            <a href="{{ route('mobile.absensi.index') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.absensi.*') ? 'text-purple-900 font-extrabold bg-white/90 shadow-sm scale-105 border border-purple-200/50' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fa-solid fa-fingerprint text-base mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Absensi</span>
            </a>

            <!-- Tab 5: Profile -->
            <a href="{{ route('mobile.profile') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.profile') ? 'text-purple-900 font-extrabold bg-white/90 shadow-sm scale-105 border border-purple-200/50' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fa-solid fa-circle-user text-base mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Profil</span>
            </a>
        </nav>
    </div>
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
