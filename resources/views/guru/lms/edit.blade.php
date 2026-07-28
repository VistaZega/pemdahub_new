@extends('layouts.guru')

@section('title', 'Edit Course - LMS Guru')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.lms.show', $course->id) }}" class="w-10 h-10 bg-white border-2 border-black rounded-xl flex items-center justify-center text-black hover:bg-amber-300 transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-black">Edit Course Ajar</h2>
            <p class="text-black font-bold text-xs">{{ $course->name }}</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.update', $course->id) }}" method="POST" class="bg-white rounded-3xl shadow-md border-2 border-black p-6 md:p-8">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Nama Course <span class="text-red-600">*</span></label>
                <input type="text" name="name" value="{{ old('name', $course->name) }}" required
                       class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                @error('name') <span class="text-red-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Status Publikasi</label>
                <select name="status" required class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                    <option value="draft" {{ $course->computed_status === 'draft' ? 'selected' : '' }}>Draft (Hanya Guru)</option>
                    <option value="active" {{ $course->computed_status === 'active' ? 'selected' : '' }}>Aktif (Terbit untuk Siswa)</option>
                    <option value="archived" {{ $course->computed_status === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Kode Akses Course</label>
                <input type="text" name="code" value="{{ old('code', $course->code) }}" 
                       class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"
                       placeholder="Biarkan kosong untuk generate otomatis">
                @error('code') <span class="text-red-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Kelas yang Diassign</label>
                <div class="border-2 border-black rounded-2xl p-4 max-h-48 overflow-y-auto space-y-2.5 bg-slate-50">
                    @forelse($classrooms as $classroom)
                    <label class="flex items-center gap-2.5 cursor-pointer bg-white p-2 rounded-xl border border-black shadow-sm">
                        <input type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}"
                               class="w-4 h-4 rounded border-2 border-black text-black focus:ring-0"
                               {{ in_array($classroom->id, old('classroom_ids', $assignedClassroomIds)) ? 'checked' : '' }}>
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
                          class="w-full border-2 border-black rounded-2xl p-4 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">{{ old('description', $course->description) }}</textarea>
            </div>

            <div class="md:col-span-2 border-2 border-black rounded-2xl p-4 shadow-sm" style="background-color: #fef08a !important; color: #000000 !important;">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_sequential" value="1" {{ old('is_sequential', $course->is_sequential) ? 'checked' : '' }} class="w-5 h-5 text-black border-2 border-black rounded focus:ring-0 mr-3">
                    <div>
                        <span class="text-xs font-black text-black uppercase tracking-wider"><i class="fas fa-lock text-black mr-1"></i> Aktifkan Pembelajaran Berurutan (Sequential Learning)</span>
                        <p class="text-xs font-bold text-black mt-0.5">Siswa wajib menyelesaikan materi sebelumnya sebelum materi berikutnya terbuka 🔒.</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="mt-4 p-4 bg-slate-100 border-2 border-black rounded-2xl text-xs font-black text-black">
            <strong>Info Rincian:</strong> {{ $course->code ? 'Kode: ' . $course->code . ' | ' : '' }}Mapel: {{ $course->subject->subject_name ?? '-' }} | Semester: {{ $course->semester->semester_name ?? '-' }}
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="bg-black hover:bg-emerald-600 text-white px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-md border-2 border-black">
                <i class="fas fa-save mr-1.5"></i> Perbarui Course
            </button>
            <a href="{{ route('guru.lms.show', $course->id) }}" class="bg-slate-200 text-black border-2 border-black px-6 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider hover:bg-slate-300 transition">Batal</a>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="bg-rose-100 border-2 border-black rounded-3xl p-6 shadow-md">
        <h3 class="text-black font-black text-sm mb-1 uppercase tracking-wider"><i class="fas fa-exclamation-triangle mr-1.5 text-rose-600"></i> Hapus Course Permanen</h3>
        <p class="text-black font-bold text-xs mb-4">Menghapus course akan menghapus semua modul, materi digital, tugas, dan quiz terkait.</p>
        <form action="{{ route('guru.lms.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus course \'{{ addslashes($course->name) }}\'? Semua data di dalamnya akan terhapus.')">
            @csrf @method('DELETE')
            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-wider transition border-2 border-black shadow-md">
                <i class="fas fa-trash mr-1.5"></i> Hapus Course Ini
            </button>
        </form>
    </div>
</div>
@endsection
