<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('payment_address')->nullable()->after('external_reference');
            $table->string('gateway_uniq_id')->nullable()->unique()->after('payment_address');
            $table->string('gateway_network', 16)->nullable()->after('gateway_uniq_id');
            $table->string('txid')->nullable()->after('gateway_network');
            $table->decimal('received_amount', 14, 8)->nullable()->after('txid');
            $table->timestamp('expires_at')->nullable()->after('received_amount');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('gateway_request_id')->nullable()->after('network_label');
            $table->string('txid')->nullable()->after('gateway_request_id');
            $table->string('gateway_state', 8)->nullable()->after('txid');
            $table->timestamp('sent_at')->nullable()->after('gateway_state');
        });

        Schema::create('payment_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 32);
            $table->string('event_type', 32);
            $table->json('payload');
            $table->boolean('signature_valid')->default(false);
            $table->string('idempotency_key');
            $table->foreignId('deposit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('processing_result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'event_type']);
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_logs');

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_request_id',
                'txid',
                'gateway_state',
                'sent_at',
            ]);
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn([
                'payment_address',
                'gateway_uniq_id',
                'gateway_network',
                'txid',
                'received_amount',
                'expires_at',
            ]);
        });
    }
};
