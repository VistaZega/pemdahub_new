{{-- SAMBUTAN KETUA YAYASAN — Vibrant Pop Neo-Brutalism Executive Edition --}}
<section class="section" id="sambutan-ketua-section" style="background: var(--bg); padding: 80px 0;">
    <div style="max-width: 1240px; margin: 0 auto; padding: 0 24px;">

        <div style="background: #ffffff; border: 3px solid #000000; border-radius: 36px; padding: 48px; box-shadow: 12px 12px 0 #000000; position: relative; overflow: hidden;" data-aos="fade-up">
            
            {{-- Background Decorative Accents --}}
            <div style="position: absolute; top: -40px; right: -40px; width: 220px; height: 220px; background: #fbbf24; border-radius: 50%; opacity: 0.15; pointer-events: none;"></div>
            <div style="position: absolute; bottom: -50px; left: -50px; width: 250px; height: 250px; background: #6366f1; border-radius: 50%; opacity: 0.1; pointer-events: none;"></div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: center; position: relative; z-index: 10;">

                {{-- Left: Content & Quote --}}
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    
                    {{-- Section Tag --}}
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: #fbbf24; color: #000000; font-size: 11px; font-weight: 900; padding: 6px 14px; border-radius: 12px; border: 2px solid #000000; text-transform: uppercase; tracking-wider: 1px; box-shadow: 2px 2px 0 #000;">
                            <i class="fa-solid fa-bullhorn" style="margin-right: 6px;"></i> Sambutan Ketua Yayasan
                        </span>
                        <span style="background: #000000; color: #ffffff; font-size: 10px; font-weight: 900; padding: 6px 12px; border-radius: 12px; text-transform: uppercase;">
                            Pesan Kepemimpinan
                        </span>
                    </div>

                    {{-- Giant Quote & Message --}}
                    <div style="position: relative;">
                        <div style="font-size: 80px; color: #fbbf24; font-family: Georgia, serif; line-height: 0.8; font-weight: 900; margin-bottom: -20px; user-select: none;">
                            &ldquo;
                        </div>
                        <blockquote style="font-size: clamp(16px, 2.2vw, 19px); font-weight: 700; color: #000000; line-height: 1.8; font-style: normal; position: relative; z-index: 2; margin: 0;">
                            @php
                                $quoteRaw = \App\Models\Setting::getValue('ketua_quote', "Salam sejahtera, Ya'ahowu! Sebagai garda terdepan pendidikan di Kepulauan Nias, Yayasan Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas yang tangguh, berkarakter mulia, dan unggul secara teknologi. Selaras dengan motto abadi kami: 'Keep Moving Forward / Maju Terus Pantang Mundur', kami terus berinovasi tanpa henti melalui PembdaHUB untuk menciptakan ekosistem pembelajaran digital terbaik. Bersama, kita langkah demi langkah melangkah pasti menjawab tantangan zaman demi masa depan Nias yang gemilang!");
                                $quoteDisplay = str_replace(
                                    'Keep Moving Forward / Maju Terus Pantang Mundur',
                                    '<span style="background:#fbbf24; color:#000000; padding: 2px 8px; border-radius:6px; border: 1.5px solid #000; font-weight:900;">Keep Moving Forward / Maju Terus Pantang Mundur</span>',
                                    $quoteRaw
                                );
                            @endphp
                            {!! $quoteDisplay !!}
                        </blockquote>
                    </div>

                    {{-- Core Pillars Badges --}}
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px;">
                        <span style="background: #e0e7ff; color: #3730a3; border: 1.5px solid #000000; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 10px;">
                            <i class="fa-solid fa-graduation-cap" style="margin-right: 4px;"></i> Pendidikan Karakter
                        </span>
                        <span style="background: #ecfdf5; color: #065f46; border: 1.5px solid #000000; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 10px;">
                            <i class="fa-solid fa-laptop-code" style="margin-right: 4px;"></i> Inovasi PembdaHUB
                        </span>
                        <span style="background: #fffbeb; color: #92400e; border: 1.5px solid #000000; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 10px;">
                            <i class="fa-solid fa-rocket" style="margin-right: 4px;"></i> SDM Unggul Nias
                        </span>
                    </div>

                    {{-- Separator Line --}}
                    <div style="height: 3px; background: #000000; width: 60px; border-radius: 2px; margin-top: 4px;"></div>

                    {{-- Executive Name & Title --}}
                    <div>
                        <h3 style="font-size: 20px; font-weight: 900; color: #000000; margin: 0 0 4px 0;">
                            {{ \App\Models\Setting::getValue('ketua_nama', 'Yulianus Zega, S.Kom, M.Pd.T') }}
                        </h3>
                        <p style="font-size: 13px; font-weight: 800; color: #475569; margin: 0;">
                            {{ \App\Models\Setting::getValue('ketua_jabatan', 'Ketua Yayasan Perguruan PEMBDA Nias') }}
                        </p>
                    </div>

                </div>

                {{-- Right: Executive HD Portrait Frame --}}
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div style="position: relative; width: 100%; max-width: 340px;">
                        
                        {{-- Photo Frame --}}
                        <div style="background: #4f46e5; border: 3px solid #000000; border-radius: 32px; padding: 10px; box-shadow: 8px 8px 0 #000000; overflow: hidden; transform: rotate(-1.5deg); transition: transform 0.3s ease;" onmouseover="this.style.transform='rotate(0deg) scale(1.02)'" onmouseout="this.style.transform='rotate(-1.5deg) scale(1)'">
                            @php
                                $photoPath = base_path('public/images/photo-profile.jpeg');
                                $photoExists = file_exists($photoPath);
                            @endphp
                            <div style="width: 100%; height: 380px; border-radius: 24px; overflow: hidden; border: 2px solid #000000; background: #1e1b4b; display: flex; align-items: center; justify-content: center;">
                                @if($photoExists)
                                    <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" alt="Yulianus Zega, S.Kom, M.Pd.T" style="width: 100%; height: 100%; object-fit: cover; object-position: top;">
                                @else
                                    <i class="fa-solid fa-user-tie" style="color: #ffffff; font-size: 90px;"></i>
                                @endif
                            </div>
                        </div>

                        {{-- Official Seal Badge Overlay --}}
                        <div style="position: absolute; bottom: -16px; left: 50%; transform: translateX(-50%); background: #fbbf24; border: 2.5px solid #000000; border-radius: 16px; padding: 8px 18px; box-shadow: 4px 4px 0 #000000; white-space: nowrap; font-size: 11px; font-weight: 900; color: #000000; display: flex; align-items: center; gap: 8px; z-index: 20;">
                            <i class="fa-solid fa-certificate" style="color: #000000; font-size: 14px;"></i>
                            <span>PEMBDA NIAS LEADER</span>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>
</section>
