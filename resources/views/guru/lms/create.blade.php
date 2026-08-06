@extends('layouts.guru')

@section('title', 'Buat Course - LMS Guru')

@section('content')
<div class="space-y-6" x-data="{ submitting: false }">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.lms.index') }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-800 hover:bg-amber-100 text-amber-800 transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">Buat Course Ajar Baru</h2>
            <p class="text-gray-800 font-bold text-xs">Tambah modul ruang belajar digital baru untuk siswa Anda</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.store') }}" method="POST" @submit="if(submitting){ $event.preventDefault(); return false; } submitting = true;" class="bg-white rounded-xl shadow-md border border-gray-200 p-6 md:p-8">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-2">Nama Course <span class="text-red-600">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 font-semibold focus:ring-4 focus:ring-black/20 outline-none"
                       placeholder="Contoh: Matematika Kelas X Semester 1">
                @error('name') <span class="text-red-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-2">Mata Pelajaran <span class="text-red-600">*</span></label>
                <select name="subject_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 font-semibold focus:ring-4 focus:ring-black/20 outline-none bg-white">
                    @if($subjects->isEmpty())
                        <option value="">⚠ Belum ditugaskan untuk mata pelajaran apapun</option>
                    @else
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name }}</option>
                        @endforeach
                    @endif
                </select>
                @error('subject_id') <span class="text-red-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-2">Semester <span class="text-red-600">*</span></label>
                <select name="semester_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 font-semibold focus:ring-4 focus:ring-black/20 outline-none bg-white">
                    <option value="">-- Pilih Semester --</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ (old('semester_id') == $semester->id || ($activeSemester && $activeSemester->id == $semester->id)) ? 'selected' : '' }}>
                            {{ $semester->semester_name }} {{ $semester->academicYear ? '('.$semester->academicYear->year.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('semester_id') <span class="text-red-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-2">Kelas yang Diassign</label>
                <div class="border border-gray-200 rounded-xl p-4 max-h-48 overflow-y-auto space-y-2.5 bg-slate-50">
                    @forelse($classrooms as $classroom)
                    <label class="flex items-center gap-2.5 cursor-pointer bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                        <input type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}"
                               class="w-4 h-4 rounded border border-gray-200 text-gray-800 focus:ring-0"
                               {{ in_array($classroom->id, old('classroom_ids', [])) ? 'checked' : '' }}>
                        <span class="text-xs font-semibold text-gray-800">{{ $classroom->class_name }}</span>
                    </label>
                    @empty
                    <p class="text-gray-800 font-bold text-xs"><i class="fas fa-info-circle mr-1 text-amber-500"></i>Belum ditugaskan di kelas apapun</p>
                    @endforelse
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-2">Deskripsi Course</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-200 rounded-xl p-4 text-sm text-gray-800 font-semibold focus:ring-4 focus:ring-black/20 outline-none"
                          placeholder="Deskripsi singkat tentang course ini...">{{ old('description') }}</textarea>
            </div>

            <div class="md:col-span-2 border border-gray-200 rounded-xl p-4 shadow-sm" >
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_sequential" value="1" {{ old('is_sequential') ? 'checked' : '' }} class="w-5 h-5 text-gray-800 border border-gray-200 rounded focus:ring-0 mr-3">
                    <div>
                        <span class="text-xs font-semibold text-gray-800 tracking-wide"><i class="fas fa-lock text-gray-800 mr-1"></i> Aktifkan Pembelajaran Berurutan (Sequential Learning)</span>
                        <p class="text-xs font-bold text-gray-800 mt-0.5">Siswa wajib menyelesaikan materi sebelumnya sebelum materi berikutnya terbuka 🔒.</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" :disabled="submitting" class="bg-emerald-600 text-white hover:bg-emerald-700 px-8 py-3.5 rounded-xl font-semibold text-xs tracking-wide transition-all shadow-md border border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!submitting"><i class="fas fa-save mr-1.5"></i> Simpan Course</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-1.5"></i> Memproses...</span>
            </button>
            <a href="{{ route('guru.lms.index') }}" class="bg-slate-200 text-gray-800 border border-gray-200 px-6 py-3.5 rounded-xl font-semibold text-xs tracking-wide hover:bg-slate-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
