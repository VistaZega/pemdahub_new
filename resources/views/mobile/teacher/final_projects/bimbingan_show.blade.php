@extends('mobile.layouts.app')

@php
    $bimbinganTitle = ($schoolType === 'SMK') ? 'Bimbingan Project Akhir' : 'Bimbingan Penelitian Akhir';
@endphp

@section('title', 'Review Bimbingan - ' . $project->title)

@section('content')
<div class="space-y-4 pt-1">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.guru.final-projects.bimbingan') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Detail Bimbingan</h2>
        <div class="w-9"></div>
    </div>

    <!-- Project Hero Card (Clay Purple) -->
    <div class="clay-purple p-5 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">
                Tahap: {{ $stages[$project->current_stage]['label'] ?? ($stages[$project->current_stage]['name'] ?? ucfirst($project->current_stage ?? 'Bab 1')) }}
            </span>
            <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full uppercase">
                Status: {{ $project->status }}
            </span>
        </div>

        <h3 class="text-base font-black text-white leading-snug">
            {{ $project->title }}
        </h3>

        @if($project->abstract)
            <p class="text-[11px] text-purple-100 font-medium line-clamp-3 leading-relaxed">
                {{ $project->abstract }}
            </p>
        @endif

        <div class="pt-2 border-t border-white/20 text-[10px] font-bold text-purple-100">
            Ketua: <strong class="text-white">{{ $project->student->full_name ?? '-' }}</strong> | 
            Anggota: <span class="text-white">{{ $project->members->pluck('student.full_name')->join(', ') }}</span>
        </div>
    </div>

    <!-- Stage Progress Bar (Clay Card) -->
    <div class="clay-card p-4 space-y-2.5">
        <div class="flex items-center justify-between">
            <h4 class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                <i class="fa-solid fa-bars-progress text-purple-600"></i> Progres Bab / Tahapan
            </h4>
        </div>

        @php
            $stageKeys = array_keys($stages);
            $currentIndex = array_search($project->current_stage, $stageKeys);
            if ($currentIndex === false) $currentIndex = 0;
        @endphp
        <div class="flex items-center justify-between gap-1">
            @foreach($stages as $key => $stageInfo)
                @php $stepIndex = array_search($key, $stageKeys); @endphp
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-7 h-7 rounded-xl flex items-center justify-center text-[10px] font-black border-2 transition
                        {{ $stepIndex <= $currentIndex ? 'bg-purple-600 text-white border-purple-700 shadow-sm' : 'bg-slate-100 text-slate-400 border-slate-200' }}">
                        @if($stepIndex < $currentIndex)
                            <i class="fa-solid fa-check text-[9px]"></i>
                        @else
                            {{ $stepIndex + 1 }}
                        @endif
                    </div>
                    <span class="text-[8px] font-extrabold text-slate-600 text-center mt-1 truncate max-w-[50px] leading-tight">
                        {{ $stageInfo['label'] ?? ($stageInfo['name'] ?? ucfirst($key)) }}
                    </span>
                </div>
            @endforeach
        </div>

        @if($project->status !== 'ready' && $project->status !== 'passed')
        <div class="pt-2 border-t border-slate-100">
            <form action="{{ route('mobile.guru.final-projects.bimbingan.ready', $project->id) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Apakah bimbingan seluruh bab telah selesai dan kelompok ini siap maju Ujian Sidang?')"
                        class="w-full py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-black text-xs shadow-md active:scale-95 flex items-center justify-center gap-1.5 transition">
                    <i class="fa-solid fa-graduation-cap"></i> ACC & Tandai Siap Ujian Sidang
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- Consultation Logs & Review Form -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                Jurnal / Logbook Konsultasi Siswa ({{ $project->logs->count() }})
            </h3>
        </div>

        @forelse($project->logs as $log)
            <div class="clay-card p-4 space-y-3 border-2 {{ $log->status === 'approved' ? 'border-emerald-200' : ($log->status === 'rejected' ? 'border-rose-200' : 'border-amber-300') }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">
                            {{ $stages[$log->stage]['label'] ?? ($stages[$log->stage]['name'] ?? ucfirst($log->stage)) }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-500">
                            {{ \Carbon\Carbon::parse($log->log_date)->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    @if($log->status === 'approved')
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-green">
                            <i class="fa-solid fa-check text-[8px]"></i> Disetujui (ACC)
                        </span>
                    @elseif($log->status === 'rejected')
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-pink">
                            <i class="fa-solid fa-xmark text-[8px]"></i> Revisi
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-yellow">
                            <i class="fa-solid fa-hourglass text-[8px]"></i> Menunggu Review
                        </span>
                    @endif
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs font-bold text-slate-800 leading-relaxed">
                    {{ $log->activity }}
                </div>

                @if($log->file_attachment || $log->drive_link)
                <div class="flex items-center gap-3 text-[11px] font-black">
                    @if($log->file_attachment)
                        <a href="{{ asset('storage/' . $log->file_attachment) }}" target="_blank" class="text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-200 hover:bg-purple-100 flex items-center gap-1">
                            <i class="fa-solid fa-paperclip"></i> Lihat Berkas Draft
                        </a>
                    @endif
                    @if($log->drive_link)
                        <a href="{{ $log->drive_link }}" target="_blank" class="text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200 hover:bg-blue-100 flex items-center gap-1">
                            <i class="fa-solid fa-link"></i> Link Drive
                        </a>
                    @endif
                </div>
                @endif

                @if($log->advisor_feedback)
                    <div class="p-2.5 rounded-xl bg-purple-50 border border-purple-200 text-xs font-semibold text-purple-950">
                        <span class="font-black text-[10px] text-purple-800 block mb-0.5">Catatan Evaluasi Anda:</span>
                        {{ $log->advisor_feedback }}
                    </div>
                @endif

                <!-- Review / Evaluation Action Form -->
                <div class="pt-2 border-t border-slate-100">
                    <form action="{{ route('mobile.guru.final-projects.bimbingan.review-log', [$project->id, $log->id]) }}" method="POST" class="space-y-2">
                        @csrf
                        <textarea name="advisor_feedback" rows="2" required placeholder="Tuliskan catatan arahan, koreksi, atau alasan ACC..."
                                  class="w-full p-2.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold resize-none focus:outline-hidden focus:border-purple-500">{{ $log->advisor_feedback }}</textarea>

                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" name="status" value="rejected" class="py-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-black text-xs active:scale-95 transition">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Perlu Revisi
                            </button>
                            <button type="submit" name="status" value="approved" class="py-2 rounded-xl bg-emerald-600 text-white font-black text-xs shadow-md active:scale-95 transition">
                                <i class="fa-solid fa-check mr-1"></i> Setujui / ACC (+15 Poin)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Siswa belum mengirimkan jurnal konsultasi bimbingan.
            </div>
        @endforelse
    </div>
</div>
@endsection
