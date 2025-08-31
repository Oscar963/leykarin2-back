<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemporaryFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temporary_files', function (Blueprint $table) {
            $table->id();
            $table->string('session_id'); // Identificador de sesión/formulario
            $table->string('file_type'); // evidence, signature
            $table->string('original_name');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->unsignedBigInteger('size');
            $table->string('mime_type');
            $table->string('extension');
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at'); // Para limpieza automática
            $table->timestamps();

            // Índices
            $table->index('session_id');
            $table->index('file_type');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temporary_files');
    }
}
