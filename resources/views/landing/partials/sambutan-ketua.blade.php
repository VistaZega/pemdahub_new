{{-- SAMBUTAN KETUA YAYASAN — Executive Luxury Edition --}}
<section class="section-fullwidth" id="sambutan-ketua-section" style="width: 100%; position: relative; overflow: hidden; background: linear-gradient(135deg, #090d16 0%, #131728 40%, #1e1b4b 100%); color: #ffffff; padding: 70px 0; margin: 0;">
    
    {{-- Decorative Ambient Glows --}}
    <div style="position: absolute; top: -15%; left: -8%; width: 550px; height: 550px; background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; filter: blur(90px); pointer-events: none;"></div>
    <div style="position: absolute; bottom: -10%; right: -5%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(245, 158, 11, 0.2) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; filter: blur(90px); pointer-events: none;"></div>

    <div style="width: 100%; max-width: 1280px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 10;">
        
        <div class="sambutan-grid" style="display: grid; grid-template-columns: 1fr; gap: 40px; align-items: center;">
            
            @php
                $photoPath = base_path('public/images/photo-profile.jpeg');
                $photoExists = file_exists($photoPath);
                $ketuaNama = \App\Models\Setting::getValue('ketua_nama', 'Yulianus Zega, S.Kom, M.Pd.T');
                $ketuaJabatan = \App\Models\Setting::getValue('ketua_jabatan', 'Ketua Yayasan Perguruan PEMBDA Nias');
                $ketuaQuote = \App\Models\Setting::getValue('ketua_quote');
                
                // Naskah sambutan resmi jika belum di-kustomisasi atau masih default lama
                $defaultRichQuote = "Selamat datang di PembdaHUB, platform ekosistem pendidikan digital masa depan Yayasan Perguruan PEMBDA Nias. Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas Kepulauan Nias yang tidak hanya tangguh dan cerdas secara akademis, tetapi juga memiliki integritas karakter yang mulia serta menguasai teknologi modern secara profesional.\n\nSelaras dengan motto abadi perjuangan kami: 'Keep Moving Forward / Maju Terus Pantang Mundur', kami terus berinovasi tanpa henti membangun lingkungan belajar berbasis teknologi digital terkini untuk menjawab tantangan era globalisasi.\n\nMari bersama-sama kita bergandengan tangan—pendidik, siswa, orang tua, dan alumni—melangkah pasti mewujudkan masa depan Nias yang gemilang, berdaya saing tinggi, dan berintegritas!";
                
                $finalQuote = ($ketuaQuote && strlen($ketuaQuote) > 50) ? $ketuaQuote : $defaultRichQuote;
            @endphp

            <div class="sambutan-card-wrapper" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-top: 1px solid rgba(245, 158, 11, 0.4); border-radius: 28px; padding: 40px; backdrop-filter: blur(16px); box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);">
                
                <div style="display: flex; flex-wrap: wrap; gap: 40px; align-items: center;">
                    
                    {{-- 1. LEFT SIDE: EXECUTIVE PORTRAIT --}}
                    <div class="sambutan-photo-col" style="flex: 1 1 320px; max-width: 380px; position: relative;">
                        
                        <div style="position: relative; border-radius: 24px; overflow: hidden; border: 2px solid rgba(245, 158, 11, 0.4); box-shadow: 0 15px 35px rgba(0,0,0,0.5); background: #0f172a;">
                            @if($photoExists)
                                <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" 
                                     alt="{{ $ketuaNama }}" 
                                     style="width: 100%; height: 420px; object-fit: cover; object-position: center 15%; filter: contrast(1.05) brightness(1.02); display: block;" />
                            @else
                                <div style="width: 100%; height: 420px; background: linear-gradient(135deg, #1e293b, #0f172a); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-user-tie" style="color: #fbbf24; font-size: 100px;"></i>
                                </div>
                            @endif

                            {{-- Overlay Gradient Bottom --}}
                            <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(15, 23, 42, 0.95) 0%, rgba(15, 23, 42, 0.2) 40%, rgba(0,0,0,0) 70%); pointer-events: none;"></div>

                            {{-- Floating Badge on Photo --}}
                            <div style="position: absolute; bottom: 16px; left: 16px; right: 16px; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(245, 158, 11, 0.4); padding: 12px 16px; border-radius: 16px; backdrop-filter: blur(12px);">
                                <div style="font-size: 11px; font-weight: 800; color: #fbbf24; text-transform: uppercase; letter-spacing: 1px; display: flex; items-center; gap: 6px;">
                                    <i class="fa-solid fa-award"></i> Visionary Leadership
                                </div>
                                <div style="font-size: 14px; font-weight: 800; color: #ffffff; margin-top: 2px;">
                                    {{ $ketuaNama }}
                                </div>
                                <div style="font-size: 11px; color: #cbd5e1; margin-top: 1px;">
                                    {{ $ketuaJabatan }}
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- 2. RIGHT SIDE: INSPIRATIONAL SPEECH CONTENT --}}
                    <div class="sambutan-text-col" style="flex: 2 1 450px; display: flex; flex-direction: column; justify-content: center; gap: 20px;">
                        
                        {{-- Tag Badge --}}
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; font-size: 11px; font-weight: 900; padding: 6px 16px; border-radius: 30px; text-transform: uppercase; letter-spacing: 1.2px; box-shadow: 0 4px 15px rgba(245,158,11,0.35); display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-quote-left"></i> Sambutan Ketua Yayasan
                            </span>
                            <span style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #e2e8f0; font-size: 11px; font-weight: 700; padding: 6px 14px; border-radius: 30px; backdrop-filter: blur(10px);">
                                Perguruan PEMBDA Nias
                            </span>
                        </div>

                        {{-- Main Heading --}}
                        <h2 style="font-size: clamp(22px, 2.5vw, 30px); font-weight: 900; color: #ffffff; line-height: 1.35; margin: 0; letter-spacing: -0.5px;">
                            "Membangun Generasi Emas Nias yang Unggul, Berkarakter & Menguasai Teknologi"
                        </h2>

                        {{-- Greeting --}}
                        <div style="font-size: 18px; font-weight: 900; color: #fbbf24; letter-spacing: 0.5px;">
                            Ya'ahowu! Salam Sejahtera untuk Kita Semua.
                        </div>

                        {{-- Speech Body Paragraphs --}}
                        <div style="font-size: clamp(14px, 1.1vw, 15px); font-weight: 400; color: #cbd5e1; line-height: 1.8; margin: 0; space-y-3;" class="speech-paragraphs">
                            @foreach(explode("\n\n", $finalQuote) as $paragraph)
                                @if(trim($paragraph))
                                    <p style="margin: 0 0 14px 0;">{!! nl2br(e(trim($paragraph))) !!}</p>
                                @endif
                            @endforeach
                        </div>

                        {{-- Key Motto Glass Card --}}
                        <div style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(99, 102, 241, 0.1) 100%); border-left: 4px solid #f59e0b; border-radius: 14px; padding: 14px 20px; display: flex; align-items: center; gap: 14px;">
                            <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(245, 158, 11, 0.2); color: #fbbf24; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                <i class="fa-solid fa-compass"></i>
                            </div>
                            <div>
                                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #fbbf24; letter-spacing: 1px;">Motto Perjuangan</div>
                                <div style="font-size: 15px; font-weight: 900; color: #ffffff; margin-top: 2px;">
                                    "Keep Moving Forward / Maju Terus Pantang Mundur"
                                </div>
                            </div>
                        </div>

                        {{-- Pillars / Key Value Badges --}}
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; pt-2; margin-top: 6px;">
                            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); padding: 8px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; color: #e2e8f0; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-graduation-cap text-amber-400"></i> Karakter & Integritas
                            </div>
                            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); padding: 8px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; color: #e2e8f0; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-laptop-code text-indigo-400"></i> PembdaHUB Digital Ecosystem
                            </div>
                            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); padding: 8px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; color: #e2e8f0; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-chart-line text-emerald-400"></i> Daya Saing Masa Depan
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>

<style>
@media (max-width: 768px) {
    .sambutan-card-wrapper {
        padding: 24px !important;
    }
    .sambutan-photo-col {
        max-width: 100% !important;
    }
}
</style>
