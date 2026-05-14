<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'Harware',
        'Software License',
        'Furniture',
        'Infrascture',
        'Vehicle',
    ];

    public const CONDITIONS = [
        'Good',
        'Fair',
        'Excellent',
        'Poor',
        'at risk',
    ];

    protected $fillable = [
        'asset_name',
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
