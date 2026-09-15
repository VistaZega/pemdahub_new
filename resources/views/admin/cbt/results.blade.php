@extends('layouts.admin')
@section('title', 'Hasil Ujian - ' . $exam->exam_title)
@section('content')
<div class="space-y-8" x-data="{ activeTab: 'results', statusFilter: 'all' }">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-green-600 to-teal-700 rounded-2xl p-8 text-white shadow-xl shadow-emerald-900/10">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.cbt.show', $exam) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Hasil Ujian</h1>
                        @if($selectedClassroom)
                        <span class="px-3 py-1 bg-white/20 border border-white/30 rounded-lg text-sm font-bold text-white">
                            {{ $selectedClassroom->class_name }}
                        </span>
                        @endif
                    </div>
                    <p class="text-emerald-50 mt-1 text-base">{{ $exam->exam_title }} — {{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                {{-- Export Status Siswa (Excel) --}}
                <a href="{{ route('admin.cbt.export-participation', ['exam' => $exam, 'classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center px-4 py-2.5 bg-emerald-800/90 hover:bg-emerald-900 text-white rounded-xl font-semibold transition shadow-lg text-sm border border-emerald-500/30">
                    <i class="fas fa-file-excel mr-2 text-emerald-300"></i>Export Rekap Excel
                </a>

                @if($hasEssayQuestions ?? false)
                <a href="{{ route('admin.cbt.grade-essays', ['exam' => $exam, 'classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-semibold transition shadow-lg shadow-amber-900/20 text-sm">
                    <i class="fas fa-pen-nib mr-2"></i>Koreksi Esai
                    @if(($pendingEssaysCount ?? 0) > 0)
                    <span class="ml-2 px-2 py-0.5 text-xs bg-white text-amber-800 font-bold rounded-full">{{ $pendingEssaysCount }}</span>
                    @endif
                </a>
                @endif

                <a href="{{ route('admin.cbt.show', $exam) }}" class="inline-flex items-center px-4 py-2.5 bg-white/15 text-white rounded-xl hover:bg-white/25 transition border border-gray-200 text-sm">
                    <i class="fas fa-eye mr-2"></i>Detail Ujian
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Bar & Navigation Tabs --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            {{-- Dropdown Filter Per Kelas --}}
            <form method="GET" action="{{ route('admin.cbt.results', $exam) }}" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold">
                        <i class="fas fa-filter"></i>
                    </div>
                    <label for="classroom_id" class="text-sm font-bold text-gray-700">Filter Kelas:</label>
                </div>
                <div class="relative min-w-[220px]">
                    <select name="classroom_id" id="classroom_id" onchange="this.form.submit()" class="w-full appearance-none rounded-xl border border-gray-300 bg-white py-2.5 pl-4 pr-10 text-sm font-semibold text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition">
                        <option value="">-- Semua Kelas Terdaftar ({{ $accessibleClassrooms->count() }}) --</option>
                        @foreach($accessibleClassrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassroomId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->class_name }}
                        </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>

                @if($selectedClassroomId)
                <a href="{{ route('admin.cbt.results', $exam) }}" class="inline-flex items-center px-3 py-2 text-xs font-semibold text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 rounded-xl transition">
                    <i class="fas fa-times mr-1.5"></i>Reset Filter
                </a>
                @endif
            </form>

            {{-- Mode Tab Buttons --}}
            <div class="flex items-center bg-gray-100 p-1.5 rounded-xl self-start md:self-auto">
                <button @click="activeTab = 'results'" :class="activeTab === 'results' ? 'bg-white text-gray-900 font-bold shadow-sm' : 'text-gray-600 hover:text-gray-900 font-medium'" class="px-4 py-2 rounded-lg text-sm transition-all flex items-center gap-2">
                    <i class="fas fa-trophy text-amber-500"></i>
                    <span>Daftar Nilai Selesai</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">
                        {{ $statistics['completed_count'] ?? 0 }}
                    </span>
                </button>
                <button @click="activeTab = 'participation'" :class="activeTab === 'participation' ? 'bg-white text-gray-900 font-bold shadow-sm' : 'text-gray-600 hover:text-gray-900 font-medium'" class="px-4 py-2 rounded-lg text-sm transition-all flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-blue-500"></i>
                    <span>Status Siswa (Sudah / Belum)</span>
                    @if(($participationData['not_started_count'] ?? 0) > 0)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-rose-500 text-white font-bold animate-pulse">
                        {{ $participationData['not_started_count'] }} Belum
                    </span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 font-bold">
                        Lengkap
                    </span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- TAB 1: HASIL UJIAN (SELESAI) --}}
    <div x-show="activeTab === 'results'" class="space-y-8">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-5">
            <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center hover:shadow-lg hover:border-blue-200 transition-all duration-300">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ $statistics['completed_count'] ?? 0 }}</div>
                <div class="text-base text-gray-700 mt-1">Peserta Selesai</div>
            </div>
            <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center hover:shadow-lg hover:border-indigo-200 transition-all duration-300">
                <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-calculator text-indigo-600"></i>
                </div>
                <div class="text-3xl font-bold text-blue-600">{{ number_format($statistics['average_score'] ?? 0, 1) }}</div>
                <div class="text-base text-gray-700 mt-1">Rata-rata</div>
            </div>
            <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center hover:shadow-lg hover:border-emerald-200 transition-all duration-300">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-arrow-up text-emerald-600"></i>
                </div>
                <div class="text-3xl font-bold text-emerald-600">{{ number_format($statistics['highest_score'] ?? 0, 1) }}</div>
                <div class="text-base text-gray-700 mt-1">Tertinggi</div>
            </div>
            <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center hover:shadow-lg hover:border-green-200 transition-all duration-300">
                <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-trophy text-green-600"></i>
                </div>
                <div class="text-3xl font-bold text-green-600">{{ $statistics['passed_count'] ?? 0 }}</div>
                <div class="text-base text-gray-700 mt-1">Lulus (≥{{ $exam->passing_score }})</div>
            </div>
            <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center hover:shadow-lg hover:border-red-200 transition-all duration-300">
                <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <div class="text-3xl font-bold text-red-600">{{ $statistics['failed_count'] ?? 0 }}</div>
                <div class="text-base text-gray-700 mt-1">Tidak Lulus</div>
            </div>
        </div>

        {{-- Pass Rate Bar --}}
        @php $passRate = $statistics['pass_rate'] ?? 0; @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-base font-bold text-gray-700">Tingkat Kelulusan</span>
                <span class="text-base font-bold {{ $passRate >= 70 ? 'text-emerald-600' : ($passRate >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($passRate, 1) }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                <div class="h-3 rounded-full transition-all duration-1000 {{ $passRate >= 70 ? 'bg-gradient-to-r from-emerald-400 to-green-500' : ($passRate >= 50 ? 'bg-gradient-to-r from-amber-400 to-orange-500' : 'bg-gradient-to-r from-red-400 to-rose-500') }}" style="width: {{ min(100, $passRate) }}%"></div>
            </div>
        </div>

        {{-- Results Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-base">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100/80 border-b border-gray-200">
                            <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider w-16">Rank</th>
                            <th class="px-5 py-4 text-left text-base font-bold text-gray-700 uppercase tracking-wider">Siswa</th>
                            <th class="px-5 py-4 text-left text-base font-bold text-gray-700 uppercase tracking-wider">Kelas</th>
                            <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Benar</th>
                            <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Salah</th>
                            <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Skor</th>
                            <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Predikat</th>
                            <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($results as $result)
                        @php
                            $studentClassName = $result->session?->classroom?->class_name 
                                ?? $result->student?->currentClassroom?->first()?->class_name 
                                ?? '-';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors duration-150 {{ $result->final_score < $exam->passing_score ? 'bg-red-50/30' : '' }}">
                            <td class="px-5 py-4 text-center">
                                @if(($result->rank ?? 99) <= 3)
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl font-bold text-white text-base shadow-sm {{ $result->rank == 1 ? 'bg-gradient-to-br from-yellow-400 to-amber-500' : ($result->rank == 2 ? 'bg-gradient-to-br from-gray-300 to-gray-400' : 'bg-gradient-to-br from-amber-600 to-amber-700') }}">
                                    {{ $result->rank }}
                                </span>
                                @else
                                <span class="text-gray-700 font-semibold">{{ $result->rank ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-100 to-purple-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user text-violet-500 text-base"></i>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-900 block">{{ $result->student->full_name ?? '-' }}</span>
                                        <span class="text-xs text-gray-600">NISN: {{ $result->student->nisn ?? $result->student->nis ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-800 font-medium">
                                <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200">
                                    {{ $studentClassName }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center"><span class="font-bold text-emerald-600">{{ $result->correct_answers }}</span></td>
                            <td class="px-5 py-4 text-center"><span class="font-bold text-red-500">{{ $result->wrong_answers }}</span></td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-lg font-bold {{ $result->final_score >= $exam->passing_score ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ number_format($result->final_score, 1) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php $predColor = match($result->predicate) {
                                    'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'B' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'C' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    default => 'bg-red-50 text-red-700 border-red-200',
                                }; @endphp
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-base font-bold border {{ $predColor }}">
                                    {{ $result->predicate }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $studentPendingEssays = $result->session ? $result->session->answers->filter(fn($a) => $a->needsManualGrading())->count() : 0;
                                @endphp
                                @if($studentPendingEssays > 0)
                                <a href="{{ route('admin.cbt.grade-essays', ['exam' => $exam, 'classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-100 text-amber-800 border border-amber-200 hover:bg-amber-200 transition" title="Klik untuk koreksi esai">
                                    <i class="fas fa-clock text-xs"></i>Koreksi Esai ({{ $studentPendingEssays }})
                                </a>
                                @elseif($result->is_passed)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-sm font-bold rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check text-xs"></i>LULUS
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-sm font-bold rounded-lg bg-red-50 text-red-700 border border-red-200">
                                    <i class="fas fa-times text-xs"></i>TIDAK LULUS
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
                                        <i class="fas fa-chart-bar text-2xl text-emerald-300"></i>
                                    </div>
                                    <p class="text-gray-700 font-medium text-base">Belum ada hasil ujian yang terkumpul untuk kelas ini</p>
                                    <p class="text-gray-600 text-sm mt-1">Gunakan tab "Status Siswa" di atas untuk melihat siswa yang belum mengikuti ujian.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($results->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">{{ $results->links() }}</div>
            @endif
        </div>
    </div>

    {{-- TAB 2: STATUS PARTISIPASI SISWA (SUDAH VS BELUM) --}}
    <div x-show="activeTab === 'participation'" x-cloak class="space-y-6">
        {{-- Ringkasan Partisipasi Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-2 font-bold">
                    <i class="fas fa-users"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ $participationData['total_eligible'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 font-semibold uppercase mt-1">Total Siswa Terdaftar</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-2 font-bold">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="text-2xl font-bold text-emerald-600">{{ $participationData['completed_count'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 font-semibold uppercase mt-1">Sudah Mengerjakan</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-2 font-bold">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="text-2xl font-bold text-amber-600">{{ $participationData['in_progress_count'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 font-semibold uppercase mt-1">Sedang Mengerjakan</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-red-200 bg-red-50/20 p-5 text-center">
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-2 font-bold">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="text-2xl font-bold text-red-600">{{ $participationData['not_started_count'] ?? 0 }}</div>
                <div class="text-xs text-red-700 font-bold uppercase mt-1">Belum Mengerjakan</div>
            </div>
        </div>

        {{-- Filter Status Button & Export Banner --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-bold text-gray-700 mr-2">Filter Status:</span>
                <button @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-gray-900 text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium'" class="px-3.5 py-1.5 rounded-lg text-xs transition">
                    Semua ({{ $participationData['total_eligible'] ?? 0 }})
                </button>
                <button @click="statusFilter = 'completed'" :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white font-bold' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium'" class="px-3.5 py-1.5 rounded-lg text-xs transition">
                    Sudah Ujian ({{ $participationData['completed_count'] ?? 0 }})
                </button>
                <button @click="statusFilter = 'not_started'" :class="statusFilter === 'not_started' ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 font-medium'" class="px-3.5 py-1.5 rounded-lg text-xs transition">
                    Belum Ujian ({{ $participationData['not_started_count'] ?? 0 }})
                </button>
                @if(($participationData['in_progress_count'] ?? 0) > 0)
                <button @click="statusFilter = 'in_progress'" :class="statusFilter === 'in_progress' ? 'bg-amber-600 text-white font-bold' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 font-medium'" class="px-3.5 py-1.5 rounded-lg text-xs transition">
                    Sedang Aktif ({{ $participationData['in_progress_count'] ?? 0 }})
                </button>
                @endif
            </div>

            <div>
                <a href="{{ route('admin.cbt.export-participation', ['exam' => $exam, 'classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition shadow-sm">
                    <i class="fas fa-file-excel mr-2"></i>Download Rekap Excel (Siap Kirim ke Wali Kelas)
                </a>
            </div>
        </div>

        {{-- Participation Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="bg-gray-100/70 border-b border-gray-200">
                            <th class="px-4 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider w-12">#</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">NISN / NIS</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Kelas</th>
                            <th class="px-4 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Status Keikutsertaan</th>
                            <th class="px-4 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Skor / Nilai</th>
                            <th class="px-4 py-3.5 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Waktu Selesai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($participationData['students'] as $idx => $row)
                        <tr x-show="statusFilter === 'all' || statusFilter === '{{ $row['status'] }}'" class="hover:bg-gray-50/60 transition {{ $row['status'] === 'not_started' ? 'bg-rose-50/20' : '' }}">
                            <td class="px-4 py-3.5 text-center font-semibold text-gray-500">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-gray-900 block">{{ $row['student']->full_name ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 font-mono text-xs">
                                {{ $row['student']->nisn ?? $row['student']->nis ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs font-semibold">
                                    {{ $row['classroom_name'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($row['status'] === 'completed')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check-circle text-xs text-emerald-600"></i>Sudah Mengerjakan
                                </span>
                                @elseif($row['status'] === 'in_progress')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fas fa-spinner fa-spin text-xs text-amber-600"></i>Sedang Mengerjakan
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-800 border border-rose-200">
                                    <i class="fas fa-exclamation-circle text-xs text-rose-600"></i>Belum Ujian
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($row['result'])
                                <span class="font-bold {{ $row['result']->final_score >= $exam->passing_score ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ number_format($row['result']->final_score, 1) }}
                                </span>
                                <span class="text-xs text-gray-400 block">({{ $row['result']->predicate ?? '-' }})</span>
                                @else
                                <span class="text-gray-400 font-medium">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-xs text-gray-600">
                                @if(!empty($row['finished_at']))
                                {{ date('d/m/Y H:i', strtotime($row['finished_at'])) }}
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                Tidak ada data siswa yang terdaftar di kelas ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
