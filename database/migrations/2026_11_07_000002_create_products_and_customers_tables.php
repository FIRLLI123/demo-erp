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
        // 1. Product Categories
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        // 2. Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('sku')->unique(); // e.g. SA-5GB-REG, VCR-3GB-3D, MDM-ORBIT-MAX
            $table->string('name');
            $table->enum('type', ['SA', 'VOUCHER', 'MODEM'])->default('SA'); // SA = Starter Pack SIM Card
            $table->enum('tracking_method', ['ICCID', 'QUANTITY'])->default('ICCID');
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->string('uom')->default('PCS'); // PCS, BOX, PACK
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        // 3. Customers table (Outlet / Reseller)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tap_id')->nullable()->constrained('taps')->nullOnDelete();
            $table->string('code')->unique(); // e.g. CST-001
            $table->string('name'); // e.g. Toko Berkah Cell
            $table->enum('type', ['RETAIL', 'GROSIR'])->default('RETAIL');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};

