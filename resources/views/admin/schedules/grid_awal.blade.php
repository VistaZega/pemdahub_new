@extends('layouts.admin')

@section('title', 'Jadwal Pelajaran')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
    body, .schedule-page { font-family: 'Inter', sans-serif; }

    /* === PAGE BACKGROUND === */
    .schedule-page { background: linear-gradient(135deg, #f8f7ff 0%, #fff0f9 50%, #f0f9ff 100%); padding: 24px 24px 8px; }

    /* === HERO HEADER === */
    .schedule-hero {
        background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 30%, #a21caf 70%, #db2777 100%);
        border-radius: 24px;
        padding: 28px 32px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px -10px rgba(109,40,217,0.4);
    }
    .schedule-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .hero-orb-1 { position:absolute; width:300px; height:300px; border-radius:50%; background:rgba(255,255,255,0.08); top:-100px; right:-50px; }
    .hero-orb-2 { position:absolute; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,0.06); bottom:-80px; left:30%; }

    /* === GLASS CARDS === */
    

    /* === FILTER FORM === */
    .filter-select {
        width: 100%; padding: 10px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        transition: all 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 18px;
        padding-right: 36px;
    }
    .filter-select:focus { outline: none; border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,0.12); }

    /* === TOOLBAR === */
    .toolbar-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }
    .toolbar-btn-primary { background: linear-gradient(135deg, #7c3aed, #db2777); color: white; box-shadow: 0 4px 12px rgba(124,58,237,0.3); }
    .toolbar-btn-primary:hover { box-shadow: 0 6px 20px rgba(124,58,237,0.4); transform: translateY(-1px); }
    .toolbar-btn-secondary { background: white; color: #374151; border: 1.5px solid #e5e7eb; }
    .toolbar-btn-secondary:hover { background: #f9fafb; border-color: #d1d5db; }
    .toolbar-btn-blue { background: linear-gradient(135deg, #2563eb, #7c3aed); color: white; box-shadow: 0 4px 12px rgba(37,99,235,0.3); }
    .toolbar-btn-green { background: linear-gradient(135deg, #059669, #10b981); color: white; box-shadow: 0 4px 12px rgba(5,150,105,0.25); }
    .toolbar-btn-blue:hover, .toolbar-btn-green:hover { transform: translateY(-1px); filter: brightness(1.05); }

    /* === ZOOM SLIDER === */
    .zoom-slider-wrap { display: flex; align-items: center; gap: 8px; }
    .zoom-slider-wrap input[type=range] {
        -webkit-appearance: none; height: 4px; border-radius: 4px; flex: 1;
        background: linear-gradient(to right, #7c3aed 0%, #7c3aed var(--zoom-pct, 100%), #e5e7eb var(--zoom-pct, 100%), #e5e7eb 100%);
    }
    .zoom-slider-wrap input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%;
        background: #7c3aed; cursor: pointer; box-shadow: 0 0 0 3px rgba(124,58,237,0.2);
    }

    /* === GRID TABLE === */
    #scheduleTable { border-collapse: separate !important; border-spacing: 0 !important; }
    #scheduleTable thead tr th { padding:0; }

    /* Day header column */
    .th-day { padding: 10px 12px; text-align:center; font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; border-right: 1px solid rgba(255,255,255,0.15); position:sticky;left:0;z-index:20; white-space:nowrap; background: #1e1b4b; color:white; }
    .th-time { padding: 10px 8px; text-align:center; font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; border-right: 1px solid rgba(255,255,255,0.15); width:88px; min-width:88px; background: #312e81; color:white; }
    .th-class { padding: 12px 6px; text-align:center; font-size:13px; font-weight:900; border-right: 1px solid rgba(255,255,255,0.25); white-space:nowrap; background: linear-gradient(135deg, #1e1b4b, #312e81); color:#f8fafc; letter-spacing:.04em; min-width: 150px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
    thead tr { background: linear-gradient(135deg,#6d28d9,#db2777); }

    /* Day cell */
    .day-colors { Senin:'#4f46e5'; }
    .cell-day {
        padding: 8px 12px;
        font-size: 16px; font-weight: 900; letter-spacing:.08em; text-transform:uppercase;
        border-right: 1px solid #e9d5ff;
        align-content: center; vertical-align: middle; text-align: center;
        position: sticky; left: 0; z-index: 10;
        white-space: nowrap;
        writing-mode: vertical-rl; text-orientation: mixed;
        transform: rotate(180deg);
        min-width: 44px;
    }
    .day-senin  { background: linear-gradient(180deg,#ede9fe,#ddd6fe); color:#4c1d95; border-right:2px solid #7c3aed; }
    .day-selasa { background: linear-gradient(180deg,#fce7f3,#fbcfe8); color:#831843; border-right:2px solid #db2777; }
    .day-rabu   { background: linear-gradient(180deg,#d1fae5,#a7f3d0); color:#064e3b; border-right:2px solid #059669; }
    .day-kamis  { background: linear-gradient(180deg,#fef3c7,#fde68a); color:#78350f; border-right:2px solid #d97706; }
    .day-jumat  { background: linear-gradient(180deg,#dbeafe,#bfdbfe); color:#1e3a8a; border-right:2px solid #2563eb; }
    .day-sabtu  { background: linear-gradient(180deg,#fae8ff,#f5d0fe); color:#701a75; border-right:2px solid #d946ef; }

    /* Time cell */
    .cell-time {
        padding: 6px 8px; vertical-align: middle; text-align: center;
        border-right: 1px solid #f0e6ff;
        background: #faf5ff;
        width: 88px; min-width: 88px;
    }
    .cell-time .slot-name { font-size:13px; font-weight:800; color: #4c1d95; }
    .cell-time .slot-time { font-size:11px; font-weight:700; color:#6b7280; margin-top:2px; }

    /* Schedule row */
    .schedule-row { border-bottom: 1px solid #f3e8ff; transition: background 0.15s; }
    .row-senin td.cell-empty { background-color: rgba(79, 70, 229, 0.04); }
    .row-selasa td.cell-empty { background-color: rgba(219, 39, 119, 0.04); }
    .row-rabu td.cell-empty { background-color: rgba(5, 150, 105, 0.04); }
    .row-kamis td.cell-empty { background-color: rgba(217, 119, 6, 0.04); }
    .row-jumat td.cell-empty { background-color: rgba(37, 99, 235, 0.04); }
    .row-sabtu td.cell-empty { background-color: rgba(217, 70, 239, 0.04); }
    .schedule-row:hover td.cell-empty { background: #fdf4ff !important; }
    .day-separator td { border-bottom: 2px solid #c4b5fd !important; }

    /* Content cell */
    .cell-content { padding: 3px; border-right: 1px solid #f0e6ff; height: 60px; vertical-align: middle; min-width: 150px; }
    .cell-empty { cursor: pointer; background: transparent; transition: background 0.15s; }
    .cell-empty:hover { background: #fdf4ff; }
    .cell-plus {
        display:flex; align-items:center; justify-content:center; height:100%;
        color:#d8b4fe; font-size:18px; opacity:0; transition: opacity 0.2s;
    }
    .cell-content:hover .cell-plus { opacity:1; }

    /* Non-teaching cell */
    .cell-break { background: repeating-linear-gradient(45deg, rgba(0,0,0,0.01), rgba(0,0,0,0.01) 10px, rgba(0,0,0,0.03) 10px, rgba(0,0,0,0.03) 20px); }

    /* === SCHEDULE CARD === */
    .scard {
        display: flex; flex-direction: row; align-items: stretch;
        border-radius: 12px; overflow: hidden;
        height: calc(100% - 0px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1);
        position: relative; z-index: 1;
        border: 1.5px solid;
    }
    .scard:hover { transform: scale(1.12) translateY(-2px); z-index: 50; box-shadow: 0 12px 32px rgba(0,0,0,0.18); }

    /* Photo side */
    .scard-photo { width: 40px; flex-shrink:0; overflow:hidden; display:flex; align-items:center; justify-content:center; }
    .scard-photo img { width:100%; height:100%; object-fit:cover; }
    .scard-initials { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; color:white; letter-spacing:-.5px; }

    /* Info side */
    .scard-info { flex:1; display:flex; flex-direction:column; justify-content:center; align-items:center; padding: 3px 4px; min-width:0; }
    .scard-code { font-size:26px; font-weight:900; line-height:1; letter-spacing:-1.5px; truncate:ellipsis; max-width:100%; overflow:hidden; white-space:nowrap; color: #111827 !important; text-shadow: 0 1px 2px rgba(255,255,255,0.8); }
    .scard-jam  { font-size:10px; font-weight:700; color: #374151; margin-top:3px; display:inline-block; }

    /* Compact Mode */
    .compact-mode .scard-photo { width: 24px; }
    .compact-mode .scard-initials { font-size: 8px; }
    .compact-mode .scard-code  { font-size: 10px; }
    .compact-mode .scard-jam   { font-size: 7px; padding: 0 3px; }
    .compact-mode .cell-content { height: 40px; }
    .compact-mode .cell-time .slot-name { font-size: 9px; }
    .compact-mode .cell-time .slot-time { font-size: 8px; }

    /* Scrollbar */
    #scheduleContainer::-webkit-scrollbar { height: 6px; width: 6px; }
    #scheduleContainer::-webkit-scrollbar-track { background: #f3f4f6; }
    #scheduleContainer::-webkit-scrollbar-thumb { background: #c4b5fd; border-radius: 3px; }
    #scheduleContainer::-webkit-scrollbar-thumb:hover { background: #7c3aed; }
</style>

<div class="schedule-page">
    <!-- ╔══════════════════════════════╗
    ║        HERO HEADER              ║
    ╚══════════════════════════════╝ -->
    <div class="schedule-hero mb-5">
        <div class="hero-orb-1"></div>
        <div class="hero-orb-2"></div>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Jadwal Pelajaran</h1>
                        <p class="text-purple-200 text-sm font-medium">Grid jadwal interaktif per kelas &amp; hari</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.schedules.print', array_filter(['school_id'=>$selectedSchoolId,'academic_year_id'=>$selectedYearId,'semester'=>request('semester','ganjil'),'shift'=>request('shift','all'),'grade_level'=>request('grade_level','all')])) }}"
                   target="_blank"
                   class="toolbar-btn toolbar-btn-blue">
                    <i class="fas fa-print"></i> Cetak / PDF
                </a>
                <a href="{{ route('admin.schedules.export', array_filter(['school_id'=>$selectedSchoolId,'academic_year_id'=>$selectedYearId,'semester'=>request('semester','ganjil'),'shift'=>request('shift','all')])) }}"
                   class="toolbar-btn toolbar-btn-green">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('admin.assignments.teaching.index') }}"
                   class="toolbar-btn" style="background:rgba(255,255,255,0.18);color:white;backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.3);">
                    <i class="fas fa-users"></i> Per Guru
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center"><i class="fas fa-check text-emerald-600"></i></div>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center"><i class="fas fa-times text-red-600"></i></div>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="mb-4 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800">
        <div class="flex items-center gap-2 font-semibold mb-1"><i class="fas fa-exclamation-triangle"></i> Gagal menyimpan:</div>
        <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <!-- ╔══════════════════════════════╗
    ║      FILTER + TOOLBAR           ║
    ╚══════════════════════════════╝ -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg p-4 mb-4">
        <form method="GET" action="{{ route('admin.schedules.grid') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tahun Ajaran</label>
                <select name="academic_year_id" class="filter-select">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                            {{ $year->year }}{{ $year->is_active ? ' ✓' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Semester</label>
                <select name="semester" class="filter-select">
                    <option value="ganjil" {{ request('semester','ganjil')=='ganjil'?'selected':'' }}>Ganjil</option>
                    <option value="genap"  {{ request('semester')=='genap'?'selected':'' }}>Genap</option>
                </select>
            </div>
            @if(auth()->user()->isSuperAdmin())
            <div class="flex-1 min-w-[160px]">
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Unit Sekolah</label>
                <select name="school_id" class="filter-select">
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ $selectedSchoolId==$school->id?'selected':'' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex-1 min-w-[120px]">
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tingkat</label>
                <select name="grade_level" class="filter-select">
                    <option value="all" {{ $selectedGradeLevel === 'all' ? 'selected' : '' }}>Semua Tingkat</option>
                    @foreach($availableGrades as $grade)
                        <option value="{{ $grade }}" {{ $selectedGradeLevel == $grade ? 'selected' : '' }}>Kelas {{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Shift KBM</label>
                <select name="shift" class="filter-select">
                    <option value="all" {{ ($selectedShift ?? 'all') === 'all' ? 'selected' : '' }}>Semua Shift</option>
                    <option value="pagi" {{ ($selectedShift ?? '') === 'pagi' ? 'selected' : '' }}>☀️ Shift Pagi (Reguler)</option>
                    <option value="siang" {{ ($selectedShift ?? '') === 'siang' ? 'selected' : '' }}>🌙 Shift Siang (Eksekutif)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="toolbar-btn toolbar-btn-primary">
                    <i class="fas fa-sync-alt"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>

    <!-- TOOLBAR CONTROLS -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg px-4 py-3 mb-4 flex flex-wrap items-center gap-4">
        <!-- Zoom -->
        <div class="zoom-slider-wrap flex-1 min-w-[180px]">
            <i class="fas fa-compress text-gray-400 text-sm"></i>
            <input type="range" id="zoomSlider" min="40" max="100" value="100" step="5">
            <i class="fas fa-expand text-gray-400 text-sm"></i>
            <span id="zoomValue" class="text-sm font-bold text-purple-700 min-w-[40px] text-right">100%</span>
        </div>
        <!-- Compact -->
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <span class="text-sm font-semibold text-gray-600">Mode Ringkas</span>
            <div class="relative">
                <input type="checkbox" id="compactToggle" class="sr-only peer">
                <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:bg-purple-600 transition-colors"></div>
                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow peer-checked:translate-x-5 transition-transform"></div>
            </div>
        </label>
        <div class="w-px h-6 bg-gray-200"></div>
        <!-- Fit screen -->
        <button id="fitToScreen" class="toolbar-btn toolbar-btn-blue">
            <i class="fas fa-expand-arrows-alt"></i> Fit Layar
        </button>
    </div>

    <!-- Schedule Grid -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto" id="scheduleContainer">
            <div id="scheduleWrapper" style="transform-origin: top left; transition: transform 0.3s ease;">
                <table class="w-full" id="scheduleTable">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="th-day sticky left-0 z-20">Hari</th>
                        <th class="th-time">Waktu</th>
                        @foreach($classrooms as $classroom)
                            <th class="th-class">
                                <div class="font-bold">{{ $classroom->class_name }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        $dayMapping = [
                            'Senin' => 'monday',
                            'Selasa' => 'tuesday',
                            'Rabu' => 'wednesday',
                            'Kamis' => 'thursday',
                            'Jumat' => 'friday',
                            'Sabtu' => 'saturday'
                        ];
                        
                        // Clean uniform style for all subjects to avoid color clutter
                        $scol = [
                            'from' => 'white', 
                            'to' => 'gray-50', 
                            'border' => 'indigo-400', 
                            'text' => 'gray-800', 
                            'badge' => 'indigo-600'
                        ];
                        
                        // Function to get color for subject
                        $getSubjectColor = function($subjectId) use ($scol) {
                            return $scol;
                        };

                        // NEW: Helper for non-teaching slot styles
                        $getNonTeachingStyle = function($slotName) {
                            $name = strtolower($slotName);
                            if (str_contains($name, 'apel literasi')) {
                                return ['icon' => 'fas fa-book-reader', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'];
                            } elseif (str_contains($name, 'apel')) {
                                return ['icon' => 'fas fa-flag', 'bg' => 'bg-red-50', 'text' => 'text-red-700'];
                            } elseif (str_contains($name, 'istirahat')) {
                                return ['icon' => 'fas fa-coffee', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700'];
                            } elseif (str_contains($name, 'show time') || str_contains($name, 'showtime')) {
                                return ['icon' => 'fas fa-star', 'bg' => 'bg-purple-50', 'text' => 'text-purple-700'];
                            } elseif (str_contains($name, 'senam')) {
                                return ['icon' => 'fas fa-running', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'];
                            } elseif (str_contains($name, 'sarapan') || str_contains($name, 'makan')) {
                                return ['icon' => 'fas fa-utensils', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700'];
                            } elseif (str_contains($name, 'upacara')) {
                                return ['icon' => 'fas fa-flag-checkered', 'bg' => 'bg-red-50', 'text' => 'text-red-700'];
                            }
                            return ['icon' => 'fas fa-clock', 'bg' => 'bg-gray-100', 'text' => 'text-gray-700'];
                        };
                    @endphp

                    @foreach($days as $day)
                        @php
                            $dayEnglish = $dayMapping[$day];
                            $daySlots = $timeSlots->where('day_of_week', $dayEnglish)->sortBy('slot_order');
                            $dayClass = ['Senin'=>'day-senin','Selasa'=>'day-selasa','Rabu'=>'day-rabu','Kamis'=>'day-kamis','Jumat'=>'day-jumat','Sabtu'=>'day-sabtu'][$day] ?? 'day-senin';
                        @endphp
                        
                        @if($daySlots->count() > 0)
                        @foreach($daySlots as $slotIndex => $timeSlot)
                                <tr class="schedule-row row-{{ strtolower($day) }} {{ $loop->last ? 'day-separator' : '' }}">
                                    @if($loop->first)
                                        <td rowspan="{{ $daySlots->count() }}" class="cell-day {{ $dayClass }} sticky left-0 z-10">
                                            {{ $day }}
                                        </td>
                                    @endif

                                    @php $nonTeachingStyle = !$timeSlot->is_teaching_slot ? $getNonTeachingStyle($timeSlot->slot_name) : null; @endphp
                                    <td class="cell-time {{ !$timeSlot->is_teaching_slot ? 'cell-break '.$nonTeachingStyle['bg'] : '' }}">
                                        <div class="slot-name">{{ $timeSlot->slot_name }}</div>
                                        <div class="slot-time">{{ substr($timeSlot->start_time,0,5) }}–{{ substr($timeSlot->end_time,0,5) }}</div>
                                    </td>

                                @foreach($classrooms as $classroom)
                                    @php
                                        $lookupKey = $dayEnglish . '_' . $timeSlot->id . '_' . $classroom->id;
                                        
                                        $schedulesInCell = $scheduleGrid[$lookupKey] ?? [];
                                        if (!is_array($schedulesInCell) && $schedulesInCell) {
                                            $schedulesInCell = [$schedulesInCell];
                                        }
                                        
                                        $blockedByScheduleIds = $blockedSlots[$lookupKey] ?? [];
                                        if (!is_array($blockedByScheduleIds) && $blockedByScheduleIds) {
                                            $blockedByScheduleIds = [$blockedByScheduleIds];
                                        }
                                        
                                        $blockedByPrevious = [];
                                        foreach($blockedByScheduleIds as $bsId) {
                                            $found = $schedules->firstWhere('id', $bsId);
                                            if ($found) $blockedByPrevious[] = $found;
                                        }
                                        
                                        $itemsToDisplay = [];
                                        foreach($blockedByPrevious as $b) {
                                            $itemsToDisplay[] = ['type' => 'blocked', 'schedule' => $b];
                                        }
                                        foreach($schedulesInCell as $s) {
                                            $itemsToDisplay[] = ['type' => 'start', 'schedule' => $s];
                                        }
                                    @endphp
                                    
                                    @if(count($itemsToDisplay) > 0 && $timeSlot->is_teaching_slot)
                                        <td class="cell-content border-r border-purple-50 p-1" style="border-color:#f5eeff; vertical-align: top;"
                                            onclick="openScheduleModal('{{ $day }}', {{ $timeSlot->id }}, {{ $classroom->id }}, null)">
                                            
                                            <div class="flex flex-col gap-1 min-h-full w-full">
                                                @foreach($itemsToDisplay as $item)
                                                    @php
                                                        $bc = $item['schedule'];
                                                        $colors = $getSubjectColor($bc->subject_id);
                                                        $scol = $colors;
                                                        
                                                        // Get specific hour sequence if blocked/continuation vs start
                                                        $seqHour = $hourSequences[$bc->id] ?? 1;
                                                        
                                                        // Badge for Block System
                                                        $blockBadge = '';
                                                        $blockTitle = '';
                                                        $blockColor = '';
                                                        
                                                        $isNormal = (!isset($currentRotation) || $currentRotation === 'normal');
                                                        
                                                        if($bc->teachingAssignment) {
                                                            if($bc->teachingAssignment->block_type == 'all') {
                                                                $blockBadge = $isNormal ? 'GROUP B' : 'GROUP A';
                                                                $blockTitle = 'Siswa yang belajar (Kelas Umum)';
                                                                $blockColor = 'bg-blue-100 border-blue-300 text-blue-700';
                                                            } elseif($bc->teachingAssignment->block_type == 'split') {
                                                                $blockBadge = $isNormal ? 'GROUP A' : 'GROUP B';
                                                                $blockTitle = 'Siswa yang belajar (Ruang Praktek)';
                                                                $blockColor = 'bg-orange-100 border-orange-300 text-orange-700';
                                                            } elseif($bc->teachingAssignment->block_type == 'parallel') {
                                                                $blockBadge = 'PARALEL';
                                                                $blockTitle = 'Pelajaran Paralel / Agama (Multi Mapel Concurrent)';
                                                                $blockColor = 'bg-purple-100 border-purple-300 text-purple-700';
                                                            }
                                                        }
                                                        
                                                        // If multiple items, make font smaller
                                                        $isMulti = count($itemsToDisplay) > 1;
                                                    @endphp
                                                    
                                                    <div class="flex-1 min-h-[50px] scard relative group flex flex-col justify-between p-1.5 bg-gradient-to-br from-{{ $scol['from'] }} to-{{ $scol['to'] }} border-l-[3px] border-l-{{ $scol['border'] }} border-t border-r border-b border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-all hover:scale-[1.02] cursor-pointer overflow-hidden" 
                                                         style="height: auto;"
                                                         title="{{ $bc->subject->name ?? '-' }} — {{ $bc->teacher->full_name ?? '-' }}"
                                                         onclick="event.stopPropagation(); openScheduleModal('{{ $day }}', {{ $timeSlot->id }}, {{ $classroom->id }}, {{ $bc->id }})">
                                                         
                                                        <div class="scard-photo absolute rounded-full shadow-sm" style="background: linear-gradient(180deg,rgba(0,0,0,0.08),rgba(0,0,0,0.2)); width:20px; height:20px; top:4px; right:4px;">
                                                            @if($bc->teacher && $bc->teacher->photo)
                                                                <img src="{{ asset('storage/'.$bc->teacher->photo) }}" alt="" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                                                            @else
                                                                <div class="scard-initials" style="background:transparent; opacity:0.9; font-size:9px;">{{ strtoupper(substr($bc->teacher->full_name ?? 'G',0,2)) }}</div>
                                                            @endif
                                                        </div>
                                                        <div class="scard-info flex flex-col justify-start w-full pr-5">
                                                            <div class="text-{{ $scol['text'] }} truncate font-extrabold" style="font-size: {{ $isMulti ? '10px' : '11px' }}; letter-spacing: -0.5px;">{{ $bc->subject->code ?? '-' }}</div>
                                                            <div class="flex items-center flex-wrap gap-0.5 mt-0.5">
                                                                <span class="px-1 py-[1px] bg-white border border-gray-200 rounded text-gray-700 shadow-sm" style="font-size: 8px; font-weight:700;">J-{{ $seqHour }}</span>
                                                                @if($bc->group_code) 
                                                                    <span class="px-1 py-[1px] bg-purple-100 border border-purple-300 text-purple-700 rounded font-bold shadow-sm" title="Kelas Gabungan" style="font-size: 8px;">GAB</span>
                                                                @endif 
                                                                @if($blockBadge)
                                                                    <span class="px-1 py-[1px] {{ $blockColor }} border rounded font-bold shadow-sm" title="{{ $blockTitle }}" style="font-size: 8px;">{{ $blockBadge }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                
                                                <!-- Add button for block scheduling -->
                                                <div class="mt-1.5 text-center w-full">
                                                    <div onclick="event.stopPropagation(); openScheduleModal('{{ $day }}', {{ $timeSlot->id }}, {{ $classroom->id }}, null)" 
                                                         class="text-[10px] bg-purple-50 text-purple-600 border border-purple-200 hover:bg-purple-600 hover:text-white hover:border-purple-600 rounded cursor-pointer font-bold py-1 px-2 transition-all shadow-sm flex items-center justify-center gap-1 w-full"
                                                         title="Tambah jadwal lain di jam ini (Sistem Blok)">
                                                        <i class="fas fa-plus"></i> Tambah Jadwal Blok
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @else
                                        <td class="cell-content {{ $timeSlot->is_teaching_slot ? 'cell-empty' : 'cell-break '.$nonTeachingStyle['bg'] }} border-r"
                                            style="border-color:#f5eeff;"
                                            @if($timeSlot->is_teaching_slot)
                                                onclick="openScheduleModal('{{ $day }}', {{ $timeSlot->id }}, {{ $classroom->id }}, null)"
                                            @endif>
                                            
                                            @if($timeSlot->is_teaching_slot)
                                                <div class="cell-plus"><i class="fas fa-plus"></i></div>
                                            @else
                                                <div class="flex flex-col items-center justify-center h-full gap-1.5 p-1">
                                                    <i class="{{ $nonTeachingStyle['icon'] }} text-lg {{ $nonTeachingStyle['text'] }} mb-0.5 shadow-sm"></i>
                                                    <span class="text-[11px] font-black {{ $nonTeachingStyle['text'] }} uppercase tracking-widest text-center leading-tight drop-shadow-sm">{{ $timeSlot->slot_name }}</span>
                                                </div>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                        @else
                            <tr>
                                <td class="cell-day {{ $dayClass }} sticky left-0 z-10">{{ $day }}</td>
                                <td colspan="{{ count($classrooms) + 1 }}" class="px-4 py-5 text-center text-gray-400 text-sm">
                                    <i class="fas fa-calendar-times mr-2"></i>
                                    Belum ada slot waktu untuk hari {{ $day }}.
                                    <a href="{{ route('admin.time-slots.index', ['school_id'=>$selectedSchoolId,'day'=>$dayEnglish]) }}" class="text-purple-600 font-semibold hover:underline ml-1">Tambahkan →</a>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Schedule -->
<div id="scheduleModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-4 rounded-t-2xl sticky top-0 z-10">
            <h3 class="text-xl font-bold" id="modalTitle">Tambah Jadwal</h3>
            <div id="modalContext" class="text-sm mt-2 space-y-1 opacity-90">
                <!-- Context info will be loaded here -->
            </div>
        </div>
        
        <form id="scheduleForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <div id="editWarning" class="hidden mx-6 mt-4 p-3 bg-amber-50 border border-amber-300 rounded-xl text-amber-800 text-sm shadow-sm flex items-start gap-3">
                <i class="fas fa-exclamation-triangle mt-0.5 text-amber-600"></i>
                <div>
                    <strong>Anda sedang dalam mode UBAH (Edit).</strong><br>
                    Menyimpan form ini akan <u>MENGGANTI</u> jadwal yang sudah ada. Jika Anda ingin menambah jadwal baru di jam yang sama (Sistem Blok / Grouping), silakan tutup form ini dan klik tombol <strong>"+ Tambah Jadwal Blok"</strong> di jadwal kelas.
                </div>
            </div>

            <input type="hidden" name="day_of_week" id="modalDay">
            <input type="hidden" name="time_slot_id" id="modalTimeSlot">
            <input type="hidden" name="classroom_id" id="modalClassroom">
            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
            <input type="hidden" name="semester" value="{{ request('semester', 'ganjil') }}">
            <input type="hidden" name="subject_id" id="selectedSubjectId">
            <input type="hidden" name="teacher_id" id="selectedTeacherId">
            <input type="hidden" name="teaching_assignment_id" id="selectedAssignmentId">
            
            <div class="p-6">
                <div id="loadingState" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-purple-500 border-t-transparent"></div>
                    <p class="text-gray-600 mt-4">Memuat data mata pelajaran...</p>
                </div>

                <div id="selectionState" class="hidden space-y-4">
                    <!-- Duration Selector -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-200">
                        <label class="block text-sm font-semibold text-purple-800 mb-3">
                            <i class="fas fa-clock mr-1"></i> Jumlah Jam Pelajaran (Berturut-turut)
                        </label>
                        <div class="flex overflow-x-auto gap-2 pb-2 hide-scrollbar">
                            @for($i=1; $i<=12; $i++)
                            <label class="relative flex-shrink-0" style="width: 60px;">
                                <input type="radio" name="duration_slots" value="{{ $i }}" {{ $i == 1 ? 'checked' : '' }} 
                                    class="peer sr-only" onchange="checkDurationAvailability()">
                                <div class="p-2 border-2 border-gray-300 rounded-lg text-center cursor-pointer peer-checked:border-purple-600 peer-checked:bg-purple-100 peer-checked:text-purple-800 hover:border-purple-400 transition-all">
                                    <div class="text-xl font-bold">{{ $i }}</div>
                                    <div class="text-[10px]">Jam</div>
                                </div>
                            </label>
                            @endfor
                        </div>
                        <style>
                            .hide-scrollbar::-webkit-scrollbar {
                                height: 6px;
                            }
                            .hide-scrollbar::-webkit-scrollbar-track {
                                background: #f1f1f1; 
                                border-radius: 10px;
                            }
                            .hide-scrollbar::-webkit-scrollbar-thumb {
                                background: #d8b4fe; 
                                border-radius: 10px;
                            }
                            .hide-scrollbar::-webkit-scrollbar-thumb:hover {
                                background: #c084fc; 
                            }
                        </style>
                        <div id="durationWarning" class="hidden mt-2 text-xs text-red-600">
                            <i class="fas fa-exclamation-triangle"></i> <span id="durationWarningText"></span>
                        </div>
                    </div>

                    <!-- Group Code (Optional) -->
                    <div class="bg-gradient-to-r from-gray-50 to-white rounded-xl p-4 border border-gray-200">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-layer-group mr-1 text-purple-500"></i> Kode Grup Gabungan (Opsional)
                        </label>
                        <input type="text" name="group_code" id="modalGroupCode" 
                               class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all text-sm" 
                               placeholder="Contoh: GRP-TE-10">
                        <p class="text-[10px] text-gray-400 mt-1">Kosongkan jika bukan kelas gabungan. Isi kode yang sama untuk kelas-kelas yang digabung.</p>
                    </div>

                    <!-- Multi-Class (Gabungan Kelas) -->
                    <div class="bg-gradient-to-r from-indigo-50 to-white rounded-xl p-4 border border-indigo-200" id="multiClassSection">
                        <label class="block text-sm font-semibold text-indigo-700 mb-2">
                            <i class="fas fa-users-viewfinder mr-1"></i> Pilih Kelas Tambahan (Untuk Kelas Gabungan)
                        </label>
                        <select name="additional_classrooms[]" id="additionalClassrooms" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all text-sm select2" multiple="multiple">
                            @foreach(($allClassrooms ?? $classrooms)->groupBy('grade_level') as $grade => $groupClassrooms)
                                <optgroup label="Tingkat {{ $grade }}">
                                    @foreach($groupClassrooms as $cls)
                                        <option value="{{ $cls->id }}" class="classroom-option-{{ $cls->id }}">{{ $cls->class_name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-500 mt-1">Pilih kelas lain yang belajar bersamaan. Sistem akan otomatis menyamakan Kode Grup dan menghindari bentrok antar-kelas ini.</p>
                    </div>


                    <!-- Teaching Assignments Section -->
                    <div id="assignmentsSection" class="hidden">
                        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl p-4 border border-emerald-200">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-emerald-800">
                                    <i class="fas fa-clipboard-list mr-1"></i> Pilih dari Penugasan
                                </h4>
                                <span id="assignmentCount" class="text-xs bg-emerald-600 text-white px-2 py-0.5 rounded-full"></span>
                            </div>
                            <p class="text-xs text-emerald-700 mb-3">
                                Pilih penugasan yang sudah direncanakan. Guru & mapel akan otomatis terisi.
                            </p>
                            <div id="assignmentsList" class="space-y-2 max-h-60 overflow-y-auto">
                                <!-- Assignment cards will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Separator between assignments and manual -->
                    <div id="manualSeparator" class="hidden">
                        <div class="relative flex items-center py-2">
                            <div class="flex-grow border-t border-gray-300"></div>
                            <button type="button" id="toggleManualBtn" onclick="toggleManualSelection()" class="flex-shrink-0 mx-4 text-xs font-semibold text-gray-500 hover:text-purple-600 transition-colors flex items-center gap-1">
                                <i class="fas fa-chevron-down" id="manualToggleIcon"></i>
                                <span id="manualToggleText">Atau pilih manual</span>
                            </button>
                            <div class="flex-grow border-t border-gray-300"></div>
                        </div>
                    </div>

                    <div id="manualSelectionWrapper">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200">
                            <p class="text-sm text-blue-800 font-semibold mb-2">
                                <i class="fas fa-info-circle"></i> Petunjuk:
                            </p>
                            <p class="text-xs text-blue-700">
                                Pilih jumlah jam pelajaran, lalu pilih mata pelajaran dan guru yang berkompeten. Guru dengan tanda <i class="fas fa-star text-yellow-400 mr-1"></i> adalah keahlian utama.
                            </p>
                        </div>

                        <div id="subjectsContainer" class="space-y-3 mt-4">
                            <!-- Subjects with teachers will be loaded here -->
                        </div>
                    </div>

                    <div id="noDataState" class="hidden text-center py-8">
                        <div class="text-gray-400 mb-2">
                            <i class="fas fa-inbox text-4xl"></i>
                        </div>
                        <p class="text-gray-600 font-semibold">Tidak ada mata pelajaran tersedia</p>
                        <p class="text-sm text-gray-500 mt-1">Pastikan kelas sudah memiliki jurusan/program keahlian dan mata pelajaran sudah ditambahkan</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-between gap-3 sticky bottom-0">
                <button type="button" onclick="closeScheduleModal()" class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button type="button" id="deleteBtn" onclick="deleteSchedule()" class="px-4 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-all hidden">
                    <i class="fas fa-trash"></i>
                </button>
                <button type="submit" id="submitBtn" class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentScheduleId = null;
let classroomData = null;
let manualSelectionCollapsed = false;
const timeSlots = @json($timeSlots);

async function openScheduleModal(day, timeSlotId, classroomId, scheduleId) {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    const title = document.getElementById('modalTitle');
    const deleteBtn = document.getElementById('deleteBtn');
    const loadingState = document.getElementById('loadingState');
    const selectionState = document.getElementById('selectionState');
    const contextDiv = document.getElementById('modalContext');
    
    // Set hidden fields
    document.getElementById('modalDay').value = day;
    document.getElementById('modalTimeSlot').value = timeSlotId;
    document.getElementById('modalClassroom').value = classroomId;
    document.getElementById('modalGroupCode').value = '';
    
    currentScheduleId = scheduleId;
    
    // Show modal immediately with loading state
    modal.classList.remove('hidden');
    loadingState.classList.remove('hidden');
    selectionState.classList.add('hidden');
    
    // Find time slot name
    const timeSlot = timeSlots.find(ts => ts.id == timeSlotId);
    const timeSlotName = timeSlot?.slot_name || '';
    const timeSlotTime = timeSlot ? (timeSlot.start_time.substring(0,5) + ' - ' + timeSlot.end_time.substring(0,5)) : '';
    
    if (scheduleId && scheduleId !== 'null') {
        // Edit mode
        title.textContent = 'Edit Jadwal';
        form.action = `{{ url('admin/schedules') }}/${scheduleId}/update-grid`;
        document.getElementById('formMethod').value = 'PUT';
        deleteBtn.classList.remove('hidden');
        document.getElementById('editWarning').classList.remove('hidden');
        document.getElementById('multiClassSection').classList.add('hidden'); // Sembunyikan multi-kelas di mode Edit
    } else {
        // Create mode
        title.textContent = 'Tambah Jadwal';
        form.action = '{{ route("admin.schedules.store-grid") }}';
        document.getElementById('formMethod').value = 'POST';
        deleteBtn.classList.add('hidden');
        document.getElementById('editWarning').classList.add('hidden');
        document.getElementById('multiClassSection').classList.remove('hidden'); // Tampilkan multi-kelas di mode Create
    }
    
    // Disable current classroom in additional_classrooms select (use vanilla JS to prevent jQuery ReferenceError)
    const additionalSelect = document.getElementById('additionalClassrooms');
    if (additionalSelect) {
        Array.from(additionalSelect.options).forEach(opt => opt.disabled = false); // enable all first
        const currentOpt = additionalSelect.querySelector(`option[value="${classroomId}"]`);
        if (currentOpt) currentOpt.disabled = true;
    }
    
    // Reset assignment selection
    document.getElementById('selectedAssignmentId').value = '';
    
    // Inisialisasi Select2 jika tersedia
    if (typeof jQuery !== 'undefined') {
        if (additionalSelect) {
            jQuery(additionalSelect).val(null).trigger('change'); // reset selection visually for select2
        }
        if (typeof jQuery.fn.select2 !== 'undefined') {
            jQuery('#additionalClassrooms').select2({
                placeholder: "Pilih kelas tambahan...",
                allowClear: true,
                width: '100%',
                dropdownParent: jQuery('#scheduleModal')
            });
        }
    }
    
    try {
        // Fetch subjects, teachers, AND teaching assignments for this classroom
        const response = await fetch('{{ route("admin.api.schedule.by-classroom") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ 
                classroom_id: classroomId,
                academic_year_id: '{{ $selectedYearId }}',
                semester: '{{ request("semester", "ganjil") }}'
            })
        });
        
        if (!response.ok) throw new Error('Failed to fetch data');
        
        const data = await response.json();
        classroomData = data;
        
        // Build context info
        let contextHTML = `
            <div class="flex items-center gap-2 text-sm">
                <i class="fas fa-calendar-day"></i> <strong>${day}</strong>
                <span class="mx-2">•</span>
                <i class="fas fa-clock"></i> ${timeSlotName} (${timeSlotTime})
            </div>
            <div class="flex items-center gap-2 text-sm">
                <i class="fas fa-school"></i> <strong>${data.classroom.name}</strong>
        `;
        
        if (data.classroom.major) {
            contextHTML += ` <span class="mx-2">•</span> <span class="bg-blue-500 bg-opacity-30 px-2 py-0.5 rounded text-xs">${data.classroom.major}</span>`;
        }
        
        if (data.classroom.program_keahlian) {
            contextHTML += ` <span class="mx-2">•</span> <span class="bg-orange-500 bg-opacity-30 px-2 py-0.5 rounded text-xs">${data.classroom.program_keahlian}</span>`;
        }
        
        if (data.classroom.konsentrasi_keahlian) {
            contextHTML += ` <span class="mx-2">•</span> <span class="bg-orange-400 bg-opacity-30 px-2 py-0.5 rounded text-xs text-xs">${data.classroom.konsentrasi_keahlian}</span>`;
        }
        
        contextHTML += `</div>`;
        contextDiv.innerHTML = contextHTML;
        
        // ============================================
        // Render Teaching Assignments (if any)
        // ============================================
        const assignmentsSection = document.getElementById('assignmentsSection');
        const assignmentsList = document.getElementById('assignmentsList');
        const assignmentCount = document.getElementById('assignmentCount');
        const manualSeparator = document.getElementById('manualSeparator');
        const manualWrapper = document.getElementById('manualSelectionWrapper');
        
        const assignments = data.assignments || [];
        // In edit mode, also show the current schedule's assignment even if complete
        const isEditMode = scheduleId && scheduleId !== 'null';
        const availableAssignments = assignments.filter(a => !a.is_complete);
        // For display: show incomplete + (in edit mode) all assignments so user can see current one
        const displayAssignments = isEditMode ? assignments : availableAssignments;
        
        if (displayAssignments.length > 0) {
            assignmentsSection.classList.remove('hidden');
            manualSeparator.classList.remove('hidden');
            assignmentCount.textContent = `${displayAssignments.length} penugasan`;
            assignmentsList.innerHTML = '';
            
            displayAssignments.forEach(assignment => {
                const progressPct = assignment.hours_per_week > 0 
                    ? Math.round((assignment.plotted_jp / assignment.hours_per_week) * 100) 
                    : 0;
                
                // Use full class names for Tailwind purge compatibility
                let progressTextClass, progressBarClass;
                if (assignment.is_complete) {
                    progressTextClass = 'text-gray-400';
                    progressBarClass = 'bg-gray-400';
                } else if (progressPct >= 75) {
                    progressTextClass = 'text-emerald-600';
                    progressBarClass = 'bg-emerald-500';
                } else if (progressPct >= 50) {
                    progressTextClass = 'text-amber-600';
                    progressBarClass = 'bg-amber-500';
                } else {
                    progressTextClass = 'text-blue-600';
                    progressBarClass = 'bg-blue-500';
                }
                
                const card = document.createElement('div');
                card.className = assignment.is_complete 
                    ? 'bg-gray-50 border-2 border-gray-200 rounded-xl p-3 cursor-pointer hover:border-gray-400 hover:shadow-md transition-all opacity-60'
                    : 'bg-white border-2 border-gray-200 rounded-xl p-3 cursor-pointer hover:border-emerald-400 hover:shadow-md transition-all';
                card.dataset.assignmentId = assignment.id;
                card.onclick = () => selectAssignment(assignment);
                
                const completeBadge = assignment.is_complete 
                    ? '<span class="text-xs bg-gray-400 text-white px-2 py-0.5 rounded-full ml-2">Lengkap</span>' 
                    : '';
                
                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-book ${assignment.is_complete ? 'text-gray-400' : 'text-emerald-600'} text-sm"></i>
                                <span class="font-bold ${assignment.is_complete ? 'text-gray-500' : 'text-gray-900'} text-sm truncate">${assignment.subject_name}</span>
                                ${assignment.subject_code ? `<span class="text-xs text-gray-400">(${assignment.subject_code})</span>` : ''}
                                ${assignment.group_code ? `<span class="text-[10px] bg-purple-600 text-white px-1.5 py-0.5 rounded font-bold ml-1 shadow-sm"><i class="fas fa-layer-group text-[9px]"></i> GAB</span>` : ''}
                                ${assignment.block_type === 'all' ? `<span class="text-[10px] bg-blue-600 text-white px-1.5 py-0.5 rounded font-bold ml-1 shadow-sm"><i class="fas fa-users text-[9px]"></i> KEL. A</span>` : ''}
                                ${assignment.block_type === 'split' ? `<span class="text-[10px] bg-orange-600 text-white px-1.5 py-0.5 rounded font-bold ml-1 shadow-sm"><i class="fas fa-columns text-[9px]"></i> KEL. B</span>` : ''}
                                ${assignment.block_type === 'parallel' ? `<span class="text-[10px] bg-purple-600 text-white px-1.5 py-0.5 rounded font-bold ml-1 shadow-sm"><i class="fas fa-layer-group text-[9px]"></i> PARALEL</span>` : ''}
                                ${completeBadge}
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-5 h-5 rounded-full overflow-hidden bg-purple-100 flex-shrink-0 border border-white">
                                    ${assignment.photo 
                                        ? `<img src="{{ asset('storage') }}/${assignment.photo}" class="w-full h-full object-cover">`
                                        : `<div class="w-full h-full flex items-center justify-center text-[10px] text-purple-600 font-bold">${assignment.teacher_name.charAt(0)}</div>`
                                    }
                                </div>
                                <span class="text-xs text-gray-600 truncate">${assignment.teacher_name}</span>
                            </div>
                        </div>
                        <div class="text-right ml-3 flex-shrink-0">
                            <div class="text-sm font-bold ${progressTextClass}">
                                ${assignment.plotted_jp}/${assignment.hours_per_week} JP
                            </div>
                            <div class="text-xs text-gray-500">
                                sisa ${assignment.remaining_jp} JP
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="${progressBarClass} h-1.5 rounded-full transition-all" style="width: ${progressPct}%"></div>
                        </div>
                    </div>
                `;
                
                assignmentsList.appendChild(card);
            });
            
            // If there are also complete assignments, show a summary
            const completeCount = assignments.filter(a => a.is_complete).length;
            if (completeCount > 0) {
                const completeSummary = document.createElement('div');
                completeSummary.className = 'text-xs text-gray-400 text-center mt-2 py-1';
                completeSummary.innerHTML = `<i class="fas fa-check-circle text-emerald-400"></i> ${completeCount} penugasan sudah lengkap JP-nya`;
                assignmentsList.appendChild(completeSummary);
            }
            
            // In edit mode, always show manual section too. In create mode, collapse it.
            if (isEditMode) {
                manualWrapper.classList.remove('hidden');
                manualSelectionCollapsed = false;
            } else {
                manualWrapper.classList.add('hidden');
                manualSelectionCollapsed = true;
            }
        } else {
            assignmentsSection.classList.add('hidden');
            manualSeparator.classList.add('hidden');
            manualWrapper.classList.remove('hidden');
            manualSelectionCollapsed = false;
        }
        
        // ============================================
        // Build subjects list with teachers (manual)
        // ============================================
        const container = document.getElementById('subjectsContainer');
        const noDataState = document.getElementById('noDataState');
        
        if (data.subjects.length === 0 && availableAssignments.length === 0) {
            loadingState.classList.add('hidden');
            selectionState.classList.remove('hidden');
            container.classList.add('hidden');
            noDataState.classList.remove('hidden');
            return;
        }
        
        container.innerHTML = '';
        container.classList.remove('hidden');
        noDataState.classList.add('hidden');
        
        // Group subjects by category
        const grouped = {};
        data.subjects.forEach(subject => {
            const cat = subject.category || 'Umum';
            if (!grouped[cat]) grouped[cat] = [];
            grouped[cat].push(subject);
        });
        
        Object.keys(grouped).sort().forEach(category => {
            // Category header
            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'mt-4 first:mt-0';
            categoryDiv.innerHTML = `
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">${category}</h4>
            `;
            container.appendChild(categoryDiv);
            
            grouped[category].forEach(subject => {
                const subjectCard = document.createElement('div');
                subjectCard.className = 'border-2 border-gray-200 rounded-xl p-4 hover:border-purple-400 transition-all cursor-pointer bg-white';
                subjectCard.dataset.subjectId = subject.id;
                
                let teachersHTML = '';
                if (subject.teachers.length === 0) {
                    teachersHTML = '<div class="text-xs text-red-500 mt-2"><i class="fas fa-exclamation-triangle"></i> Tidak ada guru berkompeten</div>';
                } else {
                    teachersHTML = '<div class="mt-3 space-y-2">';
                    subject.teachers.forEach(teacher => {
                        teachersHTML += `
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-all cursor-pointer">
                                <input type="radio" name="teacher_selection" value="${teacher.id}" data-subject-id="${subject.id}" 
                                    class="w-4 h-4 text-purple-600 focus:ring-purple-500" 
                                    onchange="selectTeacher(${teacher.id}, ${subject.id})">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full overflow-hidden bg-purple-100 flex-shrink-0 border border-white">
                                           ${teacher.photo 
                                               ? `<img src="{{ asset('storage') }}/${teacher.photo}" class="w-full h-full object-cover">`
                                               : `<div class="w-full h-full flex items-center justify-center text-[10px] text-purple-600 font-bold">${teacher.name.charAt(0)}</div>`
                                           }
                                        </div>
                                        <span class="font-semibold text-gray-800">${teacher.name}</span>
                                    </div>
                                </div>
                            </label>
                        `;
                    });
                    teachersHTML += '</div>';
                }
                
                subjectCard.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h5 class="font-bold text-gray-900 text-base">${subject.name}</h5>
                            <p class="text-xs text-gray-500 mt-1">
                                ${subject.teachers.length} guru berkompeten
                            </p>
                        </div>
                        <div class="text-sm font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-lg">
                            ${subject.category || 'Umum'}
                        </div>
                    </div>
                    ${teachersHTML}
                `;
                
                container.appendChild(subjectCard);
            });
        });
        
        // If editing, pre-select the current teacher/assignment
        if (scheduleId && scheduleId !== 'null') {
            const scheduleResponse = await fetch(`{{ url('admin/schedules') }}/${scheduleId}/edit-grid`);
            if (!scheduleResponse.ok) {
                throw new Error('Gagal memuat data jadwal');
            }
            const scheduleData = await scheduleResponse.json();
            
            // Pre-select duration_slots
            const durationRadio = document.querySelector(`input[name="duration_slots"][value="${scheduleData.duration_slots}"]`);
            if (durationRadio) {
                durationRadio.checked = true;
            }
            
            // Pre-select group_code
            document.getElementById('modalGroupCode').value = scheduleData.group_code || '';
            
            // Pre-select assignment if it was linked
            if (scheduleData.teaching_assignment_id) {
                const assignmentCard = document.querySelector(`[data-assignment-id="${scheduleData.teaching_assignment_id}"]`);
                const matchingAssignment = assignments.find(a => a.id === scheduleData.teaching_assignment_id);
                if (assignmentCard && matchingAssignment) {
                    selectAssignment(matchingAssignment);
                } else {
                    // Assignment card not found (maybe filtered out) — fallback to manual
                    const radio = document.querySelector(`input[type="radio"][value="${scheduleData.teacher_id}"]`);
                    if (radio) {
                        radio.checked = true;
                        selectTeacher(scheduleData.teacher_id, scheduleData.subject_id);
                    }
                    if (manualSelectionCollapsed) {
                        toggleManualSelection();
                    }
                }
            } else {
                // No assignment linked — pre-select teacher from manual list
                const radio = document.querySelector(`input[type="radio"][value="${scheduleData.teacher_id}"]`);
                if (radio) {
                    radio.checked = true;
                    selectTeacher(scheduleData.teacher_id, scheduleData.subject_id);
                    if (manualSelectionCollapsed) {
                        toggleManualSelection();
                    }
                }
            }
        }
        
        loadingState.classList.add('hidden');
        selectionState.classList.remove('hidden');
        
    } catch (error) {
        console.error('Schedule modal error:', error);
        alert('Gagal memuat data jadwal. Silakan coba lagi.');
        closeScheduleModal();
    }
}

function selectTeacher(teacherId, subjectId) {
    document.getElementById('selectedTeacherId').value = teacherId;
    document.getElementById('selectedSubjectId').value = subjectId;
    document.getElementById('selectedAssignmentId').value = '';
    document.getElementById('submitBtn').disabled = false;
    
    // Clear assignment selection highlight
    document.querySelectorAll('[data-assignment-id]').forEach(card => {
        card.classList.remove('border-emerald-500', 'bg-emerald-50', 'ring-2', 'ring-emerald-300');
        card.classList.add('border-gray-200');
    });
    
    // Highlight selected subject card
    document.querySelectorAll('[data-subject-id]').forEach(card => {
        card.classList.remove('border-purple-500', 'bg-purple-50');
        card.classList.add('border-gray-200', 'bg-white');
    });
    const selectedCard = document.querySelector(`[data-subject-id="${subjectId}"]`);
    if (selectedCard) {
        selectedCard.classList.remove('border-gray-200', 'bg-white');
        selectedCard.classList.add('border-purple-500', 'bg-purple-50');
    }
    
    // Check duration availability after teacher selection
    checkDurationAvailability();
}

function selectAssignment(assignment) {
    // Set hidden fields from assignment
    document.getElementById('selectedTeacherId').value = assignment.teacher_id;
    document.getElementById('selectedSubjectId').value = assignment.subject_id;
    document.getElementById('selectedAssignmentId').value = assignment.id;
    document.getElementById('modalGroupCode').value = assignment.group_code || '';
    document.getElementById('submitBtn').disabled = false;
    
    // Clear manual subject selection highlights
    document.querySelectorAll('[data-subject-id]').forEach(card => {
        card.classList.remove('border-purple-500', 'bg-purple-50');
        card.classList.add('border-gray-200', 'bg-white');
    });
    // Clear manual teacher radio selections
    document.querySelectorAll('input[name="teacher_selection"]').forEach(radio => {
        radio.checked = false;
    });
    
    // Highlight selected assignment card
    document.querySelectorAll('[data-assignment-id]').forEach(card => {
        card.classList.remove('border-emerald-500', 'bg-emerald-50', 'ring-2', 'ring-emerald-300');
        card.classList.add('border-gray-200');
    });
    const selectedCard = document.querySelector(`[data-assignment-id="${assignment.id}"]`);
    if (selectedCard) {
        selectedCard.classList.remove('border-gray-200');
        selectedCard.classList.add('border-emerald-500', 'bg-emerald-50', 'ring-2', 'ring-emerald-300');
    }
    
    // Check duration availability
    checkDurationAvailability();
}

function toggleManualSelection() {
    const wrapper = document.getElementById('manualSelectionWrapper');
    const icon = document.getElementById('manualToggleIcon');
    const text = document.getElementById('manualToggleText');
    
    manualSelectionCollapsed = !manualSelectionCollapsed;
    
    if (manualSelectionCollapsed) {
        wrapper.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
        text.textContent = 'Atau pilih manual';
    } else {
        wrapper.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
        text.textContent = 'Sembunyikan pilihan manual';
    }
}

function checkDurationAvailability() {
    const duration = parseInt(document.querySelector('input[name="duration_slots"]:checked')?.value || 1);
    const currentTimeSlotId = parseInt(document.getElementById('modalTimeSlot').value);
    const classroomId = parseInt(document.getElementById('modalClassroom').value);
    const day = document.getElementById('modalDay').value;
    const warning = document.getElementById('durationWarning');
    const warningText = document.getElementById('durationWarningText');
    
    if (duration === 1) {
        warning.classList.add('hidden');
        return;
    }
    
    // Get current time slot
    const currentSlot = timeSlots.find(ts => ts.id === currentTimeSlotId);
    if (!currentSlot) return;
    
    // Get schedules
    const schedules = @json($schedules);
    let blockedSlots = [];
    let foundTeachingSlots = 0;
    let searchOrder = currentSlot.slot_order;
    
    // Search for required teaching slots after current slot (skip breaks)
    while (foundTeachingSlots < (duration - 1)) {
        searchOrder++;
        
        // Find next slot at this order
        const nextSlot = timeSlots.find(ts => ts.slot_order === searchOrder);
        
        if (!nextSlot) {
            blockedSlots.push(`Tidak cukup slot tersedia (butuh ${duration} jam mengajar)`);
            break;
        }
        
        // Skip break/ceremony slots - only count teaching slots
        if (!nextSlot.is_teaching_slot) {
            continue; // Keep searching
        }
        
        // Check if this teaching slot is occupied
        const occupied = schedules.find(s => 
            s.classroom_id === classroomId && 
            s.day_of_week === day && 
            s.time_slot_id === nextSlot.id
        );
        
        if (occupied) {
            blockedSlots.push(`${nextSlot.slot_name} sudah terisi`);
            break;
        }
        
        foundTeachingSlots++;
    }
    
    if (blockedSlots.length > 0) {
        warningText.textContent = blockedSlots.join(', ');
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('selectedAssignmentId').value = '';
    
    // Reset assignment highlights
    document.querySelectorAll('[data-assignment-id]').forEach(card => {
        card.classList.remove('border-emerald-500', 'bg-emerald-50', 'ring-2', 'ring-emerald-300');
        card.classList.add('border-gray-200');
    });
    
    // Reset manual selection visibility
    manualSelectionCollapsed = false;
}

function deleteSchedule() {
    if (confirm('Yakin ingin menghapus jadwal ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('admin/schedules') }}/${currentScheduleId}/delete-grid`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal on outside click
document.getElementById('scheduleModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeScheduleModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('scheduleModal').classList.contains('hidden')) {
        closeScheduleModal();
    }
});

// ==========================================
// VIEW CONTROLS: Zoom & Compact Mode
// ==========================================

// Zoom Control
const zoomSlider = document.getElementById('zoomSlider');
const zoomValue = document.getElementById('zoomValue');
const scheduleWrapper = document.getElementById('scheduleWrapper');
const scheduleContainer = document.getElementById('scheduleContainer');

if (zoomSlider && scheduleWrapper) {
    // Load saved zoom level from localStorage
    const savedZoom = localStorage.getItem('scheduleZoom') || 100;
    zoomSlider.value = savedZoom;
    applyZoom(savedZoom);

    zoomSlider.addEventListener('input', function() {
        const zoomLevel = this.value;
        applyZoom(zoomLevel);
        localStorage.setItem('scheduleZoom', zoomLevel);
    });
}

function applyZoom(zoomLevel) {
    const scale = zoomLevel / 100;
    scheduleWrapper.style.transform = `scale(${scale})`;
    scheduleWrapper.style.marginBottom = scale < 1 ? `-${(1 - scale) * scheduleWrapper.offsetHeight}px` : '0';
    zoomValue.textContent = zoomLevel + '%';
    // Update slider gradient track
    zoomSlider.style.setProperty('--zoom-pct', zoomLevel + '%');
}

// Compact Mode Toggle
const compactToggle = document.getElementById('compactToggle');
const scheduleTable = document.getElementById('scheduleTable');

if (compactToggle && scheduleTable) {
    // Load saved compact mode from localStorage
    const savedCompact = localStorage.getItem('scheduleCompact') === 'true';
    compactToggle.checked = savedCompact;
    if (savedCompact) {
        applyCompactMode(true);
    }

    compactToggle.addEventListener('change', function() {
        const isCompact = this.checked;
        applyCompactMode(isCompact);
        localStorage.setItem('scheduleCompact', isCompact);
    });
}

function applyCompactMode(isCompact) {
    if (isCompact) {
        scheduleTable.classList.add('compact-mode');
    } else {
        scheduleTable.classList.remove('compact-mode');
    }
}

// Fit to Screen Button
const fitToScreenBtn = document.getElementById('fitToScreen');

if (fitToScreenBtn) {
    fitToScreenBtn.addEventListener('click', function() {
        const table = document.getElementById('scheduleTable');
        const container = scheduleContainer;
        
        if (table && container) {
            // Calculate optimal zoom level
            const containerWidth = container.offsetWidth;
            const tableWidth = table.offsetWidth;
            const containerHeight = window.innerHeight - 300; // Subtract header/controls height
            const tableHeight = table.offsetHeight;
            
            const widthRatio = (containerWidth / tableWidth) * 100;
            
            // Fit width only — let user scroll vertically
            let optimalZoom = Math.floor(Math.min(widthRatio, 100));
            
            // Enforce minimum zoom of 60% so table remains readable
            optimalZoom = Math.max(optimalZoom, 60);
            
            // Apply zoom
            zoomSlider.value = optimalZoom;
            applyZoom(optimalZoom);
            localStorage.setItem('scheduleZoom', optimalZoom);
            
            // Enable compact mode if needed
            if (optimalZoom < 70) {
                compactToggle.checked = true;
                applyCompactMode(true);
                localStorage.setItem('scheduleCompact', 'true');
            }
            
            // Show notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-down';
            notification.innerHTML = `<i class="fas fa-check-circle mr-2"></i>Zoom disesuaikan ke ${optimalZoom}%`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    });
}

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fade-in-down {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in-down {
        animation: fade-in-down 0.3s ease-out;
    }
    #scheduleWrapper {
        transform-origin: top left;
        transition: transform 0.3s ease;
    }
    .compact-mode {
        font-size: 10px;
    }
    .schedule-card {
        position: relative;
        z-index: 10;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .schedule-card:hover {
        z-index: 100;
        transform: scale(1.15) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    }
    .schedule-cell-content {
        overflow: visible !important;
    }
    #scheduleTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    .schedule-row {
        height: 60px; /* Fixed height for rows */
    }
    .schedule-cell-day, .schedule-cell-time {
        height: inherit;
    }
    /* Day Separator Style */
    .day-separator td {
        border-bottom: 3px solid #374151 !important; /* Firm Dark Gray/Slate Border */
    }
    /* Ensure sticky cells also get the separator border */
    .day-separator .schedule-cell-day,
    .day-separator .schedule-cell-time {
        border-bottom: 3px solid #374151 !important;
    }
`;
document.head.appendChild(style);
</script>
@endsection
