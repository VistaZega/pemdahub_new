@extends('layouts.admin')
@section('title', 'Direktori Ikatan Alumni (IKA)')

@section('content')
<div class="space-y-6">
    <!-- Hero Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 p-8 text-white shadow-xl border border-purple-900/50">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -top-10 w-48 h-48 bg-pink-600/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-purple-500 via-indigo-500 to-pink-500 shadow-lg shadow-purple-500/30 ring-4 ring-white/10">
                    <i class="fas fa-address-book text-white text-3xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-500/30 text-purple-200 border border-purple-400/30 backdrop-blur-sm">
                            <i class="fas fa-users mr-1"></i> IKA PEMBDA Community
                        </span>
                    </div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white drop-shadow-sm">Direktori Ikatan Alumni (IKA)</h1>
                    <p class="text-purple-200/80 text-sm mt-1">Pusat database alumni publik & pendaftaran mandiri ikatan keluarga alumni</p>
                </div>
            </div>

            <div>
                <a href="{{ route('admin.alumni-directory.create') }}" class="px-5 py-3 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-xl font-bold text-sm transition shadow-lg shadow-purple-500/25 flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Tambah Data Alumni
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Pills -->
    <div class="flex items-center gap-2 bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/80 w-fit">
        <a href="{{ route('admin.alumni.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-900 hover:bg-white/60 font-semibold rounded-xl text-sm transition flex items-center gap-2">
            <i class="fas fa-list text-slate-400"></i> Data Alumni Sistem
        </a>
        <a href="{{ route('admin.alumni-directory.index') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-xl text-sm shadow-md shadow-purple-500/20 flex items-center gap-2">
            <i class="fas fa-address-book"></i> Direktori Alumni (IKA)
        </a>
        <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-900 hover:bg-white/60 font-semibold rounded-xl text-sm transition flex items-center gap-2">
            <i class="fas fa-chart-line text-slate-400"></i> Tracer Study (BMW)
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-800 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
        <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Alumni Directory Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 text-xs uppercase tracking-wider font-bold">
                        <th class="px-6 py-4">Nama & Alumni</th>
                        <th class="px-6 py-4">Sekolah & Tahun</th>
                        <th class="px-6 py-4">Profesi / Perusahaan</th>
                        <th class="px-6 py-4 text-center">Status Publik</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($directories as $dir)
                    <tr class="hover:bg-purple-50/30 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3.5">
                                <img src="{{ $dir->photo_url }}" class="w-11 h-11 rounded-full object-cover border-2 border-purple-200 shadow-sm" alt="{{ $dir->full_name }}">
                                <div>
                                    <p class="font-bold text-slate-900 text-base">{{ $dir->full_name }} {{ $dir->alias_name ? "({$dir->alias_name})" : '' }}</p>
                                    <p class="text-xs text-slate-500 font-medium">
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold mr-1">{{ $dir->gender == 'L' ? 'L' : 'P' }}</span>
                                        {{ $dir->phone ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-slate-900 font-bold">{{ $dir->school->name ?? '-' }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 font-bold text-xs border border-purple-200">
                                    Lulus: {{ $dir->graduation_year }}
                                </span>
                                @if($dir->jurusan)
                                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-semibold text-xs border border-indigo-100">
                                    {{ $dir->jurusan }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-slate-900 font-semibold">{{ $dir->occupation ?? 'Belum Diisi' }}</p>
                            @if($dir->company_name)
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                <i class="fas fa-building text-slate-400 mr-1"></i>{{ $dir->company_name }}
                            </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($dir->is_approved)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                    <i class="fas fa-check-circle text-emerald-600"></i> Disetujui
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200 shadow-sm">
                                    <i class="fas fa-clock text-amber-600"></i> Menunggu
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                            <a href="{{ route('admin.alumni-directory.show', $dir) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.alumni-directory.edit', $dir) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition shadow-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.alumni-directory.toggle-approval', $dir) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $dir->is_approved ? 'membatalkan persetujuan' : 'menyetujui' }} data alumni ini untuk tampil di publik?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-xl {{ $dir->is_approved ? 'bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white' }} transition shadow-sm" title="{{ $dir->is_approved ? 'Batalkan Persetujuan' : 'Setujui' }}">
                                    <i class="fas {{ $dir->is_approved ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.alumni-directory.destroy', $dir) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data alumni ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition shadow-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-slate-400 bg-slate-50/50">
                            <div class="w-16 h-16 rounded-full bg-slate-200/60 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-address-book text-3xl"></i>
                            </div>
                            <p class="font-bold text-slate-700 text-base">Belum Ada Data Direktori Alumni</p>
                            <p class="text-xs text-slate-500 mt-1">Data pendaftaran mandiri IKA PEMBDA akan muncul di sini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($directories->hasPages())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $directories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
