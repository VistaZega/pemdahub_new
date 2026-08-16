@php
    $currentRoute = request()->route()->getName();
    $currentGroup = $group ?? 'siswa';
    $currentSchoolId = $schoolId ?? null;
    $currentDate = $date ?? \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
@endphp

<div class="space-y-4 mb-6">
    <!-- Top Header: Title & Unit Selector -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white text-xl shadow-md shrink-0">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Pusat Absensi Terpadu</h1>
                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                        {{ $selectedSchool->name ?? 'Perguruan Pembda' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">
                    Monitoring kehadiran real-time, input massal, dan rekapitulasi data 3 kelompok terintegrasi
                </p>
            </div>
        </div>

        <!-- Unit Kerja & Date Controls -->
        <div class="flex items-center gap-2.5 flex-wrap">
            @if($isSuperAdmin && isset($schools) && $schools->count() > 1)
            <div class="relative min-w-[200px]">
                <select onchange="changeAttendanceSchool(this.value)"
                        class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-300 rounded-xl px-3 py-2 text-xs font-black text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition cursor-pointer appearance-none pr-8">
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ $currentSchoolId == $sch->id ? 'selected' : '' }}>
                            {{ $sch->name }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-500 text-xs">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>
            @endif

            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 shadow-2xs">
                <i class="fa-regular fa-calendar text-slate-400 text-xs mr-2"></i>
                <input type="date" value="{{ $currentDate }}" onchange="changeAttendanceDate(this.value)"
                       class="bg-transparent border-none text-xs font-black text-slate-800 focus:ring-0 p-0 cursor-pointer">
            </div>
        </div>
    </div>

    <!-- Kelompok Pill Switcher & Sub-Nav Tabs -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-2.5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <!-- 3 Kelompok Tabs (Siswa, Guru, Pegawai) -->
        <div class="inline-flex p-1 bg-slate-100/90 rounded-xl border border-slate-200 gap-1">
            @if(!$isYayasan)
            <a href="{{ request()->fullUrlWithQuery(['group' => 'siswa']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-black transition-all flex items-center gap-1.5
                      {{ $currentGroup === 'siswa' ? 'bg-white text-emerald-700 shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="fa-solid fa-graduation-cap text-[11px]"></i>
                <span>Siswa</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['group' => 'guru']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-black transition-all flex items-center gap-1.5
                      {{ $currentGroup === 'guru' ? 'bg-white text-emerald-700 shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="fa-solid fa-chalkboard-user text-[11px]"></i>
                <span>Guru</span>
            </a>
            @endif
            <a href="{{ request()->fullUrlWithQuery(['group' => 'pegawai']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-black transition-all flex items-center gap-1.5
                      {{ $currentGroup === 'pegawai' ? 'bg-white text-emerald-700 shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="fa-solid fa-user-tie text-[11px]"></i>
                <span>Pegawai / Staf</span>
            </a>
        </div>

        <!-- 4 Sub-Nav Navigation Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            <!-- 1. Live -->
            <a href="{{ route('admin.attendance.live', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-1.5 shrink-0 border
                      {{ str_contains($currentRoute, 'live') ? 'bg-rose-500 text-white border-rose-600 shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <span class="w-2 h-2 rounded-full {{ str_contains($currentRoute, 'live') ? 'bg-white animate-pulse' : 'bg-rose-500' }}"></span>
                <span>Live Feed</span>
            </a>

            <!-- 2. Monitoring -->
            <a href="{{ route('admin.attendance.monitoring', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-1.5 shrink-0 border
                      {{ str_contains($currentRoute, 'monitoring') || $currentRoute === 'admin.attendance.index' ? 'bg-emerald-600 text-white border-emerald-700 shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <i class="fa-solid fa-chart-line text-[11px]"></i>
                <span>Monitoring Harian</span>
            </a>

            <!-- 3. Bulk Input -->
            <a href="{{ route('admin.attendance.bulk', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-1.5 shrink-0 border
                      {{ str_contains($currentRoute, 'bulk') ? 'bg-emerald-600 text-white border-emerald-700 shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <i class="fa-solid fa-table-list text-[11px]"></i>
                <span>Input Massal</span>
            </a>

            <!-- 4. Rekap -->
            <a href="{{ route('admin.attendance.rekap', ['group' => $currentGroup, 'school_id' => $currentSchoolId]) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-1.5 shrink-0 border
                      {{ str_contains($currentRoute, 'rekap') ? 'bg-emerald-600 text-white border-emerald-700 shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                <i class="fa-solid fa-file-invoice text-[11px]"></i>
                <span>Rekap & Laporan</span>
            </a>
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
