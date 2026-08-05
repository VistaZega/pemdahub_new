{{-- BERITA — Bento/Apple Style (Dynamic from Database) --}}
<section id="berita" class="section">
    <div class="fw">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:48px; flex-wrap:wrap; gap:16px;" data-aos="fade-up">
            <div>
                <div class="section-label">
                    <div class="section-label-dot" style="background:var(--coral);"></div>
                    <span class="section-label-text" style="color:var(--coral);">Berita & Kegiatan</span>
                </div>
                <h2 class="h1" style="margin-bottom:8px;">Berita Terbaru</h2>
                <p class="body">Informasi terkini seputar kegiatan dan prestasi Yayasan PEMBDA Nias.</p>
            </div>
        </div>

        <div class="bento bento-3" data-aos="fade-up" data-aos-delay="100">
            @forelse($news as $item)
            @php
                $catLower = strtolower($item->category_label ?? '');
                if (str_contains($catLower, 'prestasi') || str_contains($catLower, 'penghargaan') || str_contains($catLower, 'juara')) {
                    $pillBg = 'var(--emerald-bg)';
                    $pillColor = 'var(--emerald)';
                } elseif (str_contains($catLower, 'kegiatan') || str_contains($catLower, 'acara') || str_contains($catLower, 'lomba')) {
                    $pillBg = 'var(--blue-bg)';
                    $pillColor = 'var(--blue)';
                } elseif (str_contains($catLower, 'kerjasama') || str_contains($catLower, 'mitra') || str_contains($catLower, 'mou')) {
                    $pillBg = 'var(--violet-bg)';
                    $pillColor = 'var(--violet)';
                } else {
                    $pillBg = 'var(--amber-bg)';
                    $pillColor = 'var(--amber)';
                }
            @endphp
            <div class="bcard" style="padding:0; overflow:hidden;">
                <div class="news-img" style="background:linear-gradient(135deg, {{ $item->gradient_from }}, {{ $item->gradient_to }});">
                    @if($item->image)
                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <i class="{{ $item->icon }}" style="font-size:48px; color:rgba(255,255,255,0.15);"></i>
                    @endif
                    <span class="feature-pill" style="position:absolute; top:12px; left:12px; background:{{ $pillBg }}; color:{{ $pillColor }};">{{ $item->category_label }}</span>
                </div>
                <div style="padding:0 24px 24px;">
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">
                        <i class="fa-regular fa-calendar" style="margin-right:4px;"></i> {{ $item->formatted_date }}
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:17px; line-height:1.4;">{{ $item->title }}</h4>
                    <p class="body" style="font-size:14px;">{{ Str::limit($item->excerpt ?? $item->content, 120) }}</p>
                </div>
            </div>
            @empty
            {{-- Fallback: show placeholder cards if no news in database --}}
            <div class="bcard" style="padding:0; overflow:hidden;">
                <div class="news-img" style="background:linear-gradient(135deg, #2563eb, #60a5fa);">
                    <i class="fa-solid fa-trophy" style="font-size:48px; color:rgba(255,255,255,0.15);"></i>
                    <span class="feature-pill" style="position:absolute; top:12px; left:12px; background:var(--emerald-bg); color:var(--emerald);">Prestasi</span>
                </div>
                <div style="padding:0 24px 24px;">
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">
                        <i class="fa-regular fa-calendar" style="margin-right:4px;"></i> Coming Soon
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:17px; line-height:1.4;">Berita akan segera hadir</h4>
                    <p class="body" style="font-size:14px;">Nantikan informasi terbaru dari Yayasan PEMBDA Nias...</p>
                </div>
            </div>

            <div class="bcard" style="padding:0; overflow:hidden;">
                <div class="news-img" style="background:linear-gradient(135deg, #059669, #34d399);">
                    <i class="fa-solid fa-users" style="font-size:48px; color:rgba(255,255,255,0.15);"></i>
                    <span class="feature-pill" style="position:absolute; top:12px; left:12px; background:var(--blue-bg); color:var(--blue);">Kegiatan</span>
                </div>
                <div style="padding:0 24px 24px;">
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">
                        <i class="fa-regular fa-calendar" style="margin-right:4px;"></i> Coming Soon
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:17px; line-height:1.4;">Kegiatan sekolah akan segera diupdate</h4>
                    <p class="body" style="font-size:14px;">Dokumentasi kegiatan Yayasan PEMBDA akan ditampilkan di sini...</p>
                </div>
            </div>

            <div class="bcard" style="padding:0; overflow:hidden;">
                <div class="news-img" style="background:linear-gradient(135deg, #d97706, #fbbf24);">
                    <i class="fa-solid fa-handshake" style="font-size:48px; color:rgba(255,255,255,0.15);"></i>
                    <span class="feature-pill" style="position:absolute; top:12px; left:12px; background:var(--violet-bg); color:var(--violet);">Kerjasama</span>
                </div>
                <div style="padding:0 24px 24px;">
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">
                        <i class="fa-regular fa-calendar" style="margin-right:4px;"></i> Coming Soon
                    </div>
                    <h4 class="h3" style="margin-bottom:8px; font-size:17px; line-height:1.4;">Kerjasama dan MoU akan segera diperbarui</h4>
                    <p class="body" style="font-size:14px;">Informasi kerjasama Yayasan PEMBDA dengan berbagai pihak...</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>
