@extends('layouts.app')

@section('title', $workspace['title'] ?? 'Researcher Dashboard')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $workspace['title'] ?? 'Researcher Dashboard' }}</h1>
            <p class="muted">{{ $workspace['subtitle'] ?? 'Không gian đặt thiết bị và theo dõi các lượt sử dụng liên quan đến người dùng nghiên cứu.' }}</p>
        </div>
        <div class="actions">
            @include('dashboard.partials.quick-actions', ['actions' => $workspace['quick_actions'] ?? []])
        </div>
    </div>

    @include('dashboard.partials.workspace-intro', ['workspace' => $workspace, 'insight' => $insight])

    <section class="detail-grid">
        <div class="metric"><span class="muted">Booking của tôi</span><strong>{{ $summary->cards['my_booking_count'] }}</strong></div>
        <div class="metric"><span class="muted">Sắp sử dụng</span><strong>{{ $summary->cards['upcoming_booking_count'] }}</strong></div>
        <div class="metric"><span class="muted">Đang check-in</span><strong>{{ $summary->cards['checked_in_session_count'] }}</strong></div>
        <div class="metric"><span class="muted">Đã hoàn tất</span><strong>{{ $summary->cards['completed_session_count'] }}</strong></div>
        <div class="metric"><span class="muted">Thiết bị có thể đặt</span><strong>{{ $summary->cards['available_equipment_count'] }}</strong></div>
    </section>

    <x-modal id="create-booking-modal" title="Tạo lịch đặt" description="Tạo booking cá nhân mà không rời dashboard.">
        <form method="POST" action="{{ route('bookings.store') }}" data-ajax-form>
            @csrf
            @include('bookings.partials.create-form', [
                'booking' => $booking,
                'idPrefix' => 'researcher_booking_modal',
            ])
            <div class="actions" style="margin-top: 16px;">
                <button class="primary" type="submit">Tạo lịch đặt</button>
                <button type="button" data-modal-close>Đóng</button>
            </div>
        </form>
    </x-modal>

    <section class="panel" style="margin-top: 16px;">
        <h2>Lịch sử dụng của tôi</h2>
        <table>
            <thead>
                <tr>
                    <th>Thiết bị</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Trạng thái</th>
                    <th>Mục đích</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary->tables['my_upcoming_bookings'] as $booking)
                    <tr>
                        <td>{{ $booking->equipment?->equipment_code }}</td>
                        <td>{{ $booking->start_time?->format('Y-m-d H:i') }}</td>
                        <td>{{ $booking->end_time?->format('Y-m-d H:i') }}</td>
                        <td><span class="badge">{{ $booking->status->value }}</span></td>
                        <td>{{ $booking->purpose }}</td>
                        <td><a class="button" href="{{ route('bookings.show', $booking) }}">Chi tiết</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Bạn chưa có lịch sử dụng sắp tới.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Phiên sử dụng gần đây</h2>
        <table>
            <thead>
                <tr>
                    <th>Thiết bị</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary->tables['my_recent_sessions'] as $usageSession)
                    <tr>
                        <td>{{ $usageSession->equipment?->equipment_code }}</td>
                        <td>{{ $usageSession->started_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $usageSession->ended_at?->format('Y-m-d H:i') ?? 'Đang chạy' }}</td>
                        <td><span class="badge">{{ $usageSession->status->value }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Bạn chưa có phiên sử dụng nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Thiết bị gợi ý</h2>
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên thiết bị</th>
                    <th>Loại</th>
                    <th>Lab</th>
                    <th>Thời lượng</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary->tables['suggested_equipments'] as $equipment)
                    <tr>
                        <td>{{ $equipment->equipment_code }}</td>
                        <td>{{ $equipment->name }}</td>
                        <td>{{ $equipment->type }}</td>
                        <td>{{ $equipment->laboratory }}</td>
                        <td>{{ $equipment->allowed_usage_duration_minutes }} phút</td>
                        <td><a class="button" href="{{ route('equipments.show', $equipment) }}">Xem</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Chưa có thiết bị gợi ý.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
