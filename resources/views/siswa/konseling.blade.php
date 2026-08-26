@extends('layouts.siswa')

@section('content')
<div class="space-y-6 pb-12" x-data="{
    showUploadModal: false,
    selectedLevel: 'school',
    selectedRank: 'winner',
    get estimatedPoints() {
        const levelPoints = {
            'international': 500,
            'national': 400,
            'province': 300,
            'city': 200,
            'district': 100,
            'school': 50
        };
        const rankBonus = {
            'winner': 50,
            'runner_up': 30,
            'third_place': 20,
            'participant': 0
        };
        const base = levelPoints[this.selectedLevel] || 50;
        const bonus = rankBonus[this.selectedRank] || 0;
        return base + bonus;
    }
}">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute right-0 top-0 translate-x-8 -translate-y-8 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white text-2xl border border-white/30 shadow-inner">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight">Catatan Prestasi & Perkembangan</h1>
                    <p class="text-amber-100 text-sm mt-0.5">Unggah bukti kejuaraan mandiri dan pantau rekam jejak reputasi Anda.</p>
                </div>
            </div>
            <button type="button" @click="showUploadModal = true"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-amber-900 font-black text-sm shadow-md hover:bg-amber-50 active:scale-95 transition cursor-pointer">
                <i class="fas fa-plus-circle text-amber-600"></i>
                <span>Unggah Prestasi Baru</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="flex-1 text-sm font-bold">
                <p class="text-xs uppercase tracking-wider text-emerald-600 font-black">Berhasil</p>
                <p class="mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-200 text-rose-900 flex items-start gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="flex-1 text-sm">
                <p class="text-xs uppercase tracking-wider text-rose-600 font-black">Terjadi Kesalahan</p>
                <ul class="mt-1 list-disc list-inside text-xs font-bold space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Reputation & Stats Overview -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Poin Reputasi -->
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-star"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider truncate">Total Poin Reputasi</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">{{ number_format($stats['reputation_points'] ?? 0) }} <span class="text-xs font-bold text-amber-600">Pts</span></h3>
                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">
                    {{ $stats['level_name'] ?? 'Newbie' }}
                </span>
            </div>
        </div>

        <!-- Prestasi Diakui -->
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-award"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider truncate">Prestasi Diakui</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">{{ $stats['verified_count'] ?? 0 }} <span class="text-xs font-bold text-gray-400">Kejuaraan</span></h3>
                <p class="text-[10px] font-bold text-emerald-600 mt-1">Poin Sah & Dipertahankan</p>
            </div>
        </div>

        <!-- Menunggu Justifikasi -->
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider truncate">Menunggu Justifikasi</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">{{ $stats['pending_count'] ?? 0 }} <span class="text-xs font-bold text-gray-400">Pengajuan</span></h3>
                <p class="text-[10px] font-bold text-orange-600 mt-1">Poin Aktif Sementara</p>
            </div>
        </div>

        <!-- Total Pembinaan / Kasus -->
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-pink-100 text-pink-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-clipboard-user"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider truncate">Catatan Pembinaan</p>
                <h3 class="text-xl font-black text-gray-900 mt-0.5">{{ $counselingRecords->count() }} <span class="text-xs font-bold text-gray-400">Catatan</span></h3>
                <p class="text-[10px] font-bold text-pink-600 mt-1">Bimbingan & Konseling</p>
            </div>
        </div>
    </div>

    <!-- Section: Prestasi & Penghargaan Siswa -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-medal text-amber-500"></i>
                    Daftar Prestasi & Penghargaan
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Kejuaraan resmi yang telah Anda unggah atau dicatat oleh sekolah.</p>
            </div>
            <button type="button" @click="showUploadModal = true" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200 hover:bg-amber-100 transition">
                <i class="fas fa-plus"></i> Unggah Prestasi
            </button>
        </div>

        <div class="space-y-4">
            @forelse($achievements as $achievement)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Icon / Points Badge -->
                            <div class="flex-shrink-0 flex md:flex-col items-center justify-center gap-2 md:w-28 p-3 rounded-2xl bg-amber-50 border border-amber-100 text-center">
                                <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-2xl shadow-sm">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-200 text-amber-900">
                                        +{{ $achievement->points ?? 50 }} Pts
                                    </span>
                                    <p class="text-[11px] font-bold text-gray-500 mt-1">
                                        {{ $achievement->achievement_date ? $achievement->achievement_date->format('d M Y') : '-' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Detail Content -->
                            <div class="flex-1 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-900 leading-snug">{{ $achievement->title }}</h3>
                                        <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 uppercase">
                                                <i class="fas fa-tag mr-1 text-[10px]"></i>{{ $achievement->type_label }}
                                            </span>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 uppercase">
                                                <i class="fas fa-layer-group mr-1 text-[10px]"></i>Tingkat {{ $achievement->level_label }}
                                            </span>
                                            @if($achievement->rank)
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 uppercase">
                                                    <i class="fas fa-crown mr-1 text-[10px]"></i>{{ $achievement->rank_label }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="shrink-0">
                                        @if($achievement->status === 'verified')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                <i class="fas fa-check-circle text-emerald-600"></i>
                                                Diakui oleh Wali Kelas
                                            </span>
                                        @elseif($achievement->status === 'rejected')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                                <i class="fas fa-times-circle text-rose-600"></i>
                                                Tidak Diakui (Poin Ditarik)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                <i class="fas fa-hourglass-half text-amber-600 animate-spin"></i>
                                                Menunggu Justifikasi Wali Kelas
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($achievement->description)
                                    <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Keterangan / Penyelenggara:</p>
                                        <p class="text-sm text-gray-700 leading-relaxed">{{ $achievement->description }}</p>
                                    </div>
                                @endif

                                <!-- Verification Notes if Rejected or Verified -->
                                @if($achievement->verification_notes)
                                    <div class="p-3.5 rounded-2xl border {{ $achievement->status === 'rejected' ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-emerald-50 border-emerald-200 text-emerald-900' }}">
                                        <p class="text-xs font-bold uppercase tracking-wider mb-0.5">
                                            Catatan Wali Kelas:
                                        </p>
                                        <p class="text-xs font-semibold">{{ $achievement->verification_notes }}</p>
                                    </div>
                                @endif

                                <!-- Bukti Dokumen Sertifikat / Piagam -->
                                @if($achievement->certificate_file)
                                    @php
                                        $ext = strtolower(pathinfo($achievement->certificate_file, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                        $isPdf = ($ext === 'pdf');
                                    @endphp

                                    <div class="pt-2">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ asset('storage/' . $achievement->certificate_file) }}" target="_blank"
                                               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-800 hover:bg-amber-100 hover:text-amber-900 border border-gray-200 transition">
                                                <i class="fas {{ $isPdf ? 'fa-file-pdf text-red-500' : 'fa-image text-blue-500' }} text-sm"></i>
                                                <span>Lihat Dokumen Bukti ({{ strtoupper($ext) }})</span>
                                                <i class="fas fa-external-link-alt text-[10px] text-gray-400"></i>
                                            </a>
                                        </div>

                                        @if($isImage)
                                            <div class="mt-2 rounded-2xl overflow-hidden border border-gray-200 max-w-xs shadow-xs">
                                                <img src="{{ asset('storage/' . $achievement->certificate_file) }}" alt="Bukti Prestasi" class="w-full h-32 object-cover hover:scale-105 transition cursor-pointer" onclick="window.open(this.src, '_blank')">
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Footer Info -->
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex flex-wrap justify-between items-center text-xs text-gray-500 gap-2">
                        <span>Tahun Ajaran: <strong class="text-gray-700">{{ $achievement->academicYear->year ?? '-' }}</strong></span>
                        @if($achievement->verifiedBy)
                            <span>Djustifikasi oleh: <strong class="text-gray-700">{{ $achievement->verifiedBy->name }}</strong> ({{ $achievement->verified_at ? $achievement->verified_at->diffForHumans() : '' }})</span>
                        @else
                            <span>Diunggah {{ $achievement->created_at ? $achievement->created_at->diffForHumans() : '-' }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-4 text-amber-400">
                        <i class="fas fa-award text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Belum Ada Prestasi Terdata</h3>
                    <p class="text-gray-500 text-sm mt-1 max-w-md mx-auto">Pernah meraih juara atau penghargaan? Unggah sekarang untuk mendapatkan poin reputasi secara instan!</p>
                    <button type="button" @click="showUploadModal = true" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-amber-500 text-white font-black text-xs hover:bg-amber-600 transition shadow-sm">
                        <i class="fas fa-plus"></i> Unggah Prestasi Pertama Anda
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Section: Catatan Konseling & Pembinaan -->
    <div class="space-y-4 pt-6 border-t border-gray-200">
        <div>
            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-clipboard-list text-pink-600"></i>
                Catatan Konseling & Bimbingan Karakter
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">Rekam jejak bimbingan, konseling, dan pembinaan karakter oleh Guru BK & Wali Kelas.</p>
        </div>

        <div class="space-y-4">
            @forelse($counselingRecords as $record)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Icon / Date -->
                            <div class="flex-shrink-0 flex md:flex-col items-center justify-center gap-2 md:w-24 p-3 rounded-2xl {{ $record->record_type === 'penghargaan' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-pink-50 text-pink-600 border border-pink-100' }} text-center">
                                <div class="w-12 h-12 rounded-xl {{ $record->record_type === 'penghargaan' ? 'bg-blue-600' : 'bg-pink-600' }} text-white flex items-center justify-center text-xl shadow-sm">
                                    <i class="fas {{ $record->record_type === 'penghargaan' ? 'fa-trophy' : 'fa-clipboard-user' }}"></i>
                                </div>
                                <div class="text-center">
                                    <p class="font-black text-gray-900 text-xs">{{ $record->incident_date->format('d M') }}</p>
                                    <p class="text-[10px] text-gray-500 font-bold">{{ $record->incident_date->format('Y') }}</p>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-900">{{ $record->title }}</h3>
                                        <div class="flex flex-wrap items-center gap-2 mt-1">
                                            @php
                                                $catColors = [
                                                    'akademik'  => 'bg-blue-100 text-blue-800',
                                                    'perilaku'  => 'bg-red-100 text-red-800',
                                                    'sosial'    => 'bg-green-100 text-green-800',
                                                    'karir'     => 'bg-purple-100 text-purple-800',
                                                    'pribadi'   => 'bg-orange-100 text-orange-800',
                                                    'olahraga'  => 'bg-cyan-100 text-cyan-800',
                                                    'seni'      => 'bg-pink-100 text-pink-800',
                                                    'keagamaan' => 'bg-emerald-100 text-emerald-800',
                                                ];
                                                $colorClass = $catColors[$record->category] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $colorClass }}">
                                                {{ ucfirst($record->category) }}
                                            </span>

                                            @if($record->record_type === 'penghargaan')
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-star mr-1"></i>{{ ucfirst($record->achievement_level) }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ match($record->severity) { 'ringan' => 'bg-green-100 text-green-800', 'sedang' => 'bg-yellow-100 text-yellow-800', 'berat' => 'bg-orange-100 text-orange-800', 'kritis' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-800' } }}">
                                                    {{ ucfirst($record->severity) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium border {{ match($record->status) { 'selesai' => 'bg-green-50 text-green-700 border-green-200', 'tindak_lanjut' => 'bg-blue-50 text-blue-700 border-blue-200', default => 'bg-gray-50 text-gray-600 border-gray-200' } }}">
                                        {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                    </span>
                                </div>

                                <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">{{ $record->description }}</p>

                                @if($record->action_taken)
                                    <div class="bg-gray-50 rounded-2xl p-3.5 border border-gray-100">
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tindak Lanjut:</p>
                                        <p class="text-sm text-gray-700">{{ $record->action_taken }}</p>
                                    </div>
                                @endif

                                @if($record->attachment)
                                    @php
                                        $ext = strtolower(pathinfo($record->attachment, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                        $isPdf = ($ext === 'pdf');
                                    @endphp
                                    
                                    <div class="mt-3">
                                        @if($isImage)
                                            <div class="rounded-2xl overflow-hidden border border-gray-200 max-w-sm">
                                                <img src="{{ asset('storage/' . $record->attachment) }}" alt="Bukti" class="w-full h-auto">
                                            </div>
                                        @elseif($isPdf)
                                            <a href="{{ asset('storage/' . $record->attachment) }}" target="_blank" class="flex items-center gap-3 p-3 bg-red-50 rounded-2xl border border-red-100 hover:bg-red-100 transition group w-fit">
                                                <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center group-hover:bg-red-200 transition">
                                                    <i class="fas fa-file-pdf"></i>
                                                </div>
                                                <div class="text-left">
                                                    <p class="text-xs font-bold text-red-900">Dokumen PDF</p>
                                                    <p class="text-xs text-red-600">Klik untuk melihat</p>
                                                </div>
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $record->attachment) }}" target="_blank" class="inline-flex items-center text-sm text-blue-600 hover:underline">
                                                <i class="fas fa-paperclip mr-2"></i> Lihat Lampiran
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                        <span>Dilaporkan oleh: <strong class="text-gray-700">{{ $record->counselor->name ?? 'Admin' }}</strong></span>
                        <span>{{ $record->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                        <i class="fas fa-clipboard-check text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Belum Ada Catatan Konseling</h3>
                    <p class="text-gray-500 text-sm mt-1">Belum ada catatan bimbingan atau pembinaan untuk saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- MODAL: Form Upload Prestasi Mandiri -->
    <div x-show="showUploadModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto"
         @keydown.escape.window="showUploadModal = false">
        
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-100"
             @click.outside="showUploadModal = false">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-6 text-white rounded-t-3xl flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg border border-white/30">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black leading-tight">Unggah Prestasi & Kejuaraan</h3>
                        <p class="text-xs text-amber-100">Poin reputasi akan langsung aktif dan diverifikasi oleh Wali Kelas.</p>
                    </div>
                </div>
                <button type="button" @click="showUploadModal = false" class="w-8 h-8 rounded-full bg-black/20 hover:bg-black/40 text-white flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body / Form -->
            <form action="{{ route('siswa.prestasi.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf

                <!-- Point Preview Notice Box -->
                <div class="p-4 rounded-2xl bg-amber-50 border-2 border-amber-200 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-amber-950 flex items-center justify-center text-xl font-black shrink-0 shadow-xs">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-amber-900 uppercase tracking-wider">Estimasi Poin Langsung Diterima:</p>
                            <p class="text-xs text-amber-700">Poin masuk seketika ke akun Anda saat formulir dikirim.</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-2xl font-black text-amber-900" x-text="'+' + estimatedPoints + ' Pts'">+50 Pts</span>
                    </div>
                </div>

                <!-- 1. Nama Prestasi / Kejuaraan -->
                <div class="space-y-1">
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-wider">
                        Nama Prestasi / Kejuaraan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" required placeholder="Contoh: Juara 1 Olimpiade Sains Terapan Nasional"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-sm font-semibold text-gray-900 transition">
                </div>

                <!-- 2. Kategori / Bidang & Tingkat -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider">
                            Bidang / Kategori <span class="text-rose-500">*</span>
                        </label>
                        <select name="type" required
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-sm font-semibold text-gray-900 transition">
                            <option value="academic">Akademik / Sains</option>
                            <option value="sport">Olahraga & Atletik</option>
                            <option value="art">Seni, Musik & Budaya</option>
                            <option value="competition">Kompetisi Kejuruan / LKS</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider">
                            Tingkat Kejuaraan <span class="text-rose-500">*</span>
                        </label>
                        <select name="level" x-model="selectedLevel" required
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-sm font-semibold text-gray-900 transition">
                            <option value="school">Tingkat Sekolah (+50 Poin)</option>
                            <option value="district">Tingkat Kecamatan (+100 Poin)</option>
                            <option value="city">Tingkat Kabupaten/Kota (+200 Poin)</option>
                            <option value="province">Tingkat Provinsi (+300 Poin)</option>
                            <option value="national">Tingkat Nasional (+400 Poin)</option>
                            <option value="international">Tingkat Internasional (+500 Poin)</option>
                        </select>
                    </div>
                </div>

                <!-- 3. Peringkat / Hasil & Tanggal Perolehan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider">
                            Hasil / Peringkat
                        </label>
                        <select name="rank" x-model="selectedRank"
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-sm font-semibold text-gray-900 transition">
                            <option value="winner">Juara 1 (Bonus +50 Poin)</option>
                            <option value="runner_up">Juara 2 (Bonus +30 Poin)</option>
                            <option value="third_place">Juara 3 (Bonus +20 Poin)</option>
                            <option value="participant">Peserta / Harapan (+0 Poin)</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider">
                            Tanggal Perolehan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="achievement_date" max="{{ date('Y-m-d') }}" required value="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-sm font-semibold text-gray-900 transition">
                    </div>
                </div>

                <!-- 4. Upload Dokumen Bukti (Sertifikat / Piagam / Medali) -->
                <div class="space-y-1">
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-wider flex items-center justify-between">
                        <span>Dokumen Bukti (Sertifikat/Piagam) <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-gray-400 font-bold">PDF, JPG, PNG (Maks 10MB)</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 hover:border-amber-500 rounded-2xl p-4 text-center bg-gray-50 transition">
                        <i class="fas fa-file-arrow-up text-3xl text-gray-400 mb-2"></i>
                        <input type="file" name="certificate_file" required accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full text-xs font-bold text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-amber-500 file:text-white hover:file:bg-amber-600 cursor-pointer">
                        <p class="text-[10px] text-gray-400 font-semibold mt-2">Wajib mengunggah scan/foto sertifikat atau piagam kejuaraan yang jelas agar dapat divalidasi oleh Wali Kelas.</p>
                    </div>
                </div>

                <!-- 5. Keterangan / Deskripsi -->
                <div class="space-y-1">
                    <label class="block text-xs font-black text-gray-700 uppercase tracking-wider">
                        Keterangan Tambahan / Penyelenggara (Opsional)
                    </label>
                    <textarea name="description" rows="3" placeholder="Sebutkan instansi penyelenggara lomba, lokasi acara, atau rincian kejuaraan..."
                              class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 text-sm font-semibold text-gray-900 transition resize-none"></textarea>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showUploadModal = false"
                            class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-700 font-bold text-xs hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-6 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-black text-xs shadow-md hover:from-amber-600 hover:to-orange-600 active:scale-95 transition cursor-pointer flex items-center gap-2">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <span>Unggah & Dapatkan Poin</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

