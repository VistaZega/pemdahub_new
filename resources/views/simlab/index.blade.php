<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaHUB Research &amp; SimLab — Kompleks Laboratorium Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            background-color: #f1f5f9;
            background-image:
                linear-gradient(to right, rgba(14, 165, 233, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(14, 165, 233, 0.05) 1px, transparent 1px);
            background-size: 32px 32px;
            min-height: 100vh;
            color: #0f172a;
        }
        .blueprint-room {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .blueprint-room:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
@php
    $isInstructor = auth()->check() && (auth()->user()->teacher || auth()->user()->isAdmin());

    $engineUrls = [
        'gasoline_engine'    => asset('simlab-engines/mesin-4tak-2tak.html'),
        'beam_load'          => asset('simlab-engines/teknik-gambar-bangunan.html'),
        'subnetting'         => asset('simlab-engines/tkj-jaringan.html'),
        'physics_motion'     => asset('simlab-engines/fisika-gerak-gaya.html'),
        'biology_cell'       => asset('simlab-engines/biologi-pembelahan-sel.html'),
        'math_statistics'    => asset('simlab-engines/matematika-statistika.html'),
        'sociology_dynamics' => asset('simlab-engines/sosiologi-dinamika-sosial.html'),
        'language_syntax'    => asset('simlab-engines/bahasa-struktur-kalimat.html'),
        'chem_ph'            => asset('simlab-engines/kimia-asam-basa-ph.html'),
        'bio_photosynthesis' => asset('simlab-engines/biologi-fotosintesis.html'),
        'physics_parabola'   => asset('simlab-engines/fisika-gerak-parabola.html'),
        'math_trigonometry'  => asset('simlab-engines/matematika-trigonometri.html'),
        'econ_market'        => asset('simlab-engines/ekonomi-pasar-keseimbangan.html'),
        'physics_wave'       => asset('simlab-engines/ipa-gelombang-bunyi.html'),
        'bio_blood'          => asset('simlab-engines/bio-peredaran-darah.html'),
        'physics_archimedes' => asset('simlab-engines/fisika-archimedes-apung.html'),
        'chem_atom'          => asset('simlab-engines/kimia-struktur-atom.html'),
        'ipa_greenhouse'     => asset('simlab-engines/ipa-efek-rumah-kaca.html'),
        'olympiad_5'         => asset('simlab-engines/olimpiade-5-soal.html'),
        'chemical_reaction'  => asset('simlab-engines/kimia-reaksi-laju.html'),
        'quadratic_graph'    => asset('simlab-engines/matematika-kuadrat-parabola.html'),
        'ohm_law'            => asset('simlab-engines/fisika-hukum-ohm-rangkaian.html'),
        'logic_gate'         => asset('simlab-engines/informatika-gerbang-logika.html'),
        'physics_doppler'    => asset('simlab-engines/fisika-efek-doppler.html'),
        'physics_pulley'     => asset('simlab-engines/fisika-katrol-usaha.html'),
        'chem_stoichiometry' => asset('simlab-engines/kimia-stoikiometri-pembatas.html'),
        'math_probability'   => asset('simlab-engines/matematika-peluang-kombinasi.html'),
        'ptn_speed'          => asset('simlab-engines/ptn-soal-cepat.html'),
    ];

    $rooms = [
        'fisika'        => ['code' => 'LAB-01', 'name' => 'Sayap IPA & Fisika', 'sub' => 'Gerak, Gaya, Fluida, Bunyi & Listrik', 'icon' => 'fa-atom', 'color' => '#0284c7', 'ring' => 'ring-sky-500 border-sky-500 bg-sky-50/90'],
        'kimia_biologi' => ['code' => 'LAB-02', 'name' => 'Sayap Kimia & Biologi', 'sub' => 'Reaksi, Atom, pH, Sel & Tubuh', 'icon' => 'fa-flask', 'color' => '#059669', 'ring' => 'ring-emerald-500 border-emerald-500 bg-emerald-50/90'],
        'matematika'    => ['code' => 'LAB-03', 'name' => 'Sayap Matematika', 'sub' => 'Grafik, Statistika, Trigonometri & Peluang', 'icon' => 'fa-square-root-variable', 'color' => '#4f46e5', 'ring' => 'ring-indigo-500 border-indigo-500 bg-indigo-50/90'],
        'vokasi'        => ['code' => 'LAB-04', 'name' => 'Bengkel Vokasi (SMK)', 'sub' => 'Otomotif 4-Tak, DPIB, TKJ & Logika', 'icon' => 'fa-gears', 'color' => '#ea580c', 'ring' => 'ring-orange-500 border-orange-500 bg-orange-50/90'],
        'ips_bahasa'    => ['code' => 'LAB-05', 'name' => 'Sayap IPS & Bahasa', 'sub' => 'Ekonomi Pasar, Sosiologi & Bahasa', 'icon' => 'fa-earth-asia', 'color' => '#7c3aed', 'ring' => 'ring-purple-500 border-purple-500 bg-purple-50/90'],
        'olimpiade'     => ['code' => 'LAB-06', 'name' => 'Arena Olimpiade & PTN', 'sub' => '5 Soal Dunia & Trik Cepat SNBT', 'icon' => 'fa-trophy', 'color' => '#d97706', 'ring' => 'ring-amber-500 border-amber-500 bg-amber-50/90'],
    ];

    $labsPayload = $virtualLabs->map(function ($lab) use ($engineUrls) {
        return [
            'id'               => $lab->id,
            'slug'             => $lab->slug,
            'title'            => $lab->title,
            'discipline_key'   => $lab->discipline_key,
            'discipline_label' => $lab->discipline_label,
            'subject_name'     => $lab->subject_name,
            'description'      => $lab->description,
            'icon'             => $lab->icon ?? 'fa-flask',
            'accent_color'     => $lab->accent_color ?? '#0284c7',
            'sop_steps'        => is_array($lab->sop_steps) ? $lab->sop_steps : [],
            'engine_url'       => $engineUrls[$lab->engine_type] ?? null,
            'custom_html'      => $lab->engine_type === 'ai_custom_html' ? $lab->custom_html : null,
            'full_url'         => route('simlab.lab', $lab->slug),
            'course_name'      => $lab->course?->course_name,
            'can_delete'       => auth()->check() && ($lab->created_by === auth()->id() || auth()->user()->isAdmin()),
            'tc'               => $lab->theory_and_challenge,
        ];
    })->values();
@endphp
<body class="antialiased selection:bg-sky-200 selection:text-slate-900" x-data="labFloorplanApp()">

    <!-- Top Command Bar -->
    <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur-md">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 via-teal-500 to-emerald-500 flex items-center justify-center shadow-md shadow-sky-500/20">
                        <i class="fas fa-microscope text-white text-base"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-slate-900 tracking-tight text-sm sm:text-base block leading-none">PembdaHUB <span class="text-sky-600">Research Complex</span></span>
                        <span class="text-[11px] text-slate-500 font-medium hidden sm:block">Denah Laboratorium &amp; Meja Eksperimen Langsung</span>
                    </div>
                </a>

                @if($isInstructor)
                <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold">
                    <i class="fas fa-chalkboard-teacher"></i> Mode Instruktur
                </span>
                @else
                <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold">
                    <i class="fas fa-user-graduate"></i> Mode Praktikan
                </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <!-- Search Global -->
                <div class="relative hidden sm:block w-56 lg:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" @input="if(searchQuery.trim() !== '') activeRoom = 'all'"
                           placeholder="Cari fenomena / soal..."
                           class="w-full pl-8 pr-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-sky-500 transition">
                </div>

                <button type="button" @click="showFloorplan = !showFloorplan"
                        class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-bold transition inline-flex items-center gap-1.5">
                    <i class="fas" :class="showFloorplan ? 'fa-map-location-dot text-sky-600' : 'fa-map'"></i>
                    <span class="hidden md:inline" x-text="showFloorplan ? 'Sembunyikan Denah' : 'Buka Denah Gedung'"></span>
                </button>

                @if($isInstructor)
                <button @click="openAiModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold shadow-sm transition">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <span class="hidden sm:inline">Buat Simulator AI</span>
                </button>
                @endif

                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Portal</span>
                </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-5">

        <!-- BAGIAN 1: DENAH KOMPLEKS LABORATORIUM INTERAKTIF (BLUEPRINT FLOORPLAN) -->
        <section x-show="showFloorplan" x-transition class="bg-white border border-slate-300 rounded-3xl p-4 sm:p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 mb-4 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 text-[10px] font-extrabold uppercase tracking-wider">Denah Gedung Riset</span>
                        <h1 class="text-base sm:text-lg font-extrabold text-slate-900">Pilih Ruangan Laboratorium untuk Memasuki Meja Praktikum</h1>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Klik salah satu sayap ruangan di bawah ini — rak instrumen &amp; meja simulasi live akan langsung menyala tanpa pindah halaman.</p>
                </div>
                <button type="button" @click="activeRoom = 'all'"
                        :class="activeRoom === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition">
                    Tampilkan Semua ({{ $virtualLabs->count() }} Alat)
                </button>
            </div>

            <!-- Floorplan Architectural Grid (6 Wing Rooms) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach($rooms as $rKey => $r)
                @php $rCount = $virtualLabs->filter(fn($v) => $v->discipline_key === $rKey)->count(); @endphp
                <button type="button" @click="selectRoom('{{ $rKey }}')"
                        :class="activeRoom === '{{ $rKey }}' ? '{{ $r['ring'] }} ring-2 shadow-md' : 'border-slate-200 bg-slate-50/70 hover:bg-white hover:border-slate-300'"
                        class="blueprint-room text-left rounded-2xl border-2 p-3.5 flex flex-col justify-between gap-2.5">
                    <div class="flex items-center justify-between w-full">
                        <span class="font-mono text-[10px] font-extrabold px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-600">{{ $r['code'] }}</span>
                        <span class="w-2.5 h-2.5 rounded-full" :class="activeRoom === '{{ $rKey }}' ? 'bg-emerald-500 animate-ping' : 'bg-slate-300'"></span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-base shrink-0 shadow-xs" style="background: {{ $r['color'] }};">
                            <i class="fas {{ $r['icon'] }}"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="font-extrabold text-xs sm:text-sm text-slate-900 leading-tight">{{ $r['name'] }}</div>
                            <div class="text-[10px] text-slate-500 mt-0.5 line-clamp-1">{{ $r['sub'] }}</div>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between text-[11px] font-bold text-slate-600">
                        <span>{{ $rCount }} Simulasi</span>
                        <span class="text-sky-600" x-show="activeRoom === '{{ $rKey }}'">Aktif ●</span>
                    </div>
                </button>
                @endforeach
            </div>
        </section>

        <!-- BAGIAN 2: MEJA RISET SPLIT-SCREEN (RAK INSTRUMEN KIRI + MEJA PRAKTIKUM LIVE KANAN) -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            <!-- PANEL KIRI: RAK INSTRUMEN & DAFTAR FENOMENA -->
            <aside class="lg:col-span-4 xl:col-span-3 bg-white border border-slate-300 rounded-3xl p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-200">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fas fa-cabinet-filing text-sky-600"></i>
                        <span>Rak Instrumen Lab</span>
                    </h2>
                    <span class="text-xs font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200" x-text="filteredLabs.length + ' Tersedia'"></span>
                </div>

                <!-- Mobile Search -->
                <div class="sm:hidden">
                    <input type="text" x-model="searchQuery" placeholder="Cari fenomena atau soal..."
                           class="w-full px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-900">
                </div>

                <div class="space-y-2 max-h-[680px] overflow-y-auto pr-1">
                    <template x-for="lab in filteredLabs" :key="lab.id">
                        <button type="button" @click="selectLab(lab)"
                                :class="selectedLab && selectedLab.id === lab.id ? 'bg-sky-50 border-sky-500 ring-1 ring-sky-500 shadow-xs' : 'bg-slate-50/70 border-slate-200 hover:bg-white hover:border-slate-300'"
                                class="w-full text-left p-3 rounded-2xl border transition flex items-start gap-3 group">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm shrink-0 shadow-xs mt-0.5"
                                 :style="'background:' + lab.accent_color">
                                <i class="fas" :class="lab.icon"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[10px] font-bold text-slate-500 truncate" x-text="lab.subject_name"></span>
                                    <span x-show="selectedLab && selectedLab.id === lab.id" class="text-[10px] font-extrabold text-emerald-600 shrink-0">● LIVE</span>
                                </div>
                                <div class="font-bold text-xs sm:text-sm text-slate-900 leading-snug mt-0.5" x-text="lab.title"></div>
                                <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed" x-text="lab.description"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </aside>

            <!-- PANEL KANAN: MEJA PRAKTIKUM LIVE -->
            <div class="lg:col-span-8 xl:col-span-9 bg-white border border-slate-300 rounded-3xl p-4 sm:p-5 shadow-sm space-y-4" x-show="selectedLab">
                <!-- Header Meja Praktikum -->
                <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 pb-3.5 border-b border-slate-200">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white text-lg shrink-0 shadow-sm"
                             :style="'background:' + (selectedLab?.accent_color || '#0284c7')">
                            <i class="fas" :class="selectedLab?.icon || 'fa-flask'"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 text-[10px] font-extrabold uppercase" x-text="selectedLab?.discipline_label"></span>
                                <span class="text-xs font-semibold text-slate-500" x-text="selectedLab?.subject_name"></span>
                            </div>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight mt-0.5" x-text="selectedLab?.title"></h2>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        @if($isInstructor)
                        <button type="button" @click="copySelectedUrl()"
                                class="px-3 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition inline-flex items-center gap-1.5">
                            <i class="fas fa-link"></i>
                            <span x-text="copied ? 'Link LMS Tersalin!' : 'Salin Link LMS'"></span>
                        </button>
                        <button type="button" x-show="selectedLab?.can_delete" @click="deleteLab(selectedLab.id, selectedLab.title)"
                                class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition">
                            <i class="fas fa-trash-can"></i>
                        </button>
                        @endif
                        <a :href="selectedLab?.full_url" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-sky-600 text-white text-xs font-bold transition inline-flex items-center gap-1.5">
                            <i class="fas fa-expand"></i>
                            <span>Mode Layar Penuh</span>
                        </a>
                    </div>
                </div>

                <!-- Pertanyaan Pemantik Fenomena -->
                <div class="px-4 py-3 rounded-2xl bg-gradient-to-r from-sky-50 to-emerald-50 border border-sky-200 flex items-start gap-3">
                    <i class="fas fa-lightbulb text-amber-500 text-lg mt-0.5 shrink-0"></i>
                    <p class="text-xs sm:text-sm font-medium text-slate-800 leading-relaxed" x-text="selectedLab?.description"></p>
                </div>

                <!-- Tab Switcher Meja Praktikum -->
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
                    <button type="button" @click="workTab = 'sim'" :class="workTab === 'sim' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition inline-flex items-center gap-1.5">
                        <i class="fas fa-play-circle text-emerald-400"></i> Meja Simulasi Live
                    </button>
                    <button type="button" @click="workTab = 'sop'" :class="workTab === 'sop' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition inline-flex items-center gap-1.5">
                        <i class="fas fa-clipboard-check"></i> SOP Praktikum (<span x-text="selectedLab?.sop_steps?.length || 0"></span>)
                    </button>
                    <button type="button" @click="workTab = 'teori'" :class="workTab === 'teori' ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition inline-flex items-center gap-1.5">
                        <i class="fas fa-book-open"></i> Teori, Hukum &amp; Rumus
                    </button>
                    <button type="button" @click="workTab = 'soal'" :class="workTab === 'soal' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition inline-flex items-center gap-1.5">
                        <i class="fas fa-bullseye"></i> Pecahkan Soal HOTS
                    </button>
                </div>

                <!-- TAB 1: MEJA SIMULASI LIVE -->
                <div x-show="workTab === 'sim'">
                    <template x-if="selectedLab?.engine_url">
                        <iframe :src="selectedLab.engine_url" class="w-full rounded-2xl border border-slate-200 bg-white" style="height: 690px;"></iframe>
                    </template>
                    <template x-if="!selectedLab?.engine_url && selectedLab?.custom_html">
                        <iframe sandbox="allow-scripts" :srcdoc="selectedLab.custom_html" class="w-full rounded-2xl border border-slate-200 bg-slate-950" style="height: 640px;"></iframe>
                    </template>
                </div>

                <!-- TAB 2: SOP PRAKTIKUM -->
                <div x-show="workTab === 'sop'" x-cloak class="space-y-3 py-2">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-amber-700">Prosedur Operasional Standar (SOP) Masuk Lab</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <template x-for="(step, idx) in (selectedLab?.sop_steps || [])" :key="idx">
                            <label class="flex items-start gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-emerald-400 cursor-pointer transition">
                                <input type="checkbox" class="mt-1 rounded text-emerald-600">
                                <span class="text-xs sm:text-sm text-slate-800 leading-relaxed">
                                    <strong class="text-emerald-700" x-text="'Langkah ' + (idx + 1) + ':'"></strong>
                                    <span x-text="step"></span>
                                </span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- TAB 3: TEORI, HUKUM ALAM & RUMUS -->
                <div x-show="workTab === 'teori'" x-cloak class="space-y-4 py-2">
                    <div class="p-5 rounded-2xl bg-sky-50 border border-sky-200">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-sky-700">Hukum &amp; Prinsip Ilmu Pengetahuan</span>
                        <h3 class="text-lg font-extrabold text-slate-900 mt-1" x-text="selectedLab?.tc?.law"></h3>
                        <p class="text-sm text-slate-700 leading-relaxed mt-2" x-text="selectedLab?.tc?.theory"></p>
                    </div>
                    <div class="p-5 rounded-2xl bg-slate-900 text-emerald-300 font-mono text-base sm:text-lg font-bold text-center" x-text="selectedLab?.tc?.formula"></div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-700">
                        <strong>Keterangan Variabel &amp; Satuan:</strong><br>
                        <span x-text="selectedLab?.tc?.variables"></span>
                    </div>
                </div>

                <!-- TAB 4: TANTANGAN SOAL HOTS -->
                <div x-show="workTab === 'soal'" x-cloak class="space-y-4 py-2">
                    <div class="p-5 rounded-2xl bg-indigo-50 border border-indigo-200">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-700">Uji Pemahaman Soal Rumit (HOTS)</span>
                        <p class="text-sm sm:text-base font-bold text-slate-900 mt-2 leading-relaxed" x-text="selectedLab?.tc?.challenge_q"></p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="(opt, oIdx) in (selectedLab?.tc?.options || [])" :key="oIdx">
                            <button type="button" @click="chosenOpt = oIdx; checkedAns = true"
                                    :class="checkedAns
                                        ? (oIdx === selectedLab.tc.answer_idx
                                            ? 'bg-emerald-50 border-emerald-500 text-emerald-900 font-bold'
                                            : (chosenOpt === oIdx ? 'bg-rose-50 border-rose-400 text-rose-800' : 'bg-slate-50 border-slate-200 text-slate-500'))
                                        : 'bg-white hover:bg-sky-50 border-slate-300 text-slate-800'"
                                    class="p-3.5 rounded-xl border text-left text-xs sm:text-sm font-semibold transition flex items-center justify-between">
                                <span x-text="String.fromCharCode(65 + oIdx) + '. ' + opt"></span>
                                <i x-show="checkedAns && oIdx === selectedLab.tc.answer_idx" class="fas fa-check-circle text-emerald-600"></i>
                            </button>
                        </template>
                    </div>
                    <div x-show="checkedAns" x-transition class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs sm:text-sm text-emerald-900 leading-relaxed">
                        <strong><i class="fas fa-lightbulb text-amber-500 mr-1"></i> Pembahasan Lengkap:</strong><br>
                        <span x-text="selectedLab?.tc?.solution"></span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal: Buat Simulator Baru dengan AI (Guru/Admin) -->
    @if($isInstructor)
    <div x-show="openAiModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="if(!isGenerating) openAiModal = false"
             class="w-full max-w-xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden text-slate-900">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                        <i class="fas fa-wand-magic-sparkles text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Buat Simulator Lab Baru (AI Generator)</h3>
                        <p class="text-[11px] text-slate-500">Hasilkan simulasi fenomena beranimasi + SOP praktikum otomatis untuk siswa</p>
                    </div>
                </div>
                <button @click="if(!isGenerating) openAiModal = false" class="text-slate-400 hover:text-slate-700 p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form @submit.prevent="submitAiLab" class="p-6 space-y-4">
                <div x-show="aiError" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium" x-text="aiError"></div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Judul Fenomena / Simulator Lab *</label>
                    <input type="text" x-model="form.title" required placeholder="Contoh: Simulator Mesin Pendingin AC / Hukum Archimedes / Siklus Air"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Rumpun / Kategori *</label>
                        <select x-model="form.category" required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-indigo-500">
                            <option value="umum">IPA / IPS / Bahasa / Umum</option>
                            <option value="smp">Matematika &amp; Sains Dasar</option>
                            <option value="sma">Sains Lanjut (Fisika/Kimia/Biologi)</option>
                            <option value="vokasi">Kejuruan &amp; Vokasi (SMK)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Mata Pelajaran / Kejuruan *</label>
                        <input type="text" x-model="form.subject_name" required placeholder="Contoh: Fisika / TKR / Kimia / Biologi"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                @if($teacherCourses->count() > 0)
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Hubungkan ke Course / Modul LMS (Opsional)</label>
                    <select x-model="form.course_id"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-indigo-500">
                        <option value="">-- Tidak dihubungkan ke course tertentu --</option>
                        @foreach($teacherCourses as $c)
                        <option value="{{ $c->id }}">{{ $c->course_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Objek Nyata, Variabel &amp; Contoh Soal yang Diinginkan</label>
                    <textarea x-model="form.description" rows="3" placeholder="Jelaskan objek nyata yang ditampilkan, variabel slider yang bisa diubah siswa, serta contoh soal yang dibuktikan..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="openAiModal = false" :disabled="isGenerating"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isGenerating"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-60 text-white text-xs font-semibold shadow-md transition">
                        <i class="fas" :class="isGenerating ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i>
                        <span x-text="isGenerating ? 'Merakit Simulator & SOP...' : 'Generate Simulator'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        function labFloorplanApp() {
            const labs = @json($labsPayload);
            return {
                labs,
                activeRoom: 'fisika',
                searchQuery: '',
                showFloorplan: true,
                selectedLab: labs.find(l => l.discipline_key === 'fisika') || labs[0] || null,
                workTab: 'sim',
                chosenOpt: null,
                checkedAns: false,
                copied: false,
                openAiModal: false,
                isGenerating: false,
                aiError: '',
                form: {
                    title: '',
                    category: 'sma',
                    subject_name: '',
                    course_id: '',
                    description: ''
                },
                get filteredLabs() {
                    const q = this.searchQuery.toLowerCase().trim();
                    return this.labs.filter(l => {
                        const matchRoom = this.activeRoom === 'all' || l.discipline_key === this.activeRoom;
                        const matchQ = !q || (l.title + ' ' + l.subject_name + ' ' + l.description + ' ' + l.discipline_label).toLowerCase().includes(q);
                        return matchRoom && matchQ;
                    });
                },
                selectRoom(roomKey) {
                    this.activeRoom = roomKey;
                    const firstInRoom = this.labs.find(l => l.discipline_key === roomKey);
                    if (firstInRoom) this.selectLab(firstInRoom);
                },
                selectLab(lab) {
                    this.selectedLab = lab;
                    this.workTab = 'sim';
                    this.chosenOpt = null;
                    this.checkedAns = false;
                },
                copySelectedUrl() {
                    if (!this.selectedLab) return;
                    navigator.clipboard?.writeText(this.selectedLab.full_url);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 1800);
                },
                async submitAiLab() {
                    this.isGenerating = true;
                    this.aiError = '';
                    try {
                        const res = await fetch('{{ route("simlab.ai-generate") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            this.aiError = data.message || 'Gagal membangkitkan simulator.';
                            return;
                        }
                        window.location.href = data.redirect_url;
                    } catch (e) {
                        this.aiError = 'Terjadi gangguan koneksi saat memproses permintaan.';
                    } finally {
                        this.isGenerating = false;
                    }
                },
                async deleteLab(id, title) {
                    if (!confirm(`Hapus simulator "${title}"?`)) return;
                    try {
                        const res = await fetch(`{{ url('/simlab/lab') }}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        if (res.ok) window.location.reload();
                        else alert('Gagal menghapus simulator.');
                    } catch (e) {
                        alert('Terjadi kesalahan saat menghapus simulator.');
                    }
                }
            };
        }
    </script>
</body>
</html>
