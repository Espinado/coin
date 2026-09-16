<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->decimal('credited_amount', 16, 2)->nullable()->after('currency');
            $table->string('credited_currency', 8)->nullable()->after('credited_amount');
            $table->decimal('exchange_rate', 16, 8)->nullable()->after('credited_currency');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn(['credited_amount', 'credited_currency', 'exchange_rate']);
        });
    }
};
