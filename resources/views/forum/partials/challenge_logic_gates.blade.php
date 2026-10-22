<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- 🔌 PembdaCHALLENGE: Sirkuit Gerbang Logika IT (Logic Gates Master)       -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
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
        <button type="button" @click="alertToast.show = false" class="text-inherit hover:opacity-75 text-sm font-black px-1">✕</button>
    </div>

    <!-- Top Dashboard: Level Selector, Target, Stats, Undo & Cheatsheet -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2 p-2 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316]">
        
        <!-- Pilihan Misi Sirkuit -->
        <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap">
            <span class="text-[9.5px] font-mono-code font-bold text-slate-500 mr-0.5 hidden sm:inline">MISI:</span>
            <button type="button" 
                    @click="changeLevel(1)" 
                    :disabled="isCooldown"
                    class="px-2 py-0.5 rounded-lg text-[11px] font-black border-2 border-[#121316] transition-all cursor-pointer"
                    :class="level === 1 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                <span>Level 1: Dasar (AND/OR)</span>
            </button>
            <button type="button" 
                    @click="changeLevel(2)" 
                    :disabled="isCooldown"
                    class="px-2 py-0.5 rounded-lg text-[11px] font-black border-2 border-[#121316] transition-all cursor-pointer"
                    :class="level === 2 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                <span>Level 2: XOR & NOT</span>
            </button>
            <button type="button" 
                    @click="changeLevel(3)" 
                    :disabled="isCooldown"
                    class="px-2 py-0.5 rounded-lg text-[11px] font-black border-2 border-[#121316] transition-all cursor-pointer"
                    :class="level === 3 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                <span>Level 3: Master CPU (NAND/NOR)</span>
            </button>
        </div>

        <!-- Info Target Output & Langkah -->
        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap text-xs font-mono-code font-bold">
            <!-- Target LED -->
            <div class="flex items-center gap-1.5 px-2 py-0.5 bg-emerald-100 border border-emerald-400 rounded-lg text-emerald-950 font-black shadow-2xs">
                <span class="text-[10px]">🎯 TARGET:</span>
                <span class="text-xs text-emerald-700 underline font-black" x-text="level === 3 ? 'OUT1=1 & OUT2=1' : 'OUTPUT = 1 (HIGH)'"></span>
            </div>

            <!-- Langkah / Toggle Count -->
            <div class="flex items-center gap-1" title="Jumlah kali saklar dialihkan">
                <span class="text-slate-500 text-[10px]">TOGGLE:</span>
                <span class="px-1.5 py-0.2 rounded bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="moves"></span>
                <span class="text-[9px] text-slate-500" x-text="'(Opt: ' + optimalMoves + ')'"></span>
            </div>

            <!-- Waktu -->
            <div class="flex items-center gap-1">
                <span class="text-slate-500 text-[10px]">WAKTU:</span>
                <span class="px-1.5 py-0.2 rounded bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="formatTime(timerSeconds)"></span>
            </div>
        </div>

        <!-- Tombol Aksi: Undo, Reset, & Tabel Kebenaran -->
        <div class="flex items-center gap-1">
            <button type="button" 
                    @click="undoMove()" 
                    :disabled="history.length === 0 || isCooldown || victoryModal.show" 
                    class="btn-tactile-white px-2 py-0.5 rounded-lg text-[11px] font-black disabled:opacity-40 cursor-pointer"
                    title="Batalkan 1 saklar terakhir">
                <i class="fa-solid fa-arrow-rotate-left mr-0.5"></i> Undo
            </button>
            <button type="button" 
                    @click="resetSwitches()" 
                    :disabled="isCooldown || moves === 0" 
                    class="btn-tactile-white px-2 py-0.5 rounded-lg text-[11px] font-black disabled:opacity-40 cursor-pointer"
                    title="Nol-kan semua saklar">
                <i class="fa-solid fa-rotate-left mr-0.5"></i> Reset
            </button>
            <button type="button" 
                    @click="showTruthTable = true" 
                    class="p-1 px-2 rounded-lg bg-indigo-50 border border-indigo-300 text-indigo-900 text-xs font-black hover:bg-indigo-100 transition cursor-pointer flex items-center gap-1"
                    title="Buka Cheatsheet Tabel Kebenaran (Truth Table)">
                <i class="fa-solid fa-table"></i>
                <span class="text-[10px] hidden sm:inline">Tabel Kebenaran</span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ARENA PAPAN BREADBOARD / PCB SIRKUIT DIGITAL NEON                    -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="relative w-full rounded-2xl border-2 border-[#121316] bg-slate-950 p-2.5 sm:p-4 overflow-hidden shadow-[3px_3px_0px_#121316] select-none text-white">
        
        <!-- Grid PCB Background Glow Effect -->
        <div class="absolute inset-0 opacity-15 pointer-events-none" 
             style="background-image: radial-gradient(#10b981 1px, transparent 1px); background-size: 16px 16px;"></div>

        <!-- Header Petunjuk Aliran Arus Logika -->
        <div class="relative z-10 mb-2 flex items-center justify-between text-[10px] sm:text-xs font-mono-code flex-wrap gap-2 pb-1.5 border-b border-slate-800">
            <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-900 border border-slate-700 text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Aliran Arus Biner: <span class="text-emerald-400 font-bold">1 = HIGH (Menyala)</span>, <span class="text-rose-400 font-bold">0 = LOW (Mati)</span></span>
            </div>
            <div class="text-slate-400 font-bold hidden sm:block">
                <span>Klik Saklar Input (A, B, C...) untuk Mengubah Nilai Logika</span>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- LEVEL 1: SIRKUIT 2 INPUT (A, B) DENGAN GERBANG AND & OR         -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="level === 1" class="relative z-10 py-2">
            <div class="grid grid-cols-12 gap-2 sm:gap-4 items-center">
                
                <!-- KOLOM 1: SAKLAR INPUT (SWITCH A & B) -->
                <div class="col-span-3 flex flex-col gap-3 justify-center">
                    <!-- Switch A -->
                    <div class="bg-slate-900/90 border-2 border-slate-700 p-2 rounded-xl flex items-center justify-between shadow-md">
                        <div>
                            <span class="text-[10px] font-mono-code text-slate-400 block">INPUT A</span>
                            <span class="text-sm font-black font-mono-code" :class="switches.A ? 'text-emerald-400' : 'text-slate-400'" x-text="switches.A ? '1 (HIGH)' : '0 (LOW)'"></span>
                        </div>
                        <button type="button" 
                                @click="toggleSwitch('A')" 
                                class="w-10 h-7 rounded-lg border-2 border-[#121316] font-black text-xs transition-all flex items-center justify-center cursor-pointer shadow-xs active:scale-95"
                                :class="switches.A ? 'bg-emerald-500 text-white shadow-[0_0_10px_#10b981]' : 'bg-slate-700 text-slate-300'">
                            <span x-text="switches.A ? 'ON' : 'OFF'"></span>
                        </button>
                    </div>

                    <!-- Switch B -->
                    <div class="bg-slate-900/90 border-2 border-slate-700 p-2 rounded-xl flex items-center justify-between shadow-md">
                        <div>
                            <span class="text-[10px] font-mono-code text-slate-400 block">INPUT B</span>
                            <span class="text-sm font-black font-mono-code" :class="switches.B ? 'text-emerald-400' : 'text-slate-400'" x-text="switches.B ? '1 (HIGH)' : '0 (LOW)'"></span>
                        </div>
                        <button type="button" 
                                @click="toggleSwitch('B')" 
                                class="w-10 h-7 rounded-lg border-2 border-[#121316] font-black text-xs transition-all flex items-center justify-center cursor-pointer shadow-xs active:scale-95"
                                :class="switches.B ? 'bg-emerald-500 text-white shadow-[0_0_10px_#10b981]' : 'bg-slate-700 text-slate-300'">
                            <span x-text="switches.B ? 'ON' : 'OFF'"></span>
                        </button>
                    </div>
                </div>

                <!-- KOLOM 2: GERBANG LOGIKA TAHAP 1 (AND & OR) -->
                <div class="col-span-5 flex flex-col gap-3 justify-center">
                    <!-- Gerbang 1: AND (A AND B) -->
                    <div class="bg-slate-900 border-2 border-indigo-500/60 p-2 rounded-xl relative shadow-md">
                        <div class="flex items-center justify-between text-[11px] font-mono-code font-bold mb-1">
                            <span class="px-1.5 py-0.2 bg-indigo-600/80 rounded text-indigo-100">GERBANG 1: AND</span>
                            <span class="text-[10px]" :class="gate1L1 ? 'text-emerald-400' : 'text-rose-400'" x-text="'Out = ' + (gate1L1 ? '1' : '0')"></span>
                        </div>
                        <div class="text-[10px] text-slate-300 font-mono-code flex items-center justify-between bg-slate-950/70 px-2 py-1 rounded">
                            <span>A • B = <span x-text="switches.A ? 1 : 0"></span> • <span x-text="switches.B ? 1 : 0"></span></span>
                            <span class="font-black" :class="gate1L1 ? 'text-emerald-400' : 'text-slate-500'" x-text="gate1L1 ? '► 1 (TRUE)' : '► 0 (FALSE)'"></span>
                        </div>
                    </div>

                    <!-- Gerbang 2: OR (A OR B) -->
                    <div class="bg-slate-900 border-2 border-amber-500/60 p-2 rounded-xl relative shadow-md">
                        <div class="flex items-center justify-between text-[11px] font-mono-code font-bold mb-1">
                            <span class="px-1.5 py-0.2 bg-amber-600/80 rounded text-amber-100">GERBANG 2: OR</span>
                            <span class="text-[10px]" :class="gate2L1 ? 'text-emerald-400' : 'text-rose-400'" x-text="'Out = ' + (gate2L1 ? '1' : '0')"></span>
                        </div>
                        <div class="text-[10px] text-slate-300 font-mono-code flex items-center justify-between bg-slate-950/70 px-2 py-1 rounded">
                            <span>A + B = <span x-text="switches.A ? 1 : 0"></span> + <span x-text="switches.B ? 1 : 0"></span></span>
                            <span class="font-black" :class="gate2L1 ? 'text-emerald-400' : 'text-slate-500'" x-text="gate2L1 ? '► 1 (TRUE)' : '► 0 (FALSE)'"></span>
                        </div>
                    </div>
                </div>

                <!-- KOLOM 3: OUTPUT AKHIR (XOR GATE & LED LAMP) -->
                <div class="col-span-4 flex flex-col items-center justify-center p-2 bg-slate-900/90 border-2 border-slate-700 rounded-xl relative shadow-lg">
                    <!-- Gerbang Akhir: XOR antara Gerbang 1 dan Gerbang 2 -->
                    <div class="w-full text-center pb-1.5 mb-1.5 border-b border-slate-800 font-mono-code">
                        <span class="text-[9.5px] px-1.5 py-0.5 rounded bg-fuchsia-600/80 text-white font-bold">GERBANG AKHIR: XOR</span>
                        <div class="text-[9px] text-slate-400 mt-1">G1 ⊕ G2 (Berbeda = 1)</div>
                    </div>

                    <!-- Lampu Indikator LED Utama -->
                    <div class="my-1 flex flex-col items-center justify-center">
                        <div class="w-14 h-14 rounded-full border-3 border-[#121316] flex items-center justify-center transition-all duration-300"
                             :class="outputL1 ? 'bg-emerald-400 text-[#121316] shadow-[0_0_30px_#10b981] scale-105' : 'bg-slate-800 text-slate-500 shadow-inner'">
                            <i class="fa-solid fa-lightbulb text-2xl transition-transform" :class="outputL1 ? 'scale-110' : ''"></i>
                        </div>
                        <span class="text-xs font-mono-code font-black mt-1" :class="outputL1 ? 'text-emerald-400 animate-pulse' : 'text-slate-500'">
                            <span x-text="outputL1 ? '● 1 (MENYALA)' : '○ 0 (MATI)'"></span>
                        </span>
                    </div>

                    <!-- Target Status -->
                    <div class="w-full text-center mt-1">
                        <span class="text-[9px] font-mono-code px-2 py-0.5 rounded-full"
                              :class="outputL1 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40'">
                            <span x-text="outputL1 ? '🎯 TARGET TERCAPAI ✓' : 'Belum Mencapai Target'"></span>
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- LEVEL 2: SIRKUIT 3 INPUT (A, B, C) DENGAN NOT, XOR, & AND       -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="level === 2" class="relative z-10 py-1.5">
            <div class="grid grid-cols-12 gap-2 sm:gap-3 items-center">
                
                <!-- KOLOM 1: SAKLAR INPUT (SWITCH A, B, C) -->
                <div class="col-span-3 flex flex-col gap-2 justify-center">
                    <!-- Switch A -->
                    <div class="bg-slate-900 border border-slate-700 p-1.5 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-[9px] font-mono-code text-slate-400 block">INPUT A</span>
                            <span class="text-xs font-black font-mono-code" :class="switches.A ? 'text-emerald-400' : 'text-slate-400'" x-text="switches.A ? '1' : '0'"></span>
                        </div>
                        <button type="button" @click="toggleSwitch('A')" class="px-2 py-0.5 rounded-md text-[10px] font-black border border-slate-900 cursor-pointer" :class="switches.A ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-300'">
                            <span x-text="switches.A ? 'ON' : 'OFF'"></span>
                        </button>
                    </div>

                    <!-- Switch B -->
                    <div class="bg-slate-900 border border-slate-700 p-1.5 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-[9px] font-mono-code text-slate-400 block">INPUT B</span>
                            <span class="text-xs font-black font-mono-code" :class="switches.B ? 'text-emerald-400' : 'text-slate-400'" x-text="switches.B ? '1' : '0'"></span>
                        </div>
                        <button type="button" @click="toggleSwitch('B')" class="px-2 py-0.5 rounded-md text-[10px] font-black border border-slate-900 cursor-pointer" :class="switches.B ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-300'">
                            <span x-text="switches.B ? 'ON' : 'OFF'"></span>
                        </button>
                    </div>

                    <!-- Switch C -->
                    <div class="bg-slate-900 border border-slate-700 p-1.5 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-[9px] font-mono-code text-slate-400 block">INPUT C</span>
                            <span class="text-xs font-black font-mono-code" :class="switches.C ? 'text-emerald-400' : 'text-slate-400'" x-text="switches.C ? '1' : '0'"></span>
                        </div>
                        <button type="button" @click="toggleSwitch('C')" class="px-2 py-0.5 rounded-md text-[10px] font-black border border-slate-900 cursor-pointer" :class="switches.C ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-300'">
                            <span x-text="switches.C ? 'ON' : 'OFF'"></span>
                        </button>
                    </div>
                </div>

                <!-- KOLOM 2: GERBANG LOGIKA TAHAP 1 (XOR & NOT) -->
                <div class="col-span-5 flex flex-col gap-2.5 justify-center">
                    <!-- Gerbang XOR (A XOR B) -->
                    <div class="bg-slate-900 border border-fuchsia-500/60 p-2 rounded-xl">
                        <div class="flex items-center justify-between text-[10.5px] font-mono-code mb-1">
                            <span class="px-1.5 py-0.2 bg-fuchsia-600/80 rounded font-bold">GERBANG 1: XOR</span>
                            <span :class="gate1L2 ? 'text-emerald-400' : 'text-rose-400'" x-text="'Out = ' + (gate1L2 ? '1' : '0')"></span>
                        </div>
                        <div class="text-[9.5px] text-slate-300 font-mono-code bg-slate-950 px-2 py-1 rounded flex justify-between">
                            <span>A ⊕ B</span>
                            <span :class="gate1L2 ? 'text-emerald-400 font-bold' : 'text-slate-500'" x-text="gate1L2 ? '► 1' : '► 0'"></span>
                        </div>
                    </div>

                    <!-- Gerbang NOT (NOT C) (Inverter) -->
                    <div class="bg-slate-900 border border-rose-500/60 p-2 rounded-xl">
                        <div class="flex items-center justify-between text-[10.5px] font-mono-code mb-1">
                            <span class="px-1.5 py-0.2 bg-rose-600/80 rounded font-bold">GERBANG 2: NOT (Inverter)</span>
                            <span :class="gate2L2 ? 'text-emerald-400' : 'text-rose-400'" x-text="'Out = ' + (gate2L2 ? '1' : '0')"></span>
                        </div>
                        <div class="text-[9.5px] text-slate-300 font-mono-code bg-slate-950 px-2 py-1 rounded flex justify-between">
                            <span>¬C (Kebalikan C)</span>
                            <span :class="gate2L2 ? 'text-emerald-400 font-bold' : 'text-slate-500'" x-text="gate2L2 ? '► 1' : '► 0'"></span>
                        </div>
                    </div>
                </div>

                <!-- KOLOM 3: OUTPUT AKHIR (AND GATE & LED) -->
                <div class="col-span-4 flex flex-col items-center justify-center p-2 bg-slate-900 border border-slate-700 rounded-xl">
                    <div class="w-full text-center pb-1 mb-1 border-b border-slate-800 font-mono-code">
                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-indigo-600 font-bold">GERBANG AKHIR: AND</span>
                        <div class="text-[8.5px] text-slate-400 mt-0.5">Keduanya Harus 1</div>
                    </div>

                    <div class="my-1 flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-full border-2 border-[#121316] flex items-center justify-center transition-all"
                             :class="outputL2 ? 'bg-emerald-400 text-slate-950 shadow-[0_0_25px_#10b981] scale-105' : 'bg-slate-800 text-slate-500'">
                            <i class="fa-solid fa-lightbulb text-xl"></i>
                        </div>
                        <span class="text-xs font-mono-code font-black mt-1" :class="outputL2 ? 'text-emerald-400' : 'text-slate-500'" x-text="outputL2 ? '1 (MENYALA)' : '0 (MATI)'"></span>
                    </div>

                    <span class="text-[8.5px] font-mono-code px-1.5 py-0.2 rounded-full mt-1"
                          :class="outputL2 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40'">
                        <span x-text="outputL2 ? 'TARGET TERCAPAI ✓' : 'Belum Selesai'"></span>
                    </span>
                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- LEVEL 3: MASTER CPU 4-BIT ALU (A, B, C, D) DENGAN 2 OUTPUT     -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div x-show="level === 3" class="relative z-10 py-1">
            <div class="grid grid-cols-12 gap-2 sm:gap-2.5 items-center">
                
                <!-- KOLOM 1: 4 SAKLAR INPUT (A, B, C, D) -->
                <div class="col-span-3 grid grid-cols-2 gap-1.5">
                    <template x-for="swKey in ['A', 'B', 'C', 'D']" :key="'sw-l3-' + swKey">
                        <div class="bg-slate-900 border border-slate-700 p-1.5 rounded-lg flex flex-col items-center justify-between">
                            <span class="text-[8.5px] font-mono-code text-slate-400" x-text="'IN ' + swKey"></span>
                            <span class="text-[11px] font-mono-code font-black my-0.5" :class="switches[swKey] ? 'text-emerald-400' : 'text-slate-500'" x-text="switches[swKey] ? '1' : '0'"></span>
                            <button type="button" @click="toggleSwitch(swKey)" class="w-full py-0.5 rounded text-[9px] font-black border border-slate-900 cursor-pointer" :class="switches[swKey] ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-300'">
                                <span x-text="switches[swKey] ? 'ON' : 'OFF'"></span>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- KOLOM 2: KOMBINASI GERBANG NAND, NOR, XOR -->
                <div class="col-span-5 flex flex-col gap-1.5 justify-center">
                    <!-- NAND (A NAND B) -->
                    <div class="bg-slate-900 border border-cyan-500/50 p-1.5 rounded-lg flex items-center justify-between text-[9.5px] font-mono-code">
                        <span>G1: NAND (A, B)</span>
                        <span class="font-black" :class="gate1L3 ? 'text-emerald-400' : 'text-rose-400'" x-text="gate1L3 ? '► 1' : '► 0'"></span>
                    </div>

                    <!-- NOR (C NOR D) -->
                    <div class="bg-slate-900 border border-teal-500/50 p-1.5 rounded-lg flex items-center justify-between text-[9.5px] font-mono-code">
                        <span>G2: NOR (C, D)</span>
                        <span class="font-black" :class="gate2L3 ? 'text-emerald-400' : 'text-rose-400'" x-text="gate2L3 ? '► 1' : '► 0'"></span>
                    </div>

                    <!-- XOR (B XOR C) -->
                    <div class="bg-slate-900 border border-purple-500/50 p-1.5 rounded-lg flex items-center justify-between text-[9.5px] font-mono-code">
                        <span>G3: XOR (B, C)</span>
                        <span class="font-black" :class="gate3L3 ? 'text-emerald-400' : 'text-rose-400'" x-text="gate3L3 ? '► 1' : '► 0'"></span>
                    </div>
                </div>

                <!-- KOLOM 3: DUA OUTPUT LED SEKALIGUS (OUT 1 & OUT 2) -->
                <div class="col-span-4 grid grid-cols-2 gap-1.5 p-2 bg-slate-900 border border-slate-700 rounded-xl text-center">
                    <!-- OUT 1 (G1 AND G3) -->
                    <div class="flex flex-col items-center justify-center p-1 bg-slate-950 rounded-lg">
                        <span class="text-[8px] font-mono-code text-slate-400">OUT 1 (SUM)</span>
                        <div class="w-9 h-9 rounded-full border border-slate-700 flex items-center justify-center my-1"
                             :class="out1L3 ? 'bg-emerald-400 text-slate-950 shadow-[0_0_15px_#10b981]' : 'bg-slate-800 text-slate-500'">
                            <i class="fa-solid fa-lightbulb text-sm"></i>
                        </div>
                        <span class="text-[9.5px] font-mono-code font-black" :class="out1L3 ? 'text-emerald-400' : 'text-slate-500'" x-text="out1L3 ? '1 (ON)' : '0 (OFF)'"></span>
                    </div>

                    <!-- OUT 2 (G2 OR G3) -->
                    <div class="flex flex-col items-center justify-center p-1 bg-slate-950 rounded-lg">
                        <span class="text-[8px] font-mono-code text-slate-400">OUT 2 (CARRY)</span>
                        <div class="w-9 h-9 rounded-full border border-slate-700 flex items-center justify-center my-1"
                             :class="out2L3 ? 'bg-emerald-400 text-slate-950 shadow-[0_0_15px_#10b981]' : 'bg-slate-800 text-slate-500'">
                            <i class="fa-solid fa-lightbulb text-sm"></i>
                        </div>
                        <span class="text-[9.5px] font-mono-code font-black" :class="out2L3 ? 'text-emerald-400' : 'text-slate-500'" x-text="out2L3 ? '1 (ON)' : '0 (OFF)'"></span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Footer Tips Teori Gerbang Logika & Aljabar Boolean -->
    <div class="mt-2 p-1.5 bg-indigo-50/60 border border-indigo-200 rounded-xl flex flex-wrap items-center justify-between gap-1.5 text-[10.5px] text-indigo-900 font-mono-code">
        <div class="flex items-center gap-1.5">
            <span class="text-xs">💡</span>
            <span class="font-bold">Teori Aljabar Boolean:</span>
            <span class="bg-white px-1.5 py-0.2 rounded border border-indigo-300 font-black text-indigo-950">
                Claude Shannon (1937) — Pondasi Arsitektur CPU Komputer Modern
            </span>
        </div>
        <div class="text-[10px] text-indigo-700 hidden sm:block">
            Universal Gates: NAND & NOR dapat membentuk semua gerbang logika lainnya!
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL CHEATSHEET TABEL KEBENARAN (TRUTH TABLE)                      -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="showTruthTable" class="fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs" style="display: none;">
        <div @click.away="showTruthTable = false" class="bg-white border-2 border-[#121316] rounded-2xl w-full max-w-lg overflow-hidden shadow-[6px_6px_0px_#121316] max-h-[85vh] flex flex-col">
            <div class="p-3 bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 text-white flex items-center justify-between border-b-2 border-[#121316]">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📖</span>
                    <h3 class="font-black text-sm">Cheatsheet Tabel Kebenaran (Truth Table)</h3>
                </div>
                <button type="button" @click="showTruthTable = false" class="text-white hover:text-amber-300 font-black text-base px-1.5 cursor-pointer">✕</button>
            </div>
            
            <div class="p-3.5 space-y-3 overflow-y-auto font-mono-code text-xs text-slate-800">
                <div class="grid grid-cols-2 gap-2 text-[11px]">
                    <!-- AND -->
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-300">
                        <span class="font-black text-indigo-700 block mb-1">AND (Konjungsi •)</span>
                        <p class="text-[10px] text-slate-600 mb-1">Output = 1 HANYA jika semua input bernilai 1.</p>
                        <div class="bg-white p-1 rounded border border-slate-200 text-[9.5px]">
                            0 • 0 = 0 | 0 • 1 = 0<br>
                            1 • 0 = 0 | <span class="font-bold text-emerald-600">1 • 1 = 1</span>
                        </div>
                    </div>

                    <!-- OR -->
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-300">
                        <span class="font-black text-amber-700 block mb-1">OR (Disjungsi +)</span>
                        <p class="text-[10px] text-slate-600 mb-1">Output = 1 jika MINIMAL salah satu input bernilai 1.</p>
                        <div class="bg-white p-1 rounded border border-slate-200 text-[9.5px]">
                            0 + 0 = 0 | <span class="font-bold text-emerald-600">0 + 1 = 1</span><br>
                            <span class="font-bold text-emerald-600">1 + 0 = 1</span> | <span class="font-bold text-emerald-600">1 + 1 = 1</span>
                        </div>
                    </div>

                    <!-- NOT -->
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-300">
                        <span class="font-black text-rose-700 block mb-1">NOT (Inverter ¬)</span>
                        <p class="text-[10px] text-slate-600 mb-1">Membalik nilai input.</p>
                        <div class="bg-white p-1 rounded border border-slate-200 text-[9.5px]">
                            ¬0 = <span class="font-bold text-emerald-600">1</span> | ¬1 = <span class="font-bold text-rose-600">0</span>
                        </div>
                    </div>

                    <!-- XOR -->
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-300">
                        <span class="font-black text-fuchsia-700 block mb-1">XOR (Exclusive OR ⊕)</span>
                        <p class="text-[10px] text-slate-600 mb-1">Output = 1 jika kedua input BERBEDA.</p>
                        <div class="bg-white p-1 rounded border border-slate-200 text-[9.5px]">
                            0 ⊕ 0 = 0 | <span class="font-bold text-emerald-600">0 ⊕ 1 = 1</span><br>
                            <span class="font-bold text-emerald-600">1 ⊕ 0 = 1</span> | 1 ⊕ 1 = 0
                        </div>
                    </div>
                </div>

                <!-- NAND & NOR -->
                <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-300 text-[10.5px]">
                    <span class="font-black text-emerald-900 block mb-0.5">NAND & NOR (Universal Gates)</span>
                    <p class="text-[10px] text-emerald-800">
                        <strong>NAND</strong> adalah kebalikan dari AND (¬(A • B)).<br>
                        <strong>NOR</strong> adalah kebalikan dari OR (¬(A + B)).
                    </p>
                </div>
            </div>

            <div class="p-2.5 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="button" @click="showTruthTable = false" class="btn-tactile-yellow px-4 py-1.5 rounded-xl text-xs font-black">
                    Paham, Lanjutkan!
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL KEMENANGAN GERBANG LOGIKA (LOGIC MASTER VICTORY)              -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="victoryModal.show" class="fixed inset-0 z-[130] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white border-2 border-[#121316] rounded-2xl w-full max-w-sm overflow-hidden shadow-[6px_6px_0px_#10b981] animate-bounce">
            <div class="p-3.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 text-white text-center border-b-2 border-[#121316]">
                <span class="text-3xl block mb-1">⚡💡🏆</span>
                <h3 class="font-black text-base sm:text-lg">SIRKUIT AKTIF SEMPURNA!</h3>
                <p class="text-[11px] text-emerald-100 font-mono-code mt-0.5" x-text="victoryModal.rankTitle"></p>
            </div>

            <div class="p-4 space-y-3 text-center">
                <!-- Bintang Kehormatan -->
                <div class="flex items-center justify-center gap-1.5 text-2xl text-amber-400">
                    <template x-for="s in victoryModal.stars" :key="s">
                        <i class="fa-solid fa-star drop-shadow-md"></i>
                    </template>
                </div>

                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-300 font-mono-code">
                    <div class="text-[11px] text-emerald-800 font-bold">Reward Reputasi STEAM:</div>
                    <div class="text-lg font-black text-emerald-700" x-text="'+' + victoryModal.points + ' Poin STEAM'"></div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Tuntas dalam <span class="font-bold text-[#121316]" x-text="moves"></span> kali saklar</div>
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
            if (this.level === 1) return 2;
            if (this.level === 2) return 2;
            return 3;
        },

        // ═══════════════════════════════════════════════════════════════
        // LOGIKA SIRKUIT LEVEL 1: (A AND B) XOR (A OR B)
        // ═══════════════════════════════════════════════════════════════
        get gate1L1() {
            return this.switches.A && this.switches.B; // AND
        },
        get gate2L1() {
            return this.switches.A || this.switches.B; // OR
        },
        get outputL1() {
            // XOR antara Gerbang 1 dan Gerbang 2
            return this.gate1L1 !== this.gate2L1;
        },

        // ═══════════════════════════════════════════════════════════════
        // LOGIKA SIRKUIT LEVEL 2: (A XOR B) AND (NOT C)
        // ═══════════════════════════════════════════════════════════════
        get gate1L2() {
            return this.switches.A !== this.switches.B; // XOR
        },
        get gate2L2() {
            return !this.switches.C; // NOT
        },
        get outputL2() {
            return this.gate1L2 && this.gate2L2; // AND
        },

        // ═══════════════════════════════════════════════════════════════
        // LOGIKA SIRKUIT LEVEL 3: 
        // G1 = NAND(A, B) = !(A && B)
        // G2 = NOR(C, D)  = !(C || D)
        // G3 = XOR(B, C)  = B !== C
        // Out1 = G1 && G3
        // Out2 = G2 || G3
        // ═══════════════════════════════════════════════════════════════
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
            
            // Simpan riwayat untuk undo
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
