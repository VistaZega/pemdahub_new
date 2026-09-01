@extends('layouts.admin')

@section('title', 'Preview: ' . ($course->course_name ?? $course->name))

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.lms.supervisi.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 font-bold text-sm transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Supervisi
            </a>
            <h1 class="text-2xl font-black text-slate-800 mt-1">{{ $course->course_name ?? $course->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-4 py-2 rounded-xl text-sm font-bold {{ $course->review_status_color }}">
                {{ $course->review_status_label }}
            </span>
        </div>
    </div>

    {{-- Course Info --}}
    <div class="bg-white rounded-2xl p-6 border-2 border-slate-200 shadow-sm grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div><span class="font-bold text-slate-500 block text-xs">Guru</span>{{ $course->teacher?->full_name ?? '-' }}</div>
        <div><span class="font-bold text-slate-500 block text-xs">Mapel</span>{{ $course->subject->subject_name ?? '-' }}</div>
        <div><span class="font-bold text-slate-500 block text-xs">Modul</span>{{ $course->modules->count() }}</div>
        <div><span class="font-bold text-slate-500 block text-xs">Materi</span>{{ $course->modules->flatMap->materials->count() }}</div>
    </div>

    {{-- Modul Preview --}}
    @foreach($course->modules as $module)
    <div class="bg-white rounded-2xl border-2 border-slate-200 overflow-hidden shadow-sm">
        <div class="bg-slate-100 px-5 py-3 border-b-2 border-slate-200">
            <h3 class="font-black text-sm flex items-center gap-2">
                <span class="bg-indigo-600 text-white w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold">{{ $module->sequence }}</span>
                {{ $module->title }}
            </h3>
        </div>
        <div class="p-4 space-y-2">
            @forelse($module->materials as $material)
            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs {{ $material->material_type == 'pdf' ? 'bg-red-100 text-red-600' : ($material->material_type == 'video' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-600') }}">
                    <i class="fas {{ $material->material_type == 'pdf' ? 'fa-file-pdf' : ($material->material_type == 'video' ? 'fa-video' : 'fa-file') }}"></i>
                </span>
                <span class="text-sm font-bold text-slate-700 flex-1">{{ $material->title }}</span>
                <span class="text-[10px] text-slate-400 uppercase font-bold">{{ $material->material_type }}</span>
            </div>
            @empty
            <p class="text-xs text-slate-400 italic p-2">Belum ada materi</p>
            @endforelse

            @if($module->assignments->count())
            <div class="mt-2 pt-2 border-t border-slate-200">
                <span class="text-xs font-bold text-amber-600">📝 {{ $module->assignments->count() }} Tugas</span>
            </div>
            @endif
            @if($module->quizzes->count())
            <div class="mt-1">
                <span class="text-xs font-bold text-purple-600">❓ {{ $module->quizzes->count() }} Quiz</span>
            </div>
            @endif
        </div>
    </div>
    @endforeach

    {{-- Review Form --}}
    <div class="bg-white rounded-2xl p-6 border-2 border-slate-200 shadow-sm">
        <h3 class="font-black text-sm mb-4">✍️ Berikan Review</h3>
        <form method="POST" action="{{ route('admin.lms.supervisi.review', $course->id) }}" class="space-y-4">
            @csrf
            <textarea name="review_note" rows="3" placeholder="Catatan review (opsional)..." class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-4 focus:ring-indigo-500/20 outline-none">{{ $course->review_note }}</textarea>
            <div class="flex flex-wrap gap-3">
                <button type="submit" name="action" value="approve" class="flex-1 min-w-[120px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl text-sm transition shadow-md">
                    ✅ Setujui
                </button>
                <button type="submit" name="action" value="pending" class="flex-1 min-w-[120px] bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl text-sm transition shadow-md">
                    ⏳ Minta Review Ulang
                </button>
                <button type="submit" name="action" value="reject" class="flex-1 min-w-[120px] bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-xl text-sm transition shadow-md">
                    ❌ Tolak
                </button>
            </div>
        </form>
    </div>
</div>
@endsection