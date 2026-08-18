<?php

namespace App\Exports;

use App\Models\LmsQuiz;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class QuizResultsExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected $quiz;
    protected $course;
    protected $recapData;
    protected $maxAttemptsCount;
    protected $selectedClassroom;
    protected $headerRowCount = 0;
    protected $dataRowCount = 0;

    public function __construct(LmsQuiz $quiz, $recapData, int $maxAttemptsCount, $selectedClassroom = null)
    {
        $this->quiz = $quiz;
        $this->course = $quiz->course;
        $this->recapData = $recapData;
        $this->maxAttemptsCount = $maxAttemptsCount;
        $this->selectedClassroom = $selectedClassroom;
    }

    public function array(): array
    {
        $data = [];

        // Title
        $data[] = ['REKAPITULASI HASIL PENGERJAAN KUIS LMS'];
        $data[] = []; // empty row

        // Quiz Information
        $data[] = ['Judul Kuis', ':', $this->quiz->title];
        $data[] = ['Mata Pelajaran / Kursus', ':', $this->course->name ?? '-'];
        $data[] = ['Guru Pengampu', ':', $this->course->teacher->user->name ?? '-'];
        $data[] = ['Rombel / Kelas', ':', $this->selectedClassroom ? $this->selectedClassroom->class_name : 'Semua Rombel Terdaftar'];
        $data[] = ['Batas Kelulusan (KKM)', ':', ($this->quiz->passing_score ?? 75) . '%'];
        $data[] = ['Maksimal Percobaan', ':', ($this->quiz->max_attempts ?? 1) . ' kali'];
        $data[] = ['Tanggal Export', ':', date('d-m-Y H:i')];
        $data[] = []; // empty row before table

        // Table Header
        $headerRow = ['No', 'NISN', 'Nama Siswa', 'Kelas'];
        for ($i = 1; $i <= $this->maxAttemptsCount; $i++) {
            $headerRow[] = 'Percobaan ' . $i;
        }
        $headerRow[] = 'Nilai Tertinggi';
        $headerRow[] = 'Status Kelulusan';

        $data[] = $headerRow;
        $this->headerRowCount = count($data);

        // Data rows
        $no = 1;
        foreach ($this->recapData as $row) {
            $dataRow = [
                $no++,
                $row['student']->nisn ?? $row['student']->nis ?? '-',
                $row['student']->full_name ?? $row['student']->user->name ?? '-',
                $row['classroom_name'] ?? '-',
            ];

            // Attempts columns
            for ($i = 1; $i <= $this->maxAttemptsCount; $i++) {
                $score = $row['attempts_by_index'][$i] ?? null;
                $dataRow[] = $score !== null ? $score : '-';
            }

            // Highest score
            $dataRow[] = $row['best_score'] !== null ? $row['best_score'] : '-';

            // Status
            $dataRow[] = $row['status_label'];

            $data[] = $dataRow;
        }

        $this->dataRowCount = count($data);

        return $data;
    }

    public function title(): string
    {
        return 'Rekap Nilai Kuis';
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 6,   // No
            'B' => 16,  // NISN
            'C' => 32,  // Nama Siswa
            'D' => 16,  // Kelas
        ];

        $colIndex = 5;
        for ($i = 1; $i <= $this->maxAttemptsCount; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++);
            $widths[$colLetter] = 14;
        }

        $highestColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++);
        $widths[$highestColLetter] = 16; // Nilai Tertinggi

        $statusColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++);
        $widths[$statusColLetter] = 18; // Status

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        // Title formatting
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A3:A9')->getFont()->setBold(true);

        $headerRowIndex = $this->headerRowCount;
        $totalCols = 4 + $this->maxAttemptsCount + 2;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // Header Table styling
        $sheet->getStyle("A{$headerRowIndex}:{$lastColLetter}{$headerRowIndex}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '312E81'], // Indigo 900
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension($headerRowIndex)->setRowHeight(26);

        // Data rows styling
        $startDataRow = $headerRowIndex + 1;
        $endDataRow = $this->dataRowCount;

        if ($endDataRow >= $startDataRow) {
            // Borders
            $sheet->getStyle("A{$headerRowIndex}:{$lastColLetter}{$endDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ]);

            // Alignment
            $sheet->getStyle("A{$startDataRow}:A{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$startDataRow}:B{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$startDataRow}:D{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Center align attempt columns and highest score and status
            $firstAttemptCol = 'E';
            $sheet->getStyle("{$firstAttemptCol}{$startDataRow}:{$lastColLetter}{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra stripe styling & score bold
            $highestColIndex = 4 + $this->maxAttemptsCount + 1;
            $highestColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($highestColIndex);
            $sheet->getStyle("{$highestColLetter}{$startDataRow}:{$highestColLetter}{$endDataRow}")->getFont()->setBold(true);

            for ($r = $startDataRow; $r <= $endDataRow; $r++) {
                $sheet->getRowDimension($r)->setRowHeight(20);
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->getFill()->applyFromArray([
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ]);
                }
            }
        }

        return [];
    }
}
