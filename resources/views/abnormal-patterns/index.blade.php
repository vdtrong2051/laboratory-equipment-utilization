@extends('layouts.app')

@section('title', 'Bất thường')

@section('content')
    @php
        $currentUser = app(\App\Services\UserContextService::class)->currentOrNull();
        $dashboardRoute = match (true) {
            $currentUser?->isAdmin() => 'dashboard.admin',
            $currentUser?->isManager() => 'dashboard.manager',
            $currentUser?->isLabStaff() => 'dashboard.lab-staff',
            $currentUser?->isResearcher() => 'dashboard.researcher',
            default => 'dashboard.manager',
        };
    @endphp

    <div class="page-head">
        <div>
            <h1>Bất thường thiết bị</h1>
            <p class="muted">Theo dõi các vấn đề khai thác, tiếp nhận xử lý và hoàn tất xử lý theo từng thiết bị.</p>
        </div>
        <a class="button" href="{{ route($dashboardRoute) }}">Dashboard vai trò</a>
    </div>

    <form class="panel filter-toolbar" method="GET" action="{{ route('abnormal-patterns.index') }}">
        <div class="field">
            <label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Support\UiLabel::abnormalStatus($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="severity">Mức độ</label>
            <select id="severity" name="severity">
                <option value="">Tất cả</option>
                @foreach ($severities as $severity)
                    <option value="{{ $severity }}" @selected(request('severity') === $severity)>{{ \App\Support\UiLabel::abnormalSeverity($severity) }}</option>
                @endforeach
            </select>
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
            <label for="rule_name">Loại bất thường</label>
            <select id="rule_name" name="rule_name">
                <option value="">Tất cả</option>
                @foreach ($ruleNames as $ruleName)
                    <option value="{{ $ruleName }}" @selected(request('rule_name') === $ruleName)>{{ \App\Support\UiLabel::abnormalRule($ruleName) }}</option>
                @endforeach
            </select>
        </div>
        <button class="primary" type="submit">Lọc</button>
    </form>

    <div class="panel table-card" id="abnormal-patterns-table">
        <div class="table-toolbar">
            <div>
                <h2>Bất thường</h2>
                <div class="record-count">{{ $patterns->total() }} bản ghi</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Phát hiện</th>
                        <th>Thiết bị</th>
                        <th>Loại bất thường</th>
                        <th>Mức độ</th>
                        <th>Trạng thái</th>
                        <th>Thông báo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patterns as $pattern)
                        <tr data-href="{{ route('abnormal-patterns.show', $pattern) }}">
                            <td>{{ $pattern->detected_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $pattern->equipment->equipment_code }} · {{ $pattern->equipment->name }}</td>
                            <td>{{ \App\Support\UiLabel::abnormalRule($pattern->rule_name) }}</td>
                            <td><span class="badge">{{ \App\Support\UiLabel::abnormalSeverity($pattern->severity) }}</span></td>
                            <td><span class="badge">{{ \App\Support\UiLabel::abnormalStatus($pattern->status) }}</span></td>
                            <td>{{ \App\Support\UiLabel::abnormalMessage($pattern->rule_name, $pattern->message) }}</td>
                            <td>
                                <div class="row-actions">
                                    <a class="button primary" href="{{ route('abnormal-patterns.show', $pattern) }}">Chi tiết</a>
                                    @if ($pattern->status !== 'resolved')
                                        <details class="action-menu">
                                            <summary class="button">...</summary>
                                            <div class="action-menu-panel">
                                                @if ($pattern->status === 'open')
                                                    <button class="secondary" type="button" data-modal-open="review-pattern-{{ $pattern->id }}">Tiếp nhận xử lý</button>
                                                @endif
                                                @if ($pattern->status === 'reviewed')
                                                    <button class="primary" type="button" data-modal-open="resolve-pattern-{{ $pattern->id }}">Hoàn tất xử lý</button>
                                                @endif
                                                <a class="button" href="{{ route('equipments.show', $pattern->equipment) }}">Mở thiết bị</a>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @if ($pattern->status === 'open')
                            <x-modal id="review-pattern-{{ $pattern->id }}" title="Tiếp nhận xử lý" description="{{ \App\Support\UiLabel::abnormalRule($pattern->rule_name) }}">
                                <form method="POST" action="{{ route('abnormal-patterns.review', $pattern) }}" data-ajax-form>
                                    @csrf
                                    @include('abnormal-patterns.partials.review-form', ['pattern' => $pattern])
                                    <div class="actions" style="margin-top: 16px;">
                                        <button class="secondary" type="submit">Tiếp nhận xử lý</button>
                                        <button type="button" data-modal-close>Đóng</button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif

                        @if ($pattern->status === 'reviewed')
                            <x-modal id="resolve-pattern-{{ $pattern->id }}" title="Hoàn tất xử lý" description="{{ \App\Support\UiLabel::abnormalRule($pattern->rule_name) }}">
                                <form method="POST" action="{{ route('abnormal-patterns.resolve', $pattern) }}" data-ajax-form>
                                    @csrf
                                    @include('abnormal-patterns.partials.resolve-form', ['pattern' => $pattern])
                                    <div class="actions" style="margin-top: 16px;">
                                        <button class="primary" type="submit">Hoàn tất xử lý</button>
                                        <button type="button" data-modal-close>Đóng</button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">Không có bất thường phù hợp với bộ lọc hiện tại.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $patterns->links() }}
        </div>
    </div>
@endsection
