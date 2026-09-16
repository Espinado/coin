<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $currency = (string) config('coin.wallet.base_currency', 'USDT');

        DB::table('plans')
            ->orderBy('id')
            ->get(['id', 'price_label', 'min_deposit', 'price_amount'])
            ->each(function ($plan) use ($currency): void {
                $label = trim((string) ($plan->price_label ?? ''));

                if ($label === '' || ! str_starts_with($label, '$')) {
                    return;
                }

                $amount = $plan->min_deposit ?? $plan->price_amount;

                if ($amount === null) {
                    $numeric = preg_replace('/[^0-9.]/', '', substr($label, 1));

                    if ($numeric === '' || ! is_numeric($numeric)) {
                        return;
                    }

                    $amount = (float) $numeric;
                }

                DB::table('plans')
                    ->where('id', $plan->id)
                    ->update([
                        'price_label' => number_format((float) $amount, 0, '.', ',').' '.$currency,
                    ]);
            });
    }

    public function down(): void
    {
        // Display labels only; no rollback needed.
    }
};
