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
    @php
        $user = auth()->user();
        $activeRole = session('active_role');
        
        $isYayasan = $user ? ($user->isKetuaYayasan() || $user->hasRole('ketua_yayasan')) : false;
        $isSA = $user ? $user->isSuperAdmin() : false;
        
        // Prioritaskan session active_role jika di-set secara eksplisit
        if ($activeRole === 'superadmin') {
            $isYayasan = false;
            $isSA = true;
        } elseif ($activeRole === 'ketua_yayasan') {
            $isYayasan = true;
            $isSA = false;
        } else {
            // Default: Yayasan override Super Admin untuk sidebar ini
            $isSA = $isSA && !$isYayasan;
        }

        $isAdmin = $user ? ($user->isAdminSekolah() && !$isYayasan) : false;
        $isKepsek = $user ? ($user->isKepalaSekolah() && !$isYayasan) : false;
        $isFinance = $user ? ($user->isBendahara() && !$isYayasan) : false;
        $isPanitiaCbt = $user ? $user->isPanitiaCbt() : false;
        $isPanitiaPkl = $user ? $user->isPanitiaPkl() : false;
        $isPanitiaProyek = $user ? $user->isPanitiaProyek() : false;
        $isPksOrPiket = $user ? $user->isPksOrPiket() : false;
        $canAccess = $isSA || $isAdmin || $isFinance || $isYayasan || $isKepsek;
        $isOnlyCommittee = !$canAccess && ($isPanitiaCbt || $isPanitiaPkl || $isPanitiaProyek || $isPksOrPiket);
        $ac = 'bg-indigo-50 text-indigo-700 font-semibold active'; // active class
        $nc = 'text-gray-600 hover:bg-gray-50'; // normal class
        
        // Cek tipe sekolah untuk kustomisasi menu
        $schoolType = $user->school ? strtoupper($user->school->type) : 'ALL';
        $isSMA = $schoolType === 'SMA';
        $isSMK = $schoolType === 'SMK';
        $isSMP = $schoolType === 'SMP';
    @endphp

    @if($isYayasan)
    <!-- ════════════════ SIDEBAR EKSKLUSIF YAYASAN ════════════════ -->
    <!-- 1. GENERAL -->
    <div class="pt-2" data-menu-group="general">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-compass text-[10px]"></i> General</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <a href="{{ route('yayasan.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.dashboard') || request()->routeIs('admin.dashboard') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-home text-[10px]"></i></div>
                <span>Dashboard Utama</span>
            </a>
            <a href="{{ route('yayasan.progress-input') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.progress-input*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-tasks text-[10px]"></i></div>
                <span>Progress Input Data</span>
            </a>
            <a href="{{ route('yayasan.calendar.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.calendar.*') || request()->routeIs('admin.calendar.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-calendar-alt text-[10px]"></i></div>
                <span>Kalender Pendidikan</span>
            </a>
            <a href="{{ route('yayasan.letters.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.letters.*') || request()->routeIs('admin.letters.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-700 flex items-center justify-center text-white shadow-sm"><i class="fas fa-file-signature text-[10px]"></i></div>
                <span>Surat Digital / Edaran</span>
            </a>
        </div>
    </div>

    <!-- 2. KEPEGAWAIAN DAN SDM -->
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
            <a href="{{ route('yayasan.performance_contracts.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.performance_contracts.*') || request()->routeIs('admin.performance_contracts.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-400 to-red-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-file-signature text-[10px]"></i></div>
                <span>Validasi Perjanjian Kinerja</span>
            </a>
            <a href="{{ route('yayasan.performance_evaluations.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.performance_evaluations.*') || request()->routeIs('admin.performance_evaluations.*') ? $ac : $nc }}">
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
            <a href="{{ route('admin.operational-expenses.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.operational-expenses.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-wallet text-[10px]"></i></div>
                <span>Pengeluaran Operasional</span>
            </a>
        </div>
    </div>

    <!-- 3. KEUANGAN -->
    <div class="pt-4" data-menu-group="finance">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-wallet text-[10px]"></i> Keuangan</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <a href="{{ route('admin.operational-expenses.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.operational-expenses.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-sm"><i class="fas fa-wallet text-[10px]"></i></div>
                <span>Pengeluaran Operasional</span>
            </a>
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

    <!-- 4. PEMBDA ELITE -->
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
    @else
    <!-- ── Dashboard ── -->
    <a href="{{ route('admin.dashboard') }}"
       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.dashboard') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow">
            <i class="fas fa-tachometer-alt text-xs"></i>
        </div>
        <span class="text-sm flex-1 font-semibold">Dashboard</span>
    </a>

    <!-- ── Surat Edaran Yayasan ── -->
    <a href="{{ route('admin.letters.index') }}"
       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.letters.*') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-700 flex items-center justify-center text-white shadow">
            <i class="fas fa-file-signature text-xs"></i>
        </div>
        <span class="text-sm flex-1 font-semibold">Surat Edaran Yayasan</span>
    </a>
    @endif

    @if(!$isYayasan)
    @if($isOnlyCommittee)
    <!-- tombol kembali ke portal guru -->
    <a href="{{ route('guru.dashboard') }}"
       class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 mt-2 border border-emerald-200">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white shadow">
            <i class="fas fa-arrow-left text-xs"></i>
        </div>
        <span class="text-sm flex-1 font-semibold">Kembali ke Portal Guru</span>
    </a>
    @endif

    @if(!$isOnlyCommittee)
    <!-- ════════════════ GROUP: DATA MASTER ════════════════ -->
    <div class="pt-4" data-menu-group="master">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-database text-[10px]"></i> Data Master</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            @if($isSA)
            <a href="{{ route('admin.schools.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.schools.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-school text-[10px]"></i></div>
                <span>Kelola Sekolah</span>
            </a>
            @elseif($isAdmin && $user->school_id)
            <a href="{{ route('admin.schools.edit', $user->school_id) }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.schools.edit') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-school text-[10px]"></i></div>
                <span>Kelola Sekolah</span>
            </a>
            @endif

            @if($isSA)
            <a href="{{ route('admin.academic-years.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.academic-years.*') || request()->routeIs('admin.semesters.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-orange-400 to-amber-600 flex items-center justify-center text-white"><i class="fas fa-calendar-alt text-[10px]"></i></div>
                <span>Tahun Ajaran & Semester</span>
            </a>
            @endif

            @if($isSA)
            <a href="{{ route('admin.majors.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.majors.*') || request()->routeIs('admin.program-keahlians.*') || request()->routeIs('admin.konsentrasi-keahlians.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-400 to-fuchsia-600 flex items-center justify-center text-white"><i class="fas fa-layer-group text-[10px]"></i></div>
                <span>Kompetensi Keahlian</span>
            </a>
            @endif

            <a href="{{ route('admin.subjects.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.subjects.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-pink-400 to-pink-600 flex items-center justify-center text-white"><i class="fas fa-book text-[10px]"></i></div>
                <span>Mata Pelajaran</span>
            </a>

            <a href="{{ route('admin.classrooms.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.classrooms.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-400 to-cyan-600 flex items-center justify-center text-white"><i class="fas fa-door-open text-[10px]"></i></div>
                <span>Ruang Kelas</span>
            </a>

            @if($isSA)
            <a href="{{ route('admin.master.positions.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.master.positions.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center text-white"><i class="fas fa-id-badge text-[10px]"></i></div>
                <span>Jabatan</span>
            </a>
            @endif

            <a href="{{ route('admin.time-slots.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.time-slots.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-hourglass-half text-[10px]"></i></div>
                <span>Jam Pelajaran</span>
            </a>
        </div>
    </div>

    <!-- ════════════════ GROUP: DATA PENGGUNA ════════════════ -->
    <div class="pt-3" data-menu-group="users">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-users text-[10px]"></i> Data Pengguna</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            @if($isSA || $isAdmin || $isYayasan)
            <a href="{{ route('admin.users.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.users.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-user-cog text-[10px]"></i></div>
                <span>Kelola Akun</span>
            </a>
            @endif

            <a href="{{ route('admin.students.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.students.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white"><i class="fas fa-user-graduate text-[10px]"></i></div>
                <span>Data Siswa</span>
            </a>

            <a href="{{ route('admin.teachers.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.teachers.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-chalkboard-teacher text-[10px]"></i></div>
                <span>Data Guru</span>
            </a>

            <a href="{{ route('admin.employees.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.employees.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-briefcase text-[10px]"></i></div>
                <span>Data Pegawai</span>
            </a>

            <a href="{{ route('admin.parents.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.parents.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-orange-400 to-amber-600 flex items-center justify-center text-white"><i class="fas fa-people-roof text-[10px]"></i></div>
                <span>Orang Tua / Wali</span>
            </a>
        </div>
    </div>

    <!-- ════════════════ GROUP: PEMBDA ELITE ════════════════ -->
    @if(!$isFinance)
    <div class="pt-3" data-menu-group="reputation">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-trophy text-[10px]"></i> Pembda Elite</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('forum.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('forum.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-comments text-[10px]"></i></div>
                <span>Pembda Space</span>
            </a>
            <a href="{{ route('reputation.leaderboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('reputation.leaderboard') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white"><i class="fas fa-ranking-star text-[10px]"></i></div>
                <span>Hall of Fame</span>
            </a>
            <a href="{{ route('admin.reputation.logs') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.reputation.logs') || request()->routeIs('admin.reputation.award.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-history text-[10px]"></i></div>
                <span>Log & Reward</span>
            </a>
            <a href="{{ route('admin.reputation.badges.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.reputation.badges.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-medal text-[10px]"></i></div>
                <span>Kelola Lencana</span>
            </a>
        </div>
    </div>
    @endif

    <!-- ════════════════ GROUP: PENUGASAN & JADWAL ════════════════ -->
    <div class="pt-3" data-menu-group="assignment">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-tasks text-[10px]"></i> Penugasan & Jadwal</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.assignments.positions.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.assignments.positions.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-400 to-pink-600 flex items-center justify-center text-white"><i class="fas fa-user-tag text-[10px]"></i></div>
                <span>Penugasan Jabatan</span>
            </a>
            <a href="{{ route('admin.assignments.teaching.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.assignments.teaching.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-person-chalkboard text-[10px]"></i></div>
                <span>Penugasan Mengajar</span>
            </a>
            <a href="{{ route('admin.schedules.grid') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.schedules.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-400 to-violet-600 flex items-center justify-center text-white"><i class="fas fa-calendar-days text-[10px]"></i></div>
                <span>Jadwal Pelajaran</span>
            </a>
            @if($isSMK || $isSA)
            <a href="{{ route('admin.block-schedule.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.block-schedule.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-orange-400 to-amber-600 flex items-center justify-center text-white"><i class="fas fa-cubes text-[10px]"></i></div>
                <span>Sistem Blok</span>
            </a>
            @endif
            <a href="{{ route('admin.calendar.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.calendar.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white"><i class="fas fa-calendar-alt text-[10px]"></i></div>
                <span>Kalender Pendidikan</span>
            </a>
        </div>
    </div>

    <!-- ════════════════ GROUP: SDM & KEPEGAWAIAN ════════════════ -->
    @if($isSA || $isAdmin || $isYayasan || $isKepsek || $isFinance)
    <div class="pt-3" data-menu-group="payroll">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-id-card-clip text-[10px]"></i> Kepegawaian</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            {{-- Dashboard SDM: Super Admin Erwin, Super Admin Yayasan, Admin Sekolah, Kepala Sekolah --}}
            @if($isSA || $isYayasan || $isAdmin || $isKepsek)
            <a href="{{ route('admin.employees.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.employees.dashboard') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-chart-pie text-[10px]"></i></div>
                <span>Dashboard SDM</span>
            </a>
            @endif

            {{-- Validasi & Evaluasi Kinerja: Super Admin Yayasan, Kepala Sekolah --}}
            @if($isYayasan || $isKepsek)
            <a href="{{ route('admin.performance_contracts.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.performance_contracts.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-400 to-red-600 flex items-center justify-center text-white"><i class="fas fa-file-signature text-[10px]"></i></div>
                <span>Validasi Perjanjian Kinerja</span>
            </a>
            <a href="{{ route('admin.performance_evaluations.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.performance_evaluations.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-star-half-alt text-[10px]"></i></div>
                <span>Evaluasi Kinerja</span>
            </a>
            @endif

            {{-- Absensi Guru & Absensi Staf: Super Admin Erwin, Admin Sekolah, Kepala Sekolah --}}
            @if(($isSA || $isAdmin || $isKepsek) && !$isYayasan)
            <a href="{{ route('admin.teachers.attendance.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.teachers.attendance.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-chalkboard-teacher text-[10px]"></i></div>
                <span>Absensi Guru</span>
            </a>
            <a href="{{ route('admin.employees.attendance.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.employees.attendance.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-fingerprint text-[10px]"></i></div>
                <span>Absensi Staf</span>
            </a>
            @endif

            {{-- Absensi TEFA: Super Admin Erwin --}}
            @if($isSA && !$isYayasan)
            <a href="{{ route('admin.tefa.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.tefa.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white"><i class="fas fa-tools text-[10px]"></i></div>
                <span>Absensi TEFA (Bengkelin)</span>
            </a>
            @endif

            {{-- Cuti & Izin: Super Admin Yayasan, Kepala Sekolah --}}
            @if($isYayasan || $isKepsek)
            <a href="{{ route('admin.employees.leaves.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.employees.leaves.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-400 to-sky-600 flex items-center justify-center text-white"><i class="fas fa-calendar-check text-[10px]"></i></div>
                <span>Cuti & Izin</span>
            </a>
            @endif

            {{-- Rekap Beban Kerja & Penggajian, Slip Gaji, Pengaturan Gaji: Super Admin Yayasan, Bendahara --}}
            @if($isYayasan || $isFinance)
            <a href="{{ route('admin.workload.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.workload.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-list-check text-[10px]"></i></div>
                <span>Rekap Beban Kerja dan Penggajian</span>
            </a>
            <a href="{{ route('admin.payroll.slip-search') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payroll.slip-search') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white"><i class="fas fa-file-invoice-dollar text-[10px]"></i></div>
                <span>Slip Gaji</span>
            </a>
            <a href="{{ route('admin.payroll.settings') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payroll.settings') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-sliders text-[10px]"></i></div>
                <span>Pengaturan Gaji</span>
            </a>
            <a href="{{ route('admin.operational-expenses.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.operational-expenses.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-wallet text-[10px]"></i></div>
                <span>Pengeluaran Operasional</span>
            </a>
            @endif
        </div>
    </div>
    @endif
    @endif

    <!-- ════════════════ GROUP: KESISWAAN ════════════════ -->
    @if(!$isOnlyCommittee || $isPksOrPiket)
    <div class="pt-3" data-menu-group="student_affairs">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-user-shield text-[10px]"></i> Kesiswaan</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.counseling.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.counseling.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-chart-line text-[10px]"></i></div>
                <span>Perkembangan Siswa</span>
            </a>
        </div>
    </div>
    @endif

    <!-- ── GROUP: PKL & HUBUNGAN INDUSTRI ── -->
    @if((!$isOnlyCommittee && ($isSMK || $isSA || $isYayasan)) || $isPanitiaPkl)
    <div class="pt-3" data-menu-group="pkl_alumni">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-briefcase text-[10px]"></i> PKL & Alumni</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.pkl-alumni.dudis.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.pkl-alumni.dudis.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-building text-[10px]"></i></div>
                <span>Master DUDI</span>
            </a>
            <a href="{{ route('admin.pkl-alumni.placements.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.pkl-alumni.placements.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-briefcase text-[10px]"></i></div>
                <span>Penempatan PKL</span>
            </a>
            <a href="{{ route('admin.pkl-alumni.monitorings.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.pkl-alumni.monitorings.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-file-invoice text-[10px]"></i></div>
                <span>Monitoring (Guru)</span>
            </a>
            <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.pkl-alumni.tracer.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-graduation-cap text-[10px]"></i></div>
                <span>Tracer Study (BMW)</span>
            </a>
            <a href="{{ route('admin.alumni-directory.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.alumni-directory.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-users text-[10px]"></i></div>
                <span>Rembuk Alumni (IKA)</span>
            </a>
            <a href="{{ route('admin.pkl-alumni.jobs.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.pkl-alumni.jobs.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white"><i class="fas fa-list text-[10px]"></i></div>
                <span>Lowongan Kerja</span>
            </a>
        </div>
    </div>
    @endif

    <!-- ── GROUP: TUGAS/PROYEK AKHIR ── -->
    @if((!$isOnlyCommittee && ($isSMA || $isSMK || $isSA || $isYayasan)) || $isPanitiaProyek)
    <div class="pt-3" data-menu-group="final_projects">
        <button class="menu-group-toggle w-full flex items-start justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-start gap-2"><i class="fas fa-file-signature text-[10px] mt-[3px]"></i> @if($isSMA) Tugas Penelitian Ilmiah @elseif($isSMK) Project Akhir SMK @else Penelitian & Project Akhir @endif</span>
            <i class="fas fa-chevron-right text-[9px] chevron mt-[3px]"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.final-projects.formats.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.final-projects.formats.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-red-400 to-rose-600 flex items-center justify-center text-white"><i class="fas fa-file-pdf text-[10px]"></i></div>
                <span>Panduan & Format</span>
            </a>
            <a href="{{ route('admin.final-projects.proposals.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.final-projects.proposals.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-user-tie text-[10px]"></i></div>
                <span>Judul & Pembimbing</span>
            </a>
            <a href="{{ route('admin.final-projects.exams.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.final-projects.exams.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-400 to-fuchsia-600 flex items-center justify-center text-white"><i class="fas fa-calendar-check text-[10px]"></i></div>
                <span>Jadwal & Ujian</span>
            </a>
        </div>
    </div>
    @endif

    @if(!$isOnlyCommittee)
    <!-- ════════════════ GROUP: AKADEMIK ════════════════ -->
    <div class="pt-3" data-menu-group="academic">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-graduation-cap text-[10px]"></i> Akademik</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.attendances.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.attendances.index') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-list-check text-[10px]"></i></div>
                <span>Daftar Absensi</span>
            </a>
            <a href="{{ route('admin.attendances.bulk') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.attendances.bulk') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-users-viewfinder text-[10px]"></i></div>
                <span>Input Absensi Kelas</span>
            </a>
            <a href="{{ route('admin.attendances.monitoring') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.attendances.monitoring') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-chart-line text-[10px]"></i></div>
                <span>Monitoring Absensi</span>
            </a>
            <a href="{{ route('admin.grades.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.grades.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white"><i class="fas fa-pen-to-square text-[10px]"></i></div>
                <span>Nilai</span>
            </a>
            <a href="{{ route('admin.grade-weights.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.grade-weights.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-scale-balanced text-[10px]"></i></div>
                <span>Bobot Nilai</span>
            </a>
            <a href="{{ route('admin.report_cards.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.report_cards.*') && !request()->routeIs('admin.settings.report-cards') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-file-lines text-[10px]"></i></div>
                <span>Rapor Digital</span>
            </a>
            @if($isSA || $isAdmin)
            <a href="{{ route('admin.settings.report-cards') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.settings.report-cards*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-cog text-[10px]"></i></div>
                <span>Pengaturan Rapor</span>
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- ════════════════ GROUP: CBT ════════════════ -->
    @if((!$isOnlyCommittee && $canAccess) || $isPanitiaCbt)
    <div class="pt-3" data-menu-group="cbt">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-desktop text-[10px]"></i> CBT / Ujian Online</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.cbt.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.cbt.index') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-400 to-cyan-600 flex items-center justify-center text-white"><i class="fas fa-gauge-high text-[10px]"></i></div>
                <span>Monitoring Ujian</span>
            </a>
            <a href="{{ route('admin.cbt.exams.create') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.cbt.exams.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-school text-[10px]"></i></div>
                <span>Kelola Ujian Sekolah</span>
            </a>
            <a href="{{ route('admin.cbt.banks') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.cbt.banks') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-database text-[10px]"></i></div>
                <span>Bank Soal</span>
            </a>
            <a href="{{ route('admin.cbt.report') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.cbt.report') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-400 to-violet-600 flex items-center justify-center text-white"><i class="fas fa-chart-pie text-[10px]"></i></div>
                <span>Laporan CBT</span>
            </a>
        </div>
    </div>
    @endif

    <!-- ════════════════ GROUP: LMS ════════════════ -->
    @if(!$isOnlyCommittee && $canAccess)
    <div class="pt-3" data-menu-group="lms">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-book-open text-[10px]"></i> LMS / Pembelajaran</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.lms.monitoring') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.lms.monitoring') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-desktop text-[10px]"></i></div>
                <span>Monitoring LMS</span>
            </a>
            <a href="{{ route('admin.knowledge.monitoring') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.knowledge.monitoring') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-cube text-[10px]"></i></div>
                <span>Pantauan Knowledge & Media</span>
            </a>
        </div>
    </div>
    @endif

    <!-- ════════════════ GROUP: PSB ════════════════ -->
    @if($isSA || $isAdmin || $isYayasan || $isKepsek)
    <div class="pt-3" data-menu-group="psb">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-user-plus text-[10px]"></i> PSB</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.psb.applicants.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.psb.applicants.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-file-circle-plus text-[10px]"></i></div>
                <span>Data Pendaftar</span>
            </a>
            @if($isSA || $isAdmin || $isYayasan)
            <a href="{{ route('admin.psb.settings.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.psb.settings.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-sliders-h text-[10px]"></i></div>
                <span>Pengaturan PSB</span>
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- ════════════════ GROUP: KEUANGAN ════════════════ -->
    @if($isYayasan || $isFinance || $isSA || $isAdmin)
    <div class="pt-3" data-menu-group="finance">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-coins text-[10px]"></i> Keuangan</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            {{-- Tagihan Siswa & Pembayaran: Dibuka untuk Super Admin Erwin & Bendahara. Ditutup untuk Admin Sekolah & Yayasan --}}
            @if(($isSA || $isFinance) && !$isYayasan && !$isAdmin)
            <a href="{{ route('admin.bills.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.bills.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-file-invoice-dollar text-[10px]"></i></div>
                <span>Tagihan Siswa</span>
            </a>
            <a href="{{ route('admin.payments.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payments.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-money-bill-transfer text-[10px]"></i></div>
                <span>Pembayaran</span>
            </a>
            @endif

            {{-- Laporan Rekap Tagihan: Dibuka untuk Super Admin Erwin, Super Admin Yayasan, Admin Sekolah, & Bendahara --}}
            <a href="{{ route('admin.payment_reports.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payment_reports.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-chart-pie text-[10px]"></i></div>
                <span>Laporan Rekap Tagihan</span>
            </a>

            {{-- Menu khusus Super Admin Yayasan --}}
            @if($isYayasan)
            <a href="{{ route('yayasan.operational_expenses.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.operational_expenses.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-orange-700 flex items-center justify-center text-white"><i class="fas fa-list-check text-[10px]"></i></div>
                <span>Rencana Belanja Operasional</span>
            </a>
            <a href="{{ route('yayasan.financial_recap.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.financial_recap.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-600 to-purple-800 flex items-center justify-center text-white"><i class="fas fa-chart-line text-[10px]"></i></div>
                <span>Rekapitulasi Keuangan Yayasan</span>
            </a>
            @endif

            @if($isSA && !$isYayasan)
            <a href="{{ route('yayasan.contribution_balance.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('yayasan.contribution_balance.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white"><i class="fas fa-school text-[10px]"></i></div>
                <span>Kontribusi Unit Sekolah</span>
            </a>
            <a href="{{ route('admin.settings.late-fees') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.settings.late-fees*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white"><i class="fas fa-percent text-[10px]"></i></div>
                <span>Pengaturan Denda</span>
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- ════════════════ GROUP: KONTEN WEBSITE ════════════════ -->
    @if($isSA)
    <div class="pt-3" data-menu-group="website">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-globe text-[10px]"></i> Konten Website</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.news.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.news.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-400 to-pink-600 flex items-center justify-center text-white"><i class="fas fa-newspaper text-[10px]"></i></div>
                <span>Kelola Berita</span>
            </a>
            <a href="{{ route('admin.gallery.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.gallery.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-images text-[10px]"></i></div>
                <span>Kelola Galeri</span>
            </a>
            <a href="{{ route('admin.homepage-content.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->is('admin/homepage-content*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white"><i class="fas fa-cog text-[10px]"></i></div>
                <span>Konten Umum</span>
            </a>
        </div>
    </div>
    @endif

    @if(!$isOnlyCommittee)
    <!-- ════════════════ GROUP: PELATIHAN ════════════════ -->
    <div class="pt-3" data-menu-group="training">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-chalkboard text-[10px]"></i> Pelatihan</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.training-modules.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.training-modules.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-400 to-cyan-600 flex items-center justify-center text-white"><i class="fas fa-book-reader text-[10px]"></i></div>
                <span>Kelola Materi</span>
            </a>
            <a href="{{ route('training.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('training.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-eye text-[10px]"></i></div>
                <span>Lihat Materi</span>
            </a>
        </div>
    </div>
    @endif

    <!-- ════════════════ GROUP: PENGATURAN & OTORISASI ════════════════ -->
    @if($isSA || $isAdmin)
    <div class="pt-3 border-t border-gray-100" data-menu-group="settings_auth">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-sliders-h text-[10px]"></i> Pengaturan & Otorisasi</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.settings.features') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.settings.features') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-shield-halved text-[10px]"></i></div>
                <span>Otorisasi Fitur</span>
            </a>
        </div>
    </div>
    @endif

    <!-- ════════════════ GROUP: SURVEY KEPUASAN ════════════════ -->
    @if($isSA || $isAdmin || $isKepsek)
    <div class="pt-3 border-t border-gray-100" data-menu-group="surveys">
        <button class="menu-group-toggle w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-poll text-[10px]"></i> Survey Kepuasan</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body closed mt-1 space-y-0.5">
            <a href="{{ route('admin.surveys.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.surveys.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-rose-500 flex items-center justify-center text-white"><i class="fas fa-clipboard-list text-[10px]"></i></div>
                <span>Kelola Survey</span>
            </a>
        </div>
    </div>
    @endif
    @endif

@endsection
