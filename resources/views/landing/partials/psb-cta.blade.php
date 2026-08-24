{{-- PSB CTA — Bright & Cheerful Luminous Banner --}}
<section id="psb" class="section" style="padding: 60px 0 100px;">
    <div class="fw">
        <div class="bcard span-3" data-aos="fade-up" style="text-align:center; padding:72px 40px; background:linear-gradient(135deg, #eff6ff 0%, #ffffff 50%, #f0fdf4 100%); border:2px solid #bfdbfe; box-shadow:0 25px 60px -15px rgba(37,99,235,0.12); position:relative; overflow:hidden; border-radius:28px;">
            
            {{-- Decorative Cheerful Blobs --}}
            <div style="position:absolute; top:-100px; left:-100px; width:320px; height:320px; background:radial-gradient(circle, rgba(251,191,36,0.2) 0%, rgba(0,0,0,0) 70%); border-radius:50%; pointer-events:none;"></div>
            <div style="position:absolute; bottom:-100px; right:-100px; width:380px; height:380px; background:radial-gradient(circle, rgba(16,185,129,0.18) 0%, rgba(0,0,0,0) 70%); border-radius:50%; pointer-events:none;"></div>
            
            <div style="max-width:720px; margin:0 auto; position:relative; z-index:1;">
                @if(isset($activeWave) && $activeWave)
                    <div style="display:inline-flex; align-items:center; gap:8px; background:#dcfce7; border:1.5px solid #86efac; padding:8px 24px; border-radius:100px; margin-bottom:28px; box-shadow:0 2px 10px rgba(16,185,129,0.1);">
                        <span style="width:8px; height:8px; background:#16a34a; border-radius:50%;"></span>
                        <span style="color:#15803d; font-size:13px; font-weight:800; letter-spacing:0.05em; text-transform:uppercase;">Pendaftaran Dibuka</span>
                    </div>

                    <h2 style="font-size:clamp(32px,5vw,48px); font-weight:900; color:#0f172a; letter-spacing:-0.02em; line-height:1.15; margin-bottom:16px;">
                        Penerimaan Siswa Baru<br>
                        <span style="background:linear-gradient(135deg, #2563eb, #7c3aed); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">{{ $activeWave->name }}</span>
                    </h2>

                    <p style="font-size:17.5px; color:#334155; line-height:1.7; margin-bottom:36px; font-weight:500;">
                        Mari bergabung menjadi bagian dari <strong style="color:#2563eb; font-weight:800;">Perguruan PEMBDA Nias</strong>.<br>
                        Wujudkan pendidikan berkualitas dan masa depan cemerlang bersama kami.
                    </p>

                    <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap; margin-bottom:40px;">
                        <a href="{{ route('public.registration.index') }}" class="btn btn-gold" style="padding:16px 36px; font-size:15px; font-weight:800;">
                            <i class="fa-solid fa-user-plus"></i> Daftar Sekarang
                        </a>
                        <a href="{{ route('public.registration.check') }}" class="btn btn-ghost" style="background:#ffffff; border-color:#cbd5e1; padding:16px 36px; font-size:15px; font-weight:700;">
                            <i class="fa-solid fa-magnifying-glass" style="color:#2563eb;"></i> Cek Status
                        </a>
                    </div>
                @else
                    <div style="display:inline-flex; align-items:center; gap:10px; background:#fee2e2; border:1.5px solid #fca5a5; padding:8px 24px; border-radius:100px; margin-bottom:24px;">
                        <span style="color:#dc2626; font-size:13px; font-weight:800; letter-spacing:0.05em; text-transform:uppercase;"><i class="fa-solid fa-circle-check" style="margin-right:6px;"></i> Pendaftaran Telah Selesai</span>
                    </div>

                    <h2 style="font-size:clamp(30px,4.5vw,44px); font-weight:900; color:#0f172a; letter-spacing:-0.02em; line-height:1.2; margin-bottom:16px;">
                        Penerimaan Siswa Baru
                    </h2>

                    <p style="font-size:17px; color:#334155; line-height:1.75; margin-bottom:36px; font-weight:500;">
                        Proses penerimaan siswa baru telah selesai dan <strong style="color:#2563eb; font-weight:800;">Kegiatan Belajar Mengajar (KBM) Semester Ganjil TP. 2026/2027</strong> sedang berlangsung aktif.<br>
                        <span style="color:#64748b; font-size:14.5px;">Informasi gelombang pendaftaran baru akan diumumkan menjelang periode tahun ajaran berikutnya.</span>
                    </p>

                    <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
                        <a href="{{ route('public.registration.check') }}" class="btn btn-gold" style="padding:16px 36px; font-size:15px; font-weight:800;">
                            <i class="fa-solid fa-magnifying-glass"></i> Cek Status Pendaftar
                        </a>
                        <a href="#kontak" class="btn btn-ghost" style="background:#ffffff; border-color:#cbd5e1; padding:16px 36px; font-size:15px; font-weight:700;">
                            <i class="fa-solid fa-headset" style="color:#2563eb;"></i> Hubungi Sekretariat
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
