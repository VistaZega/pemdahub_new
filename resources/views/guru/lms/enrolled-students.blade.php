@extends('layouts.guru')

@section('title', 'Siswa Terdaftar - ' . $course->name)

@push('styles')
<style>
    .fade-in { animation: fadeIn 0.4s ease both; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stat-card-glow {
        transition: all 0.3s ease;
    }
    .stat-card-glow:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.08);
    }
</style>
@endpush

@section('content')
@php
    $totalCount = $enrollments->count();
    $activeCount = $enrollments->whereIn('status', ['enrolled', 'in_progress'])->count();
    $completedCount = $enrollments->where('status', 'completed')->count();
    $droppedCount = $enrollments->where('status', 'dropped')->count();
@endphp

<div class="space-y-6" x-data="{ searchQuery: '' }">
    {{-- Header --}}
    <div class="rounded-3xl p-6 md:p-8 shadow-md border-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-black text-amber-400 mb-2 uppercase tracking-wider">
                    <a href="{{ route('guru.lms.index') }}" class="hover:underline text-amber-400">LMS Guru</a>
                    <i class="fas fa-chevron-right text-[10px] opacity-70"></i>
                    <a href="{{ route('guru.lms.show', $course->id) }}" class="hover:underline text-amber-400">{{ $course->name }}</a>
                    <i class="fas fa-chevron-right text-[10px] opacity-70"></i>
                    <span class="text-white">Data Kelas & Siswa</span>
                </div>
                <h1 class="text-2xl font-black text-white tracking-wide">Data Kelas & Siswa Terdaftar</h1>
                <p class="text-amber-300 font-bold text-xs mt-1">Mengelola siswa terdaftar yang mengikuti course {{ $course->name }}.</p>
            </div>
            <div>
                <a href="{{ route('guru.lms.show', $course->id) }}" class="inline-flex items-center gap-2 bg-white text-black border-2 border-black px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider hover:bg-amber-300 transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali ke Course
                </a>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="bg-emerald-200 border-2 border-black text-black px-5 py-3.5 rounded-2xl text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-sm">
        <i class="fas fa-check-circle text-black text-base"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-rose-200 border-2 border-black text-black px-5 py-3.5 rounded-2xl text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-sm">
        <i class="fas fa-exclamation-circle text-black text-base"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Forms Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Enroll Form --}}
        @if($availableClassrooms->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-sm mb-1 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-user-plus text-emerald-500"></i> Daftarkan Kelas
                </h3>
                <p class="text-xs text-gray-400 mb-4">Tambahkan semua siswa dari kelas tertentu sekaligus.</p>
                <form action="{{ route('guru.lms.students.enroll', $course->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <select name="classroom_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($availableClassrooms as $classroom)
                            <option value="{{ $classroom->id }}">{{ $classroom->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm hover:shadow flex items-center justify-center gap-2">
                        <i class="fas fa-plus-circle"></i> Daftarkan Siswa
                    </button>
                </form>
                @error('classroom_id')
                <p class="text-rose-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center justify-center text-center">
            <div>
                <div class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center mx-auto mb-2"><i class="fas fa-check-double"></i></div>
                <p class="text-xs font-bold text-gray-700">Semua Kelas Terdaftar</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Tidak ada kelas lain yang tersedia untuk didaftarkan.</p>
            </div>
        </div>
        @endif

        {{-- Connected Classrooms --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <h3 class="font-bold text-gray-800 text-sm mb-1 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-link text-blue-500"></i> Kelas Terhubung
            </h3>
            <p class="text-xs text-gray-400 mb-4">Daftar kelas yang saat ini terhubung dengan course ini.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($course->lmsClasses as $lmsClass)
                <div class="flex items-center justify-between p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr($lmsClass->classroom->class_name ?? 'K', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-blue-800 text-xs leading-none">{{ $lmsClass->classroom->class_name ?? 'N/A' }}</p>
                            <p class="text-[9px] text-blue-400 font-semibold mt-1">{{ $lmsClass->getEnrolledCount() }} Siswa Terdaftar</p>
                        </div>
                    </div>
                    <form action="{{ route('guru.lms.students.enroll', $course->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="classroom_id" value="{{ $lmsClass->classroom_id }}">
                        <button type="submit" class="bg-white border border-blue-200 text-blue-600 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                            <i class="fas fa-sync-alt mr-1"></i> Sync
                        </button>
                    </form>
                </div>
                @empty
                <div class="sm:col-span-2 py-6 text-center text-xs text-gray-400 italic">
                    Belum ada kelas yang terhubung.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Search and Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Search Bar --}}
        <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fas fa-user-graduate text-indigo-500"></i>
                Daftar Siswa
            </h4>
            <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 text-xs">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" x-model="searchQuery" placeholder="Cari siswa atau NISN..." class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
        </div>

        @if($enrollments->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider pl-6 w-12">#</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal Daftar</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider text-center pr-6 w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $index = 1; @endphp
                    @foreach($enrollments as $enrollment)
                    @php
                        $studentName = $enrollment->student->full_name ?? $enrollment->student->user->name ?? 'Siswa';
                        $nisn = $enrollment->student->nisn ?? '';
                    @endphp
                    <tr x-show="searchQuery === '' || '{{ strtolower($studentName) }}'.includes(searchQuery.toLowerCase()) || '{{ $nisn }}'.includes(searchQuery)"
                        class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-6 py-3.5 text-gray-400 pl-6">{{ $index++ }}</td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-[11px] font-bold text-white shadow-sm flex-shrink-0 group-hover:scale-105 transition-transform">
                                    {{ strtoupper(substr($studentName, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">{{ $studentName }}</p>
                                    <p class="text-[9px] text-gray-400 font-semibold">{{ $enrollment->student->nisn ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-xs text-gray-600 font-semibold">
                            {{ $enrollment->lmsClass->classroom->class_name ?? '-' }}
                        </td>
                        <td class="px-6 py-3.5">
                            @if($enrollment->status === 'enrolled')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-100 uppercase">
                                Terdaftar
                            </span>
                            @elseif($enrollment->status === 'in_progress')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-100 uppercase">
                                Belajar
                            </span>
                            @elseif($enrollment->status === 'completed')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase">
                                Selesai
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-bold bg-gray-50 text-gray-600 border border-gray-150 uppercase">
                                {{ $enrollment->status }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-xs text-gray-500">
                            {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('d M Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-3.5 text-center pr-6">
                            <form action="{{ route('guru.lms.students.unenroll', [$course->id, $enrollment->student_id]) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin mengeluarkan siswa {{ $studentName }} dari course ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-colors border border-gray-150" title="Keluarkan Siswa">
                                    <i class="fas fa-user-minus text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-16 text-center">
            <div class="w-20 h-20 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users-slash text-3xl text-slate-350"></i>
            </div>
            <h3 class="text-base font-bold text-gray-700 mb-1">Belum Ada Siswa</h3>
            <p class="text-xs text-gray-400 max-w-xs mx-auto leading-relaxed">Belum ada siswa terdaftar pada course ini. Gunakan panel di atas untuk mendaftarkan kelas.</p>
        </div>
        @endif
    </div>

    {{-- Stats Row --}}
    @if($totalCount > 0)
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center stat-card-glow shadow-sm">
            <p class="text-2xl font-extrabold text-blue-600">{{ $totalCount }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Total Terdaftar</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center stat-card-glow shadow-sm">
            <p class="text-2xl font-extrabold text-amber-600">{{ $activeCount }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Aktif Belajar</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center stat-card-glow shadow-sm">
            <p class="text-2xl font-extrabold text-emerald-600">{{ $completedCount }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Selesai Kelas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center stat-card-glow shadow-sm">
            <p class="text-2xl font-extrabold text-rose-600">{{ $droppedCount }}</p>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Dropped Out</p>
        </div>
    </div>
    @endif
</div>
@endsection
