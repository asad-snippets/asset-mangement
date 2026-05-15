<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
