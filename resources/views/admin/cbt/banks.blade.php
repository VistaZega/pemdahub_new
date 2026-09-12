@extends('layouts.admin')
@section('title', 'Bank Soal CBT')
@section('content')
<div class="space-y-8" x-data="importBankModal()">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.cbt.index') }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-white/10">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Bank Soal</h1>
                    <p class="text-violet-50 mt-1 text-base">Semua bank soal yang dibuat oleh guru</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.cbt.banks.import-template') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white rounded-xl transition text-sm font-semibold border border-white/20">
                    <i class="fas fa-download"></i>
                    <span class="hidden sm:inline">Download Template</span>
                </a>
                <button @click="showModal = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-violet-700 rounded-xl hover:bg-violet-50 transition text-sm font-bold shadow-lg hover:shadow-xl">
                    <i class="fas fa-file-import"></i>
                    Import Bank Soal
                </button>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.cbt.banks') }}" class="flex flex-wrap items-end gap-3">
            @if($isSuperAdmin)
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Unit Sekolah</label>
                <select name="school_id" class="w-full rounded-xl border-gray-200 text-sm py-2 focus:border-violet-500 focus:ring-violet-500 font-semibold">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="w-full rounded-xl border-gray-200 text-sm py-2 focus:border-violet-500 focus:ring-violet-500 font-semibold">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>
                        {{ $subj->subject_name ?? $subj->name }} {{ $isSuperAdmin && $subj->school ? '(' . $subj->school->name . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Guru</label>
                <select name="teacher_id" class="w-full rounded-xl border-gray-200 text-sm py-2 focus:border-violet-500 focus:ring-violet-500 font-semibold">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>
                        {{ $t->full_name ?? $t->user->name ?? $t->teacher_code }} {{ $isSuperAdmin && $t->school ? '(' . $t->school->name . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-violet-600 text-white rounded-xl hover:bg-violet-700 transition text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if(request('school_id') || request('subject_id') || request('teacher_id'))
            <a href="{{ route('admin.cbt.banks') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 transition text-sm font-medium">
                <i class="fas fa-times"></i> Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Import Warnings --}}
    @if(session('import_warnings') && count(session('import_warnings')) > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-amber-600 text-sm"></i>
            </div>
            <div>
                <h3 class="font-bold text-amber-800 text-sm">Beberapa baris dilewati saat import:</h3>
                <ul class="mt-2 space-y-1 text-sm text-amber-700">
                    @foreach(session('import_warnings') as $warning)
                    <li class="flex items-start gap-1.5">
                        <i class="fas fa-circle text-[4px] mt-2 flex-shrink-0"></i>
                        {{ $warning }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Banks Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-base">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="bg-gradient-to-r from-gray-50 to-gray-100/80 border-b border-gray-200">
                        <th class="px-5 py-4 text-left text-base font-bold text-gray-700 uppercase tracking-wider">Nama Bank Soal</th>
                        @if($isSuperAdmin)
                        <th class="px-5 py-4 text-left text-base font-bold text-gray-700 uppercase tracking-wider">Unit Sekolah</th>
                        @endif
                        <th class="px-5 py-4 text-left text-base font-bold text-gray-700 uppercase tracking-wider">Mapel</th>
                        <th class="px-5 py-4 text-left text-base font-bold text-gray-700 uppercase tracking-wider">Guru</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Kelas</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Jumlah Soal</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Status & Akses</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($banks as $bank)
                    <tr class="hover:bg-violet-50/30 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-100 to-purple-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-book text-violet-500 text-base"></i>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-900">{{ $bank->bank_name }}</span>
                                    @if($bank->description)
                                    <p class="text-base text-gray-800 mt-0.5 truncate max-w-[240px]">{{ $bank->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @if($isSuperAdmin)
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $bank->school?->name ?? '-' }}
                            </span>
                        </td>
                        @endif
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $bank->subject->subject_name ?? $bank->subject->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-gray-800">{{ $bank->teacher?->full_name ?? '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-violet-50 text-violet-700 text-base font-bold border border-violet-100">Kelas {{ $bank->grade_level }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-100 text-blue-800 font-bold border border-blue-100">{{ $bank->questions_count ?? $bank->questions->count() }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex flex-col items-center gap-1.5">
                                @if($bank->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-bold rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check-circle text-xs"></i>Aktif
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-bold rounded-md bg-gray-50 text-gray-700 border border-gray-200">
                                    <i class="fas fa-pause-circle text-xs"></i>Nonaktif
                                </span>
                                @endif

                                @if($bank->is_shared)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-md bg-purple-50 text-purple-700 border border-purple-200" title="Dapat digunakan oleh semua guru di unit sekolah saat membuat CBT/Kuis">
                                    <i class="fas fa-users text-xs"></i>Dibagikan
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-md bg-amber-50 text-amber-700 border border-amber-200" title="Hanya pemilik dan admin (tidak muncul di dropdown guru lain)">
                                    <i class="fas fa-lock text-xs"></i>Privat
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('admin.cbt.banks.toggle-share', $bank) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center transition border {{ $bank->is_shared ? 'bg-purple-50 text-purple-600 hover:bg-purple-100 border-purple-200' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-200' }}"
                                            title="{{ $bank->is_shared ? 'Ubah menjadi privat (tidak dibagikan)' : 'Bagikan ke semua guru di unit sekolah ini' }}">
                                        <i class="fas {{ $bank->is_shared ? 'fa-share-alt' : 'fa-lock' }} text-xs"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.cbt.banks.destroy', $bank) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus bank soal ini beserta seluruh butir soalnya?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-700 transition border border-red-200"
                                            title="Hapus Bank Soal">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isSuperAdmin ? 8 : 7 }}" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl bg-violet-50 flex items-center justify-center mb-4"><i class="fas fa-database text-2xl text-violet-300"></i></div>
                                <p class="text-gray-700 font-medium">Belum ada bank soal</p>
                                <p class="text-gray-500 text-sm mt-1">Klik "Import Bank Soal" untuk menambahkan soal dari file Excel</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($banks->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">{{ $banks->links() }}</div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- IMPORT MODAL --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>

        {{-- Modal Content --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Modal Header --}}
            <div class="sticky top-0 z-10 bg-gradient-to-r from-violet-600 to-purple-600 rounded-t-2xl px-6 py-5 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="fas fa-file-import text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">Import Bank Soal dari Excel</h2>
                            <p class="text-violet-200 text-sm mt-0.5">Upload file template yang sudah diisi dengan soal-soal</p>
                        </div>
                    </div>
                    <button @click="showModal = false" class="w-8 h-8 rounded-lg bg-white/15 hover:bg-white/25 flex items-center justify-center transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <form action="{{ route('admin.cbt.banks.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6" onsubmit="document.getElementById('importBtn').innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Memproses...'; document.getElementById('importBtn').classList.add('opacity-75', 'cursor-not-allowed'); return true;">
                @csrf

                {{-- Step 1: Bank Info --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2 text-violet-700">
                        <span class="w-7 h-7 rounded-full bg-violet-100 flex items-center justify-center text-sm font-bold">1</span>
                        <h3 class="font-bold text-base">Informasi Bank Soal</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($isSuperAdmin)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Unit Sekolah <span class="text-red-500">*</span></label>
                            <select name="school_id" x-model="selectedSchool" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-violet-500 focus:ring-violet-500 px-4 py-2.5 text-sm font-bold text-gray-800 bg-gray-50">
                                @foreach($schools as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="school_id" value="{{ $schoolId }}">
                        @endif

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Bank Soal <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_name" required value="{{ old('bank_name') }}"
                                   class="w-full rounded-xl border-gray-200 shadow-sm focus:border-violet-500 focus:ring-violet-500 px-4 py-2.5 text-sm"
                                   placeholder="Contoh: UAS Matematika Kelas 10 Sem 1">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Mata Pelajaran <span class="text-red-500">*</span></label>
                            <select name="subject_id" x-model="selectedSubjectId" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-violet-500 focus:ring-violet-500 px-4 py-2.5 text-sm font-semibold">
                                <option value="">-- Pilih Mapel --</option>
                                <template x-for="subj in filteredSubjects" :key="subj.id">
                                    <option :value="subj.id" x-text="subj.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tingkat Kelas <span class="text-red-500">*</span></label>
                            <select name="grade_level" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-violet-500 focus:ring-violet-500 px-4 py-2.5 text-sm font-semibold">
                                @foreach(['7','8','9','10','11','12'] as $gl)
                                <option value="{{ $gl }}" {{ old('grade_level') == $gl ? 'selected' : '' }}>Kelas {{ $gl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Guru Pengampu</label>
                            <select name="teacher_id" x-model="selectedTeacherId" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-violet-500 focus:ring-violet-500 px-4 py-2.5 text-sm font-semibold">
                                <option value="">-- Otomatis (Bank Soal Sekolah) --</option>
                                <template x-for="t in filteredTeachers" :key="t.id">
                                    <option :value="t.id" x-text="t.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                            <input type="text" name="description" value="{{ old('description') }}"
                                   class="w-full rounded-xl border-gray-200 shadow-sm focus:border-violet-500 focus:ring-violet-500 px-4 py-2.5 text-sm"
                                   placeholder="Deskripsi opsional...">
                        </div>

                        <div class="md:col-span-2 pt-1">
                            <label class="flex items-center gap-3 p-3.5 bg-violet-50/70 rounded-xl border border-violet-100 cursor-pointer hover:bg-violet-100/50 transition">
                                <input type="hidden" name="is_shared" value="0">
                                <input type="checkbox" name="is_shared" value="1" checked class="w-5 h-5 rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                                <div>
                                    <span class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                                        <i class="fas fa-users text-violet-600"></i> Bagikan Bank Soal ke Semua Guru
                                    </span>
                                    <p class="text-xs text-gray-600 mt-0.5">Guru mata pelajaran di unit sekolah ini dapat langsung memilih dan menggunakan bank soal ini saat membuat Ujian CBT atau Kuis LMS.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Step 2: Upload File --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-violet-700">
                            <span class="w-7 h-7 rounded-full bg-violet-100 flex items-center justify-center text-sm font-bold">2</span>
                            <h3 class="font-bold text-base">Upload File Soal</h3>
                        </div>
                        <a href="{{ route('admin.cbt.banks.import-template') }}"
                           class="inline-flex items-center gap-1.5 text-sm font-semibold text-violet-600 hover:text-violet-800 transition">
                            <i class="fas fa-download text-xs"></i> Download Template
                        </a>
                    </div>

                    {{-- Upload Area --}}
                    <div class="relative"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)">
                        <label :class="isDragging ? 'border-violet-500 bg-violet-50 scale-[1.01]' : (fileName ? 'border-emerald-300 bg-emerald-50' : 'border-gray-300 bg-gray-50 hover:bg-violet-50 hover:border-violet-300')"
                               class="flex flex-col items-center justify-center w-full p-8 border-2 border-dashed rounded-xl cursor-pointer transition-all duration-200">
                            <template x-if="!fileName">
                                <div class="text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-violet-100 flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-violet-500"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700">Klik atau seret file ke sini</p>
                                    <p class="text-xs text-gray-500 mt-1">Format: <strong>.xlsx</strong> (Excel saja) atau <strong>.zip</strong> (Excel + gambar)</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Maksimal 50MB</p>
                                </div>
                            </template>
                            <template x-if="fileName">
                                <div class="text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-file-check text-2xl text-emerald-500"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-emerald-700" x-text="fileName"></p>
                                    <p class="text-xs text-emerald-600 mt-1" x-text="fileSize"></p>
                                    <button type="button" @click.stop="clearFile()" class="mt-2 text-xs text-red-500 hover:text-red-700 font-semibold">
                                        <i class="fas fa-times"></i> Hapus & ganti file
                                    </button>
                                </div>
                            </template>
                            <input type="file" name="import_file" accept=".xlsx,.xls,.zip" class="hidden" x-ref="fileInput" required
                                   @change="handleFileSelect($event)">
                        </label>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Info Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    {{-- LaTeX Info --}}
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-square-root-variable text-blue-600"></i>
                            <span class="text-sm font-bold text-blue-800">Formula LaTeX</span>
                        </div>
                        <div class="space-y-1 text-xs text-blue-700">
                            <p>Tulis formula di antara tanda <code class="bg-blue-100 px-1 rounded">$...$</code></p>
                            <div class="grid grid-cols-2 gap-x-3 gap-y-0.5 mt-2">
                                <span><code class="bg-blue-100/70 px-1 rounded">$x^2$</code> → pangkat</span>
                                <span><code class="bg-blue-100/70 px-1 rounded">$\sqrt{x}$</code> → akar</span>
                                <span><code class="bg-blue-100/70 px-1 rounded">$\frac{a}{b}$</code> → pecahan</span>
                                <span><code class="bg-blue-100/70 px-1 rounded">$H_2O$</code> → subskrip</span>
                                <span><code class="bg-blue-100/70 px-1 rounded">$F=ma$</code> → rumus</span>
                                <span><code class="bg-blue-100/70 px-1 rounded">$\pi$</code> → simbol</span>
                            </div>
                        </div>
                    </div>

                    {{-- ZIP Info --}}
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-4 border border-amber-100">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-image text-amber-600"></i>
                            <span class="text-sm font-bold text-amber-800">Gambar & Video</span>
                        </div>
                        <div class="space-y-1 text-xs text-amber-700">
                            <p>Untuk soal bergambar, upload file <strong>.zip</strong> berisi:</p>
                            <div class="bg-amber-100/50 rounded-lg p-2 mt-1.5 font-mono text-[11px]">
                                <div>📦 soal.zip</div>
                                <div class="ml-3">📄 soal.xlsx</div>
                                <div class="ml-3">📁 images/</div>
                                <div class="ml-6">🖼️ soal1.jpg, soal2.png...</div>
                            </div>
                            <p class="mt-1">Video: isi kolom <code class="bg-amber-100 px-1 rounded">video_url</code> dengan URL YouTube</p>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false"
                            class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-medium">
                        Batal
                    </button>
                    <button type="submit" id="importBtn"
                            :class="fileName ? 'bg-gradient-to-r from-violet-600 to-purple-600 hover:shadow-lg cursor-pointer' : 'bg-gray-300 cursor-not-allowed pointer-events-none'"
                            class="px-6 py-2.5 text-white rounded-xl transition text-sm font-bold flex items-center gap-2 shadow">
                        <i class="fas fa-file-import"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function importBankModal() {
    const rawSubjects = @json($subjects->map(fn($s) => ['id' => $s->id, 'name' => ($s->subject_name ?? $s->name), 'school_id' => $s->school_id]));
    const rawTeachers = @json($teachers->map(fn($t) => ['id' => $t->id, 'name' => ($t->full_name ?? $t->user?->name ?? $t->teacher_code), 'school_id' => $t->school_id]));

    return {
        showModal: false,
        isDragging: false,
        fileName: '',
        fileSize: '',
        isSubmitting: false,
        allSubjects: rawSubjects,
        allTeachers: rawTeachers,
        selectedSchool: '{{ old("school_id", $schoolId ?? ($schools->first()?->id ?? "")) }}',
        selectedSubjectId: '{{ old("subject_id", "") }}',
        selectedTeacherId: '{{ old("teacher_id", "") }}',

        get filteredSubjects() {
            if (!this.selectedSchool) return this.allSubjects;
            return this.allSubjects.filter(s => s.school_id == this.selectedSchool);
        },

        get filteredTeachers() {
            if (!this.selectedSchool) return this.allTeachers;
            return this.allTeachers.filter(t => t.school_id == this.selectedSchool);
        },

        init() {
            this.$watch('selectedSchool', (val) => {
                this.selectedSubjectId = '';
                this.selectedTeacherId = '';
            });
        },

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) {
                this.setFile(file);
            }
        },

        handleDrop(e) {
            this.isDragging = false;
            const file = e.dataTransfer.files[0];
            if (file) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (['xlsx', 'xls', 'zip'].includes(ext)) {
                    // Set the file to the input
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs.fileInput.files = dt.files;
                    this.setFile(file);
                } else {
                    alert('Format file tidak didukung. Gunakan .xlsx atau .zip');
                }
            }
        },

        setFile(file) {
            this.fileName = file.name;
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            this.fileSize = sizeMB + ' MB';
            const ext = file.name.split('.').pop().toLowerCase();
            if (ext === 'zip') {
                this.fileSize += ' — ZIP (Excel + gambar)';
            } else {
                this.fileSize += ' — Excel';
            }
        },

        clearFile() {
            this.fileName = '';
            this.fileSize = '';
            this.$refs.fileInput.value = '';
        }
    };
}
</script>
@endsection
