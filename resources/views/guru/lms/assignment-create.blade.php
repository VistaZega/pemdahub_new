@extends('layouts.guru')

@section('title', 'Buat Tugas - LMS')

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
    .ql-editor code {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 0.375rem !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-size: 0.875em !important;
        border: 1px solid #e2e8f0 !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{ codeMode: false }">
    <div class="flex items-center gap-4">
        <a href="{{ route('guru.lms.show', $course->id) }}?tab=assignments" class="w-11 h-11 bg-white border-2 border-black rounded-2xl flex items-center justify-center text-black hover:bg-amber-300 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-black tracking-tight">Buat Penugasan Siswa Baru</h2>
            <p class="text-black font-bold text-xs">Course: {{ $course->name }}</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.assignments.store', $course->id) }}" method="POST" enctype="multipart/form-data" id="assignmentCreateForm"
          class="bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden">
        @csrf
        <div class="px-8 py-6 border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
            <h3 class="text-white font-black tracking-wide flex items-center gap-2 text-base uppercase">
                <i class="fas fa-tasks text-amber-400"></i> Detail Informasi Tugas Siswa
            </h3>
            <p class="text-amber-300 text-xs font-bold mt-1">Buat instruksi penugasan visual, batas waktu pengumpulan, dan tipe berkas.</p>
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
                        <div id="quill-assignment-editor">{!! old('description') !!}</div>
                    </div>

                    {{-- Raw HTML Editor --}}
                    <div x-show="codeMode" x-cloak>
                        <textarea id="quill-assignment-raw-textarea"
                                  class="w-full h-56 font-mono text-xs p-4 rounded-2xl border-2 border-black bg-slate-900 text-amber-300 focus:ring-4 focus:ring-black/20 outline-none resize-y"
                                  oninput="syncRawHtml(this.value)"
                                  placeholder="<p>Tuliskan instruksi penugasan dalam format kode HTML...</p>"></textarea>
                    </div>

                    <input type="hidden" name="description" id="quill-assignment-input" value="{{ old('description') }}">
                    <p class="text-[11px] text-gray-500 font-bold mt-1.5">
                        <i class="fas fa-info-circle text-sky-600 mr-1"></i> Editor mendukung teks tebal, miring, daftar nomor/bullet, tabel, blok kode, dan link.
                    </p>
                </div>

                {{-- Mode Penugasan: Individu vs Kelompok --}}
                <div class="p-5 rounded-2xl border-2 border-black bg-slate-50 space-y-3">
                    <label class="block text-xs font-black text-black uppercase tracking-wider">Mode Penugasan <span class="text-rose-600">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-black bg-white cursor-pointer hover:bg-amber-50 transition-all shadow-xs">
                            <input type="radio" name="is_group_assignment" value="0" {{ old('is_group_assignment', '0') == '0' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-black border-2 border-black focus:ring-0">
                            <div>
                                <span class="font-black text-black text-sm block">👤 Tugas Individu</span>
                                <span class="text-xs font-bold text-gray-500">Setiap siswa mengumpulkan tugas masing-masing secara terpisah.</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-black bg-white cursor-pointer hover:bg-amber-50 transition-all shadow-xs">
                            <input type="radio" name="is_group_assignment" value="1" {{ old('is_group_assignment') == '1' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-black border-2 border-black focus:ring-0">
                            <div>
                                <span class="font-black text-black text-sm block">👥 Tugas Kelompok</span>
                                <span class="text-xs font-bold text-gray-500">Hanya Ketua Kelompok yang mengumpulkan 1 berkas. Nilai otomatis masuk ke seluruh anggota kelompok.</span>
                            </div>
                        </label>
                    </div>
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
                               x-model="maxScore"
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                </div>

                {{-- Rubrik Penilaian --}}
                <div class="p-5 rounded-2xl border-2 border-black bg-white space-y-4" x-data="{ 
                    rubrik: {{ old('rubrik') ?: '[{"nama":"Kelengkapan Isi","bobot":40,"maks":100},{"nama":"Struktur & Kerapihan","bobot":30,"maks":100},{"nama":"Kreativitas & Analisis","bobot":30,"maks":100}]' }},
                    maxScore: {{ old('max_score', 100) }},
                    addKomponen() {
                        this.rubrik.push({nama: '', bobot: 20, maks: 100});
                    },
                    hapusKomponen(i) {
                        this.rubrik.splice(i, 1);
                    },
                    get totalBobot() {
                        return this.rubrik.reduce((s, r) => s + parseFloat(r.bobot || 0), 0);
                    },
                    get isValid() {
                        return this.totalBobot === 100;
                    },
                    get hitungSkor() {
                        return this.rubrik.reduce((s, r) => s + (parseFloat(r.maks || 100) * parseFloat(r.bobot || 0) / 100), 0);
                    },
                    serilkan() {
                        document.getElementById('rubrik_json').value = JSON.stringify(this.rubrik);
                    }
                }">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-black uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-tasks text-purple-600"></i> Rubrik Penilaian (Bobot Total: <span x-text="totalBobot" :class="isValid ? 'text-emerald-600' : 'text-rose-600'" class="font-black"></span>%)
                        </label>
                        <button type="button" @click="addKomponen()" class="px-3 py-1.5 bg-purple-600 text-white rounded-xl text-xs font-black hover:bg-purple-700 transition flex items-center gap-1">
                            <i class="fas fa-plus"></i> Tambah Komponen
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 font-bold">Rubrik digunakan saat menilai tugas. Bobot total harus 100%. Skor akhir dihitung otomatis dari nilai tiap komponen × bobot.</p>

                    <template x-for="(r, i) in rubrik" :key="i">
                        <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="flex-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Nama Komponen</label>
                                <input type="text" x-model="r.nama" placeholder="Contoh: Analisis Data"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-purple-500 outline-none">
                            </div>
                            <div class="w-20">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Bobot %</label>
                                <input type="number" x-model="r.bobot" min="0" max="100" step="5"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-purple-500 outline-none">
                            </div>
                            <div class="w-20">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Maks</label>
                                <input type="number" x-model="r.maks" min="1" max="100"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-purple-500 outline-none">
                            </div>
                            <button type="button" @click="hapusKomponen(i)" x-show="rubrik.length > 1" class="mt-5 w-8 h-8 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition flex items-center justify-center">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </template>

                    <div class="flex items-center gap-4 text-xs font-bold text-gray-600 p-3 bg-indigo-50 rounded-xl border border-indigo-200">
                        <span>Skor Maksimal dari Rubrik: <b x-text="Math.round(hitungSkor)"></b></span>
                        <span x-show="!isValid" class="text-rose-600"><i class="fas fa-exclamation-triangle"></i> Bobot belum 100% (<span x-text="totalBobot"></span>%)</span>
                        <span x-show="isValid" class="text-emerald-600"><i class="fas fa-check-circle"></i> Bobot 100% ✅</span>
                    </div>

                    <input type="hidden" name="rubrik" id="rubrik_json" :value="serilkan() || JSON.stringify(rubrik)">
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

        const form = document.getElementById('assignmentCreateForm');
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