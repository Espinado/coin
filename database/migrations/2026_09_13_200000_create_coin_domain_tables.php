<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_slug', 16)->nullable()->after('email');
            $table->string('epoch_label')->nullable()->after('account_slug');
            $table->unsignedInteger('active_tflops')->default(0)->after('epoch_label');
            $table->string('nodes_label')->nullable()->after('active_tflops');
            $table->decimal('expected_daily_reward', 10, 2)->default(0)->after('nodes_label');
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tier_label')->nullable();
            $table->string('price_label');
            $table->unsignedInteger('tflops');
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->string('infra');
            $table->decimal('reward_multiplier', 5, 2)->default(1);
            $table->decimal('daily_estimate', 8, 2)->nullable();
            $table->unsignedInteger('max_tflops')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('available', 12, 2)->default(0);
            $table->decimal('pending', 12, 2)->default(0);
            $table->string('usd_estimate_label')->nullable();
            $table->string('payout_address')->nullable();
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('status')->default('active');
            $table->unsignedInteger('tflops');
            $table->unsignedSmallInteger('duration_days');
            $table->unsignedSmallInteger('days_elapsed')->default(0);
            $table->decimal('accrued_amount', 12, 2)->default(0);
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('occurred_label');
            $table->string('type');
            $table->string('source')->nullable();
            $table->string('amount_label');
            $table->string('amount_tone')->default('neutral');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('reward_period_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period_key');
            $table->string('period_label');
            $table->string('total_label');
            $table->timestamps();

            $table->unique(['user_id', 'period_key']);
        });

        Schema::create('referral_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->unsignedInteger('invited_count')->default(0);
            $table->unsignedInteger('active_contracts')->default(0);
            $table->decimal('total_rewards', 12, 2)->default(0);
            $table->unsignedTinyInteger('level1_percent')->default(5);
            $table->unsignedTinyInteger('level2_percent')->default(2);
            $table->unsignedInteger('level1_users')->default(0);
            $table->unsignedInteger('level2_users')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_profiles');
        Schema::dropIfExists('reward_period_totals');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('plans');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'account_slug',
                'epoch_label',
                'active_tflops',
                'nodes_label',
                'expected_daily_reward',
            ]);
        });
    }
};
