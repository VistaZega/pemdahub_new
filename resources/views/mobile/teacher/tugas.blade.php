@extends('mobile.layouts.app')

@section('title', 'Nilai Tugas Siswa - Guru Mobile')

@section('content')
<div class="space-y-4">
    <div>
        <h2 class="text-lg font-extrabold text-white">Kelola & Nilai Tugas Siswa</h2>
        <p class="text-[11px] text-slate-400">Pemeriksaan Submission Tugas dari HP</p>
    </div>

    <div class="space-y-3">
        @forelse($assignments as $assignment)
            <div class="glass-card rounded-2xl p-4 space-y-3" x-data="{ openSubmissions: false }">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            {{ $assignment->course->course_name ?? 'Mata Pelajaran' }}
                        </span>
                        <h3 class="text-sm font-extrabold text-white mt-1">{{ $assignment->title }}</h3>
                        <p class="text-xs text-slate-400"><i class="fa-regular fa-clock mr-1"></i>Batas Waktu: {{ $assignment->due_date ?? '-' }}</p>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Terkumpul: {{ count($assignment->submissions ?? []) }} Tugas</span>
                    <button @click="openSubmissions = !openSubmissions" class="px-3 py-1.5 bg-slate-800 text-purple-300 text-xs font-bold rounded-xl border border-slate-700">
                        <span x-text="openSubmissions ? 'Sembunyikan' : 'Lihat & Nilai'"></span>
                    </button>
                </div>

                <!-- Submissions List Inside Card -->
                <div x-show="openSubmissions" class="space-y-2 pt-2 border-t border-slate-800">
                    @forelse($assignment->submissions as $sub)
                        <div class="p-3 bg-slate-900/90 rounded-xl space-y-2 border border-slate-800">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-white">{{ $sub->student->full_name ?? 'Siswa' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $sub->created_at ? $sub->created_at->diffForHumans() : '' }}</span>
                            </div>

                            <form action="{{ route('mobile.guru.tugas.grade', $sub->id) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="number" name="score" value="{{ $sub->score }}" placeholder="Nilai (0-100)" required min="0" max="100"
                                       class="w-24 px-2.5 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-white text-xs">
                                <button type="submit" class="px-3 py-1.5 bg-purple-600 text-white font-bold text-xs rounded-lg shadow">
                                    Simpan Nilai
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 italic">Belum ada siswa yang mengumpulkan tugas ini.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                Belum ada tugas yang dibuat.
            </div>
        @endforelse
    </div>
</div>
@endsection
