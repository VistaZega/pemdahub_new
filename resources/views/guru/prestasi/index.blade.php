@extends('layouts.guru')
@section('title', 'Justifikasi Prestasi Siswa - Portal Wali Kelas')

@section('content')
<div class="space-y-6 pb-12" x-data="{
    showJustifyModal: false,
    selectedAchievement: null,
    justifyAction: 'approve', // 'approve' or 'reject'
    justifyNotes: '',
    previewFileUrl: '',
    previewFileType: '',
    showPreviewModal: false,

    openJustify(ach, action) {
        this.selectedAchievement = ach;
        this.justifyAction = action;
        this.justifyNotes = (action === 'approve') ? 'Sertifikat dan data prestasi telah diverifikasi keabsahannya.' : '';
        this.showJustifyModal = true;
    },

    openPreview(url, type) {
        this.previewFileUrl = url;
        this.previewFileType = type;
        this.showPreviewModal = true;
    }
}">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #312e81 100%) !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <a href="{{ route('guru.dashboard') }}" class="text-xs font-black text-amber-300 hover:text-amber-400 uppercase tracking-wider mb-2 inline-flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Dashboard Utama
                </a>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-trophy text-black"></i>
                    </div>
                    Justifikasi Prestasi Siswa (Wali Kelas)
                </h1>
                <p class="text-xs text-slate-300 font-bold mt-1">
                    Tinjau dokumen bukti kejuaraan siswa. Akui untuk mempertahankan poin atau Tolak untuk menarik kembali poin yang telah dikreditkan.
                </p>
            </div>

            @if($isWaliKelas)
                <div class="flex flex-wrap items-center gap-2">
                    @foreach($homeroomClassrooms as $cls)
                        <span class="bg-amber-400 text-black border-2 border-black px-3.5 py-1.5 rounded-2xl text-xs font-black uppercase tracking-wider shadow-sm">
                            <i class="fas fa-chalkboard-user mr-1"></i> Kelas: {{ $cls->class_name }} ({{ $cls->school->short_name ?? $cls->school->name ?? '' }})
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-100 border-2 border-black text-emerald-950 flex items-start gap-3 shadow-md">
            <div class="w-8 h-8 rounded-xl bg-emerald-400 border-2 border-black text-black flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-check font-black"></i>
            </div>
            <div class="flex-1 text-sm font-black">
                <p class="text-xs uppercase tracking-wider text-emerald-800">Berhasil Disimpan</p>
                <p class="mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-100 border-2 border-black text-rose-950 flex items-start gap-3 shadow-md">
            <div class="w-8 h-8 rounded-xl bg-rose-400 border-2 border-black text-black flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-exclamation font-black"></i>
            </div>
            <div class="flex-1 text-sm">
                <p class="text-xs uppercase tracking-wider text-rose-800 font-black">Perhatian</p>
                <ul class="mt-1 list-disc list-inside text-xs font-bold space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Stats Cards (Neo-Brutalism) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total -->
        <a href="{{ route('guru.prestasi-siswa.index', ['status' => 'all']) }}" 
           class="p-5 rounded-3xl border-2 border-black bg-white shadow-md hover:translate-x-0.5 hover:-translate-y-0.5 transition flex items-center justify-between {{ $statusFilter === 'all' ? 'ring-4 ring-slate-900' : '' }}">
            <div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Total Prestasi</p>
                <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ $stats['total'] }}</h3>
                <p class="text-[10px] font-bold text-slate-600 mt-0.5">Seluruh Riwayat</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 border-2 border-black text-slate-900 flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-layer-group"></i>
            </div>
        </a>

        <!-- Menunggu Justifikasi -->
        <a href="{{ route('guru.prestasi-siswa.index', ['status' => 'pending']) }}" 
           class="p-5 rounded-3xl border-2 border-black bg-amber-50 shadow-md hover:translate-x-0.5 hover:-translate-y-0.5 transition flex items-center justify-between {{ $statusFilter === 'pending' ? 'ring-4 ring-amber-500' : '' }}">
            <div>
                <p class="text-[10px] font-black text-amber-800 uppercase tracking-wider">Menunggu Justifikasi</p>
                <h3 class="text-2xl font-black text-amber-950 mt-0.5">{{ $stats['pending'] }}</h3>
                <p class="text-[10px] font-black text-amber-700 mt-0.5">Perlu Ditinjau Segera</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-400 border-2 border-black text-black flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-hourglass-half animate-pulse"></i>
            </div>
        </a>

        <!-- Diakui (Verified) -->
        <a href="{{ route('guru.prestasi-siswa.index', ['status' => 'verified']) }}" 
           class="p-5 rounded-3xl border-2 border-black bg-emerald-50 shadow-md hover:translate-x-0.5 hover:-translate-y-0.5 transition flex items-center justify-between {{ $statusFilter === 'verified' ? 'ring-4 ring-emerald-500' : '' }}">
            <div>
                <p class="text-[10px] font-black text-emerald-800 uppercase tracking-wider">Prestasi Diakui</p>
                <h3 class="text-2xl font-black text-emerald-950 mt-0.5">{{ $stats['verified'] }}</h3>
                <p class="text-[10px] font-black text-emerald-700 mt-0.5">Poin Sah & Dipertahankan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-400 border-2 border-black text-black flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-check-double"></i>
            </div>
        </a>

        <!-- Ditolak / Tidak Diakui -->
        <a href="{{ route('guru.prestasi-siswa.index', ['status' => 'rejected']) }}" 
           class="p-5 rounded-3xl border-2 border-black bg-rose-50 shadow-md hover:translate-x-0.5 hover:-translate-y-0.5 transition flex items-center justify-between {{ $statusFilter === 'rejected' ? 'ring-4 ring-rose-500' : '' }}">
            <div>
                <p class="text-[10px] font-black text-rose-800 uppercase tracking-wider">Tidak Diakui</p>
                <h3 class="text-2xl font-black text-rose-950 mt-0.5">{{ $stats['rejected'] }}</h3>
                <p class="text-[10px] font-black text-rose-700 mt-0.5">Poin Ditarik Kembali</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-400 border-2 border-black text-black flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-ban"></i>
            </div>
        </a>
    </div>

    {{-- Filter Bar & Search --}}
    <div class="bg-white p-4 rounded-3xl border-2 border-black shadow-md">
        <form action="{{ route('guru.prestasi-siswa.index') }}" method="GET" class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Status Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
                <a href="{{ route('guru.prestasi-siswa.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
                   class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 border-black transition whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-amber-400 text-black shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Menunggu ({{ $stats['pending'] }})
                </a>
                <a href="{{ route('guru.prestasi-siswa.index', array_merge(request()->except('status', 'page'), ['status' => 'verified'])) }}"
                   class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 border-black transition whitespace-nowrap {{ $statusFilter === 'verified' ? 'bg-emerald-400 text-black shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Diakui ({{ $stats['verified'] }})
                </a>
                <a href="{{ route('guru.prestasi-siswa.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
                   class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 border-black transition whitespace-nowrap {{ $statusFilter === 'rejected' ? 'bg-rose-400 text-black shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Ditolak ({{ $stats['rejected'] }})
                </a>
                <a href="{{ route('guru.prestasi-siswa.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                   class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 border-black transition whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Semua ({{ $stats['total'] }})
                </a>
            </div>

            <!-- Search & Classroom Filter -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="status" value="{{ $statusFilter }}">

                @if($homeroomClassrooms->count() > 1)
                    <select name="classroom_id" onchange="this.form.submit()" class="px-3 py-2 rounded-2xl border-2 border-black text-xs font-black text-black bg-slate-50">
                        <option value="">Semua Kelas Asuhan</option>
                        @foreach($homeroomClassrooms as $cls)
                            <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                        @endforeach
                    </select>
                @endif

                <div class="relative flex-1 md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa / kejuaraan..."
                           class="w-full pl-9 pr-3 py-2 rounded-2xl border-2 border-black text-xs font-black text-black focus:outline-hidden focus:bg-amber-50 transition">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                </div>

                @if(request()->has('search') || request()->has('classroom_id'))
                    <a href="{{ route('guru.prestasi-siswa.index', ['status' => $statusFilter]) }}" class="px-3 py-2 rounded-2xl border-2 border-black bg-slate-200 text-black text-xs font-black hover:bg-slate-300">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Main List of Achievements --}}
    @if($achievements->count() > 0)
        <div class="space-y-4">
            @foreach($achievements as $ach)
                @php
                    $isPending = ($ach->status === 'pending');
                    $isVerified = ($ach->status === 'verified');
                    $isRejected = ($ach->status === 'rejected');

                    $cardBorder = match($ach->status) {
                        'verified' => 'border-emerald-500 bg-emerald-50/30',
                        'rejected' => 'border-rose-400 bg-rose-50/20 opacity-85',
                        default    => 'border-amber-400 bg-amber-50/30 ring-2 ring-amber-400/50',
                    };

                    $ext = $ach->certificate_file ? strtolower(pathinfo($ach->certificate_file, PATHINFO_EXTENSION)) : null;
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                    $isPdf = ($ext === 'pdf');
                @endphp

                <div class="bg-white rounded-3xl border-2 border-black p-5 md:p-6 shadow-md transition hover:shadow-lg {{ $cardBorder }}">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                        
                        <!-- Left: Student Info & Achievement Details -->
                        <div class="flex items-start gap-4 flex-1">
                            <!-- Avatar / Student Initial -->
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600 border-2 border-black text-white flex items-center justify-center font-black text-base shrink-0 shadow-xs">
                                {{ strtoupper(substr($ach->student->full_name ?? 'S', 0, 2)) }}
                            </div>

                            <div class="space-y-2 flex-1">
                                <!-- Student Name & Classroom -->
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-black text-base md:text-lg text-slate-900">
                                        {{ $ach->student->full_name ?? 'Siswa' }}
                                    </h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-slate-900 text-white border border-black">
                                        {{ $ach->student->currentClassroom->class_name ?? 'Kelas' }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-500 font-mono">
                                        NISN: {{ $ach->student->nisn ?? '-' }}
                                    </span>
                                </div>

                                <!-- Achievement Title -->
                                <h4 class="font-black text-slate-800 text-base flex items-center gap-2">
                                    <i class="fas fa-award text-amber-500"></i>
                                    {{ $ach->title }}
                                </h4>

                                <!-- Badges (Category, Level, Rank, Date) -->
                                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                    <span class="px-2.5 py-0.5 rounded-xl text-xs font-black bg-blue-100 text-blue-900 border border-blue-300">
                                        {{ $ach->type_label }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-xl text-xs font-black bg-purple-100 text-purple-900 border border-purple-300">
                                        Tingkat {{ $ach->level_label }}
                                    </span>
                                    @if($ach->rank)
                                        <span class="px-2.5 py-0.5 rounded-xl text-xs font-black bg-amber-100 text-amber-900 border border-amber-300">
                                            <i class="fas fa-crown text-[10px] mr-1"></i>{{ $ach->rank_label }}
                                        </span>
                                    @endif
                                    <span class="px-2.5 py-0.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        <i class="fas fa-calendar-alt text-[10px] mr-1"></i>{{ $ach->achievement_date ? $ach->achievement_date->format('d M Y') : '-' }}
                                    </span>
                                </div>

                                <!-- Description / Organizer -->
                                @if($ach->description)
                                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-xs font-medium text-slate-700 mt-2">
                                        <p class="font-bold text-slate-500 uppercase text-[10px] mb-0.5">Keterangan / Penyelenggara:</p>
                                        <p>{{ $ach->description }}</p>
                                    </div>
                                @endif

                                <!-- Verification Notes (if present) -->
                                @if($ach->verification_notes)
                                    <div class="p-3 rounded-2xl border text-xs font-bold mt-2 {{ $isRejected ? 'bg-rose-100 border-rose-300 text-rose-950' : 'bg-emerald-100 border-emerald-300 text-emerald-950' }}">
                                        <p class="text-[10px] uppercase font-black tracking-wider">
                                            Catatan Justifikasi (Oleh: {{ $ach->verifiedBy->name ?? 'Wali Kelas' }}):
                                        </p>
                                        <p class="mt-0.5 font-semibold">{{ $ach->verification_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Points, Proof Document & Justification Action Buttons -->
                        <div class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-end justify-between gap-4 shrink-0 border-t lg:border-t-0 pt-4 lg:pt-0 border-slate-200">
                            
                            <!-- Points & Status Pill -->
                            <div class="text-left lg:text-right space-y-1">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-2xl border-2 border-black font-black text-sm {{ $isRejected ? 'bg-slate-200 text-slate-600 line-through' : 'bg-amber-400 text-black shadow-xs' }}">
                                    <i class="fas fa-bolt text-xs"></i>
                                    <span>+{{ $ach->points }} Poin Reputasi</span>
                                </div>

                                <div>
                                    @if($isVerified)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-200 text-emerald-950 border border-black">
                                            <i class="fas fa-check-circle text-emerald-700"></i> Diakui
                                        </span>
                                    @elseif($isRejected)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-200 text-rose-950 border border-black">
                                            <i class="fas fa-times-circle text-rose-700"></i> Ditolak (Poin Ditarik)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-300 text-amber-950 border border-black animate-pulse">
                                            <i class="fas fa-hourglass-half text-amber-800"></i> Menunggu Justifikasi
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Document Proof Preview Button -->
                            @if($ach->certificate_file)
                                <div>
                                    <button type="button" 
                                            @click="openPreview('{{ asset('storage/' . $ach->certificate_file) }}', '{{ $isPdf ? 'pdf' : 'image' }}')"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-2xl bg-white border-2 border-black text-slate-900 font-black text-xs hover:bg-slate-100 active:scale-95 transition shadow-xs">
                                        <i class="fas {{ $isPdf ? 'fa-file-pdf text-red-600' : 'fa-image text-blue-600' }}"></i>
                                        <span>Lihat Dokumen Bukti</span>
                                    </button>
                                </div>
                            @else
                                <span class="text-[11px] font-bold text-slate-400 italic">Tanpa lampiran dokumen</span>
                            @endif

                            <!-- Action Buttons: AKUI vs TOLAK -->
                            <div class="flex flex-wrap items-center gap-2 pt-2">
                                @if($isPending)
                                    <!-- Tombol Akui (Hijau) -->
                                    <button type="button" 
                                            @click="openJustify({{ json_encode($ach) }}, 'approve')"
                                            class="px-4 py-2 rounded-2xl bg-emerald-400 hover:bg-emerald-500 text-black font-black text-xs border-2 border-black shadow-xs active:scale-95 transition cursor-pointer flex items-center gap-1.5">
                                        <i class="fas fa-check"></i>
                                        <span>Akui Prestasi</span>
                                    </button>

                                    <!-- Tombol Tolak / Tarik Poin (Merah) -->
                                    <button type="button" 
                                            @click="openJustify({{ json_encode($ach) }}, 'reject')"
                                            class="px-4 py-2 rounded-2xl bg-rose-400 hover:bg-rose-500 text-black font-black text-xs border-2 border-black shadow-xs active:scale-95 transition cursor-pointer flex items-center gap-1.5">
                                        <i class="fas fa-ban"></i>
                                        <span>Tolak & Tarik Poin</span>
                                    </button>
                                @else
                                    <!-- Tombol Ubah Justifikasi -->
                                    <button type="button" 
                                            @click="openJustify({{ json_encode($ach) }}, '{{ $isVerified ? 'reject' : 'approve' }}')"
                                            class="px-3 py-1.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-black text-xs border-2 border-black shadow-2xs active:scale-95 transition cursor-pointer flex items-center gap-1">
                                        <i class="fas fa-rotate"></i>
                                        <span>Ubah Status</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="pt-4">
                {{ $achievements->links() }}
            </div>
        </div>
    @else
        <div class="bg-white rounded-3xl border-2 border-black p-12 text-center shadow-md">
            <div class="w-20 h-20 bg-amber-300 border-2 border-black rounded-3xl flex items-center justify-center mx-auto mb-4 text-3xl shadow-sm">
                <i class="fas fa-trophy text-black"></i>
            </div>
            <h3 class="text-lg font-black text-slate-900 uppercase">Tidak Ada Data Prestasi Siswa</h3>
            <p class="text-xs font-bold text-slate-500 mt-1 max-w-md mx-auto">
                @if($statusFilter === 'pending')
                    Bagus! Tidak ada prestasi siswa yang menunggu justifikasi saat ini.
                @else
                    Belum ada data prestasi yang sesuai dengan filter pencarian ini.
                @endif
            </p>
        </div>
    @endif

    {{-- MODAL: Justifikasi (Akui / Tolak) --}}
    <div x-show="showJustifyModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs overflow-y-auto"
         @keydown.escape.window="showJustifyModal = false">
        
        <div class="bg-white rounded-3xl border-3 border-black shadow-2xl max-w-lg w-full overflow-hidden"
             @click.outside="showJustifyModal = false">
            
            <!-- Header Modal -->
            <div class="p-6 border-b-2 border-black" 
                 :class="justifyAction === 'approve' ? 'bg-emerald-400 text-black' : 'bg-rose-400 text-black'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white border-2 border-black flex items-center justify-center text-lg shadow-xs">
                            <i class="fas" :class="justifyAction === 'approve' ? 'fa-check-circle text-emerald-700' : 'fa-ban text-rose-700'"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black uppercase" x-text="justifyAction === 'approve' ? 'Konfirmasi Akui Prestasi' : 'Tolak & Tarik Poin Prestasi'"></h3>
                            <p class="text-xs font-bold opacity-90" x-text="justifyAction === 'approve' ? 'Poin prestasi siswa akan dipertahankan' : 'Poin yang telah dikreditkan akan ditarik kembali'"></p>
                        </div>
                    </div>
                    <button type="button" @click="showJustifyModal = false" class="w-8 h-8 rounded-full bg-black/10 hover:bg-black/20 text-black flex items-center justify-center font-black">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Body / Form -->
            <template x-if="selectedAchievement">
                <form :action="'{{ url('guru/prestasi-siswa') }}/' + selectedAchievement.id + '/justify'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="action" :value="justifyAction">

                    <!-- Detail Summary Box -->
                    <div class="p-4 rounded-2xl border-2 border-black bg-slate-50 space-y-1.5 text-xs">
                        <div class="flex justify-between font-bold">
                            <span class="text-slate-500">Nama Siswa:</span>
                            <span class="font-black text-slate-900" x-text="selectedAchievement.student ? selectedAchievement.student.full_name : '-'"></span>
                        </div>
                        <div class="flex justify-between font-bold">
                            <span class="text-slate-500">Nama Kejuaraan:</span>
                            <span class="font-black text-slate-900" x-text="selectedAchievement.title"></span>
                        </div>
                        <div class="flex justify-between font-bold">
                            <span class="text-slate-500">Poin Prestasi:</span>
                            <span class="font-black text-amber-700" x-text="'+' + (selectedAchievement.points || 0) + ' Pts'"></span>
                        </div>
                    </div>

                    <!-- Notice Alert when Rejecting -->
                    <div x-show="justifyAction === 'reject'" class="p-3.5 rounded-2xl bg-rose-100 border-2 border-rose-300 text-rose-950 text-xs font-bold">
                        <p class="font-black flex items-center gap-1.5">
                            <i class="fas fa-triangle-exclamation text-rose-600"></i>
                            Peringatan Penarikan Poin:
                        </p>
                        <p class="mt-1">
                            Sistem akan secara otomatis mencabut <strong x-text="(selectedAchievement.points || 0) + ' poin'"></strong> dari total reputasi siswa. Alasan penolakan wajib dicantumkan.
                        </p>
                    </div>

                    <!-- Notes / Reason Input -->
                    <div class="space-y-1">
                        <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                            <span x-text="justifyAction === 'approve' ? 'Catatan Tambahan (Opsional):' : 'Alasan Penolakan (Wajib Diisi):'"></span>
                        </label>
                        <textarea name="notes" x-model="justifyNotes" rows="3" 
                                  :required="justifyAction === 'reject'"
                                  :placeholder="justifyAction === 'approve' ? 'Contoh: Berkas sertifikat asli telah diperiksa dan valid.' : 'Contoh: Dokumen sertifikat tidak valid atau bukan atas nama siswa bersangkutan.'"
                                  class="w-full px-4 py-3 rounded-2xl border-2 border-black text-xs font-bold text-slate-900 focus:outline-hidden focus:bg-amber-50 transition resize-none"></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="pt-3 border-t-2 border-black flex items-center justify-end gap-3">
                        <button type="button" @click="showJustifyModal = false" class="px-5 py-2.5 rounded-2xl border-2 border-black bg-slate-200 hover:bg-slate-300 text-black font-black text-xs">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-2xl border-2 border-black font-black text-xs text-black shadow-xs active:scale-95 transition cursor-pointer"
                                :class="justifyAction === 'approve' ? 'bg-emerald-400 hover:bg-emerald-500' : 'bg-rose-400 hover:bg-rose-500'">
                            <span x-text="justifyAction === 'approve' ? 'Ya, Akui Prestasi' : 'Tolak & Tarik Poin'"></span>
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- MODAL: Pratinjau Dokumen Bukti (Image / PDF) --}}
    <div x-show="showPreviewModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto"
         @keydown.escape.window="showPreviewModal = false">
        
        <div class="bg-white rounded-3xl border-3 border-black shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col"
             @click.outside="showPreviewModal = false">
            
            <div class="p-4 bg-slate-900 text-white flex items-center justify-between border-b-2 border-black">
                <div class="flex items-center gap-2">
                    <i class="fas fa-file-shield text-amber-400"></i>
                    <h3 class="text-sm font-black uppercase">Dokumen Bukti Prestasi</h3>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="previewFileUrl" target="_blank" class="px-3 py-1 rounded-xl bg-amber-400 text-black text-xs font-black border border-black hover:bg-amber-300">
                        Buka di Tab Baru <i class="fas fa-external-link-alt text-[10px]"></i>
                    </a>
                    <button type="button" @click="showPreviewModal = false" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center font-black">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="p-4 overflow-y-auto flex-1 bg-slate-100 flex items-center justify-center min-h-[350px]">
                <template x-if="previewFileType === 'image'">
                    <img :src="previewFileUrl" alt="Bukti Prestasi" class="max-w-full max-h-[70vh] rounded-2xl border-2 border-black shadow-md object-contain">
                </template>

                <template x-if="previewFileType === 'pdf'">
                    <iframe :src="previewFileUrl" class="w-full h-[70vh] rounded-2xl border-2 border-black shadow-md"></iframe>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
