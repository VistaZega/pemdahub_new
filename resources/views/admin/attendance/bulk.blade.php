@extends('layouts.admin')

@section('title', 'Input Presensi Massal - Edu Attendance Hub')

@push('styles')
<style>
    .clay-card {
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 8px 12px 24px rgba(30, 41, 59, 0.06), -6px -6px 16px rgba(255, 255, 255, 0.9), inset 2px 2px 4px rgba(255, 255, 255, 0.8), inset -2px -2px 4px rgba(0, 0, 0, 0.03);
        border: 2px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Unified Playful Clay Header --}}
    @include('admin.attendance.header')

    {{-- Classroom / Rombel Selector if Group = Siswa --}}
    @if($group === 'siswa')
    <div class="clay-card rounded-[2rem] p-6 flex flex-wrap items-center justify-between gap-5 bg-white">
        <form method="GET" class="flex items-center gap-3.5 flex-wrap">
            <input type="hidden" name="group" value="siswa">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Pilih Rombel / Kelas:</span>
            <select name="classroom_id" onchange="this.form.submit()"
                    class="clay-pill-soft rounded-2xl px-5 py-3 text-xs font-bold text-slate-800 focus:ring-4 focus:ring-indigo-100 min-w-[240px] shadow-sm cursor-pointer">
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                        🏛️ {{ $cls->class_name }}
                    </option>
                @endforeach
            </select>
        </form>

        <span class="text-xs font-extrabold text-indigo-700 bg-indigo-100/80 px-5 py-2.5 rounded-2xl shadow-xs border border-indigo-200">
            Total <b>{{ $persons->count() }}</b> Siswa Terdaftar
        </span>
    </div>
    @endif

    @if($persons->isEmpty())
    <div class="clay-card rounded-[2.5rem] py-24 text-center text-slate-400 bg-white">
        <div class="w-16 h-16 bg-amber-100 rounded-3xl border-2 border-amber-200 flex items-center justify-center mx-auto mb-4 text-amber-600 text-3xl shadow-md">
            <i class="fas fa-user-slash"></i>
        </div>
        <p class="text-lg text-slate-800 font-extrabold" style="font-family: var(--clay-font-title);">Tidak ada data terdaftar untuk filter yang dipilih.</p>
        <p class="text-xs text-slate-500 font-semibold mt-1">Pastikan data kelas atau pegawai sudah aktif di sistem.</p>
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
        <div class="clay-card rounded-[2rem] p-6 mb-6 bg-white">
            <div class="flex flex-wrap items-center gap-3.5">
                <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider flex items-center gap-2 mr-2">
                    <i class="fas fa-wand-magic-sparkles text-amber-500 text-sm"></i> Quick Actions:
                </span>
                <button type="button" onclick="setAllStatus('hadir')" 
                        class="clay-btn-emerald px-5 py-2.5 rounded-2xl text-xs font-extrabold uppercase tracking-wider hover:opacity-90 transition active:scale-95 shadow-md flex items-center gap-2">
                    <i class="fas fa-check"></i>
                    <span>Semua Hadir</span>
                </button>
                <button type="button" onclick="setAllTimeEmptyOnly()" 
                        class="clay-btn-amber px-5 py-2.5 rounded-2xl text-xs font-extrabold uppercase tracking-wider hover:opacity-90 transition active:scale-95 shadow-md flex items-center gap-2">
                    <i class="fas fa-clock"></i>
                    <span>Jam Default (07:15 – 15:00)</span>
                </button>
                <button type="button" onclick="resetAll()" 
                        class="clay-pill-soft px-5 py-2.5 text-slate-600 rounded-2xl text-xs font-extrabold uppercase tracking-wider hover:bg-slate-100 transition active:scale-95 shadow-sm flex items-center gap-2">
                    <i class="fas fa-rotate-left"></i>
                    <span>Reset Yang Kosong</span>
                </button>
                <span class="ml-auto text-xs font-extrabold text-indigo-700 bg-indigo-50 border border-indigo-200 px-5 py-2 rounded-2xl shadow-xs">
                    {{ $persons->count() }} Data Siap Diinput
                </span>
            </div>
        </div>

        {{-- Spreadsheet Table --}}
        <div class="clay-card rounded-[2.5rem] overflow-hidden mb-8 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white border-b-2 border-slate-900">
                        <tr class="whitespace-nowrap">
                            <th class="px-5 py-4 text-center text-xs font-extrabold uppercase tracking-wider w-16 text-yellow-300">No</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wider min-w-[240px]">Nama & Sumber Presensi Real</th>
                            <th class="px-5 py-4 text-center text-xs font-extrabold uppercase tracking-wider min-w-[180px]">Status</th>
                            <th class="px-5 py-4 text-center text-xs font-extrabold uppercase tracking-wider min-w-[140px]">Jam Masuk</th>
                            <th class="px-5 py-4 text-center text-xs font-extrabold uppercase tracking-wider min-w-[140px]">Jam Pulang</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wider min-w-[200px]">Keterangan</th>
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
                        <tr class="hover:bg-indigo-50/40 transition-colors {{ $ex ? 'bg-emerald-50/30' : '' }}">
                            <td class="px-3 py-3.5 text-center text-xs text-slate-600 font-mono font-extrabold whitespace-nowrap">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-yellow-300 to-amber-500 text-slate-900 border-2 border-white flex items-center justify-center font-extrabold text-xs shrink-0 shadow-sm overflow-hidden">
                                        @if(!empty($p->photo_url))
                                            <img src="{{ $p->photo_url }}" class="w-full h-full object-cover" alt="{{ $p->full_name }}">
                                        @else
                                            <span class="text-slate-900 font-extrabold text-xs">{{ strtoupper(substr($p->full_name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-slate-800 text-xs md:text-sm leading-tight flex items-center gap-2 flex-wrap">
                                            <span class="truncate max-w-[200px]" title="{{ $p->full_name }}">{{ $p->full_name }}</span>
                                            @if($ex)
                                                @if($ex->recorded_via === 'gps')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold bg-blue-100 text-blue-700 border border-blue-200 whitespace-nowrap">
                                                        <i class="fas fa-location-dot mr-1"></i> GPS
                                                    </span>
                                                @elseif($ex->recorded_via === 'rfid')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200 whitespace-nowrap">
                                                        <i class="fas fa-id-card mr-1"></i> RFID
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">
                                                        <i class="fas fa-pen mr-1"></i> Manual
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono font-bold mt-0.5">{{ $codeVal }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                <select name="attendance[{{ $p->id }}][status]"
                                        class="att-status w-full px-4 py-2.5 clay-pill-soft rounded-2xl text-xs font-extrabold text-slate-800 focus:ring-4 focus:ring-indigo-100 shadow-xs cursor-pointer">
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
                                       class="att-time-in w-full px-4 py-2.5 clay-pill-soft rounded-2xl text-xs font-mono font-extrabold text-center text-slate-800 focus:ring-4 focus:ring-indigo-100 shadow-xs">
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <input type="time" name="attendance[{{ $p->id }}][time_out]"
                                       value="{{ $timeOutVal }}"
                                       data-is-existing="{{ $ex && $timeOutVal ? 'true' : 'false' }}"
                                       class="att-time-out w-full px-4 py-2.5 clay-pill-soft rounded-2xl text-xs font-mono font-extrabold text-center text-slate-800 focus:ring-4 focus:ring-indigo-100 shadow-xs">
                            </td>
                            <td class="px-5 py-4">
                                <input type="text" name="attendance[{{ $p->id }}][notes]"
                                       value="{{ $ex?->notes }}" placeholder="Keterangan opsional..."
                                       class="w-full px-4 py-2.5 clay-pill-soft rounded-2xl text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:ring-4 focus:ring-indigo-100 shadow-xs">
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
               class="clay-pill-soft px-8 py-4 text-slate-600 rounded-2xl font-extrabold uppercase tracking-wider hover:bg-slate-100 transition text-xs shadow-sm">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                    class="clay-btn-primary px-10 py-4 rounded-2xl font-extrabold uppercase tracking-wider shadow-xl transition active:scale-95 flex items-center gap-3 text-xs">
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
