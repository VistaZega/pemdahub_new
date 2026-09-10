@extends('layouts.admin')
@section('title', 'Koreksi Esai - ' . $exam->exam_title)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" onload="setTimeout(() => renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true}, {left: '\\(', right: '\\)', display: false}, {left: '$', right: '$', display: false}]}), 200)"></script>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 rounded-2xl p-8 text-white shadow-xl shadow-purple-900/10">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.cbt.results', $exam) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Koreksi Soal Esai</h1>
                    <p class="text-violet-100 mt-1 text-base">
                        {{ $exam->exam_title }} &bull; {{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.cbt.results', $exam) }}" class="px-5 py-2.5 bg-white text-violet-700 rounded-xl font-semibold hover:bg-violet-50 transition shadow-lg shadow-violet-900/20 text-sm flex items-center gap-2">
                    <i class="fas fa-chart-bar"></i>Lihat Hasil Ujian
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-emerald-600 text-base"></i>
        </div>
        <p class="text-emerald-700 text-base font-medium">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Stats Summary --}}
    @php
        $totalAnswers = $answers->count();
        $unscoredCount = $answers->whereNull('manual_score')->count();
        $scoredCount = $answers->whereNotNull('manual_score')->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-xl font-bold">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900">{{ $totalAnswers }}</div>
                <div class="text-sm text-gray-500 font-medium">Total Jawaban Esai</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 text-xl font-bold">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-amber-600">{{ $unscoredCount }}</div>
                <div class="text-sm text-gray-500 font-medium">Belum Dikoreksi</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 text-xl font-bold">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-emerald-600">{{ $scoredCount }}</div>
                <div class="text-sm text-gray-500 font-medium">Sudah Dinilai</div>
            </div>
        </div>
    </div>

    @if($answers->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4 text-gray-400 text-3xl">
            <i class="fas fa-inbox"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Jawaban Esai</h3>
        <p class="text-gray-500 max-w-md mx-auto">
            Tidak ada jawaban esai yang perlu dikoreksi untuk ujian ini atau siswa belum mengumpulkan ujian.
        </p>
    </div>
    @else
    {{-- Answers List --}}
    <div class="space-y-6">
        @foreach($answers as $idx => $answer)
        @php
            $isGraded = $answer->manual_score !== null;
            $eq = $examQuestions->get($answer->question_id);
            $maxScore = $eq?->getEffectivePoints() ?? $answer->question->points ?? 10;
            $student = $answer->session->student ?? null;
            $classroom = $student?->classroom ?? null;
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ open: {{ $isGraded ? 'false' : 'true' }} }">
            {{-- Accordion Header --}}
            <button @click="open = !open" class="w-full p-5 flex items-center justify-between text-left hover:bg-gray-50/80 transition">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $isGraded ? 'from-emerald-400 to-green-500' : 'from-amber-400 to-orange-500' }} flex items-center justify-center text-white font-bold text-base shadow-sm">
                        {{ $idx + 1 }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-gray-900 text-base">{{ $student->full_name ?? '-' }}</span>
                            @if($classroom)
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                {{ $classroom->class_name }}
                            </span>
                            @endif
                            @if($isGraded)
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                <i class="fas fa-check text-xs"></i>Nilai: {{ $answer->manual_score }}/{{ $maxScore }}
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                <i class="fas fa-clock text-xs"></i>Belum Dinilai
                            </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-1">
                            Soal: {{ Str::limit(strip_tags($answer->question->question_text ?? ''), 90) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400 hidden sm:inline" x-text="open ? 'Tutup' : 'Buka Koreksi'"></span>
                    <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                </div>
            </button>

            {{-- Accordion Body --}}
            <div x-show="open" x-transition class="border-t border-gray-200 p-6 space-y-5 bg-gray-50/40">
                {{-- Question Card --}}
                <div class="p-5 bg-violet-50/60 rounded-xl border border-violet-100">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-question-circle text-violet-600"></i>
                            <span class="text-xs font-bold text-violet-700 uppercase tracking-wider">Pertanyaan Soal (Maks: {{ $maxScore }} Poin)</span>
                        </div>
                    </div>
                    <div class="text-base text-gray-900 prose prose-sm max-w-none">{!! $answer->question->question_text ?? '-' !!}</div>
                </div>

                {{-- Student Answer Card --}}
                <div class="p-5 bg-blue-50/70 rounded-xl border border-blue-100">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-pen text-blue-600"></i>
                        <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Jawaban Siswa</span>
                    </div>
                    <div class="text-base text-gray-800 whitespace-pre-wrap font-medium">
                        {{ !empty(trim($answer->text_answer ?? '')) ? $answer->text_answer : '(Siswa tidak mengisi jawaban teks)' }}
                    </div>
                </div>

                {{-- Answer Key (if available) --}}
                @if($answer->question->answer_key)
                <div class="p-5 bg-emerald-50/70 rounded-xl border border-emerald-100">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-key text-emerald-600"></i>
                        <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Panduan Kunci Jawaban</span>
                    </div>
                    <div class="text-base text-gray-800 whitespace-pre-wrap">{{ $answer->question->answer_key }}</div>
                </div>
                @endif

                {{-- Grading Form --}}
                <form action="{{ route('admin.cbt.answers.grade', ['answer' => $answer]) }}" method="POST" class="p-5 bg-white rounded-xl border border-gray-200 shadow-sm">
                    @csrf
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <i class="fas fa-star text-amber-500"></i>
                        <span class="text-base font-bold text-gray-900">Formulir Penilaian</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Berikan Skor (0 - {{ $maxScore }})
                            </label>
                            <input type="number" name="manual_score" min="0" max="{{ $maxScore }}" step="0.5" 
                                value="{{ old('manual_score', $answer->manual_score ?? '') }}"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-lg font-bold" 
                                placeholder="Contoh: {{ $maxScore }}" required>
                            <p class="text-xs text-gray-400 mt-1">Bobot maksimal untuk soal ini adalah {{ $maxScore }} poin.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Catatan / Feedback (Opsional)
                            </label>
                            <textarea name="teacher_feedback" rows="2" 
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm" 
                                placeholder="Catatan evaluasi untuk siswa...">{{ old('teacher_feedback', $answer->teacher_feedback ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <div class="text-xs text-gray-400">
                            @if($isGraded)
                            <i class="fas fa-info-circle mr-1"></i>Terakhir dinilai pada {{ $answer->graded_at?->format('d/m/Y H:i') ?? '-' }}
                            @endif
                        </div>
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-xl hover:from-violet-700 hover:to-indigo-700 transition font-semibold text-sm flex items-center gap-2 shadow-md shadow-violet-500/20">
                            <i class="fas fa-save"></i>{{ $isGraded ? 'Perbarui Nilai' : 'Simpan Nilai' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
