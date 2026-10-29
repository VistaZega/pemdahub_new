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
@section('title', 'Pemilihan OSIS - ' . ($election->school->name ?? 'PembdaHUB'))

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    {{-- BREADCRUMB --}}
    <div class="flex items-center text-xs font-semibold text-slate-500 gap-2">
        <a href="{{ url('/') }}" class="hover:text-emerald-600 transition-colors">Dashboard</a>
        <span>&rsaquo;</span>
        <span class="text-slate-800 font-bold">Pemilihan OSIS</span>
    </div>

    {{-- 1. HERO BANNER + WIDGET WAKTU --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
        
        {{-- BANNER UTAMA (9 Cols) --}}
        <div class="lg:col-span-8 relative overflow-hidden rounded-3xl p-6 md:p-8 border-2 border-slate-900 shadow-xl flex flex-col justify-between"
             style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #047857 100%); color: #ffffff;">
            
            {{-- Background Pattern & Glow --}}
            <div class="absolute -top-12 -right-12 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                {{-- Logo Sekolah / Yayasan --}}
                <div class="flex-shrink-0 bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/20 shadow-lg text-center">
                    <img src="{{ asset('images/logo.png') }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=OSIS&background=047857&color=fff&size=100'" 
                         alt="Logo" class="w-16 h-16 md:w-20 md:h-20 object-contain mx-auto">
                    <span class="block text-[10px] font-black uppercase tracking-wider text-emerald-300 mt-1">E-Voting</span>
                </div>

                {{-- Headline & Tagline --}}
                <div class="text-center md:text-left flex-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold tracking-wider uppercase mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        PERIODE {{ $election->period ?? '2026 / 2027' }}
                    </div>
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-black tracking-tight leading-tight uppercase drop-shadow-md">
                        {{ $election->title ?? 'PEMILIHAN OSIS' }}
                    </h1>
                    <p class="text-slate-200 text-sm md:text-base mt-2 font-medium italic drop-shadow">
                        &ldquo;{{ $election->theme_tagline ?? 'Bersama Membangun Masa Depan yang Lebih Baik' }}&rdquo;
                    </p>
                </div>
            </div>

            {{-- Footer Info Strip di Banner --}}
            <div class="relative z-10 mt-6 pt-4 border-t border-white/10 flex flex-wrap items-center justify-between text-xs text-slate-300 gap-3">
                <div class="flex items-center gap-2">
                    <i class="fas fa-shield-halved text-emerald-400"></i>
                    <span>Asas LUBER (Langsung, Umum, Bebas, Rahasia, Jujur & Adil)</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-white/10 px-2.5 py-1 rounded-lg border border-white/10 font-mono">
                        {{ $election->school->name ?? 'SMK Swasta Pembda Nias' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- WIDGET JADWAL & COUNTDOWN TIMER (4 Cols) --}}
        <div class="lg:col-span-4 flex flex-col gap-4">
            {{-- Waktu Pemungutan Suara --}}
            <div class="bg-white rounded-2xl p-4 border-2 border-slate-900 shadow-md">
                <div class="flex items-center gap-2 text-slate-800 font-extrabold text-sm mb-3">
                    <i class="fas fa-calendar-check text-blue-600"></i>
                    <span>Waktu Pemungutan Suara</span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block font-semibold text-[10px] uppercase">Mulai</span>
                        <span class="font-bold text-slate-800 text-[11px] block mt-0.5">
                            {{ $election->start_time ? $election->start_time->translatedFormat('D, d M Y') : 'Sen, 27 Okt 2026' }}
                        </span>
                        <span class="font-black text-emerald-600 text-xs">
                            {{ $election->start_time ? $election->start_time->format('H:i') . ' WIB' : '07:00 WIB' }}
                        </span>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block font-semibold text-[10px] uppercase">Berakhir</span>
                        <span class="font-bold text-slate-800 text-[11px] block mt-0.5">
                            {{ $election->end_time ? $election->end_time->translatedFormat('D, d M Y') : 'Jum, 31 Okt 2026' }}
                        </span>
                        <span class="font-black text-rose-600 text-xs">
                            {{ $election->end_time ? $election->end_time->format('H:i') . ' WIB' : '15:00 WIB' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Countdown Timer Live --}}
            <div class="bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-950 text-white rounded-2xl p-4 border-2 border-slate-900 shadow-md flex-1 flex flex-col justify-center">
                <div class="flex items-center justify-between text-xs font-bold text-slate-300 mb-2">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-hourglass-half text-amber-400 animate-spin" style="animation-duration: 8s;"></i>
                        <span>Sisa Waktu Pemilihan</span>
                    </span>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        @if($election->isVotingOpen()) LIVE @elseif($election->isUpcoming()) AKAN DATANG @else SELESAI @endif
                    </span>
                </div>

                <div class="grid grid-cols-4 gap-2 text-center mt-1" id="countdown-timer">
                    <div class="bg-white/10 rounded-xl p-2 border border-white/10">
                        <span class="block text-xl md:text-2xl font-black font-mono text-emerald-400" id="cd-days">0</span>
                        <span class="text-[9px] uppercase font-bold text-slate-300">Hari</span>
                    </div>
                    <div class="bg-white/10 rounded-xl p-2 border border-white/10">
                        <span class="block text-xl md:text-2xl font-black font-mono text-cyan-400" id="cd-hours">00</span>
                        <span class="text-[9px] uppercase font-bold text-slate-300">Jam</span>
                    </div>
                    <div class="bg-white/10 rounded-xl p-2 border border-white/10">
                        <span class="block text-xl md:text-2xl font-black font-mono text-amber-400" id="cd-minutes">00</span>
                        <span class="text-[9px] uppercase font-bold text-slate-300">Menit</span>
                    </div>
                    <div class="bg-white/10 rounded-xl p-2 border border-white/10">
                        <span class="block text-xl md:text-2xl font-black font-mono text-rose-400" id="cd-seconds">00</span>
                        <span class="text-[9px] uppercase font-bold text-slate-300">Detik</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. STATUS HAK PILIH / STRUK COBLOS DIGITAL --}}
    @if($voter && $voter->has_voted)
        {{-- SUDAH MEMILIH --}}
        <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-100 rounded-3xl p-6 border-2 border-emerald-600 shadow-md relative overflow-hidden">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-2xl shadow-lg border-2 border-emerald-700 flex-shrink-0">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider mb-1">
                            ✓ SUARA SAH TERCATAT
                        </div>
                        <h3 class="text-lg md:text-xl font-black text-emerald-950">
                            Terima Kasih, {{ $currentUser->name }}! Hak Suara Anda Telah Disalurkan
                        </h3>
                        <p class="text-xs text-emerald-800 mt-0.5">
                            Pilihan Anda telah tersimpan secara anonim dalam kotak suara terenkripsi. Sesuai asas rahasia, suara Anda tidak dapat diubah kembali.
                        </p>
                    </div>
                </div>

                {{-- Token Bukti Digital --}}
                <div class="bg-white px-4 py-2.5 rounded-2xl border-2 border-emerald-500 shadow-sm text-center md:text-right flex-shrink-0">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Nomor Tiket Suara Digital</span>
                    <span class="font-mono font-black text-sm md:text-base text-emerald-700 tracking-wider">
                        {{ $voter->vote_token ?? 'PEMBDA-VOTE-HASH' }}
                    </span>
                    <span class="text-[10px] text-slate-500 block">
                        Waktu: {{ $voter->voted_at ? $voter->voted_at->translatedFormat('d M Y - H:i') : now()->format('d M Y - H:i') }} WIB
                    </span>
                </div>
            </div>
        </div>
    @else
        {{-- BELUM MEMILIH --}}
        <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-emerald-50 rounded-2xl p-4 border-2 border-slate-900 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-md">
                <i class="fas fa-circle-info"></i>
            </div>
            <div class="flex-1 text-xs text-slate-700">
                <span class="font-black text-slate-900 text-sm block">Gunakan hak pilih Anda dengan bijak!</span>
                Pilih satu pasangan calon yang menurut Anda paling mampu memimpin OSIS {{ $election->school->name ?? 'SMK Pembda Nias' }} periode {{ $election->period ?? '2026/2027' }}.
            </div>
            @if(!$election->isVotingOpen())
                <span class="px-3 py-1.5 rounded-xl bg-rose-100 text-rose-800 text-xs font-black border border-rose-300">
                    Bilik Suara Belum Dibuka / Selesai
                </span>
            @endif
        </div>
    @endif

    {{-- 3. GRID PASANGAN CALON PENGURUS OSIS --}}
    <div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-6">
            <div>
                <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fas fa-users-gear text-emerald-600"></i>
                    Pasangan Calon Pengurus OSIS
                </h2>
                <p class="text-xs text-slate-600 mt-1">
                    Kenali visi, misi, dan program kerja setiap pasangan calon sebelum memberikan suara.
                </p>
            </div>

            @if($election->show_realtime_count || $election->isClosed() || in_array($currentUser->role, ['superadmin', 'admin', 'yayasan']))
                <a href="{{ route('osis.real-count', $election->slug) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow">
                    <i class="fas fa-chart-pie text-emerald-400"></i>
                    Lihat Hasil Real Count
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($election->candidates as $candidate)
                @php
                    $badgeColors = [
                        1 => ['bg' => 'bg-rose-600', 'border' => 'border-rose-600', 'text' => 'text-rose-600', 'light' => 'bg-rose-50'],
                        2 => ['bg' => 'bg-blue-600', 'border' => 'border-blue-600', 'text' => 'text-blue-600', 'light' => 'bg-blue-50'],
                        3 => ['bg' => 'bg-amber-500', 'border' => 'border-amber-500', 'text' => 'text-amber-500', 'light' => 'bg-amber-50'],
                        4 => ['bg' => 'bg-emerald-600', 'border' => 'border-emerald-600', 'text' => 'text-emerald-600', 'light' => 'bg-emerald-50'],
                    ];
                    $cStyle = $badgeColors[$candidate->candidate_number] ?? ['bg' => 'bg-slate-800', 'border' => 'border-slate-800', 'text' => 'text-slate-800', 'light' => 'bg-slate-50'];
                    $hasVoted = $voter && $voter->has_voted;
                @endphp

                <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-lg overflow-hidden flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl relative group">
                    
                    {{-- Top Card Header --}}
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl {{ $cStyle['bg'] }} text-white font-black text-sm flex items-center justify-center shadow-md">
                                {{ $candidate->candidate_number }}
                            </span>
                            <div>
                                <span class="text-[10px] uppercase font-black tracking-wider text-slate-400 block leading-none">Calon Pengurus OSIS</span>
                                <span class="text-xs font-black text-slate-800">PASLON NOMOR {{ $candidate->candidate_number }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Foto Paslon --}}
                    <div class="relative bg-slate-100 overflow-hidden" style="aspect-ratio: 4/3;">
                        <img src="{{ $candidate->photo_url }}" 
                             alt="Paslon {{ $candidate->candidate_number }}" 
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&auto=format&fit=crop&q=80';">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-3 text-white">
                            {{-- Susunan KSB / Paslon --}}
                            <div class="grid grid-cols-3 gap-1 text-center">
                                @if($candidate->secretary_name)
                                    <div class="bg-black/60 backdrop-blur-sm rounded-lg p-1">
                                        <span class="text-[8px] uppercase tracking-wider text-amber-300 block font-bold">Sekretaris</span>
                                        <span class="text-[10px] font-black truncate block leading-tight" title="{{ $candidate->secretary_name }}">
                                            {{ Str::limit($candidate->secretary_name, 12) }}
                                        </span>
                                    </div>
                                @endif
                                
                                <div class="bg-black/80 backdrop-blur-sm rounded-lg p-1 border border-white/30 transform scale-105 shadow-md">
                                    <span class="text-[8px] uppercase tracking-wider text-emerald-300 block font-black">KETUA</span>
                                    <span class="text-[10px] font-black truncate block leading-tight" title="{{ $candidate->leader_name }}">
                                        {{ Str::limit($candidate->leader_name, 13) }}
                                    </span>
                                </div>

                                @if($candidate->treasurer_name)
                                    <div class="bg-black/60 backdrop-blur-sm rounded-lg p-1">
                                        <span class="text-[8px] uppercase tracking-wider text-amber-300 block font-bold">Bendahara</span>
                                        <span class="text-[10px] font-black truncate block leading-tight" title="{{ $candidate->treasurer_name }}">
                                            {{ Str::limit($candidate->treasurer_name, 12) }}
                                        </span>
                                    </div>
                                @elseif($candidate->vice_name)
                                    <div class="bg-black/60 backdrop-blur-sm rounded-lg p-1">
                                        <span class="text-[8px] uppercase tracking-wider text-cyan-300 block font-bold">Wakil Ketua</span>
                                        <span class="text-[10px] font-black truncate block leading-tight" title="{{ $candidate->vice_name }}">
                                            {{ Str::limit($candidate->vice_name, 12) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Body Kutipan Slogan --}}
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-xs text-slate-600 line-clamp-4 italic leading-relaxed">
                            &ldquo;{{ $candidate->quote_slogan ?? 'Membangun generasi OSIS yang berkarakter, berdaya saing, dan inovatif.' }}&rdquo;
                        </p>

                        {{-- Tombol Aksi --}}
                        <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2">
                            {{-- Tombol Lihat Detail --}}
                            <button type="button" 
                                    onclick="openCandidateModal({{ json_encode($candidate) }})"
                                    class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl border-2 border-slate-900 text-slate-800 text-xs font-bold hover:bg-slate-100 transition">
                                <i class="fas fa-eye text-blue-600"></i>
                                <span>Lihat Detail</span>
                            </button>

                            {{-- Tombol Pilih Paslon --}}
                            @if($hasVoted)
                                <button type="button" disabled
                                        class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-200 text-slate-400 text-xs font-bold cursor-not-allowed border-2 border-slate-300">
                                    <i class="fas fa-check"></i>
                                    <span>Terkunci</span>
                                </button>
                            @elseif(!$election->isVotingOpen())
                                <button type="button" disabled
                                        class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-200 text-slate-400 text-xs font-bold cursor-not-allowed border-2 border-slate-300">
                                    <i class="fas fa-lock"></i>
                                    <span>Tutup</span>
                                </button>
                            @else
                                <button type="button" 
                                        onclick="confirmVote({{ $candidate->id }}, {{ $candidate->candidate_number }}, '{{ addslashes($candidate->leader_name) }}', '{{ $candidate->photo_url }}')"
                                        class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md border-2 border-slate-900 transition hover:scale-[1.02] active:scale-95">
                                    <i class="fas fa-check-to-slot"></i>
                                    <span>Pilih Paslon</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-4 p-12 text-center bg-white rounded-3xl border-2 border-slate-300">
                    <p class="text-slate-500 font-bold">Belum ada data calon pengurus OSIS yang diinput.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 4. FOOTER INFORMASI TAMBAHAN, HAK PILIH & KEAMANAN (3 Cards) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-4">
        
        {{-- Card 1: Informasi Tambahan --}}
        <div class="bg-white rounded-3xl p-5 border-2 border-slate-900 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 text-slate-900 font-black text-sm mb-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <span>Informasi Tambahan</span>
                </div>
                <ul class="text-xs text-slate-600 space-y-2">
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mt-1.5 flex-shrink-0"></span>
                        <span>Setiap akun civitas hanya dapat memberikan <strong>satu suara</strong>.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mt-1.5 flex-shrink-0"></span>
                        <span>Pastikan Anda telah membaca visi, misi, dan program kerja sebelum memilih.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mt-1.5 flex-shrink-0"></span>
                        <span>Setelah tombol simpan ditekan, suara tidak dapat diubah kembali.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mt-1.5 flex-shrink-0"></span>
                        <span>Jika mengalami kendala teknis, segera hubungi Panitia KPU OSIS / Admin Sekolah.</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Card 2: Siapa yang Berhak Memilih? --}}
        <div class="bg-white rounded-3xl p-5 border-2 border-slate-900 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 text-slate-900 font-black text-sm mb-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                        <i class="fas fa-id-card-clip"></i>
                    </div>
                    <span>Siapa yang Berhak Memilih?</span>
                </div>
                <ul class="text-xs text-slate-600 space-y-2">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                        <span>Seluruh siswa aktif {{ $election->school->name ?? 'SMK Swasta Pembda Nias' }}.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                        <span>Seluruh bapak/ibu guru dan tenaga kependidikan aktif.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                        <span>Gunakan akun PembdaHUB masing-masing yang telah terdaftar.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                        <span>Pemilihan dapat diakses via smartphone maupun laptop/komputer lab.</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Card 3: Keamanan dan Kerahasiaan --}}
        <div class="bg-white rounded-3xl p-5 border-2 border-slate-900 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 text-slate-900 font-black text-sm mb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span>Keamanan & Kerahasiaan</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    <strong>Pilihan Anda bersifat rahasia.</strong> Sistem E-Voting PembdaHUB memisahkan identitas pemilih dari kartu suara yang dicoblos ke kotak suara digital. Sistem menjamin tidak ada seorang pun (termasuk panitia & admin) yang dapat mengetahui paslon pilihan Anda.
                </p>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-500">
                    <span>Protokol Enkripsi:</span>
                    <span class="text-emerald-700 font-mono">SHA-256 Protected</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL PASLON (POSTER HD, VISI & MISI) --}}
<div id="modal-candidate-detail" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in-95 duration-200">
        {{-- Modal Header --}}
        <div class="sticky top-0 bg-white p-5 border-b border-slate-100 flex items-center justify-between z-10">
            <div class="flex items-center gap-3">
                <span id="modal-badge-number" class="w-9 h-9 rounded-xl bg-slate-900 text-white font-black text-base flex items-center justify-center shadow">1</span>
                <div>
                    <h3 id="modal-candidate-title" class="text-base font-black text-slate-900">PASLON NOMOR 01</h3>
                    <p id="modal-candidate-names" class="text-xs text-slate-500 font-medium"></p>
                </div>
            </div>
            <button type="button" onclick="closeCandidateModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Modal Content --}}
        <div class="p-6 space-y-6">
            {{-- Flyer / Poster HD --}}
            <div class="rounded-2xl overflow-hidden border-2 border-slate-900 shadow-md bg-slate-100">
                <img id="modal-poster-img" src="" alt="Poster Flyer" class="w-full h-auto object-contain max-h-96 mx-auto">
            </div>

            {{-- Visi --}}
            <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1.5">
                    <i class="fas fa-bullseye text-blue-600"></i> VISI UTAMA
                </h4>
                <div class="bg-blue-50/70 p-4 rounded-2xl border border-blue-200 text-xs md:text-sm text-slate-800 font-medium leading-relaxed" id="modal-vision">
                </div>
            </div>

            {{-- Misi --}}
            <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                    <i class="fas fa-list-check text-emerald-600"></i> MISI
                </h4>
                <ul class="space-y-2 text-xs text-slate-700" id="modal-mission-list">
                </ul>
            </div>

            {{-- Program Kerja Unggulan --}}
            <div id="modal-programs-container">
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                    <i class="fas fa-rocket text-purple-600"></i> PROGRAM KERJA UNGGULAN
                </h4>
                <ul class="space-y-2 text-xs text-slate-700" id="modal-programs-list">
                </ul>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-3xl text-right">
            <button type="button" onclick="closeCandidateModal()" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI COBLOS --}}
<div id="modal-confirm-vote" class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-2xl max-w-md w-full p-6 text-center animate-in fade-in zoom-in-95 duration-200">
        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 text-2xl flex items-center justify-center mx-auto mb-4 border-2 border-emerald-200 shadow-inner">
            <i class="fas fa-box-archive"></i>
        </div>

        <h3 class="text-lg font-black text-slate-900">Konfirmasi Surat Suara</h3>
        <p class="text-xs text-slate-600 mt-1">
            Apakah Anda yakin ingin memberikan suara Anda kepada pasangan calon ini?
        </p>

        {{-- Kartu Pilihan Paslon --}}
        <div class="my-5 p-4 rounded-2xl bg-slate-50 border-2 border-slate-900 flex items-center gap-4 text-left">
            <img id="confirm-candidate-photo" src="" alt="Paslon" class="w-14 h-14 rounded-xl object-cover border-2 border-slate-900 flex-shrink-0">
            <div>
                <span id="confirm-candidate-number-badge" class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-600 text-white">
                    PASLON NO. 1
                </span>
                <h4 id="confirm-candidate-name" class="font-black text-sm text-slate-900 mt-1"></h4>
                <span class="text-[10px] text-slate-500 block">Calon Ketua OSIS</span>
            </div>
        </div>

        <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-[11px] text-amber-900 mb-6 text-left flex items-start gap-2">
            <i class="fas fa-triangle-exclamation text-amber-600 mt-0.5 flex-shrink-0"></i>
            <span><strong>PENTING:</strong> Setelah disimpan, suara Anda langsung dicatat secara permanen dan tidak dapat diganti atau dibatalkan.</span>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <button type="button" onclick="closeConfirmModal()" class="py-3 px-4 rounded-xl border-2 border-slate-900 text-slate-700 text-xs font-bold hover:bg-slate-100 transition">
                Batal / Pikir Lagi
            </button>
            <button type="button" id="btn-submit-vote" onclick="executeSubmitVote()" class="py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-lg border-2 border-slate-900 transition hover:scale-[1.02] flex items-center justify-center gap-1.5">
                <span>Ya, Coblos Sekarang!</span>
            </button>
        </div>
    </div>
</div>

{{-- SCRIPT INTERAKTIF (COUNTDOWN, MODALS, VOTING AJAX & CONFETTI) --}}
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    // 1. COUNTDOWN TIMER ENGINE
    const endTimeString = "{{ $election->end_time ? $election->end_time->toIso8601String() : '' }}";
    function updateCountdown() {
        if (!endTimeString) return;
        const end = new Date(endTimeString).getTime();
        const now = new Date().getTime();
        const distance = end - now;

        if (distance < 0) {
            document.getElementById('cd-days').innerText = '0';
            document.getElementById('cd-hours').innerText = '00';
            document.getElementById('cd-minutes').innerText = '00';
            document.getElementById('cd-seconds').innerText = '00';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('cd-days').innerText = days;
        document.getElementById('cd-hours').innerText = String(hours).padStart(2, '0');
        document.getElementById('cd-minutes').innerText = String(minutes).padStart(2, '0');
        document.getElementById('cd-seconds').innerText = String(seconds).padStart(2, '0');
    }
    setInterval(updateCountdown, 1000);
    updateCountdown();

    // 2. MODAL DETAIL PASLON
    function openCandidateModal(c) {
        document.getElementById('modal-badge-number').innerText = c.candidate_number;
        document.getElementById('modal-candidate-title').innerText = 'PASLON NOMOR ' + c.candidate_number;
        
        let names = c.leader_name;
        if (c.secretary_name) names += ' (Ketua) & ' + c.secretary_name + ' (Sekretaris)';
        document.getElementById('modal-candidate-names').innerText = names;

        // Poster
        const posterImg = document.getElementById('modal-poster-img');
        posterImg.src = c.poster_flyer ? (c.poster_flyer.startsWith('http') ? c.poster_flyer : '/' + c.poster_flyer) : (c.photo ? '/' + c.photo : 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&auto=format&fit=crop&q=80');

        // Visi
        document.getElementById('modal-vision').innerText = c.vision || c.quote_slogan || '-';

        // Misi List
        const missionList = document.getElementById('modal-mission-list');
        missionList.innerHTML = '';
        if (Array.isArray(c.mission) && c.mission.length > 0) {
            c.mission.forEach((m, idx) => {
                const li = document.createElement('li');
                li.className = 'flex items-start gap-2';
                li.innerHTML = `<span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] flex items-center justify-center flex-shrink-0 mt-0.5">${idx+1}</span> <span>${m}</span>`;
                missionList.appendChild(li);
            });
        } else {
            missionList.innerHTML = '<li class="text-slate-400 italic">Misi belum dicantumkan.</li>';
        }

        // Program Kerja List
        const progList = document.getElementById('modal-programs-list');
        progList.innerHTML = '';
        if (Array.isArray(c.work_programs) && c.work_programs.length > 0) {
            c.work_programs.forEach((p, idx) => {
                const li = document.createElement('li');
                li.className = 'flex items-start gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100';
                li.innerHTML = `<i class="fas fa-star text-amber-500 mt-1 flex-shrink-0"></i> <span>${p}</span>`;
                progList.appendChild(li);
            });
            document.getElementById('modal-programs-container').classList.remove('hidden');
        } else {
            document.getElementById('modal-programs-container').classList.add('hidden');
        }

        document.getElementById('modal-candidate-detail').classList.remove('hidden');
    }

    function closeCandidateModal() {
        document.getElementById('modal-candidate-detail').classList.add('hidden');
    }

    // 3. VOTING ENGINE CONFIRMATION & AJAX
    let selectedCandidateId = null;

    function confirmVote(id, number, name, photoUrl) {
        selectedCandidateId = id;
        document.getElementById('confirm-candidate-photo').src = photoUrl;
        document.getElementById('confirm-candidate-number-badge').innerText = 'PASLON NOMOR ' + number;
        document.getElementById('confirm-candidate-name').innerText = name;
        document.getElementById('modal-confirm-vote').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('modal-confirm-vote').classList.add('hidden');
    }

    function executeSubmitVote() {
        if (!selectedCandidateId) return;

        const btn = document.getElementById('btn-submit-vote');
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> <span>Mencatat Suara...</span>`;

        fetch("{{ route('osis.vote') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                election_id: {{ $election->id }},
                candidate_id: selectedCandidateId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Confetti blast!
                confetti({
                    particleCount: 150,
                    spread: 80,
                    origin: { y: 0.6 }
                });

                closeConfirmModal();

                // Reload dengan notifikasi atau reload page
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } else {
                alert(data.message || 'Terjadi kesalahan saat mencatat suara.');
                btn.disabled = false;
                btn.innerHTML = `<span>Ya, Coblos Sekarang!</span>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kendala jaringan. Pastikan koneksi internet Anda stabil.');
            btn.disabled = false;
            btn.innerHTML = `<span>Ya, Coblos Sekarang!</span>`;
        });
    }
</script>
@endsection
