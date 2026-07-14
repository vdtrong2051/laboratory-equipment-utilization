@extends('layouts.app')

@section('title', 'Phiên sử dụng')

@section('content')
    <div class="page-head">
        <div>
            <h1>Phiên sử dụng</h1>
            <p class="muted">Theo dõi các phiên đã check-in, đang dùng hoặc đã hoàn tất.</p>
        </div>
        <a class="button" href="{{ route('bookings.index') }}">Lịch đặt</a>
    </div>

    <form class="panel filter-toolbar" method="GET" action="{{ route('usage-sessions.index') }}">
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

    <div class="panel table-card" id="active-sessions">
        <div class="table-toolbar">
            <div>
                <h2>Phiên sử dụng</h2>
                <div class="record-count">{{ $usageSessions->total() }} bản ghi</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bắt đầu</th>
                        <th>Kết thúc</th>
                        <th>Thiết bị</th>
                        <th>Người dùng</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usageSessions as $usageSession)
                        <tr data-href="{{ route('usage-sessions.show', $usageSession) }}">
                            <td>{{ $usageSession->started_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $usageSession->ended_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $usageSession->equipment->equipment_code }} · {{ $usageSession->equipment->name }}</td>
                            <td>{{ $usageSession->user->name }}</td>
                            <td><span class="badge">{{ $usageSession->status->value }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a class="button primary" href="{{ route('usage-sessions.show', $usageSession) }}">Chi tiết</a>
                                    @if (in_array($usageSession->status, [\App\Enums\BookingStatus::CheckedIn, \App\Enums\BookingStatus::Overdue], true))
                                        <details class="action-menu">
                                            <summary class="button">...</summary>
                                            <div class="action-menu-panel">
                                                <button class="secondary" type="button" data-modal-open="complete-session-{{ $usageSession->id }}">Hoàn tất</button>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @if (in_array($usageSession->status, [\App\Enums\BookingStatus::CheckedIn, \App\Enums\BookingStatus::Overdue], true))
                            <x-modal id="complete-session-{{ $usageSession->id }}" title="Hoàn tất phiên" description="Xác nhận thiết bị đã được trả hoặc phiên sử dụng đã kết thúc.">
                                <form method="POST" action="{{ route('usage-sessions.complete', $usageSession) }}" data-ajax-form>
                                    @csrf
                                    @include('usage-sessions.partials.complete-form', ['usageSession' => $usageSession])
                                    <div class="actions" style="margin-top: 16px;">
                                        <button class="primary" type="submit">Hoàn tất phiên</button>
                                        <button type="button" data-modal-close>Đóng</button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">Chưa có phiên sử dụng phù hợp với bộ lọc hiện tại.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $usageSessions->links() }}
        </div>
    </div>
@endsection
