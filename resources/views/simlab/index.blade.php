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
        'ipa_renewable'           => asset('simlab-engines/ipa-energi-terbarukan.html'),
        'bio_genetics'            => asset('simlab-engines/biologi-genetika-mendel.html'),
        'math_transform'          => asset('simlab-engines/matematika-transformasi-geometri.html'),
        'vokasi_plc'              => asset('simlab-engines/vokasi-otomasi-relay.html'),
        'econ_compound'           => asset('simlab-engines/ekonomi-bunga-majemuk.html'),
        // === Simulator Batch Baru ===
        'physics_optics'          => asset('simlab-engines/fisika-optika-lensa.html') . '?v=20261127',
        'physics_elevator'        => asset('simlab-engines/fisika-newton-lift.html') . '?v=20261127',
        'physics_electric_field'  => asset('simlab-engines/fisika-medan-listrik.html') . '?v=20261127',
        'bio_osmosis'             => asset('simlab-engines/biologi-osmosis-sel.html') . '?v=20261127',
        'bio_nervous'             => asset('simlab-engines/biologi-sistem-saraf.html') . '?v=20261127',
        'math_integral'           => asset('simlab-engines/matematika-integral-riemann.html') . '?v=20261127',
        'math_trig_circle'        => asset('simlab-engines/matematika-trigonometri-interaktif.html') . '?v=20261127',
        'vokasi_lan_topo'         => asset('simlab-engines/vokasi-jaringan-lan.html') . '?v=20261127',
        'vokasi_cad_dpib'         => asset('simlab-engines/vokasi-cad-denah.html') . '?v=20261127',
        'econ_inflation'          => asset('simlab-engines/ips-inflasi-deflasi.html') . '?v=20261127',
        'ptn_speed_advanced'      => asset('simlab-engines/ptn-soal-cepat-lanjutan.html') . '?v=20261127',
    ];

    $rooms = [
        'fisika' => [
            'code'       => 'LAB-01',
            'floor'      => 'Lantai 1 · Sayap Timur',
            'name'       => 'Lab IPA & Fisika',
            'sub'        => 'Mekanika, Optika, Fluida, Gelombang & Listrik',
            'icon'       => 'fa-atom',
            'color'      => '#0284c7',
            'ring'       => 'ring-sky-500 border-sky-500 bg-sky-50/90',
            'grad'       => 'from-sky-950 via-sky-900 to-cyan-900',
            'badge_bg'   => 'bg-sky-500/20 text-sky-200 border-sky-400/30',
            'prof_name'  => 'Prof. Dr. Albertus Surya, M.Si.',
            'prof_title' => 'Kepala Lab Fisika & Optika',
            'prof_quote' => 'Selamat datang di Sayap Fisika! Mari buktikan hukum alam lewat eksperimen nyata—dari lensa optik hingga medan listrik.',
            'prof_coat'  => '#ffffff',
            'prof_tie'   => '#0284c7',
            'prof_hair'  => '#cbd5e1',
            'prof_skin'  => '#f1c27d',
            'prof_item'  => 'prisma',
            'equipment'  => ['Meja Optika & Lensa', 'Generator Medan Listrik', 'Lift Hukum Newton', 'Rangkaian Hukum Ohm'],
        ],
        'kimia_biologi' => [
            'code'       => 'LAB-02',
            'floor'      => 'Lantai 1 · Sayap Barat',
            'name'       => 'Lab Kimia & Biologi',
            'sub'        => 'Reaksi Molekul, pH, Osmosis Sel & Saraf',
            'icon'       => 'fa-flask',
            'color'      => '#059669',
            'ring'       => 'ring-emerald-500 border-emerald-500 bg-emerald-50/90',
            'grad'       => 'from-emerald-950 via-teal-900 to-emerald-900',
            'badge_bg'   => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30',
            'prof_name'  => 'Prof. Dr. Clara Wijaya, Ph.D.',
            'prof_title' => 'Kepala Lab Biokimia & Sel',
            'prof_quote' => 'Kenakan jas labmu! Di sini kita mengamati reaksi molekul, skala pH, osmosis sel, hingga penjalaran impuls saraf.',
            'prof_coat'  => '#ffffff',
            'prof_tie'   => '#059669',
            'prof_hair'  => '#334155',
            'prof_skin'  => '#ffdbac',
            'prof_item'  => 'erlenmeyer',
            'equipment'  => ['Rak Tabung & pH Meter', 'Mikroskop Osmosis Sel', 'Model Atom Bohr', 'Simulator Sistem Saraf'],
        ],
        'matematika' => [
            'code'       => 'LAB-03',
            'floor'      => 'Lantai 2 · Sayap Timur',
            'name'       => 'Lab Matematika',
            'sub'        => 'Grafik, Trigonometri, Integral & Peluang',
            'icon'       => 'fa-square-root-variable',
            'color'      => '#4f46e5',
            'ring'       => 'ring-indigo-500 border-indigo-500 bg-indigo-50/90',
            'grad'       => 'from-indigo-950 via-violet-900 to-slate-900',
            'badge_bg'   => 'bg-indigo-500/20 text-indigo-200 border-indigo-400/30',
            'prof_name'  => 'Prof. Dr. Hendra Kusuma, M.Mat.',
            'prof_title' => 'Kepala Lab Analisis & Geometri',
            'prof_quote' => 'Matematika itu hidup! Lihat bagaimana kurva parabola, lingkaran trigonometri, dan integral Riemann bekerja secara visual.',
            'prof_coat'  => '#e2e8f0',
            'prof_tie'   => '#4f46e5',
            'prof_hair'  => '#1e293b',
            'prof_skin'  => '#e0ac69',
            'prof_item'  => 'kompas',
            'equipment'  => ['Plotter Parabola', 'Lingkaran Trigonometri', 'Mesin Integral Riemann', 'Distribusi Statistika'],
        ],
        'vokasi' => [
            'code'       => 'LAB-04',
            'floor'      => 'Lantai 1 · Gedung Bengkel',
            'name'       => 'Bengkel Vokasi (SMK)',
            'sub'        => 'Otomotif 4-Tak, CAD DPIB, Jaringan LAN & PLC',
            'icon'       => 'fa-gears',
            'color'      => '#ea580c',
            'ring'       => 'ring-orange-500 border-orange-500 bg-orange-50/90',
            'grad'       => 'from-orange-950 via-amber-900 to-stone-900',
            'badge_bg'   => 'bg-orange-500/20 text-orange-200 border-orange-400/30',
            'prof_name'  => 'Ir. Bima Pratama, M.T.',
            'prof_title' => 'Kepala Bengkel Vokasi & Industri',
            'prof_quote' => 'Siap kerja industri! Uji mesin 4-tak, rakit topologi jaringan LAN, gambar denah CAD DPIB, dan kendalikan motor PLC.',
            'prof_coat'  => '#fb923c',
            'prof_tie'   => '#1e293b',
            'prof_hair'  => '#0f172a',
            'prof_skin'  => '#c68642',
            'prof_item'  => 'kunci',
            'equipment'  => ['Engine Stand 4-Tak', 'Rack Topologi LAN TKJ', 'Meja Gambar CAD DPIB', 'Panel Relay PLC'],
        ],
        'ips_bahasa' => [
            'code'       => 'LAB-05',
            'floor'      => 'Lantai 2 · Sayap Barat',
            'name'       => 'Lab IPS & Bahasa',
            'sub'        => 'Ekonomi Pasar, Inflasi, Sosiologi & Bahasa',
            'icon'       => 'fa-earth-asia',
            'color'      => '#7c3aed',
            'ring'       => 'ring-purple-500 border-purple-500 bg-purple-50/90',
            'grad'       => 'from-purple-950 via-fuchsia-900 to-slate-900',
            'badge_bg'   => 'bg-purple-500/20 text-purple-200 border-purple-400/30',
            'prof_name'  => 'Prof. Dr. Ratna Sari, M.Hum.',
            'prof_title' => 'Kepala Lab Sosial, Ekonomi & Bahasa',
            'prof_quote' => 'Pahami dinamika pasar, dampak inflasi terhadap daya beli, penyebaran norma sosial, hingga struktur kalimat lintas bahasa.',
            'prof_coat'  => '#f3e8ff',
            'prof_tie'   => '#7c3aed',
            'prof_hair'  => '#292524',
            'prof_skin'  => '#f1c27d',
            'prof_item'  => 'buku',
            'equipment'  => ['Bursa Kurva Pasar', 'Monitor Inflasi & Deflasi', 'Peta Dinamika Sosial', 'Analisis SPOK & Tenses'],
        ],
        'olimpiade' => [
            'code'       => 'LAB-06',
            'floor'      => 'Lantai 3 · Menara Prestasi',
            'name'       => 'Arena Olimpiade & PTN',
            'sub'        => '5 Soal Dunia & 35 Soal Cepat SNBT/UTBK',
            'icon'       => 'fa-trophy',
            'color'      => '#d97706',
            'ring'       => 'ring-amber-500 border-amber-500 bg-amber-50/90',
            'grad'       => 'from-amber-950 via-yellow-900 to-slate-900',
            'badge_bg'   => 'bg-amber-500/20 text-amber-200 border-amber-400/30',
            'prof_name'  => 'Prof. Dr. Satria Nusantara',
            'prof_title' => 'Pelatih Kepala Olimpiade & SNBT',
            'prof_quote' => 'Asah kecepatan berpikirmu! Kuasai soal Olimpiade Sains Internasional & trik cepat <15 detik menembus PTN favorit.',
            'prof_coat'  => '#1e293b',
            'prof_tie'   => '#fbbf24',
            'prof_hair'  => '#94a3b8',
            'prof_skin'  => '#e0ac69',
            'prof_item'  => 'piala',
            'equipment'  => ['Arena 5 Soal Olimpiade', 'Tryout SNBT 15 Soal', 'Paket Lanjutan 20 Soal', 'Stopwatch Trik Cepat'],
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
                    <input type="text" x-model="searchQuery" @input="if(searchQuery.trim() !== '') { activeRoom = 'all'; viewMode = 'inside'; }"
                           placeholder="Cari fenomena / soal..."
                           class="w-full pl-8 pr-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-sky-500 transition">
                </div>

                <button type="button" @click="viewMode === 'lobby' ? enterRoom(activeRoom === 'all' ? 'fisika' : activeRoom) : backToLobby()"
                        class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-bold transition inline-flex items-center gap-1.5">
                    <i class="fas" :class="viewMode === 'lobby' ? 'fa-door-open text-sky-600' : 'fa-building text-sky-600'"></i>
                    <span class="hidden md:inline" x-text="viewMode === 'lobby' ? 'Masuk Meja Riset' : 'Lobi Gedung Lab'"></span>
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

        <!-- BAGIAN 1: LOBI GEDUNG LABORATORIUM DISIPLIN ILMU (6 RUANGAN KHUSUS + PROFESOR KEPALA LAB) -->
        <section x-show="viewMode === 'lobby'" x-transition class="space-y-5">
            <!-- Fasad & Direktori Gedung Riset -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-sky-950 to-slate-900 text-white p-6 sm:p-8 shadow-xl border border-slate-800">
                <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(#38bdf8 1px, transparent 1px); background-size: 24px 24px;"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                    <div class="space-y-2 max-w-3xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 border border-sky-400/30 text-sky-200 text-xs font-bold">
                            <i class="fas fa-landmark"></i>
                            <span>Gedung Laboratorium Terpadu PembdaHUB · 6 Ruangan Disiplin Ilmu</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight">
                            Selamat Datang di Kompleks Laboratorium Riset &amp; Vokasi
                        </h1>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Setiap ruangan dipimpin langsung oleh <strong class="text-white">Profesor Kepala Lab</strong> sesuai bidang keilmuannya dan dilengkapi meja praktikum, instrumen eksperimen, SOP, teori, serta soal tantangan HOTS. Pilih pintu ruangan di bawah ini untuk masuk.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <div class="px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15 text-center">
                            <div class="text-2xl font-extrabold text-sky-300">6</div>
                            <div class="text-[11px] text-slate-300 font-semibold">Ruangan Lab</div>
                        </div>
                        <div class="px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15 text-center">
                            <div class="text-2xl font-extrabold text-emerald-300">{{ $virtualLabs->count() }}</div>
                            <div class="text-[11px] text-slate-300 font-semibold">Alat Simulasi</div>
                        </div>
                        <button type="button" @click="activeRoom = 'all'; viewMode = 'inside'"
                                class="px-4 py-3 rounded-2xl bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold text-xs sm:text-sm transition shadow-lg shadow-sky-500/25 inline-flex items-center gap-2">
                            <i class="fas fa-compass"></i>
                            <span>Jelajahi Semua Alat</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Grid 6 Pintu Ruangan Lab & Profesor Kepala Lab -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($rooms as $rKey => $r)
                @php $rCount = $virtualLabs->filter(fn($v) => $v->discipline_key === $rKey)->count(); @endphp
                <div @click="enterRoom('{{ $rKey }}')"
                     class="group cursor-pointer rounded-3xl bg-white border-2 border-slate-200 hover:border-sky-500 shadow-sm hover:shadow-xl transition-all duration-200 overflow-hidden flex flex-col">

                    <!-- Jendela Kaca Interior Ruangan Lab + Ilustrasi Alat + Profesor Kepala Lab -->
                    <div class="relative h-48 bg-gradient-to-br {{ $r['grad'] }} p-4 flex items-end justify-between overflow-hidden">
                        <!-- Latar Grid Arsitektural & Alat Lab SVG -->
                        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: linear-gradient(to right, rgba(255,255,255,0.15) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.15) 1px, transparent 1px); background-size: 20px 20px;"></div>

                        <!-- Badge Kode Ruangan & Lantai -->
                        <div class="absolute top-3.5 left-4 right-4 flex items-center justify-between gap-2 z-10">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-950/60 backdrop-blur-sm border border-white/15 text-white font-mono text-[11px] font-extrabold">
                                {{ $r['code'] }} · {{ $r['floor'] }}
                            </span>
                            <span class="px-2.5 py-1 rounded-full border text-[11px] font-bold {{ $r['badge_bg'] }}">
                                {{ $rCount }} Alat Lab
                            </span>
                        </div>

                        <!-- Ilustrasi Peralatan Khas Ruangan (Kiri) -->
                        <div class="relative z-10 flex flex-col justify-end h-full pt-8 max-w-[58%]">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white text-lg shadow-inner">
                                    <i class="fas {{ $r['icon'] }}"></i>
                                </div>
                                <div class="text-white">
                                    <div class="font-extrabold text-base leading-tight">{{ $r['name'] }}</div>
                                    <div class="text-[11px] text-slate-200/90 line-clamp-1">{{ $r['sub'] }}</div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach(array_slice($r['equipment'], 0, 3) as $eq)
                                <span class="px-2 py-0.5 rounded-md bg-slate-950/45 border border-white/15 text-[10px] text-slate-100 font-medium">{{ $eq }}</span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Ilustrasi SVG Profesor Kepala Lab (Kanan) -->
                        <div class="relative z-10 shrink-0 -mb-4 group-hover:scale-105 transition-transform duration-300">
                            <svg width="112" height="128" viewBox="0 0 112 128" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="{{ $r['prof_name'] }}">
                                <!-- Bayangan -->
                                <ellipse cx="56" cy="122" rx="38" ry="6" fill="rgba(0,0,0,0.35)"/>
                                <!-- Jas Lab / Wearpack -->
                                <path d="M22 122 C22 92, 34 80, 56 80 C78 80, 90 92, 90 122 Z" fill="{{ $r['prof_coat'] }}" stroke="#cbd5e1" stroke-width="1.5"/>
                                <!-- Kemeja dalam & Dasi -->
                                <polygon points="48,80 64,80 56,104" fill="#e2e8f0"/>
                                <polygon points="54,82 58,82 60,104 56,110 52,104" fill="{{ $r['prof_tie'] }}"/>
                                <!-- Kerah Jas Lab -->
                                <polygon points="42,81 52,81 48,104 38,94" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="1"/>
                                <polygon points="70,81 60,81 64,104 74,94" fill="#f1f5f9" stroke="#cbd5e1" stroke-width="1"/>
                                <!-- Lencana Nama Profesor -->
                                <rect x="65" y="96" width="16" height="8" rx="2" fill="#0f172a"/>
                                <rect x="67" y="99" width="12" height="2" rx="1" fill="#38bdf8"/>
                                <!-- Leher -->
                                <rect x="49" y="66" width="14" height="16" rx="6" fill="{{ $r['prof_skin'] }}"/>
                                <!-- Rambut Belakang -->
                                <circle cx="56" cy="44" r="24" fill="{{ $r['prof_hair'] }}"/>
                                <!-- Wajah -->
                                <ellipse cx="56" cy="48" rx="20" ry="22" fill="{{ $r['prof_skin'] }}"/>
                                <!-- Rambut Atas -->
                                <path d="M36 44 C36 25, 76 25, 76 44 C70 34, 42 34, 36 44 Z" fill="{{ $r['prof_hair'] }}"/>
                                <!-- Kacamata Ahli -->
                                <rect x="40" y="43" width="13" height="9" rx="3" fill="rgba(255,255,255,0.25)" stroke="#0f172a" stroke-width="2"/>
                                <rect x="59" y="43" width="13" height="9" rx="3" fill="rgba(255,255,255,0.25)" stroke="#0f172a" stroke-width="2"/>
                                <line x1="53" y1="47" x2="59" y2="47" stroke="#0f172a" stroke-width="2"/>
                                <!-- Mata & Senyum -->
                                <circle cx="46.5" cy="47.5" r="1.8" fill="#0f172a"/>
                                <circle cx="65.5" cy="47.5" r="1.8" fill="#0f172a"/>
                                <path d="M49 58 Q56 63 63 58" stroke="#7c2d12" stroke-width="2" stroke-linecap="round" fill="none"/>
                                <!-- Alat Khas di Tangan Kiri -->
                                @if($r['prof_item'] === 'erlenmeyer')
                                <path d="M18 96 L24 96 L28 112 Q28 116 24 116 L18 116 Q14 116 14 112 Z" fill="#34d399" stroke="#ecfdf5" stroke-width="1.5"/>
                                <rect x="19" y="88" width="4" height="8" fill="#a7f3d0"/>
                                @elseif($r['prof_item'] === 'prisma')
                                <polygon points="20,90 31,112 9,112" fill="rgba(56,189,248,0.65)" stroke="#e0f2fe" stroke-width="1.5"/>
                                @elseif($r['prof_item'] === 'piala')
                                <path d="M13 92 H29 L26 105 H16 Z" fill="#fbbf24" stroke="#fef3c7" stroke-width="1.2"/>
                                <rect x="19" y="105" width="4" height="7" fill="#f59e0b"/>
                                <rect x="15" y="112" width="12" height="4" rx="1" fill="#d97706"/>
                                @elseif($r['prof_item'] === 'kunci')
                                <rect x="17" y="92" width="5" height="22" rx="2" fill="#cbd5e1" stroke="#475569" stroke-width="1"/>
                                <circle cx="19.5" cy="91" r="5" fill="#94a3b8" stroke="#475569" stroke-width="1"/>
                                @elseif($r['prof_item'] === 'kompas')
                                <line x1="21" y1="88" x2="12" y2="114" stroke="#a5b4fc" stroke-width="2.5" stroke-linecap="round"/>
                                <line x1="21" y1="88" x2="30" y2="114" stroke="#a5b4fc" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="21" cy="88" r="3" fill="#fbbf24"/>
                                @else
                                <rect x="12" y="92" width="18" height="22" rx="2" fill="#a855f7" stroke="#f3e8ff" stroke-width="1.5"/>
                                <line x1="15" y1="98" x2="27" y2="98" stroke="#fff" stroke-width="1.5"/>
                                <line x1="15" y1="103" x2="25" y2="103" stroke="#fff" stroke-width="1.5"/>
                                @endif
                            </svg>
                        </div>
                    </div>

                    <!-- Detail Kepala Lab & Tombol Masuk Ruangan -->
                    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between gap-3">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <div class="text-xs sm:text-sm font-extrabold text-slate-900">{{ $r['prof_name'] }}</div>
                                    <div class="text-[11px] font-bold" style="color: {{ $r['color'] }};">{{ $r['prof_title'] }}</div>
                                </div>
                                <span class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-sky-100 text-slate-500 group-hover:text-sky-600 flex items-center justify-center transition">
                                    <i class="fas fa-door-open text-xs"></i>
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 italic leading-relaxed bg-slate-50 border border-slate-200/80 rounded-xl p-2.5">
                                "{{ $r['prof_quote'] }}"
                            </p>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-500">Klik untuk masuk ruangan</span>
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-extrabold text-white shadow-xs transition group-hover:translate-x-0.5"
                                  style="background: {{ $r['color'] }};">
                                Masuk Lab <i class="fas fa-arrow-right text-[10px]"></i>
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- BAGIAN 2: DI DALAM RUANGAN LAB (SAMBUTAN PROFESOR + RAK INSTRUMEN + MEJA PRAKTIKUM LIVE) -->
        <section x-show="viewMode === 'inside'" x-cloak class="space-y-4">

            <!-- Koridor Navigasi Ruangan & Sambutan Profesor Kepala Lab -->
            <div class="bg-white border border-slate-300 rounded-3xl p-4 shadow-sm space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="backToLobby()"
                                class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-sky-700 text-white text-xs font-extrabold transition inline-flex items-center gap-1.5">
                            <i class="fas fa-arrow-left"></i>
                            <span>Keluar ke Lobi Gedung</span>
                        </button>
                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach($rooms as $rKey => $r)
                            <button type="button" @click="selectRoom('{{ $rKey }}')"
                                    :class="activeRoom === '{{ $rKey }}' ? 'text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    :style="activeRoom === '{{ $rKey }}' ? 'background: {{ $r['color'] }}' : ''"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5">
                                <i class="fas {{ $r['icon'] }} text-[11px]"></i>
                                <span>{{ $r['code'] }}</span>
                            </button>
                            @endforeach
                            <button type="button" @click="activeRoom = 'all'"
                                    :class="activeRoom === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition">
                                Semua ({{ $virtualLabs->count() }})
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Banner Sambutan Profesor Kepala Lab Saat Berada di Dalam Ruangan -->
                <template x-if="currentRoomMeta">
                    <div class="rounded-2xl bg-gradient-to-r p-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                         :class="currentRoomMeta.grad">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-14 h-14 rounded-2xl bg-white/15 border border-white/25 flex items-center justify-center shrink-0 text-2xl">
                                <i class="fas" :class="currentRoomMeta.icon"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-white/20 text-[10px] font-extrabold uppercase" x-text="currentRoomMeta.code + ' · ' + currentRoomMeta.floor"></span>
                                    <span class="text-xs font-bold text-sky-200" x-text="currentRoomMeta.prof_name + ' — ' + currentRoomMeta.prof_title"></span>
                                </div>
                                <p class="text-xs sm:text-sm text-white/95 mt-1 leading-relaxed">
                                    "<span x-text="currentRoomMeta.prof_quote"></span>"
                                </p>
                            </div>
                        </div>
                        <div class="hidden xl:flex flex-wrap gap-1.5 shrink-0 max-w-xs justify-end">
                            <template x-for="eq in currentRoomMeta.equipment" :key="eq">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-950/40 border border-white/15 text-[11px] font-semibold text-slate-100" x-text="eq"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

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
                        <button type="button" x-show="selectedLab?.can_delete" @click="openEditModal(selectedLab)"
                                title="Edit Simulator Buatan Saya"
                                class="px-3 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition inline-flex items-center gap-1">
                            <i class="fas fa-pen-to-square"></i>
                            <span class="hidden sm:inline">Edit</span>
                        </button>
                        <button type="button" x-show="selectedLab?.can_delete" @click="deleteLab(selectedLab.id, selectedLab.title)"
                                title="Hapus Simulator Buatan Saya"
                                class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition inline-flex items-center gap-1">
                            <i class="fas fa-trash-can"></i>
                            <span class="hidden sm:inline">Hapus</span>
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

            <form @submit.prevent="submitAiLab" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                <div x-show="aiError" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="aiError"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">1. Pilih Ruangan Lab Penempatan *</label>
                        <select x-model="form.room_key" required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 font-semibold focus:outline-none focus:border-indigo-500">
                            <option value="fisika">LAB-01: Sayap IPA &amp; Fisika</option>
                            <option value="kimia_biologi">LAB-02: Sayap Kimia &amp; Biologi</option>
                            <option value="matematika">LAB-03: Sayap Matematika</option>
                            <option value="vokasi">LAB-04: Bengkel Vokasi (SMK)</option>
                            <option value="ips_bahasa">LAB-05: Sayap IPS &amp; Bahasa</option>
                            <option value="olimpiade">LAB-06: Arena Olimpiade &amp; PTN</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Mata Pelajaran / Kejuruan</label>
                        <input type="text" x-model="form.subject_name" placeholder="Contoh: Fisika / TKR / Kimia / Biologi"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">2. Nama Simulator *</label>
                    <input type="text" x-model="form.title" required placeholder="Contoh: Simulator Tekanan Hidrostatis &amp; Bendungan Air"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Narasi Penjelasan Simulator (Dibaca Siswa di Rak Lab) *</label>
                    <textarea x-model="form.description" rows="2" required placeholder="Tuliskan pertanyaan pemantik / fenomena nyata (misal: Mengapa dinding bendungan bagian bawah selalu dibuat jauh lebih tebal daripada bagian atas?)"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-indigo-500"></textarea>
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
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">3. Instruksi Pembangkit Simulator untuk AI *</label>
                    <textarea x-model="form.prompt_spec" rows="4" required placeholder="Jelaskan secara jelas: objek nyata apa yang digambar, slider variabel apa saja yang bisa diubah siswa (misal kedalaman air &amp; massa jenis), efek animasi apa yang terjadi, dan angka apa yang ditampilkan..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-indigo-500"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Jika instruksi belum dapat dipahami AI sebagai simulasi ilmiah, sistem akan meminta Anda memperjelas instruksi terlebih dahulu.</p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="openAiModal = false" :disabled="isGenerating"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isGenerating"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-60 text-white text-xs font-semibold shadow-md transition">
                        <i class="fas" :class="isGenerating ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i>
                        <span x-text="isGenerating ? 'AI Memvalidasi &amp; Merakit...' : 'Generate Simulator'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Simulator (Khusus Guru Pembuat / Admin) -->
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="if(!isSavingEdit) showEditModal = false"
             class="w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden text-slate-900">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-900 text-base"><i class="fas fa-pen-to-square text-amber-600 mr-1.5"></i> Edit Simulator Saya</h3>
                <button @click="if(!isSavingEdit) showEditModal = false" class="text-slate-400 hover:text-slate-700 p-1"><i class="fas fa-times"></i></button>
            </div>
            <form @submit.prevent="submitEditLab" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ruangan Lab Penempatan</label>
                    <select x-model="editForm.room_key" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900">
                        <option value="fisika">LAB-01: Sayap IPA &amp; Fisika</option>
                        <option value="kimia_biologi">LAB-02: Sayap Kimia &amp; Biologi</option>
                        <option value="matematika">LAB-03: Sayap Matematika</option>
                        <option value="vokasi">LAB-04: Bengkel Vokasi (SMK)</option>
                        <option value="ips_bahasa">LAB-05: Sayap IPS &amp; Bahasa</option>
                        <option value="olimpiade">LAB-06: Arena Olimpiade &amp; PTN</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Simulator</label>
                    <input type="text" x-model="editForm.title" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Mata Pelajaran</label>
                    <input type="text" x-model="editForm.subject_name" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Narasi Penjelasan Simulator</label>
                    <textarea x-model="editForm.description" rows="3" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-300 text-sm text-slate-900"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold">Batal</button>
                    <button type="submit" :disabled="isSavingEdit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold">
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

            return {
                labs,
                roomsMeta,
                viewMode: (initRoomParam || initSlug) ? 'inside' : 'lobby',
                activeRoom: initRoom,
                searchQuery: '',
                selectedLab: initLab,
                workTab: 'sim',
                chosenOpt: null,
                checkedAns: false,
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
