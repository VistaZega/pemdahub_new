@extends('layouts.siswa')

@section('title', 'DNA Akademik & Potensi Belajar Saya')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Hero Archetype Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-2xl p-6 sm:p-7 text-white relative overflow-hidden shadow-md border border-slate-800">
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center text-xs text-slate-300 font-semibold mb-2.5 gap-2">
                <a href="{{ route('siswa.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span class="text-slate-500">/</span>
                <span class="text-purple-300 font-bold">DNA Akademik 360°</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <span class="px-3 py-1 bg-gradient-to-r {{ $analysis['archetype']['color'] }} text-white rounded-full text-[11px] font-black uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm">
                        <i class="fas {{ $analysis['archetype']['badge_icon'] }}"></i> {{ $analysis['archetype']['title'] }}
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Halo, {{ $student->full_name }}! 👋</h1>
                    <p class="text-xs sm:text-sm text-slate-200 font-medium">
                        Tipe DNA Belajar Anda: <b class="text-white">"{{ $analysis['archetype']['tagline'] }}"</b>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('siswa.dna.pdf') }}" class="px-5 py-2.5 bg-white text-indigo-900 hover:bg-gray-50 rounded-xl font-bold transition flex items-center gap-2 text-xs shadow-md active:scale-95">
                        <i class="fas fa-file-pdf text-rose-600"></i> Unduh Rapor DNA 360°
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl shadow-xs text-xs font-semibold flex items-center gap-2">
        <i class="fas fa-check-circle text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Grid 360° Potensi --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- Kolom Kiri: Radar Chart & Sumbu --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-fuchsia-600"></i> Radar 6 Sumbu Potensi Anda
                    </h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                        Skor 0 - 100
                    </span>
                </div>

                <div class="relative w-full aspect-square max-w-[320px] mx-auto py-2">
                    <canvas id="studentDnaChart"></canvas>
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

            {{-- 6 Dimension Meters --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3 text-xs">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <i class="fas fa-sliders text-indigo-600"></i> Rincian Nilai Sumbu
                </h3>
                <div class="space-y-2.5">
                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🧮 Logika & Analitik</span>
                            <span class="text-indigo-600 font-mono">{{ $analysis['scores']['logic'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $analysis['scores']['logic'] }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🗣️ Komunikasi & Bahasa</span>
                            <span class="text-indigo-600 font-mono">{{ $analysis['scores']['communication'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $analysis['scores']['communication'] }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🛠️ Keahlian Vokasi & Praktik</span>
                            <span class="text-indigo-600 font-mono">{{ $analysis['scores']['technical'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $analysis['scores']['technical'] }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🤝 Sosial & Karakter</span>
                            <span class="text-indigo-600 font-mono">{{ $analysis['scores']['social'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $analysis['scores']['social'] }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">🎨 Kreativitas & Inovasi</span>
                            <span class="text-indigo-600 font-mono">{{ $analysis['scores']['creative'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $analysis['scores']['creative'] }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between font-bold mb-1">
                            <span class="text-gray-700">⏱️ Kedisiplinan & Presensi</span>
                            <span class="text-indigo-600 font-mono">{{ $analysis['scores']['discipline'] }}/100</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="bg-rose-500 h-1.5 rounded-full" style="width: {{ $analysis['scores']['discipline'] }}%"></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Rekomendasi Karir & Form Diagnostik Minat --}}
        <div class="lg:col-span-7 space-y-6">
            
            {{-- Karakteristik Archetype --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-sparkles text-amber-500"></i> Mengenal Gaya Belajar Anda
                </h3>
                <p class="text-xs text-gray-600 leading-relaxed">
                    {{ $analysis['archetype']['description'] }}
                </p>
            </div>

            {{-- Rekomendasi Masa Depan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fas fa-compass text-indigo-600"></i> Peluang Karier & Rumpun Kuliah Terbaik
                </h3>

                @if($analysis['school_type'] === 'SMK')
                    @if(isset($analysis['recommendations']['career_tracks']))
                    <div class="space-y-3">
                        @foreach($analysis['recommendations']['career_tracks'] as $track)
                        <div class="p-3.5 bg-indigo-50/60 rounded-xl border border-indigo-100 space-y-1 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-indigo-950">{{ $track['title'] }}</span>
                                <span class="px-2 py-0.5 bg-indigo-200 text-indigo-800 rounded font-bold text-[10px]">{{ $track['relevance'] }}</span>
                            </div>
                            <p class="text-gray-600">{{ $track['description'] }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @if(isset($analysis['recommendations']['pkl_recommendation']))
                    <div class="p-3.5 bg-emerald-50 rounded-xl text-xs text-emerald-900">
                        <b>Rekomendasi Tempat PKL:</b> {{ $analysis['recommendations']['pkl_recommendation'] }}
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

            {{-- Form Kuesioner Pemetaan Diagnostik Mandiri --}}
            <div class="bg-gradient-to-br from-indigo-50/80 via-purple-50/50 to-pink-50/50 rounded-2xl border border-indigo-100 p-6 sm:p-7 space-y-5">
                <div class="flex items-center justify-between border-b border-indigo-100 pb-3">
                    <h3 class="text-sm font-bold text-indigo-950 flex items-center gap-2">
                        <i class="fas fa-tasks text-purple-600"></i> Kalibrasi Mandiri Minat & Cita-Cita
                    </h3>
                    <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2.5 py-1 rounded-full">
                        {{ $analysis['diagnostic'] ? '✓ Sudah Terkalibrasi' : 'Belum Diisi' }}
                    </span>
                </div>

                <p class="text-xs text-gray-600 leading-relaxed">
                    Bantu AI PembdaHUB menyempurnakan akurasi DNA Anda dengan memilih minat, gaya belajar, dan cita-cita yang paling sesuai dengan kata hati Anda.
                </p>

                <form method="POST" action="{{ route('siswa.dna.diagnostic.save') }}" class="space-y-4 text-xs">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1.5">Gaya Belajar & Kerja Favorit *</label>
                            <select name="work_style_preference" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-gray-800">
                                <option value="praktik" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'praktik' ? 'selected' : '' }}>Praktik Langsung & Eksperimen Fisik</option>
                                <option value="analisis" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'analisis' ? 'selected' : '' }}>Menganalisis Masalah & Riset Logika</option>
                                <option value="kreatif" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'kreatif' ? 'selected' : '' }}>Membuat Karya Kreatif & Desain Visual</option>
                                <option value="kolaborasi" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'kolaborasi' ? 'selected' : '' }}>Diskusi Kelompok & Presentasi Publik</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1.5">Rumpun Minat Paling Disukai *</label>
                            <select name="favorite_subject_cluster" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-gray-800">
                                <option value="eksak" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'eksak' ? 'selected' : '' }}>Sains, Matematika & Komputer</option>
                                <option value="vokasi" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'vokasi' ? 'selected' : '' }}>Rekayasa Mesin, Otomotif & Jaringan</option>
                                <option value="bahasa" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'bahasa' ? 'selected' : '' }}>Bahasa, Sastra & Komunikasi</option>
                                <option value="sosial" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'sosial' ? 'selected' : '' }}>Hukum, Bisnis, Manajemen & Sosial</option>
                                <option value="seni" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'seni' ? 'selected' : '' }}>Desain Grafis, Multimedia & Seni</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-gray-700 mb-1.5">Cita-Cita / Profesi Impian Masa Depan *</label>
                            <input type="text" name="career_aspiration" value="{{ old('career_aspiration', $analysis['diagnostic']->career_aspiration ?? '') }}" required placeholder="Contoh: Software Developer di Perusahaan Multinasional / Dokter Spesialis / Wirausahawan Otomotif" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold">
                        </div>
                    </div>

                    {{-- Hidden Self Scores with defaults --}}
                    <input type="hidden" name="logic_self_score" value="{{ $analysis['scores']['logic'] }}">
                    <input type="hidden" name="creative_self_score" value="{{ $analysis['scores']['creative'] }}">
                    <input type="hidden" name="communication_self_score" value="{{ $analysis['scores']['communication'] }}">
                    <input type="hidden" name="technical_self_score" value="{{ $analysis['scores']['technical'] }}">
                    <input type="hidden" name="social_self_score" value="{{ $analysis['scores']['social'] }}">
                    <input type="hidden" name="discipline_self_score" value="{{ $analysis['scores']['discipline'] }}">

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl font-bold text-xs shadow-sm flex items-center gap-2 transition active:scale-95">
                            <i class="fas fa-save"></i>
                            <span>Simpan & Kalibrasi DNA</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('studentDnaChart').getContext('2d');
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
