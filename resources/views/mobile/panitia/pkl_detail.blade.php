@extends('mobile.layouts.app')

@section('title', 'Detail Penempatan PKL - Panitia Mobile')

@section('content')
<div class="space-y-4 pt-1">
    <!-- Top Header Navigation -->
    <div class="flex items-center justify-between gap-2 px-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.panitia.pkl') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-base font-black text-slate-900 leading-tight truncate">Detail Penempatan</h2>
                <p class="text-[10px] text-slate-500 font-bold truncate">{{ $placement->student->full_name ?? 'Siswa' }}</p>
            </div>
        </div>
        <div class="w-9 h-9 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-800 flex items-center justify-center text-base font-black shrink-0">
            💼
        </div>
    </div>

    <!-- Student Profile Card -->
    <div class="clay-yellow p-5 space-y-3 rounded-3xl shadow-md">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white/40 text-slate-950 flex items-center justify-center text-xl font-black border border-white/60 shrink-0 shadow-xs">
                🎓
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-black text-slate-950 leading-tight truncate">{{ $placement->student->full_name }}</h3>
                <p class="text-[10px] text-amber-950 font-bold truncate">
                    NISN: {{ $placement->student->nisn ?? '-' }} • {{ $placement->student->school->name ?? 'Pembda' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Form 1: Penugasan Guru Pembimbing PKL -->
    <div class="clay-card p-5 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-3">
        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-user-tie text-purple-600 text-sm"></i> Penugasan Guru Pembimbing PKL
        </h4>

        <form action="{{ route('mobile.panitia.pkl.assign-teacher', $placement->id) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-black text-slate-700 mb-1">Pilih Guru Pembimbing:</label>
                <select name="teacher_id" required class="w-full px-3 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:border-purple-600 outline-hidden">
                    <option value="">-- Pilih Guru Pembimbing --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $placement->teacher_id == $t->id ? 'selected' : '' }}>
                            {{ $t->full_name }} ({{ $t->school->name ?? 'Pembda' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full py-2.5 bg-purple-600 text-white font-black text-xs rounded-xl shadow-xs hover:bg-purple-700 active:scale-95 transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Guru Pembimbing
            </button>
        </form>
    </div>

    <!-- Form 2: Status & Periode Penempatan -->
    <div class="clay-card p-5 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-3">
        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-calendar-check text-amber-500 text-sm"></i> Status & Tanggal Periode PKL
        </h4>

        <form action="{{ route('mobile.panitia.pkl.update-status', $placement->id) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-black text-slate-700 mb-1">Status Penempatan:</label>
                <select name="status" required class="w-full px-3 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900">
                    <option value="pending" {{ $placement->status === 'pending' ? 'selected' : '' }}>Pending / Menunggu</option>
                    <option value="active" {{ $placement->status === 'active' ? 'selected' : '' }}>Aktif (Sedang Berjalan)</option>
                    <option value="completed" {{ $placement->status === 'completed' ? 'selected' : '' }}>Selesai PKL</option>
                    <option value="canceled" {{ $placement->status === 'canceled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-black text-slate-700 mb-1">Tanggal Mulai:</label>
                    <input type="date" name="start_date" value="{{ $placement->start_date ? $placement->start_date->format('Y-m-d') : '' }}"
                           class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900">
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-700 mb-1">Tanggal Selesai:</label>
                    <input type="date" name="end_date" value="{{ $placement->end_date ? $placement->end_date->format('Y-m-d') : '' }}"
                           class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900">
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 bg-amber-500 text-slate-950 font-black text-xs rounded-xl shadow-xs hover:bg-amber-400 active:scale-95 transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-floppy-disk"></i> Perbarui Status Penempatan
            </button>
        </form>
    </div>

    <!-- DUDI Info Card -->
    <div class="clay-card p-5 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-2.5">
        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-building text-blue-600 text-sm"></i> Informasi Mitra DUDI
        </h4>

        <div class="p-3 bg-blue-50/60 rounded-2xl border border-blue-100 space-y-1.5 text-xs">
            <p class="font-black text-blue-950">{{ $placement->dudi->name ?? ($placement->company_name ?? 'Mitra Mandiri') }}</p>
            <p class="text-slate-600 font-bold text-[11px]">{{ $placement->dudi->address ?? ($placement->company_address ?? 'Alamat belum diatur') }}</p>
            <p class="text-slate-500 font-bold text-[10px]">Instruktur / Kontak: {{ $placement->mentor_name ?? '-' }} ({{ $placement->mentor_phone ?? '-' }})</p>
        </div>
    </div>
</div>
@endsection
