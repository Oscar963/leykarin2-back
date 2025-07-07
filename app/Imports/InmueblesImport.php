<?php

namespace App\Imports;

use App\Models\Inmueble;
use App\Models\ImportHistory;
use App\Models\ImportedRecord;
use App\Services\ImportHistoryService;
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
use Maatwebsite\Excel\Concerns\WithLimit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class InmueblesImport implements 
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

    protected $errors = [];
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $duplicatesCount = 0;
    protected $currentRow = 0;

    // Cache para optimizar consultas (para futuras extensiones)
    protected $existingInmuebles = [];
    
    // Historial de importación
    protected $importHistory = null;
    protected $importHistoryService = null;

    public function __construct(ImportHistory $importHistory = null)
    {
        $this->importHistory = $importHistory;
        $this->importHistoryService = app(ImportHistoryService::class);
        $this->initializeCache();
    }

    /**
     * Inicializar caché para optimizar consultas
     */
    protected function initializeCache()
    {
        // Cargar inmuebles existentes para detectar duplicados
        // Solo cargamos número y descripción para comparación
        $existing = Inmueble::select('numero', 'descripcion', 'id')->get();
        
        foreach ($existing as $inmueble) {
            if ($inmueble->numero) {
                $this->existingInmuebles['numero_' . strtolower(trim($inmueble->numero))] = $inmueble->id;
            }
            if ($inmueble->descripcion) {
                $this->existingInmuebles['desc_' . strtolower(trim($inmueble->descripcion))] = $inmueble->id;
            }
        }
    }

    /**
     * Mapear las columnas del Excel a nombres de campos normalizados
     */
    protected function mapRowKeys(array $row): array
    {
        $mapping = [
            // Campos principales
            'numero' => 'numero',
            'n' => 'numero',
            'n°' => 'numero',
            'item' => 'numero',
            'descripcion' => 'descripcion',
            'descripción' => 'descripcion',
            'description' => 'descripcion',
            
            // Ubicación
            'calle' => 'calle',
            'avenida' => 'calle',
            'pasaje' => 'calle',
            'avenida/calle/pasaje' => 'calle',
            'numeracion' => 'numeracion',
            'numeración' => 'numeracion',
            'numero_calle' => 'numeracion',
            'número_calle' => 'numeracion',
            
            // Lote y manzana
            'lote_sitio' => 'lote_sitio',
            'lote/sitio' => 'lote_sitio',
            'lote' => 'lote_sitio',
            'sitio' => 'lote_sitio',
            'manzana' => 'manzana',
            'mz' => 'manzana',
            'poblacion_villa' => 'poblacion_villa',
            'población_villa' => 'poblacion_villa',
            'población/villa' => 'poblacion_villa',
            'población' => 'poblacion_villa',
            'villa' => 'poblacion_villa',
            
            // Inscripción
            'foja' => 'foja',
            'fs' => 'foja',
            'inscripcion_numero' => 'inscripcion_numero',
            'inscripción_número' => 'inscripcion_numero',
            'nro_inscripcion' => 'inscripcion_numero',
            'inscripcion_anio' => 'inscripcion_anio',
            'inscripción_año' => 'inscripcion_anio',
            'año_inscripcion' => 'inscripcion_anio',
            
            // Avalúo
            'rol_avaluo' => 'rol_avaluo',
            'rol_avalúo' => 'rol_avaluo',
            'rol' => 'rol_avaluo',
            'superficie' => 'superficie',
            'sup' => 'superficie',
            'm2' => 'superficie',
            'm²' => 'superficie',
            
            // Deslindes
            'deslinde_norte' => 'deslinde_norte',
            'norte' => 'deslinde_norte',
            'deslinde_n' => 'deslinde_norte',
            'deslinde_sur' => 'deslinde_sur',
            'sur' => 'deslinde_sur',
            'deslinde_s' => 'deslinde_sur',
            'deslinde_este' => 'deslinde_este',
            'este' => 'deslinde_este',
            'deslinde_e' => 'deslinde_este',
            'deslinde_oeste' => 'deslinde_oeste',
            'oeste' => 'deslinde_oeste',
            'deslinde_o' => 'deslinde_oeste',
            
            // Decretos
            'decreto_incorporacion' => 'decreto_incorporacion',
            'decreto_incorporación' => 'decreto_incorporacion',
            'dcto_incorporacion' => 'decreto_incorporacion',
            'decreto_destinacion' => 'decreto_destinacion',
            'decreto_destinación' => 'decreto_destinacion',
            'dcto_destinacion' => 'decreto_destinacion',
            
            // Observaciones
            'observaciones' => 'observaciones',
            'obs' => 'observaciones',
            'comentarios' => 'observaciones',
            'notas' => 'observaciones',
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
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->currentRow++;
        
        // Mapear las claves del array para normalizar los nombres
        $row = $this->mapRowKeys($row);

        // Verificar si la fila tiene al menos un dato (no está completamente vacía)
        $hasData = false;
        $allFields = [
            'numero', 'descripcion', 'calle', 'numeracion', 'lote_sitio', 'manzana',
            'poblacion_villa', 'foja', 'inscripcion_numero', 'inscripcion_anio',
            'rol_avaluo', 'superficie', 'deslinde_norte', 'deslinde_sur',
            'deslinde_este', 'deslinde_oeste', 'decreto_incorporacion',
            'decreto_destinacion', 'observaciones'
        ];

        foreach ($allFields as $field) {
            if (isset($row[$field]) && trim($row[$field]) !== '') {
                $hasData = true;
                break;
            }
        }

        // Si la fila está completamente vacía, ignorarla silenciosamente
        if (!$hasData) {
            $this->skippedCount++;
            return null;
        }

        // Validar que tenga al menos número O descripción
        if ((!isset($row['numero']) || trim($row['numero']) === '') && 
            (!isset($row['descripcion']) || trim($row['descripcion']) === '')) {
            $this->errors[] = [
                'row' => $this->currentRow,
                'error' => 'Debe tener al menos un número o descripción.',
                'data' => $row
            ];
            $this->skippedCount++;
            return null;
        }

        try {
            // Verificar duplicados
            if ($this->isDuplicate($row)) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'error' => 'DUPLICADO: Ya existe un inmueble con el mismo número o descripción.',
                    'data' => $row
                ];
                $this->duplicatesCount++;
                $this->skippedCount++;
                return null;
            }

            // Limpiar y preparar datos
            $cleanedData = $this->cleanRowData($row);

            // Crear el modelo
            $inmueble = new Inmueble($cleanedData);

            $this->importedCount++;
            
            // Registrar en historial si está disponible
            if ($this->importHistory && $this->importHistoryService) {
                try {
                    $this->importHistoryService->registerImportedRecord(
                        $this->importHistory,
                        'inmuebles',
                        $inmueble->id ?? 0, // Se actualizará después de guardar
                        $row,
                        $cleanedData,
                        $this->currentRow
                    );
                } catch (Exception $e) {
                    Log::warning('Error al registrar en historial: ' . $e->getMessage());
                }
            }
            
            // Actualizar cache para detectar duplicados en las siguientes filas
            $this->updateCache($cleanedData);

            return $inmueble;

        } catch (Exception $e) {
            $this->errors[] = [
                'row' => $this->currentRow,
                'error' => 'ERROR: ' . $e->getMessage(),
                'data' => $row
            ];
            $this->skippedCount++;
            return null;
        }
    }

    /**
     * Verificar si es un registro duplicado
     */
    protected function isDuplicate(array $row): bool
    {
        // Verificar por número
        if (!empty($row['numero'])) {
            $numeroKey = 'numero_' . strtolower(trim($row['numero']));
            if (isset($this->existingInmuebles[$numeroKey])) {
                return true;
            }
        }

        // Verificar por descripción
        if (!empty($row['descripcion'])) {
            $descKey = 'desc_' . strtolower(trim($row['descripcion']));
            if (isset($this->existingInmuebles[$descKey])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Actualizar cache con nuevo registro
     */
    protected function updateCache(array $data): void
    {
        if (!empty($data['numero'])) {
            $numeroKey = 'numero_' . strtolower(trim($data['numero']));
            $this->existingInmuebles[$numeroKey] = 'importing'; // Marcar como en proceso
        }

        if (!empty($data['descripcion'])) {
            $descKey = 'desc_' . strtolower(trim($data['descripcion']));
            $this->existingInmuebles[$descKey] = 'importing'; // Marcar como en proceso
        }
    }

    /**
     * Limpiar y normalizar datos de la fila
     */
    protected function cleanRowData(array $row): array
    {
        $cleanedData = [];

        $fields = [
            'numero', 'descripcion', 'calle', 'numeracion', 'lote_sitio', 'manzana',
            'poblacion_villa', 'foja', 'inscripcion_numero', 'inscripcion_anio',
            'rol_avaluo', 'superficie', 'deslinde_norte', 'deslinde_sur',
            'deslinde_este', 'deslinde_oeste', 'decreto_incorporacion',
            'decreto_destinacion', 'observaciones'
        ];

        foreach ($fields as $field) {
            $value = $row[$field] ?? null;
            $cleanedData[$field] = $this->cleanValue($value);
        }

        return $cleanedData;
    }

    /**
     * Limpiar y normalizar valores
     */
    protected function cleanValue($value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        
        $cleaned = trim((string) $value);
        return $cleaned === '' ? null : $cleaned;
    }

    /**
     * Número de filas por lote para inserción
     */
    public function batchSize(): int
    {
        return 50; // Procesar en lotes de 50
    }

    /**
     * Tamaño del chunk para lectura
     */
    public function chunkSize(): int
    {
        return 100; // Leer en chunks de 100
    }

    /**
     * Fila donde están los encabezados
     */
    public function headingRow(): int
    {
        return 1; // Los encabezados están en la primera fila
    }

    /**
     * Fila donde empezar a leer los datos
     */
    public function startRow(): int
    {
        return 2; // Los datos empiezan desde la segunda fila
    }

    /**
     * Limitar el número de filas a procesar
     */
    public function limit(): int
    {
        // Sin límite práctico (usar un valor muy alto)
        return 1000000;
    }

    /**
     * Obtener errores de importación
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener estadísticas de importación
     */
    public function getImportStats(): array
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'duplicates' => $this->duplicatesCount,
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
            'error' => 'EXCEPCIÓN: ' . $e->getMessage(),
            'data' => []
        ];
        $this->skippedCount++;
        
        // Log del error para debugging
        Log::error('Error en importación de inmuebles', [
            'row' => $this->importedCount + $this->skippedCount,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Reglas de validación para cada fila
     */
    public function rules(): array
    {
        return [
            'numero' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'calle' => 'nullable|string|max:255',
            'numeracion' => 'nullable|string|max:50',
            'superficie' => 'nullable|string|max:100',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function customValidationMessages(): array
    {
        return [
            'numero.max' => 'El número no puede tener más de 255 caracteres.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'calle.max' => 'La calle no puede tener más de 255 caracteres.',
        ];
    }
}
