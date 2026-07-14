<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function useHomeRedirectRole(UserRole $role): User
{
    $user = User::factory()->create([
        'email' => $role->value.'.home@lab.local',
        'role' => $role,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $user->email]);

    return $user;
}

test('home redirects admin to admin dashboard', function () {
    useHomeRedirectRole(UserRole::Admin);

    $this->get(route('home'))->assertRedirect(route('dashboard.admin'));
});

test('home redirects manager to manager dashboard', function () {
    useHomeRedirectRole(UserRole::Manager);

    $this->get(route('home'))->assertRedirect(route('dashboard.manager'));
});

test('home redirects lab staff to lab staff dashboard', function () {
    useHomeRedirectRole(UserRole::LabStaff);

    $this->get(route('home'))->assertRedirect(route('dashboard.lab-staff'));
});

test('home redirects researcher to researcher dashboard', function () {
    useHomeRedirectRole(UserRole::Researcher);

    $this->get(route('home'))->assertRedirect(route('dashboard.researcher'));
});

test('home redirects to login when demo user is missing', function () {
    config(['demo.user_email' => 'missing@lab.local']);

    $this->get(route('home'))->assertRedirect(route('login'));
});
