<style>
    .program-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
    @media (max-width: 1024px) {
        .program-grid-4 {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }
    @media (max-width: 640px) {
        .program-grid-4 {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }
</style>

{{-- PROGRAM KEAHLIAN — Full-Width Screen Enlarged Edition --}}
<section id="program" class="section" style="background: var(--bg); padding: 80px 0; width: 100%;">
    <div style="width: 100%; max-width: 1680px; margin: 0 auto; padding: 0 32px;">
        
        {{-- Section Header --}}
        <div style="text-align: center; max-width: 800px; margin: 0 auto 56px;" data-aos="fade-up">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: #000000; color: #fbbf24; font-size: 12px; font-weight: 900; padding: 7px 18px; border-radius: 20px; border: 2px solid #000000; text-transform: uppercase; margin-bottom: 16px; box-shadow: 3px 3px 0 #fbbf24;">
                <i class="fa-solid fa-bolt" style="color: #fbbf24;"></i> Program Keahlian Unggulan
            </div>
            <h2 style="font-size: 36px; font-weight: 900; color: #000000; letter-spacing: -0.5px; margin-bottom: 14px;">
                Program Keahlian <span style="color: var(--indigo);">SMKS Pembda Nias</span>
            </h2>
            <p style="font-size: 16px; color: var(--text-secondary); font-weight: 600; line-height: 1.6;">
                Mencetak lulusan berketerampilan tinggi, siap kerja, dan berdaya saing global di bidang teknik dan teknologi digital.
            </p>
        </div>

        {{-- Full-Width Grid Showcase (Enlarged Cards) --}}
        <div class="program-grid-4" data-aos="fade-up" data-aos-delay="100">
            
            {{-- 1. Teknik Otomotif (Vibrant Rose Red) --}}
            <div style="background: linear-gradient(135deg, #e11d48, #be123c); border: 3.5px solid #000000; border-radius: 32px; padding: 26px 20px; color: #ffffff; box-shadow: 10px 10px 0 #000000; transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between;" 
                 onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='14px 14px 0 #000000';" 
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='10px 10px 0 #000000';">
                
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; border-bottom: 2.5px solid rgba(0,0,0,0.3); padding-bottom: 18px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 56px; height: 56px; background: #fbbf24; border: 3px solid #000000; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #000000; box-shadow: 3px 3px 0 #000;">
                                <i class="fa-solid fa-car"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 21px; font-weight: 900; color: #ffffff; margin: 0;">Teknik Otomotif</h3>
                                <span style="font-size: 12px; font-weight: 800; color: #fecdd3;">2 Konsentrasi Unggulan</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="background: #ffffff; color: #000000; border: 2.5px solid #000000; border-radius: 18px; padding: 14px 18px; display: flex; align-items: flex-start; gap: 14px; box-shadow: 2px 2px 0 #000;">
                            <div style="width: 36px; height: 36px; background: #fff1f2; border: 2px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #e11d48; font-size: 16px; shrink: 0;">
                                <i class="fa-solid fa-truck-pickup"></i>
                            </div>
                            <div>
                                <div style="font-weight: 900; font-size: 14px; color: #000000;">Teknik Kendaraan Ringan (TKR)</div>
                                <div style="font-size: 12px; font-weight: 700; color: #475569;">Perawatan & perbaikan mesin mobil roda empat</div>
                            </div>
                        </div>

                        <div style="background: #ffffff; color: #000000; border: 2.5px solid #000000; border-radius: 18px; padding: 14px 18px; display: flex; align-items: flex-start; gap: 14px; box-shadow: 2px 2px 0 #000;">
                            <div style="width: 36px; height: 36px; background: #fff1f2; border: 2px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #e11d48; font-size: 16px; shrink: 0;">
                                <i class="fa-solid fa-motorcycle"></i>
                            </div>
                            <div>
                                <div style="font-weight: 900; font-size: 14px; color: #000000;">Teknik Sepeda Motor (TSM)</div>
                                <div style="font-size: 12px; font-weight: 700; color: #475569;">Perawatan, servis & sistem injeksi sepeda motor</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Teknik Elektronika (Vibrant Indigo Purple) --}}
            <div style="background: linear-gradient(135deg, #4f46e5, #3730a3); border: 3.5px solid #000000; border-radius: 32px; padding: 26px 20px; color: #ffffff; box-shadow: 10px 10px 0 #000000; transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between;" 
                 onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='14px 14px 0 #000000';" 
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='10px 10px 0 #000000';">
                
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; border-bottom: 2.5px solid rgba(0,0,0,0.3); padding-bottom: 18px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 56px; height: 56px; background: #38bdf8; border: 3px solid #000000; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #000000; box-shadow: 3px 3px 0 #000;">
                                <i class="fa-solid fa-tv"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 21px; font-weight: 900; color: #ffffff; margin: 0;">Teknik Elektronika</h3>
                                <span style="font-size: 12px; font-weight: 800; color: #c7d2fe;">1 Konsentrasi Unggulan</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="background: #ffffff; color: #000000; border: 2.5px solid #000000; border-radius: 18px; padding: 14px 18px; display: flex; align-items: flex-start; gap: 14px; box-shadow: 2px 2px 0 #000;">
                            <div style="width: 36px; height: 36px; background: #e0e7ff; border: 2px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #4338ca; font-size: 16px; shrink: 0;">
                                <i class="fa-solid fa-volume-high"></i>
                            </div>
                            <div>
                                <div style="font-weight: 900; font-size: 14px; color: #000000;">Teknik Audio Video</div>
                                <div style="font-size: 12px; font-weight: 700; color: #475569;">Instalasi, perbaikan & pemrosesan perangkat sistem audio-video</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Teknik Jaringan (Vibrant Teal Emerald) --}}
            <div style="background: linear-gradient(135deg, #0d9488, #0f766e); border: 3.5px solid #000000; border-radius: 32px; padding: 26px 20px; color: #ffffff; box-shadow: 10px 10px 0 #000000; transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between;" 
                 onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='14px 14px 0 #000000';" 
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='10px 10px 0 #000000';">
                
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; border-bottom: 2.5px solid rgba(0,0,0,0.3); padding-bottom: 18px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 56px; height: 56px; background: #a7f3d0; border: 3px solid #000000; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #000000; box-shadow: 3px 3px 0 #000;">
                                <i class="fa-solid fa-network-wired"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 20px; font-weight: 900; color: #ffffff; margin: 0;">Teknik Jaringan & IT</h3>
                                <span style="font-size: 12px; font-weight: 800; color: #99f6e4;">1 Konsentrasi Unggulan</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="background: #ffffff; color: #000000; border: 2.5px solid #000000; border-radius: 18px; padding: 14px 18px; display: flex; align-items: flex-start; gap: 14px; box-shadow: 2px 2px 0 #000;">
                            <div style="width: 36px; height: 36px; background: #ccfbf1; border: 2px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #0f766e; font-size: 16px; shrink: 0;">
                                <i class="fa-solid fa-server"></i>
                            </div>
                            <div>
                                <div style="font-weight: 900; font-size: 14px; color: #000000;">Teknik Komputer & Jaringan (TKJ)</div>
                                <div style="font-size: 12px; font-weight: 700; color: #475569;">Pengelolaan server, jaringan fiber optic, & infrastruktur IT digital</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Teknik Konstruksi & DPIB (Vibrant Golden Amber) --}}
            <div style="background: linear-gradient(135deg, #fbbf24, #d97706); border: 3.5px solid #000000; border-radius: 32px; padding: 26px 20px; color: #000000; box-shadow: 10px 10px 0 #000000; transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between;" 
                 onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='14px 14px 0 #000000';" 
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='10px 10px 0 #000000';">
                
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; border-bottom: 2.5px solid #000000; padding-bottom: 18px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 56px; height: 56px; background: #000000; border: 3px solid #000000; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #fbbf24; box-shadow: 3px 3px 0 #fff;">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 20px; font-weight: 900; color: #000000; margin: 0;">Teknik Konstruksi & Properti</h3>
                                <span style="font-size: 12px; font-weight: 800; color: #78350f;">1 Konsentrasi Unggulan</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="background: #ffffff; color: #000000; border: 2.5px solid #000000; border-radius: 18px; padding: 14px 18px; display: flex; align-items: flex-start; gap: 14px; box-shadow: 2px 2px 0 #000;">
                            <div style="width: 36px; height: 36px; background: #fef3c7; border: 2px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #b45309; font-size: 16px; shrink: 0;">
                                <i class="fa-solid fa-drafting-compass"></i>
                            </div>
                            <div>
                                <div style="font-weight: 900; font-size: 14px; color: #000000;">Desain Pemodelan Bangunan (DPIB)</div>
                                <div style="font-size: 12px; font-weight: 700; color: #475569;">Desain arsitektur, CAD 3D, & pemodelan gedung digital</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>
