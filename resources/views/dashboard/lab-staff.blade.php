@extends('layouts.app')

@section('title', $workspace['title'] ?? 'Lab Staff Dashboard')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $workspace['title'] ?? 'Lab Staff Dashboard' }}</h1>
            <p class="muted">{{ $workspace['subtitle'] ?? 'Không gian thao tác hằng ngày cho nhận thiết bị, theo dõi phiên sử dụng và kiểm tra trạng thái vận hành.' }}</p>
        </div>
        <div class="actions">
            @include('dashboard.partials.quick-actions', ['actions' => $workspace['quick_actions'] ?? []])
        </div>
    </div>

    @include('dashboard.partials.workspace-intro', ['workspace' => $workspace, 'insight' => $insight])

    <section class="detail-grid">
        <div class="metric"><span class="muted">Booking hôm nay</span><strong>{{ $summary->cards['today_booking_count'] }}</strong></div>
        <div class="metric"><span class="muted">Cần check-in</span><strong>{{ $summary->cards['waiting_check_in_count'] }}</strong></div>
        <div class="metric"><span class="muted">Phiên đang chạy</span><strong>{{ $summary->cards['active_session_count'] }}</strong></div>
        <div class="metric"><span class="muted">Quá hạn</span><strong>{{ $summary->cards['overdue_session_count'] }}</strong></div>
        <div class="metric"><span class="muted">POWERED_IDLE</span><strong>{{ $summary->cards['powered_idle_equipment_count'] }}</strong></div>
        <div class="metric"><span class="muted">ACTIVE</span><strong>{{ $summary->cards['active_equipment_count'] }}</strong></div>
    </section>

    <section class="panel" id="today-bookings" style="margin-top: 16px;">
        <h2>Booking cần theo dõi</h2>
        <table>
            <thead>
                <tr>
                    <th>Thiết bị</th>
                    <th>Người dùng</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary->tables['upcoming_bookings'] as $booking)
                    <tr>
                        <td>{{ $booking->equipment?->equipment_code }}</td>
                        <td>{{ $booking->user?->name }}</td>
                        <td>{{ $booking->start_time?->format('Y-m-d H:i') }}</td>
                        <td>{{ $booking->end_time?->format('Y-m-d H:i') }}</td>
                        <td><span class="badge">{{ $booking->status->value }}</span></td>
                        <td>
                            <div class="actions">
                                <a class="button" href="{{ route('bookings.show', $booking) }}">Chi tiết</a>
                                @if ($booking->status === \App\Enums\BookingStatus::Booked)
                                    <button class="secondary" type="button" data-modal-open="dashboard-check-in-{{ $booking->id }}">Check-in</button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    @if ($booking->status === \App\Enums\BookingStatus::Booked)
                        <x-modal id="dashboard-check-in-{{ $booking->id }}" title="Check-in booking" description="Xác nhận người dùng đã nhận thiết bị.">
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
                @empty
                    <tr><td colspan="6" class="muted">Không có booking cần theo dõi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel" id="active-sessions" style="margin-top: 16px;">
        <h2>Phiên đang chạy</h2>
        <table>
            <thead>
                <tr>
                    <th>Thiết bị</th>
                    <th>Người dùng</th>
                    <th>Bắt đầu</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary->tables['active_sessions'] as $usageSession)
                    <tr>
                        <td>{{ $usageSession->equipment?->equipment_code }}</td>
                        <td>{{ $usageSession->user?->name }}</td>
                        <td>{{ $usageSession->started_at?->format('Y-m-d H:i') }}</td>
                        <td><span class="badge">{{ $usageSession->status->value }}</span></td>
                        <td>
                            <div class="actions">
                                <a class="button" href="{{ route('usage-sessions.show', $usageSession) }}">Mở phiên</a>
                                <button class="secondary" type="button" data-modal-open="dashboard-complete-session-{{ $usageSession->id }}">Hoàn tất</button>
                            </div>
                        </td>
                    </tr>

                    <x-modal id="dashboard-complete-session-{{ $usageSession->id }}" title="Hoàn tất phiên" description="Xác nhận phiên đã kết thúc.">
                        <form method="POST" action="{{ route('usage-sessions.complete', $usageSession) }}" data-ajax-form>
                            @csrf
                            @include('usage-sessions.partials.complete-form', ['usageSession' => $usageSession])
                            <div class="actions" style="margin-top: 16px;">
                                <button class="primary" type="submit">Hoàn tất phiên</button>
                                <button type="button" data-modal-close>Đóng</button>
                            </div>
                        </form>
                    </x-modal>
                @empty
                    <tr><td colspan="5" class="muted">Không có phiên đang chạy.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Thiết bị bật nhưng idle</h2>
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên thiết bị</th>
                    <th>Lab</th>
                    <th>Utilization</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary->tables['powered_idle_equipments'] as $equipment)
                    <tr>
                        <td>{{ $equipment->equipment_code }}</td>
                        <td>{{ $equipment->name }}</td>
                        <td>{{ $equipment->laboratory }}</td>
                        <td>{{ $equipment->utilization_rate }}%</td>
                        <td><a class="button" href="{{ route('equipments.show', $equipment) }}">Chi tiết</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Không có thiết bị POWERED_IDLE.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
