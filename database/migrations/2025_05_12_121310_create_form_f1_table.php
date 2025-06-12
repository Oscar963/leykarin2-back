<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormF1Table extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_f1', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('url');
            $table->string('type');
            $table->integer('size');
            $table->decimal('amount', 15, 2)->nullable(); // Monto del formulario F1
            
            //Relaciones            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
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
        Schema::dropIfExists('form_f1');
    }
}
