<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_dependency_id')->constrained('type_dependencies')->cascadeOnDelete();
            $table->unsignedInteger('year');
            $table->unsignedInteger('current_seq')->default(0);
            $table->unique(['type_dependency_id', 'year'], 'complaint_counters_type_year_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_counters');
    }
};
