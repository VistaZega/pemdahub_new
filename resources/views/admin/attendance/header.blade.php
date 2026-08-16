@php
    $currentRoute = request()->route()->getName();
    $currentGroup = $group ?? 'siswa';
    $currentSchoolId = $schoolId ?? null;
    $currentDate = $date ?? \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
@endphp

<div class="space-y-6 mb-8">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO GREETING BANNER (VIBRANT POP NEO-BRUTALISM) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-[2.5rem] shadow-2xl p-6 md:p-8 border-2 border-black" 
         style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #064e3b 100%) !important; color: #ffffff !important;">
        {{-- Ornament blurs --}}
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-emerald-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-start md:items-center">
                <div class="w-16 h-16 rounded-2xl bg-amber-400 text-black border-2 border-black shadow-lg flex items-center justify-center text-2xl font-black shrink-0 mr-5">
                    <i class="fas fa-clipboard-user text-black"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="px-3.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border-2 border-black bg-amber-400 text-black shadow-xs">
                            <i class="fas fa-layer-group mr-1.5"></i> Pusat Absensi Terpadu
                        </span>
                        <span class="px-3.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border-2 border-black bg-emerald-400 text-black shadow-xs">
                            <i class="fas fa-shield-halved mr-1.5"></i> 3 Kelompok & 4 Unit
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black text-white leading-tight tracking-tight">
                        Presensi {{ ucfirst($currentGroup) }} — {{ $selectedSchool->name ?? 'Perguruan Pembda' }}
                    </h1>
                    <p class="text-xs md:text-sm font-bold mt-1.5 text-emerald-200 leading-relaxed max-w-xl">
                        Monitoring real-time GPS & RFID, input massal terlindungi, dan rekapitulasi laporan resmi.
                    </p>
                </div>
            </div>

            {{-- Controls: Unit & Date --}}
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                @if($isSuperAdmin && isset($schools) && $schools->count() > 1)
                <div class="relative min-w-[220px]">
                    <select onchange="changeAttendanceSchool(this.value)"
                            class="w-full bg-white hover:bg-amber-50 border-2 border-black rounded-2xl px-5 py-3 text-xs font-black text-black focus:ring-2 focus:ring-amber-400 transition cursor-pointer appearance-none pr-10 shadow-md">
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ $currentSchoolId == $sch->id ? 'selected' : '' }}>
                                🏫 {{ $sch->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black text-xs">
                        <i class="fas fa-chevron-down font-black"></i>
                    </div>
                </div>
                @endif

                <div class="inline-flex items-center gap-2.5 bg-amber-400 text-black border-2 border-black rounded-2xl px-5 py-2.5 shadow-md">
                    <i class="fas fa-calendar-alt text-black text-sm"></i>
                    <input type="date" value="{{ $currentDate }}" onchange="changeAttendanceDate(this.value)"
                           class="bg-transparent border-none text-xs font-black text-black focus:ring-0 p-0 cursor-pointer">
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- NAVIGATION BAR: KELOMPOK & SUB-MENU (LEGA & RAPI) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-xl flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        {{-- Kelompok Switcher --}}
        <div>
            <span class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">
                Pilih Kelompok:
            </span>
            <div class="flex flex-wrap items-center gap-2.5">
                @if(!$isYayasan)
                <a href="{{ request()->fullUrlWithQuery(['group' => 'siswa']) }}" 
                   class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ $currentGroup === 'siswa' ? 'bg-black text-amber-400 shadow-md scale-[1.02]' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-user-graduate text-xs"></i>
                    <span>Siswa</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group' => 'guru']) }}" 
                   class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ $currentGroup === 'guru' ? 'bg-black text-amber-400 shadow-md scale-[1.02]' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                    <span>Guru</span>
                </a>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['group' => 'pegawai']) }}" 
                   class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ $currentGroup === 'pegawai' ? 'bg-black text-amber-400 shadow-md scale-[1.02]' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-user-tie text-xs"></i>
                    <span>Pegawai / Staf</span>
                </a>
            </div>
        </div>

        {{-- 4 Sub-Menu Navigation Tabs --}}
        <div>
            <span class="block text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2 lg:text-right">
                Pilihan Modul:
            </span>
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- 1. Live Feed --}}
                <a href="{{ route('admin.attendance.live', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ str_contains($currentRoute, 'live') ? 'bg-rose-500 text-white shadow-md scale-[1.02]' : 'bg-white text-black hover:bg-amber-300' }}">
                    <span class="w-2.5 h-2.5 rounded-full {{ str_contains($currentRoute, 'live') ? 'bg-white animate-ping' : 'bg-rose-500' }}"></span>
                    <span>Live Feed</span>
                </a>

                {{-- 2. Monitoring Harian --}}
                <a href="{{ route('admin.attendance.monitoring', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ str_contains($currentRoute, 'monitoring') || $currentRoute === 'admin.attendance.index' ? 'bg-black text-amber-400 shadow-md scale-[1.02]' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-chart-line text-xs"></i>
                    <span>Monitoring Harian</span>
                </a>

                {{-- 3. Input Massal --}}
                <a href="{{ route('admin.attendance.bulk', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ str_contains($currentRoute, 'bulk') ? 'bg-black text-amber-400 shadow-md scale-[1.02]' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-table text-xs"></i>
                    <span>Input Massal</span>
                </a>

                {{-- 4. Rekap & Laporan --}}
                <a href="{{ route('admin.attendance.rekap', ['group' => $currentGroup, 'school_id' => $currentSchoolId]) }}"
                   class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ str_contains($currentRoute, 'rekap') ? 'bg-black text-amber-400 shadow-md scale-[1.02]' : 'bg-white text-black hover:bg-amber-300' }}">
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
