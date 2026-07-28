@extends('layouts.guru')

@section('title', 'Course Management - ' . $course->name)

@push('styles')
<style>
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
            <div class="flex items-start gap-4 mb-6">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #1e3a8a !important; color: #ffffff !important;">
                    @if($scientist)
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                    @else
                    <i class="fas fa-chalkboard-teacher text-white text-2xl"></i>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-black tracking-tight leading-tight">{{ $course->course_name ?? $course->name }}</h1>
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
                <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-layer-group text-emerald-600 text-xs"></i></span>
                    STRUKTUR KURIKULUM
                </h3>
                <div class="flex gap-2">
                    <a href="{{ route('guru.lms.modules.create', $course->id) }}" class="bg-white border border-gray-200 text-gray-600 px-3 py-2 rounded-xl text-[10px] font-bold uppercase transition hover:bg-gray-50 hover:border-gray-300 shadow-sm">
                        <i class="fas fa-plus mr-1 text-emerald-500"></i> Tambah Modul
                    </a>
                    <button @click="$dispatch('open-game-modal')" class="bg-indigo-600 text-white px-3 py-2 rounded-xl text-[10px] font-bold uppercase transition hover:bg-indigo-700 shadow-md hover:shadow-lg">
                        <i class="fas fa-gamepad mr-1"></i> Buat Game
                    </button>
                    <button @click="$dispatch('open-material-modal')" class="bg-emerald-600 text-white px-3 py-2 rounded-xl text-[10px] font-bold uppercase transition hover:bg-emerald-700 shadow-md hover:shadow-lg">
                        <i class="fas fa-upload mr-1"></i> Upload Materi
                    </button>
                </div>
            </div>

            @forelse($course->modules as $module)
            @php 
                $moduleColor = $module->color ?? 'blue';
                $mColor = \App\Models\LmsCourse::getColorClasses($moduleColor);
            @endphp
            <div class="module-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                {{-- Module Header --}}
                <div class="bg-gradient-to-r {{ $mColor['gradient'] ?? $mColor['bg'] }} px-5 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white font-bold text-sm border border-gray-100 shadow-sm">
                            {{ $module->sequence }}
                        </span>
                        <div>
                            <h3 class="font-bold text-white text-sm tracking-wide">{{ $module->title }}</h3>
                            <p class="text-white/90 text-[10px] font-bold uppercase tracking-widest">{{ $module->materials->count() }} MATERI</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('guru.lms.modules.edit', $module->id) }}" class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-all border border-gray-100">
                            <i class="fas fa-edit text-[10px]"></i>
                        </a>
                        <form action="{{ route('guru.lms.modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Hapus modul dan seluruh materinya?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center text-white/40 hover:text-rose-200 hover:bg-rose-500/20 transition-all border border-gray-100">
                                <i class="fas fa-trash text-[10px]"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                {{-- Materials List --}}
                <div class="p-4 space-y-2">
                    @forelse($module->materials as $material)
                    <div x-data="{ expanded: false }" class="bg-gray-50/50 rounded-xl border border-gray-100 hover:border-{{ $moduleColor }}-200 hover:bg-white transition-all overflow-hidden group/mat">
                        <div class="flex items-center justify-between p-3.5 cursor-pointer" @click="expanded = !expanded">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-sm flex-shrink-0 {{ $material->material_type === 'pdf' ? 'bg-rose-500' : ($material->material_type === 'video' ? 'bg-blue-500' : ($material->material_type === 'image' ? 'bg-emerald-500' : ($material->material_type === 'link' ? 'bg-purple-500' : ($material->material_type === 'interactive' ? 'bg-indigo-600' : ($material->material_type === 'document' ? 'bg-orange-500' : 'bg-gray-500'))))) }}">
                                    <i class="fas {{ $material->material_type === 'pdf' ? 'fa-file-pdf' : ($material->material_type === 'video' ? 'fa-video' : ($material->material_type === 'image' ? 'fa-image' : ($material->material_type === 'link' ? 'fa-link' : ($material->material_type === 'interactive' ? 'fa-gamepad' : ($material->material_type === 'document' ? 'fa-file-alt' : 'fa-file'))))) }}"></i>
                                </span>
                                <div>
                                    <p class="font-bold text-gray-700 text-sm">
                                        <span class="text-{{ $moduleColor }}-500 opacity-60 font-bold mr-1 text-xs">{{ $module->getCode() }}-{{ $loop->iteration }}</span>
                                        {{ preg_replace('/^\d+\.\d+\s*/', '', $material->title) }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $material->getContentTypeLabel() }}{{ $material->file_size ? ' · ' . number_format($material->file_size / 1024, 0) . ' KB' : '' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-chevron-down text-gray-300 text-xs transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
                                <button @click.stop="$dispatch('open-edit-material-modal', {{ json_encode([
                                    'id' => $material->id,
                                    'module_id' => $material->module_id,
                                    'title' => preg_replace('/^\d+\.\d+\s*/', '', $material->title),
                                    'material_type' => $material->material_type,
                                    'content' => $material->content,
                                    'file_url' => $material->file_url,
                                    'update_url' => route('guru.lms.materials.update', $material->id)
                                ]) }})" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition-colors border border-gray-100 opacity-0 group-hover/mat:opacity-100" title="Edit Materi"><i class="fas fa-edit text-xs"></i></button>
                                @if($material->file_path)
                                <a href="{{ route('guru.lms.materials.download', $material->id) }}" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white text-gray-400 hover:bg-blue-50 hover:text-blue-500 transition-colors border border-gray-100 opacity-0 group-hover/mat:opacity-100" onclick="event.stopPropagation()"><i class="fas fa-download text-xs"></i></a>
                                @endif
                                @if($material->file_url)
                                <a href="{{ $material->file_url }}" target="_blank" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white text-gray-400 hover:bg-blue-50 hover:text-blue-500 transition-colors border border-gray-100 opacity-0 group-hover/mat:opacity-100" onclick="event.stopPropagation()"><i class="fas fa-external-link-alt text-xs"></i></a>
                                @endif
                                <form action="{{ route('guru.lms.materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Hapus materi?')" class="inline" onclick="event.stopPropagation()">
                                    @csrf @method('DELETE')
                                    <button class="w-8 h-8 rounded-lg flex items-center justify-center bg-white text-gray-400 hover:bg-rose-50 hover:text-rose-500 transition-colors border border-gray-100 opacity-0 group-hover/mat:opacity-100"><i class="fas fa-trash text-xs"></i></button>
                                </form>
                            </div>
                        </div>
                        <div x-show="expanded" x-transition x-cloak class="px-5 pb-4 border-t border-gray-100 bg-gray-50/30">
                            {{-- Media Players --}}
                            <div class="mt-4 mb-3">
                                @if($material->material_type === 'video')
                                    @if($material->isYouTubeVideo())
                                        <div class="w-full rounded-xl overflow-hidden shadow-lg border border-gray-200 bg-black mb-4" style="height: 560px; width: 100%;">
                                            <iframe class="w-full h-full" src="{{ $material->getVideoEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                        </div>
                                    @else
                                        <div class="w-full rounded-xl overflow-hidden shadow-lg border border-gray-200 bg-black mb-4" style="height: 560px; width: 100%;">
                                            <video class="w-full h-full object-contain" controls preload="metadata">
                                                <source src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" type="video/mp4">
                                                Browser Anda tidak mendukung tag video.
                                            </video>
                                        </div>
                                        <div class="mt-3 flex gap-2">
                                            <a href="{{ $material->file_path ? route('guru.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-download"></i> Unduh Video
                                            </a>
                                        </div>
                                    @endif
                                @elseif($material->material_type === 'image')
                                    <div class="w-full rounded-xl overflow-hidden shadow-md border border-gray-100 bg-gray-900 flex justify-center">
                                        <img src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" class="max-h-[400px] object-contain w-auto h-auto" alt="{{ $material->title }}">
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ $material->file_path ? route('guru.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                            <i class="fas fa-download"></i> Unduh Gambar
                                        </a>
                                    </div>
                                @elseif($material->material_type === 'pdf' || ($material->material_type === 'document' && str_ends_with(strtolower($material->file_name ?? $material->file_path ?? ''), '.pdf')) || str_contains(strtolower($material->title ?? ''), '[pdf]'))
                                    <div class="w-full rounded-xl overflow-hidden shadow-md border border-gray-200 bg-white mb-4" style="height: 600px;">
                                        <iframe src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" class="w-full h-full" frameborder="0"></iframe>
                                    </div>

                                    <div class="p-4 rounded-xl border border-red-100 bg-red-50/30 flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-xl bg-red-500 text-white flex items-center justify-center shadow-md">
                                                <i class="fas fa-file-pdf text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800 text-sm">Dokumen PDF Terlampir</p>
                                                <p class="text-[10px] text-gray-400 font-medium">Ukuran: {{ $material->file_size ? number_format($material->file_size / (1024 * 1024), 2) . ' MB' : 'Tidak diketahui' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '#') }}" target="_blank" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/10 font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-external-link-alt"></i> Buka di Tab Baru
                                            </a>
                                            <a href="{{ $material->file_path ? route('guru.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-download"></i> Unduh PDF
                                            </a>
                                        </div>
                                    </div>
                                @elseif($material->material_type === 'link')
                                    <div class="p-4 rounded-xl border border-purple-100 bg-purple-50/30 flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-xl bg-purple-500 text-white flex items-center justify-center shadow-md">
                                                <i class="fas fa-link text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800 text-sm">Tautan Luar / Link Eksternal</p>
                                                <p class="text-[10px] text-gray-400 font-medium truncate max-w-xs sm:max-w-md">{{ $material->file_url }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ $material->file_url }}" target="_blank" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-external-link-alt"></i> Kunjungi Tautan
                                            </a>
                                        </div>
                                    </div>
                                @elseif($material->material_type === 'interactive')
                                    <div class="w-full rounded-xl overflow-hidden shadow-md border border-gray-200 bg-gray-50 mb-4" style="height: 600px;">
                                        <iframe :src="expanded ? '{{ $material->file_url }}' : ''" class="w-full h-full" frameborder="0" allowfullscreen="allowfullscreen" allow="geolocation *; microphone *; camera *; midi *; encrypted-media *; autoplay *"></iframe>
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <a href="{{ $material->file_url }}" target="_blank" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                            <i class="fas fa-external-link-alt"></i> Buka Game di Tab Baru
                                        </a>
                                    </div>
                                @elseif($material->file_path || $material->material_type === 'document')
                                    <div class="p-4 rounded-xl border border-blue-100 bg-blue-50/30 flex items-center justify-between gap-4 mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-md">
                                                <i class="fas fa-file-alt text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800 text-sm">Dokumen Terlampir: {{ $material->file_name ?: ($material->title ?: 'File Materi') }}</p>
                                                <p class="text-[10px] text-gray-400 font-medium">Tipe: {{ strtoupper(pathinfo($material->file_name ?? $material->file_path ?? 'DOC', PATHINFO_EXTENSION)) }}{{ $material->file_size ? ' · ' . number_format($material->file_size / 1024, 0) . ' KB' : '' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($material->file_path)
                                            <a href="{{ route('guru.lms.materials.view', $material->id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/10 font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-external-link-alt"></i> Buka / Preview
                                            </a>
                                            <a href="{{ route('guru.lms.materials.download', $material->id) }}" download class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                                <i class="fas fa-download"></i> Unduh File
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Text Content --}}
                            @if($material->content)
                            <div class="prose prose-sm max-w-none text-gray-600 mt-3">{!! strip_tags($material->content) !== $material->content ? $material->content : nl2br(e($material->content)) !!}</div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-6 bg-gray-50/50 rounded-xl border-2 border-dashed border-gray-100 text-center">
                        <p class="text-xs text-gray-400 font-medium italic">Belum ada materi di modul ini</p>
                    </div>
                    @endforelse
                </div>

                {{-- Games List --}}
                @if($module->games->count() > 0)
                <div class="px-4 pb-4 space-y-2">
                    <h4 class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-2 px-1 flex items-center gap-1.5"><i class="fas fa-gamepad"></i> Mini Games ({{ $module->games->count() }})</h4>
                    @foreach($module->games as $game)
                    <div class="bg-indigo-50/50 rounded-xl border border-indigo-100 p-3.5 flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-sm">
                                <i class="fas fa-gamepad"></i>
                            </span>
                            <div>
                                <p class="font-bold text-indigo-900 text-sm flex items-center gap-2">
                                    {{ $game->title }}
                                    <span class="bg-indigo-100 text-indigo-700 text-[9px] font-bold px-1.5 py-0.5 rounded-md uppercase">{{ str_replace('_', ' ', $game->game_type) }}</span>
                                </p>
                                <p class="text-[10px] text-indigo-400 font-bold uppercase mt-0.5"><i class="fas fa-star text-yellow-400"></i> REWARD: {{ $game->reward_points }} EXP</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(in_array($game->game_type, ['quiz', 'true_false']))
                            <form action="{{ route('guru.lms.games.live.create', $game->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="w-auto px-3 h-8 rounded-lg flex items-center justify-center bg-cyan-500 text-white hover:bg-cyan-600 transition-colors font-bold text-xs shadow-sm gap-2" title="Jalankan Mode Multiplayer Live (seperti Kahoot)">
                                    <i class="fas fa-satellite-dish"></i> Host Live
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('guru.lms.games.destroy', $game->id) }}" method="POST" onsubmit="return confirm('Hapus game ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="w-8 h-8 rounded-lg flex items-center justify-center bg-white text-gray-400 hover:bg-rose-50 hover:text-rose-500 transition-colors border border-indigo-100 shadow-sm"><i class="fas fa-trash text-xs"></i></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-layer-group text-3xl text-emerald-300"></i></div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">Belum Ada Modul</h3>
                <p class="text-gray-400 text-sm mb-6">Buat modul pertama untuk menata materi pelajaran Anda.</p>
                <a href="{{ route('guru.lms.modules.create', $course->id) }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-6 py-3 rounded-xl hover:bg-emerald-700 transition shadow-md font-bold text-sm">
                    <i class="fas fa-plus"></i> Buat Modul Baru
                </a>
            </div>
            @endforelse
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: ASSIGNMENTS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'assignments'" class="mt-6 space-y-4 tab-content">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-tasks text-blue-600 text-xs"></i></span>
                    PENUGASAN SISWA
                </h3>
                <a href="{{ route('guru.lms.assignments.create', $course->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase transition hover:bg-blue-700 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-1"></i> Buat Tugas
                </a>
            </div>

            {{-- Submission Progress Overview --}}
            @if($course->assignments->count() > 0)
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-chart-bar text-blue-500"></i> Ringkasan Pengumpulan Tugas
                    </span>
                    <span class="text-[10px] font-bold text-gray-500">{{ $totalStudents }} siswa terdaftar</span>
                </div>
                @foreach($course->assignments as $asgn)
                @php $subCount = $asgn->submissions_count ?? 0; $subPercent = $totalStudents > 0 ? round(($subCount / $totalStudents) * 100) : 0; @endphp
                <div class="flex items-center gap-3 py-1.5">
                    <span class="text-[10px] font-semibold text-gray-600 w-32 truncate">{{ Str::limit($asgn->title, 20) }}</span>
                    <div class="flex-1 bg-white/60 rounded-full h-2 overflow-hidden border border-blue-100">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full progress-animate" style="width: {{ $subPercent }}%"></div>
                    </div>
                    <span class="text-[10px] font-bold text-blue-700 w-20 text-right">{{ $subCount }}/{{ $totalStudents }}</span>
                </div>
                @endforeach
            </div>
            @endif

            @forelse($course->assignments as $assignment)
            @php
                $hasModule = (bool)$assignment->module;
                $aModColor = $hasModule ? ($assignment->module->color ?? 'blue') : 'blue';
                $aColorClasses = \App\Models\LmsCourse::getColorClasses($aModColor);
                $hasModule = false; // Force light theme
            @endphp
            <div class="rounded-2xl shadow-sm border p-5 transition-all bg-white border-l-4 {{ str_replace('200', '500', $aColorClasses['border'] ?? 'border-blue-500') }} text-gray-800 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 opacity-[0.03] rounded-bl-full pointer-events-none {{ $aColorClasses['bg'] ?? 'bg-blue-600' }}"></div>
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 border {{ $hasModule ? 'bg-white/20 text-white border-white/20 shadow-sm' : 'bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 border-blue-100 shadow-sm' }}">
                            <i class="fas fa-file-invoice text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h4 class="font-bold text-base leading-tight {{ $hasModule ? 'text-white' : 'text-gray-800' }}">{{ $assignment->title }}</h4>
                                @if($assignment->module)
                                    <span class="bg-white/20 text-white text-[9px] font-bold px-2 py-0.5 rounded-lg border border-white/10 uppercase tracking-widest">
                                        {{ $assignment->module->getCode() }}
                                    </span>
                                @else
                                    <span class="bg-gray-50 text-gray-400 text-[9px] font-bold px-2 py-0.5 rounded-lg border border-gray-100 uppercase tracking-widest">Global</span>
                                @endif
                                @if($assignment->allow_resubmit)
                                <span class="bg-blue-50 text-blue-600 text-[9px] font-bold px-2 py-0.5 rounded-full border border-blue-100 uppercase">REVISI OK</span>
                                @endif
                            </div>
                            @if($assignment->description)
                                <p class="text-sm mt-1 mb-3 line-clamp-2 {{ $hasModule ? 'text-white/80' : 'text-gray-500' }}">{{ $assignment->description }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 text-[10px] font-bold uppercase tracking-wider">
                                @if($assignment->deadline)
                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {{ $hasModule ? ($assignment->isOverdue() ? 'bg-rose-500/20 text-white border-rose-500/30' : 'bg-white/15 text-white border-white/10') : ($assignment->isOverdue() ? 'bg-rose-50 text-rose-500 border border-rose-100' : 'bg-gray-50 border border-gray-100') }}">
                                    <i class="fas fa-clock"></i> {{ $assignment->deadline->format('d M Y H:i') }}
                                    @if($assignment->isOverdue()) <span class="animate-pulse">TELAT</span> @endif
                                </span>
                                @endif
                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {{ $hasModule ? 'bg-white/15 text-white border-white/10' : 'bg-gray-50 border border-gray-100' }}"><i class="fas fa-star {{ $hasModule ? 'text-yellow-200' : 'text-amber-400' }}"></i> SKOR: {{ $assignment->max_score }}</span>
                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {{ $hasModule ? 'bg-white/25 text-white border-white/20' : 'bg-blue-50 text-blue-500 border-blue-100' }}"><i class="fas fa-paper-plane"></i> {{ $assignment->submissions_count ?? 0 }} TERKUMPUL</span>
                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {{ $hasModule ? 'bg-white/15 text-white border-white/10' : 'bg-gray-50 border border-gray-100' }}"><i class="fas fa-tag"></i> {{ $assignment->getAssignmentTypeLabel() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 ml-4 flex-shrink-0">
                        <a href="{{ route('guru.lms.assignments.edit', $assignment->id) }}" class="w-10 h-10 rounded-xl flex items-center justify-center transition-all border {{ $hasModule ? 'bg-white/20 text-white border-white/15 hover:bg-white/30' : 'bg-gray-50 text-gray-400 border-gray-100 hover:bg-yellow-50 hover:text-yellow-600' }}"><i class="fas fa-edit text-xs"></i></a>
                        <a href="{{ route('guru.lms.assignments.show', $assignment->id) }}" class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition shadow-sm hover:shadow-md flex items-center justify-center gap-1.5 {{ $hasModule ? 'bg-white text-' . $aModColor . '-700 hover:bg-gray-50' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                            <i class="fas fa-check-double"></i> KOREKSi
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl shadow-sm border p-12 text-center">
                <div class="w-20 h-20 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-tasks text-3xl text-blue-300"></i></div>
                <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Tugas</h3>
                <p class="text-sm text-gray-400">Buat tugas pertama untuk mengevaluasi pemahaman siswa.</p>
            </div>
            @endforelse
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: QUIZZES --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'quizzes'" class="mt-6 space-y-4 tab-content">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center"><i class="fas fa-question-circle text-purple-600 text-xs"></i></span>
                    EVALUASI & QUIZ
                </h3>
                <a href="{{ route('guru.lms.quizzes.create', $course->id) }}" class="bg-purple-600 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase transition hover:bg-purple-700 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-1"></i> Buat Quiz
                </a>
            </div>

            @forelse($course->quizzes as $quiz)
            @php
                $hasModule = (bool)$quiz->module;
                $qModColor = $hasModule ? ($quiz->module->color ?? 'purple') : 'purple';
                $qColorClasses = \App\Models\LmsCourse::getColorClasses($qModColor);
                $hasModule = false; // Force light theme
            @endphp
            <div class="rounded-2xl shadow-sm border p-5 transition-all bg-white border-l-4 {{ str_replace('200', '500', $qColorClasses['border'] ?? 'border-blue-500') }} text-gray-800 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 opacity-[0.03] rounded-bl-full pointer-events-none {{ $qColorClasses['bg'] ?? 'bg-blue-600' }}"></div>
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 border {{ $hasModule ? 'bg-white/20 text-white border-white/20 shadow-sm' : 'bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600 border-purple-100 shadow-sm' }}">
                            <i class="fas fa-vial text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h4 class="font-bold text-base leading-tight {{ $hasModule ? 'text-white' : 'text-gray-800' }}">{{ $quiz->title }}</h4>
                                @if($quiz->module)
                                    <span class="bg-white/20 text-white text-[9px] font-bold px-2 py-0.5 rounded-lg border border-white/10 uppercase tracking-widest">
                                        {{ $quiz->module->getCode() }} · {{ $quiz->module->title }}
                                    </span>
                                @else
                                    <span class="bg-gray-50 text-gray-400 text-[9px] font-bold px-2 py-0.5 rounded-lg border border-gray-100 uppercase tracking-widest">Global</span>
                                @endif
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase tracking-widest border {{ $hasModule ? 'bg-white/30 text-white border-white/20' : ($quiz->is_published ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-yellow-50 text-yellow-600 border-yellow-100') }}">
                                    {{ $quiz->is_published ? 'PUBLISHED' : 'DRAFT' }}
                                </span>
                            </div>
                            @if($quiz->description)
                                <p class="text-sm mt-1 mb-3 line-clamp-2 {{ $hasModule ? 'text-white/80' : 'text-gray-500' }}">{{ $quiz->description }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 text-[10px] font-bold uppercase tracking-wider">
                                @if($quiz->time_limit)
                                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {{ $hasModule ? 'bg-white/15 text-white border-white/10' : 'bg-gray-50 text-gray-400 border-gray-100' }}">
                                        <i class="fas fa-stopwatch {{ $hasModule ? 'text-white/90' : 'text-orange-400' }}"></i> {{ $quiz->time_limit }} MENIT
                                    </span>
                                @endif
                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {{ $hasModule ? 'bg-white/15 text-white border-white/10' : 'bg-gray-50 text-gray-400 border-gray-100' }}">
                                    <i class="fas fa-check-circle {{ $hasModule ? 'text-white/90' : 'text-emerald-400' }}"></i> PASSING: {{ $quiz->passing_score }}%
                                </span>
                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {{ $hasModule ? 'bg-white/25 text-white border-white/20' : 'bg-purple-50 text-purple-500 border-purple-100' }}">
                                    <i class="fas fa-users"></i> {{ $quiz->attempts_count ?? 0 }} PERCOBAAN
                                </span>
                                @if($quiz->shuffle_questions)
                                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {{ $hasModule ? 'bg-white/25 text-white border-white/20' : 'bg-orange-50 text-orange-500 border-orange-100' }}">
                                        <i class="fas fa-random"></i> ACAK
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 ml-4 flex-shrink-0">
                        <a href="{{ route('guru.lms.quizzes.show', $quiz->id) }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition text-center flex justify-center items-center gap-1.5 {{ $hasModule ? 'bg-white text-' . $qModColor . '-700 hover:bg-gray-50 shadow-sm' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                            <i class="fas fa-cog"></i> Kelola
                        </a>
                        <a href="{{ route('guru.lms.quizzes.results', $quiz->id) }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition text-center flex justify-center items-center gap-1.5 {{ $hasModule ? 'bg-white/20 text-white hover:bg-white/30 border border-white/10' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                            <i class="fas fa-chart-bar"></i> Hasil
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl shadow-sm border p-12 text-center">
                <div class="w-20 h-20 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-question-circle text-3xl text-purple-300"></i></div>
                <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Quiz</h3>
                <p class="text-sm text-gray-400">Buat quiz untuk mengevaluasi pemahaman siswa.</p>
            </div>
            @endforelse
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: ANNOUNCEMENTS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'announcements'" class="mt-6 space-y-4 tab-content">
            <div x-data="{ showForm: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <span class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fas fa-bullhorn text-amber-600 text-xs"></i></span>
                        PAPAN PENGUMUMAN
                    </h3>
                    <button @click="showForm = !showForm" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase transition hover:bg-amber-700 shadow-md hover:shadow-lg">
                        <i class="fas fa-plus mr-1"></i> Buat Pengumuman
                    </button>
                </div>

                <form x-show="showForm" x-transition action="{{ route('guru.lms.announcements.store', $course->id) }}" method="POST" class="bg-white rounded-2xl shadow-md border border-amber-100 p-6 mb-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Judul Pengumuman</label>
                            <input type="text" name="title" required placeholder="Contoh: Jadwal Ujian Tengah Semester..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Isi Pesan</label>
                            <textarea name="content" required placeholder="Tuliskan detail pengumuman untuk siswa..." rows="4" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all"></textarea>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer group">
                                <input type="checkbox" name="is_pinned" value="1" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                <span class="group-hover:text-amber-700 transition-colors">Sematkan di atas</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" @click="showForm = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase">Batal</button>
                                <button type="submit" class="bg-amber-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-amber-700 transition shadow-sm text-sm uppercase tracking-widest">PUBLISH</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @forelse($course->announcements as $announcement)
            <div class="bg-white rounded-2xl shadow-sm border {{ $announcement->is_pinned ? 'border-amber-200 ring-1 ring-amber-100' : 'border-gray-100' }} p-5 relative overflow-hidden hover:shadow-md transition-shadow">
                @if($announcement->is_pinned)
                <div class="absolute top-0 right-0">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[8px] font-bold px-3 py-1 rounded-bl-xl uppercase tracking-widest shadow-sm"><i class="fas fa-thumbtack mr-1"></i>PINNED</div>
                </div>
                @endif
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center">
                                <i class="fas fa-bullhorn text-amber-500 text-xs"></i>
                            </div>
                            <h4 class="font-bold text-gray-800">{{ $announcement->title }}</h4>
                        </div>
                        <div class="prose prose-sm text-gray-600 max-w-none ml-10">{!! nl2br(e($announcement->content)) !!}</div>
                        <div class="flex gap-4 mt-4 ml-10 text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                            <span class="flex items-center gap-1.5"><i class="fas fa-user-circle"></i> {{ $announcement->author->name ?? 'Sistem' }}</span>
                            <span class="flex items-center gap-1.5"><i class="fas fa-clock"></i> {{ $announcement->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <form action="{{ route('guru.lms.announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Hapus pengumuman?')" class="ml-4">
                        @csrf @method('DELETE')
                        <button class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-rose-50 hover:text-rose-500 transition-all border border-gray-100 flex items-center justify-center">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl shadow-sm border p-12 text-center">
                <div class="w-20 h-20 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-bullhorn text-3xl text-amber-300"></i></div>
                <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Pengumuman</h3>
                <p class="text-sm text-gray-400">Buat pengumuman untuk memberitahu siswa tentang hal penting.</p>
            </div>
            @endforelse
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: DISCUSSIONS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'discussions'" class="mt-6 tab-content">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center max-w-2xl mx-auto">
                <div class="w-20 h-20 bg-gradient-to-br from-cyan-50 to-cyan-100 text-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-cyan-100 shadow-inner">
                    <i class="fas fa-comments text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Forum Diskusi Interaktif</h3>
                <p class="text-gray-500 mb-8 px-4">Ruang untuk tanya jawab dan diskusi tentang materi course ini.</p>
                
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <div class="text-2xl font-bold text-cyan-600 leading-none">{{ $course->discussions_count ?? 0 }}</div>
                        <div class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-widest">Topik Diskusi</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <div class="text-2xl font-bold text-cyan-600 leading-none">0</div>
                        <div class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-widest">Belum Terjawab</div>
                    </div>
                </div>

                <a href="{{ route('guru.lms.discussions.index', $course->id) }}" class="inline-flex items-center gap-2 bg-cyan-600 text-white px-8 py-3.5 rounded-xl font-bold hover:bg-cyan-700 transition shadow-lg hover:shadow-xl uppercase tracking-widest text-sm">
                    Buka Forum Diskusi <i class="fas fa-arrow-right"></i>
                </a>
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
                <div class="stat-card bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800 leading-none">{{ $totalStudents }}</div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Siswa Aktif</div>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-white rounded-2xl border border-emerald-100 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center">
                            <i class="fas fa-chart-line text-emerald-600"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800 leading-none">{{ min($avgProgress, 100) }}%</div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Rata-rata Progress</div>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-white rounded-2xl border border-amber-100 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center">
                            <i class="fas fa-paper-plane text-amber-600"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800 leading-none">{{ $totalSubmissions }}</div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Tugas Terkumpul</div>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-white rounded-2xl border border-purple-100 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center">
                            <i class="fas fa-vial text-purple-600"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800 leading-none">{{ $totalQuizAttempts }}</div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Quiz Dikerjakan</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart 1: Progress Membaca Materi -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-book-open text-emerald-600 text-xs"></i></span>
                        PROGRES MEMBACA MATERI BELAJAR
                    </h4>
                    <div class="h-80 relative chart-container">
                        @if(empty($materialsData))
                        <div class="absolute inset-0 flex items-center justify-center text-gray-400 italic text-sm bg-gray-50 rounded-xl">
                            Belum ada data progres membaca.
                        </div>
                        @else
                        <canvas id="materialsChart"></canvas>
                        @endif
                    </div>
                </div>

                <!-- Chart 2: Distribusi Nilai Kuis -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center"><i class="fas fa-poll text-purple-600 text-xs"></i></span>
                        DISTRIBUSI NILAI KUIS
                    </h4>
                    <div class="h-80 relative chart-container">
                        @if(array_sum(array_values($quizScores)) === 0)
                        <div class="absolute inset-0 flex items-center justify-center text-gray-400 italic text-sm bg-gray-50 rounded-xl">
                            Belum ada kuis yang dikerjakan.
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
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-7 h-7 bg-orange-100 rounded-lg flex items-center justify-center"><i class="fas fa-user-friends text-orange-600 text-xs"></i></span>
                        Kelas Terdaftar ({{ $course->lmsClasses->count() }})
                    </h4>
                    
                    <div class="space-y-3">
                        @forelse($course->lmsClasses as $lmsClass)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 group hover:bg-white hover:border-orange-200 hover:shadow-sm transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-200 flex items-center justify-center font-bold text-gray-600">
                                    {{ substr($lmsClass->classroom->class_name ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <span class="font-bold text-gray-700">{{ $lmsClass->classroom->class_name ?? 'N/A' }}</span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[9px] font-bold uppercase tracking-tighter">{{ $lmsClass->getStatusLabel() }}</span>
                                        <span class="text-gray-300">·</span>
                                        <span class="text-[10px] text-gray-400 font-medium">Semester {{ $course->semester->semester_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <div class="text-lg font-bold text-gray-700 leading-none">{{ $lmsClass->getEnrolledCount() }}</div>
                                    <div class="text-[9px] font-bold text-gray-400 mt-1 uppercase">Siswa</div>
                                </div>
                                <form action="{{ route('guru.lms.students.enroll', $course->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="classroom_id" value="{{ $lmsClass->classroom_id }}">
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-white border border-gray-100 text-gray-400 hover:text-orange-600 hover:border-orange-200 hover:bg-orange-50 transition-all flex items-center justify-center shadow-sm" title="Sinkronisasi Siswa">
                                        <i class="fas fa-sync-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        @if($course->classroom)
                        <div class="flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <span class="font-bold text-gray-700">{{ $course->classroom->class_name }}</span>
                        </div>
                        @else
                        <div class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <i class="fas fa-users-slash text-3xl text-gray-200 mb-3"></i>
                            <p class="text-gray-400 text-xs font-medium">Belum ada kelas yang didaftarkan.</p>
                        </div>
                        @endif
                        @endforelse
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 flex justify-end">
                        <a href="{{ route('guru.lms.students.index', $course->id) }}" class="text-xs font-bold text-orange-600 hover:text-orange-700 uppercase tracking-widest flex items-center gap-2">
                            Kelola Pendaftaran <i class="fas fa-plus-circle"></i>
                        </a>
                    </div>
                </div>

                {{-- Description --}}
                @if($course->description)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-info-circle text-blue-600 text-xs"></i></span>
                        Deskripsi Mata Pelajaran
                    </h4>
                    <div class="prose prose-sm text-gray-600 max-w-none">{!! nl2br(e($course->description)) !!}</div>
                </div>
                @endif
            </div>

            {{-- Access Config Sidebar --}}
            <div class="space-y-6">
                <div class="bg-gradient-to-br {{ $colorConfig['gradient'] }} rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 opacity-10"><i class="fas fa-shield-alt text-8xl"></i></div>
                    <h4 class="font-bold text-sm uppercase tracking-widest mb-6 border-b border-white/20 pb-3 flex items-center gap-2">
                        <i class="fas fa-cog"></i> Konfigurasi Akses
                    </h4>
                    
                    <div class="space-y-4 relative z-10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium opacity-80 uppercase tracking-wider">Status Publish</span>
                            <span class="bg-white/20 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase">{{ $course->is_published ? 'ON' : 'OFF' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium opacity-80 uppercase tracking-wider">Tingkat Kelas</span>
                            <span class="bg-white/20 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase">KELAS {{ $courseClassroom->grade_level ?? ($firstLmsClassroom->grade_level ?? '?') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium opacity-80 uppercase tracking-wider">Kode Akses</span>
                            <code class="bg-white/20 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase">{{ $course->code ?: '-' }}</code>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium opacity-80 uppercase tracking-wider">Enrollment</span>
                            <span class="bg-white/20 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase">MANUAL/SYNC</span>
                        </div>
                    </div>

                    <a href="{{ route('guru.lms.edit', $course->id) }}" class="block w-full mt-8 bg-white/20 hover:bg-white/30 border border-gray-100 text-white rounded-xl py-2.5 text-xs font-bold uppercase tracking-widest transition-all text-center">
                        Pengaturan Lanjut
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
        gameType: 'quiz',
        pairs: [{term: '', definition: ''}],
        wheelItems: ['Hadiah 1', 'Hadiah 2', 'Zonk', 'Jackpot'],
        quizQuestions: [{ question: '', options: ['', '', '', ''], answer: 0 }],
        tfStatements: [{ statement: '', is_true: true }],
        guessWords: [{ word: '', hint: '' }],
        scrambleWords: [{ word: '', hint: '' }],
        sequenceItems: [{ item: '' }],
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
        addSequenceItem() { this.sequenceItems.push({ item: '' }) },
        removeSequenceItem(index) { this.sequenceItems.splice(index, 1) },
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
            if (this.gameType === 'sequence') return JSON.stringify({ items: this.sequenceItems.filter(i => i.item.trim() !== '') });
            if (this.gameType === 'image_hotspot') return JSON.stringify({ hotspots: this.hotspots.filter(h => h.label.trim() !== '') });
            if (this.gameType === 'chem_balancer') return JSON.stringify({ equations: this.chemEquations.filter(e => e.equation.trim() !== '') });
            if (this.gameType === 'math_ninja') return JSON.stringify({ config: this.mathConfig });
            return JSON.stringify({ pairs: this.pairs.filter(p => p.term.trim() !== '' && p.definition.trim() !== '') });
        }
    }" 
    @open-game-modal.window="open = true" 
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" style="display: none">
    
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-2xl w-full relative z-10 border border-indigo-100">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white font-bold tracking-wide flex items-center gap-2"><i class="fas fa-gamepad"></i> Game Builder Studio</h3>
                <button @click="open = false" class="text-white/70 hover:text-white transition-colors bg-white/10 w-8 h-8 rounded-full flex items-center justify-center"><i class="fas fa-times"></i></button>
            </div>
            
            <form action="{{ route('guru.lms.games.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <input type="hidden" name="game_data" :value="getGameData()">

                <div class="space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Modul</label>
                            <select name="module_id" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">-- Pilih Modul --</option>
                                @foreach($course->modules as $mod)
                                    <option value="{{ $mod->id }}">{{ $mod->getCode() }} - {{ $mod->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">EXP Reward (Poin)</label>
                            <input type="number" name="reward_points" value="50" min="0" max="1000" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Judul Game</label>
                        <input type="text" name="title" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Contoh: Kuis Cepat Modul 1" required>
                    </div>

                    <div class="p-4 bg-red-50 border border-red-100 rounded-2xl" x-show="['quiz', 'true_false', 'word_guess', 'scramble', 'sequence'].includes(gameType)">
                        <h4 class="text-xs font-bold text-red-600 uppercase tracking-widest mb-3 flex items-center gap-2"><i class="fas fa-fire"></i> Pengaturan Hardcore Mode (Opsional)</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Batas Waktu (Detik)</label>
                                <input type="number" name="time_limit" min="5" max="300" placeholder="Kosongkan jika tak terbatas" class="w-full rounded-xl border-red-200 bg-white text-sm focus:border-red-500 focus:ring-red-500 placeholder-gray-400">
                                <p class="text-[10px] text-gray-500 mt-1">Siswa akan gagal otomatis jika waktu habis.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Batas Nyawa</label>
                                <input type="number" name="lives_count" min="1" max="10" placeholder="Kosongkan jika tak terbatas" class="w-full rounded-xl border-red-200 bg-white text-sm focus:border-red-500 focus:ring-red-500 placeholder-gray-400">
                                <p class="text-[10px] text-gray-500 mt-1">Siswa Game Over jika nyawa habis. Khusus Tebak Kata selalu 5 nyawa.</p>
                            </div>
                        </div>
                    </div>


                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Tipe Game</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="flashcard" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-3 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-layer-group text-2xl mb-2 text-indigo-400 peer-checked:text-indigo-600"></i>
                                    <p class="text-xs font-bold text-gray-600">Flashcard 3D</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="match" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-3 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-puzzle-piece text-2xl mb-2 text-purple-400 peer-checked:text-purple-600"></i>
                                    <p class="text-xs font-bold text-gray-600">Match Pairs</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="spin_wheel" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-3 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-dharmachakra text-2xl mb-2 text-pink-400 peer-checked:text-pink-600"></i>
                                    <p class="text-xs font-bold text-gray-600">Spin Wheel</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="quiz" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-3 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-list-check text-2xl mb-2 text-emerald-400 peer-checked:text-emerald-600"></i>
                                    <p class="text-xs font-bold text-gray-600">Kuis</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="true_false" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-3 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all">
                                    <i class="fas fa-check-double text-2xl mb-2 text-blue-400 peer-checked:text-blue-600"></i>
                                    <p class="text-xs font-bold text-gray-600">Benar/Salah</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="word_guess" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-3 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all h-full flex flex-col items-center justify-center">
                                    <i class="fas fa-keyboard text-2xl mb-2 text-amber-400 peer-checked:text-amber-600"></i>
                                    <p class="text-[11px] font-bold text-gray-600 leading-tight">Tebak Kata</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="scramble" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-3 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all h-full flex flex-col items-center justify-center">
                                    <i class="fas fa-cubes text-2xl mb-2 text-orange-400 peer-checked:text-orange-600"></i>
                                    <p class="text-[11px] font-bold text-gray-600 leading-tight">Susun Kata</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="sequence" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-100 p-3 text-center hover:border-indigo-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all h-full flex flex-col items-center justify-center">
                                    <i class="fas fa-sort-amount-down text-2xl mb-2 text-cyan-400 peer-checked:text-cyan-600"></i>
                                    <p class="text-[11px] font-bold text-gray-600 leading-tight">Urutkan</p>
                                </div>
                            </label>
                            
                            <!-- STEM Games -->
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="image_hotspot" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-emerald-100 p-3 text-center hover:border-emerald-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all h-full flex flex-col items-center justify-center shadow-[0_0_10px_rgba(16,185,129,0.1)]">
                                    <i class="fas fa-microscope text-2xl mb-2 text-emerald-500 peer-checked:text-emerald-700"></i>
                                    <p class="text-[11px] font-bold text-emerald-700 leading-tight">Titik Buta (Biologi/Geografi)</p>
                                </div>
                            </label>
                            
                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="chem_balancer" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-sky-100 p-3 text-center hover:border-sky-300 peer-checked:border-sky-500 peer-checked:bg-sky-50 transition-all h-full flex flex-col items-center justify-center shadow-[0_0_10px_rgba(14,165,233,0.1)]">
                                    <i class="fas fa-flask text-2xl mb-2 text-sky-500 peer-checked:text-sky-700"></i>
                                    <p class="text-[11px] font-bold text-sky-700 leading-tight">Reaksi Kimia</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="game_type" value="math_ninja" x-model="gameType" class="peer sr-only">
                                <div class="rounded-xl border-2 border-purple-100 p-3 text-center hover:border-purple-300 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all h-full flex flex-col items-center justify-center shadow-[0_0_10px_rgba(168,85,247,0.1)]">
                                    <i class="fas fa-calculator text-2xl mb-2 text-purple-500 peer-checked:text-purple-700"></i>
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
                                        <input type="text" x-model="pairs[index].term" placeholder="Istilah / Pertanyaan" class="flex-1 rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <input type="text" x-model="pairs[index].definition" placeholder="Definisi / Jawaban" class="flex-1 rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                                        <input type="text" x-model="wheelItems[index]" placeholder="Label Item (Misal: 100 EXP, Zonk)" class="flex-1 rounded-lg border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500">
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
                                            <input type="text" x-model="quizQuestions[index].question" placeholder="Pertanyaan..." class="flex-1 rounded-lg border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 font-medium">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 pl-8">
                                            <template x-for="(opt, optIdx) in q.options" :key="optIdx">
                                                <div class="flex items-center gap-2">
                                                    <input type="radio" :name="'correct_answer_'+index" :value="optIdx" x-model.number="quizQuestions[index].answer" class="text-emerald-500 focus:ring-emerald-500 w-4 h-4">
                                                    <input type="text" x-model="quizQuestions[index].options[optIdx]" :placeholder="'Opsi ' + ['A','B','C','D'][optIdx]" class="flex-1 rounded-md border-gray-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
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
                                        <input type="text" x-model="tfStatements[index].statement" placeholder="Tuliskan pernyataan di sini..." class="flex-1 rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <select x-model="tfStatements[index].is_true" class="rounded-lg border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 w-28 font-bold" :class="(tfStatements[index].is_true === 'true' || tfStatements[index].is_true === true) ? 'text-emerald-600' : 'text-rose-600'">
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
                                        <input type="text" x-model="guessWords[index].word" placeholder="Kata (Tanpa Spasi)" class="w-1/3 rounded-lg border-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500 font-mono uppercase">
                                        <input type="text" x-model="guessWords[index].hint" placeholder="Petunjuk / Clue" class="flex-1 rounded-lg border-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
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
                                        <input type="text" x-model="scrambleWords[index].word" placeholder="Kata Benda/Kerja (Tanpa Spasi)" class="w-1/3 rounded-lg border-gray-200 text-sm focus:border-orange-500 focus:ring-orange-500 font-mono uppercase">
                                        <input type="text" x-model="scrambleWords[index].hint" placeholder="Petunjuk Singkat" class="flex-1 rounded-lg border-gray-200 text-sm focus:border-orange-500 focus:ring-orange-500">
                                        <button type="button" @click="removeScrambleWord(index)" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Editor: Urutkan (Sequence) --}}
                        <div x-show="gameType === 'sequence'" style="display: none;">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-800">Langkah / Urutan Benar</h4>
                                <button type="button" @click="addSequenceItem()" class="text-xs bg-cyan-100 text-cyan-600 px-2 py-1 rounded-lg font-bold hover:bg-cyan-200 transition"><i class="fas fa-plus"></i> Tambah Urutan</button>
                            </div>
                            <p class="text-xs text-gray-500 mb-3"><i class="fas fa-info-circle text-cyan-500"></i> Masukkan daftar langkah dari urutan PERTAMA (atas) hingga TERAKHIR (bawah). Sistem akan mengacaknya otomatis saat dimainkan.</p>
                            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(seq, index) in sequenceItems" :key="index">
                                    <div class="flex gap-2 items-center bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                                        <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center text-cyan-500 font-bold text-xs" x-text="index + 1"></div>
                                        <input type="text" x-model="sequenceItems[index].item" placeholder="Contoh: Panaskan air hingga mendidih" class="flex-1 rounded-lg border-gray-200 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                        <button type="button" @click="removeSequenceItem(index)" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fas fa-times"></i></button>
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
                                        <input type="text" x-model="hotspots[index].label" placeholder="Nama Area (misal: Mitokondria)" class="flex-1 rounded-lg border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <input type="number" x-model="hotspots[index].x" placeholder="X %" min="0" max="100" class="w-20 rounded-lg border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <input type="number" x-model="hotspots[index].y" placeholder="Y %" min="0" max="100" class="w-20 rounded-lg border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
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
                                        <input type="text" x-model="chemEquations[index].equation" placeholder="Contoh: _ H2 + _ O2 -> _ H2O (Gunakan underscore _)" class="w-full rounded-lg border-gray-200 text-sm focus:border-sky-500 focus:ring-sky-500 font-mono">
                                        <div class="flex gap-2 items-center">
                                            <input type="text" x-model="chemEquations[index].answers" placeholder="Jawaban Koefisien (pisahkan koma: 2,1,2)" class="flex-1 rounded-lg border-gray-200 text-sm focus:border-sky-500 focus:ring-sky-500">
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
                                    <select x-model="mathConfig.operation" class="w-full rounded-lg border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="add">Penjumlahan (+)</option>
                                        <option value="sub">Pengurangan (-)</option>
                                        <option value="mul">Perkalian (x)</option>
                                        <option value="mixed">Campuran Acak</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Tingkat Kesulitan</label>
                                    <select x-model="mathConfig.difficulty" class="w-full rounded-lg border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
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
                        <i class="fas fa-save"></i> Buat Game
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- MATERIAL UPLOAD MODAL --}}
{{-- ═══════════════════════════════════════════════ --}}
<div x-data="{ open: false, type: 'document' }" @open-material-modal.window="open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-lg w-full relative z-10 border border-gray-100">
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white font-bold tracking-wide flex items-center gap-2"><i class="fas fa-upload"></i> Upload Materi Baru</h3>
                <button @click="open = false" class="text-white/70 hover:text-white transition-colors"><i class="fas fa-times"></i></button>
            </div>
            
            <form action="{{ route('guru.lms.materials.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Modul Target</label>
                        <select name="module_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                            <option value="">-- Pilih Modul --</option>
                            @foreach($course->modules as $mod)
                            <option value="{{ $mod->id }}">Modul {{ $mod->sequence }}: {{ $mod->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Judul Materi</label>
                        <input type="text" name="title" required placeholder="Contoh: Pengantar Algoritma..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Tipe Materi</label>
                            <select name="material_type" required x-model="type" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                                <option value="document">Dokumen</option>
                                <option value="pdf">PDF</option>
                                <option value="video">Video</option>
                                <option value="image">Gambar</option>
                                <option value="link">Link Eksternal</option>
                                <option value="interactive">Game / Interaktif (Embed)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">File (Opsional, Maks. 10 MB)</label>
                            <input type="file" name="file" class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        </div>
                    </div>

                    <!-- Info Batasan Video & File -->
                    <div x-show="type === 'video'" class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-700 space-y-2" style="display: none">
                        <p class="font-bold flex items-center gap-1">
                            <i class="fas fa-info-circle text-blue-600 text-sm"></i> Informasi Upload Video:
                        </p>
                        <ul class="list-disc list-inside space-y-1 ml-1 text-blue-600">
                            <li>Format yang didukung: <strong>MP4</strong> (disarankan agar dapat diputar langsung di browser).</li>
                            <li>Ukuran file maksimal: <strong>10 MB</strong> (melalui upload langsung).</li>
                            <li class="text-orange-700 font-medium mt-1"><strong>Rekomendasi:</strong> Jika video terlalu besar/berat (&gt;10 MB), disarankan untuk menguploadnya ke <strong>YouTube</strong> terlebih dahulu, lalu masukkan linknya pada kolom <strong>URL</strong> di bawah. Ini akan menghemat penyimpanan hosting dan memastikan video berjalan lancar bagi siswa.</li>
                        </ul>
                    </div>

                    <div x-show="type === 'interactive'" class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-xs text-purple-700 space-y-2" style="display: none">
                        <p class="font-bold flex items-center gap-1">
                            <i class="fas fa-gamepad text-purple-600 text-sm"></i> Informasi Game / Interaktif (Embed):
                        </p>
                        <p>Masukkan Link URL Game dari platform edukasi eksternal (Contoh: Quizizz, Wordwall, PhET, H5P) ke dalam kolom <strong>URL/Link</strong> di bawah ini.</p>
                        <p class="font-bold">Sistem akan secara otomatis menyematkannya ke dalam halaman materi agar siswa bisa memainkannya langsung!</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">URL (Opsional)</label>
                        <input type="url" name="file_url" placeholder="https://..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Isi Konten Teks (Opsional)</label>
                        <textarea name="content" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all"></textarea>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="open = false" class="flex-1 px-6 py-3 rounded-xl font-bold bg-gray-50 text-gray-400 hover:text-gray-600 transition-all uppercase tracking-widest text-xs">Batal</button>
                        <button type="submit" class="flex-1 px-6 py-3 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-lg hover:shadow-emerald-200 uppercase tracking-widest text-xs">Simpan Materi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- MATERIAL EDIT MODAL --}}
{{-- ═══════════════════════════════════════════════ --}}
<div x-data="{ open: false, mat: {} }" @open-edit-material-modal.window="mat = $event.detail; open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-lg w-full relative z-10 border border-gray-100">
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white font-bold tracking-wide flex items-center gap-2"><i class="fas fa-edit"></i> Edit Materi Pembelajaran</h3>
                <button @click="open = false" class="text-white/70 hover:text-white transition-colors"><i class="fas fa-times"></i></button>
            </div>
            
            <form :action="mat.update_url" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Modul Target</label>
                        <select name="module_id" x-model="mat.module_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                            @foreach($course->modules as $mod)
                            <option value="{{ $mod->id }}">Modul {{ $mod->sequence }}: {{ $mod->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Judul Materi</label>
                        <input type="text" name="title" x-model="mat.title" required placeholder="Judul Materi..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Keterangan / Teks Materi</label>
                        <textarea name="content" x-model="mat.content" rows="4" placeholder="Keterangan materi atau instruksi..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-all"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Tipe Materi</label>
                            <select name="material_type" x-model="mat.material_type" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                                <option value="document">Dokumen</option>
                                <option value="pdf">PDF</option>
                                <option value="video">Video</option>
                                <option value="image">Gambar</option>
                                <option value="link">Link Eksternal</option>
                                <option value="interactive">Game / Interaktif (Embed)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Ganti File (Maks. 10 MB)</label>
                            <input type="file" name="file" class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                        </div>
                    </div>

                    <div x-show="mat.material_type === 'video' || mat.material_type === 'link' || mat.material_type === 'interactive'" style="display: none;">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">URL / Link (YouTube, Game, dll)</label>
                        <input type="url" name="file_url" x-model="mat.file_url" placeholder="https://..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="open = false" class="flex-1 px-6 py-3 rounded-xl font-bold bg-gray-50 text-gray-400 hover:text-gray-600 transition-all uppercase tracking-widest text-xs">Batal</button>
                        <button type="submit" class="flex-1 px-6 py-3 rounded-xl font-bold bg-amber-600 text-white hover:bg-amber-700 transition-all shadow-lg hover:shadow-amber-200 uppercase tracking-widest text-xs">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
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
@endpush
