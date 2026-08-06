@extends('layouts.guru')

@section('title', 'Hasil Quiz - ' . $quiz->title)

@push('styles')
<style>
    .animate-fade-in { animation: fadeIn 0.4s ease both; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stat-card-gradient-1 { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); }
    .stat-card-gradient-2 { background: linear-gradient(135deg, #10b981 0%, #047857 100%); }
    .stat-card-gradient-3 { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
</style>
@endpush

@section('content')
@php
    $ranges = [
        '81-100' => 0,
        '61-80'  => 0,
        '41-60'  => 0,
        '21-40'  => 0,
        '0-20'   => 0,
    ];
    foreach($quiz->attempts as $attempt) {
        if ($attempt->score !== null) {
            $score = $attempt->score;
            if ($score > 80) $ranges['81-100']++;
            elseif ($score > 60) $ranges['61-80']++;
            elseif ($score > 40) $ranges['41-60']++;
            elseif ($score > 20) $ranges['21-40']++;
            else $ranges['0-20']++;
        }
    }
    $maxRangeCount = max(max($ranges), 1);
@endphp

<div class="space-y-6 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.lms.quizzes.show', $quiz->id) }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-emerald-600 transition-colors shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-0.5">
                <span>{{ $course->name }}</span>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span>Quiz</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Hasil Quiz: {{ $quiz->title }}</h2>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="stat-card-gradient-1 rounded-xl p-6 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <i class="fas fa-user-friends text-7xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-indigo-100">Total Pengerjaan</p>
            <p class="text-3xl font-extrabold mt-1">{{ $totalAttempts }}</p>
            <p class="text-[10px] text-indigo-100 mt-2 font-medium">Jumlah percobaan pengerjaan oleh seluruh siswa.</p>
        </div>

        <div class="stat-card-gradient-2 rounded-xl p-6 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <i class="fas fa-check-circle text-7xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-emerald-100">Siswa Lulus</p>
            <p class="text-3xl font-extrabold mt-1">{{ $passedCount }} <span class="text-sm font-normal text-emerald-100">/ {{ $totalAttempts }}</span></p>
            <p class="text-[10px] text-emerald-100 mt-2 font-medium">Batas kelulusan quiz ini adalah {{ $quiz->passing_score }}%.</p>
        </div>

        <div class="stat-card-gradient-3 rounded-xl p-6 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <i class="fas fa-star text-7xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-purple-100">Rata-rata Skor</p>
            <p class="text-3xl font-extrabold mt-1">{{ $avgScore ? number_format($avgScore, 1) . '%' : '-' }}</p>
            <p class="text-[10px] text-purple-100 mt-2 font-medium">Skor rata-rata dari seluruh pengerjaan yang selesai.</p>
        </div>
    </div>

    {{-- Chart & Table Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Distribution Chart --}}
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-sm mb-4 tracking-wide flex items-center gap-2">
                    <i class="fas fa-chart-bar text-purple-600"></i> Distribusi Nilai Siswa
                </h3>
                <div class="space-y-4">
                    @foreach($ranges as $range => $count)
                    @php
                        $percentage = ($count / $maxRangeCount) * 100;
                        $color = $range === '81-100' ? 'bg-emerald-500' : ($range === '61-80' ? 'bg-blue-500' : ($range === '41-60' ? 'bg-amber-500' : ($range === '21-40' ? 'bg-orange-500' : 'bg-rose-500')));
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-medium">
                            <span class="text-gray-500">Skor {{ $range }}</span>
                            <span class="text-gray-700 font-bold">{{ $count }} siswa</span>
                        </div>
                        <div class="w-full bg-gray-50 h-2.5 rounded-full overflow-hidden border border-gray-100">
                            <div class="h-full {{ $color }} rounded-full transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="pt-6 border-t border-gray-50 mt-4 text-[10px] text-gray-400 font-medium leading-relaxed">
                * Distribusi di atas menggambarkan pengelompokan skor berdasarkan rentang nilai kelulusan.
            </div>
        </div>

        {{-- Table --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <i class="fas fa-users text-indigo-500"></i>
                    Daftar Pengerjaan Siswa
                </h4>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-5 py-3 text-xs font-bold text-gray-400 tracking-wide pl-6">Siswa</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-400 tracking-wide">Tanggal Selesai</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-400 tracking-wide text-center">Durasi</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-400 tracking-wide text-center">Skor</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-400 tracking-wide text-center">Status</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-400 tracking-wide text-center pr-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($quiz->attempts as $attempt)
                        @php
                            $studentName = $attempt->student->full_name ?? $attempt->student->user->name ?? 'N/A';
                            $initials = strtoupper(substr($studentName, 0, 1));
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-5 py-3.5 pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-[11px] font-bold text-white shadow-sm flex-shrink-0 group-hover:scale-105 transition-transform">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-800">{{ $studentName }}</p>
                                        <p class="text-[9px] text-gray-400 font-semibold">{{ $attempt->student->nisn ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600">
                                @if($attempt->finished_at)
                                    {{ $attempt->finished_at->format('d M Y H:i') }}
                                @else
                                    <span class="text-gray-400 italic">Belum selesai</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600 text-center font-medium">
                                {{ $attempt->duration ? $attempt->duration . ' mnt' : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center font-bold">
                                @if($attempt->score !== null)
                                    <span class="text-sm {{ $attempt->is_passed ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ number_format($attempt->score, 1) }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($attempt->is_passed === true)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    <span class="w-1 h-1 bg-emerald-500 rounded-full"></span> Lulus
                                </span>
                                @elseif($attempt->is_passed === false)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                    <span class="w-1 h-1 bg-rose-500 rounded-full"></span> Gagal
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-150 text-gray-600 border border-gray-200">
                                    <span class="w-1 h-1 bg-gray-500 rounded-full"></span> Belum Selesai
                                </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center pr-6">
                                <a href="{{ route('guru.lms.quizzes.attempts.show', $attempt->id) }}" class="inline-flex items-center bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-2.5 py-1 rounded-lg text-xs font-bold transition">
                                    <i class="fas fa-edit mr-1"></i> Detail / Nilai
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400 italic">Belum ada pengerjaan quiz untuk saat ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
