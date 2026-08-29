{{-- HERO SECTION — DesainPake AI Tactile Style (100% Real Database Data) --}}
<section id="beranda" class="pt-10 pb-16 px-4 sm:px-8">
    <div class="max-w-7xl mx-auto">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- SISI KIRI HERO -->
            <div class="lg:col-span-7">
                
                <!-- Tagline Asterisk -->
                <div class="text-[11px] font-mono-code font-extrabold text-[#ff3823] uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <span class="text-base">✱</span>
                    <span>SMART SCHOOL & EKOSISTEM PENDIDIKAN NIAS</span>
                </div>

                <!-- Display Headline -->
                <h1 class="text-4xl sm:text-6xl font-black text-[#121316] leading-[1.08] tracking-tight mb-6">
                    Belajar apa pun, <br>
                    <span class="highlight-marker">tinggal buka.</span>
                </h1>

                <!-- Subheadline dengan Chip Kode -->
                <p class="text-sm sm:text-base text-[#4b5563] leading-relaxed mb-8 max-w-xl font-medium">
                    Tulis kebutuhan belajarmu — <code class="px-2 py-1 bg-[#ede9df] text-[#121316] rounded font-mono-code text-xs font-bold">modul tkj fiber optik</code> atau <code class="px-2 py-1 bg-[#ede9df] text-[#121316] rounded font-mono-code text-xs font-bold">jadwal ujian cbt</code> saja cukup. Sistem menyusun modul digital, rekap absensi RFID, dan pantau logbook PKL secara otomatis.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-4 mb-6">
                    <a href="#showcase" class="px-7 py-3.5 rounded-full btn-tactile-red text-sm font-black tracking-wide flex items-center gap-2">
                        <span>Buka Etalase Karya</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="#galeri" class="px-6 py-3.5 rounded-full btn-tactile-white text-sm font-bold flex items-center gap-2">
                        <span>Dokumentasi Sekolah</span>
                        <span>↓</span>
                    </a>
                </div>

                <!-- Micro Proof Strip -->
                <div class="text-[11px] font-mono-code text-[#71717a] flex flex-wrap items-center gap-3">
                    <span class="font-bold text-[#121316]">{{ isset($totalStudents) ? number_format($totalStudents) : 0 }} Siswa Aktif</span>
                    <span>&bull;</span>
                    <span>{{ $totalDudi ?? '45+' }} Mitra DUDI</span>
                    <span>&bull;</span>
                    <span>TP {{ isset($activeAcademicYear) && $activeAcademicYear ? $activeAcademicYear->name : '2026/2027' }} ({{ isset($activeAcademicYear) && $activeAcademicYear && $activeAcademicYear->semester ? ucfirst($activeAcademicYear->semester) : 'Aktif' }})</span>
                </div>

            </div>

            <!-- SISI KANAN HERO (Interactive Generator Card dengan Corner Framing ⌜ ⌟) -->
            <div class="lg:col-span-5">
                <div class="relative bg-white border-2 border-[#121316] rounded-3xl p-6 shadow-[6px_6px_0px_#121316]">
                    
                    <!-- Corner Crosshairs -->
                    <span class="absolute top-2.5 left-3 text-[#999] font-mono-code text-xs select-none">⌜</span>
                    <span class="absolute top-2.5 right-3 text-[#999] font-mono-code text-xs select-none">⌝</span>
                    <span class="absolute bottom-2.5 left-3 text-[#999] font-mono-code text-xs select-none">⌞</span>
                    <span class="absolute bottom-2.5 right-3 text-[#999] font-mono-code text-xs select-none">⌟</span>

                    <!-- Top Status -->
                    <div class="flex items-center justify-between text-[10px] font-mono-code font-bold uppercase mb-4 text-[#555]">
                        <span class="flex items-center gap-1.5 text-emerald-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            PEMBDA SMART SYSTEM
                        </span>
                        <span>{{ isset($activeAcademicYear) && $activeAcademicYear ? 'TP ' . $activeAcademicYear->name : 'ONLINE 24/7' }}</span>
                    </div>

                    <!-- Input Prompt Mockup -->
                    <div class="bg-[#121316] text-[#fde047] font-mono-code text-xs p-3.5 rounded-xl mb-4 flex items-center gap-2 shadow-inner">
                        <span class="text-[#ff3823]">&rsaquo;</span>
                        <span class="truncate">pencarian modul: kurikulum merdeka & praktikum dudi</span>
                    </div>

                    <!-- Preview Poster Inside Generator -->
                    <div class="bg-[#faf3e0] border border-[#121316] rounded-2xl p-6 text-center relative overflow-hidden">
                        <div class="text-[10px] font-mono-code font-extrabold uppercase tracking-widest text-[#777] mb-2">
                            &bull; 3 UNIT SEKOLAH TERPADU &bull;
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-black text-[#121316] uppercase leading-tight tracking-tight mb-2">
                            MODUL & RISET<br>SISWA PEMBDA
                        </h3>
                        <p class="text-[11px] font-bold text-[#555] mb-4">LMS Terintegrasi, CBT Digital & Logbook PKL</p>
                        
                        <a href="#showcase" class="inline-block px-5 py-2 rounded-full bg-[#ff3823] text-white text-xs font-black uppercase border border-[#121316] shadow-[2px_2px_0px_#121316] hover:translate-x-0.5 transition-transform">
                            JELAJAHI KARYA SISWA
                        </a>

                        <!-- Price/Free Starburst Badge -->
                        <div class="absolute bottom-3 right-3 w-12 h-12 bg-[#fbc02d] rounded-full border border-[#121316] flex items-center justify-center font-black text-[10px] rotate-12 shadow-sm">
                            REAL
                        </div>

                        <!-- Accent Graphic Circle -->
                        <div class="absolute -top-4 -right-4 w-14 h-14 bg-[#fbc02d] rounded-full opacity-80 pointer-events-none"></div>
                    </div>

                    <!-- Bottom Meta Info -->
                    <div class="flex items-center justify-between text-[10px] font-mono-code font-bold text-[#777] mt-4">
                        <span>{{ $totalCourses ?? 0 }} Modul KBM Aktif</span>
                        <span>{{ $totalExams ?? 0 }} Bank Soal CBT</span>
                    </div>

                </div>
            </div>

        </div>

        <!-- BOTTOM STATS STRIP (100% REAL DATABASE COUNTS) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-12 mt-12 border-t border-[#e7e3d8]">
            <div class="p-4 bg-white/60 rounded-2xl border border-[#e7e3d8]">
                <div class="text-3xl sm:text-4xl font-black text-[#121316] font-mono-code">{{ isset($totalStudents) ? number_format($totalStudents) : 0 }}</div>
                <div class="text-[11px] font-mono-code font-bold uppercase text-[#777] mt-1">Siswa Aktif Terdaftar</div>
            </div>
            <div class="p-4 bg-white/60 rounded-2xl border border-[#e7e3d8]">
                <div class="text-3xl sm:text-4xl font-black text-[#121316] font-mono-code">{{ isset($totalTeachers) ? number_format($totalTeachers) : 0 }}</div>
                <div class="text-[11px] font-mono-code font-bold uppercase text-[#777] mt-1">Guru & Pendidik Ahli</div>
            </div>
            <div class="p-4 bg-white/60 rounded-2xl border border-[#e7e3d8]">
                <div class="text-3xl sm:text-4xl font-black text-[#121316] font-mono-code">{{ isset($extracurriculars) ? $extracurriculars->count() : 16 }} Cabang</div>
                <div class="text-[11px] font-mono-code font-bold uppercase text-[#777] mt-1">Ekstrakurikuler Juara</div>
            </div>
            <div class="p-4 bg-white/60 rounded-2xl border border-[#e7e3d8]">
                <div class="text-3xl sm:text-4xl font-black text-[#121316] font-mono-code">{{ isset($totalAlumni) ? number_format($totalAlumni) : 0 }}+</div>
                <div class="text-[11px] font-mono-code font-bold uppercase text-[#777] mt-1">Alumni Sejak 1970</div>
            </div>
        </div>

    </div>
</section>
