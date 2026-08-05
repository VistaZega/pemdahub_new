{{-- SAMBUTAN KETUA YAYASAN — Bold Indigo Theme --}}
<section class="section" style="background:#ffffff; border-bottom: 1px solid var(--border);">
    <div class="fw">
        <div style="max-width:1000px; margin:0 auto;" data-aos="fade-up">

            <div class="bcard shimmer-card sambutan-card" style="padding:48px 56px; position:relative; overflow:hidden; border: 1.5px solid var(--border); background:linear-gradient(135deg, #ffffff, var(--indigo-bg)); border-left: 4px solid var(--gold);">

                {{-- Decorative Quote Icon Background --}}
                <div style="position:absolute; top:-20px; left:24px; font-size:200px; color:var(--indigo); opacity:0.04; pointer-events:none; font-family:Georgia, serif; line-height:1;">
                    &ldquo;
                </div>

                {{-- Subtle gold corner accent --}}
                <div style="position:absolute; top:0; right:0; width:120px; height:120px; background:radial-gradient(circle at top right, rgba(245,158,11,0.08), transparent); pointer-events:none; border-radius:0 var(--radius) 0 0;"></div>

                <div class="sambutan-layout">
                    
                    {{-- Content (Left) --}}
                    <div class="sambutan-content">
                        <div class="section-label" style="margin-bottom:24px;">
                            <div class="section-label-dot" style="background:var(--gold);"></div>
                            <span class="section-label-text" style="color:var(--indigo-dark); font-weight:800;">Sambutan Ketua Yayasan</span>
                        </div>

                        <blockquote class="body-lg" style="font-style:italic; font-size: clamp(15px, 2vw, 17px); line-height:1.8; color:var(--text-primary); margin-bottom:28px; font-weight:500; position:relative;">
                            @php
                                $quoteRaw = \App\Models\Setting::getValue('ketua_quote', "Salam sejahtera, Ya'ahowu! Sebagai garda terdepan pendidikan di Kepulauan Nias, Yayasan Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas yang tangguh, berkarakter mulia, dan unggul secara teknologi. Selaras dengan motto abadi kami: 'Keep Moving Forward / Maju Terus Pantang Mundur', kami terus berinovasi tanpa henti melalui PembdaHUB untuk menciptakan ekosistem pembelajaran digital terbaik. Bersama, kita langkah demi langkah melangkah pasti menjawab tantangan zaman demi masa depan Nias yang gemilang!");
                                $quoteDisplay = str_replace(
                                    'Keep Moving Forward / Maju Terus Pantang Mundur',
                                    '<strong style="color:var(--gold); font-weight:800; font-style:normal;">Keep Moving Forward / Maju Terus Pantang Mundur</strong>',
                                    $quoteRaw
                                );
                            @endphp
                            &ldquo;{!! $quoteDisplay !!}&rdquo;
                        </blockquote>

                        <div style="width:40px; height:3px; background:linear-gradient(90deg, var(--gold), var(--gold-bright, #fbbf24)); margin-bottom: 20px; border-radius:2px;"></div>

                        <div>
                            <div class="h3" style="font-size:18px; font-weight:800; color:var(--indigo-dark); margin-bottom:4px;">
                                {{ \App\Models\Setting::getValue('ketua_nama', 'Yulianus Zega, S.Kom, M.Pd.T') }}
                            </div>
                            <div class="caption" style="color:var(--text-secondary); font-size:13px; font-weight:700;">
                                {{ \App\Models\Setting::getValue('ketua_jabatan', 'Ketua Yayasan Perguruan PEMBDA Nias') }}
                            </div>
                        </div>
                    </div>

                    {{-- Avatar / Profile Photo (Right) --}}
                    <div class="sambutan-photo-container">
                        <div class="sambutan-photo">
                            @php
                                $photoPath = base_path('public/images/photo-profile.jpeg');
                                $photoExists = file_exists($photoPath);
                            @endphp
                            @if($photoExists)
                                <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" alt="Yulianus Zega, S.Kom, M.Pd.T" style="width:100%; height:100%; object-fit:cover; object-position:top;">
                            @else
                                <i class="fa-solid fa-user-tie" style="color:#ffffff; font-size:70px;"></i>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<style>
.sambutan-layout {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 48px;
    text-align: left;
}
.sambutan-content {
    flex: 1;
    max-width: 650px;
}
.sambutan-photo-container {
    flex-shrink: 0;
}
.sambutan-photo {
    width: 320px;
    height: 400px;
    border-radius: 32px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--indigo), var(--indigo-light));
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow:
        12px 12px 0 rgba(245,158,11,0.15), /* shadow warna gold offset */
        0 14px 36px rgba(79,46,209,0.15);
    border: 4px solid #ffffff;
    transition: all 0.4s ease;
    transform: rotate(2deg);
}
.sambutan-photo:hover {
    transform: rotate(0deg) translateY(-5px);
    box-shadow:
        8px 8px 0 rgba(245,158,11,0.25),
        0 18px 40px rgba(79,46,209,0.2);
}
.sambutan-card {
    transition: box-shadow 0.35s ease, transform 0.35s ease;
}
.sambutan-card:hover {
    box-shadow: var(--shadow-hover), 0 0 0 1px rgba(245,158,11,0.15);
    transform: translateY(-2px);
}

/* Responsiveness untuk tampilan Handphone / Tablet Kecil */
@media (max-width: 768px) {
    .sambutan-layout {
        flex-direction: column-reverse; /* Foto di atas saat mobile (opsional, ganti column jika ingin teks di atas) */
        text-align: center;
        gap: 32px;
    }
    .sambutan-card {
        padding: 32px 24px;
        border-left: none;
        border-top: 4px solid var(--gold);
    }
    .section-label {
        justify-content: center;
    }
    .sambutan-content {
        max-width: 100%;
    }
    .sambutan-photo {
        width: 150px;
        height: 150px;
        border-radius: 30px;
        transform: rotate(0);
        box-shadow:
            0 0 0 4px #ffffff,
            0 0 0 7px var(--gold, #f59e0b),
            0 14px 36px rgba(79,46,209,0.25);
    }
    .sambutan-photo:hover {
        transform: translateY(-3px);
        box-shadow:
            0 0 0 4px #ffffff,
            0 0 0 7px var(--gold, #f59e0b),
            0 18px 40px rgba(79,46,209,0.3);
    }
    .sambutan-content .h3, .sambutan-content .caption {
        text-align: center;
    }
    .sambutan-content div[style*="width:40px"] {
        margin-left: auto;
        margin-right: auto;
    }
}
</style>
