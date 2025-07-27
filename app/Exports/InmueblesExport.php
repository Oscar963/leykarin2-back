<?php

namespace App\Exports;

use App\Models\Inmueble;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InmueblesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Inmueble::all();
    }

    /**
     * Encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'Número',
            'Descripción',
            'Calle',
            'Numeración',
            'Lote/Sitio',
            'Manzana',
            'Población/Villa',
            'Foja',
            'Inscripción N°',
            'Inscripción Año',
            'Rol Avalúo',
            'Superficie (m²)',
            'Deslinde Norte',
            'Deslinde Sur',
            'Deslinde Este',
            'Deslinde Oeste',
            'Decreto Incorporación',
            'Decreto Destinación',
            'Observaciones',
        ];
    }

    /**
     * Mapear los datos de cada fila
     */
    public function map($inmueble): array
    {
        return [
            $inmueble->numero,
            $inmueble->descripcion,
            $inmueble->calle,
            $inmueble->numeracion,
            $inmueble->lote_sitio,
            $inmueble->manzana,
            $inmueble->poblacion_villa,
            $inmueble->foja,
            $inmueble->inscripcion_numero,
            $inmueble->inscripcion_anio,
            $inmueble->rol_avaluo,
            $inmueble->superficie,
            $inmueble->deslinde_norte,
            $inmueble->deslinde_sur,
            $inmueble->deslinde_este,
            $inmueble->deslinde_oeste,
            $inmueble->decreto_incorporacion,
            $inmueble->decreto_destinacion,
            $inmueble->observaciones,
        ];
    }

    /**
     * Estilos para el archivo Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Estilo para los encabezados (fila 1)
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
                'name' => 'Calibri'
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '06048C']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Estilo para todas las celdas de datos
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:T' . $lastRow)->applyFromArray([
            'font' => [
                'size' => 10,
                'name' => 'Calibri'
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Alternar colores de filas para mejor legibilidad
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':T' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA']
                    ]
                ]);
            }
        }

        // Centrar columnas numéricas y de año
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Número
        $sheet->getStyle('J:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Inscripción Año
        $sheet->getStyle('L:L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Superficie

        // Fijar la primera fila
        $sheet->freezePane('A2');

        return $sheet;
    }

    /**
     * Anchos de las columnas
     */
    // No es necesario columnWidths, lo manejamos con ShouldAutoSize y AfterSheet

    /**
     * Evento para asegurar ancho mínimo de columnas según encabezado
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headings = $this->headings();
                $colIndex = 1;
                foreach ($headings as $heading) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    // Largo mínimo: largo del texto + 2 extra
                    $minWidth = mb_strlen($heading) + 2;
                    $currentWidth = $sheet->getColumnDimension($column)->getWidth();
                    if ($currentWidth < $minWidth) {
                        $sheet->getColumnDimension($column)->setAutoSize(false);
                        $sheet->getColumnDimension($column)->setWidth($minWidth);
                    }
                    $colIndex++;
                }
            }
        ];
    }
}
