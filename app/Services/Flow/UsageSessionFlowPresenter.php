<?php

namespace App\Services\Flow;

use App\Enums\BookingStatus;
use App\Models\UsageSession;

class UsageSessionFlowPresenter
{
    public function present(UsageSession $session): array
    {
        $hasTelemetry = $session->activitySignals()->exists();
        $isCompleted = $session->status === BookingStatus::Completed;

        return [
            'current_step' => $this->currentStep($session, $hasTelemetry),
            'story' => $this->story($session, $hasTelemetry),
            'steps' => [
                [
                    'key' => 'booked',
                    'label' => 'Booked',
                    'description' => $session->booking ? 'Có booking #'.$session->booking->id.' làm nguồn.' : 'Phiên không có booking nguồn.',
                    'state' => 'completed',
                ],
                [
                    'key' => 'checked_in',
                    'label' => 'Checked In',
                    'description' => $session->checkedInBy?->name ? 'Check-in bởi '.$session->checkedInBy->name.'.' : 'Đã tạo usage session.',
                    'state' => 'completed',
                ],
                [
                    'key' => 'session_running',
                    'label' => 'Session Running',
                    'description' => $isCompleted ? 'Phiên đã kết thúc.' : 'Thiết bị đang trong quá trình sử dụng hoặc chờ hoàn tất.',
                    'state' => $isCompleted ? 'completed' : 'current',
                ],
                [
                    'key' => 'telemetry_received',
                    'label' => 'Telemetry Received',
                    'description' => $hasTelemetry ? 'Đã nhận activity signals mô phỏng.' : 'Chưa có activity signal cho phiên này.',
                    'state' => $hasTelemetry ? 'completed' : 'pending',
                ],
                [
                    'key' => 'completed',
                    'label' => 'Completed',
                    'description' => $isCompleted ? 'Phiên đã hoàn tất và sẵn sàng đưa vào metrics.' : 'Chờ lab staff hoàn tất phiên.',
                    'state' => $isCompleted ? 'completed' : 'pending',
                ],
            ],
        ];
    }

    private function currentStep(UsageSession $session, bool $hasTelemetry): string
    {
        if ($session->status === BookingStatus::Completed) {
            return 'completed';
        }

        return $hasTelemetry ? 'telemetry_received' : 'session_running';
    }

    private function story(UsageSession $session, bool $hasTelemetry): string
    {
        if ($session->status === BookingStatus::Completed) {
            return 'Phiên đã hoàn tất. Dữ liệu này có thể được gom vào usage metrics và rule evaluation.';
        }

        if ($hasTelemetry) {
            return 'Phiên đang có telemetry, nghĩa là backend đã có tín hiệu để phân biệt máy đang hoạt động thật hay chỉ bật nguồn.';
        }

        return 'Phiên đã check-in nhưng chưa có activity signal. Bước tiếp theo là nhận telemetry mô phỏng hoặc hoàn tất phiên nếu ca sử dụng đã xong.';
    }
}
