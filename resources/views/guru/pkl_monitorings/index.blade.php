@extends('layouts.guru')

@section('title', 'Monitoring PKL')
@section('page_title', 'Monitoring PKL')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Daftar Tempat PKL (DUDI)</h2>
            <p class="text-sm text-slate-500 mt-1">Pilih DUDI untuk melihat detail siswa dan membuat laporan monitoring.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-800 text-xs font-black shadow-xs">
                <i class="fas fa-clock text-indigo-600"></i> Beban: {{ $pklHours ?? 0 }} JP
            </span>
            <a href="{{ route('guru.pkl.index') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-xs transition">
                <i class="fas fa-list text-emerald-600"></i> Daftar Siswa
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($groups as $group)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl shadow-sm">
                            <i class="fas fa-building"></i>
                        </div>
                        @if($group->is_perangkat_ready)
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full border border-emerald-200">
                                <i class="fas fa-check-circle mr-1"></i> Perangkat Siap
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full border border-amber-200">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Perangkat Belum
                            </span>
                        @endif
                    </div>
                    
                    <h3 class="text-lg font-bold text-slate-800 line-clamp-2" title="{{ $group->dudi->name ?? 'Unknown DUDI' }}">
                        {{ $group->dudi->name ?? 'Unknown DUDI' }}
                    </h3>
                    
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-clock w-5 text-slate-400"></i>
                            <span class="font-medium">Shift:</span>
                            <span class="ml-2">{{ $group->shift ?: 'Tidak ada shift' }}</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-600">
                            <i class="fas fa-users w-5 text-slate-400"></i>
                            <span class="font-medium">Siswa Dibimbing:</span>
                            <span class="ml-2 font-bold text-indigo-600">{{ $group->total_students }} Orang</span>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <a href="{{ route('guru.pkl_monitorings.show', [$group->dudi_id, $group->shift ?: 'null']) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-white border border-indigo-200 text-indigo-600 hover:bg-indigo-50 rounded-xl text-sm font-semibold transition-colors">
                        Lihat Detail & Lapor
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12 bg-white rounded-2xl border border-slate-200 border-dashed">
                    <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Tidak Ada Data PKL</h3>
                    <p class="text-slate-500 mt-2 max-w-md mx-auto">Anda belum ditugaskan sebagai guru pembimbing PKL untuk siswa manapun pada saat ini.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
