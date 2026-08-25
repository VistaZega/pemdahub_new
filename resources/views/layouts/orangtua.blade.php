{{--
    Orang Tua Layout — extends unified master layout
    Theme: Blue (Parent Portal)
--}}
@extends('layouts.app', [
    'theme'       => 'blue',
    'sidebarId'   => 'ortu-sidebar',
    'storageKey'  => 'ortu_sidebar_collapsed',
    'portalName'  => 'Portal Orang Tua',
    'portalSub'   => 'PembdaHUB Education System',
    'portalIcon'  => 'fas fa-people-roof',
])

@section('sidebar-menu')
    @php
        $ac = 'bg-blue-50 text-blue-700 font-semibold active';
        $nc = 'text-gray-600 hover:bg-gray-50';
    @endphp

    <a href="{{ route('orangtua.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('orangtua.dashboard') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-cyan-600 flex items-center justify-center text-white shadow"><i class="fas fa-home text-xs"></i></div>
        <span class="text-sm flex-1">Dashboard</span>
    </a>

    @if(isset($children) && $children->count() > 0)
        @foreach($children as $child)
            <div class="pt-3" data-menu-group="child_{{ $child->id }}">
                <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
                    <span class="flex items-center gap-2"><i class="fas fa-user text-[10px]"></i> {{ $child->full_name }}</span>
                    <i class="fas fa-chevron-right text-[9px] chevron"></i>
                </button>
                <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
                    <a href="{{ route('orangtua.anak.nilai', $child->id) }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('orangtua.anak.nilai') && request()->route('student') == $child->id ? $ac : $nc }}">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white"><i class="fas fa-chart-bar text-[10px]"></i></div>
                        <span>Nilai</span>
                    </a>
                    <a href="{{ route('orangtua.anak.dna', $child->id) }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('orangtua.anak.dna') && request()->route('student') == $child->id ? $ac : $nc }}">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-fuchsia-500 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-dna text-[10px]"></i></div>
                        <span class="font-bold text-indigo-950">DNA Potensi 360°</span>
                    </a>
                    <a href="{{ route('orangtua.anak.tagihan', $child->id) }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('orangtua.anak.tagihan') && request()->route('student') == $child->id ? $ac : $nc }}">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white"><i class="fas fa-file-invoice-dollar text-[10px]"></i></div>
                        <span>Tagihan</span>
                    </a>
                    @if(\App\Models\Setting::getValue('siswa_view_attendance_recap', true))
                    <a href="{{ route('orangtua.anak.absensi', $child->id) }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('orangtua.anak.absensi') && request()->route('student') == $child->id ? $ac : $nc }}">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-clipboard-check text-[10px]"></i></div>
                        <span>Absensi</span>
                    </a>
                    @endif
                    <a href="{{ route('orangtua.anak.jadwal', $child->id) }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('orangtua.anak.jadwal') && request()->route('student') == $child->id ? $ac : $nc }}">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-calendar-alt text-[10px]"></i></div>
                        <span>Jadwal</span>
                    </a>
                    <a href="{{ route('orangtua.anak.konseling', $child->id) }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('orangtua.anak.konseling') && request()->route('student') == $child->id ? $ac : $nc }}">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-pink-400 to-rose-600 flex items-center justify-center text-white"><i class="fas fa-heart text-[10px]"></i></div>
                        <span>Konseling</span>
                    </a>
                </div>
            </div>
        @endforeach
    @endif

    <div class="pt-4 border-t border-gray-150 mt-3 space-y-1">
        <a href="{{ route('training.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('training.*') ? $ac : $nc }}">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-400 to-cyan-600 flex items-center justify-center text-white"><i class="fas fa-book-reader text-[10px]"></i></div>
            <span>Pelatihan PembdaHUB</span>
        </a>

        <a href="{{ route('forum.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('forum.*') ? $ac : $nc }}">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-comments text-[10px]"></i></div>
            <span>Pembda Space</span>
        </a>

        @if(\App\Models\Setting::getValue('siswa_view_reputation_leaderboard', true))
        <a href="{{ route('reputation.leaderboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('reputation.leaderboard') ? $ac : $nc }}">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white"><i class="fas fa-ranking-star text-[10px]"></i></div>
            <span>Hall of Fame</span>
        </a>
        @endif
    </div>
@endsection
