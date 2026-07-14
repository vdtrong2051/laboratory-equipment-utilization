@props(['workspace' => [], 'insight' => []])

<section class="workspace-context">
    <div class="panel insight-box">
        <p class="muted" style="margin: 0 0 4px;">Context Summary</p>
        <h2>Natural Language Insight</h2>
        <p class="insight-headline">{{ $insight['headline'] ?? 'Chưa có insight.' }}</p>
        <p class="muted">{{ $insight['narrative'] ?? ($workspace['subtitle'] ?? 'Dashboard đang dùng dữ liệu demo.') }}</p>
    </div>

    <div class="panel">
        <h2>Tín hiệu nhanh</h2>
        <div class="context-grid">
            @forelse (($insight['context'] ?? []) as $item)
                <div class="context-item">
                    <span>{{ $item['label'] }}</span>
                    <strong>{{ $item['value'] }}</strong>
                </div>
            @empty
                <div class="context-item">
                    <span>Workspace</span>
                    <strong>{{ $workspace['title'] ?? 'Demo' }}</strong>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section>
    <div class="page-head" style="margin-bottom: 12px;">
        <div>
            <h2>Next Actions</h2>
            <p class="muted">Các bước ưu tiên được rút ra từ dữ liệu hiện tại của role này.</p>
        </div>
    </div>

    <div class="next-actions-grid">
        @forelse (($insight['next_actions'] ?? []) as $action)
            <article class="panel action-card">
                <div>
                    <h3>{{ $action['label'] }}</h3>
                    <p>{{ $action['description'] }}</p>
                </div>

                @if (isset($action['route']) && \Illuminate\Support\Facades\Route::has($action['route']))
                    @php
                        $method = strtoupper($action['method'] ?? 'GET');
                        $classes = 'button '.(($action['variant'] ?? null) === 'primary' ? 'primary' : '');
                    @endphp

                    @if ($method === 'POST')
                        <form method="POST" action="{{ route($action['route']) }}">
                            @csrf
                            <button class="{{ trim($classes) }}" type="submit">Thực hiện</button>
                        </form>
                    @else
                        <a class="{{ trim($classes) }}" href="{{ route($action['route']) }}">Mở</a>
                    @endif
                @endif
            </article>
        @empty
            <article class="panel action-card">
                <div>
                    <h3>Đọc dashboard</h3>
                    <p>Chưa có next action động cho role này.</p>
                </div>
            </article>
        @endforelse
    </div>
</section>
