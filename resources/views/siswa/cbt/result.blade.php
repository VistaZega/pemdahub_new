@extends('layouts.siswa')
@section('title', 'Hasil Ujian - ' . $exam->exam_title)
@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 via-orange-500 to-rose-600 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex items-center gap-5">
            <a href="{{ route('siswa.cbt.index') }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-100">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Hasil Ujian</h1>
                <p class="text-amber-200 mt-1 text-sm">{{ $exam->exam_title }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600 text-sm"></i></div>
        <p class="text-emerald-700 text-sm font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(($pendingEssaysCount ?? 0) > 0)
    <div class="flex items-start gap-3.5 p-5 bg-amber-50 border border-amber-200 rounded-2xl">
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-600">
            <i class="fas fa-hourglass-half text-lg"></i>
        </div>
        <div>
            <h3 class="font-bold text-amber-900 text-sm">Menunggu Penilaian Esai ({{ $pendingEssaysCount }} Soal)</h3>
            <p class="text-amber-700 text-xs mt-1">
                Ujian ini memiliki <strong>{{ $pendingEssaysCount }} soal esai</strong> yang telah Anda isi dan sedang menunggu koreksi manual oleh Guru/Admin. Skor dan status kelulusan di bawah ini masih bersifat sementara dan akan diperbarui otomatis setelah penilaian selesai.
            </p>
        </div>
    </div>
    @endif

    @if($latestResult)
    {{-- Main Score Card --}}
    @php
        $isPassed = $latestResult->final_score >= $exam->passing_score;
        $hasPending = ($pendingEssaysCount ?? 0) > 0;
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        <div class="w-32 h-32 rounded-2xl mx-auto flex items-center justify-center mb-4 {{ $isPassed ? 'bg-gradient-to-br from-emerald-100 to-green-50' : ($hasPending ? 'bg-gradient-to-br from-amber-100 to-orange-50' : 'bg-gradient-to-br from-red-100 to-rose-50') }}">
            <span class="text-5xl font-bold {{ $isPassed ? 'text-emerald-600' : ($hasPending ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($latestResult->final_score, 1) }}</span>
        </div>
        <div class="flex items-center justify-center gap-2 mb-3">
            @if($hasPending)
            <span class="px-4 py-1.5 rounded-xl text-sm font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
                <i class="fas fa-clock"></i>MENUNGGU PENILAIAN ESAI
            </span>
            @else
            <span class="px-4 py-1.5 rounded-xl text-sm font-bold border {{ $isPassed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                {{ $isPassed ? 'LULUS' : 'TIDAK LULUS' }}
            </span>
            @endif
            @if($latestResult->predicate)
            <span class="px-3 py-1.5 rounded-xl text-sm font-bold bg-blue-50 text-blue-700 border border-blue-200">{{ $hasPending ? 'Predikat Sementara' : 'Predikat' }}: {{ $latestResult->predicate }}</span>
            @endif
        </div>
        <p class="text-gray-500 text-sm">KKM: {{ $exam->passing_score }} &bull; {{ $exam->subject->subject_name ?? $exam->subject->name ?? '' }}</p>
    </div>

    {{-- Stats Cards --}}
    @php
        $sCards = [
            ['Jawaban Benar', $latestResult->correct_answers, 'fa-check-circle', 'emerald'],
            ['Jawaban Salah', $latestResult->wrong_answers, 'fa-times-circle', 'red'],
            ['Tidak Dijawab', $latestResult->unanswered, 'fa-minus-circle', 'gray'],
        ];
        if ($hasPending) {
            $sCards[] = ['Menunggu Koreksi', $pendingEssaysCount, 'fa-clock', 'amber'];
        }
        $sCards[] = ['Peringkat', $latestResult->rank ?? '-', 'fa-trophy', 'purple'];
    @endphp
    <div class="grid grid-cols-2 {{ $hasPending ? 'md:grid-cols-5' : 'md:grid-cols-4' }} gap-4">
        @foreach($sCards as [$label, $val, $icon, $color])
        <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-lg transition-all duration-300">
            <div class="w-10 h-10 rounded-xl bg-{{ $color }}-100 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                <i class="fas {{ $icon }} text-{{ $color }}-600 text-sm"></i>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ $val }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    {{-- Score Progress --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center"><i class="fas fa-chart-bar text-blue-600"></i></div>
            <h2 class="text-lg font-bold text-gray-900">Detail Skor</h2>
        </div>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-1.5"><span class="text-gray-600 font-medium">Jawaban Benar</span><span class="font-bold text-gray-900">{{ $latestResult->correct_answers }}/{{ $latestResult->total_questions }}</span></div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-400 to-blue-500 rounded-full" style="width: {{ $latestResult->total_questions > 0 ? ($latestResult->correct_answers / $latestResult->total_questions * 100) : 0 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1.5"><span class="text-gray-600 font-medium">Skor Akhir {{ $hasPending ? '(Sementara)' : '' }}</span><span class="font-bold text-gray-900">{{ number_format($latestResult->final_score, 1) }}</span></div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $isPassed ? 'bg-gradient-to-r from-emerald-400 to-green-500' : ($hasPending ? 'bg-gradient-to-r from-amber-400 to-orange-500' : 'bg-gradient-to-r from-red-400 to-rose-500') }}" style="width: {{ min(100, $latestResult->final_score) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-wrap gap-3 justify-center">
        @if($exam->allow_review && $latestResult->session_id)
        <a href="{{ route('siswa.cbt.review', $latestResult->session_id) }}" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-xl hover:shadow-lg transition font-medium text-sm flex items-center gap-2">
            <i class="fas fa-search"></i>Review Jawaban
        </a>
        @endif
        <a href="{{ route('siswa.cbt.history') }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition font-medium text-sm flex items-center gap-2">
            <i class="fas fa-history"></i>Riwayat
        </a>
        <a href="{{ route('siswa.cbt.index') }}" class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl hover:shadow-lg transition font-medium text-sm flex items-center gap-2">
            <i class="fas fa-list"></i>Kembali
        </a>
    </div>

    {{-- Previous Attempts --}}
    @if($results->count() > 1)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center"><i class="fas fa-history text-amber-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Riwayat Percobaan</h2>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100"><tr class="bg-gradient-to-r from-gray-50 to-gray-100/80">
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Percobaan</th>
                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Nilai</th>
                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Benar</th>
                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($results as $i => $r)
                    <tr class="hover:bg-amber-50/30 transition-colors">
                        <td class="px-6 py-3.5 font-medium text-gray-900">Percobaan {{ $results->count() - $i }}</td>
                        <td class="px-6 py-3.5 text-center text-lg font-bold {{ $r->final_score >= $exam->passing_score ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($r->final_score, 1) }}</td>
                        <td class="px-6 py-3.5 text-center text-gray-600">{{ $r->correct_answers }}/{{ $r->correct_answers + $r->wrong_answers + $r->unanswered }}</td>
                        <td class="px-6 py-3.5 text-center"><span class="px-2.5 py-0.5 rounded-lg text-xs font-bold border {{ $r->final_score >= $exam->passing_score ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">{{ $r->final_score >= $exam->passing_score ? 'Lulus' : 'Tidak Lulus' }}</span></td>
                        <td class="px-6 py-3.5 text-right text-gray-500 text-xs">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
