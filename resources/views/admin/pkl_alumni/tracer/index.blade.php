@extends('layouts.admin')
@section('title', 'Laporan Tracer Study Alumni - Portal Admin')

@section('content')
<div class="space-y-6">
    <!-- Hero Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 text-white shadow-xl border border-indigo-900/50">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -top-10 w-48 h-48 bg-blue-600/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 via-blue-500 to-emerald-500 shadow-lg shadow-indigo-500/30 ring-4 ring-white/10">
                    <i class="fas fa-chart-line text-white text-3xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 backdrop-blur-sm">
                            <i class="fas fa-briefcase mr-1"></i> Career & Study Analytics
                        </span>
                    </div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white drop-shadow-sm">Tracer Study Alumni (BMW)</h1>
                    <p class="text-indigo-200/80 text-sm mt-1">Penelusuran keterserapan kerja (Bekerja, Melanjutkan, Wirausaha) lulusan Pembda Nias</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-4 py-2 rounded-xl bg-white/10 text-white font-semibold text-xs border border-white/10 backdrop-blur-md">
                    <i class="fas fa-poll mr-1.5 text-emerald-400"></i> {{ $tracers->total() }} Responden Tracer
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Pills -->
    <div class="flex items-center gap-2 bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/80 w-fit">
        <a href="{{ route('admin.alumni.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-900 hover:bg-white/60 font-semibold rounded-xl text-sm transition flex items-center gap-2">
            <i class="fas fa-list text-slate-400"></i> Data Alumni Sistem
        </a>
        <a href="{{ route('admin.alumni-directory.index') }}" class="px-5 py-2.5 text-slate-600 hover:text-slate-900 hover:bg-white/60 font-semibold rounded-xl text-sm transition flex items-center gap-2">
            <i class="fas fa-address-book text-slate-400"></i> Direktori Alumni (IKA)
        </a>
        <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-semibold rounded-xl text-sm shadow-md shadow-indigo-500/20 flex items-center gap-2">
            <i class="fas fa-chart-line"></i> Tracer Study (BMW)
        </a>
    </div>

    <!-- Tab Guide Info Box -->
    <div class="bg-gradient-to-r from-indigo-900/5 via-blue-900/5 to-emerald-900/5 rounded-2xl border border-indigo-100 p-4 shadow-sm">
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
        <form action="{{ route('admin.pkl-alumni.tracer.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Cari Nama Alumni</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alumni..." class="w-full bg-white border border-slate-300 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 transition shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Status BMW</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 transition shadow-sm">
                    <option value="">Semua Status (Kerja/Kuliah/Wirausaha)</option>
                    <option value="kerja" {{ request('status') === 'kerja' ? 'selected' : '' }}>💼 Bekerja</option>
                    <option value="kuliah" {{ request('status') === 'kuliah' ? 'selected' : '' }}>🎓 Melanjutkan Kuliah</option>
                    <option value="wirausaha" {{ request('status') === 'wirausaha' ? 'selected' : '' }}>🏬 Wirausaha</option>
                    <option value="mencari_kerja" {{ request('status') === 'mencari_kerja' ? 'selected' : '' }}>🔍 Mencari Kerja</option>
                    <option value="lainnya" {{ request('status') === 'lainnya' ? 'selected' : '' }}>📌 Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tahun Lulus</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-calendar-alt text-sm"></i>
                    </div>
                    <input type="number" name="graduation_year" value="{{ request('graduation_year') }}" placeholder="Contoh: 2026" class="w-full bg-white border border-slate-300 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 transition shadow-sm">
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm shadow-md shadow-indigo-500/20 transition flex items-center justify-center gap-2">
                    <i class="fas fa-filter"></i> Filter Tracer
                </button>
                @if(request()->anyFilled(['search', 'status', 'graduation_year', 'school_id']))
                    <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-medium text-sm transition flex items-center justify-center" title="Reset Filter">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tracer Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-900 text-slate-200 text-xs uppercase tracking-wider font-bold">
                        <th class="px-6 py-4">Alumni & Sekolah</th>
                        <th class="px-6 py-4 text-center">Tahun Lulus</th>
                        <th class="px-6 py-4 text-center">Status BMW</th>
                        <th class="px-6 py-4">Rincian Karir / Studi</th>
                        <th class="px-6 py-4">Tgl Survei</th>
                        <th class="px-6 py-4">Umpan Balik Sekolah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tracers as $t)
                        <tr class="hover:bg-indigo-50/30 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-blue-500 text-white flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-indigo-100 flex-shrink-0">
                                        {{ strtoupper(substr($t->alumni->full_name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-base leading-tight">{{ $t->alumni->full_name }}</p>
                                        <p class="text-xs text-slate-500 font-medium mt-0.5"><i class="fas fa-school text-indigo-400 mr-1"></i>{{ $t->alumni->school->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 font-mono font-bold text-xs border border-slate-200">
                                    {{ $t->alumni->graduation_year }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badgeStyle = match($t->employment_status) {
                                        'kerja' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'kuliah' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'wirausaha' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'mencari_kerja' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                                    };
                                    $statusText = match($t->employment_status) {
                                        'kerja' => '💼 Bekerja',
                                        'kuliah' => '🎓 Kuliah',
                                        'wirausaha' => '🏬 Wirausaha',
                                        'mencari_kerja' => '🔍 Mencari Kerja',
                                        default => '📌 Lainnya'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border shadow-sm {{ $badgeStyle }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($t->employment_status === 'kerja')
                                    <p class="font-bold text-slate-900">{{ $t->job_title }}</p>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5"><i class="fas fa-building text-slate-400 mr-1"></i>{{ $t->company_name }}</p>
                                    @if($t->salary_range)
                                        <p class="text-xs text-emerald-600 font-bold mt-0.5"><i class="fas fa-money-bill-wave mr-1"></i>{{ $t->salary_range }}</p>
                                    @endif
                                @elseif($t->employment_status === 'kuliah')
                                    <p class="font-bold text-slate-900">{{ $t->major }}</p>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5"><i class="fas fa-university text-slate-400 mr-1"></i>{{ $t->university_name }}</p>
                                @elseif($t->employment_status === 'wirausaha')
                                    <p class="font-bold text-slate-900">Wirausaha</p>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5"><i class="fas fa-store text-slate-400 mr-1"></i>Bidang: {{ $t->wirausaha_field }}</p>
                                @else
                                    <span class="text-slate-400 italic text-xs">Belum ada rincian</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-xs">
                                {{ $t->survey_date ? $t->survey_date->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 max-w-[220px] truncate" title="{{ $t->feedback_for_school }}">
                                <span class="text-slate-600 italic text-xs">"{{ $t->feedback_for_school ?? '-' }}"</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-slate-400 bg-slate-50/50">
                                <div class="w-16 h-16 rounded-full bg-slate-200/60 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-chart-line text-3xl"></i>
                                </div>
                                <p class="font-bold text-slate-700 text-base">Belum Ada Data Tracer Study</p>
                                <p class="text-xs text-slate-500 mt-1">Hasil pengisian survei oleh alumni akan tampil di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tracers->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $tracers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
