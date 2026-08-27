<?php

namespace App\Models;

use App\Enums\Role;
use App\Models\Traits\IsRoleModel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasUlids;
    use IsRoleModel;

    public Role $role = Role::ADMIN;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_super' => 'boolean',
        ];
    }

    /**
     * Get the superadmin account.
     */
    public static function super(): ?self
    {
        return self::where('is_super', true)->first();
    }
}
