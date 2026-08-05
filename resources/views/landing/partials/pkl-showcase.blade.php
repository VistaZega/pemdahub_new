{{-- SOROTAN PKL — Showcase Logbook & Monitoring Pembimbing --}}
@if(isset($pklShowcase) && $pklShowcase->count() > 0)
<style>
/* === PKL SHOWCASE SECTION === */
.pkl-section {
    padding: 5rem 1rem;
    background: transparent;
    position: relative;
    overflow: hidden;
}
.pkl-section::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -120px;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.pkl-section::after {
    content: '';
    position: absolute;
    bottom: -60px;
    left: -80px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16,185,129,0.06) 0%, transparent 70%);
    pointer-events: none;
}
.pkl-container {
    width: 100%;
    padding: 0 40px;
    margin: 0 auto;
    position: relative;
    z-index: 10;
}
@media (max-width: 768px) {
    .pkl-container { padding: 0 20px; }
}

/* Header */
.pkl-header {
    text-align: center;
    margin-bottom: 3rem;
}
.pkl-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 1rem;
}
.pkl-label-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--emerald, #10b981);
    animation: pklPulse 2s ease-in-out infinite;
}
@keyframes pklPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.3); }
}
.pkl-label-text {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--emerald, #10b981);
}
.pkl-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary, #0f0d2e);
    margin: 0 0 0.75rem 0;
    line-height: 1.2;
}
.pkl-title span {
    background: linear-gradient(135deg, var(--indigo, #6366f1), var(--emerald, #10b981));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.pkl-subtitle {
    font-size: 1.1rem;
    color: var(--text-secondary, #5b6478);
    max-width: 640px;
    margin: 0 auto;
    line-height: 1.7;
}

/* Stats Bar */
.pkl-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    max-width: 700px;
    margin: 0 auto 3rem;
}
.pkl-stat-card {
    background: rgba(15, 23, 42, 0.6); /* Contrast slate color against purple */
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 1.25rem 1rem;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}
.pkl-stat-card:hover {
    transform: translateY(-4px);
    background: rgba(15, 23, 42, 0.85);
    box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.4);
}
/* Variasi Warna Card Stats */
.stat-indigo { border-bottom: 3px solid rgba(99,102,241,0.6); }
.stat-indigo:hover { border-color: rgba(99,102,241,1); box-shadow: 0 12px 30px -5px rgba(99,102,241,0.25); }

.stat-emerald { border-bottom: 3px solid rgba(16,185,129,0.6); }
.stat-emerald:hover { border-color: rgba(16,185,129,1); box-shadow: 0 12px 30px -5px rgba(16,185,129,0.25); }

.stat-amber { border-bottom: 3px solid rgba(245,158,11,0.6); }
.stat-amber:hover { border-color: rgba(245,158,11,1); box-shadow: 0 12px 30px -5px rgba(245,158,11,0.25); }
.pkl-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 0.75rem;
}
.pkl-stat-number {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-primary, #0f0d2e);
    line-height: 1;
    margin-bottom: 0.25rem;
}
.pkl-stat-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-secondary, #94a3b8);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    opacity: 0.9;
}

/* Carousel Container */
.pkl-carousel-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
}

.pkl-carousel-track {
    display: flex;
    transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    will-change: transform;
}

.pkl-slide {
    flex: 0 0 33.333%;
    padding: 0 0.75rem;
    box-sizing: border-box;
    transition: all 0.3s ease;
}
@media (min-width: 1441px) {
    .pkl-slide {
        flex: 0 0 25%;
    }
}

/* Carousel Card */
.pkl-card {
    background: rgba(15, 23, 42, 0.6); /* Contrast slate color against purple */
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.15); /* Garis Penjelas (Borders) */
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.pkl-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px -12px rgba(79,46,209,0.18);
    border-color: rgba(99,102,241,0.3);
}

/* Photo Container */
.pkl-photo-container {
    position: relative;
    width: 100%;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
    overflow: hidden;
}
.pkl-photo {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.pkl-card:hover .pkl-photo {
    transform: scale(1.05);
}

/* Photo Overlay Gradient */
.pkl-photo-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 50%;
    background: linear-gradient(to top, rgba(0,0,0,0.45), transparent);
    pointer-events: none;
}

/* Badge */
.pkl-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    backdrop-filter: blur(8px);
    z-index: 5;
}
.pkl-badge-logbook {
    background: rgba(99,102,241,0.9);
    color: #fff;
}
.pkl-badge-monitoring {
    background: rgba(16,185,129,0.9);
    color: #fff;
}

/* Date badge */
.pkl-date-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    background: rgba(255,255,255,0.9);
    color: var(--text-primary, #0f0d2e);
    backdrop-filter: blur(8px);
    z-index: 5;
}

/* Card Content */
.pkl-card-body {
    padding: 1.25rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}
.pkl-description {
    font-size: 0.9rem;
    color: var(--text-secondary, #5b6478);
    line-height: 1.6;
    margin-bottom: 1rem;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Person Info */
.pkl-person {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(224,221,247,0.5);
}
.pkl-person-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(224,221,247,0.5);
    flex-shrink: 0;
    background: var(--bg, #f4f3ff);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted, #9ca3af);
    font-size: 18px;
}
.pkl-person-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.pkl-person-info {
    flex: 1;
    min-width: 0;
}
.pkl-person-name {
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--text-primary, #0f0d2e);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pkl-person-meta {
    font-size: 0.75rem;
    color: var(--text-secondary, #5b6478);
    margin: 2px 0 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pkl-dudi-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    background: var(--bg, #f4f3ff);
    color: var(--indigo, #6366f1);
    border: 1px solid rgba(224,221,247,0.5);
    flex-shrink: 0;
}

/* Carousel Controls */
.pkl-carousel-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 2rem;
}
.pkl-carousel-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid var(--border, #e0ddf7);
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(8px);
    color: var(--text-primary, #0f0d2e);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: all 0.3s ease;
}
.pkl-carousel-btn:hover {
    background: var(--indigo, #6366f1);
    color: #fff;
    border-color: var(--indigo, #6366f1);
    transform: scale(1.1);
}
.pkl-carousel-btn:disabled {
    opacity: 0.3;
    cursor: default;
    transform: none;
}

/* Dots */
.pkl-dots {
    display: flex;
    gap: 8px;
    align-items: center;
}
.pkl-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--border, #e0ddf7);
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    padding: 0;
}
.pkl-dot.active {
    background: var(--indigo, #6366f1);
    width: 28px;
    border-radius: 5px;
}

/* Responsive */
@media (max-width: 1024px) {
    .pkl-slide {
        flex: 0 0 50%;
    }
}
@media (max-width: 768px) {
    .pkl-title { font-size: 1.8rem; }
    .pkl-stats { grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
    .pkl-stat-card { padding: 1rem 0.5rem; }
    .pkl-stat-number { font-size: 1.3rem; }
    .pkl-stat-icon { width: 36px; height: 36px; font-size: 14px; }
    .pkl-slide {
        flex: 0 0 100%;
    }
    .pkl-carousel-btn { width: 40px; height: 40px; font-size: 14px; }
}
</style>

<section id="pkl-showcase" class="pkl-section" data-aos="fade-up">
    <div class="pkl-container">

        {{-- Header --}}
        <div class="pkl-header" data-aos="fade-up">
            <div class="pkl-label">
                <div class="pkl-label-dot"></div>
                <span class="pkl-label-text">Sorotan PKL</span>
            </div>
            <h2 class="pkl-title">Aktivitas Nyata di <span>Dunia Industri</span></h2>
            <p class="pkl-subtitle">
                Siswa-siswi SMK Perguruan PEMBDA Nias terjun langsung ke dunia kerja. Inilah momen terbaik dari logbook harian dan kunjungan monitoring pembimbing.
            </p>
        </div>

        {{-- Stats Bar --}}
        <div class="pkl-stats" data-aos="fade-up" data-aos-delay="100">
            <div class="pkl-stat-card stat-indigo">
                <div class="pkl-stat-icon" style="background:rgba(99,102,241,0.1); color:#818cf8; border: 1px solid rgba(99,102,241,0.2);">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div class="pkl-stat-number" data-count="{{ $totalApprovedLogs }}">0</div>
                <div class="pkl-stat-label">Logbook Disetujui</div>
            </div>
            <div class="pkl-stat-card stat-emerald">
                <div class="pkl-stat-icon" style="background:rgba(16,185,129,0.1); color:#34d399; border: 1px solid rgba(16,185,129,0.2);">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div class="pkl-stat-number" data-count="{{ $totalMonitorings }}">0</div>
                <div class="pkl-stat-label">Kunjungan Monitoring</div>
            </div>
            <div class="pkl-stat-card stat-amber">
                <div class="pkl-stat-icon" style="background:rgba(245,158,11,0.1); color:#fbbf24; border: 1px solid rgba(245,158,11,0.2);">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div class="pkl-stat-number" data-count="{{ $totalDudi }}">0</div>
                <div class="pkl-stat-label">Mitra DUDI</div>
            </div>
        </div>

        {{-- Carousel --}}
        <div class="pkl-carousel-wrapper" data-aos="fade-up" data-aos-delay="200">
            <div class="pkl-carousel-track" id="pklCarouselTrack">
                @foreach($pklShowcase as $index => $item)
                <div class="pkl-slide">
                    <div class="pkl-card">
                        {{-- Photo --}}
                        <div class="pkl-photo-container">
                            @if($item['photo'])
                                <img class="pkl-photo" src="{{ $item['photo'] }}" alt="Kegiatan PKL" loading="lazy"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;width:100%;height:100%;position:absolute;top:0;left:0;background:linear-gradient(135deg,#e0e7ff,#f0eeff);\' ><i class=\'fa-solid fa-image\' style=\'font-size:48px;color:#c7d2fe;\'></i></div>';">
                            @else
                                <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;position:absolute;top:0;left:0;background:linear-gradient(135deg,#e0e7ff,#f0eeff);">
                                    <i class="fa-solid fa-image" style="font-size:48px;color:#c7d2fe;"></i>
                                </div>
                            @endif
                            <div class="pkl-photo-overlay"></div>

                            {{-- Badge --}}
                            @if($item['type'] === 'logbook')
                                <span class="pkl-badge pkl-badge-logbook">
                                    <i class="fa-solid fa-pen-to-square"></i> Logbook Siswa
                                </span>
                            @else
                                <span class="pkl-badge pkl-badge-monitoring">
                                    <i class="fa-solid fa-user-tie"></i> Monitoring Guru
                                </span>
                            @endif

                            {{-- Date --}}
                            <span class="pkl-date-badge">
                                <i class="fa-regular fa-calendar"></i> {{ $item['date'] }}
                            </span>
                        </div>

                        {{-- Content --}}
                        <div class="pkl-card-body">
                            <p class="pkl-description">{{ $item['description'] ?: 'Kegiatan PKL di dunia industri.' }}</p>

                            <div class="pkl-person">
                                <div class="pkl-person-avatar">
                                    @if($item['person_photo'])
                                        <img src="{{ $item['person_photo'] }}" alt="{{ $item['person_name'] }}" onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<i class=\'fa-solid fa-user\'></i>';">
                                    @else
                                        <i class="fa-solid fa-user"></i>
                                    @endif
                                </div>
                                <div class="pkl-person-info">
                                    <p class="pkl-person-name">{{ $item['person_name'] }}</p>
                                    <p class="pkl-person-meta">
                                        <i class="fa-solid fa-school" style="margin-right:3px; color:var(--text-muted);"></i>
                                        {{ $item['school_name'] }}
                                    </p>
                                </div>
                                <div class="pkl-dudi-tag" title="{{ $item['dudi_name'] }}">
                                    <i class="fa-solid fa-building"></i>
                                    {{ \Illuminate\Support\Str::limit($item['dudi_name'], 18) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Carousel Controls --}}
        <div class="pkl-carousel-controls" data-aos="fade-up" data-aos-delay="300">
            <button class="pkl-carousel-btn" id="pklPrev" aria-label="Sebelumnya">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div class="pkl-dots" id="pklDots"></div>
            <button class="pkl-carousel-btn" id="pklNext" aria-label="Berikutnya">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('pklCarouselTrack');
    const prevBtn = document.getElementById('pklPrev');
    const nextBtn = document.getElementById('pklNext');
    const dotsContainer = document.getElementById('pklDots');

    if (!track) return;

    const slides = track.querySelectorAll('.pkl-slide');
    const totalSlides = slides.length;
    let currentIndex = 0;
    let autoPlayTimer;
    let slidesPerView = 3;

    function getSlidesPerView() {
        if (window.innerWidth <= 768) return 1;
        if (window.innerWidth <= 1024) return 2;
        if (window.innerWidth > 1440) return 4;
        return 3;
    }

    function getMaxIndex() {
        return Math.max(0, totalSlides - slidesPerView);
    }

    function updateCarousel() {
        slidesPerView = getSlidesPerView();
        const maxIndex = getMaxIndex();
        if (currentIndex > maxIndex) currentIndex = maxIndex;

        const slideWidth = 100 / slidesPerView;
        track.style.transform = `translateX(-${currentIndex * slideWidth}%)`;

        // Update dots
        updateDots();

        // Update buttons
        prevBtn.disabled = currentIndex <= 0;
        nextBtn.disabled = currentIndex >= maxIndex;
    }

    function createDots() {
        dotsContainer.innerHTML = '';
        slidesPerView = getSlidesPerView();
        const maxIndex = getMaxIndex();
        const numDots = maxIndex + 1;

        for (let i = 0; i < numDots; i++) {
            const dot = document.createElement('button');
            dot.className = 'pkl-dot' + (i === 0 ? ' active' : '');
            dot.setAttribute('aria-label', 'Slide ' + (i + 1));
            dot.addEventListener('click', function() {
                currentIndex = i;
                updateCarousel();
                resetAutoPlay();
            });
            dotsContainer.appendChild(dot);
        }
    }

    function updateDots() {
        const dots = dotsContainer.querySelectorAll('.pkl-dot');
        dots.forEach(function(dot, i) {
            dot.classList.toggle('active', i === currentIndex);
        });
    }

    function next() {
        const maxIndex = getMaxIndex();
        if (currentIndex < maxIndex) {
            currentIndex++;
        } else {
            currentIndex = 0; // Loop back
        }
        updateCarousel();
    }

    function prev() {
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = getMaxIndex(); // Loop to end
        }
        updateCarousel();
    }

    function startAutoPlay() {
        autoPlayTimer = setInterval(next, 5000);
    }

    function resetAutoPlay() {
        clearInterval(autoPlayTimer);
        startAutoPlay();
    }

    // Event Listeners
    prevBtn.addEventListener('click', function() { prev(); resetAutoPlay(); });
    nextBtn.addEventListener('click', function() { next(); resetAutoPlay(); });

    // Touch/Swipe support
    let touchStartX = 0;
    let touchEndX = 0;

    track.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    track.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) { next(); } else { prev(); }
            resetAutoPlay();
        }
    }, { passive: true });

    // Pause on hover
    track.addEventListener('mouseenter', function() { clearInterval(autoPlayTimer); });
    track.addEventListener('mouseleave', function() { startAutoPlay(); });

    // Resize handler
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            createDots();
            updateCarousel();
        }, 250);
    });

    // Init
    createDots();
    updateCarousel();
    startAutoPlay();
});
</script>
@else
{{-- Fallback: No PKL data yet --}}
<section id="pkl-showcase" class="section" style="background: transparent;" data-aos="fade-up">
    <div class="fw">
        <div style="text-align:center; margin-bottom:56px;">
            <div class="section-label" style="justify-content:center;">
                <div class="section-label-dot" style="background:var(--emerald, #10b981);"></div>
                <span class="section-label-text" style="color:var(--emerald, #10b981);">Sorotan PKL</span>
            </div>
            <h2 class="h1" style="margin-bottom:12px;">Aktivitas Nyata di <span style="background:linear-gradient(135deg,var(--indigo),var(--emerald)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">Dunia Industri</span></h2>
        </div>
        <div class="bcard text-center" style="max-width:700px; margin:0 auto; padding:48px;">
            <div class="icon-circle" style="background:rgba(16,185,129,0.1); color:#10b981; width:80px; height:80px; font-size:36px; margin:0 auto 24px;">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <h3 class="h2" style="margin-bottom:12px;">Kegiatan PKL Segera Hadir</h3>
            <p class="body-lg">Laporan kegiatan PKL siswa dan monitoring pembimbing akan segera ditampilkan di sini. Nantikan update selanjutnya!</p>
        </div>
    </div>
</section>
@endif
