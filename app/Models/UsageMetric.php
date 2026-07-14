<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['equipment_id', 'period_start', 'period_end', 'total_booked_minutes', 'total_powered_minutes', 'total_active_minutes', 'total_idle_minutes', 'booking_utilization_rate', 'actual_utilization_rate', 'powered_idle_rate', 'calculated_at'])]
class UsageMetric extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'booking_utilization_rate' => 'decimal:2',
            'actual_utilization_rate' => 'decimal:2',
            'powered_idle_rate' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
