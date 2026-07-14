<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInService
{
    public function checkIn(Booking $booking, User $checkedInBy): UsageSession
    {
        if ($booking->status !== BookingStatus::Booked) {
            throw ValidationException::withMessages([
                'booking' => 'Chỉ booking ở trạng thái BOOKED mới có thể check-in.',
            ]);
        }

        if ($booking->usageSession()->exists()) {
            throw ValidationException::withMessages([
                'booking' => 'Booking này đã có usage session.',
            ]);
        }

        return DB::transaction(function () use ($booking, $checkedInBy): UsageSession {
            $booking->update([
                'status' => BookingStatus::CheckedIn,
            ]);

            return UsageSession::query()->create([
                'booking_id' => $booking->id,
                'equipment_id' => $booking->equipment_id,
                'user_id' => $booking->user_id,
                'checked_in_by' => $checkedInBy->id,
                'started_at' => now(),
                'status' => BookingStatus::CheckedIn,
                'notes' => 'Usage session được tạo từ check-in booking.',
            ]);
        });
    }
}
