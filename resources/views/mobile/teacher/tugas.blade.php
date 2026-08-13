@extends('mobile.layouts.app')

@section('title', 'Nilai Tugas 3D - Guru Mobile')

@section('content')
<div class="space-y-4">
    <div>
        <h2 class="text-xl font-black text-slate-900">Kelola & Nilai Tugas Siswa 📝</h2>
        <p class="text-[11px] text-slate-500 font-bold">Pemeriksaan Submission Tugas dari HP</p>
    </div>

    <div class="space-y-3">
        @forelse($assignments as $assignment)
            <div class="clay-card p-4.5 space-y-3" x-data="{ openSubmissions: false }">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-3 py-0.5 rounded-full text-[9px] font-black bg-purple-100 text-purple-800 border border-purple-200 uppercase">
                            {{ $assignment->course->course_name ?? 'Mata Pelajaran' }}
                        </span>
                        <h3 class="text-sm font-black text-slate-900 mt-1.5">{{ $assignment->title }}</h3>
                        <p class="text-xs text-slate-500 font-bold"><i class="fa-regular fa-clock mr-1 text-purple-600"></i>Batas Waktu: {{ $assignment->due_date ?? '-' }}</p>
                    </div>
                </div>

                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-black">Terkumpul: {{ count($assignment->submissions ?? []) }} Tugas</span>
                    <button @click="openSubmissions = !openSubmissions" class="px-3.5 py-1.5 bg-purple-50 text-purple-700 text-xs font-black rounded-xl border border-purple-200">
                        <span x-text="openSubmissions ? 'Sembunyikan' : 'Lihat & Nilai'"></span>
                    </button>
                </div>

                <!-- Submissions List Inside Card -->
                <div x-show="openSubmissions" class="space-y-2 pt-2.5 border-t border-slate-100">
                    @forelse($assignment->submissions as $sub)
                        @php
                            $subStudent = $sub->student ?? null;
                            $studentPhoto = $subStudent?->photo_url ?? null;
                            if (!$studentPhoto || str_contains($studentPhoto, 'default-student.jpg') || str_contains($studentPhoto, 'default-avatar')) {
                                if (isset($subStudent->user->avatar_url) && $subStudent->user->avatar_url) {
                                    $studentPhoto = $subStudent->user->avatar_url;
                                } else {
                                    $studentPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($subStudent?->full_name ?? 'Siswa') . '&background=7c3aed&color=fff&bold=true';
                                }
                            }
                        @endphp
                        <div class="p-3.5 bg-[#f4f7fc] rounded-2xl space-y-2 border-2 border-slate-200/80">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <div class="flex items-center space-x-2.5">
                                    <img src="{{ $studentPhoto }}" alt="{{ $subStudent?->full_name ?? 'Siswa' }}"
                                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($subStudent?->full_name ?? 'Siswa') }}&background=7c3aed&color=fff&bold=true';"
                                         class="w-8 h-8 rounded-xl object-cover border border-purple-300 shadow-xs shrink-0">
                                    <div>
                                        <span class="font-black text-slate-900 block leading-snug">{{ $subStudent?->full_name ?? 'Siswa' }}</span>
                                        <span class="text-[9px] text-slate-500 font-bold">NIS: {{ $subStudent?->nis ?? '-' }}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400 font-bold shrink-0">{{ $sub->created_at ? $sub->created_at->diffForHumans() : '' }}</span>
                            </div>

                            <form action="{{ route('mobile.guru.tugas.grade', $sub->id) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="number" name="score" value="{{ $sub->score }}" placeholder="Nilai (0-100)" required min="0" max="100"
                                       class="w-24 px-3 py-2 bg-white border-2 border-slate-300 rounded-xl text-slate-900 text-xs font-black">
                                <button type="submit" class="clay-btn px-4 py-2 text-white font-black text-xs">
                                    Simpan Nilai
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 italic font-bold">Belum ada siswa yang mengumpulkan tugas ini.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Belum ada tugas yang dibuat.
            </div>
        @endforelse
    </div>
</div>
@endsection
