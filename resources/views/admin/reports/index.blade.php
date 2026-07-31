@extends('layouts.admin')

@section('title', 'Laporan Rekap Status Tagihan Siswa')

@section('content')
<div class="space-y-8 pb-12">
    <!-- Page Header -->
    <div class="bg-white border border-slate-100 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-600 text-white shadow-lg shadow-emerald-500/20 shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Laporan Rekap Status Tagihan</h1>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Monitoring status pembayaran siswa secara komprehensif per bulan</p>
                </div>
            </div>
            
            <a href="{{ route('admin.payment_reports.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20 hover:from-emerald-700 hover:to-teal-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Form Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 space-y-6">
        <div class="flex items-center gap-2.5 pb-4 border-b border-slate-100">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Filter Parameter Laporan</h2>
        </div>

        <form method="GET" action="{{ route('admin.payment_reports.index') }}" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @if($isSuperAdmin)
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Sekolah</label>
                    <div class="relative">
                        <select name="school_id" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer" onchange="this.form.submit()">
                            <option value="">Semua Sekolah</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
                @endif

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Jenis Tagihan</label>
                    <div class="relative">
                        <select name="payment_type_id" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Jenis Tagihan</option>
                            @foreach($paymentTypes as $type)
                            <option value="{{ $type->id }}" {{ $paymentTypeId == $type->id ? 'selected' : '' }}>{{ $type->type_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Tahun Ajaran</label>
                    <div class="relative">
                        <select name="academic_year_id" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                            @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>{{ $ay->year }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Kelas</label>
                    <div class="relative">
                        <select name="classroom_id" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Kelas</option>
                            @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" {{ $classroomId == $classroom->id ? 'selected' : '' }}>{{ $classroom->class_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Periode Evaluasi</label>
                    <div class="relative">
                        <select name="period_type" id="periodType" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer" onchange="togglePeriodFields()">
                            <option value="yearly" {{ $periodType == 'yearly' ? 'selected' : '' }}>Tahunan Full (12 Bulan)</option>
                            <option value="ytd"    {{ $periodType == 'ytd'    ? 'selected' : '' }}>Sampai Bulan Ini (YTD)</option>
                            <option value="month"  {{ $periodType == 'month'  ? 'selected' : '' }}>Bulan Spesifik</option>
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div id="monthField" style="{{ $periodType != 'month' ? 'display:none' : '' }}" class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Pilih Bulan</label>
                    <div class="relative">
                        <select name="month" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div id="yearField" style="{{ $periodType != 'month' ? 'display:none' : '' }}" class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Pilih Tahun Kalender</label>
                    <div class="relative">
                        <select name="year" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                            @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="show_all" id="showAll" value="1" {{ $showAll ? 'checked' : '' }}
                           class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                    <span class="text-xs font-semibold text-slate-700">Tampilkan siswa non-aktif (pindah / lulus / keluar)</span>
                </label>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.payment_reports.index') }}" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all">Reset</a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-emerald-600 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20 hover:bg-emerald-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Tampilkan Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Item Tagihan</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ number_format($totalBills, 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Record tagihan terdaftar</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-slate-100 text-slate-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Target Nominal</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">Rp {{ number_format($totalAmount, 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Seluruh tagihan terdata</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Total Terbayar</p>
                    <h3 class="text-2xl font-extrabold text-emerald-600 tracking-tight">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h3>
                    @if($totalAmount > 0)
                    <p class="text-[11px] font-bold text-emerald-700">{{ number_format(($totalPaid / $totalAmount) * 100, 1) }}% Tercapai</p>
                    @endif
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-rose-600 uppercase tracking-wider">Total Tunggakan</p>
                    <h3 class="text-2xl font-extrabold text-rose-600 tracking-tight">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h3>
                    @if($totalAmount > 0)
                    <p class="text-[11px] font-bold text-rose-700">{{ number_format(($totalOutstanding / $totalAmount) * 100, 1) }}% Belum Terbayar</p>
                    @endif
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filter Info Chips -->
    @if($selectedPaymentType || $selectedSchool || $selectedClassroom || $selectedAcademicYear)
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px]">Filter Aktif:</span>
            @if($selectedSchool)
            <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg font-bold border border-blue-200">🏫 {{ $selectedSchool->name }}</span>
            @endif
            @if($selectedAcademicYear)
            <span class="inline-flex items-center px-2.5 py-1 bg-purple-100 text-purple-800 rounded-lg font-bold border border-purple-200">📅 TP {{ $selectedAcademicYear->year }}</span>
            @endif
            @if($selectedClassroom)
            <span class="inline-flex items-center px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg font-bold border border-amber-200">📚 {{ $selectedClassroom->class_name }}</span>
            @endif
            @if($selectedPaymentType)
            <span class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-bold border border-emerald-200">💳 {{ $selectedPaymentType->type_name }}</span>
            @endif
            <span class="inline-flex items-center px-2.5 py-1 bg-slate-200 text-slate-700 rounded-lg font-bold">
                {{ $periodType == 'yearly' ? 'Full Tahun' : ($periodType == 'month' ? 'Bulan '.$month.'/'.$year : 'YTD s/d Bulan '.$month) }}
            </span>
        </div>
        <span class="font-bold text-slate-900">{{ number_format($studentsData->count(), 0, ',', '.') }} siswa ditemukan</span>
    </div>
    @endif

    <!-- Matrix Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <!-- Table Header & Legend -->
        <div class="p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-600 rounded-lg text-white shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tabel Matrix Rekap Siswa × Bulan (Jul–Jun)</h3>
            </div>

            <!-- Legend Bar -->
            <div class="flex items-center gap-4 text-xs flex-wrap font-semibold">
                <div class="flex items-center gap-1.5">
                    <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">✓</span>
                    <span class="text-slate-700">Lunas</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">◐</span>
                    <span class="text-slate-700">Cicilan</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">✕</span>
                    <span class="text-slate-700">Belum Bayar</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-300 font-bold text-sm">—</span>
                    <span class="text-slate-400">Tidak Ada Tagihan</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm min-w-[950px] border-separate border-spacing-0">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-3 py-4 text-center sticky left-0 bg-slate-50 z-10 text-xs font-bold text-slate-600 uppercase tracking-wider w-10 border-r border-slate-200">No</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider min-w-[170px]">Nama Siswa</th>
                        @if($isSuperAdmin)
                        <th class="px-4 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Sekolah</th>
                        @endif
                        <th class="px-4 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Kelas</th>
                        @foreach(['Jul','Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun'] as $mon)
                        <th class="px-2 py-4 text-center text-xs font-bold {{ $mon == 'Jul' ? 'border-l-2 border-slate-200 text-indigo-700' : 'text-slate-600' }} uppercase tracking-wider w-9">{{ $mon }}</th>
                        @endforeach
                        <th class="px-4 py-4 text-right border-l-2 border-slate-200 text-xs font-bold text-blue-700 uppercase tracking-wider min-w-[120px]">Target Tagihan</th>
                        <th class="px-4 py-4 text-right text-xs font-bold text-emerald-700 uppercase tracking-wider min-w-[110px]">Terbayar</th>
                        <th class="px-4 py-4 text-right text-xs font-bold text-rose-700 uppercase tracking-wider min-w-[110px]">Tunggakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $no = 0; @endphp
                    @forelse($studentsData as $studentData)
                    @php
                        $no++;
                        $student      = $studentData['student'];
                        $classroom    = $studentData['classroom'];
                        $monthlyBills = $studentData['monthly_bills'];
                        $monthOrder   = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
                        $isInactive   = $student->status != 'aktif';
                        $statusColors = [
                            'lulus'  => 'bg-blue-50 text-blue-700 border-blue-200',
                            'pindah' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'keluar' => 'bg-rose-50 text-rose-700 border-rose-200',
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $isInactive ? 'bg-slate-50/60' : '' }}">
                        <td class="px-3 py-3.5 text-center text-xs text-slate-500 font-bold sticky left-0 {{ $isInactive ? 'bg-slate-50' : 'bg-white' }} border-r border-slate-200">{{ $no }}</td>
                        <td class="px-4 py-3.5 align-middle">
                            <p class="font-bold text-slate-900 text-xs leading-tight">{{ $student->full_name }}</p>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mt-0.5">{{ $student->nisn ?? 'NISN -' }}</p>
                            @if($isInactive)
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold border {{ $statusColors[$student->status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ ucfirst($student->status) }}
                            </span>
                            @endif
                        </td>
                        @if($isSuperAdmin)
                        <td class="px-4 py-3.5 text-xs font-semibold text-slate-600 align-middle">{{ $student->school?->name ?? '-' }}</td>
                        @endif
                        <td class="px-4 py-3.5 text-xs text-slate-700 font-bold align-middle">{{ $classroom?->class_name ?? '-' }}</td>

                        @foreach($monthOrder as $m)
                        @php
                            $hasBill = isset($monthlyBills[$m]);
                            $status  = $hasBill ? $monthlyBills[$m]['status'] : null;
                            $isPaid  = $status == 'lunas';
                            $isPartial = $status == 'cicilan';
                        @endphp
                        <td class="px-2 py-3.5 text-center align-middle {{ $m == 7 ? 'border-l-2 border-slate-200' : '' }}">
                            @if($hasBill)
                                @if($isPaid)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-emerald-500 text-white font-bold text-xs shadow-sm" title="Lunas">✓</span>
                                @elseif($isPartial)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-amber-500 text-white font-bold text-xs shadow-sm" title="Cicilan">◐</span>
                                @else
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-rose-500 text-white font-bold text-xs shadow-sm" title="Belum Bayar">✕</span>
                                @endif
                            @else
                                <span class="text-slate-300 font-bold">—</span>
                            @endif
                        </td>
                        @endforeach

                        <td class="px-4 py-3.5 text-right border-l-2 border-slate-100 align-middle">
                            <span class="text-xs font-extrabold text-blue-700">Rp {{ number_format($studentData['total_amount'], 0, ',', '.') }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right align-middle">
                            <span class="text-xs font-extrabold text-emerald-600">Rp {{ number_format($studentData['total_paid'], 0, ',', '.') }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right align-middle">
                            @if($studentData['total_outstanding'] > 0)
                            <span class="text-xs font-extrabold text-rose-600">Rp {{ number_format($studentData['total_outstanding'], 0, ',', '.') }}</span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                Lunas ✓
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="20" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Data Laporan Tidak Ditemukan</p>
                                    <p class="text-xs text-slate-400 mt-1">Gunakan parameter filter yang sesuai lalu klik <strong>Tampilkan Laporan</strong>.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($studentsData->isNotEmpty())
                <tfoot class="bg-slate-100 border-t-2 border-slate-200">
                    <tr>
                        <td colspan="{{ $isSuperAdmin ? 4 : 3 }}" class="px-4 py-4 text-xs font-extrabold text-slate-800 text-right uppercase tracking-wider">
                            TOTAL ({{ number_format($studentsData->count(), 0, ',', '.') }} Siswa)
                        </td>
                        @foreach(array_fill(0, 12, null) as $_)
                        <td></td>
                        @endforeach
                        <td class="px-4 py-4 text-right border-l-2 border-slate-300 font-extrabold text-blue-800 text-xs">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right font-extrabold text-emerald-700 text-xs">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right font-extrabold text-rose-700 text-xs">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<script>
function togglePeriodFields() {
    const periodType = document.getElementById('periodType').value;
    const monthField = document.getElementById('monthField');
    const yearField  = document.getElementById('yearField');
    if (periodType === 'month') {
        monthField.style.display = 'block';
        yearField.style.display  = 'block';
    } else {
        monthField.style.display = 'none';
        yearField.style.display  = 'none';
    }
}
</script>
@endsection
