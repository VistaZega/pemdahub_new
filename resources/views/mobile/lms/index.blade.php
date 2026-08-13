@extends('mobile.layouts.app')

@section('title', 'LMS PembdaHUB - Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-extrabold text-white">LMS Digital</h2>
            <p class="text-[11px] text-slate-400">Pembelajaran & Materi Sekolah</p>
        </div>
        <a href="{{ route('mobile.lms.catalog') }}" 
           class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-indigo-400 text-xs font-bold hover:text-white transition flex items-center gap-1.5">
            <i class="fa-solid fa-compass"></i> Katalog
        </a>
    </div>

    <!-- Courses Grid/List -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Mata Pelajaran Saya</h3>

        @forelse($enrolledCourses as $course)
            <a href="{{ route('mobile.lms.show', $course->id) }}" class="glass-card rounded-2xl p-4 block hover:border-purple-500/40 transition group">
                <div class="flex items-start space-x-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md group-hover:scale-105 transition">
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <span class="text-[10px] font-bold text-purple-400 uppercase tracking-wide">{{ $course->code }}</span>
                        <h4 class="text-sm font-extrabold text-white truncate leading-tight">{{ $course->course_name }}</h4>
                        <p class="text-xs text-slate-400 truncate mt-0.5"><i class="fa-regular fa-user mr-1"></i>{{ $course->teacher->full_name ?? 'Pengajar' }}</p>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]"><i class="fa-solid fa-layer-group mr-1 text-purple-400"></i>{{ count($course->modules ?? []) }} Modul</span>
                    <span class="text-indigo-400 font-semibold group-hover:translate-x-1 transition flex items-center gap-1">
                        Buka Kelas <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </span>
                </div>
            </a>
        @empty
            <div class="glass-card rounded-2xl p-8 text-center text-slate-500">
                <i class="fa-solid fa-graduation-cap text-3xl mb-2 text-purple-400/50"></i>
                <p class="text-xs font-semibold">Anda belum terdaftar di kelas manapun.</p>
                <a href="{{ route('mobile.lms.catalog') }}" class="inline-block mt-3 px-4 py-2 bg-purple-600 text-white text-xs font-bold rounded-xl shadow-md">Jelajahi Katalog Kelas</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
