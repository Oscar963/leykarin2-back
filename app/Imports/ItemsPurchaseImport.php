<?php

namespace App\Imports;

use App\Models\ItemPurchase;
use App\Models\BudgetAllocation;
use App\Models\TypePurchase;
use App\Models\PublicationMonth;
use App\Models\StatusItemPurchase;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class ItemsPurchaseImport implements WithMultipleSheets
{
    protected $projectId;
    protected $sheetImport;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->sheetImport = new ItemsPurchaseSheetImport($projectId);
    }

    public function sheets(): array
    {
        return [
            'Plantilla Ítems de Compra' => $this->sheetImport,
        ];
    }

    public function getErrors()
    {
        return $this->sheetImport->getErrors();
    }

    public function getImportStats()
    {
        return $this->sheetImport->getImportStats();
    }
}

class ItemsPurchaseSheetImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    WithBatchInserts, 
    WithChunkReading, 
    WithCalculatedFormulas,
    SkipsOnError,
    SkipsEmptyRows,
    WithStartRow
{
    use SkipsErrors;
    
    protected $projectId;
    protected $errors = [];
    protected $importedCount = 0;
    protected $skippedCount = 0;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * Mapear las columnas del Excel a nombres de campos normalizados
     */
    protected function mapRowKeys(array $row): array
    {
        $mapping = [
            'linea' => 'linea',
            'línea' => 'linea',
            'producto_o_servicio' => 'producto_o_servicio',
            'producto o servicio' => 'producto_o_servicio',
            'cantidad' => 'cantidad',
            'monto' => 'monto',
            'cantidad_oc' => 'cantidad_oc',
            'cantidad oc' => 'cantidad_oc',
            'meses_envio_oc' => 'meses_envio_oc',
            'meses envio oc' => 'meses_envio_oc',
            'dist_regional' => 'dist_regional',
            'dist. regional' => 'dist_regional',
            'cod_gasto_presupuestario' => 'cod_gasto_presupuestario',
            'cod. gasto presupuestario' => 'cod_gasto_presupuestario',
            'tipo_de_compra' => 'tipo_de_compra',
            'tipo de compra' => 'tipo_de_compra',
            'mes_de_publicacion' => 'mes_de_publicacion',
            'mes de publicación' => 'mes_de_publicacion',
            'mes de publicacion' => 'mes_de_publicacion',
            'comentario' => 'comentario',
        ];

        $mappedRow = [];
        foreach ($row as $key => $value) {
            $normalizedKey = mb_strtolower(trim($key));
            $mappedKey = $mapping[$normalizedKey] ?? $normalizedKey;
            $mappedRow[$mappedKey] = $value;
        }

        return $mappedRow;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Mapear las claves del array para normalizar los nombres
        $row = $this->mapRowKeys($row);
        
        // Log para debugging
        Log::info('Fila procesada:', $row);
        
        // Verificar si estamos leyendo la hoja correcta
        // Si la fila contiene campos como 'codigo', 'descripcion', 'formato_para_importar'
        // significa que estamos leyendo la hoja de asignaciones presupuestarias
        if (isset($row['codigo']) || isset($row['descripcion']) || isset($row['formato_para_importar'])) {
            Log::warning('Se está leyendo la hoja incorrecta. Esta fila parece ser de asignaciones presupuestarias:', $row);
            return null; // Ignorar esta fila
        }
        
        // Verificar si la fila tiene al menos un dato (no está completamente vacía)
        $hasData = false;
        $allFields = [
            'linea',
            'producto_o_servicio',
            'cantidad',
            'monto',
            'cantidad_oc',
            'meses_envio_oc',
            'dist_regional',
            'cod_gasto_presupuestario',
            'tipo_de_compra',
            'mes_de_publicacion',
            'comentario',
        ];
        
        foreach ($allFields as $field) {
            if (isset($row[$field]) && trim($row[$field]) !== '') {
                $hasData = true;
                break;
            }
        }
        
        // Si la fila está completamente vacía, ignorarla silenciosamente
        if (!$hasData) {
            return null;
        }
        
        // Si la fila tiene al menos un dato, validar que todos los campos obligatorios estén presentes
        $requiredFields = [
            'linea',
            'producto_o_servicio',
            'cantidad',
            'monto',
            'cantidad_oc',
            'meses_envio_oc',
            'dist_regional',
            'cod_gasto_presupuestario',
            'tipo_de_compra',
            'mes_de_publicacion',
            // 'comentario' - Este campo es opcional
        ];
        
        foreach ($requiredFields as $field) {
            if (!isset($row[$field]) || trim($row[$field]) === '') {
                $this->errors[] = [
                    'row' => $this->importedCount + $this->skippedCount + 1,
                    'error' => "El campo '$field' es obligatorio y no está presente o está vacío.",
                    'data' => $row
                ];
                $this->skippedCount++;
                return null;
            }
        }

        try {
            // Mapear las relaciones
            $budgetAllocationId = $this->getBudgetAllocationId($row['cod_gasto_presupuestario'] ?? '');
            $typePurchaseId = $this->getTypePurchaseId($row['tipo_de_compra'] ?? '');
            $publicationMonthId = $this->getPublicationMonthId($row['mes_de_publicacion'] ?? '');
            $statusItemPurchaseId = $this->getDefaultStatusId();

            // Log de debugging para las relaciones
            Log::info('Relaciones mapeadas:', [
                'budget_allocation_id' => $budgetAllocationId,
                'type_purchase_id' => $typePurchaseId,
                'publication_month_id' => $publicationMonthId,
                'status_item_purchase_id' => $statusItemPurchaseId,
            ]);

            // Crear el modelo
            $itemPurchase = new ItemPurchase([
                'item_number' => $this->parseInteger($row['linea'] ?? 0),
                'product_service' => $row['producto_o_servicio'] ?? '',
                'quantity_item' => $this->parseInteger($row['cantidad'] ?? 0),
                'amount_item' => $this->parseAmount($row['monto'] ?? 0),
                'quantity_oc' => $this->parseInteger($row['cantidad_oc'] ?? 0),
                'months_oc' => $row['meses_envio_oc'] ?? '',
                'regional_distribution' => $row['dist_regional'] ?? '',
                'cod_budget_allocation_type' => $row['cod_gasto_presupuestario'] ?? '',
                'comment' => $row['comentario'] ?? '',
                // Relaciones
                'project_id' => $this->projectId,
                'budget_allocation_id' => $budgetAllocationId,
                'type_purchase_id' => $typePurchaseId,
                'status_item_purchase_id' => $statusItemPurchaseId,
                'publication_month_id' => $publicationMonthId,
                // Usuario que importa
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            Log::info('ItemPurchase creado:', $itemPurchase->toArray());

            $this->importedCount++;
            return $itemPurchase;

        } catch (Exception $e) {
            $this->errors[] = [
                'row' => $this->importedCount + $this->skippedCount + 1,
                'error' => $e->getMessage(),
                'data' => $row
            ];
            $this->skippedCount++;
            Log::error('Error importing item purchase row: ' . $e->getMessage(), $row);
            return null;
        }
    }

    /**
     * Obtener ID de asignación presupuestaria por código
     */
    protected function getBudgetAllocationId($code)
    {
        if (empty($code)) {
            Log::warning('Código de asignación presupuestaria vacío');
            return null;
        }

        // Buscar por código exacto
        $budgetAllocation = BudgetAllocation::where('code', trim($code))->first();
        
        if ($budgetAllocation) {
            Log::info("Asignación presupuestaria encontrada por código exacto: {$code} -> ID {$budgetAllocation->id}");
            return $budgetAllocation->id;
        }

        // Si no encuentra, buscar por descripción que contenga el código
        $budgetAllocation = BudgetAllocation::where('description', 'like', '%' . trim($code) . '%')->first();
        
        if ($budgetAllocation) {
            Log::info("Asignación presupuestaria encontrada por descripción: {$code} -> ID {$budgetAllocation->id}");
            return $budgetAllocation->id;
        }

        // Si no encuentra, buscar en el formato "código - descripción"
        if (strpos($code, ' - ') !== false) {
            $parts = explode(' - ', $code);
            $budgetAllocation = BudgetAllocation::where('code', trim($parts[0]))->first();
            
            if ($budgetAllocation) {
                Log::info("Asignación presupuestaria encontrada por formato código-descripción: {$code} -> ID {$budgetAllocation->id}");
                return $budgetAllocation->id;
            }
        }

        // Si no encuentra nada, usar la primera disponible como fallback
        $fallbackAllocation = BudgetAllocation::first();
        if ($fallbackAllocation) {
            Log::warning("No se encontró asignación presupuestaria para '{$code}', usando fallback ID {$fallbackAllocation->id}");
            return $fallbackAllocation->id;
        }

        Log::error("No hay asignaciones presupuestarias disponibles en el sistema");
        return null;
    }

    /**
     * Obtener ID de tipo de compra por nombre
     */
    protected function getTypePurchaseId($name)
    {
        if (empty($name)) {
            Log::warning('Nombre de tipo de compra vacío');
            return null;
        }

        $typePurchase = TypePurchase::where('name', 'like', '%' . trim($name) . '%')
            ->orWhere('cod_purchase_type', trim($name))
            ->first();

        if ($typePurchase) {
            Log::info("Tipo de compra encontrado: {$name} -> ID {$typePurchase->id}");
            return $typePurchase->id;
        }

        // Fallback: usar el primer tipo disponible
        $fallbackType = TypePurchase::first();
        if ($fallbackType) {
            Log::warning("No se encontró tipo de compra para '{$name}', usando fallback ID {$fallbackType->id}");
            return $fallbackType->id;
        }

        Log::error("No hay tipos de compra disponibles en el sistema");
        return null;
    }

    /**
     * Obtener ID de mes de publicación
     */
    protected function getPublicationMonthId($monthYear)
    {
        if (empty($monthYear)) {
            Log::warning('Mes de publicación vacío');
            return null;
        }

        // Buscar por formato "Dic 2025"
        $publicationMonth = PublicationMonth::where('formatted_date', trim($monthYear))->first();
        
        if ($publicationMonth) {
            Log::info("Mes de publicación encontrado por formato: {$monthYear} -> ID {$publicationMonth->id}");
            return $publicationMonth->id;
        }

        // Buscar por nombre corto y año
        $parts = explode(' ', trim($monthYear));
        if (count($parts) >= 2) {
            $shortName = $parts[0];
            $year = $parts[1];
            
            $publicationMonth = PublicationMonth::where('short_name', $shortName)
                ->where('year', $year)
                ->first();
                
            if ($publicationMonth) {
                Log::info("Mes de publicación encontrado por partes: {$monthYear} -> ID {$publicationMonth->id}");
                return $publicationMonth->id;
            }
        }

        // Fallback: usar el primer mes disponible
        $fallbackMonth = PublicationMonth::first();
        if ($fallbackMonth) {
            Log::warning("No se encontró mes de publicación para '{$monthYear}', usando fallback ID {$fallbackMonth->id}");
            return $fallbackMonth->id;
        }

        Log::error("No hay meses de publicación disponibles en el sistema");
        return null;
    }

    /**
     * Obtener ID del estado por defecto
     */
    protected function getDefaultStatusId()
    {
        $defaultStatus = StatusItemPurchase::where('name', 'like', '%pendiente%')
            ->orWhere('name', 'like', '%borrador%')
            ->orWhere('name', 'like', '%draft%')
            ->first();

        return $defaultStatus ? $defaultStatus->id : StatusItemPurchase::first()->id;
    }

    /**
     * Parsear entero
     */
    protected function parseInteger($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        // Remover caracteres no numéricos
        $cleanValue = preg_replace('/[^0-9]/', '', $value);
        return $cleanValue ? (int) $cleanValue : 0;
    }

    /**
     * Parsear monto (remover símbolos de moneda y comas)
     */
    protected function parseAmount($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        // Remover símbolos de moneda, puntos y comas
        $cleanValue = preg_replace('/[^0-9]/', '', $value);
        return $cleanValue ? (int) $cleanValue : 0;
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'linea' => 'required|numeric|min:1',
            'producto_o_servicio' => 'required|string|max:255',
            'cantidad' => 'required|numeric|min:1',
            'monto' => 'required|numeric|min:0',
            'cantidad_oc' => 'required|numeric|min:0',
            'meses_envio_oc' => 'required|string|max:100',
            'dist_regional' => 'required|string|max:255',
            'cod_gasto_presupuestario' => 'required|string|max:100',
            'tipo_de_compra' => 'required|string|max:255',
            'mes_de_publicacion' => 'required|string|max:100',
            'comentario' => 'nullable|string|max:500',
        ];
    }

    /**
     * Mensajes de validación personalizados
     */
    public function customValidationMessages()
    {
        return [
            'producto_o_servicio.required' => 'El campo Producto o Servicio es obligatorio.',
            'producto_o_servicio.string' => 'El campo Producto o Servicio debe ser texto.',
            'cantidad.required' => 'El campo Cantidad es obligatorio.',
            'cantidad.numeric' => 'El campo Cantidad debe ser numérico.',
            'cantidad.min' => 'El campo Cantidad debe ser mayor a 0.',
            'monto.required' => 'El campo Monto es obligatorio.',
            'monto.numeric' => 'El campo Monto debe ser numérico.',
            'monto.min' => 'El campo Monto debe ser mayor o igual a 0.',
        ];
    }

    /**
     * Número de filas por lote
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Tamaño del chunk para lectura
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Fila donde están los encabezados
     */
    public function headingRow(): int
    {
        return 1;
    }

    /**
     * Fila donde empezar a leer los datos
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Obtener errores de importación
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Obtener estadísticas de importación
     */
    public function getImportStats()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => count($this->errors),
            'total_processed' => $this->importedCount + $this->skippedCount
        ];
    }

    /**
     * Manejar errores durante la importación
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = [
            'row' => $this->importedCount + $this->skippedCount + 1,
            'error' => $e->getMessage(),
            'data' => []
        ];
        $this->skippedCount++;
        Log::error('Error during import: ' . $e->getMessage());
    }
} 