<?php

namespace App\Models;

use App\Helpers\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = [
        'user_photo_url',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'company_name',
        'industry',
        'company_size',
        'company_size_employees',
        'location',
        'contact_number',
        'department',
        'role',
        'status',
        'user_photo',
        'permissions',
        'otp_code',
        'otp_expires_at',
        'otp_verified_at'
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
            'otp_expires_at' => 'datetime',
            'otp_verified_at' => 'datetime',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        $rolePermissions = Role::permissionsFor($this->role ?? '');
        $userPermissions = $this->permissions ?? [];
        $allPermissions = array_merge($rolePermissions, $userPermissions);

        return in_array($permission, $allPermissions, true);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function companyId(): int
    {
        return $this->company_id ?? $this->id;
    }

    public function getUserPhotoUrlAttribute(): ?string
    {
        $path = $this->user_photo;
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return url($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return url('/' . $path);
        }

        return Storage::url($path);
    }
}