<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    public const CONDITIONS = [
        'Excellent',
        'Good',
        'Fair',
        'Poor',
        'At Risk',
    ];

    protected $fillable = [
        'user_id',
        'asset_name',
        'category_id',
        'category',
        'asset_code',
        'description',
        'purchase_date',
        'purchase_cost',
        'condition',
        'location',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
