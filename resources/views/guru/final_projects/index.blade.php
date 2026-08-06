@extends('layouts.guru')
@section('title', 'Bimbingan Tugas Akhir - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Bar --}}
    @php
        $teacherModel = \App\Models\Teacher::with('school')->where('user_id', auth()->id())->first();
        $pageTitle = 'Bimbingan Tugas Akhir';
        $entityName = 'Tugas Akhir';
        if ($teacherModel && $teacherModel->school->type === 'SMA') {
            $pageTitle = 'Bimbingan Penelitian';
            $entityName = 'Penelitian Ilmiah';
        } elseif ($teacherModel && $teacherModel->school->type === 'SMK') {
            $pageTitle = 'Bimbingan Project Akhir';
            $entityName = 'Project Akhir';
        }

        // Query Stats for Statistics Cards
        $teacherId = $teacherModel?->id ?? 0;
        $stats = \App\Models\FinalProject::where('advisor_id', $teacherId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'ready_for_exam' THEN 1 ELSE 0 END) as ready_for_exam,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            ")->first();
    @endphp

    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #047857 100%) !important;">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-md text-xl">
                    <i class="fas fa-file-signature text-black"></i>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-black text-white" style="color: #ffffff !important;">{{ $pageTitle }}</h1>
                    <p class="text-xs md:text-sm font-bold text-emerald-200 mt-1" style="color: #a7f3d0 !important;">Monitoring & Konsultasi Bimbingan Karya {{ $entityName }} Siswa</p>
                </div>
            </div>
            <div>
                <span class="bg-amber-400 text-black border-2 border-black px-4 py-2 rounded-2xl font-black text-xs uppercase tracking-wider shadow-sm inline-flex items-center gap-2">
                    <i class="fas fa-user-graduate text-black"></i> Pembimbing Utama
                </span>
            </div>
        </div>
    </div>

    {{-- Stats Cards Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Siswa --}}
        <div class="bg-gradient-to-br from-violet-650 to-indigo-800 text-white rounded-3xl shadow-md p-5 border border-white/20 relative overflow-hidden">
            <div class="relative z-10 space-y-1">
                <p class="text-xs font-black text-white uppercase tracking-wider">Total Bimbingan</p>
                <h3 class="text-2xl font-black">{{ $stats->total ?? 0 }}</h3>
                <p class="text-xs text-white font-bold">Siswa terdaftar</p>
            </div>
            <div class="absolute right-0 bottom-0 opacity-20 text-6xl transform translate-x-4 translate-y-2 pointer-events-none">
                <i class="fas fa-users"></i>
            </div>
        </div>

        {{-- Dalam Pengerjaan --}}
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-3xl shadow-md p-5 border border-white/20 relative overflow-hidden">
            <div class="relative z-10 space-y-1">
                <p class="text-xs font-black text-white uppercase tracking-wider">Dalam Pengerjaan</p>
                <h3 class="text-2xl font-black">{{ $stats->in_progress ?? 0 }}</h3>
                <p class="text-xs text-white font-bold">Sedang menyusun laporan</p>
            </div>
            <div class="absolute right-0 bottom-0 opacity-20 text-6xl transform translate-x-4 translate-y-2 pointer-events-none">
                <i class="fas fa-spinner"></i>
            </div>
        </div>

        {{-- Layak Sidang --}}
        <div class="bg-gradient-to-br from-cyan-600 to-blue-700 text-white rounded-3xl shadow-md p-5 border border-white/20 relative overflow-hidden">
            <div class="relative z-10 space-y-1">
                <p class="text-xs font-black text-white uppercase tracking-wider">Layak Sidang</p>
                <h3 class="text-2xl font-black">{{ $stats->ready_for_exam ?? 0 }}</h3>
                <p class="text-xs text-white font-bold">Siap diujikan</p>
            </div>
            <div class="absolute right-0 bottom-0 opacity-20 text-6xl transform translate-x-4 translate-y-2 pointer-events-none">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>

        {{-- Selesai / Lulus --}}
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-3xl shadow-md p-5 border border-white/20 relative overflow-hidden">
            <div class="relative z-10 space-y-1">
                <p class="text-xs font-black text-white uppercase tracking-wider">Selesai / Lulus</p>
                <h3 class="text-2xl font-black">{{ $stats->completed ?? 0 }}</h3>
                <p class="text-xs text-white font-bold">Lulus ujian akhir</p>
            </div>
            <div class="absolute right-0 bottom-0 opacity-20 text-6xl transform translate-x-4 translate-y-2 pointer-events-none">
                <i class="fas fa-award"></i>
            </div>
        </div>
    </div>

    {{-- Filter & Search Card --}}
    <div class="bg-white rounded-3xl shadow-md border border-gray-250 p-5">
        <form action="{{ route('guru.final-projects.bimbingan.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Search --}}
            <div class="relative sm:col-span-2">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-500 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa atau judul {{ $entityName }}..." class="w-full bg-white border border-gray-300 rounded-2xl pl-11 pr-4 py-2.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition font-medium">
            </div>

            {{-- Status filter --}}
            <div>
                <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-gray-300 rounded-2xl px-4 py-2.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition font-bold">
                    <option value="">Semua Status...</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Belum Mulai)</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Dalam Pengerjaan</option>
                    <option value="ready_for_exam" {{ request('status') === 'ready_for_exam' ? 'selected' : '' }}>Layak Sidang</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai / Lulus</option>
                </select>
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-3xl shadow-md border border-gray-250 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-300 text-xs font-black text-gray-700 uppercase tracking-wider text-left">
                    <tr>
                        <th class="py-4 pl-6">Kelompok / Siswa</th>
                        <th class="py-4">Judul {{ $entityName }}</th>
                        <th class="py-4">Jenis</th>
                        <th class="py-4 text-center">Status</th>
                        <th class="py-4 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-250 text-xs text-gray-750">
                    @forelse($projects as $p)
                        <tr class="hover:bg-gray-100 transition">
                            <td class="py-4.5 pl-6">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100 text-emerald-800 flex items-center justify-center flex-shrink-0 text-sm border border-emerald-300">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 text-xs leading-tight truncate">Ketua: {{ $p->student->full_name }}</p>
                                        @if($p->members && $p->members->count() > 1)
                                            <ul class="text-xs text-gray-650 mt-1 space-y-0.5 border-l-2 border-emerald-500 pl-1.5 leading-normal font-bold">
                                                @foreach($p->members->where('role', 'member') as $member)
                                                    <li class="truncate">- {{ $member->student->full_name }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        <p class="text-xs font-black text-emerald-600 mt-1.5 uppercase tracking-wider">{{ $p->student->school->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4.5 max-w-[300px]">
                                <p class="font-bold text-gray-900 leading-relaxed truncate" title="{{ $p->title }}">{{ $p->title }}</p>
                                <p class="text-xs text-gray-600 mt-1 line-clamp-1 leading-normal font-bold" title="{{ $p->abstract }}">{{ $p->abstract }}</p>
                            </td>
                            <td class="py-4.5">
                                <span class="bg-gray-150 text-gray-800 px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wide border border-gray-300">
                                    {{ $p->type === 'penelitian_ilmiah' ? 'Penelitian Ilmiah' : 'Project Akhir' }}
                                </span>
                            </td>
                            <td class="py-4.5 text-center">
                                @php
                                    $statusClass = match($p->status) {
                                        'approved' => 'bg-blue-100 text-blue-800 border-blue-300',
                                        'in_progress' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                                        'ready_for_exam' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
                                        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        default => 'bg-gray-150 text-gray-800 border-gray-300'
                                    };
                                    $statusText = match($p->status) {
                                        'approved' => 'Disetujui',
                                        'in_progress' => 'Pengerjaan',
                                        'ready_for_exam' => 'Layak Sidang',
                                        'completed' => 'Selesai',
                                        default => $p->status
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black border {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="py-4.5 pr-6 text-right">
                                <a href="{{ route('guru.final-projects.bimbingan.show', $p->id) }}" class="inline-flex items-center gap-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-extrabold px-3 py-1.5 rounded-xl text-xs border border-emerald-300 transition-all shadow-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center text-gray-700 italic font-bold">
                                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3.5 border border-gray-300">
                                    <i class="fas fa-file-signature text-xl text-gray-505"></i>
                                </div>
                                <p class="text-xs font-semibold text-gray-600">Belum ada siswa bimbingan yang ditugaskan ke Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
            <div class="px-6 py-4 border-t border-gray-250 bg-gray-50">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
