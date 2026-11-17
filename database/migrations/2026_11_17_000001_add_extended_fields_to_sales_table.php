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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->text('address')->nullable()->after('phone');
            $table->decimal('sales_limit', 15, 2)->default(0)->after('address');
            $table->decimal('sisa_limit', 15, 2)->default(0)->after('sales_limit');
            $table->string('sales_type', 50)->default('CANVASSER')->after('sisa_limit'); // CANVASSER, DIRECT_SALES, MODERN_OUTLET, SPV
            $table->string('sales_level', 30)->default('SF')->after('sales_type'); // SF, TL, SPV, COORD
            $table->string('bakulku_phone', 20)->nullable()->after('sales_level');
            $table->string('ngrs_type', 30)->nullable()->after('bakulku_phone'); // Digipos, MKIOS, Bulk
            $table->boolean('hadir')->default(true)->after('ngrs_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'address',
                'sales_limit',
                'sisa_limit',
                'sales_type',
                'sales_level',
                'bakulku_phone',
                'ngrs_type',
                'hadir',
            ]);
        });
    }
};
