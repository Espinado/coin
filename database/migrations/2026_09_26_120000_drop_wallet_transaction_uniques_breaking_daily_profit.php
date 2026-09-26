<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM wallet_transactions'))
            ->pluck('Key_name')
            ->unique();

        Schema::table('wallet_transactions', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('wallet_transactions_user_source_type_unique')) {
                $table->dropUnique('wallet_transactions_user_source_type_unique');
            }

            if ($indexes->contains('wallet_transactions_reference_type_unique')) {
                $table->dropUnique('wallet_transactions_reference_type_unique');
            }
        });
    }

    public function down(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM wallet_transactions'))
            ->pluck('Key_name')
            ->unique();

        Schema::table('wallet_transactions', function (Blueprint $table) use ($indexes) {
            if (! $indexes->contains('wallet_transactions_user_source_type_unique')) {
                $table->unique(['user_id', 'source', 'type'], 'wallet_transactions_user_source_type_unique');
            }

            if (! $indexes->contains('wallet_transactions_reference_type_unique')) {
                $table->unique(
                    ['reference_type', 'reference_id', 'type'],
                    'wallet_transactions_reference_type_unique',
                );
            }
        });
    }
};
