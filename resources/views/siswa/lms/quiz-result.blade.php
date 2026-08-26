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
    @keyframes countUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.2); }
        50% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.4); }
    }
    @keyframes pulseGlowRed {
        0%, 100% { box-shadow: 0 0 20px rgba(244, 63, 94, 0.2); }
        50% { box-shadow: 0 0 40px rgba(244, 63, 94, 0.4); }
    }
    @keyframes shimmer {
        0% { background-position: -200% center; }
        100% { background-position: 200% center; }
    }
    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes badgePop {
        0% { transform: scale(0); }
        60% { transform: scale(1.15); }
        100% { transform: scale(1); }
    }

    .result-fadeIn {
        animation: fadeInUp 0.6s ease-out both;
    }
    .result-scale {
        animation: fadeInScale 0.5s ease-out both;
    }
    .result-slide {
        animation: slideInLeft 0.5s ease-out both;
    }

    .score-ring {
        animation: drawCircle 1.5s ease-out both;
        animation-delay: 0.3s;
    }

    .score-glow-pass {
        animation: pulseGlow 2s ease-in-out infinite;
    }
    .score-glow-fail {
        animation: pulseGlowRed 2s ease-in-out infinite;
    }

    .badge-pop {
        animation: badgePop 0.4s ease-out both;
        animation-delay: 1s;
    }

    .stat-card {
        animation: fadeInUp 0.5s ease-out both;
    }
    .stat-card:nth-child(1) { animation-delay: 0.6s; }
    .stat-card:nth-child(2) { animation-delay: 0.75s; }
    .stat-card:nth-child(3) { animation-delay: 0.9s; }

    .review-card {
        animation: fadeInUp 0.4s ease-out both;
    }

    .shimmer-text {
        background: linear-gradient(90deg, currentColor 40%, rgba(255,255,255,0.8) 50%, currentColor 60%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: shimmer 3s linear infinite;
    }

    /* SVG Progress Ring */
    .progress-ring-circle-bg {
        stroke: #e5e7eb;
    }
    .progress-ring-circle-pass {
        stroke: url(#gradientPass);
        filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.4));
    }
    .progress-ring-circle-fail {
        stroke: url(#gradientFail);
        filter: drop-shadow(0 0 6px rgba(244, 63, 94, 0.4));
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-8" x-data="{ shown: true }">

    {{-- ===== SCORE HERO SECTION (100% SOLID UI UX PRO MAX) ===== --}}
    <div class="bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden result-fadeIn">
        {{-- Top solid accent banner --}}
        <div class="h-3 border-b-2 border-black" style="background-color: {{ $attempt->is_passed ? '#059669' : '#dc2626' }} !important;"></div>

        <div class="px-6 sm:px-10 py-10">
            {{-- Score Circle --}}
            <div class="flex flex-col items-center">
                <div class="relative rounded-full p-2 border-4 border-black bg-white shadow-lg">
                    <svg width="190" height="190" viewBox="0 0 120 120" class="transform -rotate-90">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#e2e8f0" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none" stroke-width="10"
                                stroke="{{ $attempt->is_passed ? '#059669' : '#dc2626' }}"
                                stroke-linecap="round"
                                stroke-dasharray="314"
                                stroke-dashoffset="{{ 314 - (314 * min($attempt->score, 100) / 100) }}"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-4xl font-black text-black"
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
                        <span class="text-xs font-black uppercase tracking-widest text-black mt-1">SKOR ANDA</span>
                    </div>
                </div>

                {{-- Pass/Fail Badge --}}
                <div class="mt-6">
                    @if($attempt->is_passed)
                    <div class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl text-white font-black text-lg shadow-md border-2 border-black uppercase tracking-wider" style="background-color: #059669 !important;">
                        <i class="fas fa-trophy text-white text-xl"></i> DILATAN LULUS
                    </div>
                    @else
                    <div class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl text-white font-black text-lg shadow-md border-2 border-black uppercase tracking-wider" style="background-color: #dc2626 !important;">
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
                <div class="stat-card rounded-2xl p-4 text-center border-2 border-black shadow-md" style="background-color: #a7f3d0 !important;">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-black text-emerald-400 mb-2 border border-black">
                        <i class="fas fa-check text-emerald-400 text-lg"></i>
                    </div>
                    <div class="text-3xl font-black text-black leading-none">{{ $correctAnswers }}</div>
                    <div class="text-xs text-black font-black uppercase tracking-wider mt-1">Jawaban Benar</div>
                </div>
                <div class="stat-card rounded-2xl p-4 text-center border-2 border-black shadow-md" style="background-color: #fecdd3 !important;">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-black text-rose-400 mb-2 border border-black">
                        <i class="fas fa-times text-rose-400 text-lg"></i>
                    </div>
                    <div class="text-3xl font-black text-black leading-none">{{ $wrongAnswers }}</div>
                    <div class="text-xs text-black font-black uppercase tracking-wider mt-1">Jawaban Salah</div>
                </div>
                @if($pendingAnswers > 0)
                <div class="stat-card rounded-2xl p-4 text-center border-2 border-black shadow-md" style="background-color: #fef08a !important;">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-black text-amber-400 mb-2 border border-black">
                        <i class="fas fa-hourglass-half text-amber-400 text-lg"></i>
                    </div>
                    <div class="text-3xl font-black text-black leading-none">{{ $pendingAnswers }}</div>
                    <div class="text-xs text-black font-black uppercase tracking-wider mt-1">Menunggu Dinilai</div>
                </div>
                @endif
                <div class="stat-card rounded-2xl p-4 text-center border-2 border-black shadow-md" style="background-color: #e0f2fe !important;">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-black text-blue-400 mb-2 border border-black">
                        <i class="fas fa-list-ol text-blue-400 text-lg"></i>
                    </div>
                    <div class="text-3xl font-black text-black leading-none">{{ $totalQuestions }}</div>
                    <div class="text-xs text-black font-black uppercase tracking-wider mt-1">Total Soal</div>
                </div>
            </div>

            {{-- Info Row --}}
            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 mt-6 text-sm font-black">
                <div class="flex items-center gap-2 text-black bg-slate-100 border border-black px-4 py-2 rounded-xl shadow-sm">
                    <i class="fas fa-bullseye text-black"></i>
                    <span>Batas Lulus: <span class="font-black text-black">{{ $quiz->passing_score }}%</span></span>
                </div>
                <div class="flex items-center gap-2 text-black bg-slate-100 border border-black px-4 py-2 rounded-xl shadow-sm">
                    <i class="fas fa-redo text-black"></i>
                    <span>Percobaan Ujian: <span class="font-black text-black">#{{ $attempt->id }}</span></span>
                </div>
                <div class="flex items-center gap-2 text-black bg-slate-100 border border-black px-4 py-2 rounded-xl shadow-sm">
                    <i class="fas fa-calendar-check text-black"></i>
                    <span class="font-black text-black">{{ $attempt->finished_at ? $attempt->finished_at->format('d M Y H:i') : '-' }}</span>
                </div>
            </div>

            {{-- Retry Button --}}
            @php
                $remaining = $quiz->getRemainingAttempts($student->id);
            @endphp
            @if($remaining === null || $remaining > 0)
            <div class="mt-8 text-center">
                <a href="{{ route('siswa.lms.quizzes.start', $quiz->id) }}"
                   class="inline-flex items-center gap-3 bg-black hover:bg-amber-400 hover:text-black text-white px-8 py-4 rounded-2xl transition-all shadow-md border-2 border-black font-black text-sm uppercase tracking-wider">
                    <i class="fas fa-redo text-base"></i>
                    Coba Ulang Ujian
                    @if($remaining !== null)
                    <span class="text-xs bg-amber-400 text-black px-2.5 py-1 rounded-lg border border-black">{{ $remaining }}x tersisa</span>
                    @endif
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- ===== INFORMASI KERAHASIAAN SOAL ===== --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-md border-2 border-black space-y-4 result-fadeIn">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-black text-amber-400 flex items-center justify-center flex-shrink-0 shadow-md border-2 border-black text-xl">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h3 class="font-black text-black text-lg sm:text-xl leading-tight">Detail Soal & Kunci Jawaban Dirahasiakan</h3>
                <p class="text-xs sm:text-sm font-bold text-gray-700 mt-1 leading-relaxed">
                    Untuk menjaga kerahasiaan, sportivitas, dan integritas evaluasi belajar, rincian soal serta kunci jawaban yang benar tidak ditampilkan pada halaman hasil.
                </p>
                <div class="mt-3 p-3 bg-amber-50 rounded-xl border border-amber-300 flex items-center gap-2.5 text-xs font-bold text-amber-900">
                    <i class="fas fa-info-circle text-amber-600 text-sm flex-shrink-0"></i>
                    <span>Jika Anda ingin meningkatkan nilai, silakan pelajari kembali modul materi terkait di ruang kelas sebelum mencoba kuis berikutnya.</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== BACK TO COURSE BUTTON ===== --}}
    <div class="pt-2">
        <a href="{{ route('siswa.lms.show', $course->id) }}?tab=quizzes"
           class="inline-flex items-center gap-3 bg-black hover:bg-amber-400 hover:text-black text-white px-7 py-4 rounded-2xl transition-all font-black text-sm uppercase tracking-wider border-2 border-black shadow-md">
            <i class="fas fa-arrow-left text-base"></i>
            Kembali ke Ruang Belajar Kelas
        </a>
    </div>

</div>
@endsection
