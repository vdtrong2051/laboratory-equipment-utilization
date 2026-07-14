@php
    $isPersonalBooking = $currentUser->isResearcher();
@endphp

<div class="form-grid two">
    <div class="field">
        <label for="{{ $idPrefix }}_equipment_id">Thiết bị</label>
        <select id="{{ $idPrefix }}_equipment_id" name="equipment_id" required>
            <option value="">Chọn thiết bị</option>
            @foreach ($equipments as $equipment)
                <option value="{{ $equipment->id }}" @selected((string) old('equipment_id', $booking->equipment_id) === (string) $equipment->id)>
                    {{ $equipment->equipment_code }} · {{ $equipment->name }} · {{ $equipment->laboratory }}
                </option>
            @endforeach
        </select>
        @error('equipment_id') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="equipment_id"></span>
    </div>
    <div class="field">
        <label for="{{ $idPrefix }}_user_id">Người sử dụng</label>
        @if ($isPersonalBooking)
            <input value="{{ $currentUser->name }} · {{ $currentUser->role->value }}" disabled>
            <input name="user_id" type="hidden" value="{{ $currentUser->id }}">
        @else
            <select id="{{ $idPrefix }}_user_id" name="user_id" required>
                <option value="">Chọn người dùng</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('user_id') === (string) $user->id)>
                        {{ $user->name }} · {{ $user->role->value }}
                    </option>
                @endforeach
            </select>
        @endif
        @error('user_id') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="user_id"></span>
    </div>
    <div class="field">
        <label for="{{ $idPrefix }}_start_time">Bắt đầu</label>
        <input id="{{ $idPrefix }}_start_time" name="start_time" type="datetime-local" value="{{ old('start_time', $booking->start_time?->format('Y-m-d\TH:i')) }}" required>
        @error('start_time') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="start_time"></span>
    </div>
    <div class="field">
        <label for="{{ $idPrefix }}_end_time">Kết thúc</label>
        <input id="{{ $idPrefix }}_end_time" name="end_time" type="datetime-local" value="{{ old('end_time', $booking->end_time?->format('Y-m-d\TH:i')) }}" required>
        @error('end_time') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="end_time"></span>
    </div>
    <div class="field" style="grid-column: 1 / -1;">
        <label for="{{ $idPrefix }}_purpose">Mục đích sử dụng</label>
        <input id="{{ $idPrefix }}_purpose" name="purpose" value="{{ old('purpose') }}" placeholder="Ví dụ: Quan sát mẫu, chuẩn bị PCR...">
        @error('purpose') <span class="error">{{ $message }}</span> @enderror
        <span class="error" data-error-for="purpose"></span>
    </div>
</div>

<p class="muted">
    @if ($isPersonalBooking)
        Lịch đặt này sẽ được tạo cho chính bạn: {{ $currentUser->name }} ({{ $currentUser->email }}).
    @else
        Người tạo lịch hiện tại: {{ $currentUser->name }} ({{ $currentUser->email }}).
    @endif
</p>
