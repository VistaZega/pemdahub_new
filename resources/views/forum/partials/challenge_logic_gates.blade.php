<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- 🔌 PembdaCHALLENGE: Sirkuit Gerbang Logika IT (Logic Gates Master)       -->
<!-- Tema Terang & Kontras Tinggi (High Contrast STEAM Workbench)             -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<style>
@keyframes bulb-pulse-glow {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 25px #fde047, 0 0 50px rgba(234, 179, 8, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 40px #facc15, 0 0 70px rgba(250, 204, 21, 0.65);
    }
}
.bulb-glow-active {
    animation: bulb-pulse-glow 1.6s infinite ease-in-out;
}

#pembda-challenge.mobile-landscape-active .logic-circuit-row,
#pembda-challenge.mobile-physical-landscape .logic-circuit-row {
    flex-direction: row !important;
    gap: 6px !important;
}
#pembda-challenge.mobile-landscape-active .logic-circuit-row > div,
#pembda-challenge.mobile-physical-landscape .logic-circuit-row > div {
    min-width: 0 !important;
    padding: 6px !important;
}
#pembda-challenge.mobile-landscape-active .circuit-bulb,
#pembda-challenge.mobile-physical-landscape .circuit-bulb {
    width: 44px !important;
    height: 44px !important;
}
#pembda-challenge.mobile-landscape-active .circuit-bulb i,
#pembda-challenge.mobile-physical-landscape .circuit-bulb i {
    font-size: 20px !important;
}
@media (max-height: 520px) and (orientation: landscape) {
    #pembda-challenge .logic-circuit-row {
        flex-direction: row !important;
        gap: 6px !important;
    }
    #pembda-challenge .logic-circuit-row > div {
        min-width: 0 !important;
        padding: 6px !important;
    }
    #pembda-challenge .circuit-bulb {
        width: 44px !important;
        height: 44px !important;
    }
    #pembda-challenge .circuit-bulb i {
        font-size: 20px !important;
    }
}
</style>

<div x-data="logicGatesGame()" x-init="init()" class="w-full">
    
    <!-- Banner Cooldown (Jika Nyawa Habis) -->
    <div x-show="isCooldown" class="mb-2 p-3 rounded-2xl bg-rose-50 border-2 border-rose-600 text-rose-900 shadow-[3px_3px_0px_#e11d48]">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⏳</span>
            <div class="flex-1">
                <h4 class="font-black text-xs sm:text-sm text-rose-950">Sirkuit Mengalami Konsleting (Cooldown)</h4>
                <p class="text-xs text-rose-800 mt-0.5">Analisis tabel kebenaran gerbang logika Anda sebelum mencoba kembali.</p>
                <div class="mt-1 text-xs font-mono-code font-black text-rose-700">
                    Dapat mencoba kembali dalam: <span class="bg-rose-200 px-2 py-0.5 rounded-md text-rose-950" x-text="formatTime(cooldownSeconds)"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Toast Notifikasi -->
    <div x-show="alertToast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-2 p-2 rounded-xl border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-between gap-2 text-xs font-bold"
         :class="alertToast.isSuccess ? 'bg-emerald-500 text-white' : 'bg-[#fde047] text-[#121316]'">
        <div class="flex items-center gap-2">
            <span class="text-sm" x-text="alertToast.isSuccess ? '⚡' : '💡'"></span>
            <span x-text="alertToast.message"></span>
        </div>
        <button type="button" @click="alertToast.show = false" class="text-inherit hover:opacity-75 text-sm font-black px-1 cursor-pointer">✕</button>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- 💡 PANDUAN CEPAT (CARA BERMAIN) - KONTRAS TINGGI                     -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="hide-in-landscape mb-2 p-2 sm:p-2.5 rounded-xl bg-gradient-to-r from-amber-50 via-yellow-50 to-emerald-50 border-2 border-[#121316] text-[#121316] shadow-[2px_2px_0px_#121316]">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="text-xl">💡</span>
                <div>
                    <span class="font-black text-xs text-[#121316] block sm:inline">PANDUAN CARA MAIN:</span>
                    <span class="text-[11px] text-slate-800">Tujuan Anda: <b>Nyalakan Lampu Bohlam di sebelah kanan</b> hingga berpijar emas ✨!</span>
                </div>
            </div>
            <div class="flex items-center gap-2 text-[10.5px] font-mono-code font-bold flex-wrap">
                <div class="flex items-center gap-1 bg-white px-2 py-0.5 rounded-md border border-slate-300 text-slate-900 shadow-2xs">
                    <span class="text-amber-600 font-black">1.</span>
                    <span>Klik saklar <b>A / B</b> di kiri</span>
                </div>
                <div class="flex items-center gap-1 bg-white px-2 py-0.5 rounded-md border border-slate-300 text-slate-900 shadow-2xs">
                    <span class="text-emerald-600 font-black">2.</span>
                    <span>Arus hijau <b>(1)</b> mengalir</span>
                </div>
                <div class="flex items-center gap-1 bg-white px-2 py-0.5 rounded-md border border-slate-300 text-slate-900 shadow-2xs">
                    <span class="text-cyan-700 font-black">3.</span>
                    <span>Lampu Nyala = <b>Menang</b>!</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Dashboard: Level Selector, Target, Stats, Undo & Cheatsheet -->
    <div class="flex items-center justify-between gap-1.5 mb-1.5 p-1.5 sm:p-2 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316] text-[#121316] shrink-0">
        
        <!-- Pilihan Misi Sirkuit -->
        <div class="flex items-center gap-1 sm:gap-1.5 overflow-x-auto no-scrollbar py-0.5">
            <span class="text-[9.5px] font-mono-code font-bold text-slate-600 mr-0.5 hidden md:inline shrink-0">MISI:</span>
            <button type="button" 
                    @click="changeLevel(1)" 
                    :disabled="isCooldown"
                    class="px-2 py-0.5 rounded-lg text-[10.5px] font-black border-2 border-[#121316] transition-all cursor-pointer whitespace-nowrap shrink-0"
                    :class="level === 1 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 shadow-none'">
                <span>Lvl 1: AND/OR</span>
            </button>
            <button type="button" 
                    @click="changeLevel(2)" 
                    :disabled="isCooldown"
                    class="px-2 py-0.5 rounded-lg text-[10.5px] font-black border-2 border-[#121316] transition-all cursor-pointer whitespace-nowrap shrink-0"
                    :class="level === 2 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 shadow-none'">
                <span>Lvl 2: XOR/NOT</span>
            </button>
            <button type="button" 
                    @click="changeLevel(3)" 
                    :disabled="isCooldown"
                    class="px-2 py-0.5 rounded-lg text-[10.5px] font-black border-2 border-[#121316] transition-all cursor-pointer whitespace-nowrap shrink-0"
                    :class="level === 3 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 shadow-none'">
                <span>Lvl 3: CPU (NAND)</span>
            </button>
        </div>

        <!-- Info Target Output & Langkah -->
        <div class="flex items-center gap-1.5 sm:gap-2 text-[11px] font-mono-code font-bold shrink-0">
            <!-- Target LED -->
            <div class="flex items-center gap-1 px-1.5 py-0.5 bg-emerald-100 border border-emerald-400 rounded-lg text-emerald-950 font-black shadow-2xs text-[10px]">
                <span class="text-emerald-900 font-black" x-text="level === 3 ? 'BOHLAM 1 & 2' : 'BOHLAM 1'"></span>
            </div>

            <!-- Langkah / Toggle Count -->
            <div class="flex items-center gap-1" title="Jumlah kali saklar dialihkan">
                <span class="text-slate-600 text-[9.5px]">TOGGLE:</span>
                <span class="px-1.5 py-0.2 rounded bg-slate-100 border border-[#121316] text-[#121316] font-black text-[11px]" x-text="moves"></span>
            </div>

            <!-- Waktu -->
            <div class="hidden sm:flex items-center gap-1">
                <span class="text-slate-600 text-[9.5px]">WAKTU:</span>
                <span class="px-1.5 py-0.2 rounded bg-slate-100 border border-[#121316] text-[#121316] font-black text-[11px]" x-text="formatTime(timerSeconds)"></span>
            </div>
        </div>

        <!-- Tombol Aksi: Undo, Reset, & Tabel Kebenaran -->
        <div class="flex items-center gap-1 shrink-0">
            <button type="button" 
                    @click="undoMove()" 
                    :disabled="history.length === 0 || isCooldown || victoryModal.show" 
                    class="btn-tactile-white px-1.5 sm:px-2 py-0.5 rounded-lg text-[10.5px] font-black disabled:opacity-40 cursor-pointer"
                    title="Batalkan 1 saklar terakhir">
                <i class="fa-solid fa-arrow-rotate-left mr-0.5 text-slate-700"></i> <span class="hidden sm:inline">Undo</span>
            </button>
            <button type="button" 
                    @click="resetSwitches()" 
                    :disabled="isCooldown || moves === 0" 
                    class="btn-tactile-white px-1.5 sm:px-2 py-0.5 rounded-lg text-[10.5px] font-black disabled:opacity-40 cursor-pointer"
                    title="Nol-kan semua saklar">
                <i class="fa-solid fa-rotate-left mr-0.5 text-slate-700"></i> <span class="hidden sm:inline">Reset</span>
            </button>
            <button type="button" 
                    @click="showTruthTable = true" 
                    class="p-1 px-1.5 rounded-lg bg-indigo-50 border border-indigo-300 text-indigo-900 text-xs font-black hover:bg-indigo-100 transition cursor-pointer flex items-center gap-1 shadow-2xs"
                    title="Buka Cheatsheet Tabel Kebenaran (Truth Table)">
                <i class="fa-solid fa-table text-indigo-700"></i>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ARENA PAPAN BREADBOARD SIRKUIT TERANG (HIGH CONTRAST WORKBENCH)     -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="relative w-full rounded-2xl border-2 border-[#121316] bg-gradient-to-b from-slate-100 via-sky-50/60 to-slate-100 p-3 sm:p-4 overflow-hidden shadow-[3px_3px_0px_#121316] select-none text-[#121316]">
        
        <!-- Grid Breadboard Lab Terang -->
        <div class="absolute inset-0 opacity-25 pointer-events-none" 
             style="background-image: radial-gradient(#64748b 1.5px, transparent 1.5px); background-size: 16px 16px;"></div>

        <!-- Header Petunjuk Aliran Arus Logika -->
        <div class="hide-in-landscape relative z-10 mb-2 sm:mb-3 flex items-center justify-between text-[10.5px] sm:text-xs font-mono-code flex-wrap gap-2 pb-1.5 sm:pb-2 border-b border-slate-300">
            <div class="flex items-center gap-2">
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-slate-300 text-slate-800 shadow-2xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Status Arus: <b class="text-emerald-700">1 = HIGH (Menyala/Hijau)</b> | <b class="text-slate-600">0 = LOW (Mati/Abu)</b></span>
                </span>
            </div>
            <div class="text-amber-800 font-bold flex items-center gap-1 text-[11px] bg-amber-100/80 px-2 py-0.5 rounded-md border border-amber-300">
                <i class="fa-solid fa-hand-pointer text-amber-700"></i>
                <span>Klik saklar input untuk mengalirkan arus listrik</span>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- LEVEL 1: SIRKUIT 2 INPUT (A, B) DENGAN GERBANG AND & OR         -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="level === 1" class="relative z-10 py-0.5 sm:py-1">
            <div class="logic-circuit-row flex flex-col lg:flex-row items-stretch justify-between gap-2 sm:gap-3 w-full">
                
                <!-- TAHAP 1: SAKLAR INPUT (SWITCH A & B) -->
                <div class="flex-1 flex flex-col justify-between bg-white border-2 border-[#121316] p-2 sm:p-3 rounded-2xl shadow-[2px_2px_0px_#121316] min-w-0">
                    <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-200 font-mono-code">
                        <span class="text-xs font-black text-amber-900 flex items-center gap-1">
                            <i class="fa-solid fa-bolt text-amber-600"></i> 1. SUMBER INPUT
                        </span>
                        <span class="text-[9.5px] text-slate-500 font-bold">Klik Saklar</span>
                    </div>

                    <div class="flex flex-col gap-2 sm:gap-2.5 my-auto">
                        <!-- Switch A -->
                        <div class="bg-slate-50 p-2 sm:p-2.5 rounded-xl border-2 border-slate-200 flex items-center justify-between gap-2 shadow-xs hover:border-slate-300 transition">
                            <div>
                                <span class="text-[9.5px] sm:text-[10px] font-mono-code text-slate-600 block font-bold">SAKLAR A</span>
                                <div class="flex items-center gap-1 sm:gap-1.5 mt-0.5">
                                    <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full border border-slate-400 transition-all"
                                          :class="switches.A ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-slate-300'"></span>
                                    <span class="text-[11px] sm:text-xs font-black font-mono-code" :class="switches.A ? 'text-emerald-700' : 'text-slate-600'" x-text="switches.A ? '1 (ON)' : '0 (OFF)'"></span>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="toggleSwitch('A')" 
                                    class="px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-xl border-2 border-[#121316] font-black text-[11px] sm:text-xs transition-all flex items-center gap-1 cursor-pointer shadow-xs active:scale-95 shrink-0"
                                    :class="switches.A ? 'bg-emerald-500 text-white shadow-[2px_2px_0px_#065f46]' : 'bg-slate-200 text-slate-800 hover:bg-slate-300'">
                                <span x-text="switches.A ? '● ON' : '○ OFF'"></span>
                            </button>
                        </div>

                        <!-- Switch B -->
                        <div class="bg-slate-50 p-2 sm:p-2.5 rounded-xl border-2 border-slate-200 flex items-center justify-between gap-2 shadow-xs hover:border-slate-300 transition">
                            <div>
                                <span class="text-[9.5px] sm:text-[10px] font-mono-code text-slate-600 block font-bold">SAKLAR B</span>
                                <div class="flex items-center gap-1 sm:gap-1.5 mt-0.5">
                                    <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full border border-slate-400 transition-all"
                                          :class="switches.B ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-slate-300'"></span>
                                    <span class="text-[11px] sm:text-xs font-black font-mono-code" :class="switches.B ? 'text-emerald-700' : 'text-slate-600'" x-text="switches.B ? '1 (ON)' : '0 (OFF)'"></span>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="toggleSwitch('B')" 
                                    class="px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-xl border-2 border-[#121316] font-black text-[11px] sm:text-xs transition-all flex items-center gap-1 cursor-pointer shadow-xs active:scale-95 shrink-0"
                                    :class="switches.B ? 'bg-emerald-500 text-white shadow-[2px_2px_0px_#065f46]' : 'bg-slate-200 text-slate-800 hover:bg-slate-300'">
                                <span x-text="switches.B ? '● ON' : '○ OFF'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] text-slate-600 text-center font-mono-code font-bold">
                        Listrik mengalir ke G1 & G2 ►
                    </div>
                </div>

                <!-- TAHAP 2: GERBANG LOGIKA TAHAP 1 (AND & OR) -->
                <div class="flex-[1.3] flex flex-col justify-between gap-2 bg-white border-2 border-[#121316] p-2 sm:p-3 rounded-2xl shadow-[2px_2px_0px_#121316] min-w-0">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-200 font-mono-code">
                        <span class="text-xs font-black text-indigo-950 flex items-center gap-1">
                            <i class="fa-solid fa-microchip text-indigo-600"></i> 2. GERBANG TAHAP 1
                        </span>
                        <span class="text-[9.5px] text-slate-500 font-bold">Proses Sinyal</span>
                    </div>

                    <div class="flex flex-col gap-2 my-auto">
                        <!-- Gerbang 1: AND -->
                        <div class="bg-slate-50 p-2 sm:p-2.5 rounded-xl border-2 transition-all shadow-xs"
                             :class="gate1L1 ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-200'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="px-1.5 sm:px-2 py-0.5 rounded text-[9.5px] sm:text-[10px] font-black bg-indigo-600 text-white font-mono-code shadow-2xs">GERBANG 1: AND</span>
                                <span class="text-[10.5px] sm:text-xs font-mono-code font-black px-1.5 py-0.2 rounded border"
                                      :class="gate1L1 ? 'bg-emerald-100 border-emerald-400 text-emerald-900' : 'bg-slate-200 border-slate-300 text-slate-700'"
                                      x-text="'Out: ' + (gate1L1 ? '1' : '0')"></span>
                            </div>
                            <p class="text-[10px] text-slate-700 mb-1 font-medium">
                                Syarat: <b>Saklar A & B harus ON (1 & 1)</b>.
                            </p>
                            <div class="flex items-center justify-between text-[9.5px] font-mono-code bg-white px-1.5 py-0.5 rounded border border-slate-200">
                                <span class="text-slate-800 font-bold">
                                    A:<b :class="switches.A ? 'text-emerald-700' : 'text-slate-500'" x-text="switches.A ? '1' : '0'"></b>
                                    &bull; 
                                    B:<b :class="switches.B ? 'text-emerald-700' : 'text-slate-500'" x-text="switches.B ? '1' : '0'"></b>
                                </span>
                                <span class="font-black" :class="gate1L1 ? 'text-emerald-700' : 'text-slate-500'" x-text="gate1L1 ? '✓ LOLOS' : '✗ PUTUS'"></span>
                            </div>
                        </div>

                        <!-- Gerbang 2: OR -->
                        <div class="bg-slate-50 p-2 sm:p-2.5 rounded-xl border-2 transition-all shadow-xs"
                             :class="gate2L1 ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-200'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="px-1.5 sm:px-2 py-0.5 rounded text-[9.5px] sm:text-[10px] font-black bg-amber-500 text-[#121316] font-mono-code shadow-2xs">GERBANG 2: OR</span>
                                <span class="text-[10.5px] sm:text-xs font-mono-code font-black px-1.5 py-0.2 rounded border"
                                      :class="gate2L1 ? 'bg-emerald-100 border-emerald-400 text-emerald-900' : 'bg-slate-200 border-slate-300 text-slate-700'"
                                      x-text="'Out: ' + (gate2L1 ? '1' : '0')"></span>
                            </div>
                            <p class="text-[10px] text-slate-700 mb-1 font-medium">
                                Syarat: <b>Salah satu saklar (A/B) ON</b>.
                            </p>
                            <div class="flex items-center justify-between text-[9.5px] font-mono-code bg-white px-1.5 py-0.5 rounded border border-slate-200">
                                <span class="text-slate-800 font-bold">
                                    A:<b :class="switches.A ? 'text-emerald-700' : 'text-slate-500'" x-text="switches.A ? '1' : '0'"></b>
                                    + 
                                    B:<b :class="switches.B ? 'text-emerald-700' : 'text-slate-500'" x-text="switches.B ? '1' : '0'"></b>
                                </span>
                                <span class="font-black" :class="gate2L1 ? 'text-emerald-700' : 'text-slate-500'" x-text="gate2L1 ? '✓ LOLOS' : '✗ PUTUS'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] text-slate-600 text-center font-mono-code font-bold">
                        G1 & G2 ke Gerbang Akhir ►
                    </div>
                </div>

                <!-- TAHAP 3: GERBANG AKHIR (XOR) & BOHLAM TARGET -->
                <div class="flex-1 flex flex-col justify-between bg-white border-2 p-2 sm:p-3 rounded-2xl shadow-[2px_2px_0px_#121316] min-w-0 transition-all"
                     :class="outputL1 ? 'border-emerald-600 bg-emerald-50/40' : 'border-[#121316]'">
                    
                    <div>
                        <!-- Header Gerbang Akhir -->
                        <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-200 font-mono-code">
                            <span class="text-xs font-black text-fuchsia-950 flex items-center gap-1">
                                <i class="fa-solid fa-code-fork text-fuchsia-600"></i> 3. AKHIR: XOR
                            </span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-fuchsia-100 text-fuchsia-900 border border-fuchsia-300 font-black">Beda = 1</span>
                        </div>

                        <!-- Keterangan XOR -->
                        <div class="bg-slate-50 p-1.5 sm:p-2 rounded-xl border border-slate-200 text-[9.5px] font-mono-code text-slate-800 mb-1.5">
                            <div class="text-slate-700 text-[9px] mb-0.5 font-medium">
                                Syarat: <b>Output G1 & G2 BERBEDA</b>.
                            </div>
                            <div class="flex items-center justify-between pt-0.5 border-t border-slate-200 text-[9px]">
                                <span>G1: <b :class="gate1L1 ? 'text-emerald-700' : 'text-slate-600'" x-text="gate1L1 ? '1' : '0'"></b></span>
                                <span class="font-bold text-slate-400">≠</span>
                                <span>G2: <b :class="gate2L1 ? 'text-emerald-700' : 'text-slate-600'" x-text="gate2L1 ? '1' : '0'"></b></span>
                                <span>➔</span>
                                <span class="font-black" :class="outputL1 ? 'text-emerald-700' : 'text-rose-700'" x-text="outputL1 ? '✓ 1' : '✗ 0'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- PUSAT TARGET UTAMA: LAMPU BOHLAM -->
                    <div class="my-auto py-1 sm:py-2 flex flex-col items-center justify-center bg-gradient-to-b from-amber-50/70 to-yellow-50/40 rounded-xl border border-amber-200 p-2">
                        <div class="relative flex items-center justify-center">
                            <!-- Efek Pijar Lingkaran saat Lampu Menyala -->
                            <div x-show="outputL1" class="absolute inset-0 rounded-full bg-yellow-400/40 blur-xl animate-pulse"></div>
                            
                            <!-- Bola Lampu -->
                            <div class="circuit-bulb w-12 h-12 sm:w-16 sm:h-16 lg:w-20 lg:h-20 rounded-full border-2 sm:border-3 flex items-center justify-center transition-all duration-300 relative z-10"
                                 :class="outputL1 ? 'bg-gradient-to-br from-yellow-300 via-amber-400 to-yellow-500 text-slate-950 border-amber-400 bulb-glow-active shadow-lg' : 'bg-slate-200 text-slate-400 border-slate-300 shadow-inner'">
                                <i class="fa-solid fa-lightbulb text-2xl sm:text-3xl lg:text-4xl transition-all"
                                   :class="outputL1 ? 'text-slate-950 scale-110 drop-shadow-[0_2px_4px_rgba(0,0,0,0.3)]' : 'text-slate-400'"></i>
                            </div>
                        </div>

                        <!-- Label Status Lampu (Kontras Tinggi) -->
                        <div class="text-center mt-1.5">
                            <div class="text-[11px] sm:text-xs font-mono-code font-black"
                                 :class="outputL1 ? 'text-amber-950 font-black' : 'text-slate-600'">
                                <span x-text="outputL1 ? '✨ NYALA! (1)' : '○ MATI (0)'"></span>
                            </div>
                            <div class="text-[9.5px] font-black mt-0.5"
                                 :class="outputL1 ? 'text-emerald-800' : 'text-slate-500'">
                                <span x-text="outputL1 ? '🎉 TARGET TERCAPAI!' : 'Atur saklar agar nyala'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] text-center font-mono-code font-bold"
                         :class="outputL1 ? 'text-emerald-800' : 'text-slate-600'">
                        <span x-text="outputL1 ? 'Target: 1 (HIGH) ✓' : 'Target: 1 (HIGH)'"></span>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- LEVEL 2: SIRKUIT 3 INPUT (A, B, C) DENGAN NOT, XOR, & AND       -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="level === 2" class="relative z-10 py-0.5 sm:py-1">
            <div class="logic-circuit-row flex flex-col lg:flex-row items-stretch justify-between gap-2 sm:gap-3 w-full">
                
                <!-- TAHAP 1: 3 SAKLAR INPUT (SWITCH A, B, C) -->
                <div class="flex-1 flex flex-col justify-between bg-white border-2 border-[#121316] p-2 sm:p-3 rounded-2xl shadow-[2px_2px_0px_#121316] min-w-0">
                    <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-200 font-mono-code">
                        <span class="text-xs font-black text-amber-900 flex items-center gap-1">
                            <i class="fa-solid fa-bolt text-amber-600"></i> 1. 3 INPUT
                        </span>
                        <span class="text-[9.5px] text-slate-500 font-bold">A, B, C</span>
                    </div>

                    <div class="flex flex-col gap-1.5 sm:gap-2 my-auto">
                        <!-- Switch A -->
                        <div class="bg-slate-50 p-1.5 sm:p-2 rounded-xl border border-slate-200 flex items-center justify-between gap-2 shadow-xs">
                            <div>
                                <span class="text-[9px] sm:text-[9.5px] font-mono-code text-slate-600 block font-bold">INPUT A</span>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <span class="w-2.5 h-2.5 rounded-full border border-slate-400"
                                          :class="switches.A ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-slate-300'"></span>
                                    <span class="text-[11px] font-black font-mono-code" :class="switches.A ? 'text-emerald-700' : 'text-slate-600'" x-text="switches.A ? '1 (ON)' : '0 (OFF)'"></span>
                                </div>
                            </div>
                            <button type="button" @click="toggleSwitch('A')" class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border-2 border-[#121316] font-black text-[10px] sm:text-[11px] cursor-pointer shadow-xs" :class="switches.A ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-800'">
                                <span x-text="switches.A ? 'ON' : 'OFF'"></span>
                            </button>
                        </div>

                        <!-- Switch B -->
                        <div class="bg-slate-50 p-1.5 sm:p-2 rounded-xl border border-slate-200 flex items-center justify-between gap-2 shadow-xs">
                            <div>
                                <span class="text-[9px] sm:text-[9.5px] font-mono-code text-slate-600 block font-bold">INPUT B</span>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <span class="w-2.5 h-2.5 rounded-full border border-slate-400"
                                          :class="switches.B ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-slate-300'"></span>
                                    <span class="text-[11px] font-black font-mono-code" :class="switches.B ? 'text-emerald-700' : 'text-slate-600'" x-text="switches.B ? '1 (ON)' : '0 (OFF)'"></span>
                                </div>
                            </div>
                            <button type="button" @click="toggleSwitch('B')" class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border-2 border-[#121316] font-black text-[10px] sm:text-[11px] cursor-pointer shadow-xs" :class="switches.B ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-800'">
                                <span x-text="switches.B ? 'ON' : 'OFF'"></span>
                            </button>
                        </div>

                        <!-- Switch C -->
                        <div class="bg-slate-50 p-1.5 sm:p-2 rounded-xl border border-slate-200 flex items-center justify-between gap-2 shadow-xs">
                            <div>
                                <span class="text-[9px] sm:text-[9.5px] font-mono-code text-slate-600 block font-bold">INPUT C</span>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <span class="w-2.5 h-2.5 rounded-full border border-slate-400"
                                          :class="switches.C ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-slate-300'"></span>
                                    <span class="text-[11px] font-black font-mono-code" :class="switches.C ? 'text-emerald-700' : 'text-slate-600'" x-text="switches.C ? '1 (ON)' : '0 (OFF)'"></span>
                                </div>
                            </div>
                            <button type="button" @click="toggleSwitch('C')" class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg border-2 border-[#121316] font-black text-[10px] sm:text-[11px] cursor-pointer shadow-xs" :class="switches.C ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-800'">
                                <span x-text="switches.C ? 'ON' : 'OFF'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] text-slate-600 text-center font-mono-code font-bold">
                        A & B ke G1, C ke G2 ►
                    </div>
                </div>

                <!-- TAHAP 2: GERBANG LOGIKA TAHAP 1 (XOR & NOT) -->
                <div class="flex-[1.3] flex flex-col justify-between gap-2 bg-white border-2 border-[#121316] p-2 sm:p-3 rounded-2xl shadow-[2px_2px_0px_#121316] min-w-0">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-200 font-mono-code">
                        <span class="text-xs font-black text-indigo-950 flex items-center gap-1">
                            <i class="fa-solid fa-microchip text-indigo-600"></i> 2. GERBANG TAHAP 1
                        </span>
                        <span class="text-[9.5px] text-slate-500 font-bold">XOR & NOT</span>
                    </div>

                    <div class="flex flex-col gap-2 my-auto">
                        <!-- Gerbang XOR (A XOR B) -->
                        <div class="bg-slate-50 p-2 sm:p-2.5 rounded-xl border-2 transition-all shadow-xs"
                             :class="gate1L2 ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-200'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="px-1.5 sm:px-2 py-0.5 rounded text-[9.5px] sm:text-[10px] font-black bg-fuchsia-600 text-white font-mono-code shadow-2xs">GERBANG 1: XOR</span>
                                <span class="text-[10.5px] sm:text-xs font-mono-code font-black px-1.5 py-0.2 rounded border"
                                      :class="gate1L2 ? 'bg-emerald-100 border-emerald-400 text-emerald-900' : 'bg-slate-200 border-slate-300 text-slate-700'"
                                      x-text="'Out: ' + (gate1L2 ? '1' : '0')"></span>
                            </div>
                            <p class="text-[10px] text-slate-700 mb-1 font-medium">
                                Syarat: <b>Input A & B harus beda nilai</b>.
                            </p>
                            <div class="flex items-center justify-between text-[9.5px] font-mono-code bg-white px-1.5 py-0.5 rounded border border-slate-200">
                                <span class="text-slate-800 font-bold">A (<b :class="switches.A ? 'text-emerald-700' : 'text-slate-500'" x-text="switches.A ? '1' : '0'"></b>) ≠ B (<b :class="switches.B ? 'text-emerald-700' : 'text-slate-500'" x-text="switches.B ? '1' : '0'"></b>)</span>
                                <span class="font-black" :class="gate1L2 ? 'text-emerald-700' : 'text-slate-500'" x-text="gate1L2 ? '✓ BEDA (1)' : '✗ SAMA (0)'"></span>
                            </div>
                        </div>

                        <!-- Gerbang NOT (NOT C) -->
                        <div class="bg-slate-50 p-2 sm:p-2.5 rounded-xl border-2 transition-all shadow-xs"
                             :class="gate2L2 ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-200'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="px-1.5 sm:px-2 py-0.5 rounded text-[9.5px] sm:text-[10px] font-black bg-rose-600 text-white font-mono-code shadow-2xs">GERBANG 2: NOT</span>
                                <span class="text-[10.5px] sm:text-xs font-mono-code font-black px-1.5 py-0.2 rounded border"
                                      :class="gate2L2 ? 'bg-emerald-100 border-emerald-400 text-emerald-900' : 'bg-slate-200 border-slate-300 text-slate-700'"
                                      x-text="'Out: ' + (gate2L2 ? '1' : '0')"></span>
                            </div>
                            <p class="text-[10px] text-slate-700 mb-1 font-medium">
                                Syarat: <b>Membalik nilai C</b> (0 ➔ 1).
                            </p>
                            <div class="flex items-center justify-between text-[9.5px] font-mono-code bg-white px-1.5 py-0.5 rounded border border-slate-200">
                                <span class="text-slate-800 font-bold">C: <b :class="switches.C ? 'text-emerald-700' : 'text-slate-500'" x-text="switches.C ? '1' : '0'"></b> ➔ Balik:</span>
                                <span class="font-black" :class="gate2L2 ? 'text-emerald-700' : 'text-slate-500'" x-text="gate2L2 ? '✓ 1' : '✗ 0'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] text-slate-600 text-center font-mono-code font-bold">
                        G1 & G2 ke Gerbang AND ►
                    </div>
                </div>

                <!-- TAHAP 3: GERBANG AKHIR (AND) & BOHLAM TARGET -->
                <div class="flex-1 flex flex-col justify-between bg-white border-2 p-2 sm:p-3 rounded-2xl shadow-[2px_2px_0px_#121316] min-w-0 transition-all"
                     :class="outputL2 ? 'border-emerald-600 bg-emerald-50/40' : 'border-[#121316]'">
                    
                    <div>
                        <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-200 font-mono-code">
                            <span class="text-xs font-black text-indigo-950 flex items-center gap-1">
                                <i class="fa-solid fa-code-merge text-indigo-600"></i> 3. AKHIR: AND
                            </span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-900 border border-indigo-300 font-black">Keduanya 1</span>
                        </div>

                        <div class="bg-slate-50 p-1.5 sm:p-2 rounded-xl border border-slate-200 text-[9.5px] font-mono-code text-slate-800 mb-1.5">
                            <div class="text-slate-700 text-[9px] mb-0.5 font-medium">
                                Syarat: <b>G1 & G2 Keduanya Wajib bernilai 1</b>!
                            </div>
                            <div class="flex items-center justify-between pt-0.5 border-t border-slate-200 text-[9px]">
                                <span>G1: <b :class="gate1L2 ? 'text-emerald-700' : 'text-slate-600'" x-text="gate1L2 ? '1' : '0'"></b></span>
                                <span class="font-bold text-slate-400">&bull;</span>
                                <span>G2: <b :class="gate2L2 ? 'text-emerald-700' : 'text-slate-600'" x-text="gate2L2 ? '1' : '0'"></b></span>
                                <span>➔</span>
                                <span class="font-black" :class="outputL2 ? 'text-emerald-700' : 'text-rose-700'" x-text="outputL2 ? '✓ 1' : '✗ 0'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- PUSAT TARGET BOHLAM LEVEL 2 -->
                    <div class="my-auto py-1 sm:py-2 flex flex-col items-center justify-center bg-gradient-to-b from-amber-50/70 to-yellow-50/40 rounded-xl border border-amber-200 p-2">
                        <div class="relative flex items-center justify-center">
                            <div x-show="outputL2" class="absolute inset-0 rounded-full bg-yellow-400/40 blur-xl animate-pulse"></div>
                            <div class="circuit-bulb w-12 h-12 sm:w-16 sm:h-16 lg:w-20 lg:h-20 rounded-full border-2 sm:border-3 flex items-center justify-center transition-all duration-300 relative z-10"
                                 :class="outputL2 ? 'bg-gradient-to-br from-yellow-300 via-amber-400 to-yellow-500 text-slate-950 border-amber-400 bulb-glow-active shadow-lg' : 'bg-slate-200 text-slate-400 border-slate-300 shadow-inner'">
                                <i class="fa-solid fa-lightbulb text-2xl sm:text-3xl lg:text-4xl transition-all"
                                   :class="outputL2 ? 'text-slate-950 scale-110 drop-shadow-[0_2px_4px_rgba(0,0,0,0.3)]' : 'text-slate-400'"></i>
                            </div>
                        </div>

                        <div class="text-center mt-1.5">
                            <div class="text-[11px] sm:text-xs font-mono-code font-black"
                                 :class="outputL2 ? 'text-amber-950' : 'text-slate-600'">
                                <span x-text="outputL2 ? '✨ NYALA! (1)' : '○ MATI (0)'"></span>
                            </div>
                            <div class="text-[9.5px] font-black mt-0.5"
                                 :class="outputL2 ? 'text-emerald-800' : 'text-slate-500'">
                                <span x-text="outputL2 ? '🎉 LEVEL 2 BERHASIL!' : 'Ubah Saklar A, B, atau C'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] text-center font-mono-code font-bold"
                         :class="outputL2 ? 'text-emerald-800' : 'text-slate-600'">
                        <span x-text="outputL2 ? 'Target: 1 (HIGH) ✓' : 'Target: 1 (HIGH)'"></span>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- LEVEL 3: MASTER CPU (4 SAKLAR A,B,C,D & DUAL OUTPUT BOHLAM)     -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="level === 3" class="relative z-10 py-0.5 sm:py-1">
            <div class="logic-circuit-row flex flex-col lg:flex-row items-stretch justify-between gap-2 sm:gap-3 w-full">
                
                <!-- TAHAP 1: 4 SAKLAR INPUT (A, B, C, D) -->
                <div class="flex-1 flex flex-col justify-between bg-white border-2 border-[#121316] p-2 sm:p-3 rounded-2xl shadow-[2px_2px_0px_#121316] min-w-0">
                    <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-200 font-mono-code">
                        <span class="text-xs font-black text-amber-900 flex items-center gap-1">
                            <i class="fa-solid fa-bolt text-amber-600"></i> 1. 4 INPUT
                        </span>
                        <span class="text-[9.5px] text-slate-500 font-bold">A, B, C, D</span>
                    </div>

                    <div class="grid grid-cols-2 gap-1.5 sm:gap-2 my-auto">
                        <template x-for="swKey in ['A', 'B', 'C', 'D']" :key="'sw-l3-' + swKey">
                            <div class="bg-slate-50 p-1.5 rounded-xl border border-slate-200 flex flex-col items-center justify-between gap-1 shadow-xs">
                                <span class="text-[9.5px] font-mono-code text-slate-700 font-bold" x-text="'SAKLAR ' + swKey"></span>
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full border border-slate-400"
                                          :class="switches[swKey] ? 'bg-emerald-500 shadow-[0_0_6px_#10b981]' : 'bg-slate-300'"></span>
                                    <span class="text-[10px] font-mono-code font-black" :class="switches[swKey] ? 'text-emerald-700' : 'text-slate-600'" x-text="switches[swKey] ? '1' : '0'"></span>
                                </div>
                                <button type="button" @click="toggleSwitch(swKey)" class="w-full py-0.5 rounded text-[9.5px] font-black border-2 border-[#121316] cursor-pointer shadow-xs" :class="switches[swKey] ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-800'">
                                    <span x-text="switches[swKey] ? 'ON' : 'OFF'"></span>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] text-slate-600 text-center font-mono-code font-bold">
                        Arus ke 3 Gerbang Logika ►
                    </div>
                </div>

                <!-- TAHAP 2: GERBANG LOGIKA TAHAP 1 (NAND, NOR, XOR) -->
                <div class="flex-[1.3] flex flex-col justify-between gap-2 bg-white border-2 border-[#121316] p-2 sm:p-3 rounded-2xl shadow-[2px_2px_0px_#121316] min-w-0">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-200 font-mono-code">
                        <span class="text-xs font-black text-indigo-950 flex items-center gap-1">
                            <i class="fa-solid fa-microchip text-indigo-600"></i> 2. GERBANG TAHAP 1
                        </span>
                        <span class="text-[9.5px] text-slate-500 font-bold">NAND, NOR, XOR</span>
                    </div>

                    <div class="flex flex-col gap-1.5 my-auto">
                        <!-- G1: NAND (A, B) -->
                        <div class="bg-slate-50 p-1.5 rounded-xl border border-slate-200 flex items-center justify-between text-[9.5px] font-mono-code shadow-xs">
                            <div>
                                <span class="px-1.5 py-0.2 rounded bg-cyan-700 text-white font-bold">G1: NAND (A,B)</span>
                                <span class="text-[8.5px] text-slate-600 ml-1">Mati bila 1 & 1</span>
                            </div>
                            <span class="font-black px-1.5 py-0.2 rounded text-[10px] border" :class="gate1L3 ? 'bg-emerald-100 border-emerald-400 text-emerald-900' : 'bg-slate-200 border-slate-300 text-slate-700'" x-text="gate1L3 ? '1' : '0'"></span>
                        </div>

                        <!-- G2: NOR (C, D) -->
                        <div class="bg-slate-50 p-1.5 rounded-xl border border-slate-200 flex items-center justify-between text-[9.5px] font-mono-code shadow-xs">
                            <div>
                                <span class="px-1.5 py-0.2 rounded bg-teal-700 text-white font-bold">G2: NOR (C,D)</span>
                                <span class="text-[8.5px] text-slate-600 ml-1">Nyala bila 0 & 0</span>
                            </div>
                            <span class="font-black px-1.5 py-0.2 rounded text-[10px] border" :class="gate2L3 ? 'bg-emerald-100 border-emerald-400 text-emerald-900' : 'bg-slate-200 border-slate-300 text-slate-700'" x-text="gate2L3 ? '1' : '0'"></span>
                        </div>

                        <!-- G3: XOR (B, C) -->
                        <div class="bg-slate-50 p-1.5 rounded-xl border border-slate-200 flex items-center justify-between text-[9.5px] font-mono-code shadow-xs">
                            <div>
                                <span class="px-1.5 py-0.2 rounded bg-purple-700 text-white font-bold">G3: XOR (B,C)</span>
                                <span class="text-[8.5px] text-slate-600 ml-1">Nyala bila B ≠ C</span>
                            </div>
                            <span class="font-black px-1.5 py-0.2 rounded text-[10px] border" :class="gate3L3 ? 'bg-emerald-100 border-emerald-400 text-emerald-900' : 'bg-slate-200 border-slate-300 text-slate-700'" x-text="gate3L3 ? '1' : '0'"></span>
                        </div>
                    </div>

                    <div class="mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] text-slate-600 text-center font-mono-code font-bold">
                        Output ke 2 Bohlam Target ►
                    </div>
                </div>

                <!-- TAHAP 3: DUA BOHLAM TARGET SEKALIGUS (OUT 1 & OUT 2) -->
                <div class="flex-1 flex flex-col justify-between bg-white border-2 p-2 sm:p-3 rounded-2xl shadow-[2px_2px_0px_#121316] min-w-0 transition-all"
                     :class="outputL3 ? 'border-emerald-600 bg-emerald-50/40' : 'border-[#121316]'">
                    
                    <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-200 font-mono-code">
                        <span class="text-xs font-black text-amber-900 flex items-center gap-1">
                            <i class="fa-solid fa-trophy text-amber-600"></i> 3. DUA TARGET
                        </span>
                        <span class="text-[9px] text-emerald-800 font-black">Wajib ON</span>
                    </div>

                    <div class="grid grid-cols-2 gap-1.5 sm:gap-2 my-auto py-1">
                        <!-- OUT 1 (G1 AND G3) -->
                        <div class="flex flex-col items-center justify-center p-1.5 bg-amber-50/50 rounded-xl border border-amber-200">
                            <span class="text-[8.5px] font-mono-code text-slate-700 font-bold mb-0.5">BOHLAM 1 (SUM)</span>
                            <div class="circuit-bulb w-10 h-10 sm:w-12 sm:h-12 rounded-full border-2 flex items-center justify-center my-0.5 transition-all"
                                 :class="out1L3 ? 'bg-amber-400 text-slate-950 border-amber-400 shadow-[0_0_15px_#facc15]' : 'bg-slate-200 text-slate-400 border-slate-300'">
                                <i class="fa-solid fa-lightbulb text-lg sm:text-xl" :class="out1L3 ? 'text-slate-950 scale-110' : 'text-slate-400'"></i>
                            </div>
                            <span class="text-[10px] font-mono-code font-black mt-0.5" :class="out1L3 ? 'text-emerald-800' : 'text-slate-600'" x-text="out1L3 ? '1 (ON)' : '0 (OFF)'"></span>
                            <span class="text-[8px] text-slate-600 font-bold">G1 &bull; G3 = 1</span>
                        </div>

                        <!-- OUT 2 (G2 OR G3) -->
                        <div class="flex flex-col items-center justify-center p-1.5 bg-amber-50/50 rounded-xl border border-amber-200">
                            <span class="text-[8.5px] font-mono-code text-slate-700 font-bold mb-0.5">BOHLAM 2 (CARRY)</span>
                            <div class="circuit-bulb w-10 h-10 sm:w-12 sm:h-12 rounded-full border-2 flex items-center justify-center my-0.5 transition-all"
                                 :class="out2L3 ? 'bg-amber-400 text-slate-950 border-amber-400 shadow-[0_0_15px_#facc15]' : 'bg-slate-200 text-slate-400 border-slate-300'">
                                <i class="fa-solid fa-lightbulb text-lg sm:text-xl" :class="out2L3 ? 'text-slate-950 scale-110' : 'text-slate-400'"></i>
                            </div>
                            <span class="text-[10px] font-mono-code font-black mt-0.5" :class="out2L3 ? 'text-emerald-800' : 'text-slate-600'" x-text="out2L3 ? '1 (ON)' : '0 (OFF)'"></span>
                            <span class="text-[8px] text-slate-600 font-bold">G2 + G3 = 1</span>
                        </div>
                    </div>

                    <div class="mt-1.5 pt-1 border-t border-slate-200 text-[9.5px] text-center font-mono-code font-bold"
                         :class="outputL3 ? 'text-emerald-800 font-bold' : 'text-slate-600'">
                        <span x-text="outputL3 ? '🎉 DUA BOHLAM MENYALA! ✓' : 'Nyalakan kedua bohlam'"></span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Footer Tips Teori Gerbang Logika & Aljabar Boolean -->
    <div class="hide-in-landscape mt-2 p-2 bg-indigo-50 border border-indigo-200 rounded-xl flex flex-wrap items-center justify-between gap-1.5 text-[10.5px] text-indigo-950 font-mono-code">
        <div class="flex items-center gap-1.5">
            <span class="text-xs">💡</span>
            <span class="font-bold">Edukasi IT & Logika:</span>
            <span class="bg-white px-1.5 py-0.2 rounded border border-indigo-300 font-black text-indigo-950 shadow-2xs">
                Aljabar Boolean (Claude Shannon, 1937) — Fondasi Logika Komputer & Ponsel Modern
            </span>
        </div>
        <div class="text-[10px] text-indigo-800 font-bold hidden sm:block">
            Semua mikroprosesor CPU di dunia dibangun dari jutaan saklar logika seperti ini!
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL CHEATSHEET TABEL KEBENARAN + ANALOGI KEHIDUPAN NYATA          -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="showTruthTable" class="fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs" style="display: none;">
        <div @click.away="showTruthTable = false" class="bg-white border-2 border-[#121316] rounded-2xl w-full max-w-xl overflow-hidden shadow-[6px_6px_0px_#121316] max-h-[88vh] flex flex-col">
            <div class="p-3 bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 text-white flex items-center justify-between border-b-2 border-[#121316]">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📖</span>
                    <div>
                        <h3 class="font-black text-sm">Kunci Rahasia Gerbang Logika (Truth Table)</h3>
                        <p class="text-[10px] text-indigo-100">Dipahami dengan mudah lewat analogi kehidupan sehari-hari</p>
                    </div>
                </div>
                <button type="button" @click="showTruthTable = false" class="text-white hover:text-amber-300 font-black text-base px-1.5 cursor-pointer">✕</button>
            </div>
            
            <div class="p-3.5 space-y-2.5 overflow-y-auto font-mono-code text-xs text-slate-800">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                    
                    <!-- AND -->
                    <div class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200">
                        <span class="font-black text-indigo-950 block mb-0.5">1. GERBANG AND (DAN)</span>
                        <p class="text-[10px] text-slate-700 mb-1">
                            <b>Kuncinya:</b> Menyala HANYA jika <b>SEMUA</b> saklar ON (1 dan 1).
                        </p>
                        <div class="bg-white p-1.5 rounded border border-indigo-200 text-[9.5px] text-slate-800">
                            🔑 <i>Analogi:</i> Pintu brankas 2 kunci. Harus Kunci A <b>DAN</b> Kunci B terbuka!
                        </div>
                    </div>

                    <!-- OR -->
                    <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200">
                        <span class="font-black text-amber-950 block mb-0.5">2. GERBANG OR (ATAU)</span>
                        <p class="text-[10px] text-slate-700 mb-1">
                            <b>Kuncinya:</b> Menyala jika <b>SALAH SATU</b> atau keduanya ON.
                        </p>
                        <div class="bg-white p-1.5 rounded border border-amber-200 text-[9.5px] text-slate-800">
                            🚪 <i>Analogi:</i> Pintu darurat 2 tombol. Tekan Tombol A <b>ATAU</b> Tombol B, pintu terbuka!
                        </div>
                    </div>

                    <!-- NOT -->
                    <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200">
                        <span class="font-black text-rose-950 block mb-0.5">3. GERBANG NOT (PEMBALIK)</span>
                        <p class="text-[10px] text-slate-700 mb-1">
                            <b>Kuncinya:</b> Membalik nilai: <b>0 jadi 1</b>, dan <b>1 jadi 0</b>.
                        </p>
                        <div class="bg-white p-1.5 rounded border border-rose-200 text-[9.5px] text-slate-800">
                            💡 <i>Analogi:</i> Lampu jalan otomatis. Saat matahari mati (malam), lampu justru MENYALA.
                        </div>
                    </div>

                    <!-- XOR -->
                    <div class="p-2.5 rounded-xl bg-fuchsia-50 border border-fuchsia-200">
                        <span class="font-black text-fuchsia-950 block mb-0.5">4. GERBANG XOR (BEDA)</span>
                        <p class="text-[10px] text-slate-700 mb-1">
                            <b>Kuncinya:</b> Menyala HANYA jika kedua input <b>BERBEDA</b> (1 dan 0).
                        </p>
                        <div class="bg-white p-1.5 rounded border border-fuchsia-200 text-[9.5px] text-slate-800">
                            🪜 <i>Analogi:</i> Saklar tangga 2 arah. Jika posisi saklar atas dan bawah berbeda, lampu menyala.
                        </div>
                    </div>

                    <!-- NAND -->
                    <div class="p-2.5 rounded-xl bg-cyan-50 border border-cyan-200">
                        <span class="font-black text-cyan-950 block mb-0.5">5. GERBANG NAND (BUKAN AND)</span>
                        <p class="text-[10px] text-slate-700 mb-1">
                            <b>Kuncinya:</b> Kebalikan dari AND. Mati HANYA jika keduanya bernilai 1.
                        </p>
                    </div>

                    <!-- NOR -->
                    <div class="p-2.5 rounded-xl bg-teal-50 border border-teal-200">
                        <span class="font-black text-teal-950 block mb-0.5">6. GERBANG NOR (BUKAN OR)</span>
                        <p class="text-[10px] text-slate-700 mb-1">
                            <b>Kuncinya:</b> Kebalikan dari OR. Menyala HANYA jika keduanya bernilai 0.
                        </p>
                    </div>

                </div>

                <div class="p-2 bg-slate-100 rounded-xl text-center text-[10.5px] text-slate-700 font-bold border border-slate-200">
                    💡 <b>Tip Strategi:</b> Perhatikan apa syarat gerbang sebelum lampu target, lalu atur saklar di sebelah kiri agar memenuhi syarat tersebut!
                </div>
            </div>

            <div class="p-2.5 bg-slate-50 border-t-2 border-[#121316] flex justify-end">
                <button type="button" @click="showTruthTable = false" class="btn-tactile-yellow px-4 py-1.5 rounded-xl text-xs font-black shadow-[2px_2px_0px_#121316] cursor-pointer">
                    Saya Mengerti, Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL KEMENANGAN (VICTORY MODAL)                                    -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="victoryModal.show" class="fixed inset-0 z-[130] flex items-center justify-center p-3 sm:p-4 bg-black/75 backdrop-blur-xs" style="display: none;">
        <div class="bg-white border-3 border-[#121316] rounded-2xl w-full max-w-sm overflow-hidden shadow-[6px_6px_0px_#121316] p-4 text-center animate-bounce-short">
            <div class="w-16 h-16 rounded-full bg-emerald-100 border-2 border-emerald-500 text-emerald-700 flex items-center justify-center mx-auto mb-2 text-2xl shadow-md">
                <i class="fa-solid fa-bolt-lightning animate-pulse"></i>
            </div>

            <h3 class="text-base font-black text-[#121316]">SIRKUIT BERHASIL DITERANGI!</h3>
            <p class="text-xs text-slate-700 mt-0.5 font-medium">Logika biner Anda sempurna mengalirkan arus listrik ke lampu target!</p>

            <div class="my-3 py-2 px-3 bg-amber-50 rounded-xl border-2 border-amber-300">
                <!-- Bintang -->
                <div class="flex items-center justify-center gap-1 text-amber-500 text-lg mb-1">
                    <template x-for="i in victoryModal.stars" :key="'star-' + i">
                        <i class="fa-solid fa-star"></i>
                    </template>
                </div>
                <!-- Poin STEAM -->
                <div class="text-sm font-black font-mono-code text-amber-950">
                    +<span x-text="victoryModal.points"></span> Poin STEAM Reputasi
                </div>
                <!-- Gelar -->
                <div class="text-[11px] font-bold text-amber-900 mt-1" x-text="victoryModal.rankTitle"></div>
            </div>

            <div class="flex gap-2">
                <button type="button" @click="victoryModal.show = false; changeLevel(level < 3 ? level + 1 : 1)" class="flex-1 btn-tactile-yellow py-2 rounded-xl text-xs font-black shadow-[2px_2px_0px_#121316] cursor-pointer">
                    <span x-text="level < 3 ? 'Lanjut Level ' + (level + 1) + ' ►' : 'Mainkan Ulang ↺'"></span>
                </button>
                <button type="button" @click="victoryModal.show = false" class="btn-tactile-white py-2 px-3 rounded-xl text-xs font-black shadow-[2px_2px_0px_#121316] cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- SCRIPT ENGINE SIRKUIT GERBANG LOGIKA (ALPINE.JS ENGINE)                 -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<script>
function logicGatesGame() {
    return {
        level: 1, // 1: Dasar, 2: XOR & NOT, 3: Master CPU
        switches: {
            A: false,
            B: false,
            C: false,
            D: false
        },
        moves: 0,
        timerSeconds: 0,
        timerInterval: null,
        isCooldown: false,
        cooldownSeconds: 0,
        hasWonToday: false,
        history: [], // Untuk fitur undo
        showTruthTable: false,

        alertToast: {
            show: false,
            message: '',
            isSuccess: false
        },

        victoryModal: {
            show: false,
            stars: 3,
            points: 50,
            rankTitle: '',
            message: ''
        },

        get optimalMoves() {
            if (this.level === 1) return 1;
            if (this.level === 2) return 1;
            return 2;
        },

        // Logika Sirkuit Level 1
        get gate1L1() {
            return this.switches.A && this.switches.B; // AND
        },
        get gate2L1() {
            return this.switches.A || this.switches.B; // OR
        },
        get outputL1() {
            return this.gate1L1 !== this.gate2L1; // XOR
        },

        // Logika Sirkuit Level 2
        get gate1L2() {
            return this.switches.A !== this.switches.B; // XOR
        },
        get gate2L2() {
            return !this.switches.C; // NOT
        },
        get outputL2() {
            return this.gate1L2 && this.gate2L2; // AND
        },

        // Logika Sirkuit Level 3
        get gate1L3() {
            return !(this.switches.A && this.switches.B); // NAND
        },
        get gate2L3() {
            return !(this.switches.C || this.switches.D); // NOR
        },
        get gate3L3() {
            return this.switches.B !== this.switches.C; // XOR
        },
        get out1L3() {
            return this.gate1L3 && this.gate3L3;
        },
        get out2L3() {
            return this.gate2L3 || this.gate3L3;
        },
        get outputL3() {
            return this.out1L3 && this.out2L3;
        },

        init() {
            this.resetSwitches();
            this.fetchStatus();
            this.startTimer();
        },

        changeLevel(newLevel) {
            if (this.isCooldown) return;
            this.level = newLevel;
            this.resetSwitches();
            this.timerSeconds = 0;
            this.victoryModal.show = false;
        },

        resetSwitches() {
            this.switches.A = false;
            this.switches.B = false;
            this.switches.C = false;
            this.switches.D = false;
            this.moves = 0;
            this.history = [];
            this.alertToast.show = false;
        },

        toggleSwitch(swKey) {
            if (this.isCooldown || this.victoryModal.show) return;
            
            this.history.push({ ...this.switches });
            this.switches[swKey] = !this.switches[swKey];
            this.moves++;

            this.checkVictory();
        },

        undoMove() {
            if (this.history.length === 0 || this.isCooldown || this.victoryModal.show) return;
            const lastState = this.history.pop();
            this.switches = { ...lastState };
            this.moves--;
        },

        checkVictory() {
            let isWon = false;
            if (this.level === 1 && this.outputL1) isWon = true;
            else if (this.level === 2 && this.outputL2) isWon = true;
            else if (this.level === 3 && this.outputL3) isWon = true;

            if (isWon) {
                this.submitVictory();
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
                        game_key: 'logic_gates',
                        moves: this.moves,
                        lives_left: 3,
                        seconds_taken: Math.max(2, this.timerSeconds)
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
                console.error('Submit Logic Gates victory error:', e);
            }
        },

        async fetchStatus() {
            try {
                const res = await fetch('/forum/challenge/status?game_key=logic_gates', {
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
                console.warn('Gagal memuat status Logic Gates:', e);
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
