<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ⚡ PembdaCHALLENGE: Misionaris vs Kanibal (River Crossing Logic Quest)  -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="pembda-challenge"
     class="mb-6 bg-white border-2 border-[#121316] rounded-3xl overflow-hidden shadow-[4px_4px_0px_#121316] scroll-mt-24 transition-all duration-300" 
     x-data="pembdaChallenge()" 
     x-init="initGame()"
     @open-challenge.window="isCollapsed = false">

    <!-- Header Bar -->
    <div class="p-3 sm:p-3.5 border-b-2 border-[#121316] flex flex-wrap justify-between items-center bg-gradient-to-r from-amber-300 via-yellow-300 to-amber-200 cursor-pointer hover:opacity-95 transition" 
         @click="isCollapsed = !isCollapsed">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-gradient-to-tr from-violet-600 via-indigo-600 to-pink-500 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center text-white font-black text-lg shrink-0">
                ⚡
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="forum-hdr text-sm sm:text-base font-black text-[#121316] tracking-tight leading-tight m-0">PembdaCHALLENGE</h2>
                    <span class="text-[9px] font-mono-code font-black px-2 py-0.5 rounded-full bg-[#121316] text-[#fde047] flex items-center gap-1 shadow-xs">
                        <span>GAME SAINS & LOGIKA</span>
                    </span>
                    <span class="text-[9px] font-mono-code font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-400">
                        +50 POIN
                    </span>
                </div>
                <span class="text-[11px] sm:text-xs text-slate-800 font-bold block sm:inline mt-0.5">Misionaris vs Kanibal — Seberangkan 3 Ilmuwan & 3 Kanibal dengan selamat!</span>
            </div>
        </div>

        <div class="flex items-center gap-2 mt-2 sm:mt-0" @click.stop>
            @if(request('view') === 'challenge')
                <!-- Tombol Langsung Kembali ke Lobi Forum -->
                <a href="{{ route('forum.index') }}" class="btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_#121316]">
                    <i class="fa-solid fa-arrow-left text-xs text-rose-600"></i>
                    <span>Lobi Utama</span>
                </a>
            @endif

            <!-- Status Nyawa -->
            <div class="flex items-center gap-1 px-2.5 py-1 bg-white border-2 border-[#121316] rounded-xl shadow-[1px_1px_0px_#121316]" title="Sisa Kesempatan">
                <template x-for="i in 3" :key="i">
                    <span class="text-xs sm:text-sm transition-transform" :class="i <= lives ? 'scale-100' : 'opacity-20 grayscale scale-75'">
                        ❤️
                    </span>
                </template>
            </div>

            <!-- Tombol Instruksi -->
            <button @click="showRulesModal = true" class="btn-tactile-white px-2.5 py-1 rounded-xl text-xs font-black flex items-center gap-1">
                <i class="fa-solid fa-circle-question text-indigo-600"></i>
                <span class="hidden sm:inline">Aturan</span>
            </button>

            <!-- Tombol Buka/Tutup -->
            <button @click="isCollapsed = !isCollapsed" class="p-1.5 bg-white border-2 border-[#121316] rounded-xl text-[#121316] shadow-[2px_2px_0px_#121316] hover:bg-[#faf8f5] transition">
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300" :class="isCollapsed ? '' : 'rotate-180'"></i>
            </button>
        </div>
    </div>

    <!-- Arena Permainan -->
    <div x-show="!isCollapsed" x-transition.opacity.duration.300ms class="p-3 sm:p-4 bg-gradient-to-b from-sky-50/70 via-indigo-50/40 to-emerald-50/50 border-t border-[#121316]/10">

        <!-- Banner Cooldown (Jika Nyawa Habis) -->
        <div x-show="isCooldown" class="mb-3 p-3.5 rounded-2xl bg-rose-50 border-2 border-rose-600 text-rose-900 shadow-[3px_3px_0px_#e11d48]">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⏳</span>
                <div class="flex-1">
                    <h4 class="font-black text-sm text-rose-950">Sesi Sedang Cooldown (Istirahat Berpikir)</h4>
                    <p class="text-xs text-rose-800 mt-0.5">3 Kesempatan Anda telah habis. Luangkan waktu untuk menganalisis strategi pemecahan masalah.</p>
                    <div class="mt-1.5 text-xs font-mono-code font-black text-rose-700">
                        Dapat mencoba kembali dalam: <span class="bg-rose-200 px-2 py-0.5 rounded-md text-rose-950" x-text="formatTime(cooldownSeconds)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Bar (Langkah, Waktu, Rekor) -->
        <div class="flex flex-wrap items-center justify-between gap-2.5 mb-3 p-2.5 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316]">
            <div class="flex items-center gap-3 flex-wrap text-xs font-mono-code font-bold">
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-500 text-[11px]">LANGKAH:</span>
                    <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="moves"></span>
                    <span class="text-[10px] text-slate-500 hidden sm:inline">(Optimum: 11)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-500 text-[11px]">WAKTU:</span>
                    <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="formatTime(timerSeconds)"></span>
                </div>
                <template x-if="hasWonToday">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-400 text-[9px] font-black">
                        ✓ Tuntas Hari Ini
                    </span>
                </template>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="resetCurrentSession()" :disabled="isCooldown || moves === 0" class="btn-tactile-white px-2.5 py-1 rounded-lg text-xs font-black disabled:opacity-40">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Ulang Posisi
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- ARENA VISUAL SUNGAI & TEPI (RESPONSIVE PC & MOBILE OPTIMIZED) -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="relative w-full rounded-2xl border-2 border-[#121316] bg-gradient-to-b from-sky-200 via-sky-300 to-sky-400 shadow-[4px_4px_0px_#121316]">
            
            <!-- Gelombang Air Sungai CSS -->
            <div class="absolute inset-0 opacity-25 pointer-events-none rounded-2xl" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 16px 16px;"></div>

            <!-- Kontainer Utama: 3 Kolom (Tepi Barat - Sungai & Perahu - Tepi Timur) -->
            <div class="grid grid-cols-1 md:grid-cols-12 relative min-h-[360px]">
                
                <!-- 1. TEPI BARAT (LEFT BANK) -->
                <div class="md:col-span-4 p-3 sm:p-3.5 bg-emerald-100/90 border-b-2 md:border-b-0 md:border-r-2 border-[#121316] flex flex-col justify-between relative rounded-t-2xl md:rounded-tr-none md:rounded-l-2xl">
                    <div class="flex items-center justify-between pb-1.5 border-b border-[#121316]/20">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">🏝️</span>
                            <span class="font-black text-xs sm:text-sm text-[#121316]">TEPI BARAT</span>
                        </div>
                        <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md bg-white border border-[#121316]">
                            M: <span x-text="westM"></span> | K: <span x-text="westC"></span>
                        </span>
                    </div>

                    <!-- Area Karakter Tepi Barat (Grid 3 Kolom Rapi Anti-Potong) -->
                    <div class="py-2.5 my-auto">
                        <p class="text-[9px] font-mono-code font-bold text-slate-600 mb-1.5 uppercase tracking-wider text-center sm:text-left">
                            Ketuk untuk naik perahu:
                        </p>
                        
                        <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                            <!-- Karakter Misionaris Barat -->
                            <template x-for="i in westM" :key="'wm-' + i">
                                <button type="button" 
                                        @click="boardBoat('M', 'west')"
                                        :disabled="boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="h-12 sm:h-13.5 rounded-xl bg-white border-2 border-[#121316] shadow-[1px_2px_0px_#121316] flex flex-col items-center justify-center hover:scale-105 active:scale-95 transition disabled:opacity-40 disabled:hover:scale-100 cursor-pointer group p-1"
                                        title="Misionaris (Ilmuwan)">
                                    <span class="text-lg sm:text-xl group-hover:animate-bounce leading-none">🧙‍♂️</span>
                                    <span class="text-[7.5px] font-mono-code font-black text-indigo-700 uppercase leading-none mt-1">ILMUWAN</span>
                                </button>
                            </template>

                            <!-- Karakter Kanibal Barat -->
                            <template x-for="i in westC" :key="'wc-' + i">
                                <button type="button" 
                                        @click="boardBoat('C', 'west')"
                                        :disabled="boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="h-12 sm:h-13.5 rounded-xl bg-amber-100 border-2 border-[#121316] shadow-[1px_2px_0px_#121316] flex flex-col items-center justify-center hover:scale-105 active:scale-95 transition disabled:opacity-40 disabled:hover:scale-100 cursor-pointer group p-1"
                                        title="Kanibal">
                                    <span class="text-lg sm:text-xl group-hover:animate-bounce leading-none">👹</span>
                                    <span class="text-[7.5px] font-mono-code font-black text-amber-900 uppercase leading-none mt-1">KANIBAL</span>
                                </button>
                            </template>

                            <template x-if="westM === 0 && westC === 0">
                                <div class="col-span-3 text-center py-4 text-xs font-mono-code text-slate-500 font-bold italic">
                                    Tepi Barat Kosong ✓
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Tombol Cepat HP untuk Tepi Barat -->
                    <div class="flex gap-1.5 pt-1.5 border-t border-[#121316]/10 md:hidden">
                        <button type="button" @click="boardBoat('M', 'west')" :disabled="westM === 0 || boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving" class="flex-1 py-1 bg-white border border-[#121316] rounded-lg text-[10px] font-black">
                            +1 Ilmuwan
                        </button>
                        <button type="button" @click="boardBoat('C', 'west')" :disabled="westC === 0 || boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving" class="flex-1 py-1 bg-amber-200 border border-[#121316] rounded-lg text-[10px] font-black">
                            +1 Kanibal
                        </button>
                    </div>
                </div>

                <!-- 2. SUNGAI & PERAHU (RIVER & BOAT) -->
                <div class="md:col-span-4 p-3 sm:p-3.5 flex flex-col justify-between items-center relative bg-gradient-to-r from-blue-400/80 via-cyan-400/80 to-blue-400/80">
                    
                    <!-- Status Posisi Perahu -->
                    <div class="text-[9px] sm:text-[10px] font-mono-code font-black px-3 py-0.5 rounded-full bg-[#121316] text-white shadow-xs z-10 mb-1">
                        PERAHU DI: <span class="text-yellow-300" x-text="boatPosition === 'west' ? 'TEPI BARAT ◄' : '► TEPI TIMUR'"></span>
                    </div>

                    <!-- Area Perahu Bergerak (Animated Realistic Boat) -->
                    <div class="my-auto w-full py-2 flex flex-col items-center justify-center relative z-10">
                        
                        <!-- Wrapper Animasi Gerak Perahu Melintasi Sungai -->
                        <div class="w-full flex transition-all duration-800 ease-in-out px-1 sm:px-2"
                             :class="boatPosition === 'west' ? 'justify-start' : 'justify-end'">
                            
                            <!-- KONTEN PERAHU SAMPAN KAYU REALISTIS 3D & PENUMPANG -->
                            <div class="w-60 sm:w-68 relative select-none transition-transform"
                                 :class="isMoving ? 'boat-rowing scale-105' : 'boat-idle'">
                                
                                <!-- SVG DEFS (Gradien Warna Kayu & Efek Visual) -->
                                <svg class="absolute w-0 h-0 pointer-events-none" aria-hidden="true">
                                    <defs>
                                        <!-- Gradien Interior Lambung Dalam (Gelap & Berkedalaman) -->
                                        <linearGradient id="boatInnerWoodGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#1f0a02" />
                                            <stop offset="100%" stop-color="#3d1504" />
                                        </linearGradient>

                                        <!-- Gradien Papan Kayu Atas & Bibir Perahu (Warm Amber Teak) -->
                                        <linearGradient id="boatPlankTopGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#f59e0b" />
                                            <stop offset="25%" stop-color="#b45309" />
                                            <stop offset="100%" stop-color="#78350f" />
                                        </linearGradient>

                                        <!-- Gradien Papan Kayu Tengah (Mahoni) -->
                                        <linearGradient id="boatPlankMidGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#92400e" />
                                            <stop offset="100%" stop-color="#5a2206" />
                                        </linearGradient>

                                        <!-- Gradien Papan Lunas Bawah (Kayu Basah Air Sungai) -->
                                        <linearGradient id="boatPlankBtmGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#5a2206" />
                                            <stop offset="100%" stop-color="#2a0d01" />
                                        </linearGradient>

                                        <!-- Highlight Bibir Perahu (Keemasan Cahaya Matahari) -->
                                        <linearGradient id="boatRimLightGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" stop-color="#fef08a" stop-opacity="0.9" />
                                            <stop offset="50%" stop-color="#fbbf24" stop-opacity="1" />
                                            <stop offset="100%" stop-color="#fef08a" stop-opacity="0.9" />
                                        </linearGradient>

                                        <!-- Gradien Batang Kayu Dayung -->
                                        <linearGradient id="boatOarShaft" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#fef3c7" />
                                            <stop offset="50%" stop-color="#d97706" />
                                            <stop offset="100%" stop-color="#92400e" />
                                        </linearGradient>

                                        <!-- Gradien Daun Dayung Kayu Pipih -->
                                        <linearGradient id="boatOarBlade" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#b45309" />
                                            <stop offset="100%" stop-color="#78350f" />
                                        </linearGradient>
                                    </defs>
                                </svg>

                                <!-- LAYER 1: INNER HULL (DINDING DALAM, LANTAI, BANGKU DUDUK, & DAYUNG KAYU) -->
                                <svg viewBox="0 0 280 115" class="w-full h-auto block filter drop-shadow-sm pointer-events-none" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Dinding Dalam Belakang Perahu -->
                                    <path d="M 12 36 Q 140 48 268 36 L 265 43 Q 140 56 15 43 Z" fill="#2d1204" />
                                    
                                    <!-- Rongga Lantai Dalam Sampan -->
                                    <path d="M 15 43 Q 140 56 265 43 L 248 68 Q 140 78 32 68 Z" fill="url(#boatInnerWoodGrad)" />
                                    
                                    <!-- Gading-gading Balok Lantai Interior Kayu -->
                                    <line x1="75" y1="48" x2="75" y2="72" stroke="#150601" stroke-width="2.5" stroke-linecap="round" />
                                    <line x1="140" y1="52" x2="140" y2="76" stroke="#150601" stroke-width="2.5" stroke-linecap="round" />
                                    <line x1="205" y1="48" x2="205" y2="72" stroke="#150601" stroke-width="2.5" stroke-linecap="round" />
                                    
                                    <!-- Bangku Kayu Penumpang (Thwarts) -->
                                    <!-- Bangku Kursi 1 (Kiri) -->
                                    <rect x="52" y="50" width="60" height="7" rx="2" fill="#78350f" stroke="#451a03" stroke-width="1.2" />
                                    <line x1="54" y1="52" x2="110" y2="52" stroke="#b45309" stroke-width="1" />
                                    <!-- Bangku Kursi 2 (Kanan) -->
                                    <rect x="168" y="50" width="60" height="7" rx="2" fill="#78350f" stroke="#451a03" stroke-width="1.2" />
                                    <line x1="170" y1="52" x2="226" y2="52" stroke="#b45309" stroke-width="1" />

                                    <!-- Dayung Kayu Asli (Oar) yang Menjulur Miring ke Air -->
                                    <g class="oar-assembly" :class="isMoving ? 'oar-active' : ''">
                                        <!-- Batang Dayung Panjang -->
                                        <line x1="142" y1="36" x2="262" y2="92" stroke="url(#boatOarShaft)" stroke-width="4.5" stroke-linecap="round" />
                                        <!-- Daun Dayung Oval Pipih di Permukaan Air -->
                                        <ellipse cx="264" cy="94" rx="15" ry="7" transform="rotate(32 264 94)" fill="url(#boatOarBlade)" stroke="#451a03" stroke-width="1.2" />
                                        <!-- Cincin Pengait Dayung di Bibir Lambung -->
                                        <circle cx="218" cy="68" r="3.5" fill="#f59e0b" stroke="#78350f" stroke-width="1.5" />
                                    </g>
                                </svg>

                                <!-- LAYER 2: PENUMPANG DUDUK DI DALAM PERAHU (MENYATU DI LAMBUNG KAPAL) -->
                                <div class="absolute inset-0 z-20 pointer-events-none">
                                    
                                    <!-- Kursi 1 (Kiri / Depan - Duduk di Bangku 1) -->
                                    <div class="absolute left-[17%] w-[26%] top-0 bottom-[22px] flex flex-col items-center justify-end pointer-events-auto">
                                        <template x-if="boatPassengers.length > 0">
                                            <button type="button" 
                                                    @click="unboardBoat(0)"
                                                    :disabled="isMoving || isCooldown"
                                                    class="group/pax relative flex flex-col items-center justify-end w-full cursor-pointer focus:outline-none transition-transform hover:-translate-y-1.5 active:translate-y-0"
                                                    title="Ketuk karakter untuk turunkan ke daratan">
                                                
                                                <!-- Badge Aksi Mini di Atas Kepala Karakter -->
                                                <div class="mb-0.5 px-1.5 py-0.5 rounded-full text-[7.5px] font-mono-code font-black border border-[#121316] shadow-2xs transition-all flex items-center gap-0.5"
                                                     :class="boatPassengers[0] === 'M' ? 'bg-indigo-100 text-indigo-900 group-hover/pax:bg-rose-500 group-hover/pax:text-white' : 'bg-amber-100 text-amber-900 group-hover/pax:bg-rose-500 group-hover/pax:text-white'">
                                                    <span class="group-hover/pax:hidden" x-text="boatPassengers[0] === 'M' ? 'ILMUWAN' : 'KANIBAL'"></span>
                                                    <span class="hidden group-hover/pax:inline font-bold">✕ TURUN</span>
                                                </div>

                                                <!-- Karakter Duduk di Lambung (Setengah Badan Menyembul Keluar Perahu) -->
                                                <div class="passenger-idle-bob transform group-hover/pax:scale-110 transition-transform">
                                                    <span class="text-3xl sm:text-4xl filter drop-shadow-[0_3px_2px_rgba(0,0,0,0.4)] select-none leading-none block"
                                                          x-text="boatPassengers[0] === 'M' ? '🧙‍♂️' : '👹'"></span>
                                                </div>
                                            </button>
                                        </template>

                                        <template x-if="boatPassengers.length === 0">
                                            <div class="flex flex-col items-center justify-end w-full opacity-60 pb-1 select-none">
                                                <span class="text-[7.5px] font-mono-code font-extrabold text-amber-200 bg-amber-950/70 px-1.5 py-0.5 rounded border border-amber-500/30">
                                                    KOSONG
                                                </span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Kursi 2 (Kanan / Belakang - Duduk di Bangku 2) -->
                                    <div class="absolute left-[58%] w-[26%] top-0 bottom-[22px] flex flex-col items-center justify-end pointer-events-auto">
                                        <template x-if="boatPassengers.length > 1">
                                            <button type="button" 
                                                    @click="unboardBoat(1)"
                                                    :disabled="isMoving || isCooldown"
                                                    class="group/pax relative flex flex-col items-center justify-end w-full cursor-pointer focus:outline-none transition-transform hover:-translate-y-1.5 active:translate-y-0"
                                                    title="Ketuk karakter untuk turunkan ke daratan">
                                                
                                                <!-- Badge Aksi Mini di Atas Kepala Karakter -->
                                                <div class="mb-0.5 px-1.5 py-0.5 rounded-full text-[7.5px] font-mono-code font-black border border-[#121316] shadow-2xs transition-all flex items-center gap-0.5"
                                                     :class="boatPassengers[1] === 'M' ? 'bg-indigo-100 text-indigo-900 group-hover/pax:bg-rose-500 group-hover/pax:text-white' : 'bg-amber-100 text-amber-900 group-hover/pax:bg-rose-500 group-hover/pax:text-white'">
                                                    <span class="group-hover/pax:hidden" x-text="boatPassengers[1] === 'M' ? 'ILMUWAN' : 'KANIBAL'"></span>
                                                    <span class="hidden group-hover/pax:inline font-bold">✕ TURUN</span>
                                                </div>

                                                <!-- Karakter Duduk di Lambung (Setengah Badan Menyembul Keluar Perahu) -->
                                                <div class="passenger-idle-bob transform group-hover/pax:scale-110 transition-transform" style="animation-delay: 0.6s;">
                                                    <span class="text-3xl sm:text-4xl filter drop-shadow-[0_3px_2px_rgba(0,0,0,0.4)] select-none leading-none block"
                                                          x-text="boatPassengers[1] === 'M' ? '🧙‍♂️' : '👹'"></span>
                                                </div>
                                            </button>
                                        </template>

                                        <template x-if="boatPassengers.length <= 1">
                                            <div class="flex flex-col items-center justify-end w-full opacity-60 pb-1 select-none">
                                                <span class="text-[7.5px] font-mono-code font-extrabold text-amber-200 bg-amber-950/70 px-1.5 py-0.5 rounded border border-amber-500/30">
                                                    KOSONG
                                                </span>
                                            </div>
                                        </template>
                                    </div>

                                </div>

                                <!-- LAYER 3: OUTER HULL (LAMBUNG LUAR KAYU SAMPAN 3D, PAKU PASAK & PROW POSTS) -->
                                <svg viewBox="0 0 280 115" class="w-full h-auto absolute inset-0 z-30 pointer-events-none" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    
                                    <!-- Bibir Lambung Luar Depan (Gunwale) -->
                                    <path d="M 8 32 Q 140 50 272 32 L 270 38 Q 140 56 10 38 Z" fill="url(#boatPlankTopGrad)" stroke="#451a03" stroke-width="1.2" />
                                    <!-- Highlight Garis Kayu Emas Mengilap di Bibir -->
                                    <path d="M 12 33 Q 140 49 268 33" stroke="url(#boatRimLightGrad)" stroke-width="2" stroke-linecap="round" fill="none" />

                                    <!-- Papan Lambung Lapisan 1 (Upper Lapstrake Plank) -->
                                    <path d="M 10 38 Q 140 56 270 38 L 262 52 Q 140 69 18 52 Z" fill="url(#boatPlankTopGrad)" stroke="#451a03" stroke-width="1" />
                                    
                                    <!-- Pasak Paku Rivet Kayu Kuningan di Sepanjang Lambung -->
                                    <circle cx="35" cy="43" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="70" cy="48" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="105" cy="53" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="140" cy="55" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="175" cy="53" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="210" cy="48" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />
                                    <circle cx="245" cy="43" r="1.6" fill="#fde68a" stroke="#78350f" stroke-width="0.6" />

                                    <!-- Papan Lambung Lapisan 2 (Middle Plank) -->
                                    <path d="M 18 52 Q 140 69 262 52 L 250 67 Q 140 83 30 67 Z" fill="url(#boatPlankMidGrad)" stroke="#3b1302" stroke-width="1" />

                                    <!-- Papan Lambung Lapisan 3 / Lunas Bawah (Keel Plank) -->
                                    <path d="M 30 67 Q 140 83 250 67 L 234 79 Q 140 93 46 79 Z" fill="url(#boatPlankBtmGrad)" stroke="#1f0a02" stroke-width="1.2" />

                                    <!-- Haluan Runcing Kiri (Stem Bow Post) & Tali Tambat -->
                                    <path d="M 8 32 C 4 21 3 13 10 9 C 15 11 13 23 10 38 Z" fill="#78350f" stroke="#270f03" stroke-width="1.2" />
                                    <circle cx="10" cy="18" r="2.8" fill="none" stroke="#fef08a" stroke-width="1.5" />

                                    <!-- Haluan Runcing Kanan (Stern Post) & Tali Tambat -->
                                    <path d="M 272 32 C 276 21 277 13 270 9 C 265 11 267 23 270 38 Z" fill="#78350f" stroke="#270f03" stroke-width="1.2" />
                                    <circle cx="270" cy="18" r="2.8" fill="none" stroke="#fef08a" stroke-width="1.5" />
                                    
                                    <!-- Ukiran Lambang Tradisional di Tengah Lambung -->
                                    <path d="M 134 68 L 140 62 L 146 68 L 140 74 Z" fill="#b45309" stroke="#fde68a" stroke-width="0.8" />
                                </svg>

                                <!-- LAYER 4: RIANG AIR & OMBAK GELOMBANG SUNGAI DI BAWAH PERAHU -->
                                <svg viewBox="0 0 280 115" class="w-full h-auto absolute inset-0 z-10 pointer-events-none" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Bayangan Perahu di Dalam Air Sungai -->
                                    <ellipse cx="140" cy="91" rx="108" ry="8" fill="rgba(3, 105, 161, 0.45)" />
                                    
                                    <!-- Riak Air Gelombang Putih di Lunas -->
                                    <path d="M 36 86 Q 140 98 244 86" stroke="rgba(255,255,255,0.85)" stroke-width="2.5" stroke-linecap="round" fill="none" class="water-wake-anim" />
                                    <path d="M 62 93 Q 140 103 218 93" stroke="rgba(255,255,255,0.5)" stroke-width="1.8" stroke-linecap="round" fill="none" class="water-wake-anim" style="animation-delay: 0.5s;" />
                                    <!-- Percikan Buih Air di Belakang Dayung -->
                                    <circle cx="265" cy="95" r="2.5" fill="rgba(255,255,255,0.8)" class="animate-ping" style="animation-duration: 2s;" />
                                </svg>

                            </div>
                        </div>

                        <!-- Status Kapasitas Perahu -->
                        <div class="mt-2 flex items-center justify-center gap-1.5 z-10">
                            <div class="text-[9px] font-mono-code font-black px-2.5 py-0.5 rounded-full bg-[#121316] text-amber-200 border border-amber-400/40 shadow-xs flex items-center gap-1.5">
                                <span>KAPASITAS: 2 ORANG</span>
                                <span class="text-amber-400">•</span>
                                <span class="text-white" x-text="boatPassengers.length + '/2 TERISI'"></span>
                            </div>
                        </div>

                        <!-- Tombol Eksekusi: DAYUNG PERAHU (Utuh & Terlihat Jelas) -->
                        <div class="mt-2 flex flex-col items-center gap-1 w-full">
                            <button type="button" 
                                    @click="rowBoat()"
                                    :disabled="boatPassengers.length === 0 || isMoving || isCooldown"
                                    class="w-full sm:w-auto px-5 py-2 rounded-xl text-xs sm:text-sm font-black border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition-all flex items-center justify-center gap-1.5"
                                    :class="boatPassengers.length > 0 && !isMoving && !isCooldown ? 'bg-[#fde047] hover:bg-yellow-300 text-[#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none' : 'bg-slate-200 text-slate-400 border-slate-400 cursor-not-allowed shadow-none'">
                                <span class="text-sm">⛵</span>
                                <span x-text="isMoving ? 'Sedang Menyeberang Arus...' : (boatPosition === 'west' ? 'DAYUNG KE TIMUR ►' : '◄ DAYUNG KE BARAT')"></span>
                            </button>
                            <span class="text-[9px] font-mono-code font-bold text-slate-800" x-show="boatPassengers.length === 0">
                                ⚠️ Naikkan minimal 1 orang ke perahu untuk mendayung
                            </span>
                        </div>
                    </div>

                    <div class="text-[9px] font-mono-code font-bold text-slate-700 z-10 text-center mt-1 pb-1">
                        Arus Sungai Cepat ~ Perahu Muat Maks 2
                    </div>
                </div>

                <!-- 3. TEPI TIMUR (RIGHT BANK - GOAL) -->
                <div class="md:col-span-4 p-3 sm:p-3.5 bg-emerald-100/90 border-t-2 md:border-t-0 md:border-l-2 border-[#121316] flex flex-col justify-between relative rounded-b-2xl md:rounded-bl-none md:rounded-r-2xl">
                    <div class="flex items-center justify-between pb-1.5 border-b border-[#121316]/20">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">🏁</span>
                            <span class="font-black text-xs sm:text-sm text-[#121316]">TEPI TIMUR (TUJUAN)</span>
                        </div>
                        <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md bg-white border border-[#121316]">
                            M: <span x-text="eastM"></span> | K: <span x-text="eastC"></span>
                        </span>
                    </div>

                    <!-- Area Karakter Tepi Timur (Grid 3 Kolom Rapi Anti-Potong) -->
                    <div class="py-2.5 my-auto">
                        <p class="text-[9px] font-mono-code font-bold text-slate-600 mb-1.5 uppercase tracking-wider text-center sm:text-left">
                            Karakter yang telah selamat sampai:
                        </p>

                        <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                            <!-- Karakter Misionaris Timur -->
                            <template x-for="i in eastM" :key="'em-' + i">
                                <button type="button" 
                                        @click="boardBoat('M', 'east')"
                                        :disabled="boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="h-12 sm:h-13.5 rounded-xl bg-white border-2 border-[#121316] shadow-[1px_2px_0px_#121316] flex flex-col items-center justify-center hover:scale-105 active:scale-95 transition disabled:opacity-40 disabled:hover:scale-100 cursor-pointer group p-1"
                                        title="Kembalikan ke perahu">
                                    <span class="text-lg sm:text-xl group-hover:animate-bounce leading-none">🧙‍♂️</span>
                                    <span class="text-[7.5px] font-mono-code font-black text-indigo-700 uppercase leading-none mt-1">ILMUWAN</span>
                                </button>
                            </template>

                            <!-- Karakter Kanibal Timur -->
                            <template x-for="i in eastC" :key="'ec-' + i">
                                <button type="button" 
                                        @click="boardBoat('C', 'east')"
                                        :disabled="boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="h-12 sm:h-13.5 rounded-xl bg-amber-100 border-2 border-[#121316] shadow-[1px_2px_0px_#121316] flex flex-col items-center justify-center hover:scale-105 active:scale-95 transition disabled:opacity-40 disabled:hover:scale-100 cursor-pointer group p-1"
                                        title="Kembalikan ke perahu">
                                    <span class="text-lg sm:text-xl group-hover:animate-bounce leading-none">👹</span>
                                    <span class="text-[7.5px] font-mono-code font-black text-amber-900 uppercase leading-none mt-1">KANIBAL</span>
                                </button>
                            </template>

                            <template x-if="eastM === 0 && eastC === 0">
                                <div class="col-span-3 text-center py-4 text-xs font-mono-code text-slate-500 font-bold italic">
                                    Belum ada yang menyeberang
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Tombol Cepat HP untuk Tepi Timur -->
                    <div class="flex gap-1.5 pt-1.5 border-t border-[#121316]/10 md:hidden">
                        <button type="button" @click="boardBoat('M', 'east')" :disabled="eastM === 0 || boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving" class="flex-1 py-1 bg-white border border-[#121316] rounded-lg text-[10px] font-black">
                            +1 Ilmuwan
                        </button>
                        <button type="button" @click="boardBoat('C', 'east')" :disabled="eastC === 0 || boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving" class="flex-1 py-1 bg-amber-200 border border-[#121316] rounded-lg text-[10px] font-black">
                            +1 Kanibal
                        </button>
                    </div>
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

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- GAYA KHUSUS & ANIMASI PERAHU REALISTIS & GERAKAN GELOMBANG AIR        -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<style>
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
        showRulesModal: false,
        isMoving: false,
        isCooldown: false,
        cooldownSeconds: 0,
        hasWonToday: false,
        
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
