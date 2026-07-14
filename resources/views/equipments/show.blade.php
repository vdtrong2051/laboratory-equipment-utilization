@extends('layouts.app')

@section('title', $equipment->equipment_code)

@section('content')
    @php
        $currentUser = app(\App\Services\UserContextService::class)->currentOrNull();
        $canManageSystem = $currentUser?->canManageSystem() ?? false;
        $canOperateLab = $currentUser?->canOperateLab() ?? false;
        $canViewManagement = $currentUser?->canViewManagementDashboard() ?? false;

        $usageModeLabel = $equipment->usage_mode === \App\Enums\UsageMode::OnSite ? 'Sử dụng tại chỗ' : 'Di động';
        $operationalLabel = \App\Support\UiLabel::operationalStatus($equipment->current_operational_status->value);
        $analysisLabel = \App\Support\UiLabel::analysisStatus($equipment->current_analysis_status->value);
        $latestMetric = $equipment->usageMetrics->first();
        $openPatterns = $equipment->abnormalPatterns;
        $hasUsageData = $equipment->usage_sessions_count > 0 || $equipment->activity_signals_count > 0 || $equipment->power_events_count > 0;
        $hasDataQualityPattern = $openPatterns->contains(fn ($pattern) => class_basename($pattern->rule_name) === 'DemoDataQualityRule');
        $ruleLabel = fn ($ruleName) => \App\Support\UiLabel::abnormalRule($ruleName);
    @endphp

    <div class="page-head">
        <div>
            <p class="muted" style="margin: 0 0 6px;">Thiết bị / {{ $equipment->equipment_code }}</p>
            <h1>{{ $equipment->name }}</h1>
            <p class="muted">{{ $equipment->type }} · {{ $equipment->laboratory }} · {{ $usageModeLabel }}</p>
            <div class="actions" style="margin-top: 8px;">
                <span class="badge">{{ $operationalLabel }}</span>
                <span class="badge">{{ $analysisLabel }}</span>
                @if ($hasDataQualityPattern)
                    <span class="badge">Dữ liệu cần kiểm tra</span>
                @endif
            </div>
        </div>
        <div class="actions">
            <a class="button" href="{{ route('equipments.index') }}">← Danh sách thiết bị</a>
            @if ($canManageSystem)
                <button class="button primary" type="button" data-modal-open="edit-equipment-modal">Sửa</button>
            @endif
            @if ($canOperateLab || $canViewManagement || $canManageSystem)
                <details class="action-menu">
                    <summary class="button">...</summary>
                    <div class="action-menu-panel">
                        @if ($canOperateLab)
                            <form method="POST" action="{{ route('equipments.evaluate-operational-status', $equipment) }}">
                                @csrf
                                <button class="secondary" type="submit">Đồng bộ trạng thái</button>
                            </form>
                        @endif
                        @if ($canViewManagement)
                            <form method="POST" action="{{ route('equipments.calculate-usage-metrics', $equipment) }}">
                                @csrf
                                <button class="secondary" type="submit">Tính lại chỉ số 30 ngày</button>
                            </form>
                            <form method="POST" action="{{ route('equipments.evaluate-rules', $equipment) }}">
                                @csrf
                                <button class="secondary" type="submit">Đánh giá lại bất thường</button>
                            </form>
                        @endif
                        @if ($canManageSystem)
                            <button class="danger" type="button" data-modal-open="archive-equipment-modal">Ngừng sử dụng</button>
                        @endif
                    </div>
                </details>
            @endif
        </div>
    </div>

    <section class="panel">
        <h2>Tình trạng</h2>
        @if ($openPatterns->isNotEmpty())
            <p>Thiết bị đang có {{ $openPatterns->count() }} bất thường cần kiểm tra. Trạng thái khai thác và chất lượng dữ liệu được tách riêng để tránh nhầm lẫn khi đánh giá.</p>
            <div class="actions">
                <a class="button primary" href="{{ route('abnormal-patterns.index', ['equipment_id' => $equipment->id, 'status' => 'open']) }}">Xem cảnh báo</a>
            </div>
        @elseif (! $hasUsageData)
            <p>Thiết bị chưa ghi nhận phiên sử dụng hoặc tín hiệu hoạt động trong kỳ hiện tại. Các chỉ số khai thác chỉ có ý nghĩa sau khi có dữ liệu vận hành.</p>
        @else
            <p>Thiết bị đã có dữ liệu liên quan và chưa có bất thường mở cần xử lý.</p>
        @endif
    </section>

    <section class="detail-grid admin-kpi-grid" style="margin-top: 16px;">
        <div class="metric">
            <span class="muted">Tỷ lệ khai thác</span>
            <strong>{{ $latestMetric?->actual_utilization_rate ?? $equipment->utilization_rate }}%</strong>
        </div>
        <div class="metric">
            <span class="muted">Lần dùng gần nhất</span>
            <strong>{{ $equipment->last_used_at?->format('d/m/Y H:i') ?? 'Chưa có' }}</strong>
        </div>
        <div class="metric">
            <span class="muted">Giới hạn sử dụng</span>
            <strong>{{ $equipment->allowed_usage_duration_minutes ?? '-' }} phút</strong>
        </div>
        <div class="metric">
            <span class="muted">Bất thường mở</span>
            <strong>{{ $openPatterns->count() }}</strong>
        </div>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Tổng quan</h2>
        <div class="detail-grid">
            <div class="metric"><span class="muted">Mã thiết bị</span><strong>{{ $equipment->equipment_code }}</strong></div>
            <div class="metric"><span class="muted">Loại</span><strong>{{ $equipment->type }}</strong></div>
            <div class="metric"><span class="muted">Phòng lab</span><strong>{{ $equipment->laboratory }}</strong></div>
            <div class="metric"><span class="muted">Chế độ sử dụng</span><strong>{{ $usageModeLabel }}</strong></div>
            <div class="metric"><span class="muted">Trạng thái vận hành</span><strong>{{ $operationalLabel }}</strong></div>
            <div class="metric"><span class="muted">Trạng thái phân tích</span><strong>{{ $analysisLabel }}</strong></div>
        </div>
        <p class="muted" style="margin-top: 12px;">
            Dữ liệu liên quan:
            <a href="{{ route('bookings.index', ['equipment_id' => $equipment->id]) }}">{{ $equipment->bookings_count }} lịch đặt</a>
            · <a href="{{ route('usage-sessions.index', ['equipment_id' => $equipment->id]) }}">{{ $equipment->usage_sessions_count }} phiên sử dụng</a>
            · {{ $equipment->power_events_count }} sự kiện nguồn
            · {{ $equipment->activity_signals_count }} tín hiệu hoạt động
            · <a href="{{ route('abnormal-patterns.index', ['equipment_id' => $equipment->id]) }}">{{ $equipment->abnormal_patterns_count }} bất thường</a>
        </p>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Chỉ số khai thác</h2>
        @if ($latestMetric && (int) $latestMetric->total_booked_minutes === 0 && (int) $latestMetric->total_powered_minutes === 0 && (int) $latestMetric->total_active_minutes === 0)
            <div class="empty-state">
                Chưa ghi nhận hoạt động trong kỳ {{ $latestMetric->period_start->format('d/m') }}-{{ $latestMetric->period_end->format('d/m/Y') }}.
            </div>
        @elseif ($equipment->usageMetrics->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Kỳ</th>
                        <th>Thời gian đặt</th>
                        <th>Thời gian bật</th>
                        <th>Hoạt động thực</th>
                        <th>Bật không hoạt động</th>
                        <th>Tỷ lệ khai thác</th>
                        <th>Tỷ lệ nhàn rỗi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipment->usageMetrics as $metric)
                        <tr>
                            <td>{{ $metric->period_start->format('d/m') }} - {{ $metric->period_end->format('d/m/Y') }}</td>
                            <td>{{ $metric->total_booked_minutes }} phút<br><span class="muted">{{ $metric->booking_utilization_rate }}%</span></td>
                            <td>{{ $metric->total_powered_minutes }} phút</td>
                            <td>{{ $metric->total_active_minutes }} phút</td>
                            <td>{{ $metric->total_idle_minutes }} phút</td>
                            <td><span class="badge">{{ $metric->actual_utilization_rate }}%</span></td>
                            <td>{{ $metric->powered_idle_rate }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">Chưa có chỉ số khai thác cho thiết bị này.</div>
        @endif
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Bất thường đang mở</h2>
        <table>
            <thead>
                <tr>
                    <th>Loại bất thường</th>
                    <th>Mức độ</th>
                    <th>Trạng thái</th>
                    <th>Thông báo</th>
                    <th>Phát hiện lúc</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($openPatterns as $pattern)
                    @php
                        $message = \App\Support\UiLabel::abnormalMessage($pattern->rule_name, $pattern->message);
                    @endphp
                    <tr>
                        <td>{{ $ruleLabel($pattern->rule_name) }}</td>
                        <td><span class="badge">{{ \App\Support\UiLabel::abnormalSeverity($pattern->severity) }}</span></td>
                        <td><span class="badge">{{ \App\Support\UiLabel::abnormalStatus($pattern->status) }}</span></td>
                        <td>{{ $message }}</td>
                        <td>{{ $pattern->detected_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>
                            <div class="row-actions">
                                <a class="button primary" href="{{ route('abnormal-patterns.show', $pattern) }}">Xem chi tiết</a>
                                @if ($canViewManagement && $pattern->status === 'open')
                                    <button class="secondary" type="button" data-modal-open="review-pattern-{{ $pattern->id }}">Tiếp nhận xử lý</button>
                                @elseif ($canViewManagement && $pattern->status === 'reviewed')
                                    <button class="secondary" type="button" data-modal-open="resolve-pattern-{{ $pattern->id }}">Hoàn tất xử lý</button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    @if ($canViewManagement && $pattern->status === 'open')
                        <x-modal id="review-pattern-{{ $pattern->id }}" title="Tiếp nhận xử lý" description="{{ $ruleLabel($pattern->rule_name) }}">
                            <form method="POST" action="{{ route('abnormal-patterns.review', $pattern) }}" data-ajax-form>
                                @csrf
                                @include('abnormal-patterns.partials.review-form', ['pattern' => $pattern])
                                <div class="actions" style="margin-top: 16px;">
                                    <button class="secondary" type="submit">Tiếp nhận xử lý</button>
                                    <button type="button" data-modal-close>Đóng</button>
                                </div>
                            </form>
                        </x-modal>
                    @elseif ($canViewManagement && $pattern->status === 'reviewed')
                        <x-modal id="resolve-pattern-{{ $pattern->id }}" title="Hoàn tất xử lý" description="{{ $ruleLabel($pattern->rule_name) }}">
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
                        <td colspan="6" class="muted">Chưa có bất thường đang mở cho thiết bị này.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if ($canManageSystem)
        <x-modal id="edit-equipment-modal" title="Sửa thiết bị" description="Chỉ cập nhật metadata. Trạng thái vận hành, phân tích, tỷ lệ khai thác và lần dùng gần nhất do hệ thống tự tính.">
            <form method="POST" action="{{ route('equipments.update', $equipment) }}" data-ajax-form>
                @csrf
                @method('PUT')
                @include('equipments._form', ['equipment' => $equipment, 'submitLabel' => 'Cập nhật thiết bị'])
            </form>
        </x-modal>

        <x-modal id="archive-equipment-modal" title="Ngừng sử dụng thiết bị" description="{{ $equipment->equipment_code }} · {{ $equipment->name }}">
            <form method="POST" action="{{ route('equipments.destroy', $equipment) }}">
                @csrf
                @method('DELETE')
                <p>Thiết bị sẽ không còn xuất hiện trong danh sách hoạt động, nhưng toàn bộ lịch sử sử dụng, lịch đặt, chỉ số và bất thường vẫn được giữ lại.</p>
                <div class="actions" style="margin-top: 16px;">
                    <button class="danger" type="submit">Ngừng sử dụng thiết bị</button>
                    <button type="button" data-modal-close>Đóng</button>
                </div>
            </form>
        </x-modal>
    @endif
@endsection
