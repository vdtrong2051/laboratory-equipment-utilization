@props(['id', 'title', 'description' => null])

<div class="modal-backdrop" id="{{ $id }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
    <div class="modal-panel">
        <div class="modal-head">
            <div>
                <h2 id="{{ $id }}-title">{{ $title }}</h2>
                @if ($description)
                    <p class="muted" style="margin: 4px 0 0;">{{ $description }}</p>
                @endif
            </div>
            <button class="icon-button" type="button" data-modal-close aria-label="Đóng modal">×</button>
        </div>
        <div class="modal-body">
            <div class="modal-message" data-modal-message></div>
            {{ $slot }}
        </div>
    </div>
</div>
