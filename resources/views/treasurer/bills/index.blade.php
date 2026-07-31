@extends('layouts.treasurer')

@section('title', 'Tagihan Siswa')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tagihan Siswa</h1>
                    <p class="text-gray-600 mt-1">Kelola tagihan pembayaran siswa</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" id="batchPayBtn" onclick="openBatchPayModal()" 
                    class="hidden flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl font-medium hover:from-blue-700 hover:to-indigo-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span id="batchPayText">Bayar Terpilih (0)</span>
                </button>
                <button type="button" onclick="openBulkUpdateModal(false)" 
                    class="flex items-center justify-center gap-2 px-5 py-3 bg-indigo-50 border-2 border-indigo-500 text-indigo-700 rounded-xl font-medium hover:bg-indigo-600 hover:text-white shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Ubah Nominal Massal
                </button>
                <button type="button" onclick="openBulkDeleteModal(false)" 
                    class="flex items-center justify-center gap-2 px-5 py-3 bg-rose-50 border-2 border-rose-500 text-rose-700 rounded-xl font-medium hover:bg-rose-600 hover:text-white shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus Massal
                </button>
                <a href="{{ route('treasurer.bills.bulk-create') }}" 
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-xl font-medium hover:from-emerald-700 hover:to-teal-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Tagihan Massal
                </a>
                <a href="{{ route('treasurer.bills.create') }}" 
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-700 text-white rounded-xl font-medium hover:from-emerald-700 hover:to-green-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Tagihan
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <form method="GET" action="{{ route('treasurer.bills.index') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Ajaran</label>
                <select name="academic_year_id" onchange="document.getElementById('filterForm').submit()" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                        {{ $year->year }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Tagihan</label>
                <select name="payment_type_id" onchange="document.getElementById('filterForm').submit()" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">Semua Jenis</option>
                    @foreach($paymentTypes as $type)
                    <option value="{{ $type->id }}" {{ $paymentTypeId == $type->id ? 'selected' : '' }}>
                        {{ ($type->is_recurring ?? false) ? '[Bulanan] ' : '[1 Kali] ' }}{{ $type->type_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas</label>
                <select name="classroom_id" onchange="document.getElementById('filterForm').submit()" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">Semua Kelas</option>
                    @forelse($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" {{ $classroomId == $classroom->id ? 'selected' : '' }}>
                        {{ $classroom->class_name }}
                    </option>
                    @empty
                    <option value="" disabled>-- Belum ada kelas untuk tahun ini --</option>
                    @endforelse
                </select>
                @if($classrooms->isEmpty())
                <p class="text-xs text-amber-600 mt-1">⚠️ Kelas untuk tahun ajaran ini belum dibuat. Silakan buat kelas terlebih dahulu di menu Admin.</p>
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Cari Siswa</label>
                <input type="text" name="search" value="{{ $search }}" 
                    placeholder="Nama siswa..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all">
                    Filter
                </button>
                <a href="{{ route('treasurer.bills.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-all">
                    Reset
                </a>
                <a href="{{ route('treasurer.bills.export', request()->only(['academic_year_id', 'payment_type_id', 'classroom_id', 'search'])) }}" 
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Bills Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Jul</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Agu</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Sep</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Okt</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Nov</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Des</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Jan</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Feb</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Mar</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Apr</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Mei</th>
                        <th class="px-0 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Jun</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Tunggakan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($groupedBills as $index => $group)
                    <tr class="hover:bg-gray-50 transition-colors">
                        @if($group['is_first_row'])
                        <td class="px-3 py-2 align-middle" rowspan="{{ $group['rowspan'] }}">
                            <span class="flex items-center justify-center w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 text-white text-xs font-bold">
                                {{ $loop->iteration }}
                            </span>
                        </td>
                        <td class="px-3 py-2 align-middle" rowspan="{{ $group['rowspan'] }}">
                            <div>
                                <p class="font-semibold text-gray-900 text-xs">{{ $group['student']->full_name }}</p>
                                <p class="text-xs text-gray-500">{{ $group['student']->nisn }}</p>
                            </div>
                        </td>
                        @endif
                        <td class="px-3 py-2 align-middle">
                            <span class="text-xs font-medium text-gray-700">{{ $group['payment_type']->type_name }} ({{ $group['academic_year']->year }})</span>
                        </td>
                        @if($group['payment_type']->is_recurring ?? true)
                            @php
                                $months = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
                            @endphp
                            @foreach($months as $month)
                            <td class="px-0 py-2 align-middle">
                                @if($group['monthly_data'][$month])
                                    @php
                                        $bill = $group['monthly_data'][$month];
                                        $isPaid = $bill->status == 'lunas';
                                        $isPartial = $bill->status == 'cicilan';
                                        
                                        // Calculate if overdue
                                        $dueDate = \Carbon\Carbon::create($bill->year, $bill->month, 10);
                                        $isOverdue = !$isPaid && \Carbon\Carbon::now()->isAfter($dueDate);
                                        
                                        // Determine color
                                        if ($isPaid) {
                                            $color = 'bg-emerald-500 text-white'; // Paid = Green
                                            $statusText = 'Lunas';
                                        } elseif ($isOverdue) {
                                            $color = 'bg-red-500 text-white'; // Overdue = Red
                                            $statusText = 'Lewat Jatuh Tempo';
                                        } else {
                                            $color = 'bg-yellow-500 text-white'; // Not due yet = Yellow
                                            $statusText = 'Belum Jatuh Tempo';
                                        }
                                    @endphp
                                    <div class="flex justify-center">
                                        @if(!$isPaid)
                                        <input type="checkbox" class="bill-checkbox hidden" 
                                               data-bill-id="{{ $bill->id }}"
                                               data-student-id="{{ $group['student']->id }}"
                                               data-student-name="{{ $group['student']->full_name }}"
                                               data-amount="{{ $bill->amount }}"
                                               data-late-fee="{{ $bill->late_fee }}"
                                               data-month="{{ $bill->month }}"
                                               data-year="{{ $bill->year }}"
                                               data-payment-type="{{ $group['payment_type']->type_name }}">
                                        @endif
                                        <div class="w-6 h-6 rounded flex items-center justify-center {{ $color }} cursor-pointer hover:scale-110 transition-transform bill-box {{ !$isPaid ? 'can-pay' : '' }}" 
                                             title="{{ $group['payment_type']->type_name }} - Rp {{ number_format($bill->amount, 0, ',', '.') }}{{ $bill->late_fee > 0 ? ' + Denda Rp ' . number_format($bill->late_fee, 0, ',', '.') : '' }} - {{ $statusText }}"
                                             data-bill-id="{{ $bill->id }}"
                                             data-student-id="{{ $group['student']->id }}"
                                             data-student-name="{{ $group['student']->full_name }}"
                                             data-amount="{{ $bill->amount }}"
                                             data-late-fee="{{ $bill->late_fee }}"
                                             data-month="{{ $bill->month }}"
                                             data-year="{{ $bill->year }}"
                                             data-payment-type="{{ $group['payment_type']->type_name }}"
                                             data-paid="{{ $isPaid ? '1' : '0' }}"
                                             onclick="if(!event.shiftKey) openQuickPayModal(this)">
                                            @if($isPaid)
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            @elseif($isOverdue)
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                            @else
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H9a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                            </svg>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <span class="text-gray-300 text-xs">-</span>
                                    </div>
                                @endif
                            </td>
                            @endforeach
                        @else
                            <td colspan="12" class="px-3 py-2 align-middle">
                                @php
                                    $fb = $group['first_bill'] ?? null;
                                    $isPaid = $fb ? $fb->status == 'lunas' : false;
                                    $isPartial = $fb ? $fb->status == 'cicilan' : false;
                                    
                                    // Calculate if overdue
                                    $isOverdue = false;
                                    if ($fb && !$isPaid && $fb->due_date) {
                                        $isOverdue = \Carbon\Carbon::now()->isAfter(\Carbon\Carbon::parse($fb->due_date));
                                    }
                                    
                                    // Determine color
                                    if ($isPaid) {
                                        $color = 'bg-emerald-500 text-white'; // Paid = Green
                                        $statusText = 'Lunas';
                                    } elseif ($isOverdue) {
                                        $color = 'bg-red-500 text-white'; // Overdue = Red
                                        $statusText = 'Lewat Jatuh Tempo';
                                    } else {
                                        $color = 'bg-yellow-500 text-white'; // Not due yet = Yellow
                                        $statusText = 'Belum Jatuh Tempo';
                                    }
                                @endphp
                                <div class="flex items-center justify-between px-3 py-1.5 bg-gray-50/80 rounded-xl border border-gray-100/60">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $isPaid ? 'bg-emerald-100 text-emerald-800' : ($isOverdue ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $statusText }}
                                        </span>
                                        @if($fb && $fb->due_date)
                                        <span class="text-[10px] text-gray-500 font-medium">
                                            Jatuh Tempo: {{ \Carbon\Carbon::parse($fb->due_date)->translatedFormat('d M Y') }}
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-bold text-gray-900">
                                            Rp {{ number_format($group['total_amount'], 0, ',', '.') }}
                                        </span>
                                        
                                        @if($fb && !$isPaid)
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" class="bill-checkbox hidden" 
                                                   data-bill-id="{{ $fb->id }}"
                                                   data-student-id="{{ $group['student']->id }}"
                                                   data-student-name="{{ $group['student']->full_name }}"
                                                   data-amount="{{ $fb->amount }}"
                                                   data-late-fee="{{ $fb->late_fee }}"
                                                   data-month=""
                                                   data-year="{{ $fb->year }}"
                                                   data-payment-type="{{ $group['payment_type']->type_name }}">
                                            
                                            <div class="w-6 h-6 rounded flex items-center justify-center {{ $color }} cursor-pointer hover:scale-110 transition-transform bill-box can-pay" 
                                                 title="{{ $group['payment_type']->type_name }} - Rp {{ number_format($fb->amount, 0, ',', '.') }}{{ $fb->late_fee > 0 ? ' + Denda Rp ' . number_format($fb->late_fee, 0, ',', '.') : '' }} - {{ $statusText }}"
                                                 data-bill-id="{{ $fb->id }}"
                                                 data-student-id="{{ $group['student']->id }}"
                                                 data-student-name="{{ $group['student']->full_name }}"
                                                 data-amount="{{ $fb->amount }}"
                                                 data-late-fee="{{ $fb->late_fee }}"
                                                 data-month=""
                                                 data-year="{{ $fb->year }}"
                                                 data-payment-type="{{ $group['payment_type']->type_name }}"
                                                 data-paid="0"
                                                 onclick="if(!event.shiftKey) openQuickPayModal(this)">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H9a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                        @else
                                        <div class="w-6 h-6 rounded flex items-center justify-center bg-emerald-500 text-white shadow-sm border border-white">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        @endif
                        <td class="px-3 py-2 align-middle">
                            <div class="text-xs">
                                <p class="text-gray-500">{{ $group['bill_count'] }} × Rp {{ number_format($group['total_amount'] / $group['bill_count'], 0, ',', '.') }}</p>
                                @if($group['outstanding'] > 0)
                                <p class="text-red-600 font-semibold">Rp {{ number_format($group['outstanding'], 0, ',', '.') }}</p>
                                @else
                                <p class="text-emerald-600 font-semibold">✓ Lunas</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('treasurer.payments.create', ['student_id' => $group['student']->id]) }}" 
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-white hover:scale-110 transform transition-all shadow-md hover:shadow-lg"
                                    title="Bayar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </a>
                                @if(isset($group['first_bill']) && $group['first_bill'] && $group['first_bill']->paid_amount == 0)
                                <a href="{{ route('treasurer.bills.edit', $group['first_bill']->id) }}" 
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 text-white hover:scale-110 transform transition-all shadow-md hover:shadow-lg"
                                    title="Edit Tagihan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('treasurer.bills.destroy', $group['first_bill']->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tagihan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                        class="w-7 h-7 flex items-center justify-center rounded-lg bg-gradient-to-br from-rose-500 to-rose-600 text-white hover:scale-110 transform transition-all shadow-md hover:shadow-lg"
                                        title="Hapus Tagihan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-lg font-medium">Tidak ada data tagihan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary/Rekapitulasi Section -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Siswa -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Total Siswa</p>
                    <p class="text-3xl font-bold">{{ $totalStudents }}</p>
                    <p class="text-blue-100 text-xs mt-1">Siswa dengan tagihan</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-xl p-3">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Tagihan -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Total Tagihan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalBills, 0, ',', '.') }}</p>
                    <p class="text-purple-100 text-xs mt-1">Semua tagihan</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-xl p-3">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Pembayaran -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm font-medium mb-1">Total Pembayaran</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
                    <p class="text-emerald-100 text-xs mt-1">Sudah dibayar</p>
                </div>
                <div class="bg-emerald-400 bg-opacity-30 rounded-xl p-3">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Tunggakan -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium mb-1">Total Tunggakan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</p>
                    <p class="text-red-100 text-xs mt-1">Belum dibayar</p>
                </div>
                <div class="bg-red-400 bg-opacity-30 rounded-xl p-3">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Summary Details -->
    <div class="mt-6 bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Ringkasan Rekapitulasi
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="border-l-4 border-blue-500 bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Tingkat Pembayaran</p>
                @php
                    $paymentRate = $totalBills > 0 ? ($totalPaid / $totalBills) * 100 : 0;
                @endphp
                <div class="flex items-end gap-2">
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($paymentRate, 1) }}%</p>
                    <p class="text-sm text-gray-500 mb-1">dari total tagihan</p>
                </div>
                <div class="mt-2 bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $paymentRate }}%"></div>
                </div>
            </div>
            <div class="border-l-4 border-emerald-500 bg-emerald-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Rata-rata per Siswa</p>
                <p class="text-2xl font-bold text-emerald-600">
                    Rp {{ number_format($totalStudents > 0 ? $totalPaid / $totalStudents : 0, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Sudah dibayar per siswa</p>
            </div>
            <div class="border-l-4 border-red-500 bg-red-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Rata-rata Tunggakan</p>
                <p class="text-2xl font-bold text-red-600">
                    Rp {{ number_format($totalStudents > 0 ? $totalOutstanding / $totalStudents : 0, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Tunggakan per siswa</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Pay Modal -->
<div id="quickPayModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-2xl bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">💳 Quick Pay</h3>
            <button onclick="closeQuickPayModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="quickPayForm" method="POST" action="{{ route('treasurer.payments.store') }}">
            @csrf
            <input type="hidden" name="bill_id" id="quick_bill_id">
            <input type="hidden" name="student_id" id="quick_student_id">
            <input type="hidden" name="payment_date" id="quick_payment_date" value="{{ now()->format('Y-m-d H:i:s') }}">
            
            <div class="space-y-4">
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-sm text-gray-600">Siswa</p>
                    <p class="font-semibold text-gray-900" id="quick_student_name"></p>
                </div>
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-sm text-gray-600">Tagihan</p>
                    <p class="font-semibold text-gray-900" id="quick_bill_detail"></p>
                </div>
                <div id="quick_late_fee_display" class="bg-orange-50 rounded-xl p-4 hidden">
                    <p class="text-sm text-gray-600">Denda Keterlambatan</p>
                    <p class="font-semibold text-orange-600" id="quick_late_fee_amount"></p>
                    <p class="text-xs text-gray-500 mt-1">Sudah termasuk dalam jumlah bayar</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Bayar (Rp)</label>
                    <input type="number" name="amount_paid" id="quick_amount" required min="0" step="1000"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1" id="quick_amount_note"></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Metode Pembayaran</label>
                    <select name="payment_method" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        <option value="cash">💵 Tunai</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                        <option value="qris">📱 QRIS</option>
                        <option value="card">💳 Kartu Kredit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">No. Referensi (Opsional)</label>
                    <input type="text" name="reference_number" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="mt-6 flex gap-3">
                <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-all">
                    Proses Pembayaran
                </button>
                <button type="button" onclick="closeQuickPayModal()" class="px-4 py-3 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300 transition-all">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Batch Pay Modal -->
<div id="batchPayModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-2xl bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">💳 Pembayaran Batch</h3>
            <button onclick="closeBatchPayModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="batchPayForm" method="POST" action="{{ route('treasurer.payments.batch-store') }}">
            @csrf
            <input type="hidden" name="bill_ids" id="batch_bill_ids">
            <input type="hidden" name="student_id" id="batch_student_id">
            <input type="hidden" name="payment_date" id="batch_payment_date" value="{{ now()->format('Y-m-d H:i:s') }}">
            
            <div class="space-y-4">
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-sm text-gray-600">Siswa</p>
                    <p class="font-semibold text-gray-900" id="batch_student_name"></p>
                </div>
                <div class="bg-blue-50 rounded-xl p-4 max-h-48 overflow-y-auto">
                    <p class="text-sm text-gray-600 mb-2">Tagihan Terpilih</p>
                    <div id="batch_bills_list" class="space-y-1"></div>
                </div>
                <div class="bg-emerald-50 rounded-xl p-4">
                    <p class="text-sm text-gray-600">Total Pembayaran</p>
                    <p class="text-2xl font-bold text-emerald-600" id="batch_total">Rp 0</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Metode Pembayaran</label>
                    <select name="payment_method" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        <option value="cash">💵 Tunai</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                        <option value="qris">📱 QRIS</option>
                        <option value="card">💳 Kartu Kredit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">No. Referensi (Opsional)</label>
                    <input type="text" name="reference_number" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="mt-6 flex gap-3">
                <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-all">
                    Proses Pembayaran Batch
                </button>
                <button type="button" onclick="closeBatchPayModal()" class="px-4 py-3 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300 transition-all">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const monthNames = {
    1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
    5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
    9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
};

// Quick Pay Modal Functions
function openQuickPayModal(element) {
    const billId = element.dataset.billId;
    const studentId = element.dataset.studentId;
    const studentName = element.dataset.studentName;
    const amount = parseFloat(element.dataset.amount);
    const lateFee = parseFloat(element.dataset.lateFee || 0);
    const month = element.dataset.month;
    const year = element.dataset.year;
    const paymentType = element.dataset.paymentType;
    
    document.getElementById('quick_bill_id').value = billId;
    document.getElementById('quick_student_id').value = studentId;
    document.getElementById('quick_student_name').textContent = studentName;
    
    const monthName = (month && monthNames[month]) ? monthNames[month] : '';
    const detailText = monthName ? `${paymentType} (${monthName} ${year})` : `${paymentType} (${year})`;
    document.getElementById('quick_bill_detail').textContent = detailText;
    
    // Calculate total with late fee
    const totalWithLateFee = amount + lateFee;
    document.getElementById('quick_amount').value = totalWithLateFee;
    
    // Show late fee if exists
    if (lateFee > 0) {
        document.getElementById('quick_late_fee_display').classList.remove('hidden');
        document.getElementById('quick_late_fee_amount').textContent = `Rp ${lateFee.toLocaleString('id-ID')}`;
        document.getElementById('quick_amount_note').textContent = `Tagihan: Rp ${amount.toLocaleString('id-ID')} + Denda: Rp ${lateFee.toLocaleString('id-ID')}`;
    } else {
        document.getElementById('quick_late_fee_display').classList.add('hidden');
        document.getElementById('quick_amount_note').textContent = monthName ? `Tagihan bulan ${monthName}` : 'Tagihan 1 Kali';
    }
    
    document.getElementById('quickPayModal').classList.remove('hidden');
}

function closeQuickPayModal() {
    document.getElementById('quickPayModal').classList.add('hidden');
}

// Batch Payment Functions
let selectedBills = [];

// Enable checkbox selection on shift+click
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.bill-box.can-pay').forEach(box => {
        box.addEventListener('click', function(e) {
            if (e.shiftKey) {
                e.stopPropagation();
                toggleBillSelection(this);
            }
        });
    });
});

function toggleBillSelection(element) {
    const checkbox = element.previousElementSibling;
    if (!checkbox || !checkbox.classList.contains('bill-checkbox')) return;
    
    const billId = element.dataset.billId;
    const index = selectedBills.findIndex(b => b.id === billId);
    
    if (index > -1) {
        selectedBills.splice(index, 1);
        element.classList.remove('ring-2', 'ring-blue-500');
        checkbox.checked = false;
    } else {
        selectedBills.push({
            id: billId,
            studentId: element.dataset.studentId,
            studentName: element.dataset.studentName,
            amount: parseFloat(element.dataset.amount),
            lateFee: parseFloat(element.dataset.lateFee || 0),
            month: element.dataset.month,
            year: element.dataset.year,
            paymentType: element.dataset.paymentType
        });
        element.classList.add('ring-2', 'ring-blue-500');
        checkbox.checked = true;
    }
    
    updateBatchButton();
}

function updateBatchButton() {
    const btn = document.getElementById('batchPayBtn');
    const text = document.getElementById('batchPayText');
    
    if (selectedBills.length > 0) {
        btn.classList.remove('hidden');
        btn.classList.add('flex');
        text.textContent = `Bayar Terpilih (${selectedBills.length})`;
    } else {
        btn.classList.add('hidden');
        btn.classList.remove('flex');
    }
}

function openBatchPayModal() {
    if (selectedBills.length === 0) return;
    
    // Check if all bills are from same student
    const studentId = selectedBills[0].studentId;
    const allSameStudent = selectedBills.every(b => b.studentId === studentId);
    
    if (!allSameStudent) {
        alert('Harap pilih tagihan dari siswa yang sama!');
        return;
    }
    
    // Populate modal
    document.getElementById('batch_student_name').textContent = selectedBills[0].studentName;
    document.getElementById('batch_student_id').value = studentId;
    document.getElementById('batch_bill_ids').value = selectedBills.map(b => b.id).join(',');
    
    // Populate bills list
    const billsList = document.getElementById('batch_bills_list');
    billsList.innerHTML = '';
    let total = 0;
    let totalLateFee = 0;
    
    selectedBills.forEach(bill => {
        const billAmount = bill.amount;
        const lateFee = bill.lateFee || 0;
        const billTotal = billAmount + lateFee;
        
        total += billAmount;
        totalLateFee += lateFee;
        
        const monthName = (bill.month && monthNames[bill.month]) ? monthNames[bill.month] : '';
        const detailText = monthName ? `${bill.paymentType} (${monthName} ${bill.year})` : `${bill.paymentType} (${bill.year})`;
        const div = document.createElement('div');
        div.className = 'flex justify-between text-sm';
        div.innerHTML = `
            <span>${detailText}</span>
            <span class="font-semibold">Rp ${new Intl.NumberFormat('id-ID').format(billTotal)}${lateFee > 0 ? '<span class="text-xs text-orange-600"> (+denda)</span>' : ''}</span>
        `;
        billsList.appendChild(div);
    });
    
    const grandTotal = total + totalLateFee;
    
    if (totalLateFee > 0) {
        const lateFeeDiv = document.createElement('div');
        lateFeeDiv.className = 'flex justify-between text-sm pt-2 mt-2 border-t border-orange-200';
        lateFeeDiv.innerHTML = `
            <span class="text-orange-600">Total Denda Keterlambatan</span>
            <span class="font-semibold text-orange-600">Rp ${new Intl.NumberFormat('id-ID').format(totalLateFee)}</span>
        `;
        billsList.appendChild(lateFeeDiv);
    }
    
    document.getElementById('batch_total').textContent = `Rp ${new Intl.NumberFormat('id-ID').format(grandTotal)}`;
    
    document.getElementById('batchPayModal').classList.remove('hidden');
}

function closeBatchPayModal() {
    document.getElementById('batchPayModal').classList.add('hidden');
}

// Close modals on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQuickPayModal();
        closeBatchPayModal();
        closeBulkUpdateModal();
        closeBulkDeleteModal();
    }
});

</script>

<!-- Modern Bulk Update Modal (Treasurer) -->
<div id="bulkUpdateModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" onclick="closeBulkUpdateModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative z-50 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-indigo-500/10 to-transparent"></div>
            <div class="px-8 py-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Ubah Nominal Massal</h3>
                            <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider mt-0.5">Centang Tagihan Siswa yang Ingin Diperbarui</p>
                        </div>
                    </div>
                    <button onclick="closeBulkUpdateModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-900 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-5">
                    <!-- Input Nominal Baru -->
                    <div class="p-5 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-indigo-900 mb-1">Nominal Tagihan Baru (Rp) <span class="text-rose-500">*</span></label>
                            <p class="text-xs text-gray-500">Nominal baru ini akan diterapkan pada tagihan yang Anda centang.</p>
                        </div>
                        <input type="number" id="bulk_new_amount" placeholder="Misal: 150000" min="0" step="1000"
                            class="w-full md:w-64 px-4 py-3 bg-white border-2 border-indigo-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 font-bold text-gray-900 text-lg outline-none">
                    </div>

                    <!-- Live Search & Select All -->
                    <div class="flex items-center justify-between gap-4 pt-1">
                        <div class="relative flex-1">
                            <input type="text" id="modal_update_search" onkeyup="filterModalBills('update')" placeholder="🔍 Cari nama siswa atau kelas di modal..." 
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <label class="flex items-center gap-2 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 rounded-xl cursor-pointer text-xs font-bold text-indigo-700 transition-all">
                            <input type="checkbox" id="modal_update_select_all" onchange="toggleModalSelectAll('update', this.checked)" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            Pilih Semua
                        </label>
                    </div>

                    <!-- Visual Table inside Modal -->
                    <div class="max-h-64 overflow-y-auto border border-gray-100 rounded-2xl shadow-inner bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider sticky top-0 bg-white border-b border-gray-100">
                                <tr>
                                    <th class="p-3 text-center w-10">Pilih</th>
                                    <th class="p-3">Siswa</th>
                                    <th class="p-3">Jenis Tagihan</th>
                                    <th class="p-3">Bulan/Tahun</th>
                                    <th class="p-3 text-right">Nominal Saat Ini</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50" id="modal_update_bills_tbody">
                                @forelse($unpaidBillsList as $bill)
                                <tr class="modal-update-row hover:bg-indigo-50/40 transition-colors" data-search="{{ strtolower($bill->student->full_name . ' ' . ($bill->student->classroom->class_name ?? '')) }}">
                                    <td class="p-3 text-center">
                                        <input type="checkbox" class="modal-update-cb w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" value="{{ $bill->id }}">
                                    </td>
                                    <td class="p-3 font-bold text-gray-900">
                                        {{ $bill->student->full_name }}
                                        <span class="block text-[10px] font-normal text-gray-400">{{ $bill->student->classroom->class_name ?? 'Tanpa Kelas' }}</span>
                                    </td>
                                    <td class="p-3 text-gray-600">{{ $bill->paymentType->type_name ?? '-' }}</td>
                                    <td class="p-3 text-gray-600">
                                        @if($bill->month)
                                        {{ \Carbon\Carbon::create($bill->year, $bill->month, 1)->format('M Y') }}
                                        @else
                                        1 Kali ({{ $bill->year }})
                                        @endif
                                    </td>
                                    <td class="p-3 text-right font-bold text-gray-800">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-400 font-medium">Tidak ada tagihan yang belum dibayar saat ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="button" onclick="closeBulkUpdateModal()" class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-semibold text-xs hover:bg-gray-200 transition-all">
                            Batal
                        </button>
                        <button type="button" id="bulk_update_submit_btn" onclick="submitBulkUpdateVisual()" class="flex-[2] py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-xs shadow-lg shadow-indigo-500/20 hover:-translate-y-0.5 transition-all">
                            Simpan & Perbarui Nominal Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modern Bulk Delete Modal (Treasurer) -->
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" onclick="closeBulkDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative z-50 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-rose-500/10 to-transparent"></div>
            <div class="px-8 py-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Hapus Tagihan Massal</h3>
                            <p class="text-xs font-bold text-rose-500 uppercase tracking-wider mt-0.5">Centang Tagihan Hasil Latihan / Uji Coba yang Akan Dihapus</p>
                        </div>
                    </div>
                    <button onclick="closeBulkDeleteModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-900 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-5">
                    <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-xl text-xs text-amber-800 space-y-1">
                        <p class="font-bold text-xs text-amber-900">🛡️ Pembersihan Tagihan Latihan</p>
                        <p>Centang tagihan-tagihan di bawah ini yang ingin Anda hapus dari sistem. Tagihan yang pernah dibayar secara otomatis terlindungi.</p>
                    </div>

                    <!-- Live Search & Select All -->
                    <div class="flex items-center justify-between gap-4">
                        <div class="relative flex-1">
                            <input type="text" id="modal_delete_search" onkeyup="filterModalBills('delete')" placeholder="🔍 Cari nama siswa atau kelas di modal..." 
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500">
                        </div>
                        <label class="flex items-center gap-2 px-3 py-2 bg-rose-50 hover:bg-rose-100 rounded-xl cursor-pointer text-xs font-bold text-rose-700 transition-all">
                            <input type="checkbox" id="modal_delete_select_all" onchange="toggleModalSelectAll('delete', this.checked)" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500">
                            Pilih Semua
                        </label>
                    </div>

                    <!-- Visual Table inside Modal -->
                    <div class="max-h-64 overflow-y-auto border border-gray-100 rounded-2xl shadow-inner bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider sticky top-0 bg-white border-b border-gray-100">
                                <tr>
                                    <th class="p-3 text-center w-10">Pilih</th>
                                    <th class="p-3">Siswa</th>
                                    <th class="p-3">Jenis Tagihan</th>
                                    <th class="p-3">Bulan/Tahun</th>
                                    <th class="p-3 text-right">Nominal Tagihan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50" id="modal_delete_bills_tbody">
                                @forelse($unpaidBillsList as $bill)
                                <tr class="modal-delete-row hover:bg-rose-50/40 transition-colors" data-search="{{ strtolower($bill->student->full_name . ' ' . ($bill->student->classroom->class_name ?? '')) }}">
                                    <td class="p-3 text-center">
                                        <input type="checkbox" class="modal-delete-cb w-4 h-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500 cursor-pointer" value="{{ $bill->id }}">
                                    </td>
                                    <td class="p-3 font-bold text-gray-900">
                                        {{ $bill->student->full_name }}
                                        <span class="block text-[10px] font-normal text-gray-400">{{ $bill->student->classroom->class_name ?? 'Tanpa Kelas' }}</span>
                                    </td>
                                    <td class="p-3 text-gray-600">{{ $bill->paymentType->type_name ?? '-' }}</td>
                                    <td class="p-3 text-gray-600">
                                        @if($bill->month)
                                        {{ \Carbon\Carbon::create($bill->year, $bill->month, 1)->format('M Y') }}
                                        @else
                                        1 Kali ({{ $bill->year }})
                                        @endif
                                    </td>
                                    <td class="p-3 text-right font-bold text-rose-600">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-400 font-medium">Tidak ada tagihan yang dapat dihapus saat ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="button" onclick="closeBulkDeleteModal()" class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-semibold text-xs hover:bg-gray-200 transition-all">
                            Batal
                        </button>
                        <button type="button" id="bulk_delete_submit_btn" onclick="submitBulkDeleteVisual()" class="flex-[2] py-3 bg-gradient-to-r from-rose-600 to-red-700 text-white rounded-xl font-semibold text-xs shadow-lg shadow-rose-500/20 hover:-translate-y-0.5 transition-all">
                            Ya, Hapus Tagihan Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openBulkUpdateModal(useSelected = false) {
    document.getElementById('bulkUpdateModal').classList.remove('hidden');
}

function closeBulkUpdateModal() {
    document.getElementById('bulkUpdateModal').classList.add('hidden');
}

function openBulkDeleteModal(useSelected = false) {
    document.getElementById('bulkDeleteModal').classList.remove('hidden');
}

function closeBulkDeleteModal() {
    document.getElementById('bulkDeleteModal').classList.add('hidden');
}

function filterModalBills(type) {
    const q = document.getElementById(`modal_${type}_search`).value.toLowerCase();
    document.querySelectorAll(`.modal-${type}-row`).forEach(row => {
        const text = row.dataset.search || '';
        if (text.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function toggleModalSelectAll(type, isChecked) {
    document.querySelectorAll(`.modal-${type}-cb`).forEach(cb => {
        const row = cb.closest('tr');
        if (row && row.style.display !== 'none') {
            cb.checked = isChecked;
        }
    });
}

function submitBulkUpdateVisual() {
    const newAmount = document.getElementById('bulk_new_amount').value;
    if (!newAmount || parseFloat(newAmount) < 0) {
        alert('Harap masukkan nominal tagihan baru yang valid!');
        return;
    }

    let selectedIds = [];
    document.querySelectorAll('.modal-update-cb:checked').forEach(cb => {
        selectedIds.push(parseInt(cb.value));
    });

    if (selectedIds.length === 0) {
        alert('Harap centang setidaknya 1 tagihan dari daftar di dalam modal!');
        return;
    }

    const btn = document.getElementById('bulk_update_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">Memproses Perubahan...</span>';

    fetch('{{ route('treasurer.bills.bulk-update-amount') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            bill_ids: selectedIds,
            new_amount: newAmount,
            _token: '{{ csrf_token() }}'
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan.');
            btn.disabled = false;
            btn.textContent = 'Simpan & Perbarui Nominal Terpilih';
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.textContent = 'Simpan & Perbarui Nominal Terpilih';
    });
}

function submitBulkDeleteVisual() {
    let selectedIds = [];
    document.querySelectorAll('.modal-delete-cb:checked').forEach(cb => {
        selectedIds.push(parseInt(cb.value));
    });

    if (selectedIds.length === 0) {
        alert('Harap centang setidaknya 1 tagihan dari daftar di dalam modal!');
        return;
    }

    if (!confirm(`Apakah Anda yakin ingin menghapus ${selectedIds.length} tagihan terpilih ini? Tindakan ini tidak dapat dibatalkan.`)) {
        return;
    }

    const btn = document.getElementById('bulk_delete_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">Memproses Hapus...</span>';

    fetch('{{ route('treasurer.bills.bulk-delete') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            bill_ids: selectedIds,
            _token: '{{ csrf_token() }}'
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan.');
            btn.disabled = false;
            btn.textContent = 'Ya, Hapus Tagihan Terpilih';
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.textContent = 'Ya, Hapus Tagihan Terpilih';
    });
}
</script>
@endsection
