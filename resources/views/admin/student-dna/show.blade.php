@extends(request()->routeIs('guru.*') ? 'layouts.guru' : 'layouts.admin')

@section('title', 'Profil DNA Akademik 360° - ' . $student->full_name)

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
@php
    $sp = $analysis['subject_profile'] ?? [];
    $ev = $analysis['evidence_360'] ?? [];
    $classroomName = $analysis['database_identity']['classroom_name'] ?? '-';
    $dnaAiEndpoint = request()->routeIs('guru.*')
        ? route('guru.dna.generate-ai', $student)
        : route('admin.dna.generate-ai', $student);
    $dnaViewerRole = request()->routeIs('guru.*') ? 'guru' : 'admin';

    $dimRows = [
        ['key' => 'logic',         'label' => 'Logika & Analitik',       'icon' => 'fa-brain',              'bar' => 'bg-sky-600',     'badge' => 'bg-sky-50 text-sky-800 border-sky-200'],
        ['key' => 'communication', 'label' => 'Komunikasi & Bahasa',     'icon' => 'fa-comments',           'bar' => 'bg-emerald-600', 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
        ['key' => 'technical',     'label' => 'Keahlian Teknis & Vokasi','icon' => 'fa-screwdriver-wrench', 'bar' => 'bg-indigo-600',  'badge' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
        ['key' => 'social',        'label' => 'Sosial & Kepemimpinan',   'icon' => 'fa-users',              'bar' => 'bg-amber-500',   'badge' => 'bg-amber-50 text-amber-800 border-amber-200'],
        ['key' => 'creative',      'label' => 'Kreativitas & Inovasi',   'icon' => 'fa-palette',            'bar' => 'bg-purple-600',  'badge' => 'bg-purple-50 text-purple-800 border-purple-200'],
        ['key' => 'discipline',    'label' => 'Kedisiplinan & Ketekunan','icon' => 'fa-user-check',         'bar' => 'bg-rose-600',    'badge' => 'bg-rose-50 text-rose-800 border-rose-200'],
    ];
    $dimLabels = [
        'logic'         => 'Logika & Sains',
        'communication' => 'Bahasa & Literasi',
        'technical'     => 'Teknis & Vokasi',
        'social'        => 'Sosial & Ekonomi',
        'creative'      => 'Seni & Desain',
    ];
@endphp

<div class="max-w-[1360px] mx-auto space-y-5 pb-10" x-data="{ activeTab: 'analisis' }">

    {{-- 1. HERO PROFIL SISWA (Deep Slate + Emerald Accent — Selaras Portal Guru) --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 px-6 py-6 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-emerald-500/15 blur-2xl"></div>

        <div class="relative z-10 flex flex-wrap items-center text-xs text-slate-300 font-medium mb-4 gap-2">
            <a href="{{ request()->routeIs('guru.*') ? route('guru.dashboard') : route('admin.dashboard') }}" class="hover:text-white transition">
                <i class="fas fa-home mr-1 text-emerald-400"></i>Dashboard
            </a>
            <span class="text-slate-600">/</span>
            <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.index') : route('admin.dna.index') }}" class="hover:text-white transition">
                DNA Akademik 360°
            </a>
            <span class="text-slate-600">/</span>
            <span class="text-emerald-300 font-semibold">{{ $student->full_name }}</span>
        </div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4 min-w-0">
                <div class="relative shrink-0">
                    <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}"
                         onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';"
                         class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-emerald-400/80 bg-slate-800 shadow-lg">
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-lg bg-emerald-500 text-slate-950 flex items-center justify-center text-xs font-bold shadow" title="Profil Terverifikasi">
                        <i class="fas {{ $analysis['archetype']['badge_icon'] }}"></i>
                    </span>
                </div>

                <div class="min-w-0 space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-400 text-slate-950 text-xs font-extrabold">
                            <i class="fas {{ $analysis['archetype']['badge_icon'] }} text-[11px]"></i>
                            <span>{{ $analysis['archetype']['title'] }}</span>
                        </span>
                        @if(!empty($analysis['vocational_major']))
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-400/20 border border-amber-300/40 text-amber-200 text-xs font-bold">
                            Jurusan {{ $analysis['vocational_major']['code'] }} · {{ $analysis['vocational_major']['label'] }}
                        </span>
                        @endif
                    </div>

                    <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight truncate">
                        {{ $student->full_name }}
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-300 leading-snug">
                        {{ $analysis['archetype']['tagline'] }} &bull; Kelas <strong class="text-white">{{ $classroomName }}</strong> ({{ $analysis['database_identity']['school_name'] }})
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.pdf', $student) : route('admin.dna.pdf', $student) }}"
                   class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs transition inline-flex items-center gap-2 shadow-md">
                    <i class="fas fa-file-pdf"></i>
                    <span>Unduh Rapor DNA (PDF)</span>
                </a>
                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.index') : route('admin.dna.index') }}"
                   class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white font-bold text-xs transition inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left text-[11px]"></i>
                    <span>Kembali ke Daftar Siswa</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 2. EMPAT KARTU KPI TELEMETRI SISWA --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Rerata Nilai Akademik</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">
                    {{ ($sp['student_avg'] ?? null) !== null ? $sp['student_avg'] : '-' }}
                    <span class="text-xs font-semibold text-slate-500">/ 100</span>
                </p>
                <p class="text-xs text-slate-500 mt-0.5">Mata Pelajaran Tercatat: <strong class="text-slate-800">{{ $analysis['metrics']['total_grades'] }} Mapel</strong></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-book-open"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kehadiran Sekolah (RFID)</p>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1 tabular-nums">
                    {{ $analysis['metrics']['attendance_display'] ?? '-' }}
                </p>
                <p class="text-xs text-slate-500 mt-0.5">Disiplin kehadiran harian</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-id-card"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Aktivitas LMS &amp; Ujian CBT</p>
                <p class="text-xl font-extrabold text-slate-900 mt-1 tabular-nums">
                    {{ $ev['lms_count'] ?? 0 }} Tugas · CBT {{ ($ev['cbt_avg'] ?? null) !== null ? $ev['cbt_avg'] : '-' }}
                </p>
                <p class="text-xs text-slate-500 mt-0.5">Poin Apresiasi &amp; Sikap: <strong class="text-slate-800">{{ $analysis['metrics']['reputation_points'] }} Poin</strong></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-laptop-code"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kelengkapan Sumber Data</p>
                <div class="flex items-center gap-2.5 mt-1">
                    <span class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ $analysis['confidence_score'] }}%</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $analysis['confidence_score'] }}%"></div>
                    </div>
                </div>
                <p class="text-xs text-slate-500 truncate mt-0.5">{{ $analysis['confidence_label'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-database"></i>
            </div>
        </div>
    </div>

    {{-- 3. GRID UTAMA ASIMETRIS (4 Kolom Radar & 6 Sumbu | 8 Kolom Ruang Kerja Tab) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- KOLOM KIRI: RADAR + 6 SUMBU + IDENTITAS RESMI --}}
        <aside class="lg:col-span-4 bg-white border border-slate-200/90 rounded-2xl shadow-xs divide-y divide-slate-200/80 overflow-hidden">
            {{-- Radar Chart --}}
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center text-xs">
                            <i class="fas fa-chart-pie"></i>
                        </span>
                        <span>Peta Radar 6 Sumbu Potensi</span>
                    </h2>
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[11px] font-bold text-slate-600 tabular-nums">Skala 0–100</span>
                </div>
                <div class="relative w-full aspect-square max-w-[300px] mx-auto py-1">
                    <canvas id="dnaRadarChart"></canvas>
                </div>
            </div>

            {{-- Rincian Skor 6 Dimensi --}}
            <div class="p-5 space-y-3.5 text-xs">
                <h3 class="text-sm font-extrabold text-slate-900">Rincian Skor &amp; Sumber Bukti Tiap Sumbu</h3>
                @foreach($dimRows as $dr)
                @php $val = $analysis['scores'][$dr['key']]; @endphp
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/70 space-y-1.5">
                    <div class="flex items-center justify-between font-bold">
                        <span class="text-slate-900 flex items-center gap-2">
                            <i class="fas {{ $dr['icon'] }} text-slate-500 w-3.5 text-center"></i>
                            <span>{{ $dr['label'] }}</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-md border font-extrabold tabular-nums font-mono text-[11px] {{ $dr['badge'] }}">
                            {{ $val }}/100
                        </span>
                    </div>
                    <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $dr['bar'] }} h-1.5 rounded-full" style="width: {{ $val }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-snug">{{ $analysis['dimension_sources'][$dr['key']] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Identitas Resmi & Pengampu --}}
            <div class="p-5 bg-slate-50/60 space-y-2.5 text-xs">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-id-badge text-emerald-600"></i>
                    <span>Identitas &amp; Pengampu Resmi</span>
                </h3>
                <dl class="space-y-2 text-slate-600 pt-1">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">NISN / NIS</dt>
                        <dd class="font-bold text-slate-900 tabular-nums text-right">{{ $student->nisn ?: '-' }} / {{ $analysis['database_identity']['student_nis'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Orang Tua / Wali</dt>
                        <dd class="font-bold text-slate-900 text-right">{{ $analysis['database_identity']['parent_name'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Wali Kelas</dt>
                        <dd class="font-bold text-slate-900 text-right">{{ $analysis['database_identity']['homeroom_teacher'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Kepala Sekolah</dt>
                        <dd class="font-bold text-slate-900 text-right">{{ $analysis['database_identity']['principal_name'] }}</dd>
                    </div>
                </dl>
            </div>
        </aside>

        {{-- KOLOM KANAN: RUANG KERJA TAB ANALISIS, KARIER & REKAM JEJAK --}}
        <section class="lg:col-span-8 space-y-4">
            {{-- Tab Bar Modern Pill (Selaras dengan Tab Dashboard Guru) --}}
            <div class="bg-white border border-slate-200/90 rounded-2xl p-2 shadow-xs flex flex-wrap items-center gap-2 text-xs sm:text-sm font-bold">
                <button type="button" @click="activeTab = 'analisis'"
                        :class="activeTab === 'analisis' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900'"
                        class="px-4 py-2.5 rounded-xl transition inline-flex items-center gap-2">
                    <i class="fas fa-microchip text-xs" :class="activeTab === 'analisis' ? 'text-emerald-300' : ''"></i>
                    <span>Analisis ClassAI &amp; Peta Mapel</span>
                </button>
                <button type="button" @click="activeTab = 'karier'"
                        :class="activeTab === 'karier' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900'"
                        class="px-4 py-2.5 rounded-xl transition inline-flex items-center gap-2">
                    <i class="fas fa-compass text-xs" :class="activeTab === 'karier' ? 'text-emerald-300' : ''"></i>
                    <span>Arah Studi &amp; Karier</span>
                </button>
                <button type="button" @click="activeTab = 'rekam'"
                        :class="activeTab === 'rekam' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900'"
                        class="px-4 py-2.5 rounded-xl transition inline-flex items-center gap-2">
                    <i class="fas fa-database text-xs" :class="activeTab === 'rekam' ? 'text-emerald-300' : ''"></i>
                    <span>Rekam Jejak 360° ({{ $sp['subject_count'] ?? 0 }} Mapel)</span>
                </button>
            </div>

            {{-- TAB 1: ANALISIS CLASSAI & PETA MAPEL --}}
            <div x-show="activeTab === 'analisis'" class="space-y-5">
                @include('partials.student-dna-ai-panel', [
                    'analysis'   => $analysis,
                    'aiEndpoint' => $dnaAiEndpoint,
                    'viewerRole' => $dnaViewerRole,
                ])
            </div>

            {{-- TAB 2: ARAH STUDI & KARIER --}}
            <div x-show="activeTab === 'karier'" x-cloak class="space-y-5">
                <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
                    <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-route text-emerald-600"></i>
                            <span>{{ $analysis['school_type'] === 'SMK' ? 'Bidang Keahlian, Karier & PKL Industri Prioritas' : ($analysis['school_type'] === 'SMP' ? 'Rekomendasi Jalur Pendidikan Lanjutan (SMA / SMK)' : 'Pilihan Rumpun Program Studi Perguruan Tinggi') }}</span>
                        </h3>
                    </div>

                    <div class="p-5 space-y-4 text-xs">
                        @if($analysis['school_type'] === 'SMK')
                            @if(!empty($analysis['recommendations']['career_tracks']))
                            <div class="divide-y divide-slate-200 border border-slate-200 rounded-xl overflow-hidden">
                                @foreach($analysis['recommendations']['career_tracks'] as $track)
                                <div class="p-4 space-y-1.5 hover:bg-slate-50/70 transition">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-extrabold text-slate-900 text-sm">{{ $track['title'] }}</span>
                                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 font-bold text-[11px]">{{ $track['relevance'] }}</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed">{{ $track['description'] }}</p>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            @if(!empty($analysis['recommendations']['pkl_recommendation']))
                            <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200 space-y-1">
                                <p class="font-extrabold text-emerald-950">Rekomendasi Tempat Praktik Kerja Lapangan (PKL)</p>
                                <p class="text-slate-700 leading-relaxed">{{ $analysis['recommendations']['pkl_recommendation'] }}</p>
                            </div>
                            @endif

                            @if(!empty($analysis['recommendations']['certifications']))
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1.5">
                                <p class="font-extrabold text-slate-900">Rekomendasi Sertifikasi Kompetensi (BNSP / Industri)</p>
                                <ul class="list-disc list-inside text-slate-700 space-y-1">
                                    @foreach($analysis['recommendations']['certifications'] as $cert)
                                    <li>{{ $cert }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        @else
                            @if(!empty($analysis['recommendations']['college_majors']))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                @foreach($analysis['recommendations']['college_majors'] as $major)
                                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1.5">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-sky-50 border border-sky-200 text-sky-900 font-bold text-[11px]">
                                        {{ $major['cluster'] }}
                                    </span>
                                    <p class="font-extrabold text-slate-900 text-sm">{{ $major['major'] }}</p>
                                    <p class="text-[11px] text-emerald-700 font-bold">Tingkat Kesesuaian: {{ $major['readiness'] }}</p>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        @endif

                        @if(!empty($analysis['recommendations']['learning_strategy']))
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                            <p class="font-extrabold text-slate-900">Strategi Belajar yang Disarankan</p>
                            <p class="text-slate-700 leading-relaxed">{{ $analysis['recommendations']['learning_strategy'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TAB 3: REKAM JEJAK LENGKAP 360° --}}
            <div x-show="activeTab === 'rekam'" x-cloak class="space-y-5">
                {{-- Daftar Seluruh Nilai Mapel --}}
                <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
                    <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-sm font-extrabold text-slate-900">Daftar Capaian Seluruh Mata Pelajaran</h3>
                        <span class="px-2.5 py-0.5 rounded-lg bg-slate-200/70 text-xs font-bold text-slate-800 tabular-nums">{{ $sp['subject_count'] ?? 0 }} Mapel</span>
                    </div>
                    @if(!empty($sp['subjects']))
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-4 py-3">Mata Pelajaran</th>
                                    <th class="px-4 py-3">Rumpun Dimensi</th>
                                    <th class="px-4 py-3 text-right">Nilai Siswa</th>
                                    <th class="px-4 py-3 text-right">Rerata Sekolah</th>
                                    <th class="px-4 py-3 text-right">Selisih vs Sekolah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($sp['subjects'] as $row)
                                <tr class="hover:bg-emerald-50/30">
                                    <td class="px-4 py-2.5 font-bold text-slate-900">{{ $row['subject_name'] }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $dimLabels[$row['dimension']] ?? $row['dimension'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-extrabold text-slate-900 tabular-nums">{{ $row['score'] }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-600 tabular-nums">{{ $row['school_avg'] ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-right font-bold tabular-nums {{ ($row['delta'] ?? 0) >= 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ $row['delta'] !== null ? (($row['delta'] >= 0 ? '+' : '') . $row['delta']) : '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-5 text-xs text-slate-500">Belum ada data nilai mata pelajaran yang tercatat untuk siswa ini.</div>
                    @endif
                </div>

                {{-- Prestasi, Tugas Akhir / PKL, & Ekskul --}}
                <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs divide-y divide-slate-200 text-xs">
                    <div class="p-5 space-y-2">
                        <h4 class="font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-trophy text-amber-500"></i>
                            <span>Prestasi &amp; Penghargaan Tercatat</span>
                        </h4>
                        @if(!empty($ev['achievements']))
                        <ul class="list-disc list-inside space-y-1 text-slate-700">
                            @foreach($ev['achievements'] as $ach)
                            <li><strong>{{ $ach['ranking'] ? $ach['ranking'] . ' — ' : '' }}{{ $ach['title'] }}</strong>{{ $ach['level'] ? ' (Tingkat ' . $ach['level'] . ')' : '' }}</li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-slate-500">Belum ada penghargaan atau prestasi lomba yang tercatat pada database.</p>
                        @endif
                    </div>

                    <div class="p-5 space-y-2">
                        <h4 class="font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-briefcase text-indigo-600"></i>
                            <span>Tugas Akhir / Penelitian &amp; Praktik Kerja Lapangan (PKL)</span>
                        </h4>
                        @if(!empty($ev['final_project']))
                        <p class="text-slate-700"><strong>Karya / Proyek:</strong> "{{ $ev['final_project']['title'] }}" — Tahap: {{ $ev['final_project']['stage'] }}{{ $ev['final_project']['grade'] !== null ? ' (Nilai: ' . $ev['final_project']['grade'] . ')' : '' }}</p>
                        @endif
                        @if(!empty($ev['pkl']))
                        <p class="text-slate-700"><strong>Lokasi PKL:</strong> {{ $ev['pkl']['company'] }} · {{ $ev['pkl']['approved_logs'] }} logbook disetujui</p>
                        @endif
                        @if(empty($ev['final_project']) && empty($ev['pkl']))
                        <p class="text-slate-500">Belum ada data tugas akhir atau penempatan PKL yang tercatat.</p>
                        @endif
                    </div>

                    <div class="p-5 space-y-2">
                        <h4 class="font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-medal text-emerald-600"></i>
                            <span>Keaktifan Ekstrakurikuler</span>
                        </h4>
                        @if(!empty($ev['extracurriculars']) && $ev['extracurriculars']->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach($ev['extracurriculars'] as $ek)
                            <span class="px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 font-bold text-emerald-900">
                                {{ $ek->extracurricular->name ?? 'Ekstrakurikuler' }}{{ $ek->role_label ? ' · ' . $ek->role_label : '' }}
                            </span>
                            @endforeach
                        </div>
                        @else
                        <p class="text-slate-500">Belum terdaftar pada kegiatan ekstrakurikuler aktif.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('dnaRadarChart');
    if (!el) return;
    new Chart(el.getContext('2d'), {
        type: 'radar',
        data: {
            labels: [
                'Logika & Analitik',
                'Komunikasi',
                'Teknis & Vokasi',
                'Sosial',
                'Kreativitas',
                'Kedisiplinan'
            ],
            datasets: [{
                label: 'Skor Potensi',
                data: [
                    {{ $analysis['scores']['logic'] }},
                    {{ $analysis['scores']['communication'] }},
                    {{ $analysis['scores']['technical'] }},
                    {{ $analysis['scores']['social'] }},
                    {{ $analysis['scores']['creative'] }},
                    {{ $analysis['scores']['discipline'] }}
                ],
                backgroundColor: 'rgba(16, 185, 129, 0.18)',
                borderColor: '#059669',
                borderWidth: 2.5,
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#ffffff',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: { color: '#e2e8f0' },
                    grid: { color: '#e2e8f0' },
                    suggestedMin: 40,
                    suggestedMax: 100,
                    ticks: { display: false, stepSize: 20 },
                    pointLabels: {
                        font: { size: 10, weight: '700' },
                        color: '#1e293b'
                    }
                }
            },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endsection
