<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\UserContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoUserCapability
{
    public function __construct(
        private readonly UserContextService $userContext,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$capabilities): Response
    {
        $user = $this->userContext->currentOrNull();

        if (! $user instanceof User) {
            abort(Response::HTTP_FORBIDDEN, 'Chưa có ngữ cảnh demo user để kiểm tra quyền truy cập.');
        }

        foreach ($capabilities as $capability) {
            if ($this->allows($user, $capability)) {
                return $next($request);
            }
        }

        abort(Response::HTTP_FORBIDDEN, 'Vai trò demo hiện tại không thể truy cập workspace này.');
    }

    private function allows(User $user, string $capability): bool
    {
        return match ($capability) {
            'booking' => $user->canUseBookingWorkspace(),
            'lab' => $user->canOperateLab(),
            'management' => $user->canViewManagementDashboard(),
            'system' => $user->canManageSystem(),
            default => false,
        };
    }
}
