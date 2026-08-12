@extends('layouts.guru')
@section('title', 'Absensi Siswa - Portal Guru')

@section('content')


<div class="space-y-6">

    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black print-hide" style="background: linear-gradient(135deg, #090d16 0%, #311b92 50%, #4a148c 100%) !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-clipboard-check text-black"></i>
                    </div>
                    Absensi & Rekap Kehadiran Siswa
                </h1>
                <p class="text-xs md:text-sm font-bold text-purple-200 mt-1" style="color: #e9d5ff !important;">
                    Rekapitulasi dan catatan kehadiran siswa @if($selectedClassroom) · {{ $selectedClassroom->class_name }} @endif @if($activeYear) ({{ $activeYear->year }}) @endif
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('guru.absensi.input') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-2xl text-xs font-black uppercase tracking-wider shadow-md transition">
                    <i class="fas fa-plus-circle text-black"></i> Input Absen Pelajaran
                </a>
                <form method="GET" class="flex items-center gap-2 flex-wrap">
                    <select name="classroom_id" onchange="this.form.submit()" class="text-xs font-black border-2 border-black rounded-2xl px-4 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                        <option value="" style="color: #000000 !important;">-- Pilih Kelas --</option>
                        @foreach($classrooms as $cr)
                            <option value="{{ $cr->id }}" {{ $selectedClassroomId == $cr->id ? 'selected' : '' }} style="color: #000000 !important;">
                                {{ $cr->class_name }}
                            </option>
                        @endforeach
                    </select>

                    @if($selectedClassroomId)
                    <select name="month" onchange="this.form.submit()" class="text-xs font-black border-2 border-black rounded-2xl px-3 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                        @foreach($monthsList as $mNum => $mName)
                            <option value="{{ $mNum }}" {{ $selectedMonth == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>

                    <select name="year" onchange="this.form.submit()" class="text-xs font-black border-2 border-black rounded-2xl px-3 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                        @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    @endif
                </form>
            </div>
        </div>
    </div>

    @if(!$selectedClassroomId)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <i class="fas fa-hand-pointer text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 font-bold">Pilih kelas terlebih dahulu untuk melihat rekap absensi.</p>
        </div>
    @elseif(!$selectedClassroom)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <i class="fas fa-exclamation-circle text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 font-bold">Kelas tidak ditemukan atau Anda tidak mengajar di kelas ini.</p>
        </div>
    @else
        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @php
                $items = [
                    ['label' => 'Hadir', 'value' => $summary['present'], 'color' => 'green', 'icon' => 'check-circle'],
                    ['label' => 'Sakit', 'value' => $summary['sick'], 'color' => 'yellow', 'icon' => 'briefcase-medical'],
                    ['label' => 'Izin', 'value' => $summary['permission'], 'color' => 'blue', 'icon' => 'envelope'],
                    ['label' => 'Alpha', 'value' => $summary['absent'], 'color' => 'red', 'icon' => 'times-circle'],
                    ['label' => 'Persentase', 'value' => $summary['percentage'].'%', 'color' => 'purple', 'icon' => 'percentage'],
                ];
            @endphp
            @foreach($items as $item)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center print-no-bg">
                    <div class="w-8 h-8 bg-{{ $item['color'] }}-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-{{ $item['icon'] }} text-{{ $item['color'] }}-600 text-sm"></i>
                    </div>
                    <p class="text-xl font-bold text-gray-800">{{ $item['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $item['label'] }} ({{ $monthsList[$selectedMonth] ?? '' }})</p>
                </div>
            @endforeach
        </div>

        {{-- Tab Controls & Action --}}
        <div x-data="{ viewMode: 'matrix' }" class="space-y-4">
            <div class="flex items-center justify-between gap-4 flex-wrap bg-white p-2 rounded-2xl border border-gray-100 shadow-sm print-hide">
                <div class="flex items-center gap-2">
                    <button @click="viewMode = 'matrix'" :class="viewMode === 'matrix' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 border border-black">
                        <i class="fas fa-table"></i> Kehadiran Harian (Sekolah)
                    </button>
                    <button @click="viewMode = 'log'" :class="viewMode === 'log' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 border border-black">
                        <i class="fas fa-list-ul"></i> Kehadiran Pelajaran Saya
                    </button>
                </div>
                <a href="{{ route('guru.absensi.print', request()->all()) }}" target="_blank" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-extrabold shadow-sm transition flex items-center gap-2 border border-purple-800">
                    <i class="fas fa-print"></i> Cetak Rekap
                </a>
            </div>

            {{-- 1. TAB MATRIKS BULANAN --}}
            <div x-show="viewMode === 'matrix'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden print-force-show print-no-bg">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm md:text-base">
                        <i class="fas fa-calendar-check text-purple-600"></i> Matriks Kehadiran Harian (Sekolah) — Bulan {{ $monthsList[$selectedMonth] ?? '' }} {{ $selectedYear }} ({{ $selectedClassroom->class_name }})
                    </h2>
                    <span class="text-xs text-gray-500 font-semibold hidden md:inline">H: Hadir | S: Sakit | I: Izin | A: Alpha</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white font-bold">
                                <th class="px-3 py-3 text-center border-r border-slate-700 w-10">No</th>
                                <th class="px-4 py-3 text-left border-r border-slate-700 min-w-[180px]">Nama Siswa</th>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    <th class="px-1 py-2 text-center border-r border-slate-700 min-w-[24px]">{{ $d }}</th>
                                @endfor
                                <th class="px-2 py-2 text-center border-r border-slate-700 bg-green-900/60">H</th>
                                <th class="px-2 py-2 text-center border-r border-slate-700 bg-yellow-900/60">S</th>
                                <th class="px-2 py-2 text-center border-r border-slate-700 bg-blue-900/60">I</th>
                                <th class="px-2 py-2 text-center border-r border-slate-700 bg-red-900/60">A</th>
                                <th class="px-2 py-2 text-center bg-purple-900/60">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($classroomStudents as $idx => $st)
                                @php
                                    $stStat = $studentStats[$st->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'percentage' => 0];
                                @endphp
                                <tr class="hover:bg-purple-50/30 transition">
                                    <td class="px-3 py-2.5 text-center font-bold text-gray-500 border-r border-gray-100">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-2.5 font-bold text-gray-900 border-r border-gray-100 truncate max-w-[200px]" title="{{ $st->full_name }}">
                                        {{ $st->full_name }}
                                        <div class="text-[9px] text-gray-400 font-normal">NISN: {{ $st->nisn ?? '-' }}</div>
                                    </td>
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $stStatus = $matrixMap[$st->id][$d] ?? null;
                                            $stBadge = match($stStatus) {
                                                'hadir' => 'bg-green-500 text-white',
                                                'sakit' => 'bg-yellow-400 text-black',
                                                'izin' => 'bg-blue-500 text-white',
                                                'alpha' => 'bg-red-500 text-white',
                                                default => 'text-gray-300'
                                            };
                                            $stChar = match($stStatus) {
                                                'hadir' => 'H',
                                                'sakit' => 'S',
                                                'izin' => 'I',
                                                'alpha' => 'A',
                                                default => '.'
                                            };
                                        @endphp
                                        <td class="px-1 py-1.5 text-center border-r border-gray-100">
                                            @if($stStatus)
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[10px] font-black {{ $stBadge }}">{{ $stChar }}</span>
                                            @else
                                                <span class="text-gray-200">.</span>
                                            @endif
                                        </td>
                                    @endfor
                                    <td class="px-2 py-2 text-center font-black text-green-700 bg-green-50/50 border-r border-gray-100">{{ $stStat['hadir'] }}</td>
                                    <td class="px-2 py-2 text-center font-black text-yellow-700 bg-yellow-50/50 border-r border-gray-100">{{ $stStat['sakit'] }}</td>
                                    <td class="px-2 py-2 text-center font-black text-blue-700 bg-blue-50/50 border-r border-gray-100">{{ $stStat['izin'] }}</td>
                                    <td class="px-2 py-2 text-center font-black text-red-700 bg-red-50/50 border-r border-gray-100">{{ $stStat['alpha'] }}</td>
                                    <td class="px-2 py-2 text-center font-black text-purple-700 bg-purple-50/50">{{ $stStat['percentage'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $daysInMonth + 7 }}" class="p-8 text-center text-gray-400">
                                        Tidak ada data siswa aktif pada kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. TAB RIWAYAT LOG ABSENSI --}}
            <div x-show="viewMode === 'log'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden print-force-show print-no-bg print-page-break">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-purple-50/50">
                    <h2 class="font-bold text-purple-900 flex items-center gap-2">
                        <i class="fas fa-chalkboard-teacher text-purple-500"></i> Rekap Kehadiran Pelajaran Saya
                    </h2>
                    <div class="text-right">
                        <div class="text-xs font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full mb-1 inline-block">
                            {{ $selectedClassroom->class_name }} ({{ $monthsList[$selectedMonth] ?? '' }} {{ $selectedYear }})
                        </div>
                        @if(isset($assignmentInfo))
                        <div class="text-[10px] font-bold text-purple-800">
                            {{ $assignmentInfo }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-slate-900 text-white font-bold text-xs">
                                <th class="px-3 py-3 text-center border-r border-slate-700 w-10 border-b border-slate-700" rowspan="2">No</th>
                                <th class="px-4 py-3 text-left border-r border-slate-700 min-w-[180px] border-b border-slate-700" rowspan="2">Nama Siswa</th>
                                @php $dateCount = count($lessonDates ?? []); @endphp
                                <th class="px-1 py-2 text-center border-r border-slate-700 border-b border-slate-700" colspan="{{ $dateCount > 0 ? $dateCount : 1 }}">Tanggal</th>
                                <th class="px-2 py-2 text-center bg-slate-800 border-b border-slate-700" colspan="5">Total</th>
                            </tr>
                            <tr class="bg-slate-900 text-white font-bold text-xs">
                                @forelse($lessonDates ?? [] as $d)
                                    <th class="px-1 py-2 text-center border-r border-slate-700 min-w-[24px]">{{ $d }}</th>
                                @empty
                                    <th class="px-3 py-2 text-center border-r border-slate-700 text-gray-400 font-normal italic">Belum ada absen</th>
                                @endforelse
                                <th class="px-2 py-2 text-center border-r border-slate-700 bg-green-900/60">H</th>
                                <th class="px-2 py-2 text-center border-r border-slate-700 bg-yellow-900/60">S</th>
                                <th class="px-2 py-2 text-center border-r border-slate-700 bg-blue-900/60">I</th>
                                <th class="px-2 py-2 text-center border-r border-slate-700 bg-red-900/60">A</th>
                                <th class="px-2 py-2 text-center bg-purple-900/60">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($classroomStudents as $idx => $st)
                                @php
                                    $stStat = $lessonStudentStats[$st->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'percentage' => 0];
                                    $isWajib = in_array($st->id, $wajibStudentIds);
                                @endphp
                                <tr class="hover:bg-purple-50/30 transition {{ !$isWajib ? 'bg-gray-50/50' : '' }}">
                                    <td class="px-3 py-2.5 text-center font-bold {{ !$isWajib ? 'text-gray-300' : 'text-gray-500' }} border-r border-gray-100">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-2.5 font-bold border-r border-gray-100 truncate max-w-[200px] {{ !$isWajib ? 'text-gray-400' : 'text-gray-900' }}" title="{{ $st->full_name }}">
                                        {{ $st->full_name }}
                                        <div class="text-[9px] {{ !$isWajib ? 'text-gray-300' : 'text-gray-400' }} font-normal">
                                            NISN: {{ $st->nisn ?? '-' }} 
                                        </div>
                                    </td>
                                    @forelse($lessonDates ?? [] as $d)
                                        @php
                                            $stStatus = $lessonMatrixMap[$st->id][$d] ?? null;
                                            if ($stStatus) {
                                                $stBadge = match($stStatus) {
                                                    'hadir' => 'bg-green-500 text-white',
                                                    'sakit' => 'bg-yellow-400 text-black',
                                                    'izin' => 'bg-blue-500 text-white',
                                                    'alpha' => 'bg-red-500 text-white',
                                                    default => 'text-gray-300'
                                                };
                                                $stChar = match($stStatus) {
                                                    'hadir' => 'H',
                                                    'sakit' => 'S',
                                                    'izin' => 'I',
                                                    'alpha' => 'A',
                                                    default => '?'
                                                };
                                            }
                                        @endphp
                                        <td class="px-1 py-1.5 text-center border-r border-gray-100 {{ !$isWajib && !$stStatus ? 'bg-gray-100/50' : '' }}">
                                            @if($stStatus)
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[10px] font-black {{ $stBadge }}">{{ $stChar }}</span>
                                            @elseif(!$isWajib)
                                                <span class="fa-stack" style="font-size: 0.55em; vertical-align: middle;" title="Tidak Wajib Hadir (Beda Kelompok/Mata Pelajaran)">
                                                    <i class="fas fa-hand-paper fa-stack-1x text-amber-500"></i>
                                                    <i class="fas fa-ban fa-stack-2x text-red-600"></i>
                                                </span>
                                            @else
                                                <span class="text-gray-200">.</span>
                                            @endif
                                        </td>
                                    @empty
                                        <td class="px-3 py-1.5 text-center border-r border-gray-100 text-gray-300">-</td>
                                    @endforelse
                                    <td class="px-2 py-2 text-center font-black {{ $isWajib ? 'text-green-700 bg-green-50/50' : 'text-gray-400 bg-gray-100/50' }} border-r border-gray-100">{{ $stStat['hadir'] > 0 ? $stStat['hadir'] : '-' }}</td>
                                    <td class="px-2 py-2 text-center font-black {{ $isWajib ? 'text-yellow-700 bg-yellow-50/50' : 'text-gray-400 bg-gray-100/50' }} border-r border-gray-100">{{ $stStat['sakit'] > 0 ? $stStat['sakit'] : '-' }}</td>
                                    <td class="px-2 py-2 text-center font-black {{ $isWajib ? 'text-blue-700 bg-blue-50/50' : 'text-gray-400 bg-gray-100/50' }} border-r border-gray-100">{{ $stStat['izin'] > 0 ? $stStat['izin'] : '-' }}</td>
                                    <td class="px-2 py-2 text-center font-black {{ $isWajib ? 'text-red-700 bg-red-50/50' : 'text-gray-400 bg-gray-100/50' }} border-r border-gray-100">{{ $stStat['alpha'] > 0 ? $stStat['alpha'] : '-' }}</td>
                                    <td class="px-2 py-2 text-center font-black {{ $isWajib ? 'text-purple-700 bg-purple-50/50' : 'text-gray-400 bg-gray-100/50' }}">{{ $stStat['percentage'] > 0 ? $stStat['percentage'].'%' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $daysInMonth + 7 }}" class="p-8 text-center text-gray-400">
                                        Tidak ada data siswa aktif pada kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
