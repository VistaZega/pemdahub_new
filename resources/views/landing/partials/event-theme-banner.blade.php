@php
    $theme = $homepageTheme ?? 'regular';
@endphp

@if($theme !== 'regular')
    <style>
        @keyframes marquee-running {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee-running {
            display: inline-flex;
            white-space: nowrap;
            animation: marquee-running 22s linear infinite;
            will-change: transform;
        }
        .event-theme-banner:hover .animate-marquee-running {
            animation-play-state: paused;
        }
    </style>

    {{-- EVENT THEME TOP BANNER & CUSTOM THEME STYLES --}}
    @if($theme === 'kemerdekaan')
        <div class="event-theme-banner bg-gradient-to-r from-red-800 via-red-600 to-red-800 text-white py-2.5 px-4 border-b-2 border-amber-300 shadow-md relative z-[60] overflow-hidden">
            <div class="animate-marquee-running items-center gap-8 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-3.5 inline-block rounded shadow-xs border border-white/40" viewBox="0 0 3 2"><rect width="3" height="1" fill="#ef4444"/><rect y="1" width="3" height="1" fill="#ffffff"/></svg>
                    <span>Dirgahayu Republik Indonesia! ~ "Nusantara Baru, Indonesia Maju"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">17 Agustus</span>
                    <svg class="w-5 h-3.5 inline-block rounded shadow-xs border border-white/40" viewBox="0 0 3 2"><rect width="3" height="1" fill="#ef4444"/><rect y="1" width="3" height="1" fill="#ffffff"/></svg>
                </span>
                <span class="text-amber-300">★</span>
                <span class="flex items-center gap-2">
                    <i class="fas fa-award text-amber-300"></i>
                    <span>Perguruan PEMBDA Nias Mengucapkan Selamat Hari Kemerdekaan RI Ke-81!</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">Merdeka!</span>
                    <i class="fas fa-award text-amber-300"></i>
                </span>
                <span class="text-amber-300">★</span>
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-3.5 inline-block rounded shadow-xs border border-white/40" viewBox="0 0 3 2"><rect width="3" height="1" fill="#ef4444"/><rect y="1" width="3" height="1" fill="#ffffff"/></svg>
                    <span>Dirgahayu Republik Indonesia! ~ "Nusantara Baru, Indonesia Maju"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">17 Agustus</span>
                    <svg class="w-5 h-3.5 inline-block rounded shadow-xs border border-white/40" viewBox="0 0 3 2"><rect width="3" height="1" fill="#ef4444"/><rect y="1" width="3" height="1" fill="#ffffff"/></svg>
                </span>
                <span class="text-amber-300">★</span>
                <span class="flex items-center gap-2">
                    <i class="fas fa-award text-amber-300"></i>
                    <span>Perguruan PEMBDA Nias Mengucapkan Selamat Hari Kemerdekaan RI Ke-81!</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">Merdeka!</span>
                    <i class="fas fa-award text-amber-300"></i>
                </span>
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

            /* === TULISAN PembdaHUB: HUB DIGARISI PUTIH TEBAL === */
            [data-theme="kemerdekaan"] .hero-section .display span {
                color: #dc2626 !important;
                -webkit-text-fill-color: #dc2626 !important;
                -webkit-text-stroke: 2.5px #ffffff !important;
                text-shadow: 
                    -2px -2px 0 #ffffff,
                     2px -2px 0 #ffffff,
                    -2px  2px 0 #ffffff,
                     2px  2px 0 #ffffff,
                     0 6px 18px rgba(0, 0, 0, 0.4) !important;
            }

            /* === HERO BACKGROUND BENDERA MERAH PUTIH (ATAS MERAH, BAWAH PUTIH) === */
            [data-theme="kemerdekaan"] .hero-section {
                background: 
                    linear-gradient(180deg, 
                        #b91c1c 0%, 
                        #dc2626 36%, 
                        #ef4444 46%, 
                        #ffffff 54%, 
                        #f8fafc 100%
                    ) !important;
                position: relative;
            }

            /* Lipatan Kain Bendera & Efek Berkibar */
            [data-theme="kemerdekaan"] .hero-section::before {
                content: '';
                position: absolute;
                inset: 0;
                background: 
                    repeating-linear-gradient(
                        -45deg,
                        rgba(0, 0, 0, 0.05) 0px,
                        rgba(0, 0, 0, 0.05) 50px,
                        rgba(255, 255, 255, 0.05) 50px,
                        rgba(255, 255, 255, 0.05) 100px
                    ),
                    radial-gradient(ellipse at 50% 30%, rgba(255,255,255,0.2) 0%, transparent 70%);
                pointer-events: none;
                z-index: 1;
            }

            /* Particle Merah Putih */
            [data-theme="kemerdekaan"] .particle:nth-child(even) {
                background: rgba(239, 68, 68, 0.9) !important;
                box-shadow: 0 0 12px rgba(239, 68, 68, 0.8) !important;
            }
            [data-theme="kemerdekaan"] .particle:nth-child(odd) {
                background: rgba(255, 255, 255, 0.95) !important;
                box-shadow: 0 0 12px rgba(255, 255, 255, 0.9) !important;
            }

            /* === TOMBOL EKSPLORASI EKOSISTEM AGAR TERBACA JELAS PADA LATAR PUTIH === */
            [data-theme="kemerdekaan"] .btn-ghost-white {
                background: linear-gradient(135deg, #7f1d1d, #991b1b) !important;
                color: #ffffff !important;
                border: 2px solid #b91c1c !important;
                font-weight: 800 !important;
                box-shadow: 0 6px 18px rgba(127, 29, 29, 0.35) !important;
            }
            [data-theme="kemerdekaan"] .btn-ghost-white:hover {
                background: linear-gradient(135deg, #991b1b, #dc2626) !important;
                color: #ffffff !important;
                border-color: #ef4444 !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 10px 24px rgba(220, 38, 38, 0.45) !important;
            }

            /* Stat Item & Tombol di Area Bawah yang Putih */
            [data-theme="kemerdekaan"] .live-stat-item {
                background: linear-gradient(135deg, #b91c1c, #dc2626) !important;
                border: 2px solid #fca5a5 !important;
                color: #ffffff !important;
                box-shadow: 0 8px 24px rgba(185, 28, 28, 0.25) !important;
            }
            [data-theme="kemerdekaan"] .live-stat-val {
                color: #fef08a !important;
            }
            [data-theme="kemerdekaan"] .live-stat-label {
                color: #ffffff !important;
            }

            [data-theme="kemerdekaan"] .hero-glow-1 {
                background: radial-gradient(circle, rgba(239, 68, 68, 0.4) 0%, transparent 70%) !important;
            }
            [data-theme="kemerdekaan"] .hero-glow-2 {
                background: radial-gradient(circle, rgba(255, 255, 255, 0.6) 0%, transparent 70%) !important;
            }
        </style>

    @elseif($theme === 'paskah')
        <div class="event-theme-banner bg-gradient-to-r from-purple-900 via-indigo-900 to-purple-950 text-amber-200 py-2.5 px-4 border-b-2 border-amber-400 shadow-md relative z-[60] overflow-hidden">
            <div class="animate-marquee-running items-center gap-8 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span class="flex items-center gap-2">
                    <i class="fas fa-cross text-amber-300"></i>
                    <span>Selamat Hari Raya Paskah! ~ "Terang Kebangkitan &amp; Harapan Bagi Sesama"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">Hari Paskah</span>
                    <i class="fas fa-cross text-amber-300"></i>
                </span>
                <span class="text-amber-300">★</span>
                <span class="flex items-center gap-2">
                    <i class="fas fa-cross text-amber-300"></i>
                    <span>Selamat Hari Raya Paskah! ~ "Terang Kebangkitan &amp; Harapan Bagi Sesama"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">Hari Paskah</span>
                    <i class="fas fa-cross text-amber-300"></i>
                </span>
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
        <div class="event-theme-banner bg-gradient-to-r from-emerald-800 via-rose-800 to-emerald-950 text-amber-200 py-2.5 px-4 border-b-2 border-amber-400 shadow-md relative z-[60] overflow-hidden">
            <div class="animate-marquee-running items-center gap-8 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span class="flex items-center gap-2">
                    <i class="fas fa-tree text-emerald-300"></i>
                    <span>Selamat Hari Natal &amp; Tahun Baru! ~ "Damai Suka Cita &amp; Berkah Bagi Kita Semua"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">Natal &amp; Tahun Baru</span>
                    <i class="fas fa-snowflake text-sky-200"></i>
                </span>
                <span class="text-amber-300">★</span>
                <span class="flex items-center gap-2">
                    <i class="fas fa-tree text-emerald-300"></i>
                    <span>Selamat Hari Natal &amp; Tahun Baru! ~ "Damai Suka Cita &amp; Berkah Bagi Kita Semua"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">Natal &amp; Tahun Baru</span>
                    <i class="fas fa-snowflake text-sky-200"></i>
                </span>
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
        <div class="event-theme-banner bg-gradient-to-r from-rose-950 via-amber-900 to-slate-950 text-amber-300 py-2.5 px-4 border-b-2 border-amber-500 shadow-md relative z-[60] overflow-hidden">
            <div class="animate-marquee-running items-center gap-8 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span class="flex items-center gap-2">
                    <i class="fas fa-medal text-amber-300"></i>
                    <span>Selamat Hari Pahlawan (10 November)! ~ "Kobarkan Semangat Perjuangan &amp; Integritas Bangsa"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">10 November</span>
                    <i class="fas fa-medal text-amber-300"></i>
                </span>
                <span class="text-amber-300">★</span>
                <span class="flex items-center gap-2">
                    <i class="fas fa-medal text-amber-300"></i>
                    <span>Selamat Hari Pahlawan (10 November)! ~ "Kobarkan Semangat Perjuangan &amp; Integritas Bangsa"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">10 November</span>
                    <i class="fas fa-medal text-amber-300"></i>
                </span>
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
        <div class="event-theme-banner bg-gradient-to-r from-sky-900 via-blue-900 to-indigo-950 text-amber-300 py-2.5 px-4 border-b-2 border-amber-400 shadow-md relative z-[60] overflow-hidden">
            <div class="animate-marquee-running items-center gap-8 text-xs sm:text-sm font-extrabold tracking-wide uppercase">
                <span class="flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-amber-300"></i>
                    <span>Selamat Hari Pendidikan Nasional! ~ "Tut Wuri Handayani — Ing Ngarso Sung Tulodo"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">2 Mei (Hardiknas)</span>
                    <i class="fas fa-book-open text-amber-300"></i>
                </span>
                <span class="text-amber-300">★</span>
                <span class="flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-amber-300"></i>
                    <span>Selamat Hari Pendidikan Nasional! ~ "Tut Wuri Handayani — Ing Ngarso Sung Tulodo"</span>
                    <span class="bg-amber-400 text-black px-2.5 py-0.5 rounded-lg text-[11px] font-black border border-amber-300">2 Mei (Hardiknas)</span>
                    <i class="fas fa-book-open text-amber-300"></i>
                </span>
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
