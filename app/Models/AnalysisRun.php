<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'status',
    'trigger_source',
    'triggered_by',
    'period_start',
    'period_end',
    'processed_equipment',
    'operational_status_evaluated',
    'usage_metrics_calculated',
    'rule_sets_evaluated',
    'matched_rules',
    'summary',
    'error_message',
    'started_at',
    'finished_at',
])]
class AnalysisRun extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'summary' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
