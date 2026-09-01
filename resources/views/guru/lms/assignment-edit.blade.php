@extends('layouts.guru')

@section('title', 'Edit Tugas - LMS')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow {
        border: 2px solid #000 !important;
        border-top-left-radius: 1rem !important;
        border-top-right-radius: 1rem !important;
        background-color: #f8fafc;
    }
    .ql-container.ql-snow {
        border: 2px solid #000 !important;
        border-top: none !important;
        border-bottom-left-radius: 1rem !important;
        border-bottom-right-radius: 1rem !important;
        background-color: #fff;
        font-family: inherit;
    }
    .ql-editor {
        min-height: 180px;
        max-height: 400px;
        font-size: 0.925rem;
        font-weight: 500;
        color: #0f172a;
    }
    .ql-editor p {
        margin-bottom: 0.4rem !important;
        line-height: 1.6 !important;
    }
    .ql-editor table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 0.75rem 0 !important;
    }
    .ql-editor th, .ql-editor td {
        border: 1px solid #cbd5e1 !important;
        padding: 0.5rem 0.75rem !important;
        text-align: left !important;
    }
    .ql-editor th {
        background-color: #f1f5f9 !important;
        font-weight: 800 !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{ codeMode: false }">
    <div class="flex items-center gap-4">
        <a href="{{ route('guru.lms.assignments.show', $assignment->id) }}" class="w-11 h-11 bg-white border-2 border-black rounded-2xl flex items-center justify-center text-black hover:bg-amber-300 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-black tracking-tight">Edit Penugasan Siswa</h2>
            <p class="text-black font-bold text-xs">Course: {{ $course->name }}</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.assignments.update', $assignment->id) }}" method="POST" enctype="multipart/form-data" id="assignmentForm"
          class="bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden">
        @csrf
        @method('PUT')
        
        <div class="px-8 py-6 border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
            <h3 class="text-white font-black tracking-wide flex items-center gap-2 text-base uppercase">
                <i class="fas fa-edit text-amber-400"></i> Edit Informasi & Instruksi Tugas
            </h3>
            <p class="text-amber-300 text-xs font-bold mt-1">Perbarui judul, instruksi visual, batas waktu pengumpulan, atau berkas pendukung.</p>
        </div>

        <div class="p-8 space-y-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Judul Tugas <span class="text-rose-600">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $assignment->title) }}" required
                           class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"
                           placeholder="Contoh: Tugas 1 - Rangkaian Sensor Mikrokontroler">
                    @error('title') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Modul Terkait <span class="text-rose-600">*</span></label>
                    <select name="module_id" required class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                        <option value="">— Pilih Modul —</option>
                        @foreach($modules as $module)
                        <option value="{{ $module->id }}" {{ old('module_id', $assignment->module_id) == $module->id ? 'selected' : '' }}>
                            {{ $module->getCode() }} — {{ $module->title }}
                        </option>
                        @endforeach
                    </select>
                    @error('module_id') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- WYSIWYG / HTML Editor for Description --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-black text-black uppercase tracking-wider">
                            Deskripsi / Instruksi Tugas
                        </label>
                        <button type="button" @click="codeMode = !codeMode; syncCodeMode(codeMode)" class="px-3 py-1 bg-black hover:bg-slate-800 text-white rounded-lg text-xs font-black transition flex items-center gap-1.5 shadow-xs">
                            <i class="fas fa-code text-amber-400"></i>
                            <span x-text="codeMode ? 'Mode Visual (WYSIWYG)' : 'Mode Kode HTML'"></span>
                        </button>
                    </div>

                    {{-- Visual Quill Editor --}}
                    <div x-show="!codeMode" class="bg-white rounded-2xl shadow-sm">
                        <div id="quill-assignment-editor">{!! old('description', $assignment->description) !!}</div>
                    </div>

                    {{-- Raw HTML Editor --}}
                    <div x-show="codeMode" x-cloak>
                        <textarea id="quill-assignment-raw-textarea"
                                  class="w-full h-56 font-mono text-xs p-4 rounded-2xl border-2 border-black bg-slate-900 text-amber-300 focus:ring-4 focus:ring-black/20 outline-none resize-y"
                                  oninput="syncRawHtml(this.value)"
                                  placeholder="<p>Tuliskan instruksi penugasan dalam format kode HTML...</p>"></textarea>
                    </div>

                    <input type="hidden" name="description" id="quill-assignment-input" value="{{ old('description', $assignment->description) }}">
                    <p class="text-[11px] text-gray-500 font-bold mt-1.5">
                        <i class="fas fa-info-circle text-sky-600 mr-1"></i> Editor mendukung teks tebal, miring, daftar nomor/bullet, tabel, blok kode, dan link.
                    </p>
                </div>

                {{-- Mode Penugasan: Individu vs Kelompok --}}
                <div class="p-5 rounded-2xl border-2 border-black bg-slate-50 space-y-3">
                    <label class="block text-xs font-black text-black uppercase tracking-wider">Mode Penugasan <span class="text-rose-600">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-black bg-white cursor-pointer hover:bg-amber-50 transition-all shadow-xs">
                            <input type="radio" name="is_group_assignment" value="0" {{ old('is_group_assignment', $assignment->is_group_assignment ? '1' : '0') == '0' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-black border-2 border-black focus:ring-0">
                            <div>
                                <span class="font-black text-black text-sm block">👤 Tugas Individu</span>
                                <span class="text-xs font-bold text-gray-500">Setiap siswa mengumpulkan tugas masing-masing secara terpisah.</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-black bg-white cursor-pointer hover:bg-amber-50 transition-all shadow-xs">
                            <input type="radio" name="is_group_assignment" value="1" {{ old('is_group_assignment', $assignment->is_group_assignment ? '1' : '0') == '1' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-black border-2 border-black focus:ring-0">
                            <div>
                                <span class="font-black text-black text-sm block">👥 Tugas Kelompok</span>
                                <span class="text-xs font-bold text-gray-500">Hanya Ketua Kelompok yang mengumpulkan 1 berkas. Nilai otomatis masuk ke seluruh anggota.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Tipe Pengumpulan <span class="text-rose-600">*</span></label>
                        <select name="assignment_type" required class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                            <option value="file" {{ old('assignment_type', $assignment->assignment_type) === 'file' ? 'selected' : '' }}>Upload File</option>
                            <option value="text" {{ old('assignment_type', $assignment->assignment_type) === 'text' ? 'selected' : '' }}>Teks</option>
                            <option value="file_text" {{ old('assignment_type', $assignment->assignment_type) === 'file_text' ? 'selected' : '' }}>File + Teks</option>
                            <option value="link" {{ old('assignment_type', $assignment->assignment_type) === 'link' ? 'selected' : '' }}>Link URL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Deadline</label>
                        <input type="datetime-local" name="due_date"
                               value="{{ old('due_date', $assignment->deadline ? \Carbon\Carbon::parse($assignment->deadline)->format('Y-m-d\TH:i') : '') }}"
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Skor Maksimal <span class="text-rose-600">*</span></label>
                        <input type="number" name="max_score" value="{{ old('max_score', $assignment->max_score) }}" min="1" max="100" required
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex items-center gap-3 bg-amber-100 border-2 border-black rounded-2xl p-4 shadow-xs">
                        <label class="flex items-center gap-3 text-xs font-black text-black uppercase cursor-pointer">
                            <input type="checkbox" name="allow_resubmit" value="1"
                                   {{ old('allow_resubmit', $assignment->allow_resubmit) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-2 border-black text-black focus:ring-0"
                                   onchange="document.getElementById('max_resubmissions_field').classList.toggle('hidden')">
                            Boleh Revisi / Kirim Ulang
                        </label>
                    </div>
                    <div id="max_resubmissions_field" class="{{ old('allow_resubmit', $assignment->allow_resubmit) ? '' : 'hidden' }}">
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Maks Revisi</label>
                        <input type="number" name="max_resubmissions" value="{{ old('max_resubmissions', $assignment->max_resubmissions ?? 3) }}" min="1" max="10"
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                </div>

                @if($assignment->file_path)
                <div class="bg-slate-100 rounded-2xl border-2 border-black p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-black flex items-center justify-center text-amber-400 font-black shrink-0">
                            <i class="fas fa-paperclip"></i>
                        </span>
                        <div>
                            <p class="text-xs font-black text-black">Berkas Lampiran Saat Ini:</p>
                            <p class="text-xs font-bold text-gray-600 truncate max-w-sm">{{ basename($assignment->file_path) }}</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-black uppercase bg-emerald-300 text-black px-2.5 py-1 rounded-lg border border-black">Tersedia</span>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Unggah File Lampiran Baru (Opsional, Maks 10 MB)</label>
                    <input type="file" name="file" class="w-full text-xs text-black font-bold file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-2 file:border-black file:text-xs file:font-black file:bg-amber-300 file:text-black hover:file:bg-black hover:file:text-white cursor-pointer">
                    <p class="text-[11px] text-gray-500 font-bold mt-1">Biarkan kosong jika tidak ingin mengubah berkas lampiran yang sudah ada.</p>
                </div>
            </div>

            <div class="pt-6 border-t-2 border-black flex gap-3">
                <a href="{{ route('guru.lms.assignments.show', $assignment->id) }}" class="flex-1 bg-slate-200 text-black border-2 border-black px-6 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider hover:bg-slate-300 transition text-center">Batal</a>
                <button type="submit" class="flex-[2] bg-emerald-600 text-white hover:bg-emerald-700 px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-md border-2 border-black">
                    <i class="fas fa-save mr-1.5 text-amber-400"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    let quillAssignment = null;

    document.addEventListener('DOMContentLoaded', () => {
        const toolbarOptions = [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'align': [] }],
            ['table'],
            ['code-block'],
            ['link'],
            ['clean']
        ];

        const editorEl = document.getElementById('quill-assignment-editor');
        if (editorEl) {
            quillAssignment = new Quill('#quill-assignment-editor', {
                theme: 'snow',
                placeholder: 'Jelaskan instruksi tugas yang harus dikerjakan siswa...',
                modules: {
                    toolbar: toolbarOptions,
                    table: true
                }
            });

            quillAssignment.on('text-change', function() {
                const input = document.getElementById('quill-assignment-input');
                const rawEl = document.getElementById('quill-assignment-raw-textarea');
                const val = quillAssignment.root.innerHTML === '<p><br></p>' ? '' : quillAssignment.root.innerHTML;
                if (input) input.value = val;
                if (rawEl && document.activeElement !== rawEl) rawEl.value = val;
            });

            // Initialize raw textarea value
            const rawEl = document.getElementById('quill-assignment-raw-textarea');
            const input = document.getElementById('quill-assignment-input');
            if (rawEl && input) {
                rawEl.value = input.value;
            }
        }

        const form = document.getElementById('assignmentForm');
        if (form) {
            form.addEventListener('submit', function() {
                const input = document.getElementById('quill-assignment-input');
                if (quillAssignment && input) {
                    input.value = quillAssignment.root.innerHTML === '<p><br></p>' ? '' : quillAssignment.root.innerHTML;
                }
            });
        }
    });

    function syncCodeMode(isCodeMode) {
        const rawEl = document.getElementById('quill-assignment-raw-textarea');
        const input = document.getElementById('quill-assignment-input');
        if (isCodeMode) {
            const currentHtml = quillAssignment ? (quillAssignment.root.innerHTML === '<p><br></p>' ? '' : quillAssignment.root.innerHTML) : (input ? input.value : '');
            if (rawEl) rawEl.value = currentHtml;
        } else {
            if (rawEl && quillAssignment) {
                quillAssignment.root.innerHTML = rawEl.value;
                if (input) input.value = rawEl.value;
            }
        }
    }

    function syncRawHtml(value) {
        const input = document.getElementById('quill-assignment-input');
        if (input) input.value = value;
        if (quillAssignment) quillAssignment.root.innerHTML = value;
    }
</script>
@endpush