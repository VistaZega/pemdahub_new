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
                    @elseif($log->status === 'rejected')
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-300">
                            <i class="fa-solid fa-xmark text-[8px] mr-0.5"></i> Perlu Revisi
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

                <!-- Catatan Revisi jika Ditolak -->
                @if($log->status === 'rejected' && $log->mentor_notes)
                    <div class="p-2.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs space-y-0.5">
                        <span class="font-black flex items-center gap-1"><i class="fa-solid fa-comment-dots text-rose-600"></i> Catatan Revisi Anda:</span>
                        <p class="font-bold italic bg-white/70 p-2 rounded-lg border border-rose-100">"{{ $log->mentor_notes }}"</p>
                    </div>
                @endif

                <!-- Action Buttons for Teacher -->
                <div class="pt-1 space-y-2">
                    @if($log->status === 'approved')
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-emerald-700 flex items-center gap-1">
                                <i class="fa-solid fa-shield-check"></i> Disetujui: {{ \Carbon\Carbon::parse($log->approved_at)->translatedFormat('d/m/Y H:i') }}
                            </span>
                            <button type="button" onclick="openMobileRevisionModal('{{ route('mobile.guru.pkl.log.reject', [$placement->id, $log->id]) }}', '{{ \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') }}')"
                                    class="text-[10px] text-rose-600 font-extrabold underline">
                                Minta Revisi
                            </button>
                        </div>
                    @elseif($log->status === 'rejected')
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] font-bold text-rose-600">Menunggu revisi siswa</span>
                            <form action="{{ route('mobile.guru.pkl.log.approve', [$placement->id, $log->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="py-1.5 px-3 rounded-xl bg-emerald-100 text-emerald-800 font-black text-xs border border-emerald-300">
                                    Setujui Langsung
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="openMobileRevisionModal('{{ route('mobile.guru.pkl.log.reject', [$placement->id, $log->id]) }}', '{{ \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') }}')"
                                    class="py-2.5 px-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-black text-xs shadow-xs active:scale-95 flex items-center justify-center gap-1 transition">
                                <i class="fa-solid fa-rotate-left"></i> Minta Revisi
                            </button>
                            <form action="{{ route('mobile.guru.pkl.log.approve', [$placement->id, $log->id]) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Verifikasi & setujui jurnal PKL tanggal {{ \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') }}?')"
                                        class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-black text-xs shadow-md active:scale-95 flex items-center justify-center gap-1 transition">
                                    <i class="fa-solid fa-circle-check"></i> Setujui (ACC)
                                </button>
                            </form>
                        </div>
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

<!-- Modal Minta Revisi Mobile -->
<div id="mobile-revision-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 hidden">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full max-w-md p-5 space-y-4 border-2 border-slate-200">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-black text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-slate-900">Minta Revisi Jurnal PKL</h4>
                    <p class="text-[10px] text-slate-400 font-bold" id="mobile-modal-date">Tanggal</p>
                </div>
            </div>
            <button type="button" onclick="closeMobileRevisionModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-black active:scale-95">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <form id="mobile-revision-form" action="" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-black text-slate-800 mb-1">
                    Catatan Arahan Revisi untuk Siswa <span class="text-rose-500">*</span>
                </label>
                <textarea name="mentor_notes" rows="3" required
                          placeholder="Tuliskan arahan perbaikan (misal: Foto kurang jelas / Deskripsi mohon diperjelas langkah kerjanya)..."
                          class="w-full p-3 bg-slate-50 border-2 border-slate-200 rounded-2xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-rose-500 resize-none"></textarea>
                <p class="text-[10px] text-slate-400 mt-1 font-semibold">Catatan ini akan dikirim langsung ke notifikasi & HP siswa.</p>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-1">
                <button type="button" onclick="closeMobileRevisionModal()" class="py-2.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-black active:scale-95">
                    Batal
                </button>
                <button type="submit" class="py-2.5 bg-gradient-to-r from-rose-500 to-red-600 text-white rounded-xl text-xs font-black shadow-md active:scale-95 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Revisi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openMobileRevisionModal(actionUrl, dateStr) {
    const modal = document.getElementById('mobile-revision-modal');
    const form = document.getElementById('mobile-revision-form');
    const dateText = document.getElementById('mobile-modal-date');
    form.action = actionUrl;
    dateText.innerText = 'Jurnal Tanggal: ' + dateStr;
    modal.classList.remove('hidden');
}

function closeMobileRevisionModal() {
    const modal = document.getElementById('mobile-revision-modal');
    modal.classList.add('hidden');
}
</script>
@endsection
