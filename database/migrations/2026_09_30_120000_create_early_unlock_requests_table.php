<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('early_unlock_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 32)->unique();
            $table->decimal('principal_amount', 14, 2);
            $table->decimal('fee_percent', 8, 2);
            $table->decimal('fee_min', 14, 2);
            $table->decimal('fee_amount', 14, 2);
            $table->decimal('credit_amount', 14, 2);
            $table->string('currency', 10)->default('USDT');
            $table->string('status', 20)->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('early_unlock_requests');
    }
};
