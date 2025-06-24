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
                'Cantidad OC' => '2 (OBLIGATORIO)',
                'Meses envio OC' => 'Ene, Feb (OBLIGATORIO)',
                'Dist. Regional' => 'Región Metropolitana (OBLIGATORIO)',
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
                'Cantidad OC' => '1',
                'Meses envio OC' => 'Mar',
                'Dist. Regional' => 'Valparaíso',
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

        // Datos de ejemplo con fondo amarillo claro
        $sheet->getStyle('A2:K3')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC']
            ]
        ]);

        // Bordes para todas las celdas
        $sheet->getStyle('A1:K3')->applyFromArray([
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
                'Cantidad OC' => '2 (OBLIGATORIO)',
                'Meses envio OC' => 'Ene, Feb (OBLIGATORIO)',
                'Dist. Regional' => 'Región Metropolitana (OBLIGATORIO)',
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
                'Cantidad OC' => '1',
                'Meses envio OC' => 'Mar',
                'Dist. Regional' => 'Valparaíso',
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

        // Datos de ejemplo con fondo amarillo claro
        $sheet->getStyle('A2:K3')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC']
            ]
        ]);

        // Bordes para todas las celdas
        $sheet->getStyle('A1:K3')->applyFromArray([
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
        
        // Validación para Cod. Gasto Presupuestario (columna H)
        $budgetValidation = $sheet->getCell('H2')->getDataValidation();
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
        $budgetValidation->setFormula1('\'Asignaciones Presupuestarias\'!$A$2:$A$1000');
        
        // Aplicar validación a todo el rango de la columna H
        for ($row = 2; $row <= 1000; $row++) {
            $sheet->getCell('H' . $row)->setDataValidation(clone $budgetValidation);
        }

        // Validación para Tipo de Compra (columna I)
        $typeValidation = $sheet->getCell('I2')->getDataValidation();
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
        $typeValidation->setFormula1('\'Tipos de Compra\'!$A$2:$A$1000');
        
        // Aplicar validación a todo el rango de la columna I
        for ($row = 2; $row <= 1000; $row++) {
            $sheet->getCell('I' . $row)->setDataValidation(clone $typeValidation);
        }

        // Validación para Mes de publicación (columna J)
        $monthValidation = $sheet->getCell('J2')->getDataValidation();
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
        $monthValidation->setFormula1('\'Meses de Publicación\'!$A$2:$A$1000');
        
        // Aplicar validación a todo el rango de la columna J
        for ($row = 2; $row <= 1000; $row++) {
            $sheet->getCell('J' . $row)->setDataValidation(clone $monthValidation);
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
        $allocations = BudgetAllocation::select('code', 'description')->get();
        
        return $allocations->map(function($allocation) {
            return [
                'Código' => $allocation->code,
                'Descripción' => $allocation->description,
                'Formato para importar' => $allocation->code . ' - ' . $allocation->description,
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Código',
            'Descripción',
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