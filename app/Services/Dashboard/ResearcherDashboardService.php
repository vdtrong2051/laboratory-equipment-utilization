<?php

namespace App\Services\Dashboard;

use App\DTOs\DashboardSummaryData;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageSession;
use App\Models\User;

class ResearcherDashboardService implements DashboardSummaryService
{
    public function summaryFor(User $user): DashboardSummaryData
    {
        if (Equipment::getConnectionResolver() === null) {
            return new DashboardSummaryData(
                cards: [
                    'role' => 'researcher',
                    'user' => $user->name,
                    'scope' => 'personal_equipment_usage',
                ],
            );
        }

        return new DashboardSummaryData(
            cards: [
                'role' => 'researcher',
                'user' => $user->name,
                'scope' => 'personal_equipment_usage',
                'my_booking_count' => Booking::query()->where('user_id', $user->id)->count(),
                'upcoming_booking_count' => Booking::query()
                    ->where('user_id', $user->id)
                    ->where('status', BookingStatus::Booked)
                    ->where('end_time', '>=', now())
                    ->count(),
                'checked_in_session_count' => UsageSession::query()
                    ->where('user_id', $user->id)
                    ->whereIn('status', [BookingStatus::CheckedIn, BookingStatus::Overdue])
                    ->count(),
                'completed_session_count' => UsageSession::query()
                    ->where('user_id', $user->id)
                    ->where('status', BookingStatus::Completed)
                    ->count(),
                'available_equipment_count' => Equipment::query()->count(),
            ],
            tables: [
                'my_upcoming_bookings' => Booking::query()
                    ->with('equipment')
                    ->where('user_id', $user->id)
                    ->whereIn('status', [BookingStatus::Booked, BookingStatus::CheckedIn, BookingStatus::Overdue])
                    ->where('end_time', '>=', now()->subDay())
                    ->orderBy('start_time')
                    ->limit(8)
                    ->get(),
                'my_recent_sessions' => UsageSession::query()
                    ->with(['equipment', 'booking'])
                    ->where('user_id', $user->id)
                    ->latest('started_at')
                    ->limit(8)
                    ->get(),
                'suggested_equipments' => Equipment::query()
                    ->orderBy('laboratory')
                    ->orderBy('equipment_code')
                    ->limit(8)
                    ->get(),
            ],
        );
    }
}
