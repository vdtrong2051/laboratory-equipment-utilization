@extends('layouts.app')

@section('title', 'Chi tiết bất thường')

@section('content')
    @php
        $ruleLabel = \App\Support\UiLabel::abnormalRule($pattern->rule_name);
        $message = \App\Support\UiLabel::abnormalMessage($pattern->rule_name, $pattern->message);
    @endphp

    <div class="page-head">
        <div>
            <h1>{{ $ruleLabel }}</h1>
            <p class="muted">{{ $pattern->equipment->equipment_code }} · {{ $pattern->equipment->name }}</p>
        </div>
        <div class="actions">
            <a class="button" href="{{ route('abnormal-patterns.index') }}">← Danh sách bất thường</a>
            <a class="button" href="{{ route('equipments.show', $pattern->equipment) }}">Mở thiết bị</a>
            @if ($pattern->status === 'open')
                <button class="secondary" type="button" data-modal-open="review-pattern-modal">Tiếp nhận xử lý</button>
            @elseif ($pattern->status === 'reviewed')
                <button class="primary" type="button" data-modal-open="resolve-pattern-modal">Hoàn tất xử lý</button>
            @endif
        </div>
    </div>

    <section class="panel">
        <h2>Tóm tắt xử lý</h2>
        <div class="detail-grid">
            <div class="metric"><span class="muted">Loại bất thường</span><strong>{{ $ruleLabel }}</strong></div>
            <div class="metric"><span class="muted">Mức độ</span><strong>{{ \App\Support\UiLabel::abnormalSeverity($pattern->severity) }}</strong></div>
            <div class="metric"><span class="muted">Trạng thái</span><strong>{{ \App\Support\UiLabel::abnormalStatus($pattern->status) }}</strong></div>
            <div class="metric"><span class="muted">Phát hiện lúc</span><strong>{{ $pattern->detected_at?->format('d/m/Y H:i') ?? '-' }}</strong></div>
            <div class="metric"><span class="muted">Người tiếp nhận</span><strong>{{ $pattern->reviewedBy?->name ?? '-' }}</strong></div>
            <div class="metric"><span class="muted">Người hoàn tất</span><strong>{{ $pattern->resolvedBy?->name ?? '-' }}</strong></div>
        </div>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Vấn đề cần kiểm tra</h2>
        <p>{{ $message }}</p>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Dữ liệu làm căn cứ</h2>
        @if (! empty($pattern->evidence))
            <div class="detail-grid">
                @foreach ($pattern->evidence as $key => $value)
                    <div class="metric">
                        <span class="muted">{{ str($key)->headline() }}</span>
                        <strong>{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</strong>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">Chưa có dữ liệu căn cứ chi tiết cho bất thường này.</div>
        @endif
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Lịch sử xử lý</h2>
        <div class="user-list">
            <div class="context-item">
                <strong>{{ $pattern->detected_at?->format('d/m/Y H:i') ?? '-' }} — Hệ thống phát hiện bất thường</strong>
                <span>{{ $ruleLabel }}</span>
            </div>
            @if ($pattern->reviewed_at)
                <div class="context-item">
                    <strong>{{ $pattern->reviewed_at->format('d/m/Y H:i') }} — {{ $pattern->reviewedBy?->name ?? 'Người quản lý' }} tiếp nhận xử lý</strong>
                    <span>Bất thường chuyển sang trạng thái đang xử lý.</span>
                </div>
            @endif
            @if ($pattern->resolved_at)
                <div class="context-item">
                    <strong>{{ $pattern->resolved_at->format('d/m/Y H:i') }} — {{ $pattern->resolvedBy?->name ?? 'Người quản lý' }} hoàn tất xử lý</strong>
                    <span>{{ $pattern->resolution_note ?? 'Không có ghi chú kết quả.' }}</span>
                </div>
            @endif
        </div>
    </section>

    <details class="panel" style="margin-top: 16px;">
        <summary><strong>Chi tiết kỹ thuật</strong></summary>
        <pre style="white-space: pre-wrap; margin-top: 12px;">{{ json_encode($pattern->evidence ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </details>

    @if ($pattern->status === 'open')
        <x-modal id="review-pattern-modal" title="Tiếp nhận xử lý" description="{{ $ruleLabel }}">
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
        <x-modal id="resolve-pattern-modal" title="Hoàn tất xử lý" description="{{ $ruleLabel }}">
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
@endsection
