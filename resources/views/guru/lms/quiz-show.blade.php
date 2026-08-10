@extends('layouts.guru')

@section('title', $quiz->title . ' - Quiz LMS')

@section('content')
<div class="space-y-6" x-data="{}">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.lms.show', $course->id) }}?tab=quizzes" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-800 hover:bg-amber-100 text-amber-800 transition-all shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">{{ $quiz->title }}</h2>
                <p class="text-gray-800 font-bold text-xs">Course: {{ $course->name }} | Modul: {{ $quiz->module ? ($quiz->module->getCode() . ' · ' . $quiz->module->title) : 'Global' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs flex-wrap">
            <a href="{{ route('guru.lms.quizzes.edit', $quiz->id) }}" class="px-4 py-2.5 rounded-xl bg-white text-gray-800 border border-gray-200 hover:bg-amber-100 text-amber-800 font-semibold tracking-wide transition shadow-sm">
                <i class="fas fa-edit mr-1"></i> Edit Detail
            </a>

            {{-- Tombol Publish/Unpublish --}}
            <form action="{{ route('guru.lms.quizzes.togglePublish', $quiz->id) }}" method="POST">
                @csrf
                @if($quiz->is_published)
                    <button type="submit"
                        onclick="return confirm('Yakin ingin menyembunyikan quiz ini dari siswa?')"
                        class="px-4 py-2.5 rounded-xl bg-emerald-400 text-gray-800 border border-gray-200 hover:bg-emerald-500 transition font-semibold tracking-wide flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-eye"></i> Published
                        <span class="text-[10px] uppercase">(Set Draft)</span>
                    </button>
                @else
                    <button type="submit"
                        class="px-4 py-2.5 rounded-xl bg-amber-400 text-gray-800 border border-gray-200 hover:bg-amber-500 transition font-semibold tracking-wide flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-eye-slash"></i> Draft
                        <span class="text-[10px] uppercase">(Klik untuk Publish!)</span>
                    </button>
                @endif
            </form>

            <a href="{{ route('guru.lms.quizzes.results', $quiz->id) }}" class="bg-purple-600 text-white hover:bg-purple-700 border border-gray-200 px-4 py-2.5 rounded-xl font-semibold tracking-wide transition shadow-md">
                <i class="fas fa-chart-bar mr-1 text-amber-500"></i> {{ $quiz->attempts_count }} Percobaan
            </a>

            <form action="{{ route('guru.lms.quizzes.destroy', $quiz->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus quiz ini beserta semua percobaan siswa? Tindakan ini tidak dapat dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-white text-red-600 hover:bg-red-100 border-2 border-red-300 px-4 py-2.5 rounded-xl font-semibold tracking-wide transition shadow-sm text-xs">
                    <i class="fas fa-trash mr-1"></i> Hapus Quiz
                </button>
            </form>
        </div>
    </div>

    <!-- Quiz Info & Settings -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
        {{-- Alert status quiz --}}
        @if(!$quiz->is_published)
        <div class="mb-5 bg-amber-100 border border-gray-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-gray-800 text-xl"></i>
            <div>
                <p class="text-gray-800 font-semibold text-sm tracking-wide">Status Quiz: DRAFT (Tersembunyi)</p>
                <p class="text-gray-800 font-bold text-xs mt-0.5">Siswa belum dapat melihat quiz ini. Klik tombol <strong>"Draft (Klik untuk Publish!)"</strong> di atas agar terbit untuk siswa.</p>
            </div>
        </div>
        @elseif($quiz->start_time && now()->isBefore($quiz->start_time))
        <div class="mb-5 bg-amber-200 border border-gray-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-clock text-gray-800 text-xl"></i>
            <div>
                <p class="text-gray-800 font-semibold text-sm tracking-wide">⚠️ Quiz Terbit tapi BELUM DAPAT DIBUKA Siswa</p>
                <p class="text-gray-800 font-bold text-xs mt-0.5">Waktu Mulai diset pada: <strong>{{ $quiz->start_time->format('d M Y H:i') }}</strong>.</p>
            </div>
        </div>
        @elseif($quiz->end_time && now()->isAfter($quiz->end_time))
        <div class="mb-5 bg-rose-200 border border-gray-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-times-circle text-gray-800 text-xl"></i>
            <div>
                <p class="text-gray-800 font-semibold text-sm tracking-wide">⚠️ Waktu Pengerjaan Quiz Telah Berakhir</p>
                <p class="text-gray-800 font-bold text-xs mt-0.5">Quiz berakhir pada: <strong>{{ $quiz->end_time->format('d M Y H:i') }}</strong>.</p>
            </div>
        </div>
        @else
        <div class="mb-5 bg-emerald-200 border border-gray-200 rounded-xl p-4 flex items-center gap-3">
            <i class="fas fa-check-circle text-gray-800 text-xl"></i>
            <p class="text-gray-800 font-semibold text-xs tracking-wide">Quiz Aktif & Terbit — Siswa dapat langsung mengerjakan quiz ini.</p>
        </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
        <div class="mb-5 bg-rose-100 border border-gray-200 rounded-xl p-4">
            <p class="text-gray-800 font-semibold text-xs mb-1 tracking-wide"><i class="fas fa-times-circle mr-1 text-rose-600"></i> Terjadi kesalahan input:</p>
            <ul class="list-disc ml-5 text-gray-800 font-bold text-xs space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('guru.lms.quizzes.update', $quiz->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-1">Judul Quiz</label>
                    <input type="text" name="title" value="{{ old('title', $quiz->title) }}" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 font-semibold focus:ring-4 focus:ring-black/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-1">Durasi Waktu (Menit)</label>
                    <input type="number" name="time_limit" value="{{ old('time_limit', $quiz->time_limit) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 font-semibold focus:ring-4 focus:ring-black/20 outline-none">
                </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Passing Score (%)</label>
                    <input type="number" name="passing_score" value="{{ old('passing_score', $quiz->passing_score) }}" required min="0" max="100" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs text-gray-500">Maks. Percobaan</label>
                    <input type="number" name="max_attempts" value="{{ old('max_attempts', $quiz->max_attempts) }}" min="1" max="10" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                {{-- start_time / end_time: KRITIS - jika diisi, quiz tidak bisa dikerjakan diluar rentang waktu --}}
                <div>
                    <label class="text-xs text-gray-500 flex items-center gap-1">
                        Waktu Mulai
                        <span class="text-orange-500 font-bold">⚠</span>
                        <span class="text-[9px] text-gray-400">(kosongkan = langsung tersedia)</span>
                    </label>
                    <input type="datetime-local" name="start_time"
                        value="{{ old('start_time', $quiz->start_time ? $quiz->start_time->format('Y-m-d\TH:i') : '') }}"
                        class="w-full border {{ ($quiz->start_time && now()->isBefore($quiz->start_time)) ? 'border-orange-400 bg-orange-50' : 'border-gray-200' }} rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs text-gray-500 flex items-center gap-1">
                        Waktu Selesai
                        <span class="text-red-400 font-bold">⚠</span>
                        <span class="text-[9px] text-gray-400">(kosongkan = tidak ada batas)</span>
                    </label>
                    <input type="datetime-local" name="end_time"
                        value="{{ old('end_time', $quiz->end_time ? $quiz->end_time->format('Y-m-d\TH:i') : '') }}"
                        class="w-full border {{ ($quiz->end_time && now()->isAfter($quiz->end_time)) ? 'border-red-400 bg-red-50' : 'border-gray-200' }} rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="flex items-center gap-4 pt-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="shuffle_questions" value="0">
                        <input type="checkbox" name="shuffle_questions" value="1" {{ $quiz->shuffle_questions ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600">
                        <span class="text-sm text-gray-700">Acak Soal</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="show_result" value="0">
                        <input type="checkbox" name="show_result" value="1" {{ $quiz->show_result ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600">
                        <span class="text-sm text-gray-700">Tampilkan Hasil</span>
                    </label>
                </div>
                <div class="flex items-end">
                    <input type="hidden" name="description" value="{{ $quiz->description }}">
                    <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
        <div class="mt-3 text-xs text-gray-400">
            Total Skor: <strong class="text-gray-700">{{ $totalScore }}</strong> | Soal: <strong class="text-gray-700">{{ $quiz->questions->count() }}</strong>
        </div>
    </div>

    <!-- Questions -->
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h3 class="font-bold text-gray-800 text-lg flex-shrink-0"><i class="fas fa-list-ol text-purple-500 mr-2"></i>Daftar Soal</h3>
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Sinkronisasi dari Bank Soal --}}
                @if($quiz->question_package_id)
                <form action="{{ route('guru.lms.quizzes.syncFromBank', $quiz->id) }}" method="POST" onsubmit="return confirm('Soal dari bank soal akan ditambahkan ke quiz ini. Soal yang sudah ada tidak akan terhapus. Lanjutkan?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
                        <i class="fas fa-sync-alt"></i> Sinkron dari Bank Soal
                        @if($quiz->cbtQuestionBank)
                        <span class="text-[10px] opacity-80">({{ $quiz->cbtQuestionBank->bank_name }})</span>
                        @endif
                    </button>
                </form>
                @endif

                <!-- Unduh Template -->
                <a href="{{ route('guru.lms.quizzes.template', $quiz->id) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg transition shadow-sm">
                    <i class="fas fa-download text-purple-500"></i> Unduh Template Soal
                </a>
                
                <!-- Form Impor -->
                <form action="{{ route('guru.lms.quizzes.questions.import', $quiz->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg p-1 shadow-sm">
                    @csrf
                    <input type="file" name="file" required accept=".csv,.xlsx,.xls" class="text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer w-[160px]">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-2.5 py-1 rounded text-xs font-bold transition shadow-sm">
                        <i class="fas fa-file-import"></i> Impor Soal
                    </button>
                </form>
            </div>
        </div>

        @foreach($quiz->questions as $i => $q)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs font-medium">Soal {{ $i + 1 }}</span>
                        <span class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', $q->question_type)) }} · {{ $q->score }} poin</span>
                    </div>
                    <p class="text-gray-800 font-medium">{{ $q->question }}</p>
                    
                    {{-- Media Preview --}}
                    @if($q->image_path)
                    <div class="mt-2 max-w-xs rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                        <img src="{{ asset('storage/' . $q->image_path) }}" class="max-h-[150px] object-contain w-auto h-auto">
                    </div>
                    @endif
                    @if($q->video_url)
                    <div class="mt-2 text-xs text-gray-500 flex items-center gap-1.5">
                        <i class="fas fa-video text-purple-500 animate-pulse"></i> 
                        <span>Video:</span>
                        <a href="{{ $q->video_url }}" target="_blank" class="text-blue-600 hover:underline font-semibold truncate max-w-xs sm:max-w-md">{{ $q->video_url }}</a>
                    </div>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" 
                            class="p-1.5 text-amber-500 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition"
                            title="Edit Soal"
                            @click="$dispatch('edit-question', {{ json_encode([
                                'id' => $q->id,
                                'question' => $q->question,
                                'question_type' => $q->question_type,
                                'score' => $q->score,
                                'correct_answer' => (string)($q->correct_answer ?? ''),
                                'options' => $q->options ?? [],
                                'video_url' => $q->video_url ?? '',
                                'image_path' => $q->image_path ?? '',
                                'action' => route('guru.lms.questions.update', $q->id),
                            ]) }})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('guru.lms.questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Hapus soal?')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>

            @if($q->question_type === 'multiple_choice' && $q->options)
            <div class="mt-2 space-y-1 ml-4">
                @foreach($q->options as $optIdx => $opt)
                @php
                    $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                    $isAssocOpt = is_array($opt) && isset($opt['key']);
                    $optKey = $isAssocOpt ? $opt['key'] : (string)$optIdx;
                    $optLabel = $isAssocOpt ? $opt['key'] : ($alphabet[$optIdx] ?? $optIdx + 1);
                    $optText = $isAssocOpt ? $opt['text'] : $opt;
                    $isCorrectOpt = (string)$q->correct_answer === (string)$optKey;
                @endphp
                <div class="flex items-center gap-2 text-sm {{ $isCorrectOpt ? 'text-green-700 font-semibold' : 'text-gray-600' }}">
                    <span class="w-6 h-6 rounded-full border {{ $isCorrectOpt ? 'bg-green-100 border-green-500' : 'border-gray-300' }} flex items-center justify-center text-xs">{{ $optLabel }}</span>
                    {{ $optText }}
                    @if($isCorrectOpt) <i class="fas fa-check text-green-500 ml-1"></i> @endif
                </div>
                @endforeach
            </div>
            @elseif($q->question_type === 'true_false')
            <p class="text-sm text-gray-500 ml-4 mt-1">Jawaban: <strong class="text-green-600">{{ $q->correct_answer === 'true' ? 'Benar' : 'Salah' }}</strong></p>
            @elseif($q->correct_answer)
            <p class="text-sm text-gray-500 ml-4 mt-1">Kunci: <strong class="text-green-600">{{ $q->correct_answer }}</strong></p>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Add Question Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="{ qtype: 'multiple_choice' }">
        <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-plus-circle text-purple-500 mr-2"></i>Tambah Soal Baru</h4>
        <form action="{{ route('guru.lms.quizzes.questions.store', $quiz->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Pertanyaan <span class="text-red-500">*</span></label>
                    <textarea name="question" rows="2" required class="w-full border rounded-lg px-3 py-2 text-sm mt-1 math-support"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Tipe Soal</label>
                        <select name="question_type" x-model="qtype" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                            <option value="multiple_choice">Pilihan Ganda</option>
                            <option value="true_false">Benar/Salah</option>
                            <option value="short_answer">Jawaban Singkat</option>
                            <option value="essay">Essay</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Skor</label>
                        <input type="number" name="score" value="10" min="0.5" step="0.5" required class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                    </div>
                </div>

                <!-- MC Options -->
                <div x-show="qtype === 'multiple_choice'" class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">Opsi Jawaban</label>
                    @foreach(['A', 'B', 'C', 'D', 'E'] as $key)
                    <div class="flex gap-2 items-center">
                        <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold">{{ $key }}</span>
                        <input type="hidden" name="options[{{ $loop->index }}][key]" value="{{ $key }}">
                        <input type="text" name="options[{{ $loop->index }}][text]" placeholder="Opsi {{ $key }}" class="flex-1 border rounded-lg px-3 py-2 text-sm">
                    </div>
                    @endforeach
                </div>

                <!-- Media Upload Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Gambar Pendukung (Opsional)</label>
                        <input type="file" name="image" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer mt-1">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Video Link / YouTube URL (Opsional)</label>
                        <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                    </div>
                </div>

                <!-- Correct Answer -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Kunci Jawaban</label>
                    <div x-show="qtype === 'multiple_choice'">
                        <select name="correct_answer" :disabled="qtype !== 'multiple_choice'" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                            <option value="" disabled selected>-- Pilih Kunci Jawaban --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                    <div x-show="qtype === 'true_false'">
                        <select name="correct_answer" :disabled="qtype !== 'true_false'" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                            <option value="true">Benar</option>
                            <option value="false">Salah</option>
                        </select>
                    </div>
                    <div x-show="qtype === 'short_answer'">
                        <input type="text" name="correct_answer" :disabled="qtype !== 'short_answer'" placeholder="Jawaban yang benar" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                    </div>
                    <div x-show="qtype === 'essay'">
                        <p class="text-xs text-gray-400 mt-1">Essay dinilai manual oleh guru</p>
                    </div>
                </div>

                <button type="submit" class="bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Soal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Question Modal -->
<div x-data="{ 
        open: false, 
        id: '', 
        question: '', 
        question_type: 'multiple_choice', 
        score: 10, 
        correct_answer: '', 
        options: [
            {key: 'A', text: ''},
            {key: 'B', text: ''},
            {key: 'C', text: ''},
            {key: 'D', text: ''},
            {key: 'E', text: ''}
        ],
        video_url: '',
        image_path: '',
        has_image: false,
        action: ''
     }" 
     @edit-question.window="
        open = true;
        id = $event.detail.id;
        question = $event.detail.question;
        question_type = $event.detail.question_type;
        score = $event.detail.score;
        action = $event.detail.action;
        video_url = $event.detail.video_url || '';
        image_path = $event.detail.image_path || '';
        has_image = !!image_path;
        
        let rawCorrect = $event.detail.correct_answer;
        if (question_type === 'multiple_choice' && /^\d+$/.test(rawCorrect)) {
            const alphabets = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            correct_answer = alphabets[parseInt(rawCorrect)] || rawCorrect;
        } else {
            correct_answer = rawCorrect;
        }
        
        let opts = [];
        try {
            opts = typeof $event.detail.options === 'string' 
                ? JSON.parse($event.detail.options || '[]') 
                : ($event.detail.options || []);
        } catch (e) {
            opts = [];
        }
        
        if (opts && Array.isArray(opts) && opts.length > 0) {
            let first = opts[0];
            if (typeof first === 'string') {
                const alphabets = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                options = opts.map((opt, index) => {
                    return { key: alphabets[index] || String(index + 1), text: opt };
                });
            } else if (first && typeof first === 'object' && 'key' in first) {
                options = opts;
            } else {
                options = opts;
            }
        } else {
            options = [
                {key: 'A', text: ''},
                {key: 'B', text: ''},
                {key: 'C', text: ''},
                {key: 'D', text: ''},
                {key: 'E', text: ''}
            ];
        }
     "
     x-show="open" 
     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/50"
     style="display: none;">
    
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 max-w-lg w-full p-6 space-y-4" @click.away="open = false">
        <div class="flex justify-between items-center pb-2 border-b">
            <h4 class="font-bold text-gray-800 text-lg"><i class="fas fa-edit text-amber-500 mr-2"></i>Edit Soal</h4>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        
        <form :action="action" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Pertanyaan <span class="text-red-500">*</span></label>
                    <textarea name="question" rows="2" required x-model="question" class="w-full border rounded-lg px-3 py-2 text-sm mt-1 math-support"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Tipe Soal (Tidak dapat diubah)</label>
                        <div class="text-sm font-semibold text-gray-800 mt-2 bg-gray-100 px-3 py-2 rounded-lg" x-text="
                            question_type === 'multiple_choice' ? 'Pilihan Ganda' : 
                            (question_type === 'true_false' ? 'Benar/Salah' : 
                            (question_type === 'short_answer' ? 'Jawaban Singkat' : 'Essay'))
                        "></div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Skor</label>
                        <input type="number" name="score" x-model="score" min="0.5" step="0.5" required class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                    </div>
                </div>

                <!-- MC Options -->
                <template x-if="question_type === 'multiple_choice'">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Opsi Jawaban</label>
                        <template x-for="(opt, index) in options" :key="index">
                            <div class="flex gap-2 items-center">
                                <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold" x-text="opt.key"></span>
                                <input type="hidden" :name="'options['+index+'][key]'" :value="opt.key">
                                <input type="text" :name="'options['+index+'][text]'" x-model="opt.text" required class="flex-1 border rounded-lg px-3 py-2 text-sm">
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Media Upload Fields for Edit -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Gambar Pendukung (Opsional)</label>
                        <template x-if="has_image">
                            <div class="flex items-center gap-2 mt-1 mb-1.5 p-1.5 bg-gray-50 border border-gray-200 rounded-lg">
                                <span class="text-[10px] text-gray-500 truncate flex-1">Ada gambar terlampir</span>
                                <label class="inline-flex items-center text-[10px] text-red-650 hover:text-red-800 cursor-pointer select-none font-semibold">
                                    <input type="checkbox" name="clear_image" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-1 scale-90">
                                    Hapus
                                </label>
                            </div>
                        </template>
                        <input type="file" name="image" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer mt-1">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Video Link / YouTube URL (Opsional)</label>
                        <input type="url" name="video_url" x-model="video_url" placeholder="https://www.youtube.com/watch?v=..." class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                    </div>
                </div>

                <!-- Correct Answer -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Kunci Jawaban</label>
                    <div x-show="question_type === 'multiple_choice'">
                        <select name="correct_answer" x-model="correct_answer" :disabled="question_type !== 'multiple_choice'" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                            <option value="" disabled selected>-- Pilih Kunci Jawaban --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                    <div x-show="question_type === 'true_false'">
                        <select name="correct_answer" x-model="correct_answer" :disabled="question_type !== 'true_false'" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                            <option value="true">Benar</option>
                            <option value="false">Salah</option>
                        </select>
                    </div>
                    <div x-show="question_type === 'short_answer'">
                        <input type="text" name="correct_answer" x-model="correct_answer" :disabled="question_type !== 'short_answer'" placeholder="Jawaban yang benar" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                    </div>
                    <div x-show="question_type === 'essay'">
                        <p class="text-xs text-gray-400 mt-1">Essay dinilai manual oleh guru</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t">
                    <button type="button" @click="open = false" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200">Batal</button>
                    <button type="submit" class="bg-amber-500 text-white px-5 py-2 rounded-lg hover:bg-amber-600 transition text-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection


