@extends('layouts.guru')

@section('content')
<div class="space-y-8 pb-12">
    {{-- Header Banner Section (Neo-Brutalism) --}}
    <div class="rounded-3xl p-6 sm:p-8 shadow-xl border-2 border-black relative overflow-hidden flex flex-col sm:flex-row sm:items-center justify-between gap-6" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #047857 100%) !important;">
        <div class="space-y-3 relative z-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-xl border border-black text-xs sm:text-sm font-black uppercase tracking-wider shadow-sm" style="background-color: #fbbf24 !important; color: #000000 !important;">
                <i class="fas fa-file-signature text-black"></i>
                <span>Modul Kinerja Guru & Staff</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight" style="color: #ffffff !important;">
                Perjanjian Kinerja Saya
            </h2>
            <p class="text-emerald-200 font-bold text-sm sm:text-base max-w-2xl leading-relaxed" style="color: #a7f3d0 !important;">
                Kelola dokumen komitmen kinerja riil, pantau tahapan verifikasi secara langsung, serta periksa hasil evaluasi akhir Anda.
            </p>
        </div>
        
        <div class="relative z-10 shrink-0">
            <a href="{{ route('guru.performance_contracts.create') }}" class="inline-flex items-center gap-2.5 bg-amber-400 hover:bg-amber-300 text-black px-6 py-4 rounded-2xl text-xs sm:text-sm font-black uppercase tracking-wider transition-all shadow-md border-2 border-black">
                <i class="fas fa-plus text-black"></i> Buat Kontrak Baru
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="bg-emerald-100 border-2 border-emerald-500 text-emerald-950 px-5 py-4 rounded-2xl flex items-center gap-4 shadow-sm font-bold text-sm sm:text-base">
            <div class="bg-emerald-600 text-white w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 shadow-md">
                <i class="fas fa-check"></i>
            </div>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-100 border-2 border-rose-500 text-rose-950 px-5 py-4 rounded-2xl flex items-center gap-4 shadow-sm font-bold text-sm sm:text-base">
            <div class="bg-rose-600 text-white w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 shadow-md">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    {{-- TAHAPAN & PROGRESS PENGAJUAN PERJANJIAN KINERJA --}}
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
            <div>
                <h3 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2.5">
                    <i class="fas fa-tasks text-emerald-600"></i>
                    <span>Tahapan & Progress Pengajuan Perjanjian Kinerja</span>
                </h3>
                <p class="text-sm sm:text-base text-slate-700 font-bold mt-1">Pantau posisi status dan riwayat tahapan dari setiap dokumen perjanjian yang telah Anda ajukan.</p>
            </div>
        </div>

        @if($contracts->isEmpty())
        {{-- Alur Panduan 4 Tahapan Utama (Muncul saat belum ada pengajuan sebagai panduan) --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border-2 border-slate-200">
            <div class="mb-6 pb-5 border-b-2 border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-xs font-black px-3 py-1 bg-indigo-100 text-indigo-900 rounded-lg uppercase tracking-wider block w-fit mb-1 border border-indigo-300">Panduan Alur Verifikasi</span>
                    <h4 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="fas fa-sitemap text-indigo-600"></i>
                        <span>4 Tahapan Proses Pengajuan & Verifikasi Perjanjian Kinerja</span>
                    </h4>
                </div>
                <div class="text-xs sm:text-sm font-black text-slate-800 bg-slate-100 px-3.5 py-2 rounded-xl border border-slate-300">
                    <i class="fas fa-info-circle text-indigo-600 mr-1"></i> Setiap perjanjian yang diajukan akan diproses melalui 4 tahapan ini
                </div>
            </div>

            {{-- Visual Stepper Horizontal Lingkaran Bulat --}}
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 relative pt-2">
                {{-- Garis Penghubung Horizontal (Membelah Tengah Lingkaran di Desktop) --}}
                <div class="hidden sm:block absolute top-7 left-[12%] right-[12%] h-1.5 bg-gradient-to-r from-emerald-400 via-indigo-400 via-amber-400 to-purple-400 rounded-full z-0 shadow-inner"></div>

                {{-- [1] Pengajuan --}}
                <div class="relative z-10 flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-2.5">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-emerald-100 border-2 border-emerald-400">
                        1
                    </div>
                    <div>
                        <span class="text-xs font-black text-emerald-700 uppercase tracking-wider block mb-0.5">[1] Tahap 1</span>
                        <h5 class="font-black text-base sm:text-lg text-slate-900">Pengajuan Kinerja</h5>
                        <p class="text-xs sm:text-sm font-bold text-slate-700 mt-1">Guru menyusun komitmen & mengajukan form perjanjian ke sistem.</p>
                    </div>
                </div>

                {{-- [2] Persetujuan Kasek --}}
                <div class="relative z-10 flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-2.5">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-indigo-100 border-2 border-indigo-400">
                        2
                    </div>
                    <div>
                        <span class="text-xs font-black text-indigo-700 uppercase tracking-wider block mb-0.5">[2] Tahap 2</span>
                        <h5 class="font-black text-base sm:text-lg text-slate-900">Persetujuan Kasek</h5>
                        <p class="text-xs sm:text-sm font-bold text-slate-700 mt-1">Kepala Sekolah memeriksa, memverifikasi, & menyetujui pengajuan.</p>
                    </div>
                </div>

                {{-- [3] Persetujuan Yayasan --}}
                <div class="relative z-10 flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-2.5">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-amber-100 border-2 border-amber-400">
                        3
                    </div>
                    <div>
                        <span class="text-xs font-black text-amber-700 uppercase tracking-wider block mb-0.5">[3] Tahap 3</span>
                        <h5 class="font-black text-base sm:text-lg text-slate-900">Persetujuan Yayasan</h5>
                        <p class="text-xs sm:text-sm font-bold text-slate-700 mt-1">Yayasan memberikan persetujuan akhir agar dokumen resmi berlaku.</p>
                    </div>
                </div>

                {{-- [4] Evaluasi --}}
                <div class="relative z-10 flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-2.5">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-purple-100 border-2 border-purple-400">
                        4
                    </div>
                    <div>
                        <span class="text-xs font-black text-purple-700 uppercase tracking-wider block mb-0.5">[4] Tahap 4</span>
                        <h5 class="font-black text-base sm:text-lg text-slate-900">Evaluasi & Penilaian</h5>
                        <p class="text-xs sm:text-sm font-bold text-slate-700 mt-1">Penilaian kinerja akhir oleh pimpinan di akhir tahun pelajaran.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-xl border-2 border-slate-200 text-center space-y-5">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-800 border-2 border-slate-300 text-2xl shadow-sm">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="max-w-xl mx-auto space-y-2">
                <h4 class="text-lg sm:text-xl font-black text-slate-900">Belum Ada Dokumen Perjanjian Kinerja yang Diajukan</h4>
                <p class="text-slate-700 font-bold text-sm sm:text-base leading-relaxed">
                    Anda belum membuat dokumen perjanjian kinerja untuk tahun pelajaran ini. Silakan klik tombol di bawah untuk menyusun dan mengajukan komitmen kinerja baru.
                </p>
            </div>
            <div>
                <a href="{{ route('guru.performance_contracts.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm sm:text-base px-6 py-3.5 rounded-2xl shadow-md transition-all border-2 border-emerald-500">
                    <i class="fas fa-plus"></i> Buat Perjanjian Sekarang
                </a>
            </div>
        </div>
        @else
            <div class="space-y-6">
                @foreach($contracts as $contract)
                @php
                    // Penentuan title kontrak
                    $contractTitle = 'Perjanjian Kinerja';
                    $typeBadgeClass = 'bg-gray-100 text-gray-800 border-gray-300';
                    $typeIcon = 'fa-file-alt';
                    if ($contract->contract_type == 'pkg_kejuruan') {
                        $contractTitle = 'Form 2A (Kejuruan/Produktif)';
                        $typeBadgeClass = 'bg-blue-100 text-blue-900 border-blue-400';
                        $typeIcon = 'fa-tools text-blue-600';
                    } elseif ($contract->contract_type == 'pkg_umum') {
                        $contractTitle = 'Form 2B (Mata Pelajaran Umum)';
                        $typeBadgeClass = 'bg-indigo-100 text-indigo-900 border-indigo-400';
                        $typeIcon = 'fa-book-open text-indigo-600';
                    } else {
                        $posName = $contract->position ? $contract->position->position_name : 'Jabatan Tambahan';
                        $contractTitle = 'Form 4 (' . $posName . ')';
                        $typeBadgeClass = 'bg-amber-100 text-amber-900 border-amber-400';
                        $typeIcon = 'fa-briefcase text-amber-600';
                    }

                    // Penentuan status tiap step (1: Pengajuan, 2: Kasek, 3: Yayasan, 4: Evaluasi)
                    $st = $contract->status;
                    
                    // Step 1: Pengajuan
                    $step1Status = 'done'; // karena sudah tersimpan
                    $step1Text = 'Selesai Diajukan';
                    $step1Date = $contract->created_at->format('d M Y');

                    // Step 2: Kasek
                    $step2Status = 'pending';
                    $step2Text = 'Menunggu Pengajuan';
                    if ($st == 'submitted_to_kepsek') {
                        $step2Status = 'active';
                        $step2Text = 'Sedang Diperiksa Kasek';
                    } elseif (in_array($st, ['approved_by_kepsek', 'approved_by_yayasan'])) {
                        $step2Status = 'done';
                        $step2Text = 'Disetujui Kasek';
                    } elseif ($st == 'rejected') {
                        $step2Status = 'rejected';
                        $step2Text = 'Ditolak / Dikembalikan';
                    }

                    // Step 3: Yayasan
                    $step3Status = 'pending';
                    $step3Text = 'Menunggu Kasek';
                    if ($st == 'approved_by_kepsek') {
                        $step3Status = 'active';
                        $step3Text = 'Verifikasi Ketua Yayasan';
                    } elseif ($st == 'approved_by_yayasan') {
                        $step3Status = 'done';
                        $step3Text = 'Disetujui Ketua Yayasan';
                    } elseif ($st == 'rejected') {
                        $step3Status = 'rejected';
                        $step3Text = 'Proses Terhenti';
                    }

                    // Step 4: Evaluasi
                    $step4Status = 'pending';
                    $step4Text = 'Belum Tahap Evaluasi';
                    $approvedEval = $contract->evaluations ? $contract->evaluations->where('status', 'approved_by_yayasan')->first() : null;
                    $submittedEval = $contract->evaluations ? $contract->evaluations->where('status', 'submitted_to_yayasan')->first() : null;

                    if ($st == 'approved_by_yayasan') {
                        if ($approvedEval && $approvedEval->score > 0) {
                            $step4Status = 'done';
                            $step4Text = 'Selesai Dinilai (' . number_format($approvedEval->score, 2) . ')';
                        } elseif ($submittedEval) {
                            $step4Status = 'active';
                            $step4Text = 'Evaluasi Menunggu ACC';
                        } else {
                            $step4Status = 'active';
                            $step4Text = 'Masa Pelaksanaan & Target';
                        }
                    } elseif ($st == 'rejected') {
                        $step4Status = 'rejected';
                        $step4Text = '-';
                    }
                @endphp

                @php
                    $headerBgClass = 'bg-gradient-to-r from-indigo-50 via-purple-50/50 to-white text-slate-900 border-l-8 border-indigo-600';
                    $iconBgClass = 'bg-indigo-600 text-white border-2 border-indigo-300 shadow-md shadow-indigo-500/20';
                    $titleTextClass = 'text-indigo-950';
                    $badgeTextClass = 'text-indigo-700';
                    $yearBadgeClass = 'bg-indigo-100/80 text-indigo-900 border border-indigo-300';
                    if ($contract->contract_type == 'pkg_kejuruan') {
                        $headerBgClass = 'bg-gradient-to-r from-blue-50 via-cyan-50/50 to-white text-slate-900 border-l-8 border-blue-600';
                        $iconBgClass = 'bg-blue-600 text-white border-2 border-blue-300 shadow-md shadow-blue-500/20';
                        $titleTextClass = 'text-blue-950';
                        $badgeTextClass = 'text-blue-700';
                        $yearBadgeClass = 'bg-blue-100/80 text-blue-900 border border-blue-300';
                    } elseif ($contract->contract_type == 'pkg_umum') {
                        $headerBgClass = 'bg-gradient-to-r from-indigo-50 via-purple-50/50 to-white text-slate-900 border-l-8 border-indigo-600';
                        $iconBgClass = 'bg-indigo-600 text-white border-2 border-indigo-300 shadow-md shadow-indigo-500/20';
                        $titleTextClass = 'text-indigo-950';
                        $badgeTextClass = 'text-indigo-700';
                        $yearBadgeClass = 'bg-indigo-100/80 text-indigo-900 border border-indigo-300';
                    } else {
                        $headerBgClass = 'bg-gradient-to-r from-amber-50 via-orange-50/50 to-white text-slate-900 border-l-8 border-amber-500';
                        $iconBgClass = 'bg-amber-600 text-white border-2 border-amber-300 shadow-md shadow-amber-500/20';
                        $titleTextClass = 'text-amber-950';
                        $badgeTextClass = 'text-amber-800';
                        $yearBadgeClass = 'bg-amber-100/80 text-amber-900 border border-amber-300';
                    }
                @endphp

                <div class="bg-white rounded-3xl shadow-xl border-2 {{ $st == 'rejected' ? 'border-rose-400 ring-4 ring-rose-50' : 'border-slate-200 ring-4 ring-slate-100' }} overflow-hidden transition-all duration-300 hover:shadow-2xl">
                    {{-- Header Top Bar dari Card Progress --}}
                    <div class="{{ $headerBgClass }} border-b-2 border-slate-200 px-6 sm:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl {{ $iconBgClass }} flex items-center justify-center text-2xl shrink-0">
                                <i class="fas {{ $typeIcon }} text-white"></i>
                            </div>
                            <div>
                                <div class="text-xs font-black {{ $badgeTextClass }} uppercase tracking-wider">Title Progres Pengajuan</div>
                                <h4 class="text-lg sm:text-2xl font-black {{ $titleTextClass }} tracking-tight">{{ $contractTitle }}</h4>
                                <div class="text-xs sm:text-sm font-bold mt-1 flex items-center gap-2">
                                    <span class="{{ $yearBadgeClass }} font-black px-3 py-0.5 rounded-lg"><i class="fas fa-calendar-alt mr-1"></i> TP. {{ $contract->academicYear->year ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 self-start sm:self-center">
                            @if($st == 'submitted_to_kepsek')
                                <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-black bg-gradient-to-r from-amber-400 to-orange-500 text-slate-950 border-2 border-amber-200 shadow-lg shadow-amber-500/30">
                                    <i class="fas fa-clock animate-pulse"></i> Menunggu Kasek
                                </span>
                            @elseif($st == 'approved_by_kepsek')
                                <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-black bg-gradient-to-r from-blue-500 to-indigo-600 text-white border-2 border-blue-300 shadow-lg shadow-blue-500/30">
                                    <i class="fas fa-spinner animate-spin"></i> Menunggu Yayasan
                                </span>
                            @elseif($st == 'approved_by_yayasan')
                                <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-black bg-gradient-to-r from-emerald-500 to-teal-600 text-white border-2 border-emerald-300 shadow-lg shadow-emerald-500/30">
                                    <i class="fas fa-check-circle"></i> ACC Yayasan
                                </span>
                            @elseif($st == 'rejected')
                                <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-black bg-gradient-to-r from-rose-500 to-red-600 text-white border-2 border-rose-300 shadow-lg shadow-rose-500/30">
                                    <i class="fas fa-times-circle"></i> Dikembalikan
                                </span>
                            @else
                                <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                    {{ $st }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Stepper Progress Bar Horizontal (4 Tahapan Bulat & Kontras Tinggi) --}}
                    <div class="p-6 sm:p-8 bg-gradient-to-b from-slate-50 via-white to-slate-50/80">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 relative">
                            {{-- Line connector horizontal (Membelah tengah lingkaran di desktop) --}}
                            <div class="hidden sm:block absolute top-7 left-[12%] right-[12%] h-2 bg-slate-200 border border-slate-300 rounded-full z-0 shadow-inner">
                                @php
                                    $progressWidth = '0%';
                                    if ($step4Status == 'done') { $progressWidth = '100%'; }
                                    elseif ($step3Status == 'done' || $step4Status == 'active') { $progressWidth = '75%'; }
                                    elseif ($step2Status == 'done' || $step3Status == 'active') { $progressWidth = '50%'; }
                                    elseif ($step1Status == 'done' || $step2Status == 'active') { $progressWidth = '25%'; }
                                @endphp
                                <div class="h-full bg-gradient-to-r from-emerald-500 via-indigo-500 to-purple-600 rounded-full transition-all duration-700 shadow-md" style="width: {{ $progressWidth }}"></div>
                            </div>

                            {{-- [1] Pengajuan Kinerja --}}
                            <div class="relative z-10 flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-2.5">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-emerald-100 border-2 border-emerald-400">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-emerald-700 uppercase tracking-wider block mb-0.5">[1] Tahap 1</span>
                                    <h5 class="font-black text-base sm:text-lg text-slate-900">Pengajuan Kinerja</h5>
                                    <div class="mt-1">
                                        <span class="text-xs sm:text-sm font-black text-emerald-950 bg-emerald-100 px-3.5 py-1 rounded-full border border-emerald-400 inline-block shadow-sm">{{ $step1Text }}</span>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600 block mt-1">{{ $step1Date }}</span>
                                </div>
                            </div>

                            {{-- [2] Persetujuan Kasek --}}
                            <div class="relative z-10 flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-2.5">
                                @if($step2Status == 'done')
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-emerald-100 border-2 border-emerald-400">
                                        <i class="fas fa-check"></i>
                                    </div>
                                @elseif($step2Status == 'active')
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-xl shadow-indigo-500/40 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-indigo-200 border-2 border-indigo-400 animate-pulse scale-110">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                @elseif($step2Status == 'rejected')
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-rose-600 to-red-700 text-white shadow-lg shadow-rose-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-rose-100 border-2 border-rose-400">
                                        <i class="fas fa-times"></i>
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-800 text-white shadow-md flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-slate-200 border-2 border-slate-600">
                                        2
                                    </div>
                                @endif
                                <div>
                                    <span class="text-xs font-black {{ $step2Status == 'done' ? 'text-emerald-700' : ($step2Status == 'active' ? 'text-indigo-700' : ($step2Status == 'rejected' ? 'text-rose-700' : 'text-slate-700')) }} uppercase tracking-wider block mb-0.5">[2] Tahap 2</span>
                                    <h5 class="font-black text-base sm:text-lg text-slate-900">Persetujuan Kasek</h5>
                                    <div class="mt-1">
                                        @if($step2Status == 'done')
                                            <span class="text-xs sm:text-sm font-black text-emerald-950 bg-emerald-100 px-3.5 py-1 rounded-full border border-emerald-400 inline-block shadow-sm">{{ $step2Text }}</span>
                                        @elseif($step2Status == 'active')
                                            <span class="text-xs sm:text-sm font-black text-white bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-1.5 rounded-full border border-indigo-300 inline-block shadow-md shadow-indigo-500/20 animate-pulse">{{ $step2Text }}</span>
                                        @elseif($step2Status == 'rejected')
                                            <span class="text-xs sm:text-sm font-black text-white bg-gradient-to-r from-rose-500 to-red-600 px-3.5 py-1 rounded-full border border-rose-400 inline-block shadow-sm">{{ $step2Text }}</span>
                                        @else
                                            <span class="text-xs sm:text-sm font-black text-slate-800 bg-white px-3.5 py-1 rounded-full border-2 border-slate-300 inline-block shadow-sm">{{ $step2Text }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- [3] Persetujuan Yayasan --}}
                            <div class="relative z-10 flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-2.5">
                                @if($step3Status == 'done')
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-emerald-100 border-2 border-emerald-400">
                                        <i class="fas fa-check"></i>
                                    </div>
                                @elseif($step3Status == 'active')
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-xl shadow-amber-500/40 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-amber-200 border-2 border-amber-400 animate-pulse scale-110">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                @elseif($step3Status == 'rejected')
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-rose-600 to-red-700 text-white shadow-lg shadow-rose-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-rose-100 border-2 border-rose-400">
                                        <i class="fas fa-times"></i>
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-800 text-white shadow-md flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-slate-200 border-2 border-slate-600">
                                        3
                                    </div>
                                @endif
                                <div>
                                    <span class="text-xs font-black {{ $step3Status == 'done' ? 'text-emerald-700' : ($step3Status == 'active' ? 'text-amber-700' : ($step3Status == 'rejected' ? 'text-rose-700' : 'text-slate-700')) }} uppercase tracking-wider block mb-0.5">[3] Tahap 3</span>
                                    <h5 class="font-black text-base sm:text-lg text-slate-900">Persetujuan Yayasan</h5>
                                    <div class="mt-1">
                                        @if($step3Status == 'done')
                                            <span class="text-xs sm:text-sm font-black text-emerald-950 bg-emerald-100 px-3.5 py-1 rounded-full border border-emerald-400 inline-block shadow-sm">{{ $step3Text }}</span>
                                        @elseif($step3Status == 'active')
                                            <span class="text-xs sm:text-sm font-black text-slate-950 bg-gradient-to-r from-amber-400 to-orange-500 px-4 py-1.5 rounded-full border border-amber-300 inline-block shadow-md shadow-amber-500/20 animate-pulse">{{ $step3Text }}</span>
                                        @elseif($step3Status == 'rejected')
                                            <span class="text-xs sm:text-sm font-black text-white bg-gradient-to-r from-rose-500 to-red-600 px-3.5 py-1 rounded-full border border-rose-400 inline-block shadow-sm">{{ $step3Text }}</span>
                                        @else
                                            <span class="text-xs sm:text-sm font-black text-slate-800 bg-white px-3.5 py-1 rounded-full border-2 border-slate-300 inline-block shadow-sm">{{ $step3Text }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- [4] Evaluasi --}}
                            <div class="relative z-10 flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-2.5">
                                @if($step4Status == 'done')
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-600 to-indigo-700 text-white shadow-lg shadow-purple-500/30 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-purple-100 border-2 border-purple-400">
                                        <i class="fas fa-star text-amber-300"></i>
                                    </div>
                                @elseif($step4Status == 'active')
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-xl shadow-purple-500/40 flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-purple-200 border-2 border-purple-400 animate-pulse scale-110">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                @elseif($step4Status == 'rejected')
                                    <div class="w-12 h-12 rounded-full bg-slate-700 text-slate-300 flex items-center justify-center text-lg font-black shrink-0 border-2 border-slate-600">
                                        <i class="fas fa-ban"></i>
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-800 text-white shadow-md flex items-center justify-center text-lg font-black shrink-0 ring-4 ring-slate-200 border-2 border-slate-600">
                                        4
                                    </div>
                                @endif
                                <div>
                                    <span class="text-xs font-black {{ $step4Status == 'done' ? 'text-purple-700' : ($step4Status == 'active' ? 'text-purple-700' : 'text-slate-700') }} uppercase tracking-wider block mb-0.5">[4] Tahap 4</span>
                                    <h5 class="font-black text-base sm:text-lg text-slate-900">Evaluasi & Penilaian</h5>
                                    <div class="mt-1">
                                        @if($step4Status == 'done')
                                            <span class="text-xs sm:text-sm font-black text-white bg-gradient-to-r from-purple-600 to-indigo-600 px-3.5 py-1 rounded-full border border-purple-400 inline-block shadow-sm">{{ $step4Text }}</span>
                                        @elseif($step4Status == 'active')
                                            <span class="text-xs sm:text-sm font-black text-white bg-gradient-to-r from-purple-500 to-indigo-600 px-4 py-1.5 rounded-full border border-purple-300 inline-block shadow-md shadow-purple-500/20 animate-pulse">{{ $step4Text }}</span>
                                        @elseif($step4Status == 'rejected')
                                            <span class="text-xs sm:text-sm font-black text-slate-800 bg-white px-3.5 py-1 rounded-full border-2 border-slate-300 inline-block shadow-sm">-</span>
                                        @else
                                            <span class="text-xs sm:text-sm font-black text-slate-800 bg-white px-3.5 py-1 rounded-full border-2 border-slate-300 inline-block shadow-sm">{{ $step4Text }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Catatan Penolakan jika dikembalikan --}}
                        @if($st == 'rejected' && !empty($contract->notes))
                            <div class="mt-6 bg-rose-50 border-2 border-rose-300 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5">
                                <div class="bg-rose-600 text-white w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div>
                                    <h6 class="font-black text-rose-900 text-base">Catatan Pengembalian / Penolakan:</h6>
                                    <p class="text-rose-800 text-sm sm:text-base font-medium mt-1 leading-relaxed">{{ $contract->notes }}</p>
                                    <div class="mt-3">
                                        <a href="{{ route('guru.performance_contracts.edit', $contract->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm sm:text-base rounded-xl shadow-md transition-all">
                                            <i class="fas fa-pen"></i> Perbaiki & Ajukan Ulang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Tombol Aksi Langsung pada Card Progres --}}
                        <div class="mt-6 pt-5 border-t-2 border-gray-100 flex flex-wrap items-center justify-end gap-3">
                            <a href="{{ route('guru.performance_contracts.show', $contract->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border-2 border-indigo-200 hover:border-indigo-600 font-bold text-sm sm:text-base transition-all shadow-sm">
                                <i class="fas fa-file-contract"></i> Lihat Dokumen Lengkap
                            </a>
                            @if($st == 'approved_by_yayasan')
                                <a href="{{ route('guru.performance_contracts.print', $contract->id) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm sm:text-base transition-all shadow-md shadow-emerald-600/20">
                                    <i class="fas fa-print"></i> Cetak Pakta Integritas
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Table Card (Daftar Semua Perjanjian) --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-lg sm:text-xl font-black text-gray-900 flex items-center gap-2.5">
                <i class="fas fa-table text-indigo-600"></i>
                <span>Tabel Riwayat Perjanjian Kinerja</span>
            </h3>
            <span class="text-xs sm:text-sm font-semibold text-gray-500">Daftar lengkap arsip perjanjian</span>
        </div>

        <div class="bg-white rounded-3xl shadow-xl border border-indigo-100 overflow-hidden">
            <div class="h-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-500"></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-100 via-indigo-50/40 to-slate-100 border-b-2 border-gray-200">
                            <th class="px-6 py-5 text-sm font-black text-gray-800 uppercase tracking-wider">Tahun Ajaran</th>
                            <th class="px-6 py-5 text-sm font-black text-gray-800 uppercase tracking-wider">Tipe Kontrak</th>
                            <th class="px-6 py-5 text-sm font-black text-gray-800 uppercase tracking-wider">Jabatan</th>
                            <th class="px-6 py-5 text-sm font-black text-gray-800 uppercase tracking-wider">Status Persetujuan</th>
                            <th class="px-6 py-5 text-sm font-black text-gray-800 uppercase tracking-wider">Hasil Evaluasi Akhir</th>
                            <th class="px-6 py-5 text-sm font-black text-gray-800 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($contracts as $contract)
                        <tr class="hover:bg-indigo-50/40 transition-all duration-200 group">
                            <td class="px-6 py-5">
                                <div class="font-black text-base sm:text-lg text-gray-900">{{ $contract->academicYear->year }}</div>
                                <div class="text-sm font-bold text-indigo-600 mt-0.5">Semester {{ $contract->academicYear->semester }}</div>
                            </td>
                            <td class="px-6 py-5">
                                @if($contract->contract_type == 'pkg_kejuruan')
                                    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-black bg-blue-100 text-blue-900 border-2 border-blue-300 shadow-sm">
                                        <i class="fas fa-tools text-blue-600"></i> Form 2A (Kejuruan)
                                    </span>
                                @elseif($contract->contract_type == 'pkg_umum')
                                    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-black bg-indigo-100 text-indigo-900 border-2 border-indigo-300 shadow-sm">
                                        <i class="fas fa-book-open text-indigo-600"></i> Form 2B (Umum)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-black bg-amber-100 text-amber-900 border-2 border-amber-300 shadow-sm">
                                        <i class="fas fa-briefcase text-amber-600"></i> Form 4 (Jabatan)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-base text-gray-800 font-bold">
                                {{ $contract->position ? $contract->position->position_name : '-' }}
                            </td>
                            <td class="px-6 py-5">
                                @if($contract->status == 'draft')
                                    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-bold bg-gray-100 text-gray-700 border-2 border-gray-300 shadow-sm"><i class="fas fa-file-alt"></i> Draft</span>
                                @elseif($contract->status == 'submitted_to_kepsek')
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black bg-amber-100 text-amber-900 border-2 border-amber-400 shadow-sm">
                                        <i class="fas fa-clock text-amber-600 animate-pulse"></i> [1] &rarr; [2] Kasek
                                    </span>
                                @elseif($contract->status == 'approved_by_kepsek')
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black bg-blue-100 text-blue-900 border-2 border-blue-400 shadow-sm">
                                        <i class="fas fa-spinner text-blue-600 animate-spin"></i> [2] &rarr; [3] Yayasan
                                    </span>
                                @elseif($contract->status == 'approved_by_yayasan')
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black bg-emerald-100 text-emerald-900 border-2 border-emerald-400 shadow-sm">
                                        <i class="fas fa-check-circle text-emerald-600"></i> Disetujui Penuh
                                    </span>
                                @elseif($contract->status == 'rejected')
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black bg-rose-100 text-rose-900 border-2 border-rose-400 shadow-sm">
                                        <i class="fas fa-times-circle text-rose-600"></i> Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                @php
                                    $approvedEval = $contract->evaluations ? $contract->evaluations->where('status', 'approved_by_yayasan')->first() : null;
                                    $submittedEval = $contract->evaluations ? $contract->evaluations->where('status', 'submitted_to_yayasan')->first() : null;
                                @endphp
                                @if($approvedEval && $approvedEval->score > 0)
                                    @php
                                        $s = $approvedEval->score;
                                        $label = ($s >= 4.5) ? 'Sangat Baik' : (($s >= 3.5) ? 'Baik' : (($s >= 2.5) ? 'Cukup' : 'Kurang'));
                                        $badgeClass = ($s >= 4.5) ? 'bg-purple-100 text-purple-900 border-purple-400' : (($s >= 3.5) ? 'bg-emerald-100 text-emerald-900 border-emerald-400' : 'bg-amber-100 text-amber-900 border-amber-400');
                                    @endphp
                                    <a href="{{ route('guru.performance_contracts.show', $contract->id) }}" class="inline-flex flex-col items-start gap-1 p-3 rounded-2xl {{ $badgeClass }} border-2 hover:shadow-md transition-all">
                                        <div class="flex items-center gap-2 font-black text-base">
                                            <span>{{ number_format($s, 2) }}</span>
                                            <i class="fas fa-star text-amber-400"></i>
                                            <span class="text-xs font-black px-2 py-0.5 bg-white/90 rounded-md">{{ $label }}</span>
                                        </div>
                                        <span class="text-xs font-bold opacity-90"><i class="fas fa-check-circle mr-1"></i> [4] Evaluasi ACC Yayasan</span>
                                    </a>
                                @elseif($submittedEval)
                                    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-black bg-amber-100 text-amber-900 border-2 border-amber-400 shadow-sm">
                                        <i class="fas fa-clock text-amber-600 animate-pulse"></i> [4] Menunggu ACC Yayasan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300 shadow-sm"><i class="fas fa-hourglass-start text-slate-500"></i> Belum Dinilai</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-2.5">
                                    <a href="{{ route('guru.performance_contracts.show', $contract->id) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border-2 border-indigo-200 hover:border-indigo-600 transition-all shadow-sm tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye text-base"></i>
                                    </a>
                                    
                                    @if(in_array($contract->status, ['draft', 'submitted_to_kepsek', 'rejected']))
                                        <a href="{{ route('guru.performance_contracts.edit', $contract->id) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white border-2 border-amber-200 hover:border-amber-600 transition-all shadow-sm tooltip" title="Edit Kontrak">
                                            <i class="fas fa-pen text-base"></i>
                                        </a>
                                        <form action="{{ route('guru.performance_contracts.destroy', $contract->id) }}" method="POST" class="m-0 p-0 inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kontrak ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white border-2 border-rose-200 hover:border-rose-600 transition-all shadow-sm tooltip" title="Hapus Kontrak">
                                                <i class="fas fa-trash-alt text-base"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($contract->status == 'approved_by_yayasan')
                                        <a href="{{ route('guru.performance_contracts.print', $contract->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm border-2 border-emerald-600 transition-all shadow-md tooltip" title="Cetak Pakta Integritas">
                                            <i class="fas fa-print"></i> Cetak
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-slate-100 border-2 border-slate-200 mb-5 shadow-inner">
                                    <i class="fas fa-file-signature text-3xl text-slate-400"></i>
                                </div>
                                <h3 class="text-gray-900 font-black text-xl mb-2">Belum Ada Kontrak</h3>
                                <p class="text-gray-600 font-medium text-base mb-6 max-w-lg mx-auto leading-relaxed">Anda belum membuat Perjanjian Kinerja untuk Tahun Ajaran ini. Silakan mulai buat komitmen baru sekarang.</p>
                                <a href="{{ route('guru.performance_contracts.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-2xl text-base font-bold transition-all shadow-md">
                                    <i class="fas fa-plus"></i> Buat Sekarang
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
