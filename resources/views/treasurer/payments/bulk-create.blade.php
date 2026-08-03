@extends('layouts.treasurer')

@section('title', 'Pembayaran Massal')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Pembayaran Massal</h1>
                    <p class="text-gray-600 mt-1">Input pembayaran untuk banyak siswa sekaligus per kelas</p>
                </div>
            </div>
            <a href="{{ route('treasurer.payments.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300">
                Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl mb-6">
        <ul class="list-disc list-inside text-red-700">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl mb-6">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    <form id="bulkPaymentForm" method="POST" action="{{ route('treasurer.payments.bulk-store') }}">
        @csrf

        <!-- Step 1: Filter -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">📋 Filter Tagihan</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Ajaran</label>
                    <select name="academic_year_id" id="academic_year_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ ($activeAcademicYear && $activeAcademicYear->id == $year->id) ? 'selected' : '' }}>{{ $year->year }} {{ $year->is_active ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas</label>
                    <select name="classroom_id" id="classroom_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}">{{ $classroom->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Tagihan</label>
                    <select name="payment_type_id" id="payment_type_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach($paymentTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Bulan (Untuk SPP)</label>
                    <select name="month" id="month" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Semua Bulan --</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                    </select>
                </div>
            </div>

            <button type="button" onclick="loadBills()" class="px-6 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all font-medium">
                🔍 Tampilkan Tagihan
            </button>
        </div>

        <!-- Step 2: Select Bills -->
        <div id="billsSection" class="bg-white rounded-2xl shadow-lg p-6 mb-6" style="display: none;">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">✓ Pilih Tagihan</h2>
                <div class="flex gap-2">
                    <button type="button" onclick="selectAll()" class="px-3 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">
                        Pilih Semua
                    </button>
                    <button type="button" onclick="deselectAll()" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                        Batal
                    </button>
                </div>
            </div>

            <div id="billsTableContainer" class="overflow-x-auto mb-4">
                <!-- Bills will be loaded here -->
            </div>

            <p id="selectedInfo" class="text-sm font-semibold text-gray-700">0 tagihan dipilih - Rp 0</p>
        </div>

        <!-- Step 3: Payment Details -->
        <div id="paymentSection" class="bg-white rounded-2xl shadow-lg p-6 mb-6" style="display: none;">
            <h2 class="text-xl font-bold text-gray-900 mb-4">💳 Detail Pembayaran</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Metode Pembayaran</label>
                    <select name="payment_method" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Metode --</option>
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="card">Kartu</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Pembayaran</label>
                    <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                    <input type="text" name="notes" placeholder="Catatan pembayaran..." class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-700 text-white rounded-xl font-medium hover:from-emerald-700 hover:to-green-800 shadow-lg">
                    💰 Proses <span id="submitCount">0</span> Pembayaran
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let selectedBills = [];

function loadBills() {
    const academicYearId = document.getElementById('academic_year_id').value;
    const classroomId = document.getElementById('classroom_id').value;
    const paymentTypeId = document.getElementById('payment_type_id').value;
    const month = document.getElementById('month').value;

    if (!academicYearId || !classroomId || !paymentTypeId) {
        alert('Harap pilih semua filter!');
        return;
    }

    const billsSection = document.getElementById('billsSection');
    billsSection.style.display = 'block';
    document.getElementById('billsTableContainer').innerHTML = '<p class="text-center py-4">Memuat...</p>';

    fetch(`{{ route('treasurer.payments.fetch-bills') }}?academic_year_id=${academicYearId}&classroom_id=${classroomId}&payment_type_id=${paymentTypeId}&month=${month}`)
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (!data.success || data.bills.length === 0) {
                document.getElementById('billsTableContainer').innerHTML = '<p class="text-center py-4 text-gray-500">Tidak ada tagihan belum lunas.</p>';
                return;
            }

            let html = `
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                            <th class="px-4 py-3 text-left">Siswa</th>
                            <th class="px-4 py-3 text-center">Bulan</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>`;

            data.bills.forEach(bill => {
                const amount = parseFloat(bill.amount) + parseFloat(bill.late_fee || 0);
                html += `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" class="bill-cb" value="${bill.id}" data-amount="${amount}" onchange="updateSelection()">
                        </td>
                        <td class="px-4 py-3">${bill.student.full_name}</td>
                        <td class="px-4 py-3 text-center">${bill.month ? getMonthName(bill.month) : '-'}</td>
                        <td class="px-4 py-3 text-right font-semibold">Rp ${amount.toLocaleString('id-ID')}</td>
                    </tr>`;
            });

            html += '</tbody></table>';
            document.getElementById('billsTableContainer').innerHTML = html;
            document.getElementById('paymentSection').style.display = 'block';
        })
        .catch(() => {
            showFlashMessage('Gagal memuat data tagihan. Silakan coba lagi.', 'error');
        });
}

function getMonthName(m) {
    const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return months[m] || '-';
}

function toggleAll(cb) {
    document.querySelectorAll('.bill-cb').forEach(c => c.checked = cb.checked);
    updateSelection();
}

function selectAll() {
    document.querySelectorAll('.bill-cb').forEach(c => c.checked = true);
    document.getElementById('selectAll').checked = true;
    updateSelection();
}

function deselectAll() {
    document.querySelectorAll('.bill-cb').forEach(c => c.checked = false);
    document.getElementById('selectAll').checked = false;
    updateSelection();
}

function updateSelection() {
    const checked = document.querySelectorAll('.bill-cb:checked');
    selectedBills = [];
    let total = 0;
    
    checked.forEach(cb => {
        selectedBills.push(cb.value);
        total += parseFloat(cb.dataset.amount);
    });

    document.getElementById('selectedInfo').textContent = `${checked.length} tagihan dipilih - Rp ${total.toLocaleString('id-ID')}`;
    document.getElementById('submitCount').textContent = checked.length;
}

document.getElementById('bulkPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (selectedBills.length === 0) {
        alert('Pilih minimal 1 tagihan!');
        return;
    }

    selectedBills.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'bill_ids[]';
        input.value = id;
        this.appendChild(input);
    });

    if (confirm(`Proses ${selectedBills.length} pembayaran?`)) {
        this.submit();
    }
});
</script>
@endsection
