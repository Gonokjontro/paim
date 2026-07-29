<?php

namespace App\Http\Controllers;

use App\Models\Target;
use App\Models\Alert;
use App\Models\AlertPolicy;
use App\Services\AlertEvaluationService;
use Illuminate\Http\Request;

class TargetAlertController extends Controller
{
    protected AlertEvaluationService $alertService;

    public function __construct(AlertEvaluationService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function index()
    {
        $workspaceId = 1;

        // Run alert evaluation engine
        $this->alertService->evaluateWorkspaceTargets($workspaceId);

        $targets = Target::where('workspace_id', $workspaceId)->get();
        $alerts = Alert::where('workspace_id', $workspaceId)->orderBy('created_at', 'desc')->get();

        return view('targets.index', compact('targets', 'alerts'));
    }

    public function storeTarget(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'period_type' => 'required|string',
            'warning_threshold_pct' => 'required|integer|min:50|max:100',
        ]);

        $workspaceId = 1;
        Target::create([
            'workspace_id' => $workspaceId,
            'name' => $request->name,
            'scope_type' => 'global',
            'period_type' => $request->period_type,
            'target_amount' => $request->target_amount,
            'basis' => 'forecast',
            'warning_threshold_pct' => $request->warning_threshold_pct,
            'critical_threshold_pct' => 100,
            'status' => 'active',
        ]);

        return redirect()->route('targets.index')->with('success', "Budget target '{$request->name}' created.");
    }

    public function acknowledgeAlert($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->status = 'acknowledged';
        $alert->acknowledged_at = now();
        $alert->save();

        return back()->with('success', 'Alert acknowledged.');
    }
}
