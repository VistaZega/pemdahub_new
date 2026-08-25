@extends(request()->routeIs('guru.*') ? 'layouts.guru' : 'layouts.admin')

@section('title', 'DNA Akademik Siswa 360°')

@section('content')
<div class="space-y-6">
    {{-- Hero Header --}}
    <div class="bg-gradient-to-r from-fuchsia-600 via-purple-600 to-indigo-700 rounded-2xl p-6 sm:p-7 text-white relative overflow-hidden shadow-sm">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4 blur-xl"></div>
        <div class="relative">
            <div class="flex items-center text-sm text-white/70 mb-2 gap-2">
                <a href="{{ request()->routeIs('guru.*') ? route('guru.dashboard') : route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span>/</span>
                <span class="text-white font-semibold">DNA Akademik 360°</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black flex items-center gap-2.5">
                        <i class="fas fa-dna text-fuchsia-300"></i> DNA Akademik Siswa 360°
                    </h1>
                    <p class="text-xs sm:text-sm text-purple-100 mt-1 max-w-2xl leading-relaxed">
                        Pemetaan potensi holistik berbasis data nyata: Kognitif, Keterampilan Vokasi, Kedisiplinan RFID, Keaktifan LMS, dan Karakter.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @if(auth()->user()->isSuperAdmin() && isset($schools))
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1.5">Unit Sekolah</label>
                <select name="school_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua Unit Sekolah --</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1.5">Rombel / Kelas</label>
                <select name="classroom_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                        {{ $cls->class_name ?? $cls->name }} ({{ $cls->school->code ?? '' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-600 mb-1.5">Cari Siswa (Nama / NISN)</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau NISN..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-sm transition">
                    Cari
                </button>
                <a href="{{ url()->current() }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold text-xs transition" title="Reset">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Daftar Siswa --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-100">
                    <tr>
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Nama Siswa</th>
                        <th class="p-4">Kelas & Unit</th>
                        <th class="p-4 text-center">Tipe DNA Dominan</th>
                        <th class="p-4 text-center">Status Kalibrasi Data</th>
                        <th class="p-4 text-right">Aksi Analisis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $dnaService = app(\App\Services\StudentDnaService::class);
                    @endphp
                    @forelse($students as $idx => $s)
                    @php
                        $dna = $dnaService->analyze($s);
                    @endphp
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 text-center font-bold text-gray-400">{{ $students->firstItem() + $idx }}</td>
                        <td class="p-4">
                            <p class="font-bold text-gray-900 text-sm leading-snug">{{ $s->full_name }}</p>
                            <p class="text-[10px] text-gray-400 font-mono">NISN: {{ $s->nisn ?: '-' }} &bull; NIS: {{ $s->nis ?: '-' }}</p>
                        </td>
                        <td class="p-4">
                            <p class="font-bold text-gray-800">{{ $s->currentClassroom->first()->class_name ?? $s->currentClassroom->first()->name ?? '-' }}</p>
                            <p class="text-[10px] text-gray-500">{{ $s->school->name ?? '-' }}</p>
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-white bg-gradient-to-r {{ $dna['archetype']['color'] }} shadow-xs">
                                <i class="fas {{ $dna['archetype']['badge_icon'] }} text-[10px]"></i>
                                {{ $dna['archetype']['title'] }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="inline-flex flex-col items-center">
                                <span class="text-[10px] font-bold text-gray-700">Akurasi: {{ $dna['confidence_score'] }}%</span>
                                <div class="w-20 bg-gray-200 rounded-full h-1.5 mt-1 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-gradient-to-r from-indigo-500 to-fuchsia-500" style="width: {{ $dna['confidence_score'] }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.show', $s) : route('admin.dna.show', $s) }}" class="px-3.5 py-1.5 bg-fuchsia-50 hover:bg-fuchsia-100 text-fuchsia-700 rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1">
                                    <i class="fas fa-radar"></i> Detail 360°
                                </a>
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.pdf', $s) : route('admin.dna.pdf', $s) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition shadow-2xs" title="Cetak PDF">
                                    <i class="fas fa-file-pdf text-rose-500"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400 italic">
                            Tidak ada data siswa yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
