@extends('layouts.admin')

@section('title', 'Input Absensi Massal - Pusat Absensi')

@section('content')
<div class="space-y-6">
    <!-- Unified Header -->
    @include('admin.attendance.header')

    <!-- Rombel Filter if Group = Siswa -->
    @if($group === 'siswa')
    <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex items-center gap-2.5 flex-wrap">
            <input type="hidden" name="group" value="siswa">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <span class="text-xs font-black text-slate-700 uppercase tracking-wider">Pilih Rombel / Kelas:</span>
            <select name="classroom_id" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-black text-slate-800 focus:ring-2 focus:ring-emerald-500 min-w-[200px]">
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                        {{ $cls->class_name }}
                    </option>
                @endforeach
            </select>
        </form>

        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl">
            {{ $persons->count() }} Siswa Terdaftar di Kelas Ini
        </span>
    </div>
    @endif

    @if($persons->isEmpty())
    <div class="bg-white rounded-3xl border border-slate-200 py-20 text-center text-slate-400">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl">
            <i class="fa-solid fa-user-slash"></i>
        </div>
        <p class="font-bold text-base text-slate-800">Tidak ada data terdaftar untuk filter yang dipilih.</p>
        <p class="text-xs text-slate-400 mt-1">Pastikan data kelas/pegawai/guru sudah aktif di sistem.</p>
    </div>
    @else

    <form action="{{ route('admin.attendance.bulk.store') }}" method="POST" id="bulkAttendanceForm">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="school_id" value="{{ $schoolId }}">
        <input type="hidden" name="group" value="{{ $group }}">
        @if($group === 'siswa')
            <input type="hidden" name="classroom_id" value="{{ $classroomId }}">
        @endif

        <!-- Quick Action Bar -->
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-4 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-black text-slate-500 uppercase tracking-wider flex items-center gap-1.5 mr-1">
                    <i class="fa-solid fa-wand-magic-sparkles text-emerald-500"></i> Quick Set:
                </span>
                <button type="button" onclick="setAllStatus('hadir')" 
                        class="px-3 py-1.5 bg-emerald-100 text-emerald-800 rounded-xl text-xs font-black hover:bg-emerald-200 transition active:scale-95 shadow-2xs">
                    <i class="fa-solid fa-check mr-1"></i> Semua Hadir
                </button>
                <button type="button" onclick="setAllTimeEmptyOnly()" 
                        class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded-xl text-xs font-black hover:bg-blue-200 transition active:scale-95 shadow-2xs">
                    <i class="fa-solid fa-clock mr-1"></i> Jam Default Yang Kosong (07:15 – 15:00)
                </button>
                <button type="button" onclick="resetAll()" 
                        class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-black hover:bg-slate-200 transition active:scale-95">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset Yang Kosong
                </button>
                <span class="ml-auto text-xs font-black text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                    {{ $persons->count() }} Data Siap Diinput
                </span>
            </div>
        </div>

        <!-- Spreadsheet Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider w-10">No</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider">Nama & Sumber Presensi</th>
                            <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-44">Status</th>
                            <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-32">Jam Masuk</th>
                            <th class="px-4 py-3.5 text-center text-[11px] font-black text-slate-500 uppercase tracking-wider w-32">Jam Pulang</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-black text-slate-500 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($persons as $idx => $p)
                        @php
                            $ex = $existing->get($p->id);
                            $timeInVal = $ex?->time_in ? substr($ex->time_in, 0, 5) : '';
                            $timeOutVal = $ex?->time_out ? substr($ex->time_out, 0, 5) : '';
                            $codeVal = $group === 'siswa' ? ($p->nisn ?: ($p->nis ?: '-')) : ($p->employee_code ?: ($p->nip ?: '-'));
                        @endphp
                        <tr class="hover:bg-emerald-50/30 transition-colors {{ $ex ? 'bg-emerald-50/20' : '' }}">
                            <td class="px-4 py-3 text-xs text-slate-400 font-bold">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white font-black text-xs shrink-0 shadow-xs">
                                        {{ strtoupper(substr($p->full_name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-sm truncate flex items-center gap-1.5 flex-wrap">
                                            <span>{{ $p->full_name }}</span>
                                            @if($ex)
                                                @if($ex->recorded_via === 'gps')
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-blue-100 text-blue-700 border border-blue-200">
                                                        <i class="fa-solid fa-location-dot"></i> GPS
                                                    </span>
                                                @elseif($ex->recorded_via === 'rfid')
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                                        <i class="fa-solid fa-id-card"></i> RFID
                                                    </span>
                                                @else
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 border border-slate-200">
                                                        <i class="fa-solid fa-pen"></i> Manual
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $codeVal }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <select name="attendance[{{ $p->id }}][status]"
                                        class="att-status w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-800 focus:ring-2 focus:ring-emerald-500">
                                    <option value="hadir" {{ ($ex?->status ?? 'hadir') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                    <option value="terlambat" {{ $ex?->status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                    <option value="izin" {{ $ex?->status === 'izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="sakit" {{ $ex?->status === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                    @if($group !== 'siswa')
                                        <option value="dinas_luar" {{ $ex?->status === 'dinas_luar' ? 'selected' : '' }}>Dinas Luar</option>
                                        <option value="cuti" {{ $ex?->status === 'cuti' ? 'selected' : '' }}>Cuti</option>
                                    @endif
                                    <option value="alpha" {{ $ex?->status === 'alpha' ? 'selected' : '' }}>Alpha</option>
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input type="time" name="attendance[{{ $p->id }}][time_in]"
                                       value="{{ $timeInVal }}"
                                       data-is-existing="{{ $ex && $timeInVal ? 'true' : 'false' }}"
                                       class="att-time-in w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-center text-slate-800 focus:ring-2 focus:ring-emerald-500">
                            </td>
                            <td class="px-4 py-3">
                                <input type="time" name="attendance[{{ $p->id }}][time_out]"
                                       value="{{ $timeOutVal }}"
                                       data-is-existing="{{ $ex && $timeOutVal ? 'true' : 'false' }}"
                                       class="att-time-out w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-center text-slate-800 focus:ring-2 focus:ring-emerald-500">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="attendance[{{ $p->id }}][notes]"
                                       value="{{ $ex?->notes }}" placeholder="Keterangan opsional"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.attendance.monitoring', ['group' => $group, 'school_id' => $schoolId, 'date' => $date]) }}"
               class="px-6 py-3 bg-white border border-slate-200 text-slate-700 rounded-2xl font-bold hover:bg-slate-50 transition text-xs">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                    class="px-8 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-2xl font-black hover:from-emerald-700 hover:to-teal-800 shadow-md transition flex items-center gap-2 text-xs uppercase tracking-wider active:scale-95">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Absensi {{ ucfirst($group) }}</span>
            </button>
        </div>
    </form>
    @endif
</div>

<script>
function setAllStatus(status) {
    document.querySelectorAll('.att-status').forEach(s => s.value = status);
}

function setAllTimeEmptyOnly() {
    document.querySelectorAll('.att-time-in').forEach(i => {
        if (!i.value) i.value = '07:15';
    });
    document.querySelectorAll('.att-time-out').forEach(o => {
        if (!o.value) o.value = '15:00';
    });
}

function resetAll() {
    document.querySelectorAll('.att-time-in').forEach(i => {
        if (i.getAttribute('data-is-existing') !== 'true') i.value = '';
    });
    document.querySelectorAll('.att-time-out').forEach(o => {
        if (o.getAttribute('data-is-existing') !== 'true') o.value = '';
    });
}

document.getElementById('bulkAttendanceForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan Absensi...';
});
</script>
@endsection
