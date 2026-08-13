@extends('mobile.layouts.app')

@section('title', 'LMS Digital Pro - Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-black text-slate-900">LMS Digital</h2>
            <p class="text-[11px] text-slate-500 font-medium">Pembelajaran & Materi Sekolah</p>
        </div>
        <a href="{{ route('mobile.lms.catalog') }}" 
           class="px-3.5 py-2 rounded-2xl bg-white border border-slate-200 text-purple-700 text-xs font-black hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-compass text-purple-600"></i> Katalog
        </a>
    </div>

    <!-- Courses Grid/List -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Mata Pelajaran Saya</h3>

        @forelse($enrolledCourses as $course)
            <a href="{{ route('mobile.lms.show', $course->id) }}" class="pro-card rounded-2xl p-4 block hover:border-purple-300 transition group">
                <div class="flex items-start space-x-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-md shadow-purple-500/25 group-hover:scale-105 transition">
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <span class="text-[10px] font-black text-purple-600 uppercase tracking-wide">{{ $course->code }}</span>
                        <h4 class="text-sm font-black text-slate-900 truncate leading-tight">{{ $course->course_name }}</h4>
                        <p class="text-xs text-slate-500 truncate mt-0.5 font-medium"><i class="fa-regular fa-user mr-1 text-purple-600"></i>{{ $course->teacher->full_name ?? 'Pengajar' }}</p>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                    <span class="text-slate-500 text-[11px]"><i class="fa-solid fa-layer-group mr-1 text-purple-600"></i>{{ count($course->modules ?? []) }} Modul</span>
                    <span class="text-purple-600 font-extrabold group-hover:translate-x-1 transition flex items-center gap-1">
                        Buka Kelas <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </span>
                </div>
            </a>
        @empty
            <div class="pro-card rounded-2xl p-8 text-center text-slate-500">
                <i class="fa-solid fa-graduation-cap text-3xl mb-2 text-purple-500"></i>
                <p class="text-xs font-bold text-slate-700">Anda belum terdaftar di kelas manapun.</p>
                <a href="{{ route('mobile.lms.catalog') }}" class="inline-block mt-3 px-4 py-2 bg-purple-600 text-white text-xs font-black rounded-xl shadow-md">Jelajahi Katalog Kelas</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
