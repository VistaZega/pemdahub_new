@extends('mobile.layouts.app')

@section('title', 'Detail Tugas Akhir - Panitia Mobile')

@section('content')
<div class="space-y-4 pt-1">
    <!-- Top Header Navigation -->
    <div class="flex items-center justify-between gap-2 px-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.panitia.final-project') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-base font-black text-slate-900 leading-tight truncate">Review Tugas Akhir</h2>
                <p class="text-[10px] text-slate-500 font-bold truncate">Kelola Proposal & Sidang</p>
            </div>
        </div>
        <div class="w-9 h-9 rounded-2xl bg-purple-50 border-2 border-purple-300 text-purple-700 flex items-center justify-center text-base font-black shrink-0">
            🛠️
        </div>
    </div>

    <!-- Title & Status Card -->
    <div class="clay-purple p-5 space-y-3 rounded-3xl shadow-md">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/20 text-white px-2.5 py-0.5 rounded-full border border-white/30">
                {{ $project->type === 'research' ? 'Penelitian Ilmiah SMA' : 'Project Akhir SMK' }}
            </span>
            <span class="text-xs font-black bg-amber-400 text-slate-950 px-3 py-1 rounded-full border border-amber-300 shadow-2xs">
                {{ $project->current_stage_name }}
            </span>
        </div>

        <h3 class="text-sm font-black text-white leading-snug">{{ $project->title }}</h3>

        <div class="pt-1 flex items-center justify-between text-[10px] text-purple-100 font-bold border-t border-white/20">
            <span>Ketua: {{ $project->student->full_name ?? '-' }}</span>
            <span>Status: {{ strtoupper($project->status) }}</span>
        </div>
    </div>

    <!-- Form 1: Persetujuan Proposal & Penunjukan Pembimbing -->
    <div class="clay-card p-5 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-3">
        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-stamp text-amber-500 text-sm"></i> Verifikasi & Persetujuan Proposal
        </h4>

        <form action="{{ route('mobile.panitia.final-project.approve-proposal', $project->id) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-black text-slate-700 mb-1">Status Keputusan Panitia:</label>
                <select name="status" required class="w-full px-3 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:border-purple-600 outline-hidden">
                    <option value="approved" {{ $project->status === 'approved' ? 'selected' : '' }}>✅ Disetujui (Approved) - Lanjut Bimbingan Bab I</option>
                    <option value="revision" {{ $project->status === 'revision' ? 'selected' : '' }}>⚠️ Perlu Revisi (Perbaikan Proposal)</option>
                    <option value="rejected" {{ $project->status === 'rejected' ? 'selected' : '' }}>❌ Ditolak (Rejected)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-black text-slate-700 mb-1">Tunjuk Guru Pembimbing:</label>
                <select name="advisor_id" class="w-full px-3 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:border-purple-600 outline-hidden">
                    <option value="">-- Pilih Guru Pembimbing --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $project->advisor_id == $t->id ? 'selected' : '' }}>
                            {{ $t->full_name }} ({{ $t->school->name ?? 'Pembda' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-black text-slate-700 mb-1">Catatan / Alasan Revisi:</label>
                <textarea name="rejection_reason" rows="2" placeholder="Tuliskan catatan perbaikan atau instruksi untuk kelompok siswa..."
                          class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900 resize-none">{{ $project->rejection_reason }}</textarea>
            </div>

            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-950 font-black text-xs rounded-xl shadow-xs hover:opacity-95 active:scale-95 transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-check-double"></i> Simpan Keputusan Proposal
            </button>
        </form>
    </div>

    <!-- Form 2: Jadwal Sidang & Penunjukan Dewan Penguji -->
    <div class="clay-card p-5 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-3">
        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-graduation-cap text-purple-600 text-sm"></i> Penguji Ujian Sidang & Tahapan
        </h4>

        <form action="{{ route('mobile.panitia.final-project.assign-examiner', $project->id) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-black text-slate-700 mb-1">Tahapan Saat Ini (*Stage*):</label>
                <select name="current_stage" class="w-full px-3 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900">
                    <option value="proposal" {{ $project->current_stage === 'proposal' ? 'selected' : '' }}>Penyusunan Proposal</option>
                    <option value="bab1" {{ $project->current_stage === 'bab1' ? 'selected' : '' }}>Bab I: Pendahuluan</option>
                    <option value="bab2" {{ $project->current_stage === 'bab2' ? 'selected' : '' }}>Bab II: Kajian Pustaka</option>
                    <option value="bab3" {{ $project->current_stage === 'bab3' ? 'selected' : '' }}>Bab III: Metodologi</option>
                    <option value="bab4" {{ $project->current_stage === 'bab4' ? 'selected' : '' }}>Bab IV: Hasil & Pembahasan</option>
                    <option value="bab5" {{ $project->current_stage === 'bab5' ? 'selected' : '' }}>Bab V: Penutup</option>
                    <option value="sidang" {{ $project->current_stage === 'sidang' ? 'selected' : '' }}>🎓 Siap Sidang / Ujian Akhir</option>
                    <option value="completed" {{ $project->current_stage === 'completed' ? 'selected' : '' }}>🏆 Lulus & Selesai</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-black text-slate-700 mb-1">Penguji Utama (1):</label>
                    <select name="examiner_id" class="w-full px-2.5 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900">
                        <option value="">-- Pilih Penguji 1 --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $project->examiner_id == $t->id ? 'selected' : '' }}>
                                {{ $t->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-700 mb-1">Penguji Anggota (2):</label>
                    <select name="examiner2_id" class="w-full px-2.5 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900">
                        <option value="">-- Pilih Penguji 2 --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $project->examiner2_id == $t->id ? 'selected' : '' }}>
                                {{ $t->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-black text-slate-700 mb-1">Tanggal Sidang:</label>
                    <input type="datetime-local" name="exam_date" value="{{ $project->exam_date ? $project->exam_date->format('Y-m-d\TH:i') : '' }}"
                           class="w-full px-2.5 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900">
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-700 mb-1">Ruang Sidang:</label>
                    <input type="text" name="exam_location" value="{{ $project->exam_location }}" placeholder="Contoh: Lab Komputer 1"
                           class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-xs font-bold text-slate-900">
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 bg-purple-600 text-white font-black text-xs rounded-xl shadow-xs hover:bg-purple-700 active:scale-95 transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Jadwal & Dewan Penguji
            </button>
        </form>
    </div>

    <!-- Abstrak Proposal -->
    @if($project->abstract)
        <div class="clay-card p-5 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-2">
            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-align-left text-blue-600"></i> Abstrak / Gambaran Proyek
            </h4>
            <p class="text-xs text-slate-700 font-semibold leading-relaxed p-3 bg-slate-50 rounded-2xl border border-slate-100">
                {{ $project->abstract }}
            </p>
        </div>
    @endif

    <!-- Anggota Kelompok -->
    <div class="clay-card p-5 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-2.5">
        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-users text-emerald-600"></i> Anggota Kelompok ({{ $project->members->count() + 1 }} Orang)
        </h4>

        <div class="space-y-1.5 text-xs">
            <div class="p-2.5 bg-purple-50 rounded-xl border border-purple-200 flex items-center justify-between">
                <span class="font-black text-purple-950">👑 {{ $project->student->full_name ?? '-' }}</span>
                <span class="text-[9px] font-black text-purple-700 uppercase">Ketua Kelompok</span>
            </div>
            @foreach($project->members as $mbr)
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <span class="font-bold text-slate-800">👤 {{ $mbr->student->full_name ?? '-' }}</span>
                    <span class="text-[9px] font-bold text-slate-500">Anggota</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
