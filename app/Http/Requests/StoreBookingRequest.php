<?php

namespace App\Http\Requests;

use App\Services\BookingService;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'integer', 'exists:equipments,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'purpose' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $hasConflict = app(BookingService::class)->hasScheduleConflict(
                    equipmentId: (int) $this->input('equipment_id'),
                    startTime: Carbon::parse($this->input('start_time')),
                    endTime: Carbon::parse($this->input('end_time')),
                );

                if ($hasConflict) {
                    $validator->errors()->add('start_time', 'Thiết bị đã có lịch đặt giao với khoảng thời gian này.');
                }
            },
        ];
    }

    protected function failedValidation(ValidationValidator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
