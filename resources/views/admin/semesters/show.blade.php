@extends('layouts.admin')

@section('title', 'Detail Semester - ' . $semester->semester_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-md">
                <i class="fas fa-calendar-alt text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $semester->semester_name }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Tahun Pelajaran: {{ $semester->academicYear->name ?? '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.semesters.edit', $semester) }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition shadow-sm flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Semester
            </a>
            <a href="{{ route('admin.semesters.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="p-4 bg-gray-50 rounded-xl space-y-1">
                <p class="text-xs text-gray-400 font-medium">Tahun Pelajaran</p>
                <p class="text-sm font-bold text-gray-900">{{ $semester->academicYear->name ?? '-' }}</p>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl space-y-1">
                <p class="text-xs text-gray-400 font-medium">Status Keaktifan</p>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $semester->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                    <i class="fas fa-circle text-[8px]"></i> {{ $semester->is_active ? 'Semester Aktif' : 'Tidak Aktif' }}
                </span>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl space-y-1">
                <p class="text-xs text-gray-400 font-medium">Tanggal Mulai</p>
                <p class="text-sm font-bold text-gray-900">{{ $semester->start_date ? \Carbon\Carbon::parse($semester->start_date)->translatedFormat('d F Y') : '-' }}</p>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl space-y-1">
                <p class="text-xs text-gray-400 font-medium">Tanggal Selesai</p>
                <p class="text-sm font-bold text-gray-900">{{ $semester->end_date ? \Carbon\Carbon::parse($semester->end_date)->translatedFormat('d F Y') : '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
