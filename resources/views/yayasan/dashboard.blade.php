@extends('layouts.yayasan')

@section('title', 'Dashboard - Ketua Yayasan')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-violet-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-2xl">🏛️</span>
                    <span class="bg-white/20 px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wider">Ketua Yayasan</span>
                </div>
                <h1 class="text-2xl font-bold">Dashboard Yayasan</h1>
                <p class="text-white/70 text-sm mt-1">Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</p>
            </div>
            @if($currentAcademicYear)
            <span class="inline-flex items-center gap-1 bg-white/15 px-3 py-1.5 rounded-lg text-xs font-medium">
                <i class="fas fa-calendar-alt"></i>
                {{ $currentAcademicYear->year }}
            </span>
            @endif
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Total Sekolah --}}
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-violet-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Unit Pendidikan</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_schools'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Sekolah aktif</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-school text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Total Siswa --}}
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Siswa</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_students']) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Siswa aktif ber-rombel (TP {{ $currentAcademicYear->year ?? '' }})</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-user-graduate text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Total Pegawai --}}
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-emerald-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Pegawai</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($stats['total_employees']) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Guru & tenaga pendidikan</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-green-600 flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Per-School Summary --}}
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-bar text-violet-500"></i>
            Ringkasan Per Sekolah
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="bg-gray-50 text-gray-600">
                        <th class="text-left py-3 px-4 rounded-l-lg font-semibold">Nama Sekolah</th>
                        <th class="text-center py-3 px-4 font-semibold">Jenjang</th>
                        <th class="text-center py-3 px-4 font-semibold">Jumlah Siswa</th>
                        <th class="text-center py-3 px-4 font-semibold">Jumlah Pegawai</th>
                        <th class="text-center py-3 px-4 rounded-r-lg font-semibold">Hari Aktif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($schoolSummaries as $school)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 font-medium text-gray-800">{{ $school['name'] }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold @if($school['type'] === 'SMP') bg-blue-100 text-blue-700 @elseif($school['type'] === 'SMA') bg-green-100 text-green-700 @elseif($school['type'] === 'SMK') bg-orange-100 text-orange-700 @else bg-gray-100 text-gray-700 @endif">
                                {{ $school['type'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-semibold text-gray-700">{{ number_format($school['student_count']) }}</td>
                        <td class="py-3 px-4 text-center font-semibold text-gray-700">{{ number_format($school['employee_count']) }}</td>
                        <td class="py-3 px-4 text-center font-semibold text-gray-700">{{ $school['active_days'] }} Hari</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-violet-50 font-bold text-violet-800">
                        <td class="py-3 px-4 rounded-l-lg">Total</td>
                        <td class="py-3 px-4 text-center">{{ $schoolSummaries->count() }} Sekolah</td>
                        <td class="py-3 px-4 text-center">{{ number_format($stats['total_students']) }}</td>
                        <td class="py-3 px-4 text-center">{{ number_format($stats['total_employees']) }}</td>
                        <td class="py-3 px-4 text-center rounded-r-lg">-</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Yayasan Info Card --}}
    @if($yayasan)
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-violet-500"></i>
            Informasi Yayasan
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 mb-1">Nama Resmi</p>
                <p class="font-semibold text-gray-800">{{ $yayasan->name }}</p>
            </div>
            @if($yayasan->address)
            <div>
                <p class="text-gray-500 mb-1">Alamat</p>
                <p class="font-semibold text-gray-800">{{ $yayasan->address }}</p>
            </div>
            @endif
            @if($yayasan->city)
            <div>
                <p class="text-gray-500 mb-1">Kota</p>
                <p class="font-semibold text-gray-800">{{ $yayasan->city }}</p>
            </div>
            @endif
            @if($yayasan->province)
            <div>
                <p class="text-gray-500 mb-1">Provinsi</p>
                <p class="font-semibold text-gray-800">{{ $yayasan->province }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
