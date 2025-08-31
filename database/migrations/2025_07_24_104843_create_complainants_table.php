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
            $table->foreignId('type_dependency_id')->constrained('type_dependencies');
            $table->string('name');
            $table->string('address');
            $table->string('rut');
            $table->string('phone');
            $table->string('charge');
            $table->string('email');
            $table->string('unit');
            $table->string('function');
            $table->integer('grade')->nullable();
            $table->date('birthdate');
            $table->date('entry_date');
            $table->string('contractual_status');
            $table->boolean('is_victim');
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
