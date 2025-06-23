<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publication_months', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);        // "Diciembre"
            $table->string('short_name', 10);  // "Dic"
            $table->integer('month_number');   // 12
            $table->integer('year')->nullable(); // Año de publicación
            $table->timestamps();
            
            // Índice único para evitar duplicados por mes y año
            $table->unique(['month_number', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publication_months');
    }
}; 