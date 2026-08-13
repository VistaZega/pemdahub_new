@extends('mobile.layouts.app')

@section('title', 'Rekap Nilai 3D - Mobile Pro')

@section('content')
<div class="space-y-4">
    <!-- Header & Average Badge Clay Card -->
    <div class="clay-green p-6 flex items-center justify-between">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">Rekap Nilai Siswa</span>
            <h2 class="text-base font-black text-white mt-1.5 leading-snug">{{ $activeSemester->name ?? 'Semester Aktif' }}</h2>
        </div>
        <div class="text-right bg-white/30 backdrop-blur-md border border-white/40 rounded-2xl p-3 shadow-sm">
            <span class="text-2xl font-black text-white leading-none">{{ $avgScore }}</span>
            <span class="block text-[9px] font-black uppercase text-emerald-100 mt-0.5">Rata-rata</span>
        </div>
    </div>

    <!-- Grades List by Subject -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Daftar Nilai per Mata Pelajaran</h3>

        @forelse($gradesBySubject as $subjectName => $subjectGrades)
            <div class="clay-card p-4.5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h4 class="text-xs font-black text-slate-900"><i class="fa-solid fa-book text-emerald-600 mr-1.5"></i>{{ $subjectName }}</h4>
                    <span class="text-[10px] text-slate-500 font-extrabold">{{ count($subjectGrades) }} Penilaian</span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    @foreach($subjectGrades as $g)
                        <div class="p-3 rounded-2xl bg-[#f4f7fc] border-2 border-slate-200/80 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-500 block truncate font-bold">{{ $g->assessmentType->name ?? 'Nilai' }}</span>
                                <span class="text-[9px] text-slate-400 font-bold">{{ $g->created_at ? $g->created_at->format('d/m/Y') : '' }}</span>
                            </div>
                            <span class="text-base font-black text-emerald-600">{{ $g->score }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Belum ada data nilai di semester ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
