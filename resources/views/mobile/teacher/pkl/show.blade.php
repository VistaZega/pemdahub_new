@extends('mobile.layouts.app')

@section('title', 'Verifikasi Logbook PKL - ' . ($placement->student->full_name ?? 'Siswa'))

@section('content')
<div class="space-y-4 pt-1">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.guru.pkl') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Jurnal Siswa PKL</h2>
        <div class="w-9"></div>
    </div>

    <!-- Student Summary Hero Card -->
    <div class="clay-orange p-5 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">
                Logbook Harian
            </span>
            <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full">
                {{ $placement->logs->count() }} Total Laporan
            </span>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white/20 border-2 border-white/40 text-white font-black text-lg flex items-center justify-center shrink-0">
                {{ strtoupper(substr($placement->student->full_name ?? 'S', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-black text-white leading-tight truncate">
                    {{ $placement->student->full_name ?? '-' }}
                </h3>
                <p class="text-xs text-orange-100 font-bold mt-0.5 truncate">
                    NISN: {{ $placement->student->nisn ?? '-' }} | {{ $placement->dudi->name ?? ($placement->company_name ?? 'DUDI') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Daily Logbooks List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                Daftar Jurnal Kegiatan Harian ({{ $placement->logs->count() }})
            </h3>
        </div>

        @forelse($placement->logs as $log)
            <div class="clay-card p-4 space-y-3 border-2 {{ $log->status === 'approved' ? 'border-emerald-200' : 'border-amber-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar-check text-orange-500"></i>
                        {{ \Carbon\Carbon::parse($log->log_date ?? $log->created_at)->translatedFormat('l, d F Y') }}
                    </span>

                    @if($log->status === 'approved')
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-green">
                            <i class="fa-solid fa-check-double text-[8px] mr-0.5"></i> Terverifikasi
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-yellow">
                            <i class="fa-solid fa-hourglass-half text-[8px] mr-0.5"></i> Menunggu
                        </span>
                    @endif
                </div>

                <!-- Foto Bukti PKL yang Diunggah Siswa -->
                @if($log->photo_url)
                    <div class="rounded-2xl overflow-hidden border-2 border-slate-200 bg-slate-100 relative group">
                        <img src="{{ $log->photo_url }}" 
                             alt="Bukti PKL {{ $log->log_date }}" 
                             class="w-full h-44 object-cover"
                             onclick="window.open('{{ $log->photo_url }}', '_blank')">
                        <div class="absolute bottom-2 right-2 bg-black/70 backdrop-blur-xs text-white px-2 py-1 rounded-lg text-[9px] font-bold flex items-center gap-1">
                            <i class="fa-solid fa-magnifying-glass-plus"></i> Klik untuk Perbesar
                        </div>
                    </div>
                @endif

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs font-bold text-slate-800 leading-relaxed whitespace-pre-line">
                    {{ $log->activity ?? $log->activity_description }}
                </div>

                <!-- GPS Tagging Info -->
                @if($log->latitude && $log->longitude)
                    <div class="flex items-center justify-between text-blue-700 bg-blue-50/70 px-2.5 py-1.5 rounded-xl border border-blue-200 text-[10px] font-extrabold">
                        <span class="flex items-center gap-1 truncate">
                            <i class="fa-solid fa-location-dot text-rose-500"></i>
                            <span>Tag GPS: {{ number_format($log->latitude, 5) }}, {{ number_format($log->longitude, 5) }}</span>
                        </span>
                        <a href="https://maps.google.com/?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank"
                           class="text-[9px] font-black text-blue-800 bg-white px-2 py-0.5 rounded-md border border-blue-300 shadow-2xs hover:bg-blue-100 transition shrink-0">
                            Buka Peta <i class="fa-solid fa-arrow-up-right-from-square text-[8px] ml-0.5"></i>
                        </a>
                    </div>
                @endif

                <!-- Action Button for Teacher -->
                <div class="pt-1 flex items-center justify-between">
                    @if($log->status === 'approved')
                        <span class="text-[10px] font-extrabold text-emerald-700 flex items-center gap-1">
                            <i class="fa-solid fa-shield-check"></i> Disetujui: {{ \Carbon\Carbon::parse($log->approved_at)->translatedFormat('d/m/Y H:i') }}
                        </span>
                    @else
                        <form action="{{ route('mobile.guru.pkl.log.approve', [$placement->id, $log->id]) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" onclick="return confirm('Verifikasi & setujui jurnal PKL tanggal {{ \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') }}?')"
                                    class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-black text-xs shadow-md active:scale-95 flex items-center justify-center gap-1.5 transition">
                                <i class="fa-solid fa-circle-check"></i> Setujui & ACC Jurnal Ini (+10 Poin)
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-1">
                <p>Siswa belum menginput jurnal harian PKL.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
