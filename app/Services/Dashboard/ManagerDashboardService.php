<?php

namespace App\Services\Dashboard;

use App\DTOs\DashboardSummaryData;
use App\Enums\AnalysisStatus;
use App\Enums\BookingStatus;
use App\Enums\OperationalStatus;
use App\Models\AbnormalPattern;
use App\Models\AnalysisRun;
use App\Models\Equipment;
use App\Models\UsageMetric;
use App\Models\User;

class ManagerDashboardService implements DashboardSummaryService
{
    public function summaryFor(User $user): DashboardSummaryData
    {
        if (Equipment::getConnectionResolver() === null) {
            return new DashboardSummaryData(
                cards: [
                    'role' => 'manager',
                    'user' => $user->name,
                    'scope' => 'utilization_analysis',
                ],
            );
        }

        $equipmentCount = Equipment::query()->count();
        $openAbnormalCount = AbnormalPattern::query()->where('status', 'open')->count();
        $capacityPressureCount = Equipment::query()->where('current_analysis_status', AnalysisStatus::CapacityPressure)->count();
        $underutilizedCount = Equipment::query()->where('current_analysis_status', AnalysisStatus::Underutilized)->count();
        $idleWhilePoweredCount = Equipment::query()->where('current_analysis_status', AnalysisStatus::IdleWhilePowered)->count();
        $activeCount = Equipment::query()->where('current_operational_status', OperationalStatus::Active)->count();
        $checkedInSessionCount = Equipment::query()
            ->whereHas('usageSessions', fn ($query) => $query->where('status', BookingStatus::CheckedIn))
            ->count();

        $latestMetrics = UsageMetric::query()
            ->with('equipment')
            ->latest('calculated_at')
            ->limit(8)
            ->get();

        return new DashboardSummaryData(
            cards: [
                'role' => 'manager',
                'user' => $user->name,
                'scope' => 'utilization_analysis',
                'equipment_count' => $equipmentCount,
                'open_abnormal_count' => $openAbnormalCount,
                'active_equipment_count' => $activeCount,
                'checked_in_equipment_count' => $checkedInSessionCount,
                'capacity_pressure_count' => $capacityPressureCount,
                'underutilized_count' => $underutilizedCount,
                'idle_while_powered_count' => $idleWhilePoweredCount,
                'average_actual_utilization_rate' => round((float) ($latestMetrics->avg('actual_utilization_rate') ?? 0), 2),
                'average_powered_idle_rate' => round((float) ($latestMetrics->avg('powered_idle_rate') ?? 0), 2),
            ],
            tables: [
                'attention_equipments' => $this->attentionEquipments(),
                'open_abnormal_patterns' => $this->openAbnormalPatterns(),
                'latest_usage_metrics' => $latestMetrics,
                'latest_analysis_runs' => $this->latestAnalysisRuns(),
            ],
            charts: [
                'analysis_status_counts' => [
                    AnalysisStatus::Normal->value => Equipment::query()->where('current_analysis_status', AnalysisStatus::Normal)->count(),
                    AnalysisStatus::Underutilized->value => $underutilizedCount,
                    AnalysisStatus::CapacityPressure->value => $capacityPressureCount,
                    AnalysisStatus::IdleWhilePowered->value => $idleWhilePoweredCount,
                ],
                'operational_status_counts' => [
                    OperationalStatus::Off->value => Equipment::query()->where('current_operational_status', OperationalStatus::Off)->count(),
                    OperationalStatus::PoweredIdle->value => Equipment::query()->where('current_operational_status', OperationalStatus::PoweredIdle)->count(),
                    OperationalStatus::Active->value => $activeCount,
                ],
            ],
        );
    }

    private function attentionEquipments()
    {
        return Equipment::query()
            ->withCount(['abnormalPatterns' => fn ($query) => $query->where('status', 'open')])
            ->where(function ($query): void {
                $query->where('current_analysis_status', '!=', AnalysisStatus::Normal)
                    ->orWhere('utilization_rate', '>', 85)
                    ->orWhereHas('abnormalPatterns', fn ($subQuery) => $subQuery->where('status', 'open'));
            })
            ->orderByDesc('abnormal_patterns_count')
            ->orderByDesc('utilization_rate')
            ->limit(8)
            ->get();
    }

    private function openAbnormalPatterns()
    {
        return AbnormalPattern::query()
            ->with(['equipment', 'usageSession'])
            ->where('status', 'open')
            ->latest('detected_at')
            ->limit(8)
            ->get();
    }

    private function latestAnalysisRuns()
    {
        return AnalysisRun::query()
            ->with('triggeredBy')
            ->latest('started_at')
            ->limit(8)
            ->get();
    }
}
