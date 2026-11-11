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
        // 1. Master Rekening Bank & Kas PT
        Schema::create('company_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 60);
            $table->string('account_number', 50)->nullable();
            $table->string('account_holder', 100);
            $table->string('branch_name', 100)->nullable();
            $table->enum('account_type', ['BANK', 'CASH'])->default('BANK');
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });

        // 2. Master Vendor & Supplier
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('category', 60)->default('PRINCIPAL_TELCO');
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });

        // 3. Master Skema Harga (Price Profile / Tiering Outlet)
        Schema::create('price_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->string('segment', 30)->default('RS'); // RS, BOS, SD
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->integer('min_order_qty')->default(1);
            $table->text('description')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_profiles');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('company_banks');
    }
};
