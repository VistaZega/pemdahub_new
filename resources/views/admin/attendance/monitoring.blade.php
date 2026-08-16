@extends('layouts.admin')

@section('title', 'Monitoring Absensi Harian - Pusat Absensi')

@push('styles')
<style>
    .stat-metric-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-metric-card:hover {
        transform: translateY(-3px);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Unified LMS Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- STATISTIC METRIC CARDS (LMS NEO-BRUTALIST) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        @php
            $statPills = [
                ['key' => null,          'label' => 'Semua',       'val' => $stats['total'],      'icon' => 'fa-users',                 'bg' => 'bg-black text-amber-400'],
                ['key' => 'hadir',       'label' => 'Hadir',       'val' => $stats['hadir'],      'icon' => 'fa-circle-check',          'bg' => 'bg-emerald-300 text-black'],
                ['key' => 'terlambat',   'label' => 'Terlambat',   'val' => $stats['terlambat'],  'icon' => 'fa-clock',                 'bg' => 'bg-amber-300 text-black'],
                ['key' => 'izin',        'label' => 'Izin',        'val' => $stats['izin'],       'icon' => 'fa-envelope-open-text',    'bg' => 'bg-sky-300 text-black'],
                ['key' => 'sakit',       'label' => 'Sakit',       'val' => $stats['sakit'],      'icon' => 'fa-heart-pulse',           'bg' => 'bg-yellow-300 text-black'],
                ['key' => 'dinas_luar',  'label' => 'Dinas Luar',  'val' => $stats['dinas_luar'], 'icon' => 'fa-briefcase',             'bg' => 'bg-purple-300 text-black'],
                ['key' => 'alpha',       'label' => 'Alpha',       'val' => $stats['alpha'],      'icon' => 'fa-circle-xmark',          'bg' => 'bg-rose-400 text-black'],
                ['key' => 'belum',       'label' => 'Belum Absen', 'val' => $stats['belum'],      'icon' => 'fa-hourglass-half',        'bg' => 'bg-gray-100 text-black'],
            ];
        @endphp

        @foreach($statPills as $p)
        @php
            $isActive = ($statusFilter === $p['key']) || ($statusFilter === null && $p['key'] === null);
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => $p['key']]) }}" 
           class="stat-metric-card p-4 rounded-3xl border-2 border-black transition-all flex flex-col justify-between shadow-md {{ $p['bg'] }}
                  {{ $isActive ? 'ring-4 ring-black scale-[1.03]' : 'opacity-90 hover:opacity-100' }}">
            <div class="flex items-center justify-between text-[11px] font-black uppercase tracking-wider">
                <span>{{ $p['label'] }}</span>
                <i class="fas {{ $p['icon'] }} text-xs"></i>
            </div>
            <div class="text-2xl font-black mt-2 font-mono tracking-tight">{{ number_format($p['val']) }}</div>
        </a>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TOOLBAR: FILTER, SEARCH & ACTIONS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white p-5 rounded-3xl border-2 border-black shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-4">
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
                        class="w-full bg-white border-2 border-black rounded-2xl px-4 py-2.5 text-xs font-black text-black focus:ring-2 focus:ring-amber-400 cursor-pointer shadow-sm">
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
                       class="w-full bg-white border-2 border-black rounded-2xl pl-10 pr-4 py-2.5 text-xs font-black text-black placeholder:text-gray-400 focus:ring-2 focus:ring-amber-400 shadow-sm">
                <i class="fas fa-search absolute left-4 top-3 text-black text-xs"></i>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-black hover:bg-amber-400 hover:text-black text-white text-xs font-black uppercase tracking-wider rounded-2xl border-2 border-black transition shadow-sm">
                Filter
            </button>

            @if($search || $classroomId || $statusFilter)
            <a href="{{ route('admin.attendance.monitoring', ['group' => $group, 'school_id' => $schoolId, 'date' => $date]) }}" 
               class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-black text-xs font-black rounded-2xl border-2 border-black transition shadow-sm">
                <i class="fas fa-rotate-left mr-1"></i> Reset
            </a>
            @endif
        </form>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.attendance.bulk', ['group' => $group, 'school_id' => $schoolId, 'date' => $date, 'classroom_id' => $classroomId]) }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-300 hover:bg-amber-400 text-black rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black shadow-md transition active:scale-95">
                <i class="fas fa-table text-sm"></i>
                <span>Input Massal</span>
            </a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- DATA TABLE (LMS STYLE) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border-2 border-black shadow-xl overflow-hidden">
        <div class="px-6 py-5 bg-white border-b-2 border-black flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-black text-amber-400 flex items-center justify-center font-black text-base border-2 border-black shadow-sm">
                    <i class="fas fa-users-viewfinder"></i>
                </div>
                <div>
                    <h2 class="font-black text-black text-base tracking-tight">
                        Daftar Presensi {{ ucfirst($group) }}
                    </h2>
                    <p class="text-xs text-gray-500 font-bold">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} &middot; {{ $selectedSchool->name ?? '' }}
                    </p>
                </div>
            </div>
            <span class="text-xs font-black text-black bg-amber-300 border-2 border-black px-4 py-1.5 rounded-2xl shadow-sm">
                Total <b>{{ $items->count() }}</b> Data
            </span>
        </div>

        @if($items->isEmpty())
        <div class="py-20 text-center text-gray-400">
            <div class="w-16 h-16 bg-amber-100 rounded-3xl border-2 border-black flex items-center justify-center mx-auto mb-4 text-black text-3xl shadow-md">
                <i class="fas fa-user-slash"></i>
            </div>
            <p class="font-black text-base text-black">Tidak ada data presensi yang sesuai dengan filter.</p>
            <p class="text-xs text-gray-500 font-bold mt-1">Silakan pilih kelas, ubah tanggal, atau sesuaikan filter di atas.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-black text-amber-400 border-b-2 border-black">
                    <tr>
                        <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-wider w-12">No</th>
                        <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-wider">Nama & Identitas</th>
                        <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-wider">Unit / Rombel</th>
                        <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-wider w-40">Status Kehadiran</th>
                        <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-wider w-32">Jam Masuk</th>
                        <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-wider w-32">Jam Pulang</th>
                        <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-wider w-28">Metode</th>
                        <th class="px-5 py-4 text-left text-[11px] font-black uppercase tracking-wider">Keterangan</th>
                        <th class="px-5 py-4 text-center text-[11px] font-black uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black/10">
                    @foreach($items as $idx => $it)
                    <tr class="hover:bg-amber-50/80 transition-colors {{ $it->status === 'belum' ? 'bg-gray-50/50' : '' }}" id="row-person-{{ $it->person_id }}">
                        <td class="px-5 py-4 text-xs text-black font-black font-mono">{{ $idx + 1 }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-2xl bg-black text-amber-400 border-2 border-black flex items-center justify-center font-black text-xs shrink-0 shadow-sm">
                                    {{ strtoupper(substr($it->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-black text-black text-sm truncate">{{ $it->name }}</div>
                                    <div class="text-xs text-gray-500 font-bold font-mono">{{ $it->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-xs text-black font-black">
                            <span class="inline-block bg-gray-100 border border-black/20 rounded-xl px-3 py-1">
                                {{ $it->info }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $badgeStyle = match($it->status) {
                                    'hadir'      => 'bg-emerald-300 text-black',
                                    'terlambat'  => 'bg-amber-300 text-black',
                                    'izin'       => 'bg-sky-300 text-black',
                                    'sakit'      => 'bg-yellow-300 text-black',
                                    'dinas_luar' => 'bg-purple-300 text-black',
                                    'cuti'       => 'bg-indigo-300 text-black',
                                    'alpha'      => 'bg-rose-400 text-black',
                                    default      => 'bg-gray-200 text-black'
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black shadow-xs {{ $badgeStyle }}">
                                {{ $it->status === 'belum' ? 'Belum Absen' : ucfirst(str_replace('_', ' ', $it->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center text-xs font-mono font-black text-black">
                            {{ $it->time_in }}
                        </td>
                        <td class="px-5 py-4 text-center text-xs font-mono font-black text-black">
                            {{ $it->time_out }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($it->recorded_via === 'gps')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-blue-100 text-blue-900 border-2 border-blue-900 shadow-2xs">
                                    <i class="fas fa-location-dot"></i> GPS
                                </span>
                            @elseif($it->recorded_via === 'rfid')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-amber-200 text-black border-2 border-black shadow-2xs">
                                    <i class="fas fa-id-card"></i> RFID
                                </span>
                            @elseif($it->recorded_via === 'manual')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-gray-100 text-black border-2 border-black shadow-2xs">
                                    <i class="fas fa-pen"></i> MANUAL
                                </span>
                            @else
                                <span class="text-gray-300 font-bold">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs font-bold text-gray-700 max-w-[180px] truncate">
                            {{ $it->notes ?? '-' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-center gap-2">
                                {{-- Tombol Edit Modal --}}
                                <button type="button" 
                                        onclick="openEditModal({{ json_encode([
                                            'person_id'    => $it->person_id,
                                            'name'         => $it->name,
                                            'code'         => $it->code,
                                            'info'         => $it->info,
                                            'classroom_id' => $it->classroom_id,
                                            'attendance_id'=> $it->attendance_id,
                                            'status'       => $it->status,
                                            'time_in'      => $it->raw_time_in,
                                            'time_out'     => $it->raw_time_out,
                                            'notes'        => $it->notes,
                                        ]) }})"
                                        class="px-3 py-2 bg-amber-300 hover:bg-amber-400 text-black rounded-xl font-black border-2 border-black shadow-sm transition active:scale-95" 
                                        title="Edit Presensi">
                                    <i class="fas fa-edit"></i>
                                </button>

                                {{-- Tombol Hapus --}}
                                @if($it->attendance_id)
                                <form action="{{ route('admin.attendance.destroy', ['id' => $it->attendance_id, 'group' => $group]) }}" 
                                      method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/mereset data presensi {{ $it->name }}?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-2 bg-rose-300 hover:bg-rose-400 text-black rounded-xl font-black border-2 border-black shadow-sm transition active:scale-95" title="Hapus Presensi">
                                        <i class="fas fa-trash"></i>
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
{{-- 🌟 INTERACTIVE MODAL EDIT PRESENSI (LMS STYLE) --}}
{{-- ═══════════════════════════════════════════════ --}}
<div id="editAttendanceModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-4 border-black shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
        {{-- Modal Header --}}
        <div class="px-6 py-5 bg-black text-amber-400 flex items-center justify-between border-b-2 border-black">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-300 text-black flex items-center justify-center text-xl font-black border-2 border-black shadow-sm">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div>
                    <h3 class="font-black text-lg text-white leading-tight">Edit Presensi Individu</h3>
                    <p class="text-xs text-amber-300 font-bold mt-0.5" id="modal_person_subtitle">Nama & Rombel</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-9 h-9 rounded-2xl bg-white/10 hover:bg-amber-300 hover:text-black text-white flex items-center justify-center transition border border-white/20">
                <i class="fas fa-times text-sm font-black"></i>
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

            {{-- Person Info Banner --}}
            <div class="bg-amber-50 border-2 border-black rounded-2xl p-4 flex items-center gap-3.5 shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-black text-amber-400 font-black flex items-center justify-center text-sm border-2 border-black shrink-0" id="modal_avatar_initial">
                    YZ
                </div>
                <div class="min-w-0">
                    <div class="font-black text-black text-base truncate" id="modal_person_name">Nama Pengguna</div>
                    <div class="text-xs text-gray-600 font-black font-mono" id="modal_person_code">NISN / NIP</div>
                </div>
            </div>

            {{-- Status Selector Cards --}}
            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">
                    Pilih Status Kehadiran:
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="hadir" class="sr-only peer modal-status-radio">
                        <div class="p-3 rounded-2xl border-2 border-black text-center text-xs font-black text-black bg-white peer-checked:bg-emerald-300 peer-checked:shadow-md transition">
                            <i class="fas fa-circle-check block text-base mb-1 text-black"></i>
                            Hadir
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="terlambat" class="sr-only peer modal-status-radio">
                        <div class="p-3 rounded-2xl border-2 border-black text-center text-xs font-black text-black bg-white peer-checked:bg-amber-300 peer-checked:shadow-md transition">
                            <i class="fas fa-clock block text-base mb-1 text-black"></i>
                            Terlambat
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="izin" class="sr-only peer modal-status-radio">
                        <div class="p-3 rounded-2xl border-2 border-black text-center text-xs font-black text-black bg-white peer-checked:bg-sky-300 peer-checked:shadow-md transition">
                            <i class="fas fa-envelope block text-base mb-1 text-black"></i>
                            Izin
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="sakit" class="sr-only peer modal-status-radio">
                        <div class="p-3 rounded-2xl border-2 border-black text-center text-xs font-black text-black bg-white peer-checked:bg-yellow-300 peer-checked:shadow-md transition">
                            <i class="fas fa-heart-pulse block text-base mb-1 text-black"></i>
                            Sakit
                        </div>
                    </label>
                    @if($group !== 'siswa')
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="dinas_luar" class="sr-only peer modal-status-radio">
                        <div class="p-3 rounded-2xl border-2 border-black text-center text-xs font-black text-black bg-white peer-checked:bg-purple-300 peer-checked:shadow-md transition">
                            <i class="fas fa-briefcase block text-base mb-1 text-black"></i>
                            Dinas Luar
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="cuti" class="sr-only peer modal-status-radio">
                        <div class="p-3 rounded-2xl border-2 border-black text-center text-xs font-black text-black bg-white peer-checked:bg-indigo-300 peer-checked:shadow-md transition">
                            <i class="fas fa-calendar-xmark block text-base mb-1 text-black"></i>
                            Cuti
                        </div>
                    </label>
                    @endif
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="alpha" class="sr-only peer modal-status-radio">
                        <div class="p-3 rounded-2xl border-2 border-black text-center text-xs font-black text-black bg-white peer-checked:bg-rose-400 peer-checked:shadow-md transition">
                            <i class="fas fa-circle-xmark block text-base mb-1 text-black"></i>
                            Alpha
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="belum" class="sr-only peer modal-status-radio">
                        <div class="p-3 rounded-2xl border-2 border-black text-center text-xs font-black text-black bg-white peer-checked:bg-black peer-checked:text-amber-400 peer-checked:shadow-md transition">
                            <i class="fas fa-hourglass block text-base mb-1"></i>
                            Belum Absen
                        </div>
                    </label>
                </div>
            </div>

            {{-- Jam Masuk & Jam Pulang --}}
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Jam Masuk:</span>
                        <button type="button" onclick="document.getElementById('modal_time_in').value='07:15'" class="text-[10px] text-black font-black underline hover:text-amber-600">
                            07:15
                        </button>
                    </label>
                    <input type="time" name="time_in" id="modal_time_in"
                           class="w-full bg-white border-2 border-black rounded-2xl px-4 py-2.5 text-xs font-mono font-black text-black focus:ring-2 focus:ring-amber-400 shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Jam Pulang:</span>
                        <button type="button" onclick="document.getElementById('modal_time_out').value='15:00'" class="text-[10px] text-black font-black underline hover:text-amber-600">
                            15:00
                        </button>
                    </label>
                    <input type="time" name="time_out" id="modal_time_out"
                           class="w-full bg-white border-2 border-black rounded-2xl px-4 py-2.5 text-xs font-mono font-black text-black focus:ring-2 focus:ring-amber-400 shadow-sm">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-black text-black uppercase tracking-wider mb-1.5">
                    Keterangan / Catatan:
                </label>
                <textarea name="notes" id="modal_notes" rows="2" placeholder="Tuliskan keterangan opsional..."
                          class="w-full bg-white border-2 border-black rounded-2xl p-3 text-xs font-bold text-black focus:ring-2 focus:ring-amber-400 shadow-sm"></textarea>
            </div>

            {{-- Modal Actions --}}
            <div class="flex items-center justify-end gap-3 pt-3 border-t-2 border-black">
                <button type="button" onclick="closeEditModal()" 
                        class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-black rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black transition shadow-sm">
                    Batal
                </button>
                <button type="submit" id="modalSaveBtn"
                        class="px-6 py-3 bg-black hover:bg-amber-400 hover:text-black text-amber-400 rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black shadow-md transition active:scale-95 flex items-center gap-2">
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
    document.getElementById('modal_avatar_initial').innerText = (data.name || 'AB').substring(0, 2).toUpperCase();

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
