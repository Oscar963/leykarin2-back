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
            $table->string('folio')->unique();
            $table->string('token')->unique();
            $table->foreignId('type_complaint_id')->constrained('type_complaints'); //tipo de denuncia
            $table->foreignId('complainant_id')->constrained('complainants'); //denunciante
            $table->foreignId('denounced_id')->constrained('denounceds'); //denunciado

            $table->foreignId('hierarchical_level_id')->constrained('hierarchical_levels'); //nivel jerarquico
            $table->foreignId('work_relationship_id')->constrained('work_relationships'); //relacion laboral
            $table->foreignId('supervisor_relationship_id')->constrained('supervisor_relationships'); //relacion supervisor
            $table->text('circumstances_narrative'); //circunstancias narración
            $table->text('consequences_narrative'); //consecuencias narración

            $table->softDeletes();
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
