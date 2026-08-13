@extends('mobile.layouts.app')

@section('title', 'Rekap Nilai - Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header & Average Badge -->
    <div class="glass-card rounded-3xl p-5 relative overflow-hidden bg-gradient-to-br from-indigo-950/80 via-slate-900 to-slate-950 border border-indigo-500/30 flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-indigo-400 uppercase">Rekap Nilai Siswa</span>
            <h2 class="text-base font-extrabold text-white mt-0.5">{{ $activeSemester->name ?? 'Semester Aktif' }}</h2>
        </div>
        <div class="text-right bg-indigo-500/20 border border-indigo-500/30 rounded-2xl p-3">
            <span class="text-2xl font-black text-indigo-300">{{ $avgScore }}</span>
            <span class="block text-[9px] text-slate-400 font-bold uppercase">Rata-rata</span>
        </div>
    </div>

    <!-- Grades List by Subject -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Daftar Nilai per Mata Pelajaran</h3>

        @forelse($gradesBySubject as $subjectName => $subjectGrades)
            <div class="glass-card rounded-2xl p-4 space-y-2.5">
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                    <h4 class="text-xs font-extrabold text-white"><i class="fa-solid fa-book text-indigo-400 mr-1.5"></i>{{ $subjectName }}</h4>
                    <span class="text-[10px] text-slate-400 font-semibold">{{ count($subjectGrades) }} Penilaian</span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    @foreach($subjectGrades as $g)
                        <div class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 block truncate">{{ $g->assessmentType->name ?? 'Nilai' }}</span>
                                <span class="text-[9px] text-slate-500">{{ $g->created_at ? $g->created_at->format('d/m/Y') : '' }}</span>
                            </div>
                            <span class="text-sm font-black text-indigo-400">{{ $g->score }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                Belum ada data nilai di semester ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
