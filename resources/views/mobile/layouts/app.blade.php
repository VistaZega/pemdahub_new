<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f4f7fc] text-slate-800 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PembdaHUB Playful Mobile')</title>

    <!-- PWA Manifest & App Icons -->
    <link rel="manifest" href="{{ asset('manifest.json?v=6') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png?v=6') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icons/icon-192x192.png?v=6') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icons/icon-512x512.png?v=6') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,500;0,600;0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
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
                    },
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            background-color: #f4f7fc;
            color: #1e293b;
        }
        
        /* 3D Claymorphism Utility Classes */
        .clay-card {
            background: #ffffff;
            border-radius: 1.5rem;
            padding: 1.25rem;
            box-shadow: 
                8px 12px 24px rgba(15, 23, 42, 0.06), 
                -4px -4px 12px rgba(255, 255, 255, 0.9), 
                inset 2px 2px 4px rgba(255, 255, 255, 0.8), 
                inset -2px -2px 4px rgba(15, 23, 42, 0.03);
            border: 2px solid rgba(255, 255, 255, 0.8);
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }
        .clay-card:active {
            transform: scale(0.96);
        }

        .clay-blue {
            background: linear-gradient(145deg, #60a5fa, #3b82f6);
            color: #ffffff;
            border-radius: 2rem;
            box-shadow: 
                6px 10px 20px rgba(59, 130, 246, 0.35), 
                inset 2px 2px 6px rgba(255, 255, 255, 0.4), 
                inset -3px -3px 6px rgba(29, 78, 216, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .clay-yellow {
            background: linear-gradient(145deg, #fbbf24, #f59e0b);
            color: #ffffff;
            border-radius: 2rem;
            box-shadow: 
                6px 10px 20px rgba(245, 158, 11, 0.35), 
                inset 2px 2px 6px rgba(255, 255, 255, 0.5), 
                inset -3px -3px 6px rgba(180, 83, 9, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .clay-green {
            background: linear-gradient(145deg, #34d399, #10b981);
            color: #ffffff;
            border-radius: 2rem;
            box-shadow: 
                6px 10px 20px rgba(16, 185, 129, 0.35), 
                inset 2px 2px 6px rgba(255, 255, 255, 0.4), 
                inset -3px -3px 6px rgba(4, 120, 87, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .clay-pink {
            background: linear-gradient(145deg, #fb7185, #f43f5e);
            color: #ffffff;
            border-radius: 2rem;
            box-shadow: 
                6px 10px 20px rgba(244, 63, 94, 0.35), 
                inset 2px 2px 6px rgba(255, 255, 255, 0.4), 
                inset -3px -3px 6px rgba(190, 18, 60, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .clay-purple {
            background: linear-gradient(145deg, #a78bfa, #8b5cf6);
            color: #ffffff;
            border-radius: 2rem;
            box-shadow: 
                6px 10px 20px rgba(139, 92, 246, 0.35), 
                inset 2px 2px 6px rgba(255, 255, 255, 0.4), 
                inset -3px -3px 6px rgba(109, 40, 217, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .clay-cyan {
            background: linear-gradient(145deg, #22d3ee, #06b6d4);
            color: #ffffff;
            border-radius: 2rem;
            box-shadow: 
                6px 10px 20px rgba(6, 182, 212, 0.35), 
                inset 2px 2px 6px rgba(255, 255, 255, 0.4), 
                inset -3px -3px 6px rgba(14, 116, 144, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .clay-orange {
            background: linear-gradient(145deg, #fb923c, #f97316);
            color: #ffffff;
            border-radius: 2rem;
            box-shadow: 
                6px 10px 20px rgba(249, 115, 22, 0.35), 
                inset 2px 2px 6px rgba(255, 255, 255, 0.4), 
                inset -3px -3px 6px rgba(194, 65, 12, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .clay-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 2.5rem;
            box-shadow: 
                0 16px 32px rgba(15, 23, 42, 0.12),
                inset 2px 2px 4px rgba(255, 255, 255, 0.9);
            border: 3px solid #ffffff;
        }

        .clay-btn {
            background: linear-gradient(145deg, #3b82f6, #2563eb);
            color: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 
                4px 6px 14px rgba(37, 99, 235, 0.35),
                inset 2px 2px 4px rgba(255, 255, 255, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.4);
        }
        .clay-btn:active {
            transform: scale(0.95);
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-[#f4f7fc] text-slate-800 flex flex-col justify-between overflow-x-hidden">

    <!-- Top Playful Clay Header -->
    <header class="sticky top-0 z-40 w-full max-w-md mx-auto bg-[#f4f7fc]/90 backdrop-blur-md border-b border-slate-200/60 px-5 py-3.5 flex items-center justify-between"
            x-data="{ showRoleModal: false }">
        <div class="flex items-center space-x-3">
            <div class="w-11 h-11 rounded-2xl bg-white p-1 shadow-lg shadow-purple-500/20 border-2 border-white flex items-center justify-center shrink-0">
                <img src="{{ asset('images/app-logo.png?v=6') }}" alt="PembdaHUB Logo" class="w-full h-full object-contain rounded-xl">
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 tracking-tight leading-none">PembdaHUB</h1>
                <span class="text-[10px] font-extrabold text-blue-600 tracking-wider uppercase">Playful 3D App</span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @auth
            <!-- Role Switcher Button -->
            @php $currentRole = session('active_role', auth()->user()->role); @endphp
            @if(auth()->user()->isOwnerOrSuperAdmin() || auth()->user()->isGuru() || auth()->user()->isAdminSekolah() || auth()->user()->isKepalaSekolah())
                <button @click="showRoleModal = true" 
                        class="px-3.5 py-2 text-xs font-black bg-white text-blue-600 border-2 border-blue-200 rounded-2xl hover:bg-blue-50 flex items-center gap-1.5 transition shadow-sm">
                    <i class="fa-solid fa-repeat text-blue-600 text-xs"></i>
                    <span class="capitalize">{{ str_replace('_', ' ', $currentRole) }}</span>
                </button>
            @endif

            <!-- PWA Install Button -->
            <div x-data="{ deferredPrompt: null, canInstall: false }" 
                 x-init="window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; canInstall = true; });">
                <button x-show="canInstall" 
                        @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then(() => { canInstall = false; });"
                        class="w-10 h-10 rounded-2xl bg-white border-2 border-slate-200 flex items-center justify-center text-slate-700 hover:text-blue-600 transition shadow-sm">
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
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
             style="display: none;">
            <div class="bg-[#f4f7fc] rounded-3xl p-6 w-full max-w-xs shadow-2xl border-4 border-white space-y-4">
                <div class="flex items-center justify-between border-b-2 border-slate-200/80 pb-3">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-repeat text-blue-600"></i> Beralih Peran (Switch Role)
                    </h3>
                    <button @click="showRoleModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <p class="text-xs text-slate-600 font-bold">Pilih mode tampilan aktif:</p>

                <form action="{{ route('mobile.switch-role') }}" method="POST" class="space-y-2.5">
                    @csrf
                    @if(auth()->user()->isOwnerOrSuperAdmin())
                        <button type="submit" name="role" value="superadmin" class="w-full text-left p-3.5 rounded-2xl bg-white border-2 border-slate-200 text-xs font-black hover:bg-blue-50 hover:border-blue-300 transition flex items-center justify-between shadow-sm">
                            <span>👑 Super Admin</span>
                            @if($currentRole === 'superadmin')<i class="fa-solid fa-circle-check text-blue-600 text-base"></i>@endif
                        </button>

                        <button type="submit" name="role" value="ketua_yayasan" class="w-full text-left p-3.5 rounded-2xl bg-white border-2 border-slate-200 text-xs font-black hover:bg-purple-50 hover:border-purple-300 transition flex items-center justify-between shadow-sm">
                            <span>🏛️ Ketua Yayasan</span>
                            @if($currentRole === 'ketua_yayasan')<i class="fa-solid fa-circle-check text-purple-600 text-base"></i>@endif
                        </button>
                    @endif

                    @if(auth()->user()->isOwnerOrSuperAdmin() || auth()->user()->isGuru() || auth()->user()->isAdminSekolah())
                        <button type="submit" name="role" value="guru" class="w-full text-left p-3.5 rounded-2xl bg-white border-2 border-slate-200 text-xs font-black hover:bg-blue-50 hover:border-blue-300 transition flex items-center justify-between shadow-sm">
                            <span>👨‍🏫 Guru / Tenaga Pendidik</span>
                            @if($currentRole === 'guru')<i class="fa-solid fa-circle-check text-blue-600 text-base"></i>@endif
                        </button>
                    @endif

                    @if(auth()->user()->isOwnerOrSuperAdmin() || auth()->user()->hasRole('siswa'))
                        <button type="submit" name="role" value="siswa" class="w-full text-left p-3.5 rounded-2xl bg-white border-2 border-slate-200 text-xs font-black hover:bg-blue-50 hover:border-blue-300 transition flex items-center justify-between shadow-sm">
                            <span>🎓 Siswa</span>
                            @if($currentRole === 'siswa')<i class="fa-solid fa-circle-check text-blue-600 text-base"></i>@endif
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
                 class="mb-3 p-4 rounded-2xl bg-emerald-500 text-white text-xs font-black flex items-center justify-between shadow-lg shadow-emerald-500/25 border-2 border-white">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mb-3 p-4 rounded-2xl bg-rose-500 text-white text-xs font-black flex items-center justify-between shadow-lg shadow-rose-500/25 border-2 border-white">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="show = false"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 pb-32 px-5 pt-1 overflow-y-auto">
        @yield('content')
    </main>

    <!-- Floating 3D Clay Navigation Bar -->
    @auth
    <div class="fixed bottom-4 left-5 right-5 z-50">
        <nav class="clay-nav px-3 py-2.5 flex items-center justify-around">
            <!-- Tab 1: Dashboard -->
            <a href="{{ route('mobile.dashboard') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.dashboard') ? 'text-blue-600 font-black bg-blue-50 scale-110 shadow-sm border border-blue-200' : 'text-slate-400 hover:text-slate-700' }}">
                <i class="fa-solid fa-house text-lg mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Beranda</span>
            </a>

            <!-- Tab 2: Pembda Space -->
            <a href="{{ route('mobile.space.index') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.space.*') ? 'text-blue-600 font-black bg-blue-50 scale-110 shadow-sm border border-blue-200' : 'text-slate-400 hover:text-slate-700' }}">
                <i class="fa-solid fa-comments text-lg mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Space</span>
            </a>

            <!-- Tab 3: LMS -->
            <a href="{{ route('mobile.lms.index') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.lms.*') ? 'text-blue-600 font-black bg-blue-50 scale-110 shadow-sm border border-blue-200' : 'text-slate-400 hover:text-slate-700' }}">
                <i class="fa-solid fa-book-open text-lg mb-0.5"></i>
                <span class="text-[10px] tracking-tight">LMS</span>
            </a>

            <!-- Tab 4: Absensi -->
            <a href="{{ route('mobile.absensi.index') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.absensi.*') ? 'text-blue-600 font-black bg-blue-50 scale-110 shadow-sm border border-blue-200' : 'text-slate-400 hover:text-slate-700' }}">
                <i class="fa-solid fa-fingerprint text-lg mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Absensi</span>
            </a>

            <!-- Tab 5: Profile -->
            <a href="{{ route('mobile.profile') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.profile') ? 'text-blue-600 font-black bg-blue-50 scale-110 shadow-sm border border-blue-200' : 'text-slate-400 hover:text-slate-700' }}">
                <i class="fa-solid fa-circle-user text-lg mb-0.5"></i>
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
