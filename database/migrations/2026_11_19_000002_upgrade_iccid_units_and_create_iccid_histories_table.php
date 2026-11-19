<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade iccid_units (padanan ms_sn) agar siap tracking aktivasi/expired/HPP/konsinyasi per kartu
        Schema::table('iccid_units', function (Blueprint $table) {
            if (!Schema::hasColumn('iccid_units', 'activation_date')) {
                $table->date('activation_date')->nullable()->after('last_doc_number');
            }
            if (!Schema::hasColumn('iccid_units', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('activation_date');
            }
            if (!Schema::hasColumn('iccid_units', 'cost_price')) {
                $table->decimal('cost_price', 15, 2)->default(0)->after('expired_date');
            }
            if (!Schema::hasColumn('iccid_units', 'is_consignment')) {
                $table->boolean('is_consignment')->default(false)->after('cost_price');
            }
        });

        // 2. Tabel Riwayat Perjalanan ICCID / SN (padanan ms_sn_d)
        if (!Schema::hasTable('iccid_histories')) {
            Schema::create('iccid_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('iccid_unit_id')->constrained('iccid_units')->cascadeOnDelete();
                $table->string('iccid_number', 30)->index();
                $table->string('event_type', 30); // GR_IN, MUTATION_OUT, MUTATION_IN, REPACK_OUT, REPACK_IN, SO_OUT, RETURN_IN, ADJUSTMENT
                $table->string('doc_type', 20)->nullable();
                $table->string('doc_number', 50)->nullable()->index();
                $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('sales_id')->nullable()->constrained('sales')->nullOnDelete();
                $table->string('old_status', 20)->nullable();
                $table->string('new_status', 20)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('iccid_histories');

        Schema::table('iccid_units', function (Blueprint $table) {
            foreach (['activation_date', 'expired_date', 'cost_price', 'is_consignment'] as $col) {
                if (Schema::hasColumn('iccid_units', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
