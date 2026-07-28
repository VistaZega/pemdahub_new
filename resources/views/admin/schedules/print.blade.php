<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jadwal Pelajaran - {{ $school->name ?? 'PembdaHUB' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-container { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
        }
        .badge-reguler { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-block-a { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-block-b { background-color: #fce7f3; color: #9d174d; border: 1px solid #fbcfe8; }
        .badge-parallel { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
    </style>
</head>
<body class="p-6">

    <!-- ACTION TOOLBAR (NO PRINT) -->
    <div class="no-print max-w-7xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-md border border-gray-100">
        <div class="flex items-center gap-3">
            <button onclick="window.history.back()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <span class="text-sm font-semibold text-gray-500">Pratinjau Cetak Jadwal Pelajaran</span>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-indigo-500/25 transition flex items-center gap-2 text-sm">
                <i class="fas fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- MAIN PRINT CONTAINER -->
    <div class="print-container max-w-7xl mx-auto bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
        
        <!-- HEADER KOP -->
        <div class="border-b-2 border-gray-800 pb-4 mb-6 text-center relative">
            <h1 class="text-2xl font-black uppercase tracking-wider text-gray-900">{{ $school->name ?? 'PEMBDA HUB' }}</h1>
            <p class="text-xs text-gray-600 mt-1">{{ $school->address ?? 'Sistem Informasi Akademik Terpadu' }}</p>
            <h2 class="text-lg font-bold uppercase tracking-wide text-purple-900 mt-3">JADWAL PELAJARAN SEKOAH</h2>
            <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-semibold text-gray-700 mt-2">
                <span><i class="fas fa-calendar-alt text-purple-600"></i> Tahun Ajaran: {{ $academicYear->year ?? '-' }}</span>
                <span><i class="fas fa-flag text-purple-600"></i> Semester: {{ ucfirst($semester) }}</span>
                <span><i class="fas fa-clock text-purple-600"></i> Shift KBM: 
                    @if($selectedShift === 'pagi') ☀️ Shift Pagi (Reguler)
                    @elseif($selectedShift === 'siang') 🌙 Shift Siang (Eksekutif)
                    @else 🔘 Semua Shift
                    @endif
                </span>
                @if($currentRotation !== 'normal')
                <span><i class="fas fa-sync text-purple-600"></i> Rotasi Blok Aktif: <strong>{{ strtoupper($currentRotation) }}</strong></span>
                @endif
            </div>
        </div>

        <!-- LEGEND & INFORMASI INDIKATOR -->
        <div class="flex flex-wrap items-center justify-between gap-3 text-xs mb-4 p-3 bg-gray-50 rounded-xl border border-gray-200">
            <div class="flex items-center gap-2 font-bold text-gray-700">
                <i class="fas fa-info-circle text-purple-600"></i> Keterangan Sistem:
            </div>
            <div class="flex flex-wrap items-center gap-3 font-semibold">
                <span class="px-2.5 py-1 rounded-lg badge-reguler">Jadwal Reguler</span>
                <span class="px-2.5 py-1 rounded-lg badge-block-a">Blok Kelompok A</span>
                <span class="px-2.5 py-1 rounded-lg badge-block-b">Blok Kelompok B</span>
                <span class="px-2.5 py-1 rounded-lg badge-parallel">Paralel / Agama</span>
            </div>
        </div>

        <!-- TABLE MATRIKS JADWAL -->
        @php
            $days = [
                'monday' => 'Senin',
                'tuesday' => 'Selasa',
                'wednesday' => 'Rabu',
                'thursday' => 'Kamis',
                'friday' => 'Jumat',
                'saturday' => 'Sabtu'
            ];
        @endphp

        @foreach($days as $dayKey => $dayLabel)
            @php
                $daySlots = $timeSlots->where('day_of_week', $dayKey)->sortBy('slot_order');
            @endphp

            @if($daySlots->count() > 0)
            <div class="mb-8 page-break-inside-avoid">
                <div class="bg-indigo-900 text-white px-4 py-2 rounded-t-xl font-extrabold text-sm uppercase tracking-wider flex items-center justify-between">
                    <span><i class="fas fa-calendar-day mr-2"></i> Hari {{ $dayLabel }}</span>
                    <span class="text-xs font-normal text-indigo-200">{{ $daySlots->count() }} Slot Pelajaran</span>
                </div>

                <div class="overflow-x-auto border border-gray-300 rounded-b-xl">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-800 border-b border-gray-300 font-bold">
                                <th class="p-2 border-r border-gray-300 text-center w-28">Jam / Slot</th>
                                @foreach($classrooms as $classroom)
                                <th class="p-2 border-r border-gray-300 text-center min-w-[120px]">
                                    <div class="font-extrabold">{{ $classroom->class_name }}</div>
                                    <div class="text-[10px] text-gray-500 font-medium">Tingkat {{ $classroom->grade_level }} • {{ ucfirst($classroom->shift ?? 'pagi') }}</div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($daySlots as $slot)
                            <tr class="{{ $slot->is_teaching_slot ? 'bg-white' : 'bg-amber-50/50' }}">
                                <!-- TIME SLOT CELL -->
                                <td class="p-2 border-r border-gray-300 text-center font-semibold bg-gray-50">
                                    <div class="font-bold text-gray-900">{{ $slot->slot_name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</div>
                                </td>

                                <!-- CLASSROOM COLUMNS -->
                                @foreach($classrooms as $classroom)
                                    @php
                                        $key = $dayKey . '_' . $slot->id . '_' . $classroom->id;
                                        $cellSchedules = $scheduleGrid[$key] ?? [];
                                    @endphp
                                    <td class="p-1.5 border-r border-gray-200 text-center align-top min-h-[50px]">
                                        @if(!$slot->is_teaching_slot)
                                            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-widest">{{ $slot->slot_name }}</span>
                                        @elseif(!empty($cellSchedules))
                                            @foreach($cellSchedules as $sched)
                                                @php
                                                    $blockType = $sched->teachingAssignment->block_type ?? 'none';
                                                    $badgeClass = 'badge-reguler';
                                                    $blockTag = '';
                                                    if ($blockType === 'all') {
                                                        $badgeClass = 'badge-block-a';
                                                        $blockTag = '(Blok A)';
                                                    } elseif ($blockType === 'split') {
                                                        $badgeClass = 'badge-block-b';
                                                        $blockTag = '(Blok B)';
                                                    } elseif ($blockType === 'parallel') {
                                                        $badgeClass = 'badge-parallel';
                                                        $blockTag = '(Paralel)';
                                                    }
                                                @endphp
                                                <div class="p-1.5 mb-1 rounded-lg text-left {{ $badgeClass }} shadow-sm">
                                                    <div class="font-bold leading-tight">{{ $sched->subject->name ?? $sched->subject->subject_name ?? '-' }} <span class="text-[9px] opacity-75">{{ $blockTag }}</span></div>
                                                    <div class="text-[10px] mt-0.5 opacity-90"><i class="fas fa-user-tie text-[9px] mr-1"></i>{{ $sched->teacher->full_name ?? '-' }}</div>
                                                    @if($sched->duration_slots > 1)
                                                    <div class="text-[9px] mt-0.5 text-gray-500 italic">{{ $sched->duration_slots }} Slot Jam</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach

        <!-- FOOTER TANDA TANGAN -->
        <div class="mt-12 pt-6 border-t border-gray-300 grid grid-cols-2 text-xs font-semibold text-center page-break-inside-avoid">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold text-gray-900 mt-1">Kepala Sekolah</p>
                <div class="h-16"></div>
                <p class="font-extrabold text-gray-900 underline">( ___________________________ )</p>
            </div>
            <div>
                <p>Nias Selatan, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                <p class="font-bold text-gray-900 mt-1">Waka Kurikulum / Tim Penjadwalan</p>
                <div class="h-16"></div>
                <p class="font-extrabold text-gray-900 underline">( ___________________________ )</p>
            </div>
        </div>

    </div>

</body>
</html>
