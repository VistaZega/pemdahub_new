@extends('layouts.admin')

@section('title', 'Input Presensi Massal - Haute Academic Suite')

@push('styles')
<style>
    .luxury-glass-panel {
        background: rgba(11, 17, 29, 0.75) !important;
        backdrop-filter: blur(28px) !important;
        -webkit-backdrop-filter: blur(28px) !important;
        border: 1px solid rgba(212, 175, 55, 0.25) !important;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.75), inset 0 1px 1px rgba(255, 255, 255, 0.12) !important;
        position: relative;
        overflow: hidden;
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

    {{-- Classroom / Rombel Selector if Group = Siswa --}}
    @if($group === 'siswa')
    <div class="luxury-glass-panel rounded-[2rem] p-6 shadow-2xl flex flex-wrap items-center justify-between gap-5 text-white">
        <form method="GET" class="flex items-center gap-3.5 flex-wrap">
            <input type="hidden" name="group" value="siswa">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <span class="text-xs font-bold text-amber-300 uppercase tracking-[0.2em]">Pilih Rombel / Kelas:</span>
            <select name="classroom_id" onchange="this.form.submit()"
                    class="bg-[#0b101c]/90 border border-amber-400/30 rounded-2xl px-5 py-3 text-xs font-bold text-amber-200 focus:ring-2 focus:ring-amber-400 min-w-[240px] shadow-lg cursor-pointer">
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }} class="bg-[#0b101c] text-white">
                        🏛️ {{ $cls->class_name }}
                    </option>
                @endforeach
            </select>
        </form>

        <span class="text-xs font-bold text-amber-200 bg-amber-400/10 border border-amber-400/30 px-5 py-2.5 rounded-full shadow-sm tracking-wider">
            Total <b class="text-white">{{ $persons->count() }}</b> Siswa Terdaftar
        </span>
    </div>
    @endif

    @if($persons->isEmpty())
    <div class="luxury-glass-panel rounded-[2.5rem] py-24 text-center text-slate-400 shadow-2xl">
        <div class="w-16 h-16 bg-amber-400/10 rounded-3xl border border-amber-400/30 flex items-center justify-center mx-auto mb-4 text-amber-300 text-3xl shadow-lg">
            <i class="fas fa-user-slash"></i>
        </div>
        <p class="font-serif text-lg text-white font-medium">Tidak ada data terdaftar untuk filter yang dipilih.</p>
        <p class="text-xs text-slate-400 mt-1">Pastikan data kelas atau pegawai sudah aktif di sistem.</p>
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
        <div class="luxury-glass-panel rounded-[2rem] p-6 mb-6 shadow-2xl text-white">
            <div class="flex flex-wrap items-center gap-3.5">
                <span class="text-xs font-bold text-amber-300 uppercase tracking-[0.2em] flex items-center gap-2 mr-2">
                    <i class="fas fa-wand-magic-sparkles text-amber-400 text-sm"></i> Quick Actions:
                </span>
                <button type="button" onclick="setAllStatus('hadir')" 
                        class="px-5 py-2.5 bg-emerald-500/20 text-emerald-300 rounded-2xl text-xs font-bold uppercase tracking-wider border border-emerald-400/40 hover:bg-emerald-500 hover:text-white transition active:scale-95 shadow-sm flex items-center gap-2">
                    <i class="fas fa-check"></i>
                    <span>Semua Hadir</span>
                </button>
                <button type="button" onclick="setAllTimeEmptyOnly()" 
                        class="px-5 py-2.5 bg-amber-500/20 text-amber-300 rounded-2xl text-xs font-bold uppercase tracking-wider border border-amber-400/40 hover:bg-amber-500 hover:text-black transition active:scale-95 shadow-sm flex items-center gap-2">
                    <i class="fas fa-clock"></i>
                    <span>Jam Default (07:15 – 15:00)</span>
                </button>
                <button type="button" onclick="resetAll()" 
                        class="px-5 py-2.5 bg-white/5 text-slate-300 rounded-2xl text-xs font-bold uppercase tracking-wider border border-white/10 hover:bg-white/15 transition active:scale-95 shadow-sm flex items-center gap-2">
                    <i class="fas fa-rotate-left"></i>
                    <span>Reset Yang Kosong</span>
                </button>
                <span class="ml-auto text-xs font-bold text-amber-200 bg-black/50 border border-amber-400/30 px-5 py-2 rounded-full shadow-sm">
                    {{ $persons->count() }} Data Siap Diinput
                </span>
            </div>
        </div>

        {{-- Spreadsheet Table --}}
        <div class="luxury-table-container rounded-[2.5rem] overflow-hidden mb-8 text-white">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#070b14] border-b border-amber-400/30 text-amber-300">
                        <tr class="whitespace-nowrap">
                            <th class="px-5 py-4 text-center text-[11px] font-bold uppercase tracking-[0.2em] w-16 text-amber-400/70">No</th>
                            <th class="px-5 py-4 text-left text-[11px] font-bold uppercase tracking-[0.2em] min-w-[240px] text-amber-300">Nama & Sumber Presensi Real</th>
                            <th class="px-5 py-4 text-center text-[11px] font-bold uppercase tracking-[0.2em] min-w-[180px] text-amber-300">Status</th>
                            <th class="px-5 py-4 text-center text-[11px] font-bold uppercase tracking-[0.2em] min-w-[140px] text-amber-300">Jam Masuk</th>
                            <th class="px-5 py-4 text-center text-[11px] font-bold uppercase tracking-[0.2em] min-w-[140px] text-amber-300">Jam Pulang</th>
                            <th class="px-5 py-4 text-left text-[11px] font-bold uppercase tracking-[0.2em] min-w-[200px] text-amber-300">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($persons as $idx => $p)
                        @php
                            $ex = $existing->get($p->id);
                            $timeInVal = $ex?->time_in ? substr($ex->time_in, 0, 5) : '';
                            $timeOutVal = $ex?->time_out ? substr($ex->time_out, 0, 5) : '';
                            $codeVal = $group === 'siswa' ? ($p->nisn ?: ($p->nis ?: '-')) : ($p->employee_code ?: ($p->nip ?: '-'));
                        @endphp
                        <tr class="hover:bg-white/[0.04] transition-colors {{ $ex ? 'bg-emerald-500/[0.04]' : '' }}">
                            <td class="px-3 py-3.5 text-center text-xs text-slate-400 font-mono font-bold whitespace-nowrap">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-700 text-black border border-white/40 flex items-center justify-center font-black text-xs shrink-0 shadow-sm overflow-hidden">
                                        @if(!empty($p->photo_url))
                                            <img src="{{ $p->photo_url }}" class="w-full h-full object-cover" alt="{{ $p->full_name }}">
                                        @else
                                            <span class="text-black font-black text-xs">{{ strtoupper(substr($p->full_name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-xs md:text-sm leading-tight flex items-center gap-2 flex-wrap">
                                            <span class="truncate max-w-[200px]" title="{{ $p->full_name }}">{{ $p->full_name }}</span>
                                            @if($ex)
                                                @if($ex->recorded_via === 'gps')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-blue-500/20 text-blue-300 border border-blue-400/40 whitespace-nowrap">
                                                        <i class="fas fa-location-dot mr-1"></i> GPS
                                                    </span>
                                                @elseif($ex->recorded_via === 'rfid')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-amber-500/20 text-amber-300 border border-amber-400/40 whitespace-nowrap">
                                                        <i class="fas fa-id-card mr-1"></i> RFID
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold bg-slate-500/20 text-slate-300 border border-slate-400/40 whitespace-nowrap">
                                                        <i class="fas fa-pen mr-1"></i> Manual
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-amber-200/60 font-mono mt-0.5">{{ $codeVal }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3.5 whitespace-nowrap">
                                <select name="attendance[{{ $p->id }}][status]"
                                        class="att-status w-full px-4 py-2.5 bg-[#0b101c]/90 border border-white/20 rounded-2xl text-xs font-bold text-white focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30 shadow-sm cursor-pointer">
                                    <option value="hadir" {{ ($ex?->status ?? 'hadir') === 'hadir' ? 'selected' : '' }} class="bg-[#0b101c]">Hadir</option>
                                    <option value="terlambat" {{ $ex?->status === 'terlambat' ? 'selected' : '' }} class="bg-[#0b101c]">Terlambat</option>
                                    <option value="izin" {{ $ex?->status === 'izin' ? 'selected' : '' }} class="bg-[#0b101c]">Izin</option>
                                    <option value="sakit" {{ $ex?->status === 'sakit' ? 'selected' : '' }} class="bg-[#0b101c]">Sakit</option>
                                    @if($group !== 'siswa')
                                        <option value="dinas_luar" {{ $ex?->status === 'dinas_luar' ? 'selected' : '' }} class="bg-[#0b101c]">Dinas Luar</option>
                                        <option value="cuti" {{ $ex?->status === 'cuti' ? 'selected' : '' }} class="bg-[#0b101c]">Cuti</option>
                                    @endif
                                    <option value="alpha" {{ $ex?->status === 'alpha' ? 'selected' : '' }} class="bg-[#0b101c]">Alpha</option>
                                </select>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <input type="time" name="attendance[{{ $p->id }}][time_in]"
                                       value="{{ $timeInVal }}"
                                       data-is-existing="{{ $ex && $timeInVal ? 'true' : 'false' }}"
                                       class="att-time-in w-full px-4 py-2.5 bg-[#0b101c]/90 border border-white/20 rounded-2xl text-xs font-mono font-bold text-center text-white focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30 shadow-sm">
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <input type="time" name="attendance[{{ $p->id }}][time_out]"
                                       value="{{ $timeOutVal }}"
                                       data-is-existing="{{ $ex && $timeOutVal ? 'true' : 'false' }}"
                                       class="att-time-out w-full px-4 py-2.5 bg-[#0b101c]/90 border border-white/20 rounded-2xl text-xs font-mono font-bold text-center text-white focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30 shadow-sm">
                            </td>
                            <td class="px-5 py-4">
                                <input type="text" name="attendance[{{ $p->id }}][notes]"
                                       value="{{ $ex?->notes }}" placeholder="Keterangan opsional..."
                                       class="w-full px-4 py-2.5 bg-[#0b101c]/90 border border-white/20 rounded-2xl text-xs font-medium text-white placeholder:text-slate-500 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30 shadow-sm">
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
               class="px-8 py-4 bg-white/5 border border-white/15 text-slate-300 rounded-2xl font-bold uppercase tracking-wider hover:bg-white/10 transition text-xs shadow-sm">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                    class="px-10 py-4 bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 hover:opacity-90 text-black rounded-2xl font-black uppercase tracking-wider border border-white/40 shadow-2xl transition active:scale-95 flex items-center gap-3 text-xs">
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
