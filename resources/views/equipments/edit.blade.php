@extends('layouts.app')

@section('title', 'Sửa thiết bị')

@section('content')
    <div class="page-head">
        <div>
            <h1>Sửa thiết bị</h1>
            <p class="muted">{{ $equipment->equipment_code }} · {{ $equipment->name }}</p>
        </div>
        <a class="button" href="{{ route('equipments.show', $equipment) }}">Xem chi tiết</a>
    </div>

    <form class="panel" method="POST" action="{{ route('equipments.update', $equipment) }}">
        @csrf
        @method('PUT')
        @include('equipments._form', ['submitLabel' => 'Lưu thay đổi'])
    </form>
@endsection
