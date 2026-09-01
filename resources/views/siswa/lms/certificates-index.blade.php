@extends('layouts.siswa')

@section('title', 'Sertifikat Saya')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-purple-600 text-white rounded-3xl p-6 md:p-8 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-3xl">
                🏆
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight">Sertifikat Saya</h1>
                <p class="text-white/80 text-sm font-bold mt-1">Semua sertifikat penyelesaian kursus Anda</p>
            </div>
        </div>
    </div>

    @if($certificates->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($certificates as $cert)
        <div class="bg-white rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden hover:shadow-2xl transition">
            <div class="bg-gradient-to-r from-amber-500 to-purple-600 h-3"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sertifikat</span>
                    <span class="text-[10px] font-bold text-slate-400">{{ $cert->issued_at ? $cert->issued_at->format('d M Y') : '-' }}</span>
                </div>
                <h3 class="font-black text-lg text-slate-800 mb-1">{{ $cert->course->course_name ?? $cert->course->name }}</h3>
                <p class="text-xs font-bold text-slate-500 mb-3">{{ $cert->course->subject->subject_name ?? '-' }}</p>
                <div class="flex items-center gap-2 mb-4">
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold">Progress {{ $cert->final_progress }}%</span>
                    @if($cert->final_score)
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-bold">Skor: {{ $cert->final_score }}</span>
                    @endif
                </div>
                <div class="text-[10px] font-mono text-slate-400 mb-4 bg-slate-50 rounded-xl p-2 border border-slate-100 text-center">
                    {{ $cert->certificate_code }}
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('siswa.lms.certificates.show', $cert->id) }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 rounded-xl text-center transition">
                        👁️ Lihat
                    </a>
                    <a href="{{ route('siswa.lms.certificates.download', $cert->id) }}" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2.5 rounded-xl text-center transition">
                        📥 Download PDF
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-3xl p-10 border-2 border-dashed border-slate-300 text-center">
        <div class="text-5xl mb-4">🎯</div>
        <h3 class="font-black text-lg text-slate-700 mb-2">Belum Ada Sertifikat</h3>
        <p class="text-sm text-slate-500 max-w-md mx-auto">Selesaikan seluruh modul pembelajaran (progress 100%) untuk mendapatkan sertifikat penyelesaian kursus.</p>
    </div>
    @endif
</div>
@endsection