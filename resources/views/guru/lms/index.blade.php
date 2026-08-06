@extends('layouts.guru')

@section('title', 'LMS - Portal Guru PembdaHUB')

@push('styles')
<style>
    .course-card { animation: fadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .course-card:nth-child(1) { animation-delay: 0s; }
    .course-card:nth-child(2) { animation-delay: 0.05s; }
    .course-card:nth-child(3) { animation-delay: 0.10s; }
    .course-card:nth-child(4) { animation-delay: 0.15s; }
    .course-card:nth-child(5) { animation-delay: 0.20s; }
    .course-card:nth-child(6) { animation-delay: 0.25s; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO BANNER (UI-UX PRO MAX PREMIUM UI) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 text-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border border-slate-700/60">
        {{-- Background Subtle Glow --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center shadow-inner backdrop-blur-md">
                        <i class="fas fa-chalkboard-teacher text-xl text-emerald-400"></i>
                    </div>
                    <div>
                        <span class="text-emerald-400 text-[11px] font-bold uppercase tracking-widest block">LMS Portal Guru</span>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Selamat Datang, {{ explode(' ', $teacher->user->name ?? 'Guru')[0] }}! 👋</h2>
                    </div>
                </div>
                <p class="text-slate-300 text-sm max-w-lg leading-relaxed mt-1">Kelola modul pembelajaran digital, materi instruksional, penugasan, dan kuis evaluasi kelas Anda.</p>
                
                @if($activeSemester)
                <div class="mt-4 inline-flex items-center gap-2 bg-slate-800/80 border border-slate-700 rounded-xl px-3.5 py-1.5 backdrop-blur-sm">
                    <i class="fas fa-calendar-alt text-amber-400 text-xs"></i>
                    <span class="text-xs font-semibold text-slate-200">{{ $activeSemester->semester_name }}</span>
                </div>
                @endif
            </div>

            {{-- Quick Stats Pills --}}
            <div class="flex items-center gap-3">
                @php
                    $totalMaterials = $courses->sum('materials_count');
                    $totalAssignments = $courses->sum('assignments_count');
                    $totalQuizzes = $courses->sum('quizzes_count');
                @endphp
                <div class="bg-slate-800/60 backdrop-blur-md border border-slate-700/80 rounded-2xl px-5 py-3.5 text-center min-w-[95px] shadow-sm">
                    <div class="text-2xl font-black text-white leading-none">{{ $courses->total() }}</div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1.5">Course</div>
                </div>
                <div class="bg-slate-800/60 backdrop-blur-md border border-slate-700/80 rounded-2xl px-5 py-3.5 text-center min-w-[95px] shadow-sm">
                    <div class="text-2xl font-black text-emerald-400 leading-none">{{ $totalMaterials }}</div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1.5">Materi</div>
                </div>
                <div class="bg-slate-800/60 backdrop-blur-md border border-slate-700/80 rounded-2xl px-5 py-3.5 text-center min-w-[95px] shadow-sm">
                    <div class="text-2xl font-black text-amber-400 leading-none">{{ $totalAssignments + $totalQuizzes }}</div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1.5">Evaluasi</div>
                </div>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="relative z-10 mt-6 pt-5 border-t border-slate-700/60 flex items-center justify-between">
            <a href="{{ route('guru.lms.create') }}" class="inline-flex items-center gap-2.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold px-6 py-3 rounded-xl text-xs tracking-wide transition-all shadow-lg hover:shadow-emerald-500/25 active:scale-95">
                <i class="fas fa-plus-circle text-sm"></i> Buat Course Ajar Baru
            </a>
            <span class="text-slate-400 text-xs font-medium hidden sm:inline-block"><i class="fas fa-lightbulb text-amber-400 mr-1.5"></i>Tips: Susun materi berurutan dalam modul ajar.</span>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════ --}}
    {{-- COURSE GRID --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if($courses->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($courses as $course)
        @php
            $scientist = $course->getScientistConfig();
            $classNames = $course->lmsClasses->pluck('classroom.class_name')->filter()->implode(', ');
        @endphp
        <div class="course-card group">
            <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 h-full flex flex-col hover:-translate-y-1">
                {{-- Card Header --}}
                <div class="relative p-5 bg-slate-900 text-white overflow-hidden border-b border-slate-800">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-11 h-11 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center flex-shrink-0 shadow-sm text-emerald-400">
                                @if($scientist)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                                @else
                                <i class="fas fa-book text-lg"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-white text-base leading-snug line-clamp-2 group-hover:text-emerald-300 transition-colors">{{ $course->name }}</h3>
                                <p class="text-amber-400 text-[11px] font-semibold tracking-wide mt-0.5">{{ $course->subject->subject_name ?? '-' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="bg-slate-800 text-slate-300 px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase border border-slate-700">{{ $course->getShortCode() }}</span>
                            @if($course->is_published)
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase bg-emerald-950/80 text-emerald-400 border border-emerald-800/80 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Published
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase bg-slate-800 text-slate-400 border border-slate-700">Draft</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex-1 flex flex-col bg-white">
                    {{-- Description --}}
                    <p class="text-slate-600 text-xs mb-4 line-clamp-2 leading-relaxed flex-shrink-0">{{ $course->description ?: 'Kelola materi dan aktivitas belajar untuk ' . ($course->subject->subject_name ?? '') . '.' }}</p>
                    
                    {{-- Stats Metrics Grid --}}
                    <div class="grid grid-cols-3 gap-2 mb-4 flex-shrink-0">
                        <div class="p-2.5 rounded-xl text-center bg-slate-50 border border-slate-100 shadow-inner">
                            <div class="text-lg font-extrabold text-slate-800 leading-none">{{ $course->materials_count }}</div>
                            <div class="text-[10px] font-semibold text-slate-500 mt-1 uppercase tracking-wider">Materi</div>
                        </div>
                        <div class="p-2.5 rounded-xl text-center bg-slate-50 border border-slate-100 shadow-inner">
                            <div class="text-lg font-extrabold text-slate-800 leading-none">{{ $course->assignments_count }}</div>
                            <div class="text-[10px] font-semibold text-slate-500 mt-1 uppercase tracking-wider">Tugas</div>
                        </div>
                        <div class="p-2.5 rounded-xl text-center bg-slate-50 border border-slate-100 shadow-inner">
                            <div class="text-lg font-extrabold text-slate-800 leading-none">{{ $course->quizzes_count }}</div>
                            <div class="text-[10px] font-semibold text-slate-500 mt-1 uppercase tracking-wider">Quiz</div>
                        </div>
                    </div>

                    {{-- Classroom Info Badge --}}
                    @if($classNames)
                    <div class="flex items-center gap-2 text-xs text-slate-700 mb-4 bg-emerald-50/60 p-2.5 rounded-xl border border-emerald-100 flex-shrink-0">
                        <i class="fas fa-users text-emerald-600 text-xs"></i>
                        <span class="truncate font-semibold text-slate-800" title="{{ $classNames }}">Kelas: {{ $classNames }}</span>
                    </div>
                    @endif
                    
                    {{-- Action buttons --}}
                    <div class="mt-auto space-y-2">
                        <a href="{{ route('guru.lms.show', $course->id) }}"
                           class="flex items-center justify-center gap-2 w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md text-xs font-bold tracking-wide">
                            <i class="fas fa-arrow-right-to-bracket text-xs text-amber-400"></i> Kelola Ruang Ajar
                        </a>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('guru.lms.edit', $course->id) }}"
                               class="flex items-center justify-center gap-1.5 w-full bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-xl transition-colors text-xs font-semibold border border-slate-200/80" title="Edit Course">
                                <i class="fas fa-edit text-xs text-slate-500"></i> Edit
                            </a>
                            <form action="{{ route('guru.lms.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus course \'{{ addslashes($course->name) }}\'? Semua modul dan materi di dalamnya akan terhapus.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="flex items-center justify-center gap-1.5 w-full bg-rose-50 hover:bg-rose-100 text-rose-700 px-3 py-2 rounded-xl transition-colors text-xs font-semibold border border-rose-200/80" title="Hapus Course">
                                    <i class="fas fa-trash text-xs text-rose-500"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $courses->links() }}</div>
    
    @else
    {{-- Empty State --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-12 md:p-16 text-center max-w-xl mx-auto">
        <div class="w-16 h-16 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-emerald-600">
            <i class="fas fa-book-open text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Course Ajar</h3>
        <p class="text-slate-500 text-xs mb-6 max-w-sm mx-auto leading-relaxed">Mulai buat course pertama Anda untuk mengunggah materi digital, penugasan siswa, dan game edukasi.</p>
        <a href="{{ route('guru.lms.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-7 py-3 rounded-xl font-bold text-xs tracking-wide transition-all shadow-md">
            <i class="fas fa-plus-circle text-xs"></i> Buat Course Pertama
        </a>
    </div>
    @endif
</div>
@endsection
