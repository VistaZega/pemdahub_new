@extends('mobile.layouts.app')

@section('title', 'LMS Digital 3D - Mobile Pro')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">LMS Digital 📚</h2>
            <p class="text-[11px] text-slate-500 font-bold">Pembelajaran & Materi Sekolah</p>
        </div>
        <a href="{{ route('mobile.lms.catalog') }}" 
           class="px-4 py-2.5 rounded-2xl bg-white border-2 border-purple-200 text-purple-700 text-xs font-black hover:bg-purple-50 transition flex items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-compass text-purple-600"></i> Katalog
        </a>
    </div>

    <!-- Courses Grid/List (Clay Cards) -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Mata Pelajaran Saya</h3>

        @forelse($enrolledCourses as $course)
            <a href="{{ route('mobile.lms.show', $course->id) }}" class="clay-card p-4.5 block hover:border-purple-300 transition group">
                <div class="flex items-start space-x-3.5">
                    <div class="w-13 h-13 rounded-2xl clay-purple flex items-center justify-center text-white font-black text-xl shadow-md group-hover:scale-105 transition">
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <span class="text-[10px] font-black text-purple-600 uppercase tracking-wider bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">{{ $course->code }}</span>
                        <h4 class="text-sm font-black text-slate-900 truncate leading-tight mt-1">{{ $course->course_name }}</h4>
                        <p class="text-xs text-slate-500 truncate mt-0.5 font-bold"><i class="fa-regular fa-user mr-1 text-purple-600"></i>{{ $course->teacher->full_name ?? 'Pengajar' }}</p>
                    </div>
                </div>

                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-black">
                    <span class="text-slate-500 text-[11px]"><i class="fa-solid fa-layer-group mr-1 text-purple-600"></i>{{ count($course->modules ?? []) }} Modul</span>
                    <span class="text-purple-600 font-black group-hover:translate-x-1 transition flex items-center gap-1">
                        Buka Kelas <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </span>
                </div>
            </a>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 font-bold">
                <i class="fa-solid fa-graduation-cap text-4xl mb-2 text-purple-400"></i>
                <p class="text-xs font-black text-slate-700">Anda belum terdaftar di kelas manapun.</p>
                <a href="{{ route('mobile.lms.catalog') }}" class="clay-btn inline-block mt-3 px-5 py-2.5 text-white text-xs font-black shadow-md">Jelajahi Katalog Kelas</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
