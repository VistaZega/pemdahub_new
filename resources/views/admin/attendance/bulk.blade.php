@extends('layouts.admin')

@section('title', 'Input Absensi Massal - Pusat Absensi')

@section('content')
<div class="space-y-8">
    {{-- Unified Header --}}
    @include('admin.attendance.header')

    {{-- Classroom / Rombel Selector if Group = Siswa --}}
    @if($group === 'siswa')
    <div class="bg-white p-6 rounded-3xl border-2 border-black shadow-xl flex flex-wrap items-center justify-between gap-5">
        <form method="GET" class="flex items-center gap-3.5 flex-wrap">
            <input type="hidden" name="group" value="siswa">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <span class="text-xs font-black text-black uppercase tracking-wider">Pilih Rombel / Kelas:</span>
            <select name="classroom_id" onchange="this.form.submit()"
                    class="bg-white border-2 border-black rounded-2xl px-5 py-3 text-xs font-black text-black focus:ring-2 focus:ring-amber-400 min-w-[240px] shadow-sm cursor-pointer">
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                        {{ $cls->class_name }}
                    </option>
                @endforeach
            </select>
        </form>

        <span class="text-xs font-black text-black bg-amber-300 border-2 border-black px-5 py-2.5 rounded-2xl shadow-sm">
            Total <b>{{ $persons->count() }}</b> Siswa Terdaftar
        </span>
    </div>
    @endif

    @if($persons->isEmpty())
    <div class="bg-white rounded-3xl border-2 border-black py-20 text-center text-gray-400 shadow-xl">
        <div class="w-16 h-16 bg-amber-100 rounded-3xl border-2 border-black flex items-center justify-center mx-auto mb-4 text-black text-3xl shadow-md">
            <i class="fas fa-user-slash"></i>
        </div>
        <p class="font-black text-base text-black">Tidak ada data terdaftar untuk filter yang dipilih.</p>
        <p class="text-xs text-gray-500 font-bold mt-1">Pastikan data kelas atau pegawai sudah aktif di sistem.</p>
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
        <div class="bg-white rounded-3xl border-2 border-black p-6 mb-6 shadow-xl">
            <div class="flex flex-wrap items-center gap-3.5">
                <span class="text-xs font-black text-black uppercase tracking-wider flex items-center gap-2 mr-2">
                    <i class="fas fa-magic text-amber-500 text-sm"></i> Quick Actions:
                </span>
                <button type="button" onclick="setAllStatus('hadir')" 
                        class="px-5 py-3 bg-emerald-300 text-black rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black hover:bg-emerald-400 transition active:scale-95 shadow-sm flex items-center gap-2">
                    <i class="fas fa-check text-black"></i>
                    <span>Semua Hadir</span>
                </button>
                <button type="button" onclick="setAllTimeEmptyOnly()" 
                        class="px-5 py-3 bg-amber-300 text-black rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black hover:bg-amber-400 transition active:scale-95 shadow-sm flex items-center gap-2">
                    <i class="fas fa-clock text-black"></i>
                    <span>Jam Default (07:15 – 15:00)</span>
                </button>
                <button type="button" onclick="resetAll()" 
                        class="px-5 py-3 bg-gray-100 text-black rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black hover:bg-gray-200 transition active:scale-95 shadow-sm flex items-center gap-2">
                    <i class="fas fa-rotate-left text-black"></i>
                    <span>Reset Yang Kosong</span>
                </button>
                <span class="ml-auto text-xs font-black text-black bg-black text-amber-400 border-2 border-black px-5 py-2.5 rounded-2xl shadow-sm">
                    {{ $persons->count() }} Data Siap Diinput
                </span>
            </div>
        </div>        {{-- Spreadsheet Table --}}
        <div class="bg-white rounded-3xl border-2 border-black shadow-xl overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-black text-amber-400 border-b-2 border-black">
                        <tr class="whitespace-nowrap">
                            <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider w-16">No</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider min-w-[240px]">Nama & Sumber Presensi Real</th>
                            <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider min-w-[180px]">Status</th>
                            <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider min-w-[140px]">Jam Masuk</th>
                            <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider min-w-[140px]">Jam Pulang</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider min-w-[200px]">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black/10">
                        @foreach($persons as $idx => $p)
                        @php
                            $ex = $existing->get($p->id);
                            $timeInVal = $ex?->time_in ? substr($ex->time_in, 0, 5) : '';
                            $timeOutVal = $ex?->time_out ? substr($ex->time_out, 0, 5) : '';
                            $codeVal = $group === 'siswa' ? ($p->nisn ?: ($p->nis ?: '-')) : ($p->employee_code ?: ($p->nip ?: '-'));
                        @endphp
                        <tr class="hover:bg-amber-50/80 transition-colors {{ $ex ? 'bg-emerald-50/30' : '' }}">
                            <td class="px-3 py-3.5 text-center text-xs text-black font-black font-mono whitespace-nowrap">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-amber-400 border-2 border-black flex items-center justify-center font-black text-xs shrink-0 shadow-xs overflow-hidden">
                                        @if(!empty($p->photo_url))
                                            <img src="{{ $p->photo_url }}" class="w-full h-full object-cover" alt="{{ $p->full_name }}">
                                        @else
                                            <span class="text-black font-black text-xs">{{ strtoupper(substr($p->full_name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-black text-black text-xs md:text-sm leading-tight flex items-center gap-2 flex-wrap">
                                            <span class="truncate max-w-[200px]" title="{{ $p->full_name }}">{{ $p->full_name }}</span>
                                            @if($ex)
                                                @if($ex->recorded_via === 'gps')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-blue-100 text-blue-900 border border-blue-900 whitespace-nowrap">
                                                        <i class="fas fa-location-dot mr-1"></i> GPS
                                                    </span>
                                                @elseif($ex->recorded_via === 'rfid')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-amber-200 text-black border border-black whitespace-nowrap">
                                                        <i class="fas fa-id-card mr-1"></i> RFID
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-gray-100 text-black border border-black whitespace-nowrap">
                                                        <i class="fas fa-pen mr-1"></i> Manual
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-500 font-bold font-mono">{{ $codeVal }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                <select name="attendance[{{ $p->id }}][status]"
                                        class="att-status w-full px-4 py-2.5 bg-white border-2 border-black rounded-2xl text-xs font-black text-black focus:ring-2 focus:ring-amber-400 shadow-sm cursor-pointer">
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
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <input type="time" name="attendance[{{ $p->id }}][time_in]"
                                       value="{{ $timeInVal }}"
                                       data-is-existing="{{ $ex && $timeInVal ? 'true' : 'false' }}"
                                       class="att-time-in w-full px-4 py-2.5 bg-white border-2 border-black rounded-2xl text-xs font-mono font-black text-center text-black focus:ring-2 focus:ring-amber-400 shadow-sm">
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <input type="time" name="attendance[{{ $p->id }}][time_out]"
                                       value="{{ $timeOutVal }}"
                                       data-is-existing="{{ $ex && $timeOutVal ? 'true' : 'false' }}"
                                       class="att-time-out w-full px-4 py-2.5 bg-white border-2 border-black rounded-2xl text-xs font-mono font-black text-center text-black focus:ring-2 focus:ring-amber-400 shadow-sm">
                            </td>
                            <td class="px-5 py-4">
                                <input type="text" name="attendance[{{ $p->id }}][notes]"
                                       value="{{ $ex?->notes }}" placeholder="Keterangan opsional..."
                                       class="w-full px-4 py-2.5 bg-white border-2 border-black rounded-2xl text-xs font-bold text-black focus:ring-2 focus:ring-amber-400 shadow-sm">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Submit Button Bar --}}
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.attendance.monitoring', ['group' => $group, 'school_id' => $schoolId, 'date' => $date]) }}"
               class="px-8 py-4 bg-gray-100 border-2 border-black text-black rounded-2xl font-black uppercase tracking-wider hover:bg-gray-200 transition text-xs shadow-sm">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                    class="px-10 py-4 bg-black hover:bg-amber-400 hover:text-black text-amber-400 rounded-2xl font-black uppercase tracking-wider border-2 border-black shadow-xl transition active:scale-95 flex items-center gap-3 text-xs">
                <i class="fas fa-save text-base"></i>
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
