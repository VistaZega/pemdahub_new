@extends('layouts.guru')

@section('title', 'Pengajuan Cuti Saya - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #047857 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-calendar-times text-black"></i>
                    </div>
                    Pengajuan Cuti Saya
                </h1>
                <p class="text-xs md:text-sm font-bold text-emerald-200 mt-1" style="color: #a7f3d0 !important;">Kelola dan pantau status pengajuan cuti atau izin kepegawaian Anda</p>
            </div>
            <a href="{{ route('guru.leaves.create') }}" class="bg-amber-400 hover:bg-amber-300 text-black border-2 border-black px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-md flex items-center gap-2">
                <i class="fas fa-plus text-black"></i> Ajukan Cuti Baru
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-xl flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="text-green-700 font-medium text-sm">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">No</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Cuti</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Durasi</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Alasan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Lampiran</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leaves as $index => $leave)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 text-center font-medium">
                                {{ $leaves->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-800 text-sm">
                                    {{ \App\Models\EmployeeLeave::LEAVE_TYPES[$leave->leave_type] ?? $leave->leave_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ $leave->start_date->translatedFormat('d M Y') }}</div>
                                <div class="text-xs text-gray-400">s.d. {{ $leave->end_date->translatedFormat('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 text-center font-bold">
                                {{ $leave->days_count }} Hari
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $leave->reason }}">
                                {{ $leave->reason }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($leave->attachment)
                                    <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors" title="Lihat Lampiran">
                                        <i class="fas fa-file-pdf text-sm"></i>
                                    </a>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusConfig = [
                                        'pending' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'label' => 'Menunggu Persetujuan'],
                                        'approved_kepsek' => ['bg' => 'bg-blue-100 text-blue-800 border-blue-200', 'label' => 'Disetujui Kepsek'],
                                        'approved' => ['bg' => 'bg-green-100 text-green-800 border-green-200', 'label' => 'Disetujui'],
                                        'rejected' => ['bg' => 'bg-rose-100 text-rose-800 border-rose-200', 'label' => 'Ditolak'],
                                    ];
                                    $st = $statusConfig[$leave->status] ?? ['bg' => 'bg-gray-100 text-gray-800', 'label' => $leave->status];
                                @endphp
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold border {{ $st['bg'] }}">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                <div class="text-3xl mb-2">📁</div>
                                Belum ada pengajuan cuti yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leaves->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
