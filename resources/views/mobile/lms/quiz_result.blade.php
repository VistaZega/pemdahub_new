@extends('mobile.layouts.app')

@section('title', 'Hasil Kuis - PembdaHUB Mobile')

@section('content')
<div class="space-y-4">
    <!-- Back Link -->
    <a href="{{ route('mobile.lms.show', $attempt->quiz->course_id) }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Modul LMS
    </a>

    <!-- Score Header Clay Card -->
    <div class="{{ $attempt->is_passed ? 'clay-green' : 'clay-pink' }} p-6 text-center space-y-3">
        <div class="w-16 h-16 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-white text-3xl font-black mx-auto border-2 border-white shadow-md">
            {{ $attempt->is_passed ? '🎉' : '💪' }}
        </div>

        <div>
            <span class="px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-white/30 text-white border border-white/40">
                {{ $attempt->is_passed ? 'Selesai - Lulus' : 'Selesai - Belum Lulus' }}
            </span>
            <h1 class="text-3xl font-black text-white mt-2 leading-none">{{ number_format($attempt->score, 1) }}%</h1>
            <p class="text-xs text-white/90 font-bold mt-1">Nilai Kuis: {{ $attempt->quiz->title }}</p>
        </div>
    </div>

    <!-- Details Card -->
    <div class="clay-card p-4.5 space-y-2 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <span class="font-bold text-slate-500">Batas Nilai Lulus:</span>
            <span class="font-black text-slate-900">{{ $attempt->quiz->passing_score ?? 70 }}%</span>
        </div>
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <span class="font-bold text-slate-500">Waktu Selesai:</span>
            <span class="font-black text-slate-900">{{ $attempt->finished_at ? $attempt->finished_at->translatedFormat('d M Y, H:i') : '-' }}</span>
        </div>
        <div class="flex items-center justify-between pt-1">
            <span class="font-bold text-slate-500">Status Kelulusan:</span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $attempt->is_passed ? 'clay-green' : 'clay-pink' }}">
                {{ $attempt->is_passed ? 'LULUS' : 'REMEDIAL' }}
            </span>
        </div>
    </div>

    <!-- Action Link -->
    <a href="{{ route('mobile.lms.show', $attempt->quiz->course_id) }}" class="clay-btn block w-full py-4 text-center text-white font-black text-xs uppercase tracking-wider shadow-lg">
        <i class="fa-solid fa-house-laptop mr-1.5"></i> Kembali ke LMS Kursus
    </a>
</div>
@endsection
