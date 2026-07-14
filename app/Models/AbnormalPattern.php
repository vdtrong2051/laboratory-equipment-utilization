<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['equipment_id', 'usage_session_id', 'rule_name', 'severity', 'status', 'message', 'evidence', 'detected_at', 'reviewed_by', 'reviewed_at', 'resolved_by', 'resolved_at', 'resolution_note'])]
class AbnormalPattern extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'detected_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'resolved_at' => 'datetime',
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

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
