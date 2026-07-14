<?php

namespace App\Services\Insights;

use App\DTOs\DashboardSummaryData;
use App\Models\User;

class LabStaffInsightService implements RoleInsightService
{
    public function insightFor(User $user, DashboardSummaryData $summary): array
    {
        $cards = $summary->cards;
        $waiting = (int) ($cards['waiting_check_in_count'] ?? 0);
        $active = (int) ($cards['active_session_count'] ?? 0);
        $overdue = (int) ($cards['overdue_session_count'] ?? 0);
        $idle = (int) ($cards['powered_idle_equipment_count'] ?? 0);

        $headline = $overdue > 0
            ? 'Có phiên quá hạn cần xử lý ngay trong ca trực.'
            : 'Ca trực có thể bắt đầu từ lịch check-in và thiết bị đang bật.';

        $narrative = "Hôm nay có {$waiting} lượt cần chuẩn bị check-in, {$active} phiên đang chạy và {$idle} thiết bị đang bật nhưng chưa hoạt động. Kỹ thuật viên nên xử lý các phiên quá hạn trước, sau đó kiểm tra nhóm POWERED_IDLE để tránh lãng phí thời gian bật máy.";

        return [
            'headline' => $headline,
            'narrative' => $narrative,
            'context' => [
                ['label' => 'Cần check-in', 'value' => $waiting],
                ['label' => 'Phiên đang chạy', 'value' => $active],
                ['label' => 'Quá hạn', 'value' => $overdue],
                ['label' => 'POWERED_IDLE', 'value' => $idle],
            ],
            'next_actions' => [
                [
                    'label' => $overdue > 0 ? 'Xử lý phiên quá hạn' : 'Mở lịch đặt hôm nay',
                    'description' => $overdue > 0
                        ? 'Ưu tiên liên hệ người dùng hoặc hoàn tất phiên đang giữ thiết bị quá lâu.'
                        : 'Chuẩn bị check-in cho các lượt sắp đến trong 30 phút tới.',
                    'route' => $overdue > 0 ? 'usage-sessions.index' : 'bookings.index',
                    'variant' => 'primary',
                ],
                [
                    'label' => 'Kiểm tra phiên đang chạy',
                    'description' => 'Xem trạng thái từng phiên để hoàn tất hoặc mô phỏng telemetry khi cần.',
                    'route' => 'usage-sessions.index',
                ],
                [
                    'label' => 'Rà soát thiết bị idle',
                    'description' => 'Tắt hoặc nhắc người dùng thao tác với thiết bị đang bật nhưng không hoạt động.',
                    'route' => 'equipments.index',
                ],
            ],
        ];
    }
}
