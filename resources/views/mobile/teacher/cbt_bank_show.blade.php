@extends('mobile.layouts.app')

@section('title', 'Detail Bank Soal CBT - Mobile Pro')

@section('content')
<div class="space-y-4" x-data="{ showLmsModal: false }">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('mobile.guru.cbt') }}" 
           class="w-10 h-10 rounded-2xl bg-white border-2 border-slate-200 flex items-center justify-center text-slate-700 font-black shadow-sm">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Detail Bank Soal CBT</span>
        <div class="w-10"></div>
    </div>

    <!-- Hero Card Bank Soal -->
    <div class="clay-card p-5 bg-gradient-to-br from-purple-700 via-indigo-700 to-purple-900 text-white space-y-3.5 shadow-lg border-2 border-purple-400">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-purple-500/40 text-purple-100 text-[10px] font-black uppercase border border-purple-300/30">
                    {{ $bank->subject->name ?? $bank->subject->subject_name ?? 'CBT' }} &bull; Kelas {{ $bank->grade_level }}
                </span>
                <h2 class="text-base font-black text-white leading-tight mt-1">{{ $bank->bank_name }}</h2>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-white/20 flex items-center justify-center text-xl shrink-0 border border-white/20">
                📦
            </div>
        </div>

        <div class="flex items-center justify-between text-xs font-black pt-2 border-t border-white/10">
            <span class="text-purple-200"><i class="fa-solid fa-list-check mr-1"></i>{{ count($bank->questions ?? []) }} Butir Soal</span>
            <span class="text-amber-300"><i class="fa-solid fa-shield-halved mr-1"></i>{{ $bank->is_shared ? 'Dibagikan (Shared)' : 'Privat' }}</span>
        </div>

        <!-- Action Button: Jadikan Kuis LMS -->
        <button @click="showLmsModal = true" 
                class="w-full py-3 bg-amber-400 text-slate-900 font-black text-xs rounded-2xl border-2 border-black hover:bg-amber-300 transition flex items-center justify-center gap-2 shadow-md">
            <i class="fa-solid fa-rocket text-black text-sm"></i> 🚀 Jadikan Kuis LMS Baru
        </button>
    </div>

    <!-- Questions List Header -->
    <div class="flex items-center justify-between px-1">
        <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-circle-question text-purple-600"></i> Daftar Soal ({{ count($bank->questions ?? []) }})
        </h3>
    </div>

    <!-- Question Cards -->
    <div class="space-y-3">
        @forelse($bank->questions as $idx => $question)
            <div class="clay-card p-4.5 space-y-3 bg-white border-2 border-slate-200">
                <!-- Question Top Header -->
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-xl bg-purple-600 text-white font-black text-xs flex items-center justify-center shadow-xs">
                        {{ $idx + 1 }}
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[9px] font-black uppercase border border-slate-200">
                            {{ strtoupper(str_replace('_', ' ', $question->question_type ?? 'Pilihan Ganda')) }}
                        </span>
                        <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 text-[9px] font-black border border-purple-200">
                            {{ $question->points ?? 10 }} Poin
                        </span>
                    </div>
                </div>

                <!-- Question Text -->
                <div class="text-xs font-bold text-slate-900 leading-relaxed whitespace-pre-line bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                    {!! strip_tags($question->question_text) !== $question->question_text ? $question->question_text : nl2br(e($question->question_text)) !!}
                </div>

                <!-- Options List -->
                @if(isset($question->options) && count($question->options) > 0)
                    <div class="space-y-1.5 pt-1">
                        @foreach($question->options->sortBy('sort_order') as $opt)
                            <div class="p-2.5 rounded-xl text-xs font-bold flex items-start space-x-2 border {{ $opt->is_correct ? 'bg-emerald-50 text-emerald-900 border-emerald-300' : 'bg-white text-slate-700 border-slate-200' }}">
                                <span class="w-5 h-5 rounded-lg shrink-0 flex items-center justify-center text-[10px] font-black {{ $opt->is_correct ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ $opt->option_label }}
                                </span>
                                <span class="flex-1 mt-0.5 leading-tight">
                                    {{ $opt->option_text }}
                                    @if($opt->is_correct)
                                        <i class="fa-solid fa-circle-check text-emerald-600 ml-1"></i>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 font-bold text-xs space-y-2">
                <div class="text-3xl">❓</div>
                <p>Belum ada butir soal di dalam bank soal ini.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal Tautkan ke LMS Kelas Mobile -->
    <div x-show="showLmsModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="clay-card p-5 bg-white space-y-3.5 w-full max-w-sm border-2 border-purple-300 shadow-2xl">
            <div class="flex items-center justify-between border-b-2 border-slate-100 pb-2">
                <h3 class="text-xs font-black text-purple-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-rocket text-purple-600"></i> Tautkan Bank Soal ke Kuis LMS
                </h3>
                <button @click="showLmsModal = false" class="text-slate-400 hover:text-slate-600 font-black text-sm">&times;</button>
            </div>

            <form action="{{ route('guru.cbt.banks.assign-to-lms', $bank->id) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="is_mobile" value="1">
                <div class="p-3 bg-purple-50 rounded-2xl border border-purple-200">
                    <p class="text-[10px] font-black text-purple-700 uppercase">Bank Soal Terpilih:</p>
                    <p class="text-xs font-black text-slate-900 mt-0.5">{{ $bank->bank_name }} ({{ count($bank->questions ?? []) }} Soal)</p>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-700 uppercase">Pilih Kelas / Mata Pelajaran LMS Target</label>
                    <select name="course_id" required class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                        <option value="">-- Pilih Kelas LMS --</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}">{{ $c->course_name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-700 uppercase">Judul Kuis LMS Baru</label>
                    <input type="text" name="title" value="Kuis - {{ $bank->bank_name }}" required 
                           class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-700 uppercase">Durasi Pengerjaan (Menit)</label>
                    <input type="number" name="time_limit" value="30" min="1" required 
                           class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showLmsModal = false" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-black text-xs rounded-xl border border-slate-300">Batal</button>
                    <button type="submit" class="flex-[2] py-2.5 bg-purple-600 text-white font-black text-xs rounded-xl shadow-md hover:bg-purple-700 transition">
                        Tautkan & Buat Kuis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
