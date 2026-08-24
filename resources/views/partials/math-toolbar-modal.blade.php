{{-- 
    Interactive Math & Science Toolbar & Formula Palette
    Provides rich symbols, KaTeX formulas, Greek alphabet, superscripts/subscripts, fractions, matrices, and live KaTeX preview.
--}}
<div id="math-palette-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-all duration-200" aria-labelledby="math-modal-title" role="dialog" aria-modal="true">
    <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-150">
        
        {{-- Header --}}
        <div class="px-6 py-4 bg-gradient-to-r from-purple-700 via-indigo-700 to-emerald-700 text-white flex items-center justify-between shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-inner">
                    <i class="fas fa-square-root-variable text-lg text-amber-300"></i>
                </div>
                <div>
                    <h3 id="math-modal-title" class="text-base font-black text-white tracking-wide flex items-center gap-2">
                        Palet Simbol & Rumus Matematika / Sains
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-400 text-purple-950 font-bold uppercase">KaTeX Ready</span>
                    </h3>
                    <p class="text-xs text-purple-100 font-medium mt-0.5">
                        Target Input: <span id="math-target-indicator" class="font-bold text-amber-200 underline">Pertanyaan / Opsi</span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="window.closeMathPalette()" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-all focus:outline-none" title="Tutup (Esc)">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Search & Live Preview Bar --}}
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="relative w-full sm:w-80">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="math-search-input" onkeyup="window.filterMathSymbols(this.value)" placeholder="Cari simbol (contoh: akar, integral, sudut, alfa, pangkat, pecahan)..." class="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
            </div>
            
            {{-- KaTeX Preview Strip --}}
            <div class="flex-1 w-full flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 overflow-x-auto min-h-[38px]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex-shrink-0 flex items-center gap-1">
                    <i class="fas fa-eye text-purple-500"></i> Preview:
                </span>
                <div id="math-modal-preview" class="text-sm font-semibold text-slate-800 flex-1 overflow-x-auto">
                    <span class="text-slate-400 text-xs italic">Klik simbol atau rumus di bawah untuk menyisipkan</span>
                </div>
            </div>
        </div>

        {{-- Category Tabs --}}
        <div class="flex overflow-x-auto px-4 pt-3 pb-2 border-b border-slate-200 bg-white gap-2 text-xs font-bold select-none no-scrollbar">
            <button type="button" onclick="window.switchMathTab('popular')" class="math-tab-btn active px-3 py-1.5 rounded-xl bg-purple-100 text-purple-800 border border-purple-300 flex items-center gap-1.5 transition-all whitespace-nowrap" data-tab="popular">
                <i class="fas fa-star text-amber-500"></i> Populer
            </button>
            <button type="button" onclick="window.switchMathTab('powers')" class="math-tab-btn px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-purple-50 hover:text-purple-700 border border-transparent flex items-center gap-1.5 transition-all whitespace-nowrap" data-tab="powers">
                <i class="fas fa-superscript text-indigo-500"></i> Pangkat & Indeks
            </button>
            <button type="button" onclick="window.switchMathTab('fractions')" class="math-tab-btn px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-purple-50 hover:text-purple-700 border border-transparent flex items-center gap-1.5 transition-all whitespace-nowrap" data-tab="fractions">
                <i class="fas fa-divide text-emerald-500"></i> Pecahan & Aritmatika
            </button>
            <button type="button" onclick="window.switchMathTab('geometry')" class="math-tab-btn px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-purple-50 hover:text-purple-700 border border-transparent flex items-center gap-1.5 transition-all whitespace-nowrap" data-tab="geometry">
                <i class="fas fa-shapes text-cyan-500"></i> Geometri & Sudut
            </button>
            <button type="button" onclick="window.switchMathTab('sets')" class="math-tab-btn px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-purple-50 hover:text-purple-700 border border-transparent flex items-center gap-1.5 transition-all whitespace-nowrap" data-tab="sets">
                <i class="fas fa-project-diagram text-rose-500"></i> Himpunan & Logika
            </button>
            <button type="button" onclick="window.switchMathTab('calculus')" class="math-tab-btn px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-purple-50 hover:text-purple-700 border border-transparent flex items-center gap-1.5 transition-all whitespace-nowrap" data-tab="calculus">
                <i class="fas fa-wave-square text-violet-500"></i> Kalkulus & Matriks
            </button>
            <button type="button" onclick="window.switchMathTab('greek')" class="math-tab-btn px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-purple-50 hover:text-purple-700 border border-transparent flex items-center gap-1.5 transition-all whitespace-nowrap" data-tab="greek">
                <i class="fas fa-font text-teal-500"></i> Huruf Yunani
            </button>
            <button type="button" onclick="window.switchMathTab('science')" class="math-tab-btn px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-purple-50 hover:text-purple-700 border border-transparent flex items-center gap-1.5 transition-all whitespace-nowrap" data-tab="science">
                <i class="fas fa-flask text-amber-600"></i> Fisika & Kimia
            </button>
        </div>

        {{-- Symbol Grid Content --}}
        <div class="p-5 overflow-y-auto flex-1 bg-slate-50/50 max-h-[50vh] custom-scrollbar" id="math-symbols-container">
            
            {{-- TAB: Populer --}}
            <div class="math-tab-content space-y-4" id="tab-popular">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-bolt text-amber-500"></i> Simbol & Operator Paling Sering Digunakan
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-popular">
                        {{-- Injected by JS --}}
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-magic text-purple-500"></i> Template Rumus KaTeX Cepat
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5" id="grid-popular-templates">
                        {{-- Injected by JS --}}
                    </div>
                </div>
            </div>

            {{-- TAB: Pangkat & Indeks --}}
            <div class="math-tab-content space-y-4 hidden" id="tab-powers">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-superscript text-indigo-500"></i> Pangkat Atas (Superscript)
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-powers-super"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-subscript text-indigo-500"></i> Indeks Bawah (Subscript)
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-powers-sub"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-code text-purple-500"></i> Format KaTeX Pangkat & Indeks
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5" id="grid-powers-templates"></div>
                </div>
            </div>

            {{-- TAB: Pecahan & Aritmatika --}}
            <div class="math-tab-content space-y-4 hidden" id="tab-fractions">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-divide text-emerald-500"></i> Pecahan Biasa (Unicode)
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-fractions-uni"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-calculator text-emerald-500"></i> Simbol Operasi & Perbandingan
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-fractions-ops"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-layer-group text-purple-500"></i> Pecahan Bertingkat & KaTeX
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5" id="grid-fractions-templates"></div>
                </div>
            </div>

            {{-- TAB: Geometri & Sudut --}}
            <div class="math-tab-content space-y-4 hidden" id="tab-geometry">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-draw-polygon text-cyan-500"></i> Simbol Sudut, Bangun & Relasi
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-geometry-symbols"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-wave-square text-cyan-600"></i> Fungsi Trigonometri (KaTeX)
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5" id="grid-trig-templates"></div>
                </div>
            </div>

            {{-- TAB: Himpunan & Logika --}}
            <div class="math-tab-content space-y-4 hidden" id="tab-sets">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-circle-nodes text-rose-500"></i> Notasi Himpunan & Operasi
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-sets-symbols"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-brain text-rose-500"></i> Logika Matematika & Simbol Bilangan
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-logic-symbols"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-code text-purple-500"></i> Template Notasi Himpunan KaTeX
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5" id="grid-sets-templates"></div>
                </div>
            </div>

            {{-- TAB: Kalkulus & Matriks --}}
            <div class="math-tab-content space-y-4 hidden" id="tab-calculus">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-infinity text-violet-500"></i> Simbol Kalkulus & Diferensial
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-calculus-symbols"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-table-cells text-violet-600"></i> Template Kalkulus, Matriks & Vektor
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5" id="grid-calculus-templates"></div>
                </div>
            </div>

            {{-- TAB: Huruf Yunani --}}
            <div class="math-tab-content space-y-4 hidden" id="tab-greek">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-font text-teal-500"></i> Huruf Yunani Kecil (Lowercase)
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-greek-lower"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-bold text-teal-500"></i> Huruf Yunani Kapital (Uppercase)
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-greek-upper"></div>
                </div>
            </div>

            {{-- TAB: Fisika & Kimia --}}
            <div class="math-tab-content space-y-4 hidden" id="tab-science">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-temperature-high text-amber-600"></i> Satuan & Konstanta Fisika
                    </h4>
                    <div class="flex flex-wrap gap-2" id="grid-science-symbols"></div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-vial text-emerald-600"></i> Panah Reaksi & Senyawa Kimia
                    </h4>
                    <div class="flex flex-wrap gap-2 mb-3" id="grid-chem-arrows"></div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5" id="grid-chem-templates"></div>
                </div>
            </div>

            {{-- Search Results Container --}}
            <div id="math-search-results" class="hidden space-y-3">
                <h4 class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="fas fa-filter text-purple-600"></i> Hasil Pencarian Simbol / Rumus:
                </h4>
                <div class="flex flex-wrap gap-2 bg-white p-4 rounded-2xl border border-slate-200" id="search-grid-symbols"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5" id="search-grid-templates"></div>
            </div>
        </div>

        {{-- Footer Guidance --}}
        <div class="px-6 py-3.5 bg-slate-100 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded-md font-bold text-[10px]">Tips KaTeX</span>
                <span class="text-slate-600">Ketik rumus dengan format <code class="px-1.5 py-0.5 bg-white border border-slate-300 rounded text-purple-700 font-mono font-bold">$rumus$</code> untuk tampilan matematika yang sempurna.</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.closeMathPalette()" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl transition-all">
                    Selesai
                </button>
            </div>
        </div>

    </div>
</div>

<script>
(function() {
    // Global references
    window.lastActiveMathField = null;

    // Track active field when user focuses or clicks any textarea/input
    document.addEventListener('focusin', function(e) {
        if (e.target && (e.target.matches('textarea, input[type="text"], input:not([type])') || e.target.classList.contains('math-support') || e.target.classList.contains('math-support-input'))) {
            window.lastActiveMathField = e.target;
            updateTargetIndicator();
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target && (e.target.matches('textarea, input[type="text"], input:not([type])') || e.target.classList.contains('math-support') || e.target.classList.contains('math-support-input'))) {
            window.lastActiveMathField = e.target;
            updateTargetIndicator();
        }
    });

    function updateTargetIndicator() {
        const ind = document.getElementById('math-target-indicator');
        if (!ind) return;
        if (window.lastActiveMathField) {
            let label = window.lastActiveMathField.getAttribute('placeholder') 
                || window.lastActiveMathField.getAttribute('name') 
                || window.lastActiveMathField.id 
                || 'Field Aktif';
            if (label.length > 25) label = label.substring(0, 22) + '...';
            ind.textContent = label;
        } else {
            ind.textContent = 'Pertanyaan / Opsi';
        }
    }

    // Insert text at cursor
    window.insertAtCursor = function(myField, myValue) {
        if (!myField) {
            myField = window.lastActiveMathField;
        }
        if (!myField) {
            // Find first math-support field on page
            myField = document.querySelector('.math-support, .math-support-input, textarea');
        }
        if (!myField) return;

        myField.focus();

        if (document.selection) {
            var sel = document.selection.createRange();
            sel.text = myValue;
        } else if (myField.selectionStart || myField.selectionStart == '0') {
            var startPos = myField.selectionStart;
            var endPos = myField.selectionEnd;
            var val = myField.value;
            myField.value = val.substring(0, startPos) + myValue + val.substring(endPos, val.length);
            myField.selectionStart = startPos + myValue.length;
            myField.selectionEnd = startPos + myValue.length;
        } else {
            myField.value += myValue;
        }

        // Trigger input event for Alpine.js / live bindings
        myField.dispatchEvent(new Event('input', { bubbles: true }));
        myField.dispatchEvent(new Event('change', { bubbles: true }));

        // Trigger live preview if attached
        if (myField._updateLivePreview) {
            myField._updateLivePreview();
        }

        // Update modal preview
        updateModalPreview(myValue);
    };

    function updateModalPreview(str) {
        const prevEl = document.getElementById('math-modal-preview');
        if (!prevEl) return;
        prevEl.innerHTML = '';
        const span = document.createElement('span');
        span.textContent = str;
        prevEl.appendChild(span);

        if (window.renderMathInElement) {
            window.renderMathInElement(prevEl, {
                delimiters: [
                    {left: '$$', right: '$$', display: true},
                    {left: '$', right: '$', display: false},
                    {left: '\\(', right: '\\)', display: false},
                    {left: '\\[', right: '\\]', display: true}
                ],
                throwOnError: false
            });
        }
    }

    // Modal Control
    window.openMathPalette = function(targetField) {
        if (targetField) {
            window.lastActiveMathField = targetField;
            updateTargetIndicator();
        }
        const modal = document.getElementById('math-palette-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                const searchInp = document.getElementById('math-search-input');
                if (searchInp) searchInp.focus();
            }, 100);
        }
    };

    window.closeMathPalette = function() {
        const modal = document.getElementById('math-palette-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            if (window.lastActiveMathField) {
                window.lastActiveMathField.focus();
            }
        }
    };

    // Close on Escape key or backdrop click
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeMathPalette();
        }
    });

    document.getElementById('math-palette-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            window.closeMathPalette();
        }
    });

    // Tab switcher
    window.switchMathTab = function(tabName) {
        document.querySelectorAll('.math-tab-btn').forEach(btn => {
            if (btn.getAttribute('data-tab') === tabName) {
                btn.className = 'math-tab-btn active px-3 py-1.5 rounded-xl bg-purple-100 text-purple-800 border border-purple-300 flex items-center gap-1.5 transition-all whitespace-nowrap shadow-xs';
            } else {
                btn.className = 'math-tab-btn px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-purple-50 hover:text-purple-700 border border-transparent flex items-center gap-1.5 transition-all whitespace-nowrap';
            }
        });

        document.querySelectorAll('.math-tab-content').forEach(c => c.classList.add('hidden'));
        const targetTab = document.getElementById('tab-' + tabName);
        if (targetTab) targetTab.classList.remove('hidden');

        // Hide search results if switching tab
        const searchResults = document.getElementById('math-search-results');
        if (searchResults) searchResults.classList.add('hidden');
        const searchInp = document.getElementById('math-search-input');
        if (searchInp) searchInp.value = '';
    };

    // DATA DICTIONARY FOR SYMBOLS & TEMPLATES
    const MATH_DATA = {
        popular: {
            symbols: [
                { s: '√', name: 'Akar kuadrat' },
                { s: 'π', name: 'Pi' },
                { s: '±', name: 'Plus minus' },
                { s: '÷', name: 'Bagi' },
                { s: '×', name: 'Kali' },
                { s: '²', name: 'Kuadrat pangkat 2' },
                { s: '³', name: 'Kubik pangkat 3' },
                { s: '⁴', name: 'Pangkat 4' },
                { s: 'ⁿ', name: 'Pangkat n' },
                { s: '½', name: 'Setengah 1/2' },
                { s: '¼', name: 'Seperempat 1/4' },
                { s: '¾', name: 'Tiga perempat 3/4' },
                { s: 'α', name: 'Alpha' },
                { s: 'β', name: 'Beta' },
                { s: 'γ', name: 'Gamma' },
                { s: 'θ', name: 'Theta' },
                { s: 'λ', name: 'Lambda' },
                { s: 'Σ', name: 'Sigma' },
                { s: 'Δ', name: 'Delta' },
                { s: '∞', name: 'Tak hingga infinity' },
                { s: '≠', name: 'Tidak sama dengan' },
                { s: '≤', name: 'Kurang dari sama dengan' },
                { s: '≥', name: 'Lebih dari sama dengan' },
                { s: '≈', name: 'Mendekati kira-kira' },
                { s: '∈', name: 'Elemen himpunan' },
                { s: '∪', name: 'Gabungan union' },
                { s: '∩', name: 'Irisan intersection' },
                { s: '∠', name: 'Sudut angle' },
                { s: '°', name: 'Derajat degree' },
                { s: '∫', name: 'Integral' },
                { s: '‰', name: 'Permil' }
            ],
            templates: [
                { label: 'Pecahan', code: '$\\frac{a}{b}$', desc: 'Pecahan a per b' },
                { label: 'Akar Kuadrat', code: '$\\sqrt{x}$', desc: 'Akar x' },
                { label: 'Akar Derajat n', code: '$\\sqrt[n]{x}$', desc: 'Akar pangkat n dari x' },
                { label: 'Pangkat KaTeX', code: '$x^{n}$', desc: 'x pangkat n' },
                { label: 'Indeks KaTeX', code: '$x_{n}$', desc: 'x indeks n' },
                { label: 'Pangkat & Indeks', code: '$x_{1}^{2}$', desc: 'x1 kuadrat' },
                { label: 'Integral', code: '$\\int_{a}^{b} f(x) \\, dx$', desc: 'Integral tentu' },
                { label: 'Limit', code: '$\\lim_{x \\to 0} f(x)$', desc: 'Limit x menuju 0' }
            ]
        },
        powers: {
            super: [
                { s: '⁰', name: 'Pangkat 0' }, { s: '¹', name: 'Pangkat 1' }, { s: '²', name: 'Pangkat 2' },
                { s: '³', name: 'Pangkat 3' }, { s: '⁴', name: 'Pangkat 4' }, { s: '⁵', name: 'Pangkat 5' },
                { s: '⁶', name: 'Pangkat 6' }, { s: '⁷', name: 'Pangkat 7' }, { s: '⁸', name: 'Pangkat 8' },
                { s: '⁹', name: 'Pangkat 9' }, { s: '⁺', name: 'Pangkat plus' }, { s: '⁻', name: 'Pangkat minus' },
                { s: '⁼', name: 'Pangkat sama dengan' }, { s: '⁽', name: 'Pangkat kurung buka' }, { s: '⁾', name: 'Pangkat kurung tutup' },
                { s: 'ⁿ', name: 'Pangkat n' }, { s: 'ˣ', name: 'Pangkat x' }, { s: 'ʸ', name: 'Pangkat y' }
            ],
            sub: [
                { s: '₀', name: 'Indeks 0' }, { s: '₁', name: 'Indeks 1' }, { s: '₂', name: 'Indeks 2' },
                { s: '₃', name: 'Indeks 3' }, { s: '₄', name: 'Indeks 4' }, { s: '₅', name: 'Indeks 5' },
                { s: '₆', name: 'Indeks 6' }, { s: '₇', name: 'Indeks 7' }, { s: '₈', name: 'Indeks 8' },
                { s: '₉', name: 'Indeks 9' }, { s: '₊', name: 'Indeks plus' }, { s: '₋', name: 'Indeks minus' },
                { s: '₌', name: 'Indeks sama dengan' }, { s: '₍', name: 'Indeks kurung buka' }, { s: '₎', name: 'Indeks kurung tutup' },
                { s: 'ₐ', name: 'Indeks a' }, { s: 'ₑ', name: 'Indeks e' }, { s: 'ₒ', name: 'Indeks o' },
                { s: 'ₓ', name: 'Indeks x' }, { s: 'ᵢ', name: 'Indeks i' }, { s: 'ⱼ', name: 'Indeks j' }
            ],
            templates: [
                { label: 'Pangkat Polinomial', code: '$ax^2 + bx + c = 0$', desc: 'Persamaan kuadrat' },
                { label: 'Pangkat Pecahan', code: '$x^{\\frac{1}{2}}$', desc: 'x pangkat setengah' },
                { label: 'Barisan x_n', code: '$x_1, x_2, \\dots, x_n$', desc: 'Barisan indeks' }
            ]
        },
        fractions: {
            uni: [
                { s: '½', name: '1/2 Setengah' }, { s: '⅓', name: '1/3 Sepertiga' }, { s: '⅔', name: '2/3 Dua pertiga' },
                { s: '¼', name: '1/4 Seperempat' }, { s: '¾', name: '3/4 Tiga perempat' }, { s: '⅕', name: '1/5 Seperlima' },
                { s: '⅖', name: '2/5 Dua perlima' }, { s: '⅗', name: '3/5 Tiga perlima' }, { s: '⅘', name: '4/5 Empat perlima' },
                { s: '⅙', name: '1/6 Seperenam' }, { s: '⅚', name: '5/6 Lima perenam' }, { s: '⅛', name: '1/8 Seperdelapan' },
                { s: '⅜', name: '3/8 Tiga perdelapan' }, { s: '⅝', name: '5/8 Lima perdelapan' }, { s: '⅞', name: '7/8 Tujuh perdelapan' }
            ],
            ops: [
                { s: '+', name: 'Tambah' }, { s: '−', name: 'Kurang' }, { s: '×', name: 'Kali' }, { s: '÷', name: 'Bagi' },
                { s: '±', name: 'Plus minus' }, { s: '∓', name: 'Minus plus' }, { s: '·', name: 'Titik kali dot' },
                { s: '∗', name: 'Bintang asterisk' }, { s: '‰', name: 'Permil' }, { s: '≠', name: 'Tidak sama dengan' },
                { s: '≈', name: 'Kira-kira hampir sama' }, { s: '≡', name: 'Ekuivalen identik' }, { s: '≢', name: 'Tidak ekuivalen' },
                { s: '≤', name: 'Kurang dari sama dengan' }, { s: '≥', name: 'Lebih dari sama dengan' },
                { s: '≪', name: 'Jauh lebih kecil' }, { s: '≫', name: 'Jauh lebih besar' }, { s: '∞', name: 'Tak hingga' },
                { s: '∝', name: 'Sebanding proporsional' }
            ],
            templates: [
                { label: 'Pecahan Bertingkat', code: '$\\frac{\\frac{a}{b}}{\\frac{c}{d}}$', desc: 'Pecahan bersarang' },
                { label: 'Pecahan Campuran', code: '$3\\frac{1}{2}$', desc: 'Tiga setengah' },
                { label: 'Rumus ABC Kuadrat', code: '$x = \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}$', desc: 'Rumus ABC' }
            ]
        },
        geometry: {
            symbols: [
                { s: '∠', name: 'Sudut angle' }, { s: '∡', name: 'Sudut terukur' }, { s: '∟', name: 'Sudut siku-siku' },
                { s: '△', name: 'Segitiga triangle' }, { s: '▭', name: 'Persegi panjang' }, { s: '▱', name: 'Jajar genjang' },
                { s: '◯', name: 'Lingkaran circle' }, { s: '⬡', name: 'Segienam heksagon' }, { s: '⊥', name: 'Tegak lurus perpendicular' },
                { s: '∥', name: 'Sejajar parallel' }, { s: '∦', name: 'Tidak sejajar' }, { s: '≅', name: 'Kongruen' },
                { s: '∼', name: 'Sebangun similar' }, { s: '°', name: 'Derajat degree' }, { s: "'", name: 'Menit busur' }, { s: '"', name: 'Detik busur' }
            ],
            templates: [
                { label: 'sin(x)', code: '$\\sin(x)$', desc: 'Sinus x' },
                { label: 'cos(x)', code: '$\\cos(x)$', desc: 'Cosinus x' },
                { label: 'tan(x)', code: '$\\tan(x)$', desc: 'Tangen x' },
                { label: 'Identitas Trigonometri', code: '$\\sin^2(x) + \\cos^2(x) = 1$', desc: 'Identitas Pythagoras' },
                { label: 'Sudut Segitiga', code: '$\\angle ABC = 90^\\circ$', desc: 'Besar sudut' },
                { label: 'Panjang Ruas Garis', code: '$\\overline{AB} = 10\\text{ cm}$', desc: 'Ruas garis AB' }
            ]
        },
        sets: {
            symbols: [
                { s: '∈', name: 'Anggota elemen in' }, { s: '∉', name: 'Bukan anggota not in' }, { s: '∋', name: 'Memuat contains' },
                { s: '⊂', name: 'Himpunan bagian subset' }, { s: '⊃', name: 'Superset' }, { s: '⊆', name: 'Subset atau sama dengan' },
                { s: '⊇', name: 'Superset atau sama dengan' }, { s: '⊄', name: 'Bukan subset' }, { s: '∪', name: 'Gabungan union' },
                { s: '∩', name: 'Irisan intersection' }, { s: '∅', name: 'Himpunan kosong empty set' }, { s: '∖', name: 'Selisih himpunan' },
                { s: '℘', name: 'Himpunan kuasa power set' }
            ],
            logic: [
                { s: '∀', name: 'Untuk semua for all' }, { s: '∃', name: 'Ada terdapat exists' }, { s: '∄', name: 'Tidak ada' },
                { s: '∧', name: 'Dan and konjungsi' }, { s: '∨', name: 'Atau or disjungsi' }, { s: '¬', name: 'Bukan negasi not' },
                { s: '⇒', name: 'Maka implikasi implies' }, { s: '⇔', name: 'Jika dan hanya jika biimplikasi' },
                { s: '∴', name: 'Oleh karena itu therefore' }, { s: '∵', name: 'Karena because' },
                { s: 'ℕ', name: 'Bilangan Asli Natural' }, { s: 'ℤ', name: 'Bilangan Bulat Integer' },
                { s: 'ℚ', name: 'Bilangan Rasional' }, { s: 'ℝ', name: 'Bilangan Real Nyata' }, { s: 'ℂ', name: 'Bilangan Kompleks' }
            ],
            templates: [
                { label: 'Notasi Pembentuk Himpunan', code: '$\\{x \\mid x \\in \\mathbb{R}, x > 0\\}$', desc: 'Notasi himpunan' },
                { label: 'Operasi Gabungan & Irisan', code: '$A \\cup B, \\quad A \\cap B$', desc: 'Union & Intersection' },
                { label: 'Implikasi Logika', code: '$p \\Rightarrow q \\equiv \\neg p \\lor q$', desc: 'Ekuivalensi logika' }
            ]
        },
        calculus: {
            symbols: [
                { s: '∫', name: 'Integral' }, { s: '∬', name: 'Integral ganda' }, { s: '∭', name: 'Integral lipat tiga' },
                { s: '∮', name: 'Integral lintasan' }, { s: '∂', name: 'Turunan parsial partial' }, { s: '∇', name: 'Nabla gradient del' },
                { s: 'dx', name: 'Diferensial dx' }, { s: 'dy', name: 'Diferensial dy' }, { s: 'dt', name: 'Diferensial dt' }
            ],
            templates: [
                { label: 'Turunan Fungsi', code: '$\\frac{df}{dx} = \\lim_{h \\to 0} \\frac{f(x+h) - f(x)}{h}$', desc: 'Definisi turunan' },
                { label: 'Deret Sigma (Jumlah)', code: '$\\sum_{i=1}^{n} i = \\frac{n(n+1)}{2}$', desc: 'Jumlah deret' },
                { label: 'Deret Perkalian Pi', code: '$\\prod_{i=1}^{n} x_i$', desc: 'Perkalian deret' },
                { label: 'Logaritma Basis b', code: '$\\log_b(a) = c$', desc: 'Bentuk logaritma' },
                { label: 'Logaritma Natural (ln)', code: '$\\ln(x)$', desc: 'Logaritma natural' },
                { label: 'Vektor', code: '$\\vec{v} = a\\hat{i} + b\\hat{j}$', desc: 'Notasi vektor' },
                { label: 'Matriks 2x2', code: '$\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}$', desc: 'Matriks ordo 2x2' },
                { label: 'Determinan 2x2', code: '$\\begin{vmatrix} a & b \\\\ c & d \\end{vmatrix} = ad - bc$', desc: 'Determinan matriks' },
                { label: 'Sistem Persamaan Linier', code: '$\\begin{cases} 2x + y = 7 \\\\ x - y = 2 \\end{cases}$', desc: 'SPLDV' }
            ]
        },
        greek: {
            lower: [
                { s: 'α', name: 'Alpha' }, { s: 'β', name: 'Beta' }, { s: 'γ', name: 'Gamma' }, { s: 'δ', name: 'Delta' },
                { s: 'ε', name: 'Epsilon' }, { s: 'ζ', name: 'Zeta' }, { s: 'η', name: 'Eta' }, { s: 'θ', name: 'Theta' },
                { s: 'ι', name: 'Iota' }, { s: 'κ', name: 'Kappa' }, { s: 'λ', name: 'Lambda' }, { s: 'μ', name: 'Mu' },
                { s: 'ν', name: 'Nu' }, { s: 'ξ', name: 'Xi' }, { s: 'ο', name: 'Omicron' }, { s: 'π', name: 'Pi' },
                { s: 'ρ', name: 'Rho' }, { s: 'σ', name: 'Sigma' }, { s: 'τ', name: 'Tau' }, { s: 'υ', name: 'Upsilon' },
                { s: 'φ', name: 'Phi' }, { s: 'χ', name: 'Chi' }, { s: 'ψ', name: 'Psi' }, { s: 'ω', name: 'Omega' }
            ],
            upper: [
                { s: 'Α', name: 'Alpha kapital' }, { s: 'Β', name: 'Beta kapital' }, { s: 'Γ', name: 'Gamma kapital' },
                { s: 'Δ', name: 'Delta kapital' }, { s: 'Ε', name: 'Epsilon kapital' }, { s: 'Ζ', name: 'Zeta kapital' },
                { s: 'Η', name: 'Eta kapital' }, { s: 'Θ', name: 'Theta kapital' }, { s: 'Ι', name: 'Iota kapital' },
                { s: 'Κ', name: 'Kappa kapital' }, { s: 'Λ', name: 'Lambda kapital' }, { s: 'Μ', name: 'Mu kapital' },
                { s: 'Ν', name: 'Nu kapital' }, { s: 'Ξ', name: 'Xi kapital' }, { s: 'Ο', name: 'Omicron kapital' },
                { s: 'Π', name: 'Pi kapital' }, { s: 'Ρ', name: 'Rho kapital' }, { s: 'Σ', name: 'Sigma kapital' },
                { s: 'Τ', name: 'Tau kapital' }, { s: 'Υ', name: 'Upsilon kapital' }, { s: 'Φ', name: 'Phi kapital' },
                { s: 'Χ', name: 'Chi kapital' }, { s: 'Ψ', name: 'Psi kapital' }, { s: 'Ω', name: 'Omega kapital' }
            ]
        },
        science: {
            symbols: [
                { s: '℃', name: 'Derajat Celcius' }, { s: '℉', name: 'Derajat Fahrenheit' }, { s: 'K', name: 'Kelvin' },
                { s: 'Ω', name: 'Ohm hambatan listrik' }, { s: 'µ', name: 'Mikro' }, { s: 'Å', name: 'Angstrom' },
                { s: 'ħ', name: 'Konstanta Planck tereduksi' }, { s: 'ℏ', name: 'H-bar' }
            ],
            arrows: [
                { s: '→', name: 'Panah reaksi ke kanan' }, { s: '⇌', name: 'Kesetimbangan bolak-balik' },
                { s: '⇄', name: 'Reaksi reversible' }, { s: '↑', name: 'Gas terbentuk' }, { s: '↓', name: 'Endapan terbentuk' },
                { s: '⇒', name: 'Hasil' }, { s: '↔', name: 'Resonansi' }
            ],
            templates: [
                { label: 'Senyawa Air', code: '$H_2O$', desc: 'Air' },
                { label: 'Karbondioksida', code: '$CO_2$', desc: 'Gas CO2' },
                { label: 'Asam Sulfat', code: '$H_2SO_4$', desc: 'Asam sulfat' },
                { label: 'Ion Kalsium', code: '$Ca^{2+}$$', desc: 'Kation Ca 2+' },
                { label: 'Ion Sulfat', code: '$SO_4^{2-}$', desc: 'Anion SO4 2-' },
                { label: 'Reaksi Kimia Pembentukan', code: '$2H_2 + O_2 \\rightarrow 2H_2O$', desc: 'Reaksi pembentukan air' }
            ]
        }
    };

    // Helper to create symbol button
    function createSymbolBtn(item) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'w-10 h-10 bg-white hover:bg-purple-100 hover:text-purple-800 text-slate-800 border border-slate-200 rounded-xl text-base font-bold transition-all shadow-2xs flex items-center justify-center focus:ring-2 focus:ring-purple-400 focus:outline-none select-none';
        btn.textContent = item.s;
        btn.title = item.name || item.s;
        btn.onclick = function(e) {
            e.preventDefault();
            window.insertAtCursor(null, item.s);
        };
        return btn;
    }

    // Helper to create template button
    function createTemplateBtn(t) {
        const card = document.createElement('button');
        card.type = 'button';
        card.className = 'p-3 bg-white hover:bg-purple-50/80 hover:border-purple-300 border border-slate-200 rounded-xl text-left transition-all shadow-2xs focus:ring-2 focus:ring-purple-400 focus:outline-none flex flex-col justify-between group';
        
        const top = document.createElement('div');
        top.className = 'text-xs font-bold text-slate-800 group-hover:text-purple-700 mb-1 flex items-center justify-between w-full';
        top.innerHTML = `<span>${t.label}</span> <i class="fas fa-plus-circle text-slate-300 group-hover:text-purple-500 text-xs"></i>`;
        
        const code = document.createElement('div');
        code.className = 'text-[11px] font-mono text-purple-600 bg-purple-50/50 px-2 py-1 rounded-lg border border-purple-100/50 truncate w-full';
        code.textContent = t.code;

        card.appendChild(top);
        card.appendChild(code);

        card.title = t.desc || t.label;
        card.onclick = function(e) {
            e.preventDefault();
            window.insertAtCursor(null, t.code);
        };
        return card;
    }

    // Populate all grids on load
    function populateModalGrids() {
        // Popular
        const gPop = document.getElementById('grid-popular');
        if (gPop) MATH_DATA.popular.symbols.forEach(s => gPop.appendChild(createSymbolBtn(s)));
        const gPopT = document.getElementById('grid-popular-templates');
        if (gPopT) MATH_DATA.popular.templates.forEach(t => gPopT.appendChild(createTemplateBtn(t)));

        // Powers
        const gPowSup = document.getElementById('grid-powers-super');
        if (gPowSup) MATH_DATA.powers.super.forEach(s => gPowSup.appendChild(createSymbolBtn(s)));
        const gPowSub = document.getElementById('grid-powers-sub');
        if (gPowSub) MATH_DATA.powers.sub.forEach(s => gPowSub.appendChild(createSymbolBtn(s)));
        const gPowT = document.getElementById('grid-powers-templates');
        if (gPowT) MATH_DATA.powers.templates.forEach(t => gPowT.appendChild(createTemplateBtn(t)));

        // Fractions
        const gFracU = document.getElementById('grid-fractions-uni');
        if (gFracU) MATH_DATA.fractions.uni.forEach(s => gFracU.appendChild(createSymbolBtn(s)));
        const gFracO = document.getElementById('grid-fractions-ops');
        if (gFracO) MATH_DATA.fractions.ops.forEach(s => gFracO.appendChild(createSymbolBtn(s)));
        const gFracT = document.getElementById('grid-fractions-templates');
        if (gFracT) MATH_DATA.fractions.templates.forEach(t => gFracT.appendChild(createTemplateBtn(t)));

        // Geometry
        const gGeoS = document.getElementById('grid-geometry-symbols');
        if (gGeoS) MATH_DATA.geometry.symbols.forEach(s => gGeoS.appendChild(createSymbolBtn(s)));
        const gTrigT = document.getElementById('grid-trig-templates');
        if (gTrigT) MATH_DATA.geometry.templates.forEach(t => gTrigT.appendChild(createTemplateBtn(t)));

        // Sets
        const gSetS = document.getElementById('grid-sets-symbols');
        if (gSetS) MATH_DATA.sets.symbols.forEach(s => gSetS.appendChild(createSymbolBtn(s)));
        const gLogS = document.getElementById('grid-logic-symbols');
        if (gLogS) MATH_DATA.sets.logic.forEach(s => gLogS.appendChild(createSymbolBtn(s)));
        const gSetT = document.getElementById('grid-sets-templates');
        if (gSetT) MATH_DATA.sets.templates.forEach(t => gSetT.appendChild(createTemplateBtn(t)));

        // Calculus
        const gCalcS = document.getElementById('grid-calculus-symbols');
        if (gCalcS) MATH_DATA.calculus.symbols.forEach(s => gCalcS.appendChild(createSymbolBtn(s)));
        const gCalcT = document.getElementById('grid-calculus-templates');
        if (gCalcT) MATH_DATA.calculus.templates.forEach(t => gCalcT.appendChild(createTemplateBtn(t)));

        // Greek
        const gGrkL = document.getElementById('grid-greek-lower');
        if (gGrkL) MATH_DATA.greek.lower.forEach(s => gGrkL.appendChild(createSymbolBtn(s)));
        const gGrkU = document.getElementById('grid-greek-upper');
        if (gGrkU) MATH_DATA.greek.upper.forEach(s => gGrkU.appendChild(createSymbolBtn(s)));

        // Science
        const gSciS = document.getElementById('grid-science-symbols');
        if (gSciS) MATH_DATA.science.symbols.forEach(s => gSciS.appendChild(createSymbolBtn(s)));
        const gChmA = document.getElementById('grid-chem-arrows');
        if (gChmA) MATH_DATA.science.arrows.forEach(s => gChmA.appendChild(createSymbolBtn(s)));
        const gChmT = document.getElementById('grid-chem-templates');
        if (gChmT) MATH_DATA.science.templates.forEach(t => gChmT.appendChild(createTemplateBtn(t)));
    }

    // Search & Filter
    window.filterMathSymbols = function(keyword) {
        keyword = (keyword || '').trim().toLowerCase();
        const searchResults = document.getElementById('math-search-results');
        const symGrid = document.getElementById('search-grid-symbols');
        const tmplGrid = document.getElementById('search-grid-templates');

        if (!keyword) {
            if (searchResults) searchResults.classList.add('hidden');
            const activeTabBtn = document.querySelector('.math-tab-btn.active');
            const tabName = activeTabBtn ? activeTabBtn.getAttribute('data-tab') : 'popular';
            const tabContent = document.getElementById('tab-' + tabName);
            if (tabContent) tabContent.classList.remove('hidden');
            return;
        }

        // Hide all tabs
        document.querySelectorAll('.math-tab-content').forEach(c => c.classList.add('hidden'));
        if (searchResults) searchResults.classList.remove('hidden');
        if (symGrid) symGrid.innerHTML = '';
        if (tmplGrid) tmplGrid.innerHTML = '';

        // Collect all symbols and templates
        let matchedSymbols = [];
        let matchedTemplates = [];

        function scanList(arr, isTemplate) {
            if (!arr) return;
            arr.forEach(item => {
                const nameMatch = (item.name || '').toLowerCase().includes(keyword);
                const sMatch = (item.s || '').toLowerCase().includes(keyword);
                const lblMatch = (item.label || '').toLowerCase().includes(keyword);
                const codeMatch = (item.code || '').toLowerCase().includes(keyword);
                const descMatch = (item.desc || '').toLowerCase().includes(keyword);

                if (nameMatch || sMatch || lblMatch || codeMatch || descMatch) {
                    if (isTemplate) {
                        if (!matchedTemplates.some(t => t.code === item.code)) matchedTemplates.push(item);
                    } else {
                        if (!matchedSymbols.some(s => s.s === item.s)) matchedSymbols.push(item);
                    }
                }
            });
        }

        // Scan all categories
        Object.values(MATH_DATA).forEach(cat => {
            if (cat.symbols) scanList(cat.symbols, false);
            if (cat.super) scanList(cat.super, false);
            if (cat.sub) scanList(cat.sub, false);
            if (cat.uni) scanList(cat.uni, false);
            if (cat.ops) scanList(cat.ops, false);
            if (cat.logic) scanList(cat.logic, false);
            if (cat.lower) scanList(cat.lower, false);
            if (cat.upper) scanList(cat.upper, false);
            if (cat.arrows) scanList(cat.arrows, false);
            if (cat.templates) scanList(cat.templates, true);
        });

        if (matchedSymbols.length === 0 && matchedTemplates.length === 0) {
            if (symGrid) symGrid.innerHTML = '<p class="text-xs text-slate-400 italic py-2">Tidak ditemukan simbol yang cocok dengan kata kunci "' + keyword + '".</p>';
        } else {
            if (symGrid) matchedSymbols.forEach(s => symGrid.appendChild(createSymbolBtn(s)));
            if (tmplGrid) matchedTemplates.forEach(t => tmplGrid.appendChild(createTemplateBtn(t)));
        }
    };

    // ═════════════════════════════════════════════════════════════════
    // SMART TOOLBAR GENERATOR FOR TEXTAREAS (.math-support)
    // ═════════════════════════════════════════════════════════════════
    function initMathToolbars() {
        const textareas = document.querySelectorAll('textarea.math-support:not([data-math-initialized])');
        
        textareas.forEach(textarea => {
            textarea.setAttribute('data-math-initialized', 'true');
            if (!textarea.id) {
                textarea.id = 'math_field_' + Math.random().toString(36).substring(2, 9);
            }

            // Create toolbar container
            const toolbar = document.createElement('div');
            toolbar.className = 'flex flex-wrap gap-1.5 p-2 bg-gradient-to-r from-purple-50/90 via-indigo-50/70 to-emerald-50/70 border border-slate-200 rounded-t-2xl items-center select-none shadow-2xs';

            // Category badge
            const badge = document.createElement('span');
            badge.className = 'text-[9px] font-black text-purple-700 bg-purple-100/80 px-2 py-1 rounded-lg uppercase tracking-wider flex items-center gap-1 mr-1 border border-purple-200/60';
            badge.innerHTML = '<i class="fas fa-square-root-variable text-[10px] text-purple-600"></i> Simbol & Rumus:';
            toolbar.appendChild(badge);

            // High frequency quick symbols
            const quickSymbols = ['√', 'π', '±', '÷', '×', '²', '³', '⁴', 'ⁿ', '½', '¼', '¾', 'α', 'β', 'γ', 'θ', 'λ', 'Σ', 'Δ', '∞', '≠', '≤', '≥', '≈', '∈', '∪', '∩', '∠', '°', '∫'];
            
            quickSymbols.forEach(sym => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-2 py-1 bg-white hover:bg-purple-100 hover:text-purple-800 border border-slate-200 rounded-lg text-xs font-bold transition-all shadow-2xs focus:outline-none';
                btn.textContent = sym;
                btn.title = 'Sisipkan ' + sym;
                btn.onclick = function(e) {
                    e.preventDefault();
                    window.insertAtCursor(textarea, sym);
                };
                toolbar.appendChild(btn);
            });

            // Quick KaTeX template button: Fraction
            const btnFrac = document.createElement('button');
            btnFrac.type = 'button';
            btnFrac.className = 'px-2 py-1 bg-white hover:bg-emerald-100 hover:text-emerald-800 border border-emerald-200 text-emerald-700 rounded-lg text-xs font-bold transition-all shadow-2xs flex items-center gap-1 focus:outline-none';
            btnFrac.innerHTML = '<i class="fas fa-divide text-[10px]"></i> Pecahan';
            btnFrac.title = 'Sisipkan Template Pecahan: $\\frac{a}{b}$';
            btnFrac.onclick = function(e) {
                e.preventDefault();
                window.insertAtCursor(textarea, '$\\frac{a}{b}$');
            };
            toolbar.appendChild(btnFrac);

            // Quick KaTeX template button: Root
            const btnRoot = document.createElement('button');
            btnRoot.type = 'button';
            btnRoot.className = 'px-2 py-1 bg-white hover:bg-emerald-100 hover:text-emerald-800 border border-emerald-200 text-emerald-700 rounded-lg text-xs font-bold transition-all shadow-2xs flex items-center gap-1 focus:outline-none';
            btnRoot.innerHTML = '<i class="fas fa-square-root-variable text-[10px]"></i> Akar';
            btnRoot.title = 'Sisipkan Template Akar: $\\sqrt{x}$';
            btnRoot.onclick = function(e) {
                e.preventDefault();
                window.insertAtCursor(textarea, '$\\sqrt{x}$');
            };
            toolbar.appendChild(btnRoot);

            // Standout Button: Full Math Palette Modal
            const btnPalette = document.createElement('button');
            btnPalette.type = 'button';
            btnPalette.className = 'ml-auto px-2.5 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-black transition-all shadow-xs flex items-center gap-1.5 focus:outline-none';
            btnPalette.innerHTML = '<i class="fas fa-palette text-[10px] text-amber-300"></i> <span>Palet Lengkap</span>';
            btnPalette.title = 'Buka Palet Simbol & Rumus Matematika Lengkap';
            btnPalette.onclick = function(e) {
                e.preventDefault();
                window.openMathPalette(textarea);
            };
            toolbar.appendChild(btnPalette);

            // Live Preview Toggle Button
            const btnPreview = document.createElement('button');
            btnPreview.type = 'button';
            btnPreview.className = 'px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-lg text-xs font-bold transition-all shadow-2xs flex items-center gap-1 focus:outline-none';
            btnPreview.innerHTML = '<i class="fas fa-eye text-[10px] text-indigo-500"></i> <span>Live Preview</span>';
            btnPreview.title = 'Tampilkan/Sembunyikan Live KaTeX Preview';
            
            // Preview Container Box
            const previewBox = document.createElement('div');
            previewBox.className = 'mt-1.5 p-3 bg-white border border-dashed border-purple-300 rounded-xl text-sm text-slate-800 shadow-2xs hidden transition-all';
            previewBox.innerHTML = '<div class="text-[10px] font-bold text-purple-600 uppercase tracking-wider mb-1 flex items-center gap-1"><i class="fas fa-eye"></i> Live Formula Preview:</div><div class="preview-content font-medium text-slate-700"></div>';
            
            const prevContent = previewBox.querySelector('.preview-content');

            function updatePreview() {
                if (previewBox.classList.contains('hidden')) return;
                const text = textarea.value.trim();
                if (!text) {
                    prevContent.innerHTML = '<span class="text-slate-400 text-xs italic">Ketik pertanyaan atau rumus di atas untuk melihat preview...</span>';
                    return;
                }
                prevContent.textContent = text;
                if (window.renderMathInElement) {
                    window.renderMathInElement(prevContent, {
                        delimiters: [
                            {left: '$$', right: '$$', display: true},
                            {left: '$', right: '$', display: false},
                            {left: '\\(', right: '\\)', display: false},
                            {left: '\\[', right: '\\]', display: true}
                        ],
                        throwOnError: false
                    });
                }
            }

            textarea._updateLivePreview = updatePreview;
            textarea.addEventListener('input', updatePreview);

            btnPreview.onclick = function(e) {
                e.preventDefault();
                previewBox.classList.toggle('hidden');
                if (!previewBox.classList.contains('hidden')) {
                    btnPreview.classList.add('bg-purple-100', 'text-purple-800', 'border-purple-300');
                    updatePreview();
                } else {
                    btnPreview.classList.remove('bg-purple-100', 'text-purple-800', 'border-purple-300');
                }
            };
            toolbar.appendChild(btnPreview);

            // Insert toolbar before textarea
            textarea.parentNode.insertBefore(toolbar, textarea);
            textarea.classList.add('rounded-t-none');

            // Insert previewBox after textarea
            if (textarea.nextSibling) {
                textarea.parentNode.insertBefore(previewBox, textarea.nextSibling);
            } else {
                textarea.parentNode.appendChild(previewBox);
            }
        });
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        populateModalGrids();
        initMathToolbars();

        // Observe dynamic DOM changes (e.g. Alpine.js modal openings, dynamic question adding)
        const observer = new MutationObserver(function() {
            initMathToolbars();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
</script>
