@extends('layouts.siswa')

@section('title', 'Sertifikat')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-purple-600 h-4"></div>
        <div class="p-8 text-center">
            @php $cert = $certificate; @endphp
            <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 shadow-lg">
                🏆
            </div>
            <h1 class="text-2xl font-black text-slate-800 mb-1">Sertifikat Penyelesaian</h1>
            <p class="text-sm text-slate-500 font-bold mb-6">Diberikan kepada</p>
            <h2 class="text-3xl font-black text-indigo-600 mb-2">{{ $cert->student->full_name }}</h2>
            <p class="text-sm text-slate-600 font-bold mb-6">Telah menyelesaikan kursus</p>
            <h3 class="text-xl font-black text-slate-800 mb-2">{{ $cert->course->course_name ?? $cert->course->name }}</h3>
            <p class="text-sm text-slate-500 font-bold mb-6">{{ $cert->course->subject->subject_name ?? '' }}</p>

            <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto mb-6">
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                    <p class="text-[10px] font-bold text-slate-500 uppercase">Progress</p>
                    <p class="text-lg font-black text-emerald-600">{{ $cert->final_progress }}%</p>
                </div>
                @if($cert->final_score)
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                    <p class="text-[10px] font-bold text-slate-500 uppercase">Nilai Akhir</p>
                    <p class="text-lg font-black text-indigo-600">{{ $cert->final_score }}</p>
                </div>
                @endif
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                    <p class="text-[10px] font-bold text-slate-500 uppercase">Diterbitkan</p>
                    <p class="text-sm font-bold text-slate-700">{{ $cert->issued_at ? $cert->issued_at->format('d F Y') : '-' }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                    <p class="text-[10px] font-bold text-slate-500 uppercase">Kode</p>
                    <p class="text-[11px] font-mono font-bold text-slate-700">{{ $cert->certificate_code }}</p>
                </div>
            </div>

            <div class="flex gap-3 justify-center">
                <a href="{{ route('siswa.lms.certificates.download', $cert->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-8 py-3 rounded-xl text-sm transition shadow-md">
                    📥 Download PDF
                </a>
                <a href="{{ route('siswa.lms.show', $cert->course_id) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-6 py-3 rounded-xl text-sm transition border-2 border-slate-200">
                    Lihat Kursus
                </a>
            </div>
        </div>
    </div>
</div>
@endsection