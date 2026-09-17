@extends('layouts.siswa')
@section('title', 'Ujian CBT')
@section('content')
<div class="space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 via-orange-500 to-rose-600 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center border border-gray-100">
                    <i class="fas fa-laptop text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Ujian CBT</h1>
                    <p class="text-amber-200 mt-1 text-sm">Ujian online yang tersedia untuk Anda</p>
                </div>
            </div>
            <a href="{{ route('siswa.cbt.history') }}" class="px-5 py-2.5 bg-white/15 rounded-xl font-medium text-sm border border-gray-100 hover:bg-white/25 transition flex items-center gap-2">
                <i class="fas fa-history"></i>Riwayat Ujian
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600 text-sm"></i></div>
        <p class="text-emerald-700 text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('warning'))
    <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-triangle text-amber-600 text-sm"></i></div>
        <p class="text-amber-700 text-sm font-medium">{{ session('warning') }}</p>
    </div>
    @endif

    {{-- Exam Cards --}}
    @forelse($availableExams as $exam)
    <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex flex-col md:flex-row md:items-center gap-5">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">{{ strtoupper($exam->exam_type) }}</span>
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">{{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }}</span>
                    @if($exam->requires_tuition_payment && isset($exam->compliance))
                        @if($exam->compliance['has_dispensation'])
                            <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1">
                                <i class="fas fa-certificate text-[10px]"></i> Dispensasi Wali Kelas
                            </span>
                        @elseif($exam->compliance['allowed'])
                            <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                <i class="fas fa-check-circle text-[10px]"></i> SPP Lunas
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-1">
                                <i class="fas fa-lock text-[10px]"></i> Perlu Kepatuhan SPP
                            </span>
                        @endif
                    @endif
                </div>
                <a href="{{ route('siswa.cbt.show', $exam) }}">
                    <h2 class="text-xl font-bold text-gray-900 group-hover:text-amber-700 transition-colors">{{ $exam->exam_title }}</h2>
                </a>
                @if($exam->exam_description)
                <p class="text-sm text-gray-600 mt-1.5 line-clamp-2">{{ Str::limit($exam->exam_description, 120) }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-4 mt-3">
                    <span class="text-xs text-gray-500 flex items-center gap-1.5"><div class="w-5 h-5 rounded bg-gray-100 flex items-center justify-center"><i class="fas fa-user-tie text-gray-400" style="font-size:9px"></i></div>{{ $exam->teacher?->full_name ?? '-' }}</span>
                    <span class="text-xs text-gray-500 flex items-center gap-1.5"><div class="w-5 h-5 rounded bg-gray-100 flex items-center justify-center"><i class="fas fa-clock text-gray-400" style="font-size:9px"></i></div>{{ $exam->duration_minutes }} menit</span>
                    <span class="text-xs text-gray-500 flex items-center gap-1.5"><div class="w-5 h-5 rounded bg-gray-100 flex items-center justify-center"><i class="fas fa-list-ol text-gray-400" style="font-size:9px"></i></div>{{ $exam->total_questions_shown }} soal</span>
                    <span class="text-xs text-gray-500 flex items-center gap-1.5"><div class="w-5 h-5 rounded bg-gray-100 flex items-center justify-center"><i class="fas fa-redo text-gray-400" style="font-size:9px"></i></div>{{ $exam->attempts_used }}/{{ $exam->max_attempts }}</span>
                    @if($exam->start_time)
                    <span class="text-xs text-gray-500 flex items-center gap-1.5"><div class="w-5 h-5 rounded bg-gray-100 flex items-center justify-center"><i class="fas fa-calendar text-gray-400" style="font-size:9px"></i></div>{{ $exam->start_time->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
            </div>
            <div class="flex flex-col items-end gap-3 flex-shrink-0">
                @if($exam->last_result)
                <div class="text-center px-5 py-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="text-2xl font-bold {{ $exam->last_result->final_score >= $exam->passing_score ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($exam->last_result->final_score, 1) }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Nilai Terakhir</div>
                </div>
                @endif
                @if($exam->has_active_session)
                <a href="{{ route('siswa.cbt.show', $exam) }}" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:shadow-lg transition font-bold text-sm flex items-center gap-2">
                    <i class="fas fa-forward"></i>Lanjutkan Ujian
                </a>
                @elseif($exam->can_attempt)
                <a href="{{ route('siswa.cbt.show', $exam) }}" class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl hover:shadow-lg transition font-bold text-sm flex items-center gap-2">
                    <i class="fas fa-play"></i>Mulai Ujian
                </a>
                @elseif(isset($exam->compliance) && !$exam->compliance['allowed'])
                <a href="{{ route('siswa.cbt.show', $exam) }}" class="px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl transition font-bold text-xs flex items-center gap-1.5">
                    <i class="fas fa-lock text-[11px]"></i> Cek Status / Hubungi Wali
                </a>
                @else
                <span class="px-6 py-3 bg-gray-100 text-gray-400 rounded-xl cursor-not-allowed text-sm font-medium">Percobaan Habis</span>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
        <div class="w-20 h-20 rounded-2xl bg-amber-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-inbox text-3xl text-amber-500"></i></div>
        <h3 class="text-xl font-bold text-gray-700 mb-2">Tidak Ada Ujian</h3>
        <p class="text-gray-500 text-sm">Belum ada ujian CBT yang tersedia saat ini.</p>
    </div>
    @endforelse
</div>
@endsection
