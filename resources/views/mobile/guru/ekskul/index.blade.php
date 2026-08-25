@extends('mobile.layouts.app')

@section('title', 'Manajemen Ekstrakurikuler')

@section('content')
<div class="space-y-4 pb-20">

    {{-- Top Bar --}}
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-700 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="text-center">
            <h1 class="text-sm font-black text-slate-900 leading-tight">Manajemen Ekskul</h1>
            <p class="text-[10px] text-indigo-700 font-bold uppercase tracking-wider">Unit Kegiatan Siswa</p>
        </div>
        <a href="{{ route('mobile.space.index') }}" class="w-10 h-10 rounded-2xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-sm active:scale-95 transition" title="Pembda Space">
            <i class="fa-solid fa-comments"></i>
        </a>
    </div>

    {{-- Hero Card (Clean Light Theme UI/UX Pro Max) --}}
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <span class="px-2.5 py-1 bg-indigo-100 text-indigo-900 border border-indigo-200 rounded-full text-[10px] font-black tracking-wider uppercase flex items-center gap-1">
                <span>🎨</span> Pembinaan Karakter
            </span>
            @if($pendingClaimsCount > 0)
            <span class="px-2.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-black animate-pulse">
                {{ $pendingClaimsCount }} Klaim Menunggu
            </span>
            @else
            <span class="text-[10px] font-bold text-emerald-700 flex items-center gap-1">
                <i class="fa-solid fa-check-circle"></i> Seluruh Klaim Disetujui
            </span>
            @endif
        </div>

        <div class="space-y-1">
            <h2 class="text-base font-black text-slate-900 leading-snug">Unit Kegiatan & Ekskul</h2>
            <p class="text-xs text-slate-600 leading-relaxed font-medium">
                Kelola keanggotaan siswa, setujui klaim mandiri (+15 Poin), dan pantau catatan latihan rutin.
            </p>
        </div>

        {{-- Stat Pills --}}
        <div class="grid grid-cols-2 gap-2 pt-1">
            <div class="bg-indigo-50/70 rounded-2xl p-2.5 border border-indigo-100 text-center">
                <p class="text-[10px] text-indigo-700 font-black uppercase">Total Unit</p>
                <p class="text-base font-black text-slate-900">{{ $ekskuls->count() }} <span class="text-[10px] font-bold text-slate-500">Ekskul</span></p>
            </div>
            <div class="bg-purple-50/70 rounded-2xl p-2.5 border border-purple-100 text-center">
                <p class="text-[10px] text-purple-700 font-black uppercase">Total Anggota</p>
                <p class="text-base font-black text-purple-900">{{ $ekskuls->sum('active_members_count') }} <span class="text-[10px] font-bold text-slate-500">Siswa Aktif</span></p>
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
            <a href="{{ route('mobile.guru.ekskul.show', $ekskul) }}" class="block bg-white p-4.5 rounded-3xl border-2 {{ $ekskul->isFoundationLevel() ? 'border-amber-300 ring-2 ring-amber-50' : 'border-slate-200' }} shadow-xs active:scale-[0.98] transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-2xl shadow-2xs flex-shrink-0">
                            {{ $ekskul->display_icon }}
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-1">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-800">
                                    {{ $ekskul->category_label }}
                                </span>
                                @if($ekskul->isFoundationLevel())
                                <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                    🏛️ Lintas Yayasan
                                </span>
                                @endif
                            </div>
                            <h3 class="font-black text-slate-900 text-xs mt-1 leading-snug">{{ $ekskul->name }}</h3>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-xs mt-2"></i>
                </div>

                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200/80 mt-3 space-y-1 text-[11px] text-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-bold">👨‍🏫 Pembina:</span>
                        <span class="font-black text-slate-900 truncate max-w-[150px]">{{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: 'PKS Kesiswaan') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-bold">👥 Anggota:</span>
                        <span class="font-black text-indigo-700">{{ $ekskul->active_members_count }} Siswa Aktif</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="p-8 bg-white rounded-3xl border-2 border-dashed border-slate-200 text-center text-xs text-slate-400 font-medium">
                Belum ada unit ekstrakurikuler yang terdaftar di sekolah Anda.
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
