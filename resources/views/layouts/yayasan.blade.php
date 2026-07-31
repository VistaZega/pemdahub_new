{{--
    Yayasan Layout — extends unified master layout
    Theme: Violet/Purple
--}}
@extends('layouts.app', [
    'theme'       => 'violet',
    'sidebarId'   => 'yayasan-sidebar',
    'storageKey'  => 'yayasan_sidebar_collapsed',
    'portalName'  => 'Ketua Yayasan',
    'portalSub'   => 'PembdaHUB Oversight',
    'portalIcon'  => 'fas fa-landmark',
])

@section('sidebar-menu')
    @php
        $ac = 'bg-violet-50 text-violet-700 font-semibold active';
        $nc = 'text-gray-600 hover:bg-gray-50';
    @endphp

    <!-- ════════════════ 1. GENERAL ════════════════ -->
    <div class="pt-2" data-menu-group="general">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-compass text-[10px]"></i> General</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <!-- Dashboard Utama -->
            <a href="{{ route('yayasan.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.dashboard') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-home text-[10px]"></i></div>
                <span>Dashboard Utama</span>
            </a>

            <!-- Progress Input Data -->
            <a href="{{ route('yayasan.progress-input') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.progress-input*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-tasks text-[10px]"></i></div>
                <span>Progress Input Data</span>
            </a>

            <!-- Kalender Pendidikan -->
            <a href="{{ route('yayasan.calendar.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.calendar.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-calendar-alt text-[10px]"></i></div>
                <span>Kalender Pendidikan</span>
            </a>

            <!-- Surat Digital -->
            <a href="{{ route('yayasan.letters.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.letters.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-700 flex items-center justify-center text-white shadow-sm"><i class="fas fa-file-signature text-[10px]"></i></div>
                <span>Surat Digital / Edaran</span>
            </a>
        </div>
    </div>

    <!-- ════════════════ 2. KEPEGAWAIAN DAN SDM ════════════════ -->
    <div class="pt-4" data-menu-group="kinerja">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-id-card-clip text-[10px]"></i> Kepegawaian dan SDM</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <a href="{{ route('admin.employees.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.employees.dashboard') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-chart-pie text-[10px]"></i></div>
                <span>Dashboard SDM</span>
            </a>
            <a href="{{ route('yayasan.performance_contracts.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.performance_contracts.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-400 to-red-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-file-signature text-[10px]"></i></div>
                <span>Validasi Perjanjian Kinerja</span>
            </a>
            <a href="{{ route('yayasan.performance_evaluations.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.performance_evaluations.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-star-half-alt text-[10px]"></i></div>
                <span>Evaluasi Kinerja</span>
            </a>
            <a href="{{ route('admin.employees.leaves.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.employees.leaves.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-400 to-sky-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-calendar-check text-[10px]"></i></div>
                <span>Cuti & Izin</span>
            </a>
            <a href="{{ route('admin.workload.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.workload.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-list-check text-[10px]"></i></div>
                <span>Rekap Beban Kerja dan Penggajian</span>
            </a>
            <a href="{{ route('admin.payroll.slip-search') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payroll.slip-search') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-file-invoice-dollar text-[10px]"></i></div>
                <span>Slip Gaji</span>
            </a>
            <a href="{{ route('admin.payroll.settings') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payroll.settings') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-sliders text-[10px]"></i></div>
                <span>Pengaturan Gaji</span>
            </a>
        </div>
    </div>

    <!-- ════════════════ 3. KEUANGAN ════════════════ -->
    <div class="pt-4" data-menu-group="finance">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-wallet text-[10px]"></i> Keuangan</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <a href="{{ route('admin.payment_reports.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payment_reports.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-chart-pie text-[10px]"></i></div>
                <span>Laporan Rekap Tagihan</span>
            </a>
            <a href="{{ route('yayasan.operational_expenses.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.operational_expenses.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-orange-700 flex items-center justify-center text-white shadow-sm"><i class="fas fa-list-check text-[10px]"></i></div>
                <span>Rencana Belanja Operasional</span>
            </a>
            <a href="{{ route('yayasan.financial_recap.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.financial_recap.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-600 to-purple-800 flex items-center justify-center text-white shadow-sm"><i class="fas fa-chart-line text-[10px]"></i></div>
                <span>Rekapitulasi Keuangan Yayasan</span>
            </a>
            <a href="{{ route('yayasan.contribution_balance.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.contribution_balance.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white shadow-sm"><i class="fas fa-school text-[10px]"></i></div>
                <span>Kontribusi Unit Sekolah</span>
            </a>
        </div>
    </div>

    <!-- ════════════════ 4. PEMBDA ELITE ════════════════ -->
    <div class="pt-4" data-menu-group="elite">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-trophy text-[10px]"></i> Pembda Elite</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <a href="{{ route('training.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('training.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-400 to-cyan-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-book-reader text-[10px]"></i></div>
                <span>Pelatihan PembdaHUB</span>
            </a>
            <a href="{{ route('forum.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('forum.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-comments text-[10px]"></i></div>
                <span>Pembda Space</span>
            </a>
            <a href="{{ route('reputation.leaderboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('reputation.leaderboard') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-ranking-star text-[10px]"></i></div>
                <span>Hall of Fame</span>
            </a>
        </div>
    </div>
@endsection
