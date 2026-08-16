@php
    $currentRoute = request()->route()->getName();
    $currentGroup = $group ?? 'siswa';
    $currentSchoolId = $schoolId ?? null;
    $currentDate = $date ?? \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .edu-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
    }
    .edu-btn-primary {
        background: #4f46e5;
        color: #ffffff;
        border: 1px solid #4338ca;
    }
    .edu-btn-primary:hover {
        background: #4338ca;
    }
    .edu-btn-light {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #e2e8f0;
    }
    .edu-btn-light:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
</style>

<div class="space-y-6 mb-8 font-sans">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO GREETING BANNER (CLEAN & SPACIOUS)         --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="rounded-3xl p-6 md:p-8 text-white shadow-lg relative overflow-hidden" 
         style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #1e3a8a 100%);">
        
        {{-- Background Soft Glows --}}
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-40 -top-10 w-48 h-48 rounded-full bg-indigo-500/15 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-center gap-5">
                {{-- Mascot Icon Container --}}
                <div class="w-16 h-16 rounded-2xl bg-amber-400 text-slate-900 flex items-center justify-center text-3xl font-extrabold shadow-md shrink-0 border border-amber-300">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 mb-2 flex-wrap">
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-white/15 text-amber-200 border border-white/20 inline-flex items-center gap-1.5">
                            <i class="fas fa-layer-group text-amber-300"></i> Pusat Absensi Terpadu
                        </span>
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 inline-flex items-center gap-1.5">
                            <i class="fas fa-satellite-dish"></i> Real-Time GPS & RFID
                        </span>
                    </div>
                    <h1 class="text-xl md:text-2xl lg:text-3xl font-extrabold tracking-tight text-white leading-snug">
                        Presensi {{ ucfirst($currentGroup) }} &mdash; {{ $selectedSchool->name ?? 'Perguruan Pembda' }}
                    </h1>
                    <p class="text-xs md:text-sm font-medium mt-1 text-slate-300 leading-relaxed max-w-xl">
                        Monitoring absensi harian, verifikasi kehadiran tepat waktu, dan rekapitulasi data terpadu.
                    </p>
                </div>
            </div>

            {{-- Controls: Unit & Date Filter --}}
            <div class="flex flex-wrap items-center gap-3.5 shrink-0">
                @if($isSuperAdmin && isset($schools) && $schools->count() > 1)
                <div class="relative min-w-[220px]">
                    <select onchange="changeAttendanceSchool(this.value)"
                            class="w-full bg-white text-slate-800 font-bold text-xs rounded-xl px-4 py-3 shadow-md focus:ring-2 focus:ring-amber-400 transition cursor-pointer appearance-none pr-10 border border-slate-200">
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ $currentSchoolId == $sch->id ? 'selected' : '' }}>
                                🏫 {{ $sch->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500 text-xs">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                @endif

                <div class="inline-flex items-center gap-2.5 bg-white text-slate-800 rounded-xl px-4 py-2.5 shadow-md border border-slate-200">
                    <i class="fas fa-calendar-alt text-indigo-600 text-sm"></i>
                    <input type="date" value="{{ $currentDate }}" onchange="changeAttendanceDate(this.value)"
                           class="bg-transparent border-none text-xs font-bold text-slate-800 focus:ring-0 p-0 cursor-pointer">
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- NAVIGATION BAR: KELOMPOK & SUB-MENU             --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="edu-card p-5 md:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        {{-- Kelompok Switcher --}}
        <div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">
                Kelompok Presensi:
            </span>
            <div class="flex flex-wrap items-center gap-2.5">
                @if(!$isYayasan)
                <a href="{{ request()->fullUrlWithQuery(['group' => 'siswa']) }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm
                          {{ $currentGroup === 'siswa' ? 'edu-btn-primary shadow-indigo-100' : 'edu-btn-light' }}">
                    <i class="fas fa-user-graduate text-xs"></i>
                    <span>Siswa</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group' => 'guru']) }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm
                          {{ $currentGroup === 'guru' ? 'edu-btn-primary shadow-indigo-100' : 'edu-btn-light' }}">
                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                    <span>Guru</span>
                </a>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['group' => 'pegawai']) }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm
                          {{ $currentGroup === 'pegawai' ? 'edu-btn-primary shadow-indigo-100' : 'edu-btn-light' }}">
                    <i class="fas fa-user-tie text-xs"></i>
                    <span>Pegawai / Staf</span>
                </a>
            </div>
        </div>

        {{-- 4 Sub-Menu Navigation Tabs --}}
        <div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5 lg:text-right">
                Pilihan Menu Presensi:
            </span>
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- 1. Live Feed --}}
                <a href="{{ route('admin.attendance.live', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2 px-4.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm
                          {{ str_contains($currentRoute, 'live') ? 'bg-rose-600 text-white border border-rose-700' : 'edu-btn-light' }}">
                    <span class="w-2 h-2 rounded-full {{ str_contains($currentRoute, 'live') ? 'bg-white animate-ping' : 'bg-rose-500' }}"></span>
                    <span>Live Feed</span>
                </a>

                {{-- 2. Monitoring Harian --}}
                <a href="{{ route('admin.attendance.monitoring', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2 px-4.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm
                          {{ str_contains($currentRoute, 'monitoring') || $currentRoute === 'admin.attendance.index' ? 'edu-btn-primary shadow-indigo-100' : 'edu-btn-light' }}">
                    <i class="fas fa-chart-pie text-xs"></i>
                    <span>Monitoring Harian</span>
                </a>

                {{-- 3. Input Massal --}}
                <a href="{{ route('admin.attendance.bulk', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2 px-4.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm
                          {{ str_contains($currentRoute, 'bulk') ? 'bg-amber-500 text-slate-900 border border-amber-600 font-extrabold' : 'edu-btn-light' }}">
                    <i class="fas fa-table-list text-xs"></i>
                    <span>Input Massal</span>
                </a>

                {{-- 4. Rekap & Laporan --}}
                <a href="{{ route('admin.attendance.rekap', ['group' => $currentGroup, 'school_id' => $currentSchoolId]) }}"
                   class="inline-flex items-center gap-2 px-4.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm
                          {{ str_contains($currentRoute, 'rekap') ? 'bg-emerald-600 text-white border border-emerald-700' : 'edu-btn-light' }}">
                    <i class="fas fa-file-invoice text-xs"></i>
                    <span>Rekap & Laporan</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function changeAttendanceSchool(schoolId) {
    const url = new URL(window.location.href);
    url.searchParams.set('school_id', schoolId);
    window.location.href = url.toString();
}

function changeAttendanceDate(dateVal) {
    const url = new URL(window.location.href);
    url.searchParams.set('date', dateVal);
    window.location.href = url.toString();
}
</script>
