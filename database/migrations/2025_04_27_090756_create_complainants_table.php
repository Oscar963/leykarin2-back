<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplainantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complainants', function (Blueprint $table) {
            $table->id();

            // Datos personales
            $table->string('name', 50);
            $table->string('rut', 15);
            $table->string('phone', 15);
            $table->string('email', 50);
            $table->string('address', 50);

            // Datos laborales
            $table->string('charge', 50);
            $table->string('unit', 50);
            $table->string('function', 100);
            $table->unsignedTinyInteger('grade_eur')->nullable();
            $table->date('date_income');
            $table->string('type_contract');
            $table->string('type_ladder')->nullable();
            $table->unsignedTinyInteger('grade')->nullable();

            // Datos espeficos
            $table->boolean('is_victim');

            // Relaciones con otras tablas
            $table->foreignId('dependence_id')->constrained('dependences')->onDelete('cascade'); // Tipo de denuncia

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
        Schema::dropIfExists('complainants');
    }
}
