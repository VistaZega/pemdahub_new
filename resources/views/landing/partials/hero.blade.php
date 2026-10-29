{{-- HERO SECTION — DesainPake AI Tactile Style x Surat Kabar Warta Yayasan (100% Real Database Data) --}}
<section id="beranda" class="pt-8 sm:pt-10 pb-16 px-4 sm:px-8">
    <div class="max-w-7xl mx-auto">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-stretch">
            
            <!-- SISI KIRI HERO (Branding Yayasan & Ekosistem Smart School) -->
            <div class="lg:col-span-6 flex flex-col justify-between">
                
                <div>
                    <!-- Tagline Asterisk -->
                    <div class="text-[11px] font-mono-code font-extrabold text-[#ff3823] uppercase tracking-wider mb-3.5 flex items-center gap-1.5">
                        <span class="text-base">✱</span>
                        <span>YAYASAN PERGURUAN PEMBDA NIAS &bull; SMART SCHOOL</span>
                    </div>

                    <!-- Display Headline (Berkelas & Bernas) -->
                    <h1 class="text-3xl sm:text-5xl xl:text-6xl font-black text-[#121316] leading-[1.08] tracking-tight mb-5">
                        Belajar apa pun bisa, <br>
                        <span class="highlight-marker">sistemnya siap membersamai.</span>
                    </h1>

                    <!-- Narasi Utama: Berkelas, Kekinian, Rendah Hati (2 Paragraf Mengalir) -->
                    <div class="space-y-3 mb-6 text-xs sm:text-[13.5px] text-[#4b5563] leading-relaxed font-medium max-w-xl">
                        <p>
                            Di <strong>Yayasan Perguruan PEMBDA Nias</strong>, kami meyakini bahwa setiap anak membawa keunikan serta potensi berharga yang patut dirawat dengan sepenuh hati. Tidak ada mimpi yang terlalu dini dan tiada cita-cita yang mustahil ketika ruang belajar dibangun atas dasar <strong>ketulusan, rasa ingin tahu yang sehat, serta kesempatan yang setara bagi semua</strong>.
                        </p>
                        <p>
                            Melalui <strong>tiga unit sekolah terpadu</strong> (SMPS Pembda 2, SMAS Pembda 1, dan SMKS Pembda Nias), kami menghadirkan ekosistem belajar yang membumi namun adaptif zaman — memadukan penguasaan <code class="px-2 py-0.5 bg-[#ede9df] text-[#121316] rounded font-mono-code text-[11px] font-bold">Riset Sains & Rekayasa</code>, eksplorasi <code class="px-2 py-0.5 bg-[#ede9df] text-[#121316] rounded font-mono-code text-[11px] font-bold">Teknologi Terapan IoT & AI</code>, hingga <code class="px-2 py-0.5 bg-[#ede9df] text-[#121316] rounded font-mono-code text-[11px] font-bold">16+ Ruang Minat & Bakat</code>. Bersama semangat luhur <em>'Maju Terus Pantang Mundur'</em>, kami hadir mengawal setiap langkahmu untuk tumbuh menjadi pribadi yang berilmu, berbudi pekerti mulia, dan siap memberi manfaat nyata bagi keluarga, Nias, serta bangsa.
                        </p>
                    </div>

                    <!-- Pilar Keunggulan 3 Unit Sekolah (Penyelaras Keseimbangan Tinggi Visual) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-6 max-w-xl">
                        <div class="bg-white/80 border-2 border-[#121316] rounded-xl p-2.5 shadow-[2px_2px_0px_#121316]">
                            <div class="text-[9px] font-mono-code font-bold text-[#ff3823] uppercase flex items-center gap-1 mb-0.5">
                                <i class="fa-solid fa-school text-[8px]"></i> 3 Unit Terpadu
                            </div>
                            <div class="text-xs font-black text-[#121316]">SMP &bull; SMA &bull; SMK</div>
                            <div class="text-[10px] font-mono-code text-[#71717a] mt-0.5">Satu Ekosistem Harmonis</div>
                        </div>

                        <div class="bg-white/80 border-2 border-[#121316] rounded-xl p-2.5 shadow-[2px_2px_0px_#121316]">
                            <div class="text-[9px] font-mono-code font-bold text-[#2563eb] uppercase flex items-center gap-1 mb-0.5">
                                <i class="fa-solid fa-microchip text-[8px]"></i> Praktik & Riset
                            </div>
                            <div class="text-xs font-black text-[#121316]">Kelas Industri & AI</div>
                            <div class="text-[10px] font-mono-code text-[#71717a] mt-0.5">{{ $totalDudi ?? '45+' }} Mitra Industri</div>
                        </div>

                        <div class="bg-white/80 border-2 border-[#121316] rounded-xl p-2.5 shadow-[2px_2px_0px_#121316]">
                            <div class="text-[9px] font-mono-code font-bold text-emerald-600 uppercase flex items-center gap-1 mb-0.5">
                                <i class="fa-solid fa-seedling text-[8px]"></i> Karakter & Bakat
                            </div>
                            <div class="text-xs font-black text-[#121316]">16+ Ekskul Juara</div>
                            <div class="text-[10px] font-mono-code text-[#71717a] mt-0.5">Asah Potensi Terbaik</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-full btn-tactile-red text-xs sm:text-sm font-black tracking-wide flex items-center gap-2">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span>Buka Dashboard</span>
                                <span>&rarr;</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-3 rounded-full btn-tactile-red text-xs sm:text-sm font-black tracking-wide flex items-center gap-2">
                                <span>Masuk Ruang Belajar</span>
                                <span>&rarr;</span>
                            </a>
                        @endauth

                        <a href="#showcase" class="px-5 py-3 rounded-full btn-tactile-white text-xs sm:text-sm font-bold flex items-center gap-2">
                            <span>Etalase Karya Siswa</span>
                            <span>↓</span>
                        </a>

                        @if(isset($activeWave) && $activeWave)
                            <a href="{{ route('psb.index') }}" class="px-4 py-3 rounded-full bg-[#fbc02d] text-[#121316] border border-[#121316] text-xs font-black shadow-[2px_2px_0px_#121316] hover:translate-x-0.5 transition-transform flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-600 animate-ping"></span>
                                <span>PSB TP {{ $activeAcademicYear->name ?? '2026/2027' }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Micro Proof Strip -->
                <div class="text-[11px] font-mono-code text-[#71717a] flex flex-wrap items-center gap-3 pt-4 border-t border-[#e7e3d8]/80">
                    <span class="font-bold text-[#121316]">{{ isset($totalStudents) ? number_format($totalStudents) : 0 }} Siswa Aktif</span>
                    <span>&bull;</span>
                    <span>{{ isset($totalTeachers) ? number_format($totalTeachers) : 0 }} Pendidik Ahli</span>
                    <span>&bull;</span>
                    <span>{{ $totalDudi ?? '45+' }} Mitra DUDI</span>
                    <span>&bull;</span>
                    <span>TP {{ isset($activeAcademicYear) && $activeAcademicYear ? $activeAcademicYear->name : '2026/2027' }} ({{ isset($activeAcademicYear) && $activeAcademicYear && $activeAcademicYear->semester ? ucfirst($activeAcademicYear->semester) : 'Aktif' }})</span>
                </div>

            </div>

            <!-- SISI KANAN HERO (Surat Kabar / Gazette Warta Yayasan Perguruan PEMBDA - Kertas Stensil x Kliping Putih) -->
            <div class="lg:col-span-6 flex flex-col">
                <div id="warta-pembda" class="relative bg-[#f4ebd9] border-2 border-[#121316] rounded-2xl p-5 sm:p-6 shadow-[6px_6px_0px_#121316] flex flex-col justify-between h-full transition-all">
                    
                    <!-- Corner Gazette Notations -->
                    <span class="absolute top-2 left-2.5 text-[#121316]/40 font-mono-code text-[11px] select-none">⌜</span>
                    <span class="absolute top-2 right-2.5 text-[#121316]/40 font-mono-code text-[11px] select-none">⌝</span>
                    <span class="absolute bottom-2 left-2.5 text-[#121316]/40 font-mono-code text-[11px] select-none">⌞</span>
                    <span class="absolute bottom-2 right-2.5 text-[#121316]/40 font-mono-code text-[11px] select-none">⌟</span>

                    <div>
                        <!-- Top Gazette Band / Header Bar -->
                        <div class="flex items-center justify-between text-[9px] font-mono-code font-bold uppercase tracking-wider text-[#554e3d] border-b border-[#121316]/25 pb-1.5 mb-2">
                            <span class="flex items-center gap-1.5 text-[#121316]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#ff3823] animate-pulse"></span>
                                EDISI STENSIL & PERS CIVITAS
                            </span>
                            <span class="text-[#6d6450]">PULAU NIAS &bull; SUMATERA UTARA</span>
                        </div>

                        <!-- Gazette Title / Masthead -->
                        <div class="text-center py-1">
                            <h2 class="font-newspaper text-2xl sm:text-3xl font-black text-[#121316] tracking-tight uppercase leading-none">
                                WARTA PERGURUAN PEMBDA
                            </h2>
                            <p class="text-[9px] font-mono-code uppercase tracking-widest text-[#554e3d] mt-1">
                                Lembaga Informasi & Publikasi Resmi Yayasan Perguruan PEMBDA Nias
                            </p>
                        </div>

                        <!-- Dateline Double-Rule Newspaper Border (Kertas Stensil Bar) -->
                        <div class="border-y-2 border-[#121316] bg-[#ebe1c7]/80 py-1 px-2 my-2.5 flex items-center justify-between text-[10px] font-mono-code font-bold uppercase text-[#121316]">
                            <span>VOL. XXVI &bull; NO. {{ now()->format('m.y') }}</span>
                            <span class="text-[#4b4334]">{{ now()->translatedFormat('l, d F Y') }}</span>
                            <span class="text-[#ff3823] flex items-center gap-1">
                                <i class="fa-solid fa-bolt text-[9px]"></i> KABAR HARI INI
                            </span>
                        </div>

                        @php
                            $featuredNews = (isset($news) && $news->isNotEmpty()) ? $news->first() : null;
                            $secondaryNews = (isset($news) && $news->count() > 1) ? $news->slice(1, 2) : collect();
                        @endphp

                        <!-- BERITA UTAMA / TAJUK UTAMA (Kliping Putih Bersih di Atas Kertas Stensil) -->
                        @if($featuredNews)
                            <article class="bg-white border-2 border-[#121316] rounded-xl p-3.5 sm:p-4 shadow-[3px_3px_0px_#121316] mb-3.5 group cursor-pointer hover:-translate-y-0.5 transition-all" onclick="openNewsDetailModal({{ $featuredNews->id }})">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="px-2 py-0.5 bg-[#ff3823] text-white text-[9px] font-mono-code font-black uppercase tracking-wider rounded">
                                        ★ LAPORAN UTAMA
                                    </span>
                                    <span class="text-[10px] font-mono-code font-bold text-[#2563eb] uppercase truncate max-w-[200px]">
                                        {{ $featuredNews->category_label ?? ucfirst($featuredNews->category ?? 'Warta') }} &bull; {{ $featuredNews->published_at ? \Carbon\Carbon::parse($featuredNews->published_at)->translatedFormat('d M Y') : 'Terbaru' }}
                                    </span>
                                </div>

                                <!-- Main Headline -->
                                <h3 class="font-newspaper text-lg sm:text-xl font-black text-[#121316] leading-snug tracking-tight mb-2 group-hover:text-[#ff3823] transition-colors">
                                    {{ $featuredNews->title }}
                                </h3>

                                <!-- Image & Snippet Layout -->
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                                    @php
                                        $leadImg = $featuredNews->image_url ?? ($featuredNews->thumbnail_url ?? ($featuredNews->image ? asset('storage/' . $featuredNews->image) : null));
                                    @endphp

                                    @if($leadImg)
                                        <div class="sm:col-span-5">
                                            <div class="relative rounded-lg overflow-hidden border border-[#121316] bg-slate-100 shadow-sm aspect-[4/3]">
                                                <img src="{{ $leadImg }}" alt="{{ $featuredNews->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                <div class="absolute bottom-0 inset-x-0 bg-[#121316]/80 backdrop-blur-[2px] text-white text-[8px] font-mono-code px-1.5 py-0.5 truncate text-center">
                                                    Dok. Humas Yayasan
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-7 flex flex-col justify-between">
                                    @else
                                        <div class="sm:col-span-12 flex flex-col justify-between">
                                    @endif
                                        <p class="text-xs text-[#374151] font-newspaper leading-relaxed line-clamp-3">
                                            {{ $featuredNews->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($featuredNews->content), 140) }}
                                        </p>
                                        <div class="mt-2 flex items-center justify-between text-[11px] font-mono-code font-bold">
                                            <span class="text-[#6b7280] truncate max-w-[150px]">Oleh: {{ $featuredNews->author_name ?? ($featuredNews->author->name ?? 'Humas PEMBDA') }}</span>
                                            <span class="text-[#ff3823] group-hover:translate-x-0.5 transition-transform flex items-center gap-1 font-extrabold flex-shrink-0">
                                                Baca Liputan &rarr;
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @else
                            <!-- Fallback Editorial -->
                            <div class="p-4 bg-white border-2 border-[#121316] rounded-xl text-center my-3 shadow-[2px_2px_0px_#121316]">
                                <div class="text-[10px] font-mono-code font-bold text-[#ff3823] uppercase mb-1">EDISI RESMI YAYASAN</div>
                                <h3 class="font-newspaper text-lg font-black text-[#121316] mb-1">Maju Terus Pantang Mundur: Pendidikan Nias Modern</h3>
                                <p class="text-xs text-[#555] font-newspaper">Yayasan Perguruan PEMBDA Nias terus berinovasi memadukan riset, vokasi, dan karakter mulia untuk masa depan generasi Nias.</p>
                            </div>
                        @endif

                        <!-- Newspaper Horizontal Column Divider -->
                        <div class="relative my-3">
                            <div class="border-t border-[#121316] w-full"></div>
                            <div class="border-t border-[#121316] w-full mt-[2px]"></div>
                            <span class="absolute left-1/2 -translate-x-1/2 -top-2 bg-[#f4ebd9] px-2 text-[8px] sm:text-[9px] font-mono-code font-bold uppercase tracking-widest text-[#554e3d] whitespace-nowrap">
                                ✦ KAWAT BERITA & WARTA LAINNYA ✦
                            </span>
                        </div>

                        <!-- SECONDARY WIRE STORIES (KILAS WARTA - Kliping Putih Bersih) -->
                        @if($secondaryNews->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach($secondaryNews as $sub)
                                    <div class="border-2 border-[#121316] bg-white p-2.5 rounded-lg flex flex-col justify-between hover:bg-[#fffdf7] hover:-translate-y-0.5 transition-all cursor-pointer group shadow-[2px_2px_0px_#121316]" onclick="openNewsDetailModal({{ $sub->id }})">
                                        <div>
                                            <div class="text-[9px] font-mono-code font-bold text-[#2563eb] uppercase mb-1 flex items-center justify-between">
                                                <span class="truncate max-w-[110px]">[{{ $sub->category_label ?? 'KILAS' }}]</span>
                                                <span class="text-[#777]">{{ $sub->published_at ? \Carbon\Carbon::parse($sub->published_at)->translatedFormat('d M') : 'Baru' }}</span>
                                            </div>
                                            <h4 class="font-newspaper text-xs font-bold text-[#121316] leading-snug line-clamp-2 group-hover:text-[#ff3823] transition-colors">
                                                {{ $sub->title }}
                                            </h4>
                                        </div>
                                        <div class="mt-2 pt-1 border-t border-[#ede9df] flex items-center justify-between text-[10px] font-mono-code">
                                            <span class="text-[#777] truncate max-w-[100px]">{{ $sub->author_name ?? 'Redaksi' }}</span>
                                            <span class="text-[#ff3823] font-bold group-hover:translate-x-0.5 transition-transform">Baca &raquo;</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-2 text-[10px] font-mono-code text-[#888]">
                                Edisi publikasi berkala Yayasan Perguruan PEMBDA Nias.
                            </div>
                        @endif
                    </div>

                    <!-- Bottom Gazette Footer Bar -->
                    <div class="mt-3.5 pt-2.5 border-t-2 border-[#121316] flex flex-wrap items-center justify-between gap-2 text-[10px] font-mono-code font-bold">
                        <div class="flex items-center gap-1.5 text-[#4b4334]">
                            <i class="fa-solid fa-stamp text-[#ff3823]"></i>
                            <span>HUMAS RESMI PEMBDA NIAS</span>
                        </div>
                        <a href="#berita" class="text-[#121316] hover:text-[#ff3823] hover:underline flex items-center gap-1">
                            <span>Indeks Seluruh Arsip ({{ isset($totalNewsCount) ? $totalNewsCount : (isset($news) ? $news->count() : 0) }})</span>
                            <span>&darr;</span>
                        </a>
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
                <div class="text-[11px] font-mono-code font-bold uppercase text-[#777] mt-1">Terdaftar di Portal Alumni</div>
            </div>
        </div>

    </div>
</section>

{{-- MODAL SURAT KABAR (NEWSPAPER ARTICLE READER MODAL) --}}
<div id="newspaperModal" class="fixed inset-0 z-[100] bg-[#121316]/75 backdrop-blur-sm hidden items-center justify-center p-3 sm:p-6 overflow-y-auto" onclick="handleNewspaperBackdropClick(event)">
    <div class="relative bg-[#fffefb] border-2 border-[#121316] rounded-2xl max-w-3xl w-full max-h-[92vh] overflow-hidden shadow-[8px_8px_0px_#121316] flex flex-col my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Modal Newspaper Top Header -->
        <div class="bg-[#f6f4ee] border-b-2 border-[#121316] px-5 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#ff3823]"></span>
                <span class="text-xs font-mono-code font-bold uppercase text-[#121316]">
                    WARTA PERGURUAN PEMBDA &bull; LEMBAR ARTIKEL
                </span>
            </div>
            <button type="button" onclick="closeNewsDetailModal()" class="w-8 h-8 rounded-full bg-white border border-[#121316] text-[#121316] hover:bg-[#ff3823] hover:text-white flex items-center justify-center text-xs font-bold transition-colors shadow-sm" aria-label="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Newspaper Content Body -->
        <div class="p-6 sm:p-8 overflow-y-auto flex-1 font-newspaper">
            
            <!-- Category & Dateline -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#121316]/20 pb-2 mb-4 text-xs font-mono-code font-bold">
                <div class="flex items-center gap-2">
                    <span id="modalNewsCategory" class="px-2.5 py-0.5 bg-[#121316] text-white rounded text-[10px] uppercase">
                        BERITA
                    </span>
                    <span id="modalNewsDate" class="text-[#666]">
                        -
                    </span>
                </div>
                <div id="modalNewsAuthor" class="text-[#ff3823]">
                    Oleh: Redaksi Humas PEMBDA
                </div>
            </div>

            <!-- Title / Headline -->
            <h2 id="modalNewsTitle" class="text-2xl sm:text-4xl font-black text-[#121316] leading-tight tracking-tight mb-4">
                -
            </h2>

            <!-- Image Container -->
            <div id="modalNewsImageContainer" class="mb-5 hidden">
                <div class="border border-[#121316] rounded-xl overflow-hidden bg-slate-100 shadow-sm">
                    <img id="modalNewsImage" src="" alt="" class="w-full max-h-96 object-cover">
                </div>
                <p class="text-[10px] font-mono-code text-[#71717a] mt-1.5 italic text-center">
                    Dokumentasi Resmi Liputan Yayasan Perguruan PEMBDA Nias
                </p>
            </div>

            <!-- Excerpt Lead Box -->
            <div id="modalNewsExcerptBox" class="p-4 bg-[#faf8f5] border-l-4 border-[#ff3823] rounded-r-xl mb-5 text-sm sm:text-base italic text-[#374151] leading-relaxed">
                <span id="modalNewsExcerpt"></span>
            </div>

            <!-- Full Article Content -->
            <div id="modalNewsContent" class="text-sm sm:text-base text-[#1f2937] leading-relaxed space-y-4 font-normal">
                -
            </div>

            <!-- Editorial Signature -->
            <div class="mt-8 pt-4 border-t-2 border-[#121316] flex flex-wrap items-center justify-between gap-3 text-xs font-mono-code text-[#71717a]">
                <div>
                    <strong>Penerbit:</strong> Divisi Informasi & Humas Yayasan Perguruan PEMBDA Nias
                </div>
                <div>
                    <em>Verifikasi: Terbit & Terbuka untuk Umum</em>
                </div>
            </div>

        </div>

        <!-- Modal Newspaper Footer Actions -->
        <div class="bg-[#f6f4ee] border-t-2 border-[#121316] px-5 py-3 flex flex-wrap items-center justify-between gap-2 flex-shrink-0">
            <div class="flex items-center gap-2">
                <button type="button" onclick="shareNewsViaWhatsApp()" class="px-3.5 py-1.5 rounded-full bg-emerald-600 text-white text-xs font-mono-code font-bold hover:bg-emerald-700 flex items-center gap-1.5 transition-colors shadow-sm">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Bagikan WA</span>
                </button>
                <button type="button" onclick="copyNewsLink()" class="px-3.5 py-1.5 rounded-full bg-white border border-[#121316] text-[#121316] text-xs font-mono-code font-bold hover:bg-[#ede9df] flex items-center gap-1.5 transition-colors shadow-sm">
                    <i class="fa-solid fa-link text-xs"></i>
                    <span id="copyLinkText">Salin Tautan</span>
                </button>
            </div>
            
            <button type="button" onclick="closeNewsDetailModal()" class="px-5 py-1.5 rounded-full btn-tactile-white text-xs font-mono-code font-bold">
                Tutup Lembaran (ESC)
            </button>
        </div>

    </div>
</div>

{{-- JAVASCRIPT LOGIC UNTUK SURAT KABAR MODAL --}}
<script>
    const newsDataset = {
        @if(isset($news) && $news->isNotEmpty())
            @foreach($news as $item)
                {{ $item->id }}: {
                    id: {{ $item->id }},
                    title: @json($item->title),
                    category: @json($item->category_label ?? ucfirst($item->category ?? 'Warta')),
                    date: @json($item->published_at ? \Carbon\Carbon::parse($item->published_at)->translatedFormat('l, d F Y') : 'Edisi Terbaru'),
                    author: @json($item->author_name ?? ($item->author->name ?? 'Redaksi Humas PEMBDA')),
                    image: @json($item->image_url ?? ($item->thumbnail_url ?? ($item->image ? asset('storage/' . $item->image) : null))),
                    excerpt: @json($item->excerpt ?? ''),
                    content: @json($item->content ?? $item->excerpt ?? ''),
                    url: @json(url('/berita/' . ($item->slug ?? $item->id)))
                },
            @endforeach
        @endif
    };

    let activeModalNews = null;

    function openNewsDetailModal(newsId) {
        const item = newsDataset[newsId];
        if (!item) return;

        activeModalNews = item;

        document.getElementById('modalNewsTitle').textContent = item.title;
        document.getElementById('modalNewsCategory').textContent = item.category;
        document.getElementById('modalNewsDate').textContent = item.date;
        document.getElementById('modalNewsAuthor').textContent = 'Oleh: ' + item.author;

        const imgContainer = document.getElementById('modalNewsImageContainer');
        const imgElem = document.getElementById('modalNewsImage');
        if (item.image) {
            imgElem.src = item.image;
            imgElem.alt = item.title;
            imgContainer.classList.remove('hidden');
        } else {
            imgContainer.classList.add('hidden');
        }

        const excerptBox = document.getElementById('modalNewsExcerptBox');
        const excerptElem = document.getElementById('modalNewsExcerpt');
        if (item.excerpt && item.excerpt.trim() !== '') {
            excerptElem.textContent = item.excerpt;
            excerptBox.classList.remove('hidden');
        } else {
            excerptBox.classList.add('hidden');
        }

        // Render content (break into paragraphs if raw text)
        const contentElem = document.getElementById('modalNewsContent');
        if (item.content && item.content.includes('<p>')) {
            contentElem.innerHTML = item.content;
        } else {
            const paragraphs = (item.content || item.excerpt || '').split(/\n+/).filter(p => p.trim() !== '');
            if (paragraphs.length > 0) {
                contentElem.innerHTML = paragraphs.map(p => `<p>${p}</p>`).join('');
            } else {
                contentElem.textContent = item.title;
            }
        }

        const modal = document.getElementById('newspaperModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeNewsDetailModal() {
        const modal = document.getElementById('newspaperModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        activeModalNews = null;
    }

    function handleNewspaperBackdropClick(event) {
        if (event.target.id === 'newspaperModal') {
            closeNewsDetailModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeNewsDetailModal();
        }
    });

    function shareNewsViaWhatsApp() {
        if (!activeModalNews) return;
        const text = encodeURIComponent(`*${activeModalNews.title}*\n\nBaca warta resmi Yayasan Perguruan PEMBDA Nias:\n${activeModalNews.url}`);
        window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
    }

    function copyNewsLink() {
        if (!activeModalNews) return;
        navigator.clipboard.writeText(activeModalNews.url).then(() => {
            const textElem = document.getElementById('copyLinkText');
            const original = textElem.textContent;
            textElem.textContent = 'Tersalin!';
            setTimeout(() => {
                textElem.textContent = original;
            }, 2000);
        });
    }
</script>
