<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE withdrawals MODIFY gateway_state VARCHAR(32) NULL');
        DB::statement('ALTER TABLE payment_webhook_logs MODIFY processing_result TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE withdrawals MODIFY gateway_state VARCHAR(8) NULL');
        DB::statement('ALTER TABLE payment_webhook_logs MODIFY processing_result VARCHAR(255) NULL');
    }
};
