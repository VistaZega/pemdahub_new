@extends('mobile.layouts.app')

@section('title', 'LMS Digital PembdaHUB - Kursus & Mata Pelajaran')

@section('content')
@php
    $totalMaterials = $enrolledCourses->sum('materials_count');
    $totalAssignments = $enrolledCourses->sum('assignments_count');
    $totalQuizzes = $enrolledCourses->sum('quizzes_count');
@endphp

<div class="space-y-4 pb-12" x-data="{ 
    showAddCourse: false, 
    searchQuery: ''
}">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between gap-2 px-1 pt-1">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-2xl bg-purple-600 text-white flex items-center justify-center shadow-md shrink-0">
                    <i class="fa-solid fa-book-bookmark text-sm"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-black text-slate-900 leading-tight truncate">LMS Digital</h2>
                    <p class="text-[10px] text-slate-500 font-bold truncate">Ruang Belajar & Materi Interaktif</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-1.5 shrink-0 flex-wrap justify-end">
            @if($isTeacher)
                <a href="{{ route('guru.cbt.banks.index') }}" 
                   class="px-2.5 py-2 rounded-xl bg-amber-400 text-black text-xs font-black hover:bg-amber-300 active:scale-95 transition flex items-center gap-1.5 shadow-2xs border border-amber-500">
                    <i class="fa-solid fa-database text-xs"></i>
                    <span>Bank Soal</span>
                </a>
                <button @click="showAddCourse = !showAddCourse" 
                        class="px-3 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-xs font-black shadow-md hover:opacity-90 active:scale-95 transition flex items-center gap-1.5">
                    <i class="fa-solid" :class="showAddCourse ? 'fa-xmark' : 'fa-plus'"></i>
                    <span x-text="showAddCourse ? 'Batal' : '+ Kelas'"></span>
                </button>
            @endif
            <a href="{{ route('mobile.lms.catalog') }}" 
               class="px-3 py-2 rounded-xl bg-white border-2 border-purple-200 text-purple-700 text-xs font-black hover:bg-purple-50 active:scale-95 transition flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-compass text-purple-600"></i>
                <span>Katalog</span>
            </a>
            <a href="{{ route('siswa.lms.index', ['switch_mode' => 'desktop']) }}" 
               title="Beralih ke Versi Web Desktop"
               class="px-2.5 py-2 rounded-xl bg-blue-50 border-2 border-blue-200 text-blue-700 text-xs font-black hover:bg-blue-100 active:scale-95 transition flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-desktop text-blue-600"></i>
                <span>Mode Web</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Banner Clay Card -->
    <div class="clay-purple p-5 sm:p-6 space-y-3.5 rounded-3xl shadow-lg">
        <div class="flex items-center justify-between gap-3 px-1">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/25 px-3 py-1 rounded-full border border-white/35 text-white shadow-xs">
                {{ $isTeacher ? '👨‍🏫 Panel Pengajar LMS' : '👨‍🎓 Ruang Belajar Siswa' }}
            </span>
            <span class="text-[10px] font-black text-purple-100 bg-white/20 px-3 py-1 rounded-full border border-white/30">
                {{ $enrolledCourses->count() }} Kelas Aktif
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2.5 pt-1 text-center">
            <div class="py-3 px-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 shadow-xs">
                <span class="text-xl font-black text-white leading-none block">{{ $enrolledCourses->count() }}</span>
                <span class="text-[9px] font-extrabold text-purple-100 uppercase tracking-wider mt-1 block">Mapel</span>
            </div>
            <div class="py-3 px-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 shadow-xs">
                <span class="text-xl font-black text-white leading-none block">{{ $totalMaterials }}</span>
                <span class="text-[9px] font-extrabold text-purple-100 uppercase tracking-wider mt-1 block">Materi</span>
            </div>
            <div class="py-3 px-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 shadow-xs">
                <span class="text-xl font-black text-white leading-none block">{{ $totalAssignments + $totalQuizzes }}</span>
                <span class="text-[9px] font-extrabold text-purple-100 uppercase tracking-wider mt-1 block">Tugas & Kuis</span>
            </div>
        </div>
    </div>

    <!-- Teacher: Form Tambah Kelas LMS Baru (Collapsible Slide-Down) -->
    @if($isTeacher)
        <div x-show="showAddCourse" x-collapse class="clay-card p-5 space-y-3.5 bg-gradient-to-b from-purple-50 to-white border-2 border-purple-300 shadow-md">
            <div class="flex items-center justify-between border-b border-purple-100 pb-2">
                <h3 class="text-xs font-black text-purple-950 flex items-center gap-2">
                    <i class="fa-solid fa-square-plus text-purple-600 text-sm"></i> Buat Kursus / Kelas LMS Baru
                </h3>
                <span class="text-[9px] font-extrabold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">Khusus Guru</span>
            </div>

            <form action="{{ route('mobile.lms.course.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Nama Kelas / Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="course_name" required placeholder="Contoh: Pemrograman Web & Perangkat Bergerak XII RPL" 
                           class="w-full px-3.5 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Rombongan Belajar (Rombel)</label>
                        <select name="classroom_id" class="w-full px-3 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600">
                            <option value="">-- Pilih Rombel Mengajar --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Mata Pelajaran Induk</label>
                        <select name="subject_id" class="w-full px-3 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Kode Kelas (Opsional)</label>
                    <input type="text" name="code" placeholder="Kosongkan untuk generate otomatis (cth: LMS-XXXX)" 
                           class="w-full px-3.5 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600">
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Deskripsi Ringkas Kelas</label>
                    <textarea name="description" rows="2" placeholder="Tuliskan tujuan pembelajaran, capaian modul, atau instruksi umum kelas..." 
                              class="w-full px-3.5 py-2 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600 resize-none"></textarea>
                </div>

                <div class="p-3.5 bg-purple-100/70 rounded-2xl border border-purple-300 flex items-start gap-2.5">
                    <input type="checkbox" id="is_sequential" name="is_sequential" value="1" class="mt-0.5 w-4 h-4 text-purple-600 rounded border-purple-400 focus:ring-purple-500 cursor-pointer">
                    <label for="is_sequential" class="text-[11px] font-bold text-purple-950 cursor-pointer select-none">
                        <span class="font-black text-purple-900 flex items-center gap-1">
                            <i class="fa-solid fa-lock text-purple-700"></i> Mode Pembelajaran Bertahap (Sequential Lock)
                        </span>
                        Siswa wajib menyelesaikan materi/modul satu per satu secara berurutan sebelum membuka bab selanjutnya.
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-black text-xs rounded-xl shadow-md hover:from-purple-700 hover:to-indigo-700 active:scale-98 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan & Publikasikan Kelas LMS
                </button>
            </form>
        </div>
    @endif

    <!-- Rombel Horizontal Filter Pills for Teacher -->
    @if($isTeacher && $classrooms->count() > 0)
        <div class="space-y-1.5 px-0.5">
            <div class="flex items-center justify-between text-[11px] font-black text-slate-700">
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-users text-purple-600"></i> Pilih Rombel Mengajar:</span>
                @if($selectedClassroomId)
                    <a href="{{ route('mobile.lms.index') }}" class="text-[10px] text-purple-600 font-extrabold hover:underline">Reset Filter ✕</a>
                @endif
            </div>
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar -mx-1 px-1">
                <a href="{{ route('mobile.lms.index') }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 shadow-2xs
                          {{ !$selectedClassroomId ? 'bg-slate-900 text-amber-300 border-slate-900' : 'bg-white text-slate-700 border-slate-200 hover:border-purple-300' }}">
                    <i class="fa-solid fa-table-cells-large text-[10px] mr-1"></i> Semua Rombel
                </a>
                @foreach($classrooms as $cls)
                    <a href="{{ route('mobile.lms.index', ['classroom_id' => $cls->id]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 shadow-2xs
                              {{ $selectedClassroomId == $cls->id ? 'bg-slate-900 text-amber-300 border-slate-900' : 'bg-white text-slate-700 border-slate-200 hover:border-purple-300' }}">
                        {{ $cls->class_name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Search & Filter Controls -->
    <div class="clay-card p-3 space-y-2.5 bg-white border border-slate-200 shadow-sm">
        <!-- Live Search Input -->
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Cari nama mapel, guru pengampu, kode kelas..." 
                   class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-purple-500 focus:bg-white transition">
            <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <!-- Course Cards Section -->
    <div class="space-y-3.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-graduation-cap text-purple-600"></i>
                {{ $isTeacher ? 'Mata Pelajaran Ampuan Guru' : 'Daftar Mata Pelajaran Siswa' }}
            </h3>
            <span class="text-[10px] font-extrabold text-slate-500">
                Total: {{ $enrolledCourses->count() }} Kelas
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($enrolledCourses as $course)
                @php
                    $subjectName = $course->subject->name ?? '';
                    $theme = $course->design;
                    $progress = $courseProgress[$course->id] ?? 0;
                    $teacherName = $course->teacher->full_name ?? ($course->teacher->user->name ?? 'Guru Pengampu');
                    $classNames = $course->lmsClasses->pluck('classroom.class_name')->filter()->implode(', ');
                    if (empty($classNames)) {
                        $classNames = $course->classroom->class_name ?? 'Semua Kelas';
                    }
                @endphp

                <!-- Dynamic Course Card with Search Filter -->
                <div class="clay-card overflow-hidden border-2 border-slate-200/90 hover:border-purple-400 transition-all duration-200 shadow-sm hover:shadow-md group bg-white rounded-3xl"
                     x-show="!searchQuery || '{{ strtolower(addslashes($course->course_name . ' ' . $subjectName . ' ' . $teacherName . ' ' . $course->code . ' ' . $classNames)) }}'.includes(searchQuery.toLowerCase())">
                    
                    <!-- Card Top Thematic Gradient Banner -->
                    <div class="bg-gradient-to-r {{ $theme['gradient'] }} p-5 text-white relative overflow-hidden rounded-t-3xl">
                        <!-- Subtle Background Watermark Graphic -->
                        <div class="absolute -right-3 -bottom-4 text-white/10 text-6xl pointer-events-none transform -rotate-12">
                            <i class="{{ $theme['icon'] }}"></i>
                        </div>

                        <!-- Top Floating Badges -->
                        <div class="flex items-center justify-between gap-2.5 relative z-10 mb-3">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-white/20 backdrop-blur-xs text-white border border-white/30 shadow-2xs">
                                    {{ $course->code }}
                                </span>
                                @if($classNames)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-black/25 backdrop-blur-xs text-white border border-white/20">
                                        <i class="fa-solid fa-users-rectangle text-[8px] mr-0.5"></i> {{ $classNames }}
                                    </span>
                                @endif
                            </div>

                            @if($course->is_sequential)
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black bg-amber-400/90 text-amber-950 border border-amber-300 shadow-2xs flex items-center gap-1 shrink-0">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Bertahap
                                </span>
                            @endif
                        </div>

                        <!-- Main Icon & Course Name Row -->
                        <div class="flex items-start gap-3.5 relative z-10">
                            <!-- 3D Style Glossy Thematic Icon -->
                            <div class="w-13 h-13 rounded-2xl bg-white/20 backdrop-blur-md border border-white/35 shadow-md flex items-center justify-center text-white text-2xl group-hover:scale-108 transition-transform duration-200 shrink-0">
                                <i class="{{ $theme['icon'] }}"></i>
                            </div>

                            <!-- Course Title & Category -->
                            <div class="min-w-0 flex-1">
                                <span class="text-[9px] font-extrabold uppercase tracking-wide text-white/80 block">
                                    {{ $theme['emoji'] }} {{ $theme['category'] }}
                                </span>
                                <h4 class="text-sm font-black text-white leading-snug drop-shadow-xs line-clamp-2 mt-0.5">
                                    {{ $course->course_name }}
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body Content -->
                    <div class="p-5 space-y-3.5">
                        <!-- Instructor Info & Description -->
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 font-black text-[10px] flex items-center justify-center border border-purple-200 shrink-0">
                                    {{ strtoupper(substr($teacherName, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-black text-slate-800 truncate">{{ $teacherName }}</p>
                                    <p class="text-[9px] font-bold text-slate-400">Guru Pengampu</p>
                                </div>
                            </div>

                            @if($course->subject)
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black {{ $theme['badgeBg'] }} border shrink-0">
                                    {{ $course->subject->name }}
                                </span>
                            @endif
                        </div>

                        @if($course->description)
                            <p class="text-[11px] text-slate-600 font-semibold line-clamp-2 leading-relaxed bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                {{ $course->description }}
                            </p>
                        @endif

                        <!-- Key Metrics Grid -->
                        <div class="grid grid-cols-4 gap-2 pt-1 text-center">
                            <div class="py-2.5 px-1 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <span class="text-xs font-black text-slate-800 block">{{ $course->materials_count }}</span>
                                <span class="text-[8px] font-black text-slate-500 uppercase">Materi</span>
                            </div>
                            <div class="py-2.5 px-1 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <span class="text-xs font-black text-slate-800 block">{{ $course->assignments_count }}</span>
                                <span class="text-[8px] font-black text-slate-500 uppercase">Tugas</span>
                            </div>
                            <div class="py-2.5 px-1 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <span class="text-xs font-black text-slate-800 block">{{ $course->quizzes_count }}</span>
                                <span class="text-[8px] font-black text-slate-500 uppercase">Kuis</span>
                            </div>
                            <div class="py-2.5 px-1 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <span class="text-xs font-black text-slate-800 block">{{ $course->modules->count() }}</span>
                                <span class="text-[8px] font-black text-slate-500 uppercase">Modul</span>
                            </div>
                        </div>

                        <!-- Progress Bar (for Students) -->
                        @if(!$isTeacher)
                            <div class="space-y-1.5 pt-1">
                                <div class="flex items-center justify-between text-[10px] font-black">
                                    <span class="text-slate-600">Progres Pembelajaran:</span>
                                    <span class="{{ $progress == 100 ? 'text-emerald-600' : ($progress > 0 ? 'text-blue-600' : 'text-slate-400') }}">
                                        {{ $progress }}% Selesai
                                    </span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden border border-slate-200">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $progress == 100 ? 'bg-emerald-500' : ($progress > 40 ? 'bg-blue-500' : 'bg-amber-500') }}"
                                         style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Footer -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                            @if($isTeacher)
                                <form action="{{ route('mobile.lms.course.destroy', $course->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus seluruh kelas LMS {{ addslashes($course->course_name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-2.5 rounded-xl bg-rose-50 text-rose-700 text-[10px] font-black border border-rose-200 hover:bg-rose-100 active:scale-95 transition flex items-center gap-1">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] font-extrabold text-slate-400">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-[9px] mr-0.5"></i> Terdaftar
                                </span>
                            @endif

                            <a href="{{ route('mobile.lms.show', $course->id) }}" 
                               class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-r {{ $theme['gradient'] }} text-white text-xs font-black text-center shadow-md active:scale-98 hover:opacity-95 transition flex items-center justify-center gap-1.5 group-hover:shadow-lg">
                                <span>Masuk Kelas LMS</span>
                                <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="clay-card p-8 text-center text-slate-500 space-y-3 bg-white rounded-3xl">
                    <div class="w-16 h-16 rounded-3xl bg-purple-50 border-2 border-purple-200 text-purple-600 text-3xl flex items-center justify-center mx-auto shadow-inner">
                        🎓
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800">Belum Ada Kelas LMS</h4>
                        <p class="text-xs text-slate-500 font-semibold mt-1">Anda belum memiliki atau mengikuti kelas LMS pada semester aktif saat ini.</p>
                    </div>
                    @if($isTeacher)
                        <button @click="showAddCourse = true" class="clay-btn inline-flex items-center gap-1.5 px-5 py-2.5 text-white text-xs font-black shadow-md">
                            <i class="fa-solid fa-plus"></i> Buat Kelas Pertama
                        </button>
                    @else
                        <a href="{{ route('mobile.lms.catalog') }}" class="clay-btn inline-flex items-center gap-1.5 px-5 py-2.5 text-white text-xs font-black shadow-md">
                            <i class="fa-solid fa-compass"></i> Jelajahi Katalog Kelas
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection




