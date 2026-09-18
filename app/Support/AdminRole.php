<?php

namespace App\Support;

enum AdminRole: string
{
    case Superadmin = 'superadmin';
    case Operator = 'operator';
    case Viewer = 'viewer';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::Superadmin->value => __('coin.admin.roles.superadmin'),
            self::Operator->value => __('coin.admin.roles.operator'),
            self::Viewer->value => __('coin.admin.roles.viewer'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }
}
