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
                    <p class="text-xs text-slate-500">
                        {{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }} &middot; {{ $exam->duration_minutes }} menit
                        @if($exam->scoring_mode === 'competition')
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            ⚡ Benar +{{ $exam->correct_points }} &bull; Salah -{{ $exam->wrong_penalty }} &bull; Kosong 0
                        </span>
                        @endif
                    </p>
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

                            {{-- Option Text & Image --}}
                            <div class="flex-1 min-w-0">
                                @if(!empty(trim($option->option_text)) && trim($option->option_text) !== '(Gambar)')
                                <span class="text-[15px] leading-relaxed transition-colors block"
                                    :class="selectedAnswers[{{ $question->id }}] === '{{ $option->option_label }}' ? 'text-white font-medium' : 'text-slate-700'">
                                    {{ $option->option_text }}
                                </span>
                                @endif
                                @if($option->option_image)
                                <div class="mt-1.5 inline-block">
                                    <img src="{{ $option->option_image_url }}" alt="Opsi {{ $displayLabel }}"
                                         class="max-w-full sm:max-w-md max-h-48 object-contain rounded-xl border border-slate-200 bg-white p-2 shadow-xs transition hover:scale-[1.02] cursor-pointer"
                                         onclick="event.stopPropagation(); window.open(this.src, '_blank')"
                                         title="Klik untuk memperbesar gambar">
                                </div>
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
                    <div x-data="{
                            essayMode: (handwritingAnswers[{{ $question->id }}] ? 'draw' : 'text')
                         }"
                         x-init="$watch('currentIndex', () => { if (currentIndex === {{ $idx }}) window.dispatchEvent(new CustomEvent('cbt-resize-canvas-{{ $question->id }}')); })"
                         class="space-y-3">

                        {{-- Mode Selector Tabs --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-slate-200">
                            <div class="flex items-center gap-1.5 p-1 bg-slate-200/70 rounded-xl text-xs font-bold">
                                <button type="button" @click="essayMode = 'text'"
                                    :class="essayMode === 'text' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                                    <i class="fas fa-keyboard"></i>
                                    <span>Ketik Teks</span>
                                    <span x-show="textAnswers[{{ $question->id }}] && textAnswers[{{ $question->id }}].trim()" class="w-2 h-2 rounded-full bg-emerald-500" title="Teks terisi"></span>
                                </button>
                                <button type="button" @click="essayMode = 'draw'; $nextTick(() => window.dispatchEvent(new CustomEvent('cbt-resize-canvas-{{ $question->id }}')))"
                                    :class="essayMode === 'draw' ? 'bg-white text-purple-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                                    <i class="fas fa-signature text-purple-600"></i>
                                    <span>Tulis Tangan</span>
                                    <span x-show="handwritingAnswers[{{ $question->id }}]" class="w-2 h-2 rounded-full bg-purple-500" title="Tulisan tangan tersimpan"></span>
                                </button>
                                <button type="button" @click="essayMode = 'both'; $nextTick(() => window.dispatchEvent(new CustomEvent('cbt-resize-canvas-{{ $question->id }}')))"
                                    :class="essayMode === 'both' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-3 py-1.5 rounded-lg transition hidden sm:flex items-center gap-1.5">
                                    <i class="fas fa-columns text-blue-600"></i>
                                    <span>Teks + Tulis Tangan</span>
                                </button>
                            </div>

                            <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                                <span class="hidden md:inline text-slate-400">Pilihan input untuk:</span>
                                <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 font-semibold border border-purple-200/60">Arab / Jepang / Rumus</span>
                            </div>
                        </div>

                        {{-- Mode 1: Keyboard Textarea --}}
                        <div x-show="essayMode === 'text' || essayMode === 'both'" class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <i class="fas fa-pen text-[10px] mr-1"></i> Jawaban Ketik (Teks):
                            </label>
                            <textarea rows="5" class="w-full rounded-xl border-2 border-slate-200 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-3 text-slate-800 text-[15px] leading-relaxed transition"
                                placeholder="Ketik jawaban Anda di sini..."
                                x-model="textAnswers[{{ $question->id }}]"
                                @input.debounce.1000ms="saveTextAnswer({{ $question->id }})"></textarea>
                        </div>

                        {{-- Mode 2: Handwriting Canvas Pad --}}
                        <div x-show="essayMode === 'draw' || essayMode === 'both'"
                             x-data="cbtHandwritingPad({{ $question->id }}, '{{ $answers[$question->id]->handwriting_image_url ?? '' }}')"
                             x-init="init()"
                             class="space-y-2">

                            <div class="flex flex-wrap items-center justify-between gap-2 bg-slate-100 p-2.5 rounded-xl border border-slate-200">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1">
                                        <i class="fas fa-pen-nib text-purple-600"></i> Pena:
                                    </span>
                                    {{-- Color choices --}}
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="setColor('#0f172a')"
                                            :class="color === '#0f172a' ? 'ring-2 ring-purple-500 scale-110' : 'opacity-70 hover:opacity-100'"
                                            class="w-6 h-6 rounded-full bg-slate-900 transition shadow-xs" title="Tinta Hitam"></button>
                                        <button type="button" @click="setColor('#1d4ed8')"
                                            :class="color === '#1d4ed8' ? 'ring-2 ring-purple-500 scale-110' : 'opacity-70 hover:opacity-100'"
                                            class="w-6 h-6 rounded-full bg-blue-600 transition shadow-xs" title="Tinta Biru"></button>
                                        <button type="button" @click="setColor('#047857')"
                                            :class="color === '#047857' ? 'ring-2 ring-purple-500 scale-110' : 'opacity-70 hover:opacity-100'"
                                            class="w-6 h-6 rounded-full bg-emerald-600 transition shadow-xs" title="Tinta Hijau"></button>
                                    </div>

                                    <div class="h-4 w-px bg-slate-300 mx-1"></div>

                                    {{-- Size choices --}}
                                    <div class="flex items-center gap-1 text-xs">
                                        <button type="button" @click="setSize(2.5)"
                                            :class="size === 2.5 ? 'bg-purple-600 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200'"
                                            class="px-2 py-1 rounded-md transition text-[11px]">Sedang</button>
                                        <button type="button" @click="setSize(4.5)"
                                            :class="size === 4.5 ? 'bg-purple-600 text-white font-bold' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-200'"
                                            class="px-2 py-1 rounded-md transition text-[11px]">Tebal</button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" @click="undo()" :disabled="history.length === 0"
                                        :class="history.length === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-200 active:scale-95 cursor-pointer'"
                                        class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-1 transition shadow-xs">
                                        <i class="fas fa-undo text-[11px]"></i>
                                        <span>Undo</span>
                                    </button>
                                    <button type="button" @click="clear()"
                                        class="px-2.5 py-1 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-semibold flex items-center gap-1 transition active:scale-95 cursor-pointer">
                                        <i class="fas fa-trash-alt text-[11px]"></i>
                                        <span>Bersihkan</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Canvas Wrapper with Lined Paper Effect --}}
                            <div class="relative w-full rounded-2xl overflow-hidden border-2 border-slate-300 bg-white shadow-inner group">
                                <canvas x-ref="canvas"
                                    class="w-full block cursor-crosshair touch-none"
                                    style="height: 280px; background-color: #ffffff; background-image: repeating-linear-gradient(transparent, transparent 27px, #e2e8f0 27px, #e2e8f0 28px); background-position: 0 20px;"
                                    @pointerdown="startDrawing($event)"
                                    @pointermove="draw($event)"
                                    @pointerup="stopDrawing($event)"
                                    @pointerleave="stopDrawing($event)"
                                    @pointercancel="stopDrawing($event)"></canvas>

                                {{-- Watermark guide --}}
                                <div class="pointer-events-none absolute bottom-2 right-3 text-[11px] font-semibold text-slate-400 bg-white/80 px-2 py-0.5 rounded-md border border-slate-200 flex items-center gap-1.5">
                                    <span x-show="isSaving" class="text-amber-600"><i class="fas fa-spinner fa-spin"></i> Menyimpan...</span>
                                    <span x-show="!isSaving && hasDrawn" class="text-emerald-600"><i class="fas fa-check-circle"></i> Tulisan tersimpan otomatis</span>
                                    <span x-show="!isSaving && !hasDrawn"><i class="fas fa-pen-nib"></i> Sentuh atau gunakan mouse untuk menulis</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-500 italic">
                                * Cocok untuk mapel Agama Islam (ayat/tulisan Arab), Bahasa Jepang (Hiragana/Katakana/Kanji), maupun notasi matematika. Goresan Anda tersimpan otomatis.
                            </p>
                        </div>
                    </div>

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
                <div class="flex items-center justify-between mt-4 gap-2">
                    <button @click="prevQuestion()" x-show="currentIndex > 0"
                        class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition text-sm font-medium shadow-sm">
                        <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                    </button>
                    <div x-show="currentIndex === 0"></div>

                    {{-- Tombol Kosongkan Jawaban (Penting untuk mode kompetisi agar tidak kena penalti -1) --}}
                    <button type="button" @click="clearAnswer({{ $question->id }})"
                        x-show="selectedAnswers[{{ $question->id }}] || textAnswers[{{ $question->id }}]"
                        class="px-4 py-2.5 bg-amber-50 border border-amber-300 text-amber-800 rounded-xl hover:bg-amber-100 transition text-xs md:text-sm font-semibold shadow-sm flex items-center gap-1.5"
                        title="Kosongkan jawaban soal ini agar tidak mendapat penalti nilai">
                        <i class="fas fa-undo text-amber-600"></i>
                        <span>Kosongkan / Lewati</span>
                    </button>

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
        handwritingAnswers: @json($answers->mapWithKeys(fn($a) => [$a->question_id => $a->handwriting_image_url])->filter()->toArray()),
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
            for (let qid in this.handwritingAnswers) {
                if (this.handwritingAnswers[qid]) this.answeredQuestions[qid] = true;
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
                if (this.unsyncedAnswers[qid].handwriting_data) {
                    this.handwritingAnswers[qid] = this.unsyncedAnswers[qid].handwriting_data;
                }
            }

            // Listen to handwriting save events from drawing canvas pads
            window.addEventListener('save-handwriting', (e) => {
                this.saveHandwriting(e.detail.questionId, e.detail.dataUrl);
            });

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
            // Toggle deselect if same option is clicked again
            if (this.selectedAnswers[questionId] === value) {
                this.clearAnswer(questionId);
                return;
            }
            this.selectedAnswers[questionId] = value;
            this.answeredQuestions[questionId] = true;
            this.saveToServer(questionId, value, null);
        },

        clearAnswer(questionId) {
            delete this.selectedAnswers[questionId];
            delete this.textAnswers[questionId];
            delete this.handwritingAnswers[questionId];
            this.answeredQuestions[questionId] = false;
            window.dispatchEvent(new CustomEvent('cbt-clear-canvas-' + questionId));
            this.saveToServer(questionId, null, null, '');
        },

        saveTextAnswer(questionId) {
            let text = this.textAnswers[questionId] || '';
            let hasHw = !!this.handwritingAnswers[questionId];
            this.answeredQuestions[questionId] = !!text.trim() || hasHw;
            this.saveToServer(questionId, null, text, undefined);
        },

        saveHandwriting(questionId, dataUrl) {
            if (dataUrl) {
                this.handwritingAnswers[questionId] = dataUrl;
                this.answeredQuestions[questionId] = true;
            } else {
                delete this.handwritingAnswers[questionId];
                let hasText = this.textAnswers[questionId] && this.textAnswers[questionId].trim();
                this.answeredQuestions[questionId] = !!hasText;
            }
            let currentText = this.textAnswers[questionId] || '';
            this.saveToServer(questionId, null, currentText, dataUrl || '');
        },

        saveToServer(questionId, selectedOption, textAnswer, handwritingData) {
            // Write to local cache first
            let key = 'cbt_pending_' + this.sessionId;
            let pending = JSON.parse(localStorage.getItem(key) || '{}');
            let item = pending[questionId] || { question_id: questionId };
            if (typeof selectedOption !== 'undefined') item.selected_option = selectedOption;
            if (typeof textAnswer !== 'undefined') item.text_answer = textAnswer;
            if (typeof handwritingData !== 'undefined') item.handwriting_data = handwritingData;
            item.timestamp = Date.now();
            pending[questionId] = item;
            localStorage.setItem(key, JSON.stringify(pending));
            this.unsyncedAnswers = pending;

            if (!this.isOnline) return; // Stored locally, will sync when online

            // Queue pending save — always keep the latest answer
            this.pendingSave = { questionId, selectedOption, textAnswer, handwritingData };
            if (this.savingAnswer) return;
            this._processSaveQueue();
        },

        _processSaveQueue() {
            if (!this.pendingSave) return;
            this.savingAnswer = true;
            const data = this.pendingSave;
            this.pendingSave = null;

            let bodyPayload = {
                question_id: data.questionId,
                selected_option: data.selectedOption,
                text_answer: data.textAnswer,
            };
            if (typeof data.handwritingData !== 'undefined') {
                bodyPayload.handwriting_data = data.handwritingData;
            }

            fetch(`{{ url('siswa/cbt/sessions') }}/${this.sessionId}/save-answer`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify(bodyPayload)
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
                    let bodyPayload = {
                        question_id: data.question_id,
                        selected_option: data.selected_option,
                        text_answer: data.text_answer,
                    };
                    if (typeof data.handwriting_data !== 'undefined') {
                        bodyPayload.handwriting_data = data.handwriting_data;
                    }
                    return fetch(`{{ url('siswa/cbt/sessions') }}/${this.sessionId}/save-answer`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                        body: JSON.stringify(bodyPayload)
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

/**
 * Controller untuk kanvas tulisan tangan (Signature-pad style untuk Arab/Jepang/Mulok)
 */
function cbtHandwritingPad(questionId, initialImageUrl) {
    return {
        questionId: questionId,
        initialImageUrl: initialImageUrl,
        color: '#0f172a',
        size: 3.5,
        isDrawing: false,
        hasDrawn: !!initialImageUrl,
        isSaving: false,
        history: [],
        currentStroke: null,
        ctx: null,
        saveTimeout: null,

        init() {
            this.$nextTick(() => {
                this.setupCanvas();
            });

            window.addEventListener('resize', () => {
                this.handleResize();
            });

            window.addEventListener('cbt-resize-canvas-' + this.questionId, () => {
                this.$nextTick(() => this.handleResize());
            });

            window.addEventListener('cbt-clear-canvas-' + this.questionId, () => {
                this.clear(false);
            });
        },

        setupCanvas() {
            const canvas = this.$refs.canvas;
            if (!canvas) return;
            this.ctx = canvas.getContext('2d');

            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            const displayWidth = rect.width > 50 ? rect.width : (canvas.parentElement?.clientWidth || 600);
            const displayHeight = 280;

            canvas.width = Math.floor(displayWidth * dpr);
            canvas.height = Math.floor(displayHeight * dpr);
            this.ctx.scale(dpr, dpr);

            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';

            if (this.initialImageUrl) {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => {
                    this.ctx.drawImage(img, 0, 0, displayWidth, displayHeight);
                    this.hasDrawn = true;
                };
                img.src = this.initialImageUrl;
            }
        },

        handleResize() {
            const canvas = this.$refs.canvas;
            if (!canvas || !this.ctx) return;
            const rect = canvas.getBoundingClientRect();
            const displayWidth = rect.width > 50 ? rect.width : (canvas.parentElement?.clientWidth || 600);
            const displayHeight = 280;
            const dpr = window.devicePixelRatio || 1;

            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;
            const tempCtx = tempCanvas.getContext('2d');
            tempCtx.drawImage(canvas, 0, 0);

            canvas.width = Math.floor(displayWidth * dpr);
            canvas.height = Math.floor(displayHeight * dpr);
            this.ctx.scale(dpr, dpr);
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';

            this.ctx.drawImage(tempCanvas, 0, 0, tempCanvas.width / dpr, tempCanvas.height / dpr, 0, 0, displayWidth, displayHeight);
        },

        setColor(newColor) {
            this.color = newColor;
        },

        setSize(newSize) {
            this.size = newSize;
        },

        getPos(e) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        },

        startDrawing(e) {
            if (e.pointerType === 'mouse' && e.button !== 0) return;
            try { this.$refs.canvas.setPointerCapture(e.pointerId); } catch(err) {}

            this.isDrawing = true;
            const pos = this.getPos(e);
            this.currentStroke = {
                color: this.color,
                size: this.size,
                points: [pos]
            };

            this.ctx.beginPath();
            this.ctx.strokeStyle = this.color;
            this.ctx.lineWidth = this.size;
            this.ctx.moveTo(pos.x, pos.y);
            this.ctx.lineTo(pos.x + 0.1, pos.y + 0.1);
            this.ctx.stroke();
        },

        draw(e) {
            if (!this.isDrawing || !this.currentStroke) return;
            e.preventDefault();
            const pos = this.getPos(e);
            this.currentStroke.points.push(pos);

            const points = this.currentStroke.points;
            if (points.length < 2) return;

            this.ctx.beginPath();
            this.ctx.strokeStyle = this.color;
            this.ctx.lineWidth = this.size;

            const p1 = points[points.length - 2];
            const p2 = points[points.length - 1];
            const midPoint = {
                x: (p1.x + p2.x) / 2,
                y: (p1.y + p2.y) / 2
            };

            this.ctx.moveTo(p1.x, p1.y);
            this.ctx.quadraticCurveTo(p1.x, p1.y, midPoint.x, midPoint.y);
            this.ctx.stroke();
        },

        stopDrawing(e) {
            if (!this.isDrawing) return;
            this.isDrawing = false;
            try { this.$refs.canvas.releasePointerCapture(e.pointerId); } catch(err) {}

            if (this.currentStroke && this.currentStroke.points.length > 0) {
                this.history.push(this.currentStroke);
                this.currentStroke = null;
                this.hasDrawn = true;
                this.triggerAutoSave();
            }
        },

        triggerAutoSave() {
            clearTimeout(this.saveTimeout);
            this.isSaving = true;
            this.saveTimeout = setTimeout(() => {
                const canvas = this.$refs.canvas;
                const dataUrl = canvas.toDataURL('image/png');
                window.dispatchEvent(new CustomEvent('save-handwriting', {
                    detail: { questionId: this.questionId, dataUrl: dataUrl }
                }));
                this.isSaving = false;
            }, 750);
        },

        undo() {
            if (this.history.length === 0) return;
            this.history.pop();
            this.redrawAll();
            this.triggerAutoSave();
            if (this.history.length === 0 && !this.initialImageUrl) {
                this.hasDrawn = false;
                window.dispatchEvent(new CustomEvent('save-handwriting', {
                    detail: { questionId: this.questionId, dataUrl: '' }
                }));
            }
        },

        redrawAll() {
            const canvas = this.$refs.canvas;
            const rect = canvas.getBoundingClientRect();
            const displayWidth = rect.width > 50 ? rect.width : (canvas.parentElement?.clientWidth || 600);
            const displayHeight = 280;

            this.ctx.clearRect(0, 0, displayWidth, displayHeight);

            for (const stroke of this.history) {
                if (!stroke.points || stroke.points.length === 0) continue;
                this.ctx.beginPath();
                this.ctx.strokeStyle = stroke.color;
                this.ctx.lineWidth = stroke.size;
                this.ctx.lineCap = 'round';
                this.ctx.lineJoin = 'round';

                const points = stroke.points;
                this.ctx.moveTo(points[0].x, points[0].y);
                if (points.length === 1) {
                    this.ctx.lineTo(points[0].x + 0.1, points[0].y + 0.1);
                } else {
                    for (let i = 1; i < points.length; i++) {
                        const midPoint = {
                            x: (points[i - 1].x + points[i].x) / 2,
                            y: (points[i - 1].y + points[i].y) / 2
                        };
                        this.ctx.quadraticCurveTo(points[i - 1].x, points[i - 1].y, midPoint.x, midPoint.y);
                    }
                    this.ctx.lineTo(points[points.length - 1].x, points[points.length - 1].y);
                }
                this.ctx.stroke();
            }
        },

        clear(emitSave = true) {
            const canvas = this.$refs.canvas;
            if (!canvas || !this.ctx) return;
            const rect = canvas.getBoundingClientRect();
            const displayWidth = rect.width > 50 ? rect.width : (canvas.parentElement?.clientWidth || 600);
            const displayHeight = 280;

            this.ctx.clearRect(0, 0, displayWidth, displayHeight);
            this.history = [];
            this.initialImageUrl = null;
            this.hasDrawn = false;

            if (emitSave) {
                window.dispatchEvent(new CustomEvent('save-handwriting', {
                    detail: { questionId: this.questionId, dataUrl: '' }
                }));
            }
        }
    };
}
</script>
</body>
</html>
