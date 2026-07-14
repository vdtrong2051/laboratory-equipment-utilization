<?php

namespace App\Services\Dashboard;

use App\DTOs\DashboardSummaryData;
use App\Models\User;

interface DashboardSummaryService
{
    public function summaryFor(User $user): DashboardSummaryData;
}
