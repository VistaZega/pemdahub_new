@extends('layouts.guru')
@section('title', 'Kelas Saya - Portal Guru')

@section('content')
<div class="gd-wrap">
    {{-- Header Hero --}}
    <div class="gd-hero">
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-400 text-slate-950 flex items-center justify-center text-xl shadow-md shrink-0">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-white leading-tight">Kelas Saya</h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-0.5">Daftar seluruh kelas yang Anda ampu semester ini</p>
                </div>
            </div>
            @if($activeYear)
                <span class="bg-emerald-400 text-slate-950 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                    TP {{ $activeYear->year }}
                </span>
            @endif
        </div>
    </div>

    @if($classrooms->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($classrooms as $cr)
                @php $isWali = $homeroomClassroom && $homeroomClassroom->id === $cr->id; @endphp
                <div class="gd-card overflow-hidden hover:shadow-lg transition duration-200 flex flex-col justify-between">
                    <div class="bg-gradient-to-r {{ $isWali ? 'from-emerald-600 to-teal-600' : 'from-slate-800 to-indigo-900' }} p-5 text-white">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-extrabold text-lg leading-snug">{{ $cr->class_name }}</h3>
                            @if($isWali)
                                <span class="text-[11px] font-bold bg-white/20 px-2.5 py-0.5 rounded-full shrink-0">
                                    <i class="fas fa-star mr-1 text-amber-300"></i>Wali Kelas
                                </span>
                            @endif
                        </div>
                        <p class="text-white/80 text-xs sm:text-sm mt-1">{{ $cr->school->name ?? '' }}</p>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                                <p class="text-xs text-slate-500 font-medium">Siswa</p>
                                <p class="font-extrabold text-xl text-slate-900">{{ $cr->students_count }}</p>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                                <p class="text-xs text-slate-500 font-medium">Tingkat</p>
                                <p class="font-extrabold text-xl text-slate-900">{{ $cr->grade_level ?? '-' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('guru.siswa-kelas', $cr->id) }}"
                           class="block text-center bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 rounded-xl transition">
                            <i class="fas fa-eye mr-1.5"></i> Lihat Siswa
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="gd-card p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="fas fa-chalkboard"></i>
            </div>
            <p class="text-base font-bold text-slate-800">Belum ada kelas yang ditugaskan</p>
            <p class="text-sm text-slate-500 mt-1">Hubungi admin sekolah untuk penugasan kelas mengajar Anda.</p>
        </div>
    @endif
</div>
@endsection
