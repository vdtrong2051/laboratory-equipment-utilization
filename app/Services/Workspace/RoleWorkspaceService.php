<?php

namespace App\Services\Workspace;

use App\DTOs\DashboardSummaryData;
use App\Models\User;
use App\Services\Insights\RoleInsightService;
use Illuminate\Support\Arr;

class RoleWorkspaceService
{
    /**
     * @return array<string, mixed>
     */
    public function workspaceFor(User $user): array
    {
        return config('role_navigation.workspaces.'.$user->role->value, []);
    }

    /**
     * @return array<string, mixed>
     */
    public function insightFor(User $user, DashboardSummaryData $summary): array
    {
        $serviceClass = Arr::get($this->workspaceFor($user), 'insight_service');

        if (! is_string($serviceClass)) {
            return [
                'headline' => 'Chưa cấu hình insight cho workspace này.',
                'narrative' => 'Dashboard vẫn hiển thị dữ liệu thô, nhưng chưa có phần diễn giải tự nhiên.',
                'context' => [],
                'next_actions' => [],
            ];
        }

        $service = app($serviceClass);

        if (! $service instanceof RoleInsightService) {
            throw new \RuntimeException($serviceClass.' must implement '.RoleInsightService::class);
        }

        return $service->insightFor($user, $summary);
    }
}
