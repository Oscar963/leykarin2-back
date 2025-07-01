<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('token');
            $table->year('year');

            // Relaciones con otras tablas
            $table->foreignId('form_f1_id')->nullable()->unique()->constrained('form_f1')->onDelete('cascade'); // Formulario F1 (relación 1:1)
            $table->foreignId('decreto_id')->nullable()->unique()->constrained('decretos')->onDelete('cascade'); // Decreto aprobado
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Usuario que crea el plan de compra
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Usuario que actualiza el plan de compra           
            $table->foreignId('direction_id')->constrained('directions')->onDelete('cascade'); // Dirección del plan de compra

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_plans');
    }
}
