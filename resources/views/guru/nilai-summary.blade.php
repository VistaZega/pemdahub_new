@extends('layouts.guru')
@section('title', 'Rekap Nilai - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #312e81 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-chart-bar text-black"></i>
                    </div>
                    Rekap Nilai Akhir
                </h1>
                <p class="text-xs md:text-sm font-bold text-indigo-200 mt-1" style="color: #c7d2fe !important;">Ringkasan dan kalkulasi nilai siswa per semester</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('guru.nilai') }}" class="text-xs bg-white hover:bg-slate-100 text-black border-2 border-black font-black px-4 py-2.5 rounded-2xl transition uppercase tracking-wider shadow-md">
                    <i class="fas fa-list mr-1"></i> Daftar Nilai
                </a>
                <a href="{{ route('guru.nilai.input') }}" class="text-xs bg-amber-400 hover:bg-amber-300 text-black border-2 border-black font-black px-4 py-2.5 rounded-2xl transition uppercase tracking-wider shadow-md">
                    <i class="fas fa-edit mr-1"></i> Input Nilai
                </a>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('guru.nilai.summary') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-5 py-3">
            <h2 class="text-white font-bold flex items-center gap-2">
                <i class="fas fa-filter"></i> Filter Rekap
            </h2>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Semester</label>
                <select name="semester_id" onchange="this.form.submit()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                            {{ $sem->semester_name ?? 'Semester '.$sem->semester_number }} - {{ $sem->academicYear->year ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas</label>
                <select name="classroom_id" onchange="this.form.submit()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classrooms as $cr)
                        <option value="{{ $cr->id }}" {{ $selectedClassroomId == $cr->id ? 'selected' : '' }}>
                            {{ $cr->class_name }}{{ $cr->is_homeroom ? ' ★ Wali Kelas' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Mata Pelajaran</label>
                <select name="subject_id" onchange="this.form.submit()" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300" @if(!$selectedClassroomId) disabled @endif>
                    <option value="">-- Pilih Mapel --</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" {{ $selectedSubjectId == $subj->id ? 'selected' : '' }}>
                            {{ $subj->subject_name ?? $subj->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    {{-- Grade Weight Info --}}
    @if($gradeWeight)
    <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-balance-scale text-blue-500 mt-0.5"></i>
            <div class="text-sm text-blue-700">
                <span class="font-semibold">Bobot Perhitungan:</span>
                Tugas <span class="font-bold">{{ number_format($gradeWeight->tugas_weight, 0) }}%</span> |
                PTS <span class="font-bold">{{ number_format($gradeWeight->pts_weight, 0) }}%</span> |
                PAS <span class="font-bold">{{ number_format($gradeWeight->pas_weight, 0) }}%</span> |
                Sikap <span class="font-bold">{{ number_format($gradeWeight->sikap_weight, 0) }}%</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Summary Table --}}
    @if($studentSummary->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-table text-blue-500"></i> Rekap Nilai Per Siswa
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold w-12">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Siswa</th>
                        <th class="px-4 py-3 text-center font-semibold">Tugas</th>
                        <th class="px-4 py-3 text-center font-semibold">PTS</th>
                        <th class="px-4 py-3 text-center font-semibold">PAS</th>
                        <th class="px-4 py-3 text-center font-semibold">Sikap</th>
                        <th class="px-4 py-3 text-center font-semibold bg-blue-50">Nilai Akhir</th>
                        <th class="px-4 py-3 text-center font-semibold">Predikat</th>
                        <th class="px-4 py-3 text-center font-semibold">KKM</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($studentSummary as $idx => $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $item['student']->full_name }}</td>
                            @if($item['grades'])
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $item['grades']['tugas'] >= 75 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ number_format($item['grades']['tugas'], 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $item['grades']['pts'] >= 75 ? 'bg-green-100 text-green-700' : ($item['grades']['pts'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                                        {{ $item['grades']['pts'] > 0 ? number_format($item['grades']['pts'], 0) : '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $item['grades']['pas'] >= 75 ? 'bg-green-100 text-green-700' : ($item['grades']['pas'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                                        {{ $item['grades']['pas'] > 0 ? number_format($item['grades']['pas'], 0) : '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $item['grades']['sikap'] >= 75 ? 'bg-green-100 text-green-700' : ($item['grades']['sikap'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                                        {{ $item['grades']['sikap'] > 0 ? number_format($item['grades']['sikap'], 0) : '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center bg-blue-50">
                                    <span class="inline-block px-2.5 py-1 rounded-lg font-bold text-sm {{ $item['grades']['final_score'] >= 80 ? 'bg-green-100 text-green-700' : ($item['grades']['final_score'] >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ number_format($item['grades']['final_score'], 1) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $item['grades']['predicate'] === 'A' ? 'bg-green-100 text-green-700' : ($item['grades']['predicate'] === 'B' ? 'bg-blue-100 text-blue-700' : ($item['grades']['predicate'] === 'C' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')) }}">
                                        {{ $item['grades']['predicate'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $item['grades']['kkm'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($item['grades']['is_passed'])
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold">Tuntas</span>
                                    @else
                                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold">Belum Tuntas</span>
                                    @endif
                                </td>
                            @else
                                <td colspan="8" class="px-4 py-3 text-center text-gray-400 text-xs">Belum ada nilai</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Stats footer --}}
        @php
            $withGrades = $studentSummary->filter(fn($i) => $i['grades'] !== null);
            $avgFinal = $withGrades->count() > 0 ? $withGrades->avg('grades.final_score') : 0;
            $passedCount = $withGrades->where('grades.is_passed', true)->count();
        @endphp
        <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-6 text-sm text-gray-600">
            <span>Total Siswa: <strong>{{ $studentSummary->count() }}</strong></span>
            <span>Sudah Dinilai: <strong>{{ $withGrades->count() }}</strong></span>
            <span>Rata-rata Akhir: <strong class="{{ $avgFinal >= 75 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($avgFinal, 1) }}</strong></span>
            <span>Tuntas: <strong class="text-green-600">{{ $passedCount }}</strong></span>
            <span>Belum Tuntas: <strong class="text-red-600">{{ $withGrades->count() - $passedCount }}</strong></span>
        </div>
    </div>
    @elseif($selectedClassroomId && $selectedSubjectId)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
        <i class="fas fa-clipboard text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">Belum ada data nilai untuk filter ini.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
        <i class="fas fa-hand-pointer text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">Pilih kelas dan mata pelajaran untuk melihat rekap nilai.</p>
    </div>
    @endif
</div>
@endsection
