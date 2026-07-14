<?php

namespace App\Services\Dashboard;

use App\DTOs\DashboardSummaryData;
use App\Enums\BookingStatus;
use App\Enums\OperationalStatus;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageSession;
use App\Models\User;

class LabStaffDashboardService implements DashboardSummaryService
{
    public function summaryFor(User $user): DashboardSummaryData
    {
        if (Equipment::getConnectionResolver() === null) {
            return new DashboardSummaryData(
                cards: [
                    'role' => 'lab_staff',
                    'user' => $user->name,
                    'scope' => 'daily_operations',
                ],
            );
        }

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        return new DashboardSummaryData(
            cards: [
                'role' => 'lab_staff',
                'user' => $user->name,
                'scope' => 'daily_operations',
                'today_booking_count' => Booking::query()
                    ->whereBetween('start_time', [$todayStart, $todayEnd])
                    ->count(),
                'waiting_check_in_count' => Booking::query()
                    ->where('status', BookingStatus::Booked)
                    ->where('start_time', '<=', now()->addMinutes(30))
                    ->count(),
                'active_session_count' => UsageSession::query()
                    ->whereIn('status', [BookingStatus::CheckedIn, BookingStatus::Overdue])
                    ->count(),
                'overdue_session_count' => UsageSession::query()
                    ->where('status', BookingStatus::Overdue)
                    ->count(),
                'powered_idle_equipment_count' => Equipment::query()
                    ->where('current_operational_status', OperationalStatus::PoweredIdle)
                    ->count(),
                'active_equipment_count' => Equipment::query()
                    ->where('current_operational_status', OperationalStatus::Active)
                    ->count(),
            ],
            tables: [
                'upcoming_bookings' => Booking::query()
                    ->with(['equipment', 'user'])
                    ->whereIn('status', [BookingStatus::Booked, BookingStatus::CheckedIn, BookingStatus::Overdue])
                    ->where('end_time', '>=', now()->subHours(2))
                    ->orderBy('start_time')
                    ->limit(8)
                    ->get(),
                'active_sessions' => UsageSession::query()
                    ->with(['equipment', 'user', 'booking'])
                    ->whereIn('status', [BookingStatus::CheckedIn, BookingStatus::Overdue])
                    ->latest('started_at')
                    ->limit(8)
                    ->get(),
                'powered_idle_equipments' => Equipment::query()
                    ->where('current_operational_status', OperationalStatus::PoweredIdle)
                    ->orderBy('laboratory')
                    ->limit(8)
                    ->get(),
            ],
        );
    }
}
