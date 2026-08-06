@extends('layouts.guru')

@section('title', 'Buat Course - LMS Guru')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{ submitting: false }">
    <div class="flex items-center gap-4">
        <a href="{{ route('guru.lms.index') }}" class="w-11 h-11 bg-white border-2 border-black rounded-2xl flex items-center justify-center text-black hover:bg-amber-300 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-black tracking-tight">Buat Course Ajar Baru</h2>
            <p class="text-black font-bold text-xs">Tambah ruang belajar digital baru untuk kelas Anda</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.store') }}" method="POST" @submit="if(submitting){ $event.preventDefault(); return false; } submitting = true;" class="bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden">
        <div class="px-8 py-6 border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
            <h3 class="text-white font-black tracking-wide flex items-center gap-2 text-base uppercase">
                <i class="fas fa-book-medical text-amber-400"></i> Detail Informasi Course Ajar
            </h3>
            <p class="text-amber-300 text-xs font-bold mt-1">Lengkapi informasi dasar course sebelum menyusun modul materi.</p>
        </div>

        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Nama Course <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"
                           placeholder="Contoh: Pemrograman Mikrokontroler XI DPIB">
                    @error('name') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Mata Pelajaran <span class="text-rose-600">*</span></label>
                    <select name="subject_id" required class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                        @if($subjects->isEmpty())
                            <option value="">⚠ Belum ditugaskan untuk mata pelajaran apapun</option>
                        @else
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('subject_id') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Semester <span class="text-rose-600">*</span></label>
                    <select name="semester_id" required class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                        <option value="">-- Pilih Semester --</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ (old('semester_id') == $semester->id || ($activeSemester && $activeSemester->id == $semester->id)) ? 'selected' : '' }}>
                                {{ $semester->semester_name }} {{ $semester->academicYear ? '('.$semester->academicYear->year.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('semester_id') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Kelas yang Di-assign</label>
                    <div class="border-2 border-black rounded-2xl p-4 max-h-48 overflow-y-auto space-y-2 bg-slate-50">
                        @forelse($classrooms as $classroom)
                        <label class="flex items-center gap-3 cursor-pointer bg-white p-2.5 rounded-xl border-2 border-black shadow-xs">
                            <input type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}"
                                   class="w-4 h-4 rounded border-2 border-black text-black focus:ring-0"
                                   {{ in_array($classroom->id, old('classroom_ids', [])) ? 'checked' : '' }}>
                            <span class="text-xs font-black text-black">{{ $classroom->class_name }}</span>
                        </label>
                        @empty
                        <p class="text-black font-bold text-xs"><i class="fas fa-info-circle mr-1 text-amber-500"></i>Belum ditugaskan di kelas apapun</p>
                        @endforelse
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Deskripsi Course</label>
                    <textarea name="description" rows="3"
                              class="w-full border-2 border-black rounded-2xl p-4 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"
                              placeholder="Deskripsi singkat tentang course ini...">{{ old('description') }}</textarea>
                </div>

                <div class="md:col-span-2 border-2 border-black rounded-2xl p-4 bg-amber-100 shadow-xs">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_sequential" value="1" {{ old('is_sequential') ? 'checked' : '' }} class="w-5 h-5 text-black border-2 border-black rounded focus:ring-0 mr-3">
                        <div>
                            <span class="text-xs font-black text-black tracking-wide uppercase"><i class="fas fa-lock text-black mr-1"></i> Aktifkan Pembelajaran Berurutan (Sequential Learning)</span>
                            <p class="text-xs font-bold text-black mt-0.5">Siswa wajib menyelesaikan materi sebelumnya sebelum materi berikutnya terbuka 🔒.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t-2 border-black flex gap-3">
                <a href="{{ route('guru.lms.index') }}" class="flex-1 bg-slate-200 text-black border-2 border-black px-6 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider hover:bg-slate-300 transition text-center">Batal</a>
                <button type="submit" :disabled="submitting" class="flex-[2] bg-emerald-600 text-white hover:bg-emerald-700 px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-md border-2 border-black disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!submitting"><i class="fas fa-save mr-1.5 text-amber-400"></i> Simpan Course</span>
                    <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-1.5"></i> Memproses...</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
