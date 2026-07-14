@extends('layouts.app')

@section('title', 'Lịch sử phân tích')

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
            <h1>Lịch sử phân tích</h1>
            <p class="muted">Theo dõi các lần phân tích dữ liệu sử dụng thiết bị và số rule được phát hiện.</p>
        </div>
    </div>

    <form class="panel filter-toolbar" method="GET" action="{{ route('analysis-runs.index') }}">
        <div class="field">
            <label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="trigger_source">Nguồn chạy</label>
            <select id="trigger_source" name="trigger_source">
                <option value="">Tất cả</option>
                @foreach ($triggerSources as $triggerSource)
                    <option value="{{ $triggerSource }}" @selected(request('trigger_source') === $triggerSource)>{{ $triggerSource }}</option>
                @endforeach
            </select>
        </div>
        <button class="primary" type="submit">Lọc</button>
    </form>

    <div class="panel table-card">
        <div class="table-toolbar">
            <div>
                <h2>Các lần phân tích</h2>
                <div class="record-count">{{ $analysisRuns->total() }} bản ghi</div>
            </div>
        </div>

        @if ($analysisRuns->count() > 0)
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Trạng thái</th>
                            <th>Nguồn</th>
                            <th>Người chạy</th>
                            <th>Thiết bị</th>
                            <th>Rule match</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($analysisRuns as $analysisRun)
                            <tr data-href="{{ route('analysis-runs.show', $analysisRun) }}">
                                <td>{{ $analysisRun->started_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $analysisRun->finished_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td><span class="badge">{{ $analysisRun->status }}</span></td>
                                <td>{{ $analysisRun->trigger_source }}</td>
                                <td>{{ $analysisRun->triggeredBy?->name ?? '-' }}</td>
                                <td>{{ $analysisRun->processed_equipment }}</td>
                                <td>{{ $analysisRun->matched_rules }}</td>
                                <td><a class="button primary" href="{{ route('analysis-runs.show', $analysisRun) }}">Chi tiết</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $analysisRuns->links() }}
            </div>
        @else
            <div class="empty-state">
                <strong>Chưa có lần phân tích nào</strong>
                <p>Chạy phân tích để tính mức sử dụng và phát hiện bất thường. Quản lý phòng thí nghiệm có thể khởi tạo lần phân tích mới từ dashboard quản lý.</p>
                <a class="button" href="{{ route($dashboardRoute) }}">Mở dashboard vai trò</a>
            </div>
        @endif
    </div>
@endsection
