@extends('layouts.guru')
@section('title', 'Koreksi Esai: ' . $exam->exam_title)
@section('content')
<div class="space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex items-center gap-5">
            <a href="{{ route('guru.cbt.exams.results', $exam) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Koreksi Esai</h1>
                <p class="text-emerald-50 mt-1 text-base">{{ $exam->exam_title }} &bull; {{ $answers->count() }} jawaban</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600 text-base"></i></div>
        <p class="text-emerald-700 text-base font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if($answers->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-16 text-center">
        <i class="fas fa-check-circle text-5xl text-emerald-300 mb-4"></i>
        <h3 class="text-xl font-bold text-gray-700 mb-2">Tidak Ada Esai</h3>
        <p class="text-gray-700">Tidak ada jawaban esai yang perlu dikoreksi.</p>
    </div>
    @else
    {{-- Essay Answer List --}}
    <div class="space-y-6">
        @foreach($answers as $idx => $answer)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ open: {{ $answer->manual_score === null ? 'true' : 'false' }} }">
            {{-- Header --}}
            <button @click="open = !open" class="w-full p-5 flex items-center justify-between text-left hover:bg-gray-50 transition">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $answer->manual_score !== null ? 'from-emerald-400 to-green-500' : 'from-amber-400 to-orange-500' }} flex items-center justify-center text-white font-bold text-base">
                        {{ $idx + 1 }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900">{{ $answer->session->student->full_name ?? '-' }}</span>
                            @if($answer->manual_score !== null)
                            <span class="px-2 py-0.5 rounded-lg text-base font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Sudah Dinilai: {{ $answer->manual_score }}</span>
                            @else
                            <span class="px-2 py-0.5 rounded-lg text-base font-bold bg-amber-100 text-amber-800 border border-amber-200">Belum Dinilai</span>
                            @endif
                        </div>
                        <p class="text-base text-gray-700 mt-0.5">Soal: {{ Str::limit(strip_tags($answer->question->question_text ?? ''), 80) }}</p>
                    </div>
                </div>
                <i class="fas fa-chevron-down text-gray-800 transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>

            {{-- Body --}}
            <div x-show="open" x-transition class="border-t border-gray-200 p-6 space-y-5">
                {{-- Question --}}
                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-question-circle text-indigo-500"></i>
                        <span class="text-base font-bold text-indigo-600 uppercase tracking-wider">Pertanyaan</span>
                    </div>
                    <div class="text-base text-gray-800 prose prose-sm max-w-none">{!! $answer->question->question_text ?? '-' !!}</div>
                </div>

                {{-- Student Answer --}}
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-pen text-blue-500"></i>
                        <span class="text-base font-bold text-blue-600 uppercase tracking-wider">Jawaban Siswa</span>
                    </div>
                    <div class="text-base text-gray-800 whitespace-pre-wrap">{{ $answer->text_answer ?? '-' }}</div>
                </div>

                {{-- Answer Key --}}
                @if($answer->question->answer_key)
                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-key text-emerald-500"></i>
                        <span class="text-base font-bold text-emerald-600 uppercase tracking-wider">Kunci Jawaban</span>
                    </div>
                    <div class="text-base text-gray-800 whitespace-pre-wrap">{{ $answer->question->answer_key }}</div>
                </div>
                @endif

                @php
                    $maxScore = isset($examQuestions) ? ($examQuestions->get($answer->question_id)?->getEffectivePoints() ?? $answer->question->points ?? 10) : ($answer->question->points ?? 10);
                @endphp

                {{-- Grading Form --}}
                <form action="{{ route('guru.cbt.answers.grade', ['answer' => $answer]) }}" method="POST" class="p-5 bg-gray-50 rounded-xl border border-gray-200">
                    @csrf
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-star text-amber-500"></i>
                        <span class="text-base font-bold text-gray-900">Penilaian</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Skor (maks. {{ $maxScore }})</label>
                            <input type="number" name="manual_score" min="0" max="{{ $maxScore }}" step="0.5" value="{{ $answer->manual_score }}"
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-lg font-bold" required>
                        </div>
                        <div>
                            <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Feedback (opsional)</label>
                            <textarea name="teacher_feedback" rows="2" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base" placeholder="Catatan untuk siswa...">{{ $answer->teacher_feedback }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-green-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base flex items-center gap-2">
                            <i class="fas fa-save"></i>Simpan Nilai
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
