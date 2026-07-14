@extends('layouts.app')

@section('title', 'Không có quyền truy cập')

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
        $message = $exception->getMessage() ?: 'Vai trò hiện tại không có quyền truy cập khu vực này.';
    @endphp

    <div class="page-head">
        <div>
            <h1>Không có quyền truy cập</h1>
            <p class="muted">{{ $message }}</p>
        </div>
        <div class="actions">
            <a class="button primary" href="{{ route($dashboardRoute) }}">Về dashboard vai trò</a>
            <a class="button" href="{{ route('demo-login.index') }}">Đổi tài khoản demo</a>
        </div>
    </div>

    <section class="panel">
        <h2>Ngữ cảnh hiện tại</h2>
        <div class="detail-grid">
            <div class="metric">
                <span class="muted">User</span>
                <strong>{{ $currentUser?->name ?? 'Chưa có demo user' }}</strong>
            </div>
            <div class="metric">
                <span class="muted">Vai trò</span>
                <strong>{{ $currentUser?->roleLabel() ?? 'Demo mode' }}</strong>
            </div>
            <div class="metric">
                <span class="muted">Gợi ý</span>
                <strong>Chọn workspace phù hợp</strong>
                <p class="muted">Dùng nút Đổi tài khoản demo trên header để vào đúng vai trò cần kiểm thử.</p>
            </div>
        </div>
    </section>
@endsection
