<?php

namespace App\Http\Middleware;

use App\Services\UserContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoUserSelected
{
    public function __construct(
        private readonly UserContextService $userContext,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->userContext->currentOrNull() !== null) {
            return $next($request);
        }

        return redirect()
            ->route('login')
            ->with('status', 'Đăng nhập hoặc chọn tài khoản mẫu để bắt đầu.');
    }
}
