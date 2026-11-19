<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name')->unique();
            $table->string('type_desc')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('sales_levels', function (Blueprint $table) {
            $table->id();
            $table->string('level_name')->unique();
            $table->string('level_desc')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        // Seed initial Sales Types from user
        $types = [
            ['type_name' => 'SF', 'type_desc' => 'Sales Force Lapangan / Canvasser', 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
            ['type_name' => 'DS', 'type_desc' => 'Direct Sales', 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
            ['type_name' => 'Sales Event', 'type_desc' => 'Penjualan Event / Pameran / Bazzar', 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
            ['type_name' => 'Sales NGRS', 'type_desc' => 'Sales Penanganan Khusus Outlet NGRS', 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
            ['type_name' => 'Sales Indihome', 'type_desc' => 'Sales FMC / Indihome Telkomsel', 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('sales_types')->insert($types);

        // Seed initial Sales Levels from user (Izzi standard)
        $levels = [
            ['level_name' => 'SALES', 'level_desc' => 'SALES', 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => 'SPV', 'level_desc' => 'SUPERVISOR', 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => 'BM', 'level_desc' => 'BRANCH MANAGER', 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => 'GM', 'level_desc' => 'GENERAL MANAGER', 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('sales_levels')->insert($levels);

        // Update existing sales in DB to match new types and levels if needed
        DB::table('sales')->whereIn('sales_type', ['CANVASSER', 'DIRECT_SALES', 'MODERN_OUTLET'])->update(['sales_type' => 'SF']);
        DB::table('sales')->whereIn('sales_level', ['SF', 'TL'])->update(['sales_level' => 'SALES']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_levels');
        Schema::dropIfExists('sales_types');
    }
};
