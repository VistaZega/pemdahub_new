@extends('layouts.admin')
@section('title', 'Pengurus OSIS Terpilih & Reward Poin - ' . $election->title)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-12">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl border-2 border-slate-900 shadow-md">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-1">
                <a href="{{ route('admin.osis.index') }}" class="hover:text-emerald-600">E-Voting OSIS</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800">Pengurus OSIS Terpilih</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>👑 Pengurus OSIS Terpilih & 150 Poin</span>
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                {{ $election->title }} &bull; Periode {{ $election->period }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('osis.real-count', $election->slug) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition flex items-center gap-1.5 shadow">
                <i class="fas fa-chart-pie text-emerald-400"></i> Real Count
            </a>
            <a href="{{ route('admin.osis.index') }}" class="px-4 py-2 rounded-xl border-2 border-slate-900 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-5 rounded-2xl bg-emerald-50 border-2 border-emerald-600 text-emerald-950 text-xs font-bold flex items-center gap-3 shadow-md animate-in fade-in">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg flex-shrink-0">
                <i class="fas fa-crown"></i>
            </div>
            <div>
                <span class="block text-sm font-black text-emerald-900">Pengesahan Berhasil!</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- KARTU HIGHLIGHT PASLON TERPILIH --}}
    @if($selectedWinner)
        <div class="bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-100 rounded-3xl p-6 md:p-8 border-2 border-amber-500 shadow-xl relative overflow-hidden">
            <div class="flex flex-col md:flex-row items-center gap-6 relative z-10">
                <img src="{{ $selectedWinner->photo_url }}" 
                     alt="{{ $selectedWinner->leader_name }}" 
                     class="w-32 h-32 md:w-36 md:h-36 rounded-3xl object-cover border-4 border-amber-400 shadow-2xl flex-shrink-0">

                <div class="flex-1 text-center md:text-left space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow">
                        <i class="fas fa-crown"></i> PASLON PEMENANG (SUARA TERBANYAK)
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900">
                        PASLON 0{{ $selectedWinner->candidate_number }} &mdash; {{ $selectedWinner->leader_name }}
                    </h2>
                    <p class="text-xs md:text-sm text-slate-700 italic">
                        &ldquo;{{ $selectedWinner->quote_slogan }}&rdquo;
                    </p>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 pt-2">
                        <span class="bg-white/80 px-3 py-1 rounded-xl text-xs font-bold text-slate-800 border border-amber-300">
                            Perolehan: <strong>{{ number_format($selectedWinner->vote_count) }} Suara</strong> ({{ $selectedWinner->vote_percentage }}%)
                        </span>
                        <span class="bg-white/80 px-3 py-1 rounded-xl text-xs font-bold text-emerald-800 border border-emerald-300">
                            Status: <strong>{{ $election->isCompleted() ? 'Resmi Disahkan' : 'Menunggu Pengesahan' }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- FORM PENGESAHAN & PEMETAAN AKUN SISWA UNTUK 150 POIN --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-xl p-6 md:p-8 space-y-6">
        <div>
            <h3 class="text-base font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
                <i class="fas fa-award text-amber-500"></i>
                Pengesahan Akun Pengurus & Pemberian +150 Poin Prestasi
            </h3>
            <p class="text-xs text-slate-600 mt-1">
                Pilih akun siswa yang bersangkutan untuk masing-masing posisi pengurus OSIS terpilih. Saat disahkan, sistem otomatis menambahkan <strong>150 Poin Prestasi</strong> ke profil siswa di PembdaHUB.
            </p>
        </div>

        <form action="{{ route('admin.osis.assign-officers', $election->id) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Pilihan Paslon Pemenang --}}
            <div>
                <label class="block text-xs font-black uppercase text-slate-700 mb-1.5">Pilih Paslon yang Disahkan Sebagai Pemenang <span class="text-rose-500">*</span></label>
                <select name="candidate_id" class="w-full rounded-xl border-2 border-slate-900 p-2.5 text-xs font-bold text-slate-800" required>
                    @foreach($election->candidates as $c)
                        <option value="{{ $c->id }}" {{ ($selectedWinner && $selectedWinner->id === $c->id) ? 'selected' : '' }}>
                            Paslon 0{{ $c->candidate_number }} - {{ $c->leader_name }} ({{ number_format($c->vote_count) }} Suara)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 3 Kolom Pemetaan Akun --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 p-5 rounded-2xl bg-slate-50 border-2 border-slate-200">
                
                {{-- 1. Ketua OSIS --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase text-slate-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> KETUA OSIS
                        </span>
                        <span class="text-[10px] font-bold text-amber-600">+150 Poin</span>
                    </div>
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200 text-xs">
                        <span class="text-slate-400 block text-[10px]">Nama di Paslon:</span>
                        <span class="font-bold text-slate-800">{{ $selectedWinner?->leader_name ?? '-' }}</span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Hubungkan Akun Siswa (User ID):</label>
                        <select name="leader_user_id" class="w-full rounded-xl border border-slate-300 p-2 text-xs font-medium">
                            <option value="">-- Pilih Akun Siswa --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->user_id }}" {{ ($selectedWinner && $selectedWinner->leader_user_id == $st->user_id) ? 'selected' : '' }}>
                                    {{ $st->full_name }} (NISN: {{ $st->nisn ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 2. Sekretaris OSIS --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase text-slate-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> SEKRETARIS OSIS
                        </span>
                        <span class="text-[10px] font-bold text-amber-600">+150 Poin</span>
                    </div>
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200 text-xs">
                        <span class="text-slate-400 block text-[10px]">Nama di Paslon:</span>
                        <span class="font-bold text-slate-800">{{ $selectedWinner?->secretary_name ?? '-' }}</span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Hubungkan Akun Siswa (User ID):</label>
                        <select name="secretary_user_id" class="w-full rounded-xl border border-slate-300 p-2 text-xs font-medium">
                            <option value="">-- Pilih Akun Siswa --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->user_id }}" {{ ($selectedWinner && $selectedWinner->secretary_user_id == $st->user_id) ? 'selected' : '' }}>
                                    {{ $st->full_name }} (NISN: {{ $st->nisn ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 3. Bendahara OSIS --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase text-slate-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span> BENDAHARA OSIS
                        </span>
                        <span class="text-[10px] font-bold text-amber-600">+150 Poin</span>
                    </div>
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200 text-xs">
                        <span class="text-slate-400 block text-[10px]">Nama di Paslon:</span>
                        <span class="font-bold text-slate-800">{{ $selectedWinner?->treasurer_name ?? '-' }}</span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Hubungkan Akun Siswa (User ID):</label>
                        <select name="treasurer_user_id" class="w-full rounded-xl border border-slate-300 p-2 text-xs font-medium">
                            <option value="">-- Pilih Akun Siswa --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->user_id }}" {{ ($selectedWinner && $selectedWinner->treasurer_user_id == $st->user_id) ? 'selected' : '' }}>
                                    {{ $st->full_name }} (NISN: {{ $st->nisn ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Status Poin Saat Ini --}}
            @if($selectedWinner && $selectedWinner->points_awarded)
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 flex items-center justify-between text-xs text-emerald-900">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-emerald-600 text-base"></i>
                        <span><strong>Reward Selesai:</strong> Masing-masing pengurus telah menerima <strong>150 Poin Reputasi</strong> pada {{ $selectedWinner->points_awarded_at ? $selectedWinner->points_awarded_at->translatedFormat('d F Y - H:i') : '' }} WIB.</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-600 text-white font-black text-[10px]">AKTIF</span>
                </div>
            @endif

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin mengesahkan Pengurus OSIS ini dan memberikan 150 Poin Prestasi ke akun masing-masing pengurus?');" class="px-6 py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-lg border-2 border-black transition hover:scale-105 flex items-center gap-2">
                    <i class="fas fa-crown"></i>
                    <span>Sahkan Pengurus OSIS & Berikan 150 Poin Prestasi</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
