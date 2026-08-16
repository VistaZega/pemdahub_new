@extends('layouts.admin')

@section('title', 'Monitoring Absensi Harian - Pusat Absensi')

@push('styles')
<style>
@keyframes pulseSubtle {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.85; transform: scale(1.02); }
}
.stat-active {
    animation: pulseSubtle 3s infinite ease-in-out;
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Unified Header -->
    @include('admin.attendance.header')

    <!-- Interactive Quick Stat Pills (Click to Filter) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
        @php
            $statPills = [
                ['key' => null,          'label' => 'Semua',       'val' => $stats['total'],      'icon' => 'fa-users',                 'bg' => 'bg-slate-900 text-white',             'border' => 'border-slate-800'],
                ['key' => 'hadir',       'label' => 'Hadir',       'val' => $stats['hadir'],      'icon' => 'fa-circle-check',          'bg' => 'bg-emerald-50 text-emerald-800',      'border' => 'border-emerald-200'],
                ['key' => 'terlambat',   'label' => 'Terlambat',   'val' => $stats['terlambat'],  'icon' => 'fa-clock',                 'bg' => 'bg-amber-50 text-amber-800',          'border' => 'border-amber-200'],
                ['key' => 'izin',        'label' => 'Izin',        'val' => $stats['izin'],       'icon' => 'fa-envelope-open-text',    'bg' => 'bg-blue-50 text-blue-800',            'border' => 'border-blue-200'],
                ['key' => 'sakit',       'label' => 'Sakit',       'val' => $stats['sakit'],      'icon' => 'fa-heart-pulse',           'bg' => 'bg-yellow-50 text-yellow-800',        'border' => 'border-yellow-200'],
                ['key' => 'dinas_luar',  'label' => 'Dinas Luar',  'val' => $stats['dinas_luar'], 'icon' => 'fa-briefcase',             'bg' => 'bg-purple-50 text-purple-800',        'border' => 'border-purple-200'],
                ['key' => 'alpha',       'label' => 'Alpha',       'val' => $stats['alpha'],      'icon' => 'fa-circle-xmark',          'bg' => 'bg-rose-50 text-rose-800',            'border' => 'border-rose-200'],
                ['key' => 'belum',       'label' => 'Belum Absen', 'val' => $stats['belum'],      'icon' => 'fa-hourglass-half',        'bg' => 'bg-slate-100 text-slate-700',         'border' => 'border-slate-300'],
            ];
        @endphp

        @foreach($statPills as $p)
        @php
            $isActive = ($statusFilter === $p['key']) || ($statusFilter === null && $p['key'] === null);
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => $p['key']]) }}" 
           class="p-3 rounded-2xl border transition-all flex flex-col justify-between shadow-2xs {{ $p['border'] }}
                  {{ $isActive ? 'ring-2 ring-emerald-500 scale-[1.02] shadow-xs ' . $p['bg'] : 'bg-white hover:bg-slate-50 text-slate-700' }}">
            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider opacity-80">
                <span>{{ $p['label'] }}</span>
                <i class="fa-solid {{ $p['icon'] }} text-[10px]"></i>
            </div>
            <span class="text-xl font-black mt-2">{{ number_format($p['val']) }}</span>
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
            <div class="min-w-[180px]">
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
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NISN, NIP, atau kode..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-8 pr-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-black rounded-xl transition shadow-2xs">
                Filter
            </button>

            @if($search || $classroomId || $statusFilter)
            <a href="{{ route('admin.attendance.monitoring', ['group' => $group, 'school_id' => $schoolId, 'date' => $date]) }}" 
               class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                Reset Filter
            </a>
            @endif
        </form>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.attendance.bulk', ['group' => $group, 'school_id' => $schoolId, 'date' => $date, 'classroom_id' => $classroomId]) }}" 
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-table-list text-[11px]"></i>
                <span>Mode Input Massal</span>
            </a>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h2 class="font-black text-slate-900 text-sm">
                        Daftar Kehadiran {{ ucfirst($group) }}
                    </h2>
                    <p class="text-[11px] text-slate-400 font-semibold">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} &middot; {{ $selectedSchool->name ?? '' }}
                    </p>
                </div>
            </div>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                Total <b>{{ $items->count() }}</b> Orang
            </span>
        </div>

        @if($items->isEmpty())
        <div class="py-16 text-center text-slate-400">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-300 text-2xl">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <p class="font-bold text-sm text-slate-700">Tidak ada data kehadiran yang sesuai dengan filter.</p>
            <p class="text-xs text-slate-400 mt-0.5">Silakan sesuaikan filter rombel, tanggal, atau status di atas.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider w-10">No</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider">Nama & Identitas</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider">Unit / Rombel</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-36">Status Kehadiran</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-28">Jam Masuk</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-28">Jam Pulang</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-24">Metode</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-28">Aksi (Edit / Hapus)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($items as $idx => $it)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $it->status === 'belum' ? 'bg-slate-50/30' : '' }}" id="row-person-{{ $it->person_id }}">
                        <td class="px-4 py-3.5 text-xs text-slate-400 font-bold">{{ $idx + 1 }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white font-black text-xs shrink-0 shadow-xs">
                                    {{ strtoupper(substr($it->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 text-sm truncate">{{ $it->name }}</div>
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
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider border {{ $badgeStyle }}">
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
                            <div class="inline-flex items-center gap-1.5">
                                <!-- Tombol Edit (Buka Modal) -->
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
                                        class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl transition shadow-2xs" 
                                        title="Edit Presensi Individu">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>

                                <!-- Tombol Hapus (Jika sudah ada data) -->
                                @if($it->attendance_id)
                                <form action="{{ route('admin.attendance.destroy', ['id' => $it->attendance_id, 'group' => $group]) }}" 
                                      method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi {{ $it->name }}?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl transition shadow-2xs" title="Hapus Data Presensi">
                                        <i class="fa-solid fa-trash text-xs"></i>
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

<!-- ========================================================================= -->
<!-- 🌟 INTERACTIVE MODAL EDIT PRESENSI INDIVIDU                              -->
<!-- ========================================================================= -->
<div id="editAttendanceModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden transform transition-all">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-white text-lg">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div>
                    <h3 class="font-black text-base leading-tight">Edit Presensi Individu</h3>
                    <p class="text-xs text-emerald-100 font-semibold mt-0.5" id="modal_person_subtitle">Nama & Rombel</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form action="{{ route('admin.attendance.single.save') }}" method="POST" id="singleAttendanceForm" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="person_id" id="modal_person_id">
            <input type="hidden" name="classroom_id" id="modal_classroom_id">

            <!-- Person Info Banner -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white font-black flex items-center justify-center text-sm shadow-xs shrink-0" id="modal_avatar_initial">
                    YZ
                </div>
                <div class="min-w-0">
                    <div class="font-black text-slate-900 text-sm truncate" id="modal_person_name">Nama Pengguna</div>
                    <div class="text-xs text-slate-400 font-mono" id="modal_person_code">NISN / NIP</div>
                </div>
            </div>

            <!-- Status Selector -->
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                    Status Kehadiran:
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="hadir" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-black text-slate-700 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 peer-checked:shadow-xs transition">
                            <i class="fa-solid fa-circle-check block text-sm mb-1"></i>
                            Hadir
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="terlambat" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-black text-slate-700 peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 peer-checked:shadow-xs transition">
                            <i class="fa-solid fa-clock block text-sm mb-1"></i>
                            Terlambat
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="izin" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-black text-slate-700 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 peer-checked:shadow-xs transition">
                            <i class="fa-solid fa-envelope block text-sm mb-1"></i>
                            Izin
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="sakit" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-black text-slate-700 peer-checked:bg-yellow-500 peer-checked:text-white peer-checked:border-yellow-500 peer-checked:shadow-xs transition">
                            <i class="fa-solid fa-heart-pulse block text-sm mb-1"></i>
                            Sakit
                        </div>
                    </label>
                    @if($group !== 'siswa')
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="dinas_luar" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-black text-slate-700 peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 peer-checked:shadow-xs transition">
                            <i class="fa-solid fa-briefcase block text-sm mb-1"></i>
                            Dinas Luar
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="cuti" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-black text-slate-700 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-checked:shadow-xs transition">
                            <i class="fa-solid fa-calendar-xmark block text-sm mb-1"></i>
                            Cuti
                        </div>
                    </label>
                    @endif
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="alpha" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-black text-slate-700 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 peer-checked:shadow-xs transition">
                            <i class="fa-solid fa-circle-xmark block text-sm mb-1"></i>
                            Alpha
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="belum" class="sr-only peer modal-status-radio">
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center text-xs font-black text-slate-700 peer-checked:bg-slate-700 peer-checked:text-white peer-checked:border-slate-700 peer-checked:shadow-xs transition">
                            <i class="fa-solid fa-hourglass block text-sm mb-1"></i>
                            Belum Absen
                        </div>
                    </label>
                </div>
            </div>

            <!-- Jam Masuk & Jam Pulang -->
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Jam Masuk:</span>
                        <button type="button" onclick="document.getElementById('modal_time_in').value='07:15'" class="text-[10px] text-emerald-600 font-bold hover:underline">
                            07:15
                        </button>
                    </label>
                    <input type="time" name="time_in" id="modal_time_in"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-mono font-black text-slate-800 focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Jam Pulang:</span>
                        <button type="button" onclick="document.getElementById('modal_time_out').value='15:00'" class="text-[10px] text-emerald-600 font-bold hover:underline">
                            15:00
                        </button>
                    </label>
                    <input type="time" name="time_out" id="modal_time_out"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-mono font-black text-slate-800 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                    Keterangan / Catatan:
                </label>
                <textarea name="notes" id="modal_notes" rows="2" placeholder="Tuliskan keterangan jika izin, sakit, dinas, dll..."
                          class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" 
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" id="modalSaveBtn"
                        class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white rounded-xl text-xs font-black shadow-md transition flex items-center gap-1.5 active:scale-95">
                    <i class="fa-solid fa-floppy-disk"></i>
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
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';
});
</script>
@endsection
