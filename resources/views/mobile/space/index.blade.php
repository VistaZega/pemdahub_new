@extends('mobile.layouts.app')

@section('title', 'Pembda Space Groups - WA Groups Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title & Create Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Pembda Space Groups 💬</h2>
            <p class="text-[11px] text-slate-500 font-bold">Grup Diskusi & Komunitas Sekolah Real-time</p>
        </div>
        <a href="{{ route('mobile.space.create') }}" 
           class="clay-btn px-4 py-2.5 text-white text-xs font-black flex items-center gap-1.5 shadow-md">
            <i class="fa-solid fa-plus text-xs"></i> Post Baru
        </a>
    </div>

    <!-- Search Input (Clay Search Box) -->
    <form action="{{ route('mobile.space.index') }}" method="GET" class="relative">
        @if($groupFilter)<input type="hidden" name="filter" value="{{ $groupFilter }}">@endif
        <div class="relative">
            <input type="text" name="search" value="{{ $search ?? '' }}" 
                   placeholder="Cari nama grup obrolan..." 
                   class="w-full pl-10 pr-4 py-3 bg-white border-2 border-slate-200/90 rounded-2xl text-slate-900 text-xs placeholder-slate-400 font-bold focus:outline-none focus:border-purple-600 shadow-sm transition">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
        </div>
    </form>

    <!-- Group Filter Pills (Clay Pills) -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('mobile.space.index', ['filter' => 'semua']) }}" 
           class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $groupFilter === 'semua' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
            🔥 Semua Grup ({{ count($groups) }})
        </a>
        <a href="{{ route('mobile.space.index', ['filter' => 'kelas']) }}" 
           class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $groupFilter === 'kelas' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
            🏫 Kelas Saya
        </a>
        <a href="{{ route('mobile.space.index', ['filter' => 'lobi']) }}" 
           class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $groupFilter === 'lobi' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
            📢 Lobi & Pengumuman
        </a>
    </div>

    <!-- GROUPS FEED (WA GROUPS STYLE CARDS) -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Grup Obrolan Aktif</h3>

        @forelse($groups as $grp)
            @php
                $latest = $grp->latestThread;
                $memberCount = $grp->members_count ?? count($grp->members ?? []);
            @endphp
            <a href="{{ route('mobile.space.group.show', $grp->id) }}" 
               class="clay-card p-4.5 block transition relative hover:border-purple-300 space-y-2.5 bg-white border-2 border-slate-200">
                
                <div class="flex items-center space-x-3">
                    <!-- Group Icon Avatar -->
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-xl font-black shadow-md border-2 border-white shrink-0">
                        {{ $grp->icon ?? '💬' }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black text-slate-900 leading-snug truncate">{{ $grp->name }}</h4>
                            <span class="text-[9px] text-slate-400 font-bold shrink-0">
                                {{ $latest?->created_at ? $latest->created_at->diffForHumans() : $grp->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="text-[11px] text-slate-600 truncate font-medium mt-0.5">
                            @if($latest)
                                <strong class="text-purple-700">{{ $latest->user->name ?? 'User' }}:</strong> {{ strip_tags($latest->content) }}
                            @else
                                <span class="italic text-slate-400">Belum ada obrolan baru</span>
                            @endif
                        </p>

                        <div class="flex items-center space-x-2 text-[9px] font-bold text-slate-400 mt-1">
                            <span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 font-black">
                                👥 {{ $memberCount }} Anggota
                            </span>
                            @if($grp->only_admin_can_post)
                                <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-black">
                                    📢 Pengumuman Guru
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">💬</div>
                <p>Belum ada grup obrolan terdaftar saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
