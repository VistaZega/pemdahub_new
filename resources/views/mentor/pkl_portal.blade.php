<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Mentor DUDI - PembdaHUB</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome & Alpine JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            background-color: #fafafa;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans text-gray-800 antialiased" x-data="{ activeTab: 'logs' }">

    {{-- Header Banner --}}
    <header class="bg-gradient-to-r from-brand-700 via-brand-600 to-indigo-600 text-white shadow-md">
        <div class="max-w-5xl mx-auto px-4 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shadow-inner">
                    <i class="fas fa-briefcase text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold leading-tight">Portal Mentor PKL</h1>
                    <p class="text-[10px] text-white/80">SMKS Swasta Pembda Nias & Mitra Industri (DUDI)</p>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">
                    Akses Industri
                </span>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 py-6">
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-2xl text-sm font-semibold mb-6 flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 text-base"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Student Profile Card --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col md:flex-row items-center gap-5">
                <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-50 border border-gray-200 shadow-md flex-shrink-0">
                    <img src="{{ $placement->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $placement->student->full_name }}">
                </div>
                <div class="text-center md:text-left flex-1 space-y-1">
                    <span class="text-[10px] bg-brand-100 text-brand-700 font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                        Siswa Magang
                    </span>
                    <h2 class="text-xl font-bold text-gray-900 leading-none mt-1">{{ $placement->student->full_name }}</h2>
                    <p class="text-xs text-gray-500 flex flex-wrap items-center justify-center md:justify-start gap-x-2 gap-y-1">
                        <span><i class="fas fa-school mr-1 text-gray-400"></i>{{ $placement->student->school->name ?? 'SMKS Swasta Pembda Nias' }}</span>
                        <span class="text-gray-300">·</span>
                        <span>Jurusan: {{ $placement->student->major->name ?? '-' }}</span>
                    </p>
                </div>
                <div class="border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-6 w-full md:w-auto text-center md:text-left">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Tempat Magang</p>
                    <p class="text-sm font-bold text-gray-850">{{ $placement->company_name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-user-tie text-[10px] mr-1 text-gray-400"></i>Mentor: {{ $placement->mentor_name }}</p>
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="flex border-b border-gray-200 mb-6 bg-white rounded-2xl p-1 shadow-sm">
            <button @click="activeTab = 'logs'" :class="activeTab === 'logs' ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-600 hover:text-brand-600'" class="flex-1 py-3 text-sm font-bold rounded-xl transition flex items-center justify-center gap-2">
                <i class="fas fa-clipboard-list"></i> Logbook Harian ({{ $logs->count() }})
            </button>
            <button @click="activeTab = 'grades'" :class="activeTab === 'grades' ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-600 hover:text-brand-600'" class="flex-1 py-3 text-sm font-bold rounded-xl transition flex items-center justify-center gap-2">
                <i class="fas fa-star"></i> Penilaian Akhir
            </button>
        </div>

        {{-- Logbook Tab Content --}}
        <div x-show="activeTab === 'logs'" x-transition class="space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-850 mb-4 flex items-center gap-2">
                    <i class="fas fa-list text-brand-500"></i> Verifikasi Jurnal Kegiatan Magang
                </h3>

                <div class="space-y-6 relative pl-5 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-150">
                    @forelse($logs as $log)
                        @php
                            $statusClass = match($log->status) {
                                'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-250',
                                'rejected' => 'bg-rose-50 text-rose-700 border-rose-250',
                                default => 'bg-amber-50 text-amber-700 border-amber-250'
                            };
                            $statusText = match($log->status) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Perlu Revisi',
                                default => 'Perlu Verifikasi DUDI'
                            };
                        @endphp
                        <div class="relative" x-data="{ showRejectForm: false }">
                            {{-- Bullet --}}
                            <div class="absolute -left-[21px] top-1.5 z-10 w-2.5 h-2.5 rounded-full border-2 border-white {{ $log->status === 'approved' ? 'bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,0.2)]' : ($log->status === 'rejected' ? 'bg-rose-500 shadow-[0_0_0_3px_rgba(239,68,68,0.2)]' : 'bg-amber-500 shadow-[0_0_0_3px_rgba(245,158,11,0.2)]') }}"></div>

                            <div class="border border-gray-100 hover:border-gray-200 rounded-2xl p-4 transition-all">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">
                                            {{ \Carbon\Carbon::parse($log->log_date)->translatedFormat('l, d M Y') }}
                                        </p>
                                        @if($log->latitude && $log->longitude)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="inline-flex items-center text-[10px] text-blue-600 hover:underline mt-1">
                                                <i class="fas fa-map-marked-alt mr-1"></i> Lihat Peta GPS ({{ number_format($log->latitude, 5) }}, {{ number_format($log->longitude, 5) }})
                                            </a>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic mt-0.5 block">Lokasi GPS tidak terekam</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="md:col-span-3 text-xs text-gray-650 leading-relaxed whitespace-pre-line">
                                        {{ $log->activity }}
                                    </div>
                                    @if($log->photo)
                                        <div class="md:col-span-1">
                                            <button type="button" @click="$dispatch('open-preview-modal', { url: '{{ asset('storage/' . $log->photo) }}', name: 'Bukti Kegiatan Logbook PKL ({{ $log->log_date->format('d/m/Y') }})' })" class="block rounded-xl overflow-hidden border-2 border-indigo-400 hover:border-indigo-600 shadow-md hover:shadow-lg max-h-[100px] group relative w-full transition transform active:scale-95">
                                                <img src="{{ asset('storage/' . $log->photo) }}" class="w-full h-[100px] object-cover group-hover:scale-105 transition duration-300" alt="Foto kegiatan">
                                                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[11px] font-extrabold gap-1">
                                                    <i class="fas fa-search-plus"></i> Lihat di Layar
                                                </div>
                                            </button>
                                            <a href="{{ asset('storage/' . $log->photo) }}" target="_blank" download class="mt-1.5 block text-center text-[10px] text-gray-500 hover:text-indigo-700 font-bold hover:underline">
                                                <i class="fas fa-download mr-1"></i> Unduh File Asli
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                @if($log->status === 'submitted')
                                    {{-- Approval buttons --}}
                                    <div class="mt-4 border-t border-gray-50 pt-3 flex flex-wrap gap-2 justify-end" x-show="!showRejectForm">
                                        <button @click="showRejectForm = true" class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold px-4 py-2 rounded-xl text-xs transition flex items-center gap-1">
                                            <i class="fas fa-times"></i> Minta Revisi
                                        </button>
                                        <form action="{{ route('mentor.pkl.log.approve', [$token, $log->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-4 py-2 rounded-xl text-xs shadow transition flex items-center gap-1">
                                                <i class="fas fa-check"></i> Setujui Jurnal
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Rejection Note form --}}
                                    <div class="mt-4 border-t border-gray-50 pt-3" x-show="showRejectForm" x-cloak>
                                        <form action="{{ route('mentor.pkl.log.reject', [$token, $log->id]) }}" method="POST" class="space-y-3">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alasan Penolakan / Catatan Perbaikan</label>
                                                <textarea name="mentor_notes" rows="2" placeholder="Contoh: Deskripsi kegiatan kurang detail, atau foto bukti tidak sesuai..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-brand-400 focus:bg-white transition" required></textarea>
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="showRejectForm = false" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold px-3 py-1.5 rounded-lg text-xs transition">
                                                    Batal
                                                </button>
                                                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow transition">
                                                    Kirim Penolakan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                @if($log->status === 'rejected' && $log->mentor_notes)
                                    <div class="mt-3 p-3 bg-rose-50/50 border border-rose-100 rounded-xl text-xs text-rose-800">
                                        <span class="font-bold flex items-center gap-1 mb-0.5"><i class="fas fa-exclamation-circle"></i> Catatan Mentor:</span>
                                        <p class="italic">"{{ $log->mentor_notes }}"</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-400 italic text-sm">
                            Belum ada entri logbook harian yang dikirim oleh siswa.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Grades Tab Content --}}
        <div x-show="activeTab === 'grades'" x-transition class="space-y-6" x-cloak>
            @if($placement->grade)
                {{-- Already Graded state --}}
                <div class="bg-slate-900 text-white rounded-3xl shadow-lg border border-slate-800 p-6 md:p-8 relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-brand-500/10 rounded-full"></div>
                    <div class="relative z-10 max-w-2xl">
                        <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-2">
                            <i class="fas fa-check-circle"></i> Penilaian Industri Berhasil Disimpan
                        </div>
                        <h3 class="text-xl font-bold mb-6">Hasil Penilaian Akhir Magang</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="bg-white/5 rounded-2xl p-5 border border-white/5 flex flex-col justify-center">
                                <span class="text-[10px] text-slate-400 uppercase tracking-wider">Rata-rata Nilai</span>
                                <span class="text-4xl font-extrabold text-white mt-1">{{ number_format($placement->grade->score_average, 1) }}</span>
                            </div>
                            <div class="md:col-span-2 grid grid-cols-2 gap-4">
                                <div class="bg-white/5 p-3 rounded-xl border border-white/5">
                                    <span class="text-[10px] text-slate-400 block">Kedisiplinan</span>
                                    <span class="text-lg font-bold text-emerald-400">{{ $placement->grade->score_discipline }}</span>
                                </div>
                                <div class="bg-white/5 p-3 rounded-xl border border-white/5">
                                    <span class="text-[10px] text-slate-400 block">Kerjasama Tim</span>
                                    <span class="text-lg font-bold text-emerald-400">{{ $placement->grade->score_teamwork }}</span>
                                </div>
                                <div class="bg-white/5 p-3 rounded-xl border border-white/5">
                                    <span class="text-[10px] text-slate-400 block">Kemampuan Teknis</span>
                                    <span class="text-lg font-bold text-emerald-400">{{ $placement->grade->score_technical }}</span>
                                </div>
                                <div class="bg-white/5 p-3 rounded-xl border border-white/5">
                                    <span class="text-[10px] text-slate-400 block">Keselamatan Kerja</span>
                                    <span class="text-lg font-bold text-emerald-400">{{ $placement->grade->score_safety }}</span>
                                </div>
                            </div>
                        </div>

                        @if($placement->grade->notes)
                            <div class="p-4 bg-white/5 border border-white/5 rounded-2xl">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan Tambahan & Rekomendasi Kerja:</span>
                                <p class="text-slate-200 italic text-sm">"{{ $placement->grade->notes }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                {{-- Grading Form --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6" x-data="{
                    disc: 80,
                    team: 80,
                    tech: 80,
                    safe: 80,
                    get avg() {
                        return (parseInt(this.disc) + parseInt(this.team) + parseInt(this.tech) + parseInt(this.safe)) / 4;
                    }
                }">
                    <h3 class="text-base font-bold text-gray-850 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                        <i class="fas fa-edit text-brand-500"></i> Formulir Penilaian Akhir Magang (PKL)
                    </h3>

                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                        Harap berikan nilai evaluasi untuk siswa magang bersangkutan selama melakukan kegiatan praktik kerja di instansi Anda. Rentang nilai: <strong>0 - 100</strong>.
                    </p>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                        <div class="flex items-start gap-3 text-amber-800">
                            <i class="fas fa-exclamation-triangle mt-1 text-amber-600"></i>
                            <div>
                                <p class="text-sm font-bold mb-1">Peringatan Sebelum Menilai</p>
                                <p class="text-xs leading-relaxed">Masa magang siswa ini dijadwalkan selesai pada <strong>{{ \Carbon\Carbon::parse($placement->end_date)->translatedFormat('d F Y') }}</strong>. Pastikan seluruh laporan kegiatan (logbook) telah diperiksa. <br><strong>PENTING:</strong> Setelah nilai akhir disimpan, status PKL siswa akan otomatis terkunci menjadi <strong>Selesai</strong> dan siswa tidak dapat lagi mengisi logbook harian.</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('mentor.pkl.grade.store', $token) }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Score Inputs --}}
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center mb-1.5">
                                        <label class="block text-xs font-bold text-gray-650 uppercase">1. Kedisiplinan</label>
                                        <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded" x-text="disc"></span>
                                    </div>
                                    <input type="range" name="score_discipline" min="0" max="100" x-model="disc" class="w-full accent-brand-600 h-2 bg-gray-100 rounded-lg appearance-none cursor-pointer">
                                    <span class="text-[10px] text-gray-400 block mt-1">Kehadiran, ketepatan waktu, kepatuhan aturan DUDI.</span>
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-1.5">
                                        <label class="block text-xs font-bold text-gray-650 uppercase">2. Kerjasama & Sikap</label>
                                        <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded" x-text="team"></span>
                                    </div>
                                    <input type="range" name="score_teamwork" min="0" max="100" x-model="team" class="w-full accent-brand-600 h-2 bg-gray-100 rounded-lg appearance-none cursor-pointer">
                                    <span class="text-[10px] text-gray-400 block mt-1">Komunikasi, kolaborasi dengan staf, keaktifan berinteraksi.</span>
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-1.5">
                                        <label class="block text-xs font-bold text-gray-650 uppercase">3. Kemampuan Teknis</label>
                                        <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded" x-text="tech"></span>
                                    </div>
                                    <input type="range" name="score_technical" min="0" max="100" x-model="tech" class="w-full accent-brand-600 h-2 bg-gray-100 rounded-lg appearance-none cursor-pointer">
                                    <span class="text-[10px] text-gray-400 block mt-1">Kualitas hasil pekerjaan, pemahaman tugas lapangan.</span>
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-1.5">
                                        <label class="block text-xs font-bold text-gray-650 uppercase">4. Keselamatan Kerja & Kebersihan</label>
                                        <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded" x-text="safe"></span>
                                    </div>
                                    <input type="range" name="score_safety" min="0" max="100" x-model="safe" class="w-full accent-brand-600 h-2 bg-gray-100 rounded-lg appearance-none cursor-pointer">
                                    <span class="text-[10px] text-gray-400 block mt-1">Penerapan standar K3, kerapihan peralatan & ruang kerja.</span>
                                </div>
                            </div>

                            {{-- Live preview / Total calculated --}}
                            <div class="flex flex-col justify-center items-center bg-gray-50 border border-gray-100 rounded-3xl p-6 text-center">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nilai Rata-Rata</span>
                                <div class="text-5xl font-black text-brand-700 mt-2" x-text="avg.toFixed(1)"></div>
                                <p class="text-xs text-gray-500 mt-3 max-w-[200px] leading-relaxed">
                                    Dihitung secara otomatis dari akumulasi rata-rata 4 poin di samping.
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-5">
                            <label class="block text-xs font-bold text-gray-650 uppercase mb-2">Catatan Kualitatif / Rekomendasi Kerja Untuk Siswa</label>
                            <textarea name="notes" rows="4" placeholder="Berikan komentar membangun mengenai sikap, pencapaian terbaik siswa, atau prospek kerja siswa di industri Anda..." class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:bg-white transition"></textarea>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-start gap-3 mt-4">
                            <input type="checkbox" id="confirm_finish" required class="mt-1 w-4 h-4 text-brand-600 bg-white border-gray-300 rounded focus:ring-brand-500 cursor-pointer">
                            <label for="confirm_finish" class="text-xs text-gray-700 cursor-pointer">
                                Saya telah memeriksa semua logbook harian siswa dan <strong>secara sadar memberikan nilai akhir</strong>. Saya mengerti bahwa tindakan ini akan <strong>menutup akses logbook siswa</strong> (Selesai PKL).
                            </label>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-6 py-3 rounded-2xl shadow-lg transition text-sm flex items-center gap-2">
                                <i class="fas fa-check-double"></i> Simpan Penilaian Akhir
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
        
    </main>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-500 text-center py-6 text-xs mt-12 border-t border-slate-800">
        <div class="max-w-5xl mx-auto px-4">
            <p class="font-bold text-slate-400 mb-1">SMKS Swasta Pembda Nias</p>
            <p>&copy; {{ date('Y') }} PembdaHUB. Dikembangkan bersama Yayasan Perguruan PEMBDA Nias.</p>
        </div>
    </footer>

    @include('components.preview-modal')
</body>
</html>
