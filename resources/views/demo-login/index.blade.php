<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Chọn tài khoản trải nghiệm</title>
        <style>
            :root {
                --bg: #f4f7fb;
                --panel: #ffffff;
                --line: #d9e2ec;
                --text: #17212f;
                --muted: #66768a;
                --primary: #146c94;
                --primary-dark: #0f4f6b;
                --demo-bg: #fff8e7;
                --demo-line: #f2d48b;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                background: var(--bg);
                color: var(--text);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                font-size: 14px;
                line-height: 1.5;
            }
            a { color: inherit; text-decoration: none; }
            .login-shell { max-width: 1080px; margin: 0 auto; padding: 36px 24px; }
            .eyebrow { margin: 0 0 6px; color: var(--muted); font-size: 13px; font-weight: 700; }
            .page-head { margin-bottom: 16px; }
            h1 { margin: 0; font-size: 32px; line-height: 1.1; }
            h2 { margin: 0; font-size: 21px; line-height: 1.2; }
            .muted { color: var(--muted); }
            .page-description { margin: 8px 0 0; max-width: 620px; font-size: 15px; }
            .demo-notice {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                margin-bottom: 18px;
                border: 1px solid var(--demo-line);
                background: var(--demo-bg);
                border-radius: 8px;
                padding: 11px 12px;
            }
            .demo-notice strong { display: block; }
            .demo-mark {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                min-width: 32px;
                height: 32px;
                border-radius: 8px;
                background: #f6d36b;
                color: #5f4200;
                font-size: 12px;
                font-weight: 800;
            }
            .status {
                border: 1px solid #9ad7cd;
                background: #effcf9;
                color: #107569;
                padding: 10px 12px;
                border-radius: 8px;
                margin-bottom: 16px;
            }
            .role-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
            .panel {
                background: var(--panel);
                border: 1px solid var(--line);
                border-radius: 10px;
                padding: 16px;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            }
            .role-heading {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 8px;
            }
            .role-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 34px;
                min-width: 34px;
                height: 34px;
                border-radius: 8px;
                background: #eef6f9;
                color: var(--primary-dark);
                font-size: 16px;
                font-weight: 800;
            }
            .role-code {
                display: inline-flex;
                margin-top: 4px;
                border-radius: 999px;
                padding: 2px 7px;
                background: #eef2f6;
                color: #475569;
                font-size: 11px;
                font-weight: 750;
                letter-spacing: .04em;
            }
            .role-description { margin: 0 0 13px; font-size: 14px; }
            .user-list { display: grid; gap: 4px; }
            .user-row {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr) auto;
                align-items: center;
                gap: 10px;
                padding: 8px 2px;
                border-top: 1px solid #edf2f7;
            }
            .user-row:first-child { border-top: 0; }
            .avatar {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 34px;
                min-width: 34px;
                height: 34px;
                border-radius: 50%;
                background: #eaf4f8;
                color: var(--primary-dark);
                font-size: 12px;
                font-weight: 800;
            }
            .user-name {
                display: block;
                overflow: hidden;
                color: var(--text);
                font-size: 15px;
                font-weight: 650;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .user-email {
                display: block;
                overflow: hidden;
                color: var(--muted);
                font-size: 13px;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .button, button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 34px;
                padding: 7px 11px;
                border: 1px solid var(--primary);
                border-radius: 8px;
                background: var(--primary);
                color: white;
                font: inherit;
                cursor: pointer;
                white-space: nowrap;
            }
            .button.secondary {
                background: white;
                color: var(--primary-dark);
                border-color: var(--line);
            }
            button:hover { background: var(--primary-dark); }
            .footer-link { margin-top: 18px; }
            @media (max-width: 840px) {
                .login-shell { padding: 28px 16px; }
                .role-grid { grid-template-columns: 1fr; }
                .user-row { grid-template-columns: auto minmax(0, 1fr); }
                .user-row button { grid-column: 2; justify-self: start; }
            }
        </style>
    </head>
    <body>
        @php
            $roleMeta = [
                \App\Enums\UserRole::Admin->value => [
                    'label' => 'Quản trị viên',
                    'code' => 'ADMIN',
                    'icon' => 'QT',
                    'description' => 'Quản lý dữ liệu nền, thiết bị và cấu hình hệ thống.',
                ],
                \App\Enums\UserRole::Manager->value => [
                    'label' => 'Quản lý phòng thí nghiệm',
                    'code' => 'MANAGER',
                    'icon' => 'QL',
                    'description' => 'Theo dõi khai thác, bất thường và batch analysis.',
                ],
                \App\Enums\UserRole::LabStaff->value => [
                    'label' => 'Kỹ thuật viên',
                    'code' => 'LAB_STAFF',
                    'icon' => 'KT',
                    'description' => 'Xử lý check-in, usage session và vận hành lab.',
                ],
                \App\Enums\UserRole::Researcher->value => [
                    'label' => 'Người nghiên cứu / Sinh viên',
                    'code' => 'RESEARCHER',
                    'icon' => 'NC',
                    'description' => 'Đặt thiết bị và theo dõi lịch sử sử dụng cá nhân.',
                ],
            ];

            $initials = function (string $name): string {
                $words = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);

                return collect($words)
                    ->take(2)
                    ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
                    ->implode('');
            };
        @endphp

        <main class="login-shell">
            <div class="page-head">
                <p class="eyebrow">Laboratory Equipment Utilization System</p>
                <h1>Chọn tài khoản trải nghiệm</h1>
                <p class="muted page-description">Mỗi tài khoản có quyền truy cập và không gian làm việc khác nhau.</p>
            </div>

            <div class="demo-notice">
                <span class="demo-mark">DEMO</span>
                <div>
                    <strong>Chế độ trình diễn</strong>
                    <span class="muted">Đây là các tài khoản mẫu, không yêu cầu mật khẩu.</span>
                </div>
            </div>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            <section class="role-grid">
                @foreach (\App\Enums\UserRole::cases() as $role)
                    @php($meta = $roleMeta[$role->value])
                    <div class="panel">
                        <div class="role-heading">
                            <span class="role-icon">{{ $meta['icon'] }}</span>
                            <div>
                                <h2>{{ $meta['label'] }}</h2>
                                <span class="role-code">{{ $meta['code'] }}</span>
                            </div>
                        </div>
                        <p class="muted role-description">{{ $meta['description'] }}</p>
                        <div class="user-list">
                            @forelse (($usersByRole[$role->value] ?? collect()) as $user)
                                <form class="user-row" method="POST" action="{{ route('demo-login.login') }}">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <span class="avatar">{{ $initials($user->name) }}</span>
                                    <div>
                                        <span class="user-name">{{ $user->name }}</span>
                                        <span class="user-email">{{ $user->email }}</span>
                                    </div>
                                    <button type="submit">Đăng nhập</button>
                                </form>
                            @empty
                                <div class="muted">Chưa có tài khoản trải nghiệm active cho vai trò này.</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </section>

            <div class="footer-link">
                <a class="button secondary" href="{{ route('login') }}">← Quay lại đăng nhập</a>
            </div>
        </main>
    </body>
</html>
