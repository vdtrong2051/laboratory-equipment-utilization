@props(['steps', 'currentStep' => null, 'story' => null])

<section class="panel flow-panel">
    <div class="page-head" style="margin-bottom: 12px;">
        <div>
            <h2>Luồng nghiệp vụ</h2>
            @if ($story)
                <p class="muted">{{ $story }}</p>
            @endif
        </div>
    </div>

    <ol class="flow-stepper">
        @foreach ($steps as $step)
            @php
                $state = $step['state'] ?? ($step['key'] === $currentStep ? 'current' : 'pending');
            @endphp
            <li class="flow-step {{ $state }}" aria-current="{{ $step['key'] === $currentStep ? 'step' : 'false' }}">
                <span class="flow-dot">{{ $loop->iteration }}</span>
                <div>
                    <strong>{{ $step['label'] }}</strong>
                    <span>{{ $step['description'] }}</span>
                </div>
            </li>
        @endforeach
    </ol>
</section>
