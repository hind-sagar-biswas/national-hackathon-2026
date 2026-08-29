<?php

namespace App\Utils;

use App\Enums\Permission;
use App\Enums\Role;

class RolePermissionMap
{
    /**
     * Mapping for Roles and Permissions
     *
     * @var array<string, array<string>>
     */
    private array $map = [
        Role::ADMIN->value => [
            // Dashboards
            Permission::VIEW_ADMIN_DASHBOARD->value,

            // User Management
            Permission::VIEW_USERS->value,
            Permission::VIEW_USER->value,
            Permission::CREATE_USERS->value,
            Permission::UPDATE_USERS->value,
            Permission::DELETE_USERS->value,

            // Banking & Financial Oversight
            Permission::VIEW_DEPOSITS->value,
            Permission::CONFIRM_DEPOSITS->value,
            Permission::REJECT_DEPOSITS->value,
            Permission::VIEW_HOLDS->value,
            Permission::RELEASE_HOLDS->value,
            Permission::CAPTURE_HOLDS->value,
            Permission::VIEW_RECONCILIATION->value,
            Permission::RUN_RECONCILIATION_AUDIT->value,
            Permission::RUN_RECONCILIATION_ROLLUP->value,
            Permission::VIEW_ALL_TRANSACTIONS->value,
            Permission::VIEW_TRANSACTION->value,
            Permission::VIEW_TRANSFERS->value,
            Permission::VIEW_LOANS->value,
            Permission::VIEW_MONEY_REQUESTS->value,
        ],
        Role::USER->value => [
            // Dashboards
            Permission::VIEW_USER_DASHBOARD->value,

            // Transfers
            Permission::VIEW_TRANSFERS->value,
            Permission::CREATE_TRANSFERS->value,

            // Money Requests (P2P Invoicing)
            Permission::VIEW_MONEY_REQUESTS->value,
            Permission::CREATE_MONEY_REQUESTS->value,
            Permission::APPROVE_MONEY_REQUESTS->value,
            Permission::REJECT_MONEY_REQUESTS->value,
            Permission::DELETE_MONEY_REQUESTS->value,

            // Peer-to-Peer Loans
            Permission::VIEW_LOANS->value,
            Permission::VIEW_LOAN->value,
            Permission::CREATE_LOANS->value,
            Permission::REPAY_LOANS->value,
            Permission::WAIVE_LOANS->value,

            // Deposits
            Permission::VIEW_DEPOSITS->value,
            Permission::CREATE_DEPOSITS->value,

            // Transactions Explorer
            Permission::VIEW_TRANSACTIONS->value,
            Permission::VIEW_TRANSACTION->value,

            // Profile
            Permission::DELETE_ACCOUNT->value,
        ],
    ];

    public function __construct()
    {
        if (config('app.feature.user_ban')) {
            $this->map[Role::ADMIN->value][] = Permission::TOGGLE_USERS->value;
        }
    }

    /**
     * Get the permissions for a given role
     *
     * @return array<string>
     */
    public function getPermissionsForRole(string $role): array
    {
        return $this->map[$role] ?? [];
    }
}
