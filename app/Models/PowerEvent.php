<?php

namespace App\Models;

use App\Enums\PowerEventType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['equipment_id', 'event_type', 'source', 'gateway_id', 'recorded_by', 'recorded_at', 'raw_payload'])]
class PowerEvent extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'event_type' => PowerEventType::class,
            'recorded_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
