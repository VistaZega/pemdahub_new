<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class LmsCourseGroupsImport implements ToCollection, WithHeadingRow
{
    protected array $rows = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->rows[] = $row->toArray();
        }
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
