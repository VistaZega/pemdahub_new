@extends('mobile.layouts.app')

@section('title', 'Jadwal Mengajar - Guru Mobile')

@section('content')
<div class="space-y-4" x-data="{ day: '{{ $activeDay }}' }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-extrabold text-white">Jadwal Mengajar Guru</h2>
            <p class="text-[11px] text-slate-400">Pegawai: {{ $teacher->full_name ?? '-' }}</p>
        </div>
    </div>

    <!-- Day Filter Pills -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
        @foreach($days as $d)
            <button @click="day = '{{ $d }}'" 
                    :class="day === '{{ $d }}' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-900 text-slate-400 border border-slate-800'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition">
                {{ $dayLabels[$d] }}
            </button>
        @endforeach
    </div>

    <!-- Schedules List -->
    @foreach($days as $d)
        <div x-show="day === '{{ $d }}'" class="space-y-2.5">
            @forelse($schedulesByDay[$d] as $sch)
                <div class="glass-card rounded-2xl p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white">{{ $sch->subject->name ?? 'Mata Pelajaran' }}</h4>
                            <span class="text-[10px] text-slate-400"><i class="fa-solid fa-school mr-1"></i>Kelas: {{ $sch->classroom->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="px-2 py-1 rounded-md bg-slate-800 text-[10px] text-purple-300 font-bold border border-slate-700">
                            {{ $sch->timeSlot ? ($sch->timeSlot->start_time . ' - ' . $sch->timeSlot->end_time) : ($sch->start_time ?? '-') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                    <i class="fa-solid fa-calendar-check text-2xl mb-1 text-slate-600"></i>
                    <p>Tidak ada jadwal mengajar di hari {{ $dayLabels[$d] }}.</p>
                </div>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
