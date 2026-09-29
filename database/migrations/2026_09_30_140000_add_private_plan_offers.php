<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('visibility', 20)->default('public')->after('is_active');
            $table->string('offer_status', 20)->nullable()->after('visibility');
            $table->index(['visibility', 'is_active']);
        });

        Schema::create('plan_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->unsignedInteger('duration_days');
            $table->decimal('annual_profit_percent', 8, 2);
            $table->string('currency', 10)->default('USDT');
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_offers');

        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex(['visibility', 'is_active']);
            $table->dropColumn(['visibility', 'offer_status']);
        });
    }
};
