@extends('layouts.guru')

@section('title', 'Edit Tugas - LMS')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.lms.assignments.show', $assignment->id) }}" class="text-gray-500 hover:text-emerald-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Tugas</h2>
            <p class="text-gray-500 text-sm">Course: {{ $course->name }}</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.assignments.update', $assignment->id) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @csrf
        @method('PUT')
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $assignment->title) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modul <span class="text-red-500">*</span></label>
                <select name="module_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">— Pilih Modul —</option>
                    @foreach($modules as $module)
                    <option value="{{ $module->id }}" {{ old('module_id', $assignment->module_id) == $module->id ? 'selected' : '' }}>
                        {{ $module->getCode() }} — {{ $module->title }}
                    </option>
                    @endforeach
                </select>
                @error('module_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Instruksi</label>
                <textarea name="description" rows="4"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 math-support"
                          placeholder="Jelaskan tugas yang harus dikerjakan siswa...">{{ old('description', $assignment->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                    <input type="datetime-local" name="due_date"
                           value="{{ old('due_date', $assignment->deadline ? \Carbon\Carbon::parse($assignment->deadline)->format('Y-m-d\TH:i') : '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Skor Maksimal <span class="text-red-500">*</span></label>
                    <input type="number" name="max_score" value="{{ old('max_score', $assignment->max_score) }}" min="1" max="100" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="flex items-center gap-3 bg-blue-50 rounded-lg p-3">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="allow_resubmit" value="1"
                               {{ old('allow_resubmit', $assignment->allow_resubmit) ? 'checked' : '' }}
                               class="rounded text-blue-600 focus:ring-blue-500"
                               onchange="document.getElementById('max_resubmissions_field').classList.toggle('hidden')">
                        Boleh Revisi / Kirim Ulang
                    </label>
                </div>
                <div id="max_resubmissions_field" class="{{ old('allow_resubmit', $assignment->allow_resubmit) ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maks Revisi</label>
                    <input type="number" name="max_resubmissions" value="{{ old('max_resubmissions', $assignment->max_resubmissions ?? 3) }}" min="1" max="10"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Berapa kali siswa boleh mengirim ulang</p>
                </div>
            </div>

            @if($assignment->file_path)
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-sm text-gray-600"><i class="fas fa-paperclip mr-1"></i> File saat ini: <span class="font-medium">{{ basename($assignment->file_path) }}</span></p>
                <p class="text-xs text-gray-400 mt-1">Upload file baru untuk mengganti, atau biarkan kosong untuk tetap menggunakan file saat ini</p>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File Lampiran (opsional)</label>
                <input type="file" name="file" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">Maksimal 10 MB</p>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 transition shadow-md">
                <i class="fas fa-save mr-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('guru.lms.assignments.show', $assignment->id) }}" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-xl hover:bg-gray-200 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
