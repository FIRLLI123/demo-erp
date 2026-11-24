<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cluster_so_cutoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cluster_id')->constrained('clusters')->cascadeOnDelete();
            $table->time('close_time')->default('17:00:00');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_force_closed')->default(false);
            $table->date('force_closed_date')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique('cluster_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cluster_so_cutoffs');
    }
};
