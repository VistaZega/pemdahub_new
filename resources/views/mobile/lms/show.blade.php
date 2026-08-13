@extends('mobile.layouts.app')

@section('title', $course->course_name . ' - LMS 3D Mobile')

@section('content')
<div class="space-y-4" x-data="{ tab: 'modul' }">
    <!-- Back Link -->
    <a href="{{ route('mobile.lms.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke LMS
    </a>

    <!-- Course Banner Clay Card -->
    <div class="clay-purple p-6 relative overflow-hidden">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-white font-black text-2xl border-2 border-white shadow-md">
                <i class="fa-solid fa-book-bookmark"></i>
            </div>
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-white/30 text-white text-[9px] font-black border border-white/40 uppercase">
                    {{ $course->code }}
                </span>
                <h2 class="text-base font-black text-white mt-1 leading-snug tracking-tight">{{ $course->course_name }}</h2>
                <p class="text-xs text-purple-100 mt-0.5 font-bold"><i class="fa-regular fa-user mr-1"></i>{{ $course->teacher->full_name ?? 'Pengajar' }}</p>
            </div>
        </div>
    </div>

    <!-- Navigation Clay Tabs -->
    <div class="flex items-center space-x-2 border-b-2 border-slate-200/80 pb-2">
        <button @click="tab = 'modul'" 
                :class="tab === 'modul' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="flex-1 py-2.5 px-3 rounded-2xl text-xs transition text-center">
            📚 Modul
        </button>
        <button @click="tab = 'tugas'" 
                :class="tab === 'tugas' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="flex-1 py-2.5 px-3 rounded-2xl text-xs transition text-center">
            📝 Tugas ({{ count($course->assignments ?? []) }})
        </button>
        <button @click="tab = 'kuis'" 
                :class="tab === 'kuis' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="flex-1 py-2.5 px-3 rounded-2xl text-xs transition text-center">
            ❓ Kuis ({{ count($course->quizzes ?? []) }})
        </button>
    </div>

    <!-- Tab 1: Modules & Materials -->
    <div x-show="tab === 'modul'" class="space-y-3">
        @forelse($course->modules as $module)
            <div class="clay-card p-4.5 space-y-3">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl clay-purple flex items-center justify-center font-black text-xs">
                        {{ $loop->iteration }}
                    </div>
                    <h3 class="text-sm font-black text-slate-900">{{ $module->name ?? $module->title }}</h3>
                </div>

                @if(isset($module->materials) && count($module->materials) > 0)
                    <div class="space-y-2 pl-2 border-l-4 border-purple-300">
                        @foreach($module->materials as $mat)
                            <div class="p-3 rounded-2xl bg-[#f4f7fc] border-2 border-slate-200/80 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5 min-w-0">
                                    <i class="fa-solid fa-file-lines text-purple-600 text-sm"></i>
                                    <span class="text-xs font-bold text-slate-900 truncate">{{ $mat->title }}</span>
                                </div>
                                <span class="text-[10px] font-black text-purple-700 uppercase bg-purple-100 px-2 py-0.5 rounded-md border border-purple-200">{{ $mat->type }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 italic pl-2 font-bold">Belum ada materi di modul ini.</p>
                @endif
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Belum ada modul pembelajaran yang diunggah.
            </div>
        @endforelse
    </div>

    <!-- Tab 2: Assignments -->
    <div x-show="tab === 'tugas'" class="space-y-3">
        @forelse($course->assignments as $assignment)
            <div class="clay-card p-4.5 space-y-2">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-sm font-black text-slate-900">{{ $assignment->title }}</h4>
                        <p class="text-xs text-slate-500 font-bold mt-0.5">Batas waktu: {{ $assignment->due_date ?? '-' }}</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black clay-yellow">Tugas</span>
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Tidak ada tugas aktif saat ini.
            </div>
        @endforelse
    </div>

    <!-- Tab 3: Quizzes -->
    <div x-show="tab === 'kuis'" class="space-y-3">
        @forelse($course->quizzes as $quiz)
            <div class="clay-card p-4.5 space-y-2">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-sm font-black text-slate-900">{{ $quiz->title }}</h4>
                        <p class="text-xs text-slate-500 font-bold mt-0.5">Durasi: {{ $quiz->time_limit ?? 30 }} menit</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black clay-pink">Kuis</span>
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Tidak ada kuis aktif saat ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
