@extends('layouts.app')

@section('title', 'Chi tiết Analysis Run')

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
            <h1>Chi tiết Analysis Run</h1>
            <p class="muted">Run #{{ $analysisRun->id }} · {{ $analysisRun->trigger_source }}</p>
        </div>
        <div class="actions">
            <a class="button" href="{{ route('analysis-runs.index') }}">Danh sách</a>
            <a class="button" href="{{ route($dashboardRoute) }}">Dashboard vai trò</a>
        </div>
    </div>

    <x-flow-stepper :steps="$flow['steps']" :current-step="$flow['current_step']" :story="$flow['story']" />

    <section class="panel">
        <h2>Thông tin chạy batch</h2>
        <div class="detail-grid">
            <div class="metric"><span class="muted">Trạng thái</span><strong>{{ $analysisRun->status }}</strong></div>
            <div class="metric"><span class="muted">Nguồn chạy</span><strong>{{ $analysisRun->trigger_source }}</strong></div>
            <div class="metric"><span class="muted">Người chạy</span><strong>{{ $analysisRun->triggeredBy?->name ?? '-' }}</strong></div>
            <div class="metric"><span class="muted">Bắt đầu</span><strong>{{ $analysisRun->started_at?->format('d/m/Y H:i') ?? '-' }}</strong></div>
            <div class="metric"><span class="muted">Kết thúc</span><strong>{{ $analysisRun->finished_at?->format('d/m/Y H:i') ?? '-' }}</strong></div>
            <div class="metric"><span class="muted">Kỳ phân tích</span><strong>{{ $analysisRun->period_start->format('d/m') }} - {{ $analysisRun->period_end->format('d/m/Y') }}</strong></div>
        </div>
    </section>

    <section class="detail-grid" style="margin-top: 16px;">
        <div class="metric"><span class="muted">Thiết bị xử lý</span><strong>{{ $analysisRun->processed_equipment }}</strong></div>
        <div class="metric"><span class="muted">Operational status</span><strong>{{ $analysisRun->operational_status_evaluated }}</strong></div>
        <div class="metric"><span class="muted">Usage metrics</span><strong>{{ $analysisRun->usage_metrics_calculated }}</strong></div>
        <div class="metric"><span class="muted">Rule sets</span><strong>{{ $analysisRun->rule_sets_evaluated }}</strong></div>
        <div class="metric"><span class="muted">Rule match</span><strong>{{ $analysisRun->matched_rules }}</strong></div>
    </section>

    @if ($analysisRun->error_message)
        <section class="panel" style="margin-top: 16px;">
            <h2>Lỗi</h2>
            <p>{{ $analysisRun->error_message }}</p>
        </section>
    @endif

    <section class="panel" style="margin-top: 16px;">
        <h2>Summary JSON</h2>
        <pre style="white-space: pre-wrap; margin: 0;">{{ json_encode($analysisRun->summary ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </section>
@endsection
