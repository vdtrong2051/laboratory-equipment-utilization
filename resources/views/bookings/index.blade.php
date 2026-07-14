@extends('layouts.app')

@section('title', 'Lịch đặt thiết bị')

@section('content')
    @php
        $currentUser = app(\App\Services\UserContextService::class)->currentOrNull();
        $canOperateLab = $currentUser?->canOperateLab() ?? false;
    @endphp

    <div class="page-head">
        <div>
            <h1>Lịch đặt thiết bị</h1>
            <p class="muted">Theo dõi booking trước khi chuyển sang check-in và usage session.</p>
        </div>
        <button class="primary" type="button" data-modal-open="create-booking-modal">Tạo lịch đặt</button>
    </div>

    <form class="panel filter-toolbar" method="GET" action="{{ route('bookings.index') }}">
        <div class="field">
            <label for="date">Ngày</label>
            <input id="date" name="date" type="date" value="{{ request('date') }}">
        </div>
        <div class="field">
            <label for="equipment_id">Thiết bị</label>
            <select id="equipment_id" name="equipment_id">
                <option value="">Tất cả</option>
                @foreach ($equipments as $equipment)
                    <option value="{{ $equipment->id }}" @selected((string) request('equipment_id') === (string) $equipment->id)>{{ $equipment->equipment_code }} · {{ $equipment->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->value }}</option>
                @endforeach
            </select>
        </div>
        <button class="primary" type="submit">Lọc</button>
    </form>

    <div class="panel table-card" id="bookings-table">
        <div class="table-toolbar">
            <div>
                <h2>Lịch đặt</h2>
                <div class="record-count">{{ $bookings->total() }} bản ghi</div>
            </div>
            <button class="primary" type="button" data-modal-open="create-booking-modal">Tạo lịch đặt</button>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Thiết bị</th>
                        <th>Người dùng</th>
                        <th>Trạng thái</th>
                        <th>Mục đích</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr data-href="{{ route('bookings.show', $booking) }}">
                            <td>
                                <strong>{{ $booking->start_time->format('d/m/Y H:i') }}</strong><br>
                                <span class="muted">đến {{ $booking->end_time->format('H:i') }}</span>
                            </td>
                            <td>{{ $booking->equipment->equipment_code }} · {{ $booking->equipment->name }}</td>
                            <td>{{ $booking->user->name }}</td>
                            <td><span class="badge">{{ $booking->status->value }}</span></td>
                            <td>{{ $booking->purpose ?? '-' }}</td>
                            <td>
                                <div class="row-actions">
                                    <a class="button primary" href="{{ route('bookings.show', $booking) }}">Chi tiết</a>
                                    @if ($booking->status === \App\Enums\BookingStatus::Booked)
                                        <details class="action-menu">
                                            <summary class="button">...</summary>
                                            <div class="action-menu-panel">
                                                @if ($canOperateLab)
                                                    <button class="secondary" type="button" data-modal-open="check-in-booking-{{ $booking->id }}">Check-in</button>
                                                @endif
                                                <button class="danger" type="button" data-modal-open="cancel-booking-{{ $booking->id }}">Huỷ lịch</button>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @if ($booking->status === \App\Enums\BookingStatus::Booked && $canOperateLab)
                            <x-modal id="check-in-booking-{{ $booking->id }}" title="Check-in booking" description="Xác nhận người dùng đã nhận thiết bị.">
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
                            <x-modal id="cancel-booking-{{ $booking->id }}" title="Huỷ lịch đặt" description="Xác nhận trước khi huỷ booking.">
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
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">Chưa có lịch đặt phù hợp với bộ lọc hiện tại.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $bookings->links() }}
        </div>
    </div>

    <x-modal id="create-booking-modal" title="Tạo lịch đặt" description="Tạo booking mới mà không rời khỏi danh sách hiện tại.">
        <form method="POST" action="{{ route('bookings.store') }}" data-ajax-form>
            @csrf
            @include('bookings.partials.create-form', [
                'booking' => new \App\Models\Booking([
                    'start_time' => now()->addHour()->startOfHour(),
                    'end_time' => now()->addHours(3)->startOfHour(),
                ]),
                'idPrefix' => 'booking_create_modal',
            ])
            <div class="actions" style="margin-top: 16px;">
                <button class="primary" type="submit">Tạo lịch đặt</button>
                <button type="button" data-modal-close>Đóng</button>
            </div>
        </form>
    </x-modal>
@endsection
