@extends('mobile.layouts.app')

@section('title', 'LMS Digital 3D - Mobile Pro')

@section('content')
<div class="space-y-4" x-data="{ showAddCourse: false }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">LMS Digital 📚</h2>
            <p class="text-[11px] text-slate-500 font-bold">Pembelajaran & Materi Sekolah</p>
        </div>
        <div class="flex items-center gap-1.5">
            @if($isTeacher)
                <button @click="showAddCourse = !showAddCourse" 
                        class="px-3.5 py-2 rounded-2xl bg-purple-600 text-white text-xs font-black hover:bg-purple-700 transition flex items-center gap-1 shadow-md">
                    <i class="fa-solid fa-plus-circle"></i> <span x-text="showAddCourse ? 'Batal' : '+ Kelas Baru'"></span>
                </button>
            @endif
            <a href="{{ route('mobile.lms.catalog') }}" 
               class="px-3.5 py-2 rounded-2xl bg-white border-2 border-purple-200 text-purple-700 text-xs font-black hover:bg-purple-50 transition flex items-center gap-1 shadow-sm">
                <i class="fa-solid fa-compass text-purple-600"></i> Katalog
            </a>
        </div>
    </div>

    <!-- Teacher Form Tambah Kelas LMS Baru -->
    @if($isTeacher)
        <div x-show="showAddCourse" x-transition class="clay-card p-5 space-y-3 bg-purple-50 border-2 border-purple-200">
            <h3 class="text-xs font-black text-purple-900 flex items-center gap-1.5">
                <i class="fa-solid fa-square-plus text-purple-600"></i> Buat Kursus / Kelas LMS Baru
            </h3>
            <form action="{{ route('mobile.lms.course.store') }}" method="POST" class="space-y-2.5">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-700 uppercase">Nama Kelas / Mata Pelajaran</label>
                    <input type="text" name="course_name" required placeholder="Contoh: Matematika Kelas X SMK" 
                           class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Pilih Rombel Kelas</label>
                        <select name="classroom_id" class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                            <option value="">-- Pilih Rombel Mengajar --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase">Mata Pelajaran</label>
                        <select name="subject_id" class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-700 uppercase">Kode Kelas (Opsional)</label>
                    <input type="text" name="code" placeholder="Kosongkan jika ingin dibuat otomatis..." 
                           class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-700 uppercase">Deskripsi Ringkas Kelas</label>
                    <textarea name="description" rows="2" placeholder="Penjelasan mengenai kelas ini..." class="w-full p-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 outline-none resize-none"></textarea>
                </div>

                <div class="p-3 bg-purple-100/60 rounded-xl border border-purple-200 flex items-start gap-2.5">
                    <input type="checkbox" id="is_sequential" name="is_sequential" value="1" class="mt-0.5 w-4 h-4 text-purple-600 rounded border-purple-300 focus:ring-purple-500">
                    <label for="is_sequential" class="text-[11px] font-bold text-purple-950 cursor-pointer">
                        <span class="font-black text-purple-900 block">🔒 Wajibkan Penyelesaian Modul Secara Bertahap</span>
                        Siswa tidak bisa mengerjakan/membuka modul berikutnya sebelum modul sebelumnya diselesaikan.
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 bg-purple-600 text-white font-black text-xs rounded-xl shadow-sm hover:bg-purple-700 transition">
                    Simpan & Buat Kelas LMS
                </button>
            </form>
        </div>
    @endif

    <!-- Courses Grid/List (Clay Cards) -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">
            {{ $isTeacher ? 'Mata Pelajaran Ampuan Saya' : 'Mata Pelajaran Saya' }}
        </h3>

        @forelse($enrolledCourses as $course)
            <div class="clay-card p-4.5 block hover:border-purple-300 transition group space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <a href="{{ route('mobile.lms.show', $course->id) }}" class="flex items-start space-x-3.5 flex-1 min-w-0">
                        <div class="w-13 h-13 rounded-2xl clay-purple flex items-center justify-center text-white font-black text-xl shadow-md group-hover:scale-105 transition shrink-0">
                            <i class="fa-solid fa-book"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-black text-purple-600 uppercase tracking-wider bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">{{ $course->code }}</span>
                            <h4 class="text-sm font-black text-slate-900 truncate leading-tight mt-1">{{ $course->course_name }}</h4>
                            <p class="text-xs text-slate-500 truncate mt-0.5 font-bold"><i class="fa-regular fa-user mr-1 text-purple-600"></i>{{ $course->teacher->full_name ?? 'Pengajar' }}</p>
                        </div>
                    </a>

                    @if($isTeacher)
                        <a href="{{ route('mobile.lms.course.delete', $course->id) }}" 
                           onclick="return confirm('Hapus seluruh kelas LMS ini?')"
                           class="px-2.5 py-1.5 bg-rose-50 text-rose-700 rounded-xl text-[10px] font-black border border-rose-200 hover:bg-rose-100 transition flex items-center gap-1">
                            <i class="fa-solid fa-trash-can"></i> Hapus
                        </a>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-black">
                    <span class="text-slate-500 text-[11px]"><i class="fa-solid fa-layer-group mr-1 text-purple-600"></i>{{ count($course->modules ?? []) }} Modul</span>
                    <a href="{{ route('mobile.lms.show', $course->id) }}" class="text-purple-600 font-black hover:translate-x-1 transition flex items-center gap-1">
                        Buka Kelas <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 font-bold">
                <i class="fa-solid fa-graduation-cap text-4xl mb-2 text-purple-400"></i>
                <p class="text-xs font-black text-slate-700">Belum ada kelas LMS yang dibuat atau diikuti.</p>
                @if($isTeacher)
                    <button @click="showAddCourse = true" class="clay-btn inline-block mt-3 px-5 py-2.5 text-white text-xs font-black shadow-md">+ Buat Kelas Pertama</button>
                @else
                    <a href="{{ route('mobile.lms.catalog') }}" class="clay-btn inline-block mt-3 px-5 py-2.5 text-white text-xs font-black shadow-md">Jelajahi Katalog Kelas</a>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection

