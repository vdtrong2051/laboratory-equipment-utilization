@extends('layouts.app')

@section('title', 'Tạo lịch đặt')

@section('content')
    <div class="page-head">
        <div>
            <h1>Tạo lịch đặt</h1>
            <p class="muted">Booking mới sẽ ở trạng thái BOOKED. Kỹ thuật viên sẽ check-in khi người dùng nhận thiết bị.</p>
        </div>
        <a class="button" href="{{ route('bookings.index') }}">Danh sách lịch</a>
    </div>

    <form class="panel" method="POST" action="{{ route('bookings.store') }}">
        @csrf
        @include('bookings.partials.create-form', ['idPrefix' => 'booking_create_page'])

        <div class="actions" style="margin-top: 16px;">
            <button class="primary" type="submit">Tạo lịch đặt</button>
            <a class="button" href="{{ route('bookings.index') }}">Huỷ</a>
        </div>
    </form>
@endsection
