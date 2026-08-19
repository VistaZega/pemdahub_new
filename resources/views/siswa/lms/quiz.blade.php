@extends('layouts.siswa')

@section('title', 'Quiz: ' . $quiz->title)

@push('styles')
<style>
    /* ===== Immersive Exam Experience ===== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 8px rgba(239,68,68,0.3); }
        50% { box-shadow: 0 0 24px rgba(239,68,68,0.6); }
    }
    @keyframes checkPop {
        0% { transform: scale(0); }
        60% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(40px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes gentlePulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    .quiz-fadeIn {
        animation: fadeInUp 0.5s ease-out both;
    }
    .quiz-fadeIn-delay-1 { animation-delay: 0.08s; }
    .quiz-fadeIn-delay-2 { animation-delay: 0.16s; }
    .quiz-fadeIn-delay-3 { animation-delay: 0.24s; }

    .timer-pulse {
        animation: pulseGlow 1s ease-in-out infinite;
    }
    .timer-gentle-pulse {
        animation: gentlePulse 1.5s ease-in-out infinite;
    }
    .check-anim {
        animation: checkPop 0.3s ease-out both;
    }
    .nav-slide {
        animation: slideInRight 0.4s ease-out both;
    }

    /* Custom radio/checkbox styling */
    .quiz-option input[type="radio"],
    .quiz-option input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        flex-shrink: 0;
        transition: all 0.2s ease;
        position: relative;
        cursor: pointer;
    }
    .quiz-option input[type="radio"]:checked {
        border-color: #7c3aed;
        background: #7c3aed;
    }
    .quiz-option input[type="radio"]:checked::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        animation: checkPop 0.2s ease-out;
    }
    .quiz-option:hover {
        transform: translateX(4px);
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
    }
    .quiz-option.selected {
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
    }

    /* Glassmorphism footer */
    .glass-footer {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-top: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Navigator scrollbar */
    .nav-panel::-webkit-scrollbar {
        width: 4px;
    }
    .nav-panel::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.4);
        border-radius: 4px;
    }

    /* Question number badge gradient */
    .q-number-badge {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 50%, #5b21b6 100%);
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    }

    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Timer gradient states */
    .timer-safe {
        background: linear-gradient(135deg, #059669, #10b981);
    }
    .timer-warning {
        background: linear-gradient(135deg, #d97706, #f59e0b);
    }
    .timer-danger {
        background: linear-gradient(135deg, #dc2626, #ef4444);
    }
</style>
@endpush

@section('content')
<div x-data="quizApp()" x-init="initQuiz()" class="relative">

    {{-- ===== FLOATING TIMER BAR (UI UX PRO MAX SOLID STYLING) ===== --}}
    <div class="sticky top-0 z-50 -mx-4 sm:-mx-6 lg:-mx-8 mb-6">
        <div class="shadow-xl border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-3.5">
                    {{-- Left: Course & Quiz info --}}
                    <div class="flex items-center gap-3.5 min-w-0 flex-1">
                        <div class="hidden sm:flex items-center justify-center w-11 h-11 rounded-2xl flex-shrink-0 border-2 border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                            <i class="fas fa-file-alt text-xl text-black"></i>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-white font-black text-base sm:text-lg truncate tracking-wide">{{ $quiz->title }}</h1>
                            <p class="text-amber-400 font-bold text-xs truncate">{{ $course->name }} · Percobaan Ujian #{{ $attempt->id }}</p>
                        </div>
                    </div>

                    {{-- Center: Progress --}}
                    <div class="hidden md:flex items-center gap-3 px-4">
                        <div class="text-xs text-white font-black uppercase tracking-wider">
                            <span class="text-amber-300 font-black text-sm" x-text="answeredCount"></span>
                            <span>/ {{ count($questions) }} Soal Terjawab</span>
                        </div>
                        <div class="w-36 h-3 bg-slate-800 border border-slate-600 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 border border-black" style="background-color: #fbbf24 !important;"
                                 :style="'width:' + (answeredCount / {{ count($questions) }} * 100) + '%'"></div>
                        </div>
                    </div>

                    {{-- Right: Timer --}}
                    @if($quiz->time_limit)
                    @php
                        $initSec = $remainingSeconds !== null ? $remainingSeconds : ($quiz->time_limit * 60);
                        $initMin = floor($initSec / 60);
                        $initRemSec = $initSec % 60;
                        $initFormatted = sprintf('%02d:%02d', $initMin, $initRemSec);
                    @endphp
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div :class="timerClass"
                             class="px-5 py-2.5 rounded-2xl flex items-center gap-2.5 transition-all duration-500 border-2 border-black shadow-md">
                            <i class="fas fa-stopwatch text-white text-base"></i>
                            <span id="timer" class="text-white font-mono font-black text-xl tracking-wider"
                                  :class="{ 'timer-gentle-pulse': timerSeconds <= 120 }"
                                  x-text="timerDisplay">{{ $initFormatted }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Anti-Cheat Info Banner --}}
        <div class="border-b-2 border-black shadow-md" style="background-color: #fef08a !important;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-center gap-2 py-2 text-xs text-black font-black uppercase tracking-wide">
                    <i class="fas fa-shield-alt text-black text-sm"></i>
                    <span>Sistem Pengawasan Ujian Aktif · Dilarang berpindah tab atau meminimalkan browser selama ujian!</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MAIN CONTENT GRID ===== --}}
    <div class="flex gap-6">

        {{-- Questions Column --}}
        <div class="flex-1 min-w-0">
            <form id="quizForm" action="{{ route('siswa.lms.quizzes.submit', $attempt->id) }}" method="POST"
                  class="space-y-6" onsubmit="return confirm('Yakin ingin mengumpulkan jawaban ujian ini? Anda tidak bisa mengubah jawaban setelah dikumpulkan.')">
                @csrf

                @foreach($questions as $i => $question)
                @php $existingAnswer = $answerMap[$question->id] ?? null; @endphp
                <div id="question-{{ $i }}"
                     class="bg-white rounded-3xl shadow-md border-2 border-black overflow-hidden quiz-fadeIn"
                     style="animation-delay: {{ $i * 0.06 }}s"
                     :class="{ 'ring-4 ring-amber-400 border-black': flagged.includes({{ $i }}) }">

                    {{-- Question Header --}}
                    <div class="flex items-center justify-between px-6 pt-6 pb-3 border-b-2 border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="w-11 h-11 rounded-2xl flex items-center justify-center text-white text-base font-black flex-shrink-0 shadow-md border-2 border-black" style="background-color: #1e3a8a !important;">
                                {{ $i + 1 }}
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black border border-black" style="background-color: #fef08a !important; color: #000000 !important;">
                                    <i class="fas fa-star text-black text-[10px]"></i> {{ $question->score }} Poin
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black border border-black" style="background-color: #e0f2fe !important; color: #000000 !important;">
                                    @if($question->question_type === 'multiple_choice')
                                        <i class="fas fa-list-ul text-black text-[10px]"></i> Pilihan Ganda
                                    @elseif($question->question_type === 'true_false')
                                        <i class="fas fa-toggle-on text-black text-[10px]"></i> Benar/Salah
                                    @elseif($question->question_type === 'short_answer')
                                        <i class="fas fa-pencil-alt text-black text-[10px]"></i> Jawaban Singkat
                                    @elseif($question->question_type === 'essay')
                                        <i class="fas fa-align-left text-black text-[10px]"></i> Essay
                                    @endif
                                </span>
                            </div>
                        </div>
                        {{-- Flag button --}}
                        <button type="button"
                                @click="toggleFlag({{ $i }})"
                                :class="flagged.includes({{ $i }}) ? 'bg-amber-400 text-black border-2 border-black font-black' : 'bg-slate-100 text-black border-2 border-black hover:bg-amber-300 font-bold'"
                                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs transition-all shadow-sm">
                            <i class="fas fa-flag text-xs"></i>
                            <span class="hidden sm:inline" x-text="flagged.includes({{ $i }}) ? 'Ragu-ragu (Ditandai)' : 'Tandai Ragu'"></span>
                        </button>
                    </div>

                    {{-- Question Text --}}
                    <div class="px-6 py-4">
                        <p class="text-black font-black text-base md:text-lg leading-relaxed">{!! nl2br(e($question->question)) !!}</p>
                        
                        {{-- Media Display --}}
                        @if($question->image_path)
                        <div class="mt-4 max-w-xl rounded-2xl overflow-hidden shadow-md border-2 border-black bg-white">
                            <img src="{{ asset('storage/' . $question->image_path) }}" class="w-full h-auto object-contain max-h-[380px]" alt="Gambar Soal">
                        </div>
                        @endif
                        
                        @if($question->video_url)
                        <div class="mt-4 max-w-xl rounded-2xl overflow-hidden shadow-md border-2 border-black bg-black">
                            @php
                                $isYoutube = preg_match('/(youtube\.com|youtu\.be)/i', $question->video_url);
                                $embedUrl = '';
                                if ($isYoutube) {
                                    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/i', $question->video_url, $matches)) {
                                        $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                    } elseif (preg_match('/(?:v=|\/embed\/|&v=)([a-zA-Z0-9_-]+)/i', $question->video_url, $matches)) {
                                        $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                    }
                                }
                            @endphp
                            
                            @if($isYoutube && $embedUrl)
                                <div class="aspect-video w-full">
                                    <iframe class="w-full h-full" src="{{ $embedUrl }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                </div>
                            @else
                                <video class="w-full h-auto object-contain max-h-[380px]" controls preload="metadata">
                                    <source src="{{ $question->video_url }}" type="video/mp4">
                                    Browser Anda tidak mendukung tag video.
                                </video>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Answer Options --}}
                    <div class="px-6 pb-6">
                        @php
                            $options = $quiz->shuffle_questions ? $question->getShuffledOptions($attempt->id) : $question->options;
                        @endphp

                        @if($question->question_type === 'multiple_choice' && $options)
                        <div class="space-y-2.5">
                            @foreach($options as $idx => $opt)
                            @php
                                $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                                $isAssoc = is_array($opt) && isset($opt['key']);
                                $optValue = $isAssoc ? $opt['key'] : (string)$idx;
                                $optLabel = $isAssoc ? $opt['key'] : ($alphabet[$idx] ?? $idx + 1);
                                $optText = $isAssoc ? $opt['text'] : $opt;
                                $showLabel = $isAssoc || ($optLabel !== $optText);
                                $isChecked = ($existingAnswer && (string)$existingAnswer->answer === (string)$optValue);
                            @endphp
                            <label class="quiz-option flex items-center gap-3.5 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 shadow-sm
                                          {{ $isChecked ? 'selected border-black bg-purple-200 text-black font-black shadow-md' : 'border-slate-300 bg-white hover:bg-amber-100 hover:border-black text-black font-bold' }}"
                                   @click="markAnswered({{ $question->id }})">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $optValue }}"
                                       {{ $isChecked ? 'checked' : '' }}
                                       @change="markAnswered({{ $question->id }})"
                                       class="w-5 h-5 text-black border-2 border-black focus:ring-black">
                                @if($showLabel)
                                <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-black text-white text-xs font-black flex-shrink-0 border border-black">{{ $optLabel }}</span>
                                @endif
                                <span class="text-sm md:text-base text-black font-extrabold leading-snug">{{ $optText }}</span>
                            </label>
                            @endforeach
                        </div>

                        @elseif($question->question_type === 'true_false')
                        <div class="grid grid-cols-2 gap-4">
                            <label class="quiz-option flex items-center justify-center gap-3 p-4.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 shadow-sm
                                          {{ ($existingAnswer && $existingAnswer->answer === 'true') ? 'selected border-black bg-emerald-300 text-black font-black shadow-md' : 'border-slate-300 bg-white hover:bg-emerald-100 hover:border-black text-black font-bold' }}"
                                   @click="markAnswered({{ $question->id }})">
                                <input type="radio" name="answers[{{ $question->id }}]" value="true"
                                       {{ ($existingAnswer && $existingAnswer->answer === 'true') ? 'checked' : '' }}
                                       @change="markAnswered({{ $question->id }})"
                                       class="w-5 h-5 text-black border-2 border-black focus:ring-black">
                                <i class="fas fa-check-circle text-emerald-700 text-lg"></i>
                                <span class="text-base font-black text-black">BENAR</span>
                            </label>
                            <label class="quiz-option flex items-center justify-center gap-3 p-4.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 shadow-sm
                                          {{ ($existingAnswer && $existingAnswer->answer === 'false') ? 'selected border-black bg-rose-300 text-black font-black shadow-md' : 'border-slate-300 bg-white hover:bg-rose-100 hover:border-black text-black font-bold' }}"
                                   @click="markAnswered({{ $question->id }})">
                                <input type="radio" name="answers[{{ $question->id }}]" value="false"
                                       {{ ($existingAnswer && $existingAnswer->answer === 'false') ? 'checked' : '' }}
                                       @change="markAnswered({{ $question->id }})"
                                       class="w-5 h-5 text-black border-2 border-black focus:ring-black">
                                <i class="fas fa-times-circle text-rose-700 text-lg"></i>
                                <span class="text-base font-black text-black">SALAH</span>
                            </label>
                        </div>

                        @elseif($question->question_type === 'short_answer')
                        <div>
                            <input type="text" name="answers[{{ $question->id }}]"
                                   value="{{ $existingAnswer->answer ?? '' }}"
                                   @input="markAnswered({{ $question->id }})"
                                   class="w-full border-2 border-black rounded-2xl px-5 py-4 text-base font-black focus:ring-4 focus:ring-black/20 outline-none text-black bg-white math-support shadow-inner"
                                   placeholder="Ketik jawaban singkat Anda di sini...">
                        </div>

                        @elseif($question->question_type === 'essay')
                        <div>
                            <textarea name="answers[{{ $question->id }}]" rows="5"
                                      @input="markAnswered({{ $question->id }})"
                                      class="w-full border-2 border-black rounded-2xl px-5 py-4 text-base font-bold focus:ring-4 focus:ring-black/20 outline-none text-black bg-white resize-y math-support shadow-inner"
                                      placeholder="Tuliskan penjelasan jawaban essay Anda secara rinci di sini...">{{ $existingAnswer->answer ?? '' }}</textarea>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Spacer for sticky footer --}}
                <div class="h-24"></div>
            </form>
        </div>

        {{-- ===== QUESTION NAVIGATOR — Desktop Sidebar ===== --}}
        <div class="hidden lg:block w-64 flex-shrink-0">
            <div class="sticky top-36 nav-slide">
                <div class="bg-white rounded-3xl shadow-lg border-2 border-black overflow-hidden">
                    <div class="px-4 py-3.5 border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
                        <div class="flex items-center justify-between">
                            <h3 class="text-white text-xs font-black uppercase tracking-wider"><i class="fas fa-th mr-1.5 text-amber-400"></i> Navigasi Soal</h3>
                            <span class="text-xs text-amber-300 font-black"><span x-text="answeredCount"></span>/{{ count($questions) }}</span>
                        </div>
                    </div>
                    <div class="p-4 nav-panel max-h-[60vh] overflow-y-auto">
                        <div class="grid grid-cols-5 gap-2">
                            @foreach($questions as $i => $question)
                            <button type="button"
                                    @click="scrollToQuestion({{ $i }})"
                                    :class="{
                                        'bg-emerald-500 text-white border-2 border-black font-black shadow-md': answered.includes({{ $question->id }}) && !flagged.includes({{ $i }}),
                                        'bg-amber-400 text-black border-2 border-black font-black shadow-md': flagged.includes({{ $i }}),
                                        'bg-slate-100 text-black border-2 border-black font-black hover:bg-amber-300': !answered.includes({{ $question->id }}) && !flagged.includes({{ $i }})
                                    }"
                                    class="w-full aspect-square rounded-xl flex items-center justify-center text-xs transition-all duration-200 hover:scale-110">
                                {{ $i + 1 }}
                            </button>
                            @endforeach
                        </div>
                        {{-- Legend --}}
                        <div class="mt-4 pt-3 border-t-2 border-slate-100 space-y-2">
                            <div class="flex items-center gap-2 text-xs font-black text-black">
                                <span class="w-3.5 h-3.5 rounded bg-emerald-500 border border-black flex-shrink-0"></span> Dijawab
                            </div>
                            <div class="flex items-center gap-2 text-xs font-black text-black">
                                <span class="w-3.5 h-3.5 rounded bg-amber-400 border border-black flex-shrink-0"></span> Ragu-ragu
                            </div>
                            <div class="flex items-center gap-2 text-xs font-black text-black">
                                <span class="w-3.5 h-3.5 rounded bg-slate-100 border border-black flex-shrink-0"></span> Belum Dijawab
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== QUESTION NAVIGATOR — Mobile Bottom Bar ===== --}}
    <div class="lg:hidden fixed bottom-16 left-0 right-0 z-40 bg-white border-t-2 border-black shadow-2xl"
         x-show="showMobileNav" x-transition
         @click.away="showMobileNav = false">
        <div class="px-4 py-4 max-h-56 overflow-y-auto">
            <div class="grid grid-cols-6 gap-2">
                @foreach($questions as $i => $question)
                <button type="button"
                        @click="scrollToQuestion({{ $i }}); showMobileNav = false"
                        :class="{
                            'bg-emerald-500 text-white border-2 border-black font-black': answered.includes({{ $question->id }}) && !flagged.includes({{ $i }}),
                            'bg-amber-400 text-black border-2 border-black font-black': flagged.includes({{ $i }}),
                            'bg-slate-100 text-black border-2 border-black font-black': !answered.includes({{ $question->id }}) && !flagged.includes({{ $i }})
                        }"
                        class="aspect-square rounded-xl flex items-center justify-center text-xs transition-all">
                    {{ $i + 1 }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== STICKY SUBMIT FOOTER ===== --}}
    <div class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t-2 border-black shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-3">
                {{-- Left: Summary --}}
                <div class="flex items-center gap-4">
                    {{-- Mobile navigator toggle --}}
                    <button type="button" @click="showMobileNav = !showMobileNav"
                            class="lg:hidden flex items-center justify-center w-11 h-11 rounded-2xl bg-black text-white hover:bg-amber-400 hover:text-black transition border-2 border-black">
                        <i class="fas fa-th text-base"></i>
                    </button>
                    <div class="text-xs sm:text-sm text-black font-black">
                        <span class="text-black font-black text-base" x-text="answeredCount"></span><span class="text-black">/{{ count($questions) }}</span>
                        <span class="text-black ml-1">Soal Dijawab</span>
                        <template x-if="flagged.length > 0">
                            <span class="text-black font-black ml-1">
                                · <span x-text="flagged.length" class="bg-amber-300 px-2 py-0.5 rounded border border-black"></span> Ragu
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Right: Submit --}}
                <button type="button"
                        @click="submitQuiz()"
                        class="inline-flex items-center gap-2 bg-black hover:bg-emerald-600 text-white font-black px-6 py-3 rounded-2xl border-2 border-black transition-all shadow-md text-xs sm:text-sm uppercase tracking-wider">
                    <i class="fas fa-paper-plane text-sm"></i>
                    <span class="hidden sm:inline">Kumpulkan Jawaban Ujian</span>
                    <span class="sm:hidden">Kirim</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function quizApp() {
    return {
        answered: [
            // Pre-populate with already-answered question IDs
            @foreach($questions as $question)
                @if(isset($answerMap[$question->id]))
                    {{ $question->id }},
                @endif
            @endforeach
        ],
        flagged: [],
        showMobileNav: false,
        timerSeconds: {{ $remainingSeconds !== null ? $remainingSeconds : ($quiz->time_limit ? $quiz->time_limit * 60 : 0) }},
        timerDisplay: '{{ $remainingSeconds !== null ? sprintf("%02d:%02d", floor($remainingSeconds / 60), $remainingSeconds % 60) : ($quiz->time_limit ? sprintf("%02d:00", $quiz->time_limit) : "00:00") }}',
        timerClass: 'timer-safe',

        get answeredCount() {
            return this.answered.length;
        },

        markAnswered(questionId) {
            if (!this.answered.includes(questionId)) {
                this.answered.push(questionId);
            }
        },

        toggleFlag(index) {
            const pos = this.flagged.indexOf(index);
            if (pos === -1) {
                this.flagged.push(index);
            } else {
                this.flagged.splice(pos, 1);
            }
        },

        scrollToQuestion(index) {
            const el = document.getElementById('question-' + index);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('ring-2', 'ring-purple-400');
                setTimeout(() => el.classList.remove('ring-2', 'ring-purple-400'), 1500);
            }
        },

        submitQuiz() {
            document.getElementById('quizForm').requestSubmit();
        },

        initQuiz() {
            // Option selection highlight
            document.querySelectorAll('.quiz-option').forEach(label => {
                const input = label.querySelector('input[type="radio"]');
                if (input) {
                    input.addEventListener('change', () => {
                        const name = input.getAttribute('name');
                        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
                            r.closest('.quiz-option')?.classList.remove('selected', 'border-purple-300', 'bg-purple-50/50', 'border-emerald-300', 'bg-emerald-50/50', 'border-rose-300', 'bg-rose-50/50');
                            r.closest('.quiz-option')?.classList.add('border-transparent');
                        });
                        label.classList.remove('border-transparent');
                        label.classList.add('selected', 'border-purple-300');
                    });
                }
            });

            // Text/textarea input tracking
            document.querySelectorAll('input[type="text"][name^="answers"], textarea[name^="answers"]').forEach(input => {
                if (input.value.trim()) {
                    const match = input.name.match(/answers\[(\d+)\]/);
                    if (match) this.markAnswered(parseInt(match[1]));
                }
            });
        }
    };
}

@if($quiz->time_limit)
// Enhanced Timer
(function() {
    let seconds = {{ $remainingSeconds !== null ? $remainingSeconds : ($quiz->time_limit * 60) }};
    const timerEl = document.getElementById('timer');
    const form = document.getElementById('quizForm');

    const interval = setInterval(() => {
        seconds--;
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        const display = m + ':' + String(s).padStart(2, '0');
        if (timerEl) timerEl.textContent = display;

        // Update Alpine timer state
        const appEl = document.querySelector('[x-data]');
        if (appEl && appEl.__x) {
            appEl.__x.$data.timerSeconds = seconds;
            appEl.__x.$data.timerDisplay = display;

            if (seconds > 300) {
                appEl.__x.$data.timerClass = 'timer-safe';
            } else if (seconds > 120) {
                appEl.__x.$data.timerClass = 'timer-warning';
            } else {
                appEl.__x.$data.timerClass = 'timer-danger timer-pulse';
            }
        }

        if (seconds <= 0) {
            clearInterval(interval);
            alert('Waktu ujian telah habis! Jawaban Anda sedang dikumpulkan secara otomatis.');
            form.submit();
        }
    }, 1000);
})();
@endif

// Anti-Cheat: Tab/Window Focus Loss Detector
(function() {
    let warningCount = 0;
    const maxWarnings = 3;
    const form = document.getElementById('quizForm');
    let isSubmitting = false;
    let lastWarningTime = 0;

    form.addEventListener('submit', () => {
        isSubmitting = true;
    });

    function triggerWarning() {
        if (isSubmitting) return;

        const now = Date.now();
        if (now - lastWarningTime < 1000) {
            return;
        }
        lastWarningTime = now;

        warningCount++;
        if (warningCount >= maxWarnings) {
            alert("PERINGATAN KRITIS: Anda telah keluar dari halaman ujian sebanyak " + warningCount + " kali. Ujian Anda otomatis dikumpulkan karena indikasi pelanggaran akademik.");
            isSubmitting = true;
            form.submit();
        } else {
            alert("PERINGATAN: Anda terpantau keluar dari halaman kuis (membuka tab/aplikasi lain). Ujian akan otomatis dikumpulkan jika Anda keluar " + (maxWarnings - warningCount) + " kali lagi!");
        }
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            triggerWarning();
        }
    });

    window.addEventListener('blur', () => {
        triggerWarning();
    });
})();
</script>
@endpush
