@extends('layouts.guru')
@section('title', 'Edit Bank Soal')
@section('content')
<div class="space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex items-center gap-5">
            <a href="{{ route('guru.cbt.banks.show', $bank) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Bank Soal</h1>
                <p class="text-emerald-50 mt-1 text-base">{{ $bank->bank_name }}</p>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-times text-red-600 text-base"></i></div>
        <p class="text-red-700 text-base font-medium">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"><i class="fas fa-edit text-emerald-600"></i></div>
            <h2 class="text-lg font-bold text-gray-900">Informasi Bank Soal</h2>
        </div>

        {{-- Form DELETE terpisah (di luar form UPDATE untuk menghindari nested form) --}}
        <form id="delete-bank-form" action="{{ route('guru.cbt.banks.destroy', $bank) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus bank soal ini beserta semua soalnya? Tindakan ini tidak dapat dibatalkan.')">
            @csrf @method('DELETE')
        </form>

        <form action="{{ route('guru.cbt.banks.update', $bank) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Bank Soal <span class="text-red-500">*</span></label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $bank->bank_name) }}" required
                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800"
                        placeholder="Contoh: UAS Matematika Kelas 10 Semester 1">
                    @error('bank_name')<p class="text-red-500 text-base mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select name="subject_id" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800">
                        <option value="">-- Pilih Mapel --</option>
                        @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" {{ old('subject_id', $bank->subject_id) == $subj->id ? 'selected' : '' }}>{{ $subj->subject_name ?? $subj->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id')<p class="text-red-500 text-base mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tingkat Kelas <span class="text-red-500">*</span></label>
                    <select name="grade_level" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800">
                        @foreach($gradeLevels as $gl)
                        <option value="{{ $gl }}" {{ old('grade_level', $bank->grade_level) == $gl ? 'selected' : '' }}>Kelas {{ $gl }}</option>
                        @endforeach
                    </select>
                    @error('grade_level')<p class="text-red-500 text-base mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800"
                        placeholder="Deskripsi opsional...">{{ old('description', $bank->description) }}</textarea>
                </div>
                <div>
                    <label class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200 cursor-pointer hover:bg-emerald-50 transition">
                        <input type="checkbox" name="is_shared" value="1" {{ old('is_shared', $bank->is_shared) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-5 h-5">
                        <div>
                            <span class="text-base font-medium text-gray-700">Bagikan ke guru lain</span>
                            <p class="text-base text-gray-800">Guru lain dapat menggunakan soal dari bank ini</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <button type="submit" form="delete-bank-form" class="px-5 py-2.5 bg-white border border-red-200 text-red-600 rounded-xl hover:bg-red-50 transition text-base font-medium flex items-center gap-2">
                    <i class="fas fa-trash"></i>Hapus Bank Soal
                </button>
                <div class="flex gap-3">
                    <a href="{{ route('guru.cbt.banks.show', $bank) }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-800 rounded-xl hover:bg-gray-50 transition text-base font-medium">Batal</a>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:shadow-lg transition text-base font-medium flex items-center gap-2">
                        <i class="fas fa-save"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
