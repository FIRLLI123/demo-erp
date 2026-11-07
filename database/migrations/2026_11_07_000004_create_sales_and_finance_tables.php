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
        // 1. Sales Orders (SO)
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 20)->default('SO');
            $table->string('doc_number', 50)->unique(); // e.g. SO/202611/0001
            $table->foreignId('tap_id')->constrained('taps')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('sales_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('order_date');
            $table->enum('channel', ['WEB_ADMIN', 'MOBILE_SALES'])->default('WEB_ADMIN');
            $table->enum('status', ['DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'DELIVERED', 'COMPLETED'])->default('APPROVED');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 2. Goods Delivery / Surat Jalan (GD)
        Schema::create('goods_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 20)->default('GD');
            $table->string('doc_number', 50)->unique(); // e.g. GD/202611/0001
            $table->string('ref_doc_type', 20)->default('SO');
            $table->string('ref_doc_number', 50)->nullable();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('delivery_date');
            $table->string('recipient_name')->nullable();
            $table->enum('status', ['DELIVERED', 'CONFIRMED'])->default('DELIVERED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Accounts Receivable / Piutang Faktur (AR)
        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 20)->default('AR');
            $table->string('doc_number', 50)->unique(); // e.g. AR/202611/0001
            $table->string('ref_doc_type', 20)->default('SO');
            $table->string('ref_doc_number', 50)->nullable();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->enum('status', ['UNPAID', 'PARTIAL', 'PAID'])->default('UNPAID');
            $table->timestamps();
        });

        // 4. Payments / Tanda Terima Kasir (FP - Faktur Pelunasan)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 20)->default('FP');
            $table->string('doc_number', 50)->unique(); // e.g. FP/202611/0001
            $table->string('ref_doc_type', 20)->default('AR');
            $table->string('ref_doc_number', 50)->nullable();
            $table->foreignId('accounts_receivable_id')->constrained('accounts_receivable')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('payment_date');
            $table->enum('payment_method', ['CASH', 'TRANSFER', 'DEPOSIT', 'RETURN_DEDUCTION'])->default('CASH');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('cashier_name')->default('Kasir TAP');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Bank Receipts (BBM - Bukti Bank Masuk)
        Schema::create('bank_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 20)->default('BBM');
            $table->string('doc_number', 50)->unique(); // e.g. BBM/202611/0001
            $table->string('ref_doc_type', 20)->default('FP');
            $table->string('ref_doc_number', 50)->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('bank_name')->default('BCA');
            $table->string('account_number')->nullable();
            $table->date('receipt_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['VERIFIED', 'PENDING'])->default('VERIFIED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('accounts_receivable');
        Schema::dropIfExists('goods_deliveries');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};

