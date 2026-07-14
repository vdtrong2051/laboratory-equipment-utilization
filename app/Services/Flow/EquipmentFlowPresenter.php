<?php

namespace App\Services\Flow;

use App\Enums\AnalysisStatus;
use App\Models\Equipment;

class EquipmentFlowPresenter
{
    public function present(Equipment $equipment): array
    {
        $hasMetrics = $equipment->usageMetrics()->exists();
        $hasPatterns = $equipment->abnormalPatterns()->where('status', 'open')->exists();

        return [
            'current_step' => $this->currentStep($hasMetrics, $hasPatterns, $equipment->current_analysis_status !== AnalysisStatus::Normal),
            'story' => $this->story($equipment, $hasMetrics, $hasPatterns),
            'steps' => [
                [
                    'key' => 'usage_data',
                    'label' => 'Usage Data',
                    'description' => $equipment->usage_sessions_count.' usage sessions, '.$equipment->activity_signals_count.' activity signals.',
                    'state' => $equipment->usage_sessions_count > 0 ? 'completed' : 'current',
                ],
                [
                    'key' => 'metrics_calculated',
                    'label' => 'Metrics Calculated',
                    'description' => $hasMetrics ? 'Đã có usage metrics gần nhất.' : 'Chưa có usage metrics cho thiết bị này.',
                    'state' => $hasMetrics ? 'completed' : 'pending',
                ],
                [
                    'key' => 'rules_evaluated',
                    'label' => 'Rules Evaluated',
                    'description' => 'Trạng thái phân tích hiện tại: '.$equipment->current_analysis_status->value.'.',
                    'state' => $equipment->current_analysis_status !== AnalysisStatus::Normal ? 'completed' : ($hasMetrics ? 'current' : 'pending'),
                ],
                [
                    'key' => 'pattern_detected',
                    'label' => 'Pattern Detected',
                    'description' => $equipment->abnormal_patterns_count.' abnormal patterns liên quan.',
                    'state' => $hasPatterns ? 'completed' : 'pending',
                ],
                [
                    'key' => 'management_insight',
                    'label' => 'Management Insight',
                    'description' => $hasPatterns ? 'Cần manager xem bất thường đang mở.' : 'Chưa cần can thiệp quản lý đặc biệt.',
                    'state' => $hasPatterns ? 'current' : 'pending',
                ],
            ],
        ];
    }

    private function currentStep(bool $hasMetrics, bool $hasPatterns, bool $hasAnalysisSignal): string
    {
        if ($hasPatterns) {
            return 'management_insight';
        }

        if ($hasAnalysisSignal) {
            return 'pattern_detected';
        }

        if ($hasMetrics) {
            return 'rules_evaluated';
        }

        return 'usage_data';
    }

    private function story(Equipment $equipment, bool $hasMetrics, bool $hasPatterns): string
    {
        if ($hasPatterns) {
            return $equipment->equipment_code.' đã đi đủ chuỗi từ usage data đến abnormal pattern. Nên đọc bất thường đang mở để hiểu vì sao thiết bị cần chú ý.';
        }

        if ($hasMetrics) {
            return $equipment->equipment_code.' đã có metrics, nhưng chưa có bất thường mở. Đây là thiết bị có dữ liệu đủ để tiếp tục đánh giá rule.';
        }

        return $equipment->equipment_code.' mới có dữ liệu nền hoặc phiên sử dụng chưa đủ. Cần thêm usage session/telemetry trước khi phân tích sâu.';
    }
}
