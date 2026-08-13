@extends('mobile.layouts.app')

@section('title', $course->course_name . ' - LMS Mobile')

@section('content')
<div class="space-y-4" x-data="{ tab: 'modul' }">
    <!-- Back Link -->
    <a href="{{ route('mobile.lms.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke LMS
    </a>

    <!-- Course Banner Card -->
    <div class="glass-card rounded-3xl p-5 relative overflow-hidden bg-gradient-to-br from-purple-950/80 via-slate-900 to-slate-950 border border-purple-500/30">
        <div class="flex items-center space-x-3.5">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white font-extrabold text-2xl shadow-lg border border-purple-400/30">
                <i class="fa-solid fa-book-bookmark"></i>
            </div>
            <div>
                <span class="px-2 py-0.5 rounded-md bg-purple-500/20 text-purple-300 text-[9px] font-bold border border-purple-500/30 uppercase">
                    {{ $course->code }}
                </span>
                <h2 class="text-base font-extrabold text-white mt-1 leading-snug">{{ $course->course_name }}</h2>
                <p class="text-xs text-slate-400 mt-0.5"><i class="fa-regular fa-user mr-1"></i>{{ $course->teacher->full_name ?? 'Pengajar' }}</p>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-800 pb-2">
        <button @click="tab = 'modul'" 
                :class="tab === 'modul' ? 'bg-purple-600 text-white shadow-md' : 'bg-slate-900 text-slate-400 border border-slate-800'"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition text-center">
            📚 Modul & Materi
        </button>
        <button @click="tab = 'tugas'" 
                :class="tab === 'tugas' ? 'bg-purple-600 text-white shadow-md' : 'bg-slate-900 text-slate-400 border border-slate-800'"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition text-center">
            📝 Tugas ({{ count($course->assignments ?? []) }})
        </button>
        <button @click="tab = 'kuis'" 
                :class="tab === 'kuis' ? 'bg-purple-600 text-white shadow-md' : 'bg-slate-900 text-slate-400 border border-slate-800'"
                class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition text-center">
            ❓ Kuis ({{ count($course->quizzes ?? []) }})
        </button>
    </div>

    <!-- Tab 1: Modules & Materials -->
    <div x-show="tab === 'modul'" class="space-y-3">
        @forelse($course->modules as $module)
            <div class="glass-card rounded-2xl p-4 space-y-3">
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-xs">
                        {{ $loop->iteration }}
                    </div>
                    <h3 class="text-sm font-extrabold text-white">{{ $module->name ?? $module->title }}</h3>
                </div>

                @if(isset($module->materials) && count($module->materials) > 0)
                    <div class="space-y-2 pl-2 border-l-2 border-purple-500/30">
                        @foreach($module->materials as $mat)
                            <div class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5 min-w-0">
                                    <i class="fa-solid fa-file-lines text-purple-400 text-sm"></i>
                                    <span class="text-xs font-medium text-slate-200 truncate">{{ $mat->title }}</span>
                                </div>
                                <span class="text-[10px] text-slate-500 uppercase">{{ $mat->type }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 italic pl-2">Belum ada materi di modul ini.</p>
                @endif
            </div>
        @empty
            <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                Belum ada modul pembelajaran yang diunggah.
            </div>
        @endforelse
    </div>

    <!-- Tab 2: Assignments -->
    <div x-show="tab === 'tugas'" class="space-y-3">
        @forelse($course->assignments as $assignment)
            <div class="glass-card rounded-2xl p-4 space-y-2">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-sm font-extrabold text-white">{{ $assignment->title }}</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Batas waktu: {{ $assignment->due_date ?? '-' }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">Tugas</span>
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                Tidak ada tugas aktif saat ini.
            </div>
        @endforelse
    </div>

    <!-- Tab 3: Quizzes -->
    <div x-show="tab === 'kuis'" class="space-y-3">
        @forelse($course->quizzes as $quiz)
            <div class="glass-card rounded-2xl p-4 space-y-2">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-sm font-extrabold text-white">{{ $quiz->title }}</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Durasi: {{ $quiz->time_limit ?? 30 }} menit</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Kuis</span>
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                Tidak ada kuis aktif saat ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
