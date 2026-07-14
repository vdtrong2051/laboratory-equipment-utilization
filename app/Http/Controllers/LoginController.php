<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function index(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        return back()
            ->withInput($request->only('email'))
            ->with('status', 'Đăng nhập thật chưa được bật trong phiên bản demo. Hãy dùng tài khoản mẫu để trải nghiệm theo role.');
    }
}
