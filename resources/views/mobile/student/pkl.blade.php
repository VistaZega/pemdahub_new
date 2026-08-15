@extends('mobile.layouts.app')

@section('title', 'Jurnal PKL Harian - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1">
    <!-- Header Card -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Praktik Kerja Lapangan (PKL)</h2>
        <div class="w-9"></div>
    </div>

    @if($pklPlacement)
        <!-- DUDI Placement Hero Clay Card -->
        <div class="clay-orange p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40 shadow-xs">
                    SMK Kelas XII
                </span>
                <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full border border-white/30">
                    {{ $pklPlacement->shift ? 'Shift ' . ucfirst($pklPlacement->shift) : 'Reguler' }}
                </span>
            </div>

            <div>
                <h3 class="text-lg font-black text-white leading-tight">
                    {{ $pklPlacement->dudi->name ?? ($pklPlacement->company_name ?? 'Lokasi DUDI Belum Ditentukan') }}
                </h3>
                @if($pklPlacement->dudi?->address)
                    <p class="text-[11px] text-orange-100 mt-1 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-xs shrink-0"></i>
                        <span class="truncate">{{ $pklPlacement->dudi->address }}</span>
                    </p>
                @endif
            </div>

            <div class="pt-2 border-t border-white/20 flex items-center justify-between text-[11px] font-bold text-orange-100">
                <div class="flex items-center gap-1.5 min-w-0">
                    <i class="fa-solid fa-chalkboard-user text-xs shrink-0"></i>
                    <span class="truncate">Pembimbing: <strong>{{ $pklPlacement->teacher->full_name ?? '-' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Quick Stats Widget -->
        @php
            $totalLogs = $logs->count();
            $approvedLogs = $logs->where('status', 'approved')->count();
            $pendingLogs = $logs->where('status', '!=', 'approved')->count();
        @endphp
        <div class="grid grid-cols-3 gap-2.5">
            <div class="clay-card p-3 text-center">
                <span class="text-xl font-black text-slate-800 leading-none">{{ $totalLogs }}</span>
                <span class="block text-[9px] font-black text-slate-500 uppercase mt-1">Total Jurnal</span>
            </div>
            <div class="clay-green p-3 text-center">
                <span class="text-xl font-black leading-none">{{ $approvedLogs }}</span>
                <span class="block text-[9px] font-black uppercase mt-1">Disetujui</span>
            </div>
            <div class="clay-yellow p-3 text-center">
                <span class="text-xl font-black leading-none">{{ $pendingLogs }}</span>
                <span class="block text-[9px] font-black uppercase mt-1">Menunggu</span>
            </div>
        </div>

        <!-- Form Input Jurnal PKL Harian (Clay Card) -->
        <div class="clay-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-orange-500"></i> Input Jurnal Kegiatan Hari Ini
                </h3>
                <span class="text-[9px] font-extrabold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-200">Wajib Setiap Hari</span>
            </div>

            <form action="{{ route('mobile.pkl.log') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label for="date" class="block text-xs font-black text-slate-800 mb-1">Tanggal Kegiatan</label>
                    <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}" onclick="try { this.showPicker(); } catch(e) {}" required
                           class="w-full px-3.5 py-2.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold cursor-pointer focus:outline-hidden focus:border-orange-500">
                </div>

                <div>
                    <label for="activity_description" class="block text-xs font-black text-slate-800 mb-1">Deskripsi Aktivitas / Pekerjaan di DUDI</label>
                    <textarea id="activity_description" name="activity_description" rows="3" required
                              placeholder="Tuliskan secara jelas aktivitas pekerjaan, mesin/alat yang digunakan, atau materi yang dipelajari hari ini..."
                              class="w-full p-3.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold resize-none focus:outline-hidden focus:border-orange-500"></textarea>
                </div>

                <button type="submit" class="clay-btn w-full py-3 text-white font-black text-xs flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Jurnal Harian
                </button>
            </form>
        </div>

    @else
        <!-- Empty Placement State -->
        <div class="clay-card p-6 text-center space-y-3">
            <div class="w-14 h-14 rounded-3xl bg-amber-50 border-2 border-amber-200 text-amber-500 text-2xl flex items-center justify-center mx-auto shadow-inner">
                💼
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Penempatan PKL Belum Ditentukan</h3>
                <p class="text-xs text-slate-500 font-semibold mt-1">Data penempatan tempat DUDI dan Guru Pembimbing Anda sedang dalam proses oleh Panitia PKL & Hubin SMK.</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-[11px] font-bold text-slate-600">
                Hubungi Panitia PKL atau Guru Jurusan untuk informasi penempatan industri Anda.
            </div>
        </div>
    @endif

    <!-- Riwayat Jurnal PKL -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Riwayat Jurnal PKL ({{ $logs->count() }})</h3>
        </div>

        @forelse($logs as $log)
            <div class="clay-card p-4 space-y-2 border-2 {{ $log->status === 'approved' ? 'border-emerald-200' : 'border-amber-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar-check text-blue-500"></i>
                        {{ \Carbon\Carbon::parse($log->log_date ?? $log->created_at)->translatedFormat('l, d F Y') }}
                    </span>
                    @if($log->status === 'approved')
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-green">
                            <i class="fa-solid fa-check-double text-[8px] mr-0.5"></i> Disetujui
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-yellow">
                            <i class="fa-solid fa-hourglass-half text-[8px] mr-0.5"></i> Menunggu
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-700 font-bold leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                    {{ $log->activity ?? $log->activity_description }}
                </p>

                @if($log->status === 'approved' && $log->approved_at)
                    <div class="text-[10px] font-extrabold text-emerald-700 flex items-center justify-between pt-1">
                        <span><i class="fa-solid fa-shield-halved mr-1"></i>Diverifikasi Pembimbing</span>
                        <span>{{ \Carbon\Carbon::parse($log->approved_at)->translatedFormat('d M Y H:i') }}</span>
                    </div>
                @endif
            </div>
        @empty
            @if($pklPlacement)
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Belum ada jurnal harian yang dikirim. Mulai laporkan kegiatan PKL Anda hari ini! 🚀
            </div>
            @endif
        @endforelse
    </div>
</div>
@endsection
