@extends('layouts.admin')

@section('title', 'Monitoring Absensi Harian - Pusat Absensi')

@section('content')
<div class="space-y-6">
    <!-- Unified Header -->
    @include('admin.attendance.header')

    <!-- Interactive Stats Pills (Click to Filter) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
        @php
            $statPills = [
                ['key' => null,          'label' => 'Semua',       'val' => $stats['total'],                                                                          'bg' => 'bg-slate-900 text-white',             'border' => 'border-slate-800'],
                ['key' => 'hadir',       'label' => 'Hadir',       'val' => $stats['hadir'],                                                                          'bg' => 'bg-emerald-50 text-emerald-800',      'border' => 'border-emerald-200'],
                ['key' => 'terlambat',   'label' => 'Terlambat',   'val' => $stats['terlambat'],                                                                      'bg' => 'bg-amber-50 text-amber-800',          'border' => 'border-amber-200'],
                ['key' => 'izin',        'label' => 'Izin',        'val' => $stats['izin'],                                                                           'bg' => 'bg-blue-50 text-blue-800',            'border' => 'border-blue-200'],
                ['key' => 'sakit',       'label' => 'Sakit',       'val' => $stats['sakit'],                                                                          'bg' => 'bg-yellow-50 text-yellow-800',        'border' => 'border-yellow-200'],
                ['key' => 'dinas_luar',  'label' => 'Dinas Luar',  'val' => $stats['dinas_luar'],                                                                     'bg' => 'bg-purple-50 text-purple-800',        'border' => 'border-purple-200'],
                ['key' => 'alpha',       'label' => 'Alpha',       'val' => $stats['alpha'],                                                                          'bg' => 'bg-rose-50 text-rose-800',            'border' => 'border-rose-200'],
                ['key' => 'belum',       'label' => 'Belum Absen', 'val' => $stats['belum'],                                                                          'bg' => 'bg-slate-100 text-slate-700',         'border' => 'border-slate-300'],
            ];
        @endphp

        @foreach($statPills as $p)
        @php
            $isActive = ($statusFilter === $p['key']) || ($statusFilter === null && $p['key'] === null);
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => $p['key']]) }}" 
           class="p-3 rounded-2xl border transition-all flex flex-col justify-between shadow-2xs {{ $p['border'] }}
                  {{ $isActive ? 'ring-2 ring-emerald-500 scale-[1.02] shadow-xs ' . $p['bg'] : 'bg-white hover:bg-slate-50 text-slate-700' }}">
            <span class="text-[10px] font-black uppercase tracking-wider truncate opacity-70">{{ $p['label'] }}</span>
            <span class="text-xl font-black mt-1">{{ number_format($p['val']) }}</span>
        </a>
        @endforeach
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">
            @if($statusFilter)
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif

            <!-- Classroom filter if group = Siswa -->
            @if($group === 'siswa' && $classrooms->isNotEmpty())
            <div class="min-w-[170px]">
                <select name="classroom_id" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-black text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Rombel / Kelas</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Search input -->
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / NISN / NIP..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-8 pr-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-black rounded-xl transition shadow-2xs">
                Filter
            </button>

            @if($search || $classroomId || $statusFilter)
            <a href="{{ route('admin.attendance.monitoring', ['group' => $group, 'school_id' => $schoolId, 'date' => $date]) }}" 
               class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                Reset
            </a>
            @endif
        </form>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.attendance.bulk', ['group' => $group, 'school_id' => $schoolId, 'date' => $date, 'classroom_id' => $classroomId]) }}" 
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                <span>Input Massal Hari Ini</span>
            </a>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-black text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-list-check text-emerald-600"></i>
                <span>Daftar Kehadiran {{ ucfirst($group) }} — {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
            </h2>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                {{ $items->count() }} Data Ditampilkan
            </span>
        </div>

        @if($items->isEmpty())
        <div class="py-16 text-center text-slate-400">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-300 text-2xl">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <p class="font-bold text-sm text-slate-700">Tidak ada data kehadiran yang sesuai dengan filter.</p>
            <p class="text-xs text-slate-400 mt-0.5">Silakan pilih kelas, ubah tanggal, atau sesuaikan filter status di atas.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider w-10">No</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider">Nama & Identitas</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider">Unit / Rombel</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-32">Status</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-28">Jam Masuk</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-28">Jam Pulang</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-24">Via</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-16">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($items as $idx => $it)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $it->status === 'belum' ? 'bg-slate-50/30' : '' }}">
                        <td class="px-4 py-3.5 text-xs text-slate-400 font-bold">{{ $idx + 1 }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white font-black text-xs shrink-0 shadow-xs">
                                    {{ strtoupper(substr($it->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $it->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $it->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-slate-600 font-bold">
                            {{ $it->info }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @php
                                $badgeStyle = match($it->status) {
                                    'hadir'      => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'terlambat'  => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'izin'       => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'sakit'      => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'dinas_luar' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'cuti'       => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                    'alpha'      => 'bg-rose-100 text-rose-800 border-rose-200',
                                    default      => 'bg-slate-100 text-slate-500 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-black uppercase tracking-wider border {{ $badgeStyle }}">
                                {{ $it->status === 'belum' ? 'Belum Absen' : ucfirst(str_replace('_', ' ', $it->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center text-xs font-mono font-bold text-slate-800">
                            {{ $it->time_in }}
                        </td>
                        <td class="px-4 py-3.5 text-center text-xs font-mono font-bold text-slate-800">
                            {{ $it->time_out }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($it->recorded_via === 'gps')
                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-blue-100 text-blue-700 border border-blue-200">
                                    <i class="fa-solid fa-location-dot"></i> GPS
                                </span>
                            @elseif($it->recorded_via === 'rfid')
                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-id-card"></i> RFID
                                </span>
                            @elseif($it->recorded_via === 'manual')
                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 border border-slate-200">
                                    <i class="fa-solid fa-pen"></i> MANUAL
                                </span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-xs text-slate-500 max-w-[180px] truncate">
                            {{ $it->notes ?? '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($it->attendance_id)
                            <form action="{{ route('admin.attendance.destroy', ['id' => $it->attendance_id, 'group' => $group]) }}" 
                                  method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini?');" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Data Presensi">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                            @else
                            <span class="text-slate-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
