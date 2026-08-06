@extends('layouts.guru')

@section('title', 'Kelola Ruang Ajar - ' . $course->name)

@push('styles')
<style>
    .tab-content { animation: tabFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) both; }
    @keyframes tabFadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .module-card { animation: tabFadeIn 0.3s ease both; }
    .stat-card {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
    }
    .chart-container {
        background: linear-gradient(135deg, rgba(248,250,252,0.8) 0%, rgba(241,245,249,0.5) 100%);
        border-radius: 1.25rem;
        padding: 0.75rem;
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
    {{-- COURSE HERO BANNER (UI-UX PRO MAX PREMIUM UI) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 text-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border border-slate-700/60">
        {{-- Background Glow --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            {{-- Back Navigation + Actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <a href="{{ route('guru.lms.index') }}" class="inline-flex items-center gap-2 bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700/80 px-4 py-2 rounded-xl text-xs font-semibold backdrop-blur-md transition-all">
                    <i class="fas fa-arrow-left text-xs"></i> Kembali ke Daftar Course
                </a>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('guru.lms.meeting.attendance', $course->id) }}" class="bg-slate-800/80 border border-slate-700 text-slate-200 hover:bg-slate-700 px-3.5 py-2 rounded-xl text-xs font-semibold backdrop-blur-md transition-all flex items-center gap-2">
                        <i class="fas fa-clipboard-user text-amber-400"></i> Rekap Kehadiran
                    </a>
                    @if($course->meeting_active)
                    <a href="{{ route('guru.lms.meeting', $course->id) }}" target="_blank" class="bg-emerald-500 text-slate-950 hover:bg-emerald-400 px-4 py-2 rounded-xl text-xs font-extrabold shadow-lg transition-all flex items-center gap-2 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-slate-950"></span> PERTEMUAN AKTIF
                    </a>
                    @else
                    <form action="{{ route('guru.lms.meeting.start', $course->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-slate-800 border border-slate-700 text-emerald-400 hover:bg-slate-700 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5">
                            <i class="fas fa-video"></i> Mulai Live Meeting
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Course Title & Info --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ $course->getShortCode() }}</span>
                        <span class="bg-slate-800 text-slate-300 border border-slate-700 px-3 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider">{{ $course->subject->subject_name ?? '-' }}</span>
                        @if($classNames)
                        <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 px-3 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"><i class="fas fa-users mr-1"></i>{{ $classNames }}</span>
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">{{ $course->name }}</h1>
                    @if($scientist && isset($scientist['quote']))
                    <p class="text-slate-300 text-xs italic flex items-center gap-2">
                        <i class="fas fa-quote-left text-amber-400 text-xs"></i>
                        <span>"{{ $scientist['quote'] }}"</span> — <strong class="text-amber-400 font-bold not-italic">{{ $scientist['name'] }}</strong>
                    </p>
                    @endif
                </div>

                {{-- Action Pills --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <button onclick="window.dispatchEvent(new CustomEvent('open-material-modal'))" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold px-5 py-3 rounded-xl text-xs shadow-md transition-all flex items-center gap-2">
                        <i class="fas fa-upload text-xs"></i> Upload Materi
                    </button>
                    <a href="{{ route('guru.lms.modules.create', $course->id) }}" class="bg-slate-800 hover:bg-slate-700 text-white font-semibold px-4 py-3 rounded-xl text-xs border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-plus text-amber-400 text-xs"></i> Tambah Modul
                    </a>
                </div>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB NAVIGATION (UI-UX PRO MAX SEGMENTED PILLS) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div x-data="{ tab: '{{ request('tab', 'materials') }}' }">
        <div class="bg-white p-2 rounded-2xl border border-slate-200/90 shadow-sm overflow-x-auto custom-scrollbar">
            <div class="flex items-center gap-1.5 min-w-max">
                <button @click="tab = 'materials'" :class="tab === 'materials' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold'" class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2">
                    <i class="fas fa-book-open text-xs"></i> Kurikulum / Materi
                    <span :class="tab === 'materials' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $course->modules->sum(fn($m) => $m->materials->count()) }}</span>
                </button>
                <button @click="tab = 'assignments'" :class="tab === 'assignments' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold'" class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2">
                    <i class="fas fa-tasks text-xs"></i> Tugas Siswa
                    <span :class="tab === 'assignments' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $course->assignments->count() }}</span>
                </button>
                <button @click="tab = 'quizzes'" :class="tab === 'quizzes' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold'" class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2">
                    <i class="fas fa-vial text-xs"></i> Kuis & Evaluasi
                    <span :class="tab === 'quizzes' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $course->quizzes->count() }}</span>
                </button>
                <button @click="tab = 'announcements'" :class="tab === 'announcements' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold'" class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2">
                    <i class="fas fa-bullhorn text-xs"></i> Pengumuman
                    <span :class="tab === 'announcements' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $course->announcements->count() }}</span>
                </button>
                <button @click="tab = 'discussions'" :class="tab === 'discussions' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold'" class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2">
                    <i class="fas fa-comments text-xs"></i> Diskusi
                </button>
                <button @click="tab = 'analytics'" :class="tab === 'analytics' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold'" class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2">
                    <i class="fas fa-chart-line text-xs"></i> Analitik Belajar
                </button>
                <button @click="tab = 'info'" :class="tab === 'info' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold'" class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2">
                    <i class="fas fa-info-circle text-xs"></i> Data Kelas
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: MATERIALS / MODULES --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'materials'" class="mt-6 space-y-6 tab-content">
            @forelse($course->modules as $module)
            <div class="module-card bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden transition-all">
                {{-- Module Header --}}
                <div class="px-5 py-4 flex items-center justify-between bg-slate-900 text-white border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-extrabold text-xs">
                            {{ $module->sequence }}
                        </span>
                        <div>
                            <h3 class="font-bold text-white text-sm md:text-base leading-snug">{{ $module->title }}</h3>
                            <p class="text-[11px] text-slate-400 font-medium">{{ $module->materials->count() }} Materi Pembelajaran</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('guru.lms.modules.edit', [$course->id, $module->id]) }}" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-colors text-xs" title="Edit Modul">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('guru.lms.modules.destroy', [$course->id, $module->id]) }}" method="POST" onsubmit="return confirm('Hapus modul ini beserta materinya?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-xl bg-rose-950/80 border border-rose-800/80 text-rose-400 hover:bg-rose-900 flex items-center justify-center transition-colors text-xs" title="Hapus Modul">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Materials List --}}
                <div class="p-4 space-y-3 bg-slate-50/50">
                    @forelse($module->materials as $material)
                    <div x-data="{ expanded: false }" class="bg-white rounded-xl border border-slate-200/90 shadow-sm hover:shadow-md transition-all overflow-hidden">
                        <div class="flex items-center justify-between p-3.5 cursor-pointer bg-white hover:bg-slate-50 transition-colors" @click="expanded = !expanded">
                            <div class="flex items-center gap-3.5">
                                @php
                                    $bgIcon = match($material->material_type) {
                                        'pdf' => 'bg-rose-100 text-rose-600 border-rose-200',
                                        'video' => 'bg-sky-100 text-sky-600 border-sky-200',
                                        'image' => 'bg-emerald-100 text-emerald-600 border-emerald-200',
                                        'link' => 'bg-purple-100 text-purple-600 border-purple-200',
                                        'interactive' => 'bg-indigo-100 text-indigo-600 border-indigo-200',
                                        default => 'bg-slate-100 text-slate-600 border-slate-200',
                                    };
                                    $iconClass = match($material->material_type) {
                                        'pdf' => 'fa-file-pdf',
                                        'video' => 'fa-play-circle',
                                        'image' => 'fa-image',
                                        'link' => 'fa-link',
                                        'interactive' => 'fa-gamepad',
                                        default => 'fa-file-alt',
                                    };
                                @endphp
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center border shrink-0 shadow-xs {{ $bgIcon }}">
                                    <i class="fas {{ $iconClass }} text-base"></i>
                                </div>
                                <div>
                                    <h5 class="text-xs md:text-sm font-bold text-slate-800 leading-tight group-hover:text-emerald-600 transition-colors">
                                        <span class="text-slate-400 font-semibold mr-1 text-[11px]">{{ $module->getCode() }}-{{ $loop->iteration }}</span>
                                        {{ preg_replace('/^\d+\.\d+\s*/', '', $material->title) }}
                                    </h5>
                                    <p class="text-[11px] text-slate-500 font-medium mt-0.5 flex items-center gap-2">
                                        <span class="capitalize">{{ $material->material_type }}</span>
                                        @if($material->file_size)
                                        <span class="text-slate-300">•</span>
                                        <span>{{ number_format($material->file_size / 1024, 0) }} KB</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button @click.stop="$dispatch('open-edit-material-modal', {{ json_encode([
                                    'id' => $material->id,
                                    'module_id' => $material->module_id,
                                    'title' => preg_replace('/^\d+\.\d+\s*/', '', $material->title),
                                    'material_type' => $material->material_type,
                                    'content' => $material->content,
                                    'file_url' => $material->file_url,
                                    'update_url' => route('guru.lms.materials.update', $material->id)
                                ]) }})" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors text-xs" title="Edit Materi">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <form action="{{ route('guru.lms.materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')" class="inline" @click.stop>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors text-xs" title="Hapus Materi">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <i class="fas fa-chevron-down text-slate-400 text-xs transition-transform duration-300" :class="expanded ? 'rotate-180' : ''"></i>
                            </div>
                        </div>

                        {{-- Expanded Material Content --}}
                        <div x-show="expanded" x-transition x-cloak class="px-5 pb-5 border-t border-slate-100 bg-slate-50/50">
                            <div class="mt-4">
                                @if($material->material_type === 'video')
                                    @if($material->isYouTubeVideo())
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border border-slate-200 bg-slate-900 mb-4 h-[350px] md:h-[450px]">
                                        <iframe class="w-full h-full" src="{{ $material->getVideoEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                    </div>
                                    @else
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border border-slate-200 bg-slate-900 mb-4 h-[350px] md:h-[450px]">
                                        <video class="w-full h-full object-contain" controls preload="metadata">
                                            <source src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" type="video/mp4">
                                            Browser Anda tidak mendukung pemutar video.
                                        </video>
                                    </div>
                                    @endif
                                @elseif($material->material_type === 'pdf')
                                    <div class="w-full rounded-2xl overflow-hidden shadow-md border border-slate-200 bg-white mb-4 h-[500px]">
                                        <iframe src="{{ $material->file_path ? route('guru.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" class="w-full h-full" frameborder="0"></iframe>
                                    </div>
                                @endif

                                {{-- Text Content safely balanced --}}
                                @if($material->content)
                                <div class="prose prose-sm max-w-none text-slate-800 mt-3 p-4 rounded-xl bg-white border border-slate-200 shadow-xs">
                                    {!! strip_tags($material->content) !== $material->content ? balanceHtmlTags($material->content) : balanceHtmlTags(nl2br(e($material->content))) !!}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 bg-white rounded-xl border border-slate-200 text-center">
                        <p class="text-xs text-slate-500 font-semibold italic">Belum ada materi di modul ini.</p>
                    </div>
                    @endforelse
                </div>

                {{-- Games List inside Module --}}
                @if($module->games->count() > 0)
                <div class="px-4 pb-4 space-y-2 bg-slate-50/50 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-700 tracking-wider uppercase mb-2 pt-3 flex items-center gap-1.5">
                        <i class="fas fa-gamepad text-indigo-600"></i> Mini Games Pembelajaran ({{ $module->games->count() }})
                    </h4>
                    @foreach($module->games as $game)
                    <div class="rounded-xl border border-slate-200 bg-white p-3 flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-sm shadow-xs">
                                <i class="fas fa-gamepad"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800 text-xs flex items-center gap-2">
                                    {{ $game->title }}
                                    <span class="bg-amber-100 text-amber-800 text-[9px] font-bold px-2 py-0.5 rounded uppercase">{{ str_replace('_', ' ', $game->game_type) }}</span>
                                </p>
                                <p class="text-[10px] text-slate-500 font-medium mt-0.5"><i class="fas fa-star text-amber-500 mr-1"></i>Reward: {{ $game->reward_points }} EXP</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(in_array($game->game_type, ['quiz', 'true_false']))
                            <form action="{{ route('guru.lms.games.live.create', $game->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs flex items-center gap-1.5 transition-colors">
                                    <i class="fas fa-satellite-dish text-amber-400 text-xs"></i> Host Live Game
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('guru.lms.games.destroy', $game->id) }}" method="POST" onsubmit="return confirm('Hapus game ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors text-xs" title="Hapus Game">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <i class="fas fa-folder-open text-2xl"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-1">Belum Ada Modul Ajar</h3>
                <p class="text-slate-500 text-xs mb-5 max-w-sm mx-auto">Buat modul pertama untuk menyusun materi digital dan aktivitas pembelajaran kelas Anda.</p>
                <a href="{{ route('guru.lms.modules.create', $course->id) }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold text-xs shadow-sm transition-all">
                    <i class="fas fa-plus text-xs"></i> Buat Modul Baru
                </a>
            </div>
            @endforelse
        </div>


        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: ASSIGNMENTS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'assignments'" class="mt-6 space-y-4 tab-content" style="display: none;">
            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Penugasan Siswa</h3>
                    <p class="text-slate-500 text-xs mt-0.5">Kelola instruksi tugas dan penilaian berkas siswa.</p>
                </div>
                <a href="{{ route('guru.lms.assignments.create', $course->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Buat Tugas Baru
                </a>
            </div>

            @forelse($course->assignments as $assignment)
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1.5 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded uppercase">Poin: {{ $assignment->max_points }}</span>
                        @if($assignment->due_date)
                        <span class="bg-slate-100 text-slate-600 text-[10px] font-semibold px-2.5 py-0.5 rounded"><i class="fas fa-clock mr-1"></i>Tenggat: {{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y, H:i') }}</span>
                        @endif
                    </div>
                    <h4 class="text-base font-bold text-slate-900">{{ $assignment->title }}</h4>
                    <p class="text-xs text-slate-600 line-clamp-2">{{ $assignment->instructions }}</p>
                </div>

                <div class="flex items-center gap-3 border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
                    <a href="{{ route('guru.lms.assignments.show', [$course->id, $assignment->id]) }}" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-4 py-2.5 rounded-xl text-xs shadow-sm transition-colors flex items-center gap-2">
                        <i class="fas fa-user-check text-amber-400 text-xs"></i> Periksa Pengumpulan ({{ $assignment->submissions->count() }})
                    </a>
                    <a href="{{ route('guru.lms.assignments.edit', [$course->id, $assignment->id]) }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors text-xs" title="Edit Tugas">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
                <p class="text-xs text-slate-500 font-semibold italic">Belum ada tugas yang dibuat untuk course ini.</p>
            </div>
            @endforelse
        </div>


        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: QUIZZES --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'quizzes'" class="mt-6 space-y-4 tab-content" style="display: none;">
            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Kuis & Evaluasi Pembelajaran</h3>
                    <p class="text-slate-500 text-xs mt-0.5">Kelola soal kuis dan mode live game interaktif.</p>
                </div>
                <a href="{{ route('guru.lms.quizzes.create', $course->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Buat Kuis Baru
                </a>
            </div>

            @forelse($course->quizzes as $quiz)
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1.5 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="bg-indigo-100 text-indigo-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded uppercase">{{ $quiz->questions->count() }} Soal</span>
                        <span class="bg-slate-100 text-slate-600 text-[10px] font-semibold px-2.5 py-0.5 rounded"><i class="fas fa-stopwatch mr-1"></i>{{ $quiz->duration_minutes }} Menit</span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">{{ $quiz->title }}</h4>
                    <p class="text-xs text-slate-600 line-clamp-2">{{ $quiz->description }}</p>
                </div>

                <div class="flex items-center gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-slate-100 flex-wrap">
                    <a href="{{ route('guru.lms.quizzes.results', [$course->id, $quiz->id]) }}" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-4 py-2.5 rounded-xl text-xs shadow-sm transition-colors flex items-center gap-2">
                        <i class="fas fa-poll text-amber-400 text-xs"></i> Hasil Nilai
                    </a>
                    <a href="{{ route('guru.lms.quizzes.show', [$course->id, $quiz->id]) }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-colors text-xs" title="Kelola Soal">
                        <i class="fas fa-list-ol"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
                <p class="text-xs text-slate-500 font-semibold italic">Belum ada kuis evaluasi yang dibuat.</p>
            </div>
            @endforelse
        </div>


        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: ANNOUNCEMENTS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'announcements'" class="mt-6 space-y-4 tab-content" style="display: none;">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm space-y-3">
                <h3 class="text-base font-bold text-slate-800">Buat Pengumuman Baru</h3>
                <form action="{{ route('guru.lms.announcements.store', $course->id) }}" method="POST">
                    @csrf
                    <textarea name="content" rows="3" required placeholder="Tuliskan pengumuman penting untuk siswa di course ini..." class="w-full border border-slate-200 rounded-xl p-3.5 text-xs text-slate-800 font-medium focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all flex items-center gap-1.5">
                            <i class="fas fa-paper-plane text-xs"></i> Kirim Pengumuman
                        </button>
                    </div>
                </form>
            </div>

            @forelse($course->announcements as $announcement)
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                            {{ substr($announcement->user->name ?? 'G', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">{{ $announcement->user->name ?? 'Guru' }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ $announcement->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                <div class="text-xs text-slate-700 font-medium leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    {!! nl2br(e($announcement->content)) !!}
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
                <p class="text-xs text-slate-500 font-semibold italic">Belum ada pengumuman di kelas ini.</p>
            </div>
            @endforelse
        </div>


        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: DISCUSSIONS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'discussions'" class="mt-6 tab-content" style="display: none;">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-sm text-center">
                <i class="fas fa-comments text-3xl text-emerald-500 mb-2"></i>
                <h3 class="text-base font-bold text-slate-800">Forum Diskusi Kelas</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-4">Gunakan forum diskusi untuk berinteraksi dan menjawab pertanyaan siswa seputar materi pelajaran.</p>
                <a href="{{ route('guru.lms.discussions.index', $course->id) }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                    <i class="fas fa-external-link-alt text-xs"></i> Buka Forum Diskusi
                </a>
            </div>
        </div>


        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: ANALYTICS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'analytics'" class="mt-6 space-y-6 tab-content" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm space-y-4">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-chart-bar text-emerald-600"></i> Progres Membaca Materi
                    </h4>
                    <div class="h-64 chart-container">
                        <canvas id="materialsChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm space-y-4">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-chart-pie text-amber-500"></i> Distribusi Nilai Kuis
                    </h4>
                    <div class="h-64 chart-container">
                        <canvas id="quizzesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>


        {{-- ═══════════════════════════════════════════════ --}}
        {{-- TAB: INFO / CLASS DATA --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'info'" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6 tab-content" style="display: none;">
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Deskripsi Course</h3>
                    <div class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100">
                        {!! nl2br(e($course->description ?: 'Belum ada deskripsi.')) !!}
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Kode Akses Kelas</h3>
                    <div class="bg-slate-900 text-white p-4 rounded-xl text-center space-y-1">
                        <span class="text-2xl font-black tracking-widest text-amber-400 font-mono">{{ $course->access_code ?: 'PUBLIC' }}</span>
                        <p class="text-[10px] text-slate-400">Bagikan kode ini kepada siswa untuk bergabung.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════ --}}
{{-- MATERIAL UPLOAD MODAL --}}
{{-- ═══════════════════════════════════════════════ --}}
<div x-data="{ open: false, type: 'document', file_url: '', material_title: '' }" @open-material-modal.window="open = true" x-show="open" class="fixed inset-0 overflow-y-auto" style="display: none; z-index: 99999 !important;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="open" x-transition class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="open = false"></div>

        <div x-show="open" x-transition class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-lg w-full relative border border-slate-200 z-50">
            <div class="px-6 py-4 flex items-center justify-between bg-slate-900 text-white">
                <h3 class="text-white font-bold tracking-wide flex items-center gap-2 text-sm uppercase"><i class="fas fa-upload text-emerald-400"></i> Upload Materi Pembelajaran</h3>
                <button @click="open = false" class="text-slate-400 hover:text-white transition-colors w-8 h-8 rounded-lg flex items-center justify-center"><i class="fas fa-times"></i></button>
            </div>
            
            <form action="{{ route('guru.lms.materials.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Modul Target <span class="text-rose-500">*</span></label>
                        <select name="module_id" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            <option value="">-- Pilih Modul --</option>
                            @foreach($course->modules as $mod)
                            <option value="{{ $mod->id }}">Modul {{ $mod->sequence }}: {{ $mod->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul Materi <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" x-model="material_title" required placeholder="Contoh: Pengantar Algoritma..." class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tipe Materi</label>
                            <select name="material_type" required x-model="type" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                                <option value="document">Dokumen Word/PPT</option>
                                <option value="pdf">PDF</option>
                                <option value="video">Video</option>
                                <option value="image">Gambar</option>
                                <option value="link">Link Eksternal</option>
                                <option value="interactive">Game / Simulator Embed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">File (Maks. 10 MB)</label>
                            <input type="file" name="file" class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">URL / Link Pembelajaran (Opsional)</label>
                        <input type="url" name="file_url" x-model="file_url" placeholder="https://..." class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Isi Konten Teks / Keterangan (Opsional)</label>
                        <textarea name="content" rows="3" placeholder="Tuliskan petunjuk atau rangkuman materi..." class="w-full border border-slate-200 rounded-xl p-3.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="open = false" class="flex-1 px-5 py-2.5 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all text-xs">Batal</button>
                        <button type="submit" class="flex-1 px-5 py-2.5 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm text-xs"><i class="fas fa-save mr-1"></i> Simpan Materi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════ --}}
{{-- MATERIAL EDIT MODAL --}}
{{-- ═══════════════════════════════════════════════ --}}
<div x-data="{ open: false, mat: {} }" @open-edit-material-modal.window="mat = $event.detail; open = true" x-show="open" class="fixed inset-0 overflow-y-auto" style="display: none; z-index: 99999 !important;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="open" x-transition class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="open = false"></div>

        <div x-show="open" x-transition class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-lg w-full relative border border-slate-200 z-50">
            <div class="px-6 py-4 flex items-center justify-between bg-slate-900 text-white">
                <h3 class="text-white font-bold tracking-wide flex items-center gap-2 text-sm uppercase"><i class="fas fa-edit text-emerald-400"></i> Edit Materi Pembelajaran</h3>
                <button @click="open = false" class="text-slate-400 hover:text-white transition-colors w-8 h-8 rounded-lg flex items-center justify-center"><i class="fas fa-times"></i></button>
            </div>
            
            <form :action="mat.update_url" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Modul Target</label>
                        <select name="module_id" x-model="mat.module_id" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            @foreach($course->modules as $mod)
                            <option value="{{ $mod->id }}">Modul {{ $mod->sequence }}: {{ $mod->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul Materi</label>
                        <input type="text" name="title" x-model="mat.title" required placeholder="Judul Materi..." class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Isi Konten Teks / Keterangan</label>
                        <textarea name="content" x-model="mat.content" rows="4" placeholder="Keterangan materi atau instruksi..." class="w-full border border-slate-200 rounded-xl p-3.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tipe Materi</label>
                            <select name="material_type" x-model="mat.material_type" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                                <option value="document">Dokumen Word/PPT</option>
                                <option value="pdf">PDF</option>
                                <option value="video">Video</option>
                                <option value="image">Gambar</option>
                                <option value="link">Link Eksternal</option>
                                <option value="interactive">Game / Interaktif Embed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ganti File (Maks. 10 MB)</label>
                            <input type="file" name="file" class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        </div>
                    </div>

                    <div x-show="mat.material_type === 'video' || mat.material_type === 'link' || mat.material_type === 'interactive'" style="display: none;">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">URL / Link Pembelajaran</label>
                        <input type="url" name="file_url" x-model="mat.file_url" placeholder="https://..." class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="open = false" class="flex-1 px-5 py-2.5 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all text-xs">Batal</button>
                        <button type="submit" class="flex-1 px-5 py-2.5 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm text-xs"><i class="fas fa-save mr-1"></i> Perbarui Materi</button>
                    </div>
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
        const customTooltip = {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleFont: { size: 12, weight: 'bold' },
            bodyFont: { size: 11 },
            padding: 12,
            cornerRadius: 10,
            displayColors: true,
            boxPadding: 4
        };

        const matCtx = document.getElementById('materialsChart');
        if (matCtx) {
            const matData = @json($materialsData);
            const labels = matData.map(d => d.title);
            const counts = matData.map(d => d.count);

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
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.7)'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11 },
                                color: '#6b7280'
                            }
                        },
                        tooltip: customTooltip
                    }
                }
            });
        }
    });
</script>
@endpush
