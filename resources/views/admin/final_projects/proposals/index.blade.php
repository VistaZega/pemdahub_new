@extends('layouts.admin')
@php
    $user = auth()->user();
    $schoolType = $user->school ? strtoupper($user->school->type) : 'ALL';
    $isSMA = $schoolType === 'SMA';
    $isSMK = $schoolType === 'SMK';
    
    $pageTitle = 'Usulan Judul ';
    $entityName = 'tugas akhir';
    if ($isSMA) {
        $pageTitle .= 'Tugas Penelitian Ilmiah';
        $entityName = 'penelitian ilmiah';
    } else if ($isSMK) {
        $pageTitle .= 'Project Akhir SMK';
        $entityName = 'project akhir';
    } else {
        $pageTitle .= 'Penelitian & Project Akhir';
        $entityName = 'tugas akhir / project';
    }

    // Siapkan data guru per sekolah dalam format JSON untuk Alpine.js
    $teachersBySchool = $teachers->groupBy('school_id')->map(function($group) {
        return $group->map(function($t) {
            return [
                'id' => $t->id,
                'name' => $t->full_name,
                'school_id' => $t->school_id,
                'school_name' => $t->school->name ?? 'N/A',
            ];
        })->values();
    });
    $allTeachersJson = $teachers->map(function($t) {
        return [
            'id' => $t->id,
            'name' => $t->full_name,
            'school_id' => $t->school_id,
            'school_name' => $t->school->name ?? 'N/A',
        ];
    })->values();
@endphp
@section('title', $pageTitle . ' - Portal Admin')

@section('content')
<div x-data="proposalManager()" class="flex flex-col gap-8 md:gap-10 pb-12">

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- 1. PLAYFUL HERO HEADER (Claymorphism Style) --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 p-7 md:p-8 text-white shadow-xl border border-indigo-700/50">
        {{-- Decorative 3D Glow Orbs --}}
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 p-0.5 shadow-lg shadow-orange-500/30 flex-shrink-0 flex items-center justify-center">
                    <div class="w-full h-full bg-indigo-950/40 backdrop-blur-xs rounded-[14px] flex items-center justify-center text-amber-300 text-2xl">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-700/60 border border-indigo-500/50 text-indigo-200 text-xs font-black uppercase tracking-wider mb-2 backdrop-blur-xs">
                        <i class="fas fa-sparkles text-amber-400"></i> Platform Tugas Akhir Berbasis Proyek
                    </div>
                    <h1 class="text-xl md:text-2xl lg:text-3xl font-black tracking-tight text-white flex items-center gap-2.5">
                        {{ $pageTitle }}
                    </h1>
                    <p class="text-xs md:text-sm text-indigo-100/90 mt-1 font-medium max-w-2xl leading-relaxed">
                        Kelola pendaftaran judul, pembentukan kelompok siswa, penugasan guru pembimbing, serta pantau progres bimbingan karya akhir.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('admin.final-projects.proposals.create') }}" class="group inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-400 to-teal-500 hover:from-emerald-300 hover:to-teal-400 text-slate-950 font-black text-sm shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all border border-emerald-300">
                    <div class="w-6 h-6 rounded-xl bg-slate-950/10 flex items-center justify-center text-slate-950">
                        <i class="fas fa-plus text-xs group-hover:rotate-90 transition-transform duration-300"></i>
                    </div>
                    <span>Buat Kelompok Baru</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-5 py-4 rounded-2xl text-xs md:text-sm shadow-lg shadow-emerald-500/15 flex items-center gap-3 border border-emerald-400">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check text-sm font-black"></i>
            </div>
            <span class="font-bold tracking-wide">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-gradient-to-r from-rose-500 to-red-600 text-white px-5 py-4 rounded-2xl text-xs md:text-sm shadow-lg shadow-rose-500/15 flex items-center gap-3 border border-rose-400">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation text-sm font-black"></i>
            </div>
            <span class="font-bold tracking-wide">{{ session('error') }}</span>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border-2 border-rose-200 text-rose-900 px-5 py-4 rounded-2xl text-xs md:text-sm shadow-md space-y-1.5">
            <div class="flex items-center gap-2 font-extrabold text-rose-700">
                <i class="fas fa-circle-exclamation"></i> Terdapat kesalahan input:
            </div>
            <ul class="list-disc pl-5 text-xs font-bold text-rose-800 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- 2. CLAYMORPHISM QUICK STATS (4 Counter Cards) --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
        {{-- Card 1: Total Usulan --}}
        <div class="bg-white rounded-3xl p-5 border-2 border-indigo-100 shadow-[0_8px_20px_-6px_rgba(79,70,229,0.12)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fas fa-folder-open text-lg"></i>
                </div>
                <span class="text-[11px] font-black uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100">Semua</span>
            </div>
            <div class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">{{ $stats['total'] ?? $projects->total() }}</div>
            <div class="text-xs font-bold text-slate-500 mt-0.5">Total Usulan Judul</div>
        </div>

        {{-- Card 2: Disetujui & Bimbingan --}}
        <div class="bg-white rounded-3xl p-5 border-2 border-emerald-100 shadow-[0_8px_20px_-6px_rgba(16,185,129,0.12)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fas fa-graduation-cap text-lg"></i>
                </div>
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">Aktif</span>
            </div>
            <div class="text-2xl md:text-3xl font-black text-emerald-600 tracking-tight">{{ $stats['approved'] ?? 0 }}</div>
            <div class="text-xs font-bold text-slate-500 mt-0.5">Bimbingan Berjalan</div>
        </div>

        {{-- Card 3: Menunggu Verifikasi --}}
        <div class="bg-white rounded-3xl p-5 border-2 border-amber-100 shadow-[0_8px_20px_-6px_rgba(245,158,11,0.12)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fas fa-clock-rotate-left text-lg"></i>
                </div>
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-100">Perlu Tindakan</span>
            </div>
            <div class="text-2xl md:text-3xl font-black text-amber-600 tracking-tight">{{ $stats['pending'] ?? 0 }}</div>
            <div class="text-xs font-bold text-slate-500 mt-0.5">Menunggu Verifikasi</div>
        </div>

        {{-- Card 4: Siap Sidang & Lulus --}}
        <div class="bg-white rounded-3xl p-5 border-2 border-sky-100 shadow-[0_8px_20px_-6px_rgba(14,165,233,0.12)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-2xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fas fa-award text-lg"></i>
                </div>
                <span class="text-[11px] font-black uppercase tracking-wider text-sky-700 bg-sky-50 px-2.5 py-1 rounded-full border border-sky-100">Tahap Akhir</span>
            </div>
            <div class="text-2xl md:text-3xl font-black text-sky-600 tracking-tight">{{ $stats['ready_for_exam'] ?? 0 }}</div>
            <div class="text-xs font-bold text-slate-500 mt-0.5">Layak Sidang / Selesai</div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- 3. FILTER & SEARCH CONTROL BAR --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl p-5 md:p-6 border-2 border-slate-100 shadow-[0_6px_20px_-4px_rgba(0,0,0,0.05)]">
        <form action="{{ route('admin.final-projects.proposals.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-6 items-center">
            
            {{-- Input Pencarian --}}
            <div class="md:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400">
                    <i class="fas fa-search text-sm"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari judul, nama ketua, atau anggota..." 
                       class="w-full bg-slate-50 hover:bg-white border-2 border-slate-200 focus:border-indigo-500 rounded-2xl pl-14 pr-4 py-3 text-xs md:text-sm font-bold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all">
            </div>

            {{-- Filter Status --}}
            <div class="md:col-span-3">
                <select name="status" onchange="this.form.submit()" 
                        class="w-full bg-slate-50 hover:bg-white border-2 border-slate-200 focus:border-indigo-500 rounded-2xl px-4 py-2.5 text-xs md:text-sm font-bold text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                    <option value="">Semua Status Proyek...</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending (Menunggu)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>✅ Disetujui (Approved)</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>📘 Pengerjaan / Bimbingan</option>
                    <option value="ready_for_exam" {{ request('status') === 'ready_for_exam' ? 'selected' : '' }}>🎯 Layak Sidang / Ujian</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>🎓 Selesai / Lulus</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>❌ Ditolak</option>
                </select>
            </div>

            {{-- Filter Sekolah (Khusus Super Admin) --}}
            @if($isSA)
                <div class="md:col-span-3">
                    <select name="school_id" onchange="this.form.submit()" 
                            class="w-full bg-slate-50 hover:bg-white border-2 border-slate-200 focus:border-indigo-500 rounded-2xl px-4 py-2.5 text-xs md:text-sm font-bold text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                        <option value="">Semua Sekolah...</option>
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>
                                🏫 {{ $sch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Tombol Filter & Reset --}}
            <div class="{{ $isSA ? 'md:col-span-2' : 'md:col-span-5' }} flex items-center gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-3 px-4 rounded-2xl text-xs md:text-sm shadow-md hover:shadow-lg transition-all active:scale-95 flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter text-xs"></i> Filter
                </button>
                <a href="{{ route('admin.final-projects.proposals.index') }}" 
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold py-2.5 px-4 rounded-2xl text-xs md:text-sm transition-all border border-slate-200 flex items-center justify-center gap-1.5" title="Reset Pencarian">
                    <i class="fas fa-arrows-rotate text-xs"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- 4. DAFTAR USULAN PROJECT (Playful Clay Table) --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-[0_8px_25px_-6px_rgba(0,0,0,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-indigo-50/50 border-b-2 border-slate-200 text-[11px] font-black text-slate-700 uppercase tracking-wider">
                        <th class="py-6 pl-8 pr-5">Kelompok Siswa</th>
                        <th class="py-6 px-5">Judul & Pembimbing</th>
                        <th class="py-6 px-5 text-center">Jenis</th>
                        <th class="py-6 px-5 text-center">Status</th>
                        <th class="py-6 px-5 text-center">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projects as $p)
                        @php
                            $statusPill = match($p->status) {
                                'pending'        => ['bg' => 'bg-amber-100/80 text-amber-900 border-amber-300', 'icon' => 'fa-clock', 'text' => 'Pending'],
                                'approved'       => ['bg' => 'bg-emerald-100/80 text-emerald-900 border-emerald-300', 'icon' => 'fa-circle-check', 'text' => 'Disetujui'],
                                'rejected'       => ['bg' => 'bg-rose-100/80 text-rose-900 border-rose-300', 'icon' => 'fa-circle-xmark', 'text' => 'Ditolak'],
                                'in_progress'    => ['bg' => 'bg-indigo-100/80 text-indigo-900 border-indigo-300', 'icon' => 'fa-book-open-reader', 'text' => 'Bimbingan'],
                                'ready_for_exam' => ['bg' => 'bg-sky-100/80 text-sky-900 border-sky-300', 'icon' => 'fa-award', 'text' => 'Layak Sidang'],
                                'completed'      => ['bg' => 'bg-teal-100/80 text-teal-900 border-teal-300', 'icon' => 'fa-graduation-cap', 'text' => 'Lulus'],
                                default          => ['bg' => 'bg-slate-100 text-slate-800 border-slate-300', 'icon' => 'fa-circle-dot', 'text' => $p->status]
                            };
                        @endphp
                        <tr class="hover:bg-indigo-50/20 transition-all duration-150 group">
                            
                            {{-- Kolom Kelompok & Anggota --}}
                            <td class="py-8 pl-8 pr-5 align-top min-w-[240px] max-w-[320px]">
                                @php
                                    $memberClassroomIds = $p->members->map(function($m) {
                                        return $m->student->currentClassroom->first()?->id ?? $m->student->classroom_id;
                                    })->filter()->unique();
                                    $isCrossClass = $memberClassroomIds->count() > 1;
                                @endphp
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center flex-shrink-0 text-sm font-black shadow-md shadow-indigo-500/20 border-2 border-white">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-black">
                                                <i class="fas fa-crown text-amber-500 text-[9px]"></i> Ketua
                                            </span>
                                            @if($isCrossClass)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 border border-purple-200 text-[10px] font-black" title="Kelompok memiliki anggota dari kelas berbeda">
                                                    <i class="fas fa-sparkles text-amber-500 text-[9px]"></i> Lintas Kelas
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-black text-slate-900 truncate" title="{{ $p->student->full_name }}">
                                            {{ $p->student->full_name }}
                                            <span class="text-[11px] font-bold text-indigo-600">({{ $p->student->currentClassroom->first()?->class_name ?? 'XII' }})</span>
                                        </div>

                                        @if($p->members && $p->members->count() > 1)
                                            <div class="mt-1.5 space-y-1 bg-slate-50/80 rounded-xl p-2 border border-slate-200">
                                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 block mb-0.5">
                                                    Anggota ({{ $p->members->where('role', 'member')->count() }} siswa):
                                                </span>
                                                @foreach($p->members->where('role', 'member') as $member)
                                                    @php
                                                        $mClass = $member->student->currentClassroom->first()?->class_name;
                                                    @endphp
                                                    <div class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5 truncate" title="{{ $member->student->full_name }}">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 flex-shrink-0"></span>
                                                        <span class="truncate">{{ $member->student->full_name }}</span>
                                                        @if($mClass)
                                                            <span class="text-[10px] text-slate-500 font-bold flex-shrink-0">({{ $mClass }})</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="mt-2 flex items-center gap-1.5 text-[11px] font-extrabold text-indigo-700">
                                            <i class="fas fa-school text-[10px] text-indigo-400"></i>
                                            <span class="truncate">{{ $p->student->school->name ?? 'Sekolah' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Judul & Guru Pembimbing --}}
                            <td class="py-8 px-5 align-top min-w-[280px]">
                                <div class="space-y-2">
                                    <h4 class="font-extrabold text-slate-900 text-sm leading-snug hover:text-indigo-600 transition-colors" title="{{ $p->title }}">
                                        {{ $p->title }}
                                    </h4>

                                    @if($p->abstract && $p->abstract !== 'Deskripsi ditentukan oleh Panitia')
                                        <p class="text-xs text-slate-600 font-medium line-clamp-2 leading-relaxed">
                                            {{ $p->abstract }}
                                        </p>
                                    @endif

                                    <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between flex-wrap gap-2 text-xs">
                                        <div class="inline-flex items-center gap-1.5 font-bold text-slate-700">
                                            <div class="w-5 h-5 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px]">
                                                <i class="fas fa-chalkboard-user"></i>
                                            </div>
                                            <span>Pembimbing: <strong class="text-slate-900">{{ $p->advisor->full_name ?? 'Belum Ditugaskan' }}</strong></span>
                                        </div>

                                        @if($p->current_stage)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-black">
                                                <i class="fas fa-chart-line text-[9px]"></i> {{ $p->current_stage_name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Jenis --}}
                            <td class="py-8 px-5 text-center align-top whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider {{ $p->type === 'penelitian_ilmiah' ? 'bg-purple-50 text-purple-800 border border-purple-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                    <i class="fas {{ $p->type === 'penelitian_ilmiah' ? 'fa-microscope' : 'fa-screwdriver-wrench' }} text-[10px]"></i>
                                    {{ $p->type === 'penelitian_ilmiah' ? 'Penelitian' : 'Project Akhir' }}
                                </span>
                            </td>

                            {{-- Kolom Status --}}
                            <td class="py-8 px-5 text-center align-top whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border shadow-xs {{ $statusPill['bg'] }}">
                                    <i class="fas {{ $statusPill['icon'] }} text-[11px]"></i>
                                    {{ $statusPill['text'] }}
                                </span>
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="py-8 px-5 text-center align-top whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-2">
                                    
                                    {{-- Tombol Verifikasi Cepat (jika pending) --}}
                                    @if($p->status === 'pending')
                                        <button @click="openModal({{ $p->id }}, '{{ addslashes($p->student->full_name) }}', '{{ addslashes($p->title) }}', {{ $p->student->school_id }})" 
                                                class="inline-flex items-center gap-1.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-extrabold px-3 py-1.5 rounded-xl text-xs shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all transform active:scale-95 border border-indigo-500" 
                                                title="Verifikasi & Tetapkan Pembimbing">
                                            <i class="fas fa-circle-check text-xs"></i> Verifikasi
                                        </button>
                                    @endif

                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('admin.final-projects.proposals.edit', $p->id) }}" 
                                       class="inline-flex items-center gap-1.5 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black px-3 py-1.5 rounded-xl text-xs shadow-sm hover:shadow transition-all active:scale-95 border border-amber-300" 
                                       title="Edit Judul, Ganti Pembimbing, atau Ubah Anggota Kelompok">
                                        <i class="fas fa-edit text-xs"></i> Edit
                                    </a>

                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('admin.final-projects.proposals.destroy', $p->id) }}" 
                                          method="POST" 
                                          class="inline-block" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus usulan project \'{{ addslashes($p->title) }}\' beserta seluruh data kelompoknya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center w-8 h-8 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-xl text-xs border border-rose-200 hover:border-rose-600 shadow-sm transition-all active:scale-95" 
                                                title="Hapus Usulan Project Ini">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center">
                                <div class="max-w-sm mx-auto space-y-4">
                                    <div class="w-16 h-16 rounded-3xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto text-2xl border-2 border-indigo-100 shadow-inner">
                                        <i class="fas fa-folder-plus"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-extrabold text-slate-800">Belum Ada Usulan Project</h3>
                                        <p class="text-xs text-slate-500 font-medium mt-1">Belum ada data usulan project atau tidak ada data yang cocok dengan filter pencarian Anda.</p>
                                    </div>
                                    <a href="{{ route('admin.final-projects.proposals.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                                        <i class="fas fa-plus"></i> Buat Kelompok Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($projects->hasPages())
            <div class="px-6 py-4 border-t-2 border-slate-100 bg-slate-50/70">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- 5. MODAL VERIFIKASI & ASSIGN (Alpine.js powered) --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <template x-teleport="body">
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeModal()"></div>

            {{-- Modal Content Card --}}
            <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl z-10 border-2 border-indigo-100 overflow-hidden"
                 x-show="showModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 @click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-indigo-900 to-purple-900 text-white">
                    <h3 class="text-base font-extrabold flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center shadow-md font-black text-xs">
                            <i class="fas fa-user-check"></i>
                        </div>
                        Verifikasi Pengajuan Judul
                    </h3>
                    <button @click="closeModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Body Form --}}
                <form :action="formAction" method="POST">
                    @csrf
                    <div class="p-6 space-y-5 max-h-[65vh] overflow-y-auto">

                        {{-- Ringkasan Siswa & Judul --}}
                        <div class="bg-indigo-50/70 rounded-2xl p-4.5 space-y-2.5 border-2 border-indigo-100">
                            <div>
                                <span class="text-[10px] text-indigo-700 font-black uppercase tracking-wider block">Siswa Pengusul</span>
                                <p class="font-black text-slate-900 text-sm mt-0.5" x-text="studentName"></p>
                            </div>
                            <div class="pt-2 border-t border-indigo-200/60">
                                <span class="text-[10px] text-indigo-700 font-black uppercase tracking-wider block">Judul Usulan</span>
                                <p class="font-bold text-slate-800 text-xs leading-relaxed mt-0.5" x-text="projectTitle"></p>
                            </div>
                        </div>

                        {{-- Keputusan Verifikasi --}}
                        <div>
                            <label class="block text-xs font-black text-slate-800 uppercase mb-2 tracking-wider">Pilih Keputusan</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="action = 'approve'"
                                        :class="action === 'approve' ? 'bg-emerald-500 text-white border-emerald-600 shadow-md font-black' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 font-bold'"
                                        class="py-3 px-4 rounded-2xl border-2 text-xs flex items-center justify-center gap-2 transition-all">
                                    <i class="fas fa-circle-check"></i> Setujui Judul
                                </button>
                                <button type="button" @click="action = 'reject'"
                                        :class="action === 'reject' ? 'bg-rose-500 text-white border-rose-600 shadow-md font-black' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 font-bold'"
                                        class="py-3 px-4 rounded-2xl border-2 text-xs flex items-center justify-center gap-2 transition-all">
                                    <i class="fas fa-circle-xmark"></i> Tolak Judul
                                </button>
                            </div>
                            <input type="hidden" name="action" :value="action">
                        </div>

                        {{-- Form Guru Pembimbing (Jika Approve) --}}
                        <div x-show="action === 'approve'" x-transition class="space-y-2">
                            <label for="advisor_id" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                Tetapkan Guru Pembimbing <span class="text-rose-500">*</span>
                            </label>
                            <select name="advisor_id" id="advisor_id" 
                                    class="w-full bg-slate-50 border-2 border-slate-200 focus:border-indigo-500 rounded-2xl px-4 py-3 text-xs font-bold text-slate-900 focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all">
                                <option value="">-- Pilih Guru Pembimbing --</option>
                                <template x-for="t in currentTeachers" :key="t.id">
                                    <option :value="t.id" x-text="t.name"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Form Alasan Penolakan (Jika Reject) --}}
                        <div x-show="action === 'reject'" x-transition class="space-y-2">
                            <label for="rejection_reason" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                Alasan Penolakan <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="rejection_reason" id="rejection_reason" rows="3" 
                                      placeholder="Tuliskan catatan perbaikan atau alasan penolakan judul..." 
                                      class="w-full bg-slate-50 border-2 border-slate-200 focus:border-rose-500 rounded-2xl p-4 text-xs font-bold text-slate-900 focus:outline-none focus:ring-4 focus:ring-rose-100 transition-all"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-slate-50 border-t-2 border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="closeModal()" 
                                class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-md transition flex items-center gap-2">
                            <i class="fas fa-check"></i> Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

{{-- Alpine.js State Management --}}
<script>
function proposalManager() {
    return {
        showModal: false,
        studentName: '',
        projectTitle: '',
        schoolId: null,
        formAction: '',
        action: 'approve',
        teachersBySchool: @json($teachersBySchool),
        allTeachers: @json($allTeachersJson),

        get currentTeachers() {
            if (this.schoolId && this.teachersBySchool[this.schoolId]) {
                return this.teachersBySchool[this.schoolId];
            }
            return this.allTeachers;
        },

        openModal(id, studentName, title, schoolId) {
            this.studentName = studentName;
            this.projectTitle = title;
            this.schoolId = schoolId;
            this.action = 'approve';
            this.formAction = "{{ url('/admin/final-projects/proposals') }}/" + id + "/assign";
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        }
    }
}
</script>
@endsection
