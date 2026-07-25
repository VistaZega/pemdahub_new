@extends('layouts.guru')

@section('title', 'Buat Course - LMS')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.lms.index') }}" class="text-gray-500 hover:text-emerald-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Buat Course Baru</h2>
            <p class="text-gray-500 text-sm">Tambah course pembelajaran baru</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Course <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                       placeholder="Contoh: Matematika Kelas X Semester 1">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran <span class="text-red-500">*</span></label>
                <select name="subject_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    @if($subjects->isEmpty())
                        <option value="">⚠ Belum ditugaskan untuk mata pelajaran apapun</option>
                    @else
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name }}</option>
                        @endforeach
                    @endif
                </select>
                @error('subject_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
                <select name="semester_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Semester --</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ (old('semester_id') == $semester->id || ($activeSemester && $activeSemester->id == $semester->id)) ? 'selected' : '' }}>
                            {{ $semester->semester_name }} {{ $semester->academicYear ? '('.$semester->academicYear->year.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('semester_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kelas yang Diassign</label>
                <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto space-y-2">
                    @forelse($classrooms as $classroom)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}"
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                               {{ in_array($classroom->id, old('classroom_ids', [])) ? 'checked' : '' }}>
                        <span class="text-sm">{{ $classroom->class_name }}</span>
                    </label>
                    @empty
                    <p class="text-amber-600 text-sm"><i class="fas fa-info-circle mr-1"></i>Belum ditugaskan di kelas apapun</p>
                    @endforelse
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500"
                          placeholder="Deskripsi singkat tentang course ini...">{{ old('description') }}</textarea>
            </div>

            <div class="md:col-span-2 bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_sequential" value="1" {{ old('is_sequential') ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500 mr-3">
                    <div>
                        <span class="text-sm font-bold text-gray-900"><i class="fas fa-lock text-indigo-600 mr-1"></i> Aktifkan Pembelajaran Berurutan (Sequential Learning)</span>
                        <p class="text-xs text-gray-600 mt-0.5">Siswa harus menyelesaikan materi sebelumnya terlebih dahulu sebelum materi berikutnya terbuka 🔒.</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl hover:bg-emerald-700 transition shadow-md">
                <i class="fas fa-save mr-1"></i> Simpan Course
            </button>
            <a href="{{ route('guru.lms.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-xl hover:bg-gray-200 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
