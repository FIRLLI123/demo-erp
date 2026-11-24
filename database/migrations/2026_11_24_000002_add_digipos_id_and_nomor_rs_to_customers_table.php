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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'digipos_id')) {
                $table->string('digipos_id', 50)->nullable()->after('code')->comment('ID DigiPOS Toko');
            }
            if (!Schema::hasColumn('customers', 'nomor_rs')) {
                $table->string('nomor_rs', 30)->nullable()->after('digipos_id')->comment('Nomor RS / Chip Mkios Utama');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'nomor_rs')) {
                $table->dropColumn('nomor_rs');
            }
            if (Schema::hasColumn('customers', 'digipos_id')) {
                $table->dropColumn('digipos_id');
            }
        });
    }
};
