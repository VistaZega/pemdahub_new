@extends('layouts.admin')
@section('title', 'Kelola Penempatan PKL - Portal Admin')

@section('content')
<div class="space-y-6">
    {{-- Header Bar --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-900 via-indigo-800 to-violet-900 rounded-[2rem] shadow-2xl shadow-indigo-200/50 p-8">
        <!-- Dekorasi Background -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-violet-500/20 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-white flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-xl">
                        <i class="fas fa-briefcase text-2xl"></i>
                    </div>
                    Kelola Penempatan PKL
                </h1>
                <p class="text-indigo-100/80 mt-2 text-sm font-medium">
                    Mengatur dan memantau siswa magang di instansi/perusahaan mitra secara komprehensif.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pkl-alumni.placements.map') }}" class="group relative inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white font-bold px-6 py-3 rounded-2xl text-sm shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 w-0 bg-white/10 transition-all duration-300 ease-out group-hover:w-full"></div>
                    <i class="fas fa-map-marked-alt text-indigo-300 group-hover:text-white transition-colors"></i> 
                    <span class="relative">Peta Sebaran</span>
                </a>
                <a href="{{ route('admin.pkl-alumni.placements.create') }}" class="group relative inline-flex items-center gap-2 bg-gradient-to-r from-emerald-400 to-emerald-500 hover:from-emerald-300 hover:to-emerald-400 text-emerald-950 font-black px-6 py-3 rounded-2xl text-sm shadow-xl shadow-emerald-500/30 transition-all duration-300 hover:-translate-y-1">
                    <i class="fas fa-plus"></i> 
                    <span>Tambah Data</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-250 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="bg-white rounded-3xl shadow-xl border-2 border-black p-5">
        <form action="{{ route('admin.pkl-alumni.placements.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-black text-base"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama siswa, NISN, atau nama perusahaan DUDI..." 
                       class="w-full bg-slate-50 hover:bg-white border-2 border-black rounded-2xl pl-11 pr-4 py-3.5 text-sm font-black text-black placeholder:text-slate-400 focus:ring-4 focus:ring-black/20 focus:bg-white outline-none transition-all">
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 bg-black hover:bg-emerald-600 text-white font-black px-7 py-3.5 rounded-2xl text-xs uppercase tracking-wider transition-all border-2 border-black shadow-md">
                    <i class="fas fa-search text-amber-400"></i> Cari
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.pkl-alumni.placements.index') }}" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 bg-rose-100 hover:bg-rose-600 hover:text-white text-rose-800 font-black px-5 py-3.5 rounded-2xl text-xs uppercase tracking-wider transition-all border-2 border-black shadow-xs">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Placements Table Card --}}
    <div class="bg-white/90 backdrop-blur-2xl rounded-[2rem] shadow-2xl shadow-indigo-100/40 border border-indigo-50 overflow-hidden ring-1 ring-slate-900/5">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-indigo-900/5 backdrop-blur-sm border-b border-indigo-100/50">
                    <tr>
                        <th class="py-5 pl-6 font-black text-xs text-indigo-900 uppercase tracking-widest text-left">Siswa & Kelas</th>
                        <th class="py-5 font-black text-xs text-indigo-900 uppercase tracking-widest text-left">Industri & Lokasi</th>
                        <th class="py-5 font-black text-xs text-indigo-900 uppercase tracking-widest text-left">Pembimbing</th>
                        <th class="py-5 font-black text-xs text-indigo-900 uppercase tracking-widest text-left">Periode</th>
                        <th class="py-5 text-center font-black text-xs text-indigo-900 uppercase tracking-widest">Status</th>
                        <th class="py-5 text-right pr-6 font-black text-xs text-indigo-900 uppercase tracking-widest">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50/50 text-sm">
                    @forelse($placements as $p)
                        <tr class="hover:bg-indigo-50/50 transition-all duration-300 group">
                            <td class="py-5 pl-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-[1rem] overflow-hidden bg-slate-100 border-2 border-white shadow-md flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                        <img src="{{ $p->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $p->student->full_name }}">
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-slate-800 leading-tight">{{ $p->student->full_name }}</p>
                                        <p class="text-[11px] font-bold text-indigo-500 mt-1 flex items-center gap-1">
                                            <i class="fas fa-school"></i> {{ $p->student->school->name ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5">
                                @php
                                    $dudiName = $p->company_name ?? 'Unknown DUDI';
                                    $colors = [
                                        ['bg' => 'bg-indigo-100/50', 'text' => 'text-indigo-700', 'icon' => 'text-indigo-500'],
                                        ['bg' => 'bg-emerald-100/50', 'text' => 'text-emerald-700', 'icon' => 'text-emerald-500'],
                                        ['bg' => 'bg-rose-100/50', 'text' => 'text-rose-700', 'icon' => 'text-rose-500'],
                                        ['bg' => 'bg-amber-100/50', 'text' => 'text-amber-700', 'icon' => 'text-amber-500'],
                                        ['bg' => 'bg-sky-100/50', 'text' => 'text-sky-700', 'icon' => 'text-sky-500'],
                                        ['bg' => 'bg-fuchsia-100/50', 'text' => 'text-fuchsia-700', 'icon' => 'text-fuchsia-500'],
                                    ];
                                    $theme = $colors[abs(crc32($dudiName)) % count($colors)];
                                @endphp
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl {{ $theme['bg'] }} {{ $theme['text'] }} flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                                        <i class="fas fa-building text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $dudiName }}</p>
                                        <p class="text-xs font-semibold text-slate-400 mt-0.5 max-w-[200px] truncate" title="{{ $p->company_address }}">
                                            <i class="fas fa-map-marker-alt {{ $theme['icon'] }} mr-1"></i>{{ $p->company_address }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5">
                                <div class="space-y-2">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-100 shadow-sm text-xs font-bold text-slate-600">
                                        <i class="fas fa-user-tie text-indigo-400"></i> DUDI: {{ $p->mentor_name }}
                                    </div>
                                    <br>
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-100 shadow-sm text-xs font-bold text-slate-600">
                                        <i class="fas fa-chalkboard-teacher text-emerald-400"></i> Sekolah: {{ $p->teacher->user->name ?? '-' }}
                                    </div>
                                </div>
                            </td>
                            <td class="py-5">
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 shadow-sm inline-flex">
                                    <i class="far fa-calendar-alt text-indigo-400"></i>
                                    {{ $p->start_date->format('d/m/y') }} – {{ $p->end_date->format('d/m/y') }}
                                </div>
                            </td>
                            <td class="py-5 text-center">
                                @php
                                    $statusClass = match($p->status) {
                                        'active' => 'bg-gradient-to-r from-emerald-400 to-emerald-500 text-white shadow-emerald-200',
                                        'completed' => 'bg-gradient-to-r from-blue-400 to-indigo-500 text-white shadow-blue-200',
                                        default => 'bg-gradient-to-r from-slate-300 to-slate-400 text-white shadow-slate-200'
                                    };
                                    $statusText = match($p->status) {
                                        'active' => 'Aktif',
                                        'completed' => 'Selesai',
                                        default => 'Batal'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold shadow-md {{ $statusClass }}">
                                    @if($p->status == 'active') <i class="fas fa-circle-notch fa-spin text-[10px]"></i> @endif
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="py-5 text-right pr-6">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Copy Signed URL --}}
                                    <button onclick="copyToClipboard('{{ route('mentor.pkl.portal', $p->signed_token) }}', this)" class="group/btn relative w-9 h-9 rounded-xl bg-white border border-slate-200 hover:border-indigo-300 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 shadow-sm transition-all duration-300 flex items-center justify-center hover:-translate-y-0.5" title="Salin Tautan Mentor DUDI">
                                        <i class="fas fa-link"></i>
                                    </button>
                                    
                                    {{-- Detail --}}
                                    <a href="{{ route('admin.pkl-alumni.placements.show', $p->id) }}" class="group/btn relative w-9 h-9 rounded-xl bg-white border border-slate-200 hover:border-emerald-300 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 shadow-sm transition-all duration-300 flex items-center justify-center hover:-translate-y-0.5" title="Detail Logs & Nilai">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.pkl-alumni.placements.edit', $p->id) }}" class="group/btn relative w-9 h-9 rounded-xl bg-white border border-slate-200 hover:border-amber-300 text-slate-400 hover:text-amber-600 hover:bg-amber-50 shadow-sm transition-all duration-300 flex items-center justify-center hover:-translate-y-0.5" title="Edit Penempatan">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.pkl-alumni.placements.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data penempatan PKL ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="group/btn relative w-9 h-9 rounded-xl bg-white border border-slate-200 hover:border-rose-300 text-slate-400 hover:text-rose-600 hover:bg-rose-50 shadow-sm transition-all duration-300 flex items-center justify-center hover:-translate-y-0.5" title="Hapus Penempatan">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="w-20 h-20 rounded-[2rem] bg-indigo-50 border-2 border-indigo-100 flex items-center justify-center mx-auto mb-4 shadow-inner">
                                    <i class="fas fa-briefcase text-3xl text-indigo-300"></i>
                                </div>
                                <h3 class="text-lg font-black text-slate-700">Belum Ada Data Penempatan</h3>
                                <p class="text-sm font-medium text-slate-500 mt-1">Data penempatan PKL siswa akan muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($placements->hasPages())
            <div class="px-6 py-5 border-t border-indigo-100/50 bg-slate-50/50">
                {{ $placements->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check text-emerald-500 text-[10px]"></i>';
            button.classList.add('bg-emerald-50', 'border-emerald-200');
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('bg-emerald-50', 'border-emerald-200');
            }, 2000);
        }, function(err) {
            console.error('Failed to copy: ', err);
        });
    }
</script>
@endsection
