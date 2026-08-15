@extends('mobile.layouts.app')

@section('title', 'Catatan Prestasi, Pembinaan & Perkembangan Siswa')

@section('content')
<div class="space-y-4 pb-12" x-data="{ activeTab: 'prestasi' }">
    <!-- Header Title -->
    <div class="flex items-center justify-between gap-2 px-1 pt-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-base font-black text-slate-900 leading-tight truncate">Catatan Siswa</h2>
                <p class="text-[10px] text-slate-500 font-bold truncate">Prestasi, Pembinaan & Perkembangan</p>
            </div>
        </div>

        <a href="{{ route('mobile.hall-of-fame') }}" class="px-3 py-1.5 rounded-xl bg-amber-50 border-2 border-amber-300 text-amber-900 text-xs font-black hover:bg-amber-100 active:scale-95 transition flex items-center gap-1.5 shadow-2xs shrink-0">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>Hall of Fame</span>
        </a>
    </div>

    <!-- Student Reputation & Stats Banner Clay Card -->
    <div class="clay-purple p-5 sm:p-6 space-y-3.5 rounded-3xl shadow-lg">
        <div class="flex items-center justify-between gap-3 px-1">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-7 h-7 rounded-xl bg-white/20 text-white flex items-center justify-center text-xs font-black shadow-2xs">
                    🎓
                </span>
                <div class="min-w-0">
                    <h3 class="text-xs font-black text-white leading-tight truncate">{{ $student->full_name }}</h3>
                    <p class="text-[9px] text-purple-100 font-bold truncate">{{ $student->school->name ?? 'Pembda' }} • NISN: {{ $student->nisn ?? '-' }}</p>
                </div>
            </div>
            <span class="text-[10px] font-black text-purple-900 bg-amber-300 px-3 py-1 rounded-full border border-amber-200 shadow-xs shrink-0">
                ⭐ {{ number_format($stats['reputation_points']) }} Pts
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2.5 pt-1 text-center">
            <button @click="activeTab = 'prestasi'" 
                    class="py-2.5 px-2 rounded-2xl transition duration-200"
                    :class="activeTab === 'prestasi' ? 'bg-white text-purple-950 shadow-md scale-102 font-black' : 'bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold'">
                <span class="text-lg font-black leading-none block">{{ $stats['total_prestasi'] }}</span>
                <span class="text-[9px] uppercase tracking-wider mt-1 block">🏆 Prestasi</span>
            </button>
            <button @click="activeTab = 'pembinaan'" 
                    class="py-2.5 px-2 rounded-2xl transition duration-200"
                    :class="activeTab === 'pembinaan' ? 'bg-white text-purple-950 shadow-md scale-102 font-black' : 'bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold'">
                <span class="text-lg font-black leading-none block">{{ $stats['total_pembinaan'] }}</span>
                <span class="text-[9px] uppercase tracking-wider mt-1 block">🛡️ Pembinaan</span>
            </button>
            <button @click="activeTab = 'perkembangan'" 
                    class="py-2.5 px-2 rounded-2xl transition duration-200"
                    :class="activeTab === 'perkembangan' ? 'bg-white text-purple-950 shadow-md scale-102 font-black' : 'bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold'">
                <span class="text-lg font-black leading-none block">{{ $stats['total_perkembangan'] }}</span>
                <span class="text-[9px] uppercase tracking-wider mt-1 block">📈 Observasi</span>
            </button>
        </div>
    </div>

    <!-- Navigation Segmented Tabs -->
    <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-200/80 rounded-2xl border border-slate-300 shadow-inner">
        <button @click="activeTab = 'prestasi'"
                :class="activeTab === 'prestasi' ? 'bg-amber-400 text-slate-950 font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900'"
                class="py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-award text-xs"></i>
            <span>Prestasi ({{ $stats['total_prestasi'] }})</span>
        </button>
        <button @click="activeTab = 'pembinaan'"
                :class="activeTab === 'pembinaan' ? 'bg-purple-600 text-white font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900'"
                class="py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-hand-holding-heart text-xs"></i>
            <span>Pembinaan ({{ $stats['total_pembinaan'] }})</span>
        </button>
        <button @click="activeTab = 'perkembangan'"
                :class="activeTab === 'perkembangan' ? 'bg-blue-600 text-white font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900'"
                class="py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-chart-line text-xs"></i>
            <span>Perkembangan ({{ $stats['total_perkembangan'] }})</span>
        </button>
    </div>

    <!-- TAB 1: PRESTASI SISWA -->
    <div x-show="activeTab === 'prestasi'" x-transition class="space-y-3.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-trophy text-amber-500"></i> Rekam Jejak Prestasi & Penghargaan
            </h3>
            <span class="text-[10px] font-bold text-slate-500">Total: {{ $achievements->count() }} Capaian</span>
        </div>

        @forelse($achievements as $ach)
            @php
                $levelColors = [
                    'international' => 'bg-rose-100 text-rose-800 border-rose-300',
                    'national' => 'bg-amber-100 text-amber-800 border-amber-300',
                    'province' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                    'city' => 'bg-blue-100 text-blue-800 border-blue-300',
                    'district' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'school' => 'bg-slate-100 text-slate-800 border-slate-300',
                ];
                $levelBadgeClass = $levelColors[$ach->level] ?? 'bg-slate-100 text-slate-800 border-slate-300';
            @endphp
            <div class="clay-card p-5 space-y-3 bg-white border-2 border-amber-200/80 rounded-3xl shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                            {{ $ach->rank === 'winner' ? '🥇' : ($ach->rank === 'runner_up' ? '🥈' : ($ach->rank === 'third_place' ? '🥉' : '🎖️')) }}
                        </div>
                        <div class="min-w-0">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border {{ $levelBadgeClass }}">
                                Tingkat {{ $ach->level_label }}
                            </span>
                            <h4 class="text-xs font-black text-slate-900 leading-snug mt-1 line-clamp-2">
                                {{ $ach->title }}
                            </h4>
                            <p class="text-[10px] font-bold text-slate-500 mt-0.5">
                                {{ $ach->rank_label }} • Bidang {{ $ach->type_label }}
                            </p>
                        </div>
                    </div>
                </div>

                @if($ach->description)
                    <div class="p-3 rounded-2xl bg-amber-50/60 border border-amber-200/80 text-[11px] text-slate-700 leading-relaxed">
                        {{ $ach->description }}
                    </div>
                @endif

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-bold text-slate-500">
                    <span class="flex items-center gap-1">
                        <i class="fa-regular fa-calendar text-slate-400"></i>
                        {{ $ach->achievement_date ? $ach->achievement_date->translatedFormat('d F Y') : '-' }}
                    </span>

                    @if($ach->certificate_file)
                        <a href="{{ asset('storage/' . $ach->certificate_file) }}" target="_blank" 
                           class="px-3 py-1.5 rounded-xl bg-amber-500 text-white text-[10px] font-black hover:bg-amber-600 active:scale-95 transition flex items-center gap-1 shadow-xs">
                            <i class="fa-solid fa-file-arrow-down"></i> Lihat Piagam
                        </a>
                    @else
                        <span class="text-[9px] font-extrabold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                            ⭐ Terverifikasi Resmi
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 space-y-3 bg-white rounded-3xl">
                <div class="w-16 h-16 rounded-3xl bg-amber-50 border-2 border-amber-200 text-amber-600 text-3xl flex items-center justify-center mx-auto shadow-inner">
                    🏆
                </div>
                <div>
                    <h4 class="text-sm font-black text-slate-800">Belum Ada Catatan Prestasi</h4>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Ayo raih prestasi akademik maupun non-akademik dan dapatkan sertifikat penghargaan dari sekolah!</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- TAB 2: CATATAN PEMBINAAN & BK -->
    <div x-show="activeTab === 'pembinaan'" x-transition class="space-y-3.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-hand-holding-heart text-purple-600"></i> Bimbingan Karakter & Konseling
            </h3>
            <span class="text-[10px] font-bold text-slate-500">Total: {{ $counselings->count() }} Sesi</span>
        </div>

        @forelse($counselings as $csl)
            @php
                $statusColors = [
                    'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'closed' => 'bg-slate-100 text-slate-800 border-slate-300',
                    'in_progress' => 'bg-blue-100 text-blue-800 border-blue-300',
                    'open' => 'bg-amber-100 text-amber-800 border-amber-300',
                ];
                $statusBadge = $statusColors[$csl->status] ?? 'bg-slate-100 text-slate-800 border-slate-300';
            @endphp
            <div class="clay-card p-5 space-y-3 bg-white border-2 border-purple-200/80 rounded-3xl shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg shadow-xs border border-purple-200 shrink-0">
                            🛡️
                        </div>
                        <div class="min-w-0">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border bg-purple-50 text-purple-800 border-purple-200">
                                {{ ucfirst($csl->record_type) }} • {{ ucfirst($csl->category ?? 'Karakter') }}
                            </span>
                            <h4 class="text-xs font-black text-slate-900 leading-snug mt-1">
                                {{ $csl->title }}
                            </h4>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">
                                Pembimbing: {{ $csl->counselor->name ?? 'Guru BK / Wali Kelas' }}
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl text-[9px] font-black uppercase border shrink-0 {{ $statusBadge }}">
                        {{ $csl->status === 'resolved' ? 'Selesai' : ($csl->status === 'in_progress' ? 'Berjalan' : 'Aktif') }}
                    </span>
                </div>

                @if($csl->description)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-[11px] text-slate-700 leading-relaxed space-y-1.5">
                        <p class="font-semibold text-slate-800">{{ $csl->description }}</p>
                        @if($csl->action_taken)
                            <p class="text-[10px] text-purple-900 font-bold bg-purple-50 p-2 rounded-xl border border-purple-200">
                                💡 <span class="font-black">Tindakan/Solusi:</span> {{ $csl->action_taken }}
                            </p>
                        @endif
                        @if($csl->follow_up)
                            <p class="text-[10px] text-blue-900 font-bold bg-blue-50 p-2 rounded-xl border border-blue-200">
                                🎯 <span class="font-black">Tindak Lanjut:</span> {{ $csl->follow_up }}
                            </p>
                        @endif
                    </div>
                @endif

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-bold text-slate-400">
                    <span class="flex items-center gap-1">
                        <i class="fa-regular fa-calendar"></i>
                        {{ $csl->incident_date ? $csl->incident_date->translatedFormat('d F Y') : '-' }}
                    </span>
                    <span class="text-slate-500">
                        📍 {{ $csl->location ?? 'Sekolah' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 space-y-3 bg-white rounded-3xl">
                <div class="w-16 h-16 rounded-3xl bg-purple-50 border-2 border-purple-200 text-purple-600 text-3xl flex items-center justify-center mx-auto shadow-inner">
                    🛡️
                </div>
                <div>
                    <h4 class="text-sm font-black text-slate-800">Catatan Pembinaan Bersih</h4>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Tidak ada catatan kasus atau pelanggaran kedisiplinan. Pertahankan karakter mulia dan keteladanan Anda!</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- TAB 3: CATATAN PERKEMBANGAN & OBSERVASI -->
    <div x-show="activeTab === 'perkembangan'" x-transition class="space-y-3.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-chart-line text-blue-600"></i> Observasi & Perkembangan Belajar
            </h3>
            <span class="text-[10px] font-bold text-slate-500">Total: {{ $developmentNotes->count() }} Catatan</span>
        </div>

        @forelse($developmentNotes as $note)
            @php
                $aspectIcons = [
                    'akademik' => 'fa-book-open text-blue-600',
                    'sikap' => 'fa-heart text-rose-600',
                    'keterampilan' => 'fa-screwdriver-wrench text-amber-600',
                    'spiritual' => 'fa-hands-praying text-emerald-600',
                    'sosial' => 'fa-users text-indigo-600',
                    'fisik' => 'fa-person-running text-orange-600',
                    'ekstrakurikuler' => 'fa-medal text-purple-600',
                ];
                $iconClass = $aspectIcons[$note->aspect] ?? 'fa-circle-info text-blue-600';
            @endphp
            <div class="clay-card p-5 space-y-3 bg-white border-2 border-blue-200/80 rounded-3xl shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base border border-blue-200 shrink-0">
                            <i class="fa-solid {{ $iconClass }}"></i>
                        </div>
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-blue-100 text-blue-900 border border-blue-200">
                                Aspek {{ ucfirst($note->aspect) }}
                            </span>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">
                                Dicatat oleh: {{ $note->notedByUser->name ?? 'Wali Kelas' }}
                            </p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400">
                        {{ $note->created_at ? $note->created_at->translatedFormat('d M Y') : '-' }}
                    </span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] font-black text-slate-500 uppercase block">🔍 Hasil Pengamatan / Observasi:</span>
                        <p class="text-slate-800 font-semibold leading-relaxed">{{ $note->observation }}</p>
                    </div>

                    @if($note->progress)
                        <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200/80 space-y-1">
                            <span class="text-[10px] font-black text-emerald-800 uppercase block">🚀 Kemajuan / Progres:</span>
                            <p class="text-emerald-950 font-bold leading-relaxed">{{ $note->progress }}</p>
                        </div>
                    @endif

                    @if($note->challenges)
                        <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200/80 space-y-1">
                            <span class="text-[10px] font-black text-amber-800 uppercase block">⚠️ Tantangan / Hambatan:</span>
                            <p class="text-amber-950 font-bold leading-relaxed">{{ $note->challenges }}</p>
                        </div>
                    @endif

                    @if($note->suggestion)
                        <div class="p-3 rounded-2xl bg-blue-50 border border-blue-200/80 space-y-1">
                            <span class="text-[10px] font-black text-blue-800 uppercase block">💡 Saran Pembinaan:</span>
                            <p class="text-blue-950 font-bold leading-relaxed">{{ $note->suggestion }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 space-y-3 bg-white rounded-3xl">
                <div class="w-16 h-16 rounded-3xl bg-blue-50 border-2 border-blue-200 text-blue-600 text-3xl flex items-center justify-center mx-auto shadow-inner">
                    📈
                </div>
                <div>
                    <h4 class="text-sm font-black text-slate-800">Belum Ada Catatan Perkembangan</h4>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Catatan perkembangan belajar dan karakter dari wali kelas akan tampil di sini.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
