@extends('layouts.admin')

@section('title', 'Monitoring Presensi - Pusat Absensi')

@push('styles')
<style>
    .edu-font {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .edu-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }
    .edu-stat-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 18px 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .edu-stat-card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.08);
    }
    .edu-badge-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
    }
    .edu-table-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 edu-font">
    {{-- Unified Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- STATISTIC METRIC CARDS                          --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
        @php
            $statCards = [
                ['key' => null,         'label' => 'Total Terdaftar',  'desc' => 'Seluruh ' . $group . ' aktif',  'val' => $stats['total'],      'icon' => 'fa-users',              'icon_bg' => 'bg-indigo-50 text-indigo-600 border border-indigo-100', 'active_border' => 'ring-2 ring-indigo-500 border-indigo-500'],
                ['key' => 'hadir',      'label' => 'Hadir Tepat',      'desc' => 'Tepat waktu hari ini',         'val' => $stats['hadir'],      'icon' => 'fa-circle-check',       'icon_bg' => 'bg-emerald-50 text-emerald-600 border border-emerald-100', 'active_border' => 'ring-2 ring-emerald-500 border-emerald-500'],
                ['key' => 'terlambat',  'label' => 'Terlambat',        'desc' => 'Lewat jam toleransi',          'val' => $stats['terlambat'],  'icon' => 'fa-clock',              'icon_bg' => 'bg-amber-50 text-amber-600 border border-amber-100', 'active_border' => 'ring-2 ring-amber-500 border-amber-500'],
                ['key' => 'izin',       'label' => 'Izin Resmi',        'desc' => 'Ada surat / dispensasi',       'val' => $stats['izin'],       'icon' => 'fa-envelope-open-text', 'icon_bg' => 'bg-blue-50 text-blue-600 border border-blue-100', 'active_border' => 'ring-2 ring-blue-500 border-blue-500'],
                ['key' => 'sakit',      'label' => 'Sakit',             'desc' => 'Keterangan medis dokter',      'val' => $stats['sakit'],      'icon' => 'fa-heart-pulse',        'icon_bg' => 'bg-yellow-50 text-yellow-600 border border-yellow-100', 'active_border' => 'ring-2 ring-yellow-500 border-yellow-500'],
                ['key' => 'dinas_luar', 'label' => 'Dinas Luar',        'desc' => 'Tugas kedinasan luar',         'val' => $stats['dinas_luar'], 'icon' => 'fa-briefcase',          'icon_bg' => 'bg-purple-50 text-purple-600 border border-purple-100', 'active_border' => 'ring-2 ring-purple-500 border-purple-500'],
                ['key' => 'alpha',      'label' => 'Tanpa Keterangan',  'desc' => 'Alpha / tidak hadir',          'val' => $stats['alpha'],      'icon' => 'fa-circle-xmark',       'icon_bg' => 'bg-rose-50 text-rose-600 border border-rose-100', 'active_border' => 'ring-2 ring-rose-500 border-rose-500'],
                ['key' => 'belum',      'label' => 'Belum Presensi',    'desc' => 'Menunggu scan/tap kartu',      'val' => $stats['belum'],      'icon' => 'fa-hourglass-half',     'icon_bg' => 'bg-slate-100 text-slate-600 border border-slate-200', 'active_border' => 'ring-2 ring-slate-500 border-slate-500'],
            ];
        @endphp

        @foreach($statCards as $p)
        @php
            $isActive = ($statusFilter === $p['key']) || ($statusFilter === null && $p['key'] === null);
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => $p['key']]) }}" 
           class="edu-stat-card {{ $isActive ? $p['active_border'] . ' bg-slate-50' : 'bg-white' }}">
            
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lg font-bold {{ $p['icon_bg'] }}">
                    <i class="fas {{ $p['icon'] }}"></i>
                </div>
                <span class="text-2xl lg:text-3xl font-extrabold text-slate-800 leading-none font-mono">
                    {{ number_format($p['val']) }}
                </span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">{{ $p['label'] }}</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">{{ $p['desc'] }}</p>
            </div>
        </a>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TOOLBAR: FILTER & SEARCH                        --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="edu-card p-5 md:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white">
        <form method="GET" class="flex flex-wrap items-center gap-3 flex-1">
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">
            @if($statusFilter)
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif

            {{-- Classroom Filter if Siswa --}}
            @if($group === 'siswa' && $classrooms->isNotEmpty())
            <div class="min-w-[200px]">
                <select name="classroom_id" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">🏫 Semua Rombel / Kelas</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[220px] max-w-md">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NISN, NIP, atau kode..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                <i class="fas fa-search absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-sm">
                Filter
            </button>

            @if($search || $classroomId || $statusFilter)
            <a href="{{ route('admin.attendance.monitoring', ['group' => $group, 'school_id' => $schoolId, 'date' => $date]) }}" 
               class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                <i class="fas fa-rotate-left mr-1"></i> Reset
            </a>
            @endif
        </form>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('admin.attendance.bulk', ['group' => $group, 'school_id' => $schoolId, 'date' => $date, 'classroom_id' => $classroomId]) }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-900 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm border border-amber-500">
                <i class="fas fa-table-list text-sm"></i>
                <span>Input Massal</span>
            </a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- DATA TABLE CONTAINER                            --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="edu-card overflow-hidden bg-white">
        {{-- Card Header --}}
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-4 bg-slate-50/60">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg font-bold shadow-sm">
                    <i class="fas fa-users-viewfinder"></i>
                </div>
                <div>
                    <h2 class="text-base md:text-lg font-bold text-slate-800 leading-snug">
                        Daftar Presensi {{ ucfirst($group) }}
                    </h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} &middot; {{ $selectedSchool->name ?? '' }}
                    </p>
                </div>
            </div>
            <span class="text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3.5 py-1.5 rounded-lg shadow-2xs">
                Total <b>{{ $items->count() }}</b> Data
            </span>
        </div>

        @if($items->isEmpty())
        <div class="py-20 text-center text-slate-400">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400 text-2xl">
                <i class="fas fa-user-slash"></i>
            </div>
            <p class="text-base text-slate-700 font-bold">Tidak ada data presensi yang sesuai dengan filter.</p>
            <p class="text-xs text-slate-500 mt-1">Silakan sesuaikan filter rombel, tanggal, atau status di atas.</p>
        </div>
        @else
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-900 text-white border-b border-slate-800 text-xs font-bold uppercase tracking-wider">
                    <tr class="whitespace-nowrap">
                        <th class="py-3.5 px-4 text-center w-12 text-slate-400">No</th>
                        <th class="py-3.5 px-4 text-left">Nama & Identitas</th>
                        <th class="py-3.5 px-4 text-left">Unit / Rombel</th>
                        <th class="py-3.5 px-4 text-center">Status Kehadiran</th>
                        <th class="py-3.5 px-3 text-center">Masuk</th>
                        <th class="py-3.5 px-3 text-center">Pulang</th>
                        <th class="py-3.5 px-3 text-center">Metode</th>
                        <th class="py-3.5 px-4 text-left">Keterangan</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-xs">
                    @foreach($items as $idx => $it)
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $it->status === 'belum' ? 'bg-slate-50/30' : '' }}" id="row-person-{{ $it->person_id }}">
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-500">{{ $idx + 1 }}</td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                {{-- Avatar Profile --}}
                                <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden text-slate-700">
                                    @if(!empty($it->photo_url))
                                        <img src="{{ $it->photo_url }}" class="w-full h-full object-cover" alt="{{ $it->name }}">
                                    @else
                                        <span>{{ strtoupper(substr($it->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 text-xs md:text-sm leading-snug break-words" title="{{ $it->name }}">{{ $it->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $it->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold">
                                {{ $it->info }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @php
                                $badgeStyle = match($it->status) {
                                    'hadir'      => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
                                    'terlambat'  => 'bg-amber-100 text-amber-800 border border-amber-300',
                                    'izin'       => 'bg-blue-100 text-blue-800 border border-blue-300',
                                    'sakit'      => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                                    'dinas_luar' => 'bg-purple-100 text-purple-800 border border-purple-300',
                                    'cuti'       => 'bg-indigo-100 text-indigo-800 border border-indigo-300',
                                    'alpha'      => 'bg-rose-100 text-rose-800 border border-rose-300',
                                    default      => 'bg-slate-100 text-slate-600 border border-slate-300'
                                };
                            @endphp
                            <span class="edu-badge-status {{ $badgeStyle }}">
                                {{ $it->status === 'belum' ? 'Belum Absen' : ucfirst(str_replace('_', ' ', $it->status)) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-800 whitespace-nowrap">
                            {{ $it->time_in }}
                        </td>
                        <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-800 whitespace-nowrap">
                            {{ $it->time_out }}
                        </td>
                        <td class="py-3.5 px-3 text-center whitespace-nowrap">
                            @if($it->recorded_via === 'gps')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fas fa-location-dot mr-1"></i> GPS
                                </span>
                            @elseif($it->recorded_via === 'rfid')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i class="fas fa-id-card mr-1"></i> RFID
                                </span>
                            @elseif($it->recorded_via === 'manual')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="fas fa-pen mr-1"></i> MANUAL
                                </span>
                            @else
                                <span class="text-slate-400 font-bold">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 max-w-[160px] truncate">
                            {{ $it->notes ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                {{-- Edit Button --}}
                                <button type="button" 
                                        onclick="openEditModal({{ json_encode([
                                            'person_id'    => $it->person_id,
                                            'name'         => $it->name,
                                            'code'         => $it->code,
                                            'photo_url'    => $it->photo_url,
                                            'info'         => $it->info,
                                            'classroom_id' => $it->classroom_id,
                                            'attendance_id'=> $it->attendance_id,
                                            'status'       => $it->status,
                                            'time_in'      => $it->raw_time_in,
                                            'time_out'     => $it->raw_time_out,
                                            'notes'        => $it->notes,
                                        ]) }})"
                                        class="edu-table-btn bg-amber-400 hover:bg-amber-500 text-slate-900 border-amber-500" 
                                        title="Edit Presensi">
                                    <i class="fas fa-edit text-[11px]"></i>
                                    <span>Edit</span>
                                </button>

                                {{-- Hapus Button --}}
                                @if($it->attendance_id)
                                <form action="{{ route('admin.attendance.destroy', ['id' => $it->attendance_id, 'group' => $group]) }}" 
                                      method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/mereset data presensi {{ $it->name }}?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="edu-table-btn bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200" title="Hapus Presensi">
                                        <i class="fas fa-trash text-[11px]"></i>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- EDIT MODAL                                      --}}
{{-- ═══════════════════════════════════════════════ --}}
<div id="editAttendanceModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
        {{-- Modal Header --}}
        <div class="px-6 py-5 bg-slate-50 flex items-center justify-between border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg font-bold shadow-sm">
                    <i class="fas fa-user-pen"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 leading-snug">Edit Presensi Individu</h3>
                    <p class="text-xs text-indigo-600 font-semibold mt-0.5" id="modal_person_subtitle">Nama & Rombel</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-lg bg-white hover:bg-slate-100 text-slate-400 flex items-center justify-center transition border border-slate-200">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Modal Form --}}
        <form action="{{ route('admin.attendance.single.save') }}" method="POST" id="singleAttendanceForm" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="person_id" id="modal_person_id">
            <input type="hidden" name="classroom_id" id="modal_classroom_id">

            {{-- Person Info Card --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm shrink-0 overflow-hidden" id="modal_avatar_container">
                    <span id="modal_avatar_initial">YZ</span>
                </div>
                <div class="min-w-0">
                    <div class="font-bold text-slate-800 text-sm truncate" id="modal_person_name">Nama Pengguna</div>
                    <div class="text-xs text-slate-400 font-mono" id="modal_person_code">NISN / NIP</div>
                </div>
            </div>

            {{-- Status Selector Cards --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Status Kehadiran:
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="hadir" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold text-slate-700 bg-white peer-checked:bg-emerald-600 peer-checked:border-emerald-600 peer-checked:text-white transition">
                            <i class="fas fa-circle-check block text-base mb-1"></i>
                            Hadir
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="terlambat" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold text-slate-700 bg-white peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white transition">
                            <i class="fas fa-clock block text-base mb-1"></i>
                            Terlambat
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="izin" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold text-slate-700 bg-white peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-checked:text-white transition">
                            <i class="fas fa-envelope block text-base mb-1"></i>
                            Izin
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="sakit" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold text-slate-700 bg-white peer-checked:bg-yellow-500 peer-checked:border-yellow-500 peer-checked:text-white transition">
                            <i class="fas fa-heart-pulse block text-base mb-1"></i>
                            Sakit
                        </div>
                    </label>
                    @if($group !== 'siswa')
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="dinas_luar" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold text-slate-700 bg-white peer-checked:bg-purple-600 peer-checked:border-purple-600 peer-checked:text-white transition">
                            <i class="fas fa-briefcase block text-base mb-1"></i>
                            Dinas Luar
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="cuti" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold text-slate-700 bg-white peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:text-white transition">
                            <i class="fas fa-calendar-xmark block text-base mb-1"></i>
                            Cuti
                        </div>
                    </label>
                    @endif
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="alpha" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold text-slate-700 bg-white peer-checked:bg-rose-600 peer-checked:border-rose-600 peer-checked:text-white transition">
                            <i class="fas fa-circle-xmark block text-base mb-1"></i>
                            Alpha
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="belum" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-bold text-slate-700 bg-white peer-checked:bg-slate-700 peer-checked:border-slate-700 peer-checked:text-white transition">
                            <i class="fas fa-hourglass block text-base mb-1"></i>
                            Belum Absen
                        </div>
                    </label>
                </div>
            </div>

            {{-- Jam Masuk & Jam Pulang --}}
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Jam Masuk:</span>
                        <button type="button" onclick="document.getElementById('modal_time_in').value='07:15'" class="text-[10px] text-indigo-600 font-bold underline">
                            07:15
                        </button>
                    </label>
                    <input type="time" name="time_in" id="modal_time_in"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-800 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Jam Pulang:</span>
                        <button type="button" onclick="document.getElementById('modal_time_out').value='15:00'" class="text-[10px] text-indigo-600 font-bold underline">
                            15:00
                        </button>
                    </label>
                    <input type="time" name="time_out" id="modal_time_out"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-800 outline-none">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Keterangan / Catatan:
                </label>
                <textarea name="notes" id="modal_notes" rows="2" placeholder="Tuliskan keterangan opsional..."
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-medium text-slate-800 outline-none"></textarea>
            </div>

            {{-- Modal Actions --}}
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200">
                <button type="button" onclick="closeEditModal()" 
                        class="px-5 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit" id="modalSaveBtn"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm transition active:scale-95 flex items-center gap-2">
                    <i class="fas fa-save text-xs"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('modal_person_id').value = data.person_id || '';
    document.getElementById('modal_classroom_id').value = data.classroom_id || '';
    document.getElementById('modal_person_name').innerText = data.name || '';
    document.getElementById('modal_person_code').innerText = data.code || '-';
    document.getElementById('modal_person_subtitle').innerText = data.info || '';
    
    // Avatar thumbnail
    const avatarContainer = document.getElementById('modal_avatar_container');
    if (data.photo_url) {
        avatarContainer.innerHTML = `<img src="${data.photo_url}" class="w-full h-full object-cover" alt="${data.name}">`;
    } else {
        avatarContainer.innerHTML = `<span class="text-indigo-700 font-bold text-sm">${(data.name || 'AB').substring(0, 2).toUpperCase()}</span>`;
    }

    // Set Radio Status
    const curStatus = data.status || 'hadir';
    const radios = document.querySelectorAll('.modal-status-radio');
    radios.forEach(r => {
        r.checked = (r.value === curStatus);
    });

    document.getElementById('modal_time_in').value = data.time_in || '';
    document.getElementById('modal_time_out').value = data.time_out || '';
    document.getElementById('modal_notes').value = data.notes || '';

    document.getElementById('editAttendanceModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editAttendanceModal').classList.add('hidden');
}

document.getElementById('singleAttendanceForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('modalSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
});
</script>
@endsection
