@extends('layouts.admin')

@section('title', 'Rekapitulasi Presensi Bulanan - Haute Academic Suite')

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
    @media print {
        body * { visibility: hidden; }
        #printable_rekap_area, #printable_rekap_area * { visibility: visible; }
        #printable_rekap_area { position: absolute; left: 0; top: 0; width: 100%; background: #fff !important; color: #000 !important; }
        .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Unified Luxury Header --}}
    <div class="no-print">
        @include('admin.attendance.header')
    </div>

    {{-- Filter Bar (Month, Year, Classroom) --}}
    <div class="luxury-glass-panel rounded-[2rem] p-6 shadow-2xl flex flex-wrap items-center justify-between gap-5 no-print text-white">
        <form method="GET" class="flex flex-wrap items-center gap-3.5 flex-1">
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">

            {{-- Month --}}
            <div class="min-w-[170px]">
                <select name="month" onchange="this.form.submit()"
                        class="w-full bg-[#0b101c]/90 border border-amber-400/30 rounded-2xl px-5 py-3 text-xs font-bold text-amber-200 focus:ring-2 focus:ring-amber-400 cursor-pointer shadow-lg">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }} class="bg-[#0b101c] text-white">
                            📅 {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Year --}}
            <div class="min-w-[130px]">
                <select name="year" onchange="this.form.submit()"
                        class="w-full bg-[#0b101c]/90 border border-amber-400/30 rounded-2xl px-5 py-3 text-xs font-bold text-amber-200 focus:ring-2 focus:ring-amber-400 cursor-pointer shadow-lg">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }} class="bg-[#0b101c] text-white">
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Classroom if Siswa --}}
            @if($group === 'siswa' && $classrooms->isNotEmpty())
            <div class="min-w-[220px]">
                <select name="classroom_id" onchange="this.form.submit()"
                        class="w-full bg-[#0b101c]/90 border border-amber-400/30 rounded-2xl px-5 py-3 text-xs font-bold text-amber-200 focus:ring-2 focus:ring-amber-400 cursor-pointer shadow-lg">
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }} class="bg-[#0b101c] text-white">
                            🏛️ {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black text-xs font-black uppercase tracking-wider rounded-2xl border border-white/40 shadow-lg hover:shadow-amber-500/20 transition active:scale-95">
                Tampilkan
            </button>
        </form>

        <div class="flex items-center gap-3 shrink-0">
            <button type="button" onclick="window.print()" class="px-6 py-3 bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black rounded-2xl text-xs font-black uppercase tracking-wider border border-white/40 transition flex items-center gap-2 shadow-lg active:scale-95">
                <i class="fas fa-print text-sm text-black"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    {{-- Printable Rekap Matrix Container --}}
    <div id="printable_rekap_area" class="luxury-table-container rounded-[2.5rem] p-7 md:p-9 space-y-6 text-white">
        {{-- Document Header --}}
        <div class="border-b border-white/10 pb-6 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-300">Laporan Resmi Presensi Bulanan</p>
                <h2 class="font-serif text-xl md:text-2xl font-bold text-white tracking-tight mt-1">
                    REKAPITULASI PRESENSI {{ strtoupper($group) }} &mdash; {{ strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y')) }}
                </h2>
                <p class="text-xs text-slate-300 font-medium mt-1">
                    🏛️ {{ $selectedSchool->name ?? 'Perguruan Pembda Nias' }}
                    @if($group === 'siswa' && $classroomId)
                        &middot; Kelas / Rombel: <b class="text-amber-300">{{ $classrooms->firstWhere('id', $classroomId)?->class_name }}</b>
                    @endif
                </p>
            </div>
            <div class="text-right text-xs font-mono font-bold text-slate-400">
                Dicetak: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        @if($persons->isEmpty())
        <div class="py-24 text-center text-slate-400">
            <div class="w-16 h-16 bg-amber-400/10 rounded-3xl border border-amber-400/30 flex items-center justify-center mx-auto mb-4 text-amber-300 text-3xl shadow-lg">
                <i class="fas fa-folder-open"></i>
            </div>
            <p class="font-serif text-lg text-white font-medium">Tidak ada data untuk periode ini.</p>
        </div>
        @else
        <div class="overflow-x-auto border border-white/10 rounded-2xl">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#070b14] text-amber-300 font-bold border-b border-amber-400/30">
                        <th class="p-3 w-12 text-center border-r border-white/10">No</th>
                        <th class="p-3 min-w-[180px] border-r border-white/10">Nama Lengkap</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $dt = \Carbon\Carbon::create($year, $month, $d);
                            $isSun = $dt->isSunday();
                        @endphp
                        <th class="p-1.5 text-center w-8 border-r border-white/10 {{ $isSun ? 'bg-rose-500/20 text-rose-300' : '' }}">
                            {{ $d }}
                        </th>
                        @endfor
                        <th class="p-2.5 text-center w-10 bg-emerald-500/20 text-emerald-300 border-r border-white/10" title="Hadir">H</th>
                        <th class="p-2.5 text-center w-10 bg-amber-500/20 text-amber-300 border-r border-white/10" title="Terlambat">T</th>
                        <th class="p-2.5 text-center w-10 bg-blue-500/20 text-blue-300 border-r border-white/10" title="Izin">I</th>
                        <th class="p-2.5 text-center w-10 bg-yellow-500/20 text-yellow-300 border-r border-white/10" title="Sakit">S</th>
                        <th class="p-2.5 text-center w-10 bg-rose-500/20 text-rose-300 border-r border-white/10" title="Alpha">A</th>
                        <th class="p-2.5 text-center w-16 bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black font-black">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($persons as $idx => $p)
                    @php
                        $totH = 0; $totT = 0; $totI = 0; $totS = 0; $totA = 0; $totDL = 0; $totC = 0;
                    @endphp
                    <tr class="hover:bg-white/[0.04] transition-colors">
                        <td class="p-3 text-center text-slate-400 font-mono font-bold border-r border-white/5">{{ $idx + 1 }}</td>
                        <td class="p-3 font-bold text-white border-r border-white/5 truncate max-w-[240px]" title="{{ $p->full_name }}">
                            {{ $p->full_name }}
                        </td>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $dt = \Carbon\Carbon::create($year, $month, $d);
                            $isSun = $dt->isSunday();
                            $att = $matrix[$p->id][$d] ?? null;
                            $st = $att?->status;

                            if ($st === 'hadir') $totH++;
                            elseif ($st === 'terlambat') { $totH++; $totT++; }
                            elseif ($st === 'izin') $totI++;
                            elseif ($st === 'sakit') $totS++;
                            elseif ($st === 'dinas_luar') { $totH++; $totDL++; }
                            elseif ($st === 'cuti') $totC++;
                            elseif ($st === 'alpha') $totA++;

                            $letter = match($st) {
                                'hadir'      => 'H',
                                'terlambat'  => 'T',
                                'izin'       => 'I',
                                'sakit'      => 'S',
                                'dinas_luar' => 'D',
                                'cuti'       => 'C',
                                'alpha'      => 'A',
                                default      => $isSun ? '•' : '-'
                            };

                            $cellBg = match($st) {
                                'hadir'      => 'text-emerald-300 font-bold bg-emerald-500/10',
                                'terlambat'  => 'text-amber-300 font-bold bg-amber-500/10',
                                'izin'       => 'text-blue-300 font-bold bg-blue-500/10',
                                'sakit'      => 'text-yellow-300 font-bold bg-yellow-500/10',
                                'dinas_luar' => 'text-purple-300 font-bold bg-purple-500/10',
                                'cuti'       => 'text-indigo-300 font-bold bg-indigo-500/10',
                                'alpha'      => 'text-rose-300 font-bold bg-rose-500/10',
                                default      => $isSun ? 'bg-rose-500/5 text-rose-400' : 'text-slate-600'
                            };
                        @endphp
                        <td class="p-1.5 text-center font-mono font-bold text-xs border-r border-white/5 {{ $cellBg }}">
                            {{ $letter }}
                        </td>
                        @endfor

                        @php
                            $totalActiveDays = max(1, $totH + $totI + $totS + $totA + $totC);
                            $rate = round(($totH / $totalActiveDays) * 100, 1);
                        @endphp
                        <td class="p-2 text-center font-mono font-bold text-emerald-300 bg-emerald-500/10 border-r border-white/5">{{ $totH }}</td>
                        <td class="p-2 text-center font-mono font-bold text-amber-300 bg-amber-500/10 border-r border-white/5">{{ $totT }}</td>
                        <td class="p-2 text-center font-mono font-bold text-blue-300 bg-blue-500/10 border-r border-white/5">{{ $totI }}</td>
                        <td class="p-2 text-center font-mono font-bold text-yellow-300 bg-yellow-500/10 border-r border-white/5">{{ $totS }}</td>
                        <td class="p-2 text-center font-mono font-bold text-rose-300 bg-rose-500/10 border-r border-white/5">{{ $totA }}</td>
                        <td class="p-2 text-center font-mono font-black text-black bg-gradient-to-r from-amber-200 to-yellow-500">
                            {{ $totH > 0 ? $rate . '%' : '0%' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend / Keterangan Kode --}}
        <div class="pt-4 border-t border-white/10 flex flex-wrap items-center gap-4 text-xs font-bold text-slate-300">
            <span class="uppercase tracking-wider text-amber-300/80">Keterangan:</span>
            <span class="flex items-center gap-1.5"><span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 inline-flex items-center justify-center font-mono font-bold text-xs">H</span> Hadir</span>
            <span class="flex items-center gap-1.5"><span class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-400/40 inline-flex items-center justify-center font-mono font-bold text-xs">T</span> Terlambat</span>
            <span class="flex items-center gap-1.5"><span class="w-6 h-6 rounded-lg bg-blue-500/20 text-blue-300 border border-blue-400/40 inline-flex items-center justify-center font-mono font-bold text-xs">I</span> Izin</span>
            <span class="flex items-center gap-1.5"><span class="w-6 h-6 rounded-lg bg-yellow-500/20 text-yellow-300 border border-yellow-400/40 inline-flex items-center justify-center font-mono font-bold text-xs">S</span> Sakit</span>
            <span class="flex items-center gap-1.5"><span class="w-6 h-6 rounded-lg bg-rose-500/20 text-rose-300 border border-rose-400/40 inline-flex items-center justify-center font-mono font-bold text-xs">A</span> Alpha</span>
        </div>
        @endif
    </div>
</div>
@endsection
