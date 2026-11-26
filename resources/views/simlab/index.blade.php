<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaHUB Virtual Lab — Ruang Penelitian &amp; Praktikum Interaktif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            background-color: #050a14;
            background-image:
                linear-gradient(to right, rgba(56, 189, 248, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(56, 189, 248, 0.04) 1px, transparent 1px),
                radial-gradient(circle at 80% 10%, rgba(16, 185, 129, 0.14) 0%, transparent 45%),
                radial-gradient(circle at 15% 20%, rgba(56, 189, 248, 0.12) 0%, transparent 40%);
            background-size: 40px 40px, 40px 40px, 100% 100%, 100% 100%;
            min-height: 100vh;
            color: #f8fafc;
        }
        .lab-glass {
            background: rgba(15, 23, 42, 0.78);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(148, 163, 184, 0.16);
        }
        .lab-glass:hover {
            border-color: rgba(56, 189, 248, 0.45);
            transform: translateY(-2px);
        }
    </style>
</head>
@php
    $isInstructor = auth()->check() && (auth()->user()->teacher || auth()->user()->isAdmin());
    $disciplineTabs = [
        'fisika'        => ['label' => 'Lab IPA & Fisika', 'icon' => 'fa-atom', 'active' => 'bg-sky-600 text-white border-sky-400 shadow-lg shadow-sky-600/25'],
        'kimia_biologi' => ['label' => 'Lab Kimia & Biologi', 'icon' => 'fa-flask', 'active' => 'bg-emerald-600 text-white border-emerald-400 shadow-lg shadow-emerald-600/25'],
        'matematika'    => ['label' => 'Lab Matematika', 'icon' => 'fa-square-root-variable', 'active' => 'bg-indigo-600 text-white border-indigo-400 shadow-lg shadow-indigo-600/25'],
        'vokasi'        => ['label' => 'Lab Kejuruan & Vokasi', 'icon' => 'fa-gears', 'active' => 'bg-amber-600 text-white border-amber-400 shadow-lg shadow-amber-600/25'],
        'ips_bahasa'    => ['label' => 'Lab IPS, Ekonomi & Bahasa', 'icon' => 'fa-earth-asia', 'active' => 'bg-purple-600 text-white border-purple-400 shadow-lg shadow-purple-600/25'],
    ];
@endphp
<body class="antialiased selection:bg-cyan-500 selection:text-slate-950" x-data="simLabCatalog()">

    <!-- Top Navbar -->
    <nav class="sticky top-0 z-40 border-b border-slate-800/80 bg-slate-950/90 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 via-emerald-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-cyan-500/25">
                    <i class="fas fa-microscope text-white text-base"></i>
                </div>
                <div>
                    <span class="font-extrabold text-white tracking-tight text-base block leading-none">PembdaHUB <span class="text-cyan-400">Research &amp; SimLab</span></span>
                    <span class="text-[11px] text-slate-400">Ruang Laboratorium Penelitian &amp; Praktikum Virtual</span>
                </div>
            </a>

            <div class="flex items-center gap-2.5">
                @if($isInstructor)
                <button @click="openAiModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-indigo-600/25 transition">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <span>Buat Simulator AI</span>
                </button>
                @endif

                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-slate-200 text-xs sm:text-sm font-medium border border-slate-700 transition">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Kembali ke Portal</span>
                </a>
                @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs sm:text-sm font-medium border border-slate-700 transition">
                    <i class="fas fa-right-to-bracket"></i>
                    <span>Login</span>
                </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Banner: Ruangan Penelitian & Praktikum -->
    <header class="relative overflow-hidden border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7">
                    <div class="flex flex-wrap items-center gap-2 mb-3.5">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/15 border border-cyan-400/30 text-cyan-300 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Laboratorium Riset &amp; Praktikum Terpadu</span>
                        </span>
                        @if($isInstructor)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-xs font-bold">
                            <i class="fas fa-chalkboard-teacher"></i> Mode Instruktur (Guru)
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/15 border border-amber-400/30 text-amber-300 text-xs font-bold">
                            <i class="fas fa-user-graduate"></i> Mode Praktikan (Siswa)
                        </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white mb-3 leading-tight">
                        Ruang Eksperimen &amp; Pemodelan <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-emerald-400 to-indigo-400">Fenomena Ilmu Pengetahuan</span>
                    </h1>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-5">
                        PembdaHUB Simulator Lab menghadirkan kemajuan teknologi &amp; AI langsung ke ruang kelas: memodelkan hukum fisika, reaksi kimia, proses biologi, matematika, ekonomi, hingga sistem kejuruan SMK menjadi simulasi objek nyata yang bisa diubah variabelnya dan dibuktikan langsung.
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700/80 flex items-center gap-2.5">
                            <i class="fas fa-flask-vial text-cyan-400 text-lg"></i>
                            <div>
                                <div class="text-lg font-extrabold text-white leading-none">{{ $virtualLabs->count() }}</div>
                                <div class="text-[11px] text-slate-400">Ruang Praktikum</div>
                            </div>
                        </div>
                        <div class="px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700/80 flex items-center gap-2.5">
                            <i class="fas fa-layer-group text-emerald-400 text-lg"></i>
                            <div>
                                <div class="text-lg font-extrabold text-white leading-none">5</div>
                                <div class="text-[11px] text-slate-400">Rumpun Ilmu</div>
                            </div>
                        </div>
                        <button type="button" @click="showGuide = !showGuide" class="px-4 py-2.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-400/35 text-indigo-200 text-xs sm:text-sm font-bold transition inline-flex items-center gap-2">
                            <i class="fas fa-circle-info text-cyan-400"></i>
                            <span x-text="showGuide ? 'Tutup Panduan Lab' : 'Panduan Guru &amp; Siswa'"></span>
                        </button>
                    </div>
                </div>

                <!-- Ilustrasi Ruangan Penelitian & Meja Praktikum (SVG Vector Lab Scene) -->
                <div class="lg:col-span-5">
                    <div class="relative rounded-2xl bg-slate-900/80 border border-slate-700/70 p-4 shadow-2xl overflow-hidden">
                        <div class="flex items-center justify-between text-[11px] font-mono text-slate-400 pb-2 mb-2 border-b border-slate-800">
                            <span><i class="fas fa-circle text-emerald-400 text-[8px] mr-1.5"></i>PEMBDA-LAB // WORKBENCH 01</span>
                            <span>STATUS: READY</span>
                        </div>
                        <svg viewBox="0 0 460 220" class="w-full h-auto rounded-xl bg-slate-950/90 border border-slate-800">
                            <!-- Grid dinding lab -->
                            <defs>
                                <pattern id="labGrid" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(56,189,248,0.08)" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <rect width="460" height="220" fill="url(#labGrid)" />
                            <!-- Rak alat di dinding -->
                            <rect x="25" y="28" width="110" height="6" rx="3" fill="#334155"/>
                            <!-- Tabung reaksi di rak -->
                            <rect x="40" y="10" width="10" height="20" rx="5" fill="rgba(56,189,248,0.6)" stroke="#bae6fd" stroke-width="1.2"/>
                            <rect x="60" y="8" width="10" height="22" rx="5" fill="rgba(52,211,153,0.6)" stroke="#a7f3d0" stroke-width="1.2"/>
                            <rect x="80" y="12" width="10" height="18" rx="5" fill="rgba(244,114,182,0.6)" stroke="#fbcfe8" stroke-width="1.2"/>
                            <rect x="100" y="10" width="10" height="20" rx="5" fill="rgba(251,191,36,0.6)" stroke="#fde68a" stroke-width="1.2"/>

                            <!-- Monitor Osiloskop / Riset di Meja -->
                            <rect x="165" y="34" width="150" height="96" rx="10" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/>
                            <rect x="175" y="44" width="130" height="70" rx="6" fill="#020617"/>
                            <!-- Gelombang sinus bercahaya -->
                            <path d="M 180 79 Q 198 48 215 79 T 250 79 T 285 79 L 300 79" fill="none" stroke="#34d399" stroke-width="2.5"/>
                            <path d="M 180 86 Q 205 106 230 86 T 280 86" fill="none" stroke="#38bdf8" stroke-width="1.8" stroke-dasharray="4 3"/>
                            <rect x="228" y="130" width="24" height="18" fill="#334155"/>
                            <rect x="210" y="146" width="60" height="6" rx="3" fill="#475569"/>

                            <!-- Gelas Beaker Kimia & Erlenmeyer di Kiri Meja -->
                            <path d="M 45 152 L 52 96 L 82 96 L 89 152 Z" fill="rgba(16,185,129,0.22)" stroke="#94a3b8" stroke-width="2"/>
                            <path d="M 48 150 L 52 122 L 82 122 L 86 150 Z" fill="rgba(16,185,129,0.55)"/>
                            <circle cx="62" cy="136" r="3" fill="#a7f3d0"/>
                            <circle cx="72" cy="129" r="2.5" fill="#a7f3d0"/>
                            <!-- Labu Erlenmeyer -->
                            <path d="M 118 96 L 128 96 L 132 116 L 146 152 L 100 152 L 114 116 Z" fill="rgba(168,85,247,0.25)" stroke="#cbd5e1" stroke-width="2"/>
                            <path d="M 107 150 L 113 134 L 133 134 L 139 150 Z" fill="rgba(168,85,247,0.65)"/>

                            <!-- Mikroskop & Model Atom di Kanan Meja -->
                            <path d="M 355 150 L 405 150 L 398 140 L 362 140 Z" fill="#475569"/>
                            <path d="M 388 140 Q 405 110 385 88" fill="none" stroke="#94a3b8" stroke-width="6" stroke-linecap="round"/>
                            <rect x="372" y="72" width="12" height="30" rx="3" transform="rotate(20 378 87)" fill="#38bdf8"/>
                            <rect x="358" y="124" width="32" height="4" rx="2" fill="#cbd5e1"/>
                            <!-- Orbit Atom -->
                            <g transform="translate(405, 52)">
                                <ellipse rx="24" ry="9" fill="none" stroke="#f59e0b" stroke-width="1.3" transform="rotate(30)"/>
                                <ellipse rx="24" ry="9" fill="none" stroke="#38bdf8" stroke-width="1.3" transform="rotate(-30)"/>
                                <circle r="4.5" fill="#fbbf24"/>
                            </g>

                            <!-- Meja Praktikum Utama -->
                            <rect x="14" y="152" width="432" height="14" rx="4" fill="#1e293b" stroke="#475569" stroke-width="1.5"/>
                            <rect x="30" y="166" width="16" height="46" fill="#0f172a"/>
                            <rect x="414" y="166" width="16" height="46" fill="#0f172a"/>
                            <text x="230" y="192" text-anchor="middle" fill="#64748b" font-size="10" font-family="monospace" font-weight="bold">LABORATORIUM VIRTUAL PEMBDAHUV • EKSPERIMEN INTERAKTIF</text>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Panel Informasi: Apa Itu Simulator Lab & Cara Membantu Guru/Siswa -->
            <div x-show="showGuide" x-transition class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 pt-6 border-t border-slate-800/80">
                <div class="lab-glass rounded-2xl p-5 border-l-4 border-l-emerald-400">
                    <h3 class="text-sm sm:text-base font-bold text-emerald-300 flex items-center gap-2 mb-2">
                        <i class="fas fa-chalkboard-teacher"></i> Untuk Guru: Alat Peraga &amp; Pembuktian Konsep di Kelas
                    </h3>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-1.5 leading-relaxed">
                        <li>• <strong>Visualisasi Konsep Abstrak:</strong> Tampilkan gerakan partikel, gaya vektor, pembelahan sel, atau siklus mesin langsung di layar proyektor/LMS.</li>
                        <li>• <strong>Bahas Contoh Soal Sulit:</strong> Gunakan tombol <em>"Uji Kasus Soal"</em> di ruang lab untuk membuktikan rumus secara visual di hadapan siswa.</li>
                        <li>• <strong>Bangkitkan Simulator via AI:</strong> Belum ada simulasi untuk topik tertentu? Klik <strong>Buat Simulator AI</strong> atau bangkitkan langsung dari Course LMS Anda.</li>
                    </ul>
                </div>
                <div class="lab-glass rounded-2xl p-5 border-l-4 border-l-cyan-400">
                    <h3 class="text-sm sm:text-base font-bold text-cyan-300 flex items-center gap-2 mb-2">
                        <i class="fas fa-user-graduate"></i> Untuk Siswa: Ruang Praktikum Aman &amp; Eksperimen Mandiri
                    </h3>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-1.5 leading-relaxed">
                        <li>• <strong>Ikuti SOP Lab Sungguhan:</strong> Setiap ruang lab dilengkapi daftar langkah SOP praktikum dan lembar catatan pengamatan.</li>
                        <li>• <strong>Belajar dengan Mengubah Variabel:</strong> Geser slider massa, suhu, pH, sudut, atau tegangan untuk melihat akibatnya secara langsung tanpa takut alat rusak.</li>
                        <li>• <strong>Pahami Cara Menjawab Soal:</strong> Uji soal-soal hitungan sulit langsung di simulasi agar paham logika di balik rumusnya.</li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Toolbar Filter Rumpun Lab & Pencarian -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2.5">
                    <i class="fas fa-door-open text-cyan-400"></i>
                    <span>Pilih Ruangan Laboratorium Praktikum</span>
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Dikelompokkan berdasarkan rumpun bidang ilmu agar mudah ditemukan lintas jenjang SMP, SMA, &amp; SMK.</p>
            </div>

            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Cari fenomena atau mapel..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700/80 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 transition">
            </div>
        </div>

        <!-- Tabs Rumpun Ilmu Standar -->
        <div class="flex flex-wrap items-center gap-2">
            <button @click="activeDiscipline = 'all'"
                :class="activeDiscipline === 'all' ? 'bg-slate-100 text-slate-950 border-white shadow-lg' : 'bg-slate-900/80 text-slate-300 border-slate-800 hover:border-slate-700'"
                class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold border transition flex items-center gap-2">
                <i class="fas fa-layer-group"></i>
                <span>Semua Ruang Lab</span>
                <span class="px-1.5 py-0.5 rounded-md bg-black/15 text-[11px]">{{ $virtualLabs->count() }}</span>
            </button>

            @foreach($disciplineTabs as $dKey => $dInfo)
            @php $dCount = $virtualLabs->filter(fn($v) => $v->discipline_key === $dKey)->count(); @endphp
            <button @click="activeDiscipline = '{{ $dKey }}'"
                :class="activeDiscipline === '{{ $dKey }}' ? '{{ $dInfo['active'] }}' : 'bg-slate-900/80 text-slate-300 border-slate-800 hover:border-slate-700'"
                class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold border transition flex items-center gap-2">
                <i class="fas {{ $dInfo['icon'] }}"></i>
                <span>{{ $dInfo['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-md bg-black/25 text-[11px]">{{ $dCount }}</span>
            </button>
            @endforeach
        </div>

        <!-- Grid Kartu Ruangan Lab -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($virtualLabs as $lab)
            @php
                $canDelete = auth()->check() && ($lab->created_by === auth()->id() || auth()->user()->isAdmin());
                $searchBlob = strtolower($lab->title . ' ' . $lab->subject_name . ' ' . $lab->discipline_label . ' ' . $lab->description . ' ' . ($lab->course->course_name ?? ''));
            @endphp
            <div x-show="(activeDiscipline === 'all' || activeDiscipline === '{{ $lab->discipline_key }}') && ('{{ addslashes($searchBlob) }}'.includes(searchQuery.toLowerCase()))"
                 x-transition
                 class="lab-glass rounded-2xl p-5 flex flex-col justify-between transition duration-200 relative group">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-lg shadow-md shrink-0"
                                 style="background: {{ $lab->accent_color ?? '#0284c7' }};">
                                <i class="fas {{ $lab->icon ?? 'fa-flask' }}"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-cyan-300 border border-slate-700">
                                    {{ $lab->discipline_label }}
                                </span>
                                <div class="text-xs text-slate-400 font-medium mt-0.5 truncate">{{ $lab->subject_name }}</div>
                            </div>
                        </div>

                        @if($canDelete)
                        <button @click="deleteLab({{ $lab->id }}, '{{ addslashes($lab->title) }}')"
                                title="Hapus Simulator"
                                class="opacity-0 group-hover:opacity-100 text-slate-500 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition shrink-0">
                            <i class="fas fa-trash-can text-xs"></i>
                        </button>
                        @endif
                    </div>

                    <h3 class="text-base sm:text-lg font-bold text-white mb-2 leading-snug group-hover:text-cyan-300 transition">
                        {{ $lab->title }}
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-400 line-clamp-3 leading-relaxed mb-4">
                        {{ $lab->description }}
                    </p>

                    @if($lab->course)
                    <div class="mb-4 px-3 py-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/25 flex items-center gap-2 text-xs text-indigo-300">
                        <i class="fas fa-book-open shrink-0"></i>
                        <span class="truncate">LMS: {{ $lab->course->course_name }}</span>
                    </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                    <span class="text-[11px] text-slate-400 flex items-center gap-1.5">
                        <i class="fas fa-clipboard-check text-emerald-400"></i>
                        <span>{{ is_array($lab->sop_steps) ? count($lab->sop_steps) : 0 }} Langkah SOP</span>
                    </span>

                    <div class="flex items-center gap-1.5">
                        @if($isInstructor)
                        <button type="button"
                                @click="navigator.clipboard?.writeText('{{ route('simlab.lab', $lab->slug) }}'); $el.innerHTML='<i class=\'fas fa-check text-emerald-400\'></i>'; setTimeout(() => $el.innerHTML='<i class=\'fas fa-link\'></i>', 1500)"
                                title="Salin Link untuk Materi/Tugas LMS"
                                class="px-2.5 py-2 rounded-xl bg-slate-800 hover:bg-emerald-600 text-slate-300 hover:text-white text-xs transition">
                            <i class="fas fa-link"></i>
                        </button>
                        @endif
                        <a href="{{ route('simlab.lab', $lab->slug) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-cyan-600 text-white text-xs font-semibold transition">
                            <span>{{ $isInstructor ? 'Pratinjau Lab' : 'Masuk Ruang Lab' }}</span>
                            <i class="fas fa-arrow-right text-[11px]"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </main>

    <!-- Modal: Buat Simulator Baru dengan AI (Guru/Admin) -->
    @if($isInstructor)
    <div x-show="openAiModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="if(!isGenerating) openAiModal = false"
             class="w-full max-w-xl rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-900/90">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                        <i class="fas fa-wand-magic-sparkles text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Buat Simulator Lab Baru (AI Generator)</h3>
                        <p class="text-[11px] text-slate-400">Hasilkan simulasi fenomena beranimasi + SOP praktikum otomatis untuk siswa</p>
                    </div>
                </div>
                <button @click="if(!isGenerating) openAiModal = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form @submit.prevent="submitAiLab" class="p-6 space-y-4">
                <div x-show="aiError" class="p-3 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs" x-text="aiError"></div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Judul Fenomena / Simulator Lab *</label>
                    <input type="text" x-model="form.title" required placeholder="Contoh: Simulator Mesin Pendingin AC / Hukum Archimedes / Siklus Air"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Rumpun / Kategori *</label>
                        <select x-model="form.category" required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="umum">IPA / IPS / Bahasa / Umum</option>
                            <option value="smp">Matematika &amp; Sains Dasar</option>
                            <option value="sma">Sains Lanjut (Fisika/Kimia/Biologi)</option>
                            <option value="vokasi">Kejuruan &amp; Vokasi (SMK)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Mata Pelajaran / Kejuruan *</label>
                        <input type="text" x-model="form.subject_name" required placeholder="Contoh: Fisika / TKR / Kimia / Biologi"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                @if($teacherCourses->count() > 0)
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Hubungkan ke Course / Modul LMS (Opsional)</label>
                    <select x-model="form.course_id"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">-- Tidak dihubungkan ke course tertentu --</option>
                        @foreach($teacherCourses as $c)
                        <option value="{{ $c->id }}">{{ $c->course_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Objek Nyata, Variabel &amp; Contoh Soal yang Diinginkan</label>
                    <textarea x-model="form.description" rows="3" placeholder="Jelaskan objek nyata yang ditampilkan, variabel slider yang bisa diubah siswa, serta contoh soal yang dibuktikan..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="openAiModal = false" :disabled="isGenerating"
                            class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isGenerating"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-60 text-white text-xs font-semibold shadow-lg transition">
                        <i class="fas" :class="isGenerating ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i>
                        <span x-text="isGenerating ? 'Merakit Simulator & SOP...' : 'Generate Simulator'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        function simLabCatalog() {
            return {
                activeDiscipline: 'all',
                searchQuery: '',
                showGuide: true,
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
                        if (res.ok) {
                            window.location.reload();
                        } else {
                            alert('Gagal menghapus simulator.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan saat menghapus simulator.');
                    }
                }
            };
        }
    </script>
</body>
</html>
