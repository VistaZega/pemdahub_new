<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $election->title ?? 'Pemilihan OSIS' }} - Perguruan Pemda Nias</title>
    
    <!-- Open Graph Metadata untuk WhatsApp & Media Sosial -->
    <meta property="og:title" content="{{ $election->title ?? 'Pemilihan OSIS' }} - Periode {{ $election->period ?? '2026/2027' }}">
    <meta property="og:description" content="Kenali visi, misi, dan program kerja pasangan calon Ketua OSIS {{ $election->school->name ?? 'SMK Swasta Pembda Nias' }}. Salurkan hak suara Anda di E-Voting PembdaHUB!">
    <meta property="og:image" content="{{ asset('images/osis/osis_banner_smk.jpg') }}">
    <meta property="og:type" content="website">

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-emerald-500 selection:text-white">

    {{-- NAVBAR PUBLIK --}}
    <nav class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b-2 border-slate-900 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=PEMBDA&background=047857&color=fff'" alt="Logo" class="w-12 h-12 object-contain group-hover:scale-105 transition-transform">
                <div>
                    <span class="block text-xs uppercase tracking-wider font-extrabold text-emerald-700 leading-none">Yayasan Perguruan Pemda Nias</span>
                    <span class="block text-base font-black text-slate-900 tracking-tight">Portal Pemilihan OSIS</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                    <i class="fas fa-home"></i> Beranda Utama
                </a>
                @auth
                    <a href="{{ route('osis.booth') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md border-2 border-slate-900 transition hover:scale-105">
                        <i class="fas fa-box-archive"></i> Buka Bilik Suara
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-black shadow-md border-2 border-slate-900 transition hover:scale-105">
                        <i class="fas fa-arrow-right-to-bracket text-emerald-400"></i> Masuk untuk Memilih
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- MAIN CONTAINER --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

        @if($election && $election->is_simulation)
            {{-- BANNER SIMULASI PUBLIK --}}
            <div class="rounded-2xl p-4 bg-amber-400 border-2 border-black shadow-[3px_3px_0px_#000] text-slate-950 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-black text-amber-300 flex items-center justify-center text-xl font-black shrink-0">
                        🧪
                    </span>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wider">TAHAP UJI COBA & SIMULASI FITUR E-VOTING OSIS</h2>
                        <p class="text-xs font-semibold text-slate-900 mt-0.5">
                            Halaman publik ini merupakan <strong>tahap simulasi pengenalan fitur kampanye & voting digital</strong> bagi civitas sekolah. Data kandidat dan hak suara akan di-reset bersih saat Pemilihan OSIS Resmi sekolah diselenggarakan.
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-xl bg-black text-amber-300 text-xs font-mono font-black uppercase shrink-0">
                    Mode Simulasi
                </span>
            </div>
        @endif

        {{-- 1. HERO BANNER KAMPANYE --}}
        <div class="relative overflow-hidden rounded-3xl p-8 md:p-12 border-2 border-slate-900 shadow-2xl"
             style="background: linear-gradient(135deg, #090d16 0%, #1e3a8a 50%, #064e3b 100%); color: #ffffff;">
            
            <div class="absolute -top-16 -right-16 w-80 h-80 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -left-16 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold tracking-wider uppercase mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        SUKSESI OSIS PERIODE {{ $election->period ?? '2026 / 2027' }}
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight uppercase">
                        {{ $election->title ?? 'PEMILIHAN PENGURUS OSIS' }}
                    </h1>
                    <p class="text-slate-200 text-base sm:text-lg mt-3 font-medium italic">
                        &ldquo;{{ $election->theme_tagline ?? 'Bersama Membangun Masa Depan yang Lebih Baik' }}&rdquo;
                    </p>
                    <p class="text-slate-300 text-xs sm:text-sm mt-4 max-w-2xl leading-relaxed">
                        {{ $election->description ?? 'Wadah demokrasi civitas sekolah untuk memilih pemimpin masa depan yang berintegritas, kreatif, dan berdaya saing global.' }}
                    </p>

                    <div class="mt-6 flex flex-wrap items-center justify-center lg:justify-start gap-4">
                        @auth
                            <a href="{{ route('osis.booth') }}" class="px-6 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-sm font-black shadow-lg border-2 border-black transition hover:scale-105 inline-flex items-center gap-2">
                                <i class="fas fa-check-to-slot"></i> Berikan Hak Suara Sekarang
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-sm font-black shadow-lg border-2 border-black transition hover:scale-105 inline-flex items-center gap-2">
                                <i class="fas fa-lock"></i> Login Siswa / Guru untuk Memilih
                            </a>
                        @endauth
                        
                        <a href="#kandidat" class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white text-sm font-bold border border-white/20 transition inline-flex items-center gap-2">
                            <i class="fas fa-users"></i> Kenali Paslon
                        </a>
                    </div>
                </div>

                {{-- Countdown Box --}}
                <div class="lg:col-span-4 bg-slate-900/80 backdrop-blur-md rounded-3xl p-6 border-2 border-white/20 shadow-xl text-center">
                    <span class="text-xs uppercase font-extrabold tracking-wider text-amber-400 block mb-1">
                        <i class="fas fa-clock mr-1"></i> Hitung Mundur Pemilihan
                    </span>
                    <span class="text-[11px] text-slate-300 block mb-4">
                        Ditutup pada: {{ $election->end_time ? $election->end_time->translatedFormat('d F Y - H:i') : '31 Okt 2026' }} WIB
                    </span>

                    <div class="grid grid-cols-4 gap-2 text-center" id="countdown-timer">
                        <div class="bg-white/10 rounded-xl p-2.5 border border-white/10">
                            <span class="block text-2xl font-black font-mono text-emerald-400" id="cd-days">0</span>
                            <span class="text-[9px] uppercase font-bold text-slate-400">Hari</span>
                        </div>
                        <div class="bg-white/10 rounded-xl p-2.5 border border-white/10">
                            <span class="block text-2xl font-black font-mono text-cyan-400" id="cd-hours">00</span>
                            <span class="text-[9px] uppercase font-bold text-slate-400">Jam</span>
                        </div>
                        <div class="bg-white/10 rounded-xl p-2.5 border border-white/10">
                            <span class="block text-2xl font-black font-mono text-amber-400" id="cd-minutes">00</span>
                            <span class="text-[9px] uppercase font-bold text-slate-400">Mnt</span>
                        </div>
                        <div class="bg-white/10 rounded-xl p-2.5 border border-white/10">
                            <span class="block text-2xl font-black font-mono text-rose-400" id="cd-seconds">00</span>
                            <span class="text-[9px] uppercase font-bold text-slate-400">Dtk</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/10 text-[11px] text-slate-400 flex items-center justify-between">
                        <span>Unit Penyelenggara:</span>
                        <span class="font-bold text-white">{{ $election->school->name ?? 'SMK Swasta Pembda' }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if($election->isCompleted() && $election->winnerCandidate)
        {{-- SECTION PENGURUS OSIS TERPILIH --}}
        <section class="bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-600 rounded-[2.5rem] p-8 md:p-12 border-3 border-slate-900 shadow-2xl text-slate-950 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                <div class="relative flex-shrink-0">
                    <img src="{{ $election->winnerCandidate->photo_url }}" 
                         alt="{{ $election->winnerCandidate->leader_name }}" 
                         class="w-44 h-44 md:w-52 md:h-52 rounded-3xl object-cover border-4 border-slate-900 shadow-2xl">
                    <div class="absolute -top-3 -right-3 w-12 h-12 rounded-full bg-slate-900 text-amber-300 text-xl flex items-center justify-center border-2 border-white shadow-lg">
                        👑
                    </div>
                </div>

                <div class="flex-1 text-center md:text-left space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900 text-amber-300 text-xs font-black uppercase tracking-wider shadow">
                        <span>PENGURUS OSIS TERPILIH & RESMI DISAHKAN</span>
                    </div>

                    <h2 class="text-3xl md:text-4xl font-black uppercase tracking-tight">
                        {{ $election->winnerCandidate->leader_name }}
                    </h2>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-xs font-bold text-slate-900">
                        <span class="bg-white/60 px-3 py-1 rounded-xl border border-black/10">
                            Sekretaris: <strong>{{ $election->winnerCandidate->secretary_name ?? '-' }}</strong>
                        </span>
                        <span class="bg-white/60 px-3 py-1 rounded-xl border border-black/10">
                            Bendahara: <strong>{{ $election->winnerCandidate->treasurer_name ?? '-' }}</strong>
                        </span>
                        <span class="bg-slate-900 text-amber-300 px-3 py-1 rounded-xl border border-black font-black">
                            ⭐ Peraih +150 Poin Prestasi PembdaHUB
                        </span>
                    </div>

                    <p class="text-xs md:text-sm text-slate-900 italic font-medium pt-1 max-w-2xl">
                        &ldquo;{{ $election->winnerCandidate->quote_slogan }}&rdquo;
                    </p>
                </div>
            </div>
        </section>
        @endif

        {{-- 2. GRID PASANGAN CALON KAMPANYE --}}
        <section id="kandidat" class="space-y-6 pt-4">
            <div class="text-center max-w-2xl mx-auto">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-300">
                    Daftar Kandidat Resmi
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-2">
                    Kenali Pasangan Calon Pengurus OSIS
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Pelajari visi, misi, dan program kerja terbaik mereka sebelum Anda menggunakan hak suara di bilik suara PembdaHUB.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($election->candidates as $candidate)
                    @php
                        $badgeColors = [
                            1 => ['bg' => 'bg-rose-600', 'border' => 'border-rose-600', 'text' => 'text-rose-600'],
                            2 => ['bg' => 'bg-blue-600', 'border' => 'border-blue-600', 'text' => 'text-blue-600'],
                            3 => ['bg' => 'bg-amber-500', 'border' => 'border-amber-500', 'text' => 'text-amber-500'],
                            4 => ['bg' => 'bg-emerald-600', 'border' => 'border-emerald-600', 'text' => 'text-emerald-600'],
                        ];
                        $cStyle = $badgeColors[$candidate->candidate_number] ?? ['bg' => 'bg-slate-800', 'border' => 'border-slate-800', 'text' => 'text-slate-800'];
                    @endphp

                    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-xl overflow-hidden flex flex-col justify-between transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl group">
                        
                        {{-- Top Header Card --}}
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-xl {{ $cStyle['bg'] }} text-white font-black text-sm flex items-center justify-center shadow">
                                    {{ $candidate->candidate_number }}
                                </span>
                                <div>
                                    <span class="text-[10px] uppercase font-black tracking-wider text-slate-400 block leading-none">Paslon Nomor Urut</span>
                                    <span class="text-xs font-black text-slate-800">NOMOR 0{{ $candidate->candidate_number }}</span>
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
                                <div class="grid grid-cols-3 gap-1 text-center">
                                    @if($candidate->secretary_name)
                                        <div class="bg-black/60 backdrop-blur-sm rounded-lg p-1">
                                            <span class="text-[8px] uppercase tracking-wider text-amber-300 block font-bold">Sekretaris</span>
                                            <span class="text-[10px] font-black truncate block leading-tight">{{ Str::limit($candidate->secretary_name, 12) }}</span>
                                        </div>
                                    @endif
                                    
                                    <div class="bg-black/80 backdrop-blur-sm rounded-lg p-1 border border-white/30 transform scale-105 shadow-md">
                                        <span class="text-[8px] uppercase tracking-wider text-emerald-300 block font-black">KETUA</span>
                                        <span class="text-[10px] font-black truncate block leading-tight">{{ Str::limit($candidate->leader_name, 13) }}</span>
                                    </div>

                                    @if($candidate->treasurer_name)
                                        <div class="bg-black/60 backdrop-blur-sm rounded-lg p-1">
                                            <span class="text-[8px] uppercase tracking-wider text-amber-300 block font-bold">Bendahara</span>
                                            <span class="text-[10px] font-black truncate block leading-tight">{{ Str::limit($candidate->treasurer_name, 12) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Body Slogan & Action --}}
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <p class="text-xs text-slate-600 line-clamp-4 italic leading-relaxed">
                                &ldquo;{{ $candidate->quote_slogan ?? 'Mewujudkan kepengurusan OSIS yang aktif, solutif, dan berprestasi.' }}&rdquo;
                            </p>

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                                <button type="button" 
                                        onclick="openDetailModal({{ json_encode($candidate) }})"
                                        class="flex-1 py-2.5 px-3 rounded-xl border-2 border-slate-900 text-slate-800 text-xs font-bold hover:bg-slate-100 transition inline-flex items-center justify-center gap-1.5">
                                    <i class="fas fa-eye text-blue-600"></i>
                                    <span>Visi & Misi</span>
                                </button>

                                <a href="{{ route('osis.booth') }}" 
                                   class="flex-1 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow border-2 border-slate-900 transition inline-flex items-center justify-center gap-1.5">
                                    <i class="fas fa-vote-yea"></i>
                                    <span>Coblos</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 3. CALLOUT CARDS (ASAS & TATA CARA PEMILIHAN) --}}
        <section class="bg-white rounded-3xl p-8 border-2 border-slate-900 shadow-xl grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 text-xl flex items-center justify-center flex-shrink-0 border-2 border-emerald-300">
                    <i class="fas fa-mobile-screen"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900">E-Voting Modern & Fleksibel</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Siswa dan guru dapat memilih secara praktis dari HP smartphone masing-masing, maupun komputer lab sekolah.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 text-xl flex items-center justify-center flex-shrink-0 border-2 border-blue-300">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900">100% Rahasia & Aman</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Kotak suara digital tidak menyimpan identitas akun pemilih. Pilihan Anda terlindungi secara penuh dari intervensi.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 text-xl flex items-center justify-center flex-shrink-0 border-2 border-purple-300">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900">Rekapitulasi Akurat</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Perhitungan suara dihitung secara otomatis dan transparan begitu waktu pemilihan resmi berakhir.
                    </p>
                </div>
            </div>
        </section>
    </main>

    {{-- FOOTER PUBLIK --}}
    <footer class="bg-slate-900 text-white border-t-2 border-slate-900 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-400 space-y-2">
            <p class="font-bold text-slate-200">
                &copy; {{ date('Y') }} Komisi Pemilihan OSIS &mdash; Yayasan Perguruan Pemda Nias
            </p>
            <p>
                Sistem E-Voting Terintegrasi PembdaHUB Education Management System
            </p>
        </div>
    </footer>

    {{-- MODAL DETAIL PASLON PUBLIK --}}
    <div id="modal-public-detail" class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in-95 duration-200">
            <div class="sticky top-0 bg-white p-5 border-b border-slate-100 flex items-center justify-between z-10">
                <div class="flex items-center gap-3">
                    <span id="m-badge-number" class="w-9 h-9 rounded-xl bg-slate-900 text-white font-black text-base flex items-center justify-center shadow">1</span>
                    <div>
                        <h3 id="m-candidate-title" class="text-base font-black text-slate-900">PASLON NOMOR 01</h3>
                        <p id="m-candidate-names" class="text-xs text-slate-500 font-medium"></p>
                    </div>
                </div>
                <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <div class="rounded-2xl overflow-hidden border-2 border-slate-900 shadow-md bg-slate-100">
                    <img id="m-poster-img" src="" alt="Poster" class="w-full h-auto object-contain max-h-96 mx-auto">
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1.5">
                        <i class="fas fa-bullseye text-blue-600"></i> VISI UTAMA
                    </h4>
                    <div class="bg-blue-50/70 p-4 rounded-2xl border border-blue-200 text-xs md:text-sm text-slate-800 font-medium leading-relaxed" id="m-vision"></div>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-list-check text-emerald-600"></i> MISI
                    </h4>
                    <ul class="space-y-2 text-xs text-slate-700" id="m-mission-list"></ul>
                </div>

                <div id="m-programs-container">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-rocket text-purple-600"></i> PROGRAM KERJA UNGGULAN
                    </h4>
                    <ul class="space-y-2 text-xs text-slate-700" id="m-programs-list"></ul>
                </div>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-3xl flex items-center justify-between">
                <button type="button" onclick="closeDetailModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-100 transition">
                    Tutup
                </button>
                <a href="{{ route('osis.booth') }}" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md border-2 border-slate-900 transition">
                    <i class="fas fa-vote-yea mr-1"></i> Buka Bilik Suara
                </a>
            </div>
        </div>
    </div>

    <script>
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

        function openDetailModal(c) {
            document.getElementById('m-badge-number').innerText = c.candidate_number;
            document.getElementById('m-candidate-title').innerText = 'PASLON NOMOR ' + c.candidate_number;
            
            let names = c.leader_name;
            if (c.secretary_name) names += ' (Ketua) & ' + c.secretary_name + ' (Sekretaris)';
            document.getElementById('m-candidate-names').innerText = names;

            const posterImg = document.getElementById('m-poster-img');
            posterImg.src = c.poster_flyer ? (c.poster_flyer.startsWith('http') ? c.poster_flyer : '/' + c.poster_flyer) : (c.photo ? '/' + c.photo : 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&auto=format&fit=crop&q=80');

            document.getElementById('m-vision').innerText = c.vision || c.quote_slogan || '-';

            const missionList = document.getElementById('m-mission-list');
            missionList.innerHTML = '';
            if (Array.isArray(c.mission) && c.mission.length > 0) {
                c.mission.forEach((m, idx) => {
                    const li = document.createElement('li');
                    li.className = 'flex items-start gap-2';
                    li.innerHTML = `<span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] flex items-center justify-center flex-shrink-0 mt-0.5">${idx+1}</span> <span>${m}</span>`;
                    missionList.appendChild(li);
                });
            }

            const progList = document.getElementById('m-programs-list');
            progList.innerHTML = '';
            if (Array.isArray(c.work_programs) && c.work_programs.length > 0) {
                c.work_programs.forEach((p) => {
                    const li = document.createElement('li');
                    li.className = 'flex items-start gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100';
                    li.innerHTML = `<i class="fas fa-star text-amber-500 mt-1 flex-shrink-0"></i> <span>${p}</span>`;
                    progList.appendChild(li);
                });
                document.getElementById('m-programs-container').classList.remove('hidden');
            } else {
                document.getElementById('m-programs-container').classList.add('hidden');
            }

            document.getElementById('modal-public-detail').classList.remove('hidden');
        }

        function closeDetailModal() {
            document.getElementById('modal-public-detail').classList.add('hidden');
        }
    </script>
</body>
</html>
