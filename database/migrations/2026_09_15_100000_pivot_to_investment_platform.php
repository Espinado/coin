<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('telegram', 64)->nullable()->after('phone');
            $table->string('country_code', 2)->nullable()->after('telegram');
            $table->timestamp('last_login_at')->nullable()->after('country_code');
            $table->text('admin_lead_note')->nullable()->after('kyc_status');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('min_deposit', 14, 2)->nullable()->after('price_label');
            $table->decimal('price_amount', 14, 2)->nullable()->after('min_deposit');
            $table->decimal('annual_profit_percent', 8, 2)->nullable()->after('price_amount');
            $table->string('currency', 8)->default('USDT')->after('annual_profit_percent');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('principal_amount', 14, 2)->nullable()->after('status');
            $table->string('currency', 8)->default('USDT')->after('principal_amount');
            $table->decimal('annual_profit_percent', 8, 2)->nullable()->after('currency');
            $table->timestamp('started_at')->nullable()->after('annual_profit_percent');
            $table->timestamp('ends_at')->nullable()->after('started_at');
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->string('currency', 8)->default('USDT')->after('user_id');
            $table->decimal('locked_balance', 14, 2)->default(0)->after('pending');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->decimal('amount', 14, 2)->nullable()->after('user_id');
            $table->string('currency', 8)->nullable()->after('amount');
            $table->string('reference_type')->nullable()->after('status_label');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->timestamp('occurred_at')->nullable()->after('reference_id');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('currency', 8)->default('USDT')->after('amount');
            $table->string('withdrawal_type', 32)->default('available_balance')->after('currency');
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 8)->default('USDT');
            $table->string('status', 20)->default('pending');
            $table->string('method', 32)->default('mock');
            $table->string('external_reference')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referral_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->decimal('purchase_amount', 14, 2);
            $table->decimal('commission_percent', 5, 2);
            $table->decimal('commission_amount', 14, 2);
            $table->string('currency', 8)->default('USDT');
            $table->timestamps();

            $table->unique('contract_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
        Schema::dropIfExists('deposits');

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['currency', 'withdrawal_type']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['amount', 'currency', 'reference_type', 'reference_id', 'occurred_at']);
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['currency', 'locked_balance']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'principal_amount',
                'currency',
                'annual_profit_percent',
                'started_at',
                'ends_at',
            ]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'min_deposit',
                'price_amount',
                'annual_profit_percent',
                'currency',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'telegram',
                'country_code',
                'last_login_at',
                'admin_lead_note',
            ]);
        });
    }
};
