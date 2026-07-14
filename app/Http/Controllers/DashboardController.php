<?php

namespace App\Http\Controllers;

use App\Enums\AnalysisStatus;
use App\Enums\OperationalStatus;
use App\Enums\UsageMode;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\User;
use App\Services\Dashboard\AdminDashboardService;
use App\Services\Dashboard\LabStaffDashboardService;
use App\Services\Dashboard\ManagerDashboardService;
use App\Services\Dashboard\ResearcherDashboardService;
use App\Services\EquipmentAnalysisBatchService;
use App\Services\UserContextService;
use App\Services\Workspace\RoleWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserContextService $userContext,
        private readonly AdminDashboardService $adminDashboardService,
        private readonly LabStaffDashboardService $labStaffDashboardService,
        private readonly ManagerDashboardService $managerDashboardService,
        private readonly ResearcherDashboardService $researcherDashboardService,
        private readonly EquipmentAnalysisBatchService $equipmentAnalysisBatchService,
        private readonly RoleWorkspaceService $roleWorkspaceService,
    ) {}

    public function home(): RedirectResponse
    {
        $currentUser = $this->userContext->currentOrNull();

        if ($currentUser === null) {
            return redirect()->route('equipments.index');
        }

        return redirect()->route($this->dashboardRoute($currentUser->role));
    }

    public function admin(): View
    {
        $currentUser = $this->userContext->current();
        $summary = $this->adminDashboardService->summaryFor($currentUser);

        return view('dashboard.admin', [
            'currentUser' => $currentUser,
            'summary' => $summary,
            'workspace' => $this->roleWorkspaceService->workspaceFor($currentUser),
            'insight' => $this->roleWorkspaceService->insightFor($currentUser, $summary),
            ...$this->equipmentFormData(),
        ]);
    }

    public function labStaff(): View
    {
        $currentUser = $this->userContext->current();
        $summary = $this->labStaffDashboardService->summaryFor($currentUser);

        return view('dashboard.lab-staff', [
            'currentUser' => $currentUser,
            'summary' => $summary,
            'workspace' => $this->roleWorkspaceService->workspaceFor($currentUser),
            'insight' => $this->roleWorkspaceService->insightFor($currentUser, $summary),
            'booking' => new Booking([
                'start_time' => now()->addHour()->startOfHour(),
                'end_time' => now()->addHours(3)->startOfHour(),
            ]),
            'equipments' => Equipment::query()->orderBy('equipment_code')->get(),
            'users' => User::query()->whereKey($currentUser->id)->get(),
        ]);
    }

    public function manager(): View
    {
        $currentUser = $this->userContext->current();
        $summary = $this->managerDashboardService->summaryFor($currentUser);

        return view('dashboard.manager', [
            'currentUser' => $currentUser,
            'summary' => $summary,
            'workspace' => $this->roleWorkspaceService->workspaceFor($currentUser),
            'insight' => $this->roleWorkspaceService->insightFor($currentUser, $summary),
            'booking' => new Booking([
                'start_time' => now()->addHour()->startOfHour(),
                'end_time' => now()->addHours(3)->startOfHour(),
            ]),
            'equipments' => Equipment::query()->orderBy('equipment_code')->get(),
            'users' => User::query()->whereKey($currentUser->id)->get(),
        ]);
    }

    public function researcher(): View
    {
        $currentUser = $this->userContext->current();
        $summary = $this->researcherDashboardService->summaryFor($currentUser);
        $booking = new Booking([
            'start_time' => now()->addHour()->startOfHour(),
            'end_time' => now()->addHours(3)->startOfHour(),
        ]);

        return view('dashboard.researcher', [
            'currentUser' => $currentUser,
            'summary' => $summary,
            'workspace' => $this->roleWorkspaceService->workspaceFor($currentUser),
            'insight' => $this->roleWorkspaceService->insightFor($currentUser, $summary),
            'booking' => $booking,
            'equipments' => Equipment::query()->orderBy('equipment_code')->get(),
            'users' => User::query()->whereKey($currentUser->id)->get(),
        ]);
    }

    public function runManagerAnalysis(Request $request): RedirectResponse|JsonResponse
    {
        $summary = $this->equipmentAnalysisBatchService->run(
            now()->subDays(30)->startOfDay(),
            now()->endOfDay(),
            $this->userContext->current(),
            'web',
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã chạy batch analysis cho '.$summary['processed_equipment'].' thiết bị. Rule match: '.$summary['matched_rules'].'.',
                'data' => $summary,
                'refresh' => ['target' => '#manager-critical-data'],
            ]);
        }

        return redirect()
            ->route('dashboard.manager')
            ->with('status', 'Đã chạy batch analysis cho '.$summary['processed_equipment'].' thiết bị. Rule match: '.$summary['matched_rules'].'.');
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

    private function equipmentFormData(): array
    {
        return [
            'equipment' => new Equipment([
                'usage_mode' => UsageMode::OnSite,
                'current_operational_status' => OperationalStatus::Off,
                'current_analysis_status' => AnalysisStatus::Normal,
                'utilization_rate' => 0,
            ]),
            'usageModes' => UsageMode::cases(),
            'operationalStatuses' => OperationalStatus::cases(),
            'analysisStatuses' => AnalysisStatus::cases(),
        ];
    }
}
