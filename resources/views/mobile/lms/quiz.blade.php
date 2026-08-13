@extends('mobile.layouts.app')

@section('title', 'Pengerjaan Kuis - PembdaHUB Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Clay Card -->
    <div class="clay-purple p-5 space-y-2">
        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-white/30 text-white border border-white/40">
            {{ $quiz->course->course_name ?? 'Kuis LMS' }}
        </span>
        <h1 class="text-base font-black text-white leading-snug">{{ $quiz->title }}</h1>
        <div class="flex items-center justify-between text-xs text-purple-100 font-bold pt-1 border-t border-white/20">
            <span><i class="fa-regular fa-clock mr-1"></i>Durasi: {{ $quiz->time_limit ?? 30 }} menit</span>
            <span><i class="fa-solid fa-list-check mr-1"></i>{{ count($questions) }} Soal</span>
        </div>
    </div>

    <!-- Quiz Question & Answer Form -->
    <form action="{{ route('mobile.lms.quiz.submit', $attempt->id) }}" method="POST" class="space-y-3.5" id="quizForm">
        @csrf

        @forelse($questions as $idx => $q)
            @php $prevAns = $answerMap[$q->id]->answer ?? ''; @endphp
            <div class="clay-card p-5 space-y-3" x-data="{ selectedAnswer: '{{ $prevAns }}' }">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <span class="px-2.5 py-1 rounded-xl clay-purple text-white text-xs font-black">
                        Soal #{{ $idx + 1 }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-400">Poin: {{ $q->score ?? 10 }}</span>
                </div>

                <!-- Question Text -->
                <div class="text-xs font-black text-slate-900 leading-relaxed space-y-2">
                    {!! $q->question_text ?? $q->question !!}
                </div>

                <!-- OPTIONS FOR MULTIPLE CHOICE / TRUE-FALSE -->
                @if(in_array($q->question_type, ['multiple_choice', 'true_false']) && !empty($q->options))
                    <div class="space-y-2 pt-1">
                        @foreach($q->options as $optIdx => $opt)
                            @php 
                                $optVal = is_array($opt) ? ($opt['key'] ?? $optIdx) : (is_numeric($optIdx) ? (chr(65 + $optIdx)) : $optIdx);
                                $optLabel = is_array($opt) ? ($opt['text'] ?? '') : $opt;
                            @endphp
                            <label @click="selectedAnswer = '{{ $optVal }}'"
                                   :class="selectedAnswer === '{{ $optVal }}' ? 'clay-purple text-white font-black scale-[1.02]' : 'bg-[#f4f7fc] text-slate-800 border-2 border-slate-200/80 font-bold'"
                                   class="p-3.5 rounded-2xl flex items-center space-x-3 cursor-pointer transition">
                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $optVal }}" :checked="selectedAnswer === '{{ $optVal }}'" class="hidden">
                                <div class="w-6 h-6 rounded-xl flex items-center justify-center text-xs font-black border border-current shadow-sm">
                                    {{ is_numeric($optIdx) ? chr(65 + $optIdx) : strtoupper($optIdx) }}
                                </div>
                                <span class="text-xs flex-1 leading-snug">{{ $optLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <!-- SHORT ANSWER / TEXT INPUT -->
                    <div class="pt-1">
                        <label class="block text-[11px] font-black text-slate-700 mb-1">Jawaban Anda:</label>
                        <textarea name="answers[{{ $q->id }}]" rows="3" placeholder="Ketik jawaban Anda di sini..."
                                  class="w-full p-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-xs font-bold text-slate-900 outline-none focus:border-purple-500 transition resize-none">{{ $prevAns }}</textarea>
                    </div>
                @endif
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Tidak ada soal dalam kuis ini.
            </div>
        @endforelse

        <!-- Submit Button -->
        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan kuis ini?')" class="clay-btn w-full py-4 text-white font-black text-xs uppercase tracking-wider shadow-lg">
            <i class="fa-solid fa-paper-plane mr-1.5"></i> Selesaikan & Kirim Kuis
        </button>
    </form>
</div>
@endsection
