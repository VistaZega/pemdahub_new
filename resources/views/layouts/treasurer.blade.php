{{--
    Treasurer Layout — extends unified master layout
    Theme: Emerald/Green (Finance-focused) or Indigo if active role is Super Admin
--}}
@php
    $isSuperAdminView = session('active_role') === 'superadmin' || (auth()->user()?->isSuperAdmin() && session('active_role') !== 'bendahara');
@endphp

@extends('layouts.app', [
    'theme'       => $isSuperAdminView ? 'indigo' : 'emerald',
    'sidebarId'   => $isSuperAdminView ? 'admin-sidebar' : 'treasurer-sidebar',
    'storageKey'  => $isSuperAdminView ? 'admin_sidebar_collapsed' : 'treasurer_sidebar_collapsed',
    'portalName'  => $isSuperAdminView ? 'PembdaHUB' : 'Bendahara',
    'portalSub'   => $isSuperAdminView ? 'Super Admin Panel' : 'PembdaHUB Finance',
    'portalIcon'  => $isSuperAdminView ? 'fas fa-graduation-cap' : 'fas fa-wallet',
])

@section('sidebar-menu')
    @if($isSuperAdminView)
        @include('layouts.partials.admin_sidebar')
    @else
        @php
            $ac = 'bg-emerald-50 text-emerald-700 font-semibold active';
            $nc = 'text-gray-600 hover:bg-gray-50';
        @endphp

        <!-- Dashboard -->
        <a href="{{ route('treasurer.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('treasurer.dashboard') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-green-600 flex items-center justify-center text-white shadow"><i class="fas fa-home text-xs"></i></div>
            <span class="text-sm flex-1">Dashboard</span>
        </a>

        <!-- ════════════════ GROUP: TAGIHAN ════════════════ -->
        <div class="pt-4" data-menu-group="bills">
            <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
                <span class="flex items-center gap-2"><i class="fas fa-file-invoice text-[10px]"></i> Tagihan</span>
                <i class="fas fa-chevron-right text-[9px] chevron"></i>
            </button>
            <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
                <a href="{{ route('treasurer.bills.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.bills.index') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-list text-[10px]"></i></div>
                    <span>Daftar Tagihan</span>
                </a>
                <a href="{{ route('treasurer.bills.create') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.bills.create') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white"><i class="fas fa-plus-circle text-[10px]"></i></div>
                    <span>Buat Tagihan</span>
                </a>
                <a href="{{ route('treasurer.bills.bulk-create') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.bills.bulk-create') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-layer-group text-[10px]"></i></div>
                    <span>Tagihan Massal</span>
                </a>
            </div>
        </div>

        <!-- ════════════════ GROUP: PEMBAYARAN ════════════════ -->
        <div class="pt-3" data-menu-group="payments">
            <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
                <span class="flex items-center gap-2"><i class="fas fa-money-bill-wave text-[10px]"></i> Pembayaran</span>
                <i class="fas fa-chevron-right text-[9px] chevron"></i>
            </button>
            <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
                <a href="{{ route('treasurer.payments.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.payments.index') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-list-check text-[10px]"></i></div>
                    <span>Daftar Pembayaran</span>
                </a>
                <a href="{{ route('treasurer.payments.create') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.payments.create') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white"><i class="fas fa-cash-register text-[10px]"></i></div>
                    <span>Input Pembayaran</span>
                </a>
                <a href="{{ route('treasurer.payments.bulk-create') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.payments.bulk-create') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-coins text-[10px]"></i></div>
                    <span>Pembayaran Massal</span>
                </a>
            </div>
        </div>

        <!-- ════════════════ GROUP: LAPORAN ════════════════ -->
        <div class="pt-3" data-menu-group="reports">
            <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
                <span class="flex items-center gap-2"><i class="fas fa-chart-bar text-[10px]"></i> Laporan</span>
                <i class="fas fa-chevron-right text-[9px] chevron"></i>
            </button>
            <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
                <a href="{{ route('admin.payment_reports.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payment_reports.*') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-chart-pie text-[10px]"></i></div>
                    <span>Laporan Rekap Tagihan</span>
                </a>
                <a href="{{ route('treasurer.reports.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.reports.*') && !request()->routeIs('treasurer.consolidation.index') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-chart-line text-[10px]"></i></div>
                    <span>Progress Pembayaran</span>
                </a>
                <a href="{{ route('treasurer.consolidation.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.consolidation.index') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-400 to-red-600 flex items-center justify-center text-white"><i class="fas fa-file-invoice-dollar text-[10px]"></i></div>
                    <span>Laporan Konsolidasi Yayasan</span>
                </a>
            </div>
        </div>

        <!-- ════════════════ GROUP: KEPEGAWAIAN ════════════════ -->
        <div class="pt-3" data-menu-group="assignments">
            <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
                <span class="flex items-center gap-2"><i class="fas fa-id-card text-[10px]"></i> Kepegawaian</span>
                <i class="fas fa-chevron-right text-[9px] chevron"></i>
            </button>
            <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
                <a href="{{ route('treasurer.salary-report') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.salary-report') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-pink-400 to-pink-600 flex items-center justify-center text-white"><i class="fas fa-coins text-[10px]"></i></div>
                    <span>Rekap Beban Kerja dan Penggajian</span>
                </a>
                <a href="{{ route('treasurer.payroll.slip-search') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.payroll.slip-search') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-receipt text-[10px]"></i></div>
                    <span>Slip Gaji</span>
                </a>
                <a href="{{ route('treasurer.payroll.settings') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('treasurer.payroll.settings') ? $ac : $nc }}">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white"><i class="fas fa-sliders-h text-[10px]"></i></div>
                    <span>Pengaturan Gaji</span>
                </a>
            </div>
        </div>
    @endif
@endsection
