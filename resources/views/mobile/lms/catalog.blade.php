@extends('mobile.layouts.app')

@section('title', 'Katalog Kelas - LMS Mobile')

@section('content')
<div class="space-y-4">
    <!-- Back Link -->
    <a href="{{ route('mobile.lms.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke LMS Saya
    </a>

    <div>
        <h2 class="text-lg font-extrabold text-white">Katalog Kelas LMS</h2>
        <p class="text-[11px] text-slate-400">Pilih & ikuti kelas mata pelajaran yang tersedia</p>
    </div>

    <!-- Course List -->
    <div class="space-y-3">
        @forelse($courses as $course)
            <div class="glass-card rounded-2xl p-4 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="px-2 py-0.5 rounded-md bg-purple-500/20 text-purple-300 text-[9px] font-bold border border-purple-500/30">
                            {{ $course->code }}
                        </span>
                        <h3 class="text-sm font-extrabold text-white mt-1">{{ $course->course_name }}</h3>
                        <p class="text-xs text-slate-400"><i class="fa-regular fa-user mr-1"></i>{{ $course->teacher->full_name ?? 'Pengajar' }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-800 text-xs">
                    <span class="text-[11px] text-slate-400"><i class="fa-solid fa-check-circle text-emerald-400 mr-1"></i>Aktif</span>
                    <a href="{{ route('mobile.lms.show', $course->id) }}" 
                       class="px-3.5 py-1.5 rounded-xl bg-purple-600 text-white text-xs font-bold shadow hover:bg-purple-500 transition">
                        Lihat Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-8 text-center text-slate-500 text-xs">
                Belum ada kelas yang tersedia di katalog.
            </div>
        @endforelse

        <div class="pt-2">
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
