<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- 🏛️ PembdaCHALLENGE: Menara Hanoi (Tower of Hanoi - Logic Quest)         -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div x-data="hanoiGame()" x-init="init()" class="w-full">
    
    <!-- Banner Cooldown (Jika Nyawa Habis) -->
    <div x-show="isCooldown" class="mb-3 p-3.5 rounded-2xl bg-rose-50 border-2 border-rose-600 text-rose-900 shadow-[3px_3px_0px_#e11d48]">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⏳</span>
            <div class="flex-1">
                <h4 class="font-black text-sm text-rose-950">Sesi Sedang Cooldown (Istirahat Berpikir)</h4>
                <p class="text-xs text-rose-800 mt-0.5">3 Kesempatan Anda telah habis akibat pelanggaran aturan. Luangkan waktu untuk menganalisis strategi pemindahan rekursif.</p>
                <div class="mt-1.5 text-xs font-mono-code font-black text-rose-700">
                    Dapat mencoba kembali dalam: <span class="bg-rose-200 px-2 py-0.5 rounded-md text-rose-950" x-text="formatTime(cooldownSeconds)"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Pelanggaran Aturan Hanoi (Shake Animation & Red Glow) -->
    <div x-show="illegalMoveAlert.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-3 p-2.5 sm:p-3 rounded-xl bg-rose-500 text-white border-2 border-[#121316] shadow-[3px_3px_0px_#121316] flex items-center justify-between gap-2 text-xs font-bold">
        <div class="flex items-center gap-2">
            <span class="text-base">⚠️</span>
            <span x-text="illegalMoveAlert.message"></span>
        </div>
        <button type="button" @click="illegalMoveAlert.show = false" class="text-white hover:text-amber-200 text-sm font-black px-1">✕</button>
    </div>

    <!-- Top Dashboard: Level Selector, Stats, Undo & Reset -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 mb-3 p-2.5 bg-white border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316]">
        
        <!-- Pilihan Tingkat Kesulitan (Jumlah Cakram) -->
        <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap">
            <span class="text-[10px] font-mono-code font-bold text-slate-500 mr-1 hidden sm:inline">CAKRAM:</span>
            <button type="button" 
                    @click="changeDifficulty(3)" 
                    :disabled="isCooldown"
                    class="px-2 sm:px-2.5 py-1 rounded-lg text-xs font-black border-2 border-[#121316] transition-all"
                    :class="diskCount === 3 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                3 (Pemula)
            </button>
            <button type="button" 
                    @click="changeDifficulty(4)" 
                    :disabled="isCooldown"
                    class="px-2 sm:px-2.5 py-1 rounded-lg text-xs font-black border-2 border-[#121316] transition-all"
                    :class="diskCount === 4 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                4 (Standar)
            </button>
            <button type="button" 
                    @click="changeDifficulty(5)" 
                    :disabled="isCooldown"
                    class="px-2 sm:px-2.5 py-1 rounded-lg text-xs font-black border-2 border-[#121316] transition-all"
                    :class="diskCount === 5 ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-none'">
                5 (Master)
            </button>
        </div>

        <!-- Info Langkah, Waktu, & Nyawa -->
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap text-xs font-mono-code font-bold">
            <div class="flex items-center gap-1.5" title="Jumlah langkah yang telah diambil">
                <span class="text-slate-500 text-[11px]">LANGKAH:</span>
                <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="moves"></span>
                <span class="text-[10px] text-slate-500" x-text="'(Opt: ' + optimalMoves + ')'"></span>
            </div>

            <div class="flex items-center gap-1.5">
                <span class="text-slate-500 text-[11px]">WAKTU:</span>
                <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] border border-[#121316] text-[#121316] font-black text-xs" x-text="formatTime(timerSeconds)"></span>
            </div>

            <!-- Sisa Nyawa -->
            <div class="flex items-center gap-0.5 px-2 py-0.5 bg-rose-50 border border-rose-300 rounded-md" title="Sisa Kesempatan">
                <template x-for="i in 3" :key="i">
                    <span class="text-xs transition-transform" :class="i <= lives ? 'scale-100' : 'opacity-20 grayscale scale-75'">
                        ❤️
                    </span>
                </template>
            </div>
        </div>

        <!-- Tombol Aksi: Undo & Reset -->
        <div class="flex items-center gap-1.5">
            <button type="button" 
                    @click="undoMove()" 
                    :disabled="history.length === 0 || isCooldown || victoryModal.show" 
                    class="btn-tactile-white px-2 sm:px-2.5 py-1 rounded-lg text-xs font-black disabled:opacity-40"
                    title="Batalkan 1 langkah terakhir">
                <i class="fa-solid fa-arrow-rotate-left mr-1"></i> Undo
            </button>
            <button type="button" 
                    @click="resetGame()" 
                    :disabled="isCooldown || moves === 0" 
                    class="btn-tactile-white px-2 sm:px-2.5 py-1 rounded-lg text-xs font-black disabled:opacity-40"
                    title="Ulangi susunan tiang dari awal">
                <i class="fa-solid fa-rotate-left mr-1"></i> Ulang
            </button>
            <button type="button" 
                    @click="showRulesModal = true" 
                    class="p-1 sm:px-2 py-1 rounded-lg bg-indigo-50 border border-indigo-300 text-indigo-900 text-xs font-black hover:bg-indigo-100 transition"
                    title="Pelajari Aturan & Teori Menara Hanoi">
                <i class="fa-solid fa-circle-question"></i>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ARENA VISUAL MENARA HANOI: 3 TIANG MARMER & CAKRAM TINGKAT 3D       -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div class="relative w-full rounded-2xl border-2 border-[#121316] bg-gradient-to-b from-amber-50/80 via-slate-50 to-amber-100/60 p-3 sm:p-5 overflow-hidden shadow-[4px_4px_0px_#121316] select-none">
        
        <!-- Hint Status Instruksi -->
        <div class="mb-3 flex items-center justify-between text-[10px] sm:text-xs font-mono-code">
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/90 border border-[#121316] text-[#121316] font-black shadow-xs">
                <span class="w-2 h-2 rounded-full animate-ping" :class="selectedPeg === null ? 'bg-indigo-500' : 'bg-amber-500'"></span>
                <span x-show="selectedPeg === null">Sentuh Tiang untuk <span class="text-indigo-700">MENGANGKAT CAKRAM</span></span>
                <span x-show="selectedPeg !== null">Pilih Tiang Tujuan untuk <span class="text-amber-800">MELETAKKAN CAKRAM</span></span>
            </div>
            <div class="text-slate-600 font-bold hidden sm:block">
                Tujuan: Pindahkan seluruh tumpukan ke <span class="text-emerald-700 font-black">TIANG C 🏆</span>
            </div>
        </div>

        <!-- PANGGUNG 3 TIANG (GRID 3 KOLOM) -->
        <div class="grid grid-cols-3 gap-2 sm:gap-4 items-end relative min-h-[260px] sm:min-h-[300px] pt-10 pb-4">
            
            <!-- LOOP 3 TIANG (0: Tiang A, 1: Tiang B, 2: Tiang C) -->
            <template x-for="(tower, pegIndex) in towers" :key="'peg-' + pegIndex">
                <div class="flex flex-col items-center justify-end relative h-full group cursor-pointer"
                     @click="handlePegClick(pegIndex)">
                    
                    <!-- Header Tiang & Badge -->
                    <div class="absolute -top-7 sm:-top-8 flex flex-col items-center">
                        <div class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-mono-code font-black border border-[#121316] shadow-2xs transition-all"
                             :class="{
                                 'bg-indigo-100 text-indigo-900': pegIndex === 0 && selectedPeg !== pegIndex,
                                 'bg-slate-100 text-slate-800': pegIndex === 1 && selectedPeg !== pegIndex,
                                 'bg-emerald-100 text-emerald-900': pegIndex === 2 && selectedPeg !== pegIndex,
                                 'bg-yellow-300 text-[#121316] ring-2 ring-[#121316] scale-105': selectedPeg === pegIndex
                             }">
                            <span x-show="pegIndex === 0">🚩 TIANG A (ASAL)</span>
                            <span x-show="pegIndex === 1">🔄 TIANG B (TRANSIT)</span>
                            <span x-show="pegIndex === 2">🏆 TIANG C (TUJUAN)</span>
                        </div>
                    </div>

                    <!-- AREA TIANG VERTIKAL & TUMPUKAN CAKRAM -->
                    <div class="relative w-full flex flex-col items-center justify-end min-h-[200px] sm:min-h-[240px] px-1 sm:px-2">
                        
                        <!-- Batang Tiang Silinder Vertikal Kayu Jati / Emas -->
                        <div class="absolute bottom-0 w-3 sm:w-4 top-2 rounded-t-full border-2 border-[#121316] transition-colors"
                             :class="selectedPeg === pegIndex ? 'bg-amber-400' : 'bg-gradient-to-b from-amber-600 via-amber-700 to-amber-900 shadow-inner'">
                            <!-- Puncak Mahkota Tiang -->
                            <div class="w-full h-3 rounded-t-full bg-yellow-200/80 border-b border-amber-950"></div>
                        </div>

                        <!-- TUMPUKAN CAKRAM-CAKRAM (DARI BAWAH KE ATAS) -->
                        <div class="w-full flex flex-col items-center justify-end z-10 space-y-1 sm:space-y-1.5 pb-0.5">
                            <template x-for="(disk, dIndex) in tower" :key="'disk-' + pegIndex + '-' + disk">
                                <div class="transition-all duration-300 ease-out flex items-center justify-center border-2 border-[#121316] rounded-xl shadow-[2px_2px_0px_#121316] text-[10px] sm:text-xs font-mono-code font-black cursor-pointer select-none relative"
                                     :class="getDiskClass(disk, pegIndex, dIndex)"
                                     :style="'width: ' + getDiskWidth(disk) + '; height: ' + getDiskHeight() + ';'">
                                    
                                    <!-- Angka Ukuran Cakram -->
                                    <span class="drop-shadow-xs" x-text="disk"></span>

                                    <!-- Indikator Cakram Terangkat (Melayang di Udara) -->
                                    <template x-if="selectedPeg === pegIndex && dIndex === tower.length - 1">
                                        <div class="absolute -top-6 px-1.5 py-0.2 rounded bg-[#121316] text-[#fde047] text-[7.5px] font-mono-code font-black tracking-wider shadow-md whitespace-nowrap animate-pulse">
                                            ▲ TERANGKAT
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                    </div>

                    <!-- Alas Piringan Kayu / Dudukan Tiang Bawah -->
                    <div class="w-full h-4 sm:h-5 rounded-lg border-2 border-[#121316] bg-gradient-to-r from-amber-800 via-amber-700 to-amber-900 shadow-md relative z-20 flex items-center justify-center">
                        <div class="w-11/12 h-1 bg-amber-950/60 rounded-full"></div>
                    </div>

                    <!-- Tombol Sentuh Tiang (Interactive Button Bawah) -->
                    <div class="mt-2 w-full">
                        <button type="button" 
                                class="w-full py-1.5 px-1 rounded-xl text-[10px] sm:text-xs font-black border-2 border-[#121316] transition-all flex items-center justify-center gap-1"
                                :class="{
                                    'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316] ring-2 ring-yellow-400': selectedPeg === pegIndex,
                                    'bg-white text-slate-800 hover:bg-[#faf8f5] shadow-[2px_2px_0px_#121316]': selectedPeg === null && tower.length > 0,
                                    'bg-emerald-100 text-emerald-950 hover:bg-emerald-200 border-emerald-900 shadow-[2px_2px_0px_#121316]': selectedPeg !== null && selectedPeg !== pegIndex,
                                    'opacity-40 bg-slate-100 text-slate-400 shadow-none border-slate-300 cursor-not-allowed': selectedPeg === null && tower.length === 0
                                }">
                            <span x-show="selectedPeg === pegIndex">✕ Batal</span>
                            <span x-show="selectedPeg === null && tower.length > 0">▲ Angkat</span>
                            <span x-show="selectedPeg === null && tower.length === 0">Kosong</span>
                            <span x-show="selectedPeg !== null && selectedPeg !== pegIndex">↵ Letakkan</span>
                        </button>
                    </div>

                </div>
            </template>

        </div>

        <!-- Papan Landasan Kayu Panggung Tebal -->
        <div class="w-full h-3 sm:h-4 bg-gradient-to-r from-amber-900 via-amber-800 to-amber-950 rounded-b-xl border-2 border-t-0 border-[#121316] shadow-inner flex items-center justify-around">
            <div class="w-1/4 h-1 bg-amber-950/80 rounded-full"></div>
            <div class="w-1/4 h-1 bg-amber-950/80 rounded-full"></div>
            <div class="w-1/4 h-1 bg-amber-950/80 rounded-full"></div>
        </div>

    </div>

    <!-- Footer Bar Tips STEAM & Rumus Matematika Hanoi -->
    <div class="mt-3 p-2.5 bg-indigo-50/60 border border-indigo-200 rounded-xl flex flex-wrap items-center justify-between gap-2 text-xs text-indigo-900 font-mono-code">
        <div class="flex items-center gap-2">
            <span class="text-sm">📐</span>
            <span class="font-bold">Hukum Eksponensial Hanoi:</span>
            <span class="bg-white px-2 py-0.5 rounded border border-indigo-300 font-black text-indigo-950">
                Langkah Minimum = 2<sup><span x-text="diskCount"></span></sup> - 1 = <span x-text="optimalMoves"></span> Langkah
            </span>
        </div>
        <div class="text-[11px] text-indigo-700">
            Karakteristik: Algoritma Rekursif (Divide & Conquer)
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL KEMENANGAN MENARA HANOI                                       -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="victoryModal.show" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-[#121316]/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white border-2 border-[#121316] rounded-3xl p-5 sm:p-6 max-w-md w-full shadow-[6px_6px_0px_#121316] text-center" @click.stop>
            <div class="w-16 h-16 rounded-2xl bg-[#fde047] border-2 border-[#121316] shadow-[3px_3px_0px_#121316] flex items-center justify-center text-3xl mx-auto mb-3">
                🏛️
            </div>

            <div class="flex justify-center gap-1 mb-2">
                <template x-for="i in 3" :key="i">
                    <span class="text-2xl" :class="i <= victoryModal.stars ? 'text-amber-400' : 'text-slate-300'">★</span>
                </template>
            </div>

            <h3 class="font-black text-xl text-[#121316] mb-1">MENARA BERHASIL DIPINDAHKAN!</h3>
            <p class="text-xs font-mono-code font-bold text-indigo-600 mb-3" x-text="victoryModal.rankTitle"></p>

            <div class="p-3 bg-amber-50 rounded-2xl border-2 border-amber-300 mb-4 text-xs font-bold text-amber-950 space-y-1">
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
                <button type="button" @click="resetGame(); victoryModal.show = false" class="btn-tactile-white flex-1 py-2 rounded-xl text-xs font-black">
                    Main Lagi
                </button>
                <button type="button" @click="changeDifficulty(diskCount < 5 ? diskCount + 1 : 3); victoryModal.show = false" class="btn-tactile-yellow flex-1 py-2 rounded-xl text-xs font-black">
                    Level Berikutnya ►
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL ATURAN & TEORI REKURSI MENARA HANOI                           -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div x-show="showRulesModal" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-[#121316]/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white border-2 border-[#121316] rounded-3xl p-5 sm:p-6 max-w-lg w-full shadow-[6px_6px_0px_#121316]" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b-2 border-[#121316] mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🏛️</span>
                    <h3 class="font-black text-sm sm:text-base text-[#121316]">Hukum & Logika Menara Hanoi</h3>
                </div>
                <button type="button" @click="showRulesModal = false" class="w-7 h-7 rounded-lg border-2 border-[#121316] bg-slate-100 hover:bg-slate-200 text-xs font-black flex items-center justify-center">
                    ✕
                </button>
            </div>

            <div class="space-y-3 text-xs text-slate-700 font-sans leading-relaxed max-h-[60vh] overflow-y-auto pr-1">
                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200">
                    <h4 class="font-black text-amber-950 mb-1">🎯 Tujuan Utama:</h4>
                    <p>Pindahkan seluruh tumpukan cakram dari <strong>Tiang A (Asal)</strong> ke <strong>Tiang C (Tujuan)</strong> menggunakan bantuan <strong>Tiang B (Transit)</strong>.</p>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                    <h4 class="font-black text-slate-900 mb-1.5">⚖️ 3 Aturan Wajib (Hukum Hanoi):</h4>
                    <ul class="list-disc pl-4 space-y-1 text-slate-700">
                        <li><strong>Hanya 1 Cakram:</strong> Anda hanya dapat memindahkan satu cakram teratas dalam satu waktu.</li>
                        <li><strong>Cakram Lebih Kecil di Atas:</strong> Cakram yang lebih besar <strong>DILARANG KERAS</strong> ditaruh di atas cakram yang lebih kecil.</li>
                        <li><strong>Penalti Pelanggaran:</strong> Melanggar aturan akan mengurangi 1 kesempatan ❤️ (3x salah = Cooldown 15 Menit).</li>
                    </ul>
                </div>

                <div class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-950">
                    <h4 class="font-black text-indigo-900 mb-1">💡 Tips Logika Pemrograman & Sains:</h4>
                    <p class="text-[11px]">
                        Menara Hanoi adalah representasi klasik dari <strong>Algoritma Rekursif</strong> dalam ilmu komputer. 
                        Untuk memindahkan $n$ cakram ke Tiang C:
                        <br>1. Pindahkan $n-1$ cakram ke Tiang B.
                        <br>2. Pindahkan cakram terbesar ke Tiang C.
                        <br>3. Pindahkan $n-1$ cakram dari Tiang B ke Tiang C.
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 flex justify-end">
                <button type="button" @click="showRulesModal = false" class="btn-tactile-yellow px-4 py-2 rounded-xl text-xs font-black">
                    Saya Mengerti, Mulai Main!
                </button>
            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- SCRIPT ALPINE.JS ENGINE MENARA HANOI (LOGIKA REKURSI & INTERAKSI)     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<script>
function hanoiGame() {
    return {
        diskCount: 3,
        towers: [[], [], []],
        selectedPeg: null,
        moves: 0,
        lives: 3,
        timerSeconds: 0,
        timerInterval: null,
        isCooldown: false,
        cooldownSeconds: 0,
        hasWonToday: false,
        history: [],
        
        illegalMoveAlert: {
            show: false,
            message: '',
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
            return Math.pow(2, this.diskCount) - 1;
        },

        init() {
            this.resetGame();
            this.fetchStatus();
            this.startTimer();
        },

        resetGame() {
            const initial = [];
            for (let i = this.diskCount; i >= 1; i--) {
                initial.push(i);
            }
            this.towers = [initial, [], []];
            this.selectedPeg = null;
            this.moves = 0;
            this.history = [];
            this.illegalMoveAlert.show = false;
        },

        changeDifficulty(count) {
            if (this.isCooldown) return;
            this.diskCount = count;
            this.resetGame();
            this.lives = 3;
            this.timerSeconds = 0;
            this.victoryModal.show = false;
        },

        handlePegClick(pegIndex) {
            if (this.isCooldown || this.victoryModal.show) return;

            // Jika belum ada tiang yang dipilih (mengangkat cakram)
            if (this.selectedPeg === null) {
                if (this.towers[pegIndex].length === 0) {
                    return;
                }
                this.selectedPeg = pegIndex;
                this.illegalMoveAlert.show = false;
            } 
            // Jika mengklik tiang yang sama (membatalkan angkatan)
            else if (this.selectedPeg === pegIndex) {
                this.selectedPeg = null;
            } 
            // Jika memindahkan ke tiang lain
            else {
                this.moveDisk(this.selectedPeg, pegIndex);
            }
        },

        moveDisk(fromPeg, toPeg) {
            const sourceTower = this.towers[fromPeg];
            const targetTower = this.towers[toPeg];

            if (sourceTower.length === 0) {
                this.selectedPeg = null;
                return;
            }

            const diskToMove = sourceTower[sourceTower.length - 1];
            const topOfTarget = targetTower.length > 0 ? targetTower[targetTower.length - 1] : null;

            // Validasi aturan Hanoi: Cakram lebih besar tidak boleh di atas cakram lebih kecil
            if (topOfTarget !== null && diskToMove > topOfTarget) {
                this.triggerIllegalMove(diskToMove, topOfTarget);
                return;
            }

            // Langkah Sah!
            sourceTower.pop();
            targetTower.push(diskToMove);
            this.moves++;
            this.history.push({ from: fromPeg, to: toPeg, disk: diskToMove });
            this.selectedPeg = null;
            this.illegalMoveAlert.show = false;

            // Cek kemenangan: seluruh cakram ada di Tiang C (index 2)
            if (this.towers[2].length === this.diskCount) {
                this.handleVictory();
            }
        },

        undoMove() {
            if (this.history.length === 0 || this.isCooldown || this.victoryModal.show) return;
            const lastMove = this.history.pop();
            this.towers[lastMove.to].pop();
            this.towers[lastMove.from].push(lastMove.disk);
            this.moves--;
            this.selectedPeg = null;
            this.illegalMoveAlert.show = false;
        },

        triggerIllegalMove(disk, targetDisk) {
            this.lives--;
            this.illegalMoveAlert.show = true;
            this.illegalMoveAlert.message = `Hukum Hanoi Dilanggar! Cakram ${disk} lebih besar daripada Cakram ${targetDisk}. Cakram besar tidak boleh menindih cakram yang lebih kecil! Sisa Kesempatan: ${this.lives} ❤️`;
            
            if (this.illegalMoveAlert.timeout) clearTimeout(this.illegalMoveAlert.timeout);
            this.illegalMoveAlert.timeout = setTimeout(() => {
                this.illegalMoveAlert.show = false;
            }, 4500);

            if (this.lives <= 0) {
                this.triggerGameOver();
            }
        },

        async triggerGameOver() {
            this.illegalMoveAlert.show = false;
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
                        game_key: 'tower_of_hanoi',
                        moves: this.moves,
                        seconds_taken: this.timerSeconds
                    })
                });
                const data = await res.json();
                this.isCooldown = true;
                this.cooldownSeconds = data.cooldown_seconds || 900;
                this.startCooldownTimer();
                this.resetGame();
            } catch (e) {
                console.error(e);
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
                        game_key: 'tower_of_hanoi',
                        moves: this.moves,
                        lives_left: this.lives,
                        seconds_taken: this.timerSeconds,
                        disk_count: this.diskCount
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
                console.error('Submit Hanoi victory error:', e);
            }
        },

        async fetchStatus() {
            try {
                const res = await fetch('/forum/challenge/status?game_key=tower_of_hanoi', {
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
                console.warn('Gagal memuat status Hanoi:', e);
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
                    this.lives = 3;
                    clearInterval(timer);
                }
            }, 1000);
        },

        formatTime(sec) {
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        },

        getDiskWidth(disk) {
            // Lebar proporsional bertingkat
            const widths = {
                1: '32%',
                2: '46%',
                3: '60%',
                4: '76%',
                5: '92%',
                6: '98%'
            };
            return widths[disk] || '40%';
        },

        getDiskHeight() {
            return this.diskCount >= 5 ? '26px' : '32px';
        },

        getDiskClass(disk, pegIndex, dIndex) {
            const isFloating = this.selectedPeg === pegIndex && dIndex === this.towers[pegIndex].length - 1;
            
            // Palet Warna Spektrum STEAM
            const colorGradients = {
                1: 'bg-gradient-to-r from-yellow-300 via-amber-300 to-yellow-400 text-amber-950',
                2: 'bg-gradient-to-r from-orange-400 via-amber-400 to-orange-500 text-orange-950',
                3: 'bg-gradient-to-r from-emerald-400 via-teal-400 to-emerald-500 text-emerald-950',
                4: 'bg-gradient-to-r from-sky-400 via-blue-400 to-indigo-500 text-blue-950',
                5: 'bg-gradient-to-r from-purple-400 via-fuchsia-400 to-purple-600 text-white',
                6: 'bg-gradient-to-r from-rose-500 via-pink-500 to-red-600 text-white'
            };

            const baseColor = colorGradients[disk] || 'bg-slate-300 text-slate-800';
            
            if (isFloating) {
                return `${baseColor} ring-4 ring-[#fde047] shadow-xl -translate-y-8 z-30 scale-105 animate-pulse`;
            }
            
            return `${baseColor} hover:brightness-105 active:scale-95`;
        }
    };
}
</script>
