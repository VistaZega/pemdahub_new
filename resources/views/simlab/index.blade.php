<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaHUB — Kompleks Laboratorium Riset &amp; Vokasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Public Sans', system-ui, -apple-system, sans-serif;
            background-color: #e8eef5;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background-image: url('{{ asset("images/simlab-building-bg.webp") }}?v=20261127f');
            background-size: cover;
            background-position: center 25%;
            background-repeat: no-repeat;
            opacity: 0.22;
            filter: saturate(0.85) contrast(1.05);
        }
        .font-mono-lab {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-variant-numeric: tabular-nums;
        }
        [x-cloak] { display: none !important; }
        /* Custom scrollbar halus konsisten dengan palet arsitektural */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
@php
    $isInstructor = auth()->check() && (auth()->user()->teacher || auth()->user()->isAdmin());
    $vTag = '?v=20261127e';

    $engineUrls = [
        'gasoline_engine'        => asset('simlab-engines/mesin-4tak-2tak.html') . $vTag,
        'beam_load'              => asset('simlab-engines/teknik-gambar-bangunan.html') . $vTag,
        'subnetting'             => asset('simlab-engines/tkj-jaringan.html') . $vTag,
        'physics_motion'         => asset('simlab-engines/fisika-gerak-gaya.html') . $vTag,
        'biology_cell'           => asset('simlab-engines/biologi-pembelahan-sel.html') . $vTag,
        'math_statistics'        => asset('simlab-engines/matematika-statistika.html') . $vTag,
        'sociology_dynamics'     => asset('simlab-engines/sosiologi-dinamika-sosial.html') . $vTag,
        'language_syntax'        => asset('simlab-engines/bahasa-struktur-kalimat.html') . $vTag,
        'chem_ph'                => asset('simlab-engines/kimia-asam-basa-ph.html') . $vTag,
        'bio_photosynthesis'     => asset('simlab-engines/biologi-fotosintesis.html') . $vTag,
        'physics_parabola'       => asset('simlab-engines/fisika-gerak-parabola.html') . $vTag,
        'math_trigonometry'      => asset('simlab-engines/matematika-trigonometri.html') . $vTag,
        'econ_market'            => asset('simlab-engines/ekonomi-pasar-keseimbangan.html') . $vTag,
        'physics_wave'           => asset('simlab-engines/ipa-gelombang-bunyi.html') . $vTag,
        'bio_blood'              => asset('simlab-engines/bio-peredaran-darah.html') . $vTag,
        'physics_archimedes'     => asset('simlab-engines/fisika-archimedes-apung.html') . $vTag,
        'chem_atom'              => asset('simlab-engines/kimia-struktur-atom.html') . $vTag,
        'ipa_greenhouse'         => asset('simlab-engines/ipa-efek-rumah-kaca.html') . $vTag,
        'olympiad_5'             => asset('simlab-engines/olimpiade-5-soal.html') . $vTag,
        'chemical_reaction'      => asset('simlab-engines/kimia-reaksi-laju.html') . $vTag,
        'quadratic_graph'        => asset('simlab-engines/matematika-kuadrat-parabola.html') . $vTag,
        'ohm_law'                => asset('simlab-engines/fisika-hukum-ohm-rangkaian.html') . $vTag,
        'logic_gate'             => asset('simlab-engines/informatika-gerbang-logika.html') . $vTag,
        'physics_doppler'        => asset('simlab-engines/fisika-efek-doppler.html') . $vTag,
        'physics_pulley'         => asset('simlab-engines/fisika-katrol-usaha.html') . $vTag,
        'chem_stoichiometry'     => asset('simlab-engines/kimia-stoikiometri-pembatas.html') . $vTag,
        'math_probability'       => asset('simlab-engines/matematika-peluang-kombinasi.html') . $vTag,
        'ptn_speed'              => asset('simlab-engines/ptn-soal-cepat.html') . $vTag,
        'ipa_renewable'          => asset('simlab-engines/ipa-energi-terbarukan.html') . $vTag,
        'bio_genetics'           => asset('simlab-engines/biologi-genetika-mendel.html') . $vTag,
        'math_transform'         => asset('simlab-engines/matematika-transformasi-geometri.html') . $vTag,
        'vokasi_plc'             => asset('simlab-engines/vokasi-otomasi-relay.html') . $vTag,
        'econ_compound'          => asset('simlab-engines/ekonomi-bunga-majemuk.html') . $vTag,
        'physics_optics'         => asset('simlab-engines/fisika-optika-lensa.html') . $vTag,
        'physics_elevator'       => asset('simlab-engines/fisika-newton-lift.html') . $vTag,
        'physics_electric_field' => asset('simlab-engines/fisika-medan-listrik.html') . $vTag,
        'bio_osmosis'            => asset('simlab-engines/biologi-osmosis-sel.html') . $vTag,
        'bio_nervous'            => asset('simlab-engines/biologi-sistem-saraf.html') . $vTag,
        'math_integral'          => asset('simlab-engines/matematika-integral-riemann.html') . $vTag,
        'math_trig_circle'       => asset('simlab-engines/matematika-trigonometri-interaktif.html') . $vTag,
        'vokasi_lan_topo'        => asset('simlab-engines/vokasi-jaringan-lan.html') . $vTag,
        'vokasi_cad_dpib'        => asset('simlab-engines/vokasi-cad-denah.html') . $vTag,
        'econ_inflation'         => asset('simlab-engines/ips-inflasi-deflasi.html') . $vTag,
        'ptn_speed_advanced'     => asset('simlab-engines/ptn-soal-cepat-lanjutan.html') . $vTag,
        'physics_pendulum'       => asset('simlab-engines/fisika-bandul-harmonik.html') . $vTag,
        'physics_calorimetry'    => asset('simlab-engines/fisika-asas-black-kalor.html') . $vTag,
        'chem_titration'         => asset('simlab-engines/kimia-titrasi-asam-basa.html') . $vTag,
        'bio_enzyme'             => asset('simlab-engines/biologi-enzim-katalase.html') . $vTag,
        'math_derivative'        => asset('simlab-engines/matematika-turunan-garis-singgung.html') . $vTag,
        'math_sequences'         => asset('simlab-engines/matematika-barisan-deret.html') . $vTag,
        'vokasi_caliper'         => asset('simlab-engines/vokasi-jangka-sorong-mikrometer.html') . $vTag,
        'geo_tectonics'          => asset('simlab-engines/ips-tektonik-gempa.html') . $vTag,
        'physics_faraday'        => asset('simlab-engines/fisika-induksi-faraday.html') . $vTag,
        'physics_pascal'         => asset('simlab-engines/fisika-tekanan-hidrolik.html') . $vTag,
        'chem_voltaic'           => asset('simlab-engines/kimia-sel-volta.html') . $vTag,
        'bio_respiration'        => asset('simlab-engines/biologi-pernapasan-paru.html') . $vTag,
        'math_linear_prog'       => asset('simlab-engines/matematika-program-linear.html') . $vTag,
        'math_vectors'           => asset('simlab-engines/matematika-vektor-2d.html') . $vTag,
        'vokasi_electronics'     => asset('simlab-engines/vokasi-elektronika-komponen.html') . $vTag,
        'vokasi_gears'           => asset('simlab-engines/vokasi-transmisi-gear.html') . $vTag,
        'econ_accounting'        => asset('simlab-engines/ips-persamaan-akuntansi.html') . $vTag,
        'ptn_reasoning'          => asset('simlab-engines/ptn-penalaran-umum-hots.html') . $vTag,
        'physics_kepler'         => asset('simlab-engines/fisika-orbit-kepler.html') . $vTag,
        'physics_thermo_pv'      => asset('simlab-engines/fisika-termodinamika-pv.html') . $vTag,
        'physics_bernoulli'      => asset('simlab-engines/fisika-fluida-bernoulli.html') . $vTag,
        'physics_collision'      => asset('simlab-engines/fisika-momentum-tumbukan.html') . $vTag,
        'physics_quantum_decay'  => asset('simlab-engines/fisika-kuantum-radioaktif.html') . $vTag,
        'bio_protein_synthesis'  => asset('simlab-engines/biologi-sintesis-protein.html') . $vTag,
        'chem_equilibrium'       => asset('simlab-engines/kimia-kesetimbangan-lechatelier.html') . $vTag,
        'chem_vsepr'             => asset('simlab-engines/kimia-bentuk-molekul-vsepr.html') . $vTag,
        'math_exp_matrix'        => asset('simlab-engines/matematika-eksponen-logaritma-matriks.html') . $vTag,
        'ptn_spatial_3d'         => asset('simlab-engines/ptn-spasial-figural-3d.html') . $vTag,
        'lang_indo_discourse'    => asset('simlab-engines/ips-literasi-wacana-snbt.html') . $vTag,
        'lang_english_snbt'      => asset('simlab-engines/ips-literasi-inggris-snbt.html') . $vTag,
    ];

    $rooms = [
        'fisika' => [
            'code'       => 'LAB-01',
            'floor'      => 'Lantai 1 · Sayap Timur',
            'name'       => 'Lab IPA & Fisika',
            'sub'        => 'Mekanika, Optika, Fluida, Gelombang & Listrik',
            'icon'       => 'fa-atom',
            'color'      => '#0369a1',
            'prof_name'  => 'Prof. Dr. Sanohugo Zega, M.Si.',
            'prof_title' => 'Kepala Laboratorium Fisika & Optika',
            'prof_photo' => asset('images/simlab-professors/prof-fisika.webp'),
            'prof_quote' => 'Uji hukum alam melalui pengukuran variabel langsung—dari lintasan proyektil dan lensa optik hingga medan listrik.',
            'equipment'  => ['Meja Optika Lensa', 'Osiloskop Doppler', 'Generator Medan Listrik', 'Lift Hukum Newton'],
        ],
        'kimia_biologi' => [
            'code'       => 'LAB-02',
            'floor'      => 'Lantai 1 · Sayap Barat',
            'name'       => 'Lab Kimia & Biologi',
            'sub'        => 'Reaksi Molekul, pH, Osmosis Sel & Saraf',
            'icon'       => 'fa-flask',
            'color'      => '#047857',
            'prof_name'  => 'Prof. Dr. Tafaeri Telaumbanua, Ph.D.',
            'prof_title' => 'Kepala Laboratorium Biokimia & Sel',
            'prof_photo' => asset('images/simlab-professors/prof-kimia-biologi.webp'),
            'prof_quote' => 'Amati reaksi stoikiometri, derajat keasaman pH, osmosis membran sel, genetika Mendel, dan penjalaran impuls saraf.',
            'equipment'  => ['Reaktor Stoikiometri', 'pH Meter & Lakmus', 'Mikroskop Osmosis Sel', 'Simulator Sistem Saraf'],
        ],
        'matematika' => [
            'code'       => 'LAB-03',
            'floor'      => 'Lantai 2 · Sayap Timur',
            'name'       => 'Lab Matematika',
            'sub'        => 'Grafik, Trigonometri, Integral & Peluang',
            'icon'       => 'fa-square-root-variable',
            'color'      => '#3730a3',
            'prof_name'  => 'Prof. Dr. Talifusi Zebua, M.Mat.',
            'prof_title' => 'Kepala Laboratorium Analisis & Geometri',
            'prof_photo' => asset('images/simlab-professors/prof-matematika.webp'),
            'prof_quote' => 'Pahami hubungan aljabar dan geometri secara visual lewat kurva kuadrat, lingkaran satuan, integral Riemann, dan peluang.',
            'equipment'  => ['Plotter Parabola', 'Lingkaran Trigonometri', 'Mesin Integral Riemann', 'Matriks Ruang Sampel'],
        ],
        'vokasi' => [
            'code'       => 'LAB-04',
            'floor'      => 'Lantai 1 · Gedung Bengkel',
            'name'       => 'Bengkel Vokasi (SMK)',
            'sub'        => 'Otomotif 4-Tak, CAD DPIB, Jaringan LAN & PLC',
            'icon'       => 'fa-gears',
            'color'      => '#c2410c',
            'prof_name'  => 'Ir. Samaeri Harefa, M.T.',
            'prof_title' => 'Kepala Bengkel Rekayasa Vokasi',
            'prof_photo' => asset('images/simlab-professors/prof-vokasi.webp'),
            'prof_quote' => 'Latihan standar kerja industri: diagnosis mesin 4-tak, topologi & subnetting jaringan, denah CAD DPIB, dan panel DOL motor 3-fasa.',
            'equipment'  => ['Engine Stand 4-Tak', 'Rack Topologi LAN', 'Meja Gambar CAD DPIB', 'Panel Relay DOL 3-Fasa'],
        ],
        'ips_bahasa' => [
            'code'       => 'LAB-05',
            'floor'      => 'Lantai 2 · Sayap Barat',
            'name'       => 'Lab IPS & Bahasa',
            'sub'        => 'Ekonomi Pasar, Inflasi, Sosiologi & Bahasa',
            'icon'       => 'fa-earth-asia',
            'color'      => '#9f1239',
            'prof_name'  => 'Prof. Dr. Saradodo Mendrofa, M.Ec.',
            'prof_title' => 'Kepala Laboratorium Sosial, Ekonomi & Bahasa',
            'prof_photo' => asset('images/simlab-professors/prof-ips-bahasa.webp'),
            'prof_quote' => 'Analisis keseimbangan pasar, dampak inflasi terhadap daya beli riil, dinamika norma sosial, hingga struktur sintaksis lintas bahasa.',
            'equipment'  => ['Kurva Pasar Pe-Qe', 'Proyeksi Inflasi Riil', 'Model Bunga Majemuk', 'Peta Agen Sosiologi'],
        ],
        'olimpiade' => [
            'code'       => 'LAB-06',
            'floor'      => 'Lantai 3 · Menara Prestasi',
            'name'       => 'Arena Olimpiade & PTN',
            'sub'        => '5 Soal Dunia & 35 Soal Cepat SNBT/UTBK',
            'icon'       => 'fa-trophy',
            'color'      => '#b45309',
            'prof_name'  => 'Prof. Dr. Orudua Lase, M.Sc.',
            'prof_title' => 'Kepala Pusat Pembinaan Olimpiade & PTN',
            'prof_photo' => asset('images/simlab-professors/prof-olimpiade.webp'),
            'prof_quote' => 'Uji ketajaman nalar dan kecepatan di bawah batas waktu menggunakan soal Olimpiade Sains Internasional dan bank soal SNBT/UTBK.',
            'equipment'  => ['Arena 5 Soal Olimpiade', 'Paket SNBT 15 Soal Inti', 'Paket 20 Soal Lanjutan', 'Stopwatch Strategi Cepat'],
        ],
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
            'scenario'         => $lab->learning_scenario,
        ];
    })->values();
@endphp
<body class="antialiased selection:bg-sky-100 selection:text-sky-950" x-data="labFloorplanApp()">
<!--
  THESIS: Kompleks Laboratorium Riset & Vokasi PembdaHUB ditata sebagai gedung institut riset arsitektural di atas kertas terang (#f6f7f8) dengan konsol instrumen berbatas garis 1px presisi, menolak tumpukan kartu dekoratif, gradien pelangi, maupun avatar kartun.
  OWN-WORLD: Surface putih (#ffffff) & paper gray (#f6f7f8), hairline border #d8dee6, tipografi Public Sans + JetBrains Mono tabular-nums, potret foto realistis 6 Profesor Nias, dan aksen warna fungsional per sayap disiplin ilmu.
  STORY: Pengguna memasuki direktori gedung 6 ruangan ber-kepala lab Profesor Nias, memilih ruangan, dan langsung mengoperasikan meja praktikum, SOP, teori/rumus, serta uji pemahaman HOTS dalam satu konsol terpadu.
  FIRST VIEWPORT: Header 56px datar, ringkasan arsitektural direktori gedung, dan grid 6 pintu ruangan lab berbatas garis 1px menampilkan foto realistis Profesor Nias beserta inventaris instrumennya.
  FORM: Architectural Research Directory + Split-Screen Instrument Console (mode Operate).
  FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, and DESIGN.md
-->

    <!-- Top Command Bar (Fasad Gelap Institusional, 56px, border bawah tegas) -->
    <header class="sticky top-0 z-40 border-b border-slate-800 bg-slate-900 text-white">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0 text-white hover:text-sky-300 transition">
                    <span class="w-8 h-8 rounded-md bg-sky-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
                        <i class="fas fa-microscope"></i>
                    </span>
                    <div class="min-w-0">
                        <span class="font-extrabold tracking-tight text-sm block leading-none">PembdaHUB Kompleks Laboratorium</span>
                        <span class="text-[11px] text-slate-300 font-medium hidden sm:block mt-0.5">Institut Riset &amp; Praktikum Vokasi Nias</span>
                    </div>
                </a>

                <span class="hidden md:inline-flex items-center gap-1.5 pl-3 border-l border-slate-700 text-xs font-semibold text-slate-200">
                    <span class="w-2 h-2 rounded-full {{ $isInstructor ? 'bg-emerald-400' : 'bg-sky-400' }}"></span>
                    {{ $isInstructor ? 'Mode Instruktur' : 'Mode Praktikan' }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative hidden sm:block w-56 lg:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery"
                           @input="if(searchQuery.trim() !== '') { activeRoom = 'all'; viewMode = 'inside'; }"
                           placeholder="Cari fenomena atau alat..."
                           class="w-full pl-8 pr-3 py-1.5 rounded-md bg-slate-800 border border-slate-700 text-xs text-white placeholder-slate-400 focus:bg-slate-950 focus:outline-none focus:border-sky-500 transition">
                </div>

                <button type="button"
                        @click="viewMode === 'lobby' ? enterRoom(activeRoom === 'all' ? 'fisika' : activeRoom) : backToLobby()"
                        class="px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 text-xs font-bold transition inline-flex items-center gap-1.5">
                    <i class="fas" :class="viewMode === 'lobby' ? 'fa-door-open text-sky-400' : 'fa-building text-sky-400'"></i>
                    <span class="hidden md:inline" x-text="viewMode === 'lobby' ? 'Masuk Meja Riset' : 'Direktori Gedung'"></span>
                </button>

                @if($isInstructor)
                <button type="button" @click="openAiModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold transition">
                    <i class="fas fa-plus"></i>
                    <span class="hidden sm:inline">Buat Simulator AI</span>
                </button>
                @endif

                @auth
                <a href="{{ url('/dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-100 border border-slate-700 text-xs font-bold transition">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Portal</span>
                </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-5">

        <!-- BAGIAN 1: DIREKTORI GEDUNG LABORATORIUM (6 RUANGAN DISIPLIN ILMU + PROFESOR KEPALA LAB) -->
        <section x-show="viewMode === 'lobby'" x-transition class="space-y-4">
            <!-- Header Direktori Arsitektural + Fasad Gedung Laboratorium PembdaHUB -->
            <div class="bg-white/95 backdrop-blur-[2px] border border-slate-300 rounded-lg overflow-hidden">
                <div class="h-40 sm:h-52 w-full relative overflow-hidden border-b border-slate-300 bg-slate-900">
                    <img src="{{ asset('images/simlab-building-bg.webp') }}?v=20261127f"
                         alt="Gedung Laboratorium PembdaHUB"
                         class="w-full h-full object-cover object-center opacity-90">
                    <div class="absolute bottom-3 left-4 bg-slate-900/90 text-white border border-slate-700 px-3 py-1.5 rounded text-[11px] font-mono-lab font-bold">
                        GEDUNG LABORATORIUM DISIPLIN ILMU &amp; VOKASI · YAYASAN PERGURUAN PEMBDA NIAS
                    </div>
                </div>
                <div class="px-5 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="max-w-3xl">
                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">
                            Gedung Laboratorium Disiplin Ilmu PembdaHUB
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed">
                            Enam sayap laboratorium khusus yang masing-masing dipimpin oleh Profesor Kepala Lab beserta instrumen praktikum interaktif, SOP kerja, landasan teori, dan uji pemahaman HOTS.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 shrink-0">
                        <div class="flex items-center gap-4 font-mono-lab text-xs text-slate-700 pr-2">
                            <div><span class="font-bold text-slate-900 text-sm">6</span> Ruangan Lab</div>
                            <div class="h-4 w-px bg-slate-300"></div>
                            <div><span class="font-bold text-slate-900 text-sm">{{ $virtualLabs->count() }}</span> Instrumen Aktif</div>
                            <div class="h-4 w-px bg-slate-300"></div>
                            <div><span class="font-bold text-emerald-700 text-sm" x-text="totalMasteredCount"></span> Tuntas</div>
                        </div>
                        <button type="button" @click="activeRoom = 'all'; viewMode = 'inside'"
                                class="px-4 py-2 rounded-md bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition inline-flex items-center gap-2">
                            <span>Buka Indeks Semua Instrumen</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Grid Arsitektural 6 Pintu Ruangan (Hairline Grid 1px + Pita Warna Wayfinding Solid) -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-px bg-slate-300 border border-slate-300 rounded-lg overflow-hidden">
                @foreach($rooms as $rKey => $r)
                @php $rCount = $virtualLabs->filter(fn($v) => $v->discipline_key === $rKey)->count(); @endphp
                <article @click="enterRoom('{{ $rKey }}')"
                         style="border-top: 4px solid {{ $r['color'] }};"
                         class="group cursor-pointer bg-white/95 hover:bg-white transition-colors flex flex-col justify-between">
                    <div class="p-5 space-y-4">
                        <!-- Baris Kode Ruangan & Kapasitas -->
                        <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-200">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono-lab font-bold text-white" style="background: {{ $r['color'] }};">{{ $r['code'] }}</span>
                                <span class="text-xs text-slate-600 font-semibold">{{ $r['floor'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 font-mono-lab text-xs font-bold">
                                <span class="text-slate-700">{{ $rCount }} Alat</span>
                                <span class="text-slate-300">|</span>
                                <span class="text-emerald-700" x-text="roomMasteredCount('{{ $rKey }}') + '/{{ $rCount }} Tuntas'"></span>
                            </div>
                        </div>

                        <!-- Potret Realistis Profesor Kepala Lab + Identitas Ruangan -->
                        <div class="flex items-start gap-4">
                            <img src="{{ $r['prof_photo'] }}"
                                 alt="{{ $r['prof_name'] }}"
                                 loading="lazy"
                                 class="w-20 h-20 rounded-lg object-cover border border-slate-300 shrink-0 bg-slate-100">
                            <div class="min-w-0 flex-1">
                                <h2 class="text-base font-extrabold text-slate-900 leading-snug group-hover:text-sky-800 transition-colors">
                                    {{ $r['name'] }}
                                </h2>
                                <div class="text-xs font-bold mt-1" style="color: {{ $r['color'] }};">{{ $r['prof_name'] }}</div>
                                <div class="text-[11px] font-medium text-slate-500">{{ $r['prof_title'] }}</div>
                                <p class="text-xs text-slate-600 mt-2 leading-relaxed line-clamp-2">{{ $r['prof_quote'] }}</p>
                            </div>
                        </div>

                        <!-- Inventaris Peralatan Ruangan (Tipografi Bersih) -->
                        <div class="pt-3 border-t border-slate-200 text-[11px] text-slate-600 leading-relaxed">
                            <span class="font-bold text-slate-900">Instrumen:</span>
                            {{ implode(' · ', $r['equipment']) }}
                        </div>
                    </div>

                    <!-- Bar Masuk Ruangan -->
                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs font-bold text-slate-700 group-hover:text-slate-950">
                        <span>Masuki Ruangan Laboratorium</span>
                        <span class="inline-flex items-center gap-1.5 font-mono-lab group-hover:translate-x-0.5 transition-transform" style="color: {{ $r['color'] }};">
                            BUKA PINTU <i class="fas fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </article>
                @endforeach
            </div>
        </section>

        <!-- BAGIAN 2: DI DALAM RUANGAN LAB (STRIP KEPALA LAB + KONSOL INSTRUMEN SPLIT-SCREEN) -->
        <section x-show="viewMode === 'inside'" x-cloak class="space-y-4">

            <!-- Bar Koridor Ruangan & Identitas Kepala Lab -->
            <div :style="currentRoomMeta ? 'border-top: 4px solid ' + currentRoomMeta.color : 'border-top: 4px solid #0f172a'"
                 class="bg-white border border-slate-300 rounded-lg px-4 py-3 flex flex-col xl:flex-row xl:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="backToLobby()"
                            class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition inline-flex items-center gap-1.5">
                        <i class="fas fa-arrow-left text-[10px]"></i>
                        <span>Direktori Gedung</span>
                    </button>
                    <div class="h-5 w-px bg-slate-300 hidden sm:block"></div>
                    <div class="flex flex-wrap items-center gap-1">
                        @foreach($rooms as $rKey => $r)
                        <button type="button" @click="selectRoom('{{ $rKey }}')"
                                :class="activeRoom === '{{ $rKey }}' ? 'text-white' : 'bg-slate-50 text-slate-700 border-slate-300 hover:bg-slate-100'"
                                :style="activeRoom === '{{ $rKey }}' ? 'background: {{ $r['color'] }}; border-color: {{ $r['color'] }};' : ''"
                                class="px-2.5 py-1.5 rounded-md border text-xs font-bold transition inline-flex items-center gap-1.5 font-mono-lab">
                            <span>{{ $r['code'] }}</span>
                        </button>
                        @endforeach
                        <button type="button" @click="activeRoom = 'all'"
                                :class="activeRoom === 'all' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-300 hover:bg-slate-100'"
                                class="px-2.5 py-1.5 rounded-md border text-xs font-bold transition">
                            Semua ({{ $virtualLabs->count() }})
                        </button>
                    </div>
                </div>

                <!-- Pendampingan Kepala Lab Ruangan Aktif -->
                <template x-if="currentRoomMeta">
                    <div class="flex items-center gap-3 pl-0 xl:pl-4 xl:border-l border-slate-200 min-w-0">
                        <img :src="currentRoomMeta.prof_photo"
                             :alt="currentRoomMeta.prof_name"
                             class="w-10 h-10 rounded-md object-cover border border-slate-300 shrink-0 bg-slate-100">
                        <div class="min-w-0">
                            <div class="text-xs font-extrabold text-slate-900 truncate" x-text="currentRoomMeta.prof_name"></div>
                            <div class="text-[11px] text-slate-500 truncate" x-text="currentRoomMeta.name + ' · ' + currentRoomMeta.prof_title"></div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Konsol Instrumen Terpadu (1 Bingkai, Pembatas Garis 1px, Tanpa Nested Cards) -->
            <div class="bg-white border border-slate-300 rounded-lg overflow-hidden grid grid-cols-1 lg:grid-cols-12 items-stretch">

                <!-- KOLOM KIRI: INDEKS INSTRUMEN RUANGAN -->
                <aside class="lg:col-span-4 xl:col-span-3 border-b lg:border-b-0 lg:border-r border-slate-300 bg-slate-50/70 flex flex-col">
                    <div class="px-4 py-3 border-b border-slate-200 bg-white flex items-center justify-between gap-2">
                        <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">
                            Daftar Instrumen
                        </h2>
                        <div class="flex items-center gap-2 font-mono-lab text-[11px] font-bold">
                            <span class="text-emerald-700" x-text="roomMasteredCount(activeRoom) + ' Tuntas'"></span>
                            <span class="text-slate-300">/</span>
                            <span class="text-slate-600" x-text="filteredLabs.length + ' Unit'"></span>
                        </div>
                    </div>

                    <!-- Search Mobile -->
                    <div class="p-3 border-b border-slate-200 sm:hidden bg-white">
                        <input type="text" x-model="searchQuery" placeholder="Cari fenomena atau alat..."
                               class="w-full px-3 py-1.5 rounded-md bg-slate-50 border border-slate-300 text-xs text-slate-900">
                    </div>

                    <div class="divide-y divide-slate-200 max-h-[760px] overflow-y-auto flex-1">
                        <template x-for="(lab, idx) in filteredLabs" :key="lab.id">
                            <button type="button" @click="selectLab(lab)"
                                    :class="selectedLab && selectedLab.id === lab.id ? 'bg-white text-slate-950' : 'hover:bg-white/80 text-slate-700'"
                                    class="w-full text-left px-4 py-3 transition-colors flex items-start gap-3">
                                <span :class="selectedLab && selectedLab.id === lab.id ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700'"
                                      class="font-mono-lab text-[11px] font-extrabold px-1.5 py-0.5 rounded shrink-0 mt-0.5"
                                      x-text="String(idx + 1).padStart(2, '0')"></span>
                                <div class="min-w-0 flex-1 flex flex-col gap-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] font-semibold text-slate-500 truncate" x-text="lab.subject_name"></span>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <span x-show="isMastered(lab.slug)" class="font-mono-lab text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded">✓ TUNTAS</span>
                                            <span x-show="selectedLab && selectedLab.id === lab.id" class="font-mono-lab text-[10px] font-bold text-sky-700">● AKTIF</span>
                                        </div>
                                    </div>
                                    <div class="text-xs sm:text-sm font-bold text-slate-900 leading-snug" x-text="lab.title"></div>
                                    <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed" x-text="lab.description"></p>
                                </div>
                            </button>
                        </template>
                        <div x-show="filteredLabs.length === 0" class="p-6 text-center text-xs text-slate-500">
                            Tidak ada instrumen yang cocok dengan pencarian Anda.
                        </div>
                    </div>
                </aside>

                <!-- KOLOM KANAN: MEJA PRAKTIKUM & KONTEN AKADEMIK -->
                <div class="lg:col-span-8 xl:col-span-9 flex flex-col min-w-0 bg-white">
                    <!-- Header Instrumen Aktif -->
                    <div class="px-5 py-4 border-b border-slate-200 flex flex-col xl:flex-row xl:items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 font-semibold">
                                <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-mono-lab text-[11px] font-bold" x-text="'NO. ' + selectedLabNumber"></span>
                                <span x-show="selectedLab && isMastered(selectedLab.slug)" class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono-lab text-[11px] font-bold">LULUS PRAKTIKUM ✓</span>
                                <span class="font-mono-lab font-bold text-slate-800" x-text="selectedLab?.discipline_label"></span>
                                <span>·</span>
                                <span x-text="selectedLab?.subject_name"></span>
                            </div>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight mt-0.5" x-text="selectedLab?.title"></h2>
                            <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed" x-text="selectedLab?.description"></p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            @if($isInstructor)
                            <button type="button" @click="copySelectedUrl()"
                                    class="px-3 py-1.5 rounded-md bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold transition inline-flex items-center gap-1.5">
                                <i class="fas fa-link text-slate-500"></i>
                                <span x-text="copied ? 'Tersalin' : 'Salin Link LMS'"></span>
                            </button>
                            <button type="button" x-show="selectedLab?.can_delete" @click="openEditModal(selectedLab)"
                                    class="px-3 py-1.5 rounded-md bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold transition inline-flex items-center gap-1">
                                <i class="fas fa-pen-to-square text-slate-500"></i>
                                <span>Edit</span>
                            </button>
                            <button type="button" x-show="selectedLab?.can_delete" @click="deleteLab(selectedLab.id, selectedLab.title)"
                                    class="px-3 py-1.5 rounded-md bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 text-xs font-bold transition inline-flex items-center gap-1">
                                <i class="fas fa-trash-can"></i>
                                <span>Hapus</span>
                            </button>
                            @endif
                            <a :href="selectedLab?.full_url"
                               class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition inline-flex items-center gap-1.5">
                                <i class="fas fa-expand text-[11px]"></i>
                                <span>Layar Penuh</span>
                            </a>
                        </div>
                    </div>

                    <!-- Tab Bar Datar Presisi -->
                    <div class="px-5 border-b border-slate-200 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-6">
                            <button type="button" @click="workTab = 'sim'"
                                    :class="workTab === 'sim' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                                    class="py-3 border-b-2 text-xs sm:text-sm font-bold transition inline-flex items-center gap-2">
                                <i class="fas fa-sliders"></i> Meja Praktikum
                            </button>
                            <button type="button" @click="workTab = 'sop'"
                                    :class="workTab === 'sop' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                                    class="py-3 border-b-2 text-xs sm:text-sm font-bold transition inline-flex items-center gap-2">
                                <i class="fas fa-list-check"></i> Skenario &amp; SOP (<span x-text="checkedStepsCount + '/' + (selectedLab?.sop_steps?.length || 0)"></span>)
                            </button>
                            <button type="button" @click="workTab = 'teori'"
                                    :class="workTab === 'teori' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                                    class="py-3 border-b-2 text-xs sm:text-sm font-bold transition inline-flex items-center gap-2">
                                <i class="fas fa-book-open"></i> Teori &amp; Rumus
                            </button>
                            <button type="button" @click="workTab = 'soal'"
                                    :class="workTab === 'soal' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                                    class="py-3 border-b-2 text-xs sm:text-sm font-bold transition inline-flex items-center gap-2">
                                <i class="fas fa-clipboard-check"></i> Verifikasi Keluar Lab
                            </button>
                        </div>

                        <button x-show="workTab === 'sim'" type="button" @click="showScenario = !showScenario"
                                class="py-1.5 px-2.5 rounded border border-slate-300 bg-white hover:bg-slate-100 text-[11px] font-bold text-slate-700 transition inline-flex items-center gap-1.5">
                            <i class="fas" :class="showScenario ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            <span x-text="showScenario ? 'Sembunyikan Panduan' : 'Tampilkan Skenario 4 Tahap'"></span>
                        </button>
                    </div>

                    <!-- TAB 1: MEJA PRAKTIKUM LIVE + LEMBAR PANDUAN MASUK LAB -->
                    <div x-show="workTab === 'sim'" class="flex-1 bg-white">
                        <!-- Lembar Kerja 4 Tahap (Jelas Tujuan, Aktivitas & Fokus Pengamatan sebelum eksperimen) -->
                        <div x-show="showScenario && selectedLab?.scenario" class="border-b border-slate-300 bg-slate-50">
                            <div class="px-5 py-2.5 border-b border-slate-200 bg-white flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2 text-xs font-extrabold text-slate-800">
                                    <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-mono-lab text-[10px]">ALUR BELAJAR TERPANDU</span>
                                    <span>Ikuti Tahap 1–3 di meja alat, lalu selesaikan Tahap 4 sebelum keluar lab</span>
                                </div>
                                <div class="flex items-center gap-3 text-[11px] font-mono-lab font-bold">
                                    <span class="text-slate-600" x-text="checkedStepsCount + '/' + (selectedLab?.scenario?.actions?.length || 0) + ' Langkah Diceklis'"></span>
                                    <button type="button" @click="workTab = 'soal'" class="px-2.5 py-1 rounded bg-emerald-700 hover:bg-emerald-800 text-white font-sans font-bold transition">
                                        Uji Pemahaman &amp; Keluar Lab <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200">
                                <!-- Tahap 1: Tujuan Belajar -->
                                <div class="p-4 space-y-1.5 bg-white/80">
                                    <div class="text-[11px] font-mono-lab font-extrabold uppercase tracking-wider text-sky-800">1. Tujuan Belajar (Masuk Lab)</div>
                                    <p class="text-xs text-slate-800 leading-relaxed" x-text="selectedLab?.scenario?.goal"></p>
                                    <div x-show="selectedLab?.scenario?.formula" class="pt-1 font-mono-lab text-[11px] font-bold text-slate-900 bg-slate-100 border border-slate-200 rounded px-2 py-1" x-text="selectedLab?.scenario?.formula"></div>
                                </div>

                                <!-- Tahap 2: Apa & Bagaimana Dilakukan -->
                                <div class="p-4 space-y-1.5 bg-white/80">
                                    <div class="text-[11px] font-mono-lab font-extrabold uppercase tracking-wider text-slate-800">2. Apa &amp; Bagaimana Dilakukan</div>
                                    <div class="space-y-1.5">
                                        <template x-for="(step, idx) in (selectedLab?.scenario?.actions || [])" :key="idx">
                                            <label class="flex items-start gap-2 cursor-pointer text-xs text-slate-700 leading-snug hover:text-slate-950">
                                                <input type="checkbox" :checked="isStepChecked(idx)" @change="toggleStep(idx)" class="mt-0.5 rounded border-slate-300 text-slate-900">
                                                <span :class="isStepChecked(idx) ? 'line-through text-slate-400' : ''">
                                                    <strong class="font-mono-lab text-slate-900" x-text="'0' + (idx + 1) + '.'"></strong>
                                                    <span x-text="step"></span>
                                                </span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <!-- Tahap 3: Fokus Pengamatan -->
                                <div class="p-4 space-y-1.5 bg-white/80 flex flex-col justify-between">
                                    <div class="space-y-1.5">
                                        <div class="text-[11px] font-mono-lab font-extrabold uppercase tracking-wider text-amber-800">3. Apa yang Diamati &amp; Dicatat</div>
                                        <p class="text-xs text-slate-800 leading-relaxed" x-text="selectedLab?.scenario?.observation_focus"></p>
                                    </div>
                                    <div class="pt-2 text-[11px] text-slate-500">
                                        <span class="font-bold text-slate-700">Target Keluar:</span> Selesaikan catatan &amp; pertanyaan di tab <strong>Verifikasi Keluar Lab</strong>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <template x-if="selectedLab?.engine_url">
                            <iframe :src="selectedLab.engine_url" class="w-full border-0 block bg-white" style="height: 740px;"></iframe>
                        </template>
                        <template x-if="!selectedLab?.engine_url && selectedLab?.custom_html">
                            <iframe sandbox="allow-scripts" :srcdoc="selectedLab.custom_html" class="w-full border-0 block bg-white" style="height: 700px;"></iframe>
                        </template>
                    </div>

                    <!-- TAB 2: PROSEDUR SOP LENGKAP -->
                    <div x-show="workTab === 'sop'" x-cloak class="p-6 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Skenario Kerja &amp; Prosedur Operasional Standar</h3>
                                <p class="text-xs text-slate-600 mt-0.5" x-text="selectedLab?.scenario?.goal"></p>
                            </div>
                            <span class="font-mono-lab text-xs font-bold text-slate-700" x-text="checkedStepsCount + '/' + (selectedLab?.sop_steps?.length || 0) + ' Selesai'"></span>
                        </div>
                        <div class="divide-y divide-slate-200 border border-slate-200 rounded-md">
                            <template x-for="(step, idx) in (selectedLab?.sop_steps || [])" :key="idx">
                                <label class="flex items-start gap-3 p-4 bg-white hover:bg-slate-50 cursor-pointer transition">
                                    <input type="checkbox" :checked="isStepChecked(idx)" @change="toggleStep(idx)" class="mt-0.5 rounded border-slate-300 text-slate-900">
                                    <span class="text-xs sm:text-sm text-slate-800 leading-relaxed">
                                        <strong class="font-mono-lab text-slate-900 mr-1" x-text="'0' + (idx + 1) + '.'"></strong>
                                        <span x-text="step"></span>
                                    </span>
                                </label>
                            </template>
                        </div>
                        <div class="p-4 rounded-md bg-slate-50 border border-slate-200 text-xs text-slate-800 leading-relaxed">
                            <strong class="text-slate-900">Fokus Pengamatan Utama:</strong><br>
                            <span x-text="selectedLab?.scenario?.observation_focus"></span>
                        </div>
                    </div>

                    <!-- TAB 3: TEORI, HUKUM ALAM & RUMUS -->
                    <div x-show="workTab === 'teori'" x-cloak class="p-6 space-y-4">
                        <div class="pb-4 border-b border-slate-200">
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hukum &amp; Prinsip Ilmu Pengetahuan</div>
                            <h3 class="text-lg font-extrabold text-slate-900 mt-1" x-text="selectedLab?.tc?.law"></h3>
                            <p class="text-sm text-slate-700 leading-relaxed mt-2" x-text="selectedLab?.tc?.theory"></p>
                        </div>
                        <div class="p-4 rounded-md bg-slate-900 text-slate-100 font-mono-lab text-sm sm:text-base font-bold" x-text="selectedLab?.tc?.formula"></div>
                        <div class="p-4 rounded-md bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-700 leading-relaxed">
                            <strong class="text-slate-900">Keterangan Simbol &amp; Satuan:</strong><br>
                            <span x-text="selectedLab?.tc?.variables"></span>
                        </div>
                    </div>

                    <!-- TAB 4: VERIFIKASI PEMAHAMAN KELUAR LAB (EXIT TICKET & PENGETAHUAN BARU) -->
                    <div x-show="workTab === 'soal'" x-cloak class="p-6 space-y-5">
                        <div class="p-4 rounded-md bg-slate-50 border border-slate-200 space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <label class="text-xs font-extrabold uppercase tracking-wider text-slate-700">1. Catatan Hasil Pengamatan di Lab</label>
                                <span class="text-[11px] font-mono-lab text-slate-500">Tersimpan otomatis di perangkat ini</span>
                            </div>
                            <textarea rows="2" x-model="studentNote" @input="saveNote()"
                                      placeholder="Tuliskan pola atau perubahan besaran yang kamu amati saat mengubah slider/variabel..."
                                      class="w-full rounded-md bg-white border border-slate-300 p-2.5 text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-slate-900"></textarea>
                        </div>

                        <div class="p-4 rounded-md bg-slate-50 border border-slate-200">
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">2. Pembuktian Pemahaman Konsep (Syarat Keluar Lab)</div>
                            <p class="text-sm sm:text-base font-bold text-slate-900 leading-relaxed mt-1.5" x-text="selectedLab?.tc?.challenge_q"></p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            <template x-for="(opt, oIdx) in (selectedLab?.tc?.options || [])" :key="oIdx">
                                <button type="button"
                                        @click="answerChallenge(oIdx)"
                                        :class="checkedAns
                                            ? (oIdx === selectedLab.tc.answer_idx
                                                ? 'bg-emerald-50 border-emerald-600 text-emerald-950 font-bold'
                                                : (chosenOpt === oIdx ? 'bg-rose-50 border-rose-500 text-rose-900' : 'bg-white border-slate-200 text-neutral-500'))
                                            : 'bg-white hover:bg-slate-50 border-slate-300 text-slate-800'"
                                        class="w-full text-left px-4 py-3 rounded-md border text-xs sm:text-sm transition flex items-center justify-between gap-2">
                                    <span x-text="String.fromCharCode(65 + oIdx) + '. ' + opt"></span>
                                    <i x-show="checkedAns && oIdx === selectedLab.tc.answer_idx" class="fas fa-check text-emerald-700"></i>
                                </button>
                            </template>
                        </div>

                        <div x-show="checkedAns && !isCorrectAns" x-transition class="p-4 rounded-md bg-rose-50 border border-rose-300 text-xs sm:text-sm text-rose-950 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <strong>Belum Tepat — Uji Kembali di Meja Praktikum:</strong>
                                <span x-text="selectedLab?.tc?.solution"></span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" @click="workTab = 'sim'" class="px-3 py-1.5 rounded bg-white border border-rose-300 text-rose-800 text-xs font-bold">Kembali ke Meja Alat</button>
                                <button type="button" @click="retryChallenge()" class="px-3 py-1.5 rounded bg-rose-700 text-white text-xs font-bold">Ulangi Pertanyaan</button>
                            </div>
                        </div>

                        <div x-show="(checkedAns && isCorrectAns) || (selectedLab && isMastered(selectedLab.slug))" x-transition class="p-5 rounded-md bg-emerald-50 border border-emerald-400 space-y-2.5 text-emerald-950">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-800">4. Intisari Pengetahuan Baru (Lulus Praktikum ✓)</span>
                                <span class="px-2 py-0.5 rounded bg-emerald-700 text-white font-mono-lab text-[10px] font-bold">KOMPETENSI TERVERIFIKASI</span>
                            </div>
                            <p class="text-xs sm:text-sm font-semibold leading-relaxed" x-text="selectedLab?.scenario?.expected_outcome"></p>
                            <div class="pt-2 border-t border-emerald-200 text-xs sm:text-sm leading-relaxed">
                                <strong>Kesimpulan Ilmiah &amp; Pembahasan:</strong><br>
                                <span x-text="selectedLab?.scenario?.key_takeaway"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal: Buat Simulator Baru dengan AI (Guru/Admin) -->
    @if($isInstructor)
    <div x-show="openAiModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.away="if(!isGenerating) openAiModal = false"
             class="w-full max-w-xl rounded-lg bg-white border border-slate-300 shadow-xl overflow-hidden text-slate-900">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Buat Simulator Lab Baru (AI Generator)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Hasilkan simulasi interaktif lengkap dengan kontrol variabel &amp; SOP</p>
                </div>
                <button type="button" @click="if(!isGenerating) openAiModal = false" class="text-slate-500 hover:text-slate-900 p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form @submit.prevent="submitAiLab" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                <div x-show="aiError" class="p-3 rounded-md bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold" x-text="aiError"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">1. Ruangan Lab Penempatan *</label>
                        <select x-model="form.room_key" required
                                class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900 font-semibold focus:outline-none focus:border-slate-900">
                            <option value="fisika">LAB-01: Lab IPA &amp; Fisika</option>
                            <option value="kimia_biologi">LAB-02: Lab Kimia &amp; Biologi</option>
                            <option value="matematika">LAB-03: Lab Matematika</option>
                            <option value="vokasi">LAB-04: Bengkel Vokasi (SMK)</option>
                            <option value="ips_bahasa">LAB-05: Lab IPS &amp; Bahasa</option>
                            <option value="olimpiade">LAB-06: Arena Olimpiade &amp; PTN</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Mata Pelajaran / Kejuruan</label>
                        <input type="text" x-model="form.subject_name" placeholder="Contoh: Fisika / TKR / Kimia / Biologi"
                               class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">2. Nama Simulator *</label>
                    <input type="text" x-model="form.title" required placeholder="Contoh: Simulator Tekanan Hidrostatis &amp; Bendungan Air"
                           class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Narasi Penjelasan Simulator *</label>
                    <textarea x-model="form.description" rows="2" required placeholder="Tuliskan pertanyaan pemantik atau fenomena nyata untuk siswa..."
                              class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"></textarea>
                </div>

                @if($teacherCourses->count() > 0)
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Hubungkan ke Course / Modul LMS (Opsional)</label>
                    <select x-model="form.course_id"
                            class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900">
                        <option value="">-- Tidak dihubungkan ke course tertentu --</option>
                        @foreach($teacherCourses as $c)
                        <option value="{{ $c->id }}">{{ $c->course_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">3. Instruksi Pembangkit Simulator untuk AI *</label>
                    <textarea x-model="form.prompt_spec" rows="4" required placeholder="Jelaskan objek yang digambar, slider variabel yang dapat diubah siswa, animasi yang terjadi, serta pembacaan angka hasilnya..."
                              class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-slate-900"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Jika instruksi belum dapat dipahami sebagai simulasi ilmiah, sistem akan meminta penjelasan ulang.</p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="openAiModal = false" :disabled="isGenerating"
                            class="px-4 py-2 rounded-md bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isGenerating"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-md bg-slate-900 hover:bg-slate-800 disabled:opacity-60 text-white text-xs font-bold transition">
                        <i class="fas" :class="isGenerating ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                        <span x-text="isGenerating ? 'Memvalidasi &amp; Merakit...' : 'Generate Simulator'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Simulator (Khusus Guru Pembuat / Admin) -->
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.away="if(!isSavingEdit) showEditModal = false"
             class="w-full max-w-lg rounded-lg bg-white border border-slate-300 shadow-xl overflow-hidden text-slate-900">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-900 text-base">Edit Simulator Saya</h3>
                <button type="button" @click="if(!isSavingEdit) showEditModal = false" class="text-slate-500 hover:text-slate-900 p-1"><i class="fas fa-times"></i></button>
            </div>
            <form @submit.prevent="submitEditLab" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ruangan Lab Penempatan</label>
                    <select x-model="editForm.room_key" required class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900">
                        <option value="fisika">LAB-01: Lab IPA &amp; Fisika</option>
                        <option value="kimia_biologi">LAB-02: Lab Kimia &amp; Biologi</option>
                        <option value="matematika">LAB-03: Lab Matematika</option>
                        <option value="vokasi">LAB-04: Bengkel Vokasi (SMK)</option>
                        <option value="ips_bahasa">LAB-05: Lab IPS &amp; Bahasa</option>
                        <option value="olimpiade">LAB-06: Arena Olimpiade &amp; PTN</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Simulator</label>
                    <input type="text" x-model="editForm.title" required class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Mata Pelajaran</label>
                    <input type="text" x-model="editForm.subject_name" class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Narasi Penjelasan Simulator</label>
                    <textarea x-model="editForm.description" rows="3" required class="w-full px-3 py-2 rounded-md bg-white border border-slate-300 text-sm text-slate-900"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-md bg-white border border-slate-300 text-slate-700 text-xs font-bold">Batal</button>
                    <button type="submit" :disabled="isSavingEdit" class="px-5 py-2 rounded-md bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold">
                        <span x-text="isSavingEdit ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        function labFloorplanApp() {
            const labs = @json($labsPayload);
            const roomsMeta = @json($rooms);
            const params = new URLSearchParams(window.location.search);
            const initRoomParam = params.get('room');
            const initSlug = params.get('lab');
            const initRoom = initRoomParam || 'fisika';
            const initLab = (initSlug && labs.find(l => l.slug === initSlug))
                || labs.find(l => l.discipline_key === initRoom)
                || labs[0]
                || null;

            const loadJsonStore = (k, fallback) => {
                try { return JSON.parse(localStorage.getItem(k) || 'null') || fallback; } catch (e) { return fallback; }
            };
            const masteredSlugs = loadJsonStore('pembda_simlab_mastered_v1', []);
            const notesMap = loadJsonStore('pembda_simlab_notes_v1', {});
            const stepChecksMap = loadJsonStore('pembda_simlab_steps_v1', {});

            return {
                labs,
                roomsMeta,
                viewMode: (initRoomParam || initSlug) ? 'inside' : 'lobby',
                activeRoom: initRoom,
                searchQuery: '',
                selectedLab: initLab,
                workTab: 'sim',
                showScenario: true,
                chosenOpt: null,
                checkedAns: false,
                isCorrectAns: false,
                masteredSlugs,
                notesMap,
                stepChecksMap,
                studentNote: initLab ? (notesMap[initLab.slug] || '') : '',
                copied: false,
                openAiModal: false,
                isGenerating: false,
                aiError: '',
                form: {
                    room_key: 'fisika',
                    title: '',
                    subject_name: '',
                    course_id: '',
                    description: '',
                    prompt_spec: ''
                },
                showEditModal: false,
                isSavingEdit: false,
                editForm: {
                    id: null,
                    room_key: 'fisika',
                    title: '',
                    subject_name: '',
                    description: ''
                },
                openEditModal(lab) {
                    this.editForm = {
                        id: lab.id,
                        room_key: lab.discipline_key,
                        title: lab.title,
                        subject_name: lab.subject_name,
                        description: lab.description
                    };
                    this.showEditModal = true;
                },
                async submitEditLab() {
                    this.isSavingEdit = true;
                    try {
                        const res = await fetch(`{{ url('/simlab/lab') }}/${this.editForm.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.editForm)
                        });
                        if (res.ok) window.location.reload();
                        else alert('Gagal memperbarui simulator.');
                    } catch (e) {
                        alert('Terjadi gangguan koneksi.');
                    } finally {
                        this.isSavingEdit = false;
                    }
                },
                get currentRoomMeta() {
                    return this.activeRoom && this.activeRoom !== 'all' ? (this.roomsMeta[this.activeRoom] || null) : null;
                },
                get filteredLabs() {
                    const q = this.searchQuery.toLowerCase().trim();
                    return this.labs.filter(l => {
                        const matchRoom = this.activeRoom === 'all' || l.discipline_key === this.activeRoom;
                        const matchQ = !q || (l.title + ' ' + l.subject_name + ' ' + l.description + ' ' + l.discipline_label).toLowerCase().includes(q);
                        return matchRoom && matchQ;
                    });
                },
                get selectedLabNumber() {
                    if (!this.selectedLab) return '01';
                    const i = this.filteredLabs.findIndex(l => l.id === this.selectedLab.id);
                    if (i >= 0) return String(i + 1).padStart(2, '0');
                    const allIdx = this.labs.findIndex(l => l.id === this.selectedLab.id);
                    return String((allIdx >= 0 ? allIdx : 0) + 1).padStart(2, '0');
                },
                get totalMasteredCount() {
                    const set = new Set(this.masteredSlugs);
                    return this.labs.filter(l => set.has(l.slug)).length;
                },
                isMastered(slug) {
                    return !!slug && this.masteredSlugs.includes(slug);
                },
                roomMasteredCount(roomKey) {
                    const set = new Set(this.masteredSlugs);
                    return this.labs.filter(l => (roomKey === 'all' || l.discipline_key === roomKey) && set.has(l.slug)).length;
                },
                isStepChecked(idx) {
                    if (!this.selectedLab) return false;
                    const arr = this.stepChecksMap[this.selectedLab.slug] || [];
                    return arr.includes(idx);
                },
                get checkedStepsCount() {
                    if (!this.selectedLab) return 0;
                    return (this.stepChecksMap[this.selectedLab.slug] || []).length;
                },
                toggleStep(idx) {
                    if (!this.selectedLab) return;
                    const slug = this.selectedLab.slug;
                    const prev = this.stepChecksMap[slug] || [];
                    const next = prev.includes(idx) ? prev.filter(i => i !== idx) : [...prev, idx];
                    this.stepChecksMap = { ...this.stepChecksMap, [slug]: next };
                    try { localStorage.setItem('pembda_simlab_steps_v1', JSON.stringify(this.stepChecksMap)); } catch (e) {}
                },
                saveNote() {
                    if (!this.selectedLab) return;
                    this.notesMap = { ...this.notesMap, [this.selectedLab.slug]: this.studentNote };
                    try { localStorage.setItem('pembda_simlab_notes_v1', JSON.stringify(this.notesMap)); } catch (e) {}
                },
                answerChallenge(oIdx) {
                    if (!this.selectedLab || !this.selectedLab.tc) return;
                    this.chosenOpt = oIdx;
                    this.checkedAns = true;
                    this.isCorrectAns = (oIdx === this.selectedLab.tc.answer_idx);
                    if (this.isCorrectAns && !this.masteredSlugs.includes(this.selectedLab.slug)) {
                        this.masteredSlugs = [...this.masteredSlugs, this.selectedLab.slug];
                        try { localStorage.setItem('pembda_simlab_mastered_v1', JSON.stringify(this.masteredSlugs)); } catch (e) {}
                    }
                },
                retryChallenge() {
                    this.chosenOpt = null;
                    this.checkedAns = false;
                    this.isCorrectAns = false;
                },
                enterRoom(roomKey) {
                    this.selectRoom(roomKey);
                    this.viewMode = 'inside';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                backToLobby() {
                    this.searchQuery = '';
                    this.viewMode = 'lobby';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
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
                    this.isCorrectAns = false;
                    this.studentNote = this.notesMap[lab.slug] || '';
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
                        alert('Terjadi gangguan koneksi.');
                    }
                }
            };
        }
    </script>
</body>
</html>
