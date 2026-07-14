<?php

namespace App\Services;

use App\Models\Equipment;
use App\Enums\AnalysisStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EquipmentService
{
    public function paginatedList(array $filters = []): LengthAwarePaginator
    {
        return Equipment::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('equipment_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->withCount(['abnormalPatterns' => fn ($query) => $query->where('status', 'open')])
            ->when($filters['laboratory'] ?? null, fn ($query, string $laboratory) => $query->where('laboratory', $laboratory))
            ->when($filters['usage_mode'] ?? null, fn ($query, string $usageMode) => $query->where('usage_mode', $usageMode))
            ->when($filters['analysis_status'] ?? null, fn ($query, string $status) => $query->where('current_analysis_status', $status))
            ->when($filters['operational_status'] ?? null, fn ($query, string $status) => $query->where('current_operational_status', $status))
            ->when($filters['has_abnormal'] ?? null, fn ($query) => $query->whereHas('abnormalPatterns', fn ($subQuery) => $subQuery->where('status', 'open')))
            ->when($filters['attention'] ?? null, function ($query): void {
                $query->where(function ($query): void {
                    $query->where('current_analysis_status', '!=', AnalysisStatus::Normal)
                        ->orWhereHas('abnormalPatterns', fn ($subQuery) => $subQuery->where('status', 'open'));
                });
            })
            ->orderBy('laboratory')
            ->orderBy('equipment_code')
            ->paginate(12)
            ->withQueryString();
    }

    public function laboratories(): Collection
    {
        return Equipment::query()
            ->select('laboratory')
            ->distinct()
            ->orderBy('laboratory')
            ->get();
    }

    public function create(array $data): Equipment
    {
        return Equipment::query()->create($data);
    }

    public function update(Equipment $equipment, array $data): Equipment
    {
        $equipment->update($data);

        return $equipment->refresh();
    }

    public function delete(Equipment $equipment): void
    {
        $equipment->delete();
    }
}
