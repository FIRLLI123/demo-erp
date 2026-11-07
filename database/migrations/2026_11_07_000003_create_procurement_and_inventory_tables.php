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
        // 1. Purchase Orders (PO)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 20)->default('PO');
            $table->string('doc_number', 50)->unique(); // e.g. PO/202611/0001
            $table->foreignId('tap_id')->constrained('taps')->cascadeOnDelete();
            $table->string('supplier_name')->default('PT TELKOMSEL DISTRIBUSI PUSAT');
            $table->date('order_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['DRAFT', 'ORDERED', 'PARTIAL', 'RECEIVED', 'CANCELLED'])->default('DRAFT');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 2. Goods Receipts (GR)
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 20)->default('GR');
            $table->string('doc_number', 50)->unique(); // e.g. GR/202611/0001
            $table->string('ref_doc_type', 20)->default('PO');
            $table->string('ref_doc_number', 50)->nullable();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('tap_id')->constrained('taps')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete(); // Usually MAIN TAP
            $table->date('received_date');
            $table->enum('status', ['DRAFT', 'CONFIRMED'])->default('CONFIRMED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity_received')->default(1);
            $table->timestamps();
        });

        // 3. Stock Balances (Quantity based)
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->timestamps();
            $table->unique(['warehouse_id', 'product_id']);
        });

        // 4. ICCID Units (ICCID based)
        Schema::create('iccid_units', function (Blueprint $table) {
            $table->id();
            $table->string('iccid_number', 30)->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->enum('status', ['AVAILABLE', 'RESERVED', 'SOLD', 'RETURNED'])->default('AVAILABLE');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('last_doc_type', 20)->nullable();
            $table->string('last_doc_number', 50)->nullable();
            $table->timestamps();
        });

        // 5. Stock Movement (SM)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 20)->default('SM');
            $table->string('doc_number', 50)->unique(); // e.g. SM/202611/0001
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('movement_date');
            $table->enum('status', ['DRAFT', 'COMPLETED'])->default('COMPLETED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_movement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_movement_id')->constrained('stock_movements')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // 6. Repack Transactions (RO / RPKOUT & RI / RPKIN)
        Schema::create('repacks', function (Blueprint $table) {
            $table->id();
            $table->enum('doc_type', ['RPKOUT', 'RPKIN'])->default('RPKOUT');
            $table->string('doc_number', 50)->unique(); // e.g. RO/202611/0001 or RI/202611/0001
            $table->string('ref_doc_type', 20)->nullable();
            $table->string('ref_doc_number', 50)->nullable();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('repack_date');
            $table->enum('status', ['DRAFT', 'COMPLETED'])->default('COMPLETED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('repack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repack_id')->constrained('repacks')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('item_type', ['INPUT', 'OUTPUT'])->default('INPUT'); // INPUT = barang diurai, OUTPUT = hasil kemas baru
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repack_items');
        Schema::dropIfExists('repacks');
        Schema::dropIfExists('stock_movement_items');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('iccid_units');
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};

