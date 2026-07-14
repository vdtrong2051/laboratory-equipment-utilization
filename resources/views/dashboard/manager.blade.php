@extends('layouts.app')

@section('title', $workspace['title'] ?? 'Tổng quan khai thác')

@section('content')
    @php
        $latestRun = $summary->tables['latest_analysis_runs']->first();
        $openPattern = $summary->tables['open_abnormal_patterns']->first();
        $missingDataCount = max(0, (int) $summary->cards['equipment_count'] - (int) $summary->tables['latest_usage_metrics']->count());
    @endphp

    <div class="page-head">
        <div>
            <h1>{{ $workspace['title'] ?? 'Tổng quan khai thác' }}</h1>
            <p class="muted">{{ $workspace['subtitle'] ?? 'Theo dõi mức sử dụng, thiết bị cần chú ý và các bất thường cần xử lý.' }}</p>
        </div>
        <div class="actions">
            <button class="button primary" type="button" data-modal-open="run-batch-analysis-modal">Chạy phân tích</button>
        </div>
    </div>

    <section class="workspace-context">
        <div class="panel insight-box">
            <h2>Nhận định</h2>
            <p class="insight-headline">{{ $insight['headline'] }}</p>
            <p>{{ $insight['narrative'] }}</p>
        </div>

        <div class="detail-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
            <div class="metric"><span class="muted">Bất thường mở</span><strong>{{ $summary->cards['open_abnormal_count'] }}</strong></div>
            <div class="metric"><span class="muted">Thiết bị quá tải</span><strong>{{ $summary->cards['capacity_pressure_count'] }}</strong></div>
            <div class="metric"><span class="muted">Ít được sử dụng</span><strong>{{ $summary->cards['underutilized_count'] }}</strong></div>
            <div class="metric"><span class="muted">Khai thác trung bình</span><strong>{{ $summary->cards['average_actual_utilization_rate'] }}%</strong></div>
        </div>
    </section>

    <section class="panel">
        <h2>Việc cần xử lý</h2>
        <div class="user-list">
            <div class="context-item">
                <strong>{{ $openPattern ? 'Kiểm tra cảnh báo của '.$openPattern->equipment->equipment_code : 'Không có bất thường mở' }}</strong>
                <span>{{ $openPattern ? \App\Support\UiLabel::abnormalMessage($openPattern->rule_name, $openPattern->message) : 'Tiếp tục theo dõi sau lần phân tích tiếp theo.' }}</span>
                @if ($openPattern)
                    <div class="actions" style="margin-top: 8px;">
                        <a class="button primary" href="{{ route('abnormal-patterns.show', $openPattern) }}">Xem cảnh báo</a>
                    </div>
                @endif
            </div>

            <div class="context-item">
                <strong>{{ $latestRun ? 'Lần phân tích gần nhất: '.$latestRun->started_at?->format('d/m/Y H:i') : 'Chưa có lần phân tích định kỳ nào' }}</strong>
                <span>{{ $latestRun ? $latestRun->processed_equipment.' thiết bị được xử lý, '.$latestRun->matched_rules.' quy tắc phát hiện.' : 'Chạy phân tích để tạo dữ liệu nền cho đánh giá khai thác.' }}</span>
                <div class="actions" style="margin-top: 8px;">
                    @if ($latestRun)
                        <a class="button" href="{{ route('analysis-runs.show', $latestRun) }}">Xem lần phân tích</a>
                    @else
                        <button class="button secondary" type="button" data-modal-open="run-batch-analysis-modal">Chạy phân tích</button>
                    @endif
                </div>
            </div>

            <div class="context-item">
                <strong>Dữ liệu khai thác còn trống ở {{ $missingDataCount }} thiết bị</strong>
                <span>Ưu tiên kiểm tra các thiết bị thiếu phiên sử dụng hoặc chưa có chỉ số trong kỳ gần nhất.</span>
                <div class="actions" style="margin-top: 8px;">
                    <a class="button" href="{{ route('equipments.index', ['attention' => '1']) }}">Xem thiết bị cần chú ý</a>
                </div>
            </div>
        </div>
    </section>

    <x-modal id="run-batch-analysis-modal" title="Chạy phân tích định kỳ" description="Tạo lần phân tích mới cho 30 ngày gần nhất.">
        <form method="POST" action="{{ route('dashboard.manager.run-analysis') }}" data-ajax-form>
            @csrf
            <p>Hệ thống sẽ tính lại chỉ số khai thác, đánh giá các quy tắc phát hiện và lưu lịch sử phân tích để kiểm tra sau.</p>
            <p class="muted">Thao tác này dùng dữ liệu mô phỏng hiện có, chưa gọi trực tiếp thiết bị IoT.</p>
            <div class="actions" style="margin-top: 16px;">
                <button class="primary" type="submit">Chạy phân tích</button>
                <button type="button" data-modal-close>Đóng</button>
            </div>
        </form>
    </x-modal>

    <section class="panel" style="margin-top: 16px;">
        <div class="table-toolbar" style="padding: 0 0 12px; border-bottom: 0;">
            <div>
                <h2>Thiết bị ưu tiên</h2>
                <div class="record-count">Chỉ hiển thị thiết bị có bất thường hoặc tín hiệu khai thác cần chú ý</div>
            </div>
            <a class="button" href="{{ route('equipments.index', ['attention' => '1']) }}">Xem tất cả</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Thiết bị</th>
                    <th>Vấn đề</th>
                    <th>Khai thác</th>
                    <th>Lần dùng cuối</th>
                    <th>Mức độ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary->tables['attention_equipments'] as $equipment)
                    @php
                        $issue = $equipment->abnormal_patterns_count > 0
                            ? $equipment->abnormal_patterns_count.' bất thường mở'
                            : \App\Support\UiLabel::analysisStatus($equipment->current_analysis_status->value);
                        $severity = $equipment->abnormal_patterns_count > 0 ? 'Cần kiểm tra' : 'Theo dõi';
                    @endphp
                    <tr data-href="{{ route('equipments.show', $equipment) }}">
                        <td><strong>{{ $equipment->equipment_code }}</strong><br><span class="muted">{{ $equipment->name }}</span></td>
                        <td>{{ $issue }}</td>
                        <td>{{ $equipment->utilization_rate }}%</td>
                        <td>{{ $equipment->last_used_at?->format('d/m/Y H:i') ?? 'Chưa có' }}</td>
                        <td><span class="badge">{{ $severity }}</span></td>
                        <td><a class="button primary" href="{{ route('equipments.show', $equipment) }}">Mở</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">Chưa có thiết bị cần chú ý.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <div class="table-toolbar" style="padding: 0 0 12px; border-bottom: 0;">
            <div>
                <h2>Phân tích gần nhất</h2>
                <div class="record-count">Dùng để kiểm tra dữ liệu đã được cập nhật đến đâu</div>
            </div>
            <a class="button" href="{{ route('analysis-runs.index') }}">Lịch sử phân tích</a>
        </div>

        @if ($latestRun)
            <div class="detail-grid">
                <div class="metric"><span class="muted">Thời điểm chạy</span><strong>{{ $latestRun->started_at?->format('d/m/Y H:i') ?? '-' }}</strong></div>
                <div class="metric"><span class="muted">Thiết bị xử lý</span><strong>{{ $latestRun->processed_equipment }}</strong></div>
                <div class="metric"><span class="muted">Quy tắc phát hiện</span><strong>{{ $latestRun->matched_rules }}</strong></div>
            </div>
        @else
            <div class="empty-state">Chưa có lần phân tích nào. Hãy chạy phân tích để tạo dữ liệu đánh giá đầu tiên.</div>
        @endif
    </section>
@endsection
