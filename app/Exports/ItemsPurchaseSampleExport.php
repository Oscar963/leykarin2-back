<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemsPurchaseSampleExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $rows;

    public function __construct($rows = 10)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->generateSampleData();
    }

    public function headings(): array
    {
        return [
            'Línea',
            'Producto o Servicio',
            'Cantidad',
            'Monto',
            'Cantidad OC',
            'Meses envio OC',
            'Dist. Regional',
            'Cod. Gasto Presupuestario',
            'Tipo de Compra',
            'Mes de publicación',
            'Comentario',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Encabezados: fondo azul y texto blanco
        $sheet->getStyle('A1:K1')->applyFromArray([
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

        // Datos de ejemplo con fondo verde claro
        $sheet->getStyle("A2:K{$highestRow}")->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F5E8']
            ]
        ]);

        // Bordes para todas las celdas
        $sheet->getStyle("A1:K{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Congelar la fila de encabezados
        $sheet->freezePane('A2');

        return [];
    }

    public function title(): string
    {
        return 'Plantilla Ítems de Compra';
    }

    public function generateSampleData()
    {
        $sampleData = [
            // Equipos informáticos
            [
                'Línea' => 1,
                'Producto o Servicio' => 'Laptop HP ProBook 450 G8 Intel Core i5',
                'Cantidad' => 5,
                'Monto' => 650000,
                'Cantidad OC' => 2,
                'Meses envio OC' => 'Ene, Feb',
                'Dist. Regional' => 'Región Metropolitana',
                'Cod. Gasto Presupuestario' => '123456',
                'Tipo de Compra' => 'Bienes',
                'Mes de publicación' => 'Dic 2025',
                'Comentario' => 'Equipos informáticos para personal administrativo (opcional)',
            ],
            [
                'Línea' => 2,
                'Producto o Servicio' => 'Impresora multifuncional HP LaserJet Pro M404n',
                'Cantidad' => 3,
                'Monto' => 180000,
                'Cantidad OC' => 1,
                'Meses envio OC' => 'Mar',
                'Dist. Regional' => 'Valparaíso',
                'Cod. Gasto Presupuestario' => '123457',
                'Tipo de Compra' => 'Bienes',
                'Mes de publicación' => 'Ene 2026',
                'Comentario' => 'Impresoras para áreas de atención al público',
            ],
            [
                'Línea' => 3,
                'Producto o Servicio' => 'Monitor LED 24" Samsung F24T350FHL',
                'Cantidad' => 8,
                'Monto' => 95000,
                'Cantidad OC' => 2,
                'Meses envio OC' => 'Abr, May',
                'Dist. Regional' => 'Biobío',
                'Cod. Gasto Presupuestario' => '123458',
                'Tipo de Compra' => 'Bienes',
                'Mes de publicación' => 'Feb 2026',
                'Comentario' => 'Monitores para estaciones de trabajo',
            ],
            // Servicios
            [
                'Línea' => 4,
                'Producto o Servicio' => 'Servicio de mantenimiento preventivo equipos informáticos',
                'Cantidad' => 12,
                'Monto' => 45000,
                'Cantidad OC' => 1,
                'Meses envio OC' => 'Jun',
                'Dist. Regional' => 'O\'Higgins',
                'Cod. Gasto Presupuestario' => '123459',
                'Tipo de Compra' => 'Servicios',
                'Mes de publicación' => 'Mar 2026',
                'Comentario' => 'Mantenimiento mensual de equipos',
            ],
            [
                'Línea' => 5,
                'Producto o Servicio' => 'Licencia Microsoft Office 365 Business Premium',
                'Cantidad' => 25,
                'Monto' => 85000,
                'Cantidad OC' => 1,
                'Meses envio OC' => 'Jul',
                'Dist. Regional' => 'Maule',
                'Cod. Gasto Presupuestario' => '123460',
                'Tipo de Compra' => 'Servicios',
                'Mes de publicación' => 'Abr 2026',
                'Comentario' => 'Licencias anuales para personal',
            ],
            // Mobiliario
            [
                'Línea' => 6,
                'Producto o Servicio' => 'Escritorio ejecutivo con cajonera',
                'Cantidad' => 10,
                'Monto' => 120000,
                'Cantidad OC' => 2,
                'Meses envio OC' => 'Ago, Sep',
                'Dist. Regional' => 'Los Lagos',
                'Cod. Gasto Presupuestario' => '123461',
                'Tipo de Compra' => 'Bienes',
                'Mes de publicación' => 'May 2026',
                'Comentario' => 'Mobiliario para nuevas oficinas',
            ],
            [
                'Línea' => 7,
                'Producto o Servicio' => 'Silla ergonómica ejecutiva con reposabrazos',
                'Cantidad' => 15,
                'Monto' => 85000,
                'Cantidad OC' => 2,
                'Meses envio OC' => 'Oct, Nov',
                'Dist. Regional' => 'Araucanía',
                'Cod. Gasto Presupuestario' => '123462',
                'Tipo de Compra' => 'Bienes',
                'Mes de publicación' => 'Jun 2026',
                'Comentario' => 'Sillas para personal administrativo',
            ],
            // Materiales
            [
                'Línea' => 8,
                'Producto o Servicio' => 'Papel bond A4 75g 500 hojas',
                'Cantidad' => 100,
                'Monto' => 8500,
                'Cantidad OC' => 4,
                'Meses envio OC' => 'Ene, Abr, Jul, Oct',
                'Dist. Regional' => 'Tarapacá',
                'Cod. Gasto Presupuestario' => '123463',
                'Tipo de Compra' => 'Bienes',
                'Mes de publicación' => 'Jul 2026',
                'Comentario' => 'Papel para impresoras institucionales',
            ],
            [
                'Línea' => 9,
                'Producto o Servicio' => 'Cartuchos de tinta HP 952XL Negro',
                'Cantidad' => 20,
                'Monto' => 25000,
                'Cantidad OC' => 2,
                'Meses envio OC' => 'Feb, Ago',
                'Dist. Regional' => 'Antofagasta',
                'Cod. Gasto Presupuestario' => '123464',
                'Tipo de Compra' => 'Bienes',
                'Mes de publicación' => 'Ago 2026',
                'Comentario' => 'Cartuchos para impresoras HP',
            ],
            [
                'Línea' => 10,
                'Producto o Servicio' => 'Servicio de asesoría legal especializada',
                'Cantidad' => 1,
                'Monto' => 2500000,
                'Cantidad OC' => 1,
                'Meses envio OC' => 'Dic',
                'Dist. Regional' => 'Coquimbo',
                'Cod. Gasto Presupuestario' => '123465',
                'Tipo de Compra' => 'Servicios',
                'Mes de publicación' => 'Sep 2026',
                'Comentario' => 'Asesoría legal para contrataciones públicas',
            ],
        ];

        // Si se requieren más filas, duplicar y modificar datos
        if ($this->rows > 10) {
            $additionalRows = [];
            for ($i = 11; $i <= $this->rows; $i++) {
                $baseRow = $sampleData[($i - 1) % 10];
                $additionalRows[] = [
                    'Línea' => $i,
                    'Producto o Servicio' => $baseRow['Producto o Servicio'] . ' - Variante ' . ($i - 10),
                    'Cantidad' => $baseRow['Cantidad'] + rand(1, 5),
                    'Monto' => $baseRow['Monto'] + rand(10000, 50000),
                    'Cantidad OC' => $baseRow['Cantidad OC'],
                    'Meses envio OC' => $baseRow['Meses envio OC'],
                    'Dist. Regional' => $baseRow['Dist. Regional'],
                    'Cod. Gasto Presupuestario' => $baseRow['Cod. Gasto Presupuestario'] + ($i - 10),
                    'Tipo de Compra' => $baseRow['Tipo de Compra'],
                    'Mes de publicación' => $baseRow['Mes de publicación'],
                    'Comentario' => $baseRow['Comentario'] . ' - Fila adicional ' . $i,
                ];
            }
            $sampleData = array_merge($sampleData, $additionalRows);
        }

        // Retornar solo el número de filas solicitado
        return array_slice($sampleData, 0, $this->rows);
    }
} 