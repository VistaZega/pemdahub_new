<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ⚡ PembdaCHALLENGE: Misionaris vs Kanibal (River Crossing Logic Quest)  -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="pembda-challenge"
     class="mb-6 bg-white border-2 border-[#121316] rounded-3xl overflow-hidden shadow-[4px_4px_0px_#121316] scroll-mt-24 transition-all duration-300" 
     x-data="pembdaChallenge()" 
     x-init="initGame()"
     @open-challenge.window="isCollapsed = false">

    <!-- Header Bar -->
    <div class="p-4 border-b-2 border-[#121316] flex flex-wrap justify-between items-center bg-gradient-to-r from-amber-300 via-yellow-300 to-amber-200 cursor-pointer hover:opacity-95 transition" 
         @click="isCollapsed = !isCollapsed">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-violet-600 via-indigo-600 to-pink-500 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center text-white font-black text-xl shrink-0">
                ⚡
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="forum-hdr text-base sm:text-lg font-black text-[#121316] tracking-tight leading-tight m-0">PembdaCHALLENGE</h2>
                    <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full bg-[#121316] text-[#fde047] flex items-center gap-1 shadow-xs">
                        <span>GAME SAINS & LOGIKA</span>
                    </span>
                    <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-400">
                        +50 POIN
                    </span>
                </div>
                <span class="text-xs text-slate-800 font-bold block sm:inline mt-0.5">Misionaris vs Kanibal — Seberangkan 3 Ilmuwan & 3 Kanibal dengan selamat!</span>
            </div>
        </div>

        <div class="flex items-center gap-2 mt-2 sm:mt-0" @click.stop>
            <!-- Status Nyawa -->
            <div class="flex items-center gap-1 px-2.5 py-1.5 bg-white border-2 border-[#121316] rounded-xl shadow-[1px_1px_0px_#121316]" title="Sisa Kesempatan">
                <template x-for="i in 3" :key="i">
                    <span class="text-sm transition-transform" :class="i <= lives ? 'scale-100' : 'opacity-20 grayscale scale-75'">
                        ❤️
                    </span>
                </template>
            </div>

            <!-- Tombol Instruksi -->
            <button @click="showRulesModal = true" class="btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-black flex items-center gap-1">
                <i class="fa-solid fa-circle-question text-indigo-600"></i>
                <span class="hidden sm:inline">Aturan</span>
            </button>

            <!-- Tombol Buka/Tutup -->
            <button @click="isCollapsed = !isCollapsed" class="p-2 bg-white border-2 border-[#121316] rounded-xl text-[#121316] shadow-[2px_2px_0px_#121316] hover:bg-[#faf8f5] transition">
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300" :class="isCollapsed ? '' : 'rotate-180'"></i>
            </button>
        </div>
    </div>

    <!-- Arena Permainan -->
    <div x-show="!isCollapsed" x-transition.opacity.duration.300ms class="p-4 sm:p-6 bg-gradient-to-b from-sky-50/70 via-indigo-50/40 to-emerald-50/50 border-t border-[#121316]/10">

        <!-- Banner Cooldown (Jika Nyawa Habis) -->
        <div x-show="isCooldown" class="mb-4 p-4 rounded-2xl bg-rose-50 border-2 border-rose-600 text-rose-900 shadow-[3px_3px_0px_#e11d48]">
            <div class="flex items-center gap-3">
                <span class="text-3xl">⏳</span>
                <div class="flex-1">
                    <h4 class="font-black text-sm text-rose-950">Sesi Sedang Cooldown (Istirahat Berpikir)</h4>
                    <p class="text-xs text-rose-800 mt-0.5">3 Kesempatan Anda telah habis. Luangkan waktu untuk menganalisis strategi pemecahan masalah.</p>
                    <div class="mt-2 text-xs font-mono-code font-black text-rose-700">
                        Dapat mencoba kembali dalam: <span class="bg-rose-200 px-2 py-0.5 rounded-md text-rose-950" x-text="formatTime(cooldownSeconds)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Bar (Langkah, Waktu, Rekor) -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 p-3 bg-white border-2 border-[#121316] rounded-2xl shadow-[2px_2px_0px_#121316]">
            <div class="flex items-center gap-3 flex-wrap text-xs font-mono-code font-bold">
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-500">LANGKAH:</span>
                    <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-sm" x-text="moves"></span>
                    <span class="text-[10px] text-slate-500 hidden sm:inline">(Optimum: 11)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-500">WAKTU:</span>
                    <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] border border-[#121316] text-[#121316] font-black" x-text="formatTime(timerSeconds)"></span>
                </div>
                <template x-if="hasWonToday">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-400 text-[10px] font-black">
                        ✓ Tuntas Hari Ini
                    </span>
                </template>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="resetCurrentSession()" :disabled="isCooldown || moves === 0" class="btn-tactile-white px-3 py-1 rounded-xl text-xs font-black disabled:opacity-40">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Ulang Posisi
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- ARENA VISUAL SUNGAI & TEPI (RESPONSIVE PC & MOBILE OPTIMIZED) -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="relative w-full rounded-2xl border-2 border-[#121316] overflow-hidden bg-gradient-to-b from-sky-200 via-sky-300 to-sky-400 shadow-[4px_4px_0px_#121316]">
            
            <!-- Gelombang Air Sungai CSS -->
            <div class="absolute inset-0 opacity-25 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 16px 16px;"></div>

            <!-- Kontainer Utama: 3 Kolom (Tepi Barat - Sungai & Perahu - Tepi Timur) -->
            <div class="grid grid-cols-1 md:grid-cols-12 min-h-[380px] sm:min-h-[420px] relative">
                
                <!-- 1. TEPI BARAT (LEFT BANK) -->
                <div class="md:col-span-4 p-4 bg-emerald-100/90 border-b-2 md:border-b-0 md:border-r-2 border-[#121316] flex flex-col justify-between relative">
                    <div class="flex items-center justify-between pb-2 border-b border-[#121316]/20">
                        <div class="flex items-center gap-1.5">
                            <span class="text-base">🏝️</span>
                            <span class="font-black text-xs sm:text-sm text-[#121316]">TEPI BARAT</span>
                        </div>
                        <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md bg-white border border-[#121316]">
                            M: <span x-text="westM"></span> | K: <span x-text="westC"></span>
                        </span>
                    </div>

                    <!-- Area Karakter Tepi Barat -->
                    <div class="py-4 my-auto">
                        <p class="text-[10px] font-mono-code font-bold text-slate-600 mb-2 uppercase tracking-wider text-center sm:text-left">
                            Ketuk karakter untuk naik ke perahu:
                        </p>
                        
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                            <!-- Karakter Misionaris Barat -->
                            <template x-for="i in westM" :key="'wm-' + i">
                                <button type="button" 
                                        @click="boardBoat('M', 'west')"
                                        :disabled="boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="w-12 h-14 sm:w-14 sm:h-16 rounded-2xl bg-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex flex-col items-center justify-center hover:scale-105 active:scale-95 transition disabled:opacity-40 disabled:hover:scale-100 cursor-pointer group"
                                        title="Misionaris (Ilmuwan)">
                                    <span class="text-xl sm:text-2xl group-hover:animate-bounce">🧙‍♂️</span>
                                    <span class="text-[8px] font-mono-code font-black text-indigo-700 uppercase">ILMUWAN</span>
                                </button>
                            </template>

                            <!-- Karakter Kanibal Barat -->
                            <template x-for="i in westC" :key="'wc-' + i">
                                <button type="button" 
                                        @click="boardBoat('C', 'west')"
                                        :disabled="boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="w-12 h-14 sm:w-14 sm:h-16 rounded-2xl bg-amber-100 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex flex-col items-center justify-center hover:scale-105 active:scale-95 transition disabled:opacity-40 disabled:hover:scale-100 cursor-pointer group"
                                        title="Kanibal">
                                    <span class="text-xl sm:text-2xl group-hover:animate-bounce">👹</span>
                                    <span class="text-[8px] font-mono-code font-black text-amber-900 uppercase">KANIBAL</span>
                                </button>
                            </template>

                            <template x-if="westM === 0 && westC === 0">
                                <div class="w-full text-center py-4 text-xs font-mono-code text-slate-500 font-bold italic">
                                    Tepi Barat Kosong ✓
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Tombol Cepat HP untuk Tepi Barat -->
                    <div class="flex gap-1.5 pt-2 border-t border-[#121316]/10 md:hidden">
                        <button type="button" @click="boardBoat('M', 'west')" :disabled="westM === 0 || boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving" class="flex-1 py-1.5 bg-white border border-[#121316] rounded-lg text-[10px] font-black">
                            +1 Ilmuwan
                        </button>
                        <button type="button" @click="boardBoat('C', 'west')" :disabled="westC === 0 || boatPosition !== 'west' || boatPassengers.length >= 2 || isMoving" class="flex-1 py-1.5 bg-amber-200 border border-[#121316] rounded-lg text-[10px] font-black">
                            +1 Kanibal
                        </button>
                    </div>
                </div>

                <!-- 2. SUNGAI & PERAHU (RIVER & BOAT) -->
                <div class="md:col-span-4 p-4 flex flex-col justify-between items-center relative overflow-hidden bg-gradient-to-r from-blue-400/80 via-cyan-400/80 to-blue-400/80">
                    
                    <!-- Status Posisi Perahu -->
                    <div class="text-[10px] font-mono-code font-black px-3 py-1 rounded-full bg-[#121316] text-white shadow-xs z-10">
                        PERAHU DI: <span class="text-yellow-300" x-text="boatPosition === 'west' ? 'TEPI BARAT ◄' : '► TEPI TIMUR'"></span>
                    </div>

                    <!-- Area Perahu Bergerak (Animated Boat) -->
                    <div class="my-auto w-full py-4 flex flex-col items-center justify-center relative z-10">
                        
                        <!-- Wrapper Animasi Perahu -->
                        <div class="w-full flex transition-all duration-700 ease-in-out px-2"
                             :class="boatPosition === 'west' ? 'justify-start' : 'justify-end'">
                            
                            <!-- Bodi Perahu Kayu Neo-Brutalist -->
                            <div class="w-48 sm:w-56 bg-amber-800 border-2 border-[#121316] rounded-2xl p-2.5 shadow-[3px_4px_0px_#121316] flex flex-col items-center gap-1.5 transition-transform"
                                 :class="isMoving ? 'scale-105 animate-pulse' : ''">
                                
                                <div class="w-full flex items-center justify-between text-[9px] font-mono-code font-black text-amber-200 px-1">
                                    <span>KAPASITAS: 2 ORANG</span>
                                    <span x-text="boatPassengers.length + '/2'"></span>
                                </div>

                                <!-- Kursi Penumpang di Perahu -->
                                <div class="grid grid-cols-2 gap-2 w-full">
                                    <!-- Kursi 1 -->
                                    <div class="h-16 rounded-xl border-2 border-dashed border-amber-400/60 bg-amber-900/50 flex items-center justify-center relative overflow-hidden">
                                        <template x-if="boatPassengers.length > 0">
                                            <button type="button" 
                                                    @click="unboardBoat(0)"
                                                    :disabled="isMoving || isCooldown"
                                                    class="w-full h-full flex flex-col items-center justify-center bg-white border border-[#121316] rounded-lg shadow-xs hover:bg-rose-50 cursor-pointer"
                                                    title="Ketuk untuk turunkan ke tepi">
                                                <span class="text-2xl" x-text="boatPassengers[0] === 'M' ? '🧙‍♂️' : '👹'"></span>
                                                <span class="text-[8px] font-mono-code font-black text-slate-700" x-text="boatPassengers[0] === 'M' ? 'ILMUWAN' : 'KANIBAL'"></span>
                                            </button>
                                        </template>
                                        <template x-if="boatPassengers.length === 0">
                                            <span class="text-[9px] font-mono-code text-amber-300/70 font-bold">KOSONG</span>
                                        </template>
                                    </div>

                                    <!-- Kursi 2 -->
                                    <div class="h-16 rounded-xl border-2 border-dashed border-amber-400/60 bg-amber-900/50 flex items-center justify-center relative overflow-hidden">
                                        <template x-if="boatPassengers.length > 1">
                                            <button type="button" 
                                                    @click="unboardBoat(1)"
                                                    :disabled="isMoving || isCooldown"
                                                    class="w-full h-full flex flex-col items-center justify-center bg-white border border-[#121316] rounded-lg shadow-xs hover:bg-rose-50 cursor-pointer"
                                                    title="Ketuk untuk turunkan ke tepi">
                                                <span class="text-2xl" x-text="boatPassengers[1] === 'M' ? '🧙‍♂️' : '👹'"></span>
                                                <span class="text-[8px] font-mono-code font-black text-slate-700" x-text="boatPassengers[1] === 'M' ? 'ILMUWAN' : 'KANIBAL'"></span>
                                            </button>
                                        </template>
                                        <template x-if="boatPassengers.length <= 1">
                                            <span class="text-[9px] font-mono-code text-amber-300/70 font-bold">KOSONG</span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Eksekusi: DAYUNG PERAHU -->
                        <div class="mt-4 flex flex-col items-center gap-1.5 w-full">
                            <button type="button" 
                                    @click="rowBoat()"
                                    :disabled="boatPassengers.length === 0 || isMoving || isCooldown"
                                    class="w-full sm:w-auto px-6 py-3 rounded-2xl text-xs sm:text-sm font-black border-2 border-[#121316] shadow-[3px_3px_0px_#121316] transition-all flex items-center justify-center gap-2"
                                    :class="boatPassengers.length > 0 && !isMoving && !isCooldown ? 'bg-[#fde047] hover:bg-yellow-300 text-[#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none' : 'bg-slate-200 text-slate-400 border-slate-400 cursor-not-allowed shadow-none'">
                                <span class="text-base">⛵</span>
                                <span x-text="isMoving ? 'Sedang Menyeberang...' : (boatPosition === 'west' ? 'DAYUNG KE TIMUR ►' : '◄ DAYUNG KE BARAT')"></span>
                            </button>
                            <span class="text-[10px] font-mono-code font-bold text-slate-800" x-show="boatPassengers.length === 0">
                                ⚠️ Perahu butuh minimal 1 orang pendayung!
                            </span>
                        </div>
                    </div>

                    <div class="text-[10px] font-mono-code font-bold text-slate-700 z-10 text-center">
                        Arus Sungai Cepat ~ Perahu Muat Maks 2
                    </div>
                </div>

                <!-- 3. TEPI TIMUR (RIGHT BANK - GOAL) -->
                <div class="md:col-span-4 p-4 bg-emerald-100/90 border-t-2 md:border-t-0 md:border-l-2 border-[#121316] flex flex-col justify-between relative">
                    <div class="flex items-center justify-between pb-2 border-b border-[#121316]/20">
                        <div class="flex items-center gap-1.5">
                            <span class="text-base">🏁</span>
                            <span class="font-black text-xs sm:text-sm text-[#121316]">TEPI TIMUR (TUJUAN)</span>
                        </div>
                        <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md bg-white border border-[#121316]">
                            M: <span x-text="eastM"></span> | K: <span x-text="eastC"></span>
                        </span>
                    </div>

                    <!-- Area Karakter Tepi Timur -->
                    <div class="py-4 my-auto">
                        <p class="text-[10px] font-mono-code font-bold text-slate-600 mb-2 uppercase tracking-wider text-center sm:text-left">
                            Karakter yang telah selamat sampai:
                        </p>

                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                            <!-- Karakter Misionaris Timur -->
                            <template x-for="i in eastM" :key="'em-' + i">
                                <button type="button" 
                                        @click="boardBoat('M', 'east')"
                                        :disabled="boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="w-12 h-14 sm:w-14 sm:h-16 rounded-2xl bg-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex flex-col items-center justify-center hover:scale-105 active:scale-95 transition disabled:opacity-40 disabled:hover:scale-100 cursor-pointer group"
                                        title="Kembalikan ke perahu">
                                    <span class="text-xl sm:text-2xl group-hover:animate-bounce">🧙‍♂️</span>
                                    <span class="text-[8px] font-mono-code font-black text-indigo-700 uppercase">ILMUWAN</span>
                                </button>
                            </template>

                            <!-- Karakter Kanibal Timur -->
                            <template x-for="i in eastC" :key="'ec-' + i">
                                <button type="button" 
                                        @click="boardBoat('C', 'east')"
                                        :disabled="boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving || isCooldown"
                                        class="w-12 h-14 sm:w-14 sm:h-16 rounded-2xl bg-amber-100 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex flex-col items-center justify-center hover:scale-105 active:scale-95 transition disabled:opacity-40 disabled:hover:scale-100 cursor-pointer group"
                                        title="Kembalikan ke perahu">
                                    <span class="text-xl sm:text-2xl group-hover:animate-bounce">👹</span>
                                    <span class="text-[8px] font-mono-code font-black text-amber-900 uppercase">KANIBAL</span>
                                </button>
                            </template>

                            <template x-if="eastM === 0 && eastC === 0">
                                <div class="w-full text-center py-4 text-xs font-mono-code text-slate-500 font-bold italic">
                                    Belum ada yang menyeberang
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Tombol Cepat HP untuk Tepi Timur -->
                    <div class="flex gap-1.5 pt-2 border-t border-[#121316]/10 md:hidden">
                        <button type="button" @click="boardBoat('M', 'east')" :disabled="eastM === 0 || boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving" class="flex-1 py-1.5 bg-white border border-[#121316] rounded-lg text-[10px] font-black">
                            +1 Ilmuwan
                        </button>
                        <button type="button" @click="boardBoat('C', 'east')" :disabled="eastC === 0 || boatPosition !== 'east' || boatPassengers.length >= 2 || isMoving" class="flex-1 py-1.5 bg-amber-200 border border-[#121316] rounded-lg text-[10px] font-black">
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

            // Animasi perahu berlayar selama 700ms
            setTimeout(() => {
                this.boatPosition = this.boatPosition === 'west' ? 'east' : 'west';
                this.isMoving = false;

                // Evaluasi kondisi bahaya di kedua tepi sungai
                this.evaluateBankSafety();
            }, 700);
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
