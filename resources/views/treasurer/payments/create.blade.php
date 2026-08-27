@extends('layouts.treasurer')

@section('title', 'Catat Pembayaran')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-3xl">
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Catat Pembayaran</h1>
                <p class="text-gray-600 mt-1">Input pembayaran dari siswa</p>
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

    <form action="{{ route('treasurer.payments.store') }}" method="POST">
        @csrf

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="space-y-5">
                <!-- Siswa -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">👤 Siswa</label>
                    <select name="student_id" id="student_id" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        onchange="loadStudentBills()">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ (old('student_id') ?? $selectedStudentId ?? '') == $student->id ? 'selected' : '' }}>
                            {{ $student->full_name }} ({{ $student->nisn }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tagihan (Optional) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">📋 Tagihan (Opsional)</label>
                    <select name="bill_id" id="bill_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        onchange="fillBillAmount()">
                        <option value="">-- Pilih Tagihan (Kosongkan jika bayar tanpa tagihan) --</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Pilih tagihan untuk auto-fill jumlah dan update status tagihan</p>
                </div>

                <!-- Jumlah Bayar -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">💰 Jumlah Bayar (Rp)</label>
                    <input type="number" name="amount_paid" id="amount_paid" value="{{ old('amount_paid') }}" required min="0" step="1000"
                        placeholder="500000"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Payment Method & Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">💳 Metode Pembayaran</label>
                        <select name="payment_method" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Metode --</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>💵 Tunai</option>
                            <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>🏦 Transfer Bank</option>
                            <option value="qris" {{ old('payment_method') == 'qris' ? 'selected' : '' }}>📱 QRIS</option>
                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>💳 Kartu Kredit</option>
                            <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>📝 Cek</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Tanggal Bayar</label>
                        <input type="datetime-local" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Transaction Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">🔖 ID Transaksi</label>
                        <input type="text" name="transaction_id" value="{{ old('transaction_id') }}"
                            placeholder="TRX-2026-001"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <p class="mt-1 text-xs text-gray-500">ID unik dari sistem pembayaran</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">📄 No. Referensi</label>
                        <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                            placeholder="REF-001"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <p class="mt-1 text-xs text-gray-500">No. referensi bank atau struk</p>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">📝 Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="Catatan pembayaran...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" 
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-700 text-white rounded-xl font-medium hover:from-emerald-700 hover:to-green-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Pembayaran
            </button>
            <a href="{{ route('treasurer.payments.index') }}" 
                class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
@php
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
@endphp

// Master list of all bills
const allBills = [
    @foreach($bills as $bill)
    {
        id: "{{ $bill->id }}",
        student_id: "{{ $bill->student_id }}",
        amount: "{{ $bill->amount }}",
        paid_amount: "{{ $bill->paid_amount }}",
        status: "{{ $bill->status }}",
        type_name: "{{ $bill->paymentType->type_name ?? 'Tagihan' }}",
        is_recurring: {{ ($bill->paymentType->is_recurring ?? false) ? 'true' : 'false' }},
        month_name: "{{ (isset($bill->month) && $bill->month > 0) ? ($monthNames[$bill->month] ?? $bill->month) : '' }}",
        year: "{{ $bill->year ?? '' }}"
    },
    @endforeach
];

// Auto-load bills if student is preselected
document.addEventListener('DOMContentLoaded', function() {
    const studentId = document.getElementById('student_id').value;
    if (studentId) {
        const preselectedBillId = "{{ old('bill_id') ?? $selectedBillId ?? '' }}";
        loadStudentBills(preselectedBillId);
    }    
    // Auto-download receipt after successful payment
    @if(session('payment_id'))
        const paymentId = {{ session('payment_id') }};
        if (confirm('Pembayaran berhasil! Download kwitansi sekarang?')) {
            window.location.href = '/bendahara/payments/' + paymentId + '/receipt';
        }
    @endif
});

function loadStudentBills(selectedBillId = '') {
    const studentId = document.getElementById('student_id').value;
    const billSelect = document.getElementById('bill_id');
    
    // Clear existing dynamic options (keep first option)
    billSelect.innerHTML = '<option value="">-- Pilih Tagihan (Kosongkan jika bayar tanpa tagihan) --</option>';
    
    // Filter and add matching bills
    allBills.forEach(bill => {
        if (bill.student_id === studentId) {
            const option = document.createElement('option');
            option.value = bill.id;
            option.setAttribute('data-amount', bill.amount);
            option.setAttribute('data-student', bill.student_id);
            if (selectedBillId && String(bill.id) === String(selectedBillId)) {
                option.selected = true;
            }
            
            // Format amount
            const formattedAmount = new Intl.NumberFormat('id-ID').format(bill.amount);
            
            // Determine recurrence prefix
            const prefix = bill.is_recurring ? '[Bulanan]' : '[1 Kali]';
            
            // Determine month text
            const monthText = bill.month_name ? ` (${bill.month_name} ${bill.year})` : '';
            
            // Determine installment text
            let remainingText = '';
            if (bill.status === 'cicilan') {
                const remaining = bill.amount - bill.paid_amount;
                const formattedRemaining = new Intl.NumberFormat('id-ID').format(remaining);
                remainingText = ` (Sisa: Rp ${formattedRemaining})`;
            }
            
            option.textContent = `${prefix} ${bill.type_name}${monthText} - Rp ${formattedAmount}${remainingText}`;
            billSelect.appendChild(option);
        }
    });
    
    if (selectedBillId) {
        billSelect.value = selectedBillId;
        fillBillAmount();
    } else {
        billSelect.value = '';
        document.getElementById('amount_paid').value = '';
    }
}

function fillBillAmount() {
    const billSelect = document.getElementById('bill_id');
    const selectedOption = billSelect.options[billSelect.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        const amount = selectedOption.getAttribute('data-amount');
        document.getElementById('amount_paid').value = amount;
    }
}

// Anti Double-Submit Guard
document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.querySelector('form');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            if (this.dataset.submitting === 'true') {
                e.preventDefault();
                return false;
            }
            this.dataset.submitting = 'true';
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
            }
        });
    }
});
</script>
@endsection
