@extends('layouts.admin')

@section('title', 'Edit Tagihan')

@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Tagihan</h1>
                <p class="text-gray-600 mt-1">Ubah rincian tagihan pembayaran siswa</p>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
        <ul class="list-disc list-inside text-red-700">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Informasi Siswa & Tagihan (Read-Only) -->
    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Siswa</label>
            <p class="font-bold text-gray-800">{{ $bill->student->full_name }}</p>
            <p class="text-xs text-gray-500">NISN: {{ $bill->student->nisn }}</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jenis Tagihan</label>
            <p class="font-bold text-gray-800">{{ $bill->paymentType->type_name }}</p>
            @if($bill->month)
                @php
                    $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                @endphp
                <span class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-md bg-blue-100 text-blue-800">
                    Bulan {{ $monthNames[$bill->month] ?? $bill->month }} {{ $bill->year }}
                </span>
            @endif
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tahun Ajaran / Semester</label>
            <p class="font-bold text-gray-800">{{ $bill->academicYear->year }}</p>
            <p class="text-xs text-gray-500">{{ $bill->semester->semester_name ?? 'N/A' }}</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Sudah Dibayar</label>
            <p class="font-bold text-emerald-600">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    <form action="{{ route('admin.bills.update', $bill) }}" method="POST">
        @csrf
        @method('PUT')

        @php
            $initialDiscount = (float) old('discount_amount', $bill->discount_amount ?? 0);
            $initialAmount = (float) old('amount', $bill->amount);
            $baseTarif = (float) ($bill->amount + ($bill->discount_amount ?? 0));
            if ($baseTarif <= 0) {
                $baseTarif = (float) ($bill->paymentType->amount ?? $initialAmount);
            }
        @endphp

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <i class="fas fa-tags text-blue-600"></i> Pengaturan Nominal & Potongan Tagihan
            </h3>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tarif Normal / Standar (Rp)</label>
                        <input type="number" id="base_tarif" value="{{ (int)$baseTarif }}" min="0" step="1000"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold text-gray-700">
                        <p class="text-xs text-gray-500 mt-1">Tarif acuan sebelum diskon/potongan</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Potongan / Beasiswa (Rp)</label>
                        <input type="number" name="discount_amount" id="discount_amount" value="{{ (int)$initialDiscount }}" min="0" step="1000"
                            class="w-full px-4 py-3 border border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 text-blue-700 font-bold bg-blue-50/30">
                        <p class="text-xs text-gray-500 mt-1">Besaran diskon atau potongan biaya</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-coins mr-1"></i> Jumlah Tagihan Akhir <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="amount" value="{{ (int)$initialAmount }}" min="0" step="1000" required
                            class="w-full px-4 py-3 border-2 border-blue-600 rounded-xl focus:ring-2 focus:ring-blue-500 font-black text-gray-900 text-lg">
                        <p class="text-xs text-blue-600 font-medium mt-1">Nominal bersih yang wajib dibayar</p>
                    </div>
                </div>

                <!-- Tombol Cepat Diskon -->
                <div class="flex flex-wrap items-center gap-2 p-3 bg-gray-50 rounded-xl text-xs">
                    <span class="font-bold text-gray-600 mr-1"><i class="fas fa-bolt text-amber-500"></i> Tombol Cepat:</span>
                    <button type="button" onclick="setQuickDiscount(1.0)" class="px-2.5 py-1 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 transition">
                        Gratis 100% (Bebas Biaya)
                    </button>
                    <button type="button" onclick="setQuickDiscount(0.5)" class="px-2.5 py-1 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition">
                        Potongan 50%
                    </button>
                    <button type="button" onclick="setQuickDiscount(0.25)" class="px-2.5 py-1 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition">
                        Potongan 25%
                    </button>
                    <button type="button" onclick="setQuickDiscount(0)" class="px-2.5 py-1 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition">
                        Reset (Tanpa Potongan)
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan / Jenis Potongan</label>
                        <input type="text" name="discount_reason" id="discount_reason" list="admin_discount_reasons"
                            value="{{ old('discount_reason', $bill->discount_reason) }}"
                            placeholder="Contoh: Beasiswa Prestasi, Bantuan Yayasan, Keringanan Khusus"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        <datalist id="admin_discount_reasons">
                            <option value="Beasiswa Prestasi">
                            <option value="Anak Guru / Pegawai Yayasan">
                            <option value="Bantuan Khusus Yayasan">
                            <option value="Keringanan Keluarga Prasejahtera">
                            <option value="Saudara Kandung (Sibling Discount)">
                            <option value="Keringanan Musibah / Khusus">
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $bill->due_date ? $bill->due_date->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-info-circle mr-1"></i> Status Pembayaran <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                            <option value="belum_bayar" {{ old('status', $bill->status) == 'belum_bayar' ? 'selected' : '' }}>Belum Dibayar</option>
                            <option value="cicilan" {{ old('status', $bill->status) == 'cicilan' ? 'selected' : '' }}>Dibayar Sebagian (Cicilan)</option>
                            <option value="lunas" {{ old('status', $bill->status) == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-edit mr-1"></i> Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                            placeholder="Tambahkan catatan jika diperlukan...">{{ old('notes', $bill->notes) }}</textarea>
                    </div>
                </div>

                @if($bill->month)
                <!-- Pilihan Bawa Potongan ke Bulan-Bulan Berikutnya -->
                <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-2xl">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="apply_to_subsequent_months" value="1" checked
                            class="mt-1 w-5 h-5 rounded text-blue-600 focus:ring-blue-500 border-gray-300">
                        <div>
                            <span class="font-extrabold text-blue-950 text-sm flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Terapkan potongan & nominal ini ke bulan-bulan berikutnya di tahun ajaran ini
                            </span>
                            <p class="text-xs text-blue-800 mt-1 leading-relaxed">
                                <strong>Sangat direkomendasikan:</strong> Jika diaktifkan, pengaturan potongan untuk bulan {{ $monthNames[$bill->month] ?? $bill->month }} ini otomatis akan dibawa dan diterapkan pada seluruh tagihan yang belum dibayar di bulan-bulan berikutnya hingga akhir Tahun Pelajaran {{ $bill->academicYear->year }}.
                            </p>
                        </div>
                    </label>
                </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" 
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl font-medium hover:from-blue-700 hover:to-indigo-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Perbarui Tagihan
            </button>
            <a href="{{ route('admin.bills.show', $bill) }}" 
                class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
    const baseTarifInput = document.getElementById('base_tarif');
    const discountInput = document.getElementById('discount_amount');
    const amountInput = document.getElementById('amount');

    function syncFromDiscount() {
        const base = parseFloat(baseTarifInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const finalAmount = Math.max(0, base - discount);
        amountInput.value = Math.round(finalAmount);
    }

    function syncFromAmount() {
        const base = parseFloat(baseTarifInput.value) || 0;
        const finalAmount = parseFloat(amountInput.value) || 0;
        const discount = Math.max(0, base - finalAmount);
        discountInput.value = Math.round(discount);
    }

    function setQuickDiscount(pct) {
        const base = parseFloat(baseTarifInput.value) || 0;
        const discount = Math.round(base * pct);
        discountInput.value = discount;
        syncFromDiscount();

        const reasonInput = document.getElementById('discount_reason');
        if (pct === 1.0 && !reasonInput.value) {
            reasonInput.value = 'Beasiswa Penuh (Gratis 100%)';
        } else if (pct === 0.5 && !reasonInput.value) {
            reasonInput.value = 'Keringanan Biaya 50%';
        }
    }

    if (discountInput && amountInput && baseTarifInput) {
        discountInput.addEventListener('input', syncFromDiscount);
        baseTarifInput.addEventListener('input', syncFromDiscount);
        amountInput.addEventListener('input', syncFromAmount);
    }
</script>
@endsection
