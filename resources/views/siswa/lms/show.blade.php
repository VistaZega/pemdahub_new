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
    .prose p { margin-bottom: 0.75rem !important; line-height: 1.75 !important; }
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
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 1. COURSE HERO BANNER (SESUAI DASHBOARD SISWA) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @php
        $design = $course->design ?? null;
    @endphp
    <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-lg relative overflow-hidden" style="border-radius: 1.75rem; border: 2px solid #e0e7ff; margin-bottom: 2rem;">
        {{-- Rainbow Decorative Stripe --}}
        <div class="h-3.5 w-full bg-gradient-to-r from-rose-500 via-amber-500 via-emerald-500 via-cyan-500 to-purple-600" style="height: 0.5rem;"></div>

        <div style="padding: 2rem 2.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 2rem;">
            <div style="display: flex; align-items: center; gap: 2rem; flex: 1; min-width: 280px;">
                <div style="position: relative; flex-shrink: 0;">
                    <div style="width: 5.5rem; height: 5.5rem; border-radius: 1.5rem; overflow: hidden; border: 4px solid #ffffff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); background-color: #eff6ff; display: flex; align-items: center; justify-content: center; font-size: 2.25rem;">
                        @if($scientist)
                            <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                        @elseif(isset($design['icon']))
                            <i class="{{ $design['icon'] }} text-indigo-600 text-3xl"></i>
                        @else
                            <i class="fas fa-graduation-cap text-indigo-600 text-3xl"></i>
                        @endif
                    </div>
                </div>

                <div style="flex: 1; min-width: 0; padding-left: 0.5rem;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; margin-bottom: 0.6rem;">
                        <a href="{{ route('siswa.lms.index') }}" style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #0f172a; color: #ffffff; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fas fa-arrow-left" style="font-size: 0.65rem;"></i>
                            <span>Daftar Kelas</span>
                        </a>
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #e0e7ff; color: #312e81; border: 1px solid #c7d2fe;">
                            {{ $course->getShortCode() }}
                        </span>
                        @if(isset($design['category']))
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f3e8ff; color: #581c87; border: 1px solid #e9d5ff;">
                            {{ $design['category'] }}
                        </span>
                        @endif
                    </div>

                    <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; line-height: 1.35; margin-top: 0.25rem; margin-bottom: 0.4rem;">
                        {{ $course->course_name ?? $course->name }}
                    </h1>

                    <p style="font-size: 0.875rem; color: #475569; font-weight: 600; display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-top: 0.4rem;">
                        <span><i class="fas fa-chalkboard-teacher" style="color: #6366f1; margin-right: 0.4rem;"></i> Pengajar: {{ $course->teacher->user->name ?? '-' }}</span>
                        <span>&bull;</span>
                        <span><i class="fas fa-book" style="color: #6366f1; margin-right: 0.4rem;"></i> {{ $course->subject->subject_name ?? '' }}</span>
                    </p>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; flex-shrink: 0;">
                <div style="padding: 0.75rem 1.25rem; border-radius: 1rem; background-color: #eef2ff; border: 2px solid #c7d2fe; color: #312e81; font-size: 0.8rem; font-weight: 900; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-layer-group" style="color: #4f46e5;"></i>
                    <span>{{ $course->modules->count() }} Modul Ajar</span>
                </div>
                <div style="padding: 0.75rem 1.25rem; border-radius: 1rem; background-color: #ecfdf5; border: 2px solid #a7f3d0; color: #064e3b; font-size: 0.8rem; font-weight: 900; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-chart-line" style="color: #059669;"></i>
                    <span>Progress {{ number_format($courseProgress) }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Pinned Announcements --}}
    @if(isset($course->announcements) && $course->announcements->where('is_pinned', true)->count())
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-200 rounded-3xl p-5 shadow-sm mb-6" style="border-radius: 1.5rem;">
        <h4 class="font-black text-amber-900 text-sm mb-2 flex items-center gap-2">
            <span class="w-7 h-7 bg-amber-200 rounded-xl flex items-center justify-center"><i class="fas fa-bullhorn text-amber-700 text-xs"></i></span>
            PENGUMUMAN PENTING KELAS
        </h4>
        @foreach($course->announcements->where('is_pinned', true)->take(2) as $ann)
        <div class="mb-2 last:mb-0 ml-9">
            <p class="font-extrabold text-slate-900 text-sm">{{ $ann->title }}</p>
            <p class="text-slate-600 text-xs mt-0.5 font-medium">{{ Str::limit($ann->content, 120) }}</p>
            <div class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wider font-bold">{{ $ann->created_at->diffForHumans() }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB NAVIGATION --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl shadow-md border-2 border-indigo-100 p-2 flex flex-wrap gap-2 sticky top-0 z-20 backdrop-blur-md bg-white/95" style="border-radius: 1.5rem; border: 2px solid #e0e7ff; margin-bottom: 2rem;">
        <button @click="tab = 'modules'" :class="tab === 'modules' ? 'bg-indigo-600 text-white shadow-md font-black' : 'bg-slate-50 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 font-bold border border-slate-200'" class="flex-1 min-w-[70px] px-4 py-3 rounded-2xl text-xs transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-book-open text-sm"></i> <span class="hidden sm:inline">Modul</span>
        </button>
        <button @click="tab = 'assignments'" :class="tab === 'assignments' ? 'bg-indigo-600 text-white shadow-md font-black' : 'bg-slate-50 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 font-bold border border-slate-200'" class="flex-1 min-w-[70px] px-4 py-3 rounded-2xl text-xs transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-tasks text-sm"></i> <span class="hidden sm:inline">Tugas</span>
            @if($pendingAssignments > 0)<span class="rounded-full px-2 py-0.5 text-[10px] font-black bg-rose-500 text-white shadow-xs">{{ $pendingAssignments }}</span>@endif
        </button>
        <button @click="tab = 'quizzes'" :class="tab === 'quizzes' ? 'bg-indigo-600 text-white shadow-md font-black' : 'bg-slate-50 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 font-bold border border-slate-200'" class="flex-1 min-w-[70px] px-4 py-3 rounded-2xl text-xs transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-question-circle text-sm"></i> <span class="hidden sm:inline">Quiz</span>
            @if($availableQuizzes > 0)<span class="rounded-full px-2 py-0.5 text-[10px] font-black bg-purple-500 text-white shadow-xs">{{ $availableQuizzes }}</span>@endif
        </button>
        <button @click="tab = 'announcements'" :class="tab === 'announcements' ? 'bg-indigo-600 text-white shadow-md font-black' : 'bg-slate-50 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 font-bold border border-slate-200'" class="flex-1 min-w-[70px] px-4 py-3 rounded-2xl text-xs transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-bullhorn text-sm"></i> <span class="hidden sm:inline">Info</span>
        </button>
        <button @click="tab = 'discussions'" :class="tab === 'discussions' ? 'bg-indigo-600 text-white shadow-md font-black' : 'bg-slate-50 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 font-bold border border-slate-200'" class="flex-1 min-w-[70px] px-4 py-3 rounded-2xl text-xs transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fas fa-comments text-sm"></i> <span class="hidden sm:inline">Diskusi</span>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TAB: MODULES / MATERIALS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div x-show="tab === 'modules'" class="space-y-6 tab-content">
        {{-- Panduan Penyelesaian Modul untuk Siswa --}}
        <div class="bg-gradient-to-br from-indigo-50/80 via-white to-purple-50/60 rounded-3xl p-6 border border-indigo-100/80 text-slate-800 shadow-sm relative overflow-hidden">
            <div class="relative z-10 space-y-3">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-base font-bold shadow-sm shrink-0">
                        <i class="fas fa-info-circle text-white"></i>
                    </span>
                    <h4 class="font-extrabold text-base uppercase tracking-wider text-slate-900">Panduan Penyelesaian Modul Pembelajaran</h4>
                </div>
                <p class="text-xs font-semibold text-slate-600 leading-relaxed">
                    Untuk menyukseskan pembelajaran Anda dan memperoleh progress 100%, ikuti 4 langkah mudah berikut pada setiap modul:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                    <div class="bg-white rounded-2xl p-4 border border-indigo-100/80 shadow-xs flex items-start gap-3 hover:shadow-md transition-shadow">
                        <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-black text-sm flex-shrink-0 shadow-sm">1</span>
                        <div>
                            <p class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">Pelajari Materi</p>
                            <p class="text-[11px] text-slate-600 leading-snug mt-1 font-medium">Simak PDF, Video, atau Link sesuai waktu belajar minimal yang ditentukan.</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-indigo-100/80 shadow-xs flex items-start gap-3 hover:shadow-md transition-shadow">
                        <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 text-white flex items-center justify-center font-black text-sm flex-shrink-0 shadow-sm">2</span>
                        <div>
                            <p class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">Verifikasi Selesai</p>
                            <p class="text-[11px] text-slate-600 leading-snug mt-1 font-medium">Centang kotak pernyataan verifikasi aktif, lalu klik tombol <b>Tandai Selesai</b>.</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-indigo-100/80 shadow-xs flex items-start gap-3 hover:shadow-md transition-shadow">
                        <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white flex items-center justify-center font-black text-sm flex-shrink-0 shadow-sm">3</span>
                        <div>
                            <p class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">Mainkan Game</p>
                            <p class="text-[11px] text-slate-600 leading-snug mt-1 font-medium">Uji daya ingat dan kumpulkan EXP / skor dengan memainkan mini game interaktif.</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-indigo-100/80 shadow-xs flex items-start gap-3 hover:shadow-md transition-shadow">
                        <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-400 to-pink-500 text-white flex items-center justify-center font-black text-sm flex-shrink-0 shadow-sm">4</span>
                        <div>
                            <p class="text-xs font-extrabold text-slate-900 uppercase tracking-wide">Kuis & Tugas</p>
                            <p class="text-[11px] text-slate-600 leading-snug mt-1 font-medium">Selesaikan Kuis Evaluasi dan kirim Tugas pada tab menu di atas tepat waktu.</p>
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
                                @endif
                            @elseif($material->material_type === 'image')
                                <div class="w-full rounded-xl overflow-hidden shadow-md border border-gray-100 bg-gray-900 flex justify-center">
                                    <img src="{{ $material->file_path ? route('siswa.lms.materials.view', $material->id) : ($material->file_url ?? '') }}" class="max-h-[400px] object-contain w-auto h-auto" alt="{{ $material->title }}">
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <a href="{{ $material->file_path ? route('siswa.lms.materials.download', $material->id) : ($material->file_url ?? '#') }}" download class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                        <i class="fas fa-download"></i> Unduh Gambar
                                    </a>
                                </div>
                            @elseif(($material->material_type === 'pdf' || str_ends_with(strtolower($material->file_name ?? $material->file_path ?? ''), '.pdf') || str_contains(strtolower($material->title ?? ''), '[pdf]')) && strtolower(pathinfo($material->file_name ?? $material->file_path ?? '', PATHINFO_EXTENSION)) === 'pdf')
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
                                        <a href="{{ route('siswa.lms.materials.view', $material->id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/10 font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                            <i class="fas fa-external-link-alt"></i> Buka / Preview
                                        </a>
                                        <a href="{{ route('siswa.lms.materials.download', $material->id) }}" download class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition-all shadow-sm flex items-center gap-1.5" onclick="event.stopPropagation()">
                                            <i class="fas fa-download"></i> Unduh File
                                        </a>
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
                <div class="bg-indigo-50/50 rounded-xl border border-indigo-100 p-3.5 flex items-center justify-between group">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-sm {{ $attempt ? 'bg-emerald-500' : 'bg-indigo-600' }}">
                            <i class="fas {{ $attempt ? 'fa-check' : 'fa-gamepad' }}"></i>
                        </span>
                        <div>
                            <p class="font-bold text-indigo-900 text-sm flex items-center gap-2">
                                {{ $game->title }}
                                <span class="bg-indigo-100 text-indigo-700 text-[9px] font-bold px-1.5 py-0.5 rounded-md uppercase">{{ str_replace('_', ' ', $game->game_type) }}</span>
                            </p>
                            <p class="text-[10px] text-indigo-400 font-bold uppercase mt-0.5"><i class="fas fa-star text-yellow-400"></i> REWARD: {{ $game->reward_points }} EXP</p>
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
                                        <span class="bg-amber-400 text-slate-950 text-[10px] px-2.5 py-0.5 rounded-full border border-amber-300 uppercase tracking-wider font-extrabold shadow-xs">👑 Anda Ketua Kelompok</span>
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
                                <i class="fas fa-exclamation-triangle text-amber-600 text-lg flex-shrink-0"></i>
                                <span>Anda belum dimasukkan ke dalam kelompok tugas oleh Guru. Silakan hubungi Guru Anda untuk dimasukkan ke kelompok.</span>
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
                <div><strong class="{{ $hasModule ? 'text-white font-bold' : 'text-blue-700' }}">Catatan Guru:</strong> {{ $sub->teacher_notes }}</div>
            </div>
            @endif

            @if($sub && $sub->status === 'revision_requested')
            <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-4 mb-4 text-amber-900 shadow-sm">
                <div class="flex items-center gap-2 font-bold text-sm text-amber-800 mb-1">
                    <i class="fas fa-exclamation-triangle text-amber-600 text-lg"></i> Permintaan Revisi Dari Guru
                </div>
                <p class="text-xs text-amber-800 font-medium leading-relaxed">{{ $sub->revision_notes ?: $sub->feedback ?: 'Guru meminta Anda untuk memperbaiki dan mengunggah ulang berkas jawaban tugas ini.' }}</p>
            </div>
            @endif

            @if($sub && ($sub->submission_text || $sub->file_path))
            <div class="border border-gray-100 bg-gray-50 rounded-xl p-4 mb-3">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
                    <i class="fas fa-paperclip"></i> {{ $isGroupWork ? 'Jawaban / Berkas Tugas Kelompok' : 'Jawaban / Tugas Anda' }}
                    @if($isGroupWork && $sub->student)
                    <span class="text-[10px] font-semibold text-purple-700 ml-1">(Dikumpulkan oleh: {{ $sub->student->user->name ?? $sub->student->full_name }})</span>
                    @endif
                </p>
                @if($sub->submission_text)
                <div class="bg-white border border-gray-200 rounded-lg p-3 text-sm text-gray-700 whitespace-pre-wrap mb-3">{!! $sub->submission_text !!}</div>
                @endif
                @if($sub->file_path)
                <a href="{{ Storage::disk('public')->url($sub->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors text-sm font-semibold shadow-sm">
                    <i class="fas fa-file-download"></i> Unduh File Jawaban
                </a>
                @endif
            </div>
            @endif

            @php
                $canSubmit = false;
                $canRevise = false;
                if ($isGroupWork) {
                    if ($myGroup && $isLeader) {
                        $canSubmit = !$sub || $sub->status === 'draft';
                        $canRevise = $sub && ($sub->status === 'revision_requested' || ($sub->status === 'graded' && $assignment->allow_resubmit && $assignment->canResubmit($sub->student_id ?? null)));
                    }
                } else {
                    $canSubmit = !$sub || $sub->status === 'draft';
                    $canRevise = $sub && ($sub->status === 'revision_requested' || ($sub->status === 'graded' && $assignment->allow_resubmit && $assignment->canResubmit($sub->student_id ?? null)));
                }
            @endphp

            {{-- Group Member Notice (If not leader and group work) --}}
            @if($isGroupWork && $myGroup && !$isLeader)
            <div class="p-4 rounded-2xl border-2 border-purple-200 bg-purple-50/60 mb-3 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-purple-200 text-purple-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-info text-xs"></i>
                </div>
                <div class="text-xs text-purple-900">
                    <p class="font-extrabold">Informasi Pengumpulan Tugas Kelompok</p>
                    @if($sub && $sub->status !== 'draft')
                    <p class="font-medium text-purple-800 mt-0.5">
                        Tugas kelompok ini <strong class="text-emerald-700">sudah dikumpulkan</strong> oleh Ketua Kelompok Anda (<strong class="text-gray-900">{{ $myGroup->leader?->user?->name ?? 'Ketua' }}</strong>). Anda tidak perlu mengunggah berkas lagi.
                    </p>
                    @else
                    <p class="font-medium text-purple-800 mt-0.5">
                        Pengunggahan berkas tugas dilakukan oleh Ketua Kelompok (<strong class="text-gray-900">{{ $myGroup->leader?->user?->name ?? 'Ketua Kelompok' }}</strong>). Nilai dari guru akan otomatis masuk ke akun Anda.
                    </p>
                    @endif
                </div>
            </div>
            @endif

            @if($canSubmit || $canRevise)
            <details class="group/submit" open>
                <summary class="cursor-pointer text-base font-extrabold flex items-center gap-2 transition-colors {{ $hasModule ? 'text-white hover:text-white/80' : 'text-blue-600 hover:text-blue-700' }}">
                    <i class="fas fa-upload text-sm"></i> {{ $canRevise ? 'Kirim Revisi Tugas' : ($isGroupWork ? 'Kumpulkan Tugas Kelompok (Sebagai Ketua)' : 'Kumpulkan Tugas Sekarang') }}
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
                        <span>Anda mengunggah tugas mewakili <strong>{{ $myGroup->name }}</strong> ({{ $myGroup->members->count() }} Anggota). Berkas jawaban akan terhubung ke semua anggota.</span>
                    </div>
                    @endif
                    @php
                        $aType = $assignment->assignment_type ?? 'file_text';
                    @endphp
                    <div class="p-3.5 rounded-xl text-xs font-bold flex items-center gap-2 border shadow-sm {{ $hasModule ? 'bg-white/10 border-white/20 text-white' : 'bg-white border-blue-200 text-blue-900' }}">
                        <i class="fas fa-info-circle text-base text-amber-400"></i>
                        <span>Ketentuan Pengumpulan: 
                            @if($aType === 'file')
                                <strong class="underline decoration-rose-400">Wajib Unggah File</strong>
                            @elseif($aType === 'text')
                                <strong class="underline decoration-rose-400">Wajib Mengisi Teks Jawaban</strong>
                            @elseif($aType === 'link')
                                <strong class="underline decoration-rose-400">Wajib Memasukkan Link URL / Teks</strong>
                            @else
                                <strong class="underline decoration-rose-400">Wajib Upload File DAN Mengisi Teks Jawaban</strong>
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
                        <label class="block text-xs font-bold mb-1 {{ $hasModule ? 'text-white' : 'text-gray-700' }}">
                            Berkas File @if(in_array($aType, ['file', 'file_text']) && !($sub && $sub->file_path)) <span class="text-rose-500 font-extrabold">* (Wajib)</span> @endif
                        </label>
                        <div class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:bg-gray-50 transition-colors">
                            <input type="file" name="file" onchange="validateLmsFileSize(this)" class="w-full text-sm cursor-pointer {{ $hasModule ? 'text-white/80 file:bg-white/20 file:text-white hover:file:bg-white/30' : 'text-gray-600 file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 file:px-4 file:py-2 file:border-none file:rounded-lg file:font-bold file:mr-4' }}" {{ (in_array($aType, ['file', 'file_text']) && !($sub && $sub->file_path)) ? 'required' : '' }}>
                            <p class="text-[10px] text-gray-400 mt-2 font-medium">Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG. Maksimal 10MB.</p>
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
<style>
.game-bg { background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); }
.game-card-glow { box-shadow: 0 0 40px rgba(139,92,246,0.3), 0 25px 50px rgba(0,0,0,0.5); }
.neon-text { text-shadow: 0 0 20px currentColor; }
.glass { background: rgba(255,255,255,0.07); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.15); }
.glass-light { background: rgba(255,255,255,0.12); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.2); }
.keyboard-key { box-shadow: 0 4px 0 rgba(0,0,0,0.4); transition: all 0.1s; }
.keyboard-key:active { box-shadow: 0 1px 0 rgba(0,0,0,0.4); transform: translateY(3px); }
.word-tile { border-bottom: 3px solid; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.word-tile.revealed { animation: tile-reveal 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
.quiz-opt { transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
.quiz-opt:hover:not(:disabled) { transform: translateY(-3px) scale(1.01); }
.progress-glow { box-shadow: 0 0 15px currentColor; }
.flashcard-scene { perspective: 1200px; }
.flashcard-inner { transition: transform 0.7s cubic-bezier(0.34, 1.56, 0.64, 1); transform-style: preserve-3d; }
.flashcard-inner.flipped { transform: rotateY(180deg); }
.flashcard-face { backface-visibility: hidden; -webkit-backface-visibility: hidden; }
.flashcard-back { transform: rotateY(180deg); }
.match-btn { transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.match-btn:hover:not(:disabled):not(.matched) { transform: scale(1.04) translateY(-2px); }
.spin-btn { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.spin-btn:hover:not(:disabled) { transform: scale(1.07); }
.tf-btn { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.tf-btn:hover:not(:disabled) { transform: scale(1.05) translateY(-4px); }
.particle-float { animation: particle-float 6s ease-in-out infinite; }
@keyframes particle-float { 0%,100% { transform: translateY(0) rotate(0deg); opacity:0.6; } 50% { transform: translateY(-30px) rotate(180deg); opacity:0.9; } }
@keyframes tile-reveal { 0% { transform: scale(0.8); opacity:0.3; } 100% { transform: scale(1); opacity:1; } }
@keyframes pulse-ring { 0% { transform: scale(0.8); opacity:1; } 100% { transform: scale(1.8); opacity:0; } }
@keyframes score-pop { 0% { transform: scale(0) rotate(-10deg); opacity:0; } 60% { transform: scale(1.2) rotate(5deg); } 100% { transform: scale(1) rotate(0deg); opacity:1; } }
.score-pop { animation: score-pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
@keyframes correct-flash { 0%,100% { background: rgba(16,185,129,0.2); } 50% { background: rgba(16,185,129,0.5); } }
@keyframes wrong-flash { 0%,100% { background: rgba(239,68,68,0.2); } 50% { background: rgba(239,68,68,0.5); } }
.correct-flash { animation: correct-flash 0.4s ease 2; }
.wrong-flash { animation: wrong-flash 0.4s ease 2; }
.life-heart { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.life-heart.lost { animation: heart-lost 0.5s forwards; }
@keyframes heart-lost { 0% { transform: scale(1.3); } 100% { transform: scale(0.7); filter: grayscale(1); opacity:0.3; } }
</style>
<div x-data="gamePlayer()" 
    @open-game-player.window="loadGame($event.detail)" 
    x-show="open" 
    class="fixed inset-0 overflow-y-auto" style="display: none; z-index: 999999 !important;"
    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

    {{-- Dark backdrop (clickable to close, placed underneath modal content) --}}
    <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-md transition-opacity" @click="closeGame()"></div>

    {{-- Floating particles (decorative, pointer-events-none) --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-10">
        <div class="particle-float absolute top-[10%] left-[8%] w-2 h-2 rounded-full bg-violet-400 opacity-40"></div>
        <div class="particle-float absolute top-[30%] right-[10%] w-3 h-3 rounded-full bg-pink-400 opacity-30" style="animation-delay:1s"></div>
        <div class="particle-float absolute bottom-[20%] left-[15%] w-2 h-2 rounded-full bg-cyan-400 opacity-40" style="animation-delay:2s"></div>
        <div class="particle-float absolute top-[70%] right-[20%] w-1.5 h-1.5 rounded-full bg-amber-300 opacity-40" style="animation-delay:3s"></div>
    </div>

    {{-- Centered modal card --}}
    <div class="flex items-center justify-center min-h-screen p-2 sm:p-4 relative z-20">
        <div x-show="open"
            x-transition:enter="ease-out duration-400"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="relative w-full max-w-4xl flex flex-col game-card-glow rounded-2xl sm:rounded-3xl overflow-hidden min-h-[520px] max-h-[96vh] sm:max-h-[90vh] z-30 shadow-2xl"
            style="background: linear-gradient(180deg, #1e1b4b 0%, #0f172a 100%); border: 1px solid rgba(255,255,255,0.2);">


            {{-- Animated gradient ring top --}}
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-violet-500 via-pink-500 via-50% to-cyan-500"></div>

            {{-- HEADER --}}
            <div class="relative shrink-0 px-4 sm:px-6 py-3.5 sm:py-4 flex items-center justify-between z-20" style="background: rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.15)">
                {{-- Game Icon + Title --}}
                <div class="flex items-center gap-3">
                    <div class="relative w-11 h-11 rounded-2xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #7c3aed, #ec4899); box-shadow: 0 0 20px rgba(124,58,237,0.6)">
                        <i class="fas fa-gamepad text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-white text-sm sm:text-base leading-tight" style="color: #ffffff !important;" x-text="game.title"></h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full" style="background: rgba(139,92,246,0.4); color: #c4b5fd; border: 1px solid rgba(139,92,246,0.6)" x-text="game.type.replace('_', ' ')"></span>
                        </div>
                    </div>
                </div>

                {{-- EXP Badge + Close --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-2xl" style="background: rgba(251,191,36,0.2); border: 1px solid rgba(251,191,36,0.5)" x-show="!completed">
                        <i class="fas fa-bolt text-yellow-400 text-sm"></i>
                        <span class="text-yellow-300 font-black text-sm">+<span x-text="game.reward"></span> EXP</span>
                    </div>
                    {{-- Progress dots for multi-item games --}}
                    <div class="hidden sm:flex items-center gap-1" x-show="!completed && totalItems > 1 && game.type !== 'spin_wheel'">
                        <template x-for="i in Math.min(totalItems, 8)" :key="i">
                            <div class="w-2 h-2 rounded-full transition-all duration-300" :class="i-1 < currentIndex ? 'bg-violet-400' : (i-1 === currentIndex ? 'bg-white scale-125' : 'bg-white/20')"></div>
                        </template>
                    </div>
                    <button @click="closeGame()" class="w-10 h-10 rounded-2xl flex items-center justify-center text-white/70 hover:text-white hover:bg-white/15 transition-all">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="h-1.5 w-full shrink-0" style="background: rgba(255,255,255,0.08)" x-show="!completed && totalItems > 1 && game.type !== 'spin_wheel'">
                <div class="h-full transition-all duration-700 ease-out" style="background: linear-gradient(90deg, #7c3aed, #ec4899)" :style="`width: ${((currentIndex + 1) / Math.max(totalItems, 1)) * 100}%`"></div>
            </div>

            {{-- GAME AREA --}}
            <div class="flex-1 overflow-y-auto relative">

                {{-- Loader --}}
                <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center z-50" style="background: rgba(15,12,41,0.95)">
                    <div class="relative w-20 h-20 mb-5">
                        <div class="absolute inset-0 rounded-full border-4 border-violet-500/30"></div>
                        <div class="absolute inset-0 rounded-full border-4 border-t-violet-500 border-r-pink-500 animate-spin"></div>
                        <div class="absolute inset-[6px] rounded-full border-4 border-t-transparent border-pink-400/50 animate-spin" style="animation-direction: reverse; animation-duration: 0.8s"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <i class="fas fa-gamepad text-violet-400 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-violet-300 font-bold tracking-widest text-sm uppercase animate-pulse">Memuat Game...</p>
                </div>

                {{-- GAME CANVAS --}}
                <div class="p-4 sm:p-8 min-h-full flex flex-col items-center justify-center">

                    {{-- ══════════════════════════════ --}}
                    {{-- HARDCORE MODE STATUS BAR --}}
                    {{-- ══════════════════════════════ --}}
                    <div x-show="['quiz', 'true_false', 'word_guess', 'scramble', 'sequence'].includes(game.type) && !completed && !loading" class="w-full max-w-3xl mx-auto mb-6 flex items-center justify-between gap-4 p-4 rounded-3xl" style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.15); display: none;">
                        
                        {{-- Timer --}}
                        <div x-show="timeLimit > 0" class="flex-1">
                            <div class="flex items-center justify-between text-xs font-bold text-white/90 mb-1">
                                <span><i class="fas fa-stopwatch text-rose-400 mr-1"></i> Waktu</span>
                                <span class="text-white font-black" x-text="timeRemaining + 's'"></span>
                            </div>
                            <div class="w-full h-2.5 rounded-full overflow-hidden bg-white/10">
                                <div class="h-full transition-all duration-1000 ease-linear" :style="'width: ' + ((timeRemaining/timeLimit)*100) + '%; background: ' + (timeRemaining <= 5 ? '#ef4444' : 'linear-gradient(90deg, #f43f5e, #ec4899)')"></div>
                            </div>
                        </div>

                        {{-- Spacer if timer is hidden but combo/lives shown --}}
                        <div x-show="!timeLimit || timeLimit <= 0" class="flex-1"></div>

                        {{-- Combo --}}
                        <div x-show="currentCombo > 0" class="px-3 py-1 rounded-xl bg-orange-500/30 border border-orange-500/50 relative mx-4 shrink-0">
                            <div x-show="showComboEffect" class="absolute -inset-2 bg-orange-400 opacity-20 blur-xl rounded-full transition-opacity duration-300"></div>
                            <span class="text-orange-400 font-black text-sm tracking-wide animate-pulse">🔥 COMBO x<span x-text="currentCombo"></span></span>
                        </div>

                        {{-- Lives --}}
                        <div x-show="maxLives !== null" class="flex gap-1 shrink-0">
                            <template x-for="i in maxLives">
                                <i class="fas fa-heart text-xl transition-all" :class="i <= lives ? 'text-rose-500 scale-110 drop-shadow-[0_0_8px_rgba(244,63,94,0.6)]' : 'text-white/20 scale-90'"></i>
                            </template>
                        </div>
                    </div>

                    {{-- ══════════════════════════════ --}}
                    {{-- 1. FLASHCARD PLAYER --}}
                    {{-- ══════════════════════════════ --}}
                    <div x-show="game.type === 'flashcard' && !loading && !completed" class="w-full max-w-2xl mx-auto flex flex-col items-center">
                        {{-- Counter + Progress --}}
                        <div class="mb-5 w-full flex items-center justify-between">
                            <span class="text-white/60 text-xs font-bold uppercase tracking-widest">Kartu</span>
                            <span class="text-white font-black text-sm"><span x-text="currentIndex + 1"></span> / <span x-text="totalItems"></span></span>
                        </div>

                        {{-- Flashcard (fixed height with scrollable inner) --}}
                        <div class="flashcard-scene w-full mb-8 cursor-pointer" style="height: 280px; min-height: 200px" @click="flipCard()">
                            <div class="flashcard-inner w-full h-full relative" :class="isFlipped ? 'flipped' : ''">
                                {{-- Front --}}
                                <div class="flashcard-face absolute inset-0 rounded-3xl flex flex-col" style="background: linear-gradient(135deg, rgba(30, 27, 75, 0.9), rgba(49, 46, 129, 0.8)); border: 2px solid rgba(139,92,246,0.6); box-shadow: 0 0 30px rgba(124,58,237,0.3)">
                                    {{-- Label bar --}}
                                    <div class="shrink-0 flex items-center justify-between px-5 pt-4 pb-3" style="border-bottom: 1px solid rgba(139,92,246,0.3)">
                                        <span class="text-[10px] font-black tracking-[0.2em] uppercase" style="color: #c4b5fd;">❓ PERTANYAAN</span>
                                        <span class="text-xs font-black px-2.5 py-0.5 rounded-full" style="background: rgba(139,92,246,0.4); color: #e9d5ff; border: 1px solid rgba(139,92,246,0.5)" x-text="(currentIndex + 1) + ' / ' + totalItems"></span>
                                    </div>
                                    {{-- Scrollable content --}}
                                    <div class="flex-1 overflow-y-auto flex items-center justify-center p-6 text-center">
                                        <h2 class="font-extrabold text-white leading-snug break-words" style="color: #ffffff !important; font-size: clamp(1.1rem, 3vw, 2rem)" x-text="currentFlashcard.term"></h2>
                                    </div>
                                    {{-- Hint footer --}}
                                    <div class="shrink-0 flex items-center justify-center gap-2 pb-3 text-white/50 text-xs font-bold">
                                        <i class="fas fa-sync-alt"></i> Klik kartu untuk melihat jawaban
                                    </div>
                                </div>
                                {{-- Back --}}
                                <div class="flashcard-face flashcard-back absolute inset-0 rounded-3xl flex flex-col" style="background: linear-gradient(135deg, rgba(6, 78, 59, 0.9), rgba(15, 118, 110, 0.85)); border: 2px solid rgba(16,185,129,0.6); box-shadow: 0 0 30px rgba(16,185,129,0.3)">
                                    {{-- Label bar --}}
                                    <div class="shrink-0 flex items-center justify-between px-5 pt-4 pb-3" style="border-bottom: 1px solid rgba(16,185,129,0.3)">
                                        <span class="text-[10px] font-black tracking-[0.2em] uppercase" style="color: #6ee7b7;">✅ JAWABAN</span>
                                        <i class="fas fa-check-circle text-emerald-400"></i>
                                    </div>
                                    {{-- Scrollable content --}}
                                    <div class="flex-1 overflow-y-auto flex items-center justify-center p-6 text-center">
                                        <h2 class="font-extrabold text-white leading-snug break-words" style="color: #ffffff !important; font-size: clamp(1.1rem, 3vw, 2rem)" x-text="currentFlashcard.definition"></h2>
                                    </div>
                                    <div class="shrink-0 flex items-center justify-center gap-3 pb-4 px-4" @click.stop>
                                        <button @click="answerFlashcard(false)" class="flex-1 py-3 rounded-xl font-bold text-white transition-all hover:scale-105 shadow-md" style="background: linear-gradient(135deg, #ef4444, #b91c1c); border: 1px solid rgba(239,68,68,0.5)">
                                            <i class="fas fa-times-circle mr-1"></i> Belum Hafal
                                        </button>
                                        <button @click="answerFlashcard(true)" class="flex-1 py-3 rounded-xl font-bold text-white transition-all hover:scale-105 shadow-md" style="background: linear-gradient(135deg, #10b981, #047857); border: 1px solid rgba(16,185,129,0.5)">
                                            <i class="fas fa-check-circle mr-1"></i> Sudah Hafal!
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Flashcard Navigation (Hidden when flipped) --}}
                        <div class="flex items-center gap-4 w-full max-w-sm transition-opacity duration-300" :class="isFlipped ? 'opacity-0 pointer-events-none' : 'opacity-100'">
                            <button @click="prevCard(); $event.stopPropagation()" :disabled="currentIndex === 0" class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-bold transition-all disabled:opacity-30 hover:scale-110" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="flex-1 h-2 rounded-full overflow-hidden" style="background: rgba(255,255,255,0.15)">
                                <div class="h-full rounded-full transition-all duration-500" style="background: linear-gradient(90deg, #7c3aed, #ec4899)" :style="`width: ${((currentIndex + 1) / totalItems) * 100}%`"></div>
                            </div>
                            <button @click="nextCard(); $event.stopPropagation()" :disabled="currentIndex === totalItems - 1" class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-bold transition-all disabled:opacity-30 hover:scale-110" style="background: linear-gradient(135deg, #7c3aed, #ec4899); color: white">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ══════════════════════════════ --}}
                    {{-- 2. MATCH PAIRS PLAYER --}}
                    {{-- ══════════════════════════════ --}}
                    <div x-show="game.type === 'match' && !loading && !completed" class="w-full max-w-4xl mx-auto flex flex-col items-center">
                        {{-- Cara Bermain --}}
                        <div class="w-full mb-6 p-4 rounded-2xl flex items-start gap-3" style="background: rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.4)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(139,92,246,0.4)">
                                <i class="fas fa-info-circle text-violet-300 text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] font-black uppercase tracking-widest text-violet-300 mb-0.5">CARA BERMAIN</div>
                                <div class="font-bold text-white text-sm leading-snug" style="color: #ffffff !important;">Pilih satu kotak di kiri dan pasangkan dengan jawaban yang tepat di kanan.</div>
                            </div>
                        </div>

                        <div class="w-full mb-6 flex items-center justify-between">
                            <h4 class="text-white font-bold text-sm" style="color: #ffffff !important;">🔗 Cocokkan Pasangan</h4>
                            <div class="px-4 py-2 rounded-xl font-black text-sm flex items-center gap-2" style="background: rgba(16,185,129,0.25); color: #34d399; border: 1px solid rgba(16,185,129,0.4)">
                                <i class="fas fa-check-double"></i> <span x-text="matchedPairs.length"></span>/<span x-text="totalItems"></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:gap-5 w-full">
                            <div class="flex flex-col gap-3">
                                <template x-for="item in matchItems.terms" :key="'term-'+item.id">
                                    <button @click="selectMatchItem('term', item)" :disabled="matchedPairs.includes(item.id)"
                                        class="match-btn w-full p-4 rounded-2xl font-bold text-sm text-center transition-all"
                                        :class="{
                                            'opacity-50 cursor-default': matchedPairs.includes(item.id),
                                            'scale-105': selectedTerm === item && !matchedPairs.includes(item.id)
                                        }"
                                        :style="matchedPairs.includes(item.id) ? 'background: rgba(16,185,129,0.3); border: 2px solid rgba(16,185,129,0.6); color: #6ee7b7' : (selectedTerm === item ? 'background: linear-gradient(135deg,#7c3aed,#6d28d9); border: 2px solid rgba(139,92,246,0.9); color:white; box-shadow: 0 0 25px rgba(124,58,237,0.5)' : 'background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #ffffff')">
                                        <span x-text="item.text"></span>
                                    </button>
                                </template>
                            </div>
                            <div class="flex flex-col gap-3">
                                <template x-for="item in matchItems.definitions" :key="'def-'+item.id">
                                    <button @click="selectMatchItem('definition', item)" :disabled="matchedPairs.includes(item.id)"
                                        class="match-btn w-full p-4 rounded-2xl font-bold text-sm text-center transition-all"
                                        :class="{
                                            'opacity-50 cursor-default': matchedPairs.includes(item.id),
                                            'scale-105': selectedDef === item && !matchedPairs.includes(item.id)
                                        }"
                                        :style="matchedPairs.includes(item.id) ? 'background: rgba(16,185,129,0.3); border: 2px solid rgba(16,185,129,0.6); color: #6ee7b7' : (selectedDef === item ? 'background: linear-gradient(135deg,#ec4899,#be185d); border: 2px solid rgba(236,72,153,0.9); color:white; box-shadow: 0 0 25px rgba(236,72,153,0.5)' : 'background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #ffffff')">
                                        <span x-text="item.text"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- ══════════════════════════════ --}}
                    {{-- 3. SPIN WHEEL PLAYER --}}
                    {{-- ══════════════════════════════ --}}
                    <div x-show="game.type === 'spin_wheel' && !loading && !completed" class="w-full max-w-lg mx-auto flex flex-col items-center">
                        {{-- Cara Bermain --}}
                        <div class="w-full mb-6 p-4 rounded-2xl flex items-start gap-3" style="background: rgba(236,72,153,0.2); border: 1px solid rgba(236,72,153,0.4)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(236,72,153,0.4)">
                                <i class="fas fa-info-circle text-pink-300 text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] font-black uppercase tracking-widest text-pink-300 mb-0.5">CARA BERMAIN</div>
                                <div class="font-bold text-white text-sm leading-snug" style="color: #ffffff !important;">Klik tombol putar dan tunggu untuk melihat hadiah apa yang akan kamu dapatkan!</div>
                            </div>
                        </div>

                        <div class="relative mb-8 mt-4">
                            {{-- Glow ring --}}
                            <div class="absolute inset-[-8px] rounded-full opacity-60" style="background: conic-gradient(from 0deg, #7c3aed, #ec4899, #06b6d4, #10b981, #f59e0b, #7c3aed); filter: blur(12px)"></div>
                            {{-- Pointer --}}
                            <div class="absolute -top-6 left-1/2 -translate-x-1/2 z-30 text-4xl drop-shadow-lg" style="filter: drop-shadow(0 0 8px rgba(239,68,68,0.8))">▼</div>
                            {{-- Wheel --}}
                            <div class="relative w-[280px] h-[280px] sm:w-[360px] sm:h-[360px] rounded-full overflow-hidden"
                                 style="border: 8px solid rgba(255,255,255,0.2); box-shadow: 0 0 60px rgba(124,58,237,0.4), inset 0 0 30px rgba(0,0,0,0.4)"
                                 :style="`transform: rotate(${wheelRotation}deg); transition: transform ${wheelSpinning ? '4s' : '0s'} cubic-bezier(0.1, 0.7, 0.1, 1)`">
                                <template x-if="game.data.items">
                                    <template x-for="(item, index) in (game.data.items || [])" :key="index">
                                        <div class="absolute top-0 left-0 w-full h-full flex items-center justify-center origin-center"
                                             :style="`transform: rotate(${index * (360 / Math.max(1, (game.data.items || []).length))}deg)`">
                                            <div class="absolute h-1/2 w-1/2 origin-bottom-right"
                                                 :style="`transform: skewY(${90 - (360 / Math.max(1, (game.data.items || []).length))}deg); background: ${getWheelColor(index)}`">
                                            </div>
                                            <div class="absolute z-10 font-black text-white text-xs sm:text-sm drop-shadow-md"
                                                 :style="`transform: rotate(${((360 / Math.max(1, (game.data.items || []).length)) / 2) - 90}deg) translate(30%, 0); transform-origin: center; text-shadow: 0 1px 4px rgba(0,0,0,0.5)`">
                                                 <span x-text="item"></span>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                                {{-- Center Hub --}}
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-14 h-14 rounded-full z-20 flex items-center justify-center" style="background: linear-gradient(135deg, #1a1535, #0f0c29); border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 0 20px rgba(0,0,0,0.6)">
                                    <i class="fas fa-star text-yellow-400 text-xl" style="filter: drop-shadow(0 0 6px gold)"></i>
                                </div>
                            </div>
                        </div>
                        <button x-show="!spinResult" @click="spinWheel()" :disabled="wheelSpinning" class="spin-btn px-10 py-5 rounded-full font-black text-white text-xl tracking-widest flex items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none relative overflow-hidden" style="background: linear-gradient(135deg, #7c3aed, #ec4899); box-shadow: 0 0 40px rgba(124,58,237,0.6)">
                            <div class="absolute inset-0 bg-white opacity-0 hover:opacity-10 transition-opacity"></div>
                            <i class="fas fa-sync-alt" :class="wheelSpinning ? 'animate-spin' : ''"></i>
                            PUTAR SEKARANG!
                        </button>
                        
                        {{-- Spin Result & Execution Button --}}
                        <div x-show="spinResult" x-transition.scale class="mt-8 flex flex-col items-center text-center p-6 rounded-3xl" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3)">
                            <p class="text-white/80 font-bold uppercase tracking-widest text-xs mb-2">HASIL PUTARAN</p>
                            <h2 class="text-3xl sm:text-4xl font-black text-yellow-300 mb-6 drop-shadow-md" x-text="spinResult"></h2>
                            
                            <button @click="finishGame()" class="px-8 py-4 rounded-2xl font-black text-white text-lg flex items-center gap-3 transition-all hover:scale-105" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 0 30px rgba(16,185,129,0.4)">
                                <i class="fas fa-gift text-2xl"></i> SELESAIKAN & KLAIM REWARD
                            </button>
                        </div>
                    </div>

                    {{-- ══════════════════════════════ --}}
                    {{-- 4. QUIZ PLAYER --}}
                    {{-- ══════════════════════════════ --}}
                    <div x-show="game.type === 'quiz' && !loading && !completed" class="w-full max-w-2xl mx-auto flex flex-col items-center" style="display: none;">
                        {{-- Cara Bermain --}}
                        <div class="w-full mb-6 p-4 rounded-2xl flex items-start gap-3" style="background: rgba(16,185,129,0.2); border: 1px solid rgba(16,185,129,0.4)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(16,185,129,0.4)">
                                <i class="fas fa-info-circle text-emerald-300 text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] font-black uppercase tracking-widest text-emerald-300 mb-0.5">CARA BERMAIN</div>
                                <div class="font-bold text-white text-sm leading-snug" style="color: #ffffff !important;">Baca pertanyaan dengan saksama dan pilih satu jawaban yang paling tepat dari pilihan yang tersedia.</div>
                            </div>
                        </div>
                        
                        {{-- Question Card --}}
                        <div class="w-full mb-8 p-6 sm:p-8 rounded-3xl text-center" style="background: rgba(30, 27, 75, 0.7); border: 2px solid rgba(139, 92, 246, 0.4); box-shadow: 0 10px 30px rgba(0,0,0,0.3)">
                            <span class="inline-block text-[10px] font-black uppercase tracking-[0.2em] mb-4 px-3 py-1 rounded-full" style="background: rgba(16,185,129,0.4); color: #a7f3d0; border: 1px solid rgba(16,185,129,0.6)">Soal <span x-text="currentIndex + 1"></span> dari <span x-text="totalItems"></span></span>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-white leading-snug" style="color: #ffffff !important; text-shadow: 0 2px 6px rgba(0,0,0,0.5)" x-text="currentQuiz.question"></h2>
                        </div>
                        {{-- Options --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
                            <template x-for="(opt, idx) in (currentQuiz.options || [])" :key="idx">
                                <button @click="answerQuiz(idx)" :disabled="selectedAnswer !== null"
                                    class="quiz-opt relative p-5 sm:p-6 rounded-2xl font-bold text-left flex items-center gap-4 min-h-[90px] overflow-hidden transition-all hover:scale-[1.02]"
                                    :class="{'correct-flash': selectedAnswer === idx && isCorrect, 'wrong-flash': selectedAnswer === idx && !isCorrect}"
                                    :style="selectedAnswer === null ? 'background: rgba(255,255,255,0.12); border: 2px solid rgba(255,255,255,0.25); color: #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.2)' : (selectedAnswer === idx && isCorrect ? 'background: #059669; border: 3px solid #34d399; color: #ffffff' : (selectedAnswer === idx && !isCorrect ? 'background: #dc2626; border: 3px solid #f87171; color: #ffffff' : (idx === currentQuiz.answer && selectedAnswer !== null ? 'background: #059669; border: 3px solid #34d399; color: #ffffff' : 'background: rgba(255,255,255,0.05); border: 2px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.5)')))">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-lg shrink-0 shadow-inner"
                                        :style="selectedAnswer === null ? 'background: rgba(139,92,246,0.6); color: #ffffff' : (selectedAnswer === idx && isCorrect ? 'background: #10b981; color: white' : (selectedAnswer === idx && !isCorrect ? 'background: #ef4444; color: white' : (idx === currentQuiz.answer && selectedAnswer !== null ? 'background: #10b981; color: white' : 'background: rgba(255,255,255,0.2); color: rgba(255,255,255,0.5)')))"
                                        x-text="['A','B','C','D'][idx]"></div>
                                    <span class="leading-tight text-base sm:text-lg flex-1 font-bold text-white" style="color: #ffffff !important;" x-text="opt"></span>
                                    {{-- Correct/Wrong indicator --}}
                                    <div class="ml-auto shrink-0" x-show="selectedAnswer !== null && selectedAnswer === idx">
                                        <i class="text-xl" :class="isCorrect ? 'fas fa-check-circle text-emerald-300' : 'fas fa-times-circle text-rose-300'"></i>
                                    </div>
                                    <div class="ml-auto shrink-0" x-show="selectedAnswer !== null && selectedAnswer !== idx && idx === currentQuiz.answer">
                                        <i class="fas fa-check-circle text-emerald-300 text-xl"></i>
                                    </div>
                                </button>
                            </template>
                        </div>
                        <div x-show="selectedAnswer !== null" class="mt-8">
                            <button @click="nextQuiz()" class="px-8 py-4 rounded-2xl font-black text-white flex items-center gap-3 text-lg transition-all hover:scale-105" style="background: linear-gradient(135deg, #7c3aed, #ec4899); box-shadow: 0 0 30px rgba(124,58,237,0.4)">
                                Lanjut <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ══════════════════════════════ --}}
                    {{-- 5. TRUE/FALSE PLAYER --}}
                    {{-- ══════════════════════════════ --}}
                    <div x-show="game.type === 'true_false' && !loading && !completed" class="w-full max-w-2xl mx-auto flex flex-col items-center" style="display: none;">
                        {{-- Cara Bermain --}}
                        <div class="w-full mb-6 p-4 rounded-2xl flex items-start gap-3" style="background: rgba(30, 58, 138, 0.5); border: 1px solid rgba(96, 165, 250, 0.4)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(59,130,246,0.4)">
                                <i class="fas fa-info-circle text-blue-300 text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] font-black uppercase tracking-widest text-blue-300 mb-0.5">CARA BERMAIN</div>
                                <div class="font-bold text-white text-sm leading-snug" style="color: #ffffff !important;">Baca pernyataan di bawah ini dengan teliti. Tentukan apakah pernyataan tersebut BENAR atau SALAH.</div>
                            </div>
                        </div>

                        {{-- Statement --}}
                        <div class="w-full mb-8 p-6 sm:p-10 rounded-3xl text-center" style="background: rgba(30, 27, 75, 0.7); border: 2px solid rgba(139, 92, 246, 0.4); box-shadow: 0 10px 30px rgba(0,0,0,0.4)">
                            <span class="inline-block text-xs font-black uppercase tracking-[0.2em] mb-4 px-4 py-1.5 rounded-full" style="background: rgba(59,130,246,0.4); color: #bfdbfe; border: 1px solid rgba(59,130,246,0.6)">Pernyataan <span x-text="currentIndex + 1"></span> / <span x-text="totalItems"></span></span>
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white leading-snug tracking-wide" style="color: #ffffff !important; text-shadow: 0 2px 8px rgba(0,0,0,0.6)" x-text="currentTf.statement"></h2>
                        </div>
                        {{-- BENAR / SALAH Buttons --}}
                        <div class="flex gap-4 sm:gap-6 w-full justify-center">
                            <button @click="answerTf(true)" :disabled="selectedAnswer !== null"
                                class="tf-btn flex-1 max-w-[280px] p-6 sm:p-8 rounded-3xl flex flex-col items-center justify-center gap-4 font-black text-2xl cursor-pointer transition-all hover:-translate-y-2"
                                :style="selectedAnswer === null ? 'background: linear-gradient(135deg, rgba(16,185,129,0.35), rgba(5,150,105,0.45)); border: 3px solid #10b981; color: #a7f3d0; box-shadow: 0 10px 25px rgba(16,185,129,0.3)' : (selectedAnswer === true && isCorrect ? 'background: #059669; border: 3px solid #34d399; color: #ffffff; box-shadow: 0 0 40px rgba(16,185,129,0.7)' : (selectedAnswer === true && !isCorrect ? 'background: #dc2626; border: 3px solid #f87171; color: #ffffff' : (selectedAnswer !== null && selectedAnswer !== true && isCorrect === false ? 'background: #059669; border: 3px solid #34d399; color: #ffffff' : 'background: rgba(255,255,255,0.05); border: 3px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.4)')))">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full flex items-center justify-center text-3xl sm:text-4xl shadow-lg" style="background: rgba(16,185,129,0.5); color: #ffffff;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <span class="text-white font-black text-2xl tracking-wide" style="color: #ffffff !important;">BENAR</span>
                            </button>
                            <button @click="answerTf(false)" :disabled="selectedAnswer !== null"
                                class="tf-btn flex-1 max-w-[280px] p-6 sm:p-8 rounded-3xl flex flex-col items-center justify-center gap-4 font-black text-2xl cursor-pointer transition-all hover:-translate-y-2"
                                :style="selectedAnswer === null ? 'background: linear-gradient(135deg, rgba(239,68,68,0.35), rgba(220,38,38,0.45)); border: 3px solid #ef4444; color: #fecaca; box-shadow: 0 10px 25px rgba(239,68,68,0.3)' : (selectedAnswer === false && isCorrect ? 'background: #059669; border: 3px solid #34d399; color: #ffffff; box-shadow: 0 0 40px rgba(16,185,129,0.7)' : (selectedAnswer === false && !isCorrect ? 'background: #dc2626; border: 3px solid #f87171; color: #ffffff' : (selectedAnswer !== null && selectedAnswer !== false && isCorrect === false ? 'background: #059669; border: 3px solid #34d399; color: #ffffff' : 'background: rgba(255,255,255,0.05); border: 3px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.4)')))">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full flex items-center justify-center text-3xl sm:text-4xl shadow-lg" style="background: rgba(239,68,68,0.5); color: #ffffff;">
                                    <i class="fas fa-times"></i>
                                </div>
                                <span class="text-white font-black text-2xl tracking-wide" style="color: #ffffff !important;">SALAH</span>
                            </button>
                        </div>
                        {{-- Result feedback --}}
                        <div x-show="selectedAnswer !== null" class="mt-6 w-full p-4 rounded-2xl text-center" :style="isCorrect ? 'background: rgba(16,185,129,0.25); border: 2px solid rgba(16,185,129,0.5)' : 'background: rgba(239,68,68,0.25); border: 2px solid rgba(239,68,68,0.5)'">
                            <p class="font-black text-xl" :class="isCorrect ? 'text-emerald-300' : 'text-rose-300'" x-text="isCorrect ? '✅ Jawaban Benar!' : '❌ Jawaban Salah!'"></p>
                        </div>
                        <div x-show="selectedAnswer !== null" class="mt-6">
                            <button @click="nextTf()" class="px-8 py-4 rounded-2xl font-black text-white flex items-center gap-3 text-lg transition-all hover:scale-105" style="background: linear-gradient(135deg, #3b82f6, #6366f1); box-shadow: 0 0 30px rgba(59,130,246,0.4)">
                                Lanjut <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ══════════════════════════════ --}}
                    {{-- 6. WORD GUESS PLAYER --}}
                    {{-- ══════════════════════════════ --}}
                    <div x-show="game.type === 'word_guess' && !loading && !completed" class="w-full max-w-3xl mx-auto flex flex-col items-center" style="display: none;">
                        {{-- Cara Bermain --}}
                        <div class="w-full mb-6 p-4 rounded-2xl flex items-start gap-3" style="background: rgba(245,158,11,0.25); border: 1px solid rgba(245,158,11,0.5)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(245,158,11,0.4)">
                                <i class="fas fa-info-circle text-amber-300 text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] font-black uppercase tracking-widest text-amber-300 mb-0.5">CARA BERMAIN</div>
                                <div class="font-bold text-white text-sm leading-snug" style="color: #ffffff !important;">Tebak kata rahasia dengan memilih huruf pada keyboard di bawah. Perhatikan petunjuk yang diberikan!</div>
                            </div>
                        </div>

                        {{-- Top bar: Hint + Lives --}}
                        <div class="w-full mb-8 flex flex-col sm:flex-row items-stretch gap-3">
                            {{-- Hint --}}
                            <div class="flex-1 flex items-center gap-3 p-5 rounded-2xl" style="background: rgba(30, 27, 75, 0.7); border: 2px solid rgba(245, 158, 11, 0.4)">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 shadow-inner" style="background: rgba(245,158,11,0.4)">
                                    <i class="fas fa-lightbulb text-yellow-300 text-2xl"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-yellow-300 mb-1">💡 PETUNJUK</div>
                                    <div class="font-extrabold text-white text-lg sm:text-xl leading-tight" style="color: #ffffff !important; text-shadow: 0 2px 4px rgba(0,0,0,0.5)" x-text="currentGuess.hint || 'Tebak kata rahasia!'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Word tiles --}}
                        <div class="flex flex-wrap justify-center gap-3 mb-10 w-full px-2">
                            <template x-for="(char, idx) in (currentGuess.word || '').split('')" :key="idx">
                                <div class="word-tile rounded-2xl flex items-center justify-center font-black uppercase shadow-lg"
                                     style="width: 56px; height: 68px; font-size: 2rem"
                                     :class="char === ' ' ? '' : ''"
                                     :style="char === ' ' ? 'width: 24px; background: transparent; border: none; box-shadow: none' : (guessedLetters.includes(char.toUpperCase()) ? 'background: #059669; border-bottom: 5px solid #34d399; color: #ffffff; box-shadow: 0 4px 20px rgba(16,185,129,0.6)' : (lives === 0 && !guessedLetters.includes(char.toUpperCase()) ? 'background: #dc2626; border-bottom: 5px solid #f87171; color: #ffffff' : 'background: rgba(255,255,255,0.18); border-bottom: 5px solid rgba(255,255,255,0.4); color: #ffffff'))"
                                     x-text="char === ' ' ? '' : (guessedLetters.includes(char.toUpperCase()) || lives === 0 ? char.toUpperCase() : '?')"
                                ></div>
                            </template>
                        </div>

                        {{-- Keyboard — QWERTY layout dengan tombol besar & jelas --}}
                        <div class="w-full max-w-2xl">
                            {{-- Row 1: QWERTYUIOP (10 huruf) --}}
                            <div class="flex justify-center gap-2 sm:gap-3 mb-2 sm:mb-3">
                                <template x-for="letter in 'QWERTYUIOP'.split('')" :key="letter">
                                    <button @click="guessLetter(letter)" :disabled="guessedLetters.includes(letter) || isCorrect || lives === 0"
                                        class="keyboard-key rounded-xl font-black flex items-center justify-center select-none shadow-md transition-all active:scale-95 hover:-translate-y-1"
                                        style="width: 9.2%; max-width: 60px; min-width: 32px; height: 64px; font-size: 1.4rem"
                                        :style="guessedLetters.includes(letter) ? ((currentGuess.word || '').toUpperCase().includes(letter) ? 'background: #059669; color: white; border: 2px solid #34d399; box-shadow: 0 0 20px rgba(16,185,129,0.5)' : 'background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.25); border: 2px solid rgba(255,255,255,0.08)') : 'background: #334155; color: #ffffff; border: 2px solid #64748b; cursor: pointer; box-shadow: 0 4px 0 #1e293b;'"
                                        x-text="letter">
                                    </button>
                                </template>
                            </div>
                            {{-- Row 2: ASDFGHJKL (9 huruf) --}}
                            <div class="flex justify-center gap-2 sm:gap-3 mb-2 sm:mb-3">
                                <template x-for="letter in 'ASDFGHJKL'.split('')" :key="letter">
                                    <button @click="guessLetter(letter)" :disabled="guessedLetters.includes(letter) || isCorrect || lives === 0"
                                        class="keyboard-key rounded-xl font-black flex items-center justify-center select-none shadow-md transition-all active:scale-95 hover:-translate-y-1"
                                        style="width: 9.2%; max-width: 60px; min-width: 32px; height: 64px; font-size: 1.4rem"
                                        :style="guessedLetters.includes(letter) ? ((currentGuess.word || '').toUpperCase().includes(letter) ? 'background: #059669; color: white; border: 2px solid #34d399; box-shadow: 0 0 20px rgba(16,185,129,0.5)' : 'background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.25); border: 2px solid rgba(255,255,255,0.08)') : 'background: #334155; color: #ffffff; border: 2px solid #64748b; cursor: pointer; box-shadow: 0 4px 0 #1e293b;'"
                                        x-text="letter">
                                    </button>
                                </template>
                            </div>
                            {{-- Row 3: ZXCVBNM (7 huruf) --}}
                            <div class="flex justify-center gap-2 sm:gap-3">
                                <template x-for="letter in 'ZXCVBNM'.split('')" :key="letter">
                                    <button @click="guessLetter(letter)" :disabled="guessedLetters.includes(letter) || isCorrect || lives === 0"
                                        class="keyboard-key rounded-xl font-black flex items-center justify-center select-none shadow-md transition-all active:scale-95 hover:-translate-y-1"
                                        style="width: 9.2%; max-width: 60px; min-width: 32px; height: 64px; font-size: 1.4rem"
                                        :style="guessedLetters.includes(letter) ? ((currentGuess.word || '').toUpperCase().includes(letter) ? 'background: #059669; color: white; border: 2px solid #34d399; box-shadow: 0 0 20px rgba(16,185,129,0.5)' : 'background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.25); border: 2px solid rgba(255,255,255,0.08)') : 'background: #334155; color: #ffffff; border: 2px solid #64748b; cursor: pointer; box-shadow: 0 4px 0 #1e293b;'"
                                        x-text="letter">
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Result popup --}}
                        <div x-show="isCorrect || lives === 0" class="mt-8 w-full p-6 rounded-3xl text-center score-pop" :style="isCorrect ? 'background: rgba(16,185,129,0.25); border: 2px solid rgba(16,185,129,0.5)' : 'background: rgba(239,68,68,0.25); border: 2px solid rgba(239,68,68,0.5)'">
                            <div class="text-5xl mb-3" x-text="isCorrect ? '🎉' : '💀'"></div>
                            <h3 class="text-2xl font-black mb-1" :class="isCorrect ? 'text-emerald-300' : 'text-rose-300'" x-text="isCorrect ? 'Tepat Sekali! 🎊' : 'Sayang Sekali...'"></h3>
                            <p x-show="!isCorrect" class="text-white/80 text-sm mb-4">Kata yang benar: <strong class="text-white font-black text-base" x-text="currentGuess.word"></strong></p>
                            <button @click="nextGuess()" class="px-8 py-3 rounded-2xl font-black text-white transition-all hover:scale-105" :style="isCorrect ? 'background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 0 25px rgba(16,185,129,0.5)' : 'background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 0 25px rgba(245,158,11,0.4)'">
                                Kata Berikutnya <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ══════════════════════════════ --}}
                    {{-- 7. SCRAMBLE PLAYER --}}
                    {{-- ══════════════════════════════ --}}
                    <div x-show="game.type === 'scramble' && !loading && !completed" class="w-full max-w-3xl mx-auto flex flex-col items-center" style="display: none;">
                        {{-- Cara Bermain --}}
                        <div class="w-full mb-6 p-4 rounded-2xl flex items-start gap-3" style="background: rgba(249,115,22,0.2); border: 1px solid rgba(249,115,22,0.4)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(249,115,22,0.4)">
                                <i class="fas fa-info-circle text-orange-300 text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] font-black uppercase tracking-widest text-orange-300 mb-0.5">CARA BERMAIN</div>
                                <div class="font-bold text-white text-sm leading-snug" style="color: #ffffff !important;">Klik huruf-huruf yang teracak untuk menyusunnya menjadi sebuah kata yang benar!</div>
                            </div>
                        </div>

                        {{-- Top bar: Hint --}}
                        <div class="w-full mb-8 flex items-center gap-3 p-5 rounded-2xl" style="background: rgba(30, 27, 75, 0.7); border: 2px solid rgba(249, 115, 22, 0.4)">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 shadow-inner" style="background: rgba(249,115,22,0.4)">
                                <i class="fas fa-lightbulb text-orange-300 text-2xl"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-orange-300 mb-1">💡 PETUNJUK</div>
                                <div class="font-extrabold text-white text-lg sm:text-xl leading-tight" style="color: #ffffff !important; text-shadow: 0 2px 4px rgba(0,0,0,0.5)" x-text="currentScramble.hint || 'Susun huruf menjadi kata yang benar!'"></div>
                            </div>
                        </div>

                        {{-- Selected Letters (Answer Box) --}}
                        <div class="w-full mb-8 p-8 rounded-3xl" style="background: rgba(255,255,255,0.08); border: 2px dashed rgba(255,255,255,0.3); min-height: 140px">
                            <div class="text-[12px] font-black uppercase tracking-widest text-white mb-6 text-center shadow-sm" style="color: #ffffff !important;">J A W A B A N  K A M U</div>
                            <div class="flex flex-wrap justify-center gap-3">
                                <template x-for="(letter, idx) in selectedLetters" :key="letter.id">
                                    <button @click="undoScrambleLetter(letter)" 
                                            class="rounded-2xl flex items-center justify-center font-black uppercase transition-all hover:-translate-y-2 hover:shadow-xl"
                                            style="width: 56px; height: 68px; font-size: 1.8rem; background: rgba(249,115,22,0.9); color: white; border-bottom: 6px solid #c2410c; box-shadow: 0 5px 15px rgba(249,115,22,0.4)"
                                            x-text="letter.char">
                                    </button>
                                </template>
                                {{-- Empty placeholders --}}
                                <template x-for="i in Math.max(0, (currentScramble.word || '').length - selectedLetters.length)">
                                    <div class="rounded-2xl border-4 border-dashed border-white/30" style="width: 56px; height: 68px"></div>
                                </template>
                            </div>
                        </div>

                        {{-- Scrambled Letters (Choices) --}}
                        <div class="w-full flex flex-wrap justify-center gap-4 mb-8">
                            <template x-for="letter in scrambledLetters" :key="letter.id">
                                <button @click="selectScrambleLetter(letter)" :disabled="letter.used || isCorrect"
                                        class="rounded-2xl flex items-center justify-center font-black uppercase transition-all hover:-translate-y-2 hover:shadow-xl active:scale-95"
                                        style="width: 64px; height: 74px; font-size: 2rem"
                                        :style="letter.used ? 'background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.08); cursor: default; transform: scale(0.95)' : 'background: #ffffff; color: #3730a3; border-bottom: 6px solid #818cf8; cursor: pointer; box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.3)'"
                                        x-text="letter.char">
                                </button>
                            </template>
                        </div>

                        {{-- Result popup --}}
                        <div x-show="isCorrect" class="mt-4 w-full p-6 rounded-3xl text-center score-pop" style="background: rgba(16,185,129,0.25); border: 2px solid rgba(16,185,129,0.5)">
                            <div class="text-5xl mb-3">🎉</div>
                            <h3 class="text-2xl font-black mb-1 text-emerald-300">Tepat Sekali! 🎊</h3>
                            <button @click="nextScramble()" class="mt-4 px-8 py-3 rounded-2xl font-black text-white transition-all hover:scale-105" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 0 25px rgba(16,185,129,0.5)">
                                Lanjut <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ══════════════════════════════ --}}
                    {{-- 8. SEQUENCE PLAYER --}}
                    {{-- ══════════════════════════════ --}}
                    <div x-show="game.type === 'sequence' && !loading && !completed" class="w-full max-w-3xl mx-auto flex flex-col items-center" style="display: none;">
                        {{-- Counter + Progress if totalItems > 1 --}}
                        <div x-show="totalItems > 1" class="mb-4 w-full flex items-center justify-between">
                            <span class="text-cyan-400 text-xs font-bold uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-layer-group"></i> Kelompok Urutan
                            </span>
                            <span class="text-white font-black text-sm"><span x-text="currentIndex + 1"></span> / <span x-text="totalItems"></span></span>
                        </div>

                        <div class="w-full mb-6 p-4 rounded-2xl text-center" style="background: rgba(6,182,212,0.12); border: 1px solid rgba(6,182,212,0.3)">
                            <h2 class="text-xl sm:text-2xl font-black mb-1" style="color: #22d3ee; text-shadow: 0 1px 3px rgba(0,0,0,0.5);" x-text="currentSequenceGroupTitle || 'Susun Sesuai Urutan!'"></h2>
                            <p class="text-sm font-bold" style="color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">Geser (Drag & Drop) atau gunakan panah atas/bawah pada kotak-kotak di bawah ini ke urutan yang benar dari atas ke bawah.</p>
                        </div>

                        <div class="w-full max-w-xl mb-6 relative p-4 rounded-3xl" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1)">
                            <div class="space-y-3 min-h-[250px]" x-sort="handleSequenceSort">
                                <template x-for="(item, idx) in shuffledSequence" :key="item.id">
                                    <div x-sort:item="item.id" class="w-full p-3 sm:p-4 rounded-2xl font-bold text-sm transition-all flex items-center gap-2 sm:gap-4 cursor-grab active:cursor-grabbing hover:scale-[1.01] bg-white group" style="color: #0f172a; box-shadow: 0 4px 15px rgba(0,0,0,0.1)">
                                        <div class="flex flex-col items-center gap-1">
                                            <button @click.stop="moveSequenceUp(idx)" :disabled="idx === 0" class="text-gray-400 hover:text-cyan-500 disabled:opacity-30 disabled:cursor-not-allowed px-2 py-1"><i class="fas fa-chevron-up"></i></button>
                                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center font-black text-xs" style="background: rgba(6,182,212,0.15); color: #06b6d4" x-text="idx + 1"></div>
                                            <button @click.stop="moveSequenceDown(idx)" :disabled="idx === shuffledSequence.length - 1" class="text-gray-400 hover:text-cyan-500 disabled:opacity-30 disabled:cursor-not-allowed px-2 py-1"><i class="fas fa-chevron-down"></i></button>
                                        </div>
                                        <div class="w-px h-10 bg-gray-200 hidden sm:block"></div>
                                        <span class="flex-1 px-2" x-text="item.item"></span>
                                        <div class="shrink-0 flex items-center justify-center text-gray-300 group-hover:text-cyan-500 transition-colors ml-auto sm:ml-0" x-sort:handle>
                                            <i class="fas fa-grip-vertical text-lg sm:text-xl p-2 cursor-grab"></i>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="flex gap-4 items-center">
                            <button @click="checkSequence()" :disabled="isCorrect" class="px-8 py-3 rounded-2xl font-black text-white text-base transition-all hover:scale-105 hover:shadow-[0_0_25px_#06b6d4] disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2" style="background: linear-gradient(135deg, #0891b2, #06b6d4);">
                                <i class="fas fa-check-circle"></i> <span x-text="isCorrect ? 'Urutan Benar!' : 'Periksa Urutan'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- GAME TYPE: IMAGE HOTSPOT --}}
                    <div x-show="game.type === 'image_hotspot' && !loading && !completed" class="w-full max-w-4xl mx-auto flex flex-col items-center" style="display: none;">
                        <div class="w-full mb-6 p-4 rounded-2xl text-center" style="background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3)">
                            <h2 class="text-xl sm:text-2xl font-black mb-1 text-emerald-400" style="text-shadow: 0 1px 3px rgba(0,0,0,0.5);">Titik Buta</h2>
                            <p class="text-sm font-bold text-white mb-2">Cari dan sentuh area: <span class="text-yellow-300 text-lg uppercase ml-1" x-text="currentHotspot?.label"></span></p>
                        </div>
                        <div class="relative w-full max-w-3xl overflow-hidden rounded-xl cursor-crosshair bg-white/5 border border-white/10" @click="checkHotspot($event)">
                            <img :src="game.data.image_url" class="w-full h-auto object-contain pointer-events-none select-none">
                            <template x-for="h in foundHotspots" :key="h.label">
                                <div class="absolute w-8 h-8 -ml-4 -mt-4 bg-emerald-500 rounded-full opacity-80 flex items-center justify-center text-white font-bold pointer-events-none shadow-[0_0_15px_#10b981]" :style="'left: '+h.x+'%; top: '+h.y+'%;'">
                                    <i class="fas fa-check"></i>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- GAME TYPE: CHEMISTRY BALANCER --}}
                    <div x-show="game.type === 'chem_balancer' && !loading && !completed" class="w-full max-w-4xl mx-auto flex flex-col items-center" style="display: none;">
                        <div class="w-full mb-6 p-4 rounded-2xl text-center" style="background: rgba(14,165,233,0.12); border: 1px solid rgba(14,165,233,0.3)">
                            <h2 class="text-xl sm:text-2xl font-black mb-1 text-sky-400" style="text-shadow: 0 1px 3px rgba(0,0,0,0.5);">Reaksi Kimia</h2>
                            <p class="text-sm font-bold text-white mb-2">Seimbangkan persamaan reaksi berikut ini dengan mengisi koefisien angka yang tepat.</p>
                        </div>
                        <div class="w-full p-8 rounded-3xl bg-white/5 border border-white/10 text-center flex flex-col items-center">
                            <div class="flex items-center gap-2 flex-wrap justify-center mb-12 font-mono text-3xl font-bold">
                                <template x-for="(part, i) in parsedChemEquation" :key="i">
                                    <div class="flex items-center">
                                        <template x-if="part.isInput">
                                            <input type="number" min="1" max="99" x-model="chemInputs[part.index]" class="w-20 text-center text-slate-900 rounded-xl p-3 font-black mx-2 focus:ring-4 focus:ring-sky-500 outline-none">
                                        </template>
                                        <template x-if="!part.isInput">
                                            <span class="text-white mx-1 text-4xl" x-text="part.text"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <button @click="checkChemEquation()" class="px-10 py-4 rounded-2xl font-black text-white text-xl transition-all hover:scale-105 hover:shadow-[0_0_20px_#0ea5e9]" style="background: linear-gradient(135deg, #0284c7, #0ea5e9);">
                                <i class="fas fa-flask mr-2"></i> Periksa Reaksi
                            </button>
                        </div>
                    </div>

                    {{-- GAME TYPE: MATH NINJA --}}
                    <div x-show="game.type === 'math_ninja' && !loading && !completed" class="w-full max-w-3xl mx-auto flex flex-col items-center relative" style="height: 60vh; display: none;">
                        <div class="absolute inset-0 border-b-4 border-slate-700 bg-slate-900/50 rounded-t-xl overflow-hidden shadow-inner">
                            <!-- Falling Box -->
                            <div class="absolute w-full flex justify-center transition-all duration-100 ease-linear" :style="'top: '+mathBoxTop+'%;'">
                                <div class="px-8 py-6 bg-purple-600 rounded-2xl font-black text-5xl shadow-[0_0_30px_#9333ea] border-2 border-purple-400" x-text="currentMathEquation"></div>
                            </div>
                            <!-- Fire/Laser effect when correct -->
                            <div x-show="mathHitEffect" x-transition.opacity.duration.300ms class="absolute inset-0 bg-green-500/30 flex items-center justify-center z-10 pointer-events-none">
                                <i class="fas fa-bolt text-9xl text-yellow-400 opacity-80" style="filter: blur(4px)"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-0 w-full p-6 bg-slate-800 rounded-b-xl flex gap-4 border-t border-slate-700 shadow-xl">
                            <input type="number" x-model="mathInput" @keyup.enter="checkMathNinja()" x-ref="mathInputRef" class="flex-1 rounded-xl p-4 text-3xl font-black text-center text-slate-900 outline-none focus:ring-4 focus:ring-purple-500" placeholder="Ketik jawaban...">
                            <button @click="checkMathNinja()" class="px-10 py-4 bg-green-500 rounded-xl font-black text-white text-2xl shadow-[0_0_15px_#22c55e] hover:bg-green-400 hover:scale-105 transition-all">SERANG</button>
                        </div>
                    </div>
                    <div x-show="completed" class="text-center w-full max-w-lg mx-auto" style="display: none;">
                        {{-- Trophy animation --}}
                        <div class="relative w-36 h-36 mx-auto mb-8">
                            <div class="absolute inset-0 rounded-full" style="background: radial-gradient(circle, rgba(251,191,36,0.3), transparent 70%); animation: pulse-ring 2s ease-out infinite"></div>
                            <div class="absolute inset-0 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, rgba(251,191,36,0.2), rgba(245,158,11,0.3)); border: 2px solid rgba(251,191,36,0.4)">
                                <span class="text-7xl">🏆</span>
                            </div>
                            <div class="absolute -top-3 -right-2 text-3xl animate-bounce">✨</div>
                            <div class="absolute -bottom-2 -left-3 text-2xl animate-bounce" style="animation-delay:0.3s">🎉</div>
                            <div class="absolute top-2 -left-4 text-xl animate-bounce" style="animation-delay:0.6s">⭐</div>
                        </div>

                        <h2 class="text-4xl sm:text-5xl font-black text-white mb-3" style="text-shadow: 0 0 30px rgba(255,255,255,0.3)" x-text="alreadyDone ? 'Sudah Selesai!' : 'Luar Biasa!'"></h2>
                        <p class="text-white/60 mb-6 text-lg">Kamu berhasil menyelesaikan <span x-text="game.title" class="font-black text-violet-300"></span></p>

                        {{-- Already done notice --}}
                        <div x-show="alreadyDone" class="mb-6 px-5 py-3 rounded-2xl text-sm font-bold text-amber-300" style="background: rgba(251,191,36,0.12); border: 1px solid rgba(251,191,36,0.3)">
                            <i class="fas fa-info-circle mr-1"></i> Kamu sudah pernah menyelesaikan game ini. EXP tidak dihitung ulang.
                        </div>

                        {{-- Score breakdown for quiz-type games --}}
                        <div x-show="totalAnswered > 0" class="flex items-center justify-center gap-4 mb-6">
                            <div class="text-center px-5 py-3 rounded-2xl" style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3)">
                                <div class="text-2xl font-black text-emerald-400" x-text="correctAnswers"></div>
                                <div class="text-[9px] font-black uppercase tracking-widest text-emerald-400/70">Benar</div>
                            </div>
                            <div class="text-white/30 text-2xl font-black">/</div>
                            <div class="text-center px-5 py-3 rounded-2xl" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1)">
                                <div class="text-2xl font-black text-white" x-text="totalAnswered"></div>
                                <div class="text-[9px] font-black uppercase tracking-widest text-white/40">Total Soal</div>
                            </div>
                            <div class="text-white/30 text-2xl font-black">=</div>
                            <div class="text-center px-5 py-3 rounded-2xl" style="background: rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.4)">
                                <div class="text-2xl font-black text-violet-300" x-text="totalAnswered > 0 ? Math.round(correctAnswers/totalAnswered*100) + '%' : '—'"></div>
                                <div class="text-[9px] font-black uppercase tracking-widest text-violet-400/70">Akurasi</div>
                            </div>
                        </div>

                        {{-- EXP Reward Card --}}
                        <div class="inline-block p-6 sm:p-8 rounded-3xl mb-8 relative overflow-hidden score-pop" style="background: linear-gradient(135deg, #4c1d95, #7c3aed, #5b21b6); border: 1px solid rgba(139,92,246,0.5); box-shadow: 0 0 60px rgba(124,58,237,0.5)">
                            <div class="absolute inset-0" style="background: radial-gradient(ellipse at top right, rgba(236,72,153,0.3), transparent 60%)"></div>
                            <div class="relative z-10">
                                <p class="text-violet-300 text-[10px] font-black uppercase tracking-[0.3em] mb-2" x-text="alreadyDone ? 'EXP SEBELUMNYA' : 'EXP DIPEROLEH'"></p>
                                <div class="flex items-center justify-center gap-3">
                                    <span class="text-5xl sm:text-6xl font-black text-white">+<span x-text="earnedExp"></span></span>
                                    <span class="text-3xl font-black text-yellow-300" style="filter: drop-shadow(0 0 8px gold)">EXP</span>
                                </div>
                                <p x-show="totalAnswered > 0 && !alreadyDone" class="text-violet-300/70 text-xs mt-2 font-bold">
                                    dari maks. <span x-text="game.reward"></span> EXP
                                </p>
                            </div>
                        </div>

                        <div>
                            <button @click="closeGameAndRefresh()" class="px-8 py-4 rounded-2xl font-black text-white/80 transition-all hover:text-white hover:scale-105 text-sm uppercase tracking-widest" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15)">
                                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Modul
                            </button>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/sort@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/alpinejs@3/dist/cdn.min.js" defer></script>
<script>
function gamePlayer() {
    return {
        open: false,
        loading: false,
        completed: false,
        game: {
            id: null,
            title: '',
            type: '',
            data: {},
            reward: 0
        },
        
        // Flashcard State
        isFlipped: false,
        currentIndex: 0,
        spinResult: null,
        
        // Match State
        matchItems: { terms: [], definitions: [] },
        selectedTerm: null,
        selectedDef: null,
        matchedPairs: [],
        
        // Wheel State
        wheelRotation: 0,
        wheelSpinning: false,

        // Quiz & TF State
        selectedAnswer: null,
        isCorrect: false,
        correctAnswers: 0,   // running count of correct answers
        totalAnswered: 0,    // running count of answered questions
        earnedExp: 0,        // actual EXP received from server
        alreadyDone: false,  // true if student already completed this game before
        
        // Hardcore Mode State
        timeLimit: null,
        timeRemaining: 0,
        timerInterval: null,
        maxLives: null,
        lives: 5,
        currentCombo: 0,
        comboBonusPoints: 0,
        showComboEffect: false,
        isGameOver: false,
        
        // Word Guess State
        guessedLetters: [],

        // Scramble State
        scrambledLetters: [],
        selectedLetters: [],
        
        // Sequence State
        sequenceGroups: [],
        currentSequenceGroupTitle: '',
        shuffledSequence: [],
        userSequence: [],
        
        get totalItems() {
            if (this.game.type === 'flashcard' || this.game.type === 'match') return this.game.data.pairs?.length || 0;
            if (this.game.type === 'quiz') return this.game.data.questions?.length || 0;
            if (this.game.type === 'true_false') return this.game.data.statements?.length || 0;
            if (this.game.type === 'word_guess') return this.game.data.words?.length || 0;
            if (this.game.type === 'scramble') return this.game.data.words?.length || 0;
            if (this.game.type === 'sequence') {
                if (this.sequenceGroups && this.sequenceGroups.length > 0) return this.sequenceGroups.length;
                return 1;
            }
            return this.game.data.items?.length || 0;
        },
        
        get currentFlashcard() {
            if (this.game.type !== 'flashcard' || !this.game.data.pairs) return {};
            return this.game.data.pairs[this.currentIndex] || {};
        },
        get currentQuiz() {
            if (this.game.type !== 'quiz' || !this.game.data.questions) return {};
            return this.game.data.questions[this.currentIndex] || {};
        },
        get currentTf() {
            if (this.game.type !== 'true_false' || !this.game.data.statements) return {};
            return this.game.data.statements[this.currentIndex] || {};
        },
        get currentGuess() {
            if (this.game.type !== 'word_guess' || !this.game.data.words) return {};
            return this.game.data.words[this.currentIndex] || {};
        },
        get currentScramble() {
            if (this.game.type !== 'scramble' || !this.game.data.words) return {};
            return this.game.data.words[this.currentIndex] || {};
        },
        loadGame(detail) {
            this.game = detail;
            this.open = true;
            this.completed = false;
            this.loading = true;
            
            // Reset states
            this.isFlipped = false;
            this.currentIndex = 0;
            this.selectedTerm = null;
            this.selectedDef = null;
            this.matchedPairs = [];
            this.wheelRotation = 0;
            this.wheelSpinning = false;
            this.spinResult = null;
            this.selectedAnswer = null;
            this.isCorrect = false;
            this.guessedLetters = [];
            this.lives = this.game.lives_count ? this.game.lives_count : (this.game.type === 'word_guess' ? 5 : null);
            this.maxLives = this.lives;
            this.timeLimit = this.game.time_limit ? this.game.time_limit : null;
            this.timeRemaining = 0;
            if(this.timerInterval) clearInterval(this.timerInterval);
            this.currentCombo = 0;
            this.comboBonusPoints = 0;
            this.showComboEffect = false;
            this.isGameOver = false;
            this.scrambledLetters = [];
            this.selectedLetters = [];
            this.sequenceGroups = [];
            this.currentSequenceGroupTitle = '';
            this.shuffledSequence = [];
            this.userSequence = [];
            this.correctAnswers = 0;
            this.totalAnswered = 0;
            this.earnedExp = 0;
            this.alreadyDone = false;
            
            // STEM Reset
            this.currentHotspot = null;
            this.foundHotspots = [];
            this.parsedChemEquation = [];
            this.chemInputs = [];
            this.currentMathEquation = '';
            this.mathAnswer = null;
            this.mathInput = '';
            this.mathBoxTop = 0;
            this.mathHitEffect = false;
            if(this.mathInterval) clearInterval(this.mathInterval);
            
            setTimeout(() => {
                this.initGameMode();
                this.loading = false;
            }, 600);
        },
        
        initGameMode() {
            if (this.game.type === 'match' && this.game.data.pairs) {
                // Shuffle logic
                let pairs = [...this.game.data.pairs].map((p, i) => ({...p, id: i}));
                
                let terms = pairs.map(p => ({id: p.id, text: p.term}));
                let defs = pairs.map(p => ({id: p.id, text: p.definition}));
                
                // Fisher-Yates shuffle
                for (let i = terms.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [terms[i], terms[j]] = [terms[j], terms[i]];
                }
                for (let i = defs.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [defs[i], defs[j]] = [defs[j], defs[i]];
                }
                
                this.matchItems = { terms, definitions: defs };
            }
            if (this.game.type === 'scramble') {
                this.initScramble();
            }
            if (this.game.type === 'sequence') {
                let gd = this.game.data || {};
                if (gd.groups && gd.groups.length > 0) {
                    this.sequenceGroups = JSON.parse(JSON.stringify(gd.groups));
                } else if (gd.items && gd.items.length > 0) {
                    this.sequenceGroups = [{ title: 'Kelompok 1', items: JSON.parse(JSON.stringify(gd.items)) }];
                } else {
                    this.sequenceGroups = [];
                }
                this.currentIndex = 0;
                this.initSequenceGroup(0);
            }
            if (this.game.type === 'image_hotspot' && this.game.data.hotspots) {
                this.currentIndex = 0;
                this.currentHotspot = this.game.data.hotspots[0];
                this.foundHotspots = [];
            }
            if (this.game.type === 'chem_balancer' && this.game.data.equations) {
                this.initChemEquation();
            }
            if (this.game.type === 'math_ninja') {
                this.startMathNinja();
            }
            
            // Start Hardcore Timer for supported games
            if (['quiz', 'true_false', 'word_guess', 'scramble', 'sequence', 'image_hotspot', 'chem_balancer'].includes(this.game.type)) {
                this.startTimer();
            }
        },
        
        closeGame() {
            if (this.completed) {
                this.closeGameAndRefresh();
            } else {
                if (confirm('Yakin ingin keluar? Progres belum tersimpan.')) {
                    this.open = false;
                }
            }
        },
        
        closeGameAndRefresh() {
            this.open = false;
            window.location.reload();
        },
        
        // --- Hardcore Mode Logic ---
        startTimer() {
            if (this.timeLimit && this.timeLimit > 0) {
                this.timeRemaining = this.timeLimit;
                if(this.timerInterval) clearInterval(this.timerInterval);
                this.timerInterval = setInterval(() => {
                    if (this.isCorrect || this.isGameOver || this.completed) return;
                    
                    this.timeRemaining--;
                    if (this.timeRemaining <= 0) {
                        clearInterval(this.timerInterval);
                        this.handleTimeOut();
                    }
                }, 1000);
            }
        },
        
        handleTimeOut() {
            if (this.game.type === 'quiz' || this.game.type === 'true_false') {
                this.selectedAnswer = -1; // Dummy incorrect
                this.checkAnswer(false);
            } else if (this.game.type === 'word_guess') {
                this.checkLife(true); // force loose a life, if game over it stops, else next word
                if(!this.isGameOver) { this.isCorrect = true; setTimeout(() => { this.nextGuess(); }, 1500); }
            } else if (this.game.type === 'scramble') {
                this.checkLife(true);
                if(!this.isGameOver) { this.isCorrect = true; setTimeout(() => { this.nextScramble(); }, 1500); }
            } else if (this.game.type === 'sequence') {
                this.checkLife(true);
                if(!this.isGameOver) { 
                    this.isCorrect = true; 
                    if (this.currentIndex < this.totalItems - 1) {
                        setTimeout(() => { 
                            this.currentIndex++; 
                            this.initSequenceGroup(this.currentIndex);
                            this.startTimer();
                        }, 1500);
                    } else {
                        setTimeout(() => { this.finishGame(); }, 1500);
                    }
                }
            }
        },
        
        checkLife(isWrong = false) {
            if (isWrong) {
                this.currentCombo = 0; // reset combo
                if (this.maxLives !== null) {
                    this.lives--;
                    if (this.lives <= 0) {
                        this.isGameOver = true;
                        this.finishGame();
                        return false;
                    }
                }
            } else {
                this.currentCombo++;
                if (this.currentCombo >= 3) {
                    this.showComboEffect = true;
                    this.comboBonusPoints += (this.currentCombo * 5); // bonus points
                    setTimeout(() => { this.showComboEffect = false; }, 2000);
                }
            }
            return true;
        },

        
        // --- Flashcard Logic ---
        flipCard() {
            this.isFlipped = !this.isFlipped;
        },
        nextCard() {
            if (this.currentIndex < this.totalItems - 1) {
                this.isFlipped = false;
                setTimeout(() => { this.currentIndex++; }, 200);
            }
        },
        prevCard() {
            if (this.currentIndex > 0) {
                this.isFlipped = false;
                setTimeout(() => { this.currentIndex--; }, 200);
            }
        },
        answerFlashcard(isKnown) {
            this.totalAnswered++;
            if(isKnown) this.correctAnswers++;
            
            if (this.currentIndex < this.totalItems - 1) {
                this.isFlipped = false;
                setTimeout(() => { this.currentIndex++; }, 200);
            } else {
                this.finishGame();
            }
        },
        
        // --- Match Logic ---
        selectMatchItem(type, item) {
            if (type === 'term') this.selectedTerm = item;
            if (type === 'definition') this.selectedDef = item;
            
            this.checkMatch();
        },
        checkMatch() {
            if (this.selectedTerm && this.selectedDef) {
                if (this.selectedTerm.id === this.selectedDef.id) {
                    // Match!
                    this.matchedPairs.push(this.selectedTerm.id);
                    spawnConfetti();
                    
                    if (this.matchedPairs.length === this.totalItems) {
                        setTimeout(() => { this.finishGame(); }, 800);
                    }
                }
                
                // Reset selection
                setTimeout(() => {
                    this.selectedTerm = null;
                    this.selectedDef = null;
                }, 400);
            }
        },
        
        // --- Spin Wheel Logic ---
        getWheelColor(index) {
            const colors = ['#f43f5e', '#ec4899', '#d946ef', '#a855f7', '#8b5cf6', '#6366f1', '#3b82f6'];
            return colors[index % colors.length];
        },
        spinWheel() {
            if (this.wheelSpinning) return;
            this.wheelSpinning = true;
            
            // Random spins between 5 and 10 full rotations + random angle
            const spins = 5 + Math.floor(Math.random() * 5);
            const extraAngle = Math.floor(Math.random() * 360);
            const totalRotation = this.wheelRotation + (spins * 360) + extraAngle;
            
            this.wheelRotation = totalRotation;
            
            setTimeout(() => {
                this.wheelSpinning = false;
                spawnConfetti();
                // Hitung item mana yang menang
                let normalizedAngle = totalRotation % 360;
                let itemsCount = Math.max(1, (this.game.data.items || []).length);
                let anglePerItem = 360 / itemsCount;
                // Pointer di atas (0 derajat) tapi rotasi roda counter-clockwise relative to pointer
                let winningIndex = Math.floor((360 - normalizedAngle + (anglePerItem/2)) % 360 / anglePerItem);
                this.spinResult = this.game.data.items[winningIndex] || "Hadiah Misteri";
            }, 4000);
        },

        // --- Quiz Logic ---
        answerQuiz(idx) {
            if (this.selectedAnswer !== null || this.isGameOver) return;
            this.selectedAnswer = idx;
            this.isCorrect = (idx === parseInt(this.currentQuiz.answer));
            this.totalAnswered++;
            if (this.isCorrect) { 
                this.correctAnswers++; 
                this.checkLife(false);
                spawnConfetti(); 
            } else {
                this.checkLife(true);
            }
        },
        nextQuiz() {
            if (this.currentIndex < this.totalItems - 1) {
                this.selectedAnswer = null;
                this.isCorrect = false;
                this.currentIndex++;
                this.startTimer();
            } else {
                setTimeout(() => { this.finishGame(); }, 500);
            }
        },
        
        // --- True/False Logic ---
        answerTf(val) {
            if (this.selectedAnswer !== null || this.isGameOver) return;
            this.selectedAnswer = val;
            let correctVal = (this.currentTf.is_true === 'true' || this.currentTf.is_true === true);
            this.isCorrect = (val === correctVal);
            this.totalAnswered++;
            if (this.isCorrect) { 
                this.correctAnswers++; 
                this.checkLife(false);
                spawnConfetti(); 
            } else {
                this.checkLife(true);
            }
        },
        nextTf() {
            if (this.currentIndex < this.totalItems - 1) {
                this.selectedAnswer = null;
                this.isCorrect = false;
                this.currentIndex++;
                this.startTimer();
            } else {
                setTimeout(() => { this.finishGame(); }, 500);
            }
        },
        
        // --- Word Guess Logic ---
        guessLetter(letter) {
            if (this.guessedLetters.includes(letter) || this.isCorrect || this.isGameOver) return;
            
            this.guessedLetters.push(letter);
            const word = (this.currentGuess.word || '').toUpperCase();
            
            if (word.includes(letter)) {
                // Check if won
                const won = word.split('').every(c => c === ' ' || this.guessedLetters.includes(c));
                if (won) {
                    this.isCorrect = true;
                    this.checkLife(false);
                    spawnConfetti();
                }
            } else {
                this.checkLife(true);
            }
        },
        nextGuess() {
            // Track word_guess completion per word
            this.totalAnswered++;
            if (this.isCorrect) this.correctAnswers++;

            if (this.currentIndex < this.totalItems - 1) {
                this.guessedLetters = [];
                if (!this.maxLives) this.lives = 5; // Reset only if not hardcore mode
                this.isCorrect = false;
                this.currentIndex++;
                this.startTimer();
            } else {
                setTimeout(() => { this.finishGame(); }, 500);
            }
        },

        // --- Scramble Logic ---
        initScramble() {
            const word = (this.currentScramble.word || '').toUpperCase();
            let chars = word.split('');
            for (let i = chars.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [chars[i], chars[j]] = [chars[j], chars[i]];
            }
            this.scrambledLetters = chars.map((char, i) => ({ id: i, char: char, used: false }));
            this.selectedLetters = [];
            this.isCorrect = false;
        },
        selectScrambleLetter(letterObj) {
            if (letterObj.used || this.isCorrect || this.isGameOver) return;
            letterObj.used = true;
            this.selectedLetters.push(letterObj);
            
            if (this.selectedLetters.length === this.scrambledLetters.length) {
                const formedWord = this.selectedLetters.map(l => l.char).join('');
                if (formedWord === (this.currentScramble.word || '').toUpperCase()) {
                    this.isCorrect = true;
                    this.checkLife(false);
                    spawnConfetti();
                } else {
                    this.checkLife(true);
                    setTimeout(() => {
                        this.selectedLetters = [];
                        this.scrambledLetters.forEach(l => l.used = false);
                    }, 600);
                }
            }
        },
        undoScrambleLetter(letterObj) {
            if (this.isCorrect) return;
            letterObj.used = false;
            this.selectedLetters = this.selectedLetters.filter(l => l.id !== letterObj.id);
        },
        nextScramble() {
            this.totalAnswered++;
            if (this.isCorrect) this.correctAnswers++;
            
            if (this.currentIndex < this.totalItems - 1) {
                this.currentIndex++;
                this.initScramble();
                this.startTimer();
            } else {
                setTimeout(() => { this.finishGame(); }, 500);
            }
        },

        // --- Sequence Logic ---
        initSequenceGroup(groupIndex) {
            if (!this.sequenceGroups[groupIndex]) return;
            let group = this.sequenceGroups[groupIndex];
            let items = [...(group.items || [])].map((p, i) => ({...p, originalIndex: i, id: i}));
            for (let i = items.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [items[i], items[j]] = [items[j], items[i]];
            }
            this.shuffledSequence = items;
            this.currentSequenceGroupTitle = group.title || ('Kelompok ' + (groupIndex + 1));
            this.isCorrect = false;
        },
        moveSequenceUp(idx) {
            if (idx <= 0 || this.isGameOver) return;
            const movedItem = this.shuffledSequence[idx];
            this.shuffledSequence.splice(idx, 1);
            this.shuffledSequence.splice(idx - 1, 0, movedItem);
        },
        moveSequenceDown(idx) {
            if (idx >= this.shuffledSequence.length - 1 || this.isGameOver) return;
            const movedItem = this.shuffledSequence[idx];
            this.shuffledSequence.splice(idx, 1);
            this.shuffledSequence.splice(idx + 1, 0, movedItem);
        },
        handleSequenceSort(itemId, position) {
            // Find the object from its ID
            const item = this.shuffledSequence.find(i => i.id === itemId);
            if(!item) return;
            let index = this.shuffledSequence.indexOf(item);
            this.shuffledSequence.splice(index, 1);
            this.shuffledSequence.splice(position, 0, item);
        },
        checkSequence() {
            if (this.isGameOver || this.isCorrect) return;
            
            let isWin = true;
            for(let i = 0; i < this.shuffledSequence.length; i++) {
                if(this.shuffledSequence[i].originalIndex !== i) {
                    isWin = false; break;
                }
            }
            
            if(isWin) {
                this.isCorrect = true;
                this.checkLife(false);
                spawnConfetti();
                
                if (this.currentIndex < this.totalItems - 1) {
                    this.totalAnswered++;
                    this.correctAnswers++;
                    setTimeout(() => {
                        this.currentIndex++;
                        this.initSequenceGroup(this.currentIndex);
                        this.startTimer();
                    }, 1200);
                } else {
                    this.totalAnswered = this.totalItems;
                    this.correctAnswers = this.totalItems;
                    setTimeout(() => { this.finishGame(); }, 1200);
                }
            } else {
                let alive = this.checkLife(true);
                if(alive) {
                    alert('Urutan masih salah, periksa kembali!');
                }
            }
        },
        
        // --- STEM GAMES LOGIC ---
        checkHotspot(event) {
            if (this.isGameOver || !this.currentHotspot) return;
            const rect = event.currentTarget.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / rect.width) * 100;
            const y = ((event.clientY - rect.top) / rect.height) * 100;
            
            // Check distance (tolerance 8%)
            const dist = Math.sqrt(Math.pow(x - this.currentHotspot.x, 2) + Math.pow(y - this.currentHotspot.y, 2));
            if (dist <= 8) {
                this.foundHotspots.push({x, y, label: this.currentHotspot.label});
                this.correctAnswers++;
                this.totalAnswered++;
                this.currentIndex++;
                this.checkLife(false);
                
                if (this.currentIndex >= this.game.data.hotspots.length) {
                    spawnConfetti();
                    setTimeout(() => { this.finishGame(); }, 1500);
                } else {
                    this.currentHotspot = this.game.data.hotspots[this.currentIndex];
                }
            } else {
                this.checkLife(true);
            }
        },

        initChemEquation() {
            this.currentIndex = 0;
            this.loadChemEquation();
        },
        loadChemEquation() {
            const eqObj = this.game.data.equations[this.currentIndex];
            if (!eqObj) return;
            const parts = eqObj.equation.split('_');
            let parsed = [];
            let inputIdx = 0;
            for(let i=0; i<parts.length; i++){
                if(i > 0) {
                    parsed.push({ isInput: true, index: inputIdx });
                    this.chemInputs[inputIdx] = '';
                    inputIdx++;
                }
                if(parts[i].trim() !== '') {
                    parsed.push({ isInput: false, text: parts[i] });
                }
            }
            this.parsedChemEquation = parsed;
        },
        checkChemEquation() {
            if (this.isGameOver) return;
            const eqObj = this.game.data.equations[this.currentIndex];
            const correctAnswers = eqObj.answers.split(',').map(a => a.trim());
            
            let isCorrect = true;
            for(let i=0; i<correctAnswers.length; i++){
                if(this.chemInputs[i] != correctAnswers[i]){
                    isCorrect = false; break;
                }
            }
            
            if(isCorrect) {
                this.correctAnswers++;
                this.totalAnswered++;
                this.currentIndex++;
                this.checkLife(false);
                if(this.currentIndex >= this.game.data.equations.length) {
                    spawnConfetti();
                    setTimeout(() => { this.finishGame(); }, 1500);
                } else {
                    this.loadChemEquation();
                }
            } else {
                this.checkLife(true);
            }
        },

        startMathNinja() {
            this.currentIndex = 0;
            this.nextMathEquation();
        },
        nextMathEquation() {
            this.mathBoxTop = 0;
            this.mathInput = '';
            
            let num1, num2;
            const config = this.game.data.config;
            const diff = config.difficulty;
            let max = diff === 'easy' ? 10 : (diff === 'medium' ? 50 : 100);
            num1 = Math.floor(Math.random() * max) + 1;
            num2 = Math.floor(Math.random() * max) + 1;
            
            let op = config.operation;
            if(op === 'mixed'){
                const ops = ['add','sub','mul'];
                op = ops[Math.floor(Math.random()*ops.length)];
            }
            
            if(op === 'sub' && num1 < num2) { let temp = num1; num1 = num2; num2 = temp; }
            if(op === 'mul' && diff !== 'easy') { num1 = Math.floor(Math.random() * 20)+1; num2 = Math.floor(Math.random() * 10)+1; }
            
            let eqText = "";
            if(op === 'add'){ eqText = num1 + ' + ' + num2; this.mathAnswer = num1 + num2; }
            else if(op === 'sub'){ eqText = num1 + ' - ' + num2; this.mathAnswer = num1 - num2; }
            else if(op === 'mul'){ eqText = num1 + ' x ' + num2; this.mathAnswer = num1 * num2; }
            
            this.currentMathEquation = eqText;
            
            if(this.mathInterval) clearInterval(this.mathInterval);
            this.mathInterval = setInterval(() => {
                if(this.isGameOver) { clearInterval(this.mathInterval); return; }
                this.mathBoxTop += (diff === 'hard' ? 2 : (diff === 'medium' ? 1.5 : 1));
                if(this.mathBoxTop >= 80) { // Touched bottom zone
                    clearInterval(this.mathInterval);
                    let alive = this.checkLife(true);
                    if(alive) {
                        this.nextMathEquation(); 
                    }
                }
            }, 50); 
            
            setTimeout(() => { if(this.$refs.mathInputRef) this.$refs.mathInputRef.focus(); }, 100);
        },
        checkMathNinja() {
            if (this.isGameOver) return;
            if (parseInt(this.mathInput) === this.mathAnswer) {
                clearInterval(this.mathInterval);
                this.mathHitEffect = true;
                setTimeout(() => { this.mathHitEffect = false; }, 300);
                
                this.correctAnswers++;
                this.totalAnswered++;
                this.currentIndex++;
                this.checkLife(false); 
                
                if (this.currentIndex >= 10) { 
                    spawnConfetti();
                    setTimeout(() => { this.finishGame(); }, 1000);
                } else {
                    setTimeout(() => { this.nextMathEquation(); }, 400);
                }
            } else {
                this.mathInput = '';
                this.checkLife(true);
            }
        },
        
        // --- Completion Logic ---
        finishGame() {
            this.loading = true;
            if(this.timerInterval) clearInterval(this.timerInterval);

            const payload = {
                correct: this.correctAnswers,
                total: this.totalAnswered,
                combo_bonus: this.comboBonusPoints
            };
            
            fetch(`/siswa/lms_games/${this.game.id}/finish`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                this.earnedExp = data.reward_points || 0;
                this.alreadyDone = data.already_done || false;
                this.completed = true;
                if (!this.alreadyDone) {
                    setTimeout(() => { spawnConfetti(); }, 400);
                    setTimeout(() => { spawnConfetti(); }, 900);
                }
            })
            .catch(err => {
                this.loading = false;
                alert('Terjadi kesalahan saat menyimpan progres. Pastikan koneksi internet stabil.');
            });
        }
    }
}

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

function validateLmsFileSize(input) {
    if (input && input.files && input.files[0]) {
        const file = input.files[0];
        const maxBytes = 10 * 1024 * 1024; // 10MB
        if (file.size > maxBytes) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
            alert('⚠️ PERHATIAN: Ukuran berkas "' + file.name + '" (' + sizeMB + ' MB) melebihi batas maksimal 10 MB.\n\nSilakan pilih berkas lain atau kompres berkas Anda terlebih dahulu agar pengiriman berhasil.');
            input.value = '';
            return false;
        }
    }
}

function handleLmsAssignmentSubmit(form) {
    const fileInput = form.querySelector('input[type="file"][name="file"]');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        const maxBytes = 10 * 1024 * 1024;
        if (file.size > maxBytes) {
            alert('⚠️ Berkas terlalu besar (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB). Batas maksimal pengungahan adalah 10 MB.');
            return false;
        }
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengunggah & Mengirim Jawaban... Mohon Tunggu';
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    }
    return true;
}
</script>
@endpush
