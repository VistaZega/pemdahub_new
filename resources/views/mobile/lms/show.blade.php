@extends('mobile.layouts.app')

@section('title', $course->course_name . ' - LMS PembdaHUB Mobile')

@section('content')
@php
    $isTeacher = session('active_role') === 'guru' || 
                 auth()->user()?->isGuru() || 
                 $isTeacher;
@endphp
<div class="space-y-4" x-data="{ tab: '{{ request('tab', 'modul') }}', showAddModule: false, showAddMaterial: false, showAddAssignment: false, showAddQuiz: false }">
    <!-- Back Link & Mode Web Switcher -->
    <div class="flex items-center justify-between">
        <a href="{{ route('mobile.lms.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke LMS
        </a>
        <a href="{{ route('siswa.lms.show', ['course' => $course->id, 'switch_mode' => 'desktop']) }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-blue-200 text-blue-600 rounded-xl text-[11px] font-black shadow-xs hover:bg-blue-50 transition">
            <i class="fa-solid fa-desktop text-xs"></i> Buka Mode Web
        </a>
    </div>

    <!-- Course Banner Clay Card -->
    <div class="clay-purple p-6 relative overflow-hidden">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-white font-black text-2xl border-2 border-white shadow-md shrink-0">
                <i class="fa-solid fa-book-bookmark"></i>
            </div>
            <div>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full bg-white/30 text-white text-[9px] font-black border border-white/40 uppercase">
                        {{ $course->code }}
                    </span>
                    @if($course->is_sequential)
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-900 text-[9px] font-black border border-black uppercase shadow-xs">
                            🔒 Belajar Bertahap
                        </span>
                    @endif
                </div>
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

        <!-- Teacher Action Bar for Modules & Materials -->
        @if($isTeacher)
            <div class="space-y-2">
                <a href="{{ route('guru.lms.analytics', $course->id) }}" class="w-full py-2.5 px-3 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-900 text-white font-black text-xs text-center shadow-sm hover:from-slate-800 hover:to-indigo-800 transition flex items-center justify-center gap-2 border border-slate-700">
                    <i class="fa-solid fa-chart-pie text-cyan-400"></i> Buka Analitik &amp; Rekap WA Per Kelas
                </a>
                <div class="grid grid-cols-2 gap-2">
                    <button @click="showAddModule = !showAddModule" class="py-2.5 px-3 rounded-2xl bg-purple-600 text-white font-black text-xs text-center shadow-sm hover:bg-purple-700 transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-folder-plus"></i> <span x-text="showAddModule ? 'Batal' : '+ Tambah Modul'"></span>
                    </button>
                    <button @click="showAddMaterial = !showAddMaterial" class="py-2.5 px-3 rounded-2xl bg-emerald-600 text-white font-black text-xs text-center shadow-sm hover:bg-emerald-700 transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-file-circle-plus"></i> <span x-text="showAddMaterial ? 'Batal' : '+ Unggah Materi'"></span>
                    </button>
                </div>
            </div>

            <!-- Form Tambah Modul -->
            <div x-show="showAddModule" x-transition class="clay-card p-5 space-y-3 bg-purple-50 border-2 border-purple-200">
                <h4 class="text-xs font-black text-purple-900">➕ Buat Modul Pembelajaran Baru</h4>
                <form action="{{ route('mobile.lms.module.store', $course->id) }}" method="POST" class="space-y-2.5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Nama Modul</label>
                        <input type="text" name="title" required placeholder="Contoh: Bab 1 - Pengenalan Dasar" 
                               class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Deskripsi Ringkas</label>
                        <input type="text" name="description" placeholder="Penjelasan singkat modul..." 
                               class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-purple-600 text-white font-black text-xs rounded-xl shadow-sm hover:bg-purple-700 transition">
                        Simpan Modul
                    </button>
                </form>
            </div>

            <!-- Form Unggah Materi -->
            <div x-show="showAddMaterial" x-transition class="clay-card p-5 space-y-3 bg-emerald-50 border-2 border-emerald-200">
                <h4 class="text-xs font-black text-emerald-900">➕ Unggah / Buat Materi Baru</h4>
                <form action="{{ route('mobile.lms.material.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-2.5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Judul Materi</label>
                        <input type="text" name="title" required placeholder="Contoh: Slide Presentasi PDF / Video YouTube" 
                               class="w-full p-2.5 bg-white border-2 border-emerald-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-slate-700 uppercase">Pilih Modul</label>
                            <select name="module_id" class="w-full p-2.5 bg-white border-2 border-emerald-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                                <option value="">-- Materi Utama (Tanpa Modul) --</option>
                                @foreach($course->modules as $mod)
                                    <option value="{{ $mod->id }}">{{ $mod->name ?? $mod->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-700 uppercase">Tipe Materi</label>
                            <select name="material_type" required class="w-full p-2.5 bg-white border-2 border-emerald-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                                <option value="pdf">📄 PDF / Dokumen</option>
                                <option value="video">🎥 Video / YouTube</option>
                                <option value="text">📝 Teks Pembelajaran</option>
                                <option value="link">🔗 Link Eksternal</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Unggah Berkas PDF / Video (Opsional)</label>
                        <input type="file" name="file" class="w-full text-xs font-bold text-slate-600 bg-white border-2 border-emerald-200 rounded-xl p-2">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Link URL Video YouTube / Website (Opsional)</label>
                        <input type="url" name="file_url" placeholder="https://www.youtube.com/watch?v=..." 
                               class="w-full p-2.5 bg-white border-2 border-emerald-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Isi Teks / Catatan Tambahan (Opsional)</label>
                        <textarea name="content" rows="3" placeholder="Ringkasan materi..." class="w-full p-2.5 bg-white border-2 border-emerald-200 rounded-xl text-xs font-bold text-slate-900 outline-none resize-none"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 text-white font-black text-xs rounded-xl shadow-sm hover:bg-emerald-700 transition">
                        Simpan & Publikasikan Materi
                    </button>
                </form>
            </div>
        @endif

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
                        <div class="p-3.5 rounded-2xl bg-[#f4f7fc] border-2 border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <div class="flex items-start space-x-2.5 min-w-0">
                                <div class="mt-0.5 shrink-0">
                                    @if($mat->material_type === 'video' || $mat->isYouTubeVideo())
                                        <i class="fa-solid fa-circle-play text-red-500 text-base"></i>
                                    @elseif(in_array($mat->material_type, ['pdf', 'document']))
                                        <i class="fa-solid fa-file-pdf text-red-600 text-base"></i>
                                    @else
                                        <i class="fa-solid fa-file-lines text-purple-600 text-base"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-black text-slate-900 leading-snug break-words">{{ $mat->title }}</h4>
                                    <span class="inline-block text-[9px] font-black text-purple-700 uppercase bg-purple-100 px-2 py-0.5 rounded-md border border-purple-200 mt-1">
                                        {{ $mat->getContentTypeLabel() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200/60 w-full sm:w-auto justify-between sm:justify-end">
                                @if($isTeacher)
                                    <a href="{{ route('mobile.lms.material.delete', $mat->id) }}" 
                                       onclick="return confirm('Hapus materi ini?')"
                                       class="px-2.5 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-black hover:bg-rose-100 transition flex items-center gap-1">
                                        <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                    </a>
                                @endif
                                <a href="{{ route('mobile.lms.material', $mat->id) }}" class="clay-btn py-2 px-3 text-[11px] font-black text-white whitespace-nowrap shadow-sm">
                                    <i class="fa-solid fa-book-open mr-1"></i> Buka Materi
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Module Grouped Materials -->
        @forelse($course->modules as $module)
            <div class="clay-card p-4.5 space-y-3">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl clay-purple flex items-center justify-center font-black text-xs shrink-0">
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
                            <div class="p-3.5 rounded-2xl bg-[#f4f7fc] border-2 border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                <div class="flex items-start space-x-2.5 min-w-0">
                                    <div class="mt-0.5 shrink-0">
                                        @if($mat->material_type === 'video' || $mat->isYouTubeVideo())
                                            <i class="fa-solid fa-circle-play text-red-500 text-base"></i>
                                        @elseif(in_array($mat->material_type, ['pdf', 'document']))
                                            <i class="fa-solid fa-file-pdf text-red-600 text-base"></i>
                                        @else
                                            <i class="fa-solid fa-file-lines text-purple-600 text-base"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-black text-slate-900 leading-snug break-words">{{ $mat->title }}</h4>
                                        <span class="inline-block text-[9px] font-black text-purple-700 uppercase bg-purple-100 px-2 py-0.5 rounded-md border border-purple-200 mt-1">
                                            {{ $mat->getContentTypeLabel() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200/60 w-full sm:w-auto justify-between sm:justify-end">
                                    @if($isTeacher)
                                        <a href="{{ route('mobile.lms.material.delete', $mat->id) }}" 
                                           onclick="return confirm('Hapus materi ini?')"
                                           class="px-2.5 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-black hover:bg-rose-100 transition flex items-center gap-1">
                                            <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                        </a>
                                    @endif
                                    <a href="{{ route('mobile.lms.material', $mat->id) }}" class="clay-btn py-2 px-3 text-[11px] font-black text-white whitespace-nowrap shadow-sm">
                                        <i class="fa-solid fa-book-open mr-1"></i> Buka Materi
                                    </a>
                                </div>
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

        <!-- Teacher Action Bar for Assignments -->
        @if($isTeacher)
            <div>
                <button @click="showAddAssignment = !showAddAssignment" class="w-full py-2.5 px-3 rounded-2xl bg-amber-600 text-white font-black text-xs text-center shadow-sm hover:bg-amber-700 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus-circle"></i> <span x-text="showAddAssignment ? 'Batal' : '+ Buat Tugas Baru'"></span>
                </button>
            </div>

            <!-- Form Tambah Tugas -->
            <div x-show="showAddAssignment" x-transition class="clay-card p-5 space-y-3 bg-amber-50 border-2 border-amber-200">
                <h4 class="text-xs font-black text-amber-900">➕ Buat Tugas Baru</h4>
                <form action="{{ route('mobile.lms.assignment.store', $course->id) }}" method="POST" class="space-y-2.5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Judul Tugas</label>
                        <input type="text" name="title" required placeholder="Contoh: Tugas Mandiri Bab 1" 
                               class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Petunjuk & Instruksi Tugas</label>
                        <textarea name="description" rows="3" placeholder="Kerjakan soal berikut..." class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Metode Pengumpulan Jawaban Siswa</label>
                        <select name="assignment_type" class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                            <option value="file_text">📝 Teks + 📁 Upload File / Foto (Fleksibel - Rekomendasi)</option>
                            <option value="text">✍️ Hanya Teks / Essay</option>
                            <option value="file">📁 Hanya Upload File / Foto Lembar Jawaban</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Batas Waktu Pengumpulan (Deadline)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[9px] font-bold text-slate-500 block mb-0.5">Tanggal</span>
                                <input type="date" name="due_date_only" required value="{{ date('Y-m-d') }}"
                                       class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                            </div>
                            <div>
                                <span class="text-[9px] font-bold text-slate-500 block mb-0.5">Jam</span>
                                <input type="time" name="due_time_only" required value="23:59"
                                       class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-amber-600 text-white font-black text-xs rounded-xl shadow-sm hover:bg-amber-700 transition">
                        Simpan Tugas
                    </button>
                </form>
            </div>
        @endif

        @forelse($course->assignments as $assignment)
            @php 
                $sub = $submissionMap[$assignment->id] ?? null; 
                $myGroup = $studentGroupMap[$assignment->id] ?? null;
                $isGroupWork = $assignment->isGroupAssignment();
                $isLeader = $myGroup && $student && $myGroup->isLeader($student->id);
            @endphp
            <div class="clay-card p-5 space-y-3" x-data="{ openForm: false, showEdit: false }">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black clay-yellow">Tugas</span>
                            @if($isGroupWork)
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black bg-purple-100 text-purple-800 border border-purple-300 uppercase flex items-center gap-1 shadow-2xs">
                                    <i class="fa-solid fa-users text-[8px]"></i> Tugas Kelompok
                                </span>
                            @endif
                            @if($assignment->allow_resubmit)
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black bg-blue-100 text-blue-800 border border-blue-200 uppercase shadow-2xs">
                                    Revisi OK
                                </span>
                            @endif
                        </div>
                        <h4 class="text-xs font-black text-slate-900 mt-1 leading-snug">{{ $assignment->title }}</h4>
                        <p class="text-[10px] text-slate-500 font-bold mt-0.5">
                            <i class="fa-regular fa-clock text-yellow-600 mr-1"></i>Batas Waktu: {{ $assignment->deadline ? \Carbon\Carbon::parse($assignment->deadline)->translatedFormat('d M Y H:i') : ($assignment->due_date ?? '-') }}
                        </p>
                    </div>

                    <!-- Submission Status Badge for Student / Maker Badge for Teacher -->
                    @if($isTeacher)
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="showEdit = !showEdit" 
                                    class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 text-[10px] font-black border border-amber-300 hover:bg-amber-100 transition flex items-center gap-1">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <a href="{{ route('mobile.lms.assignment.delete', $assignment->id) }}" 
                               onclick="return confirm('Hapus tugas ini secara permanen?')"
                               class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 text-[10px] font-black border border-rose-200 hover:bg-rose-100 transition flex items-center gap-1">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </a>
                        </div>
                    @else
                        <div class="shrink-0">
                            @if($sub)
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase inline-block
                                    {{ $sub->status === 'graded' ? 'clay-green' : ($sub->status === 'late' ? 'clay-pink' : 'clay-blue') }}">
                                    {{ $sub->status === 'graded' ? 'Nilai: ' . $sub->score : ($sub->status === 'late' ? 'Terlambat' : 'Terkumpul') }}
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-600 border border-slate-200 inline-block">
                                    Belum Ada
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Status Pipeline on Mobile (Mirrored from Desktop for better UX) --}}
                @if(!$isTeacher)
                    @php
                        $pipelineStep = 0;
                        if($sub && $sub->status !== 'draft') $pipelineStep = 1;
                        if($sub && ($sub->status === 'graded' || $sub->score !== null)) $pipelineStep = 2;
                    @endphp
                    <div class="flex items-center justify-between py-2 px-3 bg-slate-50 rounded-xl border border-slate-200 text-[10px] font-black">
                        <div class="flex items-center gap-1.5 {{ $pipelineStep === 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-extrabold {{ $pipelineStep === 0 ? 'bg-amber-500 text-white shadow-xs' : 'bg-emerald-500 text-white' }}">
                                @if($pipelineStep > 0)<i class="fa-solid fa-check text-[8px]"></i>@else 1 @endif
                            </span>
                            <span>Belum</span>
                        </div>
                        <div class="flex-1 h-0.5 mx-2 {{ $pipelineStep >= 1 ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                        <div class="flex items-center gap-1.5 {{ $pipelineStep === 1 ? 'text-blue-600' : ($pipelineStep > 1 ? 'text-emerald-600' : 'text-slate-400') }}">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-extrabold {{ $pipelineStep === 1 ? 'bg-blue-500 text-white shadow-xs' : ($pipelineStep > 1 ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500') }}">
                                @if($pipelineStep > 1)<i class="fa-solid fa-check text-[8px]"></i>@else 2 @endif
                            </span>
                            <span>Dikumpulkan</span>
                        </div>
                        <div class="flex-1 h-0.5 mx-2 {{ $pipelineStep >= 2 ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                        <div class="flex items-center gap-1.5 {{ $pipelineStep >= 2 ? 'text-emerald-600' : 'text-slate-400' }}">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-extrabold {{ $pipelineStep >= 2 ? 'bg-emerald-500 text-white shadow-xs' : 'bg-slate-200 text-slate-500' }}">
                                @if($pipelineStep >= 2)<i class="fa-solid fa-check text-[8px]"></i>@else 3 @endif
                            </span>
                            <span>Dinilai</span>
                        </div>
                    </div>
                @endif

                {{-- Group Assignment Info Card on Mobile --}}
                @if($isGroupWork)
                    @if(!$isTeacher)
                        @if($myGroup)
                            <div class="p-3.5 rounded-2xl border-2 border-purple-200 bg-purple-50/90 space-y-2.5">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-1.5 font-black text-purple-950 text-xs">
                                        <i class="fa-solid fa-users text-purple-600"></i>
                                        <span>{{ $myGroup->name }}</span>
                                        @if($isLeader)
                                            <span class="bg-amber-400 text-slate-950 text-[9px] px-2 py-0.5 rounded-full border border-black uppercase font-black shadow-2xs">👑 Anda Ketua</span>
                                        @else
                                            <span class="bg-purple-200 text-purple-800 text-[9px] px-2 py-0.5 rounded-full uppercase font-black">Anggota</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-600">
                                        Ketua Kelompok: <strong class="text-slate-900">{{ $myGroup->leader?->user?->name ?? $myGroup->leader?->full_name ?? 'Belum Ditunjuk' }}</strong>
                                    </div>
                                </div>

                                @if(!empty($myGroup->theme))
                                    <div class="text-[11px] font-bold text-purple-950 bg-white/95 border border-purple-200 rounded-xl px-2.5 py-1.5 flex items-center gap-1.5">
                                        <i class="fa-solid fa-lightbulb text-amber-500 shrink-0"></i>
                                        <span><strong class="text-purple-700 uppercase text-[9px]">Tema / Proyek:</strong> {{ $myGroup->theme }}</span>
                                    </div>
                                @endif

                                <div class="text-[11px] text-slate-700 bg-white/80 p-2.5 rounded-xl border border-purple-100">
                                    <span class="font-black text-purple-900">Daftar Anggota Kelompok:</span>
                                    <span class="text-slate-800 font-bold ml-1">
                                        {{ $myGroup->members->pluck('user.name')->filter()->implode(', ') ?: ($myGroup->members->pluck('full_name')->filter()->implode(', ') ?: '—') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Group Submission Notice --}}
                            @if($sub && $sub->status !== 'draft')
                                <div class="p-3 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-900 text-[11px] font-medium flex items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                                    <div>
                                        <p class="font-black text-emerald-950">Tugas Kelompok Telah Terkumpul</p>
                                        <p class="text-emerald-800 mt-0.5 leading-snug">
                                            Sudah dikumpulkan oleh <strong class="text-slate-900">{{ $sub->student?->user?->name ?? $sub->student?->full_name ?? 'Anggota Kelompok' }}</strong>. Nilai dan feedback guru berlaku untuk seluruh anggota kelompok.
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="p-3 rounded-2xl border border-purple-200 bg-purple-50/60 text-purple-900 text-[11px] font-medium flex items-start gap-2">
                                    <i class="fa-solid fa-circle-info text-purple-600 text-sm mt-0.5 shrink-0"></i>
                                    <div>
                                        <p class="font-black text-purple-950">Info Pengumpulan Tugas Kelompok</p>
                                        <p class="text-purple-800 mt-0.5 leading-snug">
                                            Dapat dikumpulkan oleh Ketua Kelompok (<strong class="text-slate-900">{{ $myGroup->leader?->user?->name ?? $myGroup->leader?->full_name ?? 'Ketua' }}</strong>) atau anggota manapun. Cukup 1 siswa yang mengunggah tugas untuk seluruh kelompok.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="p-3.5 rounded-2xl border border-amber-300 bg-amber-50 text-amber-900 text-[11px] font-bold flex items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base shrink-0 mt-0.5"></i>
                                <span>Tugas ini diset sebagai Tugas Kelompok, namun Anda belum dimasukkan ke dalam kelompok oleh Guru. Anda tetap dapat mengumpulkan tugas secara mandiri melalui form di bawah.</span>
                            </div>
                        @endif
                    @else
                        {{-- Teacher overview for group assignment on mobile --}}
                        @if($assignment->groups && $assignment->groups->count() > 0)
                            <div x-data="{ showGroupsMobile: false }" class="space-y-2">
                                <button type="button" @click="showGroupsMobile = !showGroupsMobile" class="w-full py-2 px-3 bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 rounded-xl text-[11px] font-black flex items-center justify-between transition">
                                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-users text-purple-600"></i> Daftar Kelompok ({{ $assignment->groups->count() }} Kelompok)</span>
                                    <i class="fa-solid text-xs" :class="showGroupsMobile ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                </button>
                                <div x-show="showGroupsMobile" x-collapse class="space-y-2 pt-1">
                                    @foreach($assignment->groups as $grp)
                                        <div class="p-3 bg-white rounded-xl border border-purple-200 text-[11px] space-y-1">
                                            <div class="flex items-center justify-between font-black text-purple-950">
                                                <span>{{ $grp->name }}</span>
                                                <span class="text-[10px] text-slate-500 font-bold">Ketua: {{ $grp->leader?->user?->name ?? $grp->leader?->full_name ?? 'Belum Ditunjuk' }}</span>
                                            </div>
                                            @if(!empty($grp->theme))
                                                <div class="text-[10px] font-bold text-amber-700">Tema: {{ $grp->theme }}</div>
                                            @endif
                                            <div class="text-[10px] text-slate-600">
                                                <strong class="text-purple-900">Anggota:</strong>
                                                {{ $grp->members->pluck('user.name')->filter()->implode(', ') ?: ($grp->members->pluck('full_name')->filter()->implode(', ') ?: '—') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                @endif

                @if($assignment->description)
                    <div class="text-[11px] text-slate-700 font-medium bg-slate-50 p-3 rounded-2xl border border-slate-200 leading-relaxed">
                        {!! $assignment->description !!}
                    </div>
                @endif

                @if($assignment->file_path)
                    @php
                        $ext = strtolower(pathinfo($assignment->file_path, PATHINFO_EXTENSION));
                        $isPdf = $ext === 'pdf';
                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic']);
                        $fileUrl = Storage::disk('public')->url($assignment->file_path);
                    @endphp
                    @if($isImg)
                        <div class="w-full max-h-64 rounded-xl overflow-hidden bg-slate-900 flex justify-center p-1 mt-2 border border-slate-200">
                            <img src="{{ $fileUrl }}" class="max-h-60 object-contain rounded-lg" alt="Lampiran Soal">
                        </div>
                    @endif
                    <div class="p-3 rounded-xl border border-blue-100 bg-blue-50/50 flex flex-col gap-2 mt-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg {{ $isPdf ? 'bg-rose-500' : ($isImg ? 'bg-indigo-500' : 'bg-blue-500') }} text-white flex items-center justify-center shadow-sm shrink-0">
                                @if($isPdf)
                                    <i class="fa-solid fa-file-pdf"></i>
                                @elseif($isImg)
                                    <i class="fa-solid fa-image"></i>
                                @else
                                    <i class="fa-solid fa-file-alt"></i>
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-[10px]">Lampiran Soal / Instruksi Guru</p>
                                <p class="text-[9px] text-slate-500 font-medium uppercase">{{ strtoupper($ext) }} File</p>
                            </div>
                        </div>
                        <div class="mt-1">
                            <a href="{{ $fileUrl }}" target="_blank" class="w-full py-2 text-center rounded-lg bg-white border border-slate-200 text-slate-700 font-bold text-[10px] shadow-sm flex items-center justify-center gap-1">
                                <i class="fa-solid fa-eye"></i> Buka Berkas Penuh
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Submission Details / Feedback if graded for Student -->
                @if(!$isTeacher && $sub && $sub->score !== null)
                    <div class="p-3 bg-emerald-50 rounded-2xl border-2 border-emerald-200 text-xs space-y-1">
                        <span class="font-black text-emerald-900 block">✨ Nilai {{ $isGroupWork ? 'Kelompok' : 'Tugas' }}: {{ $sub->score }}/100</span>
                        @if($sub->feedback)
                            <p class="text-[11px] text-emerald-800 font-bold">Catatan Guru: "{{ $sub->feedback }}"</p>
                        @endif
                    </div>
                @endif

                {{-- Submitted files/text preview --}}
                @if(!$isTeacher && $sub && ($sub->submission_text || count($sub->file_list ?? [])))
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2">
                        <div class="flex items-center justify-between text-[11px] font-black text-slate-700">
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-paperclip text-purple-600"></i> {{ $isGroupWork ? 'Berkas / Jawaban Kelompok' : 'Berkas / Jawaban Anda' }}</span>
                            @if($isGroupWork && $sub->student)
                                <span class="text-[10px] text-purple-700 font-bold">(Oleh: {{ $sub->student->user->name ?? $sub->student->full_name }})</span>
                            @endif
                        </div>
                        @if($sub->submission_text)
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200 text-slate-800 font-medium text-[11px] whitespace-pre-wrap leading-relaxed">
                                {{ $sub->submission_text }}
                            </div>
                        @endif
                        @if(count($sub->file_list ?? []) > 0)
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                @foreach($sub->file_list as $fIdx => $fPath)
                                    @php 
                                        $isImg = \App\Models\LmsSubmission::isImagePath($fPath);
                                        $fUrl = Storage::disk('public')->url($fPath);
                                    @endphp
                                    <div class="p-2 bg-white rounded-xl border border-slate-200 flex flex-col items-center justify-between text-center gap-1.5">
                                        @if($isImg)
                                            <div class="w-full h-20 rounded-lg overflow-hidden bg-slate-900 flex items-center justify-center cursor-pointer" onclick="window.open('{{ $fUrl }}', '_blank')">
                                                <img src="{{ $fUrl }}" class="max-h-full max-w-full object-contain" alt="Berkas">
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-700 truncate w-full">Foto {{ $fIdx + 1 }}</span>
                                        @else
                                            <div class="w-full h-20 rounded-lg bg-rose-50 flex items-center justify-center border border-rose-100">
                                                <i class="fa-solid fa-file-pdf text-2xl text-rose-600"></i>
                                            </div>
                                            <span class="text-[10px] font-bold text-rose-700 truncate w-full">Dokumen PDF</span>
                                        @endif
                                        <a href="{{ $fUrl }}" target="_blank" class="w-full py-1 text-center rounded-lg bg-slate-100 text-blue-600 font-bold text-[10px] hover:bg-blue-50 transition">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka
                                        </a>
                                    </div>
                                @endforeach
                            </div>
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

                    <!-- FORM EDIT TUGAS UNTUK GURU -->
                    <div x-show="showEdit" x-transition class="pt-3 border-t-2 border-amber-300 space-y-3 bg-amber-50/90 p-4 rounded-2xl border border-amber-200">
                        <h4 class="text-xs font-black text-amber-900">✏️ Edit Tugas LMS</h4>
                        <form action="{{ route('mobile.lms.assignment.update', $assignment->id) }}" method="POST" class="space-y-2.5">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-[10px] font-black text-slate-700 uppercase">Judul Tugas</label>
                                <input type="text" name="title" value="{{ $assignment->title }}" required 
                                       class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-700 uppercase">Petunjuk & Instruksi Tugas</label>
                                <textarea name="description" rows="3" class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none resize-none">{{ $assignment->description }}</textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-700 uppercase">Metode Pengumpulan Jawaban Siswa</label>
                                <select name="assignment_type" class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                                    <option value="file_text" {{ ($assignment->assignment_type ?? 'file_text') === 'file_text' ? 'selected' : '' }}>📝 Teks + 📁 Upload File / Foto (Fleksibel - Rekomendasi)</option>
                                    <option value="text" {{ $assignment->assignment_type === 'text' ? 'selected' : '' }}>✍️ Hanya Teks / Essay</option>
                                    <option value="file" {{ $assignment->assignment_type === 'file' ? 'selected' : '' }}>📁 Hanya Upload File / Foto Lembar Jawaban</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Batas Waktu Pengumpulan (Deadline)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-[9px] font-bold text-slate-500 block mb-0.5">Tanggal</span>
                                        <input type="date" name="due_date_only" value="{{ $assignment->deadline ? \Carbon\Carbon::parse($assignment->deadline)->format('Y-m-d') : date('Y-m-d') }}"
                                               class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-bold text-slate-500 block mb-0.5">Jam</span>
                                        <input type="time" name="due_time_only" value="{{ $assignment->deadline ? \Carbon\Carbon::parse($assignment->deadline)->format('H:i') : '23:59' }}"
                                               class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button type="button" @click="showEdit = false" class="flex-1 py-2 bg-slate-200 text-slate-700 font-black text-xs rounded-xl">Batal</button>
                                <button type="submit" class="flex-[2] py-2 bg-amber-600 text-white font-black text-xs rounded-xl shadow-xs hover:bg-amber-700 transition">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Toggle Submit Form Button for Student -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] text-slate-500 font-bold">
                            @if($sub)
                                Terkumpul: {{ \Carbon\Carbon::parse($sub->submitted_at)->diffForHumans() }}
                                @if($isGroupWork && $sub->student)
                                    (oleh {{ explode(' ', trim($sub->student->user->name ?? $sub->student->full_name))[0] }})
                                @endif
                            @else
                                Belum dikirim
                            @endif
                        </span>
                        @if(!$sub || $assignment->allow_resubmit)
                            <button @click="openForm = !openForm" class="clay-btn py-2 px-3.5 text-xs font-black text-white shadow-sm">
                                <span x-text="openForm ? 'Tutup Form' : '{{ $sub ? '📤 Kumpul Ulang' : '✏️ Kirim Jawaban' }}'"></span>
                            </button>
                        @else
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold border border-slate-200">
                                🔒 Selesai
                            </span>
                        @endif
                    </div>

                    <!-- SUBMISSION FORM FOR STUDENT (FLEXIBLE TYPES) -->
                    <div x-show="openForm" x-transition class="pt-3 border-t-2 border-purple-100 space-y-3">
                        <form action="{{ route('mobile.lms.assignment.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return handleMobileAssignmentSubmit(this)" class="space-y-3">
                            @csrf
                            
                            @if(in_array($assignment->assignment_type, ['text', 'file_text', null, '']))
                                <div>
                                    <label class="block text-xs font-black text-slate-800 mb-1">Teks Jawaban / Keterangan {{ $assignment->assignment_type === 'text' ? '*' : '(Opsional)' }}</label>
                                    <textarea name="submission_text" rows="3" {{ $assignment->assignment_type === 'text' ? 'required' : '' }} placeholder="Ketik penjelasan atau jawaban tugas Anda di sini..."
                                              class="w-full p-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-xs font-bold text-slate-900 outline-none focus:border-purple-500 transition resize-none">{{ $sub->submission_text ?? '' }}</textarea>
                                </div>
                            @endif

                            @if(in_array($assignment->assignment_type, ['file', 'file_text', null, '']))
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-black text-slate-800">Unggah Berkas Tugas {{ $assignment->assignment_type === 'file' ? '*' : '(Opsional)' }}</label>
                                        <span class="text-[10px] font-black px-2 py-0.5 rounded-md bg-purple-100 text-purple-700">PDF / Foto</span>
                                    </div>

                                    <div class="mobile-lms-file-wrapper space-y-2 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl p-3"
                                         data-assignment-id="{{ $assignment->id }}"
                                         data-file-required="{{ $assignment->assignment_type === 'file' ? '1' : '0' }}"
                                         data-has-existing="{{ ($sub && count($sub->file_list)) ? '1' : '0' }}">
                                        
                                        {{-- Hidden input for final form submit --}}
                                        <input type="file" name="files[]" class="mobile-lms-main-file hidden" multiple>

                                        <div class="grid grid-cols-2 gap-2">
                                            {{-- PDF Picker (Triggers File Manager directly) --}}
                                            <label class="p-2.5 rounded-xl bg-white border border-rose-200 text-rose-700 font-extrabold text-[11px] flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-95 transition">
                                                <i class="fa-solid fa-file-pdf text-rose-600"></i>
                                                <span>Pilih PDF</span>
                                                <input type="file" multiple accept="application/pdf,.pdf" onchange="handleMobileFileSelection(this)" class="hidden">
                                            </label>

                                            {{-- Gallery Picker (Triggers Album / Photos) --}}
                                            <label class="p-2.5 rounded-xl bg-white border border-purple-200 text-purple-700 font-extrabold text-[11px] flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-95 transition">
                                                <i class="fa-solid fa-images text-purple-600"></i>
                                                <span>Pilih Galeri</span>
                                                <input type="file" multiple accept="image/*" onchange="handleMobileFileSelection(this)" class="hidden">
                                            </label>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2">
                                            {{-- Native Camera --}}
                                            <label class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-extrabold text-[11px] flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-95 transition">
                                                <i class="fa-solid fa-camera text-emerald-600"></i>
                                                <span>Kamera HP</span>
                                                <input type="file" accept="image/*" capture="environment" onchange="handleMobileFileSelection(this)" class="hidden">
                                            </label>

                                            {{-- All Files / File Manager --}}
                                            <label class="p-2.5 rounded-xl bg-slate-100 border border-slate-300 text-slate-700 font-extrabold text-[11px] flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-95 transition">
                                                <i class="fa-solid fa-folder-open text-slate-600"></i>
                                                <span>Semua Berkas</span>
                                                <input type="file" multiple accept="*/*" onchange="handleMobileFileSelection(this)" class="hidden">
                                            </label>
                                        </div>

                                        {{-- Queue preview container --}}
                                        <div class="mobile-queue-container hidden space-y-1.5 pt-2 border-t border-slate-200">
                                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                                <span>Berkas Dipilih (<span class="mobile-queue-count text-purple-700 font-black">0</span>):</span>
                                                <button type="button" onclick="clearMobileFiles(this)" class="text-[10px] text-rose-600 hover:underline font-bold">Hapus Semua</button>
                                            </div>
                                            <div class="mobile-queue-list grid grid-cols-1 sm:grid-cols-2 gap-1.5"></div>
                                        </div>

                                        <p class="text-[10px] text-purple-700 font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-circle-info"></i> Gunakan tombol <strong>Pilih PDF</strong> untuk membuka File Manager HP, atau <strong>Pilih Galeri</strong> untuk foto.
                                        </p>
                                    </div>
                                </div>
                            @endif

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

        <!-- Teacher Action Bar for Quizzes -->
        @if($isTeacher)
            <div>
                <button @click="showAddQuiz = !showAddQuiz" class="w-full py-2.5 px-3 rounded-2xl bg-pink-600 text-white font-black text-xs text-center shadow-sm hover:bg-pink-700 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus-circle"></i> <span x-text="showAddQuiz ? 'Batal' : '+ Buat Kuis Baru'"></span>
                </button>
            </div>

            <!-- Form Tambah Kuis -->
            <div x-show="showAddQuiz" x-transition class="clay-card p-5 space-y-3 bg-pink-50 border-2 border-pink-200">
                <h4 class="text-xs font-black text-pink-900">➕ Buat Kuis Baru</h4>
                <form action="{{ route('mobile.lms.quiz.store', $course->id) }}" method="POST" class="space-y-2.5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Judul Kuis</label>
                        <input type="text" name="title" required placeholder="Contoh: Kuis Harian Bab 1" 
                               class="w-full p-2.5 bg-white border-2 border-pink-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Petunjuk Kuis</label>
                        <input type="text" name="description" placeholder="Pilihlah satu jawaban paling tepat..." 
                               class="w-full p-2.5 bg-white border-2 border-pink-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Durasi Pengerjaan (Menit)</label>
                        <input type="number" name="time_limit" value="30" min="1" required 
                               class="w-full p-2.5 bg-white border-2 border-pink-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-pink-600 text-white font-black text-xs rounded-xl shadow-sm hover:bg-pink-700 transition">
                        Simpan Kuis
                    </button>
                </form>
            </div>
        @endif

        @forelse($course->quizzes as $quiz)
            @php 
                $attempts = $attemptMap[$quiz->id] ?? collect(); 
                $latestFinished = $attempts->whereNotNull('finished_at')->sortByDesc('finished_at')->first();
            @endphp
            <div class="clay-card p-5 space-y-3" x-data="{ showEditQuiz: false }">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black clay-pink">Kuis LMS</span>
                        <h4 class="text-xs font-black text-slate-900 mt-1">{{ $quiz->title }}</h4>
                        <p class="text-[10px] text-slate-500 font-bold mt-0.5"><i class="fa-regular fa-clock text-pink-600 mr-1"></i>Durasi: {{ $quiz->time_limit ?? 30 }} menit</p>
                    </div>

                    @if($isTeacher)
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="showEditQuiz = !showEditQuiz" 
                                    class="px-2.5 py-1 rounded-lg bg-pink-50 text-pink-800 text-[10px] font-black border border-pink-300 hover:bg-pink-100 transition flex items-center gap-1">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <a href="{{ route('mobile.lms.quiz.delete', $quiz->id) }}" 
                               onclick="return confirm('Hapus kuis ini secara permanen?')"
                               class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 text-[10px] font-black border border-rose-200 hover:bg-rose-100 transition flex items-center gap-1">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </a>
                        </div>
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

                    <!-- FORM EDIT KUIS UNTUK GURU -->
                    <div x-show="showEditQuiz" x-transition class="pt-3 border-t-2 border-pink-300 space-y-3 bg-pink-50/90 p-4 rounded-2xl border border-pink-200">
                        <h4 class="text-xs font-black text-pink-900">✏️ Edit Kuis LMS</h4>
                        <form action="{{ route('mobile.lms.quiz.update', $quiz->id) }}" method="POST" class="space-y-2.5">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-[10px] font-black text-slate-700 uppercase">Judul Kuis</label>
                                <input type="text" name="title" value="{{ $quiz->title }}" required 
                                       class="w-full p-2.5 bg-white border-2 border-pink-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-700 uppercase">Petunjuk Kuis</label>
                                <input type="text" name="description" value="{{ $quiz->description }}" 
                                       class="w-full p-2.5 bg-white border-2 border-pink-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-700 uppercase">Durasi Pengerjaan (Menit)</label>
                                <input type="number" name="time_limit" value="{{ $quiz->time_limit ?? 30 }}" min="1" required 
                                       class="w-full p-2.5 bg-white border-2 border-pink-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button type="button" @click="showEditQuiz = false" class="flex-1 py-2 bg-slate-200 text-slate-700 font-black text-xs rounded-xl">Batal</button>
                                <button type="submit" class="flex-[2] py-2 bg-pink-600 text-white font-black text-xs rounded-xl shadow-xs hover:bg-pink-700 transition">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Student Action View -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                        @php
                            $canAttemptQuiz = $student ? $quiz->canAttempt($student->id) : true;
                            $hasActiveAttempt = $student && $attempts->whereNull('finished_at')->isNotEmpty();
                        @endphp

                        @if($latestFinished)
                            <a href="{{ route('mobile.lms.quiz.result', $latestFinished->id) }}" class="px-3 py-2 bg-slate-100 text-slate-800 text-xs font-black rounded-xl border border-slate-200">
                                📊 Lihat Hasil
                            </a>
                        @else
                            <span class="text-[10px] text-slate-500 font-bold">Belum dikerjakan</span>
                        @endif

                        @if($canAttemptQuiz || $hasActiveAttempt)
                            <a href="{{ route('mobile.lms.quiz.start', $quiz->id) }}" class="clay-btn py-2.5 px-4 text-xs font-black text-white shadow-md">
                                <i class="fa-solid fa-pen-nib mr-1"></i> {{ $hasActiveAttempt ? 'Lanjutkan Kuis' : ($latestFinished ? 'Ulangi Kuis' : 'Mulai Kerjakan Kuis') }}
                            </a>
                        @else
                            <span class="px-3 py-2 bg-slate-100 text-slate-500 text-xs font-black rounded-xl border border-slate-200">
                                <i class="fa-solid fa-lock mr-1"></i> Batas Percobaan Habis
                            </span>
                        @endif
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

@push('scripts')
<script>
function getMobileWrapper(el) {
    if (!el) return document.querySelector('.mobile-lms-file-wrapper');
    return el.closest('.mobile-lms-file-wrapper') || document.querySelector('.mobile-lms-file-wrapper');
}

function getMobileStore(wrapper) {
    if (!wrapper) return { files: [] };
    if (!wrapper._filesStore) {
        wrapper._filesStore = { files: [] };
    }
    return wrapper._filesStore;
}

function handleMobileFileSelection(input) {
    if (!input || !input.files) return;
    const wrapper = getMobileWrapper(input);
    if (!wrapper) return;
    const store = getMobileStore(wrapper);

    const allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif'];
    for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];
        const ext = (file.name.split('.').pop() || '').toLowerCase();

        if (!allowed.includes(ext)) {
            alert('⚠️ FORMAT BERKAS DITOLAK!\n\nBerkas "' + file.name + '" berekstensi .' + ext + ' tidak diizinkan.\nHarap pilih berkas PDF atau Gambar (JPG, PNG, WEBP).');
            continue;
        }

        const maxBytes = 25 * 1024 * 1024;
        if (file.size > maxBytes) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
            alert('⚠️ UKURAN BERKAS TERLALU BESAR!\n\nUkuran berkas "' + file.name + '" (' + sizeMB + ' MB) melebihi batas maksimal 25 MB.');
            continue;
        }

        const isDuplicate = store.files.some(f => f.name === file.name && f.size === file.size);
        if (!isDuplicate) {
            store.files.push(file);
        }
    }
    input.value = '';
    renderMobileQueue(wrapper);
}

function removeMobileFile(btn, index) {
    const wrapper = getMobileWrapper(btn);
    if (!wrapper) return;
    const store = getMobileStore(wrapper);
    store.files.splice(index, 1);
    renderMobileQueue(wrapper);
}

function clearMobileFiles(btn) {
    const wrapper = getMobileWrapper(btn);
    if (!wrapper) return;
    const store = getMobileStore(wrapper);
    store.files = [];
    renderMobileQueue(wrapper);
}

function renderMobileQueue(wrapper) {
    if (!wrapper) return;
    const store = getMobileStore(wrapper);
    const container = wrapper.querySelector('.mobile-queue-container');
    const list = wrapper.querySelector('.mobile-queue-list');
    const count = wrapper.querySelector('.mobile-queue-count');

    if (!container || !list || !count) return;

    count.innerText = store.files.length;
    list.innerHTML = '';

    if (store.files.length === 0) {
        container.classList.add('hidden');
        return;
    }
    container.classList.remove('hidden');

    store.files.forEach((file, idx) => {
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        const isImg = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif'].includes(ext);
        const card = document.createElement('div');
        card.className = 'bg-white p-2 rounded-xl border border-slate-200 flex items-center justify-between gap-1 shadow-2xs';

        const sizeKB = (file.size / 1024).toFixed(0);
        const sizeText = file.size > 1024 * 1024 ? (file.size / (1024 * 1024)).toFixed(1) + 'MB' : sizeKB + 'KB';

        card.innerHTML = `
            <div class="flex items-center gap-1.5 min-w-0">
                <i class="fa-solid ${isImg ? 'fa-image text-purple-600' : 'fa-file-pdf text-rose-600'} text-sm shrink-0"></i>
                <div class="truncate">
                    <p class="text-[10px] font-bold text-slate-800 truncate" title="${file.name}">${file.name}</p>
                    <span class="text-[9px] text-slate-400 font-semibold">${sizeText}</span>
                </div>
            </div>
            <button type="button" onclick="removeMobileFile(this, ${idx})" class="text-[10px] text-rose-500 font-extrabold hover:text-rose-700 px-1">✕</button>
        `;
        list.appendChild(card);
    });
}

function handleMobileAssignmentSubmit(form) {
    const wrapper = form.querySelector('.mobile-lms-file-wrapper');
    if (wrapper) {
        const store = getMobileStore(wrapper);
        const mainInput = wrapper.querySelector('.mobile-lms-main-file');
        if (store.files.length > 0 && mainInput) {
            try {
                const dt = new DataTransfer();
                store.files.forEach(f => dt.items.add(f));
                mainInput.files = dt.files;
            } catch (e) {
                console.warn('DataTransfer error:', e);
            }
        }

        const isRequired = wrapper.dataset.fileRequired === '1';
        const hasExisting = wrapper.dataset.hasExisting === '1';
        if (isRequired && store.files.length === 0 && !hasExisting) {
            alert('⚠️ Anda belum memilih berkas jawaban!\n\nSilakan pilih Dokumen PDF atau Foto tugas terlebih dahulu.');
            return false;
        }
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim Jawaban...';
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    }
    return true;
}

// Backward compatibility alias
function validateMobileLmsFiles(input) { return handleMobileFileSelection(input); }
function validateMobileLmsPdf(input) { return handleMobileFileSelection(input); }
</script>
@endpush
