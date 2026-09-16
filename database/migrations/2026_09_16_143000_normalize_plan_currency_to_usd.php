<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $base = (string) config('coin.wallet.base_currency', 'USDT');

        foreach (['plans', 'contracts', 'wallets', 'wallet_transactions', 'withdrawals', 'deposits', 'referral_commissions'] as $table) {
            if (! $this->tableHasColumn($table, 'currency')) {
                continue;
            }

            DB::table($table)->where('currency', 'USD')->update(['currency' => $base]);
        }

        if ($this->tableHasColumn('deposits', 'credited_currency')) {
            DB::table('deposits')->where('credited_currency', 'USD')->update(['credited_currency' => $base]);
        }

        if (DB::getSchemaBuilder()->hasTable('platform_settings')) {
            $legacyRate = DB::table('platform_settings')->where('key', 'btc_per_usd')->value('value');

            if ($legacyRate !== null && ! DB::table('platform_settings')->where('key', 'btc_per_usdt')->exists()) {
                DB::table('platform_settings')->insert([
                    'key' => 'btc_per_usdt',
                    'value' => $legacyRate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Irreversible data normalization.
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        return DB::getSchemaBuilder()->hasTable($table)
            && DB::getSchemaBuilder()->hasColumn($table, $column);
    }
};
