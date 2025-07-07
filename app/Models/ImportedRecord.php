<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportedRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_history_id',
        'table_name',
        'record_id',
        'original_data',
        'processed_data',
        'row_number',
        'row_hash',
        'status',
        'error_message',
        'imported_at',
        'rolled_back_at',
    ];

    protected $casts = [
        'original_data' => 'array',
        'processed_data' => 'array',
        'imported_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];

    /**
     * Estados posibles del registro
     */
    const STATUS_IMPORTED = 'imported';
    const STATUS_ROLLED_BACK = 'rolled_back';
    const STATUS_ERROR = 'error';

    /**
     * Relación con el historial de importación
     */
    public function importHistory(): BelongsTo
    {
        return $this->belongsTo(ImportHistory::class);
    }

    /**
     * Scope para registros importados exitosamente
     */
    public function scopeImported($query)
    {
        return $query->where('status', self::STATUS_IMPORTED);
    }

    /**
     * Scope para registros con rollback
     */
    public function scopeRolledBack($query)
    {
        return $query->where('status', self::STATUS_ROLLED_BACK);
    }

    /**
     * Scope para registros con errores
     */
    public function scopeWithErrors($query)
    {
        return $query->where('status', self::STATUS_ERROR);
    }

    /**
     * Scope para registros por tabla
     */
    public function scopeFromTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Verificar si el registro fue importado exitosamente
     */
    public function isImported(): bool
    {
        return $this->status === self::STATUS_IMPORTED;
    }

    /**
     * Verificar si el registro fue revertido
     */
    public function isRolledBack(): bool
    {
        return $this->status === self::STATUS_ROLLED_BACK;
    }

    /**
     * Verificar si el registro tiene error
     */
    public function hasError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * Marcar como revertido
     */
    public function markAsRolledBack(): void
    {
        $this->update([
            'status' => self::STATUS_ROLLED_BACK,
            'rolled_back_at' => now(),
        ]);
    }

    /**
     * Marcar como error
     */
    public function markAsError(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_ERROR,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Obtener el modelo relacionado
     */
    public function getRelatedModel()
    {
        // Mapeo de nombres de tabla a modelos
        $modelMap = [
            'inmuebles' => Inmueble::class,
            'users' => User::class,
            // Agregar más mapeos según sea necesario
        ];

        $modelClass = $modelMap[$this->table_name] ?? null;
        
        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($this->record_id);
    }

    /**
     * Generar hash de la fila para detectar duplicados
     */
    public static function generateRowHash(array $data): string
    {
        // Crear un hash basado en los campos clave
        $keyFields = ['numero', 'descripcion', 'calle'];
        $hashData = [];
        
        foreach ($keyFields as $field) {
            $hashData[$field] = $data[$field] ?? '';
        }
        
        return md5(json_encode($hashData));
    }

    /**
     * Crear registro de importación
     */
    public static function createFromImport(
        int $importHistoryId,
        string $tableName,
        int $recordId,
        array $originalData,
        array $processedData = null,
        int $rowNumber,
        string $rowHash = null
    ): self {
        return static::create([
            'import_history_id' => $importHistoryId,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'original_data' => $originalData,
            'processed_data' => $processedData,
            'row_number' => $rowNumber,
            'row_hash' => $rowHash,
            'status' => self::STATUS_IMPORTED,
        ]);
    }
} 