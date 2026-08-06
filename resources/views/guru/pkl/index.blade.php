@extends('layouts.guru')
@section('title', 'Monitoring PKL - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Bar --}}
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #047857 100%) !important;">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-briefcase text-black"></i>
                    </div>
                    Monitoring Praktik Kerja Lapangan (PKL)
                </h1>
                <p class="text-xs md:text-sm font-bold text-emerald-200 mt-1" style="color: #a7f3d0 !important;">
                    Pemantauan Harian Logbook & Nilai Evaluasi Industri Siswa Bimbingan
                </p>
            </div>
            <div>
                <span class="inline-flex items-center gap-2 text-xs font-black px-4 py-2 rounded-2xl border-2 border-black shadow-sm uppercase tracking-wider" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="far fa-user text-black"></i> Pembimbing Lapangan
                </span>
            </div>
        </div>
    </div>

    {{-- Placements List Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                <i class="fas fa-list text-emerald-500"></i> Daftar Siswa Bimbingan PKL
            </h2>
            <span class="text-xs bg-gray-150 px-2.5 py-1 rounded-lg text-gray-600 font-bold">
                {{ $placements->count() }} Siswa
            </span>
        </div>
        <div class="p-5">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider text-left">
                            <th class="pb-3 pl-4">Siswa</th>
                            <th class="pb-3">Industri (DUDI) & Mentor</th>
                            <th class="pb-3 text-center">Logbook Harian</th>
                            <th class="pb-3 text-center">Nilai Industri</th>
                            <th class="pb-3 text-center pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($placements as $placement)
                            @php
                                $totalLogs = $placement->logs->count();
                                $approvedLogs = $placement->logs->where('status', 'approved')->count();
                                $pendingLogs = $placement->logs->where('status', 'submitted')->count();
                                $rejectedLogs = $placement->logs->where('status', 'rejected')->count();
                            @endphp
                            <tr class="group hover:bg-gray-50/55 transition-all text-xs text-gray-700">
                                <td class="py-4 pl-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm flex-shrink-0">
                                            <img src="{{ $placement->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $placement->student->full_name }}">
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 text-sm leading-tight">{{ $placement->student->full_name }}</p>
                                            <p class="text-[10px] text-gray-400 mt-0.5">NISN: {{ $placement->student->nisn }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <p class="font-bold text-gray-800 leading-snug">{{ $placement->company_name }}</p>
                                    <p class="text-[10px] text-gray-500 flex items-center gap-1 mt-0.5">
                                        <i class="fas fa-user-tie text-[9px] text-gray-400"></i>
                                        {{ $placement->mentor_name }} ({{ $placement->mentor_phone ?? '-' }})
                                    </p>
                                </td>
                                <td class="py-4 text-center">
                                    <div class="inline-flex items-center gap-1 bg-gray-50 border border-gray-100 rounded-lg p-1">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700" title="Disetujui">
                                            {{ $approvedLogs }} ✔
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700" title="Menunggu Persetujuan">
                                            {{ $pendingLogs }} ⏳
                                        </span>
                                        @if($rejectedLogs > 0)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700" title="Revisi">
                                                {{ $rejectedLogs }} ❌
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 text-center">
                                    @if($placement->grade)
                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-lg font-bold">
                                            <i class="fas fa-star text-amber-400 text-[9px]"></i>
                                            {{ number_format($placement->grade->score_average, 1) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-400 border border-gray-200 px-2 py-0.5 rounded-lg font-medium text-[10px] italic">
                                            Belum Dinilai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 text-center pr-4">
                                    <a href="{{ route('guru.pkl.show', $placement->id) }}" class="inline-flex items-center gap-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-3 py-1.5 rounded-xl shadow transition">
                                        <i class="fas fa-eye text-[10px]"></i> Pantau
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-400 italic">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-briefcase text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500">Tidak ada siswa bimbingan PKL yang ditugaskan kepada Anda.</p>
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
