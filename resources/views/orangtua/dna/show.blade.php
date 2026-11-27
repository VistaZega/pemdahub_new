@extends('layouts.orangtua')

@section('title', 'DNA Potensi Anak - ' . $student->full_name)

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
@php
    $sp = $analysis['subject_profile'] ?? [];
    $ev = $analysis['evidence_360'] ?? [];
    $dimRows = [
        ['key' => 'logic', 'label' => 'Logika & Analitik', 'icon' => 'fa-brain', 'bar' => 'bg-sky-600'],
        ['key' => 'communication', 'label' => 'Komunikasi & Bahasa', 'icon' => 'fa-comments', 'bar' => 'bg-emerald-600'],
        ['key' => 'technical', 'label' => 'Keahlian Teknis & Vokasi', 'icon' => 'fa-wrench', 'bar' => 'bg-indigo-600'],
        ['key' => 'social', 'label' => 'Sosial & Kepemimpinan', 'icon' => 'fa-users', 'bar' => 'bg-amber-600'],
        ['key' => 'creative', 'label' => 'Kreativitas & Inovasi', 'icon' => 'fa-palette', 'bar' => 'bg-purple-600'],
        ['key' => 'discipline', 'label' => 'Kedisiplinan & Ketekunan', 'icon' => 'fa-clock', 'bar' => 'bg-rose-600'],
    ];
@endphp

<div class="space-y-5" x-data="{ activeTab: 'analisis' }">
    {{-- Header Dossier Anak (Solid Slate, Kontras Tinggi) --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl px-5 py-5 sm:px-6 text-white">
        <div class="flex items-center text-xs text-slate-300 font-medium mb-3 gap-2">
            <a href="{{ route('orangtua.dashboard') }}" class="hover:text-white transition">Dashboard</a>
            <span class="text-slate-600">/</span>
            <span class="text-sky-300 font-semibold">DNA Potensi Anak 360°</span>
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
                        {{ $analysis['archetype']['tagline'] }} · {{ $analysis['database_identity']['school_name'] }}
                        @if(!empty($analysis['vocational_major']))
                        · Jurusan <strong class="text-amber-300">{{ $analysis['vocational_major']['code'] }}</strong>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('orangtua.anak.dna.pdf', $student) }}"
                   class="px-4 py-2 rounded-lg bg-white hover:bg-slate-100 text-slate-900 font-bold text-xs transition inline-flex items-center gap-2">
                    <i class="fas fa-file-pdf text-rose-600"></i>
                    <span>Cetak Laporan DNA (PDF)</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Strip Telemetri 4 Metrik Kunci --}}
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

    {{-- Grid Utama Asimetris --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- KOLOM KIRI: RADAR + 6 SUMBU --}}
        <aside class="lg:col-span-4 bg-white border border-slate-300 rounded-xl divide-y divide-slate-200 overflow-hidden">
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-sky-700 text-xs"></i>
                        <span>Peta Radar 6 Sumbu Potensi Anak</span>
                    </h2>
                    <span class="text-[11px] font-semibold text-slate-500 tabular-nums">Skala 0–100</span>
                </div>
                <div class="relative w-full aspect-square max-w-[300px] mx-auto py-1">
                    <canvas id="parentDnaChart"></canvas>
                </div>
            </div>

            <div class="p-5 space-y-3 text-xs">
                <h3 class="text-sm font-bold text-slate-900">Rincian Capaian Dimensi Anak</h3>
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
                </div>
                @endforeach
            </div>
        </aside>

        {{-- KOLOM KANAN: TAB ANALISIS & ARAH MASA DEPAN --}}
        <section class="lg:col-span-8 space-y-4">
            <div class="bg-white border border-slate-300 rounded-xl px-4 flex flex-wrap items-center gap-6 text-xs sm:text-sm font-bold">
                <button type="button" @click="activeTab = 'analisis'"
                        :class="activeTab === 'analisis' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                        class="py-3.5 border-b-2 transition inline-flex items-center gap-2">
                    <i class="fas fa-microchip text-xs"></i>
                    <span>Analisis ClassAI &amp; Pendampingan</span>
                </button>
                <button type="button" @click="activeTab = 'karier'"
                        :class="activeTab === 'karier' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                        class="py-3.5 border-b-2 transition inline-flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-xs"></i>
                    <span>Rekomendasi Masa Depan Anak</span>
                </button>
            </div>

            <div x-show="activeTab === 'analisis'" class="space-y-4">
                @include('partials.student-dna-ai-panel', [
                    'analysis'   => $analysis,
                    'aiEndpoint' => route('orangtua.anak.dna.generate-ai', $student),
                    'viewerRole' => 'orangtua',
                ])
            </div>

            <div x-show="activeTab === 'karier'" x-cloak class="bg-white border border-slate-300 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $analysis['school_type'] === 'SMK' ? 'Peluang Karier Industri & PKL yang Sesuai' : ($analysis['school_type'] === 'SMP' ? 'Rekomendasi Jalur Sekolah Lanjutan (SMA / SMK)' : 'Rekomendasi Pilihan Rumpun Studi Lanjutan') }}
                    </h3>
                </div>
                <div class="p-5 space-y-4 text-xs">
                    @if($analysis['school_type'] === 'SMK')
                        @if(!empty($analysis['recommendations']['career_tracks']))
                        <div class="divide-y divide-slate-200 border border-slate-200 rounded-lg">
                            @foreach($analysis['recommendations']['career_tracks'] as $track)
                            <div class="p-4 space-y-1">
                                <p class="font-bold text-slate-900 text-sm">{{ $track['title'] }}</p>
                                <p class="text-slate-600 leading-relaxed">{{ $track['description'] }}</p>
                            </div>
                            @endforeach
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
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('parentDnaChart');
    if (!el) return;
    new Chart(el.getContext('2d'), {
        type: 'radar',
        data: {
            labels: [
                'Logika',
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
                    pointLabels: { font: { size: 10, weight: '700' }, color: '#334155' }
                }
            },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endsection
