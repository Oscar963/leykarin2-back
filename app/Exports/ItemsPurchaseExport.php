<?php

namespace App\Exports;

use App\Models\ItemPurchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ItemsPurchaseExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle, WithMultipleSheets
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function sheets(): array
    {
        return [
            new ItemsPurchaseSheet($this->projectId),
            new PublicationDatesSheet($this->projectId),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ItemPurchase::where('project_id', $this->projectId)
            ->with('project', 'statusItemPurchase', 'typePurchase', 'budgetAllocation', 'publicationMonth')
            ->orderBy('item_number', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Línea',
            'Producto o Servicio',
            'Cantidad',
            'Monto',
            'Total Ítem',
            'Cantidad OC',
            'Meses envio OC',
            'Dist. Regional',
            'Asignación Presupuestaria',
            'Cod. Gasto Presupuestario',
            'Tipo de Compra',
            'Cód. tipo compra',
            'Mes de publicación',
            'Comentario',
        ];
    }

    public function map($row): array
    {
        return [
            $row->item_number,
            $row->product_service,
            $row->quantity_item,
            $row->amount_item,
            $row->getTotalAmount(),
            $row->quantity_oc,
            $row->months_oc,
            $row->regional_distribution,
            $row->budgetAllocation->code . ' - ' . $row->budgetAllocation->description,
            $row->cod_budget_allocation_type,
            $row->typePurchase->name,
            $row->typePurchase->cod_purchase_type,
            $row->publication_date_formatted,
            $row->comment ?? '',
        ];
    }

    /**
     * Aplicar estilos a la hoja de Excel.
     */
    public function styles(Worksheet $sheet)
    {
        // Encabezados: fondo azul y texto blanco, centrado
        $sheet->getStyle('A1:N1')->applyFromArray([
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

        // Zebra striping (filas alternas)
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2']
                    ]
                ]);
            }
        }

        // Bordes para todas las celdas
        $sheet->getStyle("A1:N$highestRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Congelar la fila de encabezados
        $sheet->freezePane('A2');

        // Obtener la cantidad de filas de la hoja de ítems
        $highestRow = $sheet->getHighestRow();

        // Definir el rango de la lista de meses en la hoja 'Meses de Publicación'
        $mesesSheet = 'Meses de Publicación';
        $listaMeses = "'{$mesesSheet}'!\$A$2:\$A$100"; // Ajusta el rango si tienes más/menos meses

        // Aplica la validación de datos a la columna correspondiente (M = 13) para 'Mes de publicación'
        for (
            $row = 2; $row <= $highestRow; $row++
        ) {
            $validation = $sheet->getCell("M$row")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1($listaMeses);
            $validation->setErrorTitle('Valor inválido');
            $validation->setError('Por favor, selecciona un mes válido de la lista.');
            $validation->setPromptTitle('Selecciona un mes');
            $validation->setPrompt('Elige un mes de publicación de la lista.');
        }

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '"$ "#,##0', // Monto en CLP
            'E' => '"$ "#,##0', // Total/Item en CLP
        ];
    }

    public function title(): string
    {
        return 'Ítems de Compra';
    }
}

class ItemsPurchaseSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ItemPurchase::where('project_id', $this->projectId)
            ->with('project', 'statusItemPurchase', 'typePurchase', 'budgetAllocation', 'publicationMonth')
            ->orderBy('item_number', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Línea',
            'Producto o Servicio',
            'Cantidad',
            'Monto',
            'Total Ítem',
            'Cantidad OC',
            'Meses envio OC',
            'Dist. Regional',
            'Asignación Presupuestaria',
            'Cod. Gasto Presupuestario',
            'Tipo de Compra',
            'Cód. tipo compra',
            'Mes de publicación',
            'Comentario',
        ];
    }

    public function map($row): array
    {
        return [
            $row->item_number,
            $row->product_service,
            $row->quantity_item,
            $row->amount_item,
            $row->getTotalAmount(),
            $row->quantity_oc,
            $row->months_oc,
            $row->regional_distribution,
            $row->budgetAllocation->code . ' - ' . $row->budgetAllocation->description,
            $row->cod_budget_allocation_type,
            $row->typePurchase->name,
            $row->typePurchase->cod_purchase_type,
            $row->publication_date_formatted,
            $row->comment ?? '',
        ];
    }

    /**
     * Aplicar estilos a la hoja de Excel.
     */
    public function styles(Worksheet $sheet)
    {
        // Encabezados: fondo azul y texto blanco, centrado
        $sheet->getStyle('A1:N1')->applyFromArray([
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

        // Zebra striping (filas alternas)
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2']
                    ]
                ]);
            }
        }

        // Bordes para todas las celdas
        $sheet->getStyle("A1:N$highestRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Congelar la fila de encabezados
        $sheet->freezePane('A2');

        // Ajustar ancho de columnas específicas (igual que en la plantilla)
        $sheet->getColumnDimension('A')->setWidth(8);  // Línea
        $sheet->getColumnDimension('B')->setWidth(40); // Producto o Servicio
        $sheet->getColumnDimension('C')->setWidth(10); // Cantidad
        $sheet->getColumnDimension('D')->setWidth(12); // Monto
        $sheet->getColumnDimension('E')->setWidth(15); // Total Ítem
        $sheet->getColumnDimension('F')->setWidth(12); // Cantidad OC
        $sheet->getColumnDimension('G')->setWidth(15); // Meses envio OC
        $sheet->getColumnDimension('H')->setWidth(12); // Dist. Regional
        $sheet->getColumnDimension('I')->setWidth(35); // Asignación Presupuestaria
        $sheet->getColumnDimension('J')->setWidth(15); // Cod. Gasto Presupuestario
        $sheet->getColumnDimension('K')->setWidth(15); // Tipo de Compra
        $sheet->getColumnDimension('L')->setWidth(12); // Cód. tipo compra
        $sheet->getColumnDimension('M')->setWidth(15); // Mes de publicación
        $sheet->getColumnDimension('N')->setWidth(25); // Comentario

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '"$ "#,##0', // Monto en CLP
            'E' => '"$ "#,##0', // Total/Item en CLP
        ];
    }

    public function title(): string
    {
        return 'Ítems de Compra';
    }
}

class PublicationDatesSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping, WithTitle
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return \App\Models\PublicationMonth::orderBy('year', 'desc')
            ->orderBy('month_number', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Mes de Publicación',
        ];
    }

    public function map($row): array
    {
        return [
            $row->short_name . ' ' . $row->year,
        ];
    }

    /**
     * Aplicar estilos a la hoja de Excel.
     */
    public function styles(Worksheet $sheet)
    {
        // Encabezados: fondo verde y texto blanco, centrado
        $sheet->getStyle('A1:A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '28a745']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Zebra striping (filas alternas)
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:A{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2']
                    ]
                ]);
            }
        }

        // Bordes para todas las celdas
        $sheet->getStyle("A1:A$highestRow")->applyFromArray([
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
        return 'Meses de Publicación';
    }
}
