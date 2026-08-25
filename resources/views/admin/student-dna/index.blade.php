@extends(request()->routeIs('guru.*') ? 'layouts.guru' : 'layouts.admin')

@section('title', 'DNA Akademik Siswa 360°')

@section('content')
<div class="space-y-6">
    {{-- Hero Header (High Contrast, Crisp Typography) --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-2xl p-6 sm:p-7 text-white relative overflow-hidden shadow-md border border-slate-800">
        <div class="absolute -top-12 -right-12 w-56 h-56 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center text-xs text-slate-300 font-semibold mb-2.5 gap-2">
                <a href="{{ request()->routeIs('guru.*') ? route('guru.dashboard') : route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span class="text-slate-500">/</span>
                <span class="text-purple-300 font-bold">DNA Akademik 360°</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white flex items-center gap-3 tracking-tight">
                        <span class="w-10 h-10 rounded-xl bg-purple-500/20 border border-purple-400/30 flex items-center justify-center text-purple-300">
                            <i class="fas fa-fingerprint text-lg"></i>
                        </span>
                        DNA Akademik Siswa 360°
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-200 mt-2 max-w-2xl leading-relaxed font-normal">
                        Pemetaan potensi holistik berbasis data nyata: Kognitif, Keterampilan Vokasi, Kedisiplinan RFID, Keaktifan LMS, dan Karakter.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @if(auth()->user()->isSuperAdmin() && isset($schools))
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Unit Sekolah</label>
                <select name="school_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua Unit Sekolah --</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Rombel / Kelas</label>
                <select name="classroom_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                        {{ $cls->class_name ?? $cls->name }} {{ $cls->school ? '(' . ($cls->school->short_name ?? $cls->school->name) . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Cari Siswa (Nama / NISN / NIS)</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama, NISN, atau NIS..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                    <i class="fas fa-filter text-[10px]"></i>
                    <span>Cari</span>
                </button>
                <a href="{{ url()->current() }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition border border-slate-200" title="Reset Filter">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Daftar Siswa --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-800 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Nama Siswa</th>
                        <th class="p-4">Kelas & Unit Sekolah</th>
                        <th class="p-4 text-center">Tipe DNA Dominan</th>
                        <th class="p-4 text-center">Status Kalibrasi Data</th>
                        <th class="p-4 text-right">Aksi Analisis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $dnaService = app(\App\Services\StudentDnaService::class);
                    @endphp
                    @forelse($students as $idx => $s)
                    @php
                        $dna = $dnaService->analyze($s);
                        $classroomName = $s->currentClassroom->first()->class_name 
                            ?? $s->currentClassroom->first()->name 
                            ?? ($s->classrooms->first()->class_name ?? ($s->classroom->class_name ?? '-'));
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4 text-center font-bold text-slate-400">{{ $students->firstItem() + $idx }}</td>
                        <td class="p-4">
                            <p class="font-bold text-slate-900 text-sm leading-snug">{{ $s->full_name }}</p>
                            <p class="text-[11px] text-slate-500 font-mono">NISN: {{ $s->nisn ?: '-' }} &bull; NIS: {{ $s->formatted_nis ?: ($s->nis ?: '-') }}</p>
                        </td>
                        <td class="p-4">
                            <p class="font-bold text-slate-800 text-xs">{{ $classroomName }}</p>
                            <p class="text-[11px] text-slate-500">{{ $s->school->name ?? '-' }}</p>
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-white bg-gradient-to-r {{ $dna['archetype']['color'] }} shadow-xs">
                                <i class="fas {{ $dna['archetype']['badge_icon'] }} text-[10px]"></i>
                                {{ $dna['archetype']['title'] }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="inline-flex flex-col items-center">
                                <span class="text-[11px] font-bold text-slate-700">Akurasi: {{ $dna['confidence_score'] }}%</span>
                                <div class="w-24 bg-slate-200 rounded-full h-1.5 mt-1 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-gradient-to-r from-indigo-500 to-fuchsia-500" style="width: {{ $dna['confidence_score'] }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.show', $s) : route('admin.dna.show', $s) }}" class="px-3.5 py-1.5 bg-fuchsia-50 hover:bg-fuchsia-100 text-fuchsia-700 rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1 border border-fuchsia-200">
                                    <i class="fas fa-radar"></i> Detail 360°
                                </a>
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.pdf', $s) : route('admin.dna.pdf', $s) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition border border-slate-200 shadow-2xs" title="Cetak PDF">
                                    <i class="fas fa-file-pdf text-rose-500"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center">
                            <div class="max-w-md mx-auto space-y-3">
                                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto text-slate-400 text-2xl">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800">Tidak Ada Siswa yang Ditemukan</h4>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Tidak ada data siswa yang cocok dengan filter rombel / pencarian yang dipilih. Silakan reset filter untuk menampilkan semua siswa.
                                </p>
                                <a href="{{ url()->current() }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition shadow-sm">
                                    <i class="fas fa-undo"></i> Tampilkan Semua Siswa
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50/50">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
