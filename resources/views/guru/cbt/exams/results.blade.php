@extends('layouts.guru')
@section('title', 'Hasil: ' . $exam->exam_title)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" onload="setTimeout(() => renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true}, {left: '\\(', right: '\\)', display: false}, {left: '$', right: '$', display: false}]}), 200)"></script>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('guru.cbt.exams.show', $exam) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Hasil Ujian</h1>
                    <p class="text-emerald-50 mt-1 text-base">{{ $exam->exam_title }} &bull; {{ $exam->subject->subject_name ?? '-' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($hasEssayQuestions)
                <a href="{{ route('guru.cbt.exams.grade-essays', $exam) }}" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-xl font-black text-sm shadow-md transition flex items-center gap-2">
                    <i class="fas fa-pen-fancy"></i>Koreksi Esai
                    @if(($pendingEssaysCount ?? 0) > 0)
                        <span class="px-2 py-0.5 bg-red-600 text-white text-xs font-black rounded-full shadow-sm">{{ $pendingEssaysCount }} Belum Dinilai</span>
                    @endif
                </a>
                @endif
                @if($exam->status === 'completed')
                <form action="{{ route('guru.cbt.exams.sync-grades', $exam) }}" method="POST">@csrf
                    <button class="px-5 py-2.5 bg-white/15 rounded-xl font-medium text-base border border-gray-200 hover:bg-white/25 transition flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i>Sinkron Nilai
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-5">
        @php $rCards = [
            ['Peserta', $statistics['total_participants'] ?? 0, 'fa-users', 'blue'],
            ['Selesai', $statistics['completed_count'] ?? 0, 'fa-check-double', 'emerald'],
            ['Rata-rata', number_format($statistics['average_score'] ?? 0, 1), 'fa-chart-line', 'amber'],
            ['Lulus', $statistics['passed_count'] ?? 0, 'fa-trophy', 'green'],
            ['Tidak Lulus', $statistics['failed_count'] ?? 0, 'fa-times-circle', 'red'],
        ]; @endphp
        @foreach($rCards as [$label, $val, $icon, $color])
        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center hover:shadow-lg transition-all duration-300">
            <div class="w-11 h-11 rounded-xl bg-{{ $color }}-100 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                <i class="fas {{ $icon }} text-{{ $color }}-600"></i>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ $val }}</div>
            <div class="text-base text-gray-700 mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    {{-- Pass Rate Bar --}}
    @php $pr = $statistics['pass_rate'] ?? 0; @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-3">
            <span class="text-base font-bold text-gray-700">Tingkat Kelulusan</span>
            <span class="text-base font-bold {{ $pr >= 75 ? 'text-emerald-600' : ($pr >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($pr, 1) }}%</span>
        </div>
        <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full {{ $pr >= 75 ? 'bg-gradient-to-r from-emerald-400 to-green-500' : ($pr >= 50 ? 'bg-gradient-to-r from-amber-400 to-orange-500' : 'bg-gradient-to-r from-red-400 to-rose-500') }}" style="width: {{ $pr }}%"></div>
        </div>
    </div>

    {{-- Results Table --}}
    <div x-data="{ activeTab: 'results' }" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <i class="fas text-emerald-600" :class="activeTab === 'results' ? 'fa-list-ol' : 'fa-chart-pie'"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-900" x-text="activeTab === 'results' ? 'Daftar Hasil' : 'Analisis Butir Soal'"></h2>
            </div>
            <div class="flex bg-gray-100 p-1 rounded-xl self-start sm:self-auto">
                <button @click="activeTab = 'results'" :class="activeTab === 'results' ? 'bg-white text-gray-950 font-bold shadow-sm' : 'text-gray-700 hover:text-gray-950'" class="px-4 py-2 rounded-lg text-base transition-all uppercase tracking-wider font-semibold">Daftar Hasil</button>
                <button @click="activeTab = 'item_analysis'" :class="activeTab === 'item_analysis' ? 'bg-white text-gray-950 font-bold shadow-sm' : 'text-gray-700 hover:text-gray-950'" class="px-4 py-2 rounded-lg text-base transition-all uppercase tracking-wider font-semibold">Analisis Butir Soal</button>
            </div>
        </div>

        {{-- Results list tab --}}
        <div x-show="activeTab === 'results'" class="overflow-x-auto">
            <table class="w-full text-base">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3.5 text-left text-base font-semibold text-gray-700 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3.5 text-left text-base font-semibold text-gray-700 uppercase tracking-wider">Siswa</th>
                        <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Benar</th>
                        <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Salah</th>
                        <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Kosong</th>
                        <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Nilai</th>
                        <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Predikat</th>
                        <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Durasi</th>
                        <th class="px-6 py-3.5 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($results as $rank => $result)
                    @php
                        $score = $result->total_score ?? 0;
                        $predikat = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 70 ? 'C' : ($score >= 60 ? 'D' : 'E')));
                        $pColor = match($predikat) {
                            'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'B' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'C' => 'bg-amber-100 text-amber-800 border-amber-200',
                            default => 'bg-red-50 text-red-700 border-red-200',
                        };
                    @endphp
                    <tr class="hover:bg-gradient-to-r hover:from-emerald-50/30 hover:to-transparent transition-colors">
                        <td class="px-6 py-3.5 font-bold text-gray-800">{{ $rank + 1 }}</td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-base">
                                    {{ strtoupper(substr($result->session->student->full_name ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $result->session->student->full_name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-center text-gray-800">{{ $result->session->student->classroom?->class_name ?? '-' }}</td>
                        <td class="px-6 py-3.5 text-center font-bold text-emerald-600">{{ $result->correct_answers ?? 0 }}</td>
                        <td class="px-6 py-3.5 text-center font-bold text-red-500">{{ $result->wrong_answers ?? 0 }}</td>
                        <td class="px-6 py-3.5 text-center text-gray-800">{{ $result->unanswered ?? 0 }}</td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="text-lg font-bold {{ $score >= ($exam->passing_score ?? 70) ? 'text-emerald-600' : 'text-red-500' }}">{{ number_format($score, 1) }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-center"><span class="px-2.5 py-0.5 rounded-lg text-base font-bold border {{ $pColor }}">{{ $predikat }}</span></td>
                        <td class="px-6 py-3.5 text-center text-gray-700 text-base">
                            @if($result->started_at && $result->completed_at)
                            {{ $result->started_at->diffInMinutes($result->completed_at) }} mnt
                            @else - @endif
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            <div class="flex flex-col items-center gap-1">
                                @if($score >= ($exam->passing_score ?? 70))
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">LULUS</span>
                                @else
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200">TIDAK LULUS</span>
                                @endif

                                @if($result->session && $result->session->answers()->needsGrading()->exists())
                                    <a href="{{ route('guru.cbt.exams.grade-essays', $exam) }}" class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-black rounded-md bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition" title="Ada esai siswa yang belum dinilai">
                                        <i class="fas fa-pen-fancy text-amber-600 text-[10px]"></i> Koreksi Esai
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-16 text-gray-800"><i class="fas fa-inbox text-4xl mb-3 block"></i>Belum ada hasil</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Item Analysis tab --}}
        <div x-show="activeTab === 'item_analysis'" x-cloak class="p-6 space-y-6">
            @forelse($itemAnalysis as $idx => $item)
            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 hover:bg-white hover:shadow-md transition duration-300">
                <div class="flex items-start justify-between gap-4 flex-wrap border-b border-gray-200 pb-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-800 text-white flex items-center justify-center font-bold text-base">{{ $idx + 1 }}</div>
                        <div>
                            <span class="px-2 py-0.5 rounded text-base font-bold uppercase tracking-wide bg-blue-100 text-blue-700 border border-blue-200">
                                {{ strtoupper(str_replace('_', ' ', $item['question_type'])) }}
                            </span>
                            <span class="text-base text-gray-800 ml-1 font-medium">{{ $item['total_answers'] }} jawaban terkumpul</span>
                        </div>
                    </div>
                    
                    {{-- Indices --}}
                    <div class="flex items-center gap-3 flex-wrap">
                        {{-- Difficulty --}}
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl text-base font-bold border {{ $item['difficulty_class'] }}">
                            <span class="text-gray-800 font-medium">Kesukaran:</span>
                            <span>{{ $item['difficulty_index'] }} ({{ $item['difficulty_label'] }})</span>
                        </div>
                        {{-- Discrimination --}}
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl text-base font-bold border {{ $item['discrimination_class'] }}">
                            <span class="text-gray-800 font-medium">Daya Pembeda:</span>
                            <span>{{ $item['discrimination_index'] }} ({{ $item['discrimination_label'] }})</span>
                        </div>
                    </div>
                </div>

                {{-- Question Text --}}
                <div class="prose prose-slate max-w-none text-[15px] leading-relaxed text-gray-700 mb-4">
                    {!! $item['question_text'] !!}
                </div>

                {{-- Distractor progress bars --}}
                @if(!empty($item['distractors']))
                <div class="mt-4 space-y-2.5 max-w-2xl">
                    <div class="text-base font-bold uppercase tracking-wider text-gray-800 mb-1.5">Sebaran Jawaban Siswa</div>
                    @foreach($item['distractors'] as $dist)
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-base font-medium">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded flex items-center justify-center font-bold text-base {{ $dist['is_correct'] ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                                    {{ $dist['label'] }}
                                </span>
                                <span class="{{ $dist['is_correct'] ? 'text-emerald-700 font-bold' : 'text-gray-800' }}">{{ $dist['text'] }}</span>
                                @if($dist['is_correct'])
                                <span class="text-[9px] px-1 bg-emerald-100 text-emerald-800 rounded font-black uppercase ml-1">Kunci</span>
                                @endif
                            </div>
                            <span class="text-gray-700 font-mono">{{ $dist['count'] }} siswa ({{ $dist['percentage'] }}%)</span>
                        </div>
                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $dist['is_correct'] ? 'bg-emerald-500' : 'bg-gray-400' }}" style="width: {{ $dist['percentage'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-16 text-gray-800"><i class="fas fa-inbox text-4xl mb-3 block"></i>Belum ada data analisis butir soal</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
