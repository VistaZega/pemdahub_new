@extends('layouts.siswa')
@section('title', 'Nilai & Rapor - Portal Siswa')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-chart-bar text-amber-500"></i> Nilai & Rapor
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Lihat perkembangan nilai akademik kamu</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="semester_id" onchange="this.form.submit()" class="text-sm border border-gray-200 bg-white rounded-xl px-3 py-2 shadow-sm focus:ring-2 focus:ring-amber-300 focus:border-amber-400 transition">
                @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                        {{ $sem->semester_name ?? 'Semester '.$sem->semester_number }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Stats Overview --}}
    @if($subjectGrades->count() > 0)
    @php
        $totalSubjects = $subjectGrades->count();
        $overallAvg = round($subjectGrades->avg('average'), 1);
        $totalEntries = $subjectGrades->sum('grade_count');
        $highestSubject = $subjectGrades->sortByDesc('average')->first();
        $lowestSubject = $subjectGrades->sortBy('average')->first();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book text-amber-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalSubjects }}</p>
                    <p class="text-xs text-gray-500">Mata Pelajaran</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalEntries }}</p>
                    <p class="text-xs text-gray-500">Total Nilai</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $overallAvg >= 80 ? 'text-emerald-600' : ($overallAvg >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $overallAvg }}</p>
                    <p class="text-xs text-gray-500">Rata-rata</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-trophy text-purple-600"></i>
                </div>
                <div>
                    <p class="text-lg font-bold text-purple-600 truncate" title="{{ $highestSubject['subject']->subject_name ?? $highestSubject['subject']->name ?? '-' }}">
                        {{ $highestSubject['average'] }}
                    </p>
                    <p class="text-xs text-gray-500 truncate">Tertinggi</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Weight Info --}}
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl px-4 py-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
        <span class="font-semibold text-amber-700"><i class="fas fa-balance-scale mr-1"></i>Bobot Penilaian:</span>
        <span class="text-amber-600">Tugas <b>{{ number_format($gradeWeight->tugas_weight, 0) }}%</b></span>
        <span class="text-amber-600">PTS <b>{{ number_format($gradeWeight->pts_weight, 0) }}%</b></span>
        <span class="text-amber-600">PAS <b>{{ number_format($gradeWeight->pas_weight, 0) }}%</b></span>
        <span class="text-amber-600">Sikap <b>{{ number_format($gradeWeight->sikap_weight, 0) }}%</b></span>
    </div>

    {{-- Analytics Dashboard Section --}}
    @if($subjectGrades->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Radar Chart: Peta Kekuatan Akademik --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-1">
                    <i class="fas fa-chart-pie text-amber-500"></i> Peta Kekuatan Akademik
                </h3>
                <p class="text-xs text-gray-500">Analisis rata-rata nilai kompetensi per mata pelajaran</p>
            </div>
            <div class="relative h-64 mt-4 flex items-center justify-center">
                <canvas id="radarChart"></canvas>
            </div>
        </div>

        {{-- Bar Chart: Capaian Nilai vs KKM --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between lg:col-span-2">
            <div>
                <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-1">
                    <i class="fas fa-chart-bar text-amber-500"></i> Rata-rata Nilai vs KKM
                </h3>
                <p class="text-xs text-gray-500">Membandingkan pencapaian Anda dengan batas ketuntasan minimal (KKM)</p>
            </div>
            <div class="relative h-64 mt-4">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>

    @if($monthlyGrades->isNotEmpty())
    {{-- Line Chart: Tren Progress Nilai Bulanan --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-1">
                <i class="fas fa-chart-line text-amber-500"></i> Tren Progress & Kemajuan Belajar
            </h3>
            <p class="text-xs text-gray-500">Perkembangan rata-rata nilai Anda dari bulan ke bulan sepanjang semester</p>
        </div>
        <div class="relative h-56 mt-4">
            <canvas id="lineChart"></canvas>
        </div>
    </div>
    @endif
    @endif

    {{-- Main Grade Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ expandedRow: null }">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-table text-amber-500"></i> Nilai Per Mata Pelajaran
            </h2>
            <span class="text-xs text-gray-400">{{ $totalSubjects }} mapel</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-4 text-left font-semibold w-12">#</th>
                        <th class="px-4 py-4 text-left font-semibold">Mata Pelajaran</th>
                        <th class="px-4 py-4 text-center font-semibold">Tugas ({{ number_format($gradeWeight->tugas_weight, 0) }}%)</th>
                        <th class="px-4 py-4 text-center font-semibold">PTS ({{ number_format($gradeWeight->pts_weight, 0) }}%)</th>
                        <th class="px-4 py-4 text-center font-semibold">PAS ({{ number_format($gradeWeight->pas_weight, 0) }}%)</th>
                        <th class="px-4 py-4 text-center font-semibold">Sikap ({{ number_format($gradeWeight->sikap_weight, 0) }}%)</th>
                        @if($showReportCard)
                        <th class="px-4 py-4 text-center font-semibold">Akhir</th>
                        <th class="px-4 py-4 text-center font-semibold">Predikat</th>
                        @endif
                        <th class="px-4 py-4 text-center w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                    $getSourceInfo = function($grade) {
                        if (!$grade) return null;
                        $type = $grade->lms_source_type;
                        $notes = $grade->notes ?: 'Input Manual';
                        
                        $icon = match($type) {
                            'quiz_attempt' => 'fa-laptop-code text-indigo-500',
                            'submission'   => 'fa-book text-blue-500',
                            'cbt_exam'     => 'fa-desktop text-orange-500',
                            default        => 'fa-keyboard text-gray-400'
                        };
                        
                        $label = match($type) {
                            'quiz_attempt' => 'LMS Kuis',
                            'submission'   => 'LMS Tugas',
                            'cbt_exam'     => 'CBT Exam',
                            default        => 'Manual'
                        };

                        return (object)['icon' => $icon, 'label' => $label, 'notes' => $notes, 'score' => $grade->score, 'date' => $grade->created_at->format('d/M/y')];
                    };
                    @endphp

                    @foreach($subjectGrades as $idx => $sg)
                        @php $subjectId = $sg['subject']->id; @endphp
                        <tr class="hover:bg-amber-50/30 transition-colors group cursor-pointer" @click="expandedRow === {{ $subjectId }} ? expandedRow = null : expandedRow = {{ $subjectId }}" :class="expandedRow === {{ $subjectId }} ? 'bg-amber-50/50' : ''">
                            <td class="px-4 py-4 text-xs text-gray-400 font-mono">{{ $loop->iteration }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0">
                                        <i class="fas fa-book-open text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 mb-0.5 leading-tight">{{ $sg['subject']->subject_name ?? $sg['subject']->name ?? '-' }}</p>
                                        <div class="flex flex-wrap items-center gap-2 mt-1">
                                            <span class="text-xs bg-gray-100 text-gray-655 px-1.5 py-0.5 rounded font-mono font-medium">KKM: {{ $sg['subject']->kkm ?? 75 }}</span>
                                            
                                            @if($sg['final_score'] !== null)
                                                @php $isPassed = $sg['final_score'] >= ($sg['subject']->kkm ?? 75); @endphp
                                                @if($isPassed)
                                                    <span class="bg-emerald-55 text-emerald-750 px-1.5 py-0.5 rounded text-[9px] font-bold border border-emerald-100 uppercase tracking-wider flex items-center gap-1"><i class="fas fa-check-circle text-[9px]"></i> Tuntas</span>
                                                @else
                                                    <span class="bg-rose-55 text-rose-750 px-1.5 py-0.5 rounded text-[9px] font-bold border border-rose-100 uppercase tracking-wider flex items-center gap-1"><i class="fas fa-exclamation-circle text-[9px] animate-pulse"></i> Belum Tuntas</span>
                                                @endif
                                            @endif
                                            <span class="text-[10px] text-blue-500 font-bold bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100 flex items-center gap-1"><i class="fas fa-hand-pointer animate-pulse"></i> Klik lihat rincian Kuis & Tugas</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            {{-- Tugas --}}
                            <td class="px-4 py-4 text-center">
                                @if($sg['tugas_avg'] !== null)
                                    <span class="inline-block px-3 py-1 rounded-lg font-bold text-xs min-w-[3rem] {{ $sg['tugas_avg'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($sg['tugas_avg'] >= 60 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                        {{ number_format($sg['tugas_avg'], 0) }}
                                    </span>
                                @else
                                    <span class="text-gray-200 text-xs">&mdash;</span>
                                @endif
                            </td>
                            {{-- PTS --}}
                            <td class="px-4 py-4 text-center">
                                @if($sg['uts_avg'] !== null)
                                    <span class="inline-block px-3 py-1 rounded-lg font-bold text-xs min-w-[3rem] {{ $sg['uts_avg'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($sg['uts_avg'] >= 60 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                        {{ number_format($sg['uts_avg'], 0) }}
                                    </span>
                                @else
                                    <span class="text-gray-200 text-xs">&mdash;</span>
                                @endif
                            </td>
                            {{-- PAS --}}
                            <td class="px-4 py-4 text-center">
                                @if($sg['uas_avg'] !== null)
                                    <span class="inline-block px-3 py-1 rounded-lg font-bold text-xs min-w-[3rem] {{ $sg['uas_avg'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($sg['uas_avg'] >= 60 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                        {{ number_format($sg['uas_avg'], 0) }}
                                    </span>
                                @else
                                    <span class="text-gray-200 text-xs">&mdash;</span>
                                @endif
                            </td>
                            {{-- Sikap --}}
                            <td class="px-4 py-4 text-center">
                                @if($sg['sikap_avg'] !== null)
                                    <span class="inline-block px-3 py-1 rounded-lg font-bold text-xs min-w-[3rem] {{ $sg['sikap_avg'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($sg['sikap_avg'] >= 60 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                        {{ number_format($sg['sikap_avg'], 0) }}
                                    </span>
                                @else
                                    <span class="text-gray-200 text-xs">&mdash;</span>
                                @endif
                            </td>
                            @if($showReportCard)
                            {{-- Nilai Akhir --}}
                            <td class="px-4 py-4 text-center">
                                @if($sg['final_score'] !== null)
                                    <span class="inline-block px-3 py-1.5 rounded-xl font-bold text-sm shadow-sm {{ $sg['final_score'] >= $sg['subject']->kkm ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white' : ($sg['final_score'] >= 60 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white' : 'bg-gradient-to-br from-rose-500 to-red-600 text-white') }}">
                                        {{ number_format($sg['final_score'], 1) }}
                                    </span>
                                @else
                                    <span class="text-gray-200 text-xs">&mdash;</span>
                                @endif
                            </td>
                            {{-- Predikat --}}
                            <td class="px-4 py-4 text-center">
                                @if($sg['predicate'] !== null)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $sg['predicate'] == 'A' ? 'bg-green-100 text-green-700' : ($sg['predicate'] == 'B' ? 'bg-blue-100 text-blue-700' : ($sg['predicate'] == 'C' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')) }}">
                                        {{ $sg['predicate'] }}
                                    </span>
                                @else
                                    <span class="text-gray-200 text-xs">&mdash;</span>
                                @endif
                            </td>
                            @endif
                            <td class="px-4 py-4 text-center">
                                <i class="fas fa-chevron-down text-gray-300 transition-transform duration-300" :class="expandedRow === {{ $subjectId }} ? 'rotate-180 text-amber-500' : ''"></i>
                            </td>
                        </tr>

                        {{-- ⏬ Expanded Source Details ⏬ --}}
                        <tr class="bg-gray-50/50" x-show="expandedRow === {{ $subjectId }}" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            <td colspan="{{ $showReportCard ? 9 : 7 }}" class="px-8 py-6">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                    {{-- Tugas Group --}}
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b pb-2 flex items-center justify-between">
                                            <span>Tugas & Kuis</span>
                                            <span class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">{{ $sg['tugas_grades']->count() }}</span>
                                        </h4>
                                        <div class="space-y-2">
                                            @forelse($sg['tugas_grades'] as $grade)
                                                @php $info = $getSourceInfo($grade); @endphp
                                                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm hover:border-blue-200 transition flex flex-col gap-2">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0">
                                                                <i class="fas {{ $info->icon }} text-xs"></i>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <p class="text-xs font-bold text-gray-700 leading-none truncate">{{ $info->label }}</p>
                                                                <p class="text-[10px] text-gray-400 mt-1.5 font-medium leading-none">{{ $info->date }}</p>
                                                            </div>
                                                        </div>
                                                        <span class="inline-block px-2 py-0.5 rounded-md font-bold text-xs bg-blue-50 text-blue-600 flex-shrink-0">
                                                            {{ number_format($grade->score, 0) }}
                                                        </span>
                                                    </div>
                                                    @if($info->notes)
                                                    <div class="text-[10px] text-gray-500 bg-gray-50 px-2 py-1 rounded border border-gray-100/50 truncate" title="{{ $info->notes }}">
                                                        {{ $info->notes }}
                                                    </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-xs text-gray-300 text-center italic py-2">Tidak ada data</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    {{-- PTS Group --}}
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b pb-2 flex items-center justify-between">
                                            <span>PTS (UTS)</span>
                                            <span class="bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded">{{ $sg['uts_count'] }}</span>
                                        </h4>
                                        <div class="space-y-2">
                                            @forelse($sg['uts_grades'] as $grade)
                                                @php $info = $getSourceInfo($grade); @endphp
                                                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm hover:border-indigo-200 transition flex flex-col gap-2">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0">
                                                                <i class="fas {{ $info->icon }} text-xs"></i>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <p class="text-xs font-bold text-gray-700 leading-none truncate">{{ $info->label }}</p>
                                                                <p class="text-[10px] text-gray-400 mt-1.5 font-medium leading-none">{{ $info->date }}</p>
                                                            </div>
                                                        </div>
                                                        <span class="inline-block px-2 py-0.5 rounded-md font-bold text-xs bg-indigo-50 text-indigo-600 flex-shrink-0">
                                                            {{ number_format($grade->score, 0) }}
                                                        </span>
                                                    </div>
                                                    @if($info->notes)
                                                    <div class="text-[10px] text-gray-500 bg-gray-50 px-2 py-1 rounded border border-gray-100/50 truncate" title="{{ $info->notes }}">
                                                        {{ $info->notes }}
                                                    </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-xs text-gray-300 text-center italic py-2">Tidak ada data</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    {{-- PAS Group --}}
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b pb-2 flex items-center justify-between">
                                            <span>PAS (UAS)</span>
                                            <span class="bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded">{{ $sg['uas_count'] }}</span>
                                        </h4>
                                        <div class="space-y-2">
                                            @forelse($sg['uas_grades'] as $grade)
                                                @php $info = $getSourceInfo($grade); @endphp
                                                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm hover:border-purple-200 transition flex flex-col gap-2">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0">
                                                                <i class="fas {{ $info->icon }} text-xs"></i>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <p class="text-xs font-bold text-gray-700 leading-none truncate">{{ $info->label }}</p>
                                                                <p class="text-[10px] text-gray-400 mt-1.5 font-medium leading-none">{{ $info->date }}</p>
                                                            </div>
                                                        </div>
                                                        <span class="inline-block px-2 py-0.5 rounded-md font-bold text-xs bg-purple-50 text-purple-600 flex-shrink-0">
                                                            {{ number_format($grade->score, 0) }}
                                                        </span>
                                                    </div>
                                                    @if($info->notes)
                                                    <div class="text-[10px] text-gray-500 bg-gray-50 px-2 py-1 rounded border border-gray-100/50 truncate" title="{{ $info->notes }}">
                                                        {{ $info->notes }}
                                                    </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-xs text-gray-300 text-center italic py-2">Tidak ada data</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    {{-- Sikap Group --}}
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b pb-2 flex items-center justify-between">
                                            <span>Penilaian Sikap</span>
                                            <span class="bg-teal-100 text-teal-600 px-1.5 py-0.5 rounded">{{ $sg['sikap_grades']->count() }}</span>
                                        </h4>
                                        <div class="space-y-2">
                                            @forelse($sg['sikap_grades'] as $grade)
                                                @php $info = $getSourceInfo($grade); @endphp
                                                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm hover:border-teal-200 transition flex flex-col gap-2">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0">
                                                                <i class="fas {{ $info->icon }} text-xs"></i>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <p class="text-xs font-bold text-gray-700 leading-none truncate">{{ $info->label }}</p>
                                                                <p class="text-[10px] text-gray-400 mt-1.5 font-medium leading-none">{{ $info->date }}</p>
                                                            </div>
                                                        </div>
                                                        <span class="inline-block px-2 py-0.5 rounded-md font-bold text-xs bg-teal-50 text-teal-600 flex-shrink-0">
                                                            {{ number_format($grade->score, 0) }}
                                                        </span>
                                                    </div>
                                                    @if($info->notes)
                                                    <div class="text-[10px] text-gray-500 bg-gray-50 px-2 py-1 rounded border border-gray-100/50 truncate" title="{{ $info->notes }}">
                                                        {{ $info->notes }}
                                                    </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-xs text-gray-300 text-center italic py-2">Tidak ada data</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                {{-- Footer totals --}}
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-200">
                        <td colspan="2" class="px-4 py-4 text-right font-bold text-gray-600 text-sm">Rata-rata Keseluruhan</td>
                        <td class="px-4 py-4 text-center">
                            @php $avgTugas = $subjectGrades->whereNotNull('tugas_avg')->avg('tugas_avg'); @endphp
                            @if($avgTugas)
                                <span class="font-bold text-sm text-gray-700">{{ round($avgTugas, 1) }}</span>
                            @else <span class="text-gray-300">&mdash;</span> @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @php $avgPts = $subjectGrades->whereNotNull('uts_avg')->avg('uts_avg'); @endphp
                            @if($avgPts)
                                <span class="font-bold text-sm text-gray-700">{{ round($avgPts, 1) }}</span>
                            @else <span class="text-gray-300">&mdash;</span> @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @php $avgPas = $subjectGrades->whereNotNull('uas_avg')->avg('uas_avg'); @endphp
                            @if($avgPas)
                                <span class="font-bold text-sm text-gray-700">{{ round($avgPas, 1) }}</span>
                            @else <span class="text-gray-300">&mdash;</span> @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @php $avgSikap = $subjectGrades->whereNotNull('sikap_avg')->avg('sikap_avg'); @endphp
                            @if($avgSikap)
                                <span class="font-bold text-sm text-gray-700">{{ round($avgSikap, 1) }}</span>
                            @else <span class="text-gray-300">&mdash;</span> @endif
                        </td>
                        @if($showReportCard)
                        <td class="px-4 py-4 text-center">
                            @php $avgFinal = $subjectGrades->whereNotNull('final_score')->avg('final_score'); @endphp
                            @if($avgFinal)
                                <span class="inline-block px-3 py-1.5 rounded-xl font-bold text-sm shadow-sm {{ $avgFinal >= 75 ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white' : ($avgFinal >= 60 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white' : 'bg-gradient-to-br from-rose-500 to-red-600 text-white') }}">
                                    {{ round($avgFinal, 1) }}
                                </span>
                            @else <span class="text-gray-300">&mdash;</span> @endif
                        </td>
                        {{-- Average Predicate --}}
                        <td class="px-4 py-4 text-center">
                            @php
                                $avgKkm = $subjectGrades->avg(fn($sg) => $sg['subject']->kkm ?? 75) ?: 75;
                                $overallPredicate = $avgFinal ? \App\Models\FinalGrade::scoreToPredicate($avgFinal, (int)$avgKkm) : null;
                            @endphp
                            @if($overallPredicate)
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $overallPredicate == 'A' ? 'bg-green-100 text-green-700' : ($overallPredicate == 'B' ? 'bg-blue-100 text-blue-700' : ($overallPredicate == 'C' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')) }}">
                                    {{ $overallPredicate }}
                                </span>
                            @else <span class="text-gray-300">&mdash;</span> @endif
                        </td>
                        @endif
                        <td class="bg-gray-50 border-t-0"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-clipboard text-2xl text-gray-300"></i>
            </div>
            <h3 class="text-gray-600 font-semibold mb-1">Belum Ada Nilai</h3>
            <p class="text-gray-400 text-sm">Nilai untuk semester ini belum tersedia.</p>
        </div>
    @endif

    {{-- Rapor Digital --}}
    @if($showReportCard && $reportCards->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-file-alt text-blue-500"></i> Rapor Digital
                </h2>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($reportCards as $rc)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md hover:border-blue-200 transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-800">{{ $rc->semester->semester_name ?? 'Semester' }}</span>
                                <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                                    <i class="fas fa-check-circle mr-0.5"></i>Published
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">
                                {{ $rc->academicYear->year ?? '' }} · {{ $rc->classroom->class_name ?? '' }}
                            </p>
                            <div class="grid grid-cols-3 gap-2 text-center mb-3">
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <p class="text-xs text-gray-500">Rata-rata</p>
                                    <p class="font-bold text-amber-600">{{ number_format($rc->average_score, 1) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <p class="text-xs text-gray-500">Peringkat</p>
                                    <p class="font-bold text-blue-600">#{{ $rc->rank }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <p class="text-xs text-gray-500">Predikat</p>
                                    <p class="font-bold text-emerald-600">{{ $rc->predicate ?? '-' }}</p>
                                </div>
                            </div>
                            <a href="{{ route('siswa.raport.print', $rc->id) }}" target="_blank"
                               class="block text-center bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-sm font-medium py-2.5 rounded-xl shadow-sm transition-all">
                                <i class="fas fa-print mr-1"></i> Cetak Rapor
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Radar Chart
        const ctxRadar = document.getElementById('radarChart').getContext('2d');
        new Chart(ctxRadar, {
            type: 'radar',
            data: {
                labels: @json($chartSubjects),
                datasets: [{
                    label: 'Rata-rata Nilai',
                    data: @json($chartAverages),
                    backgroundColor: 'rgba(245, 158, 11, 0.2)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(245, 158, 11, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(245, 158, 11, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { display: true },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: { stepSize: 20 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Bar Chart
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: @json($chartSubjects),
                datasets: [
                    {
                        label: 'Nilai Rata-rata',
                        data: @json($chartAverages),
                        backgroundColor: 'rgba(59, 130, 246, 0.85)',
                        borderRadius: 6,
                    },
                    {
                        label: 'KKM',
                        data: @json($chartKkms),
                        backgroundColor: 'rgba(239, 68, 68, 0.4)',
                        borderRadius: 6,
                        type: 'line',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { stepSize: 20 }
                    }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });

        @if($monthlyGrades->isNotEmpty())
        // Line Chart
        const ctxLine = document.getElementById('lineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: @json($monthlyGrades->pluck('label')),
                datasets: [{
                    label: 'Rata-rata Nilai Bulanan',
                    data: @json($monthlyGrades->pluck('avg')),
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(16, 185, 129, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        suggestedMin: 50,
                        suggestedMax: 100,
                        ticks: { stepSize: 10 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
        @endif
    });
</script>
@endpush
@endsection
