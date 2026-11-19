<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade product_categories (digunakan sebagai Master Tipe Produk)
        Schema::table('product_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('product_categories', 'description')) {
                $table->string('description', 255)->nullable()->after('code');
            }
            if (!Schema::hasColumn('product_categories', 'status')) {
                $table->string('status', 20)->default('ACTIVE')->after('description');
            }
        });

        // 2. Ubah products.type dari ENUM menjadi VARCHAR(50) agar fleksibel
        DB::statement("ALTER TABLE products MODIFY type VARCHAR(50) NOT NULL DEFAULT 'SA'");

        // 3. Tambahkan kolom extended pada products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'item_code_telkomsel')) {
                $table->string('item_code_telkomsel', 100)->nullable()->after('sku');
            }
            if (!Schema::hasColumn('products', 'denom')) {
                $table->string('denom', 60)->nullable()->after('name');
            }
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('denom');
            }
            if (!Schema::hasColumn('products', 'special_price')) {
                $table->decimal('special_price', 15, 2)->default(0)->after('selling_price');
            }
            if (!Schema::hasColumn('products', 'is_consignment')) {
                $table->boolean('is_consignment')->default(false)->after('uom')->comment('TRUE=bisa konsinyasi, FALSE=non-konsinyasi');
            }
            if (!Schema::hasColumn('products', 'is_virtual')) {
                $table->boolean('is_virtual')->default(false)->after('is_consignment');
            }
            if (!Schema::hasColumn('products', 'is_ppn')) {
                $table->boolean('is_ppn')->default(true)->after('is_virtual');
            }
            if (!Schema::hasColumn('products', 'tax_alias')) {
                $table->string('tax_alias', 150)->nullable()->after('is_ppn');
            }
            if (!Schema::hasColumn('products', 'min_stock')) {
                $table->integer('min_stock')->default(0)->after('tax_alias');
            }
            if (!Schema::hasColumn('products', 'activation_date')) {
                $table->date('activation_date')->nullable()->after('min_stock');
            }
            if (!Schema::hasColumn('products', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('activation_date');
            }
        });

        // 4. Tabel Harga Produk per Wilayah (Cluster / Area / TAP)
        if (!Schema::hasTable('product_prices')) {
            Schema::create('product_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
                $table->foreignId('cluster_id')->nullable()->constrained('clusters')->nullOnDelete();
                $table->foreignId('tap_id')->nullable()->constrained('taps')->nullOnDelete();
                $table->decimal('cost_price', 15, 2)->default(0);
                $table->decimal('selling_price', 15, 2)->default(0);
                $table->decimal('special_price', 15, 2)->default(0);
                $table->string('status', 20)->default('ACTIVE');
                $table->timestamps();

                $table->index(['product_id', 'cluster_id']);
                $table->index(['product_id', 'tap_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');

        Schema::table('products', function (Blueprint $table) {
            $cols = [
                'item_code_telkomsel', 'denom', 'description', 'special_price',
                'is_consignment', 'is_virtual', 'is_ppn', 'tax_alias',
                'min_stock', 'activation_date', 'expired_date',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('product_categories', function (Blueprint $table) {
            foreach (['description', 'status'] as $col) {
                if (Schema::hasColumn('product_categories', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
