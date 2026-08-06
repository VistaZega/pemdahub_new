@extends('layouts.guru')

@section('title', 'Analitik Pembelajaran - ' . $course->course_name)

@section('content')
<div class="space-y-6">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HEADER BANNER --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-900 rounded-xl p-6 md:p-8 text-white shadow-xl border border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="{{ route('guru.lms.show', $course->id) }}" class="inline-flex items-center gap-2 text-xs font-bold text-cyan-400 hover:text-cyan-300 mb-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Course
                </a>
                <h1 class="text-2xl font-semibold text-white leading-tight">Analitik Pembelajaran Siswa</h1>
                <p class="text-xs text-slate-300 mt-1">Course: <strong class="text-white">{{ $course->course_name ?? $course->name }}</strong> | Mapel: {{ $course->subject->subject_name ?? '-' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-slate-800/80 border border-slate-700 rounded-xl px-5 py-3 text-center shadow-inner">
                    <span class="text-2xl font-semibold text-cyan-400 block">{{ $avgCourseProgress }}%</span>
                    <span class="text-[9px] font-bold tracking-wide text-slate-400">Rata-rata Kelas</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- STAT CARDS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-500 tracking-wide block">Total Siswa</span>
                <span class="text-2xl font-semibold text-gray-900">{{ count($studentStats) }} Siswa</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-book-open"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-500 tracking-wide block">Materi Terbit</span>
                <span class="text-2xl font-semibold text-gray-900">{{ $course->materials->count() }} Materi</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-tasks"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-500 tracking-wide block">Tugas Terbit</span>
                <span class="text-2xl font-semibold text-gray-900">{{ $course->assignments->count() }} Tugas</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border-2 border-red-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-500 text-white flex items-center justify-center font-bold text-xl shadow-sm">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-red-600 tracking-wide block">Siswa Perlu Perhatian</span>
                <span class="text-2xl font-semibold text-red-600">{{ count($atRiskStudents) }} Siswa</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- AT-RISK STUDENTS BANNER --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if(count($atRiskStudents) > 0)
    <div class="bg-red-50 rounded-xl p-6 border-2 border-red-200 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center font-bold">
                <i class="fas fa-user-clock text-lg"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-red-900">Daftar Siswa Perlu Perhatian Khusus</h3>
                <p class="text-xs text-red-700 font-medium">Siswa dengan persentase membaca materi < 40% atau belum mengumpulkan tugas</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($atRiskStudents as $atRisk)
            <div class="bg-white p-4 rounded-xl border border-red-200 flex items-center justify-between shadow-xs">
                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-gray-900 text-sm truncate">{{ $atRisk['student']->user->name ?? '-' }}</h4>
                    <p class="text-xs text-gray-500">Progres: <strong class="text-red-600">{{ $atRisk['progress'] }}%</strong> | Tugas: {{ $atRisk['submissions_count'] }} dikumpul</p>
                </div>
                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-[10px] font-bold rounded-lg tracking-wide">Perlu Dorongan</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- ALL STUDENTS PROGRESS TABLE --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                <i class="fas fa-list-check text-indigo-600"></i> Detail Progres & Keterlibatan Siswa
            </h3>
            <span class="text-xs font-bold text-gray-500">{{ count($studentStats) }} Siswa Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-200 text-xs font-extrabold text-gray-600 tracking-wide">
                        <th class="text-left px-6 py-3.5">Siswa</th>
                        <th class="text-center px-4 py-3.5">Materi Selesai</th>
                        <th class="text-center px-4 py-3.5">Tugas Dikumpul</th>
                        <th class="text-left px-6 py-3.5">Progres Total</th>
                        <th class="text-center px-4 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($studentStats as $st)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $st['student']->user->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">NISN: {{ $st['student']->nisn ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4 text-center font-bold text-gray-800">
                            {{ $st['completed_materials'] }} / {{ $course->materials->count() }}
                        </td>
                        <td class="px-4 py-4 text-center font-bold text-gray-800">
                            {{ $st['submissions_count'] }} / {{ $course->assignments->count() }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $st['progress'] >= 75 ? 'bg-emerald-500' : ($st['progress'] >= 40 ? 'bg-blue-500' : 'bg-red-500') }}" style="width: {{ $st['progress'] }}%"></div>
                                </div>
                                <span class="text-xs font-bold text-gray-700 min-w-[36px] text-right">{{ $st['progress'] }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($st['progress'] >= 75)
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-bold">Sangat Aktif 🌟</span>
                            @elseif($st['progress'] >= 40)
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg text-xs font-bold">Aktif 📖</span>
                            @else
                            <span class="px-2.5 py-1 bg-red-100 text-red-800 rounded-lg text-xs font-bold">Perlu Dorongan ⚠️</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada siswa terdaftar di course ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
