<?php

namespace App\Exports;

use App\Models\Mobile;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class MobileExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Mobile::select('number', 'office', 'direction', 'person')->get();
    }

    /**
     * Definir los encabezados para el archivo Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Número',
            'Oficina',
            'Dirección',
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
