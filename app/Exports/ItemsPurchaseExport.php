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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ItemsPurchaseExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping, WithColumnFormatting, WithTitle
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
            ->with('project', 'statusItemPurchase', 'typePurchase', 'budgetAllocation')
            ->get();
    }

    public function headings(): array
    {
        return [
            'N°',
            'Producto o Servicio',
            'Cantidad/Item',
            'Monto',
            'Total/Item',
            'Cant./OC',
            'Meses OC',
            'Dist. Regional',
            'Asignación Presupuestaria',
            'Cod.Gasto',
            'Tipo Compra',
            'Cod.Tipo Compra',
            'Estado',
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
            $row->statusItemPurchase->name,
        ];
    }

    /**
     * Aplicar estilos a la hoja de Excel.
     */
    public function styles(Worksheet $sheet)
    {
        // Encabezados: fondo azul y texto blanco, centrado
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD']
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
                $sheet->getStyle("A{$row}:M{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2']
                    ]
                ]);
            }
        }

        // Bordes para todas las celdas
        $sheet->getStyle("A1:M$highestRow")->applyFromArray([
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

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Monto
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total/Item
        ];
    }

    public function title(): string
    {
        return 'Ítems de Compra';
    }
}
