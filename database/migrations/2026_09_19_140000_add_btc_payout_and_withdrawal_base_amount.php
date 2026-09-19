<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->string('btc_payout_address')->nullable()->after('network_label');
            $table->string('btc_network_label')->nullable()->after('btc_payout_address');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->decimal('base_amount', 12, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('base_amount');
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['btc_payout_address', 'btc_network_label']);
        });
    }
};
