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

        if ($this->foreignKeyExists('referral_commissions', 'referral_commissions_contract_id_foreign')) {
            Schema::table('referral_commissions', function (Blueprint $table) {
                $table->dropForeign(['contract_id']);
            });
        }

        Schema::table('referral_commissions', function (Blueprint $table) {
            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('referral_commissions') || ! $this->foreignKeyExists('referral_commissions', 'referral_commissions_contract_id_foreign')) {
            return;
        }

        Schema::table('referral_commissions', function (Blueprint $table) {
            $table->dropForeign(['contract_id']);
        });

        Schema::table('referral_commissions', function (Blueprint $table) {
            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts')
                ->cascadeOnDelete();
        });
    }

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $foreignKey)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};
