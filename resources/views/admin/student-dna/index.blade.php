@extends(request()->routeIs('guru.*') ? 'layouts.guru' : 'layouts.admin')

@section('title', 'DNA Akademik Siswa 360°')

@section('content')
@php
    $badgeStyles = [
        'logic'         => 'bg-sky-50 border-sky-300 text-sky-900',
        'communication' => 'bg-emerald-50 border-emerald-300 text-emerald-900',
        'technical'     => 'bg-indigo-50 border-indigo-300 text-indigo-900',
        'social'        => 'bg-amber-50 border-amber-300 text-amber-900',
        'creative'      => 'bg-purple-50 border-purple-300 text-purple-900',
        'discipline'    => 'bg-rose-50 border-rose-300 text-rose-900',
    ];
@endphp

<div class="space-y-5" x-data="{ showGuide: false }">
    {{-- Header Institusional (Solid Slate, Tanpa Gradien/Ornamen Berlebih) --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl px-5 py-5 sm:px-6 text-white">
        <div class="flex items-center text-xs text-slate-300 font-medium mb-2.5 gap-2">
            <a href="{{ request()->routeIs('guru.*') ? route('guru.dashboard') : route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
            <span class="text-slate-600">/</span>
            <span class="text-sky-300 font-semibold">DNA Akademik Siswa 360°</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
                    <i class="fas fa-fingerprint text-sky-400"></i>
                    <span>DNA Akademik Siswa 360°</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Pemetaan potensi belajar siswa berbasis data nyata PembdaHUB (nilai per mata pelajaran, LMS, ujian CBT, presensi RFID, tugas akhir/PKL, prestasi, dan cita-cita) beserta analisis personal ClassAI.
                </p>
            </div>

            <button type="button" @click="showGuide = !showGuide"
                    class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-100 text-xs font-semibold transition inline-flex items-center gap-2 shrink-0 self-start lg:self-auto">
                <i class="fas fa-book-open text-sky-400"></i>
                <span x-text="showGuide ? 'Tutup Panduan & Kamus' : 'Panduan & Kamus 6 Profil'"></span>
            </button>
        </div>
    </div>

    {{-- Panduan & Kamus 6 Profil Belajar (Terlipat Secara Default agar Daftar Siswa Langsung Terlihat) --}}
    <div x-show="showGuide" x-cloak x-transition class="bg-white border border-slate-300 rounded-xl overflow-hidden text-xs">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200 border-b border-slate-200 bg-slate-50/60">
            <div class="p-4 space-y-1">
                <p class="font-bold text-slate-900">Tujuan Pemetaan</p>
                <p class="text-slate-600 leading-relaxed">
                    Membantu guru BK, wali kelas, kepala sekolah, dan yayasan memahami profil keunggulan spesifik setiap siswa secara menyeluruh sebagai dasar bimbingan studi dan karier.
                </p>
            </div>
            <div class="p-4 space-y-1">
                <p class="font-bold text-slate-900">Sumber Data Nyata 360° &amp; ClassAI</p>
                <p class="text-slate-600 leading-relaxed">
                    Mengolah nilai per mapel, tugas LMS, CBT, presensi RFID, prestasi, karya/PKL, dan cita-cita siswa. Profil bakat dihitung dari 5 sumbu kompetensi tanpa tertimpa angka presensi.
                </p>
            </div>
            <div class="p-4 space-y-1">
                <p class="font-bold text-slate-900">Catatan Anti-Salah Tafsir</p>
                <p class="text-slate-600 leading-relaxed">
                    Pemetaan ini berfungsi sebagai alat bantu pendampingan belajar yang bersifat dinamis mengikuti perkembangan siswa, bukan label permanen atau penentu tunggal masa depan.
                </p>
            </div>
        </div>

        <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-brain text-sky-700"></i> 1. Analis Logis &amp; Pemecah Masalah</div>
                <p class="text-slate-600 leading-relaxed">Menonjol pada penalaran matematika, sains, pola data, dan penyelesaian soal hitungan secara runtut. Arah: Teknologi Informasi, Rekayasa, Riset Sains, Keuangan.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-comments text-emerald-700"></i> 2. Komunikator &amp; Artikulator Bahasa</div>
                <p class="text-slate-600 leading-relaxed">Cakap literasi bacaan, tata bahasa, menulis argumen terstruktur, dan berbicara persuasif. Arah: Komunikasi Publik, Hukum, Pendidikan Bahasa, Diplomasi.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-wrench text-indigo-700"></i> 3. Praktisi Teknis &amp; Rekayasa Terapan</div>
                <p class="text-slate-600 leading-relaxed">Unggul dalam kerja praktik bengkel atau laboratorium, perakitan perangkat, jaringan, dan otomasi. Arah: Teknik Industri, Otomotif, Elektronika, Jaringan Komputer.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-users text-amber-700"></i> 4. Pemimpin Sosial &amp; Kolaborator</div>
                <p class="text-slate-600 leading-relaxed">Cakap memimpin tim, berempati tinggi, aktif di kepengurusan ekstrakurikuler, dan kuat pada rumpun sosial-ekonomi. Arah: Manajemen, Psikologi, Kebijakan Publik.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-palette text-purple-700"></i> 5. Kreator Visual &amp; Perancang Inovatif</div>
                <p class="text-slate-600 leading-relaxed">Peka terhadap estetika, rancangan grafis, multimedia, dan karya orisinal. Arah: Desain Komunikasi Visual, Arsitektur, Produksi Media Digital.</p>
            </div>
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="font-bold text-slate-900 flex items-center gap-2"><i class="fas fa-clock text-rose-700"></i> 6. Pelaksana Tekun &amp; Berdisiplin Tinggi</div>
                <p class="text-slate-600 leading-relaxed">Konsisten menjaga kehadiran sekolah dan ketepatan waktu pengumpulan tugas. Arah: Tata Kelola Perkantoran, Logistik, Akuntansi, Audit, Sekolah Kedinasan.</p>
            </div>
        </div>
    </div>

    {{-- Bar Filter Pencarian --}}
    <div class="bg-white border border-slate-300 rounded-xl p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end text-xs">
            @if(auth()->user()->isSuperAdmin() && isset($schools))
            <div class="sm:col-span-3">
                <label class="block font-bold text-slate-700 mb-1">Unit Sekolah</label>
                <select name="school_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white font-semibold text-slate-800 focus:outline-none focus:border-sky-600">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="{{ auth()->user()->isSuperAdmin() && isset($schools) ? 'sm:col-span-3' : 'sm:col-span-4' }}">
                <label class="block font-bold text-slate-700 mb-1">Rombel / Kelas</label>
                <select name="classroom_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white font-semibold text-slate-800 focus:outline-none focus:border-sky-600">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                        {{ $cls->class_name ?? $cls->name }} {{ $cls->school ? '(' . ($cls->school->short_name ?? $cls->school->name) . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="{{ auth()->user()->isSuperAdmin() && isset($schools) ? 'sm:col-span-6' : 'sm:col-span-8' }} flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block font-bold text-slate-700 mb-1">Cari Peserta Didik (Nama / NISN / NIS)</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama, NISN, atau NIS..."
                               class="w-full pl-8 pr-3 py-2 rounded-lg border border-slate-300 bg-white font-medium text-slate-800 focus:outline-none focus:border-sky-600">
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold transition inline-flex items-center gap-1.5">
                    <i class="fas fa-filter text-[10px]"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ url()->current() }}" class="px-3 py-2 bg-white hover:bg-slate-100 text-slate-700 rounded-lg font-semibold transition border border-slate-300" title="Atur Ulang Filter">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Direktori Evaluasi Siswa --}}
    <div class="bg-white border border-slate-300 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">No</th>
                        <th class="px-4 py-3">Peserta Didik</th>
                        <th class="px-4 py-3">Kelas &amp; Unit Sekolah</th>
                        <th class="px-4 py-3">Profil Dominan Belajar</th>
                        <th class="px-4 py-3">Mapel Unggulan</th>
                        <th class="px-4 py-3 text-center">Kelengkapan Data</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php $dnaService = app(\App\Services\StudentDnaService::class); @endphp
                    @forelse($students as $idx => $s)
                    @php
                        $dna = $dnaService->analyze($s);
                        $classroomName = $s->currentClassroom->first()->class_name
                            ?? $s->currentClassroom->first()->name
                            ?? ($s->classrooms->first()->class_name ?? ($s->classroom->class_name ?? '-'));
                        $pKey = $dna['archetype']['primary_key'] ?? 'technical';
                        $pStyle = $badgeStyles[$pKey] ?? 'bg-slate-100 border-slate-300 text-slate-800';
                        $topOne = $dna['subject_profile']['top_subjects'][0] ?? null;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-4 py-3 text-center font-bold text-slate-400 tabular-nums">{{ $students->firstItem() + $idx }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $s->photo_url }}"
                                     class="w-9 h-9 rounded-lg object-cover border border-slate-200 bg-slate-100 shrink-0"
                                     alt="{{ $s->full_name }}"
                                     onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-xs sm:text-sm leading-snug truncate">{{ $s->full_name }}</p>
                                    <p class="text-[11px] text-slate-500 font-mono tabular-nums">NISN: {{ $s->nisn ?: '-' }} · NIS: {{ $s->formatted_nis ?: ($s->nis ?: '-') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-800">{{ $classroomName }}</p>
                            <p class="text-[11px] text-slate-500">
                                {{ $s->school->name ?? '-' }}
                                @if(!empty($dna['vocational_major']))
                                · <span class="font-semibold text-amber-800">{{ $dna['vocational_major']['code'] }}</span>
                                @endif
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border text-[11px] font-bold {{ $pStyle }}">
                                <i class="fas {{ $dna['archetype']['badge_icon'] }} text-[10px]"></i>
                                <span>{{ $dna['archetype']['title'] }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($topOne)
                            <div class="font-semibold text-slate-800 truncate max-w-[190px]">{{ $topOne['subject_name'] }}</div>
                            <div class="text-[11px] text-emerald-700 font-bold tabular-nums">Nilai {{ $topOne['score'] }}</div>
                            @else
                            <span class="text-slate-400">Belum ada nilai</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex flex-col items-center">
                                <span class="text-[11px] font-bold text-slate-700 tabular-nums">{{ $dna['confidence_score'] }}%</span>
                                <div class="w-20 bg-slate-200 rounded-full h-1.5 mt-1 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-sky-600" style="width: {{ $dna['confidence_score'] }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.show', $s) : route('admin.dna.show', $s) }}"
                                   class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition inline-flex items-center gap-1.5">
                                    <i class="fas fa-folder-open text-[10px]"></i>
                                    <span>Dossier 360°</span>
                                </a>
                                <a href="{{ request()->routeIs('guru.*') ? route('guru.dna.pdf', $s) : route('admin.dna.pdf', $s) }}"
                                   class="px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300" title="Cetak Rapor DNA (PDF)">
                                    <i class="fas fa-file-pdf text-rose-600"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center">
                            <div class="max-w-md mx-auto space-y-2.5">
                                <p class="text-sm font-bold text-slate-900">Tidak Ada Peserta Didik Ditemukan</p>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Tidak ada data siswa yang cocok dengan filter kelas atau kata kunci pencarian saat ini.
                                </p>
                                <a href="{{ url()->current() }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold text-xs transition">
                                    <i class="fas fa-undo"></i>
                                    <span>Tampilkan Semua Siswa</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
