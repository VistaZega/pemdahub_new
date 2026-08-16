@extends('layouts.admin')

@section('title', 'Monitoring Presensi - Edu Attendance Hub')

@push('styles')
<style>
    .clay-stat-card {
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 8px 12px 24px rgba(30, 41, 59, 0.06), -6px -6px 16px rgba(255, 255, 255, 0.9), inset 2px 2px 4px rgba(255, 255, 255, 0.8), inset -2px -2px 4px rgba(0, 0, 0, 0.03);
        border: 2px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .clay-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 12px 18px 30px rgba(30, 41, 59, 0.12), -8px -8px 20px rgba(255, 255, 255, 1);
    }
    .clay-badge {
        box-shadow: 2px 3px 6px rgba(0,0,0,0.06), inset 1px 1px 2px rgba(255,255,255,0.7), inset -1px -1px 2px rgba(0,0,0,0.05);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Unified Playful Clay Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- PLAYFUL CLAY STAT METRIC CARDS (8 VIBRANT CHIPS)--}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        @php
            $statCards = [
                ['key' => null,         'label' => 'Total Terdaftar',  'desc' => 'Seluruh ' . $group . ' aktif',  'val' => $stats['total'],      'icon' => 'fa-users',              'icon_bg' => 'from-indigo-500 to-blue-600 text-white shadow-indigo-200', 'active_border' => 'border-indigo-400 ring-2 ring-indigo-300'],
                ['key' => 'hadir',      'label' => 'Hadir Tepat',      'desc' => 'Tepat waktu hari ini',         'val' => $stats['hadir'],      'icon' => 'fa-circle-check',       'icon_bg' => 'from-emerald-400 to-teal-600 text-white shadow-emerald-200', 'active_border' => 'border-emerald-400 ring-2 ring-emerald-300'],
                ['key' => 'terlambat',  'label' => 'Terlambat',        'desc' => 'Lewat jam toleransi',          'val' => $stats['terlambat'],  'icon' => 'fa-clock',              'icon_bg' => 'from-amber-400 to-orange-500 text-white shadow-amber-200', 'active_border' => 'border-amber-400 ring-2 ring-amber-300'],
                ['key' => 'izin',       'label' => 'Izin Resmi',        'desc' => 'Ada surat / dispensasi',       'val' => $stats['izin'],       'icon' => 'fa-envelope-open-text', 'icon_bg' => 'from-sky-400 to-blue-600 text-white shadow-sky-200', 'active_border' => 'border-sky-400 ring-2 ring-sky-300'],
                ['key' => 'sakit',      'label' => 'Sakit',             'desc' => 'Keterangan medis dokter',      'val' => $stats['sakit'],      'icon' => 'fa-heart-pulse',        'icon_bg' => 'from-yellow-300 to-amber-500 text-slate-900 shadow-yellow-200', 'active_border' => 'border-yellow-400 ring-2 ring-yellow-300'],
                ['key' => 'dinas_luar', 'label' => 'Dinas Luar',        'desc' => 'Tugas kedinasan luar',         'val' => $stats['dinas_luar'], 'icon' => 'fa-briefcase',          'icon_bg' => 'from-purple-400 to-indigo-600 text-white shadow-purple-200', 'active_border' => 'border-purple-400 ring-2 ring-purple-300'],
                ['key' => 'alpha',      'label' => 'Tanpa Keterangan',  'desc' => 'Alpha / tidak hadir',          'val' => $stats['alpha'],      'icon' => 'fa-circle-xmark',       'icon_bg' => 'from-rose-400 to-pink-600 text-white shadow-rose-200', 'active_border' => 'border-rose-400 ring-2 ring-rose-300'],
                ['key' => 'belum',      'label' => 'Belum Presensi',    'desc' => 'Menunggu scan/tap kartu',      'val' => $stats['belum'],      'icon' => 'fa-hourglass-half',     'icon_bg' => 'from-slate-400 to-slate-600 text-white shadow-slate-200', 'active_border' => 'border-slate-400 ring-2 ring-slate-300'],
            ];
        @endphp

        @foreach($statCards as $p)
        @php
            $isActive = ($statusFilter === $p['key']) || ($statusFilter === null && $p['key'] === null);
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => $p['key']]) }}" 
           class="clay-stat-card rounded-3xl p-5 md:p-6 flex flex-col justify-between relative overflow-hidden group
                  {{ $isActive ? $p['active_border'] . ' bg-blue-50/40 scale-[1.02]' : '' }}">
            
            <div class="flex items-center justify-between mb-4 relative z-10">
                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br flex items-center justify-center text-xl font-bold shadow-md p-3 {{ $p['icon_bg'] }}"
                     style="box-shadow: 3px 5px 12px rgba(0,0,0,0.15), inset 2px 2px 3px rgba(255,255,255,0.4);">
                    <i class="fas {{ $p['icon'] }}"></i>
                </div>
                <span class="text-3xl font-extrabold text-slate-800 leading-none font-mono" style="font-family: var(--clay-font-title);">
                    {{ number_format($p['val']) }}
                </span>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-700" style="font-family: var(--clay-font-title);">{{ $p['label'] }}</p>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $p['desc'] }}</p>
            </div>
        </a>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- CLAY TOOLBAR: FILTER & SEARCH                   --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="clay-card rounded-[2rem] p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-5 bg-white">
        <form method="GET" class="flex flex-wrap items-center gap-3.5 flex-1">
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">
            @if($statusFilter)
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif

            {{-- Classroom Filter if Siswa --}}
            @if($group === 'siswa' && $classrooms->isNotEmpty())
            <div class="min-w-[220px]">
                <select name="classroom_id" onchange="this.form.submit()"
                        class="w-full clay-pill-soft rounded-2xl px-5 py-3 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 cursor-pointer shadow-sm">
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
            <div class="relative flex-1 min-w-[240px] max-w-md">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NISN, NIP, atau kode..."
                       class="w-full clay-pill-soft rounded-2xl pl-11 pr-5 py-3 text-xs font-bold text-slate-800 placeholder:text-slate-400 focus:ring-4 focus:ring-indigo-100 shadow-sm outline-none transition">
                <i class="fas fa-search absolute left-4.5 top-3.5 text-indigo-500 text-xs"></i>
            </div>

            <button type="submit" class="clay-btn-primary px-6 py-3 text-xs font-extrabold uppercase tracking-wider rounded-2xl shadow-md transition active:scale-95">
                Filter
            </button>

            @if($search || $classroomId || $statusFilter)
            <a href="{{ route('admin.attendance.monitoring', ['group' => $group, 'school_id' => $schoolId, 'date' => $date]) }}" 
               class="clay-pill-soft px-5 py-3 text-slate-600 text-xs font-bold rounded-2xl transition hover:bg-slate-100 shadow-sm">
                <i class="fas fa-rotate-left mr-1.5"></i> Reset
            </a>
            @endif
        </form>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('admin.attendance.bulk', ['group' => $group, 'school_id' => $schoolId, 'date' => $date, 'classroom_id' => $classroomId]) }}" 
               class="clay-btn-amber inline-flex items-center gap-2.5 px-6 py-3 rounded-2xl text-xs font-extrabold uppercase tracking-wider shadow-md transition active:scale-95">
                <i class="fas fa-table-list text-sm"></i>
                <span>Input Massal</span>
            </a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- PLAYFUL CLAY DATA TABLE                         --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="clay-card rounded-[2.5rem] overflow-hidden bg-white">
        {{-- Card Header --}}
        <div class="px-7 py-6 border-b border-slate-100 flex items-center justify-between flex-wrap gap-4 bg-gradient-to-r from-blue-50/50 via-indigo-50/30 to-purple-50/30">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center font-bold text-xl shadow-md shrink-0 mr-4"
                     style="box-shadow: 3px 5px 12px rgba(67, 97, 238, 0.35);">
                    <i class="fas fa-users-viewfinder"></i>
                </div>
                <div>
                    <h2 class="text-lg md:text-xl font-extrabold tracking-tight text-slate-800 leading-tight" style="font-family: var(--clay-font-title);">
                        Daftar Presensi {{ ucfirst($group) }}
                    </h2>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} &middot; {{ $selectedSchool->name ?? '' }}
                    </p>
                </div>
            </div>
            <span class="text-xs font-extrabold text-indigo-700 bg-indigo-100/80 px-4 py-2 rounded-2xl shadow-xs border border-indigo-200">
                Total <b>{{ $items->count() }}</b> Data Terdaftar
            </span>
        </div>

        @if($items->isEmpty())
        <div class="py-24 text-center text-slate-400">
            <div class="w-16 h-16 bg-amber-100 rounded-3xl border-2 border-amber-200 flex items-center justify-center mx-auto mb-4 text-amber-600 text-3xl shadow-md">
                <i class="fas fa-user-slash"></i>
            </div>
            <p class="text-lg text-slate-800 font-extrabold" style="font-family: var(--clay-font-title);">Tidak ada data presensi yang sesuai dengan filter.</p>
            <p class="text-xs text-slate-500 font-semibold mt-1">Silakan sesuaikan filter rombel, tanggal, atau status di atas.</p>
        </div>
        @else
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse table-fixed">
                <thead class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white border-b-2 border-slate-900">
                    <tr class="whitespace-nowrap">
                        <th class="py-4 pl-5 pr-2 text-center text-xs font-extrabold uppercase tracking-wider w-12 text-yellow-300">No</th>
                        <th class="py-4 px-3 text-left text-xs font-extrabold uppercase tracking-wider w-[23%]">Nama & Identitas</th>
                        <th class="py-4 px-2 text-left text-xs font-extrabold uppercase tracking-wider w-[14%]">Unit / Rombel</th>
                        <th class="py-4 px-2 text-center text-xs font-extrabold uppercase tracking-wider w-[15%]">Status Kehadiran</th>
                        <th class="py-4 px-1.5 text-center text-xs font-extrabold uppercase tracking-wider w-[7%]">Masuk</th>
                        <th class="py-4 px-1.5 text-center text-xs font-extrabold uppercase tracking-wider w-[7%]">Pulang</th>
                        <th class="py-4 px-1.5 text-center text-xs font-extrabold uppercase tracking-wider w-[8%]">Metode</th>
                        <th class="py-4 px-2 text-left text-xs font-extrabold uppercase tracking-wider w-[11%]">Keterangan</th>
                        <th class="py-4 pl-2 pr-6 text-center text-xs font-extrabold uppercase tracking-wider w-[15%]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($items as $idx => $it)
                    <tr class="hover:bg-indigo-50/50 transition-colors {{ $it->status === 'belum' ? 'bg-slate-50/40' : '' }}" id="row-person-{{ $it->person_id }}">
                        <td class="py-4 pl-5 pr-2 text-center text-xs text-slate-600 font-mono font-extrabold whitespace-nowrap">{{ $idx + 1 }}</td>
                        <td class="py-4 px-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                {{-- Playful Clay Avatar --}}
                                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-yellow-300 to-amber-500 text-slate-900 border-2 border-white flex items-center justify-center font-extrabold text-xs shrink-0 shadow-sm overflow-hidden">
                                    @if(!empty($it->photo_url))
                                        <img src="{{ $it->photo_url }}" class="w-full h-full object-cover" alt="{{ $it->name }}">
                                    @else
                                        <span class="text-slate-900 font-black text-xs">{{ strtoupper(substr($it->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-extrabold text-slate-800 text-xs md:text-sm leading-tight truncate" title="{{ $it->name }}">{{ $it->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono font-bold mt-0.5 truncate">{{ $it->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-2">
                            <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 rounded-xl text-[11px] font-bold text-slate-700 truncate max-w-full" title="{{ $it->info }}">
                                {{ $it->info }}
                            </span>
                        </td>
                        <td class="py-4 px-2 text-center">
                            @php
                                $badgeStyle = match($it->status) {
                                    'hadir'      => 'bg-gradient-to-r from-emerald-400 to-teal-500 text-white shadow-emerald-200',
                                    'terlambat'  => 'bg-gradient-to-r from-amber-400 to-orange-400 text-slate-900 shadow-amber-200',
                                    'izin'       => 'bg-gradient-to-r from-sky-400 to-blue-500 text-white shadow-sky-200',
                                    'sakit'      => 'bg-gradient-to-r from-yellow-300 to-amber-400 text-slate-900 shadow-yellow-200',
                                    'dinas_luar' => 'bg-gradient-to-r from-purple-400 to-indigo-500 text-white shadow-purple-200',
                                    'cuti'       => 'bg-gradient-to-r from-indigo-400 to-blue-600 text-white shadow-indigo-200',
                                    'alpha'      => 'bg-gradient-to-r from-rose-400 to-pink-500 text-white shadow-rose-200',
                                    default      => 'bg-slate-200 text-slate-700 shadow-slate-100'
                                };
                            @endphp
                            <span class="clay-badge inline-flex items-center justify-center px-4 py-1.5 rounded-2xl text-[11px] font-extrabold uppercase tracking-normal whitespace-nowrap leading-tight {{ $badgeStyle }}">
                                {{ $it->status === 'belum' ? 'Belum Absen' : ucfirst(str_replace('_', ' ', $it->status)) }}
                            </span>
                        </td>
                        <td class="py-4 px-1.5 text-center text-xs font-mono font-extrabold text-slate-800 whitespace-nowrap">
                            {{ $it->time_in }}
                        </td>
                        <td class="py-4 px-1.5 text-center text-xs font-mono font-extrabold text-slate-800 whitespace-nowrap">
                            {{ $it->time_out }}
                        </td>
                        <td class="py-4 px-1.5 text-center whitespace-nowrap">
                            @if($it->recorded_via === 'gps')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold bg-blue-100 text-blue-700 border border-blue-200 whitespace-nowrap">
                                    <i class="fas fa-location-dot mr-1"></i> GPS
                                </span>
                            @elseif($it->recorded_via === 'rfid')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200 whitespace-nowrap">
                                    <i class="fas fa-id-card mr-1"></i> RFID
                                </span>
                            @elseif($it->recorded_via === 'manual')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">
                                    <i class="fas fa-pen mr-1"></i> MANUAL
                                </span>
                            @else
                                <span class="text-slate-400 font-bold text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-2 text-xs font-semibold text-slate-600 truncate">
                            {{ $it->notes ?? '-' }}
                        </td>
                        <td class="py-4 pl-2 pr-6 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-2">
                                {{-- Playful Clay Edit Button --}}
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
                                        class="clay-btn-amber px-3 py-1.5 rounded-xl font-extrabold transition active:scale-95 inline-flex items-center justify-center gap-1.5 text-xs" 
                                        title="Edit Presensi">
                                    <i class="fas fa-edit text-[11px]"></i>
                                    <span>Edit</span>
                                </button>

                                {{-- Playful Clay Hapus Button --}}
                                @if($it->attendance_id)
                                <form action="{{ route('admin.attendance.destroy', ['id' => $it->attendance_id, 'group' => $group]) }}" 
                                      method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/mereset data presensi {{ $it->name }}?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="clay-btn-coral px-3 py-1.5 rounded-xl font-extrabold transition active:scale-95 inline-flex items-center justify-center gap-1.5 text-xs" title="Hapus Presensi">
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
{{-- 🌟 PLAYFUL CLAY EDIT MODAL                      --}}
{{-- ═══════════════════════════════════════════════ --}}
<div id="editAttendanceModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="clay-card rounded-[2.5rem] max-w-lg w-full overflow-hidden transform transition-all bg-white shadow-2xl">
        {{-- Modal Header --}}
        <div class="px-8 py-6 bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-between border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center text-xl font-bold shadow-md">
                    <i class="fas fa-user-pen"></i>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold text-slate-800 leading-tight" style="font-family: var(--clay-font-title);">Edit Presensi Individu</h3>
                    <p class="text-xs text-indigo-600 font-bold mt-0.5" id="modal_person_subtitle">Nama & Rombel</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-10 h-10 rounded-full bg-white hover:bg-rose-500 hover:text-white text-slate-400 flex items-center justify-center transition shadow-sm border border-slate-200">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Modal Form --}}
        <form action="{{ route('admin.attendance.single.save') }}" method="POST" id="singleAttendanceForm" class="p-8 space-y-5">
            @csrf
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="person_id" id="modal_person_id">
            <input type="hidden" name="classroom_id" id="modal_classroom_id">

            {{-- Person Info Card --}}
            <div class="bg-indigo-50/60 border-2 border-indigo-100 rounded-3xl p-4 flex items-center gap-4 shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-yellow-300 to-amber-500 text-slate-900 font-extrabold flex items-center justify-center text-sm shrink-0 overflow-hidden shadow-sm" id="modal_avatar_container">
                    <span id="modal_avatar_initial">YZ</span>
                </div>
                <div class="min-w-0">
                    <div class="font-extrabold text-slate-800 text-base truncate" id="modal_person_name">Nama Pengguna</div>
                    <div class="text-xs text-indigo-500 font-mono font-bold" id="modal_person_code">NISN / NIP</div>
                </div>
            </div>

            {{-- Status Selector Cards --}}
            <div>
                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2.5" style="font-family: var(--clay-font-title);">
                    Pilih Status Kehadiran:
                </label>
                <div class="grid grid-cols-3 gap-2.5">
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="hadir" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-100 text-center text-xs font-extrabold text-slate-600 bg-slate-50 peer-checked:bg-emerald-500 peer-checked:border-emerald-600 peer-checked:text-white peer-checked:shadow-md transition">
                            <i class="fas fa-circle-check block text-lg mb-1"></i>
                            Hadir
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="terlambat" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-100 text-center text-xs font-extrabold text-slate-600 bg-slate-50 peer-checked:bg-amber-400 peer-checked:border-amber-500 peer-checked:text-slate-900 peer-checked:shadow-md transition">
                            <i class="fas fa-clock block text-lg mb-1"></i>
                            Terlambat
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="izin" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-100 text-center text-xs font-extrabold text-slate-600 bg-slate-50 peer-checked:bg-sky-500 peer-checked:border-sky-600 peer-checked:text-white peer-checked:shadow-md transition">
                            <i class="fas fa-envelope block text-lg mb-1"></i>
                            Izin
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="sakit" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-100 text-center text-xs font-extrabold text-slate-600 bg-slate-50 peer-checked:bg-yellow-400 peer-checked:border-yellow-500 peer-checked:text-slate-900 peer-checked:shadow-md transition">
                            <i class="fas fa-heart-pulse block text-lg mb-1"></i>
                            Sakit
                        </div>
                    </label>
                    @if($group !== 'siswa')
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="dinas_luar" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-100 text-center text-xs font-extrabold text-slate-600 bg-slate-50 peer-checked:bg-purple-500 peer-checked:border-purple-600 peer-checked:text-white peer-checked:shadow-md transition">
                            <i class="fas fa-briefcase block text-lg mb-1"></i>
                            Dinas Luar
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="cuti" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-100 text-center text-xs font-extrabold text-slate-600 bg-slate-50 peer-checked:bg-indigo-500 peer-checked:border-indigo-600 peer-checked:text-white peer-checked:shadow-md transition">
                            <i class="fas fa-calendar-xmark block text-lg mb-1"></i>
                            Cuti
                        </div>
                    </label>
                    @endif
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="alpha" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-100 text-center text-xs font-extrabold text-slate-600 bg-slate-50 peer-checked:bg-rose-500 peer-checked:border-rose-600 peer-checked:text-white peer-checked:shadow-md transition">
                            <i class="fas fa-circle-xmark block text-lg mb-1"></i>
                            Alpha
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="belum" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-100 text-center text-xs font-extrabold text-slate-600 bg-slate-50 peer-checked:bg-slate-700 peer-checked:border-slate-800 peer-checked:text-white peer-checked:shadow-md transition">
                            <i class="fas fa-hourglass block text-lg mb-1"></i>
                            Belum Absen
                        </div>
                    </label>
                </div>
            </div>

            {{-- Jam Masuk & Jam Pulang --}}
            <div class="grid grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>Jam Masuk:</span>
                        <button type="button" onclick="document.getElementById('modal_time_in').value='07:15'" class="text-[10px] text-indigo-600 font-bold underline hover:text-indigo-800">
                            07:15
                        </button>
                    </label>
                    <input type="time" name="time_in" id="modal_time_in"
                           class="w-full clay-pill-soft rounded-2xl px-5 py-3 text-xs font-mono font-extrabold text-slate-800 focus:ring-4 focus:ring-indigo-100 shadow-xs outline-none">
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>Jam Pulang:</span>
                        <button type="button" onclick="document.getElementById('modal_time_out').value='15:00'" class="text-[10px] text-indigo-600 font-bold underline hover:text-indigo-800">
                            15:00
                        </button>
                    </label>
                    <input type="time" name="time_out" id="modal_time_out"
                           class="w-full clay-pill-soft rounded-2xl px-5 py-3 text-xs font-mono font-extrabold text-slate-800 focus:ring-4 focus:ring-indigo-100 shadow-xs outline-none">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                    Keterangan / Catatan:
                </label>
                <textarea name="notes" id="modal_notes" rows="2" placeholder="Tuliskan keterangan opsional..."
                          class="w-full clay-pill-soft rounded-2xl p-4 text-xs font-semibold text-slate-800 focus:ring-4 focus:ring-indigo-100 shadow-xs outline-none"></textarea>
            </div>

            {{-- Modal Actions --}}
            <div class="flex items-center justify-end gap-3.5 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" 
                        class="clay-pill-soft px-6 py-3.5 text-slate-600 rounded-2xl text-xs font-extrabold uppercase tracking-wider hover:bg-slate-100 transition shadow-sm">
                    Batal
                </button>
                <button type="submit" id="modalSaveBtn"
                        class="clay-btn-primary px-8 py-3.5 rounded-2xl text-xs font-extrabold uppercase tracking-wider shadow-lg transition active:scale-95 flex items-center gap-2.5">
                    <i class="fas fa-save text-sm"></i>
                    <span>Simpan Perubahan</span>
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
        avatarContainer.innerHTML = `<span class="text-slate-900 font-extrabold text-sm">${(data.name || 'AB').substring(0, 2).toUpperCase()}</span>`;
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
