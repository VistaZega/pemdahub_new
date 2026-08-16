@extends('layouts.admin')

@section('title', 'Input Presensi Massal - Pusat Absensi')

@push('styles')
<style>
    .edu-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 font-sans">
    {{-- Unified Header --}}
    @include('admin.attendance.header')

    {{-- Classroom / Rombel Selector if Group = Siswa --}}
    @if($group === 'siswa')
    <div class="edu-card p-5 md:p-6 flex flex-wrap items-center justify-between gap-4 bg-white">
        <form method="GET" class="flex items-center gap-3 flex-wrap">
            <input type="hidden" name="group" value="siswa">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pilih Rombel / Kelas:</span>
            <select name="classroom_id" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 min-w-[220px] cursor-pointer">
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                        🏛️ {{ $cls->class_name }}
                    </option>
                @endforeach
            </select>
        </form>

        <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-4 py-2 rounded-xl border border-indigo-100">
            Total <b>{{ $persons->count() }}</b> Siswa Terdaftar
        </span>
    </div>
    @endif

    @if($persons->isEmpty())
    <div class="edu-card py-20 text-center text-slate-400 bg-white">
        <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400 text-2xl">
            <i class="fas fa-user-slash"></i>
        </div>
        <p class="text-base text-slate-700 font-bold">Tidak ada data terdaftar untuk filter yang dipilih.</p>
        <p class="text-xs text-slate-500 mt-1">Pastikan data kelas atau pegawai sudah aktif di sistem.</p>
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

        {{-- Quick Set Action Bar --}}
        <div class="edu-card p-5 md:p-6 mb-6 bg-white">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5 mr-2">
                    <i class="fas fa-magic text-amber-500"></i> Aksi Cepat:
                </span>
                <button type="button" onclick="setAllStatus('hadir')" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-2xs flex items-center gap-1.5">
                    <i class="fas fa-check"></i>
                    <span>Semua Hadir</span>
                </button>
                <button type="button" onclick="setAllTimeEmptyOnly()" 
                        class="px-4 py-2 bg-amber-400 hover:bg-amber-500 text-slate-900 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-2xs flex items-center gap-1.5">
                    <i class="fas fa-clock"></i>
                    <span>Jam Default (07:15 – 15:00)</span>
                </button>
                <button type="button" onclick="resetAll()" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold uppercase tracking-wider transition flex items-center gap-1.5">
                    <i class="fas fa-rotate-left"></i>
                    <span>Reset Yang Kosong</span>
                </button>
                <span class="ml-auto text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-4 py-2 rounded-xl">
                    {{ $persons->count() }} Data Siap Diinput
                </span>
            </div>
        </div>

        {{-- Spreadsheet Table --}}
        <div class="edu-card overflow-hidden mb-6 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-900 text-white border-b border-slate-800 text-xs font-bold uppercase tracking-wider">
                        <tr class="whitespace-nowrap">
                            <th class="py-3.5 px-4 text-center w-12 text-slate-400">No</th>
                            <th class="py-3.5 px-4 text-left min-w-[220px]">Nama & Sumber Presensi Real</th>
                            <th class="py-3.5 px-4 text-center min-w-[160px]">Status</th>
                            <th class="py-3.5 px-4 text-center min-w-[130px]">Jam Masuk</th>
                            <th class="py-3.5 px-4 text-center min-w-[130px]">Jam Pulang</th>
                            <th class="py-3.5 px-4 text-left min-w-[180px]">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($persons as $idx => $p)
                        @php
                            $ex = $existing->get($p->id);
                            $timeInVal = $ex?->time_in ? substr($ex->time_in, 0, 5) : '';
                            $timeOutVal = $ex?->time_out ? substr($ex->time_out, 0, 5) : '';
                            $codeVal = $group === 'siswa' ? ($p->nisn ?: ($p->nis ?: '-')) : ($p->employee_code ?: ($p->nip ?: '-'));
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $ex ? 'bg-emerald-50/20' : '' }}">
                            <td class="py-3.5 px-4 text-center text-xs text-slate-500 font-mono font-bold">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden text-slate-700">
                                        @if(!empty($p->photo_url))
                                            <img src="{{ $p->photo_url }}" class="w-full h-full object-cover" alt="{{ $p->full_name }}">
                                        @else
                                            <span>{{ strtoupper(substr($p->full_name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 text-xs md:text-sm leading-snug flex items-center gap-2 flex-wrap">
                                            <span class="truncate max-w-[200px]" title="{{ $p->full_name }}">{{ $p->full_name }}</span>
                                            @if($ex)
                                                @if(in_array($ex->recorded_via, ['rfid', 'qrcode', 'device', 'scanner']))
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap" title="Absen via Scan Kartu RFID / QR Code">
                                                        <i class="fas fa-id-card mr-1"></i> Scan RFID
                                                    </span>
                                                @elseif(in_array($ex->recorded_via, ['gps', 'web', 'phone', 'online']))
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap" title="Absen via Website Mobile/Desktop">
                                                        <i class="fas fa-mobile-screen-button mr-1"></i> Phone/PC
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap" title="Absen diinput Manual oleh Admin">
                                                        <i class="fas fa-pen mr-1"></i> Manual
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $codeVal }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <select name="attendance[{{ $p->id }}][status]"
                                        class="att-status w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
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
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <input type="time" name="attendance[{{ $p->id }}][time_in]"
                                       value="{{ $timeInVal }}"
                                       data-is-existing="{{ $ex && $timeInVal ? 'true' : 'false' }}"
                                       class="att-time-in w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-center text-slate-800 focus:ring-2 focus:ring-indigo-500">
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <input type="time" name="attendance[{{ $p->id }}][time_out]"
                                       value="{{ $timeOutVal }}"
                                       data-is-existing="{{ $ex && $timeOutVal ? 'true' : 'false' }}"
                                       class="att-time-out w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-center text-slate-800 focus:ring-2 focus:ring-indigo-500">
                            </td>
                            <td class="py-3.5 px-4">
                                <input type="text" name="attendance[{{ $p->id }}][notes]"
                                       value="{{ $ex?->notes }}" placeholder="Keterangan opsional..."
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Submit Button Bar --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.attendance.monitoring', ['group' => $group, 'school_id' => $schoolId, 'date' => $date]) }}"
               class="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold uppercase tracking-wider hover:bg-slate-200 transition text-xs">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                    class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold uppercase tracking-wider shadow-sm transition active:scale-95 flex items-center gap-2 text-xs">
                <i class="fas fa-save text-sm"></i>
                <span>Simpan Absensi Massal {{ ucfirst($group) }}</span>
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
});
</script>
@endsection
