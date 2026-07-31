@extends('layouts.treasurer')

@section('title', 'Edit Tagihan')

@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Tagihan Siswa</h1>
                <p class="text-gray-600 mt-1">Ubah nominal atau rincian tagihan siswa yang belum dibayar</p>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-xl text-red-700 font-medium">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
        <ul class="list-disc list-inside text-red-700">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Informasi Siswa & Tagihan -->
    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Siswa</label>
            <p class="font-bold text-gray-800">{{ $bill->student->full_name }}</p>
            <p class="text-xs text-gray-500">NISN: {{ $bill->student->nisn }}</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jenis Tagihan</label>
            <p class="font-bold text-gray-800">{{ $bill->paymentType->type_name }}</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tahun Ajaran</label>
            <p class="font-bold text-gray-800">{{ $bill->academicYear->year }}</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status</label>
            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                Belum Dibayar
            </span>
        </div>
    </div>

    <form action="{{ route('treasurer.bills.update', $bill) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Tagihan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', (int)$bill->amount) }}" min="0" step="1000" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <p class="text-xs text-gray-500 mt-1">Ubah besaran tagihan dalam Rupiah</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $bill->due_date ? $bill->due_date->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="Tambahkan catatan jika diperlukan...">{{ old('notes', $bill->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" 
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-700 hover:to-teal-700 shadow-lg hover:shadow-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Perubahan
            </button>
            <a href="{{ route('treasurer.bills.index') }}" 
                class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
