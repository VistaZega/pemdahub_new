{{-- EKOSISTEM DIGITAL — New Section --}}
<section id="ekosistem" class="section" style="background: linear-gradient(135deg, #0f172a, #1e1b4b);">
    <div class="fw">
        <div style="text-align:center; margin-bottom:56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center;">
                <div class="section-label-dot" style="background:var(--cyan);"></div>
                <span class="section-label-text" style="color:var(--cyan);">Ekosistem Digital</span>
            </div>
            <h2 style="color:#fff; font-size:clamp(24px,3vw,36px); font-weight:800; letter-spacing:-0.02em; margin-bottom:12px;">
                Bukan Sekadar <span style="background:linear-gradient(135deg,#22d3ee,#3b82f6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">Sekolah</span>
            </h2>
            <p style="font-size:18px; color:rgba(255,255,255,0.7); max-width:680px; margin:0 auto; line-height:1.6;">
                Sebuah ekosistem pendidikan digital terdepan yang menghubungkan seluruh elemen pembelajaran, administrasi, dan evaluasi dalam satu platform canggih.
            </p>
        </div>

        <div class="bento bento-4" style="margin-bottom:60px;" data-aos="fade-up" data-aos-delay="100">
            {{-- LMS --}}
            <div class="stat-card" style="background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.1);">
                <div class="icon-circle" style="background:rgba(59,130,246,0.15); color:#93c5fd; width:64px; height:64px; font-size:28px; margin:0 auto 20px; border:1px solid rgba(59,130,246,0.3);">
                    <i class="fa-solid fa-laptop-code"></i>
                </div>
                <div class="stat-card__value" style="color:#fff;" data-count="{{ $totalCourses }}" data-suffix="+"></div>
                <div class="stat-card__label" style="color:#93c5fd;">Kursus Online</div>
                <p style="font-size:12px; color:rgba(255,255,255,0.5); margin-top:12px; line-height:1.5;">Modul E-Learning & interaksi kelas digital harian</p>
            </div>

            {{-- CBT --}}
            <div class="stat-card" style="background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.1);">
                <div class="icon-circle" style="background:rgba(16,185,129,0.15); color:#6ee7b7; width:64px; height:64px; font-size:28px; margin:0 auto 20px; border:1px solid rgba(16,185,129,0.3);">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
                <div class="stat-card__value" style="color:#fff;" data-count="{{ $totalExams }}"></div>
                <div class="stat-card__label" style="color:#6ee7b7;">Ujian CBT</div>
                <p style="font-size:12px; color:rgba(255,255,255,0.5); margin-top:12px; line-height:1.5;">Evaluasi akademik terenkripsi dan otomatis</p>
            </div>

            {{-- Forum --}}
            <div class="stat-card" style="background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.1);">
                <div class="icon-circle" style="background:rgba(245,158,11,0.15); color:#fcd34d; width:64px; height:64px; font-size:28px; margin:0 auto 20px; border:1px solid rgba(245,158,11,0.3);">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div class="stat-card__value" style="color:#fff;" data-count="{{ $totalForumThreads }}"></div>
                <div class="stat-card__label" style="color:#fcd34d;">Diskusi Aktif</div>
                <p style="font-size:12px; color:rgba(255,255,255,0.5); margin-top:12px; line-height:1.5;">Forum kolaborasi tanya-jawab lintas sekolah</p>
            </div>

            {{-- Training --}}
            <div class="stat-card" style="background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.1);">
                <div class="icon-circle" style="background:rgba(168,85,247,0.15); color:#d8b4fe; width:64px; height:64px; font-size:28px; margin:0 auto 20px; border:1px solid rgba(168,85,247,0.3);">
                    <i class="fa-solid fa-book-open-reader"></i>
                </div>
                <div class="stat-card__value" style="color:#fff;" data-count="{{ \App\Models\TrainingModule::published()->count() }}"></div>
                <div class="stat-card__label" style="color:#d8b4fe;">Pelatihan</div>
                <p style="font-size:12px; color:rgba(255,255,255,0.5); margin-top:12px; line-height:1.5;">Modul pengembangan diri untuk guru dan siswa</p>
            </div>
        </div>

        {{-- Animated Marquee --}}
        <div style="background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.05); border-radius:100px; padding:8px; overflow:hidden;" data-aos="fade-up" data-aos-delay="200">
            <div class="marquee-track">
                <div class="marquee-content" style="animation: marqueeScroll 25s linear infinite;">
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Absensi RFID & GPS</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Learning Management System</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Computer Based Test</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Raport Digital E-Rapor</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> E-Konseling BK</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Sistem Reputasi & Point</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Portal Orang Tua</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Jurnal PKL Industri</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Jejaring Alumni</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Penggajian & Payroll</div>
                    
                    {{-- Duplicate for seamless loop --}}
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Absensi RFID & GPS</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Learning Management System</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Computer Based Test</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> Raport Digital E-Rapor</div>
                    <div class="marquee-item" style="background:transparent; border-color:rgba(255,255,255,0.1); color:#fff;"><i class="fa-solid fa-check" style="color:var(--cyan);"></i> E-Konseling BK</div>
                </div>
            </div>
        </div>
    </div>
</section>
