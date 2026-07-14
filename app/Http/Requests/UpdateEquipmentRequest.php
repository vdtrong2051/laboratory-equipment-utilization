<?php

namespace App\Http\Requests;

use App\Enums\UsageMode;
use App\Models\Equipment;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Equipment $equipment */
        $equipment = $this->route('equipment');

        return [
            'equipment_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('equipments', 'equipment_code')->ignore($equipment),
            ],
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
