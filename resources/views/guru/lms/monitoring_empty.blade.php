@extends('layouts.guru')

@section('title', 'Pantauan Progres Siswa - LMS Guru')

@section('content')
<div class="bg-white rounded-3xl p-10 border-2 border-black shadow-xl text-center max-w-xl mx-auto space-y-4">
    <div class="w-16 h-16 bg-amber-100 border-2 border-black rounded-3xl flex items-center justify-center mx-auto text-amber-600 text-2xl font-black shadow-sm">
        <i class="fas fa-chalkboard-user"></i>
    </div>
    <h2 class="text-xl font-black text-slate-900">Belum Ada Kursus LMS Aktif</h2>
    <p class="text-xs text-slate-600 font-bold leading-relaxed">
        Anda belum membuat atau ditugaskan pada kursus pembelajaran LMS aktif. Buat kursus baru untuk mulai mengunggah materi, tugas, dan memantau capaian belajar siswa.
    </p>
    <div class="pt-2">
        <a href="{{ route('guru.lms.create') }}" class="inline-flex items-center gap-2 bg-black hover:bg-slate-800 text-amber-400 px-5 py-3 rounded-2xl font-black text-xs uppercase tracking-wider border-2 border-black shadow-md transition">
            <i class="fas fa-plus-circle"></i> Buat Kursus LMS Baru
        </a>
    </div>
</div>
@endsection
