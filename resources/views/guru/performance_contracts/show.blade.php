@extends('layouts.guru')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto pb-12">
    {{-- Header Navigation --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2">
                <i class="fas fa-file-contract"></i> Rincian & Status Pengajuan
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900">Detail Perjanjian Kinerja</h2>
            <p class="text-sm sm:text-base text-gray-600 font-medium mt-1">Tinjau rincian komitmen bukti fisik nyata dan tahapan verifikasi dokumen Anda.</p>
        </div>
        <a href="{{ route('guru.performance_contracts.index') }}" class="inline-flex items-center gap-2 bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-800 px-5 py-2.5 rounded-2xl text-sm sm:text-base font-bold transition-all shadow-sm self-start sm:self-center">
            <i class="fas fa-arrow-left text-emerald-600"></i> Kembali ke Daftar
        </a>
    </div>

    @php
        // Penentuan status tiap step (1: Pengajuan, 2: Kasek, 3: Yayasan, 4: Evaluasi)
        $st = $contract->status;
        
        // Step 1: Pengajuan
        $step1Status = 'done';
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

        $contractTitle = 'Perjanjian Kinerja';
        $typeIcon = 'fa-file-alt text-gray-600';
        if ($contract->contract_type == 'pkg_kejuruan') {
            $contractTitle = 'Form 2A (Kejuruan/Produktif)';
            $typeIcon = 'fa-tools text-blue-600';
        } elseif ($contract->contract_type == 'pkg_umum') {
            $contractTitle = 'Form 2B (Mata Pelajaran Umum)';
            $typeIcon = 'fa-book-open text-indigo-600';
        } else {
            $posName = $contract->position ? $contract->position->position_name : 'Jabatan Tambahan';
            $contractTitle = 'Form 4 (' . $posName . ')';
            $typeIcon = 'fa-briefcase text-amber-600';
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

    {{-- Progress Tracker Card Khusus Dokumen Ini --}}
    <div class="bg-white rounded-3xl shadow-xl border-2 {{ $st == 'rejected' ? 'border-rose-400 ring-4 ring-rose-50' : 'border-slate-200 ring-4 ring-slate-100' }} overflow-hidden transition-all duration-300 hover:shadow-2xl">
        <div class="{{ $headerBgClass }} border-b-2 border-slate-200 px-6 sm:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl {{ $iconBgClass }} flex items-center justify-center text-2xl shrink-0">
                    <i class="fas {{ $typeIcon }} text-white"></i>
                </div>
                <div>
                    <div class="text-xs font-black {{ $badgeTextClass }} uppercase tracking-wider">Status Tahapan Pengajuan</div>
                    <h3 class="text-lg sm:text-2xl font-black {{ $titleTextClass }} tracking-tight">{{ $contractTitle }}</h3>
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

        {{-- 4 Tahapan Progress Tracker Bulat & Kontras Tinggi --}}
        <div class="p-6 sm:p-8 bg-gradient-to-b from-slate-50 via-white to-slate-50/80">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 relative">
                {{-- Line connector horizontal --}}
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
                    <div class="w-full flex flex-col items-start sm:items-center">
                        <span class="text-xs font-black text-emerald-700 uppercase tracking-wider block mb-0.5">[1] Tahap 1</span>
                        <h5 class="font-black text-base sm:text-lg text-slate-900">Pengajuan Kinerja</h5>
                        <div class="mt-1.5 w-full flex justify-start sm:justify-center">
                            <span class="text-xs font-bold text-emerald-950 bg-emerald-100 px-4 py-1.5 rounded-full border border-emerald-400 inline-flex items-center justify-center text-center shadow-xs leading-snug">{{ $step1Text }}</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-600 block mt-1">{{ $step1Date }}</span>
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
                    <div class="w-full flex flex-col items-start sm:items-center">
                        <span class="text-xs font-black {{ $step2Status == 'done' ? 'text-emerald-700' : ($step2Status == 'active' ? 'text-indigo-700' : ($step2Status == 'rejected' ? 'text-rose-700' : 'text-slate-700')) }} uppercase tracking-wider block mb-0.5">[2] Tahap 2</span>
                        <h5 class="font-black text-base sm:text-lg text-slate-900">Persetujuan Kasek</h5>
                        <div class="mt-1.5 w-full flex justify-start sm:justify-center">
                            @if($step2Status == 'done')
                                <span class="text-xs font-bold text-emerald-950 bg-emerald-100 px-4 py-1.5 rounded-full border border-emerald-400 inline-flex items-center justify-center text-center shadow-xs leading-snug">{{ $step2Text }}</span>
                            @elseif($step2Status == 'active')
                                <span class="text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-1.5 rounded-full border border-indigo-300 inline-flex items-center justify-center text-center shadow-md shadow-indigo-500/20 animate-pulse leading-snug">{{ $step2Text }}</span>
                            @elseif($step2Status == 'rejected')
                                <span class="text-xs font-bold text-white bg-gradient-to-r from-rose-500 to-red-600 px-4 py-1.5 rounded-full border border-rose-400 inline-flex items-center justify-center text-center shadow-xs leading-snug">{{ $step2Text }}</span>
                            @else
                                <span class="text-xs font-bold text-slate-800 bg-white px-4 py-1.5 rounded-full border border-slate-300 inline-flex items-center justify-center text-center shadow-xs leading-snug">{{ $step2Text }}</span>
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
                    <div class="w-full flex flex-col items-start sm:items-center">
                        <span class="text-xs font-black {{ $step3Status == 'done' ? 'text-emerald-700' : ($step3Status == 'active' ? 'text-amber-700' : ($step3Status == 'rejected' ? 'text-rose-700' : 'text-slate-700')) }} uppercase tracking-wider block mb-0.5">[3] Tahap 3</span>
                        <h5 class="font-black text-base sm:text-lg text-slate-900">Persetujuan Yayasan</h5>
                        <div class="mt-1.5 w-full flex justify-start sm:justify-center">
                            @if($step3Status == 'done')
                                <span class="text-xs font-bold text-emerald-950 bg-emerald-100 px-4 py-1.5 rounded-full border border-emerald-400 inline-flex items-center justify-center text-center shadow-xs leading-snug">{{ $step3Text }}</span>
                            @elseif($step3Status == 'active')
                                <span class="text-xs font-bold text-slate-950 bg-gradient-to-r from-amber-400 to-orange-500 px-4 py-1.5 rounded-full border border-amber-300 inline-flex items-center justify-center text-center shadow-md shadow-amber-500/20 animate-pulse leading-snug">{{ $step3Text }}</span>
                            @elseif($step3Status == 'rejected')
                                <span class="text-xs font-bold text-white bg-gradient-to-r from-rose-500 to-red-600 px-4 py-1.5 rounded-full border border-rose-400 inline-flex items-center justify-center text-center shadow-xs leading-snug">{{ $step3Text }}</span>
                            @else
                                <span class="text-xs font-bold text-slate-800 bg-white px-4 py-1.5 rounded-full border border-slate-300 inline-flex items-center justify-center text-center shadow-xs leading-snug">{{ $step3Text }}</span>
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
                    <div class="w-full flex flex-col items-start sm:items-center">
                        <span class="text-xs font-black {{ $step4Status == 'done' ? 'text-purple-700' : ($step4Status == 'active' ? 'text-purple-700' : 'text-slate-700') }} uppercase tracking-wider block mb-0.5">[4] Tahap 4</span>
                        <h5 class="font-black text-base sm:text-lg text-slate-900">Evaluasi & Penilaian</h5>
                        <div class="mt-1.5 w-full flex justify-start sm:justify-center">
                            @if($step4Status == 'done')
                                <span class="text-xs font-bold text-white bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-1.5 rounded-full border border-purple-400 inline-flex items-center justify-center text-center shadow-xs leading-snug">{{ $step4Text }}</span>
                            @elseif($step4Status == 'active')
                                <span class="text-xs font-bold text-white bg-gradient-to-r from-purple-500 to-indigo-600 px-4 py-1.5 rounded-full border border-purple-300 inline-flex items-center justify-center text-center shadow-md shadow-purple-500/20 animate-pulse leading-snug">{{ $step4Text }}</span>
                            @elseif($step4Status == 'rejected')
                                <span class="text-xs font-bold text-slate-800 bg-white px-4 py-1.5 rounded-full border border-slate-300 inline-flex items-center justify-center text-center shadow-xs leading-snug">-</span>
                            @else
                                <span class="text-xs font-bold text-slate-800 bg-white px-4 py-1.5 rounded-full border border-slate-300 inline-flex items-center justify-center text-center shadow-xs leading-snug">{{ $step4Text }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

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
        </div>
    </div>

    {{-- Dokumen Utama --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden">
        <div class="h-2 bg-gradient-to-r from-emerald-500 via-indigo-500 to-purple-500"></div>
        <div class="p-6 sm:p-10">
            <div class="text-center mb-8 border-b-4 border-indigo-600 pb-6 inline-block w-full">
                <h2 class="text-2xl sm:text-3xl font-black text-gray-900 uppercase tracking-wide leading-tight">
                    @if($contract->contract_type == 'pkg_kejuruan')
                        PERJANJIAN KINERJA GURU<br><span class="text-indigo-600">(PRODUKTIF/KEJURUAN)</span>
                    @elseif($contract->contract_type == 'pkg_umum')
                        PERJANJIAN KINERJA GURU<br><span class="text-indigo-600">(MAPEL UMUM)</span>
                    @else
                        PERJANJIAN KINERJA JABATAN
                    @endif
                </h2>
                <p class="text-base sm:text-lg font-black text-indigo-600 mt-2.5 uppercase tracking-widest bg-indigo-50 inline-block px-4 py-1.5 rounded-full border border-indigo-200">
                    Tahun Pelajaran {{ $contract->academicYear->year }}
                </p>
            </div>

            <div class="bg-indigo-50/60 border-2 border-indigo-200/80 rounded-2xl p-5 sm:p-6 text-gray-800 leading-relaxed text-base sm:text-lg mb-8 shadow-inner font-medium">
                <p class="mb-3">Yang bertanda tangan di bawah ini, saya selaku <strong class="text-indigo-950 font-black">Pihak yang menyatakan berjanji</strong>:</p>
                <div class="space-y-2 bg-white p-4 rounded-xl border border-indigo-100 my-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 pb-2">
                        <span class="text-sm font-bold text-gray-500">Nama Lengkap</span>
                        <span class="font-black text-base sm:text-lg text-gray-900">{{ auth()->user()->name }}</span>
                    </div>
                    @if($contract->contract_type == 'jabatan_tambahan')
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pt-1">
                        <span class="text-sm font-bold text-gray-500">Jabatan Tambahan</span>
                        <span class="font-black text-base sm:text-lg text-indigo-700 bg-indigo-50 px-3 py-1 rounded-lg border border-indigo-200">{{ $contract->position->position_name ?? '-' }}</span>
                    </div>
                    @endif
                </div>
                <p>
                    Dengan ini menyatakan <strong class="text-indigo-950 font-black bg-amber-200/80 px-2 py-0.5 rounded">KOMITMEN DAN KESANGGUPAN PENUH</strong> untuk melaksanakan serta mencapai target kinerja riil pada Tahun Pelajaran {{ $contract->academicYear->year }}, sebagaimana tertuang secara rinci pada tabel bukti fisik nyata berikut ini:
                </p>
            </div>

            <div class="overflow-x-auto rounded-2xl border-2 border-gray-200 shadow-md mb-8">
                @if(in_array($contract->contract_type, ['pkg_kejuruan', 'pkg_umum']))
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-100 via-gray-100 to-slate-100 border-b-2 border-gray-300">
                                <th class="px-5 py-4 text-sm font-black text-gray-800 uppercase tracking-wider text-center border-r-2 border-gray-200" width="8%">No</th>
                                <th class="px-5 py-4 text-sm font-black text-gray-800 uppercase tracking-wider border-r-2 border-gray-200" width="38%">Pilar Perjanjian Kinerja</th>
                                <th class="px-5 py-4 text-sm font-black text-gray-800 uppercase tracking-wider border-r-2 border-gray-200">Rencana Bukti Fisik Nyata (Target)</th>
                                <th class="px-5 py-4 text-sm font-black text-gray-800 uppercase tracking-wider text-center" width="18%">Status Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-gray-100">
                            <tr class="hover:bg-indigo-50/40 transition-colors">
                                <td class="px-5 py-5 text-center font-black text-base text-gray-800 border-r-2 border-gray-200 bg-gray-50/60">1</td>
                                <td class="px-5 py-5 font-black text-base sm:text-lg text-indigo-950 border-r-2 border-gray-200 bg-gray-50/30">{{ $contract->contract_type == 'pkg_kejuruan' ? 'Kompetensi Praktik (30%)' : 'Kompetensi Relevansi Praktik (30%)' }}</td>
                                <td class="px-5 py-5 text-base sm:text-lg font-bold text-gray-800 border-r-2 border-gray-200 leading-relaxed">{{ $contract->target_data['pilar_1'] ?? '-' }}</td>
                                <td class="px-5 py-5 text-center bg-gray-50/40 text-sm font-bold text-gray-500 italic">Menunggu Evaluasi Akhir</td>
                            </tr>
                            <tr class="hover:bg-indigo-50/40 transition-colors">
                                <td class="px-5 py-5 text-center font-black text-base text-gray-800 border-r-2 border-gray-200 bg-gray-50/60">2</td>
                                <td class="px-5 py-5 font-black text-base sm:text-lg text-indigo-950 border-r-2 border-gray-200 bg-gray-50/30">{{ $contract->contract_type == 'pkg_kejuruan' ? 'Kontribusi Program (30%)' : 'Kontribusi Program/TEFA (30%)' }}</td>
                                <td class="px-5 py-5 text-base sm:text-lg font-bold text-gray-800 border-r-2 border-gray-200 leading-relaxed">{{ $contract->target_data['pilar_2'] ?? '-' }}</td>
                                <td class="px-5 py-5 text-center bg-gray-50/40 text-sm font-bold text-gray-500 italic">Menunggu Evaluasi Akhir</td>
                            </tr>
                            <tr class="hover:bg-indigo-50/40 transition-colors">
                                <td class="px-5 py-5 text-center font-black text-base text-gray-800 border-r-2 border-gray-200 bg-gray-50/60">3</td>
                                <td class="px-5 py-5 font-black text-base sm:text-lg text-indigo-950 border-r-2 border-gray-200 bg-gray-50/30">Kolaborasi (20%)</td>
                                <td class="px-5 py-5 text-base sm:text-lg font-bold text-gray-800 border-r-2 border-gray-200 leading-relaxed">{{ $contract->target_data['pilar_3'] ?? '-' }}</td>
                                <td class="px-5 py-5 text-center bg-gray-50/40 text-sm font-bold text-gray-500 italic">Menunggu Evaluasi Akhir</td>
                            </tr>
                            <tr class="hover:bg-indigo-50/40 transition-colors">
                                <td class="px-5 py-5 text-center font-black text-base text-gray-800 border-r-2 border-gray-200 bg-gray-50/60">4</td>
                                <td class="px-5 py-5 font-black text-base sm:text-lg text-indigo-950 border-r-2 border-gray-200 bg-gray-50/30">Budaya Industri 5R (20%)</td>
                                <td class="px-5 py-5 text-base sm:text-lg font-bold text-gray-800 border-r-2 border-gray-200 leading-relaxed">{{ $contract->target_data['pilar_4'] ?? '-' }}</td>
                                <td class="px-5 py-5 text-center bg-gray-50/40 text-sm font-bold text-gray-500 italic">Menunggu Evaluasi Akhir</td>
                            </tr>
                        </tbody>
                    </table>
                @elseif($contract->contract_type == 'jabatan_tambahan')
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-100 via-gray-100 to-slate-100 border-b-2 border-gray-300">
                                <th class="px-5 py-4 text-sm font-black text-gray-800 uppercase tracking-wider text-center border-r-2 border-gray-200" width="8%">No</th>
                                <th class="px-5 py-4 text-sm font-black text-gray-800 uppercase tracking-wider border-r-2 border-gray-200">Deskripsi Target Pekerjaan (Harus Bisa Diukur)</th>
                                <th class="px-5 py-4 text-sm font-black text-gray-800 uppercase tracking-wider text-center" width="22%">Status Evaluasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-gray-100">
                            <tr class="hover:bg-indigo-50/40 transition-colors">
                                <td class="px-5 py-5 text-center font-black text-base text-gray-800 border-r-2 border-gray-200 bg-gray-50/60">1</td>
                                <td class="px-5 py-5 text-base sm:text-lg font-bold text-gray-800 border-r-2 border-gray-200 leading-relaxed">{{ $contract->target_data['target_1'] ?? '-' }}</td>
                                <td class="px-5 py-5 text-center bg-gray-50/40 text-sm font-bold text-gray-500 italic">Menunggu Evaluasi Akhir</td>
                            </tr>
                            <tr class="hover:bg-indigo-50/40 transition-colors">
                                <td class="px-5 py-5 text-center font-black text-base text-gray-800 border-r-2 border-gray-200 bg-gray-50/60">2</td>
                                <td class="px-5 py-5 text-base sm:text-lg font-bold text-gray-800 border-r-2 border-gray-200 leading-relaxed">{{ $contract->target_data['target_2'] ?? '-' }}</td>
                                <td class="px-5 py-5 text-center bg-gray-50/40 text-sm font-bold text-gray-500 italic">Menunggu Evaluasi Akhir</td>
                            </tr>
                            <tr class="hover:bg-indigo-50/40 transition-colors">
                                <td class="px-5 py-5 text-center font-black text-base text-gray-800 border-r-2 border-gray-200 bg-gray-50/60">3</td>
                                <td class="px-5 py-5 text-base sm:text-lg font-bold text-gray-800 border-r-2 border-gray-200 leading-relaxed">{{ $contract->target_data['target_3'] ?? '-' }}</td>
                                <td class="px-5 py-5 text-center bg-gray-50/40 text-sm font-bold text-gray-500 italic">Menunggu Evaluasi Akhir</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Hasil Evaluasi Akhir Semester --}}
    @if($contract->evaluations && $contract->evaluations->count() > 0)
    <div class="space-y-6">
        <h3 class="text-xl sm:text-2xl font-black text-gray-900 flex items-center gap-3">
            <i class="fas fa-award text-amber-500"></i> Hasil Evaluasi Akhir Semester
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($contract->evaluations as $eval)
                @if($eval->status === 'approved_by_yayasan')
                <div class="bg-white rounded-3xl shadow-xl border border-indigo-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-700 px-6 py-4">
                        <h4 class="text-white font-black text-lg">{{ $eval->semester->name ?? 'Semester' }}</h4>
                        <p class="text-indigo-100 text-sm font-medium">{{ $eval->semester->academicYear->name ?? '' }}</p>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-5 bg-indigo-50/70 p-4 rounded-2xl border border-indigo-100">
                            <span class="text-base font-black text-indigo-950">Nilai Akhir:</span>
                            <div class="flex items-center gap-3">
                                <span class="text-3xl sm:text-4xl font-black text-indigo-700">{{ number_format($eval->score, 2) }}</span>
                                <div class="flex text-amber-400 text-sm sm:text-base">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="fas fa-star {{ $i <= round($eval->score) ? '' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mt-4 pt-2">
                            <h5 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Rincian Penilaian per Pilar</h5>
                            @foreach($eval->evaluation_data as $key => $score)
                                @php $displayKey = ucwords(str_replace('_', ' ', $key)); @endphp
                                <div class="flex justify-between items-center text-base py-2 border-b border-gray-100">
                                    <span class="text-gray-800 font-bold truncate pr-4" title="{{ $displayKey }}">{{ $displayKey }}</span>
                                    <span class="font-black text-indigo-900 bg-indigo-50 border border-indigo-200 px-3 py-1 rounded-xl text-sm">{{ $score }} / 5</span>
                                </div>
                            @endforeach
                        </div>

                        @if(is_array($eval->evaluation_data) && count($eval->evaluation_data) > 0)
                            @php
                                $scores = $eval->evaluation_data;
                                $maxScore = max($scores);
                                $minScore = min($scores);
                                $highestKeys = array_keys($scores, $maxScore);
                                $lowestKeys = array_keys($scores, $minScore);

                                $getKeyLabel = function($k) use ($contract) {
                                    if (isset($contract->target_data[$k])) {
                                        $val = $contract->target_data[$k];
                                        $text = is_array($val) ? implode(', ', $val) : $val;
                                        if (!empty($text)) {
                                            return \Illuminate\Support\Str::limit(strip_tags($text), 45);
                                        }
                                    }
                                    $map = [
                                        'pilar_1' => ($contract->contract_type == 'pkg_kejuruan') ? 'Kompetensi Praktik' : 'Kompetensi Relevansi Praktik',
                                        'pilar_2' => ($contract->contract_type == 'pkg_kejuruan') ? 'Kontribusi Program' : 'Kontribusi Program/TEFA',
                                        'pilar_3' => 'Kolaborasi',
                                        'pilar_4' => 'Budaya Industri 5R / K3',
                                    ];
                                    return $map[$k] ?? ucwords(str_replace('_', ' ', $k));
                                };

                                $lowestLabel = $getKeyLabel($lowestKeys[0] ?? '');
                                $highestLabel = $getKeyLabel($highestKeys[0] ?? '');
                                $score = $eval->score;

                                if ($score >= 4.0) {
                                    $analisa = "Kinerja sangat konsisten dan unggul. Keunggulan utama pada aspek {$highestLabel} ({$maxScore}/5). Layak dipertahankan sebagai rol model.";
                                } elseif ($score >= 3.5) {
                                    if ($minScore < 3.0) {
                                        $analisa = "Memenuhi syarat rata-rata SK (> 3.5), namun perlu perhatian khusus pada peningkatan aspek {$lowestLabel} ({$minScore}/5).";
                                    } else {
                                        $analisa = "Kinerja stabil dan memenuhi target di seluruh pilar. Paling menonjol pada aspek {$highestLabel} ({$maxScore}/5).";
                                    }
                                } elseif ($score >= 2.5) {
                                    $analisa = "Kinerja dalam tahap cukup. Diperlukan pembinaan dan pendampingan intensif khususnya pada aspek {$lowestLabel} ({$minScore}/5).";
                                } else {
                                    $analisa = "Kinerja berada di bawah target yang disepakati. Evaluasi menyeluruh dan evaluasi pembinaan diperlukan pada aspek {$lowestLabel}.";
                                }
                            @endphp
                            <div class="mt-6 p-5 bg-indigo-50/90 border-2 border-indigo-200 rounded-2xl text-left text-sm text-slate-800 space-y-2 shadow-inner font-medium">
                                <div class="font-black text-indigo-950 flex items-center gap-2 text-base">
                                    <i class="fas fa-chart-pie text-indigo-600"></i> Analisis Deskriptif Kinerja
                                </div>
                                <div class="leading-relaxed text-slate-800">{{ $analisa }}</div>
                            </div>
                        @endif

                        @if($eval->notes)
                        <div class="mt-5 bg-amber-50 p-4 rounded-2xl border-2 border-amber-200">
                            <p class="text-sm font-black text-amber-900 mb-1 flex items-center gap-2"><i class="fas fa-comment-dots text-amber-600"></i> Catatan Evaluasi:</p>
                            <p class="text-base text-amber-950 italic font-medium">"{{ $eval->notes }}"</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        
        @if($contract->evaluations->where('status', 'approved_by_yayasan')->count() === 0)
            <div class="bg-gray-50 p-6 rounded-3xl text-center border-2 border-dashed border-gray-300">
                <p class="text-gray-600 font-bold text-base">Evaluasi semester sedang diproses atau belum di-ACC oleh Yayasan.</p>
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
