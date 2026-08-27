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

<section id="platform" class="section" style="background: var(--bg-card, #ffffff);">
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
            <div class="bcard" style="text-align:center; padding:36px 28px;">
                <div class="icon-circle pillar-icon" style="background:var(--blue-bg); color:var(--blue);">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <h3 class="h3" style="margin-bottom:8px; font-size:17px;">Administrasi &amp; Kepegawaian</h3>
                <p class="body" style="font-size:13px;">Data pegawai, jabatan, surat-menyurat, dan pengelolaan SDM terintegrasi.</p>
            </div>

            {{-- Pillar 2: Keuangan --}}
            <div class="bcard" style="text-align:center; padding:36px 28px;">
                <div class="icon-circle pillar-icon" style="background:var(--emerald-bg); color:var(--emerald);">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <h3 class="h3" style="margin-bottom:8px; font-size:17px;">Keuangan</h3>
                <p class="body" style="font-size:13px;">Pembayaran SPP, tagihan digital, laporan keuangan, dan rekap otomatis.</p>
            </div>

            {{-- Pillar 3: Akademik --}}
            <div class="bcard" style="text-align:center; padding:36px 28px;">
                <div class="icon-circle pillar-icon" style="background:var(--violet-bg); color:var(--violet);">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h3 class="h3" style="margin-bottom:8px; font-size:17px;">Akademik</h3>
                <p class="body" style="font-size:13px;">Pembelajaran, penilaian, penjadwalan, dan monitoring perkembangan siswa.</p>
            </div>
        </div>

        {{-- Feature keyword marquee -> diganti Showcase Penelitian & Project Akhir --}}
        @if(isset($finalProjectsShowcase) && $finalProjectsShowcase->count() > 0)
        <div data-aos="fade-up" data-aos-delay="200" style="margin-top:56px; overflow:hidden; position:relative; padding-bottom: 24px;">
            <div class="marquee-fade-left"></div>
            <div class="marquee-fade-right"></div>
            <div class="marquee-track">
                <div class="marquee-content" style="display: flex; gap: 24px; padding-left: 24px;">
                    @foreach($finalProjectsShowcase as $fp)
                        <div class="bcard" style="min-width: 360px; max-width: 400px; border-radius: 24px; background: linear-gradient(135deg, var(--indigo-bg), #ffffff); border: 1px solid var(--indigo-light); box-shadow: 0 10px 30px -10px rgba(99,102,241,0.15); display:flex; flex-direction:column; justify-content:space-between; overflow:hidden;">
                            
                            {{-- Bagian Atas: Judul (Besar, Blok dengan Bayangan dan Bold) --}}
                            <div style="padding: 24px 28px; flex-grow: 1;">
                                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--indigo); margin-bottom: 12px; display:inline-flex; align-items:center; gap:6px;">
                                    <i class="fa-solid {{ $fp->type === 'penelitian_ilmiah' ? 'fa-microscope' : 'fa-lightbulb' }}"></i> 
                                    {{ $fp->type === 'penelitian_ilmiah' ? 'Penelitian Ilmiah' : 'Project Akhir' }}
                                </div>
                                <h4 style="font-size: 19px; font-weight: 900; color: var(--text-primary); line-height: 1.4; text-shadow: 1px 2px 4px rgba(0,0,0,0.12); margin: 0;">
                                    {{ $fp->title }}
                                </h4>
                            </div>
                            
                            {{-- Bagian Bawah: Teks Nama Tim Bergerak Dari Kiri ke Kanan --}}
                            <div style="background: rgba(99,102,241,0.04); border-top: 1px dashed rgba(99,102,241,0.25); padding: 14px 0;">
                                @php
                                    $teamNames = $fp->members->map(function($m) { return $m->student->full_name ?? 'Siswa'; })->implode(' &nbsp;&bull;&nbsp; ');
                                @endphp
                                <marquee direction="right" scrollamount="4" style="font-size: 13px; font-weight: 700; color: var(--indigo); white-space: nowrap; padding: 0 12px;">
                                    <i class="fa-solid fa-users" style="margin-right:6px; opacity:0.8;"></i> {!! $teamNames !!}
                                </marquee>
                            </div>
                        </div>
                    @endforeach

                    {{-- Duplicate for seamless loop --}}
                    @foreach($finalProjectsShowcase as $fp)
                        <div class="bcard" style="min-width: 360px; max-width: 400px; border-radius: 24px; background: linear-gradient(135deg, var(--indigo-bg), #ffffff); border: 1px solid var(--indigo-light); box-shadow: 0 10px 30px -10px rgba(99,102,241,0.15); display:flex; flex-direction:column; justify-content:space-between; overflow:hidden;">
                            
                            <div style="padding: 24px 28px; flex-grow: 1;">
                                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--indigo); margin-bottom: 12px; display:inline-flex; align-items:center; gap:6px;">
                                    <i class="fa-solid {{ $fp->type === 'penelitian_ilmiah' ? 'fa-microscope' : 'fa-lightbulb' }}"></i> 
                                    {{ $fp->type === 'penelitian_ilmiah' ? 'Penelitian Ilmiah' : 'Project Akhir' }}
                                </div>
                                <h4 style="font-size: 19px; font-weight: 900; color: var(--text-primary); line-height: 1.4; text-shadow: 1px 2px 4px rgba(0,0,0,0.12); margin: 0;">
                                    {{ $fp->title }}
                                </h4>
                            </div>
                            
                            <div style="background: rgba(99,102,241,0.04); border-top: 1px dashed rgba(99,102,241,0.25); padding: 14px 0;">
                                @php
                                    $teamNames = $fp->members->map(function($m) { return $m->student->full_name ?? 'Siswa'; })->implode(' &nbsp;&bull;&nbsp; ');
                                @endphp
                                <marquee direction="right" scrollamount="4" style="font-size: 13px; font-weight: 700; color: var(--indigo); white-space: nowrap; padding: 0 12px;">
                                    <i class="fa-solid fa-users" style="margin-right:6px; opacity:0.8;"></i> {!! $teamNames !!}
                                </marquee>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
