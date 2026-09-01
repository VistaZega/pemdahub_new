@extends('layouts.admin')

@section('title', 'Dashboard Kepala Sekolah - Ringkasan Komprehensif')

@section('content')
<div class="space-y-8">

    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-slate-800 via-slate-700 to-indigo-800 text-white rounded-3xl p-6 md:p-8 shadow-xl border-2 border-white/10">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight">📊 Dashboard Kepala Sekolah</h1>
                <p class="text-white/70 text-sm font-bold mt-1">
                    Ringkasan Nilai, LMS, Kehadiran & Status Raport — {{ $activeAy?->year ?? 'TP Berjalan' }}
                </p>
            </div>
            @if($schoolIds->count() > 1)
            <form method="GET" class="flex items-center gap-2">
                <select name="school_id" onchange="this.form.submit()" class="bg-white/10 border border-white/20 text-white rounded-xl px-4 py-2 text-sm font-bold">
                    @foreach($schoolIds as $sid)
                    @php $sch = \App\Models\School::find($sid); @endphp
                    <option value="{{ $sid }}" {{ $sid == $selectedSchoolId ? 'selected' : '' }} class="text-black">{{ $sch->name ?? 'Sekolah' }}</option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>
    </div>

    {{-- 1. REKAP NILAI --}}
    @php $ns = collect($nilaiStats)->firstWhere('school.id', $selectedSchoolId); @endphp
    <div class="bg-white rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-6 py-4">
            <h2 class="font-black text-lg flex items-center gap-2"><i class="fas fa-graduation-cap"></i> Rekap Nilai Akademik</h2>
        </div>
        <div class="p-6">
            @if($ns && count($ns['classes']) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-xs tracking-wider">
                            <th class="p-3 text-left">Kelas</th>
                            <th class="p-3 text-center">Rata-rata</th>
                            <th class="p-3 text-center">Lulus</th>
                            <th class="p-3 text-center">Total</th>
                            <th class="p-3 text-center">Ketuntasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ns['classes'] as $cd)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-3 font-bold">{{ $cd['class']->class_name }}</td>
                            <td class="p-3 text-center font-black text-lg">{{ $cd['avg'] ?? '-' }}</td>
                            <td class="p-3 text-center">{{ $cd['passed'] }}/{{ $cd['total'] }}</td>
                            <td class="p-3 text-center font-bold">{{ $cd['total'] }} Siswa</td>
                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $cd['pass_rate'] >= 90 ? 'bg-emerald-100 text-emerald-800' : ($cd['pass_rate'] >= 75 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $cd['pass_rate'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 p-4 bg-indigo-50 rounded-2xl border border-indigo-200">
                <span class="font-bold text-sm">Rata-rata Keseluruhan: <b class="text-xl">{{ $ns['overall_avg'] ? number_format($ns['overall_avg'], 2) : '-' }}</b></span>
            </div>
            @else
            <p class="text-slate-500 text-sm">Belum ada data nilai untuk semester ini.</p>
            @endif
        </div>
    </div>

    {{-- 2. AKTIVITAS LMS GURU --}}
    <div class="bg-white rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-4">
            <h2 class="font-black text-lg flex items-center gap-2"><i class="fas fa-chalkboard-teacher"></i> Aktivitas LMS Guru</h2>
        </div>
        <div class="p-6">
            @php $filteredTeachers = $lmsActivity->filter(fn($t) => $t->teacher->school_id == $selectedSchoolId); @endphp
            @if($filteredTeachers->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-xs tracking-wider">
                            <th class="p-3 text-left">Guru</th>
                            <th class="p-3 text-center">Kursus</th>
                            <th class="p-3 text-center">Materi</th>
                            <th class="p-3 text-center">Tugas</th>
                            <th class="p-3 text-center">Quiz</th>
                            <th class="p-3 text-center">Terakhir Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filteredTeachers as $ta)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-3 font-bold">{{ $ta->teacher->full_name }}</td>
                            <td class="p-3 text-center">{{ $ta->courses }}</td>
                            <td class="p-3 text-center">{{ $ta->materials }}</td>
                            <td class="p-3 text-center">{{ $ta->assignments }}</td>
                            <td class="p-3 text-center">{{ $ta->quizzes }}</td>
                            <td class="p-3 text-center text-xs">
                                @if($ta->last_activity)
                                <span class="text-slate-500">{{ \Carbon\Carbon::parse($ta->last_activity)->diffForHumans() }}</span>
                                @else
                                <span class="text-rose-500 font-bold">Belum Aktif</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-slate-500 text-sm">Belum ada aktivitas LMS di sekolah ini.</p>
            @endif
        </div>
    </div>

    {{-- 3. REKAP KEHADIRAN HARI INI --}}
    <div class="bg-white rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white px-6 py-4">
            <h2 class="font-black text-lg flex items-center gap-2"><i class="fas fa-fingerprint"></i> Rekap Kehadiran Hari Ini</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($attendanceSummary as $as)
                @if($as['school']->id == $selectedSchoolId || $selectedSchoolId == $schoolIds->first())
                <div class="p-5 rounded-2xl border-2 border-slate-200">
                    <h3 class="font-black text-sm mb-3 flex items-center gap-2"><i class="fas fa-school text-indigo-600"></i> {{ $as['school']->name }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                            <p class="text-[10px] font-bold uppercase text-emerald-700">👨‍🎓 Siswa</p>
                            <p class="text-2xl font-black text-emerald-800 mt-1">{{ $as['siswa_hadir'] }}<span class="text-sm font-bold text-emerald-500">/{{ $as['siswa_total'] }}</span></p>
                            <p class="text-xs font-bold text-emerald-600 mt-1">{{ $as['siswa_pct'] }}% Hadir</p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                            <p class="text-[10px] font-bold uppercase text-blue-700">👨‍🏫 Guru</p>
                            <p class="text-2xl font-black text-blue-800 mt-1">{{ $as['guru_hadir'] }}<span class="text-sm font-bold text-blue-500">/{{ $as['guru_total'] }}</span></p>
                            <p class="text-xs font-bold text-blue-600 mt-1">{{ $as['guru_pct'] }}% Hadir</p>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- 4. STATUS RAPORT --}}
    <div class="bg-white rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-amber-600 to-orange-600 text-white px-6 py-4">
            <h2 class="font-black text-lg flex items-center gap-2"><i class="fas fa-file-alt"></i> Status Raport per Kelas</h2>
        </div>
        <div class="p-6">
            @php $rs = collect($raportStats)->firstWhere('school.id', $selectedSchoolId); @endphp
            @if($rs && count($rs['classes']) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-xs tracking-wider">
                            <th class="p-3 text-left">Kelas</th>
                            <th class="p-3 text-center">Total</th>
                            <th class="p-3 text-center">Draft</th>
                            <th class="p-3 text-center">Finalisasi</th>
                            <th class="p-3 text-center">Published</th>
                            <th class="p-3 text-center">Progres</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rs['classes'] as $rc)
                        @php $progress = $rc['total'] > 0 ? round(($rc['published'] / $rc['total']) * 100, 1) : 0; @endphp
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-3 font-bold">{{ $rc['class']->class_name }}</td>
                            <td class="p-3 text-center">{{ $rc['total'] }}</td>
                            <td class="p-3 text-center"><span class="text-amber-600 font-bold">{{ $rc['draft'] }}</span></td>
                            <td class="p-3 text-center"><span class="text-blue-600 font-bold">{{ $rc['finalized'] }}</span></td>
                            <td class="p-3 text-center"><span class="text-emerald-600 font-bold">{{ $rc['published'] }}</span></td>
                            <td class="p-3 text-center">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-slate-200 rounded-full h-2">
                                        <div class="h-full rounded-full {{ $progress >= 100 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold">{{ $progress }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-slate-500 text-sm">Belum ada raport untuk semester ini.</p>
            @endif
        </div>
    </div>

    <p class="text-center text-xs text-slate-400 font-bold py-4">🤖 PembdaHUB — Dashboard Kepsek diperbarui otomatis setiap halaman dimuat</p>
</div>
@endsection