<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PembdaHUB — Smart School & Ekosistem Terpadu Yayasan Perguruan PEMBDA Nias</title>
    <meta name="description" content="Portal Smart School resmi Yayasan Perguruan PEMBDA Nias. Menghubungkan SMAS Pembda 1, SMPS Pembda 2, dan SMKS Pembda Nias dalam satu ekosistem digital terpadu (LMS, CBT, Presensi RFID, Logbook PKL, dan Rapor Merdeka).">
    <link rel="icon" href="{{ asset('images/logo-pembda.png') }}" type="image/png">

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            font-family: 'Plus Jakarta Sans', sans-serif;
            --bg-warm: #faf8f5;
            --text-dark: #121316;
            --brand-red: #ff3823;
            --brand-yellow: #fbc02d;
            --brand-blue: #2563eb;
            --border-ink: #121316;
        }

        body {
            background-color: var(--bg-warm);
            color: var(--text-dark);
            letter-spacing: -0.02em;
            overflow-x: hidden;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Yellow Highlighter Stroke Effect (Khas Anthropic Frontend Style) */
        .highlight-marker {
            background: linear-gradient(180deg, transparent 62%, #fde047 62%, #fde047 92%, transparent 92%);
            display: inline;
        }

        /* Tactile Solid Drop-Shadow Buttons */
        .btn-tactile-red {
            background: #ff3823;
            color: #ffffff;
            border: 1.5px solid #121316;
            box-shadow: 3px 3px 0px #121316;
            transition: all 0.15s ease;
        }
        .btn-tactile-red:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316;
        }

        .btn-tactile-white {
            background: #ffffff;
            color: #121316;
            border: 1.5px solid #121316;
            box-shadow: 3px 3px 0px #121316;
            transition: all 0.15s ease;
        }
        .btn-tactile-white:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316;
        }

        /* Poster Cards */
        .poster-card {
            border: 1.5px solid #121316;
            border-radius: 20px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 3px 3px 0px rgba(18, 19, 22, 0.1);
        }
        .poster-card:hover {
            transform: translateY(-4px);
            box-shadow: 6px 8px 0px #121316;
        }

        /* Dotted Graph Paper Background */
        .graph-paper-box {
            background-color: #f6f4ee;
            background-image: radial-gradient(#d1cebe 1px, transparent 1px);
            background-size: 14px 14px;
        }

        /* Photo Frame Polaroid Style */
        .photo-frame {
            background: #ffffff;
            border: 2px solid #121316;
            border-radius: 16px;
            padding: 10px;
            box-shadow: 4px 4px 0px #121316;
            transition: all 0.2s ease;
        }
        .photo-frame:hover {
            transform: translateY(-3px) rotate(0.5deg);
            box-shadow: 6px 6px 0px #121316;
        }

        /* Filter Tab Active / Inactive */
        .filter-btn-active {
            background: #121316;
            color: #ffffff;
            border: 1.5px solid #121316;
        }
        .filter-btn-inactive {
            background: #ffffff;
            color: #555555;
            border: 1.5px solid #e2ded5;
        }
        .filter-btn-inactive:hover {
            border-color: #121316;
            color: #121316;
        }

        /* Floating Mars Audio Player */
        .floating-audio-dock {
            position: fixed;
            bottom: 24px;
            left: 24px;
            z-index: 9999;
        }
        .audio-tactile-btn {
            background: #121316;
            color: #ffffff;
            border: 1.5px solid #ffffff;
            box-shadow: 3px 3px 0px #ff3823;
            border-radius: 9999px;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .audio-tactile-btn:hover {
            transform: translate(1px, 1px);
            box-shadow: 2px 2px 0px #ff3823;
        }
    </style>
</head>
<body data-theme="{{ $homepageTheme ?? 'regular' }}" class="antialiased selection:bg-[#fde047] selection:text-black">

    @if(isset($homepageTheme) && $homepageTheme !== 'regular')
        @include('landing.partials.event-theme-banner')
    @endif

    {{-- 1. NAVBAR --}}
    @include('landing.partials.navigation')

    {{-- 2. HERO & LIVE INTERACTIVE MOCKUP TERMINAL --}}
    @include('landing.partials.hero')

    {{-- 3. ALUR 4 LANGKAH KBM --}}
    @include('landing.partials.platform-overview')

    {{-- 4. ETALASE POSTER WARNA-WARNI (Project, PKL, Modul LMS, Prestasi) --}}
    @include('landing.partials.showcase')

    {{-- 5. EKSTRAKURIKULER & MINAT BAKAT SISWA --}}
    @include('landing.partials.kegiatan-siswa')

    {{-- 6. GALERI FOTO MOMEN POLAROID BENTO --}}
    @include('landing.partials.galeri')

    {{-- 7. PEMBDA SPACE & FORUM STEAM --}}
    @include('landing.partials.pembda-space')

    {{-- 8. HALL OF FAME CIVITAS TERPILIH --}}
    @include('landing.partials.hall-of-fame')

    {{-- 9. JEJARING ALUMNI PEMBDA --}}
    @include('landing.partials.alumni-showcase')

    {{-- 10. FITUR PINTAR GRAPH PAPER --}}
    @include('landing.partials.pembdahub-features')

    {{-- 11. 3 UNIT SEKOLAH MANDIRI --}}
    @include('landing.partials.unit-sekolah')

    {{-- 12. BERITA & WARTA TERKINI --}}
    @include('landing.partials.berita')

    {{-- 13. INDUSTRI BESAR MITRA KERJASAMA SMK --}}
    @include('landing.partials.mitra-industri')

    {{-- 14. FOOTER --}}
    @include('landing.partials.footer')

    {{-- FLOATING AUDIO PLAYER (MARS YAYASAN) --}}
    <div id="mars-player" class="floating-audio-dock">
        <audio id="mars-audio" loop preload="none">
            <source src="/audio/mars-pembda.mp4" type="audio/mp4">
            Your browser does not support the audio element.
        </audio>
        <button id="mars-toggle" class="audio-tactile-btn" title="Putar Mars Yayasan PEMBDA">
            <i class="fa-solid fa-music text-[#fbc02d]"></i>
            <span id="mars-text">Mars Pembda</span>
        </button>
    </div>

    <script>
        // Mars Audio Toggle Handler
        document.addEventListener('DOMContentLoaded', function() {
            const marsAudio = document.getElementById('mars-audio');
            const marsToggle = document.getElementById('mars-toggle');
            const marsText = document.getElementById('mars-text');

            if (marsToggle && marsAudio) {
                let isPlaying = false;
                marsToggle.addEventListener('click', function() {
                    if (isPlaying) {
                        marsAudio.pause();
                        marsText.textContent = 'Mars Pembda';
                        marsToggle.classList.remove('bg-[#ff3823]');
                        marsToggle.classList.add('bg-[#121316]');
                        isPlaying = false;
                    } else {
                        marsAudio.play().then(() => {
                            marsText.textContent = 'Memutar Mars...';
                            marsToggle.classList.remove('bg-[#121316]');
                            marsToggle.classList.add('bg-[#ff3823]');
                            isPlaying = true;
                        }).catch(e => {
                            console.log('Audio autoplay prevented:', e);
                        });
                    }
                });
            }
        });
    </script>

</body>
</html>