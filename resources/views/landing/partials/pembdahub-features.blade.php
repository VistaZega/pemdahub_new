{{-- PEMBDAHUB FEATURES — Bold Indigo Theme --}}
<section id="features" class="section" style="background:var(--bg);">
    <div class="fw">
        <div style="text-align:center; margin-bottom:64px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center;">
                <div class="section-label-dot" style="background:var(--indigo-light);"></div>
                <span class="section-label-text" style="color:var(--indigo);">Modul & Fitur PembdaHUB</span>
            </div>
            <h2 class="h1" style="margin-bottom:12px;">Fitur & <span style="background:linear-gradient(135deg,var(--indigo),var(--indigo-light)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">Ekosistem</span> Sistem</h2>
            <p class="body-lg" style="max-width:680px; margin:0 auto;">Seluruh layanan PembdaHUB dikelompokkan dalam modul-modul utama untuk mendukung kegiatan akademik, operasional, dan pengembangan kompetensi.</p>
        </div>

        {{-- CATEGORY 1: MANAJEMEN SEKOLAH --}}
        <div style="margin-bottom:60px;" data-aos="fade-up">
            <h3 class="h2" style="font-size: 22px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; color: var(--indigo-dark);">
                <i class="fa-solid fa-chalkboard-user" style="color: var(--indigo);"></i> Manajemen Sekolah & Pembelajaran
            </h3>
            
            {{-- Bento Row: Featured --}}
            <div class="bento bento-2" style="margin-bottom:20px;">
                {{-- Featured 1: RFID Multi Input --}}
                <div class="bcard shimmer-card" style="display:grid; grid-template-columns:1fr 1fr; gap:32px; align-items:center;">
                    <div>
                        <div class="feature-pill" style="background:var(--blue-bg); color:var(--blue); margin-bottom:16px;">
                            <i class="fa-solid fa-bolt"></i> Fitur Unggulan
                        </div>
                        <h4 class="h3" style="margin-bottom:12px; font-size:clamp(18px,2vw,22px);">Absensi RFID Multi Input</h4>
                        <p class="body" style="margin-bottom:20px; font-size: 13px;">Sistem kehadiran cerdas berbasis tap kartu RFID, GPS geo-location, atau input manual. Data tersinkronisasi real-time ke orang tua dan wali kelas.</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <span class="feature-pill" style="background:var(--emerald-bg); color:var(--emerald);"><i class="fa-solid fa-check"></i> Tap Kartu</span>
                            <span class="feature-pill" style="background:var(--amber-bg); color:var(--amber);"><i class="fa-solid fa-check"></i> GPS & Geofence</span>
                            <span class="feature-pill" style="background:var(--violet-bg); color:var(--violet);"><i class="fa-solid fa-check"></i> Real-time</span>
                        </div>
                    </div>
                    <div style="background:linear-gradient(135deg, var(--blue-bg), #dbeafe); border-radius:16px; padding:32px; display:flex; align-items:center; justify-content:center; min-height:180px; position:relative; overflow:hidden;">
                        <div style="position:absolute; top:-20px; right:-20px; width:100px; height:100px; border-radius:50%; background:rgba(59,130,246,0.08);"></div>
                        <i class="fa-solid fa-id-card-clip" style="font-size:64px; color:var(--blue); opacity:0.3;"></i>
                    </div>
                </div>

                {{-- Featured 2: LMS --}}
                <div class="bcard bcard-dark" style="display:grid; grid-template-columns:1fr 1fr; gap:32px; align-items:center; border:none;">
                    <div style="background:rgba(139,92,246,0.08); border-radius:16px; padding:32px; display:flex; align-items:center; justify-content:center; min-height:180px; position:relative; overflow:hidden;">
                        <div style="position:absolute; top:-20px; left:-20px; width:100px; height:100px; border-radius:50%; background:rgba(139,92,246,0.1);"></div>
                        <i class="fa-solid fa-chalkboard" style="font-size:64px; color:var(--violet); opacity:0.35;"></i>
                    </div>
                    <div>
                        <div class="feature-pill" style="background:rgba(139,92,246,0.15); color:#a78bfa; margin-bottom:16px;">
                            <i class="fa-solid fa-star"></i> Learning Platform
                        </div>
                        <h4 style="font-size:clamp(18px,2vw,22px); font-weight:700; color:#fff; margin-bottom:12px; letter-spacing:-0.02em;">Pembelajaran (LMS)</h4>
                        <p style="font-size:13px; color:#94a3b8; line-height:1.6; margin-bottom:20px;">Platform kelas digital yang lengkap. Pembagian materi interaktif, pengumpulan tugas daring, dan kuis otomatis terintegrasi.</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <span class="feature-pill" style="background:rgba(16,185,129,0.15); color:#6ee7b7;"><i class="fa-solid fa-check"></i> E-Materi</span>
                            <span class="feature-pill" style="background:rgba(245,158,11,0.15); color:#fcd34d;"><i class="fa-solid fa-check"></i> Penugasan</span>
                            <span class="feature-pill" style="background:rgba(59,130,246,0.15); color:#93bbfb;"><i class="fa-solid fa-check"></i> Kuis</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compact Cards Row --}}
            <div class="bento bento-4">
                {{-- Feature: Pembayaran --}}
                <div class="bcard" style="padding:24px;">
                    <div class="icon-circle" style="background:var(--emerald-bg); color:var(--emerald); margin-bottom:16px;">
                        <i class="fa-solid fa-money-check-dollar"></i>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:15px;">Pembayaran SPP</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">Layanan tagihan SPP digital, rekap pembayaran otomatis, serta riwayat transaksi transparan bagi orang tua.</p>
                </div>

                {{-- Feature: CBT --}}
                <div class="bcard" style="padding:24px;">
                    <div class="icon-circle" style="background:var(--cyan-bg); color:var(--cyan); margin-bottom:16px;">
                        <i class="fa-solid fa-laptop-code"></i>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:15px;">Computer Based Test</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">Ujian sekolah berbasis komputer dengan bank soal terenkripsi, timer adaptif, dan rilis nilai otomatis.</p>
                </div>

                {{-- Feature: Penjadwalan --}}
                <div class="bcard" style="padding:24px;">
                    <div class="icon-circle" style="background:var(--amber-bg); color:var(--amber); margin-bottom:16px;">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:15px;">Tugas & Penjadwalan</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">Kalender akademik terpadu, pembagian tugas mengajar guru, dan visualisasi jadwal pelajaran kelas.</p>
                </div>

                {{-- Feature: Perkembangan Siswa --}}
                <div class="bcard" style="padding:24px;">
                    <div class="icon-circle" style="background:var(--coral-bg); color:var(--coral); margin-bottom:16px;">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:15px;">Catatan Perkembangan</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">Dokumentasi capaian akademik, evaluasi kepribadian, serta konseling terintegrasi guru BK.</p>
                </div>
            </div>
        </div>

        {{-- CATEGORY 2: PENGEMBANGAN SISWA & GURU (NEW & Gamification) --}}
        <div style="margin-bottom:60px;" data-aos="fade-up" data-aos-delay="50">
            <h3 class="h2" style="font-size: 22px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; color: var(--indigo-dark);">
                <i class="fa-solid fa-people-up-trend" style="color: var(--indigo);"></i> Pengembangan Siswa & Guru
            </h3>

            <div class="bento bento-3" style="margin-bottom:20px;">
                {{-- NEW: Sistem Reputasi & Leaderboard --}}
                <div class="bcard gradient-border-card shimmer-card" style="padding:28px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                        <div class="icon-circle" style="background:linear-gradient(135deg, var(--gold-bg), #fef3c7); color:var(--gold); border: 1px solid rgba(245,158,11,0.25);">
                            <i class="fa-solid fa-ranking-star"></i>
                        </div>
                        <span class="feature-pill" style="background:linear-gradient(135deg, var(--gold), #fbbf24); color:#1e1b4b; font-weight:700; box-shadow: 0 4px 10px rgba(245,158,11,0.25);">
                            <i class="fa-solid fa-trophy"></i> Point Gamifikasi
                        </span>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:16px; color:var(--text-primary);">Reputasi & Leaderboard</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6; margin-bottom: 12px;">Sistem penghargaan berbasis poin aktivitas bagi siswa dan guru. Setiap keaktifan LMS, presensi RFID, dan prestasi menyumbang poin ke leaderboard sekolah.</p>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <span class="feature-pill" style="background:rgba(245,158,11,0.08); color:var(--gold);"><i class="fa-solid fa-star"></i> Poin Keaktifan</span>
                        <span class="feature-pill" style="background:rgba(245,158,11,0.08); color:var(--gold);"><i class="fa-solid fa-medal"></i> Leaderboard</span>
                    </div>
                </div>

                {{-- NEW: Forum Diskusi --}}
                <div class="bcard gradient-border-card shimmer-card" style="padding:28px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                        <div class="icon-circle" style="background:var(--blue-bg); color:var(--blue); border: 1px solid rgba(59,130,246,0.25);">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                        <span class="feature-pill" style="background:var(--blue); color:#fff; font-weight:700;">
                            <i class="fa-solid fa-plus-circle"></i> Kolaboratif
                        </span>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:16px; color:var(--text-primary);">Forum Diskusi Akademik</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6; margin-bottom: 12px;">Ruang interaksi sosial akademik bagi guru dan siswa. Fasilitas berbagi thread pelajaran, tanya jawab soal sulit, dan kolaborasi antar tingkat kelas.</p>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <span class="feature-pill" style="background:var(--blue-bg); color:var(--blue);"><i class="fa-solid fa-share-nodes"></i> Tanya Jawab</span>
                        <span class="feature-pill" style="background:var(--blue-bg); color:var(--blue);"><i class="fa-solid fa-users"></i> Antar-Sekolah</span>
                    </div>
                </div>

                {{-- NEW: Tugas Akhir / Proyek Penelitian --}}
                <div class="bcard gradient-border-card shimmer-card" style="padding:28px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                        <div class="icon-circle" style="background:var(--violet-bg); color:var(--violet); border: 1px solid rgba(139,92,246,0.25);">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <span class="feature-pill" style="background:var(--violet); color:#fff; font-weight:700;">
                            <i class="fa-solid fa-graduation-cap"></i> Kelas XII
                        </span>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:16px; color:var(--text-primary);">Tugas Akhir & Penelitian</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6; margin-bottom: 12px;">Perekaman siklus penulisan Karya Tulis Ilmiah (SMA) dan Proyek Akhir berbasis Keahlian (SMK). Mengakomodasi pengajuan proposal, bimbingan guru, hingga sidang.</p>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <span class="feature-pill" style="background:var(--violet-bg); color:var(--violet);"><i class="fa-solid fa-signature"></i> Bimbingan Online</span>
                        <span class="feature-pill" style="background:var(--violet-bg); color:var(--violet);"><i class="fa-solid fa-book-bookmark"></i> Repository</span>
                    </div>
                </div>
            </div>

            <div class="bento bento-2">
                {{-- NEW: PKL & Alumni (Tracer Study) --}}
                <div class="bcard gradient-border-card shimmer-card" style="padding:28px; display:grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: center;">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                            <div class="icon-circle" style="background:var(--emerald-bg); color:var(--emerald); border: 1px solid rgba(16,185,129,0.25);">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                        </div>
                        <h4 class="h3" style="margin-bottom:8px; font-size:16px; color:var(--text-primary);">PKL & Tracer Study Alumni</h4>
                        <p style="font-size:12px; color:var(--text-secondary); line-height:1.6; margin-bottom: 12px;">Modul pemantauan Praktek Kerja Lapangan (PKL) SMK dengan jurnal digital harian, rating industri, serta tracer study mandiri bagi jejaring alumni PEMBDA.</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <span class="feature-pill" style="background:var(--emerald-bg); color:var(--emerald);"><i class="fa-solid fa-building"></i> Jurnal Kerja</span>
                            <span class="feature-pill" style="background:var(--emerald-bg); color:var(--emerald);"><i class="fa-solid fa-users-line"></i> Tracer Study</span>
                        </div>
                    </div>
                    <div style="background:linear-gradient(135deg, var(--emerald-bg), #d1fae5); border-radius:16px; padding:24px; display:flex; align-items:center; justify-content:center; min-height:140px; position:relative; overflow:hidden;">
                        <i class="fa-solid fa-circle-nodes" style="font-size:54px; color:var(--emerald); opacity:0.3;"></i>
                    </div>
                </div>

                {{-- Existing: Pembinaan & Penghargaan --}}
                <div class="bcard" style="padding:28px;">
                    <div class="icon-circle" style="background:var(--coral-bg); color:var(--coral); margin-bottom:16px;">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:16px;">Pembinaan & Penghargaan</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6; margin-bottom: 12px;">Pencatatan pelanggaran (poin minus) serta pemberian penghargaan prestasi (poin plus) yang terhubung otomatis pada riwayat beasiswa atau keringanan biaya.</p>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <span class="feature-pill" style="background:var(--coral-bg); color:var(--coral);"><i class="fa-solid fa-scale-balanced"></i> Konseling BK</span>
                        <span class="feature-pill" style="background:var(--coral-bg); color:var(--coral);"><i class="fa-solid fa-certificate"></i> Reward Point</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CATEGORY 3: ADMINISTRASI & MONITORING --}}
        <div data-aos="fade-up" data-aos-delay="100">
            <h3 class="h2" style="font-size: 22px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; color: var(--indigo-dark);">
                <i class="fa-solid fa-sliders" style="color: var(--indigo);"></i> Administrasi & Kontrol Orang Tua
            </h3>

            <div class="bento bento-3">
                {{-- Feature: Kepegawaian --}}
                <div class="bcard" style="padding:24px;">
                    <div class="icon-circle" style="background:var(--blue-bg); color:var(--blue); margin-bottom:16px;">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:15px;">Administrasi Kepegawaian</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">Database terpadu profil guru, status sertifikasi, beban kerja mengajar, dan arsip kepegawaian yayasan.</p>
                </div>

                {{-- Feature: Parental Control --}}
                <div class="bcard" style="padding:24px;">
                    <div class="icon-circle" style="background:var(--emerald-bg); color:var(--emerald); margin-bottom:16px;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:15px;">Parental Control</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">Akses monitoring bagi orang tua untuk memantau presensi RFID, capaian nilai ujian, tagihan keuangan, dan riwayat bimbingan anak.</p>
                </div>

                {{-- Feature: Dashboard --}}
                <div class="bcard" style="padding:24px;">
                    <div class="icon-circle" style="background:var(--amber-bg); color:var(--amber); margin-bottom:16px;">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:15px;">Real-time Dashboard</h4>
                    <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">Penyajian visual performa sekolah, statistik rekapitulasi kehadiran harian, dan grafik sebaran nilai siswa.</p>
                </div>
            </div>
        </div>
    </div>
</section>
