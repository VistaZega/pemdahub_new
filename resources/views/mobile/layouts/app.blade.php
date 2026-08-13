<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 text-slate-900 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PembdaHUB Mobile')</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('192x192.png') }}">

    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
                    boxShadow: {
                        'pro': '0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01)',
                        'card': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                        'glow-indigo': '0 10px 20px -5px rgba(79, 70, 229, 0.3)',
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
            background: #f8fafc;
        }
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 9999px;
        }
        .pro-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.04);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pro-card:active {
            transform: scale(0.98);
        }
        .nav-floating {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-50 text-slate-900 flex flex-col justify-between overflow-x-hidden">

    <!-- Top Mobile Header -->
    <header class="sticky top-0 z-40 w-full bg-white/90 backdrop-blur-xl border-b border-slate-200/80 px-4 py-3 flex items-center justify-between shadow-sm"
            x-data="{ showRoleModal: false }">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-xl shadow-md shadow-indigo-500/25">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <h1 class="text-base font-black text-slate-900 tracking-tight leading-none">PembdaHUB</h1>
                <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-wide">Mobile Pro</span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @auth
            <!-- Role Switcher Button -->
            @php $currentRole = session('active_role', auth()->user()->role); @endphp
            @if(auth()->user()->isOwnerOrSuperAdmin() || auth()->user()->isGuru() || auth()->user()->isAdminSekolah() || auth()->user()->isKepalaSekolah())
                <button @click="showRoleModal = true" 
                        class="px-3 py-1.5 text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200/80 rounded-xl hover:bg-indigo-100 flex items-center gap-1.5 transition shadow-sm">
                    <i class="fa-solid fa-repeat text-indigo-600 text-xs"></i>
                    <span class="capitalize">{{ str_replace('_', ' ', $currentRole) }}</span>
                </button>
            @endif

            <!-- PWA Install Button -->
            <div x-data="{ deferredPrompt: null, canInstall: false }" 
                 x-init="window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; canInstall = true; });">
                <button x-show="canInstall" 
                        @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then(() => { canInstall = false; });"
                        class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 hover:text-indigo-600 transition">
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
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             style="display: none;">
            <div class="bg-white rounded-3xl p-5 w-full max-w-xs shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-repeat text-indigo-600"></i> Beralih Peran (Switch Role)
                    </h3>
                    <button @click="showRoleModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <p class="text-xs text-slate-500 font-medium">Pilih tampilan role aktif untuk akun Anda:</p>

                <form action="{{ route('mobile.switch-role') }}" method="POST" class="space-y-2">
                    @csrf
                    @if(auth()->user()->isOwnerOrSuperAdmin())
                        <button type="submit" name="role" value="superadmin" class="w-full text-left p-3 rounded-2xl border border-slate-200 text-xs font-bold hover:bg-indigo-50 hover:border-indigo-300 transition flex items-center justify-between">
                            <span>👑 Super Admin / Yayasan</span>
                            @if($currentRole === 'superadmin')<i class="fa-solid fa-circle-check text-indigo-600 text-sm"></i>@endif
                        </button>
                    @endif

                    @if(auth()->user()->isOwnerOrSuperAdmin() || auth()->user()->isGuru() || auth()->user()->isAdminSekolah())
                        <button type="submit" name="role" value="guru" class="w-full text-left p-3 rounded-2xl border border-slate-200 text-xs font-bold hover:bg-indigo-50 hover:border-indigo-300 transition flex items-center justify-between">
                            <span>👨‍🏫 Guru / Tenaga Pendidik</span>
                            @if($currentRole === 'guru')<i class="fa-solid fa-circle-check text-indigo-600 text-sm"></i>@endif
                        </button>
                    @endif

                    @if(auth()->user()->isOwnerOrSuperAdmin() || auth()->user()->hasRole('siswa'))
                        <button type="submit" name="role" value="siswa" class="w-full text-left p-3 rounded-2xl border border-slate-200 text-xs font-bold hover:bg-indigo-50 hover:border-indigo-300 transition flex items-center justify-between">
                            <span>🎓 Siswa</span>
                            @if($currentRole === 'siswa')<i class="fa-solid fa-circle-check text-indigo-600 text-sm"></i>@endif
                        </button>
                    @endif
                </form>
            </div>
        </div>
        @endauth
    </header>

    <!-- Alert Messages / Toast Notifications -->
    <div class="px-4 pt-3">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                 class="mb-3 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mb-3 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-rose-500 hover:text-rose-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 pb-28 px-4 pt-1 overflow-y-auto">
        @yield('content')
    </main>

    <!-- Floating Bottom Navigation Bar (UI/UX Pro Max) -->
    @auth
    <div class="fixed bottom-3 left-4 right-4 z-50">
        <nav class="nav-floating rounded-3xl px-3 py-2 flex items-center justify-around">
            <!-- Tab 1: Dashboard -->
            <a href="{{ route('mobile.dashboard') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.dashboard') ? 'text-indigo-600 font-black bg-indigo-50/80 scale-105' : 'text-slate-400 hover:text-slate-700' }}">
                <i class="fa-solid fa-house text-base mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Beranda</span>
            </a>

            <!-- Tab 2: Pembda Space -->
            <a href="{{ route('mobile.space.index') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.space.*') ? 'text-indigo-600 font-black bg-indigo-50/80 scale-105' : 'text-slate-400 hover:text-slate-700' }}">
                <i class="fa-solid fa-comments text-base mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Space</span>
            </a>

            <!-- Tab 3: LMS -->
            <a href="{{ route('mobile.lms.index') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.lms.*') ? 'text-indigo-600 font-black bg-indigo-50/80 scale-105' : 'text-slate-400 hover:text-slate-700' }}">
                <i class="fa-solid fa-book-open text-base mb-0.5"></i>
                <span class="text-[10px] tracking-tight">LMS</span>
            </a>

            <!-- Tab 4: Absensi -->
            <a href="{{ route('mobile.absensi.index') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.absensi.*') ? 'text-indigo-600 font-black bg-indigo-50/80 scale-105' : 'text-slate-400 hover:text-slate-700' }}">
                <i class="fa-solid fa-fingerprint text-base mb-0.5"></i>
                <span class="text-[10px] tracking-tight">Absensi</span>
            </a>

            <!-- Tab 5: Profile -->
            <a href="{{ route('mobile.profile') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition duration-200 {{ request()->routeIs('mobile.profile') ? 'text-indigo-600 font-black bg-indigo-50/80 scale-105' : 'text-slate-400 hover:text-slate-700' }}">
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
