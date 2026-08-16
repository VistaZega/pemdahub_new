@extends('layouts.admin')

@section('title', 'Absensi Guru Hari Ini')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg">
                <i class="fas fa-chalkboard-teacher text-xl text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Absensi Guru</h1>
                <p class="text-gray-500 text-sm mt-0.5">Monitoring kehadiran guru hari ini</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.teachers.attendance.bulk', ['school_id' => $schoolId, 'date' => $date]) }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 shadow transition-all text-sm">
                <i class="fas fa-list-check"></i> Input Absensi Massal
            </a>
            <a href="{{ route('admin.teachers.attendance.rekap', ['school_id' => $schoolId]) }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all text-sm">
                <i class="fas fa-table"></i> Rekap Bulanan
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isKetuaYayasan())
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Sekolah</label>
                <select name="school_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50">
                    <option value="">-- Pilih Sekolah --</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                       class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50">
            </div>
            @if(!auth()->user()->isSuperAdmin() && !auth()->user()->isKetuaYayasan())
                <input type="hidden" name="school_id" value="{{ $schoolId }}">
            @endif
            <button type="submit" class="px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 transition-colors">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
        </form>
    </div>

    @if($schoolId)
    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @php
            $statItems = [
                ['label' => 'Hadir',      'key' => 'hadir',     'color' => 'from-green-400 to-emerald-500',  'icon' => 'fa-check-circle'],
                ['label' => 'Sakit',      'key' => 'sakit',     'color' => 'from-yellow-400 to-amber-500',   'icon' => 'fa-briefcase-medical'],
                ['label' => 'Izin',       'key' => 'izin',      'color' => 'from-blue-400 to-blue-500',      'icon' => 'fa-file-alt'],
                ['label' => 'Alpha',      'key' => 'alpha',     'color' => 'from-red-400 to-rose-500',       'icon' => 'fa-times-circle'],
                ['label' => 'Dinas Luar', 'key' => 'dinas_luar','color' => 'from-purple-400 to-violet-500',  'icon' => 'fa-road'],
                ['label' => 'Cuti',       'key' => 'cuti',      'color' => 'from-indigo-400 to-indigo-500',  'icon' => 'fa-umbrella-beach'],
                ['label' => 'Belum Absen','key' => 'belum',     'color' => 'from-gray-400 to-slate-500',     'icon' => 'fa-clock'],
            ];
        @endphp
        @foreach($statItems as $si)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $si['color'] }} flex items-center justify-center flex-shrink-0">
                <i class="fas {{ $si['icon'] }} text-white text-sm"></i>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats[$si['key']] ?? 0 }}</div>
                <div class="text-xs text-gray-500">{{ $si['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list text-emerald-500"></i>
                Data Absensi — {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
            </h2>
            <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                {{ $teachers->count() }} guru sudah absen
            </span>
        </div>

        @if($teachers->isEmpty())
        <div class="py-16 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-clock text-3xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 font-medium">Belum ada guru yang absen hari ini</p>
            <p class="text-gray-400 text-sm mt-1">Gunakan tombol "Input Absensi Massal" untuk mulai input</p>
            <a href="{{ route('admin.teachers.attendance.bulk', ['school_id' => $schoolId, 'date' => $date]) }}"
               class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 transition-colors">
                <i class="fas fa-list-check"></i> Input Sekarang
            </a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Guru</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($teachers as $index => $teacher)
                    @php $att = $attendances->get($teacher->id); @endphp
                    <tr class="hover:bg-emerald-50/40 transition-colors">
                        <td class="px-5 py-3.5 text-sm text-gray-400">{{ $index + 1 }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center flex-shrink-0 shadow-xs">
                                    <span class="text-white text-xs font-bold">{{ strtoupper(substr($teacher->full_name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 text-sm flex items-center gap-1.5 flex-wrap">
                                        <span>{{ $teacher->full_name }}</span>
                                        @if($att)
                                            @if($att->recorded_via === 'gps')
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-blue-100 text-blue-700 border border-blue-200">
                                                    <i class="fas fa-location-dot"></i> GPS
                                                </span>
                                            @elseif($att->recorded_via === 'rfid')
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                                    <i class="fas fa-id-card"></i> RFID
                                                </span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 border border-slate-200">
                                                    <i class="fas fa-pen"></i> Manual
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $teacher->employee_code ?? '-' }} · {{ $teacher->teacher?->teacher_code ?? 'Guru' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($att)
                            @php
                                $colorMap = [
                                    'hadir'     => 'bg-green-100 text-green-700 border border-green-200',
                                    'sakit'     => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
                                    'izin'      => 'bg-blue-100 text-blue-700 border border-blue-200',
                                    'alpha'     => 'bg-red-100 text-red-700 border border-red-200',
                                    'dinas_luar'=> 'bg-purple-100 text-purple-700 border border-purple-200',
                                    'cuti'      => 'bg-indigo-100 text-indigo-700 border border-indigo-200',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $colorMap[$att->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ \App\Models\EmployeeAttendance::STATUSES[$att->status] ?? $att->status }}
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center text-sm text-gray-700 font-mono font-medium">
                            {{ $att?->time_in ? substr($att->time_in, 0, 5) : '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-center text-sm text-gray-700 font-mono font-medium">
                            {{ $att?->time_out ? substr($att->time_out, 0, 5) : '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-500 max-w-[160px] truncate">
                            {{ $att?->notes ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($att)
                            <form action="{{ route('admin.teachers.attendance.destroy', $att->id) }}" method="POST"
                                  onsubmit="return confirm('Hapus data absensi ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @else
    {{-- No School Selected --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-20 text-center">
        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-school text-3xl text-emerald-300"></i>
        </div>
        <p class="text-gray-500 font-medium">Pilih sekolah untuk melihat data absensi guru</p>
    </div>
    @endif
</div>
@endsection
