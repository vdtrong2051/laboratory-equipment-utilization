<?php

namespace App\Services\Dashboard;

use App\DTOs\DashboardSummaryData;
use App\Enums\AnalysisStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\AbnormalPattern;
use App\Models\AnalysisRun;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageSession;
use App\Models\User;

class AdminDashboardService implements DashboardSummaryService
{
    public function summaryFor(User $user): DashboardSummaryData
    {
        if (Equipment::getConnectionResolver() === null) {
            return new DashboardSummaryData(
                cards: [
                    'role' => 'admin',
                    'user' => $user->name,
                    'scope' => 'system_configuration',
                ],
            );
        }

        return new DashboardSummaryData(
            cards: [
                'role' => 'admin',
                'user' => $user->name,
                'scope' => 'system_configuration',
                'user_count' => User::query()->count(),
                'active_user_count' => User::query()->where('is_active', true)->count(),
                'equipment_count' => Equipment::query()->count(),
                'booking_count' => Booking::query()->count(),
                'checked_in_session_count' => UsageSession::query()->where('status', BookingStatus::CheckedIn)->count(),
                'open_abnormal_count' => AbnormalPattern::query()->where('status', 'open')->count(),
                'latest_analysis_run_status' => AnalysisRun::query()->latest('started_at')->value('status'),
            ],
            tables: [
                'recent_users' => User::query()->latest()->limit(6)->get(),
                'recent_bookings' => Booking::query()->with(['equipment', 'user'])->latest()->limit(6)->get(),
                'latest_analysis_runs' => AnalysisRun::query()->with('triggeredBy')->latest('started_at')->limit(6)->get(),
            ],
            charts: [
                'role_counts' => [
                    UserRole::Admin->value => User::query()->where('role', UserRole::Admin)->count(),
                    UserRole::Manager->value => User::query()->where('role', UserRole::Manager)->count(),
                    UserRole::LabStaff->value => User::query()->where('role', UserRole::LabStaff)->count(),
                    UserRole::Researcher->value => User::query()->where('role', UserRole::Researcher)->count(),
                ],
                'analysis_status_counts' => [
                    AnalysisStatus::Normal->value => Equipment::query()->where('current_analysis_status', AnalysisStatus::Normal)->count(),
                    AnalysisStatus::Underutilized->value => Equipment::query()->where('current_analysis_status', AnalysisStatus::Underutilized)->count(),
                    AnalysisStatus::CapacityPressure->value => Equipment::query()->where('current_analysis_status', AnalysisStatus::CapacityPressure)->count(),
                    AnalysisStatus::IdleWhilePowered->value => Equipment::query()->where('current_analysis_status', AnalysisStatus::IdleWhilePowered)->count(),
                ],
            ],
        );
    }
}
