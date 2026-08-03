@extends('layouts.admin')

@section('title', 'Tambah Tagihan')

@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tambah Tagihan</h1>
                <p class="text-gray-600 mt-1">Buat tagihan pembayaran untuk siswa</p>
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

    <form action="{{ route('admin.bills.store') }}" method="POST">
        @csrf

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-user mr-1"></i> Siswa <span class="text-red-500">*</span></label>
                    <select name="student_id" required id="student_select" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->full_name }} ({{ $student->nisn }})
                        </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih siswa yang akan ditagih</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-credit-card mr-1"></i> Jenis Tagihan <span class="text-red-500">*</span></label>
                        <select name="payment_type_id" id="payment_type_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500" onchange="updateAmounts()">
                            <option value="">-- Pilih Jenis Tagihan --</option>
                            @foreach($paymentTypes as $type)
                            <option value="{{ $type->id }}" 
                                data-amount="{{ $type->amount }}"
                                data-yayasan-share="{{ $type->yayasan_share_amount }}"
                                {{ old('payment_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->type_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Tahun Pelajaran <span class="text-red-500">*</span></label>
                        <select name="academic_year_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500">
                            <option value="">-- Pilih Tahun Pelajaran --</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id', $activeYear->id ?? '') == $year->id ? 'selected' : '' }}>
                                TP. {{ $year->year }} {{ $year->is_active ? '(Aktif)' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-history mr-1"></i> Sifat Tagihan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center p-3 border border-gray-300 rounded-xl cursor-pointer hover:bg-green-50 hover:border-green-500 transition-all">
                            <input type="radio" name="billing_type" value="single" checked onchange="toggleBillingType()"
                                class="w-4 h-4 text-green-600 focus:ring-green-500">
                            <span class="ml-3 font-medium text-gray-900">1 Kali Saja</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-300 rounded-xl cursor-pointer hover:bg-green-50 hover:border-green-500 transition-all">
                            <input type="radio" name="billing_type" value="monthly" onchange="toggleBillingType()"
                                class="w-4 h-4 text-green-600 focus:ring-green-500">
                            <span class="ml-3 font-medium text-gray-900">Bulanan (Berulang)</span>
                        </label>
                    </div>
                </div>

                <!-- Monthly Generation Options (hidden by default) -->
                <div id="monthly_options" style="display: none;">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-xl mb-4">
                        <p class="text-sm text-blue-800">
                            <span class="font-bold"><i class="fas fa-lightbulb text-yellow-500 mr-1"></i> SPP Bulanan:</span> 
                            Sistem akan membuat tagihan untuk beberapa bulan sekaligus dengan jatuh tempo tanggal yang sama setiap bulannya.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Bulan Mulai</label>
                            <select name="start_month" id="start_month" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500">
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7" selected>Juli (Awal Tahun Ajaran)</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-list-ol mr-1"></i> Jumlah Bulan</label>
                            <select name="generate_months" id="generate_months" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500">
                                <option value="1">1 Bulan</option>
                                <option value="2">2 Bulan</option>
                                <option value="3">3 Bulan</option>
                                <option value="6">6 Bulan (Semester)</option>
                                <option value="12" selected>12 Bulan (1 Tahun)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar mr-1"></i> Tanggal Jatuh Tempo</label>
                            <select name="due_day" id="due_day" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500">
                                @for($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ $i == 10 ? 'selected' : '' }}>Tanggal {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Single Bill Options (shown by default) -->
                <div id="single_date_field">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar mr-1"></i> Bulan Jatuh Tempo</label>
                            <select name="single_month" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500">
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3" selected>Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Tanggal Jatuh Tempo <span class="text-red-500">*</span></label>
                            <input type="date" name="due_date" value="{{ old('due_date') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                </div>

                <!-- Bill Amount fields -->
                <div id="single_amount_details">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">💰 Jumlah Tagihan <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" min="0" step="1000"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                                placeholder="500000">
                            <p class="text-xs text-gray-500 mt-1">Masukkan jumlah dalam Rupiah</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">🏢 Setoran Yayasan</label>
                            <input type="number" name="yayasan_share_amount" id="yayasan_share_amount" value="{{ old('yayasan_share_amount') }}" min="0" step="1000"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                                placeholder="0">
                            <p class="text-xs text-gray-500 mt-1">Nominal hak Yayasan (opsional)</p>
                        </div>
                    </div>
                </div>

                <div id="monthly_details" style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">💰 Jumlah Per Bulan (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="monthly_amount" id="monthly_amount" value="{{ old('monthly_amount') }}" min="0" step="1000"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                                placeholder="500000">
                            <p class="text-xs text-gray-500 mt-1">Jumlah SPP setiap bulan</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">🏢 Setoran Yayasan Per Bulan</label>
                            <input type="number" name="monthly_yayasan_share_amount" id="monthly_yayasan_share_amount" value="{{ old('monthly_yayasan_share_amount') }}" min="0" step="1000"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                                placeholder="0">
                            <p class="text-xs text-gray-500 mt-1">Nominal hak Yayasan per bulan</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-edit mr-1"></i> Catatan (Opsional)</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500"
                        placeholder="Catatan tambahan untuk tagihan ini...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" 
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-700 text-white rounded-xl font-medium hover:from-green-700 hover:to-emerald-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Tagihan
            </button>
            <a href="{{ route('admin.bills.index') }}" 
                class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function toggleBillingType() {
    const billingType = document.querySelector('input[name="billing_type"]:checked').value;
    const isRecurring = billingType === 'monthly';
    
    const monthlyOptions = document.getElementById('monthly_options');
    const monthlyDetails = document.getElementById('monthly_details');
    const singleDateField = document.getElementById('single_date_field');
    const singleAmountDetails = document.getElementById('single_amount_details');
    
    if (monthlyOptions) monthlyOptions.style.display = isRecurring ? 'block' : 'none';
    if (monthlyDetails) monthlyDetails.style.display = isRecurring ? 'block' : 'none';
    if (singleDateField) singleDateField.style.display = isRecurring ? 'none' : 'block';
    if (singleAmountDetails) singleAmountDetails.style.display = isRecurring ? 'none' : 'block';
    
    // Toggle required attributes
    if (isRecurring) {
        document.querySelector('input[name="due_date"]')?.removeAttribute('required');
        document.querySelector('input[name="amount"]')?.removeAttribute('required');
        document.querySelector('input[name="monthly_amount"]')?.setAttribute('required', 'required');
    } else {
        document.querySelector('input[name="due_date"]')?.setAttribute('required', 'required');
        document.querySelector('input[name="amount"]')?.setAttribute('required', 'required');
        document.querySelector('input[name="monthly_amount"]')?.removeAttribute('required');
    }
    
    // Auto-fill default amount based on the selection
    updateAmounts();
}

function updateAmounts() {
    const billingType = document.querySelector('input[name="billing_type"]:checked').value;
    const isRecurring = billingType === 'monthly';
    const paymentTypeSelect = document.getElementById('payment_type_id');
    if (!paymentTypeSelect) return;
    
    const selectedOption = paymentTypeSelect.options[paymentTypeSelect.selectedIndex];
    if (!selectedOption) return;
    
    const defaultAmount = selectedOption.getAttribute('data-amount');
    const defaultYayasanShare = selectedOption.getAttribute('data-yayasan-share');
    
    if (defaultAmount) {
        if (!isRecurring) {
            const amountInput = document.querySelector('input[name="amount"]');
            const yayasanInput = document.querySelector('input[name="yayasan_share_amount"]');
            
            if (amountInput) amountInput.value = parseInt(defaultAmount);
            if (yayasanInput && defaultYayasanShare) yayasanInput.value = parseInt(defaultYayasanShare);
            else if (yayasanInput) yayasanInput.value = '';
        } else {
            const monthlyAmountInput = document.querySelector('input[name="monthly_amount"]');
            const monthlyYayasanInput = document.querySelector('input[name="monthly_yayasan_share_amount"]');
            
            if (monthlyAmountInput) monthlyAmountInput.value = parseInt(defaultAmount);
            if (monthlyYayasanInput && defaultYayasanShare) monthlyYayasanInput.value = parseInt(defaultYayasanShare);
            else if (monthlyYayasanInput) monthlyYayasanInput.value = '';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleBillingType();
});
</script>
@endsection
