@extends('layouts.admin')
@section('title', 'Kelola Penempatan PKL - Portal Admin')

@section('content')
<div class="space-y-6">
    {{-- Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4">
        <div>
            <h1 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-briefcase text-indigo-500"></i> Kelola Penempatan PKL
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Mengatur dan memantau siswa magang di instansi/perusahaan mitra
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pkl-alumni.placements.map') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl text-xs shadow transition">
                <i class="fas fa-map-marked-alt"></i> Peta Sebaran PKL
            </a>
            <a href="{{ route('admin.pkl-alumni.placements.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl text-xs shadow transition">
                <i class="fas fa-plus"></i> Tambah Penempatan
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-250 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form action="{{ route('admin.pkl-alumni.placements.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3.5 top-3.5 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa atau perusahaan..." class="w-full bg-gray-50 border border-gray-100 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
            </div>
            
            @if($isSA)
                <div class="w-full md:w-64">
                    <select name="school_id" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                        <option value="">Semua Sekolah SMK...</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-5 py-2.5 rounded-xl text-sm transition">
                Cari
            </button>
            @if(request()->anyFilled(['search', 'school_id']))
                <a href="{{ route('admin.pkl-alumni.placements.index') }}" class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold px-4 py-2.5 rounded-xl text-sm transition flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Placements Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider text-left">
                    <tr>
                        <th class="py-3.5 pl-5">Siswa & Kelas</th>
                        <th class="py-3.5">Industri & Alamat</th>
                        <th class="py-3.5">Pembimbing (DUDI & Sekolah)</th>
                        <th class="py-3.5">Periode</th>
                        <th class="py-3.5 text-center">Status</th>
                        <th class="py-3.5 text-center pr-5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-xs text-gray-700">
                    @forelse($placements as $p)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-4 pl-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm flex-shrink-0">
                                        <img src="{{ $p->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $p->student->full_name }}">
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm leading-tight">{{ $p->student->full_name }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $p->student->school->name ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <p class="font-bold text-gray-800 leading-snug">{{ $p->company_name }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5 max-w-[200px] truncate" title="{{ $p->company_address }}">
                                    <i class="fas fa-map-marker-alt text-rose-500 mr-0.5"></i>{{ $p->company_address }}
                                </p>
                            </td>
                            <td class="py-4 space-y-1">
                                <p class="font-semibold text-gray-800 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    DUDI: {{ $p->mentor_name }}
                                </p>
                                <p class="font-semibold text-gray-500 flex items-center gap-1.5 pl-3">
                                    Sekolah: {{ $p->teacher->user->name ?? '-' }}
                                </p>
                            </td>
                            <td class="py-4 font-medium text-gray-600">
                                {{ $p->start_date->format('d/m/y') }} – {{ $p->end_date->format('d/m/y') }}
                            </td>
                            <td class="py-4 text-center">
                                @php
                                    $statusClass = match($p->status) {
                                        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-250',
                                        'completed' => 'bg-blue-50 text-blue-700 border-blue-250',
                                        default => 'bg-gray-150 text-gray-600 border-gray-300'
                                    };
                                    $statusText = match($p->status) {
                                        'active' => 'Aktif',
                                        'completed' => 'Selesai',
                                        default => 'Batal'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="py-4 text-center pr-5">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Copy Signed URL --}}
                                    <button onclick="copyToClipboard('{{ route('mentor.pkl.portal', $p->signed_token) }}', this)" class="bg-gray-50 hover:bg-indigo-50 text-gray-500 hover:text-indigo-600 border border-gray-200 hover:border-indigo-150 p-2 rounded-xl transition shadow-sm" title="Salin Tautan Mentor DUDI">
                                        <i class="fas fa-link text-[10px]"></i>
                                    </button>
                                    
                                    {{-- Detail --}}
                                    <a href="{{ route('admin.pkl-alumni.placements.show', $p->id) }}" class="bg-gray-50 hover:bg-emerald-50 text-gray-500 hover:text-emerald-600 border border-gray-200 hover:border-emerald-150 p-2 rounded-xl transition shadow-sm" title="Detail Logs & Nilai">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.pkl-alumni.placements.edit', $p->id) }}" class="bg-gray-50 hover:bg-amber-50 text-gray-500 hover:text-amber-600 border border-gray-200 hover:border-amber-150 p-2 rounded-xl transition shadow-sm" title="Edit Penempatan">
                                        <i class="fas fa-edit text-[10px]"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.pkl-alumni.placements.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data penempatan PKL ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-gray-50 hover:bg-rose-50 text-gray-500 hover:text-rose-600 border border-gray-200 hover:border-rose-150 p-2 rounded-xl transition shadow-sm" title="Hapus Penempatan">
                                            <i class="fas fa-trash-alt text-[10px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400 italic">
                                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-briefcase text-lg text-gray-300"></i>
                                </div>
                                Belum ada data penempatan PKL.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($placements->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
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
