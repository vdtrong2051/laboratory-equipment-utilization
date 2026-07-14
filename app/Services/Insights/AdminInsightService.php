<?php

namespace App\Services\Insights;

use App\DTOs\DashboardSummaryData;
use App\Models\User;

class AdminInsightService implements RoleInsightService
{
    public function insightFor(User $user, DashboardSummaryData $summary): array
    {
        $cards = $summary->cards;
        $activeUsers = (int) ($cards['active_user_count'] ?? 0);
        $users = (int) ($cards['user_count'] ?? 0);
        $equipment = (int) ($cards['equipment_count'] ?? 0);
        $openAbnormal = (int) ($cards['open_abnormal_count'] ?? 0);
        $latestRun = $cards['latest_analysis_run_status'] ?? 'Chưa có';

        return [
            'headline' => 'Tình trạng hệ thống cần được theo dõi ở mức dữ liệu nền và cấu hình.',
            'narrative' => "Hệ thống hiện có {$equipment} thiết bị, {$activeUsers}/{$users} người dùng hoạt động và {$openAbnormal} bất thường chưa xử lý. Lần phân tích gần nhất: {$latestRun}.",
            'context' => [
                ['label' => 'Người dùng hoạt động', 'value' => "{$activeUsers}/{$users}"],
                ['label' => 'Thiết bị', 'value' => $equipment],
                ['label' => 'Bất thường mở', 'value' => $openAbnormal],
                ['label' => 'Phân tích gần nhất', 'value' => $latestRun],
            ],
            'next_actions' => [
                [
                    'label' => 'Cập nhật danh mục thiết bị',
                    'description' => 'Kiểm tra thiết bị thiếu thông tin hoặc cần bổ sung trước khi vận hành.',
                    'route' => 'equipments.index',
                    'variant' => 'primary',
                ],
                [
                    'label' => 'Xem lịch sử phân tích',
                    'description' => 'Theo dõi các lần phân tích dữ liệu và số rule được phát hiện.',
                    'route' => 'analysis-runs.index',
                ],
            ],
        ];
    }
}
