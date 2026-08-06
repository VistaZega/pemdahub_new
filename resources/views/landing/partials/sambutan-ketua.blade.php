{{-- SAMBUTAN KETUA YAYASAN — Intact Head Top-Shifted & Clean Paragraphs Edition --}}
<section class="section-fullwidth" id="sambutan-ketua-section" style="width: 100%; position: relative; overflow: hidden; background: linear-gradient(135deg, #0b0f19 0%, #1e1b4b 45%, #312e81 100%); color: #ffffff; padding: 0;">
    
    {{-- Abstract Organic Ambient Glowing Orbs --}}
    <div style="position: absolute; top: -10%; left: 5%; width: 500px; height: 500px; background: rgba(99, 102, 241, 0.35); border-radius: 50%; filter: blur(110px); pointer-events: none;"></div>
    <div style="position: absolute; bottom: 0%; right: 10%; width: 500px; height: 500px; background: rgba(245, 158, 11, 0.22); border-radius: 50%; filter: blur(110px); pointer-events: none;"></div>

    <div style="width: 100%; max-width: 1480px; margin: 0 auto; min-height: 560px; display: flex; align-items: center; position: relative; z-index: 10;">
        
        <div class="sambutan-full-container" style="display: flex; flex-wrap: wrap; width: 100%; align-items: center;">

            {{-- 1. LEFT SIDE: PHOTO SHIFTED FURTHER UPWARDS --}}
            <div class="sambutan-left-photo" style="flex: 1 1 48%; min-width: 320px; position: relative; display: flex; justify-content: center; align-items: flex-start; min-height: 560px; overflow: hidden;">
                @php
                    $photoPath = base_path('public/images/photo-profile.jpeg');
                    $photoExists = file_exists($photoPath);
                @endphp
                
                {{-- Ambient Light Aura behind photo --}}
                <div style="position: absolute; top: 35%; left: 50%; transform: translate(-50%, -50%); width: 480px; height: 480px; background: radial-gradient(circle, rgba(99, 102, 241, 0.55) 0%, rgba(245, 158, 11, 0.2) 45%, rgba(0,0,0,0) 75%); border-radius: 50%; filter: blur(45px); pointer-events: none;"></div>

                @if($photoExists)
                    <div style="position: relative; width: 100%; max-width: 580px; height: 600px; display: flex; align-items: flex-start; justify-content: center; overflow: hidden; margin-top: -45px;">
                        
                        {{-- Photo Element: Shifted upwards further with intact head --}}
                        <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" 
                             alt="Yulianus Zega, S.Kom, M.Pd.T" 
                             style="width: 100%; height: 100%; object-fit: cover; object-position: top center; filter: contrast(1.08) brightness(1.05); transform: translateY(-30px);
                                    -webkit-mask-image: radial-gradient(ellipse at 50% 30%, rgba(0,0,0,1) 50%, rgba(0,0,0,0.85) 70%, rgba(0,0,0,0) 95%);
                                    mask-image: radial-gradient(ellipse at 50% 30%, rgba(0,0,0,1) 50%, rgba(0,0,0,0.85) 70%, rgba(0,0,0,0) 95%);" />
                        
                        {{-- Soft Vignette Overlay --}}
                        <div style="position: absolute; inset: 0; pointer-events: none; background: radial-gradient(ellipse at 50% 30%, transparent 40%, rgba(30, 27, 75, 0.25) 70%, #0b0f19 92%);"></div>
                    </div>
                @else
                    <div style="width: 340px; height: 500px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; border-radius: 20px;">
                        <i class="fa-solid fa-user-tie" style="color: #ffffff; font-size: 130px;"></i>
                    </div>
                @endif
            </div>

            {{-- 2. RIGHT SIDE: HIGH-IMPACT TEXT & NEAT PARAGRAPHS --}}
            <div class="sambutan-right-text" style="flex: 1 1 52%; min-width: 320px; padding: 40px 40px 40px 16px; display: flex; flex-direction: column; justify-content: center; gap: 18px;">
                
                {{-- Header Pill --}}
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="background: #f59e0b; color: #000000; font-size: 11px; font-weight: 900; padding: 6px 16px; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 12px rgba(245,158,11,0.3);">
                        <i class="fa-solid fa-quote-left" style="margin-right: 6px;"></i> Sambutan Ketua Yayasan
                    </span>
                    <span style="background: rgba(255,255,255,0.1); color: #e2e8f0; font-size: 11px; font-weight: 700; padding: 6px 14px; border-radius: 20px; backdrop-filter: blur(10px);">
                        Perguruan PEMBDA Nias
                    </span>
                </div>

                {{-- Quote Body (Rapi Alinea) --}}
                <div style="font-size: clamp(15px, 2.1vw, 18px); font-weight: 600; color: #ffffff; line-height: 1.8; margin: 0;" class="space-y-3">
                    <p style="font-weight: 900; color: #fbbf24; font-size: 20px; margin-bottom: 6px;">Ya'ahowu!</p>
                    
                    <p style="margin-bottom: 12px;">Sebagai salah satu garda terdepan pendidikan di Kepulauan Nias, Yayasan Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas yang tangguh, berkarakter mulia, dan unggul secara teknologi.</p>
                    
                    <p style="margin-bottom: 12px;">Selaras dengan motto abadi kami: <span style="color:#fbbf24; font-weight:900; text-decoration: underline decoration-amber-500 underline-offset-4;">' Keep Moving Forward / Maju Terus Pantang Mundur '</span>, kami terus berinovasi tanpa henti melalui PembdaHUB untuk menciptakan ekosistem pembelajaran digital terbaik.</p>
                    
                    <p style="margin-bottom: 0;">Bersama, kita langkah demi langkah melangkah pasti menjawab tantangan zaman demi masa depan Nias yang gemilang!</p>
                </div>

                {{-- Golden Accent Divider --}}
                <div style="width: 80px; height: 4px; background: linear-gradient(90deg, #f59e0b, #fbbf24); border-radius: 2px; margin-top: 4px;"></div>

                {{-- Chairman Name & Position --}}
                <div>
                    <h3 style="font-size: 22px; font-weight: 900; color: #ffffff; margin: 0 0 4px 0; letter-spacing: -0.3px;">
                        {{ \App\Models\Setting::getValue('ketua_nama', 'Yulianus Zega, S.Kom, M.Pd.T') }}
                    </h3>
                    <p style="font-size: 14px; font-weight: 700; color: #fbbf24; margin: 0;">
                        {{ \App\Models\Setting::getValue('ketua_jabatan', 'Ketua Yayasan Perguruan PEMBDA Nias') }}
                    </p>
                </div>

            </div>

        </div>

    </div>
</section>

<style>
@media (max-width: 768px) {
    .sambutan-full-container {
        flex-direction: column !important;
    }
    .sambutan-left-photo {
        min-height: 440px !important;
        width: 100% !important;
    }
    .sambutan-right-text {
        padding: 24px !important;
        width: 100% !important;
    }
}
</style>
