@extends('layouts.admin')

@section('title', 'Penilaian Projek P5 - ' . $project->title)

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
                <a href="{{ route('admin.p5.show', $project) }}" class="hover:text-white transition">{{ $project->title }}</a>
                <span>/</span>
                <span class="text-white font-semibold">Input Penilaian</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-bold uppercase tracking-wider mb-2 inline-block">
                        Tema: {{ $project->theme }}
                    </span>
                    <h1 class="text-2xl font-bold flex items-center gap-2.5">
                        <i class="fas fa-pen-fancy"></i> Matriks Penilaian Capaian Projek P5
                    </h1>
                    <p class="text-xs text-purple-100 mt-1 font-mono">
                        Kelas: <b>{{ $project->classroom->name }}</b> &bull; Total Siswa: <b>{{ count($students) }}</b>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.p5.show', $project) }}" class="px-5 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-semibold transition flex items-center gap-2 text-sm shadow-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Keterangan Skala Nilai --}}
    <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4.5 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-indigo-950 font-bold">
            <i class="fas fa-info-circle text-indigo-600 text-base"></i>
            <span>Panduan Skala Capaian P5:</span>
        </div>
        <div class="flex flex-wrap items-center gap-4 text-gray-700 font-medium">
            <span class="inline-flex items-center gap-1.5"><b class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded font-mono">MB</b> Mulai Berkembang</span>
            <span class="inline-flex items-center gap-1.5"><b class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded font-mono">SB</b> Sedang Berkembang</span>
            <span class="inline-flex items-center gap-1.5"><b class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-mono">BSH</b> Berkembang Sesuai Harapan</span>
            <span class="inline-flex items-center gap-1.5"><b class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded font-mono">SAB</b> Sangat Berkembang</span>
        </div>
    </div>

    {{-- Form Matriks Penilaian --}}
    <form action="{{ route('admin.p5.assess.store', $project) }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200">
                        <tr>
                            <th class="p-4 w-12 text-center border-r border-gray-200">No</th>
                            <th class="p-4 w-56 border-r border-gray-200">Nama Siswa</th>
                            @foreach($project->targets as $idx => $target)
                            <th class="p-4 min-w-[200px] border-r border-gray-200 text-center bg-indigo-50/50">
                                <p class="text-[11px] text-indigo-900 font-bold">Target {{ $idx + 1 }}: {{ $target->dimension }}</p>
                                <p class="text-[10px] text-gray-500 font-normal mt-0.5 line-clamp-2" title="{{ $target->sub_element }}">
                                    {{ $target->sub_element }}
                                </p>
                            </th>
                            @endforeach
                            <th class="p-4 min-w-[220px]">Catatan Proses Projek</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($students as $idx => $s)
                        @php
                            $studentScores = $existingAssessments->get($s->id, collect());
                            $studentNote = $existingNotes->get($s->id);
                        @endphp
                        <tr class="hover:bg-gray-50/90 transition">
                            <td class="p-4 text-center font-bold text-gray-400 border-r border-gray-100">{{ $idx + 1 }}</td>
                            <td class="p-4 border-r border-gray-100">
                                <p class="font-bold text-gray-900 leading-snug">{{ $s->full_name }}</p>
                                <p class="text-[10px] text-gray-400 font-mono">{{ $s->nis ?? '-' }}</p>
                            </td>

                            {{-- Target Score Radio Buttons --}}
                            @foreach($project->targets as $target)
                            @php
                                $currentVal = $studentScores->get($target->id)?->score;
                            @endphp
                            <td class="p-3 border-r border-gray-100 text-center bg-gray-50/30">
                                <div class="grid grid-cols-4 gap-1 p-1 bg-white rounded-xl border border-gray-200 shadow-2xs">
                                    @foreach(['MB', 'SB', 'BSH', 'SAB'] as $opt)
                                    <label class="cursor-pointer">
                                        <input type="radio" 
                                               name="scores[{{ $s->id }}][{{ $target->id }}]" 
                                               value="{{ $opt }}" 
                                               class="sr-only peer" 
                                               {{ $currentVal === $opt ? 'checked' : '' }}>
                                        <span class="block py-1 text-[11px] font-bold rounded-lg transition text-center select-none
                                            text-gray-500 hover:bg-gray-100
                                            peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:shadow-sm">
                                            {{ $opt }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </td>
                            @endforeach

                            {{-- Catatan Proses --}}
                            <td class="p-3">
                                <textarea name="notes[{{ $s->id }}]" 
                                          rows="2" 
                                          placeholder="Catatan keaktifan, kepemimpinan, atau kerja sama..." 
                                          class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 leading-relaxed">{{ old("notes.{$s->id}", $studentNote->notes ?? '') }}</textarea>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($project->targets) + 3 }}" class="p-8 text-center text-gray-400 italic">
                                Belum ada siswa di rombel kelas ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Sticky Footer Simpan --}}
            <div class="p-5 bg-gray-50 border-t border-gray-200 flex items-center justify-between gap-4">
                <a href="{{ route('admin.p5.show', $project) }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-bold text-xs transition">
                    Batal & Kembali
                </a>
                <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl font-bold text-sm shadow-md shadow-indigo-500/20 flex items-center gap-2.5 transition active:scale-[0.98]">
                    <i class="fas fa-save"></i>
                    <span>Simpan Seluruh Penilaian P5</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
