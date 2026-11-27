@extends(request()->routeIs('guru.*') ? 'layouts.guru' : 'layouts.admin')

@section('title', 'DNA Akademik Siswa 360°')

@section('content')
@php
    $dnaService = app(\App\Services\StudentDnaService::class);

    $profileCatalog = [
        'logic' => [
            'title'      => 'Analis Logis & Pemecah Masalah',
            'short'      => 'Analis Logis',
            'icon'       => 'fa-brain',
            'pill'       => 'bg-sky-50 border-sky-200 text-sky-900',
            'icon_box'   => 'bg-sky-100 text-sky-700 border-sky-200',
            'bar'        => 'bg-sky-600',
            'desc'       => 'Menonjol pada penalaran matematika, sains, pola data, dan penyelesaian soal hitungan secara runtut.',
            'arah'       => 'Teknologi Informasi, Rekayasa, Riset Sains, Keuangan',
        ],
        'communication' => [
            'title'      => 'Komunikator & Artikulator Bahasa',
            'short'      => 'Komunikator Bahasa',
            'icon'       => 'fa-comments',
            'pill'       => 'bg-emerald-50 border-emerald-200 text-emerald-900',
            'icon_box'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'bar'        => 'bg-emerald-600',
            'desc'       => 'Cakap literasi bacaan, tata bahasa, menulis argumen terstruktur, dan berbicara persuasif.',
            'arah'       => 'Komunikasi Publik, Hukum, Pendidikan Bahasa, Diplomasi',
        ],
        'technical' => [
            'title'      => 'Praktisi Teknis & Rekayasa Terapan',
            'short'      => 'Praktisi Teknis',
            'icon'       => 'fa-screwdriver-wrench',
            'pill'       => 'bg-indigo-50 border-indigo-200 text-indigo-900',
            'icon_box'   => 'bg-indigo-100 text-indigo-700 border-indigo-200',
            'bar'        => 'bg-indigo-600',
            'desc'       => 'Unggul dalam kerja praktik bengkel/laboratorium, perakitan perangkat, jaringan, dan otomasi.',
            'arah'       => 'Teknik Industri, Otomotif, Elektronika, Jaringan Komputer',
        ],
        'social' => [
            'title'      => 'Pemimpin Sosial & Kolaborator',
            'short'      => 'Pemimpin Sosial',
            'icon'       => 'fa-users',
            'pill'       => 'bg-amber-50 border-amber-200 text-amber-900',
            'icon_box'   => 'bg-amber-100 text-amber-800 border-amber-200',
            'bar'        => 'bg-amber-500',
            'desc'       => 'Cakap memimpin tim, berempati tinggi, aktif berorganisasi, dan kuat pada rumpun sosial-ekonomi.',
            'arah'       => 'Manajemen, Psikologi, Kebijakan Publik, Kewirausahaan',
        ],
        'creative' => [
            'title'      => 'Kreator Visual & Perancang Inovatif',
            'short'      => 'Kreator Inovatif',
            'icon'       => 'fa-palette',
            'pill'       => 'bg-purple-50 border-purple-200 text-purple-900',
            'icon_box'   => 'bg-purple-100 text-purple-700 border-purple-200',
            'bar'        => 'bg-purple-600',
            'desc'       => 'Peka terhadap estetika, rancangan grafis, pemodelan bangunan, multimedia, dan karya orisinal.',
            'arah'       => 'Desain Komunikasi Visual, Arsitektur, Media Digital',
        ],
        'discipline' => [
            'title'      => 'Pelaksana Tekun & Berdisiplin Tinggi',
            'short'      => 'Pelaksana Tekun',
            'icon'       => 'fa-user-check',
            'pill'       => 'bg-rose-50 border-rose-200 text-rose-900',
            'icon_box'   => 'bg-rose-100 text-rose-700 border-rose-200',
            'bar'        => 'bg-rose-600',
            'desc'       => 'Konsisten menjaga kehadiran sekolah, ketepatan waktu tugas, dan ketertiban prosedur kerja.',
            'arah'       => 'Tata Kelola Perkantoran, Akuntansi, Logistik, Kedinasan',
        ],
    ];

    $dimAxisInfo = [
        ['key' => 'logic',         'short' => 'LOG', 'label' => 'Logika & Sains',       'color' => 'bg-sky-600'],
        ['key' => 'communication', 'short' => 'BAH', 'label' => 'Komunikasi & Bahasa',  'color' => 'bg-emerald-600'],
        ['key' => 'technical',     'short' => 'TEK', 'label' => 'Teknis & Vokasi',      'color' => 'bg-indigo-600'],
        ['key' => 'social',        'short' => 'SOS', 'label' => 'Sosial & Kepemimpinan','color' => 'bg-amber-500'],
        ['key' => 'creative',      'short' => 'KRE', 'label' => 'Kreatif & Inovasi',    'color' => 'bg-purple-600'],
        ['key' => 'discipline',    'short' => 'DIS', 'label' => 'Disiplin & Sikap',     'color' => 'bg-rose-600'],
    ];

    // Pre-analyze halaman aktif sekali saja agar bisa dipakai untuk KPI & Tabel/Kartu tanpa hitung ulang
    $analyzedRows = [];
    $profileCounts = array_fill_keys(array_keys($profileCatalog), 0);
    $sumConfidence = 0;
    $sumAcademicAvg = 0;
    $countWithAvg = 0;

    foreach ($students as $s) {
        $dna = $dnaService->analyze($s);
        $pKey = $dna['archetype']['primary_key'] ?? 'technical';
        if (isset($profileCounts[$pKey])) {
            $profileCounts[$pKey]++;
        }
        $sumConfidence += (int) ($dna['confidence_score'] ?? 0);
        $stAvg = $dna['subject_profile']['student_avg'] ?? null;
        if ($stAvg !== null) {
            $sumAcademicAvg += (float) $stAvg;
            $countWithAvg++;
        }
        $analyzedRows[] = [
            'student' => $s,
            'dna'     => $dna,
        ];
    }

    $pageCount = count($analyzedRows);
    $avgConfidencePage = $pageCount > 0 ? round($sumConfidence / $pageCount) : 0;
    $avgAcademicPage = $countWithAvg > 0 ? round($sumAcademicAvg / $countWithAvg, 1) : null;

    // Cari profil terbanyak di halaman ini
    $dominantProfileKey = 'logic';
    $dominantProfileMax = 0;
    foreach ($profileCounts as $k => $cnt) {
        if ($cnt > $dominantProfileMax) {
            $dominantProfileMax = $cnt;
            $dominantProfileKey = $k;
        }
    }

    $selectedClassObj = request('classroom_id') ? $classrooms->firstWhere('id', request('classroom_id')) : null;
    $selectedClassName = $selectedClassObj ? ($selectedClassObj->class_name ?? $selectedClassObj->name) : 'Semua Rombel yang Diampu';
@endphp

<div class="max-w-[1360px] mx-auto space-y-5 pb-10"
     x-data="{
        viewMode: localStorage.getItem('dna_guru_view_mode') || 'table',
        infoDrawer: null,
        profileFilter: 'all',
        setView(m) {
            this.viewMode = m;
            localStorage.setItem('dna_guru_view_mode', m);
        },
        toggleDrawer(name) {
            this.infoDrawer = (this.infoDrawer === name) ? null : name;
        }
     }">

    {{-- 1. HERO HEADER (Selaras dengan Dashboard Portal Guru: Deep Slate + Emerald Accent) --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 px-6 py-6 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-emerald-500/15 blur-2xl"></div>
        <div class="pointer-events-none absolute right-32 -bottom-16 h-44 w-44 rounded-full bg-sky-500/10 blur-2xl"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-2 max-w-3xl">
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-300">
                    <a href="{{ request()->routeIs('guru.*') ? route('guru.dashboard') : route('admin.dashboard') }}" class="hover:text-white transition font-medium">
                        <i class="fas fa-home mr-1 text-emerald-400"></i>Dashboard
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="text-emerald-300 font-semibold">DNA Akademik Siswa 360°</span>
                    <span class="hidden sm:inline text-slate-600">•</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-200 text-[11px] font-bold">
                        <i class="fas fa-microchip mr-1"></i>ClassAI Synthesis Aktif
                    </span>
                </div>

                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shrink-0 border border-emerald-300/30">
                        <i class="fas fa-fingerprint text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white">
                            DNA Akademik Siswa 360°
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed mt-0.5">
                            Pemetaan potensi 6 sumbu kompetensi berbasis bukti nyata (<strong class="text-white">Nilai Mapel, LMS, Ujian CBT, Presensi RFID, Proyek/PKL, Prestasi &amp; Cita-cita</strong>) disertai sintesis naratif personal <strong class="text-emerald-300">ClassAI</strong>.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tombol Laci Informasi (Kamus 6 Profil & Metodologi) --}}
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button type="button" @click="toggleDrawer('profiles')"
                        :class="infoDrawer === 'profiles' ? 'bg-emerald-500 text-slate-950 border-emerald-400' : 'bg-white/10 hover:bg-white/15 text-white border-white/15'"
                        class="px-4 py-2.5 rounded-xl border text-xs font-bold transition inline-flex items-center gap-2">
                    <i class="fas fa-compass" :class="infoDrawer === 'profiles' ? 'text-slate-950' : 'text-emerald-300'"></i>
                    <span>Kamus 6 Profil Belajar</span>
                    <i class="fas text-[10px]" :class="infoDrawer === 'profiles' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>

                <button type="button" @click="toggleDrawer('methodology')"
                        :class="infoDrawer === 'methodology' ? 'bg-sky-400 text-slate-950 border-sky-300' : 'bg-white/10 hover:bg-white/15 text-white border-white/15'"
                        class="px-4 py-2.5 rounded-xl border text-xs font-bold transition inline-flex items-center gap-2">
                    <i class="fas fa-microscope" :class="infoDrawer === 'methodology' ? 'text-slate-950' : 'text-sky-300'"></i>
                    <span>Metodologi &amp; Teknik 360°</span>
                    <i class="fas text-[10px]" :class="infoDrawer === 'methodology' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
            </div>
        </div>

        {{-- Laci 1: Kamus 6 Profil Belajar --}}
        <div x-show="infoDrawer === 'profiles'" x-cloak x-transition
             class="relative z-10 mt-5 pt-5 border-t border-white/15">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-300">
                    <i class="fas fa-layer-group mr-1.5"></i>Kamus 6 Profil Belajar Utama &amp; Kombinasi Dual-Profil
                </p>
                <button type="button" @click="infoDrawer = null" class="text-xs text-slate-300 hover:text-white">
                    Tutup <i class="fas fa-times ml-1"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                @foreach($profileCatalog as $pKey => $pInfo)
                <div class="rounded-xl bg-slate-950/55 border border-white/10 p-3.5 space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-white">
                        <span class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center text-emerald-300">
                            <i class="fas {{ $pInfo['icon'] }} text-xs"></i>
                        </span>
                        <span>{{ $pInfo['title'] }}</span>
                    </div>
                    <p class="text-slate-300 leading-relaxed text-[11px]">{{ $pInfo['desc'] }}</p>
                    <p class="text-[11px] text-emerald-300 font-semibold">Arah Studi/Karier: <span class="text-slate-200 font-normal">{{ $pInfo['arah'] }}</span></p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Laci 2: Metodologi, Pendekatan & Teknik --}}
        <div x-show="infoDrawer === 'methodology'" x-cloak x-transition
             class="relative z-10 mt-5 pt-5 border-t border-white/15">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold uppercase tracking-wider text-sky-300">
                    <i class="fas fa-sliders mr-1.5"></i>Metodologi Pengumpulan Data, Pendekatan Relatif Sekolah &amp; Sintesis ClassAI
                </p>
                <button type="button" @click="infoDrawer = null" class="text-xs text-slate-300 hover:text-white">
                    Tutup <i class="fas fa-times ml-1"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
                <div class="rounded-xl bg-slate-950/55 border border-white/10 p-4 space-y-2">
                    <p class="font-bold text-white flex items-center gap-2">
                        <i class="fas fa-database text-sky-400"></i> 1. Enam Sumber Bukti Terverifikasi
                    </p>
                    <p class="text-slate-300 leading-relaxed text-[11px]">
                        Menggabungkan <strong class="text-white">Nilai Rapor &amp; Mapel</strong>, ketuntasan modul <strong class="text-white">LMS</strong>, hasil <strong class="text-white">Ujian CBT</strong>, kehadiran <strong class="text-white">RFID</strong>, proyek <strong class="text-white">Tugas Akhir / Logbook PKL</strong>, serta rekam <strong class="text-white">Prestasi, BK &amp; Diagnostik Cita-cita</strong>.
                    </p>
                </div>
                <div class="rounded-xl bg-slate-950/55 border border-white/10 p-4 space-y-2">
                    <p class="font-bold text-white flex items-center gap-2">
                        <i class="fas fa-scale-balanced text-emerald-400"></i> 2. Pendekatan Dual-Profil &amp; Relatif Sekolah
                    </p>
                    <p class="text-slate-300 leading-relaxed text-[11px]">
                        Capaian mapel dibandingkan dengan <strong class="text-white">rerata unit sekolah</strong> (bukan sekadar angka mentah). Penentuan profil utama dihitung dari <strong class="text-white">5 sumbu bakat akademik/vokasi</strong> agar tidak tertimpa angka presensi RFID.
                    </p>
                </div>
                <div class="rounded-xl bg-slate-950/55 border border-white/10 p-4 space-y-2">
                    <p class="font-bold text-white flex items-center gap-2">
                        <i class="fas fa-shield-heart text-amber-400"></i> 3. Sintesis ClassAI &amp; Anti-Salah Tafsir
                    </p>
                    <p class="text-slate-300 leading-relaxed text-[11px]">
                        Narasi disusun khusus oleh <strong class="text-white">ClassAI</strong> untuk tiap siswa dan di-cache berdasarkan sidik data. Laporan ini bersifat <strong class="text-emerald-300">dinamis &amp; pendampingan</strong>, bukan vonis permanen kemampuan siswa.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. EMPAT KARTU KPI RINGKASAN KELAS (Selaras dengan Gaya Kartu Dashboard Guru) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Cakupan Peserta Didik</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">{{ number_format($students->total()) }} <span class="text-xs font-semibold text-slate-500">Siswa</span></p>
                <p class="text-xs text-emerald-700 font-semibold mt-0.5 truncate" title="{{ $selectedClassName }}">
                    <i class="fas fa-chalkboard-user mr-1"></i>{{ $selectedClassName }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Profil Terbanyak (Halaman Ini)</p>
                <p class="text-sm font-extrabold text-slate-900 mt-1.5 truncate" title="{{ $profileCatalog[$dominantProfileKey]['title'] ?? '-' }}">
                    {{ $profileCatalog[$dominantProfileKey]['short'] ?? 'Belum Terpetakan' }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    <strong class="text-slate-800 tabular-nums">{{ $dominantProfileMax }}</strong> dari {{ $pageCount }} siswa di tampilan ini
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas {{ $profileCatalog[$dominantProfileKey]['icon'] ?? 'fa-chart-pie' }}"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Rerata Nilai Akademik</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">
                    {{ $avgAcademicPage !== null ? $avgAcademicPage : '-' }}
                    <span class="text-xs font-semibold text-slate-500">/ 100</span>
                </p>
                <p class="text-xs text-slate-500 mt-0.5">
                    Dari <strong class="text-slate-700">{{ $countWithAvg }}</strong> siswa yang memiliki nilai
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-award"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kelengkapan Sumber Data</p>
                <div class="flex items-center gap-2.5 mt-1">
                    <span class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ $avgConfidencePage }}%</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $avgConfidencePage }}%"></div>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Rerata kelengkapan 6 sumber bukti</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-database"></i>
            </div>
        </div>
    </div>

    {{-- 3. COMMAND BAR: FILTER KELAS, PENCARIAN, CHIP PROFIL & SWITCHER TAMPILAN --}}
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5">
            <form method="GET" class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 items-end text-xs">
                @if(auth()->user()->isSuperAdmin() && isset($schools))
                <div class="lg:col-span-3">
                    <label class="block font-bold text-slate-700 mb-1.5">
                        <i class="fas fa-school text-slate-400 mr-1"></i>Unit Sekolah
                    </label>
                    <select name="school_id" onchange="this.form.submit()"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50/60 hover:bg-white font-semibold text-slate-800 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="{{ auth()->user()->isSuperAdmin() && isset($schools) ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                    <label class="block font-bold text-slate-700 mb-1.5">
                        <i class="fas fa-users-rectangle text-emerald-600 mr-1"></i>Rombongan Belajar / Kelas
                    </label>
                    <select name="classroom_id" onchange="this.form.submit()"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50/60 hover:bg-white font-semibold text-slate-800 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        <option value="">Semua Rombel ({{ $classrooms->count() }} Kelas)</option>
                        @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                            {{ $cls->class_name ?? $cls->name }} {{ $cls->school ? '(' . ($cls->school->short_name ?? $cls->school->name) . ')' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="{{ auth()->user()->isSuperAdmin() && isset($schools) ? 'lg:col-span-4' : 'lg:col-span-5' }}">
                    <label class="block font-bold text-slate-700 mb-1.5">
                        <i class="fas fa-magnifying-glass text-slate-400 mr-1"></i>Cari Peserta Didik
                    </label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Ketik nama siswa, NISN, atau NIS..."
                               class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50/60 hover:bg-white font-medium text-slate-800 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <div class="lg:col-span-3 flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition inline-flex items-center justify-center gap-1.5 shadow-xs">
                        <i class="fas fa-filter text-[11px]"></i>
                        <span>Terapkan</span>
                    </button>

                    @if(request()->filled('classroom_id') || request()->filled('search') || request()->filled('school_id'))
                    <a href="{{ url()->current() }}"
                       class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition inline-flex items-center justify-center"
                       title="Reset Semua Filter">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                    @endif

                    {{-- Switcher Tampilan Tabel vs Kartu --}}
                    <div class="inline-flex rounded-xl border border-slate-300 bg-slate-100 p-0.5 shrink-0" title="Pilih Mode Tampilan">
                        <button type="button" @click="setView('table')"
                                :class="viewMode === 'table' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                                class="px-2.5 py-1.5 rounded-lg font-bold text-xs transition inline-flex items-center gap-1">
                            <i class="fas fa-table-list"></i>
                            <span class="hidden xl:inline">Tabel</span>
                        </button>
                        <button type="button" @click="setView('grid')"
                                :class="viewMode === 'grid' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                                class="px-2.5 py-1.5 rounded-lg font-bold text-xs transition inline-flex items-center gap-1">
                            <i class="fas fa-grip"></i>
                            <span class="hidden xl:inline">Kartu</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Baris Filter Cepat Profil Belajar (Client-side Instant Filter untuk Halaman Aktif) --}}
        <div class="px-4 sm:px-5 py-3 bg-slate-50 border-t border-slate-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[11px] font-bold text-slate-500 mr-1">
                    <i class="fas fa-sliders mr-1 text-slate-400"></i>Saring Profil:
                </span>
                <button type="button" @click="profileFilter = 'all'"
                        :class="profileFilter === 'all' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100'"
                        class="px-2.5 py-1 rounded-lg border font-bold text-[11px] transition inline-flex items-center gap-1.5">
                    <span>Semua</span>
                    <span class="px-1.5 py-0.2 rounded bg-white/20 text-[10px] tabular-nums">{{ $pageCount }}</span>
                </button>
                @foreach($profileCatalog as $pKey => $pInfo)
                    @if(($profileCounts[$pKey] ?? 0) > 0)
                    <button type="button" @click="profileFilter = (profileFilter === '{{ $pKey }}' ? 'all' : '{{ $pKey }}')"
                            :class="profileFilter === '{{ $pKey }}' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100'"
                            class="px-2.5 py-1 rounded-lg border font-semibold text-[11px] transition inline-flex items-center gap-1.5">
                        <i class="fas {{ $pInfo['icon'] }} text-[10px]"></i>
                        <span>{{ $pInfo['short'] }}</span>
                        <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 font-bold text-[10px] tabular-nums">{{ $profileCounts[$pKey] }}</span>
                    </button>
                    @endif
                @endforeach
            </div>

            <div class="text-[11px] text-slate-500 font-medium">
                Menampilkan <strong class="text-slate-800 tabular-nums">{{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }}</strong> dari <strong class="text-slate-800 tabular-nums">{{ $students->total() }}</strong> siswa
            </div>
        </div>
    </div>

    {{-- 4A. MODE TAMPILAN TABEL ANALITIK (Default) --}}
    <div x-show="viewMode === 'table'" class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/90 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3.5 w-11 text-center">No</th>
                        <th class="px-4 py-3.5">Peserta Didik</th>
                        <th class="px-4 py-3.5">Rombel &amp; Jurusan</th>
                        <th class="px-4 py-3.5">Profil Belajar (Utama &amp; Pendukung)</th>
                        <th class="px-4 py-3.5">Spektrum 6 Sumbu Potensi</th>
                        <th class="px-4 py-3.5">Mapel Unggulan</th>
                        <th class="px-4 py-3.5 text-center">Kelengkapan Bukti</th>
                        <th class="px-4 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80">
                    @forelse($analyzedRows as $idx => $row)
                    @php
                        $s = $row['student'];
                        $dna = $row['dna'];
                        $classroomName = $s->currentClassroom->first()->class_name
                            ?? $s->currentClassroom->first()->name
                            ?? ($s->classrooms->first()->class_name ?? ($s->classroom->class_name ?? '-'));
                        $pKey = $dna['archetype']['primary_key'] ?? 'technical';
                        $pMeta = $profileCatalog[$pKey] ?? $profileCatalog['technical'];
                        $topSubjects = array_slice($dna['subject_profile']['top_subjects'] ?? [], 0, 2);
                        $scores = $dna['scores'] ?? [];
                        $ev = $dna['evidence_360'] ?? [];
                    @endphp
                    <tr x-show="profileFilter === 'all' || profileFilter === '{{ $pKey }}'"
                        class="hover:bg-emerald-50/35 transition group">
                        <td class="px-4 py-3.5 text-center font-bold text-slate-400 tabular-nums">
                            {{ $students->firstItem() + $idx }}
                        </td>

                        {{-- Peserta Didik --}}
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.show', $s) : route('admin.dna.show', $s) }}" class="shrink-0">
                                    <img src="{{ $s->photo_url }}"
                                         class="w-10 h-10 rounded-xl object-cover border border-slate-200 bg-slate-100 group-hover:border-emerald-400 transition"
                                         alt="{{ $s->full_name }}"
                                         onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.show', $s) : route('admin.dna.show', $s) }}"
                                       class="font-extrabold text-slate-900 hover:text-emerald-700 text-xs sm:text-[13px] leading-snug block truncate transition">
                                        {{ $s->full_name }}
                                    </a>
                                    <p class="text-[11px] text-slate-500 font-mono tabular-nums mt-0.5">
                                        NISN: {{ $s->nisn ?: '-' }} · NIS: {{ $s->formatted_nis ?: ($s->nis ?: '-') }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Rombel & Jurusan --}}
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-800">{{ $classroomName }}</div>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                <span class="text-[11px] text-slate-500">{{ $s->school->short_name ?? ($s->school->name ?? '-') }}</span>
                                @if(!empty($dna['vocational_major']))
                                <span class="px-1.5 py-0.5 rounded bg-amber-50 border border-amber-200 text-amber-900 font-bold text-[10px]" title="{{ $dna['vocational_major']['label'] }}">
                                    {{ $dna['vocational_major']['code'] }}
                                </span>
                                @endif
                            </div>
                        </td>

                        {{-- Profil Belajar Utama & Pendukung --}}
                        <td class="px-4 py-3.5">
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold {{ $pMeta['pill'] }}">
                                    <i class="fas {{ $dna['archetype']['badge_icon'] }} text-[10px]"></i>
                                    <span>{{ $dna['archetype']['primary_title'] ?? $dna['archetype']['title'] }}</span>
                                </span>
                                @if(!empty($dna['archetype']['secondary_title']))
                                <div class="text-[11px] text-slate-500 flex items-center gap-1 pl-0.5">
                                    <i class="fas fa-plus text-[9px] text-slate-400"></i>
                                    <span>Pendukung: <strong class="text-slate-700 font-semibold">{{ $dna['archetype']['secondary_title'] }}</strong></span>
                                </div>
                                @endif
                            </div>
                        </td>

                        {{-- Spektrum Mini 6 Sumbu Potensi --}}
                        <td class="px-4 py-3.5">
                            <div class="w-44 space-y-1.5">
                                <div class="grid grid-cols-6 gap-1 items-end h-8 px-1.5 pt-1 pb-0.5 bg-slate-50 rounded-lg border border-slate-200/80">
                                    @foreach($dimAxisInfo as $ax)
                                    @php $scVal = (int) ($scores[$ax['key']] ?? 60); @endphp
                                    <div class="flex flex-col items-center justify-end h-full" title="{{ $ax['label'] }}: {{ $scVal }}/100">
                                        <div class="w-full rounded-t {{ $ax['color'] }}" style="height: {{ max(18, min(100, $scVal)) }}%"></div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-6 gap-1 text-[9px] font-bold text-slate-400 text-center font-mono">
                                    @foreach($dimAxisInfo as $ax)
                                    <span title="{{ $ax['label'] }}: {{ $scores[$ax['key']] ?? 0 }}">{{ $ax['short'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </td>

                        {{-- Mapel Unggulan --}}
                        <td class="px-4 py-3.5">
                            @if(!empty($topSubjects))
                            <div class="space-y-1">
                                @foreach($topSubjects as $tIdx => $sub)
                                <div class="flex items-center justify-between gap-2 max-w-[210px]">
                                    <span class="{{ $tIdx === 0 ? 'font-bold text-slate-900' : 'text-slate-600' }} truncate" title="{{ $sub['subject_name'] }}">
                                        {{ $tIdx + 1 }}. {{ $sub['subject_name'] }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded {{ $tIdx === 0 ? 'bg-emerald-50 border border-emerald-200 text-emerald-800 font-extrabold' : 'bg-slate-100 text-slate-700 font-semibold' }} text-[10px] tabular-nums shrink-0">
                                        {{ $sub['score'] }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <span class="text-slate-400 italic">Belum ada nilai mapel</span>
                            @endif
                        </td>

                        {{-- Kelengkapan Bukti --}}
                        <td class="px-4 py-3.5 text-center">
                            <div class="inline-flex flex-col items-center w-24">
                                <div class="flex items-center justify-between w-full text-[11px] font-bold text-slate-700 tabular-nums mb-1">
                                    <span>{{ $dna['subject_profile']['subject_count'] ?? 0 }} Mapel</span>
                                    <span class="text-emerald-700">{{ $dna['confidence_score'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-emerald-600" style="width: {{ $dna['confidence_score'] }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Tindakan --}}
                        <td class="px-4 py-3.5 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.show', $s) : route('admin.dna.show', $s) }}"
                                   class="px-3 py-2 bg-slate-900 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
                                    <i class="fas fa-chart-radar text-[11px] text-emerald-300"></i>
                                    <span>Profil 360°</span>
                                </a>
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.pdf', $s) : route('admin.dna.pdf', $s) }}"
                                   class="px-2.5 py-2 bg-white hover:bg-rose-50 text-slate-700 hover:text-rose-700 rounded-xl text-xs font-semibold transition border border-slate-300 hover:border-rose-300"
                                   title="Unduh Rapor DNA Akademik (PDF)">
                                    <i class="fas fa-file-pdf text-rose-600"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center">
                            <div class="max-w-md mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-lg">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-900">Tidak Ada Peserta Didik Ditemukan</p>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Tidak ada data siswa yang cocok dengan filter rombel atau kata kunci pencarian saat ini.
                                </p>
                                <a href="{{ url()->current() }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition">
                                    <i class="fas fa-rotate-left"></i>
                                    <span>Tampilkan Semua Siswa</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50/70">
            {{ $students->links() }}
        </div>
        @endif
    </div>

    {{-- 4B. MODE TAMPILAN KARTU VISUAL (Grid Card View untuk Guru yang Suka Visual) --}}
    <div x-show="viewMode === 'grid'" x-cloak class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($analyzedRows as $idx => $row)
            @php
                $s = $row['student'];
                $dna = $row['dna'];
                $classroomName = $s->currentClassroom->first()->class_name
                    ?? $s->currentClassroom->first()->name
                    ?? ($s->classrooms->first()->class_name ?? ($s->classroom->class_name ?? '-'));
                $pKey = $dna['archetype']['primary_key'] ?? 'technical';
                $pMeta = $profileCatalog[$pKey] ?? $profileCatalog['technical'];
                $topSubjects = array_slice($dna['subject_profile']['top_subjects'] ?? [], 0, 2);
                $scores = $dna['scores'] ?? [];
            @endphp
            <div x-show="profileFilter === 'all' || profileFilter === '{{ $pKey }}'"
                 class="bg-white border border-slate-200/90 hover:border-emerald-400 rounded-2xl p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between gap-4">

                <div class="space-y-3.5">
                    {{-- Header Kartu Siswa --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $s->photo_url }}"
                                 class="w-12 h-12 rounded-2xl object-cover border border-slate-200 bg-slate-100 shrink-0"
                                 alt="{{ $s->full_name }}"
                                 onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                            <div class="min-w-0">
                                <h3 class="font-extrabold text-slate-900 text-sm truncate">{{ $s->full_name }}</h3>
                                <p class="text-xs font-semibold text-slate-600 truncate">
                                    {{ $classroomName }}
                                    @if(!empty($dna['vocational_major']))
                                    · <span class="text-amber-800 font-bold">{{ $dna['vocational_major']['code'] }}</span>
                                    @endif
                                </p>
                                <p class="text-[11px] text-slate-400 font-mono tabular-nums">NISN: {{ $s->nisn ?: '-' }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px] tabular-nums shrink-0" title="Kelengkapan Sumber Data">
                            {{ $dna['confidence_score'] }}% Data
                        </span>
                    </div>

                    {{-- Badge Profil Dominan --}}
                    <div class="p-3 rounded-xl border {{ $pMeta['pill'] }} space-y-1">
                        <div class="flex items-center gap-2 font-extrabold text-xs">
                            <i class="fas {{ $dna['archetype']['badge_icon'] }}"></i>
                            <span>{{ $dna['archetype']['title'] }}</span>
                        </div>
                        <p class="text-[11px] opacity-85 leading-snug">{{ $dna['archetype']['tagline'] }}</p>
                    </div>

                    {{-- Bar 6 Sumbu Potensi --}}
                    <div class="space-y-1.5 pt-1">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                            <span>Spektrum 6 Sumbu Kompetensi</span>
                            <span class="tabular-nums">Rerata Mapel: <strong class="text-slate-900">{{ $dna['subject_profile']['student_avg'] ?? '-' }}</strong></span>
                        </div>
                        <div class="grid grid-cols-6 gap-1.5 pt-1">
                            @foreach($dimAxisInfo as $ax)
                            @php $scVal = (int) ($scores[$ax['key']] ?? 60); @endphp
                            <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-1.5 text-center">
                                <div class="text-[9px] font-bold text-slate-400">{{ $ax['short'] }}</div>
                                <div class="text-xs font-extrabold text-slate-800 tabular-nums mt-0.5">{{ $scVal }}</div>
                                <div class="w-full bg-slate-200 h-1 rounded-full mt-1 overflow-hidden">
                                    <div class="{{ $ax['color'] }} h-1 rounded-full" style="width: {{ $scVal }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Mapel Unggulan --}}
                    <div class="pt-1">
                        <p class="text-[11px] font-bold text-slate-500 mb-1.5">Mata Pelajaran Unggulan:</p>
                        @if(!empty($topSubjects))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($topSubjects as $sub)
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-[11px] font-semibold">
                                {{ $sub['subject_name'] }}: <strong class="tabular-nums">{{ $sub['score'] }}</strong>
                            </span>
                            @endforeach
                        </div>
                        @else
                        <p class="text-xs text-slate-400 italic">Belum ada data nilai mata pelajaran</p>
                        @endif
                    </div>
                </div>

                {{-- Footer Kartu --}}
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.show', $s) : route('admin.dna.show', $s) }}"
                       class="flex-1 px-3.5 py-2 bg-slate-900 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition inline-flex items-center justify-center gap-1.5">
                        <i class="fas fa-chart-radar text-emerald-300"></i>
                        <span>Buka Profil Lengkap 360°</span>
                    </a>
                    <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.pdf', $s) : route('admin.dna.pdf', $s) }}"
                       class="px-3 py-2 bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-700 rounded-xl text-xs font-bold transition border border-slate-200"
                       title="Unduh PDF">
                        <i class="fas fa-file-pdf text-rose-600"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        @if($students->hasPages())
        <div class="bg-white border border-slate-200/90 rounded-2xl px-5 py-3.5">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
