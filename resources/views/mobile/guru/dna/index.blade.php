@extends('mobile.layouts.app')

@section('title', 'Direktori DNA Siswa')

@section('content')
<div class="space-y-4 pb-20">

    {{-- Top Bar --}}
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center text-slate-700 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="text-center">
            <h1 class="text-sm font-black text-slate-900 leading-tight">DNA Akademik 360°</h1>
            <p class="text-[10px] text-fuchsia-600 font-bold uppercase tracking-wider">Direktori Potensi Siswa</p>
        </div>
        <div class="w-10"></div>
    </div>

    {{-- Hero Card --}}
    <div class="bg-gradient-to-br from-slate-900 via-purple-950 to-indigo-950 p-5 rounded-3xl text-white relative overflow-hidden shadow-lg border border-purple-800/40 space-y-2">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-fuchsia-500/20 rounded-full blur-2xl pointer-events-none"></div>
        <span class="px-2.5 py-1 bg-white/20 backdrop-blur-xs rounded-full text-[10px] font-black tracking-wider uppercase inline-flex items-center gap-1">
            <span>🧬</span> 6 Dimensi Potensi Belajar
        </span>
        <h2 class="text-lg font-black text-white leading-snug">Radar Bakat & Minat Karier</h2>
        <p class="text-[11px] text-slate-200 leading-relaxed">
            Analisis multidimensi berbasis nilai rapor, CBT, aktivitas LMS, kedisiplinan RFID, dan rekam jejak ekskul.
        </p>
    </div>

    {{-- Search Bar --}}
    <form method="GET" action="{{ route('mobile.guru.dna') }}" class="relative">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa atau NISN..." class="w-full pl-10 pr-4 py-3 bg-white rounded-2xl border border-slate-200 text-xs font-semibold shadow-xs focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
        @if(request('search'))
        <a href="{{ route('mobile.guru.dna') }}" class="absolute right-3.5 top-3 text-slate-400 hover:text-slate-600 text-sm">&times;</a>
        @endif
    </form>

    {{-- Students List --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between text-xs text-slate-500 font-bold px-1">
            <span>Daftar Siswa ({{ $students->total() }})</span>
            @if(request('search'))
            <span class="text-purple-600">Hasil pencarian: "{{ request('search') }}"</span>
            @endif
        </div>

        @forelse($students as $student)
        <a href="{{ route('mobile.guru.dna.show', $student) }}" class="block bg-white p-3.5 rounded-3xl border border-slate-200/80 shadow-xs active:scale-[0.98] transition">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 shadow-2xs flex-shrink-0">
                        <img src="{{ $student->photo_url }}" class="w-full h-full object-cover" alt="{{ $student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-black text-slate-900 text-xs truncate leading-snug">{{ $student->full_name }}</h3>
                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">NISN: {{ $student->nisn ?: '-' }} &bull; {{ $student->nis ?: '-' }}</p>
                        <p class="text-[10px] text-purple-700 font-bold mt-0.5">
                            {{ $student->school->short_name ?: $student->school->name }} &bull; {{ $student->currentClassroom->first()->class_name ?? ($student->classroom->class_name ?? '-') }}
                        </p>
                    </div>
                </div>

                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs flex-shrink-0">
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </div>
        </a>
        @empty
        <div class="p-8 bg-white rounded-3xl border border-dashed border-slate-300 text-center space-y-2">
            <p class="text-3xl">🔍</p>
            <p class="font-bold text-slate-800 text-xs">Siswa Tidak Ditemukan</p>
            <p class="text-[11px] text-slate-500">Coba gunakan kata kunci pencarian yang lain.</p>
        </div>
        @endforelse

        <div class="pt-2">
            {{ $students->links() }}
        </div>
    </div>

</div>
@endsection
