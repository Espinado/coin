<?php

namespace App\Services;

use App\Models\Admin;
use App\Support\AdminAbility;
use App\Support\AdminRole;

class AdminAuthorization
{
    /** @var array<AdminRole, list<string>> */
    private const ROLE_ABILITIES = [
        AdminRole::Superadmin->value => ['*'],
        AdminRole::Operator->value => [
            AdminAbility::ManageUsers,
            AdminAbility::ManageDeposits,
            AdminAbility::ManageWithdrawals,
            AdminAbility::ManagePlanChanges,
            AdminAbility::ManagePlans,
            AdminAbility::ManageSupport,
        ],
        AdminRole::Viewer->value => [],
    ];

    public function allows(Admin $admin, string $ability): bool
    {
        $role = $admin->adminRole();
        $abilities = self::ROLE_ABILITIES[$role->value] ?? [];

        if (in_array('*', $abilities, true)) {
            return true;
        }

        return in_array($ability, $abilities, true);
    }
}
