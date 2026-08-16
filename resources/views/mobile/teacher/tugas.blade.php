@extends('mobile.layouts.app')

@section('title', 'Periksa Tugas & Nilai - Guru PembdaHUB')

@section('content')
<div class="space-y-4 pt-1" x-data="{ activeFilter: 'all' }">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between gap-2 px-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-base font-black text-slate-900 leading-tight truncate">Periksa Tugas</h2>
                <p class="text-[10px] text-slate-500 font-bold truncate">Penilaian Submission LMS Siswa</p>
            </div>
        </div>

        <div class="w-9 h-9 rounded-2xl bg-purple-50 border-2 border-purple-300 text-purple-700 flex items-center justify-center text-base font-black shrink-0">
            📝
        </div>
    </div>

    <!-- Stats Banner Clay Card -->
    @php
        $totalSubmissions = 0;
        $gradedSubmissions = 0;
        foreach($assignments as $asg) {
            $subs = $asg->submissions ?? [];
            $totalSubmissions += count($subs);
            foreach($subs as $s) {
                if ($s->score !== null) $gradedSubmissions++;
            }
        }
        $pendingSubmissions = $totalSubmissions - $gradedSubmissions;
    @endphp
    <div class="clay-purple p-5 sm:p-6 space-y-3.5 rounded-3xl shadow-md">
        <div class="flex items-center justify-between text-white">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider bg-white/20 px-2.5 py-0.5 rounded-full border border-white/30">
                    Aktivitas Penilaian
                </span>
                <h3 class="text-sm font-black text-white mt-1">LMS Assignment Tracker</h3>
            </div>
            <span class="text-xs font-black bg-amber-400 text-slate-950 px-3 py-1 rounded-full border border-amber-300 shadow-xs">
                {{ $pendingSubmissions }} Perlu Dinilai
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2 text-center text-white">
            <div class="p-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30">
                <span class="text-lg font-black block leading-tight">{{ $assignments->count() }}</span>
                <span class="text-[9px] uppercase tracking-wide opacity-90 font-bold">Total Tugas</span>
            </div>
            <div class="p-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30">
                <span class="text-lg font-black block leading-tight">{{ $totalSubmissions }}</span>
                <span class="text-[9px] uppercase tracking-wide opacity-90 font-bold">Terkumpul</span>
            </div>
            <div class="p-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30">
                <span class="text-lg font-black block leading-tight">{{ $gradedSubmissions }}</span>
                <span class="text-[9px] uppercase tracking-wide opacity-90 font-bold">Dinilai</span>
            </div>
        </div>
    </div>

    <!-- Rombel Filter Pills -->
    @if(isset($classrooms) && $classrooms->count() > 0)
        <div class="space-y-1.5 px-0.5">
            <div class="flex items-center justify-between text-[11px] font-black text-slate-700">
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-users text-purple-600"></i> Filter Kelas / Rombel:</span>
                @if($selectedClassroomId)
                    <a href="{{ route('mobile.guru.tugas') }}" class="text-[10px] text-purple-600 font-extrabold hover:underline">Reset Filter ✕</a>
                @endif
            </div>
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar -mx-1 px-1">
                <a href="{{ route('mobile.guru.tugas') }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 shadow-2xs
                          {{ !$selectedClassroomId ? 'bg-slate-900 text-amber-300 border-slate-900' : 'bg-white text-slate-700 border-slate-200 hover:border-purple-300' }}">
                    <i class="fa-solid fa-table-cells-large text-[10px] mr-1"></i> Semua Rombel
                </a>
                @foreach($classrooms as $cls)
                    <a href="{{ route('mobile.guru.tugas', ['classroom_id' => $cls->id]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 shadow-2xs
                              {{ $selectedClassroomId == $cls->id ? 'bg-slate-900 text-amber-300 border-slate-900' : 'bg-white text-slate-700 border-slate-200 hover:border-purple-300' }}">
                        {{ $cls->class_name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Assignments List -->
    <div class="space-y-3.5">
        @forelse($assignments as $assignment)
            @php
                $assignmentSubs = $assignment->submissions ?? collect([]);
                $ungradedCount = $assignmentSubs->whereNull('score')->count();
            @endphp
            <div class="clay-card p-5 space-y-3 bg-white border-2 border-slate-200 rounded-3xl shadow-sm hover:border-purple-300 transition" x-data="{ openSubmissions: {{ $ungradedCount > 0 ? 'true' : 'false' }} }">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black bg-purple-100 text-purple-800 border border-purple-200 uppercase">
                            {{ $assignment->course->course_name ?? 'Mata Pelajaran' }}
                        </span>
                        <h3 class="text-xs font-black text-slate-900 mt-1.5 leading-snug">{{ $assignment->title }}</h3>
                        <p class="text-[10px] text-slate-400 font-bold mt-0.5">
                            <i class="fa-regular fa-clock text-purple-500 mr-1"></i>Batas Waktu: {{ $assignment->due_date ?? '-' }}
                        </p>
                    </div>

                    @if($ungradedCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-300 text-[9px] font-black shrink-0">
                            {{ $ungradedCount }} Belum Dinilai
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[9px] font-black shrink-0">
                            ✓ Selesai
                        </span>
                    @endif
                </div>

                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-600 font-black flex items-center gap-1.5">
                        <i class="fa-solid fa-users text-slate-400 text-xs"></i>
                        {{ count($assignmentSubs) }} Pengumpulan
                    </span>
                    <button @click="openSubmissions = !openSubmissions" class="px-3.5 py-1.5 bg-purple-50 text-purple-700 text-xs font-black rounded-xl border border-purple-200 hover:bg-purple-100 active:scale-95 transition flex items-center gap-1.5 shadow-2xs">
                        <i class="fa-solid" :class="openSubmissions ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        <span x-text="openSubmissions ? 'Tutup Daftar' : 'Periksa ({{ count($assignmentSubs) }})'"></span>
                    </button>
                </div>

                <!-- Submissions List Inside Card -->
                <div x-show="openSubmissions" x-collapse class="space-y-3 pt-3 border-t border-slate-100">
                    @forelse($assignmentSubs as $sub)
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
                        <div class="p-4 bg-slate-50/80 rounded-2xl space-y-2.5 border-2 border-slate-200 hover:border-purple-200 transition">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img src="{{ $studentPhoto }}" alt="{{ $subStudent?->full_name ?? 'Siswa' }}"
                                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($subStudent?->full_name ?? 'Siswa') }}&background=7c3aed&color=fff&bold=true';"
                                         class="w-9 h-9 rounded-xl object-cover border border-purple-300 shadow-2xs shrink-0">
                                    <div class="min-w-0">
                                        <h4 class="font-black text-slate-900 truncate leading-snug">{{ $subStudent?->full_name ?? 'Siswa' }}</h4>
                                        <p class="text-[9px] text-slate-400 font-bold">NISN: {{ $subStudent?->nisn ?? $subStudent?->nis ?? '-' }}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400 font-bold shrink-0">
                                    {{ $sub->created_at ? $sub->created_at->diffForHumans() : '' }}
                                </span>
                            </div>

                            @if($sub->submission_text)
                                <div class="p-2.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 font-semibold leading-relaxed">
                                    {{ $sub->submission_text }}
                                </div>
                            @endif

                            @if($sub->file_path)
                                <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-[10px] font-black border border-blue-200 hover:bg-blue-100 active:scale-95 transition">
                                    <i class="fa-solid fa-paperclip"></i> Lihat File Lampiran Siswa
                                </a>
                            @endif

                            <form action="{{ route('mobile.guru.tugas.grade', $sub->id) }}" method="POST" class="flex items-center gap-2 pt-1 border-t border-slate-200/60">
                                @csrf
                                <div class="flex-1 relative">
                                    <input type="number" name="score" value="{{ $sub->score }}" placeholder="Nilai (0-100)" required min="0" max="100"
                                           class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-black focus:outline-hidden focus:border-purple-600">
                                </div>
                                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-black text-xs rounded-xl shadow-xs hover:opacity-95 active:scale-95 transition shrink-0">
                                    {{ $sub->score !== null ? 'Perbarui Nilai' : 'Simpan Nilai' }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-4 text-center text-slate-400 text-xs font-bold bg-slate-50 rounded-2xl">
                            Belum ada siswa yang mengumpulkan tugas ini.
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 space-y-2 bg-white rounded-3xl">
                <div class="text-3xl">📝</div>
                <h4 class="text-sm font-black text-slate-800">Belum Ada Tugas Aktif</h4>
                <p class="text-xs text-slate-400 font-bold">Buat penugasan baru di Modul LMS untuk melihat submission siswa di sini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
