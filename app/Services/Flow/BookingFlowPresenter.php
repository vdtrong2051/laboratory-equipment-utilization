<?php

namespace App\Services\Flow;

use App\Enums\BookingStatus;
use App\Models\Booking;

class BookingFlowPresenter
{
    public function present(Booking $booking): array
    {
        $currentStep = match ($booking->status) {
            BookingStatus::Booked => 'confirm_booking',
            BookingStatus::CheckedIn, BookingStatus::Overdue => 'checked_in',
            BookingStatus::Completed => 'completed',
            BookingStatus::Cancelled => 'cancelled',
            BookingStatus::NoShow => 'no_show',
        };

        return [
            'current_step' => $currentStep,
            'story' => $this->story($booking),
            'steps' => [
                [
                    'key' => 'select_equipment',
                    'label' => 'Select Equipment',
                    'description' => $booking->equipment?->equipment_code
                        ? 'Đã chọn '.$booking->equipment->equipment_code.' trong '.$booking->equipment->laboratory.'.'
                        : 'Chọn thiết bị cần dùng.',
                    'state' => 'completed',
                ],
                [
                    'key' => 'select_time',
                    'label' => 'Select Time',
                    'description' => $booking->start_time->format('d/m H:i').' - '.$booking->end_time->format('H:i'),
                    'state' => 'completed',
                ],
                [
                    'key' => 'add_purpose',
                    'label' => 'Add Purpose',
                    'description' => $booking->purpose ?: 'Chưa ghi mục đích, vẫn có thể theo dõi booking.',
                    'state' => $booking->purpose ? 'completed' : 'current',
                ],
                [
                    'key' => 'confirm_booking',
                    'label' => 'Confirm Booking',
                    'description' => match ($booking->status) {
                        BookingStatus::Booked => 'Booking đang chờ lab staff check-in.',
                        BookingStatus::CheckedIn, BookingStatus::Overdue => 'Booking đã chuyển thành usage session.',
                        BookingStatus::Completed => 'Booking đã hoàn tất.',
                        BookingStatus::Cancelled => 'Booking đã bị huỷ.',
                        BookingStatus::NoShow => 'Người dùng không đến sử dụng.',
                    },
                    'state' => $this->confirmationState($booking),
                ],
            ],
        ];
    }

    private function confirmationState(Booking $booking): string
    {
        return match ($booking->status) {
            BookingStatus::Booked => 'current',
            BookingStatus::Cancelled, BookingStatus::NoShow => 'blocked',
            default => 'completed',
        };
    }

    private function story(Booking $booking): string
    {
        return match ($booking->status) {
            BookingStatus::Booked => 'Booking đã có thiết bị, thời gian và người sử dụng. Bước tiếp theo là lab staff check-in khi người dùng đến nhận thiết bị.',
            BookingStatus::CheckedIn, BookingStatus::Overdue => 'Booking này đã đi tiếp sang usage session. Từ đây dữ liệu vận hành và telemetry sẽ quyết định trạng thái sử dụng thực tế.',
            BookingStatus::Completed => 'Booking đã khép lại thành một phiên sử dụng hoàn tất, có thể đưa vào tính metrics.',
            BookingStatus::Cancelled => 'Booking đã bị huỷ nên không tạo usage session và không đóng góp dữ liệu sử dụng.',
            BookingStatus::NoShow => 'Booking bị đánh dấu no-show, đây là tín hiệu quan trọng khi đánh giá lãng phí lịch đặt.',
        };
    }
}
