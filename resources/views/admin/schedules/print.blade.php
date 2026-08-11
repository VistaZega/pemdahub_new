<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Matriks Jadwal Pelajaran - {{ $school->name ?? 'PembdaHUB' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #0f172a; }
        
        /* EXACT A4 LANDSCAPE SINGLE-PAGE PRINT FIT */
        @page {
            size: A4 landscape;
            margin: 5mm 6mm 5mm 6mm;
        }

        @media print {
            .no-print { display: none !important; }
            html, body {
                width: 285mm !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 7.5pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            table {
                width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
                page-break-inside: avoid !important;
            }
            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            th, td {
                padding: 1px 1px !important;
                word-wrap: break-word !important;
                overflow: hidden !important;
                text-align: center !important;
            }
            .cell-box {
                padding: 1px 2px !important;
                font-size: 7.5pt !important;
                line-height: 1.1 !important;
            }
            .footer-ttd {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        .badge-reguler { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
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
            <span class="text-xs font-bold text-indigo-900">🖨️ Cetak Matriks Jadwal Pelajaran (Desain Presisi A4 Landscape - 1 Halaman)</span>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black rounded-xl shadow-md hover:shadow-indigo-500/25 transition flex items-center gap-2 text-xs">
                <i class="fas fa-print"></i> Cetak / Simpan PDF (A4 Landscape)
            </button>
        </div>
    </div>

    <!-- MAIN PRINT CONTAINER -->
    <div class="print-container w-full mx-auto bg-white p-2.5 rounded-xl shadow border border-gray-200 overflow-x-hidden">
        
        <!-- HEADER KOP SEKOAH -->
        <div class="border-b-2 border-slate-900 pb-1 mb-1.5 text-center">
            <h1 class="text-lg font-black uppercase tracking-wider text-slate-900 leading-tight">{{ $school->name ?? 'PEMBDA HUB' }}</h1>
            <p class="text-[9.5px] text-slate-600 leading-none mt-0.5">{{ $school->address ?? 'Sistem Informasi Akademik Terpadu' }}</p>
            <h2 class="text-xs font-black uppercase tracking-wide text-indigo-900 mt-0.5">MATRIKS JADWAL PELAJARAN SEKOAH</h2>
            <div class="flex flex-wrap items-center justify-center gap-3 text-[9px] font-bold text-slate-700 mt-0.5">
                <span><strong>Tahun Ajaran:</strong> {{ $academicYear->year ?? '-' }}</span>
                <span>•</span>
                <span><strong>Semester:</strong> {{ ucfirst($semester) }}</span>
                <span>•</span>
                <span><strong>Shift:</strong> 
                    @if($selectedShift === 'pagi') Shift Pagi (Reguler)
                    @elseif($selectedShift === 'siang') Shift Siang (Eksekutif)
                    @else Semua Shift
                    @endif
                </span>
                @if($selectedGradeLevel && $selectedGradeLevel !== 'all')
                <span>•</span>
                <span><strong>Tingkat Kelas:</strong> Kelas {{ $selectedGradeLevel }}</span>
                @endif
                @if($currentRotation !== 'normal')
                <span>•</span>
                <span><strong>Rotasi Blok:</strong> {{ strtoupper($currentRotation) }}</span>
                @endif
            </div>
        </div>

        <!-- LEGEND & INDIKATOR MODUL -->
        <div class="flex items-center justify-between text-[9px] mb-1.5 px-2 py-0.5 bg-slate-50 rounded border border-slate-300">
            <div class="font-bold text-slate-800">
                <i class="fas fa-info-circle text-indigo-600"></i> Legenda Matriks:
            </div>
            <div class="flex items-center gap-2 font-bold text-[8.5px]">
                <span class="px-1.5 py-0.5 rounded badge-reguler">Reguler</span>
                <span class="px-1.5 py-0.5 rounded badge-block-a">Blok Kelompok A</span>
                <span class="px-1.5 py-0.5 rounded badge-block-b">Blok Kelompok B</span>
                <span class="px-1.5 py-0.5 rounded badge-parallel">Paralel / Agama</span>
            </div>
        </div>

        <!-- TABLE MATRIKS JADWAL (A4 LANDSCAPE SINGLE PAGE FIT) -->
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

        <div class="w-full border border-slate-500 rounded overflow-hidden">
            <table class="w-full text-[8.5px] text-center border-collapse border border-slate-500 table-fixed">
                <thead>
                    <tr class="bg-slate-900 text-white font-black border-b border-slate-500">
                        <th class="p-1 border-r border-slate-500 text-center w-10">Hari</th>
                        <th class="p-1 border-r border-slate-500 text-center w-14">Waktu</th>
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
                            <th class="p-0.5 border-r border-slate-500 text-center align-middle">
                                <div class="font-black text-[8px] leading-tight text-white break-words max-h-8 overflow-hidden text-center uppercase tracking-tight">
                                    {{ $displayClassName }}
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $dayKey => $dayLabel)
                        @php
                            $daySlots = $timeSlots->filter(function($slot) use ($dayKey, $dayLabel) {
                                $d = strtolower(trim($slot->day_of_week ?? ''));
                                return $d === strtolower($dayKey) || $d === strtolower($dayLabel);
                            })->sortBy(function($slot) {
                                return ($slot->start_time ?? '00:00') . '_' . sprintf('%04d', $slot->slot_order ?? 0);
                            })->unique(function($slot) {
                                return trim($slot->slot_name) . '_' . trim($slot->start_time);
                            });
                            $slotCount = $daySlots->count();
                        @endphp

                        @if($slotCount > 0)
                            @foreach($daySlots as $index => $slot)
                            <tr class="border-b border-slate-400 {{ $slot->is_teaching_slot ? ($loop->parent->odd ? 'bg-white' : 'bg-slate-50') : 'bg-amber-50/70' }}">
                                @if($index === 0)
                                <td rowspan="{{ $slotCount }}" class="p-1 border-r border-slate-500 text-center font-black bg-slate-100 uppercase tracking-widest text-[8.5px] align-middle">
                                    {{ $dayLabel }}
                                </td>
                                @endif
                                <td class="p-0.5 border-r border-slate-500 text-center font-bold bg-slate-100">
                                    <div class="text-slate-900 font-black text-[8.5px] leading-none truncate">{{ $slot->slot_name }}</div>
                                    <div class="text-[7.5px] text-slate-600 font-semibold leading-none mt-0.5">{{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}</div>
                                </td>
                                @foreach($classrooms as $classroom)
                                    @php
                                        $k1 = strtolower($dayKey) . '_' . $slot->id . '_' . $classroom->id;
                                        $k2 = strtolower($slot->day_of_week) . '_' . $slot->id . '_' . $classroom->id;
                                        $cellSchedules = $scheduleGrid[$k1] ?? $scheduleGrid[$k2] ?? [];
                                    @endphp
                                    <td class="p-0.5 border-r border-slate-400 text-center align-middle">
                                        @if(!$slot->is_teaching_slot)
                                            <span class="text-[7.5px] font-bold text-amber-800 uppercase leading-none">{{ $slot->slot_name }}</span>
                                        @elseif(!empty($cellSchedules))
                                            @foreach($cellSchedules as $sched)
                                                @php
                                                    $subjectCode = $sched->subject->code ?? $sched->subject->subject_code ?? $sched->subject->name ?? '-';
                                                    $teacherName = $sched->teacher->full_name ?? '-';
                                                    $blockType = $sched->teachingAssignment->block_type ?? 'none';
                                                    $blockTag = '';
                                                    $badgeClass = 'badge-reguler';
                                                    if ($blockType === 'all') {
                                                        $badgeClass = 'badge-block-a';
                                                        $blockTag = '(A)';
                                                    } elseif ($blockType === 'split') {
                                                        $badgeClass = 'badge-block-b';
                                                        $blockTag = '(B)';
                                                    } elseif ($blockType === 'parallel') {
                                                        $badgeClass = 'badge-parallel';
                                                        $blockTag = '(Paralel)';
                                                    }
                                                @endphp
                                                <div class="p-0.5 mb-0.5 rounded text-center {{ $badgeClass }} cell-box shadow-xs" title="{{ $sched->subject->name ?? '' }} — {{ $teacherName }}">
                                                    <div class="font-black text-[8.5px] text-slate-900 leading-none tracking-tight truncate">
                                                        {{ $subjectCode }}
                                                    </div>
                                                    @if($teacherName && $teacherName !== '-')
                                                    <div class="text-[7px] text-slate-700 font-semibold leading-none mt-0.5 truncate max-w-[100px] mx-auto">
                                                        {{ strlen($teacherName) > 12 ? substr($teacherName, 0, 10) . '.' : $teacherName }}
                                                    </div>
                                                    @endif
                                                    @if($blockTag)
                                                    <div class="text-[7px] font-extrabold text-slate-700 leading-none mt-0.5">{{ $blockTag }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-slate-300 text-[8px]">-</span>
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
        <div class="mt-2 pt-1 border-t border-slate-400 grid grid-cols-2 text-[9px] font-semibold text-center footer-ttd">
            <div>
                <p class="leading-none">Mengetahui,</p>
                <p class="font-bold text-slate-900 mt-0.5 leading-none">Waka Kurikulum / Tim Penjadwalan</p>
                <div class="h-8"></div>
                @if($wakaName)
                    <p class="font-black text-slate-900 underline leading-none">{{ $wakaName }}</p>
                @else
                    <p class="font-black text-slate-900 underline leading-none">( ___________________________ )</p>
                @endif
            </div>
            <div>
                <p class="leading-none">Gunungsitoli, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                <p class="font-bold text-slate-900 mt-0.5 leading-none">Kepala {{ $school->name ?? 'Sekolah' }}</p>
                <div class="h-8"></div>
                @if($principalName)
                    <p class="font-black text-slate-900 underline leading-none">{{ $principalName }}</p>
                    @if($principalNip)
                        <p class="text-[8px] text-slate-600 font-normal leading-none mt-0.5">NIP. {{ $principalNip }}</p>
                    @endif
                @else
                    <p class="font-black text-slate-900 underline leading-none">( ___________________________ )</p>
                @endif
            </div>
        </div>

    </div>

</body>
</html>
