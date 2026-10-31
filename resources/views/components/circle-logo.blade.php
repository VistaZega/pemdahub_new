@props([
    'size' => 'md', // xs, sm, md, lg, xl, 2xl, full
    'variant' => 'horizontal', // emblem, horizontal, vertical, card, banner
    'withMotto' => true,
    'animated' => true,
    'class' => '',
])

@php
    $sizeMap = [
        'xs' => ['box' => 'w-7 h-7 rounded-xl', 'text' => 'text-sm', 'sub' => 'text-[8px]', 'gap' => 'gap-1.5', 'badge' => 'px-1.5 py-0.5 text-[9px]'],
        'sm' => ['box' => 'w-9 h-9 rounded-2xl', 'text' => 'text-base', 'sub' => 'text-[9px]', 'gap' => 'gap-2', 'badge' => 'px-2 py-0.5 text-[10px]'],
        'md' => ['box' => 'w-11 h-11 sm:w-12 sm:h-12 rounded-2xl', 'text' => 'text-lg sm:text-xl', 'sub' => 'text-[9.5px]', 'gap' => 'gap-2.5', 'badge' => 'px-2.5 py-0.5 text-[11px]'],
        'lg' => ['box' => 'w-16 h-16 rounded-3xl', 'text' => 'text-2xl', 'sub' => 'text-xs', 'gap' => 'gap-3', 'badge' => 'px-3 py-1 text-xs'],
        'xl' => ['box' => 'w-24 h-24 rounded-3xl', 'text' => 'text-3xl', 'sub' => 'text-sm', 'gap' => 'gap-4', 'badge' => 'px-3.5 py-1 text-sm'],
        '2xl' => ['box' => 'w-32 h-32 rounded-3xl', 'text' => 'text-4xl', 'sub' => 'text-base', 'gap' => 'gap-5', 'badge' => 'px-4 py-1.5 text-base'],
        'full' => ['box' => 'w-full max-w-[280px] aspect-square rounded-3xl', 'text' => 'text-3xl', 'sub' => 'text-xs', 'gap' => 'gap-3', 'badge' => 'px-3 py-1 text-xs'],
    ];

    $cfg = $sizeMap[$size] ?? $sizeMap['md'];
@endphp

@if($variant === 'emblem')
    {{-- ═══════════════════════════════════════════
         EMBLEM SAJA: ORB PASTEL HIDUP DENGAN ORBIT DUA ARAH & GLOSS 3D
    ═══════════════════════════════════════════ --}}
    <div class="relative {{ $cfg['box'] }} flex items-center justify-center shrink-0 group select-none transition-transform duration-300 group-hover:scale-105 {{ $class }}">
        <!-- Soft Pastel Pulsing Aura Blobs -->
        <div class="absolute -inset-1 rounded-[inherit] bg-gradient-to-tr from-pink-300/40 via-sky-300/35 to-amber-200/40 blur-sm opacity-70 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

        <!-- SVG Vector Animated Emblem (100% Pastel, No Black, No Grey) -->
        <svg class="w-full h-full relative z-10 drop-shadow-xs" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <!-- Pastel Clay Glass Core Background -->
                <radialGradient id="pcBg_{{ $size }}" cx="35%" cy="30%" r="70%">
                    <stop offset="0%" stop-color="#ffffff" />
                    <stop offset="50%" stop-color="#fdf2f8" />
                    <stop offset="85%" stop-color="#f0f9ff" />
                    <stop offset="100%" stop-color="#fefce8" />
                </radialGradient>

                <!-- Gradient Orbits Pastel -->
                <linearGradient id="pcRoseGrad_{{ $size }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f43f5e" />
                    <stop offset="100%" stop-color="#fb7185" />
                </linearGradient>
                <linearGradient id="pcSkyGrad_{{ $size }}" x1="100%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#0284c7" />
                    <stop offset="100%" stop-color="#38bdf8" />
                </linearGradient>
                <linearGradient id="pcAmberGrad_{{ $size }}" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#f59e0b" />
                    <stop offset="100%" stop-color="#fbbf24" />
                </linearGradient>
                <linearGradient id="pcCoreCGrad_{{ $size }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#e11d48" />
                    <stop offset="50%" stop-color="#9333ea" />
                    <stop offset="100%" stop-color="#0284c7" />
                </linearGradient>
            </defs>

            <!-- Pastel Glass Rounded Backdrop -->
            <rect x="4" y="4" width="192" height="192" rx="46" fill="url(#pcBg_{{ $size }})" stroke="#fbcfe8" stroke-width="2.5" />

            <!-- Faint Pastel Orbital Guides -->
            <circle cx="100" cy="100" r="76" stroke="#fbcfe8" stroke-width="1.8" stroke-dasharray="4 6" opacity="0.75" />
            <circle cx="100" cy="100" r="63" stroke="#bae6fd" stroke-width="1.4" stroke-dasharray="3 5" opacity="0.7" />
            <circle cx="100" cy="100" r="50" stroke="#fef08a" stroke-width="1.2" stroke-dasharray="2 4" opacity="0.7" />

            <!-- Orbit 1: Pastel Sky / Cyan (Clockwise) -->
            <g class="{{ $animated ? 'circle-spin-slow' : '' }}" style="transform-origin: 100px 100px;">
                <path d="M 100,24 A 76,76 0 0,1 174,115" fill="none" stroke="url(#pcSkyGrad_{{ $size }})" stroke-width="5.5" stroke-linecap="round" />
                <!-- Orbit Head Pearl Bead -->
                <circle cx="174" cy="115" r="5" fill="#0284c7" />
                <circle cx="172.5" cy="113.5" r="1.8" fill="#ffffff" opacity="0.9" />
            </g>

            <!-- Orbit 2: Pastel Rose / Pink (Counter-Clockwise) -->
            <g class="{{ $animated ? 'circle-spin-reverse' : '' }}" style="transform-origin: 100px 100px;">
                <path d="M 162,100 A 63,63 0 0,1 88,162" fill="none" stroke="url(#pcRoseGrad_{{ $size }})" stroke-width="5" stroke-linecap="round" />
                <!-- Orbit Head Pearl Bead -->
                <circle cx="88" cy="162" r="4.8" fill="#f43f5e" />
                <circle cx="86.8" cy="160.8" r="1.6" fill="#ffffff" opacity="0.9" />
            </g>

            <!-- Orbit 3: Pastel Amber Sun (Clockwise Fast) -->
            <g class="{{ $animated ? 'circle-spin-slow' : '' }}" style="transform-origin: 100px 100px; animation-duration: 9s;">
                <path d="M 64,64 A 50,50 0 0,1 136,64" fill="none" stroke="url(#pcAmberGrad_{{ $size }})" stroke-width="4.2" stroke-linecap="round" />
                <circle cx="136" cy="64" r="4.2" fill="#f59e0b" />
                <circle cx="134.8" cy="63" r="1.4" fill="#ffffff" opacity="0.9" />
            </g>

            <!-- Central Monogram "C" with 3D Gloss Highlight -->
            <g class="{{ $animated ? 'circle-pulse-soft' : '' }}" style="transform-origin: 100px 100px;">
                <!-- White Backer Glow -->
                <circle cx="100" cy="100" r="30" fill="#ffffff" opacity="0.95" />
                <!-- Rich Pastel Curve -->
                <path d="M 123,76 A 32,32 0 1,0 123,124" fill="none" stroke="url(#pcCoreCGrad_{{ $size }})" stroke-width="14" stroke-linecap="round" />
                <!-- 3D Glossy Light Reflection -->
                <path d="M 120,78 A 30,30 0 1,0 120,122" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" opacity="0.75" />
                <!-- Pastel Amber Pearl Terminal Bead -->
                <circle cx="128" cy="119" r="5.2" fill="#fbbf24" stroke="#ffffff" stroke-width="1.2" />
                <circle cx="126.5" cy="117.5" r="1.8" fill="#ffffff" opacity="0.95" />
            </g>

            <!-- Micro Sparkle Stars -->
            <circle cx="36" cy="42" r="2.2" fill="#f43f5e" opacity="0.8" />
            <circle cx="164" cy="48" r="2.5" fill="#38bdf8" opacity="0.85" />
            <circle cx="168" cy="154" r="2.2" fill="#fbbf24" opacity="0.85" />
            <circle cx="34" cy="148" r="2" fill="#34d399" opacity="0.8" />
        </svg>
    </div>

@elseif($variant === 'horizontal')
    {{-- ═══════════════════════════════════════════
         HORIZONTAL: EMBLEM + TYPOGRAPHY + MOTTO
    ═══════════════════════════════════════════ --}}
    <div class="flex items-center {{ $cfg['gap'] }} select-none {{ $class }}">
        <!-- Pastel Animated Emblem -->
        <x-circle-logo size="{{ $size }}" variant="emblem" :animated="$animated" />

        <!-- Brand Name & Motto -->
        <div class="flex flex-col min-w-0">
            <div class="flex items-center gap-1.5 leading-none">
                <span class="font-display font-black {{ $cfg['text'] }} text-pink-950 tracking-tight">Pembda</span>
                <div class="relative inline-flex items-center gap-1 sm:gap-1.5 {{ $cfg['badge'] }} rounded-xl border-2 border-pink-200/90 shadow-2xs overflow-hidden group-hover:border-pink-300 transition-colors"
                     style="background: linear-gradient(135deg, #fff1f2 0%, #fdf2f8 50%, #fef9c3 100%);">
                    <!-- Shimmer Stripe -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/80 to-transparent -translate-x-full circle-shimmer pointer-events-none"></div>
                    <!-- Live Pastel Ping Dot -->
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-pink-500"></span>
                    </span>
                    <!-- Flowing Gradient Text Pastel -->
                    <span class="font-display font-black tracking-widest uppercase circle-gradient-text">
                        CIRCLE
                    </span>
                </div>
            </div>

            @if($withMotto)
                <div class="hidden sm:flex items-center gap-1.5 {{ $cfg['sub'] }} font-black tracking-widest uppercase mt-1">
                    <span class="text-sky-600 font-extrabold hover:text-sky-500 transition-colors">CONNECT</span>
                    <span class="text-pink-300">•</span>
                    <span class="text-rose-600 font-extrabold hover:text-rose-500 transition-colors">CREATE</span>
                    <span class="text-pink-300">•</span>
                    <span class="text-emerald-600 font-extrabold hover:text-emerald-500 transition-colors">GROW</span>
                </div>
            @endif
        </div>
    </div>

@elseif($variant === 'vertical')
    {{-- ═══════════════════════════════════════════
         VERTICAL: EMBLEM CENTERED + LARGE TYPO + MOTTO
    ═══════════════════════════════════════════ --}}
    <div class="flex flex-col items-center text-center select-none {{ $class }}">
        <x-circle-logo size="{{ $size }}" variant="emblem" :animated="$animated" class="mb-3" />

        <div class="flex items-center justify-center gap-2 mb-1.5">
            <span class="font-display font-black text-2xl sm:text-3xl text-pink-950 tracking-tight">PEMBDA</span>
            <span class="font-display font-black text-2xl sm:text-3xl tracking-widest uppercase circle-gradient-text">
                CIRCLE
            </span>
        </div>

        @if($withMotto)
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-pink-50 border-2 border-pink-200 text-slate-800 text-[10px] sm:text-xs font-black tracking-[0.2em] uppercase shadow-2xs">
                <span class="text-sky-600">CONNECT</span>
                <span class="text-pink-300">•</span>
                <span class="text-rose-600">CREATE</span>
                <span class="text-pink-300">•</span>
                <span class="text-emerald-600">GROW</span>
            </div>
        @endif
    </div>

@elseif($variant === 'banner' || $variant === 'card')
    {{-- ═══════════════════════════════════════════
         BANNER / CARD: FLAGSHIP 100% PASTEL WITH 3 PILLARS
    ═══════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-3xl p-5 sm:p-7 border-2 border-pink-200/90 shadow-sm transition-all duration-300 hover:shadow-md {{ $class }}"
         style="background: linear-gradient(135deg, #fdf2f8 0%, #faedcd 35%, #e0f2fe 70%, #dcfce7 100%) !important;">
        <!-- Soft Pastel Floating Glows -->
        <div class="absolute -top-16 -right-16 w-56 h-56 bg-pink-200/60 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-56 h-56 bg-sky-200/60 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-6">
            <!-- Left: Animated Pastel Emblem & Manifesto -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-5 text-center sm:text-left max-w-xl">
                <!-- Emblem -->
                <x-circle-logo size="xl" variant="emblem" :animated="$animated" />

                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/90 border-2 border-pink-200 text-pink-700 text-[10px] font-black tracking-widest uppercase mb-2 shadow-2xs">
                        <span class="animate-pulse">✨</span>
                        <span>Official Edu-Social Network & Portfolio</span>
                    </div>
                    <h3 class="font-display font-black text-2xl sm:text-3xl text-slate-900 tracking-tight leading-tight mb-1.5">
                        <span>Pembda</span><span class="circle-gradient-text ml-1.5">CIRCLE</span>
                    </h3>
                    <div class="flex items-center justify-center sm:justify-start gap-2 text-xs sm:text-sm font-black tracking-widest uppercase mb-2.5">
                        <span class="text-sky-600">CONNECT</span>
                        <span class="text-pink-300">•</span>
                        <span class="text-rose-600">CREATE</span>
                        <span class="text-pink-300">•</span>
                        <span class="text-emerald-600">GROW</span>
                    </div>
                    <p class="text-xs sm:text-sm font-semibold text-slate-700 leading-relaxed">
                        Wadah sosial-edukasi resmi keluarga besar Pembda: menghubungkan seluruh siswa, guru, & mitra DUDI, memamerkan karya nyata, dan membangun reputasi masa depan.
                    </p>
                </div>
            </div>

            <!-- Right: 3 Pastel Motto Pillars -->
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-2.5 w-full lg:w-72 shrink-0">
                <!-- Pillar 1: CONNECT (Sky Blue) -->
                <div class="p-3 rounded-2xl bg-white/85 backdrop-blur-xs border-2 border-sky-200/90 shadow-2xs flex items-center gap-3 transition-transform hover:scale-[1.02]">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-base shrink-0 border border-sky-300">
                        🌐
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-black text-sky-800 uppercase tracking-wider">CONNECT</div>
                        <div class="text-[10px] text-slate-600 truncate font-bold">Jejaring 3 Unit & Industri</div>
                    </div>
                </div>

                <!-- Pillar 2: CREATE (Rose Pink) -->
                <div class="p-3 rounded-2xl bg-white/85 backdrop-blur-xs border-2 border-rose-200/90 shadow-2xs flex items-center gap-3 transition-transform hover:scale-[1.02]">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-base shrink-0 border border-rose-300">
                        💡
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-black text-rose-800 uppercase tracking-wider">CREATE</div>
                        <div class="text-[10px] text-slate-600 truncate font-bold">Portofolio Project & Riset</div>
                    </div>
                </div>

                <!-- Pillar 3: GROW (Mint Green) -->
                <div class="p-3 rounded-2xl bg-white/85 backdrop-blur-xs border-2 border-emerald-200/90 shadow-2xs flex items-center gap-3 transition-transform hover:scale-[1.02]">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-base shrink-0 border border-emerald-300">
                        🚀
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-black text-emerald-800 uppercase tracking-wider">GROW</div>
                        <div class="text-[10px] text-slate-600 truncate font-bold">Lencana Skill & Jawara</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
