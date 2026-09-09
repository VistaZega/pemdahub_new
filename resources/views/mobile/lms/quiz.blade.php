@extends('mobile.layouts.app')

@section('title', 'Pengerjaan Kuis: ' . $quiz->title . ' - PembdaHUB Mobile')
@section('hide_bottom_nav', true)

@section('content')
@php
    $initSec = $remainingSeconds !== null ? $remainingSeconds : ($quiz->time_limit ? ($quiz->time_limit * 60) : 0);
    $initMin = floor($initSec / 60);
    $initRemSec = $initSec % 60;
    $initFormatted = sprintf('%02d:%02d', $initMin, $initRemSec);
    $effectivePoints = $quiz->getEffectivePointsPerQuestion(count($questions));
@endphp

<div x-data="mobileQuizApp()" x-init="init()" class="space-y-4 pb-28">

    {{-- ===== STICKY TOP TIMER & PROGRESS BAR ===== --}}
    <div class="sticky top-0 z-40 -mx-4 px-4 py-2.5 bg-white/95 backdrop-blur-md border-b-2 border-slate-200 shadow-md">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 text-[10px] font-black text-purple-700 uppercase tracking-wider truncate">
                    <span>{{ $quiz->course->course_name ?? 'Kuis LMS' }}</span>
                </div>
                <div class="text-xs font-black text-slate-900 truncate">
                    <span x-text="answeredCount"></span>/{{ count($questions) }} Soal Terjawab
                </div>
            </div>

            {{-- Realtime Countdown Timer --}}
            @if($quiz->time_limit)
            <div class="flex-shrink-0">
                <div class="timer-badge-container flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-600 text-white font-mono font-black text-xs shadow-sm transition-colors duration-300">
                    <i class="fa-solid fa-stopwatch text-[11px]"></i>
                    <span id="floatingTimerDisplay">{{ $initFormatted }}</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-2 border border-slate-200">
            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-full rounded-full transition-all duration-300"
                 :style="'width:' + (answeredCount / {{ count($questions) > 0 ? count($questions) : 1 }} * 100) + '%'"></div>
        </div>
    </div>

    {{-- Header Banner Card --}}
    <div class="clay-purple p-5 space-y-2">
        <div class="flex items-center justify-between">
            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-white/30 text-white border border-white/40">
                Percobaan #{{ $attempt->id }}
            </span>
            @if($quiz->time_limit)
            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-amber-400 text-slate-900 shadow-sm">
                <i class="fa-regular fa-clock mr-1"></i>Batasan: {{ $quiz->time_limit }} Menit
            </span>
            @endif
        </div>
        <h1 class="text-base font-black text-white leading-snug">{{ $quiz->title }}</h1>
        @if($quiz->description)
            <p class="text-xs text-purple-100 font-medium leading-relaxed">{{ $quiz->description }}</p>
        @endif
        <div class="flex items-center justify-between text-[11px] text-purple-100 font-bold pt-2 border-t border-white/20">
            <span><i class="fa-solid fa-list-check mr-1"></i>Total: {{ count($questions) }} Soal</span>
            <span><i class="fa-solid fa-trophy mr-1"></i>KKM: {{ $quiz->passing_score ?? 70 }}%</span>
        </div>
    </div>

    <!-- Quiz Question & Answer Form -->
    <form action="{{ route('mobile.lms.quiz.submit', $attempt->id) }}" method="POST" class="space-y-4" id="quizForm">
        @csrf

        @forelse($questions as $idx => $q)
            @php 
                $prevAns = $answerMap[$q->id]->answer ?? ''; 
                $qText = $q->question ?? ($q->question_text ?? '');
                $options = ($quiz->shuffle_questions && method_exists($q, 'getShuffledOptions')) 
                    ? $q->getShuffledOptions($attempt->id) 
                    : ($q->options ?? []);
                $pointVal = ($quiz->points_per_question !== null || $quiz->question_sample_count !== null)
                    ? $effectivePoints
                    : ($q->score ?? 10);
            @endphp

            <div class="clay-card p-5 space-y-3" id="q-card-{{ $q->id }}">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-xl clay-purple text-white text-xs font-black">
                            #{{ $idx + 1 }}
                        </span>
                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-black border border-slate-200">
                            @if(($q->question_type ?? '') === 'multiple_choice')
                                Pilihan Ganda
                            @elseif(($q->question_type ?? '') === 'true_false')
                                Benar / Salah
                            @elseif(($q->question_type ?? '') === 'short_answer')
                                Isian Singkat
                            @elseif(($q->question_type ?? '') === 'essay')
                                Essay
                            @else
                                Soal
                            @endif
                        </span>
                    </div>
                    <span class="text-[11px] font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200">
                        {{ $pointVal }} Poin
                    </span>
                </div>

                <!-- Question Text -->
                <div class="text-xs font-black text-slate-900 leading-relaxed space-y-2">
                    {!! nl2br(e($qText)) !!}
                </div>

                <!-- Question Image Media -->
                @if(!empty($q->image_path))
                    <div class="my-2 rounded-2xl overflow-hidden border-2 border-slate-200 bg-white">
                        <img src="{{ asset('storage/' . $q->image_path) }}" alt="Gambar Soal #{{ $idx + 1 }}" class="w-full h-auto max-h-64 object-contain mx-auto">
                    </div>
                @endif

                <!-- Question Video Media -->
                @if(!empty($q->video_url))
                    <div class="my-2 rounded-2xl overflow-hidden border-2 border-slate-200 bg-black">
                        @php
                            $isYoutube = preg_match('/(youtube\.com|youtu\.be)/i', $q->video_url);
                            $embedUrl = '';
                            if ($isYoutube) {
                                if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/i', $q->video_url, $m)) {
                                    $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                                } elseif (preg_match('/(?:v=|\/embed\/|&v=)([a-zA-Z0-9_-]+)/i', $q->video_url, $m)) {
                                    $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                                }
                            }
                        @endphp
                        @if($isYoutube && $embedUrl)
                            <div class="aspect-video w-full">
                                <iframe class="w-full h-full" src="{{ $embedUrl }}" frameborder="0" allowfullscreen></iframe>
                            </div>
                        @else
                            <video class="w-full h-auto max-h-64" controls preload="metadata">
                                <source src="{{ $q->video_url }}" type="video/mp4">
                                Browser Anda tidak mendukung video.
                            </video>
                        @endif
                    </div>
                @endif

                <!-- OPTIONS FOR MULTIPLE CHOICE -->
                @if(($q->question_type ?? 'multiple_choice') === 'multiple_choice' && !empty($options))
                    <div class="space-y-2 pt-1">
                        @foreach($options as $optIdx => $opt)
                            @php 
                                $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                                $isAssoc = is_array($opt) && isset($opt['key']);
                                $optVal = $isAssoc ? $opt['key'] : (string)$optIdx;
                                $optLabel = $isAssoc ? $opt['key'] : ($alphabet[$optIdx] ?? (is_numeric($optIdx) ? chr(65 + $optIdx) : strtoupper($optIdx)));
                                $optText = $isAssoc ? $opt['text'] : $opt;
                                $isChecked = ((string)$prevAns === (string)$optVal);
                            @endphp
                            <label class="p-3.5 rounded-2xl flex items-center space-x-3 cursor-pointer transition border-2"
                                   :class="answers['{{ $q->id }}'] == '{{ $optVal }}' ? 'clay-purple text-white font-black scale-[1.01]' : 'bg-[#f4f7fc] text-slate-800 border-slate-200/80 font-bold hover:border-purple-300'">
                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $optVal }}"
                                       @change="setAnswer('{{ $q->id }}', '{{ $optVal }}')"
                                       :checked="answers['{{ $q->id }}'] == '{{ $optVal }}'"
                                       class="hidden">
                                <div class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-black border border-current shadow-sm flex-shrink-0"
                                     :class="answers['{{ $q->id }}'] == '{{ $optVal }}' ? 'bg-white text-purple-700' : 'bg-white text-slate-700'">
                                    {{ $optLabel }}
                                </div>
                                <span class="text-xs flex-1 leading-snug">{{ $optText }}</span>
                            </label>
                        @endforeach
                    </div>

                <!-- OPTIONS FOR TRUE/FALSE -->
                @elseif(($q->question_type ?? '') === 'true_false')
                    <div class="grid grid-cols-2 gap-2.5 pt-1">
                        <label class="p-3.5 rounded-2xl flex items-center justify-center space-x-2 cursor-pointer transition border-2 text-center"
                               :class="answers['{{ $q->id }}'] == 'true' ? 'bg-emerald-600 text-white border-emerald-600 font-black scale-[1.02] shadow-md' : 'bg-emerald-50 text-emerald-900 border-emerald-200 font-bold'">
                            <input type="radio" name="answers[{{ $q->id }}]" value="true"
                                   @change="setAnswer('{{ $q->id }}', 'true')"
                                   :checked="answers['{{ $q->id }}'] == 'true'"
                                   class="hidden">
                            <i class="fa-solid fa-circle-check text-sm"></i>
                            <span class="text-xs">BENAR</span>
                        </label>

                        <label class="p-3.5 rounded-2xl flex items-center justify-center space-x-2 cursor-pointer transition border-2 text-center"
                               :class="answers['{{ $q->id }}'] == 'false' ? 'bg-rose-600 text-white border-rose-600 font-black scale-[1.02] shadow-md' : 'bg-rose-50 text-rose-900 border-rose-200 font-bold'">
                            <input type="radio" name="answers[{{ $q->id }}]" value="false"
                                   @change="setAnswer('{{ $q->id }}', 'false')"
                                   :checked="answers['{{ $q->id }}'] == 'false'"
                                   class="hidden">
                            <i class="fa-solid fa-circle-xmark text-sm"></i>
                            <span class="text-xs">SALAH</span>
                        </label>
                    </div>

                <!-- SHORT ANSWER -->
                @elseif(($q->question_type ?? '') === 'short_answer')
                    <div class="pt-1 space-y-1">
                        <label class="block text-[11px] font-black text-slate-700">Ketikkan Jawaban Singkat:</label>
                        <input type="text" name="answers[{{ $q->id }}]"
                               @input="setAnswer('{{ $q->id }}', $event.target.value)"
                               value="{{ $prevAns }}"
                               placeholder="Tuliskan jawaban Anda di sini..."
                               class="w-full p-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-xs font-bold text-slate-900 outline-none focus:border-purple-500 transition">
                    </div>

                <!-- ESSAY -->
                @else
                    <div class="pt-1 space-y-1">
                        <label class="block text-[11px] font-black text-slate-700">Tuliskan Jawaban Lengkap (Essay):</label>
                        <textarea name="answers[{{ $q->id }}]" rows="4"
                                  @input="setAnswer('{{ $q->id }}', $event.target.value)"
                                  placeholder="Ketikkan uraian jawaban essay Anda di sini..."
                                  class="w-full p-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-xs font-bold text-slate-900 outline-none focus:border-purple-500 transition resize-none">{{ $prevAns }}</textarea>
                    </div>
                @endif
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Tidak ada soal dalam kuis ini.
            </div>
        @endforelse

        {{-- ===== INLINE BOTTOM SUBMIT & RECAP CARD ===== --}}
        <div class="clay-card p-5 space-y-4 border-2 border-purple-200 bg-gradient-to-b from-white to-purple-50/40">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-md shrink-0">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-black text-slate-900 leading-tight">Akhir Lembar Soal Kuis</h3>
                    <p class="text-xs text-slate-600 font-bold mt-0.5">
                        <span class="text-purple-700 font-extrabold text-sm" x-text="answeredCount"></span> dari {{ count($questions) }} soal telah Anda jawab
                    </p>
                </div>
            </div>

            {{-- Mini Question Navigator / Status Grid --}}
            <div class="p-3 bg-white rounded-2xl border border-slate-200 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-black text-slate-600">
                    <span>Rekap Lembar Jawaban:</span>
                    <span class="text-purple-700 font-extrabold" x-text="answeredCount === {{ count($questions) }} ? 'Semua Terjawab ✅' : ({{ count($questions) }} - answeredCount) + ' Soal Belum Diisi ⚠️'"></span>
                </div>
                <div class="grid grid-cols-5 sm:grid-cols-10 gap-1.5 pt-1">
                    @foreach($questions as $idx => $q)
                        <a href="#q-card-{{ $q->id }}"
                           class="h-8 rounded-xl flex items-center justify-center text-xs font-black transition border-2"
                           :class="answers['{{ $q->id }}'] ? 'bg-purple-600 text-white border-purple-700 shadow-sm' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-purple-50'">
                            {{ $idx + 1 }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Warning if some questions are unanswered --}}
            <div x-show="answeredCount < {{ count($questions) }}" class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm shrink-0"></i>
                <span>Perhatian: Masih ada butir soal yang belum dijawab. Anda tetap bisa mengirimkan kuis sekarang jika sudah yakin.</span>
            </div>

            {{-- Big Prominent Inline Submit Button --}}
            <button type="submit" 
                    onclick="return confirm('Apakah Anda yakin ingin menyelesaikan dan mengirimkan seluruh jawaban kuis ini?')"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-700 text-white font-black text-sm uppercase tracking-wider shadow-lg hover:shadow-xl active:scale-[0.98] transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane text-base"></i>
                <span>Kirim & Selesaikan Kuis</span>
            </button>
        </div>

        {{-- ===== STICKY BOTTOM SUBMIT BAR ===== --}}
        <div class="fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t-2 border-slate-200 px-4 py-3 shadow-2xl">
            <div class="max-w-md mx-auto flex items-center justify-between gap-3">
                <div class="text-[11px] font-black text-slate-700 min-w-0 flex-1">
                    <span class="text-purple-700 font-black text-base" x-text="answeredCount"></span>/{{ count($questions) }}
                    <span>Soal Dijawab</span>
                </div>
                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan dan mengirimkan seluruh jawaban kuis ini?')"
                        class="clay-purple py-3 px-5 text-white font-black text-xs uppercase tracking-wider shadow-lg flex items-center justify-center gap-2 flex-shrink-0 active:scale-95 transition">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Kirim Jawaban</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function mobileQuizApp() {
    return {
        answers: {
            @foreach($questions as $q)
                @if(isset($answerMap[$q->id]) && $answerMap[$q->id]->answer !== null && $answerMap[$q->id]->answer !== '')
                    '{{ $q->id }}': '{{ addslashes($answerMap[$q->id]->answer) }}',
                @endif
            @endforeach
        },

        get answeredCount() {
            let count = 0;
            for (let key in this.answers) {
                if (this.answers[key] !== null && String(this.answers[key]).trim() !== '') {
                    count++;
                }
            }
            return count;
        },

        setAnswer(questionId, val) {
            if (val !== null && String(val).trim() !== '') {
                this.answers[questionId] = String(val).trim();
            } else {
                delete this.answers[questionId];
            }
        },

        init() {
            // Initial binding from form inputs
            document.querySelectorAll('input[type="text"][name^="answers"], textarea[name^="answers"]').forEach(input => {
                const match = input.name.match(/answers\[(\d+)\]/);
                if (match && input.value.trim()) {
                    this.answers[match[1]] = input.value.trim();
                }
            });
        }
    };
}

@if($quiz->time_limit)
// Enhanced Mobile Timer Countdown & Auto Submit
(function() {
    let remainingSeconds = {{ $remainingSeconds !== null ? $remainingSeconds : ($quiz->time_limit * 60) }};
    const floatingTimerDisplayEl = document.getElementById('floatingTimerDisplay');
    const quizForm = document.getElementById('quizForm');
    let isSubmitted = false;

    quizForm.addEventListener('submit', () => {
        isSubmitted = true;
    });

    const timerInterval = setInterval(() => {
        if (isSubmitted) {
            clearInterval(timerInterval);
            return;
        }

        remainingSeconds--;
        const m = Math.floor(Math.max(0, remainingSeconds) / 60);
        const s = Math.max(0, remainingSeconds) % 60;
        const timeStr = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');

        if (floatingTimerDisplayEl) {
            floatingTimerDisplayEl.textContent = timeStr;
        }

        // Timer badge color styling
        const timerBadges = document.querySelectorAll('.timer-badge-container');
        if (remainingSeconds <= 60) {
            timerBadges.forEach(el => {
                el.classList.remove('bg-purple-600', 'bg-amber-500');
                el.classList.add('bg-red-600', 'animate-pulse');
            });
        } else if (remainingSeconds <= 120) {
            timerBadges.forEach(el => {
                el.classList.remove('bg-purple-600', 'bg-red-600', 'animate-pulse');
                el.classList.add('bg-amber-500');
            });
        }

        if (remainingSeconds <= 0) {
            clearInterval(timerInterval);
            isSubmitted = true;
            alert('Waktu pengerjaan kuis telah habis! Jawaban Anda akan otomatis dikirimkan.');
            quizForm.submit();
        }
    }, 1000);
})();
@endif
</script>
@endpush
