<?php

namespace App\Rules;

use App\Support\BitcoinAddressValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BitcoinPayoutAddress implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(BitcoinAddressValidator::class)->isValid(is_string($value) ? $value : null)) {
            $fail(__('coin.wallet.btc_payout_address_invalid'));
        }
    }
}
