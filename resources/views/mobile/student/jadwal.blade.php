@extends('mobile.layouts.app')

@section('title', 'Jadwal Pelajaran - Mobile')

@section('content')
<div class="space-y-4" x-data="{ day: '{{ $activeDay }}' }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-extrabold text-white">Jadwal Pelajaran</h2>
            <p class="text-[11px] text-slate-400">Kelas: {{ $classroom->name ?? 'Belum ada kelas' }}</p>
        </div>
        <a href="{{ route('mobile.dashboard') }}" class="text-xs text-indigo-400 hover:text-white">
            <i class="fa-solid fa-arrow-left"></i> Beranda
        </a>
    </div>

    <!-- Day Pills Filter -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
        @foreach($days as $d)
            <button @click="day = '{{ $d }}'" 
                    :class="day === '{{ $d }}' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-900 text-slate-400 border border-slate-800'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition">
                {{ $dayLabels[$d] }}
            </button>
        @endforeach
    </div>

    <!-- Schedule List per Selected Day -->
    @foreach($days as $d)
        <div x-show="day === '{{ $d }}'" class="space-y-2.5">
            @forelse($schedulesByDay[$d] as $sch)
                <div class="glass-card rounded-2xl p-3.5 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white">{{ $sch->subject->name ?? 'Mata Pelajaran' }}</h4>
                            <span class="text-[10px] text-slate-400"><i class="fa-regular fa-user mr-1"></i>{{ $sch->teacher->full_name ?? 'Pengajar' }}</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="px-2 py-1 rounded-md bg-slate-800 text-[10px] text-indigo-300 font-bold border border-slate-700">
                            {{ $sch->timeSlot ? ($sch->timeSlot->start_time . ' - ' . $sch->timeSlot->end_time) : ($sch->start_time ?? '-') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                    <i class="fa-solid fa-calendar-xmark text-2xl mb-1 text-slate-600"></i>
                    <p>Tidak ada jadwal pelajaran di hari {{ $dayLabels[$d] }}.</p>
                </div>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
