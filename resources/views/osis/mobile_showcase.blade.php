<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pemilihan OSIS - Mobile PembdaHUB</title>
    
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
<body class="bg-slate-100 text-slate-800 font-sans pb-24 select-none">

    {{-- TOP APP BAR MOBILE --}}
    <header class="sticky top-0 z-40 bg-slate-900 text-white px-4 py-3.5 flex items-center justify-between shadow-md border-b-2 border-slate-950">
        <div class="flex items-center gap-3">
            <a href="{{ url('/m') }}" class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-slate-200 active:scale-95 transition">
                <i class="fas fa-chevron-left text-sm"></i>
            </a>
            <div>
                <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider block leading-none">E-Voting Mobile</span>
                <h1 class="text-sm font-black text-white truncate max-w-[200px]">{{ $election->title ?? 'Pemilihan OSIS' }}</h1>
            </div>
        </div>

        <button type="button" onclick="shareCampaign()" class="w-9 h-9 rounded-xl bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 flex items-center justify-center active:scale-95 transition">
            <i class="fas fa-share-nodes text-sm"></i>
        </button>
    </header>

    {{-- KONTEN UTAMA MOBILE --}}
    <main class="p-4 space-y-4 max-w-md mx-auto">

        {{-- BANNER HERO MOBILE --}}
        <div class="rounded-3xl p-5 border-2 border-slate-900 shadow-xl relative overflow-hidden"
             style="background: linear-gradient(135deg, #090d16 0%, #1e3a8a 60%, #064e3b 100%); color: #ffffff;">
            
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-[10px] font-black tracking-wider uppercase mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                PERIODE {{ $election->period ?? '2026/2027' }}
            </div>

            <h2 class="text-xl font-black uppercase tracking-tight leading-tight">
                {{ $election->title ?? 'Pemilihan OSIS' }}
            </h2>
            <p class="text-slate-200 text-xs mt-1.5 italic font-medium">
                &ldquo;{{ $election->theme_tagline ?? 'Bersama Membangun Masa Depan yang Lebih Baik' }}&rdquo;
            </p>

            {{-- Countdown Mini Mobile --}}
            <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs">
                <span class="text-[11px] text-slate-300 flex items-center gap-1">
                    <i class="fas fa-hourglass-half text-amber-400"></i> Sisa Waktu:
                </span>
                <div class="flex items-center gap-1 font-mono font-black text-emerald-400" id="m-cd">
                    <span id="m-cd-days">0</span>h
                    <span id="m-cd-hours">00</span>j
                    <span id="m-cd-minutes">00</span>m
                    <span id="m-cd-seconds">00</span>d
                </div>
            </div>
        </div>

        {{-- INFO CHIP --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-3 flex items-center gap-3 text-xs text-blue-900 shadow-xs">
            <i class="fas fa-fingerprint text-blue-600 text-lg flex-shrink-0"></i>
            <div>
                <span class="font-bold block">1 Civitas = 1 Suara Sah</span>
                <span class="text-[11px] text-blue-700">Pilihan bersifat rahasia dan tercatat aman di sistem PembdaHUB.</span>
            </div>
        </div>

        {{-- SECTION KANDIDAT PASLON --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fas fa-users text-emerald-600"></i> Paslon Kandidat
                </h3>
                <span class="text-[11px] font-bold text-slate-500">{{ $election->candidates->count() }} Paslon</span>
            </div>

            @foreach($election->candidates as $candidate)
                @php
                    $colors = [
                        1 => 'border-rose-600 bg-rose-600 text-rose-600',
                        2 => 'border-blue-600 bg-blue-600 text-blue-600',
                        3 => 'border-amber-500 bg-amber-500 text-amber-500',
                        4 => 'border-emerald-600 bg-emerald-600 text-emerald-600',
                    ];
                    $badgeBg = [
                        1 => 'bg-rose-600',
                        2 => 'bg-blue-600',
                        3 => 'bg-amber-500',
                        4 => 'bg-emerald-600',
                    ][$candidate->candidate_number] ?? 'bg-slate-800';
                @endphp

                <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-md overflow-hidden transition active:scale-[0.99]">
                    {{-- Header Paslon --}}
                    <div class="p-3 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg {{ $badgeBg }} text-white font-black text-xs flex items-center justify-center shadow">
                                {{ $candidate->candidate_number }}
                            </span>
                            <span class="text-xs font-black text-slate-900">PASLON NOMOR 0{{ $candidate->candidate_number }}</span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Calon Pengurus</span>
                    </div>

                    {{-- Image & KSB --}}
                    <div class="relative bg-slate-200" style="aspect-ratio: 16/10;">
                        <img src="{{ $candidate->photo_url }}" 
                             alt="Paslon {{ $candidate->candidate_number }}" 
                             class="w-full h-full object-cover"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&auto=format&fit=crop&q=80';">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-2.5 text-white">
                            <div class="grid grid-cols-3 gap-1 text-center">
                                @if($candidate->secretary_name)
                                    <div class="bg-black/60 rounded p-1">
                                        <span class="text-[7px] uppercase text-amber-300 font-bold block">Sekretaris</span>
                                        <span class="text-[9px] font-black truncate block leading-tight">{{ $candidate->secretary_name }}</span>
                                    </div>
                                @endif
                                <div class="bg-black/80 rounded p-1 border border-white/30 transform scale-105 shadow">
                                    <span class="text-[7px] uppercase text-emerald-300 font-black block">KETUA</span>
                                    <span class="text-[9px] font-black truncate block leading-tight">{{ $candidate->leader_name }}</span>
                                </div>
                                @if($candidate->treasurer_name)
                                    <div class="bg-black/60 rounded p-1">
                                        <span class="text-[7px] uppercase text-amber-300 font-bold block">Bendahara</span>
                                        <span class="text-[9px] font-black truncate block leading-tight">{{ $candidate->treasurer_name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Slogan & Button --}}
                    <div class="p-3.5 space-y-3">
                        <p class="text-xs text-slate-600 italic line-clamp-3 leading-relaxed">
                            &ldquo;{{ $candidate->quote_slogan ?? 'Membangun karakter siswa yang tangguh dan kreatif.' }}&rdquo;
                        </p>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button" 
                                    onclick="openMobileDetail({{ json_encode($candidate) }})"
                                    class="py-2.5 px-3 rounded-xl border-2 border-slate-900 text-slate-800 text-xs font-bold hover:bg-slate-100 transition flex items-center justify-center gap-1">
                                <i class="fas fa-eye text-blue-600"></i> Detail Visi
                            </button>

                            <a href="{{ route('osis.booth') }}" 
                               class="py-2.5 px-3 rounded-xl bg-emerald-600 active:bg-emerald-700 text-white text-xs font-black shadow border-2 border-slate-900 transition flex items-center justify-center gap-1">
                                <i class="fas fa-vote-yea"></i> Pilih Ini
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

    {{-- STICKY BOTTOM ACTION BAR (SELALU TERLIHAT) --}}
    <div class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md p-3 border-t-2 border-slate-900 shadow-2xl z-40 max-w-md mx-auto">
        @auth
            <a href="{{ route('osis.booth') }}" class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 active:bg-emerald-700 text-white font-black text-sm shadow-lg border-2 border-slate-900 flex items-center justify-center gap-2">
                <i class="fas fa-box-archive text-emerald-200"></i>
                <span>Masuk ke Bilik Suara Digital</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="w-full py-3.5 px-4 rounded-2xl bg-slate-900 active:bg-slate-800 text-white font-black text-sm shadow-lg border-2 border-slate-900 flex items-center justify-center gap-2">
                <i class="fas fa-lock text-emerald-400"></i>
                <span>Login Civitas untuk Memilih</span>
            </a>
        @endauth
    </div>

    {{-- BOTTOM SHEET DETAIL PASLON (SLIDE UP) --}}
    <div id="bottom-sheet" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-xs flex flex-col justify-end">
        <div class="bg-white rounded-t-3xl border-t-2 border-x-2 border-slate-900 max-h-[85vh] flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-200 max-w-md mx-auto w-full">
            
            {{-- Sheet Header --}}
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2">
                    <span id="bs-number" class="w-7 h-7 rounded-lg bg-slate-900 text-white font-black text-xs flex items-center justify-center">1</span>
                    <h3 id="bs-title" class="text-sm font-black text-slate-900">PASLON 01</h3>
                </div>
                <button type="button" onclick="closeMobileDetail()" class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 active:scale-95">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            {{-- Sheet Body --}}
            <div class="p-4 space-y-4 overflow-y-auto flex-1">
                <div class="rounded-2xl overflow-hidden border border-slate-900 shadow-sm bg-slate-100">
                    <img id="bs-poster" src="" alt="Poster" class="w-full h-auto object-contain max-h-72 mx-auto">
                </div>

                <div>
                    <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider mb-1">Visi Utama</span>
                    <p id="bs-vision" class="text-xs text-slate-800 bg-blue-50 p-3 rounded-xl border border-blue-200 leading-relaxed font-medium"></p>
                </div>

                <div>
                    <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider mb-1.5">Misi Paslon</span>
                    <ul id="bs-missions" class="space-y-1.5 text-xs text-slate-700"></ul>
                </div>

                <div id="bs-prog-container">
                    <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider mb-1.5">Program Unggulan</span>
                    <ul id="bs-programs" class="space-y-1.5 text-xs text-slate-700"></ul>
                </div>
            </div>

            {{-- Sheet Footer --}}
            <div class="p-3 border-t border-slate-100 bg-white">
                <a href="{{ route('osis.booth') }}" class="w-full py-3 px-4 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center gap-2 shadow border-2 border-slate-900">
                    <i class="fas fa-vote-yea"></i> Coblos Paslon Ini
                </a>
            </div>
        </div>
    </div>

    <script>
        const endTimeString = "{{ $election->end_time ? $election->end_time->toIso8601String() : '' }}";
        function updateMobileCountdown() {
            if (!endTimeString) return;
            const end = new Date(endTimeString).getTime();
            const now = new Date().getTime();
            const distance = end - now;

            if (distance < 0) {
                document.getElementById('m-cd').innerHTML = '<span class="text-rose-400 font-bold">Waktu Habis</span>';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('m-cd-days').innerText = days;
            document.getElementById('m-cd-hours').innerText = String(hours).padStart(2, '0');
            document.getElementById('m-cd-minutes').innerText = String(minutes).padStart(2, '0');
            document.getElementById('m-cd-seconds').innerText = String(seconds).padStart(2, '0');
        }
        setInterval(updateMobileCountdown, 1000);
        updateMobileCountdown();

        function openMobileDetail(c) {
            document.getElementById('bs-number').innerText = c.candidate_number;
            document.getElementById('bs-title').innerText = 'PASLON NOMOR 0' + c.candidate_number + ' - ' + c.leader_name;
            
            const posterImg = document.getElementById('bs-poster');
            posterImg.src = c.poster_flyer ? (c.poster_flyer.startsWith('http') ? c.poster_flyer : '/' + c.poster_flyer) : (c.photo ? '/' + c.photo : 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&auto=format&fit=crop&q=80');

            document.getElementById('bs-vision').innerText = c.vision || c.quote_slogan || '-';

            const mList = document.getElementById('bs-missions');
            mList.innerHTML = '';
            if (Array.isArray(c.mission) && c.mission.length > 0) {
                c.mission.forEach((m, idx) => {
                    const li = document.createElement('li');
                    li.className = 'flex items-start gap-2';
                    li.innerHTML = `<span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 text-[9px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">${idx+1}</span> <span>${m}</span>`;
                    mList.appendChild(li);
                });
            }

            const pList = document.getElementById('bs-programs');
            pList.innerHTML = '';
            if (Array.isArray(c.work_programs) && c.work_programs.length > 0) {
                c.work_programs.forEach((p) => {
                    const li = document.createElement('li');
                    li.className = 'flex items-start gap-2 bg-slate-50 p-2 rounded-lg border border-slate-100';
                    li.innerHTML = `<i class="fas fa-star text-amber-500 mt-0.5 text-[10px] flex-shrink-0"></i> <span>${p}</span>`;
                    pList.appendChild(li);
                });
                document.getElementById('bs-prog-container').classList.remove('hidden');
            } else {
                document.getElementById('bs-prog-container').classList.add('hidden');
            }

            document.getElementById('bottom-sheet').classList.remove('hidden');
        }

        function closeMobileDetail() {
            document.getElementById('bottom-sheet').classList.add('hidden');
        }

        function shareCampaign() {
            const url = window.location.href;
            const text = 'Ayo kenali dan pilih calon pemimpin OSIS SMK Swasta Pemda Nias periode 2026/2027 melalui E-Voting PembdaHUB!';
            if (navigator.share) {
                navigator.share({
                    title: 'Pemilihan OSIS PembdaHUB',
                    text: text,
                    url: url
                }).catch(() => {});
            } else {
                window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(text + ' ' + url)}`, '_blank');
            }
        }
    </script>
</body>
</html>
