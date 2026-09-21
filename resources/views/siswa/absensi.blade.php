@extends('layouts.siswa')
@section('title', 'Absensi - Portal Siswa')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-clipboard-check text-purple-500"></i> Rekap Kehadiran
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Riwayat kehadiran kamu
                @if($activeYear) &middot; {{ $activeYear->year }} @endif
            </p>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-4 rounded-xl shadow-lg text-white flex items-center gap-4">
            <div class="flex-none">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-map-marker-alt text-2xl"></i>
                </div>
            </div>
            <div class="flex-grow">
                <h3 class="font-bold text-lg">Absensi Mandiri (GPS)</h3>
                <div class="flex flex-wrap gap-2 mt-1">
                    <span class="bg-white/20 px-2 py-0.5 rounded text-xs font-bold"><i class="fas fa-clock mr-1"></i> Jam Masuk: {{ $classroom->entry_time ?? '07:30' }}</span>
                    <span class="bg-white/20 px-2 py-0.5 rounded text-xs font-bold"><i class="fas fa-hourglass-half mr-1"></i> Toleransi: {{ $classroom->late_tolerance ?? 15 }} Menit</span>
                </div>
            </div>
            <div>
                <button id="btnAbsenNow" class="bg-white text-indigo-600 font-bold py-2 px-4 rounded-lg shadow hover:bg-indigo-50 transition transform hover:scale-105 flex items-center gap-2">
                    <i class="fas fa-fingerprint"></i> Absen Sekarang
                </button>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
            $items = [
                ['label' => 'Hadir', 'value' => $summary['present'], 'color' => 'green', 'icon' => 'check-circle'],
                ['label' => 'Sakit', 'value' => $summary['sick'], 'color' => 'yellow', 'icon' => 'briefcase-medical'],
                ['label' => 'Izin', 'value' => $summary['permission'], 'color' => 'blue', 'icon' => 'envelope'],
                ['label' => 'Absen', 'value' => $summary['absent'], 'color' => 'red', 'icon' => 'times-circle'],
                ['label' => 'Terlambat', 'value' => $summary['late'], 'color' => 'orange', 'icon' => 'clock'],
                ['label' => 'Persentase', 'value' => $summary['percentage'].'%', 'color' => 'purple', 'icon' => 'percentage'],
            ];
        @endphp
        @foreach($items as $item)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <div class="w-8 h-8 bg-{{ $item['color'] }}-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-{{ $item['icon'] }} text-{{ $item['color'] }}-600 text-sm"></i>
                </div>
                <p class="text-xl font-bold text-gray-800">{{ $item['value'] }}</p>
                <p class="text-xs text-gray-500">{{ $item['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Attendance Bar --}}
    @if($summary['total'] > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h2 class="font-bold text-gray-800 mb-3 flex items-center gap-2"><i class="fas fa-chart-pie text-purple-500"></i> Distribusi Kehadiran</h2>
        <div class="h-6 flex rounded-full overflow-hidden bg-gray-100">
            @if($summary['present'] > 0)
                <div class="bg-green-500 flex items-center justify-center text-white text-xs font-bold" style="width:{{ ($summary['present']/$summary['total'])*100 }}%">{{ $summary['present'] }}</div>
            @endif
            @if($summary['late'] > 0)
                <div class="bg-orange-400 flex items-center justify-center text-white text-xs font-bold" style="width:{{ ($summary['late']/$summary['total'])*100 }}%">{{ $summary['late'] }}</div>
            @endif
            @if($summary['sick'] > 0)
                <div class="bg-yellow-400 flex items-center justify-center text-white text-xs font-bold" style="width:{{ ($summary['sick']/$summary['total'])*100 }}%">{{ $summary['sick'] }}</div>
            @endif
            @if($summary['permission'] > 0)
                <div class="bg-blue-400 flex items-center justify-center text-white text-xs font-bold" style="width:{{ ($summary['permission']/$summary['total'])*100 }}%">{{ $summary['permission'] }}</div>
            @endif
            @if($summary['absent'] > 0)
                <div class="bg-red-500 flex items-center justify-center text-white text-xs font-bold" style="width:{{ ($summary['absent']/$summary['total'])*100 }}%">{{ $summary['absent'] }}</div>
            @endif
        </div>
        <div class="flex flex-wrap gap-4 mt-3 text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-500 rounded-full"></span> Hadir</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-orange-400 rounded-full"></span> Terlambat</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-yellow-400 rounded-full"></span> Sakit</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-blue-400 rounded-full"></span> Izin</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-500 rounded-full"></span> Absen</span>
        </div>
    </div>
    @endif

    {{-- Heatmap / Presensi Matrix --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-th text-indigo-500"></i> Heatmap Presensi</h2>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Visualisasi kehadiran Tahun Pelajaran berjalan</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold">
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-green-500 rounded-full"></span> HADIR</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-orange-500 rounded-full"></span> TERLAMBAT</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-blue-400 rounded-full"></span> IZIN</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-yellow-400 rounded-full"></span> SAKIT</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-red-500 rounded-full"></span> ALPHA</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-purple-500 rounded-full"></span> LIBUR</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-rose-200 rounded-full"></span> PEKAN</span>
            </div>
        </div>
        <div class="p-5">
            <div class="space-y-6">
                @php
                    $currentDate = now();
                @endphp

                @for($m = 0; $m < $monthsToShow; $m++)
                    @php
                        $iterMonth = $currentDate->copy()->subMonths($m);
                        $daysInMonth = $iterMonth->daysInMonth;
                        $monthName = $iterMonth->translatedFormat('F Y');
                    @endphp
                    <div class="space-y-2">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider pl-1">{{ $monthName }}</h4>
                        <div class="flex flex-wrap gap-1.5">
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $dateStr = $iterMonth->format('Y-m-').str_pad($d, 2, '0', STR_PAD_LEFT);
                                    $att = $attendances->get($dateStr);
                                    $carbonDate = \Carbon\Carbon::parse($dateStr);
                                    $isWeekend = $carbonDate->isWeekend();
                                    
                                    $colorClass = 'bg-gray-100 text-gray-300'; // Default empty
                                    $statusLabel = $att->status ?? ($isWeekend ? 'Akhir Pekan' : 'Tanpa Keterangan');
                                    
                                    if ($att) {
                                        $colorClass = match($att->status) {
                                            'hadir' => 'bg-green-500 text-white shadow-[0_0_8px_rgba(34,197,94,0.4)]',
                                            'terlambat' => 'bg-orange-500 text-white shadow-[0_0_8px_rgba(249,115,22,0.4)]',
                                            'izin' => 'bg-blue-400 text-white',
                                            'sakit' => 'bg-yellow-400 text-white',
                                            'alpha' => 'bg-red-500 text-white',
                                            'libur' => 'bg-purple-500 text-white shadow-[0_0_8px_rgba(168,85,247,0.4)]',
                                            default => 'bg-gray-200 text-gray-400'
                                        };
                                        if($att->status == 'libur') $statusLabel = 'Hari Libur / Tidak Aktif';
                                    } elseif ($isWeekend) {
                                        $colorClass = 'bg-rose-50/70 text-rose-300 border border-rose-100/50';
                                        $statusLabel = 'Akhir Pekan (Libur)';
                                    }
                                @endphp
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all cursor-default relative group {{ $colorClass }}" title="{{ $carbonDate->translatedFormat('d F Y') }} - {{ $statusLabel }}">
                                    {{ $d }}
                                    
                                    {{-- Tooltip info on hover --}}
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-32 hidden group-hover:block z-50">
                                        <div class="bg-slate-800 text-white text-xs p-2 rounded-lg shadow-xl text-center">
                                            <p class="font-bold mb-0.5 uppercase tracking-tighter">{{ $statusLabel }}</p>
                                            <p class="text-xs opacity-70 mb-1 border-b border-white/10 pb-1">{{ $carbonDate->translatedFormat('l, d M Y') }}</p>
                                            @if($att && $att->time_in) 
                                                <p class="opacity-70">{{ date('H:i', strtotime($att->time_in)) }} - {{ $att->time_out ? date('H:i', strtotime($att->time_out)) : '?' }}</p> 
                                            @endif
                                        </div>
                                        <div class="w-2 h-2 bg-slate-800 rotate-45 mx-auto -mt-1"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @endfor
            </div>
        </div>
        <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center gap-2 text-xs text-gray-500 italic">
                <i class="fas fa-info-circle opacity-50"></i>
                <span>Ketuk pada kotak tanggal untuk melihat detail absensi. Akhir pekan (Sabtu-Minggu) ditandai dengan kotak pudar.</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnAbsen = document.getElementById('btnAbsenNow');

        // ==== Generate or Get Device Fingerprint ====
        function getDeviceId() {
            let deviceId = localStorage.getItem('pembdahub_device_id');
            if (!deviceId) {
                // Generate a random UUID-like string
                deviceId = 'dev_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                localStorage.setItem('pembdahub_device_id', deviceId);
            }
            return deviceId;
        }

        btnAbsen.addEventListener('click', function () {
            // Check if Geolocation is supported
            if (!navigator.geolocation) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Browser Anda tidak mendukung fitur lokasi (GPS). Gunakan browser lain yang lebih modern.',
                });
                return;
            }

            // Disable button during process
            btnAbsen.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendapatkan Lokasi...';
            btnAbsen.disabled = true;

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const deviceId = getDeviceId();

                    // Send data to API
                    fetch('{{ route('siswa.attendance.gps-scan') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: lat,
                            longitude: lng,
                            device_id: deviceId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        btnAbsen.innerHTML = '<i class="fas fa-fingerprint"></i> Absen Sekarang';
                        btnAbsen.disabled = false;

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Absensi Gagal',
                                text: data.message,
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btnAbsen.innerHTML = '<i class="fas fa-fingerprint"></i> Absen Sekarang';
                        btnAbsen.disabled = false;

                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: 'Gagal menghubungkan ke server. Harap periksa koneksi internet Anda.',
                        });
                    });
                },
                function (error) {
                    btnAbsen.innerHTML = '<i class="fas fa-fingerprint"></i> Absen Sekarang';
                    btnAbsen.disabled = false;

                    let errorMsg = 'Gagal mendapatkan lokasi GPS.';
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMsg = 'Anda harus MENGIZINKAN akses lokasi (GPS) di HP/Browser Anda untuk bisa absen.';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMsg = 'Sinyal GPS tidak tersedia, coba keluar ruangan untuk mencari sinyal GPS.';
                    } else if (error.code === error.TIMEOUT) {
                        errorMsg = 'Waktu pengambilan GPS habis (timeout). Harap coba lagi di area terbuka.';
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Akses Lokasi Ditolak/Gagal',
                        text: errorMsg,
                    });
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        });
    });
</script>
@endpush
