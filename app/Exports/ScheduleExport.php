<?php

namespace App\Exports;

use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ScheduleExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected $schoolId;
    protected $academicYearId;
    protected $semester;
    protected $classroomId;
    protected $day;
    protected $shift;
    protected $classroom;
    protected $academicYear;
    
    public function __construct($schoolId, $academicYearId, $semester, $classroomId = null, $day = null, $shift = 'all')
    {
        $this->schoolId = $schoolId;
        $this->academicYearId = $academicYearId;
        $this->semester = $semester;
        $this->classroomId = $classroomId;
        $this->day = $day;
        $this->shift = $shift ?: 'all';
        
        if ($classroomId) {
            $this->classroom = Classroom::find($classroomId);
        }
        
        $this->academicYear = AcademicYear::find($academicYearId);
    }
    
    public function array(): array
    {
        $data = [];
        
        // Title rows
        $data[] = ['JADWAL PELAJARAN'];
        $data[] = []; // Empty row
        
        // Info details
        $data[] = ['Tahun Ajaran', ':', $this->academicYear->year ?? '-'];
        $data[] = ['Semester', ':', ucfirst($this->semester)];
        if ($this->classroom) {
            $data[] = ['Kelas', ':', $this->classroom->class_name];
        }
        $shiftLabel = $this->shift === 'pagi' ? 'Shift Pagi (Reguler)' : ($this->shift === 'siang' ? 'Shift Siang (Eksekutif)' : 'Semua Shift');
        $data[] = ['Shift KBM', ':', $shiftLabel];
        $data[] = []; // Empty row before table
        
        // Days mapping
        $dayMapping = [
            'Senin' => 'monday',
            'Selasa' => 'tuesday',
            'Rabu' => 'wednesday',
            'Kamis' => 'thursday',
            'Jumat' => 'friday',
            'Sabtu' => 'saturday'
        ];
        
        // Determine which days to show
        $daysToShow = $this->day ? [$this->day] : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        // Header row: Jam | Senin | Selasa | Rabu | Kamis | Jumat
        $headerRow = ['Jam'];
        foreach ($daysToShow as $day) {
            $headerRow[] = $day;
        }
        $data[] = $headerRow;
        
        // Get all time slots grouped by day and slot_order
        $allTimeSlots = [];
        $maxSlots = 0;
        $shiftFilter = $this->shift;
        
        foreach ($daysToShow as $day) {
            $dayEnglish = $dayMapping[$day];
            $slotsQuery = TimeSlot::where('school_id', $this->schoolId)
                ->where('day_of_week', $dayEnglish)
                ->where('is_teaching_slot', true);
                
            if ($shiftFilter !== 'all') {
                $slotsQuery->where(function($q) use ($shiftFilter) {
                    $q->where('shift', $shiftFilter)->orWhere('shift', 'all')->orWhereNull('shift');
                });
            }

            $slots = $slotsQuery->orderBy('slot_order')->get();
            
            $allTimeSlots[$day] = $slots->keyBy('slot_order');
            $maxSlots = max($maxSlots, $slots->max('slot_order') ?? 0);
        }
        
        // Get all schedules grouped
        $schedules = [];
        foreach ($daysToShow as $day) {
            $dayEnglish = $dayMapping[$day];
            $daySchedules = Schedule::with(['teacher', 'subject', 'classroom', 'timeSlot', 'teachingAssignment'])
                ->where('school_id', $this->schoolId)
                ->where('academic_year_id', $this->academicYearId)
                ->where('semester', $this->semester)
                ->where('day_of_week', $dayEnglish);
            
            if ($this->classroomId) {
                $daySchedules->where('classroom_id', $this->classroomId);
            }
            
            $schedules[$day] = $daySchedules->get()->groupBy('time_slot_id');
        }
        
        // Build grid rows - iterate through all possible slot orders
        for ($slotOrder = 1; $slotOrder <= $maxSlots; $slotOrder++) {
            $row = [];
            $hasTimeSlot = false;
            $timeDisplay = "Jam {$slotOrder}";
            
            // Get time display from first available day
            foreach ($daysToShow as $day) {
                if (isset($allTimeSlots[$day][$slotOrder])) {
                    $timeSlot = $allTimeSlots[$day][$slotOrder];
                    $timeDisplay = $timeSlot->slot_name . "\n" . 
                                  substr($timeSlot->start_time, 0, 5) . ' - ' . 
                                  substr($timeSlot->end_time, 0, 5);
                    $hasTimeSlot = true;
                    break;
                }
            }
            
            $row[] = $timeDisplay;
            
            // Each day column
            foreach ($daysToShow as $day) {
                $timeSlot = $allTimeSlots[$day][$slotOrder] ?? null;
                
                if ($timeSlot) {
                    $schedulesInSlot = $schedules[$day][$timeSlot->id] ?? null;
                    
                    if ($schedulesInSlot && $schedulesInSlot->count() > 0) {
                        $cellContents = [];
                        foreach ($schedulesInSlot as $schedule) {
                            $subjectName = $schedule->subject->name ?? $schedule->subject->subject_name ?? '-';
                            $teacherName = $schedule->teacher->full_name ?? '-';
                            $classroomName = $schedule->classroom->class_name ?? '-';
                            $duration = $schedule->duration_slots > 1 ? " ({$schedule->duration_slots} jam)" : "";
                            
                            $blockType = $schedule->teachingAssignment->block_type ?? 'none';
                            $blockLabel = '';
                            if ($blockType === 'all') {
                                $blockLabel = " [Blok Klp A]";
                            } elseif ($blockType === 'split') {
                                $blockLabel = " [Blok Klp B]";
                            } elseif ($blockType === 'parallel') {
                                $blockLabel = " [Paralel]";
                            }
                            
                            $cellContents[] = $subjectName . $blockLabel . "\n" . 
                                          $teacherName . "\n" . 
                                          $classroomName . 
                                          $duration;
                        }
                        
                        $row[] = implode("\n---\n", $cellContents);
                    } else {
                        $row[] = '-';
                    }
                } else {
                    $row[] = '-';
                }
            }
            
            $data[] = $row;
        }
        
        return $data;
    }
    
    public function title(): string
    {
        if ($this->classroom) {
            return substr('Jadwal ' . $this->classroom->class_name, 0, 31);
        }
        return 'Jadwal Pelajaran';
    }
    
    public function columnWidths(): array
    {
        $widths = ['A' => 18]; // Jam column
        
        // Day columns
        $columns = ['B', 'C', 'D', 'E', 'F'];
        foreach ($columns as $col) {
            $widths[$col] = 35;
        }
        
        return $widths;
    }
    
    public function styles(Worksheet $sheet)
    {
        // Merge title cells
        $sheet->mergeCells('A1:F1');
        
        // Get last row and column
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        // Find header row (contains "Jam")
        $headerRow = 0;
        for ($i = 1; $i <= 10; $i++) {
            if ($sheet->getCell("A{$i}")->getValue() === 'Jam') {
                $headerRow = $i;
                break;
            }
        }
        
        $dataStartRow = $headerRow + 1;
        
        return [
            // Title row (row 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Info rows (row 3-5) - labels
            '3:5' => [
                'font' => [
                    'bold' => true,
                ],
            ],
            // Header row - Jam | Senin | Selasa | dst
            $headerRow => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'], // Indigo
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
            // All data cells
            "A{$dataStartRow}:{$highestColumn}{$highestRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '999999'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ],
            // Time column
            "A{$dataStartRow}:A{$highestRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6'], // Gray-100
                ],
            ],
        ];
    }
}
