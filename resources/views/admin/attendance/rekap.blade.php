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
<div class="space-y-8">
    {{-- Unified LMS Header --}}
    <div class="no-print">
        @include('admin.attendance.header')
    </div>

    {{-- Filter Bar (Month, Year, Classroom) --}}
    <div class="bg-white p-5 rounded-3xl border-2 border-black shadow-md flex flex-wrap items-center justify-between gap-4 no-print">
        <form method="GET" class="flex flex-wrap items-center gap-3 flex-1">
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">

            {{-- Month --}}
            <div class="min-w-[160px]">
                <select name="month" onchange="this.form.submit()"
                        class="w-full bg-white border-2 border-black rounded-2xl px-4 py-2.5 text-xs font-black text-black focus:ring-2 focus:ring-amber-400 cursor-pointer shadow-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Year --}}
            <div class="min-w-[120px]">
                <select name="year" onchange="this.form.submit()"
                        class="w-full bg-white border-2 border-black rounded-2xl px-4 py-2.5 text-xs font-black text-black focus:ring-2 focus:ring-amber-400 cursor-pointer shadow-sm">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- Classroom if Siswa --}}
            @if($group === 'siswa' && $classrooms->isNotEmpty())
            <div class="min-w-[200px]">
                <select name="classroom_id" onchange="this.form.submit()"
                        class="w-full bg-white border-2 border-black rounded-2xl px-4 py-2.5 text-xs font-black text-black focus:ring-2 focus:ring-amber-400 cursor-pointer shadow-sm">
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <button type="submit" class="px-5 py-2.5 bg-black hover:bg-amber-400 hover:text-black text-white text-xs font-black uppercase tracking-wider rounded-2xl border-2 border-black transition shadow-sm">
                Tampilkan
            </button>
        </form>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-amber-300 hover:bg-amber-400 text-black rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black transition flex items-center gap-2 shadow-md active:scale-95">
                <i class="fas fa-print text-sm"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    {{-- Printable Rekap Matrix Container --}}
    <div id="printable_rekap_area" class="bg-white rounded-3xl border-2 border-black shadow-xl p-6 md:p-8 space-y-6">
        {{-- Document Header --}}
        <div class="border-b-2 border-black pb-5 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-gray-500">Laporan Resmi Presensi Bulanan</p>
                <h2 class="text-xl font-black text-black tracking-tight mt-0.5">
                    REKAPITULASI PRESENSI {{ strtoupper($group) }} — {{ strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y')) }}
                </h2>
                <p class="text-xs text-black font-bold mt-1">
                    🏫 {{ $selectedSchool->name ?? 'Perguruan Pembda' }}
                    @if($group === 'siswa' && $classroomId)
                        &middot; Kelas / Rombel: <b>{{ $classrooms->firstWhere('id', $classroomId)?->class_name }}</b>
                    @endif
                </p>
            </div>
            <div class="text-right text-xs font-mono font-bold text-gray-500">
                Dicetak: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        @if($persons->isEmpty())
        <div class="py-20 text-center text-gray-400">
            <div class="w-16 h-16 bg-amber-100 rounded-3xl border-2 border-black flex items-center justify-center mx-auto mb-4 text-black text-3xl shadow-md">
                <i class="fas fa-folder-open"></i>
            </div>
            <p class="font-black text-base text-black">Tidak ada data untuk periode ini.</p>
        </div>
        @else
        <div class="overflow-x-auto border-2 border-black rounded-2xl">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-black text-amber-400 font-black">
                        <th class="p-2.5 w-10 text-center border-r border-gray-700">No</th>
                        <th class="p-2.5 min-w-[170px] border-r border-gray-700">Nama Lengkap</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $dt = \Carbon\Carbon::create($year, $month, $d);
                            $isSun = $dt->isSunday();
                        @endphp
                        <th class="p-1 text-center w-7 border-r border-gray-700 {{ $isSun ? 'bg-rose-600 text-white' : '' }}">
                            {{ $d }}
                        </th>
                        @endfor
                        <th class="p-2 text-center w-9 bg-emerald-500 text-black border-r border-gray-700" title="Hadir">H</th>
                        <th class="p-2 text-center w-9 bg-amber-400 text-black border-r border-gray-700" title="Terlambat">T</th>
                        <th class="p-2 text-center w-9 bg-sky-400 text-black border-r border-gray-700" title="Izin">I</th>
                        <th class="p-2 text-center w-9 bg-yellow-400 text-black border-r border-gray-700" title="Sakit">S</th>
                        <th class="p-2 text-center w-9 bg-rose-500 text-white border-r border-gray-700" title="Alpha">A</th>
                        <th class="p-2 text-center w-14 bg-black text-amber-400 font-black">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-t border-black">
                    @foreach($persons as $idx => $p)
                    @php
                        $totH = 0; $totT = 0; $totI = 0; $totS = 0; $totA = 0; $totDL = 0; $totC = 0;
                    @endphp
                    <tr class="hover:bg-amber-50/70 transition-colors">
                        <td class="p-2.5 text-center text-black font-black font-mono border-r border-gray-200">{{ $idx + 1 }}</td>
                        <td class="p-2.5 font-black text-black border-r border-gray-200 truncate max-w-[220px]" title="{{ $p->full_name }}">
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
                                'hadir'      => 'text-emerald-700 font-black bg-emerald-50',
                                'terlambat'  => 'text-amber-800 font-black bg-amber-100',
                                'izin'       => 'text-sky-800 font-black bg-sky-100',
                                'sakit'      => 'text-yellow-800 font-black bg-yellow-100',
                                'dinas_luar' => 'text-purple-800 font-black bg-purple-100',
                                'cuti'       => 'text-indigo-800 font-black bg-indigo-100',
                                'alpha'      => 'text-rose-800 font-black bg-rose-100',
                                default      => $isSun ? 'bg-rose-50 text-rose-300' : 'text-gray-300'
                            };
                        @endphp
                        <td class="p-1 text-center font-mono font-black text-xs border-r border-gray-200 {{ $cellBg }}">
                            {{ $letter }}
                        </td>
                        @endfor

                        @php
                            $totalActiveDays = max(1, $totH + $totI + $totS + $totA + $totC);
                            $rate = round(($totH / $totalActiveDays) * 100, 1);
                        @endphp
                        <td class="p-2 text-center font-mono font-black text-emerald-800 bg-emerald-50 border-r border-gray-200">{{ $totH }}</td>
                        <td class="p-2 text-center font-mono font-black text-amber-800 bg-amber-50 border-r border-gray-200">{{ $totT }}</td>
                        <td class="p-2 text-center font-mono font-black text-sky-800 bg-sky-50 border-r border-gray-200">{{ $totI }}</td>
                        <td class="p-2 text-center font-mono font-black text-yellow-800 bg-yellow-50 border-r border-gray-200">{{ $totS }}</td>
                        <td class="p-2 text-center font-mono font-black text-rose-800 bg-rose-50 border-r border-gray-200">{{ $totA }}</td>
                        <td class="p-2 text-center font-mono font-black text-black bg-amber-300">
                            {{ $totH > 0 ? $rate . '%' : '0%' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend / Keterangan Kode --}}
        <div class="pt-4 border-t-2 border-black flex flex-wrap items-center gap-4 text-xs font-black text-black">
            <span class="uppercase tracking-wider text-gray-500">Keterangan:</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded-lg bg-emerald-300 text-black border border-black inline-flex items-center justify-center font-mono font-black text-xs">H</span> Hadir</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded-lg bg-amber-300 text-black border border-black inline-flex items-center justify-center font-mono font-black text-xs">T</span> Terlambat</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded-lg bg-sky-300 text-black border border-black inline-flex items-center justify-center font-mono font-black text-xs">I</span> Izin</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded-lg bg-yellow-300 text-black border border-black inline-flex items-center justify-center font-mono font-black text-xs">S</span> Sakit</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded-lg bg-rose-400 text-black border border-black inline-flex items-center justify-center font-mono font-black text-xs">A</span> Alpha</span>
        </div>
        @endif
    </div>
</div>
@endsection
