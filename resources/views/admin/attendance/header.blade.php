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
    .edu-font {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .edu-hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.3;
        white-space: nowrap;
    }
    .edu-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
        white-space: nowrap;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .edu-btn-primary {
        background-color: #4f46e5;
        color: #ffffff !important;
        border: 1px solid #4338ca;
        box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);
    }
    .edu-btn-primary:hover {
        background-color: #4338ca;
    }
    .edu-btn-light {
        background-color: #f8fafc;
        color: #334155 !important;
        border: 1px solid #e2e8f0;
    }
    .edu-btn-light:hover {
        background-color: #f1f5f9;
        color: #0f172a !important;
    }
    .edu-btn-coral {
        background-color: #e11d48;
        color: #ffffff !important;
        border: 1px solid #be123c;
        box-shadow: 0 2px 4px rgba(225, 29, 72, 0.25);
    }
    .edu-btn-amber {
        background-color: #f59e0b;
        color: #0f172a !important;
        border: 1px solid #d97706;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.25);
    }
    .edu-btn-emerald {
        background-color: #059669;
        color: #ffffff !important;
        border: 1px solid #047857;
        box-shadow: 0 2px 4px rgba(5, 150, 105, 0.25);
    }
    .edu-control-group {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .edu-control-box {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 9px 16px;
        border-radius: 12px;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }
    .edu-select-custom {
        width: 100%;
        background-color: #ffffff;
        color: #1e293b;
        font-weight: 700;
        font-size: 12px;
        border-radius: 12px;
        padding: 9px 38px 9px 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        border: 1px solid #cbd5e1;
        cursor: pointer;
        outline: none;
        appearance: none;
    }
</style>

<div class="space-y-6 mb-8 edu-font">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO GREETING BANNER                           --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="rounded-3xl p-6 md:p-8 text-white shadow-lg relative overflow-hidden" 
         style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #1e3a8a 100%);">
        
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
                    <div class="flex items-center gap-3 mb-2.5 flex-wrap">
                        <span class="edu-hero-pill bg-white/15 text-amber-200 border border-white/25">
                            <i class="fas fa-layer-group text-amber-300"></i>
                            <span>Pusat Absensi Terpadu</span>
                        </span>
                        <span class="edu-hero-pill bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                            <i class="fas fa-satellite-dish"></i>
                            <span>Real-Time GPS & RFID</span>
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
            <div class="edu-control-group shrink-0">
                @if($isSuperAdmin && isset($schools) && $schools->count() > 1)
                <div class="relative min-w-[220px]">
                    <select onchange="changeAttendanceSchool(this.value)" class="edu-select-custom">
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ $currentSchoolId == $sch->id ? 'selected' : '' }}>
                                🏫 {{ $sch->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 text-xs">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                @endif

                <div class="edu-control-box">
                    <i class="fas fa-calendar-alt text-indigo-600 text-sm"></i>
                    <input type="date" value="{{ $currentDate }}" onchange="changeAttendanceDate(this.value)"
                           class="bg-transparent border-none text-xs font-bold text-slate-800 focus:ring-0 p-0 cursor-pointer outline-none">
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- NAVIGATION BAR: KELOMPOK & SUB-MENU             --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl p-5 md:p-6 border border-slate-200 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        {{-- Kelompok Switcher --}}
        <div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">
                Kelompok Presensi:
            </span>
            <div class="flex flex-wrap items-center gap-3">
                @if(!$isYayasan)
                <a href="{{ request()->fullUrlWithQuery(['group' => 'siswa']) }}" 
                   class="edu-nav-btn {{ $currentGroup === 'siswa' ? 'edu-btn-primary' : 'edu-btn-light' }}">
                    <i class="fas fa-user-graduate text-xs"></i>
                    <span>Siswa</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group' => 'guru']) }}" 
                   class="edu-nav-btn {{ $currentGroup === 'guru' ? 'edu-btn-primary' : 'edu-btn-light' }}">
                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                    <span>Guru</span>
                </a>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['group' => 'pegawai']) }}" 
                   class="edu-nav-btn {{ $currentGroup === 'pegawai' ? 'edu-btn-primary' : 'edu-btn-light' }}">
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
            <div class="flex flex-wrap items-center gap-3">
                {{-- 1. Live Feed --}}
                <a href="{{ route('admin.attendance.live', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="edu-nav-btn {{ str_contains($currentRoute, 'live') ? 'edu-btn-coral' : 'edu-btn-light' }}">
                    <span class="w-2 h-2 rounded-full {{ str_contains($currentRoute, 'live') ? 'bg-white' : 'bg-rose-500' }}"></span>
                    <span>Live Feed</span>
                </a>

                {{-- 2. Monitoring Harian --}}
                <a href="{{ route('admin.attendance.monitoring', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="edu-nav-btn {{ str_contains($currentRoute, 'monitoring') || $currentRoute === 'admin.attendance.index' ? 'edu-btn-primary' : 'edu-btn-light' }}">
                    <i class="fas fa-chart-pie text-xs"></i>
                    <span>Monitoring Harian</span>
                </a>

                {{-- 3. Input Massal --}}
                <a href="{{ route('admin.attendance.bulk', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="edu-nav-btn {{ str_contains($currentRoute, 'bulk') ? 'edu-btn-amber' : 'edu-btn-light' }}">
                    <i class="fas fa-table-list text-xs"></i>
                    <span>Input Massal</span>
                </a>

                {{-- 4. Rekap & Laporan --}}
                <a href="{{ route('admin.attendance.rekap', ['group' => $currentGroup, 'school_id' => $currentSchoolId]) }}"
                   class="edu-nav-btn {{ str_contains($currentRoute, 'rekap') ? 'edu-btn-emerald' : 'edu-btn-light' }}">
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
