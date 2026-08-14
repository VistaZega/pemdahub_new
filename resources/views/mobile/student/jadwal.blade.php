@extends('mobile.layouts.app')

@section('title', 'Jadwal 3D - Mobile Pro')

@section('content')
<div class="space-y-4" x-data="{ day: '{{ $activeDay }}' }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Jadwal Pelajaran 📅</h2>
            <p class="text-[11px] text-slate-500 font-bold">Kelas: {{ $classroom->class_name ?? 'Belum ada kelas' }}</p>
        </div>
        <a href="{{ route('mobile.dashboard') }}" class="text-xs text-blue-600 font-black hover:text-blue-700">
            <i class="fa-solid fa-arrow-left"></i> Beranda
        </a>
    </div>

    <!-- Day Pills Filter (Clay Pills) -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
        @foreach($days as $d)
            <button @click="day = '{{ $d }}'" 
                    :class="day === '{{ $d }}' ? 'clay-blue text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                    class="px-4 py-2 rounded-2xl text-xs whitespace-nowrap transition">
                {{ $dayLabels[$d] }}
            </button>
        @endforeach
    </div>

    <!-- Schedule List per Selected Day -->
    @foreach($days as $d)
        <div x-show="day === '{{ $d }}'" class="space-y-2.5">
            @forelse($schedulesByDay[$d] as $sch)
                <div class="clay-card p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-11 h-11 rounded-2xl clay-blue flex items-center justify-center font-black text-lg shadow-sm">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-900">{{ $sch->subject->name ?? 'Mata Pelajaran' }}</h4>
                            <span class="text-[10px] text-slate-500 font-bold"><i class="fa-regular fa-user mr-1 text-blue-600"></i>{{ $sch->teacher->full_name ?? 'Pengajar' }}</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="px-3 py-1 rounded-xl bg-blue-50 text-[10px] text-blue-800 font-black border border-blue-200">
                            {{ $sch->timeSlot ? ($sch->timeSlot->start_time . ' - ' . $sch->timeSlot->end_time) : ($sch->start_time ?? '-') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                    <i class="fa-solid fa-calendar-xmark text-3xl mb-1 text-slate-400"></i>
                    <p>Tidak ada jadwal pelajaran di hari {{ $dayLabels[$d] }}.</p>
                </div>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
