<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDenouncedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('denounceds', function (Blueprint $table) {
            $table->id();

            // Datos personales
            $table->string('name', 50);
            $table->string('rut', 15)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('address', 50)->nullable();
            $table->string('charge', 50);
            $table->unsignedTinyInteger('grade')->nullable();
            $table->string('email', 50)->nullable();
            $table->string('unit', 50);
            $table->string('function', 100);

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
        Schema::dropIfExists('denounceds');
    }
}
