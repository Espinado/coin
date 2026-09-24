<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('status_reason', 64)->nullable()->after('status');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('status_reason', 64)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('status_reason');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('status_reason');
        });
    }
};
