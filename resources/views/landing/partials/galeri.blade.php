{{-- GALERI — Bento/Apple Style (Dynamic from Database) --}}
<section id="galeri" class="section" style="background:#fff;">
    <div class="fw">
        <div style="text-align:center; margin-bottom:48px;" data-aos="fade-up">
            <div class="section-label" style="justify-content:center;">
                <div class="section-label-dot" style="background:var(--cyan);"></div>
                <span class="section-label-text" style="color:var(--cyan);">Galeri</span>
            </div>
            <h2 class="h1" style="margin-bottom:12px;">Momen & Kegiatan</h2>
            <p class="body-lg" style="max-width:500px; margin:0 auto;">Dokumentasi berbagai kegiatan di Yayasan PEMBDA Nias.</p>
        </div>

        <div class="gallery-bento" data-aos="fade-up" data-aos-delay="100">
            @forelse($galleryItems as $item)
            <div class="gal-item" style="{{ $item->image ? '' : 'background:' . $item->category_gradient . ';' }}">
                @if($item->image)
                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <i class="{{ $item->category_icon }}" style="font-size:{{ $loop->first ? '56px' : '28px' }};"></i>
                @endif
                <span class="gal-label">{{ $item->title }}</span>
            </div>
            @empty
            {{-- Fallback: show placeholder gallery items --}}
            <div class="gal-item" style="background:linear-gradient(135deg, #0f172a, #1e3a5f);">
                <i class="fa-solid fa-school" style="font-size:56px;"></i>
                <span class="gal-label">Upacara Bendera</span>
            </div>
            <div class="gal-item" style="background:linear-gradient(135deg, #2563eb, #60a5fa);">
                <i class="fa-solid fa-flask"></i>
                <span class="gal-label">Praktikum Lab</span>
            </div>
            <div class="gal-item" style="background:linear-gradient(135deg, #059669, #34d399);">
                <i class="fa-solid fa-futbol"></i>
                <span class="gal-label">Olahraga</span>
            </div>
            <div class="gal-item" style="background:linear-gradient(135deg, #7c3aed, #a78bfa);">
                <i class="fa-solid fa-music"></i>
                <span class="gal-label">Pentas Seni</span>
            </div>
            <div class="gal-item" style="background:linear-gradient(135deg, #d97706, #fbbf24);">
                <i class="fa-solid fa-wrench"></i>
                <span class="gal-label">Praktik Bengkel</span>
            </div>
            <div class="gal-item" style="background:linear-gradient(135deg, #dc2626, #f87171);">
                <i class="fa-solid fa-trophy"></i>
                <span class="gal-label">Prestasi</span>
            </div>
            <div class="gal-item" style="background:linear-gradient(135deg, #0891b2, #22d3ee);">
                <i class="fa-solid fa-laptop-code"></i>
                <span class="gal-label">Lab Komputer</span>
            </div>
            @endforelse
        </div>
    </div>
</section>
