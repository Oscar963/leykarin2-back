<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\BudgetAllocation;
use App\Models\TypePurchase;
use App\Models\PublicationMonth;

class ItemsPurchaseTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ItemsPurchaseTemplateSheet(),
            new BudgetAllocationsReferenceSheet(),
            new TypePurchasesReferenceSheet(),
            new PublicationMonthsReferenceSheet(),
        ];
    }
}

class ItemsPurchaseTemplateSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    public function array(): array
    {
        // Solo 2 filas de ejemplo para mantener la plantilla simple y rápida
        return [
            [
                1,
                'Laptop HP ProBook 450 G8',
                5,
                850000,
                4250000,
                2,
                'Enero, Febrero',
                '15-1',
                '29.03.002 - ADQUISICION DE EQUIPOS INFORMATICOS',
                '29.03.002',
                'Bienes',
                'B',
                'Dic 2025',
                'Equipos para oficina'
            ],
            [
                2,
                'Servicio de mantenimiento de equipos',
                12,
                25000,
                300000,
                1,
                'Marzo',
                '15-1',
                '29.02.004 - SERVICIOS DE MANTENIMIENTO',
                '29.02.004',
                'Servicios',
                'S',
                'Ene 2026',
                'Servicio anual'
            ]
        ];
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
            'Comentario'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $this->addDataValidation($event->sheet->getDelegate());
            },
        ];
    }

    private function addDataValidation(Worksheet $sheet)
    {
        // Definir el rango de la lista de asignaciones presupuestarias en la hoja de referencia
        $asignacionesSheet = 'Asignaciones Presupuestarias';
        $listaAsignaciones = "'{$asignacionesSheet}'!\$D\$2:\$D\$100"; // Columna D de la hoja de referencia (Formato para importar)

        // Definir el rango de la lista de tipos de compra en la hoja de referencia
        $tiposCompraSheet = 'Tipos de Compra';
        $listaTiposCompra = "'{$tiposCompraSheet}'!\$A\$2:\$A\$100"; // Columna A de la hoja de referencia (nombre)

        // Definir el rango de la lista de meses de publicación en la hoja de referencia
        $mesesPublicacionSheet = 'Meses de Publicación';
        $listaMesesPublicacion = "'{$mesesPublicacionSheet}'!\$A\$2:\$A\$100"; // Columna A de la hoja de referencia

        // Obtener el primer valor de cada lista para establecer como valor por defecto
        $firstAsignacion = "='{$asignacionesSheet}'!D2";
        $firstTipoCompra = "='{$tiposCompraSheet}'!A2";

        // Aplicar validación de datos a las columnas I, K y L desde la fila 2 hasta 100
        for ($row = 2; $row <= 100; $row++) {
            // Validación para columna I (Asignación Presupuestaria)
            $validation = $sheet->getCell("I$row")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1($listaAsignaciones);
            $validation->setErrorTitle('Error de validación');
            $validation->setError('Por favor seleccione una asignación presupuestaria válida de la lista.');
            $validation->setPromptTitle('Asignación Presupuestaria');
            $validation->setPrompt('Seleccione una asignación presupuestaria de la lista desplegable.');
            
            // Establecer el primer valor como valor por defecto solo en las primeras 2 filas de datos
            if ($row <= 3) {
                $sheet->getCell("I$row")->setValue($firstAsignacion);
            }
            
            // Fórmula para Cod. Gasto Presupuestario (columna J)
            $formulaIngles = "=IF(ISNA(MATCH(I{$row},'{$asignacionesSheet}'!\$D\$2:\$D\$100,0)),\"\",INDEX('{$asignacionesSheet}'!\$A\$2:\$A\$100,MATCH(I{$row},'{$asignacionesSheet}'!\$D\$2:\$D\$100,0)))";
            $sheet->getCell("J{$row}")->setValue($formulaIngles);

            // Validación para columna K (Tipo de Compra)
            $validationTipo = $sheet->getCell("K$row")->getDataValidation();
            $validationTipo->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validationTipo->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validationTipo->setAllowBlank(false);
            $validationTipo->setShowInputMessage(true);
            $validationTipo->setShowErrorMessage(true);
            $validationTipo->setShowDropDown(true);
            $validationTipo->setFormula1($listaTiposCompra);
            $validationTipo->setErrorTitle('Error de validación');
            $validationTipo->setError('Por favor seleccione un tipo de compra válido de la lista.');
            $validationTipo->setPromptTitle('Tipo de Compra');
            $validationTipo->setPrompt('Seleccione un tipo de compra de la lista desplegable.');
            
            // Establecer el primer valor como valor por defecto solo en las primeras 2 filas de datos
            if ($row <= 3) {
                $sheet->getCell("K$row")->setValue($firstTipoCompra);
            }

            // Fórmula para Cód. tipo compra (columna L)
            $formulaTipoCompra = "=IF(ISNA(MATCH(K{$row},'{$tiposCompraSheet}'!\$A\$2:\$A\$100,0)),\"\",INDEX('{$tiposCompraSheet}'!\$B\$2:\$B\$100,MATCH(K{$row},'{$tiposCompraSheet}'!\$A\$2:\$A\$100,0)))";
            $sheet->getCell("L{$row}")->setValue($formulaTipoCompra);

            // Validación para columna M (Mes de publicación)
            $validationMes = $sheet->getCell("M$row")->getDataValidation();
            $validationMes->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validationMes->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validationMes->setAllowBlank(false);
            $validationMes->setShowInputMessage(true);
            $validationMes->setShowErrorMessage(true);
            $validationMes->setShowDropDown(true);
            $validationMes->setFormula1($listaMesesPublicacion);
            $validationMes->setErrorTitle('Error de validación');
            $validationMes->setError('Por favor seleccione un mes de publicación válido de la lista.');
            $validationMes->setPromptTitle('Mes de publicación');
            $validationMes->setPrompt('Seleccione un mes de publicación de la lista desplegable.');
        }
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados: fondo azul y texto blanco
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

        // Datos de ejemplo con fondo amarillo claro
        $sheet->getStyle('A2:N3')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC']
            ]
        ]);

        // Bordes para las celdas con datos
        $sheet->getStyle('A1:N3')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Congelar la fila de encabezados
        $sheet->freezePane('A2');

        // Ajustar ancho de columnas específicas
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

    public function title(): string
    {
        return 'Plantilla Ítems de Compra';
    }
}

class BudgetAllocationsReferenceSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $allocations = BudgetAllocation::select('cod_budget_allocation_type', 'code', 'description')
            ->orderBy('code')
            ->get();
        
        return $allocations->map(function($allocation) {
            return [
                $allocation->cod_budget_allocation_type,
                $allocation->code,
                $allocation->description,
                $allocation->code . ' - ' . $allocation->description
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Cod. Gasto Presupuestario',
            'Código',
            'Descripción',
            'Formato para importar'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Encabezados
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '28a745']
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Asignaciones Presupuestarias';
    }
}

class TypePurchasesReferenceSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $types = TypePurchase::select('name', 'cod_purchase_type')
            ->orderBy('name')
            ->get();
        
        return $types->map(function($type) {
            return [
                $type->name,
                $type->cod_purchase_type
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Código'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'fd7e14']
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Tipos de Compra';
    }
}

class PublicationMonthsReferenceSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $months = PublicationMonth::orderBy('year', 'desc')
            ->orderBy('month_number', 'asc')
            ->get();
        
        return $months->map(function($month) {
            return [
                $month->formatted_date
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Mes de Publicación'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados
        $sheet->getStyle('A1:A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '17a2b8']
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Meses de Publicación';
    }
} 