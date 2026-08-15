@extends('mobile.layouts.app')

@section('title', 'Kelola Panitia PKL - PembdaHUB Mobile')

@section('content')
<div class="space-y-4 pt-1" x-data="{ 
    filterStatus: '{{ $statusFilter }}',
    searchQuery: '{{ $search ?? '' }}'
}">
    <!-- Header Title -->
    <div class="flex items-center justify-between gap-2 px-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <h2 class="text-base font-black text-slate-900 leading-tight truncate">Panitia PKL</h2>
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[9px] font-black border border-amber-300">
                        {{ $activeAY->name ?? 'TP Aktif' }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-500 font-bold truncate">Kelola Penempatan & Pembimbing DUDI</p>
            </div>
        </div>
        <div class="w-9 h-9 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-800 flex items-center justify-center text-base font-black shrink-0">
            💼
        </div>
    </div>

    <!-- Quick Stats Clay Card -->
    <div class="clay-yellow p-5 space-y-3 rounded-3xl shadow-md">
        <div class="flex items-center justify-between text-slate-900">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/40 px-2.5 py-0.5 rounded-full border border-white/60">
                Statistik Penempatan Siswa
            </span>
            <span class="text-xs font-black bg-white px-3 py-1 rounded-full border border-amber-300 shadow-2xs">
                Total: {{ $totalPlacements }} Siswa
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2 text-center text-slate-950">
            <a href="{{ route('mobile.panitia.pkl', ['status' => 'active']) }}" class="p-2 rounded-2xl bg-white/40 backdrop-blur-md border border-white/60 block hover:bg-white/60 transition">
                <span class="text-lg font-black block leading-tight text-emerald-800">{{ $activePlacements }}</span>
                <span class="text-[9px] uppercase tracking-wide font-extrabold text-emerald-950">Aktif DUDI</span>
            </a>
            <a href="{{ route('mobile.panitia.pkl', ['status' => 'completed']) }}" class="p-2 rounded-2xl bg-white/40 backdrop-blur-md border border-white/60 block hover:bg-white/60 transition">
                <span class="text-lg font-black block leading-tight text-blue-800">{{ $completedPlacements }}</span>
                <span class="text-[9px] uppercase tracking-wide font-extrabold text-blue-950">Selesai</span>
            </a>
            <a href="{{ route('mobile.panitia.pkl', ['status' => 'pending']) }}" class="p-2 rounded-2xl bg-white/40 backdrop-blur-md border border-white/60 block hover:bg-white/60 transition">
                <span class="text-lg font-black block leading-tight text-rose-700">{{ $unassignedTeacher }}</span>
                <span class="text-[9px] uppercase tracking-wide font-extrabold text-rose-950">Belum Ada Guru</span>
            </a>
        </div>
    </div>

    <!-- Search and Status Filter Form -->
    <form action="{{ route('mobile.panitia.pkl') }}" method="GET" class="clay-card p-4 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-2.5">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama siswa atau perusahaan DUDI..." 
                   class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500">
        </div>

        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('mobile.panitia.pkl', ['status' => 'all', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl text-[11px] font-black whitespace-nowrap transition {{ $statusFilter === 'all' ? 'bg-amber-400 text-slate-950 shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                Semua ({{ $totalPlacements }})
            </a>
            <a href="{{ route('mobile.panitia.pkl', ['status' => 'active', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl text-[11px] font-black whitespace-nowrap transition {{ $statusFilter === 'active' ? 'bg-emerald-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                Aktif ({{ $activePlacements }})
            </a>
            <a href="{{ route('mobile.panitia.pkl', ['status' => 'completed', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl text-[11px] font-black whitespace-nowrap transition {{ $statusFilter === 'completed' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                Selesai ({{ $completedPlacements }})
            </a>
            <a href="{{ route('mobile.panitia.pkl', ['status' => 'pending', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl text-[11px] font-black whitespace-nowrap transition {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                Pending
            </a>
        </div>
    </form>

    <!-- Placement Cards List -->
    <div class="space-y-3">
        @forelse($placements as $plc)
            @php
                $statusBadges = [
                    'active' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    'completed' => 'bg-blue-100 text-blue-800 border-blue-300',
                    'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                    'canceled' => 'bg-rose-100 text-rose-800 border-rose-300',
                ];
                $badgeClass = $statusBadges[$plc->status] ?? 'bg-slate-100 text-slate-800 border-slate-300';
            @endphp
            <div class="clay-card p-4.5 bg-white border-2 border-slate-200 rounded-3xl shadow-sm hover:border-amber-400 transition space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-black shrink-0">
                            🎓
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-900 leading-tight truncate">
                                {{ $plc->student->full_name ?? 'Siswa' }}
                            </h4>
                            <p class="text-[9px] font-bold text-slate-400 mt-0.5 truncate">
                                NISN: {{ $plc->student->nisn ?? '-' }} • {{ $plc->student->school->name ?? 'Pembda' }}
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border shrink-0 {{ $badgeClass }}">
                        {{ ucfirst($plc->status) }}
                    </span>
                </div>

                <!-- DUDI & Teacher Info -->
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5 text-[11px]">
                    <div class="flex items-center gap-1.5 font-bold text-slate-800">
                        <i class="fa-solid fa-building text-amber-600 text-xs"></i>
                        <span class="truncate">DUDI: <strong>{{ $plc->dudi->name ?? ($plc->company_name ?? 'Belum Ditentukan') }}</strong></span>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-600 font-bold">
                        <i class="fa-solid fa-user-tie text-purple-600 text-xs"></i>
                        <span class="truncate">Pembimbing: <strong>{{ $plc->teacher->full_name ?? '⚠️ Belum Di-assign' }}</strong></span>
                    </div>
                    @if($plc->start_date && $plc->end_date)
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-bold">
                            <i class="fa-regular fa-calendar"></i>
                            <span>{{ $plc->start_date->format('d/m/Y') }} s.d. {{ $plc->end_date->format('d/m/Y') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Quick Action Buttons -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('mobile.panitia.pkl.show', $plc->id) }}" 
                       class="px-3.5 py-1.5 rounded-xl bg-amber-400 text-slate-950 text-xs font-black hover:bg-amber-300 active:scale-95 transition flex items-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-pen-to-square text-xs"></i> Kelola Penempatan
                    </a>

                    <span class="text-[10px] font-bold text-slate-400">
                        {{ $plc->shift ? 'Shift ' . ucfirst($plc->shift) : 'Reguler' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 space-y-2 bg-white rounded-3xl">
                <div class="text-3xl">💼</div>
                <h4 class="text-sm font-black text-slate-800">Tidak Ada Data Penempatan PKL</h4>
                <p class="text-xs text-slate-400 font-bold">Belum ada data siswa PKL yang sesuai dengan filter pencarian ini.</p>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="pt-2">
            {{ $placements->links() }}
        </div>
    </div>
</div>
@endsection
