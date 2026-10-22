<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- 🚰 PembdaCHALLENGE: Teka-Teki Tabung Air (Water Jug Riddle - Die Hard 3) -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div x-data="waterJugGame()" x-init="init()" class="w-full">
    
    <!-- Banner Cooldown (Jika Nyawa Habis) -->
    <div x-show="isCooldown" class="mb-3 p-3.5 rounded-2xl bg-rose-50 border-2 border-rose-600 text-rose-900 shadow-[3px_3px_0px_#e11d48]">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⏳</span>
            <div class="flex-1">
                <h4 class="font-black text-sm text-rose-950">Sesi Sedang Cooldown (Istirahat Berpikir)</h4>
                <p class="text-xs text-rose-800 mt-0.5">Kesempatan Anda telah habis. Luangkan waktu sejenak untuk menganalisis strategi takaran matematis.</p>
                <div class="mt-1.5 text-xs font-mono-code font-black text-rose-700">
                    Dapat mencoba kembali dalam: <span class="bg-rose-200 px-2 py-0.5 rounded-md text-rose-950" x-text="formatTime(cooldownSeconds)"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Toast Notifikasi Aksi -->
    <div x-show="alertToast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-3 p-2.5 sm:p-3 rounded-xl border-2 border-[#121316] shadow-[3px_3px_0px_#121316] flex items-center justify-between gap-2 text-xs font-bold"
         :class="alertToast.isSuccess ? 'bg-emerald-500 text-white' : 'bg-[#fde047] text-[#121316]'">
        <div class="flex items-center gap-2">
            <span class="text-base" x-text="alertToast.isSuccess ? '🎉' : '💡'"></span>
            <span x-text="alertToast.message"></span>
        </div>
        <button type="button" @click="alertToast.show = false" class="text-inherit hover:opacity-75 text-sm font-black px-1">✕</button>
    </div>

    <!-- Top Dashboard: Mode Selector, Target, Stats, Undo & Reset -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 mb-3 p-2.5 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316]">
        
        <!-- Pilihan Tingkat Kesulitan / Mode Tabung -->
        <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap">
            <span class="text-[10px] font-mono-code font-bold text-slate-500 mr-1 hidden sm:inline">MODE:</span>
            <button type="button" 
                    @click="changeMode(1)" 
                    :disabled="isCooldown"
                    class="px-2.5 py-1 rounded-lg text-xs font-black border-2 border-[#121316] transition-all cursor-pointer"
                    :class="modeIndex === 1 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                <span>💣 Die Hard 3 (3L & 5L)</span>
            </button>
            <button type="button" 
                    @click="changeMode(2)" 
                    :disabled="isCooldown"
                    class="px-2.5 py-1 rounded-lg text-xs font-black border-2 border-[#121316] transition-all cursor-pointer"
                    :class="modeIndex === 2 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                <span>🧪 Lab Kimia (4L & 9L)</span>
            </button>
            <button type="button" 
                    @click="changeMode(3)" 
                    :disabled="isCooldown"
                    class="px-2.5 py-1 rounded-lg text-xs font-black border-2 border-[#121316] transition-all cursor-pointer"
                    :class="modeIndex === 3 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                <span>⚖️ Alkimia (5L & 7L)</span>
            </button>
        </div>

        <!-- Info Langkah, Target, & Waktu -->
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap text-xs font-mono-code font-bold">
            <!-- Target Sasaran -->
            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-amber-100 border border-amber-400 rounded-lg text-amber-950 font-black shadow-2xs">
                <span>🎯 TARGET:</span>
                <span class="text-sm text-rose-600 underline" x-text="targetLiters + ' LITER'"></span>
            </div>

            <!-- Langkah -->
            <div class="flex items-center gap-1.5" title="Jumlah langkah yang telah diambil">
                <span class="text-slate-500 text-[11px]">LANGKAH:</span>
                <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="moves"></span>
                <span class="text-[10px] text-slate-500" x-text="'(Opt: ' + optimalMoves + ')'"></span>
            </div>

            <!-- Waktu -->
            <div class="flex items-center gap-1.5">
                <span class="text-slate-500 text-[11px]">WAKTU:</span>
                <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="formatTime(timerSeconds)"></span>
            </div>
        </div>

        <!-- Tombol Aksi: Undo, Reset, & Aturan -->
        <div class="flex items-center gap-1.5">
            <button type="button" 
                    @click="undoMove()" 
                    :disabled="history.length === 0 || isCooldown || victoryModal.show" 
                    class="btn-tactile-white px-2 sm:px-2.5 py-1 rounded-lg text-xs font-black disabled:opacity-40 cursor-pointer"
                    title="Batalkan 1 langkah penuangan terakhir">
                <i class="fa-solid fa-arrow-rotate-left mr-1"></i> Undo
            </button>
            <button type="button" 
                    @click="resetGame()" 
                    :disabled="isCooldown || moves === 0" 
                    class="btn-tactile-white px-2 sm:px-2.5 py-1 rounded-lg text-xs font-black disabled:opacity-40 cursor-pointer"
                    title="Kosongkan kedua tabung ke posisi awal">
                <i class="fa-solid fa-rotate-left mr-1"></i> Reset
            </button>
            <button type="button" 
                    @click="showRulesModal = true" 
                    class="p-1 sm:px-2 py-1 rounded-lg bg-indigo-50 border border-indigo-300 text-indigo-900 text-xs font-black hover:bg-indigo-100 transition cursor-pointer"
                    title="Pelajari Aturan & Rahasia Matematika Die Hard 3">
                <i class="fa-solid fa-circle-question"></i>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ARENA VISUAL TABUNG AIR: 2 SILINDER KACA & KRAN AIR DINAMIS 3D      -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="relative w-full rounded-2xl border-2 border-[#121316] bg-gradient-to-b from-sky-100/70 via-slate-50 to-blue-100/60 p-3 sm:p-5 overflow-hidden shadow-[4px_4px_0px_#121316] select-none">
        
        <!-- Instruksi Singkat & Status Air -->
        <div class="mb-3 flex items-center justify-between text-[10px] sm:text-xs font-mono-code flex-wrap gap-2">
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/90 border border-[#121316] text-[#121316] font-black shadow-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 animate-ping"></span>
                <span>Tugas: Takar Tepat <span class="text-rose-600 font-black text-sm" x-text="targetLiters + ' Liter'"></span> pada salah satu tabung!</span>
            </div>
            <div class="text-slate-700 font-bold hidden sm:block">
                <span>Sumber Air:</span> <span class="text-emerald-700 font-black">Tak Terbatas (Kran Publik) ✓</span>
            </div>
        </div>

        <!-- GRID 2 TABUNG AIR DENGAN PUSAT INTERAKSI MENUANG (POURING CENTER) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-8 items-end relative py-2 sm:py-4 max-w-3xl mx-auto">
            
            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- 1. TABUNG A (KAPASITAS KECIL / 3 LITER)                     -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="flex flex-col items-center justify-end bg-white/80 backdrop-blur-xs p-3.5 sm:p-4 rounded-2xl border-2 border-[#121316] shadow-[3px_3px_0px_#121316] relative">
                
                <!-- Header Tabung A -->
                <div class="w-full flex items-center justify-between mb-2 pb-1.5 border-b border-[#121316]/15 font-mono-code">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs">🧪</span>
                        <span class="font-black text-xs sm:text-sm text-[#121316]">TABUNG A</span>
                        <span class="text-[9px] px-1.5 py-0.2 rounded-full bg-indigo-100 text-indigo-900 border border-indigo-300 font-bold" x-text="'Maks ' + maxA + 'L'"></span>
                    </div>
                    <div class="text-xs font-black" :class="curA === targetLiters ? 'text-emerald-600 font-mono text-sm' : 'text-slate-800'">
                        <span x-text="curA"></span> / <span x-text="maxA"></span> Liter
                    </div>
                </div>

                <!-- Kran Air Atas (Faucet A) -->
                <div class="flex flex-col items-center mb-1">
                    <div class="w-5 h-5 rounded-md bg-amber-600 border border-[#121316] flex items-center justify-center text-[10px] text-white shadow-xs">
                        <i class="fa-solid fa-faucet-drip"></i>
                    </div>
                    <!-- Animasi Pancuran Air Mengalir saat Diisi -->
                    <div x-show="isFillingA" 
                         x-transition.opacity 
                         style="width: 6px; height: 30px; background: linear-gradient(to bottom, #38bdf8, #0284c7); border-radius: 9999px;" 
                         class="animate-pulse"></div>
                    <div x-show="!isFillingA" style="width: 2px; height: 10px; background: rgba(0,0,0,0.1);"></div>
                </div>

                <!-- Silinder Tabung Kaca Transparan Tabung A -->
                <div style="width: 140px; height: 200px; position: relative; border: 3px solid #121316; border-radius: 0 0 16px 16px; background: rgba(240, 249, 255, 0.6); overflow: hidden; box-shadow: inset 0 0 15px rgba(186, 230, 253, 0.5);"
                     class="group">
                    
                    <!-- Garis-Garis Takaran Liter (1L, 2L, 3L) -->
                    <template x-for="mark in maxA" :key="'mark-a-' + mark">
                        <div style="position: absolute; left: 0; right: 0; display: flex; items-center; justify-content: space-between; padding: 0 6px;"
                             :style="'bottom: ' + ((mark / maxA) * 100) + '%; transform: translateY(50%); z-index: 20; pointer-events: none;'">
                            <span style="height: 1.5px; width: 14px; background: rgba(18, 19, 22, 0.45);"></span>
                            <span class="text-[8px] font-mono-code font-bold text-slate-500" x-text="mark + 'L'"></span>
                            <span style="height: 1.5px; width: 14px; background: rgba(18, 19, 22, 0.45);"></span>
                        </div>
                    </template>

                    <!-- Garis Target Khusus (Jika target berada di rentang tabung ini) -->
                    <template x-if="targetLiters <= maxA">
                        <div style="position: absolute; left: 0; right: 0; z-index: 25; pointer-events: none;"
                             :style="'bottom: ' + ((targetLiters / maxA) * 100) + '%; transform: translateY(50%);'">
                            <div style="border-top: 2.5px dashed #dc2626; width: 100%; position: relative;">
                                <span style="position: absolute; right: 4px; top: -14px; font-size: 7.5px; font-weight: 900; background: #dc2626; color: #ffffff; padding: 1px 4px; border-radius: 4px;">
                                    TARGET <span x-text="targetLiters + 'L'"></span>
                                </span>
                            </div>
                        </div>
                    </template>

                    <!-- CAIRAN AIR BIRU DI DALAM TABUNG A (DINAMIS DENGAN KETINGGIAN) -->
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1); z-index: 10;"
                         :style="'height: ' + ((curA / maxA) * 100) + '%;'"
                         :class="curA === targetLiters ? 'bg-gradient-to-t from-emerald-600 via-teal-500 to-emerald-400' : 'bg-gradient-to-t from-sky-600 via-cyan-500 to-sky-400'">
                        
                        <!-- Permukaan Air (Riak Gelombang Atas) -->
                        <div x-show="curA > 0" 
                             style="width: 100%; height: 8px; background: rgba(255, 255, 255, 0.55); border-radius: 50%; position: absolute; top: -4px;"></div>
                        
                        <!-- Gelembung Air Halus -->
                        <div x-show="curA > 0" class="w-full h-full relative overflow-hidden opacity-60">
                            <span style="position: absolute; left: 25%; bottom: 15%; width: 4px; height: 4px; background: #ffffff; border-radius: 50%;" class="animate-ping"></span>
                            <span style="position: absolute; left: 65%; bottom: 40%; width: 6px; height: 6px; background: #ffffff; border-radius: 50%;" class="animate-bounce"></span>
                        </div>
                    </div>

                    <!-- Label Status di Tengah Air -->
                    <div class="absolute inset-0 flex items-center justify-center z-30 pointer-events-none">
                        <span class="text-sm font-mono-code font-black px-2 py-0.5 rounded-lg border border-[#121316] shadow-xs"
                              :class="curA === targetLiters ? 'bg-[#fde047] text-[#121316] ring-2 ring-emerald-500' : 'bg-white/90 text-[#121316]'">
                            <span x-text="curA + ' / ' + maxA + ' L'"></span>
                        </span>
                    </div>

                </div>

                <!-- Alas Dudukan Kayu Tabung A -->
                <div style="width: 160px; height: 10px; background: #78350f; border: 2px solid #121316; border-radius: 4px; margin-top: 2px;" class="shadow-xs"></div>

                <!-- DECK KONTROL AKSI TABUNG A -->
                <div class="mt-3 grid grid-cols-3 gap-1.5 w-full">
                    <!-- Tombol 1: Isi Penuh -->
                    <button type="button" 
                            @click="fillJug('A')" 
                            :disabled="curA === maxA || isAnimating || isCooldown"
                            class="py-1.5 px-1 rounded-xl text-[10px] font-black border-2 border-[#121316] transition-all flex flex-col items-center justify-center gap-0.5 disabled:opacity-40 cursor-pointer"
                            :class="curA < maxA ? 'bg-sky-100 hover:bg-sky-200 text-sky-950 shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none' : 'bg-slate-100 text-slate-400 border-slate-300 shadow-none'">
                        <span>🚰</span>
                        <span>Isi Penuh</span>
                    </button>

                    <!-- Tombol 2: Tuang ke B -->
                    <button type="button" 
                            @click="pourJug('A', 'B')" 
                            :disabled="curA === 0 || curB === maxB || isAnimating || isCooldown"
                            class="py-1.5 px-1 rounded-xl text-[10px] font-black border-2 border-[#121316] transition-all flex flex-col items-center justify-center gap-0.5 disabled:opacity-40 cursor-pointer"
                            :class="curA > 0 && curB < maxB ? 'bg-[#fde047] hover:bg-yellow-300 text-[#121316] shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none' : 'bg-slate-100 text-slate-400 border-slate-300 shadow-none'">
                        <span>🌊</span>
                        <span>Tuang ke B ►</span>
                    </button>

                    <!-- Tombol 3: Kosongkan -->
                    <button type="button" 
                            @click="emptyJug('A')" 
                            :disabled="curA === 0 || isAnimating || isCooldown"
                            class="py-1.5 px-1 rounded-xl text-[10px] font-black border-2 border-[#121316] transition-all flex flex-col items-center justify-center gap-0.5 disabled:opacity-40 cursor-pointer"
                            :class="curA > 0 ? 'bg-rose-50 hover:bg-rose-100 text-rose-900 shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none' : 'bg-slate-100 text-slate-400 border-slate-300 shadow-none'">
                        <span>🗑️</span>
                        <span>Kosongkan</span>
                    </button>
                </div>

            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- 2. TABUNG B (KAPASITAS BESAR / 5 LITER)                     -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="flex flex-col items-center justify-end bg-white/80 backdrop-blur-xs p-3.5 sm:p-4 rounded-2xl border-2 border-[#121316] shadow-[3px_3px_0px_#121316] relative">
                
                <!-- Header Tabung B -->
                <div class="w-full flex items-center justify-between mb-2 pb-1.5 border-b border-[#121316]/15 font-mono-code">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs">🧪</span>
                        <span class="font-black text-xs sm:text-sm text-[#121316]">TABUNG B</span>
                        <span class="text-[9px] px-1.5 py-0.2 rounded-full bg-amber-100 text-amber-900 border border-amber-300 font-bold" x-text="'Maks ' + maxB + 'L'"></span>
                    </div>
                    <div class="text-xs font-black" :class="curB === targetLiters ? 'text-emerald-600 font-mono text-sm' : 'text-slate-800'">
                        <span x-text="curB"></span> / <span x-text="maxB"></span> Liter
                    </div>
                </div>

                <!-- Kran Air Atas (Faucet B) -->
                <div class="flex flex-col items-center mb-1">
                    <div class="w-5 h-5 rounded-md bg-amber-600 border border-[#121316] flex items-center justify-center text-[10px] text-white shadow-xs">
                        <i class="fa-solid fa-faucet-drip"></i>
                    </div>
                    <!-- Animasi Pancuran Air Mengalir saat Diisi -->
                    <div x-show="isFillingB" 
                         x-transition.opacity 
                         style="width: 6px; height: 30px; background: linear-gradient(to bottom, #38bdf8, #0284c7); border-radius: 9999px;" 
                         class="animate-pulse"></div>
                    <div x-show="!isFillingB" style="width: 2px; height: 10px; background: rgba(0,0,0,0.1);"></div>
                </div>

                <!-- Silinder Tabung Kaca Transparan Tabung B (Lebih Tinggi) -->
                <div style="width: 155px; height: 230px; position: relative; border: 3px solid #121316; border-radius: 0 0 16px 16px; background: rgba(240, 249, 255, 0.6); overflow: hidden; box-shadow: inset 0 0 15px rgba(186, 230, 253, 0.5);"
                     class="group">
                    
                    <!-- Garis-Garis Takaran Liter (1L, 2L, 3L, 4L, 5L) -->
                    <template x-for="mark in maxB" :key="'mark-b-' + mark">
                        <div style="position: absolute; left: 0; right: 0; display: flex; items-center; justify-content: space-between; padding: 0 6px;"
                             :style="'bottom: ' + ((mark / maxB) * 100) + '%; transform: translateY(50%); z-index: 20; pointer-events: none;'">
                            <span style="height: 1.5px; width: 14px; background: rgba(18, 19, 22, 0.45);"></span>
                            <span class="text-[8px] font-mono-code font-bold text-slate-500" x-text="mark + 'L'"></span>
                            <span style="height: 1.5px; width: 14px; background: rgba(18, 19, 22, 0.45);"></span>
                        </div>
                    </template>

                    <!-- Garis Target Khusus (4 Liter di Tabung 5L) -->
                    <template x-if="targetLiters <= maxB">
                        <div style="position: absolute; left: 0; right: 0; z-index: 25; pointer-events: none;"
                             :style="'bottom: ' + ((targetLiters / maxB) * 100) + '%; transform: translateY(50%);'">
                            <div style="border-top: 2.5px dashed #dc2626; width: 100%; position: relative;">
                                <span style="position: absolute; right: 4px; top: -14px; font-size: 7.5px; font-weight: 900; background: #dc2626; color: #ffffff; padding: 1px 4px; border-radius: 4px;">
                                    TARGET <span x-text="targetLiters + 'L'"></span>
                                </span>
                            </div>
                        </div>
                    </template>

                    <!-- CAIRAN AIR BIRU DI DALAM TABUNG B (DINAMIS DENGAN KETINGGIAN) -->
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1); z-index: 10;"
                         :style="'height: ' + ((curB / maxB) * 100) + '%;'"
                         :class="curB === targetLiters ? 'bg-gradient-to-t from-emerald-600 via-teal-500 to-emerald-400' : 'bg-gradient-to-t from-sky-600 via-cyan-500 to-sky-400'">
                        
                        <!-- Permukaan Air (Riak Gelombang Atas) -->
                        <div x-show="curB > 0" 
                             style="width: 100%; height: 8px; background: rgba(255, 255, 255, 0.55); border-radius: 50%; position: absolute; top: -4px;"></div>
                        
                        <!-- Gelembung Air Halus -->
                        <div x-show="curB > 0" class="w-full h-full relative overflow-hidden opacity-60">
                            <span style="position: absolute; left: 30%; bottom: 20%; width: 5px; height: 5px; background: #ffffff; border-radius: 50%;" class="animate-ping"></span>
                            <span style="position: absolute; left: 70%; bottom: 35%; width: 7px; height: 7px; background: #ffffff; border-radius: 50%;" class="animate-bounce"></span>
                        </div>
                    </div>

                    <!-- Label Status di Tengah Air -->
                    <div class="absolute inset-0 flex items-center justify-center z-30 pointer-events-none">
                        <span class="text-sm font-mono-code font-black px-2 py-0.5 rounded-lg border border-[#121316] shadow-xs"
                              :class="curB === targetLiters ? 'bg-[#fde047] text-[#121316] ring-2 ring-emerald-500' : 'bg-white/90 text-[#121316]'">
                            <span x-text="curB + ' / ' + maxB + ' L'"></span>
                        </span>
                    </div>

                </div>

                <!-- Alas Dudukan Kayu Tabung B -->
                <div style="width: 175px; height: 10px; background: #78350f; border: 2px solid #121316; border-radius: 4px; margin-top: 2px;" class="shadow-xs"></div>

                <!-- DECK KONTROL AKSI TABUNG B -->
                <div class="mt-3 grid grid-cols-3 gap-1.5 w-full">
                    <!-- Tombol 1: Isi Penuh -->
                    <button type="button" 
                            @click="fillJug('B')" 
                            :disabled="curB === maxB || isAnimating || isCooldown"
                            class="py-1.5 px-1 rounded-xl text-[10px] font-black border-2 border-[#121316] transition-all flex flex-col items-center justify-center gap-0.5 disabled:opacity-40 cursor-pointer"
                            :class="curB < maxB ? 'bg-sky-100 hover:bg-sky-200 text-sky-950 shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none' : 'bg-slate-100 text-slate-400 border-slate-300 shadow-none'">
                        <span>🚰</span>
                        <span>Isi Penuh</span>
                    </button>

                    <!-- Tombol 2: Tuang ke A -->
                    <button type="button" 
                            @click="pourJug('B', 'A')" 
                            :disabled="curB === 0 || curA === maxA || isAnimating || isCooldown"
                            class="py-1.5 px-1 rounded-xl text-[10px] font-black border-2 border-[#121316] transition-all flex flex-col items-center justify-center gap-0.5 disabled:opacity-40 cursor-pointer"
                            :class="curB > 0 && curA < maxA ? 'bg-[#fde047] hover:bg-yellow-300 text-[#121316] shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none' : 'bg-slate-100 text-slate-400 border-slate-300 shadow-none'">
                        <span>🌊</span>
                        <span>◄ Tuang ke A</span>
                    </button>

                    <!-- Tombol 3: Kosongkan -->
                    <button type="button" 
                            @click="emptyJug('B')" 
                            :disabled="curB === 0 || isAnimating || isCooldown"
                            class="py-1.5 px-1 rounded-xl text-[10px] font-black border-2 border-[#121316] transition-all flex flex-col items-center justify-center gap-0.5 disabled:opacity-40 cursor-pointer"
                            :class="curB > 0 ? 'bg-rose-50 hover:bg-rose-100 text-rose-900 shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none' : 'bg-slate-100 text-slate-400 border-slate-300 shadow-none'">
                        <span>🗑️</span>
                        <span>Kosongkan</span>
                    </button>
                </div>

            </div>

        </div>

    </div>

    <!-- Footer Bar Tips STEAM & Matematika Die Hard 3 -->
    <div class="mt-3 p-2.5 bg-indigo-50/60 border border-indigo-200 rounded-xl flex flex-wrap items-center justify-between gap-2 text-xs text-indigo-900 font-mono-code">
        <div class="flex items-center gap-2">
            <span class="text-sm">🧮</span>
            <span class="font-bold">Teorema Bézout & Aljabar Linier:</span>
            <span class="bg-white px-2 py-0.5 rounded border border-indigo-300 font-black text-indigo-950">
                <span x-text="maxA"></span>x + <span x-text="maxB"></span>y = <span x-text="targetLiters"></span> (Solusi Bilangan Bulat Positif)
            </span>
        </div>
        <div class="text-[11px] text-indigo-700">
            Karakteristik: State Space Search (BFS Graph Algorithm)
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL KEMENANGAN TABUNG AIR (DIE HARD 3 VICTORY)                   -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="victoryModal.show" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-[#121316]/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white border-2 border-[#121316] rounded-3xl p-5 sm:p-6 max-w-md w-full shadow-[6px_6px_0px_#121316] text-center" @click.stop>
            <div class="w-16 h-16 rounded-2xl bg-emerald-400 border-2 border-[#121316] shadow-[3px_3px_0px_#121316] flex items-center justify-center text-3xl mx-auto mb-3">
                🚰
            </div>

            <div class="flex justify-center gap-1 mb-2">
                <template x-for="i in 3" :key="i">
                    <span class="text-2xl" :class="i <= victoryModal.stars ? 'text-amber-400' : 'text-slate-300'">★</span>
                </template>
            </div>

            <h3 class="font-black text-xl text-[#121316] mb-1">TARGET AIR TEPAT TERUKUR!</h3>
            <p class="text-xs font-mono-code font-bold text-indigo-600 mb-3" x-text="victoryModal.rankTitle"></p>

            <div class="p-3 bg-sky-50 rounded-2xl border-2 border-sky-300 mb-4 text-xs font-bold text-sky-950 space-y-1">
                <div>Jumlah Langkah: <span class="font-black text-[#121316]" x-text="moves"></span> (Optimum: <span x-text="optimalMoves"></span>)</div>
                <div>Waktu Selesai: <span class="font-black text-[#121316]" x-text="formatTime(timerSeconds)"></span></div>
                <template x-if="victoryModal.points > 0">
                    <div class="text-emerald-700 font-black mt-1 text-sm">
                        🎉 +<span x-text="victoryModal.points"></span> Poin Reputasi STEAM Diperoleh!
                    </div>
                </template>
                <template x-if="victoryModal.points === 0">
                    <div class="text-slate-600 text-[11px] mt-1">
                        ✓ Rekor hari ini telah tercatat. Mainkan level lain untuk tantangan baru!
                    </div>
                </template>
            </div>

            <div class="flex gap-2">
                <button type="button" @click="resetGame(); victoryModal.show = false" class="btn-tactile-white flex-1 py-2 rounded-xl text-xs font-black cursor-pointer">
                    Main Lagi
                </button>
                <button type="button" @click="changeMode(modeIndex < 3 ? modeIndex + 1 : 1); victoryModal.show = false" class="btn-tactile-yellow flex-1 py-2 rounded-xl text-xs font-black cursor-pointer">
                    Level Berikutnya ►
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL ATURAN & TEORI MATEMATIKA DIE HARD WATER JUG                 -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="showRulesModal" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-[#121316]/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white border-2 border-[#121316] rounded-3xl p-5 sm:p-6 max-w-lg w-full shadow-[6px_6px_0px_#121316]" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b-2 border-[#121316] mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🚰</span>
                    <h3 class="font-black text-sm sm:text-base text-[#121316]">Hukum Teka-Teki Tabung Air (Die Hard 3)</h3>
                </div>
                <button type="button" @click="showRulesModal = false" class="w-7 h-7 rounded-lg border-2 border-[#121316] bg-slate-100 hover:bg-slate-200 text-xs font-black flex items-center justify-center cursor-pointer">
                    ✕
                </button>
            </div>

            <div class="space-y-3 text-xs text-slate-700 font-sans leading-relaxed max-h-[60vh] overflow-y-auto pr-1">
                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200">
                    <h4 class="font-black text-amber-950 mb-1">🎯 Cerita Legendaris:</h4>
                    <p>Di film <em>Die Hard: With a Vengeance (Die Hard 3)</em>, Bruce Willis dan Samuel L. Jackson diberi jeriken 3 Galon dan 5 Galon di taman publik, dengan instruksi untuk menaruh tepat <strong>4 Galon air</strong> pada timbangan bom sebelum waktu habis!</p>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <h4 class="font-black text-slate-900 mb-1.5">⚖️ 3 Operasi yang Diperbolehkan:</h4>
                    <ul class="list-disc pl-4 space-y-1 text-slate-700">
                        <li><strong>Isi Penuh:</strong> Isi tabung dari kran sampai batas maksimumnya.</li>
                        <li><strong>Tuang Antar-Tabung:</strong> Tuangkan air dari satu tabung ke tabung lain sampai tabung penerima penuh ATAU tabung penuang habis.</li>
                        <li><strong>Kosongkan:</strong> Buang seluruh air di dalam salah satu tabung hingga 0 Liter.</li>
                    </ul>
                </div>

                <div class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-950">
                    <h4 class="font-black text-indigo-900 mb-1">💡 Rahasia Matematika & Algoritma:</h4>
                    <p class="text-[11px]">
                        Teka-teki ini dijamin dapat diselesaikan jika dan hanya jika target $Z$ merupakan kelipatan dari $FPB(X, Y)$. Karena $FPB(3, 5) = 1$, maka volume berapa pun dari 1 hingga 5 liter dapat diukur secara eksak!
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 flex justify-end">
                <button type="button" @click="showRulesModal = false" class="btn-tactile-yellow px-4 py-2 rounded-xl text-xs font-black cursor-pointer">
                    Mengerti, Mulai Beraksi! 🚀
                </button>
            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- SCRIPT ALPINE.JS ENGINE TEKA-TEKI TABUNG AIR (WATER JUG ENGINE)       -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<script>
function waterJugGame() {
    return {
        modeIndex: 1, // 1: (3L, 5L -> 4L), 2: (4L, 9L -> 6L), 3: (5L, 7L -> 3L)
        maxA: 3,
        maxB: 5,
        targetLiters: 4,
        curA: 0,
        curB: 0,
        moves: 0,
        timerSeconds: 0,
        timerInterval: null,
        isCooldown: false,
        cooldownSeconds: 0,
        hasWonToday: false,
        isAnimating: false,
        isFillingA: false,
        isFillingB: false,
        history: [], // Riwayat undo: [ { a: 0, b: 0 } ]
        
        alertToast: {
            show: false,
            message: '',
            isSuccess: false,
            timeout: null
        },

        victoryModal: {
            show: false,
            stars: 3,
            points: 50,
            rankTitle: '',
            message: ''
        },

        showRulesModal: false,

        get optimalMoves() {
            if (this.modeIndex === 1) return 6; // 3L & 5L -> 4L
            if (this.modeIndex === 2) return 8; // 4L & 9L -> 6L
            return 6; // 5L & 7L -> 3L
        },

        init() {
            this.setModeParams();
            this.resetGame();
            this.fetchStatus();
            this.startTimer();
        },

        setModeParams() {
            if (this.modeIndex === 1) {
                this.maxA = 3;
                this.maxB = 5;
                this.targetLiters = 4;
            } else if (this.modeIndex === 2) {
                this.maxA = 4;
                this.maxB = 9;
                this.targetLiters = 6;
            } else {
                this.maxA = 5;
                this.maxB = 7;
                this.targetLiters = 3;
            }
        },

        changeMode(newMode) {
            if (this.isCooldown) return;
            this.modeIndex = newMode;
            this.setModeParams();
            this.resetGame();
            this.timerSeconds = 0;
            this.victoryModal.show = false;
        },

        resetGame() {
            this.curA = 0;
            this.curB = 0;
            this.moves = 0;
            this.history = [];
            this.alertToast.show = false;
            this.isAnimating = false;
        },

        // Simpan snapshot untuk fitur Undo
        saveHistory() {
            this.history.push({ a: this.curA, b: this.curB });
        },

        undoMove() {
            if (this.history.length === 0 || this.isAnimating || this.isCooldown || this.victoryModal.show) return;
            const lastState = this.history.pop();
            this.curA = lastState.a;
            this.curB = lastState.b;
            this.moves--;
            this.alertToast.show = false;
        },

        // Aksi 1: Isi Tabung Penuh
        fillJug(jug) {
            if (this.isAnimating || this.isCooldown || this.victoryModal.show) return;

            if (jug === 'A') {
                if (this.curA === this.maxA) return;
                this.saveHistory();
                this.isAnimating = true;
                this.isFillingA = true;
                setTimeout(() => {
                    this.curA = this.maxA;
                    this.moves++;
                    this.isFillingA = false;
                    this.isAnimating = false;
                    this.checkVictory();
                }, 350);
            } else {
                if (this.curB === this.maxB) return;
                this.saveHistory();
                this.isAnimating = true;
                this.isFillingB = true;
                setTimeout(() => {
                    this.curB = this.maxB;
                    this.moves++;
                    this.isFillingB = false;
                    this.isAnimating = false;
                    this.checkVictory();
                }, 350);
            }
        },

        // Aksi 2: Kosongkan Tabung
        emptyJug(jug) {
            if (this.isAnimating || this.isCooldown || this.victoryModal.show) return;

            if (jug === 'A') {
                if (this.curA === 0) return;
                this.saveHistory();
                this.curA = 0;
                this.moves++;
            } else {
                if (this.curB === 0) return;
                this.saveHistory();
                this.curB = 0;
                this.moves++;
            }
        },

        // Aksi 3: Tuangkan Air dari Satu Tabung ke Tabung Lainnya
        pourJug(from, to) {
            if (this.isAnimating || this.isCooldown || this.victoryModal.show) return;

            if (from === 'A' && to === 'B') {
                if (this.curA === 0 || this.curB === this.maxB) return;
                this.saveHistory();
                
                // Kapasitas sisa di Tabung B
                const spaceB = this.maxB - this.curB;
                const pourAmount = Math.min(this.curA, spaceB);
                
                this.curA -= pourAmount;
                this.curB += pourAmount;
                this.moves++;
                this.checkVictory();
            } else if (from === 'B' && to === 'A') {
                if (this.curB === 0 || this.curA === this.maxA) return;
                this.saveHistory();

                // Kapasitas sisa di Tabung A
                const spaceA = this.maxA - this.curA;
                const pourAmount = Math.min(this.curB, spaceA);

                this.curB -= pourAmount;
                this.curA += pourAmount;
                this.moves++;
                this.checkVictory();
            }
        },

        checkVictory() {
            if (this.curA === this.targetLiters || this.curB === this.targetLiters) {
                this.handleVictory();
            }
        },

        async handleVictory() {
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
                        game_key: 'water_jug',
                        moves: this.moves,
                        lives_left: 3,
                        seconds_taken: Math.max(3, this.timerSeconds)
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
                }
            } catch (e) {
                console.error('Submit Water Jug victory error:', e);
            }
        },

        async fetchStatus() {
            try {
                const res = await fetch('/forum/challenge/status?game_key=water_jug', {
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
                console.warn('Gagal memuat status Water Jug:', e);
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
            const timer = setInterval(() => {
                if (this.cooldownSeconds > 0) {
                    this.cooldownSeconds--;
                } else {
                    this.isCooldown = false;
                    clearInterval(timer);
                }
            }, 1000);
        },

        formatTime(sec) {
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
    };
}
</script>
