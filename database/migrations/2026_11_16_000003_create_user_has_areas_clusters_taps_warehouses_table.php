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
        // 1. Tambah kolom has_full_access ke users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'has_full_access')) {
                $table->boolean('has_full_access')->default(false)->after('role_id')->comment('Akses Penuh ke seluruh wilayah dan gudang');
            }
        });

        // 2. Buat tabel tunggal user_has_areas_clusters_taps_warehouses
        if (!Schema::hasTable('user_has_areas_clusters_taps_warehouses')) {
            Schema::create('user_has_areas_clusters_taps_warehouses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('area_id')->nullable()->constrained('areas')->cascadeOnDelete();
                $table->foreignId('cluster_id')->nullable()->constrained('clusters')->cascadeOnDelete();
                $table->foreignId('tap_id')->nullable()->constrained('taps')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->cascadeOnDelete();
                $table->boolean('is_all_warehouses')->default(true)->comment('True = Semua gudang di TAP termasuk sales baru');
                $table->timestamps();

                $table->index(['user_id', 'tap_id'], 'idx_user_tap');
                $table->index(['user_id', 'warehouse_id'], 'idx_user_wh');
            });
        }

        // 3. Set default data
        // User 1 (Admin Utama / IT DEV): Akses Penuh
        DB::table('users')->where('id', 1)->update(['has_full_access' => true]);

        // User Pasar Minggu
        $uPm = DB::table('users')->where('email', 'admin.pasarminggu@wahanapos.com')->first();
        if ($uPm) {
            DB::table('user_has_areas_clusters_taps_warehouses')->updateOrInsert(
                ['user_id' => $uPm->id, 'tap_id' => 1, 'warehouse_id' => null],
                [
                    'area_id' => 1,
                    'cluster_id' => 1,
                    'is_all_warehouses' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // User Blok M
        $uBm = DB::table('users')->where('email', 'admin.blokm@wahanapos.com')->first();
        if ($uBm) {
            DB::table('user_has_areas_clusters_taps_warehouses')->updateOrInsert(
                ['user_id' => $uBm->id, 'tap_id' => 2, 'warehouse_id' => null],
                [
                    'area_id' => 1,
                    'cluster_id' => 1,
                    'is_all_warehouses' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_has_areas_clusters_taps_warehouses');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'has_full_access')) {
                $table->dropColumn('has_full_access');
            }
        });
    }
};
