<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $portalTitle ?? 'PembdaHUB')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>

    <!-- PWA Manifest & App Shell Meta Tags -->
    <link rel="manifest" href="/manifest.json?v=6">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PembdaHUB">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png?v=6">

    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/sw.js');
        });
      }
    </script>

    @php
        // ═══════════════════════════════════════════
        // THEME CONFIGURATION — set by each layout
        // ═══════════════════════════════════════════
        $theme       = $theme ?? 'indigo';
        $sidebarId   = $sidebarId ?? 'app-sidebar';
        $storageKey  = $storageKey ?? 'sidebar_collapsed';
        $portalName  = $portalName ?? 'PembdaHUB';
        $portalSub   = $portalSub ?? '';
        $portalEmoji = $portalEmoji ?? '🎓';
        $portalIcon  = $portalIcon ?? 'fas fa-graduation-cap';

        // Color mappings
        $themes = [
            'indigo' => [
                'header'      => 'from-indigo-700 via-indigo-600 to-purple-600',
                'active_bg'   => 'bg-gradient-to-r from-indigo-50 to-purple-50',
                'active_text' => 'text-indigo-700',
                'icon_grad'   => 'from-indigo-500 to-purple-600',
                'accent'      => 'indigo',
            ],
            'emerald' => [
                'header'      => 'from-emerald-600 via-green-600 to-teal-600',
                'active_bg'   => 'bg-gradient-to-r from-emerald-50 to-green-50',
                'active_text' => 'text-emerald-700',
                'icon_grad'   => 'from-emerald-500 to-teal-600',
                'accent'      => 'emerald',
            ],
            'blue' => [
                'header'      => 'from-blue-600 via-cyan-600 to-blue-700',
                'active_bg'   => 'bg-gradient-to-r from-blue-50 to-cyan-50',
                'active_text' => 'text-blue-700',
                'icon_grad'   => 'from-blue-500 to-cyan-600',
                'accent'      => 'blue',
            ],
            'amber' => [
                'header'      => 'from-amber-600 via-orange-600 to-amber-700',
                'active_bg'   => 'bg-gradient-to-r from-amber-50 to-orange-50',
                'active_text' => 'text-amber-700',
                'icon_grad'   => 'from-amber-500 to-orange-600',
                'accent'      => 'amber',
            ],
            'rose' => [
                'header'      => 'from-rose-600 via-pink-600 to-rose-700',
                'active_bg'   => 'bg-gradient-to-r from-rose-50 to-pink-50',
                'active_text' => 'text-rose-700',
                'icon_grad'   => 'from-rose-500 to-pink-600',
                'accent'      => 'rose',
            ],
            'violet' => [
                'header'      => 'from-violet-700 via-purple-700 to-violet-800',
                'active_bg'   => 'bg-gradient-to-r from-violet-50 to-purple-50',
                'active_text' => 'text-violet-700',
                'icon_grad'   => 'from-violet-500 to-purple-600',
                'accent'      => 'violet',
            ],
        ];
        $t = $themes[$theme] ?? $themes['indigo'];
    @endphp

    <style>
        #{{ $sidebarId }} {
            font-family: 'Plus Jakarta Sans', sans-serif;
            width: 272px;
            min-width: 272px;
            transition: left .3s ease, width .3s ease, min-width .3s ease, opacity .3s ease;
            overflow-y: auto;
            overflow-x: hidden;
            will-change: left, width;
        }
        #{{ $sidebarId }}.collapsed {
            width: 0; min-width: 0; opacity: 0; overflow: hidden;
        }
        
        /* ── Desktop & Laptop (640px and above) ── */
        @media (min-width: 640px) {
            #{{ $sidebarId }} {
                height: calc(100vh - 62px);
                position: sticky;
                top: 62px;
            }
        }
        #main-content { 
            transition: all .3s ease;
            min-width: 0;
            flex: 1;
        }

        /* ── Menu Group Toggle Headers ── */
        .menu-group-toggle {
            color: #64748b !important; /* text-slate-500 */
            font-weight: 700 !important;
            text-align: left !important;
            transition: color 0.15s ease;
        }
        .menu-group-toggle:hover {
            color: #1e293b !important; /* text-slate-800 */
        }

        /* ── Menu Items ── */
        .menu-item { position: relative; overflow: hidden; transition: all .2s ease; }
        .menu-item::before {
            content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 3px;
            background: linear-gradient(180deg, var(--accent-from, #4F46E5), var(--accent-to, #7C3AED));
            transform: scaleY(0); transition: transform .2s ease;
        }
        .menu-item:hover::before, .menu-item.active::before { transform: scaleY(1); }

        .menu-item:not(.active) {
            color: #475569 !important; /* text-slate-600 */
            font-weight: 500 !important;
        }
        .menu-item:not(.active):hover {
            color: #0f172a !important; /* text-slate-900 */
            background-color: #f8fafc !important; /* bg-slate-50 */
        }

        /* ── Group Collapse ── */
        .menu-group-body { overflow: hidden; transition: max-height .3s ease; }
        .menu-group-body.closed { max-height: 0 !important; }
        .menu-group-toggle .chevron { transition: transform .2s ease; }
        .menu-group-toggle.open .chevron { transform: rotate(90deg); }

        /* ── Mobile (Below 640px) ── */
        @media (max-width: 639px) {
            #{{ $sidebarId }} {
                position: fixed !important; left: -320px; top: 0 !important; bottom: 0 !important; z-index: 9999;
                width: 280px !important; min-width: 280px !important;
                height: 100% !important; max-height: 100vh;
                background: white;
                box-shadow: 4px 0 25px rgba(0,0,0,.1);
                transform: none !important;
            }
            #{{ $sidebarId }}.show-mobile { left: 0 !important; opacity: 1; }
            #{{ $sidebarId }}.collapsed { left: -320px; }
        }

        /* ── Hamburger Animation ── */
        .hamburger span { display: block; width: 20px; height: 2px; background: white; transition: all .3s ease; }
        .hamburger.is-active span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
        .hamburger.is-active span:nth-child(2) { opacity: 0; }
        .hamburger.is-active span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }

        /* Scrollbar */
        #{{ $sidebarId }}::-webkit-scrollbar { width: 4px; }
        #{{ $sidebarId }}::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
    <!-- KaTeX for rendering mathematical formulas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" onload="renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true}, {left: '$', right: '$', display: false}, {left: '\\(', right: '\\)', display: false}, {left: '\\[', right: '\\]', display: true}]});"></script>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    @if(!request()->has('embed'))
    <!-- Mobile Backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black/40 z-[9998] hidden lg:hidden"></div>

    <!-- ═══════ HEADER ═══════ -->
    <header class="bg-gradient-to-r {{ $t['header'] }} text-white shadow-lg fixed top-0 w-full z-40 print:hidden" style="background-color: #1e1b4b;">
        <div class="flex items-center justify-between px-4 lg:px-6 h-[62px]">
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" type="button" style="touch-action: manipulation;" class="hamburger flex flex-col justify-center items-center gap-[5px] p-2 rounded-lg hover:bg-white/10 transition is-active" aria-label="Toggle sidebar">
                    <span></span><span></span><span></span>
                </button>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center p-1">
                        <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Pembda" class="w-full h-full object-contain">
                    </span>
                    <div>
                        <h1 class="text-lg font-bold leading-tight">{!! str_replace('HUB', '<span class="text-red-400">HUB</span>', $portalName) !!}</h1>
                        @if($portalSub)
                            <p class="text-[10px] text-white/70 leading-none">{{ $portalSub }}</p>
                        @endif
                    </div>
                </div>
            <div class="flex items-center gap-3">
                @if(auth()->user())
                    @php
                        $currentRole = session('active_role') ?? auth()->user()->role;
                    @endphp
                    
                    @if(auth()->user()->isOwnerOrSuperAdmin())
                        @php
                            $roleMeta = [
                                'superadmin' => [
                                    'title' => 'Super Admin',
                                    'desc' => 'Administrator Utama',
                                    'icon' => 'fas fa-chess-king',
                                    'color' => 'bg-amber-400 text-black',
                                ],
                                'ketua_yayasan' => [
                                    'title' => 'Ketua Yayasan',
                                    'desc' => 'Pengawasan & Keuangan',
                                    'icon' => 'fas fa-landmark',
                                    'color' => 'bg-purple-600 text-white',
                                ],
                                'guru' => [
                                    'title' => 'Guru Pengampu',
                                    'desc' => 'Portal Guru, LMS & Nilai',
                                    'icon' => 'fas fa-chalkboard-teacher',
                                    'color' => 'bg-emerald-600 text-white',
                                ],
                                'orang_tua' => [
                                    'title' => 'Orang Tua / Wali',
                                    'desc' => (auth()->user()->username === 'yulzega' || auth()->user()->email === 'yulzega@gmail.com') ? 'Wali dari Celeste Nibenia Ogaena' : 'Monitoring Akademik Siswa',
                                    'icon' => 'fas fa-user-friends',
                                    'color' => 'bg-pink-600 text-white',
                                ],
                            ];
                            $activeMeta = $roleMeta[$currentRole] ?? [
                                'title' => ucwords(str_replace('_', ' ', $currentRole)),
                                'desc' => 'Mode Aktif',
                                'icon' => 'fas fa-user-circle',
                                'color' => 'bg-slate-800 text-white',
                            ];
                        @endphp

                        {{-- Elegant Role Switcher Dropdown --}}
                        <div class="relative" x-data="{ openRoleSwitch: false }">
                            <button @click="openRoleSwitch = !openRoleSwitch" @click.away="openRoleSwitch = false"
                                    class="flex items-center gap-2 bg-black/40 hover:bg-black/60 px-3 py-1.5 rounded-xl border-2 border-black text-white text-xs font-black transition shadow-sm active:scale-95">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                <i class="{{ $activeMeta['icon'] }} text-amber-300"></i>
                                <span class="hidden sm:inline font-black">{{ $activeMeta['title'] }}</span>
                                <span class="sm:hidden font-black">{{ Str::limit($activeMeta['title'], 8) }}</span>
                                <i class="fas fa-chevron-down text-[9px] text-white/70 ml-0.5 transition-transform" :class="{ 'rotate-180': openRoleSwitch }"></i>
                            </button>

                            <div x-show="openRoleSwitch" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95" 
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100" 
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-72 bg-slate-900 border-2 border-black rounded-2xl shadow-2xl z-50 overflow-hidden text-white"
                                 style="display: none;">
                                
                                <div class="px-4 py-3 bg-gradient-to-r from-slate-950 to-slate-900 border-b border-slate-800">
                                    <div class="text-[10px] font-black uppercase tracking-wider text-amber-300 flex items-center justify-between">
                                        <span>Ganti Mode Peran (Role Switcher)</span>
                                        <i class="fas fa-crown text-amber-400"></i>
                                    </div>
                                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">Pilih tampilan dashboard yang ingin diakses:</p>
                                </div>

                                <div class="p-2 space-y-1">
                                    {{-- 1. Super Admin --}}
                                    <form action="{{ route('switch-role') }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        <input type="hidden" name="role" value="superadmin">
                                        <button type="submit" 
                                                class="w-full text-left px-3 py-2.5 rounded-xl text-xs font-black transition flex items-center justify-between group
                                                       {{ $currentRole === 'superadmin' ? 'bg-amber-400 text-black shadow-sm' : 'hover:bg-slate-800 text-slate-200' }}">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $currentRole === 'superadmin' ? 'bg-black text-amber-400' : 'bg-slate-800 text-amber-300' }} shrink-0">
                                                    <i class="fas fa-chess-king text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="leading-tight font-black">Super Admin</div>
                                                    <div class="text-[10px] {{ $currentRole === 'superadmin' ? 'text-black/70' : 'text-slate-400' }} font-normal">Administrator Utama</div>
                                                </div>
                                            </div>
                                            @if($currentRole === 'superadmin')
                                                <i class="fas fa-check-circle text-black text-sm"></i>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- 2. Ketua Yayasan --}}
                                    <form action="{{ route('switch-role') }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        <input type="hidden" name="role" value="ketua_yayasan">
                                        <button type="submit" 
                                                class="w-full text-left px-3 py-2.5 rounded-xl text-xs font-black transition flex items-center justify-between group
                                                       {{ $currentRole === 'ketua_yayasan' ? 'bg-purple-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-200' }}">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $currentRole === 'ketua_yayasan' ? 'bg-black text-purple-300' : 'bg-slate-800 text-purple-400' }} shrink-0">
                                                    <i class="fas fa-landmark text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="leading-tight font-black">Ketua Yayasan</div>
                                                    <div class="text-[10px] {{ $currentRole === 'ketua_yayasan' ? 'text-purple-200' : 'text-slate-400' }} font-normal">Pengawasan & Keuangan</div>
                                                </div>
                                            </div>
                                            @if($currentRole === 'ketua_yayasan')
                                                <i class="fas fa-check-circle text-white text-sm"></i>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- 3. Guru Pengampu --}}
                                    <form action="{{ route('switch-role') }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        <input type="hidden" name="role" value="guru">
                                        <button type="submit" 
                                                class="w-full text-left px-3 py-2.5 rounded-xl text-xs font-black transition flex items-center justify-between group
                                                       {{ $currentRole === 'guru' ? 'bg-emerald-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-200' }}">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $currentRole === 'guru' ? 'bg-black text-emerald-300' : 'bg-slate-800 text-emerald-400' }} shrink-0">
                                                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="leading-tight font-black">Guru Pengampu</div>
                                                    <div class="text-[10px] {{ $currentRole === 'guru' ? 'text-emerald-200' : 'text-slate-400' }} font-normal">Portal Guru, LMS & Nilai</div>
                                                </div>
                                            </div>
                                            @if($currentRole === 'guru')
                                                <i class="fas fa-check-circle text-white text-sm"></i>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- 4. Orang Tua / Wali --}}
                                    <form action="{{ route('switch-role') }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        <input type="hidden" name="role" value="orang_tua">
                                        <button type="submit" 
                                                class="w-full text-left px-3 py-2.5 rounded-xl text-xs font-black transition flex items-center justify-between group
                                                       {{ $currentRole === 'orang_tua' ? 'bg-pink-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-200' }}">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $currentRole === 'orang_tua' ? 'bg-black text-pink-300' : 'bg-slate-800 text-pink-400' }} shrink-0">
                                                    <i class="fas fa-user-friends text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="leading-tight font-black">Orang Tua / Wali</div>
                                                    <div class="text-[10px] {{ $currentRole === 'orang_tua' ? 'text-pink-200' : 'text-slate-400' }} font-normal">{{ (auth()->user()->username === 'yulzega' || auth()->user()->email === 'yulzega@gmail.com') ? 'Wali: Celeste Nibenia Ogaena' : 'Monitoring Akademik Siswa' }}</div>
                                                </div>
                                            </div>
                                            @if($currentRole === 'orang_tua')
                                                <i class="fas fa-check-circle text-white text-sm"></i>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @elseif(auth()->user()->isKepalaSekolah())
                        <form action="{{ route('switch-role') }}" method="POST" class="inline">
                            @csrf
                            @if($currentRole === 'kepala_sekolah')
                                <input type="hidden" name="role" value="guru">
                                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 shadow border border-emerald-400/30">
                                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                                    <span class="hidden sm:inline">Masuk Mode Guru</span>
                                    <span class="sm:hidden">Mode Guru</span>
                                </button>
                            @else
                                <input type="hidden" name="role" value="kepala_sekolah">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 shadow border border-indigo-500/30">
                                    <i class="fas fa-user-shield text-xs"></i>
                                    <span class="hidden sm:inline">Masuk Mode Kepsek</span>
                                    <span class="sm:hidden">Mode Kepsek</span>
                                </button>
                            @endif
                        </form>
                    @elseif(auth()->user()->isAdminSekolah() && (auth()->user()->teacher || auth()->user()->employee || auth()->user()->hasRole('guru') || auth()->user()->hasRole('pegawai') || auth()->user()->isSecondaryAdminSekolah()))
                        <form action="{{ route('switch-role') }}" method="POST" class="inline">
                            @csrf
                            @if($currentRole === 'admin_sekolah')
                                <input type="hidden" name="role" value="{{ auth()->user()->teacher ? 'guru' : 'pegawai' }}">
                                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 shadow border border-emerald-400/30">
                                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                                    <span class="hidden sm:inline">Masuk Mode {{ auth()->user()->teacher ? 'Guru' : 'Pegawai' }}</span>
                                    <span class="sm:hidden">Mode {{ auth()->user()->teacher ? 'Guru' : 'Pegawai' }}</span>
                                </button>
                            @else
                                <input type="hidden" name="role" value="admin_sekolah">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 shadow border border-indigo-500/30">
                                    <i class="fas fa-user-shield text-xs"></i>
                                    <span class="hidden sm:inline">Masuk Mode Admin Sekolah</span>
                                    <span class="sm:hidden">Mode Admin</span>
                                </button>
                            @endif
                        </form>
                    @endif

                    {{-- School Switcher untuk guru yang mengajar di beberapa unit --}}
                    @if(auth()->user()->hasMultiSchoolAccess() && in_array($currentRole, ['guru', 'kepala_sekolah', 'ketua_yayasan']))
                        @php
                            $availableSchools = auth()->user()->getAvailableSchools();
                            $activeSchoolId = session('active_school_id', auth()->user()->school_id);
                            $activeSchool = $availableSchools->firstWhere('id', $activeSchoolId) ?? $availableSchools->first();
                        @endphp
                        @if($availableSchools->count() > 1)
                            <div class="relative" x-data="{ openSchool: false }">
                                <button @click="openSchool = !openSchool" @click.away="openSchool = false"
                                    class="hidden sm:flex items-center gap-1.5 bg-white/15 hover:bg-white/25 px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-white transition border border-white/20 shadow-sm">
                                    <i class="fas fa-school text-amber-300"></i>
                                    <span class="max-w-[120px] truncate">{{ $activeSchool->name ?? 'Pilih Unit' }}</span>
                                    <i class="fas fa-chevron-down text-[9px] ml-0.5 transition-transform" :class="{ 'rotate-180': openSchool }"></i>
                                </button>
                                <div x-show="openSchool" x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-64 bg-slate-800 border border-white/20 rounded-xl shadow-xl z-50 overflow-hidden"
                                     style="display: none;">
                                    <div class="px-3 py-2 border-b border-white/10">
                                        <p class="text-[10px] uppercase tracking-wider text-white/50 font-bold">Beralih Unit Sekolah</p>
                                    </div>
                                    @foreach($availableSchools as $school)
                                        <form action="{{ route('switch-school') }}" method="POST" class="m-0 p-0">
                                            @csrf
                                            <input type="hidden" name="school_id" value="{{ $school->id }}">
                                            <button type="submit"
                                                class="w-full text-left px-3 py-2.5 text-xs hover:bg-white/10 transition flex items-center gap-2.5 {{ $school->id == $activeSchoolId ? 'bg-indigo-600/30 text-white font-bold' : 'text-white/80' }}">
                                                @if($school->id == $activeSchoolId)
                                                    <i class="fas fa-check-circle text-emerald-400 text-sm"></i>
                                                @else
                                                    <i class="far fa-circle text-white/30 text-sm"></i>
                                                @endif
                                                <span class="truncate">{{ $school->name }}</span>
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="hidden md:flex items-center gap-2 bg-black/25 px-3 py-1.5 rounded-xl text-sm border border-white/10 shadow-xs">
                        <img src="{{ auth()->user()->photo_url }}" class="w-7 h-7 rounded-full object-cover border-2 border-amber-400 flex-shrink-0" alt="Avatar">
                        <span class="font-black text-amber-300 text-xs tracking-tight">{{ auth()->user()->name ?? 'User' }}</span>
                        @if(auth()->user()->school)
                            <span class="text-white/50">·</span>
                            <span class="text-white/70 text-xs">{{ auth()->user()->school->name }}</span>
                        @endif
                    </div>
                    <a href="{{ route('profile.settings') }}" class="bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                        <i class="fas fa-user-cog text-xs"></i>
                        <span class="hidden sm:inline">Profil Akun</span>
                    </a>

                    <a href="{{ route('notifications.index') }}" class="relative bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5" x-data="{ unread: 0 }" x-init="fetch('{{ route('notifications.unread-count') }}').then(r=>r.json()).then(d=>unread=d.count)">
                        <i class="fas fa-bell text-xs"></i>
                        <template x-if="unread > 0">
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center" x-text="unread"></span>
                        </template>
                        <span class="hidden sm:inline">Notif</span>
                    </a>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-white/10 hover:bg-red-500 px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                            <i class="fas fa-sign-out-alt text-xs"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk / Login</span>
                    </a>
                @endif
            </div>
        </div>
    </header>
    @endif

    <!-- ═══════ BODY ═══════ -->
    <div class="flex flex-1" style="padding-top: 62px;">
        <!-- ═══════ SIDEBAR ═══════ -->
        @if(!request()->has('embed'))
        <aside id="{{ $sidebarId }}" class="bg-white border-r border-gray-200 flex-shrink-0 collapsed print:hidden">
            <div class="p-4 space-y-1">
                <!-- Mobile Close Button -->
                <button type="button" class="md:hidden w-full flex items-center justify-between px-3 py-2 bg-gray-100 rounded-xl text-gray-600 mb-4 font-bold" onclick="document.getElementById('sidebar-toggle').click()">
                    <span>Tutup Menu</span>
                    <i class="fas fa-times"></i>
                </button>
                @yield('sidebar-menu')

                <!-- Spacer -->
                <div class="h-6"></div>
            </div>
        </aside>
        <script>
            (function() {
                const sidebarId  = '{{ $sidebarId }}';
                const storageKey = '{{ $storageKey }}';
                if (window.innerWidth >= 640 && localStorage.getItem(storageKey) === 'false') {
                    const btn = document.getElementById('sidebar-toggle');
                    const sb = document.getElementById(sidebarId);
                    if (btn && sb) {
                        btn.classList.remove('is-active');
                        sb.classList.remove('collapsed');
                    }
                }
            })();
        </script>
        @endif

        <!-- ═══════ MAIN CONTENT ═══════ -->
        <main id="main-content" class="flex-1 min-w-0 print:p-0 print:m-0 print:block {{ request()->has('embed') ? 'p-0 bg-slate-900' : 'p-4 lg:p-6' }}">
            @if(!request()->has('embed'))
                @include('partials.flash-messages')
            @endif
            @yield('content')
        </main>
    </div>

    <!-- ═══════ FOOTER ═══════ -->
    @if(!request()->has('embed'))
    <footer class="bg-gray-800 text-gray-400 text-center py-3 text-xs print:hidden">
        &copy; {{ date('Y') }} Pembda<span class="text-red-400">HUB</span> &mdash; Yayasan Perguruan PEMBDA Nias
    </footer>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarId  = '{{ $sidebarId }}';
        const storageKey = '{{ $storageKey }}';
        const toggle  = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById(sidebarId);
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!toggle || !sidebar) return;

        const isMobile = () => window.innerWidth < 640;

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            if (isMobile()) {
                sidebar.classList.toggle('show-mobile');
                backdrop.classList.toggle('hidden');
                // Force Repaint hack for iOS
                void sidebar.offsetHeight;
            } else {
                sidebar.classList.toggle('collapsed');
                toggle.classList.toggle('is-active');
                localStorage.setItem(storageKey, sidebar.classList.contains('collapsed'));
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                }, 150);
            }
        });

        if (backdrop) {
            backdrop.addEventListener('click', function () {
                sidebar.classList.remove('show-mobile');
                backdrop.classList.add('hidden');
                document.body.style.overflow = '';
            });
        }

        // Restore desktop state
        if (!isMobile()) {
            if (localStorage.getItem(storageKey) === 'false') {
                sidebar.classList.remove('collapsed');
                toggle.classList.remove('is-active');
            } else {
                sidebar.classList.add('collapsed');
                toggle.classList.add('is-active');
            }
        }

        // Resize handler
        let rt;
        window.addEventListener('resize', function () {
            clearTimeout(rt);
            rt = setTimeout(function () {
                if (!isMobile()) {
                    sidebar.classList.remove('show-mobile');
                    backdrop.classList.add('hidden');
                    document.body.style.overflow = '';
                } else {
                    sidebar.classList.remove('collapsed');
                    toggle.classList.remove('is-active');
                }
            }, 100);
        });

        // Restore group states
        document.querySelectorAll('[data-menu-group]').forEach(function (g) {
            const key = storageKey + '_grp_' + g.dataset.menuGroup;
            const state = localStorage.getItem(key);
            const btn = g.querySelector('.menu-group-toggle');
            const body = g.querySelector('.menu-group-body');
            if (btn && body) {
                if (state === 'open') {
                    btn.classList.add('open');
                    body.classList.remove('closed');
                } else {
                    btn.classList.remove('open');
                    body.classList.add('closed');
                }
            }
        });
    });

    function toggleGroup(btn) {
        const body = btn.nextElementSibling;
        const group = btn.closest('[data-menu-group]');
        const storageKey = '{{ $storageKey }}';
        const key = storageKey + '_grp_' + (group ? group.dataset.menuGroup : '');
        btn.classList.toggle('open');
        body.classList.toggle('closed');
        localStorage.setItem(key, body.classList.contains('closed') ? 'closed' : 'open');
    }
    </script>
    @include('partials.ux-scripts')
    <!-- Mathematical & Science Symbols Toolbar & Formula Palette Modal -->
    @include('partials.math-toolbar-modal')

    <!-- Form Upload Guard & Session Keepalive -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // ═════════════════════════════════════════════════════════════════
        // FORM UPLOAD GUARD & SESSION KEEPALIVE (Mencegah Error 419)
        // ═════════════════════════════════════════════════════════════════
        
        // 1. Session Keep-Alive: Ping server setiap 15 menit agar sesi tidak habis saat mengetik panjang
        setInterval(() => {
            fetch(window.location.href, { method: 'HEAD', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .catch(() => {});
        }, 15 * 60 * 1000);

        // 2. Max File Upload Guard: Cegah submit jika total ukuran file melampaui limit server (~50MB)
        const MAX_UPLOAD_SIZE = 50 * 1024 * 1024; // 50 MB
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form) return;

            const fileInputs = form.querySelectorAll('input[type="file"]');
            let totalBytes = 0;
            let oversizedFiles = [];

            fileInputs.forEach(input => {
                if (input.files) {
                    for (let i = 0; i < input.files.length; i++) {
                        const file = input.files[i];
                        totalBytes += file.size;
                        if (file.size > MAX_UPLOAD_SIZE) {
                            oversizedFiles.push(file.name + ' (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB)');
                        }
                    }
                }
            });

            if (totalBytes > MAX_UPLOAD_SIZE || oversizedFiles.length > 0) {
                e.preventDefault();
                alert(
                    '⚠️ PERINGATAN UKURAN FILE TERLALU BESAR!\n\n' +
                    'Total file yang diunggah melampaui batas aman (Maksimum 50 MB).\n' +
                    (oversizedFiles.length > 0 ? 'File bermasalah:\n- ' + oversizedFiles.join('\n- ') + '\n\n' : '') +
                    'Mengunggah file terlalu besar dapat menyebabkan error 419 (Sesi Berakhir).\n' +
                    'Silakan kecilkan ukuran file, atau gunakan link Google Drive / Youtube.'
                );
            }
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
