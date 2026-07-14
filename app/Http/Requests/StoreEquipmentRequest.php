<?php

namespace App\Http\Requests;

use App\Enums\UsageMode;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_code' => ['required', 'string', 'max:50', 'unique:equipments,equipment_code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'laboratory' => ['required', 'string', 'max:255'],
            'usage_mode' => ['required', Rule::enum(UsageMode::class)],
            'allowed_usage_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ];
    }

    protected function failedValidation(Validator $validator): void
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
