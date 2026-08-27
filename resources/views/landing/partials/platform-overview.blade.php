{{-- PLATFORM OVERVIEW — Bold Indigo Theme --}}
<style>
    .pillar-icon {
        margin: 0 auto 20px;
        width: 60px;
        height: 60px;
        border-radius: 18px;
        font-size: 26px;
    }
    .marquee-fade-left,
    .marquee-fade-right {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 80px;
        z-index: 2;
    }
    .marquee-fade-left {
        left: 0;
        background: linear-gradient(90deg, var(--bg-card, #ffffff), transparent);
    }
    .marquee-fade-right {
        right: 0;
        background: linear-gradient(-90deg, var(--bg-card, #ffffff), transparent);
    }
</style>

<section id="platform" class="section relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 50%, #f5f3ff 100%);">
    {{-- Decorative Background Blobs --}}
    <div class="absolute -left-32 top-0 w-96 h-96 bg-indigo-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -right-32 bottom-0 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-sky-300/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fw">
        <div style="max-width:900px; margin:0 auto; text-align:center;" data-aos="fade-up">
            {{-- Subtle intro text --}}
            <div style="display:inline-flex; align-items:center; gap:10px; background:linear-gradient(135deg, var(--indigo-bg), var(--violet-bg)); padding:10px 24px; border-radius:100px; margin-bottom:32px; border:1px solid rgba(99,102,241,0.2);">
                <i class="fa-solid fa-globe" style="color:var(--indigo-light); font-size:14px;"></i>
                <span style="font-size:13px; font-weight:600; color:var(--indigo);">perguruanpembda.com</span>
            </div>

            <h2 class="h2" style="margin-bottom:16px; line-height:1.3; color:var(--text-primary);">
                Lebih dari Sekadar Website —<br>
                <span style="background:linear-gradient(135deg, var(--indigo), var(--indigo-light)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">Ekosistem Digital</span> Pengelolaan Pendidikan
            </h2>

            <p class="body-lg" style="max-width:720px; margin:0 auto 40px; line-height:1.8;">
                <strong style="color:var(--text-primary);">perguruanpembda.com</strong> bukan hanya profil Yayasan Perguruan PEMBDA Nias, tetapi juga platform <strong style="color:var(--text-primary);">PembdaHUB</strong> — sebuah aplikasi terintegrasi untuk pengelolaan pendidikan di seluruh unit sekolah, yang dapat diakses oleh siswa, guru, dan orang tua secara langsung.
            </p>
        </div>

        {{-- Three Pillars --}}
        <div class="bento bento-3" style="max-width:1680px; margin:0 auto;" data-aos="fade-up" data-aos-delay="100">
            {{-- Pillar 1: Administrasi --}}
            <div class="bcard" style="background: linear-gradient(135deg, #3b82f6 0%, #4f46e5 100%); box-shadow: 0 10px 25px -5px rgba(79,70,229,0.4); text-align:center; padding:36px 28px; border:none; transition: transform 0.3s; transform: translateY(0);" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="icon-circle pillar-icon" style="background:rgba(255,255,255,0.2); color:#ffffff; border:1px solid rgba(255,255,255,0.3);">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <h3 class="h3" style="margin-bottom:8px; font-size:17px; color:#ffffff; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">Administrasi &amp; Kepegawaian</h3>
                <p class="body" style="font-size:13px; color:rgba(255,255,255,0.9);">Data pegawai, jabatan, surat-menyurat, dan pengelolaan SDM terintegrasi.</p>
            </div>

            {{-- Pillar 2: Keuangan --}}
            <div class="bcard" style="background: linear-gradient(135deg, #34d399 0%, #0d9488 100%); box-shadow: 0 10px 25px -5px rgba(13,148,136,0.4); text-align:center; padding:36px 28px; border:none; transition: transform 0.3s; transform: translateY(0);" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="icon-circle pillar-icon" style="background:rgba(255,255,255,0.2); color:#ffffff; border:1px solid rgba(255,255,255,0.3);">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <h3 class="h3" style="margin-bottom:8px; font-size:17px; color:#ffffff; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">Keuangan</h3>
                <p class="body" style="font-size:13px; color:rgba(255,255,255,0.9);">Pembayaran SPP, tagihan digital, laporan keuangan, dan rekap otomatis.</p>
            </div>

            {{-- Pillar 3: Akademik --}}
            <div class="bcard" style="background: linear-gradient(135deg, #8b5cf6 0%, #d946ef 100%); box-shadow: 0 10px 25px -5px rgba(217,70,239,0.4); text-align:center; padding:36px 28px; border:none; transition: transform 0.3s; transform: translateY(0);" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="icon-circle pillar-icon" style="background:rgba(255,255,255,0.2); color:#ffffff; border:1px solid rgba(255,255,255,0.3);">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h3 class="h3" style="margin-bottom:8px; font-size:17px; color:#ffffff; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">Akademik</h3>
                <p class="body" style="font-size:13px; color:rgba(255,255,255,0.9);">Pembelajaran, penilaian, penjadwalan, dan monitoring perkembangan siswa.</p>
            </div>
        </div>

        {{-- Showcase Penelitian & Project Akhir --}}
        @if(isset($finalProjectsShowcase) && $finalProjectsShowcase->count() > 0)
        <div data-aos="fade-up" data-aos-delay="200" style="margin-top:64px; overflow:hidden; position:relative; padding-bottom: 24px;">
            <div class="marquee-fade-left"></div>
            <div class="marquee-fade-right"></div>
            @php
                // Maintain consistent ultra-slow speed (120s per item) regardless of how many items are fetched
                $marqueeDuration = $finalProjectsShowcase->count() * 120;
            @endphp
            <div class="marquee-track" style="animation-duration: {{ $marqueeDuration }}s;">
                <div class="marquee-content" style="display: flex; gap: 80px; padding-left: 40px; align-items: center;">
                    @foreach($finalProjectsShowcase as $fp)
                        <div style="width: 450px; flex: 0 0 450px; display:flex; flex-direction:column; justify-content:center; align-items:center;">
                            {{-- Judul: Elegan, wrap alami tanpa dipotong --}}
                            <h4 style="width: 100%; font-size: 18px; font-weight: 800; color: var(--indigo); line-height: 1.4; text-align: center; white-space: normal; word-break: break-word; margin: 0 0 16px 0; padding: 0 10px;">
                                {{ $fp->title }}
                            </h4>
                            
                            {{-- Nama Tim: Wrap alami tanpa dipotong --}}
                            <div style="width: 100%; position: relative;">
                                @php
                                    $teamNames = $fp->members->isNotEmpty() 
                                        ? $fp->members->map(function($m) { return $m->student->full_name ?? 'Siswa'; })->implode(' &bull; ')
                                        : ($fp->student->full_name ?? 'Tim Siswa');
                                @endphp
                                <div style="font-size: 14px; font-weight: 700; color: #4b5563; text-align: center; padding: 12px 0; border-top: 1px dashed rgba(99,102,241,0.4); border-bottom: 1px dashed rgba(99,102,241,0.4); white-space: normal; word-break: break-word;">
                                    <i class="fa-solid fa-users" style="color: #6366f1; margin-right: 6px;"></i> {!! $teamNames !!}
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Duplicate for seamless loop --}}
                    @foreach($finalProjectsShowcase as $fp)
                        <div style="width: 450px; flex: 0 0 450px; display:flex; flex-direction:column; justify-content:center; align-items:center;">
                            <h4 style="width: 100%; font-size: 18px; font-weight: 800; color: var(--indigo); line-height: 1.4; text-align: center; white-space: normal; word-break: break-word; margin: 0 0 16px 0; padding: 0 10px;">
                                {{ $fp->title }}
                            </h4>
                            
                            <div style="width: 100%; position: relative;">
                                @php
                                    $teamNames = $fp->members->isNotEmpty() 
                                        ? $fp->members->map(function($m) { return $m->student->full_name ?? 'Siswa'; })->implode(' &bull; ')
                                        : ($fp->student->full_name ?? 'Tim Siswa');
                                @endphp
                                <div style="font-size: 14px; font-weight: 700; color: #4b5563; text-align: center; padding: 12px 0; border-top: 1px dashed rgba(99,102,241,0.4); border-bottom: 1px dashed rgba(99,102,241,0.4); white-space: normal; word-break: break-word;">
                                    <i class="fa-solid fa-users" style="color: #6366f1; margin-right: 6px;"></i> {!! $teamNames !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
