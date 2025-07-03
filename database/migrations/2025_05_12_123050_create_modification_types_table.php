<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModificationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nombre del tipo de modificación
            $table->text('description')->nullable(); // Descripción del tipo
            $table->timestamps();
            
            // Índices
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modification_types');
    }
} 