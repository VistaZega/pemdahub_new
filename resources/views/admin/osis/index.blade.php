@extends('layouts.app')
@section('title', 'Kelola Pemilihan OSIS - Admin PembdaHUB')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl border-2 border-slate-900 shadow-md">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-1">
                <span>Manajemen Kesiswaan</span>
                <span>&rsaquo;</span>
                <span class="text-slate-800">E-Voting OSIS</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Kelola Pemilihan OSIS
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                Atur jadwal pemilu, paslon, dan pemilih untuk seluruh unit sekolah (SMK, SMA, SMP) tiap periode.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('osis.booth') }}" target="_blank" class="px-4 py-2.5 rounded-xl border-2 border-slate-900 text-slate-800 text-xs font-bold hover:bg-slate-100 transition flex items-center gap-2">
                <i class="fas fa-external-link-alt text-blue-600"></i> Preview Bilik Suara
            </a>
            <a href="{{ route('admin.osis.create') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md border-2 border-slate-900 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Buat Agenda Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-600 text-emerald-900 text-xs font-bold flex items-center gap-3 shadow-xs">
            <i class="fas fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Tabel Event Pemilihan --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-xl overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                Daftar Agenda Pemilihan OSIS
            </h2>
            <span class="text-xs text-slate-500 font-bold">Total: {{ $elections->total() }} Agenda</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-black text-[10px] tracking-wider">
                    <tr>
                        <th class="p-4">Unit Sekolah</th>
                        <th class="p-4">Judul & Periode</th>
                        <th class="p-4">Waktu Pemilihan</th>
                        <th class="p-4 text-center">Status TPS</th>
                        <th class="p-4 text-center">Statistik</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($elections as $el)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4 font-bold text-slate-800">
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-black text-[11px] inline-block border border-emerald-300">
                                    {{ $el->school->name ?? 'Semua Unit' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="font-black text-slate-900 text-sm">{{ $el->title }}</div>
                                <div class="text-slate-500 text-[11px]">Periode {{ $el->period }} &bull; Slug: <code class="text-emerald-700">{{ $el->slug }}</code></div>
                            </td>
                            <td class="p-4 text-slate-600 text-[11px]">
                                <div>Mulai: <strong>{{ $el->start_time ? $el->start_time->format('d/m/Y H:i') : '-' }}</strong></div>
                                <div>Tutup: <strong>{{ $el->end_time ? $el->end_time->format('d/m/Y H:i') : '-' }}</strong></div>
                            </td>
                            <td class="p-4 text-center">
                                @if($el->isVotingOpen())
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-black text-[10px] border border-emerald-400">
                                        ● DIBUKA (VOTING)
                                    </span>
                                @elseif($el->isUpcoming())
                                    <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-black text-[10px] border border-blue-400">
                                        ● SOSIALISASI (UPCOMING)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-black text-[10px] border border-rose-400">
                                        ● DITUTUP
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="font-mono text-slate-800 font-bold">
                                    {{ $el->candidates_count }} Paslon &bull; {{ $el->ballots_count }}/{{ $el->voters_count }} Suara
                                </div>
                                <div class="text-[10px] text-slate-500">Turnout: {{ $el->turnout_percentage }}%</div>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Kelola Paslon --}}
                                    <a href="{{ route('admin.osis.candidates', $el->id) }}" class="p-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-300 font-bold" title="Kelola Paslon">
                                        <i class="fas fa-users"></i> Paslon
                                    </a>

                                    {{-- Sinkronkan DPT --}}
                                    <form action="{{ route('admin.osis.generate-voters', $el->id) }}" method="POST" onsubmit="return confirm('Generate / sinkronkan DPT dari database siswa dan guru sekolah ini?');" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-300 font-bold" title="Sinkronkan DPT">
                                            <i class="fas fa-address-book"></i> DPT
                                        </button>
                                    </form>

                                    {{-- Toggle TPS --}}
                                    <form action="{{ route('admin.osis.toggle-status', $el->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-xl {{ $el->status === 'voting_open' ? 'bg-amber-50 text-amber-700 border-amber-300' : 'bg-emerald-50 text-emerald-700 border-emerald-300' }} border font-bold" title="Buka/Tutup Bilik Suara">
                                            <i class="fas {{ $el->status === 'voting_open' ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.osis.edit', $el->id) }}" class="p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300 font-bold" title="Edit Agenda">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 font-bold">
                                Belum ada agenda pemilihan OSIS yang dibuat. Silakan klik tombol "Buat Agenda Baru".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $elections->links() }}
        </div>
    </div>
</div>
@endsection
