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
    <style>[x-cloak] { display: none !important; }</style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    spacing: {
                        '4.5': '1.125rem',
                        '13': '3.25rem',
                        '15': '3.75rem',
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
                <span class="text-[10px] font-extrabold text-blue-600 tracking-wider uppercase">EDUCATION PORTAL</span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @auth
            @php 
                $navUser = auth()->user();
                $currentRole = session('active_role', $navUser->role); 
                $currentDuty = session('active_duty', 'pengampu');
                $userMobileRoles = $navUser->getAvailableMobileRoles();
                $userDuties = $navUser->getAvailableDuties();
                $showSwitcher = count($userMobileRoles) > 1 || count($userDuties) > 1 || $navUser->isOwnerOrSuperAdmin();
            @endphp

            <!-- Role & Jabatan Switcher Button -->
            @if($showSwitcher)
                <button @click="showRoleModal = true" 
                        title="Beralih Peran & Jabatan"
                        class="px-3 py-1.5 text-xs font-black bg-white text-blue-600 border-2 border-blue-200 rounded-2xl hover:bg-blue-50 flex items-center gap-1.5 transition shadow-sm active:scale-95">
                    <i class="fa-solid fa-repeat text-blue-600 text-xs"></i>
                    <span class="capitalize truncate max-w-[90px]">{{ str_replace('_', ' ', $currentRole) }}</span>
                </button>
            @endif

            <!-- Quick Logout Header Button -->
            <form action="{{ route('mobile.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        title="Keluar / Logout"
                        onclick="return confirm('Apakah Anda yakin ingin keluar dari PembdaHUB Mobile?')"
                        class="w-9 h-9 rounded-2xl bg-white text-rose-600 border-2 border-rose-200 shadow-sm flex items-center justify-center hover:bg-rose-600 hover:text-white transition active:scale-95">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i>
                </button>
            </form>

            <!-- PWA Global Install Banner / Button -->
            <div x-data="{ deferredPrompt: null, canInstall: false, showBanner: false }" 
                 x-init="window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; canInstall = true; showBanner = true; });">
                <button x-show="canInstall" 
                        @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then(() => { canInstall = false; showBanner = false; });"
                        class="px-3 py-1.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-black text-xs shadow-md flex items-center gap-1.5 transition active:scale-95">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Install HP</span>
                </button>

                <!-- Floating Bottom Pop-up Banner for 1-Click Install -->
                <div x-show="showBanner" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-12"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-12"
                     class="fixed bottom-20 left-4 right-4 z-50 p-4 bg-slate-900 text-white rounded-3xl shadow-2xl border-2 border-blue-500/50 flex items-center justify-between gap-3 backdrop-blur-lg"
                     style="display: none;">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-white p-1 shrink-0 border border-white/50">
                            <img src="{{ asset('images/app-logo.png?v=6') }}" alt="PembdaHUB" class="w-full h-full object-contain rounded-xl">
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-white truncate">Install PembdaHUB Mobile 🚀</h4>
                            <p class="text-[10px] text-slate-300 font-bold truncate">Pasang ke Layar Utama HP Anda untuk 1-klik akses!</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then(() => { canInstall = false; showBanner = false; });" 
                                class="px-3.5 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-[11px] font-black rounded-xl shadow-md active:scale-95">
                            Install
                        </button>
                        <button @click="showBanner = false" class="p-2 text-slate-400 hover:text-white">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endauth
        </div>

        <!-- Role & Jabatan Switcher Modal -->
        @auth
        <div x-show="showRoleModal" 
             x-data="{ modalTab: '{{ count($userDuties) > 1 && in_array($currentRole, ['guru', 'pegawai']) ? 'duty' : 'role' }}' }"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             style="display: none;">
            <div class="bg-[#f4f7fc] rounded-3xl p-5 w-full max-w-sm shadow-2xl border-4 border-white space-y-3.5 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b-2 border-slate-200/80 pb-2.5">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-repeat text-blue-600"></i> Beralih Peran & Jabatan
                    </h3>
                    <button @click="showRoleModal = false" class="w-7 h-7 rounded-xl bg-slate-200 text-slate-600 hover:bg-slate-300 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Tab Switcher Modal (Roles vs Jabatan) -->
                @if(count($userDuties) > 0 && count($userMobileRoles) > 1)
                <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-200/80 rounded-2xl border border-slate-300">
                    <button type="button" @click="modalTab = 'role'"
                            :class="modalTab === 'role' ? 'bg-blue-600 text-white font-black shadow-sm' : 'text-slate-600 font-extrabold'"
                            class="py-1.5 text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                        <span>👤 Peran Akun</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[9px] {{ $currentRole ? 'bg-white/20 text-white' : '' }}">{{ count($userMobileRoles) }}</span>
                    </button>
                    <button type="button" @click="modalTab = 'duty'"
                            :class="modalTab === 'duty' ? 'bg-purple-600 text-white font-black shadow-sm' : 'text-slate-600 font-extrabold'"
                            class="py-1.5 text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                        <span>💼 Jabatan / Tugas</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[9px] {{ count($userDuties) > 0 ? 'bg-white/20 text-white' : '' }}">{{ count($userDuties) }}</span>
                    </button>
                </div>
                @endif

                <!-- TAB 1: PERAN AKUN (ROLES) -->
                <div x-show="modalTab === 'role'" class="space-y-2">
                    <p class="text-[11px] text-slate-600 font-bold">Pilih hak akses peran utama Anda:</p>
                    <form action="{{ route('mobile.switch-role') }}" method="POST" class="space-y-2">
                        @csrf
                        @foreach($userMobileRoles as $r)
                            @php $isCurrent = ($currentRole === $r['key']); @endphp
                            <button type="submit" name="role" value="{{ $r['key'] }}" 
                                    class="w-full text-left p-3 rounded-2xl bg-white border-2 {{ $isCurrent ? 'border-blue-500 bg-blue-50/50 shadow-sm' : 'border-slate-200 hover:border-blue-300' }} transition flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-lg shrink-0">
                                        {{ $r['icon'] }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-black text-slate-900 leading-tight truncate">{{ $r['label'] }}</div>
                                        <div class="text-[10px] text-slate-500 font-semibold truncate">{{ $r['subtitle'] }}</div>
                                    </div>
                                </div>
                                @if($isCurrent)
                                    <i class="fa-solid fa-circle-check text-blue-600 text-base shrink-0 ml-2"></i>
                                @endif
                            </button>
                        @endforeach
                    </form>
                </div>

                <!-- TAB 2: JABATAN & TUGAS STRUKTURAL (DUTIES) -->
                @if(count($userDuties) > 0)
                <div x-show="modalTab === 'duty'" class="space-y-2" style="{{ count($userMobileRoles) > 1 ? 'display: none;' : '' }}">
                    <p class="text-[11px] text-slate-600 font-bold">Pilih fokus tugas & jabatan aktif Anda:</p>
                    <form action="{{ route('mobile.switch-duty') }}" method="POST" class="space-y-2">
                        @csrf
                        @foreach($userDuties as $d)
                            @php $isCurrentDuty = ($currentDuty === $d['key']); @endphp
                            <button type="submit" name="duty" value="{{ $d['key'] }}" 
                                    class="w-full text-left p-3 rounded-2xl bg-white border-2 {{ $isCurrentDuty ? 'border-purple-500 bg-purple-50/50 shadow-sm' : 'border-slate-200 hover:border-purple-300' }} transition flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-purple-100/70 border border-purple-200 flex items-center justify-center text-lg shrink-0">
                                        {{ $d['icon'] }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-black text-slate-900 leading-tight truncate">{{ $d['label'] }}</div>
                                        <div class="text-[10px] text-slate-500 font-semibold truncate">{{ $d['description'] }}</div>
                                    </div>
                                </div>
                                @if($isCurrentDuty)
                                    <i class="fa-solid fa-circle-check text-purple-600 text-base shrink-0 ml-2"></i>
                                @endif
                            </button>
                        @endforeach
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endauth
    </header>
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
