<?php

namespace App\Http\Controllers;

use App\Enums\AnalysisStatus;
use App\Enums\OperationalStatus;
use App\Enums\UsageMode;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Equipment;
use App\Services\EquipmentService;
use App\Services\Flow\EquipmentFlowPresenter;
use App\Services\OperationalStatusService;
use App\Services\RuleEvaluationService;
use App\Services\UsageMetricService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function __construct(
        private readonly EquipmentService $equipmentService,
        private readonly OperationalStatusService $operationalStatusService,
        private readonly RuleEvaluationService $ruleEvaluationService,
        private readonly UsageMetricService $usageMetricService,
        private readonly EquipmentFlowPresenter $equipmentFlowPresenter,
    ) {}

    public function index(): View
    {
        return view('equipments.index', [
            'equipments' => $this->equipmentService->paginatedList(request()->only(['search', 'laboratory', 'usage_mode', 'analysis_status', 'operational_status', 'has_abnormal', 'attention'])),
            'laboratories' => $this->equipmentService->laboratories(),
            'usageModes' => UsageMode::cases(),
            'operationalStatuses' => OperationalStatus::cases(),
            'analysisStatuses' => AnalysisStatus::cases(),
            'blankEquipment' => new Equipment([
                'usage_mode' => UsageMode::OnSite,
            ]),
        ]);
    }

    public function create(): View
    {
        return view('equipments.create', [
            'equipment' => new Equipment([
                'usage_mode' => UsageMode::OnSite,
            ]),
            'usageModes' => UsageMode::cases(),
        ]);
    }

    public function store(StoreEquipmentRequest $request): RedirectResponse|JsonResponse
    {
        $equipment = $this->equipmentService->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã tạo thiết bị mới.',
                'data' => ['equipment_id' => $equipment->id],
                'refresh' => ['target' => '#equipments-table'],
            ]);
        }

        return redirect()
            ->route('equipments.show', $equipment)
            ->with('status', 'Đã tạo thiết bị mới.');
    }

    public function show(Equipment $equipment): View
    {
        $equipment->loadCount([
            'bookings',
            'usageSessions',
            'powerEvents',
            'activitySignals',
            'abnormalPatterns',
        ]);
        $equipment->load([
            'abnormalPatterns' => fn ($query) => $query->where('status', 'open')->latest('detected_at')->limit(8),
            'usageMetrics' => fn ($query) => $query->latest('period_end')->limit(6),
        ]);

        return view('equipments.show', [
            'equipment' => $equipment,
            'flow' => $this->equipmentFlowPresenter->present($equipment),
            'usageModes' => UsageMode::cases(),
        ]);
    }

    public function edit(Equipment $equipment): RedirectResponse
    {
        return redirect()
            ->route('equipments.show', $equipment)
            ->with('status', 'Trang sửa đầy đủ đang tạm ẩn. Hãy dùng Sửa nhanh trong modal để cập nhật metadata thiết bị.');
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): RedirectResponse|JsonResponse
    {
        $this->equipmentService->update($equipment, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã cập nhật thiết bị.',
                'data' => ['equipment_id' => $equipment->id],
                'refresh' => ['target' => '#equipments-table'],
            ]);
        }

        return redirect()
            ->route('equipments.show', $equipment)
            ->with('status', 'Đã cập nhật thiết bị.');
    }

    public function evaluateOperationalStatus(Equipment $equipment): RedirectResponse
    {
        $decision = $this->operationalStatusService->evaluateAndPersist($equipment);

        return redirect()
            ->route('equipments.show', $equipment)
            ->with('status', 'Đã cập nhật trạng thái vận hành: '.$decision->status->value.'. '.$decision->reason);
    }

    public function evaluateRules(Equipment $equipment): RedirectResponse
    {
        $results = $this->ruleEvaluationService->evaluateAndPersist($equipment);
        $matchedCount = $results->where('matched', true)->count();

        return redirect()
            ->route('equipments.show', $equipment)
            ->with('status', 'Đã đánh giá rule bất thường. Số rule match: '.$matchedCount.'.');
    }

    public function calculateUsageMetrics(Equipment $equipment): RedirectResponse
    {
        $metric = $this->usageMetricService->calculateForEquipment(
            $equipment,
            now()->subDays(30)->startOfDay(),
            now()->endOfDay(),
        );

        return redirect()
            ->route('equipments.show', $equipment)
            ->with('status', 'Đã tính usage metrics. Actual utilization: '.$metric->actual_utilization_rate.'%.');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        $this->equipmentService->delete($equipment);

        return redirect()
            ->route('equipments.index')
            ->with('status', 'Đã ngừng sử dụng thiết bị. Lịch sử liên quan vẫn được giữ lại.');
    }
}
