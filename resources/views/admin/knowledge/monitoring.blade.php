@extends('layouts.admin')

@section('title', 'Pantauan Pembda Knowledge & Media - Monitoring Kepsek & Yayasan')

@section('content')
<div class="space-y-6">

    {{-- Hero Header Section --}}
    <div class="relative overflow-hidden rounded-2xl p-8 shadow-xl text-white" style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #1e1b4b 100%);">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full border border-white/20 text-emerald-200 text-xs font-bold uppercase tracking-wider">
                    <i class="fas fa-chart-line text-amber-300"></i> Dashboard Pantauan Pimpinan & Yayasan
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">Pantauan Jurnal & Repositori Civitas Akademika</h1>
                <p class="text-emerald-100 text-sm max-w-2xl leading-relaxed">
                    Monitoring keaktifan Civitas Akademika Perguruan Pembda dalam mempublikasikan jurnal ilmiah, artikel edukasi, modul digital, serta rekapitulasi Poin Prestasi & apresiasi karya.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('knowledge.download_template') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white font-bold px-4 py-2.5 rounded-xl backdrop-blur-md border border-white/20 transition-all text-xs">
                    <i class="fas fa-file-download text-amber-300"></i> Template Jurnal (Opsional)
                </a>
                <a href="{{ route('knowledge.index') }}" target="_blank" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black px-4 py-2.5 rounded-xl transition-all text-xs shadow-md">
                    <i class="fas fa-external-link-alt"></i> Etalase Publik
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Total Materi Terunggah</p>
            <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalMaterials) }}</h3>
            <span class="text-[11px] text-emerald-600 font-semibold mt-1 inline-block"><i class="fas fa-check-circle"></i> Karya Guru</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Total Dibaca/Views</p>
            <h3 class="text-2xl font-bold text-sky-600 mt-1">{{ number_format($totalViews) }}</h3>
            <span class="text-[11px] text-sky-600 font-semibold mt-1 inline-block"><i class="fas fa-eye"></i> Interaksi Pengunjung</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Total Disukai (Likes)</p>
            <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($totalLikes) }}</h3>
            <span class="text-[11px] text-rose-600 font-semibold mt-1 inline-block"><i class="fas fa-heart"></i> Respon Siswa</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Disimpan Favorit</p>
            <h3 class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($totalBookmarks) }}</h3>
            <span class="text-[11px] text-indigo-600 font-semibold mt-1 inline-block"><i class="fas fa-bookmark"></i> Bookmark</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium text-gray-500">Total Unduhan (Downloads)</p>
            <h3 class="text-2xl font-bold text-teal-600 mt-1">{{ number_format($totalDownloads) }}</h3>
            <span class="text-[11px] text-teal-600 font-semibold mt-1 inline-block"><i class="fas fa-download"></i> Terunduh</span>
        </div>
    </div>

    {{-- Leaderboard Guru & Contributor Points --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden space-y-4">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-trophy text-amber-500"></i> Leaderboard & Poin Kontributor Guru
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Poin dihitung otomatis dari Publikasi (+10), Likes (+2), Bookmarks (+3), dan Views/10 (+1).</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/80 text-gray-600 text-xs uppercase tracking-wider font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Peringkat</th>
                        <th class="px-6 py-4">Nama Guru</th>
                        <th class="px-6 py-4">Sekolah</th>
                        <th class="px-6 py-4 text-center">Materi Terunggah</th>
                        <th class="px-6 py-4 text-center">Likes / Favorit</th>
                        <th class="px-6 py-4 text-center">Views</th>
                        <th class="px-6 py-4 text-right">Total Poin Karya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($teacherLeaderboard as $index => $item)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4 font-bold">
                                @if($index === 0)
                                    <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-black inline-flex items-center justify-center text-sm shadow-sm border border-amber-300">🥇 1</span>
                                @elseif($index === 1)
                                    <span class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-black inline-flex items-center justify-center text-sm shadow-sm border border-slate-300">🥈 2</span>
                                @elseif($index === 2)
                                    <span class="w-8 h-8 rounded-full bg-orange-100 text-orange-800 font-black inline-flex items-center justify-center text-sm shadow-sm border border-orange-300">🥉 3</span>
                                @else
                                    <span class="text-gray-500 pl-3">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-800">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $item->teacher->photo_url }}" class="w-9 h-9 rounded-full object-cover border border-gray-200">
                                    <span>{{ $item->teacher->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-gray-500">
                                {{ $item->teacher->school->name ?? 'Pembda' }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-700">
                                {{ $item->uploads }}
                            </td>
                            <td class="px-6 py-4 text-center text-xs font-semibold text-gray-600">
                                <span class="text-rose-600"><i class="fas fa-heart"></i> {{ $item->likes }}</span> /
                                <span class="text-indigo-600"><i class="fas fa-bookmark"></i> {{ $item->bookmarks }}</span>
                            </td>
                            <td class="px-6 py-4 text-center text-xs font-semibold text-sky-600">
                                {{ number_format($item->views) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-block px-3 py-1 bg-amber-50 text-amber-700 font-extrabold text-sm rounded-xl border border-amber-200 shadow-sm">
                                    {{ number_format($item->points) }} Poin
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">Belum ada data karya guru terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Uploads Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-clock text-teal-600"></i> Publikasi Terbaru
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3">Materi</th>
                        <th class="px-4 py-3">Guru</th>
                        <th class="px-4 py-3">Format</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-center">Views / Likes</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentUploads as $upload)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-4 py-3 font-bold text-gray-800">
                                <a href="{{ route('knowledge.show', $upload->slug) }}" target="_blank" class="hover:text-teal-600">
                                    {{ $upload->title }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $upload->teacher->full_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs capitalize font-semibold">
                                {{ $upload->type }}
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $upload->category_type === 'sekolah' ? 'bg-indigo-50 text-indigo-700' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $upload->category_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">
                                {{ $upload->views_count }} views · {{ $upload->likes_count }} likes
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('knowledge.show', $upload->slug) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-all" title="Pratinjau">
                                        <i class="fas fa-eye"></i> Buka
                                    </a>
                                    <form action="{{ route('admin.knowledge.destroy', $upload->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini dari sistem?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-lg transition-all border border-rose-200" title="Hapus Materi">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
