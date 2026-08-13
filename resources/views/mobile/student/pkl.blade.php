@extends('mobile.layouts.app')

@section('title', 'Jurnal PKL - Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Card -->
    <div class="glass-card rounded-3xl p-5 bg-gradient-to-br from-indigo-950/80 via-slate-900 to-slate-950 border border-indigo-500/30">
        <span class="text-[10px] font-bold text-indigo-400 uppercase">Praktik Kerja Lapangan (PKL)</span>
        <h2 class="text-base font-extrabold text-white mt-1">{{ $pklPlacement->company->name ?? 'Lokasi PKL Belum Ditentukan' }}</h2>
        <p class="text-xs text-slate-400 mt-0.5"><i class="fa-solid fa-user-tie mr-1"></i>Pembimbing: {{ $pklPlacement->advisor->full_name ?? '-' }}</p>
    </div>

    <!-- Input Log Form -->
    @if($pklPlacement)
    <div class="glass-card rounded-2xl p-4">
        <h3 class="text-xs font-bold text-white mb-3">Input Jurnal Kegiatan PKL Harian</h3>

        <form action="{{ route('mobile.pkl.log') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label for="date" class="block text-xs text-slate-300 mb-1">Tanggal</label>
                <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs">
            </div>

            <div>
                <label for="activity_description" class="block text-xs text-slate-300 mb-1">Deskripsi Kegiatan</label>
                <textarea id="activity_description" name="activity_description" rows="3" required
                          placeholder="Jelaskan pekerjaan / kegiatan PKL hari ini..."
                          class="w-full p-3 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs resize-none"></textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 text-white font-bold text-xs rounded-xl shadow-md">
                Kirim Jurnal PKL
            </button>
        </form>
    </div>
    @endif

    <!-- Logs History -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Riwayat Jurnal PKL</h3>

        @forelse($logs as $log)
            <div class="glass-card rounded-2xl p-3.5 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white">{{ \Carbon\Carbon::parse($log->date)->translatedFormat('l, d M Y') }}</span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase
                        {{ $log->status === 'approved' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                        {{ $log->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-300">{{ $log->activity_description }}</p>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                Belum ada jurnal PKL yang diinput.
            </div>
        @endforelse
    </div>
</div>
@endsection
