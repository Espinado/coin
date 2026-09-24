<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 16);
            $table->foreignId('deposit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference', 64)->nullable();
            $table->string('source', 24);
            $table->string('event_type', 32);
            $table->string('previous_status', 32)->nullable();
            $table->string('new_status', 32)->nullable();
            $table->string('gateway', 32)->nullable();
            $table->string('gateway_state', 32)->nullable();
            $table->string('gateway_result', 64)->nullable();
            $table->string('status_reason', 64)->nullable();
            $table->string('result', 24)->nullable();
            $table->string('title', 255);
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'created_at']);
            $table->index('deposit_id');
            $table->index('withdrawal_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_status_logs');
    }
};
