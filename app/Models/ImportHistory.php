<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ImportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_id',
        'version',
        'type',
        'status',
        'file_name',
        'file_original_name',
        'file_size',
        'file_mime_type',
        'file_extension',
        'user_id',
        'user_name',
        'user_email',
        'import_config',
        'column_mapping',
        'total_rows',
        'imported_count',
        'skipped_count',
        'duplicates_count',
        'error_count',
        'processing_time_ms',
        'memory_peak_mb',
        'ip_address',
        'user_agent',
        'session_id',
        'errors',
        'warnings',
        'error_summary',
        'can_rollback',
        'rollback_data',
        'rolled_back_at',
        'rolled_back_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'import_config' => 'array',
        'column_mapping' => 'array',
        'errors' => 'array',
        'warnings' => 'array',
        'rollback_data' => 'array',
        'can_rollback' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rolled_back_at' => 'datetime',
        'file_size' => 'integer',
        'processing_time_ms' => 'integer',
        'memory_peak_mb' => 'integer',
    ];

    /**
     * Estados posibles de la importación
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Tipos de importación
     */
    const TYPE_INMUEBLES = 'inmuebles';
    const TYPE_USUARIOS = 'usuarios';
    const TYPE_OTROS = 'otros';

    /**
     * Boot method para generar import_id automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->import_id)) {
                $model->import_id = Str::uuid()->toString();
            }
        });
    }

    /**
     * Relación con el usuario que realizó la importación
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el usuario que hizo rollback
     */
    public function rolledBackBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }

    /**
     * Relación con los registros importados (para rollback)
     */
    public function importedRecords(): HasMany
    {
        return $this->hasMany(ImportedRecord::class, 'import_history_id');
    }

    /**
     * Scope para importaciones por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para importaciones por estado
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para importaciones por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para importaciones recientes
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope para importaciones exitosas
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope para importaciones fallidas
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Verificar si la importación está en progreso
     */
    public function isProcessing(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Verificar si la importación fue exitosa
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verificar si la importación falló
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Verificar si la importación fue cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Obtener porcentaje de éxito
     */
    public function getSuccessRate(): float
    {
        if ($this->total_rows === 0) {
            return 0.0;
        }

        return round(($this->imported_count / $this->total_rows) * 100, 2);
    }

    /**
     * Obtener porcentaje de errores
     */
    public function getErrorRate(): float
    {
        if ($this->total_rows === 0) {
            return 0.0;
        }

        return round(($this->error_count / $this->total_rows) * 100, 2);
    }

    /**
     * Obtener tiempo de procesamiento formateado
     */
    public function getProcessingTimeFormatted(): string
    {
        if (!$this->processing_time_ms) {
            return 'N/A';
        }

        $seconds = $this->processing_time_ms / 1000;
        
        if ($seconds < 60) {
            return number_format($seconds, 2) . 's';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        return "{$minutes}m " . number_format($remainingSeconds, 2) . 's';
    }

    /**
     * Obtener tamaño de archivo formateado
     */
    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Obtener estadísticas resumidas
     */
    public function getStatistics(): array
    {
        return [
            'total_rows' => $this->total_rows,
            'imported_count' => $this->imported_count,
            'skipped_count' => $this->skipped_count,
            'duplicates_count' => $this->duplicates_count,
            'error_count' => $this->error_count,
            'success_rate' => $this->getSuccessRate(),
            'error_rate' => $this->getErrorRate(),
            'processing_time' => $this->getProcessingTimeFormatted(),
            'memory_peak' => $this->memory_peak_mb ? $this->memory_peak_mb . ' MB' : 'N/A',
        ];
    }

    /**
     * Marcar como iniciada
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
        ]);
    }

    /**
     * Marcar como completada
     */
    public function markAsCompleted(array $stats = []): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'imported_count' => $stats['imported'] ?? 0,
            'skipped_count' => $stats['skipped'] ?? 0,
            'duplicates_count' => $stats['duplicates'] ?? 0,
            'error_count' => $stats['errors'] ?? 0,
            'processing_time_ms' => $this->calculateProcessingTime(),
            'memory_peak_mb' => $this->getMemoryPeak(),
        ]);
    }

    /**
     * Marcar como fallida
     */
    public function markAsFailed(string $error = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'error_summary' => $error,
            'processing_time_ms' => $this->calculateProcessingTime(),
        ]);
    }

    /**
     * Marcar como cancelada
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
            'processing_time_ms' => $this->calculateProcessingTime(),
        ]);
    }

    /**
     * Calcular tiempo de procesamiento
     */
    private function calculateProcessingTime(): ?int
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->completed_at ?? now();
        return (int) ($endTime->diffInMilliseconds($this->started_at));
    }

    /**
     * Obtener pico de memoria usado
     */
    private function getMemoryPeak(): ?int
    {
        $memoryPeak = memory_get_peak_usage(true);
        return $memoryPeak ? (int) ($memoryPeak / 1024 / 1024) : null;
    }

    /**
     * Crear nueva versión de la importación
     */
    public function createNewVersion(): self
    {
        $version = $this->incrementVersion($this->version);
        
        return static::create([
            'import_id' => Str::uuid()->toString(),
            'version' => $version,
            'type' => $this->type,
            'status' => self::STATUS_PENDING,
            'file_name' => $this->file_name,
            'file_original_name' => $this->file_original_name,
            'file_size' => $this->file_size,
            'file_mime_type' => $this->file_mime_type,
            'file_extension' => $this->file_extension,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'import_config' => $this->import_config,
            'column_mapping' => $this->column_mapping,
        ]);
    }

    /**
     * Incrementar versión
     */
    private function incrementVersion(string $version): string
    {
        $parts = explode('.', $version);
        $parts[2] = (int) $parts[2] + 1;
        return implode('.', $parts);
    }

    /**
     * Obtener historial de versiones
     */
    public function getVersionHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('import_id', $this->import_id)
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Verificar si se puede hacer rollback
     */
    public function canPerformRollback(): bool
    {
        return $this->can_rollback && 
               $this->isSuccessful() && 
               !$this->rolled_back_at &&
               $this->imported_count > 0;
    }

    /**
     * Realizar rollback
     */
    public function performRollback(int $userId): bool
    {
        if (!$this->canPerformRollback()) {
            return false;
        }

        // Aquí implementarías la lógica de rollback
        // Por ejemplo, eliminar los registros importados
        
        $this->update([
            'rolled_back_at' => now(),
            'rolled_back_by' => $userId,
        ]);

        return true;
    }
} 