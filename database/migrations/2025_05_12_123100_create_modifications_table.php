<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modifications', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la modificación
            $table->text('description'); // Descripción de la modificación
            $table->string('version')->default('1.0'); // Versión de la modificación
            $table->date('date'); // Fecha de la modificación
            $table->string('status')->default('active'); // Estado: active, inactive, pending, approved, rejected
            
            // Relación con tipo de modificación
            $table->foreignId('modification_type_id')->constrained('modification_types')->onDelete('restrict');
            
            // Relación con plan de compra
            $table->foreignId('purchase_plan_id')->constrained('purchase_plans')->onDelete('cascade');
            
            // Usuario que realiza la modificación
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            
            // Índices
            $table->index('status');
            $table->index('modification_type_id');
            $table->index('purchase_plan_id');
            $table->index('created_by');
            $table->index('date');
            $table->index(['purchase_plan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modifications');
    }
} 