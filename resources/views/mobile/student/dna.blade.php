@extends('mobile.layouts.app')

@section('title', 'DNA Akademik & Potensi Belajar 360° - PembdaHUB Mobile')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="space-y-4 pb-14" x-data="{ activeTab: 'potensi' }">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between gap-2 px-1 pt-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-base font-black text-slate-900 leading-tight truncate">DNA Akademik 360°</h2>
                <p class="text-[10px] text-slate-500 font-bold truncate">Pemetaan Potensi, Minat & Karier</p>
            </div>
        </div>

        <a href="{{ route('siswa.dna.pdf') }}" class="px-3 py-1.5 rounded-xl bg-rose-50 border-2 border-rose-200 text-rose-700 text-xs font-black hover:bg-rose-100 active:scale-95 transition flex items-center gap-1.5 shadow-2xs shrink-0" title="Unduh PDF">
            <i class="fa-solid fa-file-pdf text-rose-500"></i>
            <span>PDF</span>
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border-2 border-emerald-300 text-emerald-800 p-3 rounded-2xl shadow-xs text-xs font-bold flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Hero Card Profile & Archetype -->
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-purple-950 p-5 rounded-3xl shadow-lg border-2 border-slate-700 text-white relative overflow-hidden space-y-3.5">
        <div class="absolute -right-8 -top-8 w-40 h-40 bg-fuchsia-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex items-center gap-3.5">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl overflow-hidden border-2 border-white/40 shadow-md bg-slate-800 shrink-0">
                <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover">
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full bg-gradient-to-r {{ $analysis['archetype']['color'] }} text-white text-[9px] font-black uppercase tracking-wider shadow-xs">
                        <i class="fas {{ $analysis['archetype']['badge_icon'] }}"></i> {{ $analysis['archetype']['title'] }}
                    </span>
                </div>
                <h3 class="text-sm font-black text-white leading-tight truncate">{{ $student->full_name }}</h3>
                <p class="text-[10px] text-slate-300 font-medium truncate mt-0.5">
                    NIS: {{ $student->formatted_nis ?: ($student->nis ?: '-') }} &bull; {{ $student->school->name ?? '-' }}
                </p>
            </div>
        </div>

        <div class="relative z-10 p-3 bg-white/10 rounded-2xl border border-white/10 space-y-1.5">
            <p class="text-xs text-purple-200 font-extrabold italic">
                "{{ $analysis['archetype']['tagline'] }}"
            </p>
            <p class="text-[11px] text-slate-200 leading-relaxed font-normal">
                {{ $analysis['archetype']['description'] }}
            </p>
        </div>

        <!-- Accuracy indicator -->
        <div class="relative z-10 flex items-center justify-between text-[10px] pt-1">
            <span class="text-slate-300 font-bold">Akurasi Kalibrasi Data:</span>
            <span class="font-black text-amber-300 bg-white/10 px-2 py-0.5 rounded-full">{{ $analysis['confidence_score'] }}% ({{ $analysis['confidence_label'] }})</span>
        </div>
    </div>

    <!-- Segmented Navigation Tabs -->
    <div class="grid grid-cols-3 gap-1.5 bg-slate-200/80 p-1 rounded-2xl border border-slate-300 text-center text-xs font-bold">
        <button @click="activeTab = 'potensi'" 
                class="py-2 rounded-xl transition"
                :class="activeTab === 'potensi' ? 'bg-white text-indigo-900 shadow-sm font-black' : 'text-slate-600 hover:text-slate-900'">
            🧭 Potensi
        </button>
        <button @click="activeTab = 'kuesioner'" 
                class="py-2 rounded-xl transition"
                :class="activeTab === 'kuesioner' ? 'bg-white text-indigo-900 shadow-sm font-black' : 'text-slate-600 hover:text-slate-900'">
            📝 Minat
        </button>
        <button @click="activeTab = 'kamus'" 
                class="py-2 rounded-xl transition"
                :class="activeTab === 'kamus' ? 'bg-white text-indigo-900 shadow-sm font-black' : 'text-slate-600 hover:text-slate-900'">
            📖 Kamus
        </button>
    </div>

    <!-- TAB 1: POTENSI & RADAR CHART -->
    <div x-show="activeTab === 'potensi'" class="space-y-4" x-transition.duration.200ms>
        <!-- Radar Chart Card -->
        <div class="bg-white p-4.5 rounded-3xl border-2 border-slate-200 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-chart-pie text-fuchsia-600"></i> Radar 6 Sumbu Potensi
                </h3>
                <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                    Skor 0-100
                </span>
            </div>

            <div class="relative w-full aspect-square max-w-[260px] mx-auto py-1">
                <canvas id="mobileDnaRadarChart"></canvas>
            </div>
        </div>

        <!-- 6 Sumbu Dimensi List -->
        <div class="bg-white p-4.5 rounded-3xl border-2 border-slate-200 shadow-xs space-y-3 text-xs">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-sliders text-indigo-600"></i> Rincian Skor Tiap Sumbu
            </h3>

            <div class="space-y-3 text-xs">
                <!-- Logika -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span class="text-slate-700">🧮 Logika & Analitik</span>
                        <span class="text-blue-600 font-black">{{ $analysis['scores']['logic'] }}/100</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $analysis['scores']['logic'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">{{ $analysis['dimension_sources']['logic'] }}</p>
                </div>

                <!-- Komunikasi -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span class="text-slate-700">🗣️ Komunikasi & Bahasa</span>
                        <span class="text-emerald-600 font-black">{{ $analysis['scores']['communication'] }}/100</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $analysis['scores']['communication'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">{{ $analysis['dimension_sources']['communication'] }}</p>
                </div>

                <!-- Vokasi -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span class="text-slate-700">🛠️ Keahlian Vokasi & Terapan</span>
                        <span class="text-indigo-600 font-black">{{ $analysis['scores']['technical'] }}/100</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $analysis['scores']['technical'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">{{ $analysis['dimension_sources']['technical'] }}</p>
                </div>

                <!-- Sosial -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span class="text-slate-700">🤝 Sosial & Kepemimpinan</span>
                        <span class="text-amber-600 font-black">{{ $analysis['scores']['social'] }}/100</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $analysis['scores']['social'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">{{ $analysis['dimension_sources']['social'] }}</p>
                </div>

                <!-- Kreativitas -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span class="text-slate-700">🎨 Kreativitas & Inovasi</span>
                        <span class="text-purple-600 font-black">{{ $analysis['scores']['creative'] }}/100</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $analysis['scores']['creative'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">{{ $analysis['dimension_sources']['creative'] }}</p>
                </div>

                <!-- Kedisiplinan -->
                <div>
                    <div class="flex justify-between font-bold mb-1">
                        <span class="text-slate-700">⏱️ Kedisiplinan & Ketekunan</span>
                        <span class="text-rose-600 font-black">{{ $analysis['scores']['discipline'] }}/100</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-rose-500 h-2 rounded-full" style="width: {{ $analysis['scores']['discipline'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">{{ $analysis['dimension_sources']['discipline'] }}</p>
                </div>
            </div>
        </div>

        <!-- Rekomendasi Karier & Strategi Belajar -->
        <div class="bg-white p-4.5 rounded-3xl border-2 border-slate-200 shadow-xs space-y-3.5">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-compass text-purple-600"></i> Rekomendasi Masa Depan
            </h3>

            <!-- Jalur Karier -->
            <div class="p-3 bg-indigo-50/60 rounded-2xl border border-indigo-100 space-y-1.5">
                <h4 class="text-xs font-black text-indigo-950 flex items-center gap-1.5">
                    <span>🚀 Pilihan Karier / Bidang Relevan:</span>
                </h4>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach($analysis['career_recommendations'] as $career)
                    <span class="px-2.5 py-1 bg-white rounded-xl text-[10px] font-black text-indigo-900 border border-indigo-200 shadow-2xs">
                        {{ $career }}
                    </span>
                    @endforeach
                </div>
            </div>

            <!-- Program Kuliah -->
            <div class="p-3 bg-purple-50/60 rounded-2xl border border-purple-100 space-y-1.5">
                <h4 class="text-xs font-black text-purple-950 flex items-center gap-1.5">
                    <span>🎓 Rekomendasi Jurusan / Kuliah:</span>
                </h4>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach($analysis['college_recommendations'] as $major)
                    <span class="px-2.5 py-1 bg-white rounded-xl text-[10px] font-black text-purple-900 border border-purple-200 shadow-2xs">
                        {{ $major }}
                    </span>
                    @endforeach
                </div>
            </div>

            <!-- Strategi Belajar -->
            <div class="p-3 bg-amber-50/60 rounded-2xl border border-amber-200 space-y-1.5">
                <h4 class="text-xs font-black text-amber-950 flex items-center gap-1.5">
                    <span>💡 Tips Strategi Belajar Personal:</span>
                </h4>
                <p class="text-[11px] text-amber-900 leading-relaxed">
                    {{ $analysis['learning_strategy'] }}
                </p>
            </div>
        </div>
    </div>

    <!-- TAB 2: KUESIONER MINAT MANDIRI -->
    <div x-show="activeTab === 'kuesioner'" class="space-y-4" x-transition.duration.200ms>
        <div class="bg-white p-4.5 rounded-3xl border-2 border-slate-200 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-2.5">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-pen-to-square text-indigo-600"></i> Kalibrasi Minat Belajar Mandiri
                </h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                    Isi kuesioner ini sesuai minat dan cita-cita pribadimu untuk menyempurnakan hasil analisis DNA.
                </p>
            </div>

            <form action="{{ route('mobile.dna.diagnostic.save') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf

                <!-- Gaya Kerja -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Gaya Kerja & Belajar Paling Nyaman</label>
                    <select name="work_style_preference" required class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold text-slate-800">
                        <option value="Praktik Langsung & Hands-on" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'Praktik Langsung & Hands-on' ? 'selected' : '' }}>🛠️ Praktik Langsung & Eksperimen Lapangan</option>
                        <option value="Analisis Teoretis & Eksakta" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'Analisis Teoretis & Eksakta' ? 'selected' : '' }}>🧮 Analisis Teoretis, Hitungan & Pemecahan Masalah</option>
                        <option value="Kolaboratif Tim & Komunikasi" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'Kolaboratif Tim & Komunikasi' ? 'selected' : '' }}>🤝 Diskusi Kelompok, Komunikasi & Presentasi</option>
                        <option value="Eksplorasi Kreatif & Desain" {{ old('work_style_preference', $analysis['diagnostic']->work_style_preference ?? '') == 'Eksplorasi Kreatif & Desain' ? 'selected' : '' }}>🎨 Eksplorasi Visual, Desain Seni & Inovasi Mandiri</option>
                    </select>
                </div>

                <!-- Rumpun Mapel Favorit -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kelompok Mapel Paling Disukai</label>
                    <select name="favorite_subject_cluster" required class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold text-slate-800">
                        <option value="Matematika & IPA" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'Matematika & IPA' ? 'selected' : '' }}>🧮 Matematika, Fisika, Kimia, Biologi & Sains</option>
                        <option value="Teknologi Kejuruan & Produktif" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'Teknologi Kejuruan & Produktif' ? 'selected' : '' }}>💻 Produktif TKJ, Otomotif, Listrik & Bengkel</option>
                        <option value="Bahasa & Literasi" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'Bahasa & Literasi' ? 'selected' : '' }}>🗣️ Bahasa Indonesia, Bahasa Inggris & Sastra</option>
                        <option value="Sosial & Humaniora" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'Sosial & Humaniora' ? 'selected' : '' }}>🤝 Sosiologi, Ekonomi, Sejarah & Geografi</option>
                        <option value="Seni & Kreativitas" {{ old('favorite_subject_cluster', $analysis['diagnostic']->favorite_subject_cluster ?? '') == 'Seni & Kreativitas' ? 'selected' : '' }}>🎨 Seni Budaya, Desain Grafis & Multimedia</option>
                    </select>
                </div>

                <!-- Cita-cita Karier -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cita-Cita / Impian Masa Depan</label>
                    <input type="text" name="career_aspiration" value="{{ old('career_aspiration', $analysis['diagnostic']->career_aspiration ?? 'Software Engineer / Praktisi IT') }}" required placeholder="Contoh: Dokter, Arsitek, Software Engineer..." class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold text-slate-800">
                </div>

                <!-- Self Score Assessment -->
                <div class="pt-2 border-t border-slate-100 space-y-3">
                    <p class="font-bold text-slate-800">Penilaian Mandiri Kepercayaan Diri (Skor 50 - 100):</p>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-600 font-bold mb-0.5">Logika & Sains</label>
                            <input type="number" name="logic_self_score" min="50" max="100" value="{{ old('logic_self_score', $analysis['diagnostic']->logic_self_score ?? 80) }}" required class="w-full px-3 py-1.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 font-bold mb-0.5">Bahasa & Bicara</label>
                            <input type="number" name="communication_self_score" min="50" max="100" value="{{ old('communication_self_score', $analysis['diagnostic']->communication_self_score ?? 80) }}" required class="w-full px-3 py-1.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 font-bold mb-0.5">Praktik / Teknik</label>
                            <input type="number" name="technical_self_score" min="50" max="100" value="{{ old('technical_self_score', $analysis['diagnostic']->technical_self_score ?? 80) }}" required class="w-full px-3 py-1.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 font-bold mb-0.5">Sosial / Tim</label>
                            <input type="number" name="social_self_score" min="50" max="100" value="{{ old('social_self_score', $analysis['diagnostic']->social_self_score ?? 80) }}" required class="w-full px-3 py-1.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 font-bold mb-0.5">Kreatif / Seni</label>
                            <input type="number" name="creative_self_score" min="50" max="100" value="{{ old('creative_self_score', $analysis['diagnostic']->creative_self_score ?? 80) }}" required class="w-full px-3 py-1.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 font-bold mb-0.5">Disiplin / Rajin</label>
                            <input type="number" name="discipline_self_score" min="50" max="100" value="{{ old('discipline_self_score', $analysis['diagnostic']->discipline_self_score ?? 85) }}" required class="w-full px-3 py-1.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black text-xs rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan & Kalibrasi Ulang DNA
                </button>
            </form>
        </div>
    </div>

    <!-- TAB 3: KAMUS 6 TIPE DOMINAN DNA -->
    <div x-show="activeTab === 'kamus'" class="space-y-3" x-transition.duration.200ms>
        <!-- 1. Logika -->
        <div class="p-4 rounded-2xl border-2 border-blue-200 bg-blue-50/40 space-y-1.5 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">🧠</span>
                <div>
                    <h4 class="font-black text-blue-950 text-xs uppercase">1. Logika & Analitik</h4>
                    <p class="text-[9px] text-blue-700 font-semibold">Algorithmic Thinker & Strategist</p>
                </div>
            </div>
            <p class="text-slate-700 text-[11px] leading-snug">Kuat dalam matematika, logika sains, komputasi, dan analisis terstruktur.</p>
            <p class="text-[10px] text-blue-900 bg-white/80 p-1.5 rounded-lg border border-blue-100 font-bold">🎯 Arah: IT/Coding, Teknik, Riset Sains, Analis Data.</p>
        </div>

        <!-- 2. Bahasa -->
        <div class="p-4 rounded-2xl border-2 border-emerald-200 bg-emerald-50/40 space-y-1.5 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold">🗣️</span>
                <div>
                    <h4 class="font-black text-emerald-950 text-xs uppercase">2. Komunikasi & Bahasa</h4>
                    <p class="text-[9px] text-emerald-700 font-semibold">Master Communicator & Diplomat</p>
                </div>
            </div>
            <p class="text-slate-700 text-[11px] leading-snug">Fasih berbicara, menulis, bernegosiasi, dan persuasif menyampaikan gagasan.</p>
            <p class="text-[10px] text-emerald-900 bg-white/80 p-1.5 rounded-lg border border-emerald-100 font-bold">🎯 Arah: PR, Jurnalisme, Hukum, Manajemen, Bisnis.</p>
        </div>

        <!-- 3. Vokasi -->
        <div class="p-4 rounded-2xl border-2 border-indigo-200 bg-indigo-50/40 space-y-1.5 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold">🛠️</span>
                <div>
                    <h4 class="font-black text-indigo-950 text-xs uppercase">3. Vokasi & Terapan</h4>
                    <p class="text-[9px] text-indigo-700 font-semibold">Applied Engineer & Builder</p>
                </div>
            </div>
            <p class="text-slate-700 text-[11px] leading-snug">Mahir kerja praktik, merakit mesin/jaringan, dan cekatan di bengkel/lab.</p>
            <p class="text-[10px] text-indigo-900 bg-white/80 p-1.5 rounded-lg border border-indigo-100 font-bold">🎯 Arah: Teknisi Industri, Otomotif, IT Support, Manufaktur.</p>
        </div>

        <!-- 4. Sosial -->
        <div class="p-4 rounded-2xl border-2 border-amber-200 bg-amber-50/40 space-y-1.5 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-amber-600 text-white flex items-center justify-center text-[10px] font-bold">🤝</span>
                <div>
                    <h4 class="font-black text-amber-950 text-xs uppercase">4. Sosial & Kepemimpinan</h4>
                    <p class="text-[9px] text-amber-700 font-semibold">Inspiring Leader & Facilitator</p>
                </div>
            </div>
            <p class="text-slate-700 text-[11px] leading-snug">Berjiwa pemimpin, peka sosial, ramah, dan pandai menggerakkan tim bersama.</p>
            <p class="text-[10px] text-amber-900 bg-white/80 p-1.5 rounded-lg border border-amber-100 font-bold">🎯 Arah: Organisasi, Keguruan, Psikologi, Administrasi.</p>
        </div>

        <!-- 5. Kreativitas -->
        <div class="p-4 rounded-2xl border-2 border-purple-200 bg-purple-50/40 space-y-1.5 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-600 text-white flex items-center justify-center text-[10px] font-bold">🎨</span>
                <div>
                    <h4 class="font-black text-purple-950 text-xs uppercase">5. Kreativitas & Inovasi</h4>
                    <p class="text-[9px] text-purple-700 font-semibold">Visionary Creator & Innovator</p>
                </div>
            </div>
            <p class="text-slate-700 text-[11px] leading-snug">Imajinatif, estetika seni tinggi, solutif out-of-the-box, dan suka merancang desain.</p>
            <p class="text-[10px] text-purple-900 bg-white/80 p-1.5 rounded-lg border border-purple-100 font-bold">🎯 Arah: DKV, Multimedia, Arsitektur, Konten Kreator.</p>
        </div>

        <!-- 6. Disiplin -->
        <div class="p-4 rounded-2xl border-2 border-rose-200 bg-rose-50/40 space-y-1.5 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-rose-600 text-white flex items-center justify-center text-[10px] font-bold">⏱️</span>
                <div>
                    <h4 class="font-black text-rose-950 text-xs uppercase">6. Kedisiplinan & Ketekunan</h4>
                    <p class="text-[9px] text-rose-700 font-semibold">Disciplined Achiever & Executor</p>
                </div>
            </div>
            <p class="text-slate-700 text-[11px] leading-snug">Presensi RFID tinggi, tepat waktu menuntaskan tugas, dan patuh aturan tertib.</p>
            <p class="text-[10px] text-rose-900 bg-white/80 p-1.5 rounded-lg border border-rose-100 font-bold">🎯 Arah: Administrasi Kantor, Akuntansi, Kedinasan.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radarCtx = document.getElementById('mobileDnaRadarChart');
    if (radarCtx) {
        new Chart(radarCtx, {
            type: 'radar',
            data: {
                labels: [
                    'Logika',
                    'Bahasa',
                    'Vokasi',
                    'Sosial',
                    'Kreatif',
                    'Disiplin'
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
                    backgroundColor: 'rgba(99, 102, 241, 0.25)',
                    borderColor: 'rgb(79, 70, 229)',
                    pointBackgroundColor: 'rgb(79, 70, 229)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(79, 70, 229)',
                    borderWidth: 2
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
                        ticks: { stepSize: 20, font: { size: 8 } },
                        pointLabels: { font: { size: 10, weight: 'bold' } }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
@endpush
