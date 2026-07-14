@extends('layouts.app')

@section('title', 'Chi tiết lịch đặt')

@section('content')
    @php
        $currentUser = app(\App\Services\UserContextService::class)->currentOrNull();
        $canOperateLab = $currentUser?->canOperateLab() ?? false;
    @endphp

    <div class="page-head">
        <div>
            <h1>Chi tiết lịch đặt</h1>
            <p class="muted">{{ $booking->equipment->equipment_code }} · {{ $booking->equipment->name }}</p>
        </div>
        <div class="actions">
            <a class="button" href="{{ route('bookings.index') }}">Danh sách lịch</a>
            @if ($booking->usageSession && $canOperateLab)
                <a class="button secondary" href="{{ route('usage-sessions.show', $booking->usageSession) }}">Xem phiên sử dụng</a>
            @endif
            @if ($booking->status === \App\Enums\BookingStatus::Booked && $canOperateLab)
                <button class="primary" type="button" data-modal-open="check-in-booking-modal">Check-in</button>
            @endif
            @if ($booking->status === \App\Enums\BookingStatus::Booked)
                <button class="danger" type="button" data-modal-open="cancel-booking-modal">Huỷ lịch</button>
            @endif
        </div>
    </div>

    <x-flow-stepper :steps="$flow['steps']" :current-step="$flow['current_step']" :story="$flow['story']" />

    <section class="panel">
        <h2>Thông tin booking</h2>
        <div class="detail-grid">
            <div class="metric"><span class="muted">Trạng thái</span><strong>{{ $booking->status->value }}</strong></div>
            <div class="metric"><span class="muted">Bắt đầu</span><strong>{{ $booking->start_time->format('d/m/Y H:i') }}</strong></div>
            <div class="metric"><span class="muted">Kết thúc</span><strong>{{ $booking->end_time->format('d/m/Y H:i') }}</strong></div>
            <div class="metric"><span class="muted">Người dùng</span><strong>{{ $booking->user->name }}</strong></div>
            <div class="metric"><span class="muted">Người tạo</span><strong>{{ $booking->creator?->name ?? '-' }}</strong></div>
            <div class="metric"><span class="muted">Thiết bị</span><strong>{{ $booking->equipment->equipment_code }}</strong></div>
        </div>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Mục đích sử dụng</h2>
        <p>{{ $booking->purpose ?? 'Chưa ghi mục đích.' }}</p>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Usage session</h2>
        @if ($booking->usageSession)
            <p>Đã tạo usage session #{{ $booking->usageSession->id }} cho booking này.</p>
            @if ($canOperateLab)
                <a class="button secondary" href="{{ route('usage-sessions.show', $booking->usageSession) }}">Mở phiên sử dụng</a>
            @endif
        @else
            <p class="muted">Chưa check-in. Khi check-in, hệ thống sẽ tạo usage session và chuyển booking sang CHECKED_IN.</p>
        @endif
    </section>

    @if ($booking->status === \App\Enums\BookingStatus::Booked && $canOperateLab)
        <x-modal id="check-in-booking-modal" title="Check-in booking" description="Xác nhận người dùng đã nhận thiết bị.">
            <form method="POST" action="{{ route('bookings.check-in', $booking) }}" data-ajax-form>
                @csrf
                @include('bookings.partials.check-in-form', ['booking' => $booking])
                <div class="actions" style="margin-top: 16px;">
                    <button class="primary" type="submit">Check-in</button>
                    <button type="button" data-modal-close>Huỷ</button>
                </div>
            </form>
        </x-modal>
    @endif

    @if ($booking->status === \App\Enums\BookingStatus::Booked)
        <x-modal id="cancel-booking-modal" title="Huỷ lịch đặt" description="Xác nhận trước khi huỷ booking.">
            <form method="POST" action="{{ route('bookings.destroy', $booking) }}" data-ajax-form>
                @csrf
                @method('DELETE')
                @include('bookings.partials.cancel-form', ['booking' => $booking])
                <div class="actions" style="margin-top: 16px;">
                    <button class="danger" type="submit">Huỷ lịch</button>
                    <button type="button" data-modal-close>Đóng</button>
                </div>
            </form>
        </x-modal>
    @endif
@endsection
