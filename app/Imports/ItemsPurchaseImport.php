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
use Maatwebsite\Excel\Concerns\WithLimit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class ItemsPurchaseImport implements WithMultipleSheets
{
    protected $projectId;
    public $sheetImport;

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
    WithBatchInserts, 
    WithChunkReading, 
    WithCalculatedFormulas,
    SkipsOnError,
    SkipsEmptyRows,
    WithStartRow,
    WithLimit
{
    use SkipsErrors;
    
    protected $projectId;
    protected $errors = [];
    protected $importedCount = 0;
    protected $skippedCount = 0;
    
    // Caché para optimizar consultas
    public $budgetAllocationsCache = [];
    public $typePurchasesCache = [];
    public $publicationMonthsCache = [];
    public $defaultStatusId = null;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->initializeCache();
    }

    /**
     * Inicializar caché de relaciones para evitar consultas repetidas
     */
    protected function initializeCache()
    {
        // Cargar todas las asignaciones presupuestarias en caché
        $budgetAllocations = BudgetAllocation::all();
        foreach ($budgetAllocations as $allocation) {
            // Cachear por código para obtener ID
            $this->budgetAllocationsCache[$allocation->code] = $allocation->id;
            // También cachear por descripción
            $this->budgetAllocationsCache[strtolower($allocation->description)] = $allocation->id;
            // Cachear por formato "código - descripción" para obtener ID
            $formatoCompleto = $allocation->code . ' - ' . $allocation->description;
            $this->budgetAllocationsCache[$formatoCompleto] = $allocation->id;
            // Cachear para obtener el cod_budget_allocation_type por ID
            $this->budgetAllocationsCache['cod_' . $allocation->id] = $allocation->cod_budget_allocation_type;
        }

        // Cargar todos los tipos de compra en caché
        $typePurchases = TypePurchase::all();
        foreach ($typePurchases as $type) {
            $this->typePurchasesCache[strtolower($type->name)] = $type->id;
            if ($type->cod_purchase_type) {
                $this->typePurchasesCache[strtolower($type->cod_purchase_type)] = $type->id;
            }
        }

        // Cargar todos los meses de publicación en caché
        $publicationMonths = PublicationMonth::all();
        foreach ($publicationMonths as $month) {
            $key = strtolower($month->short_name . ' ' . $month->year);
            $this->publicationMonthsCache[$key] = $month->id;
            $key = strtolower($month->name . ' ' . $month->year);
            $this->publicationMonthsCache[$key] = $month->id;
        }

        // Obtener ID del estado por defecto
        $defaultStatus = StatusItemPurchase::where('name', 'like', '%pendiente%')
            ->orWhere('name', 'like', '%borrador%')
            ->orWhere('name', 'like', '%draft%')
            ->first();
        $this->defaultStatusId = $defaultStatus ? $defaultStatus->id : StatusItemPurchase::first()->id;
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
            'total_item' => 'total_item', // Solo informativa, no se guarda en BD
            'total ítem' => 'total_item', // Solo informativa, no se guarda en BD
            'cantidad_oc' => 'cantidad_oc',
            'cantidad oc' => 'cantidad_oc',
            'meses_envio_oc' => 'meses_envio_oc',
            'meses envio oc' => 'meses_envio_oc',
            'dist_regional' => 'dist_regional',
            'dist. regional' => 'dist_regional',
            'asignacion_presupuestaria' => 'asignacion_presupuestaria', // Solo informativa, no se guarda en BD
            'asignación presupuestaria' => 'asignacion_presupuestaria', // Solo informativa, no se guarda en BD
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
        
        // Verificar si estamos leyendo la hoja correcta
        if (isset($row['codigo']) || isset($row['descripcion']) || isset($row['formato_para_importar'])) {
            return null; // Ignorar esta fila
        }
        
        // Verificar si la fila tiene al menos un dato (no está completamente vacía)
        $hasData = false;
        $allFields = [
            'linea',
            'producto_o_servicio',
            'cantidad',
            'monto',
            'total_item', // Solo informativa
            'cantidad_oc',
            'meses_envio_oc',
            'dist_regional',
            'asignacion_presupuestaria', // Solo informativa
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
            'asignacion_presupuestaria', // Ahora este es obligatorio para calcular cod_budget_allocation_type
            'tipo_de_compra',
            'mes_de_publicacion',
            // 'total_item' - Solo informativa, no es obligatoria
            // 'cod_gasto_presupuestario' - Ahora se calcula automáticamente
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
            // Mapear las relaciones usando caché
            // Usar la asignación presupuestaria (columna I) para obtener el ID
            $budgetAllocationId = $this->getBudgetAllocationIdFromCache($row['asignacion_presupuestaria'] ?? '');
            $typePurchaseId = $this->getTypePurchaseIdFromCache($row['tipo_de_compra'] ?? '');
            $publicationMonthId = $this->getPublicationMonthIdFromCache($row['mes_de_publicacion'] ?? '');

            // Calcular automáticamente cod_budget_allocation_type desde la base de datos
            $codBudgetAllocationType = '';
            if ($budgetAllocationId) {
                $codBudgetAllocationType = $this->budgetAllocationsCache['cod_' . $budgetAllocationId] ?? '';
            }

            // Crear el modelo
            $itemPurchase = new ItemPurchase([
                'item_number' => $this->parseInteger($row['linea'] ?? 0),
                'product_service' => $row['producto_o_servicio'] ?? '',
                'quantity_item' => $this->parseInteger($row['cantidad'] ?? 0),
                'amount_item' => $this->parseAmount($row['monto'] ?? 0),
                'quantity_oc' => $this->parseInteger($row['cantidad_oc'] ?? 0),
                'months_oc' => $row['meses_envio_oc'] ?? '',
                'regional_distribution' => $row['dist_regional'] ?? '',
                'cod_budget_allocation_type' => $codBudgetAllocationType, // Calculado automáticamente
                'comment' => $row['comentario'] ?? null, // Campo opcional
                // Relaciones
                'project_id' => $this->projectId,
                'budget_allocation_id' => $budgetAllocationId,
                'type_purchase_id' => $typePurchaseId,
                'status_item_purchase_id' => $this->defaultStatusId,
                'publication_month_id' => $publicationMonthId,
                // Usuario que importa
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $this->importedCount++;
            return $itemPurchase;

        } catch (Exception $e) {
            $this->errors[] = [
                'row' => $this->importedCount + $this->skippedCount + 1,
                'error' => $e->getMessage(),
                'data' => $row
            ];
            $this->skippedCount++;
            return null;
        }
    }

    /**
     * Obtener ID de asignación presupuestaria desde caché
     */
    public function getBudgetAllocationIdFromCache($code)
    {
        if (empty($code)) {
            return null;
        }

        $code = trim($code);
        
        // Buscar en caché por código exacto
        if (isset($this->budgetAllocationsCache[$code])) {
            return $this->budgetAllocationsCache[$code];
        }

        // Buscar en caché por descripción
        $codeLower = strtolower($code);
        if (isset($this->budgetAllocationsCache[$codeLower])) {
            return $this->budgetAllocationsCache[$codeLower];
        }

        // Si no encuentra, buscar en el formato "código - descripción"
        if (strpos($code, ' - ') !== false) {
            $parts = explode(' - ', $code);
            $codePart = trim($parts[0]);
            if (isset($this->budgetAllocationsCache[$codePart])) {
                return $this->budgetAllocationsCache[$codePart];
            }
        }

        // Fallback: usar la primera disponible
        return reset($this->budgetAllocationsCache) ?: null;
    }

    /**
     * Obtener ID de tipo de compra desde caché
     */
    public function getTypePurchaseIdFromCache($name)
    {
        if (empty($name)) {
            return null;
        }

        $nameLower = strtolower(trim($name));
        
        // Buscar en caché
        if (isset($this->typePurchasesCache[$nameLower])) {
            return $this->typePurchasesCache[$nameLower];
        }

        // Fallback: usar el primer tipo disponible
        return reset($this->typePurchasesCache) ?: null;
    }

    /**
     * Obtener ID de mes de publicación desde caché
     */
    public function getPublicationMonthIdFromCache($monthYear)
    {
        if (empty($monthYear)) {
            return null;
        }

        $monthYearLower = strtolower(trim($monthYear));
        
        // Buscar en caché
        if (isset($this->publicationMonthsCache[$monthYearLower])) {
            return $this->publicationMonthsCache[$monthYearLower];
        }

        // Fallback: usar el primer mes disponible
        return reset($this->publicationMonthsCache) ?: null;
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
     * Número de filas por lote
     */
    public function batchSize(): int
    {
        return 10;
    }

    /**
     * Tamaño del chunk para lectura
     */
    public function chunkSize(): int
    {
        return 10;
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
     * Limitar el número de filas a procesar (evita leer filas vacías infinitas)
     */
    public function limit(): int
    {
        return 1000; // Máximo 1000 filas para evitar timeouts
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
    }
} 