<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DemoUserContextController extends Controller
{
    public function index(): View
    {
        return view('demo-login.index', [
            'usersByRole' => User::query()
                ->where('is_active', true)
                ->orderBy('role')
                ->orderBy('name')
                ->get()
                ->groupBy(fn (User $user) => $user->role->value),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::query()
            ->whereKey($validated['user_id'])
            ->where('is_active', true)
            ->first();

        if (! $user instanceof User) {
            return back()->with('status', 'Tài khoản trải nghiệm này không còn active.');
        }

        $request->session()->put('demo_user_id', $user->id);

        return redirect()
            ->route($this->dashboardRoute($user->role))
            ->with('status', 'Đã vào app bằng tài khoản trải nghiệm '.$user->roleLabel().': '.$user->name.'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('demo_user_id');

        return redirect()
            ->route('login')
            ->with('status', 'Đã thoát tài khoản trải nghiệm.');
    }

    public function switchRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $role = UserRole::from($validated['role']);

        $user = User::query()
            ->where('role', $role)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if (! $user instanceof User) {
            return back()->with('status', 'Chưa có tài khoản trải nghiệm active cho vai trò đã chọn.');
        }

        $request->session()->put('demo_user_id', $user->id);

        return redirect()
            ->route($this->dashboardRoute($role))
            ->with('status', 'Đã chuyển sang ngữ cảnh '.$user->roleLabel().': '.$user->name.'.');
    }

    private function dashboardRoute(UserRole $role): string
    {
        return match ($role) {
            UserRole::Admin => 'dashboard.admin',
            UserRole::Manager => 'dashboard.manager',
            UserRole::LabStaff => 'dashboard.lab-staff',
            UserRole::Researcher => 'dashboard.researcher',
        };
    }
}
