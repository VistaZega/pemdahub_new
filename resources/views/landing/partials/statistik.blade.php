{{-- STATISTIK — Bold Indigo Theme --}}
<section id="statistik" class="section" style="background: linear-gradient(135deg, #1e1b4b, #2d2a6e);">
    <div class="fw">
        <div style="text-align:center; margin-bottom:48px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center; margin-bottom:16px;">
                <div class="section-label-dot pulse" style="background:#f59e0b;"></div>
                <span class="section-label-text" style="color:#fbbf24;">Kekuatan Platform Kami</span>
            </div>
            <h2 style="color:#fff; font-size:clamp(24px,3vw,36px); font-weight:800; letter-spacing:-0.02em;">
                Lebih dari 5 Dekade Mencerdaskan <span style="background:linear-gradient(135deg,#fbbf24,#f59e0b); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">Bangsa</span>
            </h2>
        </div>

        {{-- Baris 1: Core Stats --}}
        <div class="bento bento-4" data-aos="fade-up" data-aos-delay="100" style="margin-bottom: 16px;">
            {{-- Stat 1: Tahun Berdiri --}}
            <div class="stat-card" style="background:rgba(255,255,255,0.06); border-color:rgba(255,255,255,0.1);">
                <div class="stat-card__value" style="background:linear-gradient(135deg,#fbbf24,#f59e0b); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;" data-count="1970"></div>
                <div class="stat-card__label">Tahun Berdiri</div>
                <div class="stat-card__line" style="background:linear-gradient(90deg,#f59e0b,#fbbf24);"></div>
            </div>

            {{-- Stat 2: Total Siswa --}}
            <div class="stat-card" style="background:rgba(16,185,129,0.12); border-color:rgba(16,185,129,0.25);">
                <div class="stat-card__value" style="color:#6ee7b7;" data-count="{{ $totalStudents }}" data-suffix="+"></div>
                <div class="stat-card__label">Total Siswa Aktif</div>
                <div class="stat-card__line" style="background:linear-gradient(90deg,#10b981,#6ee7b7);"></div>
            </div>

            {{-- Stat 3: Unit Sekolah --}}
            <div class="stat-card" style="background:rgba(99,102,241,0.15); border-color:rgba(99,102,241,0.3);">
                <div class="stat-card__value" style="color:#a5b4fc;" data-count="{{ $totalSchools }}"></div>
                <div class="stat-card__label">Unit Sekolah</div>
                <div class="stat-card__line" style="background:linear-gradient(90deg,#6366f1,#a5b4fc);"></div>
            </div>

            {{-- Stat 4: Tenaga Pendidik --}}
            <div class="stat-card" style="background:rgba(139,92,246,0.12); border-color:rgba(139,92,246,0.25);">
                <div class="stat-card__value" style="color:#c4b5fd;" data-count="{{ $totalTeachers }}" data-suffix="+"></div>
                <div class="stat-card__label">Tenaga Pendidik</div>
                <div class="stat-card__line" style="background:linear-gradient(90deg,#8b5cf6,#c4b5fd);"></div>
            </div>
        </div>

        {{-- Baris 2: Ekosistem Stats --}}
        <div class="bento bento-4" data-aos="fade-up" data-aos-delay="200">
            {{-- Stat 5: Alumni --}}
            <div class="stat-card" style="background:rgba(6,182,212,0.12); border-color:rgba(6,182,212,0.25);">
                <div class="stat-card__value" style="color:#67e8f9;" data-count="{{ $totalAlumni }}" data-suffix="+"></div>
                <div class="stat-card__label">Jaringan Alumni</div>
                <div class="stat-card__line" style="background:linear-gradient(90deg,#06b6d4,#67e8f9);"></div>
            </div>

            {{-- Stat 6: LMS --}}
            <div class="stat-card" style="background:rgba(239,68,68,0.12); border-color:rgba(239,68,68,0.25);">
                <div class="stat-card__value" style="color:#fca5a5;" data-count="{{ $totalCourses }}" data-suffix="+"></div>
                <div class="stat-card__label">Kursus LMS Digital</div>
                <div class="stat-card__line" style="background:linear-gradient(90deg,#ef4444,#fca5a5);"></div>
            </div>

            {{-- Stat 7: CBT --}}
            <div class="stat-card" style="background:rgba(59,130,246,0.12); border-color:rgba(59,130,246,0.25);">
                <div class="stat-card__value" style="color:#93c5fd;" data-count="{{ $totalExams }}" data-suffix="+"></div>
                <div class="stat-card__label">Ujian CBT Sukses</div>
                <div class="stat-card__line" style="background:linear-gradient(90deg,#3b82f6,#93c5fd);"></div>
            </div>
            
            {{-- Stat 8: Program --}}
            <div class="stat-card" style="background:rgba(245,158,11,0.12); border-color:rgba(245,158,11,0.25);">
                <div class="stat-card__value" style="color:#fde68a;" data-count="5"></div>
                <div class="stat-card__label">Program Keahlian SMK</div>
                <div class="stat-card__line" style="background:linear-gradient(90deg,#f59e0b,#fde68a);"></div>
            </div>
        </div>
    </div>
</section>

<style>
.stat-card {
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius);
    padding: 36px 28px;
    text-align: center;
    transition: all 0.35s cubic-bezier(.4,0,.2,1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    position: relative;
}
.stat-card:hover {
    transform: translateY(-4px);
    filter: brightness(1.25);
    box-shadow: 0 12px 32px rgba(0,0,0,0.25);
}
.stat-card__value {
    font-size: clamp(40px, 5vw, 56px);
    font-weight: 900;
    letter-spacing: -0.03em;
    line-height: 1;
}
.stat-card__label {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(255,255,255,0.7);
    margin-top: 10px;
}
.stat-card__line {
    width: 32px;
    height: 2px;
    border-radius: 1px;
    margin: 12px auto 0;
}
</style>
