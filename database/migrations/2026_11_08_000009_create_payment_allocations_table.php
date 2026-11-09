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
        // 1. Jadikan accounts_receivable_id nullable di payments
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('accounts_receivable_id')->nullable()->change();
        });

        // 2. Buat tabel payment_allocations untuk multi-AR per 1 TTK
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('accounts_receivable_id')->constrained('accounts_receivable')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->timestamps();
        });

        // 3. Backfill existing payment data jika ada
        $existingPayments = DB::table('payments')->whereNotNull('accounts_receivable_id')->get();
        foreach ($existingPayments as $p) {
            DB::table('payment_allocations')->insert([
                'payment_id' => $p->id,
                'accounts_receivable_id' => $p->accounts_receivable_id,
                'amount' => $p->amount,
                'balance_before' => $p->amount,
                'balance_after' => 0,
                'created_at' => $p->created_at ?? now(),
                'updated_at' => $p->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};

