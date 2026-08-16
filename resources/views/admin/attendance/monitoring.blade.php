@extends('layouts.admin')

@section('title', 'Monitoring Presensi - Haute Academic Suite')

@push('styles')
<style>
    .luxury-stat-card {
        background: rgba(13, 20, 35, 0.65) !important;
        backdrop-filter: blur(24px) !important;
        -webkit-backdrop-filter: blur(24px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6), inset 0 1px 1px rgba(255, 255, 255, 0.08) !important;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .luxury-stat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(212, 175, 55, 0.5) !important;
        box-shadow: 0 25px 55px rgba(0, 0, 0, 0.75), 0 0 25px rgba(212, 175, 55, 0.15) !important;
    }
    .luxury-table-container {
        background: rgba(10, 15, 26, 0.8) !important;
        backdrop-filter: blur(28px) !important;
        -webkit-backdrop-filter: blur(28px) !important;
        border: 1px solid rgba(212, 175, 55, 0.25) !important;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), inset 0 1px 1px rgba(255, 255, 255, 0.1) !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Unified Luxury Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- LIQUID GLASS STAT METRIC CARDS (JEWEL ACCENTS) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        @php
            $statCards = [
                ['key' => null,         'label' => 'Total Terdaftar',  'desc' => 'Seluruh ' . $group . ' aktif',  'val' => $stats['total'],      'icon' => 'fa-users',              'glow' => '#6366f1', 'icon_bg' => 'from-indigo-500/20 to-indigo-900/40 border-indigo-400/40 text-indigo-300'],
                ['key' => 'hadir',      'label' => 'Hadir Tepat',      'desc' => 'Presensi sebelum batas',        'val' => $stats['hadir'],      'icon' => 'fa-circle-check',       'glow' => '#10b981', 'icon_bg' => 'from-emerald-500/20 to-emerald-900/40 border-emerald-400/40 text-emerald-300'],
                ['key' => 'terlambat',  'label' => 'Terlambat',        'desc' => 'Lewat jam toleransi',          'val' => $stats['terlambat'],  'icon' => 'fa-clock',              'glow' => '#f59e0b', 'icon_bg' => 'from-amber-500/20 to-amber-900/40 border-amber-400/40 text-amber-300'],
                ['key' => 'izin',       'label' => 'Izin Resmi',        'desc' => 'Ada surat / dispensasi',       'val' => $stats['izin'],       'icon' => 'fa-envelope-open-text', 'glow' => '#3b82f6', 'icon_bg' => 'from-blue-500/20 to-blue-900/40 border-blue-400/40 text-blue-300'],
                ['key' => 'sakit',      'label' => 'Sakit',             'desc' => 'Keterangan medis',             'val' => $stats['sakit'],      'icon' => 'fa-heart-pulse',        'glow' => '#eab308', 'icon_bg' => 'from-yellow-500/20 to-yellow-900/40 border-yellow-400/40 text-yellow-300'],
                ['key' => 'dinas_luar', 'label' => 'Dinas Luar',        'desc' => 'Tugas kedinasan',              'val' => $stats['dinas_luar'], 'icon' => 'fa-briefcase',          'glow' => '#a855f7', 'icon_bg' => 'from-purple-500/20 to-purple-900/40 border-purple-400/40 text-purple-300'],
                ['key' => 'alpha',      'label' => 'Tanpa Keterangan',  'desc' => 'Alpha / absen',                'val' => $stats['alpha'],      'icon' => 'fa-circle-xmark',       'glow' => '#f43f5e', 'icon_bg' => 'from-rose-500/20 to-rose-900/40 border-rose-400/40 text-rose-300'],
                ['key' => 'belum',      'label' => 'Belum Presensi',    'desc' => 'Menunggu scan/tap',            'val' => $stats['belum'],      'icon' => 'fa-hourglass-half',     'glow' => '#94a3b8', 'icon_bg' => 'from-slate-500/20 to-slate-900/40 border-slate-400/40 text-slate-300'],
            ];
        @endphp

        @foreach($statCards as $p)
        @php
            $isActive = ($statusFilter === $p['key']) || ($statusFilter === null && $p['key'] === null);
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => $p['key']]) }}" 
           class="luxury-stat-card rounded-3xl p-5 md:p-6 flex flex-col justify-between relative overflow-hidden group
                  {{ $isActive ? 'border-amber-400/80 bg-amber-950/20 ring-1 ring-amber-400/50 scale-[1.02]' : '' }}">
            
            {{-- Ambient Jewel Glow --}}
            <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full blur-2xl opacity-20 pointer-events-none" style="background: {{ $p['glow'] }};"></div>

            <div class="flex items-center justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br border flex items-center justify-center text-lg font-black shadow-lg {{ $p['icon_bg'] }}">
                    <i class="fas {{ $p['icon'] }}"></i>
                </div>
                <span class="text-3xl font-serif font-bold text-white leading-none font-mono">
                    {{ number_format($p['val']) }}
                </span>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-200">{{ $p['label'] }}</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5">{{ $p['desc'] }}</p>
            </div>
        </a>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TOOLBAR: FILTER & SEARCH (LIQUID GLASS SUITE)   --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="luxury-glass-panel rounded-[2rem] p-6 shadow-2xl flex flex-col lg:flex-row lg:items-center justify-between gap-5 text-white">
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
                        class="w-full bg-[#0b101c]/90 border border-amber-400/30 rounded-2xl px-5 py-3 text-xs font-bold text-amber-200 focus:ring-2 focus:ring-amber-400 cursor-pointer shadow-lg">
                    <option value="" class="bg-[#0b101c] text-white">🏛️ Semua Rombel / Kelas</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }} class="bg-[#0b101c] text-white">
                            {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[240px] max-w-md">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NISN, NIP, atau kode..."
                       class="w-full bg-[#0b101c]/90 border border-white/15 rounded-2xl pl-11 pr-5 py-3 text-xs font-medium text-white placeholder:text-slate-500 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30 shadow-lg outline-none transition">
                <i class="fas fa-search absolute left-4.5 top-3.5 text-amber-400/70 text-xs"></i>
            </div>

            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black text-xs font-black uppercase tracking-wider rounded-2xl border border-white/40 shadow-lg hover:shadow-amber-500/20 transition active:scale-95">
                Filter
            </button>

            @if($search || $classroomId || $statusFilter)
            <a href="{{ route('admin.attendance.monitoring', ['group' => $group, 'school_id' => $schoolId, 'date' => $date]) }}" 
               class="px-5 py-3 bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-bold rounded-2xl border border-white/10 transition shadow-sm">
                <i class="fas fa-rotate-left mr-1.5"></i> Reset
            </a>
            @endif
        </form>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('admin.attendance.bulk', ['group' => $group, 'school_id' => $schoolId, 'date' => $date, 'classroom_id' => $classroomId]) }}" 
               class="inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black rounded-2xl text-xs font-black uppercase tracking-wider border border-white/40 shadow-lg hover:shadow-amber-500/30 transition active:scale-95">
                <i class="fas fa-table text-sm text-black"></i>
                <span>Input Massal</span>
            </a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- LIQUID OBSIDIAN DATA TABLE                      --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="luxury-table-container rounded-[2.5rem] overflow-hidden text-white">
        {{-- Card Header with Generous Margin --}}
        <div class="px-7 py-6 border-b border-white/10 flex items-center justify-between flex-wrap gap-4 bg-black/20">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-200 via-amber-400 to-yellow-600 text-black flex items-center justify-center font-black text-lg border border-white/40 shadow-lg shrink-0 mr-4">
                    <i class="fas fa-users-viewfinder"></i>
                </div>
                <div>
                    <h2 class="font-serif text-lg md:text-xl font-bold tracking-tight text-white leading-tight">
                        Daftar Presensi {{ ucfirst($group) }}
                    </h2>
                    <p class="text-xs text-slate-400 font-medium mt-1">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} &middot; {{ $selectedSchool->name ?? '' }}
                    </p>
                </div>
            </div>
            <span class="text-xs font-bold text-amber-200 bg-amber-400/10 border border-amber-400/30 px-4 py-2 rounded-full shadow-xs tracking-wider">
                Total <b class="text-white">{{ $items->count() }}</b> Data Terdaftar
            </span>
        </div>

        @if($items->isEmpty())
        <div class="py-24 text-center text-slate-400">
            <div class="w-16 h-16 bg-amber-400/10 rounded-3xl border border-amber-400/30 flex items-center justify-center mx-auto mb-4 text-amber-300 text-3xl shadow-lg">
                <i class="fas fa-user-slash"></i>
            </div>
            <p class="font-serif text-lg text-white font-medium">Tidak ada data presensi yang sesuai dengan filter.</p>
            <p class="text-xs text-slate-400 mt-1">Silakan sesuaikan filter rombel, tanggal, atau status di atas.</p>
        </div>
        @else
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse table-fixed">
                <thead class="bg-[#070b14] border-b border-amber-400/30 text-amber-300">
                    <tr class="whitespace-nowrap">
                        <th class="py-4 pl-5 pr-2 text-center text-[11px] font-bold uppercase tracking-[0.2em] w-12 text-amber-400/70">No</th>
                        <th class="py-4 px-3 text-left text-[11px] font-bold uppercase tracking-[0.2em] w-[23%] text-amber-300">Nama & Identitas</th>
                        <th class="py-4 px-2 text-left text-[11px] font-bold uppercase tracking-[0.2em] w-[14%] text-amber-300">Unit / Rombel</th>
                        <th class="py-4 px-2 text-center text-[11px] font-bold uppercase tracking-[0.2em] w-[15%] text-amber-300">Status Kehadiran</th>
                        <th class="py-4 px-1.5 text-center text-[11px] font-bold uppercase tracking-[0.2em] w-[7%] text-amber-300">Masuk</th>
                        <th class="py-4 px-1.5 text-center text-[11px] font-bold uppercase tracking-[0.2em] w-[7%] text-amber-300">Pulang</th>
                        <th class="py-4 px-1.5 text-center text-[11px] font-bold uppercase tracking-[0.2em] w-[8%] text-amber-300">Metode</th>
                        <th class="py-4 px-2 text-left text-[11px] font-bold uppercase tracking-[0.2em] w-[11%] text-amber-300">Keterangan</th>
                        <th class="py-4 pl-2 pr-6 text-center text-[11px] font-bold uppercase tracking-[0.2em] w-[15%] text-amber-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($items as $idx => $it)
                    <tr class="hover:bg-white/[0.04] transition-colors {{ $it->status === 'belum' ? 'bg-white/[0.01]' : '' }}" id="row-person-{{ $it->person_id }}">
                        <td class="py-4 pl-5 pr-2 text-center text-xs text-slate-400 font-mono font-bold whitespace-nowrap">{{ $idx + 1 }}</td>
                        <td class="py-4 px-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                {{-- Liquid Gold Avatar --}}
                                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-700 text-black border border-white/40 flex items-center justify-center font-black text-xs shrink-0 shadow-sm overflow-hidden">
                                    @if(!empty($it->photo_url))
                                        <img src="{{ $it->photo_url }}" class="w-full h-full object-cover" alt="{{ $it->name }}">
                                    @else
                                        <span class="text-black font-black text-xs">{{ strtoupper(substr($it->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-white text-xs md:text-sm leading-tight truncate" title="{{ $it->name }}">{{ $it->name }}</div>
                                    <div class="text-[10px] text-amber-200/60 font-mono mt-0.5 truncate">{{ $it->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-2">
                            <span class="inline-flex items-center px-2.5 py-1 bg-white/5 border border-white/10 rounded-xl text-[11px] font-medium text-slate-200 truncate max-w-full" title="{{ $it->info }}">
                                {{ $it->info }}
                            </span>
                        </td>
                        <td class="py-4 px-2 text-center">
                            @php
                                $badgeStyle = match($it->status) {
                                    'hadir'      => 'bg-emerald-500/15 border-emerald-400/50 text-emerald-300 shadow-emerald-500/10',
                                    'terlambat'  => 'bg-amber-500/15 border-amber-400/50 text-amber-300 shadow-amber-500/10',
                                    'izin'       => 'bg-blue-500/15 border-blue-400/50 text-blue-300 shadow-blue-500/10',
                                    'sakit'      => 'bg-yellow-500/15 border-yellow-400/50 text-yellow-300 shadow-yellow-500/10',
                                    'dinas_luar' => 'bg-purple-500/15 border-purple-400/50 text-purple-300 shadow-purple-500/10',
                                    'cuti'       => 'bg-indigo-500/15 border-indigo-400/50 text-indigo-300 shadow-indigo-500/10',
                                    'alpha'      => 'bg-rose-500/15 border-rose-400/50 text-rose-300 shadow-rose-500/10',
                                    default      => 'bg-slate-500/10 border-slate-400/30 text-slate-300'
                                };
                            @endphp
                            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-xl text-[11px] font-bold uppercase tracking-normal border shadow-xs whitespace-nowrap leading-tight {{ $badgeStyle }}">
                                {{ $it->status === 'belum' ? 'Belum Absen' : ucfirst(str_replace('_', ' ', $it->status)) }}
                            </span>
                        </td>
                        <td class="py-4 px-1.5 text-center text-xs font-mono font-bold text-slate-200 whitespace-nowrap">
                            {{ $it->time_in }}
                        </td>
                        <td class="py-4 px-1.5 text-center text-xs font-mono font-bold text-slate-200 whitespace-nowrap">
                            {{ $it->time_out }}
                        </td>
                        <td class="py-4 px-1.5 text-center whitespace-nowrap">
                            @if($it->recorded_via === 'gps')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-blue-500/15 text-blue-300 border border-blue-400/40 shadow-xs whitespace-nowrap">
                                    <i class="fas fa-location-dot mr-1"></i> GPS
                                </span>
                            @elseif($it->recorded_via === 'rfid')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-amber-500/15 text-amber-300 border border-amber-400/40 shadow-xs whitespace-nowrap">
                                    <i class="fas fa-id-card mr-1"></i> RFID
                                </span>
                            @elseif($it->recorded_via === 'manual')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-slate-500/15 text-slate-300 border border-slate-400/40 shadow-xs whitespace-nowrap">
                                    <i class="fas fa-pen mr-1"></i> MANUAL
                                </span>
                            @else
                                <span class="text-slate-500 font-bold text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-2 text-xs font-medium text-slate-300 truncate">
                            {{ $it->notes ?? '-' }}
                        </td>
                        <td class="py-4 pl-2 pr-6 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-2">
                                {{-- Liquid Gold Edit Button --}}
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
                                        class="px-3 py-1.5 bg-gradient-to-r from-amber-400/20 to-yellow-500/10 hover:from-amber-400 hover:to-yellow-500 text-amber-200 hover:text-black rounded-xl font-bold border border-amber-400/50 shadow-xs transition active:scale-95 inline-flex items-center justify-center gap-1.5 text-xs" 
                                        title="Edit Presensi">
                                    <i class="fas fa-edit text-[11px]"></i>
                                    <span>Edit</span>
                                </button>

                                {{-- Liquid Ruby Hapus Button --}}
                                @if($it->attendance_id)
                                <form action="{{ route('admin.attendance.destroy', ['id' => $it->attendance_id, 'group' => $group]) }}" 
                                      method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/mereset data presensi {{ $it->name }}?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-300 hover:text-white rounded-xl font-bold border border-rose-400/40 shadow-xs transition active:scale-95 inline-flex items-center justify-center gap-1.5 text-xs" title="Hapus Presensi">
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
{{-- 🌟 INTERACTIVE LIQUID GLASS EDIT MODAL          --}}
{{-- ═══════════════════════════════════════════════ --}}
<div id="editAttendanceModal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
    <div class="luxury-glass-panel rounded-[2.5rem] max-w-lg w-full overflow-hidden transform transition-all text-white border border-amber-400/40 shadow-2xl">
        {{-- Modal Header --}}
        <div class="px-8 py-6 bg-black/40 flex items-center justify-between border-b border-white/10">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-200 via-amber-400 to-yellow-600 text-black flex items-center justify-center text-xl font-black border border-white/40 shadow-lg">
                    <i class="fas fa-user-edit text-black"></i>
                </div>
                <div>
                    <h3 class="font-serif text-xl font-bold text-white leading-tight">Edit Presensi Individu</h3>
                    <p class="text-xs text-amber-300/80 font-medium mt-0.5" id="modal_person_subtitle">Nama & Rombel</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-10 h-10 rounded-full bg-white/5 hover:bg-amber-400 hover:text-black text-slate-300 flex items-center justify-center transition border border-white/15">
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

            {{-- Person Info Banner --}}
            <div class="bg-amber-400/10 border border-amber-400/30 rounded-3xl p-4 flex items-center gap-4 shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-200 via-amber-400 to-yellow-600 text-black font-black flex items-center justify-center text-sm border border-white/40 shrink-0 overflow-hidden shadow-sm" id="modal_avatar_container">
                    <span id="modal_avatar_initial">YZ</span>
                </div>
                <div class="min-w-0">
                    <div class="font-bold text-white text-base truncate" id="modal_person_name">Nama Pengguna</div>
                    <div class="text-xs text-amber-200/70 font-mono" id="modal_person_code">NISN / NIP</div>
                </div>
            </div>

            {{-- Status Selector Cards --}}
            <div>
                <label class="block text-xs font-bold text-amber-300/80 uppercase tracking-[0.2em] mb-2.5">
                    Pilih Status Kehadiran:
                </label>
                <div class="grid grid-cols-3 gap-2.5">
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="hadir" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border border-white/10 text-center text-xs font-bold text-slate-300 bg-white/5 peer-checked:bg-emerald-500/20 peer-checked:border-emerald-400 peer-checked:text-emerald-300 peer-checked:shadow-lg transition">
                            <i class="fas fa-circle-check block text-lg mb-1"></i>
                            Hadir
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="terlambat" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border border-white/10 text-center text-xs font-bold text-slate-300 bg-white/5 peer-checked:bg-amber-500/20 peer-checked:border-amber-400 peer-checked:text-amber-300 peer-checked:shadow-lg transition">
                            <i class="fas fa-clock block text-lg mb-1"></i>
                            Terlambat
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="izin" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border border-white/10 text-center text-xs font-bold text-slate-300 bg-white/5 peer-checked:bg-blue-500/20 peer-checked:border-blue-400 peer-checked:text-blue-300 peer-checked:shadow-lg transition">
                            <i class="fas fa-envelope block text-lg mb-1"></i>
                            Izin
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="sakit" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border border-white/10 text-center text-xs font-bold text-slate-300 bg-white/5 peer-checked:bg-yellow-500/20 peer-checked:border-yellow-400 peer-checked:text-yellow-300 peer-checked:shadow-lg transition">
                            <i class="fas fa-heart-pulse block text-lg mb-1"></i>
                            Sakit
                        </div>
                    </label>
                    @if($group !== 'siswa')
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="dinas_luar" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border border-white/10 text-center text-xs font-bold text-slate-300 bg-white/5 peer-checked:bg-purple-500/20 peer-checked:border-purple-400 peer-checked:text-purple-300 peer-checked:shadow-lg transition">
                            <i class="fas fa-briefcase block text-lg mb-1"></i>
                            Dinas Luar
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="cuti" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border border-white/10 text-center text-xs font-bold text-slate-300 bg-white/5 peer-checked:bg-indigo-500/20 peer-checked:border-indigo-400 peer-checked:text-indigo-300 peer-checked:shadow-lg transition">
                            <i class="fas fa-calendar-xmark block text-lg mb-1"></i>
                            Cuti
                        </div>
                    </label>
                    @endif
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="alpha" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border border-white/10 text-center text-xs font-bold text-slate-300 bg-white/5 peer-checked:bg-rose-500/20 peer-checked:border-rose-400 peer-checked:text-rose-300 peer-checked:shadow-lg transition">
                            <i class="fas fa-circle-xmark block text-lg mb-1"></i>
                            Alpha
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="belum" class="sr-only peer modal-status-radio">
                        <div class="p-3.5 rounded-2xl border border-white/10 text-center text-xs font-bold text-slate-300 bg-white/5 peer-checked:bg-slate-500/30 peer-checked:border-slate-300 peer-checked:text-white peer-checked:shadow-lg transition">
                            <i class="fas fa-hourglass block text-lg mb-1"></i>
                            Belum Absen
                        </div>
                    </label>
                </div>
            </div>

            {{-- Jam Masuk & Jam Pulang --}}
            <div class="grid grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-[0.15em] mb-2 flex items-center justify-between">
                        <span>Jam Masuk:</span>
                        <button type="button" onclick="document.getElementById('modal_time_in').value='07:15'" class="text-[10px] text-amber-300 underline hover:text-amber-200">
                            07:15
                        </button>
                    </label>
                    <input type="time" name="time_in" id="modal_time_in"
                           class="w-full bg-[#0b101c]/90 border border-white/20 rounded-2xl px-5 py-3 text-xs font-mono font-bold text-white focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30 shadow-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-[0.15em] mb-2 flex items-center justify-between">
                        <span>Jam Pulang:</span>
                        <button type="button" onclick="document.getElementById('modal_time_out').value='15:00'" class="text-[10px] text-amber-300 underline hover:text-amber-200">
                            15:00
                        </button>
                    </label>
                    <input type="time" name="time_out" id="modal_time_out"
                           class="w-full bg-[#0b101c]/90 border border-white/20 rounded-2xl px-5 py-3 text-xs font-mono font-bold text-white focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30 shadow-sm outline-none">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-[0.15em] mb-2">
                    Keterangan / Catatan:
                </label>
                <textarea name="notes" id="modal_notes" rows="2" placeholder="Tuliskan keterangan opsional..."
                          class="w-full bg-[#0b101c]/90 border border-white/20 rounded-2xl p-4 text-xs font-medium text-white focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30 shadow-sm outline-none"></textarea>
            </div>

            {{-- Modal Actions --}}
            <div class="flex items-center justify-end gap-3.5 pt-4 border-t border-white/10">
                <button type="button" onclick="closeEditModal()" 
                        class="px-6 py-3.5 bg-white/5 hover:bg-white/10 text-slate-300 rounded-2xl text-xs font-bold uppercase tracking-wider border border-white/10 transition shadow-sm">
                    Batal
                </button>
                <button type="submit" id="modalSaveBtn"
                        class="px-8 py-3.5 bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 hover:opacity-90 text-black rounded-2xl text-xs font-black uppercase tracking-wider border border-white/40 shadow-xl transition active:scale-95 flex items-center gap-2.5">
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
        avatarContainer.innerHTML = `<span class="text-black font-black text-sm">${(data.name || 'AB').substring(0, 2).toUpperCase()}</span>`;
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
