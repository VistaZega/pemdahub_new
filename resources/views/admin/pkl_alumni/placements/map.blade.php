@extends('layouts.admin')
@section('title', 'Peta Sebaran PKL - Portal Admin')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map {
        height: 70vh;
        width: 100%;
        border-radius: 1rem;
        z-index: 10;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 1rem;
        padding: 0;
        overflow: hidden;
    }
    .leaflet-popup-content {
        margin: 0;
        width: 200px !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pkl-alumni.placements.index') }}" class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-100 hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-map-marked-alt text-emerald-500"></i> Peta Sebaran Siswa PKL
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Sebaran lokasi siswa magang berdasarkan titik kordinat GPS logbook terakhir.
                </p>
            </div>
        </div>
    </div>

    {{-- Map Container --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2">
        @if(count($mapData) > 0)
            <div id="map"></div>
        @else
            <div class="text-center py-20 text-gray-500">
                <i class="fas fa-map-signs text-4xl text-gray-300 mb-4"></i>
                <p class="text-sm font-bold">Belum Ada Data Lokasi</p>
                <p class="text-xs mt-1">Siswa belum mengirimkan logbook dengan kordinat GPS yang valid.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapData = @json($mapData);

        if (mapData.length > 0) {
            // Inisialisasi Peta (Center awal bisa diset dinamis atau statis)
            // Default center Nias (1.2642, 97.6698)
            const map = L.map('map').setView([mapData[0].lat, mapData[0].lng], 13);

            // Tambahkan TileLayer OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const bounds = [];

            // Looping data mapData untuk membuat marker
            mapData.forEach(student => {
                if (student.lat && student.lng) {
                    const marker = L.marker([student.lat, student.lng]).addTo(map);
                    
                    const popupContent = `
                        <div class="bg-white">
                            <div class="h-16 w-full bg-indigo-600"></div>
                            <div class="px-4 pb-4 relative text-center">
                                <img src="${student.photo}" class="w-12 h-12 rounded-full border-2 border-white mx-auto -mt-6 bg-white object-cover shadow-sm">
                                <h3 class="text-xs font-bold text-gray-800 mt-2 leading-tight">${student.student_name}</h3>
                                <p class="text-[10px] text-gray-500 mt-0.5"><i class="fas fa-building mr-1"></i>${student.company_name}</p>
                                <div class="mt-2 bg-gray-50 text-[9px] text-gray-400 p-1.5 rounded border border-gray-100">
                                    Pembaruan terakhir: ${student.log_date}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    marker.bindPopup(popupContent);
                    bounds.push([student.lat, student.lng]);
                }
            });

            // Sesuaikan zoom level agar semua marker terlihat
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    });
</script>
@endpush
