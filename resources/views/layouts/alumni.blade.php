{{--
    Alumni Layout — extends unified master layout
    Theme: Indigo/Blue
--}}
@extends('layouts.app', [
    'theme'       => 'indigo',
    'sidebarId'   => 'alumni-sidebar',
    'storageKey'  => 'alumni_sidebar_collapsed',
    'portalName'  => 'Portal Alumni',
    'portalSub'   => 'Rembuk Alumni Pembda',
    'portalIcon'  => 'fas fa-graduation-cap',
])

@section('sidebar-menu')
    @php
        $ac = 'bg-indigo-50 text-indigo-700 font-semibold active';
        $nc = 'text-gray-600 hover:bg-gray-50';
    @endphp

    <a href="{{ route('alumni.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('alumni.dashboard') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white shadow"><i class="fas fa-home text-xs"></i></div>
        <span class="text-sm flex-1 font-semibold">Dashboard</span>
    </a>

    <div class="pt-4 pb-2">
        <p class="px-4 text-xs font-bold tracking-wider text-gray-400 uppercase">Aktivitas</p>
    </div>

    <a href="{{ route('alumni.forum.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('alumni.forum.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white"><i class="fas fa-users text-[10px]"></i></div>
        <span>Forum Alumni</span>
    </a>

    <a href="{{ route('alumni.chat.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('alumni.chat.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-comment-dots text-[10px]"></i></div>
        <span>Pesan Pribadi</span>
    </a>

    <a href="{{ route('alumni.jobs.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('alumni.jobs.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white"><i class="fas fa-briefcase text-[10px]"></i></div>
        <span>Lowongan Kerja</span>
    </a>

    <a href="{{ route('alumni.kontribusi') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('alumni.kontribusi') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center text-white"><i class="fas fa-hand-holding-heart text-[10px]"></i></div>
        <span>Kontribusi & Donasi</span>
    </a>

    <a href="{{ route('alumni.tracer.form') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('alumni.tracer.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-poll-h text-[10px]"></i></div>
        <span>Tracer Study (BMW)</span>
    </a>

    <div class="pt-4 pb-2">
        <p class="px-4 text-xs font-bold tracking-wider text-gray-400 uppercase">Pengaturan</p>
    </div>

    <a href="{{ route('profile.settings') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('profile.settings') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white"><i class="fas fa-user text-[10px]"></i></div>
        <span>Profil Akun</span>
    </a>
@endsection
