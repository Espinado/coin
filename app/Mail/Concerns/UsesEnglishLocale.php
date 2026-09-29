<?php

namespace App\Mail\Concerns;

use App\Support\UserLocale;

trait UsesEnglishLocale
{
    protected function forceEnglishLocale(): static
    {
        return $this->locale(UserLocale::LOCALE);
    }
}
