@extends('layouts.admin')

@section('title', 'Rekapitulasi Presensi Bulanan - Edu Attendance Hub')

@push('styles')
<style>
    .clay-card {
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 8px 12px 24px rgba(30, 41, 59, 0.06), -6px -6px 16px rgba(255, 255, 255, 0.9), inset 2px 2px 4px rgba(255, 255, 255, 0.8), inset -2px -2px 4px rgba(0, 0, 0, 0.03);
        border: 2px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
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
<div class="space-y-10">
    {{-- Unified Playful Clay Header --}}
    <div class="no-print">
        @include('admin.attendance.header')
    </div>

    {{-- Filter Bar (Month, Year, Classroom) --}}
    <div class="clay-card rounded-[2.2rem] p-7 md:p-8 flex flex-wrap items-center justify-between gap-6 no-print bg-white">
        <form method="GET" class="flex flex-wrap items-center gap-4 flex-1">
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="school_id" value="{{ $schoolId }}">

            {{-- Month --}}
            <div class="min-w-[180px]">
                <select name="month" onchange="this.form.submit()"
                        class="w-full clay-pill-soft rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-800 focus:ring-4 focus:ring-indigo-100 cursor-pointer shadow-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            📅 {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Year --}}
            <div class="min-w-[140px]">
                <select name="year" onchange="this.form.submit()"
                        class="w-full clay-pill-soft rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-800 focus:ring-4 focus:ring-indigo-100 cursor-pointer shadow-sm">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Classroom if Siswa --}}
            @if($group === 'siswa' && $classrooms->isNotEmpty())
            <div class="min-w-[230px]">
                <select name="classroom_id" onchange="this.form.submit()"
                        class="w-full clay-pill-soft rounded-2xl px-5 py-3.5 text-xs font-bold text-slate-800 focus:ring-4 focus:ring-indigo-100 cursor-pointer shadow-sm">
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                            🏛️ {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <button type="submit" class="clay-btn-primary px-7 py-3.5 text-xs font-extrabold uppercase tracking-wider rounded-2xl shadow-md transition active:scale-95">
                Tampilkan
            </button>
        </form>

        <div class="flex items-center gap-3.5 shrink-0">
            <button type="button" onclick="window.print()" class="clay-btn-amber px-7 py-3.5 rounded-2xl text-xs font-extrabold uppercase tracking-wider transition flex items-center gap-2 shadow-md active:scale-95">
                <i class="fas fa-print text-sm"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    {{-- Printable Rekap Matrix Container --}}
    <div id="printable_rekap_area" class="clay-card rounded-[2.5rem] p-8 md:p-10 space-y-7 bg-white">
        {{-- Document Header --}}
        <div class="border-b border-slate-100 pb-7 flex items-center justify-between gap-5 flex-wrap">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-indigo-600">Laporan Resmi Presensi Bulanan</p>
                <h2 class="text-xl md:text-2xl font-extrabold text-slate-800 tracking-tight mt-1" style="font-family: var(--clay-font-title);">
                    REKAPITULASI PRESENSI {{ strtoupper($group) }} &mdash; {{ strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y')) }}
                </h2>
                <p class="text-xs text-slate-500 font-bold mt-1.5">
                    🏛️ {{ $selectedSchool->name ?? 'Perguruan Pembda Nias' }}
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
        <div class="py-28 text-center text-slate-400">
            <div class="w-16 h-16 bg-amber-100 rounded-3xl border-2 border-amber-200 flex items-center justify-center mx-auto mb-4 text-amber-600 text-3xl shadow-md">
                <i class="fas fa-folder-open"></i>
            </div>
            <p class="text-lg text-slate-800 font-extrabold" style="font-family: var(--clay-font-title);">Tidak ada data untuk periode ini.</p>
        </div>
        @else
        <div class="overflow-x-auto border border-slate-200 rounded-2xl shadow-xs">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white font-extrabold">
                        <th class="p-3.5 w-12 text-center border-r border-slate-700 text-yellow-300">No</th>
                        <th class="p-3.5 min-w-[190px] border-r border-slate-700 text-yellow-300">Nama Lengkap</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $dt = \Carbon\Carbon::create($year, $month, $d);
                            $isSun = $dt->isSunday();
                        @endphp
                        <th class="p-1.5 text-center w-8 border-r border-slate-700 {{ $isSun ? 'bg-rose-600 text-white' : '' }}">
                            {{ $d }}
                        </th>
                        @endfor
                        <th class="p-3 text-center w-10 bg-emerald-500 text-white border-r border-slate-700" title="Hadir">H</th>
                        <th class="p-3 text-center w-10 bg-amber-400 text-slate-900 border-r border-slate-700" title="Terlambat">T</th>
                        <th class="p-3 text-center w-10 bg-sky-400 text-white border-r border-slate-700" title="Izin">I</th>
                        <th class="p-3 text-center w-10 bg-yellow-400 text-slate-900 border-r border-slate-700" title="Sakit">S</th>
                        <th class="p-3 text-center w-10 bg-rose-500 text-white border-r border-slate-700" title="Alpha">A</th>
                        <th class="p-3 text-center w-16 bg-gradient-to-r from-yellow-300 to-amber-500 text-slate-900 font-extrabold">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($persons as $idx => $p)
                    @php
                        $totH = 0; $totT = 0; $totI = 0; $totS = 0; $totA = 0; $totDL = 0; $totC = 0;
                    @endphp
                    <tr class="hover:bg-indigo-50/40 transition-colors">
                        <td class="p-3.5 text-center text-slate-600 font-mono font-extrabold border-r border-slate-200">{{ $idx + 1 }}</td>
                        <td class="p-3.5 font-extrabold text-slate-800 border-r border-slate-200 truncate max-w-[240px]" title="{{ $p->full_name }}">
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
                                'izin'       => 'text-sky-800 font-bold bg-sky-100',
                                'sakit'      => 'text-yellow-800 font-bold bg-yellow-100',
                                'dinas_luar' => 'text-purple-800 font-bold bg-purple-100',
                                'cuti'       => 'text-indigo-800 font-bold bg-indigo-100',
                                'alpha'      => 'text-rose-800 font-bold bg-rose-100',
                                default      => $isSun ? 'bg-rose-50 text-rose-300' : 'text-slate-300'
                            };
                        @endphp
                        <td class="p-2 text-center font-mono font-extrabold text-xs border-r border-slate-200 {{ $cellBg }}">
                            {{ $letter }}
                        </td>
                        @endfor

                        @php
                            $totalActiveDays = max(1, $totH + $totI + $totS + $totA + $totC);
                            $rate = round(($totH / $totalActiveDays) * 100, 1);
                        @endphp
                        <td class="p-2.5 text-center font-mono font-extrabold text-emerald-800 bg-emerald-50 border-r border-slate-200">{{ $totH }}</td>
                        <td class="p-2.5 text-center font-mono font-extrabold text-amber-800 bg-amber-50 border-r border-slate-200">{{ $totT }}</td>
                        <td class="p-2.5 text-center font-mono font-extrabold text-sky-800 bg-sky-50 border-r border-slate-200">{{ $totI }}</td>
                        <td class="p-2.5 text-center font-mono font-extrabold text-yellow-800 bg-yellow-50 border-r border-slate-200">{{ $totS }}</td>
                        <td class="p-2.5 text-center font-mono font-extrabold text-rose-800 bg-rose-50 border-r border-slate-200">{{ $totA }}</td>
                        <td class="p-2.5 text-center font-mono font-black text-slate-900 bg-gradient-to-r from-yellow-300 to-amber-400">
                            {{ $totH > 0 ? $rate . '%' : '0%' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend / Keterangan Kode --}}
        <div class="pt-5 border-t border-slate-100 flex flex-wrap items-center gap-5 text-xs font-bold text-slate-700">
            <span class="uppercase tracking-wider text-indigo-600 font-extrabold">Keterangan:</span>
            <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center justify-center font-mono font-extrabold text-xs">H</span> Hadir</span>
            <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 border border-amber-300 inline-flex items-center justify-center font-mono font-extrabold text-xs">T</span> Terlambat</span>
            <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-800 border border-sky-300 inline-flex items-center justify-center font-mono font-extrabold text-xs">I</span> Izin</span>
            <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-lg bg-yellow-100 text-yellow-800 border border-yellow-300 inline-flex items-center justify-center font-mono font-extrabold text-xs">S</span> Sakit</span>
            <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-lg bg-rose-100 text-rose-800 border border-rose-300 inline-flex items-center justify-center font-mono font-extrabold text-xs">A</span> Alpha</span>
        </div>
        @endif
    </div>
</div>
@endsection
