@extends('layouts.admin')

@section('title', 'Rekapitulasi Presensi Bulanan - Pusat Absensi')

@push('styles')
<style>
    .edu-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
    }
    @media print {
        body * { visibility: hidden; }
        #printable_rekap_area, #printable_rekap_area * { visibility: visible; }
        #printable_rekap_area { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none !important; border: none !important; }
        .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6 font-sans">
    {{-- Unified Header --}}
    <div class="no-print">
        @include('admin.attendance.header')
    </div>

    {{-- Filter Bar (Month, Year, Classroom) --}}
    <div class="edu-card p-5 md:p-6 flex flex-wrap items-center justify-between gap-4 no-print bg-white">
        <form method="GET" class="flex flex-wrap items-center gap-3 flex-1">
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">

            {{-- Month --}}
            <div class="min-w-[160px]">
                <select name="month" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            📅 {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Year --}}
            <div class="min-w-[120px]">
                <select name="year" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Classroom if Siswa --}}
            @if($group === 'siswa' && $classrooms->isNotEmpty())
            <div class="min-w-[200px]">
                <select name="classroom_id" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                            🏛️ {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-sm">
                Tampilkan
            </button>
        </form>

        <div class="flex items-center gap-3 shrink-0">
            <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-900 rounded-xl text-xs font-bold uppercase tracking-wider transition flex items-center gap-2 shadow-2xs">
                <i class="fas fa-print text-xs"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    {{-- Printable Rekap Matrix Container --}}
    <div id="printable_rekap_area" class="edu-card p-6 md:p-8 space-y-6 bg-white">
        {{-- Document Header --}}
        <div class="border-b border-slate-100 pb-5 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Laporan Resmi Presensi Bulanan</p>
                <h2 class="text-lg md:text-xl font-bold text-slate-800 tracking-tight mt-0.5">
                    REKAPITULASI PRESENSI {{ strtoupper($group) }} &mdash; {{ strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y')) }}
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    🏫 {{ $selectedSchool->name ?? 'Perguruan Pembda Nias' }}
                    @if($group === 'siswa' && $classroomId)
                        &middot; Kelas / Rombel: <b class="text-indigo-600">{{ $classrooms->firstWhere('id', $classroomId)?->class_name }}</b>
                    @endif
                </p>
            </div>
            <div class="text-right text-xs font-mono font-bold text-slate-400">
                Dicetak: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        @if($persons->isEmpty())
        <div class="py-20 text-center text-slate-400">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400 text-2xl">
                <i class="fas fa-folder-open"></i>
            </div>
            <p class="text-base text-slate-700 font-bold">Tidak ada data untuk periode ini.</p>
        </div>
        @else
        <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-2xs">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white font-bold">
                        <th class="p-3 w-12 text-center border-r border-slate-700 text-slate-400">No</th>
                        <th class="p-3 min-w-[180px] border-r border-slate-700">Nama Lengkap</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $dt = \Carbon\Carbon::create($year, $month, $d);
                            $isSun = $dt->isSunday();
                        @endphp
                        <th class="p-1.5 text-center w-8 border-r border-slate-700 {{ $isSun ? 'bg-rose-600 text-white' : '' }}">
                            {{ $d }}
                        </th>
                        @endfor
                        <th class="p-2.5 text-center w-10 bg-emerald-600 text-white border-r border-slate-700" title="Hadir">H</th>
                        <th class="p-2.5 text-center w-10 bg-amber-500 text-slate-900 border-r border-slate-700" title="Terlambat">T</th>
                        <th class="p-2.5 text-center w-10 bg-blue-600 text-white border-r border-slate-700" title="Izin">I</th>
                        <th class="p-2.5 text-center w-10 bg-yellow-500 text-slate-900 border-r border-slate-700" title="Sakit">S</th>
                        <th class="p-2.5 text-center w-10 bg-rose-600 text-white border-r border-slate-700" title="Alpha">A</th>
                        <th class="p-2.5 text-center w-16 bg-slate-800 text-amber-300 font-bold">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($persons as $idx => $p)
                    @php
                        $totH = 0; $totT = 0; $totI = 0; $totS = 0; $totA = 0; $totDL = 0; $totC = 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3 text-center text-slate-500 font-mono font-bold border-r border-slate-200">{{ $idx + 1 }}</td>
                        <td class="p-3 font-bold text-slate-800 border-r border-slate-200 truncate max-w-[220px]" title="{{ $p->full_name }}">
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
                                'hadir'      => 'text-emerald-800 font-bold bg-emerald-100',
                                'terlambat'  => 'text-amber-800 font-bold bg-amber-100',
                                'izin'       => 'text-blue-800 font-bold bg-blue-100',
                                'sakit'      => 'text-yellow-800 font-bold bg-yellow-100',
                                'dinas_luar' => 'text-purple-800 font-bold bg-purple-100',
                                'cuti'       => 'text-indigo-800 font-bold bg-indigo-100',
                                'alpha'      => 'text-rose-800 font-bold bg-rose-100',
                                default      => $isSun ? 'bg-rose-50 text-rose-300' : 'text-slate-300'
                            };
                        @endphp
                        <td class="p-1.5 text-center font-mono font-bold text-xs border-r border-slate-200 {{ $cellBg }}">
                            {{ $letter }}
                        </td>
                        @endfor

                        @php
                            $totalActiveDays = max(1, $totH + $totI + $totS + $totA + $totC);
                            $rate = round(($totH / $totalActiveDays) * 100, 1);
                        @endphp
                        <td class="p-2 text-center font-mono font-bold text-emerald-800 bg-emerald-50 border-r border-slate-200">{{ $totH }}</td>
                        <td class="p-2 text-center font-mono font-bold text-amber-800 bg-amber-50 border-r border-slate-200">{{ $totT }}</td>
                        <td class="p-2 text-center font-mono font-bold text-blue-800 bg-blue-50 border-r border-slate-200">{{ $totI }}</td>
                        <td class="p-2 text-center font-mono font-bold text-yellow-800 bg-yellow-50 border-r border-slate-200">{{ $totS }}</td>
                        <td class="p-2 text-center font-mono font-bold text-rose-800 bg-rose-50 border-r border-slate-200">{{ $totA }}</td>
                        <td class="p-2 text-center font-mono font-bold text-slate-900 bg-amber-200">
                            {{ $totH > 0 ? $rate . '%' : '0%' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend / Keterangan Kode --}}
        <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center gap-4 text-xs font-bold text-slate-600">
            <span class="uppercase tracking-wider text-indigo-600">Keterangan:</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center justify-center font-mono font-bold text-xs">H</span> Hadir</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded bg-amber-100 text-amber-800 border border-amber-300 inline-flex items-center justify-center font-mono font-bold text-xs">T</span> Terlambat</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded bg-blue-100 text-blue-800 border border-blue-300 inline-flex items-center justify-center font-mono font-bold text-xs">I</span> Izin</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded bg-yellow-100 text-yellow-800 border border-yellow-300 inline-flex items-center justify-center font-mono font-bold text-xs">S</span> Sakit</span>
            <span class="flex items-center gap-1.5"><span class="w-5 h-5 rounded bg-rose-100 text-rose-800 border border-rose-300 inline-flex items-center justify-center font-mono font-bold text-xs">A</span> Alpha</span>
        </div>
        @endif
    </div>
</div>
@endsection
