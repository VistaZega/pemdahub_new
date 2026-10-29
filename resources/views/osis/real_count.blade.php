@php
    $currentUser = auth()->user();
    $layout = 'layouts.siswa';
    if ($currentUser && in_array($currentUser->role, ['guru', 'teacher', 'pegawai', 'staff'])) {
        $layout = 'layouts.guru';
    } elseif ($currentUser && in_array($currentUser->role, ['admin', 'superadmin', 'yayasan'])) {
        $layout = 'layouts.app';
    }
@endphp

@extends($layout)
@section('title', 'Real Count - Pemilihan OSIS ' . ($election->school->name ?? ''))

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    {{-- Breadcrumb --}}
    <div class="flex items-center text-xs font-semibold text-slate-500 gap-2">
        <a href="{{ url('/') }}" class="hover:text-emerald-600 transition-colors">Dashboard</a>
        <span>&rsaquo;</span>
        <a href="{{ route('osis.booth') }}" class="hover:text-emerald-600 transition-colors">Pemilihan OSIS</a>
        <span>&rsaquo;</span>
        <span class="text-slate-800 font-bold">Real Count & Rekapitulasi</span>
    </div>

    {{-- Header Banner Real Count --}}
    <div class="relative overflow-hidden rounded-3xl p-6 md:p-8 border-2 border-slate-900 shadow-xl"
         style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #064e3b 100%); color: #ffffff;">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold uppercase mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    @if($election->isClosed()) REKAPITULASI RESMI FINAL @else LIVE REAL COUNT SISTEM @endif
                </span>
                <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight">
                    Perolehan Suara Pemilihan OSIS
                </h1>
                <p class="text-slate-300 text-xs md:text-sm mt-1">
                    {{ $election->title }} &mdash; Periode {{ $election->period }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('osis.booth') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/20 transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Bilik Suara
                </a>
                @if($election->isClosed() || in_array($currentUser->role ?? '', ['superadmin', 'admin', 'yayasan']))
                    <a href="{{ route('osis.report', $election->id) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow border-2 border-black transition flex items-center gap-2">
                        <i class="fas fa-print"></i> Cetak Berita Acara
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- KONDISI JIKA MODE RAHASIA / BLIND VOTE SEDANG AKTIF --}}
    @if($hideResults)
        <div class="bg-white rounded-3xl p-10 border-2 border-slate-900 shadow-xl text-center max-w-2xl mx-auto space-y-4">
            <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 text-2xl flex items-center justify-center mx-auto border-2 border-amber-300">
                <i class="fas fa-lock"></i>
            </div>
            <h3 class="text-lg font-black text-slate-900">Perolehan Suara Paslon Sedang Dirahasiakan</h3>
            <p class="text-xs text-slate-600 leading-relaxed max-w-md mx-auto">
                Untuk menjaga asas demokrasi yang adil dan mencegah penggiringan opini (<em>bandwagon effect</em>), hasil perolehan suara masing-masing kandidat akan dibuka otomatis setelah masa pemungutan suara resmi berakhir pada:
                <strong class="block text-slate-900 mt-1">{{ $election->end_time ? $election->end_time->translatedFormat('l, d F Y - H:i') : 'Selesai' }} WIB</strong>
            </p>
            
            {{-- Tingkat Partisipasi Masih Boleh Terlihat --}}
            <div class="mt-6 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-2">
                    <span>Partisipasi Pemilih Masuk (DPT)</span>
                    <span class="text-emerald-600 font-mono font-black">{{ $turnout }}%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                    <div class="bg-emerald-600 h-3 rounded-full transition-all duration-1000" style="width: {{ $turnout }}%;"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-2">
                    <span>Total Suara Masuk: <strong>{{ number_format($totalVotes) }}</strong></span>
                    <span>Total DPT Terdaftar: <strong>{{ number_format($totalVoters) }}</strong></span>
                </div>
            </div>
        </div>
    @else
        {{-- STATISTIK UTAMA --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-3xl border-2 border-slate-900 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 text-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-to-slot"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Suara Masuk</span>
                    <span class="text-2xl font-black text-slate-900 font-mono">{{ number_format($totalVotes) }}</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-3xl border-2 border-slate-900 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 text-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Daftar Pemilih (DPT)</span>
                    <span class="text-2xl font-black text-slate-900 font-mono">{{ number_format($totalVoters) }}</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-3xl border-2 border-slate-900 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 text-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Tingkat Partisipasi</span>
                    <span class="text-2xl font-black text-emerald-600 font-mono">{{ $turnout }}%</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-3xl border-2 border-slate-900 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 text-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="truncate">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Suara Terbanyak</span>
                    <span class="text-sm font-black text-slate-900 truncate block">
                        {{ $winner ? 'Paslon 0' . $winner->candidate_number : '-' }}
                    </span>
                    <span class="text-[10px] text-slate-500 truncate block">{{ $winner?->leader_name ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- GRID KARTU PEROLEHAN SUARA --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($election->candidates as $candidate)
                @php
                    $isLeading = $winner && $winner->id === $candidate->id && $candidate->vote_count > 0;
                    $pct = $totalVotes > 0 ? round(($candidate->vote_count / $totalVotes) * 100, 1) : 0;
                @endphp

                <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-lg overflow-hidden flex flex-col justify-between transition relative {{ $isLeading ? 'ring-4 ring-emerald-500/30' : '' }}">
                    @if($isLeading && $election->isClosed())
                        <div class="bg-emerald-600 text-white text-[10px] font-black uppercase text-center py-1 tracking-wider shadow">
                            👑 PASLON TERPILIH (PEMENANG)
                        </div>
                    @endif

                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-lg bg-slate-900 text-white font-black text-xs">
                            PASLON 0{{ $candidate->candidate_number }}
                        </span>
                        <span class="text-xs font-mono font-black text-slate-700">{{ $pct }}%</span>
                    </div>

                    <div class="p-4 flex items-center gap-4">
                        <img src="{{ $candidate->photo_url }}" 
                             alt="{{ $candidate->leader_name }}" 
                             class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-900 flex-shrink-0 shadow">
                        <div class="truncate">
                            <h4 class="font-black text-sm text-slate-900 truncate">{{ $candidate->leader_name }}</h4>
                            <span class="text-[11px] text-slate-500 block truncate">Sek: {{ $candidate->secretary_name ?? '-' }}</span>
                            <span class="text-[11px] text-slate-500 block truncate">Ben: {{ $candidate->treasurer_name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-bold">Total Perolehan:</span>
                            <span class="font-black text-slate-900 font-mono text-base">{{ number_format($candidate->vote_count) }} Suara</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-emerald-600 h-2.5 rounded-full" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
