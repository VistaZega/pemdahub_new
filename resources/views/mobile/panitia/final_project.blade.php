@extends('mobile.layouts.app')

@section('title', 'Panitia Project & Penelitian Akhir - PembdaHUB Mobile')

@section('content')
<div class="space-y-4 pt-1" x-data="{ 
    filterStatus: '{{ $statusFilter }}',
    filterStage: '{{ $stageFilter }}',
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
                    <h2 class="text-base font-black text-slate-900 leading-tight truncate">Panitia TA / Proyek</h2>
                    <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-900 text-[9px] font-black border border-purple-300">
                        {{ $activeAY->name ?? 'TP Aktif' }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-500 font-bold truncate">Review Proposal & Sidang Akhir</p>
            </div>
        </div>
        <div class="w-9 h-9 rounded-2xl bg-purple-50 border-2 border-purple-300 text-purple-700 flex items-center justify-center text-base font-black shrink-0">
            🛠️
        </div>
    </div>

    <!-- Quick Stats Clay Card -->
    <div class="clay-purple p-5 space-y-3.5 rounded-3xl shadow-md">
        <div class="flex items-center justify-between text-white">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/20 px-2.5 py-0.5 rounded-full border border-white/30">
                Monitoring Tugas Akhir
            </span>
            <span class="text-xs font-black bg-amber-400 text-slate-950 px-3 py-1 rounded-full border border-amber-300 shadow-2xs">
                Total: {{ $totalProjects }} Judul
            </span>
        </div>

        <div class="grid grid-cols-4 gap-1.5 text-center text-white">
            <a href="{{ route('mobile.panitia.final-project', ['status' => 'submitted']) }}" class="p-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 block hover:bg-white/30 transition">
                <span class="text-base font-black block leading-tight text-amber-300">{{ $submittedProjects }}</span>
                <span class="text-[8px] uppercase tracking-wide font-extrabold block mt-0.5">Proposal</span>
            </a>
            <a href="{{ route('mobile.panitia.final-project', ['status' => 'approved']) }}" class="p-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 block hover:bg-white/30 transition">
                <span class="text-base font-black block leading-tight text-blue-300">{{ $approvedProjects }}</span>
                <span class="text-[8px] uppercase tracking-wide font-extrabold block mt-0.5">Bimbingan</span>
            </a>
            <a href="{{ route('mobile.panitia.final-project', ['stage' => 'sidang']) }}" class="p-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 block hover:bg-white/30 transition">
                <span class="text-base font-black block leading-tight text-emerald-300">{{ $sidangProjects }}</span>
                <span class="text-[8px] uppercase tracking-wide font-extrabold block mt-0.5">Sidang</span>
            </a>
            <a href="{{ route('mobile.panitia.final-project', ['stage' => 'completed']) }}" class="p-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 block hover:bg-white/30 transition">
                <span class="text-base font-black block leading-tight text-purple-200">{{ $completedProjects }}</span>
                <span class="text-[8px] uppercase tracking-wide font-extrabold block mt-0.5">Lulus</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Form -->
    <form action="{{ route('mobile.panitia.final-project') }}" method="GET" class="clay-card p-4 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-2.5">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul project atau nama siswa..." 
                   class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600">
        </div>

        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('mobile.panitia.final-project', ['status' => 'all', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl text-[11px] font-black whitespace-nowrap transition {{ $statusFilter === 'all' && $stageFilter === 'all' ? 'clay-purple text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                Semua ({{ $totalProjects }})
            </a>
            <a href="{{ route('mobile.panitia.final-project', ['status' => 'submitted', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl text-[11px] font-black whitespace-nowrap transition {{ $statusFilter === 'submitted' ? 'bg-amber-400 text-slate-950 shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                Proposal Baru ({{ $submittedProjects }})
            </a>
            <a href="{{ route('mobile.panitia.final-project', ['stage' => 'sidang', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl text-[11px] font-black whitespace-nowrap transition {{ $stageFilter === 'sidang' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                Siap Sidang ({{ $sidangProjects }})
            </a>
            <a href="{{ route('mobile.panitia.final-project', ['stage' => 'completed', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl text-[11px] font-black whitespace-nowrap transition {{ $stageFilter === 'completed' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                Lulus ({{ $completedProjects }})
            </a>
        </div>
    </form>

    <!-- Project Cards List -->
    <div class="space-y-3">
        @forelse($projects as $prj)
            @php
                $statusBadges = [
                    'submitted' => 'bg-amber-100 text-amber-900 border-amber-300',
                    'approved' => 'bg-blue-100 text-blue-900 border-blue-300',
                    'revision' => 'bg-rose-100 text-rose-900 border-rose-300',
                    'rejected' => 'bg-slate-100 text-slate-800 border-slate-300',
                ];
                $badgeClass = $statusBadges[$prj->status] ?? 'bg-slate-100 text-slate-800 border-slate-300';
            @endphp
            <div class="clay-card p-4.5 bg-white border-2 border-slate-200 rounded-3xl shadow-sm hover:border-purple-300 transition space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-10 h-10 rounded-2xl {{ $prj->type === 'research' ? 'bg-rose-100 text-rose-700' : 'bg-purple-100 text-purple-700' }} flex items-center justify-center text-lg font-black shrink-0">
                            {{ $prj->type === 'research' ? '🔬' : '🚀' }}
                        </div>
                        <div class="min-w-0">
                            <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">
                                {{ $prj->type === 'research' ? 'Penelitian SMA' : 'Project SMK' }} • {{ $prj->current_stage_name }}
                            </span>
                            <h4 class="text-xs font-black text-slate-900 leading-snug mt-1 line-clamp-2">
                                {{ $prj->title }}
                            </h4>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border shrink-0 {{ $badgeClass }}">
                        {{ ucfirst($prj->status) }}
                    </span>
                </div>

                <!-- Members & Advisor Info -->
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5 text-[11px]">
                    <div class="flex items-center gap-1.5 text-slate-700 font-bold">
                        <i class="fa-solid fa-users text-purple-600 text-xs"></i>
                        <span class="truncate">Ketua: <strong>{{ $prj->student->full_name ?? '-' }}</strong> ({{ $prj->members->count() + 1 }} Anggota)</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-600 font-bold">
                        <i class="fa-solid fa-chalkboard-user text-blue-600 text-xs"></i>
                        <span class="truncate">Pembimbing: <strong>{{ $prj->advisor->full_name ?? '⚠️ Belum Ada Pembimbing' }}</strong></span>
                    </div>
                    @if($prj->examiner)
                        <div class="flex items-center gap-1.5 text-slate-500 font-bold text-[10px]">
                            <i class="fa-solid fa-graduation-cap text-emerald-600"></i>
                            <span class="truncate">Penguji: <strong>{{ $prj->examiner->full_name }}</strong></span>
                        </div>
                    @endif
                </div>

                <!-- Quick Action Buttons -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('mobile.panitia.final-project.show', $prj->id) }}" 
                       class="px-3.5 py-1.5 rounded-xl bg-purple-600 text-white text-xs font-black hover:bg-purple-700 active:scale-95 transition flex items-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-clipboard-check text-xs"></i> Review & Kelola
                    </a>

                    @if($prj->status === 'submitted')
                        <span class="text-[9px] font-black text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full animate-pulse">
                            ⚠️ Perlu Approval
                        </span>
                    @elseif($prj->current_stage === 'sidang')
                        <span class="text-[9px] font-black text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">
                            🎓 Siap Ujian
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 space-y-2 bg-white rounded-3xl">
                <div class="text-3xl">🛠️</div>
                <h4 class="text-sm font-black text-slate-800">Tidak Ada Data Tugas Akhir</h4>
                <p class="text-xs text-slate-400 font-bold">Belum ada proposal atau judul tugas akhir pada kriteria ini.</p>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="pt-2">
            {{ $projects->links() }}
        </div>
    </div>
</div>
@endsection
