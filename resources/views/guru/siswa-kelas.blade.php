@extends('layouts.guru')
@section('title', 'Siswa Kelas ' . $classroom->class_name . ' - Portal Guru')

@section('content')
<div class="gd-wrap">
    {{-- Header Hero --}}
    <div class="gd-hero">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('guru.kelas') }}" class="text-xs sm:text-sm font-semibold text-emerald-300 hover:text-emerald-200 mb-2 inline-flex items-center gap-1.5">
                    <i class="fas fa-arrow-left"></i> Kembali ke Kelas Saya
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-400 text-slate-950 flex items-center justify-center shadow-md text-lg shrink-0">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <span>Siswa Kelas: {{ $classroom->class_name }}</span>
                </h1>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @can('create', App\Models\Student::class)
                <a href="{{ route('admin.students.create') }}" class="bg-emerald-400 hover:bg-emerald-300 text-slate-950 font-bold px-4 py-2 rounded-xl text-xs sm:text-sm shadow-sm transition inline-flex items-center gap-1.5">
                    <i class="fas fa-user-plus"></i> Tambah Siswa
                </a>
                @endcan
                <a href="{{ route('admin.students.index', ['classroom_id' => $classroom->id]) }}" class="bg-white/10 hover:bg-white/20 text-white font-semibold border border-white/20 px-4 py-2 rounded-xl text-xs sm:text-sm transition inline-flex items-center gap-1.5">
                    <i class="fas fa-users-gear"></i> Kelola Siswa
                </a>
                <span class="bg-white/10 text-emerald-200 border border-white/15 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold">
                    <i class="fas fa-users mr-1"></i> {{ $classroom->students->count() }} Siswa
                </span>
            </div>
        </div>
    </div>

    @if($classroom->students->count() > 0)
        <div class="gd-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-5 py-3.5 text-left font-bold uppercase text-xs tracking-wider w-12 text-emerald-300">#</th>
                            <th class="px-5 py-3.5 text-left font-bold uppercase text-xs tracking-wider text-emerald-300">NISN</th>
                            <th class="px-5 py-3.5 text-left font-bold uppercase text-xs tracking-wider text-emerald-300">Nama Lengkap</th>
                            <th class="px-5 py-3.5 text-center font-bold uppercase text-xs tracking-wider text-emerald-300">L/P</th>
                            <th class="px-5 py-3.5 text-left font-bold uppercase text-xs tracking-wider text-emerald-300">Agama</th>
                            <th class="px-5 py-3.5 text-left font-bold uppercase text-xs tracking-wider text-emerald-300">No. HP</th>
                            <th class="px-5 py-3.5 text-center font-bold uppercase text-xs tracking-wider text-emerald-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($classroom->students as $index => $student)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-slate-600">{{ $student->nisn }}</td>
                                <td class="px-5 py-3.5 font-bold text-slate-900 text-sm sm:text-base">{{ $student->full_name }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold {{ $student->gender === 'L' ? 'bg-sky-100 text-sky-800' : 'bg-pink-100 text-pink-800' }}">
                                        {{ $student->gender }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-slate-700">{{ $student->religion ?? '-' }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-600">{{ $student->phone ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @can('update', $student)
                                    <a href="{{ route('admin.students.edit', ['student' => $student->id, 'return_to' => request()->fullUrl()]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 transition" title="Edit Data Siswa">
                                        <i class="fas fa-edit text-emerald-600"></i> Edit Data
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="gd-card p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fas fa-users-slash"></i>
            </div>
            <p class="text-base font-bold text-slate-800">Belum ada siswa yang terdaftar di kelas ini</p>
        </div>
    @endif
</div>
@endsection
