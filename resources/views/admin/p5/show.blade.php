@extends('layouts.admin')

@section('title', 'Detail Projek P5 - ' . $project->title)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-700 rounded-2xl p-6 text-white relative overflow-hidden shadow-sm">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="relative">
            <div class="flex items-center text-sm text-white/70 mb-2 gap-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.p5.index') }}" class="hover:text-white transition">Projek P5</a>
                <span>/</span>
                <span class="text-white font-semibold">Detail Projek</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-bold uppercase tracking-wider mb-2 inline-block">
                        Tema: {{ $project->theme }}
                    </span>
                    <h1 class="text-2xl font-bold flex items-center gap-2.5">
                        <i class="fas fa-shapes"></i> {{ $project->title }}
                    </h1>
                    <p class="text-xs text-purple-100 mt-1 font-mono">
                        Kelas: <b>{{ $project->classroom->name }}</b> &bull; Unit: <b>{{ $project->school->name }}</b> &bull; TP: <b>{{ $project->academicYear->name }}</b>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.p5.assess', $project) }}" class="px-6 py-3 bg-white text-indigo-700 hover:bg-indigo-50 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-md active:scale-95">
                        <i class="fas fa-pen-fancy"></i> Input / Edit Nilai Siswa
                    </a>
                    <a href="{{ route('admin.p5.index') }}" class="px-5 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-semibold transition flex items-center gap-2 text-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl shadow-sm text-sm font-semibold flex items-center gap-2">
        <i class="fas fa-check-circle text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Kolom Kiri: Target Dimensi Projek --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fas fa-bullseye text-indigo-600"></i> Target Dimensi & Capaian ({{ count($project->targets) }})
                </h3>

                <div class="space-y-3">
                    @foreach($project->targets as $idx => $t)
                    <div class="p-3.5 bg-indigo-50/60 rounded-xl border border-indigo-100 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold">{{ $idx + 1 }}</span>
                            <span class="text-xs font-bold text-indigo-950">{{ $t->dimension }}</span>
                        </div>
                        <p class="text-xs text-gray-600 pl-7 leading-relaxed">
                            {{ $t->sub_element }}
                        </p>
                    </div>
                    @endforeach
                </div>

                @if($project->description)
                <div class="pt-3 border-t border-gray-100">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Deskripsi Projek:</p>
                    <p class="text-xs text-gray-600 leading-relaxed italic bg-gray-50 p-3 rounded-xl">
                        "{{ $project->description }}"
                    </p>
                </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Keterangan Skala Nilai P5:</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                        <span class="font-bold text-gray-700">MB</span>
                        <span class="text-gray-500">Mulai Berkembang</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                        <span class="font-bold text-gray-700">SB</span>
                        <span class="text-gray-500">Sedang Berkembang</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                        <span class="font-bold text-gray-700">BSH</span>
                        <span class="text-gray-500">Berkembang Sesuai Harapan</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                        <span class="font-bold text-gray-700">SAB</span>
                        <span class="text-gray-500">Sangat Berkembang</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Daftar Siswa & Progress Nilai --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-users text-indigo-600"></i> Rekap Penilaian Siswa Kelas {{ $project->classroom->name }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Total {{ count($students) }} Siswa Terdaftar di Rombel ini</p>
                    </div>
                    <a href="{{ route('admin.p5.assess', $project) }}" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition">
                        <i class="fas fa-edit"></i> Buka Matriks Nilai
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-600 font-bold border-b border-gray-100">
                            <tr>
                                <th class="p-4 w-12 text-center">No</th>
                                <th class="p-4">Nama Siswa</th>
                                <th class="p-4 text-center">NISN</th>
                                <th class="p-4 text-center">Status Nilai</th>
                                <th class="p-4 text-center">Catatan Proses</th>
                                <th class="p-4 text-right">Aksi Rapor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($students as $idx => $s)
                            @php
                                $studentScores = $assessments->get($s->id, collect());
                                $isComplete = $studentScores->count() >= count($project->targets) && count($project->targets) > 0;
                                $note = $notes->get($s->id);
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="p-4 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                                <td class="p-4">
                                    <p class="font-bold text-gray-900">{{ $s->full_name }}</p>
                                    <p class="text-[10px] text-gray-400 font-mono">{{ $s->nis ?? '-' }}</p>
                                </td>
                                <td class="p-4 text-center font-mono text-gray-600">{{ $s->nisn ?: '-' }}</td>
                                <td class="p-4 text-center">
                                    @if($isComplete)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-[10px] font-bold">
                                        <i class="fas fa-check-circle text-emerald-600"></i> Lengkap ({{ $studentScores->count() }}/{{ count($project->targets) }})
                                    </span>
                                    @elseif($studentScores->count() > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg text-[10px] font-bold">
                                        <i class="fas fa-clock text-amber-600"></i> Sebagian ({{ $studentScores->count() }}/{{ count($project->targets) }})
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-500 rounded-lg text-[10px] font-semibold">
                                        Belum Dinilai
                                    </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    @if($note && !empty($note->notes))
                                    <span class="text-emerald-600 font-bold" title="{{ $note->notes }}"><i class="fas fa-comment-dots"></i> Ada Catatan</span>
                                    @else
                                    <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('admin.p5.raport.print', [$project, $s]) }}" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 transition shadow-xs">
                                        <i class="fas fa-file-pdf text-rose-500"></i> Cetak Rapor P5
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400 italic">
                                    Tidak ada siswa yang terdaftar di rombel kelas ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
