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
        ['key' => 'logic', 'label' => 'Logika & Analitik', 'icon' => 'fa-brain', 'bar' => 'bg-sky-600'],
        ['key' => 'communication', 'label' => 'Komunikasi & Bahasa', 'icon' => 'fa-comments', 'bar' => 'bg-emerald-600'],
        ['key' => 'technical', 'label' => 'Keahlian Teknis & Vokasi', 'icon' => 'fa-wrench', 'bar' => 'bg-indigo-600'],
        ['key' => 'social', 'label' => 'Sosial & Kepemimpinan', 'icon' => 'fa-users', 'bar' => 'bg-amber-600'],
        ['key' => 'creative', 'label' => 'Kreativitas & Inovasi', 'icon' => 'fa-palette', 'bar' => 'bg-purple-600'],
        ['key' => 'discipline', 'label' => 'Kedisiplinan & Ketekunan', 'icon' => 'fa-clock', 'bar' => 'bg-rose-600'],
    ];
    $dimLabels = [
        'logic' => 'Logika & Sains',
        'communication' => 'Bahasa & Literasi',
        'technical' => 'Teknis & Vokasi',
        'social' => 'Sosial & Ekonomi',
        'creative' => 'Seni & Desain',
    ];
@endphp

<div class="space-y-5" x-data="{ activeTab: 'analisis' }">
    {{-- Header Dossier Institusional (Solid Slate, Kontras Tinggi) --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl px-5 py-5 sm:px-6 text-white">
        <div class="flex items-center text-xs text-slate-300 font-medium mb-3 gap-2">
            <a href="{{ request()->routeIs('guru.*') ? route('guru.dashboard') : route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
            <span class="text-slate-600">/</span>
            <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.index') : route('admin.dna.index') }}" class="hover:text-white transition">DNA Akademik 360°</a>
            <span class="text-slate-600">/</span>
            <span class="text-sky-300 font-semibold">Dossier Siswa</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-4 min-w-0">
                <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}"
                     onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';"
                     class="w-16 h-16 sm:w-[72px] sm:h-[72px] rounded-xl object-cover border border-slate-700 bg-slate-800 shrink-0">
                <div class="min-w-0 space-y-1.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-sky-500/15 border border-sky-400/30 text-sky-200 text-[11px] font-bold">
                        <i class="fas {{ $analysis['archetype']['badge_icon'] }} text-[10px]"></i>
                        <span>{{ $analysis['archetype']['title'] }}</span>
                    </span>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight truncate">{{ $student->full_name }}</h1>
                    <p class="text-xs text-slate-300 leading-snug">
                        {{ $analysis['archetype']['tagline'] }} · Kelas <strong class="text-white">{{ $classroomName }}</strong> ({{ $analysis['database_identity']['school_name'] }})
                        @if(!empty($analysis['vocational_major']))
                        · Jurusan <strong class="text-amber-300">{{ $analysis['vocational_major']['code'] }}</strong>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.pdf', $student) : route('admin.dna.pdf', $student) }}"
                   class="px-4 py-2 rounded-lg bg-white hover:bg-slate-100 text-slate-900 font-bold text-xs transition inline-flex items-center gap-2">
                    <i class="fas fa-file-pdf text-rose-600"></i>
                    <span>Cetak Laporan DNA (PDF)</span>
                </a>
                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.index') : route('admin.dna.index') }}"
                   class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-100 font-semibold text-xs transition inline-flex items-center gap-1.5">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    <span>Daftar Siswa</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Strip Telemetri 4 Metrik Kunci (Satu Bingkai Hairline) --}}
    <div class="bg-white border border-slate-300 rounded-xl grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 lg:divide-x divide-slate-200 overflow-hidden text-xs">
        <div class="px-5 py-3.5">
            <div class="text-slate-500 font-semibold">Rerata Nilai Akademik</div>
            <div class="text-lg font-extrabold text-slate-900 mt-0.5 tabular-nums">
                {{ ($sp['student_avg'] ?? null) !== null ? $sp['student_avg'] : '-' }}
                <span class="text-xs font-medium text-slate-500">({{ $analysis['metrics']['total_grades'] }} Mapel)</span>
            </div>
        </div>
        <div class="px-5 py-3.5">
            <div class="text-slate-500 font-semibold">Kehadiran Sekolah (RFID)</div>
            <div class="text-lg font-extrabold text-emerald-700 mt-0.5 tabular-nums">
                {{ $analysis['metrics']['attendance_display'] ?? '-' }}
            </div>
        </div>
        <div class="px-5 py-3.5">
            <div class="text-slate-500 font-semibold">Tugas LMS &amp; Ujian CBT</div>
            <div class="text-lg font-extrabold text-slate-900 mt-0.5 tabular-nums">
                {{ $ev['lms_count'] ?? 0 }} Tugas · CBT {{ ($ev['cbt_avg'] ?? null) !== null ? $ev['cbt_avg'] : '-' }}
            </div>
        </div>
        <div class="px-5 py-3.5">
            <div class="text-slate-500 font-semibold">Kelengkapan Sumber Data</div>
            <div class="flex items-center gap-2.5 mt-1">
                <span class="text-base font-extrabold text-sky-800 tabular-nums">{{ $analysis['confidence_score'] }}%</span>
                <div class="flex-1 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-sky-600 h-1.5 rounded-full" style="width: {{ $analysis['confidence_score'] }}%"></div>
                </div>
            </div>
            <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ $analysis['confidence_label'] }}</div>
        </div>
    </div>

    {{-- Grid Utama Asimetris (4 Kolom Sumbu Potensi | 8 Kolom Ruang Kerja Tab) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- KOLOM KIRI: RADAR + 6 SUMBU + IDENTITAS RESMI --}}
        <aside class="lg:col-span-4 bg-white border border-slate-300 rounded-xl divide-y divide-slate-200 overflow-hidden">
            {{-- Radar Chart --}}
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-sky-700 text-xs"></i>
                        <span>Peta Radar 6 Sumbu Potensi</span>
                    </h2>
                    <span class="text-[11px] font-semibold text-slate-500 tabular-nums">Skala 0–100</span>
                </div>
                <div class="relative w-full aspect-square max-w-[300px] mx-auto py-1">
                    <canvas id="dnaRadarChart"></canvas>
                </div>
            </div>

            {{-- Rincian Skor 6 Dimensi --}}
            <div class="p-5 space-y-3.5 text-xs">
                <h3 class="text-sm font-bold text-slate-900">Rincian Skor Tiap Sumbu</h3>
                @foreach($dimRows as $dr)
                @php $val = $analysis['scores'][$dr['key']]; @endphp
                <div class="space-y-1">
                    <div class="flex items-center justify-between font-bold">
                        <span class="text-slate-800 flex items-center gap-1.5">
                            <i class="fas {{ $dr['icon'] }} text-slate-500 w-3.5 text-center"></i>
                            <span>{{ $dr['label'] }}</span>
                        </span>
                        <span class="text-slate-900 tabular-nums font-mono">{{ $val }}/100</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $dr['bar'] }} h-1.5 rounded-full" style="width: {{ $val }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-snug">{{ $analysis['dimension_sources'][$dr['key']] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Identitas Resmi & Pengampu --}}
            <div class="p-5 bg-slate-50/60 space-y-2.5 text-xs">
                <h3 class="text-sm font-bold text-slate-900">Identitas &amp; Pengampu</h3>
                <dl class="space-y-1.5 text-slate-600">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">NISN / NIS</dt>
                        <dd class="font-semibold text-slate-900 tabular-nums text-right">{{ $student->nisn ?: '-' }} / {{ $analysis['database_identity']['student_nis'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Orang Tua / Wali</dt>
                        <dd class="font-semibold text-slate-900 text-right">{{ $analysis['database_identity']['parent_name'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Wali Kelas</dt>
                        <dd class="font-semibold text-slate-900 text-right">{{ $analysis['database_identity']['homeroom_teacher'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Kepala Sekolah</dt>
                        <dd class="font-semibold text-slate-900 text-right">{{ $analysis['database_identity']['principal_name'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Poin Apresiasi</dt>
                        <dd class="font-semibold text-slate-900 tabular-nums text-right">{{ $analysis['metrics']['reputation_points'] }} Poin</dd>
                    </div>
                </dl>
            </div>
        </aside>

        {{-- KOLOM KANAN: RUANG KERJA TAB ANALISIS, KARIER & REKAM JEJAK --}}
        <section class="lg:col-span-8 space-y-4">
            {{-- Tab Bar Datar --}}
            <div class="bg-white border border-slate-300 rounded-xl px-4 flex flex-wrap items-center gap-6 text-xs sm:text-sm font-bold">
                <button type="button" @click="activeTab = 'analisis'"
                        :class="activeTab === 'analisis' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                        class="py-3.5 border-b-2 transition inline-flex items-center gap-2">
                    <i class="fas fa-microchip text-xs"></i>
                    <span>Analisis ClassAI &amp; Peta Mapel</span>
                </button>
                <button type="button" @click="activeTab = 'karier'"
                        :class="activeTab === 'karier' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                        class="py-3.5 border-b-2 transition inline-flex items-center gap-2">
                    <i class="fas fa-compass text-xs"></i>
                    <span>Arah Studi &amp; Karier</span>
                </button>
                <button type="button" @click="activeTab = 'rekam'"
                        :class="activeTab === 'rekam' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                        class="py-3.5 border-b-2 transition inline-flex items-center gap-2">
                    <i class="fas fa-database text-xs"></i>
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
                <div class="bg-white border border-slate-300 rounded-xl overflow-hidden">
                    <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">
                            {{ $analysis['school_type'] === 'SMK' ? 'Bidang Keahlian, Karier & PKL Industri Prioritas' : ($analysis['school_type'] === 'SMP' ? 'Rekomendasi Jalur Pendidikan Lanjutan (SMA / SMK)' : 'Pilihan Rumpun Program Studi Perguruan Tinggi') }}
                        </h3>
                    </div>

                    <div class="p-5 space-y-4 text-xs">
                        @if($analysis['school_type'] === 'SMK')
                            @if(!empty($analysis['recommendations']['career_tracks']))
                            <div class="divide-y divide-slate-200 border border-slate-200 rounded-lg">
                                @foreach($analysis['recommendations']['career_tracks'] as $track)
                                <div class="p-4 space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-bold text-slate-900 text-sm">{{ $track['title'] }}</span>
                                        <span class="px-2.5 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-900 font-bold text-[11px]">{{ $track['relevance'] }}</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed">{{ $track['description'] }}</p>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            @if(!empty($analysis['recommendations']['pkl_recommendation']))
                            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-1">
                                <p class="font-bold text-slate-900">Rekomendasi Tempat Praktik Kerja Lapangan (PKL)</p>
                                <p class="text-slate-700 leading-relaxed">{{ $analysis['recommendations']['pkl_recommendation'] }}</p>
                            </div>
                            @endif

                            @if(!empty($analysis['recommendations']['certifications']))
                            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-1.5">
                                <p class="font-bold text-slate-900">Rekomendasi Sertifikasi Kompetensi (BNSP / Industri)</p>
                                <ul class="list-disc list-inside text-slate-700 space-y-1">
                                    @foreach($analysis['recommendations']['certifications'] as $cert)
                                    <li>{{ $cert }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        @else
                            @if(!empty($analysis['recommendations']['college_majors']))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($analysis['recommendations']['college_majors'] as $major)
                                <div class="p-4 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1.5">
                                    <span class="inline-block px-2 py-0.5 rounded bg-sky-50 border border-sky-200 text-sky-900 font-bold text-[11px]">
                                        {{ $major['cluster'] }}
                                    </span>
                                    <p class="font-bold text-slate-900 text-sm">{{ $major['major'] }}</p>
                                    <p class="text-[11px] text-emerald-700 font-semibold">Tingkat Kesesuaian: {{ $major['readiness'] }}</p>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        @endif

                        @if(!empty($analysis['recommendations']['learning_strategy']))
                        <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-1">
                            <p class="font-bold text-slate-900">Strategi Belajar yang Disarankan</p>
                            <p class="text-slate-700 leading-relaxed">{{ $analysis['recommendations']['learning_strategy'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TAB 3: REKAM JEJAK LENGKAP 360° --}}
            <div x-show="activeTab === 'rekam'" x-cloak class="space-y-5">
                {{-- Daftar Seluruh Nilai Mapel --}}
                <div class="bg-white border border-slate-300 rounded-xl overflow-hidden">
                    <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">Daftar Capaian Seluruh Mata Pelajaran</h3>
                        <span class="text-xs font-semibold text-slate-600 tabular-nums">{{ $sp['subject_count'] ?? 0 }} Mapel</span>
                    </div>
                    @if(!empty($sp['subjects']))
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5">Mata Pelajaran</th>
                                    <th class="px-4 py-2.5">Rumpun Dimensi</th>
                                    <th class="px-4 py-2.5 text-right">Nilai Siswa</th>
                                    <th class="px-4 py-2.5 text-right">Rerata Sekolah</th>
                                    <th class="px-4 py-2.5 text-right">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($sp['subjects'] as $row)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $row['subject_name'] }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $dimLabels[$row['dimension']] ?? $row['dimension'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-bold text-slate-900 tabular-nums">{{ $row['score'] }}</td>
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
                <div class="bg-white border border-slate-300 rounded-xl divide-y divide-slate-200 text-xs">
                    <div class="p-5 space-y-2">
                        <h4 class="font-bold text-slate-900">Prestasi &amp; Penghargaan Tercatat</h4>
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
                        <h4 class="font-bold text-slate-900">Tugas Akhir / Penelitian &amp; Praktik Kerja Lapangan (PKL)</h4>
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
                        <h4 class="font-bold text-slate-900">Keaktifan Ekstrakurikuler</h4>
                        @if(!empty($ev['extracurriculars']) && $ev['extracurriculars']->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach($ev['extracurriculars'] as $ek)
                            <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200 font-semibold text-slate-800">
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
                backgroundColor: 'rgba(2, 132, 199, 0.16)',
                borderColor: '#0284c7',
                borderWidth: 2,
                pointBackgroundColor: '#0f172a',
                pointBorderColor: '#ffffff',
                pointRadius: 3.5
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
                        color: '#334155'
                    }
                }
            },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endsection
