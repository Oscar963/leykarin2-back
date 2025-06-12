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
            $table->dateTime('date_created');
            $table->year('year');
            $table->dateTime('sending_date')->nullable();
            $table->dateTime('modification_date')->nullable();

            // Relaciones con otras tablas
            $table->foreignId('decreto_id')->nullable()->constrained('files')->onDelete('cascade'); // Decreto aprobado
            $table->foreignId('form_f1_id')->nullable()->unique()->constrained('form_f1')->onDelete('cascade'); // Formulario F1 (relación 1:1)
            $table->foreignId('status_purchase_plan_id')->constrained('status_purchase_plans')->onDelete('cascade'); // Estado del plan de compra
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Usuario que crea el plan de compra
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade'); // Usuario que actualiza el plan de compra           
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
