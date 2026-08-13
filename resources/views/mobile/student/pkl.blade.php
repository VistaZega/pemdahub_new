@extends('mobile.layouts.app')

@section('title', 'Jurnal PKL 3D - Mobile Pro')

@section('content')
<div class="space-y-4">
    <!-- Header Clay Card -->
    <div class="clay-orange p-6">
        <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">Praktik Kerja Lapangan (PKL)</span>
        <h2 class="text-base font-black text-white mt-1.5 leading-snug">{{ $pklPlacement->company->name ?? 'Lokasi PKL Belum Ditentukan' }}</h2>
        <p class="text-xs text-orange-100 mt-0.5 font-bold"><i class="fa-solid fa-user-tie mr-1"></i>Pembimbing: {{ $pklPlacement->advisor->full_name ?? '-' }}</p>
    </div>

    <!-- Input Log Form (Clay Card) -->
    @if($pklPlacement)
    <div class="clay-card p-5">
        <h3 class="text-xs font-black text-slate-900 mb-3">Input Jurnal Kegiatan PKL Harian 💼</h3>

        <form action="{{ route('mobile.pkl.log') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label for="date" class="block text-xs font-black text-slate-800 mb-1">Tanggal</label>
                <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}" required
                       class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold">
            </div>

            <div>
                <label for="activity_description" class="block text-xs font-black text-slate-800 mb-1">Deskripsi Kegiatan</label>
                <textarea id="activity_description" name="activity_description" rows="3" required
                          placeholder="Jelaskan pekerjaan / kegiatan PKL hari ini..."
                          class="w-full p-3.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold resize-none"></textarea>
            </div>

            <button type="submit" class="clay-btn w-full py-3.5 text-white font-black text-xs">
                Kirim Jurnal PKL
            </button>
        </form>
    </div>
    @endif

    <!-- Logs History (Clay Cards) -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Riwayat Jurnal PKL</h3>

        @forelse($logs as $log)
            <div class="clay-card p-4 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900">{{ \Carbon\Carbon::parse($log->date)->translatedFormat('l, d M Y') }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase
                        {{ $log->status === 'approved' ? 'clay-green' : 'clay-yellow' }}">
                        {{ $log->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-700 font-bold leading-relaxed">{{ $log->activity_description }}</p>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Belum ada jurnal PKL yang diinput.
            </div>
        @endforelse
    </div>
</div>
@endsection
