<?php
if (!function_exists('balanceHtmlTags')) {
    function balanceHtmlTags($html) {
        if (empty(trim($html ?? ''))) return $html;
        try {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $balanced = $dom->saveHTML();
            libxml_clear_errors();
            $balanced = str_replace(['<?xml encoding="utf-8" ?>', '<html>', '</html>', '<body>', '</body>'], '', $balanced);
            return trim($balanced);
        } catch (\Throwable $e) {
            return $html;
        }
    }
}
?>
@extends('layouts.siswa')

@section('title', $course->name . ' - LMS Siswa')

@push('styles')
<style>
    .prose img { display: block !important; max-width: 100% !important; height: auto !important; margin: 1.25rem auto !important; border-radius: 0.75rem !important; box-shadow: 0 4px 14px rgba(0,0,0,0.12) !important; clear: both !important; }
    .prose p { margin-bottom: 0.4rem !important; line-height: 1.6 !important; }
    .prose table { width: 100% !important; border-collapse: collapse !important; margin: 0.75rem 0 !important; font-size: 0.875rem !important; }
    .prose th, .prose td { border: 1px solid #cbd5e1 !important; padding: 0.5rem 0.75rem !important; text-align: left !important; }
    .prose th { background-color: #f1f5f9 !important; font-weight: 800 !important; color: #0f172a !important; }
    .prose tr:nth-child(even) { background-color: #f8fafc; }
    .prose ol { list-style-type: decimal !important; list-style-position: outside !important; margin-left: 1.5rem !important; padding-left: 0.5rem !important; margin-top: 0.5rem !important; margin-bottom: 0.5rem !important; }
    .prose ul { list-style-type: disc !important; list-style-position: outside !important; margin-left: 1.5rem !important; padding-left: 0.5rem !important; margin-top: 0.5rem !important; margin-bottom: 0.5rem !important; }
    .prose li { display: list-item !important; margin-bottom: 0.35rem !important; padding-left: 0.25rem !important; }
    .prose code { background-color: #f1f5f9 !important; color: #0f172a !important; padding: 0.15rem 0.4rem !important; border-radius: 0.375rem !important; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important; font-size: 0.875em !important; border: 1px solid #e2e8f0 !important; }
    .tab-content { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .hero-pattern {
        background-image: radial-gradient(circle at 25% 60%, rgba(255,255,255,0.08) 0%, transparent 50%),
                          radial-gradient(circle at 75% 20%, rgba(255,255,255,0.06) 0%, transparent 40%);
    }
    /* Confetti pulse for completion */
    @keyframes confettiPulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.15); opacity: 0.85; }
        100% { transform: scale(1); opacity: 1; }
    }
    .confetti-pulse { animation: confettiPulse 0.6s ease-in-out; }
    /* Slide down for material expand */
    @keyframes slideDown {
        from { opacity: 0; max-height: 0; transform: translateY(-8px); }
        to { opacity: 1; max-height: 1000px; transform: translateY(0); }
    }
    [x-show] .slide-down-content { animation: slideDown 0.35s ease-out; }
    /* Material card hover shimmer */
    .material-card { position: relative; overflow: hidden; }
    .material-card::after {
        content: '';
        position: absolute;
        top: 0; left: -100%; width: 50%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        transition: left 0.5s ease;
        pointer-events: none;
    }
    .material-card:hover::after { left: 100%; }
    /* Quiz card hover glow */
    .quiz-card { transition: all 0.3s ease; }
    .quiz-card:hover { box-shadow: 0 0 20px rgba(147, 51, 234, 0.12), 0 4px 12px rgba(0,0,0,0.05); }
    .quiz-card-passed { box-shadow: 0 0 15px rgba(16, 185, 129, 0.1); }
    .quiz-card-passed:hover { box-shadow: 0 0 25px rgba(16, 185, 129, 0.18), 0 4px 12px rgba(0,0,0,0.05); }
    /* Badge-new pulse for unviewed materials */
    @keyframes badgeNewPulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5); }
        50% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(59, 130, 246, 0); }
    }
    .badge-new { animation: badgeNewPulse 2s ease-in-out infinite; }
    /* Pulse animation for first quiz attempt button */
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.5); }
        50% { box-shadow: 0 0 0 8px rgba(147, 51, 234, 0); }
    }
    .btn-quiz-first { animation: pulseGlow 2s ease-in-out infinite; }
    /* Confetti particles */
    @keyframes confettiFall {
        0% { transform: translateY(0) rotate(0deg); opacity: 1; }
        100% { transform: translateY(80px) rotate(360deg); opacity: 0; }
    }
    .confetti-particle {
        position: fixed; width: 8px; height: 8px; border-radius: 2px;
        pointer-events: none; z-index: 9999;
        animation: confettiFall 0.8s ease-out forwards;
    }
    /* Module progress ring animation */
    .module-progress-ring circle.progress-arc {
        transition: stroke-dashoffset 0.8s ease-in-out;
    }
</style>
@endpush

@section('content')
@php
    $colorConfig = \App\Models\LmsCourse::getColorClasses($course->color);
    $scientist = $course->getScientistConfig();
    $pendingAssignments = $course->assignments->filter(fn($a) => !isset($submissionMap[$a->id]) || $submissionMap[$a->id]->status === 'draft')->count();
    $availableQuizzes = $course->quizzes->filter(fn($q) => $q->isAvailable())->count();
@endphp
<div class="space-y-6" x-data="{ tab: '{{ request('tab', 'modules') }}' }">
    @if(session('success'))
    <div class="bg-emerald-50 border-2 border-emerald-500 text-emerald-800 p-4 rounded-2xl flex items-center gap-3 shadow-md">
        <i class="fas fa-check-circle text-emerald-600 text-xl flex-shrink-0"></i>
        <div class="font-bold text-sm">{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-rose-50 border-2 border-rose-500 text-rose-800 p-4 rounded-2xl flex items-center gap-3 shadow-md">
        <i class="fas fa-exclamation-triangle text-rose-600 text-xl flex-shrink-0"></i>
        <div class="font-bold text-sm">{{ session('error') }}</div>
    </div>
    @endif

    {{-- Active Video Conference Banner --}}
    @if($course->meeting_active)
    <div class="bg-gradient-to-r from-rose-500 via-pink-600 to-rose-600 rounded-2xl shadow-xl p-0.5 overflow-hidden animate-pulse">
        <div class="bg-slate-900/90 backdrop-blur-md px-5 py-4 rounded-[14px] flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-white">
                <div class="w-12 h-12 bg-rose-500/20 text-rose-500 rounded-xl flex items-center justify-center text-2xl relative flex-shrink-0">
                    <span class="absolute inline-flex h-full w-full rounded-xl bg-rose-400 opacity-75 animate-ping"></span>
                    <i class="fas fa-video relative"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-white leading-tight flex items-center gap-2">
                        Kelas Tatap Muka Sedang Berlangsung! 
                        <span class="bg-rose-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full animate-bounce">LIVE</span>
                    </h3>
                    <p class="text-gray-400 text-xs mt-0.5">
                        Guru Anda telah memulai tatap muka virtual. Klik tombol untuk bergabung ke kelas.
                    </p>
                </div>
            </div>
            <a href="{{ route('siswa.lms.meeting.join', $course->id) }}" class="w-full md:w-auto bg-rose-600 hover:bg-rose-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg hover:shadow-rose-900/30 transition transform hover:scale-105 active:scale-95 text-center text-xs uppercase tracking-wider">
                Gabung Kelas Virtual
            </a>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- COURSE HERO BANNER (100% SOLID UI UX PRO MAX) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border-2 border-black">
        <div class="relative z-10">
            {{-- Back --}}
            <a href="{{ route('siswa.lms.index') }}" class="inline-flex items-center gap-2 bg-black text-white border-2 border-black px-4 py-2 rounded-xl text-xs font-black hover:bg-amber-400 hover:text-black transition-all shadow-md mb-5">
                <i class="fas fa-arrow-left text-xs"></i> Kembali ke Daftar Ruang Belajar
            </a>

            {{-- Course Info --}}
            <div class="flex items-start mb-5" style="display: flex; align-items: flex-start; gap: 1.25rem;">
                <div class="w-16 h-16 rounded-2xl overflow-hidden shadow-md flex-shrink-0 border-2 border-black bg-white" style="margin-right: 1.25rem; flex-shrink: 0;">
                    <img src="{{ $course->getTeacherPhotoUrl() }}" alt="{{ $course->teacher->user->name ?? 'Guru Pengajar' }}" class="w-full h-full object-cover object-center" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($course->teacher->user->name ?? 'Guru') }}&background=0f172a&color=ffffff&bold=true'">
                </div>
                <div style="min-width: 0; flex: 1;">
                    <h1 class="text-2xl md:text-3xl font-black text-black tracking-tight leading-tight">{{ $course->course_name ?? $course->name }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="border-2 border-black px-3 py-1 rounded-xl text-xs font-black" style="background-color: #fbbf24 !important; color: #000000 !important;">{{ $course->subject->subject_name ?? '' }}</span>
                        <span class="text-black text-xs font-black flex items-center gap-1.5"><i class="fas fa-user-tie text-black text-sm"></i> Pengajar: {{ $course->teacher->user->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Progress + Quick Stats --}}
            <div class="flex flex-wrap items-center gap-4">
                {{-- Progress Ring --}}
                <div class="bg-blue-100 border-2 border-black rounded-2xl px-5 py-3 flex items-center gap-3 shadow-md">
                    <div class="relative w-12 h-12">
                        <svg class="w-12 h-12 progress-ring" viewBox="0 0 36 36">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#000000" stroke-width="4"/>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#2563eb" stroke-width="4" stroke-dasharray="{{ $courseProgress }}, 100" stroke-linecap="round"/>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xs font-black text-black">{{ number_format($courseProgress) }}%</span>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-black">Progress Belajar</div>
                        <div class="text-sm font-black text-black">{{ $courseProgress >= 80 ? 'Hampir Selesai!' : ($courseProgress >= 40 ? 'Lanjutkan Belajar!' : ($courseProgress > 0 ? 'Baru Mulai' : 'Mulai Belajar')) }}</div>
                    </div>
                </div>

                <div class="bg-white border-2 border-black rounded-2xl px-5 py-2.5 text-center min-w-[90px] shadow-md">
                    <div class="text-xl font-black leading-none text-black">{{ $course->modules->count() }}</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-black mt-1">Modul Ajar</div>
                </div>

                {{-- Certificate claim when 100% --}}
                @if($courseProgress >= 100)
                <form action="{{ route('siswa.lms.certificates.claim', $course->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-gradient-to-r from-amber-500 to-orange-500 text-white border-2 border-black rounded-2xl px-5 py-2.5 text-center min-w-[90px] shadow-md hover:shadow-lg transition transform hover:scale-105">
                        <div class="text-xl font-black leading-none">🏆</div>
                        <div class="text-[8px] font-black uppercase tracking-widest mt-0.5">Ambil Sertifikat</div>
                    </button>
                </form>
                @endif

                @if($pendingAssignments > 0)
                <div class="border-2 border-black rounded-2xl px-5 py-2.5 text-center min-w-[90px] shadow-md" style="background-color: #fef08a !important;">
                    <div class="text-xl font-black leading-none text-black">{{ $pendingAssignments }}</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-black mt-1">Tugas Pending</div>
                </div>
                @endif

                @if($availableQuizzes > 0)
                <div class="border-2 border-black rounded-2xl px-5 py-2.5 text-center min-w-[90px] shadow-md" style="background-color: #e9d5ff !important;">
                    <div class="text-xl font-black leading-none text-black">{{ $availableQuizzes }}</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-black mt-1">Quiz Tersedia</div>
                </div>
                @endif
            </div>
        </div>
    </div>


    {{-- Pinned Announcements --}}
    @if(isset($course->announcements) && $course->announcements->where('is_pinned', true)->count())
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-4 shadow-sm">
        <h4 class="font-bold text-amber-800 text-sm mb-2 flex items-center gap-2">
            <span class="w-6 h-6 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fas fa-bullhorn text-amber-600 text-[10px] rotate-[-15deg]"></i></span>
            PENGUMUMAN PENTING
        </h4>
        @foreach($course->announcements->where('is_pinned', true)->take(2) as $ann)
        <div class="mb-2 last:mb-0 ml-8">
            <p class="font-semibold text-gray-800 text-sm">{{ $ann->title }}</p>
            <p class="text-gray-600 text-xs mt-0.5">{{ Str::limit($ann->content, 120) }}</p>
            <div class="text-[10px] text-gray-400 mt-0.5 uppercase tracking-wider font-medium">{{ $ann->created_at->diffForHumans() }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB NAVIGATION --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-md border-2 border-black p-1.5 flex flex-wrap gap-1.5 sticky top-0 z-20">
        <button @click="tab = 'modules'" :class="tab === 'modules' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-book-open text-sm"></i> <span class="hidden sm:inline">Modul</span>
        </button>
        <button @click="tab = 'assignments'" :class="tab === 'assignments' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-tasks text-sm"></i> <span class="hidden sm:inline">Tugas</span>
            @if($pendingAssignments > 0)<span class="rounded-full px-2 py-0.5 text-[10px] font-black border border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">{{ $pendingAssignments }}</span>@endif
        </button>
        <button @click="tab = 'quizzes'" :class="tab === 'quizzes' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-question-circle text-sm"></i> <span class="hidden sm:inline">Quiz</span>
            @if($availableQuizzes > 0)<span class="rounded-full px-2 py-0.5 text-[10px] font-black border border-black" style="background-color: #c084fc !important; color: #000000 !important;">{{ $availableQuizzes }}</span>@endif
        </button>
        <button @click="tab = 'announcements'" :class="tab === 'announcements' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-bullhorn text-sm"></i> <span class="hidden sm:inline">Info</span>
        </button>
        <button @click="tab = 'discussions'" :class="tab === 'discussions' ? 'bg-black text-white border-2 border-black shadow-md' : 'bg-slate-100 text-black hover:bg-amber-300 font-black'" class="flex-1 min-w-[70px] px-3 py-3 rounded-xl text-xs font-black transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-comments text-sm"></i> <span class="hidden sm:inline">Diskusi</span>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB: MODULES / MATERIALS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div x-show="tab === 'modules'" class="space-y-6 tab-content">
        {{-- Panduan Penyelesaian Modul untuk Siswa --}}
        <div class="bg-white rounded-3xl p-6 border-2 border-black text-black shadow-lg relative overflow-hidden">
            <div class="relative z-10 space-y-3">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-base font-black border-2 border-black shrink-0" style="background-color: #1e3a8a !important; color: #ffffff !important;">
                        <i class="fas fa-info-circle text-white"></i>
                    </span>
                    <h4 class="font-black text-base uppercase tracking-wider text-black">Panduan Penyelesaian Modul Pembelajaran</h4>
                </div>
                <p class="text-xs font-bold text-black leading-relaxed">
                    Untuk menyukseskan pembelajaran Anda dan memperoleh progress 100%, ikuti 4 langkah mudah berikut pada setiap modul:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                    <div class="bg-slate-100 rounded-2xl p-4 border-2 border-black shadow-sm flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0 border-2 border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">1</span>
                        <div>
                            <p class="text-xs font-black text-black uppercase">Pelajari Materi</p>
                            <p class="text-[11px] text-black leading-snug mt-1 font-bold">Simak PDF, Video, atau Link sesuai waktu belajar minimal yang ditentukan.</p>
                        </div>
                    </div>
                    <div class="bg-slate-100 rounded-2xl p-4 border-2 border-black shadow-sm flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0 border-2 border-black" style="background-color: #93c5fd !important; color: #000000 !important;">2</span>
                        <div>
                            <p class="text-xs font-black text-black uppercase">Verifikasi Selesai</p>
                            <p class="text-[11px] text-black leading-snug mt-1 font-bold">Centang kotak pernyataan verifikasi aktif, lalu klik tombol <b>Tandai Selesai</b>.</p>
                        </div>
                    </div>
                    <div class="bg-slate-100 rounded-2xl p-4 border-2 border-black shadow-sm flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0 border-2 border-black" style="background-color: #fcd34d !important; color: #000000 !important;">3</span>
                        <div>
                            <p class="text-xs font-black text-black uppercase">Mainkan Game</p>
                            <p class="text-[11px] text-black leading-snug mt-1 font-bold">Uji daya ingat dan kumpulkan EXP / skor dengan memainkan mini game interaktif.</p>
                        </div>
                    </div>
                    <div class="bg-slate-100 rounded-2xl p-4 border-2 border-black shadow-sm flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0 border-2 border-black" style="background-color: #e9d5ff !important; color: #000000 !important;">4</span>
                        <div>
                            <p class="text-xs font-black text-black uppercase">Kuis & Tugas</p>
                            <p class="text-[11px] text-black leading-snug mt-1 font-bold">Selesaikan Kuis Evaluasi dan kirim Tugas pada tab menu di atas tepat waktu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @forelse($course->modules as $module)
        @php 
            $moduleColor = $module->color ?? 'blue';
            $mColor = \App\Models\LmsCourse::getColorClasses($moduleColor);
            $completedMats = $module->materials->filter(fn($m) => isset($materialProgressMap[$m->id]) && $materialProgressMap[$m->id]->status === 'completed')->count();
            $totalMats = $module->materials->count();
            $modulePercent = $totalMats > 0 ? round(($completedMats / $totalMats) * 100) : 0;
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
            {{-- Module Header --}}
            <div class="bg-gradient-to-r {{ $mColor['gradient'] ?? $mColor['bg'] }} px-5 py-4 flex items-center justify-between shadow-sm relative overflow-hidden">
                <div class="flex items-center gap-4 relative z-10">
                    <span class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-extrabold text-xl border border-white/30 shadow-md">
                        {{ $module->sequence }}
                    </span>
                    <div>
                        <h3 class="font-extrabold text-white text-lg tracking-wide drop-shadow-sm">{{ $module->title }}</h3>
                        <p class="text-white/90 text-xs font-bold uppercase tracking-widest mt-0.5 drop-shadow-sm">
                            {{ $completedMats }}/{{ $totalMats }} SELESAI
                        </p>
                    </div>
                </div>
                @if($totalMats > 0)
                <div class="flex items-center gap-3 relative z-10">
                    {{-- SVG Progress Ring --}}
                    <div class="relative w-10 h-10 flex-shrink-0 module-progress-ring">
                        <svg class="w-10 h-10 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="2.5"/>
                            <circle class="progress-arc" cx="18" cy="18" r="15" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"
                                stroke-dasharray="{{ 2 * 3.14159 * 15 }}" stroke-dashoffset="{{ 2 * 3.14159 * 15 * (1 - $modulePercent / 100) }}"/>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-white drop-shadow-sm">{{ $modulePercent }}%</span>
                    </div>
                    {{-- Progress Bar --}}
                    <div class="hidden sm:flex items-center gap-2">
                        <div class="w-20 bg-white/20 rounded-full h-1.5 overflow-hidden backdrop-blur-sm">
                            <div class="h-full bg-white rounded-full transition-all duration-500 shadow-sm" style="width: {{ $modulePercent }}%"></div>
                        </div>
                    </div>
                    @if($modulePercent >= 100)
                    <span class="bg-white/20 backdrop-blur-sm text-white border border-white/30 text-[8px] font-bold px-2 py-1 rounded-full uppercase tracking-widest confetti-pulse shadow-sm">✓ Tuntas</span>
                    @endif
                </div>
                @endif
            </div>
            
            {{-- Materials List --}}
            <div class="p-4 space-y-2">
                @if($module->description)
                <p class="text-sm text-gray-500 mb-3 italic px-3 py-2 border-l-2 {{ $mColor['border'] }} bg-gray-50 rounded-r-lg">{{ $module->description }}</p>
                @endif
                
                @forelse($module->materials as $material)
                @php 
                    $matProgress = $materialProgressMap[$material->id] ?? null; 
                    $isLocked = false;
                    if (($course->is_sequential || $module->is_sequential || $material->prerequisite_material_id)) {
                        if ($material->prerequisite_material_id && !in_array($material->prerequisite_material_id, $completedMaterialIds ?? [])) {
                            $isLocked = true;
                        } elseif ($loop->index > 0) {
                            $prevMat = $module->materials[$loop->index - 1] ?? null;
                            if ($prevMat && !in_array($prevMat->id, $completedMaterialIds ?? [])) {
                                $isLocked = true;
                            }
                        }
                    }
                @endphp
                <div x-data="{ 
                    expanded: false, 
                    started: false, 
                    verified: false, 
                    timer: {{ $material->material_type === 'video' ? 30 : ($material->material_type === 'pdf' || $material->material_type === 'document' ? 20 : ($material->material_type === 'link' ? 15 : 10)) }}, 
                    timerId: null, 
                    startLearning() { 
                        if (!this.started && this.timer > 0) { 
                            this.started = true; 
                            trackMaterial({{ $material->id }}, 'in_progress'); 
                            this.timerId = setInterval(() => { 
                                if (this.timer > 0) { 
                                    this.timer--; 
                                } else { 
                                    clearInterval(this.timerId); 
                                } 
                            }, 1000); 
                        } 
                    } 
                }" class="rounded-xl border border-gray-100 hover:border-{{ $moduleColor }}-200 hover:bg-{{ $moduleColor }}-50/20 transition-all overflow-hidden group/mat">
                    <div class="flex items-center justify-between p-3.5 cursor-pointer" @click="if(!{{ $isLocked ? 'true' : 'false' }}){ expanded = !expanded; if(expanded) startLearning(); trackMaterial({{ $material->id }}); } else { alert('Materi ini masih terkunci. Selesaikan materi sebelumnya terlebih dahulu.'); }">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-md flex-shrink-0 {{ $isLocked ? 'bg-amber-500' : ($material->material_type === 'pdf' ? 'bg-red-500' : ($material->material_type === 'video' ? 'bg-blue-500' : ($material->material_type === 'image' ? 'bg-green-500' : ($material->material_type === 'link' ? 'bg-purple-500' : ($material->material_type === 'interactive' ? 'bg-indigo-600' : ($material->material_type === 'document' ? 'bg-orange-500' : 'bg-gray-500')))))) }}">
                                <i class="fas text-xl {{ $isLocked ? 'fa-lock' : ($material->material_type === 'pdf' ? 'fa-file-pdf' : ($material->material_type === 'video' ? 'fa-video' : ($material->material_type === 'image' ? 'fa-image' : ($material->material_type === 'link' ? 'fa-link' : ($material->material_type === 'interactive' ? 'fa-gamepad' : ($material->material_type === 'document' ? 'fa-file-alt' : 'fa-file')))))) }}"></i>
                            </span>
                            <div>
                                <p class="font-extrabold text-gray-900 group-hover/mat:text-{{ $moduleColor }}-700 transition-colors text-base"><span class="text-{{ $moduleColor }}-500 opacity-60 font-bold mr-1.5 text-sm">{{ $module->getCode() }}-{{ $loop->iteration }}</span>{{ preg_replace('/^\d+\.\d+\s*/', '', $material->title) }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">{{ $material->getContentTypeLabel() }}</span>
                                    @if($isLocked)
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold border border-amber-300">
                                        <i class="fas fa-lock mr-1"></i> Terkunci
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            @if($isLocked)
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 text-gray-500 text-xs font-bold border border-gray-200">
                                <i class="fas fa-lock text-xs"></i>
                                <span>Terkunci</span>
                            </div>
                            @else
                                <a href="{{ route('siswa.lms.materials.player', $material->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <i class="fas fa-book-open"></i> Focus Reader
                                </a>
                                @if($matProgress && $matProgress->status === 'completed')
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100">
                                    <i class="fas fa-check-circle text-xs"></i>
                                    <span class="text-[9px] font-bold">SELESAI</span>
                                </div>
                                @elseif($matProgress)
                                <div class="w-10 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full {{ $mColor['bg'] }} transition-all rounded-full" style="width: {{ $matProgress->progress_percent }}%"></div>
                                </div>
                                @endif
                            @endif
                            
                            <div class="flex items-center gap-1.5 opacity-0 group-hover/mat:opacity-100 transition-opacity">
                                <i class="fas fa-chevron-down text-gray-300 text-xs transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
                            </div>
                        </div>
                    </div>
                    <div x-show="expanded" x-transition x-cloak @click="startLearning()" class="px-5 pb-4 border-t border-gray-100 bg-gray-50/30">
                        {{-- Media Players --}}
                        <div class="mt-4 mb-3">
                            @if($material->material_type === 'video')
                                @if($material->isYouTubeVideo())
                                    <div class="w-full rounded-xl overflow-hidden shadow-lg border border-gray-200 bg-black mb-4" style="height: 560px; width: 100%;">
                                        <iframe class="w-full h-full" src="{{ $material->getVideoEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                    </div>
                                @else
                                    @if($material->fileExists())
                                    <div class="w-full rounded-xl overflow-hidden shadow-lg border border-gray-200 bg-black mb-4" style="height: 560px; width: 100%;">
                                        <video class="w-full h-full object-contain" controls preload="metadata">
                                            <source src="{{ $material->file_path ? route('siswa.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" type="video/mp4">
                                            Browser Anda tidak mendukung tag video.
                                        </video>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ $material->file_path ? route('siswa.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                            <i class="fas fa-download"></i> Unduh Video
                                        </a>
                                    </div>
                                    @else
                                    <div class="w-full rounded-xl p-8 border border-amber-200 bg-amber-50/60 mb-4 flex flex-col items-center justify-center text-center">
                                        <i class="fas fa-video-slash text-amber-500 text-4xl mb-2"></i>
                                        <p class="font-bold text-gray-800 text-sm">Berkas Video Belum Tersedia</p>
                                        <p class="text-xs text-amber-700 mt-1">Berkas video materi ini belum tersedia di penyimpanan server. Silakan hubungi guru pengampu.</p>
                                    </div>
                                    @endif
                                @endif
                            @elseif($material->material_type === 'image')
                                @if($material->fileExists())
                                <div class="w-full rounded-xl overflow-hidden shadow-md border border-gray-100 bg-gray-900 flex justify-center">
                                    <img src="{{ $material->file_path ? route('siswa.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" class="max-h-[400px] object-contain w-auto h-auto" alt="{{ $material->title }}">
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <a href="{{ $material->file_path ? route('siswa.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                        <i class="fas fa-download"></i> Unduh Gambar
                                    </a>
                                </div>
                                @else
                                <div class="w-full rounded-xl p-8 border border-amber-200 bg-amber-50/60 mb-4 flex flex-col items-center justify-center text-center">
                                    <i class="fas fa-image text-amber-500 text-4xl mb-2"></i>
                                    <p class="font-bold text-gray-800 text-sm">Berkas Gambar Belum Tersedia</p>
                                    <p class="text-xs text-amber-700 mt-1">Berkas gambar materi ini belum tersedia di penyimpanan server.</p>
                                </div>
                                @endif
                            @elseif(($material->material_type === 'pdf' || str_ends_with(strtolower($material->file_name ?? $material->file_path ?? ''), '.pdf') || str_contains(strtolower($material->title ?? ''), '[pdf]')) && strtolower(pathinfo($material->file_name ?? $material->file_path ?? '', PATHINFO_EXTENSION)) === 'pdf')
                                @if($material->fileExists())
                                <!-- Embed PDF Viewer -->
                                <div class="w-full rounded-xl overflow-hidden shadow-md border border-gray-200 bg-white mb-4" style="height: 600px;">
                                    <iframe src="{{ $material->file_path ? route('siswa.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" class="w-full h-full" frameborder="0"></iframe>
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
                                        <a href="{{ $material->file_path ? route('siswa.lms.materials.view', $material->id) : ($material->file_url ?? '#') }}" target="_blank" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/10 font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                            <i class="fas fa-external-link-alt"></i> Buka di Tab Baru
                                        </a>
                                        <a href="{{ $material->file_path ? route('siswa.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                            <i class="fas fa-download"></i> Unduh PDF
                                        </a>
                                    </div>
                                </div>
                                @else
                                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/60 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-md">
                                            <i class="fas fa-file-pdf text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 text-sm">Dokumen PDF Terlampir</p>
                                            <p class="text-[11px] text-amber-800 font-medium">⚠️ Berkas fisik belum tersedia di server{{ $material->file_size ? ' (Ukuran tercatat: ' . number_format($material->file_size / (1024 * 1024), 2) . ' MB)' : '' }}. Silakan hubungi guru pengampu untuk mengunggah ulang.</p>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="px-3 py-1.5 rounded-xl bg-gray-200 text-gray-400 font-bold text-xs flex items-center gap-1.5 cursor-not-allowed">
                                            <i class="fas fa-ban"></i> Berkas Belum Ada
                                        </span>
                                    </div>
                                </div>
                                @endif
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
                                <div class="w-full rounded-xl overflow-hidden shadow-md border border-indigo-100 bg-indigo-900/5 mb-4" style="height: 400px;">
                                    <iframe :src="expanded ? '{{ $material->file_url }}' : ''" class="w-full h-full" frameborder="0" allowfullscreen allow="geolocation *; microphone *; camera *; midi *; encrypted-media *; autoplay *"></iframe>
                                </div>
                                <div class="flex justify-end pt-2">
                                    <a href="{{ $material->file_url }}" target="_blank" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                        <i class="fas fa-external-link-alt"></i> Buka Layar Penuh
                                    </a>
                                </div>
                            @elseif($material->file_path)
                                @php $hasPhysicalFile = $material->fileExists(); @endphp
                                <div class="p-4 rounded-xl border {{ $hasPhysicalFile ? 'border-blue-100 bg-blue-50/30' : 'border-amber-200 bg-amber-50/60' }} flex items-center justify-between gap-4 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl {{ $hasPhysicalFile ? 'bg-blue-500' : 'bg-amber-500' }} text-white flex items-center justify-center shadow-md">
                                            <i class="fas {{ $hasPhysicalFile ? 'fa-file-alt' : 'fa-exclamation-triangle' }} text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 text-sm">Dokumen Terlampir: {{ $material->file_name ?: ($material->title ?: 'File Materi') }}</p>
                                            <p class="text-[10px] {{ $hasPhysicalFile ? 'text-gray-400 font-medium' : 'text-amber-800 font-semibold' }}">
                                                Tipe: {{ strtoupper(pathinfo($material->file_name ?? $material->file_path ?? 'DOC', PATHINFO_EXTENSION)) }}{{ $material->file_size ? ' · ' . number_format($material->file_size / 1024, 0) . ' KB' : '' }}
                                                @if(!$hasPhysicalFile)
                                                    · <span class="text-amber-800">⚠️ Berkas belum tersedia di server (hubungi guru pengampu)</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($hasPhysicalFile)
                                        <a href="{{ route('siswa.lms.materials.view', $material->id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/10 font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                            <i class="fas fa-external-link-alt"></i> Buka / Preview
                                        </a>
                                        <a href="{{ route('siswa.lms.materials.download', $material->id) }}" download class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                            <i class="fas fa-download"></i> Unduh File
                                        </a>
                                        @else
                                        <span class="px-3 py-1.5 rounded-xl bg-gray-200 text-gray-400 font-bold text-xs flex items-center gap-1.5 cursor-not-allowed">
                                            <i class="fas fa-ban"></i> Berkas Belum Ada
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Text Content --}}
                        @if($material->content)
                        <div class="prose prose-sm max-w-none text-gray-600 mt-3 mb-4">{!! formatLmsContent($material->content) !!}</div>
                        @endif
                        
                        {{-- Material Reaction --}}
                        @php $myReaction = $reactionsMap[$material->id]->reaction_type ?? null; @endphp
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3 text-center">Bagaimana tanggapan Anda tentang materi ini?</p>
                            <div class="flex items-center justify-center gap-3">
                                <button @click.stop="reactMaterial({{ $material->id }}, 'like', event)" class="reaction-btn px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $myReaction === 'like' ? 'bg-blue-100 text-blue-700 border-2 border-blue-300' : 'bg-gray-50 text-gray-600 hover:bg-blue-50 hover:text-blue-600 border-2 border-transparent' }}">
                                    👍 Paham
                                </button>
                                <button @click.stop="reactMaterial({{ $material->id }}, 'confused', event)" class="reaction-btn px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $myReaction === 'confused' ? 'bg-orange-100 text-orange-700 border-2 border-orange-300' : 'bg-gray-50 text-gray-600 hover:bg-orange-50 hover:text-orange-600 border-2 border-transparent' }}">
                                    🤔 Membingungkan
                                </button>
                                <button @click.stop="reactMaterial({{ $material->id }}, 'insightful', event)" class="reaction-btn px-4 py-2 rounded-xl text-sm font-bold transition-all {{ $myReaction === 'insightful' ? 'bg-emerald-100 text-emerald-700 border-2 border-emerald-300' : 'bg-gray-50 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 border-2 border-transparent' }}">
                                    🚀 Sangat Menarik
                                </button>
                            </div>
                        </div>

                        @if(!$matProgress || $matProgress->status !== 'completed')
                        <div class="mt-5 p-4 rounded-xl border-2 border-dashed transition-all" :class="timer === 0 && verified ? 'border-emerald-300 bg-emerald-50/50' : 'border-amber-300 bg-amber-50/60'">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-3.5 flex-1">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm transition-all" :class="timer === 0 ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white animate-pulse'">
                                        <i class="fas" :class="timer === 0 ? 'fa-user-shield' : 'fa-hourglass-half'"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-extrabold text-gray-800 text-xs uppercase tracking-wider">Verifikasi Pembelajaran Aktif</p>
                                            <template x-if="timer > 0">
                                                <span class="px-2 py-0.5 rounded-md bg-amber-200 text-amber-900 font-mono text-[10px] font-bold animate-pulse" x-text="'Waktu Belajar: ' + timer + ' detik'"></span>
                                            </template>
                                            <template x-if="timer === 0">
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold"><i class="fas fa-check"></i> Waktu Minimal Terpenuhi</span>
                                            </template>
                                        </div>

                                        <template x-if="timer > 0">
                                            <p class="text-xs text-amber-800 font-medium mt-1 leading-relaxed">
                                                @if($material->material_type === 'video')
                                                    <i class="fas fa-video mr-1 text-amber-600"></i> Wajib menyimak video ini terlebih dahulu sebelum tombol penyelesaian terbuka.
                                                @elseif($material->material_type === 'pdf' || $material->material_type === 'document')
                                                    <i class="fas fa-file-pdf mr-1 text-amber-600"></i> Wajib membaca & mempelajari isi dokumen materi ini sebelum tombol penyelesaian terbuka.
                                                @elseif($material->material_type === 'link')
                                                    <i class="fas fa-link mr-1 text-amber-600"></i> Wajib mengunjungi tautan eksternal ini sebelum tombol penyelesaian terbuka.
                                                @else
                                                    <i class="fas fa-book-reader mr-1 text-amber-600"></i> Wajib membaca materi teks ini sebelum tombol penyelesaian terbuka.
                                                @endif
                                            </p>
                                        </template>

                                        <template x-if="timer === 0">
                                            <label class="flex items-start sm:items-center gap-2.5 mt-2 cursor-pointer select-none group/lbl">
                                                <input type="checkbox" x-model="verified" class="w-4 h-4 mt-0.5 sm:mt-0 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 cursor-pointer shadow-sm">
                                                <span class="text-xs font-bold text-gray-700 group-hover/lbl:text-emerald-700 transition-colors">
                                                    @if($material->material_type === 'video')
                                                        Saya menyatakan telah menonton & memahami isi video pembelajaran ini hingga selesai.
                                                    @elseif($material->material_type === 'pdf' || $material->material_type === 'document')
                                                        Saya menyatakan telah membaca & memahami seluruh isi dokumen materi ini dengan saksama.
                                                    @elseif($material->material_type === 'link')
                                                        Saya menyatakan telah mengunjungi & mempelajari materi pada tautan tersebut.
                                                    @else
                                                        Saya menyatakan telah membaca & memahami materi pembelajaran ini dengan saksama.
                                                    @endif
                                                </span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <div class="w-full sm:w-auto flex justify-end">
                                    <template x-if="timer > 0">
                                        <button disabled class="w-full sm:w-auto px-4 py-2.5 bg-gray-200 text-gray-400 text-xs font-bold rounded-xl cursor-not-allowed flex items-center justify-center gap-2 border border-gray-300">
                                            <i class="fas fa-lock text-gray-400"></i> Tandai Selesai (<span x-text="timer + 's'"></span>)
                                        </button>
                                    </template>

                                    <template x-if="timer === 0">
                                        <button @click.stop="if(verified) { completeMaterial({{ $material->id }}) } else { alert('Silakan centang kotak verifikasi pernyataan bahwa Anda telah membaca/menonton materi ini terlebih dahulu.') }" 
                                                :disabled="!verified"
                                                :class="verified ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md hover:shadow-lg cursor-pointer transform hover:-translate-y-0.5' : 'bg-gray-200 text-gray-400 cursor-not-allowed border border-gray-300'"
                                                class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-2">
                                            <i class="fas" :class="verified ? 'fa-check-double' : 'fa-lock'"></i> Tandai Selesai & Tingkatkan Progress
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="flex justify-end pt-3">
                            <span class="flex items-center gap-2 px-4 py-2 bg-emerald-100 text-emerald-800 rounded-xl text-xs font-extrabold border border-emerald-300 shadow-sm">
                                <i class="fas fa-check-circle text-emerald-600 text-sm"></i> MATERI TELAH DISELESAIKAN & TERVERIFIKASI
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-8 bg-gray-50/50 rounded-xl border-2 border-dashed border-gray-100 text-center">
                    <i class="fas fa-file-invoice text-2xl text-gray-200 mb-2"></i>
                    <p class="text-xs text-gray-400 font-medium italic">Belum ada materi di modul ini.</p>
                </div>
                @endforelse
            </div>

            {{-- Games List --}}
            @if($module->games->count() > 0)
            <div class="px-4 pb-4 space-y-2 mt-4">
                <h4 class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-2 px-1 flex items-center gap-1.5"><i class="fas fa-gamepad"></i> Mini Games ({{ $module->games->count() }})</h4>
                @foreach($module->games as $game)
                @php $attempt = $gameAttemptMap[$game->id] ?? null; @endphp
                <div class="bg-indigo-50/50 rounded-xl border border-indigo-100 px-5 py-4 flex items-center justify-between group">
                    <div class="flex items-center gap-4">
                        <span class="w-11 h-11 shrink-0 mr-2 rounded-xl flex items-center justify-center text-white shadow-sm {{ $attempt ? 'bg-emerald-500' : 'bg-indigo-600' }}">
                            <i class="fas {{ $attempt ? 'fa-check text-xl' : 'fa-gamepad text-xl' }}"></i>
                        </span>
                        <div>
                            <p class="font-bold text-indigo-900 text-base flex items-center gap-2">
                                {{ $game->title }}
                                <span class="bg-white text-indigo-700 text-[9px] font-bold px-1.5 py-0.5 rounded-md uppercase border border-indigo-200 shadow-sm">{{ str_replace('_', ' ', $game->game_type) }}</span>
                            </p>
                            <p class="text-[10px] text-indigo-400 font-bold uppercase mt-1"><i class="fas fa-star text-yellow-400"></i> REWARD: {{ $game->reward_points }} EXP</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        @if($attempt)
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-xs font-bold border border-emerald-200">
                                <i class="fas fa-check-circle"></i> Selesai (+{{ $attempt->score }} EXP)
                            </span>
                        @else
                            <button @click="$dispatch('open-game-player', { id: {{ $game->id }}, type: '{{ $game->game_type }}', title: '{{ addslashes($game->title) }}', data: {{ json_encode($game->game_data) }}, reward: {{ $game->reward_points }}, time_limit: {{ $game->time_limit ?: 'null' }}, lives_count: {{ $game->lives_count ?: 'null' }} })" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-wide hover:bg-indigo-700 transition shadow-sm hover:shadow">
                                <i class="fas fa-play"></i> Mainkan
                            </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border p-12 text-center">
            <div class="w-20 h-20 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-book-reader text-3xl text-blue-300"></i></div>
            <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Modul</h3>
            <p class="text-gray-400 text-sm">Guru belum mengunggah materi pelajaran.</p>
        </div>
        @endforelse
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB: ASSIGNMENTS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div x-show="tab === 'assignments'" class="space-y-4 tab-content">
        @forelse($course->assignments as $assignment)
        @php
            $sub = $submissionMap[$assignment->id] ?? null;
            $myGroup = $studentGroupMap[$assignment->id] ?? null;
            $isGroupWork = $assignment->isGroupAssignment();
            $isLeader = $myGroup && $myGroup->isLeader($student->id);
            $hasModule = (bool)$assignment->module;
            $qModColor = $hasModule ? ($assignment->module->color ?? 'purple') : 'purple';
            $qColorClasses = \App\Models\LmsCourse::getColorClasses($qModColor);
            $hasModule = false; // Force light theme for nested elements
        @endphp
        <div class="rounded-2xl shadow-sm border p-5 hover:shadow-md transition-all bg-white border-l-4 {{ $isGroupWork ? 'border-purple-600' : str_replace('200', '500', $qColorClasses['border'] ?? 'border-blue-500') }} text-gray-800 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 opacity-[0.03] rounded-bl-full {{ $isGroupWork ? 'bg-purple-600' : ($qColorClasses['bg'] ?? 'bg-blue-600') }}"></div>
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-start gap-4 flex-1 min-w-0">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0 border {{ $isGroupWork ? 'bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600 border-purple-200 shadow-md' : 'bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 border-emerald-100 shadow-md' }}">
                        <i class="fas {{ $isGroupWork ? 'fa-users' : 'fa-tasks' }} text-2xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <h4 class="font-extrabold text-xl leading-tight {{ $hasModule ? 'text-white' : 'text-gray-900' }}">{{ $assignment->title }}</h4>
                            @if($isGroupWork)
                            <span class="bg-purple-100 text-purple-800 text-[10px] font-extrabold px-3 py-1 rounded-full border border-purple-300 uppercase tracking-widest shadow-sm flex items-center gap-1">
                                <i class="fas fa-users text-xs"></i> TUGAS KELOMPOK
                            </span>
                            @endif
                            @if($assignment->allow_resubmit)
                            <span class="bg-blue-50 text-blue-700 text-[10px] font-extrabold px-3 py-1 rounded-full border border-blue-200 uppercase tracking-widest shadow-sm">REVISI OK</span>
                            @endif
                        </div>
                        {{-- Status Pipeline --}}
                        @php
                            $pipelineStep = 0;
                            if($sub && $sub->status !== 'draft') $pipelineStep = 1;
                            if($sub && $sub->status === 'graded') $pipelineStep = 2;
                        @endphp
                        <div class="flex items-center gap-0 my-3">
                            {{-- Step 1: Belum Dikumpulkan --}}
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-extrabold shadow-sm {{ $pipelineStep >= 0 ? ($pipelineStep === 0 ? 'bg-amber-500 text-white ring-4 ring-amber-100' : 'bg-emerald-500 text-white') : 'bg-gray-200 text-gray-400' }}">
                                    @if($pipelineStep > 0)<i class="fas fa-check text-[10px]"></i>@else<span>1</span>@endif
                                </div>
                                <span class="text-[11px] font-extrabold uppercase tracking-wide ml-2 {{ $pipelineStep === 0 ? ($hasModule ? 'text-amber-300' : 'text-amber-600') : ($hasModule ? 'text-white/60' : 'text-gray-400') }} hidden sm:inline">Belum</span>
                            </div>
                            <div class="w-8 sm:w-12 h-1 {{ $pipelineStep >= 1 ? ($hasModule ? 'bg-white/40' : 'bg-emerald-400') : ($hasModule ? 'bg-white/10' : 'bg-gray-200') }} mx-2 rounded-full"></div>
                            {{-- Step 2: Dikumpulkan --}}
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-extrabold shadow-sm {{ $pipelineStep >= 1 ? ($pipelineStep === 1 ? 'bg-blue-500 text-white ring-4 ring-blue-100' : 'bg-emerald-500 text-white') : 'bg-gray-200 text-gray-400' }}">
                                    @if($pipelineStep > 1)<i class="fas fa-check text-[10px]"></i>@else<span>2</span>@endif
                                </div>
                                <span class="text-[11px] font-extrabold uppercase tracking-wide ml-2 {{ $pipelineStep === 1 ? ($hasModule ? 'text-blue-300' : 'text-blue-600') : ($hasModule ? 'text-white/60' : 'text-gray-400') }} hidden sm:inline">Dikumpulkan</span>
                            </div>
                            <div class="w-8 sm:w-12 h-1 {{ $pipelineStep >= 2 ? ($hasModule ? 'bg-white/40' : 'bg-emerald-400') : ($hasModule ? 'bg-white/10' : 'bg-gray-200') }} mx-2 rounded-full"></div>
                            {{-- Step 3: Dinilai --}}
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-extrabold shadow-sm {{ $pipelineStep >= 2 ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : 'bg-gray-200 text-gray-400' }}">
                                    @if($pipelineStep >= 2)<i class="fas fa-check text-[10px]"></i>@else<span>3</span>@endif
                                </div>
                                <span class="text-[11px] font-extrabold uppercase tracking-wide ml-2 {{ $pipelineStep === 2 ? ($hasModule ? 'text-emerald-300' : 'text-emerald-600') : ($hasModule ? 'text-white/60' : 'text-gray-400') }} hidden sm:inline">Dinilai</span>
                            </div>
                        </div>

                        {{-- Group Assignment Info Card --}}
                        @if($isGroupWork)
                            @if($myGroup)
                            <div class="p-4 rounded-2xl border-2 border-purple-200 bg-purple-50/80 mb-4 space-y-2">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2 font-black text-purple-900 text-sm">
                                        <i class="fas fa-users text-purple-600"></i>
                                        <span>{{ $myGroup->name }}</span>
                                        @if($isLeader)
                                        <span class="bg-amber-400 text-black text-[10px] px-2.5 py-0.5 rounded-full border border-black uppercase tracking-wider font-extrabold shadow-xs">👑 Anda Ketua Kelompok</span>
                                        @else
                                        <span class="bg-purple-200 text-purple-800 text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider font-extrabold">Anggota</span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-bold text-gray-600">
                                        Ketua Kelompok: <strong class="text-gray-900">{{ $myGroup->leader?->user?->name ?? $myGroup->leader?->full_name ?? 'Belum Ditunjuk' }}</strong>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-700 font-medium">
                                    <span class="font-bold text-purple-900">Daftar Anggota Kelompok:</span>
                                    <span class="text-gray-600">{{ $myGroup->members->pluck('user.name')->filter()->implode(', ') ?: ($myGroup->members->pluck('full_name')->filter()->implode(', ') ?: '—') }}</span>
                                </div>
                            </div>
                            @else
                            <div class="p-4 rounded-2xl border-2 border-amber-300 bg-amber-50 text-amber-900 mb-4 text-xs font-bold flex items-center gap-2">
                                <i class="fas fa-info-circle text-amber-600 text-lg flex-shrink-0"></i>
                                <span>Tugas ini diset sebagai Tugas Kelompok, namun Anda belum dimasukkan ke dalam kelompok oleh Guru. Anda tetap dapat mengumpulkan tugas mandiri melalui form di bawah.</span>
                            </div>
                            @endif
                        @endif

                        @if($assignment->description)
                            <div class="text-sm font-bold mt-2 mb-4 p-3 rounded-xl border-l-4 shadow-sm {{ $hasModule ? 'bg-white/10 border-white/20 text-white/90' : 'bg-gray-50 border-emerald-300 text-gray-800' }}">{!! balanceHtmlTags($assignment->description) !!}</div>
                        @endif

                        @if($assignment->file_path)
                            @php
                                $ext = strtolower(pathinfo($assignment->file_path, PATHINFO_EXTENSION));
                                $isPdf = $ext === 'pdf';
                            @endphp
                            
                            @if($isPdf)
                                <div class="w-full rounded-xl overflow-hidden shadow-md border border-gray-200 bg-white mb-4 mt-2" style="height: 500px;">
                                    <iframe src="{{ Storage::disk('public')->url($assignment->file_path) }}" class="w-full h-full" frameborder="0"></iframe>
                                </div>
                            @endif

                            <div class="p-4 rounded-xl border {{ $hasModule ? 'border-white/20 bg-white/10' : 'border-blue-100 bg-blue-50/30' }} flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4 mt-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl {{ $isPdf ? 'bg-red-500' : 'bg-blue-500' }} text-white flex items-center justify-center shadow-md flex-shrink-0">
                                        <i class="fas {{ $isPdf ? 'fa-file-pdf' : 'fa-file-alt' }} text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm {{ $hasModule ? 'text-white' : 'text-gray-800' }}">File Lampiran Tugas</p>
                                        <p class="text-[10px] font-medium {{ $hasModule ? 'text-white/60' : 'text-gray-500' }}">Format: {{ strtoupper($ext) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <a href="{{ Storage::disk('public')->url($assignment->file_path) }}" target="_blank" class="flex-1 sm:flex-none justify-center px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                        <i class="fas fa-external-link-alt"></i> Buka
                                    </a>
                                    <a href="{{ Storage::disk('public')->url($assignment->file_path) }}" download class="flex-1 sm:flex-none justify-center px-4 py-2 rounded-xl {{ $isPdf ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                        <i class="fas fa-download"></i> Unduh
                                    </a>
                                </div>
                            </div>
                        @endif
                        <div class="flex flex-wrap gap-3 text-[11px] font-extrabold uppercase tracking-widest">
                            @if($assignment->deadline)
                            <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl border-2 shadow-sm {{ $hasModule ? ($assignment->isOverdue() ? 'bg-rose-500/20 text-white border-rose-500/30' : 'bg-white/15 text-white border-white/10') : ($assignment->isOverdue() ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-white text-gray-600 border-gray-200') }}">
                                <i class="fas fa-clock text-sm"></i>
                                DEADLINE: {{ $assignment->deadline->format('d M Y H:i') }}
                                @if($assignment->isOverdue()) <span class="animate-pulse text-rose-500 ml-1">(TELAT)</span> @endif
                            </span>
                            @endif
                            <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl border-2 shadow-sm {{ $hasModule ? 'bg-white/15 text-white border-white/10' : 'bg-white text-gray-600 border-gray-200' }}"><i class="fas fa-star text-sm {{ $hasModule ? 'text-yellow-200' : 'text-amber-500' }}"></i> SKOR MAKS: {{ $assignment->max_score }}</span>
                            <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl border-2 shadow-sm {{ $hasModule ? 'bg-white/15 text-white border-white/10' : 'bg-white text-gray-600 border-gray-200' }}"><i class="fas fa-hashtag text-sm text-blue-500"></i> {{ $assignment->getAssignmentTypeLabel() }}</span>
                        </div>
                    </div>
                </div>
                @if($sub && ($sub->status === 'graded' || $sub->score !== null))
                <div class="text-center ml-4 flex-shrink-0 bg-emerald-50 rounded-2xl p-4 border-2 border-emerald-100 shadow-sm">
                    <div class="text-3xl font-extrabold leading-none text-emerald-600">{{ $sub->score }}</div>
                    <div class="text-[11px] font-extrabold mt-1.5 uppercase tracking-widest text-emerald-800/80">{{ $isGroupWork ? 'NILAI KELOMPOK' : 'NILAI ANDA' }}</div>
                </div>
                @elseif($sub && $sub->status !== 'draft')
                <span class="px-4 py-2 rounded-xl text-xs font-extrabold border-2 uppercase tracking-widest ml-4 flex-shrink-0 shadow-sm bg-blue-50 text-blue-700 border-blue-200">{{ $sub->getStatusLabel() }}</span>
                @endif
            </div>

            @if($sub && $sub->feedback)
            <div class="border rounded-xl p-3 mb-3 text-sm flex items-start gap-2 {{ $hasModule ? 'bg-white/15 border-white/20 text-white' : 'bg-emerald-50 border-emerald-200 text-gray-800' }}">
                <i class="fas fa-comment-dots mt-0.5 {{ $hasModule ? 'text-yellow-200' : 'text-emerald-500' }}"></i>
                <div><strong class="{{ $hasModule ? 'text-white font-bold' : 'text-emerald-700' }}">Feedback:</strong> {{ $sub->feedback }}</div>
            </div>
            @endif

            @if($sub && $sub->teacher_notes)
            <div class="border rounded-xl p-3 mb-3 text-sm flex items-start gap-2 {{ $hasModule ? 'bg-white/15 border-white/20 text-white' : 'bg-blue-50 border-blue-200 text-gray-800' }}">
                <i class="fas fa-sticky-note mt-0.5 {{ $hasModule ? 'text-yellow-200' : 'text-blue-500' }}"></i>
                <div><strong class="{{ $hasModule ? 'text-white fon            @if($sub && ($sub->submission_text || count($sub->file_list) > 0))
            <div class="border border-gray-100 bg-gray-50 rounded-xl p-4 mb-3">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 flex items-center justify-between">
                    <span><i class="fas fa-paperclip"></i> {{ $isGroupWork ? 'Jawaban / Berkas Tugas Kelompok' : 'Jawaban / Tugas Anda' }}</span>
                    @if($isGroupWork && $sub->student)
                    <span class="text-[10px] font-semibold text-purple-700">(Dikumpulkan oleh: {{ $sub->student->user->name ?? $sub->student->full_name }})</span>
                    @endif
                </p>
                @if($sub->submission_text)
                <div class="bg-white border border-gray-200 rounded-lg p-3 text-sm text-gray-700 whitespace-pre-wrap mb-3">{!! $sub->submission_text !!}</div>
                @endif
                @if(count($sub->file_list) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($sub->file_list as $fIndex => $fPath)
                        @php $isImg = \App\Models\LmsSubmission::isImagePath($fPath); @endphp
                        <div class="bg-white border border-gray-200 rounded-xl p-2 flex flex-col items-center justify-between text-center shadow-2xs group relative">
                            @if($isImg)
                                <div class="w-full h-24 rounded-lg overflow-hidden bg-slate-900 mb-1.5 flex items-center justify-center">
                                    <img src="{{ Storage::disk('public')->url($fPath) }}" class="max-h-full max-w-full object-contain cursor-pointer hover:scale-105 transition-transform" onclick="window.open('{{ Storage::disk('public')->url($fPath) }}', '_blank')" alt="Foto Berkas {{ $fIndex + 1 }}">
                                </div>
                                <span class="text-[11px] font-bold text-gray-700 truncate w-full">Foto {{ $fIndex + 1 }}</span>
                            @else
                                <div class="w-full h-24 rounded-lg bg-rose-50 flex items-center justify-center mb-1.5 border border-rose-100">
                                    <i class="fas fa-file-pdf text-3xl text-rose-600"></i>
                                </div>
                                <span class="text-[11px] font-bold text-rose-700 truncate w-full">Dokumen PDF</span>
                            @endif
                            <a href="{{ Storage::disk('public')->url($fPath) }}" target="_blank" class="mt-1 text-[10px] font-extrabold text-blue-600 hover:underline flex items-center gap-1">
                                <i class="fas fa-external-link-alt"></i> Buka / Lihat
                            </a>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            @php
                $canSubmit = false;
                $canRevise = false;
                if ($isGroupWork) {
                    if ($myGroup) {
                        $canSubmit = !$sub || $sub->status === 'draft';
                        $canRevise = $sub && ($sub->status === 'revision_requested' || ($sub->status === 'graded' && $assignment->allow_resubmit && $assignment->canResubmit($sub->student_id ?? null)));
                    } else {
                        // Fallback jika belum dimasukkan ke kelompok: tetap buka form agar siswa tidak terhambat
                        $canSubmit = !$sub || $sub->status === 'draft';
                        $canRevise = $sub && ($sub->status === 'revision_requested' || ($sub->status === 'graded' && $assignment->allow_resubmit && $assignment->canResubmit($sub->student_id ?? null)));
                    }
                } else {
                    $canSubmit = !$sub || $sub->status === 'draft';
                    $canRevise = $sub && ($sub->status === 'revision_requested' || ($sub->status === 'graded' && $assignment->allow_resubmit && $assignment->canResubmit($sub->student_id ?? null)));
                }
            @endphp

            {{-- Group Member Notice (If group work) --}}
            @if($isGroupWork && $myGroup)
                @if($sub && $sub->status !== 'draft')
                <div class="p-4 rounded-2xl border-2 border-emerald-200 bg-emerald-50/70 mb-3 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-check-circle text-sm"></i>
                    </div>
                    <div class="text-xs text-emerald-900">
                        <p class="font-extrabold">Tugas Kelompok Telah Terkumpul</p>
                        <p class="font-medium text-emerald-800 mt-0.5">
                            Tugas kelompok ini <strong class="text-emerald-700">sudah dikumpulkan</strong> oleh <strong class="text-gray-900">{{ $sub->student?->user?->name ?? $sub->student?->full_name ?? 'Anggota Kelompok' }}</strong>. Nilai dan feedback dari guru akan otomatis masuk ke seluruh anggota kelompok.
                        </p>
                    </div>
                </div>
                @else
                <div class="p-4 rounded-2xl border-2 border-purple-200 bg-purple-50/60 mb-3 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-purple-200 text-purple-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-info text-xs"></i>
                    </div>
                    <div class="text-xs text-purple-900">
                        <p class="font-extrabold">Informasi Pengumpulkan Tugas Kelompok ({{ $myGroup->name }})</p>
                        <p class="font-medium text-purple-800 mt-0.5">
                            Pengumpulkan tugas dapat dilakukan oleh Ketua Kelompok (<strong class="text-gray-900">{{ $myGroup->leader?->user?->name ?? $myGroup->leader?->full_name ?? 'Ketua' }}</strong>) atau anggota kelompok manapun yang mewakili. Cukup 1 siswa yang mengunggah tugas untuk seluruh kelompok.
                        </p>
                    </div>
                </div>
                @endif
            @elseif($isGroupWork && !$myGroup)
                <div class="p-4 rounded-2xl border-2 border-amber-300 bg-amber-50 mb-3 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-users-slash text-xs"></i>
                    </div>
                    <div class="text-xs text-amber-900">
                        <p class="font-extrabold">Pemberitahuan Tugas Kelompok</p>
                        <p class="font-medium text-amber-800 mt-0.5">
                            Guru belum membagi Anda ke dalam kelompok tugas. Anda tetap dapat mengumpulkan berkas tugas secara mandiri melalui form di bawah ini.
                        </p>
                    </div>
                </div>
            @endif

            @if($canSubmit || $canRevise)
            <details class="group/submit" open>
                <summary class="cursor-pointer text-base font-extrabold flex items-center gap-2 transition-colors {{ $hasModule ? 'text-white hover:text-white/80' : 'text-blue-600 hover:text-blue-700' }}">
                    <i class="fas fa-upload text-sm"></i> {{ $canRevise ? 'Kirim Revisi Tugas' : ($isGroupWork ? ($myGroup ? 'Kumpulkan Tugas Kelompok (Mewakili ' . $myGroup->name . ')' : 'Kumpulkan Tugas Mandiri') : 'Kumpulkan Tugas Sekarang') }}
                    @if($canRevise && $assignment->max_resubmissions)
                    <span class="text-xs font-normal {{ $hasModule ? 'text-white/70' : 'text-gray-400' }}">(Percobaan {{ ($sub->attempt_number ?? 1) + 1 }} dari {{ $assignment->max_resubmissions + 1 }})</span>
                    @endif
                    <i class="fas fa-chevron-down text-xs ml-auto group-open/submit:rotate-180 transition-transform {{ $hasModule ? 'text-white/60' : 'text-gray-300' }}"></i>
                </summary>
                <form action="{{ route('siswa.lms.assignments.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return handleLmsAssignmentSubmit(this)" class="mt-4 p-5 rounded-2xl space-y-4 border-2 shadow-sm {{ $hasModule ? 'bg-white/15 border-white/10 text-white' : 'bg-blue-50 border-blue-100 text-gray-800' }}">
                    @csrf
                    @if($isGroupWork && $myGroup)
                    <div class="p-3.5 rounded-xl text-xs font-bold bg-purple-100/80 border border-purple-300 text-purple-900 flex items-center gap-2">
                        <i class="fas fa-users text-purple-700 text-base flex-shrink-0"></i>
                        <span>Anda mengunggah tugas mewakili <strong>{{ $myGroup->name }}</strong> ({{ $myGroup->members->count() }} Anggota). Berkas jawaban dan nilai akan otomatis terhubung ke seluruh anggota.</span>
                    </div>
                    @elseif($isGroupWork && !$myGroup)
                    <div class="p-3.5 rounded-xl text-xs font-bold bg-amber-100/80 border border-amber-300 text-amber-900 flex items-center gap-2">
                        <i class="fas fa-info-circle text-amber-700 text-base flex-shrink-0"></i>
                        <span>Mode Tugas Kelompok: Anda belum dimasukkan ke kelompok oleh Guru. Tugas ini akan dikumpulkan dan dinilai atas nama Anda.</span>
                    </div>
                    @endif
                    @php
                        $aType = $assignment->assignment_type ?? 'file_text';
                    @endphp
                    <div class="p-3.5 rounded-xl text-xs font-bold flex items-center gap-2 border shadow-sm {{ $hasModule ? 'bg-white/10 border-white/20 text-white' : 'bg-white border-blue-200 text-blue-900' }}">
                        <i class="fas fa-info-circle text-base text-amber-400"></i>
                        <span>Ketentuan Pengumpulan: 
                            @if($aType === 'file')
                                <strong class="underline decoration-indigo-400 font-black">Wajib Unggah PDF / Foto Lembar Jawaban</strong>
                            @elseif($aType === 'text')
                                <strong class="underline decoration-rose-400">Wajib Mengisi Teks Jawaban</strong>
                            @elseif($aType === 'link')
                                <strong class="underline decoration-rose-400">Wajib Memasukkan Link URL / Teks</strong>
                            @else
                                <strong class="underline decoration-indigo-400 font-black">Wajib Unggah PDF/Foto DAN Mengisi Teks Jawaban</strong>
                            @endif
                        </span>
                    </div>

                    @if(in_array($aType, ['text', 'file_text']))
                    <div>
                        <label class="block text-xs font-bold mb-1 {{ $hasModule ? 'text-white' : 'text-gray-700' }}">
                            Teks Jawaban @if(in_array($aType, ['text', 'file_text'])) <span class="text-rose-500 font-extrabold">* (Wajib)</span> @endif
                        </label>
                        <textarea name="submission_text" rows="4" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-4 focus:ring-blue-500/20 outline-none text-gray-800 math-support" placeholder="Ketik jawaban Anda di sini..." {{ in_array($aType, ['text', 'file_text']) ? 'required' : '' }}></textarea>
                    </div>
                    @endif

                    @if(in_array($aType, ['file', 'file_text']))
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold {{ $hasModule ? 'text-white' : 'text-gray-700' }}">
                                Berkas Tugas (PDF atau Foto) @if(in_array($aType, ['file', 'file_text']) && !($sub && count($sub->file_list))) <span class="text-rose-500 font-extrabold">* (Bisa lebih dari 1 file)</span> @endif
                            </label>
                            <span class="inline-flex items-center gap-1 text-[11px] font-black px-2.5 py-0.5 rounded-lg bg-indigo-100 text-indigo-700 border border-indigo-200">
                                <i class="fas fa-camera"></i> PDF / Foto (JPG, PNG)
                            </span>
                        </div>

                        <div class="bg-white border-2 border-dashed border-indigo-200 rounded-2xl p-4 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                {{-- Button 1: Live Camera Modal Trigger --}}
                                <button type="button" onclick="openLmsCameraModal()" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2">
                                    <i class="fas fa-camera text-base"></i> Ambil Foto Langsung (Kamera)
                                </button>
                                
                                {{-- Button 2: Multi File Picker --}}
                                <label class="w-full py-3 px-4 rounded-xl bg-indigo-50 border-2 border-indigo-200 text-indigo-700 hover:bg-indigo-100 font-extrabold text-xs shadow-sm transition flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fas fa-folder-open text-base text-indigo-600"></i> Pilih Berkas (Gambar / PDF)
                                    <input type="file" name="files[]" id="lmsFileInput" multiple accept=".pdf,image/jpeg,image/png,image/jpg,image/webp,image/heic" onchange="handleLmsFileSelection(this)" class="hidden">
                                </label>
                            </div>

                            <p class="text-[11px] text-gray-500 font-medium leading-relaxed">
                                <i class="fas fa-info-circle text-indigo-500 mr-1"></i>
                                Anda bisa mengunggah dokumen PDF atau mengambil <strong>beberapa foto lembar jawaban</strong> sekaligus.
                            </p>

                            {{-- Queue Container for selected files / camera snapshots --}}
                            <div id="lmsSubmissionQueue" class="hidden space-y-2 pt-2 border-t border-gray-100">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-700">
                                    <span>Daftar Berkas Terpilih (<span id="lmsQueueCount">0</span>):</span>
                                    <button type="button" onclick="clearAllLmsFiles()" class="text-[11px] text-rose-600 hover:underline">Hapus Semua</button>
                                </div>
                                <div id="lmsQueueList" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5"></div>
                            </div>
                            <div id="lmsCameraPhotosHidden"></div>
                        </div>
                    </div>
                    @endif

                    @if($aType === 'link')
                    <div>
                        <label class="block text-xs font-bold mb-1 {{ $hasModule ? 'text-white' : 'text-gray-700' }}">
                            Link URL Jawaban <span class="text-rose-500 font-extrabold">* (Wajib)</span>
                        </label>
                        <input type="url" name="submission_text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-4 focus:ring-blue-500/20 outline-none text-gray-800" placeholder="https://..." required>
                    </div>
                    @endif

                    <button type="submit" class="w-full py-3.5 rounded-xl text-base font-extrabold uppercase tracking-widest transition-all shadow-md {{ $hasModule ? 'bg-white text-' . $qModColor . '-700 hover:bg-gray-50' : 'bg-blue-600 text-white hover:bg-blue-700 hover:shadow-lg' }}">
                        <i class="fas fa-paper-plane mr-2 text-lg"></i> Kirim Jawaban
                    </button>
                </form>
            </details>
            @endif               </label>
                        <input type="url" name="submission_text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-4 focus:ring-blue-500/20 outline-none text-gray-800" placeholder="https://..." required>
                    </div>
                    @endif

                    <button type="submit" class="w-full py-3.5 rounded-xl text-base font-extrabold uppercase tracking-widest transition-all shadow-md {{ $hasModule ? 'bg-white text-' . $qModColor . '-700 hover:bg-gray-50' : 'bg-blue-600 text-white hover:bg-blue-700 hover:shadow-lg' }}">
                        <i class="fas fa-paper-plane mr-2 text-lg"></i> Kirim Jawaban
                    </button>
                </form>
            </details>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border p-12 text-center">
            <div class="w-20 h-20 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-tasks text-3xl text-emerald-300"></i></div>
            <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Tugas</h3>
            <p class="text-gray-400 text-sm">Belum ada tugas dari guru untuk course ini.</p>
        </div>
        @endforelse
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB: QUIZZES --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div x-show="tab === 'quizzes'" class="space-y-4 tab-content">
        @forelse($course->quizzes as $quiz)
        @php
            $attempts = $attemptMap[$quiz->id] ?? collect();
            $lastAttempt = $attempts->sortByDesc('started_at')->first();
            $bestScore = $attempts->max('score');
            $remaining = $quiz->getRemainingAttempts(auth()->user()->student->id ?? 0);
            
            $hasModule = (bool)$quiz->module;
            $qModColor = $hasModule ? ($quiz->module->color ?? 'purple') : 'purple';
            $qColorClasses = \App\Models\LmsCourse::getColorClasses($qModColor);
            $hasModule = false; // Force light theme for nested elements
        @endphp
        <div class="rounded-2xl shadow-sm border p-5 transition-all bg-white border-l-4 {{ str_replace('200', '500', $qColorClasses['border'] ?? 'border-blue-500') }} text-gray-800 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 opacity-[0.03] rounded-bl-full {{ $qColorClasses['bg'] ?? 'bg-blue-600' }}"></div>
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4 flex-1 min-w-0">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0 border {{ $hasModule ? 'bg-white/20 text-white border-white/20 shadow-sm' : 'bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600 border-purple-100 shadow-md' }}">
                        <i class="fas fa-question-circle text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <h4 class="font-extrabold text-xl leading-tight {{ $hasModule ? 'text-white' : 'text-gray-900' }}">{{ $quiz->title }}</h4>
                            @if($quiz->module)
                                <span class="bg-white/20 text-white text-[10px] font-extrabold px-3 py-1 rounded-full border border-white/10 uppercase tracking-widest shadow-sm">
                                    {{ $quiz->module->getCode() }} · {{ $quiz->module->title }}
                                </span>
                            @else
                                <span class="bg-gray-50 text-gray-500 text-[10px] font-extrabold px-3 py-1 rounded-full border border-gray-200 uppercase tracking-widest shadow-sm">Global</span>
                            @endif
                        </div>
                        @if($quiz->description && $quiz->description !== $quiz->title)
                            <p class="text-base mt-2 mb-4 line-clamp-2 {{ $hasModule ? 'text-white/80' : 'text-gray-600' }}">{{ $quiz->description }}</p>
                        @endif
                        <div class="flex flex-wrap gap-2 text-[11px] font-extrabold uppercase tracking-widest">
                            @if($quiz->time_limit)
                                <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl border-2 shadow-sm {{ $hasModule ? 'bg-white/15 text-white border-white/10' : 'bg-gray-50 text-gray-500 border-gray-200' }}">
                                    <i class="fas fa-stopwatch text-sm {{ $hasModule ? 'text-white/90' : 'text-orange-500' }}"></i> {{ $quiz->time_limit }} MENIT
                                </span>
                            @endif
                            <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl border-2 shadow-sm {{ $hasModule ? 'bg-white/15 text-white border-white/10' : 'bg-gray-50 text-gray-500 border-gray-200' }}">
                                <i class="fas fa-check-double text-sm {{ $hasModule ? 'text-white/90' : 'text-emerald-500' }}"></i> PASSING: {{ $quiz->passing_score }}%
                            </span>
                            <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl border-2 shadow-sm {{ $hasModule ? 'bg-white/15 text-white border-white/10' : 'bg-gray-50 text-gray-500 border-gray-200' }}">
                                <i class="fas fa-redo text-sm {{ $hasModule ? 'text-white/90' : 'text-blue-500' }}"></i> PERCOBAAN: {{ $quiz->max_attempts ?? 1 }}X
                            </span>
                            @if($attempts->count())
                                <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl border-2 shadow-sm {{ $hasModule ? 'bg-white/25 text-white border-white/20' : 'bg-blue-50 text-blue-600 border-blue-200' }}">
                                    <i class="fas fa-history text-sm"></i> {{ $attempts->count() }}X DIKERJAKAN
                                </span>
                            @endif
                            @if($bestScore !== null)
                                <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl border-2 shadow-sm {{ $hasModule ? 'bg-white/25 text-white border-white/20' : 'bg-emerald-50 text-emerald-600 border-emerald-200' }}">
                                    <i class="fas fa-trophy text-sm {{ $hasModule ? 'text-yellow-200' : 'text-amber-500' }}"></i> TERBAIK: {{ number_format($bestScore, 1) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right flex flex-col items-end gap-3 ml-4 flex-shrink-0">
                    @if($lastAttempt && $lastAttempt->finished_at)
                    <div class="flex flex-col items-end px-5 py-4 rounded-2xl border-2 shadow-sm {{ $hasModule ? 'bg-white/20 text-white border-white/10' : ($lastAttempt->is_passed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200') }}">
                        <div class="text-3xl font-extrabold leading-none">{{ number_format($lastAttempt->score, 1) }}</div>
                        <div class="text-[11px] font-extrabold mt-2 uppercase tracking-widest">
                            @if($lastAttempt->is_passed)
                                <i class="fas fa-trophy {{ $hasModule ? 'text-yellow-250' : 'text-amber-500' }} mr-1 text-sm"></i> LULUS ✓
                            @else
                                <i class="fas fa-times-circle text-rose-400 mr-1"></i> BELUM LULUS
                            @endif
                        </div>
                    </div>
                    
                    @if($quiz->show_result && $lastAttempt)
                    <a href="{{ route('siswa.lms.quizzes.result', $lastAttempt->id) }}" class="text-xs font-extrabold uppercase tracking-widest flex items-center gap-1.5 mt-1 {{ $hasModule ? 'text-white hover:text-white/80' : 'text-blue-600 hover:text-blue-800' }}">
                        LIHAT HASIL <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                    @endif
                    @endif

                    @if($quiz->isAvailable() && ($remaining === null || $remaining > 0))
                    <a href="{{ route('siswa.lms.quizzes.start', $quiz->id) }}"
                       class="font-extrabold px-6 py-3.5 mt-2 rounded-xl text-sm transition-all shadow-md hover:shadow-lg whitespace-nowrap uppercase tracking-widest flex items-center justify-center gap-2 w-full {{ $hasModule ? 'bg-white text-' . $qModColor . '-700 hover:bg-gray-50' : 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white' }}">
                        <i class="fas fa-play"></i>
                        {{ $lastAttempt ? 'COBA LAGI' : 'MULAI KUIS' }}
                        @if($remaining !== null)<span class="opacity-70 text-[10px] ml-1">({{ $remaining }}X)</span>@endif
                    </a>
                    @elseif(!$quiz->isAvailable())
                    <div class="px-3 py-1.5 bg-gray-50 rounded-lg border border-gray-100 text-right">
                        <span class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">BELUM TERSEDIA</span>
                        @if($quiz->start_time && now()->isBefore($quiz->start_time))
                        <p class="text-[9px] text-gray-400 mt-0.5">{{ $quiz->start_time->format('d M H:i') }}</p>
                        @endif
                    </div>
                    @elseif($remaining === 0)
                    <span class="px-3 py-1.5 bg-rose-50 text-rose-500 rounded-lg border border-rose-100 text-[10px] font-bold uppercase tracking-widest">PERCOBAAN HABIS</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border p-12 text-center">
            <div class="w-20 h-20 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-question-circle text-3xl text-purple-300"></i></div>
            <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Quiz</h3>
            <p class="text-gray-400 text-sm">Cek kembali nanti untuk quiz dari guru.</p>
        </div>
        @endforelse
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB: ANNOUNCEMENTS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div x-show="tab === 'announcements'" class="space-y-4 tab-content">
        @forelse($course->announcements ?? collect() as $ann)
        <div class="bg-white rounded-2xl shadow-sm border {{ $ann->is_pinned ? 'border-amber-200 ring-1 ring-amber-100' : 'border-gray-100' }} p-5 hover:shadow-md transition-shadow">
            @if($ann->is_pinned)
            <div class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-lg text-[9px] font-bold uppercase tracking-widest mb-2">
                <i class="fas fa-thumbtack"></i> PINNED
            </div>
            @endif
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center">
                    <i class="fas fa-bullhorn text-amber-500 text-xs"></i>
                </div>
                <h4 class="font-bold text-gray-800">{{ $ann->title }}</h4>
            </div>
            <p class="text-gray-600 text-sm whitespace-pre-line ml-10">{{ $ann->content }}</p>
            <div class="flex gap-3 mt-3 ml-10 text-xs text-gray-400">
                <span><i class="fas fa-user mr-1"></i> {{ $ann->author->name ?? 'Guru' }}</span>
                <span><i class="fas fa-clock mr-1"></i> {{ $ann->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border p-12 text-center">
            <div class="w-20 h-20 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-bullhorn text-3xl text-amber-300"></i></div>
            <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Pengumuman</h3>
            <p class="text-gray-400 text-sm">Belum ada pengumuman dari guru.</p>
        </div>
        @endforelse
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB: DISCUSSIONS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div x-show="tab === 'discussions'" class="tab-content">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center max-w-md mx-auto">
            <div class="w-16 h-16 bg-gradient-to-br from-cyan-50 to-cyan-100 text-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-cyan-100 shadow-inner">
                <i class="fas fa-comments text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Forum Diskusi</h3>
            <p class="text-gray-500 text-sm mb-6">Berdiskusi dengan guru dan teman tentang materi course ini.</p>
            <a href="{{ route('siswa.lms.discussions.index', $course->id) }}" class="inline-flex items-center gap-2 bg-cyan-600 text-white px-6 py-3 rounded-xl hover:bg-cyan-700 transition shadow-md font-bold text-sm uppercase tracking-wider">
                <i class="fas fa-comments"></i> Buka Forum
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- GAME PLAYER MODAL (ALPINEJS) - PREMIUM UI --}}
{{-- ═══════════════════════════════════════════════ --}}
@include('components.lms-game-player')
@endsection

@push('scripts')
<script>
function trackMaterial(materialId, status = 'viewed') {
    fetch(`/siswa/lms/materials/${materialId}/track`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status, time_spent: 0 })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network error');
        return response.json();
    })
    .catch(() => {
        // Silently fail - don't show error for progress tracking
    });
}

// Mini confetti burst effect near a button
function spawnConfetti(event) {
    const colors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'];
    const btn = event?.target || document.body;
    const rect = btn.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top;
    for (let i = 0; i < 12; i++) {
        const el = document.createElement('div');
        el.className = 'confetti-particle';
        el.style.left = (cx + (Math.random() - 0.5) * 60) + 'px';
        el.style.top = (cy - 10) + 'px';
        el.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        el.style.transform = 'rotate(' + (Math.random() * 360) + 'deg)';
        el.style.animationDuration = (0.5 + Math.random() * 0.4) + 's';
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 900);
    }
}

function completeMaterial(materialId) {
    if(!confirm('Tandai materi ini sebagai selesai?')) return;
    
    fetch(`/siswa/lms/materials/${materialId}/track`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: 'completed', time_spent: 0 })
    })
    .then(response => {
        if (response.ok) {
            // Trigger confetti burst near the button
            spawnConfetti(event);
            // Delay reload to let confetti animation play
            setTimeout(() => window.location.reload(), 800);
        }
    });
}

function reactMaterial(materialId, type, event) {
    fetch(`/siswa/lms/materials/${materialId}/react`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ reaction_type: type })
    })
    .then(response => {
        if (response.ok) {
            const btn = event.currentTarget;
            btn.classList.add('ring-4', 'ring-blue-200', 'scale-105');
            setTimeout(() => btn.classList.remove('ring-4', 'ring-blue-200', 'scale-105'), 300);
        }
    });
}

<!-- Live Camera Modal for LMS Assignment -->
<div id="lmsCameraModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl space-y-4 p-5 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="font-extrabold text-gray-800 text-sm flex items-center gap-2">
                <i class="fas fa-camera text-emerald-600 text-base"></i> Ambil Foto Lembar Jawaban
            </h3>
            <button type="button" onclick="closeLmsCameraModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="relative bg-slate-900 rounded-2xl overflow-hidden min-h-[250px] flex items-center justify-center border-2 border-emerald-500/30">
            <video id="lmsCameraVideo" autoplay playsinline class="w-full h-full object-cover max-h-[320px]"></video>
            <canvas id="lmsCameraCanvas" class="hidden"></canvas>
            <img id="lmsCameraPreview" class="w-full h-full object-contain max-h-[320px] hidden" alt="Pratinjau Foto">
        </div>

        <div class="flex items-center justify-center gap-3 pt-1">
            <button type="button" id="lmsSnapBtn" onclick="snapLmsPhoto()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition flex items-center gap-2">
                <i class="fas fa-camera text-sm"></i> 📸 Jepret Foto
            </button>
            <button type="button" id="lmsRetakeBtn" onclick="retakeLmsPhoto()" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs shadow-md transition flex items-center gap-2 hidden">
                <i class="fas fa-redo"></i> 🔄 Foto Ulang
            </button>
            <button type="button" id="lmsUsePhotoBtn" onclick="useLmsCapturedPhoto()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs shadow-md transition flex items-center gap-2 hidden">
                <i class="fas fa-check"></i> ✔️ Gunakan Foto Ini
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let lmsCapturedPhotos = [];
let lmsSelectedFiles = [];
let lmsMediaStream = null;

function openLmsCameraModal() {
    const modal = document.getElementById('lmsCameraModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    
    const video = document.getElementById('lmsCameraVideo');
    const preview = document.getElementById('lmsCameraPreview');
    const snapBtn = document.getElementById('lmsSnapBtn');
    const retakeBtn = document.getElementById('lmsRetakeBtn');
    const useBtn = document.getElementById('lmsUsePhotoBtn');
    
    video.classList.remove('hidden');
    preview.classList.add('hidden');
    snapBtn.classList.remove('hidden');
    retakeBtn.classList.add('hidden');
    useBtn.classList.add('hidden');

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(stream => {
                lmsMediaStream = stream;
                video.srcObject = stream;
            })
            .catch(err => {
                alert('Tidak dapat mengakses kamera: ' + err.message + '\n\nPastikan izin kamera sudah diaktifkan pada browser Anda.');
                closeLmsCameraModal();
            });
    } else {
        alert('Browser Anda belum mendukung akses kamera langsung. Silakan gunakan tombol "Pilih Berkas" untuk mengambil foto dari galeri/kamera hp.');
        closeLmsCameraModal();
    }
}

function closeLmsCameraModal() {
    const modal = document.getElementById('lmsCameraModal');
    if (modal) modal.classList.add('hidden');
    if (lmsMediaStream) {
        lmsMediaStream.getTracks().forEach(track => track.stop());
        lmsMediaStream = null;
    }
}

function snapLmsPhoto() {
    const video = document.getElementById('lmsCameraVideo');
    const canvas = document.getElementById('lmsCameraCanvas');
    const preview = document.getElementById('lmsCameraPreview');
    const snapBtn = document.getElementById('lmsSnapBtn');
    const retakeBtn = document.getElementById('lmsRetakeBtn');
    const useBtn = document.getElementById('lmsUsePhotoBtn');

    if (!video || !canvas) return;

    canvas.width = video.videoWidth || 1280;
    canvas.height = video.videoHeight || 720;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
    preview.src = dataUrl;
    
    video.classList.add('hidden');
    preview.classList.remove('hidden');
    snapBtn.classList.add('hidden');
    retakeBtn.classList.remove('hidden');
    useBtn.classList.remove('hidden');
}

function retakeLmsPhoto() {
    const video = document.getElementById('lmsCameraVideo');
    const preview = document.getElementById('lmsCameraPreview');
    const snapBtn = document.getElementById('lmsSnapBtn');
    const retakeBtn = document.getElementById('lmsRetakeBtn');
    const useBtn = document.getElementById('lmsUsePhotoBtn');

    video.classList.remove('hidden');
    preview.classList.add('hidden');
    snapBtn.classList.remove('hidden');
    retakeBtn.classList.add('hidden');
    useBtn.classList.add('hidden');
}

function useLmsCapturedPhoto() {
    const preview = document.getElementById('lmsCameraPreview');
    if (!preview || !preview.src) return;
    
    lmsCapturedPhotos.push(preview.src);
    renderLmsQueue();
    closeLmsCameraModal();
}

function handleLmsFileSelection(input) {
    if (!input || !input.files) return;
    const allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif'];
    for (let i = 0; i < input.files.length; i++) {
        const f = input.files[i];
        const ext = (f.name.split('.').pop() || '').toLowerCase();
        if (!allowed.includes(ext)) {
            alert('⚠️ Berkas "' + f.name + '" berekstensi .' + ext + ' tidak diizinkan.\nHarap pilih berkas PDF atau Gambar (JPG, PNG, WEBP).');
            continue;
        }
        lmsSelectedFiles.push(f);
    }
    renderLmsQueue();
}

function removeLmsPhoto(index) {
    lmsCapturedPhotos.splice(index, 1);
    renderLmsQueue();
}

function removeLmsFile(index) {
    lmsSelectedFiles.splice(index, 1);
    renderLmsQueue();
}

function clearAllLmsFiles() {
    lmsCapturedPhotos = [];
    lmsSelectedFiles = [];
    const fileInput = document.getElementById('lmsFileInput');
    if (fileInput) fileInput.value = '';
    renderLmsQueue();
}

function renderLmsQueue() {
    const queueContainer = document.getElementById('lmsSubmissionQueue');
    const queueList = document.getElementById('lmsQueueList');
    const queueCount = document.getElementById('lmsQueueCount');
    const hiddenInputs = document.getElementById('lmsCameraPhotosHidden');
    
    if (!queueContainer || !queueList || !queueCount || !hiddenInputs) return;

    const total = lmsCapturedPhotos.length + lmsSelectedFiles.length;
    queueCount.innerText = total;
    hiddenInputs.innerHTML = '';
    queueList.innerHTML = '';

    if (total === 0) {
        queueContainer.classList.add('hidden');
        return;
    }
    queueContainer.classList.remove('hidden');

    // Render Camera Snapshots
    lmsCapturedPhotos.forEach((src, idx) => {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'camera_photos[]';
        hidden.value = src;
        hiddenInputs.appendChild(hidden);

        const card = document.createElement('div');
        card.className = 'relative bg-slate-900 rounded-xl overflow-hidden p-1 border border-emerald-300 flex flex-col items-center justify-between text-center shadow-xs';
        card.innerHTML = `
            <img src="${src}" class="w-full h-20 object-contain rounded-lg">
            <span class="text-[10px] font-bold text-white mt-1 truncate w-full">Foto Kamera ${idx + 1}</span>
            <button type="button" onclick="removeLmsPhoto(${idx})" class="mt-1 text-[10px] font-extrabold text-rose-400 hover:text-rose-200">❌ Hapus</button>
        `;
        queueList.appendChild(card);
    });

    // Render Selected Files
    lmsSelectedFiles.forEach((file, idx) => {
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        const isImg = ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext);
        const card = document.createElement('div');
        card.className = 'relative bg-gray-50 rounded-xl overflow-hidden p-1.5 border border-indigo-200 flex flex-col items-center justify-between text-center shadow-xs';
        
        if (isImg) {
            const url = URL.createObjectURL(file);
            card.innerHTML = `
                <img src="${url}" class="w-full h-20 object-contain rounded-lg bg-slate-900">
                <span class="text-[10px] font-bold text-gray-700 mt-1 truncate w-full">${file.name}</span>
                <button type="button" onclick="removeLmsFile(${idx})" class="mt-1 text-[10px] font-extrabold text-rose-600 hover:text-rose-800">❌ Hapus</button>
            `;
        } else {
            card.innerHTML = `
                <div class="w-full h-20 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center">
                    <i class="fas fa-file-pdf text-3xl text-rose-600"></i>
                </div>
                <span class="text-[10px] font-bold text-rose-700 mt-1 truncate w-full">${file.name}</span>
                <button type="button" onclick="removeLmsFile(${idx})" class="mt-1 text-[10px] font-extrabold text-rose-600 hover:text-rose-800">❌ Hapus</button>
            `;
        }
        queueList.appendChild(card);
    });
}

function handleLmsAssignmentSubmit(form) {
    // Sync selected files to DataTransfer if any
    if (lmsSelectedFiles.length > 0) {
        const dt = new DataTransfer();
        lmsSelectedFiles.forEach(file => dt.items.add(file));
        const fileInput = document.getElementById('lmsFileInput');
        if (fileInput) fileInput.files = dt.files;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengunggah & Mengirim Jawaban... Mohon Tunggu';
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    }
    return true;
}

// Backward compatibility alias
function validateLmsPdfFile(input) {
    return handleLmsFileSelection(input);
}
function validateLmsFileSize(input) {
    return handleLmsFileSelection(input);
}
</script>
@endpush
