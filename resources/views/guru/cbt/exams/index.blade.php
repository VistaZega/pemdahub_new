@extends('layouts.guru')
@section('title', 'Ujian CBT')
@section('content')
<div class="space-y-8">
    {{-- Hero Header (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 md:p-8 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #0f766e 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-md text-2xl">
                    <i class="fas fa-laptop-code text-black"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight" style="color: #ffffff !important;">Ujian CBT</h1>
                    <p class="text-xs md:text-sm font-bold text-teal-200 mt-1" style="color: #99f6e4 !important;">Kelola dan pantau ujian online komputer/smartphone siswa</p>
                </div>
            </div>
            <a href="{{ route('guru.cbt.exams.create') }}" class="inline-flex items-center px-5 py-3 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-2xl font-black uppercase text-xs tracking-wider shadow-md transition-all">
                <i class="fas fa-plus-circle text-black mr-2"></i>Buat Ujian
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600 text-base"></i></div>
        <p class="text-emerald-700 text-base font-medium">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Exams Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-base">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-5 py-4 text-left text-base font-semibold text-gray-700 uppercase tracking-wider">Judul Ujian</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Tipe</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Soal</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Durasi</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Kelas</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($exams as $exam)
                    <tr class="hover:bg-emerald-50/30 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-alt text-emerald-500 text-base"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900 truncate">{{ $exam->exam_title }}</div>
                                    <div class="text-base text-gray-800 mt-0.5">{{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 text-base font-bold rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-100">{{ strtoupper($exam->exam_type) }}</span>
                        </td>
                        <td class="px-5 py-4 text-center"><span class="font-bold text-gray-700">{{ $exam->total_questions_shown }}</span></td>
                        <td class="px-5 py-4 text-center text-gray-800">{{ $exam->duration_minutes }}′</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-700 text-base font-bold">{{ $exam->participants->count() }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php $sc = match($exam->status) {
                                'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'published' => 'bg-amber-100 text-amber-800 border-amber-200',
                                default => 'bg-gray-50 text-gray-800 border-gray-200',
                            }; @endphp
                            <span class="inline-flex items-center px-2.5 py-1 text-base font-bold rounded-lg border {{ $sc }}">{{ ucfirst($exam->status) }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('guru.cbt.exams.show', $exam) }}" class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-800 flex items-center justify-center hover:bg-indigo-100 transition" title="Detail"><i class="fas fa-eye text-base"></i></a>
                                @if($exam->status === 'draft')
                                <form action="{{ route('guru.cbt.exams.publish', $exam) }}" method="POST" class="inline">@csrf
                                    <button class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition" title="Terbitkan"><i class="fas fa-paper-plane text-base"></i></button>
                                </form>
                                @elseif($exam->status === 'published')
                                <form action="{{ route('guru.cbt.exams.activate', $exam) }}" method="POST" class="inline">@csrf
                                    <button class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition" title="Aktifkan"><i class="fas fa-play text-base"></i></button>
                                </form>
                                @elseif($exam->status === 'active')
                                <form action="{{ route('guru.cbt.exams.complete', $exam) }}" method="POST" class="inline" onsubmit="return confirm('Selesaikan ujian ini?')">@csrf
                                    <button class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition" title="Selesaikan"><i class="fas fa-stop text-base"></i></button>
                                </form>
                                @endif
                                <a href="{{ route('guru.cbt.exams.results', $exam) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition" title="Hasil"><i class="fas fa-chart-bar text-base"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4"><i class="fas fa-laptop-code text-2xl text-emerald-300"></i></div>
                                <p class="text-gray-700 font-medium">Belum ada ujian CBT</p>
                                <p class="text-gray-800 text-base mt-1">Buat ujian pertama Anda di atas</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exams->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">{{ $exams->links() }}</div>
        @endif
    </div>
</div>
@endsection
