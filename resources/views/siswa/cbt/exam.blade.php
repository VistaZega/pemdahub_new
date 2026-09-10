<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->exam_title }} — CBT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" onload="setTimeout(() => renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true}, {left: '\\(', right: '\\)', display: false}, {left: '$', right: '$', display: false}]}), 200)"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { background: #f1f5f9; margin: 0; overflow-x: hidden; }
        @if($exam->prevent_copy_paste) * { -webkit-user-select: none !important; user-select: none !important; } @endif

        /* OMR Bubble Styles */
        .omr-bubble {
            width: 44px; height: 44px; border-radius: 50%; border: 3px solid #cbd5e1;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.15s ease;
            color: #64748b; background: white; position: relative;
        }
        .omr-bubble:hover { border-color: #3b82f6; color: #3b82f6; transform: scale(1.08); }
        .omr-bubble.selected {
            background: #1e293b; border-color: #1e293b; color: white;
            box-shadow: 0 0 0 3px rgba(30,41,59,0.2);
        }
        .omr-bubble.selected::after {
            content: ''; position: absolute; inset: 4px; border-radius: 50%;
            background: #1e293b;
        }
        .omr-bubble.selected span { position: relative; z-index: 1; color: white; }

        /* OMR Row */
        .omr-row {
            display: flex; align-items: center; gap: 8px; padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0; transition: background 0.15s;
        }
        .omr-row:hover { background: #f8fafc; }
        .omr-row .q-number {
            width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; flex-shrink: 0;
        }
        .omr-row .q-number.answered { background: #22c55e; color: white; }
        .omr-row .q-number.current { background: #3b82f6; color: white; }
        .omr-row .q-number.unanswered { background: #e2e8f0; color: #94a3b8; }

        /* Nav grid bubbles */
        .nav-bubble {
            width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.15s;
        }
        .nav-bubble.answered { background: #22c55e; color: white; }
        .nav-bubble.current { background: #3b82f6; color: white; box-shadow: 0 0 0 3px rgba(59,130,246,0.3); }
        .nav-bubble.flagged { background: #f59e0b; color: white; }
        .nav-bubble.unanswered { background: #f1f5f9; color: #94a3b8; border: 2px solid #e2e8f0; }
        .nav-bubble:hover { transform: scale(1.1); }

        /* Question Card */
        .question-card { opacity: 0; transform: translateX(20px); animation: slideIn 0.3s forwards; }
        @keyframes slideIn { to { opacity: 1; transform: translateX(0); } }

        /* Scrollbar */
        .omr-sheet::-webkit-scrollbar { width: 6px; }
        .omr-sheet::-webkit-scrollbar-track { background: #f1f5f9; }
        .omr-sheet::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

        /* Print-like header stripe */
        .exam-header-stripe { height: 4px; background: linear-gradient(90deg, #1e293b 0%, #1e293b 33%, #ef4444 33%, #ef4444 66%, #1e293b 66%, #1e293b 100%); }
    </style>
</head>
<body x-data="examApp()" x-init="init()" @beforeunload.window="beforeUnload($event)">

    {{-- Top Header Bar --}}
    <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm">
        <div class="exam-header-stripe"></div>
        <div class="px-4 py-2.5 flex items-center justify-between max-w-[1600px] mx-auto">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-white">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-800 leading-tight">{{ $exam->exam_title }}</h1>
                    <p class="text-xs text-slate-500">{{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }} &middot; {{ $exam->duration_minutes }} menit</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                {{-- Network/Sync Status Widget --}}
                <div class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold transition-all border"
                    :class="!isOnline ? 'bg-red-100 text-red-700 border-red-200' : (Object.keys(unsyncedAnswers).length > 0 ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-green-100 text-green-700 border-green-200')">
                    <span x-show="!isOnline" class="flex items-center gap-1"><i class="fas fa-wifi text-red-500 animate-pulse"></i> TERPUTUS</span>
                    <span x-show="isOnline && Object.keys(unsyncedAnswers).length > 0" class="flex items-center gap-1"><i class="fas fa-sync fa-spin text-amber-500"></i> <span x-text="Object.keys(unsyncedAnswers).length"></span> BELUM SINKRON</span>
                    <span x-show="isOnline && Object.keys(unsyncedAnswers).length === 0" class="flex items-center gap-1"><i class="fas fa-check-circle text-green-500"></i> TERKONEKSI</span>
                </div>

                {{-- Timer --}}
                <div class="flex items-center gap-2 px-4 py-2 rounded-lg font-mono text-base"
                    :class="timerUrgent ? 'bg-red-100 text-red-700 animate-pulse font-bold' : 'bg-slate-100 text-slate-700'">
                    <i class="fas fa-clock text-sm"></i>
                    <span x-text="timerDisplay"></span>
                </div>
                @if($exam->prevent_tab_switch)
                <div class="text-xs px-2 py-1 rounded bg-orange-50 text-orange-600" x-show="tabSwitchCount > 0">
                    <i class="fas fa-exclamation-triangle"></i> <span x-text="tabSwitchCount"></span>/{{ config('cbt.max_tab_switches', 5) }}
                </div>
                @endif
                <button @click="confirmSubmit()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-bold shadow-sm">
                    <i class="fas fa-paper-plane mr-1"></i> Kumpulkan
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="pt-[72px] min-h-screen flex">

        {{-- LEFT: Question Display --}}
        <div class="flex-1 p-4 lg:p-6 overflow-y-auto" style="max-height: calc(100vh - 72px);">
            @foreach($questions as $idx => $question)
            <div x-show="currentIndex === {{ $idx }}" x-cloak class="question-card max-w-4xl mx-auto">

                {{-- Question Header --}}
                <div class="bg-white rounded-t-2xl border border-b-0 border-slate-200 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center font-black text-lg"
                            :class="answeredQuestions[{{ $question->id }}] ? 'bg-green-500 text-white' : 'bg-slate-800 text-white'">
                            {{ $idx + 1 }}
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-medium">SOAL {{ $idx + 1 }} DARI {{ count($questions) }}</div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide
                                    {{ $question->question_type === 'multiple_choice' ? 'bg-blue-100 text-blue-700' : ($question->question_type === 'true_false' ? 'bg-purple-100 text-purple-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ str_replace('_', ' ', $question->question_type) }}
                                </span>
                                <span class="text-xs text-slate-500">{{ $question->points }} poin</span>
                            </div>
                        </div>
                    </div>
                    <button @click="toggleFlag({{ $question->id }})" class="w-10 h-10 rounded-lg flex items-center justify-center transition"
                        :class="flaggedQuestions[{{ $question->id }}] ? 'bg-amber-100 text-amber-500' : 'bg-slate-50 text-slate-300 hover:text-slate-500'">
                        <i class="fas fa-bookmark"></i>
                    </button>
                </div>

                {{-- Question Body --}}
                <div class="bg-white border border-slate-200 px-6 py-5">
                    <div class="prose prose-slate max-w-none text-[15px] leading-relaxed">{!! $question->question_text !!}</div>

                    {{-- Question Media --}}
                    @if($question->question_image)
                    <div class="mt-4">
                        <img src="{{ $question->question_image_url }}" alt="Gambar Soal" class="max-w-full md:max-w-lg rounded-xl border border-slate-200 shadow-sm cursor-pointer" onclick="this.classList.toggle('md:max-w-lg'); this.classList.toggle('md:max-w-full')">
                    </div>
                    @endif

                    @if($question->question_audio)
                    <div class="mt-4">
                        <audio controls preload="none" class="w-full max-w-md">
                            <source src="{{ $question->question_audio_url }}">
                            Browser tidak mendukung audio.
                        </audio>
                    </div>
                    @endif

                    @if($question->question_video)
                    <div class="mt-4">
                        <video controls preload="none" class="w-full max-w-lg rounded-xl border border-slate-200 shadow-sm">
                            <source src="{{ $question->question_video_url }}">
                            Browser tidak mendukung video.
                        </video>
                    </div>
                    @endif
                </div>

                {{-- Answer Area --}}
                <div class="bg-slate-50 rounded-b-2xl border border-t-0 border-slate-200 px-6 py-5">
                    @if($question->question_type === 'multiple_choice')
                    @php
                        $options = $question->options->sortBy('sort_order');
                        if(isset($optionOrders[$question->id])) {
                            $orderMap = collect($optionOrders[$question->id]);
                            $options = $orderMap->map(fn($label) => $question->options->firstWhere('option_label', $label))->filter();
                        }
                    @endphp

                    {{-- OMR Style Bubbles --}}
                    <div class="mb-3 flex items-center gap-2 text-xs text-slate-400 uppercase tracking-widest font-bold">
                        <i class="fas fa-circle text-[6px]"></i> Pilih satu jawaban
                    </div>
                    <div class="space-y-2">
                        @foreach($options as $oIdx => $option)
                        @php $displayLabel = chr(65 + $oIdx); @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all"
                             :class="selectedAnswers[{{ $question->id }}] === '{{ $option->option_label }}' ? 'bg-slate-800 shadow-md' : 'bg-white border border-slate-200 hover:border-blue-300 hover:shadow-sm'"
                             @click="selectAnswer({{ $question->id }}, '{{ $option->option_label }}')">

                            {{-- OMR Bubble --}}
                            <div class="omr-bubble flex-shrink-0"
                                :class="selectedAnswers[{{ $question->id }}] === '{{ $option->option_label }}' ? 'selected' : ''">
                                <span>{{ $displayLabel }}</span>
                            </div>

                            {{-- Option Text --}}
                            <div class="flex-1">
                                <span class="text-[15px] leading-relaxed transition-colors"
                                    :class="selectedAnswers[{{ $question->id }}] === '{{ $option->option_label }}' ? 'text-white font-medium' : 'text-slate-700'">
                                    {{ $option->option_text }}
                                </span>
                                @if($option->option_image)
                                <img src="{{ $option->option_image_url }}" alt="Opsi {{ $displayLabel }}" class="mt-2 max-w-[200px] rounded-lg border border-slate-200">
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @elseif($question->question_type === 'true_false')
                    <div class="mb-3 flex items-center gap-2 text-xs text-slate-400 uppercase tracking-widest font-bold">
                        <i class="fas fa-circle text-[6px]"></i> Benar atau Salah
                    </div>
                    <div class="flex gap-4">
                        @foreach(['Benar' => 'T', 'Salah' => 'F'] as $label => $val)
                        <div class="flex-1 flex items-center justify-center gap-3 p-5 rounded-xl cursor-pointer transition-all"
                            :class="selectedAnswers[{{ $question->id }}] === '{{ $val }}' ? 'bg-slate-800 shadow-md' : 'bg-white border-2 border-slate-200 hover:border-blue-300'"
                            @click="selectAnswer({{ $question->id }}, '{{ $val }}')">
                            <div class="omr-bubble" :class="selectedAnswers[{{ $question->id }}] === '{{ $val }}' ? 'selected' : ''">
                                <span>{{ $val }}</span>
                            </div>
                            <span class="text-lg font-bold transition-colors"
                                :class="selectedAnswers[{{ $question->id }}] === '{{ $val }}' ? 'text-white' : 'text-slate-700'">{{ $label }}</span>
                        </div>
                        @endforeach
                    </div>

                    @elseif($question->question_type === 'essay')
                    <div class="mb-3 flex items-center gap-2 text-xs text-slate-400 uppercase tracking-widest font-bold">
                        <i class="fas fa-pen text-[8px]"></i> Tulis jawaban
                    </div>
                    <textarea rows="6" class="w-full rounded-xl border-2 border-slate-200 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-3 text-slate-800"
                        placeholder="Tulis jawaban Anda di sini..."
                        x-model="textAnswers[{{ $question->id }}]"
                        @input.debounce.1000ms="saveTextAnswer({{ $question->id }})"></textarea>

                    @elseif($question->question_type === 'fill_blank')
                    <div class="mb-3 flex items-center gap-2 text-xs text-slate-400 uppercase tracking-widest font-bold">
                        <i class="fas fa-pen text-[8px]"></i> Isi jawaban
                    </div>
                    <input type="text" class="w-full rounded-xl border-2 border-slate-200 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-3 text-slate-800"
                        placeholder="Ketik jawaban Anda..."
                        x-model="textAnswers[{{ $question->id }}]"
                        @input.debounce.1000ms="saveTextAnswer({{ $question->id }})">
                    @endif
                </div>

                {{-- Navigation --}}
                <div class="flex justify-between mt-4">
                    <button @click="prevQuestion()" x-show="currentIndex > 0"
                        class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition text-sm font-medium shadow-sm">
                        <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                    </button>
                    <div x-show="currentIndex === 0"></div>
                    <button @click="nextQuestion()" x-show="currentIndex < totalQuestions - 1"
                        class="px-5 py-2.5 bg-slate-800 text-white rounded-xl hover:bg-slate-900 transition text-sm font-medium shadow-sm">
                        Selanjutnya <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                    <button x-show="currentIndex === totalQuestions - 1" @click="confirmSubmit()"
                        class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition text-sm font-bold shadow-sm">
                        <i class="fas fa-paper-plane mr-1"></i> Kumpulkan Ujian
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- RIGHT: OMR Answer Sheet Panel --}}
        <div class="hidden lg:flex flex-col w-[320px] flex-shrink-0 border-l border-slate-200 bg-white" style="height: calc(100vh - 72px);">

            {{-- Sheet Header --}}
            <div class="p-4 border-b border-slate-200 bg-slate-50">
                <div class="text-center">
                    <div class="text-[10px] uppercase tracking-[3px] text-slate-400 font-bold">Lembar Jawaban</div>
                    <div class="text-xs text-slate-500 mt-1">{{ $exam->exam_title }}</div>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                    <div class="bg-white rounded-lg p-2 border border-slate-200">
                        <div class="text-lg font-black text-green-600" x-text="answeredCount">0</div>
                        <div class="text-[10px] text-slate-400 uppercase">Dijawab</div>
                    </div>
                    <div class="bg-white rounded-lg p-2 border border-slate-200">
                        <div class="text-lg font-black text-slate-400" x-text="totalQuestions - answeredCount">0</div>
                        <div class="text-[10px] text-slate-400 uppercase">Belum</div>
                    </div>
                    <div class="bg-white rounded-lg p-2 border border-slate-200">
                        <div class="text-lg font-black text-amber-500" x-text="flaggedCount">0</div>
                        <div class="text-[10px] text-slate-400 uppercase">Ditandai</div>
                    </div>
                </div>
            </div>

            {{-- OMR Sheet Rows --}}
            <div class="flex-1 overflow-y-auto omr-sheet">
                @foreach($questions as $idx => $question)
                @if($question->question_type === 'multiple_choice')
                @php
                    $opts = $question->options->sortBy('sort_order');
                    if(isset($optionOrders[$question->id])) {
                        $orderMap = collect($optionOrders[$question->id]);
                        $opts = $orderMap->map(fn($l) => $question->options->firstWhere('option_label', $l))->filter();
                    }
                @endphp
                <div class="omr-row" :class="currentIndex === {{ $idx }} ? 'bg-blue-50' : ''">
                    <div class="q-number cursor-pointer" @click="goToQuestion({{ $idx }})"
                        :class="currentIndex === {{ $idx }} ? 'current' : (answeredQuestions[{{ $question->id }}] ? 'answered' : 'unanswered')">
                        {{ $idx + 1 }}
                    </div>
                    <div class="flex items-center gap-1.5 flex-1">
                        @foreach($opts as $oIdx => $opt)
                        <div class="omr-bubble w-9 h-9 text-xs"
                            :class="selectedAnswers[{{ $question->id }}] === '{{ $opt->option_label }}' ? 'selected' : ''"
                            @click="selectAnswer({{ $question->id }}, '{{ $opt->option_label }}'); goToQuestion({{ $idx }})">
                            <span>{{ chr(65 + $oIdx) }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="w-5 flex-shrink-0 text-center">
                        <i class="fas fa-bookmark text-xs cursor-pointer transition"
                            :class="flaggedQuestions[{{ $question->id }}] ? 'text-amber-400' : 'text-slate-200 hover:text-slate-400'"
                            @click="toggleFlag({{ $question->id }})"></i>
                    </div>
                </div>

                @elseif($question->question_type === 'true_false')
                <div class="omr-row" :class="currentIndex === {{ $idx }} ? 'bg-blue-50' : ''">
                    <div class="q-number cursor-pointer" @click="goToQuestion({{ $idx }})"
                        :class="currentIndex === {{ $idx }} ? 'current' : (answeredQuestions[{{ $question->id }}] ? 'answered' : 'unanswered')">
                        {{ $idx + 1 }}
                    </div>
                    <div class="flex items-center gap-1.5 flex-1">
                        <div class="omr-bubble w-9 h-9 text-xs"
                            :class="selectedAnswers[{{ $question->id }}] === 'T' ? 'selected' : ''"
                            @click="selectAnswer({{ $question->id }}, 'T'); goToQuestion({{ $idx }})"><span>B</span></div>
                        <div class="omr-bubble w-9 h-9 text-xs"
                            :class="selectedAnswers[{{ $question->id }}] === 'F' ? 'selected' : ''"
                            @click="selectAnswer({{ $question->id }}, 'F'); goToQuestion({{ $idx }})"><span>S</span></div>
                    </div>
                    <div class="w-5 flex-shrink-0 text-center">
                        <i class="fas fa-bookmark text-xs cursor-pointer transition"
                            :class="flaggedQuestions[{{ $question->id }}] ? 'text-amber-400' : 'text-slate-200 hover:text-slate-400'"
                            @click="toggleFlag({{ $question->id }})"></i>
                    </div>
                </div>

                @else {{-- essay / fill_blank --}}
                <div class="omr-row" :class="currentIndex === {{ $idx }} ? 'bg-blue-50' : ''">
                    <div class="q-number cursor-pointer" @click="goToQuestion({{ $idx }})"
                        :class="currentIndex === {{ $idx }} ? 'current' : (answeredQuestions[{{ $question->id }}] ? 'answered' : 'unanswered')">
                        {{ $idx + 1 }}
                    </div>
                    <div class="flex-1 text-xs text-slate-400 italic pl-1">
                        <span x-show="answeredQuestions[{{ $question->id }}]" class="text-green-600 not-italic font-medium"><i class="fas fa-check mr-1"></i>Terisi</span>
                        <span x-show="!answeredQuestions[{{ $question->id }}]">Esai — klik untuk mengisi</span>
                    </div>
                    <div class="w-5 flex-shrink-0 text-center">
                        <i class="fas fa-bookmark text-xs cursor-pointer transition"
                            :class="flaggedQuestions[{{ $question->id }}] ? 'text-amber-400' : 'text-slate-200 hover:text-slate-400'"
                            @click="toggleFlag({{ $question->id }})"></i>
                    </div>
                </div>
                @endif
                @endforeach
            </div>

            {{-- Sheet Footer --}}
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                <button @click="confirmSubmit()" class="w-full py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-bold text-sm shadow">
                    <i class="fas fa-paper-plane mr-2"></i> Kumpulkan Ujian
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Bottom Bar --}}
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-slate-200 px-4 py-2.5 z-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">Soal</span>
                <span class="font-bold text-slate-800" x-text="(currentIndex + 1) + '/' + totalQuestions"></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs"><span class="text-green-600 font-bold" x-text="answeredCount"></span>/<span x-text="totalQuestions"></span> dijawab</span>
            </div>
            <div class="flex gap-1.5">
                <button @click="prevQuestion()" class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-sm"><i class="fas fa-chevron-left"></i></button>
                <button @click="nextQuestion()" class="w-9 h-9 rounded-lg bg-slate-800 text-white flex items-center justify-center text-sm"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>

    {{-- Paused State Fullscreen Overlay --}}
    <div x-show="isPaused" x-cloak class="fixed inset-0 z-[100] bg-slate-900/95 backdrop-blur-md flex flex-col items-center justify-center text-white p-6">
        <div class="w-24 h-24 rounded-full bg-amber-500 flex items-center justify-center mb-6 shadow-lg shadow-amber-500/30 animate-pulse text-slate-900">
            <i class="fas fa-pause text-4xl"></i>
        </div>
        <h2 class="text-2xl font-black mb-2 text-center tracking-tight">UJIAN DITANGGUHKAN SEMENTARA</h2>
        <p class="text-slate-400 text-center max-w-md text-sm leading-relaxed">
            Pengawas telah menangguhkan jalannya ujian. Seluruh pengerjaan dan timer dihentikan sementara secara aman. Mohon tunggu instruksi selanjutnya.
        </p>
    </div>

    {{-- Submit Form --}}
    <form id="submitForm" action="{{ route('siswa.cbt.submit', $session) }}" method="POST" class="hidden">@csrf</form>

<script>
function examApp() {
    return {
        currentIndex: 0,
        totalQuestions: {{ count($questions) }},
        remainingSeconds: {{ $remainingSeconds }},
        timerDisplay: '',
        timerUrgent: false,
        timerInterval: null,
        tabSwitchCount: {{ $session->tab_switch_count ?? 0 }},
        selectedAnswers: @json($answers->mapWithKeys(fn($a) => [$a->question_id => $a->selected_option])->filter()->toArray()),
        textAnswers: @json($answers->mapWithKeys(fn($a) => [$a->question_id => $a->text_answer])->filter()->toArray()),
        answeredQuestions: {},
        flaggedQuestions: {},
        sessionId: {{ $session->id }},
        csrfToken: '{{ csrf_token() }}',
        savingAnswer: false,
        pendingSave: null,

        // Connection and Pausing variables
        isOnline: navigator.onLine,
        isPaused: {{ $exam->is_paused ? 'true' : 'false' }},
        unsyncedAnswers: {},

        get answeredCount() {
            return Object.keys(this.answeredQuestions).filter(k => this.answeredQuestions[k]).length;
        },

        get flaggedCount() {
            return Object.keys(this.flaggedQuestions).filter(k => this.flaggedQuestions[k]).length;
        },

        init() {
            // Load pending local answers if any
            let key = 'cbt_pending_' + this.sessionId;
            this.unsyncedAnswers = JSON.parse(localStorage.getItem(key) || '{}');

            for (let qid in this.selectedAnswers) {
                if (this.selectedAnswers[qid]) this.answeredQuestions[qid] = true;
            }
            for (let qid in this.textAnswers) {
                if (this.textAnswers[qid]) this.answeredQuestions[qid] = true;
            }
            // Mark locally pending questions as answered
            for (let qid in this.unsyncedAnswers) {
                this.answeredQuestions[qid] = true;
                if (this.unsyncedAnswers[qid].selected_option) {
                    this.selectedAnswers[qid] = this.unsyncedAnswers[qid].selected_option;
                }
                if (this.unsyncedAnswers[qid].text_answer) {
                    this.textAnswers[qid] = this.unsyncedAnswers[qid].text_answer;
                }
            }

            this.updateTimer();
            this.timerInterval = setInterval(() => {
                if (!this.isPaused) {
                    this.remainingSeconds--;
                    this.updateTimer();
                    if (this.remainingSeconds <= 0) {
                        clearInterval(this.timerInterval);
                        this.autoSubmit();
                    }
                }
            }, 1000);

            // Network event listeners
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.syncLocalBackup();
            });
            window.addEventListener('offline', () => {
                this.isOnline = false;
            });

            // Heartbeat check for pausing (every 12 seconds)
            setInterval(() => {
                if (!this.isOnline) return;
                fetch(`{{ url('siswa/cbt/sessions') }}/${this.sessionId}/heartbeat`)
                    .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                    .then(data => {
                        this.isPaused = !!data.is_paused;
                        this.remainingSeconds = data.remaining_seconds;
                        this.updateTimer();
                        if (data.status && data.status !== 'in_progress') {
                            alert('Ujian Anda telah dikumpulkan/ditutup oleh pengawas.');
                            window.location.href = '{{ route("siswa.cbt.result", $exam) }}';
                        }
                    })
                    .catch(err => console.error('Heartbeat check failed'));
            }, 12000);

            // Sync any existing offline answers immediately on load
            this.syncLocalBackup();

            @if($exam->prevent_tab_switch)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden && !this.isPaused) {
                    this.tabSwitchCount++;
                    fetch(`{{ url('siswa/cbt/sessions') }}/${this.sessionId}/tab-switch`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    }).then(r => r.json()).then(data => {
                        if (data.auto_submitted) {
                            alert(data.message);
                            window.location.href = '{{ route("siswa.cbt.result", $exam) }}';
                        }
                    });
                }
            });
            @endif

            @if($exam->prevent_copy_paste)
            ['copy','paste','cut','contextmenu'].forEach(e => document.addEventListener(e, ev => ev.preventDefault()));
            @endif
        },

        updateTimer() {
            let s = Math.max(0, this.remainingSeconds);
            let h = Math.floor(s / 3600);
            let m = Math.floor((s % 3600) / 60);
            let sec = s % 60;
            this.timerDisplay = (h > 0 ? String(h).padStart(2,'0') + ':' : '') + String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
            this.timerUrgent = s < 300;
        },

        goToQuestion(idx) { this.currentIndex = idx; },
        prevQuestion() { if (this.currentIndex > 0) this.currentIndex--; },
        nextQuestion() { if (this.currentIndex < this.totalQuestions - 1) this.currentIndex++; },
        toggleFlag(qid) { this.flaggedQuestions[qid] = !this.flaggedQuestions[qid]; },

        selectAnswer(questionId, value) {
            this.selectedAnswers[questionId] = value;
            this.answeredQuestions[questionId] = true;
            this.saveToServer(questionId, value, null);
        },

        saveTextAnswer(questionId) {
            let text = this.textAnswers[questionId] || '';
            this.answeredQuestions[questionId] = !!text.trim();
            this.saveToServer(questionId, null, text);
        },

        saveToServer(questionId, selectedOption, textAnswer) {
            // Write to local cache first
            let key = 'cbt_pending_' + this.sessionId;
            let pending = JSON.parse(localStorage.getItem(key) || '{}');
            pending[questionId] = {
                question_id: questionId,
                selected_option: selectedOption,
                text_answer: textAnswer,
                timestamp: Date.now()
            };
            localStorage.setItem(key, JSON.stringify(pending));
            this.unsyncedAnswers = pending;

            if (!this.isOnline) return; // Stored locally, will sync when online

            // Queue pending save — always keep the latest answer
            this.pendingSave = { questionId, selectedOption, textAnswer };
            if (this.savingAnswer) return;
            this._processSaveQueue();
        },

        _processSaveQueue() {
            if (!this.pendingSave) return;
            this.savingAnswer = true;
            const data = this.pendingSave;
            this.pendingSave = null;

            fetch(`{{ url('siswa/cbt/sessions') }}/${this.sessionId}/save-answer`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify({ question_id: data.questionId, selected_option: data.selectedOption, text_answer: data.textAnswer })
            })
            .then(r => { if (!r.ok) throw new Error('Save failed'); return r.json(); })
            .then(() => {
                // remove from local storage on successful save
                let key = 'cbt_pending_' + this.sessionId;
                let pending = JSON.parse(localStorage.getItem(key) || '{}');
                delete pending[data.questionId];
                localStorage.setItem(key, JSON.stringify(pending));
                this.unsyncedAnswers = pending;
            })
            .catch(err => console.error('Save error:', err))
            .finally(() => {
                this.savingAnswer = false;
                // Process next queued save if any
                if (this.pendingSave) this._processSaveQueue();
            });
        },

        syncLocalBackup() {
            if (!this.isOnline) return Promise.resolve();
            let key = 'cbt_pending_' + this.sessionId;
            let pending = JSON.parse(localStorage.getItem(key) || '{}');
            let keys = Object.keys(pending);
            if (keys.length === 0) return Promise.resolve();

            let pChain = Promise.resolve();
            keys.forEach(qid => {
                let data = pending[qid];
                pChain = pChain.then(() => {
                    return fetch(`{{ url('siswa/cbt/sessions') }}/${this.sessionId}/save-answer`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                        body: JSON.stringify({ question_id: data.question_id, selected_option: data.selected_option, text_answer: data.text_answer })
                    })
                    .then(r => {
                        if (r.ok) {
                            let currentPending = JSON.parse(localStorage.getItem(key) || '{}');
                            delete currentPending[qid];
                            localStorage.setItem(key, JSON.stringify(currentPending));
                            this.unsyncedAnswers = currentPending;
                        }
                    })
                    .catch(err => console.error('Sync failed for question ' + qid, err));
                });
            });
            return pChain;
        },

        async confirmSubmit() {
            // Menunggu SEMUA jawaban lokal tersinkronisasi SEBELUM mengumpulkan
            if (Object.keys(this.unsyncedAnswers).length > 0) {
                if (this.isOnline) {
                    await this.syncLocalBackup();
                    // Cek ulang: kalau masih ada yang gagal sync, jangan submit dulu
                    if (Object.keys(this.unsyncedAnswers).length > 0) {
                        alert('Sebagian jawaban belum berhasil tersinkronisasi ke server. Periksa koneksi lalu coba kumpulkan lagi. (Jangan tutup halaman ini)');
                        return;
                    }
                } else {
                    alert('Tidak dapat mengumpulkan ujian. Ada jawaban yang belum tersinkronisasi ke server dan Anda sedang offline. Silakan periksa koneksi internet Anda.');
                    return;
                }
            }
            let unanswered = this.totalQuestions - this.answeredCount;
            let msg = unanswered > 0
                ? `Masih ada ${unanswered} soal belum dijawab.\nYakin ingin mengumpulkan ujian?`
                : 'Yakin ingin mengumpulkan ujian?';
            if (confirm(msg)) {
                // Hapus backup HANYA setelah semua jawaban sukses tersinkronisasi
                localStorage.removeItem('cbt_pending_' + this.sessionId);
                document.getElementById('submitForm').submit();
            }
        },

        async autoSubmit() {
            // Sinkronisasi jawaban yang belum terkirim SEBELUM auto-submit
            if (Object.keys(this.unsyncedAnswers).length > 0) {
                if (this.isOnline) {
                    await this.syncLocalBackup();
                }
            }
            localStorage.removeItem('cbt_pending_' + this.sessionId);
            alert('Waktu ujian telah habis! Jawaban dikumpulkan otomatis.');
            document.getElementById('submitForm').submit();
        },

        beforeUnload(e) {
            if (Object.keys(this.unsyncedAnswers).length > 0) {
                e.preventDefault();
                e.returnValue = 'Ujian sedang berlangsung dan ada jawaban lokal yang belum tersinkronisasi. Yakin ingin meninggalkan halaman?';
            } else {
                e.preventDefault();
                e.returnValue = 'Ujian sedang berlangsung. Yakin ingin meninggalkan halaman?';
            }
        }
    }
}
</script>
</body>
</html>
