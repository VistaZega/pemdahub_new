@extends('layouts.admin')

@section('title', 'Cuti & Izin Pegawai')

@section('content')
@php
    $user = auth()->user();
@endphp
<div class="space-y-6">
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-sky-600 shadow-lg">
                    <i class="fas fa-calendar-check text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Cuti &amp; Izin Pegawai</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Monitoring pengajuan &amp; tahapan persetujuan</p>
                </div>
            </div>
            <div class="flex gap-3 flex-wrap">
                <a href="{{ route('admin.employees.leaves.rekap') }}"
                    class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all text-sm">
                    <i class="fas fa-chart-bar"></i> Rekapitulasi
                </a>
                <a href="{{ route('admin.employees.leaves.create') }}"
                    class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-600 to-sky-700 text-white rounded-xl font-medium hover:from-cyan-700 hover:to-sky-800 shadow-md transition-all text-sm">
                    <i class="fas fa-plus"></i> Ajukan Cuti
                </a>
            </div>
        </div>
    </div>

    {{-- Info Banner untuk peran non-approver --}}
    @if(!$user->isKepalaSekolah() && !$user->isKetuaYayasan() && !$user->isSuperAdmin())
    <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800">
        <i class="fas fa-info-circle text-blue-500 mt-0.5 shrink-0"></i>
        <div>
            <span class="font-bold">Mode Monitoring:</span> Anda dapat melihat seluruh pengajuan cuti dan memantau tahapan persetujuannya.
            Persetujuan dilakukan oleh <strong>Kepala Sekolah</strong> (tahap 1) dan <strong>Yayasan</strong> (tahap 2, untuk cuti &gt;3 hari).
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-xl flex items-center gap-3">
        <i class="fas fa-check-circle text-green-500"></i>
        <p class="text-green-800 font-medium">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-xl flex items-center gap-3">
        <i class="fas fa-times-circle text-red-500"></i>
        <p class="text-red-800 font-medium">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-yellow-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Menunggu Kasek</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['pending'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center"><i class="fas fa-clock text-yellow-500 text-lg"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Menunggu Yayasan</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['needs_yayasan'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center"><i class="fas fa-building text-blue-500 text-lg"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-green-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Disetujui Bulan Ini</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['approved_month'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center"><i class="fas fa-check-circle text-green-500 text-lg"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Ditolak Bulan Ini</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center"><i class="fas fa-times-circle text-red-500 text-lg"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
        <form action="{{ route('admin.employees.leaves.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Cari Pegawai</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama pegawai..."
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all">
            </div>
            <div class="w-full md:w-44">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Status</label>
                <select name="status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all appearance-none">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\EmployeeLeave::STATUSES as $k => $v)
                    <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-44">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Jenis Cuti</label>
                <select name="leave_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all appearance-none">
                    <option value="">Semua Jenis</option>
                    @foreach(\App\Models\EmployeeLeave::LEAVE_TYPES as $k => $v)
                    <option value="{{ $k }}" {{ request('leave_type') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            @if($user->isSuperAdmin() || $user->isKetuaYayasan())
            <div class="w-full md:w-48">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Unit Sekolah</label>
                <select name="school_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all appearance-none">
                    <option value="">Semua Unit</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2">
                    <i class="fas fa-filter text-xs"></i> Filter
                </button>
                @if(request()->anyFilled(['search', 'status', 'leave_type', 'school_id']))
                <a href="{{ route('admin.employees.leaves.index') }}" class="px-4 py-2.5 bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-xl text-sm font-bold transition-all">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pegawai</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis &amp; Tanggal</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Hari</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider" style="min-width:300px">
                            <span class="flex items-center justify-center gap-1.5">
                                <i class="fas fa-route text-indigo-400"></i> Tahapan Persetujuan
                            </span>
                        </th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($leaves as $index => $leave)
                    @php
                        $needsYayasan = $leave->needsYayasanApproval();
                        $statusColors = [
                            'pending'         => ['bg-yellow-100','text-yellow-700'],
                            'approved_kepsek' => ['bg-blue-100',  'text-blue-700'],
                            'approved'        => ['bg-green-100', 'text-green-700'],
                            'rejected'        => ['bg-red-100',   'text-red-700'],
                        ];
                        [$sBg, $sTxt] = $statusColors[$leave->status] ?? ['bg-gray-100','text-gray-700'];
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        {{-- No --}}
                        <td class="px-4 py-4">
                            <div class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 font-bold text-xs flex items-center justify-center">
                                {{ $leaves->firstItem() + $index }}
                            </div>
                        </td>
                        {{-- Pegawai --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-sky-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($leave->employee->full_name ?? '?', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 text-sm">{{ $leave->employee->full_name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $leave->employee->school->name ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        {{-- Jenis & Tanggal --}}
                        <td class="px-4 py-4">
                            <span class="block text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg mb-1 w-fit">{{ $leave->leave_type_label }}</span>
                            <span class="text-xs text-gray-500">{{ $leave->start_date->format('d/m/Y') }} – {{ $leave->end_date->format('d/m/Y') }}</span>
                        </td>
                        {{-- Hari --}}
                        <td class="px-4 py-4 text-center">
                            <span class="text-lg font-black text-gray-800">{{ $leave->days_count }}</span>
                            <span class="text-xs text-gray-400 block">hari</span>
                        </td>

                        {{-- ─── KOLOM TAHAPAN PERSETUJUAN ─── --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1 justify-center flex-wrap">

                                {{-- STEP 1: Diajukan --}}
                                <div class="flex flex-col items-center gap-0.5" title="Diajukan: {{ $leave->created_at->format('d/m/Y H:i') }}">
                                    <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center shadow-sm">
                                        <i class="fas fa-paper-plane text-white" style="font-size:9px"></i>
                                    </div>
                                    <span class="text-[9px] font-bold text-emerald-600">Ajukan</span>
                                </div>

                                <div class="w-6 h-px bg-gray-300 flex-shrink-0 mb-3"></div>

                                {{-- STEP 2: Kasek --}}
                                @if($leave->approved_by_kepsek)
                                    @if($leave->status === 'rejected' && !$leave->approved_by_yayasan)
                                    <div class="flex flex-col items-center gap-0.5" title="Ditolak Kasek: {{ $leave->approvedByKepsek?->name }}">
                                        <div class="w-7 h-7 rounded-full bg-red-500 flex items-center justify-center shadow-sm">
                                            <i class="fas fa-times text-white" style="font-size:9px"></i>
                                        </div>
                                        <span class="text-[9px] font-bold text-red-600">Tolak</span>
                                    </div>
                                    @else
                                    <div class="flex flex-col items-center gap-0.5" title="Kasek: {{ $leave->approvedByKepsek?->name }} ({{ $leave->approved_at_kepsek?->format('d/m') }})">
                                        <div class="w-7 h-7 rounded-full bg-cyan-500 flex items-center justify-center shadow-sm">
                                            <i class="fas fa-check text-white" style="font-size:9px"></i>
                                        </div>
                                        <span class="text-[9px] font-bold text-cyan-600">Kasek ✓</span>
                                    </div>
                                    @endif
                                @else
                                <div class="flex flex-col items-center gap-0.5" title="Menunggu persetujuan Kepala Sekolah">
                                    <div class="w-7 h-7 rounded-full {{ $leave->status === 'pending' ? 'bg-yellow-400' : 'bg-gray-300' }} flex items-center justify-center shadow-sm {{ $leave->status === 'pending' ? 'animate-pulse' : '' }}">
                                        <i class="fas fa-hourglass-half text-white" style="font-size:9px"></i>
                                    </div>
                                    <span class="text-[9px] font-bold {{ $leave->status === 'pending' ? 'text-yellow-600' : 'text-gray-400' }}">Kasek</span>
                                </div>
                                @endif

                                @if($needsYayasan)
                                <div class="w-6 h-px bg-gray-300 flex-shrink-0 mb-3"></div>

                                {{-- STEP 3: Yayasan (hanya jika cuti > 3 hari) --}}
                                @if($leave->approved_by_yayasan)
                                    @if($leave->status === 'rejected')
                                    <div class="flex flex-col items-center gap-0.5" title="Ditolak Yayasan">
                                        <div class="w-7 h-7 rounded-full bg-red-500 flex items-center justify-center shadow-sm">
                                            <i class="fas fa-times text-white" style="font-size:9px"></i>
                                        </div>
                                        <span class="text-[9px] font-bold text-red-600">Tolak</span>
                                    </div>
                                    @else
                                    <div class="flex flex-col items-center gap-0.5" title="Yayasan: {{ $leave->approvedByYayasan?->name }} ({{ $leave->approved_at_yayasan?->format('d/m') }})">
                                        <div class="w-7 h-7 rounded-full bg-violet-500 flex items-center justify-center shadow-sm">
                                            <i class="fas fa-check text-white" style="font-size:9px"></i>
                                        </div>
                                        <span class="text-[9px] font-bold text-violet-600">Yayasan ✓</span>
                                    </div>
                                    @endif
                                @elseif($leave->status === 'approved_kepsek')
                                <div class="flex flex-col items-center gap-0.5" title="Menunggu persetujuan Yayasan">
                                    <div class="w-7 h-7 rounded-full bg-blue-400 flex items-center justify-center shadow-sm animate-pulse">
                                        <i class="fas fa-hourglass-half text-white" style="font-size:9px"></i>
                                    </div>
                                    <span class="text-[9px] font-bold text-blue-600">Yayasan</span>
                                </div>
                                @else
                                <div class="flex flex-col items-center gap-0.5" title="Belum sampai tahap ini">
                                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center shadow-sm">
                                        <i class="fas fa-building text-gray-400" style="font-size:9px"></i>
                                    </div>
                                    <span class="text-[9px] text-gray-400">Yayasan</span>
                                </div>
                                @endif
                                @endif

                                <div class="w-6 h-px bg-gray-300 flex-shrink-0 mb-3"></div>

                                {{-- STATUS AKHIR --}}
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black {{ $sBg }} {{ $sTxt }} whitespace-nowrap">
                                        @if($leave->status === 'approved') <i class="fas fa-circle-check mr-1"></i>Selesai
                                        @elseif($leave->status === 'rejected') <i class="fas fa-circle-xmark mr-1"></i>Ditolak
                                        @elseif($leave->status === 'approved_kepsek') <i class="fas fa-clock mr-1"></i>Proses
                                        @else <i class="fas fa-clock mr-1"></i>Pending
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-4 text-center">
                            <a href="{{ route('admin.employees.leaves.show', $leave) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-xs font-bold">
                                <i class="fas fa-eye text-xs"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400">
                                <i class="fas fa-calendar-xmark text-4xl"></i>
                                <p class="font-medium text-gray-500">Belum ada pengajuan cuti</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leaves->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">{{ $leaves->links() }}</div>
        @endif
    </div>
</div>
@endsection
