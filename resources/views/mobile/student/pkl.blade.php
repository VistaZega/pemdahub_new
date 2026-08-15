@extends('mobile.layouts.app')

@section('title', 'Jurnal PKL Harian - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1" x-data="pklJournalForm()">
    <!-- Header Card -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Praktik Kerja Lapangan (PKL)</h2>
        <div class="w-9"></div>
    </div>

    @if($pklPlacement)
        <!-- DUDI Placement Hero Clay Card -->
        <div class="clay-orange p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40 shadow-xs">
                    SMK Kelas XII
                </span>
                <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full border border-white/30">
                    {{ $pklPlacement->shift ? 'Shift ' . ucfirst($pklPlacement->shift) : 'Reguler' }}
                </span>
            </div>

            <div>
                <h3 class="text-lg font-black text-white leading-tight">
                    {{ $pklPlacement->dudi->name ?? ($pklPlacement->company_name ?? 'Lokasi DUDI Belum Ditentukan') }}
                </h3>
                @if($pklPlacement->dudi?->address)
                    <p class="text-[11px] text-orange-100 mt-1 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-xs shrink-0"></i>
                        <span class="truncate">{{ $pklPlacement->dudi->address }}</span>
                    </p>
                @endif
            </div>

            <div class="pt-2 border-t border-white/20 flex items-center justify-between text-[11px] font-bold text-orange-100">
                <div class="flex items-center gap-1.5 min-w-0">
                    <i class="fa-solid fa-chalkboard-user text-xs shrink-0"></i>
                    <span class="truncate">Pembimbing: <strong>{{ $pklPlacement->teacher->full_name ?? '-' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Quick Stats Widget -->
        @php
            $totalLogs = $logs->count();
            $approvedLogs = $logs->where('status', 'approved')->count();
            $pendingLogs = $logs->where('status', '!=', 'approved')->count();
        @endphp
        <div class="grid grid-cols-3 gap-2.5">
            <div class="clay-card p-3 text-center">
                <span class="text-xl font-black text-slate-800 leading-none">{{ $totalLogs }}</span>
                <span class="block text-[9px] font-black text-slate-500 uppercase mt-1">Total Jurnal</span>
            </div>
            <div class="clay-green p-3 text-center">
                <span class="text-xl font-black leading-none">{{ $approvedLogs }}</span>
                <span class="block text-[9px] font-black uppercase mt-1">Disetujui</span>
            </div>
            <div class="clay-yellow p-3 text-center">
                <span class="text-xl font-black leading-none">{{ $pendingLogs }}</span>
                <span class="block text-[9px] font-black uppercase mt-1">Menunggu</span>
            </div>
        </div>

        <!-- Form Input Jurnal PKL Harian (Clay Card with Photo & GPS) -->
        <div class="clay-card p-5 space-y-3.5 bg-white border-2 border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-camera-retro text-orange-500"></i> Kirim Jurnal & Foto Kegiatan PKL
                </h3>
                <span class="text-[9px] font-extrabold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-200">Wajib Tag GPS</span>
            </div>

            <form action="{{ route('mobile.pkl.log') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                @csrf
                <!-- Tanggal Kegiatan -->
                <div>
                    <label for="date" class="block text-xs font-black text-slate-800 mb-1">Tanggal Kegiatan</label>
                    <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" onclick="try { this.showPicker(); } catch(e) {}" required
                           class="w-full px-3.5 py-2.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold cursor-pointer focus:outline-hidden focus:border-orange-500">
                </div>

                <!-- Deskripsi Aktivitas -->
                <div>
                    <label for="activity_description" class="block text-xs font-black text-slate-800 mb-1">Deskripsi Aktivitas / Pekerjaan di DUDI</label>
                    <textarea id="activity_description" name="activity_description" rows="3" required
                              placeholder="Tuliskan secara jelas aktivitas pekerjaan, mesin/alat yang digunakan, atau materi yang dipelajari hari ini..."
                              class="w-full p-3.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold resize-none focus:outline-hidden focus:border-orange-500"></textarea>
                </div>

                <!-- Upload / Ambil Foto Bukti PKL -->
                <div>
                    <label class="block text-xs font-black text-slate-800 mb-1">
                        Foto Bukti Kegiatan PKL <span class="text-slate-400 font-bold">(Maks. 10MB)</span>
                    </label>

                    <!-- Preview Container -->
                    <template x-if="photoPreview">
                        <div class="relative rounded-2xl overflow-hidden border-2 border-orange-400 mb-2 bg-slate-900/5">
                            <img :src="photoPreview" class="w-full h-48 object-cover rounded-xl" alt="Preview Foto PKL">
                            
                            <!-- GPS Overlay Watermark on Preview -->
                            <div class="absolute bottom-2 left-2 right-2 bg-black/70 backdrop-blur-xs text-white p-2 rounded-xl text-[10px] font-bold space-y-0.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-orange-300 font-black flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot"></i> GPS TAGGED
                                    </span>
                                    <span class="text-slate-300">{{ date('d M Y') }}</span>
                                </div>
                                <p class="text-[9px] text-slate-200 truncate" x-text="latitude ? (latitude + ', ' + longitude) : 'Menunggu Koordinat...'"></p>
                            </div>

                            <button type="button" @click="clearPhoto()" 
                                    class="absolute top-2 right-2 w-7 h-7 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-md active:scale-95">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                    </template>

                    <!-- File Input Box -->
                    <div x-show="!photoPreview" 
                         @click="$refs.photoInput.click()" 
                         class="border-2 border-dashed border-slate-300 hover:border-orange-500 bg-[#f8fafc] rounded-2xl p-4 text-center cursor-pointer transition space-y-1.5 active:scale-[0.99]">
                        <div class="w-10 h-10 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center mx-auto text-lg shadow-2xs">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-800">Ambil Foto / Upload Bukti</p>
                            <p class="text-[10px] text-slate-500 font-bold">Kamera HP atau Galeri Gambar</p>
                        </div>
                    </div>

                    <input type="file" x-ref="photoInput" name="photo" accept="image/*" capture="environment" @change="previewImage($event)" class="hidden">
                </div>

                <!-- GPS Tagging Sensor Box -->
                <div class="p-3.5 rounded-2xl border-2 transition"
                     :class="gpsStatus === 'success' ? 'bg-emerald-50 border-emerald-300' : (gpsStatus === 'searching' ? 'bg-amber-50 border-amber-300' : 'bg-rose-50 border-rose-300')">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid" :class="gpsStatus === 'success' ? 'fa-location-crosshairs text-emerald-600' : (gpsStatus === 'searching' ? 'fa-spinner fa-spin text-amber-600' : 'fa-triangle-exclamation text-rose-600')"></i>
                            <span class="text-xs font-black" :class="gpsStatus === 'success' ? 'text-emerald-900' : (gpsStatus === 'searching' ? 'text-amber-900' : 'text-rose-900')">
                                Sensor GPS PKL
                            </span>
                        </div>
                        <button type="button" @click="fetchGps()" class="text-[10px] font-black underline text-blue-700 active:scale-95">
                            <i class="fa-solid fa-rotate mr-0.5"></i> Refresh Lokasi
                        </button>
                    </div>

                    <div class="mt-1.5 text-[11px] font-bold" :class="gpsStatus === 'success' ? 'text-emerald-800' : (gpsStatus === 'searching' ? 'text-amber-800' : 'text-rose-800')">
                        <template x-if="gpsStatus === 'success'">
                            <p class="flex items-center justify-between">
                                <span>📍 Terkunci: <strong x-text="latitude.toFixed(6) + ', ' + longitude.toFixed(6)"></strong></span>
                                <span class="text-[9px] bg-emerald-200/70 px-1.5 py-0.5 rounded-md text-emerald-900 font-extrabold" x-text="'±' + Math.round(accuracy) + 'm'"></span>
                            </p>
                        </template>
                        <template x-if="gpsStatus === 'searching'">
                            <p>Mengambil koordinat GPS presisi tinggi...</p>
                        </template>
                        <template x-if="gpsStatus === 'error'">
                            <p x-text="gpsErrorMessage || 'GPS tidak terdeteksi. Izin lokasi browser diperlukan.'"></p>
                        </template>
                    </div>

                    <!-- Hidden Inputs for Form Submission -->
                    <input type="hidden" name="latitude" :value="latitude">
                    <input type="hidden" name="longitude" :value="longitude">
                </div>

                <button type="submit" class="clay-btn w-full py-3 text-white font-black text-xs flex items-center justify-center gap-2 shadow-md active:scale-95">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Jurnal Harian & Foto
                </button>
            </form>
        </div>

    @else
        <!-- Empty Placement State -->
        <div class="clay-card p-6 text-center space-y-3">
            <div class="w-14 h-14 rounded-3xl bg-amber-50 border-2 border-amber-200 text-amber-500 text-2xl flex items-center justify-center mx-auto shadow-inner">
                💼
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Penempatan PKL Belum Ditentukan</h3>
                <p class="text-xs text-slate-500 font-semibold mt-1">Data penempatan tempat DUDI dan Guru Pembimbing Anda sedang dalam proses oleh Panitia PKL & Hubin SMK.</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-[11px] font-bold text-slate-600">
                Hubungi Panitia PKL atau Guru Jurusan untuk informasi penempatan industri Anda.
            </div>
        </div>
    @endif

    <!-- Riwayat Jurnal PKL & Galeri Foto Bukti -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                Riwayat Jurnal PKL ({{ $logs->count() }})
            </h3>
        </div>

        @forelse($logs as $log)
            @php
                $isApproved = ($log->status === 'approved');
                $isRejected = ($log->status === 'rejected');
            @endphp
            <div class="clay-card p-4 space-y-3 border-2 {{ $isApproved ? 'border-emerald-300 bg-white' : ($isRejected ? 'border-rose-300 bg-rose-50/20' : 'border-amber-300 bg-white') }}">
                <!-- Header Card: Date & Status -->
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar-check text-blue-500"></i>
                        {{ \Carbon\Carbon::parse($log->log_date ?? $log->created_at)->translatedFormat('l, d F Y') }}
                    </span>

                    @if($isApproved)
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-green">
                            <i class="fa-solid fa-check-double text-[8px] mr-0.5"></i> Disetujui
                        </span>
                    @elseif($isRejected)
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-300">
                            <i class="fa-solid fa-xmark text-[8px] mr-0.5"></i> Perlu Revisi
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase clay-yellow">
                            <i class="fa-solid fa-hourglass-half text-[8px] mr-0.5"></i> Menunggu
                        </span>
                    @endif
                </div>

                <!-- Foto Bukti PKL yang Diunggah -->
                @if($log->photo_url)
                    <div class="rounded-2xl overflow-hidden border-2 border-slate-200 bg-slate-100 relative group">
                        <img src="{{ $log->photo_url }}" 
                             alt="Bukti PKL {{ $log->log_date }}" 
                             class="w-full h-44 object-cover"
                             onclick="window.open('{{ $log->photo_url }}', '_blank')">
                        <div class="absolute bottom-2 right-2 bg-black/70 backdrop-blur-xs text-white px-2 py-1 rounded-lg text-[9px] font-bold flex items-center gap-1">
                            <i class="fa-solid fa-magnifying-glass-plus"></i> Klik untuk Perbesar
                        </div>
                    </div>
                @endif

                <!-- Deskripsi Aktivitas -->
                <div class="text-xs text-slate-800 font-bold leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line">
                    {{ $log->activity ?? $log->activity_description }}
                </div>

                <!-- GPS Tagging Info & Verifikasi -->
                <div class="space-y-1.5 pt-1 border-t border-slate-100 text-[10px] font-extrabold">
                    <!-- GPS Location Link -->
                    @if($log->latitude && $log->longitude)
                        <div class="flex items-center justify-between text-blue-700 bg-blue-50/70 px-2.5 py-1.5 rounded-xl border border-blue-200">
                            <span class="flex items-center gap-1 truncate">
                                <i class="fa-solid fa-location-dot text-rose-500"></i>
                                <span>Tag GPS: {{ number_format($log->latitude, 5) }}, {{ number_format($log->longitude, 5) }}</span>
                            </span>
                            <a href="https://maps.google.com/?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank"
                               class="text-[9px] font-black text-blue-800 bg-white px-2 py-0.5 rounded-md border border-blue-300 shadow-2xs hover:bg-blue-100 transition shrink-0">
                                Buka Peta <i class="fa-solid fa-arrow-up-right-from-square text-[8px] ml-0.5"></i>
                            </a>
                        </div>
                    @else
                        <span class="text-slate-400 italic block">GPS tidak terekam pada entri ini</span>
                    @endif

                    <!-- Approval Info -->
                    @if($isApproved && $log->approved_at)
                        <div class="text-emerald-700 flex items-center justify-between pt-0.5">
                            <span><i class="fa-solid fa-shield-halved mr-1"></i>Diverifikasi Pembimbing</span>
                            <span>{{ \Carbon\Carbon::parse($log->approved_at)->translatedFormat('d M Y H:i') }}</span>
                        </div>
                    @endif

                    <!-- Rejection / Revision Notes -->
                    @if($isRejected && $log->mentor_notes)
                        <div class="p-2.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                            <span class="font-black flex items-center gap-1"><i class="fa-solid fa-comment-dots"></i> Catatan Revisi Pembimbing:</span>
                            <p class="font-bold mt-0.5 italic">"{{ $log->mentor_notes }}"</p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            @if($pklPlacement)
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Belum ada jurnal harian yang dikirim. Mulai laporkan kegiatan PKL Anda hari ini! 🚀
            </div>
            @endif
        @endforelse
    </div>
</div>

<script>
function pklJournalForm() {
    return {
        photoPreview: null,
        latitude: null,
        longitude: null,
        accuracy: null,
        gpsStatus: 'searching', // 'searching', 'success', 'error'
        gpsErrorMessage: '',

        init() {
            this.fetchGps();
        },

        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        clearPhoto() {
            this.photoPreview = null;
            if (this.$refs.photoInput) {
                this.$refs.photoInput.value = '';
            }
        },

        fetchGps() {
            this.gpsStatus = 'searching';
            this.gpsErrorMessage = '';

            if (!navigator.geolocation) {
                this.gpsStatus = 'error';
                this.gpsErrorMessage = 'Browser Anda tidak mendukung sensor Geolocation GPS.';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    this.accuracy = position.coords.accuracy || 10;
                    this.gpsStatus = 'success';
                },
                (error) => {
                    console.warn('GPS Error:', error);
                    this.gpsStatus = 'error';
                    if (error.code === error.PERMISSION_DENIED) {
                        this.gpsErrorMessage = 'Izin lokasi ditolak. Silakan aktifkan izin lokasi di browser HP Anda.';
                    } else {
                        this.gpsErrorMessage = 'Gagal mendeteksi lokasi GPS. Pastikan GPS/Lokasi HP aktif.';
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
    }
}
</script>
@endsection

