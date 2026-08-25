@extends('layouts.admin')

@section('title', 'Profil Perkembangan Siswa - ' . $student->full_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl p-6 text-white relative overflow-hidden shadow-sm">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="relative">
            <div class="flex items-center text-sm text-white/70 mb-2 gap-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.counseling.index') }}" class="hover:text-white transition">BK</a>
                <span>/</span>
                <span class="text-white font-semibold">Profil Siswa</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold flex items-center gap-2.5">
                        <i class="fas fa-user-graduate"></i> {{ $student->full_name }}
                    </h1>
                    <p class="text-xs text-purple-100 mt-1 font-mono">
                        NISN: <b>{{ $student->nisn ?: '-' }}</b> &bull; Kelas: <b>{{ $student->currentClassroom->first()->name ?? '-' }}</b> &bull; Unit: <b>{{ $student->school->name ?? '-' }}</b>
                    </p>
                </div>
                <a href="{{ route('admin.counseling.index') }}" class="px-5 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-semibold transition flex items-center gap-2 text-sm shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali ke BK
                </a>
            </div>
        </div>
    </div>

    {{-- Tabs Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-comments"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Riwayat Konseling</p>
                <h3 class="text-xl font-bold text-gray-900">{{ count($counselingRecords) }} Kasus</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Catatan Perkembangan</p>
                <h3 class="text-xl font-bold text-gray-900">{{ count($developmentNotes) }} Catatan</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-lightbulb"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Rekomendasi Tindak Lanjut</p>
                <h3 class="text-xl font-bold text-gray-900">{{ count($recommendations) }} Butir</h3>
            </div>
        </div>
    </div>

    {{-- 1. Riwayat Konseling & Pembinaan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
            <i class="fas fa-history text-indigo-600"></i> Riwayat Konseling & Bimbingan ({{ count($counselingRecords) }})
        </h3>

        <div class="space-y-3">
            @forelse($counselingRecords as $rec)
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 flex items-start justify-between gap-4 text-xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-900">{{ $rec->title ?? 'Sesi Bimbingan Konseling' }}</span>
                        <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded text-[10px] font-bold">{{ $rec->category ?? 'Umum' }}</span>
                    </div>
                    <p class="text-gray-600">{{ $rec->description }}</p>
                    <p class="text-[10px] text-gray-400">Pembimbing: <b>{{ $rec->counselor->name ?? '-' }}</b> &bull; Tanggal: <b>{{ $rec->incident_date ? \Carbon\Carbon::parse($rec->incident_date)->translatedFormat('d M Y') : '-' }}</b></p>
                </div>
                <a href="{{ route('admin.counseling.show', $rec) }}" class="px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-700 rounded-lg font-bold border border-gray-200 shadow-2xs whitespace-nowrap">
                    Lihat Detail
                </a>
            </div>
            @empty
            <p class="text-xs text-gray-400 italic p-4 text-center">Belum ada riwayat konseling.</p>
            @endforelse
        </div>
    </div>

    {{-- 2. Catatan Perkembangan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
            <i class="fas fa-clipboard-check text-purple-600"></i> Catatan Perkembangan & Observasi ({{ count($developmentNotes) }})
        </h3>

        <div class="space-y-3">
            @forelse($developmentNotes as $note)
            <div class="p-4 bg-purple-50/50 rounded-xl border border-purple-100 text-xs space-y-1">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 bg-purple-200 text-purple-800 rounded text-[10px] font-bold uppercase">{{ $note->aspect }}</span>
                    <span class="text-[10px] text-gray-400 font-mono">{{ $note->created_at->translatedFormat('d M Y') }}</span>
                </div>
                <p class="text-gray-800 font-medium mt-1">{{ $note->observation }}</p>
                @if($note->progress)
                <p class="text-emerald-700 text-[11px]"><b>Kemajuan:</b> {{ $note->progress }}</p>
                @endif
                @if($note->suggestion)
                <p class="text-gray-500 text-[11px]"><b>Saran:</b> {{ $note->suggestion }}</p>
                @endif
            </div>
            @empty
            <p class="text-xs text-gray-400 italic p-4 text-center">Belum ada catatan perkembangan.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
