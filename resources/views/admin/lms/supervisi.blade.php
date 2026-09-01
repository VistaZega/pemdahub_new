@extends('layouts.admin')

@section('title', 'Supervisi Konten LMS')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-800 via-indigo-700 to-slate-800 text-white rounded-3xl p-6 md:p-8 shadow-xl border-2 border-white/10">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight">🕵️ Supervisi Konten LMS</h1>
                <p class="text-white/70 text-sm font-bold mt-1">Review, approve, atau reject course yang dibuat guru</p>
            </div>
            <div class="flex items-center gap-3">
                @if($schools->count() > 1)
                <form method="GET">
                    <select name="school_id" onchange="this.form.submit()" class="bg-white/10 border border-white/20 text-white rounded-xl px-4 py-2 text-sm font-bold">
                        <option value="">Semua Sekolah</option>
                        @foreach($schools as $s)
                        <option value="{{ $s->id }}" {{ $schoolId == $s->id ? 'selected' : '' }} class="text-black">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
                <select name="review_status" onchange="window.location.href='{{ route('admin.lms.supervisi.index') }}?school_id={{ $schoolId }}&review_status='+this.value" class="bg-white/10 border border-white/20 text-white rounded-xl px-4 py-2 text-sm font-bold">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $reviewStatus == 'pending' ? 'selected' : '' }}>⏳ Menunggu Review</option>
                    <option value="approved" {{ $reviewStatus == 'approved' ? 'selected' : '' }}>✅ Disetujui</option>
                    <option value="rejected" {{ $reviewStatus == 'rejected' ? 'selected' : '' }}>❌ Ditolak</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border-2 border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase">Total</p>
            <p class="text-2xl font-black text-slate-800">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-amber-50 rounded-2xl p-4 border-2 border-amber-200 shadow-sm">
            <p class="text-xs font-bold text-amber-600 uppercase">⏳ Perlu Review</p>
            <p class="text-2xl font-black text-amber-800">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl p-4 border-2 border-emerald-200 shadow-sm">
            <p class="text-xs font-bold text-emerald-600 uppercase">✅ Disetujui</p>
            <p class="text-2xl font-black text-emerald-800">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-rose-50 rounded-2xl p-4 border-2 border-rose-200 shadow-sm">
            <p class="text-xs font-bold text-rose-600 uppercase">❌ Ditolak</p>
            <p class="text-2xl font-black text-rose-800">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    {{-- Course Table --}}
    <div class="bg-white rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-xs tracking-wider">
                        <th class="p-3 text-left">Kursus</th>
                        <th class="p-3 text-left">Guru</th>
                        <th class="p-3 text-left">Mapel</th>
                        <th class="p-3 text-center">Konten</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Review</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3 font-bold">{{ $course->course_name ?? $course->name }}</td>
                        <td class="p-3 text-slate-600">{{ $course->teacher?->full_name ?? '-' }}</td>
                        <td class="p-3 text-slate-600">{{ $course->subject->subject_name ?? '-' }}</td>
                        <td class="p-3 text-center text-xs">
                            <span class="font-bold">{{ $course->modules_count ?? $course->modules->count() }} Modul</span> ·
                            <span>{{ $course->materials_count ?? $course->materials->count() }} Materi</span> ·
                            <span>{{ $course->assignments_count ?? 0 }} Tugas</span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $course->review_status_color }}">
                                {{ $course->review_status_label }}
                            </span>
                        </td>
                        <td class="p-3 text-center text-xs text-slate-500">
                            @if($course->reviewer)
                            {{ $course->reviewer->name }}<br>
                            <span class="text-[10px]">{{ $course->reviewed_at ? $course->reviewed_at->diffForHumans() : '-' }}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.lms.supervisi.preview', $course->id) }}" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold hover:bg-indigo-200 transition">
                                    👁️ Preview
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-slate-500">Tidak ada course.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $courses->appends(request()->query())->links() }}
</div>
@endsection