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
        Schema::create('customer_rs_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('rs_number', 30)->comment('Nomor Chip RS / Mkios / DigiPOS (MSISDN)');
            $table->string('rs_name', 100)->nullable()->comment('Nama / Label Pengenal Chip RS (misal: RS Kasir 1, RS Owner)');
            $table->string('chip_type', 30)->default('ALL')->comment('Tipe Transaksi Chip (ALL, DATA, PERDANA, REGULER)');
            $table->boolean('is_primary')->default(false)->comment('Apakah Nomor RS Utama Toko (1 = Ya, 0 = Bukan)');
            $table->string('status', 20)->default('ACTIVE')->comment('Status Chip (ACTIVE / INACTIVE)');
            $table->text('notes')->nullable()->comment('Catatan Tambahan');
            $table->timestamps();

            $table->index(['customer_id', 'is_primary']);
            $table->index('rs_number');
        });

        // Set table comment di MySQL jika DB engine MySQL
        try {
            DB::statement("ALTER TABLE `customer_rs_numbers` COMMENT = '[MASTER] Multi Nomor RS / Chip Mkios DigiPOS per Toko Outlet'");
        } catch (\Throwable $e) {
            // Ignore if not MySQL
        }

        // Migrasi data existing dari customers.nomor_rs ke customer_rs_numbers
        $existingCustomers = DB::table('customers')->whereNotNull('nomor_rs')->where('nomor_rs', '!=', '')->get();
        foreach ($existingCustomers as $cust) {
            DB::table('customer_rs_numbers')->insert([
                'customer_id' => $cust->id,
                'rs_number' => $cust->nomor_rs,
                'rs_name' => 'RS Utama Toko',
                'chip_type' => 'ALL',
                'is_primary' => true,
                'status' => 'ACTIVE',
                'notes' => 'Nomor RS awal saat registrasi outlet',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_rs_numbers');
    }
};
