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
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 text-xs font-black px-3.5 py-2 rounded-2xl border-2 border-black shadow-sm uppercase tracking-wider" style="background-color: #34d399 !important; color: #000000 !important;">
                    <i class="fas fa-clock text-black"></i> {{ $pklHours ?? 0 }} JP Penugasan
                </span>
                <span class="inline-flex items-center gap-2 text-xs font-black px-3.5 py-2 rounded-2xl border-2 border-black shadow-sm uppercase tracking-wider" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="far fa-user text-black"></i> Pembimbing Lapangan
                </span>
            </div>
        </div>
    </div>

    {{-- Stats Cards Grid (Neo-Brutalism) --}}
    @php
        $totalDudis = $placements->pluck('company_name')->unique()->filter()->count();
        $allLogs = $placements->flatMap->logs;
        $totalApprovedLogs = $allLogs->where('status', 'approved')->count();
        $totalPendingLogs = $allLogs->where('status', 'submitted')->count();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Siswa Bimbingan --}}
        <div class="bg-white rounded-3xl border-2 border-black p-4 md:p-5 shadow-md flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-lg md:text-xl font-black" style="background-color: #0284c7 !important; color: #ffffff !important;">
                    <i class="fas fa-user-graduate text-white"></i>
                </div>
                <span class="text-2xl md:text-3xl font-black text-black leading-none">{{ $placements->count() }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Siswa Bimbingan</p>
                <p class="text-[10px] md:text-[11px] font-bold text-slate-600 mt-0.5">Siswa PKL aktif diampu</p>
            </div>
        </div>

        {{-- Card 2: JP Penugasan PKL (HIGHLIGHT) --}}
        <div class="bg-white rounded-3xl border-2 border-black p-4 md:p-5 shadow-md flex flex-col justify-between relative overflow-hidden" style="background: linear-gradient(135deg, #ffffff 0%, #fef3c7 100%);">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-lg md:text-xl font-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="fas fa-clock text-black"></i>
                </div>
                <div class="text-right">
                    <span class="text-2xl md:text-3xl font-black text-black leading-none">{{ $pklHours ?? 0 }}</span>
                    <span class="text-xs font-black text-slate-800 ml-0.5">JP</span>
                </div>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Beban Jam (JP) PKL</p>
                @if(($pklHours ?? 0) > 0)
                    <p class="text-[10px] md:text-[11px] font-black text-emerald-700 mt-0.5 flex items-center gap-1">
                        <i class="fas fa-check-circle text-emerald-600 text-[10px]"></i> Ditetapkan di SK Penugasan
                    </p>
                @else
                    <p class="text-[10px] md:text-[11px] font-bold text-amber-700 mt-0.5 flex items-center gap-1" title="Hubungi Admin untuk plot JP di menu Penugasan Jabatan">
                        <i class="fas fa-info-circle text-amber-600 text-[10px]"></i> Belum diplot di Penugasan
                    </p>
                @endif
            </div>
        </div>

        {{-- Card 3: Mitra Industri (DUDI) --}}
        <div class="bg-white rounded-3xl border-2 border-black p-4 md:p-5 shadow-md flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-lg md:text-xl font-black" style="background-color: #059669 !important; color: #ffffff !important;">
                    <i class="fas fa-building text-white"></i>
                </div>
                <span class="text-2xl md:text-3xl font-black text-black leading-none">{{ $totalDudis }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Mitra Industri (DUDI)</p>
                <p class="text-[10px] md:text-[11px] font-bold text-slate-600 mt-0.5">Lokasi tempat kerja siswa</p>
            </div>
        </div>

        {{-- Card 4: Logbook Terverifikasi --}}
        <div class="bg-white rounded-3xl border-2 border-black p-4 md:p-5 shadow-md flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-lg md:text-xl font-black" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    <i class="fas fa-clipboard-check text-white"></i>
                </div>
                <div class="text-right">
                    <span class="text-2xl md:text-3xl font-black text-black leading-none">{{ $totalApprovedLogs }}</span>
                    @if($totalPendingLogs > 0)
                        <span class="text-[10px] font-black text-rose-600 ml-1">({{ $totalPendingLogs }} ⏳)</span>
                    @endif
                </div>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Logbook Disetujui</p>
                <p class="text-[10px] md:text-[11px] font-bold text-slate-600 mt-0.5">{{ $totalPendingLogs > 0 ? $totalPendingLogs . ' butuh persetujuan' : 'Semua logbook terverifikasi' }}</p>
            </div>
        </div>
    </div>

    {{-- Placements List Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                    <i class="fas fa-list text-emerald-500"></i> Daftar Siswa Bimbingan PKL
                </h2>
                <span class="px-2.5 py-0.5 rounded-lg text-xs font-black border border-emerald-300 bg-emerald-50 text-emerald-800">
                    {{ $pklHours ?? 0 }} JP Penugasan
                </span>
            </div>
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
