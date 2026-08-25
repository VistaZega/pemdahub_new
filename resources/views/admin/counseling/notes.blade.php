@extends('layouts.admin')

@section('title', 'Catatan Perkembangan Siswa')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-md">
                <i class="fas fa-clipboard-list text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Catatan Perkembangan Siswa</h1>
                <p class="text-xs text-gray-500 mt-0.5">Observasi aspek akademik, sikap, dan perkembangan karakter</p>
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
                        <th class="p-4">Aspek</th>
                        <th class="p-4">Hasil Observasi</th>
                        <th class="p-4">Kemajuan & Saran</th>
                        <th class="p-4">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($notes as $idx => $note)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 text-center font-bold text-gray-400">{{ $notes->firstItem() + $idx }}</td>
                        <td class="p-4 font-mono text-gray-500 whitespace-nowrap">{{ $note->created_at->translatedFormat('d M Y') }}</td>
                        <td class="p-4 font-bold text-gray-900">{{ $note->student->full_name ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg font-bold text-[11px] uppercase">
                                {{ $note->aspect }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-700 max-w-xs">{{ $note->observation }}</td>
                        <td class="p-4 text-gray-600 max-w-xs">
                            <p class="font-medium text-emerald-700">{{ $note->progress }}</p>
                            <p class="text-[11px] text-gray-500 italic mt-0.5">{{ $note->suggestion }}</p>
                        </td>
                        <td class="p-4 text-gray-600 font-medium whitespace-nowrap">{{ $note->notedByUser->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400 italic">Belum ada catatan perkembangan siswa yang tersimpan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $notes->links() }}
        </div>
    </div>
</div>
@endsection
