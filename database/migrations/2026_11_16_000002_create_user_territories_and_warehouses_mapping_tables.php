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
        // 1. Tambah kolom di tabel users jika belum ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'cluster_id')) {
                $table->foreignId('cluster_id')->nullable()->after('role_id')->constrained('clusters')->nullOnDelete()->comment('Cakupan Wilayah Cluster (Opsional/Multi-TAP)');
            }
            if (!Schema::hasColumn('users', 'auto_include_sales_wh')) {
                $table->boolean('auto_include_sales_wh')->default(true)->after('tap_id')->comment('Otomatis menyertakan seluruh gudang sales baru di TAP terkait');
            }
            if (!Schema::hasColumn('users', 'is_ho_readonly')) {
                $table->boolean('is_ho_readonly')->default(false)->after('auto_include_sales_wh')->comment('Akun HO: Hanya lihat laporan (Read-Only), 0 transaksi');
            }
        });

        // 2. Tabel multi-TAP (user_taps) untuk staf yang memegang lebih dari 1 TAP atau multi-cluster
        if (!Schema::hasTable('user_taps')) {
            Schema::create('user_taps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('tap_id')->constrained('taps')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'tap_id']);
            });
        }

        // 3. Tabel user_warehouses untuk custom checklist gudang (termasuk pengecualian)
        if (!Schema::hasTable('user_warehouses')) {
            Schema::create('user_warehouses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'warehouse_id']);
            });
        }

        // 4. Sinkronisasi data eksisting dari users.tap_id ke user_taps
        $existingUsersWithTap = DB::table('users')->whereNotNull('tap_id')->get();
        foreach ($existingUsersWithTap as $u) {
            DB::table('user_taps')->updateOrInsert(
                ['user_id' => $u->id, 'tap_id' => $u->tap_id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_warehouses');
        Schema::dropIfExists('user_taps');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cluster_id']);
            $table->dropColumn(['cluster_id', 'auto_include_sales_wh', 'is_ho_readonly']);
        });
    }
};
