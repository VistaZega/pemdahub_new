@extends('layouts.admin')

@section('title', 'Projek Penguatan Profil Pelajar Pancasila (P5)')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-700 rounded-2xl p-6 text-white relative overflow-hidden shadow-sm">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="relative">
            <div class="flex items-center text-sm text-white/70 mb-2 gap-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span>/</span>
                <span class="text-white font-semibold">Projek P5</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold flex items-center gap-2.5">
                        <i class="fas fa-shapes"></i> Projek Profil Pelajar Pancasila (P5)
                    </h1>
                    <p class="text-xs text-purple-100 mt-1">
                        Manajemen Tema, Target Dimensi, Penilaian Capaian, dan Cetak Rapor P5 Kurikulum Merdeka
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.p5.create') }}" class="px-6 py-3 bg-white text-indigo-700 hover:bg-indigo-50 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-md active:scale-95">
                        <i class="fas fa-plus"></i> Buat Projek P5 Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl shadow-sm text-sm font-semibold flex items-center gap-2">
        <i class="fas fa-check-circle text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <form action="{{ route('admin.p5.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @if(auth()->user()->isSuperAdmin())
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1.5">Unit Sekolah</label>
                <select name="school_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua Unit Sekolah --</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1.5">Tahun Pelajaran</label>
                <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua TP --</option>
                    @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1.5">Rombel / Kelas</label>
                <select name="classroom_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }} ({{ $cls->school->code ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <a href="{{ route('admin.p5.index') }}" class="w-full px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition">
                    <i class="fas fa-undo"></i> Reset Filter
                </a>
            </div>
        </form>
    </div>

    {{-- Grid Daftar Projek P5 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $p)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between hover:shadow-md transition">
            <div class="p-6 space-y-4">
                <div class="flex items-start justify-between gap-2">
                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-[11px] font-bold">
                        <i class="fas fa-tag mr-1 text-purple-600"></i> {{ $p->theme }}
                    </span>
                    <span class="text-xs font-bold text-gray-400 font-mono">
                        {{ $p->classroom->name ?? '-' }}
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900 leading-snug hover:text-indigo-600 transition">
                        <a href="{{ route('admin.p5.show', $p) }}">{{ $p->title }}</a>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                        {{ $p->description ?: 'Tidak ada deskripsi projek tambahan.' }}
                    </p>
                </div>

                <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs text-gray-600">
                    <div>
                        <p class="text-[10px] text-gray-400 font-medium">Target Dimensi:</p>
                        <p class="font-bold text-gray-800"><i class="fas fa-bullseye text-indigo-500 mr-1"></i>{{ $p->targets_count }} Dimensi</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-medium">Status Penilaian:</p>
                        <p class="font-bold {{ $p->assessments_count > 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                            <i class="fas fa-check-double mr-1"></i>{{ $p->assessments_count > 0 ? 'Terisi (' . $p->assessments_count . ')' : 'Belum Dinilai' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between gap-2">
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('admin.p5.show', $p) }}" class="px-3.5 py-2 bg-white hover:bg-gray-100 text-gray-700 rounded-lg text-xs font-bold border border-gray-200 transition shadow-xs" title="Lihat Detail & Rapor">
                        <i class="fas fa-eye text-indigo-600"></i> Detail
                    </a>
                    <a href="{{ route('admin.p5.assess', $p) }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Input Capaian Penilaian">
                        <i class="fas fa-pen-fancy"></i> Nilai
                    </a>
                </div>

                @if(auth()->user()->isSuperAdmin() || auth()->id() == $p->created_by)
                <form action="{{ route('admin.p5.destroy', $p) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus projek P5 ini beserta seluruh data nilainya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg text-xs transition" title="Hapus Projek">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl border border-dashed border-gray-200 p-12 text-center text-gray-500">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl mx-auto mb-3">
                <i class="fas fa-shapes"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">Belum Ada Projek P5 Terdaftar</h3>
            <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                Silakan buat projek profil pelajar pancasila baru untuk kelas dan tema yang sedang berjalan.
            </p>
            <div class="mt-4">
                <a href="{{ route('admin.p5.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Buat Projek Sekarang
                </a>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div>
        {{ $projects->links() }}
    </div>
</div>
@endsection
