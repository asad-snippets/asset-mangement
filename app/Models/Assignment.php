<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use HasFactory;

    protected $appends = [
        'display_id',
        'numeric_id',
    ];

    protected $fillable = [
        'user_id',
        'assignment_code',
        'asset_id',
        'employee_id',
        'status',
        'assignment_date',
        'expected_return_date',
        'notes',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'expected_return_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getDisplayIdAttribute(): ?string
    {
        if ($this->assignment_code) {
            return $this->assignment_code;
        }

        $id = $this->attributes['id'] ?? null;
        if (!$id) {
            return null;
        }

        return sprintf('ASSIGN-%03d', $id);
    }

    public function getNumericIdAttribute(): ?int
    {
        $id = $this->attributes['id'] ?? null;
        return $id ? (int) $id : null;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['numeric_id'] = $this->getNumericIdAttribute();
        $array['id'] = $this->getDisplayIdAttribute();

        return $array;
    }
}
