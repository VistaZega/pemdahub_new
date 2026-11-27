@extends('layouts.siswa')

@section('title', 'DNA Akademik & Potensi Belajar 360° Saya')

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
    {{-- Header Dossier Siswa (Solid Slate, Tanpa Emoji/Gradien Dekoratif) --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl px-5 py-5 sm:px-6 text-white">
        <div class="flex items-center text-xs text-slate-300 font-medium mb-3 gap-2">
            <a href="{{ route('siswa.dashboard') }}" class="hover:text-white transition">Dashboard</a>
            <span class="text-slate-600">/</span>
            <span class="text-sky-300 font-semibold">DNA Akademik 360°</span>
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
                        {{ $analysis['archetype']['tagline'] }}
                        @if(!empty($analysis['vocational_major']))
                        · Jurusan <strong class="text-amber-300">{{ $analysis['vocational_major']['label'] }}</strong>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button type="button" @click="activeTab = 'minat'"
                        class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-100 font-semibold text-xs transition inline-flex items-center gap-1.5">
                    <i class="fas fa-pen-to-square text-sky-400 text-[11px]"></i>
                    <span>{{ $analysis['diagnostic'] ? 'Ubah Minat & Cita-Cita' : 'Isi Minat & Cita-Cita' }}</span>
                </button>
                <a href="{{ route('siswa.dna.pdf') }}"
                   class="px-4 py-2 rounded-lg bg-white hover:bg-slate-100 text-slate-900 font-bold text-xs transition inline-flex items-center gap-2">
                    <i class="fas fa-file-pdf text-rose-600"></i>
                    <span>Unduh Rapor DNA (PDF)</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-semibold flex items-center gap-2">
        <i class="fas fa-check-circle text-emerald-700"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

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
                        <span>Peta Radar 6 Sumbu Potensi</span>
                    </h2>
                    <span class="text-[11px] font-semibold text-slate-500 tabular-nums">Skala 0–100</span>
                </div>
                <div class="relative w-full aspect-square max-w-[300px] mx-auto py-1">
                    <canvas id="studentDnaChart"></canvas>
                </div>
            </div>

            <div class="p-5 space-y-3.5 text-xs">
                <h3 class="text-sm font-bold text-slate-900">Rincian Capaian Tiap Sumbu</h3>
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
        </aside>

        {{-- KOLOM KANAN: RUANG KERJA 3 TAB --}}
        <section class="lg:col-span-8 space-y-4">
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
                    <span>Peluang Studi &amp; Karier</span>
                </button>
                <button type="button" @click="activeTab = 'minat'"
                        :class="activeTab === 'minat' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                        class="py-3.5 border-b-2 transition inline-flex items-center gap-2">
                    <i class="fas fa-pen-to-square text-xs"></i>
                    <span>Minat &amp; Cita-Cita Saya</span>
                </button>
            </div>

            {{-- TAB 1: ANALISIS CLASSAI & PETA MAPEL --}}
            <div x-show="activeTab === 'analisis'" class="space-y-4">
                @if(!$analysis['diagnostic'])
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-300 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="text-amber-950">
                        <strong>Lengkapi Minat &amp; Cita-Cita Anda:</strong> Isi 3 pertanyaan singkat agar ClassAI dapat menyelaraskan analisis dengan tujuan masa depan Anda.
                    </div>
                    <button type="button" @click="activeTab = 'minat'"
                            class="px-3 py-1.5 rounded-lg bg-amber-800 hover:bg-amber-900 text-white font-bold transition shrink-0">
                        Isi Sekarang
                    </button>
                </div>
                @endif

                @include('partials.student-dna-ai-panel', [
                    'analysis'   => $analysis,
                    'aiEndpoint' => route('siswa.dna.generate-ai'),
                    'viewerRole' => 'siswa',
                ])
            </div>

            {{-- TAB 2: PELUANG STUDI & KARIER --}}
            <div x-show="activeTab === 'karier'" x-cloak class="bg-white border border-slate-300 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $analysis['school_type'] === 'SMK' ? 'Peluang Karier Industri, PKL & Sertifikasi Kejuruan' : ($analysis['school_type'] === 'SMP' ? 'Rekomendasi Jalur Sekolah Lanjutan (SMA / SMK)' : 'Peluang Rumpun Studi Perguruan Tinggi') }}
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
                            <p class="font-bold text-slate-900">Rekomendasi Tempat PKL / Industri Mitra</p>
                            <p class="text-slate-700 leading-relaxed">{{ $analysis['recommendations']['pkl_recommendation'] }}</p>
                        </div>
                        @endif
                        @if(!empty($analysis['recommendations']['certifications']))
                        <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-1.5">
                            <p class="font-bold text-slate-900">Sertifikasi Kompetensi yang Disarankan</p>
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

            {{-- TAB 3: FORM MINAT & CITA-CITA MANDIRI --}}
            <div x-show="activeTab === 'minat'" x-cloak class="bg-white border border-slate-300 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-bold text-slate-900">Penyelarasan Mandiri Minat &amp; Cita-Cita</h3>
                    @if($analysis['diagnostic'])
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-50 border border-emerald-200 text-emerald-900">
                        Sudah Diisi
                    </span>
                    @else
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-slate-100 border border-slate-300 text-slate-800">
                        Belum Diisi
                    </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('siswa.dna.diagnostic.save') }}" class="p-5 space-y-4 text-xs">
                    @csrf
                    <p class="text-slate-600 leading-relaxed">
                        Isi preferensi belajar, rumpun minat, dan cita-cita Anda agar analisis ClassAI dapat mencocokkan target masa depan dengan capaian mata pelajaran saat ini.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1.5">Gaya Belajar &amp; Kerja Favorit *</label>
                            <select name="work_style_preference" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 font-semibold text-slate-800">
                                <option value="praktik" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'praktik' ? 'selected' : '' }}>Praktik Langsung &amp; Eksperimen Fisik</option>
                                <option value="analisis" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'analisis' ? 'selected' : '' }}>Menganalisis Masalah &amp; Riset Logika</option>
                                <option value="kreatif" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'kreatif' ? 'selected' : '' }}>Membuat Karya Kreatif &amp; Desain Visual</option>
                                <option value="kolaborasi" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'kolaborasi' ? 'selected' : '' }}>Diskusi Kelompok &amp; Presentasi Publik</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-800 mb-1.5">Rumpun Minat Paling Disukai *</label>
                            <select name="favorite_subject_cluster" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 font-semibold text-slate-800">
                                <option value="eksak" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'eksak' ? 'selected' : '' }}>Sains, Matematika &amp; Komputer</option>
                                <option value="vokasi" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'vokasi' ? 'selected' : '' }}>Rekayasa Mesin, Otomotif &amp; Jaringan</option>
                                <option value="bahasa" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'bahasa' ? 'selected' : '' }}>Bahasa, Sastra &amp; Komunikasi</option>
                                <option value="sosial" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'sosial' ? 'selected' : '' }}>Hukum, Bisnis, Manajemen &amp; Sosial</option>
                                <option value="seni" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'seni' ? 'selected' : '' }}>Desain Grafis, Multimedia &amp; Seni</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-800 mb-1.5">Cita-Cita / Profesi Impian Masa Depan *</label>
                            <input type="text" name="career_aspiration" value="{{ old('career_aspiration', $analysis['diagnostic']->career_aspiration ?? '') }}" required
                                   placeholder="Contoh: Pengembang Perangkat Lunak / Dokter Umum / Teknisi Jaringan / Akuntan"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 font-semibold text-slate-900">
                        </div>
                    </div>

                    <input type="hidden" name="logic_self_score" value="{{ $analysis['scores']['logic'] }}">
                    <input type="hidden" name="creative_self_score" value="{{ $analysis['scores']['creative'] }}">
                    <input type="hidden" name="communication_self_score" value="{{ $analysis['scores']['communication'] }}">
                    <input type="hidden" name="technical_self_score" value="{{ $analysis['scores']['technical'] }}">
                    <input type="hidden" name="social_self_score" value="{{ $analysis['scores']['social'] }}">
                    <input type="hidden" name="discipline_self_score" value="{{ $analysis['scores']['discipline'] }}">

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition inline-flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Simpan &amp; Selaraskan Profil DNA</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('studentDnaChart');
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
