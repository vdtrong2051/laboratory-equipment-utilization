@extends('layouts.app')

@section('title', $workspace['title'] ?? 'Tổng quan quản trị')

@section('content')
    @php
        $cards = $summary->cards;
        $latestRun = $cards['latest_analysis_run_status'] ?? 'Chưa có';
        $openAbnormal = (int) ($cards['open_abnormal_count'] ?? 0);
        $checkedInSessions = (int) ($cards['checked_in_session_count'] ?? 0);
        $roleLabels = [
            \App\Enums\UserRole::Admin->value => 'Quản trị viên',
            \App\Enums\UserRole::Manager->value => 'Quản lý',
            \App\Enums\UserRole::LabStaff->value => 'Kỹ thuật viên',
            \App\Enums\UserRole::Researcher->value => 'Nghiên cứu / Sinh viên',
        ];
    @endphp

    <div class="page-head">
        <div>
            <h1>{{ $workspace['title'] ?? 'Tổng quan quản trị' }}</h1>
            <p class="muted">{{ $workspace['subtitle'] ?? 'Quản lý dữ liệu nền, thiết bị và hoạt động hệ thống.' }}</p>
        </div>
        <div class="actions">
            <button class="button primary" type="button" data-modal-open="create-equipment-modal">Thêm thiết bị</button>
        </div>
    </div>

    <section class="detail-grid admin-kpi-grid">
        <div class="metric">
            <span class="muted">Người dùng hoạt động</span>
            <strong>{{ $cards['active_user_count'] }}/{{ $cards['user_count'] }}</strong>
        </div>
        <div class="metric">
            <span class="muted">Thiết bị</span>
            <strong>{{ $cards['equipment_count'] }}</strong>
        </div>
        <div class="metric">
            <span class="muted">Bất thường mở</span>
            <strong>{{ $openAbnormal }}</strong>
        </div>
        <div class="metric">
            <span class="muted">Phiên đang chạy</span>
            <strong>{{ $checkedInSessions }}</strong>
        </div>
    </section>

    <section class="panel insight-box" style="margin-top: 16px;">
        <h2>Tình trạng hệ thống</h2>
        <p class="insight-headline">{{ $insight['headline'] }}</p>
        <p class="muted">{{ $insight['narrative'] }}</p>
        <div class="actions" style="margin-top: 12px;">
            <a class="button" href="{{ route('equipments.index') }}">Xem chi tiết hệ thống</a>
        </div>
    </section>

    <section class="workspace-context" style="margin-top: 16px;">
        <div class="panel">
            <h2>Việc cần xử lý</h2>
            <div class="user-list">
                <div class="context-item">
                    <span>Bất thường chưa xử lý</span>
                    <strong>{{ $openAbnormal }}</strong>
                    <div class="actions" style="margin-top: 8px;">
                        <a class="button" href="{{ route('abnormal-patterns.index') }}">Xem bất thường</a>
                    </div>
                </div>
                <div class="context-item">
                    <span>Lần phân tích gần nhất</span>
                    <strong>{{ $latestRun }}</strong>
                    <div class="actions" style="margin-top: 8px;">
                        <a class="button" href="{{ route('analysis-runs.index') }}">Xem lịch sử phân tích</a>
                    </div>
                </div>
                <div class="context-item">
                    <span>Danh mục thiết bị</span>
                    <strong>{{ $cards['equipment_count'] }} thiết bị</strong>
                    <div class="actions" style="margin-top: 8px;">
                        <button class="button primary" type="button" data-modal-open="create-equipment-modal">Thêm thiết bị</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h2>Trạng thái hệ thống</h2>
            <div class="context-grid">
                <div class="context-item">
                    <span>Phân tích gần nhất</span>
                    <strong>{{ $latestRun }}</strong>
                </div>
                <div class="context-item">
                    <span>Booking</span>
                    <strong>{{ $cards['booking_count'] }}</strong>
                </div>
            </div>

            <h2 style="margin-top: 16px;">Người dùng theo vai trò</h2>
            <div class="actions">
                @foreach ($summary->charts['role_counts'] as $role => $count)
                    <span class="badge">{{ $roleLabels[$role] ?? $role }}: {{ $count }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="panel table-card" style="margin-top: 16px;">
        <div class="table-toolbar">
            <div>
                <h2>Người dùng hệ thống</h2>
                <div class="record-count">{{ $summary->tables['recent_users']->count() }} tài khoản đang hiển thị</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Đơn vị</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary->tables['recent_users'] as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge">{{ $user->roleLabel() }}</span></td>
                            <td>{{ $user->department ?? 'N/A' }}</td>
                            <td>{{ $user->is_active ? 'Đang hoạt động' : 'Tạm ngưng' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">Chưa có người dùng hệ thống.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-modal id="create-equipment-modal" title="Thêm thiết bị" description="Tạo thiết bị mới cho danh mục quản trị.">
        <form method="POST" action="{{ route('equipments.store') }}" data-ajax-form>
            @csrf
            @include('equipments._form', [
                'submitLabel' => 'Tạo thiết bị',
            ])
        </form>
    </x-modal>
@endsection
