<?php

namespace Database\Seeders;

use App\Enums\Permission as EnumsPermission;
use App\Enums\Role as EnumsRole;
use App\Utils\RolePermissionMap;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed permissions
        $allPermissions = [];
        foreach (EnumsPermission::cases() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'web',
            ]);
            $allPermissions[] = $permission->value;
        }

        // Seed roles and assign permissions
        $rolePermissionMap = new RolePermissionMap;
        foreach (EnumsRole::cases() as $role) {
            $roleModel = Role::firstOrCreate([
                'name' => $role->value,
                'guard_name' => 'web',
            ]);
            $permissions = $rolePermissionMap->getPermissionsForRole($role->value);
            // Permissions in RolePermissionMap are enum instances or values, normalize to values
            $permissionNames = array_map(function ($p) {
                return is_object($p) && method_exists($p, 'value') ? $p->value : $p;
            }, $permissions);
            $roleModel->syncPermissions($permissionNames);
        }
    }
}
