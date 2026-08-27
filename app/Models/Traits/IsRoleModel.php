<?php

namespace App\Models\Traits;

use HindBiswas\ModelUtils\Traits\BelongsToAuth;

trait IsRoleModel
{
    use BelongsToAuth;

    public static function bootIsRoleModel(): void
    {
        static::created(function ($model) {
            if ($model->user_id && property_exists($model, 'role')) {
                $model->user->assignRole($model->role);
            }
        });
    }

    /**
     * Scope to filter by search term and order.
     *
     * @param  mixed  $query
     * @param  array<int,mixed>  $filters
     * @return mixed
     */
    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? null;

        $query->when($search, function ($query, $search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere(config('fortify.email'), 'like', "%{$search}%");

                if (config('fortify.username') !== 'email') {
                    $q->orWhere(config('fortify.username'), 'like', "%{$search}%");
                }
            });
        });

        return $query;
    }
}
