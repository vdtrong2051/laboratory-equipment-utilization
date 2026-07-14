<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Đăng nhập</title>
        <style>
            :root {
                --bg: #f4f7fb;
                --panel: #ffffff;
                --line: #d9e2ec;
                --text: #17212f;
                --muted: #66768a;
                --primary: #146c94;
                --primary-dark: #0f4f6b;
                --danger: #b42318;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 24px;
                background: var(--bg);
                color: var(--text);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                font-size: 14px;
                line-height: 1.5;
            }
            a { color: inherit; text-decoration: none; }
            .login-card {
                width: min(100%, 430px);
                background: var(--panel);
                border: 1px solid var(--line);
                border-radius: 10px;
                padding: 24px;
                box-shadow: 0 18px 55px rgba(15, 23, 42, .09);
            }
            h1 { margin: 0 0 22px; font-size: 28px; line-height: 1.15; }
            .field { display: grid; gap: 6px; margin-bottom: 12px; }
            label { font-weight: 650; }
            input {
                width: 100%;
                min-height: 40px;
                border: 1px solid var(--line);
                border-radius: 8px;
                padding: 9px 10px;
                color: var(--text);
                font: inherit;
            }
            .button, button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                min-height: 40px;
                padding: 8px 12px;
                border: 1px solid var(--primary);
                border-radius: 8px;
                background: var(--primary);
                color: white;
                font: inherit;
                cursor: pointer;
            }
            button:hover { background: var(--primary-dark); }
            .button.secondary {
                background: white;
                color: var(--primary-dark);
                border-color: var(--line);
            }
            .divider {
                display: grid;
                grid-template-columns: 1fr auto 1fr;
                gap: 10px;
                align-items: center;
                color: var(--muted);
                margin: 18px 0;
            }
            .divider::before,
            .divider::after {
                content: "";
                height: 1px;
                background: var(--line);
            }
            .status {
                border: 1px solid #9ad7cd;
                background: #effcf9;
                color: #107569;
                padding: 10px 12px;
                border-radius: 8px;
                margin-bottom: 16px;
            }
            .error { color: var(--danger); font-size: 13px; }
        </style>
    </head>
    <body>
        <main class="login-card">
            <h1>Đăng nhập</h1>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Mật khẩu</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit">Đăng nhập</button>
            </form>

            <div class="divider">hoặc</div>

            <a class="button secondary" href="{{ route('demo-login.index') }}">Trải nghiệm bằng tài khoản mẫu</a>
        </main>
    </body>
</html>
