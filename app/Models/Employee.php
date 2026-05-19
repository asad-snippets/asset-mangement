<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use HasFactory;

    protected $appends = [
        'employee_photo_url',
    ];

    public const DEPARTMENTS = [
        'IT & Infrastructure',
        'Human Resources',
        'Finance & Accounting',
        'Operations',
        'Sales & Marketing',
    ];

    protected $fillable = [
        'user_id',
        'full_name',
        'email_address',
        'department_name',
        'job_title',
        'employee_photo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function getEmployeePhotoUrlAttribute(): ?string
    {
        $path = $this->employee_photo;
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
