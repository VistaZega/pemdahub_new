@extends('layouts.guru')
@section('title', 'Siswa Kelas ' . $classroom->class_name . ' - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #312e81 100%) !important;">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('guru.kelas') }}" class="text-xs font-black text-amber-300 hover:text-amber-400 uppercase tracking-wider mb-2 inline-flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Kembali ke Kelas Saya
                </a>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-user-graduate text-black"></i>
                    </div>
                    Siswa Kelas: {{ $classroom->class_name }}
                </h1>
            </div>
            <span class="bg-amber-400 text-black border-2 border-black px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-wider shadow-sm">
                <i class="fas fa-users mr-1"></i> {{ $classroom->students->count() }} Siswa Terdaftar
            </span>
        </div>
    </div>

    @if($classroom->students->count() > 0)
        <div class="bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
                        <tr>
                            <th class="px-5 py-4 text-left font-black uppercase text-xs tracking-wider w-12 text-amber-400">#</th>
                            <th class="px-5 py-4 text-left font-black uppercase text-xs tracking-wider text-amber-400">NISN</th>
                            <th class="px-5 py-4 text-left font-black uppercase text-xs tracking-wider text-amber-400">Nama Lengkap</th>
                            <th class="px-5 py-4 text-center font-black uppercase text-xs tracking-wider text-amber-400">L/P</th>
                            <th class="px-5 py-4 text-left font-black uppercase text-xs tracking-wider text-amber-400">Agama</th>
                            <th class="px-5 py-4 text-left font-black uppercase text-xs tracking-wider text-amber-400">No. HP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black/10">
                        @foreach($classroom->students as $index => $student)
                            <tr class="hover:bg-amber-50 transition">
                                <td class="px-5 py-3.5 font-black text-black">{{ $index + 1 }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs font-black text-black">{{ $student->nisn }}</td>
                                <td class="px-5 py-3.5 font-black text-black text-base">{{ $student->full_name }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-black border-2 border-black shadow-xs {{ $student->gender === 'L' ? 'bg-sky-300 text-black' : 'bg-pink-300 text-black' }}">
                                        {{ $student->gender }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-black">{{ $student->religion ?? '-' }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs font-bold text-black">{{ $student->phone ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-3xl shadow-xl border-2 border-black p-12 text-center">
            <div class="w-16 h-16 bg-amber-300 border-2 border-black rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl shadow-md">
                <i class="fas fa-users-slash text-black"></i>
            </div>
            <p class="text-base font-black uppercase text-black">Belum ada siswa yang terdaftar di kelas ini</p>
        </div>
    @endif
</div>
@endsection
