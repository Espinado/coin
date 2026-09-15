<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('referral_commissions') || ! Schema::hasTable('contracts')) {
            return;
        }

        DB::table('referral_commissions')
            ->whereNotIn('contract_id', DB::table('contracts')->select('id'))
            ->delete();

        if (Schema::hasTable('referral_profiles')) {
            $referrerIds = DB::table('referral_commissions')
                ->distinct()
                ->pluck('referrer_user_id');

            foreach ($referrerIds as $referrerId) {
                $total = (float) DB::table('referral_commissions')
                    ->where('referrer_user_id', $referrerId)
                    ->sum('commission_amount');

                DB::table('referral_profiles')
                    ->where('user_id', $referrerId)
                    ->update(['total_rewards' => $total]);
            }
        }

        if ($this->contractForeignKeyExists()) {
            return;
        }

        Schema::table('referral_commissions', function (Blueprint $table) {
            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('referral_commissions') || ! $this->contractForeignKeyExists()) {
            return;
        }

        Schema::table('referral_commissions', function (Blueprint $table) {
            $table->dropForeign(['contract_id']);
        });
    }

    private function contractForeignKeyExists(): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.REFERENTIAL_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', 'referral_commissions')
            ->where('REFERENCED_TABLE_NAME', 'contracts')
            ->exists();
    }
};
