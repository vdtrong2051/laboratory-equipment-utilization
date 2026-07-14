<?php

namespace App\Services\Insights;

use App\DTOs\DashboardSummaryData;
use App\Models\User;

class ResearcherInsightService implements RoleInsightService
{
    public function insightFor(User $user, DashboardSummaryData $summary): array
    {
        $cards = $summary->cards;
        $upcoming = (int) ($cards['upcoming_booking_count'] ?? 0);
        $checkedIn = (int) ($cards['checked_in_session_count'] ?? 0);
        $completed = (int) ($cards['completed_session_count'] ?? 0);
        $available = (int) ($cards['available_equipment_count'] ?? 0);

        $headline = $checkedIn > 0
            ? 'Bạn đang có phiên sử dụng cần theo dõi đến khi hoàn tất.'
            : ($upcoming > 0 ? 'Bạn đã có lịch sắp tới, hãy kiểm tra thời gian và thiết bị.' : 'Bạn chưa có lịch sắp tới, có thể bắt đầu bằng việc tạo booking.');

        $narrative = "{$user->name} hiện có {$upcoming} lịch sắp sử dụng, {$checkedIn} phiên đang check-in và {$completed} phiên đã hoàn tất. Dashboard này chỉ tập trung vào dữ liệu của bạn để tránh lẫn với lịch của người dùng khác.";

        return [
            'headline' => $headline,
            'narrative' => $narrative,
            'context' => [
                ['label' => 'Lịch sắp tới', 'value' => $upcoming],
                ['label' => 'Đang check-in', 'value' => $checkedIn],
                ['label' => 'Đã hoàn tất', 'value' => $completed],
                ['label' => 'Thiết bị có thể đặt', 'value' => $available],
            ],
            'next_actions' => [
                [
                    'label' => $upcoming > 0 ? 'Xem lịch của tôi' : 'Tạo lịch đặt',
                    'description' => $upcoming > 0
                        ? 'Kiểm tra thời gian, thiết bị và mục đích sử dụng đã đăng ký.'
                        : 'Chọn thiết bị phù hợp và tạo một lượt sử dụng mới.',
                    'route' => $upcoming > 0 ? 'bookings.index' : 'bookings.create',
                    'variant' => 'primary',
                ],
                [
                    'label' => 'Tìm thiết bị phù hợp',
                    'description' => 'Xem lab, loại thiết bị và thời lượng cho phép trước khi đặt.',
                    'route' => 'equipments.index',
                ],
            ],
        ];
    }
}
