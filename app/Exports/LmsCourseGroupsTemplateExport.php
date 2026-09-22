<?php

namespace App\Exports;

use App\Models\LmsCourse;
use App\Http\Controllers\Guru\LmsAssignmentController;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LmsCourseGroupsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected LmsCourse $course;

    public function __construct(LmsCourse $course)
    {
        $this->course = $course;
    }

    public function array(): array
    {
        $assignmentCtrl = app(LmsAssignmentController::class);
        $refMethod = new \ReflectionMethod($assignmentCtrl, 'getEnrolledStudentsForCourse');
        $refMethod->setAccessible(true);
        $allStudents = $refMethod->invoke($assignmentCtrl, $this->course);

        if ($allStudents->isEmpty()) {
            return [
                [
                    'nama_kelompok' => 'Kelompok 1',
                    'tema_proyek' => 'Contoh Tema / Judul Proyek 1',
                    'nisn' => '0012345678',
                    'nama_siswa' => 'Siswa Contoh 1',
                    'peran' => 'Ketua',
                    'kelas' => 'X IPA 1',
                ],
                [
                    'nama_kelompok' => 'Kelompok 1',
                    'tema_proyek' => 'Contoh Tema / Judul Proyek 1',
                    'nisn' => '0012345679',
                    'nama_siswa' => 'Siswa Contoh 2',
                    'peran' => 'Anggota',
                    'kelas' => 'X IPA 1',
                ],
                [
                    'nama_kelompok' => 'Kelompok 2',
                    'tema_proyek' => 'Contoh Tema / Judul Proyek 2',
                    'nisn' => '0012345680',
                    'nama_siswa' => 'Siswa Contoh 3',
                    'peran' => 'Ketua',
                    'kelas' => 'X IPA 2',
                ],
            ];
        }

        $rows = [];
        // Mengelompokkan awal per 5 siswa sebagai contoh draf
        $chunked = $allStudents->chunk(5);
        $groupNum = 1;

        foreach ($chunked as $chunk) {
            $groupName = "Kelompok " . $groupNum;
            $isFirst = true;

            foreach ($chunk as $std) {
                $rows[] = [
                    'nama_kelompok' => $groupName,
                    'tema_proyek' => '',
                    'nisn' => (string)($std->nisn ?? $std->nis ?? $std->id),
                    'nama_siswa' => $std->user->name ?? $std->full_name ?? '',
                    'peran' => $isFirst ? 'Ketua' : 'Anggota',
                    'kelas' => $std->classroom_name ?? '-',
                ];
                $isFirst = false;
            }
            $groupNum++;
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'nama_kelompok',
            'tema_proyek',
            'nisn',
            'nama_siswa',
            'peran',
            'kelas',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7C3AED'] // Soft Purple header
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
