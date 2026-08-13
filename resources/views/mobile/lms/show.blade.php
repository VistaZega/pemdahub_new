@extends('mobile.layouts.app')

@section('title', $course->course_name . ' - LMS PembdaHUB Mobile')

@section('content')
@php
    $isTeacher = session('active_role') === 'guru' || 
                 auth()->user()?->isGuru() || 
                 ($course->teacher_id == auth()->id());
@endphp
<div class="space-y-4" x-data="{ tab: 'modul' }">
    <!-- Back Link -->
    <a href="{{ route('mobile.lms.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke LMS
    </a>

    <!-- Course Banner Clay Card -->
    <div class="clay-purple p-6 relative overflow-hidden">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-white font-black text-2xl border-2 border-white shadow-md">
                <i class="fa-solid fa-book-bookmark"></i>
            </div>
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-white/30 text-white text-[9px] font-black border border-white/40 uppercase">
                    {{ $course->code }}
                </span>
                <h2 class="text-base font-black text-white mt-1 leading-snug tracking-tight">{{ $course->course_name }}</h2>
                <p class="text-xs text-purple-100 mt-0.5 font-bold">
                    <i class="fa-regular fa-user mr-1"></i>{{ $course->teacher->full_name ?? 'Pengajar' }}
                    @if($isTeacher)
                        <span class="ml-1.5 px-2 py-0.5 rounded-md bg-white/20 text-white text-[9px] font-black uppercase">Pengajar (Maker)</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Navigation Clay Tabs -->
    <div class="flex items-center space-x-2 border-b-2 border-slate-200/80 pb-2">
        <button @click="tab = 'modul'" 
                :class="tab === 'modul' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="flex-1 py-2.5 px-3 rounded-2xl text-xs transition text-center">
            📚 Modul
        </button>
        <button @click="tab = 'tugas'" 
                :class="tab === 'tugas' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="flex-1 py-2.5 px-3 rounded-2xl text-xs transition text-center">
            📝 Tugas ({{ count($course->assignments ?? []) }})
        </button>
        <button @click="tab = 'kuis'" 
                :class="tab === 'kuis' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="flex-1 py-2.5 px-3 rounded-2xl text-xs transition text-center">
            ❓ Kuis ({{ count($course->quizzes ?? []) }})
        </button>
    </div>

    <!-- Tab 1: Modules & Materials -->
    <div x-show="tab === 'modul'" class="space-y-3">
        <!-- Direct Materials -->
        @php
            $directMaterials = $course->materials->whereNull('module_id');
        @endphp
        @if($directMaterials->count() > 0)
            <div class="clay-card p-4 space-y-3">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-folder-open text-purple-600"></i> Materi Utama Kursus
                </h3>

                <div class="space-y-2">
                    @foreach($directMaterials as $mat)
                        <div class="p-3.5 rounded-2xl bg-[#f4f7fc] border-2 border-slate-200/80 flex items-center justify-between gap-3">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                @if($mat->material_type === 'video' || $mat->isYouTubeVideo())
                                    <i class="fa-solid fa-circle-play text-red-500 text-base"></i>
                                @elseif(in_array($mat->material_type, ['pdf', 'document']))
                                    <i class="fa-solid fa-file-pdf text-red-600 text-base"></i>
                                @else
                                    <i class="fa-solid fa-file-lines text-purple-600 text-base"></i>
                                @endif
                                <div>
                                    <h4 class="text-xs font-black text-slate-900 truncate">{{ $mat->title }}</h4>
                                    <span class="text-[9px] font-black text-purple-700 uppercase bg-purple-100 px-2 py-0.5 rounded-md border border-purple-200">
                                        {{ $mat->getContentTypeLabel() }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('mobile.lms.material', $mat->id) }}" class="clay-btn py-2 px-3 text-[11px] font-black text-white whitespace-nowrap shadow-sm">
                                <i class="fa-solid fa-book-open mr-1"></i> Buka Materi
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Module Grouped Materials -->
        @forelse($course->modules as $module)
            <div class="clay-card p-4.5 space-y-3">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl clay-purple flex items-center justify-center font-black text-xs">
                        {{ $loop->iteration }}
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900">{{ $module->name ?? $module->title }}</h3>
                        @if($module->description)
                            <p class="text-[10px] text-slate-500 font-bold">{{ $module->description }}</p>
                        @endif
                    </div>
                </div>

                @if(isset($module->materials) && count($module->materials) > 0)
                    <div class="space-y-2 pl-2 border-l-4 border-purple-300">
                        @foreach($module->materials as $mat)
                            <div class="p-3.5 rounded-2xl bg-[#f4f7fc] border-2 border-slate-200/80 flex items-center justify-between gap-3">
                                <div class="flex items-center space-x-2.5 min-w-0">
                                    @if($mat->material_type === 'video' || $mat->isYouTubeVideo())
                                        <i class="fa-solid fa-circle-play text-red-500 text-base"></i>
                                    @elseif(in_array($mat->material_type, ['pdf', 'document']))
                                        <i class="fa-solid fa-file-pdf text-red-600 text-base"></i>
                                    @else
                                        <i class="fa-solid fa-file-lines text-purple-600 text-base"></i>
                                    @endif
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 truncate">{{ $mat->title }}</h4>
                                        <span class="text-[9px] font-black text-purple-700 uppercase bg-purple-100 px-2 py-0.5 rounded-md border border-purple-200">
                                            {{ $mat->getContentTypeLabel() }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('mobile.lms.material', $mat->id) }}" class="clay-btn py-2 px-3 text-[11px] font-black text-white whitespace-nowrap shadow-sm">
                                    <i class="fa-solid fa-book-open mr-1"></i> Buka Materi
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 italic pl-2 font-bold">Belum ada materi di modul ini.</p>
                @endif
            </div>
        @empty
            @if($directMaterials->count() == 0)
                <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                    <i class="fa-solid fa-book-open-reader text-3xl mb-2 text-purple-400"></i>
                    <p>Belum ada modul atau materi pembelajaran yang diunggah.</p>
                </div>
            @endif
        @endforelse
    </div>

    <!-- Tab 2: Assignments (Interactive Submission Form for Student vs Teacher Maker View) -->
    <div x-show="tab === 'tugas'" class="space-y-3">
        @forelse($course->assignments as $assignment)
            @php $sub = $submissionMap[$assignment->id] ?? null; @endphp
            <div class="clay-card p-5 space-y-3" x-data="{ openForm: false }">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black clay-yellow">Tugas</span>
                        <h4 class="text-xs font-black text-slate-900 mt-1">{{ $assignment->title }}</h4>
                        <p class="text-[10px] text-slate-500 font-bold mt-0.5"><i class="fa-regular fa-clock text-yellow-600 mr-1"></i>Batas Waktu: {{ $assignment->due_date ?? '-' }}</p>
                    </div>

                    <!-- Submission Status Badge for Student / Maker Badge for Teacher -->
                    @if($isTeacher)
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">
                            Pengajar (Maker)
                        </span>
                    @else
                        @if($sub)
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                {{ $sub->status === 'graded' ? 'clay-green' : ($sub->status === 'late' ? 'clay-pink' : 'clay-blue') }}">
                                {{ $sub->status === 'graded' ? 'Nilai: ' . $sub->score : ($sub->status === 'late' ? 'Terlambat' : 'Terkumpul') }}
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-600 border border-slate-200">
                                Belum Ada
                            </span>
                        @endif
                    @endif
                </div>

                @if($assignment->description)
                    <div class="text-[11px] text-slate-700 font-medium bg-slate-50 p-3 rounded-2xl border border-slate-200 leading-relaxed">
                        {!! $assignment->description !!}
                    </div>
                @endif

                <!-- Submission Details / Feedback if graded for Student -->
                @if(!$isTeacher && $sub && $sub->score !== null)
                    <div class="p-3 bg-emerald-50 rounded-2xl border-2 border-emerald-200 text-xs space-y-1">
                        <span class="font-black text-emerald-900 block">✨ Nilai Tugas: {{ $sub->score }}/100</span>
                        @if($sub->feedback)
                            <p class="text-[11px] text-emerald-800 font-bold">Catatan Guru: "{{ $sub->feedback }}"</p>
                        @endif
                    </div>
                @endif

                <!-- Action Button for Teacher vs Student -->
                @if($isTeacher)
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] text-purple-700 font-extrabold flex items-center gap-1">
                            <i class="fa-solid fa-user-shield"></i> Mode Pengajar / Pembuat Tugas
                        </span>
                        <a href="{{ route('mobile.guru.tugas') }}" class="clay-btn py-2 px-3.5 text-xs font-black text-white shadow-sm">
                            <i class="fa-solid fa-list-check mr-1"></i> Periksa & Nilai Tugas Siswa
                        </a>
                    </div>
                @else
                    <!-- Toggle Submit Form Button for Student -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] text-slate-500 font-bold">
                            {{ $sub ? 'Terkumpul: ' . \Carbon\Carbon::parse($sub->submitted_at)->diffForHumans() : 'Belum dikirim' }}
                        </span>
                        <button @click="openForm = !openForm" class="clay-btn py-2 px-3.5 text-xs font-black text-white shadow-sm">
                            <span x-text="openForm ? 'Tutup Form' : '{{ $sub ? '📤 Kumpul Ulang' : '✏️ Kirim Jawaban' }}'"></span>
                        </button>
                    </div>

                    <!-- SUBMISSION FORM FOR STUDENT -->
                    <div x-show="openForm" x-transition class="pt-3 border-t-2 border-purple-100 space-y-3">
                        <form action="{{ route('mobile.lms.assignment.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-black text-slate-800 mb-1">Teks Jawaban / Link URL</label>
                                <textarea name="submission_text" rows="3" placeholder="Ketik penjelasan jawaban atau sertakan link Google Drive / URL tugas Anda..."
                                          class="w-full p-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-xs font-bold text-slate-900 outline-none focus:border-purple-500 transition resize-none">{{ $sub->submission_text ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-slate-800 mb-1">Unggah Lampiran Berkas (Opsional, Max 10MB)</label>
                                <input type="file" name="file" 
                                       class="w-full text-xs font-bold text-slate-600 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl p-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-purple-600 file:text-white">
                            </div>

                            <button type="submit" class="clay-btn w-full py-3.5 text-white font-black text-xs uppercase tracking-wider shadow-md">
                                <i class="fa-solid fa-paper-plane mr-1"></i> Kirim Jawaban Sekarang
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Tidak ada tugas aktif saat ini.
            </div>
        @endforelse
    </div>

    <!-- Tab 3: Quizzes (Interactive Quiz for Student vs Teacher Maker View) -->
    <div x-show="tab === 'kuis'" class="space-y-3">
        @forelse($course->quizzes as $quiz)
            @php 
                $attempts = $attemptMap[$quiz->id] ?? collect(); 
                $latestFinished = $attempts->whereNotNull('finished_at')->sortByDesc('finished_at')->first();
            @endphp
            <div class="clay-card p-5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black clay-pink">Kuis LMS</span>
                        <h4 class="text-xs font-black text-slate-900 mt-1">{{ $quiz->title }}</h4>
                        <p class="text-[10px] text-slate-500 font-bold mt-0.5"><i class="fa-regular fa-clock text-pink-600 mr-1"></i>Durasi: {{ $quiz->time_limit ?? 30 }} menit</p>
                    </div>

                    @if($isTeacher)
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">
                            Pengajar (Maker)
                        </span>
                    @else
                        @if($latestFinished)
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $latestFinished->is_passed ? 'clay-green' : 'clay-pink' }}">
                                {{ number_format($latestFinished->score, 1) }}%
                            </span>
                        @endif
                    @endif
                </div>

                @if($isTeacher)
                    <!-- Teacher Maker Action View -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                        <span class="text-[10px] text-purple-700 font-extrabold flex items-center gap-1">
                            <i class="fa-solid fa-user-shield"></i> Mode Pengajar / Pembuat Kuis
                        </span>
                        <a href="{{ route('mobile.guru.cbt') }}" class="clay-btn py-2.5 px-4 text-xs font-black text-white shadow-md">
                            <i class="fa-solid fa-chart-line mr-1"></i> Kelola & Lihat Hasil CBT
                        </a>
                    </div>
                @else
                    <!-- Student Action View -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                        @if($latestFinished)
                            <a href="{{ route('mobile.lms.quiz.result', $latestFinished->id) }}" class="px-3 py-2 bg-slate-100 text-slate-800 text-xs font-black rounded-xl border border-slate-200">
                                📊 Lihat Hasil
                            </a>
                        @else
                            <span class="text-[10px] text-slate-500 font-bold">Belum dikerjakan</span>
                        @endif

                        <a href="{{ route('mobile.lms.quiz.start', $quiz->id) }}" class="clay-btn py-2.5 px-4 text-xs font-black text-white shadow-md">
                            <i class="fa-solid fa-pen-nib mr-1"></i> {{ $latestFinished ? 'Ulangi Kuis' : 'Mulai Kerjakan Kuis' }}
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Tidak ada kuis aktif saat ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
