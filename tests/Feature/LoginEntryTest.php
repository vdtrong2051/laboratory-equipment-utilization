<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login page offers real login and demo account entry', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Email')
        ->assertSee('Mật khẩu')
        ->assertSee('Đăng nhập')
        ->assertSee('Trải nghiệm bằng tài khoản mẫu')
        ->assertSee(route('demo-login.index'), false);
});

test('real login submit is acknowledged as not enabled yet', function () {
    $this->post(route('login.store'), [
        'email' => 'real.user@lab.local',
        'password' => 'secret',
    ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Đăng nhập thật chưa được bật trong phiên bản demo. Hãy dùng tài khoản mẫu để trải nghiệm theo role.');
});
