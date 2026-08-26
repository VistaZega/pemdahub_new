@extends('layouts.siswa')

@section('title', 'Hasil Quiz: ' . $quiz->title)

@push('styles')
<style>
    /* ===== Premium Result Page ===== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes drawCircle {
        from { stroke-dashoffset: 314; }
    }
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.2); }
        50% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.4); }
    }
    @keyframes pulseGlowRed {
        0%, 100% { box-shadow: 0 0 20px rgba(244, 63, 94, 0.2); }
        50% { box-shadow: 0 0 40px rgba(244, 63, 94, 0.4); }
    }

    .result-fadeIn {
        animation: fadeInUp 0.6s ease-out both;
    }
    .result-scale {
        animation: fadeInScale 0.5s ease-out both;
    }

    .score-glow-pass {
        animation: pulseGlow 2s ease-in-out infinite;
    }
    .score-glow-fail {
        animation: pulseGlowRed 2s ease-in-out infinite;
    }

    .stat-card {
        animation: fadeInUp 0.5s ease-out both;
    }
    .stat-card:nth-child(1) { animation-delay: 0.2s; }
    .stat-card:nth-child(2) { animation-delay: 0.35s; }
    .stat-card:nth-child(3) { animation-delay: 0.5s; }
    .stat-card:nth-child(4) { animation-delay: 0.65s; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-8" x-data="{ shown: true }">

    {{-- ===== SCORE HERO SECTION (CERAH, SELEBRATIF & MODERN) ===== --}}
    <div class="bg-white rounded-3xl shadow-md border border-slate-200/80 overflow-hidden result-fadeIn">
        {{-- Top accent gradient banner --}}
        <div class="h-3 bg-gradient-to-r {{ $attempt->is_passed ? 'from-emerald-400 to-teal-500' : 'from-rose-400 to-pink-500' }}"></div>

        <div class="px-6 sm:px-10 py-10">
            {{-- Score Circle --}}
            <div class="flex flex-col items-center">
                <div class="relative rounded-full p-2 border-4 border-slate-100 bg-white shadow-xl {{ $attempt->is_passed ? 'score-glow-pass' : 'score-glow-fail' }}">
                    <svg width="190" height="190" viewBox="0 0 120 120" class="transform -rotate-90">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none" stroke-width="10"
                                stroke="{{ $attempt->is_passed ? '#10b981' : '#f43f5e' }}"
                                stroke-linecap="round"
                                stroke-dasharray="314"
                                stroke-dashoffset="{{ 314 - (314 * min($attempt->score, 100) / 100) }}"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-4xl font-black {{ $attempt->is_passed ? 'text-emerald-600' : 'text-rose-600' }}"
                              x-data="{ val: 0 }"
                              x-init="setTimeout(() => {
                                  let target = {{ number_format($attempt->score, 1) }};
                                  let step = target / 40;
                                  let iv = setInterval(() => {
                                      val += step;
                                      if (val >= target) { val = target; clearInterval(iv); }
                                  }, 30);
                              }, 500)"
                              x-text="val.toFixed(1) + '%'">0%</span>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-1">SKOR ANDA</span>
                    </div>
                </div>

                {{-- Pass/Fail Badge --}}
                <div class="mt-6">
                    @if($attempt->is_passed)
                    <div class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl text-white font-extrabold text-lg shadow-md shadow-emerald-200 bg-gradient-to-r from-emerald-500 to-teal-600 uppercase tracking-wider">
                        <i class="fas fa-trophy text-amber-300 text-xl"></i> DINYATAKAN LULUS 🎉
                    </div>
                    @else
                    <div class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl text-white font-extrabold text-lg shadow-md shadow-rose-200 bg-gradient-to-r from-rose-500 to-pink-600 uppercase tracking-wider">
                        <i class="fas fa-times-circle text-white text-xl"></i> BELUM LULUS
                    </div>
                    @endif
                </div>
            </div>

            {{-- Stats Row --}}
            @php
                $totalQuestions = $attempt->answers ? $attempt->answers->count() : 0;
                $correctAnswers = $attempt->answers ? $attempt->answers->where('is_correct', true)->count() : 0;
                $wrongAnswers = $attempt->answers ? $attempt->answers->where('is_correct', false)->count() : 0;
                $pendingAnswers = $attempt->answers ? $attempt->answers->whereNull('is_correct')->count() : 0;
            @endphp
            <div class="grid grid-cols-{{ $pendingAnswers > 0 ? '4' : '3' }} gap-4 mt-8">
                <div class="stat-card rounded-2xl p-4 text-center border border-emerald-200/80 bg-emerald-50/70 shadow-xs">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-500 text-white mb-2 shadow-xs">
                        <i class="fas fa-check text-lg"></i>
                    </div>
                    <div class="text-3xl font-black text-emerald-700 leading-none">{{ $correctAnswers }}</div>
                    <div class="text-xs text-emerald-800 font-bold uppercase tracking-wider mt-1">Jawaban Benar</div>
                </div>
                <div class="stat-card rounded-2xl p-4 text-center border border-rose-200/80 bg-rose-50/70 shadow-xs">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-rose-500 text-white mb-2 shadow-xs">
                        <i class="fas fa-times text-lg"></i>
                    </div>
                    <div class="text-3xl font-black text-rose-700 leading-none">{{ $wrongAnswers }}</div>
                    <div class="text-xs text-rose-800 font-bold uppercase tracking-wider mt-1">Jawaban Salah</div>
                </div>
                @if($pendingAnswers > 0)
                <div class="stat-card rounded-2xl p-4 text-center border border-amber-200/80 bg-amber-50/70 shadow-xs">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-500 text-white mb-2 shadow-xs">
                        <i class="fas fa-hourglass-half text-lg"></i>
                    </div>
                    <div class="text-3xl font-black text-amber-700 leading-none">{{ $pendingAnswers }}</div>
                    <div class="text-xs text-amber-800 font-bold uppercase tracking-wider mt-1">Menunggu Dinilai</div>
                </div>
                @endif
                <div class="stat-card rounded-2xl p-4 text-center border border-indigo-200/80 bg-indigo-50/70 shadow-xs">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-500 text-white mb-2 shadow-xs">
                        <i class="fas fa-list-ol text-lg"></i>
                    </div>
                    <div class="text-3xl font-black text-indigo-700 leading-none">{{ $totalQuestions }}</div>
                    <div class="text-xs text-indigo-800 font-bold uppercase tracking-wider mt-1">Total Soal</div>
                </div>
            </div>

            {{-- Info Row --}}
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 mt-6 text-xs font-bold">
                <div class="flex items-center gap-2 text-slate-700 bg-slate-50 border border-slate-200 px-4 py-2 rounded-xl shadow-xs">
                    <i class="fas fa-bullseye text-indigo-500"></i>
                    <span>Batas Lulus: <span class="font-black text-slate-900">{{ $quiz->passing_score }}%</span></span>
                </div>
                <div class="flex items-center gap-2 text-slate-700 bg-slate-50 border border-slate-200 px-4 py-2 rounded-xl shadow-xs">
                    <i class="fas fa-redo text-indigo-500"></i>
                    <span>Percobaan Ujian: <span class="font-black text-slate-900">#{{ $attempt->id }}</span></span>
                </div>
                <div class="flex items-center gap-2 text-slate-700 bg-slate-50 border border-slate-200 px-4 py-2 rounded-xl shadow-xs">
                    <i class="fas fa-calendar-check text-indigo-500"></i>
                    <span class="font-black text-slate-900">{{ $attempt->finished_at ? $attempt->finished_at->format('d M Y H:i') : '-' }}</span>
                </div>
            </div>

            {{-- Retry Button --}}
            @php
                $remaining = $quiz->getRemainingAttempts($student->id);
            @endphp
            @if($remaining === null || $remaining > 0)
            <div class="mt-8 text-center">
                <a href="{{ route('siswa.lms.quizzes.start', $quiz->id) }}"
                   class="inline-flex items-center gap-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 text-white px-8 py-4 rounded-2xl transition-all shadow-md shadow-indigo-100 hover:shadow-lg font-bold text-sm uppercase tracking-wider transform hover:scale-[1.02] active:scale-[0.98]">
                    <i class="fas fa-redo text-base"></i>
                    Coba Ulang Ujian
                    @if($remaining !== null)
                    <span class="text-xs bg-white/20 text-white px-2.5 py-0.5 rounded-lg backdrop-blur-sm">{{ $remaining }}x tersisa</span>
                    @endif
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- ===== INFORMASI KERAHASIAAN SOAL ===== --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 space-y-4 result-fadeIn">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm text-xl">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-lg sm:text-xl leading-tight">Detail Soal & Kunci Jawaban Dirahasiakan</h3>
                <p class="text-xs sm:text-sm font-medium text-slate-600 mt-1 leading-relaxed">
                    Untuk menjaga kerahasiaan, sportivitas, dan integritas evaluasi belajar, rincian soal serta kunci jawaban yang benar tidak ditampilkan pada halaman hasil.
                </p>
                <div class="mt-3 p-3 bg-amber-50/80 rounded-xl border border-amber-200 flex items-center gap-2.5 text-xs font-semibold text-amber-900">
                    <i class="fas fa-info-circle text-amber-600 text-sm flex-shrink-0"></i>
                    <span>Jika Anda ingin meningkatkan nilai, silakan pelajari kembali modul materi terkait di ruang kelas sebelum mencoba kuis berikutnya.</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== BACK TO COURSE BUTTON ===== --}}
    <div class="pt-2">
        <a href="{{ route('siswa.lms.show', $course->id) }}?tab=quizzes"
           class="inline-flex items-center gap-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold px-7 py-3.5 rounded-2xl transition-all text-sm uppercase tracking-wider shadow-sm hover:text-indigo-600 hover:border-indigo-200">
            <i class="fas fa-arrow-left text-base"></i>
            Kembali ke Ruang Belajar Kelas
        </a>
    </div>

</div>
@endsection
