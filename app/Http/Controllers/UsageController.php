<?php

namespace App\Http\Controllers;

use App\Models\TokenPackage;
use App\Models\UsageEntry;
use App\Models\MeterUnit;
use App\Models\Subscription;
use App\Services\TokenLedgerService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UsageController extends Controller
{
    protected TokenLedgerService $tokenLedgerService;

    public function __construct(TokenLedgerService $tokenLedgerService)
    {
        $this->tokenLedgerService = $tokenLedgerService;
    }

    public function index()
    {
        $workspaceId = 1;
        $tokenPackages = TokenPackage::where('workspace_id', $workspaceId)->with('subscription.tool', 'meterUnit')->get();
        $usageEntries = UsageEntry::where('workspace_id', $workspaceId)->with('subscription.tool', 'meterUnit')->orderBy('usage_date', 'desc')->get();
        $meterUnits = MeterUnit::where('workspace_id', $workspaceId)->get();
        $subscriptions = Subscription::where('workspace_id', $workspaceId)->where('status', 'active')->with('tool')->get();

        return view('usage.index', compact('tokenPackages', 'usageEntries', 'meterUnits', 'subscriptions'));
    }

    public function storeUsage(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'meter_unit_id' => 'required|exists:meter_units,id',
            'model_name' => 'nullable|string',
            'environment_project' => 'nullable|string',
            'usage_date' => 'required|date',
            'unit_count' => 'required|numeric|min:0.01',
        ]);

        $workspaceId = 1;
        $usageEntry = UsageEntry::create([
            'workspace_id' => $workspaceId,
            'subscription_id' => $request->subscription_id,
            'meter_unit_id' => $request->meter_unit_id,
            'model_name' => $request->model_name ?? 'gpt-4o',
            'environment_project' => $request->environment_project ?? 'default',
            'usage_date' => $request->usage_date,
            'unit_count' => $request->unit_count,
            'currency' => 'USD',
        ]);

        // Allocate via FIFO engine
        $cost = $this->tokenLedgerService->allocateUsageFIFO($usageEntry);

        return redirect()->route('usage.index')->with('success', "Usage of {$request->unit_count} units logged. Effective FIFO cost: $" . number_format($cost, 2));
    }

    public function storePackage(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'meter_unit_id' => 'required|exists:meter_units,id',
            'package_name' => 'required|string|max:255',
            'purchase_cost' => 'required|numeric|min:0',
            'granted_units' => 'required|numeric|min:1',
            'purchase_date' => 'required|date',
            'expiry_months' => 'nullable|integer|min:1',
        ]);

        $workspaceId = 1;
        $expiryDate = $request->expiry_months ? Carbon::parse($request->purchase_date)->addMonths((int)$request->expiry_months) : null;

        TokenPackage::create([
            'workspace_id' => $workspaceId,
            'subscription_id' => $request->subscription_id,
            'meter_unit_id' => $request->meter_unit_id,
            'package_name' => $request->package_name,
            'purchase_cost' => $request->purchase_cost,
            'granted_units' => $request->granted_units,
            'consumed_units' => 0,
            'remaining_units' => $request->granted_units,
            'purchase_date' => $request->purchase_date,
            'expiry_date' => $expiryDate,
            'status' => 'active',
        ]);

        return redirect()->route('usage.index')->with('success', "Token package '{$request->package_name}' added.");
    }
}
