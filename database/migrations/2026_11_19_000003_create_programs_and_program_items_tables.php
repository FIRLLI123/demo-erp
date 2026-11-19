<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Program Harga per Wilayah & Periode
        if (!Schema::hasTable('programs')) {
            Schema::create('programs', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 150);
                $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
                $table->foreignId('cluster_id')->nullable()->constrained('clusters')->nullOnDelete();
                $table->foreignId('tap_id')->nullable()->constrained('taps')->nullOnDelete();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->text('description')->nullable();
                $table->string('status', 20)->default('ACTIVE');
                $table->timestamps();

                $table->index(['cluster_id', 'status']);
            });
        }

        // 2. Detail Harga Item di Dalam Program
        if (!Schema::hasTable('program_items')) {
            Schema::create('program_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->enum('price_mode', ['FIXED', 'DISCOUNT_NOMINAL', 'MARKUP_NOMINAL'])->default('FIXED');
                $table->decimal('price_value', 15, 2)->default(0);
                $table->integer('min_qty')->default(1);
                $table->string('notes', 255)->nullable();
                $table->timestamps();

                $table->unique(['program_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('program_items');
        Schema::dropIfExists('programs');
    }
};
