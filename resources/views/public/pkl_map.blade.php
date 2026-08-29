<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Peta Sebaran Lokasi Siswa PKL — SMKS Swasta Pembda Nias | PembdaHUB</title>
    
    <link rel="icon" href="{{ asset('images/logo-pembda.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,700&family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #faf8f5;
            color: #121316;
        }
        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }
        #map {
            height: calc(100vh - 220px);
            min-height: 520px;
            width: 100%;
            border-radius: 1.5rem;
            z-index: 10;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 1.25rem;
            padding: 0;
            overflow: hidden;
            border: 2px solid #121316;
            box-shadow: 4px 4px 0px #121316;
        }
        .leaflet-popup-content {
            margin: 0 !important;
            width: 260px !important;
            line-height: 1.4;
        }
        .btn-tactile-red {
            background-color: #ff3823;
            color: #ffffff;
            border: 2px solid #121316;
            box-shadow: 3px 3px 0px #121316;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-tactile-red:hover {
            transform: translate(-1px, -1px);
            box-shadow: 4px 4px 0px #121316;
        }
        .btn-tactile-white {
            background-color: #ffffff;
            color: #121316;
            border: 2px solid #121316;
            box-shadow: 3px 3px 0px #121316;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-tactile-white:hover {
            transform: translate(-1px, -1px);
            box-shadow: 4px 4px 0px #121316;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between antialiased selection:bg-[#fde047] selection:text-[#121316]">

    <!-- Header Navigation -->
    <header class="bg-white border-b-2 border-[#121316] sticky top-0 z-40 px-4 sm:px-8 py-3.5 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('images/logo-yayasan.png') }}" alt="Logo Yayasan" class="w-9 h-9 object-contain rounded-lg border border-[#e7e3d8]">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Pembda" class="w-9 h-9 object-contain rounded-lg border border-[#e7e3d8]">
                    <div class="flex flex-col">
                        <span class="text-base font-black tracking-tight text-[#121316]">Pembda<span class="text-[#ff3823]">HUB</span></span>
                        <span class="text-[9px] font-mono-code font-bold uppercase text-[#777]">SMKS SWASTA PEMBDA NIAS</span>
                    </div>
                </a>
            </div>

            <!-- Title Tag in Middle (Desktop) -->
            <div class="hidden md:flex items-center gap-2 px-3 py-1 rounded-full bg-[#f4f1ea] border border-[#121316] text-[11px] font-mono-code font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>PETA GPS SEBARAN PRAKTIK KERJA LAPANGAN (PKL)</span>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="px-4 py-2 rounded-full btn-tactile-white text-xs font-black flex items-center gap-1.5">
                    <span>&larr;</span>
                    <span>Kembali ke Beranda</span>
                </a>
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-full btn-tactile-red text-xs font-black hidden sm:inline-flex items-center gap-1.5">
                    <span>Masuk Portal</span>
                    <span>&rarr;</span>
                </a>
            </div>

        </div>
    </header>

    <!-- Main Map Interface -->
    <main class="max-w-7xl mx-auto px-4 sm:px-8 py-6 flex-1 w-full">
        
        <!-- Top Info Strip -->
        <div class="bg-white border-2 border-[#121316] rounded-2xl p-5 mb-6 shadow-[4px_4px_0px_#121316] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded bg-[#ff3823] text-white text-[10px] font-mono-code font-bold uppercase">
                        KEMITRAAN INDUSTRI DUDI
                    </span>
                    <span class="text-xs font-mono-code text-[#777]">
                        TP {{ isset($activeAcademicYear) && $activeAcademicYear ? $activeAcademicYear->name : '2026/2027' }}
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-[#121316] tracking-tight">
                    Peta Persebaran Lokasi Siswa Magang / PKL
                </h1>
                <p class="text-xs text-[#555] font-medium mt-0.5">
                    Titik koordinat GPS realtime berdasarkan logbook presensi kehadiran siswa di mitra Dunia Usaha & Dunia Industri.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div class="px-4 py-2 bg-[#faf8f5] border border-[#121316] rounded-xl text-center">
                    <div class="text-lg font-black text-[#121316] font-mono-code">{{ count($mapData) }}</div>
                    <div class="text-[9px] font-mono-code font-bold uppercase text-[#777]">Titik Siswa Terpetakan</div>
                </div>
                <div class="px-4 py-2 bg-[#faf8f5] border border-[#121316] rounded-xl text-center">
                    <div class="text-lg font-black text-[#2563eb] font-mono-code">{{ $totalDudi ?? '45+' }}</div>
                    <div class="text-[9px] font-mono-code font-bold uppercase text-[#777]">Mitra Industri DUDI</div>
                </div>
            </div>
        </div>

        <!-- Map Container Card with Corner Framing -->
        <div class="relative bg-white border-2 border-[#121316] rounded-3xl p-3 shadow-[6px_6px_0px_#121316] overflow-hidden">
            
            @if(count($mapData) > 0)
                <div id="map"></div>
            @else
                <div class="text-center py-28 px-4 bg-[#faf8f5] rounded-2xl border border-dashed border-[#121316]">
                    <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-4 text-2xl border border-[#121316]">
                        📍
                    </div>
                    <h3 class="text-lg font-black text-[#121316] mb-1">Koordinat GPS Sedang Disinkronkan</h3>
                    <p class="text-xs text-[#555] font-medium max-w-md mx-auto">
                        Siswa magang SMKS Swasta Pembda Nias sedang aktif mengisi logbook kehadiran pada mitra industri masing-masing.
                    </p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block px-5 py-2 rounded-full btn-tactile-red text-xs font-black">
                        Kembali ke Halaman Utama
                    </a>
                </div>
            @endif

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-[#121316] text-white py-6 border-t-2 border-[#121316] px-4 sm:px-8 mt-12">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-mono-code text-slate-400">
            <div>
                &copy; {{ date('Y') }} Yayasan Perguruan PEMBDA Nias &bull; SMKS Swasta Pembda Nias
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Beranda</a>
                <span>&bull;</span>
                <a href="{{ route('login') }}" class="hover:text-white transition-colors">Portal Masuk</a>
            </div>
        </div>
    </footer>

    <!-- Leaflet Script & Map Initialization -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapData = @json($mapData);

            if (mapData && mapData.length > 0) {
                // Default Center: Pulau Nias / Gunungsitoli (1.2894, 97.6167)
                const firstLat = mapData[0].lat || 1.2894;
                const firstLng = mapData[0].lng || 97.6167;

                const map = L.map('map').setView([firstLat, firstLng], 13);

                // OpenStreetMap Tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | PembdaHUB'
                }).addTo(map);

                const bounds = [];

                // Custom Red Marker Icon
                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: #ff3823; width: 28px; height: 28px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 3px 8px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">📍</div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                    popupAnchor: [0, -14]
                });

                mapData.forEach(item => {
                    if (item.lat && item.lng) {
                        const marker = L.marker([item.lat, item.lng], { icon: customIcon }).addTo(map);
                        
                        const googleMapsRoute = `https://www.google.com/maps/search/?api=1&query=${item.lat},${item.lng}`;
                        const photoImg = item.photo 
                            ? `<img src="${item.photo}" class="w-12 h-12 rounded-full border-2 border-white mx-auto -mt-6 bg-white object-cover shadow-sm">`
                            : `<div class="w-12 h-12 rounded-full border-2 border-white mx-auto -mt-6 bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">🎓</div>`;

                        const popupHtml = `
                            <div class="bg-white font-sans overflow-hidden">
                                <div class="h-12 w-full bg-[#121316] flex items-center justify-center text-[10px] font-mono font-bold text-[#fde047] uppercase tracking-wider">
                                    SMKS PEMBDA NIAS
                                </div>
                                <div class="px-4 pb-4 pt-1 text-center">
                                    ${photoImg}
                                    <h4 class="text-xs font-black text-[#121316] mt-2 leading-tight">${item.student_name}</h4>
                                    <p class="text-[11px] font-bold text-[#2563eb] mt-0.5">🏢 ${item.company_name}</p>
                                    ${item.activity ? `<p class="text-[10px] text-[#555] mt-1.5 italic bg-[#faf8f5] p-1.5 rounded border border-[#e7e3d8]">"${item.activity}"</p>` : ''}
                                    <div class="mt-2.5 flex items-center justify-between gap-2 pt-2 border-t border-slate-100 text-[9px] font-mono">
                                        <span class="text-[#777]">📅 ${item.log_date}</span>
                                        <a href="${googleMapsRoute}" target="_blank" rel="noopener noreferrer" class="text-[#ff3823] font-bold hover:underline">
                                            Google Maps &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupHtml);
                        bounds.push([item.lat, item.lng]);
                    }
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                }
            }
        });
    </script>

</body>
</html>
