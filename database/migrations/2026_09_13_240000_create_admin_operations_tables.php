<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('expected_daily_reward');
            $table->string('kyc_status', 20)->default('none')->after('is_blocked');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_featured');
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->timestamps();
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 32)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payout_address');
            $table->string('network_label')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('epochs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('number')->unique();
            $table->decimal('reward_rate', 8, 6);
            $table->unsignedTinyInteger('epochs_per_day');
            $table->unsignedInteger('contracts_settled')->default(0);
            $table->decimal('total_rewards', 14, 2)->default(0);
            $table->foreignId('triggered_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('epoch_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('epoch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['epoch_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epoch_rewards');
        Schema::dropIfExists('epochs');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('platform_settings');

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'kyc_status']);
        });
    }
};
