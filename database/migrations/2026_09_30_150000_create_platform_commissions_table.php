<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 40);
            $table->string('reference', 64);
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('USDT');
            $table->nullableMorphs('source');
            $table->timestamp('processed_at');
            $table->timestamps();

            $table->index(['kind', 'processed_at']);
            $table->unique(['kind', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_commissions');
    }
};
