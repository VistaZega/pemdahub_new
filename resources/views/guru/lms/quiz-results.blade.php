@extends('layouts.guru')

@section('title', 'Rekap Hasil Kuis - ' . $quiz->title)

@push('styles')
<style>
    .animate-fade-in { animation: fadeIn 0.35s ease both; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stat-card-gradient-1 { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
    .stat-card-gradient-2 { background: linear-gradient(135deg, #10b981 0%, #047857 100%); }
    .stat-card-gradient-3 { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
    .stat-card-gradient-4 { background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); }
</style>
@endpush

@section('content')
@php
    $maxRangeCount = max(max($ranges), 1);
@endphp

<div class="space-y-6 animate-fade-in" x-data="{ activeTab: 'recap', search: '' }">
    {{-- Header & Actions --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.lms.quizzes.show', $quiz->id) }}" class="w-10 h-10 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors shadow-xs">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-0.5">
                    <span>{{ $course->name }}</span>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span>Kuis</span>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-indigo-600 font-semibold">Rekapitulasi Nilai</span>
                </div>
                <h2 class="text-xl md:text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                    {{ $quiz->title }}
                    @if($quiz->is_published)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Aktif</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-300">Draft</span>
                    @endif
                </h2>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('guru.lms.quizzes.results.export', $quiz->id) }}{{ $selectedClassroomId ? '?classroom_id=' . $selectedClassroomId : '' }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-xs transition transform active:scale-95">
                <i class="fas fa-file-excel text-sm"></i>
                <span>Export Excel</span>
            </a>
            <a href="{{ route('guru.lms.quizzes.show', $quiz->id) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                <i class="fas fa-cog text-gray-500"></i>
                <span>Detail Soal</span>
            </a>
        </div>
    </div>

    {{-- Filter Rombel & Parameter Kuis --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Rombel chips --}}
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex items-center gap-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider shrink-0 mr-1">
                    <i class="fas fa-users text-gray-400"></i> Rombel:
                </div>
                <a href="{{ route('guru.lms.quizzes.results', $quiz->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition
                          {{ !$selectedClassroomId ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 hover:text-black' }}">
                    <i class="fas fa-th-large text-[10px]"></i> Semua Rombel
                </a>
                @foreach($classrooms as $classroom)
                <a href="{{ route('guru.lms.quizzes.results', $quiz->id) }}?classroom_id={{ $classroom->id }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition
                          {{ $selectedClassroomId == $classroom->id ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 hover:text-black' }}">
                    <i class="fas fa-users text-[10px]"></i> {{ $classroom->class_name }}
                </a>
                @endforeach
            </div>

            {{-- Info Badges --}}
            <div class="flex items-center gap-2 text-xs flex-wrap">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-semibold">
                    <i class="fas fa-bullseye text-amber-500"></i> KKM: <strong>{{ $quiz->passing_score }}%</strong>
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200 font-semibold">
                    <i class="fas fa-redo text-blue-500"></i> Batas: <strong>{{ $quiz->max_attempts ?? 1 }}x Percobaan</strong>
                </span>
                @if($quiz->time_limit)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-800 border border-purple-200 font-semibold">
                    <i class="fas fa-clock text-purple-500"></i> Durasi: <strong>{{ $quiz->time_limit }} Menit</strong>
                </span>
                @endif
            </div>
        </div>

        @if($selectedClassroom)
        <div class="pt-2 border-t border-gray-100 text-xs text-gray-500 flex items-center gap-1.5 font-medium">
            <i class="fas fa-filter text-indigo-500"></i>
            Menampilkan data siswa untuk rombel: <strong class="text-gray-800">{{ $selectedClassroom->class_name }}</strong>
        </div>
        @endif
    </div>

    {{-- Stats Cards (4 Grid) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Siswa --}}
        <div class="stat-card-gradient-1 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-15">
                <i class="fas fa-user-graduate text-6xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-blue-100 uppercase">Total Siswa Rombel</p>
            <p class="text-3xl font-black mt-1">{{ $totalStudents }}</p>
            <p class="text-[11px] text-blue-100 mt-2 font-medium flex items-center gap-1">
                <i class="fas fa-info-circle text-[10px]"></i> Siswa terdaftar pada kelas
            </p>
        </div>

        {{-- Partisipasi --}}
        <div class="stat-card-gradient-4 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-15">
                <i class="fas fa-user-clock text-6xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-amber-100 uppercase">Sudah Mengerjakan</p>
            <p class="text-3xl font-black mt-1">
                {{ $completedStudentsCount }} <span class="text-sm font-semibold text-amber-100">/ {{ $totalStudents }}</span>
            </p>
            <p class="text-[11px] text-amber-100 mt-2 font-medium">
                {{ $totalStudents > 0 ? round(($completedStudentsCount / $totalStudents) * 100) : 0 }}% partisipasi ({{ $unattemptedStudentsCount }} belum)
            </p>
        </div>

        {{-- Kelulusan KKM --}}
        <div class="stat-card-gradient-2 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-15">
                <i class="fas fa-check-circle text-6xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-emerald-100 uppercase">Tuntas KKM</p>
            <p class="text-3xl font-black mt-1">
                {{ $passedStudentsCount }} <span class="text-sm font-semibold text-emerald-100">/ {{ max($completedStudentsCount, 1) }}</span>
            </p>
            <p class="text-[11px] text-emerald-100 mt-2 font-medium">
                {{ $completedStudentsCount > 0 ? round(($passedStudentsCount / $completedStudentsCount) * 100) : 0 }}% dari siswa yang mengerjakan
            </p>
        </div>

        {{-- Rata-rata Skor Tertinggi --}}
        <div class="stat-card-gradient-3 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-15">
                <i class="fas fa-award text-6xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-purple-100 uppercase">Rata-rata Skor Terbaik</p>
            <p class="text-3xl font-black mt-1">{{ $avgScore ? number_format($avgScore, 1) . '%' : '-' }}</p>
            <p class="text-[11px] text-purple-100 mt-2 font-medium">Dari nilai terbaik masing-masing siswa</p>
        </div>
    </div>

    {{-- Score Distribution & Search Bar --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Distribution Chart Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-sm mb-3 tracking-wide flex items-center gap-2">
                    <i class="fas fa-chart-bar text-indigo-600"></i> Distribusi Nilai Tertinggi
                </h3>
                <div class="space-y-3">
                    @foreach($ranges as $range => $count)
                    @php
                        $percentage = ($count / $maxRangeCount) * 100;
                        $color = $range === '81-100' ? 'bg-emerald-500' : ($range === '61-80' ? 'bg-blue-500' : ($range === '41-60' ? 'bg-amber-500' : ($range === '21-40' ? 'bg-orange-500' : 'bg-rose-500')));
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-medium">
                            <span class="text-gray-600">Skor {{ $range }}%</span>
                            <span class="text-gray-800 font-bold">{{ $count }} siswa</span>
                        </div>
                        <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden border border-gray-200">
                            <div class="h-full {{ $color }} rounded-full transition-all duration-700" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="pt-3 border-t border-gray-100 mt-3 text-[11px] text-gray-400 font-medium leading-relaxed">
                * Distribusi dihitung berdasarkan skor tertinggi (nilai akhir) tiap siswa.
            </div>
        </div>

        {{-- Table Container --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            {{-- Tabs & Search Header --}}
            <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
                <div class="flex items-center gap-2">
                    <button type="button" 
                            @click="activeTab = 'recap'" 
                            :class="activeTab === 'recap' ? 'bg-white text-indigo-700 shadow-xs border-gray-200' : 'text-gray-600 hover:text-gray-900 border-transparent'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-black border transition flex items-center gap-1.5">
                        <i class="fas fa-table-cells text-indigo-500"></i> Rekap Per Siswa
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-indigo-100 text-indigo-800">{{ $recapData->count() }}</span>
                    </button>
                    <button type="button" 
                            @click="activeTab = 'logs'" 
                            :class="activeTab === 'logs' ? 'bg-white text-indigo-700 shadow-xs border-gray-200' : 'text-gray-600 hover:text-gray-900 border-transparent'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-black border transition flex items-center gap-1.5">
                        <i class="fas fa-list-check text-purple-500"></i> Log Semua Percobaan
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-purple-100 text-purple-800">{{ $attempts->count() }}</span>
                    </button>
                </div>

                {{-- Live Search Input --}}
                <div class="relative w-full sm:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" 
                           x-model="search" 
                           placeholder="Cari nama / NISN..." 
                           class="w-full pl-8 pr-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>
            </div>

            {{-- TAB 1: REKAP PER SISWA (KOLOM PERCOBAAN DINAMIS + NILAI TERTINGGI) --}}
            <div x-show="activeTab === 'recap'" class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/80 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 min-w-[180px]">Siswa</th>
                            <th class="px-3 py-3 text-center min-w-[90px]">Kelas</th>
                            
                            {{-- Kolom Percobaan Dinamis (P1, P2, P3, ...) --}}
                            @for($i = 1; $i <= $displayAttemptsCount; $i++)
                            <th class="px-3 py-3 text-center min-w-[70px]">
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-redo text-[9px] text-gray-400"></i> P{{ $i }}
                                </span>
                            </th>
                            @endfor

                            {{-- Nilai Tertinggi (Skor Akhir) --}}
                            <th class="px-4 py-3 text-center min-w-[110px] bg-indigo-50/60 text-indigo-900 border-x border-indigo-100">
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-star text-amber-500"></i> Nilai Tertinggi
                                </span>
                            </th>
                            <th class="px-3 py-3 text-center min-w-[100px]">Status</th>
                            <th class="px-3 py-3 text-center pr-4 min-w-[80px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recapData as $index => $row)
                        @php
                            $student = $row['student'];
                            $studentName = $row['student_name'];
                            $initials = strtoupper(substr($studentName, 0, 1));
                        @endphp
                        <tr class="hover:bg-indigo-50/20 transition-colors group"
                            x-show="!search || '{{ strtolower($studentName) }}'.includes(search.toLowerCase()) || '{{ strtolower($row['nisn']) }}'.includes(search.toLowerCase())">
                            
                            {{-- No --}}
                            <td class="px-4 py-3 text-center text-gray-500 font-semibold">{{ $index + 1 }}</td>

                            {{-- Siswa --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-[11px] font-bold text-white shadow-xs shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 group-hover:text-indigo-600 transition">{{ $studentName }}</p>
                                        <p class="text-[10px] text-gray-400 font-medium">NISN: {{ $row['nisn'] }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Kelas --}}
                            <td class="px-3 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-[10px] font-semibold">
                                    {{ $row['classroom_name'] }}
                                </span>
                            </td>

                            {{-- Nilai Per Percobaan (P1, P2, P3...) --}}
                            @for($i = 1; $i <= $displayAttemptsCount; $i++)
                            @php
                                $score = $row['attempts_by_index'][$i] ?? null;
                                $attObj = $row['attempts_obj_by_index'][$i] ?? null;
                            @endphp
                            <td class="px-3 py-3 text-center">
                                @if($score !== null && $attObj)
                                    <a href="{{ route('guru.lms.quizzes.attempts.show', $attObj->id) }}" 
                                       title="Lihat Jawaban Percobaan {{ $i }} ({{ $attObj->finished_at ? $attObj->finished_at->format('d/m H:i') : '' }})"
                                       class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg font-black text-xs transition transform hover:scale-105 border {{ $score >= $quiz->passing_score ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}">
                                        {{ number_format($score, 1) }}
                                    </a>
                                @elseif($attObj && !$attObj->finished_at)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Sedang dikerjakan">
                                        Proses
                                    </span>
                                @else
                                    <span class="text-gray-300 font-semibold">-</span>
                                @endif
                            </td>
                            @endfor

                            {{-- Nilai Tertinggi (Skor Akhir) --}}
                            <td class="px-4 py-3 text-center bg-indigo-50/30 border-x border-indigo-100">
                                @if($row['best_score'] !== null)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-black shadow-xs {{ $row['best_score'] >= $quiz->passing_score ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                        @if($row['best_score'] >= $quiz->passing_score)
                                            <i class="fas fa-star text-[9px] text-amber-300"></i>
                                        @endif
                                        {{ number_format($row['best_score'], 1) }}%
                                    </span>
                                @else
                                    <span class="text-gray-400 font-semibold">-</span>
                                @endif
                            </td>

                            {{-- Status Kelulusan --}}
                            <td class="px-3 py-3 text-center">
                                @if($row['status_type'] === 'passed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Lulus
                                    </span>
                                @elseif($row['status_type'] === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span> Remedial
                                    </span>
                                @elseif($row['status_type'] === 'in_progress')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fas fa-spinner fa-spin text-[9px]"></i> Pengerjaan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                        Belum Mengerjakan
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-3 py-3 text-center pr-4">
                                @if($row['best_attempt'])
                                    <a href="{{ route('guru.lms.quizzes.attempts.show', $row['best_attempt']->id) }}" 
                                       class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-2.5 py-1 rounded-lg text-xs font-bold transition"
                                       title="Lihat Lembar Jawaban Terbaik">
                                        <i class="fas fa-eye text-[10px]"></i> Detail
                                    </a>
                                @elseif($row['latest_attempt'])
                                    <a href="{{ route('guru.lms.quizzes.attempts.show', $row['latest_attempt']->id) }}" 
                                       class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 hover:bg-gray-200 px-2.5 py-1 rounded-lg text-xs font-bold transition">
                                        <i class="fas fa-eye text-[10px]"></i> Detail
                                    </a>
                                @else
                                    <span class="text-gray-300 font-semibold">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 5 + $displayAttemptsCount }}" class="px-5 py-10 text-center text-gray-400 italic">
                                Belum ada data siswa untuk kuis ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB 2: LOG SEMUA PERCOBAAN (AUDIT TRAIL) --}}
            <div x-show="activeTab === 'logs'" class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/80 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-4 py-3 pl-5">Siswa</th>
                            <th class="px-4 py-3 text-center">Percobaan Ke-</th>
                            <th class="px-4 py-3">Waktu Selesai</th>
                            <th class="px-4 py-3 text-center">Durasi</th>
                            <th class="px-4 py-3 text-center">Skor</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center pr-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($attempts as $attempt)
                        @php
                            $studentName = $attempt->student->full_name ?? $attempt->student->user->name ?? 'N/A';
                            $initials = strtoupper(substr($studentName, 0, 1));
                        @endphp
                        <tr class="hover:bg-indigo-50/20 transition-colors"
                            x-show="!search || '{{ strtolower($studentName) }}'.includes(search.toLowerCase()) || '{{ strtolower($attempt->student->nisn ?? '') }}'.includes(search.toLowerCase())">
                            <td class="px-4 py-3 pl-5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-[10px] font-bold text-white shadow-xs">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $studentName }}</p>
                                        <p class="text-[10px] text-gray-400 font-medium">NISN: {{ $attempt->student->nisn ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-gray-700">
                                Percobaan #{{ $attempt->id }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                @if($attempt->finished_at)
                                    <span class="font-medium">{{ $attempt->finished_at->format('d M Y H:i') }}</span>
                                @else
                                    <span class="text-amber-600 font-semibold italic">Sedang berjalan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600 font-medium">
                                {{ $attempt->duration ? $attempt->duration . ' mnt' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold">
                                @if($attempt->score !== null)
                                    <span class="text-xs font-black {{ $attempt->is_passed ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ number_format($attempt->score, 1) }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($attempt->is_passed === true)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lulus
                                    </span>
                                @elseif($attempt->is_passed === false)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Gagal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Proses
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center pr-5">
                                <a href="{{ route('guru.lms.quizzes.attempts.show', $attempt->id) }}" class="inline-flex items-center bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-2.5 py-1 rounded-lg text-xs font-bold transition">
                                    <i class="fas fa-edit mr-1 text-[10px]"></i> Periksa
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-400 italic">Belum ada riwayat pengerjaan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
