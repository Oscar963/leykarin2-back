<?php

namespace App\Console\Commands;

use App\Models\PublicationMonth;
use App\Models\TypePurchase;
use App\Models\BudgetAllocation;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class GenerateItemsPurchaseExample extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:items-purchase-example {--output=items_purchase_example.xlsx : Nombre del archivo de salida}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un archivo Excel de ejemplo para la importación de items de compra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Generando archivo Excel de ejemplo para importación de items de compra ===');

        // Crear nuevo spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Ítems de Compra');

        // Obtener datos de referencia
        $publicationMonths = PublicationMonth::orderBy('year', 'desc')->orderBy('month_number')->take(10)->get();
        $typePurchases = TypePurchase::take(5)->get();
        $budgetAllocations = BudgetAllocation::take(5)->get();

        // Definir encabezados
        $headers = [
            'A1' => 'Línea',
            'B1' => 'Producto o Servicio',
            'C1' => 'Cantidad',
            'D1' => 'Monto',
            'E1' => 'Total/Item',
            'F1' => 'Cantidad OC',
            'G1' => 'Meses envio OC',
            'H1' => 'Dist. Regional',
            'I1' => 'Asignación Presupuestaria',
            'J1' => 'Cod. Gasto Presupuestario',
            'K1' => 'Tipo de Compra',
            'L1' => 'Mes de publicación',
            'M1' => 'Comentario (opcional)',
        ];

        // Escribir encabezados
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Estilo para encabezados
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '06048c'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        // Datos de ejemplo
        $exampleData = [
            [
                'linea' => 1,
                'producto_o_servicio' => 'Servicio de mantenimiento de equipos informáticos',
                'cantidad' => 12,
                'monto' => 25000,
                'total_item' => 300000,
                'cantidad_oc' => 1,
                'meses_envio_oc' => 'Mar',
                'dist_regional' => '15-1',
                'asignacion_presupuestaria' => $budgetAllocations->first()->code . ' - ' . $budgetAllocations->first()->description,
                'cod_gasto_presupuestario' => $budgetAllocations->first()->code,
                'tipo_de_compra' => $typePurchases->first()->name,
                'mes_de_publicacion' => $publicationMonths->first()->formatted_date,
                'comentario' => 'Servicio anual de mantenimiento preventivo'
            ],
            [
                'linea' => 2,
                'producto_o_servicio' => 'Suministros de oficina',
                'cantidad' => 50,
                'monto' => 1500,
                'total_item' => 75000,
                'cantidad_oc' => 2,
                'meses_envio_oc' => 'Abr',
                'dist_regional' => '15-1',
                'asignacion_presupuestaria' => $budgetAllocations->skip(1)->first()->code . ' - ' . $budgetAllocations->skip(1)->first()->description,
                'cod_gasto_presupuestario' => $budgetAllocations->skip(1)->first()->code,
                'tipo_de_compra' => $typePurchases->skip(1)->first()->name,
                'mes_de_publicacion' => $publicationMonths->skip(1)->first()->formatted_date,
                'comentario' => 'Materiales de oficina para el año'
            ],
            [
                'linea' => 3,
                'producto_o_servicio' => 'Capacitación en gestión de proyectos',
                'cantidad' => 1,
                'monto' => 500000,
                'total_item' => 500000,
                'cantidad_oc' => 1,
                'meses_envio_oc' => 'May',
                'dist_regional' => '15-1',
                'asignacion_presupuestaria' => $budgetAllocations->skip(2)->first()->code . ' - ' . $budgetAllocations->skip(2)->first()->description,
                'cod_gasto_presupuestario' => $budgetAllocations->skip(2)->first()->code,
                'tipo_de_compra' => $typePurchases->skip(2)->first()->name,
                'mes_de_publicacion' => $publicationMonths->skip(2)->first()->formatted_date,
                'comentario' => 'Capacitación para el equipo de trabajo'
            ]
        ];

        // Escribir datos de ejemplo
        $row = 2;
        foreach ($exampleData as $data) {
            $sheet->setCellValue('A' . $row, $data['linea']);
            $sheet->setCellValue('B' . $row, $data['producto_o_servicio']);
            $sheet->setCellValue('C' . $row, $data['cantidad']);
            $sheet->setCellValue('D' . $row, $data['monto']);
            $sheet->setCellValue('E' . $row, $data['total_item']);
            $sheet->setCellValue('F' . $row, $data['cantidad_oc']);
            $sheet->setCellValue('G' . $row, $data['meses_envio_oc']);
            $sheet->setCellValue('H' . $row, $data['dist_regional']);
            $sheet->setCellValue('I' . $row, $data['asignacion_presupuestaria']);
            $sheet->setCellValue('J' . $row, $data['cod_gasto_presupuestario']);
            $sheet->setCellValue('K' . $row, $data['tipo_de_compra']);
            $sheet->setCellValue('L' . $row, $data['mes_de_publicacion']);
            $sheet->setCellValue('M' . $row, $data['comentario']);
            $row++;
        }

        // Crear hoja de referencia para meses de publicación
        $monthSheet = $spreadsheet->createSheet();
        $monthSheet->setTitle('Meses de Publicación');
        $monthSheet->setCellValue('A1', 'Mes de Publicación');
        $monthSheet->getStyle('A1')->applyFromArray($headerStyle);

        $monthRow = 2;
        foreach ($publicationMonths as $month) {
            $monthSheet->setCellValue('A' . $monthRow, $month->formatted_date);
            $monthRow++;
        }

        // Crear hoja de referencia para tipos de compra
        $typeSheet = $spreadsheet->createSheet();
        $typeSheet->setTitle('Tipos de Compra');
        $typeSheet->setCellValue('A1', 'Tipo de Compra');
        $typeSheet->setCellValue('B1', 'Código');
        $typeSheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $typeRow = 2;
        foreach ($typePurchases as $type) {
            $typeSheet->setCellValue('A' . $typeRow, $type->name);
            $typeSheet->setCellValue('B' . $typeRow, $type->cod_purchase_type);
            $typeRow++;
        }

        // Crear hoja de referencia para asignaciones presupuestarias
        $allocationSheet = $spreadsheet->createSheet();
        $allocationSheet->setTitle('Asignaciones Presupuestarias');
        $allocationSheet->setCellValue('A1', 'Código');
        $allocationSheet->setCellValue('B1', 'Descripción');
        $allocationSheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $allocationRow = 2;
        foreach ($budgetAllocations as $allocation) {
            $allocationSheet->setCellValue('A' . $allocationRow, $allocation->code);
            $allocationSheet->setCellValue('B' . $allocationRow, $allocation->description);
            $allocationRow++;
        }

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(35);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(25);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(30);

        // Guardar archivo
        $outputFile = $this->option('output');
        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFile);

        $this->info("✓ Archivo generado: {$outputFile}");
        $this->info('');
        $this->info('=== Información del archivo ===');
        $this->info('Hoja 1: Plantilla Ítems de Compra - Datos principales');
        $this->info('Hoja 2: Meses de Publicación - Referencia de meses disponibles');
        $this->info('Hoja 3: Tipos de Compra - Referencia de tipos disponibles');
        $this->info('Hoja 4: Asignaciones Presupuestarias - Referencia de códigos disponibles');
        $this->info('');
        $this->info('=== Campos obligatorios ===');
        $this->info('- Línea: Número de línea del item');
        $this->info('- Producto o Servicio: Descripción del producto o servicio');
        $this->info('- Cantidad: Cantidad de unidades');
        $this->info('- Monto: Precio unitario');
        $this->info('- Total/Item: Total del item');
        $this->info('- Cantidad OC: Cantidad de órdenes de compra');
        $this->info('- Meses envio OC: Meses para envío de OC');
        $this->info('- Dist. Regional: Distribución regional');
        $this->info('- Cod. Gasto Presupuestario: Código de la asignación presupuestaria');
        $this->info('- Tipo de Compra: Tipo de procedimiento de compra');
        $this->info('- Mes de publicación: Mes en que se publicará');
        $this->info('');
        $this->info('=== Campos opcionales ===');
        $this->info('- Comentario: Observaciones adicionales');
        $this->info('');
        $this->info('=== Notas importantes ===');
        $this->info('1. Los valores en las hojas de referencia deben coincidir exactamente');
        $this->info('2. Los meses de publicación deben estar en formato "Ene 2026"');
        $this->info('3. Los códigos de gasto presupuestario deben existir en el sistema');
        $this->info('4. Los tipos de compra deben coincidir con los nombres del sistema');

        return 0;
    }
} 