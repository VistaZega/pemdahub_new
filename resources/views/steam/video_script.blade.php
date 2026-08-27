@php
    $user = Auth::user();
    $role = session('active_role', $user->role);
    $layout = match($role) {
        'yayasan', 'ketua_yayasan' => 'layouts.yayasan',
        'siswa' => 'layouts.siswa',
        'guru' => 'layouts.guru',
        default => 'layouts.admin',
    };
@endphp

@extends($layout)

@section('title', 'Naskah Video Presentasi (3-5 Menit) - STEAMpreneur SMK 2026')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">

    <!-- Top Navigation Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('steam.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-sm transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard Lomba
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 px-4 py-2 rounded-xl shadow-md shadow-rose-600/20 transition">
            <i class="fas fa-print"></i> Cetak Naskah Syuting
        </button>
    </div>

    <!-- Script Container Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10 space-y-8">
        
        <!-- Header -->
        <div class="border-b border-slate-200 pb-6 text-center space-y-2">
            <div class="inline-block bg-rose-100 text-rose-800 text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-wider">
                Panduan Produksi &amp; Syuting Video Presentasi
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900">
                Naskah Video Presentasi Ide Karya (Durasi Target: 4 Menit 15 Detik)
            </h1>
            <p class="text-xs text-slate-500">
                Toleransi Juknis: 3 – 5 Menit | Kategori: Bidang Teknologi Informasi (SMK IT)
            </p>
        </div>

        <!-- Presenter Roles Box -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200/60">
                <span class="font-extrabold text-amber-800 block mb-1">🎤 Presenter 1 (Ketua Tim / PPLG)</span>
                <p class="text-slate-600">Pembuka, Penegasan Kemandirian Riset, Arsitektur Multi-Unit Yayasan, dan Closing Pitch.</p>
            </div>
            <div class="p-4 bg-blue-50 rounded-2xl border border-blue-200/60">
                <span class="font-extrabold text-blue-800 block mb-1">⚡ Presenter 2 (Hardware TJKT)</span>
                <p class="text-slate-600">Demo Live Kiosk IoT, Deteksi Multi-Entitas, 5 Pilar STEAM, &amp; Fitur Tahan Putus Jaringan.</p>
            </div>
            <div class="p-4 bg-purple-50 rounded-2xl border border-purple-200/60">
                <span class="font-extrabold text-purple-800 block mb-1">📊 Presenter 3 (Bisnis / TeFa)</span>
                <p class="text-slate-600">Integrasi bengkelin.cloud, E-PKL, Student DNA, &amp; Analisis HPP/BEP Serapan Yayasan.</p>
            </div>
        </div>

        <!-- Script Segments -->
        <div class="space-y-6">

            <!-- Segmen 1 -->
            <div class="border-l-4 border-amber-500 pl-4 py-1 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900">SEGMEN 1: Hook, Skala Yayasan, &amp; Kemandirian Vokasi</h3>
                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">00:00 – 00:45</span>
                </div>
                <p class="text-xs text-slate-500 italic">
                    <strong>Visual:</strong> Establishing shot Kampus Yayasan PEMBDA. Tiga siswa berjas almamater/praktik. Teks di layar: "100% Karya Mandiri Guru &amp; Siswa SMK".
                </p>
                <div class="bg-slate-50 p-4 rounded-xl text-xs sm:text-sm text-slate-800 leading-relaxed font-serif">
                    "Salam inovasi! Saya <strong>{{ $competitionData['team_leader']['name'] }}</strong>, bersama rekan saya <strong>{{ $competitionData['team_member_1']['name'] }}</strong> dan <strong>{{ $competitionData['team_member_2']['name'] }}</strong> dari <strong>SMK Swasta Pembda Nias</strong>, di bawah naungan <strong>Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</strong>.<br><br>
                    Di tengah keterbatasan anggaran daerah kepulauan, kami tidak menyewa konsultan IT maupun vendor komersial dari luar. <strong>Sistem ini 100% dirancang, dikoding, dan dirakit secara mandiri dari nol oleh guru-guru teknis dan siswa-siswi SMK Swasta Pembda Nias!</strong><br><br>
                    Inovasi ini bukan sekadar konsep coba-coba, melainkan <strong>telah dimulai, sedang beroperasi secara nyata melayani ribuan siswa, guru, dan pegawai di SMPS Pembda 2, SMA Pembda 1, SMKS Pembda Nias, dan unit Teaching Factory Bengkelin</strong>, serta terus kami kembangkan hingga saat ini. Inilah: <strong>PEMBDA-HUB</strong>!"
                </div>
            </div>

            <!-- Segmen 2 -->
            <div class="border-l-4 border-blue-500 pl-4 py-1 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900">SEGMEN 2: Live Demo Hardware IoT &amp; Deteksi Multi-Entitas</h3>
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">00:45 – 01:30</span>
                </div>
                <p class="text-xs text-slate-500 italic">
                    <strong>Visual:</strong> Close-up Kiosk ESP32. Siswa tap kartu siswa &amp; guru. Audio DFPlayer menyapa. Layar split-screen web PembdaHUB &amp; notifikasi WhatsApp masuk di ponsel orang tua.
                </p>
                <div class="bg-slate-50 p-4 rounded-xl text-xs sm:text-sm text-slate-800 leading-relaxed font-serif">
                    "Kiosk IoT cerdas berbasis ESP32 240MHz ini kami rakit sendiri di laboratorium sekolah. Alat ini mampu mengidentifikasi multi-entitas dalam 0,3 detik: siswa SMP, SMA, SMK, guru lintas unit, pegawai yayasan, hingga teknisi TeFa.<br><br>
                    Audio modul DFPlayer menyapa secara interaktif, dan notifikasi WhatsApp langsung terkirim ke orang tua.<br><br>
                    Dan inilah keunggulan khas kepulauan kami: <strong>Fitur Resilient-Edge Buffer</strong>. Jika listrik atau internet padam total di Nias, Kiosk tetap merekam presensi di memori lokal secara offline, lalu melakukan sinkronisasi otomatis tanpa ada data yang hilang saat jaringan pulih!"
                </div>
            </div>

            <!-- Segmen 3 -->
            <div class="border-l-4 border-emerald-500 pl-4 py-1 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900">SEGMEN 3: Integrasi Komprehensif 5 Pilar STEAM</h3>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">01:30 – 02:15</span>
                </div>
                <p class="text-xs text-slate-500 italic">
                    <strong>Visual:</strong> Infografis 5 pilar STEAM terintegrasi di layar.
                </p>
                <div class="bg-slate-50 p-4 rounded-xl text-xs sm:text-sm text-slate-800 leading-relaxed font-serif">
                    "• <strong>Science:</strong> Induksi elektromagnetik RFID 13.56 MHz dan efisiensi daya mikrokontroler 0.8 Watt yang hemat energi dan siap ditenagai solar panel.<br>
                    • <strong>Technology:</strong> Arsitektur Multi-Tenant Laravel 12 dengan 62 tabel database relasional, FreeRTOS C++, dan WhatsApp Cloud Gateway.<br>
                    • <strong>Engineering:</strong> Rekayasa sirkuit dual-scanning (RFID dan QR Code) serta mekanisme fail-safe buffer lokal.<br>
                    • <strong>Arts:</strong> Desain visual UI/UX Tailwind CSS yang ergonomis dan sound UI audio sapaan interaktif.<br>
                    • <strong>Mathematics:</strong> Pemodelan algoritma radar 6 dimensi Student DNA dan aljabar matriks deteksi bentrok jadwal guru lintas unit sekolah."
                </div>
            </div>

            <!-- Segmen 4 -->
            <div class="border-l-4 border-purple-500 pl-4 py-1 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900">SEGMEN 4: Sinergi TeFa Bengkelin &amp; E-PKL Digital</h3>
                    <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md">02:15 – 03:05</span>
                </div>
                <p class="text-xs text-slate-500 italic">
                    <strong>Visual:</strong> Cuplikan Gerai TeFa di Jl. Pelita No. 09, web www.bengkelin.cloud, serta smartphone siswa menunjukkan modul E-PKL GPS &amp; Magic Link DUDI.
                </p>
                <div class="bg-slate-50 p-4 rounded-xl text-xs sm:text-sm text-slate-800 leading-relaxed font-serif">
                    "Sebagai pilar kewirausahaan vokasi (STEAMpreneur), unit Teaching Factory kami beroperasi secara nyata melayani masyarakat luas melalui platform <strong>www.bengkelin.cloud</strong>. Pelanggan dapat melakukan booking online dan memantau status servis kendaraannya secara live! Jam kerja teknisi siswa di bengkel dicatat langsung oleh Kiosk RFID PembdaHUB sebagai jam terbang praktikum.<br><br>
                    Selain itu, untuk siswa magang di DUDI, modul <strong>Smart E-PKL</strong> memvalidasi jurnal harian dengan foto dan koordinat GPS, sementara mentor industri dapat menyetujui logbook hanya dalam 1 klik melalui <strong>Magic Token Link</strong> tanpa perlu login!"
                </div>
            </div>

            <!-- Segmen 5 -->
            <div class="border-l-4 border-indigo-500 pl-4 py-1 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900">SEGMEN 5: Analisis Numerasi &amp; BEP Serapan Yayasan</h3>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">03:05 – 03:55</span>
                </div>
                <p class="text-xs text-slate-500 italic">
                    <strong>Visual:</strong> Tabel perbandingan HPP Rp 320rb vs Rp 2,5jt, margin 62,35%, dan diagram BEP 5 Unit Kiosk.
                </p>
                <div class="bg-slate-50 p-4 rounded-xl text-xs sm:text-sm text-slate-800 leading-relaxed font-serif">
                    "Secara numerasi bisnis, produk kami sangat efisien dan berdaya saing tinggi:<br>
                    • <strong>HPP 1 Unit Kiosk IoT:</strong> Hanya <strong>Rp 320.000</strong>, memberikan efisiensi biaya <strong>87,2%</strong> dibandingkan mesin komersial pasaran seharga Rp 2,5 juta.<br>
                    • <strong>Margin Keuntungan:</strong> Dijual seharga Rp 850.000 dengan margin laba kotor <strong>62,35%</strong>.<br>
                    • <strong>Break Even Point (BEP):</strong> Titik impas modal tercapai hanya pada penjualan <strong>5 unit</strong>.<br><br>
                    Kebutuhan internal di lingkungan Yayasan PEMBDA Nias sendiri tepat berjumlah <strong>5 unit</strong> (SMPS 2, SMA 1, SMKS 2 titik, dan TeFa 1 titik). Artinya, titik impas telah <strong>tercapai 100% dari penyerapan ekosistem internal yayasan kami sendiri</strong>!"
                </div>
            </div>

            <!-- Segmen 6 -->
            <div class="border-l-4 border-rose-500 pl-4 py-1 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900">SEGMEN 6: Closing Pitch &amp; Slogan</h3>
                    <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">03:55 – 04:20</span>
                </div>
                <p class="text-xs text-slate-500 italic">
                    <strong>Visual:</strong> Tiga siswa berdiri bersama memberikan gestur tangan semangat vokasi, logo Kemendikdasmen dan SMK Pembda Nias.
                </p>
                <div class="bg-slate-50 p-4 rounded-xl text-xs sm:text-sm text-slate-800 leading-relaxed font-serif">
                    "Dari keterbatasan, lahir inovasi mandiri untuk negeri!<br>
                    PEMBDA-HUB: Dari Kepulauan Nias, Membangun Transformasi Digital Vokasi Indonesia!<br><br>
                    <strong>SMK Bisa, SMK Hebat, STEAMpreneur: Solusi Nyata Untuk Indonesia!</strong>"
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
