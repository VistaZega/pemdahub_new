@extends('mobile.layouts.app')

@php
    $ujianTitle = ($schoolType === 'SMK') ? 'Ujian Sidang Project Akhir' : 'Ujian Sidang Penelitian Akhir';
@endphp

@section('title', $ujianTitle . ' - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">{{ $ujianTitle }}</h2>
        <div class="w-9"></div>
    </div>

    <!-- Hero Card (Clay Pink) -->
    <div class="clay-pink p-5 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">
                Penguji Sidang
            </span>
            <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full">
                {{ $activeYear->name ?? 'Tahun Aktif' }}
            </span>
        </div>
        <h3 class="text-base font-black text-white leading-tight">
            {{ $teacher->full_name }}
        </h3>
        <p class="text-xs text-rose-100 font-bold">
            Ditugaskan menguji <strong>{{ $projects->count() }}</strong> Kelompok Sidang
        </p>
    </div>

    <!-- List of Exam Groups -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                Jadwal Ujian Sidang ({{ $projects->count() }})
            </h3>
        </div>

        @forelse($projects as $project)
            <div class="clay-card p-4 space-y-3 border-2 {{ $project->status === 'passed' ? 'border-emerald-300' : ($project->status === 'failed' ? 'border-rose-300' : 'border-slate-200') }}"
                 x-data="{ showGradeModal: false }">
                <div class="flex items-center justify-between gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-slate-100 text-slate-800">
                        {{ $project->exam_date ? \Carbon\Carbon::parse($project->exam_date)->translatedFormat('d M Y') : 'Jadwal Ditentukan' }}
                    </span>

                    @if($project->status === 'passed')
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-green">
                            <i class="fa-solid fa-trophy text-[8px]"></i> Lulus (Nilai: {{ $project->final_score }})
                        </span>
                    @elseif($project->status === 'failed')
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-pink">
                            <i class="fa-solid fa-xmark text-[8px]"></i> Tidak Lulus (Nilai: {{ $project->final_score }})
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-yellow">
                            <i class="fa-solid fa-clock text-[8px]"></i> Belum Dinilai
                        </span>
                    @endif
                </div>

                <div>
                    <h4 class="text-xs font-black text-slate-900 leading-snug">{{ $project->title }}</h4>
                    <p class="text-[10px] text-slate-500 font-bold mt-1">
                        Ketua: <strong>{{ $project->student->full_name ?? '-' }}</strong> | Pembimbing: {{ $project->advisor->full_name ?? '-' }}
                    </p>
                    @if($project->exam_room)
                        <p class="text-[10px] text-purple-700 font-extrabold mt-0.5">
                            <i class="fa-solid fa-door-open mr-1"></i> Ruang: {{ $project->exam_room }}
                        </p>
                    @endif
                </div>

                @if($project->examiner_notes)
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs font-semibold text-slate-800">
                        <span class="font-black text-[10px] text-slate-600 block mb-0.5">Catatan Penguji:</span>
                        {{ $project->examiner_notes }}
                    </div>
                @endif

                <!-- Grade Action Button -->
                <button @click="showGradeModal = true" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white font-black text-xs shadow-md active:scale-95 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-pen-nib"></i> {{ $project->final_score !== null ? 'Perbarui Nilai Sidang' : 'Input Nilai Ujian Sidang' }}
                </button>

                <!-- Grading Modal -->
                <div x-show="showGradeModal" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                     style="display: none;">
                    <div class="bg-white rounded-3xl p-5 w-full max-w-sm shadow-2xl border-4 border-white space-y-3 text-left">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-graduation-cap text-rose-500"></i> Form Nilai Sidang
                            </h3>
                            <button @click="showGradeModal = false" class="text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-xmark text-base"></i>
                            </button>
                        </div>

                        <p class="text-[11px] font-black text-slate-700 truncate">{{ $project->title }}</p>

                        <form action="{{ route('mobile.guru.final-projects.ujian.grade', $project->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="final_score_{{ $project->id }}" class="block text-[11px] font-black text-slate-800 mb-1">Nilai Sidang (0-100) *</label>
                                    <input type="number" step="0.1" min="0" max="100" id="final_score_{{ $project->id }}" name="final_score" value="{{ $project->final_score }}" required placeholder="Contoh: 88.5"
                                           class="w-full px-3 py-2 bg-[#f4f7fc] border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-hidden focus:border-rose-500">
                                </div>

                                <div>
                                    <label for="status_{{ $project->id }}" class="block text-[11px] font-black text-slate-800 mb-1">Keputusan *</label>
                                    <select id="status_{{ $project->id }}" name="status" required
                                            class="w-full px-3 py-2 bg-[#f4f7fc] border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-hidden focus:border-rose-500">
                                        <option value="passed" {{ $project->status === 'passed' ? 'selected' : '' }}>🎉 LULUS</option>
                                        <option value="failed" {{ $project->status === 'failed' ? 'selected' : '' }}>❌ TIDAK LULUS</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="examiner_notes_{{ $project->id }}" class="block text-[11px] font-black text-slate-800 mb-1">Catatan Evaluasi / Saran Penguji</label>
                                <textarea id="examiner_notes_{{ $project->id }}" name="examiner_notes" rows="3"
                                          placeholder="Tuliskan catatan evaluasi penguasaan materi, tanya jawab, atau revisi yang harus dilengkapi..."
                                          class="w-full p-2.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold resize-none focus:outline-hidden focus:border-rose-500">{{ $project->examiner_notes }}</textarea>
                            </div>

                            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white font-black text-xs shadow-md active:scale-95 transition">
                                Simpan Nilai & Kelulusan (+25 Poin Siswa)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-2xl">🎓</div>
                <p>Belum ada jadwal ujian sidang yang menugaskan Anda sebagai Penguji.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
