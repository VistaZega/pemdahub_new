@extends('layouts.guru')
@section('title', 'Buat Bank Soal')
@section('content')
<div class="space-y-8" x-data="{
    mode: 'manual',
    isDragging: false,
    fileName: '',
    fileSize: '',
    handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        }
    },
    handleDrop(e) {
        const file = e.dataTransfer.files[0];
        if (file) {
            this.$refs.fileInput.files = e.dataTransfer.files;
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        }
    },
    clearFile() {
        this.fileName = '';
        this.fileSize = '';
        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
    }
}">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white shadow-lg">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('guru.cbt.banks.index') }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200 shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Buat Bank Soal</h1>
                    <p class="text-emerald-50 mt-1 text-base">Buat koleksi soal baru atau import langsung dari Excel/ZIP</p>
                    @if(isset($school))
                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-white/20 text-white rounded-lg text-xs font-bold border border-white/20 shadow-xs">
                        <i class="fas fa-school text-amber-300"></i>
                        <span>Unit: {{ $school->name }} ({{ strtoupper($school->type) }})</span>
                    </div>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('guru.cbt.banks.import-template') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/15 hover:bg-white/25 text-white rounded-xl transition text-base font-semibold border border-white/20 shadow-sm">
                    <i class="fas fa-download"></i>
                    <span>Download Template Excel</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-triangle text-red-600 text-lg"></i></div>
        <p class="text-red-700 text-base font-bold">{{ session('error') }}</p>
    </div>
    @endif

    @if(session('import_warnings') && count(session('import_warnings')) > 0)
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl shadow-sm space-y-2">
        <div class="flex items-center gap-2 font-bold text-amber-800 text-base">
            <i class="fas fa-exclamation-circle text-amber-600"></i>
            <span>Beberapa baris dilewati saat proses sebelumnya:</span>
        </div>
        <ul class="list-disc list-inside text-sm text-amber-700 space-y-1 pl-2 font-medium">
            @foreach(session('import_warnings') as $warning)
            <li>{{ $warning }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Mode Selector Tabs --}}
    <div class="flex border-b border-gray-200 bg-white rounded-t-2xl px-6 pt-4 gap-4 shadow-sm overflow-x-auto">
        <button type="button" @click="mode = 'manual'"
            :class="mode === 'manual' ? 'border-b-4 border-emerald-600 text-emerald-700 font-black' : 'text-gray-500 hover:text-gray-700 font-bold border-b-4 border-transparent'"
            class="pb-4 px-4 text-base sm:text-lg transition flex items-center gap-2 cursor-pointer whitespace-nowrap">
            <i class="fas fa-pen-to-square"></i> Buat Manual (Soal Ditambahkan Nanti)
        </button>
        <button type="button" @click="mode = 'import'"
            :class="mode === 'import' ? 'border-b-4 border-emerald-600 text-emerald-700 font-black' : 'text-gray-500 hover:text-gray-700 font-bold border-b-4 border-transparent'"
            class="pb-4 px-4 text-base sm:text-lg transition flex items-center gap-2 cursor-pointer whitespace-nowrap">
            <i class="fas fa-file-excel text-emerald-600"></i> Import Langsung (Excel / ZIP)
        </button>
    </div>

    {{-- Form Section --}}
    <div class="bg-white rounded-b-2xl shadow-sm border border-t-0 border-gray-200 p-8">
        
        {{-- MODE 1: MANUAL FORM --}}
        <div x-show="mode === 'manual'" x-transition>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"><i class="fas fa-info-circle text-emerald-600 text-lg"></i></div>
                <div>
                    <h2 class="text-lg font-black text-gray-900">Informasi Bank Soal (Manual)</h2>
                    <p class="text-sm text-gray-600">Soal dapat Anda ketik atau tambahkan satu per satu setelah bank soal disimpan.</p>
                </div>
            </div>

            <form action="{{ route('guru.cbt.banks.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Bank Soal <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" required
                            class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800 font-medium text-base"
                            placeholder="Contoh: UAS Matematika Kelas 10 Semester 1">
                        @error('bank_name')<p class="text-red-500 text-base mt-1 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select name="subject_id" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800 font-medium text-base">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ old('subject_id') == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')<p class="text-red-500 text-base mt-1 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tingkat Kelas <span class="text-red-500">*</span></label>
                        <select name="grade_level" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800 font-medium text-base">
                            @foreach($gradeLevels as $gl)
                            <option value="{{ $gl }}" {{ old('grade_level') == $gl ? 'selected' : '' }}>Kelas {{ $gl }}</option>
                            @endforeach
                        </select>
                        @error('grade_level')<p class="text-red-500 text-base mt-1 font-bold">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800 font-medium text-base"
                            placeholder="Deskripsi opsional...">{{ old('description') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 p-4 rounded-xl bg-gray-50 border border-gray-200 cursor-pointer hover:bg-emerald-50 transition shadow-sm">
                            <input type="checkbox" name="is_shared" value="1" {{ old('is_shared') ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-6 h-6">
                            <div>
                                <span class="text-base font-bold text-gray-900">Bagikan ke guru lain</span>
                                <p class="text-sm text-gray-600 mt-0.5 font-medium">Guru lain dapat menggunakan soal dari bank ini dalam ujian mereka</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('guru.cbt.banks.index') }}" class="px-6 py-3 bg-white border border-gray-300 text-gray-800 rounded-xl hover:bg-gray-50 transition text-base font-bold">Batal</a>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl hover:shadow-lg transition text-base font-bold flex items-center gap-2 shadow-md">
                        <i class="fas fa-save"></i> Simpan Bank Soal
                    </button>
                </div>
            </form>
        </div>

        {{-- MODE 2: IMPORT FORM --}}
        <div x-show="mode === 'import'" x-transition x-cloak>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center"><i class="fas fa-file-import text-teal-600 text-lg"></i></div>
                    <div>
                        <h2 class="text-lg font-black text-gray-900">Import Bank Soal dari Excel / ZIP</h2>
                        <p class="text-sm text-gray-600">Unggah file Excel (.xlsx) atau ZIP untuk langsung membuat bank soal beserta seluruh butir soalnya.</p>
                    </div>
                </div>
                <a href="{{ route('guru.cbt.banks.import-template') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-xl font-bold text-sm transition">
                    <i class="fas fa-download"></i> Download Template
                </a>
            </div>

            <form action="{{ route('guru.cbt.banks.import') }}" method="POST" enctype="multipart/form-data" 
                  onsubmit="document.getElementById('importSubmitBtn').innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Memproses Import...'; document.getElementById('importSubmitBtn').classList.add('opacity-75', 'cursor-not-allowed'); return true;">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Bank Soal <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" required
                            class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 px-4 py-3 text-gray-800 font-medium text-base"
                            placeholder="Contoh: UAS Matematika Kelas 10 Semester 1 (Import)">
                    </div>
                    <div>
                        <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select name="subject_id" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 px-4 py-3 text-gray-800 font-medium text-base">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ old('subject_id') == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tingkat Kelas <span class="text-red-500">*</span></label>
                        <select name="grade_level" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 px-4 py-3 text-gray-800 font-medium text-base">
                            @foreach($gradeLevels as $gl)
                            <option value="{{ $gl }}" {{ old('grade_level') == $gl ? 'selected' : '' }}>Kelas {{ $gl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="2"
                            class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 px-4 py-3 text-gray-800 font-medium text-base"
                            placeholder="Deskripsi opsional...">{{ old('description') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 p-4 rounded-xl bg-gray-50 border border-gray-200 cursor-pointer hover:bg-teal-50 transition shadow-sm">
                            <input type="checkbox" name="is_shared" value="1" {{ old('is_shared') ? 'checked' : '' }} class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 w-6 h-6">
                            <div>
                                <span class="text-base font-bold text-gray-900">Bagikan ke guru lain</span>
                                <p class="text-sm text-gray-600 mt-0.5 font-medium">Guru lain dapat menggunakan soal dari bank ini dalam ujian mereka</p>
                            </div>
                        </label>
                    </div>

                    {{-- Upload Area --}}
                    <div class="md:col-span-2 space-y-3 pt-2">
                        <label class="block text-base font-bold text-gray-800 uppercase tracking-wider">Unggah File Soal (.XLSX / .ZIP) <span class="text-red-500">*</span></label>
                        <div class="relative"
                             @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="handleDrop($event)">
                            <label :class="isDragging ? 'border-teal-500 bg-teal-50 scale-[1.01]' : (fileName ? 'border-emerald-400 bg-emerald-50/60' : 'border-gray-300 bg-gray-50 hover:bg-teal-50/50 hover:border-teal-300')"
                                   class="flex flex-col items-center justify-center w-full p-8 border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-200">
                                <template x-if="!fileName">
                                    <div class="text-center">
                                        <div class="w-16 h-16 rounded-2xl bg-teal-100 flex items-center justify-center mx-auto mb-3 shadow-sm">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-teal-600"></i>
                                        </div>
                                        <p class="text-base font-bold text-gray-800">Klik atau seret file Excel/ZIP ke area ini</p>
                                        <p class="text-sm font-medium text-gray-600 mt-1">Format: <strong>.xlsx</strong> (Excel saja) atau <strong>.zip</strong> (Excel + gambar)</p>
                                        <p class="text-xs text-gray-400 mt-1">Maksimal 50MB</p>
                                    </div>
                                </template>
                                <template x-if="fileName">
                                    <div class="text-center">
                                        <div class="w-16 h-16 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-3 shadow-sm">
                                            <i class="fas fa-file-check text-3xl text-emerald-600"></i>
                                        </div>
                                        <p class="text-base font-black text-emerald-900" x-text="fileName"></p>
                                        <p class="text-sm font-bold text-emerald-700 mt-1" x-text="fileSize"></p>
                                        <button type="button" @click.stop="clearFile()" class="mt-3 px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                            <i class="fas fa-times"></i> Hapus & ganti file
                                        </button>
                                    </div>
                                </template>
                                <input type="file" name="import_file" accept=".xlsx,.xls,.zip" class="hidden" x-ref="fileInput"
                                       :required="mode === 'import'" @change="handleFileSelect($event)">
                            </label>
                        </div>
                    </div>

                    {{-- Info Cards --}}
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-100 shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-square-root-variable text-blue-600 text-lg"></i>
                                <span class="text-base font-black text-blue-900">Formula LaTeX</span>
                            </div>
                            <div class="space-y-1.5 text-sm text-blue-800 font-medium">
                                <p>Tulis rumus matematika di antara tanda <code class="bg-blue-100 px-1.5 py-0.5 rounded font-bold text-blue-950">$...$</code></p>
                                <div class="grid grid-cols-2 gap-2 mt-2 font-mono text-xs">
                                    <span class="bg-blue-100/70 p-1.5 rounded">$x^2$ → Pangkat</span>
                                    <span class="bg-blue-100/70 p-1.5 rounded">$\sqrt{x}$ → Akar</span>
                                    <span class="bg-blue-100/70 p-1.5 rounded">$\frac{a}{b}$ → Pecahan</span>
                                    <span class="bg-blue-100/70 p-1.5 rounded">$H_2O$ → Subskrip</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-5 border border-amber-100 shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-image text-amber-600 text-lg"></i>
                                <span class="text-base font-black text-amber-900">Gambar Soal via ZIP</span>
                            </div>
                            <div class="space-y-1.5 text-sm text-amber-800 font-medium">
                                <p>Untuk soal bergambar, kemas ke file <strong>.zip</strong> dengan struktur:</p>
                                <div class="bg-amber-100/70 rounded-xl p-2.5 mt-1.5 font-mono text-xs text-amber-950">
                                    <div>📦 bank_soal.zip</div>
                                    <div class="ml-3">📄 soal.xlsx</div>
                                    <div class="ml-3">📁 images/</div>
                                    <div class="ml-6">🖼️ gbr1.jpg, gbr2.png...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('guru.cbt.banks.index') }}" class="px-6 py-3 bg-white border border-gray-300 text-gray-800 rounded-xl hover:bg-gray-50 transition text-base font-bold">Batal</a>
                    <button type="submit" id="importSubmitBtn"
                            :class="fileName ? 'bg-gradient-to-r from-teal-600 to-emerald-600 hover:shadow-lg cursor-pointer' : 'bg-gray-300 cursor-not-allowed pointer-events-none'"
                            class="px-6 py-3 text-white rounded-xl transition text-base font-bold flex items-center gap-2 shadow-md">
                        <i class="fas fa-file-import"></i> Simpan & Import Sekarang
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
