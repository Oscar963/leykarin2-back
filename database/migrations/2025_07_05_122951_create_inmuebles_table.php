<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInmueblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inmuebles', function (Blueprint $table) {
            $table->id();

            $table->string('numero')->nullable(); // N°
            $table->text('descripcion')->nullable();
            $table->string('calle')->nullable(); // AVENIDA/CALLE/PASAJE
            $table->string('numeracion')->nullable();
            $table->string('lote_sitio')->nullable();
            $table->string('manzana')->nullable();
            $table->string('poblacion_villa')->nullable();
            $table->string('foja')->nullable();
            $table->string('inscripcion_numero')->nullable();
            $table->string('inscripcion_anio')->nullable();
            $table->string('rol_avaluo')->nullable();
            $table->string('superficie')->nullable();
            $table->string('deslinde_norte')->nullable();
            $table->string('deslinde_sur')->nullable();
            $table->string('deslinde_este')->nullable();
            $table->string('deslinde_oeste')->nullable();
            $table->string('decreto_incorporacion')->nullable();
            $table->string('decreto_destinacion')->nullable();
            $table->text('observaciones')->nullable();

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
        Schema::dropIfExists('inmuebles');
    }
};
