@extends('layouts.admin')

@section('title', 'Profil DNA Akademik 360° - ' . $student->full_name)

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Top Navigation & Header --}}
    <div class="bg-gradient-to-r {{ $analysis['archetype']['color'] }} rounded-2xl p-6 sm:p-7 text-white relative overflow-hidden shadow-sm">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4 blur-2xl"></div>
        <div class="relative">
            <div class="flex items-center text-sm text-white/70 mb-2 gap-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.dna.index') }}" class="hover:text-white transition">DNA Akademik</a>
                <span>/</span>
                <span class="text-white font-semibold">Profil 360°</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="px-3 py-1 bg-white/20 rounded-full text-[11px] font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                        <i class="fas {{ $analysis['archetype']['badge_icon'] }}"></i> {{ $analysis['archetype']['title'] }}
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black">{{ $student->full_name }}</h1>
                    <p class="text-xs sm:text-sm text-purple-100 font-medium">
                        {{ $analysis['archetype']['tagline'] }} &bull; Kelas: <b>{{ $student->currentClassroom->first()->name ?? '-' }}</b> ({{ $student->school->name ?? '-' }})
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dna.pdf', $student) }}" class="px-5 py-2.5 bg-white text-indigo-900 hover:bg-gray-50 rounded-xl font-bold transition flex items-center gap-2 text-xs shadow-md active:scale-95">
                        <i class="fas fa-file-pdf text-rose-600"></i> Cetak Laporan DNA (PDF)
                    </a>
                    <a href="{{ route('admin.dna.index') }}" class="px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-semibold transition flex items-center gap-2 text-xs shadow-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid 360° Potensi --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- Kolom Kiri (5 Kolom): Radar Chart & Dimensi --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Radar Chart Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-fuchsia-600"></i> Peta Radar 6 Sumbu Potensi
                    </h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                        Skor 0 - 100
                    </span>
                </div>

                <div class="relative w-full aspect-square max-w-[340px] mx-auto py-2">
                    <canvas id="dnaRadarChart"></canvas>
                </div>

                {{-- Status Akurasi Data --}}
                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between text-xs">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Tingkat Kalibrasi Data:</p>
                        <p class="font-bold text-gray-800">{{ $analysis['confidence_label'] }} ({{ $analysis['confidence_score'] }}%)</p>
                    </div>
                    <div class="w-24 bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full bg-gradient-to-r from-indigo-500 to-fuchsia-500" style="width: {{ $analysis['confidence_score'] }}%"></div>
                    </div>
                </div>
            </div>

            {{-- 6 Dimension Meters --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fas fa-sliders text-indigo-600"></i> Rincian Skor Tiap Dimensi
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🧮 Logika & Analitik</span>
                            <span class="text-indigo-600">{{ $analysis['scores']['logic'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $analysis['scores']['logic'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🗣️ Komunikasi & Bahasa</span>
                            <span class="text-indigo-600">{{ $analysis['scores']['communication'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $analysis['scores']['communication'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🛠️ Keahlian Vokasi & Terapan</span>
                            <span class="text-indigo-600">{{ $analysis['scores']['technical'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $analysis['scores']['technical'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🤝 Sosial & Kepemimpinan</span>
                            <span class="text-indigo-600">{{ $analysis['scores']['social'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $analysis['scores']['social'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🎨 Kreativitas & Inovasi</span>
                            <span class="text-indigo-600">{{ $analysis['scores']['creative'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $analysis['scores']['creative'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">⏱️ Kedisiplinan & Ketekunan</span>
                            <span class="text-indigo-600">{{ $analysis['scores']['discipline'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-rose-500 h-2 rounded-full" style="width: {{ $analysis['scores']['discipline'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan (7 Kolom): Analisis & Rekomendasi Karir/Kuliah --}}
        <div class="lg:col-span-7 space-y-6">
            
            {{-- Deskripsi Archetype --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-fingerprint text-fuchsia-600"></i> Karakteristik Profil Belajar
                </h3>
                <p class="text-xs text-gray-600 leading-relaxed">
                    {{ $analysis['archetype']['description'] }}
                </p>
                @if(isset($analysis['recommendations']['student_dream']))
                <div class="p-3 bg-purple-50 rounded-xl text-xs text-purple-900">
                    <b>Cita-cita & Minat Mandiri Siswa:</b> "{{ $analysis['recommendations']['student_dream'] }}"
                </div>
                @endif
            </div>

            {{-- Rekomendasi Karir / Kuliah --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fas fa-compass text-indigo-600"></i> Rekomendasi Masa Depan (AI Prediction Engine)
                </h3>

                @if($analysis['school_type'] === 'SMK')
                    {{-- Jalur Karir SMK --}}
                    @if(isset($analysis['recommendations']['career_tracks']))
                    <div class="space-y-3">
                        <p class="text-xs font-bold text-gray-700">Bidang Karir & Keahlian Industri Prioritas:</p>
                        @foreach($analysis['recommendations']['career_tracks'] as $track)
                        <div class="p-3.5 bg-indigo-50/50 rounded-xl border border-indigo-100 space-y-1 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-indigo-950">{{ $track['title'] }}</span>
                                <span class="px-2 py-0.5 bg-indigo-200 text-indigo-800 rounded font-bold text-[10px]">{{ $track['relevance'] }}</span>
                            </div>
                            <p class="text-gray-600 leading-relaxed">{{ $track['description'] }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if(isset($analysis['recommendations']['pkl_recommendation']))
                    <div class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-100 text-xs space-y-1">
                        <p class="font-bold text-emerald-900"><i class="fas fa-building mr-1"></i> Tempat Praktik Kerja Lapangan (PKL) Idaman:</p>
                        <p class="text-emerald-800 leading-relaxed">{{ $analysis['recommendations']['pkl_recommendation'] }}</p>
                    </div>
                    @endif

                    @if(isset($analysis['recommendations']['certifications']))
                    <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-100 text-xs space-y-1">
                        <p class="font-bold text-amber-900"><i class="fas fa-certificate mr-1"></i> Rekomendasi Sertifikasi Profesi (BNSP / Vendor):</p>
                        <ul class="list-disc list-inside text-amber-800 space-y-0.5">
                            @foreach($analysis['recommendations']['certifications'] as $cert)
                            <li>{{ $cert }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                @else
                    {{-- Rumpun Jurusan Kuliah SMA --}}
                    @if(isset($analysis['recommendations']['college_majors']))
                    <div class="space-y-3">
                        <p class="text-xs font-bold text-gray-700">Pilihan Rumpun Jurusan Perguruan Tinggi (SNBP / SNBT):</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($analysis['recommendations']['college_majors'] as $major)
                            <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1">
                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded font-bold text-[10px] uppercase">
                                    {{ $major['cluster'] }}
                                </span>
                                <p class="font-bold text-gray-900 mt-1">{{ $major['major'] }}</p>
                                <p class="text-[10px] text-emerald-600 font-bold">Tingkat Kesiapan: {{ $major['readiness'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </div>

            {{-- 5 Pilar Data Nyata yang Membentuk DNA --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fas fa-database text-indigo-600"></i> Rekam Jejak Data Riil PembdaHUB
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-gray-400 text-[10px] font-bold">Total Nilai</p>
                        <p class="text-base font-bold text-gray-900 mt-0.5">{{ $analysis['metrics']['total_grades'] }} Record</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-gray-400 text-[10px] font-bold">Kehadiran RFID</p>
                        <p class="text-base font-bold text-emerald-600 mt-0.5">{{ $analysis['metrics']['attendance_rate'] }}%</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-gray-400 text-[10px] font-bold">Rerata CBT</p>
                        <p class="text-base font-bold text-indigo-600 mt-0.5">{{ $analysis['metrics']['cbt_average'] ?: '-' }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-gray-400 text-[10px] font-bold">Reputasi</p>
                        <p class="text-base font-bold text-purple-600 mt-0.5">{{ $analysis['metrics']['reputation_points'] }} Poin</p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dnaRadarChart').getContext('2d');
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: [
                'Logika & Analitik',
                'Komunikasi & Bahasa',
                'Keahlian Vokasi',
                'Sosial & Kepemimpinan',
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
                backgroundColor: 'rgba(147, 51, 234, 0.25)',
                borderColor: 'rgba(147, 51, 234, 1)',
                borderWidth: 2.5,
                pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(79, 70, 229, 1)',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: { color: 'rgba(0, 0, 0, 0.08)' },
                    grid: { color: 'rgba(0, 0, 0, 0.08)' },
                    suggestedMin: 40,
                    suggestedMax: 100,
                    ticks: { display: false, stepSize: 20 },
                    pointLabels: {
                        font: { size: 10, weight: 'bold' },
                        color: '#334155'
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endsection
