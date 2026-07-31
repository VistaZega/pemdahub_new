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

    <!-- PWA Manifest & App Shell Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PembdaHUB">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

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
        
        /* ── Desktop ── */
        @media (min-width: 1024px) {
            #{{ $sidebarId }} {
                height: calc(100vh - 62px);
                position: sticky;
                top: 62px;
            }
        }
        #main-content { transition: all .3s ease; }

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

        /* ── Mobile ── */
        @media (max-width: 1023px) {
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
    <header class="bg-gradient-to-r {{ $t['header'] }} text-white shadow-lg fixed top-0 w-full z-40">
        <div class="flex items-center justify-between px-4 lg:px-6 h-[62px]">
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" type="button" style="touch-action: manipulation;" class="hamburger flex flex-col justify-center items-center gap-[5px] p-2 rounded-lg hover:bg-white/10 transition is-active" aria-label="Toggle sidebar">
                    <span></span><span></span><span></span>
                </button>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="{{ $portalIcon }} text-sm"></i>
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
                    
                    @if(auth()->user()->isSuperAdmin())
                        <div class="hidden sm:flex items-center gap-1.5 bg-black/20 p-1 rounded-xl border border-white/10">
                            @if($currentRole !== 'superadmin')
                                <form action="{{ route('switch-role') }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <input type="hidden" name="role" value="superadmin">
                                    <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1.5 shadow-sm border border-gray-600 tooltip" title="Masuk Mode Super Admin">
                                        <i class="fas fa-chess-king text-gray-300"></i>
                                        <span>Super Admin</span>
                                    </button>
                                </form>
                            @endif
                            @if($currentRole !== 'ketua_yayasan')
                                <form action="{{ route('switch-role') }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <input type="hidden" name="role" value="ketua_yayasan">
                                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1.5 shadow-sm border border-purple-500/50 tooltip" title="Masuk Mode Yayasan">
                                        <i class="fas fa-building text-purple-200"></i>
                                        <span>Yayasan</span>
                                    </button>
                                </form>
                            @endif
                            @if($currentRole !== 'guru')
                                <form action="{{ route('switch-role') }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <input type="hidden" name="role" value="guru">
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1.5 shadow-sm border border-emerald-400/50 tooltip" title="Masuk Mode Guru">
                                        <i class="fas fa-chalkboard-teacher text-emerald-100"></i>
                                        <span>Guru</span>
                                    </button>
                                </form>
                            @endif
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
                                    <span class="hidden sm:inline">Masuk Mode Admin</span>
                                    <span class="sm:hidden">Mode Admin</span>
                                </button>
                            @endif
                        </form>
                    @endif
                    <div class="hidden md:flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg text-sm">
                        <img src="{{ auth()->user()->photo_url }}" class="w-6 h-6 rounded-full object-cover border border-white/20 flex-shrink-0" alt="Avatar">
                        <span class="font-medium">{{ auth()->user()->name ?? 'User' }}</span>
                        @if(auth()->user()->school)
                            <span class="text-white/50">·</span>
                            <span class="text-white/70 text-xs">{{ auth()->user()->school->name }}</span>
                        @endif
                    </div>
                    <a href="{{ route('profile.settings') }}" class="bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                        <i class="fas fa-user-cog text-xs"></i>
                        <span class="hidden sm:inline">Profil Akun</span>
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
        <aside id="{{ $sidebarId }}" class="bg-white border-r border-gray-200 flex-shrink-0 collapsed">
            <div class="p-4 space-y-1">
                <!-- Mobile Close Button -->
                <button type="button" class="lg:hidden w-full flex items-center justify-between px-3 py-2 bg-gray-100 rounded-xl text-gray-600 mb-4 font-bold" onclick="document.getElementById('sidebar-toggle').click()">
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
                if (window.innerWidth >= 1024 && localStorage.getItem(storageKey) === 'false') {
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
        <main id="main-content" class="flex-1 min-w-0 {{ request()->has('embed') ? 'p-0 bg-slate-900' : 'p-4 lg:p-6' }}">
            @if(!request()->has('embed'))
                @include('partials.flash-messages')
            @endif
            @yield('content')
        </main>
    </div>

    <!-- ═══════ FOOTER ═══════ -->
    @if(!request()->has('embed'))
    <footer class="bg-gray-800 text-gray-400 text-center py-3 text-xs">
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

        const isMobile = () => window.innerWidth < 1024;

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
    <!-- Mathematical Symbols Toolbar Helper -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Function to insert text at cursor position
        window.insertAtCursor = function(myField, myValue) {
            if (document.selection) {
                myField.focus();
                sel = document.selection.createRange();
                sel.text = myValue;
            } else if (myField.selectionStart || myField.selectionStart == '0') {
                var startPos = myField.selectionStart;
                var endPos = myField.selectionEnd;
                myField.value = myField.value.substring(0, startPos)
                    + myValue
                    + myField.value.substring(endPos, myField.value.length);
                myField.selectionStart = startPos + myValue.length;
                myField.selectionEnd = startPos + myValue.length;
            } else {
                myField.value += myValue;
            }
            myField.focus();
            // Trigger input event for Alpine.js / x-model binding
            myField.dispatchEvent(new Event('input'));
        };

        // Auto-generate toolbars for math-support fields
        function initMathToolbars() {
            const fields = document.querySelectorAll('.math-support:not([data-math-initialized])');
            fields.forEach((field, i) => {
                field.setAttribute('data-math-initialized', 'true');
                if (!field.id) {
                    field.id = 'math_field_' + Math.random().toString(36).substr(2, 9);
                }
                
                const toolbar = document.createElement('div');
                toolbar.className = 'flex flex-wrap gap-1.5 mb-1.5 p-1.5 bg-slate-50 border border-slate-200 rounded-t-xl items-center';
                
                const symbols = ['√', 'π', '±', '÷', '×', '²', '³', 'α', 'β', 'γ', 'θ', 'λ', 'Σ', 'Δ', '∞', '≠', '≤', '≥', '∫', '°'];
                
                const label = document.createElement('span');
                label.className = 'text-[9px] font-bold text-slate-400 px-1.5 uppercase tracking-wider select-none';
                label.textContent = 'Simbol Mat & Fisika:';
                toolbar.appendChild(label);
                
                symbols.forEach(sym => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'px-2 py-1 bg-white hover:bg-purple-50 hover:text-purple-600 border border-slate-200 rounded-lg text-xs font-bold transition-all shadow-sm focus:outline-none';
                    btn.textContent = sym;
                    btn.onclick = (e) => {
                        e.preventDefault();
                        window.insertAtCursor(field, sym);
                    };
                    toolbar.appendChild(btn);
                });
                
                field.parentNode.insertBefore(toolbar, field);
                field.classList.add('rounded-t-none');
            });
        }

        // Run initialization
        initMathToolbars();

        // Re-run when DOM changes (e.g. Alpine.js modal opening or dynamic questions)
        const observer = new MutationObserver(initMathToolbars);
        observer.observe(document.body, { childList: true, subtree: true });
    });
    </script>
    @stack('scripts')
</body>
</html>
