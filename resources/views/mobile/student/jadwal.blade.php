@extends('mobile.layouts.app')

@section('title', 'Jadwal Pelajaran - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1" x-data="{ viewMode: 'harian', day: '{{ $activeDay }}' }">
    <!-- Header -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <div class="text-center">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Jadwal Pelajaran</h2>
            <p class="text-[10px] text-slate-500 font-bold truncate max-w-[200px]">Kelas: {{ $classroom->name ?? ($classroom->class_name ?? 'Belum terdaftar kelas') }}</p>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Toggle View Switch: Harian vs Roster 1 Minggu -->
    <div class="bg-slate-200/80 p-1 rounded-2xl flex items-center shadow-inner border border-slate-300/60">
        <button @click="viewMode = 'harian'" 
                :class="viewMode === 'harian' ? 'bg-white text-blue-700 font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-800'"
                class="flex-1 py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-95">
            <i class="fa-solid fa-calendar-day text-xs"></i>
            <span>Mode Harian</span>
        </button>
        <button @click="viewMode = 'roster'" 
                :class="viewMode === 'roster' ? 'bg-white text-purple-700 font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-800'"
                class="flex-1 py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-95">
            <i class="fa-solid fa-table-cells text-xs"></i>
            <span>Roster 1 Minggu</span>
        </button>
    </div>

    <!-- KETERANGAN STATUS PELAJARAN HARI INI (Live Class Card) -->
    @if($currentSchedule)
        @php
            $currSubj = $currentSchedule->subject->name ?? ($currentSchedule->teachingAssignment->subject->name ?? 'Mata Pelajaran');
            $currTeacher = $currentSchedule->teacher->full_name ?? ($currentSchedule->teachingAssignment->teacher->full_name ?? 'Guru Pengajar');
            $currTime = $currentSchedule->timeSlot 
                ? ($currentSchedule->timeSlot->start_time . ' - ' . $currentSchedule->timeSlot->end_time) 
                : (($currentSchedule->start_time && $currentSchedule->end_time) ? ($currentSchedule->start_time . ' - ' . $currentSchedule->end_time) : ($currentSchedule->start_time ?? '-'));
        @endphp
        <div class="clay-green p-4.5 space-y-2 border-2 border-emerald-300/80 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-900/40 text-emerald-100 border border-emerald-400/50">
                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                    Sedang Berlangsung Saat Ini
                </span>
                <span class="text-[10px] font-black text-emerald-950 bg-white/40 px-2 py-0.5 rounded-lg">
                    {{ $currTime }}
                </span>
            </div>

            <div>
                <h3 class="text-sm font-black text-white leading-snug">{{ $currSubj }}</h3>
                <p class="text-[11px] text-emerald-100 font-bold mt-0.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-chalkboard-user text-xs text-white"></i> {{ $currTeacher }}
                </p>
                @if($currentSchedule->room)
                    <p class="text-[10px] text-emerald-200 font-extrabold mt-0.5">
                        <i class="fa-solid fa-door-open mr-1"></i> Ruangan: {{ $currentSchedule->room }}
                    </p>
                @endif
            </div>
        </div>
    @elseif($nextSchedule)
        @php
            $nextSubj = $nextSchedule->subject->name ?? ($nextSchedule->teachingAssignment->subject->name ?? 'Mata Pelajaran');
            $nextTeacher = $nextSchedule->teacher->full_name ?? ($nextSchedule->teachingAssignment->teacher->full_name ?? 'Guru Pengajar');
            $nextTime = $nextSchedule->timeSlot 
                ? ($nextSchedule->timeSlot->start_time . ' - ' . $nextSchedule->timeSlot->end_time) 
                : (($nextSchedule->start_time && $nextSchedule->end_time) ? ($nextSchedule->start_time . ' - ' . $nextSchedule->end_time) : ($nextSchedule->start_time ?? '-'));
        @endphp
        <div class="clay-orange p-4.5 space-y-2 border-2 border-orange-300/80">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-white/30 text-orange-950 border border-white/40">
                    <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                    Pelajaran Terdekat / Akan Datang
                </span>
                <span class="text-[10px] font-black text-orange-950 bg-white/40 px-2 py-0.5 rounded-lg">
                    {{ $nextTime }}
                </span>
            </div>

            <div>
                <h3 class="text-sm font-black text-white leading-snug">{{ $nextSubj }}</h3>
                <p class="text-[11px] text-orange-100 font-bold mt-0.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-chalkboard-user text-xs text-white"></i> {{ $nextTeacher }}
                </p>
                @if($nextSchedule->room)
                    <p class="text-[10px] text-orange-200 font-extrabold mt-0.5">
                        <i class="fa-solid fa-door-open mr-1"></i> Ruangan: {{ $nextSchedule->room }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    <!-- ==================== 1. TAMPILAN MODE HARIAN ==================== -->
    <div x-show="viewMode === 'harian'" class="space-y-3">
        <!-- Day Pills Filter (Clay Pills) -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
            @foreach($days as $d)
                @php 
                    $count = $schedulesByDay[$d]->count(); 
                    $isHariIni = ($d === $today);
                @endphp
                <button @click="day = '{{ $d }}'" 
                        :class="day === '{{ $d }}' ? 'clay-blue text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                        class="px-3.5 py-2 rounded-2xl text-xs whitespace-nowrap transition flex items-center gap-1.5 shrink-0 relative">
                    @if($isHariIni)
                        <span class="w-2 h-2 rounded-full bg-emerald-400 absolute top-1.5 right-1.5"></span>
                    @endif
                    <span>{{ $dayLabels[$d] }}</span>
                    @if($count > 0)
                        <span class="w-4 h-4 rounded-full text-[9px] font-black flex items-center justify-center"
                              :class="day === '{{ $d }}' ? 'bg-white/30 text-white' : 'bg-blue-100 text-blue-800'">
                            {{ $count }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Schedule List per Selected Day -->
        @foreach($days as $d)
            <div x-show="day === '{{ $d }}'" class="space-y-2.5">
                <div class="flex items-center justify-between px-1">
                    <span class="text-xs font-black text-slate-700 uppercase tracking-wider">
                        Jadwal Hari {{ $dayLabels[$d] }} ({{ $schedulesByDay[$d]->count() }} Sesi)
                    </span>
                    @if($d === $today)
                        <span class="text-[9px] font-black bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full border border-emerald-300">
                            Hari Ini
                        </span>
                    @endif
                </div>

                @forelse($schedulesByDay[$d] as $sch)
                    @php
                        $subjectName = $sch->subject->name ?? ($sch->teachingAssignment->subject->name ?? 'Mata Pelajaran');
                        $teacherName = $sch->teacher->full_name ?? ($sch->teachingAssignment->teacher->full_name ?? 'Guru Pengajar');
                        $timeDisplay = $sch->timeSlot 
                            ? ($sch->timeSlot->start_time . ' - ' . $sch->timeSlot->end_time) 
                            : (($sch->start_time && $sch->end_time) ? ($sch->start_time . ' - ' . $sch->end_time) : ($sch->start_time ?? '-'));
                        
                        $isOngoing = ($sch->time_status === 'ongoing');
                        $isUpcoming = ($sch->time_status === 'upcoming');
                        $isCompleted = ($sch->time_status === 'completed');
                    @endphp
                    <div class="clay-card p-4 flex items-center justify-between gap-3 border-2 
                        {{ $isOngoing ? 'border-emerald-400 bg-emerald-50/40' : ($isUpcoming ? 'border-blue-200' : ($isCompleted ? 'border-slate-200 opacity-80' : 'border-slate-100')) }}">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-black text-lg shadow-sm shrink-0
                                {{ $isOngoing ? 'clay-green text-white' : ($isUpcoming ? 'clay-blue text-white' : 'clay-card text-slate-600') }}">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h4 class="text-xs font-black text-slate-900 truncate">{{ $subjectName }}</h4>
                                    @if($isOngoing)
                                        <span class="text-[8px] font-black bg-emerald-500 text-white px-1.5 py-0.5 rounded-md uppercase animate-pulse">
                                            Sedang Berlangsung
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="text-[8px] font-black bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded-md uppercase">
                                            Akan Datang
                                        </span>
                                    @elseif($isCompleted)
                                        <span class="text-[8px] font-black bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-md uppercase">
                                            Selesai
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[10px] text-slate-500 font-bold truncate flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-chalkboard-user text-blue-600"></i> {{ $teacherName }}
                                </p>
                                @if($sch->room)
                                    <p class="text-[9px] text-purple-600 font-extrabold truncate">
                                        <i class="fa-solid fa-door-open mr-0.5"></i> {{ $sch->room }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black border block
                                {{ $isOngoing ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : 'bg-blue-50 text-blue-800 border-blue-200' }}">
                                {{ $timeDisplay }}
                            </span>
                            @if($sch->duration_slots > 1)
                                <span class="text-[9px] text-slate-400 font-bold mt-0.5 block">
                                    {{ $sch->duration_slots }} JP
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-1">
                        <i class="fa-solid fa-calendar-xmark text-3xl mb-1 text-slate-300"></i>
                        <p>Tidak ada jadwal pelajaran di hari {{ $dayLabels[$d] }}.</p>
                    </div>
                @endforelse
            </div>
        @endforeach
    </div>

    <!-- ==================== 2. TAMPILAN MODE ROSTER 1 MINGGU ==================== -->
    <div x-show="viewMode === 'roster'" class="space-y-4" style="display: none;">
        <!-- Weekly Summary Hero Card -->
        <div class="clay-purple p-5 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">
                    Roster Mingguan Penuh
                </span>
                <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full">
                    Senin s/d Sabtu
                </span>
            </div>

            <h3 class="text-base font-black text-white leading-tight">
                Roster Jadwal Kelas {{ $classroom->name ?? ($classroom->class_name ?? '-') }}
            </h3>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-2 pt-1 border-t border-white/20 text-center text-white">
                <div class="p-1.5 bg-white/10 rounded-xl">
                    <span class="block text-xs font-black">{{ $totalWeeklySessions }}</span>
                    <span class="text-[8px] font-bold text-purple-100 uppercase">Total Sesi</span>
                </div>
                <div class="p-1.5 bg-white/10 rounded-xl">
                    <span class="block text-xs font-black">{{ $totalWeeklyJP }}</span>
                    <span class="text-[8px] font-bold text-purple-100 uppercase">Total JP</span>
                </div>
                <div class="p-1.5 bg-white/10 rounded-xl">
                    <span class="block text-xs font-black">{{ $totalUniqueSubjects }}</span>
                    <span class="text-[8px] font-bold text-purple-100 uppercase">Mata Pelajaran</span>
                </div>
            </div>
        </div>

        <!-- Weekly Day-by-Day Roster Cards -->
        <div class="space-y-3">
            @foreach($days as $d)
                @php 
                    $daySchedules = $schedulesByDay[$d]; 
                    $isHariIni = ($d === $today);
                @endphp
                <div class="clay-card p-4 space-y-2.5 border-2 {{ $isHariIni ? 'border-blue-400 bg-blue-50/20' : 'border-slate-200' }}">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-xl flex items-center justify-center font-black text-xs
                                {{ $isHariIni ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700' }}">
                                {{ substr($dayLabels[$d], 0, 1) }}
                            </div>
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-wide">{{ $dayLabels[$d] }}</h4>
                        </div>
                        <div class="flex items-center gap-1.5">
                            @if($isHariIni)
                                <span class="text-[8px] font-black bg-blue-600 text-white px-2 py-0.5 rounded-full uppercase">
                                    Hari Ini
                                </span>
                            @endif
                            <span class="text-[10px] font-black text-slate-500">
                                {{ $daySchedules->count() }} Pelajaran
                            </span>
                        </div>
                    </div>

                    @if($daySchedules->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($daySchedules as $sch)
                                @php
                                    $subj = $sch->subject->name ?? ($sch->teachingAssignment->subject->name ?? 'Mata Pelajaran');
                                    $teacher = $sch->teacher->full_name ?? ($sch->teachingAssignment->teacher->full_name ?? 'Guru Pengajar');
                                    $time = $sch->timeSlot 
                                        ? ($sch->timeSlot->start_time . ' - ' . $sch->timeSlot->end_time) 
                                        : (($sch->start_time && $sch->end_time) ? ($sch->start_time . ' - ' . $sch->end_time) : ($sch->start_time ?? '-'));
                                @endphp
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                                    <div class="min-w-0 pr-2">
                                        <p class="font-black text-slate-800 truncate">{{ $subj }}</p>
                                        <p class="text-[10px] text-slate-500 font-semibold truncate">{{ $teacher }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-[10px] font-black text-blue-700 block">{{ $time }}</span>
                                        @if($sch->room)
                                            <span class="text-[9px] text-purple-600 font-bold block">{{ $sch->room }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[11px] text-slate-400 font-bold text-center py-2 italic">Tidak ada jadwal pelajaran.</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
