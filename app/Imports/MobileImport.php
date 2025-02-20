<?php

namespace App\Imports;

use App\Models\Mobile;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MobileImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Mobile([
            'number' => $row['number'] ?? $row['Number'] ?? null,
            'office' => $row['office'] ?? $row['Office'] ?? null,
            'direction' => $row['direction'] ?? $row['Direction'] ?? null,
            'person' => $row['person'] ?? $row['Person'] ?? null,
        ]);
    }

    public function headingRow(): int
    {
        return 1; // Indica que los encabezados están en la primera fila
    }
}
