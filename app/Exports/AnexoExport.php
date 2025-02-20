<?php

namespace App\Exports;

use App\Models\Anexo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AnexoExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Anexo::select('internal_number', 'external_number', 'office', 'unit', 'person')->get();
    }

    /**
     * Definir los encabezados para el archivo Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Número Interno',
            'Número Externo',
            'Oficina',
            'Unidad',
            'Persona'
        ];
    }

    /**
     * Aplicar estilos a la hoja de Excel.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Primera fila en negrita
        ];
    }
}
