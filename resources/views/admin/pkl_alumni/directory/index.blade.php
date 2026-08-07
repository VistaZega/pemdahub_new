@extends('layouts.admin')
@section('title', 'Direktori Ikatan Alumni (IKA)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Direktori Ikatan Alumni (IKA)</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data direktori alumni publik dan sinkronisasi lulusan sekolah</p>
        </div>
        <div>
            <a href="{{ route('admin.alumni-directory.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium text-sm shadow-sm">
                <i class="fas fa-plus"></i> Tambah Data Alumni
            </a>
        </div>
    </div>

    <!-- Navigation & Quick Links -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
        <a href="{{ route('admin.alumni.index') }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 font-medium rounded-xl text-sm transition">
            <i class="fas fa-list mr-1"></i> Data Alumni Sistem
        </a>
        <a href="{{ route('admin.alumni-directory.index') }}" class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-xl text-sm shadow-sm">
            <i class="fas fa-address-book mr-1"></i> Direktori Alumni (IKA)
        </a>
        <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 font-medium rounded-xl text-sm transition">
            <i class="fas fa-chart-line mr-1"></i> Tracer Study (BMW)
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4">Nama & Kontak</th>
                        <th class="px-6 py-4">Akademik</th>
                        <th class="px-6 py-4">Pekerjaan</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($directories as $dir)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $dir->photo_url }}" class="w-10 h-10 rounded-full object-cover border border-gray-200" alt="{{ $dir->full_name }}">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $dir->full_name }} {{ $dir->alias_name ? "({$dir->alias_name})" : '' }}</p>
                                    <p class="text-xs text-gray-500">{{ $dir->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} • {{ $dir->phone ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900 font-medium">{{ $dir->school->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">Lulus: {{ $dir->graduation_year }}</p>
                            @if($dir->jurusan)
                            <p class="text-xs text-indigo-500 font-medium mt-0.5">{{ $dir->jurusan }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900 font-medium">{{ $dir->occupation ?? '-' }}</p>
                            @if($dir->company_name)
                            <p class="text-xs text-gray-500">{{ $dir->company_name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($dir->is_approved)
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Disetujui
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Menunggu
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('admin.alumni-directory.show', $dir) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.alumni-directory.edit', $dir) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.alumni-directory.toggle-approval', $dir) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $dir->is_approved ? 'membatalkan persetujuan' : 'menyetujui' }} data alumni ini untuk tampil di publik?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $dir->is_approved ? 'bg-orange-50 text-orange-600 hover:bg-orange-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }} transition" title="{{ $dir->is_approved ? 'Batalkan Persetujuan' : 'Setujui' }}">
                                    <i class="fas {{ $dir->is_approved ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.alumni-directory.destroy', $dir) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data alumni ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users-slash text-4xl mb-3 text-gray-300"></i>
                            <p>Belum ada data pendaftaran ikatan alumni.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($directories->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $directories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
