<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_webhook_logs', function (Blueprint $table) {
            $table->dropIndex(['idempotency_key']);
            $table->unique(['gateway', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::table('payment_webhook_logs', function (Blueprint $table) {
            $table->dropUnique(['gateway', 'idempotency_key']);
            $table->index('idempotency_key');
        });
    }
};
