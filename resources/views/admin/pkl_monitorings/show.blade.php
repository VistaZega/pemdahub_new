@extends(auth()->user()->isKetuaYayasan() ? 'layouts.yayasan' : 'layouts.admin')

@section('title', 'Detail Monitoring Guru')
@section('page_title', 'Detail Laporan Monitoring - ' . $teacher->full_name)

@section('content')
<div class="space-y-8 animate-fade-in-up">
    <!-- Header Hero Section -->
    <div class="relative bg-white rounded-[2.5rem] p-6 md:p-8 mb-8 overflow-hidden shadow-2xl shadow-slate-200/50 border border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 group">
        <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-indigo-500 via-purple-500 to-pink-500"></div>
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 group-hover:bg-purple-50 transition-colors duration-700"></div>
        
        <div class="relative z-10 flex items-center gap-6 pl-4">
            <div class="w-20 h-20 rounded-[1.5rem] overflow-hidden shadow-xl shadow-indigo-200 transform group-hover:scale-105 group-hover:rotate-3 transition-all duration-300 shrink-0 border-2 border-white">
                <img src="{{ $teacher->photo_url }}" alt="{{ $teacher->full_name }}" class="w-full h-full object-cover">
            </div>
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">{{ $teacher->full_name }}</h2>
                <div class="flex flex-wrap items-center gap-3 mt-2">
                    <span class="px-3 py-1 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-chalkboard-teacher"></i> Pembimbing PKL
                    </span>
                    <span class="px-3 py-1 bg-slate-50 border border-slate-200 text-slate-600 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-id-card"></i> ID: {{ $teacher->employee_id ? $teacher->employee->nik : $teacher->id }}
                    </span>
                </div>
            </div>
        </div>
        
        @php
            $indexRoute = auth()->user()->isKetuaYayasan() ? route('yayasan.pkl_monitorings.index') : route('admin.pkl-alumni.monitorings.index');
        @endphp
        <a href="{{ $indexRoute }}" class="relative z-10 px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 rounded-2xl text-sm font-black hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all duration-300 flex items-center gap-2 shadow-sm hover:shadow-xl hover:-translate-y-1">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Kolom Kiri: Daftar Lokasi DUDI -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white/80 backdrop-blur-2xl rounded-[2rem] shadow-2xl shadow-violet-200/40 border border-violet-100 overflow-hidden sticky top-6 ring-1 ring-white/50">
                <div class="p-6 border-b border-violet-100/80 bg-gradient-to-r from-violet-50/50 to-white flex items-center justify-between">
                    <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-[1rem] bg-violet-50 text-violet-600 flex items-center justify-center border border-violet-100 shadow-sm"><i class="fas fa-map-marked-alt text-xl"></i></div>
                        Lokasi Bimbingan
                    </h3>
                    <span class="w-10 h-10 flex items-center justify-center bg-violet-600 text-white text-base font-black rounded-full shadow-lg shadow-violet-200">{{ count($placements) }}</span>
                </div>
                
                <div class="divide-y divide-slate-100/80 p-3">
                    @forelse($placements as $group)
                        @php 
                            $place = $group->first(); 
                            $dudiName = $place->dudi->name ?? 'Unknown DUDI';
                            $colors = [
                                ['icon' => 'text-indigo-500', 'borderHover' => 'hover:border-indigo-200', 'bgHover' => 'hover:bg-indigo-50/30'],
                                ['icon' => 'text-emerald-500', 'borderHover' => 'hover:border-emerald-200', 'bgHover' => 'hover:bg-emerald-50/30'],
                                ['icon' => 'text-rose-500', 'borderHover' => 'hover:border-rose-200', 'bgHover' => 'hover:bg-rose-50/30'],
                                ['icon' => 'text-amber-500', 'borderHover' => 'hover:border-amber-200', 'bgHover' => 'hover:bg-amber-50/30'],
                                ['icon' => 'text-sky-500', 'borderHover' => 'hover:border-sky-200', 'bgHover' => 'hover:bg-sky-50/30'],
                                ['icon' => 'text-fuchsia-500', 'borderHover' => 'hover:border-fuchsia-200', 'bgHover' => 'hover:bg-fuchsia-50/30'],
                            ];
                            $theme = $colors[abs(crc32($dudiName)) % count($colors)];
                        @endphp
                        <div class="p-5 {{ $theme['bgHover'] }} rounded-2xl transition-all duration-300 m-1 border border-transparent {{ $theme['borderHover'] }} hover:shadow-md">
                            <div class="flex justify-between items-start gap-3">
                                <div class="flex-1">
                                    <h4 class="font-extrabold text-slate-800 text-base flex items-start gap-2 leading-tight">
                                        <i class="fas fa-building {{ $theme['icon'] }} mt-1 shrink-0"></i> 
                                        <span>{{ $dudiName }}</span>
                                    </h4>
                                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                                        <span class="px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-xl flex items-center gap-1.5 shadow-sm"><i class="fas fa-clock text-slate-400"></i> Shift: {{ $place->shift ?: '-' }}</span>
                                        <span class="px-3 py-1.5 bg-blue-50 border border-blue-100 text-blue-700 rounded-xl flex items-center gap-1.5 shadow-sm"><i class="fas fa-users text-blue-400"></i> {{ $group->count() }} Siswa</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-5 pt-5 border-t border-slate-100">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Perangkat</span>
                                    @if($place->is_perangkat_ready)
                                        @if(isset($place->perangkat_file_path))
                                            <a href="{{ Storage::url($place->perangkat_file_path) }}" target="_blank" class="text-xs font-bold px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-xl hover:bg-emerald-500 hover:text-white transition-colors flex items-center gap-1.5 shadow-sm">
                                                <i class="fas fa-check-circle"></i> Siap (Unduh)
                                            </a>
                                        @else
                                            <span class="text-xs font-bold px-3 py-1.5 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-xl flex items-center gap-1.5 shadow-sm"><i class="fas fa-check-circle"></i> Siap</span>
                                        @endif
                                    @else
                                        <span class="text-xs font-bold px-3 py-1.5 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl flex items-center gap-1.5 shadow-sm"><i class="fas fa-times-circle"></i> Belum Siap</span>
                                    @endif
                                </div>
                                
                                <div class="space-y-3">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Daftar Siswa Bimbingan</span>
                                    @foreach($group as $studentPlacement)
                                        <div class="flex items-center gap-3 bg-white p-2.5 rounded-xl border border-slate-100 shadow-sm hover:border-violet-200 transition-colors">
                                            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-slate-100 shrink-0">
                                                <img src="{{ $studentPlacement->student->photo_url ?? asset('images/default-avatar.png') }}" class="w-full h-full object-cover" alt="Foto">
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-bold text-slate-700 truncate block">{{ $studentPlacement->student->full_name ?? 'Siswa' }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5 truncate block">{{ $studentPlacement->student->classroom->class_name ?? '-' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-slate-500">
                            <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center border-2 border-dashed border-slate-200 mb-4 shadow-inner">
                                <i class="fas fa-building-slash text-2xl text-slate-300"></i>
                            </div>
                            <p class="font-bold text-sm text-slate-600">Tidak ada data penempatan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Kolom Kanan: Riwayat Laporan -->
        <div class="lg:col-span-2">
            <div class="bg-white/80 backdrop-blur-2xl rounded-[2rem] shadow-2xl shadow-emerald-200/40 border border-emerald-100 overflow-hidden ring-1 ring-white/50">
                <div class="p-6 border-b border-emerald-100/80 bg-gradient-to-r from-emerald-50/50 to-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sticky top-0 z-10">
                    <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-[1rem] bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-sm"><i class="fas fa-file-invoice text-xl"></i></div>
                        Riwayat Laporan Monitoring
                    </h3>
                    <div class="px-5 py-2 bg-emerald-50 border border-emerald-100 text-emerald-600 text-sm font-black rounded-xl shadow-sm flex items-center gap-2">
                        <i class="fas fa-history"></i> Total: {{ $monitorings->total() }} Laporan
                    </div>
                </div>
                
                <div class="divide-y divide-slate-100/80 p-2">
                    @forelse($monitorings as $mon)
                        <div class="p-5 sm:p-6 hover:bg-slate-50/50 rounded-2xl transition-all duration-300 m-1 border border-transparent hover:border-slate-100 hover:shadow-md group">
                            <div class="flex flex-col md:flex-row justify-between gap-6">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-3 mb-3">
                                        <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-xl text-slate-700 text-sm font-black">
                                            <i class="far fa-calendar-alt text-slate-400"></i>
                                            {{ $mon->monitoring_date->format('d F Y') }}
                                        </div>
                                        <span class="px-3 py-1.5 bg-gradient-to-r from-indigo-500 to-violet-500 text-white text-[10px] font-black uppercase rounded-xl tracking-widest shadow-md">Periode {{ $mon->periode_ke }}</span>
                                        <span class="text-xs font-bold text-slate-400 px-2">{{ $mon->monitoring_date->format('l') }}</span>
                                    </div>
                                    
                                    @php
                                        $dudiName = $mon->dudi->name ?? 'Unknown DUDI';
                                        $colors = [
                                            ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-100', 'icon' => 'text-indigo-400'],
                                            ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'icon' => 'text-emerald-400'],
                                            ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100', 'icon' => 'text-rose-400'],
                                            ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100', 'icon' => 'text-amber-400'],
                                            ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-100', 'icon' => 'text-sky-400'],
                                            ['bg' => 'bg-fuchsia-50', 'text' => 'text-fuchsia-700', 'border' => 'border-fuchsia-100', 'icon' => 'text-fuchsia-400'],
                                        ];
                                        $theme = $colors[abs(crc32($dudiName)) % count($colors)];
                                    @endphp
                                    <div class="inline-flex items-center gap-2 px-4 py-2 {{ $theme['bg'] }} border {{ $theme['border'] }} {{ $theme['text'] }} rounded-xl text-xs font-bold mb-5 shadow-sm">
                                        <i class="fas fa-building {{ $theme['icon'] }}"></i> 
                                        {{ $dudiName }}
                                        @if($mon->shift) <span class="opacity-40 mx-1">|</span> Shift: {{ $mon->shift }} @endif
                                    </div>
                                    
                                    <div class="relative bg-white p-5 rounded-2xl border border-slate-100 shadow-sm shadow-slate-100/50 text-sm text-slate-600 leading-relaxed font-medium">
                                        <div class="absolute -top-3 left-5 text-4xl text-indigo-100 bg-white px-1 leading-none"><i class="fas fa-quote-left"></i></div>
                                        <p class="relative z-10 pt-2">{{ $mon->notes ?? 'Tidak ada catatan monitoring.' }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex md:flex-col gap-4 shrink-0 mt-4 md:mt-0">
                                    @if($mon->photo_path)
                                        <div x-data="{ open: false }" class="relative group/photo">
                                            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-[1.5rem] overflow-hidden border-4 border-white shadow-lg cursor-pointer relative z-10" @click="open = true">
                                                <img src="{{ Storage::url($mon->photo_path) }}" class="w-full h-full object-cover group-hover/photo:scale-110 transition-transform duration-500" alt="Foto Bukti">
                                                <div class="absolute inset-0 bg-slate-900/0 group-hover/photo:bg-slate-900/30 transition-colors flex items-center justify-center backdrop-blur-[2px] opacity-0 group-hover/photo:opacity-100">
                                                    <i class="fas fa-search-plus text-white text-3xl drop-shadow-lg transform scale-50 group-hover/photo:scale-100 transition-all duration-300"></i>
                                                </div>
                                            </div>
                                            
                                            <!-- Lightbox -->
                                            <div x-show="open" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/90 backdrop-blur-xl p-4 md:p-10" @click="open = false" @keydown.escape.window="open = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                                <div class="relative max-w-5xl w-full flex justify-center" @click.stop>
                                                    <img src="{{ Storage::url($mon->photo_path) }}" class="max-w-full max-h-[85vh] rounded-[2rem] shadow-2xl ring-8 ring-white/10 object-contain" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90 translate-y-8" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                                                    <button @click="open = false" class="absolute -top-4 -right-4 w-12 h-12 bg-rose-500 hover:bg-rose-600 text-white rounded-full flex items-center justify-center shadow-lg transition-colors border-2 border-white z-50">
                                                        <i class="fas fa-times text-xl"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($mon->assignment_letter_path)
                                        <a href="{{ Storage::url($mon->assignment_letter_path) }}" target="_blank" class="flex flex-col items-center justify-center w-24 h-24 sm:w-32 sm:h-24 bg-gradient-to-br from-rose-50 to-orange-50 border-2 border-rose-100 text-rose-600 hover:from-rose-500 hover:to-orange-500 hover:text-white hover:border-transparent hover:shadow-lg hover:shadow-rose-200 rounded-[1.5rem] transition-all duration-300 group/doc relative overflow-hidden">
                                            <div class="absolute -right-4 -bottom-4 text-6xl text-rose-500/10 group-hover/doc:text-white/20 transition-colors transform group-hover/doc:-rotate-12"><i class="fas fa-file-pdf"></i></div>
                                            <i class="fas fa-file-pdf text-3xl mb-2 group-hover/doc:-translate-y-1 transition-transform relative z-10"></i>
                                            <span class="text-[10px] font-black uppercase tracking-widest relative z-10 bg-white/50 group-hover/doc:bg-white/20 px-2 py-0.5 rounded-md mt-1 backdrop-blur-sm">Surat Tugas</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-16 text-center">
                            <div class="w-24 h-24 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center text-4xl mx-auto mb-6 border-4 border-slate-100 border-dashed shadow-inner">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <h4 class="text-xl font-black text-slate-700 mb-2">Belum Ada Laporan</h4>
                            <p class="text-slate-500 font-bold text-sm">Guru ini belum mengirimkan laporan monitoring satupun. Silakan cek kembali nanti.</p>
                        </div>
                    @endforelse
                </div>

                @if($monitorings->hasPages())
                    <div class="p-6 border-t border-slate-100 bg-slate-50/80">
                        {{ $monitorings->links() }}
                    </div>
                @endif
            </div>
        </div>

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
