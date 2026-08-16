@extends('layouts.guru')

@section('title', 'LMS - Portal Guru')

@push('styles')
<style>
    .rombel-card { animation: fadeUp 0.35s ease both; }
    .rombel-card:nth-child(1) { animation-delay: 0s; }
    .rombel-card:nth-child(2) { animation-delay: 0.05s; }
    .rombel-card:nth-child(3) { animation-delay: 0.10s; }
    .rombel-card:nth-child(4) { animation-delay: 0.15s; }
    .rombel-card:nth-child(5) { animation-delay: 0.20s; }
    .rombel-card:nth-child(6) { animation-delay: 0.25s; }
    .course-card { animation: fadeUp 0.3s ease both; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .rombel-btn.active {
        background-color: #000 !important;
        color: #fbbf24 !important;
        border-color: #000 !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO BANNER --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border-2 border-black">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3.5 mb-3">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #090d16 !important; color: #ffffff !important;">
                        <i class="fas fa-chalkboard-teacher text-2xl text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-black text-xs font-black uppercase tracking-[0.2em]">Learning Management System Guru</p>
                        <h1 class="text-2xl md:text-3xl font-black text-black tracking-tight">Selamat Datang, {{ explode(' ', $teacher->user->name ?? 'Guru')[0] }}! 👋</h1>
                    </div>
                </div>
                <p class="text-black font-bold text-sm max-w-md leading-relaxed">Pilih rombel untuk mengelola materi, tugas, dan kuis secara terfokus per kelas.</p>

                @if($activeSemester)
                <div class="mt-4 inline-flex items-center gap-2 border-2 border-black rounded-xl px-4 py-2 shadow-sm font-black text-xs bg-amber-300 text-black">
                    <i class="fas fa-calendar-alt text-black text-sm"></i>
                    <span>{{ $activeSemester->semester_name }}</span>
                </div>
                @endif
            </div>

            {{-- Quick Actions --}}
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('guru.lms.create') }}" class="inline-flex items-center gap-2 bg-black hover:bg-amber-400 hover:text-black text-white px-5 py-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-md border-2 border-black">
                    <i class="fas fa-plus-circle text-amber-400 text-sm"></i> Buat Course Baru
                </a>
                <a href="{{ route('guru.cbt.banks.index') }}" class="inline-flex items-center gap-2 bg-amber-300 hover:bg-amber-400 text-black px-5 py-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-md border-2 border-black">
                    <i class="fas fa-database text-sm"></i> Bank Soal
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- PILIH ROMBEL --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if($classrooms->count() > 0)
    <div>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 bg-black rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-users text-amber-400 text-sm"></i>
            </div>
            <div>
                <h2 class="text-base font-black text-black uppercase tracking-wider">Pilih Rombel</h2>
                <p class="text-xs text-gray-500 font-semibold">Klik rombel untuk melihat course, tugas, dan kuis kelas tersebut</p>
            </div>
        </div>

        {{-- Rombel Filter Buttons --}}
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('guru.lms.index') }}"
               class="rombel-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black border-2 border-black transition-all shadow-sm
                      {{ !$selectedClassroomId ? 'bg-black text-amber-400' : 'bg-white text-black hover:bg-amber-300' }}">
                <i class="fas fa-th-large text-xs"></i> Semua Rombel
            </a>
            @foreach($classrooms as $classroom)
            <a href="{{ route('guru.lms.index', ['classroom_id' => $classroom->id]) }}"
               class="rombel-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black border-2 border-black transition-all shadow-sm
                      {{ $selectedClassroomId == $classroom->id ? 'bg-black text-amber-400' : 'bg-white text-black hover:bg-amber-300' }}">
                <i class="fas fa-users text-xs"></i>
                {{ $classroom->class_name }}
            </a>
            @endforeach
        </div>

        {{-- Rombel Context Banner (when a rombel is selected) --}}
        @if($selectedClassroomId)
        @php $selectedClassroom = $classrooms->firstWhere('id', $selectedClassroomId); @endphp
        @if($selectedClassroom)
        <div class="bg-black text-white rounded-2xl px-6 py-4 mb-6 border-2 border-black flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-400 flex items-center justify-center shrink-0">
                    <i class="fas fa-chalkboard text-black text-lg"></i>
                </div>
                <div>
                    <p class="text-amber-300 text-[10px] font-black uppercase tracking-widest">Menampilkan course untuk rombel</p>
                    <p class="text-white font-black text-lg leading-tight">{{ $selectedClassroom->class_name }}</p>
                </div>
            </div>
            <a href="{{ route('guru.lms.index') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/30 text-white px-4 py-2 rounded-xl text-xs font-black transition">
                <i class="fas fa-times text-xs"></i> Lihat Semua
            </a>
        </div>
        @endif
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- COURSE GRID --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if($courses->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($courses as $course)
        @php
            $scientist = $course->getScientistConfig();
            $classNames = $course->lmsClasses->pluck('classroom.class_name')->filter()->implode(', ');
            $cardColors = ['#4f46e5', '#059669', '#2563eb', '#d97706', '#9333ea', '#0891b2', '#e11d48'];
            $headerBg = $cardColors[$loop->index % count($cardColors)];
        @endphp
        <div class="course-card group">
            <div class="bg-white rounded-3xl border-2 border-black overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                {{-- Card Header --}}
                <div class="relative p-5 overflow-hidden border-b-2 border-black text-white" style="background-color: {{ $headerBg }} !important;">
                    <div class="relative z-10">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black flex-shrink-0 shadow-md" style="background-color: #fbbf24 !important; color: #000000 !important;">
                                @if($scientist)
                                <svg class="w-7 h-7 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                                @else
                                <i class="fas fa-book text-black text-xl"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-black text-white text-base leading-snug line-clamp-2">{{ $course->name }}</h3>
                                <p class="text-amber-400 text-[11px] font-black tracking-wide mt-0.5">{{ $course->subject->subject_name ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="bg-slate-800 text-amber-300 px-3 py-1 rounded-xl text-[10px] font-black tracking-widest uppercase border border-slate-700">{{ $course->getShortCode() }}</span>
                            @if($course->is_published)
                            <span class="px-3 py-1 rounded-xl text-[10px] font-black tracking-widest uppercase bg-emerald-600 text-white border-2 border-black"><i class="fas fa-check-circle mr-1"></i> PUBLISHED</span>
                            @else
                            <span class="px-3 py-1 rounded-xl text-[10px] font-black tracking-widest uppercase bg-slate-200 text-black border-2 border-black">DRAFT</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex-1 flex flex-col bg-white">
                    <p class="text-black font-bold text-xs mb-4 line-clamp-2 leading-relaxed flex-shrink-0">{{ $course->description ?: 'Kelola materi dan aktivitas belajar untuk ' . ($course->subject->subject_name ?? '') . '.' }}</p>

                    {{-- Stats Row --}}
                    <div class="grid grid-cols-3 gap-2 mb-4 flex-shrink-0">
                        <div class="p-2.5 rounded-2xl text-center border-2 border-black shadow-xs bg-slate-50">
                            <div class="text-xl font-black text-black leading-none">{{ $course->materials_count }}</div>
                            <div class="text-[9px] font-black uppercase tracking-wider text-black mt-1">Materi</div>
                        </div>
                        <div class="p-2.5 rounded-2xl text-center border-2 border-black shadow-xs bg-slate-50">
                            <div class="text-xl font-black text-black leading-none">{{ $course->assignments_count }}</div>
                            <div class="text-[9px] font-black uppercase tracking-wider text-black mt-1">Tugas</div>
                        </div>
                        <div class="p-2.5 rounded-2xl text-center border-2 border-black shadow-xs bg-slate-50">
                            <div class="text-xl font-black text-black leading-none">{{ $course->quizzes_count }}</div>
                            <div class="text-[9px] font-black uppercase tracking-wider text-black mt-1">Quiz</div>
                        </div>
                    </div>

                    {{-- Rombel Info --}}
                    @if($classNames)
                    <div class="flex items-start gap-2 text-xs text-black font-black mb-4 bg-amber-50 p-3 rounded-2xl border-2 border-black flex-shrink-0">
                        <i class="fas fa-users text-black text-sm mt-0.5 shrink-0"></i>
                        <span class="font-bold leading-relaxed">{{ $classNames }}</span>
                    </div>
                    @endif

                    {{-- CTA Actions --}}
                    <div class="mt-auto space-y-2">
                        <a href="{{ route('guru.lms.show', $course->id) }}{{ $selectedClassroomId ? '?classroom_id='.$selectedClassroomId : '' }}"
                           class="flex items-center justify-center gap-2 w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-2xl transition-all shadow-md text-xs font-black uppercase tracking-wider border-2 border-black">
                            <i class="fas fa-arrow-right-to-bracket text-xs text-amber-400"></i>
                            Kelola Ruang Ajar
                            @if($selectedClassroomId)
                            <span class="ml-1 opacity-70 text-[9px] normal-case font-bold">({{ $selectedClassroom->class_name ?? '' }})</span>
                            @endif
                        </a>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('guru.lms.edit', $course->id) }}"
                               class="flex items-center justify-center gap-1.5 w-full bg-slate-100 hover:bg-amber-300 text-black px-3 py-2 rounded-xl transition-all text-xs font-black border-2 border-black shadow-xs">
                                <i class="fas fa-edit text-xs"></i> Edit
                            </a>
                            <form action="{{ route('guru.lms.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus course \'{{ addslashes($course->name) }}\'? Semua modul dan materi di dalamnya akan terhapus.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="flex items-center justify-center gap-1.5 w-full bg-rose-100 hover:bg-rose-600 hover:text-white text-rose-800 px-3 py-2 rounded-xl transition-all text-xs font-black border-2 border-black shadow-xs">
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

    @elseif($selectedClassroomId)
    {{-- Empty state when rombel selected but no courses --}}
    <div class="bg-white rounded-3xl shadow-md border-2 border-black p-12 text-center">
        <div class="w-16 h-16 bg-amber-300 border-2 border-black rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-folder-open text-2xl text-black"></i></div>
        <h3 class="text-lg font-black text-black mb-1">Belum Ada Course untuk Rombel Ini</h3>
        <p class="text-black font-bold text-xs mb-6 max-w-sm mx-auto">Buat course baru dan tautkan ke rombel <strong>{{ optional($classrooms->firstWhere('id', $selectedClassroomId))->class_name }}</strong>.</p>
        <a href="{{ route('guru.lms.create') }}" class="inline-flex items-center gap-2 bg-black hover:bg-amber-400 hover:text-black text-white px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider border-2 border-black shadow-md">
            <i class="fas fa-plus-circle text-sm text-amber-400"></i> Buat Course Baru
        </a>
    </div>

    @else
    {{-- Empty state no courses at all --}}
    <div class="bg-white rounded-3xl shadow-md border-2 border-black p-16 text-center">
        <div class="w-20 h-20 bg-amber-300 border-2 border-black rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-book-open text-3xl text-black"></i></div>
        <h3 class="text-lg font-black text-black mb-1">Belum Ada Course Ajar</h3>
        <p class="text-black font-bold text-xs mb-6 max-w-sm mx-auto">Mulai buat course pertama Anda untuk mengunggah materi digital dan tugas siswa.</p>
        <a href="{{ route('guru.lms.create') }}" class="inline-flex items-center gap-2 bg-black hover:bg-amber-400 hover:text-black text-white px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider border-2 border-black shadow-md">
            <i class="fas fa-plus-circle text-sm text-amber-400"></i> Buat Course Pertama
        </a>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- ORPHAN COURSES (Belum Ditautkan ke Rombel) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if($orphanCourses->count() > 0)
    <div class="mt-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 bg-amber-400 rounded-xl flex items-center justify-center shrink-0 border-2 border-black">
                <i class="fas fa-exclamation-triangle text-black text-sm"></i>
            </div>
            <div>
                <h2 class="text-sm font-black text-black uppercase tracking-wider">Course Belum Ditautkan ke Rombel</h2>
                <p class="text-xs text-gray-500 font-semibold">Course berikut perlu diedit dan ditautkan ke rombel agar muncul di navigasi rombel</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($orphanCourses as $course)
            <div class="bg-amber-50 border-2 border-amber-400 rounded-2xl p-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-black text-black text-sm truncate">{{ $course->name }}</p>
                    <p class="text-xs text-amber-700 font-bold mt-0.5">{{ $course->subject->subject_name ?? 'Tanpa Mapel' }}</p>
                </div>
                <a href="{{ route('guru.lms.edit', $course->id) }}"
                   class="shrink-0 inline-flex items-center gap-1.5 bg-black hover:bg-amber-400 hover:text-black text-white px-3 py-2 rounded-xl text-xs font-black border-2 border-black transition">
                    <i class="fas fa-link text-xs"></i> Edit & Tautkan
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
