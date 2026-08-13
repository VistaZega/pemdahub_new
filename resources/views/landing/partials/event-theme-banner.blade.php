@php
    $theme = $homepageTheme ?? 'regular';
@endphp

@if($theme !== 'regular')
    {{-- EVENT THEME TOP BANNER & CUSTOM THEME STYLES --}}
    @if($theme === 'kemerdekaan')
        <div class="event-theme-banner bg-gradient-to-r from-red-700 via-red-600 to-red-800 text-white py-2.5 px-4 text-center border-b-2 border-amber-300 shadow-md relative z-[60] overflow-hidden">
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 sm:gap-4 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span class="animate-bounce">🇮🇩</span>
                <span>Dirgahayu Republik Indonesia! ~ "Nusantara Baru, Indonesia Maju"</span>
                <span class="hidden md:inline bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300 shadow-xs">17 Agustus</span>
                <span class="animate-bounce">🇮🇩</span>
            </div>
        </div>
        <style>
            [data-theme="kemerdekaan"] {
                --indigo: #dc2626 !important;
                --indigo-dark: #7f1d1d !important;
                --indigo-mid: #b91c1c !important;
                --indigo-light: #ef4444 !important;
                --indigo-bg: #fef2f2 !important;
            }

            /* === HERO BACKGROUND BENDERA MERAH PUTIH BERKIBAR === */
            [data-theme="kemerdekaan"] .hero-section {
                background: 
                    linear-gradient(160deg, rgba(127, 29, 29, 0.88) 0%, rgba(185, 28, 28, 0.82) 45%, rgba(15, 23, 42, 0.94) 100%),
                    url('https://images.unsplash.com/photo-1590059530491-03204a9e5b5d?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat !important;
                position: relative;
            }
            [data-theme="kemerdekaan"] .hero-section::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, rgba(239, 68, 68, 0.35) 0%, rgba(255, 255, 255, 0.15) 50%, rgba(15, 23, 42, 0.6) 100%);
                mix-blend-mode: overlay;
                pointer-events: none;
                z-index: 1;
            }
            [data-theme="kemerdekaan"] .particle:nth-child(even) {
                background: rgba(239, 68, 68, 0.8) !important;
                box-shadow: 0 0 12px rgba(239, 68, 68, 0.9) !important;
            }
            [data-theme="kemerdekaan"] .particle:nth-child(odd) {
                background: rgba(255, 255, 255, 0.9) !important;
                box-shadow: 0 0 12px rgba(255, 255, 255, 0.9) !important;
            }
            [data-theme="kemerdekaan"] .hero-glow-1 {
                background: radial-gradient(circle, rgba(239, 68, 68, 0.5) 0%, transparent 70%) !important;
            }
            [data-theme="kemerdekaan"] .hero-glow-2 {
                background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%) !important;
            }
        </style>

    @elseif($theme === 'paskah')
        <div class="event-theme-banner bg-gradient-to-r from-purple-900 via-indigo-900 to-purple-950 text-amber-200 py-2.5 px-4 text-center border-b-2 border-amber-400 shadow-md relative z-[60] overflow-hidden">
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 sm:gap-4 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span>✝️</span>
                <span>Selamat Hari Raya Paskah! ~ "Terang Kebangkitan &amp; Harapan Bagi Sesama"</span>
                <span class="hidden md:inline bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300 shadow-xs">Hari Paskah</span>
                <span>✝️</span>
            </div>
        </div>
        <style>
            [data-theme="paskah"] {
                --indigo: #6b21a8 !important;
                --indigo-dark: #3b0764 !important;
                --indigo-mid: #581c87 !important;
                --indigo-light: #9333ea !important;
                --indigo-bg: #faf5ff !important;
            }
            [data-theme="paskah"] .hero-section {
                background: 
                    linear-gradient(160deg, rgba(59, 7, 100, 0.9) 0%, rgba(88, 28, 135, 0.85) 50%, rgba(15, 23, 42, 0.95) 100%),
                    url('https://images.unsplash.com/photo-1518709268805-4e9042af9f23?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat !important;
            }
            [data-theme="paskah"] .hero-glow-1 {
                background: radial-gradient(circle, rgba(147, 51, 234, 0.5) 0%, transparent 70%) !important;
            }
        </style>

    @elseif($theme === 'natal')
        <div class="event-theme-banner bg-gradient-to-r from-emerald-800 via-rose-800 to-emerald-950 text-amber-200 py-2.5 px-4 text-center border-b-2 border-amber-400 shadow-md relative z-[60] overflow-hidden">
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 sm:gap-4 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span class="animate-pulse">🎄</span>
                <span>Selamat Hari Natal &amp; Tahun Baru! ~ "Damai Suka Cita &amp; Berkah Bagi Kita Semua"</span>
                <span class="hidden md:inline bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300 shadow-xs">Natal &amp; Tahun Baru</span>
                <span class="animate-pulse">❄️</span>
            </div>
        </div>
        <style>
            [data-theme="natal"] {
                --indigo: #047857 !important;
                --indigo-dark: #064e3b !important;
                --indigo-mid: #065f46 !important;
                --indigo-light: #10b981 !important;
                --indigo-bg: #ecfdf5 !important;
            }
            [data-theme="natal"] .hero-section {
                background: 
                    linear-gradient(160deg, rgba(6, 78, 59, 0.9) 0%, rgba(15, 118, 110, 0.82) 50%, rgba(15, 23, 42, 0.95) 100%),
                    url('https://images.unsplash.com/photo-1543589077-47d51996477a?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat !important;
            }
            [data-theme="natal"] .hero-glow-1 {
                background: radial-gradient(circle, rgba(16, 185, 129, 0.5) 0%, transparent 70%) !important;
            }
        </style>

    @elseif($theme === 'pahlawan')
        <div class="event-theme-banner bg-gradient-to-r from-rose-950 via-amber-900 to-slate-950 text-amber-300 py-2.5 px-4 text-center border-b-2 border-amber-500 shadow-md relative z-[60] overflow-hidden">
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 sm:gap-4 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span>🎖️</span>
                <span>Selamat Hari Pahlawan (10 November)! ~ "Kobarkan Semangat Perjuangan &amp; Integritas Bangsa"</span>
                <span class="hidden md:inline bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300 shadow-xs">10 November</span>
                <span>🎖️</span>
            </div>
        </div>
        <style>
            [data-theme="pahlawan"] {
                --indigo: #9f1239 !important;
                --indigo-dark: #4c0519 !important;
                --indigo-mid: #881337 !important;
                --indigo-light: #e11d48 !important;
                --indigo-bg: #fff1f2 !important;
            }
            [data-theme="pahlawan"] .hero-section {
                background: 
                    linear-gradient(160deg, rgba(76, 5, 25, 0.92) 0%, rgba(136, 19, 55, 0.85) 50%, rgba(15, 23, 42, 0.95) 100%),
                    url('https://images.unsplash.com/photo-1579546929518-9e396f3cc809?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat !important;
            }
            [data-theme="pahlawan"] .hero-glow-1 {
                background: radial-gradient(circle, rgba(225, 29, 72, 0.5) 0%, transparent 70%) !important;
            }
        </style>

    @elseif($theme === 'pendidikan')
        <div class="event-theme-banner bg-gradient-to-r from-sky-900 via-blue-900 to-indigo-950 text-amber-300 py-2.5 px-4 text-center border-b-2 border-amber-400 shadow-md relative z-[60] overflow-hidden">
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 sm:gap-4 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span>📚</span>
                <span>Selamat Hari Pendidikan Nasional! ~ "Tut Wuri Handayani — Ing Ngarso Sung Tulodo"</span>
                <span class="hidden md:inline bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300 shadow-xs">2 Mei (Hardiknas)</span>
                <span>🎓</span>
            </div>
        </div>
        <style>
            [data-theme="pendidikan"] {
                --indigo: #0369a1 !important;
                --indigo-dark: #0c4a6e !important;
                --indigo-mid: #075985 !important;
                --indigo-light: #0284c7 !important;
                --indigo-bg: #f0f9ff !important;
            }
            [data-theme="pendidikan"] .hero-section {
                background: 
                    linear-gradient(160deg, rgba(12, 74, 110, 0.9) 0%, rgba(3, 105, 161, 0.85) 50%, rgba(15, 23, 42, 0.95) 100%),
                    url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat !important;
            }
            [data-theme="pendidikan"] .hero-glow-1 {
                background: radial-gradient(circle, rgba(2, 132, 199, 0.5) 0%, transparent 70%) !important;
            }
        </style>
    @endif
@endif
