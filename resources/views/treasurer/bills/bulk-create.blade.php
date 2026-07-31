@extends('layouts.treasurer')

@section('title', 'Buat Tagihan Massal')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-4xl">
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Buat Tagihan Massal</h1>
                <p class="text-gray-600 mt-1">Buat tagihan untuk banyak siswa sekaligus</p>
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

    <form action="{{ route('treasurer.bills.bulk-store') }}" method="POST">
        @csrf

        <!-- Section 1: Target Siswa -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 text-white font-bold text-sm">1</div>
                <h2 class="text-xl font-bold text-gray-900">Target Siswa</h2>
            </div>

            <div class="space-y-5">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-blue-800">
                            <strong>Catatan:</strong> Hanya siswa dengan status <span class="font-bold">AKTIF</span> yang akan diproses. 
                            Siswa yang sudah lulus, pindah, atau keluar tidak akan dibuatkan tagihan.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">🎯 Filter Siswa</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:bg-emerald-50 hover:border-emerald-500 transition-colors">
                            <input type="radio" name="filter_by" value="all" {{ old('filter_by', 'all') == 'all' ? 'checked' : '' }} required
                                class="w-5 h-5 text-emerald-600 focus:ring-emerald-500"
                                onchange="toggleFilterOptions()">
                            <span class="ml-3">
                                <span class="font-medium text-gray-900">👥 Semua Siswa Aktif</span>
                                <p class="text-sm text-gray-500">Buat tagihan untuk seluruh siswa di sekolah</p>
                            </span>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:bg-emerald-50 hover:border-emerald-500 transition-colors">
                            <input type="radio" name="filter_by" value="classroom" {{ old('filter_by') == 'classroom' ? 'checked' : '' }} required
                                class="w-5 h-5 text-emerald-600 focus:ring-emerald-500"
                                onchange="toggleFilterOptions()">
                            <span class="ml-3">
                                <span class="font-medium text-gray-900">🏛️ Per Kelas</span>
                                <p class="text-sm text-gray-500">Pilih satu kelas tertentu</p>
                            </span>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:bg-emerald-50 hover:border-emerald-500 transition-colors">
                            <input type="radio" name="filter_by" value="grade" {{ old('filter_by') == 'grade' ? 'checked' : '' }} required
                                class="w-5 h-5 text-emerald-600 focus:ring-emerald-500"
                                onchange="toggleFilterOptions()">
                            <span class="ml-3">
                                <span class="font-medium text-gray-900">📊 Per Tingkat</span>
                                <p class="text-sm text-gray-500">Kelas 10, 11, atau 12</p>
                            </span>
                        </label>
                    </div>
                </div>

                <div id="classroom_filter" style="display: none;">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">🏛️ Pilih Kelas</label>
                    <select name="classroom_id" id="classroom_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        onchange="updateStudentCount()">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" data-academic-year="{{ $classroom->academic_year_id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                            {{ $classroom->class_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="grade_filter" style="display: none;">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">📊 Pilih Tingkat</label>
                    <select name="grade_level" id="grade_level"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        onchange="updateStudentCount()">
                        <option value="">-- Pilih Tingkat --</option>
                        <optgroup label="SMP/MTs">
                            <option value="7" {{ old('grade_level') == '7' ? 'selected' : '' }}>Kelas VII (7)</option>
                            <option value="8" {{ old('grade_level') == '8' ? 'selected' : '' }}>Kelas VIII (8)</option>
                            <option value="9" {{ old('grade_level') == '9' ? 'selected' : '' }}>Kelas IX (9)</option>
                        </optgroup>
                        <optgroup label="SMA/SMK/MA">
                            <option value="10" {{ old('grade_level') == '10' ? 'selected' : '' }}>Kelas X (10)</option>
                            <option value="11" {{ old('grade_level') == '11' ? 'selected' : '' }}>Kelas XI (11)</option>
                            <option value="12" {{ old('grade_level') == '12' ? 'selected' : '' }}>Kelas XII (12)</option>
                        </optgroup>
                    </select>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-xl">
                    <p class="text-sm text-blue-800">
                        <span class="font-bold">💡 Estimasi:</span> 
                        <span id="student_count">Pilih filter untuk melihat target siswa</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Section 2: Detail Tagihan -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 text-white font-bold text-sm">2</div>
                <h2 class="text-xl font-bold text-gray-900">Detail Tagihan</h2>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Tahun Pelajaran</label>
                    <select name="academic_year_id" id="academic_year_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500" onchange="updateClassroomOptions(); updateStudentCount();">
                        <option value="">-- Pilih Tahun Pelajaran --</option>
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id', $activeAcademicYear->id ?? '') == $year->id ? 'selected' : '' }}>
                            TP. {{ $year->year }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">📋 Jenis Tagihan</label>
                    <select name="payment_type_id" id="payment_type_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500" onchange="updateAmounts()">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach($paymentTypes as $type)
                        <option value="{{ $type->id }}" 
                            data-amount="{{ $type->amount }}"
                            {{ old('payment_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->type_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">⏱️ Sifat Tagihan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center p-3 border border-gray-300 rounded-xl cursor-pointer hover:bg-emerald-50 hover:border-emerald-500 transition-all">
                            <input type="radio" name="billing_type" value="single" checked onchange="toggleBillingType()"
                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-3 font-medium text-gray-900">1 Kali Saja</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-300 rounded-xl cursor-pointer hover:bg-emerald-50 hover:border-emerald-500 transition-all">
                            <input type="radio" name="billing_type" value="monthly" onchange="toggleBillingType()"
                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-3 font-medium text-gray-900">Bulanan (Berulang)</span>
                        </label>
                    </div>
                </div>

                <div id="monthly_options" style="display: none;">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-xl mb-4">
                        <p class="text-sm text-blue-800">
                            <span class="font-bold">💡 SPP Bulanan:</span> 
                            Sistem akan membuat tagihan untuk beberapa bulan sekaligus dengan jatuh tempo tanggal yang sama setiap bulannya.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Bulan Mulai</label>
                            <select name="start_month" id="start_month" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">🔢 Jumlah Bulan</label>
                            <select name="generate_months" id="generate_months" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                <option value="1">1 Bulan</option>
                                <option value="2">2 Bulan</option>
                                <option value="3">3 Bulan</option>
                                <option value="6">6 Bulan (Semester)</option>
                                <option value="12" selected>12 Bulan (1 Tahun)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📆 Tanggal Jatuh Tempo</label>
                            <select name="due_day" id="due_day" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                @for($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ $i == 10 ? 'selected' : '' }}>Tanggal {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-600">
                        <span class="font-semibold">Contoh SPP:</span> Bulan Mulai: Juli, Jumlah Bulan: 12, Tanggal: 10 
                        → Akan membuat tagihan dengan jatuh tempo: 10 Jul 2025, 10 Agu 2025, ... , 10 Jun 2026 (12 bulan)
                    </p>
                </div>

                <div id="single_date_field">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">📝 Deskripsi</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                            placeholder="Contoh: Biaya Pendaftaran 2026"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">💰 Jumlah Per Siswa (Rp)</label>
                            <input type="number" name="amount" value="{{ old('amount') }}" required min="0" step="1000"
                                placeholder="500000"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            <p class="mt-1 text-xs text-gray-500">Jumlah yang sama untuk semua siswa</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Bulan Jatuh Tempo</label>
                            <select name="single_month" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📆 Tanggal Jatuh Tempo</label>
                            <select name="single_day" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                @for($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ $i == 15 ? 'selected' : '' }}>Tanggal {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div id="monthly_details" style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">💰 Jumlah Per Bulan (Rp)</label>
                            <input type="number" name="monthly_amount" value="{{ old('monthly_amount') }}" min="0" step="1000"
                                placeholder="500000"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            <p class="mt-1 text-xs text-gray-500">Jumlah SPP setiap bulan</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📝 Prefix Deskripsi</label>
                            <input type="text" name="description_prefix" value="{{ old('description_prefix', 'SPP') }}"
                                placeholder="SPP"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            <p class="mt-1 text-xs text-gray-500">Contoh: "SPP Januari 2026", "SPP Februari 2026"</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">📌 Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" 
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-700 text-white rounded-xl font-medium hover:from-emerald-700 hover:to-green-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Buat Tagihan Massal
            </button>
            <a href="{{ route('treasurer.bills.index') }}" 
                class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function updateClassroomOptions() {
    const academicYearId = document.getElementById('academic_year_id')?.value;
    const classroomSelect = document.getElementById('classroom_id');
    if (!classroomSelect) return;

    const options = classroomSelect.querySelectorAll('option');
    let hasValidSelection = false;
    options.forEach(option => {
        if (!option.value) return; // skip default placeholder
        const optAY = option.getAttribute('data-academic-year');

        let matchAY = !academicYearId || optAY == academicYearId;

        if (matchAY) {
            option.style.display = '';
            option.disabled = false;
            if (classroomSelect.value == option.value) {
                hasValidSelection = true;
            }
        } else {
            option.style.display = 'none';
            option.disabled = true;
        }
    });

    if (!hasValidSelection) {
        classroomSelect.value = '';
    }
}

function toggleFilterOptions() {
    const filterBy = document.querySelector('input[name="filter_by"]:checked').value;
    
    document.getElementById('classroom_filter').style.display = filterBy === 'classroom' ? 'block' : 'none';
    document.getElementById('grade_filter').style.display = filterBy === 'grade' ? 'block' : 'none';
    
    updateStudentCount();
}

function toggleBillingType() {
    const billingType = document.querySelector('input[name="billing_type"]:checked').value;
    const isRecurring = billingType === 'monthly';
    
    const monthlyOptions = document.getElementById('monthly_options');
    const monthlyDetails = document.getElementById('monthly_details');
    const singleDateField = document.getElementById('single_date_field');
    
    if (monthlyOptions) monthlyOptions.style.display = isRecurring ? 'block' : 'none';
    if (monthlyDetails) monthlyDetails.style.display = isRecurring ? 'block' : 'none';
    if (singleDateField) singleDateField.style.display = isRecurring ? 'none' : 'block';
    
    // Toggle required attributes
    if (isRecurring) {
        document.querySelector('input[name="amount"]')?.removeAttribute('required');
        document.querySelector('input[name="monthly_amount"]')?.setAttribute('required', 'required');
    } else {
        document.querySelector('input[name="amount"]')?.setAttribute('required', 'required');
        document.querySelector('input[name="monthly_amount"]')?.removeAttribute('required');
    }
    
    // Update default values based on selected payment type
    updateAmounts();
    updateStudentCount();
}

function updateAmounts() {
    const billingType = document.querySelector('input[name="billing_type"]:checked').value;
    const isRecurring = billingType === 'monthly';
    const paymentTypeSelect = document.getElementById('payment_type_id');
    if (!paymentTypeSelect) return;
    
    const selectedOption = paymentTypeSelect.options[paymentTypeSelect.selectedIndex];
    if (!selectedOption) return;
    
    const defaultAmount = selectedOption.getAttribute('data-amount');
    if (defaultAmount) {
        if (!isRecurring) {
            const amountInput = document.querySelector('input[name="amount"]');
            if (amountInput) amountInput.value = parseInt(defaultAmount);
        } else {
            const monthlyAmountInput = document.querySelector('input[name="monthly_amount"]');
            if (monthlyAmountInput) monthlyAmountInput.value = parseInt(defaultAmount);
        }
    }
}

function updateStudentCount() {
    const filterBy = document.querySelector('input[name="filter_by"]:checked')?.value;
    
    let message = '';
    if (filterBy === 'all') {
        message = 'Tagihan akan dibuat untuk SEMUA siswa di sekolah Anda';
    } else if (filterBy === 'classroom') {
        const classroomId = document.getElementById('classroom_id').value;
        message = classroomId ? 'Tagihan akan dibuat untuk siswa di kelas yang dipilih' : 'Pilih kelas terlebih dahulu';
    } else if (filterBy === 'grade') {
        const gradeLevel = document.getElementById('grade_level').value;
        message = gradeLevel ? `Tagihan akan dibuat untuk semua siswa tingkat ${gradeLevel}` : 'Pilih tingkat terlebih dahulu';
    }
    
    // Add monthly info if applicable
    const billingType = document.querySelector('input[name="billing_type"]:checked')?.value;
    if (billingType === 'monthly') {
        const generateMonths = document.getElementById('generate_months')?.value || 12;
        message += ` untuk ${generateMonths} bulan`;
    }
    
    document.getElementById('student_count').textContent = message;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateClassroomOptions();
    toggleFilterOptions();
    toggleBillingType();
});
</script>
@endsection
