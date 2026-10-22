<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- 🧪 PembdaCHALLENGE: Penyeimbang Reaksi Kimia (Chemical Balancing Lab)     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<style>
@keyframes bubble-rise {
    0% { transform: translateY(0) scale(0.8); opacity: 0.6; }
    50% { opacity: 0.9; }
    100% { transform: translateY(-30px) scale(1.2); opacity: 0; }
}
.chem-bubble {
    animation: bubble-rise 2s infinite ease-in;
}
@keyframes scale-glow {
    0%, 100% { box-shadow: 0 0 15px rgba(16, 185, 129, 0.4); }
    50% { box-shadow: 0 0 30px rgba(16, 185, 129, 0.8), 0 0 50px rgba(52, 211, 153, 0.4); }
}
.chem-balanced-glow {
    animation: scale-glow 1.5s infinite ease-in-out;
}
</style>

<div x-data="chemicalBalanceGame()" x-init="init()" class="w-full">
    
    <!-- Banner Cooldown (Jika Nyawa Habis) -->
    <div x-show="isCooldown" class="mb-2 p-3 rounded-2xl bg-rose-50 border-2 border-rose-600 text-rose-900 shadow-[3px_3px_0px_#e11d48]">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⏳</span>
            <div class="flex-1">
                <h4 class="font-black text-xs sm:text-sm text-rose-950">Reaksi Tidak Stabil (Cooldown)</h4>
                <p class="text-xs text-rose-800 mt-0.5">Analisis hukum kekekalan massa Lavoisier sebelum mencoba kembali.</p>
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
            <span class="text-sm" x-text="alertToast.isSuccess ? '🧪' : '💡'"></span>
            <span x-text="alertToast.message"></span>
        </div>
        <button type="button" @click="alertToast.show = false" class="text-inherit hover:opacity-75 text-sm font-black px-1">✕</button>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- 💡 PANDUAN CEPAT (CARA MAIN)                                         -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="mb-2 p-2 sm:p-2.5 rounded-xl bg-gradient-to-r from-cyan-50 via-teal-50 to-emerald-50 border-2 border-[#121316] text-[#121316] shadow-[2px_2px_0px_#121316]">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="text-xl">⚖️</span>
                <div>
                    <span class="font-black text-xs text-[#121316] block sm:inline">PANDUAN STOIKIOMETRI:</span>
                    <span class="text-[11px] text-slate-700">Setarakan jumlah atom di <b>Ruas Kiri (Reaktan)</b> dan <b>Ruas Kanan (Produk)</b>!</span>
                </div>
            </div>
            <div class="flex items-center gap-2 text-[10.5px] font-mono-code font-bold flex-wrap">
                <div class="flex items-center gap-1 bg-white px-2 py-0.5 rounded-md border border-slate-300">
                    <span class="text-cyan-700 font-black">1.</span>
                    <span>Klik <b>[-] / [+]</b> atur koefisien</span>
                </div>
                <div class="flex items-center gap-1 bg-white px-2 py-0.5 rounded-md border border-slate-300">
                    <span class="text-amber-700 font-black">2.</span>
                    <span>Samakan jumlah tiap atom</span>
                </div>
                <div class="flex items-center gap-1 bg-white px-2 py-0.5 rounded-md border border-slate-300">
                    <span class="text-emerald-700 font-black">3.</span>
                    <span>Neraca seimbang = <b>Menang</b>!</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Dashboard: Level Selector, Target, Stats, Undo & Cheatsheet -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2 p-2 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316]">
        
        <!-- Pilihan Misi Reaksi Kimia -->
        <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap">
            <span class="text-[9.5px] font-mono-code font-bold text-slate-500 mr-0.5 hidden sm:inline">REAKSI:</span>
            <button type="button" 
                    @click="changeLevel(1)" 
                    :disabled="isCooldown"
                    class="px-2 py-0.5 rounded-lg text-[11px] font-black border-2 border-[#121316] transition-all cursor-pointer"
                    :class="level === 1 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                <span>Lvl 1: Air (H₂ + O₂)</span>
            </button>
            <button type="button" 
                    @click="changeLevel(2)" 
                    :disabled="isCooldown"
                    class="px-2 py-0.5 rounded-lg text-[11px] font-black border-2 border-[#121316] transition-all cursor-pointer"
                    :class="level === 2 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                <span>Lvl 2: Metana (CH₄ + O₂)</span>
            </button>
            <button type="button" 
                    @click="changeLevel(3)" 
                    :disabled="isCooldown"
                    class="px-2 py-0.5 rounded-lg text-[11px] font-black border-2 border-[#121316] transition-all cursor-pointer"
                    :class="level === 3 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                <span>Lvl 3: Karat (Fe + O₂)</span>
            </button>
        </div>

        <!-- Info Target Output & Langkah -->
        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap text-xs font-mono-code font-bold">
            <!-- Target Status -->
            <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-lg font-black shadow-2xs border"
                 :class="isBalanced ? 'bg-emerald-100 border-emerald-400 text-emerald-950' : 'bg-amber-100 border-amber-400 text-amber-950'">
                <span class="text-[10px]">⚖️ STATUS:</span>
                <span class="text-xs underline font-black" x-text="isBalanced ? 'SEIMBANG (SETARA) ✓' : 'BELUM SEIMBANG'"></span>
            </div>

            <!-- Langkah / Penyesuaian -->
            <div class="flex items-center gap-1" title="Jumlah kali koefisien diubah">
                <span class="text-slate-500 text-[10px]">LANGKAH:</span>
                <span class="px-1.5 py-0.2 rounded bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="moves"></span>
                <span class="text-[9px] text-slate-500" x-text="'(Opt: ' + optimalMoves + ')'"></span>
            </div>

            <!-- Waktu -->
            <div class="flex items-center gap-1">
                <span class="text-slate-500 text-[10px]">WAKTU:</span>
                <span class="px-1.5 py-0.2 rounded bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="formatTime(timerSeconds)"></span>
            </div>
        </div>

        <!-- Tombol Aksi: Undo, Reset, & Panduan Hukum Lavoisier -->
        <div class="flex items-center gap-1">
            <button type="button" 
                    @click="undoMove()" 
                    :disabled="history.length === 0 || isCooldown || victoryModal.show" 
                    class="btn-tactile-white px-2 py-0.5 rounded-lg text-[11px] font-black disabled:opacity-40 cursor-pointer"
                    title="Batalkan 1 penyesuaian terakhir">
                <i class="fa-solid fa-arrow-rotate-left mr-0.5"></i> Undo
            </button>
            <button type="button" 
                    @click="resetReaction()" 
                    :disabled="isCooldown || moves === 0" 
                    class="btn-tactile-white px-2 py-0.5 rounded-lg text-[11px] font-black disabled:opacity-40 cursor-pointer"
                    title="Kembalikan semua koefisien ke 1">
                <i class="fa-solid fa-rotate-left mr-0.5"></i> Reset
            </button>
            <button type="button" 
                    @click="showRulesModal = true" 
                    class="p-1 px-2 rounded-lg bg-teal-50 border border-teal-300 text-teal-900 text-xs font-black hover:bg-teal-100 transition cursor-pointer flex items-center gap-1 shadow-2xs"
                    title="Buka Hukum Kekekalan Massa Lavoisier & Tips Stoikiometri">
                <i class="fa-solid fa-flask"></i>
                <span class="text-[10px] hidden sm:inline">Hukum Lavoisier</span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ARENA LABORATORIUM KIMIA SAINS (FULL WIDTH)                          -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="relative w-full rounded-2xl border-2 border-[#121316] bg-slate-950 p-3 sm:p-4 overflow-hidden shadow-[3px_3px_0px_#121316] select-none text-white">
        
        <!-- Lab Grid Ambient Glow Effect -->
        <div class="absolute inset-0 opacity-15 pointer-events-none" 
             style="background-image: radial-gradient(#06b6d4 1px, transparent 1px); background-size: 16px 16px;"></div>

        <!-- Header Deskripsi Reaksi -->
        <div class="relative z-10 mb-3 flex items-center justify-between text-[10.5px] sm:text-xs font-mono-code flex-wrap gap-2 pb-2 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-900 border border-slate-700 text-slate-200">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
                    <span>Reaksi: <b class="text-cyan-300" x-text="currentReaction.name"></b></span>
                </span>
            </div>
            <div class="text-amber-300 font-bold flex items-center gap-1 text-[11px]">
                <i class="fa-solid fa-scale-balanced"></i>
                <span>Hukum Kekekalan Massa: Massa Reaktan = Massa Produk</span>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- PANGGUNG FORMULA REAKSI DENGAN KONTROL KOEFISIEN INTERAKTIF     -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="relative z-10 py-2">
            
            <div class="bg-slate-900/90 border-2 rounded-2xl p-3 sm:p-4 shadow-lg transition-all"
                 :class="isBalanced ? 'border-emerald-500 chem-balanced-glow' : 'border-slate-700'">
                
                <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                    
                    <!-- RUAS KIRI: REAKTAN (REACTANTS) -->
                    <div class="flex-1 w-full bg-slate-950/80 p-3 rounded-xl border border-slate-800">
                        <div class="flex items-center justify-between mb-3 pb-1 border-b border-slate-800">
                            <span class="text-xs font-black text-cyan-400 font-mono-code flex items-center gap-1">
                                <i class="fa-solid fa-atom text-cyan-400"></i> RUAS KIRI (REAKTAN)
                            </span>
                            <span class="text-[9.5px] text-slate-400">Zat Awal Pereaksi</span>
                        </div>

                        <!-- Daftar Molekul Reaktan -->
                        <div class="flex flex-wrap items-center justify-around gap-3">
                            <template x-for="(mol, idx) in currentReaction.reactants" :key="'react-' + idx">
                                <div class="flex items-center gap-2 bg-slate-900/90 p-2 rounded-xl border border-slate-700 shadow-sm">
                                    <!-- Stepper Koefisien -->
                                    <div class="flex flex-col items-center">
                                        <button type="button" 
                                                @click="changeCoeff('reactants', idx, 1)"
                                                :disabled="isCooldown || victoryModal.show || mol.coeff >= 8"
                                                class="w-7 h-6 rounded bg-slate-800 hover:bg-slate-700 active:bg-cyan-600 text-white font-black text-xs border border-slate-600 disabled:opacity-30 cursor-pointer flex items-center justify-center transition">
                                            +
                                        </button>
                                        <div class="w-7 h-7 my-1 bg-[#fde047] text-[#121316] border border-[#121316] rounded-md font-mono-code font-black text-sm flex items-center justify-center shadow-xs"
                                             x-text="mol.coeff">
                                        </div>
                                        <button type="button" 
                                                @click="changeCoeff('reactants', idx, -1)"
                                                :disabled="isCooldown || victoryModal.show || mol.coeff <= 1"
                                                class="w-7 h-6 rounded bg-slate-800 hover:bg-slate-700 active:bg-cyan-600 text-white font-black text-xs border border-slate-600 disabled:opacity-30 cursor-pointer flex items-center justify-center transition">
                                            -
                                        </button>
                                    </div>

                                    <!-- Rumus Molekul & Visual Atom -->
                                    <div class="flex flex-col items-center pl-1">
                                        <span class="text-xl sm:text-2xl font-black font-mono-code tracking-wide text-white" x-html="mol.display"></span>
                                        <span class="text-[9px] text-slate-400 mt-0.5" x-text="mol.name"></span>
                                        
                                        <!-- Visualisasi Bola Atom Sederhana -->
                                        <div class="flex items-center gap-1 mt-1">
                                            <template x-for="(atomCount, atomName) in mol.atoms" :key="'atom-dot-' + atomName">
                                                <div class="flex items-center gap-0.5">
                                                    <span class="w-3 h-3 rounded-full border border-slate-950 flex items-center justify-center text-[7px] font-black"
                                                          :style="'background:' + getAtomColor(atomName) + '; color:' + (atomName === 'H' ? '#0f172a' : '#ffffff')"
                                                          x-text="atomName"></span>
                                                    <span class="text-[8px] text-slate-400 font-mono-code" x-text="'×' + (atomCount * mol.coeff)"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- SIMBOL REAKSI: PANAH TRANSISI (➔) & STATUS NERACA -->
                    <div class="flex flex-col items-center justify-center px-2">
                        <div class="w-12 h-12 rounded-full border-2 flex items-center justify-center text-xl transition-all shadow-md"
                             :class="isBalanced ? 'bg-emerald-500 text-slate-950 border-emerald-400 animate-bounce' : 'bg-slate-800 text-cyan-400 border-slate-700'">
                            <i class="fa-solid" :class="isBalanced ? 'fa-check' : 'fa-arrow-right'"></i>
                        </div>
                        <span class="text-[9.5px] font-mono-code font-black mt-1"
                              :class="isBalanced ? 'text-emerald-400' : 'text-slate-400'"
                              x-text="isBalanced ? 'SETARA ✓' : 'BEREAKSI'"></span>
                    </div>

                    <!-- RUAS KANAN: PRODUK (PRODUCTS) -->
                    <div class="flex-1 w-full bg-slate-950/80 p-3 rounded-xl border border-slate-800">
                        <div class="flex items-center justify-between mb-3 pb-1 border-b border-slate-800">
                            <span class="text-xs font-black text-emerald-400 font-mono-code flex items-center gap-1">
                                <i class="fa-solid fa-flask-vial text-emerald-400"></i> RUAS KANAN (PRODUK)
                            </span>
                            <span class="text-[9.5px] text-slate-400">Hasil Akhir Reaksi</span>
                        </div>

                        <!-- Daftar Molekul Produk -->
                        <div class="flex flex-wrap items-center justify-around gap-3">
                            <template x-for="(mol, idx) in currentReaction.products" :key="'prod-' + idx">
                                <div class="flex items-center gap-2 bg-slate-900/90 p-2 rounded-xl border border-slate-700 shadow-sm">
                                    <!-- Stepper Koefisien -->
                                    <div class="flex flex-col items-center">
                                        <button type="button" 
                                                @click="changeCoeff('products', idx, 1)"
                                                :disabled="isCooldown || victoryModal.show || mol.coeff >= 8"
                                                class="w-7 h-6 rounded bg-slate-800 hover:bg-slate-700 active:bg-emerald-600 text-white font-black text-xs border border-slate-600 disabled:opacity-30 cursor-pointer flex items-center justify-center transition">
                                            +
                                        </button>
                                        <div class="w-7 h-7 my-1 bg-[#fde047] text-[#121316] border border-[#121316] rounded-md font-mono-code font-black text-sm flex items-center justify-center shadow-xs"
                                             x-text="mol.coeff">
                                        </div>
                                        <button type="button" 
                                                @click="changeCoeff('products', idx, -1)"
                                                :disabled="isCooldown || victoryModal.show || mol.coeff <= 1"
                                                class="w-7 h-6 rounded bg-slate-800 hover:bg-slate-700 active:bg-emerald-600 text-white font-black text-xs border border-slate-600 disabled:opacity-30 cursor-pointer flex items-center justify-center transition">
                                            -
                                        </button>
                                    </div>

                                    <!-- Rumus Molekul & Visual Atom -->
                                    <div class="flex flex-col items-center pl-1">
                                        <span class="text-xl sm:text-2xl font-black font-mono-code tracking-wide text-white" x-html="mol.display"></span>
                                        <span class="text-[9px] text-slate-400 mt-0.5" x-text="mol.name"></span>
                                        
                                        <!-- Visualisasi Bola Atom Sederhana -->
                                        <div class="flex items-center gap-1 mt-1">
                                            <template x-for="(atomCount, atomName) in mol.atoms" :key="'atom-dot-p-' + atomName">
                                                <div class="flex items-center gap-0.5">
                                                    <span class="w-3 h-3 rounded-full border border-slate-950 flex items-center justify-center text-[7px] font-black"
                                                          :style="'background:' + getAtomColor(atomName) + '; color:' + (atomName === 'H' ? '#0f172a' : '#ffffff')"
                                                          x-text="atomName"></span>
                                                    <span class="text-[8px] text-slate-400 font-mono-code" x-text="'×' + (atomCount * mol.coeff)"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- TIMBANGAN NERACA KIMIA (CHEMICAL BALANCE BEAM)                   -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="relative z-10 my-3 p-3 bg-slate-900/90 border-2 border-slate-700 rounded-2xl">
            <div class="flex items-center justify-between mb-2 pb-1 border-b border-slate-800">
                <span class="text-xs font-black text-amber-300 font-mono-code flex items-center gap-1">
                    <i class="fa-solid fa-scale-unbalanced text-amber-400"></i> NERACA MASSA ATOM
                </span>
                <span class="text-[10px] font-mono-code"
                      :class="isBalanced ? 'text-emerald-400 font-bold' : 'text-slate-400'">
                    <span x-text="isBalanced ? '✨ NERACA TEPAT SEIMBANG (0°)' : (scaleAngle < 0 ? '◀ Berat di Reaktan (Kiri)' : 'Berat di Produk (Kanan) ▶')"></span>
                </span>
            </div>

            <!-- Visual Neraca Timbangan Berputar Dinamis -->
            <div class="py-4 relative flex flex-col items-center justify-center">
                
                <!-- Jarum & Poros Tengah -->
                <div class="relative w-full max-w-md h-16 flex items-center justify-center">
                    
                    <!-- Lengan Neraca (Beam) yang miring dinamis -->
                    <div class="w-full h-2.5 rounded-full border-2 border-slate-950 transition-transform duration-500 ease-out flex items-center justify-between px-2 relative"
                         :style="'transform: rotate(' + scaleAngle + 'deg); background: ' + (isBalanced ? '#10b981' : '#f59e0b') + '; box-shadow: ' + (isBalanced ? '0 0 15px #10b981' : 'none')">
                        
                        <!-- Piringan Kiri (Reaktan) -->
                        <div class="absolute -left-2 top-2 flex flex-col items-center">
                            <div class="w-0.5 h-6 bg-slate-400"></div>
                            <div class="w-16 h-4 rounded-b-xl border-2 border-slate-950 flex items-center justify-center text-[9px] font-mono-code font-black"
                                 :class="isBalanced ? 'bg-emerald-400 text-slate-950' : 'bg-slate-700 text-white'">
                                <span x-text="totalReactantAtoms + ' Atom'"></span>
                            </div>
                        </div>

                        <!-- Jarum Penunjuk Tengah -->
                        <div class="w-1 h-6 bg-rose-500 rounded-full mx-auto absolute left-1/2 -top-4 -translate-x-1/2"></div>

                        <!-- Piringan Kanan (Produk) -->
                        <div class="absolute -right-2 top-2 flex flex-col items-center">
                            <div class="w-0.5 h-6 bg-slate-400"></div>
                            <div class="w-16 h-4 rounded-b-xl border-2 border-slate-950 flex items-center justify-center text-[9px] font-mono-code font-black"
                                 :class="isBalanced ? 'bg-emerald-400 text-slate-950' : 'bg-slate-700 text-white'">
                                <span x-text="totalProductAtoms + ' Atom'"></span>
                            </div>
                        </div>

                    </div>

                    <!-- Dudukan Neraca (Stand) -->
                    <div class="absolute bottom-0 w-8 h-8 border-l-4 border-r-4 border-b-8 border-slate-700 rounded-t-lg z-0"></div>
                    <div class="absolute bottom-0 w-16 h-2 bg-slate-600 rounded-full"></div>
                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- TABEL KOMPARASI JUMLAH ATOM REAL-TIME                           -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
            <template x-for="(stat, atomSymbol) in atomStats" :key="'stat-' + atomSymbol">
                <div class="p-2.5 rounded-xl border-2 transition-all flex items-center justify-between"
                     :class="stat.left === stat.right ? 'bg-emerald-950/40 border-emerald-500/80' : 'bg-slate-900 border-slate-800'">
                    
                    <!-- Simbol & Nama Unsur -->
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg border-2 border-slate-950 flex items-center justify-center text-xs font-black"
                              :style="'background:' + getAtomColor(atomSymbol) + '; color:' + (atomSymbol === 'H' ? '#0f172a' : '#ffffff')"
                              x-text="atomSymbol"></span>
                        <div>
                            <span class="text-xs font-black font-mono-code block text-white" x-text="getAtomFullName(atomSymbol)"></span>
                            <span class="text-[9.5px] text-slate-400" x-text="'Unsur ' + atomSymbol"></span>
                        </div>
                    </div>

                    <!-- Jumlah Kiri vs Kanan -->
                    <div class="text-right">
                        <div class="text-xs font-mono-code font-black">
                            <span :class="stat.left === stat.right ? 'text-emerald-400' : 'text-cyan-400'" x-text="stat.left"></span>
                            <span class="text-slate-500 mx-0.5">:</span>
                            <span :class="stat.left === stat.right ? 'text-emerald-400' : 'text-amber-400'" x-text="stat.right"></span>
                        </div>
                        <span class="text-[9px] font-mono-code font-black px-1.5 py-0.2 rounded"
                              :class="stat.left === stat.right ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300'"
                              x-text="stat.left === stat.right ? '✓ SETARA' : (stat.left > stat.right ? 'Kurang ' + (stat.left - stat.right) + ' Kanan' : 'Kurang ' + (stat.right - stat.left) + ' Kiri')">
                        </span>
                    </div>

                </div>
            </template>
        </div>

    </div>

    <!-- Footer Tips Teori Kimia -->
    <div class="mt-2 p-2 bg-teal-50/70 border border-teal-200 rounded-xl flex flex-wrap items-center justify-between gap-1.5 text-[10.5px] text-teal-900 font-mono-code">
        <div class="flex items-center gap-1.5">
            <span class="text-xs">💡</span>
            <span class="font-bold">Hukum Kekekalan Massa (Antoine Lavoisier, 1789):</span>
            <span class="bg-white px-1.5 py-0.2 rounded border border-teal-300 font-black text-teal-950">
                Massa zat sebelum reaksi SAMA DENGAN massa zat sesudah reaksi.
            </span>
        </div>
        <div class="text-[10px] text-teal-700 hidden sm:block">
            Dalam reaksi kimia, atom tidak diciptakan atau dimusnahkan, hanya diatur ulang!
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL CHEATSHEET HUKUM LAVOISIER & STOIKIOMETRI                     -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="showRulesModal" class="fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs" style="display: none;">
        <div @click.away="showRulesModal = false" class="bg-white border-2 border-[#121316] rounded-2xl w-full max-w-xl overflow-hidden shadow-[6px_6px_0px_#121316] max-h-[88vh] flex flex-col">
            <div class="p-3 bg-gradient-to-r from-teal-600 via-cyan-700 to-indigo-700 text-white flex items-center justify-between border-b-2 border-[#121316]">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🧪</span>
                    <div>
                        <h3 class="font-black text-sm">Hukum Lavoisier & Rahasia Penyetaraan Reaksi</h3>
                        <p class="text-[10px] text-teal-100">Prinsip dasar stoikiometri kimia modern</p>
                    </div>
                </div>
                <button type="button" @click="showRulesModal = false" class="text-white hover:text-amber-300 font-black text-base px-1.5 cursor-pointer">✕</button>
            </div>
            
            <div class="p-3.5 space-y-2.5 overflow-y-auto font-mono-code text-xs text-slate-800">
                <div class="space-y-2 text-[11px]">
                    <div class="p-2.5 rounded-xl bg-teal-50 border border-teal-200">
                        <span class="font-black text-teal-900 block mb-1">1. Aturan Koefisien vs Indeks Subscript</span>
                        <p class="text-[10.5px] text-slate-700">
                            • <b>Koefisien (Angka Depan):</b> Angka yang boleh kita ubah (misal: <b>2</b>H₂O artinya ada 2 molekul air).<br>
                            • <b>Indeks (Angka Kecil):</b> <b>DILARANG</b> diubah karena mengubah jenis zat (H₂O adalah air, sedangkan H₂O₂ adalah racun hidrogen peroksida!).
                        </p>
                    </div>

                    <div class="p-2.5 rounded-xl bg-cyan-50 border border-cyan-200">
                        <span class="font-black text-cyan-900 block mb-1">2. Strategi Cepat Menyetarakan Reaksi</span>
                        <p class="text-[10.5px] text-slate-700">
                            <b>Langkah 1:</b> Setarakan terlebih dahulu unsur logam/non-logam utama (selain H dan O, seperti C atau Fe).<br>
                            <b>Langkah 2:</b> Setarakan atom Hidrogen (H).<br>
                            <b>Langkah 3:</b> Terakhir, setarakan atom Oksigen (O).
                        </p>
                    </div>

                    <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200">
                        <span class="font-black text-amber-900 block mb-1">3. Contoh Reaksi Air</span>
                        <div class="bg-white p-2 rounded border border-amber-200 text-[10px]">
                            Reaksi belum setara: H₂ + O₂ ➔ H₂O (Atom O kiri 2, kanan cuma 1)<br>
                            Reaksi setara: <b class="text-emerald-700">2 H₂ + 1 O₂ ➔ 2 H₂O</b><br>
                            Total H: 4 di kiri, 4 di kanan. Total O: 2 di kiri, 2 di kanan ✓
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-2.5 bg-slate-50 border-t-2 border-[#121316] flex justify-end">
                <button type="button" @click="showRulesModal = false" class="btn-tactile-yellow px-4 py-1.5 rounded-xl text-xs font-black shadow-[2px_2px_0px_#121316] cursor-pointer">
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
            <div class="w-16 h-16 rounded-full bg-teal-100 border-2 border-teal-500 text-teal-600 flex items-center justify-center mx-auto mb-2 text-2xl shadow-md">
                <i class="fa-solid fa-flask-vial animate-pulse"></i>
            </div>

            <h3 class="text-base font-black text-[#121316]">REAKSI KIMIA BERHASIL DISETARAKAN!</h3>
            <p class="text-xs text-slate-600 mt-0.5">Hukum Kekekalan Massa Lavoisier terpenuhi dengan sempurna!</p>

            <div class="my-3 py-2 px-3 bg-teal-50 rounded-xl border border-teal-300">
                <!-- Bintang -->
                <div class="flex items-center justify-center gap-1 text-amber-500 text-lg mb-1">
                    <template x-for="i in victoryModal.stars" :key="'star-' + i">
                        <i class="fa-solid fa-star"></i>
                    </template>
                </div>
                <!-- Poin STEAM -->
                <div class="text-sm font-black font-mono-code text-teal-950">
                    +<span x-text="victoryModal.points"></span> Poin STEAM Reputasi
                </div>
                <!-- Gelar -->
                <div class="text-[11px] font-bold text-teal-800 mt-1" x-text="victoryModal.rankTitle"></div>
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
<!-- SCRIPT ENGINE PENYEIMBANG REAKSI KIMIA (ALPINE.JS)                      -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<script>
function chemicalBalanceGame() {
    return {
        level: 1, // 1: Air, 2: Metana, 3: Karat
        moves: 0,
        timerSeconds: 0,
        timerInterval: null,
        isCooldown: false,
        cooldownSeconds: 0,
        hasWonToday: false,
        history: [], // Untuk fitur undo
        showRulesModal: false,

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

        // Definisi Reaksi Kimia
        reactions: {
            1: {
                name: 'Sintesis Air (Pembentukan Molekul H₂O)',
                optimalMoves: 2, // H2: 2, H2O: 2
                reactants: [
                    { name: 'Gas Hidrogen', display: 'H<sub>2</sub>', coeff: 1, atoms: { H: 2 } },
                    { name: 'Gas Oksigen', display: 'O<sub>2</sub>', coeff: 1, atoms: { O: 2 } }
                ],
                products: [
                    { name: 'Uap Air', display: 'H<sub>2</sub>O', coeff: 1, atoms: { H: 2, O: 1 } }
                ]
            },
            2: {
                name: 'Pembakaran Sempurna Gas Metana (CH₄)',
                optimalMoves: 2, // O2: 2, H2O: 2
                reactants: [
                    { name: 'Gas Metana', display: 'CH<sub>4</sub>', coeff: 1, atoms: { C: 1, H: 4 } },
                    { name: 'Gas Oksigen', display: 'O<sub>2</sub>', coeff: 1, atoms: { O: 2 } }
                ],
                products: [
                    { name: 'Karbon Dioksida', display: 'CO<sub>2</sub>', coeff: 1, atoms: { C: 1, O: 2 } },
                    { name: 'Uap Air', display: 'H<sub>2</sub>O', coeff: 1, atoms: { H: 2, O: 1 } }
                ]
            },
            3: {
                name: 'Reaksi Oksidasi / Karat Besi (Fe₂O₃)',
                optimalMoves: 4, // Fe: 4, O2: 3, Fe2O3: 2
                reactants: [
                    { name: 'Logam Besi', display: 'Fe', coeff: 1, atoms: { Fe: 1 } },
                    { name: 'Gas Oksigen', display: 'O<sub>2</sub>', coeff: 1, atoms: { O: 2 } }
                ],
                products: [
                    { name: 'Karat Besi (Besi(III) Oksida)', display: 'Fe<sub>2</sub>O<sub>3</sub>', coeff: 1, atoms: { Fe: 2, O: 3 } }
                ]
            }
        },

        get currentReaction() {
            return this.reactions[this.level];
        },

        get optimalMoves() {
            return this.currentReaction.optimalMoves;
        },

        // Perhitungan Total Atom Tiap Unsur di Ruas Kiri dan Kanan
        get atomStats() {
            const stats = {};
            const rx = this.currentReaction;

            // Kumpulkan semua unsur unik
            [...rx.reactants, ...rx.products].forEach(mol => {
                Object.keys(mol.atoms).forEach(atom => {
                    if (!stats[atom]) {
                        stats[atom] = { left: 0, right: 0 };
                    }
                });
            });

            // Hitung atom di ruas kiri
            rx.reactants.forEach(mol => {
                Object.entries(mol.atoms).forEach(([atom, count]) => {
                    stats[atom].left += (count * mol.coeff);
                });
            });

            // Hitung atom di ruas kanan
            rx.products.forEach(mol => {
                Object.entries(mol.atoms).forEach(([atom, count]) => {
                    stats[atom].right += (count * mol.coeff);
                });
            });

            return stats;
        },

        // Cek Apakah Seluruh Unsur Sudah Setara
        get isBalanced() {
            const stats = this.atomStats;
            const atoms = Object.keys(stats);
            if (atoms.length === 0) return false;
            return atoms.every(atom => stats[atom].left === stats[atom].right);
        },

        get totalReactantAtoms() {
            let total = 0;
            this.currentReaction.reactants.forEach(mol => {
                Object.values(mol.atoms).forEach(count => total += (count * mol.coeff));
            });
            return total;
        },

        get totalProductAtoms() {
            let total = 0;
            this.currentReaction.products.forEach(mol => {
                Object.values(mol.atoms).forEach(count => total += (count * mol.coeff));
            });
            return total;
        },

        // Sudut Kemiringan Neraca Timbangan (-12deg s.d. +12deg)
        get scaleAngle() {
            if (this.isBalanced) return 0;
            const diff = this.totalProductAtoms - this.totalReactantAtoms;
            return Math.max(-14, Math.min(14, diff * 3));
        },

        init() {
            this.resetReaction();
            this.fetchStatus();
            this.startTimer();
        },

        changeLevel(newLevel) {
            if (this.isCooldown) return;
            this.level = newLevel;
            this.resetReaction();
            this.timerSeconds = 0;
            this.victoryModal.show = false;
        },

        resetReaction() {
            // Reset koefisien ke 1
            const rx = this.reactions[this.level];
            rx.reactants.forEach(m => m.coeff = 1);
            rx.products.forEach(m => m.coeff = 1);
            this.moves = 0;
            this.history = [];
            this.alertToast.show = false;
        },

        changeCoeff(side, index, delta) {
            if (this.isCooldown || this.victoryModal.show) return;

            const mol = this.currentReaction[side][index];
            const newCoeff = mol.coeff + delta;

            if (newCoeff < 1 || newCoeff > 8) return;

            // Simpan snapshot untuk undo
            this.history.push({
                reactants: this.currentReaction.reactants.map(m => m.coeff),
                products: this.currentReaction.products.map(m => m.coeff)
            });

            mol.coeff = newCoeff;
            this.moves++;

            this.checkVictory();
        },

        undoMove() {
            if (this.history.length === 0 || this.isCooldown || this.victoryModal.show) return;
            const lastState = this.history.pop();
            this.currentReaction.reactants.forEach((m, i) => m.coeff = lastState.reactants[i]);
            this.currentReaction.products.forEach((m, i) => m.coeff = lastState.products[i]);
            this.moves--;
        },

        checkVictory() {
            if (this.isBalanced) {
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
                        game_key: 'chemical_balance',
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
                console.error('Submit Chemical Balance victory error:', e);
            }
        },

        async fetchStatus() {
            try {
                const res = await fetch('/forum/challenge/status?game_key=chemical_balance', {
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
                console.warn('Gagal memuat status Chemical Balance:', e);
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
        },

        getAtomColor(symbol) {
            const colors = {
                'H': '#38bdf8', // Biru muda langit
                'O': '#ef4444', // Merah
                'C': '#475569', // Abu-abu gelap / arang
                'Fe': '#d97706', // Karat besi / amber
                'N': '#818cf8'  // Indigo
            };
            return colors[symbol] || '#64748b';
        },

        getAtomFullName(symbol) {
            const names = {
                'H': 'Hidrogen (H)',
                'O': 'Oksigen (O)',
                'C': 'Karbon (C)',
                'Fe': 'Besi / Ferrum (Fe)',
                'N': 'Nitrogen (N)'
            };
            return names[symbol] || symbol;
        }
    };
}
</script>
