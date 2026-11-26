<!DOCTYPE html>
<html lang="id">
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
            background-color: #f8fafc;
            background-image:
                linear-gradient(to right, rgba(14, 165, 233, 0.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(14, 165, 233, 0.06) 1px, transparent 1px),
                radial-gradient(circle at 85% 5%, rgba(16, 185, 129, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 10% 15%, rgba(56, 189, 248, 0.08) 0%, transparent 40%);
            background-size: 36px 36px, 36px 36px, 100% 100%, 100% 100%;
            min-height: 100vh;
            color: #0f172a;
        }
        .lab-card {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(8px);
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
        }
        .lab-card:hover {
            border-color: #0ea5e9;
            box-shadow: 0 10px 26px rgba(14, 165, 233, 0.12);
            transform: translateY(-2px);
        }
    </style>
</head>
@php
    $isInstructor = auth()->check() && (auth()->user()->teacher || auth()->user()->isAdmin());
    $disciplineTabs = [
        'olimpiade'     => ['label' => '🏆 Lab Olimpiade', 'icon' => 'fa-trophy', 'active' => 'bg-amber-500 text-slate-950 border-amber-600 shadow-md'],
        'fisika'        => ['label' => 'Lab IPA & Fisika', 'icon' => 'fa-atom', 'active' => 'bg-sky-600 text-white border-sky-700 shadow-md'],
        'kimia_biologi' => ['label' => 'Lab Kimia & Biologi', 'icon' => 'fa-flask', 'active' => 'bg-emerald-600 text-white border-emerald-700 shadow-md'],
        'matematika'    => ['label' => 'Lab Matematika', 'icon' => 'fa-square-root-variable', 'active' => 'bg-indigo-600 text-white border-indigo-700 shadow-md'],
        'vokasi'        => ['label' => 'Lab Kejuruan & Vokasi', 'icon' => 'fa-gears', 'active' => 'bg-orange-600 text-white border-orange-700 shadow-md'],
        'ips_bahasa'    => ['label' => 'Lab IPS, Ekonomi & Bahasa', 'icon' => 'fa-earth-asia', 'active' => 'bg-purple-600 text-white border-purple-700 shadow-md'],
    ];
@endphp
<body class="antialiased selection:bg-sky-200 selection:text-slate-900" x-data="simLabCatalog()">

    <!-- Top Navbar (Clean Bright Lab Header) -->
    <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 via-teal-500 to-emerald-500 flex items-center justify-center shadow-md shadow-sky-500/20">
                    <i class="fas fa-microscope text-white text-base"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 tracking-tight text-base block leading-none">PembdaHUB <span class="text-sky-600">Research &amp; SimLab</span></span>
                    <span class="text-[11px] text-slate-500 font-medium">Ruang Laboratorium Penelitian &amp; Praktikum Virtual</span>
                </div>
            </a>

            <div class="flex items-center gap-2.5">
                @if($isInstructor)
                <button @click="openAiModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs sm:text-sm font-semibold shadow-md transition">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <span>Buat Simulator AI</span>
                </button>
                @endif

                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold border border-slate-300 transition">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Kembali ke Portal</span>
                </a>
                @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-semibold transition">
                    <i class="fas fa-right-to-bracket"></i>
                    <span>Login</span>
                </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Banner: Ruangan Penelitian Terang & Bersih -->
    <header class="relative overflow-hidden border-b border-slate-200 bg-gradient-to-br from-white via-sky-50/60 to-emerald-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7">
                    <div class="flex flex-wrap items-center gap-2 mb-3.5">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 border border-sky-200 text-sky-800 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Laboratorium Riset &amp; Praktikum Modern</span>
                        </span>
                        @if($isInstructor)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold">
                            <i class="fas fa-chalkboard-teacher"></i> Mode Instruktur (Guru)
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold">
                            <i class="fas fa-user-graduate"></i> Mode Praktikan (Siswa)
                        </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900 mb-3 leading-tight">
                        Pecahkan Misteri Sains &amp; Fenomena Nyata di <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 via-teal-600 to-emerald-600">Laboratorium Virtual</span>
                    </h1>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-5">
                        Mengapa kapal baja ribuan ton bisa mengapung sementara paku tenggelam? Bagaimana jantung memompa darah bersih dan kotor tanpa tercampur? Masuk ke ruang lab cerah PembdaHUB, ubah variabel eksperimennya, dan temukan jawabannya secara visual!
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 shadow-xs flex items-center gap-2.5">
                            <i class="fas fa-flask-vial text-sky-600 text-lg"></i>
                            <div>
                                <div class="text-lg font-extrabold text-slate-900 leading-none">{{ $virtualLabs->count() }}</div>
                                <div class="text-[11px] text-slate-500 font-medium">Ruang Praktikum</div>
                            </div>
                        </div>
                        <div class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 shadow-xs flex items-center gap-2.5">
                            <i class="fas fa-trophy text-amber-500 text-lg"></i>
                            <div>
                                <div class="text-lg font-extrabold text-slate-900 leading-none">5 Soal</div>
                                <div class="text-[11px] text-slate-500 font-medium">Lab Olimpiade</div>
                            </div>
                        </div>
                        <button type="button" @click="showGuide = !showGuide" class="px-4 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-800 text-xs sm:text-sm font-bold transition inline-flex items-center gap-2">
                            <i class="fas fa-circle-info text-indigo-600"></i>
                            <span x-text="showGuide ? 'Tutup Panduan Lab' : 'Panduan Guru &amp; Siswa'"></span>
                        </button>
                    </div>
                </div>

                <!-- Ilustrasi Ruangan Penelitian Cerah & Bersih (Clean Bright Lab SVG) -->
                <div class="lg:col-span-5">
                    <div class="relative rounded-2xl bg-white border border-slate-200 p-4 shadow-xl overflow-hidden">
                        <div class="flex items-center justify-between text-[11px] font-mono text-slate-500 pb-2 mb-2 border-b border-slate-100">
                            <span><i class="fas fa-circle text-emerald-500 text-[8px] mr-1.5"></i>PEMBDA-LAB // CLEAN WORKBENCH</span>
                            <span class="text-sky-600 font-bold">SIAP PRAKTIKUM</span>
                        </div>
                        <svg viewBox="0 0 460 220" class="w-full h-auto rounded-xl bg-gradient-to-b from-sky-50 to-slate-50 border border-slate-200">
                            <defs>
                                <pattern id="brightGrid" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(14,165,233,0.1)" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <rect width="460" height="220" fill="url(#brightGrid)" />
                            <!-- Jendela Lab Cerah -->
                            <rect x="310" y="16" width="120" height="80" rx="8" fill="#e0f2fe" stroke="#bae6fd" stroke-width="2"/>
                            <line x1="370" y1="16" x2="370" y2="96" stroke="#bae6fd" stroke-width="2"/>
                            <circle cx="340" cy="38" r="10" fill="#fde047" opacity="0.8"/>

                            <!-- Rak Tabung Reaksi -->
                            <rect x="25" y="34" width="110" height="6" rx="3" fill="#94a3b8"/>
                            <rect x="40" y="14" width="10" height="22" rx="5" fill="#38bdf8" stroke="#0284c7" stroke-width="1.2"/>
                            <rect x="60" y="12" width="10" height="24" rx="5" fill="#34d399" stroke="#059669" stroke-width="1.2"/>
                            <rect x="80" y="16" width="10" height="20" rx="5" fill="#f472b6" stroke="#db2777" stroke-width="1.2"/>
                            <rect x="100" y="14" width="10" height="22" rx="5" fill="#fbbf24" stroke="#d97706" stroke-width="1.2"/>

                            <!-- Monitor Riset di Meja -->
                            <rect x="160" y="32" width="140" height="92" rx="10" fill="#1e293b" stroke="#cbd5e1" stroke-width="2"/>
                            <rect x="169" y="41" width="122" height="68" rx="6" fill="#0f172a"/>
                            <path d="M 174 76 Q 192 46 208 76 T 242 76 T 276 76 L 288 76" fill="none" stroke="#34d399" stroke-width="2.5"/>
                            <path d="M 174 84 Q 198 102 222 84 T 270 84" fill="none" stroke="#38bdf8" stroke-width="1.8" stroke-dasharray="4 3"/>
                            <rect x="220" y="124" width="20" height="22" fill="#64748b"/>
                            <rect x="200" y="144" width="60" height="6" rx="3" fill="#94a3b8"/>

                            <!-- Gelas Beaker & Erlenmeyer -->
                            <path d="M 42 150 L 49 96 L 79 96 L 86 150 Z" fill="rgba(16,185,129,0.2)" stroke="#64748b" stroke-width="2"/>
                            <path d="M 45 148 L 49 122 L 79 122 L 83 148 Z" fill="rgba(16,185,129,0.55)"/>
                            <path d="M 114 96 L 124 96 L 128 116 L 142 150 L 96 150 L 110 116 Z" fill="rgba(168,85,247,0.2)" stroke="#64748b" stroke-width="2"/>
                            <path d="M 103 148 L 109 132 L 129 132 L 135 148 Z" fill="rgba(168,85,247,0.6)"/>

                            <!-- Mikroskop -->
                            <path d="M 345 148 L 395 148 L 388 138 L 352 138 Z" fill="#475569"/>
                            <path d="M 378 138 Q 395 110 375 88" fill="none" stroke="#334155" stroke-width="6" stroke-linecap="round"/>
                            <rect x="362" y="72" width="12" height="30" rx="3" transform="rotate(20 368 87)" fill="#0284c7"/>
                            <rect x="348" y="122" width="32" height="4" rx="2" fill="#64748b"/>

                            <!-- Meja Praktikum Putih Bersih -->
                            <rect x="14" y="150" width="432" height="14" rx="4" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1.5"/>
                            <rect x="30" y="164" width="16" height="48" fill="#cbd5e1"/>
                            <rect x="414" y="164" width="16" height="48" fill="#cbd5e1"/>
                            <text x="230" y="190" text-anchor="middle" fill="#475569" font-size="10" font-family="monospace" font-weight="bold">LABORATORIUM VIRTUAL PEMBDAHUV • RUANG PRAKTIKUM TERPADU</text>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Panel Informasi: Bagaimana SimLab Membantu Guru & Siswa -->
            <div x-show="showGuide" x-transition class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 pt-6 border-t border-slate-200">
                <div class="lab-card rounded-2xl p-5 border-l-4 border-l-emerald-500">
                    <h3 class="text-sm sm:text-base font-bold text-emerald-800 flex items-center gap-2 mb-2">
                        <i class="fas fa-chalkboard-teacher text-emerald-600"></i> Untuk Guru: Menjelaskan Fenomena &amp; Membuktikan Rumus
                    </h3>
                    <ul class="text-xs sm:text-sm text-slate-600 space-y-1.5 leading-relaxed">
                        <li>• <strong>Visualisasi Konsep Sulit:</strong> Tampilkan objek nyata (mobil, pembuluh darah, kapal laut, atom, mesin 4-tak) yang bergerak langsung di depan kelas.</li>
                        <li>• <strong>Pembuktian Soal &amp; Olimpiade:</strong> Gunakan tombol <em>"Uji Kasus Soal"</em> agar siswa melihat langsung mengapa sebuah rumus menghasilkan jawaban tersebut.</li>
                        <li>• <strong>Generator Simulator AI:</strong> Buat simulasi khusus untuk materi LMS Anda hanya dengan beberapa klik.</li>
                    </ul>
                </div>
                <div class="lab-card rounded-2xl p-5 border-l-4 border-l-sky-500">
                    <h3 class="text-sm sm:text-base font-bold text-sky-800 flex items-center gap-2 mb-2">
                        <i class="fas fa-user-graduate text-sky-600"></i> Untuk Siswa: Eksperimen Mandiri &amp; Pecahkan Rasa Penasaran
                    </h3>
                    <ul class="text-xs sm:text-sm text-slate-600 space-y-1.5 leading-relaxed">
                        <li>• <strong>Berangkat dari Pertanyaan Nyata:</strong> Setiap ruang lab diawali pertanyaan fenomena sehari-hari untuk memicu rasa ingin tahu.</li>
                        <li>• <strong>Ikuti SOP Lab &amp; Catat Hasil:</strong> Centang langkah SOP praktikum, ubah variabel (massa, suhu, pH, sudut), dan catat kesimpulannya.</li>
                        <li>• <strong>Latihan Soal Olimpiade:</strong> Coba <strong>Lab 5 Soal Olimpiade Internasional</strong> untuk memahami logika di balik soal-soal tingkat tinggi.</li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Toolbar Filter Rumpun Lab & Pencarian -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 flex items-center gap-2.5">
                    <i class="fas fa-door-open text-sky-600"></i>
                    <span>Pilih Ruangan Laboratorium Praktikum</span>
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Setiap ruangan dilengkapi animasi objek nyata, SOP praktikum, dan pembuktian contoh soal.</p>
            </div>

            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Cari fenomena atau mapel..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-sky-500 shadow-xs transition">
            </div>
        </div>

        <!-- Tabs Rumpun Ilmu Standar -->
        <div class="flex flex-wrap items-center gap-2">
            <button @click="activeDiscipline = 'all'"
                :class="activeDiscipline === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-white text-slate-700 border-slate-300 hover:border-slate-400'"
                class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold border transition flex items-center gap-2">
                <i class="fas fa-layer-group"></i>
                <span>Semua Ruang Lab</span>
                <span class="px-1.5 py-0.5 rounded-md bg-black/15 text-[11px]">{{ $virtualLabs->count() }}</span>
            </button>

            @foreach($disciplineTabs as $dKey => $dInfo)
            @php $dCount = $virtualLabs->filter(fn($v) => $v->discipline_key === $dKey)->count(); @endphp
            <button @click="activeDiscipline = '{{ $dKey }}'"
                :class="activeDiscipline === '{{ $dKey }}' ? '{{ $dInfo['active'] }}' : 'bg-white text-slate-700 border-slate-300 hover:border-slate-400'"
                class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold border transition flex items-center gap-2">
                <i class="fas {{ $dInfo['icon'] }}"></i>
                <span>{{ $dInfo['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-md bg-black/10 text-[11px]">{{ $dCount }}</span>
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
                 class="lab-card rounded-2xl p-5 flex flex-col justify-between transition duration-200 relative group">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-xl shadow-md shrink-0"
                                 style="background: {{ $lab->accent_color ?? '#0284c7' }};">
                                <i class="fas {{ $lab->icon ?? 'fa-flask' }}"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $lab->discipline_label }}
                                </span>
                                <div class="text-xs text-slate-500 font-semibold mt-0.5 truncate">{{ $lab->subject_name }}</div>
                            </div>
                        </div>

                        @if($canDelete)
                        <button @click="deleteLab({{ $lab->id }}, '{{ addslashes($lab->title) }}')"
                                title="Hapus Simulator"
                                class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition shrink-0">
                            <i class="fas fa-trash-can text-xs"></i>
                        </button>
                        @endif
                    </div>

                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900 mb-2 leading-snug group-hover:text-sky-700 transition">
                        {{ $lab->title }}
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                        {{ $lab->description }}
                    </p>

                    @if($lab->course)
                    <div class="mb-4 px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 flex items-center gap-2 text-xs text-indigo-800 font-medium">
                        <i class="fas fa-book-open shrink-0 text-indigo-600"></i>
                        <span class="truncate">LMS: {{ $lab->course->course_name }}</span>
                    </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <span class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                        <i class="fas fa-clipboard-check text-emerald-600"></i>
                        <span>{{ is_array($lab->sop_steps) ? count($lab->sop_steps) : 0 }} Langkah SOP</span>
                    </span>

                    <div class="flex items-center gap-1.5">
                        @if($isInstructor)
                        <button type="button"
                                @click="navigator.clipboard?.writeText('{{ route('simlab.lab', $lab->slug) }}'); $el.innerHTML='<i class=\'fas fa-check text-emerald-600\'></i>'; setTimeout(() => $el.innerHTML='<i class=\'fas fa-link\'></i>', 1500)"
                                title="Salin Link untuk Materi/Tugas LMS"
                                class="px-2.5 py-2 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 border border-slate-200 text-xs transition">
                            <i class="fas fa-link"></i>
                        </button>
                        @endif
                        <a href="{{ route('simlab.lab', $lab->slug) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-sky-600 text-white text-xs font-bold transition shadow-xs">
                            <span>{{ $isInstructor ? 'Pratinjau Lab' : 'Masuk &amp; Uji Fenomena' }}</span>
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
