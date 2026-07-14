@extends('layouts.app')

@section('title', 'Thiết bị')

@section('content')
    @php
        $currentUser = app(\App\Services\UserContextService::class)->currentOrNull();
        $canManageSystem = $currentUser?->canManageSystem() ?? false;
        $attentionUrl = route('equipments.index', ['attention' => '1']);
    @endphp

    <div class="page-head">
        <div>
            <h1>Thiết bị phòng thí nghiệm</h1>
            <p class="muted">Theo dõi thiết bị, trạng thái khai thác và các bất thường cần xử lý.</p>
        </div>
        @if ($canManageSystem)
            <button class="primary" type="button" data-modal-open="create-equipment-modal">Thêm thiết bị</button>
        @endif
    </div>

    <div class="actions" style="margin-bottom: 12px;">
        <a class="button {{ request('attention') ? '' : 'primary' }}" href="{{ route('equipments.index') }}">Tất cả</a>
        <a class="button {{ request('attention') ? 'primary' : '' }}" href="{{ $attentionUrl }}">Cần chú ý</a>
        <a class="button" href="{{ route('equipments.index', ['analysis_status' => \App\Enums\AnalysisStatus::CapacityPressure->value]) }}">Quá tải</a>
        <a class="button" href="{{ route('equipments.index', ['analysis_status' => \App\Enums\AnalysisStatus::Underutilized->value]) }}">Ít sử dụng</a>
        <a class="button" href="{{ route('equipments.index', ['analysis_status' => \App\Enums\AnalysisStatus::IdleWhilePowered->value]) }}">Bật không hoạt động</a>
    </div>

    <form class="panel filter-toolbar" method="GET" action="{{ route('equipments.index') }}">
        @if (request('attention'))
            <input type="hidden" name="attention" value="1">
        @endif
        <div class="field">
            <label for="search">Tìm kiếm</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Mã, tên, loại thiết bị">
        </div>
        <div class="field">
            <label for="laboratory">Phòng lab</label>
            <select id="laboratory" name="laboratory">
                <option value="">Tất cả</option>
                @foreach ($laboratories as $laboratory)
                    <option value="{{ $laboratory->laboratory }}" @selected(request('laboratory') === $laboratory->laboratory)>{{ $laboratory->laboratory }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="operational_status">Vận hành</label>
            <select id="operational_status" name="operational_status">
                <option value="">Tất cả</option>
                @foreach ($operationalStatuses as $status)
                    <option value="{{ $status->value }}" @selected(request('operational_status') === $status->value)>{{ \App\Support\UiLabel::operationalStatus($status->value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="analysis_status">Phân tích</label>
            <select id="analysis_status" name="analysis_status">
                <option value="">Tất cả</option>
                @foreach ($analysisStatuses as $status)
                    <option value="{{ $status->value }}" @selected(request('analysis_status') === $status->value)>{{ \App\Support\UiLabel::analysisStatus($status->value) }}</option>
                @endforeach
            </select>
        </div>
        <button class="primary" type="submit">Lọc</button>
    </form>

    <div class="panel table-card" id="equipments-table">
        <div class="table-toolbar">
            <div>
                <h2>Danh sách thiết bị</h2>
                <div class="record-count">{{ $equipments->total() }} bản ghi</div>
            </div>
            @if ($canManageSystem)
                <button class="primary" type="button" data-modal-open="create-equipment-modal">Thêm thiết bị</button>
            @endif
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên thiết bị</th>
                        <th>Lab</th>
                        <th>Vận hành</th>
                        <th>Phân tích</th>
                        <th>Khai thác</th>
                        <th>Lần dùng gần nhất</th>
                        <th>Bất thường</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipments as $equipment)
                        <tr data-href="{{ route('equipments.show', $equipment) }}">
                            <td><strong>{{ $equipment->equipment_code }}</strong></td>
                            <td>{{ $equipment->name }}</td>
                            <td>{{ $equipment->laboratory }}</td>
                            <td><span class="badge">{{ \App\Support\UiLabel::operationalStatus($equipment->current_operational_status->value) }}</span></td>
                            <td><span class="badge">{{ \App\Support\UiLabel::analysisStatus($equipment->current_analysis_status->value) }}</span></td>
                            <td>{{ $equipment->utilization_rate }}%</td>
                            <td>{{ $equipment->last_used_at?->format('d/m/Y H:i') ?? 'Chưa có' }}</td>
                            <td>{{ $equipment->abnormal_patterns_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <a class="button primary" href="{{ route('equipments.show', $equipment) }}">Chi tiết</a>
                                    @if ($canManageSystem)
                                        <details class="action-menu">
                                            <summary class="button">...</summary>
                                            <div class="action-menu-panel">
                                                <button class="secondary" type="button" data-modal-open="edit-equipment-{{ $equipment->id }}">Sửa thiết bị</button>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @if ($canManageSystem)
                            <x-modal id="edit-equipment-{{ $equipment->id }}" title="Sửa thiết bị" description="{{ $equipment->equipment_code }} · {{ $equipment->name }}">
                                <form method="POST" action="{{ route('equipments.update', $equipment) }}" data-ajax-form>
                                    @csrf
                                    @method('PUT')
                                    @include('equipments._form', ['equipment' => $equipment, 'submitLabel' => 'Cập nhật thiết bị'])
                                </form>
                            </x-modal>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">Không tìm thấy thiết bị phù hợp với bộ lọc hiện tại.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $equipments->links() }}
        </div>
    </div>

    @if ($canManageSystem)
        <x-modal id="create-equipment-modal" title="Thêm thiết bị" description="Tạo nhanh thiết bị mới mà không rời danh sách.">
            <form method="POST" action="{{ route('equipments.store') }}" data-ajax-form>
                @csrf
                @include('equipments._form', ['equipment' => $blankEquipment, 'submitLabel' => 'Tạo thiết bị'])
            </form>
        </x-modal>
    @endif
@endsection
