<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Provinsi
        Schema::create('master_provinsi', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable()->unique();
            $table->string('name', 100);
            $table->timestamps();
        });

        // 2. Tabel Master Kota / Kabupaten
        Schema::create('master_kota_kab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provinsi_id')->constrained('master_provinsi')->cascadeOnDelete();
            $table->string('code', 10)->nullable()->unique();
            $table->enum('type', ['KOTA', 'KABUPATEN'])->default('KOTA');
            $table->string('name', 100);
            $table->timestamps();
        });

        // 3. Tabel Master Kecamatan
        Schema::create('master_kecamatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kota_kab_id')->constrained('master_kota_kab')->cascadeOnDelete();
            $table->string('code', 15)->nullable()->unique();
            $table->string('name', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kecamatan');
        Schema::dropIfExists('master_kota_kab');
        Schema::dropIfExists('master_provinsi');
    }
};
