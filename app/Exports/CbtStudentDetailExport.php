<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class CbtStudentDetailExport implements FromArray, WithStyles, WithColumnWidths
{
    protected array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 12,
            'C' => 20,
            'D' => 48,
            'E' => 38,
            'F' => 38,
            'G' => 17,
            'H' => 13,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '263B63'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(36);

        // Identitas siswa.
        for ($row = 2; $row <= 8; $row++) {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->mergeCells("C{$row}:H{$row}");
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:H{$row}")
                ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($row)->setRowHeight(21);
        }

        // Header tabel.
        $sheet->getStyle('A9:H9')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '263B63'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension(9)->setRowHeight(32);

        $sheet->getStyle("A9:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        // Warna status jawaban.
        for ($row = 10; $row <= $lastRow; $row++) {
            $status = (string) $sheet->getCell("G{$row}")->getValue();
            $colors = [
                'Benar' => ['DCFCE7', '166534'],
                'Salah' => ['FEE2E2', '991B1B'],
                'Tidak dijawab' => ['E5E7EB', '374151'],
                'Belum dinilai' => ['FEF3C7', '92400E'],
            ];

            if (isset($colors[$status])) {
                [$fill, $font] = $colors[$status];
                $sheet->getStyle("G{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle("G{$row}")->getFill()
                    ->getStartColor()->setRGB($fill);
                $sheet->getStyle("G{$row}")->getFont()
                    ->getColor()->setRGB($font);
            }

            $sheet->getStyle("H{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Rekapitulasi.
        for ($row = 10; $row <= $lastRow; $row++) {
            if ($sheet->getCell("A{$row}")->getValue() === 'REKAPITULASI HASIL UJIAN') {
                $sheet->mergeCells("A{$row}:H{$row}");
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '263B63'],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(26);

                for ($r = $row + 1; $r <= $lastRow; $r++) {
                    $sheet->mergeCells("A{$r}:B{$r}");
                    $sheet->mergeCells("C{$r}:H{$r}");
                    $sheet->getStyle("A{$r}:B{$r}")->getFont()->setBold(true);
                    $sheet->getRowDimension($r)->setRowHeight(21);
                }
                break;
            }
        }

        // Cetak A4 landscape.
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->setShowGridlines(false);
        $sheet->getPageMargins()
            ->setTop(0.45)
            ->setBottom(0.55)
            ->setLeft(0.25)
            ->setRight(0.25);

        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(9, 9);
        $sheet->getHeaderFooter()->setOddFooter('&LDetail Jawaban Ujian&RHalaman &P dari &N');

        $sheet->freezePane('A10');
        $sheet->setAutoFilter('A9:H9');

        return [];
    }
}
