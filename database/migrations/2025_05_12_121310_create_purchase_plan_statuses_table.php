<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasePlanStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_plan_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_plan_id')->constrained('purchase_plans')->onDelete('cascade');
            $table->foreignId('status_purchase_plan_id')->constrained('status_purchase_plans')->onDelete('cascade');
            $table->dateTime('sending_date')->nullable();
            $table->string('plan_name')->nullable();
            $table->integer('plan_year')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('available_budget', 15, 2)->default(0);
            $table->text('sending_comment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índices con nombres más cortos
            $table->index(['purchase_plan_id', 'status_purchase_plan_id'], 'pp_status_idx');
            $table->index('sending_date', 'pp_sending_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_plan_statuses');
    }
} 