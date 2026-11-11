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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 100);
            $table->string('user_role', 50)->default('Staff');
            $table->string('module', 40); // SALES, INVENTORY, FINANCE, PROCUREMENT, MASTER, SECURITY
            $table->string('action', 50); // CREATE_SO, APPROVE_LIMIT, PROCESS_GR, etc.
            $table->enum('severity', ['INFO', 'WARNING', 'CRITICAL'])->default('INFO');
            $table->string('doc_number', 50)->nullable();
            $table->text('description');
            $table->string('ip_address', 60)->nullable();
            $table->string('user_agent', 150)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index('module');
            $table->index('severity');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
