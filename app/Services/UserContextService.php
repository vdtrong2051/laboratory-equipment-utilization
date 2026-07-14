<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use RuntimeException;

class UserContextService
{
    public function current(): User
    {
        $authenticatedUser = Auth::user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        $sessionUserId = $this->sessionUserId();

        if ($sessionUserId !== null) {
            $sessionUser = User::query()
                ->whereKey($sessionUserId)
                ->where('is_active', true)
                ->first();

            if ($sessionUser instanceof User) {
                return $sessionUser;
            }
        }

        if (app()->runningUnitTests()) {
            $demoUser = User::query()
                ->where('email', config('demo.user_email'))
                ->first();

            if ($demoUser instanceof User) {
                return $demoUser;
            }
        }

        throw new RuntimeException('Demo user is not available. Run database seeders or update DEMO_USER_EMAIL.');
    }

    public function currentOrNull(): ?User
    {
        try {
            return $this->current();
        } catch (RuntimeException) {
            return null;
        }
    }

    private function sessionUserId(): ?int
    {
        if (! Request::hasSession()) {
            return null;
        }

        $userId = Request::session()->get('demo_user_id');

        return is_numeric($userId) ? (int) $userId : null;
    }
}
