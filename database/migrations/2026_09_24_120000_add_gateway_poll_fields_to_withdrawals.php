<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->timestamp('gateway_poll_checked_at')->nullable()->after('sent_at');
            $table->string('gateway_poll_summary', 500)->nullable()->after('gateway_poll_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_poll_checked_at',
                'gateway_poll_summary',
            ]);
        });
    }
};
