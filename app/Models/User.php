<?php

namespace App\Models;

use App\Helpers\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_name',
        'industry',
        'company_size',
        'company_size_employees',
        'location',
        'contact_number',
        'department',
        'role',
        'permissions'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        $rolePermissions = Role::permissionsFor($this->role ?? '');
        $userPermissions = $this->permissions ?? [];
        $allPermissions = array_merge($rolePermissions, $userPermissions);

        return in_array($permission, $allPermissions, true);
    }
}