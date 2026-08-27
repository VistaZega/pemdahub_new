<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pitch Deck Slides (12 Slide) - STEAMpreneur SMK 2026</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background-color: #0F172A;
            color: #F8FAFC;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .slide-deck-container {
            width: 100%;
            max-width: 980px;
            background: #1E293B;
            border: 1px solid #334155;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Slide Content Area */
        .slide-viewport {
            min-height: 480px;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            background: radial-gradient(circle at top right, #1E3A8A 0%, #1E293B 70%);
        }

        .slide-tag {
            display: inline-block;
            background: #2563EB;
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 14px;
            align-self: flex-start;
        }

        .slide-title {
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 20px;
            color: #FFFFFF;
            line-height: 1.3;
        }

        .slide-body {
            font-size: 15px;
            color: #CBD5E1;
            line-height: 1.7;
        }

        .slide-body ul {
            padding-left: 24px;
            margin-top: 12px;
        }

        .slide-body li {
            margin-bottom: 10px;
        }

        .highlight-number {
            color: #F59E0B;
            font-weight: 900;
            font-size: 18px;
        }

        /* Slide Controls Bar */
        .slide-controls {
            background: #0F172A;
            padding: 18px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #334155;
        }

        .slide-counter {
            font-size: 14px;
            font-weight: 700;
            color: #94A3B8;
        }

        .control-btns {
            display: flex;
            gap: 12px;
        }

        .btn-ctrl {
            background: #2563EB;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-ctrl:hover {
            background: #1D4ED8;
        }

        .btn-ctrl:disabled {
            background: #334155;
            color: #64748B;
            cursor: not-allowed;
        }

        .btn-back {
            background: transparent;
            color: #94A3B8;
            border: 1px solid #475569;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 16px;
        }
        .btn-back:hover {
            color: #FFFFFF;
            border-color: #CBD5E1;
        }
    </style>
</head>
<body>

    <a href="{{ route('steam.index') }}" class="btn-back">⬅️ Kembali ke Dashboard Lomba</a>

    <div class="slide-deck-container">
        
        <div class="slide-viewport" id="slideViewport">
            <!-- Slide Content will be populated by JS -->
        </div>

        <div class="slide-controls">
            <span class="slide-counter" id="slideCounter">Slide 1 dari 12</span>
            <div class="control-btns">
                <button class="btn-ctrl" id="prevBtn" onclick="prevSlide()">◀ Sebelumnya</button>
                <button class="btn-ctrl" id="nextBtn" onclick="nextSlide()">Berikutnya ▶</button>
            </div>
        </div>

    </div>

    <script>
        const slides = [
            {
                tag: "SLIDE 1: JUDUL & NILAI ORISINALITAS",
                title: "PEMBDA-HUB: Ekosistem Smart School & Kiosk IoT Berbasis Resilient-Edge",
                content: `
                    <p style="font-size:16px; color:#E0E7FF; margin-bottom:12px;"><strong>Solusi Nyata Multi-Unit Yayasan Perguruan PEMBDA Nias</strong></p>
                    <ul>
                        <li><strong>Guru Pendamping:</strong> <span style="color:#60A5FA;">{{ $competitionData['mentor_teacher']['name'] }}</span> ({{ $competitionData['mentor_teacher']['nip'] }})</li>
                        <li><strong>Tim Siswa Inovator:</strong> {{ $competitionData['team_leader']['name'] }} (Ketua), {{ $competitionData['team_member_1']['name'] }}, {{ $competitionData['team_member_2']['name'] }}</li>
                        <li><strong>Kemandirian Penuh:</strong> <span style="color:#F59E0B;">100% Karya Mandiri Guru Teknis & Siswa SMK</span> (Tanpa Konsultan/Vendor Luar karena ketiadaan biaya).</li>
                        <li><strong>Status Sistem:</strong> <span style="color:#10B981;">Telah Dimulai, Sedang Berjalan Riil (In-Production), & Terus Dikembangkan</span>.</li>
                        <li><strong>Cakupan Implementasi:</strong> SMPS Pembda 2, SMA Pembda 1, SMKS Pembda Nias, & TeFa Bengkelin.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 2: PERMASALAHAN FAKTUAL DAERAH 3T",
                title: "Tantangan Nyata di Wilayah Kepulauan (Pulau Nias)",
                content: `
                    <ul>
                        <li><strong>Ketiadaan Anggaran Software Vendor:</strong> Mahalnya biaya konsultan luar (puluhan/ratusan juta) membuat sekolah harus mandiri.</li>
                        <li><strong>Kerapuhan Jaringan & Listrik Nias:</strong> Pemadaman bergilir membuat mesin absensi online biasa macet total.</li>
                        <li><strong>Fragmentasi Multi-Sekolah:</strong> Mengelola ribuan siswa & guru lintas jenjang (SMP, SMA, SMK) dalam satu yayasan.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 3: ARSITEKTUR MULTI-TENANT YAYASAN",
                title: "Solusi Ekosistem Terpadu Yayasan Perguruan PEMBDA",
                content: `
                    <p>Satu infrastruktur cerdas melayani seluruh unit secara tersentralisasi:</p>
                    <ul>
                        <li><strong>Multi-School Core (PembdaHUB):</strong> Mengelola hak akses terisolasi untuk SMP, SMA, SMK, jadwal guru lintas unit, dan analitik 360° Student DNA.</li>
                        <li><strong>Commercial TeFa Unit (www.bengkelin.cloud):</strong> Melayani pemesanan publik, live service tracking, dan POS kasir bengkel nyata di Gunungsitoli.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 4: SMART IOT KIOSK & MULTI-ENTITAS",
                title: "Hardware Kiosk IoT Cerdas (ESP32 240MHz)",
                content: `
                    <ul>
                        <li><strong>Deteksi Multi-Entitas Cerdas:</strong> 1 alat otomatis mengenali Siswa SMP/SMA/SMK, Guru Lintas Unit, Pegawai Yayasan, atau Teknisi TeFa.</li>
                        <li><strong>Dual Scan & Voice UI:</strong> RFID 13.56 MHz + GM65 QR Scanner + Audio DFPlayer Mini menyapa ramah.</li>
                        <li><strong>Fitur Resilient-Edge Buffer:</strong> Menyimpan transaksi presensi saat listrik/internet padam, auto-sync saat koneksi pulih.</li>
                        <li><strong>WhatsApp Gateway:</strong> Notifikasi real-time terkirim ke HP orang tua & pengurus yayasan.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 5: INTEGRASI 5 PILAR STEAM",
                title: "Penerapan Komprehensif 5 Pilar STEAM",
                content: `
                    <ul>
                        <li><strong>Science:</strong> Induksi elektromagnetik RFID 13.56 MHz & efisiensi daya 0.8 Watt (Solar-Ready).</li>
                        <li><strong>Technology:</strong> Multi-Tenant Laravel 12 API, FreeRTOS C++, & WhatsApp Gateway.</li>
                        <li><strong>Engineering:</strong> Rekayasa sirkuit Kiosk dual-scan & algoritma fail-safe buffer lokal.</li>
                        <li><strong>Arts:</strong> Human-Centered UI Tailwind CSS, Voice UI Audio, & visual badges gamifikasi.</li>
                        <li><strong>Mathematics:</strong> Pemodelan matriks 6D Student DNA, deteksi bentrok jadwal lintas unit, & BEP.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 6: TEACHING FACTORY 4.0",
                title: "Sinergi Komersial Unit TeFa (www.bengkelin.cloud)",
                content: `
                    <ul>
                        <li>Unit TeFa di Jl. Pelita No. 09 Gunungsitoli melayani servis kendaraan nyata dari masyarakat umum.</li>
                        <li>Pelanggan dapat melacak status pengerjaan secara transparan via web <code>www.bengkelin.cloud</code>.</li>
                        <li>Jam kerja teknisi siswa dicatat menggunakan Kiosk RFID PembdaHUB sebagai jam terbang praktikum riil.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 7: SMART E-PKL & DUDI PORTAL",
                title: "Digitalisasi Magang Industri & Magic Token Link",
                content: `
                    <ul>
                        <li><strong>Geotagging GPS & Foto Kamera:</strong> Siswa mengunggah jurnal harian PKL terbukti berada di lokasi bengkel DUDI.</li>
                        <li><strong>Verifikasi 1-Klik Mentor DUDI:</strong> Mentor industri menyetujui logbook melalui <em>Signed Magic Token Link</em> tanpa perlu ribet login akun/password.</li>
                        <li><strong>Notifikasi WhatsApp Revisi:</strong> Catatan evaluasi mentor langsung terkirim ke ponsel siswa.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 8: 360° STUDENT DNA ANALYTICS",
                title: "Pemetaan Bakat & Karir Siswa Berbasis Data Riil",
                content: `
                    <ul>
                        <li>Mengolah 6 aliran data: Presensi IoT, Nilai Rapor, Skor CBT, Poin Reputasi, Jam Kerja TeFa, & Asesmen Mandiri.</li>
                        <li>Memproyeksikan kompetensi ke dalam <strong>Radar Grafik 6 Dimensi</strong>: <em>Logic, Technical, Creative, Social, Communication, Discipline</em>.</li>
                        <li>Menghasilkan rekomendasi karir spesifik & mencetak Rapor DNA resmi berformat PDF.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 9: NUMERASI - HPP & EFISIENSI BIAYA",
                title: "Analisis Harga Pokok Produksi (HPP) Stasiun Kiosk",
                content: `
                    <ul>
                        <li>Biaya Komponen: ESP32 (55rb), RFID (18rb), DFPlayer MP3 (38rb), LCD 20x4 (42rb), Akrilik (65rb), Adaptor + Jasa Rakit (75rb).</li>
                        <li><strong>Total HPP Kiosk PembdaHUB:</strong> <span class="highlight-number">Rp 320.000 / unit</span>.</li>
                        <li>Harga Mesin Komersial Pasar: <strong>Rp 2.500.000</strong>.</li>
                        <li><strong>Efisiensi Biaya Produksi:</strong> <span class="highlight-number">87,2% Lebih Hemat</span>.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 10: NUMERASI - MARGIN & BEP SERAPAN YAYASAN",
                title: "Kelayakan Bisnis & Validasi Kebutuhan Internal",
                content: `
                    <ul>
                        <li><strong>Harga Jual Pasar:</strong> Rp 850.000 / unit (Margin laba kotor <strong>62,35%</strong>).</li>
                        <li><strong>Break Even Point (BEP):</strong> <span class="highlight-number">Hanya 5 Unit Kiosk</span>.</li>
                        <li><strong>Validasi Penyerapan Riil:</strong> Kebutuhan internal Yayasan PEMBDA Nias tepat berjumlah <strong>5 Unit</strong> (SMPS 2, SMA 1, SMKS 2 titik, TeFa Bengkelin 1 titik). BEP langsung tercapai 100% dari serapan ekosistem internal yayasan!</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 11: BUKTI KEMATANGAN & SKALABILITAS DATA",
                title: "Sistem Teruji Nyata & Terus Dikembangkan",
                content: `
                    <ul>
                        <li><strong>384 Automated Tests Passed</strong> (990 assertions lolos pengujian 100%).</li>
                        <li>Murni dikoding oleh guru & siswa (162 Model database, 129 Controller backend).</li>
                        <li>Telah beroperasi nyata melayani ribuan data siswa, guru, pegawai yayasan, dan pelanggan TeFa.</li>
                        <li>Pengembangan berkelanjutan: integrasi AI Face Recognition & Green School IoT.</li>
                    </ul>
                `
            },
            {
                tag: "SLIDE 12: KESIMPULAN & PENUTUP",
                title: "Dari Keterbatasan Menjadi Inovasi Vokasi Indonesia",
                content: `
                    <p style="font-size:18px; color:#F59E0B; margin-bottom:16px;"><strong>"SMK Bisa, SMK Hebat, STEAMpreneur: Solusi Nyata Untuk Indonesia!"</strong></p>
                    <ul>
                        <li><strong>SMK Swasta Pembda Nias</strong> — Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</li>
                        <li>Website TeFa: <code>www.bengkelin.cloud</code></li>
                        <li>Alamat: Jl. Pelita No. 09, Kota Gunungsitoli, Sumatera Utara</li>
                    </ul>
                `
            }
        ];

        let currentSlide = 0;

        function renderSlide() {
            const s = slides[currentSlide];
            document.getElementById('slideViewport').innerHTML = `
                <div class="slide-tag">${s.tag}</div>
                <div class="slide-title">${s.title}</div>
                <div class="slide-body">${s.content}</div>
            `;
            document.getElementById('slideCounter').innerText = `Slide ${currentSlide + 1} dari ${slides.length}`;
            document.getElementById('prevBtn').disabled = (currentSlide === 0);
            document.getElementById('nextBtn').disabled = (currentSlide === slides.length - 1);
        }

        function nextSlide() {
            if (currentSlide < slides.length - 1) {
                currentSlide++;
                renderSlide();
            }
        }

        function prevSlide() {
            if (currentSlide > 0) {
                currentSlide--;
                renderSlide();
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'Space') nextSlide();
            if (e.key === 'ArrowLeft') prevSlide();
        });

        // Initial Render
        renderSlide();
    </script>
</body>
</html>
