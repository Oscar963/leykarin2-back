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
            $table->integer('modification_number'); // Número de modificación
            $table->date('date'); // Fecha de la modificación
            $table->text('reason'); // Motivo de la modificación
            $table->string('status')->default('active'); // Estado: active, inactive, pending, approved, rejected
            
            // Relaciones
            $table->foreignId('purchase_plan_id')->constrained('purchase_plans')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Usuario que realizó la modificación
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Índices
            $table->index(['purchase_plan_id', 'modification_number']);
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
        Schema::dropIfExists('modifications');
    }
} 