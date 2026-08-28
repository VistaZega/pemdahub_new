@extends('layouts.siswa')
@section('title', 'Praktik Kerja Lapangan (PKL) - Portal Siswa')

@section('content')
<div class="space-y-6">
    {{-- Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4">
        <div>
            <h1 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-briefcase text-amber-500"></i> Monitoring Praktik Kerja Lapangan (PKL)
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                SMKS Swasta Pembda Nias — Manajemen Logbook & Evaluasi Industri
            </p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 px-3 py-1.5 rounded-lg text-xs font-semibold">
                <i class="far fa-calendar text-xs"></i> TA {{ date('Y') }}
            </span>
        </div>
    </div>

    @if(!$placement)
        {{-- No Active Placement state --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center max-w-xl mx-auto my-10">
            <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i class="fas fa-briefcase text-4xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">Penempatan PKL Belum Aktif</h3>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                Anda belum terdaftar dalam penempatan Praktik Kerja Lapangan (PKL) yang aktif di sistem. Silakan berkoordinasi dengan Panitia PKL atau Admin Sekolah untuk pengaturan penempatan industri Anda.
            </p>
            <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center gap-2 bg-amber-500 text-white font-bold px-5 py-2.5 rounded-xl shadow-md hover:bg-amber-600 transition text-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    @else
        {{-- Active Placement view --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left column: Placement & Evaluation --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Placement Info --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-sm font-bold text-gray-850 border-b border-gray-100 pb-3 mb-4 flex items-center gap-2">
                        <i class="fas fa-building text-amber-500"></i> Informasi Penempatan
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Instansi / Perusahaan DUDI</span>
                            <p class="text-sm font-bold text-gray-850 leading-snug">{{ $placement->company_name }}</p>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed"><i class="fas fa-map-marker-alt text-rose-500 mr-1"></i>{{ $placement->company_address }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 border-t border-gray-50 pt-3">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Pembimbing Lapangan</span>
                                <p class="text-xs font-bold text-gray-800">{{ $placement->mentor_name }}</p>
                                @if($placement->mentor_phone)
                                    <p class="text-xs text-gray-500 mt-0.5"><i class="fab fa-whatsapp text-emerald-500 mr-1"></i>{{ $placement->mentor_phone }}</p>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Guru Pembimbing</span>
                                <p class="text-xs font-bold text-gray-800">{{ $placement->teacher->user->name ?? 'Belum ditentukan' }}</p>
                                @if($placement->teacher && $placement->teacher->user->phone)
                                    <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-phone text-blue-500 mr-1"></i>{{ $placement->teacher->user->phone }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="border-t border-gray-50 pt-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Durasi Magang</span>
                            <p class="text-xs font-semibold text-gray-700 flex items-center gap-1.5 mt-0.5">
                                <i class="far fa-calendar-alt text-gray-400"></i>
                                {{ \Carbon\Carbon::parse($placement->start_date)->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($placement->end_date)->translatedFormat('d M Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Grades Card (If graded) --}}
                @if($placement->grade)
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl shadow-lg border border-slate-700 p-5 relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/10 rounded-full"></div>
                        <h3 class="text-sm font-bold border-b border-white/10 pb-3 mb-4 flex items-center justify-between">
                            <span class="flex items-center gap-2"><i class="fas fa-star text-amber-400"></i> Nilai Akhir DUDI</span>
                            <span class="bg-emerald-500/20 text-emerald-400 text-xs font-bold px-2 py-0.5 rounded-full border border-emerald-500/30">SELESAI</span>
                        </h3>
                        
                        <div class="flex items-center justify-between mb-5">
                            <div class="text-4xl font-extrabold text-white">{{ number_format($placement->grade->score_average, 1) }}</div>
                            <div class="text-right">
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Diserahkan tanggal</p>
                                <p class="text-xs font-semibold text-slate-200">{{ \Carbon\Carbon::parse($placement->grade->submitted_at)->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between p-2 rounded bg-white/5">
                                <span class="text-slate-300">Kedisiplinan</span>
                                <span class="font-bold text-emerald-400">{{ $placement->grade->score_discipline }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2 rounded bg-white/5">
                                <span class="text-slate-300">Kerjasama Tim</span>
                                <span class="font-bold text-emerald-400">{{ $placement->grade->score_teamwork }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2 rounded bg-white/5">
                                <span class="text-slate-300">Kemampuan Teknis</span>
                                <span class="font-bold text-emerald-400">{{ $placement->grade->score_technical }}</span>
                            </div>
                            <div class="flex items-center justify-between p-2 rounded bg-white/5">
                                <span class="text-slate-300">Keselamatan Kerja</span>
                                <span class="font-bold text-emerald-400">{{ $placement->grade->score_safety }}</span>
                            </div>
                        </div>

                        @if($placement->grade->notes)
                            <div class="mt-4 p-3 bg-white/5 rounded-xl border border-white/5 text-xs">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Catatan Evaluasi Mentor:</p>
                                <p class="text-slate-200 italic">"{{ $placement->grade->notes }}"</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Right Column: Form + History --}}
            <div class="lg:col-span-2 space-y-6">
                @php
                    $rejectedLogs = $placement->logs->where('status', 'rejected');
                @endphp

                {{-- Alert Banner jika ada logbook yang ditolak / perlu revisi --}}
                @if($rejectedLogs->count() > 0)
                    <div class="bg-gradient-to-r from-rose-500 to-red-600 text-white rounded-2xl p-5 shadow-lg flex items-start gap-4 animate-pulse">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-sm">Ada {{ $rejectedLogs->count() }} Logbook PKL yang Perlu Anda Revisi!</h4>
                            <p class="text-xs text-rose-100 leading-relaxed">
                                Pembimbing telah memberikan catatan revisi pada logbook Anda. Silakan klik tombol <strong>"Revisi Logbook Ini"</strong> di daftar riwayat di bawah untuk memperbaiki deskripsi atau mengganti foto.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Form Logbook --}}
                @if(!$placement->grade)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5" id="form-logbook-card">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                            <h3 class="text-sm font-bold text-gray-850 flex items-center gap-2" id="form-title">
                                <i class="fas fa-edit text-amber-500"></i> Isi Logbook Harian PKL
                            </h3>
                            <span id="revision-badge" class="hidden text-xs bg-rose-100 text-rose-700 font-bold px-2.5 py-0.5 rounded-lg border border-rose-200">
                                Mode Revisi Logbook
                            </span>
                        </div>
                        
                        @if(session('success'))
                            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-xs font-semibold mb-4">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs font-semibold mb-4">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div id="revision-notice" class="hidden mb-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-info-circle text-rose-600"></i>
                                <span>Sedang memperbaiki logbook tanggal: <strong id="revision-date-text"></strong></span>
                            </div>
                            <button type="button" onclick="cancelRevision()" class="text-[11px] font-bold text-rose-600 underline hover:text-rose-800">
                                Batal Revisi
                            </button>
                        </div>

                        <form action="{{ route('siswa.pkl.log.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="logbook-form">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Tanggal Kegiatan</label>
                                    <input type="date" name="log_date" id="input_log_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Foto Bukti Kegiatan (Maks 5MB)</label>
                                    <input type="file" name="photo" id="input_photo" accept="image/*" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition">
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">1. Kegiatan utama yang saya kerjakan hari ini adalah:</label>
                                    <textarea name="activity_1" id="input_activity_1" rows="2" minlength="15" placeholder="Contoh: Merakit PC, menginput data pasien, dll..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition" required></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">2. Alat, bahan, atau aplikasi yang saya gunakan:</label>
                                    <textarea name="activity_2" id="input_activity_2" rows="2" minlength="10" placeholder="Contoh: Obeng, Microsoft Excel, Tensimeter, dll..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition" required></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">3. Pengetahuan atau keterampilan baru yang saya pelajari:</label>
                                    <textarea name="activity_3" id="input_activity_3" rows="2" minlength="15" placeholder="Contoh: Saya belajar cara melakukan instalasi Windows 11..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition" required></textarea>
                                </div>
                            </div>

                            {{-- GPS Geolocation info --}}
                            <div class="bg-amber-50/50 border border-amber-100 rounded-xl px-4 py-3">
                                <div class="flex items-center gap-2 text-xs font-medium text-amber-800" id="gps-status">
                                    <i class="fas fa-spinner animate-spin text-amber-500"></i> Mengambil koordinat GPS Anda...
                                </div>
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" id="btn-cancel-revision" onclick="cancelRevision()" class="hidden px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-100 text-sm font-bold transition">
                                    Batal
                                </button>
                                <button type="submit" id="btn-submit-log" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-2.5 rounded-xl shadow transition text-sm flex items-center gap-2">
                                    <i class="fas fa-paper-plane"></i> Kirim Logbook
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- History Logbook --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-sm font-bold text-gray-850 border-b border-gray-100 pb-3 mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-indigo-500"></i> Riwayat Logbook PKL
                    </h3>

                    <div class="space-y-4">
                        @forelse($placement->logs as $log)
                            @php
                                $isRejected = ($log->status === 'rejected');
                                $isApproved = ($log->status === 'approved');
                            @endphp
                            <div class="border {{ $isRejected ? 'border-rose-300 bg-rose-50/20 shadow-xs' : 'border-gray-100 hover:bg-gray-50/30' }} rounded-xl p-4 transition">
                                <div class="flex flex-col md:flex-row md:items-start justify-between gap-3 mb-3">
                                    <div>
                                        <p class="text-xs font-bold text-gray-700">
                                            {{ \Carbon\Carbon::parse($log->log_date)->translatedFormat('l, d M Y') }}
                                        </p>
                                        @if($log->latitude && $log->longitude)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="inline-flex items-center text-[10px] text-blue-600 hover:underline mt-1">
                                                <i class="fas fa-map-marked-alt mr-1"></i> Lokasi GPS ({{ number_format($log->latitude, 6) }}, {{ number_format($log->longitude, 6) }})
                                            </a>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic mt-1 block">GPS tidak terekam</span>
                                        @endif
                                    </div>
                                    <div>
                                        @php
                                            $statusClass = match($log->status) {
                                                'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'rejected' => 'bg-rose-100 text-rose-800 border-rose-300 font-black',
                                                default => 'bg-amber-50 text-amber-700 border-amber-200'
                                            };
                                            $statusText = match($log->status) {
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Perlu Revisi',
                                                default => 'Menunggu Persetujuan'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $statusClass }}">
                                            <i class="fas {{ $isApproved ? 'fa-check' : ($isRejected ? 'fa-exclamation-triangle' : 'fa-clock') }}"></i>
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="md:col-span-3">
                                        <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">{{ $log->activity }}</p>
                                    </div>
                                    @if($log->photo)
                                        <div class="md:col-span-1">
                                            <a href="{{ asset('storage/' . $log->photo) }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 hover:opacity-90 transition max-h-[100px] shadow-sm">
                                                <img src="{{ asset('storage/' . $log->photo) }}" class="w-full h-[100px] object-cover" alt="Bukti Foto">
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                @if($isRejected && $log->mentor_notes)
                                    <div class="mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 space-y-1">
                                        <span class="font-bold flex items-center gap-1 text-rose-700">
                                            <i class="fas fa-comment-dots text-rose-600"></i> Catatan Revisi dari Pembimbing:
                                        </span>
                                        <p class="italic bg-white/80 p-2 rounded-lg border border-rose-100 text-rose-900 font-medium">"{{ $log->mentor_notes }}"</p>
                                    </div>
                                @endif

                                @if($isRejected && !$placement->grade)
                                    <div class="mt-3 pt-3 border-t border-rose-200 flex justify-end">
                                        <button type="button" onclick="startRevision('{{ $log->log_date->format('Y-m-d') }}', '{{ addslashes($log->activity) }}')"
                                                class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2 rounded-xl text-xs shadow-sm transition flex items-center gap-1.5 transform hover:scale-[1.02]">
                                            <i class="fas fa-edit"></i> Perbaiki / Kirim Ulang Logbook Ini
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-center py-10 text-xs text-gray-400 italic">Belum ada riwayat logbook kegiatan magang.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@if($placement && !$placement->grade)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gpsStatus = document.getElementById('gps-status');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                latInput.value = position.coords.latitude;
                lngInput.value = position.coords.longitude;
                gpsStatus.innerHTML = '<span class="text-emerald-700 flex items-center gap-1.5"><i class="fas fa-check-circle"></i> Koordinat GPS berhasil dikunci (' + position.coords.latitude.toFixed(6) + ', ' + position.coords.longitude.toFixed(6) + ')</span>';
            }, function(error) {
                console.error('GPS error:', error);
                gpsStatus.innerHTML = '<span class="text-amber-700 flex items-center gap-1.5"><i class="fas fa-exclamation-triangle"></i> Lokasi GPS gagal dimuat. Logbook tetap bisa dikirim tanpa lokasi GPS.</span>';
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        } else {
            gpsStatus.innerHTML = '<span class="text-rose-700 flex items-center gap-1.5"><i class="fas fa-times-circle"></i> Browser Anda tidak mendukung sensor GPS.</span>';
        }
    });

    function startRevision(dateStr, activityText) {
        document.getElementById('input_log_date').value = dateStr;
        
        let p1 = activityText;
        let p2 = '-';
        let p3 = '-';
        
        if (activityText.includes("Alat, bahan, atau aplikasi yang saya gunakan:\n")) {
            let parts = activityText.split("Alat, bahan, atau aplikasi yang saya gunakan:\n");
            p1 = parts[0].replace("Kegiatan utama yang saya kerjakan hari ini adalah:\n", "").trim();
            
            let parts2 = parts[1].split("Pengetahuan atau keterampilan baru yang saya pelajari:\n");
            p2 = parts2[0].trim();
            p3 = parts2.length > 1 ? parts2[1].trim() : '-';
        }

        document.getElementById('input_activity_1').value = p1;
        document.getElementById('input_activity_2').value = p2;
        document.getElementById('input_activity_3').value = p3;
        
        document.getElementById('revision-notice').classList.remove('hidden');
        document.getElementById('revision-badge').classList.remove('hidden');
        document.getElementById('btn-cancel-revision').classList.remove('hidden');
        document.getElementById('revision-date-text').innerText = dateStr;
        
        document.getElementById('form-title').innerHTML = '<i class="fas fa-undo-alt text-rose-500"></i> Perbaiki Logbook PKL';
        document.getElementById('btn-submit-log').innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Ulang Revisi Logbook';
        document.getElementById('btn-submit-log').className = 'bg-rose-600 hover:bg-rose-700 text-white font-bold px-6 py-2.5 rounded-xl shadow transition text-sm flex items-center gap-2';

        document.getElementById('form-logbook-card').scrollIntoView({ behavior: 'smooth' });
    }

    function cancelRevision() {
        document.getElementById('input_log_date').value = '{{ date('Y-m-d') }}';
        document.getElementById('input_activity_1').value = '';
        document.getElementById('input_activity_2').value = '';
        document.getElementById('input_activity_3').value = '';
        
        document.getElementById('revision-notice').classList.add('hidden');
        document.getElementById('revision-badge').classList.add('hidden');
        document.getElementById('btn-cancel-revision').classList.add('hidden');
        
        document.getElementById('form-title').innerHTML = '<i class="fas fa-edit text-amber-500"></i> Isi Logbook Harian PKL';
        document.getElementById('btn-submit-log').innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Logbook';
        document.getElementById('btn-submit-log').className = 'bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-2.5 rounded-xl shadow transition text-sm flex items-center gap-2';
    }
</script>
@endif
@endsection
