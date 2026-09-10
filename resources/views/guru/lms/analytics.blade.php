@extends('layouts.guru')

@section('title', 'Analitik Pembelajaran - ' . ($course->course_name ?? $course->name))

@section('content')
<div class="space-y-6" id="analyticsContainer">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- TOAST NOTIFICATION --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div id="copyToast" class="fixed top-4 left-4 right-4 sm:left-auto sm:right-5 sm:w-auto z-50 transform transition-all duration-300 translate-y-[-100px] opacity-0 pointer-events-none">
        <div class="bg-slate-900 text-white px-5 py-3.5 rounded-xl shadow-2xl border border-slate-700 flex items-center gap-3 max-w-sm sm:max-w-md mx-auto">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold shrink-0">
                <i class="fas fa-check-circle text-lg"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-white truncate" id="toastTitle">Berhasil Disalin!</p>
                <p class="text-[11px] text-slate-300" id="toastMessage">Teks pesan WhatsApp siap dibagikan.</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HEADER BANNER --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-900 rounded-2xl p-6 md:p-8 text-white shadow-xl border border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="{{ route('guru.lms.show', $course->id) }}" class="inline-flex items-center gap-2 text-xs font-bold text-cyan-400 hover:text-cyan-300 mb-2 transition">
                    <i class="fas fa-arrow-left"></i> Kembali ke Course
                </a>
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Analitik Pembelajaran Siswa</h1>
                <p class="text-xs text-slate-300 mt-1">
                    Course: <strong class="text-white">{{ $course->course_name ?? $course->name }}</strong> 
                    | Mapel: <span class="text-cyan-300 font-semibold">{{ $course->subject->subject_name ?? $course->subject->name ?? '-' }}</span>
                </p>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700">
                        {{ count($classesMap) }} Rombel Kelas Terdaftar
                    </span>
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-950 text-indigo-300 border border-indigo-800">
                        Tahun Ajaran: {{ $course->academicYear->name ?? 'Aktif' }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('guru.lms.export-gradebook', $course->id) }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-lg transition transform hover:-translate-y-0.5">
                    <i class="fas fa-file-excel text-sm"></i> Ekspor Rekap Excel
                </a>
                <div class="bg-slate-800/90 border border-slate-700 rounded-xl px-5 py-2.5 text-center shadow-inner">
                    <span class="text-2xl font-black text-cyan-400 block leading-none">{{ $avgCourseProgress }}%</span>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 mt-1 block">Rata-rata Kelas</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- STAT CARDS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4 transition hover:shadow-md">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl shadow-xs">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-500 tracking-wide block">Total Siswa</span>
                <span class="text-2xl font-black text-gray-900">{{ count($studentStats) }} <span class="text-xs font-semibold text-gray-500">Siswa</span></span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4 transition hover:shadow-md">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xl shadow-xs">
                <i class="fas fa-book-open"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-500 tracking-wide block">Materi Terbit</span>
                <span class="text-2xl font-black text-gray-900">{{ $course->materials->count() }} <span class="text-xs font-semibold text-gray-500">Materi</span></span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4 transition hover:shadow-md">
            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xl shadow-xs">
                <i class="fas fa-tasks"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-500 tracking-wide block">Tugas Terbit</span>
                <span class="text-2xl font-black text-gray-900">{{ $course->assignments->count() }} <span class="text-xs font-semibold text-gray-500">Tugas</span></span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border-2 border-red-200 shadow-sm flex items-center gap-4 transition hover:shadow-md bg-red-50/30">
            <div class="w-12 h-12 rounded-xl bg-red-600 text-white flex items-center justify-center font-bold text-xl shadow-sm">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-red-600 tracking-wide block">Siswa Perlu Perhatian</span>
                <span class="text-2xl font-black text-red-600">{{ count($atRiskStudents) }} <span class="text-xs font-semibold text-red-500">Siswa</span></span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- CLASS FILTER TABS (PILIHAN ROMBEL KELAS) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-filter"></i>
                </div>
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-900">Filter Pengelompokkan Kelas</h3>
                    <p class="text-[11px] text-gray-500">Pilih kelas untuk memfilter daftar dan menyiapkan laporan per kelas</p>
                </div>
            </div>
            <div class="text-xs text-gray-500 font-semibold">
                Menampilkan: <strong class="text-indigo-600" id="currentFilterLabel">Semua Kelas</strong>
            </div>
        </div>

        <div class="flex flex-wrap gap-2" id="classFilterTabs">
            <button type="button" onclick="filterByClass('all')" id="btn-tab-all" class="class-tab-btn px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 bg-slate-900 text-white shadow-sm border border-slate-900">
                <i class="fas fa-layer-group text-xs"></i>
                <span>Semua Kelas</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-[10px] font-bold text-slate-200 border border-slate-700">
                    {{ count($studentStats) }}
                </span>
                @if(count($atRiskStudents) > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-red-500 text-[10px] font-bold text-white" title="{{ count($atRiskStudents) }} siswa perlu perhatian">
                    {{ count($atRiskStudents) }}
                </span>
                @endif
            </button>

            @foreach($classesMap as $className => $cData)
            @php
                $tabSlug = Str::slug($className);
            @endphp
            <button type="button" onclick="filterByClass('{{ $className }}')" id="btn-tab-{{ $tabSlug }}" class="class-tab-btn px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 border border-gray-200">
                <i class="fas fa-chalkboard text-xs text-gray-500"></i>
                <span>{{ $className }}</span>
                <span class="px-1.5 py-0.2 rounded-md bg-gray-200 text-[10px] font-bold text-gray-700">
                    {{ $cData['total_students'] }}
                </span>
                @if($cData['at_risk_count'] > 0)
                <span class="px-1.5 py-0.2 rounded-md bg-red-100 text-red-700 text-[10px] font-extrabold border border-red-200" title="{{ $cData['at_risk_count'] }} siswa perlu perhatian">
                    {{ $cData['at_risk_count'] }} ⚠️
                </span>
                @else
                <span class="text-[10px] text-emerald-600 font-bold" title="Tuntas & Aktif">✓</span>
                @endif
            </button>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- DAFTAR SISWA PERLU PERHATIAN KHUSUS (GROUPED PER KELAS) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="space-y-4" id="atRiskSectionWrapper">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center font-bold shadow-sm">
                    <i class="fas fa-user-clock text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900">Daftar Siswa Perlu Perhatian Khusus</h3>
                    <p class="text-xs text-gray-500">Siswa dengan persentase membaca materi &lt; 40% atau belum mengumpulkan tugas (dikelompokkan per kelas)</p>
                </div>
            </div>
            <div class="hidden sm:block text-right">
                <span class="text-xs font-bold px-3 py-1 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    Total: {{ count($atRiskStudents) }} Siswa Butuh Dorongan
                </span>
            </div>
        </div>

        @if(count($atRiskStudents) === 0)
        <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-8 text-center shadow-sm">
            <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-2xl font-bold mb-3 shadow-inner">
                <i class="fas fa-check-double"></i>
            </div>
            <h4 class="text-base font-black text-emerald-900">Luar Biasa! Tidak Ada Siswa Tertinggal</h4>
            <p class="text-xs text-emerald-700 mt-1 max-w-md mx-auto">Seluruh siswa pada semua rombel telah membaca materi minimal 40% dan aktif mengumpulkan tugas pada course ini.</p>
        </div>
        @else

        {{-- Seksi Setiap Kelas --}}
        @foreach($classesMap as $className => $cData)
        @php
            $classSlug = Str::slug($className);
            $atRiskList = $cData['at_risk_students'];
            $waliName = $cData['wali_name'] ?? 'Bapak/Ibu Wali Kelas';
            $waliPhone = $cData['wali_phone'];

            // Susun Teks WhatsApp Resmi Siap Kirim
            $waMessage = "*LAPORAN PERKEMBANGAN BELAJAR SISWA LMS*\n";
            $waMessage .= "*Course:* " . ($course->course_name ?? $course->name) . "\n";
            $waMessage .= "*Kelas:* " . $className . "\n";
            $waMessage .= "*Wali Kelas:* " . $waliName . "\n\n";
            $waMessage .= "Yth. Bapak/Ibu Wali Kelas & Siswa Kelas " . $className . ",\n";
            $waMessage .= "Berikut rekap siswa yang *Perlu Perhatian Khusus* pada pembelajaran LMS (progres materi < 40% atau belum tuntas tugas):\n\n";

            if (count($atRiskList) > 0) {
                foreach ($atRiskList as $idx => $stAtRisk) {
                    $waMessage .= ($idx + 1) . ". *" . ($stAtRisk['student']->user->name ?? '-') . "*\n";
                    $waMessage .= "   • Progres Materi: " . $stAtRisk['progress'] . "%\n";
                    $waMessage .= "   • Tugas Dikumpul: " . $stAtRisk['submissions_count'] . " dari " . $course->assignments->count() . " tugas\n";
                }
            } else {
                $waMessage .= "Alhamdulillah seluruh siswa kelas ini aktif dan tuntas!\n";
            }

            $waMessage .= "\n*Ringkasan Kelas:*\n";
            $waMessage .= "• Total Siswa: " . $cData['total_students'] . " siswa\n";
            $waMessage .= "• Perlu Perhatian: " . $cData['at_risk_count'] . " siswa\n";
            $waMessage .= "• Rata-rata Progres: " . $cData['avg_progress'] . "%\n\n";
            $waMessage .= "Mohon bantuan Bapak/Ibu Wali Kelas untuk mendorong siswa-siswi di atas agar segera mengakses LMS dan menuntaskan pembelajaran. Terima kasih.";

            $encodedWa = rawurlencode($waMessage);
        @endphp

        <div class="class-group-card bg-white rounded-2xl border-2 {{ $cData['at_risk_count'] > 0 ? 'border-red-200' : 'border-emerald-200' }} shadow-sm overflow-hidden transition-all duration-200" data-class-name="{{ $className }}">
            {{-- Header Kelas --}}
            <div class="p-5 {{ $cData['at_risk_count'] > 0 ? 'bg-red-50/50' : 'bg-emerald-50/40' }} border-b {{ $cData['at_risk_count'] > 0 ? 'border-red-100' : 'border-emerald-100' }} flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white font-black text-sm tracking-wide shadow-sm flex items-center gap-2">
                        <i class="fas fa-users-rectangle text-xs text-indigo-400"></i> Kelas {{ $className }}
                    </span>
                    @if($cData['at_risk_count'] > 0)
                    <span class="px-3 py-1 rounded-xl bg-red-600 text-white font-black text-xs shadow-xs">
                        {{ $cData['at_risk_count'] }} Siswa Perlu Perhatian
                    </span>
                    @else
                    <span class="px-3 py-1 rounded-xl bg-emerald-600 text-white font-black text-xs shadow-xs">
                        Semua Siswa Tuntas 🌟
                    </span>
                    @endif
                    <span class="text-xs text-gray-500 font-semibold">
                        (Total: {{ $cData['total_students'] }} Siswa | Rata-rata: <strong>{{ $cData['avg_progress'] }}%</strong>)
                    </span>
                    <div class="flex items-center gap-1.5 text-xs text-gray-600 bg-white/80 px-3 py-1 rounded-lg border border-gray-200">
                        <i class="fas fa-user-tie text-indigo-600"></i>
                        <span>Wali Kelas: <strong class="text-gray-900">{{ $cData['wali_name'] ?? 'Belum Ditentukan' }}</strong></span>
                    </div>
                </div>

                {{-- Aksi WhatsApp Per Kelas --}}
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    {{-- Hidden text area for JS copying --}}
                    <textarea id="waText-{{ $classSlug }}" class="hidden">{{ $waMessage }}</textarea>

                    {{-- Tombol Salin Teks WA --}}
                    <button type="button" onclick="copyWaText('{{ $classSlug }}', '{{ $className }}')" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm transition transform hover:-translate-y-0.5">
                        <i class="fas fa-copy text-amber-400"></i> Salin Teks WA
                    </button>

                    {{-- Tombol Chat ke Wali Kelas (jika nomor HP ada) --}}
                    @if($waliPhone)
                    <a href="https://wa.me/{{ $waliPhone }}?text={{ $encodedWa }}" target="_blank" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm transition transform hover:-translate-y-0.5" title="Kirim langsung pesan ke WhatsApp Wali Kelas ({{ $waliPhone }})">
                        <i class="fab fa-whatsapp text-sm text-emerald-200"></i> Chat Wali Kelas
                    </a>
                    @endif

                    {{-- Tombol Share ke Grup WhatsApp --}}
                    <a href="https://api.whatsapp.com/send?text={{ $encodedWa }}" target="_blank" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-300 text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-xs transition transform hover:-translate-y-0.5" title="Buka WhatsApp untuk membagikan ke Grup Kelas">
                        <i class="fab fa-whatsapp text-emerald-600"></i> Share ke Grup
                    </a>
                </div>
            </div>

            {{-- Kartu Grid Siswa Perlu Perhatian di Kelas Ini --}}
            <div class="p-5">
                @if(count($atRiskList) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    @foreach($atRiskList as $atRisk)
                    <div class="bg-white p-4 rounded-xl border border-red-200 flex flex-col justify-between shadow-xs hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-800 text-[10px] font-black rounded-md mb-1 inline-block">
                                    {{ $className }}
                                </span>
                                <h4 class="font-black text-gray-900 text-sm truncate" title="{{ $atRisk['student']->user->name ?? '-' }}">
                                    {{ $atRisk['student']->user->name ?? '-' }}
                                </h4>
                                <div class="text-[11px] text-gray-500 mt-0.5">
                                    NISN: {{ $atRisk['student']->nisn ?? '-' }}
                                </div>
                            </div>
                            <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-black rounded-md tracking-wide shrink-0">
                                Perlu Dorongan
                            </span>
                        </div>

                        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between gap-2 text-xs">
                            <div>
                                <span class="text-gray-500 text-[11px]">Progres:</span>
                                <strong class="text-red-600 font-black">{{ $atRisk['progress'] }}%</strong>
                                <span class="text-gray-300 mx-1">|</span>
                                <span class="text-gray-500 text-[11px]">Tugas:</span>
                                <strong class="text-gray-800">{{ $atRisk['submissions_count'] }}</strong>
                            </div>

                            @if($atRisk['phone'])
                            @php
                                $studentMsg = rawurlencode("Halo " . ($atRisk['student']->user->name ?? 'Siswa') . ", bapak/ibu guru mengingatkan bahwa progres belajarmu pada materi LMS (" . ($course->course_name ?? $course->name) . ") masih " . $atRisk['progress'] . "%. Silakan segera selesaikan materi dan kumpulkan tugasmu di LMS ya. Semangat!");
                            @endphp
                            <a href="https://wa.me/{{ $atRisk['phone'] }}?text={{ $studentMsg }}" target="_blank" class="px-2 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-lg text-[11px] font-bold flex items-center gap-1 transition" title="Kirim WA ke Siswa/Orang Tua">
                                <i class="fab fa-whatsapp text-emerald-600"></i> Kontak WA
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-4 text-center text-xs text-emerald-700 font-bold bg-emerald-50/50 rounded-xl border border-emerald-100">
                    <i class="fas fa-check-circle mr-1"></i> Seluruh siswa di kelas <strong>{{ $className }}</strong> telah aktif dan menyelesaikan tugas dengan baik!
                </div>
                @endif
            </div>
        </div>
        @endforeach

        @endif
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- ALL STUDENTS PROGRESS TABLE (WITH CLASS FILTER) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                    <i class="fas fa-list-check text-indigo-600"></i> Detail Progres &amp; Keterlibatan Siswa
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Daftar lengkap seluruh siswa terdaftar beserta capaian materi &amp; tugas</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full sm:w-auto">
                {{-- Search Box --}}
                <div class="relative w-full sm:w-auto">
                    <input type="text" id="studentSearchInput" onkeyup="filterTable()" placeholder="Cari nama atau NISN siswa..." class="w-full sm:w-64 pl-9 pr-4 py-2 bg-white border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
                <span class="text-xs font-bold text-gray-500 bg-white px-3 py-2 rounded-xl border border-gray-200 text-center" id="tableFilteredCount">
                    {{ count($studentStats) }} Siswa Terdaftar
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="studentsProgressTable">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-200 text-[11px] font-black uppercase text-gray-600 tracking-wider">
                        <th class="text-center px-4 py-3.5 w-12">No</th>
                        <th class="text-left px-6 py-3.5">Siswa</th>
                        <th class="text-left px-4 py-3.5">Kelas</th>
                        <th class="text-center px-4 py-3.5">Materi Selesai</th>
                        <th class="text-center px-4 py-3.5">Tugas Dikumpul</th>
                        <th class="text-left px-6 py-3.5">Progres Total</th>
                        <th class="text-center px-4 py-3.5">Status</th>
                        <th class="text-center px-4 py-3.5 w-24">Kontak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($studentStats as $index => $st)
                    <tr class="hover:bg-gray-50/80 transition student-row" 
                        data-class="{{ $st['class_name'] }}" 
                        data-name="{{ strtolower($st['student']->user->name ?? '') }}" 
                        data-nisn="{{ $st['student']->nisn ?? '' }}">
                        <td class="px-4 py-4 text-center font-bold text-gray-400 text-xs row-number">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-black text-gray-900 text-sm">{{ $st['student']->user->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">NISN: {{ $st['student']->nisn ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-800 text-xs font-black rounded-lg border border-slate-200">
                                {{ $st['class_name'] }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center font-bold text-gray-800">
                            {{ $st['completed_materials'] }} / {{ $course->materials->count() }}
                        </td>
                        <td class="px-4 py-4 text-center font-bold text-gray-800">
                            {{ $st['submissions_count'] }} / {{ $course->assignments->count() }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-2.5 rounded-full {{ $st['progress'] >= 75 ? 'bg-emerald-500' : ($st['progress'] >= 40 ? 'bg-blue-500' : 'bg-red-500') }}" style="width: {{ $st['progress'] }}%"></div>
                                </div>
                                <span class="text-xs font-black text-gray-700 min-w-[38px] text-right">{{ $st['progress'] }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($st['progress'] >= 75)
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-bold inline-flex items-center gap-1">
                                <i class="fas fa-star text-amber-500"></i> Sangat Aktif
                            </span>
                            @elseif($st['progress'] >= 40)
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg text-xs font-bold inline-flex items-center gap-1">
                                <i class="fas fa-book-reader"></i> Aktif
                            </span>
                            @else
                            <span class="px-2.5 py-1 bg-red-100 text-red-800 rounded-lg text-xs font-bold inline-flex items-center gap-1">
                                <i class="fas fa-exclamation-circle text-red-600"></i> Perlu Dorongan
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($st['phone'])
                            @php
                                $stMsg = rawurlencode("Halo " . ($st['student']->user->name ?? 'Siswa') . ", bapak guru menginfokan progres belajarmu pada kursus LMS (" . ($course->course_name ?? $course->name) . ") saat ini adalah " . $st['progress'] . "%. Tetap semangat dan tuntaskan materinya ya!");
                            @endphp
                            <a href="https://wa.me/{{ $st['phone'] }}?text={{ $stMsg }}" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 inline-flex items-center justify-center text-sm transition" title="Kirim WA ke Siswa">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            @else
                            <span class="text-gray-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">Belum ada siswa terdaftar di course ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- JAVASCRIPT: FILTER & CLIPBOARD HANDLERS --}}
{{-- ═══════════════════════════════════════════════ --}}
<script>
let currentSelectedClass = 'all';

function filterByClass(className) {
    currentSelectedClass = className;

    // 1. Update filter tab styling
    document.querySelectorAll('.class-tab-btn').forEach(btn => {
        btn.classList.remove('bg-slate-900', 'text-white', 'border-slate-900');
        btn.classList.add('bg-gray-100', 'text-gray-800', 'border-gray-200');
    });

    const activeTabId = (className === 'all') 
        ? 'btn-tab-all' 
        : 'btn-tab-' + slugify(className);
    const activeBtn = document.getElementById(activeTabId);
    if (activeBtn) {
        activeBtn.classList.remove('bg-gray-100', 'text-gray-800', 'border-gray-200');
        activeBtn.classList.add('bg-slate-900', 'text-white', 'border-slate-900');
    }

    // 2. Update filter label
    const labelEl = document.getElementById('currentFilterLabel');
    if (labelEl) {
        labelEl.innerText = (className === 'all') ? 'Semua Kelas' : 'Kelas ' + className;
    }

    // 3. Filter Class Group Cards (Siswa Perlu Perhatian)
    const classCards = document.querySelectorAll('.class-group-card');
    classCards.forEach(card => {
        const cardClass = card.getAttribute('data-class-name');
        if (className === 'all' || cardClass === className) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });

    // 4. Filter Table Rows
    filterTable();
}

function filterTable() {
    const searchInput = document.getElementById('studentSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('#studentsProgressTable tbody tr.student-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowClass = row.getAttribute('data-class');
        const rowName = row.getAttribute('data-name') || '';
        const rowNisn = row.getAttribute('data-nisn') || '';

        const matchClass = (currentSelectedClass === 'all' || rowClass === currentSelectedClass);
        const matchQuery = (query === '' || rowName.includes(query) || rowNisn.includes(query));

        if (matchClass && matchQuery) {
            row.style.display = '';
            visibleCount++;
            // Re-number
            const noCell = row.querySelector('.row-number');
            if (noCell) {
                noCell.innerText = visibleCount;
            }
        } else {
            row.style.display = 'none';
        }
    });

    const countEl = document.getElementById('tableFilteredCount');
    if (countEl) {
        countEl.innerText = visibleCount + ' Siswa Ditampilkan';
    }
}

function copyWaText(slug, className) {
    const el = document.getElementById('waText-' + slug);
    if (!el) return;

    const textToCopy = el.value;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            showToast('Teks Berhasil Disalin!', 'Laporan kelas ' + className + ' siap dipaste ke WhatsApp.');
        }).catch(err => {
            fallbackCopyText(textToCopy, className);
        });
    } else {
        fallbackCopyText(textToCopy, className);
    }
}

function fallbackCopyText(text, className) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        showToast('Teks Berhasil Disalin!', 'Laporan kelas ' + className + ' siap dipaste ke WhatsApp.');
    } catch (err) {
        alert('Gagal menyalin otomatis. Silakan salin manual.');
    }
    document.body.removeChild(textArea);
}

function showToast(title, message) {
    const toast = document.getElementById('copyToast');
    const titleEl = document.getElementById('toastTitle');
    const msgEl = document.getElementById('toastMessage');

    if (!toast) return;

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    toast.classList.remove('translate-y-[-100px]', 'opacity-0', 'pointer-events-none');
    toast.classList.add('translate-y-0', 'opacity-100');

    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-[-100px]', 'opacity-0', 'pointer-events-none');
    }, 3500);
}

function slugify(text) {
    return text.toString().toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
}
</script>
@endsection
