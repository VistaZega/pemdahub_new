@extends('layouts.guru')

@section('title', 'Course Management - ' . $course->name)

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<style>
    .ql-toolbar.ql-snow {
        border: 2px solid #000 !important;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
        background-color: #f8fafc;
    }
    .ql-container.ql-snow {
        border: 2px solid #000 !important;
        border-top: none !important;
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
        font-family: inherit;
        background-color: #fff;
    }
    .ql-editor {
        min-height: 140px;
        max-height: 350px;
        font-size: 0.875rem;
    }
    .ql-editor img, .prose img {
        display: block !important;
        max-width: 100% !important;
        height: auto !important;
        margin: 1.25rem auto !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 4px 14px rgba(0,0,0,0.12) !important;
        clear: both !important;
    }
    .prose p {
        margin-bottom: 0.75rem !important;
        line-height: 1.75 !important;
    }
    .tab-content { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .hero-pattern {
        background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.08) 0%, transparent 50%),
                          radial-gradient(circle at 80% 20%, rgba(255,255,255,0.06) 0%, transparent 40%);
    }
    .module-card { animation: fadeIn 0.3s ease both; }
    /* Stat card hover lift */
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.12);
    }
    /* Chart container subtle gradient */
    .chart-container {
        background: linear-gradient(135deg, rgba(249,250,251,0.5) 0%, rgba(243,244,246,0.3) 100%);
        border-radius: 1rem;
        padding: 0.5rem;
    }
    /* Tab transition animations */
    [x-show].tab-content {
        animation: tabSlideIn 0.35s ease-out;
    }
    @keyframes tabSlideIn {
        from { opacity: 0; transform: translateY(12px) scale(0.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    /* Submission progress bar animation */
    @keyframes progressFill {
        from { width: 0%; }
    }
    .progress-animate {
        animation: progressFill 0.8s ease-out;
    }
</style>
@endpush

<?php
if (!function_exists('balanceHtmlTags')) {
    function balanceHtmlTags($html) {
        if (empty(trim($html))) return $html;
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $balanced = $dom->saveHTML();
        libxml_clear_errors();
        $balanced = str_replace(['<?xml encoding="utf-8" ?>', '<html>', '</html>', '<body>', '</body>'], '', $balanced);
        return trim($balanced);
    }
}
?>

@section('content')
@php
    $colorConfig = \App\Models\LmsCourse::getColorClasses($course->color);
    $scientist = $course->getScientistConfig();
    $courseClassroom = optional($course->classroom);
    $firstLmsClassroom = optional(optional($course->lmsClasses->first())->classroom);
    $classNames = $course->lmsClasses->pluck('classroom.class_name')->filter()->implode(', ');

    // Prepare analytics data
    $materialsData = [];
    foreach ($course->modules as $module) {
        foreach ($module->materials as $material) {
            $completedCount = \App\Models\LmsMaterialProgress::where('material_id', $material->id)
                ->where('status', 'completed')
                ->count();
            $materialsData[] = [
                'title' => strlen($material->title) > 20 ? substr($material->title, 0, 18) . '..' : $material->title,
                'count' => $completedCount
            ];
        }
    }

    $quizScores = [
        'A (86-100)' => 0,
        'B (71-85)' => 0,
        'C (56-70)' => 0,
        'D (0-55)' => 0,
    ];
    foreach ($course->quizzes as $quiz) {
        $attempts = \App\Models\LmsQuizAttempt::where('quiz_id', $quiz->id)->get();
        foreach ($attempts as $att) {
            $score = $att->score;
            if ($score >= 86) $quizScores['A (86-100)']++;
            elseif ($score >= 71) $quizScores['B (71-85)']++;
            elseif ($score >= 56) $quizScores['C (56-70)']++;
            else $quizScores['D (0-55)']++;
        }
    }
@endphp
<div class="space-y-6">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- COURSE HERO BANNER (100% SOLID UI UX PRO MAX) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border-2 border-black">
        <div class="relative z-10">
            {{-- Back + Status --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <a href="{{ route('guru.lms.index') }}" class="inline-flex items-center gap-2 bg-black text-white border-2 border-black px-4 py-2 rounded-xl text-xs font-black hover:bg-amber-400 hover:text-black transition-all shadow-md">
                    <i class="fas fa-arrow-left text-xs"></i> Kembali ke Daftar Course
                </a>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('guru.lms.meeting.attendance', $course->id) }}" class="bg-slate-100 border-2 border-black text-black hover:bg-amber-300 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-sm transition-all">
                        <i class="fas fa-clipboard-user text-black"></i> Rekap Kehadiran
                    </a>
                    @if($course->meeting_active)
                        <a href="{{ route('guru.lms.meeting.join', $course->id) }}" class="bg-rose-600 text-white font-black px-3.5 py-2 rounded-xl text-[10px] uppercase tracking-wider flex items-center gap-1.5 shadow-md border-2 border-black hover:bg-rose-700 transition-all">
                            <span class="w-2 h-2 bg-white rounded-full animate-ping"></span>
                            Live Tatap Muka
                        </a>
                        <form action="{{ route('guru.lms.meeting.stop', $course->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengakhiri tatap muka virtual?');">
                            @csrf
                            <button type="submit" class="bg-black text-white hover:bg-rose-600 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all border-2 border-black">
                                Akhiri
                            </button>
                        </form>
                    @else
                        <form action="{{ route('guru.lms.meeting.start', $course->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-black hover:bg-emerald-600 text-white px-3.5 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-md transition-all border-2 border-black">
                                <i class="fas fa-video text-xs text-amber-400"></i> Mulai Tatap Muka
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('guru.lms.analytics', $course->id) }}" class="px-3 py-2 bg-black hover:bg-amber-400 hover:text-black text-white border-2 border-black rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-md transition-all">
                        <i class="fas fa-chart-line text-xs"></i> Analitik
                    </a>
                    <a href="{{ route('guru.lms.export-gradebook', $course->id) }}" class="px-3 py-2 text-white rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-md border-2 border-black transition-all" style="background-color: #059669 !important;">
                        <i class="fas fa-file-excel text-xs text-white"></i> Ekspor Excel
                    </a>
                    <a href="{{ route('guru.lms.edit', $course->id) }}" class="px-3 py-2 bg-amber-300 hover:bg-amber-400 text-black border-2 border-black rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-md transition-all" title="Edit Course">
                        <i class="fas fa-edit text-xs"></i> Edit
                    </a>
                    <form action="{{ route('guru.lms.destroy', $course->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus course ini? Semua modul dan materi akan terhapus.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white border-2 border-black rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-md transition-all" title="Hapus Course">
                            <i class="fas fa-trash text-xs"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>

            {{-- Course Info --}}
            <div class="flex items-start mb-6" style="display: flex !important; align-items: flex-start !important; gap: 16px !important;">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-md border-2 border-black" style="width: 64px !important; height: 64px !important; min-width: 64px !important; min-height: 64px !important; flex-shrink: 0 !important; margin-right: 16px !important; background-color: #1e3a8a !important; color: #ffffff !important;">
                    @if($scientist)
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                    @else
                    <i class="fas fa-chalkboard-teacher text-white text-2xl"></i>
                    @endif
                </div>
                <div style="flex: 1 !important; min-width: 0 !important;">
                    <h1 class="text-2xl md:text-3xl font-black text-black tracking-tight leading-tight" style="margin: 0 !important;">{{ $course->course_name ?? $course->name }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="border-2 border-black px-3 py-1 rounded-xl text-xs font-black" style="background-color: #fbbf24 !important; color: #000000 !important;">{{ $course->subject->subject_name ?? '' }}</span>
                        <span class="text-black font-black text-xs flex items-center gap-1"><i class="fas fa-clock text-xs text-black"></i> {{ $course->semester->semester_name ?? '-' }}</span>
                        @if($classNames)
                        <span class="text-black font-black text-xs flex items-center gap-1"><i class="fas fa-users text-xs text-black"></i> Kelas: {{ $classNames }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stat Pills --}}
            <div class="flex flex-wrap gap-2.5">
                <div class="bg-white border-2 border-black rounded-2xl px-4 py-2 flex items-center gap-2 shadow-sm" style="background-color: #d1fae5 !important;">
                    <i class="fas fa-book text-black text-xs"></i>
                    <span class="text-sm font-black text-black">{{ $course->materials_count }}</span>
                    <span class="text-[10px] text-black font-black uppercase tracking-wider">Materi</span>
                </div>
                <div class="bg-white border-2 border-black rounded-2xl px-4 py-2 flex items-center gap-2 shadow-sm" style="background-color: #e0f2fe !important;">
                    <i class="fas fa-tasks text-black text-xs"></i>
                    <span class="text-sm font-black text-black">{{ $course->assignments_count }}</span>
                    <span class="text-[10px] text-black font-black uppercase tracking-wider">Tugas</span>
                </div>
                <div class="bg-white border-2 border-black rounded-2xl px-4 py-2 flex items-center gap-2 shadow-sm" style="background-color: #f3e8ff !important;">
                    <i class="fas fa-question-circle text-black text-xs"></i>
                    <span class="text-sm font-black text-black">{{ $course->quizzes_count }}</span>
                    <span class="text-[10px] text-black font-black uppercase tracking-wider">Quiz</span>
                </div>
                <div class="bg-white border-2 border-black rounded-2xl px-4 py-2 flex items-center gap-2 shadow-sm" style="background-color: #fef08a !important;">
                    <i class="fas fa-user-graduate text-black text-xs"></i>
                    <span class="text-sm font-black text-black">{{ $totalStudents }}</span>
                    <span class="text-[10px] text-black font-black uppercase tracking-wider">Siswa</span>
                </div>
                <div class="bg-white border-2 border-black rounded-2xl px-4 py-2 flex items-center gap-2 shadow-sm" style="background-color: #fecdd3 !important;">
                    <i class="fas fa-bullhorn text-black text-xs"></i>
                    <span class="text-sm font-black text-black">{{ $course->announcements_count ?? 0 }}</span>
                    <span class="text-[10px] text-black font-black uppercase tracking-wider">Info</span>
                </div>
                <div class="bg-white border-2 border-black rounded-2xl px-4 py-2 flex items-center gap-2 shadow-sm" style="background-color: #cff4fc !important;">
                    <i class="fas fa-comments text-black text-xs"></i>
                    <span class="text-sm font-black text-black">{{ $course->discussions_count ?? 0 }}</span>
                    <span class="text-[10px] text-black font-black uppercase tracking-wider">Diskusi</span>
                </div>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB NAVIGATION --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div x-data="{ tab: '{{ request('tab', 'materials') }}' }">
        <div class="bg-white rounded-2xl shadow-md border-2 border-black p-1.5 flex flex-wrap gap-1.5 sticky top-0 z-20">
            <button @click="tab = 'materials'" :class="tab === 'materials' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-1.5">
                <i class="fas fa-book text-xs"></i> <span class="hidden sm:inline">Kurikulum</span>
            </button>
            <button @click="tab = 'assignments'" :class="tab === 'assignments' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-1.5">
                <i class="fas fa-tasks text-xs"></i> <span class="hidden sm:inline">Tugas</span>
                @if($course->assignments_count > 0)<span class="rounded-full px-2 py-0.5 text-[10px] font-black border border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">{{ $course->assignments_count }}</span>@endif
            </button>
            <button @click="tab = 'quizzes'" :class="tab === 'quizzes' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-1.5">
                <i class="fas fa-question-circle text-xs"></i> <span class="hidden sm:inline">Quiz</span>
                @if($course->quizzes_count > 0)<span class="rounded-full px-2 py-0.5 text-[10px] font-black border border-black" style="background-color: #c084fc !important; color: #000000 !important;">{{ $course->quizzes_count }}</span>@endif
            </button>
            <button @click="tab = 'announcements'" :class="tab === 'announcements' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-1.5">
                <i class="fas fa-bullhorn text-xs"></i> <span class="hidden sm:inline">Pengumuman</span>
            </button>
            <button @click="tab = 'discussions'" :class="tab === 'discussions' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-1.5">
                <i class="fas fa-comments text-xs"></i> <span class="hidden sm:inline">Diskusi</span>
            </button>
            <button @click="tab = 'groups'" :class="tab === 'groups' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-1.5">
                <i class="fas fa-users text-xs"></i> <span class="hidden sm:inline">Kelompok</span>
                @if(($course->course_groups_count ?? 0) > 0)<span class="rounded-full px-2 py-0.5 text-[10px] font-black border border-black" style="background-color: #a855f7 !important; color: #ffffff !important;">{{ $course->course_groups_count }}</span>@endif
            </button>
            <button @click="tab = 'analytics'" :class="tab === 'analytics' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-1.5">
                <i class="fas fa-chart-line text-xs"></i> <span class="hidden sm:inline">Analitik</span>
            </button>
            <button @click="tab = 'info'" :class="tab === 'info' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-1.5">
                <i class="fas fa-info-circle text-xs"></i> <span class="hidden sm:inline">Data Kelas</span>
            </button>
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: MATERIALS / MODULES --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'materials'" class="mt-6 space-y-6 tab-content">
            <div class="flex items-center justify-between">
                <h3 class="font-black text-black text-sm flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center border-2 border-black" style="background-color: #d1fae5 !important; color: #000000 !important;"><i class="fas fa-layer-group text-black text-xs"></i></span>
                    STRUKTUR KURIKULUM & MODUL
                </h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('guru.lms.modules.create', $course->id) }}" class="bg-white border-2 border-black text-black px-3.5 py-2 rounded-xl text-[10px] font-black uppercase transition hover:bg-amber-300 shadow-sm">
                        <i class="fas fa-plus mr-1 text-black"></i> Tambah Modul
                    </a>
                    <button @click="$dispatch('open-game-modal')" class="bg-black hover:bg-purple-600 text-white border-2 border-black px-3.5 py-2 rounded-xl text-[10px] font-black uppercase transition shadow-md">
                        <i class="fas fa-gamepad mr-1 text-amber-400"></i> Buat Game
                    </button>
                    <button @click="$dispatch('open-material-modal')" class="bg-black hover:bg-emerald-600 text-white border-2 border-black px-3.5 py-2 rounded-xl text-[10px] font-black uppercase transition shadow-md">
                        <i class="fas fa-upload mr-1 text-amber-400"></i> Upload Materi
                    </button>
                </div>
            </div>

            @forelse($course->modules as $module)
            @php
                $moduleHeaderBg = match($module->color) {
                    'indigo' => '#4f46e5',
                    'emerald' => '#059669',
                    'rose' => '#e11d48',
                    'amber' => '#d97706',
                    'blue' => '#2563eb',
                    'purple' => '#9333ea',
                    'cyan' => '#0891b2',
                    'orange' => '#ea580c',
                    default => '#2563eb'
                };
            @endphp
            <div class="module-card bg-white rounded-3xl shadow-md border-2 border-black overflow-hidden transition-all">
                {{-- Module Header --}}
                <div class="px-5 py-4 flex items-center justify-between border-b-2 border-black text-white" style="background-color: {{ $moduleHeaderBg }} !important;">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center text-black font-black text-sm border-2 border-black shadow-sm" style="background-color: #fbbf24 !important;">
                            {{ $module->sequence }}
                        </span>
                        <div>
                            <h3 class="font-black text-white text-base tracking-wide">{{ $module->title }}</h3>
                            <p class="text-amber-200 text-[10px] font-black uppercase tracking-widest">{{ $module->materials->count() }} MATERI AJAR</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('guru.lms.modules.edit', $module->id) }}" class="w-8 h-8 bg-slate-800 rounded-xl flex items-center justify-center text-amber-400 hover:bg-amber-400 hover:text-black transition-all border border-slate-700" title="Edit Modul">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                        <form action="{{ route('guru.lms.modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Hapus modul dan seluruh materinya?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="w-8 h-8 bg-slate-800 rounded-xl flex items-center justify-center text-rose-400 hover:bg-rose-600 hover:text-white transition-all border border-slate-700" title="Hapus Modul">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                {{-- Materials List --}}
                <div class="p-4 space-y-3">
                    @forelse($module->materials as $material)
                    <div x-data="{ expanded: false }" class="bg-white rounded-2xl border-2 border-black hover:shadow-md transition-all overflow-hidden group/mat">
                        <div class="flex items-center justify-between p-3.5 cursor-pointer bg-slate-50 hover:bg-amber-100 transition-colors" @click="expanded = !expanded">
                            <div class="flex items-center gap-4">
                                @php
                                    $bgMat = match($material->material_type) {
                                        'text' => '#4f46e5',
                                        'pdf' => '#dc2626',
                                        'video' => '#2563eb',
                                        'image' => '#059669',
                                        'link' => '#9333ea',
                                        'interactive' => '#0284c7',
                                        'document' => '#ea580c',
                                        'canva' => '#00c4cc',
                                        'googledocs' => '#ea4335',
                                        'audio' => '#d97706',
                                        'embed' => '#0f172a',
                                        default => '#475569',
                                    };
                                    $iconMat = match($material->material_type) {
                                        'text' => 'fa-book-open',
                                        'pdf' => 'fa-file-pdf',
                                        'video' => 'fa-video',
                                        'image' => 'fa-image',
                                        'link' => 'fa-link',
                                        'interactive' => 'fa-gamepad',
                                        'document' => 'fa-file-word',
                                        'canva' => 'fa-palette',
                                        'googledocs' => 'fa-file-alt',
                                        'audio' => 'fa-volume-up',
                                        'embed' => 'fa-code',
                                        default => 'fa-file',
                                    };
                                @endphp
                                <span class="w-11 h-11 rounded-2xl flex items-center justify-center text-white font-black shadow-md border-2 border-black flex-shrink-0 mr-2" style="background-color: {{ $bgMat }} !important; color: #ffffff !important;">
                                    <i class="fas {{ $iconMat }} text-lg"></i>
                                </span>
                                <div class="space-y-1">
                                    <div class="font-black text-black text-sm flex flex-wrap items-center gap-2">
                                        <span class="text-black font-black text-xs bg-amber-300 px-2.5 py-0.5 rounded-lg border border-black inline-block shadow-2xs">{{ $module->getCode() }}-{{ $loop->iteration }}</span>
                                        <span>{{ preg_replace('/^\d+\.\d+\s*/', '', $material->title) }}</span>
                                    </div>
                                    <p class="text-[10px] text-black font-black uppercase tracking-wider">{{ $material->getContentTypeLabel() }}{{ $material->file_size ? ' · ' . number_format($material->file_size / 1024, 0) . ' KB' : '' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-chevron-down text-black text-sm font-black transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
                                <button @click.stop="$dispatch('open-edit-material-modal', {{ json_encode([
                                    'id' => $material->id,
                                    'module_id' => $material->module_id,
                                    'title' => preg_replace('/^\d+\.\d+\s*/', '', $material->title),
                                    'material_type' => $material->material_type,
                                    'content' => $material->content ?? '',
                                    'file_url' => $material->file_url,
                                    'update_url' => route('guru.lms.materials.update', $material->id)
                                ]) }})" class="w-8 h-8 rounded-xl flex items-center justify-center bg-white text-black hover:bg-amber-300 transition-colors border border-black shadow-sm" title="Edit Materi"><i class="fas fa-edit text-xs"></i></button>
                                @if($material->file_path)
                                <a href="{{ route('guru.lms.materials.download', $material->id) }}" class="w-8 h-8 rounded-xl flex items-center justify-center bg-white text-black hover:bg-sky-300 transition-colors border border-black shadow-sm" onclick="event.stopPropagation()" title="Unduh File"><i class="fas fa-download text-xs"></i></a>
                                @endif
                                @if($material->file_url)
                                <a href="{{ $material->file_url }}" target="_blank" class="w-8 h-8 rounded-xl flex items-center justify-center bg-white text-black hover:bg-sky-300 transition-colors border border-black shadow-sm" onclick="event.stopPropagation()" title="Buka Link"><i class="fas fa-external-link-alt text-xs"></i></a>
                                @endif
                                <form action="{{ route('guru.lms.materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')" class="inline" onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button class="w-8 h-8 rounded-xl flex items-center justify-center bg-white text-black hover:bg-rose-500 hover:text-white transition-colors border border-black shadow-sm" title="Hapus Materi"><i class="fas fa-trash text-xs"></i></button>
                                </form>
                            </div>
                        </div>
                        <div x-show="expanded" x-transition x-cloak class="px-5 pb-5 border-t-2 border-black bg-white">
                            {{-- Media Players --}}
                            <div class="mt-4 mb-3">
                                @if($material->material_type === 'video')
                                    @if($material->isYouTubeVideo())
                                        <div class="w-full rounded-2xl overflow-hidden shadow-lg border-2 border-black bg-black mb-4" style="height: 560px; width: 100%;">
                                            <iframe class="w-full h-full" src="{{ $material->getVideoEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                        </div>
                                    @else
                                        <div class="w-full rounded-2xl overflow-hidden shadow-lg border-2 border-black bg-black mb-4" style="height: 560px; width: 100%;">
                                            <video class="w-full h-full object-contain" controls preload="metadata">
                                                <source src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" type="video/mp4">
                                                Browser Anda tidak mendukung pemutar video.
                                            </video>
                                        </div>
                                        <div class="mt-3 flex gap-2">
                                            <a href="{{ $material->file_path ? route('guru.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-5 py-2.5 rounded-2xl bg-black hover:bg-emerald-600 text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black" onclick="event.stopPropagation()">
                                                <i class="fas fa-download text-amber-400"></i> Unduh Berkas Video
                                            </a>
                                        </div>
                                    @endif
                                @elseif($material->material_type === 'image')
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border-2 border-black bg-black flex justify-center p-2">
                                        <img src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" class="max-h-[450px] object-contain w-auto h-auto rounded-xl" alt="{{ $material->title }}">
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ $material->file_path ? route('guru.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-5 py-2.5 rounded-2xl bg-black hover:bg-emerald-600 text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black" onclick="event.stopPropagation()">
                                            <i class="fas fa-download text-amber-400"></i> Unduh Berkas Gambar
                                        </a>
                                    </div>
                                @elseif(($material->material_type === 'pdf' || str_ends_with(strtolower($material->file_name ?? $material->file_path ?? ''), '.pdf') || str_contains(strtolower($material->title ?? ''), '[pdf]')) && strtolower(pathinfo($material->file_name ?? $material->file_path ?? '', PATHINFO_EXTENSION)) === 'pdf')
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border-2 border-black bg-white mb-4" style="height: 650px;">
                                        <iframe src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" class="w-full h-full" frameborder="0"></iframe>
                                    </div>

                                    <div class="p-4 rounded-2xl border-2 border-black flex items-center justify-between gap-4" style="background-color: #fee2e2 !important;">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #dc2626 !important; color: #ffffff !important;">
                                                <i class="fas fa-file-pdf text-2xl text-white"></i>
                                            </div>
                                            <div>
                                                <p class="font-black text-black text-sm">Dokumen PDF Terlampir</p>
                                                <p class="text-xs text-black font-bold">Ukuran Berkas: {{ $material->file_size ? number_format($material->file_size / (1024 * 1024), 2) . ' MB' : 'Tidak diketahui' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '#') }}" target="_blank" class="px-4 py-2.5 rounded-2xl bg-white border-2 border-black text-black hover:bg-amber-300 font-black text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-external-link-alt text-xs"></i> Buka di Tab Baru
                                            </a>
                                            <a href="{{ $material->file_path ? route('guru.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-4 py-2.5 rounded-2xl text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black" style="background-color: #dc2626 !important;" onclick="event.stopPropagation()">
                                                <i class="fas fa-download text-white"></i> Unduh PDF
                                            </a>
                                        </div>
                                    </div>
                                @elseif($material->material_type === 'link')
                                    <div class="p-4 rounded-2xl border-2 border-black flex items-center justify-between gap-4" style="background-color: #f3e8ff !important;">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #9333ea !important; color: #ffffff !important;">
                                                <i class="fas fa-link text-2xl text-white"></i>
                                            </div>
                                            <div>
                                                <p class="font-black text-black text-sm">Tautan Eksternal Pembelajaran</p>
                                                <p class="text-xs text-black font-bold truncate max-w-xs sm:max-w-md">{{ $material->file_url }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ $material->file_url }}" target="_blank" class="px-4 py-2.5 rounded-2xl text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black" style="background-color: #9333ea !important;" onclick="event.stopPropagation()">
                                                <i class="fas fa-external-link-alt text-white"></i> Kunjungi Tautan
                                            </a>
                                        </div>
                                    </div>
                                @elseif($material->material_type === 'interactive')
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border-2 border-black bg-white mb-4" style="height: 600px;">
                                        <iframe :src="expanded ? '{{ $material->file_url }}' : ''" class="w-full h-full" frameborder="0" allowfullscreen="allowfullscreen" allow="geolocation *; microphone *; camera *; midi *; encrypted-media *; autoplay *"></iframe>
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <a href="{{ $material->file_url }}" target="_blank" class="px-5 py-2.5 rounded-2xl bg-black hover:bg-purple-600 text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black" onclick="event.stopPropagation()">
                                            <i class="fas fa-external-link-alt text-amber-400"></i> Buka Game di Tab Baru
                                        </a>
                                    </div>
                                @elseif($material->material_type === 'canva' || str_contains($material->file_url ?? '', 'canva.com'))
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border-2 border-black bg-white mb-4" style="height: 550px;">
                                        <iframe src="{{ str_contains($material->file_url, '/view') ? str_replace('/view', '/view?embed', $material->file_url) : $material->file_url }}" class="w-full h-full" allowfullscreen="allowfullscreen" allow="fullscreen" frameborder="0"></iframe>
                                    </div>
                                @elseif($material->material_type === 'googledocs' || str_contains($material->file_url ?? '', 'docs.google.com'))
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border-2 border-black bg-white mb-4" style="height: 600px;">
                                        <iframe src="{{ str_contains($material->file_url, '/pub') ? $material->file_url : str_replace('/edit', '/preview', $material->file_url) }}" class="w-full h-full" frameborder="0"></iframe>
                                    </div>
                                @elseif($material->material_type === 'audio')
                                    <div class="p-4 rounded-2xl border-2 border-black mb-4 flex flex-col gap-3 shadow-md" style="background-color: #fef3c7 !important;">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black border border-black"><i class="fas fa-volume-up text-lg"></i></div>
                                            <div>
                                                <p class="font-black text-black text-xs uppercase">Pemutar Rekaman Suara / Audio Materi</p>
                                                <p class="text-[11px] text-slate-700 font-bold">Dengarkan materi pembelajaran audio di bawah ini.</p>
                                            </div>
                                        </div>
                                        <audio controls class="w-full rounded-xl">
                                            <source src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" type="audio/mpeg">
                                            Browser Anda tidak mendukung pemutar audio.
                                        </audio>
                                    </div>
                                @elseif($material->material_type === 'embed')
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border-2 border-black bg-white mb-4 p-3">
                                        {!! $material->file_url !!}
                                    </div>
                                @elseif($material->file_path)
                                    <div class="p-4 rounded-2xl border-2 border-black flex items-center justify-between gap-4 mb-4" style="background-color: #e0f2fe !important;">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #0284c7 !important; color: #ffffff !important;">
                                                <i class="fas fa-file-alt text-2xl text-white"></i>
                                            </div>
                                            <div>
                                                <p class="font-black text-black text-sm">Dokumen Terlampir: {{ $material->file_name ?: ($material->title ?: 'File Materi') }}</p>
                                                <p class="text-xs text-black font-bold">Tipe: {{ strtoupper(pathinfo($material->file_name ?? $material->file_path ?? 'DOC', PATHINFO_EXTENSION)) }}{{ $material->file_size ? ' · ' . number_format($material->file_size / 1024, 0) . ' KB' : '' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($material->file_path)
                                            <a href="{{ route('guru.lms.materials.view', $material->id) }}" target="_blank" class="px-4 py-2.5 rounded-2xl bg-white border-2 border-black text-black hover:bg-amber-300 font-black text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-external-link-alt text-xs"></i> Buka / Preview
                                            </a>
                                            <a href="{{ route('guru.lms.materials.download', $material->id) }}" download class="px-4 py-2.5 rounded-2xl text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black" style="background-color: #0284c7 !important;" onclick="event.stopPropagation()">
                                                <i class="fas fa-download text-white"></i> Unduh File
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Text Content / Artikel Ajar Teks --}}
                            @if($material->content)
                            <div class="mt-4 rounded-2xl border-2 border-black bg-slate-50 overflow-hidden shadow-md">
                                <div class="px-5 py-3 border-b-2 border-black flex items-center justify-between flex-wrap gap-2" style="background-color: #0f172a !important; color: #ffffff !important;">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-book-open text-amber-400 text-sm"></i>
                                        <span class="text-xs font-black uppercase text-white tracking-wider">Artikel Pembelajaran Teks (Modul Ajar)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] bg-slate-800 text-amber-300 font-bold px-2 py-0.5 rounded-lg border border-slate-700">
                                            <i class="far fa-clock mr-1"></i> ~{{ max(1, ceil(str_word_count(strip_tags($material->content)) / 180)) }} Menit Baca
                                        </span>
                                        <button type="button" @click.stop="$dispatch('open-article-reader', { title: '{{ addslashes($material->title) }}', content: {{ json_encode($material->content) }} })" class="px-2.5 py-1 bg-amber-400 hover:bg-amber-300 text-black font-black text-[11px] rounded-lg transition border border-black flex items-center gap-1 shadow-xs">
                                            <i class="fas fa-expand text-[10px]"></i> Mode Baca Artikel
                                        </button>
                                    </div>
                                </div>
                                <div class="p-6 bg-white prose prose-sm max-w-none text-slate-900 leading-relaxed font-sans">
                                    {!! formatLmsContent($material->content) !!}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-6 bg-slate-50 rounded-2xl border-2 border-dashed border-black text-center">
                        <p class="text-xs text-black font-bold italic">Belum ada materi di modul ini</p>
                    </div>
                    @endforelse
                </div>

                {{-- Games List --}}
                @if($module->games->count() > 0)
                <div class="px-4 pb-4 space-y-2">
                    <h4 class="text-xs font-black text-black uppercase tracking-wider mb-2 px-1 flex items-center gap-1.5"><i class="fas fa-gamepad text-purple-600"></i> Mini Games Pembelajaran ({{ $module->games->count() }})</h4>
                    @foreach($module->games as $game)
                    <div class="rounded-2xl border-2 border-black p-3.5 flex items-center justify-between shadow-sm" style="background-color: #f3e8ff !important;">
                        <div class="flex items-center gap-3.5">
                            <span class="w-10 h-10 rounded-xl bg-black flex items-center justify-center text-amber-400 shadow-sm border border-black">
                                <i class="fas fa-gamepad text-lg"></i>
                            </span>
                            <div>
                                <p class="font-black text-black text-sm flex items-center gap-2">
                                    {{ $game->title }}
                                    <span class="bg-amber-300 text-black text-[9px] font-black px-2 py-0.5 rounded-md uppercase border border-black">{{ str_replace('_', ' ', $game->game_type) }}</span>
                                </p>
                                <p class="text-[10px] text-black font-black uppercase mt-0.5"><i class="fas fa-star text-amber-500"></i> REWARD: {{ $game->reward_points }} EXP</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(in_array($game->game_type, ['quiz', 'true_false']))
                            <form action="{{ route('guru.lms.games.live.create', $game->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="w-auto px-3.5 h-9 rounded-xl flex items-center justify-center bg-black text-white hover:bg-emerald-600 transition-colors font-black text-xs border-2 border-black shadow-sm gap-2" title="Jalankan Mode Multiplayer Live">
                                    <i class="fas fa-satellite-dish text-amber-400"></i> Host Live Game
                                </button>
                            </form>
                            @endif
                            <button type="button" @click="$dispatch('open-game-player', { id: {{ $game->id }}, type: '{{ $game->game_type }}', title: '{{ addslashes($game->title) }}', data: {{ json_encode($game->game_data) }}, reward: {{ $game->reward_points }}, time_limit: {{ $game->time_limit ?: 'null' }}, lives_count: {{ $game->lives_count ?: 'null' }}, is_preview: true })" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white text-black hover:bg-indigo-400 transition-colors border-2 border-black shadow-sm group" title="Preview Game">
                                <i class="fas fa-play text-xs text-indigo-600 group-hover:text-white"></i>
                            </button>
                            <button type="button" @click="$dispatch('open-edit-game-modal', {{ json_encode($game) }})" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white text-black hover:bg-amber-400 transition-colors border-2 border-black shadow-sm" title="Edit Judul, Soal & Jawaban Game">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <form action="{{ route('guru.lms.games.destroy', $game->id) }}" method="POST" onsubmit="return confirm('Hapus game ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="w-9 h-9 rounded-xl flex items-center justify-center bg-white text-black hover:bg-rose-600 hover:text-white transition-colors border-2 border-black shadow-sm" title="Hapus Game"><i class="fas fa-trash text-xs"></i></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-3xl shadow-md border-2 border-black p-12 text-center">
                <div class="w-20 h-20 bg-amber-100 border-2 border-black rounded-3xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-layer-group text-3xl text-black"></i></div>
                <h3 class="text-lg font-black text-black mb-1">Belum Ada Modul Ajar</h3>
                <p class="text-black font-bold text-xs mb-6 max-w-sm mx-auto">Buat modul pertama untuk menata materi pelajaran digital Anda.</p>
                <a href="{{ route('guru.lms.modules.create', $course->id) }}" class="inline-flex items-center gap-2 bg-black hover:bg-emerald-600 text-white px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider border-2 border-black shadow-md">
                    <i class="fas fa-plus text-amber-400"></i> Buat Modul Baru
                </a>
            </div>
            @endforelse
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: ASSIGNMENTS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'assignments'" class="mt-6 space-y-4 tab-content">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-black text-black text-sm flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center border-2 border-black" style="background-color: #e0f2fe !important; color: #000000 !important;"><i class="fas fa-tasks text-black text-xs"></i></span>
                    PENUGASAN SISWA
                </h3>
                <a href="{{ route('guru.lms.assignments.create', $course->id) }}" class="bg-black hover:bg-blue-600 text-white px-4 py-2.5 rounded-2xl text-xs font-black uppercase transition border-2 border-black shadow-md">
                    <i class="fas fa-plus mr-1 text-amber-400"></i> Buat Tugas Baru
                </a>
            </div>

            {{-- Submission Progress Overview (Collapsible Dropdown) --}}
            @if($course->assignments->count() > 0)
            <div x-data="{ openSummary: false }" class="bg-white rounded-3xl border-2 border-black p-5 shadow-md transition-all">
                <div class="flex items-center justify-between cursor-pointer select-none" @click="openSummary = !openSummary">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-black text-black uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-chart-bar text-black"></i> Ringkasan Pengumpulan Tugas Siswa
                        </span>
                        <span class="text-[10px] font-black text-black bg-amber-300 px-2 py-0.5 rounded-lg border border-black">{{ $course->assignments->count() }} Tugas</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-black text-black bg-slate-100 px-2.5 py-0.5 rounded-lg border border-black hidden sm:inline">{{ $totalStudents }} Siswa Terdaftar</span>
                        <button type="button" class="w-8 h-8 rounded-xl bg-black text-amber-400 flex items-center justify-center font-black border-2 border-black shadow-xs hover:bg-amber-300 hover:text-black transition-all">
                            <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="openSummary ? 'rotate-180' : ''"></i>
                        </button>
                    </div>
                </div>

                <div x-show="openSummary" x-transition.origin.top class="mt-4 pt-4 border-t-2 border-black space-y-2" style="display: none;">
                    @foreach($course->assignments as $asgn)
                    @php $subCount = $asgn->submissions_count ?? 0; $subPercent = $totalStudents > 0 ? round(($subCount / $totalStudents) * 100) : 0; @endphp
                    <div class="flex items-center gap-3 py-2 border-b border-slate-200 last:border-0">
                        <span class="text-xs font-black text-black w-48 truncate" title="{{ $asgn->title }}">{{ $asgn->title }}</span>
                        <div class="flex-1 bg-slate-200 rounded-full h-3 overflow-hidden border border-black">
                            <div class="h-full rounded-full transition-all duration-1000 border-r border-black" style="background-color: #0284c7 !important; width: {{ $subPercent }}%"></div>
                        </div>
                        <span class="text-xs font-black text-black w-24 text-right">{{ $subCount }}/{{ $totalStudents }} Siswa</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @forelse($course->assignments as $assignment)
            @php
                $modColor = match($assignment->module->color ?? 'amber') {
                    'indigo' => '#4f46e5',
                    'emerald' => '#059669',
                    'rose' => '#e11d48',
                    'amber' => '#d97706',
                    'blue' => '#2563eb',
                    'purple' => '#9333ea',
                    'cyan' => '#0891b2',
                    'orange' => '#ea580c',
                    default => '#059669'
                };
            @endphp
            <div class="rounded-3xl shadow-md border-2 border-black p-5 transition-all bg-white text-black relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 border-2 border-black shadow-md text-white" style="background-color: {{ $modColor }} !important;">
                            <i class="fas fa-file-invoice text-xl text-white"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h4 class="font-black text-base leading-tight text-black">{{ $assignment->title }}</h4>
                                @if($assignment->module)
                                    <span class="text-white text-[9px] font-black px-2.5 py-0.5 rounded-lg border border-black uppercase tracking-wider shadow-2xs" style="background-color: {{ $modColor }} !important;">
                                        {{ $assignment->module->getCode() }} · {{ $assignment->module->title }}
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-black text-[9px] font-black px-2 py-0.5 rounded-lg border border-black uppercase tracking-wider">Global</span>
                                @endif
                                @if($assignment->allow_resubmit)
                                <span class="bg-emerald-200 text-black text-[9px] font-black px-2 py-0.5 rounded-lg border border-black uppercase">REVISI DIIZINKAN</span>
                                @endif
                            </div>
                            @if($assignment->description)
                                <p class="text-xs font-bold text-black mt-1 mb-3 line-clamp-2">{{ strip_tags($assignment->description) }}</p>
                            @endif
                            <div class="flex flex-wrap gap-2 text-[10px] font-black uppercase tracking-wider">
                                @if($assignment->deadline)
                                <span class="flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black {{ $assignment->isOverdue() ? 'bg-rose-200 text-black' : 'bg-slate-100 text-black' }}">
                                    <i class="fas fa-clock text-xs text-black"></i> {{ $assignment->deadline->format('d M Y H:i') }}
                                    @if($assignment->isOverdue()) <span class="font-black text-rose-700 ml-1">TELAT</span> @endif
                                </span>
                                @endif
                                <span class="flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-amber-300 text-black"><i class="fas fa-star text-black"></i> SKOR: {{ $assignment->max_score }}</span>
                                <span class="flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-sky-200 text-black"><i class="fas fa-paper-plane text-black"></i> {{ $assignment->submissions_count ?? 0 }} TERKUMPUL</span>
                                <span class="flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-slate-100 text-black"><i class="fas fa-tag text-black"></i> {{ $assignment->getAssignmentTypeLabel() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 ml-4 flex-shrink-0">
                        <a href="{{ route('guru.lms.assignments.edit', $assignment->id) }}" class="w-10 h-10 rounded-2xl flex items-center justify-center transition-all border-2 border-black bg-white text-black hover:bg-amber-300 shadow-sm" title="Edit Tugas"><i class="fas fa-edit text-xs"></i></a>
                        <form action="{{ route('guru.lms.assignments.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-10 h-10 rounded-2xl flex items-center justify-center transition-all border-2 border-black bg-white text-red-600 hover:bg-red-100 shadow-sm" title="Hapus Tugas"><i class="fas fa-trash text-xs"></i></button>
                        </form>
                        <a href="{{ route('guru.lms.assignments.show', $assignment->id) }}" class="px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition shadow-md flex items-center justify-center gap-1.5 bg-black hover:bg-blue-600 text-white border-2 border-black">
                            <i class="fas fa-check-double text-amber-400"></i> KOREKSI
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-3xl shadow-md border-2 border-black p-12 text-center">
                <div class="w-20 h-20 bg-amber-100 border-2 border-black rounded-3xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-tasks text-3xl text-black"></i></div>
                <h3 class="text-lg font-black text-black mb-1">Belum Ada Penugasan</h3>
                <p class="text-black font-bold text-xs">Buat tugas pertama untuk mengevaluasi pemahaman siswa.</p>
            </div>
            @endforelse
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: QUIZZES --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'quizzes'" class="mt-6 space-y-4 tab-content">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-black text-black text-sm flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center border-2 border-black" style="background-color: #f3e8ff !important; color: #000000 !important;"><i class="fas fa-question-circle text-black text-xs"></i></span>
                    EVALUASI & QUIZ INTERAKTIF
                </h3>
                <a href="{{ route('guru.lms.quizzes.create', $course->id) }}" class="bg-black hover:bg-purple-600 text-white px-4 py-2.5 rounded-2xl text-xs font-black uppercase transition border-2 border-black shadow-md">
                    <i class="fas fa-plus mr-1 text-amber-400"></i> Buat Quiz Baru
                </a>
            </div>

            @forelse($course->quizzes as $quiz)
            @php
                $modColorQuiz = match($quiz->module->color ?? 'purple') {
                    'indigo' => '#4f46e5',
                    'emerald' => '#059669',
                    'rose' => '#e11d48',
                    'amber' => '#d97706',
                    'blue' => '#2563eb',
                    'purple' => '#9333ea',
                    'cyan' => '#0891b2',
                    'orange' => '#ea580c',
                    default => '#9333ea'
                };
            @endphp
            <div class="rounded-3xl shadow-md border-2 border-black p-5 transition-all bg-white text-black relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 border-2 border-black shadow-md text-white" style="background-color: {{ $modColorQuiz }} !important;">
                            <i class="fas fa-vial text-xl text-white"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h4 class="font-black text-base leading-tight text-black">{{ $quiz->title }}</h4>
                                @if($quiz->module)
                                    <span class="text-white text-[9px] font-black px-2.5 py-0.5 rounded-lg border border-black uppercase tracking-wider shadow-2xs" style="background-color: {{ $modColorQuiz }} !important;">
                                        {{ $quiz->module->getCode() }} · {{ $quiz->module->title }}
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-black text-[9px] font-black px-2 py-0.5 rounded-lg border border-black uppercase tracking-wider">Global</span>
                                @endif
                                <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border border-black {{ $quiz->is_published ? 'bg-emerald-300 text-black' : 'bg-amber-200 text-black' }}">
                                    {{ $quiz->is_published ? 'PUBLISHED' : 'DRAFT' }}
                                </span>
                            </div>
                            @if($quiz->description)
                                <p class="text-xs font-bold text-black mt-1 mb-3 line-clamp-2">{{ strip_tags($quiz->description) }}</p>
                            @endif
                            <div class="flex flex-wrap gap-2 text-[10px] font-black uppercase tracking-wider">
                                @if($quiz->time_limit)
                                    <span class="flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-amber-300 text-black">
                                        <i class="fas fa-stopwatch text-black"></i> {{ $quiz->time_limit }} MENIT
                                    </span>
                                @endif
                                <span class="flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-emerald-200 text-black">
                                    <i class="fas fa-check-circle text-black"></i> PASSING: {{ $quiz->passing_score }}%
                                </span>
                                <span class="flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-purple-200 text-black">
                                    <i class="fas fa-users text-black"></i> {{ $quiz->attempts_count ?? 0 }} PERCOBAAN
                                </span>
                                @if($quiz->shuffle_questions)
                                    <span class="flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-sky-200 text-black">
                                        <i class="fas fa-random text-black"></i> ACAK SOAL
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 ml-4 flex-shrink-0">
                        <a href="{{ route('guru.lms.quizzes.show', $quiz->id) }}" class="px-4 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition text-center flex justify-center items-center gap-1.5 bg-black hover:bg-purple-600 text-white border-2 border-black shadow-md">
                            <i class="fas fa-cog text-amber-400"></i> Kelola
                        </a>
                        <a href="{{ route('guru.lms.quizzes.results', $quiz->id) }}" class="px-4 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition text-center flex justify-center items-center gap-1.5 bg-slate-100 hover:bg-amber-300 text-black border-2 border-black shadow-sm">
                            <i class="fas fa-chart-bar text-black"></i> Hasil Quiz
                        </a>
                        <form action="{{ route('guru.lms.quizzes.destroy', $quiz->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus quiz ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition text-center flex justify-center items-center gap-1.5 bg-white hover:bg-red-100 text-red-600 border-2 border-red-300 shadow-sm">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-3xl shadow-md border-2 border-black p-12 text-center">
                <div class="w-20 h-20 bg-amber-100 border-2 border-black rounded-3xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-question-circle text-3xl text-black"></i></div>
                <h3 class="text-lg font-black text-black mb-1">Belum Ada Quiz Evaluasi</h3>
                <p class="text-black font-bold text-xs">Buat quiz untuk mengevaluasi pemahaman siswa secara otomatis.</p>
            </div>
            @endforelse
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: ANNOUNCEMENTS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'announcements'" class="mt-6 space-y-4 tab-content">
            <div x-data="{ showForm: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-black text-black text-sm flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl flex items-center justify-center border-2 border-black" style="background-color: #fef08a !important; color: #000000 !important;"><i class="fas fa-bullhorn text-black text-xs"></i></span>
                        PAPAN PENGUMUMAN KELAS
                    </h3>
                    <button @click="showForm = !showForm" class="bg-black hover:bg-amber-400 hover:text-black text-white border-2 border-black px-4 py-2.5 rounded-2xl text-xs font-black uppercase transition shadow-md">
                        <i class="fas fa-plus mr-1 text-amber-400"></i> Buat Pengumuman
                    </button>
                </div>

                <form x-show="showForm" x-transition action="{{ route('guru.lms.announcements.store', $course->id) }}" method="POST" class="bg-white rounded-3xl shadow-md border-2 border-black p-6 mb-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Judul Pengumuman</label>
                            <input type="text" name="title" required placeholder="Contoh: Jadwal Ujian Tengah Semester..." class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Isi Pesan Pengumuman</label>
                            <textarea name="content" required placeholder="Tuliskan detail pengumuman untuk siswa..." rows="4" class="w-full border-2 border-black rounded-2xl p-4 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"></textarea>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <label class="flex items-center gap-2 text-xs font-black text-black cursor-pointer">
                                <input type="checkbox" name="is_pinned" value="1" class="w-5 h-5 rounded border-2 border-black text-black focus:ring-0">
                                <span>Sematkan di Atas (Pinned)</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" @click="showForm = false" class="px-4 py-2 rounded-xl text-xs font-black text-black hover:bg-slate-100 uppercase">Batal</button>
                                <button type="submit" class="bg-black text-white hover:bg-emerald-600 px-6 py-2.5 rounded-2xl font-black text-xs uppercase tracking-wider border-2 border-black shadow-md">PUBLISH</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @forelse($course->announcements as $announcement)
            <div class="bg-white rounded-3xl shadow-md border-2 border-black p-5 relative overflow-hidden">
                @if($announcement->is_pinned)
                <div class="absolute top-0 right-0">
                    <div class="bg-amber-300 border-b-2 border-l-2 border-black text-black text-[9px] font-black px-3 py-1 rounded-bl-2xl uppercase tracking-wider shadow-sm"><i class="fas fa-thumbtack mr-1"></i>PINNED</div>
                </div>
                @endif
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-2xl bg-amber-300 border border-black flex items-center justify-center shadow-sm">
                                <i class="fas fa-bullhorn text-black text-xs"></i>
                            </div>
                            <h4 class="font-black text-black text-base">{{ $announcement->title }}</h4>
                        </div>
                        <div class="prose prose-sm text-black font-extrabold max-w-none ml-12 p-3 rounded-2xl bg-slate-50 border border-black">{!! nl2br(e($announcement->content)) !!}</div>
                        <div class="flex gap-4 mt-3 ml-12 text-[10px] text-black font-black uppercase tracking-wider">
                            <span class="flex items-center gap-1.5"><i class="fas fa-user-circle"></i> {{ $announcement->author->name ?? 'Sistem' }}</span>
                            <span class="flex items-center gap-1.5"><i class="fas fa-clock"></i> {{ $announcement->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <form action="{{ route('guru.lms.announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini?')" class="ml-4">
                        @csrf @method('DELETE')
                        <button class="w-9 h-9 rounded-2xl bg-slate-100 text-black hover:bg-rose-600 hover:text-white transition-all border-2 border-black flex items-center justify-center shadow-sm" title="Hapus Pengumuman">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-3xl shadow-md border-2 border-black p-12 text-center">
                <div class="w-20 h-20 bg-amber-100 border-2 border-black rounded-3xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-bullhorn text-3xl text-black"></i></div>
                <h3 class="text-lg font-black text-black mb-1">Belum Ada Pengumuman</h3>
                <p class="text-black font-bold text-xs">Buat pengumuman untuk memberitahu siswa tentang informasi penting.</p>
            </div>
            @endforelse
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: DISCUSSIONS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'discussions'" class="mt-6 tab-content">
            <div class="bg-white rounded-3xl shadow-md border-2 border-black p-8 text-center max-w-2xl mx-auto">
                <div class="w-20 h-20 bg-amber-100 text-black border-2 border-black rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <i class="fas fa-comments text-3xl"></i>
                </div>
                <h3 class="text-xl font-black text-black mb-2">Forum Diskusi Interaktif Kelas</h3>
                <p class="text-black font-bold text-xs mb-8 px-4">Ruang tanya jawab interaktif antara siswa dan guru untuk membahas materi pembelajaran.</p>
                
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-slate-100 p-4 rounded-2xl border-2 border-black shadow-sm">
                        <div class="text-3xl font-black text-black leading-none">{{ $course->discussions_count ?? 0 }}</div>
                        <div class="text-[10px] font-black text-black mt-2 uppercase tracking-widest">Topik Diskusi Aktif</div>
                    </div>
                    <div class="bg-slate-100 p-4 rounded-2xl border-2 border-black shadow-sm">
                        <div class="text-3xl font-black text-black leading-none">0</div>
                        <div class="text-[10px] font-black text-black mt-2 uppercase tracking-widest">Diskusi Belum Terjawab</div>
                    </div>
                </div>

                <a href="{{ route('guru.lms.discussions.index', $course->id) }}" class="inline-flex items-center gap-2 bg-black text-white hover:bg-amber-400 hover:text-black border-2 border-black px-8 py-3.5 rounded-2xl font-black transition shadow-md uppercase tracking-wider text-xs">
                    Buka Portal Forum Diskusi <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: COURSE MASTER GROUPS (KELOMPOK BELAJAR) --}}
        {{-- ═══════════════════════════════════════════════ --}}
        @php
            $courseMasterGroups = $course->courseGroups ?? collect();
            $groupedStudentIdsInCourse = $courseMasterGroups->flatMap(function($grp) {
                return $grp->members->pluck('id')->push($grp->leader_id);
            })->unique()->filter()->toArray();

            $availableStudentsInCourse = $allEnrolledStudents->reject(fn($s) => in_array($s->id, $groupedStudentIdsInCourse))->values();
        @endphp
        <div x-show="tab === 'groups'" class="mt-6 space-y-6 tab-content" x-data="{ showAddGroup: false, showAutoGroup: false, showImportExcel: false, selectedClassFilter: '', manualClassFilter: '' }">
            {{-- Header Card --}}
            <div class="bg-white rounded-3xl shadow-md border-2 border-black p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-2 border-gray-100 pb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-purple-600 text-white rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-black uppercase tracking-wider">Master Kelompok Belajar Kursus</h3>
                            <p class="text-xs font-bold text-gray-500">Bagi kelompok siswa satu kali di sini untuk otomatis digunakan pada seluruh tugas kelompok kursus ini.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($availableStudentsInCourse->isNotEmpty())
                        <button type="button" @click="showAddGroup = !showAddGroup; showAutoGroup = false; showImportExcel = false"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black bg-purple-600 text-white hover:bg-purple-700 border-2 border-black transition shadow-sm">
                            <i class="fas fa-plus"></i> <span x-text="showAddGroup ? 'Batal' : 'Tambah Kelompok Manual'"></span>
                        </button>
                        <button type="button" @click="showAutoGroup = !showAutoGroup; showAddGroup = false; showImportExcel = false"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black bg-amber-400 text-black hover:bg-amber-500 border-2 border-black transition shadow-sm">
                            <i class="fas fa-magic"></i> <span x-text="showAutoGroup ? 'Batal' : 'Bagi Otomatis'"></span>
                        </button>
                        @endif
                        <button type="button" @click="showImportExcel = !showImportExcel; showAddGroup = false; showAutoGroup = false"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black bg-emerald-600 text-white hover:bg-emerald-700 border-2 border-black transition shadow-sm">
                            <i class="fas fa-file-excel"></i> <span x-text="showImportExcel ? 'Batal' : 'Import Excel'"></span>
                        </button>
                    </div>
                </div>

                {{-- Baris Filter per Kelas --}}
                @if(isset($classrooms) && $classrooms->count() > 1)
                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-xs font-black text-gray-800">
                        <i class="fas fa-filter text-purple-600"></i> Filter Rombel / Kelas:
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" @click="selectedClassFilter = ''; manualClassFilter = ''"
                                :class="selectedClassFilter === '' ? 'bg-purple-600 text-white border-black shadow-sm' : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200'"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold border transition">
                            Semua Kelas ({{ $allEnrolledStudents->count() }})
                        </button>
                        @foreach($classrooms as $c)
                        @php
                            $countInClass = $allEnrolledStudents->filter(fn($s) => $s->classroom_id == $c->id || strtolower($s->classroom_name ?? '') === strtolower($c->name))->count();
                        @endphp
                        <button type="button" @click="selectedClassFilter = '{{ $c->id }}'; manualClassFilter = '{{ $c->id }}'"
                                :class="selectedClassFilter == '{{ $c->id }}' ? 'bg-purple-600 text-white border-black shadow-sm' : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200'"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold border transition">
                            {{ $c->name }} ({{ $countInClass }})
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Status Stats Bar --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5">
                    <div class="bg-slate-50 border-2 border-black rounded-2xl p-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 border border-black flex items-center justify-center font-black">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-500">Total Siswa Terdaftar</div>
                            <div class="text-lg font-black text-black">{{ $allEnrolledStudents->count() }} Orang</div>
                        </div>
                    </div>
                    <div class="bg-emerald-50 border-2 border-black rounded-2xl p-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-200 text-emerald-800 border border-black flex items-center justify-center font-black">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-500">Sudah Masuk Kelompok</div>
                            <div class="text-lg font-black text-emerald-800">{{ count($groupedStudentIdsInCourse) }} Orang</div>
                        </div>
                    </div>
                    <div class="bg-amber-50 border-2 border-black rounded-2xl p-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-200 text-amber-900 border border-black flex items-center justify-center font-black">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-500">Belum Punya Kelompok</div>
                            <div class="text-lg font-black text-amber-900">{{ $availableStudentsInCourse->count() }} Orang</div>
                        </div>
                    </div>
                </div>

                {{-- Form Tambah Kelompok Manual --}}
                <div x-show="showAddGroup" x-cloak class="mt-6 p-5 rounded-2xl border-2 border-purple-400 bg-purple-50 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black text-purple-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-user-plus"></i> Tambah Master Kelompok Kursus
                        </h4>
                        @if(isset($classrooms) && $classrooms->count() > 1)
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-purple-800">Filter Kelas Form:</span>
                            <select x-model="manualClassFilter" class="text-xs font-bold border border-purple-300 rounded-lg px-2 py-1 bg-white focus:outline-none">
                                <option value="">Semua Kelas</option>
                                @foreach($classrooms as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                    <form action="{{ route('guru.lms.groups.store', $course->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Nama Kelompok <span class="text-rose-600">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Kelompok 1" value="Kelompok {{ $courseMasterGroups->count() + 1 }}"
                                       class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Pilih Ketua Kelompok <span class="text-rose-600">* (Koordinator Kelompok)</span></label>
                                <select name="leader_id" required class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                                    <option value="">— Pilih Ketua (Tersedia: {{ $availableStudentsInCourse->count() }} Siswa) —</option>
                                    @forelse($availableStudentsInCourse as $std)
                                    <option value="{{ $std->id }}" x-show="!manualClassFilter || '{{ $std->classroom_id }}' == manualClassFilter">
                                        [{{ $std->classroom_name ?? 'Kelas' }}] {{ $std->user->name ?? $std->full_name }} (NISN: {{ $std->nisn ?? '-' }})
                                    </option>
                                    @empty
                                    <option value="" disabled>Semua siswa sudah terdaftar di kelompok lain</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-black text-gray-800">Pilih Anggota Kelompok (Centang Siswa):</label>
                                <span class="text-[11px] font-bold text-purple-700">Tersedia: {{ $availableStudentsInCourse->count() }} Orang dari {{ $allEnrolledStudents->count() }} Siswa</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-64 overflow-y-auto p-3 bg-white border-2 border-black rounded-xl">
                                @forelse($availableStudentsInCourse as $std)
                                <label x-show="!manualClassFilter || '{{ $std->classroom_id }}' == manualClassFilter"
                                       class="flex items-center gap-2 text-xs font-bold text-gray-700 hover:bg-purple-100 p-1.5 rounded-lg cursor-pointer transition">
                                    <input type="checkbox" name="member_ids[]" value="{{ $std->id }}" class="rounded text-purple-600 focus:ring-0">
                                    <span class="bg-blue-100 text-blue-800 text-[10px] px-1.5 py-0.5 rounded font-black border border-blue-200 shrink-0">{{ $std->classroom_name ?? 'Kelas' }}</span>
                                    <span class="truncate">{{ $std->user->name ?? $std->full_name }}</span>
                                </label>
                                @empty
                                <div class="col-span-full py-4 text-center text-xs text-emerald-800 font-bold bg-emerald-100 rounded-lg border border-emerald-300">
                                    <i class="fas fa-check-circle mr-1 text-emerald-600"></i> Seluruh {{ $allEnrolledStudents->count() }} siswa sudah terbagi ke dalam kelompok.
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showAddGroup = false" class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-200 text-gray-700 hover:bg-gray-300">Batal</button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-black bg-purple-600 text-white hover:bg-purple-700 border-2 border-black shadow-sm">Simpan Master Kelompok</button>
                        </div>
                    </form>
                </div>

                {{-- Form Bagi Otomatis --}}
                <div x-show="showAutoGroup" x-cloak class="mt-6 p-5 rounded-2xl border-2 border-amber-400 bg-amber-50 space-y-4">
                    <h4 class="text-xs font-black text-amber-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-magic"></i> Bagi Siswa Menjadi N Kelompok
                    </h4>
                    <form action="{{ route('guru.lms.groups.autoGenerate', $course->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @if(isset($classrooms) && $classrooms->count() > 1)
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Target Rombel / Kelas</label>
                                <select name="classroom_id" class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-amber-500 outline-none bg-white">
                                    <option value="">— Semua Kelas (Seluruh Siswa) —</option>
                                    @foreach($classrooms as $c)
                                    <option value="{{ $c->id }}">Spesifik Kelas: {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Jumlah Kelompok <span class="text-rose-600">*</span></label>
                                <input type="number" name="group_count" min="1" max="30" value="4" required
                                       class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-amber-500 outline-none bg-white">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-5 py-2.5 rounded-xl text-xs font-black bg-amber-400 text-black hover:bg-amber-500 border-2 border-black shadow-sm">
                                    <i class="fas fa-random mr-1"></i> Acak & Bentuk Kelompok
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Form Import Kelompok dari Excel --}}
                <div x-show="showImportExcel" x-cloak class="mt-6 p-5 rounded-2xl border-2 border-emerald-400 bg-emerald-50 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b-2 border-emerald-200 pb-3">
                        <h4 class="text-xs font-black text-emerald-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-file-excel text-base text-emerald-700"></i> Import Master Kelompok dari File Excel
                        </h4>
                        <a href="{{ route('guru.lms.groups.download-template', $course->id) }}"
                           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-black bg-white text-emerald-800 hover:bg-emerald-100 border-2 border-emerald-400 shadow-sm transition">
                            <i class="fas fa-download text-emerald-600"></i> Download Template Excel Kelompok
                        </a>
                    </div>

                    <p class="text-xs font-bold text-emerald-800 leading-relaxed">
                        Unduh file template Excel di atas (file ini otomatis terisi draf data seluruh {{ $allEnrolledStudents->count() }} siswa terdaftar kursus ini), kemudian sesuaikan kolom <code class="bg-emerald-100 px-1.5 py-0.5 rounded font-black text-emerald-950">nama_kelompok</code> dan <code class="bg-emerald-100 px-1.5 py-0.5 rounded font-black text-emerald-950">peran</code> (Ketua/Anggota), lalu unggah kembali file tersebut di bawah ini.
                    </p>

                    <form action="{{ route('guru.lms.groups.import-excel', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Pilih File Excel (.xlsx / .xls / .csv) <span class="text-rose-600">*</span></label>
                                <input type="file" name="import_file" required accept=".xlsx,.xls,.csv"
                                       class="w-full border-2 border-black rounded-xl px-3 py-2 text-xs font-bold text-gray-900 bg-white cursor-pointer">
                            </div>
                            <div class="flex items-center pt-4">
                                <label class="flex items-center gap-2 text-xs font-bold text-gray-800 cursor-pointer">
                                    <input type="checkbox" name="replace_existing" value="1" class="rounded text-emerald-600 focus:ring-0">
                                    <span>Hapus & gantikan kelompok lama yang sudah ada di kursus ini</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-emerald-200">
                            <button type="button" @click="showImportExcel = false" class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-200 text-gray-700 hover:bg-gray-300">Batal</button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-black bg-emerald-600 text-white hover:bg-emerald-700 border-2 border-black shadow-sm">
                                <i class="fas fa-upload mr-1"></i> Unggah & Simpan Kelompok
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Cards Daftar Kelompok Kursus --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($courseMasterGroups as $grp)
                <div class="p-5 rounded-2xl border-2 border-black bg-white hover:border-purple-600 transition-all shadow-md flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-black text-purple-900 uppercase tracking-wider bg-purple-100 px-3 py-1 rounded-xl border border-purple-300">
                                {{ $grp->name }}
                            </span>
                            <form action="{{ route('guru.lms.groups.destroy', [$course->id, $grp->id]) }}" method="POST" onsubmit="return confirm('Hapus kelompok {{ $grp->name }} dari kursus?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-rose-600 text-xs p-1" title="Hapus Kelompok">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-1.5 font-black text-gray-900">
                                <span class="text-amber-500">👑 Ketua:</span>
                                @php
                                    $leaderClassroom = $grp->leader?->studentClasses?->first()?->classroom?->name ?? $grp->leader?->classroom_name ?? null;
                                @endphp
                                @if($leaderClassroom)
                                <span class="bg-purple-100 text-purple-800 text-[10px] px-1.5 py-0.5 rounded font-black border border-purple-200 shrink-0">{{ $leaderClassroom }}</span>
                                @endif
                                <span class="truncate">{{ $grp->leader?->user?->name ?? $grp->leader?->full_name ?? '-' }}</span>
                            </div>
                            <div class="text-gray-600 font-bold text-[11px] pt-1">
                                <span class="block mb-1">Anggota ({{ $grp->members->count() }} orang):</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($grp->members as $mem)
                                    @php
                                        $memClassroom = $mem->studentClasses?->first()?->classroom?->name ?? $mem->classroom_name ?? null;
                                    @endphp
                                    <span class="bg-gray-100 border border-gray-300 text-gray-800 text-[10px] font-bold px-2 py-0.5 rounded-md flex items-center gap-1">
                                        @if($memClassroom)
                                        <span class="text-purple-700 font-black">[{{ $memClassroom }}]</span>
                                        @endif
                                        <span>{{ $mem->user->name ?? $mem->full_name }}</span>
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-12 text-center bg-white rounded-3xl border-2 border-black shadow-md p-8">
                    <div class="w-16 h-16 bg-purple-100 text-purple-700 border-2 border-black rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users-slash text-2xl"></i>
                    </div>
                    <h4 class="text-base font-black text-black">Belum Ada Master Kelompok Kursus</h4>
                    <p class="text-xs font-bold text-gray-500 max-w-md mx-auto mt-1 mb-6">
                        Buat kelompok manual, per kelas, klik "Bagi Otomatis", atau gunakan fitur "Import Excel" untuk mengunggah draf kelompok.
                    </p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <button type="button" @click="showAutoGroup = true; showAddGroup = false; showImportExcel = false"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black bg-amber-400 text-black hover:bg-amber-500 border-2 border-black transition shadow-sm">
                            <i class="fas fa-magic"></i> Bagi {{ $allEnrolledStudents->count() }} Siswa Secara Otomatis
                        </button>
                        <button type="button" @click="showImportExcel = true; showAutoGroup = false; showAddGroup = false"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black bg-emerald-600 text-white hover:bg-emerald-700 border-2 border-black transition shadow-sm">
                            <i class="fas fa-file-excel"></i> Import dari Excel
                        </button>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: ANALYTICS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'analytics'" class="mt-6 space-y-6 tab-content">
            {{-- Summary Stat Cards --}}
            @php
                $totalSubmissions = $course->assignments->sum(function($a) { return $a->submissions_count ?? 0; });
                $totalQuizAttempts = $course->quizzes->sum(function($q) { return $q->attempts_count ?? 0; });
                $avgProgress = $totalStudents > 0 && $course->materials_count > 0
                    ? round(collect($materialsData)->sum('count') / ($totalStudents * max(count($materialsData), 1)) * 100)
                    : 0;
            @endphp
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm" style="background-color: #fef08a !important;">
                            <i class="fas fa-users text-black text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-black leading-none">{{ $totalStudents }}</div>
                            <div class="text-[10px] font-black text-black uppercase tracking-widest mt-1">Siswa Terdaftar</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md" style="background-color: #d1fae5 !important;">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-white border-2 border-black flex items-center justify-center shadow-sm">
                            <i class="fas fa-chart-line text-black text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-black leading-none">{{ min($avgProgress, 100) }}%</div>
                            <div class="text-[10px] font-black text-black uppercase tracking-widest mt-1">Rata-rata Progress</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md" style="background-color: #e0f2fe !important;">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-white border-2 border-black flex items-center justify-center shadow-sm">
                            <i class="fas fa-paper-plane text-black text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-black leading-none">{{ $totalSubmissions }}</div>
                            <div class="text-[10px] font-black text-black uppercase tracking-widest mt-1">Tugas Terkumpul</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md" style="background-color: #f3e8ff !important;">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-white border-2 border-black flex items-center justify-center shadow-sm">
                            <i class="fas fa-vial text-black text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-black leading-none">{{ $totalQuizAttempts }}</div>
                            <div class="text-[10px] font-black text-black uppercase tracking-widest mt-1">Quiz Dikerjakan</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart 1: Progress Membaca Materi -->
                <div class="bg-white rounded-3xl border-2 border-black p-6 shadow-md">
                    <h4 class="text-xs font-black text-black uppercase tracking-wider mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-amber-300 border border-black flex items-center justify-center"><i class="fas fa-book-open text-black text-xs"></i></span>
                        PROGRES MEMBACA MATERI BELAJAR SISWA
                    </h4>
                    <div class="h-80 relative bg-slate-50 border-2 border-black rounded-2xl p-4">
                        @if(empty($materialsData))
                        <div class="absolute inset-0 flex items-center justify-center text-black font-bold text-xs bg-slate-100 rounded-xl">
                            Belum ada data progres membaca materi.
                        </div>
                        @else
                        <canvas id="materialsChart"></canvas>
                        @endif
                    </div>
                </div>

                <!-- Chart 2: Distribusi Nilai Kuis -->
                <div class="bg-white rounded-3xl border-2 border-black p-6 shadow-md">
                    <h4 class="text-xs font-black text-black uppercase tracking-wider mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-purple-200 border border-black flex items-center justify-center"><i class="fas fa-poll text-black text-xs"></i></span>
                        DISTRIBUSI PEROLEHAN NILAI KUIS
                    </h4>
                    <div class="h-80 relative bg-slate-50 border-2 border-black rounded-2xl p-4">
                        @if(array_sum(array_values($quizScores)) === 0)
                        <div class="absolute inset-0 flex items-center justify-center text-black font-bold text-xs bg-slate-100 rounded-xl">
                            Belum ada kuis yang dikerjakan oleh siswa.
                        </div>
                        @else
                        <canvas id="quizzesChart"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: INFO / CLASS DATA --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'info'" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6 tab-content">
            <div class="md:col-span-2 space-y-6">
                {{-- Enrolled Classes --}}
                <div class="bg-white rounded-3xl shadow-md border-2 border-black p-6">
                    <h4 class="text-xs font-black text-black uppercase tracking-wider mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-amber-300 border border-black flex items-center justify-center"><i class="fas fa-user-friends text-black text-xs"></i></span>
                        Daftar Kelas Terdaftar ({{ $course->lmsClasses->count() }})
                    </h4>
                    
                    <div class="space-y-3">
                        @forelse($course->lmsClasses as $lmsClass)
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border-2 border-black shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-11 h-11 rounded-2xl bg-black text-amber-400 font-black text-base flex items-center justify-center border border-black">
                                    {{ substr($lmsClass->classroom->class_name ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-black text-black text-sm">{{ $lmsClass->classroom->class_name ?? 'N/A' }}</span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="px-2 py-0.5 rounded-lg bg-emerald-300 text-black text-[9px] font-black uppercase border border-black">{{ $lmsClass->getStatusLabel() }}</span>
                                        <span>·</span>
                                        <span class="text-xs text-black font-bold">Semester {{ $course->semester->semester_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <div class="text-xl font-black text-black leading-none">{{ $lmsClass->getEnrolledCount() }}</div>
                                    <div class="text-[9px] font-black text-black mt-1 uppercase">Siswa Aktif</div>
                                </div>
                                <form action="{{ route('guru.lms.students.enroll', $course->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="classroom_id" value="{{ $lmsClass->classroom_id }}">
                                    <button type="submit" class="w-9 h-9 rounded-xl bg-amber-300 border border-black text-black hover:bg-black hover:text-white transition-all flex items-center justify-center shadow-sm" title="Sinkronisasi Pendaftaran Siswa">
                                        <i class="fas fa-sync-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        @if($course->classroom)
                        <div class="flex items-center p-4 bg-slate-50 rounded-2xl border-2 border-black">
                            <span class="font-black text-black">{{ $course->classroom->class_name }}</span>
                        </div>
                        @else
                        <div class="text-center py-10 bg-slate-50 rounded-2xl border-2 border-dashed border-black">
                            <i class="fas fa-users-slash text-3xl text-black mb-3"></i>
                            <p class="text-black font-bold text-xs">Belum ada kelas yang didaftarkan.</p>
                        </div>
                        @endif
                        @endforelse
                    </div>

                    <div class="mt-6 pt-6 border-t-2 border-black flex justify-end">
                        <a href="{{ route('guru.lms.students.index', $course->id) }}" class="text-xs font-black text-black uppercase tracking-wider flex items-center gap-2 bg-amber-300 px-4 py-2 rounded-xl border border-black shadow-sm hover:bg-black hover:text-white transition">
                            Kelola Pendaftaran Siswa <i class="fas fa-plus-circle"></i>
                        </a>
                    </div>
                </div>

                {{-- Description --}}
                @if($course->description)
                <div class="bg-white rounded-3xl shadow-md border-2 border-black p-6">
                    <h4 class="text-xs font-black text-black uppercase tracking-wider mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-amber-300 border border-black flex items-center justify-center"><i class="fas fa-info-circle text-black text-xs"></i></span>
                        Deskripsi & Silabus Course
                    </h4>
                    <div class="prose prose-sm text-black font-bold max-w-none p-4 bg-slate-50 rounded-2xl border border-black">{!! nl2br(e($course->description)) !!}</div>
                </div>
                @endif
            </div>

            {{-- Access Config Sidebar --}}
            <div class="space-y-6">
                <div class="rounded-3xl p-6 text-white shadow-xl border-2 border-black relative overflow-hidden" style="background-color: #090d16 !important;">
                    <h4 class="font-black text-xs uppercase tracking-wider mb-6 border-b-2 border-slate-800 pb-3 flex items-center gap-2 text-amber-400">
                        <i class="fas fa-cog"></i> Konfigurasi Ruang Ajar
                    </h4>
                    
                    <div class="space-y-4 relative z-10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-300 uppercase tracking-wider">Status Publish</span>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border border-black" style="background-color: #a7f3d0 !important; color: #000000 !important;">{{ $course->is_published ? 'LIVE' : 'DRAFT' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-300 uppercase tracking-wider">Tingkat Kelas</span>
                            <span class="bg-slate-800 text-amber-300 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border border-slate-700">KELAS {{ $courseClassroom->grade_level ?? ($firstLmsClassroom->grade_level ?? '?') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-300 uppercase tracking-wider">Kode Akses</span>
                            <code class="bg-slate-800 text-amber-300 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border border-slate-700">{{ $course->code ?: '-' }}</code>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-300 uppercase tracking-wider">Enrollment</span>
                            <span class="bg-slate-800 text-amber-300 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border border-slate-700">AUTO / SYNC</span>
                        </div>
                    </div>

                    <a href="{{ route('guru.lms.edit', $course->id) }}" class="block w-full mt-8 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-2xl py-3 text-xs font-black uppercase tracking-wider transition-all text-center shadow-md">
                        Pengaturan Course Lanjut
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- GAME BUILDER MODAL --}}
{{-- ═══════════════════════════════════════════════ --}}
<div x-data="{ 
        open: false, 
        isEdit: false,
        gameId: null,
        moduleId: '',
        title: '',
        rewardPoints: 50,
        timeLimit: '',
        livesCount: '',
        gameType: 'quiz',
        pairs: [{term: '', definition: ''}],
        wheelItems: ['Hadiah 1', 'Hadiah 2', 'Zonk', 'Jackpot'],
        quizQuestions: [{ question: '', options: ['', '', '', ''], answer: 0 }],
        tfStatements: [{ statement: '', is_true: true }],
        guessWords: [{ word: '', hint: '' }],
        scrambleWords: [{ word: '', hint: '' }],
        sequenceGroups: [{ title: 'Kelompok 1', items: [{ item: '' }, { item: '' }] }],
        hotspots: [{ x: 50, y: 50, label: '' }],
        chemEquations: [{ equation: '', answers: '' }],
        mathConfig: { operation: 'mixed', difficulty: 'easy' },
        addPair() { this.pairs.push({term: '', definition: ''}) },
        removePair(index) { this.pairs.splice(index, 1) },
        addWheelItem() { this.wheelItems.push('') },
        removeWheelItem(index) { this.wheelItems.splice(index, 1) },
        addQuizQuestion() { this.quizQuestions.push({ question: '', options: ['', '', '', ''], answer: 0 }) },
        removeQuizQuestion(index) { this.quizQuestions.splice(index, 1) },
        addTfStatement() { this.tfStatements.push({ statement: '', is_true: true }) },
        removeTfStatement(index) { this.tfStatements.splice(index, 1) },
        addGuessWord() { this.guessWords.push({ word: '', hint: '' }) },
        removeGuessWord(index) { this.guessWords.splice(index, 1) },
        addScrambleWord() { this.scrambleWords.push({ word: '', hint: '' }) },
        removeScrambleWord(index) { this.scrambleWords.splice(index, 1) },
        addSequenceGroup() { this.sequenceGroups.push({ title: 'Kelompok ' + (this.sequenceGroups.length + 1), items: [{ item: '' }, { item: '' }] }) },
        removeSequenceGroup(gIndex) { if (this.sequenceGroups.length > 1) this.sequenceGroups.splice(gIndex, 1) },
        addSequenceItem(gIndex) { if (this.sequenceGroups[gIndex]) this.sequenceGroups[gIndex].items.push({ item: '' }) },
        removeSequenceItem(gIndex, iIndex) { if (this.sequenceGroups[gIndex]) this.sequenceGroups[gIndex].items.splice(iIndex, 1) },
        addHotspot() { this.hotspots.push({ x: 50, y: 50, label: '' }) },
        removeHotspot(index) { this.hotspots.splice(index, 1) },
        addChemEquation() { this.chemEquations.push({ equation: '', answers: '' }) },
        removeChemEquation(index) { this.chemEquations.splice(index, 1) },
        getGameData() {
            if (this.gameType === 'spin_wheel') return JSON.stringify({ items: this.wheelItems.filter(i => i.trim() !== '') });
            if (this.gameType === 'quiz') return JSON.stringify({ questions: this.quizQuestions.filter(q => q.question.trim() !== '') });
            if (this.gameType === 'true_false') return JSON.stringify({ statements: this.tfStatements.filter(s => s.statement.trim() !== '') });
            if (this.gameType === 'word_guess') return JSON.stringify({ words: this.guessWords.filter(w => w.word.trim() !== '') });
            if (this.gameType === 'scramble') return JSON.stringify({ words: this.scrambleWords.filter(w => w.word.trim() !== '') });
            if (this.gameType === 'sequence') {
                let validGroups = this.sequenceGroups.map(g => ({
                    title: g.title ? g.title.trim() : '',
                    items: (g.items || []).filter(i => i.item && i.item.trim() !== '')
                })).filter(g => g.items.length > 0);
                let firstItems = validGroups.length > 0 ? validGroups[0].items : [];
                return JSON.stringify({ groups: validGroups, items: firstItems });
            }
            if (this.gameType === 'image_hotspot') return JSON.stringify({ hotspots: this.hotspots.filter(h => h.label.trim() !== '') });
            if (this.gameType === 'chem_balancer') return JSON.stringify({ equations: this.chemEquations.filter(e => e.equation.trim() !== '') });
            if (this.gameType === 'math_ninja') return JSON.stringify({ config: this.mathConfig });
            return JSON.stringify({ pairs: this.pairs.filter(p => p.term.trim() !== '' && p.definition.trim() !== '') });
        },
        resetForm() {
            this.isEdit = false;
            this.gameId = null;
            this.moduleId = '';
            this.title = '';
            this.rewardPoints = 50;
            this.timeLimit = '';
            this.livesCount = '';
            this.gameType = 'quiz';
            this.pairs = [{term: '', definition: ''}];
            this.wheelItems = ['Hadiah 1', 'Hadiah 2', 'Zonk', 'Jackpot'];
            this.quizQuestions = [{ question: '', options: ['', '', '', ''], answer: 0 }];
            this.tfStatements = [{ statement: '', is_true: true }];
            this.guessWords = [{ word: '', hint: '' }];
            this.scrambleWords = [{ word: '', hint: '' }];
            this.sequenceGroups = [{ title: 'Kelompok 1', items: [{ item: '' }, { item: '' }] }];
            this.hotspots = [{ x: 50, y: 50, label: '' }];
            this.chemEquations = [{ equation: '', answers: '' }];
            this.mathConfig = { operation: 'mixed', difficulty: 'easy' };
            this.open = true;
        },
        loadGame(game) {
            this.isEdit = true;
            this.gameId = game.id;
            this.moduleId = game.module_id || '';
            this.title = game.title || '';
            this.rewardPoints = game.reward_points || 50;
            this.timeLimit = game.time_limit || '';
            this.livesCount = game.lives_count || '';
            this.gameType = game.game_type || 'quiz';

            let gd = game.game_data || {};
            if (typeof gd === 'string') {
                try { gd = JSON.parse(gd); } catch(e) { gd = {}; }
            }

            if (this.gameType === 'quiz') {
                this.quizQuestions = gd.questions && gd.questions.length > 0 
                    ? JSON.parse(JSON.stringify(gd.questions))
                    : [{ question: '', options: ['', '', '', ''], answer: 0 }];
            } else if (this.gameType === 'true_false') {
                this.tfStatements = gd.statements && gd.statements.length > 0 
                    ? JSON.parse(JSON.stringify(gd.statements))
                    : [{ statement: '', is_true: true }];
            } else if (this.gameType === 'spin_wheel') {
                this.wheelItems = gd.items && gd.items.length > 0 
                    ? JSON.parse(JSON.stringify(gd.items))
                    : ['Hadiah 1', 'Hadiah 2'];
            } else if (this.gameType === 'flashcard' || this.gameType === 'match') {
                this.pairs = gd.pairs && gd.pairs.length > 0 
                    ? JSON.parse(JSON.stringify(gd.pairs))
                    : [{term: '', definition: ''}];
            } else if (this.gameType === 'word_guess') {
                this.guessWords = gd.words && gd.words.length > 0 
                    ? JSON.parse(JSON.stringify(gd.words))
                    : [{ word: '', hint: '' }];
            } else if (this.gameType === 'scramble') {
                this.scrambleWords = gd.words && gd.words.length > 0 
                    ? JSON.parse(JSON.stringify(gd.words))
                    : [{ word: '', hint: '' }];
            } else if (this.gameType === 'sequence') {
                if (gd.groups && gd.groups.length > 0) {
                    this.sequenceGroups = JSON.parse(JSON.stringify(gd.groups));
                } else if (gd.items && gd.items.length > 0) {
                    this.sequenceGroups = [{ title: 'Kelompok 1', items: JSON.parse(JSON.stringify(gd.items)) }];
                } else {
                    this.sequenceGroups = [{ title: 'Kelompok 1', items: [{ item: '' }, { item: '' }] }];
                }
            } else if (this.gameType === 'image_hotspot') {
                this.hotspots = gd.hotspots && gd.hotspots.length > 0 
                    ? JSON.parse(JSON.stringify(gd.hotspots))
                    : [{ x: 50, y: 50, label: '' }];
            } else if (this.gameType === 'chem_balancer') {
                this.chemEquations = gd.equations && gd.equations.length > 0 
                    ? JSON.parse(JSON.stringify(gd.equations))
                    : [{ equation: '', answers: '' }];
            } else if (this.gameType === 'math_ninja') {
                this.mathConfig = gd.config ? JSON.parse(JSON.stringify(gd.config)) : { operation: 'mixed', difficulty: 'easy' };
            }

            this.open = true;
        }
    }" 
    @open-game-modal.window="resetForm()" 
    @open-edit-game-modal.window="loadGame($event.detail)" 
    x-show="open" 
    class="fixed inset-0 overflow-y-auto" style="display: none; z-index: 99999 !important;">
    
    <div class="flex items-center justify-center min-h-screen p-4" style="z-index: 99999 !important;">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" @click="open = false" style="z-index: 99999 !important;"></div>

        <div x-show="open" x-transition class="bg-white rounded-3xl shadow-2xl overflow-hidden w-full relative border-2 border-black" style="max-width: 1100px; z-index: 100000 !important;">
            <div class="px-6 py-4 flex items-center justify-between border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
                <h3 class="text-white font-black tracking-wide flex items-center gap-2 text-sm uppercase"><i class="fas fa-gamepad text-amber-400"></i> <span x-text="isEdit ? 'Edit Game Pembelajaran' : 'Game Builder Studio (Interaktif)'"></span></h3>
                <button @click="open = false" class="text-white/80 hover:text-white transition-colors bg-slate-800 border border-slate-700 w-8 h-8 rounded-xl flex items-center justify-center font-black"><i class="fas fa-times"></i></button>
            </div>
            
            <form :action="isEdit ? '{{ url('/guru/lms/games') }}/' + gameId : '{{ route('guru.lms.games.store') }}'" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <input type="hidden" name="game_data" :value="getGameData()">

                <div class="space-y-3">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Judul Game</label>
                            <input type="text" name="title" x-model="title" class="w-full px-3 py-2 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Contoh: Kuis Cepat Modul 1" required>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Modul</label>
                            <select name="module_id" x-model="moduleId" class="w-full px-3 py-2 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">-- Pilih --</option>
                                @foreach($course->modules as $mod)
                                    <option value="{{ $mod->id }}">{{ $mod->getCode() }} - {{ $mod->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-700 mb-1">EXP Reward (Poin)</label>
                            <input type="number" name="reward_points" x-model="rewardPoints" value="50" min="0" max="1000" class="w-full px-3 py-2 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>

                    <div class="p-3 bg-red-50 border border-red-100 rounded-xl" x-show="['quiz', 'true_false', 'word_guess', 'scramble', 'sequence'].includes(gameType)">
                        <h4 class="text-xs font-bold text-red-600 uppercase tracking-widest mb-2 flex items-center gap-2"><i class="fas fa-fire"></i> Pengaturan Hardcore Mode (Opsional)</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Batas Waktu (Detik)</label>
                                <input type="number" name="time_limit" x-model="timeLimit" min="5" max="300" placeholder="Kosongkan jika tak terbatas" class="w-full px-4 py-2.5 rounded-xl border-red-200 bg-white text-sm focus:border-red-500 focus:ring-red-500 placeholder-gray-400">
                                <p class="text-[10px] text-gray-500 mt-1">Siswa akan gagal otomatis jika waktu habis.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Batas Nyawa</label>
                                <input type="number" name="lives_count" x-model="livesCount" min="1" max="10" placeholder="Kosongkan jika tak terbatas" class="w-full px-4 py-2.5 rounded-xl border-red-200 bg-white text-sm focus:border-red-500 focus:ring-red-500 placeholder-gray-400">
                                <p class="text-[10px] text-gray-500 mt-1">Siswa Game Over jika nyawa habis. Khusus Tebak Kata selalu 5 nyawa.</p>
                            </div>
                        </div>
                    </div>


                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Tipe Game</label>
                        <style>@media (min-width: 640px) { .sm\:grid-cols-6 { grid-template-columns: repeat(6, minmax(0, 1fr)); } }</style>
<div class="grid grid-cols-2 sm:grid-cols-6 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="flashcard" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-2 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-layer-group text-lg mb-1 text-indigo-400 peer-checked:text-indigo-600"></i>
                                    <p class="text-[10px] font-bold leading-tight text-gray-600">Flashcard 3D</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="match" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-2 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-puzzle-piece text-lg mb-1 text-purple-400 peer-checked:text-purple-600"></i>
                                    <p class="text-[10px] font-bold leading-tight text-gray-600">Match Pairs</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="spin_wheel" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-2 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-dharmachakra text-lg mb-1 text-pink-400 peer-checked:text-pink-600"></i>
                                    <p class="text-[10px] font-bold leading-tight text-gray-600">Spin Wheel</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="quiz" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-2 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-list-check text-lg mb-1 text-emerald-400 peer-checked:text-emerald-600"></i>
                                    <p class="text-[10px] font-bold leading-tight text-gray-600">Kuis</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="true_false" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-2 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-check-double text-lg mb-1 text-blue-400 peer-checked:text-blue-600"></i>
                                    <p class="text-[10px] font-bold leading-tight text-gray-600">Benar/Salah</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="word_guess" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-2 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all h-full flex flex-col items-center justify-center">
                                    <i class="fas fa-keyboard text-lg mb-1 text-amber-400 peer-checked:text-amber-600"></i>
                                    <p class="text-[11px] font-bold text-gray-600 leading-tight">Tebak Kata</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="scramble" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-2 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all h-full flex flex-col items-center justify-center">
                                    <i class="fas fa-cubes text-lg mb-1 text-orange-400 peer-checked:text-orange-600"></i>
                                    <p class="text-[11px] font-bold text-gray-600 leading-tight">Susun Kata</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="sequence" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-2 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all h-full flex flex-col items-center justify-center">
                                    <i class="fas fa-sort-amount-down text-lg mb-1 text-cyan-400 peer-checked:text-cyan-600"></i>
                                    <p class="text-[11px] font-bold text-gray-600 leading-tight">Urutkan</p>
                                </div>
                            </label>
                            
                            <!-- STEM Games -->
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="image_hotspot" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-emerald-100 p-3 text-center hover:border-emerald-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all h-full flex flex-col items-center justify-center shadow-[0_0_10px_rgba(16,185,129,0.1)]">
                                    <i class="fas fa-microscope text-lg mb-1 text-emerald-500 peer-checked:text-emerald-700"></i>
                                    <p class="text-[11px] font-bold text-emerald-700 leading-tight">Titik Buta (Biologi/Geografi)</p>
                                </div>
                            </label>
                            
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="chem_balancer" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-sky-100 p-3 text-center hover:border-sky-300 peer-checked:border-sky-500 peer-checked:bg-sky-50 transition-all h-full flex flex-col items-center justify-center shadow-[0_0_10px_rgba(14,165,233,0.1)]">
                                    <i class="fas fa-flask text-lg mb-1 text-sky-500 peer-checked:text-sky-700"></i>
                                    <p class="text-[11px] font-bold text-sky-700 leading-tight">Reaksi Kimia</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="math_ninja" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-purple-100 p-3 text-center hover:border-purple-300 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all h-full flex flex-col items-center justify-center shadow-[0_0_10px_rgba(168,85,247,0.1)]">
                                    <i class="fas fa-calculator text-lg mb-1 text-purple-500 peer-checked:text-purple-700"></i>
                                    <p class="text-[11px] font-bold text-purple-700 leading-tight">Math Ninja</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        {{-- Editor: Flashcard / Match --}}
                        <div x-show="gameType === 'flashcard' || gameType === 'match'">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-800">Pasangan Kartu / Kata</h4>
                                <button type="button" @click="addPair()" class="text-xs bg-indigo-100 text-indigo-600 px-2 py-1 rounded-lg font-bold hover:bg-indigo-200 transition"><i class="fas fa-plus"></i> Tambah Baris</button>
                            </div>
                            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(pair, index) in pairs" :key="index">
                                    <div class="flex gap-2 items-center bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-400 font-bold text-xs" x-text="index + 1"></div>
                                        <input type="text" x-model="pairs[index].term" placeholder="Istilah / Pertanyaan" class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <input type="text" x-model="pairs[index].definition" placeholder="Definisi / Jawaban" class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <button type="button" @click="removePair(index)" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Editor: Spin Wheel --}}
                        <div x-show="gameType === 'spin_wheel'" style="display: none;">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-800">Item Roda Undian</h4>
                                <button type="button" @click="addWheelItem()" class="text-xs bg-pink-100 text-pink-600 px-2 py-1 rounded-lg font-bold hover:bg-pink-200 transition"><i class="fas fa-plus"></i> Tambah Item</button>
                            </div>
                            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(item, index) in wheelItems" :key="index">
                                    <div class="flex gap-2 items-center bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                                        <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center text-pink-400 font-bold text-xs" x-text="index + 1"></div>
                                        <input type="text" x-model="wheelItems[index]" placeholder="Label Item (Misal: 100 EXP, Zonk)" class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500">
                                        <button type="button" @click="removeWheelItem(index)" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Editor: Quiz --}}
                        <div x-show="gameType === 'quiz'" style="display: none;">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-800">Pertanyaan Kuis</h4>
                                <button type="button" @click="addQuizQuestion()" class="text-xs bg-emerald-100 text-emerald-600 px-2 py-1 rounded-lg font-bold hover:bg-emerald-200 transition"><i class="fas fa-plus"></i> Tambah Soal</button>
                            </div>
                            <div class="space-y-4 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(q, index) in quizQuestions" :key="index">
                                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm relative">
                                        <button type="button" @click="removeQuizQuestion(index)" class="absolute top-2 right-2 w-6 h-6 rounded flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times text-xs"></i></button>
                                        <div class="flex gap-2 items-center mb-3">
                                            <div class="w-6 h-6 rounded bg-emerald-50 flex items-center justify-center text-emerald-500 font-bold text-xs" x-text="index + 1"></div>
                                            <input type="text" x-model="quizQuestions[index].question" placeholder="Pertanyaan..." class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 font-medium math-support-input">
                                            <button type="button" @click="window.openMathPalette($event.target.closest('.flex').querySelector('input[type=text]'))" title="Sisipkan Simbol" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-200"><i class="fas fa-square-root-variable"></i></button>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 pl-8">
                                            <template x-for="(opt, optIdx) in q.options" :key="optIdx">
                                                <div class="flex items-center gap-2">
                                                    <input type="radio" :name="'correct_answer_'+index" :value="optIdx" x-model.number="quizQuestions[index].answer" class="text-emerald-500 focus:ring-emerald-500 w-4 h-4">
                                                    <input type="text" x-model="quizQuestions[index].options[optIdx]" :placeholder="'Opsi ' + ['A','B','C','D'][optIdx]" class="flex-1 rounded-md border-gray-200 text-xs focus:border-emerald-500 focus:ring-emerald-500 math-support-input">
                                                    <button type="button" @click="window.openMathPalette($event.target.closest('.flex').querySelector('input[type=text]'))" title="Sisipkan Simbol" class="p-1 text-slate-400 hover:text-emerald-600"><i class="fas fa-square-root-variable text-xs"></i></button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Editor: True/False --}}
                        <div x-show="gameType === 'true_false'" style="display: none;">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-800">Pernyataan Benar/Salah</h4>
                                <button type="button" @click="addTfStatement()" class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-lg font-bold hover:bg-blue-200 transition"><i class="fas fa-plus"></i> Tambah Pernyataan</button>
                            </div>
                            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(st, index) in tfStatements" :key="index">
                                    <div class="flex gap-2 items-center bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-400 font-bold text-xs" x-text="index + 1"></div>
                                        <input type="text" x-model="tfStatements[index].statement" placeholder="Tuliskan pernyataan di sini..." class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <select x-model="tfStatements[index].is_true" class="px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 w-28 font-bold" :class="(tfStatements[index].is_true === 'true' || tfStatements[index].is_true === true) ? 'text-emerald-600' : 'text-rose-600'">
                                            <option :value="true">Benar</option>
                                            <option :value="false">Salah</option>
                                        </select>
                                        <button type="button" @click="removeTfStatement(index)" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Editor: Word Guess --}}
                        <div x-show="gameType === 'word_guess'" style="display: none;">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-800">Kata Rahasia</h4>
                                <button type="button" @click="addGuessWord()" class="text-xs bg-amber-100 text-amber-600 px-2 py-1 rounded-lg font-bold hover:bg-amber-200 transition"><i class="fas fa-plus"></i> Tambah Kata</button>
                            </div>
                            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(w, index) in guessWords" :key="index">
                                    <div class="flex gap-2 items-center bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                                        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500 font-bold text-xs" x-text="index + 1"></div>
                                        <input type="text" x-model="guessWords[index].word" placeholder="Kata (Tanpa Spasi)" class="w-1/3 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500 font-mono uppercase">
                                        <input type="text" x-model="guessWords[index].hint" placeholder="Petunjuk / Clue" class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                        <button type="button" @click="removeGuessWord(index)" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Editor: Susun Kata (Scramble) --}}
                        <div x-show="gameType === 'scramble'" style="display: none;">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-800">Kata untuk Disusun</h4>
                                <button type="button" @click="addScrambleWord()" class="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-lg font-bold hover:bg-orange-200 transition"><i class="fas fa-plus"></i> Tambah Kata</button>
                            </div>
                            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(w, index) in scrambleWords" :key="index">
                                    <div class="flex gap-2 items-center bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                                        <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center text-orange-500 font-bold text-xs" x-text="index + 1"></div>
                                        <input type="text" x-model="scrambleWords[index].word" placeholder="Kata Benda/Kerja (Tanpa Spasi)" class="w-1/3 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-orange-500 focus:ring-orange-500 font-mono uppercase">
                                        <input type="text" x-model="scrambleWords[index].hint" placeholder="Petunjuk Singkat" class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-orange-500 focus:ring-orange-500">
                                        <button type="button" @click="removeScrambleWord(index)" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Editor: Urutkan (Sequence) --}}
                        <div x-show="gameType === 'sequence'" style="display: none;">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">Kelompok Urutan (Sequence Groups)</h4>
                                    <p class="text-xs text-gray-500"><i class="fas fa-info-circle text-cyan-500"></i> Buat 1 atau lebih kelompok pengurutan. Masukkan langkah dari urutan PERTAMA (atas) ke TERAKHIR (bawah).</p>
                                </div>
                                <button type="button" @click="addSequenceGroup()" class="text-xs bg-cyan-100 text-cyan-600 px-3 py-1.5 rounded-xl font-bold hover:bg-cyan-200 transition shadow-sm flex items-center gap-1 shrink-0 ml-2">
                                    <i class="fas fa-plus"></i> Tambah Kelompok
                                </button>
                            </div>
                            
                            <div class="space-y-4 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(g, gIdx) in sequenceGroups" :key="gIdx">
                                    <div class="bg-white p-3.5 rounded-2xl border border-cyan-100 shadow-sm relative space-y-3">
                                        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-2">
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="w-6 h-6 rounded-lg bg-cyan-50 text-cyan-600 font-black text-xs flex items-center justify-center shrink-0" x-text="gIdx + 1"></span>
                                                <input type="text" x-model="sequenceGroups[gIdx].title" :placeholder="'Judul / Petunjuk Kelompok ' + (gIdx + 1) + ' (misal: Siklus Air)'" class="flex-1 px-3 py-2 rounded-xl border-gray-200 text-xs font-bold focus:border-cyan-500 focus:ring-cyan-500">
                                            </div>
                                            <button type="button" x-show="sequenceGroups.length > 1" @click="removeSequenceGroup(gIdx)" class="text-rose-400 hover:text-rose-600 p-1 transition shrink-0" title="Hapus Kelompok">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Langkah Urutan Benar:</span>
                                                <button type="button" @click="addSequenceItem(gIdx)" class="text-[11px] bg-cyan-50 text-cyan-600 hover:bg-cyan-100 px-2 py-0.5 rounded font-bold flex items-center gap-1">
                                                    <i class="fas fa-plus text-[10px]"></i> Tambah Langkah
                                                </button>
                                            </div>

                                            <template x-for="(item, iIdx) in g.items" :key="iIdx">
                                                <div class="flex gap-2 items-center bg-gray-50 p-2 rounded-xl border border-gray-200">
                                                    <div class="w-6 h-6 rounded-md bg-white border border-gray-200 flex items-center justify-center text-cyan-600 font-bold text-[11px] shrink-0" x-text="iIdx + 1"></div>
                                                    <input type="text" x-model="sequenceGroups[gIdx].items[iIdx].item" placeholder="Contoh: Panaskan air hingga mendidih" class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-xs focus:border-cyan-500 focus:ring-cyan-500">
                                                    <button type="button" x-show="g.items.length > 1" @click="removeSequenceItem(gIdx, iIdx)" class="w-6 h-6 rounded flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition shrink-0"><i class="fas fa-times text-xs"></i></button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        {{-- Editor: Image Hotspot --}}
                        <div x-show="gameType === 'image_hotspot'" style="display: none;">
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Upload Gambar Referensi <span class="text-red-500">*</span></label>
                                <input type="file" name="hotspot_image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                <p class="text-[10px] text-gray-400 mt-1">Gunakan gambar jelas (Max 2MB). Koordinat hotspot (%) dari kiri-atas.</p>
                            </div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-800">Area Target (Hotspots)</h4>
                                <button type="button" @click="addHotspot()" class="text-xs bg-emerald-100 text-emerald-600 px-2 py-1 rounded-lg font-bold hover:bg-emerald-200 transition"><i class="fas fa-plus"></i> Tambah Area</button>
                            </div>
                            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(hotspot, index) in hotspots" :key="index">
                                    <div class="flex gap-2 items-center bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                                        <input type="text" x-model="hotspots[index].label" placeholder="Nama Area (misal: Mitokondria)" class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <input type="number" x-model="hotspots[index].x" placeholder="X %" min="0" max="100" class="w-20 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <input type="number" x-model="hotspots[index].y" placeholder="Y %" min="0" max="100" class="w-20 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <button type="button" @click="removeHotspot(index)" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Editor: Chemistry Balancer --}}
                        <div x-show="gameType === 'chem_balancer'" style="display: none;">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-800">Persamaan Reaksi</h4>
                                <button type="button" @click="addChemEquation()" class="text-xs bg-sky-100 text-sky-600 px-2 py-1 rounded-lg font-bold hover:bg-sky-200 transition"><i class="fas fa-plus"></i> Tambah Persamaan</button>
                            </div>
                            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(eq, index) in chemEquations" :key="index">
                                    <div class="flex flex-col gap-2 bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                                        <input type="text" x-model="chemEquations[index].equation" placeholder="Contoh: _ H2 + _ O2 -> _ H2O (Gunakan underscore _)" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-sky-500 focus:ring-sky-500 font-mono">
                                        <div class="flex gap-2 items-center">
                                            <input type="text" x-model="chemEquations[index].answers" placeholder="Jawaban Koefisien (pisahkan koma: 2,1,2)" class="flex-1 px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-sky-500 focus:ring-sky-500">
                                            <button type="button" @click="removeChemEquation(index)" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Editor: Math Ninja --}}
                        <div x-show="gameType === 'math_ninja'" style="display: none;">
                            <h4 class="text-sm font-bold text-gray-800 mb-3">Pengaturan Math Ninja</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Operasi Hitung</label>
                                    <select x-model="mathConfig.operation" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="add">Penjumlahan (+)</option>
                                        <option value="sub">Pengurangan (-)</option>
                                        <option value="mul">Perkalian (x)</option>
                                        <option value="mixed">Campuran Acak</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Tingkat Kesulitan</label>
                                    <select x-model="mathConfig.difficulty" class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="easy">Mudah (Angka 1-10)</option>
                                        <option value="medium">Sedang (Angka 10-50)</option>
                                        <option value="hard">Sulit (Angka 50-100)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-5 py-2.5 rounded-xl text-gray-600 font-bold text-sm hover:bg-gray-100 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md hover:bg-indigo-700 hover:shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-save"></i> <span x-text="isEdit ? 'Simpan Perubahan Game' : 'Buat Game'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- MATERIAL UPLOAD MODAL --}}
{{-- ═══════════════════════════════════════════════ --}}
<div x-data="{ open: false, type: 'text', file_url: '', material_title: '' }" @open-material-modal.window="open = true; type = 'text'" x-show="open" class="fixed inset-0 overflow-y-auto" style="display: none; z-index: 99999 !important;">
    <div class="flex items-center justify-center min-h-screen p-4" style="z-index: 99999 !important;">
        <div x-show="open" x-transition class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" @click="open = false" style="z-index: 99999 !important;"></div>

        <div x-show="open" x-transition class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-3xl w-full relative border-2 border-black" style="z-index: 100000 !important;">
            <div class="px-6 py-4 flex items-center justify-between border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
                <h3 class="text-white font-black tracking-wide flex items-center gap-2 text-sm uppercase"><i class="fas fa-edit text-amber-400"></i> Buat Materi Pembelajaran Baru</h3>
                <button @click="open = false" class="text-white/80 hover:text-white transition-colors bg-slate-800 border border-slate-700 w-8 h-8 rounded-xl flex items-center justify-center font-black"><i class="fas fa-times"></i></button>
            </div>
            
            <form action="{{ route('guru.lms.materials.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Modul Target <span class="text-red-600">*</span></label>
                            <select name="module_id" required class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                                <option value="">-- Pilih Modul --</option>
                                @foreach($course->modules as $mod)
                                <option value="{{ $mod->id }}">Modul {{ $mod->sequence }}: {{ $mod->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Format / Tipe Materi <span class="text-red-600">*</span></label>
                            <select name="material_type" required x-model="type" class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-amber-50">
                                <option value="text">📝 Artikel / Modul Teks Langsung (Disarankan)</option>
                                <option value="pdf">📄 Berkas PDF</option>
                                <option value="document">📁 Dokumen Word / PPT</option>
                                <option value="video">🎥 Pemutar Video / YouTube</option>
                                <option value="image">🖼️ Gambar / Diagram</option>
                                <option value="canva">🎨 Embed Canva Presentation</option>
                                <option value="googledocs">📊 Embed Google Docs / Slides / Form</option>
                                <option value="audio">🎙️ Rekaman Audio / Podcast</option>
                                <option value="interactive">🎮 Simulator Interaktif (PhET, SimLab, GeoGebra)</option>
                                <option value="link">🔗 Link Eksternal</option>
                                <option value="embed">💻 Kode Embed HTML (Iframe Custom)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Judul Materi Pembelajaran <span class="text-red-600">*</span></label>
                        <input type="text" name="title" x-model="material_title" required placeholder="Contoh: Bab 1 - Pengantar Algoritma & Struktur Data..." class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>

                    <!-- Quill Editor Area -->
                    <div class="border-2 border-black rounded-2xl p-4 bg-slate-50 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b-2 border-slate-200 pb-2">
                            <label class="block text-xs font-black text-black uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-newspaper text-indigo-600 text-sm"></i> Isi Artikel &amp; Modul Teks Pembelajaran
                            </label>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button type="button" onclick="generateAiContent('create', '{{ addslashes($course->name ?? '') }}')" class="px-3 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-black transition-all shadow-md flex items-center gap-1 border border-black">
                                    <i class="fas fa-magic text-amber-300"></i> ✨ Generate Artikel via AI
                                </button>
                                <div class="hidden sm:inline-block text-[11px] font-bold text-slate-500">| Template:</div>
                                <button type="button" onclick="insertTemplate('create', 'summary')" class="px-2.5 py-1 bg-amber-200 hover:bg-amber-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    📘 Ringkasan Bab
                                </button>
                                <button type="button" onclick="insertTemplate('create', 'lab')" class="px-2.5 py-1 bg-emerald-200 hover:bg-emerald-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    🧪 Praktikum
                                </button>
                                <button type="button" onclick="insertTemplate('create', 'case')" class="px-2.5 py-1 bg-sky-200 hover:bg-sky-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    💡 Studi Kasus
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="content" id="quill-create-input">
                        <div id="quill-create-editor" class="bg-white min-h-[220px] rounded-xl border border-slate-300"></div>
                        <p class="text-[11px] text-slate-600 font-bold flex items-center gap-1">
                            <i class="fas fa-info-circle text-sky-600"></i> Format teks HTML (Heading, tebal, list, gambar inline, tabel) tersimpan sempurna tanpa hilang saat diedit.
                        </p>
                    </div>

                    <!-- Conditional File Upload -->
                    <div x-show="type !== 'text' && type !== 'link' && type !== 'embed' && type !== 'canva' && type !== 'googledocs'" class="bg-amber-50 p-4 border-2 border-black rounded-2xl">
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Unggah Berkas Lampiran (PDF / Doc / Video / Gambar / Audio - Maks. 10 MB)</label>
                        <input type="file" name="file" class="w-full text-xs text-black font-bold file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-2 file:border-black file:text-xs file:font-black file:bg-amber-300 file:text-black hover:file:bg-black hover:file:text-white cursor-pointer">
                    </div>

                    <!-- URL / Embed Field -->
                    <div x-show="type !== 'text'" class="bg-slate-50 p-4 border-2 border-black rounded-2xl space-y-2">
                        <label class="block text-xs font-black text-black uppercase tracking-wider">URL / Tautan / Kode Embed Eksternal</label>
                        <input type="text" name="file_url" x-model="file_url" placeholder="https://canva.com/design/... atau https://docs.google.com/presentation/d/... atau https://youtube.com/..." class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-xs text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                        <p class="text-[11px] text-slate-500 font-bold">Masukkan URL Canva, Google Docs, YouTube, Audio link, atau Kode Embed Iframe HTML.</p>
                    </div>

                    <!-- Simulator Quick Selector -->
                    <div x-show="type === 'interactive'" class="bg-purple-100 border-2 border-black rounded-2xl p-4 text-xs text-black space-y-2.5" style="display: none">
                        <p class="font-black uppercase tracking-wider flex items-center gap-1.5 text-xs">
                            <i class="fas fa-gamepad text-purple-700 text-sm"></i> Pilih Simulator Interaktif 1-Klik:
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <button type="button" @click="file_url = '{{ route('simlab.index') }}'; if(!material_title) material_title = 'Simulasi Mikrokontroler PembdaHUB SimLab'" class="p-2 bg-emerald-300 border border-black rounded-xl text-left hover:bg-emerald-400 text-xs font-black text-black flex items-center gap-1.5">
                                <i class="fas fa-microchip text-emerald-900"></i> PembdaHUB SimLab
                            </button>
                            <button type="button" @click="file_url = 'https://phet.colorado.edu/sims/html/circuit-construction-kit-dc/latest/circuit-construction-kit-dc_all.html'; if(!material_title) material_title = 'Simulasi Rangkaian Listrik PhET'" class="p-2 bg-white border border-black rounded-xl text-left hover:bg-amber-300 text-xs font-black text-black flex items-center gap-1.5">
                                <i class="fas fa-bolt text-sky-600"></i> PhET (IPA/TE)
                            </button>
                            <button type="button" @click="file_url = 'https://www.geogebra.org/classic'; if(!material_title) material_title = 'Simulasi Geometri GeoGebra'" class="p-2 bg-white border border-black rounded-xl text-left hover:bg-amber-300 text-xs font-black text-black flex items-center gap-1.5">
                                <i class="fas fa-shapes text-indigo-600"></i> GeoGebra
                            </button>
                            <button type="button" @click="file_url = 'https://molview.org'; if(!material_title) material_title = 'Simulasi Molekul Kimia 3D'" class="p-2 bg-white border border-black rounded-xl text-left hover:bg-amber-300 text-xs font-black text-black flex items-center gap-1.5">
                                <i class="fas fa-atom text-rose-600"></i> MolView 3D
                            </button>
                            <button type="button" @click="file_url = 'https://bellard.org/jslinux/'; if(!material_title) material_title = 'Simulasi Terminal Linux Server'" class="p-2 bg-white border border-black rounded-xl text-left hover:bg-amber-300 text-xs font-black text-black flex items-center gap-1.5">
                                <i class="fas fa-terminal text-slate-800"></i> JS-Linux
                            </button>
                            <button type="button" @click="file_url = 'https://quizizz.com'; if(!material_title) material_title = 'Game Edukasi Quizizz'" class="p-2 bg-white border border-black rounded-xl text-left hover:bg-amber-300 text-xs font-black text-black flex items-center gap-1.5">
                                <i class="fas fa-puzzle-piece text-purple-600"></i> Quizizz
                            </button>
                        </div>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="open = false" class="flex-1 px-6 py-3.5 rounded-2xl font-black bg-slate-200 text-black border-2 border-black hover:bg-slate-300 transition-all uppercase tracking-wider text-xs">Batal</button>
                        <button type="submit" class="flex-1 px-6 py-3.5 rounded-2xl font-black bg-black text-white hover:bg-emerald-600 transition-all border-2 border-black shadow-md uppercase tracking-wider text-xs"><i class="fas fa-save mr-1 text-amber-400"></i> Simpan Materi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- MATERIAL EDIT MODAL --}}
{{-- ═══════════════════════════════════════════════ --}}
<div x-data="{ open: false, mat: {} }" @open-edit-material-modal.window="mat = $event.detail; open = true; $nextTick(() => setQuillEditContent(mat.content))" x-show="open" class="fixed inset-0 overflow-y-auto" style="display: none; z-index: 99999 !important;">
    <div class="flex items-center justify-center min-h-screen p-4" style="z-index: 99999 !important;">
        <div x-show="open" x-transition class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" @click="open = false" style="z-index: 99999 !important;"></div>

        <div x-show="open" x-transition class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-3xl w-full relative border-2 border-black" style="z-index: 100000 !important;">
            <div class="px-6 py-4 flex items-center justify-between border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
                <h3 class="text-white font-black tracking-wide flex items-center gap-2 text-sm uppercase"><i class="fas fa-edit text-amber-400"></i> Edit Materi Pembelajaran</h3>
                <button @click="open = false" class="text-white/80 hover:text-white transition-colors bg-slate-800 border border-slate-700 w-8 h-8 rounded-xl flex items-center justify-center font-black"><i class="fas fa-times"></i></button>
            </div>
            
            <form :action="mat.update_url" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Modul Target</label>
                            <select name="module_id" x-model="mat.module_id" required class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                                @foreach($course->modules as $mod)
                                <option value="{{ $mod->id }}">Modul {{ $mod->sequence }}: {{ $mod->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Format / Tipe Materi</label>
                            <select name="material_type" x-model="mat.material_type" required class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-amber-50">
                                <option value="text">📝 Artikel / Modul Teks Langsung (Disarankan)</option>
                                <option value="pdf">📄 Berkas PDF</option>
                                <option value="document">📁 Dokumen Word / PPT</option>
                                <option value="video">🎥 Pemutar Video / YouTube</option>
                                <option value="image">🖼️ Gambar / Diagram</option>
                                <option value="canva">🎨 Embed Canva Presentation</option>
                                <option value="googledocs">📊 Embed Google Docs / Slides / Form</option>
                                <option value="audio">🎙️ Rekaman Audio / Podcast</option>
                                <option value="interactive">🎮 Simulator Interaktif (PhET, SimLab, GeoGebra)</option>
                                <option value="link">🔗 Link Eksternal</option>
                                <option value="embed">💻 Kode Embed HTML (Iframe Custom)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Judul Materi Pembelajaran</label>
                        <input type="text" name="title" x-model="mat.title" required placeholder="Judul Materi..." class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>

                    <!-- Quill Edit Editor Area -->
                    <div class="border-2 border-black rounded-2xl p-4 bg-slate-50 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b-2 border-slate-200 pb-2">
                            <label class="block text-xs font-black text-black uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-newspaper text-indigo-600 text-sm"></i> Isi Artikel &amp; Modul Teks Pembelajaran
                            </label>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button type="button" onclick="generateAiContent('edit', '{{ addslashes($course->name ?? '') }}')" class="px-3 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-black transition-all shadow-md flex items-center gap-1 border border-black">
                                    <i class="fas fa-magic text-amber-300"></i> ✨ Tulis Ulang via AI
                                </button>
                                <div class="hidden sm:inline-block text-[11px] font-bold text-slate-500">| Template:</div>
                                <button type="button" onclick="insertTemplate('edit', 'summary')" class="px-2.5 py-1 bg-amber-200 hover:bg-amber-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    📘 Ringkasan Bab
                                </button>
                                <button type="button" onclick="insertTemplate('edit', 'lab')" class="px-2.5 py-1 bg-emerald-200 hover:bg-emerald-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    🧪 Praktikum
                                </button>
                                <button type="button" onclick="insertTemplate('edit', 'case')" class="px-2.5 py-1 bg-sky-200 hover:bg-sky-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    💡 Studi Kasus
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="content" id="quill-edit-input" :value="mat.content">
                        <div id="quill-edit-editor" class="bg-white min-h-[220px] rounded-xl border border-slate-300"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">URL / Tautan Pembelajaran (Opsional)</label>
                            <input type="text" name="file_url" x-model="mat.file_url" placeholder="https://..." class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">Ganti File (Maks. 10 MB)</label>
                            <input type="file" name="file" class="w-full text-xs text-black font-bold file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-2 file:border-black file:text-xs file:font-black file:bg-amber-300 file:text-black hover:file:bg-black hover:file:text-white cursor-pointer">
                        </div>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="open = false" class="flex-1 px-6 py-3.5 rounded-2xl font-black bg-slate-200 text-black border-2 border-black hover:bg-slate-300 transition-all uppercase tracking-wider text-xs">Batal</button>
                        <button type="submit" class="flex-1 px-6 py-3.5 rounded-2xl font-black bg-black text-white hover:bg-emerald-600 transition-all border-2 border-black shadow-md uppercase tracking-wider text-xs"><i class="fas fa-save mr-1 text-amber-400"></i> Perbarui Materi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- FULL ARTICLE READER PREVIEW MODAL (FOR GURU) --}}
{{-- ═══════════════════════════════════════════════ --}}
<div x-data="{ open: false, title: '', content: '' }" @open-article-reader.window="title = $event.detail.title; content = $event.detail.content; open = true" x-show="open" class="fixed inset-0 overflow-y-auto" style="display: none; z-index: 99999 !important;">
    <div class="flex items-center justify-center min-h-screen p-4 md:p-8" style="z-index: 99999 !important;">
        <div x-show="open" x-transition class="fixed inset-0 bg-slate-900/90 backdrop-blur-md transition-opacity" @click="open = false" style="z-index: 99999 !important;"></div>

        <div x-show="open" x-transition class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-4xl w-full relative border-2 border-black flex flex-col max-h-[90vh]" style="z-index: 100000 !important;">
            <div class="px-6 py-4 flex items-center justify-between border-b-2 border-black shrink-0" style="background-color: #0f172a !important; color: #ffffff !important;">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-amber-400 text-black flex items-center justify-center font-black border border-black"><i class="fas fa-book-open"></i></span>
                    <div>
                        <span class="text-[10px] font-black uppercase text-amber-400 tracking-widest block">Pratinjau Mode Baca Artikel Pembelajaran</span>
                        <h3 class="text-white font-black tracking-wide text-sm sm:text-base leading-tight" x-text="title"></h3>
                    </div>
                </div>
                <button @click="open = false" class="text-white/80 hover:text-white transition-colors bg-slate-800 border border-slate-700 w-8 h-8 rounded-xl flex items-center justify-center font-black"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="p-6 md:p-10 overflow-y-auto flex-1 bg-white prose prose-lg max-w-none text-slate-900 leading-relaxed font-sans" x-html="content">
            </div>

            <div class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex justify-end shrink-0">
                <button type="button" @click="open = false" class="px-5 py-2 rounded-xl font-black bg-black text-white text-xs border border-black hover:bg-slate-800">Tutup Pratinjau</button>
            </div>
        </div>
    </div>
</div>
</div>
@include('components.lms-game-player')
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Custom tooltip config for all charts
        const customTooltip = {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleFont: { size: 12, weight: 'bold' },
            bodyFont: { size: 11 },
            padding: 12,
            cornerRadius: 10,
            displayColors: true,
            boxPadding: 4
        };

        // Materials Chart with gradient fill
        const matCtx = document.getElementById('materialsChart');
        if (matCtx) {
            const matData = @json($materialsData);
            const labels = matData.map(d => d.title);
            const counts = matData.map(d => d.count);

            // Create gradient
            const matGradient = matCtx.getContext('2d').createLinearGradient(0, 0, 0, 320);
            matGradient.addColorStop(0, 'rgba(16, 185, 129, 0.8)');
            matGradient.addColorStop(1, 'rgba(59, 130, 246, 0.4)');

            new Chart(matCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Siswa Selesai Membaca',
                        data: counts,
                        backgroundColor: matGradient,
                        borderColor: 'rgba(16, 185, 129, 0.9)',
                        borderWidth: 1,
                        borderRadius: 10,
                        borderSkipped: false,
                        hoverBackgroundColor: 'rgba(16, 185, 129, 0.9)',
                        hoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 800,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { size: 11 }, color: '#9ca3af' },
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false }
                        },
                        x: {
                            ticks: { font: { size: 10 }, color: '#9ca3af', maxRotation: 45 },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: customTooltip
                    }
                }
            });
        }

        // Quizzes Doughnut Chart with enhanced styling
        const quizCtx = document.getElementById('quizzesChart');
        if (quizCtx) {
            const scores = @json(array_values($quizScores));
            const totalAttempts = scores.reduce((a, b) => a + b, 0);

            new Chart(quizCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Sangat Memuaskan (86-100)', 'Memuaskan (71-85)', 'Cukup (56-70)', 'Perlu Perbaikan (0-55)'],
                    datasets: [{
                        data: scores,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.75)',
                            'rgba(16, 185, 129, 0.75)',
                            'rgba(245, 158, 11, 0.75)',
                            'rgba(239, 68, 68, 0.65)'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 8,
                        hoverBorderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    animation: {
                        animateRotate: true,
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 14,
                                boxHeight: 14,
                                borderRadius: 4,
                                useBorderRadius: true,
                                font: { size: 11, weight: '500' },
                                padding: 16,
                                color: '#6b7280'
                            }
                        },
                        tooltip: customTooltip
                    }
                },
                plugins: [{
                    id: 'centerText',
                    afterDraw: function(chart) {
                        const { width, height, ctx } = chart;
                        ctx.save();
                        const fontSize = Math.min(width, height) / 8;
                        ctx.font = `bold ${fontSize}px sans-serif`;
                        ctx.fillStyle = '#1f2937';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                        const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
                        ctx.fillText(totalAttempts, centerX, centerY - 6);
                        ctx.font = `600 ${fontSize * 0.4}px sans-serif`;
                        ctx.fillStyle = '#9ca3af';
                        ctx.fillText('PERCOBAAN', centerX, centerY + fontSize * 0.55);
                        ctx.restore();
                    }
                }]
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    let quillCreate = null;
    let quillEdit = null;

    function imageHandler(quillInstance) {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async () => {
            const file = input.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);

            try {
                const res = await fetch('{{ route("guru.lms.upload-editor-image") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await res.json();
                if (data.success && data.url) {
                    const range = quillInstance.getSelection(true) || { index: quillInstance.getLength() };
                    quillInstance.insertEmbed(range.index, 'image', data.url);
                    quillInstance.setSelection(range.index + 1);
                } else {
                    alert(data.message || 'Gagal mengunggah gambar.');
                }
            } catch (e) {
                alert('Gagal mengunggah gambar: ' + e.message);
            }
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toolbarOptions = [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'align': [] }],
            ['link', 'image', 'video'],
            ['clean']
        ];

        // Init Create Editor
        const createEditorEl = document.getElementById('quill-create-editor');
        if (createEditorEl) {
            quillCreate = new Quill('#quill-create-editor', {
                theme: 'snow',
                placeholder: 'Tuliskan petunjuk atau rangkuman materi...',
                modules: { toolbar: toolbarOptions }
            });
            quillCreate.getModule('toolbar').addHandler('image', function() {
                imageHandler(quillCreate);
            });
            quillCreate.on('text-change', function() {
                const input = document.getElementById('quill-create-input');
                if (input) input.value = quillCreate.root.innerHTML === '<p><br></p>' ? '' : quillCreate.root.innerHTML;
            });
        }

        // Init Edit Editor
        const editEditorEl = document.getElementById('quill-edit-editor');
        if (editEditorEl) {
            quillEdit = new Quill('#quill-edit-editor', {
                theme: 'snow',
                placeholder: 'Keterangan materi atau instruksi...',
                modules: { toolbar: toolbarOptions }
            });
            quillEdit.getModule('toolbar').addHandler('image', function() {
                imageHandler(quillEdit);
            });
            quillEdit.on('text-change', function() {
                const input = document.getElementById('quill-edit-input');
                if (input) input.value = quillEdit.root.innerHTML === '<p><br></p>' ? '' : quillEdit.root.innerHTML;
            });
        }
    });

    async function generateAiContent(editorType, courseSubject) {
        const titleInput = editorType === 'create' 
            ? document.querySelector('#open-material-modal input[name="title"]')
            : document.querySelector('#open-edit-material-modal input[name="title"]');
        
        const topic = titleInput ? titleInput.value.trim() : '';
        if (!topic) {
            alert('Silakan isi "Judul Materi" terlebih dahulu sebagai topik utama pembuatan artikel AI.');
            if (titleInput) titleInput.focus();
            return;
        }

        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyusun Materi AI...';

        try {
            const res = await fetch("{{ route('guru.lms.materials.generate-ai') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    topic: topic,
                    subject: courseSubject || 'Mata Pelajaran'
                })
            });

            const data = await res.json();
            if (data.success && data.content) {
                if (editorType === 'create' && quillCreate) {
                    quillCreate.root.innerHTML = data.content;
                    const input = document.getElementById('quill-create-input');
                    if (input) input.value = data.content;
                } else if (editorType === 'edit' && quillEdit) {
                    quillEdit.root.innerHTML = data.content;
                    const input = document.getElementById('quill-edit-input');
                    if (input) input.value = data.content;
                }
            } else {
                alert(data.message || 'Gagal menghasilkan materi dengan AI.');
            }
        } catch (err) {
            alert('Terjadi kesalahan sistem AI: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    function insertTemplate(editorType, templateType) {
        let html = '';
        if (templateType === 'summary') {
            html = `<h2>📌 Ringkasan Bab: [Nama Topik]</h2><p>Penjelasan pendahuluan mengenai topik yang dipelajari siswa...</p><h3>💡 1. Konsep Utama</h3><ul><li>Poin penting 1...</li><li>Poin penting 2...</li></ul><h3>⚡ 2. Hal Yang Wajib Diingat</h3><blockquote style="border-left: 4px solid #f59e0b; padding-left: 12px; font-style: italic; color: #4b5563;">Rangkuman poin inti dalam 1-2 kalimat pemungkas.</blockquote>`;
        } else if (templateType === 'lab') {
            html = `<h2>🧪 Panduan Praktikum &amp; Laboratorium</h2><p><strong>Tujuan Praktikum:</strong> Siswa mampu memahami dan mempraktikkan...</p><h3>🛠️ Alat &amp; Bahan</h3><ul><li>Alat/Bahan 1...</li><li>Alat/Bahan 2...</li></ul><h3>📝 Langkah Kerja</h3><ol><li>Langkah 1: Siapkan peralatan...</li><li>Langkah 2: Operasikan...</li></ol>`;
        } else if (templateType === 'case') {
            html = `<h2>💡 Studi Kasus &amp; Diskusi Terbuka</h2><blockquote style="border-left: 4px solid #3b82f6; padding-left: 12px; background-color: #eff6ff; padding: 10px; border-radius: 8px;"><strong>Deskripsi Kasus:</strong> Jelaskan studi kasus nyata di dunia kerja/lapangan...</blockquote><h3>🎯 Tugas Refleksi Siswa:</h3><ol><li>Identifikasi penyebab utama masalah di atas.</li><li>Tuliskan 2 usulan solusi terbaik.</li></ol>`;
        }

        if (editorType === 'create' && quillCreate) {
            quillCreate.root.innerHTML = html;
            const input = document.getElementById('quill-create-input');
            if (input) input.value = html;
        } else if (editorType === 'edit' && quillEdit) {
            quillEdit.root.innerHTML = html;
            const input = document.getElementById('quill-edit-input');
            if (input) input.value = html;
        }
    }

    function setQuillEditContent(content) {
        if (!quillEdit) return;
        let html = content || '';
        if (html && !/<(p|div|br|h[1-6]|ul|ol|li|table|blockquote)\b/i.test(html)) {
            html = html.split('\n').map(line => line.trim() ? `<p>${line}</p>` : '<p><br></p>').join('');
        }
        quillEdit.root.innerHTML = html;
        const input = document.getElementById('quill-edit-input');
        if (input) input.value = html;
    }
</script>
@endpush
