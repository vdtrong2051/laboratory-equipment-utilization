@extends('layouts.app')

@section('title', 'Chi tiết phiên sử dụng')

@section('content')
    <div class="page-head">
        <div>
            <h1>Chi tiết phiên sử dụng</h1>
            <p class="muted">{{ $usageSession->equipment->equipment_code }} · {{ $usageSession->equipment->name }}</p>
        </div>
        <div class="actions">
            <a class="button" href="{{ route('usage-sessions.index') }}">Danh sách phiên</a>
            @if ($usageSession->booking)
                <a class="button" href="{{ route('bookings.show', $usageSession->booking) }}">Xem booking</a>
            @endif
            @if (in_array($usageSession->status, [\App\Enums\BookingStatus::CheckedIn, \App\Enums\BookingStatus::Overdue], true))
                <button class="primary" type="button" data-modal-open="complete-session-modal">Hoàn tất phiên</button>
            @endif
        </div>
    </div>

    <x-flow-stepper :steps="$flow['steps']" :current-step="$flow['current_step']" :story="$flow['story']" />

    <section class="panel">
        <h2>Thông tin phiên</h2>
        <div class="detail-grid">
            <div class="metric"><span class="muted">Trạng thái</span><strong>{{ $usageSession->status->value }}</strong></div>
            <div class="metric"><span class="muted">Bắt đầu</span><strong>{{ $usageSession->started_at?->format('d/m/Y H:i') ?? '-' }}</strong></div>
            <div class="metric"><span class="muted">Kết thúc</span><strong>{{ $usageSession->ended_at?->format('d/m/Y H:i') ?? '-' }}</strong></div>
            <div class="metric"><span class="muted">Người dùng</span><strong>{{ $usageSession->user->name }}</strong></div>
            <div class="metric"><span class="muted">Check-in bởi</span><strong>{{ $usageSession->checkedInBy?->name ?? '-' }}</strong></div>
            <div class="metric"><span class="muted">Hoàn tất bởi</span><strong>{{ $usageSession->completedBy?->name ?? '-' }}</strong></div>
        </div>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Ghi chú</h2>
        <p>{{ $usageSession->notes ?? 'Chưa có ghi chú.' }}</p>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Telemetry mô phỏng</h2>
        <div class="form-grid two">
            <div class="field">
                <label for="samples">Số mẫu activity signal</label>
                <input id="samples" name="samples" type="number" min="3" max="60" value="12">
            </div>
            <div class="field">
                <label>&nbsp;</label>
                <button class="secondary" type="button" data-simulate-telemetry>Sinh telemetry mô phỏng</button>
            </div>
        </div>
        <p class="muted" data-telemetry-result style="margin-bottom: 0;">Dữ liệu sinh ở đây là nguồn mô phỏng, chưa phải dữ liệu IoT thật.</p>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Activity signals gần nhất</h2>
        <table>
            <thead>
                <tr>
                    <th>Thời điểm</th>
                    <th>Loại signal</th>
                    <th>Giá trị</th>
                    <th>Raw active</th>
                    <th>Gateway</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activitySignals as $activitySignal)
                    <tr>
                        <td>{{ $activitySignal->recorded_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $activitySignal->signal_type }}</td>
                        <td>{{ $activitySignal->signal_value }} {{ $activitySignal->unit }}</td>
                        <td>{{ $activitySignal->is_active ? 'true' : 'false' }}</td>
                        <td>{{ $activitySignal->gateway_id ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">Chưa có activity signal cho phiên này.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <h2>Power events gần nhất của thiết bị</h2>
        <table>
            <thead>
                <tr>
                    <th>Thời điểm</th>
                    <th>Sự kiện</th>
                    <th>Nguồn</th>
                    <th>Gateway</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($powerEvents as $powerEvent)
                    <tr>
                        <td>{{ $powerEvent->recorded_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $powerEvent->event_type->value }}</td>
                        <td>{{ $powerEvent->source }}</td>
                        <td>{{ $powerEvent->gateway_id ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">Chưa có power event mô phỏng cho thiết bị này.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if (in_array($usageSession->status, [\App\Enums\BookingStatus::CheckedIn, \App\Enums\BookingStatus::Overdue], true))
        <x-modal id="complete-session-modal" title="Hoàn tất phiên" description="Xác nhận thiết bị đã được trả hoặc phiên sử dụng đã kết thúc.">
            <form method="POST" action="{{ route('usage-sessions.complete', $usageSession) }}" data-ajax-form>
                @csrf
                @include('usage-sessions.partials.complete-form', ['usageSession' => $usageSession])
                <div class="actions" style="margin-top: 16px;">
                    <button class="primary" type="submit">Hoàn tất phiên</button>
                    <button type="button" data-modal-close>Đóng</button>
                </div>
            </form>
        </x-modal>
    @endif

    <script>
        const simulateButton = document.querySelector('[data-simulate-telemetry]');
        const resultText = document.querySelector('[data-telemetry-result]');
        const samplesInput = document.querySelector('#samples');

        simulateButton?.addEventListener('click', async () => {
            simulateButton.disabled = true;
            resultText.textContent = 'Đang sinh telemetry mô phỏng...';

            try {
                const response = await fetch('{{ route('usage-sessions.simulate-telemetry', $usageSession) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        samples: Number(samplesInput.value || 12),
                    }),
                });

                const payload = await response.json();

                if (! response.ok) {
                    throw new Error(payload.message || 'Không thể sinh telemetry mô phỏng.');
                }

                resultText.textContent = `${payload.message} Power events: ${payload.summary.power_events_created}; activity signals: ${payload.summary.activity_signals_created}.`;
                window.setTimeout(() => window.location.reload(), 700);
            } catch (error) {
                resultText.textContent = error.message;
            } finally {
                simulateButton.disabled = false;
            }
        });
    </script>
@endsection
