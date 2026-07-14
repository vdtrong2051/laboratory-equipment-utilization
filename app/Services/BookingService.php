<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class BookingService
{
    public function paginatedList(array $filters = []): LengthAwarePaginator
    {
        return Booking::query()
            ->with(['equipment', 'user', 'creator'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['equipment_id'] ?? null, fn ($query, string $equipmentId) => $query->where('equipment_id', $equipmentId))
            ->when($filters['user_id'] ?? null, fn ($query, string $userId) => $query->where('user_id', $userId))
            ->when($filters['date'] ?? null, function ($query, string $date): void {
                $query->whereDate('start_time', $date);
            })
            ->latest('start_time')
            ->paginate(12)
            ->withQueryString();
    }

    public function create(array $data, User $creator): Booking
    {
        return Booking::query()->create([
            ...$data,
            'created_by' => $creator->id,
            'status' => BookingStatus::Booked,
        ]);
    }

    public function cancel(Booking $booking): Booking
    {
        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        return $booking->refresh();
    }

    public function hasScheduleConflict(
        int $equipmentId,
        Carbon $startTime,
        Carbon $endTime,
        ?int $ignoreBookingId = null,
    ): bool {
        return Booking::query()
            ->where('equipment_id', $equipmentId)
            ->whereIn('status', [
                BookingStatus::Booked->value,
                BookingStatus::CheckedIn->value,
                BookingStatus::Overdue->value,
            ])
            ->when($ignoreBookingId, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }
}
