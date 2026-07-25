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

    <!-- Dashboard -->
    <a href="{{ route('yayasan.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('yayasan.dashboard') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center text-white shadow"><i class="fas fa-home text-xs"></i></div>
        <span class="text-sm flex-1 font-semibold">Dashboard</span>
    </a>

    <!-- Progress Input Data -->
    <a href="{{ route('yayasan.progress-input') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('yayasan.progress-input*') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white shadow"><i class="fas fa-tasks text-xs"></i></div>
        <span class="text-sm flex-1 font-semibold">Progress Input</span>
    </a>

    <!-- Kalender Pendidikan -->
    <a href="{{ route('yayasan.calendar.index') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('yayasan.calendar.*') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white shadow"><i class="fas fa-calendar-alt text-xs"></i></div>
        <span class="text-sm flex-1 font-semibold">Kalender Pendidikan</span>
    </a>

    <!-- Finalisasi Perjanjian Kinerja -->
    <a href="{{ route('yayasan.performance_contracts.index') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('yayasan.performance_contracts.*') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-400 to-red-600 flex items-center justify-center text-white shadow"><i class="fas fa-file-signature text-xs"></i></div>
        <span class="text-sm flex-1 font-semibold">Finalisasi Perjanjian Kinerja</span>
    </a>

    <!-- Evaluasi Perjanjian Kinerja -->
    <a href="{{ route('yayasan.performance_evaluations.index') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('yayasan.performance_evaluations.*') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white shadow"><i class="fas fa-star-half-alt text-xs"></i></div>
        <span class="text-sm flex-1 font-semibold">ACC Evaluasi Kinerja</span>
    </a>

    <!-- Laporan Monitoring PKL -->
    <a href="{{ route('yayasan.pkl_monitorings.index') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('yayasan.pkl_monitorings.*') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white shadow"><i class="fas fa-file-invoice text-xs"></i></div>
        <span class="text-sm flex-1 font-semibold">Monitoring PKL (Guru)</span>
    </a>

    <!-- Saldo Kontribusi Unit Sekolah -->
    <a href="{{ route('yayasan.contribution_balance.index') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('yayasan.contribution_balance.*') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-600 to-purple-800 flex items-center justify-center text-white shadow"><i class="fas fa-calculator text-xs"></i></div>
        <span class="text-sm flex-1 font-semibold">Saldo Kontribusi Unit</span>
    </a>

    <!-- ════════════════ GROUP: SDM & KEPEGAWAIAN ════════════════ -->
    <div class="pt-4" data-menu-group="payroll">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-id-card-clip text-[10px]"></i> SDM & Kepegawaian</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <a href="{{ route('admin.employees.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.employees.dashboard') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-chart-pie text-[10px]"></i></div>
                <span>Dashboard SDM</span>
            </a>
            <a href="{{ route('admin.performance_contracts.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.performance_contracts.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-400 to-red-600 flex items-center justify-center text-white"><i class="fas fa-file-signature text-[10px]"></i></div>
                <span>Validasi Perjanjian Kinerja</span>
            </a>
            <a href="{{ route('admin.performance_evaluations.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.performance_evaluations.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-star-half-alt text-[10px]"></i></div>
                <span>Evaluasi Kinerja</span>
            </a>
            <a href="{{ route('admin.employees.attendance.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.employees.attendance.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-fingerprint text-[10px]"></i></div>
                <span>Absensi Pegawai</span>
            </a>
            <a href="{{ route('admin.tefa.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.tefa.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white"><i class="fas fa-tools text-[10px]"></i></div>
                <span>Absensi TEFA (Bengkelin)</span>
            </a>
            <a href="{{ route('admin.employees.leaves.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.employees.leaves.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-400 to-sky-600 flex items-center justify-center text-white"><i class="fas fa-calendar-check text-[10px]"></i></div>
                <span>Cuti & Izin</span>
            </a>
            <a href="{{ route('admin.workload.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.workload.index') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-list-check text-[10px]"></i></div>
                <span>Rekap Beban Kerja</span>
            </a>
            <a href="{{ route('admin.workload.salary-report') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.workload.salary-report') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-coins text-[10px]"></i></div>
                <span>Penggajian</span>
            </a>
            <a href="{{ route('admin.payroll.slip-search') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payroll.slip-search') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-white"><i class="fas fa-file-invoice-dollar text-[10px]"></i></div>
                <span>Slip Gaji</span>
            </a>
            <a href="{{ route('admin.payroll.settings') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payroll.settings') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-sliders text-[10px]"></i></div>
                <span>Pengaturan Gaji</span>
            </a>
        </div>
    </div>

    <!-- ════════════════ GROUP: KEUANGAN ════════════════ -->
    <div class="pt-4" data-menu-group="finance">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-coins text-[10px]"></i> Keuangan</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <a href="{{ route('admin.bills.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.bills.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-file-invoice-dollar text-[10px]"></i></div>
                <span>Tagihan Siswa</span>
            </a>
            <a href="{{ route('admin.payments.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payments.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-money-bill-transfer text-[10px]"></i></div>
                <span>Pembayaran</span>
            </a>
            <a href="{{ route('admin.payment_reports.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.payment_reports.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-chart-pie text-[10px]"></i></div>
                <span>Laporan Rekap Tagihan</span>
            </a>
            <a href="{{ route('admin.settings.late-fees') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.settings.late-fees*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white"><i class="fas fa-percent text-[10px]"></i></div>
                <span>Pengaturan Denda</span>
            </a>
        </div>
    </div>

    <!-- ════════════════ GROUP: DATA PENGGUNA & MASTER ════════════════ -->
    <div class="pt-4" data-menu-group="users">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-users text-[10px]"></i> Data Pengguna & Sekolah</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <a href="{{ route('admin.users.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.users.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-user-cog text-[10px]"></i></div>
                <span>Kelola Akun</span>
            </a>
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
            <a href="{{ route('admin.schools.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.schools.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-school text-[10px]"></i></div>
                <span>Kelola Sekolah</span>
            </a>
        </div>
    </div>

    <!-- ════════════════ GROUP: UNIT PENDIDIKAN ════════════════ -->
    <div class="pt-4" data-menu-group="schools">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-school text-[10px]"></i> Unit Pendidikan</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            @php
                $schools = \App\Models\School::schoolsOnly()->where('is_active', true)->get();
            @endphp
            @foreach($schools as $school)
                <div class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-gray-600">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white text-[9px] font-bold">
                        {{ strtoupper(substr($school->type, 0, 3)) }}
                    </div>
                    <span>{{ $school->name }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ════════════════ GROUP: PEMBDA ELITE ════════════════ -->
    <div class="pt-3" data-menu-group="elite">
        <button class="menu-group-toggle open w-full flex items-center justify-between px-3 py-1.5 text-xs font-bold text-gray-400 uppercase tracking-wider hover:text-gray-600" onclick="toggleGroup(this)">
            <span class="flex items-center gap-2"><i class="fas fa-trophy text-[10px]"></i> Pembda Elite</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </button>
        <div class="menu-group-body mt-1 space-y-0.5" style="max-height:2000px">
            <a href="{{ route('training.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('training.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-400 to-cyan-600 flex items-center justify-center text-white"><i class="fas fa-book-reader text-[10px]"></i></div>
                <span>Pelatihan PembdaHUB</span>
            </a>
            <a href="{{ route('forum.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('forum.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-comments text-[10px]"></i></div>
                <span>Pembda Space</span>
            </a>
            <a href="{{ route('reputation.leaderboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('reputation.leaderboard') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white"><i class="fas fa-ranking-star text-[10px]"></i></div>
                <span>Hall of Fame</span>
            </a>
        </div>
    </div>
@endsection
