<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            DELETE t1 FROM payment_webhook_logs t1
            INNER JOIN payment_webhook_logs t2
                ON t1.gateway = t2.gateway
                AND t1.idempotency_key = t2.idempotency_key
                AND t1.id < t2.id
        ');

        $indexes = collect(DB::select('SHOW INDEX FROM payment_webhook_logs'))
            ->pluck('Key_name')
            ->unique();

        if ($indexes->contains('payment_webhook_logs_idempotency_key_index')) {
            Schema::table('payment_webhook_logs', function (Blueprint $table) {
                $table->dropIndex(['idempotency_key']);
            });
        }

        if (! $indexes->contains('payment_webhook_logs_gateway_idempotency_key_unique')) {
            Schema::table('payment_webhook_logs', function (Blueprint $table) {
                $table->unique(['gateway', 'idempotency_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('payment_webhook_logs', function (Blueprint $table) {
            $table->dropUnique(['gateway', 'idempotency_key']);
            $table->index('idempotency_key');
        });
    }
};
