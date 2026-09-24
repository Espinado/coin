<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            DELETE t1 FROM wallet_transactions t1
            INNER JOIN wallet_transactions t2
                ON t1.user_id = t2.user_id
                AND t1.source = t2.source
                AND t1.type = t2.type
                AND t1.id < t2.id
        ');

        $indexes = collect(DB::select('SHOW INDEX FROM wallet_transactions'))
            ->pluck('Key_name')
            ->unique();

        if (! $indexes->contains('wallet_transactions_user_source_type_unique')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->unique(['user_id', 'source', 'type'], 'wallet_transactions_user_source_type_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique('wallet_transactions_user_source_type_unique');
        });
    }
};
