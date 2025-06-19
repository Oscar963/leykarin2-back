<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('product_service'); // Producto o servicio
            $table->integer('quantity_item'); // Cantidad de items
            $table->bigInteger('amount_item'); // Monto por item
            $table->integer('item_number'); // Número de item
            $table->integer('quantity_oc'); // Cantidad de OC
            $table->string('months_oc'); // Meses de OC
            $table->string('regional_distribution'); // Distribución regional
            $table->string('cod_budget_allocation_type'); // Código de gasto presupuestario

            // Relaciones con otras tablas
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade'); // Proyecto
            $table->foreignId('budget_allocation_id')->constrained('budget_allocations')->onDelete('cascade'); // Asignación de presupuesto
            $table->foreignId('type_purchase_id')->constrained('type_purchases')->onDelete('cascade'); // Tipo de compra
            $table->foreignId('status_item_purchase_id')->constrained('status_item_purchases')->onDelete('cascade'); // Estado

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

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
        Schema::dropIfExists('item_purchases');
    }
}
