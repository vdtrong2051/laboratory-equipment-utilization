<?php

namespace App\Services;

use App\Models\AbnormalPattern;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AbnormalPatternService
{
    public function paginatedList(array $filters = []): LengthAwarePaginator
    {
        return AbnormalPattern::query()
            ->with(['equipment', 'usageSession', 'reviewedBy', 'resolvedBy'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['severity'] ?? null, fn ($query, string $severity) => $query->where('severity', $severity))
            ->when($filters['equipment_id'] ?? null, fn ($query, string $equipmentId) => $query->where('equipment_id', $equipmentId))
            ->when($filters['rule_name'] ?? null, fn ($query, string $ruleName) => $query->where('rule_name', $ruleName))
            ->latest('detected_at')
            ->paginate(12)
            ->withQueryString();
    }

    public function review(AbnormalPattern $abnormalPattern, User $reviewedBy): AbnormalPattern
    {
        if ($abnormalPattern->status === 'resolved') {
            return $abnormalPattern;
        }

        $abnormalPattern->update([
            'status' => 'reviewed',
            'reviewed_by' => $reviewedBy->id,
            'reviewed_at' => now(),
        ]);

        return $abnormalPattern->refresh();
    }

    public function resolve(AbnormalPattern $abnormalPattern, User $resolvedBy, ?string $resolutionNote = null): AbnormalPattern
    {
        $abnormalPattern->update([
            'status' => 'resolved',
            'resolved_by' => $resolvedBy->id,
            'resolved_at' => now(),
            'reviewed_by' => $abnormalPattern->reviewed_by ?? $resolvedBy->id,
            'reviewed_at' => $abnormalPattern->reviewed_at ?? now(),
            'resolution_note' => $resolutionNote,
        ]);

        return $abnormalPattern->refresh();
    }
}
