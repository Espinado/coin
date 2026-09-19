<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->decimal('exchange_rate', 16, 8)->nullable()->after('currency');
            $table->decimal('usdt_per_btc', 16, 8)->nullable()->after('exchange_rate');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate', 'usdt_per_btc']);
        });
    }
};
