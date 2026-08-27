{{-- SAMBUTAN KETUA YAYASAN — Compact Height (400px) Half-Body Edition --}}
<section class="section-fullwidth" id="sambutan-ketua-section" style="width: 100%; position: relative; overflow: hidden; background: linear-gradient(135deg, #0b0f19 0%, #1e1b4b 45%, #312e81 100%); color: #ffffff; padding: 0; margin: 0;">
    
    {{-- Abstract Organic Ambient Glowing Orbs --}}
    <div style="position: absolute; top: -10%; left: -5%; width: 450px; height: 450px; background: rgba(99, 102, 241, 0.35); border-radius: 50%; filter: blur(100px); pointer-events: none;"></div>
    <div style="position: absolute; bottom: 0%; right: 5%; width: 450px; height: 450px; background: rgba(245, 158, 11, 0.22); border-radius: 50%; filter: blur(100px); pointer-events: none;"></div>

    <div style="width: 100%; max-width: 100%; margin: 0; min-height: 400px; display: flex; align-items: stretch; position: relative; z-index: 10;">
        
        <div class="sambutan-full-container" style="display: flex; flex-wrap: wrap; width: 100%; align-items: stretch; margin: 0; padding: 0;">

            {{-- 1. LEFT SIDE: COMPACT HALF-BODY PORTRAIT (MIN-HEIGHT 400PX) --}}
            <div class="sambutan-left-photo" style="flex: 1 1 45%; min-width: 320px; position: relative; display: flex; align-items: stretch; min-height: 400px; overflow: hidden; margin: 0; padding: 0;">
                @php
                    $photoPath = base_path('public/images/photo-profile.jpeg');
                    $photoExists = file_exists($photoPath);
                @endphp
                
                {{-- Ambient Light Aura behind photo --}}
                <div style="position: absolute; top: 35%; left: 30%; transform: translate(-50%, -50%); width: 420px; height: 420px; background: radial-gradient(circle, rgba(99, 102, 241, 0.55) 0%, rgba(245, 158, 11, 0.2) 45%, rgba(0,0,0,0) 75%); border-radius: 50%; filter: blur(45px); pointer-events: none;"></div>

                @if($photoExists)
                    <div style="position: absolute; inset: 0; width: 100%; height: 100%; margin: 0; padding: 0; overflow: hidden;">
                        
                        {{-- Compact Half-Body Crop: object-position center bottom to align shoulders with bottom edge --}}
                        <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" 
                             alt="Yulianus Zega, S.Kom, M.Pd.T" 
                             style="width: 100%; height: 100%; object-fit: cover; object-position: center bottom; margin: 0; padding: 0; filter: contrast(1.08) brightness(1.05);
                                    -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,1) 50%, rgba(0,0,0,0) 100%);
                                    mask-image: linear-gradient(to right, rgba(0,0,0,1) 50%, rgba(0,0,0,0) 100%);" />
                    </div>
                @else
                    <div style="width: 100%; height: 100%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-user-tie" style="color: #ffffff; font-size: 110px;"></i>
                    </div>
                @endif
            </div>

            {{-- 2. RIGHT SIDE: COMPACT HIGH-IMPACT TEXT & NEAT PARAGRAPHS --}}
            <div class="sambutan-right-text" style="flex: 1 1 55%; min-width: 320px; padding: 24px 36px 24px 16px; display: flex; flex-direction: column; justify-content: center; gap: 14px; max-width: 850px;">
                
                {{-- Header Pill --}}
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="background: #f59e0b; color: #000000; font-size: 10px; font-weight: 900; padding: 5px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 12px rgba(245,158,11,0.3);">
                        <i class="fa-solid fa-quote-left" style="margin-right: 5px;"></i> Sambutan Ketua Yayasan
                    </span>
                    <span style="background: rgba(255,255,255,0.1); color: #e2e8f0; font-size: 10px; font-weight: 700; padding: 5px 12px; border-radius: 20px; backdrop-filter: blur(10px);">
                        Perguruan PEMBDA Nias
                    </span>
                </div>

                {{-- Quote Body (Rapi Alinea) --}}
                <div style="font-size: clamp(14px, 1.9vw, 17px); font-weight: 600; color: #ffffff; line-height: 1.7; margin: 0;" class="space-y-2">
                    <p style="font-weight: 900; color: #fbbf24; font-size: 18px; margin-bottom: 4px;">Ya'ahowu!</p>
                    
                    <p style="margin-bottom: 8px;">Sebagai salah satu garda terdepan pendidikan di Kepulauan Nias, Yayasan Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas yang tangguh, berkarakter mulia, dan unggul secara teknologi.</p>
                    
                    <p style="margin-bottom: 8px;">Selaras dengan motto abadi kami : <span style="color:#fbbf24; font-weight:900; text-decoration: underline decoration-amber-500 underline-offset-4;">' Keep Moving Forward / Maju Terus Pantang Mundur '</span>, kami terus berinovasi tanpa henti melalui PembdaHUB untuk menciptakan ekosistem pembelajaran digital terbaik.</p>
                    
                    <p style="margin-bottom: 0;">Bersama, kita langkah demi langkah melangkah pasti menjawab tantangan zaman demi masa depan Nias yang gemilang !</p>
                </div>

                {{-- Golden Accent Divider --}}
                <div style="width: 70px; height: 3px; background: linear-gradient(90deg, #f59e0b, #fbbf24); border-radius: 2px; margin-top: 2px;"></div>

                {{-- Chairman Name & Position --}}
                <div>
                    <h3 style="font-size: 20px; font-weight: 900; color: #ffffff; margin: 0 0 2px 0; letter-spacing: -0.3px;">
                        {{ \App\Models\Setting::getValue('ketua_nama', 'Yulianus Zega, S.Kom, M.Pd.T') }}
                    </h3>
                    <p style="font-size: 13px; font-weight: 700; color: #fbbf24; margin: 0;">
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
        min-height: 360px !important;
        width: 100% !important;
    }
    .sambutan-right-text {
        padding: 20px !important;
        width: 100% !important;
    }
}
</style>
