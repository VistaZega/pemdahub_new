@extends('layouts.guru')

@section('title', 'Edit Course - LMS')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.lms.show', $course->id) }}" class="text-gray-500 hover:text-emerald-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Course</h2>
            <p class="text-gray-500 text-sm">{{ $course->name }}</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.update', $course->id) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Course <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $course->name) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                    <option value="draft" {{ $course->computed_status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ $course->computed_status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="archived" {{ $course->computed_status === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Akses (LMS-XXXXXXXX)</label>
                <input type="text" name="code" value="{{ old('code', $course->code) }}" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500"
                       placeholder="Biarkan kosong untuk generate otomatis">
                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kelas yang Diassign</label>
                <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto space-y-2">
                    @forelse($classrooms as $classroom)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}"
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                               {{ in_array($classroom->id, old('classroom_ids', $assignedClassroomIds)) ? 'checked' : '' }}>
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
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">{{ old('description', $course->description) }}</textarea>
            </div>

            <div class="md:col-span-2 bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_sequential" value="1" {{ old('is_sequential', $course->is_sequential) ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500 mr-3">
                    <div>
                        <span class="text-sm font-bold text-gray-900"><i class="fas fa-lock text-indigo-600 mr-1"></i> Aktifkan Pembelajaran Berurutan (Sequential Learning)</span>
                        <p class="text-xs text-gray-600 mt-0.5">Siswa harus menyelesaikan materi sebelumnya terlebih dahulu sebelum materi berikutnya terbuka 🔒.</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="mt-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-500">
            <strong>Info:</strong> {{ $course->code ? 'Kode: ' . $course->code . ' | ' : '' }}Mapel: {{ $course->subject->subject_name ?? '-' }} | Semester: {{ $course->semester->semester_name ?? '-' }}
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl hover:bg-emerald-700 transition shadow-md">
                <i class="fas fa-save mr-1"></i> Perbarui
            </button>
            <a href="{{ route('guru.lms.show', $course->id) }}" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-xl hover:bg-gray-200 transition">Batal</a>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
        <h3 class="text-red-700 font-bold mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Zona Bahaya</h3>
        <p class="text-red-600 text-sm mb-3">Menghapus course akan menghapus semua modul, materi, tugas, dan quiz terkait.</p>
        <form action="{{ route('guru.lms.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus course ini? Semua data akan hilang.')">
            @csrf @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
                <i class="fas fa-trash mr-1"></i> Hapus Course
            </button>
        </form>
    </div>
</div>
@endsection
