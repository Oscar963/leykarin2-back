<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InmueblesTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    public function array(): array
    {
        return [
            // Fila 1: Encabezados (ya definidos en headings())
            // Fila 2: Primer ejemplo de datos
            [
                '001',
                'Casa Municipal',
                'Av. España',
                '123',
                'Lote 1',
                'M1',
                'Villa Central',
                'Foja 123',
                '456',
                '2024',
                'ROL-123456',
                '250 m²',
                'Linda con calle',
                'Linda con lote 2',
                'Linda con pasaje',
                'Linda con avenida',
                'Decreto 001/2024',
                'Decreto 002/2024',
                'Sin observaciones'
            ],
            // Fila 3: Segundo ejemplo de datos
            [
                '002',
                'Oficina Administrativa',
                'Pasaje Los Aromos',
                '456',
                'Sitio 2',
                'M2',
                'Población Norte',
                'Foja 456',
                '789',
                '2023',
                'ROL-789012',
                '180 m²',
                'Linda con avenida',
                'Linda con pasaje',
                'Linda con calle',
                'Linda con lote 3',
                'Decreto 003/2023',
                'Decreto 004/2023',
                'Oficina en buen estado'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'numero',
            'descripcion',
            'calle',
            'numeracion',
            'lote_sitio',
            'manzana',
            'poblacion_villa',
            'foja',
            'inscripcion_numero',
            'inscripcion_anio',
            'rol_avaluo',
            'superficie',
            'deslinde_norte',
            'deslinde_sur',
            'deslinde_este',
            'deslinde_oeste',
            'decreto_incorporacion',
            'decreto_destinacion',
            'observaciones'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados: fondo azul y texto blanco
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '06048c']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Filas de ejemplo con fondo amarillo claro
        $sheet->getStyle('A2:S3')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC']
            ]
        ]);
        
        // Bordes para todas las celdas
        $sheet->getStyle('A1:S3')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);
        
        // Congelar encabezados
        $sheet->freezePane('A2');
        
        // Agregar nota informativa
        $sheet->setCellValue('A5', 'NOTA: Los encabezados deben mantenerse exactamente como están escritos (en minúsculas, sin tildes ni espacios extra).');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0000'));
        
        return [];
    }

    public function title(): string
    {
        return 'Plantilla Inmuebles';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Aquí puedes agregar validaciones o instrucciones si lo deseas
            },
        ];
    }
}
