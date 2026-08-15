@extends('mobile.layouts.app')

@section('title', 'CBT Ujian & Monitoring Live - Guru Mobile')

@section('content')
<div class="space-y-4" x-data="{ activeTab: 'exams', showAddExam: false }">
    <!-- Header Title & Action -->
    <div class="flex items-center justify-between gap-2 px-1 pt-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-base font-black text-slate-900 leading-tight truncate">CBT Ujian & Live</h2>
                <p class="text-[10px] text-slate-500 font-bold truncate">Kelola & Pantau Ujian CBT</p>
            </div>
        </div>
        <button @click="showAddExam = !showAddExam" 
                class="clay-btn px-3 py-2 text-white text-xs font-black flex items-center gap-1.5 shadow-md shrink-0">
            <i class="fa-solid fa-plus text-xs"></i> <span x-text="showAddExam ? 'Batal' : '+ Ujian Baru'"></span>
        </button>
    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-200 text-emerald-800 text-xs font-black flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Form Buat Ujian CBT Baru (Inline Collapsible) -->
    <div x-show="showAddExam" x-transition class="clay-card p-5 space-y-3 bg-purple-50 border-2 border-purple-300">
        <h3 class="text-xs font-black text-purple-900 flex items-center gap-1.5 uppercase tracking-wider">
            <i class="fa-solid fa-laptop-code text-purple-600"></i> Buat Jadwal Ujian CBT Baru
        </h3>

        @if(count($banks) === 0)
            <div class="p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-xs font-bold space-y-1">
                <p>⚠️ <strong>Belum ada Bank Soal.</strong></p>
                <p class="text-[11px]">Bapak/Ibu perlu memiliki setidaknya 1 Bank Soal untuk membuat Jadwal Ujian CBT.</p>
            </div>
        @else
            <form action="{{ route('mobile.guru.cbt.exam.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Judul / Nama Ujian CBT *</label>
                    <input type="text" name="exam_title" required placeholder="Contoh: Ujian Tengah Semester (UTS) Matematika X" 
                           class="w-full p-3 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none focus:border-purple-600">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Pilih Bank Soal *</label>
                        <select name="question_bank_id" required class="w-full p-3 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none focus:border-purple-600">
                            @foreach($banks as $b)
                                <option value="{{ $b->id }}">{{ $b->title ?? $b->name }} ({{ $b->questions_count ?? 0 }} Soal)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Tipe Ujian CBT</label>
                        <select name="exam_type" class="w-full p-3 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none focus:border-purple-600">
                            <option value="quiz">Kuis / Quiz</option>
                            <option value="tugas">Tugas Harian</option>
                            <option value="uts">UTS (Ujian Tengah Semester)</option>
                            <option value="uas">UAS (Ujian Akhir Semester)</option>
                            <option value="remedial">Remedial</option>
                            <option value="tryout">Try Out</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Durasi (Menit)</label>
                        <input type="number" name="duration_minutes" value="60" required min="1" max="300"
                               class="w-full p-3 bg-white border-2 border-purple-200 rounded-xl text-xs font-black text-slate-900 outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">KKM / Lulus</label>
                        <input type="number" name="passing_score" value="70" required min="0" max="100"
                               class="w-full p-3 bg-white border-2 border-purple-200 rounded-xl text-xs font-black text-slate-900 outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Token Akses</label>
                        <input type="text" name="access_code" value="{{ strtoupper(Str::random(6)) }}" required
                               class="w-full p-3 bg-white border-2 border-purple-200 rounded-xl text-xs font-black text-purple-700 uppercase tracking-widest outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required
                               class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Jam Mulai</label>
                        <input type="time" name="start_time_only" value="08:00" required
                               class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>
                </div>

                <button type="submit" class="clay-btn w-full py-3.5 text-white font-black text-xs uppercase tracking-wider shadow-lg">
                    🚀 Terbitkan & Aktifkan Ujian CBT
                </button>
            </form>
        @endif
    </div>

    <!-- Category Tabs -->
    <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-200/70 rounded-2xl border-2 border-slate-300">
        <button @click="activeTab = 'exams'"
                :class="activeTab === 'exams' ? 'bg-purple-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-3 text-xs transition flex items-center justify-center gap-1.5">
            📊 Monitoring Ujian ({{ count($exams) }})
        </button>
        <button @click="activeTab = 'banks'"
                :class="activeTab === 'banks' ? 'bg-amber-400 text-slate-900 border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-3 text-xs transition flex items-center justify-center gap-1.5">
            📚 Bank Soal ({{ count($banks) }})
        </button>
    </div>

    <!-- TAB 1: JADWAL & MONITORING UJIAN CBT -->
    <div x-show="activeTab === 'exams'" x-transition class="space-y-3">
        @forelse($exams as $ex)
            @php
                $isCompleted = $ex->status === 'completed';
                $isPublished = in_array($ex->status, ['published', 'active']);
            @endphp
            <div class="clay-card p-5 space-y-3 bg-white border-2 border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border
                            {{ $isCompleted ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300' }}">
                            {{ $isCompleted ? '🏁 Selesai' : '🟢 Active / Berlangsung' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-900 text-[9px] font-black uppercase">
                            {{ $ex->exam_type ?? 'CBT' }}
                        </span>
                    </div>

                    <span class="text-[10px] font-black text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">
                        🔑 Token: {{ $ex->access_code ?? '-' }}
                    </span>
                </div>

                <div>
                    <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $ex->exam_title }}</h3>
                    <div class="flex items-center gap-3 text-[10px] font-bold text-slate-500 mt-1">
                        <span>⏱️ {{ $ex->duration_minutes }} Menit</span>
                        <span>🎯 KKM: {{ $ex->passing_score }}</span>
                        <span>👥 Terkumpul: {{ $ex->results_count ?? count($ex->results ?? []) }} Siswa</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2">
                    <a href="{{ route('mobile.guru.cbt.exam.monitor', $ex->id) }}" 
                       class="py-2.5 px-3 rounded-xl bg-purple-600 text-white text-[11px] font-black hover:bg-purple-700 transition flex items-center justify-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-chart-line text-xs"></i> Live Monitoring & Hasil
                    </a>

                    <form action="{{ route('mobile.guru.cbt.exam.toggle-status', $ex->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full py-2.5 px-3 rounded-xl text-[11px] font-black transition flex items-center justify-center gap-1.5 border
                                {{ $isCompleted ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}">
                            <i class="fa-solid {{ $isCompleted ? 'fa-play' : 'fa-stop' }} text-xs"></i>
                            {{ $isCompleted ? 'Aktifkan Kembali' : 'Selesaikan Ujian' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">📊</div>
                <p>Belum ada Jadwal Ujian CBT yang dibuat.</p>
                <button @click="showAddExam = true" class="mt-2 text-purple-600 font-black underline">
                    + Buat Ujian CBT Sekarang
                </button>
            </div>
        @endforelse
    </div>

    <!-- TAB 2: BANK SOAL SAYA -->
    <div x-show="activeTab === 'banks'" x-transition class="space-y-3">
        @forelse($banks as $bank)
            <div class="clay-card p-5 space-y-3 bg-white border-2 border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-11 h-11 rounded-2xl clay-pink flex items-center justify-center text-white text-xl font-black shadow-md">
                            💻
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $bank->title ?? $bank->name ?? 'Bank Soal' }}</h3>
                            <span class="text-[10px] font-bold text-purple-600 block">
                                {{ $bank->questions_count ?? 0 }} Butir Soal Terdaftar
                            </span>
                        </div>
                    </div>

                    <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[9px] font-black uppercase border border-purple-200">
                        {{ $bank->subject->name ?? $bank->code ?? 'CBT' }}
                    </span>
                </div>

                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[10px] font-bold text-slate-500">
                    <span>Dibuat: {{ \Carbon\Carbon::parse($bank->created_at)->translatedFormat('d M Y') }}</span>
                    <a href="{{ route('mobile.guru.cbt.bank.show', $bank->id) }}" 
                       class="px-3 py-1.5 bg-amber-400 text-slate-900 font-black rounded-xl border border-black hover:bg-amber-300 transition flex items-center gap-1 shadow-xs">
                        <i class="fa-solid fa-eye"></i> Detail & Tautkan LMS
                    </a>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">💻</div>
                <p>Belum ada Bank Soal CBT yang dibuat.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
