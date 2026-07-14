<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['equipment_id', 'usage_session_id', 'signal_type', 'signal_value', 'unit', 'is_active', 'source', 'gateway_id', 'recorded_by', 'recorded_at', 'raw_payload'])]
class ActivitySignal extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'recorded_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function usageSession(): BelongsTo
    {
        return $this->belongsTo(UsageSession::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
