<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryPurchaseHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_purchase_histories', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->string('description');
            $table->string('user');
            $table->string('action_type'); // create, update, delete, status_change, file_upload, etc.
            $table->json('details')->nullable(); // Detalles adicionales del movimiento
            $table->timestamps();

            // Relaciones con otras tablas
            $table->foreignId('purchase_plan_id')->constrained('purchase_plans')->onDelete('cascade'); // Plan de compra
            $table->foreignId('status_purchase_plan_id')->constrained('status_purchase_plans')->onDelete('cascade'); // Estado

            // Índices para mejorar el rendimiento
            $table->index(['purchase_plan_id', 'action_type']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_purchase_histories');
    }
}
