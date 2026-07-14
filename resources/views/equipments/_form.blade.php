@php
    $selectedUsageMode = old('usage_mode', $equipment->usage_mode?->value ?? 'on_site');
@endphp

<div class="form-grid two">
    <div class="field">
        <label for="equipment_code">Mã thiết bị</label>
        <input id="equipment_code" name="equipment_code" value="{{ old('equipment_code', $equipment->equipment_code) }}" required>
        @error('equipment_code') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="equipment_code"></span>
    </div>
    <div class="field">
        <label for="name">Tên thiết bị</label>
        <input id="name" name="name" value="{{ old('name', $equipment->name) }}" required>
        @error('name') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="name"></span>
    </div>
    <div class="field">
        <label for="type">Loại thiết bị</label>
        <input id="type" name="type" value="{{ old('type', $equipment->type) }}" required>
        @error('type') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="type"></span>
    </div>
    <div class="field">
        <label for="laboratory">Phòng lab</label>
        <input id="laboratory" name="laboratory" value="{{ old('laboratory', $equipment->laboratory) }}" required>
        @error('laboratory') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="laboratory"></span>
    </div>
    <div class="field">
        <label for="usage_mode">Chế độ sử dụng</label>
        <select id="usage_mode" name="usage_mode" required>
            @foreach ($usageModes as $usageMode)
                <option value="{{ $usageMode->value }}" @selected($selectedUsageMode === $usageMode->value)>
                    {{ $usageMode->value === 'on_site' ? 'Tại chỗ' : 'Di động' }}
                </option>
            @endforeach
        </select>
        @error('usage_mode') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="usage_mode"></span>
    </div>
    <div class="field">
        <label for="allowed_usage_duration_minutes">Thời lượng cho phép</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <input id="allowed_usage_duration_minutes" name="allowed_usage_duration_minutes" type="number" min="1" max="1440" value="{{ old('allowed_usage_duration_minutes', $equipment->allowed_usage_duration_minutes) }}">
            <span class="muted">phút</span>
        </div>
        @error('allowed_usage_duration_minutes') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="allowed_usage_duration_minutes"></span>
    </div>
</div>

<div class="actions" style="margin-top: 16px;">
    <button class="primary" type="submit">{{ $submitLabel }}</button>
    <a class="button" href="{{ route('equipments.index') }}">Huỷ</a>
</div>
