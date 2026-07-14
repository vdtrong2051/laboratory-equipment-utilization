<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Equipment;
use App\Models\PowerEvent;
use App\Models\UsageSession;
use App\Services\Flow\UsageSessionFlowPresenter;
use App\Services\TelemetrySimulatorService;
use App\Services\UsageSessionService;
use App\Services\UserContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsageSessionController extends Controller
{
    public function __construct(
        private readonly UsageSessionService $usageSessionService,
        private readonly TelemetrySimulatorService $telemetrySimulatorService,
        private readonly UserContextService $userContext,
        private readonly UsageSessionFlowPresenter $usageSessionFlowPresenter,
    ) {}

    public function index(): View
    {
        return view('usage-sessions.index', [
            'usageSessions' => $this->usageSessionService->paginatedList(request()->only(['status', 'equipment_id'])),
            'equipments' => Equipment::query()->orderBy('equipment_code')->get(),
            'statuses' => BookingStatus::cases(),
        ]);
    }

    public function show(UsageSession $usageSession): View
    {
        $usageSession->load(['booking', 'equipment', 'user', 'checkedInBy', 'completedBy']);

        return view('usage-sessions.show', [
            'usageSession' => $usageSession,
            'activitySignals' => $usageSession->activitySignals()
                ->latest('recorded_at')
                ->limit(10)
                ->get(),
            'powerEvents' => PowerEvent::query()
                ->where('equipment_id', $usageSession->equipment_id)
                ->where('source', 'simulated')
                ->latest('recorded_at')
                ->limit(10)
                ->get(),
            'flow' => $this->usageSessionFlowPresenter->present($usageSession),
        ]);
    }

    public function complete(Request $request, UsageSession $usageSession): RedirectResponse|JsonResponse
    {
        $this->usageSessionService->complete($usageSession, $this->userContext->current());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã hoàn tất phiên sử dụng.',
                'data' => ['usage_session_id' => $usageSession->id],
                'refresh' => ['target' => '#active-sessions'],
            ]);
        }

        return redirect()
            ->route('usage-sessions.show', $usageSession)
            ->with('status', 'Đã hoàn tất phiên sử dụng.');
    }

    public function simulateTelemetry(Request $request, UsageSession $usageSession): JsonResponse
    {
        $validated = $request->validate([
            'samples' => ['nullable', 'integer', 'min:3', 'max:60'],
        ]);

        $summary = $this->telemetrySimulatorService->simulateForUsageSession(
            $usageSession,
            (int) ($validated['samples'] ?? 12),
        );

        return response()->json([
            'message' => 'Đã sinh telemetry mô phỏng cho phiên sử dụng.',
            'summary' => $summary,
        ]);
    }
}
