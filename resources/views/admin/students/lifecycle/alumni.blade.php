@extends('layouts.admin')

@section('title', 'Data Alumni')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg">
                <i class="fas fa-graduation-cap text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Data Alumni</h1>
                <p class="text-gray-500 mt-1">Daftar siswa yang telah lulus</p>
            </div>
        </div>
    </div>

    <!-- Navigation & Quick Links -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
        <a href="{{ route('admin.alumni.index') }}" class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-xl text-sm shadow-sm">
            <i class="fas fa-list mr-1"></i> Data Alumni Sistem
        </a>
        <a href="{{ route('admin.alumni-directory.index') }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 font-medium rounded-xl text-sm transition">
            <i class="fas fa-address-book mr-1"></i> Direktori Alumni (IKA)
        </a>
        <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 font-medium rounded-xl text-sm transition">
            <i class="fas fa-chart-line mr-1"></i> Tracer Study (BMW)
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                       placeholder="Cari nama alumni...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Lulus</label>
                <input type="number" name="graduation_year" value="{{ request('graduation_year') }}"
                       class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                       placeholder="2024">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sekolah</label>
                <select name="school_id" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    <option value="">Semua Sekolah</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium shadow-sm">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Alumni List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 font-medium text-gray-600">No</th>
                        <th class="px-6 py-3 font-medium text-gray-600">NISN</th>
                        <th class="px-6 py-3 font-medium text-gray-600">Nama Lengkap</th>
                        <th class="px-6 py-3 font-medium text-gray-600">Kelas Terakhir</th>
                        <th class="px-6 py-3 font-medium text-gray-600">Tahun Masuk</th>
                        <th class="px-6 py-3 font-medium text-gray-600">Tahun Lulus</th>
                        <th class="px-6 py-3 font-medium text-gray-600">Sekolah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($alumni as $idx => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-500">{{ $alumni->firstItem() + $idx }}</td>
                        <td class="px-6 py-4 font-mono text-gray-700">{{ $item->nisn }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $item->full_name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $item->final_class ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $item->entry_year ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $item->graduation_year }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $item->school?->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-graduation-cap text-4xl mb-3"></i>
                            <p>Belum ada data alumni.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alumni->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $alumni->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
