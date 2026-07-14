<?php

namespace App\Services\Insights;

use App\DTOs\DashboardSummaryData;
use App\Models\User;

class ManagerInsightService implements RoleInsightService
{
    public function insightFor(User $user, DashboardSummaryData $summary): array
    {
        $cards = $summary->cards;
        $openAbnormal = (int) ($cards['open_abnormal_count'] ?? 0);
        $capacityPressure = (int) ($cards['capacity_pressure_count'] ?? 0);
        $underutilized = (int) ($cards['underutilized_count'] ?? 0);
        $idle = (int) ($cards['idle_while_powered_count'] ?? 0);
        $actualRate = (float) ($cards['average_actual_utilization_rate'] ?? 0);

        $headline = $capacityPressure > 0
            ? 'Có thiết bị đang chịu áp lực công suất, cần kiểm tra trước khi quyết định mua thêm.'
            : 'Dữ liệu hiện tại chưa đủ để kết luận phòng lab thiếu công suất.';

        $narrative = "Mức khai thác thực tế trung bình đang ở mức {$actualRate}%. Hệ thống ghi nhận {$openAbnormal} bất thường đang mở, {$capacityPressure} thiết bị quá tải và {$underutilized} thiết bị ít được sử dụng. Nên xử lý bất thường và hoàn thiện dữ liệu phiên sử dụng trước khi đánh giá nhu cầu mua thêm thiết bị.";

        return [
            'headline' => $headline,
            'narrative' => $narrative,
            'context' => [
                ['label' => 'Bất thường mở', 'value' => $openAbnormal],
                ['label' => 'Áp lực công suất', 'value' => $capacityPressure],
                ['label' => 'Bỏ quên', 'value' => $underutilized],
                ['label' => 'Bật nhưng idle', 'value' => $idle],
            ],
            'next_actions' => [
                [
                    'label' => $openAbnormal > 0 ? 'Xem bất thường đang mở' : 'Chạy phân tích định kỳ',
                    'description' => $openAbnormal > 0
                        ? 'Kiểm tra mức độ, thiết bị liên quan và tiếp nhận xử lý nếu cần.'
                        : 'Tạo snapshot mới để xác nhận dữ liệu phân tích trước khi ra quyết định.',
                    'route' => $openAbnormal > 0 ? 'abnormal-patterns.index' : 'dashboard.manager.run-analysis',
                    'method' => $openAbnormal > 0 ? 'GET' : 'POST',
                    'variant' => 'primary',
                ],
                [
                    'label' => 'Mở danh sách thiết bị',
                    'description' => 'Lọc thiết bị quá tải, ít được sử dụng hoặc có bất thường mở.',
                    'route' => 'equipments.index',
                ],
                [
                    'label' => 'Đọc lịch sử phân tích',
                    'description' => 'Xem lần phân tích gần nhất và số quy tắc phát hiện theo từng kỳ.',
                    'route' => 'analysis-runs.index',
                ],
            ],
        ];
    }
}
