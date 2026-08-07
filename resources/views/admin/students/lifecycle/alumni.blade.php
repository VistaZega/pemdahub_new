@extends('layouts.admin')

@section('title', 'Data Alumni')

@section('content')
<div class="space-y-6">
    <!-- Hero Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 text-white shadow-xl border border-indigo-900/50">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -top-10 w-48 h-48 bg-purple-600/15 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 shadow-lg shadow-indigo-500/30 ring-4 ring-white/10">
                    <i class="fas fa-user-graduate text-white text-3xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 backdrop-blur-sm">
                            <i class="fas fa-graduation-cap mr-1"></i> Student Lifecycle
                        </span>
                    </div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white drop-shadow-sm">Data Alumni Sistem</h1>
                    <p class="text-indigo-200/80 text-sm mt-1">Kelola data resmi seluruh lulusan siswa Yayasan Perguruan Pembda Nias</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.alumni-directory.index') }}" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-medium text-sm transition backdrop-blur-md border border-white/10 shadow-sm flex items-center gap-2">
                    <i class="fas fa-address-book text-indigo-300"></i> Direktori IKA
                </a>
                <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl font-medium text-sm transition shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                    <i class="fas fa-chart-line"></i> Tracer Study
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Pills -->
    <div class="flex items-center gap-2 bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/80 w-fit">
        <a href="{{ route('admin.alumni.index') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl text-sm shadow-md shadow-indigo-500/20 flex items-center gap-2">
            <i class="fas fa-list"></i> Data Alumni Sistem
        </a>
        <a href="{{ route('admin.alumni-directory.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-900 hover:bg-white/60 font-semibold rounded-xl text-sm transition flex items-center gap-2">
            <i class="fas fa-address-book text-slate-400"></i> Direktori Alumni (IKA)
        </a>
        <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-900 hover:bg-white/60 font-semibold rounded-xl text-sm transition flex items-center gap-2">
            <i class="fas fa-chart-line text-slate-400"></i> Tracer Study (BMW)
        </a>
    </div>

    <!-- Tab Guide Info Box -->
    <div class="bg-gradient-to-r from-indigo-900/5 via-purple-900/5 to-blue-900/5 rounded-2xl border border-indigo-100 p-4 shadow-sm">
        <div class="flex items-center gap-2 mb-2.5 text-indigo-950 font-bold text-xs uppercase tracking-wider">
            <i class="fas fa-info-circle text-indigo-600 text-sm"></i> Panduan Fitur & Fungsi Tab Alumni
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
            <div class="p-3.5 rounded-xl bg-white/90 border border-indigo-100 shadow-2xs">
                <div class="font-bold text-indigo-900 flex items-center gap-1.5 mb-1 text-sm">
                    <i class="fas fa-list text-indigo-600"></i> Data Alumni Sistem
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Data resmi siswa internal yang di-generate otomatis saat siswa diluluskan oleh sekolah (terikat NISN, NIS, & kelas terakhir).
                </p>
            </div>
            <div class="p-3.5 rounded-xl bg-white/90 border border-purple-100 shadow-2xs">
                <div class="font-bold text-purple-900 flex items-center gap-1.5 mb-1 text-sm">
                    <i class="fas fa-address-book text-purple-600"></i> Direktori Alumni (IKA)
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Database publik Ikatan Alumni (termasuk alumni pendaftaran mandiri) untuk profil sosial, riwayat kerja, & jejaring komunitas.
                </p>
            </div>
            <div class="p-3.5 rounded-xl bg-white/90 border border-emerald-100 shadow-2xs">
                <div class="font-bold text-emerald-900 flex items-center gap-1.5 mb-1 text-sm">
                    <i class="fas fa-chart-line text-emerald-600"></i> Tracer Study (BMW)
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Laporan survei keterserapan karir (Bekerja, Melanjutkan Kuliah, Wirausaha) untuk keperluan pelaporan dinas & akreditasi.
                </p>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-gradient-to-b from-white to-slate-50/50 rounded-2xl shadow-sm border border-slate-200/80 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Cari Nama / NISN</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-white rounded-xl border-slate-300 pl-11 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 shadow-sm font-medium"
                           placeholder="Ketik nama atau NISN...">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tahun Lulus</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-calendar-alt text-sm"></i>
                    </div>
                    <input type="number" name="graduation_year" value="{{ request('graduation_year') }}"
                           class="w-full bg-white rounded-xl border-slate-300 pl-11 pr-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 shadow-sm font-medium"
                           placeholder="Contoh: 2026">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Unit Sekolah</label>
                <select name="school_id" class="w-full bg-white rounded-xl border-slate-300 py-2.5 px-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 shadow-sm font-medium">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition font-semibold text-sm shadow-md shadow-indigo-500/20 flex items-center justify-center gap-2">
                    <i class="fas fa-filter"></i> Terapkan Filter
                </button>
                @if(request()->anyFilled(['search', 'graduation_year', 'school_id']))
                <a href="{{ route('admin.alumni.index') }}" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-medium text-sm transition flex items-center justify-center" title="Reset Filter">
                    <i class="fas fa-undo"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Alumni List Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 text-xs uppercase tracking-wider font-bold">
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Siswa</th>
                        <th class="px-6 py-4">Sekolah</th>
                        <th class="px-6 py-4">Kelas Terakhir</th>
                        <th class="px-6 py-4 text-center">Tahun Masuk</th>
                        <th class="px-6 py-4 text-center">Tahun Lulus</th>
                        <th class="px-6 py-4 text-center">Status Selesai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alumni as $idx => $item)
                    <tr class="hover:bg-indigo-50/40 transition">
                        <td class="px-6 py-4 font-semibold text-slate-400">{{ $alumni->firstItem() + $idx }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 text-white flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-indigo-100">
                                    {{ strtoupper(substr($item->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $item->full_name }}</p>
                                    <p class="text-xs font-mono text-slate-500">NISN: {{ $item->nisn ?? '-' }} • NIS: {{ $item->nis ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-800 font-semibold text-xs border border-slate-200">
                                <i class="fas fa-school text-indigo-500"></i> {{ $item->school?->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-700">
                            @if($item->final_class)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs">
                                    <i class="fas fa-chalkboard"></i> {{ $item->final_class }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-mono text-xs font-bold">
                                {{ $item->entry_year ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs border border-emerald-200 shadow-sm">
                                <i class="fas fa-user-check mr-1"></i> {{ $item->graduation_year }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 font-bold text-xs border border-indigo-200">
                                <i class="fas fa-check-double mr-1"></i> Lulus Siswa
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-slate-400 bg-slate-50/50">
                            <div class="w-16 h-16 rounded-full bg-slate-200/60 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user-graduate text-3xl"></i>
                            </div>
                            <p class="font-bold text-slate-700 text-base">Belum Ada Data Alumni</p>
                            <p class="text-xs text-slate-500 mt-1">Data alumni akan muncul setelah siswa dinyatakan lulus pada modul lifecycle.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alumni->hasPages())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $alumni->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
