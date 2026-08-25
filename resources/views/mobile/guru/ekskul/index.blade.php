@extends('layouts.mobile')

@section('title', 'Manajemen Ekstrakurikuler')

@section('content')
<div class="space-y-4 pb-20">

    {{-- Top Bar --}}
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center text-slate-700 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="text-center">
            <h1 class="text-sm font-black text-slate-900 leading-tight">Manajemen Ekskul</h1>
            <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider">Unit Kegiatan Siswa</p>
        </div>
        <a href="{{ route('mobile.space.index') }}" class="w-10 h-10 rounded-2xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-sm active:scale-95 transition" title="Pembda Space">
            <i class="fa-solid fa-comments"></i>
        </a>
    </div>

    {{-- Hero Card --}}
    <div class="bg-gradient-to-br from-indigo-900 via-purple-900 to-slate-950 p-5 rounded-3xl text-white relative overflow-hidden shadow-lg border border-indigo-800/40">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-purple-500/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 space-y-3">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 bg-white/20 backdrop-blur-xs rounded-full text-[10px] font-black tracking-wider uppercase flex items-center gap-1">
                    <span>🎨</span> Pembinaan Karakter
                </span>
                @if($pendingClaimsCount > 0)
                <span class="px-2.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-black animate-pulse">
                    {{ $pendingClaimsCount }} Klaim Menunggu
                </span>
                @else
                <span class="text-[10px] font-bold text-emerald-300 flex items-center gap-1">
                    <i class="fa-solid fa-check-circle"></i> Seluruh Klaim Disetujui
                </span>
                @endif
            </div>

            <div class="space-y-1">
                <h2 class="text-lg font-black text-white leading-snug">Unit Kegiatan & Ekskul</h2>
                <p class="text-[11px] text-slate-200 leading-relaxed">
                    Kelola keanggotaan siswa, setujui klaim mandiri (+15 Poin), dan pantau catatan latihan rutin.
                </p>
            </div>

            {{-- Stat Pills --}}
            <div class="grid grid-cols-2 gap-2 pt-1">
                <div class="bg-white/10 rounded-2xl p-2.5 border border-white/10 text-center">
                    <p class="text-[10px] text-slate-300 font-bold uppercase">Total Unit</p>
                    <p class="text-base font-black text-white">{{ $ekskuls->count() }} <span class="text-[10px] font-normal text-purple-200">Ekskul</span></p>
                </div>
                <div class="bg-white/10 rounded-2xl p-2.5 border border-white/10 text-center">
                    <p class="text-[10px] text-slate-300 font-bold uppercase">Total Anggota</p>
                    <p class="text-base font-black text-amber-300">{{ $ekskuls->sum('active_members_count') }} <span class="text-[10px] font-normal text-purple-200">Siswa Aktif</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Unit Ekskul --}}
    <div class="space-y-3">
        <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-layer-group text-indigo-600"></i> Unit Ekstrakurikuler Aktif
        </h2>

        <div class="space-y-3">
            @forelse($ekskuls as $ekskul)
            <a href="{{ route('mobile.guru.ekskul.show', $ekskul) }}" class="block bg-white p-4.5 rounded-3xl border {{ $ekskul->isFoundationLevel() ? 'border-amber-300 ring-2 ring-amber-50' : 'border-slate-200/80' }} shadow-xs active:scale-[0.98] transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-2xl shadow-2xs flex-shrink-0">
                            {{ $ekskul->display_icon }}
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-1">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-700">
                                    {{ $ekskul->category_label }}
                                </span>
                                @if($ekskul->isFoundationLevel())
                                <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                    🏛️ Lintas Yayasan
                                </span>
                                @endif
                            </div>
                            <h3 class="font-black text-slate-900 text-xs mt-1 leading-snug">{{ $ekskul->name }}</h3>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-xs mt-2"></i>
                </div>

                <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 mt-3 space-y-1 text-[11px] text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">👨‍🏫 Pembina / Manager:</span>
                        <span class="font-bold text-slate-800 truncate max-w-[150px]">{{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: 'PKS Kesiswaan') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">👥 Anggota:</span>
                        <span class="font-bold text-indigo-600">{{ $ekskul->active_members_count }} Siswa Aktif</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="p-8 bg-white rounded-3xl border border-dashed border-slate-300 text-center space-y-2">
                <p class="text-3xl">🎨</p>
                <p class="font-bold text-slate-800 text-xs">Belum Ada Unit Ekstrakurikuler</p>
                <p class="text-[11px] text-slate-500">Unit ekstrakurikuler dapat dibuat melalui portal web admin.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
