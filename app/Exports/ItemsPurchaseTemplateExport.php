<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use App\Models\BudgetAllocation;
use App\Models\TypePurchase;
use App\Models\PublicationMonth;

class ItemsPurchaseTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ItemsPurchaseTemplateSheet(),
            new BudgetAllocationsSheet(),
            new TypePurchasesSheet(),
            new PublicationMonthsSheet(),
        ];
    }

    public function array(): array
    {
        // Datos de ejemplo para la plantilla, todos los campos obligatorios
        return [
            [
                'Línea' => '1 (OBLIGATORIO)',
                'Producto o Servicio' => 'Ejemplo: Laptop HP ProBook 450 G8 (OBLIGATORIO)',
                'Cantidad' => '5 (OBLIGATORIO)',
                'Monto' => '500000 (OBLIGATORIO)',
                'Total/Item' => '2500000 (CALCULADO AUTOMÁTICAMENTE)',
                'Cantidad OC' => '2 (OBLIGATORIO)',
                'Meses envio OC' => 'Ene, Feb (OBLIGATORIO)',
                'Dist. Regional' => '15-1 (POR DEFECTO)',
                'Asignación Presupuestaria' => '123456 - Descripción (OBLIGATORIO)',
                'Cod. Gasto Presupuestario' => '123456 (OBLIGATORIO)',
                'Tipo de Compra' => 'Bienes (OBLIGATORIO)',
                'Mes de publicación' => 'Dic 2025 (OBLIGATORIO)',
                'Comentario' => 'Ejemplo de comentario (OPCIONAL)',
            ],
            [
                'Línea' => '2',
                'Producto o Servicio' => 'Ejemplo: Servicio de mantenimiento',
                'Cantidad' => '12',
                'Monto' => '25000',
                'Total/Item' => '300000',
                'Cantidad OC' => '1',
                'Meses envio OC' => 'Mar',
                'Dist. Regional' => '15-1',
                'Asignación Presupuestaria' => '789012 - Descripción',
                'Cod. Gasto Presupuestario' => '789012',
                'Tipo de Compra' => 'Servicios',
                'Mes de publicación' => 'Ene 2026',
                'Comentario' => 'Servicio anual (opcional)',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Línea',
            'Producto o Servicio',
            'Cantidad',
            'Monto',
            'Total/Item',
            'Cantidad OC',
            'Meses envio OC',
            'Dist. Regional',
            'Asignación Presupuestaria',
            'Cod. Gasto Presupuestario',
            'Tipo de Compra',
            'Mes de publicación',
            'Comentario',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados: fondo azul y texto blanco
        $sheet->getStyle('A1:M1')->applyFromArray([
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

        // Datos de ejemplo con fondo amarillo claro
        $sheet->getStyle('A2:M3')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC']
            ]
        ]);

        // Bordes para todas las celdas
        $sheet->getStyle('A1:M3')->applyFromArray([
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
}

class ItemsPurchaseTemplateSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        return [
            [
                'Línea' => '1 (OBLIGATORIO)',
                'Producto o Servicio' => 'Ejemplo: Laptop HP ProBook 450 G8 (OBLIGATORIO)',
                'Cantidad' => '5 (OBLIGATORIO)',
                'Monto' => '500000 (OBLIGATORIO)',
                'Total/Item' => '2500000 (CALCULADO AUTOMÁTICAMENTE)',
                'Cantidad OC' => '2 (OBLIGATORIO)',
                'Meses envio OC' => 'Ene, Feb (OBLIGATORIO)',
                'Dist. Regional' => '15-1 (POR DEFECTO)',
                'Asignación Presupuestaria' => '123456 - Descripción (OBLIGATORIO)',
                'Cod. Gasto Presupuestario' => '123456 (OBLIGATORIO)',
                'Tipo de Compra' => 'Bienes (OBLIGATORIO)',
                'Mes de publicación' => 'Dic 2025 (OBLIGATORIO)',
                'Comentario' => 'Ejemplo de comentario (OPCIONAL)',
            ],
            [
                'Línea' => '2',
                'Producto o Servicio' => 'Ejemplo: Servicio de mantenimiento',
                'Cantidad' => '12',
                'Monto' => '25000',
                'Total/Item' => '300000',
                'Cantidad OC' => '1',
                'Meses envio OC' => 'Mar',
                'Dist. Regional' => '15-1',
                'Asignación Presupuestaria' => '789012 - Descripción',
                'Cod. Gasto Presupuestario' => '789012',
                'Tipo de Compra' => 'Servicios',
                'Mes de publicación' => 'Ene 2026',
                'Comentario' => 'Servicio anual (opcional)',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Línea',
            'Producto o Servicio',
            'Cantidad',
            'Monto',
            'Total/Item',
            'Cantidad OC',
            'Meses envio OC',
            'Dist. Regional',
            'Asignación Presupuestaria',
            'Cod. Gasto Presupuestario',
            'Tipo de Compra',
            'Mes de publicación',
            'Comentario',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados: fondo azul y texto blanco
        $sheet->getStyle('A1:M1')->applyFromArray([
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

        // Datos de ejemplo con fondo amarillo claro
        $sheet->getStyle('A2:M3')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC']
            ]
        ]);

        // Bordes para todas las celdas
        $sheet->getStyle('A1:M3')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Congelar la fila de encabezados
        $sheet->freezePane('A2');

        // Agregar validación de datos con listas desplegables
        
        // Validación para Asignación Presupuestaria (columna I)
        $allocationValidation = $sheet->getCell('I2')->getDataValidation();
        $allocationValidation->setType(DataValidation::TYPE_LIST);
        $allocationValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $allocationValidation->setAllowBlank(false);
        $allocationValidation->setShowInputMessage(true);
        $allocationValidation->setShowErrorMessage(true);
        $allocationValidation->setShowDropDown(true);
        $allocationValidation->setErrorTitle('Error de entrada');
        $allocationValidation->setError('Debe seleccionar una asignación presupuestaria válida.');
        $allocationValidation->setPromptTitle('Asignación Presupuestaria');
        $allocationValidation->setPrompt('Seleccione una asignación presupuestaria de la lista.');
        $allocationValidation->setFormula1('\'Asignaciones Presupuestarias\'!$C$2:$C$100');
        
        // Aplicar validación a todo el rango de la columna I
        for ($row = 2; $row <= 100; $row++) {
            $sheet->getCell('I' . $row)->setDataValidation(clone $allocationValidation);
        }
        
        // Agregar fórmula para autocompletar Cod. Gasto Presupuestario basado en Asignación Presupuestaria
        // La fórmula busca en la hoja Asignaciones Presupuestarias el cod_budget_allocation_type
        // basado en el valor seleccionado en la columna I (Asignación Presupuestaria)
        // Optimizado para reducir uso de memoria - solo primeras 100 filas
        for ($row = 2; $row <= 100; $row++) {
            // INDEX y MATCH para buscar en columna C y retornar valor de columna A
            // MATCH busca la posición en columna C, INDEX retorna el valor de columna A en esa posición
            $formula = "=IF(I{$row}<>\"\",INDEX('Asignaciones Presupuestarias'!A:A,MATCH(I{$row},'Asignaciones Presupuestarias'!C:C,0)),\"\")";
            $sheet->getCell('J' . $row)->setValue($formula);
        }
        
        // Validación para Cod. Gasto Presupuestario (columna J - antes era I)
        $budgetValidation = $sheet->getCell('J2')->getDataValidation();
        $budgetValidation->setType(DataValidation::TYPE_LIST);
        $budgetValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $budgetValidation->setAllowBlank(false);
        $budgetValidation->setShowInputMessage(true);
        $budgetValidation->setShowErrorMessage(true);
        $budgetValidation->setShowDropDown(true);
        $budgetValidation->setErrorTitle('Error de entrada');
        $budgetValidation->setError('Debe seleccionar un código de gasto presupuestario válido.');
        $budgetValidation->setPromptTitle('Código de Gasto Presupuestario');
        $budgetValidation->setPrompt('Seleccione un código de gasto presupuestario de la lista.');
        $budgetValidation->setFormula1('\'Asignaciones Presupuestarias\'!$B$2:$B$100');
        
        // Aplicar validación a todo el rango de la columna J (optimizado)
        for ($row = 2; $row <= 100; $row++) {
            $sheet->getCell('J' . $row)->setDataValidation(clone $budgetValidation);
        }

        // Validación para Tipo de Compra (columna K - antes era J)
        $typeValidation = $sheet->getCell('K2')->getDataValidation();
        $typeValidation->setType(DataValidation::TYPE_LIST);
        $typeValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $typeValidation->setAllowBlank(false);
        $typeValidation->setShowInputMessage(true);
        $typeValidation->setShowErrorMessage(true);
        $typeValidation->setShowDropDown(true);
        $typeValidation->setErrorTitle('Error de entrada');
        $typeValidation->setError('Debe seleccionar un tipo de compra válido.');
        $typeValidation->setPromptTitle('Tipo de Compra');
        $typeValidation->setPrompt('Seleccione un tipo de compra de la lista.');
        $typeValidation->setFormula1('\'Tipos de Compra\'!$A$2:$A$100');
        
        // Aplicar validación a todo el rango de la columna K (optimizado)
        for ($row = 2; $row <= 100; $row++) {
            $sheet->getCell('K' . $row)->setDataValidation(clone $typeValidation);
        }

        // Validación para Mes de publicación (columna L - antes era K)
        $monthValidation = $sheet->getCell('L2')->getDataValidation();
        $monthValidation->setType(DataValidation::TYPE_LIST);
        $monthValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $monthValidation->setAllowBlank(false);
        $monthValidation->setShowInputMessage(true);
        $monthValidation->setShowErrorMessage(true);
        $monthValidation->setShowDropDown(true);
        $monthValidation->setErrorTitle('Error de entrada');
        $monthValidation->setError('Debe seleccionar un mes de publicación válido.');
        $monthValidation->setPromptTitle('Mes de Publicación');
        $monthValidation->setPrompt('Seleccione un mes de publicación de la lista.');
        $monthValidation->setFormula1('\'Meses de Publicación\'!$A$2:$A$100');
        
        // Aplicar validación a todo el rango de la columna L (optimizado)
        for ($row = 2; $row <= 100; $row++) {
            $sheet->getCell('L' . $row)->setDataValidation(clone $monthValidation);
        }

        return [];
    }

    public function title(): string
    {
        return 'Plantilla Ítems de Compra';
    }
}

class BudgetAllocationsSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $allocations = BudgetAllocation::select('cod_budget_allocation_type', 'code', 'description')->get();
        
        return $allocations->map(function($allocation) {
            return [
                'cod_budget_allocation_type' => $allocation->cod_budget_allocation_type,
                'code' => $allocation->code,
                'Formato para importar' => $allocation->code . ' - ' . $allocation->description,
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'cod_budget_allocation_type',
            'code',
            'Formato para importar',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Encabezados: fondo verde y texto blanco
        $sheet->getStyle('A1:C1')->applyFromArray([
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

        // Bordes para todas las celdas
        $sheet->getStyle("A1:C$highestRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Asignaciones Presupuestarias';
    }
}

class TypePurchasesSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $types = TypePurchase::select('name', 'cod_purchase_type')->get();
        
        return $types->map(function($type) {
            return [
                'Nombre' => $type->name,
                'Código' => $type->cod_purchase_type,
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Código',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Encabezados: fondo naranja y texto blanco
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'fd7e14']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Bordes para todas las celdas
        $sheet->getStyle("A1:B$highestRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Tipos de Compra';
    }
}

class PublicationMonthsSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $months = PublicationMonth::orderBy('year', 'desc')
            ->orderBy('month_number', 'desc')
            ->get();
        
        return $months->map(function($month) {
            return [
                'Mes de Publicación' => $month->formatted_date,
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Mes de Publicación',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Encabezados: fondo verde y texto blanco
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

        // Bordes para todas las celdas
        $sheet->getStyle("A1:A$highestRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Meses de Publicación';
    }
} 