@extends('layouts.admin')

@section('title', 'Live Presensi Real-Time - Pusat Absensi')

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
        padding: 16px 18px;
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
    .edu-live-row {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 14px 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        transition: all 0.2s ease;
    }
    .edu-live-row:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
    }
    .edu-live-latest {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #ffffff 100%);
        border: 2px solid #f59e0b !important;
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.15), 0 0 0 3px rgba(254, 240, 138, 0.5) !important;
        position: relative;
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
</style>
@endpush

@section('content')
<div class="space-y-6 edu-font">
    {{-- Unified Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- STATISTIC METRIC CARDS                          --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 md:gap-4">
        {{-- 1. Total Target --}}
        <div class="edu-stat-card">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center text-base font-bold">
                    <i class="fas fa-users"></i>
                </div>
                <span class="text-2xl font-extrabold text-slate-800 leading-none font-mono" id="stat_total_target">{{ number_format($stats['total_target']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Total Terdaftar</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">{{ ucfirst($group) }} aktif</p>
            </div>
        </div>

        {{-- 2. Hadir --}}
        <div class="edu-stat-card">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-base font-bold">
                    <i class="fas fa-circle-check"></i>
                </div>
                <span class="text-2xl font-extrabold text-emerald-600 leading-none font-mono" id="stat_hadir">{{ number_format($stats['hadir']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Hadir Tepat</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Sebelum batas waktu</p>
            </div>
        </div>

        {{-- 3. Terlambat --}}
        <div class="edu-stat-card">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-base font-bold">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="text-2xl font-extrabold text-amber-600 leading-none font-mono" id="stat_terlambat">{{ number_format($stats['terlambat']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Terlambat</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Lewat toleransi</p>
            </div>
        </div>

        {{-- 4. Izin & Sakit --}}
        <div class="edu-stat-card">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-base font-bold">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <span class="text-2xl font-extrabold text-blue-600 leading-none font-mono" id="stat_izin_sakit">{{ number_format($stats['izin'] + $stats['sakit'] + $stats['dinas_luar'] + $stats['cuti']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Izin & Sakit</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Surat terlampir</p>
            </div>
        </div>

        {{-- 5. Alpha --}}
        <div class="edu-stat-card">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-base font-bold">
                    <i class="fas fa-circle-xmark"></i>
                </div>
                <span class="text-2xl font-extrabold text-rose-600 leading-none font-mono" id="stat_alpha">{{ number_format($stats['alpha']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Alpha</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Tanpa keterangan</p>
            </div>
        </div>

        {{-- 6. Belum Presensi --}}
        <div class="edu-stat-card">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center text-base font-bold">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <span class="text-2xl font-extrabold text-slate-600 leading-none font-mono" id="stat_belum">{{ number_format($stats['belum']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Belum Hadir</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Menunggu scan/tap</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- FULL-WIDTH LIVE ACTIVITY FEED CONTAINER         --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="edu-card p-6 md:p-8 bg-white">
        <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6 flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-rose-600"></span>
                </span>
                <div>
                    <h3 class="text-base md:text-lg font-bold text-slate-800 leading-snug">Live Activity Feed (Urutan Presensi Masuk)</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        Menampilkan seluruh riwayat presensi hari ini. Absen terbaru & nomor urut terbesar berada di paling atas.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-4 py-2 bg-slate-900 text-amber-300 rounded-xl text-xs font-mono font-bold" id="live_clock">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }} WIB
                </span>
                <button type="button" onclick="pollLiveData()" class="px-4 py-2 bg-amber-400 hover:bg-amber-500 text-slate-900 rounded-xl text-xs font-bold uppercase tracking-wider transition active:scale-95 flex items-center gap-1.5 shadow-2xs" title="Refresh Sekarang">
                    <i class="fas fa-rotate text-xs"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        {{-- Full-Width Live Stream Feed (Vertical List) --}}
        <div id="live_events_container" class="space-y-3.5 w-full">
            @forelse($liveEvents as $ev)
                @php
                    $isLatest = !empty($ev['is_latest']);
                    $seqNo = $ev['seq_no'] ?? '-';
                    $status = $ev['status'];
                    $badgeCls = match($status) {
                        'hadir'      => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'terlambat'  => 'bg-amber-100 text-amber-800 border-amber-300',
                        'izin'       => 'bg-blue-100 text-blue-800 border-blue-300',
                        'sakit'      => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        'dinas_luar' => 'bg-purple-100 text-purple-800 border-purple-300',
                        'cuti'       => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                        'alpha'      => 'bg-rose-100 text-rose-800 border-rose-300',
                        default      => 'bg-slate-100 text-slate-600 border-slate-300'
                    };
                @endphp

                <div class="edu-live-row w-full flex flex-col md:flex-row md:items-center justify-between gap-4 {{ $isLatest ? 'edu-live-latest' : '' }}">
                    {{-- Left Section: Seq No + Latest Tag + Avatar + Identity --}}
                    <div class="flex items-center gap-4 min-w-0 flex-1">
                        {{-- Nomor Urut Absen --}}
                        <div class="shrink-0 flex flex-col items-center">
                            @if($isLatest)
                                <span class="w-10 h-10 rounded-xl bg-amber-500 text-slate-900 font-extrabold text-sm flex items-center justify-center shadow-md border border-amber-300">
                                    #{{ $seqNo }}
                                </span>
                            @else
                                <span class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs font-mono flex items-center justify-center border border-slate-200">
                                    #{{ $seqNo }}
                                </span>
                            @endif
                        </div>

                        {{-- Avatar Profile --}}
                        <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden text-slate-700 shadow-2xs">
                            @if(!empty($ev['photo_url']))
                                <img src="{{ $ev['photo_url'] }}" class="w-full h-full object-cover" alt="{{ $ev['name'] }}">
                            @else
                                <span>{{ strtoupper(substr($ev['name'], 0, 2)) }}</span>
                            @endif
                        </div>

                        {{-- Name, Class/Subtitle, & Code --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-800 text-sm leading-snug">{{ $ev['name'] }}</span>
                                @if($isLatest)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-400 text-slate-900 shadow-xs animate-pulse">
                                        🔥 TERBARU
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 font-medium mt-0.5 flex items-center gap-2 flex-wrap">
                                <span>{{ $ev['subtitle'] }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="font-mono text-slate-400">{{ $ev['code'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Middle Section: Method & Time & Status --}}
                    <div class="flex items-center gap-4 shrink-0 flex-wrap justify-between md:justify-end">
                        {{-- Recorded Via (GPS/RFID/Manual) --}}
                        <div>
                            @if($ev['recorded_via'] === 'gps')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fas fa-location-dot mr-1.5"></i> GPS
                                </span>
                            @elseif($ev['recorded_via'] === 'rfid')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i class="fas fa-id-card mr-1.5"></i> RFID
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="fas fa-pen mr-1.5"></i> Manual
                                </span>
                            @endif
                        </div>

                        {{-- Time In --}}
                        <div class="text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 px-3 py-1 rounded-lg">
                            <i class="fas fa-clock text-slate-400 mr-1"></i> {{ $ev['time_in'] ?? '-' }} WIB
                        </div>

                        {{-- Status Badge --}}
                        <div>
                            <span class="edu-badge-status {{ $badgeCls }}">
                                {{ strtoupper($ev['status']) }}
                            </span>
                        </div>

                        {{-- Right Section: Action Buttons (Edit & Hapus) --}}
                        <div class="inline-flex items-center gap-2 pl-2 md:border-l md:border-slate-200">
                            {{-- Edit Button --}}
                            <button type="button" 
                                    onclick="openEditModal({{ json_encode([
                                        'person_id'    => $ev['person_id'] ?? null,
                                        'name'         => $ev['name'] ?? '',
                                        'code'         => $ev['code'] ?? '',
                                        'photo_url'    => $ev['photo_url'] ?? '',
                                        'info'         => $ev['subtitle'] ?? '',
                                        'classroom_id' => $ev['classroom_id'] ?? null,
                                        'attendance_id'=> $ev['id'] ?? null,
                                        'status'       => $ev['status'] ?? 'hadir',
                                        'time_in'      => $ev['raw_time_in'] ?? '',
                                        'time_out'     => $ev['raw_time_out'] ?? '',
                                        'notes'        => $ev['notes'] ?? '',
                                    ]) }})"
                                    class="edu-table-btn bg-amber-400 hover:bg-amber-500 text-slate-900 border-amber-500 shadow-2xs" 
                                    title="Edit Presensi">
                                <i class="fas fa-edit text-[11px]"></i>
                                <span>Edit</span>
                            </button>

                            {{-- Hapus Button --}}
                            <form action="{{ route('admin.attendance.destroy', ['id' => $ev['id'], 'group' => $group]) }}" 
                                  method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi {{ $ev['name'] }}?');" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="edu-table-btn bg-rose-600 hover:bg-rose-700 text-white border-rose-700 shadow-2xs" title="Hapus Presensi">
                                    <i class="fas fa-trash text-[11px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-20 text-center text-slate-400">
                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400 text-2xl">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <p class="text-base text-slate-700 font-bold">Belum ada aktivitas presensi masuk pada tanggal ini.</p>
                    <p class="text-xs text-slate-500 mt-1">Aktivitas presensi GPS, RFID, atau manual akan muncul secara real-time di sini.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- EDIT MODAL                                      --}}
{{-- ═══════════════════════════════════════════════ --}}
<div id="editAttendanceModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 edu-font">
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

let livePollingInterval = null;

function pollLiveData() {
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.stats) {
                document.getElementById('stat_total_target').innerText = Number(data.stats.total_target).toLocaleString();
                document.getElementById('stat_hadir').innerText = Number(data.stats.hadir).toLocaleString();
                document.getElementById('stat_terlambat').innerText = Number(data.stats.terlambat).toLocaleString();
                document.getElementById('stat_izin_sakit').innerText = Number(data.stats.izin + data.stats.sakit + data.stats.dinas_luar + data.stats.cuti).toLocaleString();
                document.getElementById('stat_alpha').innerText = Number(data.stats.alpha).toLocaleString();
                document.getElementById('stat_belum').innerText = Number(data.stats.belum).toLocaleString();
            }

            if (data.time_now) {
                document.getElementById('live_clock').innerText = data.time_now + ' WIB';
            }

            const container = document.getElementById('live_events_container');
            if (data.liveEvents && data.liveEvents.length > 0) {
                let html = '';
                data.liveEvents.forEach((ev, idx) => {
                    const initials = (ev.name || 'AB').substring(0, 2).toUpperCase();
                    const photoHtml = ev.photo_url 
                        ? `<img src="${ev.photo_url}" class="w-full h-full object-cover" alt="${ev.name}">`
                        : `<span>${initials}</span>`;

                    let viaBadge = '';
                    if (ev.recorded_via === 'gps') {
                        viaBadge = '<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fas fa-location-dot mr-1.5"></i> GPS</span>';
                    } else if (ev.recorded_via === 'rfid') {
                        viaBadge = '<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200"><i class="fas fa-id-card mr-1.5"></i> RFID</span>';
                    } else {
                        viaBadge = '<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200"><i class="fas fa-pen mr-1.5"></i> Manual</span>';
                    }

                    let statusClass = 'bg-slate-100 text-slate-600 border-slate-300';
                    if (ev.status === 'hadir') statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                    else if (ev.status === 'terlambat') statusClass = 'bg-amber-100 text-amber-800 border-amber-300';
                    else if (ev.status === 'izin') statusClass = 'bg-blue-100 text-blue-800 border-blue-300';
                    else if (ev.status === 'sakit') statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-300';
                    else if (ev.status === 'alpha') statusClass = 'bg-rose-100 text-rose-800 border-rose-300';

                    const isLatest = (idx === 0);
                    const latestRowClass = isLatest ? 'edu-live-latest' : '';
                    const latestBadge = isLatest ? '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-400 text-slate-900 shadow-xs animate-pulse">🔥 TERBARU</span>' : '';
                    const seqBadge = isLatest 
                        ? `<span class="w-10 h-10 rounded-xl bg-amber-500 text-slate-900 font-extrabold text-sm flex items-center justify-center shadow-md border border-amber-300">#${ev.seq_no}</span>`
                        : `<span class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs font-mono flex items-center justify-center border border-slate-200">#${ev.seq_no}</span>`;

                    const modalPayload = JSON.stringify({
                        person_id: ev.person_id || null,
                        name: ev.name || '',
                        code: ev.code || '',
                        photo_url: ev.photo_url || '',
                        info: ev.subtitle || '',
                        classroom_id: ev.classroom_id || null,
                        attendance_id: ev.id || null,
                        status: ev.status || 'hadir',
                        time_in: ev.raw_time_in || '',
                        time_out: ev.raw_time_out || '',
                        notes: ev.notes || '',
                    }).replace(/"/g, '&quot;');

                    const destroyUrl = "{{ url('/admin/attendance') }}/" + ev.id + "?group={{ $group }}";
                    const csrfToken = "{{ csrf_token() }}";

                    html += `
                        <div class="edu-live-row w-full flex flex-col md:flex-row md:items-center justify-between gap-4 ${latestRowClass}">
                            <div class="flex items-center gap-4 min-w-0 flex-1">
                                <div class="shrink-0 flex flex-col items-center">
                                    ${seqBadge}
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden text-slate-700 shadow-2xs">
                                    ${photoHtml}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-800 text-sm leading-snug">${ev.name}</span>
                                        ${latestBadge}
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium mt-0.5 flex items-center gap-2 flex-wrap">
                                        <span>${ev.subtitle}</span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="font-mono text-slate-400">${ev.code}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 shrink-0 flex-wrap justify-between md:justify-end">
                                <div>${viaBadge}</div>
                                <div class="text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 px-3 py-1 rounded-lg">
                                    <i class="fas fa-clock text-slate-400 mr-1"></i> ${ev.time_in || '-'} WIB
                                </div>
                                <div>
                                    <span class="edu-badge-status ${statusClass}">
                                        ${ev.status.toUpperCase()}
                                    </span>
                                </div>
                                <div class="inline-flex items-center gap-2 pl-2 md:border-l md:border-slate-200">
                                    <button type="button" 
                                            onclick='openEditModal(${modalPayload})'
                                            class="edu-table-btn bg-amber-400 hover:bg-amber-500 text-slate-900 border-amber-500 shadow-2xs" 
                                            title="Edit Presensi">
                                        <i class="fas fa-edit text-[11px]"></i>
                                        <span>Edit</span>
                                    </button>
                                    <form action="${destroyUrl}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ${ev.name}?');" class="inline">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="edu-table-btn bg-rose-600 hover:bg-rose-700 text-white border-rose-700 shadow-2xs" title="Hapus Presensi">
                                            <i class="fas fa-trash text-[11px]"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }
        }
    })
    .catch(err => console.error('Live polling error:', err));
}

document.addEventListener('DOMContentLoaded', () => {
    livePollingInterval = setInterval(pollLiveData, 4000);
});

window.addEventListener('beforeunload', () => {
    if (livePollingInterval) clearInterval(livePollingInterval);
});
</script>
@endsection
