@extends('layouts.guru')

@section('title', 'Detail Pengerjaan - ' . ($attempt->student->full_name ?? 'Siswa'))

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.lms.quizzes.results', $quiz->id) }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-emerald-600 transition shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-0.5">
                <span>{{ $course->name }}</span>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span>{{ $quiz->title }}</span>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span>Pengerjaan</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Detail Pengerjaan: {{ $attempt->student->full_name ?? $attempt->student->user->name }}</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Attempt details list --}}
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('guru.lms.quizzes.attempts.grade', $attempt->id) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    @foreach($quiz->questions as $i => $q)
                    @php
                        $ans = $answerMap->get($q->id);
                        $studentAns = $ans->answer ?? null;
                        $ansScore = $ans->score ?? 0;
                        $isCorrect = $ans ? $ans->is_correct : null;
                        $isPending = $ans === null || $ans->is_correct === null;
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-full text-xs font-bold">Soal {{ $i + 1 }}</span>
                                <span class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', $q->question_type) }} · Max {{ $q->score }} poin</span>
                            </div>
                            
                            {{-- Status Badge --}}
                            @if($ans === null)
                                <span class="text-xs bg-gray-150 text-gray-600 px-2 py-0.5 rounded-lg">Tidak Dijawab</span>
                            @elseif($q->isAutoGradable() && $q->correct_answer !== null && $q->correct_answer !== '')
                                @if($isCorrect)
                                    <span class="text-xs bg-green-50 text-green-700 border border-green-100 px-2 py-0.5 rounded-lg"><i class="fas fa-check mr-1"></i> Benar (Auto)</span>
                                @else
                                    <span class="text-xs bg-red-50 text-red-700 border border-red-100 px-2 py-0.5 rounded-lg"><i class="fas fa-times mr-1"></i> Salah (Auto)</span>
                                @endif
                            @else
                                @if($isPending)
                                    <span class="text-xs bg-amber-50 text-amber-700 border border-amber-100 px-2 py-0.5 rounded-lg"><i class="fas fa-clock mr-1"></i> Butuh Penilaian</span>
                                @elseif($isCorrect)
                                    <span class="text-xs bg-green-50 text-green-700 border border-green-100 px-2 py-0.5 rounded-lg"><i class="fas fa-check mr-1"></i> Benar</span>
                                @else
                                    <span class="text-xs bg-red-50 text-red-700 border border-red-100 px-2 py-0.5 rounded-lg"><i class="fas fa-times mr-1"></i> Salah</span>
                                @endif
                            @endif
                        </div>

                        <p class="text-gray-800 font-semibold text-sm">{{ $q->question }}</p>

                        {{-- Question Options (if MC) --}}
                        @if($q->question_type === 'multiple_choice' && $q->options)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2 ml-2">
                            @foreach($q->options as $optIdx => $opt)
                            @php
                                $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                                $isAssocOpt = is_array($opt) && isset($opt['key']);
                                $optKey = $isAssocOpt ? $opt['key'] : (string)$optIdx;
                                $optLabel = $isAssocOpt ? $opt['key'] : ($alphabet[$optIdx] ?? $optIdx + 1);
                                $optText = $isAssocOpt ? $opt['text'] : $opt;
                                $isStudentOpt = (string)$studentAns === (string)$optKey;
                                $isCorrectOpt = (string)$q->correct_answer === (string)$optKey;
                                $optClass = 'border-gray-200 text-gray-600 bg-white';
                                if ($isStudentOpt) {
                                    $optClass = $isCorrectOpt ? 'border-green-500 bg-green-50/50 text-green-800 font-bold' : 'border-red-500 bg-red-50/50 text-red-800 font-bold';
                                } elseif ($isCorrectOpt) {
                                    $optClass = 'border-green-300 bg-green-50/20 text-green-700';
                                }
                            @endphp
                            <div class="flex items-center gap-2 p-2 border rounded-lg text-xs {{ $optClass }}">
                                <span class="w-5 h-5 rounded-full border flex items-center justify-center font-bold text-[10px] bg-gray-50">{{ $optLabel }}</span>
                                <span>{{ $optText }}</span>
                                @if($isCorrectOpt) <i class="fas fa-check text-green-500 ml-auto"></i> @endif
                                @if($isStudentOpt && !$isCorrectOpt) <i class="fas fa-times text-red-500 ml-auto"></i> @endif
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Answer Info --}}
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg text-xs space-y-1">
                            @if($q->question_type !== 'multiple_choice')
                                <p class="text-gray-500">Kunci Jawaban: <strong class="text-green-700">{{ $q->correct_answer ?? '-' }}</strong></p>
                            @endif
                            <p class="text-gray-700">Jawaban Siswa: 
                                @if($studentAns !== null)
                                    @php
                                        // Resolve display text for index-based answers
                                        $displayAns = $studentAns;
                                        if ($q->question_type === 'multiple_choice' && $q->options) {
                                            $firstOpt = $q->options[0] ?? null;
                                            if (!is_array($firstOpt) || !isset($firstOpt['key'])) {
                                                $aIdx = (int)$displayAns;
                                                $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                                                if (isset($q->options[$aIdx])) {
                                                    $displayAns = ($alphabet[$aIdx] ?? ($aIdx+1)) . '. ' . $q->options[$aIdx];
                                                }
                                            } else {
                                                foreach ($q->options as $o) {
                                                    if (isset($o['key']) && $o['key'] === $displayAns) {
                                                        $displayAns = $o['key'] . '. ' . $o['text'];
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    <strong class="text-gray-900 bg-white border px-2 py-0.5 rounded shadow-sm inline-block mt-0.5">{{ $displayAns }}</strong>
                                @else
                                    <span class="text-gray-400 italic">Tidak menjawab</span>
                                @endif
                            </p>
                        </div>

                        {{-- Grading Fields --}}
                        <div class="mt-3 pt-3 border-t flex flex-wrap items-center justify-between gap-3 bg-gray-50/30 p-3 rounded-lg">
                            <div class="flex items-center gap-3">
                                <label class="text-xs text-gray-500 font-bold">Status Penilaian:</label>
                                <div class="flex gap-2">
                                    <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                                        <input type="radio" name="grades[{{ $q->id }}][is_correct]" value="1" 
                                               {{ $isCorrect ? 'checked' : '' }}
                                               class="text-green-600 focus:ring-green-500">
                                        <span class="text-green-700 font-medium">Benar</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                                        <input type="radio" name="grades[{{ $q->id }}][is_correct]" value="0" 
                                               {{ !$isCorrect ? 'checked' : '' }}
                                               class="text-red-600 focus:ring-red-500">
                                        <span class="text-red-700 font-medium">Salah</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-gray-500 font-bold">Skor Diperoleh:</label>
                                <input type="number" name="grades[{{ $q->id }}][score]" 
                                       value="{{ old("grades.{$q->id}.score", $ansScore) }}" 
                                       min="0" max="{{ $q->score }}" step="0.5" required
                                       class="w-20 border rounded-lg px-2 py-1 text-xs text-center font-bold focus:ring-indigo-500">
                                <span class="text-xs text-gray-400">/ {{ $q->score }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 transition shadow-md font-bold text-sm">
                        <i class="fas fa-save mr-1.5"></i> Simpan & Perbarui Nilai
                    </button>
                    <a href="{{ route('guru.lms.quizzes.results', $quiz->id) }}" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-xl hover:bg-gray-200 transition text-sm">Batal</a>
                </div>
            </form>
        </div>

        {{-- Right: Student summary & Stats --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-800 text-sm tracking-wide flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-500"></i> Ringkasan Pengerjaan
                </h3>
                
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-500">Nama Siswa</span>
                        <strong class="text-gray-800">{{ $attempt->student->full_name ?? $attempt->student->user->name }}</strong>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-500">NISN</span>
                        <strong class="text-gray-800">{{ $attempt->student->nisn ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-500">Mulai Pengerjaan</span>
                        <strong class="text-gray-800">{{ $attempt->started_at ? $attempt->started_at->format('d M Y H:i') : '-' }}</strong>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-500">Selesai Pengerjaan</span>
                        <strong class="text-gray-800">{{ $attempt->finished_at ? $attempt->finished_at->format('d M Y H:i') : '-' }}</strong>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-500">Skor Saat Ini</span>
                        <strong class="text-sm font-extrabold {{ $attempt->is_passed ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $attempt->score !== null ? number_format($attempt->score, 1) . '%' : '-' }}
                        </strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        @if($attempt->is_passed === true)
                            <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded">Lulus</span>
                        @elseif($attempt->is_passed === false)
                            <span class="text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded">Gagal</span>
                        @else
                            <span class="text-gray-600 font-bold bg-gray-150 px-2 py-0.5 rounded">Belum Selesai</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
