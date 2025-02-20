<?php

namespace App\Imports;

use App\Models\Anexo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AnexoImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Anexo([
            'internal_number' => $row['internal_number'] ?? $row['Internal Number'] ?? null,
            'external_number' => $row['external_number'] ?? $row['External Number'] ?? null,
            'office' => $row['office'] ?? $row['Office'] ?? null,
            'unit' => $row['unit'] ?? $row['Unit'] ?? null,
            'person' => $row['person'] ?? $row['Person'] ?? null,
        ]);
    }

    public function headingRow(): int
    {
        return 1; // Indica que los encabezados están en la primera fila
    }
}
