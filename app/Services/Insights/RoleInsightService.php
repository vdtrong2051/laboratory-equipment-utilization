<?php

namespace App\Services\Insights;

use App\DTOs\DashboardSummaryData;
use App\Models\User;

interface RoleInsightService
{
    /**
     * @return array{headline: string, narrative: string, context: array<int, array{label: string, value: mixed}>, next_actions: array<int, array{label: string, description: string, route?: string, method?: string, variant?: string}>}
     */
    public function insightFor(User $user, DashboardSummaryData $summary): array;
}
