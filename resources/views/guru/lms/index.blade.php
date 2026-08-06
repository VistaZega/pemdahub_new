@extends('layouts.guru')

@section('title', 'LMS - Portal Guru')

@push('styles')
<style>
    .course-card { animation: fadeUp 0.4s ease both; }
    .course-card:nth-child(1) { animation-delay: 0s; }
    .course-card:nth-child(2) { animation-delay: 0.06s; }
    .course-card:nth-child(3) { animation-delay: 0.12s; }
    .course-card:nth-child(4) { animation-delay: 0.18s; }
    .course-card:nth-child(5) { animation-delay: 0.24s; }
    .course-card:nth-child(6) { animation-delay: 0.30s; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO BANNER (100% SOLID UI UX PRO MAX) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-white rounded-xl p-6 md:p-8 overflow-hidden shadow-xl border border-gray-200">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center mb-3" >
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center shadow-md border border-gray-200" >
                        <i class="fas fa-chalkboard-teacher text-2xl text-white"></i>
                    </div>
                    <div >
                        <p class="text-gray-800 text-xs font-semibold uppercase tracking-[0.2em]" >Learning Management System Guru</p>
                        <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 tracking-tight" >Selamat Datang, {{ explode(' ', $teacher->user->name ?? 'Guru')[0] }}!</h2>
                    </div>
                </div>
                <p class="text-gray-800 font-bold text-sm max-w-md leading-relaxed mt-2">Kelola modul ajar, materi digital, tugas siswa, dan kuis evaluasi untuk kelas Anda.</p>
                
                @if($activeSemester)
                <div class="mt-4 inline-flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-2 shadow-sm" >
                    <i class="fas fa-calendar-alt text-gray-800 text-sm"></i>
                    <span class="text-xs font-semibold tracking-wide">{{ $activeSemester->semester_name }}</span>
                </div>
                @endif
            </div>

            {{-- Quick Stats --}}
            <div class="flex items-center gap-3">
                @php
                    $totalMaterials = $courses->sum('materials_count');
                    $totalAssignments = $courses->sum('assignments_count');
                    $totalQuizzes = $courses->sum('quizzes_count');
                @endphp
                <div class="bg-white border border-gray-200 rounded-xl px-5 py-3 text-center min-w-[90px] shadow-md">
                    <div class="text-2xl font-semibold leading-none text-gray-800">{{ $courses->total() }}</div>
                    <div class="text-[10px] font-semibold tracking-wide text-gray-800 mt-1">Course</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl px-5 py-3 text-center min-w-[90px] shadow-md" >
                    <div class="text-2xl font-semibold leading-none text-gray-800">{{ $totalMaterials }}</div>
                    <div class="text-[10px] font-semibold tracking-wide text-gray-800 mt-1">Materi</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl px-5 py-3 text-center min-w-[90px] shadow-md" >
                    <div class="text-2xl font-semibold leading-none text-gray-800">{{ $totalAssignments + $totalQuizzes }}</div>
                    <div class="text-[10px] font-semibold tracking-wide text-gray-800 mt-1">Evaluasi</div>
                </div>
            </div>
        </div>

        {{-- Create button --}}
        <div class="relative z-10 mt-6 flex items-center gap-3">
            <a href="{{ route('guru.lms.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white hover:bg-emerald-700 px-6 py-3.5 rounded-xl font-semibold text-xs tracking-wide transition-all shadow-md border border-gray-200">
                <i class="fas fa-plus-circle text-sm"></i> Buat Course Ajar Baru
            </a>
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
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                {{-- Card Header (Solid Dark Header) --}}
                <div class="relative p-5 overflow-hidden border-b-2 border-gray-200" >
                    <div class="relative z-10">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center border border-gray-200 flex-shrink-0 shadow-md" >
                                @if($scientist)
                                <svg class="w-7 h-7 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                                @else
                                <i class="fas fa-book text-gray-800 text-xl"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-white text-base leading-snug line-clamp-2">{{ $course->name }}</h3>
                                <p class="text-amber-500 text-[11px] font-semibold tracking-wide mt-0.5">{{ $course->subject->subject_name ?? '-' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="bg-slate-800 text-amber-300 px-3 py-1 rounded-xl text-[10px] font-semibold tracking-wide border border-slate-700">{{ $course->getShortCode() }}</span>
                            @if($course->is_published)
                            <span class="px-3 py-1 rounded-xl text-[10px] font-semibold tracking-wide text-gray-800 border border-gray-200" ><i class="fas fa-check-circle mr-1"></i> PUBLISHED</span>
                            @else
                            <span class="px-3 py-1 rounded-xl text-[10px] font-semibold tracking-wide text-gray-800 border border-gray-200" >DRAFT</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex-1 flex flex-col bg-white">
                    {{-- Description --}}
                    <p class="text-gray-800 font-bold text-xs mb-4 line-clamp-2 leading-relaxed flex-shrink-0">{{ $course->description ?: 'Kelola materi dan aktivitas belajar untuk ' . ($course->subject->subject_name ?? '') . '.' }}</p>
                    
                    {{-- Stats Row --}}
                    <div class="grid grid-cols-3 gap-2 mb-4 flex-shrink-0">
                        <div class="p-2.5 rounded-xl text-center border border-gray-200 shadow-sm" >
                            <div class="text-xl font-semibold text-gray-800 leading-none">{{ $course->materials_count }}</div>
                            <div class="text-[9px] font-semibold text-gray-800 mt-1 tracking-wide">Materi</div>
                        </div>
                        <div class="p-2.5 rounded-xl text-center border border-gray-200 shadow-sm" >
                            <div class="text-xl font-semibold text-gray-800 leading-none">{{ $course->assignments_count }}</div>
                            <div class="text-[9px] font-semibold text-gray-800 mt-1 tracking-wide">Tugas</div>
                        </div>
                        <div class="p-2.5 rounded-xl text-center border border-gray-200 shadow-sm" >
                            <div class="text-xl font-semibold text-gray-800 leading-none">{{ $course->quizzes_count }}</div>
                            <div class="text-[9px] font-semibold text-gray-800 mt-1 tracking-wide">Quiz</div>
                        </div>
                    </div>

                    {{-- Classroom Info --}}
                    @if($classNames)
                    <div class="flex items-center gap-2 text-xs text-gray-800 font-bold mb-4 bg-slate-100 p-3 rounded-xl border border-gray-200 flex-shrink-0">
                        <i class="fas fa-users text-gray-800 text-sm"></i>
                        <span class="truncate font-semibold" title="{{ $classNames }}">Kelas: {{ $classNames }}</span>
                    </div>
                    @endif
                    
                    {{-- CTA Actions (Kelola, Edit, Hapus) --}}
                    <div class="mt-auto space-y-2">
                        <a href="{{ route('guru.lms.show', $course->id) }}"
                           class="flex items-center justify-center gap-2 w-full bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-3 rounded-xl transition-all shadow-md text-xs font-semibold tracking-wide border border-gray-200">
                            <i class="fas fa-arrow-right-to-bracket text-xs text-amber-500"></i> Kelola Ruang Ajar
                        </a>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('guru.lms.edit', $course->id) }}"
                               class="flex items-center justify-center gap-1.5 w-full bg-slate-100 hover:bg-amber-100 text-amber-800 text-gray-800 px-3 py-2 rounded-xl transition-all text-xs font-semibold border border-gray-200 shadow-sm" title="Edit Course">
                                <i class="fas fa-edit text-xs"></i> Edit
                            </a>
                            <form action="{{ route('guru.lms.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus course \'{{ addslashes($course->name) }}\'? Semua modul dan materi di dalamnya akan terhapus.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="flex items-center justify-center gap-1.5 w-full bg-rose-100 hover:bg-rose-600 hover:text-white text-rose-800 px-3 py-2 rounded-xl transition-all text-xs font-semibold border border-gray-200 shadow-sm" title="Hapus Course">
                                    <i class="fas fa-trash text-xs"></i> Hapus
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
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-16 text-center">
        <div class="w-20 h-20 bg-amber-100 border border-gray-200 rounded-xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-book-open text-3xl text-gray-800"></i></div>
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Belum Ada Course Ajar</h3>
        <p class="text-gray-800 font-bold text-xs mb-6 max-w-sm mx-auto">Mulai buat course pertama Anda untuk mengunggah materi digital dan tugas siswa.</p>
        <a href="{{ route('guru.lms.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white hover:bg-emerald-700 px-8 py-3.5 rounded-xl font-semibold text-xs tracking-wide border border-gray-200 shadow-md">
            <i class="fas fa-plus-circle text-sm"></i> Buat Course Pertama
        </a>
    </div>
    @endif
</div>
@endsection
