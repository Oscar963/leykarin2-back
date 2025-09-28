<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Campos para Google OAuth
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('google_email')->nullable()->after('google_id');
            $table->string('google_name')->nullable()->after('google_email');
            $table->string('google_avatar')->nullable()->after('google_name');
            $table->timestamp('google_verified_at')->nullable()->after('google_avatar');
            $table->string('google_domain')->nullable()->after('google_verified_at');
            
            // Campo para indicar el método de autenticación usado
            $table->enum('auth_provider', ['local', 'google', 'claveunica'])->default('local')->after('google_domain');
            
            // Índices para optimizar búsquedas
            $table->index('google_id');
            $table->index('google_email');
            $table->index('auth_provider');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar índices primero
            $table->dropIndex(['google_id']);
            $table->dropIndex(['google_email']);
            $table->dropIndex(['auth_provider']);
            
            // Eliminar columnas
            $table->dropColumn([
                'google_id',
                'google_email', 
                'google_name',
                'google_avatar',
                'google_verified_at',
                'google_domain',
                'auth_provider'
            ]);
        });
    }
};
