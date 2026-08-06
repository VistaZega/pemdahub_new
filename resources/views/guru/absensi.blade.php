@extends('layouts.guru')
@section('title', 'Absensi Siswa - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #311b92 50%, #4a148c 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-clipboard-check text-black"></i>
                    </div>
                    Absensi Siswa
                </h1>
                <p class="text-xs md:text-sm font-bold text-purple-200 mt-1" style="color: #e9d5ff !important;">
                    Rekapitulasi dan catatan kehadiran siswa @if($activeYear) · {{ $activeYear->year }} @endif
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('guru.absensi.input') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-2xl text-xs font-black uppercase tracking-wider shadow-md transition">
                    <i class="fas fa-plus-circle text-black"></i> Input Absensi
                </a>
                <form method="GET" class="flex items-center gap-2">
                    <select name="classroom_id" onchange="this.form.submit()" class="text-xs font-black border-2 border-black bg-white rounded-2xl px-4 py-2.5 shadow-sm text-black outline-none focus:ring-0">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms as $cr)
                            <option value="{{ $cr->id }}" {{ $selectedClassroomId == $cr->id ? 'selected' : '' }}>
                                {{ $cr->class_name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>

    @if(!$selectedClassroomId)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <i class="fas fa-hand-pointer text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Pilih kelas terlebih dahulu untuk melihat rekap absensi.</p>
        </div>
    @elseif(!$selectedClassroom)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <i class="fas fa-exclamation-circle text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Kelas tidak ditemukan atau Anda tidak mengajar di kelas ini.</p>
        </div>
    @else
        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @php
                $items = [
                    ['label' => 'Hadir', 'value' => $summary['present'], 'color' => 'green', 'icon' => 'check-circle'],
                    ['label' => 'Sakit', 'value' => $summary['sick'], 'color' => 'yellow', 'icon' => 'briefcase-medical'],
                    ['label' => 'Izin', 'value' => $summary['permission'], 'color' => 'blue', 'icon' => 'envelope'],
                    ['label' => 'Alpha', 'value' => $summary['absent'], 'color' => 'red', 'icon' => 'times-circle'],
                    ['label' => 'Persentase', 'value' => $summary['percentage'].'%', 'color' => 'purple', 'icon' => 'percentage'],
                ];
            @endphp
            @foreach($items as $item)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="w-8 h-8 bg-{{ $item['color'] }}-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-{{ $item['icon'] }} text-{{ $item['color'] }}-600 text-sm"></i>
                    </div>
                    <p class="text-xl font-bold text-gray-800">{{ $item['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $item['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Attendance Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-purple-500"></i> Riwayat Absensi - {{ $selectedClassroom->class_name }}
                </h2>
            </div>
            @if($attendances->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold">Tanggal</th>
                                <th class="px-5 py-3 text-left font-semibold">Siswa</th>
                                <th class="px-5 py-3 text-center font-semibold">Status</th>
                                <th class="px-5 py-3 text-left font-semibold">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($attendances->take(100) as $att)
                                @php
                                    $statusMap = [
                                        'hadir' => ['Hadir', 'bg-green-100 text-green-700', 'check-circle'],
                                        'sakit' => ['Sakit', 'bg-yellow-100 text-yellow-700', 'briefcase-medical'],
                                        'izin' => ['Izin', 'bg-blue-100 text-blue-700', 'envelope'],
                                        'alpha' => ['Alpha', 'bg-red-100 text-red-700', 'times-circle'],
                                    ];
                                    $s = $statusMap[$att->status] ?? ['Unknown', 'bg-gray-100 text-gray-700', 'question-circle'];
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3 text-gray-800">
                                        {{ \Carbon\Carbon::parse($att->date)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3 font-medium text-gray-800">{{ $att->student->full_name ?? '-' }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $s[1] }}">
                                            <i class="fas fa-{{ $s[2] }}"></i> {{ $s[0] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-gray-500">{{ $att->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($attendances->count() > 100)
                    <div class="px-5 py-3 border-t border-gray-100 text-center text-xs text-gray-400">
                        Menampilkan 100 dari {{ $attendances->count() }} data
                    </div>
                @endif
            @else
                <div class="p-10 text-center text-gray-400">
                    <i class="fas fa-clipboard-check text-4xl mb-3"></i>
                    <p class="text-sm">Belum ada data absensi untuk kelas ini.</p>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
