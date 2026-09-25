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
                ON t1.reference_type = t2.reference_type
                AND t1.reference_id = t2.reference_id
                AND t1.type = t2.type
                AND t1.id < t2.id
            WHERE t1.reference_type IS NOT NULL
                AND t1.reference_id IS NOT NULL
        ');

        $indexes = collect(DB::select('SHOW INDEX FROM wallet_transactions'))
            ->pluck('Key_name')
            ->unique();

        if (! $indexes->contains('wallet_transactions_reference_type_unique')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->unique(
                    ['reference_type', 'reference_id', 'type'],
                    'wallet_transactions_reference_type_unique',
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique('wallet_transactions_reference_type_unique');
        });
    }
};
