@php
    $currentRoute = request()->route()->getName();
    $currentGroup = $group ?? 'siswa';
    $currentSchoolId = $schoolId ?? null;
    $currentDate = $date ?? \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
@endphp

{{-- Google Fonts for Playful Educational UI --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    :root {
        --clay-font-title: 'Fredoka', 'Plus Jakarta Sans', sans-serif;
        --clay-font-body: 'Plus Jakarta Sans', sans-serif;
    }
    .clay-card {
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 8px 12px 24px rgba(30, 41, 59, 0.08), -6px -6px 16px rgba(255, 255, 255, 0.9), inset 2px 2px 4px rgba(255, 255, 255, 0.8), inset -2px -2px 4px rgba(0, 0, 0, 0.03);
        border: 2px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .clay-card:hover {
        transform: translateY(-4px);
        box-shadow: 12px 18px 30px rgba(30, 41, 59, 0.12), -8px -8px 20px rgba(255, 255, 255, 1);
    }
    .clay-btn-primary {
        background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        color: #ffffff;
        box-shadow: 4px 6px 14px rgba(67, 97, 238, 0.35), inset 2px 2px 3px rgba(255, 255, 255, 0.4), inset -2px -2px 3px rgba(0, 0, 0, 0.2);
        border: none;
    }
    .clay-btn-amber {
        background: linear-gradient(135deg, #ffd166 0%, #f77f00 100%);
        color: #1e293b;
        box-shadow: 4px 6px 14px rgba(247, 127, 0, 0.3), inset 2px 2px 3px rgba(255, 255, 255, 0.6), inset -2px -2px 3px rgba(0, 0, 0, 0.1);
        border: none;
    }
    .clay-btn-coral {
        background: linear-gradient(135deg, #ff758c 0%, #ff4b2b 100%);
        color: #ffffff;
        box-shadow: 4px 6px 14px rgba(255, 75, 43, 0.35), inset 2px 2px 3px rgba(255, 255, 255, 0.4), inset -2px -2px 3px rgba(0, 0, 0, 0.2);
        border: none;
    }
    .clay-btn-emerald {
        background: linear-gradient(135deg, #06d6a0 0%, #059669 100%);
        color: #ffffff;
        box-shadow: 4px 6px 14px rgba(6, 214, 160, 0.35), inset 2px 2px 3px rgba(255, 255, 255, 0.4), inset -2px -2px 3px rgba(0, 0, 0, 0.2);
        border: none;
    }
    .clay-pill-soft {
        background: #f8fafc;
        box-shadow: inset 3px 3px 6px rgba(0,0,0,0.05), inset -3px -3px 6px rgba(255,255,255,0.9);
        border: 1px solid #e2e8f0;
    }
    .clay-badge-bubble {
        border-radius: 9999px;
        box-shadow: 3px 4px 8px rgba(0,0,0,0.06), inset 1px 1px 2px rgba(255,255,255,0.6);
    }
</style>

<div class="space-y-8 mb-10">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- PLAYFUL CLAYMORPHISM HERO GREETING BANNER       --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="clay-card rounded-[2.5rem] p-8 md:p-10 lg:p-11 relative overflow-hidden" 
         style="background: linear-gradient(135deg, #4361ee 0%, #4895ef 50%, #4cc9f0 100%); color: #ffffff;">
        
        {{-- Cute Floating Background Bubbles --}}
        <div class="absolute -right-10 -bottom-10 w-56 h-56 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-36 -top-14 w-40 h-40 rounded-full bg-yellow-300/20 blur-xl pointer-events-none"></div>
        <div class="absolute left-1/3 -bottom-10 w-28 h-28 rounded-full bg-pink-400/20 blur-lg pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            <div class="flex items-start md:items-center">
                {{-- Playful 3D Mascot Icon --}}
                <div class="w-18 h-18 md:w-20 md:h-20 rounded-[2rem] bg-gradient-to-br from-yellow-300 via-amber-400 to-orange-400 text-slate-900 flex items-center justify-center text-3xl md:text-4xl shadow-xl shrink-0 mr-6 md:mr-7 transform hover:rotate-6 transition-transform"
                     style="box-shadow: 4px 8px 20px rgba(245, 158, 11, 0.45), inset 2px 2px 5px rgba(255,255,255,0.8), inset -2px -2px 5px rgba(0,0,0,0.1);">
                    <i class="fas fa-graduation-cap text-slate-900"></i>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-2.5 flex-wrap">
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-md shadow-sm flex items-center gap-2 border border-white/30">
                            <i class="fas fa-sparkles text-yellow-300"></i> Edu Attendance Hub
                        </span>
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-400 text-slate-900 shadow-sm flex items-center gap-2 font-sans">
                            <i class="fas fa-satellite-dish"></i> 100% Real-Time Track
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3.5xl font-extrabold tracking-tight text-white leading-tight" style="font-family: var(--clay-font-title);">
                        Pusat Presensi {{ ucfirst($currentGroup) }} — {{ $selectedSchool->name ?? 'Perguruan Pembda' }}
                    </h1>
                    <p class="text-xs md:text-sm font-medium mt-2 text-blue-100 leading-relaxed max-w-xl font-sans">
                        Monitoring kehadiran terpadu, verifikasi GPS & RFID, input massal cerdas, dan rekapitulasi laporan terpercaya!
                    </p>
                </div>
            </div>

            {{-- Controls: Unit & Date Filter --}}
            <div class="flex flex-wrap items-center gap-4 shrink-0">
                @if($isSuperAdmin && isset($schools) && $schools->count() > 1)
                <div class="relative min-w-[240px]">
                    <select onchange="changeAttendanceSchool(this.value)"
                            class="w-full bg-white text-slate-800 font-bold text-xs rounded-2xl px-5 py-4 shadow-md focus:ring-4 focus:ring-yellow-300 transition cursor-pointer appearance-none pr-11 border-2 border-white/70">
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ $currentSchoolId == $sch->id ? 'selected' : '' }}>
                                🏫 {{ $sch->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4.5 text-slate-600 text-xs">
                        <i class="fas fa-chevron-down font-bold"></i>
                    </div>
                </div>
                @endif

                <div class="inline-flex items-center gap-3 bg-white text-slate-800 rounded-2xl px-5.5 py-3.5 shadow-md border-2 border-white/70">
                    <i class="fas fa-calendar-day text-indigo-600 text-sm"></i>
                    <input type="date" value="{{ $currentDate }}" onchange="changeAttendanceDate(this.value)"
                           class="bg-transparent border-none text-xs font-bold text-slate-800 focus:ring-0 p-0 cursor-pointer">
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- PLAYFUL CLAY NAVIGATION BAR: KELOMPOK & SUB-MENU--}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="clay-card rounded-[2.2rem] p-6 md:p-7 flex flex-col lg:flex-row lg:items-center justify-between gap-6 md:gap-8 bg-white">
        {{-- Kelompok Switcher --}}
        <div>
            <span class="block text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-2.5" style="font-family: var(--clay-font-title);">
                Pilih Kelompok Presensi:
            </span>
            <div class="flex flex-wrap items-center gap-3">
                @if(!$isYayasan)
                <a href="{{ request()->fullUrlWithQuery(['group' => 'siswa']) }}" 
                   class="inline-flex items-center gap-2.5 px-6 py-3 rounded-2xl text-xs font-extrabold transition-all
                          {{ $currentGroup === 'siswa' ? 'clay-btn-primary scale-[1.03]' : 'clay-pill-soft text-slate-700 hover:bg-slate-100' }}">
                    <i class="fas fa-user-graduate text-xs"></i>
                    <span>Siswa</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group' => 'guru']) }}" 
                   class="inline-flex items-center gap-2.5 px-6 py-3 rounded-2xl text-xs font-extrabold transition-all
                          {{ $currentGroup === 'guru' ? 'clay-btn-primary scale-[1.03]' : 'clay-pill-soft text-slate-700 hover:bg-slate-100' }}">
                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                    <span>Guru</span>
                </a>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['group' => 'pegawai']) }}" 
                   class="inline-flex items-center gap-2.5 px-6 py-3 rounded-2xl text-xs font-extrabold transition-all
                          {{ $currentGroup === 'pegawai' ? 'clay-btn-primary scale-[1.03]' : 'clay-pill-soft text-slate-700 hover:bg-slate-100' }}">
                    <i class="fas fa-user-tie text-xs"></i>
                    <span>Pegawai / Staf</span>
                </a>
            </div>
        </div>

        {{-- 4 Sub-Menu Navigation Tabs --}}
        <div>
            <span class="block text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-2.5 lg:text-right" style="font-family: var(--clay-font-title);">
                Pilihan Menu Presensi:
            </span>
            <div class="flex flex-wrap items-center gap-3">
                {{-- 1. Live Feed --}}
                <a href="{{ route('admin.attendance.live', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2.5 px-5.5 py-3 rounded-2xl text-xs font-extrabold transition-all
                          {{ str_contains($currentRoute, 'live') ? 'clay-btn-coral scale-[1.03]' : 'clay-pill-soft text-slate-700 hover:bg-slate-100' }}">
                    <span class="w-2.5 h-2.5 rounded-full {{ str_contains($currentRoute, 'live') ? 'bg-white animate-ping' : 'bg-rose-500' }}"></span>
                    <span>Live Feed</span>
                </a>

                {{-- 2. Monitoring Harian --}}
                <a href="{{ route('admin.attendance.monitoring', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2.5 px-5.5 py-3 rounded-2xl text-xs font-extrabold transition-all
                          {{ str_contains($currentRoute, 'monitoring') || $currentRoute === 'admin.attendance.index' ? 'clay-btn-primary scale-[1.03]' : 'clay-pill-soft text-slate-700 hover:bg-slate-100' }}">
                    <i class="fas fa-chart-pie text-xs"></i>
                    <span>Monitoring Harian</span>
                </a>

                {{-- 3. Input Massal --}}
                <a href="{{ route('admin.attendance.bulk', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2.5 px-5.5 py-3 rounded-2xl text-xs font-extrabold transition-all
                          {{ str_contains($currentRoute, 'bulk') ? 'clay-btn-amber scale-[1.03]' : 'clay-pill-soft text-slate-700 hover:bg-slate-100' }}">
                    <i class="fas fa-table-list text-xs"></i>
                    <span>Input Massal</span>
                </a>

                {{-- 4. Rekap & Laporan --}}
                <a href="{{ route('admin.attendance.rekap', ['group' => $currentGroup, 'school_id' => $currentSchoolId]) }}"
                   class="inline-flex items-center gap-2.5 px-5.5 py-3 rounded-2xl text-xs font-extrabold transition-all
                          {{ str_contains($currentRoute, 'rekap') ? 'clay-btn-emerald scale-[1.03]' : 'clay-pill-soft text-slate-700 hover:bg-slate-100' }}">
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
