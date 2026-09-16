@extends('layouts.guru')
@section('title', 'Hasil: ' . $exam->exam_title)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" onload="setTimeout(() => renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true}, {left: '\\(', right: '\\)', display: false}, {left: '$', right: '$', display: false}]}), 200)"></script>
@endpush

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'results', statusFilter: 'all' }">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white shadow-xl shadow-emerald-900/10">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('guru.cbt.exams.show', $exam) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
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
                    <p class="text-emerald-50 mt-1 text-base">{{ $exam->exam_title }} &bull; {{ $exam->subject->name ?? $exam->subject->subject_name ?? '-' }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                {{-- Export Status Siswa (Excel) --}}
                <a href="{{ route('guru.cbt.exams.export-participation', ['exam' => $exam, 'classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center px-4 py-2.5 bg-emerald-800/90 hover:bg-emerald-900 text-white rounded-xl font-semibold transition shadow-lg text-sm border border-emerald-500/30">
                    <i class="fas fa-file-excel mr-2 text-emerald-300"></i>Export Rekap Excel
                </a>

                @if($hasEssayQuestions)
                <a href="{{ route('guru.cbt.exams.grade-essays', ['exam' => $exam, 'classroom_id' => $selectedClassroomId]) }}" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-xl font-black text-sm shadow-md transition flex items-center gap-2">
                    <i class="fas fa-pen-fancy"></i>Koreksi Esai
                    @if(($pendingEssaysCount ?? 0) > 0)
                        <span class="px-2 py-0.5 bg-red-600 text-white text-xs font-black rounded-full shadow-sm">{{ $pendingEssaysCount }} Belum Dinilai</span>
                    @endif
                </a>
                @endif

                @if($exam->status === 'completed')
                <form action="{{ route('guru.cbt.exams.sync-grades', $exam) }}" method="POST">@csrf
                    <button class="px-5 py-2.5 bg-white/15 rounded-xl font-medium text-base border border-gray-200 hover:bg-white/25 transition flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i>Sinkron Nilai
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Filter Bar & Navigation Tabs --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            {{-- Dropdown Filter Per Kelas --}}
            <form method="GET" action="{{ route('guru.cbt.exams.results', $exam) }}" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold">
                        <i class="fas fa-filter"></i>
                    </div>
                    <label for="classroom_id" class="text-sm font-bold text-gray-700">Filter Kelas:</label>
                </div>
                <div class="relative min-w-[220px]">
                    <select name="classroom_id" id="classroom_id" onchange="this.form.submit()" class="w-full appearance-none rounded-xl border border-gray-300 bg-white py-2.5 pl-4 pr-10 text-sm font-semibold text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition">
                        <option value="">-- Semua Kelas yang Diampu ({{ $accessibleClassrooms->count() }}) --</option>
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
                <a href="{{ route('guru.cbt.exams.results', $exam) }}" class="inline-flex items-center px-3 py-2 text-xs font-semibold text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 rounded-xl transition">
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
                    <span>Status Siswa</span>
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
                <button @click="activeTab = 'item_analysis'" :class="activeTab === 'item_analysis' ? 'bg-white text-gray-900 font-bold shadow-sm' : 'text-gray-600 hover:text-gray-900 font-medium'" class="px-4 py-2 rounded-lg text-sm transition-all flex items-center gap-2">
                    <i class="fas fa-chart-pie text-purple-500"></i>
                    <span>Analisis Butir</span>
                </button>
            </div>
        </div>
    </div>

    {{-- TAB 1: HASIL UJIAN (SELESAI) --}}
    <div x-show="activeTab === 'results'" class="space-y-8">
        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-5">
            @php $rCards = [
                ['Peserta', $statistics['total_participants'] ?? 0, 'fa-users', 'blue'],
                ['Selesai', $statistics['completed_count'] ?? 0, 'fa-check-double', 'emerald'],
                ['Rata-rata', number_format($statistics['average_score'] ?? 0, 1), 'fa-chart-line', 'amber'],
                ['Lulus', $statistics['passed_count'] ?? 0, 'fa-trophy', 'green'],
                ['Tidak Lulus', $statistics['failed_count'] ?? 0, 'fa-times-circle', 'red'],
            ]; @endphp
            @foreach($rCards as [$label, $val, $icon, $color])
            <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center hover:shadow-lg transition-all duration-300">
                <div class="w-11 h-11 rounded-xl bg-{{ $color }}-100 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas {{ $icon }} text-{{ $color }}-600"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ $val }}</div>
                <div class="text-base text-gray-700 mt-1">{{ $label }}</div>
            </div>
            @endforeach
        </div>

        {{-- Pass Rate Bar --}}
        @php $pr = $statistics['pass_rate'] ?? 0; @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-3">
                <span class="text-base font-bold text-gray-700">Tingkat Kelulusan</span>
                <span class="text-base font-bold {{ $pr >= 75 ? 'text-emerald-600' : ($pr >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($pr, 1) }}%</span>
            </div>
            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $pr >= 75 ? 'bg-gradient-to-r from-emerald-400 to-green-500' : ($pr >= 50 ? 'bg-gradient-to-r from-amber-400 to-orange-500' : 'bg-gradient-to-r from-red-400 to-rose-500') }}" style="width: {{ $pr }}%"></div>
            </div>
        </div>

        {{-- Results Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-base">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-3.5 text-left text-base font-semibold text-gray-700 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3.5 text-left text-base font-semibold text-gray-700 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Benar</th>
                            <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Salah</th>
                            <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Kosong</th>
                            <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Predikat</th>
                            <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Durasi</th>
                            <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($results as $rank => $result)
                        @php
                            $score = $result->final_score ?? $result->total_score ?? 0;
                            $predikat = $result->predicate ?? ($score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 70 ? 'C' : ($score >= 60 ? 'D' : 'E'))));
                            $pColor = match($predikat) {
                                'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'B' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'C' => 'bg-amber-100 text-amber-800 border-amber-200',
                                default => 'bg-red-50 text-red-700 border-red-200',
                            };
                            $studentClassName = $result->session?->classroom?->class_name 
                                ?? $result->student?->currentClassroom?->first()?->class_name 
                                ?? '-';
                        @endphp
                        <tr class="hover:bg-gradient-to-r hover:from-emerald-50/30 hover:to-transparent transition-colors">
                            <td class="px-6 py-3.5 font-bold text-gray-800">{{ $rank + 1 }}</td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-base">
                                        {{ strtoupper(substr($result->student->full_name ?? ($result->session->student->full_name ?? '?'), 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900 block">{{ $result->student->full_name ?? ($result->session->student->full_name ?? '-') }}</span>
                                        <span class="text-xs text-gray-500">NISN: {{ $result->student->nisn ?? $result->student->nis ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-center text-gray-800">
                                <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 font-medium">
                                    {{ $studentClassName }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-center font-bold text-emerald-600">{{ $result->correct_answers ?? 0 }}</td>
                            <td class="px-6 py-3.5 text-center font-bold text-red-500">{{ $result->wrong_answers ?? 0 }}</td>
                            <td class="px-6 py-3.5 text-center text-gray-800">{{ $result->unanswered ?? 0 }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="text-lg font-bold {{ $score >= ($exam->passing_score ?? 70) ? 'text-emerald-600' : 'text-red-500' }}">{{ number_format($score, 1) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center"><span class="px-2.5 py-0.5 rounded-lg text-base font-bold border {{ $pColor }}">{{ $predikat }}</span></td>
                            <td class="px-6 py-3.5 text-center text-gray-700 text-base">
                                @if($result->session && $result->session->started_at && $result->session->finished_at)
                                {{ $result->session->started_at->diffInMinutes($result->session->finished_at) }} mnt
                                @elseif($result->started_at && $result->completed_at)
                                {{ $result->started_at->diffInMinutes($result->completed_at) }} mnt
                                @else - @endif
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    @if($score >= ($exam->passing_score ?? 70))
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">LULUS</span>
                                    @else
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200">TIDAK LULUS</span>
                                    @endif

                                    @if($result->session && $result->session->answers()->needsGrading()->exists())
                                        <a href="{{ route('guru.cbt.exams.grade-essays', ['exam' => $exam, 'classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-black rounded-md bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition" title="Ada esai siswa yang belum dinilai">
                                            <i class="fas fa-pen-fancy text-amber-600 text-[10px]"></i> Koreksi Esai
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-16 text-gray-800">
                                <i class="fas fa-inbox text-4xl mb-3 block text-gray-400"></i>
                                <p class="text-gray-700 font-medium">Belum ada hasil ujian yang terkumpul untuk kelas ini</p>
                                <p class="text-gray-500 text-sm mt-1">Gunakan tab "Status Siswa" di atas untuk melihat siswa yang belum mengerjakan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                <a href="{{ route('guru.cbt.exams.export-participation', ['exam' => $exam, 'classroom_id' => $selectedClassroomId]) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition shadow-sm">
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

    {{-- TAB 3: ITEM ANALYSIS --}}
    <div x-show="activeTab === 'item_analysis'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
        @forelse($itemAnalysis as $idx => $item)
        <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 hover:bg-white hover:shadow-md transition duration-300">
            <div class="flex items-start justify-between gap-4 flex-wrap border-b border-gray-200 pb-3 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-800 text-white flex items-center justify-center font-bold text-base">{{ $idx + 1 }}</div>
                    <div>
                        <span class="px-2 py-0.5 rounded text-base font-bold uppercase tracking-wide bg-blue-100 text-blue-700 border border-blue-200">
                            {{ strtoupper(str_replace('_', ' ', $item['question_type'])) }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-base">
                    <div>Tingkat Kesulitan: <span class="font-bold text-{{ ($item['difficulty'] ?? '-') === 'Mudah' ? 'emerald' : (($item['difficulty'] ?? '-') === 'Sedang' ? 'amber' : 'red') }}-600">{{ $item['difficulty'] ?? '-' }}</span> ({{ $item['difficulty_index'] ?? '-' }})</div>
                    <div>Daya Pembeda: <span class="font-bold text-gray-700">{{ $item['discrimination_index'] }}</span></div>
                </div>
            </div>

            <div class="text-base text-gray-800 mb-4 prose prose-sm max-w-none">{!! $item['question_text'] !!}</div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white p-3 rounded-xl border border-gray-200 text-center">
                <div><span class="text-base text-gray-700 block">Total Menjawab</span><span class="font-bold text-gray-900 text-base">{{ $item['total_answers'] }}</span></div>
                <div><span class="text-base text-gray-700 block">Benar</span><span class="font-bold text-emerald-600 text-base">{{ $item['correct_count'] }}</span></div>
                <div><span class="text-base text-gray-700 block">Salah</span><span class="font-bold text-red-500 text-base">{{ $item['wrong_count'] }}</span></div>
                <div><span class="text-base text-gray-700 block">% Benar</span><span class="font-bold text-blue-600 text-base">{{ $item['correct_percentage'] }}%</span></div>
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-gray-700"><i class="fas fa-chart-pie text-4xl mb-3 block text-gray-400"></i>Belum ada data untuk analisis butir soal</div>
        @endforelse
    </div>
</div>
@endsection
