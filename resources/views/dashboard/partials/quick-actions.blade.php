@props(['actions' => []])

@foreach ($actions as $action)
    @continue(! \Illuminate\Support\Facades\Route::has($action['route']))

    @php
        $method = strtoupper($action['method'] ?? 'GET');
        $classes = 'button '.(($action['variant'] ?? null) === 'primary' ? 'primary' : '');
    @endphp

    @if (isset($action['modal_id']))
        <button class="{{ trim($classes) }}" type="button" data-modal-open="{{ $action['modal_id'] }}">{{ $action['label'] }}</button>
    @elseif ($method === 'POST')
        <form method="POST" action="{{ route($action['route']) }}">
            @csrf
            <button class="{{ trim($classes) }}" type="submit">{{ $action['label'] }}</button>
        </form>
    @else
        <a class="{{ trim($classes) }}" href="{{ route($action['route']) }}">{{ $action['label'] }}</a>
    @endif
@endforeach
