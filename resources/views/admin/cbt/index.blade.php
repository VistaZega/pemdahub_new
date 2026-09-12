@extends('layouts.admin')
@section('title', 'Manajemen CBT')
@section('content')
<div class="space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-laptop-code text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Manajemen CBT</h1>
                    <p class="text-violet-50 mt-1 text-base">Computer Based Test — Monitoring & pengelolaan ujian online</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.cbt.exams.create') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-violet-700 rounded-xl font-semibold hover:bg-violet-50 transition shadow-lg shadow-violet-900/20">
                    <i class="fas fa-plus-circle mr-2"></i>Buat Ujian
                </a>
                <a href="{{ route('admin.cbt.banks') }}" class="inline-flex items-center px-5 py-2.5 bg-white/15 text-white rounded-xl hover:bg-white/25 transition border border-gray-200">
                    <i class="fas fa-database mr-2"></i>Bank Soal
                </a>
                <a href="{{ route('admin.cbt.report') }}" class="inline-flex items-center px-5 py-2.5 bg-white/15 text-white rounded-xl hover:bg-white/25 transition border border-gray-200">
                    <i class="fas fa-chart-pie mr-2"></i>Laporan
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        @php
            $totalExams = $exams->total();
            $activeExams = $exams->where('status', 'active')->count();
            $completedExams = $exams->where('status', 'completed')->count();
            $draftExams = $exams->where('status', 'draft')->count();
        @endphp
        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-lg hover:border-violet-200 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-violet-100 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fas fa-file-alt text-violet-600"></i></div>
                <span class="text-base font-medium text-gray-800 uppercase tracking-wider">Total</span>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $totalExams }}</div>
            <div class="text-base text-gray-700 mt-1">Ujian CBT</div>
        </div>
        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-lg hover:border-green-200 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fas fa-play-circle text-green-600"></i></div>
                @if($activeExams > 0)
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                @endif
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $activeExams }}</div>
            <div class="text-base text-gray-700 mt-1">Sedang Aktif</div>
        </div>
        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-lg hover:border-blue-200 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fas fa-check-circle text-blue-600"></i></div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $completedExams }}</div>
            <div class="text-base text-gray-700 mt-1">Selesai</div>
        </div>
        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-lg hover:border-amber-200 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fas fa-pencil-alt text-amber-600"></i></div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $draftExams }}</div>
            <div class="text-base text-gray-700 mt-1">Draf</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            @if($isSuperAdmin)
            <div class="flex-1 min-w-[200px]">
                <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Sekolah</label>
                <select name="school_id" class="w-full rounded-xl border-gray-200 focus:ring-violet-500 focus:border-violet-500 text-base bg-gray-50">
                    <option value="">Semua Sekolah</option>
                    @foreach(\App\Models\School::where('is_active', true)->orderBy('name')->get() as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex-1 min-w-[150px]">
                <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="w-full rounded-xl border-gray-200 focus:ring-violet-500 focus:border-violet-500 text-base bg-gray-50">
                    <option value="">Semua Status</option>
                    @foreach(['draft','published','active','completed','archived'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Mapel</label>
                <select name="subject_id" class="w-full rounded-xl border-gray-200 focus:ring-violet-500 focus:border-violet-500 text-base bg-gray-50">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Guru</label>
                <select name="teacher_id" class="w-full rounded-xl border-gray-200 focus:ring-violet-500 focus:border-violet-500 text-base bg-gray-50">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->full_name ?? $t->nip }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Scope</label>
                <select name="exam_scope" class="w-full rounded-xl border-gray-200 focus:ring-violet-500 focus:border-violet-500 text-base bg-gray-50">
                    <option value="">Semua</option>
                    <option value="school" {{ request('exam_scope') === 'school' ? 'selected' : '' }}>Sekolah</option>
                    <option value="class" {{ request('exam_scope') === 'class' ? 'selected' : '' }}>Kelas</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-violet-600 text-white rounded-xl hover:bg-violet-700 transition text-base font-medium shadow-sm">
                <i class="fas fa-search mr-1.5"></i>Filter
            </button>
        </form>
    </div>

    {{-- Exams Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="bg-gradient-to-r from-gray-50 to-gray-100/80 border-b border-gray-200">
                        <th class="px-5 py-4 text-left text-base font-bold text-gray-700 uppercase tracking-wider">Ujian</th>
                        <th class="px-5 py-4 text-left text-base font-bold text-gray-700 uppercase tracking-wider">Guru</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Tipe</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Scope</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Soal</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Peserta Kelas</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-center text-base font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($exams as $exam)
                    <tr class="hover:bg-violet-50/30 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-100 to-purple-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-alt text-violet-500 text-base"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-base font-semibold text-gray-900 truncate">{{ $exam->exam_title }}</div>
                                    <div class="text-base text-gray-800 mt-0.5">{{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-base text-gray-800">
                            @if($exam->teacher)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center text-xs font-bold shrink-0">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <span class="font-bold text-gray-900 truncate" title="{{ $exam->teacher->full_name }}">{{ $exam->teacher->full_name }}</span>
                                </div>
                            @else
                                <a href="{{ route('admin.cbt.show', $exam) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition" title="Klik untuk menetapkan guru pengampu">
                                    <i class="fas fa-user-plus text-[10px]"></i>
                                    <span>Atur Guru</span>
                                </a>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 text-base font-bold rounded-lg bg-violet-50 text-violet-700 border border-violet-100">{{ strtoupper($exam->exam_type) }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($exam->exam_scope === 'school')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-base font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100"><i class="fas fa-school text-base"></i>Sekolah</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-base font-semibold rounded-lg bg-teal-50 text-teal-700 border border-teal-100"><i class="fas fa-chalkboard text-base"></i>Kelas</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center"><span class="text-base font-bold text-gray-700">{{ $exam->total_questions_shown }}</span></td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $classNames = $exam->participants->map(fn($p) => $p->classroom?->class_name ?? $p->classroom?->name)->filter()->values();
                            @endphp
                            @if($classNames->count() === 1)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-violet-50 text-violet-700 border border-violet-100" title="{{ $classNames[0] }}">
                                    <i class="fas fa-chalkboard mr-1 text-violet-400"></i>{{ Str::limit($classNames[0], 14) }}
                                </span>
                            @elseif($classNames->count() > 1)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-violet-50 text-violet-700 border border-violet-100 cursor-help" title="{{ $classNames->implode(', ') }}">
                                    <i class="fas fa-users-class text-violet-500"></i>{{ $classNames->count() }} Kelas
                                </span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php $sc = match($exam->status) {
                                'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'published' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'archived' => 'bg-gray-50 text-gray-800 border-gray-200',
                                default => 'bg-gray-50 text-gray-800 border-gray-200',
                            }; @endphp
                            <span class="inline-flex items-center px-2.5 py-1 text-base font-bold rounded-lg border {{ $sc }}">{{ ucfirst($exam->status) }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.cbt.show', $exam) }}" class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-800 flex items-center justify-center hover:bg-indigo-100 transition" title="Detail"><i class="fas fa-eye text-base"></i></a>
                                <a href="{{ route('admin.cbt.results', $exam) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition" title="Hasil"><i class="fas fa-chart-bar text-base"></i></a>
                                @if(in_array($exam->status, ['active', 'published']))
                                <form action="{{ route('admin.cbt.force-complete', $exam) }}" method="POST" class="inline" onsubmit="return confirm('Tutup paksa ujian ini?')">@csrf
                                    <button class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition" title="Tutup Paksa"><i class="fas fa-stop text-base"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl bg-violet-50 flex items-center justify-center mb-4"><i class="fas fa-laptop-code text-2xl text-violet-300"></i></div>
                                <p class="text-gray-700 font-medium">Belum ada ujian CBT</p>
                                <p class="text-gray-800 text-base mt-1">Buat ujian pertama untuk memulai</p>
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
