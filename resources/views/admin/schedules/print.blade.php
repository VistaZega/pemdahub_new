<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Matriks Jadwal Pelajaran - {{ $school->name ?? 'PembdaHUB' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #0f172a; }
        
        /* NO SLIDER - EXACT HVS LANDSCAPE FIT STYLES */
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; margin: 0 !important; font-size: 8.5px !important; width: 100% !important; overflow: hidden !important; }
            .print-container { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
            @page { size: landscape; margin: 4mm; }
            table { width: 100% !important; table-layout: fixed !important; border-collapse: collapse !important; }
            th, td { padding: 1.5px 1px !important; word-wrap: break-word !important; overflow: hidden !important; text-align: center !important; }
            .cell-box { padding: 1px !important; font-size: 8.5px !important; line-height: 1 !important; }
        }

        .badge-reguler { background-color: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-block-a { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-block-b { background-color: #fce7f3; color: #9d174d; border: 1px solid #fbcfe8; }
        .badge-parallel { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
    </style>
</head>
<body class="p-3">

    <!-- ACTION TOOLBAR (NO PRINT) -->
    <div class="no-print w-full mx-auto mb-3 flex items-center justify-between bg-white p-3 rounded-xl shadow-md border border-gray-200">
        <div class="flex items-center gap-3">
            <button onclick="window.history.back()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-xs transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <span class="text-xs font-semibold text-gray-500">Cetak Matriks Jadwal (Fit 1 Halaman HVS Landscape - Kode Pelajaran Only)</span>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow hover:shadow-indigo-500/25 transition flex items-center gap-2 text-xs">
                <i class="fas fa-print"></i> Cetak / Save PDF HVS Landscape
            </button>
        </div>
    </div>

    <!-- MAIN PRINT CONTAINER -->
    <div class="print-container w-full mx-auto bg-white p-3 rounded-xl shadow border border-gray-200 overflow-x-hidden">
        
        <!-- HEADER KOP SEKOAH -->
        <div class="border-b-2 border-gray-900 pb-1.5 mb-2 text-center">
            <h1 class="text-xl font-black uppercase tracking-wider text-gray-900 leading-tight">{{ $school->name ?? 'PEMBDA HUB' }}</h1>
            <p class="text-[10px] text-gray-600 leading-none mt-0.5">{{ $school->address ?? 'Sistem Informasi Akademik Terpadu' }}</p>
            <h2 class="text-xs font-black uppercase tracking-wide text-purple-900 mt-1">MATRIKS JADWAL PELAJARAN SEKOAH</h2>
            <div class="flex flex-wrap items-center justify-center gap-4 text-[10px] font-semibold text-gray-700 mt-0.5">
                <span><strong>Tahun Ajaran:</strong> {{ $academicYear->year ?? '-' }}</span>
                <span><strong>Semester:</strong> {{ ucfirst($semester) }}</span>
                <span><strong>Shift:</strong> 
                    @if($selectedShift === 'pagi') Shift Pagi (Reguler)
                    @elseif($selectedShift === 'siang') Shift Siang (Eksekutif)
                    @else Semua Shift
                    @endif
                </span>
                @if($currentRotation !== 'normal')
                <span><strong>Rotasi Blok:</strong> {{ strtoupper($currentRotation) }}</span>
                @endif
            </div>
        </div>

        <!-- LEGEND & INDIKATOR MODUL -->
        <div class="flex items-center justify-between text-[10px] mb-2 p-1 bg-gray-50 rounded border border-gray-200">
            <div class="font-bold text-gray-700">
                <i class="fas fa-info-circle text-purple-600"></i> Legenda:
            </div>
            <div class="flex items-center gap-2 font-semibold text-[9px]">
                <span class="px-1.5 py-0.5 rounded badge-reguler">Reguler</span>
                <span class="px-1.5 py-0.5 rounded badge-block-a">Blok Kelompok A</span>
                <span class="px-1.5 py-0.5 rounded badge-block-b">Blok Kelompok B</span>
                <span class="px-1.5 py-0.5 rounded badge-parallel">Paralel / Agama</span>
            </div>
        </div>

        <!-- TABLE MATRIKS JADWAL (FIT 1 HALAMAN - NO SLIDER) -->
        @php
            $days = [
                'monday' => 'Senin',
                'tuesday' => 'Selasa',
                'wednesday' => 'Rabu',
                'thursday' => 'Kamis',
                'friday' => 'Jumat',
                'saturday' => 'Sabtu'
            ];
            
            // Real principal details from DB
            $principalName = $school->principal->full_name ?? $school->principal_name ?? null;
            $principalNip = $school->principal->employee->nip ?? $school->principal->nip ?? null;
            $wakaName = $wakaKurikulum->full_name ?? null;
        @endphp

        <div class="w-full border border-gray-400 rounded overflow-hidden">
            <table class="w-full text-[9px] text-center border-collapse border border-gray-400 table-fixed">
                <thead>
                    <tr class="bg-gray-900 text-white font-bold border-b border-gray-400">
                        <th class="p-1 border-r border-gray-400 text-center w-12">Hari</th>
                        <th class="p-1 border-r border-gray-400 text-center w-16">Waktu</th>
                        @php
                            $romanGrades = [
                                10 => 'X', 11 => 'XI', 12 => 'XII',
                                7 => 'VII', 8 => 'VIII', 9 => 'IX'
                            ];
                        @endphp
                        @foreach($classrooms as $classroom)
                            @php
                                $romanGrade = $romanGrades[$classroom->grade_level] ?? $classroom->grade_level;
                                $rawName = $classroom->class_name ?: $classroom->class_code;
                                $cleanName = preg_replace('/^(?:(?:X|XI|XII|VII|VIII|IX|\d+)\s*[\-\:]?\s*)+/i', '', $rawName);
                                $displayClassName = $romanGrade . ' - ' . ($cleanName ?: $rawName);
                            @endphp
                            <th class="p-0.5 border-r border-gray-400 text-center align-middle">
                                <div class="font-black text-[8px] leading-tight text-white break-words max-h-10 overflow-hidden text-center uppercase tracking-tighter">
                                    {{ $displayClassName }}
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $dayKey => $dayLabel)
                        @php
                            $daySlots = $timeSlots->where('day_of_week', $dayKey)->sortBy('slot_order');
                            $slotCount = $daySlots->count();
                        @endphp

                        @if($slotCount > 0)
                            @foreach($daySlots as $index => $slot)
                            <tr class="border-b border-gray-300 {{ $slot->is_teaching_slot ? 'bg-white' : 'bg-amber-50/60' }}">
                                @if($index === 0)
                                <td rowspan="{{ $slotCount }}" class="p-1 border-r border-gray-400 text-center font-black bg-gray-100 uppercase tracking-widest text-[9px] align-middle">
                                    {{ $dayLabel }}
                                </td>
                                @endif
                                <td class="p-1 border-r border-gray-400 text-center font-bold bg-gray-50">
                                    <div class="text-gray-900 font-black text-[9px] leading-none truncate">{{ $slot->slot_name }}</div>
                                    <div class="text-[8px] text-gray-500 font-medium leading-none mt-0.5">{{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}</div>
                                </td>
                                @foreach($classrooms as $classroom)
                                    @php
                                        $key = $dayKey . '_' . $slot->id . '_' . $classroom->id;
                                        $cellSchedules = $scheduleGrid[$key] ?? [];
                                    @endphp
                                    <td class="p-0.5 border-r border-gray-300 text-center align-middle">
                                        @if(!$slot->is_teaching_slot)
                                            <span class="text-[8px] font-bold text-gray-400 uppercase leading-none">{{ $slot->slot_name }}</span>
                                        @elseif(!empty($cellSchedules))
                                            @foreach($cellSchedules as $sched)
                                                @php
                                                    $subjectCode = $sched->subject->code ?? $sched->subject->subject_code ?? $sched->subject->name ?? '-';
                                                    $blockType = $sched->teachingAssignment->block_type ?? 'none';
                                                    $blockTag = '';
                                                    $badgeClass = 'badge-reguler';
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
                                                <div class="p-0.5 mb-0.5 rounded text-center {{ $badgeClass }} cell-box shadow-xs">
                                                    <div class="font-black text-[9.5px] text-gray-900 leading-none tracking-tight truncate">
                                                        {{ $subjectCode }}
                                                    </div>
                                                    @if($blockTag)
                                                    <div class="text-[7.5px] font-bold text-gray-700 leading-none mt-0.5">{{ $blockTag }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-gray-300 text-[8px]">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- FOOTER TANDA TANGAN EKSPLISIT KEPALA SEKOAH UNIT -->
        <div class="mt-4 pt-2 border-t border-gray-400 grid grid-cols-2 text-[10px] font-semibold text-center page-break-inside-avoid">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold text-gray-900 mt-0.5">Waka Kurikulum / Tim Penjadwalan</p>
                <div class="h-10"></div>
                @if($wakaName)
                    <p class="font-black text-gray-900 underline">{{ $wakaName }}</p>
                @else
                    <p class="font-black text-gray-900 underline">( ___________________________ )</p>
                @endif
            </div>
            <div>
                <p>Gunungsitoli, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                <p class="font-bold text-gray-900 mt-0.5">Kepala {{ $school->name ?? 'Sekolah' }}</p>
                <div class="h-10"></div>
                @if($principalName)
                    <p class="font-black text-gray-900 underline">{{ $principalName }}</p>
                    @if($principalNip)
                        <p class="text-[8.5px] text-gray-600 font-normal">NIP. {{ $principalNip }}</p>
                    @endif
                @else
                    <p class="font-black text-gray-900 underline">( ___________________________ )</p>
                @endif
            </div>
        </div>

    </div>

</body>
</html>
