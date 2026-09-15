<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_profit_credit')->default(true)->after('email_two_factor_enabled');
            $table->boolean('notify_contract_expiry')->default(true)->after('notify_profit_credit');
            $table->boolean('notify_maturity_alerts')->default(false)->after('notify_contract_expiry');
            $table->boolean('notify_referral_activity')->default(false)->after('notify_maturity_alerts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notify_profit_credit',
                'notify_contract_expiry',
                'notify_maturity_alerts',
                'notify_referral_activity',
            ]);
        });
    }
};
