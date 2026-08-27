<?php

namespace App\Utils;

use App\Enums\Permission;
use App\Enums\Role;

class RolePermissionMap
{
    /**
     * Mapping for Roles and Permissions
     *
     * @var array
     */
    private $map = [
        Role::ADMIN->value => [
            Permission::VIEW_USERS->value,
            Permission::DELETE_USERS->value,
        ],
        Role::USER->value => [
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
     */
    public function getPermissionsForRole(string $role): array
    {
        return $this->map[$role] ?? [];
    }
}
