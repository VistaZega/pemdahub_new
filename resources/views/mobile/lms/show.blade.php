@extends('mobile.layouts.app')

@section('title', $course->course_name . ' - LMS PembdaHUB Mobile')

@section('content')
@php
    $isTeacher = session('active_role') === 'guru' || 
                 auth()->user()?->isGuru() || 
                 ($course->teacher_id == auth()->id());
@endphp
<div class="space-y-4" x-data="{ tab: 'modul', showAddModule: false, showAddMaterial: false, showAddAssignment: false, showAddQuiz: false }">
    <!-- Back Link -->
    <a href="{{ route('mobile.lms.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke LMS
    </a>

    <!-- Course Banner Clay Card -->
    <div class="clay-purple p-6 relative overflow-hidden">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-white font-black text-2xl border-2 border-white shadow-md shrink-0">
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

        <!-- Teacher Action Bar for Modules & Materials -->
        @if($isTeacher)
            <div class="grid grid-cols-2 gap-2">
                <button @click="showAddModule = !showAddModule" class="py-2.5 px-3 rounded-2xl bg-purple-600 text-white font-black text-xs text-center shadow-sm hover:bg-purple-700 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-folder-plus"></i> <span x-text="showAddModule ? 'Batal' : '+ Tambah Modul'"></span>
                </button>
                <button @click="showAddMaterial = !showAddMaterial" class="py-2.5 px-3 rounded-2xl bg-emerald-600 text-white font-black text-xs text-center shadow-sm hover:bg-emerald-700 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-file-circle-plus"></i> <span x-text="showAddMaterial ? 'Batal' : '+ Unggah Materi'"></span>
                </button>
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
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Batas Waktu Pengumpulan (Deadline)</label>
                        <input type="datetime-local" name="due_date" 
                               class="w-full p-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-amber-600 text-white font-black text-xs rounded-xl shadow-sm hover:bg-amber-700 transition">
                        Simpan Tugas
                    </button>
                </form>
            </div>
        @endif

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
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('mobile.lms.assignment.delete', $assignment->id) }}" 
                               onclick="return confirm('Hapus tugas ini secara permanen?')"
                               class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 text-[10px] font-black border border-rose-200 hover:bg-rose-100 transition flex items-center gap-1">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </a>
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">
                                Maker
                            </span>
                        </div>
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
            <div class="clay-card p-5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black clay-pink">Kuis LMS</span>
                        <h4 class="text-xs font-black text-slate-900 mt-1">{{ $quiz->title }}</h4>
                        <p class="text-[10px] text-slate-500 font-bold mt-0.5"><i class="fa-regular fa-clock text-pink-600 mr-1"></i>Durasi: {{ $quiz->time_limit ?? 30 }} menit</p>
                    </div>

                    @if($isTeacher)
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('mobile.lms.quiz.delete', $quiz->id) }}" 
                               onclick="return confirm('Hapus kuis ini secara permanen?')"
                               class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 text-[10px] font-black border border-rose-200 hover:bg-rose-100 transition flex items-center gap-1">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </a>
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">
                                Maker
                            </span>
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
