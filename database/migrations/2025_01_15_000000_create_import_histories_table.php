<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_histories', function (Blueprint $table) {
            $table->id();
            
            // Información básica de la importación
            $table->uuid('import_id')->unique(); // ID único de la importación
            $table->string('version')->default('1.0.0'); // Versión de la importación
            $table->string('type')->default('inmuebles'); // Tipo de importación
            $table->string('status')->default('pending'); // pending, processing, completed, failed, cancelled
            
            // Información del archivo
            $table->string('file_name');
            $table->string('file_original_name');
            $table->bigInteger('file_size'); // Tamaño en bytes
            $table->string('file_mime_type');
            $table->string('file_extension');
            
            // Información del usuario
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('user_name')->nullable(); // Nombre del usuario al momento de la importación
            $table->string('user_email')->nullable(); // Email del usuario al momento de la importación
            
            // Configuración de la importación
            $table->json('import_config')->nullable(); // Configuración usada
            $table->json('column_mapping')->nullable(); // Mapeo de columnas usado
            
            // Estadísticas de la importación
            $table->integer('total_rows')->default(0); // Total de filas en el archivo
            $table->integer('imported_count')->default(0); // Registros importados exitosamente
            $table->integer('skipped_count')->default(0); // Registros omitidos
            $table->integer('duplicates_count')->default(0); // Duplicados encontrados
            $table->integer('error_count')->default(0); // Errores encontrados
            
            // Información de performance
            $table->integer('processing_time_ms')->nullable(); // Tiempo de procesamiento en milisegundos
            $table->integer('memory_peak_mb')->nullable(); // Pico de memoria usado en MB
            
            // Información de sistema
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            
            // Errores y warnings
            $table->json('errors')->nullable(); // Errores detallados
            $table->json('warnings')->nullable(); // Warnings
            $table->text('error_summary')->nullable(); // Resumen de errores
            
            // Información de rollback
            $table->boolean('can_rollback')->default(false); // Si se puede hacer rollback
            $table->json('rollback_data')->nullable(); // Datos para rollback
            $table->timestamp('rolled_back_at')->nullable(); // Cuándo se hizo rollback
            $table->foreignId('rolled_back_by')->nullable()->constrained('users'); // Quién hizo rollback
            
            // Timestamps
            $table->timestamp('started_at')->nullable(); // Cuándo comenzó
            $table->timestamp('completed_at')->nullable(); // Cuándo terminó
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('import_id');
            $table->index('version');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_histories');
    }
}; 