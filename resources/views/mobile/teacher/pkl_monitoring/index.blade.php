@extends('mobile.layouts.app')

@section('title', 'Monitoring PKL DUDI Mingguan - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Monitoring DUDI Mingguan</h2>
        <div class="w-9"></div>
    </div>

    <!-- Hero Card (Clay Blue) -->
    <div class="clay-blue p-5 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">
                Kunjungan DUDI PKL
            </span>
            <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full">
                {{ $activeYear->name ?? 'Tahun Aktif' }}
            </span>
        </div>
        <h3 class="text-base font-black text-white leading-tight">
            Laporan Kunjungan Industri Mingguan
        </h3>
        <p class="text-xs text-blue-100 font-medium leading-relaxed">
            Guru Pembimbing wajib melaporkan kunjungan ke DUDI setiap minggu sesuai jadwal beserta bukti surat tugas & foto.
        </p>
    </div>

    <!-- Assigned DUDI Groups List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                Lokasi DUDI Bimbingan ({{ $groups->count() }})
            </h3>
            <span class="text-[10px] font-black text-blue-600">Ketuk untuk lapor</span>
        </div>

        @forelse($groups as $group)
            @php
                $shiftLabel = $group->shift ? 'Shift ' . ucfirst($group->shift) : 'Shift Reguler';
                $dudiName = $group->dudi->name ?? 'DUDI';
                $dudiAddress = $group->dudi->address ?? 'Alamat DUDI';
            @endphp
            <a href="{{ route('mobile.guru.pkl.monitoring.show', [$group->dudi_id, $group->shift ?? 'null']) }}" 
               class="clay-card p-4 block hover:border-blue-300 transition active:scale-98 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shrink-0 font-black">
                            🏢
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-900 truncate leading-snug">{{ $dudiName }}</h4>
                            <p class="text-[10px] text-slate-500 font-bold truncate">{{ $dudiAddress }}</p>
                        </div>
                    </div>

                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-blue-100 text-blue-800 border border-blue-200 shrink-0">
                        {{ $shiftLabel }}
                    </span>
                </div>

                <!-- Stats Footer -->
                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 text-center">
                    <div class="p-1.5 bg-slate-50 rounded-xl">
                        <span class="block text-xs font-black text-slate-900">{{ $group->total_students }}</span>
                        <span class="text-[8px] font-black text-slate-500 uppercase">Siswa</span>
                    </div>
                    <div class="p-1.5 bg-blue-50 rounded-xl">
                        <span class="block text-xs font-black text-blue-700">{{ $group->visit_count ?? 0 }}x</span>
                        <span class="text-[8px] font-black text-blue-700 uppercase">Kunjungan</span>
                    </div>
                    <div class="p-1.5 {{ $group->is_perangkat_ready ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }} rounded-xl">
                        <span class="block text-xs font-black">{{ $group->is_perangkat_ready ? 'Ada' : 'Belum' }}</span>
                        <span class="text-[8px] font-black uppercase">Perangkat</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-2xl">🏢</div>
                <p>Belum ada lokasi DUDI yang ditugaskan kepada Anda.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
