<?php

namespace App\Rules;

use App\Support\DisposableEmailDomains;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotDisposableEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (DisposableEmailDomains::isBlocked(is_string($value) ? $value : null)) {
            $fail(__('coin.auth.email_disposable'));
        }
    }
}
