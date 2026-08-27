<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Traits\HasProfilePhoto;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? null;
        $role = $filters['role'] ?? null;
        $isActive = $filters['is_active'] ?? null;

        $query->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");

            if (config('fortify.username') !== 'email') {
                $query->orWhere(config('fortify.username'), 'like', "%{$search}%");
            }
        })->when($role, function ($query, $role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        })->when(! is_null($isActive), function ($query) use ($isActive) {
            $query->where('is_active', $isActive);
        });

        return $query;
    }

    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=5e81ac&background=dfe5ed';
    }

    /**
     * Get the superadmin account.
     */
    public static function super(): ?self
    {
        $admin = Admin::super();

        return ($admin) ? $admin->user : null;
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }
}
