@extends('mobile.layouts.app')

@section('title', 'Jadwal Mengajar 3D - Guru Mobile')

@section('content')
<div class="space-y-4" x-data="{ day: '{{ $activeDay }}' }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Jadwal Mengajar Guru 👨‍🏫</h2>
            <p class="text-[11px] text-slate-500 font-bold">Pegawai: {{ $teacher->full_name ?? '-' }}</p>
        </div>
    </div>

    <!-- Day Filter Pills (Clay Pills) -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
        @foreach($days as $d)
            <button @click="day = '{{ $d }}'" 
                    :class="day === '{{ $d }}' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                    class="px-4 py-2 rounded-2xl text-xs whitespace-nowrap transition">
                {{ $dayLabels[$d] }}
            </button>
        @endforeach
    </div>

    <!-- Schedules List (Clay Cards) -->
    @foreach($days as $d)
        <div x-show="day === '{{ $d }}'" class="space-y-2.5">
            @forelse($schedulesByDay[$d] as $sch)
                <div class="clay-card p-4.5 flex items-center justify-between">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-11 h-11 rounded-2xl clay-purple flex items-center justify-center font-black text-lg shadow-sm">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900">{{ $sch->subject->name ?? 'Mata Pelajaran' }}</h4>
                            <span class="text-[10px] text-slate-500 font-bold"><i class="fa-solid fa-school mr-1 text-purple-600"></i>Kelas: {{ $sch->classroom->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="px-3 py-1 rounded-xl bg-purple-50 text-[10px] text-purple-900 font-black border border-purple-200">
                            {{ $sch->timeSlot ? ($sch->timeSlot->start_time . ' - ' . $sch->timeSlot->end_time) : ($sch->start_time ?? '-') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                    <i class="fa-solid fa-calendar-check text-3xl mb-1 text-slate-400"></i>
                    <p>Tidak ada jadwal mengajar di hari {{ $dayLabels[$d] }}.</p>
                </div>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
