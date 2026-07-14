@extends('layouts.app')

@section('title', 'Laboratory Equipment Utilization System')

@section('content')
    <section class="panel">
        <div class="page-head">
            <div>
                <h1>Laboratory Equipment Utilization System</h1>
                <p class="muted">Nền quản lý thiết bị đã sẵn sàng. Phase 3 đang mở màn hình quản lý danh mục thiết bị.</p>
            </div>
            <a class="button primary" href="{{ route('equipments.index') }}">Xem thiết bị</a>
        </div>
    </section>
@endsection
