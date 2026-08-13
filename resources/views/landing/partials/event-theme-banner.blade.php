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
        </style>
    @endif
@endif
