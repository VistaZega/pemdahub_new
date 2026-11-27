@extends(request()->routeIs('guru.*') ? 'layouts.guru' : 'layouts.admin')

@section('title', 'DNA Akademik Siswa 360°')

@section('content')
@php
    $badgeStyles = [
        'logic'         => 'bg-sky-50 border-sky-300 text-sky-900',
        'communication' => 'bg-emerald-50 border-emerald-300 text-emerald-900',
        'technical'     => 'bg-indigo-50 border-indigo-300 text-indigo-900',
        'social'        => 'bg-amber-50 border-amber-300 text-amber-900',
        'creative'      => 'bg-purple-50 border-purple-300 text-purple-900',
        'discipline'    => 'bg-rose-50 border-rose-300 text-rose-900',
    ];
@endphp

<div class="space-y-5" x-data="{ showGuide: false, showMethod: false }">
    {{-- Header Institusional (Solid Slate, Tanpa Gradien/Ornamen Berlebih) --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl px-5 py-5 sm:px-6 text-white">
        <div class="flex items-center text-xs text-slate-300 font-medium mb-2.5 gap-2">
            <a href="{{ request()->routeIs('guru.*') ? route('guru.dashboard') : route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
            <span class="text-slate-600">/</span>
            <span class="text-sky-300 font-semibold">DNA Akademik Siswa 360°</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
            <div class="space-y-1.5">
                <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
                    <i class="fas fa-fingerprint text-sky-400"></i>
                    <span>DNA Akademik Siswa 360°</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Pemetaan potensi belajar siswa berbasis data nyata PembdaHUB (nilai per mata pelajaran, LMS, ujian CBT,
                    presensi RFID, tugas akhir/PKL, prestasi, dan cita-cita) beserta analisis personal ClassAI.
                </p>

                {{-- Strip Metodologi Ringkas --}}
                <div class="mt-3 pt-3 border-t border-slate-700/60 flex flex-wrap items-center gap-x-5 gap-y-2">
                    <span class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mr-1">Metodologi:</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-300">
                        <i class="fas fa-database text-sky-500 text-[10px]"></i> 6 Sumber Data Terverifikasi
                    </span>
                    <span class="text-slate-700">·</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-300">
                        <i class="fas fa-chart-radar text-emerald-400 text-[10px]"></i> 6 Sumbu Dimensi
                    </span>
                    <span class="text-slate-700">·</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-300">
                        <i class="fas fa-robot text-amber-400 text-[10px]"></i> ClassAI Synthesis
                    </span>
                    <span class="text-slate-700">·</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-300">
                        <i class="fas fa-fingerprint text-rose-400 text-[10px]"></i> Dual-Profil Belajar
                    </span>
                    <button type="button" @click="showMethod = !showMethod"
                            class="ml-1 inline-flex items-center gap-1 text-[10px] text-sky-400 hover:text-sky-300 font-semibold transition underline underline-offset-2">
                        <span x-text="showMethod ? 'Sembunyikan' : 'Lihat detail metodologi'"></span>
                        <i :class="showMethod ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-[8px]"></i>
                    </button>
                </div>
            </div>

            <button type="button" @click="showGuide = !showGuide"
                    class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-100 text-xs font-semibold transition inline-flex items-center gap-2 shrink-0 self-start">
                <i class="fas fa-book-open text-sky-400"></i>
                <span x-text="showGuide ? 'Tutup Panduan & Kamus' : 'Panduan & Kamus 6 Profil'"></span>
            </button>
        </div>
    </div>

    {{-- Panel Metodologi Lengkap (Toggle) --}}
    <div x-show="showMethod" x-cloak x-transition
         class="bg-white border border-slate-200 rounded-xl overflow-hidden text-xs">

        {{-- Judul Panel --}}
        <div class="bg-slate-900 px-5 py-3 flex items-center gap-2.5">
            <i class="fas fa-microscope text-sky-400"></i>
            <span class="text-white font-bold text-sm tracking-tight">Metodologi, Pendekatan & Teknik — DNA Akademik Siswa 360°</span>
        </div>

        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Kolom 1: Metodologi --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-200">
                    <div class="w-7 h-7 rounded-lg bg-sky-50 border border-sky-200 flex items-center justify-center shrink-0">
                        <i class="fas fa-database text-sky-600 text-[11px]"></i>
                    </div>
                    <span class="font-bold text-slate-800 text-[12px] uppercase tracking-wide">Metodologi Pengumpulan Data</span>
                </div>

                <div class="space-y-2.5 text-slate-600 leading-relaxed">
                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded bg-sky-100 text-sky-700 font-bold flex items-center justify-center shrink-0 text-[10px]">1</span>
                        <div>
                            <p class="font-semibold text-slate-700">Nilai Mata Pelajaran (Rapor Digital)</p>
                            <p class="text-slate-500 mt-0.5">Rata-rata nilai per mapel dari tabel <code class="bg-slate-100 px-1 rounded">grade_entries</code>. Bobot tertinggi — mencerminkan capaian kognitif resmi.</p>
                        </div>
                    </div>
                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded bg-sky-100 text-sky-700 font-bold flex items-center justify-center shrink-0 text-[10px]">2</span>
                        <div>
                            <p class="font-semibold text-slate-700">Aktivitas LMS (Keterlibatan Digital)</p>
                            <p class="text-slate-500 mt-0.5">Partisipasi kursus, penyelesaian modul, dan skor kuis dari sistem LMS internal. Mengukur kemandirian belajar.</p>
                        </div>
                    </div>
                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded bg-sky-100 text-sky-700 font-bold flex items-center justify-center shrink-0 text-[10px]">3</span>
                        <div>
                            <p class="font-semibold text-slate-700">Ujian CBT (Penilaian Berbasis Komputer)</p>
                            <p class="text-slate-500 mt-0.5">Skor ujian dari sistem CBT yang terpisah dari penilaian guru. Memberikan data kognitif yang lebih objektif.</p>
                        </div>
                    </div>
                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded bg-sky-100 text-sky-700 font-bold flex items-center justify-center shrink-0 text-[10px]">4</span>
                        <div>
                            <p class="font-semibold text-slate-700">Presensi RFID (Disiplin Kehadiran)</p>
                            <p class="text-slate-500 mt-0.5">Persentase kehadiran via kartu RFID. Berkontribusi pada Sumbu Disiplin-Karakter sebagai indikator tanggung jawab.</p>
                        </div>
                    </div>
                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded bg-sky-100 text-sky-700 font-bold flex items-center justify-center shrink-0 text-[10px]">5</span>
                        <div>
                            <p class="font-semibold text-slate-700">Tugas Akhir / Logbook PKL</p>
                            <p class="text-slate-500 mt-0.5">Proyek akhir SMK atau penelitian SMA, termasuk jurnal harian PKL di industri. Mengukur kompetensi terapan nyata.</p>
                        </div>
                    </div>
                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded bg-sky-100 text-sky-700 font-bold flex items-center justify-center shrink-0 text-[10px]">6</span>
                        <div>
                            <p class="font-semibold text-slate-700">Prestasi & Rekam Kepribadian</p>
                            <p class="text-slate-500 mt-0.5">Penghargaan lomba, kegiatan ekskul, catatan konseling BK, serta Minat & Cita-Cita dari formulir diagnostik siswa.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom 2: Pendekatan Analisis --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-200">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0">
                        <i class="fas fa-chart-network text-emerald-600 text-[11px]"></i>
                    </div>
                    <span class="font-bold text-slate-800 text-[12px] uppercase tracking-wide">Pendekatan Analisis</span>
                </div>

                <div class="space-y-3 text-slate-600 leading-relaxed">
                    <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                        <p class="font-semibold text-emerald-800 mb-1"><i class="fas fa-layer-group mr-1.5"></i>6 Sumbu Dimensi Belajar</p>
                        <ul class="space-y-1 text-slate-600 pl-1">
                            <li><span class="font-medium text-slate-700">① Kognitif-Saintifik</span> — Matematika, IPA, Fisika, Kimia, Biologi</li>
                            <li><span class="font-medium text-slate-700">② Teknis-Terapan</span> — Mapel Kejuruan, Praktik Bengkel SMK</li>
                            <li><span class="font-medium text-slate-700">③ Linguistik-Komunikatif</span> — B. Indonesia, B. Inggris, Sastra</li>
                            <li><span class="font-medium text-slate-700">④ Sosial-Kepemimpinan</span> — IPS, PKn, Sejarah, Ekonomi</li>
                            <li><span class="font-medium text-slate-700">⑤ Kreatif-Inovatif</span> — Seni, Desain, Informatika, TIK</li>
                            <li><span class="font-medium text-slate-700">⑥ Disiplin-Karakter</span> — Kehadiran, Sikap, Apresiasi BK</li>
                        </ul>
                    </div>

                    <div class="p-3 rounded-lg bg-sky-50 border border-sky-100">
                        <p class="font-semibold text-sky-800 mb-1"><i class="fas fa-balance-scale mr-1.5"></i>Pembobotan Relatif Sekolah</p>
                        <p class="text-slate-600">Nilai siswa dibandingkan terhadap <strong>rata-rata sekolah</strong> per mapel (<em>school-relative delta</em>), bukan skala absolut 0–100. Ini membuat profil bermakna di konteks lingkungan belajar nyata siswa.</p>
                    </div>

                    <div class="p-3 rounded-lg bg-amber-50 border border-amber-100">
                        <p class="font-semibold text-amber-800 mb-1"><i class="fas fa-dna mr-1.5"></i>Sistem Dual-Profil</p>
                        <p class="text-slate-600">Setiap siswa memiliki <strong>Profil Dominan</strong> (dimensi tertinggi) + <strong>Profil Pendukung</strong> (dimensi kedua terkuat). Kombinasi ini menghasilkan narasi yang lebih akurat daripada label tunggal.</p>
                    </div>
                </div>
            </div>

            {{-- Kolom 3: Teknik & Istilah --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-200">
                    <div class="w-7 h-7 rounded-lg bg-violet-50 border border-violet-200 flex items-center justify-center shrink-0">
                        <i class="fas fa-cogs text-violet-600 text-[11px]"></i>
                    </div>
                    <span class="font-bold text-slate-800 text-[12px] uppercase tracking-wide">Teknik & Kamus Istilah</span>
                </div>

                <div class="space-y-2.5 text-slate-600 leading-relaxed">
                    <div class="border-l-2 border-violet-300 pl-3">
                        <p class="font-semibold text-slate-700">ClassAI Synthesis</p>
                        <p class="text-slate-500 mt-0.5">Narasi personal siswa dibangkitkan oleh model bahasa besar (ClassAI) berdasarkan data terstruktur dari 6 sumber di atas. Bukan teks generik — setiap narasi unik per profil data siswa.</p>
                    </div>
                    <div class="border-l-2 border-sky-300 pl-3">
                        <p class="font-semibold text-slate-700">Dossier 360°</p>
                        <p class="text-slate-500 mt-0.5"><em>Dossier</em> (dari bahasa Prancis) = berkas dokumen resmi seseorang. <em>360°</em> = pandangan dari semua dimensi. Bermakna: <strong>Berkas Profil Akademik Menyeluruh</strong> seorang siswa yang dilihat dari segala sudut data.</p>
                    </div>
                    <div class="border-l-2 border-emerald-300 pl-3">
                        <p class="font-semibold text-slate-700">Kelengkapan Sumber Data (%)</p>
                        <p class="text-slate-500 mt-0.5">Persentase dari 6 sumber data yang sudah tersedia untuk siswa tersebut. Semakin tinggi, semakin akurat dan terpercaya hasil analisisnya.</p>
                    </div>
                    <div class="border-l-2 border-amber-300 pl-3">
                        <p class="font-semibold text-slate-700">Mapel Unggulan</p>
                        <p class="text-slate-500 mt-0.5">Mata pelajaran dengan skor relatif tertinggi dibandingkan rata-rata sekolah. Bukan sekadar nilai tertinggi absolut, melainkan keunggulan komparatif nyata.</p>
                    </div>
                    <div class="border-l-2 border-rose-300 pl-3">
                        <p class="font-semibold text-slate-700">Fingerprint Cache</p>
                        <p class="text-slate-500 mt-0.5">Analisis ClassAI disimpan di basis data dan hanya diperbarui jika "sidik data" siswa berubah (ada nilai baru, prestasi baru, dll). Mencegah pemborosan penggunaan API.</p>
                    </div>

                    <div class="mt-3 pt-3 border-t border-slate-200 bg-slate-50 rounded-lg p-3">
                        <p class="font-semibold text-slate-700 mb-1"><i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i>Catatan Penting</p>
                        <p class="text-slate-500">Profil ini bersifat <strong>informatif dan rekomendatif</strong>, bukan penilaian final atau vonis kemampuan. Interpretasi tetap membutuhkan pertimbangan profesional guru dan wali kelas.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Panduan & Kamus 6 Profil Belajar (Terlipat Secara Default agar Daftar Siswa Langsung Terlihat) --}}
    <div x-show="showGuide" x-cloak x-transition class="bg-white border border-slate-300 rounded-xl overflow-hidden text-xs">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200 border-b border-slate-200 bg-slate-50/60">
            <div class="p-4 space-y-1">
                <p class="font-bold text-slate-900">Tujuan Pemetaan</p>
                <p class="text-slate-600 leading-relaxed">
                    Membantu guru BK, wali kelas, kepala sekolah, dan yayasan memahami profil keunggulan spesifik setiap siswa secara menyeluruh sebagai dasar bimbingan studi dan karier.
                </p>
            </div>
            <div class="p-4 space-y-1">
                <p class="font-bold text-slate-900">Sumber Data Nyata 360° &amp; ClassAI</p>
                <p class="text-slate-600 leading-relaxed">
                    Mengolah nilai per mapel, tugas LMS, CBT, presensi RFID, prestasi, karya/PKL, dan cita-cita siswa. Profil bakat dihitung dari 5 sumbu kompetensi tanpa tertimpa angka presensi.
                </p>
            </div>
            <div class="p-4 space-y-1">
                <p class="font-bold text-slate-900">Catatan Anti-Salah Tafsir</p>
                <p class="text-slate-600 leading-relaxed">
                    Pemetaan ini berfungsi sebagai alat bantu pendampingan belajar yang bersifat dinamis mengikuti perkembangan siswa, bukan label permanen atau penentu tunggal masa depan.
                </p>
            </div>
        </div>

        <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-brain text-sky-700"></i> 1. Analis Logis &amp; Pemecah Masalah</div>
                <p class="text-slate-600 leading-relaxed">Menonjol pada penalaran matematika, sains, pola data, dan penyelesaian soal hitungan secara runtut. Arah: Teknologi Informasi, Rekayasa, Riset Sains, Keuangan.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-comments text-emerald-700"></i> 2. Komunikator &amp; Artikulator Bahasa</div>
                <p class="text-slate-600 leading-relaxed">Cakap literasi bacaan, tata bahasa, menulis argumen terstruktur, dan berbicara persuasif. Arah: Komunikasi Publik, Hukum, Pendidikan Bahasa, Diplomasi.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-wrench text-indigo-700"></i> 3. Praktisi Teknis &amp; Rekayasa Terapan</div>
                <p class="text-slate-600 leading-relaxed">Unggul dalam kerja praktik bengkel atau laboratorium, perakitan perangkat, jaringan, dan otomasi. Arah: Teknik Industri, Otomotif, Elektronika, Jaringan Komputer.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-users text-amber-700"></i> 4. Pemimpin Sosial &amp; Kolaborator</div>
                <p class="text-slate-600 leading-relaxed">Cakap memimpin tim, berempati tinggi, aktif di kepengurusan ekstrakurikuler, dan kuat pada rumpun sosial-ekonomi. Arah: Manajemen, Psikologi, Kebijakan Publik.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-palette text-purple-700"></i> 5. Kreator Visual &amp; Perancang Inovatif</div>
                <p class="text-slate-600 leading-relaxed">Peka terhadap estetika, rancangan grafis, multimedia, dan karya orisinal. Arah: Desain Komunikasi Visual, Arsitektur, Produksi Media Digital.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-clock text-rose-700"></i> 6. Pelaksana Tekun &amp; Berdisiplin Tinggi</div>
                <p class="text-slate-600 leading-relaxed">Konsisten menjaga kehadiran sekolah dan ketepatan waktu pengumpulan tugas. Arah: Tata Kelola Perkantoran, Logistik, Akuntansi, Audit, Sekolah Kedinasan.</p>
            </div>
        </div>
    </div>

    {{-- Bar Filter Pencarian --}}
    <div class="bg-white border border-slate-300 rounded-xl p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end text-xs">
            @if(auth()->user()->isSuperAdmin() && isset($schools))
            <div class="sm:col-span-3">
                <label class="block font-bold text-slate-700 mb-1">Unit Sekolah</label>
                <select name="school_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white font-semibold text-slate-800 focus:outline-none focus:border-sky-600">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="{{ auth()->user()->isSuperAdmin() && isset($schools) ? 'sm:col-span-3' : 'sm:col-span-4' }}">
                <label class="block font-bold text-slate-700 mb-1">Rombel / Kelas</label>
                <select name="classroom_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white font-semibold text-slate-800 focus:outline-none focus:border-sky-600">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                        {{ $cls->class_name ?? $cls->name }} {{ $cls->school ? '(' . ($cls->school->short_name ?? $cls->school->name) . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="{{ auth()->user()->isSuperAdmin() && isset($schools) ? 'sm:col-span-6' : 'sm:col-span-8' }} flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block font-bold text-slate-700 mb-1">Cari Peserta Didik (Nama / NISN / NIS)</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama, NISN, atau NIS..."
                               class="w-full pl-8 pr-3 py-2 rounded-lg border border-slate-300 bg-white font-medium text-slate-800 focus:outline-none focus:border-sky-600">
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold transition inline-flex items-center gap-1.5">
                    <i class="fas fa-filter text-[10px]"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ url()->current() }}" class="px-3 py-2 bg-white hover:bg-slate-100 text-slate-700 rounded-lg font-semibold transition border border-slate-300" title="Atur Ulang Filter">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Direktori Evaluasi Siswa --}}
    <div class="bg-white border border-slate-300 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">No</th>
                        <th class="px-4 py-3">Peserta Didik</th>
                        <th class="px-4 py-3">Kelas &amp; Unit Sekolah</th>
                        <th class="px-4 py-3">Profil Dominan Belajar</th>
                        <th class="px-4 py-3">Mapel Unggulan</th>
                        <th class="px-4 py-3 text-center">Kelengkapan Data</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php $dnaService = app(\App\Services\StudentDnaService::class); @endphp
                    @forelse($students as $idx => $s)
                    @php
                        $dna = $dnaService->analyze($s);
                        $classroomName = $s->currentClassroom->first()->class_name
                            ?? $s->currentClassroom->first()->name
                            ?? ($s->classrooms->first()->class_name ?? ($s->classroom->class_name ?? '-'));
                        $pKey = $dna['archetype']['primary_key'] ?? 'technical';
                        $pStyle = $badgeStyles[$pKey] ?? 'bg-slate-100 border-slate-300 text-slate-800';
                        $topOne = $dna['subject_profile']['top_subjects'][0] ?? null;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-4 py-3 text-center font-bold text-slate-400 tabular-nums">{{ $students->firstItem() + $idx }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $s->photo_url }}"
                                     class="w-9 h-9 rounded-lg object-cover border border-slate-200 bg-slate-100 shrink-0"
                                     alt="{{ $s->full_name }}"
                                     onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-xs sm:text-sm leading-snug truncate">{{ $s->full_name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono tabular-nums">NISN: {{ $s->nisn ?: '-' }} · NIS: {{ $s->formatted_nis ?: ($s->nis ?: '-') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-800">{{ $classroomName }}</p>
                            <p class="text-[11px] text-slate-500">
                                {{ $s->school->name ?? '-' }}
                                @if(!empty($dna['vocational_major']))
                                · <span class="font-semibold text-amber-800">{{ $dna['vocational_major']['code'] }}</span>
                                @endif
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border text-[11px] font-bold {{ $pStyle }}">
                                <i class="fas {{ $dna['archetype']['badge_icon'] }} text-[10px]"></i>
                                <span>{{ $dna['archetype']['title'] }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($topOne)
                            <div class="font-semibold text-slate-800 truncate max-w-[190px]">{{ $topOne['subject_name'] }}</div>
                            <div class="text-[11px] text-emerald-700 font-bold tabular-nums">Nilai {{ $topOne['score'] }}</div>
                            @else
                            <span class="text-slate-400">Belum ada nilai</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex flex-col items-center">
                                <span class="text-[11px] font-bold text-slate-700 tabular-nums">{{ $dna['confidence_score'] }}%</span>
                                <div class="w-20 bg-slate-200 rounded-full h-1.5 mt-1 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-sky-600" style="width: {{ $dna['confidence_score'] }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.show', $s) : route('admin.dna.show', $s) }}"
                                   class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition inline-flex items-center gap-1.5">
                                    <i class="fas fa-folder-open text-[10px]"></i>
                                    <span>Dossier 360°</span>
                                </a>
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.pdf', $s) : route('admin.dna.pdf', $s) }}"
                                   class="px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300" title="Cetak Rapor DNA (PDF)">
                                    <i class="fas fa-file-pdf text-rose-600"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center">
                            <div class="max-w-md mx-auto space-y-2.5">
                                <p class="text-sm font-bold text-slate-900">Tidak Ada Peserta Didik Ditemukan</p>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Tidak ada data siswa yang cocok dengan filter kelas atau kata kunci pencarian saat ini.
                                </p>
                                <a href="{{ url()->current() }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold text-xs transition">
                                    <i class="fas fa-undo"></i>
                                    <span>Tampilkan Semua Siswa</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
