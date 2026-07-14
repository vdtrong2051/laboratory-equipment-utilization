<?php

namespace App\Http\Controllers;

use App\Models\AnalysisRun;
use App\Services\AnalysisRunService;
use App\Services\Flow\AnalysisRunFlowPresenter;
use Illuminate\View\View;

class AnalysisRunController extends Controller
{
    public function __construct(
        private readonly AnalysisRunService $analysisRunService,
        private readonly AnalysisRunFlowPresenter $analysisRunFlowPresenter,
    ) {}

    public function index(): View
    {
        return view('analysis-runs.index', [
            'analysisRuns' => $this->analysisRunService->paginatedList(request()->only(['status', 'trigger_source'])),
            'statuses' => ['running', 'completed', 'failed'],
            'triggerSources' => AnalysisRun::query()->select('trigger_source')->distinct()->orderBy('trigger_source')->pluck('trigger_source'),
        ]);
    }

    public function show(AnalysisRun $analysisRun): View
    {
        return view('analysis-runs.show', [
            'analysisRun' => $analysisRun->load('triggeredBy'),
            'flow' => $this->analysisRunFlowPresenter->present($analysisRun),
        ]);
    }
}
