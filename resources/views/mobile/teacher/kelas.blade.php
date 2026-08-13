@extends('mobile.layouts.app')

@section('title', 'My Class - Kelas Saya Guru Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title -->
    <div>
        <h2 class="text-xl font-black text-slate-900">Kelas Saya (My Class) 🏫</h2>
        <p class="text-[11px] text-slate-500 font-bold">Daftar Rombel & Kelas Mengajar Anda</p>
    </div>

    <!-- Classroom List (3D Clay Cards) -->
    <div class="space-y-3">
        @forelse($classrooms as $cls)
            <div class="clay-card p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-2xl clay-blue flex items-center justify-center text-white text-xl font-black shadow-md">
                            🏫
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $cls->class_name }}</h3>
                            <span class="text-[10px] font-bold text-slate-500 block">
                                {{ $cls->students_count ?? 0 }} Siswa Terdaftar
                            </span>
                        </div>
                    </div>

                    @if($teacher && $cls->homeroom_teacher_id == $teacher->id)
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 text-[9px] font-black uppercase shadow-xs">
                            Wali Kelas
                        </span>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('mobile.guru.absensi.input', ['classroom_id' => $cls->id]) }}" 
                       class="py-2.5 px-3 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-black text-center hover:bg-emerald-100 transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-clipboard-user text-xs"></i> Absen Siswa
                    </a>
                    <a href="{{ route('mobile.lms.index') }}" 
                       class="py-2.5 px-3 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 text-[11px] font-black text-center hover:bg-purple-100 transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-book-open text-xs"></i> Modul LMS
                    </a>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">🏫</div>
                <p>Belum ada rincian kelas mengajar yang terhubung.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
