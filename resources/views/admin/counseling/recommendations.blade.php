@extends('layouts.admin')

@section('title', 'Daftar Rekomendasi Siswa')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-md">
                <i class="fas fa-lightbulb text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Rekomendasi Bimbingan Siswa</h1>
                <p class="text-xs text-gray-500 mt-0.5">Tindak lanjut bimbingan bakat, karir, dan pembinaan</p>
            </div>
        </div>
        <a href="{{ route('admin.counseling.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali ke BK
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl shadow-xs text-xs font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-100">
                    <tr>
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Nama Siswa</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Judul Rekomendasi</th>
                        <th class="p-4 text-center">Prioritas</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4">Direkomendasikan Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recommendations as $idx => $rec)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 text-center font-bold text-gray-400">{{ $recommendations->firstItem() + $idx }}</td>
                        <td class="p-4 font-mono text-gray-500 whitespace-nowrap">{{ $rec->created_at->translatedFormat('d M Y') }}</td>
                        <td class="p-4 font-bold text-gray-900">{{ $rec->student->full_name ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-purple-50 text-purple-700 rounded-lg font-bold text-[11px] uppercase">
                                {{ $rec->category }}
                            </span>
                        </td>
                        <td class="p-4">
                            <p class="font-bold text-gray-900">{{ $rec->title }}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-1">{{ $rec->description }}</p>
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $pColor = match($rec->priority) {
                                    'tinggi' => 'bg-rose-100 text-rose-800',
                                    'sedang' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $pColor }}">
                                {{ $rec->priority ?? 'normal' }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-blue-50 text-blue-700">
                                {{ $rec->status }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-600 font-medium whitespace-nowrap">{{ $rec->recommendedByUser->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-400 italic">Belum ada data rekomendasi siswa yang tersimpan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $recommendations->links() }}
        </div>
    </div>
</div>
@endsection
