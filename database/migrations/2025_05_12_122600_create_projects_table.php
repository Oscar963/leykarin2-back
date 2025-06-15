<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('token'); 
            $table->integer('project_number'); // Número de proyecto
            $table->string('description'); // Descripción del proyecto

            // Relaciones con otras tablas
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('unit_purchasing_id')->constrained('unit_purchasings')->onDelete('cascade');
            $table->foreignId('purchase_plan_id')->constrained('purchase_plans')->onDelete('cascade');
            $table->foreignId('type_project_id')->constrained('type_projects')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
} 