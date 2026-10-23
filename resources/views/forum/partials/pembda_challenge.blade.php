<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ⚡ PembdaCHALLENGE: Misionaris vs Kanibal (River Crossing Logic Quest)  -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="pembda-challenge"
     class="mb-6 bg-white border-2 border-[#121316] rounded-3xl overflow-hidden shadow-[4px_4px_0px_#121316] scroll-mt-24 transition-all duration-300" 
     :class="{ 'mobile-landscape-active': isLandscapeMode, 'mobile-physical-landscape': isPhysicalLandscape }"
     x-data="pembdaChallenge()" 
     x-init="initGame()"
     @open-challenge.window="isCollapsed = false"
     @toggle-mobile-landscape.window="toggleLandscapeMode()">

    <!-- Floating Exit Button khusus saat Mode Landscape Aktif di Mobile -->
    <div x-show="isLandscapeMode || isPhysicalLandscape" 
         class="fixed top-2 right-2 z-[999999] flex items-center gap-2">
        <button type="button" 
                @click="toggleLandscapeMode()" 
                class="px-2.5 py-1 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs border-2 border-white shadow-xl flex items-center gap-1 cursor-pointer">
            <i class="fa-solid fa-xmark text-xs"></i>
            <span class="text-[11px]" x-text="isLandscapeMode ? 'Tutup Layar Penuh' : 'Mode Biasa'"></span>
        </button>
    </div>

    <!-- Header Bar -->
    <div class="px-2.5 py-1.5 sm:py-2 border-b-2 border-[#121316] flex items-center justify-between gap-2 bg-gradient-to-r from-amber-300 via-yellow-300 to-amber-200 cursor-pointer hover:opacity-95 transition" 
         @click="isCollapsed = !isCollapsed">
        <div class="flex items-center gap-2 min-w-0">
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-gradient-to-tr from-violet-600 via-indigo-600 to-pink-500 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center text-white font-black text-sm shrink-0">
                ⚡
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 flex-nowrap">
                    <h2 class="forum-hdr text-xs sm:text-sm font-black text-[#121316] tracking-tight leading-tight m-0 truncate">PembdaCHALLENGE</h2>
                    <span class="text-[8px] font-mono-code font-black px-1.5 py-0.2 rounded-full bg-[#121316] text-[#fde047] shrink-0 hidden xs:inline-flex items-center gap-1 shadow-xs">
                        <span>GAME SAINS</span>
                    </span>
                    <span class="text-[8px] font-mono-code font-black px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-400 shrink-0 hidden sm:inline-flex">
                        +50 POIN
                    </span>
                </div>
                <span class="text-[10px] text-slate-800 font-bold block truncate max-w-[160px] xs:max-w-[220px] sm:max-w-md md:max-w-xl" 
                      x-text="activeGame === 'chemistry' ? '🧪 Penyeimbang Reaksi Kimia (Hukum Lavoisier)' : (activeGame === 'logic' ? '🔌 Sirkuit Gerbang Logika IT (Arsitektur CPU)' : (activeGame === 'water' ? '🚰 Tabung Air (Die Hard 3)' : (activeGame === 'hanoi' ? '🏛️ Menara Hanoi (Rekursi Logika)' : '🚣 Misionaris vs Kanibal (River Crossing)')))">
                </span>
            </div>
        </div>

        <div class="flex items-center gap-1.5 shrink-0" @click.stop>
            @if(request('view') === 'challenge' || request('tab') === 'challenge')
                <!-- Tombol Langsung Kembali ke Lobi Forum / Space -->
                <a href="{{ request()->routeIs('mobile.*') ? route('mobile.space.index') : route('forum.index') }}" class="btn-tactile-white px-2 py-1 rounded-xl text-xs font-black flex items-center gap-1 shadow-[2px_2px_0px_#121316]">
                    <i class="fa-solid fa-arrow-left text-xs text-rose-600"></i>
                    <span class="hidden xs:inline text-[11px]">Lobi</span>
                </a>
            @endif

            <!-- Status Nyawa (Game Misionaris vs Kanibal) -->
            <div x-show="activeGame === 'river'" class="flex items-center gap-0.5 px-1.5 py-0.5 bg-white border-2 border-[#121316] rounded-xl shadow-[1px_1px_0px_#121316]" title="Sisa Kesempatan">
                <template x-for="i in 3" :key="i">
                    <span class="text-xs transition-transform" :class="i <= lives ? 'scale-100' : 'opacity-20 grayscale scale-75'">
                        ❤️
                    </span>
                </template>
            </div>

            <!-- Tombol Fullscreen Landscape (Khusus Layar HP / Tablet) -->
            <button type="button" 
                    @click="toggleLandscapeMode()" 
                    class="btn-tactile-white px-2 py-1 rounded-xl text-xs font-black flex items-center gap-1"
                    :class="isLandscapeMode ? 'bg-[#fde047] text-[#121316]' : 'bg-white text-[#121316]'"
                    title="Putar Arena Game Menjadi Fullscreen Landscape">
                <i class="fa-solid fa-arrows-rotate text-purple-700 text-xs"></i>
                <span class="text-[10px] font-mono-code font-black" x-text="isLandscapeMode ? 'Normal' : 'Landscape'"></span>
            </button>

            <!-- Tombol Toggle Mode Layar Penuh (Tutup/Buka Sidebar Samping) -->
            <button type="button" 
                    @click="$dispatch('toggle-theatre-mode')" 
                    class="btn-tactile-yellow px-2 py-1 rounded-xl text-xs font-black hidden md:flex items-center gap-1"
                    title="Buka / Tutup Menu Samping (Perluas Arena Permainan)">
                <i class="fa-solid fa-up-right-and-down-left-from-center text-xs text-[#121316]"></i>
                <span class="text-[11px]">Layar Penuh</span>
            </button>

            <!-- Tombol Instruksi (Misionaris) -->
            <button x-show="activeGame === 'river'" @click="showRulesModal = true" class="btn-tactile-white px-2 py-1 rounded-xl text-xs font-black hidden sm:flex items-center gap-1">
                <i class="fa-solid fa-circle-question text-indigo-600 text-xs"></i>
                <span class="text-[11px]">Aturan</span>
            </button>

            <!-- Tombol Buka/Tutup -->
            <button @click="isCollapsed = !isCollapsed" class="p-1 sm:p-1.5 bg-white border-2 border-[#121316] rounded-xl text-[#121316] shadow-[2px_2px_0px_#121316] hover:bg-[#faf8f5] transition">
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300" :class="isCollapsed ? '' : 'rotate-180'"></i>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- BAR PEMILIH TANTANGAN (TAB SWITCHER GAME SAINS & LOGIKA)            -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="px-2 py-1 bg-slate-900 border-b-2 border-[#121316] flex items-center justify-between gap-1.5 overflow-hidden">
        <div class="flex items-center gap-1 sm:gap-1.5 overflow-x-auto no-scrollbar py-0.5 min-w-0 flex-1">
            <span class="text-[9px] font-mono-code font-bold text-slate-400 uppercase tracking-wider mr-0.5 hidden md:inline shrink-0">PILIH:</span>
            <!-- Tab 1: Reaksi Kimia (BARU) -->
            <button type="button" 
                    @click="activeGame = 'chemistry'" 
                    class="px-2 py-0.5 rounded-lg text-[10.5px] font-black transition-all flex items-center gap-1 border-2 border-[#121316] cursor-pointer whitespace-nowrap shrink-0"
                    :class="activeGame === 'chemistry' ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#ffffff]' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border-slate-700'">
                <span>🧪 <span class="hidden sm:inline">Reaksi</span> Kimia</span>
                <span class="text-[6.5px] font-mono-code font-black px-1 py-0.2 rounded-full bg-emerald-500 text-white animate-pulse">BARU</span>
            </button>
            <!-- Tab 2: Gerbang Logika IT -->
            <button type="button" 
                    @click="activeGame = 'logic'" 
                    class="px-2 py-0.5 rounded-lg text-[10.5px] font-black transition-all flex items-center gap-1 border-2 border-[#121316] cursor-pointer whitespace-nowrap shrink-0"
                    :class="activeGame === 'logic' ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#ffffff]' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border-slate-700'">
                <span>🔌 <span class="hidden sm:inline">Gerbang</span> Logika</span>
            </button>
            <!-- Tab 3: Tabung Air (Die Hard 3) -->
            <button type="button" 
                    @click="activeGame = 'water'" 
                    class="px-2 py-0.5 rounded-lg text-[10.5px] font-black transition-all flex items-center gap-1 border-2 border-[#121316] cursor-pointer whitespace-nowrap shrink-0"
                    :class="activeGame === 'water' ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#ffffff]' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border-slate-700'">
                <span>🚰 Tabung Air</span>
            </button>
            <!-- Tab 4: Menara Hanoi -->
            <button type="button" 
                    @click="activeGame = 'hanoi'" 
                    class="px-2 py-0.5 rounded-lg text-[10.5px] font-black transition-all flex items-center gap-1 border-2 border-[#121316] cursor-pointer whitespace-nowrap shrink-0"
                    :class="activeGame === 'hanoi' ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#ffffff]' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border-slate-700'">
                <span>🏛️ <span class="hidden sm:inline">Menara</span> Hanoi</span>
            </button>
            <!-- Tab 5: Misionaris vs Kanibal -->
            <button type="button" 
                    @click="activeGame = 'river'" 
                    class="px-2 py-0.5 rounded-lg text-[10.5px] font-black transition-all flex items-center gap-1 border-2 border-[#121316] cursor-pointer whitespace-nowrap shrink-0"
                    :class="activeGame === 'river' ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#ffffff]' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border-slate-700'">
                <span>🚣 Misionaris</span>
            </button>
        </div>

        <div class="text-[9.5px] font-mono-code font-bold text-amber-300 hidden md:flex items-center gap-1 shrink-0">
            <i class="fa-solid fa-trophy text-xs"></i>
            <span x-text="activeGame === 'chemistry' ? 'Lavoisier (+50)' : (activeGame === 'logic' ? 'Boolean (+50)' : (activeGame === 'water' ? 'FPB (+50)' : (activeGame === 'hanoi' ? 'Rekursi (+50)' : 'Logika (+50)')))"></span>
        </div>
    </div>

    <!-- Arena Permainan -->
    <div x-show="!isCollapsed" x-transition.opacity.duration.300ms class="challenge-arena-container p-2 sm:p-2.5 bg-gradient-to-b from-sky-50/70 via-indigo-50/40 to-emerald-50/50 border-t border-[#121316]/10">

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- 1. TANTANGAN PENYEIMBANG REAKSI KIMIA (CHEMICAL BALANCE LAB)   -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="activeGame === 'chemistry'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="w-full h-full flex flex-col">
            @include('forum.partials.challenge_chemical_balance')
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- 2. TANTANGAN SIRKUIT GERBANG LOGIKA IT (LOGIC GATES MASTER)     -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="activeGame === 'logic'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="w-full h-full flex flex-col">
            @include('forum.partials.challenge_logic_gates')
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- 2. TANTANGAN TABUNG AIR (WATER JUG RIDDLE - DIE HARD 3)         -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="activeGame === 'water'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="w-full h-full flex flex-col">
            @include('forum.partials.challenge_water_jug')
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- 2. TANTANGAN MENARA HANOI (TOWER OF HANOI)                      -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="activeGame === 'hanoi'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="w-full h-full flex flex-col">
            @include('forum.partials.challenge_hanoi')
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- 3. TANTANGAN MISIONARIS VS KANIBAL (RIVER CROSSING QUEST)       -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="activeGame === 'river'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="w-full h-full flex flex-col">

        <!-- Banner Cooldown (Jika Nyawa Habis) -->
        <div x-show="isCooldown" class="mb-2 p-2 sm:p-3 rounded-2xl bg-rose-50 border-2 border-rose-600 text-rose-900 shadow-[2px_2px_0px_#e11d48]">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">⏳</span>
                <div class="flex-1">
                    <h4 class="font-black text-xs sm:text-sm text-rose-950">Sesi Sedang Cooldown (Istirahat Berpikir)</h4>
                    <p class="text-[11px] text-rose-800 mt-0.5">3 Kesempatan Anda telah habis. Luangkan waktu untuk menganalisis strategi pemecahan masalah.</p>
                    <div class="mt-1 text-xs font-mono-code font-black text-rose-700">
                        Dapat mencoba kembali dalam: <span class="bg-rose-200 px-2 py-0.5 rounded-md text-rose-950" x-text="formatTime(cooldownSeconds)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Bar (Langkah, Waktu, Rekor) -->
        <div class="flex items-center justify-between gap-1.5 mb-1.5 p-1.5 sm:p-2 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316] shrink-0">
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap text-xs font-mono-code font-bold">
                <div class="flex items-center gap-1">
                    <span class="text-slate-500 text-[10px]">LANGKAH:</span>
                    <span class="px-1.5 py-0.2 rounded bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="moves"></span>
                    <span class="text-[9.5px] text-slate-500 hidden sm:inline">(Opt: 11)</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-slate-500 text-[10px]">WAKTU:</span>
                    <span class="px-1.5 py-0.2 rounded bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="formatTime(timerSeconds)"></span>
                </div>
                <template x-if="hasWonToday">
                    <span class="px-2 py-0.2 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-400 text-[8.5px] font-black">
                        ✓ Tuntas
                    </span>
                </template>
            </div>

            <div class="flex items-center gap-1.5">
                <button type="button" @click="resetCurrentSession()" :disabled="isCooldown || moves === 0" class="btn-tactile-white px-2 py-0.5 rounded-lg text-[11px] font-black disabled:opacity-40 cursor-pointer">
                    <i class="fa-solid fa-rotate-left mr-0.5 text-xs"></i> Ulang
                </button>
                <button type="button" @click="showRulesModal = true" class="p-1 px-1.5 rounded-lg bg-indigo-50 border border-indigo-300 text-indigo-900 text-xs font-black sm:hidden cursor-pointer">
                    <i class="fa-solid fa-circle-question"></i>
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- ARENA VISUAL SUNGAI & TEPI (RESPONSIVE PC & MOBILE OPTIMIZED) -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- SVG ASSETS DEFINITIONS: KARAKTER FISIK UTUH & PERAHU 3D       -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <svg class="absolute w-0 h-0 pointer-events-none" aria-hidden="true">
            <defs>
                <!-- 1. KARAKTER FISIK LENGKAP: MISIONARIS / ILMUWAN (THE SCHOLAR) -->
                <g id="full-scholar-figure">
                    <!-- Bayangan di Tanah -->
                    <ellipse cx="27" cy="82" rx="13" ry="3" fill="rgba(18,19,22,0.3)" />

                    <!-- Tongkat Petualang Kayu di Tangan Kiri -->
                    <line x1="9" y1="30" x2="7" y2="83" stroke="#78350f" stroke-width="2.5" stroke-linecap="round" />
                    <circle cx="9" cy="29" r="3" fill="#b45309" stroke="#451a03" stroke-width="1" />

                    <!-- Kaki & Celana Lapangan Safari Khaki -->
                    <!-- Kaki Kiri -->
                    <path d="M 19 52 L 18 74 L 24 74 L 24 52 Z" fill="#6b5b3e" stroke="#3b2f1e" stroke-width="1" />
                    <!-- Sepatu Bot Kiri -->
                    <path d="M 16 73 L 25 73 L 26 81 L 14 81 Z" fill="#3e2311" stroke="#1f1007" stroke-width="1" />
                    <line x1="14" y1="81" x2="26" y2="81" stroke="#121316" stroke-width="2" />

                    <!-- Kaki Kanan -->
                    <path d="M 28 52 L 28 74 L 34 74 L 34 52 Z" fill="#6b5b3e" stroke="#3b2f1e" stroke-width="1" />
                    <!-- Sepatu Bot Kanan -->
                    <path d="M 27 73 L 36 73 L 38 81 L 26 81 Z" fill="#3e2311" stroke="#1f1007" stroke-width="1" />
                    <line x1="26" y1="81" x2="38" y2="81" stroke="#121316" stroke-width="2" />

                    <!-- Tubuh: Kemeja & Rompi Safari Berkantung -->
                    <!-- Kemeja Dalam Putih Khaki -->
                    <path d="M 19 32 L 35 32 L 36 53 L 18 53 Z" fill="#fef9c3" stroke="#451a03" stroke-width="1" />
                    <!-- Dasi Kupu/Scarf Petualang Merah Marun -->
                    <path d="M 25 32 L 29 32 L 27 37 Z" fill="#b91c1c" />

                    <!-- Rompi Lapangan Khaki Terbuka -->
                    <path d="M 17 32 L 23 32 L 22 53 L 17 53 Z" fill="#bfa176" stroke="#451a03" stroke-width="1" />
                    <path d="M 31 32 L 37 32 L 37 53 L 32 53 Z" fill="#bfa176" stroke="#451a03" stroke-width="1" />
                    <!-- Saku Rompi Kiri & Kanan -->
                    <rect x="18" y="44" width="4" height="4" rx="0.5" fill="#a18258" stroke="#451a03" stroke-width="0.8" />
                    <rect x="32" y="44" width="4" height="4" rx="0.5" fill="#a18258" stroke="#451a03" stroke-width="0.8" />

                    <!-- Sabuk Kulit Cokelat & Gesper Kuningan -->
                    <rect x="17" y="50" width="20" height="3.5" fill="#451a03" />
                    <rect x="25" y="49.5" width="4" height="4.5" fill="#fbbf24" stroke="#451a03" stroke-width="0.8" />

                    <!-- Tali Selempang Jurnal Kulit -->
                    <line x1="20" y1="32" x2="35" y2="52" stroke="#451a03" stroke-width="2" />

                    <!-- Lengan & Tangan Memegang Buku Jurnal Lapangan -->
                    <!-- Lengan Kiri (memegang tongkat) -->
                    <path d="M 18 34 L 11 40 L 11 36 Z" fill="#bfa176" stroke="#451a03" stroke-width="0.8" />
                    <!-- Lengan Kanan (memegang jurnal di dada) -->
                    <path d="M 36 34 L 38 45 L 30 45 Z" fill="#bfa176" stroke="#451a03" stroke-width="0.8" />
                    <!-- Buku Jurnal Penelitian Kulit Cokelat/Merah -->
                    <rect x="26" y="40" width="8" height="9" rx="1" fill="#991b1b" stroke="#451a03" stroke-width="1" />
                    <line x1="28" y1="40" x2="28" y2="49" stroke="#fbbf24" stroke-width="1" />
                    <!-- Tangan Peneliti -->
                    <circle cx="32" cy="44" r="2.5" fill="#fed7aa" stroke="#451a03" stroke-width="0.8" />

                    <!-- Leher -->
                    <rect x="25" y="28" width="4" height="4" fill="#fed7aa" />

                    <!-- Kepala & Wajah -->
                    <ellipse cx="27" cy="22" rx="7.5" ry="8" fill="#fed7aa" stroke="#451a03" stroke-width="1" />
                    <!-- Kacamata Bulat Emas Ilmuwan -->
                    <circle cx="24" cy="21" r="3" fill="#e0f2fe" stroke="#b45309" stroke-width="1" />
                    <circle cx="30" cy="21" r="3" fill="#e0f2fe" stroke="#b45309" stroke-width="1" />
                    <line x1="27" y1="21" x2="27" y2="21" stroke="#b45309" stroke-width="1.5" />
                    <!-- Pupil Mata Pintar -->
                    <circle cx="24.5" cy="21" r="1.2" fill="#1e293b" />
                    <circle cx="30.5" cy="21" r="1.2" fill="#1e293b" />
                    <!-- Senyum Ramah & Hidung -->
                    <path d="M 27 22 L 28 24" stroke="#451a03" stroke-width="0.8" stroke-linecap="round" />
                    <path d="M 25 26 Q 27 28 29 26" stroke="#451a03" stroke-width="1" stroke-linecap="round" fill="none" />
                    <!-- Janggut Tipis Terpelajar -->
                    <path d="M 26 28 Q 27 30 28 28" fill="#78350f" />

                    <!-- Topi Safari Penjelajah (Pith Helmet) -->
                    <!-- Kubah Topi -->
                    <path d="M 19 18 Q 27 9 35 18 Z" fill="#eab308" stroke="#451a03" stroke-width="1.2" />
                    <!-- Pita Topi Cokelat Tua -->
                    <path d="M 18 17 Q 27 15 36 17 L 36 18 Q 27 16 18 18 Z" fill="#78350f" />
                    <!-- Pinggiran Topi Melengkung Lebar (Brim) -->
                    <ellipse cx="27" cy="18" rx="11" ry="3.5" fill="#facc15" stroke="#451a03" stroke-width="1.2" />
                    <!-- Puncak Topi Penjelajah -->
                    <circle cx="27" cy="10" r="1.5" fill="#b45309" />
                </g>

                <!-- 2. KARAKTER FISIK LENGKAP: PRAJURIT SUKU RIMBA / KANIBAL (THE TRIBAL WARRIOR) -->
                <g id="full-tribal-figure">
                    <!-- Bayangan di Tanah -->
                    <ellipse cx="27" cy="82" rx="13" ry="3" fill="rgba(18,19,22,0.3)" />

                    <!-- Tombak Bambu Rimba di Tangan Kanan -->
                    <!-- Batang Tombak Bambu -->
                    <line x1="42" y1="6" x2="40" y2="83" stroke="#854d0e" stroke-width="2.5" stroke-linecap="round" />
                    <!-- Ruas-ruas Bambu -->
                    <circle cx="41.5" cy="25" r="1.8" fill="#ca8a04" />
                    <circle cx="41" cy="45" r="1.8" fill="#ca8a04" />
                    <circle cx="40.5" cy="65" r="1.8" fill="#ca8a04" />
                    <!-- Mata Tombak Batu Obsidian Hitam Runcing -->
                    <path d="M 42 2 L 45 10 L 39 10 Z" fill="#1e293b" stroke="#0f172a" stroke-width="1" />
                    <!-- Ikatan Bulu Merah di Leher Tombak -->
                    <path d="M 39 10 Q 37 15 39 18 Q 42 14 42 10 Z" fill="#dc2626" />

                    <!-- Kaki Atletis Kulit Sawo Matang Rimba -->
                    <!-- Kaki Kiri -->
                    <path d="M 20 62 L 19 79 L 24 79 L 24 62 Z" fill="#a16207" stroke="#451a03" stroke-width="1" />
                    <!-- Gelang Kaki Serat Akar -->
                    <rect x="18" y="76" width="6" height="2" rx="1" fill="#ca8a04" stroke="#451a03" stroke-width="0.8" />
                    <!-- Kaki Kiri Telanjang -->
                    <path d="M 17 79 L 25 79 L 26 82 L 16 82 Z" fill="#a16207" stroke="#451a03" stroke-width="1" />

                    <!-- Kaki Kanan -->
                    <path d="M 28 62 L 28 79 L 33 79 L 33 62 Z" fill="#a16207" stroke="#451a03" stroke-width="1" />
                    <!-- Gelang Kaki Serat Akar -->
                    <rect x="27" y="76" width="6" height="2" rx="1" fill="#ca8a04" stroke="#451a03" stroke-width="0.8" />
                    <!-- Kaki Kanan Telanjang -->
                    <path d="M 27 79 L 35 79 L 36 82 L 26 82 Z" fill="#a16207" stroke="#451a03" stroke-width="1" />

                    <!-- Rok Rami Jerami / Dedaunan Rimba Hijau-Keemasan (Grass Skirt) -->
                    <!-- Rumbai-rumbai Daun Hijau & Emas -->
                    <path d="M 16 52 L 36 52 L 38 65 L 34 62 L 31 66 L 27 61 L 23 66 L 20 62 L 15 65 Z" fill="#65a30d" stroke="#365314" stroke-width="1" />
                    <path d="M 18 53 L 34 53 L 36 63 L 33 60 L 30 64 L 26 59 L 22 64 L 17 61 Z" fill="#eab308" opacity="0.8" />
                    <!-- Sabuk Anyaman Rotan Rimba -->
                    <rect x="16" y="50" width="20" height="3" rx="1" fill="#854d0e" stroke="#451a03" stroke-width="0.8" />

                    <!-- Tubuh Berotot Sawo Matang -->
                    <path d="M 18 33 L 34 33 L 33 51 L 19 51 Z" fill="#a16207" stroke="#451a03" stroke-width="1" />
                    <!-- Garis Otot Dada & Perut -->
                    <line x1="26" y1="34" x2="26" y2="48" stroke="#78350f" stroke-width="1" stroke-linecap="round" />
                    <path d="M 21 40 Q 26 42 31 40" stroke="#78350f" stroke-width="0.8" fill="none" />

                    <!-- Kalung Taring Runcing Melingkar di Leher -->
                    <path d="M 20 33 Q 26 38 32 33" stroke="#451a03" stroke-width="1" fill="none" />
                    <polygon points="21,34 22,38 20,38" fill="#f8fafc" stroke="#451a03" stroke-width="0.6" />
                    <polygon points="24,35 25,40 23,40" fill="#f8fafc" stroke="#451a03" stroke-width="0.6" />
                    <polygon points="27,36 28,41 26,41" fill="#f8fafc" stroke="#451a03" stroke-width="0.6" />
                    <polygon points="30,35 31,40 29,40" fill="#f8fafc" stroke="#451a03" stroke-width="0.6" />

                    <!-- Lengan & Tangan Memegang Tombak -->
                    <!-- Lengan Kiri (di pinggang) -->
                    <path d="M 18 35 L 14 43 L 18 45" stroke="#a16207" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="16" cy="38" r="2" fill="#ca8a04" />
                    <!-- Lengan Kanan (memegang tombak bambu) -->
                    <path d="M 34 35 L 40 40 L 41 43" stroke="#a16207" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="36" cy="38" r="2" fill="#ca8a04" />
                    <!-- Kepalan Tangan Menggenggam Tombak -->
                    <circle cx="41" cy="42" r="2.5" fill="#a16207" stroke="#451a03" stroke-width="0.8" />

                    <!-- Leher -->
                    <rect x="24" y="28" width="4" height="5" fill="#a16207" />

                    <!-- Kepala & Wajah Prajurit Rimba -->
                    <ellipse cx="26" cy="22" rx="7.5" ry="8" fill="#a16207" stroke="#451a03" stroke-width="1" />
                    <!-- Rambut Hitam Tebal Terikat -->
                    <path d="M 18 20 Q 26 12 34 20 L 34 16 Q 26 8 18 16 Z" fill="#0f172a" />
                    <circle cx="26" cy="11" r="3" fill="#0f172a" />

                    <!-- Ikat Kepala Kulit Rimba -->
                    <rect x="18" y="15" width="16" height="3" rx="0.5" fill="#dc2626" stroke="#451a03" stroke-width="0.8" />
                    <!-- 3 Bulu Burung Eksotis di Kepala -->
                    <!-- Bulu 1 (Merah Tengah) -->
                    <path d="M 26 15 Q 26 3 24 0 Q 29 4 27 15 Z" fill="#ef4444" stroke="#451a03" stroke-width="0.8" />
                    <!-- Bulu 2 (Emas Kiri) -->
                    <path d="M 23 15 Q 21 5 17 2 Q 23 6 24 15 Z" fill="#eab308" stroke="#451a03" stroke-width="0.8" />
                    <!-- Bulu 3 (Toska Kanan) -->
                    <path d="M 29 15 Q 31 5 35 2 Q 29 6 28 15 Z" fill="#06b6d4" stroke="#451a03" stroke-width="0.8" />

                    <!-- Mata Bersemangat & Hidup -->
                    <ellipse cx="23" cy="21" rx="2" ry="1.5" fill="#f8fafc" stroke="#451a03" stroke-width="0.8" />
                    <circle cx="23.5" cy="21" r="1" fill="#0f172a" />
                    <ellipse cx="29" cy="21" rx="2" ry="1.5" fill="#f8fafc" stroke="#451a03" stroke-width="0.8" />
                    <circle cx="28.5" cy="21" r="1" fill="#0f172a" />

                    <!-- Cat Perang Tribal Merah & Putih di Pipi -->
                    <path d="M 20 23 L 23 25" stroke="#ef4444" stroke-width="1.2" stroke-linecap="round" />
                    <path d="M 20 25 L 23 27" stroke="#ffffff" stroke-width="1.2" stroke-linecap="round" />
                    <path d="M 32 23 L 29 25" stroke="#ef4444" stroke-width="1.2" stroke-linecap="round" />
                    <path d="M 32 25 L 29 27" stroke="#ffffff" stroke-width="1.2" stroke-linecap="round" />

                    <!-- Mulut Tersenyum Gagah -->
                    <path d="M 24 26 Q 26 28 28 26" stroke="#451a03" stroke-width="1.2" stroke-linecap="round" fill="none" />
                </g>

                <!-- 3. GRADIEN WARNA PERAHU & AIR -->
                <linearGradient id="boatInnerWoodGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#1f0a02" />
                    <stop offset="100%" stop-color="#3d1504" />
                </linearGradient>
                <linearGradient id="boatPlankTopGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#f59e0b" />
                    <stop offset="25%" stop-color="#b45309" />
                    <stop offset="100%" stop-color="#78350f" />
                </linearGradient>
                <linearGradient id="boatPlankMidGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#92400e" />
                    <stop offset="100%" stop-color="#5a2206" />
                </linearGradient>
                <linearGradient id="boatPlankBtmGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#5a2206" />
                    <stop offset="100%" stop-color="#2a0d01" />
                </linearGradient>
                <linearGradient id="boatRimLightGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#fef08a" stop-opacity="0.9" />
                    <stop offset="50%" stop-color="#fbbf24" stop-opacity="1" />
                    <stop offset="100%" stop-color="#fef08a" stop-opacity="0.9" />
                </linearGradient>
                <linearGradient id="boatOarShaft" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#fef3c7" />
                    <stop offset="50%" stop-color="#d97706" />
                    <stop offset="100%" stop-color="#92400e" />
                </linearGradient>
                <linearGradient id="boatOarBlade" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#b45309" />
                    <stop offset="100%" stop-color="#78350f" />
                </linearGradient>
            </defs>
        </svg>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- ARENA PANORAMA SUNGAI (HORIZONTAL FULL-VIEW PC & MOBILE)      -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="relative w-full rounded-2xl border-2 border-[#121316] bg-gradient-to-b from-sky-200 via-sky-300 to-sky-400 overflow-hidden shadow-[3px_3px_0px_#121316] flex-1 min-h-0 flex flex-col">
            
            <div class="river-panorama-height relative w-full h-[175px] sm:h-[220px] flex justify-between overflow-hidden flex-1">
                
                <!-- 1. DARATAN TEPI BARAT (KIRI) -->
                <div class="w-[26%] sm:w-[22%] h-full bg-gradient-to-r from-emerald-100 via-emerald-50 to-amber-100/90 border-r-2 border-[#121316] relative flex flex-col justify-between p-1 sm:p-2 z-10 shadow-md">
                    <!-- Header Tepi Barat -->
                    <div class="flex items-center justify-between pb-0.5 border-b border-[#121316]/20 bg-white/70 backdrop-blur-xs px-1 sm:px-1.5 py-0.5 rounded-md border border-[#121316]/10">
                        <div class="flex items-center gap-0.5">
                            <span class="text-xs">🏝️</span>
                            <span class="font-black text-[9px] sm:text-[10px] text-[#121316] tracking-tight">BARAT</span>
                        </div>
                        <span class="text-[8px] sm:text-[8.5px] font-mono-code font-black px-1 py-0.2 rounded bg-white border border-[#121316]">
                            <span class="text-indigo-700">M:<span x-text="westM"></span></span>|<span class="text-amber-800">K:<span x-text="westC"></span></span>
                        </span>
                    </div>

                    <!-- Area Karakter Fisik Berdiri di Rumput Tepi Barat -->
                    <div class="my-auto w-full flex flex-col items-center justify-center py-0.5">
                        <p class="text-[7px] sm:text-[8px] font-mono-code font-bold text-slate-600 mb-0.5 uppercase tracking-wider text-center">
                            Ketuk naik:
                        </p>
                        
                        <!-- Grid Karakter Fisik Utuh: 3 Kolom Simetris (Maksimal 2 Baris) -->
                        <div class="grid grid-cols-3 gap-0.5 sm:gap-1 w-full max-w-[140px] mx-auto justify-items-center items-end">
                            <!-- Karakter Misionaris / Ilmuwan Barat -->
                            <template x-for="i in westM" :key="'wm-' + i">
                                <button type="button" 
                                        @click="boardBoat('M', 'west')"
                                        :disabled="boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="w-full flex flex-col items-center justify-end cursor-pointer group focus:outline-none transition-transform hover:-translate-y-1.5 active:translate-y-0 disabled:opacity-40 disabled:hover:translate-y-0"
                                        title="Misionaris (Ilmuwan) - Ketuk untuk naik perahu">
                                    <span class="text-[6px] font-mono-code font-black text-indigo-900 bg-white/95 border border-[#121316] px-0.5 rounded-xs shadow-2xs mb-0.5 group-hover:bg-[#fde047]">
                                        +NAIK
                                    </span>
                                    <svg viewBox="0 0 54 86" class="w-5 sm:w-7 h-8 sm:h-11 scholar-figure">
                                        <use href="#full-scholar-figure" xlink:href="#full-scholar-figure" />
                                    </svg>
                                </button>
                            </template>

                            <!-- Karakter Kanibal / Prajurit Rimba Barat -->
                            <template x-for="i in westC" :key="'wc-' + i">
                                <button type="button" 
                                        @click="boardBoat('C', 'west')"
                                        :disabled="boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="w-full flex flex-col items-center justify-end cursor-pointer group focus:outline-none transition-transform hover:-translate-y-1.5 active:translate-y-0 disabled:opacity-40 disabled:hover:translate-y-0"
                                        title="Kanibal (Prajurit Rimba) - Ketuk untuk naik perahu">
                                    <span class="text-[6px] font-mono-code font-black text-amber-900 bg-amber-100/95 border border-[#121316] px-0.5 rounded-xs shadow-2xs mb-0.5 group-hover:bg-[#fde047]">
                                        +NAIK
                                    </span>
                                    <svg viewBox="0 0 54 86" class="w-5 sm:w-7 h-8 sm:h-11 tribal-figure">
                                        <use href="#full-tribal-figure" xlink:href="#full-tribal-figure" />
                                    </svg>
                                </button>
                            </template>
                        </div>

                        <!-- Jika Kosong -->
                        <template x-if="westM === 0 && westC === 0">
                            <div class="text-center py-2 text-[8.5px] font-mono-code text-slate-500 font-bold italic">
                                Kosong ✓
                            </div>
                        </template>
                    </div>

                    <!-- Dermaga Kayu Barat Menjorok ke Air -->
                    <div class="absolute -right-2 top-1/2 -translate-y-1/2 w-3 h-14 sm:h-18 bg-amber-800 border-2 border-[#121316] rounded-r-lg z-20 shadow-md flex flex-col justify-around py-0.5">
                        <div class="w-full h-0.5 bg-amber-950"></div>
                        <div class="w-full h-0.5 bg-amber-950"></div>
                        <div class="w-full h-0.5 bg-amber-950"></div>
                    </div>

                    <div class="text-[7px] font-mono-code font-bold text-slate-600 text-center">
                        Daratan Awal
                    </div>
                </div>

                <!-- 2. BENTANGAN SUNGAI LUAS & PERAHU BERGERAK (TENGAH) -->
                <div class="flex-1 h-full relative overflow-hidden bg-gradient-to-r from-sky-400 via-cyan-400 to-sky-400 flex flex-col justify-between p-1 sm:p-2 river-current-flow">
                    
                    <!-- Status Lokasi Perahu -->
                    <div class="w-full flex items-center justify-between z-10">
                        <div class="text-[7.5px] sm:text-[9px] font-mono-code font-black px-1.5 py-0.2 rounded-full bg-[#121316] text-white shadow-xs">
                            PERAHU: <span class="text-yellow-300" x-text="boatPosition === 'west' ? 'BARAT ◄' : '► TIMUR'"></span>
                        </div>
                        <div class="text-[7px] sm:text-[7.5px] font-mono-code font-black px-1 py-0.2 rounded-full bg-white/80 border border-[#121316] text-[#121316]">
                            KAPASITAS: <span x-text="boatPassengers.length + '/2'"></span>
                        </div>
                    </div>

                    <!-- Lintasan Perahu Berlayar Melintasi Sungai yang Luas -->
                    <div class="my-auto w-full relative h-22 sm:h-26 flex items-center z-15">
                        
                        <!-- Wrapper Perahu Bergerak (Menyeberang Jarak Luas dari Barat ke Timur) -->
                        <div class="w-full flex transition-all duration-1000 ease-in-out px-1 sm:px-3"
                             :class="boatPosition === 'west' ? 'justify-start' : 'justify-end'">
                            
                            <!-- PERAHU SAMPAN KAYU 3D & PENUMPANG FISIK DI DALAMNYA -->
                            <div class="w-40 sm:w-50 relative select-none transition-transform"
                                 :class="isMoving ? 'boat-rowing scale-105' : 'boat-idle'">
                                
                                <!-- LAYER 1: INNER HULL (DINDING DALAM, LANTAI, BANGKU DUDUK, & DAYUNG KAYU) -->
                                <svg viewBox="0 0 280 115" class="w-full h-auto block filter drop-shadow-sm pointer-events-none" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M 12 36 Q 140 48 268 36 L 265 43 Q 140 56 15 43 Z" fill="#2d1204" />
                                    <path d="M 15 43 Q 140 56 265 43 L 248 68 Q 140 78 32 68 Z" fill="url(#boatInnerWoodGrad)" />
                                    <line x1="75" y1="48" x2="75" y2="72" stroke="#150601" stroke-width="2.5" stroke-linecap="round" />
                                    <line x1="140" y1="52" x2="140" y2="76" stroke="#150601" stroke-width="2.5" stroke-linecap="round" />
                                    <line x1="205" y1="48" x2="205" y2="72" stroke="#150601" stroke-width="2.5" stroke-linecap="round" />
                                    <rect x="52" y="50" width="60" height="7" rx="2" fill="#78350f" stroke="#451a03" stroke-width="1.2" />
                                    <line x1="54" y1="52" x2="110" y2="52" stroke="#b45309" stroke-width="1" />
                                    <rect x="168" y="50" width="60" height="7" rx="2" fill="#78350f" stroke="#451a03" stroke-width="1.2" />
                                    <line x1="170" y1="52" x2="226" y2="52" stroke="#b45309" stroke-width="1" />
                                    <g class="oar-assembly" :class="isMoving ? 'oar-active' : ''">
                                        <line x1="142" y1="36" x2="262" y2="92" stroke="url(#boatOarShaft)" stroke-width="4.5" stroke-linecap="round" />
                                        <ellipse cx="264" cy="94" rx="15" ry="7" transform="rotate(32 264 94)" fill="url(#boatOarBlade)" stroke="#451a03" stroke-width="1.2" />
                                        <circle cx="218" cy="68" r="3.5" fill="#f59e0b" stroke="#78350f" stroke-width="1.5" />
                                    </g>
                                </svg>

                                <!-- LAYER 2: PENUMPANG DUDUK DI DALAM PERAHU -->
                                <div class="absolute inset-0 z-20 pointer-events-none">
                                    
                                    <!-- Kursi 1 (Kiri / Depan - Duduk di Bangku 1) -->
                                    <div style="position: absolute; left: 16%; width: 30%; top: 0; bottom: 30px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;" class="pointer-events-auto">
                                        <template x-if="boatPassengers.length > 0">
                                            <button type="button" 
                                                    @click="unboardBoat(0)"
                                                    :disabled="isMoving || isCooldown"
                                                    class="group/pax relative flex flex-col items-center justify-end w-full cursor-pointer focus:outline-none transition-transform hover:-translate-y-1.5 active:translate-y-0"
                                                    title="Ketuk karakter untuk turun ke daratan">
                                                <div class="mb-0.5 px-1 py-0.2 rounded-full text-[6.5px] font-mono-code font-black border border-[#121316] shadow-2xs transition-all flex items-center gap-0.5"
                                                     :class="boatPassengers[0] === 'M' ? 'bg-indigo-100 text-indigo-900 group-hover/pax:bg-rose-500 group-hover/pax:text-white' : 'bg-amber-100 text-amber-900 group-hover/pax:bg-rose-500 group-hover/pax:text-white'">
                                                    <span class="group-hover/pax:hidden" x-text="boatPassengers[0] === 'M' ? 'ILMUWAN' : 'KANIBAL'"></span>
                                                    <span class="hidden group-hover/pax:inline font-bold">✕ TURUN</span>
                                                </div>
                                                <div class="transform group-hover/pax:scale-105 transition-transform flex items-center justify-center">
                                                    <template x-if="boatPassengers[0] === 'M'">
                                                        <svg viewBox="0 0 54 86" class="w-8 sm:w-11 h-13 sm:h-18 scholar-figure">
                                                            <use href="#full-scholar-figure" xlink:href="#full-scholar-figure" />
                                                        </svg>
                                                    </template>
                                                    <template x-if="boatPassengers[0] === 'C'">
                                                        <svg viewBox="0 0 54 86" class="w-8 sm:w-11 h-13 sm:h-18 tribal-figure">
                                                            <use href="#full-tribal-figure" xlink:href="#full-tribal-figure" />
                                                        </svg>
                                                    </template>
                                                </div>
                                            </button>
                                        </template>

                                        <template x-if="boatPassengers.length === 0">
                                            <div class="flex flex-col items-center justify-end w-full opacity-70 pb-0.5 select-none pointer-events-none">
                                                <span class="text-[7px] font-mono-code font-bold text-amber-900 bg-amber-200/90 px-1 py-0.2 rounded border border-amber-600/40 shadow-xs">
                                                    Kursi 1
                                                </span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Kursi 2 (Kanan / Belakang - Duduk di Bangku 2) -->
                                    <div style="position: absolute; left: 54%; width: 30%; top: 0; bottom: 30px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;" class="pointer-events-auto">
                                        <template x-if="boatPassengers.length > 1">
                                            <button type="button" 
                                                    @click="unboardBoat(1)"
                                                    :disabled="isMoving || isCooldown"
                                                    class="group/pax relative flex flex-col items-center justify-end w-full cursor-pointer focus:outline-none transition-transform hover:-translate-y-1.5 active:translate-y-0"
                                                    title="Ketuk karakter untuk turun ke daratan">
                                                <div class="mb-0.5 px-1 py-0.2 rounded-full text-[6.5px] font-mono-code font-black border border-[#121316] shadow-2xs transition-all flex items-center gap-0.5"
                                                     :class="boatPassengers[1] === 'M' ? 'bg-indigo-100 text-indigo-900 group-hover/pax:bg-rose-500 group-hover/pax:text-white' : 'bg-amber-100 text-amber-900 group-hover/pax:bg-rose-500 group-hover/pax:text-white'">
                                                    <span class="group-hover/pax:hidden" x-text="boatPassengers[1] === 'M' ? 'ILMUWAN' : 'KANIBAL'"></span>
                                                    <span class="hidden group-hover/pax:inline font-bold">✕ TURUN</span>
                                                </div>
                                                <div class="transform group-hover/pax:scale-105 transition-transform flex items-center justify-center">
                                                    <template x-if="boatPassengers[1] === 'M'">
                                                        <svg viewBox="0 0 54 86" class="w-8 sm:w-11 h-13 sm:h-18 scholar-figure">
                                                            <use href="#full-scholar-figure" xlink:href="#full-scholar-figure" />
                                                        </svg>
                                                    </template>
                                                    <template x-if="boatPassengers[1] === 'C'">
                                                        <svg viewBox="0 0 54 86" class="w-8 sm:w-11 h-13 sm:h-18 tribal-figure">
                                                            <use href="#full-tribal-figure" xlink:href="#full-tribal-figure" />
                                                        </svg>
                                                    </template>
                                                </div>
                                            </button>
                                        </template>

                                        <template x-if="boatPassengers.length <= 1">
                                            <div class="flex flex-col items-center justify-end w-full opacity-70 pb-0.5 select-none pointer-events-none">
                                                <span class="text-[7px] font-mono-code font-bold text-amber-900 bg-amber-200/90 px-1 py-0.2 rounded border border-amber-600/40 shadow-xs">
                                                    Kursi 2
                                                </span>
                                            </div>
                                        </template>
                                    </div>

                                </div>

                                <!-- LAYER 3: OUTER HULL (LAMBUNG LUAR KAYU SAMPAN 3D) -->
                                <svg viewBox="0 0 280 115" class="w-full h-auto absolute inset-0 z-30 pointer-events-none" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M 8 32 Q 140 50 272 32 L 270 38 Q 140 56 10 38 Z" fill="url(#boatPlankTopGrad)" stroke="#451a03" stroke-width="1.2" />
                                    <path d="M 12 33 Q 140 49 268 33" stroke="url(#boatRimLightGrad)" stroke-width="2" stroke-linecap="round" fill="none" />
                                    <path d="M 10 38 Q 140 56 270 38 L 262 52 Q 140 69 18 52 Z" fill="url(#boatPlankTopGrad)" stroke="#451a03" stroke-width="1" />
                                    <circle cx="35" cy="43" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="70" cy="48" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="105" cy="53" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="140" cy="55" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="175" cy="53" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="210" cy="48" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="245" cy="43" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <path d="M 18 52 Q 140 69 262 52 L 250 67 Q 140 83 30 67 Z" fill="url(#boatPlankMidGrad)" stroke="#3b1302" stroke-width="1" />
                                    <path d="M 30 67 Q 140 83 250 67 L 234 79 Q 140 93 46 79 Z" fill="url(#boatPlankBtmGrad)" stroke="#1f0a02" stroke-width="1.2" />
                                    <path d="M 8 32 C 4 21 3 13 10 9 C 15 11 13 23 10 38 Z" fill="#78350f" stroke="#270f03" stroke-width="1.2" />
                                    <circle cx="10" cy="18" r="2.8" fill="none" stroke="#fef08a" stroke-width="1.5" />
                                    <path d="M 272 32 C 276 21 277 13 270 9 C 265 11 267 23 270 38 Z" fill="#78350f" stroke="#270f03" stroke-width="1.2" />
                                    <circle cx="270" cy="18" r="2.8" fill="none" stroke="#fef08a" stroke-width="1.5" />
                                </svg>

                                <!-- LAYER 4: RIAK AIR & OMBAK GELOMBANG SUNGAI -->
                                <svg viewBox="0 0 280 115" class="w-full h-auto absolute inset-0 z-10 pointer-events-none" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <ellipse cx="140" cy="91" rx="108" ry="8" fill="rgba(3, 105, 161, 0.45)" />
                                    <path d="M 36 86 Q 140 98 244 86" stroke="rgba(255,255,255,0.85)" stroke-width="2.5" stroke-linecap="round" fill="none" class="water-wake-anim" />
                                    <path d="M 62 93 Q 140 103 218 93" stroke="rgba(255,255,255,0.5)" stroke-width="1.8" stroke-linecap="round" fill="none" class="water-wake-anim" style="animation-delay: 0.5s;" />
                                </svg>

                            </div>
                        </div>

                    </div>

                    <!-- Keterangan Arus Sungai -->
                    <div class="text-[8px] font-mono-code font-bold text-slate-800 z-10 text-center flex items-center justify-center gap-1.5">
                        <span>🌊 Arus Sungai Menyeberang</span>
                        <span>•</span>
                        <span>Maks 2 Orang</span>
                    </div>
                </div>

                <!-- 3. DARATAN TEPI TIMUR (KANAN - TUJUAN) -->
                <div class="w-[26%] sm:w-[22%] h-full bg-gradient-to-l from-emerald-100 via-emerald-50 to-amber-100/90 border-l-2 border-[#121316] relative flex flex-col justify-between p-1 sm:p-2 z-10 shadow-md">
                    <!-- Header Tepi Timur -->
                    <div class="flex items-center justify-between pb-0.5 border-b border-[#121316]/20 bg-white/70 backdrop-blur-xs px-1 sm:px-1.5 py-0.5 rounded-md border border-[#121316]/10">
                        <div class="flex items-center gap-0.5">
                            <span class="text-xs">🏁</span>
                            <span class="font-black text-[9px] sm:text-[10px] text-[#121316] tracking-tight">TIMUR</span>
                        </div>
                        <span class="text-[8px] sm:text-[8.5px] font-mono-code font-black px-1 py-0.2 rounded bg-white border border-[#121316]">
                            <span class="text-indigo-700">M:<span x-text="eastM"></span></span>|<span class="text-amber-800">K:<span x-text="eastC"></span></span>
                        </span>
                    </div>

                    <!-- Area Karakter Fisik yang Telah Selamat Sampai di Timur -->
                    <div class="my-auto w-full flex flex-col items-center justify-center py-0.5">
                        <p class="text-[7px] sm:text-[8px] font-mono-code font-bold text-slate-600 mb-0.5 uppercase tracking-wider text-center">
                            Selamat Sampai:
                        </p>
                        
                        <!-- Grid Karakter Fisik Selamat: 3 Kolom Simetris (Maksimal 2 Baris) -->
                        <div class="grid grid-cols-3 gap-0.5 sm:gap-1 w-full max-w-[140px] mx-auto justify-items-center items-end">
                            <!-- Karakter Misionaris / Ilmuwan Timur -->
                            <template x-for="i in eastM" :key="'em-' + i">
                                <button type="button" 
                                        @click="boardBoat('M', 'east')"
                                        :disabled="boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="w-full flex flex-col items-center justify-end cursor-pointer group focus:outline-none transition-transform hover:-translate-y-1.5 active:translate-y-0 disabled:opacity-40 disabled:hover:translate-y-0"
                                        title="Kembalikan Ilmuwan ke perahu">
                                    <span class="text-[6px] font-mono-code font-black text-indigo-900 bg-white/95 border border-[#121316] px-0.5 rounded-xs shadow-2xs mb-0.5 group-hover:bg-rose-400 group-hover:text-white">
                                        NAIK
                                    </span>
                                    <svg viewBox="0 0 54 86" class="w-5 sm:w-7 h-8 sm:h-11 scholar-figure">
                                        <use href="#full-scholar-figure" xlink:href="#full-scholar-figure" />
                                    </svg>
                                </button>
                            </template>

                            <!-- Karakter Kanibal / Prajurit Rimba Timur -->
                            <template x-for="i in eastC" :key="'ec-' + i">
                                <button type="button" 
                                        @click="boardBoat('C', 'east')"
                                        :disabled="boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="w-full flex flex-col items-center justify-end cursor-pointer group focus:outline-none transition-transform hover:-translate-y-1.5 active:translate-y-0 disabled:opacity-40 disabled:hover:translate-y-0"
                                        title="Kembalikan Kanibal ke perahu">
                                    <span class="text-[6px] font-mono-code font-black text-amber-900 bg-amber-100/95 border border-[#121316] px-0.5 rounded-xs shadow-2xs mb-0.5 group-hover:bg-rose-400 group-hover:text-white">
                                        NAIK
                                    </span>
                                    <svg viewBox="0 0 54 86" class="w-5 sm:w-7 h-8 sm:h-11 tribal-figure">
                                        <use href="#full-tribal-figure" xlink:href="#full-tribal-figure" />
                                    </svg>
                                </button>
                            </template>
                        </div>

                        <!-- Jika Belum Ada -->
                        <template x-if="eastM === 0 && eastC === 0">
                            <div class="text-center py-2 text-[8.5px] font-mono-code text-slate-500 font-bold italic">
                                Belum ada ✓
                            </div>
                        </template>
                    </div>

                    <!-- Dermaga Kayu Timur Menjorok ke Air -->
                    <div class="absolute -left-2 top-1/2 -translate-y-1/2 w-3 h-14 sm:h-18 bg-amber-800 border-2 border-[#121316] rounded-l-lg z-20 shadow-md flex flex-col justify-around py-0.5">
                        <div class="w-full h-0.5 bg-amber-950"></div>
                        <div class="w-full h-0.5 bg-amber-950"></div>
                        <div class="w-full h-0.5 bg-amber-950"></div>
                    </div>

                    <div class="text-[7px] font-mono-code font-bold text-slate-600 text-center">
                        Tujuan 🏁
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- CONTROL HUB PERMAINAN: 3 KOLOM SEJAJAR (TIDAK BERTUMPUK)      -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="mt-1 sm:mt-1.5 grid grid-cols-3 gap-1 sm:gap-1.5 w-full items-stretch shrink-0">
            
            <!-- 1. KONTROL CEPAT TEPI BARAT (KIRI) -->
            <div class="p-1 sm:p-1.5 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316] flex flex-col justify-between">
                <div class="flex items-center justify-between text-[10px] font-mono-code font-black mb-0.5 pb-0.5 border-b border-[#121316]/10">
                    <span class="text-[#121316] truncate">🏝️ BARAT</span>
                    <span class="text-indigo-700 bg-indigo-50 px-1 py-0.2 rounded border border-indigo-200 text-[8.5px]" x-text="'M:' + westM + '|K:' + westC"></span>
                </div>
                <div class="flex gap-0.5 sm:gap-1">
                    <button type="button" 
                            @click="boardBoat('M', 'west')" 
                            :disabled="westM === 0 || boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving || isCooldown"
                            class="flex-1 py-1 px-0.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-950 border border-[#121316] rounded-lg text-[9px] sm:text-[10px] font-black transition disabled:opacity-40 cursor-pointer text-center"
                            title="Naikkan 1 Ilmuwan dari Tepi Barat">
                        +M
                    </button>
                    <button type="button" 
                            @click="boardBoat('C', 'west')" 
                            :disabled="westC === 0 || boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving || isCooldown"
                            class="flex-1 py-1 px-0.5 bg-amber-100 hover:bg-amber-200 text-amber-950 border border-[#121316] rounded-lg text-[9px] sm:text-[10px] font-black transition disabled:opacity-40 cursor-pointer text-center"
                            title="Naikkan 1 Kanibal dari Tepi Barat">
                        +K
                    </button>
                </div>
            </div>

            <!-- 2. KONTROL AKSI PERAHU & MENYEBERANG SUNGAI (TENGAH) -->
            <div class="p-1 sm:p-1.5 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316] flex flex-col items-center justify-center gap-0.5 sm:gap-1 min-w-0">
                <div class="flex items-center gap-1 w-full justify-center">
                    <!-- Tombol Dayung Utama -->
                    <button type="button" 
                            @click="rowBoat()"
                            :disabled="boatPassengers.length === 0 || isMoving || isCooldown"
                            class="flex-1 py-1 sm:py-1.5 px-1.5 sm:px-2 rounded-xl text-[11px] sm:text-xs font-black border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition-all flex items-center justify-center gap-1 cursor-pointer"
                            :class="boatPassengers.length > 0 && !isMoving && !isCooldown ? 'bg-[#fde047] hover:bg-yellow-300 text-[#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none' : 'bg-slate-200 text-slate-400 border-slate-400 cursor-not-allowed shadow-none'">
                        <span>⛵</span>
                        <span class="truncate" x-text="isMoving ? 'Menyeberang...' : (boatPosition === 'west' ? 'DAYUNG ►' : '◄ DAYUNG')"></span>
                    </button>

                    <!-- Tombol Praktis: Turunkan Semua Penumpang -->
                    <button type="button" 
                            @click="unboardAll()"
                            :disabled="boatPassengers.length === 0 || isMoving || isCooldown"
                            class="py-1 sm:py-1.5 px-1.5 sm:px-2 bg-rose-50 hover:bg-rose-100 text-rose-900 border-2 border-[#121316] rounded-xl text-[10px] sm:text-[11px] font-black shadow-[2px_2px_0px_#121316] transition disabled:opacity-30 disabled:shadow-none shrink-0 cursor-pointer"
                            title="Turunkan seluruh penumpang">
                        <span>Turun</span>
                    </button>
                </div>

                <div class="text-[7.5px] sm:text-[8.5px] font-mono-code font-bold text-slate-700 flex items-center gap-1 text-center truncate">
                    <span>Isi: (<b class="text-[#121316]" x-text="boatPassengers.length"></b>/2)</span>
                    <span>•</span>
                    <span class="text-amber-800 font-black truncate" x-text="boatPosition === 'west' ? 'Di Barat' : 'Di Timur'"></span>
                </div>
            </div>

            <!-- 3. KONTROL CEPAT TEPI TIMUR (KANAN) -->
            <div class="p-1 sm:p-1.5 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316] flex flex-col justify-between">
                <div class="flex items-center justify-between text-[10px] font-mono-code font-black mb-0.5 pb-0.5 border-b border-[#121316]/10">
                    <span class="text-[#121316] truncate">🏁 TIMUR</span>
                    <span class="text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200 text-[8.5px]" x-text="'M:' + eastM + '|K:' + eastC"></span>
                </div>
                <div class="flex gap-0.5 sm:gap-1">
                    <button type="button" 
                            @click="boardBoat('M', 'east')" 
                            :disabled="eastM === 0 || boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving || isCooldown"
                            class="flex-1 py-1 px-0.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-950 border border-[#121316] rounded-lg text-[9px] sm:text-[10px] font-black transition disabled:opacity-40 cursor-pointer text-center"
                            title="Naikkan kembali 1 Ilmuwan ke perahu">
                        +M
                    </button>
                    <button type="button" 
                            @click="boardBoat('C', 'east')" 
                            :disabled="eastC === 0 || boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving || isCooldown"
                            class="flex-1 py-1 px-0.5 bg-amber-100 hover:bg-amber-200 text-amber-950 border border-[#121316] rounded-lg text-[9px] sm:text-[10px] font-black transition disabled:opacity-40 cursor-pointer text-center"
                            title="Naikkan kembali 1 Kanibal ke perahu">
                        +K
                    </button>
                </div>
            </div>

        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- MODAL BAHAYA / PELANGGARAN ATURAN (KANIBAL MEMANGSA MISIONARIS) -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="dangerAlert.show" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
            <div class="bg-white border-2 border-[#121316] rounded-3xl w-full max-w-sm overflow-hidden shadow-[8px_8px_0px_#e11d48] animate-bounce">
                <div class="p-4 bg-rose-500 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">⚠️</span>
                        <h4 class="font-black text-sm uppercase">Kondisi Bahaya!</h4>
                    </div>
                    <span class="font-mono-code font-black text-xs bg-rose-700 px-2 py-0.5 rounded-full">-1 NYAWA</span>
                </div>
                <div class="p-5 text-center space-y-3">
                    <p class="text-sm font-extrabold text-[#121316] leading-relaxed" x-text="dangerAlert.message"></p>
                    <p class="text-xs text-slate-500 font-mono-code">
                        Kanibal memangsa Misionaris karena jumlah Kanibal lebih banyak di tepi tersebut!
                    </p>
                    <div class="pt-2">
                        <button type="button" @click="closeDangerModal()" class="btn-tactile-red w-full py-2.5 rounded-xl text-xs font-black">
                            Saya Mengerti, Ulangi Langkah ↩
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- MODAL KEMENANGAN (VICTORY & REPUTATION REWARD CLAIM)          -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="victoryModal.show" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
            <div class="bg-white border-2 border-[#121316] rounded-3xl w-full max-w-md overflow-hidden shadow-[8px_8px_0px_#10b981]">
                <div class="p-4 bg-gradient-to-r from-emerald-400 to-teal-400 text-[#121316] flex items-center justify-between border-b-2 border-[#121316]">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🏆</span>
                        <h4 class="font-black text-base uppercase">Tantangan Logika Terpecahkan!</h4>
                    </div>
                    <span class="text-xs font-mono-code font-black px-2.5 py-1 rounded-full bg-[#121316] text-[#fde047]">
                        SUKSES
                    </span>
                </div>

                <div class="p-6 text-center space-y-4">
                    <div class="text-4xl animate-bounce">
                        <span x-text="victoryModal.stars === 3 ? '⭐⭐⭐' : (victoryModal.stars === 2 ? '⭐⭐' : '⭐')"></span>
                    </div>

                    <div>
                        <h3 class="font-black text-lg text-[#121316]" x-text="victoryModal.rankTitle"></h3>
                        <p class="text-xs text-slate-600 mt-1" x-text="victoryModal.message"></p>
                    </div>

                    <!-- Statistik Skor -->
                    <div class="grid grid-cols-3 gap-2 p-3 rounded-2xl bg-[#faf8f5] border-2 border-[#121316] font-mono-code">
                        <div>
                            <span class="text-[10px] text-slate-500 block">LANGKAH</span>
                            <span class="font-black text-sm text-[#121316]" x-text="moves"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block">WAKTU</span>
                            <span class="font-black text-sm text-[#121316]" x-text="formatTime(timerSeconds)"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block">REPUTASI</span>
                            <span class="font-black text-sm text-emerald-600" x-text="'+' + victoryModal.points"></span>
                        </div>
                    </div>

                    <div class="pt-2 flex gap-2">
                        <button type="button" @click="victoryModal.show = false; resetGameFull();" class="btn-tactile-yellow flex-1 py-2.5 rounded-xl text-xs font-black">
                            Mainkan Lagi ↻
                        </button>
                        <button type="button" @click="victoryModal.show = false;" class="btn-tactile-white flex-1 py-2.5 rounded-xl text-xs font-black">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- MODAL ATURAN PERMAINAN                                        -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="showRulesModal" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showRulesModal = false" class="bg-white border-2 border-[#121316] rounded-3xl w-full max-w-md overflow-hidden shadow-[8px_8px_0px_#121316]">
                <div class="p-4 bg-[#fde047] border-b-2 border-[#121316] flex items-center justify-between">
                    <h3 class="font-black text-sm text-[#121316] flex items-center gap-2">
                        <span>📜</span> Aturan Permainan Misionaris vs Kanibal
                    </h3>
                    <button @click="showRulesModal = false" class="w-8 h-8 rounded-xl bg-white border-2 border-[#121316] text-[#121316] flex items-center justify-center font-bold">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="p-5 space-y-3 text-xs text-[#121316] leading-relaxed">
                    <div class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200">
                        <span class="font-black text-indigo-900 block mb-1">🎯 Tujuan Tantangan:</span>
                        Seberangkan 3 Misionaris (Ilmuwan) dan 3 Kanibal dari Tepi Barat ke Tepi Timur.
                    </div>
                    <ul class="space-y-1.5 list-disc pl-4 font-medium text-slate-700">
                        <li>Perahu hanya muat <strong>maksimal 2 orang</strong> dan minimal 1 orang sebagai pendayung.</li>
                        <li><strong>Kondisi Bahaya:</strong> Di tepi sungai mana pun, jika jumlah Kanibal lebih banyak daripada jumlah Misionaris, Misionaris akan dimangsa!</li>
                        <li>Anda dibekali <strong>3 Nyawa (❤️❤️❤️)</strong>. Jika gagal 3x, Anda harus istirahat 15 menit.</li>
                        <li><strong>Penilaian Poin:</strong>
                            <br>• 11 - 13 Langkah: ⭐⭐⭐ (+50 Poin)
                            <br>• 14 - 17 Langkah: ⭐⭐ (+35 Poin)
                            <br>• 18+ Langkah: ⭐ (+20 Poin)
                        </li>
                    </ul>
                    <div class="pt-2">
                        <button type="button" @click="showRulesModal = false" class="btn-tactile-red w-full py-2.5 rounded-xl text-xs font-black">
                            Mulai Bermain! 🚀
                        </button>
                    </div>
                </div>
            </div>
        </div>

        </div> <!-- Akhir wrapper activeGame === 'river' -->

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- GAYA KHUSUS & ANIMASI PERAHU REALISTIS & GERAKAN GELOMBANG AIR        -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<style>
/* ── Neo-Brutalist Tactile Styles & Fonts ── */
.forum-hdr { font-family: 'Plus Jakarta Sans', sans-serif; }
.font-mono-code { font-family: 'JetBrains Mono', monospace, ui-monospace; }

.btn-tactile-white {
    background: #ffffff;
    color: #121316;
    border: 2px solid #121316;
    box-shadow: 3px 3px 0px #121316;
    transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}
.btn-tactile-white:hover {
    background: #faf8f5;
    transform: translate(1px, 1px);
    box-shadow: 2px 2px 0px #121316;
}
.btn-tactile-white:active {
    transform: translate(3px, 3px);
    box-shadow: 0px 0px 0px #121316;
}

.btn-tactile-yellow {
    background: #fde047;
    color: #121316;
    border: 2px solid #121316;
    box-shadow: 3px 3px 0px #121316;
    transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}
.btn-tactile-yellow:hover {
    background: #fef08a;
    transform: translate(1px, 1px);
    box-shadow: 2px 2px 0px #121316;
}
.btn-tactile-yellow:active {
    transform: translate(3px, 3px);
    box-shadow: 0px 0px 0px #121316;
}

/* ── MODE FULLSCREEN LANDSCAPE 1-SCREEN FIT KHUSUS HP (ANTI TERPOTONG / ZERO SCROLL) ── */
#pembda-challenge.mobile-landscape-active,
#pembda-challenge.mobile-physical-landscape {
    position: fixed !important;
    inset: 0 !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    max-height: 100vh !important;
    z-index: 999999 !important;
    overflow: hidden !important;
    border-radius: 0 !important;
    border: none !important;
    margin: 0 !important;
    padding: 0 !important;
    background: #ffffff !important;
    box-shadow: none !important;
    display: flex !important;
    flex-direction: column !important;
}

@media (max-height: 520px) and (orientation: landscape) {
    #pembda-challenge {
        position: fixed !important;
        inset: 0 !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        max-height: 100vh !important;
        z-index: 999999 !important;
        overflow: hidden !important;
        border-radius: 0 !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        box-shadow: none !important;
        display: flex !important;
        flex-direction: column !important;
    }
}

/* Utilitas sembunyikan dekorasi / penjelasan non-esensial saat di Landscape Mode */
#pembda-challenge.mobile-landscape-active .hide-in-landscape,
#pembda-challenge.mobile-physical-landscape .hide-in-landscape {
    display: none !important;
}
@media (max-height: 520px) and (orientation: landscape) {
    #pembda-challenge .hide-in-landscape {
        display: none !important;
    }
    #pembda-challenge .river-panorama-height {
        height: 120px !important;
        min-height: 120px !important;
    }
}
#pembda-challenge.mobile-landscape-active .river-panorama-height,
#pembda-challenge.mobile-physical-landscape .river-panorama-height {
    height: 120px !important;
    min-height: 120px !important;
}

/* Animasi Mengapung Halus Perahu di Air (Gentle Idle Floating) */
@keyframes boatGentleBob {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    30% {
        transform: translateY(-3.5px) rotate(-0.8deg);
    }
    60% {
        transform: translateY(1.5px) rotate(0.6deg);
    }
    85% {
        transform: translateY(-1.5px) rotate(0.9deg);
    }
}

/* Animasi Mendayung saat Perahu Bergerak Menyeberang */
@keyframes boatRowingMotion {
    0%, 100% {
        transform: translateY(0px) rotate(0deg) scale(1.02);
    }
    25% {
        transform: translateY(-5px) rotate(-2deg) scale(1.04);
    }
    50% {
        transform: translateY(2px) rotate(1.5deg) scale(1.03);
    }
    75% {
        transform: translateY(-4px) rotate(-1deg) scale(1.04);
    }
}

/* Animasi Ayunan Dayung Kayu saat Berlayar */
@keyframes oarRowingCycle {
    0%, 100% {
        transform: rotate(0deg);
        transform-origin: 55% 45%;
    }
    50% {
        transform: rotate(-16deg) translateY(-2px);
        transform-origin: 55% 45%;
    }
}

/* Animasi Riak Air Sungai Bawah Perahu */
@keyframes waterWakePulsing {
    0%, 100% {
        transform: scaleX(0.96) scaleY(0.9);
        opacity: 0.6;
    }
    50% {
        transform: scaleX(1.08) scaleY(1.25);
        opacity: 0.95;
    }
}

/* Animasi Napas Karakter di Perahu */
@keyframes passengerBobbing {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-2.5px);
    }
}

/* Animasi Napas & Gerak Fisik Karakter Misionaris / Ilmuwan */
@keyframes scholarBreathingMotion {
    0%, 100% {
        transform: translateY(0px) scale(1);
    }
    50% {
        transform: translateY(-2.5px) scale(1.01);
    }
}

/* Animasi Gerak Ritmis Prajurit Rimba / Kanibal */
@keyframes tribalRhythmicMotion {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    25% {
        transform: translateY(-2px) rotate(1deg);
    }
    50% {
        transform: translateY(1px) rotate(-0.5deg);
    }
    75% {
        transform: translateY(-1.5px) rotate(0.8deg);
    }
}

/* Animasi Aliran Arus Sungai */
@keyframes riverWaterFlow {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: 40px 0;
    }
}

.scholar-figure {
    animation: scholarBreathingMotion 2.6s ease-in-out infinite;
}

.tribal-figure {
    animation: tribalRhythmicMotion 2.2s ease-in-out infinite;
}

.river-current-flow {
    background-image: repeating-linear-gradient(45deg, rgba(255,255,255,0.06) 0px, rgba(255,255,255,0.06) 10px, transparent 10px, transparent 20px);
    background-size: 28px 28px;
    animation: riverWaterFlow 2.5s linear infinite;
}

.boat-idle {
    animation: boatGentleBob 3.6s ease-in-out infinite;
}

.boat-rowing {
    animation: boatRowingMotion 0.8s ease-in-out infinite;
}

.oar-active {
    animation: oarRowingCycle 0.8s ease-in-out infinite;
}

.water-wake-anim {
    animation: waterWakePulsing 2.2s ease-in-out infinite;
}

.passenger-idle-bob {
    animation: passengerBobbing 2.4s ease-in-out infinite;
}
</style>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- SCRIPT ENGINE LOGIKA PEMBDACHALLENGE (ALPINE.JS & TOUCH HANDLING)     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<script>
function pembdaChallenge() {
    return {
        isCollapsed: false,
        activeGame: 'chemistry', // 'chemistry', 'logic', 'water', 'hanoi', atau 'river'
        isLandscapeMode: false,
        isPhysicalLandscape: false,
        showRulesModal: false,
        isMoving: false,
        isCooldown: false,
        cooldownSeconds: 0,
        hasWonToday: false,

        checkOrientation() {
            this.isPhysicalLandscape = (window.innerHeight <= 520 && window.innerWidth > window.innerHeight);
        },

        toggleLandscapeMode() {
            this.isLandscapeMode = !this.isLandscapeMode;
            const el = document.getElementById('pembda-challenge');
            if (this.isLandscapeMode) {
                this.isCollapsed = false;
                if (el && el.requestFullscreen) {
                    el.requestFullscreen().catch(() => {});
                } else if (el && el.webkitRequestFullscreen) {
                    el.webkitRequestFullscreen().catch(() => {});
                }
                if (screen.orientation && screen.orientation.lock) {
                    screen.orientation.lock('landscape').catch(() => {});
                }
            } else {
                if (document.exitFullscreen && document.fullscreenElement) {
                    document.exitFullscreen().catch(() => {});
                } else if (document.webkitExitFullscreen && document.webkitFullscreenElement) {
                    document.webkitExitFullscreen().catch(() => {});
                }
                if (screen.orientation && screen.orientation.unlock) {
                    screen.orientation.unlock();
                }
            }
        },
        
        // Kondisi Permainan
        lives: 3,
        moves: 0,
        timerSeconds: 0,
        timerInterval: null,
        
        // Posisi Karakter
        westM: 3,
        westC: 3,
        eastM: 0,
        eastC: 0,
        boatPosition: 'west', // 'west' atau 'east'
        boatPassengers: [], // Array berisi 'M' atau 'C' (maks 2)

        // Snapshot posisi sebelum perahu mendayung (untuk rollback jika celaka)
        lastStateSnapshot: null,

        // Modal State
        dangerAlert: {
            show: false,
            message: ''
        },
        victoryModal: {
            show: false,
            stars: 3,
            points: 50,
            rankTitle: '',
            message: ''
        },

        async initGame() {
            this.checkOrientation();
            window.addEventListener('resize', () => this.checkOrientation());
            window.addEventListener('orientationchange', () => {
                setTimeout(() => this.checkOrientation(), 250);
            });

            // Cek parameter game dari URL jika ada
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('game') === 'river' || urlParams.get('game') === 'missionaries') {
                this.activeGame = 'river';
            } else if (urlParams.get('game') === 'hanoi') {
                this.activeGame = 'hanoi';
            } else if (urlParams.get('game') === 'water' || urlParams.get('game') === 'water_jug' || urlParams.get('game') === 'diehard') {
                this.activeGame = 'water';
            } else if (urlParams.get('game') === 'logic' || urlParams.get('game') === 'logic_gates' || urlParams.get('game') === 'circuit') {
                this.activeGame = 'logic';
            } else if (urlParams.get('game') === 'chemistry' || urlParams.get('game') === 'chemical' || urlParams.get('game') === 'reaction' || urlParams.get('game') === 'kimia') {
                this.activeGame = 'chemistry';
            }

            this.resetGameFull();
            await this.fetchStatus();
            this.startTimer();

            // Auto-expand jika URL mengandung anchor atau parameter challenge
            if (window.location.hash === '#pembda-challenge' || window.location.search.includes('challenge=1')) {
                this.isCollapsed = false;
                setTimeout(() => {
                    const el = document.getElementById('pembda-challenge');
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        el.classList.add('ring-4', 'ring-[#fde047]');
                        setTimeout(() => el.classList.remove('ring-4', 'ring-[#fde047]'), 1500);
                    }
                }, 300);
            }
        },

        async fetchStatus() {
            try {
                const res = await fetch('/forum/challenge/status?game_key=missionaries_cannibals', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.is_in_cooldown) {
                    this.isCooldown = true;
                    this.cooldownSeconds = data.cooldown_seconds;
                    this.startCooldownTimer();
                }
                this.hasWonToday = data.has_won_today;
            } catch (e) {
                console.warn('Gagal memuat status challenge:', e);
            }
        },

        startTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (!this.isCooldown && !this.victoryModal.show) {
                    this.timerSeconds++;
                }
            }, 1000);
        },

        startCooldownTimer() {
            const cdInt = setInterval(() => {
                if (this.cooldownSeconds > 0) {
                    this.cooldownSeconds--;
                } else {
                    this.isCooldown = false;
                    this.lives = 3;
                    clearInterval(cdInt);
                }
            }, 1000);
        },

        formatTime(sec) {
            const m = Math.floor(sec / 60).toString().padStart(2, '0');
            const s = (sec % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        },

        // Naikkan karakter ke perahu
        boardBoat(type, bank) {
            if (this.isMoving || this.isCooldown || this.boatPassengers.length >= 2) return;
            if (this.boatPosition !== bank) return;

            if (bank === 'west') {
                if (type === 'M' && this.westM > 0) {
                    this.westM--;
                    this.boatPassengers.push('M');
                } else if (type === 'C' && this.westC > 0) {
                    this.westC--;
                    this.boatPassengers.push('C');
                }
            } else {
                if (type === 'M' && this.eastM > 0) {
                    this.eastM--;
                    this.boatPassengers.push('M');
                } else if (type === 'C' && this.eastC > 0) {
                    this.eastC--;
                    this.boatPassengers.push('C');
                }
            }
        },

        // Turunkan karakter dari perahu ke tepi tempat perahu berada
        unboardBoat(index) {
            if (this.isMoving || this.isCooldown) return;
            if (index < 0 || index >= this.boatPassengers.length) return;

            const char = this.boatPassengers.splice(index, 1)[0];
            if (this.boatPosition === 'west') {
                if (char === 'M') this.westM++;
                else this.westC++;
            } else {
                if (char === 'M') this.eastM++;
                else this.eastC++;
            }

            // Cek apakah kemenangan tercapai setelah orang turun di Timur
            this.checkVictoryCondition();
        },

        // Turunkan semua penumpang sekaligus ke tepi tempat perahu merapat
        unboardAll() {
            if (this.isMoving || this.isCooldown || this.boatPassengers.length === 0) return;
            while (this.boatPassengers.length > 0) {
                this.unboardBoat(0);
            }
        },

        // Dayung Perahu menyeberang sungai
        rowBoat() {
            if (this.isMoving || this.isCooldown || this.boatPassengers.length === 0) return;

            // Simpan snapshot sebelum perahu bergerak
            this.lastStateSnapshot = {
                westM: this.westM,
                westC: this.westC,
                eastM: this.eastM,
                eastC: this.eastC,
                boatPosition: this.boatPosition,
                boatPassengers: [...this.boatPassengers],
                moves: this.moves
            };

            this.isMoving = true;
            this.moves++;

            // Animasi perahu berlayar selama 800ms
            setTimeout(() => {
                this.boatPosition = this.boatPosition === 'west' ? 'east' : 'west';
                this.isMoving = false;

                // Evaluasi kondisi bahaya di kedua tepi sungai
                this.evaluateBankSafety();
            }, 800);
        },

        // Evaluasi apakah jumlah Kanibal > Misionaris di salah satu tepi
        evaluateBankSafety() {
            // Hitung total orang di Tepi Barat (termasuk yang di perahu jika perahu di barat)
            let currWestM = this.westM;
            let currWestC = this.westC;
            let currEastM = this.eastM;
            let currEastC = this.eastC;

            if (this.boatPosition === 'west') {
                this.boatPassengers.forEach(p => {
                    if (p === 'M') currWestM++;
                    else currWestC++;
                });
            } else {
                this.boatPassengers.forEach(p => {
                    if (p === 'M') currEastM++;
                    else currEastC++;
                });
            }

            let dangerWest = (currWestM > 0 && currWestC > currWestM);
            let dangerEast = (currEastM > 0 && currEastC > currEastM);

            if (dangerWest || dangerEast) {
                this.triggerDanger(dangerWest ? 'Tepi Barat' : 'Tepi Timur');
            } else {
                this.checkVictoryCondition();
            }
        },

        // Pemicu bahaya: nyawa berkurang & perahu dikembalikan
        async triggerDanger(location) {
            this.lives--;
            this.dangerAlert.message = `Di ${location}, jumlah Kanibal lebih banyak daripada Misionaris! Misionaris terancam dimangsa.`;
            this.dangerAlert.show = true;

            // Jika nyawa habis -> Trigger Game Over & Cooldown
            if (this.lives <= 0) {
                await this.triggerGameOver();
            }
        },

        closeDangerModal() {
            this.dangerAlert.show = false;
            // Rollback posisi ke langkah sebelum mendayung
            if (this.lastStateSnapshot && this.lives > 0) {
                this.westM = this.lastStateSnapshot.westM;
                this.westC = this.lastStateSnapshot.westC;
                this.eastM = this.lastStateSnapshot.eastM;
                this.eastC = this.lastStateSnapshot.eastC;
                this.boatPosition = this.lastStateSnapshot.boatPosition;
                this.boatPassengers = [...this.lastStateSnapshot.boatPassengers];
            }
        },

        async triggerGameOver() {
            this.dangerAlert.show = false;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/forum/challenge/fail', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        game_key: 'missionaries_cannibals',
                        moves: this.moves,
                        seconds_taken: this.timerSeconds
                    })
                });
                const data = await res.json();
                this.isCooldown = true;
                this.cooldownSeconds = data.cooldown_seconds || 900;
                this.startCooldownTimer();
                this.resetCurrentSession();
            } catch (e) {
                console.error(e);
            }
        },

        // Cek apakah kemenangan tercapai (semua 3M & 3C ada di Tepi Timur)
        async checkVictoryCondition() {
            if (this.eastM === 3 && this.eastC === 3 && this.boatPassengers.length === 0) {
                await this.submitVictory();
            }
        },

        async submitVictory() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/forum/challenge/complete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        game_key: 'missionaries_cannibals',
                        moves: this.moves,
                        lives_left: this.lives,
                        seconds_taken: this.timerSeconds
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.victoryModal.stars = data.stars;
                    this.victoryModal.points = data.points_awarded;
                    this.victoryModal.rankTitle = data.rank_title;
                    this.victoryModal.message = data.message;
                    this.victoryModal.show = true;
                    this.hasWonToday = true;

                    // Trigger Web Push ke user sendiri untuk bukti reputasi
                    if (window.PembdaPush) {
                        // Success haptic/sound
                    }
                }
            } catch (e) {
                console.error('Submit victory error:', e);
            }
        },

        resetCurrentSession() {
            this.westM = 3;
            this.westC = 3;
            this.eastM = 0;
            this.eastC = 0;
            this.boatPosition = 'west';
            this.boatPassengers = [];
            this.moves = 0;
        },

        resetGameFull() {
            this.resetCurrentSession();
            this.lives = 3;
            this.timerSeconds = 0;
            this.dangerAlert.show = false;
            this.victoryModal.show = false;
        }
    };
}
</script>
