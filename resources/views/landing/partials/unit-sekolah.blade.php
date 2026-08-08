{{-- UNIT SEKOLAH — Bento/Apple Style --}}
<section id="sekolah" class="section">
    <div class="fw">
        <div style="text-align:center; margin-bottom:56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center;">
                <div class="section-label-dot" style="background:var(--violet);"></div>
                <span class="section-label-text" style="color:var(--violet);">Unit Sekolah</span>
            </div>
            <h2 class="h1" style="margin-bottom:12px;">Pusat Keunggulan Pendidikan</h2>
            <p class="body-lg" style="max-width:600px; margin:0 auto;">Masing-masing unit memiliki keunggulan dan komitmen penuh terhadap pendidikan yang bermutu, didukung fasilitas lengkap dan pengajar profesional.</p>
        </div>

        <div class="bento bento-3" data-aos="fade-up" data-aos-delay="100">
            @foreach($schools as $school)
                @php
                    $isSMA = str_contains(strtolower($school->type), 'sma');
                    $isSMP = str_contains(strtolower($school->type), 'smp');
                    $isSMK = str_contains(strtolower($school->type), 'smk');
                    
                    if ($isSMA) {
                        $bgHeader = 'linear-gradient(135deg, #2563eb, #60a5fa)';
                        $iconClass = 'fa-solid fa-graduation-cap';
                        $pillBg = 'var(--blue-bg)';
                        $pillColor = 'var(--blue)';
                        $defaultDesc = 'Pusat Keunggulan Akademik Berakreditasi A yang membentuk generasi muda unggul, berkarakter mulia, berwawasan global, dan siap menembus Perguruan Tinggi Negeri (PTN) terfavorit di Indonesia.';
                    } elseif ($isSMP) {
                        $bgHeader = 'linear-gradient(135deg, #059669, #34d399)';
                        $iconClass = 'fa-solid fa-school';
                        $pillBg = 'var(--emerald-bg)';
                        $pillColor = 'var(--emerald)';
                        $defaultDesc = 'Membangun fondasi karakter yang kokoh, disiplin, dan keunggulan potensi akademik serta minat bakat siswa secara holistik menuju jenjang pendidikan menengah atas terdepan.';
                    } elseif ($isSMK) {
                        $bgHeader = 'linear-gradient(135deg, #d97706, #fbbf24)';
                        $iconClass = 'fa-solid fa-gears';
                        $pillBg = 'var(--amber-bg)';
                        $pillColor = 'var(--amber)';
                        $defaultDesc = 'Pusat Pendidikan Vokasi Kejuruan & Teknologi Industri Modern yang mencetak lulusan terampil, kompeten, siap kerja di DUDI mitra, serta berjiwa kewirausahaan yang tangguh.';
                    } else {
                        $bgHeader = 'linear-gradient(135deg, #8b5cf6, #c4b5fd)';
                        $iconClass = 'fa-solid fa-school-flag';
                        $pillBg = 'var(--violet-bg)';
                        $pillColor = 'var(--violet)';
                        $defaultDesc = 'Pusat pendidikan bermutu tinggi yang membentuk generasi penerus berkualitas dan berkarakter.';
                    }
                @endphp
                
                <div class="bcard unit-card">
                    <div class="unit-header" style="background:{{ $bgHeader }};">
                        <div style="position:absolute; top:-15px; right:-15px; width:80px; height:80px; border-radius:50%; background:rgba(255,255,255,0.08);"></div>
                        <i class="{{ $iconClass }}" style="font-size:48px; color:rgba(255,255,255,0.85);"></i>
                    </div>
                    
                    <div style="display:flex; gap:8px; margin-bottom:12px; flex-wrap:wrap;">
                        <span class="feature-pill" style="background:{{ $pillBg }}; color:{{ $pillColor }};">
                            {{ $school->type }} Swasta
                        </span>
                        @if($school->npsn)
                        <span class="feature-pill" style="background:var(--bg); color:var(--text-secondary); border: 1px solid var(--border);">
                            NPSN: {{ $school->npsn }}
                        </span>
                        @endif
                    </div>
                    
                    <h3 class="h3" style="margin-bottom:8px;">{{ $school->name }}</h3>
                    <p class="body" style="margin-bottom: 20px;">{{ $school->psb_description ?: $defaultDesc }}</p>
                    
                    {{-- Realtime Stats Line --}}
                    <div style="display:flex; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 16px;">
                        <div style="text-align:center;">
                            <div style="font-size:18px; font-weight:800; color:var(--text-primary);">{{ number_format($school->students_count ?? 0, 0, ',', '.') }}</div>
                            <div style="font-size:11px; font-weight:600; color:var(--text-secondary); text-transform:uppercase;">Siswa</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:18px; font-weight:800; color:var(--text-primary);">{{ $school->teachers_count ?? 0 }}</div>
                            <div style="font-size:11px; font-weight:600; color:var(--text-secondary); text-transform:uppercase;">Guru</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:18px; font-weight:800; color:var(--text-primary);">{{ $school->classrooms_count ?? 0 }}</div>
                            <div style="font-size:11px; font-weight:600; color:var(--text-secondary); text-transform:uppercase;">Kelas</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
