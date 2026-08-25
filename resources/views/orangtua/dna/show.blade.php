@extends('layouts.orangtua')

@section('title', 'DNA Potensi Anak - ' . $student->full_name)

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Hero Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-2xl p-6 sm:p-7 text-white relative overflow-hidden shadow-md border border-slate-800">
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center text-xs text-slate-300 font-semibold mb-2.5 gap-2">
                <a href="{{ route('orangtua.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span class="text-slate-500">/</span>
                <span class="text-purple-300 font-bold">DNA Potensi Anak</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden border-2 border-white/30 shadow-lg flex-shrink-0 bg-slate-800">
                        <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                    </div>
                    <div class="space-y-1.5">
                        <span class="px-3 py-1 bg-gradient-to-r {{ $analysis['archetype']['color'] }} text-white rounded-full text-[11px] font-black uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm">
                            <i class="fas {{ $analysis['archetype']['badge_icon'] }}"></i> {{ $analysis['archetype']['title'] }}
                        </span>
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $student->full_name }}</h1>
                        <p class="text-xs sm:text-sm text-slate-200 font-medium">
                            Profil Bakat & Karakter Belajar: <b class="text-white">"{{ $analysis['archetype']['tagline'] }}"</b>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('orangtua.anak.dna.pdf', $student) }}" class="px-5 py-2.5 bg-white text-indigo-900 hover:bg-gray-50 rounded-xl font-bold transition flex items-center gap-2 text-xs shadow-md active:scale-95">
                        <i class="fas fa-file-pdf text-rose-600"></i> Cetak Laporan DNA (PDF)
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid 360° Potensi --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- Kolom Kiri: Radar Chart --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-fuchsia-600"></i> Peta Radar 6 Sumbu Potensi Anak
                    </h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                        Skor 0 - 100
                    </span>
                </div>

                <div class="relative w-full aspect-square max-w-[320px] mx-auto py-2">
                    <canvas id="parentDnaChart"></canvas>
                </div>

                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between text-xs">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Akurasi Kalibrasi Data:</p>
                        <p class="font-bold text-gray-800">{{ $analysis['confidence_score'] }}% ({{ $analysis['confidence_label'] }})</p>
                    </div>
                    <div class="w-20 bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full bg-gradient-to-r from-indigo-500 to-fuchsia-500" style="width: {{ $analysis['confidence_score'] }}%"></div>
                    </div>
                </div>
            </div>

            {{-- 6 Sumbu --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3 text-xs">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2">Rincian Capaian Dimensi Anak:</h3>
                <div class="space-y-2">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="font-semibold text-gray-700">🧮 Logika & Daya Analisis</span>
                        <span class="font-bold text-indigo-600">{{ $analysis['scores']['logic'] }}/100</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="font-semibold text-gray-700">🗣️ Komunikasi & Bahasa</span>
                        <span class="font-bold text-indigo-600">{{ $analysis['scores']['communication'] }}/100</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="font-semibold text-gray-700">🛠️ Keterampilan Vokasi / Praktik</span>
                        <span class="font-bold text-indigo-600">{{ $analysis['scores']['technical'] }}/100</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="font-semibold text-gray-700">🤝 Sosial & Karakter</span>
                        <span class="font-bold text-indigo-600">{{ $analysis['scores']['social'] }}/100</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="font-semibold text-gray-700">🎨 Kreativitas & Inovasi</span>
                        <span class="font-bold text-indigo-600">{{ $analysis['scores']['creative'] }}/100</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="font-semibold text-gray-700">⏱️ Kedisiplinan & Ketekunan</span>
                        <span class="font-bold text-indigo-600">{{ $analysis['scores']['discipline'] }}/100</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Panduan Orang Tua & Masa Depan --}}
        <div class="lg:col-span-7 space-y-6">
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-heart text-rose-500"></i> Mengenal Potensi Anak Anda
                </h3>
                <p class="text-xs text-gray-600 leading-relaxed">
                    {{ $analysis['archetype']['description'] }}
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-indigo-600"></i> Rekomendasi Pilihan Masa Depan Anak
                </h3>

                @if($analysis['school_type'] === 'SMK')
                    @if(isset($analysis['recommendations']['career_tracks']))
                    <div class="space-y-3">
                        <p class="text-xs font-bold text-gray-700">Peluang Karier Industri yang Sangat Cocok:</p>
                        @foreach($analysis['recommendations']['career_tracks'] as $track)
                        <div class="p-3.5 bg-indigo-50/60 rounded-xl border border-indigo-100 text-xs space-y-1">
                            <p class="font-bold text-indigo-950">{{ $track['title'] }}</p>
                            <p class="text-gray-600">{{ $track['description'] }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif
                @else
                    @if(isset($analysis['recommendations']['college_majors']))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($analysis['recommendations']['college_majors'] as $major)
                        <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded font-bold text-[10px] uppercase">{{ $major['cluster'] }}</span>
                            <p class="font-bold text-gray-900 mt-1">{{ $major['major'] }}</p>
                            <p class="text-[10px] text-emerald-600 font-bold mt-0.5">Kesiapan: {{ $major['readiness'] }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif
                @endif
            </div>

            <div class="p-5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100 text-xs text-blue-950 space-y-2">
                <p class="font-bold flex items-center gap-2">
                    <i class="fas fa-lightbulb text-amber-500"></i> Tips Pendampingan untuk Orang Tua:
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Dukung keunggulan alami ananda dengan memberikan ruang eksplorasi pada bidang yang paling diminatinya. Jika ada sumbu yang masih berkembang, konsultasikan dengan wali kelas saat sesi penerimaan rapor.
                </p>
            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('parentDnaChart').getContext('2d');
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: [
                'Logika',
                'Komunikasi',
                'Keahlian Vokasi',
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
                backgroundColor: 'rgba(147, 51, 234, 0.25)',
                borderColor: 'rgba(147, 51, 234, 1)',
                borderWidth: 2.5,
                pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                pointBorderColor: '#fff',
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
                    pointLabels: { font: { size: 10, weight: 'bold' }, color: '#334155' }
                }
            },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endsection
