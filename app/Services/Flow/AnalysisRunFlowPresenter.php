<?php

namespace App\Services\Flow;

use App\Models\AnalysisRun;

class AnalysisRunFlowPresenter
{
    public function present(AnalysisRun $run): array
    {
        return [
            'current_step' => $this->currentStep($run),
            'story' => $this->story($run),
            'steps' => [
                [
                    'key' => 'usage_data',
                    'label' => 'Usage Data',
                    'description' => $run->processed_equipment.' thiết bị được đưa vào kỳ phân tích.',
                    'state' => $run->processed_equipment > 0 ? 'completed' : 'pending',
                ],
                [
                    'key' => 'metrics_calculated',
                    'label' => 'Metrics Calculated',
                    'description' => $run->usage_metrics_calculated.' usage metrics được tính.',
                    'state' => $run->usage_metrics_calculated > 0 ? 'completed' : 'pending',
                ],
                [
                    'key' => 'rules_evaluated',
                    'label' => 'Rules Evaluated',
                    'description' => $run->rule_sets_evaluated.' rule sets được đánh giá.',
                    'state' => $run->rule_sets_evaluated > 0 ? 'completed' : 'pending',
                ],
                [
                    'key' => 'pattern_detected',
                    'label' => 'Pattern Detected',
                    'description' => $run->matched_rules.' rule match được ghi nhận.',
                    'state' => $run->matched_rules > 0 ? 'completed' : ($run->status === 'completed' ? 'pending' : 'current'),
                ],
                [
                    'key' => 'management_insight',
                    'label' => 'Management Insight',
                    'description' => $run->status === 'completed' ? 'Manager có thể dùng kết quả này để ra quyết định.' : 'Chờ analysis run hoàn tất.',
                    'state' => $run->status === 'completed' ? 'completed' : ($run->status === 'failed' ? 'blocked' : 'pending'),
                ],
            ],
        ];
    }

    private function currentStep(AnalysisRun $run): string
    {
        if ($run->status === 'failed') {
            return 'rules_evaluated';
        }

        if ($run->status === 'completed') {
            return 'management_insight';
        }

        if ($run->rule_sets_evaluated > 0) {
            return 'pattern_detected';
        }

        if ($run->usage_metrics_calculated > 0) {
            return 'rules_evaluated';
        }

        return 'metrics_calculated';
    }

    private function story(AnalysisRun $run): string
    {
        if ($run->status === 'failed') {
            return 'Analysis run bị lỗi trước khi tạo insight quản lý. Cần đọc error message và chạy lại sau khi dữ liệu ổn định.';
        }

        if ($run->matched_rules > 0) {
            return 'Run này đã chuyển dữ liệu sử dụng thành bất thường có thể hành động. Manager nên đọc rule match trước khi xem bảng thô.';
        }

        return 'Run này đã gom dữ liệu sử dụng, tính metrics và đánh giá rule. Không có rule match nghĩa là chưa phát hiện bất thường nổi bật trong kỳ.';
    }
}
