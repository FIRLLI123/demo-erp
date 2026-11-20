<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->index(['warehouse_id', 'product_id', 'date'], 'idx_sm_wh_prod_date');
            $table->index(['doc_type', 'doc_number'], 'idx_sm_doc');
        });
    }

    public function down(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropIndex('idx_sm_wh_prod_date');
            $table->dropIndex('idx_sm_doc');
        });
    }
};
