<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_id',
        'maintenance_type',
        'priority',
        'scheduled_date',
        'assigned_to',
        'description',
        'estimated_cost',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'estimated_cost' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
