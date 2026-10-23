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
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tahun Ajaran</label>
            <p class="font-bold text-gray-800">{{ $bill->academicYear->year }}</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status Saat Ini</label>
            @if($bill->status === 'lunas')
                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                    ✓ Lunas (Dibayar Rp {{ number_format($bill->paid_amount, 0, ',', '.') }})
                </span>
            @elseif($bill->paid_amount > 0)
                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    Cicilan (Dibayar Rp {{ number_format($bill->paid_amount, 0, ',', '.') }})
                </span>
            @else
                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                    Belum Dibayar
                </span>
            @endif
        </div>
    </div>

    @if(isset($siblingBills) && $siblingBills->count() > 1)
    @php
        $monthNamesMap = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    @endphp
    <div class="bg-white rounded-2xl border border-gray-200 p-4 flex flex-wrap items-center gap-2">
        <span class="text-xs font-bold text-gray-500 mr-2">Pilih Bulan Tagihan:</span>
        @foreach($siblingBills as $sb)
            @php
                $isCurrent = $sb->id === $bill->id;
                $sbPaid = $sb->status === 'lunas';
                $sbPartial = !$sbPaid && $sb->paid_amount > 0;
            @endphp
            <a href="{{ route('treasurer.bills.edit', $sb->id) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $isCurrent ? 'bg-indigo-600 text-white shadow-md ring-2 ring-indigo-300' : ($sbPaid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : ($sbPartial ? 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')) }}">
                <span>{{ $monthNamesMap[$sb->month] ?? $sb->month }}</span>
                @if($sbPaid)
                    <span>✓</span>
                @elseif($sbPartial)
                    <span>½</span>
                @endif
            </a>
        @endforeach
    </div>
    @endif

    <form action="{{ route('treasurer.bills.update', $bill) }}" method="POST">
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
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pengaturan Nominal & Potongan Tagihan
            </h3>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Tarif Standar / Referensi -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tarif Normal / Standar (Rp)</label>
                        <input type="number" id="base_tarif" value="{{ (int)$baseTarif }}" min="0" step="1000"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-semibold text-gray-700">
                        <p class="text-xs text-gray-500 mt-1">Tarif dasar acuan sebelum potongan</p>
                    </div>

                    <!-- Nominal Potongan / Diskon -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Potongan / Beasiswa (Rp)</label>
                        <input type="number" name="discount_amount" id="discount_amount" value="{{ (int)$initialDiscount }}" min="0" step="1000"
                            class="w-full px-4 py-3 border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-emerald-700 font-bold bg-emerald-50/30">
                        <p class="text-xs text-gray-500 mt-1">Besaran diskon atau potongan biaya</p>
                    </div>

                    <!-- Nominal Akhir Tagihan -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tagihan Akhir (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="amount" value="{{ (int)$initialAmount }}" min="0" step="1000" required
                            class="w-full px-4 py-3 border-2 border-emerald-500 rounded-xl focus:ring-2 focus:ring-emerald-500 font-black text-gray-900 text-lg">
                        <p class="text-xs text-emerald-600 font-medium mt-1">Nominal bersih yang wajib dibayar siswa</p>
                    </div>
                </div>

                <!-- Tombol Cepat Diskon -->
                <div class="flex flex-wrap items-center gap-2 p-3 bg-gray-50 rounded-xl text-xs">
                    <span class="font-bold text-gray-600 mr-1"><i class="fas fa-bolt text-amber-500"></i> Tombol Cepat Potongan:</span>
                    <button type="button" onclick="setQuickDiscount(1.0)" class="px-2.5 py-1 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 transition">
                        Gratis 100% (Bebas Biaya)
                    </button>
                    <button type="button" onclick="setQuickDiscount(0.5)" class="px-2.5 py-1 bg-teal-600 text-white rounded-lg font-bold hover:bg-teal-700 transition">
                        Potongan 50%
                    </button>
                    <button type="button" onclick="setQuickDiscount(0.25)" class="px-2.5 py-1 bg-cyan-600 text-white rounded-lg font-bold hover:bg-cyan-700 transition">
                        Potongan 25%
                    </button>
                    <button type="button" onclick="setQuickDiscount(0)" class="px-2.5 py-1 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition">
                        Reset (Tanpa Potongan)
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan / Jenis Potongan</label>
                        <input type="text" name="discount_reason" id="discount_reason" list="discount_reasons_list"
                            value="{{ old('discount_reason', $bill->discount_reason) }}"
                            placeholder="Contoh: Beasiswa Prestasi, Anak Guru, Bantuan Yayasan, Keringanan Khusus"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <datalist id="discount_reasons_list">
                            <option value="Beasiswa Prestasi">
                            <option value="Anak Guru / Pegawai Yayasan">
                            <option value="Bantuan Khusus Yayasan">
                            <option value="Keringanan Keluarga Prasejahtera">
                            <option value="Saudara Kandung (Sibling Discount)">
                            <option value="Keringanan Musibah / Khusus">
                        </datalist>
                        <p class="text-xs text-gray-500 mt-1">Keterangan alasan pemberian potongan atau nama program beasiswa</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $bill->due_date ? $bill->due_date->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <p class="text-xs text-gray-500 mt-1">Default tanggal 10 bulan tagihan</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="Tambahkan catatan jika diperlukan...">{{ old('notes', $bill->notes) }}</textarea>
                </div>

                @if($bill->month)
                <!-- Pilihan Bawa Potongan ke Bulan-Bulan Berikutnya -->
                <div class="p-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-300 rounded-2xl space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="apply_to_subsequent_months" value="1" checked
                            class="mt-1 w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                        <div>
                            <span class="font-extrabold text-emerald-950 text-sm flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Terapkan perubahan nominal / diskon / beasiswa ini ke bulan-bulan berikutnya
                            </span>
                            <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                                Jika diaktifkan, pengaturan potongan/nominal untuk bulan {{ $monthNames[$bill->month] ?? $bill->month }} ini otomatis berlaku untuk bulan-bulan berikutnya sesuai cakupan periode di bawah ini.
                            </p>
                        </div>
                    </label>
                    <div class="pl-8 grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-emerald-300 bg-white cursor-pointer hover:bg-emerald-50/50 transition">
                            <input type="radio" name="carryover_scope" value="semester" checked class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <p class="text-xs font-extrabold text-emerald-950">Sampai Akhir Semester Ini (Default)</p>
                                <p class="text-[11px] text-gray-500">{{ $bill->month >= 7 ? 'Semester Ganjil (s.d. Desember)' : 'Semester Genap (s.d. Juni)' }}</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 bg-white cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" name="carryover_scope" value="academic_year" class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <p class="text-xs font-extrabold text-gray-800">Sampai Akhir Tahun Pelajaran</p>
                                <p class="text-[11px] text-gray-500">Seluruh bulan tersisa di {{ $bill->academicYear->year }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                @if($bill->paid_amount > 0 || $bill->status === 'lunas')
                <!-- Opsi Pindahkan Pembayaran ke Bulan Lain (jika salah bulan klik lunas) -->
                <div class="p-4 bg-amber-50 border-2 border-amber-300 rounded-2xl space-y-2">
                    <label class="block text-sm font-extrabold text-amber-950">
                        🔄 Pindahkan Status Lunas / Pembayaran Bulan Ini ke Bulan Lain (Opsional)
                    </label>
                    <p class="text-xs text-amber-800">
                        Jika pembayaran bulan <strong>{{ $monthNames[$bill->month] ?? $bill->month }}</strong> ini sebenarnya dimaksudkan untuk bulan lain (misalnya <strong>Agustus</strong>), pilih bulan tujuan di bawah lalu klik <strong>Simpan Perubahan</strong>:
                    </p>
                    <select name="move_payment_to_month" class="w-full md:w-72 px-3 py-2 text-sm border border-amber-400 rounded-xl bg-white font-bold text-gray-800">
                        <option value="">-- Tetap di Bulan {{ $monthNames[$bill->month] ?? $bill->month }} (Jangan Pindahkan) --</option>
                        @foreach([7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember', 1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni'] as $mNum => $mLabel)
                            @if($mNum != $bill->month)
                                <option value="{{ $mNum }}">Pindahkan Pembayaran ke Bulan {{ $mLabel }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                @endif
                @endif
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
