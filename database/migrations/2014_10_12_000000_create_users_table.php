<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('rut')->unique()->nullable();
            $table->string('name');
            $table->string('paternal_surname');
            $table->string('maternal_surname');
            $table->string('email')->unique();
            $table->boolean('status');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Campos para 2FA por email
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_code', 6)->nullable();
            $table->timestamp('two_factor_expires_at')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
