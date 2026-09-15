<?php

namespace App\Exports;

use App\Models\CbtExam;
use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CbtExamParticipationExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected CbtExam $exam;
    protected array $participationData;
    protected ?Classroom $selectedClassroom;
    protected int $headerRowIndex = 0;
    protected int $dataRowCount = 0;

    public function __construct(CbtExam $exam, array $participationData, ?Classroom $selectedClassroom = null)
    {
        $this->exam = $exam;
        $this->participationData = $participationData;
        $this->selectedClassroom = $selectedClassroom;
    }

    public function array(): array
    {
        $data = [];

        // 1. Judul Header
        $data[] = ['REKAPITULASI STATUS KEIKUTSERTAAN SISWA - CBT PEMBDAHUB'];
        $data[] = []; // Empty row

        // 2. Info Ujian
        $data[] = ['Judul Ujian', ':', $this->exam->exam_title];
        $data[] = ['Mata Pelajaran', ':', $this->exam->subject->name ?? $this->exam->subject->subject_name ?? '-'];
        $data[] = ['Tahun Ajaran / Semester', ':', ($this->exam->academicYear->name ?? '-') . ' / ' . ($this->exam->semester->name ?? '-')];
        $data[] = ['Rombel / Kelas', ':', $this->selectedClassroom ? $this->selectedClassroom->class_name : 'Semua Rombel Terdaftar'];
        $data[] = ['Wali Kelas', ':', $this->selectedClassroom?->homeroomTeacher?->full_name ?? ($this->selectedClassroom ? 'Belum ditentukan' : 'Sesuai Rombel Masing-masing')];
        $data[] = ['Guru Pengampu', ':', $this->exam->teacher?->full_name ?? ($this->exam->creator?->name ?? '-')];
        $data[] = ['KKM / Batas Kelulusan', ':', ($this->exam->passing_score ?? 75)];
        $data[] = ['Total Siswa Terdaftar', ':', $this->participationData['total_eligible'] . ' Siswa'];
        $data[] = ['Sudah Mengerjakan', ':', $this->participationData['completed_count'] . ' Siswa'];
        $data[] = ['Sedang Mengerjakan', ':', $this->participationData['in_progress_count'] . ' Siswa'];
        $data[] = ['Belum Mengerjakan', ':', $this->participationData['not_started_count'] . ' Siswa'];
        $data[] = ['Persentase Partisipasi', ':', $this->participationData['participation_rate'] . '%'];
        $data[] = ['Tanggal Unduh', ':', date('d-m-Y H:i') . ' WIB'];
        $data[] = []; // Empty row before table

        // 3. Table Headers
        $headerRow = [
            'No',
            'NISN',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jenis Kelamin',
            'Status Ujian',
            'Skor / Nilai',
            'Predikat',
            'Kelulusan',
            'Benar',
            'Salah',
            'Kosong',
            'Waktu Selesai'
        ];

        $data[] = $headerRow;
        $this->headerRowIndex = count($data);

        // 4. Data Rows
        $no = 1;
        $students = $this->participationData['students'] ?? collect();

        foreach ($students as $row) {
            $student = $row['student'];
            $result = $row['result'];

            $statusText = match($row['status']) {
                'completed' => 'SUDAH MENGERJAKAN',
                'in_progress' => 'SEDANG MENGERJAKAN',
                default => 'BELUM MENGERJAKAN',
            };

            $genderText = match(strtoupper((string)($student->gender ?? ''))) {
                'L', 'LAKI-LAKI' => 'Laki-laki',
                'P', 'PEREMPUAN' => 'Perempuan',
                default => '-',
            };

            $lulusText = '-';
            if ($result) {
                $lulusText = $result->is_passed ? 'LULUS' : 'TIDAK LULUS';
            }

            $finishedAtText = '-';
            if (!empty($row['finished_at'])) {
                $finishedAtText = date('d/m/Y H:i', strtotime($row['finished_at']));
            }

            $data[] = [
                $no++,
                $student->nisn ? "'" . $student->nisn : '-',
                $student->nis ? "'" . $student->nis : '-',
                $student->full_name ?? '-',
                $row['classroom_name'] ?? '-',
                $genderText,
                $statusText,
                $result ? number_format($result->final_score, 1) : '-',
                $result ? ($result->predicate ?? '-') : '-',
                $lulusText,
                $result ? $result->correct_answers : '-',
                $result ? $result->wrong_answers : '-',
                $result ? $result->unanswered : '-',
                $finishedAtText,
            ];
        }

        $this->dataRowCount = count($students);

        return $data;
    }

    public function title(): string
    {
        $className = $this->selectedClassroom ? $this->selectedClassroom->class_name : 'Semua_Kelas';
        return 'Status Ujian ' . substr($className, 0, 20);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 16,  // NISN
            'C' => 14,  // NIS
            'D' => 32,  // Nama Siswa
            'E' => 18,  // Kelas
            'F' => 14,  // Jenis Kelamin
            'G' => 24,  // Status Ujian
            'H' => 14,  // Skor / Nilai
            'I' => 10,  // Predikat
            'J' => 14,  // Kelulusan
            'K' => 8,   // Benar
            'L' => 8,   // Salah
            'M' => 8,   // Kosong
            'N' => 20,  // Waktu Selesai
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $headerRow = $this->headerRowIndex;
        $totalRows = $headerRow + $this->dataRowCount;

        // 1. Judul Utama
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E293B'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Info Ujian Labels
        $sheet->getStyle('A3:A15')->getFont()->setBold(true);

        // 3. Header Tabel
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF065F46'], // Dark Emerald
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF047857'],
                ],
            ],
        ];

        $sheet->getStyle("A{$headerRow}:N{$headerRow}")->applyFromArray($headerStyle);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // 4. Baris Data Styling
        if ($this->dataRowCount > 0) {
            $dataRange = "A" . ($headerRow + 1) . ":N{$totalRows}";

            // Border seluruh data
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->getColor()->setARGB('FFE2E8F0');

            // Alignment kolom
            $sheet->getStyle("A" . ($headerRow + 1) . ":A{$totalRows}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B" . ($headerRow + 1) . ":C{$totalRows}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E" . ($headerRow + 1) . ":F{$totalRows}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G" . ($headerRow + 1) . ":N{$totalRows}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Styling per baris status
            $students = $this->participationData['students'] ?? collect();
            $currRow = $headerRow + 1;

            foreach ($students as $row) {
                $status = $row['status'];
                $statusCell = "G{$currRow}";

                if ($status === 'completed') {
                    $sheet->getStyle($statusCell)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF065F46']], // Dark Green
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1FAE5']], // Light Emerald
                    ]);
                } elseif ($status === 'in_progress') {
                    $sheet->getStyle($statusCell)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF92400E']], // Dark Amber
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']], // Light Amber
                    ]);
                } else {
                    $sheet->getStyle($statusCell)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF991B1B']], // Dark Red
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']], // Light Red
                    ]);
                }

                $sheet->getRowDimension($currRow)->setRowHeight(22);
                $currRow++;
            }
        }

        return [];
    }
}
