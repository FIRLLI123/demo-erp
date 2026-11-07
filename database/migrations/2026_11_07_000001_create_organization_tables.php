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
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // super_admin, admin, finance, warehouse, sales_manager, sales
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Add role_id to users table if not exists
        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
                $table->string('phone')->nullable();
                $table->boolean('is_active')->default(true);
            });
        }

        // 2. Areas table
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. AREA-JABO
            $table->string('name'); // e.g. Area Jabodetabek
            $table->string('status')->default('ACTIVE'); // ACTIVE, INACTIVE
            $table->timestamps();
        });

        // 3. Clusters table
        Schema::create('clusters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('code')->unique(); // e.g. CLS-JKTSEL
            $table->string('name'); // e.g. Cluster Jakarta Selatan
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        // 4. TAPs table (Telecommunication Authorization Point)
        Schema::create('taps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cluster_id')->constrained('clusters')->cascadeOnDelete();
            $table->string('code')->unique(); // e.g. TAP-PASARMINGGU
            $table->string('name'); // e.g. TAP Pasar Minggu
            $table->string('address')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        // 5. Sales persons table
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tap_id')->constrained('taps')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->unique(); // e.g. SLS-001
            $table->string('name'); // e.g. Rian Hidayat
            $table->string('phone')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });

        // 6. Warehouses table (MAIN, INVENTORY, SALES)
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. WH-MAIN-PM01, WH-INV-PM01, WH-SLS-RIAN01
            $table->string('name');
            $table->enum('type', ['MAIN', 'INVENTORY', 'SALES'])->default('MAIN');
            $table->foreignId('tap_id')->constrained('taps')->cascadeOnDelete();
            $table->foreignId('sales_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('taps');
        Schema::dropIfExists('clusters');
        Schema::dropIfExists('areas');
        
        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn(['role_id', 'phone', 'is_active']);
            });
        }
        Schema::dropIfExists('roles');
    }
};

