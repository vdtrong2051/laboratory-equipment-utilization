<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UsageSessionService
{
    public function paginatedList(array $filters = []): LengthAwarePaginator
    {
        return UsageSession::query()
            ->with(['booking', 'equipment', 'user', 'checkedInBy', 'completedBy'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['equipment_id'] ?? null, fn ($query, string $equipmentId) => $query->where('equipment_id', $equipmentId))
            ->latest('started_at')
            ->paginate(12)
            ->withQueryString();
    }

    public function complete(UsageSession $usageSession, User $completedBy): UsageSession
    {
        if (! in_array($usageSession->status, [BookingStatus::CheckedIn, BookingStatus::Overdue], true)) {
            throw ValidationException::withMessages([
                'usage_session' => 'Chỉ phiên đang CHECKED_IN hoặc OVERDUE mới có thể hoàn tất.',
            ]);
        }

        return DB::transaction(function () use ($usageSession, $completedBy): UsageSession {
            $usageSession->update([
                'status' => BookingStatus::Completed,
                'completed_by' => $completedBy->id,
                'ended_at' => now(),
            ]);

            $usageSession->booking?->update([
                'status' => BookingStatus::Completed,
            ]);

            return $usageSession->refresh();
        });
    }
}
