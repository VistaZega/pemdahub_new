@extends('mobile.layouts.app')

@section('title', 'Rekap Nilai Siswa - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1">
    <!-- Top Header -->
    <div class="flex items-center justify-between gap-2 px-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-base font-black text-slate-900 leading-tight truncate">Rekap Nilai</h2>
                <p class="text-[10px] text-slate-500 font-bold truncate">Akademik & Tugas Siswa</p>
            </div>
        </div>
        <div class="w-9 h-9 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-700 flex items-center justify-center text-base font-black shrink-0">
            📈
        </div>
    </div>

    <!-- Header & Average Badge Clay Card -->
    <div class="clay-green p-5 sm:p-6 flex items-center justify-between rounded-3xl shadow-md">
        <div>
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">Semester Aktif</span>
            <h2 class="text-base font-black text-white mt-1.5 leading-snug">{{ $activeSemester->name ?? 'Semester Berjalan' }}</h2>
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
                                <span class="text-[10px] text-slate-500 block truncate font-bold">{{ $g->getGradeTypeLabel() }}</span>
                                @if($g->lms_source_type)
                                    <span class="text-[8px] font-black uppercase px-1.5 py-0.5 rounded-full {{ $g->lms_source_type === 'quiz_attempt' ? 'bg-purple-100 text-purple-700' : ($g->lms_source_type === 'submission' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700') }}">
                                        {{ $g->lms_source_type === 'quiz_attempt' ? '📝 Kuis LMS' : ($g->lms_source_type === 'submission' ? '📄 Tugas LMS' : '💻 CBT') }}
                                    </span>
                                @endif
                                <span class="text-[9px] text-slate-400 font-bold block mt-0.5">{{ $g->notes ? Str::limit($g->notes, 25) : ($g->created_at ? $g->created_at->format('d/m/Y') : '') }}</span>
                            </div>
                            <span class="text-base font-black {{ $g->score >= 75 ? 'text-emerald-600' : ($g->score >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $g->score }}</span>
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
