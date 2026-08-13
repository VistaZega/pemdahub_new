@extends('mobile.layouts.app')

@section('title', 'Katalog Kelas 3D - LMS Mobile')

@section('content')
<div class="space-y-4">
    <!-- Back Link -->
    <a href="{{ route('mobile.lms.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke LMS Saya
    </a>

    <div>
        <h2 class="text-xl font-black text-slate-900">Katalog Kelas LMS 📚</h2>
        <p class="text-[11px] text-slate-500 font-bold">Pilih & ikuti kelas mata pelajaran yang tersedia</p>
    </div>

    <!-- Course List (Clay Cards) -->
    <div class="space-y-3">
        @forelse($courses as $course)
            <div class="clay-card p-4.5 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[9px] font-black border border-purple-200 uppercase">
                            {{ $course->code }}
                        </span>
                        <h3 class="text-sm font-black text-slate-900 mt-1">{{ $course->course_name }}</h3>
                        <p class="text-xs text-slate-500 font-bold"><i class="fa-regular fa-user mr-1 text-purple-600"></i>{{ $course->teacher->full_name ?? 'Pengajar' }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 text-xs font-black">
                    <span class="text-[11px] text-emerald-600"><i class="fa-solid fa-check-circle mr-1"></i>Aktif</span>
                    <a href="{{ route('mobile.lms.show', $course->id) }}" 
                       class="clay-btn px-4 py-2 text-white text-xs font-black shadow-md">
                        Lihat Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold">
                Belum ada kelas yang tersedia di katalog.
            </div>
        @endforelse

        <div class="pt-2">
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
