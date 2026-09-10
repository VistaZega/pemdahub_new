@extends('layouts.siswa')
@section('title', 'Review Jawaban')
@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 via-orange-500 to-rose-600 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex items-center gap-5">
            <a href="{{ route('siswa.cbt.result', $exam) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-100">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Review Jawaban</h1>
                <p class="text-amber-200 mt-1 text-sm">{{ $exam->exam_title }} &bull; {{ $exam->subject->subject_name ?? $exam->subject->name ?? '' }}</p>
            </div>
        </div>
    </div>

    @php
        $pendingReviewCount = $answers->filter(function($a) {
            return in_array($a->question?->question_type, ['essay', 'fill_blank'])
                && !empty(trim($a->text_answer ?? ''))
                && $a->manual_score === null;
        })->count();
    @endphp

    @if($pendingReviewCount > 0)
    <div class="flex items-start gap-3.5 p-5 bg-amber-50 border border-amber-200 rounded-2xl">
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-600">
            <i class="fas fa-hourglass-half text-lg"></i>
        </div>
        <div>
            <h3 class="font-bold text-amber-900 text-sm">Terdapat {{ $pendingReviewCount }} Soal Esai Menunggu Koreksi</h3>
            <p class="text-amber-700 text-xs mt-1">
                Jawaban esai yang telah Anda isi di bawah ini berstatus <strong>Menunggu Koreksi</strong> dan akan dinilai secara manual oleh Guru/Admin.
            </p>
        </div>
    </div>
    @endif

    {{-- Questions Review --}}
    @foreach($questions as $idx => $question)
    @php
        $answer = $answers->get($question->id);
        $isEssayType = in_array($question->question_type, ['essay', 'fill_blank']);
        $hasTextAnswer = $answer && !empty(trim($answer->text_answer ?? ''));
        $isGraded = $answer && $answer->manual_score !== null;

        if ($isEssayType) {
            $isUnanswered = !$hasTextAnswer;
            $isPending = $hasTextAnswer && !$isGraded;
            $isCorrect = $hasTextAnswer && $isGraded && $answer->score_obtained > 0;
            $isWrong = $hasTextAnswer && $isGraded && $answer->score_obtained <= 0;

            $borderColor = $isCorrect ? 'border-l-emerald-500' : ($isWrong ? 'border-l-red-500' : ($isPending ? 'border-l-amber-500' : 'border-l-gray-300'));
            $badgeBg = $isCorrect ? 'bg-gradient-to-br from-emerald-400 to-green-500' : ($isWrong ? 'bg-gradient-to-br from-red-400 to-rose-500' : ($isPending ? 'bg-gradient-to-br from-amber-400 to-orange-500' : 'bg-gray-400'));
            $statusLabel = $isCorrect ? 'Dinilai (' . $answer->score_obtained . ' Poin)' : ($isWrong ? '0 Poin' : ($isPending ? 'Menunggu Koreksi' : 'Tidak Dijawab'));
            $statusBadgeClass = $isCorrect ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($isWrong ? 'bg-red-50 text-red-700 border-red-200' : ($isPending ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-gray-50 text-gray-500 border-gray-200'));
            $statusIcon = $isCorrect ? 'fa-check' : ($isWrong ? 'fa-times' : ($isPending ? 'fa-clock' : 'fa-minus'));
        } else {
            $isCorrect = $answer && (bool)$answer->is_correct;
            $isWrong = $answer && !$answer->is_correct && !empty($answer->selected_option);
            $isUnanswered = !$answer || empty($answer->selected_option);

            $borderColor = $isCorrect ? 'border-l-emerald-500' : ($isWrong ? 'border-l-red-500' : 'border-l-gray-300');
            $badgeBg = $isCorrect ? 'bg-gradient-to-br from-emerald-400 to-green-500' : ($isWrong ? 'bg-gradient-to-br from-red-400 to-rose-500' : 'bg-gray-400');
            $statusLabel = $isCorrect ? 'Benar' : ($isWrong ? 'Salah' : 'Tidak Dijawab');
            $statusBadgeClass = $isCorrect ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($isWrong ? 'bg-red-50 text-red-700 border-red-200' : 'bg-gray-50 text-gray-500 border-gray-200');
            $statusIcon = $isCorrect ? 'fa-check' : ($isWrong ? 'fa-times' : 'fa-minus');
        }
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 border-l-4 {{ $borderColor }} overflow-hidden">
        {{-- Question Header --}}
        <div class="p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-sm {{ $badgeBg }}">{{ $idx + 1 }}</div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">{{ strtoupper(str_replace('_', ' ', $question->question_type)) }}</span>
                    <span class="text-xs text-gray-400">{{ $question->points }} poin</span>
                </div>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-bold border flex items-center gap-1.5 {{ $statusBadgeClass }}">
                <i class="fas {{ $statusIcon }} text-xs"></i>{{ $statusLabel }}
            </span>
        </div>

        {{-- Question Body --}}
        <div class="px-5 pb-5 space-y-4">
            <div class="prose prose-sm max-w-none text-gray-800">{!! $question->question_text !!}</div>

            {{-- MC/TF Options --}}
            @if(in_array($question->question_type, ['multiple_choice', 'true_false']))
            <div class="space-y-2">
                @foreach($question->options as $option)
                @php
                    $isSelected = $answer && $answer->selected_option === $option->option_label;
                    $isKey = $option->is_correct;
                    $optBg = ($isKey && $showAnswerKey) ? 'bg-emerald-50 border-emerald-200' : ($isSelected && !$isKey ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-100');
                @endphp
                <div class="flex items-start gap-3 p-3 rounded-xl border {{ $optBg }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 font-bold text-sm {{ $isKey && $showAnswerKey ? 'bg-emerald-500 text-white' : ($isSelected ? ($isKey ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white') : 'bg-white text-gray-600 border border-gray-200') }}">{{ $option->option_label }}</div>
                    <span class="text-gray-800 pt-1 flex-1 text-sm">{{ $option->option_text }}</span>
                    @if($isSelected)
                    <span class="text-xs flex items-center gap-1 {{ $isKey ? 'text-emerald-600' : 'text-red-600' }}"><i class="fas fa-{{ $isKey ? 'check' : 'times' }}"></i>Jawaban Anda</span>
                    @elseif($isKey && $showAnswerKey)
                    <span class="text-xs text-emerald-600 flex items-center gap-1"><i class="fas fa-key"></i>Kunci</span>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- Essay/Fill Blank --}}
            @if(in_array($question->question_type, ['essay', 'fill_blank']))
            <div class="space-y-3">
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex items-center gap-2 mb-1.5"><i class="fas fa-pen text-blue-500 text-xs"></i><span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Jawaban Anda</span></div>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $answer->text_answer ?? '(Tidak dijawab)' }}</p>
                </div>
                @if($showAnswerKey && $question->answer_key)
                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    <div class="flex items-center gap-2 mb-1.5"><i class="fas fa-key text-emerald-500 text-xs"></i><span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Kunci Jawaban</span></div>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $question->answer_key }}</p>
                </div>
                @endif
                @if($answer && $answer->manual_score !== null)
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100 text-sm">
                    <span class="text-gray-500">Nilai manual:</span>
                    <span class="font-bold text-gray-900">{{ $answer->manual_score }}/{{ $question->points }}</span>
                    @if($answer->teacher_feedback)
                    <span class="text-gray-500">— {{ $answer->teacher_feedback }}</span>
                    @endif
                </div>
                @endif
            </div>
            @endif

            {{-- Explanation --}}
            @if($showAnswerKey && $question->explanation)
            <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                <div class="flex items-center gap-2 mb-1.5"><i class="fas fa-lightbulb text-amber-500 text-xs"></i><span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Pembahasan</span></div>
                <p class="text-sm text-gray-800">{{ $question->explanation }}</p>
            </div>
            @endif
        </div>
    </div>
    @endforeach

    <div class="text-center py-4">
        <a href="{{ route('siswa.cbt.result', $exam) }}" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl hover:shadow-lg transition font-bold text-sm gap-2">
            <i class="fas fa-arrow-left"></i>Kembali ke Hasil
        </a>
    </div>
</div>
@endsection
