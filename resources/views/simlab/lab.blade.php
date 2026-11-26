<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $lab->title }} - PembdaHUB Simulator Lab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0b0f19; color: #f1f5f9; }
        .font-mono-lab { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
    </style>
</head>
@php
    $isInstructor = auth()->check() && (auth()->user()->teacher || auth()->user()->isAdmin());
    $proEngineMap = [
        'gasoline_engine'    => asset('simlab-engines/mesin-4tak-2tak.html'),
        'beam_load'          => asset('simlab-engines/teknik-gambar-bangunan.html'),
        'subnetting'         => asset('simlab-engines/tkj-jaringan.html'),
        'physics_motion'     => asset('simlab-engines/fisika-gerak-gaya.html'),
        'biology_cell'       => asset('simlab-engines/biologi-pembelahan-sel.html'),
        'math_statistics'    => asset('simlab-engines/matematika-statistika.html'),
        'sociology_dynamics' => asset('simlab-engines/sosiologi-dinamika-sosial.html'),
        'language_syntax'    => asset('simlab-engines/bahasa-struktur-kalimat.html'),
    ];
    $proEngineUrl = $proEngineMap[$lab->engine_type] ?? null;
@endphp
<body class="min-h-screen flex flex-col" x-data="virtualLabRunner()">

    <!-- Top Toolbar -->
    <header class="border-b border-slate-800 bg-slate-900/95 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('simlab.index') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-bold transition shrink-0">
                    <i class="fas fa-arrow-left mr-1"></i> Katalog Lab
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-bold uppercase">{{ $lab->category }}</span>
                        @if($isInstructor)
                        <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[11px] font-bold">Mode Instruktur</span>
                        @else
                        <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[11px] font-bold">Mode Praktikan</span>
                        @endif
                        <span class="text-xs text-slate-400 font-semibold truncate hidden sm:inline">{{ $lab->subject_name }}</span>
                    </div>
                    <h1 class="text-base sm:text-lg font-extrabold text-white truncate">{{ $lab->title }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="showSop = !showSop"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition">
                    <i class="fas" :class="showSop ? 'fa-compress-alt' : 'fa-clipboard-list text-amber-400'"></i>
                    <span class="hidden md:inline" x-text="showSop ? 'Luaskan Kanvas' : 'Tampilkan SOP Lab'"></span>
                </button>
                @if($isInstructor)
                <button type="button" @click="copyLabUrl()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition">
                    <i class="fas fa-link"></i>
                    <span class="hidden sm:inline" x-text="copied ? 'Tersalin!' : 'Salin Link LMS'"></span>
                </button>
                @endif
                @if($lab->course && auth()->check() && auth()->user()->student)
                <a href="{{ route('siswa.lms.show', $lab->course->id) }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition">
                    <i class="fas fa-book-open"></i> Course LMS
                </a>
                @endif
                @if(auth()->check() && auth()->user()->student)
                <a href="{{ route('siswa.ai.index', ['mode' => 'tutor', 'prompt' => 'Saya sedang melakukan praktikum "' . $lab->title . '" (' . $lab->subject_name . '). Tolong jelaskan konsep dan variabel utamanya.']) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs sm:text-sm font-bold transition">
                    <i class="fas fa-robot text-amber-300"></i> <span class="hidden sm:inline">Tanya AI</span>
                </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Lab Workspace -->
    <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-5 w-full flex-grow grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- LEFT: SOP PRAKTIKUM & CATATAN LAB -->
        <aside x-show="showSop" x-transition class="lg:col-span-3 space-y-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold uppercase tracking-wider text-amber-400 flex items-center gap-2">
                        <i class="fas fa-clipboard-list"></i> SOP &amp; Petunjuk Lab
                    </h2>
                    <span class="text-xs font-mono-lab font-bold text-emerald-400" x-text="checkedSteps.length + '/' + totalSteps + ' Selesai'"></span>
                </div>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">{{ $lab->description }}</p>

                <div class="space-y-2 pt-2 border-t border-slate-800">
                    @foreach(($lab->sop_steps ?? []) as $idx => $step)
                    <label class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-950/80 border border-slate-800 hover:border-emerald-500/40 cursor-pointer transition">
                        <input type="checkbox" value="{{ $idx }}" x-model="checkedSteps" class="mt-1 rounded text-emerald-500 focus:ring-emerald-500">
                        <span class="text-xs text-slate-200 leading-snug">
                            <strong class="text-emerald-400">Langkah {{ $idx + 1 }}:</strong> {{ $step }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 space-y-2">
                <label class="text-xs font-extrabold uppercase tracking-wider text-cyan-400 block">
                    <i class="fas fa-pen-to-square mr-1"></i> {{ $isInstructor ? 'Catatan Instruktur / Bahan Ajar' : 'Catatan Pengamatan Siswa' }}
                </label>
                <textarea rows="4" x-model="labNotes" placeholder="Catat hasil pengamatan variabel & kesimpulan eksperimen di sini..."
                          class="w-full rounded-xl bg-slate-950 border border-slate-700 p-3 text-xs sm:text-sm text-slate-200 placeholder-slate-500 outline-none focus:border-cyan-400"></textarea>
            </div>
        </aside>

        <!-- RIGHT: INTERACTIVE ANIMATED SIMULATOR -->
        <section :class="showSop ? 'lg:col-span-9' : 'lg:col-span-12'" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 space-y-4">

            @if($proEngineUrl)
                <!-- PRO INTERACTIVE ENGINE (FROM D:\AI Implementasi) -->
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-sm font-bold text-white">Simulasi Interaktif Penuh &amp; Telemetri Real-Time</span>
                    </div>
                    <a href="{{ $proEngineUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-300 text-xs font-bold transition">
                        <i class="fas fa-up-right-from-square"></i> Buka Layar Penuh
                    </a>
                </div>
                <iframe src="{{ $proEngineUrl }}" class="w-full rounded-xl border border-slate-800 bg-slate-950" style="height: 780px;"></iframe>

            @elseif($lab->engine_type === 'gasoline_engine')
                <!-- 1. SIMULATOR MESIN BENSIN 4-TAK -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-white">Animasi Silinder Mesin Bensin 4-Tak</h3>
                        <p class="text-sm text-slate-400">Atur RPM dan Rasio Udara-BBM (AFR) untuk mengamati siklus kerja dan tenaga mesin</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold"
                          :class="engineTemp > 105 ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40'"
                          x-text="'Suhu Mesin: ' + engineTemp.toFixed(0) + '°C'"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                    <div class="md:col-span-7 bg-slate-950 rounded-2xl border border-slate-800 p-3 flex flex-col items-center">
                        <canvas id="engineCanvas" width="420" height="320" class="w-full max-w-[420px] rounded-xl"></canvas>
                        <div class="mt-2 px-4 py-1.5 rounded-full bg-slate-900 border border-slate-700 text-sm font-extrabold" :style="'color:' + strokeColor" x-text="'Siklus Aktif: ' + currentStrokeName"></div>
                    </div>
                    <div class="md:col-span-5 space-y-4">
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1">
                                <span>Putaran Mesin (RPM)</span>
                                <span class="font-mono-lab text-amber-400" x-text="rpm + ' RPM'"></span>
                            </div>
                            <input type="range" min="600" max="6000" step="100" x-model.number="rpm" class="w-full accent-amber-400 cursor-pointer">
                        </div>
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1">
                                <span>Rasio Udara : BBM (AFR)</span>
                                <span class="font-mono-lab text-cyan-400" x-text="afr.toFixed(1) + ' : 1'"></span>
                            </div>
                            <input type="range" min="10" max="18" step="0.1" x-model.number="afr" class="w-full accent-cyan-400 cursor-pointer">
                            <p class="text-xs text-slate-400 mt-1">Ideal (Stoikiometri): 14.7 : 1</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                                <div class="text-xs text-slate-400">Estimasi Tenaga</div>
                                <div class="text-xl font-extrabold font-mono-lab text-emerald-400" x-text="engineHp.toFixed(1) + ' HP'"></div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                                <div class="text-xs text-slate-400">Pembakaran</div>
                                <div class="text-sm font-extrabold mt-1" :class="Math.abs(afr - 14.7) < 1.2 ? 'text-emerald-400' : 'text-amber-400'"
                                     x-text="afr < 13.5 ? 'Boros (Rich)' : (afr > 15.8 ? 'Kering (Lean)' : 'Optimal')"></div>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($lab->engine_type === 'chemical_reaction')
                <!-- 2. SIMULATOR PERSAMAAN REAKSI KIMIA -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-white">Simulasi Tumbukan Molekul &amp; Persamaan Reaksi</h3>
                        <p class="text-sm text-slate-400">Pilih reaksi, atur suhu &amp; katalis untuk melihat laju terbentuknya molekul produk</p>
                    </div>
                    <select x-model="chemEq" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-sm font-bold text-cyan-300 outline-none">
                        <option value="water">2H₂ + O₂ → 2H₂O (Pembentukan Air)</option>
                        <option value="ammonia">N₂ + 3H₂ ⇌ 2NH₃ (Sintesis Amonia)</option>
                        <option value="methane">CH₄ + 2O₂ → CO₂ + 2H₂O (Pembakaran Metana)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                    <div class="md:col-span-7 bg-slate-950 rounded-2xl border border-slate-800 p-3">
                        <canvas id="chemCanvas" width="440" height="300" class="w-full rounded-xl"></canvas>
                        <div class="flex justify-center gap-4 text-xs font-bold mt-2">
                            <span class="text-cyan-400">● Reaktan A</span>
                            <span class="text-rose-400">● Reaktan B</span>
                            <span class="text-emerald-400">● Produk Reaksi</span>
                        </div>
                    </div>
                    <div class="md:col-span-5 space-y-4">
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1">
                                <span>Suhu Reaksi</span>
                                <span class="font-mono-lab text-rose-400" x-text="chemTemp + ' °C'"></span>
                            </div>
                            <input type="range" min="20" max="100" step="5" x-model.number="chemTemp" class="w-full accent-rose-400 cursor-pointer">
                        </div>
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1">
                                <span>Konsentrasi Partikel</span>
                                <span class="font-mono-lab text-cyan-400" x-text="chemConc + ' Molekul'"></span>
                            </div>
                            <input type="range" min="10" max="50" step="5" x-model.number="chemConc" class="w-full accent-cyan-400 cursor-pointer">
                        </div>
                        <label class="flex items-center justify-between p-3 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                            <span class="text-sm font-bold text-white">Gunakan Katalis (Percepat Reaksi)</span>
                            <input type="checkbox" x-model="chemCatalyst" class="rounded text-emerald-500">
                        </label>
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-300">Laju Reaksi Relatif:</span>
                            <span class="text-lg font-extrabold font-mono-lab text-emerald-400" x-text="chemRate.toFixed(1) + 'x'"></span>
                        </div>
                    </div>
                </div>

            @elseif($lab->engine_type === 'quadratic_graph')
                <!-- 3. SIMULATOR PERSAMAAN KUADRAT -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-white">Grafik Fungsi Kuadrat: <span class="font-mono-lab text-emerald-400" x-text="'y = ' + qa + 'x² + (' + qb + ')x + (' + qc + ')'"></span></h3>
                        <p class="text-sm text-slate-400">Geser koefisien a, b, dan c untuk mengamati perubahan kurva parabola &amp; akar-akarnya</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                    <div class="md:col-span-7 bg-slate-950 rounded-2xl border border-slate-800 p-3">
                        <canvas id="quadCanvas" width="440" height="320" class="w-full rounded-xl"></canvas>
                    </div>
                    <div class="md:col-span-5 space-y-4">
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1"><span>Koefisien a</span><span class="font-mono-lab text-emerald-400" x-text="qa"></span></div>
                            <input type="range" min="-5" max="5" step="0.5" x-model.number="qa" class="w-full accent-emerald-400 cursor-pointer">
                        </div>
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1"><span>Koefisien b</span><span class="font-mono-lab text-cyan-400" x-text="qb"></span></div>
                            <input type="range" min="-10" max="10" step="1" x-model.number="qb" class="w-full accent-cyan-400 cursor-pointer">
                        </div>
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1"><span>Konstanta c</span><span class="font-mono-lab text-amber-400" x-text="qc"></span></div>
                            <input type="range" min="-10" max="10" step="1" x-model.number="qc" class="w-full accent-amber-400 cursor-pointer">
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-1.5 text-sm">
                            <div>Diskriminan ($D$): <strong class="font-mono-lab text-amber-400" x-text="quadInfo.d"></strong></div>
                            <div>Titik Puncak: <strong class="font-mono-lab text-cyan-400" x-text="quadInfo.vertex"></strong></div>
                            <div>Akar-akar: <strong class="font-mono-lab text-emerald-400" x-text="quadInfo.roots"></strong></div>
                        </div>
                    </div>
                </div>

            @elseif($lab->engine_type === 'beam_load')
                <!-- 4. SIMULATOR BEBAN STRUKTUR BALOK (DPIB) -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-white">Simulasi Lenturan Balok Struktur (DPIB)</h3>
                        <p class="text-sm text-slate-400">Uji pengaruh beban terpusat, panjang bentang, dan material terhadap defleksi balok</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold"
                          :class="beamDeflection > 25 ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : (beamDeflection > 12 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40')"
                          x-text="beamDeflection > 25 ? '⚠️ Kritis / Berlebih!' : (beamDeflection > 12 ? '⚡ Waspada Lenturan' : '✅ Struktur Aman')"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                    <div class="md:col-span-7 bg-slate-950 rounded-2xl border border-slate-800 p-3">
                        <canvas id="beamCanvas" width="440" height="280" class="w-full rounded-xl"></canvas>
                    </div>
                    <div class="md:col-span-5 space-y-4">
                        <div>
                            <label class="block text-sm font-bold mb-1">Material Balok</label>
                            <select x-model="beamMaterial" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-sm font-bold text-white">
                                <option value="steel">Baja Profil (Kuat Tinggi)</option>
                                <option value="concrete">Beton Bertulang</option>
                                <option value="wood">Kayu Konstruksi</option>
                            </select>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1"><span>Beban Terpusat</span><span class="font-mono-lab text-amber-400" x-text="beamLoad + ' kN'"></span></div>
                            <input type="range" min="5" max="100" step="5" x-model.number="beamLoad" class="w-full accent-amber-400 cursor-pointer">
                        </div>
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1"><span>Panjang Bentang</span><span class="font-mono-lab text-cyan-400" x-text="beamSpan + ' meter'"></span></div>
                            <input type="range" min="2" max="12" step="0.5" x-model.number="beamSpan" class="w-full accent-cyan-400 cursor-pointer">
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-sm">
                            Estimasi Lendutan (Defleksi): <strong class="font-mono-lab text-cyan-400" x-text="beamDeflection.toFixed(1) + ' mm'"></strong>
                        </div>
                    </div>
                </div>

            @elseif($lab->engine_type === 'ohm_law')
                <!-- 5. LAB HUKUM OHM ANIMASI ELEKTRON -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <h3 class="text-lg font-extrabold text-white">Rangkaian Listrik DC &amp; Aliran Elektron ($V = I \times R$)</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold"
                          :class="ohmCurrentMa > 500 ? 'bg-rose-500/20 text-rose-300' : 'bg-emerald-500/20 text-emerald-300'"
                          x-text="ohmCurrentMa > 500 ? '⚠️ Overcurrent (>500 mA)' : '✅ Arus Aman'"></span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                    <div class="md:col-span-7 bg-slate-950 rounded-2xl border border-slate-800 p-3">
                        <canvas id="ohmCanvas" width="440" height="280" class="w-full rounded-xl"></canvas>
                    </div>
                    <div class="md:col-span-5 space-y-4">
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1"><span>Tegangan ($V$)</span><span class="font-mono-lab text-amber-400" x-text="ohmV + ' V'"></span></div>
                            <input type="range" min="1" max="24" step="0.5" x-model.number="ohmV" class="w-full accent-amber-400 cursor-pointer">
                        </div>
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1"><span>Hambatan ($R$)</span><span class="font-mono-lab text-cyan-400" x-text="ohmR + ' Ω'"></span></div>
                            <input type="range" min="10" max="1000" step="10" x-model.number="ohmR" class="w-full accent-cyan-400 cursor-pointer">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                                <div class="text-xs text-slate-400">Kuat Arus ($I$)</div>
                                <div class="text-lg font-extrabold font-mono-lab text-cyan-400" x-text="ohmCurrentMa.toFixed(1) + ' mA'"></div>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                                <div class="text-xs text-slate-400">Daya ($P$)</div>
                                <div class="text-lg font-extrabold font-mono-lab text-amber-400" x-text="ohmPowerW.toFixed(2) + ' W'"></div>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($lab->engine_type === 'logic_gate')
                <!-- 6. GERBANG LOGIKA DIGITAL -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <h3 class="text-lg font-extrabold text-white">Lab Gerbang Logika Digital</h3>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="g in ['AND', 'OR', 'NAND', 'NOR', 'XOR', 'NOT']" :key="g">
                            <button type="button" @click="logicGate = g" :class="logicGate === g ? 'bg-violet-500 text-white' : 'bg-slate-800 text-slate-300'" class="px-3 py-1 rounded-lg text-xs font-extrabold font-mono-lab cursor-pointer" x-text="g"></button>
                        </template>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-center">
                    <div class="space-y-3">
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex justify-between items-center">
                            <span class="font-bold text-sm">Input A</span>
                            <button type="button" @click="logicA = logicA ? 0 : 1" :class="logicA ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-300'" class="w-12 h-8 rounded-lg font-mono-lab font-extrabold cursor-pointer" x-text="logicA"></button>
                        </div>
                        <div x-show="logicGate !== 'NOT'" class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex justify-between items-center">
                            <span class="font-bold text-sm">Input B</span>
                            <button type="button" @click="logicB = logicB ? 0 : 1" :class="logicB ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-300'" class="w-12 h-8 rounded-lg font-mono-lab font-extrabold cursor-pointer" x-text="logicB"></button>
                        </div>
                    </div>
                    <div class="p-6 rounded-2xl bg-slate-950 border border-slate-800 text-center">
                        <div class="text-2xl font-extrabold font-mono-lab text-violet-400 mb-3" x-text="logicGate"></div>
                        <div class="w-14 h-14 mx-auto rounded-full border-4 flex items-center justify-center font-mono-lab text-xl font-black transition-all"
                             :class="logicOut ? 'bg-emerald-400 border-emerald-200 text-slate-950 shadow-[0_0_25px_rgba(52,211,153,0.75)]' : 'bg-slate-800 border-slate-700 text-slate-400'"
                             x-text="logicOut"></div>
                        <div class="text-xs font-bold mt-2" x-text="logicOut ? 'OUTPUT: HIGH (1)' : 'OUTPUT: LOW (0)'"></div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-2">Tabel Kebenaran</div>
                        <table class="w-full text-xs font-mono-lab text-center">
                            <thead><tr class="text-slate-400 border-b border-slate-800"><th>A</th><th x-show="logicGate !== 'NOT'">B</th><th>OUT</th></tr></thead>
                            <tbody>
                                <template x-for="(row, idx) in truthTable" :key="idx">
                                    <tr :class="(row.a === logicA && (logicGate === 'NOT' || row.b === logicB)) ? 'bg-violet-500/20 text-white font-bold' : 'text-slate-400'">
                                        <td class="py-1" x-text="row.a"></td>
                                        <td class="py-1" x-show="logicGate !== 'NOT'" x-text="row.b"></td>
                                        <td class="py-1" :class="row.out ? 'text-emerald-400' : 'text-rose-400'" x-text="row.out"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            @elseif($lab->engine_type === 'subnetting')
                <!-- 7. LAB SUBNETTING IPv4 -->
                <h3 class="text-lg font-extrabold text-white border-b border-slate-800 pb-3">Lab Subnetting IPv4 &amp; CIDR</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold mb-1">Alamat IPv4</label>
                            <input type="text" x-model="ipInput" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 font-mono-lab text-sm text-white outline-none">
                        </div>
                        <div>
                            <div class="flex justify-between text-sm font-bold mb-1"><span>Prefix CIDR</span><span class="font-mono-lab text-sky-400" x-text="'/' + cidr"></span></div>
                            <input type="range" min="16" max="30" step="1" x-model.number="cidr" class="w-full accent-sky-400 cursor-pointer">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800"><div class="text-xs text-slate-400">Network</div><div class="font-mono-lab font-bold text-sky-400" x-text="subnetInfo.network"></div></div>
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800"><div class="text-xs text-slate-400">Broadcast</div><div class="font-mono-lab font-bold text-amber-400" x-text="subnetInfo.broadcast"></div></div>
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800"><div class="text-xs text-slate-400">Subnet Mask</div><div class="font-mono-lab font-bold text-white" x-text="subnetInfo.mask"></div></div>
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800"><div class="text-xs text-slate-400">Host Usable</div><div class="font-mono-lab font-bold text-emerald-400" x-text="subnetInfo.hosts + ' Host'"></div></div>
                    </div>
                </div>

            @else
                <!-- 8. AI CUSTOM HTML5 SIMULATOR -->
                <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                    <h3 class="text-lg font-extrabold text-white">Simulator Interaktif Kustom</h3>
                    <span class="text-xs text-cyan-400 font-bold">HTML5 Interactive Lab</span>
                </div>
                <iframe sandbox="allow-scripts"
                        srcdoc="{{ $lab->custom_html }}"
                        class="w-full rounded-xl border border-slate-800 bg-slate-950"
                        style="height: 540px;"></iframe>
            @endif

        </section>
    </main>

    <script>
        function virtualLabRunner() {
            return {
                showSop: true,
                copied: false,
                copyLabUrl() {
                    navigator.clipboard?.writeText('{{ route("simlab.lab", $lab->slug) }}');
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                },
                checkedSteps: [],
                totalSteps: {{ count($lab->sop_steps ?? []) }},
                labNotes: '',
                engineType: '{{ $lab->engine_type }}',

                // 1. Gasoline Engine 4-Tak
                rpm: 1500,
                afr: 14.7,
                crankAngle: 0,
                get currentStrokeIdx() {
                    return Math.floor(((this.crankAngle % 720) + 720) % 720 / 180);
                },
                get currentStrokeName() {
                    return ['1. Langkah HISAP (Intake)', '2. Langkah KOMPRESI', '3. Langkah USAHA (Pembakaran)', '4. Langkah BUANG (Exhaust)'][this.currentStrokeIdx];
                },
                get strokeColor() {
                    return ['#38bdf8', '#fbbf24', '#f97316', '#94a3b8'][this.currentStrokeIdx];
                },
                get engineHp() {
                    const eff = Math.max(0.4, 1 - Math.abs(this.afr - 14.7) * 0.12);
                    return (this.rpm / 1000) * 18.5 * eff;
                },
                get engineTemp() {
                    return 68 + (this.rpm / 6000) * 42 + (this.afr > 15.5 ? 8 : 0);
                },

                // 2. Chemical Reaction
                chemEq: 'water',
                chemTemp: 35,
                chemConc: 25,
                chemCatalyst: false,
                get chemRate() {
                    return ((this.chemTemp / 25) * (this.chemConc / 20)) * (this.chemCatalyst ? 2.2 : 1.0);
                },

                // 3. Quadratic Graph
                qa: 1, qb: 0, qc: -4,
                get quadInfo() {
                    const a = this.qa, b = this.qb, c = this.qc;
                    if (a === 0) return { d: '-', vertex: 'Linear (a=0)', roots: b !== 0 ? ('x = ' + (-c / b).toFixed(2)) : '-' };
                    const d = b * b - 4 * a * c;
                    const vx = (-b / (2 * a)).toFixed(2);
                    const vy = (a * vx * vx + b * vx + c).toFixed(2);
                    let roots = 'Imajiner (D < 0)';
                    if (d > 0) {
                        const x1 = ((-b + Math.sqrt(d)) / (2 * a)).toFixed(2);
                        const x2 = ((-b - Math.sqrt(d)) / (2 * a)).toFixed(2);
                        roots = `x₁=${x1}, x₂=${x2}`;
                    } else if (d === 0) {
                        roots = `x₁=x₂=${vx}`;
                    }
                    return { d: d.toFixed(1), vertex: `(${vx}, ${vy})`, roots };
                },

                // 4. Beam Load
                beamMaterial: 'concrete',
                beamLoad: 25,
                beamSpan: 5,
                get beamDeflection() {
                    const factor = this.beamMaterial === 'steel' ? 0.12 : (this.beamMaterial === 'concrete' ? 0.28 : 0.65);
                    return (this.beamLoad * Math.pow(this.beamSpan, 2) * factor) / 15;
                },

                // 5. Ohm Law
                ohmV: 12, ohmR: 220,
                get ohmCurrentMa() { return (this.ohmV / Math.max(1, this.ohmR)) * 1000; },
                get ohmPowerW() { return (this.ohmV * this.ohmV) / Math.max(1, this.ohmR); },

                // 6. Logic Gate
                logicGate: 'AND', logicA: 1, logicB: 0,
                evalGate(g, a, b) {
                    if (g === 'AND') return (a && b) ? 1 : 0;
                    if (g === 'OR') return (a || b) ? 1 : 0;
                    if (g === 'NAND') return !(a && b) ? 1 : 0;
                    if (g === 'NOR') return !(a || b) ? 1 : 0;
                    if (g === 'XOR') return (a ^ b) ? 1 : 0;
                    if (g === 'NOT') return a ? 0 : 1;
                    return 0;
                },
                get logicOut() { return this.evalGate(this.logicGate, this.logicA, this.logicB); },
                get truthTable() {
                    if (this.logicGate === 'NOT') return [{a:0,b:0,out:1},{a:1,b:0,out:0}];
                    return [
                        {a:0,b:0,out:this.evalGate(this.logicGate,0,0)},
                        {a:0,b:1,out:this.evalGate(this.logicGate,0,1)},
                        {a:1,b:0,out:this.evalGate(this.logicGate,1,0)},
                        {a:1,b:1,out:this.evalGate(this.logicGate,1,1)},
                    ];
                },

                // 7. Subnetting
                ipInput: '192.168.10.25', cidr: 24,
                get subnetInfo() {
                    const p = this.ipInput.trim().split('.').map(n => parseInt(n, 10));
                    if (p.length !== 4 || p.some(n => isNaN(n) || n < 0 || n > 255)) return { network: 'Invalid', broadcast: '-', mask: '-', hosts: 0 };
                    const ip = ((p[0] << 24) | (p[1] << 16) | (p[2] << 8) | p[3]) >>> 0;
                    const m = ((~0 << (32 - this.cidr)) >>> 0);
                    const net = (ip & m) >>> 0;
                    const bc = (net | (~m >>> 0)) >>> 0;
                    const fmt = n => [(n>>>24)&255,(n>>>16)&255,(n>>>8)&255,n&255].join('.');
                    return { network: fmt(net), broadcast: fmt(bc), mask: fmt(m), hosts: Math.max(0, Math.pow(2, 32 - this.cidr) - 2) };
                },

                init() {
                    if (this.engineType === 'gasoline_engine') this.startEngineLoop();
                    if (this.engineType === 'chemical_reaction') this.startChemLoop();
                    if (this.engineType === 'quadratic_graph') this.startQuadLoop();
                    if (this.engineType === 'beam_load') this.startBeamLoop();
                    if (this.engineType === 'ohm_law') this.startOhmLoop();
                },

                startEngineLoop() {
                    const canvas = document.getElementById('engineCanvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    const loop = () => {
                        this.crankAngle = (this.crankAngle + (this.rpm / 220)) % 720;
                        const rad = (this.crankAngle * Math.PI) / 180;
                        const stroke = this.currentStrokeIdx;
                        const pistonY = 120 - Math.cos(rad) * 42;

                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        // Silinder
                        ctx.fillStyle = '#1e293b';
                        ctx.fillRect(140, 40, 140, 180);
                        // Ruang bakar warna siklus
                        const chamberColors = ['rgba(56,189,248,0.28)', 'rgba(251,191,36,0.3)', 'rgba(249,115,22,0.55)', 'rgba(148,163,184,0.25)'];
                        ctx.fillStyle = chamberColors[stroke];
                        ctx.fillRect(144, 44, 132, pistonY - 44);
                        // Katup Hisap & Buang
                        ctx.fillStyle = stroke === 0 ? '#38bdf8' : '#64748b';
                        ctx.fillRect(160, stroke === 0 ? 44 : 34, 24, 14);
                        ctx.fillStyle = stroke === 3 ? '#f43f5e' : '#64748b';
                        ctx.fillRect(236, stroke === 3 ? 44 : 34, 24, 14);
                        // Busi + Spark
                        ctx.fillStyle = '#e2e8f0';
                        ctx.fillRect(204, 20, 12, 24);
                        if (stroke === 2 && (this.crankAngle % 180) < 45) {
                            ctx.fillStyle = '#fde047';
                            ctx.beginPath(); ctx.arc(210, 52, 12, 0, Math.PI * 2); ctx.fill();
                        }
                        // Piston
                        ctx.fillStyle = '#cbd5e1';
                        ctx.fillRect(145, pistonY, 130, 42);
                        // Crankshaft & Rod
                        const cx = 210 + Math.sin(rad) * 32;
                        const cy = 255 + Math.cos(rad) * 32;
                        ctx.strokeStyle = '#94a3b8'; ctx.lineWidth = 8;
                        ctx.beginPath(); ctx.moveTo(210, pistonY + 25); ctx.lineTo(cx, cy); ctx.stroke();
                        ctx.strokeStyle = '#475569'; ctx.lineWidth = 3;
                        ctx.beginPath(); ctx.arc(210, 255, 36, 0, Math.PI * 2); ctx.stroke();
                        ctx.fillStyle = '#f59e0b';
                        ctx.beginPath(); ctx.arc(cx, cy, 7, 0, Math.PI * 2); ctx.fill();
                        requestAnimationFrame(loop);
                    };
                    loop();
                },

                startChemLoop() {
                    const canvas = document.getElementById('chemCanvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    const particles = Array.from({length: 50}, (_, i) => ({
                        x: 40 + Math.random() * 360, y: 30 + Math.random() * 240,
                        vx: (Math.random() - 0.5) * 3, vy: (Math.random() - 0.5) * 3,
                        type: i % 3 === 0 ? 'prod' : (i % 2 === 0 ? 'a' : 'b')
                    }));
                    const loop = () => {
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.strokeStyle = '#475569'; ctx.lineWidth = 3;
                        ctx.strokeRect(20, 15, 400, 270);
                        const speed = (this.chemTemp / 30) * (this.chemCatalyst ? 1.6 : 1);
                        const activeCount = Math.min(50, this.chemConc);
                        for (let i = 0; i < activeCount; i++) {
                            const p = particles[i];
                            p.x += p.vx * speed; p.y += p.vy * speed;
                            if (p.x < 30 || p.x > 410) p.vx *= -1;
                            if (p.y < 25 || p.y > 275) p.vy *= -1;
                            ctx.fillStyle = p.type === 'a' ? '#38bdf8' : (p.type === 'b' ? '#fb7185' : '#34d399');
                            ctx.beginPath(); ctx.arc(p.x, p.y, p.type === 'prod' ? 8 : 6, 0, Math.PI * 2); ctx.fill();
                        }
                        requestAnimationFrame(loop);
                    };
                    loop();
                },

                startQuadLoop() {
                    const canvas = document.getElementById('quadCanvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    const draw = () => {
                        const w = canvas.width, h = canvas.height, cx = w / 2, cy = h / 2, scale = 20;
                        ctx.clearRect(0, 0, w, h);
                        ctx.strokeStyle = '#1e293b'; ctx.lineWidth = 1;
                        for (let x = 0; x < w; x += scale) { ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, h); ctx.stroke(); }
                        for (let y = 0; y < h; y += scale) { ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke(); }
                        ctx.strokeStyle = '#64748b'; ctx.lineWidth = 2;
                        ctx.beginPath(); ctx.moveTo(0, cy); ctx.lineTo(w, cy); ctx.moveTo(cx, 0); ctx.lineTo(cx, h); ctx.stroke();
                        ctx.strokeStyle = '#10b981'; ctx.lineWidth = 3;
                        ctx.beginPath();
                        for (let px = 0; px <= w; px += 2) {
                            const x = (px - cx) / scale;
                            const y = this.qa * x * x + this.qb * x + this.qc;
                            const py = cy - y * scale;
                            if (px === 0) ctx.moveTo(px, py); else ctx.lineTo(px, py);
                        }
                        ctx.stroke();
                        requestAnimationFrame(draw);
                    };
                    draw();
                },

                startBeamLoop() {
                    const canvas = document.getElementById('beamCanvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    const draw = () => {
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        const bend = Math.min(45, this.beamDeflection * 1.1);
                        // Tumpuan kiri kanan
                        ctx.fillStyle = '#64748b';
                        ctx.beginPath(); ctx.moveTo(50, 210); ctx.lineTo(35, 240); ctx.lineTo(65, 240); ctx.fill();
                        ctx.beginPath(); ctx.moveTo(390, 210); ctx.lineTo(375, 240); ctx.lineTo(405, 240); ctx.fill();
                        // Balok melentur
                        ctx.strokeStyle = this.beamDeflection > 25 ? '#f43f5e' : (this.beamDeflection > 12 ? '#fbbf24' : '#38bdf8');
                        ctx.lineWidth = 14;
                        ctx.beginPath();
                        ctx.moveTo(45, 200);
                        ctx.quadraticCurveTo(220, 200 + bend * 2, 395, 200);
                        ctx.stroke();
                        // Panah beban
                        ctx.fillStyle = '#f97316';
                        ctx.fillRect(214, 90, 12, 70 + bend);
                        ctx.beginPath(); ctx.moveTo(202, 160 + bend); ctx.lineTo(238, 160 + bend); ctx.lineTo(220, 185 + bend); ctx.fill();
                        requestAnimationFrame(draw);
                    };
                    draw();
                },

                startOhmLoop() {
                    const canvas = document.getElementById('ohmCanvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    let offset = 0;
                    const draw = () => {
                        offset = (offset + Math.min(8, this.ohmCurrentMa / 40)) % 40;
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.strokeStyle = '#334155'; ctx.lineWidth = 6;
                        ctx.strokeRect(70, 55, 300, 170);
                        // Lampu di kanan
                        const glow = Math.min(1, this.ohmPowerW / 6);
                        ctx.fillStyle = `rgba(251, 191, 36, ${0.2 + glow * 0.8})`;
                        ctx.beginPath(); ctx.arc(370, 140, 26, 0, Math.PI * 2); ctx.fill();
                        // Elektron bergerak di jalur atas
                        ctx.fillStyle = '#38bdf8';
                        for (let x = 70 + offset; x < 370; x += 40) {
                            ctx.beginPath(); ctx.arc(x, 55, 5, 0, Math.PI * 2); ctx.fill();
                        }
                        requestAnimationFrame(draw);
                    };
                    draw();
                }
            };
        }
    </script>
</body>
</html>
