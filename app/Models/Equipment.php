<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use App\Enums\OperationalStatus;
use App\Enums\UsageMode;
use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'equipment_code',
    'name',
    'type',
    'laboratory',
    'usage_mode',
    'allowed_usage_duration_minutes',
    'current_operational_status',
    'current_analysis_status',
    'last_used_at',
    'utilization_rate',
])]
class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'equipments';

    protected function casts(): array
    {
        return [
            'usage_mode' => UsageMode::class,
            'current_operational_status' => OperationalStatus::class,
            'current_analysis_status' => AnalysisStatus::class,
            'last_used_at' => 'datetime',
            'utilization_rate' => 'decimal:2',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function usageSessions(): HasMany
    {
        return $this->hasMany(UsageSession::class);
    }

    public function powerEvents(): HasMany
    {
        return $this->hasMany(PowerEvent::class);
    }

    public function activitySignals(): HasMany
    {
        return $this->hasMany(ActivitySignal::class);
    }

    public function usageMetrics(): HasMany
    {
        return $this->hasMany(UsageMetric::class);
    }

    public function abnormalPatterns(): HasMany
    {
        return $this->hasMany(AbnormalPattern::class);
    }
}
