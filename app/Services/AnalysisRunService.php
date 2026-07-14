<?php

namespace App\Services;

use App\Models\AnalysisRun;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AnalysisRunService
{
    public function paginatedList(array $filters = []): LengthAwarePaginator
    {
        return AnalysisRun::query()
            ->with('triggeredBy')
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['trigger_source'] ?? null, fn ($query, string $triggerSource) => $query->where('trigger_source', $triggerSource))
            ->latest('started_at')
            ->paginate(12)
            ->withQueryString();
    }
}
