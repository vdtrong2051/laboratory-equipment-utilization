@extends('layouts.app')

@section('title', 'Thêm thiết bị')

@section('content')
    <div class="page-head">
        <div>
            <h1>Thêm thiết bị</h1>
            <p class="muted">Tạo mới một thiết bị trong danh mục phòng thí nghiệm.</p>
        </div>
    </div>

    <form class="panel" method="POST" action="{{ route('equipments.store') }}">
        @csrf
        @include('equipments._form', ['submitLabel' => 'Tạo thiết bị'])
    </form>
@endsection
