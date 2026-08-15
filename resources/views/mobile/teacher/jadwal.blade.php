@extends('mobile.layouts.app')

@section('title', 'Jadwal Mengajar Guru - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1" x-data="{ day: '{{ $activeDay }}' }">
    <!-- Header -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <div>
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide text-center">Jadwal Mengajar Guru</h2>
            <p class="text-[10px] text-slate-500 font-bold text-center truncate max-w-[200px]">{{ $teacher->full_name ?? '-' }}</p>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Day Filter Pills (Clay Pills) -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
        @foreach($days as $d)
            @php $count = $schedulesByDay[$d]->count(); @endphp
            <button @click="day = '{{ $d }}'" 
                    :class="day === '{{ $d }}' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                    class="px-3.5 py-2 rounded-2xl text-xs whitespace-nowrap transition flex items-center gap-1.5 shrink-0">
                <span>{{ $dayLabels[$d] }}</span>
                @if($count > 0)
                    <span class="w-4 h-4 rounded-full text-[9px] font-black flex items-center justify-center"
                          :class="day === '{{ $d }}' ? 'bg-white/30 text-white' : 'bg-purple-100 text-purple-800'">
                        {{ $count }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    <!-- Schedules List (Clay Cards) -->
    @foreach($days as $d)
        <div x-show="day === '{{ $d }}'" class="space-y-2.5">
            @forelse($schedulesByDay[$d] as $sch)
                @php
                    $subjectName = $sch->subject->name ?? ($sch->teachingAssignment->subject->name ?? 'Mata Pelajaran');
                    $className = $sch->classroom->name ?? ($sch->teachingAssignment->classroom->name ?? ($sch->classroom->class_name ?? '-'));
                    $timeDisplay = $sch->timeSlot 
                        ? ($sch->timeSlot->start_time . ' - ' . $sch->timeSlot->end_time) 
                        : (($sch->start_time && $sch->end_time) ? ($sch->start_time . ' - ' . $sch->end_time) : ($sch->start_time ?? '-'));
                @endphp
                <div class="clay-card p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-2xl clay-purple flex items-center justify-center font-black text-lg shadow-sm shrink-0">
                            <i class="fa-solid fa-chalkboard-user text-white"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-900 truncate">{{ $subjectName }}</h4>
                            <p class="text-[10px] text-slate-500 font-bold truncate flex items-center gap-1">
                                <i class="fa-solid fa-school text-purple-600"></i> Kelas: {{ $className }}
                            </p>
                            @if($sch->room)
                                <p class="text-[9px] text-purple-600 font-extrabold truncate">
                                    <i class="fa-solid fa-door-open mr-0.5"></i> {{ $sch->room }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="px-2.5 py-1 rounded-xl bg-purple-50 text-[10px] text-purple-900 font-black border border-purple-200 block">
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
                    <i class="fa-solid fa-calendar-check text-3xl mb-1 text-slate-300"></i>
                    <p>Tidak ada jadwal mengajar di hari {{ $dayLabels[$d] }}.</p>
                </div>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
