@extends('layouts.admin')

@section('title', 'Riwayat Pembayaran')

@section('content')
<div class="space-y-8 pb-12">
    <!-- Header Section -->
    <div class="bg-white border border-slate-100 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 text-white shadow-lg shadow-blue-600/20 shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Pembayaran</h1>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Monitoring dan pencatatan seluruh transaksi pembayaran siswa</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.payments.bulk-create') }}" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-xl font-bold text-xs hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Pembayaran Massal
                </a>

                <a href="{{ route('admin.payments.create') }}" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-bold text-xs shadow-md shadow-blue-600/20 hover:from-blue-700 hover:to-indigo-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Catat Pembayaran
                </a>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Transaksi -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Transaksi</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ number_format($payments->total(), 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Transaksi terdaftar</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Nominal Terbayar (Page Sum / Total Sum) -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Nominal Diterima</p>
                    <h3 class="text-2xl font-extrabold text-emerald-600 tracking-tight">
                        Rp {{ number_format($payments->sum('amount_paid'), 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium">Akumulasi halaman ini</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Terverifikasi -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-teal-600 uppercase tracking-wider">Terverifikasi</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($payments->where('is_verified', true)->count(), 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium">Status validasi lunas</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-teal-50 text-teal-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Tunai vs Digital -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-purple-600 uppercase tracking-wider">Tunai vs Digital</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ $payments->where('payment_method', 'cash')->count() }} / {{ $payments->where('payment_method', '!=', 'cash')->count() }}
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium">Rasio Tunai : Transfer/QRIS</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white border border-slate-100 rounded-2xl p-6 sm:p-8 shadow-sm">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Tahun Ajaran -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Tahun Ajaran</label>
                    <div class="relative">
                        <select name="academic_year_id" onchange="this.form.submit()" 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer">
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>{{ $year->year }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Sekolah -->
                @if(auth()->user()->isSuperAdmin())
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Sekolah</label>
                    <div class="relative">
                        <select name="school_id" onchange="this.form.submit()" 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Sekolah</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Kelas -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Kelas</label>
                    <div class="relative">
                        <select name="classroom_id" onchange="this.form.submit()" 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Kelas</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ $classroomId == $classroom->id ? 'selected' : '' }}>{{ $classroom->class_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Jenis Pembayaran -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Jenis Pembayaran</label>
                    <div class="relative">
                        <select name="payment_type_id" onchange="this.form.submit()" 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Jenis</option>
                            @foreach($paymentTypes as $type)
                                <option value="{{ $type->id }}" {{ $paymentTypeId == $type->id ? 'selected' : '' }}>{{ $type->type_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Status Verifikasi -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Status Validasi</label>
                    <div class="relative">
                        <select name="is_verified" onchange="this.form.submit()" 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="1" {{ $isVerified == '1' ? 'selected' : '' }}>Terverifikasi</option>
                            <option value="0" {{ $isVerified == '0' ? 'selected' : '' }}>Belum Verifikasi</option>
                        </select>
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Metode Bayar</label>
                    <div class="relative">
                        <select name="payment_method" onchange="this.form.submit()" 
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Metode</option>
                            <option value="cash" {{ $paymentMethod == 'cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="transfer" {{ $paymentMethod == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="qris" {{ $paymentMethod == 'qris' ? 'selected' : '' }}>QRIS</option>
                        </select>
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Cari -->
                <div class="space-y-1.5 {{ auth()->user()->isSuperAdmin() ? 'lg:col-span-2' : 'lg:col-span-2' }}">
                    <label class="text-xs font-bold text-slate-700">Pencarian</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" 
                            placeholder="Nama siswa atau NISN..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center justify-end gap-3 pt-2 border-t border-slate-100">
                <a href="{{ route('admin.payments.index', ['reset' => 1]) }}" 
                    class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all">
                    Reset Filter
                </a>
                <a href="{{ route('admin.payments.export', ['is_verified' => $isVerified, 'payment_method' => $paymentMethod, 'search' => $search, 'school_id' => $schoolId, 'classroom_id' => $classroomId, 'payment_type_id' => $paymentTypeId, 'academic_year_id' => $academicYearId, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl font-bold text-xs hover:bg-blue-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel
                </a>
                <button type="submit" 
                    class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-xl font-bold text-xs shadow-md shadow-blue-600/20 hover:bg-blue-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-separate border-spacing-0">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider w-12">No</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Tanggal & Waktu</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Siswa</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Tagihan & Periode</th>
                        <th class="px-5 py-4 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">Jumlah Bayar</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Metode</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Status Validasi</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $index => $payment)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-4 text-center align-middle">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">
                                {{ $payments->firstItem() + $index }}
                            </span>
                        </td>
                        <td class="px-5 py-4 align-middle text-xs font-semibold text-slate-700 whitespace-nowrap">
                            {{ $payment->payment_date ? $payment->payment_date->format('d M Y H:i') : '-' }}
                        </td>
                        <td class="px-5 py-4 align-middle">
                            <div>
                                <p class="font-bold text-slate-900 text-xs">{{ $payment->student->full_name }}</p>
                                <p class="text-[11px] font-semibold text-slate-400 tracking-wider uppercase mt-0.5">{{ $payment->student->nisn ?? 'NISN -' }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-4 align-middle">
                            @if($payment->bill)
                                <div>
                                    <p class="font-bold text-slate-900 text-xs">{{ $payment->bill->paymentType->type_name }} ({{ $payment->bill->academicYear->year }})</p>
                                    @if($payment->bill->month)
                                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">Periode: {{ \Carbon\Carbon::create(null, $payment->bill->month, 1)->translatedFormat('F') }} {{ $payment->bill->year }}</p>
                                    @else
                                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">1 Kali Bayar ({{ $payment->bill->year }})</p>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Pembayaran General / Massal</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-middle text-right">
                            <span class="font-extrabold text-blue-600 text-xs">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-5 py-4 align-middle text-center">
                            @if($payment->payment_method === 'cash')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Tunai
                                </span>
                            @elseif($payment->payment_method === 'transfer')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                    </svg>
                                    Transfer
                                </span>
                            @elseif($payment->payment_method === 'qris')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                    QRIS
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-[11px] font-bold">
                                    {{ strtoupper($payment->payment_method) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-middle text-center">
                            @if($payment->is_verified)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                                Terverifikasi
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Belum Verifikasi
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-middle text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.payments.show', $payment) }}" 
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm"
                                    title="Lihat Detail Transaksi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->isSuperAdmin())
                                <a href="{{ route('admin.payments.edit', $payment) }}" 
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-amber-600 hover:border-amber-300 transition-all shadow-sm"
                                    title="Edit Transaksi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi pembayaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-rose-600 hover:border-rose-300 transition-all shadow-sm"
                                        title="Hapus Transaksi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Tidak ada data pembayaran</p>
                                    <p class="text-xs text-slate-400 mt-1">Silakan pilih filter yang sesuai atau tambahkan pembayaran baru.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-white">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
