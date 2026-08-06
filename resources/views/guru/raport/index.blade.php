@extends('layouts.guru')
@section('title', 'Raport Kelas - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #881337 50%, #9f1239 100%) !important;">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-file-alt text-black"></i>
                    </div>
                    Raport Digital
                </h1>
                <p class="text-xs md:text-sm font-bold text-rose-200 mt-1" style="color: #fecdd3 !important;">Kelola dan terbitkan rapor hasil belajar siswa di kelas bimbingan Anda</p>
            </div>
            <form method="POST" action="{{ route('guru.raport.bulkDownload') }}" class="flex items-center gap-2" id="bulkDownloadForm">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                <select name="classroom_id" class="text-xs font-black border-2 border-black rounded-2xl px-4 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;" form="bulkDownloadForm">
                    @foreach($classrooms as $cr)
                        <option value="{{ $cr->id }}" style="color: #000000 !important;">{{ $cr->class_name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-2xl text-xs font-black uppercase tracking-wider shadow-md transition" onclick="return confirm('Download semua rapor kelas ini sebagai ZIP?')">
                    <i class="fas fa-download text-black"></i> Bulk Download
                </button>
            </form>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @php
            $statItems = [
                ['label' => 'Total', 'value' => $stats['total'], 'color' => 'gray', 'icon' => 'file-alt'],
                ['label' => 'Draft', 'value' => $stats['draft'], 'color' => 'yellow', 'icon' => 'pencil-alt'],
                ['label' => 'Finalized', 'value' => $stats['finalized'], 'color' => 'blue', 'icon' => 'check-circle'],
                ['label' => 'Published', 'value' => $stats['published'], 'color' => 'green', 'icon' => 'paper-plane'],
            ];
        @endphp
        @foreach($statItems as $item)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                <div class="w-8 h-8 bg-{{ $item['color'] }}-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-{{ $item['icon'] }} text-{{ $item['color'] }}-600 text-sm"></i>
                </div>
                <p class="text-xl font-bold text-gray-800">{{ $item['value'] }}</p>
                <p class="text-xs text-gray-500">{{ $item['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filters & Generate --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-rose-500 to-pink-600 px-5 py-3">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fas fa-filter"></i> Filter & Generate
            </h2>
        </div>
        <div class="p-5">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Semester</label>
                    <select name="semester_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 focus:border-rose-400 transition">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }}>
                                {{ $sem->semester_name ?? 'Semester '.$sem->semester_number }}
                                @if($sem->academicYear) ({{ $sem->academicYear->year }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas</label>
                    <select name="classroom_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 focus:border-rose-400 transition">
                        <option value="">Semua Kelas Saya</option>
                        @foreach($classrooms as $cr)
                            <option value="{{ $cr->id }}" {{ $selectedClassroomId == $cr->id ? 'selected' : '' }}>
                                {{ $cr->class_name }} ({{ $cr->students_count }} siswa)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 focus:border-rose-400 transition">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="finalized" {{ $status == 'finalized' ? 'selected' : '' }}>Finalized</option>
                        <option value="published" {{ $status == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                </div>
            </form>

            {{-- Generate Button --}}
            @if($selectedClassroomId)
                @php
                    $selectedClassroom = $classrooms->firstWhere('id', $selectedClassroomId);
                    $selectedSemester = $semesters->firstWhere('id', $semesterId);
                @endphp
                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                    <div class="text-sm text-emerald-800">
                        <span class="font-bold">Generate Rapor Otomatis:</span> 
                        Buat/perbarui rapor untuk kelas <span class="font-bold">{{ $selectedClassroom->class_name ?? '' }}</span> pada <span class="font-bold">{{ $selectedSemester->semester_name ?? 'Semester '.$selectedSemester?->semester_number }}</span>.
                    </div>
                    <form method="POST" action="{{ route('guru.raport.generate') }}">
                        @csrf
                        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                        <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-sm transition flex items-center justify-center gap-1.5"
                                onclick="return confirm('Generate/update rapor untuk kelas ini? Rapor draft akan di-update, rapor baru akan dibuat.')">
                            <i class="fas fa-cogs"></i> Generate Rapor Kelas
                        </button>
                    </form>
                </div>
            @else
                <div class="mt-4 pt-4 border-t border-gray-100 bg-amber-50 p-4 rounded-xl border border-amber-200 text-center text-sm text-amber-800 font-semibold">
                    <i class="fas fa-info-circle mr-1"></i> Silakan pilih kelas terlebih dahulu pada filter di atas untuk men-generate rapor.
                </div>
            @endif
        </div>
    </div>

    {{-- Report Cards Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list text-rose-500"></i> Daftar Rapor ({{ $reportCards->total() }})
            </h2>
        </div>
        @if($reportCards->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">Siswa</th>
                            <th class="px-5 py-3 text-center font-semibold">Kelas</th>
                            <th class="px-5 py-3 text-center font-semibold">Rata-rata</th>
                            <th class="px-5 py-3 text-center font-semibold">Peringkat</th>
                            <th class="px-5 py-3 text-center font-semibold">Predikat</th>
                            <th class="px-5 py-3 text-center font-semibold">Status</th>
                            <th class="px-5 py-3 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($reportCards as $rc)
                            @php
                                $statusColors = [
                                    'draft' => 'bg-yellow-100 text-yellow-700',
                                    'finalized' => 'bg-blue-100 text-blue-700',
                                    'published' => 'bg-green-100 text-green-700',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $rc->student->full_name ?? '-' }}</p>
                                        <p class="text-xs text-gray-400">{{ $rc->student->nisn ?? '' }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center text-xs text-gray-600">{{ $rc->classroom->class_name ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="font-bold {{ $rc->average_score >= 80 ? 'text-green-600' : ($rc->average_score >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format($rc->average_score, 1) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center font-bold text-gray-800">#{{ $rc->rank ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $rc->predicate == 'A' ? 'bg-green-100 text-green-700' : ($rc->predicate == 'B' ? 'bg-blue-100 text-blue-700' : ($rc->predicate == 'C' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')) }}">
                                        {{ $rc->predicate }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $statusColors[$rc->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($rc->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('guru.raport.show', $rc) }}" class="p-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition" title="Lihat">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        @if($rc->isEditable())
                                            <a href="{{ route('guru.raport.edit', $rc) }}" class="p-1.5 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition" title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                        @endif
                                        @if($rc->status === 'draft')
                                            <form method="POST" action="{{ route('guru.raport.finalize', $rc) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition" title="Finalize" onclick="return confirm('Finalize rapor ini?')">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($rc->status === 'finalized')
                                            <form method="POST" action="{{ route('guru.raport.publish', $rc) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="p-1.5 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition" title="Publish" onclick="return confirm('Publish rapor ini?')">
                                                    <i class="fas fa-paper-plane text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('guru.raport.print', $rc) }}" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition" title="Download PDF" target="_blank">
                                            <i class="fas fa-file-pdf text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $reportCards->links() }}
            </div>
        @else
            <div class="p-10 text-center text-gray-400">
                <i class="fas fa-file-alt text-4xl mb-3"></i>
                <p class="text-sm">Belum ada rapor untuk semester ini.</p>
                <p class="text-xs mt-1">Gunakan tombol "Generate Rapor" di atas untuk membuat rapor otomatis.</p>
            </div>
        @endif
    </div>
</div>
@endsection
