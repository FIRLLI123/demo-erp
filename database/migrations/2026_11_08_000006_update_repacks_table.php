<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repacks', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->default(0)->after('status');
            $table->string('status', 30)->default('OPEN')->change();
        });

        Schema::table('repack_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)->default(0)->after('quantity');
            $table->decimal('subtotal', 15, 2)->default(0)->after('unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('repack_items', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'subtotal']);
        });

        Schema::table('repacks', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
    }
};

