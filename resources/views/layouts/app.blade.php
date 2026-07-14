@php
    $layoutUser = app(\App\Services\UserContextService::class)->currentOrNull();
    $layoutWorkspace = $layoutUser
        ? config('role_navigation.workspaces.'.$layoutUser->role->value, [])
        : [];
    $layoutNavigation = $layoutWorkspace['navigation'] ?? [];
    $dashboardRoute = match (true) {
        $layoutUser?->isAdmin() => 'dashboard.admin',
        $layoutUser?->isManager() => 'dashboard.manager',
        $layoutUser?->isLabStaff() => 'dashboard.lab-staff',
        $layoutUser?->isResearcher() => 'dashboard.researcher',
        default => 'dashboard.manager',
    };
@endphp

<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ trim($__env->yieldContent('title', config('app.name'))) }}</title>
        <style>
            :root {
                --space-1: 0.25rem;
                --space-2: 0.5rem;
                --space-3: 0.75rem;
                --space-4: 1rem;
                --space-6: 1.5rem;
                --radius-sm: 0.375rem;
                --radius-md: 0.625rem;
                --border-subtle: #e5e7eb;
                --text-muted: #6b7280;
                --bg: #f4f7fb;
                --sidebar: #102333;
                --sidebar-soft: #18364c;
                --panel: #ffffff;
                --panel-soft: #f8fafc;
                --line: var(--border-subtle);
                --text: #17212f;
                --muted: var(--text-muted);
                --primary: #146c94;
                --primary-dark: #0f4f6b;
                --danger: #b42318;
                --ok: #107569;
            }

            * { box-sizing: border-box; }
            body {
                margin: 0;
                background: var(--bg);
                color: var(--text);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                font-size: 14px;
                line-height: 1.5;
            }
            body.sidebar-open { overflow: hidden; }
            a { color: inherit; text-decoration: none; }
            .app-shell {
                min-height: 100vh;
                display: grid;
                grid-template-columns: 240px minmax(0, 1fr);
                transition: grid-template-columns .18s ease;
            }
            .app-shell.sidebar-collapsed { grid-template-columns: 72px minmax(0, 1fr); }
            .sidebar {
                position: sticky;
                top: 0;
                height: 100vh;
                overflow-y: auto;
                background: var(--sidebar);
                color: #e6eef5;
                padding: 14px 12px;
                border-right: 1px solid rgba(255, 255, 255, .08);
            }
            .sidebar-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
            .brand-wrap { min-width: 0; }
            .brand { display: block; font-weight: 750; font-size: 15px; line-height: 1.22; }
            .brand-subtitle { color: #9eb4c5; font-size: 11px; margin-top: 2px; }
            .icon-button {
                width: 34px;
                min-width: 34px;
                height: 34px;
                padding: 0;
                border-radius: 8px;
                border: 1px solid rgba(255, 255, 255, .1);
                background: rgba(255, 255, 255, .05);
                color: #dce8f0;
            }
            .role-card {
                display: none;
            }
            .role-card strong { display: block; color: white; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .role-card span { color: #9eb4c5; font-size: 12px; }
            .nav-section { margin-top: 14px; }
            .nav-title {
                color: #8ea6b8;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .08em;
                margin: 0 0 6px;
                padding: 0 9px;
            }
            .nav-link {
                display: flex;
                align-items: center;
                gap: 9px;
                min-height: 36px;
                padding: 7px 9px;
                border-radius: 8px;
                color: #dce8f0;
                margin-bottom: 3px;
                white-space: nowrap;
            }
            .nav-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 22px;
                min-width: 22px;
                height: 22px;
                border-radius: 6px;
                background: rgba(255, 255, 255, .07);
                font-size: 12px;
                font-weight: 700;
            }
            .nav-text { overflow: hidden; text-overflow: ellipsis; }
            .nav-link:hover, .nav-link.active { background: var(--sidebar-soft); color: white; }
            .app-shell.sidebar-collapsed .sidebar { padding-left: 10px; padding-right: 10px; }
            .app-shell.sidebar-collapsed .brand-wrap,
            .app-shell.sidebar-collapsed .role-card,
            .app-shell.sidebar-collapsed .nav-title,
            .app-shell.sidebar-collapsed .nav-text { display: none; }
            .app-shell.sidebar-collapsed .sidebar-top { justify-content: center; }
            .app-shell.sidebar-collapsed .nav-link { justify-content: center; padding-left: 0; padding-right: 0; }
            .main { min-width: 0; }
            .app-header {
                position: sticky;
                top: 0;
                z-index: 10;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                min-height: 58px;
                padding: 10px 24px;
                background: rgba(244, 247, 251, .94);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid var(--line);
            }
            .header-left { display: flex; align-items: center; gap: 10px; min-width: 0; }
            .mobile-menu { display: none; }
            .header-title { font-weight: 700; font-size: 15px; }
            .header-meta { display: none; }
            .content { max-width: 1240px; margin: 0 auto; padding: 22px 24px 38px; }
            .top-actions { display: flex; gap: 7px; flex-wrap: wrap; justify-content: flex-end; }
            .user-menu { display: inline-flex; align-items: center; gap: 7px; flex-wrap: wrap; justify-content: flex-end; }
            .user-menu > :not(.user-dropdown) { display: none; }
            .user-pill {
                display: inline-flex;
                flex-direction: column;
                justify-content: center;
                min-height: 34px;
                padding: 5px 9px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--panel);
                line-height: 1.2;
            }
            .user-pill strong { font-size: 12px; }
            .user-pill span { color: var(--muted); font-size: 11px; }
            .user-dropdown { position: relative; }
            .user-dropdown summary { list-style: none; cursor: pointer; }
            .user-dropdown summary::-webkit-details-marker { display: none; }
            .user-dropdown-panel {
                position: absolute;
                right: 0;
                z-index: 20;
                min-width: 220px;
                display: grid;
                gap: 6px;
                margin-top: 6px;
                padding: 8px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--panel);
                box-shadow: 0 14px 34px rgba(15, 23, 42, .14);
            }
            .user-dropdown-panel .button,
            .user-dropdown-panel button {
                width: 100%;
                justify-content: flex-start;
            }
            .user-dropdown-meta {
                padding: 6px 8px 8px;
                border-bottom: 1px solid var(--line);
                color: var(--muted);
                font-size: 12px;
            }
            .button, button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                min-height: 34px;
                padding: 7px 10px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--panel);
                color: var(--text);
                font: inherit;
                font-size: 13px;
                cursor: pointer;
            }
            .button.primary, button.primary { background: var(--primary); border-color: var(--primary); color: white; }
            .button.primary:hover, button.primary:hover { background: var(--primary-dark); }
            .button.danger, button.danger { color: var(--danger); border-color: #f1b5ae; background: #fff7f6; }
            .button.secondary, button.secondary { background: #eef6f9; border-color: #b8d8e3; color: var(--primary-dark); }
            .page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
            h1 { margin: 0; font-size: 26px; line-height: 1.15; }
            h2 { margin: 0 0 12px; font-size: 18px; }
            .muted { color: var(--muted); }
            .panel {
                background: var(--panel);
                border: 1px solid var(--line);
                border-radius: 10px;
                padding: 16px;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            }
            .filters, .form-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
            .filter-toolbar {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
                gap: var(--space-3);
                align-items: end;
            }
            .form-grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .field { display: flex; flex-direction: column; gap: 6px; }
            label { font-weight: 600; color: #334155; }
            input, select, textarea {
                width: 100%;
                min-height: 38px;
                border: 1px solid var(--line);
                border-radius: 8px;
                padding: 8px 10px;
                background: white;
                color: var(--text);
                font: inherit;
            }
            textarea { resize: vertical; }
            .error { color: var(--danger); font-size: 13px; }
            .status {
                position: fixed;
                right: 18px;
                bottom: 18px;
                z-index: 60;
                max-width: min(420px, calc(100vw - 36px));
                border: 1px solid #9ad7cd;
                background: #effcf9;
                color: var(--ok);
                padding: 10px 12px;
                border-radius: 8px;
                box-shadow: 0 14px 34px rgba(15, 23, 42, .14);
            }
            .table-card { margin-top: var(--space-4); padding: 0; overflow: hidden; }
            .table-toolbar {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: var(--space-4);
                padding: var(--space-4);
                border-bottom: 1px solid var(--line);
            }
            .table-toolbar h2 { margin-bottom: 2px; }
            .record-count { color: var(--muted); font-size: 12px; }
            .table-wrap { overflow-x: auto; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 11px 10px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
            th { color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: .02em; background: var(--panel-soft); }
            tr[data-href] { cursor: pointer; }
            tr[data-href]:hover td { background: #f8fbfd; }
            .badge { display: inline-flex; border-radius: 999px; padding: 3px 8px; background: #eaf4f8; color: #0f4f6b; font-size: 12px; font-weight: 600; }
            .actions { display: flex; gap: 8px; flex-wrap: wrap; }
            .row-actions { display: flex; justify-content: flex-end; gap: var(--space-2); }
            .action-menu { position: relative; }
            .action-menu summary { list-style: none; }
            .action-menu summary::-webkit-details-marker { display: none; }
            .action-menu-panel {
                position: absolute;
                right: 0;
                z-index: 8;
                min-width: 172px;
                display: grid;
                gap: var(--space-2);
                padding: var(--space-2);
                margin-top: var(--space-1);
                border: 1px solid var(--line);
                border-radius: var(--radius-sm);
                background: var(--panel);
                box-shadow: 0 10px 30px rgba(15, 23, 42, .14);
            }
            .empty-state {
                padding: var(--space-6);
                color: var(--muted);
                text-align: center;
                background: var(--panel-soft);
            }
            .detail-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
            .admin-kpi-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .metric { background: var(--panel-soft); border: 1px solid var(--line); border-radius: 8px; padding: 12px; }
            .metric strong { display: block; font-size: 20px; }
            .pagination { margin-top: 16px; }
            .pagination nav { display: flex; align-items: center; justify-content: center; gap: 8px; }
            .pagination-list { display: inline-flex; align-items: center; gap: 4px; padding: 0; margin: 0; list-style: none; }
            .pagination a,
            .pagination span {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 34px;
                min-height: 34px;
                padding: 6px 10px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: white;
                font-size: 13px;
            }
            .pagination .active span { background: var(--primary); border-color: var(--primary); color: white; }
            .pagination .disabled span { color: var(--muted); background: var(--panel-soft); }
            .workspace-context {
                display: grid;
                grid-template-columns: minmax(0, 1.6fr) minmax(280px, .9fr);
                gap: 16px;
                margin-bottom: 16px;
            }
            .insight-box {
                border-left: 4px solid var(--primary);
                background: #f7fbfd;
            }
            .insight-box h2 { margin-bottom: 8px; }
            .insight-headline {
                margin: 0 0 8px;
                color: var(--primary-dark);
                font-size: 16px;
                font-weight: 750;
            }
            .context-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }
            .user-list { display: grid; gap: 10px; }
            .context-item {
                border: 1px solid var(--line);
                border-radius: 8px;
                padding: 10px;
                background: white;
            }
            .context-item span { display: block; color: var(--muted); font-size: 12px; }
            .context-item strong { display: block; margin-top: 2px; font-size: 18px; }
            .next-actions-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 16px;
            }
            .action-card {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: space-between;
                gap: 10px;
                min-height: 142px;
            }
            .action-card h3 { margin: 0; font-size: 15px; }
            .action-card p { margin: 0; color: var(--muted); }
            .flow-panel { margin-bottom: 16px; }
            .flow-stepper {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 10px;
                padding: 0;
                margin: 0;
                list-style: none;
            }
            .flow-step {
                position: relative;
                display: flex;
                gap: 9px;
                min-height: 112px;
                padding: 12px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--panel-soft);
            }
            .flow-step.completed {
                border-color: #9ad7cd;
                background: #effcf9;
            }
            .flow-step.current {
                border-color: #b8d8e3;
                background: #eef6f9;
            }
            .flow-step.blocked {
                border-color: #f1b5ae;
                background: #fff7f6;
            }
            .flow-dot {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 24px;
                min-width: 24px;
                height: 24px;
                border-radius: 50%;
                background: white;
                border: 1px solid var(--line);
                font-weight: 750;
                font-size: 12px;
            }
            .flow-step.completed .flow-dot { background: var(--ok); border-color: var(--ok); color: white; }
            .flow-step.current .flow-dot { background: var(--primary); border-color: var(--primary); color: white; }
            .flow-step.blocked .flow-dot { background: var(--danger); border-color: var(--danger); color: white; }
            .flow-step strong { display: block; font-size: 13px; }
            .flow-step span:last-child { display: block; margin-top: 4px; color: var(--muted); font-size: 12px; }
            .modal-backdrop {
                position: fixed;
                inset: 0;
                z-index: 50;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background: rgba(15, 23, 42, .46);
            }
            .modal-backdrop.is-open { display: flex; }
            .modal-panel {
                width: min(760px, 100%);
                max-height: min(88vh, 820px);
                overflow: auto;
                background: var(--panel);
                border: 1px solid var(--line);
                border-radius: 10px;
                box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
            }
            .modal-head {
                position: sticky;
                top: 0;
                z-index: 1;
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                padding: 16px;
                background: var(--panel);
                border-bottom: 1px solid var(--line);
            }
            .modal-head h2 { margin: 0; }
            .modal-body { padding: 16px; }
            .modal-message {
                display: none;
                margin-bottom: 12px;
                border: 1px solid #9ad7cd;
                background: #effcf9;
                color: var(--ok);
                padding: 10px 12px;
                border-radius: 8px;
            }
            .modal-message.is-error {
                border-color: #f1b5ae;
                background: #fff7f6;
                color: var(--danger);
            }
            .sidebar-backdrop { display: none; }
            @media (max-width: 1040px) {
                .app-shell, .app-shell.sidebar-collapsed { grid-template-columns: 1fr; }
                .sidebar {
                    position: fixed;
                    z-index: 30;
                    left: 0;
                    top: 0;
                    width: 240px;
                    transform: translateX(-100%);
                    transition: transform .18s ease;
                }
                body.sidebar-open .sidebar { transform: translateX(0); }
                .app-shell.sidebar-collapsed .brand-wrap,
                .app-shell.sidebar-collapsed .role-card,
                .app-shell.sidebar-collapsed .nav-title,
                .app-shell.sidebar-collapsed .nav-text { display: block; }
                .app-shell.sidebar-collapsed .nav-link { justify-content: flex-start; padding: 7px 9px; }
                .sidebar-backdrop {
                    display: block;
                    position: fixed;
                    inset: 0;
                    z-index: 20;
                    background: rgba(15, 23, 42, .4);
                    opacity: 0;
                    pointer-events: none;
                    transition: opacity .18s ease;
                }
                body.sidebar-open .sidebar-backdrop { opacity: 1; pointer-events: auto; }
                .mobile-menu { display: inline-flex; }
                .sidebar-toggle { display: none; }
                .app-header { padding: 10px 16px; }
                .content { padding: 18px 16px 34px; }
            }
            @media (max-width: 860px) {
                .filters, .filter-toolbar, .form-grid, .form-grid.two, .detail-grid, .workspace-context, .next-actions-grid { grid-template-columns: 1fr; }
                .flow-stepper { grid-template-columns: 1fr; }
                .page-head, .app-header { flex-direction: column; align-items: stretch; }
                .header-left, .user-menu { width: 100%; }
                .top-actions { justify-content: flex-start; }
                table { min-width: 760px; }
            }
        </style>
    </head>
    <body>
        <div class="app-shell" data-app-shell>
            <aside class="sidebar" id="app-sidebar">
                <div class="sidebar-top">
                    <div class="brand-wrap">
                        <a class="brand" href="{{ route($dashboardRoute) }}">Lab Utilization</a>
                        <div class="brand-subtitle">{{ $layoutWorkspace['title'] ?? 'Demo workspace' }}</div>
                    </div>
                    <button class="icon-button sidebar-toggle" type="button" data-sidebar-toggle aria-label="Thu gọn sidebar" title="Thu gọn sidebar">≡</button>
                </div>

                <div class="role-card">
                    <strong>{{ $layoutUser?->name ?? 'Demo context' }}</strong>
                    <span>{{ $layoutUser?->roleLabel() ?? 'Chưa có demo user' }}</span>
                </div>

                @foreach ($layoutNavigation as $section)
                    <div class="nav-section">
                        <p class="nav-title">{{ $section['title'] }}</p>
                        @foreach ($section['items'] as $item)
                            @if (\Illuminate\Support\Facades\Route::has($item['route']))
                                @php
                                    $routePattern = str_starts_with($item['route'], 'dashboard.')
                                        ? $item['route']
                                        : (str_contains($item['route'], '.')
                                        ? str($item['route'])->beforeLast('.')->append('.*')->toString()
                                        : $item['route']);
                                @endphp
                                <a class="nav-link @if(request()->routeIs($item['route']) || request()->routeIs($routePattern)) active @endif" href="{{ route($item['route']) }}" title="{{ $item['label'] }}">
                                    <span class="nav-icon">{{ $item['icon'] }}</span><span class="nav-text">{{ $item['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </aside>
            <button class="sidebar-backdrop" type="button" data-sidebar-close aria-label="Đóng sidebar"></button>

            <main class="main">
                <header class="app-header">
                    <div class="header-left">
                        <button class="icon-button mobile-menu" type="button" data-sidebar-open aria-label="Mở sidebar">≡</button>
                        <div>
                            <div class="header-title">@yield('title', config('app.name'))</div>
                            <div class="header-meta">
                                {{ $layoutUser?->roleLabel() ?? 'Demo mode' }} · Backend-owned rules · Simulated telemetry
                            </div>
                        </div>
                    </div>
                    <div class="top-actions user-menu">
                        <details class="user-dropdown">
                            <summary class="user-pill">
                                <strong>{{ $layoutUser?->name ?? 'Chưa chọn tài khoản' }}</strong>
                                <span>{{ $layoutUser?->roleLabel() ?? 'Tài khoản demo' }}</span>
                            </summary>
                            <div class="user-dropdown-panel">
                                <div class="user-dropdown-meta">Tài khoản trải nghiệm</div>
                                <a class="button" href="{{ route($dashboardRoute) }}">Trang vai trò</a>
                                <a class="button" href="{{ route('demo-login.index') }}">Đổi tài khoản demo</a>
                                <form method="POST" action="{{ route('demo-login.logout') }}">
                                    @csrf
                                    <button type="submit">Đăng xuất</button>
                                </form>
                            </div>
                        </details>
                        <div class="user-pill">
                            <strong>{{ $layoutUser?->name ?? 'Chưa chọn user' }}</strong>
                            <span>{{ $layoutUser?->roleLabel() ?? 'Demo auth' }}</span>
                        </div>
                        <a class="button" href="{{ route($dashboardRoute) }}">Trang vai trò</a>
                        <a class="button secondary" href="{{ route('demo-login.index') }}">Đổi tài khoản demo</a>
                        <form method="POST" action="{{ route('demo-login.logout') }}">
                            @csrf
                            <button type="submit">Thoát</button>
                        </form>
                        @if ($layoutUser?->canViewManagementDashboard())
                            <a class="button secondary" href="{{ route('analysis-runs.index') }}">Lịch sử batch</a>
                        @endif
                    </div>
                </header>

                <div class="content">
                    @if (session('status'))
                        <div class="status">{{ session('status') }}</div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        <script>
            const shell = document.querySelector('[data-app-shell]');
            const storageKey = 'leus.sidebarCollapsed';
            const applySidebarState = () => {
                if (window.matchMedia('(max-width: 1040px)').matches) {
                    shell?.classList.remove('sidebar-collapsed');
                    return;
                }

                shell?.classList.toggle('sidebar-collapsed', localStorage.getItem(storageKey) === '1');
            };

            applySidebarState();
            window.addEventListener('resize', applySidebarState);

            document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => {
                const collapsed = ! shell?.classList.contains('sidebar-collapsed');
                shell?.classList.toggle('sidebar-collapsed', collapsed);
                localStorage.setItem(storageKey, collapsed ? '1' : '0');
            });

            document.querySelector('[data-sidebar-open]')?.addEventListener('click', () => {
                document.body.classList.add('sidebar-open');
            });

            document.querySelector('[data-sidebar-close]')?.addEventListener('click', () => {
                document.body.classList.remove('sidebar-open');
            });

            document.querySelectorAll('tr[data-href]').forEach((row) => {
                row.addEventListener('click', (event) => {
                    if (event.target.closest('a, button, form, details, summary')) return;
                    window.location.href = row.dataset.href;
                });
            });

            const toastStatus = document.querySelector('.status');
            if (toastStatus) {
                window.setTimeout(() => {
                    toastStatus.style.display = 'none';
                }, 4200);
            }

            const openModal = (id) => {
                const modal = document.getElementById(id);
                if (! modal) return;
                modal.classList.add('is-open');
                modal.querySelector('input, select, textarea, button')?.focus();
            };

            const closeModal = (modal) => {
                modal?.classList.remove('is-open');
            };

            document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
                trigger.addEventListener('click', () => openModal(trigger.dataset.modalOpen));
            });

            document.querySelectorAll('[data-modal-close]').forEach((trigger) => {
                trigger.addEventListener('click', () => closeModal(trigger.closest('.modal-backdrop')));
            });

            document.querySelectorAll('.modal-backdrop').forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) closeModal(modal);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal(document.querySelector('.modal-backdrop.is-open'));
                }
            });

            const clearFormErrors = (form) => {
                form.querySelectorAll('[data-error-for]').forEach((node) => {
                    node.textContent = '';
                    node.style.display = 'none';
                });
            };

            const showFormErrors = (form, errors = {}) => {
                Object.entries(errors).forEach(([field, messages]) => {
                    const errorNode = form.querySelector(`[data-error-for="${field}"]`);
                    if (! errorNode) return;
                    errorNode.textContent = Array.isArray(messages) ? messages[0] : messages;
                    errorNode.style.display = 'inline';
                });
            };

            document.querySelectorAll('[data-ajax-form]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const submitter = event.submitter;
                    const messageNode = form.closest('.modal-backdrop')?.querySelector('[data-modal-message]');
                    clearFormErrors(form);
                    if (messageNode) {
                        messageNode.textContent = '';
                        messageNode.classList.remove('is-error');
                        messageNode.style.display = 'none';
                    }
                    submitter?.setAttribute('disabled', 'disabled');

                    try {
                        const response = await fetch(form.action, {
                            method: form.method || 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: new FormData(form),
                        });
                        const payload = await response.json();

                        if (! response.ok) {
                            showFormErrors(form, payload.errors || {});
                            throw new Error(payload.message || 'Dữ liệu không hợp lệ.');
                        }

                        if (messageNode) {
                            messageNode.textContent = payload.message || 'Thao tác thành công.';
                            messageNode.style.display = 'block';
                        }

                        window.setTimeout(() => window.location.reload(), 650);
                    } catch (error) {
                        if (messageNode) {
                            messageNode.textContent = error.message;
                            messageNode.classList.add('is-error');
                            messageNode.style.display = 'block';
                        }
                    } finally {
                        submitter?.removeAttribute('disabled');
                    }
                });
            });
        </script>
    </body>
</html>
