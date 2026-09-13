<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avg_epoch_label')->nullable()->after('expected_daily_reward');
            $table->string('availability_label')->nullable()->after('avg_epoch_label');
            $table->string('load_label')->nullable()->after('availability_label');
            $table->string('next_expiry_label')->nullable()->after('load_label');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedTinyInteger('capacity_percent')->nullable()->after('is_featured');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->string('started_label')->nullable()->after('progress_percent');
            $table->string('ends_label')->nullable()->after('started_label');
            $table->string('location_label')->nullable()->after('ends_label');
            $table->string('completed_summary')->nullable()->after('location_label');
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->string('pending_note')->nullable()->after('payout_address');
            $table->string('network_label')->nullable()->after('pending_note');
            $table->string('min_withdrawal_label')->nullable()->after('network_label');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('status_label')->nullable()->after('amount_tone');
        });

        Schema::create('referral_accruals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('user_label');
            $table->string('level_label');
            $table->string('plan_name');
            $table->string('amount_label');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_accruals');

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('status_label');
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['pending_note', 'network_label', 'min_withdrawal_label']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['started_label', 'ends_label', 'location_label', 'completed_summary']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('capacity_percent');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avg_epoch_label', 'availability_label', 'load_label', 'next_expiry_label']);
        });
    }
};
