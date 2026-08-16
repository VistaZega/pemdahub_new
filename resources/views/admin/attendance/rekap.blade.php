@extends('layouts.admin')

@section('title', 'Rekapitulasi Absensi Bulanan - Pusat Absensi')

@push('styles')
<style>
@media print {
    body * { visibility: hidden; }
    #printable_rekap_area, #printable_rekap_area * { visibility: visible; }
    #printable_rekap_area { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Unified Header -->
    <div class="no-print">
        @include('admin.attendance.header')
    </div>

    <!-- Filter Bar: Month, Year, and Classroom -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-wrap items-center justify-between gap-3 no-print">
        <form method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">

            <!-- Month -->
            <div class="min-w-[140px]">
                <select name="month" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-black text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Year -->
            <div class="min-w-[100px]">
                <select name="year" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-black text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Classroom if Siswa -->
            @if($group === 'siswa' && $classrooms->isNotEmpty())
            <div class="min-w-[180px]">
                <select name="classroom_id" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-black text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-black rounded-xl transition">
                Tampilkan
            </button>
        </form>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-black text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-print text-xs"></i>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Printable Area: Rekap Matrix -->
    <div id="printable_rekap_area" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
        <!-- Print Header -->
        <div class="border-b border-slate-200 pb-4 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-900">
                    REKAPITULASI ABSENSI {{ strtoupper($group) }} — {{ strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y')) }}
                </h2>
                <p class="text-xs text-slate-500 font-bold mt-0.5">
                    {{ $selectedSchool->name ?? 'Perguruan Pembda' }}
                    @if($group === 'siswa' && $classroomId)
                        &middot; Kelas: {{ $classrooms->firstWhere('id', $classroomId)?->class_name }}
                    @endif
                </p>
            </div>
            <div class="text-right text-xs font-bold text-slate-400">
                Dicetak: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        @if($persons->isEmpty())
        <div class="py-16 text-center text-slate-400">
            <i class="fa-solid fa-folder-open text-4xl mb-3 text-slate-300"></i>
            <p class="font-bold text-sm text-slate-700">Tidak ada data untuk periode ini.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 border-y border-slate-200 text-slate-600 font-black">
                        <th class="p-2 w-8 text-center border-r border-slate-200">No</th>
                        <th class="p-2 min-w-[160px] border-r border-slate-200">Nama Lengkap</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $dt = \Carbon\Carbon::create($year, $month, $d);
                            $isSun = $dt->isSunday();
                        @endphp
                        <th class="p-1 text-center w-6 border-r border-slate-200 {{ $isSun ? 'bg-rose-100 text-rose-700 font-black' : '' }}">
                            {{ $d }}
                        </th>
                        @endfor
                        <th class="p-1.5 text-center w-8 bg-emerald-50 text-emerald-800 border-r border-slate-200" title="Hadir">H</th>
                        <th class="p-1.5 text-center w-8 bg-amber-50 text-amber-800 border-r border-slate-200" title="Terlambat">T</th>
                        <th class="p-1.5 text-center w-8 bg-blue-50 text-blue-800 border-r border-slate-200" title="Izin">I</th>
                        <th class="p-1.5 text-center w-8 bg-yellow-50 text-yellow-800 border-r border-slate-200" title="Sakit">S</th>
                        <th class="p-1.5 text-center w-8 bg-rose-50 text-rose-800 border-r border-slate-200" title="Alpha">A</th>
                        <th class="p-1.5 text-center w-12 bg-slate-900 text-white font-black">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($persons as $idx => $p)
                    @php
                        $totH = 0; $totT = 0; $totI = 0; $totS = 0; $totA = 0; $totDL = 0; $totC = 0;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="p-2 text-center text-slate-400 font-bold border-r border-slate-200">{{ $idx + 1 }}</td>
                        <td class="p-2 font-bold text-slate-900 border-r border-slate-200 truncate max-w-[200px]" title="{{ $p->full_name }}">
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
                                'hadir'      => 'text-emerald-700 font-black',
                                'terlambat'  => 'text-amber-700 font-black bg-amber-50',
                                'izin'       => 'text-blue-700 font-black bg-blue-50',
                                'sakit'      => 'text-yellow-700 font-black bg-yellow-50',
                                'dinas_luar' => 'text-purple-700 font-black bg-purple-50',
                                'cuti'       => 'text-indigo-700 font-black bg-indigo-50',
                                'alpha'      => 'text-rose-700 font-black bg-rose-50',
                                default      => $isSun ? 'bg-rose-50/50 text-rose-300' : 'text-slate-300'
                            };
                        @endphp
                        <td class="p-1 text-center font-mono text-[11px] border-r border-slate-200 {{ $cellBg }}">
                            {{ $letter }}
                        </td>
                        @endfor

                        @php
                            $totalActiveDays = max(1, $totH + $totI + $totS + $totA + $totC);
                            $rate = round(($totH / $totalActiveDays) * 100, 1);
                        @endphp
                        <td class="p-1 text-center font-mono font-black text-emerald-700 bg-emerald-50/50 border-r border-slate-200">{{ $totH }}</td>
                        <td class="p-1 text-center font-mono font-black text-amber-700 bg-amber-50/50 border-r border-slate-200">{{ $totT }}</td>
                        <td class="p-1 text-center font-mono font-black text-blue-700 bg-blue-50/50 border-r border-slate-200">{{ $totI }}</td>
                        <td class="p-1 text-center font-mono font-black text-yellow-700 bg-yellow-50/50 border-r border-slate-200">{{ $totS }}</td>
                        <td class="p-1 text-center font-mono font-black text-rose-700 bg-rose-50/50 border-r border-slate-200">{{ $totA }}</td>
                        <td class="p-1 text-center font-mono font-black text-slate-900 bg-slate-100">
                            {{ $totH > 0 ? $rate . '%' : '0%' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Legend / Keterangan Kode -->
        <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-4 text-xs font-bold text-slate-500">
            <span class="uppercase tracking-wider text-[10px] text-slate-400">Keterangan:</span>
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-emerald-100 text-emerald-800 inline-flex items-center justify-center font-mono font-black text-[10px]">H</span> Hadir</span>
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-amber-100 text-amber-800 inline-flex items-center justify-center font-mono font-black text-[10px]">T</span> Terlambat</span>
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-blue-100 text-blue-800 inline-flex items-center justify-center font-mono font-black text-[10px]">I</span> Izin</span>
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-yellow-100 text-yellow-800 inline-flex items-center justify-center font-mono font-black text-[10px]">S</span> Sakit</span>
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-rose-100 text-rose-800 inline-flex items-center justify-center font-mono font-black text-[10px]">A</span> Alpha</span>
        </div>
        @endif
    </div>
</div>
@endsection
