<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Tool;
use App\Models\PaymentAccount;
use App\Models\PlanVersion;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $workspaceId = 1;
        $query = Subscription::where('workspace_id', $workspaceId)
            ->with(['tool.vendor', 'tool.category', 'paymentAccount', 'currentPlanVersion']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->get();
        $tools = Tool::where('workspace_id', $workspaceId)->get();
        $paymentAccounts = PaymentAccount::where('workspace_id', $workspaceId)->get();
        $categories = Category::where('workspace_id', $workspaceId)->get();
        $vendors = Vendor::where('workspace_id', $workspaceId)->get();

        return view('subscriptions.index', compact('subscriptions', 'tools', 'paymentAccounts', 'categories', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tool_name' => 'required|string|max:255',
            'type' => 'required|string',
            'recurring_amount' => 'required|numeric|min:0',
            'billing_cadence_months' => 'required|integer|min:1',
            'payment_account_id' => 'nullable|exists:payment_accounts,id',
            'start_date' => 'required|date',
        ]);

        $workspaceId = 1;

        // Ensure tool exists or create it
        $tool = Tool::firstOrCreate([
            'workspace_id' => $workspaceId,
            'slug' => \Illuminate\Support\Str::slug($request->tool_name),
        ], [
            'name' => $request->tool_name,
            'is_ai_tool' => true,
            'status' => 'active',
        ]);

        $sub = Subscription::create([
            'workspace_id' => $workspaceId,
            'tool_id' => $tool->id,
            'payment_account_id' => $request->payment_account_id,
            'name' => $request->name,
            'type' => $request->type,
            'status' => 'active',
            'start_date' => $request->start_date,
            'end_date' => Carbon::parse($request->start_date)->addMonths((int) $request->billing_cadence_months),
            'billing_cadence_months' => $request->billing_cadence_months,
        ]);

        // Plan version
        $normalized = $request->type === 'annual_recurring'
            ? ($request->recurring_amount / 12.0)
            : ($request->recurring_amount / (int)$request->billing_cadence_months);

        PlanVersion::create([
            'subscription_id' => $sub->id,
            'effective_start_date' => $request->start_date,
            'billing_currency' => 'USD',
            'recurring_amount' => $request->recurring_amount,
            'normalized_monthly_amount' => round($normalized, 4),
        ]);

        return redirect()->route('subscriptions.index')->with('success', "Subscription '{$sub->name}' created successfully!");
    }

    public function show($id)
    {
        $subscription = Subscription::with(['tool.vendor', 'tool.category', 'paymentAccount', 'planVersions', 'costEntries', 'tokenPackages', 'usageEntries'])->findOrFail($id);
        return view('subscriptions.show', compact('subscription'));
    }

    public function updateStatus(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);
        $request->validate(['status' => 'required|string']);

        $subscription->status = $request->status;
        $subscription->save();

        return back()->with('success', "Subscription status updated to {$subscription->status}.");
    }
}
