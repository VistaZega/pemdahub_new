@props([
    'size' => 'md', // xs, sm, md, lg, xl, 2xl, full
    'variant' => 'horizontal', // emblem, horizontal, vertical, card, banner
    'withMotto' => true,
    'animated' => true,
    'class' => '',
])

@php
    $sizeMap = [
        'xs' => ['box' => 'w-7 h-7 rounded-lg', 'text' => 'text-sm', 'sub' => 'text-[8px]', 'gap' => 'gap-1.5'],
        'sm' => ['box' => 'w-9 h-9 rounded-xl', 'text' => 'text-base', 'sub' => 'text-[9px]', 'gap' => 'gap-2'],
        'md' => ['box' => 'w-11 h-11 rounded-2xl', 'text' => 'text-lg', 'sub' => 'text-[10px]', 'gap' => 'gap-2.5'],
        'lg' => ['box' => 'w-16 h-16 rounded-2xl', 'text' => 'text-2xl', 'sub' => 'text-xs', 'gap' => 'gap-3'],
        'xl' => ['box' => 'w-24 h-24 rounded-3xl', 'text' => 'text-3xl', 'sub' => 'text-sm', 'gap' => 'gap-4'],
        '2xl' => ['box' => 'w-32 h-32 rounded-3xl', 'text' => 'text-4xl', 'sub' => 'text-base', 'gap' => 'gap-5'],
        'full' => ['box' => 'w-full max-w-[280px] aspect-square rounded-3xl', 'text' => 'text-3xl', 'sub' => 'text-xs', 'gap' => 'gap-3'],
    ];

    $cfg = $sizeMap[$size] ?? $sizeMap['md'];
@endphp

@if($variant === 'emblem')
    {{-- HANYA EMBLEM / IKON BULAT BERPUTAR --}}
    <div class="relative {{ $cfg['box'] }} bg-[#091124] p-0.5 flex items-center justify-center shrink-0 shadow-md ring-1 ring-cyan-500/30 overflow-hidden group select-none {{ $class }}">
        <!-- Glowing Ambient Aura -->
        <div class="absolute inset-0 bg-gradient-to-tr from-cyan-500/25 via-blue-600/20 to-amber-500/25 blur-xs pointer-events-none"></div>

        @if($animated)
            <video autoplay loop muted playsinline class="w-full h-full object-cover rounded-[inherit] pointer-events-none relative z-10">
                <source src="{{ asset('images/circle/pembda-circle-icon-animated.webm') }}" type="video/webm">
                <source src="{{ asset('images/circle/pembda-circle-icon-animated.mp4') }}" type="video/mp4">
                <img src="{{ asset('images/circle/pembda-circle-icon.png') }}" alt="PembdaCIRCLE" class="w-full h-full object-cover rounded-[inherit]">
            </video>
        @else
            <img src="{{ asset('images/circle/pembda-circle-icon.png') }}" alt="PembdaCIRCLE" class="w-full h-full object-cover rounded-[inherit] relative z-10">
        @endif
    </div>

@elseif($variant === 'horizontal')
    {{-- EMBLEM + TEKS PEMBDACIRCLE + MOTTO DI SEBELAHNYA (DEFAULT NAVBAR / HEADER) --}}
    <div class="flex items-center {{ $cfg['gap'] }} select-none {{ $class }}">
        <!-- Animated Emblem Orb -->
        <div class="relative {{ $cfg['box'] }} bg-[#091124] p-0.5 flex items-center justify-center shrink-0 shadow-md ring-2 ring-cyan-400/40 group-hover:ring-cyan-300 transition-all duration-300 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-tr from-cyan-500/30 via-blue-600/25 to-amber-500/25 blur-xs pointer-events-none"></div>
            @if($animated)
                <video autoplay loop muted playsinline class="w-full h-full object-cover rounded-[inherit] pointer-events-none relative z-10">
                    <source src="{{ asset('images/circle/pembda-circle-icon-animated.webm') }}" type="video/webm">
                    <source src="{{ asset('images/circle/pembda-circle-icon-animated.mp4') }}" type="video/mp4">
                    <img src="{{ asset('images/circle/pembda-circle-icon.png') }}" alt="PembdaCIRCLE" class="w-full h-full object-cover rounded-[inherit]">
                </video>
            @else
                <img src="{{ asset('images/circle/pembda-circle-icon.png') }}" alt="PembdaCIRCLE" class="w-full h-full object-cover rounded-[inherit] relative z-10">
            @endif
        </div>

        <!-- Typography & Tagline -->
        <div class="flex flex-col min-w-0">
            <div class="flex items-center gap-1.5 leading-none">
                <span class="font-display font-black {{ $cfg['text'] }} text-slate-950 tracking-tight">Pembda</span>
                <div class="relative inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-[#091124] border border-cyan-400/40 shadow-xs overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-cyan-400/25 to-transparent -translate-x-full circle-shimmer pointer-events-none"></div>
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-80"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-cyan-400 shadow-[0_0_6px_#22d3ee]"></span>
                    </span>
                    <span class="font-display font-black text-[10px] sm:text-[11px] tracking-widest uppercase text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 via-sky-400 to-amber-300">
                        CIRCLE
                    </span>
                </div>
            </div>

            @if($withMotto)
                <div class="flex items-center gap-1 {{ $cfg['sub'] }} font-black tracking-widest text-slate-600 uppercase mt-1">
                    <span class="text-cyan-600 font-extrabold hover:text-cyan-500 transition-colors">CONNECT</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-amber-600 font-extrabold hover:text-amber-500 transition-colors">CREATE</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-emerald-600 font-extrabold hover:text-emerald-500 transition-colors">GROW</span>
                </div>
            @endif
        </div>
    </div>

@elseif($variant === 'vertical')
    {{-- LOGO CENTERED VERTIKAL (COCOK UNTUK SPLASH / MODAL / POSTER) --}}
    <div class="flex flex-col items-center text-center select-none {{ $class }}">
        <!-- Animated Emblem Orb -->
        <div class="relative {{ $cfg['box'] }} bg-[#091124] p-1 flex items-center justify-center shrink-0 shadow-xl ring-4 ring-cyan-500/30 overflow-hidden mb-3">
            <div class="absolute inset-0 bg-gradient-to-tr from-cyan-500/30 via-blue-600/30 to-amber-500/30 blur-sm pointer-events-none"></div>
            @if($animated)
                <video autoplay loop muted playsinline class="w-full h-full object-cover rounded-[inherit] pointer-events-none relative z-10">
                    <source src="{{ asset('images/circle/pembda-circle-icon-animated.webm') }}" type="video/webm">
                    <source src="{{ asset('images/circle/pembda-circle-icon-animated.mp4') }}" type="video/mp4">
                    <img src="{{ asset('images/circle/pembda-circle-icon.png') }}" alt="PembdaCIRCLE" class="w-full h-full object-cover rounded-[inherit]">
                </video>
            @else
                <img src="{{ asset('images/circle/pembda-circle-icon.png') }}" alt="PembdaCIRCLE" class="w-full h-full object-cover rounded-[inherit] relative z-10">
            @endif
        </div>

        <div class="flex items-center gap-2 mb-1.5">
            <span class="font-display font-black text-2xl sm:text-3xl text-slate-950 tracking-tight">PEMBDA</span>
            <span class="font-display font-black text-2xl sm:text-3xl tracking-widest uppercase text-transparent bg-clip-text bg-gradient-to-r from-cyan-500 to-blue-600">
                CIRCLE
            </span>
        </div>

        @if($withMotto)
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-slate-900 text-white text-[10px] sm:text-xs font-black tracking-[0.2em] uppercase shadow-sm">
                <span class="text-cyan-400">CONNECT</span>
                <span class="text-slate-500">•</span>
                <span class="text-amber-400">CREATE</span>
                <span class="text-slate-500">•</span>
                <span class="text-emerald-400">GROW</span>
            </div>
        @endif
    </div>

@elseif($variant === 'banner' || $variant === 'card')
    {{-- HERO CARD / BRAND SHOWCASE DENGAN 3 PILAR MOTTO LENGKAP --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#070d1e] via-[#0b1736] to-[#091124] text-white p-6 sm:p-8 border-2 border-cyan-500/30 shadow-xl {{ $class }}">
        <!-- Cosmic Ambient Background Effects -->
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.06)_1px,_transparent_1px)] bg-[size:16px_16px] pointer-events-none opacity-40"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6 sm:gap-8">
            <!-- Left: Animated Logo + Brand Tagline -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left max-w-xl">
                <!-- Video Emblem -->
                <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-[#050a16] p-1 shrink-0 shadow-[0_0_25px_rgba(6,182,212,0.35)] ring-2 ring-cyan-400/50 overflow-hidden">
                    <video autoplay loop muted playsinline class="w-full h-full object-cover rounded-2xl pointer-events-none">
                        <source src="{{ asset('images/circle/pembda-circle-icon-animated.webm') }}" type="video/webm">
                        <source src="{{ asset('images/circle/pembda-circle-icon-animated.mp4') }}" type="video/mp4">
                        <img src="{{ asset('images/circle/pembda-circle-icon.png') }}" alt="PembdaCIRCLE" class="w-full h-full object-cover rounded-2xl">
                    </video>
                </div>

                <!-- Titles & Manifesto -->
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/20 border border-cyan-400/40 text-cyan-300 text-[10px] font-black tracking-widest uppercase mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                        <span>Official Edu-Social Network & Portfolio</span>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center justify-center sm:justify-start gap-2">
                        <span>Pembda</span>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-sky-300 to-amber-300">CIRCLE</span>
                    </h3>
                    <!-- The 3 Pillars Motto Highlight -->
                    <div class="flex items-center justify-center sm:justify-start gap-2 text-xs sm:text-sm font-black tracking-widest text-slate-300 uppercase mt-1 mb-2">
                        <span class="text-cyan-400">CONNECT</span>
                        <span class="text-slate-500">•</span>
                        <span class="text-amber-400">CREATE</span>
                        <span class="text-slate-500">•</span>
                        <span class="text-emerald-400">GROW</span>
                    </div>
                    <p class="text-xs text-slate-300 font-medium leading-relaxed">
                        Wadah sosial-edukasi resmi keluarga besar Pembda: tempat berjejaring, merilis karya kejuruan & riset, hingga menumbuhkan reputasi dan prestasi masa depan.
                    </p>
                </div>
            </div>

            <!-- Right: 3 Pillars Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-1 gap-2.5 w-full md:w-64 shrink-0">
                <!-- Pillar 1: CONNECT -->
                <div class="p-2.5 rounded-2xl bg-white/5 border border-cyan-400/30 hover:bg-cyan-500/10 transition-all flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-black text-sm shrink-0 border border-cyan-400/40">
                        🌐
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-black text-cyan-300 uppercase tracking-wider">CONNECT</div>
                        <div class="text-[10px] text-slate-300 truncate font-semibold">Jejaring 3 Unit & Industri</div>
                    </div>
                </div>

                <!-- Pillar 2: CREATE -->
                <div class="p-2.5 rounded-2xl bg-white/5 border border-amber-400/30 hover:bg-amber-500/10 transition-all flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-black text-sm shrink-0 border border-amber-400/40">
                        💡
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-black text-amber-300 uppercase tracking-wider">CREATE</div>
                        <div class="text-[10px] text-slate-300 truncate font-semibold">Portofolio Project & Riset</div>
                    </div>
                </div>

                <!-- Pillar 3: GROW -->
                <div class="p-2.5 rounded-2xl bg-white/5 border border-emerald-400/30 hover:bg-emerald-500/10 transition-all flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black text-sm shrink-0 border border-emerald-400/40">
                        🚀
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-black text-emerald-300 uppercase tracking-wider">GROW</div>
                        <div class="text-[10px] text-slate-300 truncate font-semibold">Lencana Skill & Jawara</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
