@extends('layouts.guru')
@section('title', 'Nilai Siswa - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #047857 100%) !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-chart-bar text-black"></i>
                    </div>
                    Nilai Siswa
                </h1>
                <p class="text-xs md:text-sm font-bold text-emerald-200 mt-1" style="color: #a7f3d0 !important;">Kelola dan rekap nilai siswa per mata pelajaran</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('guru.nilai.input', ['classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-black text-xs font-black uppercase tracking-wider rounded-2xl border-2 border-black shadow-md transition-all">
                    <i class="fas fa-plus-circle text-black"></i> Input Nilai
                </a>
                <a href="{{ route('guru.nilai.summary', ['classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-400 hover:bg-sky-300 text-black text-xs font-black uppercase tracking-wider rounded-2xl border-2 border-black shadow-md transition-all">
                    <i class="fas fa-table text-black"></i> Rekap
                </a>
                <form method="GET" class="flex items-center gap-2">
                    @if($classrooms->count() > 0)
                    <select name="classroom_id" onchange="this.form.submit()" class="text-xs font-black border-2 border-black bg-white rounded-2xl px-4 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                        <option value="" class="font-bold text-gray-700" style="color: #000000 !important;">Semua Kelas</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ $selectedClassroomId == $cls->id ? 'selected' : '' }} style="color: #000000 !important;">
                                Kelas {{ $cls->class_name }}
                            </option>
                        @endforeach
                    </select>
                    @endif

                    <select name="semester_id" onchange="this.form.submit()" class="text-xs font-black border-2 border-black bg-white rounded-2xl px-4 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }} style="color: #000000 !important;">
                                {{ $sem->semester_name ?? 'Semester '.$sem->semester_number }} - {{ $sem->academicYear->year ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3">
        <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-emerald-600 text-sm"></i>
        </div>
        <span class="text-emerald-700 text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation text-red-600 text-sm"></i>
        </div>
        <span class="text-red-700 text-sm font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Stats Overview --}}
    @if($subjectGrades->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $subjectGrades->count() }}</p>
                    <p class="text-xs text-gray-500">Mata Pelajaran</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $subjectGrades->sum('student_count') }}</p>
                    <p class="text-xs text-gray-500">Total Siswa</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-purple-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $subjectGrades->sum('grade_count') }}</p>
                    <p class="text-xs text-gray-500">Entry Nilai</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-amber-600"></i>
                </div>
                <div>
                    @php $overallAvg = round($subjectGrades->avg('average'), 1); @endphp
                    <p class="text-2xl font-bold {{ $overallAvg >= 80 ? 'text-emerald-600' : ($overallAvg >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $overallAvg }}</p>
                    <p class="text-xs text-gray-500">Rata-rata Umum</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Subject Cards --}}
    @if($subjectGrades->count() > 0)
        @foreach($subjectGrades as $index => $sg)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden subject-card">
                {{-- Subject Header (Collapsible) --}}
                <button type="button" onclick="toggleCard(this)" class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50/50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-book-open text-white text-sm"></i>
                        </div>
                        <div class="text-left">
                            <h2 class="font-bold text-gray-800 text-sm sm:text-base">{{ $sg['subject']->subject_name ?? $sg['subject']->name ?? '-' }}</h2>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded text-[10px] font-bold border border-emerald-100">KKM: {{ $sg['subject']->kkm ?? 75 }}</span>
                                <span class="text-xs text-gray-500 font-medium">{{ $sg['student_count'] }} siswa &middot; {{ $sg['grade_count'] }} nilai</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold px-3 py-1 rounded-lg {{ $sg['average'] >= ($sg['subject']->kkm ?? 75) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            <i class="fas fa-chart-line mr-1 text-xs"></i>{{ $sg['average'] }}
                        </span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200 group-hover:text-gray-600 card-chevron" @if($subjectGrades->count() > 1) style="transform: rotate(180deg)" @endif></i>
                    </div>
                </button>

                {{-- Table --}}
                <div class="border-t border-gray-100 card-body" @if($subjectGrades->count() > 1) style="display: none;" @endif>
                    
                    {{-- SEARCH & FILTER TOOLBAR --}}
                    <div class="px-5 py-3 bg-gray-50/50 border-b border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between">
                        <!-- Search Box -->
                        <div class="relative w-full sm:w-64">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input type="text" placeholder="Cari nama atau NISN siswa..." oninput="filterTable(this)" class="w-full pl-9 pr-4 py-1.5 bg-white text-gray-800 border border-gray-250 rounded-xl text-xs focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 outline-none transition shadow-sm font-medium">
                        </div>
                        
                        <!-- Classroom Select Filter -->
                        @if($classrooms->count() > 0)
                        <div class="flex items-center gap-1.5 w-full sm:w-auto">
                            <span class="text-xs text-gray-700 font-bold whitespace-nowrap">Filter Kelas:</span>
                            <select onchange="filterClassroom(this)" class="text-xs border border-gray-250 bg-white text-gray-800 rounded-xl px-2.5 py-1.5 focus:ring-2 focus:ring-emerald-300 focus:border-emerald-400 transition outline-none w-full sm:w-auto font-bold shadow-sm">
                                <option value="" class="text-gray-800 font-bold">Semua Kelas</option>
                                @foreach($classrooms as $cls)
                                    <option value="{{ $cls->class_name }}" class="text-gray-800 font-bold">{{ $cls->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-150">
                                <tr class="bg-gray-50/80">
                                    <th class="px-4 py-4 text-left font-bold text-gray-500 text-xs uppercase tracking-wider w-12">#</th>
                                    <th class="px-4 py-4 text-left font-bold text-gray-500 text-xs uppercase tracking-wider min-w-[220px]">Nama Siswa</th>
                                    <th class="px-4 py-4 text-center font-bold text-gray-500 text-xs uppercase tracking-wider">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span> Tugas
                                        </span>
                                    </th>
                                    <th class="px-4 py-4 text-center font-bold text-gray-500 text-xs uppercase tracking-wider">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-2 h-2 bg-indigo-500 rounded-full"></span> PTS
                                        </span>
                                    </th>
                                    <th class="px-4 py-4 text-center font-bold text-gray-500 text-xs uppercase tracking-wider">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-2 h-2 bg-purple-500 rounded-full"></span> PAS
                                        </span>
                                    </th>
                                    <th class="px-4 py-4 text-center font-bold text-gray-500 text-xs uppercase tracking-wider">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-2 h-2 bg-teal-500 rounded-full"></span> Sikap
                                        </span>
                                    </th>
                                    <th class="px-4 py-4 text-center font-bold text-gray-500 text-xs uppercase tracking-wider">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span> Rerata Akhir
                                        </span>
                                    </th>
                                    <th class="px-4 py-4 text-center font-bold text-gray-500 text-xs uppercase tracking-wider w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @php 
                                    $subjectId = $sg['subject']->id; 
                                    $subjectName = $sg['subject']->subject_name ?? $sg['subject']->name ?? '-';
                                @endphp
                                @foreach($sg['students'] as $idx => $row)
                                    @php
                                        $kkm = $sg['subject']->kkm ?? 75;
                                        $studentClassroom = $row['student']->classrooms->first()?->class_name ?? '';
                                        $studentName = $row['student']->full_name ?? '-';
                                        $studentId = $row['student']->id;
                                    @endphp
                                    <tr class="hover:bg-blue-50/30 transition-colors student-row" data-classroom="{{ $studentClassroom }}">
                                        <td class="px-4 py-4 text-xs text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full flex-shrink-0 overflow-hidden border border-gray-200">
                                                    <img src="{{ $row['student']->photo_url }}" class="w-full h-full object-cover" alt="{{ $row['student']->full_name ?? '' }}">
                                                </div>
                                                <div>
                                                    <p class="text-base font-bold text-gray-800 leading-tight student-name">{{ $row['student']->full_name ?? '-' }}</p>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-xs text-gray-500 font-mono student-nisn">{{ $row['student']->nisn ?? $row['student']->nis ?? '' }}</span>
                                                        @if($studentClassroom)
                                                            <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border border-gray-200 shadow-sm">{{ $studentClassroom }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        {{-- Tugas --}}
                                        <td class="px-4 py-4 text-center">
                                            @if($row['tugas_grades']->count() > 0)
                                                <div class="flex flex-wrap gap-1.5 justify-start">
                                                    @foreach($row['tugas_grades'] as $tg)
                                                        @php
                                                            $tgLms = $tg->isFromLms();
                                                            $tugasTooltip = ($tg->notes ?: 'Tugas/Harian') . ' (' . ($tg->created_at ? $tg->created_at->format('d/m/Y') : '-') . ')' . ($tgLms ? ' [LMS]' : '');
                                                        @endphp
                                                         <span class="inline-flex items-center px-3 py-1.5 rounded-xl font-bold text-sm border cursor-pointer shadow-sm transition hover:scale-105 {{ $tg->score >= $kkm ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : 'bg-rose-50 text-rose-700 border-rose-150' }}" 
                                                              title="{{ $tugasTooltip }}"
                                                              onclick="openDetailModal({{ $studentId }}, {{ $subjectId }}, '{{ addslashes($studentName) }}', '{{ addslashes($subjectName) }}')">
                                                            <span class="text-[10px] font-medium opacity-75 mr-1.5">{{ $tg->notes ?: 'Tugas' }}</span>
                                                            {{ $tg->score }}
                                                            @if($tgLms)<i class="fas fa-laptop text-[10px] ml-1 text-purple-650"></i>@endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-sm">&mdash;</span>
                                            @endif
                                        </td>
                                        {{-- PTS --}}
                                        <td class="px-4 py-4 text-center">
                                            @php
                                                $utsGrades = $row['all_grades']->where('grade_type', 'uts');
                                            @endphp
                                            @if($utsGrades->count() > 0)
                                                <div class="flex flex-wrap gap-1.5 justify-start">
                                                    @foreach($utsGrades as $ug)
                                                        @php
                                                            $ugLms = $ug->isFromLms();
                                                            $utsTooltip = ($ug->notes ?: 'UTS') . ' (' . ($ug->created_at ? $ug->created_at->format('d/m/Y') : '-') . ')' . ($ugLms ? ' [LMS]' : '');
                                                        @endphp
                                                         <span class="inline-flex items-center px-3 py-1.5 rounded-xl font-bold text-sm border cursor-pointer shadow-sm transition hover:scale-105 {{ $ug->score >= $kkm ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : 'bg-rose-50 text-rose-700 border-rose-150' }}" 
                                                              title="{{ $utsTooltip }}"
                                                              onclick="openDetailModal({{ $studentId }}, {{ $subjectId }}, '{{ addslashes($studentName) }}', '{{ addslashes($subjectName) }}')">
                                                            <span class="text-[10px] font-medium opacity-75 mr-1.5">{{ $ug->notes ?: 'PTS' }}</span>
                                                            {{ $ug->score }}
                                                            @if($ug->is_remedial) <span class="text-[9px] text-orange-550 font-bold ml-1">(R)</span> @endif
                                                            @if($ugLms)<i class="fas fa-laptop text-[10px] ml-1 text-purple-650"></i>@endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-sm">&mdash;</span>
                                            @endif
                                        </td>
                                        {{-- PAS --}}
                                        <td class="px-4 py-4 text-center">
                                            @php
                                                $uasGrades = $row['all_grades']->where('grade_type', 'uas');
                                            @endphp
                                            @if($uasGrades->count() > 0)
                                                <div class="flex flex-wrap gap-1.5 justify-start">
                                                    @foreach($uasGrades as $uag)
                                                        @php
                                                            $uagLms = $uag->isFromLms();
                                                            $uasTooltip = ($uag->notes ?: 'UAS') . ' (' . ($uag->created_at ? $uag->created_at->format('d/m/Y') : '-') . ')' . ($uagLms ? ' [LMS]' : '');
                                                        @endphp
                                                         <span class="inline-flex items-center px-3 py-1.5 rounded-xl font-bold text-sm border cursor-pointer shadow-sm transition hover:scale-105 {{ $uag->score >= $kkm ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : 'bg-rose-50 text-rose-700 border-rose-150' }}" 
                                                              title="{{ $uasTooltip }}"
                                                              onclick="openDetailModal({{ $studentId }}, {{ $subjectId }}, '{{ addslashes($studentName) }}', '{{ addslashes($subjectName) }}')">
                                                            <span class="text-[10px] font-medium opacity-75 mr-1.5">{{ $uag->notes ?: 'PAS' }}</span>
                                                            {{ $uag->score }}
                                                            @if($uag->is_remedial) <span class="text-[9px] text-orange-550 font-bold ml-1">(R)</span> @endif
                                                            @if($uagLms)<i class="fas fa-laptop text-[10px] ml-1 text-purple-650"></i>@endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-sm">&mdash;</span>
                                            @endif
                                        </td>
                                        {{-- Sikap --}}
                                        <td class="px-4 py-4 text-center">
                                            @php
                                                $sikapGrades = $row['all_grades']->where('grade_type', 'sikap');
                                            @endphp
                                            @if($sikapGrades->count() > 0)
                                                <div class="flex flex-wrap gap-1.5 justify-start">
                                                    @foreach($sikapGrades as $sgItem)
                                                        @php
                                                            $sgLms = $sgItem->isFromLms();
                                                            $sikapTooltip = ($sgItem->notes ?: 'Penilaian Sikap') . ' (' . ($sgItem->created_at ? $sgItem->created_at->format('d/m/Y') : '-') . ')' . ($sgLms ? ' [LMS]' : '');
                                                        @endphp
                                                         <span class="inline-flex items-center px-3 py-1.5 rounded-xl font-bold text-sm border cursor-pointer shadow-sm transition hover:scale-105 {{ $sgItem->score >= 75 ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : ($sgItem->score >= 60 ? 'bg-amber-50 text-amber-700 border-amber-150' : 'bg-rose-50 text-rose-700 border-rose-150') }}" 
                                                              title="{{ $sikapTooltip }}"
                                                              onclick="openDetailModal({{ $studentId }}, {{ $subjectId }}, '{{ addslashes($studentName) }}', '{{ addslashes($subjectName) }}')">
                                                            <span class="text-[10px] font-medium opacity-75 mr-1.5">{{ $sgItem->notes ?: 'Sikap' }}</span>
                                                            {{ $sgItem->score }}
                                                            @if($sgLms)<i class="fas fa-laptop text-[10px] ml-1 text-purple-650"></i>@endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-sm">&mdash;</span>
                                            @endif
                                        </td>
                                        {{-- Rerata Akhir --}}
                                        <td class="px-4 py-4 text-center">
                                            @if($row['final_score'] !== null)
                                                <span class="inline-block px-4 py-2 rounded-xl font-extrabold text-sm sm:text-base shadow border {{ $row['final_score'] >= $kkm ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white border-transparent' : 'bg-gradient-to-br from-rose-500 to-red-600 text-white border-transparent' }}">
                                                    {{ number_format($row['final_score'], 1) }}
                                                </span>
                                            @else
                                                <span class="text-gray-300 text-sm">&mdash;</span>
                                            @endif
                                        </td>
                                        {{-- Actions --}}
                                        <td class="px-4 py-4 text-center">
                                            <button type="button"
                                                onclick="openDetailModal({{ $studentId }}, {{ $subjectId }}, '{{ addslashes($studentName) }}', '{{ addslashes($subjectName) }}')"
                                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition" title="Detail & edit">
                                                <i class="fas fa-ellipsis-h text-sm"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        @if(!$selectedClassroomId && $classrooms->count() > 1)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-emerald-500">
                    <i class="fas fa-filter text-2xl"></i>
                </div>
                <h3 class="text-gray-600 font-semibold mb-1">Silakan Pilih Kelas</h3>
                <p class="text-gray-400 text-sm mb-4">Pilih kelas di kanan atas untuk memuat data nilai.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-2xl text-gray-300"></i>
                </div>
                <h3 class="text-gray-600 font-semibold mb-1">Belum Ada Nilai</h3>
                <p class="text-gray-400 text-sm mb-4">Mulai input nilai siswa untuk semester ini.</p>
                <a href="{{ route('guru.nilai.input', ['classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-emerald-700 transition">
                    <i class="fas fa-plus-circle"></i> Input Nilai Sekarang
                </a>
            </div>
        @endif
    @endif
</div>

{{-- Detail/Edit Modal --}}
<div id="detailModal" class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center" style="display:none;">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-lg mx-4 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div>
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fas fa-user-graduate"></i> <span id="modalStudentName"></span>
                </h3>
                <p class="text-blue-100 text-xs mt-0.5 font-medium"><i class="fas fa-book-open mr-1"></i> <span id="modalSubjectName"></span></p>
            </div>
            <button onclick="closeDetailModal()" class="text-white/80 hover:text-white transition text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="overflow-y-auto flex-1 p-6" id="modalBody"></div>
    </div>
</div>

@push('scripts')
<script>
    // Live Search
    function filterTable(input) {
        const cardBody = input.closest('.card-body');
        const query = input.value.toLowerCase().trim();
        const classSelect = cardBody.querySelector('select');
        const selectedClass = classSelect ? classSelect.value.toLowerCase().trim() : '';
        
        const rows = cardBody.querySelectorAll('.student-row');
        rows.forEach(row => {
            const name = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
            const nisn = row.querySelector('.student-nisn')?.textContent.toLowerCase() || '';
            const classroom = row.getAttribute('data-classroom')?.toLowerCase() || '';
            
            const matchesQuery = !query || name.includes(query) || nisn.includes(query);
            const matchesClass = !selectedClass || classroom === selectedClass;
            
            if (matchesQuery && matchesClass) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Classroom Filter
    function filterClassroom(select) {
        const cardBody = select.closest('.card-body');
        const searchInput = cardBody.querySelector('input[type="text"]');
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedClass = select.value.toLowerCase().trim();
        
        const rows = cardBody.querySelectorAll('.student-row');
        rows.forEach(row => {
            const name = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
            const nisn = row.querySelector('.student-nisn')?.textContent.toLowerCase() || '';
            const classroom = row.getAttribute('data-classroom')?.toLowerCase() || '';
            
            const matchesQuery = !query || name.includes(query) || nisn.includes(query);
            const matchesClass = !selectedClass || classroom === selectedClass;
            
            if (matchesQuery && matchesClass) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Collapsible cards
    function toggleCard(btn) {
        const card = btn.closest('.subject-card');
        const body = card.querySelector('.card-body');
        const chevron = card.querySelector('.card-chevron');
        if (body.style.display === 'none') {
            body.style.display = '';
            chevron.style.transform = '';
        } else {
            body.style.display = 'none';
            chevron.style.transform = 'rotate(180deg)';
        }
    }

    // Detail modal via AJAX
    function openDetailModal(studentId, subjectId, studentName, subjectName) {
        document.getElementById('modalStudentName').textContent = studentName;
        document.getElementById('modalSubjectName').textContent = subjectName;
        document.getElementById('modalBody').innerHTML = '<div class="flex justify-center items-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-500 mr-2"></i><span class="text-sm text-gray-500 font-medium">Memuat rincian nilai...</span></div>';
        document.getElementById('detailModal').style.display = 'flex';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        const url = '{{ route("guru.nilai.details") }}?student_id=' + studentId + '&subject_id=' + subjectId + '&semester_id={{ $selectedSemesterId }}';

        fetch(url)
            .then(response => response.json())
            .then(grades => {
                let html = '<div class="space-y-3">';
                if (!grades.length) {
                    html += '<p class="text-center text-gray-400 py-4">Belum ada nilai untuk siswa ini.</p>';
                }
                grades.forEach(function(g) {
                    const scoreClass = g.score >= 80 ? 'bg-emerald-100 text-emerald-700' : (g.score >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700');
                    const scoreDisplay = g.score % 1 === 0 ? Math.round(g.score) : parseFloat(g.score).toFixed(1);

                    html += '<div class="border border-gray-100 rounded-xl p-4 hover:shadow-sm transition ' + (g.is_lms ? 'bg-purple-50/50 border-purple-100' : '') + '">';
                    html += '<div class="flex items-center justify-between mb-2">';
                    html += '<div class="flex items-center gap-2">';
                    html += '<span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">' + escapeHtml(g.type) + '</span>';
                    if (g.is_lms) html += '<span class="text-xs bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded-full"><i class="fas fa-laptop mr-0.5"></i>LMS</span>';
                    if (g.is_remedial) html += '<span class="text-xs bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded-full">Remedial</span>';
                    html += '</div>';
                    html += '<span class="inline-block px-2.5 py-1 rounded-lg font-bold text-sm ' + scoreClass + '">' + scoreDisplay + '</span>';
                    html += '</div>';

                    if (g.notes) {
                        html += '<p class="text-xs text-gray-500 mb-2"><i class="fas fa-sticky-note mr-1 text-gray-400"></i>' + escapeHtml(g.notes) + '</p>';
                    }

                    html += '<div class="flex items-center justify-between">';
                    html += '<span class="text-xs text-gray-400">' + (g.created || '-') + '</span>';
                    html += '<div class="flex items-center gap-1">';
                    html += '<button type="button" onclick="startInlineEdit(' + g.id + ',' + g.score + ',\'' + escapeHtml(g.notes || '') + '\',' + (g.is_remedial ? 1 : 0) + ',\'' + csrfToken + '\')" class="text-xs text-blue-500 hover:text-blue-700 hover:bg-blue-50 px-2 py-1 rounded-md transition"><i class="fas fa-pen mr-0.5"></i>Edit' + (g.is_lms ? ' (Override)' : '') + '</button>';
                    html += '<form action="/guru/nilai/' + g.id + '" method="POST" class="inline" onsubmit="return confirm(\'Hapus nilai ini?\')">';
                    html += '<input type="hidden" name="_token" value="' + csrfToken + '">';
                    html += '<input type="hidden" name="_method" value="DELETE">';
                    html += '<button type="submit" class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded-md transition"><i class="fas fa-trash mr-0.5"></i>Hapus</button>';
                    html += '</form></div>';
                    html += '</div></div>';
                });
                html += '</div>';

                document.getElementById('modalBody').innerHTML = html;
            })
            .catch(error => {
                console.error(error);
                document.getElementById('modalBody').innerHTML = '<p class="text-center text-red-500 py-4"><i class="fas fa-exclamation-triangle mr-1"></i>Gagal memuat data nilai.</p>';
            });
    }

    function startInlineEdit(gradeId, score, notes, isRemedial, csrfToken) {
        const html = '<form action="/guru/nilai/' + gradeId + '" method="POST" class="border-2 border-blue-200 rounded-xl p-4 bg-blue-50/50 space-y-3">' +
            '<input type="hidden" name="_token" value="' + csrfToken + '">' +
            '<input type="hidden" name="_method" value="PUT">' +
            '<h4 class="font-semibold text-sm text-blue-700"><i class="fas fa-pen mr-1"></i>Edit Nilai</h4>' +
            '<div class="grid grid-cols-2 gap-3">' +
                '<div><label class="block text-xs font-semibold text-gray-500 mb-1">NILAI</label>' +
                '<input type="number" name="score" value="' + score + '" min="0" max="100" step="any" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-300 focus:border-blue-500" id="inlineEditScore"></div>' +
                '<div><label class="block text-xs font-semibold text-gray-500 mb-1">CATATAN</label>' +
                '<input type="text" name="notes" value="' + escapeHtml(notes) + '" maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-500" placeholder="Opsional"></div>' +
            '</div>' +
            '<div class="flex items-center justify-between">' +
                '<label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_remedial" value="1" ' + (isRemedial ? 'checked' : '') + ' class="w-4 h-4 text-orange-500 border-gray-300 rounded"> Remedial</label>' +
                '<div class="flex gap-2">' +
                    '<button type="button" onclick="closeDetailModal()" class="px-3 py-1.5 text-xs bg-gray-200 hover:bg-gray-300 text-gray-600 rounded-lg transition">Batal</button>' +
                    '<button type="submit" class="px-4 py-1.5 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold shadow transition"><i class="fas fa-save mr-1"></i>Simpan</button>' +
                '</div>' +
            '</div></form>';
        document.getElementById('modalBody').innerHTML = html;
        setTimeout(function() { document.getElementById('inlineEditScore')?.focus(); }, 50);
    }

    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) closeDetailModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDetailModal();
    });
</script>
@endpush
@endsection
