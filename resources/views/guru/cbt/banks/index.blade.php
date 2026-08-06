@extends('layouts.guru')
@section('title', 'Bank Soal')
@section('content')
<div class="space-y-8" x-data="{ showModal: false }">
    {{-- Hero Header (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 md:p-8 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #0f766e 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-md text-2xl">
                    <i class="fas fa-database text-black"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight" style="color: #ffffff !important;">Bank Soal</h1>
                    <p class="text-xs md:text-sm font-bold text-teal-200 mt-1" style="color: #99f6e4 !important;">Kelola koleksi soal untuk ujian CBT siswa</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" @click="showModal = true" 
                        class="px-5 py-2.5 bg-white text-black border-2 border-black rounded-2xl font-black text-xs uppercase tracking-wider transition flex items-center gap-2 shadow-md cursor-pointer">
                    <i class="fas fa-file-import text-black"></i><span>Import Soal</span>
                </button>
                <a href="{{ route('guru.cbt.banks.create') }}" class="px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-2xl font-black text-xs uppercase tracking-wider shadow-md transition flex items-center gap-2">
                    <i class="fas fa-plus text-black"></i><span>Buat Bank Soal</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600 text-lg"></i></div>
        <p class="text-emerald-800 text-base font-bold">{{ session('success') }}</p>
    </div>
    @endif

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
            <span>Beberapa baris dilewati saat import:</span>
        </div>
        <ul class="list-disc list-inside text-sm text-amber-700 space-y-1 pl-2 font-medium">
            @foreach(session('import_warnings') as $warning)
            <li>{{ $warning }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Bank Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($banks as $bank)
        <a href="{{ route('guru.cbt.banks.show', $bank) }}" class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-book text-emerald-600 text-lg"></i>
                </div>
                <span class="px-2.5 py-1 rounded-xl text-base font-bold border {{ $bank->is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-gray-50 text-gray-700 border-gray-200' }}">
                    {{ $bank->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <h3 class="font-bold text-gray-900 mb-1 group-hover:text-emerald-700 transition-colors">{{ $bank->bank_name }}</h3>
            <p class="text-base text-gray-700 mb-4">{{ $bank->subject->subject_name ?? $bank->subject->name ?? '-' }} &bull; Kelas {{ $bank->grade_level }}</p>
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="flex items-center gap-1.5 text-base text-gray-700">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 flex items-center justify-center"><i class="fas fa-question-circle text-blue-500 text-base"></i></div>
                    <span class="font-medium">{{ $bank->questions->count() }} soal</span>
                </div>
                <span class="text-base text-gray-800">{{ $bank->created_at->format('d M Y') }}</span>
            </div>
            @if($bank->is_shared)
            <div class="mt-3 flex items-center gap-1.5 text-base text-teal-600">
                <i class="fas fa-share-alt"></i><span>Dibagikan</span>
            </div>
            @endif
        </a>
        @empty
        <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-200 p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-database text-3xl text-gray-500"></i></div>
            <h3 class="text-lg font-bold text-gray-700 mb-2">Belum Ada Bank Soal</h3>
            <p class="text-gray-700 mb-4 text-base">Buat bank soal pertama Anda untuk mulai menyusun ujian.</p>
            <a href="{{ route('guru.cbt.banks.create') }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:shadow-lg transition text-base font-medium">
                <i class="fas fa-plus mr-2"></i>Buat Bank Soal
            </a>
        </div>
        @endforelse
    </div>

    @if($banks->hasPages())
    <div class="flex justify-center">{{ $banks->links() }}</div>
    @endif

    {{-- MODAL IMPORT BANK SOAL --}}
    <div x-show="showModal" x-cloak style="display: none;"
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false"></div>

        {{-- Modal Box --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10 border border-gray-200 text-left"
             @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="sticky top-0 z-10 bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 text-white flex justify-between items-center">
                <h3 class="text-lg font-bold flex items-center gap-2"><i class="fas fa-file-import"></i> Import Bank Soal Baru</h3>
                <button type="button" @click="showModal = false" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('guru.cbt.banks.import') }}" method="POST" enctype="multipart/form-data"
                  onsubmit="document.getElementById('importSubmitBtn').innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Mengimpor...'; document.getElementById('importSubmitBtn').classList.add('opacity-75', 'cursor-not-allowed'); return true;">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Nama Bank Soal <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_name" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2.5 text-gray-800 font-medium" placeholder="Contoh: UAS Matematika Kelas 10">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Mata Pelajaran <span class="text-red-500">*</span></label>
                            <select name="subject_id" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2.5 text-gray-800 font-medium">
                                <option value="">-- Pilih Mapel --</option>
                                @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Tingkat Kelas <span class="text-red-500">*</span></label>
                            <select name="grade_level" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2.5 text-gray-800 font-medium">
                                @foreach($gradeLevels as $gl)
                                <option value="{{ $gl }}">Kelas {{ $gl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Deskripsi</label>
                            <textarea name="description" rows="2" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 text-gray-800 font-medium" placeholder="Opsional..."></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                                <input type="checkbox" name="is_shared" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-5 h-5">
                                <span class="text-sm font-bold text-gray-800">Bagikan ke guru lain</span>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-bold text-gray-700 uppercase">File Soal (.XLSX / .ZIP) <span class="text-red-500">*</span></label>
                            <a href="{{ route('guru.cbt.banks.import-template') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline flex items-center gap-1">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                        <input type="file" name="import_file" accept=".xlsx,.xls,.zip" required
                               class="w-full text-sm text-gray-700 font-medium file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-300 rounded-xl p-1.5 bg-gray-50">
                        <p class="text-xs text-gray-500 mt-2 font-medium">Mendukung file .xlsx (Excel saja) atau .zip (Excel beserta folder images/). Maksimal 50MB.</p>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-200">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-bold hover:bg-gray-100 transition text-sm">Batal</button>
                    <button type="submit" id="importSubmitBtn" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold hover:shadow-md transition text-sm flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-file-import"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
