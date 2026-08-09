@extends('layouts.admin')
@section('title', 'Beban Kerja & Penggajian')

@push('styles')
<style>
    
    
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
    }
    .table-row-hover:hover {
        background: #eef2ff; /* indigo-50 */
    }
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 999px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-in { animation: fadeInUp 0.5s ease-out; }
</style>
@endpush

@section('content')
<div class="space-y-8 animate-in">

    {{-- ═══════════ HEADER ═══════════ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-xl shadow-amber-500/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 text-gray-900 tracking-tight">Beban Kerja & Penggajian</h1>
                <p class="text-sm text-gray-500 mt-0.5">Rekapitulasi beban kerja dan komponen gaji pegawai</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.workload.salary-report') }}" 
               class="inline-flex items-center gap-2.5 px-6 py-3 bg-white border-2 border-gray-100 text-gray-700 rounded-xl hover:border-indigo-200 hover:shadow-lg transition-all duration-300 font-bold text-sm">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Laporan Gaji
            </a>
        </div>
    </div>

    {{-- ═══════════ ALERTS ═══════════ --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl">
        <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </div>
        <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
    </div>
    @endif

    {{-- ═══════════ FILTERS & SUMMARY ═══════════ --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg rounded-2xl shadow-lg p-8">
        <div class="flex flex-col xl:flex-row gap-8">
            {{-- Filter Form --}}
            <form method="GET" class="flex-1">
                <div class="flex items-center gap-2 mb-5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Filter Data</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 ml-1">Sekolah</label>
                        <select name="school_id" 
                            class="w-full px-4 py-3 bg-white border-2 border-gray-100 rounded-xl text-sm font-medium focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none appearance-none cursor-pointer">
                            <option value="">Semua Sekolah</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 ml-1">Tahun Ajaran</label>
                        <select name="academic_year_id" 
                            class="w-full px-4 py-3 bg-white border-2 border-gray-100 rounded-xl text-sm font-medium focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none appearance-none cursor-pointer">
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $yearId == $ay->id ? 'selected' : '' }}>{{ $ay->year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 ml-1">Semester</label>
                        <select name="semester_id" 
                            class="w-full px-4 py-3 bg-white border-2 border-gray-100 rounded-xl text-sm font-medium focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none appearance-none cursor-pointer">
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all font-bold text-sm shadow-lg shadow-indigo-500/20 hover:-translate-y-0.5 duration-300">
                            <svg class="w-4 h-4 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Tampilkan
                        </button>
                    </div>
                </div>
            </form>

            {{-- Summary Stats --}}
            <div class="xl:border-l xl:border-gray-100 xl:pl-8 flex items-center">
                <div class="grid grid-cols-2 gap-6 w-full">
                    <div class="stat-card text-center p-4 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100/50">
                        <p class="text-[9px] font-bold text-blue-500/70 uppercase tracking-widest mb-1">Pegawai</p>
                        <p class="text-2xl font-bold text-gray-900 text-blue-700">{{ $totals['count'] }}</p>
                        <p class="text-[10px] text-blue-400 font-medium mt-0.5">Orang</p>
                    </div>
                    <div class="stat-card text-center p-4 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100/50">
                        <p class="text-[9px] font-bold text-emerald-500/70 uppercase tracking-widest mb-1">Total THP</p>
                        <p class="text-xl font-bold text-emerald-700">Rp&nbsp;{{ number_format($totals['total_compensation'], 0, ',', '.') }}</p>
                        <p class="text-[10px] text-emerald-400 font-medium mt-0.5">Per Bulan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ WORKLOAD TABLE ═══════════ --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[1000px]">
                <thead class="bg-gray-50 border-b border-gray-100">
                    @php
                        $selectedSchoolObj = isset($schools) && isset($schoolId) ? $schools->firstWhere('id', $schoolId) : null;
                        $isYayasanFilter = $selectedSchoolObj && ($selectedSchoolObj->type === 'yayasan' || $selectedSchoolObj->isYayasan());
                    @endphp
                    <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/50 border-b border-gray-100">
                        <th class="px-4 py-5 text-left text-xs font-bold text-gray-900 uppercase tracking-wider w-12">No</th>
                        <th class="px-5 py-5 text-left text-xs font-bold text-gray-900 uppercase tracking-wider min-w-[180px]">Pegawai</th>
                        <th class="px-4 py-5 text-right text-xs font-bold text-gray-900 uppercase tracking-wider">Gaji Pokok</th>
                        <th class="px-5 py-5 text-left text-xs font-bold text-gray-900 uppercase tracking-wider min-w-[150px]">Tunj. Jabatan</th>
                        @if(!$isYayasanFilter)
                        <th class="px-4 py-5 text-right text-xs font-bold text-gray-900 uppercase tracking-wider">Honor</th>
                        @endif
                        <th class="px-5 py-5 text-left text-xs font-bold text-gray-900 uppercase tracking-wider min-w-[150px]">Tunj. Yayasan</th>
                        <th class="px-5 py-5 text-right text-[10.5px] font-extrabold text-indigo-700 uppercase tracking-widest">THP</th>
                        <th class="px-4 py-5 text-center text-xs font-bold text-gray-900 uppercase tracking-wider w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaries as $index => $summary)
                    @php 
                        $employee = $summary->employee;
                        if (!$employee) {
                            continue;
                        }
                        $isYayasanEmp = $employee && ($employee->isYayasanStaff() || $employee->school?->type === 'yayasan');
                        $teachingHours = $summary->total_teaching_hours ?? 0;
                        $statusClasses = [
                            'yayasan' => 'bg-emerald-100 text-emerald-700',
                            'pns' => 'bg-blue-100 text-blue-700',
                            'honorer' => 'bg-amber-100 text-amber-700',
                            'kontrak' => 'bg-purple-100 text-purple-700',
                        ];
                        $empStatusColor = $statusClasses[$employee->employment_status ?? ''] ?? 'bg-gray-100 text-gray-500';
                    @endphp
                    <tr class="table-row-hover group border-b border-gray-50 transition-all duration-200">
                        {{-- No --}}
                        <td class="px-4 py-5 text-center align-top">
                            <span class="text-xs font-bold text-gray-400 group-hover:text-gray-700 transition-colors pt-0.5 inline-block">{{ $summaries->firstItem() + $index }}</span>
                        </td>

                        {{-- Employee Info --}}
                        <td class="px-5 py-5 align-top">
                            <div class="flex items-start gap-3">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 group-hover:text-indigo-900 leading-tight transition-colors">{{ $employee->full_name ?? '-' }}</p>
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <span class="text-[10px] font-mono font-semibold text-gray-500 group-hover:text-gray-700 transition-colors">{{ $employee->employee_code ?? '-' }}</span>
                                        <span class="px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase {{ $empStatusColor }}">{{ $employee->employment_status ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Gaji Pokok --}}
                        <td class="px-4 py-5 text-right align-top">
                            <span class="text-sm font-bold text-gray-800 tabular-nums">Rp&nbsp;{{ number_format($summary->basic_salary ?? 0, 0, ',', '.') }}</span>
                        </td>

                        {{-- Tunjangan Jabatan --}}
                        <td class="px-5 py-5 align-top">
                            @php
                                $filteredPositions = collect($employee->activePositions ?? [])->filter(function($pos) use ($schoolId) {
                                    if (!$schoolId) return true;
                                    return $pos->school_id == $schoolId || is_null($pos->school_id);
                                });
                                $totalPosAllowance = $summary->total_position_allowance ?? 0;
                            @endphp
                            @if($filteredPositions->count() > 0)
                                <div>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-gray-800 tabular-nums">
                                            @if($totalPosAllowance > 0)
                                                Rp&nbsp;{{ number_format($totalPosAllowance, 0, ',', '.') }}
                                            @else
                                                Rp&nbsp;0
                                            @endif
                                        </span>
                                    </div>
                                    <div class="space-y-1 mt-1.5 border-t border-gray-100 pt-1.5">
                                        @foreach($filteredPositions as $pos)
                                            @php 
                                                $posAmount = $pos->pivot->position_allowance > 0 
                                                    ? $pos->pivot->position_allowance 
                                                    : $pos->allowance_amount;
                                            @endphp
                                            <div class="flex justify-between items-center gap-3 text-[11px]">
                                                <span class="text-gray-500 group-hover:text-gray-900 font-medium truncate max-w-[120px] transition-all" title="{{ $pos->position_name }}">{{ $pos->position_name }}</span>
                                                <span class="text-gray-900 font-bold whitespace-nowrap tabular-nums">{{ number_format($posAmount, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="text-right">
                                    <span class="text-[11px] text-gray-300 italic">—</span>
                                </div>
                            @endif
                        </td>

                        {{-- Honor Mengajar --}}
                        @if(!$isYayasanFilter)
                        <td class="px-4 py-5 text-right align-top">
                            @php
                                $totalTeaching = $summary->total_teaching_allowance ?? 0;
                                $totalPkl = $summary->honor_pkl ?? 0;
                                $totalHonorColumn = $totalTeaching + $totalPkl;
                            @endphp
                            @if(!$isYayasanEmp || $totalHonorColumn > 0 || $teachingHours > 0)
                                <div>
                                    <span class="text-sm font-bold text-gray-800 tabular-nums">Rp&nbsp;{{ number_format($totalHonorColumn, 0, ',', '.') }}</span>
                                    
                                    @if($teachingHours > 0 || $totalTeaching > 0)
                                        @php
                                            $honorData = app(\App\Services\EmployeeAssignmentService::class)->calculateTeachingHonor(
                                                $teachingHours,
                                                $employee->employment_status ?? 'yayasan',
                                                $employee->school?->type ?? 'SMA',
                                                $employee->school_id ?? null,
                                                $employee
                                            );
                                        @endphp
                                        <div class="mt-1.5 border-t border-gray-100 pt-1.5">
                                            <p class="text-[10px] text-gray-500 group-hover:text-gray-800 font-semibold transition-colors">
                                                {{ $honorData['jam_mengajar'] }} | {{ $honorData['jam_wajib'] }} | {{ $honorData['jam_honor'] }} | {{ $honorData['jam_honor'] }} x Rp&nbsp;{{ number_format($honorData['honor_per_jam'], 0, ',', '.') }}
                                            </p>
                                            <p class="text-[9px] text-gray-400 group-hover:text-gray-600 font-medium transition-colors">Jam Tugas | Wajib | Lebih | Perhitungan</p>
                                        </div>
                                    @endif

                                    @if($totalPkl > 0)
                                        <div class="mt-1.5 pt-1.5 border-t border-purple-100">
                                            <div class="flex justify-between items-center gap-3 text-[11px]">
                                                <span class="text-purple-600 font-bold">Pembimbing PKL</span>
                                                <span class="text-purple-700 font-bold tabular-nums">{{ number_format($totalPkl, 0, ',', '.') }}</span>
                                            </div>
                                            <p class="text-[9px] text-purple-400 text-right font-medium mt-0.5">{{ $summary->pkl_supervisor_hours }} JP x Rp 43.000</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-right">
                                    <span class="text-[11px] text-gray-300 italic">—</span>
                                </div>
                            @endif
                        </td>
                        @endif

                        {{-- Tunjangan Yayasan --}}
                        <td class="px-5 py-5 align-top">
                            @php 
                                $totalYayasan = ($summary->family_allowance + $summary->child_allowance + $summary->rice_allowance); 
                                $tunjMeta = $employee ? app(\App\Services\EmployeeAssignmentService::class)->calculateTunjangan($employee, $employee->school_id) : ['meta' => ['keluarga_persen'=>0, 'gaji_pokok'=>0, 'anak_persen'=>0, 'jumlah_anak'=>0, 'beras_nominal'=>0]];
                                $meta = $tunjMeta['meta'];
                            @endphp
                            @if($totalYayasan > 0)
                                <div>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-emerald-600 tabular-nums">Rp&nbsp;{{ number_format($totalYayasan, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="space-y-1.5 mt-1.5 border-t border-gray-100 pt-1.5">
                                        @if($summary->family_allowance > 0)
                                        <div>
                                            <div class="flex justify-between items-center gap-3 text-[11px]">
                                                <span class="text-pink-600 group-hover:text-pink-700 font-bold transition-colors">Keluarga</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($summary->family_allowance, 0, ',', '.') }}</span>
                                            </div>
                                            <p class="text-[9px] text-gray-400 text-right group-hover:text-gray-600 font-medium mt-0.5 transition-colors">{{ $meta['keluarga_persen'] }}% x {{ number_format($meta['gaji_pokok'], 0, ',', '.') }}</p>
                                        </div>
                                        @endif
                                        @if($summary->child_allowance > 0)
                                        <div>
                                            <div class="flex justify-between items-center gap-3 text-[11px]">
                                                <span class="text-blue-600 group-hover:text-blue-700 font-bold transition-colors">Anak</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($summary->child_allowance, 0, ',', '.') }}</span>
                                            </div>
                                            <p class="text-[9px] text-gray-400 text-right group-hover:text-gray-600 font-medium mt-0.5 transition-colors">{{ $meta['anak_persen'] }}% x {{ number_format($meta['gaji_pokok'], 0, ',', '.') }} x {{ $meta['jumlah_anak'] }}</p>
                                        </div>
                                        @endif
                                        @if($summary->rice_allowance > 0)
                                        <div>
                                            <div class="flex justify-between items-center gap-3 text-[11px]">
                                                <span class="text-amber-600 group-hover:text-amber-700 font-bold transition-colors">Beras</span>
                                                <span class="text-gray-800 font-bold tabular-nums">{{ number_format($summary->rice_allowance, 0, ',', '.') }}</span>
                                            </div>
                                            <p class="text-[9px] text-gray-400 text-right group-hover:text-gray-600 font-medium mt-0.5 transition-colors">{{ number_format($meta['beras_nominal'], 0, ',', '.') }} x {{ 1 + $meta['jumlah_anak'] }} org</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="text-right">
                                    <span class="text-[11px] text-gray-300 italic">—</span>
                                </div>
                            @endif
                        </td>

                        {{-- THP --}}
                        <td class="px-5 py-5 text-right align-top">
                            <span class="text-base font-bold text-indigo-700 tabular-nums">Rp&nbsp;{{ number_format($summary->total_compensation ?? 0, 0, ',', '.') }}</span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-5 align-top">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Detail --}}
                                <a href="{{ route('admin.workload.salary-detail', $employee) }}" 
                                   title="Detail Gaji" 
                                   class="w-9 h-9 rounded-xl bg-white border-2 border-indigo-100 text-indigo-600 flex items-center justify-center hover:bg-indigo-50 hover:border-indigo-300 transition-all duration-300 shadow-sm">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>

                                {{-- Slip --}}
                                <a href="{{ route('admin.workload.salary-slip', ['employee' => $employee, 'academic_year_id' => $yearId, 'semester_id' => $semesterId]) }}" 
                                   title="Cetak Slip" 
                                   class="w-9 h-9 rounded-xl bg-white border-2 border-emerald-100 text-emerald-600 flex items-center justify-center hover:bg-emerald-50 hover:border-emerald-300 transition-all duration-300 shadow-sm">
                                    <i class="fas fa-print text-xs"></i>
                                </a>

                                {{-- PDF --}}
                                <a href="{{ route('admin.workload.salary-slip-pdf', ['employee' => $employee, 'academic_year_id' => $yearId, 'semester_id' => $semesterId]) }}" 
                                   title="Unduh PDF" 
                                   class="w-9 h-9 rounded-xl bg-white border-2 border-red-100 text-red-600 flex items-center justify-center hover:bg-red-50 hover:border-red-300 transition-all duration-300 shadow-sm">
                                    <i class="fas fa-file-pdf text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-400">Belum ada data beban kerja</p>
                                    <p class="text-xs text-gray-300 mt-1">Pilih filter di atas, lalu klik "Tampilkan"</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($summaries->hasPages())
        <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/30">
            {{ $summaries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
