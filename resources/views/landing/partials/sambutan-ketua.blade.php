{{-- SAMBUTAN KETUA YAYASAN — Full Width Half-Body Seamless Edition --}}
<section class="section-fullwidth" id="sambutan-ketua-section" style="width: 100%; position: relative; overflow: hidden; background: linear-gradient(135deg, #0b0f19 0%, #1e1b4b 45%, #312e81 100%); color: #ffffff; padding: 0;">
    
    {{-- Abstract Organic Ambient Glowing Orbs --}}
    <div style="position: absolute; top: 10%; left: 5%; width: 350px; height: 350px; background: rgba(99, 102, 241, 0.25); border-radius: 50%; filter: blur(90px); pointer-events: none;"></div>
    <div style="position: absolute; bottom: 5%; right: 10%; width: 400px; height: 400px; background: rgba(245, 158, 11, 0.18); border-radius: 50%; filter: blur(100px); pointer-events: none;"></div>
    <div style="position: absolute; top: 30%; right: 30%; width: 250px; height: 250px; background: rgba(139, 92, 246, 0.2); border-radius: 50%; filter: blur(80px); pointer-events: none;"></div>

    <div style="width: 100%; max-width: 1440px; margin: 0 auto; min-height: 540px; display: flex; flex-direction: column; md:flex-direction: row; items-center; position: relative; z-index: 10;">
        
        <div class="sambutan-full-container" style="display: flex; flex-wrap: wrap; width: 100%; align-items: center;">

            {{-- 1. LEFT SIDE: HALF-BODY PORTRAIT PHOTO (Enlarged, Seamlessly Blended, No Frame) --}}
            <div class="sambutan-left-photo" style="flex: 1 1 45%; min-width: 320px; position: relative; display: flex; justify-content: center; align-items: flex-end; min-height: 480px; overflow: hidden;">
                @php
                    $photoPath = base_path('public/images/photo-profile.jpeg');
                    $photoExists = file_exists($photoPath);
                @endphp
                
                @if($photoExists)
                    <div style="position: relative; width: 100%; max-width: 460px; height: 520px; display: flex; align-items: flex-end;">
                        <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" 
                             alt="Yulianus Zega, S.Kom, M.Pd.T" 
                             style="width: 100%; height: 100%; object-fit: cover; object-position: top center; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.6)); -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 75%, rgba(0,0,0,0) 100%), linear-gradient(to right, rgba(0,0,0,1) 80%, rgba(0,0,0,0) 100%); mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 75%, rgba(0,0,0,0) 100%), linear-gradient(to right, rgba(0,0,0,1) 80%, rgba(0,0,0,0) 100%);" />
                    </div>
                @else
                    <div style="width: 340px; height: 460px; background: rgba(255,255,255,0.05); display: flex; items-center; justify-content: center; border-radius: 20px;">
                        <i class="fa-solid fa-user-tie" style="color: #ffffff; font-size: 110px;"></i>
                    </div>
                @endif
            </div>

            {{-- 2. RIGHT SIDE: TEXT & QUOTE (No outer card, pure high-impact text) --}}
            <div class="sambutan-right-text" style="flex: 1 1 55%; min-width: 320px; padding: 48px 36px 48px 24px; display: flex; flex-direction: column; justify-content: center; gap: 24px;">
                
                {{-- Header Pill --}}
                <div style="display: flex; items-center; gap: 12px;">
                    <span style="background: #f59e0b; color: #000000; font-size: 11px; font-weight: 900; padding: 6px 16px; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 12px rgba(245,158,11,0.3);">
                        <i class="fa-solid fa-quote-left" style="margin-right: 6px;"></i> Sambutan Ketua Yayasan
                    </span>
                    <span style="background: rgba(255,255,255,0.1); color: #e2e8f0; font-size: 11px; font-weight: 700; padding: 6px 14px; border-radius: 20px; backdrop-filter: blur(10px);">
                        Perguruan PEMBDA Nias
                    </span>
                </div>

                {{-- Quote Body --}}
                <blockquote style="font-size: clamp(17px, 2.3vw, 21px); font-weight: 600; color: #ffffff; line-height: 1.8; margin: 0; position: relative;">
                    @php
                        $quoteRaw = \App\Models\Setting::getValue('ketua_quote', "Salam sejahtera, Ya'ahowu! Sebagai garda terdepan pendidikan di Kepulauan Nias, Yayasan Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas yang tangguh, berkarakter mulia, dan unggul secara teknologi. Selaras dengan motto abadi kami: 'Keep Moving Forward / Maju Terus Pantang Mundur', kami terus berinovasi tanpa henti melalui PembdaHUB untuk menciptakan ekosistem pembelajaran digital terbaik. Bersama, kita langkah demi langkah melangkah pasti menjawab tantangan zaman demi masa depan Nias yang gemilang!");
                        $quoteDisplay = str_replace(
                            'Keep Moving Forward / Maju Terus Pantang Mundur',
                            '<span style="color:#fbbf24; font-weight:900; text-decoration: underline decoration-amber-500 underline-offset-4;">Keep Moving Forward / Maju Terus Pantang Mundur</span>',
                            $quoteRaw
                        );
                    @endphp
                    &ldquo;{!! $quoteDisplay !!}&rdquo;
                </blockquote>

                {{-- Golden Accent Divider --}}
                <div style="width: 80px; height: 4px; background: linear-gradient(90deg, #f59e0b, #fbbf24); border-radius: 2px;"></div>

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
        min-height: 360px !important;
        width: 100% !important;
    }
    .sambutan-right-text {
        padding: 24px !important;
        width: 100% !important;
    }
}
</style>
