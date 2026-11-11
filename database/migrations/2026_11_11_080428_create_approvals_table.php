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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('approval_type', 30); // SALES_ORDER, PURCHASE_ORDER, CREDIT_LIMIT, SALES_RETURN
            $table->string('doc_number', 50);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('requestor_name', 100);
            $table->string('party_name', 150);
            $table->decimal('requested_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->string('approver_name', 100)->nullable();
            $table->text('approver_notes')->nullable();
            $table->timestamp('action_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
