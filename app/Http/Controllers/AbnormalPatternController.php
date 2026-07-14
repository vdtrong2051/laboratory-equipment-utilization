<?php

namespace App\Http\Controllers;

use App\Models\AbnormalPattern;
use App\Models\Equipment;
use App\Services\AbnormalPatternService;
use App\Services\UserContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbnormalPatternController extends Controller
{
    public function __construct(
        private readonly AbnormalPatternService $abnormalPatternService,
        private readonly UserContextService $userContext,
    ) {}

    public function index(): View
    {
        return view('abnormal-patterns.index', [
            'patterns' => $this->abnormalPatternService->paginatedList(request()->only(['status', 'severity', 'equipment_id', 'rule_name'])),
            'equipments' => Equipment::query()->orderBy('equipment_code')->get(),
            'statuses' => ['open', 'reviewed', 'resolved'],
            'severities' => ['info', 'warning', 'critical'],
            'ruleNames' => AbnormalPattern::query()->select('rule_name')->distinct()->orderBy('rule_name')->pluck('rule_name'),
        ]);
    }

    public function show(AbnormalPattern $abnormalPattern): View
    {
        return view('abnormal-patterns.show', [
            'pattern' => $abnormalPattern->load(['equipment', 'usageSession', 'reviewedBy', 'resolvedBy']),
        ]);
    }

    public function review(Request $request, AbnormalPattern $abnormalPattern): RedirectResponse|JsonResponse
    {
        $this->abnormalPatternService->review($abnormalPattern, $this->userContext->current());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã review bất thường.',
                'data' => ['abnormal_pattern_id' => $abnormalPattern->id],
                'refresh' => ['target' => '#abnormal-patterns-table'],
            ]);
        }

        return redirect()
            ->route('abnormal-patterns.show', $abnormalPattern)
            ->with('status', 'Đã review bất thường.');
    }

    public function resolve(Request $request, AbnormalPattern $abnormalPattern): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'resolution_note' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $this->abnormalPatternService->resolve($abnormalPattern, $this->userContext->current(), $validated['resolution_note']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã resolve bất thường.',
                'data' => ['abnormal_pattern_id' => $abnormalPattern->id],
                'refresh' => ['target' => '#abnormal-patterns-table'],
            ]);
        }

        return redirect()
            ->route('abnormal-patterns.show', $abnormalPattern)
            ->with('status', 'Đã resolve bất thường.');
    }
}
