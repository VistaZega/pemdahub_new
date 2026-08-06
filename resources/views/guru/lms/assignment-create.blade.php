@extends('layouts.guru')

@section('title', 'Buat Tugas - LMS')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center gap-4">
        <a href="{{ route('guru.lms.show', $course->id) }}?tab=assignments" class="w-11 h-11 bg-white border-2 border-black rounded-2xl flex items-center justify-center text-black hover:bg-amber-300 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-black tracking-tight">Buat Penugasan Siswa Baru</h2>
            <p class="text-black font-bold text-xs">Course: {{ $course->name }}</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.assignments.store', $course->id) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden">
        @csrf
        <div class="px-8 py-6 border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
            <h3 class="text-white font-black tracking-wide flex items-center gap-2 text-base uppercase">
                <i class="fas fa-tasks text-amber-400"></i> Detail Informasi Tugas Siswa
            </h3>
            <p class="text-amber-300 text-xs font-bold mt-1">Buat instruksi penugasan, batas waktu pengumpulan, dan tipe berkas.</p>
        </div>

        <div class="p-8 space-y-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Judul Tugas <span class="text-rose-600">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"
                           placeholder="Contoh: Tugas 1 - Rangkaian Sensor Mikrokontroler">
                    @error('title') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Modul <span class="text-rose-600">*</span></label>
                    <select name="module_id" required class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                        <option value="">— Pilih Modul —</option>
                        @foreach($modules as $module)
                        <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                            {{ $module->getCode() }} — {{ $module->title }}
                        </option>
                        @endforeach
                    </select>
                    @error('module_id') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Deskripsi / Instruksi Tugas</label>
                    <textarea name="description" rows="4"
                              class="w-full border-2 border-black rounded-2xl p-4 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none math-support"
                              placeholder="Jelaskan instruksi tugas yang harus dikerjakan siswa..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Tipe Pengumpulan <span class="text-rose-600">*</span></label>
                        <select name="assignment_type" required class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                            <option value="file" {{ old('assignment_type') === 'file' ? 'selected' : '' }}>Upload File</option>
                            <option value="text" {{ old('assignment_type') === 'text' ? 'selected' : '' }}>Teks</option>
                            <option value="file_text" {{ old('assignment_type') === 'file_text' ? 'selected' : '' }}>File + Teks</option>
                            <option value="link" {{ old('assignment_type') === 'link' ? 'selected' : '' }}>Link URL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Deadline</label>
                        <input type="datetime-local" name="due_date" value="{{ old('due_date') }}"
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Skor Maksimal <span class="text-rose-600">*</span></label>
                        <input type="number" name="max_score" value="{{ old('max_score', 100) }}" min="1" max="100" required
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex items-center gap-3 bg-amber-100 border-2 border-black rounded-2xl p-4 shadow-xs">
                        <label class="flex items-center gap-3 text-xs font-black text-black uppercase cursor-pointer">
                            <input type="checkbox" name="allow_resubmit" value="1" {{ old('allow_resubmit') ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-2 border-black text-black focus:ring-0" onchange="document.getElementById('max_resubmissions_field').classList.toggle('hidden')">
                            Boleh Revisi / Kirim Ulang
                        </label>
                    </div>
                    <div id="max_resubmissions_field" class="{{ old('allow_resubmit') ? '' : 'hidden' }}">
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Maks Revisi</label>
                        <input type="number" name="max_resubmissions" value="{{ old('max_resubmissions', 3) }}" min="1" max="10"
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">File Lampiran (Opsional, Maks 10 MB)</label>
                    <input type="file" name="file" class="w-full text-xs text-black font-bold file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-2 file:border-black file:text-xs file:font-black file:bg-amber-300 file:text-black hover:file:bg-black hover:file:text-white cursor-pointer">
                </div>
            </div>

            <div class="pt-6 border-t-2 border-black flex gap-3">
                <a href="{{ route('guru.lms.show', $course->id) }}?tab=assignments" class="flex-1 bg-slate-200 text-black border-2 border-black px-6 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider hover:bg-slate-300 transition text-center">Batal</a>
                <button type="submit" class="flex-[2] bg-emerald-600 text-white hover:bg-emerald-700 px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-md border-2 border-black">
                    <i class="fas fa-save mr-1.5 text-amber-400"></i> Simpan Tugas
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
