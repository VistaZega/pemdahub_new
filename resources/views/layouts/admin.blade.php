{{--
    Admin Layout — extends unified master layout
    Theme: Indigo/Purple
--}}
@extends('layouts.app', [
    'theme'       => 'indigo',
    'sidebarId'   => 'admin-sidebar',
    'storageKey'  => 'admin_sidebar_collapsed',
    'portalName'  => 'PembdaHUB',
    'portalSub'   => auth()->user()?->isSuperAdmin() ? 'Super Admin Panel' : 'Admin Sekolah',
    'portalIcon'  => 'fas fa-graduation-cap',
])

@section('sidebar-menu')
    @include('layouts.partials.admin_sidebar')
@endsection
