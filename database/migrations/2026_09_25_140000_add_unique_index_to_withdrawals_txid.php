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
            UPDATE withdrawals w1
            INNER JOIN withdrawals w2
                ON w1.txid = w2.txid
                AND w1.id < w2.id
            SET w1.txid = NULL
            WHERE w1.txid IS NOT NULL
                AND w1.txid != \'\'
        ');

        $indexes = collect(DB::select('SHOW INDEX FROM withdrawals'))
            ->pluck('Key_name')
            ->unique();

        if (! $indexes->contains('withdrawals_txid_unique')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->unique('txid');
            });
        }
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropUnique(['txid']);
        });
    }
};
