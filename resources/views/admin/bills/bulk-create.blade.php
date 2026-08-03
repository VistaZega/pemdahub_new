@extends('layouts.admin')

@section('title', 'Buat Tagihan Massal')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 pb-12">
    <!-- Header Section -->
    <div class="bg-white border border-slate-100 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.bills.index') }}" 
                   class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 transition-all shrink-0"
                   title="Kembali ke Daftar Tagihan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md shadow-emerald-500/20 text-white shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Buat Tagihan Massal</h1>
                        <p class="text-xs font-semibold text-slate-500 mt-1">Generasi tagihan untuk banyak siswa sekaligus secara otomatis</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Mode Batch Generation
                </span>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="p-5 bg-rose-50 border-l-4 border-rose-500 rounded-2xl shadow-sm">
        <div class="flex items-center gap-3 mb-2">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-sm font-bold text-rose-800">Terdapat kesalahan pengisian form:</h3>
        </div>
        <ul class="list-disc list-inside text-xs font-semibold text-rose-700 space-y-1 ml-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.bills.bulk-store') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Section 1: Target Siswa -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-600 text-white font-extrabold text-sm shadow-sm">1</div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Target Penerima Tagihan</h2>
                    <p class="text-xs text-slate-400 font-medium">Pilih sekolah dan jangkauan siswa penerima tagihan</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Sekolah Select -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Sekolah Target <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="school_id" id="school_id" required 
                            class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer"
                            onchange="updateClassroomOptions(); updateStudentCount();">
                            <option value="">-- Pilih Sekolah Target --</option>
                            @foreach($schools as $school)
                            @if(!str_contains(strtolower($school->name), 'yayasan') && strtolower($school->type) !== 'yayasan')
                            <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endif
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Filter Siswa Radio Cards -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Jangkauan Filter Siswa <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <label class="relative flex items-start p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:bg-emerald-50/50 hover:border-emerald-400 transition-all group">
                            <input type="radio" name="filter_by" value="all" {{ old('filter_by', 'all') == 'all' ? 'checked' : '' }} required
                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 mt-0.5 cursor-pointer"
                                onchange="toggleFilterOptions()">
                            <div class="ml-3">
                                <span class="font-bold text-slate-900 text-xs block group-hover:text-emerald-700 transition-colors">Semua Siswa</span>
                                <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Seluruh siswa aktif</span>
                            </div>
                        </label>

                        <label class="relative flex items-start p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:bg-emerald-50/50 hover:border-emerald-400 transition-all group">
                            <input type="radio" name="filter_by" value="classroom" {{ old('filter_by') == 'classroom' ? 'checked' : '' }} required
                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 mt-0.5 cursor-pointer"
                                onchange="toggleFilterOptions()">
                            <div class="ml-3">
                                <span class="font-bold text-slate-900 text-xs block group-hover:text-emerald-700 transition-colors">Per Kelas</span>
                                <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Pilih 1 kelas spesifik</span>
                            </div>
                        </label>

                        <label class="relative flex items-start p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:bg-emerald-50/50 hover:border-emerald-400 transition-all group">
                            <input type="radio" name="filter_by" value="grade" {{ old('filter_by') == 'grade' ? 'checked' : '' }} required
                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 mt-0.5 cursor-pointer"
                                onchange="toggleFilterOptions()">
                            <div class="ml-3">
                                <span class="font-bold text-slate-900 text-xs block group-hover:text-emerald-700 transition-colors">Per Tingkat</span>
                                <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Kelas 7, 8, atau 10</span>
                            </div>
                        </label>

                        <label class="relative flex items-start p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:bg-emerald-50/50 hover:border-emerald-400 transition-all group">
                            <input type="radio" name="filter_by" value="class_type" {{ old('filter_by') == 'class_type' ? 'checked' : '' }} required
                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 mt-0.5 cursor-pointer"
                                onchange="toggleFilterOptions()">
                            <div class="ml-3">
                                <span class="font-bold text-slate-900 text-xs block group-hover:text-emerald-700 transition-colors">Per Tipe Kelas</span>
                                <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Industri, Reguler, dll</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Conditional Filter: Kelas -->
                <div id="classroom_filter" style="display: none;" class="space-y-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Pilih Kelas Spesifik <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="classroom_id" id="classroom_id" 
                            class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer"
                            onchange="updateStudentCount()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" data-school="{{ $classroom->school_id }}" data-academic-year="{{ $classroom->academic_year_id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                {{ $classroom->class_name }} - {{ $classroom->school->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Conditional Filter: Tingkat -->
                <div id="grade_filter" style="display: none;" class="space-y-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Pilih Tingkat Kelas <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="grade_level" id="grade_level"
                            class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer"
                            onchange="updateStudentCount()">
                            <option value="">-- Pilih Tingkat --</option>
                            <optgroup label="SMP / MTs">
                                <option value="7" {{ old('grade_level') == '7' ? 'selected' : '' }}>Kelas VII (7)</option>
                                <option value="8" {{ old('grade_level') == '8' ? 'selected' : '' }}>Kelas VIII (8)</option>
                                <option value="9" {{ old('grade_level') == '9' ? 'selected' : '' }}>Kelas IX (9)</option>
                            </optgroup>
                            <optgroup label="SMA / SMK / MA">
                                <option value="10" {{ old('grade_level') == '10' ? 'selected' : '' }}>Kelas X (10)</option>
                                <option value="11" {{ old('grade_level') == '11' ? 'selected' : '' }}>Kelas XI (11)</option>
                                <option value="12" {{ old('grade_level') == '12' ? 'selected' : '' }}>Kelas XII (12)</option>
                            </optgroup>
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Conditional Filter: Tipe Kelas -->
                <div id="class_type_filter" style="display: none;" class="space-y-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Pilih Tipe Kelas <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="class_type" id="class_type"
                            class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer"
                            onchange="updateStudentCount()">
                            <option value="">-- Pilih Tipe Kelas --</option>
                            @foreach($classTypes as $type)
                                <option value="{{ $type }}" {{ old('class_type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Estimation Banner -->
                <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-blue-900">Estimasi Target:</p>
                        <p class="text-xs font-semibold text-blue-700" id="student_count">Pilih sekolah dan filter untuk melihat deskripsi target</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Detail Tagihan -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-600 text-white font-extrabold text-sm shadow-sm">2</div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Spesifikasi Tagihan</h2>
                    <p class="text-xs text-slate-400 font-medium">Tentukan jenis tagihan, periode, nominal, dan tanggal jatuh tempo</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Tahun Pelajaran & Jenis Tagihan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"/>
                            </svg>
                            Tahun Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="academic_year_id" id="academic_year_id" required 
                                class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer" 
                                onchange="updateClassroomOptions(); updateStudentCount();">
                                <option value="">-- Pilih Tahun Pelajaran --</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $activeYear->id ?? '') == $year->id ? 'selected' : '' }}>
                                    TP. {{ $year->year }} {{ $year->is_active ? '(Aktif)' : '' }}
                                </option>
                                @endforeach
                            </select>
                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Jenis Pembayaran / Tagihan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="payment_type_id" id="payment_type_id" required 
                                class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer" 
                                onchange="updateAmounts()">
                                <option value="">-- Pilih Jenis Tagihan --</option>
                                @foreach($paymentTypes as $type)
                                <option value="{{ $type->id }}" 
                                    data-amount="{{ $type->amount }}"
                                    data-yayasan-share="{{ $type->yayasan_share_amount }}"
                                    {{ old('payment_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->type_code }} - {{ $type->type_name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sifat Tagihan Radio -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Sifat Tagihan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-center p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:bg-emerald-50/50 hover:border-emerald-400 transition-all group">
                            <input type="radio" name="billing_type" value="single" checked onchange="toggleBillingType()"
                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            <span class="ml-3 font-bold text-slate-900 text-xs group-hover:text-emerald-700 transition-colors">1 Kali Saja (Sekali Bayar)</span>
                        </label>
                        <label class="flex items-center p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:bg-emerald-50/50 hover:border-emerald-400 transition-all group">
                            <input type="radio" name="billing_type" value="monthly" onchange="toggleBillingType()"
                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            <span class="ml-3 font-bold text-slate-900 text-xs group-hover:text-emerald-700 transition-colors">Bulanan Berulang (SPP)</span>
                        </label>
                    </div>
                </div>

                <!-- Options for Monthly Billing -->
                <div id="monthly_options" style="display: none;" class="space-y-4 pt-2">
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-blue-900 leading-relaxed">
                            <span class="font-bold">Mode SPP Bulanan:</span> Sistem akan men-generate tagihan bulanan berurut sekaligus dengan tanggal jatuh tempo yang seragam di setiap bulannya.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700">Bulan Mulai</label>
                            <div class="relative">
                                <select name="start_month" id="start_month" class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
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
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700">Jumlah Bulan</label>
                            <div class="relative">
                                <select name="generate_months" id="generate_months" class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                                    <option value="1">1 Bulan</option>
                                    <option value="2">2 Bulan</option>
                                    <option value="3">3 Bulan</option>
                                    <option value="6">6 Bulan (Semester)</option>
                                    <option value="12" selected>12 Bulan (1 Tahun)</option>
                                </select>
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700">Tanggal Jatuh Tempo</label>
                            <div class="relative">
                                <select name="due_day" id="due_day" class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                                    @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ $i == 10 ? 'selected' : '' }}>Tanggal {{ $i }} Setiap Bulan</option>
                                    @endfor
                                </select>
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Date & Nominal Field -->
                <div id="single_date_field" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">Deskripsi / Keterangan Tagihan</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                            placeholder="Contoh: Biaya Pendaftaran / Biaya Ujian 2026"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700">Nominal Per Siswa (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="amount" value="{{ old('amount') }}" required min="0" step="1000"
                                placeholder="500000"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                            <p class="text-[11px] font-medium text-slate-400">Nominal yang sama untuk setiap siswa</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700">Setoran Yayasan (Rp)</label>
                            <input type="number" name="yayasan_share_amount" value="{{ old('yayasan_share_amount') }}" min="0" step="1000"
                                placeholder="0"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                            <p class="text-[11px] font-medium text-slate-400">Nominal hak Yayasan (Opsional)</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700">Bulan Jatuh Tempo</label>
                            <div class="relative">
                                <select name="single_month" class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
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
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700">Tanggal Jatuh Tempo</label>
                            <div class="relative">
                                <select name="single_day" class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                                    @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ $i == 15 ? 'selected' : '' }}>Tanggal {{ $i }}</option>
                                    @endfor
                                </select>
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                  <!-- Monthly Details -->
                  <div id="monthly_details" style="display: none;" class="space-y-5">
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                          <div class="space-y-2">
                              <label class="text-xs font-bold text-slate-700">Nominal Per Bulan (Rp) <span class="text-rose-500">*</span></label>
                              <input type="number" name="monthly_amount" value="{{ old('monthly_amount') }}" min="0" step="1000"
                                  placeholder="500000"
                                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                              <p class="text-[11px] font-medium text-slate-400">Nominal SPP yang ditagihkan setiap bulan</p>
                          </div>
                          
                          <div class="space-y-2">
                              <label class="text-xs font-bold text-slate-700">Setoran Yayasan (Rp)</label>
                              <input type="number" name="monthly_yayasan_share_amount" value="{{ old('monthly_yayasan_share_amount') }}" min="0" step="1000"
                                  placeholder="0"
                                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                              <p class="text-[11px] font-medium text-slate-400">Nominal hak Yayasan per bulan</p>
                          </div>

                          <div class="space-y-2">
                              <label class="text-xs font-bold text-slate-700">Prefix Awalan Deskripsi</label>
                              <input type="text" name="description_prefix" value="{{ old('description_prefix', 'SPP') }}"
                                  placeholder="SPP"
                                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                              <p class="text-[11px] font-medium text-slate-400">Hasil: "SPP Juli 2025", "SPP Agustus 2025", ...</p>
                          </div>
                      </div>
                  </div>

                <!-- Notes -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" 
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none resize-none"
                        placeholder="Catatan tambahan untuk internal admin...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-4">
            <button type="submit" 
                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20 hover:from-emerald-700 hover:to-teal-700 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Proses & Buat Tagihan Massal
            </button>
            <a href="{{ route('admin.bills.index') }}" 
                class="px-6 py-3 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-xs hover:bg-slate-100 transition-all">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function updateClassroomOptions() {
    const schoolId = document.getElementById('school_id')?.value;
    const academicYearId = document.getElementById('academic_year_id')?.value;
    const classroomSelect = document.getElementById('classroom_id');
    if (!classroomSelect) return;

    const options = classroomSelect.querySelectorAll('option');
    let hasValidSelection = false;
    options.forEach(option => {
        if (!option.value) return;
        const optSchool = option.getAttribute('data-school');
        const optAY = option.getAttribute('data-academic-year');

        let matchSchool = !schoolId || optSchool == schoolId;
        let matchAY = !academicYearId || optAY == academicYearId;

        if (matchSchool && matchAY) {
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
    const filterBy = document.querySelector('input[name="filter_by"]:checked')?.value;
    
    document.getElementById('classroom_filter').style.display = filterBy === 'classroom' ? 'block' : 'none';
    document.getElementById('grade_filter').style.display = filterBy === 'grade' ? 'block' : 'none';
    document.getElementById('class_type_filter').style.display = filterBy === 'class_type' ? 'block' : 'none';
    
    if (filterBy === 'classroom') {
        document.getElementById('classroom_id').setAttribute('required', 'required');
        document.getElementById('grade_level').removeAttribute('required');
        document.getElementById('class_type').removeAttribute('required');
    } else if (filterBy === 'grade') {
        document.getElementById('grade_level').setAttribute('required', 'required');
        document.getElementById('classroom_id').removeAttribute('required');
        document.getElementById('class_type').removeAttribute('required');
    } else if (filterBy === 'class_type') {
        document.getElementById('class_type').setAttribute('required', 'required');
        document.getElementById('classroom_id').removeAttribute('required');
        document.getElementById('grade_level').removeAttribute('required');
    } else {
        document.getElementById('classroom_id').removeAttribute('required');
        document.getElementById('grade_level').removeAttribute('required');
        document.getElementById('class_type').removeAttribute('required');
    }
    
    updateStudentCount();
}

function toggleBillingType() {
    const billingType = document.querySelector('input[name="billing_type"]:checked')?.value;
    const isRecurring = billingType === 'monthly';
    
    const monthlyOptions = document.getElementById('monthly_options');
    const monthlyDetails = document.getElementById('monthly_details');
    const singleDateField = document.getElementById('single_date_field');
    
    if (monthlyOptions) monthlyOptions.style.display = isRecurring ? 'block' : 'none';
    if (monthlyDetails) monthlyDetails.style.display = isRecurring ? 'block' : 'none';
    if (singleDateField) singleDateField.style.display = isRecurring ? 'none' : 'block';
    
    if (isRecurring) {
        document.querySelector('input[name="amount"]')?.removeAttribute('required');
        document.querySelector('input[name="monthly_amount"]')?.setAttribute('required', 'required');
    } else {
        document.querySelector('input[name="amount"]')?.setAttribute('required', 'required');
        document.querySelector('input[name="monthly_amount"]')?.removeAttribute('required');
    }
    
    updateAmounts();
    updateStudentCount();
}

function updateAmounts() {
    const billingType = document.querySelector('input[name="billing_type"]:checked')?.value;
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

function updateStudentCount() {
    const schoolId = document.getElementById('school_id')?.value;
    const filterBy = document.querySelector('input[name="filter_by"]:checked')?.value;
    
    if (!schoolId) {
        document.getElementById('student_count').textContent = 'Silakan pilih sekolah target terlebih dahulu.';
        return;
    }
    
    let message = '';
    if (filterBy === 'all') {
        message = 'Tagihan akan dibuat untuk SELURUH SISWA AKTIF di sekolah terpilih';
    } else if (filterBy === 'classroom') {
        const classroomId = document.getElementById('classroom_id')?.value;
        message = classroomId ? 'Tagihan akan dibuat untuk seluruh siswa di kelas terpilih' : 'Pilih kelas spesifik terlebih dahulu';
    } else if (filterBy === 'grade') {
        const gradeLevel = document.getElementById('grade_level')?.value;
        message = gradeLevel ? `Tagihan akan dibuat untuk seluruh siswa tingkat ${gradeLevel}` : 'Pilih tingkat kelas terlebih dahulu';
    }
    
    const billingType = document.querySelector('input[name="billing_type"]:checked')?.value;
    if (billingType === 'monthly') {
        const generateMonths = document.getElementById('generate_months')?.value || 12;
        message += ` (Periode SPP: ${generateMonths} bulan)`;
    }
    
    document.getElementById('student_count').textContent = message;
}

document.addEventListener('DOMContentLoaded', function() {
    updateClassroomOptions();
    toggleFilterOptions();
    toggleBillingType();
});
</script>
@endsection
