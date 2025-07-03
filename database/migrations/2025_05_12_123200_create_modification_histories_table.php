<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModificationHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modification_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modification_id')->constrained('modifications')->onDelete('cascade');
            $table->string('action'); // create, update, delete, status_change
            $table->text('description'); // Descripción de la acción
            $table->json('details')->nullable(); // Detalles adicionales en formato JSON
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Usuario que realizó la acción
            $table->timestamp('date')->useCurrent(); // Fecha y hora de la acción
            
            $table->timestamps();
            
            // Índices
            $table->index(['modification_id', 'date']);
            $table->index('action');
        });

        // Crear tabla para archivos de modificaciones
        Schema::create('modification_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modification_id')->constrained('modifications')->onDelete('cascade');
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->string('file_type')->nullable()->comment('Tipo de documento: justificacion, cotizacion, etc.');
            $table->text('description')->nullable()->comment('Descripción del archivo');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Índices
            $table->unique(['modification_id', 'file_id']);
            $table->index(['modification_id', 'file_type']);
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modification_files');
        Schema::dropIfExists('modification_histories');
    }
} 