<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Kontrol & Simulator Running Teks - Perguruan PEMBDA</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&family=VT323&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b0f19;
            color: #f1f5f9;
        }

        /* Gaya Font Dot Matrix LED */
        .font-led {
            font-family: 'VT323', monospace;
            letter-spacing: 2px;
        }

        .font-tech {
            font-family: 'Chakra Petch', sans-serif;
        }

        /* Efek Bingkai Panel LED P5 */
        .led-panel-housing {
            background: #020408;
            border: 4px solid #1e293b;
            box-shadow: 0 0 35px rgba(0, 0, 0, 0.9), inset 0 0 15px rgba(0, 0, 0, 0.8);
            position: relative;
        }

        /* Grid pixel P5 halus */
        .led-grid-pattern {
            background-image: 
                radial-gradient(circle, rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 5px 5px;
        }

        /* Efek teks menyala (Amber / Gold LED) */
        .led-glow-amber {
            color: #fbbf24;
            text-shadow: 0 0 6px rgba(251, 191, 36, 0.8), 0 0 14px rgba(245, 158, 11, 0.5);
        }

        /* Efek teks menyala (Red Alert LED) */
        .led-glow-red {
            color: #f87171;
            text-shadow: 0 0 6px rgba(248, 113, 113, 0.9), 0 0 16px rgba(239, 68, 68, 0.6);
        }

        /* Efek teks menyala (Green Success LED) */
        .led-glow-green {
            color: #4ade80;
            text-shadow: 0 0 6px rgba(74, 222, 128, 0.9), 0 0 16px rgba(34, 197, 94, 0.6);
        }

        /* Efek teks menyala (Cyan Info LED) */
        .led-glow-cyan {
            color: #38bdf8;
            text-shadow: 0 0 6px rgba(56, 189, 248, 0.9), 0 0 16px rgba(14, 165, 233, 0.6);
        }

        /* Animasi Marquee Ticker */
        .marquee-wrapper {
            overflow: hidden;
            white-space: nowrap;
            position: relative;
            display: flex;
            align-items: center;
        }

        .marquee-content {
            display: inline-block;
            white-space: nowrap;
            animation: scroll-left 45s linear infinite;
        }

        @keyframes scroll-left {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* Scrollbar kustom */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
    </style>
</head>
<body class="min-h-screen pb-16 antialiased selection:bg-rose-500 selection:text-white">

    <!-- TOP HEADER -->
    <header class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-600 via-amber-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-rose-500/20">
                    <i class="fas fa-satellite-dish text-white text-lg animate-pulse"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                            PUSAT KONTROL RUNNING TEKS
                            <span class="text-[10px] font-tech uppercase px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">ONLINE LAN</span>
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400">4 Panel P5 RGB (256 × 32 Pixel) • Huidu HD-WF2 • PembdaLINK</p>
                </div>
            </div>

            <!-- Jam Server & Tombol Navigasi -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700/60 text-xs font-mono text-slate-300">
                    <i class="far fa-clock text-amber-400"></i>
                    <span id="liveClock">--:--:-- WIB</span>
                </div>

                @if($isUnlocked)
                    <span class="px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-1.5">
                        <i class="fas fa-shield-alt"></i> Mode Admin Terbuka
                    </span>
                @else
                    <button onclick="openUnlockModal()" class="px-3.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-md shadow-rose-600/30">
                        <i class="fas fa-key"></i> Buka Kunci Kontrol
                    </button>
                @endif

                <a href="{{ url('/') }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-semibold transition flex items-center gap-1.5">
                    <i class="fas fa-home"></i> Beranda
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 space-y-6">

        <!-- =================================================================== -->
        <!-- SECTION 1: SIMULATOR VIRTUAL LED MATRIX (256 x 32 Pixel) -->
        <!-- =================================================================== -->
        <section class="bg-slate-900/60 rounded-3xl border border-slate-800 p-5 lg:p-6 shadow-2xl relative overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-400 animate-ping"></span>
                    <h2 class="text-sm font-tech font-bold text-slate-200 tracking-wider uppercase">
                        VIRTUAL LED DISPLAY (RASIO FISIK 4 PANEL P5: 256 × 32 PIXEL)
                    </h2>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span id="modeBadge" class="px-2.5 py-1 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/40 font-mono font-bold">
                        LOOPING 7-IN-1
                    </span>
                    <span class="text-slate-500 font-mono">256 × 32 PX (1.28m × 0.16m)</span>
                </div>
            </div>

            <!-- FISIK DISPLAY SIMULATOR -->
            <div class="led-panel-housing rounded-2xl p-4 sm:p-6 border-slate-800">
                <!-- Bezel Panel P5 Lines -->
                <div class="relative w-full min-h-[105px] sm:min-h-[135px] py-2 bg-black rounded-xl overflow-hidden flex items-center justify-center border border-slate-900 led-grid-pattern px-3 sm:px-6">
                    
                    <!-- Sisi Grid Split Line (Menandai Batas 4 Panel Fisik 64px) -->
                    <div class="absolute inset-0 grid grid-cols-4 pointer-events-none opacity-20">
                        <div class="border-r border-cyan-500"></div>
                        <div class="border-r border-cyan-500"></div>
                        <div class="border-r border-cyan-500"></div>
                        <div></div>
                    </div>

                    <!-- Container Teks Tampilan -->
                    <div id="ledDisplayScreen" class="w-full text-center relative z-10 flex items-center justify-center">
                        <!-- 1. Normal Loop Marquee -->
                        <div id="ledNormalContainer" class="marquee-wrapper w-full">
                            <span id="ledMarqueeText" class="marquee-content font-led text-2xl sm:text-4xl led-glow-amber">
                                [ SEDANG MEMUAT DATA FEED PEMBDAHUB... ]
                            </span>
                        </div>
                        
                        <!-- 2. Selaan Absensi Interupsi Real-Time (3 Baris Rapi) -->
                        <div id="ledInterruptContainer" class="hidden w-full flex flex-col items-center justify-center text-center space-y-0.5 sm:space-y-1 py-1">
                            <!-- Baris 1: Sapaan Ramah -->
                            <div id="ledInterruptGreeting" class="font-led text-sm sm:text-lg text-emerald-400 font-semibold tracking-wider uppercase leading-none drop-shadow-[0_0_8px_rgba(52,211,153,0.85)]">
                                Selamat Pagi
                            </div>
                            <!-- Baris 2: Nama Siswa / Guru (Huruf Lebih Besar, Menonjol & Amber Glow) -->
                            <div id="ledInterruptName" class="font-led text-3xl sm:text-5xl md:text-6xl text-amber-300 font-bold tracking-wider uppercase leading-none my-0.5 drop-shadow-[0_0_20px_rgba(251,191,36,0.95)] truncate max-w-full px-2">
                                Yulianus Zega
                            </div>
                            <!-- Baris 3: Jam, Keterangan, Kelas/Rombel, dan Unit Sekolah -->
                            <div id="ledInterruptMeta" class="font-led text-xs sm:text-base text-cyan-300 tracking-wide font-medium leading-none drop-shadow-[0_0_8px_rgba(103,232,249,0.85)]">
                                [07:10/Terlambat] | X - Budi Utomo | SMAS Pembda 1
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Informasi Panel -->
                <div class="mt-3 flex flex-wrap items-center justify-between text-[11px] text-slate-400 font-mono">
                    <div class="flex items-center gap-3">
                        <span><i class="fas fa-microchip text-rose-500"></i> HD-WF2 (ESP32-S3)</span>
                        <span><i class="fas fa-wifi text-emerald-500"></i> Wi-Fi: PembdaLINK</span>
                        <span><i class="fas fa-network-wired text-blue-400"></i> IP: <strong id="lblControllerIp" class="text-white">{{ $controllerIp }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>FPS: <strong class="text-amber-400">60</strong></span>
                        <span>•</span>
                        <span>Waktu Respons LAN: <strong class="text-emerald-400">&lt; 15ms</strong></span>
                    </div>
                </div>
            </div>

            <!-- INTERACTIVE ACTION BAR (UJI COBA LANGSUNG) -->
            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <button onclick="triggerSimulateTap('Yulianus Zega', 'X - Budi Utomo', 'MASUK', 'Terlambat', 'SMAS Pembda 1', '07:10')" 
                        class="p-3 rounded-xl bg-slate-800/80 hover:bg-amber-600/20 border border-slate-700 hover:border-amber-500/50 text-left transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-amber-400 group-hover:text-amber-300">Tes Siswa Terlambat</span>
                        <i class="fas fa-user-clock text-xs text-amber-400"></i>
                    </div>
                    <p class="text-[11px] text-slate-400 truncate">Yulianus Zega [07:10/Terlambat]</p>
                </button>

                <button onclick="triggerSimulateTap('Budi Santoso', 'XI TE 1', 'MASUK', 'Hadir', 'SMKS Pembda', '06:55')" 
                        class="p-3 rounded-xl bg-slate-800/80 hover:bg-emerald-600/20 border border-slate-700 hover:border-emerald-500/50 text-left transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-emerald-400 group-hover:text-emerald-300">Tes Siswa Hadir</span>
                        <i class="fas fa-sign-in-alt text-xs text-emerald-400"></i>
                    </div>
                    <p class="text-[11px] text-slate-400 truncate">Budi Santoso [06:55/Hadir]</p>
                </button>

                <button onclick="triggerSimulateTap('Siti Rahma', 'XII DPIB', 'PULANG', 'Pulang', 'SMKS Pembda', '14:05')" 
                        class="p-3 rounded-xl bg-slate-800/80 hover:bg-cyan-600/20 border border-slate-700 hover:border-cyan-500/50 text-left transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-cyan-400 group-hover:text-cyan-300">Tes Siswa Pulang</span>
                        <i class="fas fa-sign-out-alt text-xs text-cyan-400"></i>
                    </div>
                    <p class="text-[11px] text-slate-400 truncate">Siti Rahma [14:05/Pulang]</p>
                </button>

                <button onclick="triggerSimulateTap('Drs. Y. Zega', 'Guru Produktif TE', 'MASUK', 'Hadir', 'SMKS Pembda', '07:05')" 
                        class="p-3 rounded-xl bg-slate-800/80 hover:bg-indigo-600/20 border border-slate-700 hover:border-indigo-500/50 text-left transition group">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-indigo-400 group-hover:text-indigo-300">Tes Guru / Pegawai</span>
                        <i class="fas fa-chalkboard-teacher text-xs text-indigo-400"></i>
                    </div>
                    <p class="text-[11px] text-slate-400 truncate">Drs. Y. Zega [07:05/Hadir]</p>
                </button>
            </div>
        </section>

        <!-- =================================================================== -->
        <!-- SECTION 2: 7 KANAL INFORMASI KONTINYU (REAL DATABASE STATS) -->
        <!-- =================================================================== -->
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fas fa-layer-group text-rose-500"></i> 7 Kanal Informasi Aktif di Database PembdaHUB
                    </h2>
                    <p class="text-xs text-slate-400">Semua data berikut ditarik otomatis dan berputar bergantian di layar running text.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Berita & Pengumuman -->
                <div class="bg-slate-900/50 rounded-2xl border border-slate-800 p-4 relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-purple-400 flex items-center gap-1.5">
                                <i class="fas fa-bullhorn"></i> 1. Pengumuman
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                {{ $stats['total_news'] }} Data
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 line-clamp-2">
                            Jadwal Asesmen, info pembayaran SPP, libur nasional, dan edaran dinas.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Tabel: <code>news</code></span>
                        <a href="{{ url('/admin/news') }}" target="_blank" class="text-purple-400 hover:text-purple-300 font-semibold">Kelola &rarr;</a>
                    </div>
                </div>

                <!-- 2. Prestasi Siswa -->
                <div class="bg-slate-900/50 rounded-2xl border border-slate-800 p-4 relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-amber-400 flex items-center gap-1.5">
                                <i class="fas fa-trophy"></i> 2. Prestasi Siswa
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                {{ $stats['total_achievements'] }} Juara
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 line-clamp-2">
                            Juara lomba LKS, O2SN, FLS2N, olimpiade sains, dan rekam penghargaan kesiswaan.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Tabel: <code>student_achievements</code></span>
                        <a href="{{ url('/admin/counseling') }}" target="_blank" class="text-amber-400 hover:text-amber-300 font-semibold">Lihat &rarr;</a>
                    </div>
                </div>

                <!-- 3. Agenda Hari Ini -->
                <div class="bg-slate-900/50 rounded-2xl border border-slate-800 p-4 relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-blue-400 flex items-center gap-1.5">
                                <i class="fas fa-calendar-alt"></i> 3. Agenda Hari Ini
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                {{ $stats['total_agenda_today'] }} Agenda
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 line-clamp-2">
                            Kalender pendidikan, rapat guru, ekstrakurikuler, dan agenda supervisi sekolah.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Tabel: <code>educational_calendars</code></span>
                        <a href="{{ url('/admin/calendar') }}" target="_blank" class="text-blue-400 hover:text-blue-300 font-semibold">Kalender &rarr;</a>
                    </div>
                </div>

                <!-- 4. Pojok Literasi & Motivasi -->
                <div class="bg-slate-900/50 rounded-2xl border border-slate-800 p-4 relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-rose-400 flex items-center gap-1.5">
                                <i class="fas fa-lightbulb"></i> 4. Motivasi & Literasi
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                Harian
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 line-clamp-2">
                            Quotes karakter, kata mutiara Ki Hajar Dewantara, dan pesan pembina yayasan.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Kategori: <code>motivasi</code></span>
                        <span class="text-slate-400">Auto Rotasi</span>
                    </div>
                </div>

                <!-- 5. Penelitian SMA -->
                <div class="bg-slate-900/50 rounded-2xl border border-slate-800 p-4 relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-teal-400 flex items-center gap-1.5">
                                <i class="fas fa-microscope"></i> 5. Penelitian SMA
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                {{ $stats['total_research_sma'] }} Judul
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 line-clamp-2">
                            Tugas akhir dan karya tulis ilmiah inovatif siswa kelas XII SMA Pembda.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Tipe: <code>penelitian_ilmiah</code></span>
                        <span class="text-slate-400">Unit SMA</span>
                    </div>
                </div>

                <!-- 6. Project Kejuruan SMK -->
                <div class="bg-slate-900/50 rounded-2xl border border-slate-800 p-4 relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-amber-500 flex items-center gap-1.5">
                                <i class="fas fa-tools"></i> 6. Project SMK
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                {{ $stats['total_project_smk'] }} Karya
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 line-clamp-2">
                            Produk kreatif & kewirausahaan (TE, DPIB, TKR, TSM, TKJ) siswa SMK Pembda.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Tipe: <code>project_akhir</code></span>
                        <span class="text-slate-400">Unit SMK</span>
                    </div>
                </div>

                <!-- 7. Pesan Alumni -->
                <div class="bg-slate-900/50 rounded-2xl border border-slate-800 p-4 relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-indigo-400 flex items-center gap-1.5">
                                <i class="fas fa-user-graduate"></i> 7. Pesan Alumni
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                {{ $stats['total_alumni_msg'] }} Pesan
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 line-clamp-2">
                            Testimoni, jejak karir dunia kerja & universitas, dan pesan inspiratif alumni.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">Tabel: <code>alumni_directories</code></span>
                        <a href="{{ url('/alumni/direktori') }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 font-semibold">Direktori &rarr;</a>
                    </div>
                </div>

                <!-- 8. Interupsi Absen RFID -->
                <div class="bg-emerald-950/30 rounded-2xl border border-emerald-500/30 p-4 relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                                <i class="fas fa-bolt"></i> ⚡ Selaan Absensi
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                Real-Time 10s
                            </span>
                        </div>
                        <p class="text-xs text-emerald-200/80 line-clamp-2">
                            Setiap siswa/guru tap kartu di gerbang, nama langsung muncul menyela teks berita!
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-emerald-500/20 flex items-center justify-between text-[11px]">
                        <span class="text-emerald-400/70">Endpoint: <code>rfid-scan</code></span>
                        <span class="text-emerald-300 font-mono font-bold">Prioritas #1</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================================================================== -->
        <!-- SECTION 3: PENGATURAN CONTROLLER & SCRIPT PENGIRIM KE HD-WF2 -->
        <!-- =================================================================== -->
        <section class="bg-slate-900/60 rounded-3xl border border-slate-800 p-5 lg:p-6 shadow-xl space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fas fa-cogs text-amber-400"></i> Pengaturan Jaringan & Akses Endpoint API
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Kolom 1: Konfigurasi IP Controller -->
                <div class="space-y-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800/80">
                    <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider">
                        Alamat IP Controller Huidu HD-WF2 di Wi-Fi PembdaLINK
                    </h3>
                    <div class="flex items-center gap-2">
                        <input type="text" id="inputControllerIp" value="{{ $controllerIp }}" 
                               placeholder="Contoh: 50.35.89.150 atau 192.168.1.150"
                               class="flex-1 px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-sm font-mono text-white focus:outline-none focus:border-amber-500">
                        <button onclick="saveControllerIp()" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-slate-950 text-xs font-bold transition">
                            Simpan IP
                        </button>
                    </div>
                    <p class="text-xs text-slate-500">
                        Masukkan IP statis yang sudah Anda set di aplikasi HD2020 pada controller HD-WF2.
                    </p>

                    <div class="pt-2 border-t border-slate-800">
                        <h4 class="text-xs font-semibold text-slate-300 mb-2">Endpoint Live Feed Resmi:</h4>
                        <ul class="space-y-1.5 text-xs font-mono">
                            <li class="flex items-center justify-between p-2 rounded-lg bg-slate-900 border border-slate-800">
                                <span class="text-emerald-400 truncate mr-2">GET /api/running-text/live/raw</span>
                                <a href="{{ url('/api/running-text/live/raw') }}" target="_blank" class="text-slate-400 hover:text-white"><i class="fas fa-external-link-alt"></i></a>
                            </li>
                            <li class="flex items-center justify-between p-2 rounded-lg bg-slate-900 border border-slate-800">
                                <span class="text-blue-400 truncate mr-2">GET /api/running-text/live</span>
                                <a href="{{ url('/api/running-text/live') }}" target="_blank" class="text-slate-400 hover:text-white"><i class="fas fa-external-link-alt"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Kolom 2: Panduan Script Pengirim Otomatis (Bridge LAN) -->
                <div class="space-y-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800/80">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider">
                            Script Bridge Pengirim (Jalankan di Server / PC Sekolah)
                        </h3>
                        <button onclick="copyScriptCode()" class="text-xs text-amber-400 hover:text-amber-300 font-semibold flex items-center gap-1">
                            <i class="far fa-copy"></i> Salin Script
                        </button>
                    </div>
                    <div class="relative">
                        <pre id="scriptBridgeCode" class="p-3.5 rounded-xl bg-black/80 border border-slate-800 text-[11px] font-mono text-emerald-400 overflow-x-auto"># Script Pengambil Data Otomatis PembdaHUB -> HD2020 Txt
$apiUrl = "http://50.35.89.10/api/running-text/live/raw"
$targetFile = "C:\pembdahub_text.txt"
while ($true) {
    try {
        $text = (Invoke-RestMethod -Uri $apiUrl -TimeoutSec 3)
        [System.IO.File]::WriteAllText($targetFile, $text)
    } catch {}
    Start-Sleep -Seconds 2
}</pre>
                    </div>
                    <p class="text-xs text-slate-500">
                        Script di atas otomatis mendownload teks dari PembdaHUB setiap 2 detik ke file <code>C:\pembdahub_text.txt</code> yang dibaca oleh HD2020.
                    </p>
                </div>
            </div>
        </section>

    </main>

    <!-- =================================================================== -->
    <!-- MODAL POPUP: KIRIM BREAKING NEWS / PENGUMUMAN DARURAT -->
    <!-- =================================================================== -->
    <div id="modalEmergency" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fas fa-bullhorn text-rose-500"></i> Pasang Pengumuman Mendesak (Breaking News)
                </h3>
                <button onclick="closeEmergencyModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>

            <p class="text-xs text-slate-400">
                Teks ini akan langsung diletakkan pada <strong>posisi terdepan</strong> di layar running text selama durasi yang ditentukan.
            </p>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Isi Pengumuman:</label>
                    <textarea id="txtEmergency" rows="3" placeholder="Contoh: Upacara bendera dimulai tepat pukul 07.15 WIB di Lapangan Utama..."
                              class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-rose-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Durasi Tayang:</label>
                    <select id="selDuration" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-rose-500">
                        <option value="10">10 Menit</option>
                        <option value="30">30 Menit</option>
                        <option value="60">1 Jam</option>
                        <option value="120">2 Jam</option>
                        <option value="360">6 Jam</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button onclick="closeEmergencyModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">
                    Batal
                </button>
                <button onclick="sendEmergencyBroadcast()" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs font-bold text-white shadow-lg shadow-rose-600/30">
                    Tayangkan di Layar Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL POPUP: BUKA KUNCI KONTROL PANEL (PIN) -->
    <!-- =================================================================== -->
    <div id="modalUnlock" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-sm w-full p-6 space-y-4 shadow-2xl text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-500 flex items-center justify-center mx-auto text-xl">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Masukkan PIN Akses Admin</h3>
                <p class="text-xs text-slate-400 mt-1">Masukkan kode rahasia atau login sebagai admin</p>
            </div>
            <input type="password" id="inputPin" placeholder="PIN Rahasia"
                   class="w-full text-center tracking-widest text-lg font-mono px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-rose-500">
            <div class="flex gap-2">
                <button onclick="closeUnlockModal()" class="flex-1 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">
                    Batal
                </button>
                <button onclick="submitPinUnlock()" class="flex-1 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs font-bold text-white">
                    Buka Akses
                </button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT REAL-TIME ENGINE -->
    <script>
        const API_LIVE_STATUS = "{{ url('/runteks/live-status') }}";
        const API_SIMULATE_TAP = "{{ url('/runteks/simulate-tap') }}";
        const API_EMERGENCY = "{{ url('/runteks/emergency') }}";
        const API_SETTINGS = "{{ url('/runteks/settings') }}";
        const API_UNLOCK = "{{ url('/runteks/unlock') }}";

        let pollInterval = null;

        // 1. Live Clock
        function updateLiveClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('liveClock').textContent = `${h}:${m}:${s} WIB`;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // 2. Poll Status Live Simulator
        async function fetchLiveDisplayStatus() {
            try {
                const res = await fetch(API_LIVE_STATUS);
                if (!res.ok) return;
                const data = await res.json();

                const normalContainer = document.getElementById('ledNormalContainer');
                const interruptContainer = document.getElementById('ledInterruptContainer');
                const marqueeText = document.getElementById('ledMarqueeText');
                const interruptText = document.getElementById('ledInterruptText');
                const modeBadge = document.getElementById('modeBadge');

                if (data.has_interrupt && data.interrupt) {
                    // Tampilkan selaan interupsi tap absen (3 Baris: Sapaan, Nama Besar, Detail Info)
                    normalContainer.classList.add('hidden');
                    interruptContainer.classList.remove('hidden');

                    const greetingEl = document.getElementById('ledInterruptGreeting');
                    const nameEl = document.getElementById('ledInterruptName');
                    const metaEl = document.getElementById('ledInterruptMeta');

                    // Baris 1: Sapaan
                    if (data.interrupt.greeting && greetingEl) {
                        greetingEl.textContent = data.interrupt.greeting;
                    } else if (greetingEl) {
                        greetingEl.textContent = (data.interrupt.action === 'PULANG') ? 'Sampai Jumpa' : 'Selamat Pagi';
                    }

                    // Baris 2: Nama Siswa / Guru (Huruf Jumbo)
                    if (nameEl) {
                        nameEl.textContent = data.interrupt.name || 'SISWA PEMBDA';
                    }

                    // Baris 3: Jam, Keterangan, Kelas, dan Unit Sekolah
                    if (data.interrupt.meta_line && metaEl) {
                        metaEl.textContent = data.interrupt.meta_line;
                    } else if (metaEl) {
                        const t = data.interrupt.time || '--:--';
                        const s = data.interrupt.status || 'Hadir';
                        const sub = data.interrupt.subtext || '';
                        const sch = data.interrupt.school || '';
                        const parts = [`[${t}/${s}]`];
                        if (sub) parts.push(sub);
                        if (sch) parts.push(sch);
                        metaEl.textContent = parts.join(' | ');
                    }

                    modeBadge.className = "px-2.5 py-1 rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-mono font-bold animate-pulse";
                    modeBadge.textContent = "⚡ INTERUPSI ABSENSI AKTIF (" + (data.interrupt.remaining_seconds || 10) + "s)";
                } else if (data.has_emergency && data.emergency) {
                    // Tampilkan breaking news di depan
                    interruptContainer.classList.add('hidden');
                    normalContainer.classList.remove('hidden');
                    marqueeText.textContent = data.running_text;
                    modeBadge.className = "px-2.5 py-1 rounded-md bg-rose-500/20 text-rose-300 border border-rose-500/40 font-mono font-bold";
                    modeBadge.textContent = "🚨 BREAKING NEWS AKTIF (" + (data.emergency.remaining_minutes || 15) + "m)";
                } else {
                    // Tampilkan normal playlist
                    interruptContainer.classList.add('hidden');
                    normalContainer.classList.remove('hidden');
                    marqueeText.textContent = data.running_text;
                    modeBadge.className = "px-2.5 py-1 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/40 font-mono font-bold";
                    modeBadge.textContent = "LOOPING 7-IN-1 (" + data.playlist_count + " KONTEN)";
                }
            } catch (err) {
                console.error("Gagal polling live status:", err);
            }
        }

        pollInterval = setInterval(fetchLiveDisplayStatus, 2500);
        fetchLiveDisplayStatus();

        // 3. Trigger Simulasi Tap RFID
        async function triggerSimulateTap(name, subtext, action, status, school = 'SMAS Pembda 1', time = '') {
            try {
                const res = await fetch(API_SIMULATE_TAP, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, subtext, action, status, school, time })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    // Langsung refresh simulator
                    fetchLiveDisplayStatus();
                }
            } catch (e) {
                alert("Gagal mengirim simulasi: " + e.message);
            }
        }

        // 4. Modal Emergency & Broadcast
        function openEmergencyModal() {
            document.getElementById('modalEmergency').classList.remove('hidden');
        }
        function closeEmergencyModal() {
            document.getElementById('modalEmergency').classList.add('hidden');
        }
        async function sendEmergencyBroadcast() {
            const text = document.getElementById('txtEmergency').value.trim();
            const duration = document.getElementById('selDuration').value;
            if (!text) {
                alert("Silakan masukkan teks pengumuman!");
                return;
            }
            try {
                const res = await fetch(API_EMERGENCY, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text, duration })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    closeEmergencyModal();
                    fetchLiveDisplayStatus();
                    alert("Pengumuman mendesak berhasil ditayangkan!");
                }
            } catch (e) {
                alert("Gagal: " + e.message);
            }
        }

        // 5. Simpan Controller IP
        async function saveControllerIp() {
            const ip = document.getElementById('inputControllerIp').value.trim();
            if (!ip) return;
            try {
                const res = await fetch(API_SETTINGS, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ controller_ip: ip })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    document.getElementById('lblControllerIp').textContent = ip;
                    alert("Alamat IP Controller berhasil disimpan!");
                }
            } catch (e) {
                alert("Gagal menyimpan IP: " + e.message);
            }
        }

        // 6. Modal Unlock PIN
        function openUnlockModal() {
            document.getElementById('modalUnlock').classList.remove('hidden');
        }
        function closeUnlockModal() {
            document.getElementById('modalUnlock').classList.add('hidden');
        }
        async function submitPinUnlock() {
            const pin = document.getElementById('inputPin').value.trim();
            try {
                const res = await fetch(API_UNLOCK, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pin })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    location.reload();
                } else {
                    alert(json.message);
                }
            } catch (e) {
                alert("Gagal: " + e.message);
            }
        }

        function copyScriptCode() {
            const text = document.getElementById('scriptBridgeCode').innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert("Script berhasil disalin ke clipboard!");
            });
        }
    </script>
</body>
</html>
