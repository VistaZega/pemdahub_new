@extends('layouts.admin')

@section('title', 'Tagihan Siswa')

@section('styles')
<style>
    @keyframes grow-x {
        from { transform: scaleX(0); transform-origin: left; }
        to { transform: scaleX(1); transform-origin: left; }
    }
    .animate-grow-x {
        animation: grow-x 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }

</style>
@endsection

@section('content')
<div class="relative min-h-screen pb-12">


    <div class="relative z-10 space-y-8">
        <!-- Header Section -->
        <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-lg">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                <div class="flex items-center gap-6">
                    <div class="group relative">
                        <div class="relative flex items-center justify-center w-14 h-14 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl shadow-lg">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Tagihan Siswa
                        </h1>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold border border-emerald-100">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                Managemen Pembayaran
                            </span>
                            @php
                                $currentYear = $academicYears->firstWhere('id', $academicYearId);
                            @endphp
                            <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold border border-blue-100 uppercase tracking-wider">
                                {{ $currentYear ? $currentYear->year : 'TA' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4">
                    <!-- Batch Action Buttons (Hidden by default) -->
                    <button type="button" id="bulkWaiveBtn" onclick="openBulkWaiveModal()" 
                        class="hidden group items-center gap-2 px-5 py-2.5 bg-amber-500 text-white rounded-xl font-semibold text-sm shadow-lg shadow-amber-500/20 hover:bg-amber-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="bulkWaiveText">Hapus Biaya Admin (0)</span>
                    </button>

                    <button type="button" id="batchPayBtn" onclick="openBatchPayModal()" 
                        class="hidden group items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl font-semibold text-sm shadow-lg shadow-blue-500/20 hover:bg-blue-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span id="batchPayText">Bayar Terpilih (0)</span>
                    </button>

                    <button type="button" onclick="openBulkUpdateModal(false)" 
                        class="group flex items-center gap-2 px-5 py-2.5 bg-indigo-50 border-2 border-indigo-500 text-indigo-700 rounded-xl font-semibold text-sm hover:bg-indigo-600 hover:text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Ubah Nominal Massal
                    </button>

                    <button type="button" onclick="openBulkDeleteModal(false)" 
                        class="group flex items-center gap-2 px-5 py-2.5 bg-rose-50 border-2 border-rose-500 text-rose-700 rounded-xl font-semibold text-sm hover:bg-rose-600 hover:text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus Massal
                    </button>

                    <a href="{{ route('admin.bills.bulk-create') }}" 
                        class="group flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-emerald-500 text-emerald-600 rounded-xl font-semibold text-sm hover:bg-emerald-500 hover:text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Tagihan Massal
                    </a>

                    <a href="{{ route('admin.bills.create') }}" 
                        class="group flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold text-sm shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all duration-300">
                        <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Tagihan
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-lg">
            <form method="GET" action="{{ route('admin.bills.index') }}" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Tahun Ajaran</label>
                        <div class="relative group">
                            <select name="academic_year_id" onchange="this.form.submit()" 
                                class="w-full pl-11 pr-4 py-3 bg-white border-2 border-gray-100 rounded-2xl focus:border-emerald-500 focus:ring-0 transition-all appearance-none cursor-pointer">
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>{{ $year->year }}</option>
                                @endforeach
                            </select>
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Sekolah</label>
                        <div class="relative group">
                            <select name="school_id" onchange="this.form.submit()" 
                                class="w-full pl-11 pr-4 py-3 bg-white border-2 border-gray-100 rounded-2xl focus:border-emerald-500 focus:ring-0 transition-all appearance-none cursor-pointer">
                                <option value="">Semua Sekolah</option>
                                @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Jenis Tagihan</label>
                        <div class="relative group">
                            <select name="payment_type_id" onchange="this.form.submit()" 
                                class="w-full pl-11 pr-4 py-3 bg-white border-2 border-gray-100 rounded-2xl focus:border-emerald-500 focus:ring-0 transition-all appearance-none cursor-pointer">
                                <option value="">Semua Jenis</option>
                                @foreach($paymentTypes as $type)
                                <option value="{{ $type->id }}" {{ $paymentTypeId == $type->id ? 'selected' : '' }}>
                                    {{ ($type->is_recurring ?? false) ? '[Bulanan] ' : '[1 Kali] ' }}{{ $type->type_name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Kelas</label>
                        <div class="relative group">
                            <select name="classroom_id" onchange="this.form.submit()" 
                                class="w-full pl-11 pr-4 py-3 bg-white border-2 border-gray-100 rounded-2xl focus:border-emerald-500 focus:ring-0 transition-all appearance-none cursor-pointer">
                                <option value="">Semua Kelas</option>
                                @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ $classroomId == $classroom->id ? 'selected' : '' }}>{{ $classroom->class_name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Pencarian</label>
                        <div class="relative group">
                            <input type="text" name="search" value="{{ $search }}" 
                                placeholder="Nama / NISN..."
                                class="w-full pl-11 pr-4 py-3 bg-white border-2 border-gray-100 rounded-2xl focus:border-emerald-500 focus:ring-0 transition-all">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 mt-8">
                    <a href="{{ route('admin.bills.index', ['reset' => 1]) }}" 
                        class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all">
                        Reset Filter
                    </a>
                    <a href="{{ route('admin.bills.export', request()->only(['academic_year_id', 'payment_type_id', 'classroom_id', 'search'])) }}" 
                        class="flex items-center gap-2 px-6 py-3 bg-blue-50 text-blue-600 rounded-xl font-bold hover:bg-blue-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Excel
                    </a>
                    <button type="submit" 
                        class="flex items-center gap-2 px-8 py-3 bg-emerald-500 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 hover:-translate-y-0.5 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Cards (Rekapitulasi) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Total Siswa -->
            <div class="group relative">
                <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="space-y-4">
                            <p class="text-sm font-bold text-blue-600/60 uppercase tracking-widest">Total Siswa</p>
                            <h3 class="text-3xl font-bold text-gray-900 tracking-tight">{{ number_format($totalStudents ?? 0, 0, ',', '.') }}</h3>
                            <p class="text-xs text-gray-400 font-medium">Siswa terdaftar TA ini</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/30 transform group-hover:rotate-12 transition-all duration-500">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Pembayaran -->
            <div class="group relative">
                <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="space-y-4">
                            <p class="text-sm font-bold text-emerald-600/60 uppercase tracking-widest">Total Terbayar</p>
                            <h3 class="text-3xl font-bold tracking-tight text-emerald-600">
                                <span class="text-lg font-bold">Rp</span>{{ number_format(($totalPaid ?? 0) / 1000, 0, ',', '.') }}K
                            </h3>
                            <p class="text-xs text-gray-400 font-medium">Dana masuk bulan ini</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg shadow-emerald-500/30 transform group-hover:rotate-12 transition-all duration-500">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Tunggakan -->
            <div class="group relative">
                <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="space-y-4">
                            <p class="text-sm font-bold text-rose-600/60 uppercase tracking-widest">Total Sisa</p>
                            <h3 class="text-3xl font-bold text-rose-600 tracking-tight">
                                <span class="text-lg font-bold">Rp</span>{{ number_format(($totalOutstanding ?? 0) / 1000, 0, ',', '.') }}K
                            </h3>
                            <p class="text-xs text-gray-400 font-medium">Tunggakan siswa terdata</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl shadow-lg shadow-rose-500/30 transform group-hover:rotate-12 transition-all duration-500">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Tagihan -->
            <div class="group relative">
                <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="space-y-4">
                            <p class="text-sm font-bold text-amber-600/60 uppercase tracking-widest">Target Dana</p>
                            <h3 class="text-3xl font-bold text-gray-900 tracking-tight">
                                <span class="text-lg font-bold">Rp</span>{{ number_format(($totalBillsCount ?? 0) / 1000, 0, ',', '.') }}K
                            </h3>
                            <p class="text-xs text-gray-400 font-medium">Total tagihan periode ini</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg shadow-amber-500/30 transform group-hover:rotate-12 transition-all duration-500">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Bills Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 transition-all duration-300">
        <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 px-6 py-4 border-b border-emerald-100">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900"><i class="fas fa-clipboard mr-1"></i> Daftar Tagihan Siswa</h2>
            </div>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm border-separate border-spacing-0">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-3 py-5 text-center">
                            <input type="checkbox" id="selectAll" class="w-5 h-5 rounded-lg border-2 border-gray-200 text-emerald-500 focus:ring-emerald-500/20 transition-all cursor-pointer">
                        </th>
                        <th class="px-4 py-5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa</th>
                        <th class="px-4 py-5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis</th>
                        @php $months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun']; @endphp
                        @foreach($months as $m)
                        <th class="px-0 py-5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $m }}</th>
                        @endforeach
                        <th class="px-6 py-5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Tunggakan</th>
                        <th class="px-6 py-5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($paginatedBills as $index => $group)
                    <tr class="group hover:bg-emerald-50/30 transition-all duration-300">
                        <td class="px-3 py-4 text-center">
                            @php
                                $billIds = collect($group['monthly_data'])->filter()->pluck('id')->join(',');
                                if (!$billIds && isset($group['first_bill'])) {
                                    $billIds = $group['first_bill']->id;
                                }
                                $hasLateFee = collect($group['monthly_data'])->filter(fn($b) => $b && $b->late_fee > 0 && !$b->late_fee_waived && $b->status !== 'lunas')->isNotEmpty();
                                if (!$hasLateFee && isset($group['first_bill'])) {
                                    $fb = $group['first_bill'];
                                    $hasLateFee = $fb->late_fee > 0 && !$fb->late_fee_waived && $fb->status !== 'lunas';
                                }
                            @endphp
                            @if($hasLateFee)
                            <input type="checkbox" class="row-checkbox w-5 h-5 rounded-lg border-2 border-gray-200 text-amber-500 focus:ring-amber-500/20 transition-all cursor-pointer" 
                                   data-row-index="{{ $index }}">
                            @endif
                        </td>
                        @if($group['is_first_row'])
                        <td class="px-4 py-4 align-top" rowspan="{{ $group['rowspan'] }}">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-gray-100 text-gray-500 font-bold text-xs ring-4 ring-white shadow-sm">
                                {{ $paginatedBills->firstItem() + $index }}
                            </span>
                        </td>
                        <td class="px-4 py-4 align-top" rowspan="{{ $group['rowspan'] }}">
                            <div class="flex items-center gap-4">
                                <div class="relative flex-shrink-0">

                                    <img src="{{ $group['student']->photo_url }}" class="w-12 h-12 rounded-xl object-cover border-2 border-white shadow-md" alt="{{ $group['student']->full_name }}">
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 truncate hover:text-emerald-600 transition-colors cursor-default">{{ $group['student']->full_name }}</p>
                                    <p class="text-xs font-semibold text-gray-400 tracking-wider uppercase mt-0.5">{{ $group['student']->nisn }}</p>
                                </div>
                            </div>
                        </td>
                        @endif
                        <td class="px-4 py-4 align-top">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-semibold uppercase tracking-wider border border-emerald-100">
                                {{ $group['payment_type']->type_name }} ({{ $group['academic_year']->year }})
                            </span>
                        </td>
                        
                        @if($group['payment_type']->is_recurring ?? true)
                            @php $monthMap = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6]; @endphp
                            @foreach($monthMap as $m)
                            <td class="px-0.5 py-4 text-center">
                                @if($group['monthly_data'][$m])
                                    @php 
                                        $bill = $group['monthly_data'][$m]; 
                                        $colorClass = $bill->status_color; 
                                    @endphp
                                    <div class="flex justify-center">
                                        <button type="button" 
                                            onclick="if(!event.shiftKey) openQuickPayModal(this)"
                                            data-bill-id="{{ $bill->id }}"
                                            data-student-id="{{ $group['student']->id }}"
                                            data-student-name="{{ $group['student']->full_name }}"
                                            data-amount="{{ $bill->amount }}"
                                            data-late-fee="{{ $bill->late_fee }}"
                                            data-month="{{ $bill->month }}"
                                            data-year="{{ $bill->year }}"
                                            data-payment-type="{{ $group['payment_type']->type_name }}"
                                            class="w-7 h-7 rounded-lg shadow-sm border-2 border-white transform transition-all duration-300 {{ $bill->status !== 'lunas' ? 'hover:scale-125 hover:shadow-xl hover:-translate-y-1' : '' }} bg-{{ $colorClass }}-500 {{ $bill->isOverdue() ? 'animate-pulse' : '' }} flex items-center justify-center p-0.5"
                                            title="{{ $group['payment_type']->type_name }} - {{ \Carbon\Carbon::create($bill->year, $bill->month, 1)->format('M Y') }} (Rp {{ number_format($bill->amount, 0, ',', '.') }})">
                                            @if($bill->status === 'lunas')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            @elseif($bill->isOverdue())
                                            <div class="w-1.5 h-1.5 rounded-full bg-white opacity-80"></div>
                                            @endif
                                        </button>
                                    </div>
                                @else
                                    <span class="text-gray-200 text-xs font-bold">—</span>
                                @endif
                            </td>
                            @endforeach
                        @else
                            <td colspan="12" class="px-3 py-4 align-middle">
                                @php
                                    $fb = $group['first_bill'] ?? null;
                                    $statusText = 'Belum Bayar';
                                    $colorClass = 'yellow';
                                    $isPaid = false;
                                    
                                    if ($fb) {
                                        $isPaid = $fb->status === 'lunas';
                                        if ($isPaid) {
                                            $statusText = 'Lunas';
                                            $colorClass = 'emerald';
                                        } elseif ($fb->status === 'cicilan') {
                                            $statusText = 'Cicilan';
                                            $colorClass = 'blue';
                                        } elseif ($fb->isOverdue()) {
                                            $statusText = 'Jatuh Tempo';
                                            $colorClass = 'red';
                                        }
                                    }
                                @endphp
                                <div class="flex items-center justify-between px-4 py-2 bg-emerald-50/20 rounded-2xl border border-emerald-100/40">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $colorClass }}-50 text-{{ $colorClass }}-700 border border-{{ $colorClass }}-100">
                                            {{ $statusText }}
                                        </span>
                                        @if($fb && $fb->due_date)
                                        <span class="text-xs text-gray-500 font-medium">
                                            Jatuh Tempo: {{ \Carbon\Carbon::parse($fb->due_date)->translatedFormat('d M Y') }}
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-4 font-sans">
                                        <span class="text-sm font-bold text-gray-900">
                                            Rp {{ number_format($group['total_amount'], 0, ',', '.') }}
                                        </span>
                                        
                                        @if($fb && !$isPaid)
                                        <button type="button"
                                            onclick="openQuickPayModal(this)"
                                            data-bill-id="{{ $fb->id }}"
                                            data-student-id="{{ $group['student']->id }}"
                                            data-student-name="{{ $group['student']->full_name }}"
                                            data-amount="{{ $fb->amount }}"
                                            data-late-fee="{{ $fb->late_fee }}"
                                            data-month=""
                                            data-year="{{ $fb->year }}"
                                            data-payment-type="{{ $group['payment_type']->type_name }}"
                                            class="px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all shadow-md">
                                            Bayar Cepat
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        @endif

                        <td class="px-6 py-4 text-right align-top">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-tight">{{ $group['bill_count'] }} Tagihan</p>
                                @if($group['outstanding'] > 0)
                                    <p class="text-sm font-bold text-rose-600 tracking-tight">Rp {{ number_format($group['outstanding'], 0, ',', '.') }}</p>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-semibold uppercase tracking-wider border border-emerald-100">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                        Lunas
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center align-top whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                @php $firstBill = collect($group['monthly_data'])->filter()->first() ?? $group['first_bill'] ?? null; @endphp
                                <a href="{{ $firstBill ? route('admin.bills.show', $firstBill->id) : '#' }}" 
                                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-emerald-500 hover:border-emerald-200 hover:shadow-lg transition-all duration-300 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if($group['outstanding'] > 0)
                                <a href="{{ route('admin.payments.create', ['student_id' => $group['student']->id]) }}" 
                                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 hover:-translate-y-1 transition-all duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" class="px-6 py-24 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="w-20 h-20 bg-gray-50 rounded-2xl flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-lg font-bold text-gray-900">Data Tidak Ditemukan</p>
                                    <p class="text-sm text-gray-400">Gunakan filter yang berbeda atau cari siswa lain</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-8 py-6 bg-white border-t border-gray-100">
            {{ $paginatedBills->links() }}
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
                    <p class="text-2xl font-bold">Rp {{ number_format($totalBillsCount ?? 0, 0, ',', '.') }}</p>
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
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Total Pembayaran</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
                    <p class="text-green-100 text-xs mt-1">Sudah dibayar</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-xl p-3">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

<!-- Modern Quick Pay Modal -->
<div id="quickPayModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" onclick="closeQuickPayModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-lg transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-cyan-500/5 pointer-events-none"></div>
            <div class="px-8 pt-8 pb-4">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg shadow-emerald-500/20">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Quick Pay</h3>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-0.5">Pembayaran Kilat</p>
                        </div>
                    </div>
                    <button onclick="closeQuickPayModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100/50 text-gray-400 hover:text-gray-900 hover:bg-gray-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="quickPayForm" method="POST" action="{{ route('admin.payments.store') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="bill_id" id="quick_bill_id">
                    <input type="hidden" name="student_id" id="quick_student_id">
                    <input type="hidden" name="payment_date" id="quick_payment_date" value="{{ now()->format('Y-m-d H:i:s') }}">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-100">
                            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Siswa</p>
                            <p class="font-semibold text-gray-900 truncate" id="quick_student_name"></p>
                        </div>
                        <div class="p-5 rounded-2xl bg-cyan-50 border border-cyan-100">
                            <p class="text-xs font-semibold text-cyan-600 uppercase tracking-wider mb-1">Tagihan</p>
                            <p class="font-semibold text-gray-900 truncate" id="quick_bill_detail"></p>
                        </div>
                    </div>

                    <div id="quick_late_fee_display" class="hidden p-5 rounded-2xl bg-rose-50 border border-rose-100">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                            <div>
                                <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Denda Terdeteksi</p>
                                <p class="text-lg font-bold text-rose-700" id="quick_late_fee_amount"></p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Jumlah Pembayaran</label>
                            <div class="relative">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-lg font-bold text-gray-400">Rp</span>
                                <input type="number" name="amount_paid" id="quick_amount" required min="0" step="1000"
                                    class="w-full pl-14 pr-6 py-5 bg-white border-2 border-gray-100 rounded-2xl text-2xl font-bold text-gray-900 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/50 transition-all outline-none">
                            </div>
                            <p class="text-xs font-medium text-gray-400 mt-2 px-1" id="quick_amount_note"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Metode</label>
                                <select name="payment_method" required class="w-full px-6 py-4 bg-white border-2 border-gray-100 rounded-xl text-sm font-bold text-gray-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/50 transition-all cursor-pointer outline-none appearance-none">
                                    <option value="cash">Tunai</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Ref No.</label>
                                <input type="text" name="reference_number" placeholder="Opsional"
                                    class="w-full px-6 py-4 bg-white border-2 border-gray-100 rounded-xl text-sm font-bold text-gray-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/50 transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 pb-8 flex gap-4">
                        <button type="submit" class="flex-1 px-5 py-2.5 bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-xl font-semibold text-sm shadow-lg shadow-emerald-500/30 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300">
                            Konfirmasi & Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modern Batch Pay Modal -->
<div id="batchPayModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" onclick="closeBatchPayModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-lg transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-blue-500/10 to-transparent"></div>
            <div class="px-10 py-10">
                <div class="flex items-center justify-between mb-10">
                    <div class="flex items-center gap-5">
                        <div class="p-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Batch Payment</h3>
                            <p class="text-sm font-bold text-blue-500 uppercase tracking-[0.2em] mt-1">Multi-Tagihan Terintegrasi</p>
                        </div>
                    </div>
                    <button onclick="closeBatchPayModal()" class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-900 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="batchPayForm" method="POST" action="{{ route('admin.payments.batch-store') }}" class="space-y-8">
                    @csrf
                    <input type="hidden" name="bill_ids" id="batch_bill_ids">
                    <input type="hidden" name="student_id" id="batch_student_id">
                    <input type="hidden" name="payment_date" id="batch_payment_date" value="{{ now()->format('Y-m-d H:i:s') }}">
                    
                    <div class="p-6 rounded-2xl bg-gray-50 border border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Identitas Siswa</p>
                        <p class="text-xl font-bold text-gray-900" id="batch_student_name"></p>
                    </div>

                    <div class="space-y-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-2">Rincian Tagihan Terpilih</p>
                        <div id="batch_bills_list" class="space-y-3 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 items-end">
                        <div class="p-8 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg">
                            <p class="text-xs font-semibold text-white/60 uppercase tracking-wider mb-2">Total Akumulasi</p>
                            <p class="text-3xl font-bold tracking-tight" id="batch_total">Rp 0</p>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Metode</label>
                                <select name="payment_method" required class="w-full px-6 py-5 bg-white border-2 border-gray-100 rounded-xl text-sm font-bold text-gray-700 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500/50 transition-all outline-none">
                                    <option value="cash">Tunai</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl font-semibold text-sm shadow-lg shadow-blue-500/20 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300">
                                Proses Batch
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Floating Action Bar for Batch Actions -->
    <div id="batchActionToolbar" class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[80] hidden animate-in fade-in slide-in-from-bottom-8 duration-500">
        <div class="flex items-center gap-2 p-3 bg-gray-900/90 rounded-2xl border border-gray-100 shadow-lg">
            <div class="flex items-center gap-3 px-6 py-3 border-r border-white/10">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2-2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider leading-none mb-1">Terpilih</p>
                    <p id="selectedCountText" class="text-lg font-bold text-white leading-none">0 Item</p>
                </div>
            </div>

            <div class="flex items-center gap-2 px-3">
                <button type="button" id="batchPayBtn" onclick="openBatchPayModal()" 
                        class="hidden flex items-center gap-3 px-5 py-2.5 bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-xl font-semibold text-sm hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Bayar Batch
                </button>

                <button type="button" id="bulkWaiveBtn" onclick="openBulkWaiveModal()" 
                        class="hidden flex items-center gap-3 px-5 py-2.5 bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-xl font-semibold text-sm hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus Biaya
                </button>

                <button type="button" onclick="clearSelection()" 
                        class="p-4 text-gray-500 hover:text-white hover:bg-white/10 rounded-xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

<script>
const monthNames = {
    1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
    5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
    9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
};

// Floating Toolbar elements
const batchActionToolbar = document.getElementById('batchActionToolbar');
const selectedCountText = document.getElementById('selectedCountText');
const batchPayBtn = document.getElementById('batchPayBtn');
const bulkWaiveBtn = document.getElementById('bulkWaiveBtn');

// Selection State
let selectedBills = [];
let selectedRowIndices = [];

function updateToolbar() {
    const totalSelected = selectedBills.length + selectedRowIndices.length;
    
    if (totalSelected > 0) {
        batchActionToolbar.classList.remove('hidden');
        selectedCountText.textContent = `${totalSelected} Item`;
        
        // Show/Hide specific buttons
        if (selectedBills.length > 0) batchPayBtn.classList.remove('hidden');
        else batchPayBtn.classList.add('hidden');
        
        if (selectedRowIndices.length > 0) bulkWaiveBtn.classList.remove('hidden');
        else bulkWaiveBtn.classList.add('hidden');
    } else {
        batchActionToolbar.classList.add('hidden');
    }
}

function clearSelection() {
    selectedBills = [];
    selectedRowIndices = [];
    
    // Uncheck UI
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    
    // Reset Monthly Boxes
    document.querySelectorAll('button[data-bill-id]').forEach(btn => {
        btn.classList.remove('ring-4', 'ring-emerald-500/50', 'scale-110');
    });
    
    updateToolbar();
}

// Monthly Bill Selection (Shift+Click)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('button[data-bill-id]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (e.shiftKey) {
                e.preventDefault();
                e.stopPropagation();
                toggleBillSelection(this);
            }
        });
    });
});

function toggleBillSelection(element) {
    const billId = element.dataset.billId;
    const index = selectedBills.findIndex(b => b.id === billId);
    
    if (index > -1) {
        selectedBills.splice(index, 1);
        element.classList.remove('ring-4', 'ring-emerald-500/50', 'scale-110');
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
        element.classList.add('ring-4', 'ring-emerald-500/50', 'scale-110');
    }
    
    updateToolbar();
}

// Row Checklist Selection
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
        const rowIndex = parseInt(cb.dataset.rowIndex);
        if (this.checked) {
            if (!selectedRowIndices.includes(rowIndex)) selectedRowIndices.push(rowIndex);
        } else {
            const idx = selectedRowIndices.indexOf(rowIndex);
            if (idx > -1) selectedRowIndices.splice(idx, 1);
        }
    });
    updateToolbar();
});

document.querySelectorAll('.row-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const rowIndex = parseInt(this.dataset.rowIndex);
        if (this.checked) {
            if (!selectedRowIndices.includes(rowIndex)) selectedRowIndices.push(rowIndex);
        } else {
            const idx = selectedRowIndices.indexOf(rowIndex);
            if (idx > -1) selectedRowIndices.splice(idx, 1);
            if (document.getElementById('selectAll')) document.getElementById('selectAll').checked = false;
        }
        updateToolbar();
    });
});

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
    
    const totalWithLateFee = amount + lateFee;
    document.getElementById('quick_amount').value = totalWithLateFee;
    
    if (lateFee > 0) {
        document.getElementById('quick_late_fee_display').classList.remove('hidden');
        document.getElementById('quick_late_fee_amount').textContent = `Rp ${lateFee.toLocaleString('id-ID')}`;
        document.getElementById('quick_amount_note').textContent = `Pokok: Rp ${amount.toLocaleString('id-ID')} + Denda: Rp ${lateFee.toLocaleString('id-ID')}`;
    } else {
        document.getElementById('quick_late_fee_display').classList.add('hidden');
        document.getElementById('quick_amount_note').textContent = monthName ? `Tagihan bulan ${monthName}` : 'Tagihan 1 Kali';
    }
    
    document.getElementById('quickPayModal').classList.remove('hidden');
}

function closeQuickPayModal() {
    document.getElementById('quickPayModal').classList.add('hidden');
}

// Batch Payment Logic
function openBatchPayModal() {
    if (selectedBills.length === 0) return;
    
    const studentId = selectedBills[0].studentId;
    const allSameStudent = selectedBills.every(b => b.studentId === studentId);
    
    if (!allSameStudent) {
        alert('Harap pilih tagihan dari siswa yang sama untuk pembayaran batch!');
        return;
    }
    
    document.getElementById('batch_student_name').textContent = selectedBills[0].studentName;
    document.getElementById('batch_student_id').value = studentId;
    document.getElementById('batch_bill_ids').value = selectedBills.map(b => b.id).join(',');
    
    const billsList = document.getElementById('batch_bills_list');
    billsList.innerHTML = '';
    let grandTotal = 0;
    
    selectedBills.forEach(bill => {
        const total = bill.amount + bill.lateFee;
        grandTotal += total;
        
        const monthName = (bill.month && monthNames[bill.month]) ? monthNames[bill.month] : '';
        const detailText = monthName ? `${monthName} ${bill.year}` : `${bill.year}`;
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between p-4 rounded-2xl bg-gray-50 border border-gray-100';
        div.innerHTML = `
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">${bill.paymentType}</p>
                <p class="font-bold text-gray-900">${detailText}</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-gray-900">Rp ${total.toLocaleString('id-ID')}</p>
                ${bill.lateFee > 0 ? '<p class="text-[10px] font-bold text-rose-500">Termasuk Denda</p>' : ''}
            </div>
        `;
        billsList.appendChild(div);
    });
    
    document.getElementById('batch_total').textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
    document.getElementById('batchPayModal').classList.remove('hidden');
}

function closeBatchPayModal() {
    document.getElementById('batchPayModal').classList.add('hidden');
}

// Bulk Waive Functions
function openBulkWaiveModal() {
    if (selectedRowIndices.length === 0) return;
    
    const billsWithLateFees = [];
    const currentItems = @json($paginatedBills->items());
    
    selectedRowIndices.forEach(idx => {
        const realGroup = currentItems[idx];
        if (realGroup) {
            const billsToCheck = [];
            if (realGroup.monthly_data) {
                Object.values(realGroup.monthly_data).forEach(bill => {
                    if (bill) billsToCheck.push(bill);
                });
            }
            if (realGroup.first_bill && !billsToCheck.some(b => b.id === realGroup.first_bill.id)) {
                billsToCheck.push(realGroup.first_bill);
            }
            
            billsToCheck.forEach(bill => {
                if (bill && bill.status !== 'lunas' && bill.late_fee > 0 && !bill.late_fee_waived) {
                    billsWithLateFees.push({
                        id: bill.id,
                        student_name: realGroup.student.full_name,
                        payment_type: realGroup.payment_type.type_name,
                        month: bill.month,
                        year: bill.year,
                        late_fee: bill.late_fee
                    });
                }
            });
        }
    });

    if (billsWithLateFees.length === 0) {
        alert('Tidak ada tagihan dengan denda yang dapat dihapus pada baris terpilih.');
        return;
    }

    const waiveList = document.getElementById('waive_bills_list');
    waiveList.innerHTML = '';
    let totalWaived = 0;

    billsWithLateFees.forEach(bill => {
        totalWaived += parseFloat(bill.late_fee);
        
        const monthName = (bill.month && monthNames[bill.month]) ? monthNames[bill.month] : '';
        const detailText = monthName ? `${bill.payment_type} - ${monthName} ${bill.year}` : `${bill.payment_type} - ${bill.year}`;
        
        const div = document.createElement('div');
        div.className = 'flex justify-between items-center py-3 border-b border-gray-100 last:border-0';
        div.innerHTML = `
            <div>
                <p class="font-bold text-gray-900">${bill.student_name}</p>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">${detailText}</p>
            </div>
            <p class="font-bold text-rose-600">Rp ${parseFloat(bill.late_fee).toLocaleString('id-ID')}</p>
        `;
        waiveList.appendChild(div);
    });

    document.getElementById('waive_total_late_fee').textContent = `Rp ${totalWaived.toLocaleString('id-ID')}`;
    document.getElementById('waive_bill_ids').value = billsWithLateFees.map(b => b.id).join(',');
    document.getElementById('waive_count').textContent = billsWithLateFees.length;
    document.getElementById('bulkWaiveModal').classList.remove('hidden');
}

function closeBulkWaiveModal() {
    document.getElementById('bulkWaiveModal').classList.add('hidden');
}

function submitBulkWaive() {
    const reason = document.getElementById('waive_reason').value.trim();
    if (!reason) { alert('Harap isi alasan penghapusan biaya!'); return; }
    
    const billIds = document.getElementById('waive_bill_ids').value.split(',');
    const btn = document.getElementById('waive_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">Memproses...</span>';

    fetch('{{ route('admin.bills.bulk-waive') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ bill_ids: billIds, reason: reason })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else { alert(data.message); btn.disabled = false; btn.textContent = 'Konfirmasi Penghapusan'; }
    });
}

// Global ESC key listener
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeQuickPayModal();
        closeBatchPayModal();
        closeBulkWaiveModal();
    }
});
</script>

<!-- Modern Bulk Waive Modal -->
<div id="bulkWaiveModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" onclick="closeBulkWaiveModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-lg transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-amber-500/10 to-transparent"></div>
            <div class="px-10 py-10">
                <div class="flex items-center justify-between mb-10">
                    <div class="flex items-center gap-5">
                        <div class="p-4 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Bulk Waive</h3>
                            <p class="text-sm font-bold text-amber-500 uppercase tracking-[0.2em] mt-1">Penghapusan Biaya Admin</p>
                        </div>
                    </div>
                    <button onclick="closeBulkWaiveModal()" class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-900 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-8">
                    <input type="hidden" id="waive_bill_ids">
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rincian Penghapusan (<span id="waive_count">0</span> Tagihan)</p>
                        </div>
                        <div id="waive_bills_list" class="space-y-3 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <div class="p-8 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-1">Total Dihapus</p>
                                <p class="text-3xl font-bold text-amber-700 tracking-tight" id="waive_total_late_fee">Rp 0</p>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider px-2">Alasan Penghapusan <span class="text-rose-500">*</span></label>
                        <textarea id="waive_reason" rows="3" 
                            class="w-full px-8 py-6 bg-white border-2 border-gray-100 rounded-xl text-gray-700 font-medium focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500/50 transition-all outline-none resize-none"
                            placeholder="Tuliskan alasan profesional..."></textarea>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" onclick="closeBulkWaiveModal()" class="flex-1 py-2.5 bg-gray-100 text-gray-500 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-all">
                            Batal
                        </button>
                        <button type="button" id="waive_submit_btn" onclick="submitBulkWaive()" 
                                class="flex-[2] py-2.5 bg-gradient-to-r from-amber-600 to-orange-700 text-white rounded-xl font-semibold text-sm shadow-lg shadow-amber-500/20 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300">
                            Konfirmasi & Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modern Bulk Update Modal -->
<div id="bulkUpdateModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" onclick="closeBulkUpdateModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative z-50 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-indigo-500/10 to-transparent"></div>
            <div class="px-10 py-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-5">
                        <div class="p-4 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Ubah Nominal Massal</h3>
                            <p class="text-sm font-bold text-indigo-500 uppercase tracking-[0.2em] mt-1">Perbarui Nominal Tagihan Siswa</p>
                        </div>
                    </div>
                    <button onclick="closeBulkUpdateModal()" class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-900 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <input type="hidden" id="bulk_update_use_selected" value="0">

                    <div id="bulkUpdateSelectedNotice" class="hidden p-4 bg-indigo-50 border border-indigo-200 rounded-2xl text-sm font-semibold text-indigo-800">
                        📌 Mengubah nominal untuk <strong>tagihan terpilih dari tabel</strong> yang belum lunas.
                    </div>

                    <div id="bulkUpdateFilterNotice" class="space-y-4">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Filter Target Tagihan</p>
                        
                        @if(auth()->user()->isSuperAdmin())
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Sekolah Target</label>
                            <select id="bulk_update_school_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500">
                                <option value="">Semua Sekolah</option>
                                @foreach($allSchools as $school)
                                <option value="{{ $school->id }}" {{ session('bills_filter_school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" id="bulk_update_school_id" value="{{ auth()->user()->school_id }}">
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Ajaran</label>
                                <select id="bulk_update_academic_year_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Semua Tahun Ajaran</option>
                                    @foreach($allAcademicYears as $ay)
                                    <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>{{ $ay->year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Tagihan</label>
                                <select id="bulk_update_payment_type_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Semua Jenis Tagihan</option>
                                    @foreach($allPaymentTypes as $pt)
                                    <option value="{{ $pt->id }}">{{ $pt->type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas Target</label>
                            <select id="bulk_update_classroom_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500">
                                <option value="">Semua Kelas</option>
                                @foreach($allClassrooms as $c)
                                <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="p-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100 space-y-2">
                        <label class="block text-sm font-bold text-gray-800">Nominal Tagihan Baru (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" id="bulk_new_amount" placeholder="Misal: 150000" min="0" step="1000"
                            class="w-full px-5 py-4 bg-white border-2 border-indigo-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 font-bold text-gray-900 text-xl outline-none">
                        <p class="text-xs text-gray-500">Nominal tagihan baru akan diterapkan pada tagihan yang belum dibayar / belum lunas.</p>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="button" onclick="closeBulkUpdateModal()" class="flex-1 py-3.5 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-all">
                            Batal
                        </button>
                        <button type="button" id="bulk_update_submit_btn" onclick="submitBulkUpdate()" class="flex-[2] py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-sm shadow-lg shadow-indigo-500/20 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300">
                            Simpan & Perbarui Nominal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modern Bulk Delete Modal -->
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" onclick="closeBulkDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative z-50 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-rose-500/10 to-transparent"></div>
            <div class="px-10 py-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-5">
                        <div class="p-4 bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Hapus Tagihan Massal</h3>
                            <p class="text-sm font-bold text-rose-500 uppercase tracking-[0.2em] mt-1">Pembersihan Tagihan Belum Dibayar</p>
                        </div>
                    </div>
                    <button onclick="closeBulkDeleteModal()" class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-900 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <input type="hidden" id="bulk_delete_use_selected" value="0">

                    <div id="bulkDeleteSelectedNotice" class="hidden p-4 bg-rose-50 border border-rose-200 rounded-2xl text-sm font-semibold text-rose-800">
                        ⚠️ Menghapus <strong>tagihan terpilih dari tabel</strong> yang belum memiliki riwayat pembayaran.
                    </div>

                    <div id="bulkDeleteFilterNotice" class="space-y-4">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Target Tagihan yang Akan Dihapus</p>
                        
                        @if(auth()->user()->isSuperAdmin())
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Sekolah Target</label>
                            <select id="bulk_delete_school_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500">
                                <option value="">Semua Sekolah</option>
                                @foreach($allSchools as $school)
                                <option value="{{ $school->id }}" {{ session('bills_filter_school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" id="bulk_delete_school_id" value="{{ auth()->user()->school_id }}">
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Ajaran</label>
                                <select id="bulk_delete_academic_year_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500">
                                    <option value="">Semua Tahun Ajaran</option>
                                    @foreach($allAcademicYears as $ay)
                                    <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>{{ $ay->year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Tagihan</label>
                                <select id="bulk_delete_payment_type_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500">
                                    <option value="">Semua Jenis Tagihan</option>
                                    @foreach($allPaymentTypes as $pt)
                                    <option value="{{ $pt->id }}">{{ $pt->type_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas Target</label>
                            <select id="bulk_delete_classroom_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500">
                                <option value="">Semua Kelas</option>
                                @foreach($allClassrooms as $c)
                                <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="p-5 bg-amber-50 border-l-4 border-amber-500 rounded-2xl text-xs text-amber-800 space-y-1">
                        <p class="font-bold text-sm text-amber-900">🛡️ Proteksi Keamanan Data</p>
                        <p>Tagihan yang <em>sudah pernah dibayar / cicilan</em> secara otomatis akan DILEWATI dan TIDAK AKAN DIHAPUS demi menjaga integritas data keuangan.</p>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="button" onclick="closeBulkDeleteModal()" class="flex-1 py-3.5 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-all">
                            Batal
                        </button>
                        <button type="button" id="bulk_delete_submit_btn" onclick="submitBulkDelete()" class="flex-[2] py-3.5 bg-gradient-to-r from-rose-600 to-red-700 text-white rounded-xl font-semibold text-sm shadow-lg shadow-rose-500/20 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300">
                            Ya, Hapus Tagihan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openBulkUpdateModal(useSelected = false) {
    const modal = document.getElementById('bulkUpdateModal');
    const selectedNotice = document.getElementById('bulkUpdateSelectedNotice');
    const filterNotice = document.getElementById('bulkUpdateFilterNotice');
    
    if (useSelected) {
        selectedNotice.classList.remove('hidden');
        filterNotice.classList.add('hidden');
    } else {
        selectedNotice.classList.add('hidden');
        filterNotice.classList.remove('hidden');
    }
    document.getElementById('bulk_update_use_selected').value = useSelected ? '1' : '0';
    modal.classList.remove('hidden');
}

function closeBulkUpdateModal() {
    document.getElementById('bulkUpdateModal').classList.add('hidden');
}

function submitBulkUpdate() {
    const newAmount = document.getElementById('bulk_new_amount').value;
    if (!newAmount || parseFloat(newAmount) < 0) {
        alert('Harap masukkan nominal tagihan baru yang valid!');
        return;
    }
    const useSelected = document.getElementById('bulk_update_use_selected').value === '1';
    
    let payload = {
        new_amount: newAmount,
        _token: '{{ csrf_token() }}'
    };

    if (useSelected) {
        let ids = getSelectedBillIds();
        if (ids.length === 0) { alert('Tidak ada tagihan yang dipilih!'); return; }
        payload.bill_ids = ids;
    } else {
        payload.school_id = document.getElementById('bulk_update_school_id') ? document.getElementById('bulk_update_school_id').value : '';
        payload.academic_year_id = document.getElementById('bulk_update_academic_year_id').value;
        payload.payment_type_id = document.getElementById('bulk_update_payment_type_id').value;
        payload.classroom_id = document.getElementById('bulk_update_classroom_id').value;
    }

    const btn = document.getElementById('bulk_update_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">Memproses...</span>';

    fetch('{{ route('admin.bills.bulk-update-amount') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan.');
            btn.disabled = false;
            btn.textContent = 'Simpan & Perbarui Nominal';
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.textContent = 'Simpan & Perbarui Nominal';
    });
}

function openBulkDeleteModal(useSelected = false) {
    const modal = document.getElementById('bulkDeleteModal');
    const selectedNotice = document.getElementById('bulkDeleteSelectedNotice');
    const filterNotice = document.getElementById('bulkDeleteFilterNotice');

    if (useSelected) {
        selectedNotice.classList.remove('hidden');
        filterNotice.classList.add('hidden');
    } else {
        selectedNotice.classList.add('hidden');
        filterNotice.classList.remove('hidden');
    }
    document.getElementById('bulk_delete_use_selected').value = useSelected ? '1' : '0';
    modal.classList.remove('hidden');
}

function closeBulkDeleteModal() {
    document.getElementById('bulkDeleteModal').classList.add('hidden');
}

function submitBulkDelete() {
    const useSelected = document.getElementById('bulk_delete_use_selected').value === '1';
    let payload = {
        _token: '{{ csrf_token() }}'
    };

    if (useSelected) {
        let ids = getSelectedBillIds();
        if (ids.length === 0) { alert('Tidak ada tagihan yang dipilih!'); return; }
        payload.bill_ids = ids;
    } else {
        payload.school_id = document.getElementById('bulk_delete_school_id') ? document.getElementById('bulk_delete_school_id').value : '';
        payload.academic_year_id = document.getElementById('bulk_delete_academic_year_id').value;
        payload.payment_type_id = document.getElementById('bulk_delete_payment_type_id').value;
        payload.classroom_id = document.getElementById('bulk_delete_classroom_id').value;
    }

    if (!confirm('Apakah Anda yakin ingin menghapus tagihan yang belum dibayar sesuai kriteria ini? Tindakan ini tidak dapat dibatalkan.')) {
        return;
    }

    const btn = document.getElementById('bulk_delete_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">Memproses Hapus...</span>';

    fetch('{{ route('admin.bills.bulk-delete') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan.');
            btn.disabled = false;
            btn.textContent = 'Ya, Hapus Tagihan';
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.textContent = 'Ya, Hapus Tagihan';
    });
}

function getSelectedBillIds() {
    let ids = [];
    document.querySelectorAll('.bill-checkbox:checked').forEach(cb => {
        if (cb.dataset.billId) ids.push(parseInt(cb.dataset.billId));
    });
    return ids;
}
</script>

@endsection
