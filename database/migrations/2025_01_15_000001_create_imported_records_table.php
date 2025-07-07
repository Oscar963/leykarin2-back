<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportedRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imported_records', function (Blueprint $table) {
            $table->id();
            
            // Relación con el historial de importación
            $table->foreignId('import_history_id')->constrained('import_histories')->onDelete('cascade');
            
            // Información del registro
            $table->string('table_name'); // Nombre de la tabla donde se insertó
            $table->unsignedBigInteger('record_id'); // ID del registro insertado
            $table->json('original_data'); // Datos originales del archivo
            $table->json('processed_data')->nullable(); // Datos procesados antes de insertar
            
            // Información de la fila en el archivo
            $table->integer('row_number'); // Número de fila en el archivo
            $table->string('row_hash')->nullable(); // Hash para identificar duplicados
            
            // Estado del registro
            $table->string('status')->default('imported'); // imported, rolled_back, error
            $table->text('error_message')->nullable(); // Mensaje de error si falló
            
            // Timestamps
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamp('rolled_back_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['import_history_id', 'table_name']);
            $table->index(['table_name', 'record_id']);
            $table->index('row_hash');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imported_records');
    }
}; 