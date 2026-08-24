{{-- PSB CTA — Bento/Apple Style (Dynamic) --}}
<section id="psb" class="section">
    <div class="fw">
        <div class="bcard bcard-dark span-3 hover-glow" data-aos="fade-up" style="text-align:center; padding:72px 40px; border:none; position:relative; overflow:hidden;">
            {{-- Decorative bg elements --}}
            <div style="position:absolute; top:-100px; left:-100px; width:300px; height:300px; background:radial-gradient(circle, rgba(245,158,11,0.1) 0%, rgba(0,0,0,0) 70%); border-radius:50%; z-index:0;"></div>
            <div style="position:absolute; bottom:-100px; right:-100px; width:400px; height:400px; background:radial-gradient(circle, rgba(16,185,129,0.05) 0%, rgba(0,0,0,0) 70%); border-radius:50%; z-index:0;"></div>
            
            <div style="max-width:680px; margin:0 auto; position:relative; z-index:1;">
                @if(isset($activeWave) && $activeWave)
                    <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); padding:8px 24px; border-radius:100px; margin-bottom:28px;">
                        <div class="pulse" style="background:var(--emerald);"></div>
                        <span style="color:#6ee7b7; font-size:13px; font-weight:700; letter-spacing:0.05em; text-transform:uppercase;">Pendaftaran Dibuka</span>
                    </div>

                    <h2 style="font-size:clamp(32px,5vw,48px); font-weight:900; color:#fff; letter-spacing:-0.02em; line-height:1.1; margin-bottom:16px;">
                        Penerimaan Siswa Baru<br>
                        <span style="background:linear-gradient(135deg, #fbbf24, #f59e0b); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">{{ $activeWave->name }}</span>
                    </h2>

                    <p style="font-size:18px; color:#94a3b8; line-height:1.6; margin-bottom:40px;">
                        Mari bergabung menjadi bagian dari <strong style="color:#fcd34d;">Perguruan PEMBDA Nias</strong>.<br>
                        Wujudkan pendidikan berkualitas untuk masa depan yang lebih baik.
                    </p>

                    <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap; margin-bottom:48px;">
                        <a href="{{ route('public.registration.index') }}" class="btn btn-gold" style="padding:18px 40px; font-size:16px;">
                            <i class="fa-solid fa-user-plus" style="margin-right:8px;"></i> Daftar Sekarang
                        </a>
                        <a href="{{ route('public.registration.check') }}" class="btn btn-ghost-white" style="padding:18px 40px; font-size:16px;">
                            <i class="fa-solid fa-magnifying-glass" style="margin-right:8px;"></i> Cek Status
                        </a>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:center; gap:40px; flex-wrap:wrap; background:rgba(0,0,0,0.2); padding:20px; border-radius:16px; border:1px solid rgba(255,255,255,0.05);">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div class="icon-circle" style="width:40px; height:40px; background:rgba(245,158,11,0.1); color:var(--gold);">
                                <i class="fa-regular fa-calendar"></i>
                            </div>
                            <div style="text-align:left;">
                                <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Batas Akhir</div>
                                <div style="font-size:14px; color:#e2e8f0; font-weight:600;">{{ \Carbon\Carbon::parse($activeWave->end_date)->translatedFormat('d F Y') }}</div>
                            </div>
                        </div>
                        
                        <div style="width:1px; height:32px; background:rgba(255,255,255,0.1);"></div>
                        
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div class="icon-circle" style="width:40px; height:40px; background:rgba(59,130,246,0.1); color:var(--blue);">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div style="text-align:left;">
                                <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Kuota</div>
                                <div style="font-size:14px; color:#e2e8f0; font-weight:600;">{{ $activeWave->quota - $activeWave->registered_count }} Sisa</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div style="display:inline-flex; align-items:center; gap:10px; background:rgba(239,68,68,0.2); border:1.5px solid rgba(239,68,68,0.4); padding:10px 26px; border-radius:100px; margin-bottom:28px;">
                        <span style="color:#fecaca; font-size:13px; font-weight:800; letter-spacing:0.05em; text-transform:uppercase;"><i class="fa-solid fa-circle-check" style="margin-right:8px; color:#ef4444;"></i> Pendaftaran Telah Selesai</span>
                    </div>

                    <h2 style="font-size:clamp(32px,5vw,48px); font-weight:900; color:#ffffff; letter-spacing:-0.02em; line-height:1.15; margin-bottom:18px;">
                        Penerimaan Siswa Baru
                    </h2>

                    <p style="font-size:17.5px; color:#ffffff; line-height:1.7; margin-bottom:38px; max-width:620px; margin-left:auto; margin-right:auto; font-weight:500;">
                        Proses penerimaan siswa baru telah selesai dan <strong style="color:#fbbf24; font-weight:800;">Kegiatan Belajar Mengajar (KBM) Semester Ganjil TP. 2026/2027</strong> sedang berlangsung aktif.<br>
                        <span style="color:rgba(255,255,255,0.85); font-size:15px;">Informasi gelombang pendaftaran baru akan diumumkan menjelang periode tahun ajaran berikutnya.</span>
                    </p>

                    <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
                        <a href="{{ route('public.registration.check') }}" class="btn btn-gold" style="padding:16px 36px; font-size:15px; font-weight:800;">
                            <i class="fa-solid fa-magnifying-glass" style="margin-right:6px;"></i> Cek Status Pendaftar
                        </a>
                        <a href="#kontak" class="btn btn-ghost-white" style="padding:16px 36px; font-size:15px; font-weight:700;">
                            <i class="fa-solid fa-headset" style="margin-right:6px;"></i> Hubungi Sekretariat
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
