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
        max-height: 450px;
        font-size: 0.875rem;
    }
    .ql-editor table, .prose table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 1rem 0 !important;
        font-size: 0.875rem !important;
    }
    .ql-editor th, .ql-editor td, .prose th, .prose td {
        border: 1px solid #cbd5e1 !important;
        padding: 0.5rem 0.75rem !important;
        text-align: left !important;
        vertical-align: top !important;
    }
    .ql-editor th, .prose th {
        background-color: #f1f5f9 !important;
        font-weight: 800 !important;
        color: #0f172a !important;
    }
    .ql-editor tr:nth-child(even), .prose tr:nth-child(even) {
        background-color: #f8fafc;
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
    .ql-editor p, .prose p {
        margin-bottom: 0.4rem !important;
        line-height: 1.6 !important;
    }
    .ql-editor h1, .ql-editor h2, .ql-editor h3, .prose h1, .prose h2, .prose h3 {
        margin-top: 1rem !important;
        margin-bottom: 0.4rem !important;
        font-weight: 800 !important;
    }
    .ql-editor ol, .prose ol {
        list-style-type: decimal !important;
        list-style-position: outside !important;
        margin-left: 1.5rem !important;
        padding-left: 0.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .ql-editor ul, .prose ul {
        list-style-type: disc !important;
        list-style-position: outside !important;
        margin-left: 1.5rem !important;
        padding-left: 0.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .ql-editor li, .prose li {
        display: list-item !important;
        margin-bottom: 0.35rem !important;
        padding-left: 0.25rem !important;
    }
    .prose code {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 0.375rem !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-size: 0.875em !important;
        border: 1px solid #e2e8f0 !important;
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
    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    {{-- COURSE HERO BANNER (100% SOLID UI UX PRO MAX) --}}
    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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
                    @if(!$course->shared_from_course_id)
                    <button type="button" onclick="openShareCourseModal()" class="px-3 py-2 text-white border-2 border-black rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-md transition-all hover:bg-indigo-700" style="background-color: #4f46e5 !important;" title="Bagikan salinan kursus ini ke rekan guru lain">
                        <i class="fas fa-share-nodes text-xs text-amber-300"></i> Sharing Course
                    </button>
                    @else
                    <span class="px-3 py-2 bg-indigo-50 text-indigo-700 border-2 border-dashed border-indigo-400 rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1" title="Course ini merupakan hasil sharing dari guru lain">
                        <i class="fas fa-link text-xs"></i> Hasil Sharing
                    </span>
                    @endif
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
                        @if($course->shared_from_course_id && $course->sharedFromCourse)
                        <span class="border-2 border-black px-3 py-1 rounded-xl text-xs font-black bg-indigo-100 text-indigo-900 flex items-center gap-1">
                            <i class="fas fa-share-nodes text-indigo-600"></i> Sharing dari: {{ $course->sharedFromCourse->teacher->name ?? 'Guru Lain' }}
                        </span>
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


    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    {{-- TAB NAVIGATION --}}
    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        {{-- TAB: MATERIALS / MODULES --}}
        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div x-show="tab === 'materials'" class="mt-6 space-y-6 tab-content">
            <div class="flex items-center justify-between">
                <h3 class="font-black text-black text-sm flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center border-2 border-black" style="background-color: #d1fae5 !important; color: #000000 !important;"><i class="fas fa-layer-group text-black text-xs"></i></span>
                    STRUKTUR KURIKULUM & MODUL
                </h3>
                <div class="flex flex-wrap gap-2.5">
                    @if(isset($trashedModules) && $trashedModules->isNotEmpty())
                    <button @click="$dispatch('open-trashed-modules-modal')" class="inline-flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-800 border-2 border-rose-500 px-4 py-2.5 rounded-2xl text-xs font-black uppercase transition shadow-sm" title="Lihat dan pulihkan modul yang terhapus">
                        <i class="fas fa-trash-restore text-rose-600 text-xs"></i> Sampah Modul ({{ $trashedModules->count() }})
                    </button>
                    @endif
                    <a href="{{ route('guru.lms.modules.create', $course->id) }}" class="inline-flex items-center justify-center gap-2.5 bg-white border-2 border-black text-black px-5 py-2.5 rounded-2xl text-xs font-black uppercase transition hover:bg-amber-300 shadow-sm">
                        <i class="fas fa-plus text-black text-xs"></i> Tambah Modul
                    </a>
                    <button @click="$dispatch('open-game-modal')" class="inline-flex items-center justify-center gap-2.5 bg-black hover:bg-purple-600 text-white border-2 border-black px-5 py-2.5 rounded-2xl text-xs font-black uppercase transition shadow-md">
                        <i class="fas fa-gamepad text-amber-400 text-xs"></i> Buat Game
                    </button>
                    <button @click="$dispatch('open-material-modal')" class="inline-flex items-center justify-center gap-2.5 bg-black hover:bg-emerald-600 text-white border-2 border-black px-5 py-2.5 rounded-2xl text-xs font-black uppercase transition shadow-md">
                        <i class="fas fa-upload text-amber-400 text-xs"></i> Upload Materi
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
                    <div class="flex items-center gap-1.5">
                        @if(!$loop->first)
                        <form action="{{ route('guru.lms.modules.move', $module->id) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="direction" value="up">
                            <button type="submit" class="w-8 h-8 bg-slate-800 rounded-xl flex items-center justify-center text-amber-400 hover:bg-amber-400 hover:text-black transition-all border border-slate-700 shadow-xs" title="Geser Urutan ke Atas (▲)">
                                <i class="fas fa-chevron-up text-xs"></i>
                            </button>
                        </form>
                        @endif

                        @if(!$loop->last)
                        <form action="{{ route('guru.lms.modules.move', $module->id) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="direction" value="down">
                            <button type="submit" class="w-8 h-8 bg-slate-800 rounded-xl flex items-center justify-center text-amber-400 hover:bg-amber-400 hover:text-black transition-all border border-slate-700 shadow-xs" title="Geser Urutan ke Bawah (▼)">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('guru.lms.modules.edit', $module->id) }}" class="w-8 h-8 bg-slate-800 rounded-xl flex items-center justify-center text-amber-400 hover:bg-amber-400 hover:text-black transition-all border border-slate-700" title="Edit Modul">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                        <form action="{{ route('guru.lms.modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Hapus modul ini? Modul dan materinya akan diarsipkan ke tempat sampah dan dapat dipulihkan kapan saja.')" class="inline">
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
                                        @if($material->file_path && !$material->fileExists())
                                        <span class="text-[10px] font-black bg-rose-500 text-white px-2 py-0.5 rounded-lg border border-black inline-flex items-center gap-1 shadow-2xs animate-pulse">
                                            <i class="fas fa-exclamation-triangle"></i> Berkas Fisik Belum Ada
                                        </span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-black font-black uppercase tracking-wider">{{ $material->getContentTypeLabel() }}{{ $material->file_size ? ' • ' . number_format($material->file_size / 1024, 0) . ' KB' : '' }}</p>
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
                                @if($material->file_path && $material->fileExists())
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
                            @if($material->file_path && !$material->fileExists())
                            <div class="mt-4 p-4 rounded-2xl border-2 border-rose-500 bg-rose-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-black border border-black shrink-0">
                                        <i class="fas fa-exclamation-circle text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-rose-900 text-xs uppercase tracking-wide">Peringatan: Berkas Fisik Tidak Ditemukan di Disk Server</p>
                                        <p class="text-xs text-rose-700 font-semibold">Berkas lampiran materi ini (ukuran DB: {{ $material->file_size ? number_format($material->file_size / (1024 * 1024), 2) . ' MB' : '-' }}) belum ada di penyimpanan server sehingga siswa tidak dapat mengunduhnya.</p>
                                    </div>
                                </div>
                                <button type="button" @click.stop="$dispatch('open-edit-material-modal', {{ json_encode([
                                    'id' => $material->id,
                                    'module_id' => $material->module_id,
                                    'title' => preg_replace('/^\d+\.\d+\s*/', '', $material->title),
                                    'material_type' => $material->material_type,
                                    'content' => $material->content ?? '',
                                    'file_url' => $material->file_url,
                                    'update_url' => route('guru.lms.materials.update', $material->id)
                                ]) }})" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black rounded-xl border border-black shadow-sm flex items-center gap-1.5 shrink-0">
                                    <i class="fas fa-upload text-amber-300"></i> Unggah Ulang Berkas
                                </button>
                            </div>
                            @endif

                            {{-- Media Players --}}
                            <div class="mt-4 mb-3">
                                @if($material->material_type === 'video')
                                    @if($material->isYouTubeVideo())
                                        <div class="w-full rounded-2xl overflow-hidden shadow-lg border-2 border-black bg-black mb-4" style="height: 560px; width: 100%;">
                                            <iframe class="w-full h-full" src="{{ $material->getVideoEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                        </div>
                                    @else
                                        @if($material->fileExists())
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
                                        @else
                                        <div class="p-6 rounded-2xl border-2 border-dashed border-rose-300 bg-rose-50/70 text-center flex flex-col items-center justify-center mb-4">
                                            <i class="fas fa-video-slash text-rose-500 text-3xl mb-2"></i>
                                            <p class="font-black text-rose-900 text-sm">Berkas Video Belum Tersedia di Disk</p>
                                        </div>
                                        @endif
                                    @endif
                                @elseif($material->material_type === 'image')
                                    @if($material->fileExists())
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border-2 border-black bg-black flex justify-center p-2">
                                        <img src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" class="max-h-[450px] object-contain w-auto h-auto rounded-xl" alt="{{ $material->title }}">
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ $material->file_path ? route('guru.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-5 py-2.5 rounded-2xl bg-black hover:bg-emerald-600 text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black" onclick="event.stopPropagation()">
                                            <i class="fas fa-download text-amber-400"></i> Unduh Berkas Gambar
                                        </a>
                                    </div>
                                    @else
                                    <div class="p-6 rounded-2xl border-2 border-dashed border-rose-300 bg-rose-50/70 text-center flex flex-col items-center justify-center mb-4">
                                        <i class="fas fa-image text-rose-500 text-3xl mb-2"></i>
                                        <p class="font-black text-rose-900 text-sm">Berkas Gambar Belum Tersedia di Disk</p>
                                    </div>
                                    @endif
                                @elseif(($material->material_type === 'pdf' || str_ends_with(strtolower($material->file_name ?? $material->file_path ?? ''), '.pdf') || str_contains(strtolower($material->title ?? ''), '[pdf]')) && strtolower(pathinfo($material->file_name ?? $material->file_path ?? '', PATHINFO_EXTENSION)) === 'pdf')
                                    @if($material->fileExists())
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border-2 border-black bg-white mb-4" style="height: 650px;">
                                        <iframe src="{{ route('guru.lms.materials.view', $material->id) }}" class="w-full h-full" frameborder="0"></iframe>
                                    </div>

                                    <div class="p-4 rounded-2xl border-2 border-black flex items-center justify-between gap-4" style="background-color: #fee2e2 !important;">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #dc2626 !important; color: #ffffff !important;">
                                                <i class="fas fa-file-pdf text-2xl text-white"></i>
                                            </div>
                                            <div>
                                                <p class="font-black text-black text-sm">Dokumen PDF Terlampir</p>
                                                <p class="text-xs text-black font-bold">Ukuran Berkas: {{ $material->file_size ? number_format($material->file_size / (1024 * 1024), 2) . ' MB' : 'Tidak diketahui' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('guru.lms.materials.view', $material->id) }}" target="_blank" class="px-4 py-2.5 rounded-2xl bg-white border-2 border-black text-black hover:bg-amber-300 font-black text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-external-link-alt text-xs"></i> Buka di Tab Baru
                                            </a>
                                            <a href="{{ route('guru.lms.materials.download', $material->id) }}" download class="px-4 py-2.5 rounded-2xl text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black" style="background-color: #dc2626 !important;" onclick="event.stopPropagation()">
                                                <i class="fas fa-download text-white"></i> Unduh PDF
                                            </a>
                                        </div>
                                    </div>
                                    @else
                                    <div class="p-6 rounded-2xl border-2 border-dashed border-rose-300 bg-rose-50/70 text-center flex flex-col items-center justify-center mb-4">
                                        <i class="fas fa-file-excel text-rose-500 text-3xl mb-2"></i>
                                        <p class="font-black text-rose-900 text-sm">Berkas PDF Fisik Belum Tersedia di Server Disk</p>
                                        <p class="text-xs text-rose-700 mt-1">Ukuran tercatat di database: {{ $material->file_size ? number_format($material->file_size / (1024 * 1024), 2) . ' MB' : '-' }}</p>
                                    </div>
                                    @endif
                                @elseif($material->material_type === 'link')
                                    <div class="p-4 rounded-2xl border-2 border-black flex items-center justify-between gap-4" style="background-color: #f3e8ff !important;">
                                        <div class="flex items-center gap-4">
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
                                    @php $hasPhysicalFile = $material->fileExists(); @endphp
                                    <div class="p-4 rounded-2xl border-2 border-black flex items-center justify-between gap-4 mb-4" style="background-color: {{ $hasPhysicalFile ? '#e0f2fe' : '#fee2e2' }} !important;">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: {{ $hasPhysicalFile ? '#0284c7' : '#dc2626' }} !important; color: #ffffff !important;">
                                                <i class="fas {{ $hasPhysicalFile ? 'fa-file-alt' : 'fa-exclamation-triangle' }} text-2xl text-white"></i>
                                            </div>
                                            <div>
                                                <p class="font-black text-black text-sm">Dokumen Terlampir: {{ $material->title ?: 'File Materi' }}</p>
                                                <p class="text-xs text-black font-bold">
                                                    Tipe: {{ strtoupper(pathinfo($material->file_path, PATHINFO_EXTENSION)) }}{{ $material->file_size ? ' • ' . number_format($material->file_size / 1024, 0) . ' KB' : '' }}
                                                    @if(!$hasPhysicalFile)
                                                        • <span class="text-rose-700">⚠️ Berkas fisik belum ada di server</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($hasPhysicalFile)
                                            <a href="{{ route('guru.lms.materials.view', $material->id) }}" target="_blank" class="px-4 py-2.5 rounded-2xl bg-white border-2 border-black text-black hover:bg-amber-300 font-black text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-external-link-alt text-xs"></i> Buka / Preview
                                            </a>
                                            <a href="{{ route('guru.lms.materials.download', $material->id) }}" download class="px-4 py-2.5 rounded-2xl text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black" style="background-color: #0284c7 !important;" onclick="event.stopPropagation()">
                                                <i class="fas fa-download text-white"></i> Unduh File
                                            </a>
                                            @else
                                            <button type="button" @click.stop="$dispatch('open-edit-material-modal', {{ json_encode([
                                                'id' => $material->id,
                                                'module_id' => $material->module_id,
                                                'title' => preg_replace('/^\d+\.\d+\s*/', '', $material->title),
                                                'material_type' => $material->material_type,
                                                'content' => $material->content ?? '',
                                                'file_url' => $material->file_url,
                                                'update_url' => route('guru.lms.materials.update', $material->id)
                                            ]) }})" class="px-4 py-2.5 rounded-2xl text-white font-black text-xs transition-all shadow-md flex items-center gap-1.5 border-2 border-black bg-rose-600 hover:bg-rose-700">
                                                <i class="fas fa-upload text-white"></i> Unggah Ulang Berkas
                                            </button>
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
                    <div class="rounded-2xl border-2 border-black px-5 py-4 flex items-center justify-between shadow-sm" style="background-color: #f3e8ff !important;">
                        <div class="flex items-center gap-4">
                            <span class="w-12 h-12 rounded-2xl bg-black flex items-center justify-center text-amber-400 shadow-md border-2 border-black shrink-0">
                                <i class="fas fa-gamepad text-xl"></i>
                            </span>
                            <div>
                                <p class="font-black text-black text-base flex flex-wrap items-center gap-2.5">
                                    <span>{{ $game->title }}</span>
                                    <span class="bg-white text-black text-[10px] font-black px-2.5 py-0.5 rounded-xl uppercase border-2 border-black shadow-xs">{{ str_replace('_', ' ', $game->game_type) }}</span>
                                </p>
                                <p class="text-xs text-black font-black uppercase mt-1 flex items-center gap-1.5"><i class="fas fa-star text-amber-500 text-xs"></i> REWARD: {{ $game->reward_points }} EXP</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(in_array($game->game_type, ['quiz', 'true_false']))
                            <form action="{{ route('guru.lms.games.live.create', $game->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="w-auto px-4 h-10 rounded-2xl flex items-center justify-center bg-black text-white hover:bg-emerald-600 transition-colors font-black text-xs border-2 border-black shadow-sm gap-2" title="Jalankan Mode Multiplayer Live">
                                    <i class="fas fa-satellite-dish text-amber-400"></i> Host Live Game
                                </button>
                            </form>
                            @endif
                            <button type="button" @click="$dispatch('open-game-player', { id: {{ $game->id }}, type: '{{ $game->game_type }}', title: '{{ addslashes($game->title) }}', data: {{ json_encode($game->game_data) }}, reward: {{ $game->reward_points }}, time_limit: {{ $game->time_limit ?: 'null' }}, lives_count: {{ $game->lives_count ?: 'null' }}, is_preview: true })" class="w-10 h-10 rounded-2xl flex items-center justify-center bg-white text-black hover:bg-indigo-400 transition-colors border-2 border-black shadow-sm group" title="Preview Game">
                                <i class="fas fa-play text-xs text-indigo-600 group-hover:text-white"></i>
                            </button>
                            <button type="button" @click="$dispatch('open-edit-game-modal', {{ json_encode($game) }})" class="w-10 h-10 rounded-2xl flex items-center justify-center bg-white text-black hover:bg-amber-400 transition-colors border-2 border-black shadow-sm" title="Edit Judul, Soal & Jawaban Game">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <form action="{{ route('guru.lms.games.destroy', $game->id) }}" method="POST" onsubmit="return confirm('Hapus game ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="w-10 h-10 rounded-2xl flex items-center justify-center bg-white text-black hover:bg-rose-600 hover:text-white transition-colors border-2 border-black shadow-sm" title="Hapus Game"><i class="fas fa-trash text-xs"></i></button>
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

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        {{-- TAB: ASSIGNMENTS --}}
        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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
                                        {{ $assignment->module->getCode() }} • {{ $assignment->module->title }}
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-black text-[9px] font-black px-2 py-0.5 rounded-lg border border-black uppercase tracking-wider">Global</span>
                                @endif
                                @if($assignment->allow_resubmit)
                                <span class="bg-emerald-200 text-black text-[9px] font-black px-2 py-0.5 rounded-lg border border-black uppercase">REVISI DIIZINKAN</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2 text-[10px] font-black uppercase tracking-wider mt-2.5">
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

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        {{-- TAB: QUIZZES --}}
        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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
                                        {{ $quiz->module->getCode() }} • {{ $quiz->module->title }}
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-black text-[9px] font-black px-2 py-0.5 rounded-lg border border-black uppercase tracking-wider">Global</span>
                                @endif
                                <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border border-black {{ $quiz->is_published ? 'bg-emerald-300 text-black' : 'bg-amber-200 text-black' }}">
                                    {{ $quiz->is_published ? 'PUBLISHED' : 'DRAFT' }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-2 text-[10px] font-black uppercase tracking-wider mt-2.5">
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

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        {{-- TAB: ANNOUNCEMENTS --}}
        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        {{-- TAB: DISCUSSIONS --}}
        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        {{-- TAB: COURSE MASTER GROUPS (KELOMPOK BELAJAR) --}}
        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        @php
            $rawCourseMasterGroups = $course->courseGroups()->with(['leader.major', 'members.major'])->get();
            $enrolledStudentIds = $allEnrolledStudents->pluck('id')->toArray();
            $studentClassMap = $allEnrolledStudents->keyBy('id');

            // Saring kelompok untuk tampilan: hanya tampilkan kelompok yang memuat siswa sah kursus ini
            $courseMasterGroups = $rawCourseMasterGroups->filter(function($grp) use ($enrolledStudentIds) {
                return in_array($grp->leader_id, $enrolledStudentIds) ||
                       $grp->members->pluck('id')->intersect($enrolledStudentIds)->isNotEmpty();
            });

            $groupedStudentIdsInCourse = $courseMasterGroups->flatMap(function($grp) use ($enrolledStudentIds) {
                return $grp->members->pluck('id')->push($grp->leader_id);
            })->filter(fn($id) => in_array($id, $enrolledStudentIds))->unique()->values()->toArray();

            $availableStudentsInCourse = $allEnrolledStudents->reject(fn($s) => in_array($s->id, $groupedStudentIdsInCourse))->values();

            // Data kelompok dan rombel untuk filter interaktif Alpine.js
            $groupsJsData = $courseMasterGroups->map(function($grp) use ($enrolledStudentIds, $studentClassMap) {
                $validM = $grp->members->filter(fn($m) => in_array($m->id, $enrolledStudentIds));
                $cIds = $validM->map(fn($m) => (int)($studentClassMap[$m->id]->classroom_id ?? $m->studentClasses->first()?->classroom_id ?? 0))
                    ->when(in_array($grp->leader_id, $enrolledStudentIds), fn($col) => $col->push((int)($studentClassMap[$grp->leader_id]->classroom_id ?? $grp->leader?->studentClasses->first()?->classroom_id ?? 0)))
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->toArray();
                return [
                    'id' => $grp->id,
                    'classroom_ids' => $cIds,
                ];
            })->values()->toArray();

            // Rekapitulasi statistik siswa per rombel
            $classStats = [
                'all' => [
                    'total' => $allEnrolledStudents->count(),
                    'grouped' => count($groupedStudentIdsInCourse),
                    'available' => $availableStudentsInCourse->count(),
                ]
            ];
            if (isset($classrooms)) {
                foreach ($classrooms as $c) {
                    $studentsInC = $allEnrolledStudents->filter(fn($s) => $s->classroom_id == $c->id || strtolower($s->classroom_name ?? '') === strtolower($c->name));
                    $sIdsInC = $studentsInC->pluck('id')->toArray();
                    $groupedInC = array_intersect($sIdsInC, $groupedStudentIdsInCourse);
                    $classStats[(string)$c->id] = [
                        'total' => count($sIdsInC),
                        'grouped' => count($groupedInC),
                        'available' => count($sIdsInC) - count($groupedInC),
                    ];
                }
            }

            $availableStudentsJson = $availableStudentsInCourse->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->user->name ?? $s->full_name,
                'classroom' => $s->classroom_name ?? 'Kelas',
                'classroom_id' => $s->classroom_id ?? 0,
            ])->values()->all();
        @endphp
        <div x-show="tab === 'groups'" class="mt-6 space-y-6 tab-content" 
             x-data="{ 
                 showAddGroup: false, 
                 showAutoGroup: false, 
                 showImportExcel: false, 
                 editGroupModal: false,
                 editingGroup: { id: null, name: '', theme: '', leader_id: null, member_ids: [] },
                 editingCandidates: [],
                 availablePool: {{ json_encode($availableStudentsJson) }},
                 selectedClassFilter: '', 
                 manualClassFilter: '',
                 autoClassFilter: '',
                 groupsData: {{ json_encode($groupsJsData) }},
                 statsData: {{ json_encode($classStats) }},
                 getCurrentStats() {
                     if (this.selectedClassFilter && this.statsData[this.selectedClassFilter]) {
                         return this.statsData[this.selectedClassFilter];
                     }
                     return this.statsData['all'] || { total: 0, grouped: 0, available: 0 };
                 },
                 hasVisibleGroups() {
                     if (!this.selectedClassFilter) return this.groupsData.length > 0;
                     const cid = parseInt(this.selectedClassFilter);
                     return this.groupsData.some(g => g.classroom_ids.includes(cid));
                 },
                 openEditGroupModal(data) {
                     this.editingGroup = {
                         id: data.id,
                         name: data.name,
                         theme: data.theme || '',
                         leader_id: data.leader_id,
                         member_ids: [...data.member_ids]
                     };
                     const existingIds = new Set((data.members_info || []).map(m => m.id));
                     const extra = this.availablePool.filter(s => !existingIds.has(s.id));
                     this.editingCandidates = [...(data.members_info || []), ...extra];
                     this.editGroupModal = true;
                 },
                 closeEditGroupModal() {
                     this.editGroupModal = false;
                     this.editingGroup = { id: null, name: '', theme: '', leader_id: null, member_ids: [] };
                     this.editingCandidates = [];
                 },
                 toggleMember(id) {
                     const numId = parseInt(id);
                     const idx = this.editingGroup.member_ids.indexOf(numId);
                     if (idx > -1) {
                         if (parseInt(this.editingGroup.leader_id) === numId) {
                             alert('Ketua kelompok wajib menjadi bagian dari anggota kelompok.');
                             return;
                         }
                         this.editingGroup.member_ids.splice(idx, 1);
                     } else {
                         this.editingGroup.member_ids.push(numId);
                     }
                 },
                 onLeaderChange() {
                     const lid = parseInt(this.editingGroup.leader_id);
                     if (lid && !this.editingGroup.member_ids.includes(lid)) {
                         this.editingGroup.member_ids.push(lid);
                     }
                 }
             }">
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
                        <button type="button" @click="showAutoGroup = !showAutoGroup; showAddGroup = false; showImportExcel = false; if(selectedClassFilter) autoClassFilter = selectedClassFilter"
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
                        <button type="button" @click="selectedClassFilter = ''; manualClassFilter = ''; autoClassFilter = ''"
                                :class="selectedClassFilter === '' ? 'bg-purple-600 text-white border-black shadow-sm' : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200'"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold border transition">
                            Semua Kelas ({{ $allEnrolledStudents->count() }})
                        </button>
                        @foreach($classrooms as $c)
                        @php
                            $countInClass = $allEnrolledStudents->filter(fn($s) => $s->classroom_id == $c->id || strtolower($s->classroom_name ?? '') === strtolower($c->name))->count();
                        @endphp
                        <button type="button" @click="selectedClassFilter = '{{ $c->id }}'; manualClassFilter = '{{ $c->id }}'; autoClassFilter = '{{ $c->id }}'"
                                :class="selectedClassFilter == '{{ $c->id }}' ? 'bg-purple-600 text-white border-black shadow-sm' : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200'"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold border transition">
                            {{ $c->name }} ({{ $countInClass }})
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Status Stats Bar (Dinamis Sesuai Filter Rombel) --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5">
                    <div class="bg-slate-50 border-2 border-black rounded-2xl p-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 border border-black flex items-center justify-center font-black">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-500">Total Siswa Terdaftar</div>
                            <div class="text-lg font-black text-black" x-text="getCurrentStats().total + ' Orang'">{{ $allEnrolledStudents->count() }} Orang</div>
                        </div>
                    </div>
                    <div class="bg-emerald-50 border-2 border-black rounded-2xl p-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-200 text-emerald-800 border border-black flex items-center justify-center font-black">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-500">Sudah Masuk Kelompok</div>
                            <div class="text-lg font-black text-emerald-800" x-text="getCurrentStats().grouped + ' Orang'">{{ count($groupedStudentIdsInCourse) }} Orang</div>
                        </div>
                    </div>
                    <div class="bg-amber-50 border-2 border-black rounded-2xl p-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-200 text-amber-900 border border-black flex items-center justify-center font-black">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-500">Belum Punya Kelompok</div>
                            <div class="text-lg font-black text-amber-900" x-text="getCurrentStats().available + ' Orang'">{{ $availableStudentsInCourse->count() }} Orang</div>
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
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Nama Kelompok <span class="text-rose-600">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Kelompok 1" value="Kelompok {{ $courseMasterGroups->count() + 1 }}"
                                       class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Tema / Judul Proyek (Opsional)</label>
                                <input type="text" name="theme" placeholder="Contoh: Rancang Bangun Smart Home"
                                       class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Pilih Ketua Kelompok <span class="text-rose-600">* (Koordinator)</span></label>
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
                                <select name="classroom_id" x-model="autoClassFilter" class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-amber-500 outline-none bg-white">
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
                            <div class="col-span-full pt-1">
                                <label class="flex items-center gap-2 text-xs font-bold text-amber-950 cursor-pointer">
                                    <input type="checkbox" name="replace_existing" value="1" class="rounded text-amber-600 focus:ring-0">
                                    <span>Hapus & bagi ulang seluruh {{ $allEnrolledStudents->count() }} siswa dari awal (reset kelompok lama)</span>
                                </label>
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
                @php
                    $validMembers = $grp->members->filter(fn($m) => in_array($m->id, $enrolledStudentIds));
                    $isValidLeader = in_array($grp->leader_id, $enrolledStudentIds);
                    $grpClassroomIds = $validMembers->map(fn($m) => (int)($studentClassMap[$m->id]->classroom_id ?? $m->studentClasses->first()?->classroom_id ?? 0))
                        ->when($isValidLeader && $grp->leader_id, fn($col) => $col->push((int)($studentClassMap[$grp->leader_id]->classroom_id ?? $grp->leader?->studentClasses->first()?->classroom_id ?? 0)))
                        ->filter(fn($id) => $id > 0)
                        ->unique()
                        ->values()
                        ->toArray();

                    $primaryClassroomId = $grpClassroomIds[0] ?? null;
                    $primaryClassroom = $primaryClassroomId && isset($classrooms) ? $classrooms->firstWhere('id', $primaryClassroomId) : null;
                    $primaryClassName = $primaryClassroom?->name ?? null;

                    $grpEditJson = [
                        'id' => $grp->id,
                        'name' => $grp->name,
                        'theme' => $grp->theme ?? '',
                        'leader_id' => $grp->leader_id,
                        'member_ids' => $validMembers->pluck('id')->values()->all(),
                        'members_info' => $validMembers->map(fn($m) => [
                            'id' => $m->id,
                            'name' => $m->user->name ?? $m->full_name,
                            'classroom' => $m->classroom_name ?? 'Kelas',
                            'classroom_id' => $m->classroom_id ?? 0,
                        ])->values()->all(),
                    ];
                @endphp
                <div x-show="!selectedClassFilter || {{ json_encode($grpClassroomIds) }}.includes(parseInt(selectedClassFilter))"
                     class="p-5 rounded-2xl border-2 border-black bg-white hover:border-purple-600 transition-all shadow-md flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-black text-purple-900 uppercase tracking-wider bg-purple-100 px-3 py-1 rounded-xl border border-purple-300">
                                    {{ $grp->name }}
                                </span>
                                @if($primaryClassName)
                                <span class="text-[10px] font-black text-blue-900 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-lg shrink-0">
                                    <i class="fas fa-chalkboard text-blue-600 mr-1"></i>{{ $primaryClassName }}
                                </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click='openEditGroupModal(@json($grpEditJson))'
                                        class="p-1.5 rounded-lg text-gray-500 hover:text-purple-700 hover:bg-purple-50 transition border border-transparent hover:border-purple-200" title="Edit Kelompok & Tema">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <form action="{{ route('guru.lms.groups.destroy', [$course->id, $grp->id]) }}" method="POST" onsubmit="return confirm('Hapus kelompok {{ $grp->name }} dari kursus?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition border border-transparent hover:border-rose-200" title="Hapus Kelompok">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if(!empty($grp->theme))
                        <div class="text-xs font-black text-purple-900 bg-purple-50/90 border border-purple-200 px-3 py-1.5 rounded-xl flex items-center gap-2 mb-3">
                            <i class="fas fa-lightbulb text-amber-500 shrink-0"></i>
                            <span class="truncate"><span class="text-purple-600 font-bold uppercase text-[10px] mr-1">Tema / Proyek:</span>{{ $grp->theme }}</span>
                        </div>
                        @endif

                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-1.5 font-black text-gray-900">
                                <span class="text-amber-500">👑 Ketua:</span>
                                @if($isValidLeader && $grp->leader)
                                    @php
                                        $leaderClassroom = $grp->leader->major?->code ?? $grp->leader->currentClassroom()->first()?->class_name ?? $grp->leader->classroom_name ?? null;
                                    @endphp
                                    @if($leaderClassroom)
                                    <span class="bg-purple-100 text-purple-800 text-[10px] px-1.5 py-0.5 rounded font-black border border-purple-200 shrink-0">{{ $leaderClassroom }}</span>
                                    @endif
                                    <span class="truncate">{{ $grp->leader->user?->name ?? $grp->leader->full_name }}</span>
                                @elseif($validMembers->isNotEmpty())
                                    <span class="text-amber-600 italic font-medium truncate">{{ $validMembers->first()->user?->name ?? $validMembers->first()->full_name }} (Diusulkan)</span>
                                @else
                                    <span class="text-gray-400 italic">Belum ditentukan</span>
                                @endif
                            </div>
                            <div class="text-gray-600 font-bold text-[11px] pt-1">
                                <span class="block mb-1">Anggota ({{ $validMembers->count() }} orang):</span>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($validMembers as $mem)
                                    @php
                                        $memClassroom = $mem->major?->code ?? $mem->currentClassroom()->first()?->class_name ?? $mem->classroom_name ?? null;
                                    @endphp
                                    <span class="bg-gray-100 border border-gray-300 text-gray-800 text-[10px] font-bold px-2 py-0.5 rounded-md flex items-center gap-1">
                                        @if($memClassroom)
                                        <span class="text-purple-700 font-black">[{{ $memClassroom }}]</span>
                                        @endif
                                        <span>{{ $mem->user->name ?? $mem->full_name }}</span>
                                    </span>
                                    @empty
                                    <span class="text-gray-400 italic text-[10px]">Tidak ada siswa sah di kelompok ini</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if(isset($classrooms) && $classrooms->count() > 1)
                <div x-show="selectedClassFilter && !hasVisibleGroups()" class="col-span-full py-10 text-center bg-purple-50/50 rounded-3xl border-2 border-dashed border-purple-300 p-6">
                    <div class="w-14 h-14 bg-purple-100 text-purple-700 border-2 border-purple-300 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-users-slash text-xl"></i>
                    </div>
                    <h4 class="text-sm font-black text-purple-950">Belum Ada Kelompok untuk Rombel Terpilih</h4>
                    <p class="text-xs font-bold text-purple-700 max-w-md mx-auto mt-1 mb-4">
                        Rombel yang dipilih belum memiliki master kelompok. Klik "Bagi Otomatis" atau "Tambah Kelompok Manual" untuk membentuk kelompok khusus kelas ini.
                    </p>
                    <button type="button" @click="showAutoGroup = true; autoClassFilter = selectedClassFilter; showAddGroup = false; showImportExcel = false"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-amber-400 text-black hover:bg-amber-500 border-2 border-black transition shadow-sm">
                        <i class="fas fa-magic"></i> Bagi Otomatis untuk Rombel Ini
                    </button>
                </div>
                @endif
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

            {{-- MODAL EDIT KELOMPOK MASTER KURSUS --}}
            <div x-show="editGroupModal" x-cloak 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
                 @keydown.escape.window="closeEditGroupModal()">
                <div class="bg-white rounded-3xl border-2 border-black shadow-2xl max-w-2xl w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto animate-fadeUp"
                     @click.away="closeEditGroupModal()">
                    <div class="flex items-center justify-between border-b-2 border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 border border-purple-300 flex items-center justify-center font-black">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-black uppercase tracking-wider">Edit Master Kelompok Kursus</h3>
                                <p class="text-[11px] font-bold text-gray-500">Ubah nama kelompok, tema/proyek, ketua, atau susunan anggota.</p>
                            </div>
                        </div>
                        <button type="button" @click="closeEditGroupModal()" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-black transition flex items-center justify-center">✕</button>
                    </div>

                    <form :action="'{{ url('guru/lms/' . $course->id . '/groups') }}/' + editingGroup.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Nama Kelompok <span class="text-rose-600">*</span></label>
                                <input type="text" name="name" x-model="editingGroup.name" required
                                       class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-800 mb-1">Tema / Judul Proyek (Opsional)</label>
                                <input type="text" name="theme" x-model="editingGroup.theme" placeholder="Contoh: Rancang Bangun IoT"
                                       class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-800 mb-1">Pilih Ketua Kelompok <span class="text-rose-600">*</span></label>
                            <select name="leader_id" x-model="editingGroup.leader_id" @change="onLeaderChange()" required
                                    class="w-full border-2 border-black rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                                <option value="">— Pilih Ketua Kelompok —</option>
                                <template x-for="c in editingCandidates" :key="'lead-' + c.id">
                                    <option :value="c.id" x-text="'[' + (c.classroom || 'Kelas') + '] ' + c.name" :selected="c.id == editingGroup.leader_id"></option>
                                </template>
                            </select>
                            <p class="text-[10px] text-gray-500 font-bold mt-1">*Ketua kelompok otomatis terdaftar sebagai anggota kelompok.</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-black text-gray-800">Daftar Anggota Kelompok (Centang Siswa):</label>
                                <span class="text-[11px] font-bold text-purple-700" x-text="editingGroup.member_ids.length + ' Anggota Terpilih'"></span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto p-3 bg-slate-50 border-2 border-black rounded-xl">
                                <template x-for="c in editingCandidates" :key="'mem-' + c.id">
                                    <label class="flex items-center gap-2 text-xs font-bold text-gray-700 hover:bg-purple-100 p-2 rounded-xl border border-transparent hover:border-purple-200 cursor-pointer transition select-none"
                                           :class="editingGroup.member_ids.includes(c.id) ? 'bg-purple-50 border-purple-200' : ''">
                                        <input type="checkbox" name="member_ids[]" :value="c.id"
                                               :checked="editingGroup.member_ids.includes(c.id)"
                                               @click="toggleMember(c.id)"
                                               class="rounded text-purple-600 focus:ring-0">
                                        <span class="bg-blue-100 text-blue-800 text-[10px] px-1.5 py-0.5 rounded font-black border border-blue-200 shrink-0" x-text="c.classroom || 'Kelas'"></span>
                                        <span class="truncate flex-1" x-text="c.name"></span>
                                        <span x-show="c.id == editingGroup.leader_id" class="text-[10px] font-black text-amber-600 bg-amber-100 px-1.5 py-0.5 rounded border border-amber-200 shrink-0">👑 Ketua</span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="closeEditGroupModal()" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-black bg-purple-600 text-white hover:bg-purple-700 border-2 border-black shadow-sm transition">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan Kelompok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        {{-- TAB: ANALYTICS --}}
        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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
                    <div class="flex items-center gap-4">
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
                    <div class="flex items-center gap-4">
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
                    <div class="flex items-center gap-4">
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
                    <div class="flex items-center gap-4">
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

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        {{-- TAB: INFO / CLASS DATA --}}
        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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
                                        <span>•</span>
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

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- GAME BUILDER MODAL --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div x-data="gameBuilder()" 
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
                            <div x-show="gameType === 'flashcard'" class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-indigo-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-brain text-amber-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-indigo-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-layer-group text-indigo-600"></i> Panduan Game: Flashcard 3D
                                            </h5>
                                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-black px-2.5 py-0.5 rounded-full border border-emerald-300">
                                                <i class="fas fa-check-circle"></i> Sistem EXP: Tuntas Belajar (100% EXP)
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Menggunakan metode ilmiah <em>Active Recall & Spaced Repetition</em>. Sangat ampuh melatih daya ingat aktif siswa terhadap definisi, istilah asing, formula/rumus, dan poin materi penting.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa membaca pertanyaan/istilah di depan kartu, mencoba mengingat jawabannya di pikiran, lalu <strong>mengklik kartu untuk membalik</strong> dan mencocokkan dengan jawaban di belakang. Siswa lalu menekan tombol kejujuran <em>"Sudah Hafal"</em> atau <em>"Belum Hafal"</em>.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Karena Flashcard adalah media belajar mandiri (bukan ujian), siswa yang menuntaskan seluruh kartu hingga selesai akan otomatis mendapatkan <strong>100% Reward EXP</strong> penuh sebagai apresiasi ketekunan belajar (maksimal 1x per siswa).
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div x-show="gameType === 'match'" class="mb-4 p-4 bg-gradient-to-r from-violet-50 to-purple-50 border-2 border-violet-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-violet-600 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-puzzle-piece text-amber-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-violet-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-link text-violet-600"></i> Panduan Game: Cocokkan Pasangan (Match Pairs)
                                            </h5>
                                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-black px-2.5 py-0.5 rounded-full border border-emerald-300">
                                                <i class="fas fa-check-circle"></i> Sistem EXP: Tuntas Mencocokkan (100% EXP)
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Melatih asosiasi konsep, relasi sebab-akibat, pencocokan istilah dengan definisi, rumus dengan penjelasannya, atau sinonim/antonim secara interaktif.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa mengklik 1 kotak istilah di kolom kiri lalu mengklik kotak definisi pasangannya di kolom kanan. Pasangan yang benar akan menyatu dan hilang dari arena.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Siswa memperoleh <strong>100% Reward EXP</strong> penuh setelah berhasil menjodohkan semua pasangan kartu hingga habis.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <div class="mb-4 p-4 bg-gradient-to-r from-pink-50 to-rose-50 border-2 border-pink-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-pink-600 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-dharmachakra text-amber-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-pink-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-dharmachakra text-pink-600"></i> Panduan Game: Roda Putar (Spin Wheel)
                                            </h5>
                                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-black px-2.5 py-0.5 rounded-full border border-emerald-300">
                                                <i class="fas fa-check-circle"></i> Sistem EXP: Tuntas Memutar (100% EXP)
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Ice breaking seru, undian giliran presentasi, pembagian hadiah motivasi acak, atau pertanyaan tantangan spontan.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa menekan tombol putar dan roda akan berputar acak hingga berhenti di salah satu sektor item yang guru buat.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Siswa memperoleh <strong>100% Reward EXP</strong> setelah roda berhenti berputar (1x per siswa).
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <div class="mb-4 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-question text-amber-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-emerald-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-list-ol text-emerald-600"></i> Panduan Game: Kuis Pilihan Ganda (Quiz)
                                            </h5>
                                            <span class="text-[10px] bg-amber-100 text-amber-800 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
                                                <i class="fas fa-percentage"></i> Sistem EXP: Proporsional Nilai (% Benar)
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Ujian pemahaman materi formal, evaluasi bab, serta menguji ketepatan analisis siswa terhadap konsep pilihan ganda A/B/C/D.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa menjawab satu per satu soal. Terdapat combo streak beruntun dan efek visual interaktif saat menjawab benar.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Dihitung proporsional: <code>(Jawaban Benar / Total Soal) x Reward EXP + Combo Bonus</code>. Minimal dapat 10% EXP bagi yang telah berusaha menyelesaikan.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <div class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 border-2 border-blue-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-check-double text-amber-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-blue-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-check-circle text-blue-600"></i> Panduan Game: Benar atau Salah (True / False)
                                            </h5>
                                            <span class="text-[10px] bg-amber-100 text-amber-800 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
                                                <i class="fas fa-percentage"></i> Sistem EXP: Proporsional Nilai (% Benar)
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Menguji daya berpikir kritis (*Critical Thinking*), membedakan fakta vs miskonsepsi/hoaks, serta kecepatan analisa cepat.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa membaca pernyataan lalu menekan tombol hijau (BENAR) atau merah (SALAH) dalam hitungan detik.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Proporsional terhadap persentase ketepatan: <code>(Benar / Total Pernyataan) x Reward EXP</code>.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <div class="mb-4 p-4 bg-gradient-to-r from-amber-50 to-yellow-50 border-2 border-amber-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-keyboard text-slate-950"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-amber-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-keyboard text-amber-600"></i> Panduan Game: Tebak Kata (Hangman Style)
                                            </h5>
                                            <span class="text-[10px] bg-amber-100 text-amber-800 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
                                                <i class="fas fa-percentage"></i> Sistem EXP: Proporsional Kata Benar
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Melatih pengenalan kosakata, istilah ilmiah, ejaan tepat, dan asosiasi petunjuk (clue) dengan kata rahasia.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa menebak huruf satu per satu dari keyboard virtual. Setiap kesalahan mengurangi 1 nyawa (maksimal 5 kesempatan salah).
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Proporsional: <code>(Kata Berhasil Ditebak / Total Kata) x Reward EXP</code>.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <div class="mb-4 p-4 bg-gradient-to-r from-orange-50 to-amber-50 border-2 border-orange-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-orange-500 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-cubes text-amber-200"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-orange-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-cubes text-orange-600"></i> Panduan Game: Susun Kata (Scramble / Anagram)
                                            </h5>
                                            <span class="text-[10px] bg-amber-100 text-amber-800 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
                                                <i class="fas fa-percentage"></i> Sistem EXP: Proporsional Kata Benar
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Melatih rekognisi visual kata, pemecahan teka-teki anagram, serta penguasaan terminologi pembelajaran.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Huruf-huruf diacak dan siswa harus mengklik huruf demi huruf secara berurutan membentuk kata yang benar berdasarkan petunjuk.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Proporsional: <code>(Kata Berhasil Disusun / Total Kata) x Reward EXP</code>.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <div class="mb-4 p-4 bg-gradient-to-r from-cyan-50 to-sky-50 border-2 border-cyan-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-cyan-600 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-sort-amount-down text-amber-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-cyan-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-sort-amount-down text-cyan-600"></i> Panduan Game: Urutkan Langkah / Kronologi (Sequence)
                                            </h5>
                                            <span class="text-[10px] bg-amber-100 text-amber-800 font-black px-2.5 py-0.5 rounded-full border border-amber-300">
                                                <i class="fas fa-percentage"></i> Sistem EXP: Proporsional Kelompok Benar
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Mengasah logika prosedural, pemahaman rantai kronologi sejarah, tahapan praktikum/laboratorium, siklus biologis, atau alur algoritma.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa menyeret (*drag & drop*) kartu-kartu langkah agar tersusun berurutan dari urutan teratas (pertama) ke bawah (terakhir).
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Dihitung proporsional berdasarkan kelompok urutan yang berhasil disusun tepat.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <div class="mb-4 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-microscope text-amber-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-emerald-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-microscope text-emerald-600"></i> Panduan Game: Titik Buta Gambar (STEM Visual Hotspot)
                                            </h5>
                                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-black px-2.5 py-0.5 rounded-full border border-emerald-300">
                                                <i class="fas fa-check-circle"></i> Sistem EXP: Tuntas Identifikasi (100% EXP)
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Sangat ideal untuk Biologi (organ tubuh, sel, anatomi tumbuhan), Geografi (peta wilayah), atau Fisika/Teknik (komponen alat/mesin).
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa disajikan gambar diagram dan diminta menunjuk/mengklik lokasi koordinat titik yang sesuai dengan nama bagian yang ditanyakan.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Siswa memperoleh <strong>100% Reward EXP</strong> penuh setelah berhasil menandai semua titik target.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <div class="mb-4 p-4 bg-gradient-to-r from-sky-50 to-blue-50 border-2 border-sky-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-sky-600 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                        <i class="fas fa-flask text-amber-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <h5 class="font-black text-sky-950 text-xs sm:text-sm uppercase tracking-wide flex items-center gap-2">
                                                <i class="fas fa-flask text-sky-600"></i> Panduan Game: Reaksi Kimia (Chemistry Equation Balancer)
                                            </h5>
                                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-black px-2.5 py-0.5 rounded-full border border-emerald-300">
                                                <i class="fas fa-check-circle"></i> Sistem EXP: Tuntas Penyetaraan (100% EXP)
                                            </span>
                                        </div>
                                        <p class="text-slate-700">
                                            <strong>🎯 Tujuan & Manfaat:</strong> Melatih pemahaman stoikiometri, hukum Lavoisier (kekekalan massa), dan kemampuan aljabar dalam menyeimbangkan atom reaktan & produk.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa mengisi koefisien angka pada kotak kosong persamaan kimia hingga jumlah setiap unsur seimbang di kedua sisi panah.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Siswa memperoleh <strong>100% Reward EXP</strong> setelah berhasil menyetarakan persamaan reaksi kimia tersebut.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <div class="mb-4 p-4 bg-gradient-to-r from-purple-50 to-fuchsia-50 border-2 border-purple-100 rounded-2xl text-xs text-slate-800 leading-relaxed shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0 text-sm font-black shadow-sm mt-0.5">
                                            <strong>🕹️ Cara Main Siswa:</strong> Siswa ditantang menyelesaikan rangkaian operasi matematika secepat mungkin sebelum waktu habis.
                                        </p>
                                        <p class="text-slate-700">
                                            <strong>⭐ Penentuan Poin (EXP):</strong> Siswa memperoleh <strong>100% Reward EXP</strong> saat berhasil menuntaskan level tantangan Math Ninja.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
<div x-data="{ open: false, type: 'text', file_url: '', material_title: '', codeMode: false }" @open-material-modal.window="open = true; type = 'text'; codeMode = false; material_title = ''; file_url = ''; $nextTick(() => { if (typeof quillCreate !== 'undefined' && quillCreate) { quillCreate.root.innerHTML = ''; document.getElementById('quill-create-input').value = ''; const rawEl = document.getElementById('quill-create-raw-textarea'); if (rawEl) rawEl.value = ''; } })" x-show="open" class="fixed inset-0 overflow-y-auto" style="display: none; z-index: 99999 !important;">
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
                                <option value="text">📄 Artikel / Modul Teks Langsung (Disarankan)</option>
                                <option value="pdf">📑 Berkas PDF</option>
                                <option value="document">📝 Dokumen Word / PPT</option>
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
                                <button type="button" @click="codeMode = !codeMode; syncCodeMode('create', codeMode)" class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 text-black rounded-lg text-[11px] font-black border border-black transition flex items-center gap-1">
                                    <i class="fas fa-code text-indigo-600"></i> <span x-text="codeMode ? 'Mode Visual' : 'Mode HTML'"></span>
                                </button>
                                <button type="button" onclick="generateAiContent('create', '{{ addslashes($course->name ?? '') }}')" class="px-3 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-black transition-all shadow-md flex items-center gap-1 border border-black">
                                    <i class="fas fa-magic text-amber-300"></i> ✨ Buat via AI
                                </button>
                                <div class="hidden sm:inline-block text-[11px] font-bold text-slate-500">| Template:</div>
                                <button type="button" onclick="insertTemplate('create', 'summary')" class="px-2.5 py-1 bg-amber-200 hover:bg-amber-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    <i class="fas fa-bookmark text-amber-700"></i> Ringkasan
                                </button>
                                <button type="button" onclick="insertTemplate('create', 'lab')" class="px-2.5 py-1 bg-emerald-200 hover:bg-emerald-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    <i class="fas fa-flask text-emerald-700"></i> Praktikum
                                </button>
                                <button type="button" onclick="insertTemplate('create', 'case')" class="px-2.5 py-1 bg-sky-200 hover:bg-sky-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    <i class="fas fa-lightbulb text-sky-700"></i> Kasus
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="content" id="quill-create-input">
                        <div x-show="!codeMode" id="quill-create-editor" class="bg-white min-h-[220px] rounded-xl border border-slate-300"></div>
                        <textarea x-show="codeMode" id="quill-create-raw-textarea" class="w-full bg-slate-900 text-amber-300 font-mono text-xs p-4 rounded-xl border-2 border-black min-h-[240px] outline-none leading-relaxed" placeholder="Kode HTML artikel pembelajaran..." oninput="syncRawHtml('create', this.value)"></textarea>
                        <p class="text-[11px] text-slate-600 font-bold flex items-center gap-1">
                            <i class="fas fa-info-circle text-sky-600"></i> Format teks HTML (Heading, tabel, list, gambar inline, blok kode) tersimpan sempurna tanpa hilang saat diedit.
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
<div x-data="{ open: false, mat: {}, codeMode: false }" @open-edit-material-modal.window="mat = $event.detail; open = true; codeMode = false; $nextTick(() => setQuillEditContent(mat.content))" x-show="open" class="fixed inset-0 overflow-y-auto" style="display: none; z-index: 99999 !important;">
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
                                <option value="text">📄 Artikel / Modul Teks Langsung (Disarankan)</option>
                                <option value="pdf">📑 Berkas PDF</option>
                                <option value="document">📝 Dokumen Word / PPT</option>
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
                                <button type="button" @click="codeMode = !codeMode; syncCodeMode('edit', codeMode)" class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 text-black rounded-lg text-[11px] font-black border border-black transition flex items-center gap-1">
                                    <i class="fas fa-code text-indigo-600"></i> <span x-text="codeMode ? 'Mode Visual' : 'Mode HTML'"></span>
                                </button>
                                <button type="button" onclick="generateAiContent('edit', '{{ addslashes($course->name ?? '') }}')" class="px-3 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-black transition-all shadow-md flex items-center gap-1 border border-black">
                                    <i class="fas fa-magic text-amber-300"></i> ✨ Tulis Ulang via AI
                                </button>
                                <div class="hidden sm:inline-block text-[11px] font-bold text-slate-500">| Template:</div>
                                <button type="button" onclick="insertTemplate('edit', 'summary')" class="px-2.5 py-1 bg-amber-200 hover:bg-amber-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    <i class="fas fa-bookmark text-amber-700"></i> Ringkasan
                                </button>
                                <button type="button" onclick="insertTemplate('edit', 'lab')" class="px-2.5 py-1 bg-emerald-200 hover:bg-emerald-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    <i class="fas fa-flask text-emerald-700"></i> Praktikum
                                </button>
                                <button type="button" onclick="insertTemplate('edit', 'case')" class="px-2.5 py-1 bg-sky-200 hover:bg-sky-300 text-black rounded-lg text-[11px] font-black border border-black transition">
                                    <i class="fas fa-lightbulb text-sky-700"></i> Kasus
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="content" id="quill-edit-input" :value="mat.content">
                        <div x-show="!codeMode" id="quill-edit-editor" class="bg-white min-h-[220px] rounded-xl border border-slate-300"></div>
                        <textarea x-show="codeMode" id="quill-edit-raw-textarea" class="w-full bg-slate-900 text-amber-300 font-mono text-xs p-4 rounded-xl border-2 border-black min-h-[240px] outline-none leading-relaxed" placeholder="Kode HTML artikel pembelajaran..." oninput="syncRawHtml('edit', this.value)"></textarea>
                        <p class="text-[11px] text-slate-600 font-bold flex items-center gap-1">
                            <i class="fas fa-info-circle text-sky-600"></i> Format teks HTML (Heading, tabel, list, gambar inline, blok kode) tersimpan sempurna tanpa hilang saat diedit.
                        </p>
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

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
{{-- FULL ARTICLE READER PREVIEW MODAL (FOR GURU) --}}
{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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

{{-- MODAL SAMPAH MODUL / PULIHKAN MODUL --}}
@if(isset($trashedModules) && $trashedModules->isNotEmpty())
<div x-data="{ open: false }" 
     @open-trashed-modules-modal.window="open = true" 
     x-show="open" 
     class="fixed inset-0 overflow-y-auto" 
     style="display: none; z-index: 99999 !important;">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-xs transition-opacity" @click="open = false" style="z-index: 99999 !important;"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4" style="z-index: 100000 !important;">
        <div class="relative bg-white rounded-3xl max-w-2xl w-full border-2 border-black shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="px-6 py-5 bg-rose-600 border-b-2 border-black flex items-center justify-between text-white">
                <div class="flex items-center gap-2.5">
                    <i class="fas fa-trash-restore text-amber-300 text-lg"></i>
                    <div>
                        <h3 class="font-black text-white text-base">Modul Terhapus (Tempat Sampah)</h3>
                        <p class="text-rose-100 text-xs font-bold">Modul di bawah ini dapat dipulihkan kembali ke daftar modul aktif.</p>
                    </div>
                </div>
                <button @click="open = false" class="w-8 h-8 rounded-xl bg-black/20 hover:bg-black/40 text-white flex items-center justify-center transition border border-white/20">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                @foreach($trashedModules as $tMod)
                <div class="bg-slate-50 border-2 border-black rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm hover:border-amber-400 transition-colors">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="bg-amber-300 text-black text-xs font-black px-2 py-0.5 rounded-lg border border-black shadow-2xs">
                                Urutan: {{ $tMod->sequence }}
                            </span>
                            <h4 class="font-black text-black text-sm">{{ $tMod->title }}</h4>
                        </div>
                        <p class="text-xs text-slate-600 font-bold">
                            <i class="fas fa-file-alt mr-1 text-slate-400"></i> {{ $tMod->materials_count ?? $tMod->materials()->count() }} Materi Ajar
                            <span class="mx-1">•</span>
                            <i class="far fa-clock mr-1 text-slate-400"></i> Dihapus: {{ $tMod->deleted_at?->diffForHumans() ?? 'Baru saja' }}
                        </p>
                        @if($tMod->description)
                        <p class="text-[11px] text-slate-500 italic line-clamp-1">{{ $tMod->description }}</p>
                        @endif
                    </div>
                    
                    <form action="{{ route('guru.lms.modules.restore', $tMod->id) }}" method="POST" onsubmit="return confirm('Pulihkan modul ini kembali ke urutan aktif?')">
                        @csrf
                        <input type="hidden" name="sequence" value="{{ $tMod->sequence }}">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs uppercase tracking-wider border-2 border-black shadow-xs flex items-center justify-center gap-2 transition">
                            <i class="fas fa-undo text-amber-300"></i> Pulihkan
                        </button>
                    </form>
                </div>
                @endforeach
            </div>

            <div class="px-6 py-4 bg-slate-100 border-t-2 border-black flex justify-between items-center text-xs font-bold text-slate-600">
                <span>Total: {{ $trashedModules->count() }} modul terhapus</span>
                <button type="button" @click="open = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-black border-2 border-black rounded-xl font-black uppercase text-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@include('components.lms-game-player')

{{-- ================================================================ --}}
{{-- MODAL SHARING COURSE (SOLID UI / NEO-BRUTALISM) --}}
{{-- ================================================================ --}}
<div id="share-course-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" onclick="closeShareCourseModal()"></div>

        <!-- Dialog Card -->
        <div class="relative inline-block w-full max-w-2xl bg-white border-2 border-black rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 z-10">
            <!-- Header -->
            <div class="p-6 border-b-2 border-black text-white flex items-center justify-between" style="background-color: #4f46e5 !important;">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-400 border-2 border-black flex items-center justify-center text-black text-xl shadow-md">
                        <i class="fas fa-share-nodes"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white leading-tight">Sharing Course LMS</h3>
                        <p class="text-xs text-indigo-200 font-bold mt-0.5">Bagikan salinan modul, materi, tugas & kuis ke rekan guru</p>
                    </div>
                </div>
                <button type="button" onclick="closeShareCourseModal()" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/30 text-white flex items-center justify-center transition">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <!-- Body Form -->
            <form id="form-share-course" onsubmit="submitShareCourse(event)" class="p-6 space-y-5 bg-white">
                <!-- Course Source Info Banner -->
                <div class="p-4 bg-indigo-50 border-2 border-black rounded-2xl flex items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 block">Course Sumber:</span>
                        <span class="text-sm font-black text-black">{{ $course->course_name }}</span>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-600 font-bold">
                            <span><i class="fas fa-book text-emerald-600 mr-1"></i>{{ $course->materials_count }} Materi</span>
                            <span>•</span>
                            <span><i class="fas fa-tasks text-sky-600 mr-1"></i>{{ $course->assignments_count }} Tugas</span>
                            <span>•</span>
                            <span><i class="fas fa-question-circle text-purple-600 mr-1"></i>{{ $course->quizzes_count }} Kuis</span>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 bg-amber-300 border-2 border-black rounded-xl text-xs font-black text-black shrink-0">
                        {{ $course->subject->subject_name ?? '-' }}
                    </span>
                </div>

                <!-- Loading State for Candidates -->
                <div id="share-loading" class="py-8 text-center">
                    <i class="fas fa-circle-notch fa-spin text-3xl text-indigo-600 mb-2"></i>
                    <p class="text-xs font-black text-slate-600">Memuat daftar guru rekan & rombel sasaran...</p>
                </div>

                <!-- Step 1: Select Target Teacher -->
                <div id="share-form-content" class="hidden space-y-5">
                    <!-- Unit Sekolah Sasaran (Khusus Akun Multi-Unit / Yayasan) -->
                    <div id="unit-selector-wrapper" class="hidden p-3.5 bg-amber-50 border-2 border-black rounded-2xl shadow-xs">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <label class="block text-xs font-black text-black uppercase tracking-wider">
                                🏫 Unit Sekolah Sasaran:
                            </label>
                            <span id="active-school-badge" class="px-2 py-0.5 bg-amber-300 border border-black rounded-lg text-[10px] font-black text-black"></span>
                        </div>
                        <select id="share-school-selector" onchange="changeShareSchool()" class="w-full border-2 border-black rounded-xl px-3 py-2 text-xs text-black font-black bg-white focus:ring-2 focus:ring-black/20 outline-none">
                        </select>
                        <p class="text-[10px] text-amber-900 font-bold mt-1">
                            ℹ️ Daftar guru & rombel di bawah disesuaikan dengan unit sekolah yang dipilih.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">
                            1. Pilih Guru Penerima <span class="text-rose-600">*</span>
                        </label>
                        <select id="share-target-teacher" required onchange="handleTeacherSelection()" class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-bold focus:ring-4 focus:ring-black/20 outline-none bg-white">
                            <option value="">-- Pilih Guru Penerima --</option>
                        </select>
                        <p class="text-[11px] text-slate-600 font-semibold mt-1">
                            💡 Guru yang memiliki SK mapel yang sama ditandai dengan label khusus.
                        </p>
                    </div>

                    <!-- Step 2: Target Classroom -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-black text-black uppercase tracking-wider">
                                2. Pilih Kelas Sasaran Penerima <span class="text-rose-600">*</span>
                            </label>
                            <span id="class-source-hint" class="text-[11px] font-bold text-indigo-600"></span>
                        </div>
                        <div id="share-classrooms-container" class="border-2 border-black rounded-2xl p-4 max-h-44 overflow-y-auto space-y-2 bg-slate-50">
                            <p class="text-xs text-slate-500 font-bold italic">Silakan pilih guru penerima terlebih dahulu.</p>
                        </div>
                        <p class="text-[11px] text-slate-600 font-semibold mt-1">
                            Siswa di kelas yang dipilih akan otomatis terdaftar (auto-enrolled) ke kursus baru.
                        </p>
                    </div>

                    <!-- Step 3: New Course Name -->
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">
                            3. Nama Course Baru
                        </label>
                        <input type="text" id="share-new-course-name" class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-bold focus:ring-4 focus:ring-black/20 outline-none" placeholder="Nama kursus untuk guru penerima">
                    </div>

                    <!-- Step 4: Content Options -->
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">
                            4. Konten Yang Ingin Disalin
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border-2 border-black bg-white cursor-pointer hover:bg-slate-50 transition">
                                <input type="checkbox" id="share-copy-materials" checked class="w-4 h-4 rounded border-2 border-black text-indigo-600">
                                <span class="text-xs font-black text-black">Materi Ajar ({{ $course->materials_count }})</span>
                            </label>
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border-2 border-black bg-white cursor-pointer hover:bg-slate-50 transition">
                                <input type="checkbox" id="share-copy-assignments" checked class="w-4 h-4 rounded border-2 border-black text-indigo-600">
                                <span class="text-xs font-black text-black">Tugas ({{ $course->assignments_count }})</span>
                            </label>
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border-2 border-black bg-white cursor-pointer hover:bg-slate-50 transition">
                                <input type="checkbox" id="share-copy-quizzes" checked class="w-4 h-4 rounded border-2 border-black text-indigo-600">
                                <span class="text-xs font-black text-black">Kuis ({{ $course->quizzes_count }})</span>
                            </label>
                        </div>
                        <div class="mt-2 p-3 bg-amber-50 border-2 border-black rounded-xl text-[11px] font-bold text-amber-900 flex items-start gap-2">
                            <i class="fas fa-circle-info text-amber-600 text-sm mt-0.5 shrink-0"></i>
                            <span>Tenggat waktu tugas & jadwal kuis akan direset agar guru penerima dapat mengatur jadwal belajarnya sendiri. Jawaban/nilai siswa lama <strong>tidak</strong> disalin.</span>
                        </div>
                    </div>

                    <!-- Step 5: Initial Status -->
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">
                            Status Publikasi Kursus Baru
                        </label>
                        <select id="share-status" class="w-full border-2 border-black rounded-2xl px-4 py-2.5 text-xs text-black font-bold focus:ring-4 focus:ring-black/20 outline-none bg-white">
                            <option value="active">Aktif (Langsung Terbit untuk Siswa Kelas Sasaran)</option>
                            <option value="draft">Draft (Disimpan Dahulu, Belum Terbit untuk Siswa)</option>
                        </select>
                    </div>
                </div>

                <!-- Error Banner -->
                <div id="share-error-banner" class="hidden p-3 bg-rose-100 border-2 border-black rounded-2xl text-xs font-black text-rose-800 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-rose-600 text-sm"></i>
                    <span id="share-error-text"></span>
                </div>

                <!-- Footer Actions -->
                <div class="pt-4 border-t-2 border-black flex items-center justify-end gap-3">
                    <button type="button" onclick="closeShareCourseModal()" class="px-5 py-3 rounded-2xl border-2 border-black bg-slate-200 hover:bg-slate-300 font-black text-xs uppercase tracking-wider text-black transition">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-share" class="px-6 py-3 rounded-2xl border-2 border-black text-white font-black text-xs uppercase tracking-wider shadow-md hover:bg-indigo-700 transition flex items-center gap-2" style="background-color: #4f46e5 !important;">
                        <i class="fas fa-paper-plane text-amber-300"></i>
                        <span>Kirim & Bagikan Kursus</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
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
            ['table'],
            ['code-block'],
            ['link', 'image', 'video'],
            ['clean']
        ];

        // Init Create Editor
        const createEditorEl = document.getElementById('quill-create-editor');
        if (createEditorEl) {
            quillCreate = new Quill('#quill-create-editor', {
                theme: 'snow',
                placeholder: 'Tuliskan petunjuk atau rangkuman materi...',
                modules: {
                    toolbar: toolbarOptions,
                    table: true
                }
            });
            quillCreate.getModule('toolbar').addHandler('image', function() {
                imageHandler(quillCreate);
            });
            quillCreate.on('text-change', function() {
                const input = document.getElementById('quill-create-input');
                const rawEl = document.getElementById('quill-create-raw-textarea');
                const val = quillCreate.root.innerHTML === '<p><br></p>' ? '' : quillCreate.root.innerHTML;
                if (input) input.value = val;
                if (rawEl && document.activeElement !== rawEl) rawEl.value = val;
            });
        }

        // Init Edit Editor
        const editEditorEl = document.getElementById('quill-edit-editor');
        if (editEditorEl) {
            quillEdit = new Quill('#quill-edit-editor', {
                theme: 'snow',
                placeholder: 'Keterangan materi atau instruksi...',
                modules: {
                    toolbar: toolbarOptions,
                    table: true
                }
            });
            quillEdit.getModule('toolbar').addHandler('image', function() {
                imageHandler(quillEdit);
            });
            quillEdit.on('text-change', function() {
                const input = document.getElementById('quill-edit-input');
                const rawEl = document.getElementById('quill-edit-raw-textarea');
                const val = quillEdit.root.innerHTML === '<p><br></p>' ? '' : quillEdit.root.innerHTML;
                if (input) input.value = val;
                if (rawEl && document.activeElement !== rawEl) rawEl.value = val;
            });
        }
    });

    function syncCodeMode(type, isCodeMode) {
        if (type === 'create') {
            const rawEl = document.getElementById('quill-create-raw-textarea');
            const input = document.getElementById('quill-create-input');
            if (isCodeMode) {
                const currentHtml = quillCreate ? (quillCreate.root.innerHTML === '<p><br></p>' ? '' : quillCreate.root.innerHTML) : (input ? input.value : '');
                if (rawEl) rawEl.value = currentHtml;
            } else {
                if (rawEl && quillCreate) {
                    quillCreate.root.innerHTML = rawEl.value;
                    if (input) input.value = rawEl.value;
                }
            }
        } else if (type === 'edit') {
            const rawEl = document.getElementById('quill-edit-raw-textarea');
            const input = document.getElementById('quill-edit-input');
            if (isCodeMode) {
                const currentHtml = quillEdit ? (quillEdit.root.innerHTML === '<p><br></p>' ? '' : quillEdit.root.innerHTML) : (input ? input.value : '');
                if (rawEl) rawEl.value = currentHtml;
            } else {
                if (rawEl && quillEdit) {
                    quillEdit.root.innerHTML = rawEl.value;
                    if (input) input.value = rawEl.value;
                }
            }
        }
    }

    function syncRawHtml(type, value) {
        if (type === 'create') {
            const input = document.getElementById('quill-create-input');
            if (input) input.value = value;
            if (quillCreate) quillCreate.root.innerHTML = value;
        } else if (type === 'edit') {
            const input = document.getElementById('quill-edit-input');
            if (input) input.value = value;
            if (quillEdit) quillEdit.root.innerHTML = value;
        }
    }

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
                    const rawEl = document.getElementById('quill-create-raw-textarea');
                    if (rawEl) rawEl.value = data.content;
                } else if (editorType === 'edit' && quillEdit) {
                    quillEdit.root.innerHTML = data.content;
                    const input = document.getElementById('quill-edit-input');
                    if (input) input.value = data.content;
                    const rawEl = document.getElementById('quill-edit-raw-textarea');
                    if (rawEl) rawEl.value = data.content;
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
            html = `<h2>🧪 Panduan Praktikum &amp; Laboratorium</h2><p><strong>Tujuan Praktikum:</strong> Siswa mampu memahami dan mempraktikkan...</p><h3>🛠️ Alat &amp; Bahan</h3><ul><li>Alat/Bahan 1...</li><li>Alat/Bahan 2...</li></ul><h3>📋 Langkah Kerja</h3><ol><li>Langkah 1: Siapkan peralatan...</li><li>Langkah 2: Operasikan...</li></ol>`;
        } else if (templateType === 'case') {
            html = `<h2>💡 Studi Kasus &amp; Diskusi Terbuka</h2><blockquote style="border-left: 4px solid #3b82f6; padding-left: 12px; background-color: #eff6ff; padding: 10px; border-radius: 8px;"><strong>Deskripsi Kasus:</strong> Jelaskan studi kasus nyata di dunia kerja/lapangan...</blockquote><h3>🎯 Tugas Refleksi Siswa:</h3><ol><li>Identifikasi penyebab utama masalah di atas.</li><li>Tuliskan 2 usulan solusi terbaik.</li></ol>`;
        }

        if (editorType === 'create' && quillCreate) {
            quillCreate.root.innerHTML = html;
            const input = document.getElementById('quill-create-input');
            if (input) input.value = html;
            const rawEl = document.getElementById('quill-create-raw-textarea');
            if (rawEl) rawEl.value = html;
        } else if (editorType === 'edit' && quillEdit) {
            quillEdit.root.innerHTML = html;
            const input = document.getElementById('quill-edit-input');
            if (input) input.value = html;
            const rawEl = document.getElementById('quill-edit-raw-textarea');
            if (rawEl) rawEl.value = html;
        }
    }

    function setQuillEditContent(content) {
        let html = content || '';
        if (html && !/<(p|div|br|h[1-6]|ul|ol|li|table|blockquote)\b/i.test(html)) {
            html = html.split('\n').map(line => line.trim() ? `<p>${line}</p>` : '<p><br></p>').join('');
        }
        if (quillEdit) {
            quillEdit.root.innerHTML = html;
        }
        const input = document.getElementById('quill-edit-input');
        if (input) input.value = html;
        const rawEl = document.getElementById('quill-edit-raw-textarea');
        if (rawEl) rawEl.value = html;
    }

    function gameBuilder() {
        return {
            open: false,
            isEdit: false,
            gameId: null,
            moduleId: '',
            title: '',
            rewardPoints: 50,
            timeLimit: '',
            livesCount: '',
            gameType: 'quiz',
            pairs: [
                { term: 'Fotosintesis', definition: 'Proses pembuatan makanan pada tumbuhan hijau dengan bantuan energi sinar matahari.' },
                { term: 'Mitokondria', definition: 'Organel sel yang berfungsi sebagai pusat pembangkit energi (ATP).' },
                { term: 'Demokrasi', definition: 'Sistem pemerintahan di mana kekuasaan tertinggi berada di tangan rakyat.' },
                { term: 'Gravitasi', definition: 'Gaya tarik alami bumi yang menarik benda-benda bermassa ke pusat bumi.' },
                { term: 'Ekosistem', definition: 'Hubungan timbal balik yang saling mempengaruhi antara makhluk hidup dan lingkungannya.' }
            ],
            wheelItems: ['+50 EXP Bonus', 'Tunjuk 1 Teman Menjawab', 'Bebas Tugas 1 Soal', '+100 EXP Jackpot', 'Putar Sekali Lagi', 'Zonkk! Coba Lagi'],
            quizQuestions: [
                { question: 'Planet manakah yang sering dijuluki sebagai "Planet Merah" dalam tata surya kita?', options: ['Mars', 'Venus', 'Jupiter', 'Saturnus'], answer: 0 },
                { question: 'Zat hijau pada daun yang berperan penting dalam proses fotosintesis adalah...', options: ['Klorofil', 'Kromoplas', 'Stomata', 'Sitoplasma'], answer: 0 },
                { question: 'Rumus kimia air murni yang biasa kita minum setiap hari adalah...', options: ['CO2', 'H2O', 'NaCl', 'O2'], answer: 1 },
                { question: 'Ibukota negara Indonesia yang berada di Pulau Jawa adalah...', options: ['Surabaya', 'Bandung', 'Jakarta', 'Semarang'], answer: 2 },
                { question: 'Berapakah hasil dari operasi perhitungan matematika sederhana 15 x 4 + 10?', options: ['60', '70', '80', '50'], answer: 1 }
            ],
            tfStatements: [
                { statement: 'Matahari mengelilingi bumi sebagai pusat tata surya.', is_true: false },
                { statement: 'Oksigen dihirup oleh manusia saat bernapas untuk metabolisme tubuh.', is_true: true },
                { statement: 'Sudut siku-siku memiliki besar sudut tepat 90 derajat.', is_true: true },
                { statement: 'Air membeku menjadi es padat pada suhu 100 derajat Celsius.', is_true: false },
                { statement: 'Indonesia memproklamasikan kemerdekaannya pada tanggal 17 Agustus 1945.', is_true: true }
            ],
            guessWords: [
                { word: 'MERDEKA', hint: 'Bebas dari segala bentuk penjajahan atau kekuasaan asing' },
                { word: 'GRAVITASI', hint: 'Gaya tarik bumi yang membuat setiap benda jatuh ke bawah' },
                { word: 'KOMPUTER', hint: 'Perangkat elektronik untuk mengolah data, komputasi, dan informasi' },
                { word: 'ATMOSFER', hint: 'Lapisan gas pelindung yang menyelimuti planet bumi' },
                { word: 'PANCASILA', hint: 'Dasar negara dan falsafah hidup bangsa Indonesia' }
            ],
            scrambleWords: [
                { word: 'BIOLOGI', hint: 'Ilmu yang mempelajari tentang seluk-beluk makhluk hidup' },
                { word: 'SEJARAH', hint: 'Kejadian atau peristiwa nyata yang terjadi di masa lampau' },
                { word: 'GEOGRAFI', hint: 'Ilmu tentang fenomena permukaan bumi, iklim, dan bentang alam' },
                { word: 'EKONOMI', hint: 'Ilmu tentang produksi, distribusi, dan konsumsi barang dan jasa' },
                { word: 'ALGORITMA', hint: 'Urutan langkah-langkah logis dan sistematis dalam pemecahan masalah' }
            ],
            sequenceGroups: [
                {
                    title: 'Tahapan Metamorfosis Sempurna Kupu-Kupu',
                    items: [
                        { item: '1. Telur diletakkan oleh induk pada permukaan daun' },
                        { item: '2. Telur menetas menjadi Ulat (Larva) yang aktif makan daun' },
                        { item: '3. Ulat membungkus dirinya menjadi Kepompong (Pupa)' },
                        { item: '4. Mengalami pembentukan organ dalam fase kepompong' },
                        { item: '5. Keluar menjadi Kupu-Kupu dewasa (Imago) yang indah' }
                    ]
                }
            ],
            hotspots: [
                { x: 50, y: 50, label: 'Inti Sel (Nukleus)' },
                { x: 30, y: 40, label: 'Mitokondria' },
                { x: 80, y: 50, label: 'Membran Sel' },
                { x: 45, y: 70, label: 'Ribosom' },
                { x: 65, y: 35, label: 'Badan Golgi' }
            ],
            chemEquations: [
                { equation: '_ H2 + _ O2 -> _ H2O', answers: '2, 1, 2' },
                { equation: '_ N2 + _ H2 -> _ NH3', answers: '1, 3, 2' },
                { equation: '_ CH4 + _ O2 -> _ CO2 + _ H2O', answers: '1, 2, 1, 2' },
                { equation: '_ Na + _ Cl2 -> _ NaCl', answers: '2, 1, 2' },
                { equation: '_ Fe + _ O2 -> _ Fe2O3', answers: '4, 3, 2' }
            ],
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
                this.pairs = [
                    { term: 'Fotosintesis', definition: 'Proses pembuatan makanan pada tumbuhan hijau dengan bantuan energi sinar matahari.' },
                    { term: 'Mitokondria', definition: 'Organel sel yang berfungsi sebagai pusat pembangkit energi (ATP).' },
                    { term: 'Demokrasi', definition: 'Sistem pemerintahan di mana kekuasaan tertinggi berada di tangan rakyat.' },
                    { term: 'Gravitasi', definition: 'Gaya tarik alami bumi yang menarik benda-benda bermassa ke pusat bumi.' },
                    { term: 'Ekosistem', definition: 'Hubungan timbal balik yang saling mempengaruhi antara makhluk hidup dan lingkungannya.' }
                ];
                this.wheelItems = ['+50 EXP Bonus', 'Tunjuk 1 Teman Menjawab', 'Bebas Tugas 1 Soal', '+100 EXP Jackpot', 'Putar Sekali Lagi', 'Zonkk! Coba Lagi'];
                this.quizQuestions = [
                    { question: 'Planet manakah yang sering dijuluki sebagai "Planet Merah" dalam tata surya kita?', options: ['Mars', 'Venus', 'Jupiter', 'Saturnus'], answer: 0 },
                    { question: 'Zat hijau pada daun yang berperan penting dalam proses fotosintesis adalah...', options: ['Klorofil', 'Kromoplas', 'Stomata', 'Sitoplasma'], answer: 0 },
                    { question: 'Rumus kimia air murni yang biasa kita minum setiap hari adalah...', options: ['CO2', 'H2O', 'NaCl', 'O2'], answer: 1 },
                    { question: 'Ibukota negara Indonesia yang berada di Pulau Jawa adalah...', options: ['Surabaya', 'Bandung', 'Jakarta', 'Semarang'], answer: 2 },
                    { question: 'Berapakah hasil dari operasi perhitungan matematika sederhana 15 x 4 + 10?', options: ['60', '70', '80', '50'], answer: 1 }
                ];
                this.tfStatements = [
                    { statement: 'Matahari mengelilingi bumi sebagai pusat tata surya.', is_true: false },
                    { statement: 'Oksigen dihirup oleh manusia saat bernapas untuk metabolisme tubuh.', is_true: true },
                    { statement: 'Sudut siku-siku memiliki besar sudut tepat 90 derajat.', is_true: true },
                    { statement: 'Air membeku menjadi es padat pada suhu 100 derajat Celsius.', is_true: false },
                    { statement: 'Indonesia memproklamasikan kemerdekaannya pada tanggal 17 Agustus 1945.', is_true: true }
                ];
                this.guessWords = [
                    { word: 'MERDEKA', hint: 'Bebas dari segala bentuk penjajahan atau kekuasaan asing' },
                    { word: 'GRAVITASI', hint: 'Gaya tarik bumi yang membuat setiap benda jatuh ke bawah' },
                    { word: 'KOMPUTER', hint: 'Perangkat elektronik untuk mengolah data, komputasi, dan informasi' },
                    { word: 'ATMOSFER', hint: 'Lapisan gas pelindung yang menyelimuti planet bumi' },
                    { word: 'PANCASILA', hint: 'Dasar negara dan falsafah hidup bangsa Indonesia' }
                ];
                this.scrambleWords = [
                    { word: 'BIOLOGI', hint: 'Ilmu yang mempelajari tentang seluk-beluk makhluk hidup' },
                    { word: 'SEJARAH', hint: 'Kejadian atau peristiwa nyata yang terjadi di masa lampau' },
                    { word: 'GEOGRAFI', hint: 'Ilmu tentang fenomena permukaan bumi, iklim, dan bentang alam' },
                    { word: 'EKONOMI', hint: 'Ilmu tentang produksi, distribusi, dan konsumsi barang dan jasa' },
                    { word: 'ALGORITMA', hint: 'Urutan langkah-langkah logis dan sistematis dalam pemecahan masalah' }
                ];
                this.sequenceGroups = [
                    {
                        title: 'Tahapan Metamorfosis Sempurna Kupu-Kupu',
                        items: [
                            { item: '1. Telur diletakkan oleh induk pada permukaan daun' },
                            { item: '2. Telur menetas menjadi Ulat (Larva) yang aktif makan daun' },
                            { item: '3. Ulat membungkus dirinya menjadi Kepompong (Pupa)' },
                            { item: '4. Mengalami pembentukan organ dalam fase kepompong' },
                            { item: '5. Keluar menjadi Kupu-Kupu dewasa (Imago) yang indah' }
                        ]
                    }
                ];
                this.hotspots = [
                    { x: 50, y: 50, label: 'Inti Sel (Nukleus)' },
                    { x: 30, y: 40, label: 'Mitokondria' },
                    { x: 80, y: 50, label: 'Membran Sel' },
                    { x: 45, y: 70, label: 'Ribosom' },
                    { x: 65, y: 35, label: 'Badan Golgi' }
                ];
                this.chemEquations = [
                    { equation: '_ H2 + _ O2 -> _ H2O', answers: '2, 1, 2' },
                    { equation: '_ N2 + _ H2 -> _ NH3', answers: '1, 3, 2' },
                    { equation: '_ CH4 + _ O2 -> _ CO2 + _ H2O', answers: '1, 2, 1, 2' },
                    { equation: '_ Na + _ Cl2 -> _ NaCl', answers: '2, 1, 2' },
                    { equation: '_ Fe + _ O2 -> _ Fe2O3', answers: '4, 3, 2' }
                ];
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
        };
    }

    // ================================================================
    // SHARING COURSE LMS JAVASCRIPT
    // ================================================================
    let shareCandidatesData = null;

    async function openShareCourseModal() {
        const modal = document.getElementById('share-course-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        document.getElementById('share-loading').classList.remove('hidden');
        document.getElementById('share-form-content').classList.add('hidden');
        document.getElementById('share-error-banner').classList.add('hidden');

        try {
            const res = await fetch('{{ route("guru.lms.share-candidates", $course->id) }}', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (jsonErr) {
                throw new Error('Gagal menghubungi server (Status: ' + res.status + '). Pastikan cache telah dibersihkan di server.');
            }

            if (!res.ok) throw new Error(data.error || 'Gagal memuat data guru.');

            shareCandidatesData = data;
            renderSchoolSelector(data.available_schools, data.current_school_id, data.current_school_name);
            populateTeacherDropdown(data.teachers);
            document.getElementById('share-new-course-name').value = data.course.name;

            document.getElementById('share-loading').classList.add('hidden');
            document.getElementById('share-form-content').classList.remove('hidden');
        } catch (err) {
            document.getElementById('share-loading').classList.add('hidden');
            document.getElementById('share-error-banner').classList.remove('hidden');
            document.getElementById('share-error-text').textContent = err.message;
        }
    }

    function renderSchoolSelector(availableSchools, currentSchoolId, currentSchoolName) {
        const wrapper = document.getElementById('unit-selector-wrapper');
        const select = document.getElementById('share-school-selector');
        const badge = document.getElementById('active-school-badge');

        if (!availableSchools || availableSchools.length <= 1) {
            if (wrapper) wrapper.classList.add('hidden');
            return;
        }

        wrapper.classList.remove('hidden');
        badge.textContent = currentSchoolName || 'Unit Terpilih';

        select.innerHTML = '';
        availableSchools.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name;
            if (s.id == currentSchoolId) opt.selected = true;
            select.appendChild(opt);
        });
    }

    async function changeShareSchool() {
        const schoolId = document.getElementById('share-school-selector').value;
        if (!schoolId) return;

        document.getElementById('share-loading').classList.remove('hidden');
        document.getElementById('share-form-content').classList.add('hidden');
        document.getElementById('share-error-banner').classList.add('hidden');

        try {
            const res = await fetch(`{{ route("guru.lms.share-candidates", $course->id) }}?school_id=${schoolId}`, {
                headers: { 'Accept': 'application/json' }
            });
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Gagal menghubungi server.');
            }

            if (!res.ok) throw new Error(data.error || 'Gagal memuat data guru.');

            shareCandidatesData = data;
            renderSchoolSelector(data.available_schools, data.current_school_id, data.current_school_name);
            populateTeacherDropdown(data.teachers);
            document.getElementById('share-classrooms-container').innerHTML = '<p class="text-xs text-slate-500 font-bold italic">Silakan pilih guru penerima terlebih dahulu.</p>';
            document.getElementById('class-source-hint').textContent = '';

            document.getElementById('share-loading').classList.add('hidden');
            document.getElementById('share-form-content').classList.remove('hidden');
        } catch (err) {
            document.getElementById('share-loading').classList.add('hidden');
            document.getElementById('share-error-banner').classList.remove('hidden');
            document.getElementById('share-error-text').textContent = err.message;
        }
    }

    function closeShareCourseModal() {
        const modal = document.getElementById('share-course-modal');
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function populateTeacherDropdown(teachers) {
        const select = document.getElementById('share-target-teacher');
        select.innerHTML = '<option value="">-- Pilih Guru Penerima --</option>';

        const sameSubject = teachers.filter(t => t.is_same_subject && !t.is_self);
        const selfTeacher = teachers.find(t => t.is_self);
        const otherTeachers = teachers.filter(t => !t.is_same_subject && !t.is_self);

        if (sameSubject.length > 0) {
            const optgroup = document.createElement('optgroup');
            optgroup.label = '⭐ Rekan Pengampu Mapel yang Sama';
            sameSubject.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = `${t.name} (NIP: ${t.nip})`;
                optgroup.appendChild(opt);
            });
            select.appendChild(optgroup);
        }

        if (otherTeachers.length > 0) {
            const optgroup = document.createElement('optgroup');
            optgroup.label = 'Guru Lain di Sekolah';
            otherTeachers.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = `${t.name} (NIP: ${t.nip})`;
                optgroup.appendChild(opt);
            });
            select.appendChild(optgroup);
        }

        if (selfTeacher) {
            const optgroup = document.createElement('optgroup');
            optgroup.label = 'Salin ke Diri Sendiri';
            const opt = document.createElement('option');
            opt.value = selfTeacher.id;
            opt.textContent = selfTeacher.name;
            optgroup.appendChild(opt);
            select.appendChild(optgroup);
        }
    }

    function handleTeacherSelection() {
        const teacherId = parseInt(document.getElementById('share-target-teacher').value);
        const container = document.getElementById('share-classrooms-container');
        const hint = document.getElementById('class-source-hint');

        if (!teacherId || !shareCandidatesData) {
            container.innerHTML = '<p class="text-xs text-slate-500 font-bold italic">Silakan pilih guru penerima terlebih dahulu.</p>';
            hint.textContent = '';
            return;
        }

        const teacher = shareCandidatesData.teachers.find(t => t.id === teacherId);
        let classesToDisplay = [];

        if (teacher && teacher.assigned_classes && teacher.assigned_classes.length > 0) {
            classesToDisplay = teacher.assigned_classes;
            hint.textContent = 'Menampilkan kelas yang diampu guru terpilih';
        } else {
            classesToDisplay = shareCandidatesData.all_classrooms || [];
            hint.textContent = 'Menampilkan seluruh rombel di sekolah';
        }

        if (classesToDisplay.length === 0) {
            container.innerHTML = '<p class="text-xs text-rose-600 font-bold">Tidak ada rombel aktif ditemukan.</p>';
            return;
        }

        let html = '';
        classesToDisplay.forEach((cls, idx) => {
            const checkedAttr = (idx === 0) ? 'checked' : '';
            const badge = cls.is_subject_class ? '<span class="ml-auto text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-400 px-2 py-0.5 rounded-lg">Kelas Mapel</span>' : '';
            html += `
                <label class="flex items-center gap-3 p-2.5 rounded-xl border-2 border-black bg-white cursor-pointer hover:bg-slate-50 transition">
                    <input type="checkbox" name="classroom_ids[]" value="${cls.id}" ${checkedAttr} class="w-4 h-4 rounded border-2 border-black text-indigo-600 share-class-checkbox">
                    <span class="text-xs font-black text-black">${cls.class_name}</span>
                    ${badge}
                </label>
            `;
        });

        container.innerHTML = html;
    }

    async function submitShareCourse(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-share');
        const teacherSelect = document.getElementById('share-target-teacher');
        const teacherId = teacherSelect.value;
        const teacherName = teacherSelect.options[teacherSelect.selectedIndex]?.text || 'Guru';
        const courseName = document.getElementById('share-new-course-name').value;
        const copyMaterials = document.getElementById('share-copy-materials').checked;
        const copyAssignments = document.getElementById('share-copy-assignments').checked;
        const copyQuizzes = document.getElementById('share-copy-quizzes').checked;
        const status = document.getElementById('share-status').value;

        const checkedClasses = Array.from(document.querySelectorAll('.share-class-checkbox:checked')).map(cb => cb.value);

        if (!teacherId) {
            alert('Silakan pilih guru penerima terlebih dahulu.');
            return;
        }

        if (checkedClasses.length === 0) {
            alert('Silakan centang minimal satu kelas sasaran penerima.');
            return;
        }

        if (!confirm(`Apakah Anda yakin ingin membagikan salinan kursus ini ke:\n\n${teacherName}?\n\nKonten modul, materi, dan tugas akan disalin ke akun guru penerima.`)) {
            return;
        }

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Sedang Membagikan...';
        document.getElementById('share-error-banner').classList.add('hidden');

        try {
            const payload = {
                target_teacher_id: teacherId,
                classroom_ids: checkedClasses,
                course_name: courseName,
                copy_materials: copyMaterials,
                copy_assignments: copyAssignments,
                copy_quizzes: copyQuizzes,
                status: status
            };

            const res = await fetch('{{ route("guru.lms.share", $course->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.error || data.message || 'Gagal membagikan kursus.');
            }

            alert('✅ Berhasil!\n\n' + data.message);
            closeShareCourseModal();
            window.location.reload();
        } catch (err) {
            document.getElementById('share-error-banner').classList.remove('hidden');
            document.getElementById('share-error-text').textContent = err.message;
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        }
    }
</script>
@endpush
