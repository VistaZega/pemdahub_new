@extends(auth()->user()->isKetuaYayasan() ? 'layouts.yayasan' : 'layouts.admin')

@section('title', 'Laporan Monitoring PKL')
@section('page_title', 'Rekapitulasi Laporan Monitoring PKL Pembimbing')

@section('content')
<div class="space-y-8 animate-fade-in-up">
    <!-- Header Hero Section -->
    <div class="relative bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 rounded-[2rem] p-8 md:p-10 overflow-hidden shadow-2xl shadow-indigo-500/20 border border-indigo-500/30">
        <!-- Abstract Shapes -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-violet-400/20 rounded-full blur-2xl"></div>
        <div class="absolute top-1/2 right-1/4 w-24 h-24 bg-blue-400/20 rounded-full blur-2xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 text-indigo-100 text-xs font-bold uppercase tracking-widest mb-4 backdrop-blur-md">
                    <i class="fas fa-chart-pie text-indigo-300"></i> Analytics Dashboard
                </div>
                <h2 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">Performa Guru Pembimbing</h2>
                <p class="text-indigo-100/90 text-sm md:text-base font-medium max-w-2xl leading-relaxed">Pantau secara komprehensif kinerja Guru Pembimbing dalam melaksanakan kegiatan monitoring ke lokasi DUDI (Dunia Usaha dan Dunia Industri).</p>
            </div>
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-5 flex items-center gap-5 shadow-inner hover:bg-white/20 transition-all duration-300 group cursor-default">
                <div class="flex-shrink-0 w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-indigo-600 shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="pr-4">
                    <p class="text-xs text-indigo-100 font-bold uppercase tracking-widest mb-1">Total Guru</p>
                    <p class="text-3xl font-black text-white leading-none tracking-tight">{{ $teachers->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-[2rem] shadow-2xl shadow-indigo-100/40 border border-indigo-50 overflow-hidden ring-1 ring-slate-900/5">
        <div class="p-6 border-b border-indigo-50/60 bg-gradient-to-r from-indigo-50/50 via-white to-violet-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-3">
                <div class="w-12 h-12 rounded-[1rem] bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center shadow-lg shadow-indigo-200/50">
                    <i class="fas fa-list-ul"></i>
                </div>
                Daftar Pembimbing PKL
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80">
                    <tr class="border-b border-slate-100">
                        <th class="p-5 font-black text-xs text-slate-500 uppercase tracking-widest w-16 text-center">No</th>
                        <th class="p-5 font-black text-xs text-slate-400 uppercase tracking-widest">Identitas Guru</th>
                        <th class="p-5 font-black text-xs text-slate-400 uppercase tracking-widest">Penempatan DUDI</th>
                        <th class="p-5 font-black text-xs text-slate-400 uppercase tracking-widest text-center">Aktivitas Laporan</th>
                        <th class="p-5 font-black text-xs text-slate-400 uppercase tracking-widest text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($teachers as $index => $t)
                        <tr class="hover:bg-slate-50/80 transition-all duration-300 group">
                            <td class="p-5 text-center font-bold text-slate-500">
                                {{ $teachers->firstItem() + $index }}
                            </td>
                            <td class="p-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl overflow-hidden shadow-lg shadow-indigo-200 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300 shrink-0 border-2 border-indigo-100">
                                        <img src="{{ $t->photo_url }}" alt="{{ $t->full_name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-slate-800 text-base group-hover:text-indigo-600 transition-colors">{{ $t->full_name }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                                                <i class="fas fa-id-badge text-slate-400"></i>
                                                {{ $t->employee_id ? 'NIP/NUPTK: ' . $t->employee->nik : 'ID: ' . $t->id }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-5 align-top">
                                @php
                                    $uniquePlacements = $t->pklPlacements->unique(function($item) {
                                        return $item->dudi_id ? 'dudi_'.$item->dudi_id : 'name_'.$item->company_name;
                                    });
                                @endphp
                                @if($uniquePlacements->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($uniquePlacements as $placement)
                                            @php
                                                $dudiName = $placement->dudi->name ?? $placement->company_name ?? 'DUDI Belum Ditentukan';
                                            @endphp
                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-indigo-100 transition-all group/badge">
                                                <div class="w-5 h-5 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500 shrink-0 group-hover/badge:bg-indigo-500 group-hover/badge:text-white transition-colors">
                                                    <i class="fas fa-building text-[10px]"></i>
                                                </div>
                                                <span class="text-xs font-bold text-slate-700 truncate max-w-[150px]">{{ $dudiName }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-xs font-bold">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Belum Ditempatkan
                                    </div>
                                @endif
                            </td>
                            <td class="p-5 text-center align-middle">
                                @if($t->pkl_monitorings_count > 0)
                                    <div class="inline-flex flex-col items-center justify-center p-2 rounded-2xl group-hover:bg-emerald-50 transition-colors">
                                        <span class="text-2xl font-black text-emerald-500 leading-none group-hover:scale-125 transition-transform duration-300 drop-shadow-sm">{{ $t->pkl_monitorings_count }}</span>
                                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest mt-1 bg-emerald-100/50 px-2.5 py-0.5 rounded-full border border-emerald-200">Laporan</span>
                                    </div>
                                @else
                                    <div class="inline-flex flex-col items-center justify-center p-2 opacity-50 grayscale group-hover:grayscale-0 group-hover:opacity-100 transition-all">
                                        <span class="text-xl font-bold text-slate-400 leading-none">0</span>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Laporan</span>
                                    </div>
                                @endif
                            </td>
                            <td class="p-5 text-right align-middle">
                                @php
                                    $showRoute = auth()->user()->isKetuaYayasan() ? route('yayasan.pkl_monitorings.show', $t->id) : route('admin.pkl-alumni.monitorings.show', $t->id);
                                @endphp
                                <a href="{{ $showRoute }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border-2 border-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-indigo-600 hover:text-white hover:border-indigo-600 hover:shadow-xl hover:shadow-indigo-200 transition-all duration-300 group/btn">
                                    <span>Detail</span>
                                    <i class="fas fa-arrow-right text-xs opacity-70 group-hover/btn:translate-x-1 group-hover/btn:opacity-100 transition-all"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-16 text-center">
                                <div class="w-24 h-24 mx-auto bg-slate-50 rounded-full flex items-center justify-center border-2 border-dashed border-slate-200 mb-4 shadow-inner">
                                    <i class="fas fa-user-slash text-3xl text-slate-300"></i>
                                </div>
                                <h4 class="text-lg font-bold text-slate-700 mb-1">Data Kosong</h4>
                                <p class="text-slate-500 font-medium">Belum ada data guru pembimbing PKL saat ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($teachers->hasPages())
            <div class="p-5 border-t border-slate-100 bg-slate-50/80">
                {{ $teachers->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>
@endsection
