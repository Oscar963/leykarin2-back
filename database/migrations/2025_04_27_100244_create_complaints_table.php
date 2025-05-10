<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->string('folio');
            $table->string('token');

            $table->string('hierarchical_level');
            $table->string('work_directly');
            $table->string('immediate_leadership');

            $table->text('narration_facts');
            $table->text('narration_consequences');

            $table->string('signature');

            // Relaciones con otras tablas
            $table->foreignId('type_complaint_id')->constrained('type_complaints')->onDelete('cascade'); // Tipo de denuncia
            $table->foreignId('complainant_id')->constrained('complainants')->onDelete('cascade'); // Denunciante
            $table->foreignId('denounced_id')->constrained('denounceds')->onDelete('cascade'); // Denunciado
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
        Schema::dropIfExists('complaints');
    }
}
