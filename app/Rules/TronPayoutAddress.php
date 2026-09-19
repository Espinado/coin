<?php

namespace App\Rules;

use App\Support\TronAddressValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TronPayoutAddress implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(TronAddressValidator::class)->isValid(is_string($value) ? $value : null)) {
            $fail(__('coin.wallet.payout_address_invalid'));
        }
    }
}
